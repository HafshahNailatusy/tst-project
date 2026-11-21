<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        if (!$user) {
            return;
        }

        Task::create([
            'user_id'     => $user->id,
            'title'       => 'Setup JWT Authentication & RBAC',
            'description' => 'Konfigurasi token JWT dan pembagian hak akses role.',
            'status'      => 'completed',
            'priority'    => 'high',
            'due_date'    => now()->addDays(2),
        ]);

        Task::create([
            'user_id'     => $user->id,
            'title'       => 'Pengujian Endpoint API Task',
            'description' => 'Uji CRUD tasks menggunakan Postman untuk role admin, member, dan viewer.',
            'status'      => 'in_progress',
            'priority'    => 'medium',
            'due_date'    => now()->addDays(5),
        ]);
    }
}