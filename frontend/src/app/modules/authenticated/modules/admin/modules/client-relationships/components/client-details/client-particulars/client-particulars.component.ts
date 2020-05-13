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

import { Client } from '@app/models';
import { ApiService, TitleService } from '@app/@core/services';
import { PageService } from '@auth/services';
import { AdminClientRelationshipsService } from '@app/@shared/services/admin/client-relationships/client-relationships.service';
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
export const MY_FORMATS = {
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
  selector: 'admin-client-particulars',
  templateUrl: './client-particulars.component.html',
  styleUrls: ['./client-particulars.component.scss'],
  providers: [
    { provide: MAT_MOMENT_DATE_ADAPTER_OPTIONS, useValue: {useUtc: true} },
    {
      provide: DateAdapter,
      useClass: MomentDateAdapter,
      deps: [MAT_DATE_LOCALE, MAT_MOMENT_DATE_ADAPTER_OPTIONS]
    },
    { provide: MAT_DATE_FORMATS, useValue: MY_FORMATS },
  ],
})
export class AdminClientParticularsComponent implements OnInit, OnDestroy {
  private selectionsSub: Subscription;
  private clientUpdateSub: Subscription;

  client: Client;
  dataLoaded: boolean = false;
  processing: boolean = false;
  editing: boolean = false;
  authForm_default_values;

  client_sources;
  salutations;
  genders;
  races;
  countries;
  residency_status;
  marital_status;
  employment_status;
  educational_levels;

  filteredRaces: Observable<string[]>;

  authForm = this.formBuilder.group({
    // Client Record
    'client_uuid': [null],
    'client_type_slug': [null, Validators.required],
    'display_name': [null, Validators.required],
    'source_slug': [null, Validators.required],
    'description': [null],
    'interest': [null],
    'important': [null],
    'business_name': [null],
    'business_uen': [null],

    // Individual Record
    'salutation_slug': [null],
    'full_name': [null],
    'alias': [null],
    'chinese_name': [null],
    'nric_no': [null],
    'fin_no': [null],
    'passport_no': [null],
    'gender_slug': [null],
    'date_birth': [moment()],
    'race_slug': [null],
    'country_birth_slug': [null],
    'nationality_slug': [null],
    'residency_status_slug': [null],
    'marital_status_slug': [null],
    'smoker': [false],
    'selected': [false],
    'pdpa': [false],
    'education_level_slug': [null],
    'education_institution': [null],
    'field_of_study': [null],
    'employment_status_slug': [null],
    'income_range': [null],
    'job_title': [null],
    'company_name': [null],
    'business_nature': [null],

    // Individual :: Contact
    'email': [null],
    'home_no': [null],
    'mobile_no': [null],
    'fax_no': [null],

    // Individual :: Address
    'block': [null],
    'street': [null],
    'unit': [null],
    'building': [null],
    'city': [null],
    'postal': [null],
    'country_slug': [null],
  });

  get client_uuid() { return this.authForm.controls.client_uuid; }
  get client_type_slug() { return this.authForm.controls.client_type_slug; }
  get display_name() { return this.authForm.controls.display_name; }
  get source_slug() { return this.authForm.controls.source_slug; }
  get description() { return this.authForm.controls.description; }
  get interest() { return this.authForm.controls.interest; }
  get important() { return this.authForm.controls.important; }

  get business_name() { return this.authForm.controls.business_name; }
  get business_uen() { return this.authForm.controls.business_uen; }

  get salutation_slug() { return this.authForm.controls.salutation_slug; }
  get full_name() { return this.authForm.controls.full_name; }
  get alias() { return this.authForm.controls.alias; }
  get chinese_name() { return this.authForm.controls.chinese_name; }
  get nric_no() { return this.authForm.controls.nric_no; }
  get fin_no() { return this.authForm.controls.fin_no; }
  get passport_no() { return this.authForm.controls.passport_no; }
  get gender_slug() { return this.authForm.controls.gender_slug; }
  get date_birth() { return this.authForm.controls.date_birth; }
  get race_slug() { return this.authForm.controls.race_slug; }
  get country_birth_slug() { return this.authForm.controls.country_birth_slug; }
  get nationality_slug() { return this.authForm.controls.nationality_slug; }
  get residency_status_slug() { return this.authForm.controls.residency_status_slug; }
  get marital_status_slug() { return this.authForm.controls.marital_status_slug; }
  get smoker() { return this.authForm.controls.smoker; }
  get selected() { return this.authForm.controls.selected; }
  get pdpa() { return this.authForm.controls.pdpa; }
  get education_level_slug() { return this.authForm.controls.education_level_slug; }
  get education_institution() { return this.authForm.controls.education_institution; }
  get field_of_study() { return this.authForm.controls.field_of_study; }
  get employment_status_slug() { return this.authForm.controls.employment_status_slug; }
  get income_range() { return this.authForm.controls.income_range; }
  get job_title() { return this.authForm.controls.job_title; }
  get company_name() { return this.authForm.controls.company_name; }
  get business_nature() { return this.authForm.controls.business_nature; }

  get email() { return this.authForm.controls.email; }
  get home_no() { return this.authForm.controls.home_no; }
  get mobile_no() { return this.authForm.controls.mobile_no; }
  get fax_no() { return this.authForm.controls.fax_no; }

