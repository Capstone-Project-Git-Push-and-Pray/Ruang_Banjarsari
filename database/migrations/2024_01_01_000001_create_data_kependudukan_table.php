<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migrasi tabel data kependudukan desa.
     */
    public function up(): void
    {
        Schema::create('data_kependudukan', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 16)->unique();
            $table->string('nama_lengkap');
            $table->date('tanggal_lahir');
            $table->text('alamat');
            $table->string('rt', 3);
            $table->string('rw', 3);
            $table->string('dusun')->nullable();
            $table->timestamps();

            $table->index('nik');
        });
    }

    /**
     * Rollback migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_kependudukan');
    }
};
