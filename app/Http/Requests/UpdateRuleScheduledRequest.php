<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateRuleScheduledRequest extends FormRequest
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
                Rule::exists('invoices', 'id')->where(fn ($query) => $query->where('business_id', $businessId)),
            ],
            'reminder_rule_id' => [
                'required',
                'uuid',
                Rule::exists('reminder_rules', 'id')->where('user_id', $userId),
            ],
            'scheduled_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:now'],
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
            'reminder_rule_id.required' => 'Please select a reminder rule.',
            'reminder_rule_id.exists' => 'The selected reminder rule does not exist or does not belong to you.',
            'scheduled_at.required' => 'Please select a scheduled date and time.',
            'scheduled_at.date_format' => 'The scheduled date format is invalid. Please use YYYY-MM-DDTHH:mm format.',
            'scheduled_at.after' => 'The scheduled date must be in the future.',
        ];
    }
}
