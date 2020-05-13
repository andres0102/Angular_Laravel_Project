/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Component, OnInit, OnDestroy } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Subscription } from 'rxjs';

import { Client } from '@app/models';
import { ApiService, TitleService } from '@app/@core/services';
import { PageService } from '@auth/services/page';
import { AssociatesClientRelationshipsService } from '@app/@shared/services/associates/client-relationships/client-relationships.service';

import * as Fuse from 'fuse.js';

@Component({
  selector: 'associates-client-policies',
  templateUrl: './client-policies.component.html',
  styleUrls: ['./client-policies.component.scss'],
})
export class AssociatesClientPoliciesComponent implements OnInit, OnDestroy {
  private selectionsSub: Subscription;
  private policiesSub: Subscription;
  client: Client;
  policies;
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
  tableMessages = {
    emptyMessage: 'No Policy Records to display.',
    totalMessage: 'Policy Records'
  };

  constructor (private route: ActivatedRoute,
               private apiService: ApiService,
               private titleService: TitleService,
               private pageService: PageService,
               private assClientService: AssociatesClientRelationshipsService) {
    this.client = this.route.parent.snapshot.data.client;
    if (this.searchTerm) this.searchActive = true;
  }

  ngOnInit() {
    this.getSelections();

    // Update page title
    this.titleService.manualTitle(this.client.display_name + ' | Client Policies');
  }

  ngOnDestroy() {
    if (this.selectionsSub) this.selectionsSub.unsubscribe();
    if (this.policiesSub) this.policiesSub.unsubscribe();
  }

  getSelections() {
    this.selectionsSub = this.apiService.post('selections', {
      'lists': ['lfa-provider']
    }).subscribe(res => {
      let data = res['data'];
      this.providers = data['lfa-provider'];
      this.getPolicies();
    });
  }

  getPolicies() {
    if (this.client) {
      this.dataLoaded = false;
      this.policies = [];
      this.dataRaw = [];
      // this.dataCategory = [];
      this.policiesSub = this.assClientService.getPolicies(this.client.uuid).subscribe(res => {
        let data = res.data;
        this.policies = data;
        this.dataRaw = data;
        this.fuseResults();
      });
    }
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

  getDatatableHeight(data, rowHeight) {
    let max_height = 10 * rowHeight;
    let temp_height = (data.length || 1) * rowHeight;
    return (temp_height > max_height) ? max_height : temp_height;
  }
}
