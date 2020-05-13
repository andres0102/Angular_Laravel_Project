/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Injectable } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, Router, Route } from '@angular/router';
import { tap } from 'rxjs/operators';

import { AuthService, LocalStorageService } from '@app/@core/services';
import { NbAuthService } from '@nebular/auth';

@Injectable()
export class AuthGuard implements CanActivate {

  constructor(
    private router: Router,
    private nbAuthService: NbAuthService,
    private authService: AuthService,
    private localStorageService: LocalStorageService) {}

  canActivate(next: ActivatedRouteSnapshot, state: RouterStateSnapshot) {
    // canActive can return Observable<boolean>, which is exactly what isAuhenticated returns
    return this.nbAuthService.isAuthenticatedOrRefresh()
      .pipe(
        tap(authenticated => {
          if (authenticated) {
            return true;
          } else {
            // Save current route
            this.localStorageService.setItem('auth-redirect', state.url);
            this.router.navigate(['/auth']);
            return false;
          }
        })
      );
  }

}