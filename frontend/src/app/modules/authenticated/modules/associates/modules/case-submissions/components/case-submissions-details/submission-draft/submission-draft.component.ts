/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import {
  Component,
  OnInit,
  OnDestroy,
  Inject,
  ViewChild,
  ViewChildren, QueryList,
  ChangeDetectorRef
} from "@angular/core";
import { ActivatedRoute } from "@angular/router";
import { MAT_BOTTOM_SHEET_DATA, MatStepper, MatAutocompleteTrigger } from "@angular/material";
import {
  FormBuilder,
  Validators,
  AbstractControl,
  FormGroup,
  FormArray
} from "@angular/forms";
import { Observable, Subscription, Subject } from "rxjs";
import { map, startWith } from "rxjs/operators";
import { MatBottomSheetRef } from "@angular/material/bottom-sheet";
import { STEPPER_GLOBAL_OPTIONS } from "@angular/cdk/stepper";
import {
  MomentDateAdapter,
  MAT_MOMENT_DATE_ADAPTER_OPTIONS
} from "@angular/material-moment-adapter";
import {
  DateAdapter,
  MAT_DATE_FORMATS,
  MAT_DATE_LOCALE
} from "@angular/material/core";

import { Submission } from "@app/models";
import { ApiService, AuthService } from "@app/@core/services";
import { PageService } from "@auth/services";
import { AssociatesCaseSubmissionsService } from "@app/@shared/services/associates/case-submissions/case-submissions.service";

// Depending on whether rollup is used, moment needs to be imported differently.
// Since Moment.js doesn't have a default export, we normally need to import using the `* as`
// syntax. However, rollup creates a synthetic default module and we thus need to import it using
// the `default as` syntax.
import * as _moment from "moment";
// tslint:disable-next-line:no-duplicate-imports
import { default as _rollupMoment } from "moment";

const moment = _rollupMoment || _moment;

// See the Moment.js docs for the meaning of these formats:
// https://momentjs.com/docs/#/displaying/format/
export const sub_draft_date_formats = {
  parse: {
    dateInput: "LL"
  },
  display: {
    dateInput: "LL",
    monthYearLabel: "MMM YYYY",
    dateA11yLabel: "LL",
    monthYearA11yLabel: "MMMM YYYY"
  }
};

import * as Fuse from "fuse.js";

@Component({
  selector: "associates-submission-draft",
  templateUrl: "./submission-draft.component.html",
  styleUrls: ["./submission-draft.component.scss"],
  providers: [
    {
      provide: STEPPER_GLOBAL_OPTIONS,
      useValue: { displayDefaultIndicatorType: false }
    },
    { provide: MAT_MOMENT_DATE_ADAPTER_OPTIONS, useValue: { useUtc: true } },
    {
      provide: DateAdapter,
      useClass: MomentDateAdapter,
      deps: [MAT_DATE_LOCALE, MAT_MOMENT_DATE_ADAPTER_OPTIONS]
    },
    { provide: MAT_DATE_FORMATS, useValue: sub_draft_date_formats }
  ]
})
export class AssociatesSubmissionDraftComponent implements OnInit, OnDestroy {
  constructor(
    @Inject(MAT_BOTTOM_SHEET_DATA) public submission_data: any,
    private formBuilder: FormBuilder,
    private mbsRef: MatBottomSheetRef,
    private cdr: ChangeDetectorRef,
    private route: ActivatedRoute,
    public authService: AuthService,
    private pageService: PageService,
    private apiService: ApiService,
    private assSubmissionService: AssociatesCaseSubmissionsService
  ) {}

  @ViewChild("stepper", { static: false }) public stepper: MatStepper;
  @ViewChild("insuranceOptionInput", {static: false, read: MatAutocompleteTrigger}) insuranceOptionInput: MatAutocompleteTrigger;
  @ViewChild("insuranceLifeAssuredInput", {static: false, read: MatAutocompleteTrigger}) insuranceLifeAssuredInput: MatAutocompleteTrigger;
  @ViewChild("investmentOptionInput", {static: false, read: MatAutocompleteTrigger}) investmentOptionInput: MatAutocompleteTrigger;
  // @ViewChildren(MatAutocompleteTrigger) autocompletes: QueryList<MatAutocompleteTrigger>;


  private subs_getSelections: Subscription;
  private subs_getProducts: Subscription;
  private subs_getProductOptions: Subscription;
  private subs_getLifeAssured: Subscription;

  gst_rate = 0.07;

  is_update: boolean = false;
  processing = new Subject<boolean>();
  selectionsLoaded = new Subject<boolean>();

  select_submission_category;
  select_product_category;
  select_product_category_gi;
  select_providers;
  select_payment_mode;
  select_currency;
  select_product_coverage;
  select_product;
  select_option;
  select_provider_option_all;
  select_provider_option;
  select_riders;
  select_riders_all;
  select_relationship;
  salutations;
  genders;
  races;
  countries;
  residency_status;
  marital_status;
  employment_status;
  educational_levels;
  filteredRaces: Observable<string[]>;

  lifeAssured_cache;

  insurance_options;
  insurance_optionFuse;
  insurance_riderFuse;
  insurance_riders_list;
  insurance_optionSelected: boolean = false;
  insurance_optionSelected_old: boolean = false;
  insurance_lifeAssured_active: boolean = false;
  insurance_lifeAssured_list;
  insurance_lifeAssured_fuse;
  insurance_lifeAssured_new: boolean = false;
  insurance_lifeAssured_selected: boolean = false;

  investment_options;
  investment_optionFuse;
  investment_optionSelected: boolean = false;

  // dropzone
  public files: File[] = [];
  public idDropzoneInit: boolean = false;
  public idDropzone_files_uploaded: boolean = false;
  public idDropzone_files: File[] = [];

  public caseDetails_form: FormGroup = this.formBuilder.group({
    // Submission Date
    submission_cat_slug: [null, Validators.required],
    date_submission: [moment(), Validators.required]
  });
  get submission_cat_slug() {
    return this.caseDetails_form.controls.submission_cat_slug;
  }
  get date_submission() {
    return this.caseDetails_form.controls.date_submission;
  }

