<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'whatsapp_number' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'status' => ['nullable', 'in:active,inactive'],
        ];
    }
}
