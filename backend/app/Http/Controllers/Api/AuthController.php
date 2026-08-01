<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;
use RuntimeException;

final class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $request->merge([
            'name' => is_string($request->input('name'))
                ? trim($request->input('name'))
                : $request->input('name'),

            'email' => is_string($request->input('email'))
                ? strtolower(trim($request->input('email')))
                : $request->input('email'),
        ]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        [$user, $token] = DB::transaction(
            static function () use ($validated): array {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                ]);

                $token = JWTAuth::fromUser($user);

                return [$user, $token];
            }
        );

        return $this->tokenResponse(
            user: $user,
            token: $token,
            status: 201,
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $guard = auth('api');

        if (! $guard instanceof JWTGuard) {
            throw new RuntimeException(
                'The API authentication guard is not configured as a JWT guard.'
            );
        }

        $token = $guard->attempt($credentials);

        if (! is_string($token)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $user = $guard->user();

        if (! $user instanceof User) {
            throw new RuntimeException(
                'The authenticated user could not be resolved.'
            );
        }

        return $this->tokenResponse(
            user: $user,
            token: $token,
        );
    }

    private function tokenResponse(
        User $user,
        string $token,
        int $status = 200,
    ): JsonResponse {
        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => $this->jwtExpirationSeconds(),
            ],
        ], $status);
    }

    private function jwtExpirationSeconds(): int
    {
        return (int) config('jwt.ttl', 60) * 60;
    }
}
