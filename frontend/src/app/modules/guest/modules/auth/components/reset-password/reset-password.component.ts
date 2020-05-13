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

import { AuthService } from '@app/@core/services';

export class ResetPasswordErrorStateMatcher implements ErrorStateMatcher {
  isErrorState(control: FormControl | null, form: FormGroupDirective | NgForm | null): boolean {
    const invalidCtrl = !!(control && control.invalid && control.parent.dirty);
    const invalidParent = !!(control && control.parent && control.parent.invalid && control.parent.dirty);

    return (invalidCtrl || invalidParent);
  }
}

@Component({
  selector: 'app-reset-password',
  templateUrl: './reset-password.component.html',
  styleUrls: ['./reset-password.component.scss']
})
export class ResetPasswordComponent implements OnInit, OnDestroy {
  @ViewChild("usernameInput", {static: false}) usernameInput: ElementRef;
  @ViewChild("passwordInput", {static: false}) passwordInput: ElementRef;
  @ViewChild("passwordConfirmInput", {static: false}) passwordConfirmInput: ElementRef;

  username: string = null;
  token: string = null;
  auth_sub: any;

  secure_pw: boolean = true;
  secure_cpw: boolean = true;

  processing: boolean = false;
  success: boolean = false;

  matcher = new ResetPasswordErrorStateMatcher();
  authForm = this.formBuilder.group({
    'username': [''],
    'password': ['', Validators.required],
    'password_confirmation': ['', Validators.required],
  }, { validator: this.checkPasswords });
  request: any = {};

  get password() { return this.authForm.controls.password; }
  get password_confirmation() { return this.authForm.controls.password_confirmation; }

  constructor(
    private authService: AuthService,
    private formBuilder: FormBuilder,
    protected cd: ChangeDetectorRef,
    private route: ActivatedRoute,
    private router: Router) {
      this.route.data.subscribe(data => {
        this.authForm.controls.username.setValue(data.response.username);
        this.username = data.response.username;
        this.token = data.response.token;
      });
  }

  checkPasswords(group: FormGroup) { // here we have the 'passwords' group
    let pass = group.controls.password.value;
    let confirmPass = group.controls.password_confirmation.value;
    return pass === confirmPass ? null : { password: true }
  }

  resetPassword() {
    this.process(true);
    this.auth_sub = this.authService.resetPassword(this.request).subscribe(res => {
      if (res['error'] === false && res['status'] === 'password-reset-successful') {
        this.success = true;
      } else if (res['error'] === true && res['status'] === 'token-expired') {
        this.router.navigate(['/auth/forget-password'], { queryParams: { status: 'token-expired', username: this.username }});
      } else if (res['error'] === true && res['data']['errors']['token']) {
        this.router.navigate(['/auth/forget-password'], { queryParams: { status: 'token-invalid', username: this.username }});
      } else {
        this.router.navigate(['/auth/forget-password'], { queryParams: { status: 'unexpected-error', username: this.username }});
      }
      this.cd.detectChanges();
    });
  }

  process(type: boolean) {
    if (type) {
      this.processing = true;
      this.password.disable();
      this.password_confirmation.disable();
    } else {
      this.processing = false;
      this.password.enable();
      this.password_confirmation.enable();
    }
  }

  submit() {
    this.secure_pw = true;
    this.secure_cpw = true;
    this.request = {
      username: this.username,
      token: this.token,
      password: this.password.value,
      password_confirmation: this.password_confirmation.value
    };
    this.resetPassword();
  }

  ngOnInit(): void {}

  ngOnDestroy() {
    if (this.auth_sub) this.auth_sub.unsubscribe();
  }
}
