/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Associate, Client } from '@app/models';

export class Submission {
  'uuid': string;
  'status': string;
  'status_slug': string;
  'status_desc': string;
  'date_submission': string;
  'case_count': number;
  'providers': string;
  'total_premiums': number;
  'total_ape': number;
  'associate': Associate;
  'media_count': number;
  'client': Client;
}