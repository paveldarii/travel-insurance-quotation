import { HttpErrorResponse, HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { catchError, throwError } from 'rxjs';

import { AuthService } from '../auth/auth.service';

export const authInterceptor: HttpInterceptorFn = (request, next) => {
  const authService = inject(AuthService);
  const router = inject(Router);
  const token = authService.getToken();

  const authenticatedRequest =
    token === null
      ? request
      : request.clone({
          setHeaders: {
            Authorization: `Bearer ${token}`,
          },
        });

  return next(authenticatedRequest).pipe(
    catchError((error: unknown) => {
      if (
        error instanceof HttpErrorResponse &&
        error.status === 401 &&
        !isAuthenticationRequest(request.url)
      ) {
        authService.clearSession();

        void router.navigate(['/login'], {
          queryParams: {
            sessionExpired: true,
          },
        });
      }

      return throwError(() => error);
    }),
  );
};

function isAuthenticationRequest(url: string): boolean {
  return url.includes('/auth/login') || url.includes('/auth/register');
}
