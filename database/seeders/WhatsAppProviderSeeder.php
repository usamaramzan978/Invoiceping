<?php

namespace Database\Seeders;

use App\Enums\WhatsAppProviderType;
use App\Models\User;
use App\Models\WhatsAppProvider;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WhatsAppProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a demo user
        $user = User::first();

        // WhatsApp Cloud API Provider
        WhatsAppProvider::create([
            'user_id' => $user->id,
            'name' => 'WhatsApp Cloud API - Production',
            'type' => WhatsAppProviderType::WHATSAPP_CLOUD_API->value,
            'access_token' => 'EAABsbCS1iHgBAIZBwZALOZCccT7QYYZBIZCZBQw3RJBzR9qT8VhkZBZB9SBZCZCQ7',
            'phone_number_id' => '104567891234567',
            'business_account_id' => '123456789012345',
            'app_id' => '1234567890123456',
            'app_secret' => 'abcdef1234567890abcdef1234567890',
            'is_active' => true,
            'is_default' => true,
            'notes' => 'Main production WhatsApp Cloud API account',
        ]);

        // WhatsApp Cloud API Provider (Staging)
        WhatsAppProvider::create([
            'user_id' => $user->id,
            'name' => 'WhatsApp Cloud API - Staging',
            'type' => WhatsAppProviderType::WHATSAPP_CLOUD_API->value,
            'access_token' => 'EAABsbCS1iHgBAHZBwZALOZCccT7QYYZBIZCZBQw3RJBzR9qT8VhkZBZB9SBZCZCQ8',
            'phone_number_id' => '104567891234568',
            'business_account_id' => '123456789012346',
            'app_id' => '1234567890123457',
            'app_secret' => 'bcdefg2345678901bcdefg2345678901',
            'is_active' => true,
            'is_default' => false,
            'notes' => 'Staging environment for testing',
        ]);

        // Twilio Provider
        WhatsAppProvider::create([
            'user_id' => $user->id,
            'name' => 'Twilio WhatsApp',
            'type' => WhatsAppProviderType::TWILIO->value,
            'account_sid' => 'ACaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'auth_token' => 'your_auth_token_here_12345678901234',
            'from_phone_number' => '+14155552671',
            'whatsapp_sandbox_number' => '+1415503667701',
            'is_active' => true,
            'is_default' => false,
            'notes' => 'Twilio sandbox for development',
        ]);

        // Vonage Provider
        WhatsAppProvider::create([
            'user_id' => $user->id,
            'name' => 'Vonage WhatsApp',
            'type' => WhatsAppProviderType::VONAGE->value,
            'api_key' => 'aabbccdd11223344aabbccdd11223344',
            'api_secret' => 'eeff5566aabbccdd77889900aabbccdd',
            'from_number' => '1234567890',
            'application_id' => '12ab3456-abcd-1234-abcd-1234567890ab',
            'is_active' => false,
            'is_default' => false,
            'notes' => 'Vonage account - currently inactive',
        ]);
    }
}
