/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AdminServicesModule } from '@app/@shared';

import { AdminSalesAssociatesRoutingModule } from './sales-associates-routing.module';
import { AdminSalesAssociatesLayoutComponent } from './layout/sales-associates-layout.component';
import { AdminSalesAssociatesMainComponent,
         AdminAssociateDetailsComponent,
         AdminAssociateSalesforceDataComponent,
         AdminAssociateParticularsComponent,
         AdminAssociateClientsComponent,
         AdminAssociatePoliciesComponent,
         AdminAssociateSubmissionsComponent,
         AdminAssociateAuditLogComponent
       } from './components';

@NgModule({
  declarations: [
    AdminSalesAssociatesLayoutComponent,
    AdminSalesAssociatesMainComponent,
    AdminAssociateDetailsComponent,
    AdminAssociateSalesforceDataComponent,
    AdminAssociateParticularsComponent,
    AdminAssociateClientsComponent,
    AdminAssociatePoliciesComponent,
    AdminAssociateSubmissionsComponent,
    AdminAssociateAuditLogComponent
  ],
  imports: [
    CommonModule,
    AdminServicesModule,
    AdminSalesAssociatesRoutingModule,
  ],
  providers: [],
  entryComponents: [
  ],
})
export class AdminSalesAssociatesModule { }
