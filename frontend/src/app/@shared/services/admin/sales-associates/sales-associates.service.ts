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
import { AdminAssociateAddDialogComponent } from '@app/@shared/components/admin/associate-add-dialog/associate-add-dialog.component';

import { AdminSalesforceAddBandingLfaDialogComponent } from '@app/@shared/components/admin/salesforce-add-banding-lfa-dialog/salesforce-add-banding-lfa-dialog.component';
import { AdminSalesforceAddBandingGiDialogComponent } from '@app/@shared/components/admin/salesforce-add-banding-gi-dialog/salesforce-add-banding-gi-dialog.component';
import { AdminSalesforceAddMovementDialogComponent } from '@app/@shared/components/admin/salesforce-add-movement-dialog/salesforce-add-movement-dialog.component';
import { AdminSalesforceAddProviderCodeDialogComponent } from '@app/@shared/components/admin/salesforce-add-provider-code-dialog/salesforce-add-provider-code-dialog.component';

@Injectable()
export class AdminSalesAssociatesService implements OnDestroy {

  constructor(private apiService: ApiService, public dialog: MatDialog) {
    this.initAssociates();
    this.initSelections();
  }

  private subs_getSelections: Subscription;
  public selections;
  selections_loaded = false;
  public associates;

  ngOnDestroy() {
    if (this.subs_getSelections) this.subs_getSelections.unsubscribe();
  }

  initSelections() {
    this.subs_getSelections = this.apiService.post('selections', {
      'lists': [
        'gender',
        'lfa-provider',
        'lfa-associates-designations',
        'lfa-rnf-status',
        'lfa-teams-associates-groups',
        'lfa-teams-associates-units',
        'lfa-teams-associates-code',
      ]
    }).subscribe(res => {
      let data = res['data'];
      this.selections = [];
      this.selections['gender'] = data['gender'];
      this.selections['providers'] = data['lfa-provider'];
      this.selections['designations'] = data['lfa-associates-designations'];
      this.selections['rnf-status'] = data['lfa-rnf-status'];
      this.selections['groups'] = data['lfa-teams-associates-groups'];
      this.selections['units'] = data['lfa-teams-associates-units'];
      this.selections['code'] = data['lfa-teams-associates-code'];
      this.selections_loaded = true;
    });
  }

  initAssociates() {
    this.getAssociates();
  }

  getAssociates(uuid = null) {
    let url = 'admin/associates';
    url += (uuid) ? '/' + uuid : '';
    return this.apiService.get(url).pipe(map(res => {
      let data = res['data'];
      if (!uuid) this.associates = data;
      // console.log('associates cache updated');
      return res;
    }));
  }

  updateAssociate(uuid, data) { return this.apiService.patch('admin/associates/' + uuid, data); }
  getClients(uuid) { return this.apiService.get('admin/associates/' + uuid + '/clients'); }
  getSubmissions(uuid) { return this.apiService.get('admin/associates/' + uuid + '/submissions'); }
  getPolicies(uuid) { return this.apiService.get('admin/associates/' + uuid + '/policies'); }
  getLogs(uuid) { return this.apiService.get('admin/associates/' + uuid + '/logs'); }


  // Create Associate :: Mat-Dialog
  addAssociateDialog() {
    const dialogRef = this.dialog.open(AdminAssociateAddDialogComponent, {
      width: '900px',
      disableClose: true,
      data: this.selections
    });
    dialogRef.afterClosed().subscribe(result => {
      // console.log('Add Associate Dialog :: Closed', result);
      if (result['error'] === false) this.toggleCreatedSwal(result['data']);
      else this.toggleCreatedSwalClosed();
    });
  }

  // Event :: Associate Created
  private _createdSwal = <Subject<boolean>> new Subject();
  createdSwal = this._createdSwal.asObservable();
  toggleCreatedSwal(data){ this._createdSwal.next(data); }

  // Event :: Associate Create Dialog closed
  private _createdSwalClosed = <Subject<boolean>> new Subject();
  createdSwalClosed = this._createdSwalClosed.asObservable();
  toggleCreatedSwalClosed(){ this._createdSwalClosed.next(); }




  // Create Provider Code :: Mat-Dialog
  addProviderCodeDialog(associate_uuid) {
    const dialogRef = this.dialog.open(AdminSalesforceAddProviderCodeDialogComponent, {
      width: '600px',
      disableClose: true,
      data: {'associate_uuid': associate_uuid, 'providers': this.selections['providers']}
    });
    dialogRef.afterClosed().subscribe(result => {
      // console.log('Add Provider Code Dialog :: Closed', result);
      if (result['error'] === false) this.toggleCreatedProviderCodeSwal(result['data']);
    });
  }
  // Event :: Provider Code Created
  private _createdProviderCodeSwal = <Subject<boolean>> new Subject();
  createdProviderCodeSwal = this._createdProviderCodeSwal.asObservable();
  toggleCreatedProviderCodeSwal(data){ this._createdProviderCodeSwal.next(data); }




  // Create Movements :: Mat-Dialog
  addMovementDialog(associate_uuid, lfa_sl_no) {
    const dialogRef = this.dialog.open(AdminSalesforceAddMovementDialogComponent, {
      width: '900px',
      disableClose: true,
      data: {
        'associate_uuid': associate_uuid,
        'lfa_sl_no': lfa_sl_no,
        'designations': this.selections['designations'],
        'groups': this.selections['groups'],
        'units': this.selections['units'],
      }
    });
    dialogRef.afterClosed().subscribe(result => {
      // console.log('Add Movements Dialog :: Closed', result);
      if (result['error'] === false) this.toggleCreatedMovementSwal(result['data']);
    });
  }
  // Event :: Movements Created
  private _createdMovementSwal = <Subject<boolean>> new Subject();
  createdMovementSwal = this._createdMovementSwal.asObservable();
  toggleCreatedMovementSwal(data){ this._createdMovementSwal.next(data); }




  // Create Banding LFA :: Mat-Dialog
  addBandingLfaDialog(associate_uuid) {
    const dialogRef = this.dialog.open(AdminSalesforceAddBandingLfaDialogComponent, {
      width: '600px',
      disableClose: true,
      data: associate_uuid
    });
    dialogRef.afterClosed().subscribe(result => {
      // console.log('Add Bandings LFA Dialog :: Closed', result);
      if (result['error'] === false) this.toggleCreatedBandingLfaSwal(result['data']);
    });
  }
  // Event :: Banding LFA Created
  private _createdBandingLfaSwal = <Subject<boolean>> new Subject();
  createdBandingLfaSwal = this._createdBandingLfaSwal.asObservable();
  toggleCreatedBandingLfaSwal(data){ this._createdBandingLfaSwal.next(data); }




  // Create Banding GI :: Mat-Dialog
  addBandingGiDialog(associate_uuid) {
    const dialogRef = this.dialog.open(AdminSalesforceAddBandingGiDialogComponent, {
      width: '600px',
      disableClose: true,
      data: associate_uuid
    });
    dialogRef.afterClosed().subscribe(result => {
      // console.log('Add Bandings GI Dialog :: Closed', result);
      if (result['error'] === false) this.toggleCreatedBandingGiSwal(result['data']);
    });
  }
  // Event :: Banding GI Created
  private _createdBandingGiSwal = <Subject<boolean>> new Subject();
  createdBandingGiSwal = this._createdBandingGiSwal.asObservable();
  toggleCreatedBandingGiSwal(data){ this._createdBandingGiSwal.next(data); }
}