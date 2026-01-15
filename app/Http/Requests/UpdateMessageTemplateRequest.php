<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateMessageTemplateRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100'],
            'channel' => ['required', 'string', Rule::in(['whatsapp', 'sms'])],
            'content' => ['required', 'string', 'max:5000'],
            'is_default' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'template name',
            'channel' => 'channel',
            'content' => 'message content',
            'is_default' => 'default template',
            'is_active' => 'active status',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The template name is required.',
            'name.max' => 'The template name cannot exceed 100 characters.',
            'channel.required' => 'Please select a channel (WhatsApp or SMS).',
            'channel.in' => 'The channel must be either WhatsApp or SMS.',
            'content.required' => 'The message content is required.',
            'content.max' => 'The message content cannot exceed 5000 characters.',
            'is_default.required' => 'Please specify if this is a default template.',
            'is_default.boolean' => 'The default template field must be true or false.',
            'is_active.required' => 'Please specify if this template is active.',
            'is_active.boolean' => 'The active status field must be true or false.',
        ];
    }
}