  public policy_insurance_form: FormGroup = this.formBuilder.group({
    life_assured_type: [null, Validators.required],
    life_assured_is_client: [null],
    submission_mode: [null, Validators.required],
    provider_slug: [null, Validators.required],
    product_option: ['', Validators.required],
    product_option_uuid: [''],
    payment_mode_slug: [null, Validators.required],
    currency: [null, Validators.required],
    ape: [null],
    sum_assured: [null, [Validators.max(9999999999)]],
    policy_term: [null, [Validators.required, Validators.min(1), Validators.max(99)]],
    payment_term: [null, [Validators.required, Validators.min(1), Validators.max(99)]],
    payment_frequency: [null, Validators.required],
    payment_type: [null, Validators.required],
    gross_payment_after_gst: [0, [Validators.required, Validators.min(0), Validators.max(9999999999)]],
    gross_payment_before_gst: [0],
    gross_payment_gst: [0],
    payment_discount: [0],
    nett_payment_after_gst: [0],
    nett_payment_before_gst: [0],
    nett_payment_gst: [0],
    gst_rate: [0],
    gst_compute: [false],
    life_assured: [null],
    life_assured_uuid: [null],

    // Life Assured Record
    // Individual Record
    relationship_type_slug: [null],
    salutation_slug: [null],
    full_name: [null],
    alias: [null],
    chinese_name: [null],
    nric_no: [null],
    fin_no: [null],
    passport_no: [null],
    gender_slug: [null],
    date_birth: [moment()],
    race_slug: [null],
    country_birth_slug: [null],
    nationality_slug: [null],
    residency_status_slug: [null],
    marital_status_slug: [null],
    smoker: [false],
    selected: [false],
    pdpa: [false],
    education_level_slug: [null],
    education_institution: [null],
    field_of_study: [null],
    employment_status_slug: [null],
    income_range: [null],
    job_title: [null],
    company_name: [null],
    business_nature: [null],

    // Individual :: Contact
    email: [null],
    home_no: [null],
    mobile_no: [null],
    fax_no: [null],

    // Individual :: Address
    block: [null],
    street: [null],
    unit: [null],
    building: [null],
    city: [null],
    postal: [null],
    country_slug: [null]
  });
  get insurance_life_assured_is_client() {
    return this.policy_insurance_form.controls.life_assured_is_client;
  }
  get insurance_life_assured_type() {
    return this.policy_insurance_form.controls.life_assured_type;
  }
  get insurance_provider_slug() {
    return this.policy_insurance_form.controls.provider_slug;
  }
  get insurance_product_option() {
    return this.policy_insurance_form.controls.product_option;
  }
  get insurance_product_option_uuid() {
    return this.policy_insurance_form.controls.product_option_uuid;
  }
  get insurance_payment_mode_slug() {
    return this.policy_insurance_form.controls.payment_mode_slug;
  }
  get insurance_currency() {
    return this.policy_insurance_form.controls.currency;
  }
  get insurance_ape() {
    return this.policy_insurance_form.controls.ape;
  }
  get insurance_sum_assured() {
    return this.policy_insurance_form.controls.sum_assured;
  }
  get insurance_policy_term() {
    return this.policy_insurance_form.controls.policy_term;
  }
  get insurance_payment_term() {
    return this.policy_insurance_form.controls.payment_term;
  }
  get insurance_payment_frequency() {
    return this.policy_insurance_form.controls.payment_frequency;
  }
  get insurance_payment_type() {
    return this.policy_insurance_form.controls.payment_type;
  }
  get insurance_gross_payment_after_gst() {
    return this.policy_insurance_form.controls.gross_payment_after_gst;
  }
  get insurance_gross_payment_before_gst() {
    return this.policy_insurance_form.controls.gross_payment_before_gst;
  }
  get insurance_gross_payment_gst() {
    return this.policy_insurance_form.controls.gross_payment_gst;
  }
  get insurance_payment_discount() {
    return this.policy_insurance_form.controls.payment_discount;
  }
  get insurance_nett_payment_after_gst() {
    return this.policy_insurance_form.controls.nett_payment_after_gst;
  }
  get insurance_nett_payment_before_gst() {
    return this.policy_insurance_form.controls.nett_payment_before_gst;
  }
  get insurance_nett_payment_gst() {
    return this.policy_insurance_form.controls.nett_payment_gst;
  }
  get insurance_gst_compute() {
    return this.policy_insurance_form.controls.gst_compute;
  }
  get insurance_gst_rate() {
    return this.policy_insurance_form.controls.gst_rate;
  }
  get insurance_submission_mode() {
    return this.policy_insurance_form.controls.submission_mode;
  }
  //
  get insurance_life_assured() {
    return this.policy_insurance_form.controls.life_assured;
  }
  get insurance_life_assured_uuid() {
    return this.policy_insurance_form.controls.life_assured_uuid;
  }
  get insurance_life_assured_relationship_type_slug() {
    return this.policy_insurance_form.controls.relationship_type_slug;
  }
  get salutation_slug() {
    return this.policy_insurance_form.controls.salutation_slug;
  }
  get full_name() {
    return this.policy_insurance_form.controls.full_name;
  }
  get alias() {
    return this.policy_insurance_form.controls.alias;
  }
  get chinese_name() {
    return this.policy_insurance_form.controls.chinese_name;
  }
  get nric_no() {
    return this.policy_insurance_form.controls.nric_no;
  }
  get fin_no() {
    return this.policy_insurance_form.controls.fin_no;
  }
  get passport_no() {
    return this.policy_insurance_form.controls.passport_no;
  }
  get gender_slug() {
    return this.policy_insurance_form.controls.gender_slug;
  }
  get date_birth() {
    return this.policy_insurance_form.controls.date_birth;
  }
  get race_slug() {
    return this.policy_insurance_form.controls.race_slug;
  }
  get country_birth_slug() {
    return this.policy_insurance_form.controls.country_birth_slug;
  }
  get nationality_slug() {
    return this.policy_insurance_form.controls.nationality_slug;
  }
  get residency_status_slug() {
    return this.policy_insurance_form.controls.residency_status_slug;
  }
  get marital_status_slug() {
    return this.policy_insurance_form.controls.marital_status_slug;
  }
  get smoker() {
    return this.policy_insurance_form.controls.smoker;
  }
  get selected() {
    return this.policy_insurance_form.controls.selected;
  }
  get pdpa() {
    return this.policy_insurance_form.controls.pdpa;
  }
  get education_level_slug() {
    return this.policy_insurance_form.controls.education_level_slug;
  }
  get education_institution() {
    return this.policy_insurance_form.controls.education_institution;
  }
  get field_of_study() {
    return this.policy_insurance_form.controls.field_of_study;
  }
  get employment_status_slug() {
    return this.policy_insurance_form.controls.employment_status_slug;
  }
  get income_range() {
    return this.policy_insurance_form.controls.income_range;
  }
  get job_title() {
    return this.policy_insurance_form.controls.job_title;
  }
  get company_name() {
    return this.policy_insurance_form.controls.company_name;
  }
  get business_nature() {
    return this.policy_insurance_form.controls.business_nature;
  }
  get email() {
    return this.policy_insurance_form.controls.email;
  }
  get home_no() {
    return this.policy_insurance_form.controls.home_no;
  }
  get mobile_no() {
    return this.policy_insurance_form.controls.mobile_no;
  }
  get fax_no() {
    return this.policy_insurance_form.controls.fax_no;
  }
  get block() {
    return this.policy_insurance_form.controls.block;
  }
  get street() {
    return this.policy_insurance_form.controls.street;
  }
  get unit() {
    return this.policy_insurance_form.controls.unit;
  }
  get building() {
    return this.policy_insurance_form.controls.building;
  }
  get city() {
    return this.policy_insurance_form.controls.city;
  }
  get postal() {
    return this.policy_insurance_form.controls.postal;
  }
  get country_slug() {
    return this.policy_insurance_form.controls.country_slug;
  }

