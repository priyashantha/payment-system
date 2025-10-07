<?php

namespace App\Http\Controllers\Auth;

use App\Models\Customer;
use App\Services\AuthService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CustomerAuthController extends Controller
{
    public function __construct(private AuthService $auth) {}

    public function login(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required']);
        $result = $this->auth->login(Customer::class, $request->email, $request->password, 'customer');

        if (!$result) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return response()->json($result);
    }
}
