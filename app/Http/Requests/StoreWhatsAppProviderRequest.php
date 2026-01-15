<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\WhatsAppProviderType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreWhatsAppProviderRequest extends FormRequest
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
        $type = $this->input('type');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(WhatsAppProviderType::class)],
            'is_active' => ['sometimes', 'boolean'],
            'is_default' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        // Add type-specific credential validation
        $providerType = WhatsAppProviderType::tryFrom($type ?? '');
        if ($providerType === WhatsAppProviderType::WHATSAPP_CLOUD_API) {
            $rules['access_token'] = ['required', 'string'];
            $rules['phone_number_id'] = ['required', 'string'];
            $rules['business_account_id'] = ['required', 'string'];
            $rules['app_id'] = ['nullable', 'string'];
            $rules['app_secret'] = ['nullable', 'string'];
        } elseif ($providerType === WhatsAppProviderType::TWILIO) {
            $rules['account_sid'] = ['required', 'string'];
            $rules['auth_token'] = ['required', 'string'];
            $rules['from_phone_number'] = ['required', 'string'];
            $rules['whatsapp_sandbox_number'] = ['nullable', 'string'];
        } elseif ($providerType === WhatsAppProviderType::VONAGE) {
            $rules['api_key'] = ['required', 'string'];
            $rules['api_secret'] = ['required', 'string'];
            $rules['from_number'] = ['required', 'string'];
            $rules['application_id'] = ['nullable', 'string'];
        }

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'provider name',
            'type' => 'provider type',
            'access_token' => 'access token',
            'phone_number_id' => 'phone number ID',
            'business_account_id' => 'business account ID',
            'account_sid' => 'account SID',
            'auth_token' => 'auth token',
            'from_phone_number' => 'from phone number',
            'api_key' => 'API key',
            'api_secret' => 'API secret',
            'from_number' => 'from number',
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
            'name.required' => 'Provider name is required.',
            'type.required' => 'Provider type is required.',
            'type.in' => 'Invalid provider type selected.',
            'access_token.required' => 'Access token is required for WhatsApp Cloud API.',
            'phone_number_id.required' => 'Phone number ID is required for WhatsApp Cloud API.',
            'business_account_id.required' => 'Business account ID is required for WhatsApp Cloud API.',
            'account_sid.required' => 'Account SID is required for Twilio.',
            'auth_token.required' => 'Auth token is required for Twilio.',
            'from_phone_number.required' => 'From phone number is required for Twilio.',
            'api_key.required' => 'API key is required for Vonage.',
            'api_secret.required' => 'API secret is required for Vonage.',
            'from_number.required' => 'From number is required for Vonage.',
        ];
    }
}