  public policy_insurance_payment_form: FormGroup = this.formBuilder.group({
    rider: "",
    payment_list: this.formBuilder.array([])
  });
  get rider() {
    return this.policy_insurance_payment_form.controls.rider;
  }
  get payment_list(): FormArray {
    return <FormArray>this.policy_insurance_payment_form.controls.payment_list;
  }

  public policy_investment_form: FormGroup = this.formBuilder.group({
    provider_slug: [null, Validators.required],
    product_option: [null, Validators.required],
    product_option_uuid: [null],
    submission_mode: [null, Validators.required],
    payment_mode_slug: [null, Validators.required],
    currency: [null, Validators.required],
    ape: [null],
    payment_term: [null, [Validators.required, Validators.min(1), Validators.max(99)]],
    payment_frequency: [null, Validators.required],
    payment_type: [null, Validators.required],
    gross_payment_after_gst: [0, [Validators.required, Validators.min(0), Validators.max(9999999999)]],
    gross_payment_before_gst: [0],
    gross_payment_gst: [0],
    payment_discount: [0],
    nett_payment_after_gst: [0],
    nett_payment_before_gst: [0],
    nett_payment_gst: [0],
    gst_rate: [0],
    gst_compute: [false],
    // Investments / Loans
    investment_transaction_type: [null, Validators.required],
    investment_account_type: [null, Validators.required]
  });
  get investment_submission_mode() {
    return this.policy_investment_form.controls.submission_mode;
  }
  get investment_provider_slug() {
    return this.policy_investment_form.controls.provider_slug;
  }
  get investment_product_option() {
    return this.policy_investment_form.controls.product_option;
  }
  get investment_product_option_uuid() {
    return this.policy_investment_form.controls.product_option_uuid;
  }
  get investment_payment_mode_slug() {
    return this.policy_investment_form.controls.payment_mode_slug;
  }
  get investment_currency() {
    return this.policy_investment_form.controls.currency;
  }
  get investment_ape() {
    return this.policy_investment_form.controls.ape;
  }
  get investment_payment_term() {
    return this.policy_investment_form.controls.payment_term;
  }
  get investment_payment_frequency() {
    return this.policy_investment_form.controls.payment_frequency;
  }
  get investment_payment_type() {
    return this.policy_investment_form.controls.payment_type;
  }
  get investment_gross_payment_after_gst() {
    return this.policy_investment_form.controls.gross_payment_after_gst;
  }
  get investment_gross_payment_before_gst() {
    return this.policy_investment_form.controls.gross_payment_before_gst;
  }
  get investment_gross_payment_gst() {
    return this.policy_investment_form.controls.gross_payment_gst;
  }
  get investment_payment_discount() {
    return this.policy_investment_form.controls.payment_discount;
  }
  get investment_nett_payment_after_gst() {
    return this.policy_investment_form.controls.nett_payment_after_gst;
  }
  get investment_nett_payment_before_gst() {
    return this.policy_investment_form.controls.nett_payment_before_gst;
  }
  get investment_nett_payment_gst() {
    return this.policy_investment_form.controls.nett_payment_gst;
  }
  get investment_gst_rate() {
    return this.policy_investment_form.controls.gst_rate;
  }
  get investment_gst_compute() {
    return this.policy_investment_form.controls.gst_compute;
  }
  //
  get investment_transaction_type() {
    return this.policy_investment_form.controls.investment_transaction_type;
  }
  get investment_account_type() {
    return this.policy_investment_form.controls.investment_account_type;
  }

  public policy_gi_form: FormGroup = this.formBuilder.group({
    provider_slug: [null, Validators.required],
    product_cat: [null, Validators.required],
    product_cat_slug: [null],
    submission_mode: [null, Validators.required],
    payment_mode_slug: [null, Validators.required],
    currency: [null, Validators.required],
    ape: [null],
    payment_term: [null, [Validators.required, Validators.min(1), Validators.max(99)]],
    payment_frequency: [null, Validators.required],
    payment_type: [null, Validators.required],
    gross_payment_after_gst: [0, [Validators.required, Validators.min(0), Validators.max(9999999999)]],
    gross_payment_before_gst: [0],
    gross_payment_gst: [0],
    payment_discount: [0],
    nett_payment_after_gst: [0],
    nett_payment_before_gst: [0],
    nett_payment_gst: [0],
    gst_rate: [0],
    gst_compute: [false]
  });
  get gi_provider_slug() {
    return this.policy_gi_form.controls.provider_slug;
  }
  get gi_product_cat() {
    return this.policy_gi_form.controls.product_cat;
  }
  get gi_product_cat_slug() {
    return this.policy_gi_form.controls.product_cat_slug;
  }
  get gi_submission_mode() {
    return this.policy_gi_form.controls.submission_mode;
  }
  get gi_payment_mode_slug() {
    return this.policy_gi_form.controls.payment_mode_slug;
  }
  get gi_currency() {
    return this.policy_gi_form.controls.currency;
  }
  get gi_ape() {
    return this.policy_gi_form.controls.ape;
  }
  get gi_payment_term() {
    return this.policy_gi_form.controls.payment_term;
  }
  get gi_payment_frequency() {
    return this.policy_gi_form.controls.payment_frequency;
  }
  get gi_payment_type() {
    return this.policy_gi_form.controls.payment_type;
  }
  get gi_gross_payment_after_gst() {
    return this.policy_gi_form.controls.gross_payment_after_gst;
  }
  get gi_gross_payment_before_gst() {
    return this.policy_gi_form.controls.gross_payment_before_gst;
  }
  get gi_gross_payment_gst() {
    return this.policy_gi_form.controls.gross_payment_gst;
  }
  get gi_payment_discount() {
    return this.policy_gi_form.controls.payment_discount;
  }
  get gi_nett_payment_after_gst() {
    return this.policy_gi_form.controls.nett_payment_after_gst;
  }
  get gi_nett_payment_before_gst() {
    return this.policy_gi_form.controls.nett_payment_before_gst;
  }
  get gi_nett_payment_gst() {
    return this.policy_gi_form.controls.nett_payment_gst;
  }
  get gi_gst_rate() {
    return this.policy_gi_form.controls.gst_rate;
  }
  get gi_gst_compute() {
    return this.policy_gi_form.controls.gst_compute;
  }

