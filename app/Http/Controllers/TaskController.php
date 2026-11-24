<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        try {
            $query = Task::with([
                'user:id,name,email,role',
                'project:id,name,status',
                'category:id,name',
            ])->latest();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            return $this->success($query->get(), 'Daftar task berhasil diambil', 200);
        } catch (Exception $e) {
            return $this->error('Gagal mengambil daftar task: ' . $e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id'  => 'required|exists:projects,id',
            'category_id' => 'nullable|exists:categories,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline'    => 'nullable|date',
            'status'      => 'nullable|in:pending,in_progress,completed',
        ]);

        if ($validator->fails()) {
            return $this->error('Validasi gagal', 422, $validator->errors());
        }

        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error('Unauthenticated', 401);
            }

            $task = Task::create([
                'user_id'     => $user->id,
                'project_id'  => $request->project_id,
                'category_id' => $request->category_id,
                'title'       => $request->title,
                'description' => $request->description,
                'deadline'    => $request->deadline,
                'status'      => $request->status ?? 'pending',
            ]);

            $task->load(['user:id,name,email,role', 'project:id,name,status', 'category:id,name']);

            return $this->success($task, 'Task berhasil dibuat', 201);
        } catch (Exception $e) {
            return $this->error('Gagal membuat task: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $task = Task::with([
                'user:id,name,email,role',
                'project:id,name,status',
                'category:id,name',
            ])->find($id);

            if (!$task) {
                return $this->error('Task tidak ditemukan', 404);
            }

            return $this->success($task, 'Detail task berhasil diambil', 200);
        } catch (Exception $e) {
            return $this->error('Gagal mengambil detail task: ' . $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $task = Task::find($id);

            if (!$task) {
                return $this->error('Task tidak ditemukan', 404);
            }

            $user = auth('api')->user();

            if (!$user) {
                return $this->error('Unauthenticated', 401);
            }

            if ($user->role !== 'admin' && (int) $task->user_id !== (int) $user->id) {
                return $this->error('Akses ditolak: Anda hanya dapat mengubah task milik Anda sendiri', 403);
            }

            $validator = Validator::make($request->all(), [
                'project_id'  => 'sometimes|required|exists:projects,id',
                'category_id' => 'nullable|exists:categories,id',
                'title'       => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'deadline'    => 'nullable|date',
                'status'      => 'sometimes|in:pending,in_progress,completed',
            ]);

            if ($validator->fails()) {
                return $this->error('Validasi gagal', 422, $validator->errors());
            }

            $task->update($request->only([
                'project_id',
                'category_id',
                'title',
                'description',
                'deadline',
                'status',
            ]));

            $task->load(['user:id,name,email,role', 'project:id,name,status', 'category:id,name']);

            return $this->success($task, 'Task berhasil diperbarui', 200);
        } catch (Exception $e) {
            return $this->error('Gagal memperbarui task: ' . $e->getMessage(), 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $task = Task::find($id);

            if (!$task) {
                return $this->error('Task tidak ditemukan', 404);
            }

            $user = auth('api')->user();

            if (!$user) {
                return $this->error('Unauthenticated', 401);
            }

            if ($user->role !== 'admin' && (int) $task->user_id !== (int) $user->id) {
                return $this->error('Akses ditolak: Anda hanya dapat mengubah status task milik Anda sendiri', 403);
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,in_progress,completed',
            ]);

            if ($validator->fails()) {
                return $this->error('Validasi gagal', 422, $validator->errors());
            }

            $task->update(['status' => $request->status]);

            return $this->success($task, 'Status task berhasil diperbarui', 200);
        } catch (Exception $e) {
            return $this->error('Gagal memperbarui status task: ' . $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error('Unauthenticated', 401);
            }

            if ($user->role !== 'admin') {
                return $this->error('Akses ditolak: Hanya admin yang dapat menghapus task', 403);
            }

            $task = Task::find($id);

            if (!$task) {
                return $this->error('Task tidak ditemukan', 404);
            }

            $task->delete();

            return $this->success(null, 'Task berhasil dihapus', 200);
        } catch (Exception $e) {
            return $this->error('Gagal menghapus task: ' . $e->getMessage(), 500);
        }
    }
}