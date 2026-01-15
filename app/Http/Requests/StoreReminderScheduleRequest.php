<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\BusinessProfile;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreReminderScheduleRequest extends FormRequest
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
        $user = $this->user();
        $userId = $user->id;
        /** @var BusinessProfile|null $business */
        $business = $user->business;
        $businessId = $business?->id;

        return [
            // Required fields
            'invoice_ids' => ['required', 'array', 'min:1'],
            'invoice_ids.*' => [
                'required',
                'uuid',
                Rule::exists('invoices', 'id')->where(fn ($query) => $query->where('business_id', $businessId)),
            ],
            'source_type' => ['required', 'in:manual,rule'],
            'scheduled_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:now'],

            // Manual scheduling - channel and template required
            'channel' => [
                'nullable',
                Rule::requiredIf($this->input('source_type') === 'manual'),
                'in:email,whatsapp,sms',
            ],
            'email_template_id' => [
                'nullable',
                Rule::requiredIf(fn (): bool => $this->input('source_type') === 'manual' && $this->input('channel') === 'email'),
                'integer',
                Rule::exists('email_templates', 'id')->where('user_id', $userId)->where('is_active', true),
            ],
            'include_pdf' => ['sometimes', 'boolean'],
            'message_template_id' => [
                'nullable',
                Rule::requiredIf(fn (): bool => $this->input('source_type') === 'manual'
                    && in_array($this->input('channel'), ['whatsapp', 'sms'])),
                'uuid',
                Rule::exists('message_templates', 'id')->where('user_id', $userId)->where('is_active', true),
            ],

            // Rule scheduling - rule required
            'reminder_rule_id' => [
                'nullable',
                Rule::requiredIf($this->input('source_type') === 'rule'),
                'uuid',
                Rule::exists('reminder_rules', 'id')->where('user_id', $userId),
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
            'source_type.required' => 'Please select a scheduling type (Manual or Rule).',
            'source_type.in' => 'Invalid scheduling type. Please select either Manual or Rule.',
            'scheduled_at.required' => 'Please select a scheduled date and time.',
            'scheduled_at.date_format' => 'The scheduled date format is invalid. Please use YYYY-MM-DDTHH:mm format.',
            'scheduled_at.after' => 'The scheduled date must be in the future.',
            'channel.required_if' => 'Please select a channel (Email, WhatsApp, or SMS) for manual scheduling.',
            'channel.in' => 'Invalid channel. Please select Email, WhatsApp, or SMS.',
            'email_template_id.required_if' => 'Please select an email template when using the email channel.',
            'email_template_id.exists' => 'The selected email template does not exist or is not active.',
            'message_template_id.required_if' => 'Please select a message template when using WhatsApp or SMS channel.',
            'message_template_id.exists' => 'The selected message template does not exist or is not active.',
            'reminder_rule_id.required_if' => 'Please select a reminder rule when using rule-based scheduling.',
            'reminder_rule_id.exists' => 'The selected reminder rule does not exist or does not belong to you.',
        ];
    }
}
