/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Injectable } from '@angular/core';
import { ApiService } from '@app/@core/services/api';

@Injectable()
export class AdminClientPoliciesService {

  constructor(private apiService: ApiService) { }

  getPolicies(uuid = null) {
    let url = 'admin/policies';
    url += (uuid) ? '/' + uuid : '';
    return this.apiService.get(url);
  }
}