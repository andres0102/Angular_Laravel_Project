/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { SharedModule, MaterialModules, BootstrapModules } from '@app/@shared';
import { CKEditorModule } from '@ckeditor/ckeditor5-angular';

import { GeneralRoutingModule } from './general-routing.module';

import {
  NoticesComponent } from './components'

@NgModule({
  declarations: [
    NoticesComponent
  ],
  imports: [
    CommonModule,
    SharedModule,
    BootstrapModules,
    MaterialModules,
    GeneralRoutingModule,
    CKEditorModule
  ],
  providers: []
})
export class GeneralModule { }
