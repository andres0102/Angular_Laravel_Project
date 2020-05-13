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
export const associate_particulars_date_formats = {
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
  selector: 'admin-associate-particulars',
  templateUrl: './associate-particulars.component.html',
  styleUrls: ['./associate-particulars.component.scss'],
  providers: [
    { provide: MAT_MOMENT_DATE_ADAPTER_OPTIONS, useValue: {useUtc: true} },
    {
      provide: DateAdapter,
      useClass: MomentDateAdapter,
      deps: [MAT_DATE_LOCALE, MAT_MOMENT_DATE_ADAPTER_OPTIONS]
    },
    { provide: MAT_DATE_FORMATS, useValue: associate_particulars_date_formats },
  ],
})
export class AdminAssociateParticularsComponent implements OnInit, OnDestroy {
  private selectionsSub: Subscription;
  private associateUpdateSub: Subscription;

  associate: Associate;
  dataLoaded: boolean = false;
  processing: boolean = false;
  editing: boolean = false;
  authForm_default_values;

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
    private adminSalesAssocService: AdminSalesAssociatesService) {
    this.associate = this.route.parent.snapshot.data.associate;
  }

  ngOnInit() {
    this.getSelections();
  }

  ngOnDestroy() {
    if (this.selectionsSub) this.selectionsSub.unsubscribe();
    if (this.associateUpdateSub) this.associateUpdateSub.unsubscribe();
  }

  private _filter(value, model): string[] {
    const filterValue = (value) ? value.toLowerCase() : '';
    return model.filter(option => option.slug.toLowerCase().includes(filterValue));
  }

  getSelections() {
    this.selectionsSub = this.apiService.post('selections', {
      'lists': [
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
    if (data) this.associate = data;

    // Update page title
    this.titleService.manualTitle(this.associate.personal.full_name + ' | Update Associate Personal Particulars');

    // Personal Information
    if (this.associate.personal) {
      this.salutation_slug.setValue(this.associate.personal.salutation_slug);
      this.full_name.setValue(this.associate.personal.full_name);
      this.alias.setValue(this.associate.personal.alias);
      this.chinese_name.setValue(this.associate.personal.chinese_name);
      this.nric_no.setValue(this.associate.personal.nric_no);
      this.fin_no.setValue(this.associate.personal.fin_no);
      this.passport_no.setValue(this.associate.personal.passport_no);
      this.gender_slug.setValue(this.associate.personal.gender_slug);
      this.date_birth.setValue(this.associate.personal.date_birth);
      this.race_slug.setValue(this.associate.personal.race_slug);
      this.country_birth_slug.setValue(this.associate.personal.country_birth_slug);
      this.nationality_slug.setValue(this.associate.personal.nationality_slug);
      this.residency_status_slug.setValue(this.associate.personal.residency_status_slug);
      this.marital_status_slug.setValue(this.associate.personal.marital_status_slug);
      this.smoker.setValue(this.associate.personal.smoker);
      this.selected.setValue(this.associate.personal.selected);
      this.pdpa.setValue(this.associate.personal.pdpa);
      this.education_level_slug.setValue(this.associate.personal.education_level_slug);
      this.education_institution.setValue(this.associate.personal.education_institution);
      this.field_of_study.setValue(this.associate.personal.field_of_study);
      this.employment_status_slug.setValue(this.associate.personal.employment_status_slug);
      this.job_title.setValue(this.associate.personal.job_title);
      this.income_range.setValue(this.associate.personal.income_range);
      this.company_name.setValue(this.associate.personal.company_name);
      this.business_nature.setValue(this.associate.personal.business_nature);
      this.email.setValue(this.associate.personal.contact_information.email);
      this.home_no.setValue(this.associate.personal.contact_information.home_no);
      this.mobile_no.setValue(this.associate.personal.contact_information.mobile_no);
      this.fax_no.setValue(this.associate.personal.contact_information.fax_no);
      this.block.setValue(this.associate.personal.address_information.block);
      this.street.setValue(this.associate.personal.address_information.street);
      this.unit.setValue(this.associate.personal.address_information.unit);
      this.building.setValue(this.associate.personal.address_information.building);
      this.city.setValue(this.associate.personal.address_information.city);
      this.postal.setValue(this.associate.personal.address_information.postal);
      this.country_slug.setValue(this.associate.personal.address_information.country_slug);
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
      this.router.navigate(['/authenticated/admin/sales-associates/' + this.associate.uuid + '/personal-particulars']);
      this.resetForm(null, res.data);
      this.processing = false;
      this.pageService.triggerParentRefresh(true);
      this._notification.create('success', 'Success! Associate Profile updated successfully.', { Position: 'top', Style: 'bar', Duration: 2000 });
    });
  }
}
