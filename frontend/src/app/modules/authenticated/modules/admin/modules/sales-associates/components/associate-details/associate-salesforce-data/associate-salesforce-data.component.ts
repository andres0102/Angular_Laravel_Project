/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Component, OnInit, OnDestroy } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { FormBuilder, Validators, AbstractControl } from '@angular/forms';
import { MomentDateAdapter, MAT_MOMENT_DATE_ADAPTER_OPTIONS } from '@angular/material-moment-adapter';
import { DateAdapter, MAT_DATE_FORMATS, MAT_DATE_LOCALE } from '@angular/material/core';
import { Observable, Subscription } from 'rxjs';
import { map, startWith } from 'rxjs/operators';

import { Associate } from '@app/models';
import { ApiService, TitleService } from '@app/@core/services';
import { PageService } from '@auth/services';
import { AdminSalesAssociatesService } from '@app/@shared/services/admin/sales-associates/sales-associates.service';
import { MessageService } from '@app/@shared/components/message/message.service';

// Depending on whether rollup is used, moment needs to be imported differently.
// Since Moment.js doesn't have a default export, we normally need to import using the `* as`
// syntax. However, rollup creates a synthetic default module and we thus need to import it using
// the `default as` syntax.
import * as _moment from 'moment';
// tslint:disable-next-line:no-duplicate-imports
import {default as _rollupMoment} from 'moment';

const moment = _rollupMoment || _moment;

// See the Moment.js docs for the meaning of these formats:
// https://momentjs.com/docs/#/displaying/format/
export const salesforce_data_date_formats = {
  parse: {
    dateInput: 'LL',
  },
  display: {
    dateInput: 'LL',
    monthYearLabel: 'MMM YYYY',
    dateA11yLabel: 'LL',
    monthYearA11yLabel: 'MMMM YYYY',
  },
};

@Component({
  selector: 'admin-associate-salesforce-data',
  templateUrl: './associate-salesforce-data.component.html',
  styleUrls: ['./associate-salesforce-data.component.scss'],
  providers: [
    { provide: MAT_MOMENT_DATE_ADAPTER_OPTIONS, useValue: {useUtc: true} },
    {
      provide: DateAdapter,
      useClass: MomentDateAdapter,
      deps: [MAT_DATE_LOCALE, MAT_MOMENT_DATE_ADAPTER_OPTIONS]
    },
    { provide: MAT_DATE_FORMATS, useValue: salesforce_data_date_formats },
  ],
})
export class AdminAssociateSalesforceDataComponent implements OnInit, OnDestroy {
  private selectionsSub: Subscription;
  private associateUpdateSub: Subscription;
  private associateDeleteSub: Subscription;
  private _createBandingLFAsub: Subscription;
  private _createBandingGIsub: Subscription;
  private _createMovementsub: Subscription;
  private _createProviderCodesub: Subscription;

  associate: Associate;
  dataLoaded: boolean = false;
  processing: boolean = false;
  editing: boolean = false;
  authForm_default_values;

  providers_map;
  designations_map;

  select_providers;
  select_rnf_status;
  select_salesforce_designations;
  select_groups;
  select_units;

  authForm = this.formBuilder.group({
    'full_name': ['', Validators.required],
    'email': ['', [Validators.required,Validators.email]],
    'date_lfa_application': [''],
    'date_ceo_interview': [''],
    'date_contract_start': [''],
    'date_onboarded': [''],
    // Salesforce Details
    'lfa_sl_no': ['', [Validators.required, Validators.minLength(4), Validators.maxLength(4)]],
    'applicant_code': [''],
    // RNF Details
    'rnf_status_slug': ['', Validators.required],
    'rnf_no': ['', Validators.required],
    'date_rnf_submission': ['', Validators.required],
    'date_rnf_approval': ['', Validators.required],
    'date_rnf_withdrawal': [''],
    'date_rnf_cessation': [''],
    // CMFAS Details
    'date_m9': [''],
    'date_m9a': [''],
    'date_m5': [''],
    'date_hi': [''],
    'date_m8': [''],
    'date_m8a': [''],
    'date_cert_ilp': [''],
    'date_cert_li': [''],
    'date_cert_fna': [''],
    'date_cert_bcp': [''],
    'date_cert_pgi': [''],
    'date_cert_comgi': [''],
    'date_cert_cgi': [''],
    'cert_pro': [''],
  });

