/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AdminServicesModule } from '@app/@shared';

import { AdminClientRelationshipsRoutingModule } from './client-relationships-routing.module';
import { AdminClientRelationshipsLayoutComponent } from './layout/client-relationships-layout.component';
import { AdminClientRelationshipsMainComponent,
         AdminClientDetailsComponent,
         AdminClientParticularsComponent,
         AdminClientPoliciesComponent,
         AdminClientSubmissionsComponent,
         AdminClientActivityLogComponent } from './components';

@NgModule({
  declarations: [
    AdminClientRelationshipsLayoutComponent,
    AdminClientRelationshipsMainComponent,
    AdminClientDetailsComponent,
    AdminClientParticularsComponent,
    AdminClientPoliciesComponent,
    AdminClientSubmissionsComponent,
    AdminClientActivityLogComponent
  ],
  imports: [
    CommonModule,
    AdminServicesModule,
    AdminClientRelationshipsRoutingModule,
  ],
  providers: [],
  entryComponents: [
  ],
})
export class AdminClientRelationshipsModule { }
