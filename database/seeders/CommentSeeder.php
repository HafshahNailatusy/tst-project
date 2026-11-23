<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $tasks = Task::all();

        if ($users->isEmpty() || $tasks->isEmpty()) {
            return; 
        }

        foreach ($tasks->take(3) as $task) {
            Comment::create([
                'user_id' => $users->random()->id,
                'task_id' => $task->id,
                'content' => 'Komentar contoh untuk tugas: ' . $task->title,
            ]);
        }
    }
}