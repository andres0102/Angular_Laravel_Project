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
import { AdminClientRelationshipsService } from '@app/@shared/services/admin/client-relationships/client-relationships.service';

@Component({
  selector: 'admin-client-relationships-layout',
  templateUrl: './client-relationships-layout.component.html',
  styleUrls: ['./client-relationships-layout.component.scss']
})
export class AdminClientRelationshipsLayoutComponent implements OnDestroy {
  _routerSubscriptions: Subscription;
  title;
  banner;

  constructor(
    private assClientService: AdminClientRelationshipsService,
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
  }

  ngOnDestroy() {
    if (this._routerSubscriptions) this._routerSubscriptions.unsubscribe();
  }

}
