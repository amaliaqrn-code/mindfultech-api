<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Resolve Flutter static category_id (1-6) to the user actual category ID.
     * Flutter sends kategori.index + 1 (1-6). User categories are seeded in order.
     */
    private function resolveCategoryId(int $flutterCategoryId, $user): ?int
    {
        $categories = $user->categories()->orderBy('id', 'asc')->get();
        $zeroBasedIndex = $flutterCategoryId - 1;
        if ($zeroBasedIndex < 0 || $zeroBasedIndex >= $categories->count()) {
            return null;
        }
        return $categories[$zeroBasedIndex]->id;
    }

    public function index(Request $request)
    {
        // PERUBAHAN: Hanya ambil task yang BELUM selesai (is_completed = false)
        $tasks = $request->user()->tasks()
            ->where('is_completed', false)
            ->with('category')
            ->latest()
            ->get();

        return response()->json(['message' => 'Task berhasil dibaca', 'task' => $tasks], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'category_id' => 'required|integer|min:1|max:6',
            'difficulty'  => 'required|in:easy,medium,hard',
            'deadline'    => 'nullable|date',
        ]);
        $user = $request->user();
        $actualCategoryId = $this->resolveCategoryId($request->category_id, $user);
        if (!$actualCategoryId) {
            return response()->json(['message' => 'Kategori tidak ditemukan.'], 422);
        }
        $task = $user->tasks()->create([
            'category_id' => $actualCategoryId,
            'title'       => $request->title,
            'description' => $request->description,
            'difficulty'  => $request->difficulty,
            'deadline'    => $request->deadline,
            'is_completed' => false,
        ]);
        return response()->json(['message' => 'Task berhasil dibuat', 'task' => $task], 201);
    }

    public function show(Request $request, $id)
    {
        $task = $request->user()->tasks()->findOrFail($id);
        return response()->json($task);
    }

    public function update(Request $request, $id)
    {
        $task = $request->user()->tasks()->findOrFail($id);

        $updates = [
            'title'       => $request->title ?? $task->title,
            'description' => $request->description ?? $task->description,
            'difficulty'  => $request->difficulty ?? $task->difficulty,
            'deadline'    => $request->deadline ?? $task->deadline,
            'is_completed' => $request->has('is_completed') ? filter_var($request->is_completed, FILTER_VALIDATE_BOOLEAN) : $task->is_completed,
        ];

        if ($request->has('category_id')) {
            $actualId = $this->resolveCategoryId($request->category_id, $request->user());
            if ($actualId) { $updates['category_id'] = $actualId; }
        }
        $task->update($updates);
        return response()->json(['message' => 'Task berhasil diupdate', 'task' => $task]);
    }

    // PERUBAHAN BESAR: Mengubah fungsi hapus menjadi complete status
    public function destroy(Request $request, $id)
    {
        $task = $request->user()->tasks()->findOrFail($id);

        // Ubah is_completed menjadi true, jangan di-delete()
        $task->update([
            'is_completed' => true
        ]);

        return response()->json(['message' => 'Task berhasil diselesaikan (status updated)']);
    }

    public function recommendation(Request $request)
    {
        $user = $request->user();
        $energy = $user->energyLogs()->latest()->first();
        if (!$energy) {
            return response()->json(['message' => 'Silakan isi energy terlebih dahulu'], 400);
        }

        // Tetap mencari yang is_completed = false
        $tasks = $user->tasks()->where('is_completed', false);

        if ($energy->energy_level == 'low')    $tasks->where('difficulty', 'easy');
        elseif ($energy->energy_level == 'medium') $tasks->whereIn('difficulty', ['easy', 'medium']);

        $task = $tasks->orderBy('deadline', 'asc')->first();
        if (!$task) {
            return response()->json(['message' => 'Tidak ada task yang cocok'], 404);
        }
        return response()->json(['message' => 'Task rekomendasi dari Mindy', 'task' => $task, 'energy' => $energy->energy_level]);
    }
}
