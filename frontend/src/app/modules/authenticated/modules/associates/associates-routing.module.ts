/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import {
  AssociateDashboardComponent,
  PayrollStatementsComponent,
  AssociatesSalesforceSubmissionsReportComponent } from './components'

const routes: Routes = [
  {
    path: '',
    data: {
      parent2: 'Associates',
      parent2_link: '/associates'
    },
    children: [
      {
        path: '',
        redirectTo: 'dashboard/',
        pathMatch: 'full',
      },
      // Associate Dashboard
      {
        path: 'dashboard',
        redirectTo: 'dashboard/',
        pathMatch: 'full'
      },
      {
        path: 'dashboard/:summary_range',
        data: {
          title: 'Associate Dashboard',
          hideHeader: true,
          footer: true
        },
        component: AssociateDashboardComponent
      },
      // Financial Advisory Business
      {
        path: 'business',
        children: [
          {
            path: '',
            redirectTo: 'case-submissions',
            pathMatch: 'full',
          },
          // Case Submissions
          {
            path: 'case-submissions',
            loadChildren: () => import('./modules/case-submissions/case-submissions.module').then(m => m.AssociatesCaseSubmissionsModule),
          },
          // Client Policies
          {
            path: 'client-policies',
            loadChildren: () => import('./modules/client-policies/client-policies.module').then(m => m.AssociatesClientPoliciesModule),
          },
          // Products Catalog
          {
            path: 'products-catalog',
            loadChildren: () => import('./modules/products-catalog/products-catalog.module').then(m => m.AssociatesProductsCatalogModule),
          },
        ]
      },
      // Teams Management
      {
        path: 'teams',
        children: [
          {
            path: '',
            redirectTo: 'case-submissions',
            pathMatch: 'full',
          },
          {
            path: 'case-submissions',
            loadChildren: () => import('./modules/team-submissions/team-submissions.module').then(m => m.AssociatesTeamSubmissionsModule),
          },
          {
            path: 'submissions-report',
            component: AssociatesSalesforceSubmissionsReportComponent,
            data: {
              title: 'Salesforce Submissions Report',
              footer: true
            },
          }
        ]
      },
      // Relationships
      {
        path: 'relationships',
        children: [
          {
            path: '',
            redirectTo: 'clients',
            pathMatch: 'full',
          },
          // Clients
          {
            path: 'clients',
            loadChildren: () => import('./modules/client-relationships/client-relationships.module').then(m => m.AssociatesClientRelationshipsModule),
          },
         ]
      },
      // {
      //   path: 'profile',
      //   children: [
      //     {
      //       path: '',
      //       redirectTo: 'view',
      //       pathMatch: 'full',
      //     },
      //     {
      //       path: 'view',
      //       data: {
      //         title: 'View Profile',
      //         breadcrumb: true,
      //         footer: true
      //       },
      //       component: ProfileViewComponent
      //     },
      //     {
      //       path: 'update',
      //       data: {
      //         title: 'Update Profile',
      //         breadcrumb: true,
      //         footer: true
      //       },
      //       component: ProfileUpdateComponent
      //     },
      //   ]
      // },
      {
        path: 'payroll',
        children: [
          {
            path: '',
            redirectTo: 'statements',
            pathMatch: 'full',
          },
          {
            path: 'statements',
            data: {
              title: 'Payroll Statements',
              breadcrumb: true,
              footer: true
            },
            component: PayrollStatementsComponent
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
export class AssociatesRoutingModule { }
