<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Izinkan semua user mengakses endpoint login
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi login
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6'],
        ];
    }

    /**
     * Pesan error custom (opsional)
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi',
            'email.email'    => 'Format email tidak valid',
            'password.required' => 'Password wajib diisi',
            'password.min'      => 'Password minimal 6 karakter',
        ];
    }
}
