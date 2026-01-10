<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\BusinessProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

final class BusinessProfileSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->first();
        if (! $user) {
            return;
        }

        BusinessProfile::query()->create([
            'user_id' => $user->id,
            'business_name' => 'Cascade Test Business',
            'whatsapp_number' => '+1234567890',
            'email' => 'business@example.com',
            'address' => '123 Test Lane, Test City',
            'currency' => 'USD',
            'timezone' => 'UTC',
        ]);
    }
}
