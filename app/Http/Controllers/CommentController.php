<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CommentController extends Controller
{
    use ApiResponse;

    public function index()
    {
        try {
            $comments = Comment::with(['user:id,name', 'task:id,title'])
                ->select('id', 'user_id', 'task_id', 'content', 'created_at')
                ->get();

            return $this->success($comments, 'Data comment berhasil ditampilkan');
        } catch (\Throwable $e) {
            return $this->error('Gagal menampilkan comment', 500);
        }
    }

    public function show($id)
    {
        try {
            $comment = Comment::with(['user:id,name', 'task:id,title'])
                ->select('id', 'user_id', 'task_id', 'content', 'created_at')
                ->findOrFail($id);

            return $this->success($comment, 'Detail comment berhasil ditampilkan');
        } catch (ModelNotFoundException $e) {
            return $this->error('Comment tidak ditemukan', 404);
        } catch (\Throwable $e) {
            return $this->error('Gagal menampilkan comment', 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'task_id' => 'required|exists:tasks,id',
                'content' => 'required|string',
            ]);

            // user_id diambil dari token login, bukan dari input
            $data['user_id'] = auth('api')->id();

            $comment = Comment::create($data);

            return $this->success($comment, 'Comment berhasil ditambahkan', 201);
        } catch (ValidationException $e) {
            return $this->error('Validasi gagal', 422, $e->errors());
        } catch (\Throwable $e) {
            return $this->error('Gagal menambahkan comment', 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $comment = Comment::findOrFail($id);
            $user = auth('api')->user();

            // Ownership: user biasa hanya boleh ubah miliknya, admin bebas
            if ($user->role !== 'admin' && $comment->user_id !== $user->id) {
                return $this->error('Anda hanya boleh mengubah comment milik sendiri', 403);
            }

            $data = $request->validate([
                'content' => 'required|string',
            ]);

            $comment->update($data);

            return $this->success($comment, 'Comment berhasil diperbarui');
        } catch (ModelNotFoundException $e) {
            return $this->error('Comment tidak ditemukan', 404);
        } catch (ValidationException $e) {
            return $this->error('Validasi gagal', 422, $e->errors());
        } catch (\Throwable $e) {
            return $this->error('Gagal memperbarui comment', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $comment = Comment::findOrFail($id);
            $comment->delete();

            return $this->success(null, 'Comment berhasil dihapus');
        } catch (ModelNotFoundException $e) {
            return $this->error('Comment tidak ditemukan', 404);
        } catch (\Throwable $e) {
            return $this->error('Gagal menghapus comment', 500);
        }
    }
}