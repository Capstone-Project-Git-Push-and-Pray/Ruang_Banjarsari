<?php

namespace App\Models;

use Database\Factories\DataKependudukanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataKependudukan extends Model
{
    /** @use HasFactory<DataKependudukanFactory> */
    use HasFactory;

    protected $table = 'data_kependudukan';

    /**
     * Atribut yang boleh diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nik',
        'nama_lengkap',
        'tanggal_lahir',
        'alamat',
        'rt',
        'rw',
        'dusun',
    ];

    /**
     * Casting atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }
}