  get full_name() { return this.authForm.controls.full_name; }
  get email() { return this.authForm.controls.email; }
  get date_lfa_application() { return this.authForm.controls.date_lfa_application; }
  get date_ceo_interview() { return this.authForm.controls.date_ceo_interview; }
  get date_contract_start() { return this.authForm.controls.date_contract_start; }
  get date_onboarded() { return this.authForm.controls.date_onboarded; }
  // Salesforce Details
  get lfa_sl_no() { return this.authForm.controls.lfa_sl_no; }
  get applicant_code() { return this.authForm.controls.applicant_code; }
  // RNF Details
  get rnf_status_slug() { return this.authForm.controls.rnf_status_slug; }
  get rnf_no() { return this.authForm.controls.rnf_no; }
  get date_rnf_submission() { return this.authForm.controls.date_rnf_submission; }
  get date_rnf_approval() { return this.authForm.controls.date_rnf_approval; }
  get date_rnf_withdrawal() { return this.authForm.controls.date_rnf_withdrawal; }
  get date_rnf_cessation() { return this.authForm.controls.date_rnf_cessation; }
  // CMFAS Details
  get date_m9() { return this.authForm.controls.date_m9; }
  get date_m9a() { return this.authForm.controls.date_m9a; }
  get date_m5() { return this.authForm.controls.date_m5; }
  get date_hi() { return this.authForm.controls.date_hi; }
  get date_m8() { return this.authForm.controls.date_m8; }
  get date_m8a() { return this.authForm.controls.date_m8a; }
  get date_cert_ilp() { return this.authForm.controls.date_cert_ilp; }
  get date_cert_li() { return this.authForm.controls.date_cert_li; }
  get date_cert_fna() { return this.authForm.controls.date_cert_fna; }
  get date_cert_bcp() { return this.authForm.controls.date_cert_bcp; }
  get date_cert_pgi() { return this.authForm.controls.date_cert_pgi; }
  get date_cert_comgi() { return this.authForm.controls.date_cert_comgi; }
  get date_cert_cgi() { return this.authForm.controls.date_cert_cgi; }
  get cert_pro() { return this.authForm.controls.cert_pro; }


  banding_lfa_form = this.formBuilder.group({
    'rate': ['0.5', Validators.required],
    'date_start': ['', Validators.required],
  });

  constructor (
    private formBuilder: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    private apiService: ApiService,
    private pageService: PageService,
    private titleService: TitleService,
    private _notification: MessageService,
    private adminSalesAssocService: AdminSalesAssociatesService) {
    this.associate = this.route.parent.snapshot.data.associate;

    this._createBandingLFAsub = this.adminSalesAssocService.createdBandingLfaSwal.subscribe(associate => {
      this.router.navigate(['/authenticated/admin/sales-associates/' + this.associate.uuid + '/salesforce-data']);
      this.resetForm(null, associate);
      this.processing = false;
      this.pageService.triggerParentRefresh(true);
      this._notification.create('success', 'Success! LFA Banding record added successfully.', { Position: 'top', Style: 'bar', Duration: 2000 });
    });

    this._createBandingGIsub = this.adminSalesAssocService.createdBandingGiSwal.subscribe(associate => {
      this.router.navigate(['/authenticated/admin/sales-associates/' + this.associate.uuid + '/salesforce-data']);
      this.resetForm(null, associate);
      this.processing = false;
      this.pageService.triggerParentRefresh(true);
      this._notification.create('success', 'Success! GI Banding record added successfully.', { Position: 'top', Style: 'bar', Duration: 2000 });
    });

    this._createMovementsub = this.adminSalesAssocService.createdMovementSwal.subscribe(associate => {
      this.router.navigate(['/authenticated/admin/sales-associates/' + this.associate.uuid + '/salesforce-data']);
      this.resetForm(null, associate);
      this.processing = false;
      this.pageService.triggerParentRefresh(true);
      this._notification.create('success', 'Success! Movement record added successfully.', { Position: 'top', Style: 'bar', Duration: 2000 });
    });

    this._createProviderCodesub = this.adminSalesAssocService.createdProviderCodeSwal.subscribe(associate => {
      this.router.navigate(['/authenticated/admin/sales-associates/' + this.associate.uuid + '/salesforce-data']);
      this.resetForm(null, associate);
      this.processing = false;
      this.pageService.triggerParentRefresh(true);
      this._notification.create('success', 'Success! Provider Code record added successfully.', { Position: 'top', Style: 'bar', Duration: 2000 });
    });
  }

