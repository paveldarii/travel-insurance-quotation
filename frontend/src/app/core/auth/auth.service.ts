import { computed, inject, Injectable, signal } from '@angular/core';
import { Router } from '@angular/router';
import { Observable, tap } from 'rxjs';

import { AuthResponse, LoginRequest, RegisterRequest, User } from '../../shared/models/auth.models';
import { AuthApiService } from '../api/auth-api.service';
import { AuthStorageService } from './auth-storage.service';

@Injectable({
  providedIn: 'root',
})
export class AuthService {
  private readonly authApi = inject(AuthApiService);

  private readonly authStorage = inject(AuthStorageService);

  private readonly router = inject(Router);

  private readonly currentUserState = signal<User | null>(this.authStorage.getUser());

  readonly currentUser = this.currentUserState.asReadonly();

  readonly isAuthenticated = computed(
    () => this.currentUserState() !== null && this.authStorage.getToken() !== null,
  );

  login(request: LoginRequest): Observable<AuthResponse> {
    return this.authApi.login(request).pipe(
      tap((response) => {
        this.startSession(response);
      }),
    );
  }

  register(request: RegisterRequest): Observable<AuthResponse> {
    return this.authApi.register(request).pipe(
      tap((response) => {
        this.startSession(response);
      }),
    );
  }

  getToken(): string | null {
    return this.authStorage.getToken();
  }

  clearSession(): void {
    this.authStorage.clearSession();
    this.currentUserState.set(null);
  }

  async logout(): Promise<void> {
    this.clearSession();

    await this.router.navigate(['/login']);
  }

  private startSession(response: AuthResponse): void {
    const { access_token: accessToken, user } = response.data;

    this.authStorage.saveSession(accessToken, user);

    this.currentUserState.set(user);
  }
}
