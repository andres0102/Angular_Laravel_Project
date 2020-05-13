/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AssociateServicesModule } from '@app/@shared';

import { AssociatesClientRelationshipsRoutingModule } from './client-relationships-routing.module';
import { AssociatesClientRelationshipsLayoutComponent } from './layout/client-relationships-layout.component';
import { AssociatesClientRelationshipsMainComponent,
         AssociatesClientDetailsComponent,
         AssociatesClientParticularsComponent,
         AssociatesClientPoliciesComponent,
         AssociatesClientSubmissionsComponent,
         AssociatesClientActivityLogComponent } from './components';

@NgModule({
  declarations: [
    AssociatesClientRelationshipsLayoutComponent,
    AssociatesClientRelationshipsMainComponent,
    AssociatesClientDetailsComponent,
    AssociatesClientParticularsComponent,
    AssociatesClientPoliciesComponent,
    AssociatesClientSubmissionsComponent,
    AssociatesClientActivityLogComponent
  ],
  imports: [
    CommonModule,
    AssociateServicesModule,
    AssociatesClientRelationshipsRoutingModule,
  ],
  providers: [],
  entryComponents: [
  ],
})
export class AssociatesClientRelationshipsModule { }
