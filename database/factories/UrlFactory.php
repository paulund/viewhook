<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Url;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Url>
 */
final class UrlFactory extends Factory
{
    protected $model = Url::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'last_request_at' => null,
        ];
    }

    public function withLastRequest(): static
    {
        return $this->state(fn (array $attributes): array => [
            'last_request_at' => fake()->dateTimeThisMonth(),
        ]);
    }
}
