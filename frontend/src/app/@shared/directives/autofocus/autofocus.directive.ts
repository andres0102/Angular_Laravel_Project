/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { AfterContentInit, Directive, ElementRef, Input } from '@angular/core';

@Directive({
    selector: '[autofocus]'
})
export class AutofocusDirective implements AfterContentInit {
  @Input() public autofucus: boolean;

  public constructor(private el: ElementRef) { }

  public ngAfterContentInit() {
      setTimeout(() => {
          this.el.nativeElement.focus();
      }, 500);
  }

}