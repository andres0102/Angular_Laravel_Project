/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { Subject, Observable, of } from 'rxjs';
import { tap, catchError } from 'rxjs/operators';

import { User, Response } from '@app/models';
import { ApiService } from '@app/@core/services/api';
import { LocalStorageService } from '@app/@core/services/local-storage';
import { NbAuthService } from '@nebular/auth';

@Injectable()
export class AuthService {

  constructor(
    private nbAuthService: NbAuthService,
    private router: Router,
    private api: ApiService,
    private localStorageService: LocalStorageService) {

    this.checkLoginEmailValidity.subscribe(value => this.check = value);
    this.associate_change.subscribe(value => {
      this.associate = value;
      this.router.navigate(['/']);
    });
  }

  check: boolean = false;
  checkEmailExists: Response[] = null;
  checkLoginEmailValidity: Subject<boolean> = new Subject<boolean>();

  user: User;
  associate = null;
  associate_change: Subject<any> = new Subject<any>();

  searchEmail(term: string): Observable<Response[]> {
    if (!term.trim()) {
      // if not search term, return empty string.
      this.checkEmailExists = null;
      this.checkLoginEmailValidity.next(false);
      return of(null);
    }
    return this.api.post('auth/check-username', {username: term}).pipe(
      tap((res: Response[]) => {
        if (res['error']) {
          this.checkEmailExists = null;
          this.checkLoginEmailValidity.next(false);
        } else {
          this.checkEmailExists = res;
          this.checkLoginEmailValidity.next(true);
        }
      })
    );
  }

  forgetPassword(email) { return this.api.post('auth/forget-password', email); }
  checkResetToken(token) { return this.api.post('auth/check-reset-token', token); }
  resetPassword(request) { return this.api.post('auth/reset-password', request); }

  checkEmailToken(token) { return this.api.post('auth/check-email-token', token); }
  resendEmailToken(token) { return this.api.post('auth/resend-email-token', token); }
  verifyEmail(credentials) { return this.api.post('auth/verify-email-login', credentials); }

  getAuthenticatedUser() {
    return this.api.post('auth/me', {}).pipe(
      tap((res: Response[]) => {
        if (res['error'] === true) {
          // Something went wrong, user may not be authenticated..
          // Save current route
          if (!['/auth/login', '/'].includes(this.router.url)) this.localStorageService.setItem('auth-redirect', this.router.url);
          this.invalidate('token-invalid');
        } else {
          // console.log('Auth.getAuthenticatedUser', res);
          this.user = res['data'];
          this.associate = (this.user.roles.is_associate) ? this.user.lfa : null;
        }
      }),
      catchError(err => {
        // Save current route
        this.invalidate();
        throw 'error in source. Details: ' + err;
      })
    );
  }

  changeOperatingPersonnel() {
    return this.api.post('auth/personnel', {}).pipe(
      tap((res: Response[]) => {
         this.associate = (this.user.roles.is_associate) ? this.user.lfa : null;
      })
    );
  }

  invalidate(status: string = 'token-invalid') {
    this.logout(status);
  }

  logout(status: string = 'logout') {
    // sessionStorage.clear();
    this.localStorageService.removeItem('auth-redirect');
    this.api.post('auth/logout', {});
    this.nbAuthService.logout('lfa-oauth2');
    localStorage.removeItem('auth_app_token');
    this.user = null;
    this.associate = null;
    this.router.navigate(['/auth/login'], { queryParams: { status: status }});
  }
}