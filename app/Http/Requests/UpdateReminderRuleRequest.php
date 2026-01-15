<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateReminderRuleRequest extends FormRequest
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
}
