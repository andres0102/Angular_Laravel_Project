/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Associate } from '@app/models/associate';
import { Business, Individual, DisplayPhoto } from '@app/models/common';

export class Client {
  uuid: string;
  display_name: string;
  aliases: string;
  lead_stage_slug: string;
  client_type_slug: string;
  source_slug: string;
  submissions_count: number;
  policies_count: number;
  display_photo: DisplayPhoto;
  associate: Associate;
  business: Business;
  personal: Individual;
  description: string;
  interest: string;
  important: string;
}