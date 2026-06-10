<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    // Menangani GET /api/profile dari Flutter
    public function show(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ], 200);
    }

    // Menangani PUT /api/profile dari Flutter
    public function update(Request $request)
    {
        $user = $request->user();

        // Validasi data yang dikirim Flutter
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'username' => 'sometimes|nullable|string|unique:users,username,' . $user->id,
            'phone' => 'sometimes|nullable|string|max:15',
            'gender' => 'sometimes|nullable|string',
        ]);

        // Ambil data payload dari Flutter
        $data = $request->only(['name', 'username', 'phone', 'gender']);

        // Eksekusi update ke database
        $user->update($data);

        // Kembalikan format {'user': {...}} sesuai ekspektasi '_parseUserFromResponse' di Flutter
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ], 200);
    }

    // Menangani POST /api/profile/photo dari Flutter
    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $user = $request->user();

        if ($request->hasFile('photo')) {
            // Simpan foto ke folder storage/app/public/profile_photos
            $path = $request->file('photo')->store('profile_photos', 'public');

            // Simpan path ke database
            $user->update(['image_path' => $path]);

            return response()->json([
                'message' => 'Photo uploaded successfully',
                'photo_url' => asset('storage/' . $path) // mengembalikan photo_url untuk Flutter
            ], 200);
        }

        return response()->json(['message' => 'File tidak ditemukan'], 400);
    }
}
