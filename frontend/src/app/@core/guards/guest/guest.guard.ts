/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Injectable } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, Router, Route } from '@angular/router';
import { of } from 'rxjs';
import { map, take } from 'rxjs/operators';

import { AuthService, LocalStorageService } from '@app/@core/services';
import { NbAuthService } from '@nebular/auth';

@Injectable()
export class GuestGuard implements CanActivate {

  constructor(
    private router: Router,
    private nbAuthService: NbAuthService,
    private authService: AuthService,
    private localStorageService: LocalStorageService) {}

  canActivate(next: ActivatedRouteSnapshot, state: RouterStateSnapshot): any {
    return this.nbAuthService.isAuthenticated().pipe(map(authenticated => {
      if (authenticated) {
        // LocalStorage has token = return true, lets check server if user is really authenticated...
        return this.authService.getAuthenticatedUser().subscribe(res => {
          if (res['error'] !== true) {
            let url = this.localStorageService.getItem('auth-redirect') || '/authenticated';
            this.router.navigate([url]);
            return false;
          }
        }, err => {
          // Something went wrong, user may not be authenticated..
          if (!['/auth/login', '/'].includes(state.url)) this.localStorageService.setItem('auth-redirect', state.url);
          this.authService.invalidate();
          return true;
        });
      } else {
        return true;
      }
    }));
  }

}