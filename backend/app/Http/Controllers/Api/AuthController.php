<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
{
    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['Email atau kata sandi tidak sesuai.'],
        ]);
    }

    if (! $user->is_active) {
        throw ValidationException::withMessages([
            'email' => ['Akun Anda tidak aktif.'],
        ]);
    }

    $user->tokens()->delete();   // satu sesi aktif per user

    return response()->json([
        'token' => $user->createToken('spa-'.$request->ip())->plainTextToken,
        'user'  => new UserResource($user->load('roles')),
    ]);
}
}
