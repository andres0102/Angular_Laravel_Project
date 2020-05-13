/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { Component, Input, ViewEncapsulation, AfterViewInit } from '@angular/core';
import { Subscriber } from 'rxjs'
import { AuthService } from '@app/@core/services';
import { PageService, SidebarService } from '@auth/services';
import { Online } from '@app/models';
import Typed from 'typed.js';

@Component({
  selector: 'pg-header',
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.scss'],
  encapsulation: ViewEncapsulation.None
})
export class HeaderComponent implements AfterViewInit {
	@Input() online: Online;
	@Input() breadcrumb: boolean = false;
	@Input() mobileSidebar: boolean = false;
	@Input() spinSymbol: boolean = false;

  @Input() _moduleAlerts: boolean = false;
  @Input() _moduleTeams: boolean = false;
  @Input() _moduleChats: boolean = false;
  @Input() _moduleSearch: boolean = false;

  logout_menu_show: boolean = false;

  _type_options = {
    strings: [
      "colleagues",
      "submissions",
      "friends",
      "schedules",
      "teammates",
      "messages",
      "clients",
      "leads",
      "policies",
      "payroll"
    ],
    typeSpeed: 70,
    backSpeed: 30,
    startDelay: 1000,
    backDelay: 2500,
    loop: true,
    shuffle: true,
    smartBackspace: false,
    cursorChar: '|'
  };


	constructor(
		public pageService: PageService,
    public sidebarService: SidebarService,
		private authService: AuthService) { }

  ngAfterViewInit() {
    if (this._moduleSearch) new Typed(".typed-element", this._type_options);
  }

  openConnectionSwal() { this.pageService.toggleConnectionSwal(); }
}
