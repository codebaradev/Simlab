<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Login user dan generate API token (Stateless)
     */
    public function login(array $credentials)
    {
        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'username' => ['Username atau password salah']
            ]);
        }

        $user = Auth::user();

        // Opsional: hapus token lama (1 device login)
        // $user->tokens()->delete();

        $token = $user->createToken('api_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout user (revoke token)
     */
    public function logout($user): void
    {
        $user->currentAccessToken()->delete();
    }
}
