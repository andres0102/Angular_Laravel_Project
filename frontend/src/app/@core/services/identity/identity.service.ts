/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Injectable } from '@angular/core';
import { DeviceDetectorService } from 'ngx-device-detector';
import * as Fingerprint2 from 'fingerprintjs2';

@Injectable()
export class IdentityService {
  metadata = {};

  constructor(private deviceService: DeviceDetectorService) {
    // DeviceDetectorService
    this.metadata['device_info'] = this.deviceService.getDeviceInfo();
    if (this.deviceService.isMobile()) this.metadata['device'] = 'mobile';
    else if (this.deviceService.isTablet()) this.metadata['device'] = 'tablet';
    else if (this.deviceService.isDesktop()) this.metadata['device'] = 'desktop';

    // Fingerprintjs2
    Fingerprint2.get({excludes: {canvas: true}}, (components) => {
      this.metadata['fingerprint'] = Fingerprint2.x64hash128(components.map(function (pair) { return pair.value }).join(), 31);
      components.map((data) => {
        this.metadata[data.key] = data.value;
      });
    });
  }
}