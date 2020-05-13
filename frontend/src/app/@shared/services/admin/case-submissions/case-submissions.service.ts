/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Injectable, OnDestroy } from '@angular/core';
import { Router } from '@angular/router';
import { Subject, Subscription } from 'rxjs';
import { ApiService } from '@app/@core/services/api';
import { MatDialog } from '@angular/material/dialog';
import { AdminClientRelationshipsService } from '@app/@shared/services/admin/client-relationships/client-relationships.service';

@Injectable()
export class AdminCaseSubmissionsService implements OnDestroy {

  constructor(private apiService: ApiService, private assClientService: AdminClientRelationshipsService, public dialog: MatDialog, private router: Router) {
    this.getClients();
  }

  private subs_getClients: Subscription;
  private clients;

  ngOnDestroy() {
    if (this.subs_getClients) this.subs_getClients.unsubscribe();
  }

  getClients() {
    this.subs_getClients = this.assClientService.getClients().subscribe(res => {
      this.clients = res['data'];
    });
  }

  getSubmissions(uuid = null) {
    let url = 'admin/submissions';
    url += (uuid) ? '/' + uuid : '';
    return this.apiService.get(url);
  }

  getCases(uuid) { return this.apiService.get('admin/submissions/' + uuid + '/cases'); }
  getLogs(uuid) { return this.apiService.get('admin/submissions/' + uuid + '/logs'); }
  getMedia(uuid) { return this.apiService.get('admin/submissions/' + uuid + '/media'); }
  download(uuid, media_id) { return this.apiService.download('admin/submissions/' + uuid + '/media/' + media_id); }
}