/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Component, OnInit, OnDestroy } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Subscription } from 'rxjs';

import { Submission } from '@app/models';
import { ApiService, TitleService } from '@app/@core/services';
import { PageService } from '@auth/services/page';
import { AdminSalesAssociatesService } from '@app/@shared/services/admin/sales-associates/sales-associates.service';

@Component({
  selector: 'admin-associate-audit-log',
  templateUrl: './associate-audit-log.component.html',
  styleUrls: ['./associate-audit-log.component.scss'],
})
export class AdminAssociateAuditLogComponent implements OnInit, OnDestroy {
  private logSub: Subscription;
  associate;
  logs;
  dataLoaded: boolean = false;

  constructor (private route: ActivatedRoute,
               private apiService: ApiService,
               private pageService: PageService,
               private titleService: TitleService,
               private adminSalesAssocService: AdminSalesAssociatesService) {
    this.associate = this.route.parent.snapshot.data.associate;
  }

  ngOnInit() {
    this.getLogs();
  }

  ngOnDestroy() {
    if (this.logSub) this.logSub.unsubscribe();
  }

  getLogs() {
    if (this.associate) {
      this.dataLoaded = false;
      this.logs = [];
      this.logSub = this.adminSalesAssocService.getLogs(this.associate.uuid).subscribe(res => {
        let data = res.data;
        this.logs = data;
        this.dataLoaded = true;
      });
    }
  }
}
