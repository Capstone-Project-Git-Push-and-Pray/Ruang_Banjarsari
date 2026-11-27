<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migrasi tabel pengingat tindak lanjut dari Kepala Desa ke Admin Desa (PBI #50, #51, #54).
     */
    public function up(): void
    {
        Schema::create('pengingat_tindak_lanjut', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengaduan_id')->constrained('pengaduan')->cascadeOnDelete();
            $table->foreignId('dikirim_oleh')->constrained('users')->cascadeOnDelete();
            $table->string('catatan', 255)->nullable();
            $table->timestamp('selesai_pada')->nullable();
            $table->timestamps();

            $table->index(['pengaduan_id', 'created_at']);
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengingat_tindak_lanjut');
    }
};
