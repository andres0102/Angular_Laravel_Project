/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Component, Input } from '@angular/core';

@Component({
  selector: 'access-denied',
  templateUrl: './access-denied.component.html',
  styleUrls: ['./access-denied.component.scss']
})
export class AccessDeniedComponent {
  @Input() title: string = "It appears that you do not have sufficient access rights to view this page.";
  @Input() linkText: string = "Back to Dashboard";
  @Input() link: string = "/authenticated/personal/dashboard";
}
