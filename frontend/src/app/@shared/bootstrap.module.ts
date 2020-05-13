/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { NgModule } from '@angular/core';

// Bootstrap Modules
import { BsDropdownModule,
         AccordionModule,
         AlertModule,
         ButtonsModule,
         CollapseModule,
         ModalModule,
         ProgressbarModule,
         TabsModule,
         // TooltipModule,
         TypeaheadModule } from 'ngx-bootstrap';

@NgModule({
  imports: [
    BsDropdownModule.forRoot(),
    AccordionModule.forRoot(),
    AlertModule.forRoot(),
    ButtonsModule.forRoot(),
    CollapseModule.forRoot(),
    ModalModule.forRoot(),
    ProgressbarModule.forRoot(),
    TabsModule.forRoot(),
    // TooltipModule.forRoot(),
    TypeaheadModule.forRoot()
  ],
  declarations: [],
  exports: [
    BsDropdownModule,
    AccordionModule,
    AlertModule,
    ButtonsModule,
    CollapseModule,
    ModalModule,
    ProgressbarModule,
    TabsModule,
    // TooltipModule,
    TypeaheadModule
  ]
})
export class BootstrapModules { }
