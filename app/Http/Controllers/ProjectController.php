<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Validation\ValidationException;

class ProjectController extends Controller
{
    public function index()
    {
        try {
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
    public function show($id)
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

            return $this->apiSuccess(
                'Detail project berhasil ditampilkan',
                $project
            );
        } catch (\Throwable $e) {
            return $this->apiError(
                'Project tidak ditemukan',
                404
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

            $project = $request->user()->projects()->create($data);

            return $this->apiSuccess(
                'Project berhasil ditambahkan',
                $project,
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

            $data = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|nullable|string',
                'start_date' => 'sometimes|nullable|date',
                'end_date' => 'sometimes|nullable|date|after_or_equal:start_date',
                'status' => 'sometimes|in:pending,in_progress,completed'
            ]);

            $project = $request->user()->projects()->update($data);

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
