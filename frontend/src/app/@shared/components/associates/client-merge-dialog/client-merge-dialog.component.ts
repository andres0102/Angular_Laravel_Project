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

import * as Fuse from 'fuse.js';

@Component({
  selector: 'associates-client-merge-dialog',
  templateUrl: './client-merge-dialog.component.html',
  styleUrls: ['./client-merge-dialog.component.scss']
})
export class AssociatesClientMergeDialogComponent {
  processing;
  authForm = this.formBuilder.group({
    'from_client': ['', Validators.required],
    'to_client': ['', Validators.required],
  });

  get from_client() { return this.authForm.controls.from_client; }
  get to_client() { return this.authForm.controls.to_client; }

  constructor (
    @Inject(MAT_DIALOG_DATA) private clients: any,
    private formBuilder: FormBuilder,
    private apiService: ApiService,
    public dialogRef: MatDialogRef<AssociatesClientMergeDialogComponent>) { }

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
    // console.log("Merging", this.authForm.value);
    let authData = this.authForm.value;
    let data: any = {}
    data['from_client_uuid'] = authData.from_client.uuid;
    data['to_client_uuid'] = authData.to_client.uuid;
    // Merge Client
    this.apiService.post('associates/merge-clients', data).subscribe((res) => {
      // console.log("Merge clients response", res);
      if (!res.error) this.dialogRef.close('clients_merged');
      this.processing = false;
    });
  }

}