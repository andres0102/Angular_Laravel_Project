/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { AuthenticatedLayoutComponent } from './layouts';

const routes: Routes = [
  {
    path: '',
    component: AuthenticatedLayoutComponent,
    data: {
      parent1: 'Home',
      parent1_link: '/'
    },
    children: [
      {
        path: '',
        // redirectTo: 'associates',
        // pathMatch: 'full',
      },
      // Associate module
      {
        path: 'associates',
        loadChildren: () => import('./modules/associates/associates.module').then(m => m.AssociatesModule),
      },

      // Admin authenticated
      {
        path: 'admin',
        loadChildren: () => import('./modules/admin/admin.module').then(m => m.AdminModule),
      },

      // General
      {
        path: 'general',
        loadChildren: () => import('./modules/general/general.module').then(m => m.GeneralModule),
      },
      // {
      //   path: 'products',
      //   loadChildren: './modules/products/products.module#ProductsModule'
      // },
      // {
      //   path: 'providers',
      //   loadChildren: './modules/providers/providers.module#ProvidersModule'
      // },
    ]
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AuthenticatedRoutingModule { }
