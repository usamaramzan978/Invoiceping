<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreSupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['nullable', 'uuid', 'exists:clients,id'],
            'invoice_id' => ['nullable', 'uuid', 'exists:invoices,id'],
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'attachment' => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:4096'],
        ];
    }
}

