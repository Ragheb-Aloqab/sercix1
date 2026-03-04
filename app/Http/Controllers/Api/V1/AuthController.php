<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/v1/auth/login
     * Returns API token for Company.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $company = \App\Models\Company::where('email', $request->email)->first();

        if (!$company || !Hash::check($request->password, $company->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if (($company->status ?? '') !== 'active') {
            throw ValidationException::withMessages([
                'email' => [__('auth.account_inactive') ?? 'Account is inactive.'],
            ]);
        }

        $token = $company->createToken('api-v1')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'company' => [
                'id' => $company->id,
                'company_name' => $company->company_name,
                'email' => $company->email,
            ],
        ]);
    }

    /**
     * POST /api/v1/auth/logout
     * Revokes current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
