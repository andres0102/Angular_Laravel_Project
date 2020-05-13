/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { AssociatesTeamSubmissionsLayoutComponent } from './layout/team-submissions-layout.component';
import { AssociatesTeamSubmissionsMainComponent,
         AssociatesTeamSubmissionsDetailsComponent,
         AssociatesTeamSubmissionAuditLogComponent,
         AssociatesTeamSubmissionCasesComponent,
         AssociatesTeamSubmissionUploadedFilesComponent } from './components';
import { AssociatesCaseSubmissionsResolverService } from '@app/@shared/services/associates/case-submissions/case-submissions-resolver.service';


const routes: Routes = [
  {
    path: '',
    component: AssociatesTeamSubmissionsLayoutComponent,
    children: [
      {
        path: '',
        data: {
          banner: true,
          title: 'Team Submissions',
          hideHeader: true, // Automatically hides the header on page scroll
          footer: true
        },
        component: AssociatesTeamSubmissionsMainComponent
      },
      {
        path: ':uuid',
        data: {
          title: 'View Submission',
          hideHeader: true, // Automatically hides the header on page scroll
          footer: true
        },
        component: AssociatesTeamSubmissionsDetailsComponent,
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
            component: AssociatesTeamSubmissionCasesComponent,
            data: {
              title: 'Submission Cases',
              hideHeader: true, // Automatically hides the header on page scroll
              footer: true
            },
          },
          {
            path: 'uploaded-files',
            component: AssociatesTeamSubmissionUploadedFilesComponent,
            data: {
              title: 'Submission Uploaded Files',
              hideHeader: true, // Automatically hides the header on page scroll
              footer: true
            },
          },
          {
            path: 'audit-log',
            component: AssociatesTeamSubmissionAuditLogComponent,
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
export class AssociatesTeamSubmissionsRoutingModule { }
