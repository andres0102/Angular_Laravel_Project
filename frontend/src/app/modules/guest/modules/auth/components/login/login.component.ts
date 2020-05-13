/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Component, OnInit, OnDestroy, ViewChild, ElementRef } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { FormBuilder, Validators } from '@angular/forms';
import { Observable, Subject, Subscription } from 'rxjs';
import { debounceTime, distinctUntilChanged, switchMap } from 'rxjs/operators';

import { Response, User } from '@app/models';
import { AuthService, LocalStorageService } from '@app/@core/services';
import { NbAuthService, NbAuthResult } from '@nebular/auth';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss']
})
export class LoginComponent implements OnInit, OnDestroy {
  @ViewChild("usernameInput", {static: false}) usernameInput: ElementRef;
  @ViewChild("passwordInput", {static: false}) passwordInput: ElementRef;

  authSubscription: Subscription;
  greetings: string = "day";
  status: string = null;

  secure: boolean = true;
  processing: boolean = false;
  agent$: Observable<Response[]>;
  user: User = null;
  username_init: string = null;

  authForm = this.formBuilder.group({
    'username': ['', Validators.required],
    'password': ['', Validators.required],
    'email': ['', [Validators.required, Validators.email]],
    'public' : [false]
  });

  get public() { return this.authForm.controls.public; }
  get username() { return this.authForm.controls.username; }
  get password() { return this.authForm.controls.password; }
  get email() { return this.authForm.controls.email; }

  private searchTerm = new Subject<string>();

  redirectDelay: number = 0;
  showMessages: any = {};
  strategy: string = 'lfa-oauth2';

  errors: string[] = [];
  messages: string[] = [];
  credentials: any = {};

  constructor(
    private authService: AuthService,
    private formBuilder: FormBuilder,
    private router: Router,
    private route: ActivatedRoute,
    private localStorageService: LocalStorageService,
    protected nbAuthService: NbAuthService) {

    if (this.status = this.route.snapshot.queryParamMap.get('status')) this.router.navigate([], { relativeTo: this.route, queryParams: { status: null }, replaceUrl: true, queryParamsHandling: 'merge' });
    this.localStorageService.removeItem('auth_app_token');

    if (this.username_init = (this.route.snapshot.queryParamMap.get('username') || this.localStorageService.getItem('username'))) {
      this.username.setValue(this.username_init);
    }
  }

  login() {
    this.errors = [];
    this.messages = [];
    this.process(true);
    this.authSubscription = this.nbAuthService.authenticate(this.strategy, this.credentials).subscribe((result: NbAuthResult) => {
      let response = result.getResponse();
      if (result.isSuccess()) {
        this.messages = result.getMessages();
        let auth_url = this.localStorageService.getItem('auth-redirect') || '/authenticated';
        this.localStorageService.removeItem('auth-redirect');
        this.localStorageService.setItem('public', this.public.value);
        if (!this.public.value) this.localStorageService.setItem('username', this.username.value);
        else this.localStorageService.removeItem('username');
        if (auth_url == "/auth/login") auth_url = "/";
        this.router.navigate([auth_url]);
      } else {
        // this.errors = result.getErrors();
        this.invalid_request(response);
      }
    });
  }

  invalid_request(response) {
    this.process(false);
    if (response.status == 401 && response.error['error'] == "invalid_credentials") {
      this.password.setErrors({password: true});
    } else {
      this.password.setErrors({unexpected: true});
    }
    setTimeout(() => this.passwordInput.nativeElement.focus());
  }

  // Push a search term into the observable stream.
  search(term: string): void {
    this.email.setValue(term + '@legacyfa-asia.com');
    if (this.email.invalid) {
      this.user = null;
      this.username.setErrors({email: true});
      this.username.markAsDirty();
      this.username.markAsTouched();
      this.usernameInput.nativeElement.focus();
    } else {
      this.username.setErrors(null);
      this.searchTerm.next(term);
    }
  }

  process(type: boolean) {
    if (type) {
      this.processing = true;
      this.username.disable();
      this.password.disable();
    } else {
      this.processing = false;
      this.username.enable();
      this.password.enable();
    }
  }

  submit() {
    this.credentials = {
      email: this.email.value,
      password: this.password.value
    };
    this.login();
  }

  reset() {
    this.username.setValue('');
    this.user=null;
    this.search(this.username.value);
    this.username.markAsPristine();
    this.username.markAsUntouched();
  }

  ngOnInit(): void {
    let today = new Date();
    let hrs = today.getHours();
    if (hrs < 12) this.greetings = 'morning';
    else if (hrs >= 12 && hrs <= 17) this.greetings = 'afternoon';
    else if (hrs >= 17 && hrs <= 24) this.greetings = 'evening';


    this.agent$ = this.searchTerm.pipe(
      // wait 1000ms after each keystroke before considering the term
      debounceTime(1000),
      // ignore new term if same as previous term
      distinctUntilChanged(),
      // switch to new search observable each time the term changes
      switchMap((term: string) => {
        // if (term) this.router.navigate([], { relativeTo: this.route, queryParams: { username: term }, replaceUrl: true, queryParamsHandling: 'merge' });
        // else this.router.navigate([], { relativeTo: this.route, queryParams: { username: null }, replaceUrl: true, queryParamsHandling: 'merge' });
        return this.authService.searchEmail(term);
      })
    );

    this.agent$.subscribe((res) => {
      // console.log("Response:", res);
      if (res && !res['error']) this.user = res['data'];
      else this.user = null;
    });

    setTimeout(() => {
      if (this.username_init) {
        this.search(this.username_init);
        if (this.username.valid) this.passwordInput.nativeElement.focus();
        this.username_init = null;
      }
    });

  }

  ngOnDestroy() {
    if (this.authSubscription) this.authSubscription.unsubscribe();
  }
}
