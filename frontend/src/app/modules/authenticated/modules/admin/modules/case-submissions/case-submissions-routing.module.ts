/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { AdminCaseSubmissionsLayoutComponent } from './layout/case-submissions-layout.component';
import { AdminCaseSubmissionsMainComponent,
         AdminCaseSubmissionsDetailsComponent,
         AdminSubmissionAuditLogComponent,
         AdminSubmissionCasesComponent,
         AdminSubmissionUploadedFilesComponent } from './components';
import { AdminCaseSubmissionsResolverService } from '@app/@shared/services/admin/case-submissions/case-submissions-resolver.service';

const routes: Routes = [
  {
    path: '',
    component: AdminCaseSubmissionsLayoutComponent,
    children: [
      {
        path: '',
        data: {
          banner: true,
          title: 'Case Submissions',
          hideHeader: true, // Automatically hides the header on page scroll
          footer: true
        },
        component: AdminCaseSubmissionsMainComponent
      },
      {
        path: ':uuid',
        data: {
          title: 'View Submission',
          hideHeader: true, // Automatically hides the header on page scroll
          footer: true
        },
        component: AdminCaseSubmissionsDetailsComponent,
        runGuardsAndResolvers: 'always',
        resolve: {
          submission: AdminCaseSubmissionsResolverService,
        },
        children: [
          {
            path: '',
            redirectTo: 'cases',
            pathMatch: 'full',
          },
          {
            path: 'cases',
            component: AdminSubmissionCasesComponent,
            data: {
              title: 'Submission Cases',
              hideHeader: true, // Automatically hides the header on page scroll
              footer: true
            },
          },
          {
            path: 'uploaded-files',
            component: AdminSubmissionUploadedFilesComponent,
            data: {
              title: 'Submission Uploaded Files',
              hideHeader: true, // Automatically hides the header on page scroll
              footer: true
            },
          },
          {
            path: 'audit-log',
            component: AdminSubmissionAuditLogComponent,
            data: {
              title: 'Submission Audit Log',
              hideHeader: true, // Automatically hides the header on page scroll
              footer: true
            },
          },
        ]
      }
    ]
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AdminCaseSubmissionsRoutingModule { }
