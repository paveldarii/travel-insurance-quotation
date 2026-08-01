<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

final class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_me_endpoint(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $this
            ->withToken($token)
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath(
                'data.user.email',
                $user->email,
            );
    }

    public function test_missing_token_returns_unauthorized(): void
    {
        $this
            ->getJson('/api/auth/me')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_invalid_token_returns_unauthorized(): void
    {
        $this
            ->withToken('invalid-token')
            ->getJson('/api/auth/me')
            ->assertUnauthorized();
    }
}
