<?php

declare(strict_types=1);

namespace App\Actions\MessageTemplate;

use App\Models\MessageTemplates;
use Illuminate\Support\Facades\DB;

final class UpdateMessageTemplateAction
{
    public function execute(MessageTemplates $template, array $data): MessageTemplates
    {
        return DB::transaction(function () use ($template, $data): MessageTemplates {
            // If setting as default, unset other defaults for this channel
            if ($data['is_default']) {
                MessageTemplates::query()
                    ->where('user_id', $template->user_id)
                    ->where('channel', $data['channel'])
                    ->where('id', '!=', $template->id)
                    ->update(['is_default' => false]);
            }

            $template->update([
                'name' => $data['name'],
                'channel' => $data['channel'],
                'content' => $data['content'],
                'is_default' => $data['is_default'],
                'is_active' => $data['is_active'],
            ]);

            return $template;
        });
    }
}
