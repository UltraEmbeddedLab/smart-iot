<?php declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $thing_id
 * @property string $key
 * @property string $value
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 */
final class ThingTag extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $guarded = [
        'id',
    ];

    /**
     * Get the thing that owns the tag.
     */
    public function thing(): BelongsTo
    {
        return $this->belongsTo(Thing::class);
    }
}
