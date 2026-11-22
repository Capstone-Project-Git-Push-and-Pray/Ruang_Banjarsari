<?php

namespace App\Http\Requests;

use App\Enums\AlasanModerasi;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HapusKontenRequest extends FormRequest
{
    /**
     * Hanya Admin Desa yang berwenang memoderasi konten pada rute ini (PBI #45).
     */
    public function authorize(): bool
    {
        $konten = $this->route('komentar') ?? $this->route('pengaduan');

        return $this->user()->can('moderasi', $konten);
    }

    /**
     * Aturan validasi alasan penghapusan konten.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'alasan' => ['required', Rule::enum(AlasanModerasi::class)],
            'catatan_moderasi' => [
                'nullable',
                Rule::requiredIf($this->input('alasan') === AlasanModerasi::Lainnya->value),
                'string',
                'max:1000',
            ],
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
            'alasan.required' => 'Alasan penghapusan wajib dipilih.',
            'alasan.enum' => 'Alasan penghapusan yang dipilih tidak valid.',
            'catatan_moderasi.required' => 'Catatan wajib diisi jika memilih alasan Lainnya.',
            'catatan_moderasi.max' => 'Catatan maksimal 1000 karakter.',
        ];
    }
}
