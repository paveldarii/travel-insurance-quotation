import { Injectable } from '@angular/core';

import { User } from '../../shared/models/auth.models';

interface StoredAuthSession {
  readonly token: string;
  readonly user: User;
}

@Injectable({
  providedIn: 'root',
})
export class AuthStorageService {
  private readonly storageKey = 'travel-insurance-auth-session';

  getToken(): string | null {
    return this.readSession()?.token ?? null;
  }

  getUser(): User | null {
    return this.readSession()?.user ?? null;
  }

  saveSession(token: string, user: User): void {
    const session: StoredAuthSession = {
      token,
      user,
    };

    sessionStorage.setItem(this.storageKey, JSON.stringify(session));
  }

  clearSession(): void {
    sessionStorage.removeItem(this.storageKey);
  }

  private readSession(): StoredAuthSession | null {
    const storedValue = sessionStorage.getItem(this.storageKey);

    if (storedValue === null) {
      return null;
    }

    try {
      const parsedValue: unknown = JSON.parse(storedValue);

      if (!this.isStoredAuthSession(parsedValue)) {
        this.clearSession();

        return null;
      }

      return parsedValue;
    } catch {
      this.clearSession();

      return null;
    }
  }

  private isStoredAuthSession(value: unknown): value is StoredAuthSession {
    if (typeof value !== 'object' || value === null) {
      return false;
    }

    const session = value as Partial<StoredAuthSession>;

    return (
      typeof session.token === 'string' && session.token.length > 0 && this.isUser(session.user)
    );
  }

  private isUser(value: unknown): value is User {
    if (typeof value !== 'object' || value === null) {
      return false;
    }

    const user = value as Partial<User>;

    return (
      typeof user.id === 'number' &&
      typeof user.name === 'string' &&
      user.name.length > 0 &&
      typeof user.email === 'string' &&
      user.email.length > 0
    );
  }
}
