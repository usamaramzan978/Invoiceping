<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateManualSubscriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Add admin check here if needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'plan_id' => ['nullable', 'uuid', 'exists:subscription_plans,id'],
            'billing_cycle' => ['nullable', Rule::in(['monthly', 'yearly'])],
            'status' => ['nullable', Rule::in(['active', 'trialing', 'canceled', 'expired', 'past_due'])],
            'trial_ends_at' => ['nullable', 'date'],
            'renews_at' => ['nullable', 'date', 'after:now'],
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
            'plan_id.exists' => 'The selected plan does not exist.',
            'billing_cycle.in' => 'Invalid billing cycle selected.',
            'status.in' => 'Invalid status selected.',
            'renews_at.after' => 'Renewal date must be in the future.',
        ];
    }
}
