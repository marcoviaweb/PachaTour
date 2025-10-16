<?php

namespace App\Features\Users\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Features\Users\Requests\LoginRequest;
use App\Features\Users\Requests\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'
            ],
            'terms_accepted' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'tourist',
            'preferred_language' => 'es',
        ]);

        Auth::login($user, $request->boolean('remember'));

        if ($request->expectsJson()) {
            $token = $user->createToken('auth_token')->plainTextToken;
            
            return response()->json([
                'message' => 'Usuario registrado exitosamente',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 201);
        }

        return redirect('/');
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            $error = ['email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.'];
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Credenciales incorrectas',
                    'errors' => $error
                ], 422);
            }
            
            return redirect()->back()->withErrors($error)->withInput();
        }

        $request->session()->regenerate();

        // Obtener el usuario autenticado
        $user = Auth::user();

        if ($request->expectsJson()) {
            $token = $user->createToken('auth_token')->plainTextToken;
            
            return response()->json([
                'message' => 'Inicio de sesión exitoso',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);
        }

        // Redirección basada en el rol del usuario - funciona para Inertia y requests normales
        if ($user->role === 'admin') {
            return redirect('/admin/dashboard');
        }

        return redirect('/');
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        if ($request->expectsJson() && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
            
            return response()->json([
                'message' => 'Sesión cerrada exitosamente'
            ]);
        }

        // Obtener el rol del usuario antes de cerrar sesión
        $userRole = $request->user() ? $request->user()->role : null;
        $wasAdmin = $userRole === 'admin';

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirección basada en el rol del usuario que hizo logout
        if ($wasAdmin) {
            return redirect('/login')->with('info', 'Sesión de administrador cerrada. Puede iniciar sesión nuevamente.');
        }

        return redirect('/');
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
                'nationality' => $user->nationality,
                'country' => $user->country,
                'city' => $user->city,
                'preferred_language' => $user->preferred_language,
                'is_active' => $user->is_active,
                'created_at' => $user->created_at,
            ]
        ]);
    }

    /**
     * Send password reset link
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Se ha enviado un enlace de recuperación a tu correo electrónico.'
                ]);
            }
            
            return redirect()->back()->with('status', 'Se ha enviado un enlace de recuperación a tu correo electrónico.');
        }

        $error = ['email' => 'No pudimos encontrar un usuario con esa dirección de correo electrónico.'];
        
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Error al enviar enlace de recuperación',
                'errors' => $error
            ], 422);
        }
        
        return redirect()->back()->withErrors($error);
    }

    /**
     * Show password reset form
     */
    public function showResetForm(Request $request, string $token)
    {
        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'
            ],
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Tu contraseña ha sido restablecida exitosamente.'
                ]);
            }
            
            return redirect('/')->with('status', 'Tu contraseña ha sido restablecida exitosamente.');
        }

        $error = ['email' => 'El token de restablecimiento de contraseña es inválido o ha expirado.'];
        
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Error al restablecer contraseña',
                'errors' => $error
            ], 422);
        }
        
        return redirect()->back()->withErrors($error);
    }

    /**
     * Get user profile
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        if ($request->expectsJson()) {
            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'phone' => $user->phone,
                    'nationality' => $user->nationality,
                    'preferred_language' => $user->preferred_language,
                    'created_at' => $user->created_at,
                ]
            ]);
        }

        return Inertia::render('Profile/Show', [
            'user' => $user
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'nationality' => 'nullable|string|max:100',
            'preferred_language' => 'nullable|string|in:es,en',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user->update($request->only([
            'name', 'email', 'phone', 'nationality', 'preferred_language'
        ]));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Perfil actualizado exitosamente',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Perfil actualizado exitosamente');
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'
            ],
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            $error = ['current_password' => 'La contraseña actual es incorrecta.'];
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Contraseña actual incorrecta',
                    'errors' => $error
                ], 422);
            }
            
            return redirect()->back()->withErrors($error);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Contraseña actualizada exitosamente'
            ]);
        }

        return redirect()->back()->with('success', 'Contraseña actualizada exitosamente');
    }

    /**
     * Refresh token (API only)
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Delete current token
        $request->user()->currentAccessToken()->delete();
        
        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Token renovado exitosamente',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}