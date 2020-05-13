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
import { AdminSalesAssociatesService } from '@app/@shared/services/admin/sales-associates/sales-associates.service';
import { SwalComponent } from '@sweetalert2/ngx-sweetalert2';

@Component({
  selector: 'associates-sales-associates-layout',
  templateUrl: './sales-associates-layout.component.html',
  styleUrls: ['./sales-associates-layout.component.scss']
})
export class AdminSalesAssociatesLayoutComponent implements OnDestroy {
  @ViewChild('createdSwal', {static: false}) private createdSwal: SwalComponent;
  @ViewChild('createdLfaBandingSwal', {static: false}) private createdLfaBandingSwal: SwalComponent;
  @ViewChild('createdGIBandingSwal', {static: false}) private createdGIBandingSwal: SwalComponent;
  @ViewChild('createdMovementSwal', {static: false}) private createdMovementSwal: SwalComponent;
  @ViewChild('createdProviderCodeSwal', {static: false}) private createdProviderCodeSwal: SwalComponent;


  _routerSubscriptions: Subscription;
  _createdSwalSubscriptions: Subscription;
  _createdBandingLfaSwalSubscriptions: Subscription;
  _createdBandingGiSwalSubscriptions: Subscription;
  _createdMovementSwalSubscriptions: Subscription;
  _createdProviderCodeSwalSubscriptions: Subscription;
  title;
  banner;

  constructor(
    private adminSalesAssocService: AdminSalesAssociatesService,
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

    this._createdSwalSubscriptions = this.adminSalesAssocService.createdSwal.subscribe(() => this.createdSwal.show());
    this._createdBandingLfaSwalSubscriptions = this.adminSalesAssocService.createdBandingLfaSwal.subscribe(() => this.createdLfaBandingSwal.show());
    this._createdBandingGiSwalSubscriptions = this.adminSalesAssocService.createdBandingGiSwal.subscribe(() => this.createdGIBandingSwal.show());
    this._createdMovementSwalSubscriptions = this.adminSalesAssocService.createdMovementSwal.subscribe(() => this.createdMovementSwal.show());
    this._createdProviderCodeSwalSubscriptions = this.adminSalesAssocService.createdProviderCodeSwal.subscribe(() => this.createdProviderCodeSwal.show());
  }

  ngOnDestroy() {
    if (this._routerSubscriptions) this._routerSubscriptions.unsubscribe();
    if (this._createdSwalSubscriptions) this._createdSwalSubscriptions.unsubscribe();
    if (this._createdBandingLfaSwalSubscriptions) this._createdBandingLfaSwalSubscriptions.unsubscribe();
    if (this._createdBandingGiSwalSubscriptions) this._createdBandingGiSwalSubscriptions.unsubscribe();
    if (this._createdMovementSwalSubscriptions) this._createdMovementSwalSubscriptions.unsubscribe();
    if (this._createdProviderCodeSwalSubscriptions) this._createdProviderCodeSwalSubscriptions.unsubscribe();
  }

}
