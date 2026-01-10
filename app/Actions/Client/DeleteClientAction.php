<?php

declare(strict_types=1);

namespace App\Actions\Client;

use App\Models\Client;

final class DeleteClientAction
{
    public function handle(Client $client): void
    {
        $client->delete();
    }
}
