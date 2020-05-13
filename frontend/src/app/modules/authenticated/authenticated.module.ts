/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { SharedModule, BootstrapModules, MaterialModules } from '@app/@shared';

import { AuthenticatedRoutingModule } from './authenticated-routing.module';
import { AuthenticatedLayoutComponent } from './layouts';

// Authenticated Components
import { BreadcrumbComponent,
         HeaderComponent,
         SidebarComponent,
         MenuComponent,
         MenuIconComponent,
         PageContainerComponent } from '@auth/components';
// import { ProviderCardComponent, ProvidersBarComponent, ProviderDetailBarComponent } from '@auth/modules/providers/components';

// Authenticated Services
import { SidebarService, PageService } from '@auth/services';

@NgModule({
  declarations: [
    AuthenticatedLayoutComponent,
    BreadcrumbComponent,
    HeaderComponent,
    SidebarComponent,
    MenuComponent,
    MenuIconComponent,
    PageContainerComponent,
    // ProviderCardComponent,
    // ProvidersBarComponent,
    // ProviderDetailBarComponent,
  ],
  imports: [
    CommonModule,
    SharedModule,
    BootstrapModules,
    MaterialModules,
    AuthenticatedRoutingModule,
  ],
  providers: [
    SidebarService,
    PageService
  ]
})
export class AuthenticatedModule { }
