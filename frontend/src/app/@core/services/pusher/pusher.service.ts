/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Injectable } from '@angular/core';
import { Subject, Observable, Subscription } from 'rxjs';

import Pusher from 'pusher-js';

@Injectable()
export class PusherService {
  private pusherClient: Pusher;

  private subject_root: Subject<string> = new Subject<string>();
  private subject_monitorUserLogins: Subject<{ email: string }> = new Subject<{ email: string }>();

  public channel_authentication;

  constructor() {
    this.pusherClient = new Pusher('319b9fea774b9d43b865', { cluster: 'ap1', forceTLS: true });
    this.channel_authentication = this.pusherClient.subscribe('authentication');
    this.channel_authentication.bind('UserAuthenticated', (data: { email: string }) => this.subject_monitorUserLogins.next(data));
  }

  monitorRoot(): Observable<string> {
    return this.subject_root.asObservable();
  }
  parseRoot(command) { this.subject_root.next(command) }

  monitorUserLogins(): Observable<{ email: string }> {
    return this.subject_monitorUserLogins.asObservable();
  }
}