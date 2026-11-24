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

    /**
     * GET /api/tasks
     * Menampilkan seluruh daftar task beserta relasi user dan project
     */
    public function index(Request $request)
    {
        try {
            // JIKA MODEL CATEGORY & COMMENT SUDAH LENGKAP, ubah with() menjadi:
            // $query = Task::with(['user:id,name,email,role', 'project:id,name,status', 'category:id,name', 'comments'])->latest();
            $query = Task::with([
                'user:id,name,email,role',
                'project:id,name,status',
            ])->latest();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            return $this->success($query->get(), 'Daftar task berhasil diambil', 200);
        } catch (Exception $e) {
            return $this->error('Gagal mengambil daftar task: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/tasks
     * Membuat task baru (bisa dilakukan oleh admin maupun user)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id'  => 'required|exists:projects,id',
            // JIKA TABEL CATEGORIES SUDAH ADA, ganti rule category_id di bawah menjadi:
            // 'category_id' => 'nullable|exists:categories,id',
            'category_id' => 'nullable|integer',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline'    => 'nullable|date',
            'status'      => 'nullable|in:pending,in_progress,completed',
        ]);

        if ($validator->fails()) {
            return $this->error('Validasi gagal', 422, $validator->errors());
        }

        try {
            $task = Task::create([
                'user_id'     => auth('api')->id(),
                'project_id'  => $request->project_id,
                'category_id' => $request->category_id,
                'title'       => $request->title,
                'description' => $request->description,
                'deadline'    => $request->deadline,
                'status'      => $request->status ?? 'pending',
            ]);

            // JIKA MODEL CATEGORY SUDAH ADA, tambahkan 'category:id,name' di dalam load()
            $task->load(['user:id,name,email,role', 'project:id,name,status']);

            return $this->success($task, 'Task berhasil dibuat', 201);
        } catch (Exception $e) {
            return $this->error('Gagal membuat task: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/tasks/{id}
     * Menampilkan detail satu task berdasarkan ID
     */
    public function show($id)
    {
        try {
            // JIKA MODEL CATEGORY & COMMENT SUDAH ADA, ubah with() menjadi:
            // $task = Task::with(['user:id,name,email,role', 'project:id,name,status', 'category:id,name', 'comments.user:id,name'])->find($id);
            $task = Task::with([
                'user:id,name,email,role',
                'project:id,name,status',
            ])->find($id);

            if (!$task) {
                return $this->error('Task tidak ditemukan', 404);
            }

            return $this->success($task, 'Detail task berhasil diambil', 200);
        } catch (Exception $e) {
            return $this->error('Gagal mengambil detail task: ' . $e->getMessage(), 500);
        }
    }

    /**
     * PUT/PATCH /api/tasks/{id}
     * Mengubah data task (Admin: semua task | User: hanya task miliknya sendiri)
     */
    public function update(Request $request, $id)
    {
        try {
            $task = Task::find($id);

            if (!$task) {
                return $this->error('Task tidak ditemukan', 404);
            }

            $user = auth('api')->user();

            // Pengecekan RBAC: Admin bisa ubah semua; User hanya milik sendiri
            if ($user->role !== 'admin' && $task->user_id !== $user->id) {
                return $this->error('Akses ditolak: Anda hanya dapat mengubah task milik Anda sendiri', 403);
            }

            $validator = Validator::make($request->all(), [
                'project_id'  => 'sometimes|required|exists:projects,id',
                // JIKA TABEL CATEGORIES SUDAH ADA, ganti rule category_id di bawah menjadi:
                // 'category_id' => 'nullable|exists:categories,id',
                'category_id' => 'nullable|integer',
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

            // JIKA MODEL CATEGORY SUDAH ADA, tambahkan 'category:id,name' di dalam load()
            $task->load(['user:id,name,email,role', 'project:id,name,status']);

            return $this->success($task, 'Task berhasil diperbarui', 200);
        } catch (Exception $e) {
            return $this->error('Gagal memperbarui task: ' . $e->getMessage(), 500);
        }
    }

    /**
     * PATCH /api/tasks/{id}/status
     * Mengubah status task secara cepat (Admin: semua task | User: hanya task miliknya sendiri)
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $task = Task::find($id);

            if (!$task) {
                return $this->error('Task tidak ditemukan', 404);
            }

            $user = auth('api')->user();

            if ($user->role !== 'admin' && $task->user_id !== $user->id) {
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

    /**
     * DELETE /api/tasks/{id}
     * Menghapus task (Admin: semua task | User: hanya task miliknya sendiri)
     */
    public function destroy($id)
    {
        try {
            $task = Task::find($id);

            if (!$task) {
                return $this->error('Task tidak ditemukan', 404);
            }

            $user = auth('api')->user();

            // Pengecekan RBAC: Admin bisa hapus semua; User hanya milik sendiri
            if ($user->role !== 'admin' && $task->user_id !== $user->id) {
                return $this->error('Akses ditolak: Anda hanya dapat menghapus task milik Anda sendiri', 403);
            }

            $task->delete();

            return $this->success(null, 'Task berhasil dihapus', 200);
        } catch (Exception $e) {
            return $this->error('Gagal menghapus task: ' . $e->getMessage(), 500);
        }
    }
}