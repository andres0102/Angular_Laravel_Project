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
export const salesforce_add_movement_date_formats = {
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
  selector: 'admin-salesforce-add-movement-dialog',
  templateUrl: './salesforce-add-movement-dialog.component.html',
  styleUrls: ['./salesforce-add-movement-dialog.component.scss'],
  providers: [
    { provide: MAT_MOMENT_DATE_ADAPTER_OPTIONS, useValue: {useUtc: true} },
    {
      provide: DateAdapter,
      useClass: MomentDateAdapter,
      deps: [MAT_DATE_LOCALE, MAT_MOMENT_DATE_ADAPTER_OPTIONS]
    },
    { provide: MAT_DATE_FORMATS, useValue: salesforce_add_movement_date_formats },
  ],
})
export class AdminSalesforceAddMovementDialogComponent {
  processing;
  designations;
  units;
  groups;
  authForm = this.formBuilder.group({
    'designation_slug': ['financial-services-consultant', Validators.required],
    'date_start': [new Date(), Validators.required],
    'unit': ['', Validators.required],
    'group': ['', Validators.required],
    'lfa_sl_no': ['', Validators.required],
  });

  get rate() { return this.authForm.controls.rate; }
  get date_start() { return this.authForm.controls.date_start; }
  get unit() { return this.authForm.controls.unit; }
  get group() { return this.authForm.controls.group; }
  get lfa_sl_no() { return this.authForm.controls.lfa_sl_no; }

  constructor (
    @Inject(MAT_DIALOG_DATA) private injected_data: any,
    private formBuilder: FormBuilder,
    private apiService: ApiService,
    public dialogRef: MatDialogRef<AdminSalesforceAddMovementDialogComponent>) {
    this.designations = injected_data['designations'];
    this.units = injected_data['units'];
    this.groups = injected_data['groups'];
    this.lfa_sl_no.setValue(injected_data['lfa_sl_no'])
  }

  resetForm(ref = null) {
    if (!ref) {
      this.authForm.reset();
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
    console.log("Adding Movement", this.authForm.value);
    let authData = this.authForm.value;
    let url = 'admin/associates/' + this.injected_data['associate_uuid'] + '/movements';
    this.apiService.post(url, authData).subscribe((res) => {
      console.log("Add Movement response", res);
      if (!res.error) this.dialogRef.close(res);
      this.processing = false;
    });
  }

}