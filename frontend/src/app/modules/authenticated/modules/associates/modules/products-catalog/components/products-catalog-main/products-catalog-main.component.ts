/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Component, OnInit, OnDestroy, ViewChild } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { Subscription } from 'rxjs';
import { FormBuilder } from '@angular/forms';
import { PageService } from '@auth/services/page';
import { ApiService, AuthService } from '@app/@core/services';
import { AssociatesProductsCatalogService } from '@app/@shared/services/associates/products-catalog/products-catalog.service';
import { DatatableComponent } from '@swimlane/ngx-datatable';

import * as Fuse from 'fuse.js';

@Component({
  selector: 'associates-products-catalog-main',
  templateUrl: './products-catalog-main.component.html',
  styleUrls: ['./products-catalog-main.component.scss']
})
export class AssociatesProductsCatalogMainComponent implements OnInit, OnDestroy {
  @ViewChild('seriesDatatable', {static: false}) seriesDatatable: DatatableComponent;
  private providersSub: Subscription;

  products;
  productsRaw;
  options;
  optionsRaw;
  dataLoaded: boolean = false;
  providers;
  providersFilterCount;
  providerSelected: string = null;
  seriesFilterCount;
  seriesSelected: string = null;
  series;
  optionSelected: string = null;

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
      'name',
      'slug',
      'description',
      'provider.name',
      'provider.alias',
    ],
  };

  messages = {
    emptyMessage: 'No Product Series to display.',
    totalMessage: 'Product Series'
  };

  constructor (
    private assProductsService: AssociatesProductsCatalogService,
    private apiService: ApiService,
    private authService: AuthService,
    private pageService: PageService,
    private route: ActivatedRoute,
    private router: Router) {
    // Take an initial snapshot of queryParams and apply the values
    let query = this.route.snapshot.queryParams;
    if (query['series']) this.seriesSelected = query['series'];
    if (query['f']) this.providerSelected = query['f'];
    if (query['s']) this.searchTerm = query['s'];
    if (this.searchTerm) this.searchActive = true;
  }

  ngOnInit() {
    if (this.seriesSelected) this.getOptions();
    else this.getSelections();
  }

  ngOnDestroy() {
    if (this.providersSub) this.providersSub.unsubscribe();
  }

  getSelections() {
    if (!this.providers) {
      this.providersSub = this.apiService.post('selections', {
        'lists': ['lfa-provider']
      }).subscribe(res => {
        let data = res['data'];
        this.providers = data['lfa-provider'];
        this.getProducts();
      });
    } else this.getProducts();
  }

  getProducts(refresh = false) {
    this.dataLoaded = false;
    if (!this.products || refresh) {
      this.assProductsService.getProducts().subscribe(res => {
        this.productsRaw = res.data;
        this.products = res.data;
        this.fuseResults();
      });
    } else this.fuseResults();
  }

  getOptions() {
    this.dataLoaded = false;
    this.options = [];
    this.optionsRaw = [];
    this.assProductsService.getProducts(this.seriesSelected).subscribe(res => {
      this.series = res.series;
      this.optionsRaw = res.data;
      this.options = res.data;
      this.dataLoaded = true;
    });
  }

  fuseResults() {
    let _products = this.productsRaw;
    // Filter by Search Term
    if (this.searchTerm) _products = this.filterBySearch(_products, this.searchTerm);
    // Filter by Providers
    if (this.providerSelected) _products = this.filterByProvider(_products, this.providerSelected);
    this.products = _products;
    this.updateFilterCounts();
  }

  updateFilterCounts() {
    // Update Filter Counts by Providers
    let providerData = this.productsRaw;
    // Filter by Selected Search Term
    if (this.searchTerm) providerData = this.filterBySearch(providerData, this.searchTerm);
    // Update Filter Counts by Unique Providers Slugs
    this.providersFilterCount = [];
    this.providersFilterCount['all'] = providerData.length;
    this.providers.forEach((_provider) => {
      this.providersFilterCount[_provider.slug] = this.filterByProvider(providerData, _provider.alias).length;
    });

    // Update Filter Counts by series
    let seriesData = this.productsRaw;
    // Filter by Selected Search Term
    if (this.searchTerm) seriesData = this.filterBySearch(seriesData, this.searchTerm);
    // Filter by Selected Provider
    if (this.providerSelected) seriesData = this.filterByProvider(seriesData, this.providerSelected);

    // Reflect Changes
    this.dataLoaded = true;

    // Scroll to Selected Element
    if (this.seriesSelected) this.seriesScrollTo(this.seriesSelected);
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
    this.seriesSelected = null;
    if (!this.searchTerm) this.searchTerm = null;
    if (blur && !this.searchTerm) this.searchActive = false;
    this.router.navigate([], { relativeTo: this.route, queryParams: {s: this.searchTerm}, queryParamsHandling: 'merge', replaceUrl: true });
    this.fuseResults();
  }

  filterChange() {
    this.seriesSelected = null;
    if (!this.providerSelected) this.providerSelected = null;
    this.router.navigate([], { relativeTo: this.route, queryParams: {f: this.providerSelected}, queryParamsHandling: 'merge', replaceUrl: true });
    this.fuseResults();
  }


  seriesChange(series_slug, series_title, provider_name, thumbnail_2x) {
    this.series = {
      name: series_title,
      provider: {
        name: provider_name,
        thumbnail_2x: thumbnail_2x
      }
    };
    this.router.navigate([], { relativeTo: this.route, queryParams: {series: series_slug}, replaceUrl: true });
    this.seriesSelected = series_slug;
    this.getOptions();
  }

  clearSeries(navigate = true) {
    this.dataLoaded = false;
    this.series = null;
    this.options = null;
    this.optionsRaw = null;
    setTimeout(() => {
      this.getSelections();
      if (navigate) this.router.navigate([], { relativeTo: this.route, queryParams: {series: null}, replaceUrl: true });
    });
  }

  clearFilters() {
    this.dataLoaded = false;
    setTimeout(() => {
      this.searchTerm = null;
      this.providerSelected = null;
      this.clearSeries(false);
    });
  }

  seriesScrollTo(slug) {
    setTimeout(() => {
      if (this.seriesDatatable) {
        let bodyHeight = this.seriesDatatable.bodyHeight;
        let rowHeight = 60;
        let rowIndex;
        for(var i = 0, len = this.products.length; i < len; i++ ) {
            if(this.products[i]['slug'] === slug) {
               rowIndex = i;
               break;
            }
        }
        this.seriesDatatable.element.querySelector('.datatable-body').scrollTo({
          top: (rowIndex * rowHeight) - ((bodyHeight - rowHeight) / 2),
          behavior: 'smooth'
        });
      }
    }, 1000);
  }

  seriesRowClass = (row) => {
    return {
      'bg-light-yellow': row.slug === this.seriesSelected
    };
  }

  optionsRowClass = (row) => {
    return {
      'bg-light-red': row.date_end !== '2100-12-31',
      // 'not-allowed': row.riders_count === 0,
      // 'pointer': row.riders_count > 0,
    };
  }

  getDatatableHeight(data, rowHeight) {
    let max_height = 10 * rowHeight;
    let temp_height = (data.length || 1) * rowHeight;
    return (temp_height > max_height) ? max_height : temp_height;
  }
}