<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->create([
            'name' => 'Cascade Admin',
            'email' => 'admin@cascade.test',
            'password' => Hash::make('password123'),
        ]);
    }
}
