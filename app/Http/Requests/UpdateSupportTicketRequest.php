<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateSupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => ['sometimes', 'required', 'string', 'max:200'],
            'message' => ['sometimes', 'required', 'string'],
            'priority' => ['sometimes', 'required', 'in:low,medium,high,urgent'],
            'status' => ['sometimes', 'required', 'in:open,in_progress,resolved,closed'],
            'attachment' => ['sometimes', 'nullable', 'file', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:4096'],
        ];
    }
}

