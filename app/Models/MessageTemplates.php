<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\TemplateVariableService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $user_id
 * @property string $name
 * @property string $channel
 * @property string|null $type
 * @property string $content
 * @property bool $is_default
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read BusinessProfile $business
 * @property-read User $user
 * @property-read Collection<int, ReminderSchedule> $reminderSchedules
 *
 * @method static Builder|MessageTemplates active()
 * @method static Builder|MessageTemplates default()
 * @method static Builder|MessageTemplates forBusiness(string $businessId)
 * @method static Builder|MessageTemplates forChannel(string $channel)
 * @method static Builder|MessageTemplates newModelQuery()
 * @method static Builder|MessageTemplates newQuery()
 * @method static Builder|MessageTemplates onlyTrashed()
 * @method static Builder|MessageTemplates query()
 * @method static Builder|MessageTemplates withTrashed()
 * @method static Builder|MessageTemplates withoutTrashed()
 */
final class MessageTemplates extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'message_templates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'channel',
        'type',
        'content',
        'is_default',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the reminder schedules for the message template.
     *
     * @return HasMany<ReminderSchedule>
     */
    public function reminderSchedules(): HasMany
    {
        return $this->hasMany(ReminderSchedule::class);
    }

    /**
     * Check if the template is the default for its channel.
     */
    public function isDefault(): bool
    {
        return $this->is_default;
    }

    /**
     * Check if the template is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if the template is for WhatsApp.
     */
    public function isWhatsApp(): bool
    {
        return $this->channel === 'whatsapp';
    }

    /**
     * Check if the template is for SMS.
     */
    public function isSMS(): bool
    {
        return $this->channel === 'sms';
    }

    /**
     * Replace template variables with actual values from an invoice.
     */
    public function processContent(Invoice $invoice): string
    {
        $variableService = app(TemplateVariableService::class);

        return $variableService->replaceVariables($this->content, $invoice);
    }

    /**
     * Get character count (useful for SMS).
     */
    public function getCharacterCount(): int
    {
        return mb_strlen($this->content);
    }

    /**
     * Get SMS parts count (160 chars per SMS).
     */
    public function getSMSPartsCount(): int
    {
        return (int) ceil($this->getCharacterCount() / 160);
    }

    /**
     * Scope a query to only include active templates.
     */
    protected function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include default templates.
     */
    protected function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope a query to only include templates for a specific channel.
     *
     * @param  string  $channel  Channel type: 'whatsapp', 'sms'
     */
    protected function scopeForChannel(Builder $query, string $channel): Builder
    {
        return $query->where('channel', $channel);
    }
}
