import { TestBed } from '@angular/core/testing';

import { QuotationApi } from './quotation-api';

describe('QuotationApi', () => {
  let service: QuotationApi;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(QuotationApi);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
