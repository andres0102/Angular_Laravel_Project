/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Component, OnInit, AfterContentInit, OnDestroy } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { FormBuilder, Validators } from '@angular/forms';
import { Subscription } from 'rxjs';

import { Client } from '@app/models';
import { AuthService, SessionStorageService } from '@app/@core/services';
import { PageService } from '@auth/services/page';
import { AdminClientRelationshipsService } from '@app/@shared/services/admin/client-relationships/client-relationships.service';
import { MessageService } from '@app/@shared/components/message/message.service';

@Component({
  selector: 'admin-client-details',
  templateUrl: './client-details.component.html',
  styleUrls: ['./client-details.component.scss']
})
export class AdminClientDetailsComponent implements AfterContentInit, OnInit, OnDestroy {
  private page_permission: string = 'clients_mgmt_view';
  private parentRefSub: Subscription;
  private clientSub: Subscription;
  private updateClientSub: Subscription;

  resized: boolean = false;

  client: Client;
  clientUpdate: boolean = false;

  messages = {
    emptyMessage: 'No records to display.',
    totalMessage: 'Records'
  };

  authForm = this.formBuilder.group({
    'client_type_slug': ['', Validators.required],
    'client_uuid': [''],
  });

  get client_uuid() { return this.authForm.controls.client_uuid; }
  get client_type_slug() { return this.authForm.controls.client_type_slug; }

  constructor (
    private formBuilder: FormBuilder,
    private authService: AuthService,
    private pageService: PageService,
    private sessionStorageService: SessionStorageService,
    private route: ActivatedRoute,
    private router: Router,
    private _notification: MessageService,
    private assClientService: AdminClientRelationshipsService) {
    this.client = this.route.snapshot.data.client;
    this.parentRefSub = this.pageService.parentRefresh.subscribe(() => this.getClient());
  }

  getClient() {
    let uuid = this.client.uuid;
    this.clientUpdate = true;
    this.clientSub = this.assClientService.getClients(uuid).subscribe((res) => {
      this.updateModel(res['data']);
    });
  }

  ngOnInit() {
    this.updateModel();
  }

  ngOnDestroy() {
    if (this.parentRefSub) this.parentRefSub.unsubscribe();
    if (this.clientSub) this.clientSub.unsubscribe();
    if (this.updateClientSub) this.updateClientSub.unsubscribe();
  }

  ngAfterContentInit() {
    if (!this.resized) setTimeout(() => { window.dispatchEvent(new Event('resize')); });
  }

  updateModel(data = null) {
    if (data) this.client = data;
    if (this.client && this.client.client_type_slug) this.client_type_slug.setValue(this.client.client_type_slug);
    this.clientUpdate = false;
  }

  updateValue(key, value) {
    this.clientUpdate = true;
    var data:any = {}
    data[key] = value;
    this.updateClientSub = this.assClientService.updateClient(this.client.uuid, data).subscribe((res) => {
      this.updateModel(res.data);
      this.router.navigate(['/authenticated/admin/relationships/clients/' + this.client.uuid]);
      this._notification.create('success', 'Success! Client Profile updated successfully.', { Position: 'top', Style: 'bar', Duration: 2000 });
    });
  }

  routeBack() {
    if (this.sessionStorageService.getItem('previous_url')) this.sessionStorageService.removeItem('previous_url');
    if (this.sessionStorageService.getItem('previous_title')) this.sessionStorageService.removeItem('previous_title');
  }
}
