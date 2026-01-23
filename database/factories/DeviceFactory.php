<?php declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DeviceStatus;
use App\Enums\DeviceType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Device>
 */
final class DeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'device_id' => fake()->uuid(),
            'name' => fake()->words(2, true).' Device',
            'type' => fake()->randomElement(DeviceType::cases()),
            'secret_key' => Str::password(32, symbols: false),
            'status' => DeviceStatus::Provisioning,
            'last_activity_at' => null,
            'metadata' => null,
        ];
    }

    /**
     * Indicate that the device is online.
     */
    public function online(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => DeviceStatus::Online,
            'last_activity_at' => now(),
        ]);
    }

    /**
     * Indicate that the device is offline.
     */
    public function offline(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => DeviceStatus::Offline,
            'last_activity_at' => now()->subMinutes(30),
        ]);
    }

    /**
     * Indicate that the device is provisioning.
     */
    public function provisioning(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => DeviceStatus::Provisioning,
            'last_activity_at' => null,
        ]);
    }

    /**
     * Set the device type to Arduino.
     */
    public function arduino(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => DeviceType::Arduino,
        ]);
    }

    /**
     * Set the device type to ESP32.
     */
    public function esp32(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => DeviceType::Esp32,
        ]);
    }

    /**
     * Set the device type to Raspberry Pi.
     */
    public function raspberryPi(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => DeviceType::RaspberryPi,
        ]);
    }

    /**
     * Set metadata for the device.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function withMetadata(array $metadata): static
    {
        return $this->state(fn (array $attributes): array => [
            'metadata' => $metadata,
        ]);
    }

    /**
     * Set a typical metadata structure for an online device.
     */
    public function withTypicalMetadata(): static
    {
        return $this->state(fn (array $attributes): array => [
            'metadata' => [
                'firmware_version' => fake()->semver(),
                'ip_address' => fake()->localIpv4(),
                'mac_address' => fake()->macAddress(),
                'last_boot' => now()->subHours(fake()->numberBetween(1, 72))->toIso8601String(),
            ],
        ]);
    }
}
