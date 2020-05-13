/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { Component, OnInit, ViewChild, OnDestroy, ViewEncapsulation, HostListener } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { ActivatedRoute, Router, Event, NavigationStart, NavigationEnd, NavigationError } from '@angular/router';
import { PageService, SidebarService } from '@auth/services';
import { Observable, fromEvent, merge, of, Subscription } from 'rxjs';
import { mapTo } from 'rxjs/operators';

import { SwalComponent } from '@sweetalert2/ngx-sweetalert2';

import { AuthService, PusherService, IdentityService } from '@app/@core/services';
import { Breadcrumb, Online } from '@app/models';
import { environment } from '@env/environment';

import { menuLinks } from './menu';

@Component({
  selector: 'app-authenticated-layout',
  templateUrl: './authenticated-layout.component.html',
  styleUrls: ['./authenticated-layout.component.scss'],
  encapsulation: ViewEncapsulation.None
})
export class AuthenticatedLayoutComponent implements OnInit, OnDestroy {
  @ViewChild('onlineSwal', {static: false}) private onlineSwal: SwalComponent;

  online$: Observable<boolean>;
  _online_data: Online = new Online;
  connectivity_interval: any;
  connectivity_toggle: boolean = false;
  connectivity_message: any = "We have lost internet connectivity...";

  spinSymbol: boolean = false;
  menuLinks = menuLinks;
  _menuPin: boolean = false;
  _footer: boolean = false;
  _mobileSidebar: boolean = false;

  _document_offset: number = 0;
  _document_current_scroll: number = 0;

  _moduleAlerts: boolean = false;
  _moduleChats: boolean = false;
  _moduleTeams: boolean = false;
  _moduleSearch: boolean = false;

  _hideHeader: boolean = false;
  _condensed: boolean = false;
  _breadcrumb: boolean = false;
  _breadcrumb_data: Breadcrumb = new Breadcrumb;

  _subscriptions:  Array<Subscription> = [];
  _userLoginSubscriptions: Subscription;
  _onlineSubscriptions: Subscription;
  _connectionSubscriptions: Subscription;
  _routerSubscriptions: Subscription;

  constructor(
    public toggler: PageService,
    private route: ActivatedRoute,
    private router: Router,
    private http: HttpClient,
    private authService: AuthService,
    private pusherService: PusherService,
    private sidebarService: SidebarService,
    private identity: IdentityService) {

    // console.log('Starting Authenticated Layout Component');

    this._userLoginSubscriptions = this.pusherService.monitorUserLogins().subscribe((data) => {
      if (this.authService.user && this.authService.user['email'] == data['email']) this.pusherService.parseRoot('multiple-logins');
    });

    this.online$ = merge(
      of(navigator.onLine),
      fromEvent(window, 'online').pipe(mapTo(true)),
      fromEvent(window, 'offline').pipe(mapTo(false))
    );

    this._onlineSubscriptions = this.online$.subscribe(onlineSub => {
      this._online_data.connection_status = onlineSub;
      if (this._online_data.connection_status) {
        // this.http.post('https://secure.legacyfa-asia.com/status', {}).subscribe(data => {
        this.http.get('https://api.ipify.org/?format=json', {}).subscribe(data => {
          // this._online_data.secure_status = data['metadata']['secured'];
          // this._online_data.user_ip = data['metadata']['ip_address'];
          this._online_data.user_ip = data['ip'];
          this._online_data.secure_status = (data['ip'] === `${environment.secure_ip}`);

          if (this.connectivity_interval) {
            setTimeout(()=>this.onlineSwal.show());
            clearInterval(this.connectivity_interval);
          }
        });
      } else {
        setTimeout(()=>{ this.onlineSwal.show();});
        this.connectivity_interval = setInterval(() => {
          this.connectivity_toggle = !this.connectivity_toggle;
          this.connectivity_fn();
        }, 2000);
      }
    });

    this._routerSubscriptions = router.events.subscribe( (event: Event) => {
      if (event instanceof NavigationEnd) {
        var root = this.router.routerState.snapshot.root;
        while (root) {
          this.showHeader();
          if (root.children && root.children.length) {
              root = root.children[0];
          } else if (root.data) {
              //Custom Route Data here
              this._breadcrumb_data['_pageTitle'] = root.data["title"];
              this._breadcrumb_data['_parent1'] = root.data["parent1"] || root.parent.data["parent1"] || root.parent.parent.data["parent1"] || root.parent.parent.parent.data["parent1"] || root.parent.parent.parent.parent.data["parent1"] || null;
              this._breadcrumb_data['_parent2'] = root.data["parent2"] || root.parent.data["parent2"] || root.parent.parent.data["parent2"] || root.parent.parent.parent.data["parent2"] || null;
              this._breadcrumb_data['_parent3'] = root.data["parent3"] || root.parent.data["parent3"] || root.parent.parent.data["parent3"] || null;
              this._breadcrumb_data['_parent1_link'] = root.data["parent1_link"] || root.parent.data["parent1_link"] || root.parent.parent.data["parent1_link"] || root.parent.parent.parent.data["parent1_link"] || root.parent.parent.parent.parent.data["parent1_link"] || null;
              this._breadcrumb_data['_parent2_link'] = root.data["parent2_link"] || root.parent.data["parent2_link"] || root.parent.parent.data["parent2_link"] || root.parent.parent.parent.data["parent2_link"] || null;
              this._breadcrumb_data['_parent3_link'] = root.data["parent3_link"] || root.parent.data["parent3_link"] || root.parent.parent.data["parent3_link"] || null;
              this._breadcrumb_data['_exitButton'] = root.data["exitButton"] || false;
              this._breadcrumb_data['_exitLink'] = root.data["exitLink"] || false;
              this._hideHeader = root.data["hideHeader"] || false;
              this._condensed = root.data["condensed"] || false;
              this._breadcrumb = root.data["breadcrumb"] || false;
              this._footer = root.data["footer"] || false;
              break;
          } else {
              break;
          }
        }

        //Close Sidebar and Horizonta Menu
        if(this._mobileSidebar){
          this._mobileSidebar = false;
          // this.toggler.toggleMobileSideBar(this._mobileSidebar);
          this.sidebarService.close();
        }

        //Scoll Top
        this.scrollToTop();
        this.closeMobileSidebar();
      }
    });

    this._connectionSubscriptions = this.toggler.connectionSwal.subscribe(() => this.onlineSwal.show());

    // Base Authenticated URL Redirect :: Based on User Roles
    if (this.router.url == '/authenticated') {
      // Check if user is admin
      if (this.authService.user.roles.is_associate) {
        this.router.navigate([this.router.url + '/associates']);
      // Check if user is staff
      } else if (this.authService.user.roles.is_staff) {
        this.router.navigate([this.router.url + '/admin']);
      }
    }
  }

