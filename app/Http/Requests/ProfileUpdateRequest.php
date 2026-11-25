<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'nama_lengkap' => ['nullable', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()?->id),
            ],
            'telepon' => [
                $this->user()?->isWarga() ? 'required' : 'nullable',
                'string',
                'max:20',
                Rule::unique(User::class)->ignore($this->user()?->id),
            ],
            'whatsapp' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * Pesan validasi kustom untuk pembaruan profil.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Alamat email wajib diisi untuk keperluan reset kata sandi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email ini sudah dipakai akun lain.',
            'telepon.unique' => 'Nomor telepon ini sudah dipakai akun lain.',
            'telepon.required' => 'Nomor telepon / HP wajib diisi.',
        ];
    }
}
