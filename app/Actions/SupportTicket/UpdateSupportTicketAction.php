<?php

declare(strict_types=1);

namespace App\Actions\SupportTicket;

use App\Enums\SupportTicketStatus;
use App\Models\SupportTicket;

final class UpdateSupportTicketAction
{
    public function handle(SupportTicket $ticket, array $data): SupportTicket
    {
        // If status is being set to resolved or closed, set timestamps
        if (isset($data['status'])) {
            $status = SupportTicketStatus::from($data['status']);

            if ($status === SupportTicketStatus::RESOLVED && ! $ticket->resolved_at) {
                $data['resolved_at'] = now();
            }

            if ($status === SupportTicketStatus::CLOSED && ! $ticket->closed_at) {
                $data['closed_at'] = now();
            }
        }

        $ticket->update($data);

        return $ticket;
    }
}

