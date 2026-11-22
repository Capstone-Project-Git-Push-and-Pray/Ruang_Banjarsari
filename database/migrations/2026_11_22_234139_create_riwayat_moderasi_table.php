<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migrasi tabel catatan keputusan moderasi konten (PBI #44, PBI #45).
     *
     * Salinan isi konten disimpan agar catatan tetap utuh meskipun konten aslinya hilang.
     */
    public function up(): void
    {
        Schema::create('riwayat_moderasi', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_konten');
            $table->foreignId('pengaduan_id')->nullable()->constrained('pengaduan')->nullOnDelete();
            $table->foreignId('komentar_id')->nullable()->constrained('komentar')->nullOnDelete();
            $table->foreignId('pemilik_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('aksi');
            $table->string('alasan')->nullable();
            $table->text('catatan')->nullable();
            $table->text('isi_konten');
            $table->foreignId('dilakukan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Rollback migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_moderasi');
    }
};
