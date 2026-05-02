<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->categories;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $category = $request->user()->categories()->create([
            'name' => $request->name
        ]);

        return response()->json([
            'message' => 'Category berhasil dibuat',
            'category' => $category
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $category = $request->user()->categories()->findOrFail($id);

        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $category = $request->user()->categories()->findOrFail($id);

        $category->update([
            'name' => $request->name ?? $category->name
        ]);

        return response()->json([
            'message' => 'Category berhasil diupdate',
            'category' => $category
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $category = $request->user()->categories()->findOrFail($id);
        $category->delete();

        return response()->json([
            'message' => 'Category berhasil dihapus'
        ], 200);
    }
}
