/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { Component, ViewEncapsulation, HostListener, TemplateRef, ContentChild } from '@angular/core';

import { SidebarService, PageService } from '@auth/services';

@Component({
  selector: 'authenticated-sidebar',
  templateUrl: './sidebar.component.html',
  styleUrls: ['./sidebar.component.scss'],
  host: {
    'class': 'authenticated-sidebar',
  },
  encapsulation: ViewEncapsulation.None
})
export class SidebarComponent {
  constructor(private pageService: PageService, private sidebarService: SidebarService) { }
  @ContentChild('sidebarHeader', {static: false}) sidebarHeader: TemplateRef<void>;
  @ContentChild('menuItems', {static: false}) menuItems: TemplateRef<void>;

  @HostListener('document:keydown', ["$event"])
  handleKeydownEvent(event: KeyboardEvent) {
    if (event.key == "Tab") {
      this.sidebarService.focus('tab');
    }
  }

  @HostListener('document:keyup', ["$event"])
  handleKeyupEvent(event: KeyboardEvent) {
    if (event.key == "Tab") {
      this.sidebarService.blur('tab');
    }
  }

  @HostListener('mouseenter', ["$event"]) handleMouseEnterEvent() {
    this.sidebarService.focus('mouse');
  }

  @HostListener('mouseleave', ["$event"]) handleMouseOutEvent() {
    this.sidebarService.blur('mouse');
  }
}
