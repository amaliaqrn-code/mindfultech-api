<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $tasks = $request->user()->tasks()->with('category')->latest()->get();

        return response()->json([
            'message' => 'Task berhasil dibaca',
            'task' => $tasks
        ], 201);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'category_id' => 'required|exists:categories,id',
            'difficulty' => 'required|in:easy,medium,hard',
            'deadline' => 'nullable|date'
        ]);

        $task = $request->user()->tasks()->create([
            'category_id' => $request->category_id,
            'title' => $request->title,
            'description' => $request->description,
            'difficulty' => $request->difficulty,
            'deadline' => $request->deadline,
            'is_completed' => false
        ]);

        return response()->json([
            'message' => 'Task berhasil dibuat',
            'task' => $task
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $task = $request->user()->tasks()->findOrFail($id);

        return response()->json($task);
    }

    public function update(Request $request, $id)
    {
        $task = $request->user()->tasks()->findOrFail($id);

        $task->update([
            'title' => $request->title ?? $task->title,
            'description' => $request->description ?? $task->description,
            'difficulty' => $request->difficulty ?? $task->difficulty,
            'deadline' => $request->deadline ?? $task->deadline,
            'category_id' => $request->category_id ?? $task->category_id,
            'is_completed' => $request->is_completed ?? $task->is_completed,
        ]);

        return response()->json([
            'message' => 'Task berhasil diupdate',
            'task' => $task
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $task = $request->user()->tasks()->findOrFail($id);
        $task->delete();

        return response()->json([
            'message' => 'Task berhasil dihapus'
        ]);
    }

    public function recommendation(Request $request)
    {
        $user = $request->user();

        // 🔹 1. Ambil energy terbaru
        $energy = $user->energyLogs()->latest()->first();

        if (!$energy) {
            return response()->json([
                'message' => 'Silakan isi energy terlebih dahulu'
            ], 400);
        }

        // 🔹 2. Ambil task yang belum selesai
        $tasks = $user->tasks()->where('is_completed', false);

        // 🔹 3. Filter berdasarkan energy
        if ($energy->energy_level == 'low') {
            $tasks->where('difficulty', 'easy');
        } elseif ($energy->energy_level == 'medium') {
            $tasks->whereIn('difficulty', ['easy', 'medium']);
        }
        // kalau high → ambil semua

        // 🔹 4. Urutkan berdasarkan deadline terdekat
        $task = $tasks->orderBy('deadline', 'asc')->first();

        if (!$task) {
            return response()->json([
                'message' => 'Tidak ada task yang cocok'
            ], 404);
        }

        return response()->json([
            'message' => 'Task rekomendasi dari Mindy',
            'task' => $task,
            'energy' => $energy->energy_level
        ]);
    }
}
