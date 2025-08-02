<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'type' => 'required|in:client,freelancer',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'type' => $request->type,
        ]);

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Usuário registrado com sucesso',
            'user' => $user,
            'token' => $user->createToken('token')->plainTextToken
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais estão incorretas.'],
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            // Verifica se já se passou 1 minuto desde o último envio
            $lastSent = $user->updated_at ?? $user->created_at;
            $now = Carbon::now();

            if ($now->diffInSeconds($lastSent) > 60) {
                $user->sendEmailVerificationNotification();
            }

            return response()->json([
                'message' => 'Por favor, verifique seu e-mail antes de continuar. Um novo link foi enviado, se necessário.',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }


    public function me()
    {
        return Auth::user();
    }

    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();
        return response()->noContent();
    }
}

