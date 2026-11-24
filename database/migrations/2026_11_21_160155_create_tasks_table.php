<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();

            // SEMENTARA: dibuat nullable tanpa constrained() karena migration tabel categories belum di-push.
            // JIKA TABEL CATEGORIES SUDAH ADA, ganti baris di bawah menjadi:
            // $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->unsignedBigInteger('category_id')->nullable();

            $table->string('title');
            $table->text('description')->nullable();
            $table->date('deadline')->nullable();
            $table->enum('status', [
                'pending',
                'in_progress',
                'completed'
            ])->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};