  ngOnInit() {
  }

  ngOnDestroy() {
    this._userLoginSubscriptions.unsubscribe();
    this._onlineSubscriptions.unsubscribe();
    this._connectionSubscriptions.unsubscribe();
    this._routerSubscriptions.unsubscribe();
    this.pusherService.channel_authentication.unbind();
  }

  connectivity_fn() {
    if (this.connectivity_toggle) this.connectivity_message = "We have lost internet connectivity...";
    else this.connectivity_message = "Attempting to reconnect...";
  }



  /** @function closeMobileSidebar
  *   @description Open Main Sidebar on Mobile - Service
  */
  closeMobileSidebar(){
    // pg.removeClass(document.body,"sidebar-open");
    // this.toggler.toggleMobileSideBar(false);
    this.sidebarService.close();
  }


  /**
  *   @function scrollToTop
  *   @description Force to scroll to top of page. Used on Route
  */
  scrollToTop(){
    let top = window.pageYOffset;
    if(top == 0){
      let scroller = document.querySelector(".page-container");
      if(scroller)
        scroller.scrollTo(0,0);
    }
    else{
      window.scrollTo(0, 0)
    }
  }

  /** @function openSearch
  *   @description Show Quick Search Component - Service
  */
  openSearch($e){
    $e.preventDefault();
    this.toggler.toggleSearch(true);
  }

  showHeader() {
    document.getElementsByTagName('body')[0].classList.remove("header-hidden");
    document.getElementsByTagName('pg-header')[0].removeAttribute('style');
  }
  hideHeader() {
    document.getElementsByTagName('body')[0].classList.add("header-hidden");
    document.getElementsByTagName('pg-header')[0].setAttribute('style','transform:translateY(-100%); box-shadow: none;');
  }

  @HostListener("window:resize", [])
  onResize() {
    this.toggler.pageResize();
  }

  @HostListener("copy", ['$event'])
  onCopy(event: any) {
    event.preventDefault();
    let pagelink = '\n\n Read more at: ' + document.location.href, copytext =  window.getSelection() + pagelink;
    if (event.clipboardData) event.clipboardData.setData('Text', copytext);
  }

  @HostListener("window:scroll", ['$event'])
  onWindowScroll($event) {
    this._document_offset = $event.target.documentElement.scrollTop || $event.target.scrollingElement.scrollTop || 0;
    if (this._document_offset < this._document_current_scroll) {
      this.showHeader();
    } else if (this._document_offset > this._document_current_scroll && this._document_offset >= 80 && this._hideHeader) {
      this.hideHeader();
    }
    this._document_current_scroll = this._document_offset;
  }

}
