/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { ContactInfo } from '@app/models/common/contact_info';
import { AddressInfo } from '@app/models/common/address_info';

export class Individual {
  salutation_slug: string;
  full_name: string;
  alias: string;
  chinese_name: string;
  nric_no: string;
  fin_no: string;
  passport_no: string;
  gender_slug: string;
  date_birth: string;
  race_slug: string;
  country_birth_slug: string;
  nationality_slug: string;
  residency_status_slug: string;
  marital_status_slug: string;
  employment_status_slug: string;
  job_title: string;
  income_range: string;
  company_name: string;
  business_nature: string;
  education_level_slug: string;
  education_institution: string;
  field_of_study: string;
  smoker: boolean;
  selected: boolean;
  pdpa: boolean;
  contact_information: ContactInfo;
  address_information: AddressInfo;
}