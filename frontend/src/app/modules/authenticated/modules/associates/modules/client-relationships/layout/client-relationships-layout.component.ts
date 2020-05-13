/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Component, ViewChild, OnDestroy } from '@angular/core';
import { Router, Event, NavigationEnd } from '@angular/router';
import { Subscription } from 'rxjs';
import { AuthService } from '@app/@core/services';
import { AssociatesClientRelationshipsService } from '@app/@shared/services/associates/client-relationships/client-relationships.service';
import { SwalComponent } from '@sweetalert2/ngx-sweetalert2';

@Component({
  selector: 'associates-client-relationships-layout',
  templateUrl: './client-relationships-layout.component.html',
  styleUrls: ['./client-relationships-layout.component.scss']
})
export class AssociatesClientRelationshipsLayoutComponent implements OnDestroy {
  @ViewChild('createdSwal', {static: false}) private createdSwal: SwalComponent;
  @ViewChild('mergedSwal', {static: false}) private mergedSwal: SwalComponent;

  _routerSubscriptions: Subscription;
  _createdSwalSubscriptions: Subscription;
  _mergedSwalSubscriptions: Subscription;
  title;
  banner;

  constructor(
    private assClientService: AssociatesClientRelationshipsService,
    private authService: AuthService,
    private router: Router) {

    this._routerSubscriptions = router.events.subscribe( (event: Event) => {
      if (event instanceof NavigationEnd) {
        var root = this.router.routerState.snapshot.root;
        while (root) {
          if (root.children && root.children.length) {
              root = root.children[0];
          } else if (root.data) {
              this.title = (root.data.title) ? root.data.title : null;
              this.banner = (root.data.banner === true) ? true : false;
              break;
          } else {
              break;
          }
        }
      }
    });

    this._createdSwalSubscriptions = this.assClientService.createdSwal.subscribe(() => this.createdSwal.show());
    this._mergedSwalSubscriptions = this.assClientService.mergedSwal.subscribe(() => this.mergedSwal.show());
  }

  ngOnDestroy() {
    if (this._routerSubscriptions) this._routerSubscriptions.unsubscribe();
    if (this._createdSwalSubscriptions) this._createdSwalSubscriptions.unsubscribe();
    if (this._mergedSwalSubscriptions) this._mergedSwalSubscriptions.unsubscribe();
  }

}
