<?php declare(strict_types=1);

namespace App\Models;

use App\Enums\CloudVariableType;
use App\Enums\VariablePermission;
use App\Enums\VariableUpdatePolicy;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $thing_id
 * @property string $uuid
 * @property string $name
 * @property string $variable_name
 * @property CloudVariableType $type
 * @property VariablePermission $permission
 * @property VariableUpdatePolicy $update_policy
 * @property string|null $update_parameter
 * @property string|null $min_value
 * @property string|null $max_value
 * @property array<string, mixed>|null $last_value
 * @property CarbonImmutable|null $value_updated_at
 * @property bool $persist
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property-read string $declaration
 */
final class CloudVariable extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $guarded = [
        'id',
        'uuid',
    ];

    protected static function booted(): void
    {
        self::creating(function (CloudVariable $variable): void {
            if (empty($variable->uuid)) {
                $variable->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CloudVariableType::class,
            'permission' => VariablePermission::class,
            'update_policy' => VariableUpdatePolicy::class,
            'update_parameter' => 'decimal:2',
            'min_value' => 'decimal:2',
            'max_value' => 'decimal:2',
            'last_value' => 'array',
            'value_updated_at' => 'datetime',
            'persist' => 'boolean',
        ];
    }

    /**
     * Get the C++ declaration for this variable.
     *
     * @return Attribute<string, never>
     */
    protected function declaration(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->type->declarationType().' '.$this->variable_name.';',
        );
    }

    /**
     * Get the thing that owns this variable.
     */
    public function thing(): BelongsTo
    {
        return $this->belongsTo(Thing::class);
    }
}
