import { HttpErrorResponse } from '@angular/common/http';
import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { FormArray, FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { finalize } from 'rxjs';

import { QuotationApiService } from '../../../core/api/quotation-api.service';
import {
  ApiValidationError,
  CreateQuotationRequest,
  CurrencyCode,
} from '../../../shared/models/quotation.models';
import {
  minimumArrayLength,
  quotationDatesValidator,
  TravelerAgeValidationError,
  travelerAgesValidator,
} from '../../../shared/validators/quotation.validators';

type TravelerForm = FormGroup<{
  full_name: FormControl<string>;
  date_of_birth: FormControl<string>;
}>;

type QuotationForm = FormGroup<{
  currency_id: FormControl<CurrencyCode>;
  start_date: FormControl<string>;
  end_date: FormControl<string>;
  travelers: FormArray<TravelerForm>;
}>;

interface CurrencyOption {
  readonly code: CurrencyCode;
  readonly name: string;
  readonly symbol: string;
}

@Component({
  selector: 'app-quotation-create',
  imports: [ReactiveFormsModule, RouterLink],
  templateUrl: './quotation-create.html',
  styleUrl: './quotation-create.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class QuotationCreate {
  private readonly quotationApi = inject(QuotationApiService);

  private readonly router = inject(Router);

  readonly currencies: readonly CurrencyOption[] = [
    {
      code: 'EUR',
      name: 'Euro',
      symbol: '€',
    },
    {
      code: 'USD',
      name: 'US Dollar',
      symbol: '$',
    },
    {
      code: 'GBP',
      name: 'British Pound',
      symbol: '£',
    },
  ];

  readonly isSubmitting = signal(false);

  readonly serverError = signal<string | null>(null);

  readonly form: QuotationForm = new FormGroup(
    {
      currency_id: new FormControl<CurrencyCode>('EUR', {
        nonNullable: true,
        validators: [Validators.required],
      }),

      start_date: new FormControl<string>('', {
        nonNullable: true,
        validators: [Validators.required],
      }),

      end_date: new FormControl<string>('', {
        nonNullable: true,
        validators: [Validators.required],
      }),

      travelers: new FormArray<TravelerForm>([this.createTravelerForm()], {
        validators: [minimumArrayLength(1)],
      }),
    },
    {
      validators: [quotationDatesValidator, travelerAgesValidator],
    },
  );

  get travelers(): FormArray<TravelerForm> {
    return this.form.controls.travelers;
  }

  addTraveler(): void {
    this.travelers.push(this.createTravelerForm());

    this.form.updateValueAndValidity();
  }

  removeTraveler(index: number): void {
    if (this.travelers.length <= 1) {
      return;
    }

    this.travelers.removeAt(index);
    this.form.updateValueAndValidity();
  }

  submit(): void {
    this.clearBackendErrors();

    if (this.form.invalid || this.travelers.length === 0 || this.isSubmitting()) {
      this.form.markAllAsTouched();

      return;
    }

    this.isSubmitting.set(true);
    this.serverError.set(null);

    const rawValue = this.form.getRawValue();

    const request: CreateQuotationRequest = {
      currency_id: rawValue.currency_id,

      start_date: rawValue.start_date,

      end_date: rawValue.end_date,

      travelers: rawValue.travelers.map((traveler) => ({
        full_name: traveler.full_name.trim(),

        date_of_birth: traveler.date_of_birth,
      })),
    };

    this.quotationApi
      .create(request)
      .pipe(
        finalize(() => {
          this.isSubmitting.set(false);
        }),
      )
      .subscribe({
        next: (quotation) => {
          void this.router.navigate(['/quotes', quotation.quotation_id]);
        },

        error: (error: unknown) => {
          this.handleSubmissionError(error);
        },
      });
  }

  fieldHasError(control: FormControl<string>, errorName: string): boolean {
    return control.hasError(errorName) && (control.touched || control.dirty);
  }

  travelerAgeError(index: number): string | null {
    const traveler = this.travelers.at(index);

    if (!traveler.controls.date_of_birth.touched && !traveler.controls.date_of_birth.dirty) {
      return null;
    }

    const errors = this.form.getError('invalidTravelerAges') as
      readonly TravelerAgeValidationError[] | undefined;

    const travelerError = errors?.find((error) => error.index === index);

    if (travelerError === undefined) {
      return null;
    }

    switch (travelerError.reason) {
      case 'underage':
        return 'Traveler must be at least 18 years old at the start of the trip.';

      case 'overage':
        return 'Traveler cannot be older than 70 at the start of the trip.';

      case 'born-after-trip-start':
        return 'Date of birth must be before the trip start date.';

      default:
        return 'Enter a valid date of birth.';
    }
  }

  dateRangeHasError(): boolean {
    return (
      this.form.hasError('endDateBeforeStartDate') &&
      (this.form.controls.end_date.touched || this.form.controls.end_date.dirty)
    );
  }

  backendError(control: FormControl<string>): string | null {
    const error = control.getError('backend');

    return typeof error === 'string' ? error : null;
  }

  travelerLabel(index: number): string {
    return `Traveler ${index + 1}`;
  }

  private createTravelerForm(): TravelerForm {
    return new FormGroup({
      full_name: new FormControl<string>('', {
        nonNullable: true,
        validators: [Validators.required, Validators.minLength(2), Validators.maxLength(120)],
      }),

      date_of_birth: new FormControl<string>('', {
        nonNullable: true,
        validators: [Validators.required],
      }),
    });
  }

  private handleSubmissionError(error: unknown): void {
    if (!(error instanceof HttpErrorResponse)) {
      this.serverError.set('Something went wrong. Please try again.');

      return;
    }

    if (error.status === 0) {
      this.serverError.set(
        'The server is unavailable. Please check your connection and try again.',
      );

      return;
    }

    const response = error.error as Partial<ApiValidationError>;

    if (error.status === 422 && response.errors !== undefined) {
      this.applyBackendErrors(response.errors);

      this.serverError.set('Please review the highlighted fields.');

      return;
    }

    if (typeof response.message === 'string' && response.message.length > 0) {
      this.serverError.set(response.message);

      return;
    }

    this.serverError.set('We could not create the quotation. Please try again.');
  }

  private applyBackendErrors(errors: Readonly<Record<string, readonly string[]>>): void {
    for (const [field, messages] of Object.entries(errors)) {
      const message = messages[0];

      if (message === undefined) {
        continue;
      }

      if (field === 'currency_id' || field === 'start_date' || field === 'end_date') {
        this.form.controls[field].setErrors({
          ...this.form.controls[field].errors,
          backend: message,
        });

        this.form.controls[field].markAsTouched();

        continue;
      }

      const travelerMatch = /^travelers\.(\d+)\.(full_name|date_of_birth)$/.exec(field);

      if (travelerMatch === null) {
        continue;
      }

      const index = Number(travelerMatch[1]);

      const controlName = travelerMatch[2] as 'full_name' | 'date_of_birth';

      const traveler = this.travelers.at(index);

      if (traveler === undefined) {
        continue;
      }

      traveler.controls[controlName].setErrors({
        ...traveler.controls[controlName].errors,

        backend: message,
      });

      traveler.controls[controlName].markAsTouched();
    }
  }

  private clearBackendErrors(): void {
    this.clearControlBackendError(this.form.controls.currency_id);

    this.clearControlBackendError(this.form.controls.start_date);

    this.clearControlBackendError(this.form.controls.end_date);

    for (const traveler of this.travelers.controls) {
      this.clearControlBackendError(traveler.controls.full_name);

      this.clearControlBackendError(traveler.controls.date_of_birth);
    }
  }

  private clearControlBackendError(control: FormControl<string>): void {
    const errors = control.errors;

    if (errors === null || !('backend' in errors)) {
      return;
    }

    const { backend: _backend, ...remainingErrors } = errors;

    control.setErrors(Object.keys(remainingErrors).length > 0 ? remainingErrors : null);
  }
}
