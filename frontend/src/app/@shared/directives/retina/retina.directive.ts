/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Directive, Input, OnInit, ElementRef, Renderer2 } from '@angular/core';

@Directive({
  selector: '[retina]'
})
export class RetinaDirective implements OnInit {
  isRetina:boolean = false;
  _srcRetina;
  _src;
  _srcPng;

  constructor(private El: ElementRef,private renderer: Renderer2) {
    this.isRetina = window.devicePixelRatio > 1;
  }

  @Input()
  set srcPng(value:string){
    this._srcPng = value;
  }

  @Input()
  set src2x(value:string){
    this._srcRetina = value;
  }

  @Input()
  set src1x(value:string){
    this._src = value;
  }

  ngOnInit() {
    if (this._srcPng) {
      this.renderer.setAttribute(this.El.nativeElement, "src", this._srcPng);
    } else if (this.isRetina) {
      this.renderer.setAttribute(this.El.nativeElement, "src", this._srcRetina);
    } else {
      this.renderer.setAttribute(this.El.nativeElement, "src", this._src);
    }
  }
}
