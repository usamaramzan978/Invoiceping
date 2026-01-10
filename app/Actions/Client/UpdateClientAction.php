<?php

declare(strict_types=1);

namespace App\Actions\Client;

use App\Models\Client;

final class UpdateClientAction
{
    public function handle(Client $client, array $data): Client
    {
        $client->update($data);

        return $client;
    }
}
