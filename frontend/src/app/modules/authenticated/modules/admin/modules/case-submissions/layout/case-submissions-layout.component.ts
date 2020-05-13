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
import { AdminCaseSubmissionsService } from '@app/@shared/services/admin/case-submissions/case-submissions.service';

@Component({
  selector: 'admin-case-submissions-layout',
  templateUrl: './case-submissions-layout.component.html',
  styleUrls: ['./case-submissions-layout.component.scss']
})
export class AdminCaseSubmissionsLayoutComponent implements OnDestroy {

  _routerSubscriptions: Subscription;
  _createdSwalSubscriptions: Subscription;
  title;
  banner;

  constructor(
    public assSubmissionService: AdminCaseSubmissionsService,
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
