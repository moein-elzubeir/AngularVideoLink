import { Injectable } from '@angular/core';
import { HttpClient, HttpErrorResponse, HttpParams } from '@angular/common/http';

import { Observable, throwError } from 'rxjs';
import { map, catchError } from 'rxjs/operators';

@Injectable({
  providedIn: 'root'
})
export class CalendarService {
  baseUrl = 'http://localhost:8080/api';

  constructor(private http: HttpClient) {}

  getLink(details: object): Observable<Object> {
    const options = {headers: {'Content-Type': 'application/json'}};
    return this.http.post(`${this.baseUrl}/calendar`, JSON.stringify(details), options).pipe(catchError(this.handleError));
  }

  private handleError(error: HttpErrorResponse) {
    console.log(error);

    // return an observable with a user friendly message
    return throwError('Error! something went wrong.');
  }
}