  public policy_loans_form: FormGroup = this.formBuilder.group({
    loan_property_address: [null],
    loan_consent: [null],
    loan_amount: [0, [Validators.required, Validators.min(0), Validators.max(9999999999)]],
  });
  get loan_property_address() {
    return this.policy_investment_form.controls.loan_property_address;
  }
  get loan_consent() {
    return this.policy_investment_form.controls.loan_consent;
  }
  get loan_amount() {
    return this.policy_investment_form.controls.loan_amount;
  }

  public policy_wills_form: FormGroup = this.formBuilder.group({
    provider_slug: [null, Validators.required],
    payment_mode_slug: [null, Validators.required],
    submission_mode: [null, Validators.required]
  });
  get wills_provider_slug() {
    return this.policy_wills_form.controls.provider_slug;
  }
  get wills_payment_mode_slug() {
    return this.policy_wills_form.controls.payment_mode_slug;
  }
  get wills_submission_mode() {
    return this.policy_wills_form.controls.submission_mode;
  }

  ngOnInit() {
    this.getSelections();
    this.getLifeAssured();
  }

  ngOnDestroy() {
    if (this.subs_getSelections) this.subs_getSelections.unsubscribe();
    if (this.subs_getProducts) this.subs_getProducts.unsubscribe();
    if (this.subs_getProductOptions) this.subs_getProductOptions.unsubscribe();
    if (this.subs_getLifeAssured) this.subs_getLifeAssured.unsubscribe();
  }

  submitForm() {
    this.processing.next(true);
    // this.subs_createSubmission =
    let form_data = new FormData();
    form_data.append("submission", JSON.stringify(this.caseDetails_form.value));

    if (this.submission_cat_slug.value == "insurance") {
      form_data.append(
        "case",
        JSON.stringify(this.policy_insurance_form.value)
      );
    } else if (this.submission_cat_slug.value == "cis") {
      form_data.append(
        "case",
        JSON.stringify(this.policy_investment_form.value)
      );
    } else if (this.submission_cat_slug.value == "gi") {
      form_data.append("case", JSON.stringify(this.policy_gi_form.value));
    } else if (this.submission_cat_slug.value == "loans") {
      form_data.append("case", JSON.stringify(this.policy_loans_form.value));
    } else if (this.submission_cat_slug.value == "wills") {
      form_data.append("case", JSON.stringify(this.policy_wills_form.value));
    }

    form_data.append("riders", JSON.stringify(this.payment_list.value));

    if (this.idDropzone_files.length > 0) {
      this.files = [];
      this.files = this.files.concat(this.idDropzone_files);
    }

    for (var i = 0; i < this.files.length; i++) {
      form_data.append("uploads[]", this.files[i], this.files[i].name);
    }

    // console.log("Submitting", form_data);

    // Create Submission Record
    let url = "associates/submissions/" + this.submission_data.submission.uuid + "/cases";
    if (this.submission_data.case) {
      url = "associates/submissions/" + this.submission_data.submission.uuid + "/cases/" + this.submission_data.case.uuid;
    }
    this.apiService.upload(url,
        form_data
      )
      .subscribe(res => {
        // console.log("POST submissions case", res);
        if (res.error === false) {
          // Case Created
          this.mbsRef.dismiss();
          this.pageService.triggerParentRefresh(true);
          this.assSubmissionService.toggleCreatedSwal();
        }
        this.processing.next(false);
      });
  }

  getSelections() {
    this.subs_getSelections = this.apiService
      .post("selections", {
        lists: [
          "lfa-submission-category",
          "lfa-provider-by-submission-cat",
          // 'lfa-provider',
          "lfa-product-category",
          "lfa-payment-mode",
          "currency",
          "lfa-product-coverage",
          "lfa-product-options-by-providers",
          "lfa-product-options-by-providers-all",
          "lfa-riders-by-options",
          "lfa-riders-by-options-all",
          "relationship-type",
          "salutation",
          "gender",
          "race",
          "country",
          "residency-status",
          "marital-status",
          "employment-status",
          "educational-level"
        ]
      })
      .subscribe(res => {
        let data = res["data"];
        // console.log(data);
        this.selectionsLoaded.next(true);
        this.select_submission_category = data["lfa-submission-category"];
        this.select_providers = data["lfa-provider-by-submission-cat"];
        this.select_product_category = data["lfa-product-category"];
        this.select_product_category_gi = this.select_product_category.filter(
          item => {
            return item["category"] === "gi";
          }
        );
        this.select_payment_mode = data["lfa-payment-mode"];
        this.select_currency = data["currency"];
        this.select_product_coverage = data["lfa-product-coverage"];
        this.select_provider_option = data["lfa-product-options-by-providers"];
        this.select_provider_option_all = data["lfa-product-options-by-providers-all"];
        this.select_riders = data["lfa-riders-by-options"];
        this.select_riders_all = data["lfa-riders-by-options-all"];
        this.select_relationship = data["relationship-type"];

        this.salutations = data["salutation"];
        this.genders = data["gender"];
        this.races = data["race"];
        this.countries = data["country"];
        this.residency_status = data["residency-status"];
        this.marital_status = data["marital-status"];
        this.employment_status = data["employment-status"];
        this.educational_levels = data["educational-level"];
        this.filteredRaces = this.race_slug.valueChanges.pipe(
          startWith(""),
          map(value => this._filter(value, this.races))
        );

        if (this.submission_data.case) {
          this.is_update = true;
          console.log("Injected Case:", this.submission_data.case);
          this.updateModel(this.submission_data.case);
        }

        this.cdr.markForCheck();
      });
  }

  getLifeAssured() {
    let client_uuid = this.submission_data.submission.client.uuid;
    this.subs_getLifeAssured = this.apiService
      .get("associates/clients/" + client_uuid + "/life-assured")
      .subscribe(res => {
        this.lifeAssured_cache = res["data"] || [];
      });
  }

