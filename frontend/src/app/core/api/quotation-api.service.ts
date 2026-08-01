import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { Observable } from 'rxjs';

import { environment } from '../../../environments/environment';
import {
  CreateQuotationRequest,
  Quotation,
  QuotationListResponse,
} from '../../shared/models/quotation.models';

@Injectable({
  providedIn: 'root',
})
export class QuotationApiService {
  private readonly http = inject(HttpClient);
  private readonly baseUrl = environment.apiUrl;

  create(request: CreateQuotationRequest): Observable<Quotation> {
    return this.http.post<Quotation>(`${this.baseUrl}/quotation`, request);
  }

  list(): Observable<QuotationListResponse> {
    return this.http.get<QuotationListResponse>(`${this.baseUrl}/quotations`);
  }

  get(quotationId: string): Observable<Quotation> {
    return this.http.get<Quotation>(
      `${this.baseUrl}/quotations/${encodeURIComponent(quotationId)}`,
    );
  }
}
