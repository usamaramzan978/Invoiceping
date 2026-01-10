<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateBusinessProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:200'],
            'whatsapp_number' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'currency' => ['nullable', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'default_reminder_days_before' => ['integer', 'min:0'],
            'default_reminder_days_after' => ['integer', 'min:0'],
            'max_after_due_reminders' => ['integer', 'min:0'],
        ];
    }
}
