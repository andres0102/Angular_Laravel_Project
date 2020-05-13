/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { AdminSalesAssociatesLayoutComponent } from './layout/sales-associates-layout.component';
import { AdminSalesAssociatesMainComponent,
         AdminAssociateDetailsComponent,
         AdminAssociateSalesforceDataComponent,
         AdminAssociateParticularsComponent,
         AdminAssociateClientsComponent,
         AdminAssociatePoliciesComponent,
         AdminAssociateSubmissionsComponent,
         AdminAssociateAuditLogComponent
       } from './components';
import { AdminAssociateResolverService } from '@app/@shared/services/admin/sales-associates/associate-resolver.service';

const routes: Routes = [
  {
    path: '',
    component: AdminSalesAssociatesLayoutComponent,
    children: [
      {
        path: '',
        data: {
          banner: true,
          title: 'Sales Associates Database',
          hideHeader: true, // Automatically hides the header on page scroll
          footer: true
        },
        component: AdminSalesAssociatesMainComponent
      },
      {
        path: ':uuid',
        component: AdminAssociateDetailsComponent,
        // runGuardsAndResolvers: 'always',
        resolve: {
          associate: AdminAssociateResolverService,
        },
        children: [
          {
            path: '',
            redirectTo: 'salesforce-data',
            pathMatch: 'full',
          },
          {
            path: 'salesforce-data',
            component: AdminAssociateSalesforceDataComponent,
            data: {
              title: 'Associate Salesforce Data',
              hideHeader: true, // Automatically hides the header on page scroll
              footer: true
            },
          },
          {
            path: 'personal-particulars',
            component: AdminAssociateParticularsComponent,
            data: {
              title: 'Associate Personal Particulars',
              hideHeader: true, // Automatically hides the header on page scroll
              footer: true
            },
          },
          {
            path: 'clients',
            component: AdminAssociateClientsComponent,
            data: {
              title: 'Associate Clients Records',
              hideHeader: true, // Automatically hides the header on page scroll
              footer: true
            },
          },
          {
            path: 'submissions',
            component: AdminAssociateSubmissionsComponent,
            data: {
              title: 'Associate Submissions Records',
              hideHeader: true, // Automatically hides the header on page scroll
              footer: true
            },
          },
          {
            path: 'incepted-policies',
            component: AdminAssociatePoliciesComponent,
            data: {
              title: 'Associate Policies Records',
              hideHeader: true, // Automatically hides the header on page scroll
              footer: true
            },
          },
          {
            path: 'audit-log',
            component: AdminAssociateAuditLogComponent,
            data: {
              title: 'Associate Audit Log',
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
export class AdminSalesAssociatesRoutingModule { }