  ngOnInit() {
    this.getSelections();
  }

  ngOnDestroy() {
    if (this.selectionsSub) this.selectionsSub.unsubscribe();
    if (this.associateUpdateSub) this.associateUpdateSub.unsubscribe();
    if (this.associateDeleteSub) this.associateDeleteSub.unsubscribe();
    if (this._createBandingLFAsub) this._createBandingLFAsub.unsubscribe();
    if (this._createBandingGIsub) this._createBandingGIsub.unsubscribe();
    if (this._createMovementsub) this._createMovementsub.unsubscribe();
    if (this._createProviderCodesub) this._createProviderCodesub.unsubscribe();
  }

  private _filter(value, model): string[] {
    const filterValue = (value) ? value.toLowerCase() : '';
    return model.filter(option => option.slug.toLowerCase().includes(filterValue));
  }

  getSelections() {
    this.selectionsSub = this.apiService.post('selections', {
      'lists': [
        'lfa-associates-designations',
        'lfa-rnf-status',
        'lfa-teams-associates-groups',
        'lfa-teams-associates-units',
        'lfa-provider',
      ]
    }).subscribe(res => {
      let data = res['data'];
      this.select_salesforce_designations = data['lfa-associates-designations'];
      this.select_rnf_status = data['lfa-rnf-status'];
      this.select_groups = data['lfa-teams-associates-groups'];
      this.select_units = data['lfa-teams-associates-units'];
      this.select_providers = data['lfa-provider'];
      this.processMap();
      this.updateModel();
    });
  }

  processMap() {
    //
    var select_providers: any = {};
    this.select_providers.forEach((option) => { select_providers[option.slug] = option.full_name; });
    this.providers_map = new Map(Object.entries(select_providers));
    //
    var select_salesforce_designations: any = {};
    this.select_salesforce_designations.forEach((option) => { select_salesforce_designations[option.slug] = option; });
    this.designations_map = new Map(Object.entries(select_salesforce_designations));
  }

  resetForm(ref = null, data = null) {
    if (!ref) {
      this.authForm.reset();
      this.updateModel(data);
    } else {
      const form_field = this.authForm.get(ref);
      if (!form_field.validator) return form_field.reset();

      const validator = form_field.validator({} as AbstractControl);
      if (validator && validator.required) form_field.setValue('');
      else form_field.reset();
    }
  }

