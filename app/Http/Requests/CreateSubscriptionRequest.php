<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreateSubscriptionRequest extends FormRequest
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
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'plan_id' => ['required', 'uuid', 'exists:subscription_plans,id'],
            'billing_cycle' => ['required', Rule::in(['monthly', 'yearly'])],
            'trial_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'starts_at' => ['nullable', 'date', 'after_or_equal:today'],
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
            'plan_id.required' => 'Please select a subscription plan.',
            'plan_id.exists' => 'The selected plan does not exist.',
            'billing_cycle.required' => 'Please select a billing cycle.',
            'billing_cycle.in' => 'Invalid billing cycle selected.',
        ];
    }
}
