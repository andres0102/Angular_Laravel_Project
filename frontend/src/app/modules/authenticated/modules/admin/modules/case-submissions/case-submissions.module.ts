/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AdminServicesModule } from '@app/@shared';
import { NgxDropzoneModule } from 'ngx-dropzone';

import { AdminCaseSubmissionsRoutingModule } from './case-submissions-routing.module';
import { AdminCaseSubmissionsLayoutComponent } from './layout/case-submissions-layout.component';
import { AdminCaseSubmissionsMainComponent,
         AdminCaseSubmissionsDetailsComponent,
         AdminSubmissionAuditLogComponent,
         AdminSubmissionCasesComponent,
         AdminSubmissionUploadedFilesComponent
       } from './components';

@NgModule({
  declarations: [
    AdminCaseSubmissionsLayoutComponent,
    AdminCaseSubmissionsMainComponent,
    AdminCaseSubmissionsDetailsComponent,
    AdminSubmissionAuditLogComponent,
    AdminSubmissionCasesComponent,
    AdminSubmissionUploadedFilesComponent
  ],
  imports: [
    CommonModule,
    AdminServicesModule,
    NgxDropzoneModule,
    AdminCaseSubmissionsRoutingModule,
  ],
  providers: [],
  entryComponents: [],
})
export class AdminCaseSubmissionsModule { }
