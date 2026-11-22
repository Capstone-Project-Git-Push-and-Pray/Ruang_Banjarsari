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
                $this->user()?->isWarga() ? 'nullable' : 'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()?->id),
            ],
            'telepon' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique(User::class)->ignore($this->user()?->id),
            ],
            'whatsapp' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * Konfigurasi validasi lanjutan untuk memastikan warga memiliki kontak aktif.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->user()?->isWarga() && empty($this->email) && empty($this->telepon)) {
                $validator->errors()->add('email', 'Minimal cantumkan salah satu dari email atau nomor telepon yang aktif.');
            }
        });
    }
}
