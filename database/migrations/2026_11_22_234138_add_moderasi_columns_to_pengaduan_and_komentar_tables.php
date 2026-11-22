<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan penanda konten disembunyikan (PBI #44) dan soft delete (PBI #45).
     */
    public function up(): void
    {
        foreach (['pengaduan', 'komentar'] as $namaTabel) {
            Schema::table($namaTabel, function (Blueprint $table) {
                $table->timestamp('disembunyikan_pada')->nullable();
                $table->foreignId('disembunyikan_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->softDeletes();
            });
        }
    }

    /**
     * Rollback migrasi. Konten yang sudah dihapus lewat moderasi akan muncul kembali.
     */
    public function down(): void
    {
        foreach (['pengaduan', 'komentar'] as $namaTabel) {
            Schema::table($namaTabel, function (Blueprint $table) {
                $table->dropConstrainedForeignId('disembunyikan_oleh');
                $table->dropColumn('disembunyikan_pada');
                $table->dropSoftDeletes();
            });
        }
    }
};
