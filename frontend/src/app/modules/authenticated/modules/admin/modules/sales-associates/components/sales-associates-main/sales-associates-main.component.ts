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
import { AdminSalesAssociatesService } from '@app/@shared/services/admin/sales-associates/sales-associates.service';
import { MessageService } from '@app/@shared/components/message/message.service';

import * as Fuse from 'fuse.js';

@Component({
  selector: 'admin-sales-associates-main',
  templateUrl: './sales-associates-main.component.html',
  styleUrls: ['./sales-associates-main.component.scss']
})
export class AdminSalesAssociatesMainComponent implements OnInit, OnDestroy {
  private associateSub: Subscription;
  private _createdSwalSubscriptions: Subscription;
  private page_permission = 'associates_mgmt_view';

  associates;
  dataRaw;
  dataLoaded: boolean = false;
  filtersLoaded: boolean = false;
  dataUpdating: boolean = false;
  typeFilterCount;
  statusFilterCount;
  statusSelected: string = null;
  associateTypeSelected: string = null;

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
      'personal.full_name',
      'designation',
      'first_day',
      'last_day',
      'rnf.no',
      'email',
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
    emptyMessage: 'No Associate Records to display.',
    totalMessage: 'Associate Records'
  };

  constructor (
    private adminSalesAssocService: AdminSalesAssociatesService,
    private apiService: ApiService,
    private authService: AuthService,
    private pageService: PageService,
    private route: ActivatedRoute,
    private _notification: MessageService,
    private router: Router) {

    this._createdSwalSubscriptions = this.adminSalesAssocService.createdSwal.subscribe(() => this.getAssociates());

    // Take an initial snapshot of queryParams and apply the values
    let query = this.route.snapshot.queryParams;
    if (query['type']) this.associateTypeSelected = query['type'];
    if (query['f']) this.statusSelected = query['f'];
    if (query['s']) this.searchTerm = query['s'];
    if (this.searchTerm) this.searchActive = true;

    this.getAssociates();
  }

  ngOnInit() {
  }

  ngOnDestroy() {
    if (this.associateSub) this.associateSub.unsubscribe();
    if (this._createdSwalSubscriptions) this._createdSwalSubscriptions.unsubscribe();
  }

  getAssociates(reset = false) {
    if (reset) {
      this.dataLoaded = false;
      this.filtersLoaded = false;
      this.associates = [];
      this.dataRaw = [];
    } else if (this.adminSalesAssocService.associates) {
      // Get Data from Clients Cache
      this.processAssociates(this.adminSalesAssocService.associates);
    }

    // Get Updated Data
    this.associateSub = this.adminSalesAssocService.getAssociates().subscribe(res => {
      // console.log('Data Updated', res);
      let data = res['data'];
      this.processAssociates(data);
    });
  }

  processAssociates(data) {
    this.dataRaw = data;
    this.associates = data;
    this.fuseResults();
  }

  fuseResults() {
    let _associates = this.dataRaw;
    // Filter by Search Term
    if (this.searchTerm) _associates = this.filterBySearch(_associates, this.searchTerm);
    // Filter by Client Type
    if (this.associateTypeSelected) _associates = this.filterByType(_associates, this.associateTypeSelected);
    // Filter by Client Source
    if (this.statusSelected) _associates = this.filterByStatus(_associates, this.statusSelected);
    this.associates = _associates;

    this.updateFilterCounts();

    // Reflect Changes
    this.dataLoaded = true;
  }

  updateFilterCounts() {
    // Update Filter Counts by Source
    let statusData = this.dataRaw;
    // Filter by Selected Type
    if (this.associateTypeSelected) statusData = this.filterByType(statusData, this.associateTypeSelected);
    // Filter by Selected Search Term
    if (this.searchTerm) statusData = this.filterBySearch(statusData, this.searchTerm);
    // Update Filter Counts by Unique Source Slugs
    this.statusFilterCount = [];
    this.statusFilterCount['all'] = statusData.length;
    this.statusFilterCount['active'] = this.filterByStatus(statusData, 'active').length;
    this.statusFilterCount['inactive'] = this.filterByStatus(statusData, 'inactive').length;

    // Update Filter Counts by Type
    let typeData = this.dataRaw;
    // Filter by Selected Search Term
    if (this.searchTerm) typeData = this.filterBySearch(typeData, this.searchTerm);
    // Filter by Selected Client Source
    if (this.statusSelected) typeData = this.filterByStatus(typeData, this.statusSelected);
    // Update Filter Counts by Unique Types
    this.typeFilterCount = [];
    this.typeFilterCount['all'] = typeData.length;
    this.typeFilterCount['advisors'] = this.filterByType(typeData, 'advisors').length;
    this.typeFilterCount['managers'] = this.filterByType(typeData, 'managers').length;
    this.typeFilterCount['directors'] = this.filterByType(typeData, 'directors').length;

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

  filterByType(dataset, type = null) {
    if (type) {
      return dataset.filter(row => {
        switch (type) {
          case 'advisors':
            return row['is_t1'] === true;
            break;
          case 'managers':
            return row['is_t2'] === true;
            break;
          case 'directors':
            return row['is_t3'] === true;
            break;
        }
      });
    }
    return dataset;
  }

  filterByStatus(dataset, status = null) {
    if (status) {
      return dataset.filter(row => {
        switch (status) {
          case 'active':
            return row['active'] === true;
            break;
          case 'inactive':
            return row['active'] === false;
            break;
        }
      });
    }
    return dataset;
  }

  searchChange(blur = false) {
    if (!this.searchTerm) this.searchTerm = null;
    if (blur && !this.searchTerm) this.searchActive = false;
    this.router.navigate([], { relativeTo: this.route, queryParams: {s: this.searchTerm}, queryParamsHandling: 'merge', replaceUrl: true });
    this.fuseResults();
  }

  filterChange() {
    if (!this.statusSelected) this.statusSelected = null;
    this.router.navigate([], { relativeTo: this.route, queryParams: {f: this.statusSelected}, queryParamsHandling: 'merge', replaceUrl: true });
    this.fuseResults();
  }

  typeChange(type) {
    this.router.navigate([], { relativeTo: this.route, queryParams: {type: type}, queryParamsHandling: 'merge', replaceUrl: true });
    this.associateTypeSelected = type;
    this.fuseResults();
  }

  clearFilters() {
    this.associateTypeSelected = null;
    this.searchTerm = null;
    this.statusSelected = null;
    this.fuseResults();
  }

  rowClass = (row) => {
    return {
      'bg-light-red': row.active !== true,
    };
  }

  getDatatableHeight(data, rowHeight) {
    let max_height = 10 * rowHeight;
    let temp_height = (data.length || 1) * rowHeight;
    return (temp_height > max_height) ? max_height : temp_height;
  }
}