  submissionCatChanged(evt) {
    // Reset related form field values
    this.updateModel();
    setTimeout(() => this.stepper.next());
  }

  updateModel(data = null) {
    if (!data) {
      // Reset Insurance Details
      this.policy_insurance_form.reset();
      this.policy_insurance_form.patchValue({
        currency: "SGD",
        gst_compute: false,
        date_birth: new Date(),
        payment_discount: 0,
        smoker: false,
        selected: false,
        pdpa: false,
      });
      this.insurance_options = [];
      this.insurance_optionFuse = null;
      this.insurance_optionSelected = false;
      this.insurance_lifeAssured_active = false;
      this.insurance_lifeAssured_list = [];
      this.insurance_lifeAssured_fuse = null;
      this.insurance_lifeAssured_selected = false;

      // Reset Investment Details
      this.policy_investment_form.reset();
      this.policy_investment_form.patchValue({
        currency: "SGD",
        gst_compute: false,
        payment_discount: 0,
      });
      this.investment_options = [];
      this.investment_optionFuse = null;
      this.investment_optionSelected = false;

      // Reset GI Details
      this.policy_gi_form.reset();
      this.policy_gi_form.patchValue({
        currency: "SGD",
        gst_compute: false,
        payment_discount: 0,
      });

      // Reset Loans Details
      this.policy_loans_form.reset();

      // Reset Wills Details
      this.policy_wills_form.reset();
    } else {
      // Submission Details
      this.caseDetails_form.patchValue({
        submission_cat_slug: data.submission.category_slug,
        date_submission: data.submission.date_submission,
      });
      // Case Details :: insurance
      switch (data.submission.category_slug) {
        case 'insurance': {
          this.policy_insurance_form.patchValue({
            life_assured_type: (data.submission.life_assured.is_client) ? 'client' : ((data.submission.life_assured.personal) ? 'individual' : 'na' ),
            life_assured_is_client: data.submission.life_assured.is_client,
            submission_mode: data.submission.mode,
            provider_slug: data.provider.slug,
            product_option: this.select_provider_option_all[data.provider.slug].find(option => option.uuid==data.product.option_uuid),
            product_option_uuid: data.product.option_uuid,
            payment_mode_slug: data.policy.payment_mode_slug,
            currency: data.policy.currency || "SGD",
            ape: data.submission.ape,
            sum_assured: data.policy.sum_assured,
            policy_term: data.policy.policy_term,
            payment_term: data.policy.payment_term,
            payment_frequency: data.policy.frequency,
            payment_type: data.policy.payment_type,
            gross_payment_after_gst: data.policy.gross_payment_after_gst,
            gross_payment_before_gst: data.policy.gross_payment_before_gst,
            gross_payment_gst: data.policy.gross_payment_gst,
            payment_discount: data.policy.payment_discount,
            nett_payment_after_gst: data.policy.nett_payment_after_gst,
            nett_payment_before_gst: data.policy.nett_payment_before_gst,
            nett_payment_gst: data.policy.nett_payment_gst,
            gst_rate: data.policy.gst_rate,
            gst_compute: (data.policy.gst_rate !== 0) ? true : false,
            life_assured: (data.submission.life_assured.uuid) ? this.insurance_lifeAssured_list.find(la => la.uuid == data.submission.life_assured.uuid) : null,
            life_assured_uuid: data.submission.life_assured.uuid,
          });

          if (data.product.option_uuid) {
            this.insurance_optionSelected = true;
            if (!this.select_provider_option[data.provider.slug].find(option => option.uuid==data.product.option_uuid) &&
                this.select_provider_option_all[data.provider.slug].find(option => option.uuid==data.product.option_uuid)) {
              this.insurance_optionSelected_old = true;
            }
          }

          if (data.submission.life_assured.uuid) {
            this.insurance_processLifeAssured();
          }

          break;
        }
      }
    }
  }

  //
  // Insurance
  //
  resetForm_policy_insurance_form(ref = null, data = null) {
    if (ref == "product_option") {
      this.insurance_processOption(false);
      setTimeout(() => {
        this.insuranceOptionInput.openPanel();
        this.insuranceOptionInput['_element'].nativeElement.focus();
      });
    } else if (ref == "life_assured") {
      this.insurance_processLifeAssured(false);
    }

    if (!ref) {
      this.policy_insurance_form.reset();
    } else {
      const form_field = this.policy_insurance_form.get(ref);
      if (!form_field.validator) return form_field.reset();
      const validator = form_field.validator({} as AbstractControl);
      if (validator && validator.required) form_field.setValue("");
      else form_field.reset();
    }
  }

  insurance_providerChanged(evt) {
    // Reset related form field values
    this.insurance_product_option.reset();
    this.insurance_product_option_uuid.reset();
    this.insurance_optionSelected = false;
    this.fuseResults();
    setTimeout(() => {
      this.insuranceOptionInput.openPanel();
      this.insuranceOptionInput['_element'].nativeElement.focus();
    });
    // setTimeout(() => {
    //   let element = this.autocompletes.find(element => element['_element'].nativeElement.id == "insuranceOptionInput");
    //   element.openPanel();
    // });
  }

  insurance_premiumTypeChanged(evt) {
    if (evt.value == "single")
      this.insurance_payment_frequency.setValue("single");
  }

  insurance_lifeAssuredTypeChanged(evt) {
    this.insurance_lifeAssured_active = false;
    this.insurance_processLifeAssured(false);

    if (evt.value == "client") {
      this.insurance_life_assured_is_client.setValue(true);
    } else if (evt.value == "individual") {
      this.fuseResults_lifeAssured();
      this.insurance_life_assured_is_client.setValue(false);
      this.insurance_lifeAssured_active = true;
      if (this.insurance_lifeAssured_list.length > 0) setTimeout(() => { this.insuranceLifeAssuredInput.openPanel(); });
      else setTimeout(() => { this.insuranceLifeAssuredInput['_element'].nativeElement.focus(); });
    } else if (evt.value == "na") {
      this.insurance_life_assured_is_client.setValue(false);
    }
  }

  fuseResults() {
    if (this.insurance_product_option.value) {
      this.insurance_optionFuse = new Fuse(
        this.select_provider_option[this.insurance_provider_slug.value],
        {
          shouldSort: true,
          findAllMatches: true,
          // includeMatches: true,
          threshold: 0.3,
          location: 0,
          distance: 100,
          maxPatternLength: 32,
          keys: ["name", "product_cat_slug"]
        }
      );
      this.insurance_options = this.insurance_optionFuse.search(
        this.insurance_product_option.value
      );
    } else {
      if (this.insurance_optionSelected_old) this.insurance_options = this.select_provider_option_all[this.insurance_provider_slug.value];
      else this.insurance_options = this.select_provider_option[this.insurance_provider_slug.value];
    }
  }

