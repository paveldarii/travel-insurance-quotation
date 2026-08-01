export interface User {
  readonly id: number;
  readonly name: string;
  readonly email: string;
}

export interface RegisterRequest {
  readonly name: string;
  readonly email: string;
  readonly password: string;
  readonly password_confirmation: string;
}

export interface LoginRequest {
  readonly email: string;
  readonly password: string;
}

export interface AuthData {
  readonly user: User;
  readonly access_token: string;
  readonly token_type: 'Bearer';
  readonly expires_in: number;
}

export interface AuthResponse {
  readonly data: AuthData;
}
