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

import { NgsRevealConfig } from 'ngx-scrollreveal';

@Component({
  selector: 'associates-client-activity-log',
  templateUrl: './client-activity-log.component.html',
  styleUrls: ['./client-activity-log.component.scss'],
})
export class AssociatesClientActivityLogComponent implements OnInit, OnDestroy {
  private logSub: Subscription;

  client: Client;
  logs;
  dataLoaded: boolean = false;

  constructor (private route: ActivatedRoute,
               private apiService: ApiService,
               private pageService: PageService,
               private titleService: TitleService,
               private ngxSRConfig: NgsRevealConfig,
               private assClientService: AssociatesClientRelationshipsService) {
    this.client = this.route.parent.snapshot.data.client;

    // this.ngxSRConfig.distance = 0;
  }

  ngOnInit() {
    this.getLogs();

    // Update page title
    this.titleService.manualTitle(this.client.display_name + ' | Client Activity Log');
  }

  ngOnDestroy() {
    if (this.logSub) this.logSub.unsubscribe();
  }

  getLogs() {
    if (this.client) {
      this.dataLoaded = false;
      this.logs = [];
      this.logSub = this.assClientService.getLogs(this.client.uuid).subscribe(res => {
        let data = res.data;
        this.logs = data;
        this.dataLoaded = true;
      });
    }
  }

  getLink(event) {
    switch (event) {
      case 'submission_created':
        return ['../submissions'];
        break;
      case 'updated':
        return ['../particulars'];
        break;
      default:
        return null;
    }
  }

  getStyle(event) {
    switch (event) {
      case 'submission_created':
        return 'info';
        break;
      case 'merged':
        return 'primary';
        break;
      case 'created':
        return 'success';
        break;
      case 'updated':
        return 'warning small';
        break;
      default:
        return null;
    }
  }

  getIcon(event) {
    switch (event) {
      case 'submission_created':
        return 'fas fa-cabinet-filing';
        break;
      case 'merged':
        return 'fas fa-code-merge';
        break;
      case 'created':
        return 'fas fa-plus';
        break;
      default:
        return null;
    }
  }
}
