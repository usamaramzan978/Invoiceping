<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Client and Invoice Info
            'client_id' => ['required', 'exists:clients,id'],
            'invoice_number' => ['required', 'string', 'max:50'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'currency' => ['required', 'string', 'max:10'],

            // Financial Fields
            'discount_type' => ['nullable', 'in:fixed,percent'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],

            // Notes
            'notes' => ['nullable', 'string', 'max:5000'],

            // Items
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string', 'max:500'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'client_id' => 'client',
            'invoice_number' => 'invoice number',
            'issue_date' => 'issue date',
            'due_date' => 'due date',
            'items.*.name' => 'product name',
            'items.*.description' => 'product description',
            'items.*.quantity' => 'quantity',
            'items.*.unit_price' => 'unit price',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'items.required' => 'At least one invoice item is required.',
            'items.min' => 'At least one invoice item is required.',
            'items.*.name.required' => 'Product name is required for all items.',
            'items.*.quantity.required' => 'Quantity is required for all items.',
            'items.*.quantity.min' => 'Quantity must be at least 0.01.',
            'items.*.unit_price.required' => 'Unit price is required for all items.',
        ];
    }
}
