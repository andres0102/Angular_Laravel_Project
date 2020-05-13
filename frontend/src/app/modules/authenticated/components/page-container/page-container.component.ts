import { Component, HostListener } from '@angular/core';
import { PageService } from '@auth/services';

@Component({
  selector: 'page-container',
  template: '<ng-content></ng-content>',
  styleUrls: ['./page-container.scss'],
  host:{
    'class': 'page-container'
  }
})
export class PageContainerComponent {
  constructor(private toggler: PageService) { }

  @HostListener('mouseenter', ["$event"])
  @HostListener('tap', ["$event"])
  triggerMouseOverCall(){
    this.toggler.triggerPageContainerHover(true);
  }
}
