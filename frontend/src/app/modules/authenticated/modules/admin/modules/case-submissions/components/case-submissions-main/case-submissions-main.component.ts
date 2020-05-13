/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Component, OnInit, OnDestroy, ViewChild, ElementRef, AfterViewInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { Subscription, fromEvent } from 'rxjs';
import { debounceTime, map, distinctUntilChanged, filter } from "rxjs/operators";
import { FormBuilder } from '@angular/forms';
import { PageService } from '@auth/services/page';
import { ApiService, AuthService } from '@app/@core/services';
import { AdminCaseSubmissionsService } from '@app/@shared/services/admin/case-submissions/case-submissions.service';
import { MessageService } from '@app/@shared/components/message/message.service';

import * as Fuse from 'fuse.js';

@Component({
  selector: 'admin-case-submissions-main',
  templateUrl: './case-submissions-main.component.html',
  styleUrls: ['./case-submissions-main.component.scss']
})
export class AdminCaseSubmissionsMainComponent implements OnInit, OnDestroy, AfterViewInit {
  private providersSub: Subscription;
  private restoreSub: Subscription;
  private deleteSub: Subscription;
  private page_permission = 'submissions_mgmt_view';
  @ViewChild('searchInput', {static: false}) searchInput: ElementRef;

  submissions;
  dataRaw;
  dataLoaded: boolean = false;
  associates;
  associatesFilterCount;
  associateSelected: string = null;
  statusFilterCount;
  statusSelected: string = null;

  searchActive = false;
  searchTerm: string = null;
  searchFuse;
  searchFuseOptions = {
    'shouldSort': true,
    'findAllMatches': true,
    // includeMatches: true,
    'threshold': 0.3,
    'location': 0,
    'distance': 100,
    'maxPatternLength': 32,
    'keys': [
      'client.display_name',
      'cases.provider',
      'cases.submission_category',
      'cases.product_name',
      'cases.option_name',
      'cases.product_category',
      'cases.payment_mode',
      'status',
      'status_desc',
      'date_submission'
    ],
  };

  messages = {
    emptyMessage: 'No Submission Records to display.',
    totalMessage: 'Submission Records'
  };

  constructor (
    public assSubmissionsService: AdminCaseSubmissionsService,
    private apiService: ApiService,
    private authService: AuthService,
    private pageService: PageService,
    private route: ActivatedRoute,
    private _notification: MessageService,
    private router: Router) {

    // Take an initial snapshot of queryParams and apply the values
    let query = this.route.snapshot.queryParams;
    if (query['status']) this.statusSelected = query['status'];
    if (query['f']) this.associateSelected = query['f'];
    if (query['s']) this.searchTerm = query['s'];
    if (this.searchTerm) this.searchActive = true;
  }

  ngOnInit() {
    this.getSelections();
  }

  ngAfterViewInit() {
    fromEvent(this.searchInput.nativeElement, 'keyup').pipe(
      // get value
      map((event: any) => {
        return event.target.value;
      })
      // if character length greater then 2
      ,filter(res => res.length > 2)
      // Time in milliseconds between key events
      ,debounceTime(300)
      // If previous query is diffent from current
      ,distinctUntilChanged()
      // subscription for response
    ).subscribe((text: string) => {
      this.searchChange();
    });
  }

  ngOnDestroy() {
    if (this.providersSub) this.providersSub.unsubscribe();
    if (this.restoreSub) this.restoreSub.unsubscribe();
    if (this.deleteSub) this.deleteSub.unsubscribe();
  }

  getSelections() {
    this.providersSub = this.apiService.post('selections', {
      'lists': ['lfa-associates']
    }).subscribe(res => {
      let data = res['data'];
      this.associates = data['lfa-associates'];

      this.getSubmissions();
    });
  }

  getSubmissions() {
    this.dataLoaded = false;
    this.submissions = [];
    this.dataRaw = [];
    this.assSubmissionsService.getSubmissions().subscribe(res => {
      let data = res.data;
      this.dataRaw = data;
      this.submissions = data;
      this.fuseResults();
    });
  }

  fuseResults() {
    let _submissions = this.dataRaw;
    // Filter by Search Term
    if (this.searchTerm) _submissions = this.filterBySearch(_submissions, this.searchTerm);
    // Filter by Submission Status
    _submissions = this.filterByStatus(_submissions, this.statusSelected);
    // Filter by Providers
    if (this.associateSelected) _submissions = this.filterByAssociate(_submissions, this.associateSelected);
    this.submissions = _submissions;

    this.updateFilterCounts();
  }

