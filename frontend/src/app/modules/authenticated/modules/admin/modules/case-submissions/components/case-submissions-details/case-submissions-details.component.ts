/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Component, OnInit, AfterContentInit, OnDestroy } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { FormBuilder, Validators } from '@angular/forms';
import { Subscription } from 'rxjs';

import { Submission } from '@app/models';
import { AuthService, SessionStorageService, ApiService } from '@app/@core/services';
import { PageService } from '@auth/services/page';
import { AdminCaseSubmissionsService } from '@app/@shared/services/admin/case-submissions/case-submissions.service';
import { MessageService } from '@app/@shared/components/message/message.service';

@Component({
  selector: 'admin-case-submissions-details',
  templateUrl: './case-submissions-details.component.html',
  styleUrls: ['./case-submissions-details.component.scss']
})
export class AdminCaseSubmissionsDetailsComponent {
  page_permission = 'submissions_mgmt_view';
  selectionsSub: Subscription;
  parentRefSub: Subscription;
  restoreSub: Subscription;
  submitSub: Subscription;
  deleteSub: Subscription;
  updateSub: Subscription;
  resized: boolean = false;
  // editing: boolean = false;
  step = 0;

  setStep(index: number) {
    this.step = index;
  }

  nextStep() {
    this.step++;
  }

  prevStep() {
    this.step--;
  }

  submission: Submission;
  submissionUpdate: boolean = false;
  processing: boolean = false;

  messages = {
    emptyMessage: 'No records to display.',
    totalMessage: 'Records'
  };

  constructor (
    private formBuilder: FormBuilder,
    private authService: AuthService,
    private apiService: ApiService,
    private pageService: PageService,
    private sessionStorageService: SessionStorageService,
    private route: ActivatedRoute,
    private router: Router,
    private _notification: MessageService,
    private assSubmissionService: AdminCaseSubmissionsService) {
    this.submission = this.route.snapshot.data.submission;
    this.parentRefSub = this.pageService.parentRefresh.subscribe(() => this.getSubmission());
  }

  ngOnInit() {
    this.updateModel();
  }

  ngAfterContentInit() {
    if (!this.resized) setTimeout(() => { window.dispatchEvent(new Event('resize')); });
  }

  ngOnDestroy() {
    if (this.parentRefSub) this.parentRefSub.unsubscribe();
    if (this.selectionsSub) this.selectionsSub.unsubscribe();
    if (this.restoreSub) this.restoreSub.unsubscribe();
    if (this.submitSub) this.submitSub.unsubscribe();
    if (this.deleteSub) this.deleteSub.unsubscribe();
    if (this.updateSub) this.updateSub.unsubscribe();
  }

  getSubmission() {
    let uuid = this.submission.uuid;
    this.submissionUpdate = true;
    this.assSubmissionService.getSubmissions(uuid).subscribe((res) => {
      this.updateModel(res['data']);
      // console.log('Submission Details:', res['data']);
    });
  }

  updateModel(data = null) {
    if (data) this.submission = data;
    this.submissionUpdate = false;
  }

  routeBack() {
    if (this.sessionStorageService.getItem('previous_url')) this.sessionStorageService.removeItem('previous_url');
    if (this.sessionStorageService.getItem('previous_title')) this.sessionStorageService.removeItem('previous_title');
    // this.sessionStorageService.setItem('previous_uuid', this.submission.uuid);
  }

  routeStore() {
    this.sessionStorageService.setItem('previous_title', 'Submission Details');
    this.sessionStorageService.setItem('previous_url', this.router.url);
  }

  incomeRangeText(income_range) {
    switch (income_range) {
      case 0: return '$0 - $29,999' ; break;
      case 30000: return '$30,000 - $49,999' ; break;
      case 50000: return '$50,000 - $99,999' ; break;
      case 100000: return '$100,000 - $149,999' ; break;
      case 150000: return '$150,000 - $299,999' ; break;
      case 300000: return '$300,000 and above' ; break;
      default: return income_range;
    }
  }

  restoreSubmission() {
    this.restoreSub = this.apiService.post('admin/submissions/' + this.submission.uuid + '/restore').subscribe((res) => {
      // console.log("Restored submission", res);
      if (res.error === false) {
        this.updateModel(res['data']);
        this.router.navigate(['/authenticated/admin/case-submissions/' + this.submission.uuid]);
        this._notification.create('success', 'Success! Deleted submission record is restored.', { Position: 'top', Style: 'bar', Duration: 2000 });
      }
    });
  }

  deleteSubmission() {
    this.processing = true;
    this.deleteSub = this.apiService.delete('admin/submissions/' + this.submission.uuid).subscribe((res) => {
      // console.log("Deleted submission", res);
      if (res.error === false) {
        this.updateModel(res['data']);
        this.router.navigate(['/authenticated/admin/case-submissions/' + this.submission.uuid]);
        this._notification.create('success', 'Success! Selected submission record is deleted from the server.', { Position: 'top', Style: 'bar', Duration: 2000 });
      }
    });
  }

  updateSubmissionStatus(new_status) {
    this.processing = true;
    this.updateSub = this.apiService.post('admin/submissions/' + this.submission.uuid + '/status/' + new_status).subscribe((res) => {
      // console.log("Updated submission", res);
      if (res.error === false) {
        this.updateModel(res['data']);
        this.router.navigate(['/authenticated/admin/case-submissions/' + this.submission.uuid]);
        this._notification.create('success', 'Success! Submission Status is updated.', { Position: 'top', Style: 'bar', Duration: 2000 });
      }
    });
  }
}