  blurOption() {
    if (this.insurance_product_option.value && !this.insurance_optionSelected) {
      this.insurance_product_option.setErrors({ not_valid: true });
    } else if (this.insurance_optionSelected) {
      this.insurance_product_option.setErrors(null);
    }
  }

  insurance_processOption(selected = true) {
    if (selected) {
      this.insurance_product_option_uuid.setValue(
        this.insurance_product_option.value.uuid
      );
    } else {
      this.insurance_product_option_uuid.reset();
    }
    this.insurance_optionSelected = selected;
    this.insurance_optionSelected_old = false;
  }

  displayFn(product_option) {
    return product_option ? product_option.name : undefined;
  }

  //
  // Investment
  //
  resetForm_policy_investment_form(ref = null, data = null) {
    if (ref == "product_option") {
      this.investment_processOption(false);
      setTimeout(() => {
        this.investmentOptionInput.openPanel();
        this.investmentOptionInput['_element'].nativeElement.focus();
      });
    }

    if (!ref) {
      this.policy_insurance_form.reset();
    } else {
      const form_field = this.policy_insurance_form.get(ref);
      if (!form_field.validator) return form_field.reset();
      const validator = form_field.validator({} as AbstractControl);
      if (validator && validator.required) form_field.setValue("");
      else form_field.reset();
    }
  }

  investment_providerChanged(evt) {
    // Reset related form field values
    this.investment_product_option.reset();
    this.investment_product_option_uuid.reset();
    this.investment_optionSelected = false;
    this.fuseResults_investment();
    setTimeout(() => {
      this.investmentOptionInput.openPanel();
      this.investmentOptionInput['_element'].nativeElement.focus();
    });
  }

  investment_premiumTypeChanged(evt) {
    if (evt.value == "single")
      this.investment_payment_frequency.setValue("single");
  }

  fuseResults_investment() {
    if (this.investment_product_option.value) {
      this.investment_optionFuse = new Fuse(
        this.select_provider_option[this.investment_provider_slug.value],
        {
          shouldSort: true,
          findAllMatches: true,
          // includeMatches: true,
          threshold: 0.3,
          location: 0,
          distance: 100,
          maxPatternLength: 32,
          keys: ["name", "product_cat_slug"]
        }
      );
      this.investment_options = this.investment_optionFuse.search(
        this.investment_product_option.value
      );
    } else {
      this.investment_options = this.select_provider_option[this.investment_provider_slug.value];
    }
  }

  blurOption_investment() {
    if (
      this.investment_product_option.value &&
      !this.investment_optionSelected
    ) {
      this.investment_product_option.setErrors({ not_valid: true });
    } else if (this.investment_optionSelected) {
      this.investment_product_option.setErrors(null);
    }
  }

  investment_processOption(selected = true) {
    if (selected) {
      this.investment_product_option_uuid.setValue(
        this.investment_product_option.value.uuid
      );
    } else {
      this.investment_product_option_uuid.reset();
    }
    this.investment_optionSelected = selected;
  }

  //
  // General Insurance
  //
  resetForm_policy_gi_form(ref = null, data = null) {
    if (!ref) {
      this.policy_insurance_form.reset();
    } else {
      const form_field = this.policy_insurance_form.get(ref);
      if (!form_field.validator) return form_field.reset();
      const validator = form_field.validator({} as AbstractControl);
      if (validator && validator.required) form_field.setValue("");
      else form_field.reset();
    }
  }

  gi_premiumTypeChanged(evt) {
    if (evt.value == "single") this.gi_payment_frequency.setValue("single");
  }

  gi_productCatChanged(evt) {
    this.gi_product_cat_slug.setValue(evt.value.slug);
  }

  displayFn_lifeAssured(life_assured) {
    return life_assured ? life_assured.personal.full_name : undefined;
  }

  fuseResults_lifeAssured() {
    if (this.insurance_life_assured.value) {
      this.insurance_lifeAssured_fuse = new Fuse(this.lifeAssured_cache, {
        shouldSort: true,
        findAllMatches: true,
        // includeMatches: true,
        threshold: 0.3,
        location: 0,
        distance: 100,
        maxPatternLength: 32,
        keys: ["personal.full_name", "personal.job_title"]
      });
      this.insurance_lifeAssured_list = this.insurance_lifeAssured_fuse.search(
        this.insurance_life_assured.value
      );
    } else {
      this.insurance_lifeAssured_list = this.lifeAssured_cache;
    }
  }

  insurance_processLifeAssured(selected = true) {
    if (selected) {
      this.insurance_life_assured_uuid.setValue(
        this.insurance_life_assured.value.uuid
      );
      let data = this.insurance_life_assured.value;
      this.policy_insurance_form.patchValue({
        relationship_type_slug: data.relationship_type_slug,
        salutation_slug: data.personal.salutation_slug,
        full_name: data.personal.full_name,
        alias: data.personal.alias,
        chinese_name: data.personal.chinese_name,
        nric_no: data.personal.nric_no,
        fin_no: data.personal.fin_no,
        passport_no: data.personal.passport_no,
        gender_slug: data.personal.gender_slug,
        date_birth: data.personal.date_birth,
        race_slug: data.personal.race_slug,
        country_birth_slug: data.personal.country_birth_slug,
        nationality_slug: data.personal.nationality_slug,
        residency_status_slug: data.personal.residency_status_slug,
        marital_status_slug: data.personal.marital_status_slug,
        smoker: data.personal.smoker,
        selected: data.personal.selected,
        pdpa: data.personal.pdpa,
        education_level_slug: data.personal.education_level_slug,
        education_institution: data.personal.education_institution,
        field_of_study: data.personal.field_of_study,
        employment_status_slug: data.personal.employment_status_slug,
        income_range: data.personal.income_range,
        job_title: data.personal.job_title,
        company_name: data.personal.company_name,
        business_nature: data.personal.business_nature,

        // Individual :: Contact
        email: data.personal.contact_information.email,
        home_no: data.personal.contact_information.home_no,
        mobile_no: data.personal.contact_information.mobile_no,
        fax_no: data.personal.contact_information.fax_no,

        // Individual :: Address
        block: data.personal.address_information.block,
        street: data.personal.address_information.street,
        unit: data.personal.address_information.unit,
        building: data.personal.address_information.building,
        city: data.personal.address_information.city,
        postal: data.personal.address_information.postal,
        country_slug: data.personal.address_information.country_slug
      });
    } else {
      this.insurance_life_assured_uuid.reset();

      this.policy_insurance_form.patchValue({
        relationship_type_slug: null,
        salutation_slug: null,
        full_name: this.insurance_life_assured.value,
        alias: null,
        chinese_name: null,
        nric_no: null,
        fin_no: null,
        passport_no: null,
        gender_slug: null,
        date_birth: new Date(),
        race_slug: null,
        country_birth_slug: null,
        nationality_slug: null,
        residency_status_slug: null,
        marital_status_slug: null,
        smoker: false,
        selected: false,
        pdpa: false,
        education_level_slug: null,
        education_institution: null,
        field_of_study: null,
        employment_status_slug: null,
        income_range: null,
        job_title: null,
        company_name: null,
        business_nature: null,

        // Individual :: Contact
        email: null,
        home_no: null,
        mobile_no: null,
        fax_no: null,

        // Individual :: Address
        block: null,
        street: null,
        unit: null,
        building: null,
        city: null,
        postal: null,
        country_slug: null
      });
    }
    this.insurance_lifeAssured_selected = selected;
  }

