/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Injectable } from '@angular/core';
import { Router, Resolve, RouterStateSnapshot, ActivatedRouteSnapshot } from '@angular/router';
import { map, take, catchError } from 'rxjs/operators';
import { Response } from '@app/models';
import { AuthService } from '@app/@core/services';

@Injectable({
  providedIn: 'root'
})
export class ResetPasswordTokenResolverService implements Resolve<Response> {

  constructor(private authService: AuthService, private router: Router) {}

  resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot) {
    let token = {
      token: route.paramMap.get('token')
    };

    return this.authService.checkResetToken(token).pipe(
      take(1),
      map(res => {
        console.log("Check Token", res);
        if (res['error'] === false && res['status'] === 'token-valid') {
          return res['data'];
        } else if (res['error'] === true && res['status'] === 'token-expired') {
          this.router.navigate(['/auth/forget-password'], { queryParams: { status: 'token-expired', username: (res['data']['username'] || null) }});
        } else if (res['error'] === true && res['data']['errors']['token']) {
          this.router.navigate(['/auth/forget-password'], { queryParams: { status: 'token-invalid' }});
        } else {
          this.router.navigate(['/auth/forget-password'], { queryParams: { status: 'unexpected-error' }});
        }
        return [];
      }),
      catchError(err => {
        this.router.navigate(['/auth/forget-password'], { queryParams: { status: 'unexpected-error' }});
        return [];
      })
    );
  }
}
