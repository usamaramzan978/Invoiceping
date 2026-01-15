<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreClientRequest extends FormRequest
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

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'client name',
            'whatsapp_number' => 'WhatsApp number',
            'email' => 'email address',
            'status' => 'status',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The client name is required.',
            'name.max' => 'The client name cannot exceed 150 characters.',
            'whatsapp_number.required' => 'The WhatsApp number is required.',
            'whatsapp_number.max' => 'The WhatsApp number cannot exceed 20 characters.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'The email address cannot exceed 150 characters.',
            'status.in' => 'The status must be either active or inactive.',
        ];
    }
}