  resetDropzoneFiles() {
    this.idDropzone_files = [];
    this.idDropzone_files_uploaded = false;
  }

  onDropzoneSelectMultiple(event) {
    // console.log('DZ selected', event);
    this.idDropzone_files.push(...event.addedFiles);
    this.idDropzone_files_uploaded = true;
    // console.log('Dz Files', this.idDropzone_files);
  }

  onDropzoneRemove() {
    this.resetDropzoneFiles();
    // console.log('Dz Files', this.idDropzone_files);
  }

  insurance_processAmounts() {
    if (this.submission_cat_slug.value == "insurance" && this.policy_insurance_form.valid) {
      let gross_payment_after_gst = this.insurance_gross_payment_after_gst
        .value;
      let gst_compute = this.insurance_gst_compute.value;
      let payment_discount = this.insurance_payment_discount.value;
      let results = this.processPaymentsFn(
        gst_compute,
        gross_payment_after_gst,
        payment_discount
      );
      this.insurance_gst_rate.setValue(results["gst_rate"]);
      this.insurance_gross_payment_before_gst.setValue(
        results["gross_payment_before_gst"]
      );
      this.insurance_gross_payment_gst.setValue(results["gross_payment_gst"]);
      this.insurance_gross_payment_after_gst.setValue(
        results["gross_payment_after_gst"]
      );
      this.insurance_payment_discount.setValue(results["payment_discount"]);
      this.insurance_nett_payment_before_gst.setValue(
        results["nett_payment_before_gst"]
      );
      this.insurance_nett_payment_gst.setValue(results["nett_payment_gst"]);
      this.insurance_nett_payment_after_gst.setValue(
        results["nett_payment_after_gst"]
      );
      this.insurance_ape.setValue(results["ape"]);
    } else if (this.submission_cat_slug.value == "cis" && this.policy_investment_form.valid) {
      let gross_payment_after_gst = this.investment_gross_payment_after_gst
        .value;
      let gst_compute = this.investment_gst_compute.value;
      let payment_discount = this.investment_payment_discount.value;
      let results = this.processPaymentsFn(
        gst_compute,
        gross_payment_after_gst,
        payment_discount
      );
      this.investment_gst_rate.setValue(results["gst_rate"]);
      this.investment_gross_payment_before_gst.setValue(
        results["gross_payment_before_gst"]
      );
      this.investment_gross_payment_gst.setValue(results["gross_payment_gst"]);
      this.investment_gross_payment_after_gst.setValue(
        results["gross_payment_after_gst"]
      );
      this.investment_payment_discount.setValue(results["payment_discount"]);
      this.investment_nett_payment_before_gst.setValue(
        results["nett_payment_before_gst"]
      );
      this.investment_nett_payment_gst.setValue(results["nett_payment_gst"]);
      this.investment_nett_payment_after_gst.setValue(
        results["nett_payment_after_gst"]
      );
      this.investment_ape.setValue(results["ape"]);
    } else if (this.submission_cat_slug.value == "gi" && this.policy_gi_form.valid) {
      let gross_payment_after_gst = this.gi_gross_payment_after_gst.value;
      let gst_compute = this.gi_gst_compute.value;
      let payment_discount = this.gi_payment_discount.value;
      let results = this.processPaymentsFn(
        gst_compute,
        gross_payment_after_gst,
        payment_discount
      );
      this.gi_gst_rate.setValue(results["gst_rate"]);
      this.gi_gross_payment_before_gst.setValue(
        results["gross_payment_before_gst"]
      );
      this.gi_gross_payment_gst.setValue(results["gross_payment_gst"]);
      this.gi_gross_payment_after_gst.setValue(
        results["gross_payment_after_gst"]
      );
      this.gi_payment_discount.setValue(results["payment_discount"]);
      this.gi_nett_payment_before_gst.setValue(
        results["nett_payment_before_gst"]
      );
      this.gi_nett_payment_gst.setValue(results["nett_payment_gst"]);
      this.gi_nett_payment_after_gst.setValue(
        results["nett_payment_after_gst"]
      );
      this.gi_ape.setValue(results["ape"]);
    }
  }

  processRiders(formcontrol) {
    // get get_compute from main option
    let gst_compute = this.insurance_gst_compute.value;
    // process values for riders
    let gross_payment_after_gst = formcontrol.get("gross_payment_after_gst")
      .value;
    let payment_discount = formcontrol.get("payment_discount").value;
    let results = this.processPaymentsFn(
      gst_compute,
      gross_payment_after_gst,
      payment_discount
    );
    formcontrol.get("gst_rate").setValue(results["gst_rate"]);
    formcontrol
      .get("gross_payment_before_gst")
      .setValue(results["gross_payment_before_gst"]);
    formcontrol.get("gross_payment_gst").setValue(results["gross_payment_gst"]);
    formcontrol
      .get("gross_payment_after_gst")
      .setValue(results["gross_payment_after_gst"]);
    formcontrol.get("payment_discount").setValue(results["payment_discount"]);
    formcontrol
      .get("nett_payment_before_gst")
      .setValue(results["nett_payment_before_gst"]);
    formcontrol.get("nett_payment_gst").setValue(results["nett_payment_gst"]);
    formcontrol
      .get("nett_payment_after_gst")
      .setValue(results["nett_payment_after_gst"]);
    formcontrol.get("ape").setValue(results["ape"]);
  }

