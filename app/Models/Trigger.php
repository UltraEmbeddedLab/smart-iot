<?php declare(strict_types=1);

namespace App\Models;

use App\Enums\TriggerActionType;
use App\Enums\TriggerOperator;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_id
 * @property int $cloud_variable_id
 * @property string $uuid
 * @property string $name
 * @property TriggerOperator $operator
 * @property string $value
 * @property TriggerActionType $action_type
 * @property array<string, mixed>|null $action_config
 * @property bool $is_active
 * @property CarbonImmutable|null $last_triggered_at
 * @property int $cooldown_seconds
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property-read User $user
 * @property-read CloudVariable $cloudVariable
 */
final class Trigger extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $guarded = [
        'id',
        'uuid',
    ];

    protected static function booted(): void
    {
        self::creating(function (Trigger $trigger): void {
            if (empty($trigger->uuid)) {
                $trigger->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'operator' => TriggerOperator::class,
            'action_type' => TriggerActionType::class,
            'action_config' => 'array',
            'is_active' => 'boolean',
            'last_triggered_at' => 'datetime',
            'cooldown_seconds' => 'integer',
        ];
    }

    /**
     * Check if the trigger is currently within its cooldown period.
     */
    public function isOnCooldown(): bool
    {
        if ($this->cooldown_seconds === 0 || $this->last_triggered_at === null) {
            return false;
        }

        return $this->last_triggered_at->addSeconds($this->cooldown_seconds)->isFuture();
    }

    /**
     * Get the user that owns the trigger.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cloud variable this trigger monitors.
     */
    public function cloudVariable(): BelongsTo
    {
        return $this->belongsTo(CloudVariable::class);
    }
}
