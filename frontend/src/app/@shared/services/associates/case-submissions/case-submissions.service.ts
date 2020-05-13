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
import { AssociatesClientRelationshipsService } from '@app/@shared/services/associates/client-relationships/client-relationships.service';
import { AssociatesCaseSubmissionsAddDialogComponent } from '@app/@shared/components/associates/case-submissions-add-dialog/case-submissions-add-dialog.component';

@Injectable()
export class AssociatesCaseSubmissionsService implements OnDestroy {

  constructor(private apiService: ApiService, private assClientService: AssociatesClientRelationshipsService, public dialog: MatDialog, private router: Router) {
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
    let url = 'associates/submissions';
    url += (uuid) ? '/' + uuid : '';
    return this.apiService.get(url);
  }

  getTeamSubmissions(uuid = null) {
    let url = 'associates/submissions/team';
    url += (uuid) ? '/' + uuid : '';
    return this.apiService.get(url);
  }

  getCases(uuid) { return this.apiService.get('associates/submissions/' + uuid + '/cases'); }
  getLogs(uuid) { return this.apiService.get('associates/submissions/' + uuid + '/logs'); }
  getMedia(uuid) { return this.apiService.get('associates/submissions/' + uuid + '/media'); }
  download(uuid, media_id) { return this.apiService.download('associates/submissions/' + uuid + '/media/' + media_id); }

  // Create Submission :: Mat-Dialog
  addSubmissionDialog() {
    const dialogRef = this.dialog.open(AssociatesCaseSubmissionsAddDialogComponent, {
      width: '1200px',
      disableClose: true,
      data: this.clients
    });
    dialogRef.afterClosed().subscribe(result => {
      // console.log('Add Submission Dialog :: Closed', result);
      if (result['data'] && result['data']['uuid']) {
        let route_url = "authenticated/associates/business/case-submissions/" + result['data']['uuid'] + "/uploaded-files";
        this.router.navigate([route_url]);
      }
    });
  }

  // Event :: Client Created
  private _createdSwal = <Subject<boolean>> new Subject();
  createdSwal = this._createdSwal.asObservable();
  toggleCreatedSwal(){ this._createdSwal.next(); }
}