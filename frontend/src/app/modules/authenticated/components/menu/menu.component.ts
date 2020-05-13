/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { Component, Input, ViewEncapsulation } from "@angular/core";
import {
  animate,
  state,
  style,
  transition,
  trigger
} from "@angular/animations";

import { PerfectScrollbarConfigInterface } from "ngx-perfect-scrollbar";
import { PageService, SidebarService } from "@auth/services";
import { AuthService } from "@app/@core/services";

@Component({
  selector: "menu-items",
  templateUrl: "./menu.component.html",
  styleUrls: ["./menu.component.scss"],
  animations: [
    trigger("toggleHeight", [
      state(
        "close",
        style({
          height: "0",
          overflow: "hidden",
          opacity: 0,
          padding: 0
          // display:'none',
        })
      ),
      state(
        "open",
        style({
          display: "block",
          height: "*",
          opacity: 1,
          padding: "*",
          overflow: "hidden"
        })
      ),
      transition("close => open", animate("240ms ease-in")),
      transition("open => close", animate("240ms ease-out"))
    ])
  ],
  encapsulation: ViewEncapsulation.None
})
export class MenuComponent {
  menuItems = [];
  currentItem = null;
  public config: PerfectScrollbarConfigInterface = {};

  constructor(
    public pageService: PageService,
    public sidebarService: SidebarService,
    private authService: AuthService
  ) {}

  @Input()
  set items(value) {
    this.menuItems = value;
  }

  toggleChild(event, item) {
    event.preventDefault();
    if (this.currentItem && this.currentItem != item) {
      this.currentItem["toggle"] = "close";
    }
    this.currentItem = item;
    item.toggle = item.toggle === "close" ? "open" : "close";
  }

  haveAccess(inputRoles = null) {
    if (inputRoles === null) {
      // xxx || this.authService.user.is_admin
      return true;
    } else if (inputRoles) {
      let access = false;
      inputRoles.forEach(role => {
        if (
          (role == "admin" &&
            this.authService.user &&
            this.authService.user.roles.is_staff) ||
          (role == "team" &&
            this.authService.user &&
            this.authService.user.roles.is_associate &&
            this.authService.user.roles.is_manager) ||
          (role == "associate" &&
            this.authService.user &&
            this.authService.user.roles.is_associate)
        )
          access = true;
      });
      return access;
    }
  }
}
