/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Injectable } from '@angular/core';
import { Resolve, RouterStateSnapshot, ActivatedRouteSnapshot } from '@angular/router';
import { Observable, of } from 'rxjs';
import { map, take } from 'rxjs/operators';

import { Response, Associate } from '@app/models';
import { AdminSalesAssociatesService } from './sales-associates.service';

@Injectable()
export class AdminAssociateResolverService implements Resolve<Response> {
  associate: Associate;

  constructor(private adminSalesAssocService: AdminSalesAssociatesService) {}

  resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): any {
    if (route.params.uuid) {
      return this.adminSalesAssocService.getAssociates(route.params.uuid).pipe(
        take(1),
        map(res => {
          this.associate = res['data'];
          // console.log('Sales Associates Resolver:', this.associate);
          return this.associate;
        })
      );
    } else {
      return false;
    }
  }
}
