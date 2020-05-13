/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { ModuleWithProviders, NgModule, Optional, SkipSelf } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { LayoutModule } from '@angular/cdk/layout';

// 3rd Party Modules
import { SweetAlert2Module } from '@sweetalert2/ngx-sweetalert2';
import { NgxContentLoadingModule } from 'ngx-content-loading';
import { SwiperModule, SWIPER_CONFIG, SwiperConfigInterface } from 'ngx-swiper-wrapper';
import { PerfectScrollbarModule, PERFECT_SCROLLBAR_CONFIG, PerfectScrollbarConfigInterface } from 'ngx-perfect-scrollbar';
import { ChartjsModule } from '@ctrl/ngx-chartjs';
import { NgxDatatableModule } from '@swimlane/ngx-datatable';
import { MomentModule } from 'ngx-moment';
import { NgsRevealModule } from 'ngx-scrollreveal';
import { NgxFilesizeModule } from 'ngx-filesize';

// Bootstrap Modules
import { TooltipModule } from 'ngx-bootstrap/tooltip';

// Angular Material - Common Modules
import { MatButtonModule } from '@angular/material';
import { MatCardModule } from '@angular/material/card';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatProgressBarModule } from '@angular/material/progress-bar';

// Shared Directives
import { AutofocusDirective, RetinaDirective, PhonePipe, SortByPipe, SafePipe } from './directives';

// Shared Components
import { AccessDeniedComponent, HeadingTextComponent, CardLoaderComponent,} from './components';
import { MessageModule } from './components/message/message.module';
import { MessageService } from './components/message/message.service';

const DEFAULT_PERFECT_SCROLLBAR_CONFIG: PerfectScrollbarConfigInterface = { suppressScrollX: true };
const DEFAULT_SWIPER_CONFIG: SwiperConfigInterface = { direction: 'horizontal', slidesPerView: 'auto' };

export const SharedProviders = [
  { provide: SWIPER_CONFIG, useValue: DEFAULT_SWIPER_CONFIG },
  { provide: PERFECT_SCROLLBAR_CONFIG, useValue: DEFAULT_PERFECT_SCROLLBAR_CONFIG },
  MessageService
];

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    RouterModule,
    LayoutModule,

    // 3rd Party Modules
    SweetAlert2Module,
    NgxContentLoadingModule,
    SwiperModule,
    PerfectScrollbarModule,
    ChartjsModule,
    NgxDatatableModule,
    MomentModule,
    NgsRevealModule,
    NgxFilesizeModule,
    TooltipModule.forRoot(),
    MatCardModule, MatInputModule, MatSelectModule, MatIconModule, MatButtonModule,
    MatProgressSpinnerModule, MatProgressBarModule,

    MessageModule,
  ],
  declarations: [
    AutofocusDirective,
    RetinaDirective,
    PhonePipe,
    SortByPipe,
    SafePipe,
    AccessDeniedComponent,
    HeadingTextComponent,
    CardLoaderComponent,
  ],
  exports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    RouterModule,
    LayoutModule,

    // 3rd Party Modules
    SweetAlert2Module,
    NgxContentLoadingModule,
    SwiperModule,
    PerfectScrollbarModule,
    ChartjsModule,
    NgxDatatableModule,
    MomentModule,
    NgsRevealModule,
    NgxFilesizeModule,
    TooltipModule,
    MatCardModule, MatInputModule, MatSelectModule, MatIconModule, MatButtonModule,
    MatProgressSpinnerModule, MatProgressBarModule,

    MessageModule,

    // Internal Reusable Directives
    AutofocusDirective,
    RetinaDirective,
    PhonePipe,
    SortByPipe,
    SafePipe,

    // Internal Reusable Components
    AccessDeniedComponent,
    HeadingTextComponent,
    CardLoaderComponent,
  ]
})
export class SharedModule {
  static forRoot(): ModuleWithProviders {
    return <ModuleWithProviders>{
      ngModule: SharedModule,
      providers: [
        ...SharedProviders,
      ],
    };
  }
}
