<?php declare(strict_types=1);

namespace App\Models;

use App\Enums\DeviceType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $thing_id
 * @property string $name
 * @property string $code
 * @property DeviceType $device_type
 * @property array<string, mixed>|null $parameters
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property-read Thing $thing
 */
final class Firmware extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $guarded = [
        'id',
    ];

    protected $table = 'firmware';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'device_type' => DeviceType::class,
            'parameters' => 'array',
        ];
    }

    /**
     * Get the thing this firmware belongs to.
     *
     * @return BelongsTo<Thing, $this>
     */
    public function thing(): BelongsTo
    {
        return $this->belongsTo(Thing::class);
    }
}
