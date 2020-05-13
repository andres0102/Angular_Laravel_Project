/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Component, OnDestroy } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { FormBuilder, Validators } from '@angular/forms';
import { Subscription } from 'rxjs';

import { Associate } from '@app/models';
import { AuthService, SessionStorageService, ApiService } from '@app/@core/services';
import { PageService } from '@auth/services/page';
import { AdminSalesAssociatesService } from '@app/@shared/services/admin/sales-associates/sales-associates.service';
import { MessageService } from '@app/@shared/components/message/message.service';

@Component({
  selector: 'admin-associate-details',
  templateUrl: './associate-details.component.html',
  styleUrls: ['./associate-details.component.scss']
})
export class AdminAssociateDetailsComponent implements OnDestroy {
  private page_permission: string = 'associates_mgmt_view';
  private parentRefSub: Subscription;
  private associateSub: Subscription;
  private updateAssociateSub: Subscription;

  associate: Associate;

  constructor (
    private formBuilder: FormBuilder,
    private authService: AuthService,
    private pageService: PageService,
    private sessionStorageService: SessionStorageService,
    private route: ActivatedRoute,
    private router: Router,
    private _notification: MessageService,
    private apiService: ApiService,
    private adminSalesAssocService: AdminSalesAssociatesService) {
    this.associate = this.route.snapshot.data.associate;
    this.parentRefSub = this.pageService.parentRefresh.subscribe(() => this.getAssociates());
  }

  getAssociates() {
    let uuid = this.associate.uuid;
    this.associateSub = this.adminSalesAssocService.getAssociates(uuid).subscribe((res) => {
      this.updateModel(res['data']);
    });
  }

  ngOnDestroy() {
    if (this.parentRefSub) this.parentRefSub.unsubscribe();
    if (this.associateSub) this.associateSub.unsubscribe();
  }

  updateModel(data = null) {
    if (data) this.associate = data;
  }

  routeBack() {
    if (this.sessionStorageService.getItem('previous_url')) this.sessionStorageService.removeItem('previous_url');
    if (this.sessionStorageService.getItem('previous_title')) this.sessionStorageService.removeItem('previous_title');
  }

  sendWelcomeEmail() {
    let url = 'admin/associates/' + this.associate.uuid + '/send-welcome-email';
    this.apiService.post(url).subscribe((res) => {
      // console.log("Send Email response", res);
      if (!res.error) this._notification.create('success', 'Success! Email sent to: ' + res['data']['email'], { Position: 'top', Style: 'bar', Duration: 2000 });
    });
  }
}
