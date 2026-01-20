<?php

declare(strict_types=1);

namespace App\Actions\SupportTicket;

use App\Enums\SupportTicketStatus;
use App\Models\SupportTicket;

final class CreateSupportTicketAction
{
    public function handle(array $data): SupportTicket
    {
        $data['status'] ??= SupportTicketStatus::OPEN->value;

        return SupportTicket::query()->create($data);
    }
}

