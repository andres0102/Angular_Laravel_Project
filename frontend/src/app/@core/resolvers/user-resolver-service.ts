/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Injectable } from '@angular/core';
import { Router, Resolve, RouterStateSnapshot, ActivatedRouteSnapshot } from '@angular/router';
import { Observable, of } from 'rxjs';
import { map, take } from 'rxjs/operators';

import { Response } from '@app/models';
import { AuthService, LocalStorageService } from '@app/@core/services';
import { NbAuthService } from '@nebular/auth';

@Injectable({
  providedIn: 'root'
})
export class UserResolverService implements Resolve<Response> {

  constructor(
    private authService: AuthService,
    private router: Router,
    private localStorageService: LocalStorageService,
    private nbAuthService: NbAuthService) {}

  resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot) {
    return this.authService.getAuthenticatedUser().pipe(
      take(1),
      map(res => {
        // console.log('Authenticated User Resolver:', res);
        return res['data'];
      })
    );
  }
}