  get block() { return this.authForm.controls.block; }
  get street() { return this.authForm.controls.street; }
  get unit() { return this.authForm.controls.unit; }
  get building() { return this.authForm.controls.building; }
  get city() { return this.authForm.controls.city; }
  get postal() { return this.authForm.controls.postal; }
  get country_slug() { return this.authForm.controls.country_slug; }


  constructor (
    private formBuilder: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    private apiService: ApiService,
    private pageService: PageService,
    private titleService: TitleService,
    private _notification: MessageService,
    private assClientService: AdminClientRelationshipsService) {
    this.client = this.route.parent.snapshot.data.client;
  }

  ngOnInit() {
    this.getSelections();
  }

  ngOnDestroy() {
    if (this.selectionsSub) this.selectionsSub.unsubscribe();
    if (this.clientUpdateSub) this.clientUpdateSub.unsubscribe();
  }

  private _filter(value, model): string[] {
    const filterValue = (value) ? value.toLowerCase() : '';
    return model.filter(option => option.slug.toLowerCase().includes(filterValue));
  }

  getSelections() {
    this.selectionsSub = this.apiService.post('selections', {
      'lists': [
        'lfa-client-source',
        'salutation',
        'gender',
        'race',
        'country',
        'residency-status',
        'marital-status',
        'employment-status',
        'educational-level',
      ]
    }).subscribe(res => {
      let data = res['data'];
      this.client_sources = data['lfa-client-source'];
      this.salutations = data['salutation'];
      this.genders = data['gender'];
      this.races = data['race'];
      this.countries = data['country'];
      this.residency_status = data['residency-status'];
      this.marital_status = data['marital-status'];
      this.employment_status = data['employment-status'];
      this.educational_levels = data['educational-level'];

      this.filteredRaces = this.race_slug.valueChanges.pipe(startWith(''), map(value => this._filter(value, this.races)) );

      this.updateModel();
    });
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
    if (data) this.client = data;

    // Update page title
    this.titleService.manualTitle(this.client.display_name + ' | Update Client Particulars');

    // Client Record
    this.client_uuid.setValue(this.client.uuid);
    this.client_type_slug.setValue(this.client.client_type_slug);
    this.display_name.setValue(this.client.display_name);
    this.source_slug.setValue(this.client.source_slug);
    this.description.setValue(this.client.description);
    this.interest.setValue(this.client.interest);
    this.important.setValue(this.client.important);

    // Business Information
    if (this.client.business) {
      this.business_name.setValue(this.client.business.name);
      this.business_uen.setValue(this.client.business.uen);
    }

    // Personal Information
    if (this.client.personal) {
      this.salutation_slug.setValue(this.client.personal.salutation_slug);
      this.full_name.setValue(this.client.personal.full_name);
      this.alias.setValue(this.client.personal.alias);
      this.chinese_name.setValue(this.client.personal.chinese_name);
      this.nric_no.setValue(this.client.personal.nric_no);
      this.fin_no.setValue(this.client.personal.fin_no);
      this.passport_no.setValue(this.client.personal.passport_no);
      this.gender_slug.setValue(this.client.personal.gender_slug);
      this.date_birth.setValue(this.client.personal.date_birth);
      this.race_slug.setValue(this.client.personal.race_slug);
      this.country_birth_slug.setValue(this.client.personal.country_birth_slug);
      this.nationality_slug.setValue(this.client.personal.nationality_slug);
      this.residency_status_slug.setValue(this.client.personal.residency_status_slug);
      this.marital_status_slug.setValue(this.client.personal.marital_status_slug);
      this.smoker.setValue(this.client.personal.smoker);
      this.selected.setValue(this.client.personal.selected);
      this.pdpa.setValue(this.client.personal.pdpa);
      this.education_level_slug.setValue(this.client.personal.education_level_slug);
      this.education_institution.setValue(this.client.personal.education_institution);
      this.field_of_study.setValue(this.client.personal.field_of_study);
      this.employment_status_slug.setValue(this.client.personal.employment_status_slug);
      this.job_title.setValue(this.client.personal.job_title);
      this.income_range.setValue(this.client.personal.income_range);
      this.company_name.setValue(this.client.personal.company_name);
      this.business_nature.setValue(this.client.personal.business_nature);
      this.email.setValue(this.client.personal.contact_information.email);
      this.home_no.setValue(this.client.personal.contact_information.home_no);
      this.mobile_no.setValue(this.client.personal.contact_information.mobile_no);
      this.fax_no.setValue(this.client.personal.contact_information.fax_no);
      this.block.setValue(this.client.personal.address_information.block);
      this.street.setValue(this.client.personal.address_information.street);
      this.unit.setValue(this.client.personal.address_information.unit);
      this.building.setValue(this.client.personal.address_information.building);
      this.city.setValue(this.client.personal.address_information.city);
      this.postal.setValue(this.client.personal.address_information.postal);
      this.country_slug.setValue(this.client.personal.address_information.country_slug);
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
    this.clientUpdateSub = this.assClientService.updateClient(this.client.uuid, this.getUpdatedValues()).subscribe((res) => {
      this.router.navigate(['/authenticated/admin/clients/' + this.client.uuid + '/particulars']);
      this.resetForm(null, res.data);
      this.processing = false;
      this.pageService.triggerParentRefresh(true);
      this._notification.create('success', 'Success! Client Profile updated successfully.', { Position: 'top', Style: 'bar', Duration: 2000 });
    });
  }
}
