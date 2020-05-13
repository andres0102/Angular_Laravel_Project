/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Injectable } from '@angular/core';
import { ApiService } from '@app/@core/services/api';

@Injectable()
export class AssociatesProductsCatalogService {

  constructor(private apiService: ApiService) { }

  getProducts(uuid = null) {
    let url = 'associates/products';
    url += (uuid) ? '/' + uuid : '';
    return this.apiService.get(url);
  }
}