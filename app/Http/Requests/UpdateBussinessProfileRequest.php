<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateBussinessProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'business_name' => ['required', 'string', 'max:200'],
            'whatsapp_number' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'currency' => ['nullable', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'default_reminder_days_before' => ['nullable', 'integer'],
            'default_reminder_days_after' => ['nullable', 'integer'],
            'max_after_due_reminders' => ['nullable', 'integer'],
        ];
    }
}
