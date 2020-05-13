/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { NgModule } from '@angular/core';
import { BrowserModule, HammerGestureConfig, HAMMER_GESTURE_CONFIG } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { HttpClientModule } from '@angular/common/http';

import { CoreModule } from '@app/@core/core.module';
import { SharedModule, BootstrapModules } from '@app/@shared';

import * as Hammer from 'hammerjs';
import { SweetAlert2Module } from '@sweetalert2/ngx-sweetalert2';
import { DeviceDetectorModule } from 'ngx-device-detector';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';

export class MyHammerConfig extends HammerGestureConfig {
  buildHammer(element: HTMLElement) {
    const mc = new Hammer(element, { touchAction: 'pan-y' });
    return mc;
  }
}

@NgModule({
  declarations: [
    AppComponent
  ],
  imports: [
    AppRoutingModule,
    BrowserModule,
    BrowserAnimationsModule,
    HttpClientModule,

    CoreModule.forRoot(),
    SharedModule.forRoot(),
    BootstrapModules,

    // Third-Parties Plugins
    SweetAlert2Module.forRoot({ heightAuto: false, customClass: 'tw-swal' }),
    DeviceDetectorModule.forRoot()
  ],
  providers: [
    { provide: HAMMER_GESTURE_CONFIG, useClass: MyHammerConfig }
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
