<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // READ - User melihat miliknya, admin melihat semua
    public function index(Request $request)
    {
        $user = $request->user();

        $categories = Category::query()
            ->when($user->role !== 'admin', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        return response()->json($categories);
    }

    // CREATE - User dan Admin
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $data['user_id'] = $request->user()->id;

        $category = Category::create($data);

        return response()->json([
            'message' => 'Kategori berhasil ditambahkan',
            'data' => $category
        ], 201);
    }

    // READ DETAIL
    public function show(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $this->checkAccess($request, $category);

        return response()->json($category);
    }

    // UPDATE - User dan Admin
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $this->checkAccess($request, $category);

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'description' => 'sometimes|nullable|string',
        ]);

        $category->update($data);

        return response()->json([
            'message' => 'Kategori berhasil diperbarui',
            'data' => $category
        ]);
    }

    // DELETE - Khusus Admin
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'message' => 'Kategori berhasil dihapus'
        ]);
    }

    // Membatasi akses user ke kategori miliknya
    private function checkAccess(Request $request, Category $category)
    {
        if (
            $request->user()->role !== 'admin' &&
            $category->user_id !== $request->user()->id
        ) {
            abort(403, 'Anda tidak memiliki akses ke kategori ini');
        }
    }
}
