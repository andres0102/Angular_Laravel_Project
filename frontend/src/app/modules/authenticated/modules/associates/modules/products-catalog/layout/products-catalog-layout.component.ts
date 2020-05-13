/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Component } from '@angular/core';
import { ActivatedRoute, Router, Event, NavigationStart, NavigationEnd, NavigationError } from '@angular/router';
import { Observable, fromEvent, merge, of, Subscription } from 'rxjs';
import { AuthService } from '@app/@core/services';
import { MatDialog } from '@angular/material/dialog';

@Component({
  selector: 'associates-products-catalog-layout',
  templateUrl: './products-catalog-layout.component.html',
  styleUrls: ['./products-catalog-layout.component.scss']
})
export class AssociatesProductsCatalogLayoutComponent {
  _routerSubscriptions: Subscription;
  title;
  banner;

  constructor(
    private route: ActivatedRoute,
    private authService: AuthService,
    private router: Router,
    public dialog: MatDialog) {

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
  }

  ngOnDestroy() {
    this._routerSubscriptions.unsubscribe();
  }

}
