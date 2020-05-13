/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { Injectable } from '@angular/core';
import { Observable, Subject, Subscription } from 'rxjs';
import { BreakpointObserver, Breakpoints } from '@angular/cdk/layout';

@Injectable()
export class PageService {
  _breakpoint: Subscription;
  mobile_query: string = '(max-width: 575px)';
  desktop_small_query: string = '(min-width: 576px) and (max-width: 991px)';
  desktop_large_query: string = '(min-width: 992px)';
  isMobile: boolean = false;
  isDesktop: boolean = false;
  isDesktopSmall: boolean = false;
  isDesktopBig: boolean = false;

  constructor(breakpointObserver: BreakpointObserver) {
    this._breakpoint = breakpointObserver.observe([
      this.mobile_query, this.desktop_small_query, this.desktop_large_query
    ]).subscribe(result => {
      this.isMobile = this.isDesktop = this.isDesktopSmall = this.isDesktopBig = false;
      if (result.matches && breakpointObserver.isMatched(this.mobile_query)) this.isMobile = true;
      else if (result.matches && (breakpointObserver.isMatched(this.desktop_small_query) || breakpointObserver.isMatched(this.desktop_large_query))) {
        this.isDesktop = true;
        if (breakpointObserver.isMatched(this.desktop_small_query)) this.isDesktopSmall = true;
        if (breakpointObserver.isMatched(this.desktop_large_query)) this.isDesktopBig = true;
      }
    });
  }

  // Fullscreen Function
  setFullScreen(element) {
    // Supports most browsers and their versions.
    let requestMethod = element.requestFullScreen || element.webkitRequestFullScreen || element.mozRequestFullScreen || element.msRequestFullscreen;
    if (requestMethod) { // Native full screen.
        requestMethod.call(element);
    }
  };

  // Search Toggle
  private _searchToggle = new Subject();
  searchToggle = this._searchToggle.asObservable();
  toggleSearch(toggle: boolean) { this._searchToggle.next({text:toggle}); }

  // Page Container Hover Event - Used for sidebar
  private _pageContainerHover = <Subject<boolean>> new Subject();
  pageContainerHover = this._pageContainerHover.asObservable();
  triggerPageContainerHover(toggle: boolean){ this._pageContainerHover.next(toggle); }

  // Connection Event
  private _connectionSwal = <Subject<boolean>> new Subject();
  connectionSwal = this._connectionSwal.asObservable();
  toggleConnectionSwal(){ this._connectionSwal.next(); }

  // Page Resizing Event
  private _windowResize = new Subject();
  windowResize = this._windowResize.asObservable();
  pageResize(){ this._windowResize.next(); }

  // Parent Component Refresh Event
  private _parentRefresh = <Subject<boolean>> new Subject();
  parentRefresh = this._parentRefresh.asObservable();
  triggerParentRefresh(toggle: boolean){ this._parentRefresh.next(toggle); }
}