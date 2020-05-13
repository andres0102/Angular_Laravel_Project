import { Component, OnInit, Input } from '@angular/core';

import { Breadcrumb } from '@app/models';

@Component({
  selector: 'breadcrumb',
  templateUrl: './breadcrumb.component.html',
  styleUrls: ['./breadcrumb.component.scss']
})
export class BreadcrumbComponent implements OnInit {

  @Input()
  data: Breadcrumb = {
    _pageTitle: "",
    _parent1: "",
    _parent2: "",
    _parent3: "",
    _parent1_link: "",
    _parent2_link: "",
    _parent3_link: "",
  };

  constructor() { }

  ngOnInit() {
  }

}
