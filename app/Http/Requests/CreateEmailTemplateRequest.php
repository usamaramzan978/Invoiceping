<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreateEmailTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $userId = $this->user()?->id;

        if ($userId === null) {
            return [];
        }

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('email_templates', 'name')
                    ->where('user_id', $userId)
                    ->whereNull('deleted_at'),
            ],
            'subject' => ['nullable', 'string', 'max:255'],
            'template_json' => ['required', 'json'],
            'template_html' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'is_default' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'template name',
            'template_json' => 'template data',
            'template_html' => 'template HTML',
            'is_default' => 'default status',
            'is_active' => 'active status',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Template name is required.',
            'name.unique' => 'A template with this name already exists.',
            'template_json.required' => 'Template data is required.',
            'template_json.json' => 'Template data must be valid JSON.',
        ];
    }
}
