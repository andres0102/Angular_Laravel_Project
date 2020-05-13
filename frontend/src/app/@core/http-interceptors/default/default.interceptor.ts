/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Injectable, Injector, ErrorHandler } from '@angular/core';
import { HttpEvent, HttpInterceptor, HttpHandler, HttpRequest, HttpErrorResponse } from '@angular/common/http';
import { Observable } from 'rxjs';
import { tap } from 'rxjs/operators';
import { Router } from '@angular/router';

import { IdentityService, LocalStorageService } from '@app/@core/services';

/** Passes HttpErrorResponse to application-wide error handler */
@Injectable()
export class DefaultHttpInterceptor implements HttpInterceptor {

  constructor(
    private injector: Injector,
    private router: Router,
    private identity: IdentityService,
    private localStorageService: LocalStorageService) {}

  intercept(
    request: HttpRequest<any>,
    next: HttpHandler
  ): Observable<HttpEvent<any>> {
    let newBody = request.body;
    let bodyChanged = false;

    if (request.detectContentTypeHeader() == "application/json") {
      newBody['metadata'] = this.identity.metadata;
      bodyChanged = true;
    } else if (request.detectContentTypeHeader() == "text/plain") {
      newBody += ('&fingerprint=' + (this.identity.metadata['fingerprint'] || null));
      newBody += ('&device=' + (this.identity.metadata['device'] || null));
      newBody += ('&os=' + (this.identity.metadata['device_info']['os'] || null));
      newBody += ('&os_version=' + (this.identity.metadata['device_info']['os_version'] || null));
      newBody += ('&browser=' + (this.identity.metadata['device_info']['browser'] || null));
      newBody += ('&browser_version=' + (this.identity.metadata['device_info']['browser_version'] || null));
      bodyChanged = true;
    }

    if (bodyChanged) request = request.clone({body: newBody});

    return next.handle(request).pipe(
      tap(null, (err: any) => {
        const urlTree = this.router.parseUrl(this.router.url);
        const urlWithoutParams = (urlTree.root.children['primary']) ? urlTree.root.children['primary'].segments.map(it => it.path).join('/') : null;

        if (err.status === 401 && urlWithoutParams != 'auth/login') {
          // unauthorized... probably token invalidated
          // Save current route
          if (!['/auth/login', '/'].includes(this.router.url)) this.localStorageService.setItem('auth-redirect', urlWithoutParams);
          // redirect to the login route
          this.router.navigate(['/auth/login'], { queryParams: { status: 'token-invalid' } });
        } else if (err instanceof HttpErrorResponse) {
          const appErrorHandler = this.injector.get(ErrorHandler);
          appErrorHandler.handleError(err);
        }

      })
    );
  }
}