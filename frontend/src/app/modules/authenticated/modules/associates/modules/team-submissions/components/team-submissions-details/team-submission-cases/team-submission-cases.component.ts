/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Component, OnInit, OnDestroy } from '@angular/core';
import { ActivatedRoute, Router, NavigationEnd } from '@angular/router';
import { Subscription } from 'rxjs';
import { saveAs } from 'file-saver';

import { Submission } from '@app/models';
import { ApiService, TitleService } from '@app/@core/services';
import { PageService } from '@auth/services/page';
import { AssociatesCaseSubmissionsService } from '@app/@shared/services/associates/case-submissions/case-submissions.service';
import { MessageService } from '@app/@shared/components/message/message.service';

@Component({
  selector: 'associates-team-submission-cases',
  templateUrl: './team-submission-cases.component.html',
  styleUrls: ['./team-submission-cases.component.scss'],
})
export class AssociatesTeamSubmissionCasesComponent implements OnInit, OnDestroy {
  private selectionsSub: Subscription;
  private caseSub: Subscription;
  private uploadSub: Subscription;
  private deleteSub: Subscription;
  private routeSub: Subscription;
  parentRefSub: Subscription;

  submission;
  cases;
  dataLoaded: boolean = false;
  processing: boolean = false;

  constructor (private route: ActivatedRoute,
               private router: Router,
               private _notification: MessageService,
               private apiService: ApiService,
               private pageService: PageService,
               private titleService: TitleService,
               private assSubmissionService: AssociatesCaseSubmissionsService) {
    this.submission = this.route.parent.snapshot.data.submission;
    this.parentRefSub = this.pageService.parentRefresh.subscribe(() => this.getCases());
    // if (this.submission.case_count == 0) this.router.navigate(['../'], { relativeTo: this.route, replaceUrl: true });

    this.router.routeReuseStrategy.shouldReuseRoute = function () {
      return false;
    };
    this.routeSub = this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd) {
        // Trick the Router into believing it's last link wasn't previously loaded
        this.router.navigated = false;
      }
    });
  }

  ngOnInit() {
    this.getCases();
  }

  ngOnDestroy() {
    if (this.caseSub) this.caseSub.unsubscribe();
    if (this.parentRefSub) this.parentRefSub.unsubscribe();
    if (this.uploadSub) this.uploadSub.unsubscribe();
    if (this.deleteSub) this.deleteSub.unsubscribe();
    if (this.routeSub) this.routeSub.unsubscribe();
  }

  getCases() {
    if (this.submission) {
      this.dataLoaded = false;
      this.caseSub = this.assSubmissionService.getCases(this.submission.uuid).subscribe(res => {
        let data = res.data;
        // console.log('cases', data);
        this.cases = data;
        this.dataLoaded = true;
      });
    }
  }

  downloadMedia(file_id, file_name, case_uuid) {
    this.apiService.download('associates/submissions/' + this.submission.uuid + '/cases/' + case_uuid + '/documents/' + file_id).toPromise()
      .then(blob => {
        saveAs(blob, file_name);
      });
  }

  downloadFile(case_uuid, file_type, file_name) {
    this.apiService.download('associates/portal/download/' + case_uuid + '/' + file_type).toPromise()
      .then(blob => {
        saveAs(blob, file_name);
      });
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

  removeCase(case_uuid) {
    this.processing = true;
    this.deleteSub = this.apiService.delete('associates/submissions/' + this.submission.uuid + '/cases/' + case_uuid).subscribe((res) => {
      // console.log("Deleted case", res);
      if (res.error === false) {
        let data = res.data;
        this.cases = data;
        this.pageService.triggerParentRefresh(true);
        this._notification.create('success', 'Success! Selected case record is deleted from the server.', { Position: 'top', Style: 'bar', Duration: 2000 });
        this.processing = false;
      }
    });
  }

  onDropzoneSelectCaseDocuments(event, case_uuid) {
    this.processing = true;
    // console.log('DZ selected', event);
    let files:File[] = [];
    files.push(...event.addedFiles);

    let form_data = new FormData();
    for (var i = 0; i < files.length; i++) {
      form_data.append("uploads[]", files[i], files[i].name);
    }

    this.uploadSub = this.apiService.upload('associates/submissions/' + this.submission.uuid + '/cases/' + case_uuid + '/documents', form_data).subscribe((res) => {
      // console.log("Uploaded files", res);
      if (res.error === false) {
        let data = res.data;
        this.cases = data;
        this.pageService.triggerParentRefresh(true);
        this._notification.create('success', 'Success! Documents uploaded securely to the server.', { Position: 'top', Style: 'bar', Duration: 2000 });
        this.processing = false;
      }
    });
  }

  removeMedia(media_id, case_uuid) {
    this.processing = true;
    this.deleteSub = this.apiService.delete('associates/submissions/' + this.submission.uuid + '/cases/' + case_uuid + '/documents/' + media_id).subscribe((res) => {
      // console.log("Deleted files", res);
      if (res.error === false) {
        let data = res.data;
        this.cases = data;
        this.pageService.triggerParentRefresh(true);
        this._notification.create('success', 'Success! Selected document is deleted from the server.', { Position: 'top', Style: 'bar', Duration: 2000 });
        this.processing = false;
      }
    });
  }
}
