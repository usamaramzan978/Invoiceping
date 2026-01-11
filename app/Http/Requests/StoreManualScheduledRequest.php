<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreManualScheduledRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller/action
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user()->id;
        $businessId = $this->user()->business?->id;

        return [
            // Required fields
            'invoice_ids' => ['required', 'array', 'min:1'],
            'invoice_ids.*' => [
                'required',
                'uuid',
                Rule::exists('invoices', 'id')->where(fn($query) => $query->where('business_id', $businessId)),
            ],
            'channel' => ['required', 'in:email,whatsapp,sms'],
            'scheduled_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:now'],
            'email_template_id' => [
                'nullable',
                Rule::requiredIf($this->input('channel') === 'email'),
                'integer',
                Rule::exists('email_templates', 'id')->where('user_id', $userId)->where('is_active', true),
            ],
            'message_template_id' => [
                'nullable',
                Rule::requiredIf(in_array($this->input('channel'), ['whatsapp', 'sms'])),
                'uuid',
                Rule::exists('message_templates', 'id')->where('user_id', $userId)->where('is_active', true),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'invoice_ids.required' => 'Please select at least one invoice.',
            'invoice_ids.min' => 'Please select at least one invoice.',
            'invoice_ids.*.exists' => 'One or more selected invoices do not exist or do not belong to you.',
            'channel.required' => 'Please select a channel (Email, WhatsApp, or SMS).',
            'channel.in' => 'Invalid channel. Please select Email, WhatsApp, or SMS.',
            'scheduled_at.required' => 'Please select a scheduled date and time.',
            'scheduled_at.date_format' => 'The scheduled date format is invalid. Please use YYYY-MM-DDTHH:mm format.',
            'scheduled_at.after' => 'The scheduled date must be in the future.',
            'email_template_id.required' => 'Please select an email template when using the email channel.',
            'email_template_id.exists' => 'The selected email template does not exist or is not active.',
            'message_template_id.required' => 'Please select a message template when using WhatsApp or SMS channel.',
            'message_template_id.exists' => 'The selected message template does not exist or is not active.',
        ];
    }
}
