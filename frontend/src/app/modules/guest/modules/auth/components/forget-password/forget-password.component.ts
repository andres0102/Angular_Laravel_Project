/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { Component, OnDestroy, ViewChild, ElementRef, ChangeDetectorRef, Inject } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { FormBuilder, Validators } from '@angular/forms';
import { debounceTime, distinctUntilChanged, switchMap } from 'rxjs/operators';

import { AuthService } from '@app/@core/services';

@Component({
  selector: 'app-forget-password',
  templateUrl: './forget-password.component.html',
  styleUrls: ['./forget-password.component.scss']
})
export class ForgetPasswordComponent implements OnDestroy {
  @ViewChild("usernameInput", {static: false}) usernameInput: ElementRef;

  auth_sub: any;
  status: string = null;

  processing: boolean = false;
  success: boolean = false;
  username_init: string = null;

  authForm = this.formBuilder.group({
    'username': ['', Validators.required],
  });

  get username() { return this.authForm.controls.username; }

  user: any = {};
  db: number = 1000;

  constructor(
    private authService: AuthService,
    private formBuilder: FormBuilder,
    protected cd: ChangeDetectorRef,
    private route: ActivatedRoute,
    private router: Router) {
      this.status = this.route.snapshot.queryParamMap.get('status');

      if (this.username_init = (this.route.snapshot.queryParamMap.get('username') || localStorage.getItem('username'))) {
        this.username.setValue(this.username_init);
      }
  }

  forgetPassword() {
    this.status = null;
    this.process(true);
    this.auth_sub = this.authService.forgetPassword({username: this.username.value}).subscribe((res) => {
      if (res && res['error']) {
        this.process(false);
        this.status = res['status'];
      } else {
        this.success = true;
        this.cd.detectChanges();
      }
    });
  }

  process(type: boolean) {
    if (type) {
      this.processing = true;
      this.username.disable();
    } else {
      this.processing = false;
      this.username.enable();
    }
  }

  ngOnDestroy() {
    if (this.auth_sub) this.auth_sub.unsubscribe();
  }
}
