/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AdminServicesModule } from '@app/@shared';

import { AdminClientPoliciesRoutingModule } from './client-policies-routing.module';
import { AdminClientPoliciesLayoutComponent } from './layout/client-policies-layout.component';
import { AdminClientPoliciesMainComponent } from './components';

@NgModule({
  declarations: [
    AdminClientPoliciesLayoutComponent,
    AdminClientPoliciesMainComponent,
  ],
  imports: [
    CommonModule,
    AdminServicesModule,
    AdminClientPoliciesRoutingModule,
  ],
  providers: [],
  entryComponents: [],
})
export class AdminClientPoliciesModule { }

