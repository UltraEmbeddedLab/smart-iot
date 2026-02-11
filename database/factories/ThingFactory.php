<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Device;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Thing>
 */
final class ThingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'uuid' => fake()->uuid(),
            'name' => fake()->words(2, true).' Thing',
            'timezone' => 'UTC',
            'device_id' => null,
        ];
    }

    /**
     * Associate a device with the thing.
     */
    public function withDevice(?Device $device = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'device_id' => $device?->id ?? Device::factory()->for(User::factory()),
        ]);
    }

    /**
     * Set a specific timezone.
     */
    public function withTimezone(string $timezone): static
    {
        return $this->state(fn (array $attributes): array => [
            'timezone' => $timezone,
        ]);
    }
}
