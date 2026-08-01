<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'pavel@example.com',
            'password' => 'Password123!',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'PAVEL@EXAMPLE.COM',
            'password' => 'Password123!',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.user.email',
                'pavel@example.com',
            )
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonStructure([
                'data' => [
                    'user',
                    'access_token',
                    'token_type',
                    'expires_in',
                ],
            ]);
    }

    public function test_invalid_password_returns_unauthorized(): void
    {
        User::factory()->create([
            'email' => 'pavel@example.com',
            'password' => 'Password123!',
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'pavel@example.com',
            'password' => 'WrongPassword123!',
        ])
            ->assertUnauthorized()
            ->assertExactJson([
                'message' => 'Invalid credentials.',
            ]);
    }

    public function test_unknown_email_returns_same_error(): void
    {
        $this->postJson('/api/auth/login', [
            'email' => 'unknown@example.com',
            'password' => 'Password123!',
        ])
            ->assertUnauthorized()
            ->assertExactJson([
                'message' => 'Invalid credentials.',
            ]);
    }

    public function test_login_requires_email_and_password(): void
    {
        $this->postJson('/api/auth/login', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email',
                'password',
            ]);
    }
}
