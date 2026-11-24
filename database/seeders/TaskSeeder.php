<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $project = Project::first();

        if (!$user || !$project) {
            return;
        }

        Task::create([
            'user_id'     => $user->id,
            'project_id'  => $project->id,
            'category_id' => null, // Nanti diisi Category::first()?->id jika seeder Category sudah ada
            'title'       => 'Merancang ERD dan Struktur Database',
            'description' => 'Menyusun relasi antar tabel users, projects, categories, tasks, dan comments.',
            'deadline'    => now()->addDays(3)->toDateString(),
            'status'      => 'completed',
        ]);

        Task::create([
            'user_id'     => $user->id,
            'project_id'  => $project->id,
            'category_id' => null, // Nanti diisi Category::first()?->id jika seeder Category sudah ada
            'title'       => 'Implementasi REST API Task & RBAC',
            'description' => 'Membuat endpoint CRUD tasks dengan proteksi JWT dan hak akses role.',
            'deadline'    => now()->addDays(7)->toDateString(),
            'status'      => 'in_progress',
        ]);
    }
}