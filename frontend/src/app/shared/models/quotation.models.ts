export type CurrencyCode = 'EUR' | 'USD' | 'GBP';

export interface TravelerInput {
  readonly full_name: string;
  readonly date_of_birth: string;
}

export interface CreateQuotationRequest {
  readonly currency_id: CurrencyCode;
  readonly start_date: string;
  readonly end_date: string;
  readonly travelers: readonly TravelerInput[];
}

export interface QuotationTraveler {
  readonly full_name: string;
  readonly date_of_birth: string;
  readonly age_at_trip_start: number;
  readonly subtotal: string;
}

export interface QuotationExchangeRate {
  readonly base_currency_id: CurrencyCode;
  readonly quote_currency_id: CurrencyCode;
  readonly rate_date: string;
  readonly rate: string;
}

export interface Quotation {
  readonly quotation_id: string;
  readonly total: string;
  readonly currency_id: CurrencyCode;
  readonly base_total: string;
  readonly base_currency_id: CurrencyCode;
  readonly quoted_on: string;
  readonly start_date: string;
  readonly end_date: string;
  readonly trip_days: number;
  readonly exchange_rate: QuotationExchangeRate;
  readonly travelers: readonly QuotationTraveler[];
}

export interface QuotationSummary {
  readonly quotation_id: string;
  readonly total: string;
  readonly currency_id: CurrencyCode;
  readonly quoted_on: string;
  readonly start_date: string;
  readonly end_date: string;
  readonly trip_days: number;
}

export interface QuotationListResponse {
  readonly data: readonly QuotationSummary[];
}

export interface ApiValidationError {
  readonly message: string;
  readonly errors?: Readonly<Record<string, readonly string[]>>;
}
