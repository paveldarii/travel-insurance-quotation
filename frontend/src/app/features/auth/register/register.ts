import { HttpErrorResponse } from '@angular/common/http';
import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import {
  AbstractControl,
  FormBuilder,
  ReactiveFormsModule,
  ValidationErrors,
  ValidatorFn,
  Validators,
} from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { finalize } from 'rxjs';

import { AuthService } from '../../../core/auth/auth.service';
import { ApiErrorResponse, RegisterRequest } from '../../../shared/models/auth.models';

const matchingPasswordsValidator: ValidatorFn = (
  control: AbstractControl,
): ValidationErrors | null => {
  const password = control.get('password')?.value;

  const passwordConfirmation = control.get('password_confirmation')?.value;

  if (typeof password !== 'string' || typeof passwordConfirmation !== 'string') {
    return null;
  }

  return password === passwordConfirmation
    ? null
    : {
        passwordsDoNotMatch: true,
      };
};

@Component({
  selector: 'app-register',
  imports: [ReactiveFormsModule, RouterLink],
  templateUrl: './register.html',
  styleUrl: './register.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class Register {
  private readonly formBuilder = inject(FormBuilder);

  private readonly authService = inject(AuthService);

  private readonly router = inject(Router);

  readonly isSubmitting = signal(false);
  readonly serverError = signal<string | null>(null);

  readonly form = this.formBuilder.nonNullable.group(
    {
      name: ['', [Validators.required, Validators.minLength(2), Validators.maxLength(120)]],
      email: ['', [Validators.required, Validators.email, Validators.maxLength(255)]],
      password: ['', [Validators.required, Validators.minLength(8), Validators.maxLength(255)]],
      password_confirmation: ['', [Validators.required, Validators.maxLength(255)]],
    },
    {
      validators: [matchingPasswordsValidator],
    },
  );

  submit(): void {
    if (this.form.invalid || this.isSubmitting()) {
      this.form.markAllAsTouched();

      return;
    }

    this.isSubmitting.set(true);
    this.serverError.set(null);

    const request: RegisterRequest = this.form.getRawValue();

    this.authService
      .register(request)
      .pipe(
        finalize(() => {
          this.isSubmitting.set(false);
        }),
      )
      .subscribe({
        next: () => {
          void this.router.navigate(['/quotes']);
        },
        error: (error: unknown) => {
          this.applyBackendValidationErrors(error);

          this.serverError.set(this.resolveErrorMessage(error));
        },
      });
  }

  fieldHasError(
    fieldName: 'name' | 'email' | 'password' | 'password_confirmation',
    errorName: string,
  ): boolean {
    const control = this.form.controls[fieldName];

    return control.hasError(errorName) && (control.touched || control.dirty);
  }

  passwordsDoNotMatch(): boolean {
    const confirmation = this.form.controls.password_confirmation;

    return (
      this.form.hasError('passwordsDoNotMatch') && (confirmation.touched || confirmation.dirty)
    );
  }

  private applyBackendValidationErrors(error: unknown): void {
    if (!(error instanceof HttpErrorResponse)) {
      return;
    }

    const response = error.error as Partial<ApiErrorResponse>;

    if (response.errors === undefined || response.errors === null) {
      return;
    }

    for (const fieldName of ['name', 'email', 'password', 'password_confirmation'] as const) {
      const messages = response.errors[fieldName];

      if (messages === undefined || messages.length === 0) {
        continue;
      }

      this.form.controls[fieldName].setErrors({
        ...this.form.controls[fieldName].errors,
        backend: messages[0],
      });
    }
  }

  private resolveErrorMessage(error: unknown): string {
    if (!(error instanceof HttpErrorResponse)) {
      return 'Something went wrong. Please try again.';
    }

    if (error.status === 0) {
      return 'The server is unavailable. Please check your connection.';
    }

    if (error.status === 422) {
      return 'Please review the highlighted fields.';
    }

    const response = error.error as Partial<ApiErrorResponse>;

    if (typeof response.message === 'string' && response.message.length > 0) {
      return response.message;
    }

    return 'We could not create your account. Please try again.';
  }
}
