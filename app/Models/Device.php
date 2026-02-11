<?php declare(strict_types=1);

namespace App\Models;

use App\Enums\DeviceStatus;
use App\Enums\DeviceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

final class Device extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $guarded = [
        'id',
        'device_id',
        'secret_key',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'secret_key',
    ];

    protected static function booted(): void
    {
        self::creating(function (Device $device): void {
            if (empty($device->device_id)) {
                $device->device_id = (string) Str::uuid();
            }

            if (empty($device->status)) {
                $device->status = DeviceStatus::Provisioning;
            }

            if (empty($device->secret_key)) {
                $device->secret_key = Str::password(32, symbols: false);
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => DeviceType::class,
            'status' => DeviceStatus::class,
            'secret_key' => 'hashed',
            'last_activity_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * Create a new device and return it with the plain text secret key.
     *
     * @param  array<string, mixed>  $attributes
     * @return array{device: Device, secret_key: string}
     */
    public static function createWithCredentials(array $attributes): array
    {
        $plainSecretKey = Str::password(32, symbols: false);

        $device = new self($attributes);
        $device->secret_key = $plainSecretKey;
        $device->save();

        return [
            'device' => $device,
            'secret_key' => $plainSecretKey,
        ];
    }

    /**
     * Get the user that owns the device.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a new secret key for the device.
     *
     * @return string The plain text secret key (only available once)
     */
    public function generateSecretKey(): string
    {
        $plainSecretKey = Str::password(32, symbols: false);

        $this->secret_key = $plainSecretKey;
        $this->save();

        return $plainSecretKey;
    }

    /**
     * Verify if the provided secret key matches the stored hash.
     */
    public function verifySecretKey(string $secretKey): bool
    {
        return password_verify($secretKey, $this->secret_key);
    }

    /**
     * Mark the device as online and update last activity.
     */
    public function markAsOnline(): void
    {
        $this->status = DeviceStatus::Online;
        $this->last_activity_at = now();
        $this->save();
    }

    /**
     * Mark the device as offline.
     */
    public function markAsOffline(): void
    {
        $this->status = DeviceStatus::Offline;
        $this->save();
    }

    /**
     * Update the last activity timestamp.
     */
    public function updateLastActivity(): bool
    {
        $this->last_activity_at = now();

        return $this->save();
    }

    /**
     * Check if the device is online.
     */
    public function isOnline(): bool
    {
        return $this->status === DeviceStatus::Online;
    }

    /**
     * Check if the device is offline.
     */
    public function isOffline(): bool
    {
        return $this->status === DeviceStatus::Offline;
    }

    /**
     * Check if the device is provisioning.
     */
    public function isProvisioning(): bool
    {
        return $this->status === DeviceStatus::Provisioning;
    }

    /**
     * Get a metadata value by key.
     */
    public function getMetadata(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Set a metadata value by key.
     */
    public function setMetadata(string $key, mixed $value): void
    {
        $metadata = $this->metadata ?? [];
        $metadata[$key] = $value;
        $this->metadata = $metadata;
        $this->save();
    }
}
