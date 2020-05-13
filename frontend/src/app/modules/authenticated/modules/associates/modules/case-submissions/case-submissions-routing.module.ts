/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { AssociatesCaseSubmissionsLayoutComponent } from './layout/case-submissions-layout.component';
import { AssociatesCaseSubmissionsMainComponent,
         AssociatesCaseSubmissionsDetailsComponent,
         AssociatesSubmissionAuditLogComponent,
         AssociatesSubmissionCasesComponent,
         AssociatesSubmissionUploadedFilesComponent } from './components';
import { AssociatesCaseSubmissionsResolverService } from '@app/@shared/services/associates/case-submissions/case-submissions-resolver.service';

const routes: Routes = [
  {
    path: '',
    component: AssociatesCaseSubmissionsLayoutComponent,
    children: [
      {
        path: '',
        data: {
          banner: true,
          title: 'Case Submissions',
          hideHeader: true, // Automatically hides the header on page scroll
          footer: true
        },
        component: AssociatesCaseSubmissionsMainComponent
      },
      {
        path: ':uuid',
        data: {
          title: 'View Submission',
          hideHeader: true, // Automatically hides the header on page scroll
          footer: true
        },
        component: AssociatesCaseSubmissionsDetailsComponent,
        runGuardsAndResolvers: 'always',
        resolve: {
          submission: AssociatesCaseSubmissionsResolverService,
        },
        children: [
          {
            path: '',
            redirectTo: 'cases',
            pathMatch: 'full',
          },
          {
            path: 'cases',
            component: AssociatesSubmissionCasesComponent,
            data: {
              title: 'Submission Cases',
              hideHeader: true, // Automatically hides the header on page scroll
              footer: true
            },
          },
          {
            path: 'uploaded-files',
            component: AssociatesSubmissionUploadedFilesComponent,
            data: {
              title: 'Submission Uploaded Files',
              hideHeader: true, // Automatically hides the header on page scroll
              footer: true
            },
          },
          {
            path: 'audit-log',
            component: AssociatesSubmissionAuditLogComponent,
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
export class AssociatesCaseSubmissionsRoutingModule { }
