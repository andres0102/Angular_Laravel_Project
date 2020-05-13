/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { SharedModule, MaterialModules, BootstrapModules, AssociateServicesModule } from '@app/@shared';

import { AssociatesRoutingModule } from './associates-routing.module';

import {
  AssociateDashboardComponent,
  PayrollStatementsComponent,
  AssociatesSalesforceSubmissionsReportComponent } from './components';

import {
  WhatsNewWidgetComponent
} from '@auth/widgets';

@NgModule({
  declarations: [
    AssociateDashboardComponent,
    PayrollStatementsComponent,
    AssociatesSalesforceSubmissionsReportComponent,
    WhatsNewWidgetComponent
  ],
  imports: [
    CommonModule,
    SharedModule,
    BootstrapModules,
    MaterialModules,
    AssociateServicesModule.forRoot(),
    AssociatesRoutingModule,
  ],
  providers: []
})
export class AssociatesModule { }
