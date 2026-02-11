<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Thing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ThingTag>
 */
final class ThingTagFactory extends Factory
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
            'key' => fake()->word(),
            'value' => fake()->word(),
        ];
    }
}
