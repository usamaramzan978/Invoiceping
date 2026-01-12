<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreBusinessProfileRequest extends FormRequest
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
            'whatsapp_number_e164' => ['nullable', 'string', 'max:20'], // E.164 formatted number
            'email' => ['nullable', 'email', 'max:150'],
            'currency' => ['nullable', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'default_reminder_days_before' => ['integer', 'min:0'],
            'default_reminder_days_after' => ['integer', 'min:0'],
            'max_after_due_reminders' => ['integer', 'min:0'],
        ];
    }

    /**
     * Prepare the data for validation.
     * Use E.164 format if available, otherwise use regular input.
     */
    protected function prepareForValidation(): void
    {
        // Use E.164 formatted number if available, otherwise use regular input
        if ($this->has('whatsapp_number_e164') && !empty($this->input('whatsapp_number_e164'))) {
            $this->merge([
                'whatsapp_number' => $this->input('whatsapp_number_e164'),
            ]);
        }
    }

    /**
     * Get validated data, excluding whatsapp_number_e164 as it's only used for merging.
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);

        // Remove whatsapp_number_e164 from validated data as it's only used for merging
        unset($validated['whatsapp_number_e164']);

        return $validated;
    }
}
