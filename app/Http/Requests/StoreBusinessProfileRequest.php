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
            'address' => ['nullable', 'string'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'], // 2MB max
            'currency' => ['nullable', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'default_reminder_days_before' => ['integer', 'min:0'],
            'default_reminder_days_after' => ['integer', 'min:0'],
            'max_after_due_reminders' => ['integer', 'min:0'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'business_name' => 'business name',
            'whatsapp_number' => 'WhatsApp number',
            'email' => 'email address',
            'address' => 'address',
            'tax_id' => 'tax ID',
            'image' => 'logo image',
            'currency' => 'currency',
            'timezone' => 'timezone',
            'default_reminder_days_before' => 'default reminder days before',
            'default_reminder_days_after' => 'default reminder days after',
            'max_after_due_reminders' => 'max after due reminders',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'business_name.required' => 'The business name is required.',
            'business_name.max' => 'The business name cannot exceed 200 characters.',
            'whatsapp_number.required' => 'The WhatsApp number is required.',
            'whatsapp_number.max' => 'The WhatsApp number cannot exceed 20 characters.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'The email address cannot exceed 150 characters.',
            'image.image' => 'The logo must be an image file.',
            'image.mimes' => 'The logo must be a JPEG, JPG, PNG, GIF, or WEBP file.',
            'image.max' => 'The logo size cannot exceed 2MB.',
            'currency.max' => 'The currency code cannot exceed 10 characters.',
            'timezone.max' => 'The timezone cannot exceed 50 characters.',
            'tax_id.max' => 'The tax ID cannot exceed 50 characters.',
        ];
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

    /**
     * Prepare the data for validation.
     * Use E.164 format if available, otherwise use regular input.
     */
    protected function prepareForValidation(): void
    {
        // Use E.164 formatted number if available, otherwise use regular input
        if ($this->has('whatsapp_number_e164') && ! empty($this->input('whatsapp_number_e164'))) {
            $this->merge([
                'whatsapp_number' => $this->input('whatsapp_number_e164'),
            ]);
        }
    }
}
