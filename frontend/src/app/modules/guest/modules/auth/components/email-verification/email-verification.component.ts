/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { Component, OnInit, OnDestroy, ViewChild, ElementRef, ChangeDetectorRef, Inject } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { FormBuilder, Validators, FormControl, FormGroupDirective, NgForm, FormGroup } from '@angular/forms';
import { ErrorStateMatcher } from '@angular/material/core';
import { Subscription } from 'rxjs';

import { User } from '@app/models';
import { AuthService, LocalStorageService } from '@app/@core/services';

export class EmailVerifyErrorStateMatcher implements ErrorStateMatcher {
  isErrorState(control: FormControl | null, form: FormGroupDirective | NgForm | null): boolean {
    const invalidCtrl = !!(control && control.invalid && control.parent.dirty);
    const invalidParent = !!(control && control.parent && control.parent.invalid && control.parent.dirty);

    return (invalidCtrl || invalidParent);
  }
}

@Component({
  selector: 'app-email-verification',
  templateUrl: './email-verification.component.html',
  styleUrls: ['./email-verification.component.scss']
})
export class EmailVerificationComponent implements OnInit, OnDestroy {
  @ViewChild("displayNameInput", {static: false}) displayNameInput: ElementRef;
  @ViewChild("passwordInput", {static: false}) passwordInput: ElementRef;
  @ViewChild("passwordConfirmInput", {static: false}) passwordConfirmInput: ElementRef;

  dataLoaded = false;

  token: string = null;
  username: string = null;
  check_token_sub: Subscription;
  verify_sub: Subscription;
  resend_sub: Subscription;

  secure_pw: boolean = true;
  secure_cpw: boolean = true;

  processing: boolean = false;
  success: boolean = false;

  expired: boolean = false;
  resending: boolean = false;
  resending_complete: boolean = false;

  matcher = new EmailVerifyErrorStateMatcher();
  authForm = this.formBuilder.group({
    'gender': [],
    'display_name': ['', Validators.required],
    'password': ['', Validators.required],
    'password_confirmation': ['', Validators.required],
  }, { validator: this.checkPasswords });

  form_data: any = {};
  user: User = null;

  get gender() { return this.authForm.controls.gender; }
  get display_name() { return this.authForm.controls.display_name; }
  get password() { return this.authForm.controls.password; }
  get password_confirmation() { return this.authForm.controls.password_confirmation; }

  constructor(
    private authService: AuthService,
    private localStorageService: LocalStorageService,
    private formBuilder: FormBuilder,
    protected cd: ChangeDetectorRef,
    private route: ActivatedRoute,
    private router: Router) {

      this.token = this.route.snapshot.paramMap.get('token');
      this.form_data = {
        token: this.token
      };

      this.check_token_sub = this.authService.checkEmailToken(this.form_data).subscribe((response) => {
        if (response['error']) {
            if (this.localStorageService.getItem('username')) this.localStorageService.removeItem('username');
            if (response['status'] == 'unexpected-error') {
              this.router.navigate(['/auth/login']);
            } else if (response['status'] == 'token-expired') {
              this.expired = true;
            }
        } else {
          this.user = response['data'];
          this.display_name.setValue(this.user['name']);
          this.gender.setValue(this.user['gender']);
        }
        this.dataLoaded = true;
      });
  }x

  checkPasswords(group: FormGroup) { // here we have the 'passwords' group
    let pass = group.controls.password.value;
    let confirmPass = group.controls.password_confirmation.value;
    return pass === confirmPass ? null : { password: true }
  }

  submit() {
    this.secure_pw = true;
    this.secure_cpw = true;
    this.form_data = {
      token: this.token,
      display_name: this.display_name.value,
      password: this.password.value,
      password_confirmation: this.password_confirmation.value
    };
    this.updateProfile();
  }

  updateProfile() {
    this.process(true);
    this.verify_sub = this.authService.verifyEmail(this.form_data).subscribe((response) => {
      if (response['error'] === false) {
        this.success = true;
        this.username = response['username'];
      } else {
        this.process(false);
      }
        this.cd.detectChanges();
    });
  }

  process(type: boolean) {
    if (type) {
      this.processing = true;
      this.display_name.disable();
      this.password.disable();
      this.password_confirmation.disable();
    } else {
      this.processing = false;
      this.display_name.enable();
      this.password.enable();
      this.password_confirmation.enable();
    }
  }

  resend() {
    this.resending = true;
    this.form_data = {
      token: this.token
    };
    this.resend_sub = this.authService.resendEmailToken(this.form_data).subscribe((response) => {
      if (response['error'] == false) this.resending_complete = true;
      this.cd.detectChanges();
    });
  }

  ngOnInit(): void {}

  ngOnDestroy() {
    if (this.check_token_sub) this.check_token_sub.unsubscribe();
    if (this.verify_sub) this.verify_sub.unsubscribe();
    if (this.resend_sub) this.resend_sub.unsubscribe();
  }
}
