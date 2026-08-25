<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;

class AuthController extends Controller
{
    /**
     * Authenticates the user and returns a Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $user  = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => new UserResource($user),
        ]);
    }

    /**
     * Revokes the current token (logout).
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->bearerToken();

        if ($token !== null && $token !== '') {
            $accessToken = Sanctum::$personalAccessTokenModel::findToken($token);

            if ($accessToken !== null) {
                $accessToken->delete();
            }
        }

        return response()->json(['message' => 'Logged out successfully.']);
    }
}
