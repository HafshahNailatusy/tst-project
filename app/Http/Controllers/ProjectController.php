<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = auth('api')->user() ?? $request->user();

            $projects = Project::with('user:id,name,email')
                ->select(
                    'id',
                    'user_id',
                    'name',
                    'description',
                    'start_date',
                    'end_date',
                    'status',
                    'created_at'
                )
                ->when($user && $user->role !== 'admin', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->get();

            return $this->apiSuccess(
                'Data project berhasil ditampilkan',
                $projects
            );
        } catch (\Throwable $e) {
            return $this->apiError(
                'Gagal menampilkan project'
            );
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $project = Project::with('user:id,name,email')
                ->select(
                    'id',
                    'user_id',
                    'name',
                    'description',
                    'start_date',
                    'end_date',
                    'status'
                )
                ->findOrFail($id);

            $user = auth('api')->user() ?? $request->user();

            if (
                $user &&
                $user->role !== 'admin' &&
                (int) $project->user_id !== (int) $user->id
            ) {
                return $this->apiError(
                    'Anda tidak memiliki akses untuk melihat project ini',
                    403
                );
            }

            return $this->apiSuccess(
                'Detail project berhasil ditampilkan',
                $project
            );
        } catch (ModelNotFoundException $e) {
            return $this->apiError(
                'Project tidak ditemukan',
                404
            );
        } catch (\Throwable $e) {
            return $this->apiError(
                'Gagal menampilkan detail project'
            );
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'status' => 'sometimes|in:pending,in_progress,completed'
            ]);

            $user = auth('api')->user() ?? $request->user();
            $project = $user->projects()->create($data);

            return $this->apiSuccess(
                'Project berhasil ditambahkan',
                $project->only([
                    'id',
                    'user_id',
                    'name',
                    'description',
                    'start_date',
                    'end_date',
                    'status',
                    'created_at',
                    'updated_at'
                ]),
                201
            );
        } catch (ValidationException $e) {
            return $this->apiError(
                'Validasi gagal',
                422,
                $e->errors()
            );
        } catch (\Throwable $e) {
            return $this->apiError(
                'Gagal menambahkan project'
            );
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $project = Project::findOrFail($id);
            $user = auth('api')->user() ?? $request->user();

            if (
                $user &&
                $user->role !== 'admin' &&
                (int) $project->user_id !== (int) $user->id
            ) {
                return $this->apiError(
                    'Anda tidak memiliki akses untuk mengubah project ini',
                    403
                );
            }

            $data = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|nullable|string',
                'start_date' => 'sometimes|nullable|date',
                'end_date' => 'sometimes|nullable|date|after_or_equal:start_date',
                'status' => 'sometimes|in:pending,in_progress,completed'
            ]);

            $project->update($data);

            return $this->apiSuccess(
                'Project berhasil diperbarui',
                $project
            );
        } catch (ValidationException $e) {
            return $this->apiError(
                'Validasi gagal',
                422,
                $e->errors()
            );
        } catch (\Throwable $e) {
            return $this->apiError(
                'Project tidak ditemukan atau gagal diperbarui',
                404
            );
        }
    }

    public function destroy($id)
    {
        try {
            $project = Project::findOrFail($id);
            $project->delete();

            return $this->apiSuccess(
                'Project berhasil dihapus'
            );
        } catch (\Throwable $e) {
            return $this->apiError(
                'Project tidak ditemukan',
                404
            );
        }
    }

    private function apiSuccess(string $message, $data = null, int $status = 200)
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    private function apiError(string $message, int $status = 500, $errors = null)
    {
        $response = ['message' => $message];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }
}
