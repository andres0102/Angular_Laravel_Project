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
import { AdminClientRelationshipsService } from '@app/@shared/services/admin/client-relationships/client-relationships.service';
import { MessageService } from '@app/@shared/components/message/message.service';

import * as Fuse from 'fuse.js';

@Component({
  selector: 'admin-client-relationships-main',
  templateUrl: './client-relationships-main.component.html',
  styleUrls: ['./client-relationships-main.component.scss']
})
export class AdminClientRelationshipsMainComponent implements OnInit, OnDestroy, AfterViewInit {
  private clientSourceSub: Subscription;
  private clientSub: Subscription;
  private page_permission = 'clients_mgmt_view';
  @ViewChild('searchInput', {static: false}) searchInput: ElementRef;

  clients;
  dataRaw;
  dataLoaded: boolean = false;
  filtersLoaded: boolean = false;
  dataUpdating: boolean = false;
  client_sources;
  typeFilterCount;
  sourceFilterCount;
  sourceSelected: string = null;
  clientTypeSelected: string = null;

  editing = {};
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

  constructor (
    private assClientService: AdminClientRelationshipsService,
    private apiService: ApiService,
    private authService: AuthService,
    private pageService: PageService,
    private route: ActivatedRoute,
    private _notification: MessageService,
    private router: Router) {

    // Take an initial snapshot of queryParams and apply the values
    let query = this.route.snapshot.queryParams;
    if (query['type']) this.clientTypeSelected = query['type'];
    if (query['f']) this.sourceSelected = query['f'];
    if (query['s']) this.searchTerm = query['s'];
    if (this.searchTerm) this.searchActive = true;

    this.getSelections();
    this.getClients();
  }

  ngOnInit() { }

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
    if (this.clientSourceSub) this.clientSourceSub.unsubscribe();
    if (this.clientSub) this.clientSub.unsubscribe();
  }

  getSelections() {
    if (this.assClientService.client_sources) {
      this.client_sources = this.assClientService.client_sources;
      this.processSources();
    } else {
      this.clientSourceSub = this.apiService.post('selections', {
        'lists': ['lfa-client-source']
      }).subscribe(res => {
        let data = res['data'];
        this.client_sources = data['lfa-client-source'];
        this.processSources();
      });
    }
  }

  processSources() {
    // Map Client Sources as a new Object for Inline Autocomplete
    var client_sources: any = {};
    this.client_sources.forEach((option) => { client_sources[option.slug] = option.title; });
    this.source_map = new Map(Object.entries(client_sources));
  }

  getClients(reset = false) {
    if (reset) {
      this.dataLoaded = false;
      this.filtersLoaded = false;
      this.clients = [];
      this.dataRaw = [];
    } else if (this.assClientService.clients) {
      // Get Data from Clients Cache
      this.processClients(this.assClientService.clients);
    }

    // Get Updated Data
    this.clientSub = this.assClientService.getClients().subscribe(res => {
      // console.log('Data Updated', res);
      let data = res['data'];
      this.processClients(data);
    });
  }

  processClients(data) {
    this.dataRaw = data;
    this.clients = data;
    this.fuseResults();
  }

  fuseResults() {
    let _clients = this.dataRaw;
    // Filter by Search Term
    if (this.searchTerm) _clients = this.filterBySearch(_clients, this.searchTerm);
    // Filter by Client Type
    if (this.clientTypeSelected) _clients = this.filterByType(_clients, this.clientTypeSelected);
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
    // Filter by Selected Type
    if (this.clientTypeSelected) sourceData = this.filterByType(sourceData, this.clientTypeSelected);
    // Filter by Selected Search Term
    if (this.searchTerm) sourceData = this.filterBySearch(sourceData, this.searchTerm);
    // Update Filter Counts by Unique Source Slugs
    this.sourceFilterCount = [];
    this.sourceFilterCount['all'] = sourceData.length;
    this.client_sources.forEach((_source) => {
      this.sourceFilterCount[_source.slug] = this.filterBySource(sourceData, _source.slug).length;
    });

    // Update Filter Counts by Type
    let typeData = this.dataRaw;
    // Filter by Selected Search Term
    if (this.searchTerm) typeData = this.filterBySearch(typeData, this.searchTerm);
    // Filter by Selected Client Source
    if (this.sourceSelected) typeData = this.filterBySource(typeData, this.sourceSelected);
    // Update Filter Counts by Unique Types
    this.typeFilterCount = [];
    this.typeFilterCount['all'] = typeData.length;
    this.typeFilterCount['individual'] = this.filterByType(typeData, 'individual').length;
    this.typeFilterCount['business'] = this.filterByType(typeData, 'business').length;
    this.typeFilterCount['deleted'] = this.filterByType(typeData, 'deleted').length;

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
    if (type == 'deleted') return dataset.filter(row => row['deleted_at'] != null);
    if (type) return dataset.filter(row => row['client_type_slug'] == type);
    return dataset;
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

  typeChange(type) {
    this.router.navigate([], { relativeTo: this.route, queryParams: {type: type}, queryParamsHandling: 'merge', replaceUrl: true });
    this.clientTypeSelected = type;
    this.fuseResults();
  }

  clearFilters() {
    this.clientTypeSelected = null;
    this.searchTerm = null;
    this.sourceSelected = null;
    this.fuseResults();
  }

  scrollToView(evt, cell, index) {
    this.editing = {};
    this.editing[index + '-' + cell] = true;
    const elements = document.getElementsByClassName("row-focused");
    let i = 0
    while (i < elements.length) {
      elements[i].classList.remove('row-focused');
      i++;
    }
    evt.target.closest('.datatable-row-wrapper').classList.add('row-focused');
    index++; // Index starts with 0
    let elem = document.getElementsByClassName('datatable-body')[0];
    let elemRect = elem.getBoundingClientRect();
    let elemCenterX = elemRect['x'] + (elemRect['width'] / 2);
    let target = evt.target.closest('.editable-wrapper');
    let targetRect = target.getBoundingClientRect();
    let targetCenterX = targetRect['x'] + (targetRect['width'] / 2);
    let targetDiff = targetCenterX - elemCenterX;
    // If targetCenterX > elemCenterX == positive number from element-left to center
    // If targetCenterX < elemCenterX == negative number from element-left to center
    elem.scrollTo({
      left: elem.scrollLeft + targetDiff,
      top: (50 * index) - (elemRect['height'] / 2) - 25,
      behavior: 'smooth'
    });
  }

  quitEditing(evt) {
    let element = (evt.source) ? evt.source._elementRef.nativeElement : evt.target ;
    element.closest('.datatable-row-wrapper').classList.remove('row-focused');
  }

  updateValue(evt, cell, rowIndex, uuid) {
    this.dataUpdating = true;
    this.quitEditing(evt);
    this.clients[rowIndex][cell] = evt.value;
    var data:any = {}
    data[cell] = evt.value;
    this.assClientService.updateClient(uuid, data).subscribe((res) => {
      this.dataUpdating = false;
      this.editing = {};
      this._notification.create('success', 'Success! Client Profile updated successfully.', { Position: 'top', Style: 'bar', Duration: 2000 });
    });
  }

  getDatatableHeight(data, rowHeight) {
    let max_height = 10 * rowHeight;
    let temp_height = (data.length || 1) * rowHeight;
    return (temp_height > max_height) ? max_height : temp_height;
  }
}