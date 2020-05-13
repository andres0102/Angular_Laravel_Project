/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Component, OnInit, OnDestroy } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { Subscription } from 'rxjs';
import { PageService } from '@auth/services/page';
import { ApiService, AuthService } from '@app/@core/services';
import { AssociatesClientPoliciesService } from '@app/@shared/services/associates/client-policies/client-policies.service';

import * as Fuse from 'fuse.js';

@Component({
  selector: 'associates-client-policies-main',
  templateUrl: './client-policies-main.component.html',
  styleUrls: ['./client-policies-main.component.scss']
})
export class AssociatesClientPoliciesMainComponent implements OnInit, OnDestroy {
  private providersSub: Subscription;
  page_permission = 'associate_policies_view';

  policies;
  dataRaw;
  dataLoaded: boolean = false;
  providers;
  typeFilterCount;
  providersFilterCount;
  providerSelected: string = null;

  searchActive = false;
  searchTerm = null;
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
      'policy_no',
      'transactions.transaction_no',
      'transactions.transaction_code',
      'transactions.product_code',
      'transactions.product_type',
      'transactions.product_name',
      'transactions.component_code',
      'transactions.component_name',
      'client.name',
      'life_assured.name',
      'provider.name',
      'provider.alias',
      'date_inception'
    ],
  };

  messages = {
    emptyMessage: 'No Policy Records to display.',
    totalMessage: 'Policy Records'
  };

  constructor (
    private assPolicyService: AssociatesClientPoliciesService,
    private apiService: ApiService,
    private authService: AuthService,
    private pageService: PageService,
    private route: ActivatedRoute,
    private router: Router) {

    // Take an initial snapshot of queryParams and apply the values
    let query = this.route.snapshot.queryParams;
    if (query['f']) this.providerSelected = query['f'];
    if (query['s']) this.searchTerm = query['s'];
    if (this.searchTerm) this.searchActive = true;
  }

  ngOnInit() {
    this.getSelections();
  }

  ngOnDestroy() {
    if (this.providersSub) this.providersSub.unsubscribe();
  }

  getSelections() {
    this.apiService.post('selections', {
      'lists': ['lfa-provider']
    }).subscribe(res => {
      let data = res['data'];
      this.providers = data['lfa-provider'];

      this.getPolicies();
    });
  }

  getPolicies() {
    this.dataLoaded = false;
    this.policies = [];
    this.dataRaw = [];
    this.assPolicyService.getPolicies().subscribe(res => {
      let data = res['data'];
      this.dataRaw = data;
      this.policies = data;
      this.fuseResults();
    });
  }

  fuseResults() {
    let _policies = this.dataRaw;
    // Filter by Search Term
    if (this.searchTerm) _policies = this.filterBySearch(_policies, this.searchTerm);
    // Filter by Provider
    if (this.providerSelected) _policies = this.filterByProvider(_policies, this.providerSelected);
    this.policies = _policies;

    this.updateFilterCounts();
  }

  updateFilterCounts() {
    // Update Filter Counts by Providers
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
    if (provider) return dataset.filter(row => row.provider.alias == provider);
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

  clearFilters() {
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