<?php declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WidgetType;
use App\Models\CloudVariable;
use App\Models\Dashboard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Widget>
 */
final class WidgetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dashboard_id' => Dashboard::factory(),
            'uuid' => fake()->uuid(),
            'type' => WidgetType::Value,
            'name' => fake()->words(2, true).' Widget',
            'cloud_variable_id' => CloudVariable::factory(),
            'sort_order' => 0,
        ];
    }

    /**
     * Set a specific widget type.
     */
    public function ofType(WidgetType $type): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => $type,
        ]);
    }

    /**
     * Create a widget without a cloud variable.
     */
    public function withoutVariable(): static
    {
        return $this->state(fn (array $attributes): array => [
            'cloud_variable_id' => null,
        ]);
    }

    /**
     * Set custom options.
     *
     * @param  array<string, mixed>  $options
     */
    public function withOptions(array $options): static
    {
        return $this->state(fn (array $attributes): array => [
            'options' => $options,
        ]);
    }
}
