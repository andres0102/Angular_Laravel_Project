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
import { AssociatesClientMergeDialogComponent } from '@app/@shared/components/associates/client-merge-dialog/client-merge-dialog.component';
import { AssociatesClientAddDialogComponent } from '@app/@shared/components/associates/client-add-dialog/client-add-dialog.component';

@Injectable()
export class AssociatesClientRelationshipsService implements OnDestroy {

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
    let url = 'associates/clients';
    url += (uuid) ? '/' + uuid : '';
    return this.apiService.get(url).pipe(map(res => {
      let data = res['data'];
      if (!uuid) this.clients = data;
      // console.log('clients cache updated');
      return res;
    }));
  }

  updateClient(uuid, data) { return this.apiService.patch('associates/clients/' + uuid, data); }
  getSubmissions(uuid) { return this.apiService.get('associates/clients/' + uuid + '/submissions'); }
  getPolicies(uuid) { return this.apiService.get('associates/clients/' + uuid + '/policies'); }
  getLogs(uuid) { return this.apiService.get('associates/clients/' + uuid + '/logs'); }


  // Create CLient :: Mat-Dialog
  addClientDialog() {
    const dialogRef = this.dialog.open(AssociatesClientAddDialogComponent, {
      width: '600px',
      disableClose: true,
      data: this.client_sources
    });
    dialogRef.afterClosed().subscribe(result => {
      // console.log('Add Client Dialog :: Closed', result);
      if (result['error'] === false) this.toggleCreatedSwal(result['data']);
      else this.toggleCreatedSwalClosed();
    });
  }
  // Event :: Client Created
  private _createdSwal = <Subject<boolean>> new Subject();
  createdSwal = this._createdSwal.asObservable();
  toggleCreatedSwal(data){ this._createdSwal.next(data); }
  // Event :: Client Create Dialog closed
  private _createdSwalClosed = <Subject<boolean>> new Subject();
  createdSwalClosed = this._createdSwalClosed.asObservable();
  toggleCreatedSwalClosed(){ this._createdSwalClosed.next(); }


  // Merge Clients :: Mat-Dialog
  mergeClientDialog() {
    const dialogRef = this.dialog.open(AssociatesClientMergeDialogComponent, {
      width: '600px',
      disableClose: true,
      data: this.clients
    });
    dialogRef.afterClosed().subscribe(result => {
      // console.log('Merge Client Dialog :: Closed', result);
      if (result == 'clients_merged') this.toggleMergedSwal();
    });
  }
  // Event :: Clients Merged
  private _mergedSwal = <Subject<boolean>> new Subject();
  mergedSwal = this._mergedSwal.asObservable();
  toggleMergedSwal(){ this._mergedSwal.next(); }
}