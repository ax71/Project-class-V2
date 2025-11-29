<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // 1. REGISTER
    public function register(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            // Role opsional, jika tidak diisi default 'user' di database
            // 'role' => 'in:admin,user',
        ]);

        // Buat User baru
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            // 'role' => $validated['role'] ?? 'user',
        ]);

        // Buat Token Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return Response JSON
        return response()->json([
            'message' => 'Register success',
            'data' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ], 201);
    }

    // 2. LOGIN
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // Cek apakah user ada DAN password benar
        if (! $user || ! Hash::check($request->password, $user->password)) {
            // Jika salah, lempar error validasi
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan salah.'],
            ]);
        }

        // Hapus token lama jika ingin single device login (opsional)
        $user->tokens()->delete();

        // Buat Token Baru
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login success',
            'data' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    // 3. LOGOUT
    public function logout(Request $request)
    {
        // Hapus token yang sedang digunakan saat ini (Current Access Token)
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout success'
        ]);
    }
}
