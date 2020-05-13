/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { AdminClientRelationshipsLayoutComponent } from './layout/client-relationships-layout.component';
import { AdminClientRelationshipsMainComponent,
         AdminClientDetailsComponent,
         AdminClientParticularsComponent,
         AdminClientPoliciesComponent,
         AdminClientSubmissionsComponent,
         AdminClientActivityLogComponent } from './components';
import { AdminClientResolverService } from '@app/@shared/services/admin/client-relationships/client-resolver.service';

const routes: Routes = [
  {
    path: '',
    component: AdminClientRelationshipsLayoutComponent,
    children: [
      {
        path: '',
        data: {
          banner: true,
          title: 'Clients Database',
          hideHeader: true, // Automatically hides the header on page scroll
          footer: true
        },
        component: AdminClientRelationshipsMainComponent
      },
      {
        path: ':uuid',
        component: AdminClientDetailsComponent,
        // runGuardsAndResolvers: 'always',
        resolve: {
          client: AdminClientResolverService,
        },
        children: [
          {
            path: '',
            redirectTo: 'activity-log',
            pathMatch: 'full',
          },
          {
            path: 'activity-log',
            component: AdminClientActivityLogComponent,
            data: {
              title: 'Client Activity Log',
              hideHeader: true, // Automatically hides the header on page scroll
              footer: true
            },
          },
          {
            path: 'particulars',
            component: AdminClientParticularsComponent,
            data: {
              title: 'Client Particulars',
              hideHeader: true, // Automatically hides the header on page scroll
              footer: true
            },
          },
          {
            path: 'submissions',
            component: AdminClientSubmissionsComponent,
            data: {
              title: 'Client Submissions Records',
              hideHeader: true, // Automatically hides the header on page scroll
              footer: true
            },
          },
          {
            path: 'incepted-policies',
            component: AdminClientPoliciesComponent,
            data: {
              title: 'Client Policies Records',
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
export class AdminClientRelationshipsRoutingModule { }
