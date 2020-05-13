/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */

import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Observable, of } from 'rxjs';
import { timeout, catchError } from 'rxjs/operators';
import { environment } from '@env/environment';

const httpOptions = {
  headers: new HttpHeaders({ 'Content-Type': 'application/json', 'Accept': 'application/json' })
};

const httpOptionsUpload = {
  headers: new HttpHeaders({ 'Accept': 'application/json' })
};

@Injectable()
export class ApiService {
  constructor(private http: HttpClient) { }
  timeout_length: number = 30000;

  download(path: string): Observable<any> {
    return this.http.get(`${environment.api_url}${path}`, {responseType: "blob"}).pipe(timeout(this.timeout_length), catchError(err => this.handleError(err)));
  }

  get(path: string, params: HttpParams = new HttpParams()): Observable<any> {
    return this.http.get(`${environment.api_url}${path}`, {params}).pipe(timeout(this.timeout_length), catchError(err => this.handleError(err)));
  }

  put(path: string, body: Object = {}): Observable<any> {
    return this.http.put(`${environment.api_url}${path}`, body, httpOptions).pipe(timeout(this.timeout_length), catchError(err => this.handleError(err)));
  }

  post(path: string, body: Object = {}): Observable<any> {
    return this.http.post(`${environment.api_url}${path}`, body, httpOptions).pipe(timeout(this.timeout_length), catchError(err => this.handleError(err)));
  }

  upload(path: string, body: Object = {}): Observable<any> {
    return this.http.post(`${environment.api_url}${path}`, body, httpOptionsUpload).pipe(timeout(this.timeout_length), catchError(err => this.handleError(err)));
  }

  patch(path: string, body: Object = {}): Observable<any> {
    return this.http.patch(`${environment.api_url}${path}`, body, httpOptions).pipe(timeout(this.timeout_length), catchError(err => this.handleError(err)));
  }

  delete(path): Observable<any> {
    return this.http.delete(`${environment.api_url}${path}`, httpOptions).pipe(timeout(this.timeout_length), catchError(err => this.handleError(err)));
  }

  /**
   * Handle Http operation that failed.
   * Let the app continue.
   * @param operation - name of the operation that failed
   * @param result - optional value to return as the observable result
   */
  private handleError(error) {
    let status = null;

    if (error['name'] == 'TimeoutError') status = 'request-timeout';
    else status = 'unexpected-error';

    // TODO: better job of transforming error for user consumption
    // this.log(`${operation} failed: ${error.message}`);

    // TODO: send the error to remote logging infrastructure
    console.log('Unexpected error has occurred.', error);

    // Let the app keep running by returning an empty result.
    return of({
      'error': true,
      'status': status,
      'name': error.name,
      'message': error.message
    });
  }
}