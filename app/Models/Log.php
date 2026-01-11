<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string|null $user_id
 * @property string|null $invoice_id
 * @property string $type
 * @property string $status
 * @property string|null $channel
 * @property string|null $recipient
 * @property string|null $subject
 * @property string|null $content
 * @property string|null $error_message
 * @property array<string, mixed>|null $metadata
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property Carbon|null $sent_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User|null $user
 * @property-read Invoice|null $invoice
 */
final class Log extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'user_id',
        'invoice_id',
        'type',
        'status',
        'channel',
        'recipient',
        'subject',
        'content',
        'error_message',
        'metadata',
        'ip_address',
        'user_agent',
        'sent_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the log.
     *
     * @return BelongsTo<User, Log>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the invoice associated with the log.
     *
     * @return BelongsTo<Invoice, Log>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Check if log is successful.
     */
    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if log is failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Get formatted recipient (masked for privacy).
     */
    public function getMaskedRecipient(): ?string
    {
        if (! $this->recipient) {
            return null;
        }

        if (filter_var($this->recipient, FILTER_VALIDATE_EMAIL)) {
            // Mask email: john@example.com -> j***@example.com
            $parts = explode('@', $this->recipient);
            if (count($parts) === 2) {
                $name = $parts[0];
                $domain = $parts[1];

                return mb_substr($name, 0, 1).str_repeat('*', max(1, mb_strlen($name) - 1)).'@'.$domain;
            }
        } elseif (mb_strlen($this->recipient) > 6) {
            // Mask phone: +923001234567 -> +92***1234567
            return mb_substr($this->recipient, 0, 3).str_repeat('*', mb_strlen($this->recipient) - 6).mb_substr($this->recipient, -3);
        }

        return $this->recipient;
    }

    /**
     * Scope to filter by user.
     */
    #[Scope]
    protected function forUser(Builder $query, string $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by type.
     */
    #[Scope]
    protected function ofType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by status.
     */
    #[Scope]
    protected function withStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by channel.
     */
    #[Scope]
    protected function forChannel(Builder $query, string $channel): Builder
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope to get successful messages.
     */
    #[Scope]
    protected function successful(Builder $query): Builder
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope to get failed messages.
     */
    #[Scope]
    protected function failed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to get messages sent today.
     */
    #[Scope]
    protected function today(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope to get messages sent this week.
     */
    #[Scope]
    protected function thisWeek(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    /**
     * Scope to get messages sent this month.
     */
    #[Scope]
    protected function thisMonth(Builder $query): Builder
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }
}
