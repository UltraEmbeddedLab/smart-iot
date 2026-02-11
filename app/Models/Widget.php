<?php declare(strict_types=1);

namespace App\Models;

use App\Enums\WidgetType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $dashboard_id
 * @property string $uuid
 * @property WidgetType $type
 * @property string $name
 * @property int|null $cloud_variable_id
 * @property array<string, mixed>|null $options
 * @property int $x
 * @property int $y
 * @property int $width
 * @property int $height
 * @property int $sort_order
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 */
final class Widget extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $guarded = [
        'id',
        'uuid',
    ];

    protected static function booted(): void
    {
        self::creating(function (Widget $widget): void {
            if (empty($widget->uuid)) {
                $widget->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => WidgetType::class,
            'options' => 'array',
            'x' => 'integer',
            'y' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the dashboard that owns the widget.
     */
    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class);
    }

    /**
     * Get the cloud variable associated with the widget.
     */
    public function cloudVariable(): BelongsTo
    {
        return $this->belongsTo(CloudVariable::class);
    }
}
