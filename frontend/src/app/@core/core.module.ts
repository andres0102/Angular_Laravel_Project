/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { ModuleWithProviders, NgModule, Optional, SkipSelf, ErrorHandler } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HTTP_INTERCEPTORS, HttpRequest } from '@angular/common/http';
import { of as observableOf } from 'rxjs';

import { NbThemeModule, NbLayoutModule } from '@nebular/theme';
import {
  NbAuthModule,
  NbOAuth2AuthStrategy,
  NbAuthOAuth2Token,
  NbOAuth2ClientAuthMethod,
  NbOAuth2ResponseType,
  NbOAuth2GrantType,
  NbAuthJWTInterceptor,
  NB_AUTH_TOKEN_INTERCEPTOR_FILTER
} from '@nebular/auth';
import { NbSecurityModule, NbRoleProvider } from '@nebular/security';
import { MatSnackBarModule } from '@angular/material/snack-bar';
import { AuthGuard, GuestGuard } from './guards';
import { AnimationsService,
         ApiService,
         AuthService,
         IdentityService,
         ErrorHandlerService,
         LocalStorageService,
         SessionStorageService,
         NotificationService,
         PusherService,
         TitleService } from './services';
import { httpInterceptorProviders } from './http-interceptors';
import { throwIfAlreadyLoaded } from './module-import-guard';
import { environment } from '@env/environment';

export class NbSimpleRoleProvider extends NbRoleProvider {
  getRole() {
    // here you could provide any role based on any auth flow
    return observableOf('guest');
  }
}

// Non Authenticated Routes that do not need to add OAuth2 Tokens...
export function filterInterceptorRequest(req: HttpRequest<any>) {
  return [
      'https://api.ipify.org/?format=json',                                            // Get USER IP
      'https://secure.legacyfa-asia.com/status',                                       // Ping for Online/Offline Status
      'https://secure.legacyfa-asia.com/oauth/token',                                  // Authenticate for OAuth2 Token
      'https://secure.legacyfa-asia.com/auth/check-username',                          // Check username existence
      'https://secure.legacyfa-asia.com/auth/check-email-token',                       // Check email verification token validity
      'https://secure.legacyfa-asia.com/auth/resend-email-token',                      // Resend email verification token validity
      'https://secure.legacyfa-asia.com/auth/verify-email',                            // Verify email & update password
    ].some(url => req.url.includes(url));
}

export const NB_CORE_PROVIDERS = [
  ...NbAuthModule.forRoot({
    strategies: [
      NbOAuth2AuthStrategy.setup({
        name: 'lfa-oauth2',
        baseEndpoint: `${environment.api_url}`,
        clientId: "1",
        clientSecret: '6Xp5UkF2D7kQSPxv0zKPDCAP4zwsvIEjFRN6cspm',
        clientAuthMethod: NbOAuth2ClientAuthMethod.BASIC,
        authorize: {
          endpoint: 'oauth/authorize',
          responseType: NbOAuth2ResponseType.TOKEN,
        },
        token: {
          endpoint: 'oauth/token',
          grantType: NbOAuth2GrantType.PASSWORD,
          class: NbAuthOAuth2Token
        },
        refresh: {
          endpoint: 'oauth/token',
        },
        redirect: {
          success: '/authenticated', // welcome page path
          failure: null, // stay on the same page
        },
      }),
    ]
  }).providers,

  NbSecurityModule.forRoot({
    accessControl: {
      guest: {
        view: '*',
      },
      user: {
        parent: 'guest',
        create: '*',
        edit: '*',
        remove: '*',
      },
    },
  }).providers,

  { provide: HTTP_INTERCEPTORS, useClass: NbAuthJWTInterceptor, multi: true },
  { provide: NB_AUTH_TOKEN_INTERCEPTOR_FILTER, useValue: filterInterceptorRequest },
  { provide: NbRoleProvider, useClass: NbSimpleRoleProvider },
  { provide: ErrorHandler, useClass: ErrorHandlerService },

  AuthGuard, GuestGuard,
  ApiService,
  AuthService,
  IdentityService,
  PusherService,
  httpInterceptorProviders,
  AnimationsService,
  LocalStorageService,
  SessionStorageService,
  NotificationService,
  TitleService,
];

@NgModule({
  imports: [
    CommonModule,
    NbAuthModule,
    NbThemeModule.forRoot(),
    NbLayoutModule,
    MatSnackBarModule
  ],
  exports: [
    NbAuthModule,
    NbLayoutModule,
    MatSnackBarModule
  ],
  declarations: [],
})
export class CoreModule {
  constructor(@Optional() @SkipSelf() parentModule: CoreModule) {
    throwIfAlreadyLoaded(parentModule, 'CoreModule');
  }

  static forRoot(): ModuleWithProviders {
    return <ModuleWithProviders>{
      ngModule: CoreModule,
      providers: [
        ...NB_CORE_PROVIDERS,
      ],
    };
  }
}
