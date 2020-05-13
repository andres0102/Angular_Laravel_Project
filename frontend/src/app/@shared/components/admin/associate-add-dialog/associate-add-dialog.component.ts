/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Component, Inject } from '@angular/core';
import { FormBuilder, Validators, AbstractControl } from '@angular/forms';
import { Observable } from 'rxjs';

import { map, startWith } from 'rxjs/operators';
import { ApiService } from '@app/@core/services';
import { Associate } from '@app/models';
import { MatDialogRef } from '@angular/material/dialog';
import { MAT_DIALOG_DATA } from '@angular/material';
import { MomentDateAdapter, MAT_MOMENT_DATE_ADAPTER_OPTIONS } from '@angular/material-moment-adapter';
import { DateAdapter, MAT_DATE_FORMATS, MAT_DATE_LOCALE } from '@angular/material/core';

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
export const assc_add_date_formats = {
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

import * as Fuse from 'fuse.js';

@Component({
  selector: 'admin-associate-add-dialog',
  templateUrl: './associate-add-dialog.component.html',
  styleUrls: ['./associate-add-dialog.component.scss'],
  providers: [
    { provide: MAT_MOMENT_DATE_ADAPTER_OPTIONS, useValue: {useUtc: true} },
    {
      provide: DateAdapter,
      useClass: MomentDateAdapter,
      deps: [MAT_DATE_LOCALE, MAT_MOMENT_DATE_ADAPTER_OPTIONS]
    },
    { provide: MAT_DATE_FORMATS, useValue: assc_add_date_formats },
  ]
})
export class AdminAssociateAddDialogComponent {
  constructor (
    @Inject(MAT_DIALOG_DATA) private data: any,
    private formBuilder: FormBuilder,
    private apiService: ApiService,
    public dialogRef: MatDialogRef<AdminAssociateAddDialogComponent>) {
      this.select_salesforce_designations = data['designations'];
      this.select_rnf_status = data['rnf-status'];
      this.select_groups = data['groups'];
      this.select_units = data['units'];
      this.select_code = data['code'];
      this.select_gender = data['gender'];
  }

  select_gender;
  select_salesforce_designations;
  select_rnf_status;
  select_groups;
  select_units;
  select_code;

  processing = false;
  exists = false;
  authForm = this.formBuilder.group({
    'gender_slug': ['', Validators.required],
    'full_name': ['', Validators.required],
    'email': ['', [Validators.required,Validators.email]],
    'designation_slug': ['financial-services-consultant', Validators.required],
    'banding_lfa': [0.5, Validators.required],
    'rnf_status_slug': ['', Validators.required],
    'rnf_no': ['', Validators.required],
    'unit': ['', Validators.required],
    'group': ['', Validators.required],
    'lfa_sl_no': ['', [Validators.required, Validators.minLength(4), Validators.maxLength(4)]],
    'date_lfa_application': [''],
    'date_ceo_interview': [''],
    'date_contract_start': [''],
    'date_onboarded': [''],
    'date_m9': [''],
    'date_m9a': [''],
    'date_m5': [''],
    'date_hi': [''],
    'date_rnf_submission': ['', Validators.required],
    'date_rnf_approval': ['', Validators.required],
  });
  get gender_slug() { return this.authForm.controls.gender_slug; }
  get full_name() { return this.authForm.controls.full_name; }
  get email() { return this.authForm.controls.email; }
  get banding_lfa() { return this.authForm.controls.banding_lfa; }
  get designation_slug() { return this.authForm.controls.designation_slug; }
  get rnf_status_slug() { return this.authForm.controls.rnf_status_slug; }
  get rnf_no() { return this.authForm.controls.rnf_no; }
  get unit() { return this.authForm.controls.unit; }
  get group() { return this.authForm.controls.group; }
  get lfa_sl_no() { return this.authForm.controls.lfa_sl_no; }
  get date_rnf_submission() { return this.authForm.controls.date_rnf_submission; }
  get date_rnf_approval() { return this.authForm.controls.date_rnf_approval; }
  get date_rnf_withdrawal() { return this.authForm.controls.date_rnf_withdrawal; }
  get date_rnf_cessation() { return this.authForm.controls.date_rnf_cessation; }
  get date_m9() { return this.authForm.controls.date_m9; }
  get date_m9a() { return this.authForm.controls.date_m9a; }
  get date_m5() { return this.authForm.controls.date_m5; }
  get date_hi() { return this.authForm.controls.date_hi; }
  get date_lfa_application() { return this.authForm.controls.date_lfa_application; }
  get date_ceo_interview() { return this.authForm.controls.date_ceo_interview; }
  get date_contract_start() { return this.authForm.controls.date_contract_start; }
  get date_onboarded() { return this.authForm.controls.date_onboarded; }

  resetForm(ref = null) {
    if (!ref) {
      this.authForm.reset();
      this.designation_slug.setValue('financial-services-consultant');
      this.banding_lfa.setValue(0.5);
    } else {
      const form_field = this.authForm.get(ref);
      if (!form_field.validator) return form_field.reset();

      const validator = form_field.validator({} as AbstractControl);
      if (validator && validator.required) form_field.setValue('');
      else form_field.reset();
    }
  }

  submitForm() {
    this.processing = true;
    console.log("Creating", this.authForm.value);
    // Create Client
    this.apiService.post('admin/associates', this.authForm.value).subscribe((res) => {
      console.log("POST associates", res);
      if (res.error === false) {
        // Client Created
        this.dialogRef.close(res);
      } else if (res.error && res.message == "email_exists") {
        // Email exists
        this.email.setErrors({exists: true});
      } else if (res.error && res.message == "sl_exists") {
        // Email exists
        this.lfa_sl_no.setErrors({exists: true});
      }

      this.processing = false;
    });
  }

}