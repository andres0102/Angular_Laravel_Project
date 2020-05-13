/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Component, OnInit, Inject } from '@angular/core';
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
export const CLIENT_CREATE_DATE_FORMATS = {
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
  selector: 'associates-client-add-dialog',
  templateUrl: './client-add-dialog.component.html',
  styleUrls: ['./client-add-dialog.component.scss'],
  providers: [
    { provide: MAT_MOMENT_DATE_ADAPTER_OPTIONS, useValue: {useUtc: true} },
    {
      provide: DateAdapter,
      useClass: MomentDateAdapter,
      deps: [MAT_DATE_LOCALE, MAT_MOMENT_DATE_ADAPTER_OPTIONS]
    },
    { provide: MAT_DATE_FORMATS, useValue: CLIENT_CREATE_DATE_FORMATS },
  ]
})
export class AssociatesClientAddDialogComponent implements OnInit {
  constructor (
    @Inject(MAT_DIALOG_DATA) private client_sources: any,
    private formBuilder: FormBuilder,
    private apiService: ApiService,
    public dialogRef: MatDialogRef<AssociatesClientAddDialogComponent>) { }

  ngOnInit() {
    this.new.setValue(false);
  }

  processing = false;
  exists = false;
  authForm = this.formBuilder.group({
    'display_name': ['', Validators.required],
    'client_type_slug': ['', Validators.required],
    'business_uen': [''],
    'job_title': [''],
    'company_name': [''],
    'nric_no': [''],
    'source_slug': ['', Validators.required],
    'new': [''],
    'gender_slug': [''],
    'date_birth': [''],
  });

  get new() { return this.authForm.controls.new; }
  get client_type_slug() { return this.authForm.controls.client_type_slug; }
  get display_name() { return this.authForm.controls.display_name; }
  get company_name() { return this.authForm.controls.company_name; }
  get job_title() { return this.authForm.controls.job_title; }
  get business_uen() { return this.authForm.controls.business_uen; }
  get nric_no() { return this.authForm.controls.nric_no; }
  get source_slug() { return this.authForm.controls.source_slug; }
  get gender_slug() { return this.authForm.controls.gender_slug; }
  get date_birth() { return this.authForm.controls.date_birth; }

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
    // console.log("Creating", this.authForm.value);
    // Create Client
    this.apiService.post('associates/clients', this.authForm.value).subscribe((res) => {
      // console.log("POST clients", res);
      if (res.error === false) {
        // Client Created
        this.dialogRef.close(res);
      } else if (res.error && res.message == "client_exists") {
        // Client exists
        this.exists = true;
      }

      this.processing = false;
    });
  }

}