/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { AdminClientPoliciesLayoutComponent } from './layout/client-policies-layout.component';
import { AdminClientPoliciesMainComponent } from './components';
import { AdminClientPoliciesResolverService } from '@app/@shared/services/admin/client-policies/client-policies-resolver.service';

const routes: Routes = [
  {
    path: '',
    component: AdminClientPoliciesLayoutComponent,
    children: [
      {
        path: '',
        data: {
          banner: true,
          title: 'Client Policies',
          hideHeader: true, // Automatically hides the header on page scroll
          footer: true
        },
        component: AdminClientPoliciesMainComponent
      },
    ]
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AdminClientPoliciesRoutingModule { }
