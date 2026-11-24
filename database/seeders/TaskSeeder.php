<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $user = User::where('role', 'user')->first();
        $project = Project::first();
        $category = class_exists(Category::class) ? Category::first() : null;

        if (!$admin || !$user || !$project) {
            return;
        }

        Task::firstOrCreate(
            ['title' => 'Merancang ERD dan Struktur Database'],
            [
                'user_id'     => $admin->id,
                'project_id'  => $project->id,
                'category_id' => $category?->id,
                'description' => 'Menyusun relasi antar tabel users, projects, categories, tasks, dan comments.',
                'deadline'    => now()->addDays(3)->toDateString(),
                'status'      => 'completed',
            ]
        );

        Task::firstOrCreate(
            ['title' => 'Implementasi REST API Task & RBAC'],
            [
                'user_id'     => $user->id,
                'project_id'  => $project->id,
                'category_id' => $category?->id,
                'description' => 'Membuat endpoint CRUD tasks dengan proteksi JWT dan hak akses role.',
                'deadline'    => now()->addDays(7)->toDateString(),
                'status'      => 'in_progress',
            ]
        );
    }
}