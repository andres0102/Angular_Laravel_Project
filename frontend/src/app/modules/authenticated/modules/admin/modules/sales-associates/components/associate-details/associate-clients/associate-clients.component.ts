/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Component, OnInit, OnDestroy } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { Subscription } from 'rxjs';

import { Associate } from '@app/models';
import { ApiService, TitleService } from '@app/@core/services';
import { PageService } from '@auth/services/page';
import { AdminSalesAssociatesService } from '@app/@shared/services/admin/sales-associates/sales-associates.service';

import * as Fuse from 'fuse.js';

@Component({
  selector: 'admin-associate-clients',
  templateUrl: './associate-clients.component.html',
  styleUrls: ['./associate-clients.component.scss'],
})
export class AdminAssociateClientsComponent implements OnInit, OnDestroy {
  private selectionsSub: Subscription;
  private clientsSub: Subscription;
  associate: Associate;
  clients;
  dataLoaded: boolean = false;
  dataRaw;

  filtersLoaded: boolean = false;
  client_sources;
  sourceFilterCount;
  sourceSelected: string = null;

  source_map;

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
      'display_name',
      'aliases',
      'business.name',
      'business.uen',
      'personal.full_name',
      'personal.alias',
      'personal.chinese_name',
      'personal.nric_no',
      'personal.fin_no',
      'personal.passport_no',
      'personal.job_title',
      'personal.company_name',
      'personal.contact_information.email',
      'personal.contact_information.home_no',
      'personal.contact_information.mobile_no',
      'personal.contact_information.fax_no',
    ],
  };

  messages = {
    emptyMessage: 'No Client Records to display.',
    totalMessage: 'Client Records'
  };

  constructor (private route: ActivatedRoute,
               private router: Router,
               private apiService: ApiService,
               private titleService: TitleService,
               private pageService: PageService,
               private adminSalesAssocService: AdminSalesAssociatesService) {
    this.associate = this.route.parent.snapshot.data.associate;
    if (this.searchTerm) this.searchActive = true;
  }

  ngOnInit() {
    this.getSelections();
    this.getClients();

    // Update page title
    this.titleService.manualTitle(this.associate.personal.full_name + ' | Associate Clients');
  }

  ngOnDestroy() {
    if (this.selectionsSub) this.selectionsSub.unsubscribe();
    if (this.clientsSub) this.clientsSub.unsubscribe();
  }

  getSelections() {
    this.selectionsSub = this.apiService.post('selections', {
      'lists': ['lfa-client-source']
    }).subscribe(res => {
      let data = res['data'];
      this.client_sources = data['lfa-client-source'];
      this.processSources();
    });
  }

  processSources() {
    // Map Client Sources as a new Object for Inline Autocomplete
    var client_sources: any = {};
    this.client_sources.forEach((option) => { client_sources[option.slug] = option.title; });
    this.source_map = new Map(Object.entries(client_sources));
  }

  getClients() {
    if (this.associate) {
      this.dataLoaded = false;
      this.clients = [];
      this.dataRaw = [];
      // this.dataCategory = [];
      this.clientsSub = this.adminSalesAssocService.getClients(this.associate.uuid).subscribe(res => {
        let data = res.data;
        this.clients = data;
        this.dataRaw = data;
        this.fuseResults();
      });
    }
  }

  fuseResults() {
    let _clients = this.dataRaw;
    // Filter by Search Term
    if (this.searchTerm) _clients = this.filterBySearch(_clients, this.searchTerm);
    // Filter by Client Source
    if (this.sourceSelected) _clients = this.filterBySource(_clients, this.sourceSelected);
    this.clients = _clients;

    if (this.client_sources) this.updateFilterCounts();

    // Reflect Changes
    this.dataLoaded = true;
  }

  updateFilterCounts() {
    // Update Filter Counts by Source
    let sourceData = this.dataRaw;
    // Filter by Selected Search Term
    if (this.searchTerm) sourceData = this.filterBySearch(sourceData, this.searchTerm);
    // Update Filter Counts by Unique Source Slugs
    this.sourceFilterCount = [];
    this.sourceFilterCount['all'] = sourceData.length;
    this.client_sources.forEach((_source) => {
      this.sourceFilterCount[_source.slug] = this.filterBySource(sourceData, _source.slug).length;
    });

    this.filtersLoaded = true;
  }

  filterBySearch(dataset, term = null) {
    if (term) {
      this.searchFuse = new Fuse(dataset, this.searchFuseOptions);
      return this.searchFuse.search(term);
    } else {
      return dataset;
    }
  }

  filterBySource(dataset, source = null) {
    if (source) return dataset.filter(row => row.source_slug == source);
    return dataset;
  }

  searchChange(blur = false) {
    if (!this.searchTerm) this.searchTerm = null;
    if (blur && !this.searchTerm) this.searchActive = false;
    this.router.navigate([], { relativeTo: this.route, queryParams: {s: this.searchTerm}, queryParamsHandling: 'merge', replaceUrl: true });
    this.fuseResults();
  }

  filterChange() {
    if (!this.sourceSelected) this.sourceSelected = null;
    this.router.navigate([], { relativeTo: this.route, queryParams: {f: this.sourceSelected}, queryParamsHandling: 'merge', replaceUrl: true });
    this.fuseResults();
  }

  clearFilters() {
    this.searchTerm = null;
    this.sourceSelected = null;
    this.fuseResults();
  }

  getDatatableHeight(data, rowHeight) {
    let max_height = 10 * rowHeight;
    let temp_height = (data.length || 1) * rowHeight;
    return (temp_height > max_height) ? max_height : temp_height;
  }
}
