<?php

declare(strict_types=1);

namespace App\Actions\SupportTicket;

use App\Models\SupportTicket;

final class DeleteSupportTicketAction
{
    public function handle(SupportTicket $ticket): void
    {
        $ticket->delete();
    }
}

