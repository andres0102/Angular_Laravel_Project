/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Component, OnInit, OnDestroy } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { Subscription } from 'rxjs';
import { FormBuilder } from '@angular/forms';
import { PageService } from '@auth/services/page';
import { ApiService, AuthService } from '@app/@core/services';
import { AssociatesCaseSubmissionsService } from '@app/@shared/services/associates/case-submissions/case-submissions.service';

import * as Fuse from 'fuse.js';

@Component({
  selector: 'associates-case-submissions-main',
  templateUrl: './case-submissions-main.component.html',
  styleUrls: ['./case-submissions-main.component.scss']
})
export class AssociatesCaseSubmissionsMainComponent implements OnInit, OnDestroy {
  private providersSub: Subscription;
  private _createdSwalSubscriptions: Subscription;
  private page_permission = 'associate_submissions_view';

  submissions;
  dataRaw;
  dataLoaded: boolean = false;
  providers;
  providersFilterCount;
  providerSelected: string = null;
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
    public assSubmissionsService: AssociatesCaseSubmissionsService,
    private apiService: ApiService,
    private authService: AuthService,
    private pageService: PageService,
    private route: ActivatedRoute,
    private router: Router) {

    this._createdSwalSubscriptions = this.assSubmissionsService.createdSwal.subscribe(() => this.getSubmissions());

    // Take an initial snapshot of queryParams and apply the values
    let query = this.route.snapshot.queryParams;
    if (query['status']) this.statusSelected = query['status'];
    if (query['f']) this.providerSelected = query['f'];
    if (query['s']) this.searchTerm = query['s'];
    if (this.searchTerm) this.searchActive = true;
  }

  ngOnInit() {
    this.getSelections();
  }

  ngOnDestroy() {
    if (this.providersSub) this.providersSub.unsubscribe();
    if (this._createdSwalSubscriptions) this._createdSwalSubscriptions.unsubscribe();
  }

  getSelections() {
    this.providersSub = this.apiService.post('selections', {
      'lists': ['lfa-provider']
    }).subscribe(res => {
      let data = res['data'];
      this.providers = data['lfa-provider'];

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
    if (this.statusSelected) _submissions = this.filterByStatus(_submissions, this.statusSelected);
    // Filter by Providers
    if (this.providerSelected) _submissions = this.filterByProvider(_submissions, this.providerSelected);
    this.submissions = _submissions;

    this.updateFilterCounts();
  }

  updateFilterCounts() {
    // Update Filter Counts by Providers
    let providerData = this.dataRaw;
    // Filter by Selected Status
    if (this.statusSelected) providerData = this.filterByStatus(providerData, this.statusSelected);
    // Filter by Selected Search Term
    if (this.searchTerm) providerData = this.filterBySearch(providerData, this.searchTerm);
    // Update Filter Counts by Unique Providers Slugs
    this.providersFilterCount = [];
    this.providersFilterCount['all'] = providerData.length;
    this.providers.forEach((_provider) => {
      this.providersFilterCount[_provider.slug] = this.filterByProvider(providerData, _provider.alias).length;
    });

    // Update Filter Counts by Status
    let statusData = this.dataRaw;
    // Filter by Selected Search Term
    if (this.searchTerm) statusData = this.filterBySearch(statusData, this.searchTerm);
    // Filter by Selected Provider
    if (this.providerSelected) statusData = this.filterByProvider(statusData, this.providerSelected);
    // Update Filter Counts by Unique Types
    this.statusFilterCount = [];
    this.statusFilterCount['draft'] = this.filterByStatus(statusData, 'draft').length;
    this.statusFilterCount['pending'] = this.filterByStatus(statusData, 'pending').length;
    this.statusFilterCount['rejected'] = this.filterByStatus(statusData, 'rejected').length;

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
    if (status) {
      switch (status) {
        case 'pending':
          return dataset.filter(row => [
            'pending-approval',
            'pending-screening',
            'pending-verification',
            'pending-submission'].includes(row.status_slug));
          break;
        default:
          return dataset.filter(row => row.status_slug == status);
      }
    }
    return dataset;
  }

  filterByProvider(dataset, provider = null) {
    if (provider) return dataset.filter(row => (row.providers) ? row.providers.includes(provider) : false);
    return dataset;
  }

  searchChange(blur = false) {
    if (!this.searchTerm) this.searchTerm = null;
    if (blur && !this.searchTerm) this.searchActive = false;
    this.router.navigate([], { relativeTo: this.route, queryParams: {s: this.searchTerm}, queryParamsHandling: 'merge', replaceUrl: true });
    this.fuseResults();
  }

  filterChange() {
    if (!this.providerSelected) this.providerSelected = null;
    this.router.navigate([], { relativeTo: this.route, queryParams: {f: this.providerSelected}, queryParamsHandling: 'merge', replaceUrl: true });
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
    this.providerSelected = null;
    this.fuseResults();
  }

  getDatatableHeight(data, rowHeight) {
    let max_height = 10 * rowHeight;
    let temp_height = (data.length || 1) * rowHeight;
    return (temp_height > max_height) ? max_height : temp_height;
  }
}