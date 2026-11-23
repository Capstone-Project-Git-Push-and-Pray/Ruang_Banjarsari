<?php

namespace App\Http\Requests;

use App\Models\Sanksi;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UbahSanksiRequest extends FormRequest
{
    /**
     * Akses dibatasi middleware role:admin_desa pada grup rute admin.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi perubahan sanksi (PBI #49).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tindakan' => ['required', Rule::in(['ubah', Sanksi::PENCABUTAN_DIAKHIRI, Sanksi::PENCABUTAN_DIBATALKAN])],
            'permanen' => ['boolean'],
            'berakhir_pada' => [
                Rule::requiredIf($this->input('tindakan') === 'ubah' && ! $this->boolean('permanen')),
                'nullable',
                'date',
                'after:now',
            ],
            'catatan_admin' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    /**
     * Pesan kesalahan validasi dalam Bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tindakan.required' => 'Tindakan perubahan sanksi wajib dipilih.',
            'tindakan.in' => 'Tindakan perubahan sanksi tidak valid.',
            'berakhir_pada.required' => 'Tanggal berakhir wajib diisi, kecuali sanksi dijadikan permanen.',
            'berakhir_pada.date' => 'Tanggal berakhir tidak valid.',
            'berakhir_pada.after' => 'Tanggal berakhir harus setelah waktu sekarang.',
            'catatan_admin.required' => 'Catatan alasan perubahan wajib diisi agar keputusan tercatat.',
            'catatan_admin.min' => 'Catatan alasan perubahan minimal 5 karakter.',
            'catatan_admin.max' => 'Catatan alasan perubahan maksimal 1000 karakter.',
        ];
    }
}
