/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { AuthLayoutComponent } from './layouts';
import { LoginComponent,
         ForgetPasswordComponent,
         ResetPasswordComponent,
         EmailVerificationComponent } from './components';
import { ResetPasswordTokenResolverService } from './resolvers';

const routes: Routes = [
  {
    path: '',
    component: AuthLayoutComponent,
    children: [
      {
        path: '',
        redirectTo: 'login',
        pathMatch: 'full',
      },
      {
        path: 'login',
        component: LoginComponent,
        data: { title: 'Sign into your Account' }
      },
      {
        path: 'welcome/:token',
        component: EmailVerificationComponent,
        data: { title: 'Welcome' }
      },
      {
        path: 'forget-password',
        component: ForgetPasswordComponent,
        data: { title: 'Retrieve your password' }
      },
      {
        path: 'reset-password/:token',
        component: ResetPasswordComponent,
        data: { title: 'Reset your password' },
        resolve: {
          response: ResetPasswordTokenResolverService,
        }
      },
      {
        path: 'email-verification/:token',
        component: EmailVerificationComponent,
        data: { title: 'Email Verification' }
      }
    ]
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AuthRoutingModule { }
