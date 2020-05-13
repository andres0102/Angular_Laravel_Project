/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { AssociatesClientPoliciesLayoutComponent } from './layout/client-policies-layout.component';
import { AssociatesClientPoliciesMainComponent } from './components';
import { AssociatesClientPoliciesResolverService } from '@app/@shared/services/associates/client-policies/client-policies-resolver.service';

const routes: Routes = [
  {
    path: '',
    component: AssociatesClientPoliciesLayoutComponent,
    children: [
      {
        path: '',
        data: {
          banner: true,
          title: 'Client Policies',
          hideHeader: true, // Automatically hides the header on page scroll
          footer: true
        },
        component: AssociatesClientPoliciesMainComponent
      }
    ]
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AssociatesClientPoliciesRoutingModule { }
