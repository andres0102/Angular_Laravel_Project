/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Component, ViewChild, OnInit, OnDestroy } from '@angular/core';
import { Router, Event, NavigationEnd } from '@angular/router';
import { Subscription } from 'rxjs';
import { ApiService } from '@app/@core/services';

@Component({
  selector: 'associates-salesforce-submissions-report',
  templateUrl: './salesforce-submissions-report.component.html',
  styleUrls: ['./salesforce-submissions-report.component.scss']
})
export class AssociatesSalesforceSubmissionsReportComponent implements OnInit, OnDestroy {
  _routerSubscriptions: Subscription;
  reportSub: Subscription;

  now = new Date()

  title;
  report;
  associates;

  constructor(
    private apiService: ApiService,
    private router: Router) {
    this._routerSubscriptions = router.events.subscribe( (event: Event) => {
      if (event instanceof NavigationEnd) {
        var root = this.router.routerState.snapshot.root;
        while (root) {
          if (root.children && root.children.length) {
              root = root.children[0];
          } else if (root.data) {
              this.title = (root.data.title) ? root.data.title : null;
              break;
          } else {
              break;
          }
        }
      }
    });
  }

  ngOnInit() {
    this.reportSub = this.apiService.get('associates/teams/salesforce/submission-report').subscribe(res => {
      if (res.error === false) {
        this.report = res.report;
        this.associates = res.data;
        // console.log(res);
      }
    });
  }

  ngOnDestroy() {
    if (this._routerSubscriptions) this._routerSubscriptions.unsubscribe();
  }

  prevMonth() {
    if (!this.report.earliest) {
      this.reportSub = this.apiService.get('associates/teams/salesforce/submission-report/' + this.report.prev.year + '/' + this.report.prev.month).subscribe(res => {
        if (res.error === false) {
          this.report = res.report;
          this.associates = res.data;
        }
      });
    }
  }

  nextMonth() {
    if (!this.report.latest) {
      this.reportSub = this.apiService.get('associates/teams/salesforce/submission-report/' + this.report.next.year + '/' + this.report.next.month).subscribe(res => {
        if (res.error === false) {
          this.report = res.report;
          this.associates = res.data;
        }
      });
    }
  }
}
