/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Component, OnInit, OnDestroy, Inject, ViewChild } from '@angular/core';
import { FormBuilder, Validators, AbstractControl, FormGroup } from '@angular/forms';
import { Observable, Subscription, Subject } from 'rxjs';
import { map, startWith } from 'rxjs/operators';
import { MAT_DIALOG_DATA, MatStepper, MatAutocompleteTrigger } from '@angular/material';
import { STEPPER_GLOBAL_OPTIONS } from '@angular/cdk/stepper';
import { MomentDateAdapter, MAT_MOMENT_DATE_ADAPTER_OPTIONS } from '@angular/material-moment-adapter';
import { DateAdapter, MAT_DATE_FORMATS, MAT_DATE_LOCALE } from '@angular/material/core';
import { WebcamImage, WebcamInitError, WebcamUtil } from 'ngx-webcam';
import { DomSanitizer, SafeStyle } from '@angular/platform-browser';
import { MatDialogRef } from '@angular/material/dialog';

import { Client } from '@app/models';
import { ApiService } from '@app/@core/services';
import { AssociatesClientRelationshipsService } from '@app/@shared/services/associates/client-relationships/client-relationships.service';

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

import * as Fuse from 'fuse.js';

@Component({
  selector: 'associates-case-submissions-add-dialog',
  templateUrl: './case-submissions-add-dialog.component.html',
  styleUrls: ['./case-submissions-add-dialog.component.scss'],
  providers: [
    { provide: STEPPER_GLOBAL_OPTIONS, useValue: {displayDefaultIndicatorType: false}},
    { provide: MAT_MOMENT_DATE_ADAPTER_OPTIONS, useValue: {useUtc: true} },
    {
      provide: DateAdapter,
      useClass: MomentDateAdapter,
      deps: [MAT_DATE_LOCALE, MAT_MOMENT_DATE_ADAPTER_OPTIONS]
    },
    { provide: MAT_DATE_FORMATS, useValue: MY_FORMATS },
  ]
})
export class AssociatesCaseSubmissionsAddDialogComponent implements OnInit, OnDestroy {

  constructor (
    @Inject(MAT_DIALOG_DATA) private clientsCache: any,
    private formBuilder: FormBuilder,
    private apiService: ApiService,
    sanitizer: DomSanitizer,
    private assClientService: AssociatesClientRelationshipsService,
    public dialogRef: MatDialogRef<AssociatesCaseSubmissionsAddDialogComponent>) {

    this.clients = this.clientsCache;
  }

  @ViewChild('stepper', {static: false}) public stepper: MatStepper;
  @ViewChild("clientInput", {static: false, read: MatAutocompleteTrigger}) clientInput: MatAutocompleteTrigger;

  private subs_getSelections: Subscription;
  private subs_getClients: Subscription;
  private subs_createClient: Subscription;
  private subs_createClientClosed: Subscription;
  private subs_createSubmission: Subscription;

  private client_default_values;
  private clientsFuse;
  public clients = [];
  public clientSelected: boolean = false;
  public clientNew: boolean = false;
  public processing: boolean = false;

  public files: File[] = [];

  // camera
  public idWebcamInit: boolean = false;
  public idWebcamAllow: boolean = true;
  public idWebcamImage_front: WebcamImage = null;
  public idWebcamImage_back: WebcamImage = null;
  private idWebcamTrigger: Subject<void> = new Subject<void>();

  // dropzone
  public idDropzoneInit: boolean = false;
  public idDropzoneImage_front = null;
  public idDropzoneImage_frontFile: File = null;
  public idDropzoneImage_back = null;
  public idDropzoneImage_backFile: File = null;
  public idDropzone_files_uploaded: boolean = false;
  public idDropzone_files: File[] = [];

  // PFR
  public pfrDropzone_files_uploaded: boolean = false;
  public pfrDropzone_files: File[] = [];

  // Proof of Address
  public poaDropzone_files_uploaded: boolean = false;
  public poaDropzone_files: File[] = [];

  // subcheck
  public subcheckDropzone_files_uploaded: boolean = false;
  public subcheckDropzone_files: File[] = [];

  // others
  public othersDropzone_files_uploaded: boolean = false;
  public othersDropzone_files: File[] = [];

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

