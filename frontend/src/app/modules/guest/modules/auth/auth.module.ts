/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { SharedModule, MaterialModules } from '@app/@shared';

import { VgCoreModule } from 'videogular2/core';
import { VgControlsModule } from 'videogular2/controls';
import { VgOverlayPlayModule } from 'videogular2/overlay-play';
import { VgBufferingModule } from 'videogular2/buffering';

import { AuthRoutingModule } from './auth-routing.module';
import { AuthLayoutComponent } from './layouts';
import { LoginComponent,
         ForgetPasswordComponent,
         ResetPasswordComponent,
         EmailVerificationComponent } from './components';

@NgModule({
  declarations: [
    AuthLayoutComponent,
    LoginComponent,
    ForgetPasswordComponent,
    ResetPasswordComponent,
    EmailVerificationComponent
  ],
  imports: [
    CommonModule,
    SharedModule,
    MaterialModules,
    AuthRoutingModule,
    VgCoreModule,
    VgControlsModule,
    VgOverlayPlayModule,
    VgBufferingModule
  ]
})
export class AuthModule { }
