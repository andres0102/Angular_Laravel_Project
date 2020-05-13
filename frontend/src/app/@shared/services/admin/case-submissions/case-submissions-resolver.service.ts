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

import { Response, Submission } from '@app/models';
import { AdminCaseSubmissionsService } from './case-submissions.service';

@Injectable()
export class AdminCaseSubmissionsResolverService implements Resolve<Response> {
  submission: Submission;

  constructor(private assSubmissionService: AdminCaseSubmissionsService) {}

  resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): any {
    if (route.params.uuid) {
      return this.assSubmissionService.getSubmissions(route.params.uuid).pipe(
        take(1),
        map(res => {
          this.submission = res['data'];
          // console.log('Submission Resolver:', this.submission);
          return this.submission;
        })
      );
    } else {
      return false;
    }
  }
}
