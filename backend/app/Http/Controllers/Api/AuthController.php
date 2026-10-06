<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rules\Password;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    /**
     * Log in and issue an API token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = $request->autentikasi();
        $token = $user->createToken($request->validated('device_name') ?? 'api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Revoke the token used for the current request.
     */
    public function logout(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    /**
     * Get the authenticated user.
     */
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    /**
     * Change the authenticated user's password.
     */
    public function updatePassword(Request $request): Response
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password:sanctum'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $request->user();
        $user->update(['password' => $validated['password']]);

        // Perangkat lain harus login ulang dengan password baru; token yang sedang dipakai tetap berlaku.
        $tokenSekarang = $user->currentAccessToken();
        $user->cabutSemuaAkses($tokenSekarang instanceof PersonalAccessToken ? $tokenSekarang->getKey() : null);

        return response()->noContent();
    }
}
