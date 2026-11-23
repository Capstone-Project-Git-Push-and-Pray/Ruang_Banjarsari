<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migrasi tabel sanksi fitur warga (PBI #46, #47, #48, #49).
     */
    public function up(): void
    {
        Schema::create('sanksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('fitur');
            $table->unsignedTinyInteger('tingkat');
            $table->foreignId('riwayat_moderasi_id')->nullable()->constrained('riwayat_moderasi')->nullOnDelete();
            $table->timestamp('mulai_pada');
            $table->timestamp('berakhir_pada')->nullable();
            $table->boolean('permanen')->default(false);
            $table->timestamp('dicabut_pada')->nullable();
            $table->string('jenis_pencabutan')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->foreignId('diubah_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'fitur', 'mulai_pada']);
        });
    }

    /**
     * Rollback migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('sanksi');
    }
};