  fuseRiders() {
    let riders =
      this.select_riders_all[this.insurance_product_option_uuid.value] || [];
    if (this.rider.value) {
      this.insurance_riderFuse = new Fuse(riders, {
        shouldSort: true,
        findAllMatches: true,
        // includeMatches: true,
        threshold: 0.3,
        location: 0,
        distance: 100,
        maxPatternLength: 32,
        keys: ["name", "reference_uen", "provider_slug"]
      });
      this.insurance_riders_list = this.insurance_riderFuse.search(
        this.rider.value
      );
    } else {
      this.insurance_riders_list = riders;
    }
  }

  blurRider() {
    this.rider.reset();
  }

  addRider(evt) {
    let rider = evt.option.value;
    this.payment_list.push(this.payments_default(rider.uuid, rider.name));
    this.rider.reset();
  }

  payments_default(uuid = null, name = null, type = "rider", payment = 0) {
    return this.formBuilder.group({
      uuid: uuid,
      name: name,
      type: type,
      gst_rate: this.insurance_gst_compute.value ? this.gst_rate : 0,
      gross_payment_before_gst: 0,
      gross_payment_gst: 0,
      gross_payment_after_gst: payment,
      payment_discount: 0,
      nett_payment_before_gst: 0,
      nett_payment_gst: 0,
      nett_payment_after_gst: 0,
      ape: 0
    });
  }

  paymentsRemove(rowIndex) {
    this.payment_list.removeAt(rowIndex);
  }

  paymentsRowClass = row => {
    return {
      "bg-light-yellow": row.type === "main"
    };
  };

  paymentsSum(column) {
    let sum = 0;
    sum += parseFloat(this.policy_insurance_form.controls[column].value) || 0;
    for (let c of this.payment_list.controls) {
      let amount = parseFloat(c.get(column).value) || 0;
      sum += amount;
    }
    return sum.toFixed(2);
  }

  processPaymentsFn(
    init_gst_compute = false,
    init_gross_payment_after_gst = 0,
    init_payment_discount = 0
  ) {
    let results = [];
    let gst_rate = init_gst_compute ? this.gst_rate : 0;
    results["gst_rate"] = gst_rate;
    results["gross_payment_after_gst"] = init_gross_payment_after_gst;
    results["payment_discount"] = init_payment_discount;

    // compute value for gross commissions
    if (init_gross_payment_after_gst !== 0) {
      // compute gst
      if (gst_rate !== 0) {
        let gross_gst =
          init_gross_payment_after_gst * (gst_rate / (1 + gst_rate));
        let gross_payment_before_gst =
          init_gross_payment_after_gst / (1 + gst_rate);
        results["gross_payment_gst"] = gross_gst.toFixed(5);
        results["gross_payment_before_gst"] = gross_payment_before_gst.toFixed(
          5
        );
      } else {
        results["gross_payment_gst"] = 0;
        results[
          "gross_payment_before_gst"
        ] = init_gross_payment_after_gst.toFixed(5);
      }

      // compute payment discounts
      if (init_payment_discount !== 0) {
        let discounts = init_payment_discount * init_gross_payment_after_gst;
        let nett_payment_after_gst =
          init_gross_payment_after_gst - discounts;
        results["nett_payment_after_gst"] = nett_payment_after_gst.toFixed(5);
        if (gst_rate !== 0) {
          let nett_gst = nett_payment_after_gst * (gst_rate / (1 + gst_rate));
          let nett_payment_before_gst = nett_payment_after_gst / (1 + gst_rate);
          results["nett_payment_gst"] = nett_gst.toFixed(5);
          results["nett_payment_before_gst"] = nett_payment_before_gst.toFixed(
            5
          );
        } else {
          results["nett_payment_before_gst"] =
            results["nett_payment_after_gst"];
          results["nett_payment_gst"] = 0;
        }
      } else {
        results["nett_payment_before_gst"] =
          results["gross_payment_before_gst"];
        results["nett_payment_gst"] = results["gross_payment_gst"];
        results["nett_payment_after_gst"] = results["gross_payment_after_gst"];
      }
    } else {
      results["gross_payment_before_gst"] = 0;
      results["gross_payment_gst"] = 0;
      results["nett_payment_before_gst"] = 0;
      results["nett_payment_gst"] = 0;
      results["nett_payment_after_gst"] = 0;
    }

    // Compute APE
    if (this.submission_cat_slug.value == "insurance") {
      results["ape"] = this.insurance_computeAPE(
        this.insurance_payment_term.value,
        this.insurance_payment_frequency.value,
        results["nett_payment_before_gst"]
      ).toFixed(5);
    } else if (this.submission_cat_slug.value == "cis") {
      results["ape"] = this.insurance_computeAPE(
        this.investment_payment_term.value,
        this.investment_payment_frequency.value,
        results["nett_payment_before_gst"]
      ).toFixed(5);
    } else if (this.submission_cat_slug.value == "gi") {
      results["ape"] = this.insurance_computeAPE(
        this.gi_payment_term.value,
        this.gi_payment_frequency.value,
        results["nett_payment_before_gst"]
      ).toFixed(5);
    }
    return results;
  }

  insurance_computeAPE(
    payment_term = 0,
    payment_frequency = "monthly",
    payment = 0
  ) {
    if (payment_term > 0 && payment > 0 && payment_frequency) {
      let term_multiplier = 0;
      let frequency_multiplier = 0;

      if (payment_term >= 10) {
        term_multiplier = 1;
      } else if (payment_term >= 1 && payment_term < 10) {
        term_multiplier = payment_term / 10;
      }

      switch (payment_frequency) {
        case "monthly":
          frequency_multiplier = 12;
          break;
        case "quarterly":
          frequency_multiplier = 4;
          break;
        case "semi-annually":
          frequency_multiplier = 2;
          break;
        case "annually":
          frequency_multiplier = 1;
          break;
        case "single":
          frequency_multiplier = 0.1;
          break;
      }
      let computed_value = payment * term_multiplier * frequency_multiplier;
      return computed_value;
    } else {
      return 0;
    }
  }

  formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return "0 Bytes";

    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ["Bytes", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];

    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + " " + sizes[i];
  }

  private _filter(value, model): string[] {
    const filterValue = value ? value.toLowerCase() : "";
    return model.filter(option =>
      option.slug.toLowerCase().includes(filterValue)
    );
  }

  round(value, precision) {
    var multiplier = Math.pow(10, precision || 0);
    return Math.round(value * multiplier) / multiplier;
  }

  findInvalidControls(f: FormGroup) {
    const invalid = [];
    const controls = f.controls;
    for (const name in controls) {
      if (controls[name].invalid) {
        invalid.push(name);
      }
    }
    return invalid;
  }
}
