/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AssociateServicesModule } from '@app/@shared';

import { AssociatesProductsCatalogRoutingModule } from './products-catalog-routing.module';
import { AssociatesProductsCatalogLayoutComponent } from './layout/products-catalog-layout.component';
import { AssociatesProductsCatalogMainComponent } from './components';

@NgModule({
  declarations: [
    AssociatesProductsCatalogLayoutComponent,
    AssociatesProductsCatalogMainComponent,
  ],
  imports: [
    CommonModule,
    AssociateServicesModule,
    AssociatesProductsCatalogRoutingModule,
  ],
  providers: [],
  entryComponents: [],
})
export class AssociatesProductsCatalogModule { }
