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
 * @property string $uuid
 * @property string $name
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 */
final class Dashboard extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $guarded = [
        'id',
        'uuid',
    ];

    protected static function booted(): void
    {
        self::creating(function (Dashboard $dashboard): void {
            if (empty($dashboard->uuid)) {
                $dashboard->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user that owns the dashboard.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the widgets for the dashboard.
     */
    public function widgets(): HasMany
    {
        return $this->hasMany(Widget::class)->orderBy('sort_order');
    }
}
