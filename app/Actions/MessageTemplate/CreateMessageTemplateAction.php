<?php

declare(strict_types=1);

namespace App\Actions\MessageTemplate;

use App\Models\MessageTemplates;
use Illuminate\Support\Facades\DB;

final class CreateMessageTemplateAction
{
    public function execute(string $userId, array $data): MessageTemplates
    {
        return DB::transaction(function () use ($userId, $data): MessageTemplates {
            // If setting as default, unset other defaults for this channel
            if ($data['is_default']) {
                MessageTemplates::query()
                    ->where('user_id', $userId)
                    ->where('channel', $data['channel'])
                    ->update(['is_default' => false]);
            }

            return MessageTemplates::query()->create([
                'user_id' => $userId,
                'name' => $data['name'],
                'channel' => $data['channel'],
                'content' => $data['content'],
                'is_default' => $data['is_default'],
                'is_active' => $data['is_active'],
            ]);
        });
    }
}
