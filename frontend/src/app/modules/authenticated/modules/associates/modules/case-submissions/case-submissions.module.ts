/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AssociateServicesModule } from '@app/@shared';
import { NgxDropzoneModule } from 'ngx-dropzone';

import { AssociatesCaseSubmissionsRoutingModule } from './case-submissions-routing.module';
// import { AssociatesCaseSubmissionsService,
//          AssociatesCaseSubmissionsResolverService,
//          AssociatesClientRelationshipsService } from '@associates/services';
import { AssociatesCaseSubmissionsLayoutComponent } from './layout/case-submissions-layout.component';
import { AssociatesCaseSubmissionsMainComponent,
         AssociatesCaseSubmissionsDetailsComponent,
         AssociatesSubmissionAuditLogComponent,
         AssociatesSubmissionCasesComponent,
         AssociatesSubmissionDraftComponent,
         AssociatesSubmissionUploadedFilesComponent
         // AssociatesCaseSubmissionsAddDialogComponent
       } from './components';
// import { AssociatesClientAddDialogComponent } from '@associates/modules/client-relationships/components/client-add-dialog/client-add-dialog.component';

@NgModule({
  declarations: [
    AssociatesCaseSubmissionsLayoutComponent,
    AssociatesCaseSubmissionsMainComponent,
    AssociatesCaseSubmissionsDetailsComponent,
    AssociatesSubmissionAuditLogComponent,
    AssociatesSubmissionCasesComponent,
    AssociatesSubmissionDraftComponent,
    AssociatesSubmissionUploadedFilesComponent
    // AssociatesCaseSubmissionsAddDialogComponent,
    // AssociatesClientAddDialogComponent
  ],
  imports: [
    CommonModule,
    // SharedModule,
    // BootstrapModules,
    // MaterialModules,
    AssociateServicesModule,
    NgxDropzoneModule,
    AssociatesCaseSubmissionsRoutingModule,
  ],
  providers: [
    // AssociatesCaseSubmissionsService,
    // AssociatesCaseSubmissionsResolverService,
    // AssociatesClientRelationshipsService
  ],
  entryComponents: [
    AssociatesSubmissionDraftComponent
    // AssociatesCaseSubmissionsAddDialogComponent,
    // AssociatesClientAddDialogComponent
  ],
})
export class AssociatesCaseSubmissionsModule { }
