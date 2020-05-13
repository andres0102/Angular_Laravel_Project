/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { ModuleWithProviders, NgModule } from '@angular/core';
import { WebcamModule } from 'ngx-webcam';
import { NgxDropzoneModule } from 'ngx-dropzone';

import { SharedModule } from './shared.module';
import { MaterialModules } from './material.module';
import { BootstrapModules } from './bootstrap.module';

// Associate :: Shared Components
import { AssociatesCaseSubmissionsAddDialogComponent,
         AssociatesClientAddDialogComponent, AssociatesClientMergeDialogComponent } from '@app/@shared/components/associates';

// Associate :: Shared Services
import { AssociatesClientRelationshipsService, AssociatesClientResolverService } from '@app/@shared/services/associates/client-relationships';
import { AssociatesCaseSubmissionsService, AssociatesCaseSubmissionsResolverService } from '@app/@shared/services/associates/case-submissions';
import { AssociatesClientPoliciesService, AssociatesClientPoliciesResolverService } from '@app/@shared/services/associates/client-policies';
import { AssociatesProductsCatalogService } from '@app/@shared/services/associates/products-catalog';

export const AssociatesProviders = [
  AssociatesClientRelationshipsService, AssociatesClientResolverService,
  AssociatesCaseSubmissionsService, AssociatesCaseSubmissionsResolverService,
  AssociatesClientPoliciesService, AssociatesClientPoliciesResolverService,
  AssociatesProductsCatalogService
];

@NgModule({
  imports: [
    SharedModule,
    BootstrapModules,
    MaterialModules,
    WebcamModule,
    NgxDropzoneModule,
  ],
  declarations: [
    AssociatesCaseSubmissionsAddDialogComponent,
    AssociatesClientMergeDialogComponent,
    AssociatesClientAddDialogComponent
  ],
  exports: [
    SharedModule,
    BootstrapModules,
    MaterialModules,
    AssociatesCaseSubmissionsAddDialogComponent,
    AssociatesClientMergeDialogComponent,
    AssociatesClientAddDialogComponent
  ],
  entryComponents: [
    AssociatesCaseSubmissionsAddDialogComponent,
    AssociatesClientMergeDialogComponent,
    AssociatesClientAddDialogComponent,
  ],
})
export class AssociateServicesModule {
  static forRoot(): ModuleWithProviders {
    return <ModuleWithProviders>{
      ngModule: AssociateServicesModule,
      providers: [
        ...AssociatesProviders,
      ],
    };
  }
}
