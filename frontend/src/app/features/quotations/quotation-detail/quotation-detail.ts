import { HttpErrorResponse } from '@angular/common/http';
import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { finalize } from 'rxjs';

import { QuotationApiService } from '../../../core/api/quotation-api.service';
import { CurrencyCode, Quotation } from '../../../shared/models/quotation.models';

@Component({
  selector: 'app-quotation-detail',
  imports: [RouterLink],
  templateUrl: './quotation-detail.html',
  styleUrl: './quotation-detail.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class QuotationDetail {
  private readonly route = inject(ActivatedRoute);

  private readonly router = inject(Router);

  private readonly quotationApi = inject(QuotationApiService);

  readonly quotation = signal<Quotation | null>(null);

  readonly isLoading = signal(true);

  readonly errorMessage = signal<string | null>(null);

  readonly notFound = signal(false);

  constructor() {
    this.loadQuotation();
  }

  loadQuotation(): void {
    const quotationId = this.route.snapshot.paramMap.get('quotationId');

    if (quotationId === null || !/^[A-Za-z0-9]{8}$/.test(quotationId)) {
      this.notFound.set(true);
      this.errorMessage.set('The quotation ID is invalid.');
      this.isLoading.set(false);

      return;
    }

    this.isLoading.set(true);
    this.notFound.set(false);
    this.errorMessage.set(null);

    this.quotationApi
      .get(quotationId)
      .pipe(
        finalize(() => {
          this.isLoading.set(false);
        }),
      )
      .subscribe({
        next: (quotation) => {
          this.quotation.set(quotation);
        },

        error: (error: unknown) => {
          this.handleError(error);
        },
      });
  }

  createAnotherQuotation(): void {
    void this.router.navigate(['/quotes/new']);
  }

  formatCurrency(total: string, currency: CurrencyCode): string {
    const value = Number(total);

    if (!Number.isFinite(value)) {
      return `${currency} ${total}`;
    }

    return new Intl.NumberFormat(undefined, {
      style: 'currency',
      currency,
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(value);
  }

  formatDate(value: string): string {
    const date = this.parseIsoDate(value);

    if (date === null) {
      return value;
    }

    return new Intl.DateTimeFormat(undefined, {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      timeZone: 'UTC',
    }).format(date);
  }

  travelerCountLabel(count: number): string {
    return count === 1 ? '1 traveler' : `${count} travelers`;
  }

  private handleError(error: unknown): void {
    this.quotation.set(null);

    if (!(error instanceof HttpErrorResponse)) {
      this.errorMessage.set('Something went wrong while loading the quotation.');

      return;
    }

    if (error.status === 404) {
      this.notFound.set(true);

      this.errorMessage.set('This quotation could not be found.');

      return;
    }

    if (error.status === 0) {
      this.errorMessage.set('The server is unavailable. Please check your connection.');

      return;
    }

    this.errorMessage.set('We could not load this quotation. Please try again.');
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

    const valid =
      date.getUTCFullYear() === year &&
      date.getUTCMonth() === month - 1 &&
      date.getUTCDate() === day;

    return valid ? date : null;
  }
}
