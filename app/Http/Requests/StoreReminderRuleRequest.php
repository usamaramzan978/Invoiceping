<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreReminderRuleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'is_default' => ['boolean'],
            'steps' => ['required', 'array', 'min:1'],
            'steps.*.reminder_type' => ['required', 'string'],
            'steps.*.offset_days' => ['required', 'integer'],
            'steps.*.channels' => ['required', 'array'],
            'steps.*.channels.*.channel' => ['required', 'string'],
            'steps.*.channels.*.message_template_id' => ['nullable', 'uuid'],
            'steps.*.channels.*.email_template_id' => ['nullable', 'integer'],
            'steps.*.channels.*.include_pdf' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'rule name',
            'is_default' => 'default rule',
            'steps' => 'reminder steps',
            'steps.*.reminder_type' => 'reminder type',
            'steps.*.offset_days' => 'offset days',
            'steps.*.channels' => 'channels',
            'steps.*.channels.*.channel' => 'channel',
            'steps.*.channels.*.message_template_id' => 'message template',
            'steps.*.channels.*.email_template_id' => 'email template',
            'steps.*.channels.*.include_pdf' => 'include PDF',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The rule name is required.',
            'name.max' => 'The rule name cannot exceed 255 characters.',
            'steps.required' => 'At least one reminder step is required.',
            'steps.min' => 'At least one reminder step is required.',
            'steps.*.reminder_type.required' => 'The reminder type is required for each step.',
            'steps.*.offset_days.required' => 'The offset days is required for each step.',
            'steps.*.offset_days.integer' => 'The offset days must be a number.',
            'steps.*.channels.required' => 'At least one channel is required for each step.',
            'steps.*.channels.*.channel.required' => 'The channel type is required.',
            'steps.*.channels.*.message_template_id.uuid' => 'The message template ID must be a valid UUID.',
            'steps.*.channels.*.email_template_id.integer' => 'The email template ID must be a valid number.',
            'steps.*.channels.*.include_pdf.boolean' => 'The include PDF field must be true or false.',
        ];
    }
}
