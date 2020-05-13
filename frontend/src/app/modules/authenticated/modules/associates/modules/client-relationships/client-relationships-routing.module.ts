/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { AssociatesClientRelationshipsLayoutComponent } from './layout/client-relationships-layout.component';
import { AssociatesClientRelationshipsMainComponent,
         AssociatesClientDetailsComponent,
         AssociatesClientParticularsComponent,
         AssociatesClientPoliciesComponent,
         AssociatesClientSubmissionsComponent,
         AssociatesClientActivityLogComponent } from './components';
import { AssociatesClientResolverService } from '@app/@shared/services/associates/client-relationships/client-resolver.service';

const routes: Routes = [
  {
    path: '',
    component: AssociatesClientRelationshipsLayoutComponent,
    children: [
      {
        path: '',
        data: {
          banner: true,
          title: 'Clients Database',
          hideHeader: true, // Automatically hides the header on page scroll
          footer: true
        },
        component: AssociatesClientRelationshipsMainComponent
      },
      {
        path: ':uuid',
        component: AssociatesClientDetailsComponent,
        // runGuardsAndResolvers: 'always',
        resolve: {
          client: AssociatesClientResolverService,
        },
        children: [
          {
            path: '',
            redirectTo: 'activity-log',
            pathMatch: 'full',
          },
          {
            path: 'activity-log',
            component: AssociatesClientActivityLogComponent,
            data: {
              title: 'Client Activity Log',
              hideHeader: true, // Automatically hides the header on page scroll
              footer: true
            },
          },
          {
            path: 'particulars',
            component: AssociatesClientParticularsComponent,
            data: {
              title: 'Client Particulars',
              hideHeader: true, // Automatically hides the header on page scroll
              footer: true
            },
          },
          {
            path: 'submissions',
            component: AssociatesClientSubmissionsComponent,
            data: {
              title: 'Client Submissions Records',
              hideHeader: true, // Automatically hides the header on page scroll
              footer: true
            },
          },
          {
            path: 'incepted-policies',
            component: AssociatesClientPoliciesComponent,
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
export class AssociatesClientRelationshipsRoutingModule { }
