<?php declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TriggerActionType;
use App\Enums\TriggerOperator;
use App\Models\CloudVariable;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trigger>
 */
final class TriggerFactory extends Factory
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
            'cloud_variable_id' => CloudVariable::factory(),
            'uuid' => fake()->uuid(),
            'name' => fake()->words(2, true).' Trigger',
            'operator' => TriggerOperator::GreaterThan,
            'value' => '100',
            'action_type' => TriggerActionType::Email,
            'action_config' => ['email' => fake()->email()],
            'is_active' => true,
            'cooldown_seconds' => 0,
        ];
    }

    /**
     * Set the trigger as active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => true,
        ]);
    }

    /**
     * Set the trigger as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    /**
     * Set a cooldown period in seconds.
     */
    public function withCooldown(int $seconds): static
    {
        return $this->state(fn (array $attributes): array => [
            'cooldown_seconds' => $seconds,
        ]);
    }

    /**
     * Set a specific operator.
     */
    public function ofOperator(TriggerOperator $operator): static
    {
        return $this->state(fn (array $attributes): array => [
            'operator' => $operator,
        ]);
    }

    /**
     * Set the action type to webhook with a URL.
     */
    public function webhookAction(string $url = 'https://example.com/webhook'): static
    {
        return $this->state(fn (array $attributes): array => [
            'action_type' => TriggerActionType::Webhook,
            'action_config' => ['url' => $url],
        ]);
    }
}
