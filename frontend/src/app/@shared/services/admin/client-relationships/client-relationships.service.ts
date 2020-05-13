/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Injectable, OnDestroy } from '@angular/core';
import { Subject, Subscription } from 'rxjs';
import { map } from 'rxjs/operators';
import { ApiService } from '@app/@core/services/api';
import { MatDialog } from '@angular/material/dialog';

@Injectable()
export class AdminClientRelationshipsService implements OnDestroy {

  constructor(private apiService: ApiService, public dialog: MatDialog) {
    this.initClients();
    this.initSources();
  }

  private subs_getSelections: Subscription;
  public client_sources;
  public clients;

  ngOnDestroy() {
    if (this.subs_getSelections) this.subs_getSelections.unsubscribe();
  }

  initSources() {
    this.subs_getSelections = this.apiService.post('selections', {
      'lists': ['lfa-client-source']
    }).subscribe(res => {
      let data = res['data'];
      this.client_sources = data['lfa-client-source'];
    });
  }

  initClients() {
    this.getClients();
  }

  getClients(uuid = null) {
    let url = 'admin/clients';
    url += (uuid) ? '/' + uuid : '';
    return this.apiService.get(url).pipe(map(res => {
      let data = res['data'];
      if (!uuid) this.clients = data;
      // console.log('clients cache updated');
      return res;
    }));
  }

  updateClient(uuid, data) { return this.apiService.patch('admin/clients/' + uuid, data); }
  getSubmissions(uuid) { return this.apiService.get('admin/clients/' + uuid + '/submissions'); }
  getPolicies(uuid) { return this.apiService.get('admin/clients/' + uuid + '/policies'); }
  getLogs(uuid) { return this.apiService.get('admin/clients/' + uuid + '/logs'); }
}