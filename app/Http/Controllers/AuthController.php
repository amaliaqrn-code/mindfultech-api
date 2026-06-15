<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6'
        ]);

        // Simpan user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Generate token (Sanctum)
        $token = $user->createToken('auth_token')->plainTextToken;

        // Response
        return response()->json([
            'message' => 'Register berhasil',
            'user' => $user,
            'token' => $token
        ], 201);
    }


public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation error',
            'errors' => $validator->errors()
        ], 422);
    }

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'message' => 'Email atau password salah'
        ], 401);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'Login berhasil',
        'user' => $user,
        'token' => $token
    ]);
}

    public function updateProfile(Request $request)
{
    // 1. Ambil data user yang sedang login via token Sanctum
    $user = $request->user();

    // 2. Validasi input dari Flutter
    $request->validate([
        'name' => 'sometimes|string|max:255',
        'username' => 'sometimes|nullable|string|unique:users,username,' . $user->id,
        'gender' => 'sometimes|nullable|string',
        'phone' => 'sometimes|nullable|string|max:15',
        // Jika upload gambar, aktifkan baris di bawah ini:
        // 'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg|max:2048'
    ]);

    // 3. Tampung data yang akan diupdate
    $data = $request->only(['name', 'username', 'gender', 'phone']);

    // 4. Logika opsional jika Flutter mengirimkan file foto profil
    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $path = $file->store('profile_images', 'public');
        $data['image_path'] = $path;
    }

    // 5. Update data ke database
    $user->update($data);

    // 6. Kembalikan respons sukses ke Flutter
    return response()->json([
        'message' => 'Profil berhasil diperbarui',
        'user' => $user
    ], 200);
}

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }
}
