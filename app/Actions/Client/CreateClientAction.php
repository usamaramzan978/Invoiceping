<?php

declare(strict_types=1);

namespace App\Actions\Client;

use App\Models\Client;

final class CreateClientAction
{
    public function handle(array $data): Client
    {
        return Client::query()->create($data);
    }
}
