/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Component, Input } from '@angular/core';

@Component({
  selector: 'heading-text',
  templateUrl: './heading-text.component.html',
  styleUrls: ['./heading-text.component.scss']
})
export class HeadingTextComponent {
  @Input() title: string = null;
  @Input() subtitle: string = null;
  @Input() divider: boolean = true;
  @Input() margin: boolean = true;
}
