<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migrasi tabel pengaduan (laporan pengaduan dan aspirasi warga).
     */
    public function up(): void
    {
        Schema::create('pengaduan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('jenis');
            $table->foreignId('kategori_id')->constrained('kategori')->cascadeOnDelete();
            $table->string('judul');
            $table->text('deskripsi');
            $table->string('lokasi')->nullable();
            $table->string('status')->default('diajukan');
            $table->timestamps();

            $table->index('status');
            $table->index('kategori_id');
            $table->index('user_id');
        });
    }

    /**
     * Rollback migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaduan');
    }
};
