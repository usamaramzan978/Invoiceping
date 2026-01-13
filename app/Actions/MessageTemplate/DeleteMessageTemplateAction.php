<?php

declare(strict_types=1);

namespace App\Actions\MessageTemplate;

use App\Models\MessageTemplates;
use InvalidArgumentException;

final class DeleteMessageTemplateAction
{
    public function execute(MessageTemplates $template): bool
    {
        // Prevent deletion of default templates
        if ($template->is_default) {
            throw new InvalidArgumentException('Cannot delete a default template. Set another template as default first.');
        }

        return $template->delete();
    }
}

