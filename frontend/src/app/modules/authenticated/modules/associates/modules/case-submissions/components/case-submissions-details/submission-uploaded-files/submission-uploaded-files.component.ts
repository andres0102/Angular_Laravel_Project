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

import { ApiService } from '@app/@core/services';
import { PageService } from '@auth/services';
import { AssociatesCaseSubmissionsService } from '@app/@shared/services/associates/case-submissions/case-submissions.service';
import { MessageService } from '@app/@shared/components/message/message.service';

@Component({
  selector: 'associates-submission-uploaded-files',
  templateUrl: './submission-uploaded-files.component.html',
  styleUrls: ['./submission-uploaded-files.component.scss'],
})
export class AssociatesSubmissionUploadedFilesComponent implements OnInit, OnDestroy {
  private mediaSub: Subscription;
  private uploadSub: Subscription;
  private deleteSub: Subscription;
  private routeSub: Subscription;
  submission;
  media;
  dataLoaded: boolean = false;
  processing: boolean = false;

  upload_types = [
    {'title': 'Identity Documents', 'slug': 'client-identity'},
    {'title': 'Proof of Address', 'slug': 'proof-of-address'},
    {'title': 'Personal Financial Records (PFR)', 'slug': 'pfr'},
    {'title': 'Submission Checklist', 'slug': 'submission-checklist'},
    {'title': 'Other Documents', 'slug': 'other-documents'},
  ];

  constructor (private route: ActivatedRoute,
               private router: Router,
               private _notification: MessageService,
               private apiService: ApiService,
               private pageService: PageService,
               public assSubmissionService: AssociatesCaseSubmissionsService) {
    this.submission = this.route.parent.snapshot.data.submission;

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
    this.getMedia();
  }

  ngOnDestroy() {
    if (this.mediaSub) this.mediaSub.unsubscribe();
    if (this.uploadSub) this.uploadSub.unsubscribe();
    if (this.deleteSub) this.deleteSub.unsubscribe();
    if (this.routeSub) this.routeSub.unsubscribe();
  }

  getMedia() {
    if (this.submission) {
      this.dataLoaded = false;
      this.mediaSub = this.assSubmissionService.getMedia(this.submission.uuid).subscribe(res => {
        let data = res.data;
        // console.log('media', data);
        this.media = data;
        this.dataLoaded = true;
      });
    }
  }

  downloadMedia(file_id, file_name) {
    this.apiService.download('associates/submissions/' + this.submission.uuid + '/media/' + file_id).toPromise()
      .then(blob => {
        saveAs(blob, file_name);
      });
  }

  onDropzoneUpload(event, type) {
    this.processing = true;
    // console.log('DZ selected', event);
    let files:File[] = [];
    files.push(...event.addedFiles);

    let form_data = new FormData();
    for (var i = 0; i < files.length; i++) {
      form_data.append("uploads[]", files[i], files[i].name);
    }

    this.uploadSub = this.apiService.upload('associates/submissions/' + this.submission.uuid + '/media/' + type, form_data).subscribe((res) => {
      // console.log("Uploaded files", res);
      if (res.error === false) {
        let data = res.data;
        this.media = data;
        this.pageService.triggerParentRefresh(true);
        this._notification.create('success', 'Success! Files uploaded securely to the server.', { Position: 'top', Style: 'bar', Duration: 2000 });
        this.processing = false;
      }
    });
  }

  removeMedia(media_id) {
    this.processing = true;
    this.deleteSub = this.apiService.delete('associates/submissions/' + this.submission.uuid + '/media/' + media_id).subscribe((res) => {
      // console.log("Deleted files", res);
      if (res.error === false) {
        let data = res.data;
        this.media = data;
        this.pageService.triggerParentRefresh(true);
        this._notification.create('success', 'Success! Selected file is deleted from the server.', { Position: 'top', Style: 'bar', Duration: 2000 });
        this.processing = false;
      }
    });
  }

  groupBy(items, key) {
    items.reduce((result, item) => ({
        ...result,
        [item[key]]: [
          ...(result[item[key]] || []),
          item,
        ],
      }),
      {},
    );
  }


  mediaByType(type){
    if (!this.media) return [];
    return this.media.filter(item => item.collection_name==type) || [];
  }
}
