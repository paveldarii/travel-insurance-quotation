import { HttpErrorResponse } from '@angular/common/http';
import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import { finalize } from 'rxjs';

import { QuotationApiService } from '../../../core/api/quotation-api.service';
import {
  CurrencyCode,
  PaginationMeta,
  QuotationSummary,
} from '../../../shared/models/quotation.models';

@Component({
  selector: 'app-quotation-list',
  imports: [RouterLink],
  templateUrl: './quotation-list.html',
  styleUrl: './quotation-list.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class QuotationList {
  private readonly quotationApi = inject(QuotationApiService);

  readonly quotations = signal<readonly QuotationSummary[]>([]);

  readonly pagination = signal<PaginationMeta | null>(null);

  readonly isLoading = signal(true);

  readonly errorMessage = signal<string | null>(null);

  constructor() {
    this.loadQuotations();
  }

  loadQuotations(page = 1): void {
    if (this.isLoading() && this.pagination() !== null) {
      return;
    }

    this.isLoading.set(true);
    this.errorMessage.set(null);

    this.quotationApi
      .list(page)
      .pipe(
        finalize(() => {
          this.isLoading.set(false);
        }),
      )
      .subscribe({
        next: (response) => {
          this.quotations.set(response.data);
          this.pagination.set(response.meta);
        },

        error: (error: unknown) => {
          this.errorMessage.set(this.resolveErrorMessage(error));
        },
      });
  }

  previousPage(): void {
    const pagination = this.pagination();

    if (pagination === null || pagination.current_page <= 1 || this.isLoading()) {
      return;
    }

    this.loadQuotations(pagination.current_page - 1);
  }

  nextPage(): void {
    const pagination = this.pagination();

    if (
      pagination === null ||
      pagination.current_page >= pagination.last_page ||
      this.isLoading()
    ) {
      return;
    }

    this.loadQuotations(pagination.current_page + 1);
  }

  formatCurrency(total: string, currency: CurrencyCode): string {
    const amount = Number(total);

    if (!Number.isFinite(amount)) {
      return `${currency} ${total}`;
    }

    return new Intl.NumberFormat(undefined, {
      style: 'currency',
      currency,
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(amount);
  }

  formatDate(date: string): string {
    const parsedDate = this.parseIsoDate(date);

    if (parsedDate === null) {
      return date;
    }

    return new Intl.DateTimeFormat(undefined, {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      timeZone: 'UTC',
    }).format(parsedDate);
  }

  formatTripDates(startDate: string, endDate: string): string {
    return `${this.formatDate(startDate)} – ${this.formatDate(endDate)}`;
  }

  travelerCountLabel(count: number): string {
    return count === 1 ? '1 traveler' : `${count} travelers`;
  }

  trackQuotation(_index: number, quotation: QuotationSummary): string {
    return quotation.quotation_id;
  }

  private parseIsoDate(value: string): Date | null {
    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value);

    if (match === null) {
      return null;
    }

    const year = Number(match[1]);
    const month = Number(match[2]);
    const day = Number(match[3]);

    const date = new Date(Date.UTC(year, month - 1, day));

    const isValid =
      date.getUTCFullYear() === year &&
      date.getUTCMonth() === month - 1 &&
      date.getUTCDate() === day;

    return isValid ? date : null;
  }

  private resolveErrorMessage(error: unknown): string {
    if (!(error instanceof HttpErrorResponse)) {
      return 'Something went wrong while loading your quotations.';
    }

    if (error.status === 0) {
      return 'The server is unavailable. Please check your connection.';
    }

    if (error.status === 401) {
      return 'Your session has expired. Please sign in again.';
    }

    return 'We could not load your quotations. Please try again.';
  }
}
