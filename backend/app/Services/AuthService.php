<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function login($model, $email, $password, $tokenName)
    {
        $user = $model::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        $token = $user->createToken($tokenName)->plainTextToken;

        return ['token' => $token, 'user' => $user];
    }
}