  updateModel(data = null) {
    if (data) this.associate = data;

    // Update page title
    this.titleService.manualTitle(this.associate.personal.full_name + ' | Update Associate Salesforce Data');

    // User Details
    this.full_name.setValue(this.associate.personal.full_name);
    this.email.setValue(this.associate.email);

    // Salesforce Details
    this.lfa_sl_no.setValue(this.associate.lfa_sl_no);
    this.applicant_code.setValue(this.associate.applicant_code);

    // RNF Information
    if (this.associate.rnf) {
      this.rnf_status_slug.setValue(this.associate.rnf.status_slug);
      this.rnf_no.setValue(this.associate.rnf.no);
      this.date_rnf_submission.setValue(this.associate.rnf.date_submission);
      this.date_rnf_approval.setValue(this.associate.rnf.date_approval);
      this.date_rnf_withdrawal.setValue(this.associate.rnf.date_withdrawal);
      this.date_rnf_cessation.setValue(this.associate.rnf.date_cessation);
    }

    // CMFAS Information
    if (this.associate.certs) {
      this.date_m9.setValue(this.associate.certs.date_m9);
      this.date_m9a.setValue(this.associate.certs.date_m9a);
      this.date_m5.setValue(this.associate.certs.date_m5);
      this.date_hi.setValue(this.associate.certs.date_hi);
      this.date_m8.setValue(this.associate.certs.date_m8);
      this.date_m8a.setValue(this.associate.certs.date_m8a);
      this.date_cert_ilp.setValue(this.associate.certs.date_cert_ilp);
      this.date_cert_li.setValue(this.associate.certs.date_cert_li);
      this.date_cert_fna.setValue(this.associate.certs.date_cert_fna);
      this.date_cert_bcp.setValue(this.associate.certs.date_cert_bcp);
      this.date_cert_pgi.setValue(this.associate.certs.date_cert_pgi);
      this.date_cert_comgi.setValue(this.associate.certs.date_cert_comgi);
      this.date_cert_cgi.setValue(this.associate.certs.date_cert_cgi);
      this.cert_pro.setValue(this.associate.certs.cert_pro);
    }

    this.authForm_default_values = this.authForm.value;
    this.dataLoaded = true;
    this.editing = false;
  }

  getUpdatedValues() {
    const updatedFormValues = {};
    this.authForm['_forEachChild']((control, name) => {
      if (control.value != this.authForm_default_values[name]) {
          updatedFormValues[name] = control.value;
      }
    });
    return updatedFormValues;
  }

  submitForm() {
    this.processing = true;
    this.editing = false;
    this.associateUpdateSub = this.adminSalesAssocService.updateAssociate(this.associate.uuid, this.getUpdatedValues()).subscribe((res) => {
      this.router.navigate(['/authenticated/admin/sales-associates/' + this.associate.uuid + '/salesforce-data']);
      this.resetForm(null, res.data);
      this.processing = false;
      this.pageService.triggerParentRefresh(true);
      this._notification.create('success', 'Success! Associate Profile updated successfully.', { Position: 'top', Style: 'bar', Duration: 2000 });
    });
  }

  delete_data(type = null, uuid = null){
    this.processing = true;
    let url = 'admin/associates/' + this.associate.uuid + '/' + type + '/' + uuid;
    this.associateDeleteSub = this.apiService.delete(url).subscribe((res) => {
      this.router.navigate(['/authenticated/admin/sales-associates/' + this.associate.uuid + '/salesforce-data']);
      this.resetForm(null, res.data);
      this.processing = false;
      this.pageService.triggerParentRefresh(true);
      switch (type) {
        case 'provider_codes':
          this._notification.create('success', 'Success! Provider Code record deleted successfully.', { Position: 'top', Style: 'bar', Duration: 2000 });
          break;
        case 'movements':
          this._notification.create('success', 'Success! Movement record deleted successfully.', { Position: 'top', Style: 'bar', Duration: 2000 });
          break;
        case 'bandings_lfa':
          this._notification.create('success', 'Success! LFA Banding record deleted successfully.', { Position: 'top', Style: 'bar', Duration: 2000 });
          break;
        case 'bandings_gi':
          this._notification.create('success', 'Success! GI Banding record deleted successfully.', { Position: 'top', Style: 'bar', Duration: 2000 });
          break;
      }
    });

  }

  banding_gi_rate(rank) {
    switch (rank) {
      case 1: return '60%'; break;
      case 2: return '68%'; break;
      case 3: return '75%'; break;
      case 4: return '80%'; break;
    }
  }
}
