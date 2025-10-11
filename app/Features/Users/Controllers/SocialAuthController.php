<?php

namespace App\Features\Users\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    /**
     * Handle social login (Google, Facebook, etc.)
     */
    public function socialLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'provider' => 'required|string|in:google,facebook',
            'provider_id' => 'required|string',
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'avatar' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user already exists with this social provider
        $user = User::where('email', $request->email)->first();

        if ($user) {
            // User exists, update social provider info if needed
            if (!$user->social_provider || !$user->social_id) {
                $user->update([
                    'social_provider' => $request->provider,
                    'social_id' => $request->provider_id,
                    'last_login_at' => now(),
                    'last_login_ip' => $request->ip(),
                ]);
            }
        } else {
            // Create new user
            $nameParts = explode(' ', $request->name, 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';

            $user = User::create([
                'name' => $firstName,
                'last_name' => $lastName,
                'email' => $request->email,
                'password' => Hash::make(Str::random(32)), // Random password for social users
                'role' => User::ROLE_TOURIST,
                'social_provider' => $request->provider,
                'social_id' => $request->provider_id,
                'avatar_path' => $request->avatar,
                'preferred_language' => 'es',
                'is_active' => true,
                'email_verified_at' => now(), // Social accounts are considered verified
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Cuenta desactivada'
            ], 403);
        }

        $token = $user->createToken('social_auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Inicio de sesión social exitoso',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'role' => $user->role,
                'preferred_language' => $user->preferred_language,
                'avatar_path' => $user->avatar_path,
            ],
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Link social account to existing user
     */
    public function linkSocialAccount(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'provider' => 'required|string|in:google,facebook',
            'provider_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Check if this social account is already linked to another user
        $existingUser = User::where('social_provider', $request->provider)
                           ->where('social_id', $request->provider_id)
                           ->where('id', '!=', $user->id)
                           ->first();

        if ($existingUser) {
            return response()->json([
                'message' => 'Esta cuenta social ya está vinculada a otro usuario'
            ], 409);
        }

        $user->update([
            'social_provider' => $request->provider,
            'social_id' => $request->provider_id,
        ]);

        return response()->json([
            'message' => 'Cuenta social vinculada exitosamente',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'social_provider' => $user->social_provider,
            ]
        ]);
    }

    /**
     * Unlink social account
     */
    public function unlinkSocialAccount(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->social_provider) {
            return response()->json([
                'message' => 'No hay cuenta social vinculada'
            ], 400);
        }

        $user->update([
            'social_provider' => null,
            'social_id' => null,
        ]);

        return response()->json([
            'message' => 'Cuenta social desvinculada exitosamente'
        ]);
    }
}