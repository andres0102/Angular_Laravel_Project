/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Title } from '@angular/platform-browser';
import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot } from '@angular/router';
import { filter } from 'rxjs/operators';
import { environment as env } from '@env/environment';

@Injectable()
export class TitleService {
  constructor(
    private title: Title
  ) {}

  setTitle(snapshot: ActivatedRouteSnapshot) {
    let lastChild = snapshot;
    while (lastChild.children.length) {
      lastChild = lastChild.children[0];
    }
    const { title } = lastChild.data;
    if (title) this.title.setTitle(`${title} - ${env.appName}`);
  }

  manualTitle(value: string) {
    this.title.setTitle(`${value} - ${env.appName}`);
  }
}