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
  readonly travelers_count: number;
}

export interface PaginationLinks {
  readonly first: string | null;
  readonly last: string | null;
  readonly prev: string | null;
  readonly next: string | null;
}

export interface PaginationMeta {
  readonly current_page: number;
  readonly from: number | null;
  readonly last_page: number;
  readonly path: string;
  readonly per_page: number;
  readonly to: number | null;
  readonly total: number;
}

export interface QuotationListResponse {
  readonly data: readonly QuotationSummary[];
  readonly links: PaginationLinks;
  readonly meta: PaginationMeta;
}

export interface ApiValidationError {
  readonly message: string;
  readonly errors?: Readonly<Record<string, readonly string[]>>;
}
