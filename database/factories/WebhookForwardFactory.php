<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Request;
use App\Models\Url;
use App\Models\WebhookForward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WebhookForward>
 */
final class WebhookForwardFactory extends Factory
{
    protected $model = WebhookForward::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'request_id' => Request::factory(),
            'url_id' => Url::factory(),
            'target_url' => fake()->url(),
            'method' => fake()->randomElement(['POST', 'PUT', 'PATCH']),
            'status_code' => fake()->randomElement([200, 201, 204]),
            'response_body' => json_encode(['status' => 'ok']),
            'response_time_ms' => fake()->numberBetween(50, 500),
            'error' => null,
        ];
    }

    /**
     * Create a successful forward.
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status_code' => 200,
            'response_body' => json_encode(['status' => 'ok']),
            'error' => null,
        ]);
    }

    /**
     * Create a failed forward.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status_code' => null,
            'response_body' => null,
            'error' => 'Connection timeout',
        ]);
    }

    /**
     * Create a forward with an error status code.
     */
    public function errorResponse(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status_code' => fake()->randomElement([400, 404, 500, 502, 503]),
            'response_body' => json_encode(['error' => 'Request failed']),
            'error' => null,
        ]);
    }
}