  updateFilterCounts() {
    // Update Filter Counts by Providers
    let associateData = this.dataRaw;
    // Filter by Selected Status
    associateData = this.filterByStatus(associateData, this.statusSelected);
    // Filter by Selected Search Term
    if (this.searchTerm) associateData = this.filterBySearch(associateData, this.searchTerm);
    // Update Filter Counts by Unique Providers Slugs
    this.associatesFilterCount = [];
    this.associatesFilterCount['all'] = associateData.length;
    this.associates.forEach((_associate) => {
      this.associatesFilterCount[_associate.uuid] = this.filterByAssociate(associateData, _associate.uuid).length;
    });

    // Update Filter Counts by Status
    let statusData = this.dataRaw;
    // Filter by Selected Search Term
    if (this.searchTerm) statusData = this.filterBySearch(statusData, this.searchTerm);
    // Filter by Selected Provider
    if (this.associateSelected) statusData = this.filterByAssociate(statusData, this.associateSelected);
    // Update Filter Counts by Unique Types
    this.statusFilterCount = [];
    this.statusFilterCount['pending'] = this.filterByStatus(statusData, 'pending').length;
    this.statusFilterCount['rejected'] = this.filterByStatus(statusData, 'rejected').length;
    this.statusFilterCount['deleted'] = this.filterByStatus(statusData, 'deleted').length;

    // Reflect Changes
    this.dataLoaded = true;
  }

  filterBySearch(dataset, term = null) {
    if (term) {
      this.searchFuse = new Fuse(dataset, this.searchFuseOptions);
      return this.searchFuse.search(term);
    } else {
      return dataset;
    }
  }

  filterByStatus(dataset, status = null) {
    if (status == 'deleted') return dataset.filter(row => row['deleted_at'] != null);
    if (status) {
      switch (status) {
        case 'pending':
          return dataset.filter(row => [
            'pending-approval',
            'pending-screening',
            'pending-verification',
            'pending-submission'].includes(row.status_slug) && row['deleted_at'] == null);
          break;
        default:
          return dataset.filter(row => row.status_slug == status && row['deleted_at'] == null);
      }
    }
    return dataset.filter(row => row['deleted_at'] == null);
  }

  filterByAssociate(dataset, associate_uuid = null) {
    if (associate_uuid) return dataset.filter(row => row.associate.uuid == associate_uuid);
    return dataset;
  }

  searchChange(blur = false) {
    if (!this.searchTerm) this.searchTerm = null;
    if (blur && !this.searchTerm) this.searchActive = false;
    this.router.navigate([], { relativeTo: this.route, queryParams: {s: this.searchTerm}, queryParamsHandling: 'merge', replaceUrl: true });
    this.fuseResults();
  }

  filterChange() {
    if (!this.associateSelected) this.associateSelected = null;
    this.router.navigate([], { relativeTo: this.route, queryParams: {f: this.associateSelected}, queryParamsHandling: 'merge', replaceUrl: true });
    this.fuseResults();
  }

  statusChange(status) {
    this.router.navigate([], { relativeTo: this.route, queryParams: {status: status}, queryParamsHandling: 'merge', replaceUrl: true });
    this.statusSelected = status;
    this.fuseResults();
  }

  clearFilters() {
    this.statusSelected = null;
    this.searchTerm = null;
    this.associateSelected = null;
    this.fuseResults();
  }

  rowClass = (row) => {
    return {
      'bg-light-red': row.deleted_at !== null
    };
  }

  getDatatableHeight(data, rowHeight) {
    let max_height = 10 * rowHeight;
    let temp_height = (data.length || 1) * rowHeight;
    return (temp_height > max_height) ? max_height : temp_height;
  }

  restoreSubmission(uuid) {
    this.restoreSub = this.apiService.post('admin/submissions/' + uuid + '/restore').subscribe((res) => {
      console.log("Restored submission", res);
      if (res.error === false) {
        this.getSubmissions();
        this._notification.create('success', 'Success! Deleted submission record is restored.', { Position: 'top', Style: 'bar', Duration: 2000 });
      }
    });
  }

  deleteSubmission(uuid) {
    this.deleteSub = this.apiService.delete('admin/submissions/' + uuid).subscribe((res) => {
      console.log("Deleted submission", res);
      if (res.error === false) {
        this.getSubmissions();
        this._notification.create('success', 'Success! Selected submission record is deleted from the server.', { Position: 'top', Style: 'bar', Duration: 2000 });
      }
    });
  }
}