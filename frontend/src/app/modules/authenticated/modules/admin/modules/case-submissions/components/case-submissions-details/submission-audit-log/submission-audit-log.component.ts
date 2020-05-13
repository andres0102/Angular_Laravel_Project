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
import { AdminCaseSubmissionsService } from '@app/@shared/services/admin/case-submissions/case-submissions.service';

@Component({
  selector: 'admin-submission-audit-log',
  templateUrl: './submission-audit-log.component.html',
  styleUrls: ['./submission-audit-log.component.scss'],
})
export class AdminSubmissionAuditLogComponent implements OnInit, OnDestroy {
  private logSub: Subscription;
  submission;
  logs;
  dataLoaded: boolean = false;

  constructor (private route: ActivatedRoute,
               private apiService: ApiService,
               private pageService: PageService,
               private titleService: TitleService,
               private assSubmissionService: AdminCaseSubmissionsService) {
    this.submission = this.route.parent.snapshot.data.submission;
  }

  ngOnInit() {
    this.getLogs();
  }

  ngOnDestroy() {
    if (this.logSub) this.logSub.unsubscribe();
  }

  getLogs() {
    if (this.submission) {
      this.dataLoaded = false;
      this.logs = [];
      this.logSub = this.assSubmissionService.getLogs(this.submission.uuid).subscribe(res => {
        let data = res.data;
        this.logs = data;
        this.dataLoaded = true;
      });
    }
  }
}
