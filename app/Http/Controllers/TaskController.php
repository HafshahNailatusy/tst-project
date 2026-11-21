<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // GET 
    public function index(Request $request)
    {
        $query = Task::with('user:id,name,email,role')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        return response()->json([
            'success' => true,
            'message' => 'Daftar task berhasil diambil',
            'data'    => $query->get(),
        ], 200);
    }

    // POST 
    public function store(TaskRequest $request)
    {
        $task = Task::create([
            'user_id'     => auth('api')->id(),
            'title'       => $request->title,
            'description' => $request->description,
            'status'      => $request->status ?? 'pending',
            'priority'    => $request->priority ?? 'medium',
            'due_date'    => $request->due_date,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task berhasil dibuat',
            'data'    => $task->load('user:id,name,email,role'),
        ], 201);
    }

    // GET {id}
    public function show($id)
    {
        $task = Task::with('user:id,name,email,role')->find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $task,
        ], 200);
    }

    // PUT {id}
    public function update(TaskRequest $request, $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task tidak ditemukan',
            ], 404);
        }

        $user = auth('api')->user();

        if ($user->role !== 'admin' && $task->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Anda hanya dapat mengubah task milik Anda sendiri',
            ], 403);
        }

        $task->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Task berhasil diperbarui',
            'data'    => $task->load('user:id,name,email,role'),
        ], 200);
    }

    // PATCH {id} 
    public function updateStatus(Request $request, $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task tidak ditemukan',
            ], 404);
        }

        $user = auth('api')->user();

        if ($user->role !== 'admin' && $task->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Anda hanya dapat mengubah status task milik Anda sendiri',
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $task->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status task berhasil diperbarui',
            'data'    => $task,
        ], 200);
    }

    // DELETE {id}
    public function destroy($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task tidak ditemukan',
            ], 404);
        }

        $user = auth('api')->user();

        if ($user->role !== 'admin' && $task->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Anda hanya dapat menghapus task milik Anda sendiri',
            ], 403);
        }

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task berhasil dihapus',
        ], 200);
    }
}