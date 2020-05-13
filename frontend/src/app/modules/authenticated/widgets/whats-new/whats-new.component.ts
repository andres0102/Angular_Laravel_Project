/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { Component } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { ApiService } from '@app/@core/services';

@Component({
  selector: 'app-whats-new',
  templateUrl: './whats-new.component.html',
  styleUrls: ['./whats-new.component.scss']
})
export class WhatsNewWidgetComponent {
  notices = [];
  notices_loaded: boolean = false;

  init() {
    this.getNotices();
  }

  getNotices() {
    this.notices = [];
    this.notices_loaded = false;
    this.apiService.post('dashboard/notices').subscribe((result) => {
      if (!result.error) {
        this.notices = result.data;
        this.notices_loaded = true;
      }
    });
  }

  constructor(
    private apiService: ApiService) {
      this.init();
  }
}
