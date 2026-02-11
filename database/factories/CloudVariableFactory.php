<?php declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CloudVariableType;
use App\Enums\VariablePermission;
use App\Enums\VariableUpdatePolicy;
use App\Models\Thing;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CloudVariable>
 */
final class CloudVariableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'thing_id' => Thing::factory(),
            'uuid' => fake()->uuid(),
            'name' => $name,
            'variable_name' => Str::snake($name),
            'type' => CloudVariableType::Float,
            'permission' => VariablePermission::ReadWrite,
            'update_policy' => VariableUpdatePolicy::OnChange,
            'persist' => false,
        ];
    }

    /**
     * Set a specific variable type.
     */
    public function ofType(CloudVariableType $type): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => $type,
        ]);
    }

    /**
     * Set permission to read-only.
     */
    public function readOnly(): static
    {
        return $this->state(fn (array $attributes): array => [
            'permission' => VariablePermission::ReadOnly,
        ]);
    }

    /**
     * Set update policy to periodic with given interval.
     */
    public function periodic(int $seconds): static
    {
        return $this->state(fn (array $attributes): array => [
            'update_policy' => VariableUpdatePolicy::Periodically,
            'update_parameter' => $seconds,
        ]);
    }
}
