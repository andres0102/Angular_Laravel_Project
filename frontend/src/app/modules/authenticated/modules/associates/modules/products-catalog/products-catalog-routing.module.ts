/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { AssociatesProductsCatalogLayoutComponent } from './layout/products-catalog-layout.component';
import { AssociatesProductsCatalogMainComponent } from './components';

const routes: Routes = [
  {
    path: '',
    component: AssociatesProductsCatalogLayoutComponent,
    children: [
      {
        path: '',
        data: {
          title: 'Products Catalog',
          banner: true,
          hideHeader: true, // Automatically hides the header on page scroll
          footer: true
        },
        component: AssociatesProductsCatalogMainComponent
      }
    ]
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AssociatesProductsCatalogRoutingModule { }
