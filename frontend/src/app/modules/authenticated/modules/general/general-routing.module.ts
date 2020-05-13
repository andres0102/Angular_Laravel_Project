/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import {
  NoticesComponent } from './components'

const routes: Routes = [
  {
    path: '',
    redirectTo: 'notices/',
    pathMatch: 'full',
  },
  {
    path: 'notices',
    redirectTo: 'notices/',
    pathMatch: 'full',
    data: {
      title: 'Notice Board',
      hideHeader: true,
      footer: true
    },
  },
  {
    path: 'notices/:notice_uuid',
    data: {
      title: 'Notice',
      hideHeader: true,
      footer: true
    },
    component: NoticesComponent
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class GeneralRoutingModule { }
