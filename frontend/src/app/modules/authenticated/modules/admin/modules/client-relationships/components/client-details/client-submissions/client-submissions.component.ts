/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Component, OnInit, OnDestroy } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { Subscription } from 'rxjs';

import { Client } from '@app/models';
import { ApiService, TitleService, SessionStorageService } from '@app/@core/services';
import { PageService } from '@auth/services/page';
import { AdminClientRelationshipsService } from '@app/@shared/services/admin/client-relationships/client-relationships.service';

import * as Fuse from 'fuse.js';

@Component({
  selector: 'admin-client-submissions',
  templateUrl: './client-submissions.component.html',
  styleUrls: ['./client-submissions.component.scss'],
})
export class AdminClientSubmissionsComponent implements OnInit, OnDestroy {
  private selectionsSub: Subscription;
  private submissionsSub: Subscription;
  client: Client;
  submissions;
  dataLoaded: boolean = false;
  dataRaw;

  providers;
  providersFilterCount;
  providerSelected: string = null;
  searchFuse;
  searchActive: boolean = false;
  searchTerm: string = null;
  searchFuseOptions = {
    'shouldSort': true,
    'findAllMatches': true,
    // includeMatches: true,
    'threshold': 0.3,
    'location': 0,
    'distance': 100,
    'maxPatternLength': 32,
    'keys': [
      'client.name',
      'cases.product.provider',
      'cases.submission.category',
      'cases.product.name',
      'cases.product.option',
      'cases.product.category',
      'cases.product.riders',
      'status_desc',
      'date_submission'
    ],
  };
  tableMessages = {
    emptyMessage: 'No Submission Records to display.',
    totalMessage: 'Submission Records'
  };

  constructor (private route: ActivatedRoute,
               private router: Router,
               private apiService: ApiService,
               private titleService: TitleService,
               private sessionStorageService: SessionStorageService,
               private pageService: PageService,
               private assClientService: AdminClientRelationshipsService) {
    this.client = this.route.parent.snapshot.data.client;
    if (this.searchTerm) this.searchActive = true;
  }

  ngOnInit() {
    this.getSelections();

    // Update page title
    this.titleService.manualTitle(this.client.display_name + ' | Client Submissions');
  }

  ngOnDestroy() {
    if (this.selectionsSub) this.selectionsSub.unsubscribe();
    if (this.submissionsSub) this.submissionsSub.unsubscribe();
  }

  getSelections() {
    this.selectionsSub = this.apiService.post('selections', {
      'lists': ['lfa-provider']
    }).subscribe(res => {
      let data = res['data'];
      this.providers = data['lfa-provider'];
      this.getSubmissions();
    });
  }

  getSubmissions() {
    if (this.client) {
      this.dataLoaded = false;
      this.submissions = [];
      this.dataRaw = [];
      // this.dataCategory = [];
      this.submissionsSub = this.assClientService.getSubmissions(this.client.uuid).subscribe(res => {
        let data = res.data;
        this.submissions = data;
        this.dataRaw = data;
        this.fuseResults();
      });
    }
  }

  fuseResults() {
    let _submissions = this.dataRaw;
    // Filter by Search Term
    if (this.searchTerm) _submissions = this.filterBySearch(_submissions, this.searchTerm);
    // Filter by Provider
    if (this.providerSelected) _submissions = this.filterByProvider(_submissions, this.providerSelected);
    this.submissions = _submissions;

    this.updateFilterCounts();
  }

  updateFilterCounts() {
    // Update Filter Counts by Source
    let providerData = this.dataRaw;
    // Filter by Selected Search Term
    if (this.searchTerm) providerData = this.filterBySearch(providerData, this.searchTerm);
    // Update Filter Counts by Unique Providers Slugs
    this.providersFilterCount = [];
    this.providersFilterCount['all'] = providerData.length;
    this.providers.forEach((_provider) => {
      this.providersFilterCount[_provider.slug] = this.filterByProvider(providerData, _provider.alias).length;
    });

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

  filterByProvider(dataset, provider = null) {
    if (provider) return dataset.filter(row => row.providers && row.providers.includes(provider));
    return dataset;
  }

  searchChange(blur = false) {
    if (!this.searchTerm) this.searchTerm = null;
    if (blur && !this.searchTerm) this.searchActive = false;
    this.fuseResults();
  }

  filterChange() {
    if (!this.providerSelected) this.providerSelected = null;
    this.fuseResults();
  }

  routeStore() {
    this.sessionStorageService.setItem('previous_title', 'Client Submissions Records');
    this.sessionStorageService.setItem('previous_url', this.router.url);
  }

  getDatatableHeight(data, rowHeight) {
    let max_height = 10 * rowHeight;
    let temp_height = (data.length || 1) * rowHeight;
    return (temp_height > max_height) ? max_height : temp_height;
  }
}
