/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { Injectable, RendererFactory2 } from '@angular/core';
import { Subject } from 'rxjs';

import { LocalStorageService } from '@app/@core/services';

@Injectable()
export class SidebarService {
  constructor(private rendererFactory: RendererFactory2, private local: LocalStorageService) {
    this.renderer = rendererFactory.createRenderer(null, null);

    if (local.getItem('sidebar-pinned') === true) this.pin();
    if (local.getItem('sidebar-mobile-opened') === true) this.openMobile();
  }

  renderer: any;
  isOpen: boolean = false;
  isOpenMobile: boolean = false;
  isFocused: boolean = false;
  isMouse: boolean = false;
  isTab: boolean = false;
  isPinned: boolean = false; // Mobile + Fixed

  // User focus on the sidebar
  focus(elem = null) {
    if (elem == 'mouse') this.isMouse = true;
    if (elem == 'tab') this.isTab = true;
    // If mouse or tab is activated, execute the command
    if (!this.isFocused && (this.isMouse || this.isTab)) {
      this.isFocused = true;
      this.renderer.addClass(document.body, 'sidebar-focused');
      this.open();
    }
  }

  // User unfocus on the sidebar
  blur(elem = null) {
    if (elem == 'mouse') this.isMouse = false;
    if (elem == 'tab') this.isTab = false;
    // If mouse or tab is still activated, ignore the command
    if (this.isFocused && !this.isMouse && !this.isTab) {
      this.isFocused = false;
      this.renderer.removeClass(document.body, 'sidebar-focused');
      this.close();
    }
  }

  // User pin the sidebar for permanent view (Desktop)
  pin() {
    if (!this.isPinned) {
      this.isPinned = true;
      this.renderer.addClass(document.body, 'sidebar-pinned');
      this.local.setItem('sidebar-pinned', true);
      this.open();
    }
  }

  // User unpin the sidebar (Desktop)
  unpin() {
    if (this.isPinned) {
      this.isPinned = false;
      this.renderer.removeClass(document.body, 'sidebar-pinned');
      this.local.setItem('sidebar-pinned', false);
      this.close();
    }
  }

  // Toggle sidebar pin (Desktop)
  toggle_pin() {
    if (!this.isPinned) this.pin();
    else this.unpin();
  }

  // User open the sidebar for permanent view (Mobile)
  openMobile() {
    if (!this.isOpenMobile) {
      this.isOpenMobile = true;
      this.renderer.addClass(document.body, 'sidebar-mobile-opened');
      this.local.setItem('sidebar-mobile-opened', true);
      this.open();
    }
  }

  // User unpin the sidebar (Desktop)
  closeMobile() {
    if (this.isOpenMobile) {
      this.isOpenMobile = false;
      this.renderer.removeClass(document.body, 'sidebar-mobile-opened');
      this.local.setItem('sidebar-mobile-opened', false);
      this.close();
    }
  }

  // Toggle sidebar pin (Desktop)
  toggle_mobile() {
    if (!this.isOpenMobile) this.openMobile();
    else this.closeMobile();
  }

  // Separate function to open the sidebar
  // If sidebar is already fixed or focused or opened, ignore the command
  open() {
    if (!this.isOpen && !this.isPinned && !this.isFocused) {
      this.isOpen = true;
    }
  }

  // Separate function to close the sidebar
  // If sidebar is still fixed or focused, ignore the command
  close() {
    if (this.isOpen && !this.isPinned && !this.isFocused) {
      this.isOpen = false;
    }
  }
}
