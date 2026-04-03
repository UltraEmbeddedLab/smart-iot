<?php declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DeviceType;
use App\Models\Firmware;
use App\Models\Thing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Firmware>
 */
final class FirmwareFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'thing_id' => Thing::factory(),
            'name' => fake()->words(3, true),
            'code' => '// Generated firmware code',
            'device_type' => DeviceType::Esp32,
            'parameters' => null,
        ];
    }
}
