<?php declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $device_id
 * @property string $uuid
 * @property string $name
 * @property string $timezone
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 */
final class Thing extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $guarded = [
        'id',
        'uuid',
    ];

    protected static function booted(): void
    {
        self::creating(function (Thing $thing): void {
            if (empty($thing->uuid)) {
                $thing->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user that owns the thing.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the device associated with the thing.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Get the tags for the thing.
     */
    public function tags(): HasMany
    {
        return $this->hasMany(ThingTag::class);
    }
}
