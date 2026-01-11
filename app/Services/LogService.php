<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;

/**
 * Centralized logging service for all application logs.
 * Handles message sending logs, errors, and statistics.
 */
final class LogService
{
    /**
     * Log a successful message send.
     *
     * @param  string  $channel  Channel type: 'email', 'whatsapp', 'sms'
     * @param  string  $recipient  Recipient address
     * @param  string  $content  Message content
     * @param  string|null  $subject  Email subject (for email channel)
     * @param  Invoice|null  $invoice  Related invoice
     * @param  array<string, mixed>  $metadata  Additional metadata
     * @param  string|null  $userId  User ID (defaults to authenticated user)
     */
    public function logMessageSent(
        string $channel,
        string $recipient,
        string $content,
        ?string $subject = null,
        ?Invoice $invoice = null,
        array $metadata = [],
        ?string $userId = null
    ): Log {
        return Log::query()->create([
            'user_id' => $userId ?? Auth::id(),
            'invoice_id' => $invoice?->id,
            'type' => $channel,
            'status' => 'success',
            'channel' => $channel,
            'recipient' => $recipient,
            'subject' => $subject,
            'content' => $content,
            'metadata' => array_merge($metadata, [
                'message_length' => mb_strlen($content),
                'sent_at' => now()->toIso8601String(),
            ]),
            'sent_at' => now(),
        ]);
    }

    /**
     * Log a failed message send.
     *
     * @param  string  $channel  Channel type: 'email', 'whatsapp', 'sms'
     * @param  string  $recipient  Recipient address
     * @param  string  $errorMessage  Error message
     * @param  Invoice|null  $invoice  Related invoice
     * @param  array<string, mixed>  $metadata  Additional metadata
     * @param  string|null  $userId  User ID (defaults to authenticated user)
     */
    public function logMessageFailed(
        string $channel,
        string $recipient,
        string $errorMessage,
        ?Invoice $invoice = null,
        array $metadata = [],
        ?string $userId = null
    ): Log {
        return Log::query()->create([
            'user_id' => $userId ?? Auth::id(),
            'invoice_id' => $invoice?->id,
            'type' => $channel,
            'status' => 'failed',
            'channel' => $channel,
            'recipient' => $recipient,
            'error_message' => $errorMessage,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Log an error.
     *
     * @param  string  $message  Error message
     * @param  array<string, mixed>  $context  Additional context
     * @param  Invoice|null  $invoice  Related invoice
     */
    public function logError(
        string $message,
        array $context = [],
        ?Invoice $invoice = null
    ): Log {
        return Log::query()->create([
            'user_id' => Auth::id(),
            'invoice_id' => $invoice?->id,
            'type' => 'error',
            'status' => 'error',
            'error_message' => $message,
            'metadata' => $context,
        ]);
    }

    /**
     * Log an info message.
     *
     * @param  string  $message  Info message
     * @param  array<string, mixed>  $context  Additional context
     */
    public function logInfo(
        string $message,
        array $context = []
    ): Log {
        return Log::query()->create([
            'user_id' => Auth::id(),
            'type' => 'info',
            'status' => 'success',
            'content' => $message,
            'metadata' => $context,
        ]);
    }

    /**
     * Log a warning.
     *
     * @param  string  $message  Warning message
     * @param  array<string, mixed>  $context  Additional context
     * @param  string|null  $userId  User ID (defaults to authenticated user)
     */
    public function logWarning(
        string $message,
        array $context = [],
        ?string $userId = null
    ): Log {
        return Log::query()->create([
            'user_id' => $userId ?? Auth::id(),
            'type' => 'warning',
            'status' => 'warning',
            'content' => $message,
            'metadata' => $context,
        ]);
    }

    /**
     * Get statistics for a user.
     *
     * @param  string|null  $userId  User ID (defaults to current user)
     * @return array<string, mixed>
     */
    public function getStatistics(?string $userId = null): array
    {
        $userId ??= Auth::id();
        $query = Log::query()->where('user_id', $userId);
        $messageQuery = (clone $query)->whereIn('type', ['email', 'whatsapp', 'sms']);

        $totalMessages = $messageQuery->count();
        $successfulMessages = (clone $messageQuery)->where('status', 'success')->count();
        $failedMessages = (clone $messageQuery)->where('status', 'failed')->count();

        // Calculate success rate
        $successRate = $totalMessages > 0 ? round(($successfulMessages / $totalMessages) * 100, 1) : 0;

        // Get channel breakdown
        $emailCount = (clone $query)->where('channel', 'email')->where('status', 'success')->count();
        $whatsappCount = (clone $query)->where('channel', 'whatsapp')->where('status', 'success')->count();
        $smsCount = (clone $query)->where('channel', 'sms')->where('status', 'success')->count();

        // Find most used channel
        $channelCounts = [
            'email' => $emailCount,
            'whatsapp' => $whatsappCount,
            'sms' => $smsCount,
        ];
        $mostUsedChannel = array_keys($channelCounts, max($channelCounts))[0] ?? 'email';
        $mostUsedChannelCount = max($channelCounts);

        // Calculate average messages per day (this month)
        $thisMonthMessages = (clone $messageQuery)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'success')
            ->count();
        now()->daysInMonth;
        $currentDay = now()->day;
        $avgPerDay = $currentDay > 0 ? round($thisMonthMessages / $currentDay, 1) : 0;

        return [
            'total_messages' => $totalMessages,
            'emails_sent' => $emailCount,
            'whatsapp_sent' => $whatsappCount,
            'sms_sent' => $smsCount,
            'failed_messages' => $failedMessages,
            'errors' => (clone $query)->where('type', 'error')->count(),
            'today_messages' => (clone $messageQuery)
                ->whereDate('created_at', today())
                ->where('status', 'success')
                ->count(),
            'this_week_messages' => (clone $messageQuery)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->where('status', 'success')
                ->count(),
            'this_month_messages' => $thisMonthMessages,
            'success_rate' => $successRate,
            'successful_messages' => $successfulMessages,
            'success_vs_failed_ratio' => $failedMessages > 0 ? round($successfulMessages / $failedMessages, 2) : (max($successfulMessages, 0)),
            'most_used_channel' => $mostUsedChannel,
            'most_used_channel_count' => $mostUsedChannelCount,
            'avg_messages_per_day' => $avgPerDay,
        ];
    }
}
