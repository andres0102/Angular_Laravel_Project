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
import { AssociatesCaseSubmissionsService } from '@app/@shared/services/associates/case-submissions/case-submissions.service';
import { SwalComponent } from '@sweetalert2/ngx-sweetalert2';

@Component({
  selector: 'associates-case-submissions-layout',
  templateUrl: './case-submissions-layout.component.html',
  styleUrls: ['./case-submissions-layout.component.scss']
})
export class AssociatesCaseSubmissionsLayoutComponent implements OnDestroy {
  @ViewChild('createdSwal', {static: false}) private createdSwal: SwalComponent;

  _routerSubscriptions: Subscription;
  _createdSwalSubscriptions: Subscription;
  title;
  banner;

  constructor(
    public assSubmissionService: AssociatesCaseSubmissionsService,
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

    this._createdSwalSubscriptions = this.assSubmissionService.createdSwal.subscribe(() => this.createdSwal.show());
  }

  ngOnDestroy() {
    if (this._routerSubscriptions) this._routerSubscriptions.unsubscribe();
    if (this._createdSwalSubscriptions) this._createdSwalSubscriptions.unsubscribe();
  }

}
