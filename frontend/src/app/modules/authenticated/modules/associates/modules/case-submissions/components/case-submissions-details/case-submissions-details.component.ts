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
import { MatBottomSheet, MatBottomSheetRef, MatBottomSheetConfig, } from '@angular/material/bottom-sheet';

import { Submission } from '@app/models';
import { AuthService, SessionStorageService, ApiService } from '@app/@core/services';
import { PageService } from '@auth/services/page';
import { AssociatesCaseSubmissionsService } from '@app/@shared/services/associates/case-submissions/case-submissions.service';
import { MessageService } from '@app/@shared/components/message/message.service';
import { AssociatesSubmissionDraftComponent } from './submission-draft/submission-draft.component';

@Component({
  selector: 'associates-case-submissions-details',
  templateUrl: './case-submissions-details.component.html',
  styleUrls: ['./case-submissions-details.component.scss']
})
export class AssociatesCaseSubmissionsDetailsComponent {
  page_permission = 'associate_submissions_view';
  selectionsSub: Subscription;
  parentRefSub: Subscription;
  beginSub: Subscription;
  submitSub: Subscription;
  deleteSub: Subscription;
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

  mbsConfig: MatBottomSheetConfig;

  constructor (
    private _bottomSheet: MatBottomSheet,
    private formBuilder: FormBuilder,
    private authService: AuthService,
    private apiService: ApiService,
    private pageService: PageService,
    private sessionStorageService: SessionStorageService,
    private route: ActivatedRoute,
    private router: Router,
    private _notification: MessageService,
    private assSubmissionService: AssociatesCaseSubmissionsService) {
    this.submission = this.route.snapshot.data.submission;
    this.parentRefSub = this.pageService.parentRefresh.subscribe(() => this.getSubmission());

    // if (this.submission.case_count > 0) this.router.navigate(['cases'], { relativeTo: this.route, replaceUrl: true });
    // else this.router.navigate(['audit-log'], { relativeTo: this.route, replaceUrl: true });

    this.mbsConfig = {
      disableClose: true,
      panelClass: 'mat-bottom-sheet-large',
      data: {
        submission: this.submission,
        case: null
      }
    };
  }

  ngOnInit() {
    this.updateModel();
    // if (this.submission.status_slug == 'draft' && this.submission.case_count == 0) this.enableEditingMode();
  }

  ngAfterContentInit() {
    if (!this.resized) setTimeout(() => { window.dispatchEvent(new Event('resize')); });
  }

  ngOnDestroy() {
    if (this.parentRefSub) this.parentRefSub.unsubscribe();
    if (this.selectionsSub) this.selectionsSub.unsubscribe();
    if (this.beginSub) this.beginSub.unsubscribe();
    if (this.submitSub) this.submitSub.unsubscribe();
    if (this.deleteSub) this.deleteSub.unsubscribe();
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

  enableEditingMode(): void {
    this._bottomSheet.open(AssociatesSubmissionDraftComponent, this.mbsConfig);
  }

  beginDraft() {
    this.beginSub = this.apiService.post('associates/submissions/' + this.submission.uuid + '/begin-submission').subscribe((res) => {
      // console.log("Begin Draft", res);
      if (res.error === false) {
        this.router.routeReuseStrategy.shouldReuseRoute = function(){ return false; };
        let currentUrl = this.router.url + '?';
        this.router.navigateByUrl(currentUrl)
          .then(() => {
            this.router.navigated = false;
            this.router.navigate([this.router.url]);
          });
        let data = res.data;
        this.submissionUpdate = true;
        this.updateModel(data);
        this._notification.create('success', 'Success! Draft created.', { Position: 'top', Style: 'bar', Duration: 2000 });
      }
    });
  }


  submitSubmission() {
    this.processing = true;
    this.submitSub = this.apiService.post('associates/submissions/' + this.submission.uuid + '/submit').subscribe((res) => {
      // console.log("Submitted submission", res);
      if (res.error === false) {
        this.updateModel(res['data']);
        this.router.navigate(['/authenticated/associates/business/case-submissions/' + this.submission.uuid]);
        this._notification.create('success', 'Success! Submission is submitted, pending for screening/verification.', { Position: 'top', Style: 'bar', Duration: 2000 });
      }
    });
  }


  deleteSubmission() {
    this.processing = true;
    this.deleteSub = this.apiService.delete('associates/submissions/' + this.submission.uuid).subscribe((res) => {
      // console.log("Deleted submission", res);
      if (res.error === false) {
        this.router.navigate([this.sessionStorageService.getItem('previous_url') || '/authenticated/associates/business/case-submissions']);
        this.routeBack();
        this._notification.create('success', 'Success! Selected submission record is deleted from the server.', { Position: 'top', Style: 'bar', Duration: 2000 });
      }
    });
  }

  // updateValue(key, value) {
  //   this.submissionUpdate = true;
  //   var data:any = {}
  //   data[key] = value;
  //   this.assSubmissionService.updateClient(this.client.uuid, data).subscribe((res) => {
  //     this.updateModel(res.data);
  //     this.router.navigate(['/authenticated/associates/relationships/clients/' + this.client.uuid]);
  //     this._notification.create('success', 'Success! Client Profile updated successfully.', { Position: 'top', Style: 'bar', Duration: 2000 });
  //   });
  // }
}