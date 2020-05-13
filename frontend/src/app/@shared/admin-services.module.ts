/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { ModuleWithProviders, NgModule } from '@angular/core';
// import { WebcamModule } from 'ngx-webcam';
import { NgxDropzoneModule } from 'ngx-dropzone';

import { SharedModule } from './shared.module';
import { MaterialModules } from './material.module';
import { BootstrapModules } from './bootstrap.module';

// Admin :: Shared Components
import { AdminAssociateAddDialogComponent,
         AdminSalesforceAddBandingLfaDialogComponent,
         AdminSalesforceAddBandingGiDialogComponent,
         AdminSalesforceAddMovementDialogComponent,
         AdminSalesforceAddProviderCodeDialogComponent } from '@app/@shared/components/admin';

// Admin :: Shared Services
import { AdminAssociateResolverService, AdminSalesAssociatesService } from '@app/@shared/services/admin/sales-associates';
import { AdminClientResolverService, AdminClientRelationshipsService } from '@app/@shared/services/admin/client-relationships';
import { AdminClientPoliciesResolverService, AdminClientPoliciesService } from '@app/@shared/services/admin/client-policies';
import { AdminCaseSubmissionsResolverService, AdminCaseSubmissionsService } from '@app/@shared/services/admin/case-submissions';

export const AdminProviders = [
  AdminSalesAssociatesService, AdminAssociateResolverService,
  AdminClientResolverService, AdminClientRelationshipsService,
  AdminClientPoliciesResolverService, AdminClientPoliciesService,
  AdminCaseSubmissionsResolverService, AdminCaseSubmissionsService,
];

@NgModule({
  imports: [
    SharedModule,
    BootstrapModules,
    MaterialModules,
    // WebcamModule,
    NgxDropzoneModule,
  ],
  declarations: [
    AdminAssociateAddDialogComponent,
    AdminSalesforceAddBandingLfaDialogComponent,
    AdminSalesforceAddBandingGiDialogComponent,
    AdminSalesforceAddMovementDialogComponent,
    AdminSalesforceAddProviderCodeDialogComponent
  ],
  exports: [
    SharedModule,
    BootstrapModules,
    MaterialModules,
    AdminAssociateAddDialogComponent,
    AdminSalesforceAddBandingLfaDialogComponent,
    AdminSalesforceAddBandingGiDialogComponent,
    AdminSalesforceAddMovementDialogComponent,
    AdminSalesforceAddProviderCodeDialogComponent
  ],
  entryComponents: [
    AdminAssociateAddDialogComponent,
    AdminSalesforceAddBandingLfaDialogComponent,
    AdminSalesforceAddBandingGiDialogComponent,
    AdminSalesforceAddMovementDialogComponent,
    AdminSalesforceAddProviderCodeDialogComponent
  ],
})
export class AdminServicesModule {
  static forRoot(): ModuleWithProviders {
    return <ModuleWithProviders>{
      ngModule: AdminServicesModule,
      providers: [
        ...AdminProviders,
      ],
    };
  }
}