  public selectClientForm: FormGroup = this.formBuilder.group({
    'client': ['', Validators.required],
    'client_uuid': ['', Validators.required],
  });
  get client() { return this.selectClientForm.controls.client; }

  public updateClientForm: FormGroup = this.formBuilder.group({
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

  get client_uuid() { return this.updateClientForm.controls.client_uuid; }
  get client_type_slug() { return this.updateClientForm.controls.client_type_slug; }
  get display_name() { return this.updateClientForm.controls.display_name; }
  get source_slug() { return this.updateClientForm.controls.source_slug; }
  get description() { return this.updateClientForm.controls.description; }
  get interest() { return this.updateClientForm.controls.interest; }
  get important() { return this.updateClientForm.controls.important; }
  get business_name() { return this.updateClientForm.controls.business_name; }
  get business_uen() { return this.updateClientForm.controls.business_uen; }
  get salutation_slug() { return this.updateClientForm.controls.salutation_slug; }
  get full_name() { return this.updateClientForm.controls.full_name; }
  get alias() { return this.updateClientForm.controls.alias; }
  get chinese_name() { return this.updateClientForm.controls.chinese_name; }
  get nric_no() { return this.updateClientForm.controls.nric_no; }
  get fin_no() { return this.updateClientForm.controls.fin_no; }
  get passport_no() { return this.updateClientForm.controls.passport_no; }
  get gender_slug() { return this.updateClientForm.controls.gender_slug; }
  get date_birth() { return this.updateClientForm.controls.date_birth; }
  get race_slug() { return this.updateClientForm.controls.race_slug; }
  get country_birth_slug() { return this.updateClientForm.controls.country_birth_slug; }
  get nationality_slug() { return this.updateClientForm.controls.nationality_slug; }
  get residency_status_slug() { return this.updateClientForm.controls.residency_status_slug; }
  get marital_status_slug() { return this.updateClientForm.controls.marital_status_slug; }
  get smoker() { return this.updateClientForm.controls.smoker; }
  get selected() { return this.updateClientForm.controls.selected; }
  get pdpa() { return this.updateClientForm.controls.pdpa; }
  get education_level_slug() { return this.updateClientForm.controls.education_level_slug; }
  get education_institution() { return this.updateClientForm.controls.education_institution; }
  get field_of_study() { return this.updateClientForm.controls.field_of_study; }
  get employment_status_slug() { return this.updateClientForm.controls.employment_status_slug; }
  get income_range() { return this.updateClientForm.controls.income_range; }
  get job_title() { return this.updateClientForm.controls.job_title; }
  get company_name() { return this.updateClientForm.controls.company_name; }
  get business_nature() { return this.updateClientForm.controls.business_nature; }
  get email() { return this.updateClientForm.controls.email; }
  get home_no() { return this.updateClientForm.controls.home_no; }
  get mobile_no() { return this.updateClientForm.controls.mobile_no; }
  get fax_no() { return this.updateClientForm.controls.fax_no; }
  get block() { return this.updateClientForm.controls.block; }
  get street() { return this.updateClientForm.controls.street; }
  get unit() { return this.updateClientForm.controls.unit; }
  get building() { return this.updateClientForm.controls.building; }
  get city() { return this.updateClientForm.controls.city; }
  get postal() { return this.updateClientForm.controls.postal; }
  get country_slug() { return this.updateClientForm.controls.country_slug; }

  public uploadClientIdentity: FormGroup = this.formBuilder.group({
    'idWebcamImage_front_name': [null],
    'idWebcamImage_back_name': [null],
  });

  get idWebcamImage_front_name() { return this.uploadClientIdentity.controls.idWebcamImage_front_name; }
  get idWebcamImage_back_name() { return this.uploadClientIdentity.controls.idWebcamImage_back_name; }

  ngOnInit() {
    this.getSelections();
  }

  ngOnDestroy() {
    if (this.subs_getSelections) this.subs_getSelections.unsubscribe();
    if (this.subs_getClients) this.subs_getClients.unsubscribe();
    if (this.subs_createClient) this.subs_createClient.unsubscribe();
    if (this.subs_createClientClosed) this.subs_createClientClosed.unsubscribe();
    if (this.subs_createSubmission) this.subs_createSubmission.unsubscribe();
  }

  getClients() {
    this.subs_getClients = this.assClientService.getClients().subscribe(res => {
      this.clients = res['data'];
      this.clientsCache = res['data'];
      this.clientNew = false;
    });
  }

  createClient() {
    this.clientNew = true;
    this.assClientService.addClientDialog();
    this.subs_createClient = this.assClientService.createdSwal.subscribe((client) => {
      if (this.subs_createClient) this.subs_createClient.unsubscribe();
      if (this.subs_createClientClosed) this.subs_createClientClosed.unsubscribe();
      this.client.setValue(client);
      this.processClient(true);
      this.getClients();
    });
    this.subs_createClientClosed = this.assClientService.createdSwalClosed.subscribe(() => {
      if (this.subs_createClient) this.subs_createClient.unsubscribe();
      if (this.subs_createClientClosed) this.subs_createClientClosed.unsubscribe();
      this.clientNew = false;
    });
  }

  displayFn(client?: Client): string | undefined {
    return client ? client.display_name : undefined;
  }

  fuseResults() {
    if (this.client.value) {
      this.clientsFuse = new Fuse(this.clientsCache, {
        shouldSort: true,
        findAllMatches: true,
        // includeMatches: true,
        threshold: 0.3,
        location: 0,
        distance: 100,
        maxPatternLength: 32,
        keys: ['display_name'],
      });
      this.clients = this.clientsFuse.search(this.client.value);
    } else {
      this.clients = this.clientsCache;
    }
  }

  processClient(selected = true) {
    if (selected) {
      this.selectClientForm.get('client_uuid').setValue(this.client.value.uuid);
      this.updateModel(this.client.value);
      this.stepper.next();
    } else {
      this.selectClientForm.get('client_uuid').reset();
      this.updateModel();
    }

    // console.log('Client Selected', this.client.value);
    // console.log('Client UUID', this.selectClientForm.get('client_uuid').value);
    // if (this.clientSelected) return;
    // if (this.type.value == 'fa-representative') {
    //   this.display_name.setValue(this.associate.value.name);
    //   this.processUsername();
    // }
  }

  resetForm(ref = null, data = null) {
    if (ref == 'client') this.processClient(false);

    if (!ref) {
      this.selectClientForm.reset();
    } else {
      const form_field = this.selectClientForm.get(ref);
      if (!form_field.validator) return form_field.reset();
      const validator = form_field.validator({} as AbstractControl);
      if (validator && validator.required) form_field.setValue('');
      else form_field.reset();
    }
  }

  resetForm_updateClient(ref = null, data = null) {
    if (!ref) {
      this.updateClientForm.reset();
    } else {
      const form_field = this.updateClientForm.get(ref);
      if (!form_field.validator) return form_field.reset();
      const validator = form_field.validator({} as AbstractControl);
      if (validator && validator.required) form_field.setValue('');
      else form_field.reset();
    }
  }

  resetForm_uploadIdentity(ref = null, data = null) {
    if (!ref) {
      this.uploadClientIdentity.reset();
    } else {
      const form_field = this.uploadClientIdentity.get(ref);
      if (!form_field.validator) return form_field.reset();
      const validator = form_field.validator({} as AbstractControl);
      if (validator && validator.required) form_field.setValue('');
      else form_field.reset();
    }
  }

  submitForm() {
    this.processing = true;
    // this.subs_createSubmission =
    let form_data = new FormData();
    form_data.append('client_uuid', this.client_uuid.value);
    form_data.append('updates', JSON.stringify(this.getUpdatedValues()));
    // form_data['client_uuid'] = this.client_uuid.value;
    // form_data['updates'] = this.getUpdatedValues();
    if (this.idWebcamInit) {
      this.files = [];
      // Front ID
      let front_image_blob = this.dataURItoBlob(this.idWebcamImage_front.imageAsDataUrl);
      let front_image_name = (this.idWebcamImage_front_name.value || 'Captured Image - ID Card (Front)') + '.jpg';
      let front_file = new File([front_image_blob], (front_image_name), { type: 'image/jpeg' });
      this.files.push(front_file);
      // Back ID
      let back_image_blob = this.dataURItoBlob(this.idWebcamImage_back.imageAsDataUrl);
      let back_image_name = (this.idWebcamImage_back_name.value || 'Captured Image - ID Card (Back)') + '.jpg';
      let back_file = new File([back_image_blob], (back_image_name), { type: 'image/jpeg' });
      this.files.push(back_file);
      // form_data['uploads'] = this.files;
      // form_data.append('uploads', this.files);
      // form_data.append('uploads', front_file);
    } else if (this.idDropzoneInit) {
      this.files = [];
      if (this.idDropzoneImage_frontFile) this.files.push(this.idDropzoneImage_frontFile);
      if (this.idDropzoneImage_backFile) this.files.push(this.idDropzoneImage_backFile);
      this.files = this.files.concat(this.idDropzone_files);
      // form_data['uploads'] = this.files;
      // form_data.append('uploads', this.files);
    }

    for (var i = 0; i < this.files.length; i++) {
      form_data.append("identity[]", this.files[i], this.files[i].name);
    }

    for (var i = 0; i < this.pfrDropzone_files.length; i++) {
      form_data.append("pfr[]", this.pfrDropzone_files[i], this.pfrDropzone_files[i].name);
    }

    for (var i = 0; i < this.poaDropzone_files.length; i++) {
      form_data.append("poa[]", this.poaDropzone_files[i], this.poaDropzone_files[i].name);
    }

    for (var i = 0; i < this.subcheckDropzone_files.length; i++) {
      form_data.append("sc[]", this.subcheckDropzone_files[i], this.subcheckDropzone_files[i].name);
    }

    for (var i = 0; i < this.othersDropzone_files.length; i++) {
      form_data.append("others[]", this.othersDropzone_files[i], this.othersDropzone_files[i].name);
    }


    // console.log("Submitting", form_data);

    // Create Submission Record
    this.apiService.upload('associates/submissions', form_data).subscribe((res) => {
      // console.log("POST submissions", res);
      if (res.error === false) {
        // Submission Created
        this.dialogRef.close(res);
      }
      this.processing = false;
    });
  }

  showStepper() {
    console.log(this.stepper);
  }

  private _filter(value, model): string[] {
    const filterValue = (value) ? value.toLowerCase() : '';
    return model.filter(option => option.slug.toLowerCase().includes(filterValue));
  }

  getSelections() {
    this.subs_getSelections = this.apiService.post('selections', {
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

    });
  }

  updateModel(data = null) {
    if (data) {
      // Client Record
      this.updateClientForm.patchValue({
        'client_uuid': data.uuid,
        'client_type_slug': data.client_type_slug,
        'display_name': data.display_name,
        'source_slug': data.source_slug,
        'description': data.description,
        'interest': data.interest,
        'important': data.important,
        'business_name': data.business_name,
        'business_uen': data.business_uen,
      });

      if (data.business) {
        // Business Record
        this.updateClientForm.patchValue({
          'business_name': data.business.name,
          'business_uen': data.business.uen,
        });
      }

      if (data.personal) {
        // Individual Record
        this.updateClientForm.patchValue({
          'salutation_slug': data.personal.salutation_slug,
          'full_name': data.personal.full_name,
          'alias': data.personal.alias,
          'chinese_name': data.personal.chinese_name,
          'nric_no': data.personal.nric_no,
          'fin_no': data.personal.fin_no,
          'passport_no': data.personal.passport_no,
          'gender_slug': data.personal.gender_slug,
          'date_birth': data.personal.date_birth,
          'race_slug': data.personal.race_slug,
          'country_birth_slug': data.personal.country_birth_slug,
          'nationality_slug': data.personal.nationality_slug,
          'residency_status_slug': data.personal.residency_status_slug,
          'marital_status_slug': data.personal.marital_status_slug,
          'smoker': data.personal.smoker,
          'selected': data.personal.selected,
          'pdpa': data.personal.pdpa,
          'education_level_slug': data.personal.education_level_slug,
          'education_institution': data.personal.education_institution,
          'field_of_study': data.personal.field_of_study,
          'employment_status_slug': data.personal.employment_status_slug,
          'income_range': data.personal.income_range,
          'job_title': data.personal.job_title,
          'company_name': data.personal.company_name,
          'business_nature': data.personal.business_nature,

          // Individual :: Contact
          'email': data.personal.contact_information.email,
          'home_no': data.personal.contact_information.home_no,
          'mobile_no': data.personal.contact_information.mobile_no,
          'fax_no': data.personal.contact_information.fax_no,

          // Individual :: Address
          'block': data.personal.address_information.block,
          'street': data.personal.address_information.street,
          'unit': data.personal.address_information.unit,
          'building': data.personal.address_information.building,
          'city': data.personal.address_information.city,
          'postal': data.personal.address_information.postal,
          'country_slug': data.personal.address_information.country_slug,
        });
      }
      this.client_default_values = this.updateClientForm.value;
      this.clientSelected = true;
    } else {
      this.updateClientForm.reset();
      this.clientSelected = false;
    }
  }

  getUpdatedValues() {
    if (this.client_default_values) {
      const updatedFormValues = {};
      this.updateClientForm['_forEachChild']((control, name) => {
        if (control.value != this.client_default_values[name]) {
            updatedFormValues[name] = control.value;
        }
      });
      return updatedFormValues;
    } else {
      return null;
    }
  }

  scrollToWebcam() {
    if (this.idWebcamAllow) {
      setTimeout(() => {
        let webcam = document.getElementsByTagName('webcam')[0];
        let webcamHeight = webcam['offsetHeight'] || 0;
        let containerHeight = webcam['offsetParent']['offsetParent']['offsetHeight'] || 0;
        let heightDifference = (webcamHeight && containerHeight) ? ((containerHeight - webcamHeight) / 2) : 0;
        let offset = webcam['offsetTop'] + webcam['offsetParent']['offsetTop'] + webcam['offsetParent']['offsetParent']['offsetTop'] - heightDifference;
        this.stepper['_elementRef']['nativeElement'].closest('.mat-dialog-content').scrollTo({
          top: offset,
          behavior: 'smooth'
        });
      });
    }
  }

  public handleWebcamError(error: WebcamInitError): void {
    if (error.mediaStreamError && error.mediaStreamError.name === "NotAllowedError") {
      this.idWebcamAllow = false;
    }
  }

  public get idTriggerObservable(): Observable<void> {
    return this.idWebcamTrigger.asObservable();
  }

  public idTriggerSnapshot(): void {
    this.idWebcamTrigger.next();
  }

  resetUploadInterface() {
    this.idWebcamInit = false;
    this.idDropzoneInit = false;
    this.resetWebcamImages();
    this.resetDropzoneFiles();
    this.files = [];
  }

  resetWebcamImages() {
    this.idWebcamImage_front = null;
    this.idWebcamImage_back = null;
    this.uploadClientIdentity.reset();
  }

  resetDropzoneFiles() {
    this.idDropzoneImage_front = null;
    this.idDropzoneImage_frontFile = null;
    this.idDropzoneImage_back = null;
    this.idDropzoneImage_backFile = null;
    this.idDropzone_files = [];
    this.idDropzone_files_uploaded = false;
  }

  public handleWebcamImage(webcamImage: WebcamImage): void {
    // console.info('received webcam image', webcamImage);
    if (!this.idWebcamImage_front) {
      this.idWebcamImage_front = webcamImage;
    } else if (!this.idWebcamImage_back) {
      this.idWebcamImage_back = webcamImage;
    }
  }

  dataURItoBlob(dataURI) {
    // convert base64/URLEncoded data component to raw binary data held in a string
    var byteString;
    if (dataURI.split(',')[0].indexOf('base64') >= 0) byteString = atob(dataURI.split(',')[1]);
    else byteString = unescape(dataURI.split(',')[1]);
    // separate out the mime component
    var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];
    // write the bytes of the string to a typed array
    var ia = new Uint8Array(byteString.length);
    for (var i = 0; i < byteString.length; i++) {
        ia[i] = byteString.charCodeAt(i);
    }
    return new Blob([ia], {type:mimeString});
  }


  onDropzoneSelectSingle(event, index) {
    let file = event.addedFiles[0];
    // Store the files
    if (index == 'front') this.idDropzoneImage_frontFile = file;
    else if (index == 'back') this.idDropzoneImage_backFile = file;

    this.readFile(file).then(fileContents => {
      // Put this string in a request body to upload it to an API.
      if (index == 'front') this.idDropzoneImage_front = fileContents;
      else if (index == 'back') this.idDropzoneImage_back = fileContents;
    });
  }

  onDropzoneRemove(index) {
    if (index == 'front') {
      this.idDropzoneImage_front = null;
      this.idDropzoneImage_frontFile = null;
    } else if (index == 'back') {
      this.idDropzoneImage_back = null;
      this.idDropzoneImage_backFile = null;
    } else if (index == 'multiple') {
      this.resetDropzoneFiles();
    }
  }

  upload_types = [
    {'title': 'Proof of Address', 'slug': 'proof-of-address', 'icon': 'cloud', 'optional': true},
    {'title': 'Personal Financial Records (PFR)', 'slug': 'pfr', 'icon': 'cloud', 'optional': false},
    {'title': 'Submission Checklist', 'slug': 'submission-checklist', 'icon': 'cloud', 'optional': false},
    {'title': 'Other Documents', 'slug': 'other-documents', 'icon': 'cloud', 'optional': true},
  ];

  filesQueryRef(type, ref = "files") {
    switch (type) {
      case 'client-identity': {
        //
        break;
      }
      case 'proof-of-address': {
        return (ref == 'files') ? this.poaDropzone_files : this.poaDropzone_files_uploaded;
        break;
      }
      case 'pfr': {
        return (ref == 'files') ? this.pfrDropzone_files : this.pfrDropzone_files_uploaded;
        break;
      }
      case 'submission-checklist': {
        return (ref == 'files') ? this.subcheckDropzone_files : this.subcheckDropzone_files_uploaded;
        break;
      }
      case 'other-documents': {
        return (ref == 'files') ? this.othersDropzone_files : this.othersDropzone_files_uploaded;
        break;
      }
    }
  }

  onDropzoneSelectFiles(event, type) {
    switch (type) {
      case 'client-identity': {
        this.idDropzone_files.push(...event.addedFiles);
        this.idDropzone_files_uploaded = true;
        break;
      }
      case 'proof-of-address': {
        this.poaDropzone_files.push(...event.addedFiles);
        this.poaDropzone_files_uploaded = true;
        break;
      }
      case 'pfr': {
        this.pfrDropzone_files.push(...event.addedFiles);
        this.pfrDropzone_files_uploaded = true;
        break;
      }
      case 'submission-checklist': {
        this.subcheckDropzone_files.push(...event.addedFiles);
        this.subcheckDropzone_files_uploaded = true;
        break;
      }
      case 'other-documents': {
        this.othersDropzone_files.push(...event.addedFiles);
        this.othersDropzone_files_uploaded = true;
        break;
      }
    }
  }

  onDropzoneRemoveFiles(event, type) {
    switch (type) {
      case 'client-identity': {
        //
        break;
      }
      case 'proof-of-address': {
        this.poaDropzone_files.splice(this.poaDropzone_files.indexOf(event), 1);
        break;
      }
      case 'pfr': {
        this.pfrDropzone_files.splice(this.pfrDropzone_files.indexOf(event), 1);
        break;
      }
      case 'submission-checklist': {
        this.subcheckDropzone_files.splice(this.subcheckDropzone_files.indexOf(event), 1);
        break;
      }
      case 'other-documents': {
        this.othersDropzone_files.splice(this.othersDropzone_files.indexOf(event), 1);
        break;
      }
    }
  }


  private async readFile(file: File): Promise<string | ArrayBuffer> {
    return new Promise<string | ArrayBuffer>((resolve, reject) => {
      const reader = new FileReader();

      reader.onload = e => {
        return resolve((e.target as FileReader).result);
      };

      reader.onerror = e => {
        // console.error(`FileReader failed on file ${file.name}.`);
        return reject(null);
      };

      if (!file) {
        // console.error('No file to read.');
        return reject(null);
      }

      reader.readAsDataURL(file);
    });
  }
}