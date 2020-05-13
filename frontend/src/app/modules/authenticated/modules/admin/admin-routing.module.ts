/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

const routes: Routes = [
  {
    path: '',
    data: {
      parent2: 'Admin',
      parent2_link: '/admin'
    },
    children: [
      {
        path: '',
        redirectTo: 'case-submissions',
        pathMatch: 'full',
      },
      {
        path: 'case-submissions',
        loadChildren: () => import('./modules/case-submissions/case-submissions.module').then(m => m.AdminCaseSubmissionsModule),
      },
      {
        path: 'sales-associates',
        loadChildren: () => import('./modules/sales-associates/sales-associates.module').then(m => m.AdminSalesAssociatesModule),
      },
      {
        path: 'clients',
        loadChildren: () => import('./modules/client-relationships/client-relationships.module').then(m => m.AdminClientRelationshipsModule),
      },
      {
        path: 'incepted-policies',
        loadChildren: () => import('./modules/client-policies/client-policies.module').then(m => m.AdminClientPoliciesModule),
      },
      // {
      //   path: 'user-accounts',
      //   loadChildren: () => import('./modules/user-accounts/user-accounts.module').then(m => m.UserAccountsModule),
      // },
    ]
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AdminRoutingModule { }
