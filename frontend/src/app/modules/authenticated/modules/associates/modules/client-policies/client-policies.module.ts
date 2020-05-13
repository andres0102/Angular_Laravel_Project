/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AssociateServicesModule } from '@app/@shared';

import { AssociatesClientPoliciesRoutingModule } from './client-policies-routing.module';
import { AssociatesClientPoliciesLayoutComponent } from './layout/client-policies-layout.component';
import { AssociatesClientPoliciesMainComponent } from './components';

@NgModule({
  declarations: [
    AssociatesClientPoliciesLayoutComponent,
    AssociatesClientPoliciesMainComponent,
  ],
  imports: [
    CommonModule,
    AssociateServicesModule,
    AssociatesClientPoliciesRoutingModule,
  ],
  providers: [],
  entryComponents: [],
})
export class AssociatesClientPoliciesModule { }
