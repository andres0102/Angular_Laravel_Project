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

import { Response, Policy } from '@app/models';
import { AssociatesClientPoliciesService } from './client-policies.service';

@Injectable()
export class AssociatesClientPoliciesResolverService implements Resolve<Response> {
  policy: Policy;

  constructor(private assPolicyService: AssociatesClientPoliciesService) {}

  resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): any {
    if (route.params.uuid) {
      return this.assPolicyService.getPolicies(route.params.uuid).pipe(
        take(1),
        map(res => {
          this.policy = res['data'];
          // console.log('Policy Resolver:', this.policy);
          return this.policy;
        })
      );
    } else {
      return false;
    }
  }
}
