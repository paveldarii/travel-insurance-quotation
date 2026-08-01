import { HttpErrorResponse } from '@angular/common/http';
import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { finalize } from 'rxjs';

import { AuthService } from '../../../core/auth/auth.service';
import { ApiErrorResponse } from '../../../shared/models/auth.models';

@Component({
  selector: 'app-login',
  imports: [ReactiveFormsModule, RouterLink],
  templateUrl: './login.html',
  styleUrl: './login.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class Login {
  private readonly formBuilder = inject(FormBuilder);

  private readonly authService = inject(AuthService);

  private readonly router = inject(Router);

  private readonly route = inject(ActivatedRoute);

  readonly isSubmitting = signal(false);
  readonly serverError = signal<string | null>(null);

  readonly sessionExpired = this.route.snapshot.queryParamMap.get('sessionExpired') === 'true';

  readonly form = this.formBuilder.nonNullable.group({
    email: ['', [Validators.required, Validators.email, Validators.maxLength(255)]],
    password: ['', [Validators.required, Validators.maxLength(255)]],
  });

  submit(): void {
    if (this.form.invalid || this.isSubmitting()) {
      this.form.markAllAsTouched();

      return;
    }

    this.isSubmitting.set(true);
    this.serverError.set(null);

    this.authService
      .login(this.form.getRawValue())
      .pipe(
        finalize(() => {
          this.isSubmitting.set(false);
        }),
      )
      .subscribe({
        next: () => {
          void this.router.navigateByUrl(this.getReturnUrl());
        },
        error: (error: unknown) => {
          this.serverError.set(this.resolveErrorMessage(error));
        },
      });
  }

  fieldHasError(fieldName: 'email' | 'password', errorName: string): boolean {
    const control = this.form.controls[fieldName];

    return control.hasError(errorName) && (control.touched || control.dirty);
  }

  private getReturnUrl(): string {
    const returnUrl = this.route.snapshot.queryParamMap.get('returnUrl');

    if (returnUrl === null || !returnUrl.startsWith('/') || returnUrl.startsWith('//')) {
      return '/quotes';
    }

    return returnUrl;
  }

  private resolveErrorMessage(error: unknown): string {
    if (!(error instanceof HttpErrorResponse)) {
      return 'Something went wrong. Please try again.';
    }

    if (error.status === 0) {
      return 'The server is unavailable. Please check your connection.';
    }

    if (error.status === 401) {
      return 'The email or password is incorrect.';
    }

    const response = error.error as Partial<ApiErrorResponse>;

    if (typeof response.message === 'string' && response.message.length > 0) {
      return response.message;
    }

    return 'We could not sign you in. Please try again.';
  }
}
