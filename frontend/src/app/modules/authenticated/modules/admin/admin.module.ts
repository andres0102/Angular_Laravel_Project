/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { SharedModule, MaterialModules, BootstrapModules, AdminServicesModule } from '@app/@shared';

import { AdminRoutingModule } from './admin-routing.module';

@NgModule({
  declarations: [ ],
  imports: [
    CommonModule,
    SharedModule,
    BootstrapModules,
    MaterialModules,
    AdminServicesModule.forRoot(),
    AdminRoutingModule,
  ],
  providers: []
})
export class AdminModule { }
