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

import { AssociatesTeamSubmissionsRoutingModule } from './team-submissions-routing.module';
import { AssociatesTeamSubmissionsLayoutComponent } from './layout/team-submissions-layout.component';
import { AssociatesTeamSubmissionsMainComponent,
         AssociatesTeamSubmissionsDetailsComponent,
         AssociatesTeamSubmissionAuditLogComponent,
         AssociatesTeamSubmissionCasesComponent,
         AssociatesTeamSubmissionUploadedFilesComponent
       } from './components';

@NgModule({
  declarations: [
    AssociatesTeamSubmissionsLayoutComponent,
    AssociatesTeamSubmissionsMainComponent,
    AssociatesTeamSubmissionsDetailsComponent,
    AssociatesTeamSubmissionAuditLogComponent,
    AssociatesTeamSubmissionCasesComponent,
    AssociatesTeamSubmissionUploadedFilesComponent
  ],
  imports: [
    CommonModule,
    AssociateServicesModule,
    NgxDropzoneModule,
    AssociatesTeamSubmissionsRoutingModule,
  ],
  providers: [],
  entryComponents: [],
})
export class AssociatesTeamSubmissionsModule { }
