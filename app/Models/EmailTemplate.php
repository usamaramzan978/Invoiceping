<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\EmailTemplateFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $user_id
 * @property string $name
 * @property string|null $subject
 * @property array<string, mixed> $template_json
 * @property string|null $template_html
 * @property string|null $category
 * @property bool $is_default
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
final class EmailTemplate extends Model
{
    /** @use HasFactory<EmailTemplateFactory> */
    use HasFactory;
    use HasUuids;

    use SoftDeletes;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'template_json' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if template is default
     */
    public function isDefault(): bool
    {
        return $this->is_default;
    }

    /**
     * Check if template is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Scope query to only active templates
     *
     * @param  Builder<EmailTemplate>  $query
     * @return Builder<EmailTemplate>
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to only default templates
     *
     * @param  Builder<EmailTemplate>  $query
     * @return Builder<EmailTemplate>
     */
    #[Scope]
    protected function default(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope query to templates for a specific user
     *
     * @param  Builder<EmailTemplate>  $query
     * @return Builder<EmailTemplate>
     */
    #[Scope]
    protected function forUser(Builder $query, string $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
