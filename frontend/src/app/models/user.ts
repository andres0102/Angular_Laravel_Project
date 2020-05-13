/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Individual, DisplayPhoto } from './common';

export class User {
  uuid: string;
  personal: Individual;
  spouse: {
    associate_uuid: string;
    name: string;
    job_title: string;
    company_name: string;
  };
  user_role: string;
  permissions: Array<string>;
  email: string;
  activated: boolean;
  setup: boolean;
  private: boolean;
  roles: {
    is_admin: boolean;
    is_manager: boolean;
    is_associate: boolean;
    is_staff: boolean;
    is_assistant: boolean;
    is_candidate: boolean;
    is_client: boolean;
    is_guest: boolean;
  };
  display_photo: DisplayPhoto;
  lfa: {
    uuid: string;
    onboarding_status: string;
    onboarding_status_slug: string;
    designation: string;
    designation_slug: string;
    printer_id: string;
    did_no: string;
    devices: any;
    dates: {
      date_lfa_application: string;
      date_ceo_interview: string;
      date_contract_start: string;
      date_onboarded: string;
      date_offboarded: string;
      date_resigned: string;
      date_last_day: string;
    };
  };
}