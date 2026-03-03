<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Request;
use App\Models\Url;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Request>
 */
final class RequestFactory extends Factory
{
    protected $model = Request::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $method = fake()->randomElement(['GET', 'POST', 'PUT', 'PATCH', 'DELETE']);
        $hasBody = in_array($method, ['POST', 'PUT', 'PATCH'], true);

        return [
            'url_id' => Url::factory(),
            'method' => $method,
            'path' => '/',
            'content_type' => $hasBody ? 'application/json' : null,
            'content_length' => $hasBody ? fake()->numberBetween(10, 1000) : 0,
            'headers' => $this->generateHeaders($method),
            'query_params' => fake()->optional()->randomElements([
                'page' => (string) fake()->numberBetween(1, 10),
                'limit' => (string) fake()->numberBetween(10, 100),
            ], null),
            'body' => $hasBody ? json_encode(['data' => fake()->words(5)]) : null,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }

    public function post(): static
    {
        return $this->state(fn (array $attributes): array => [
            'method' => 'POST',
            'content_type' => 'application/json',
            'content_length' => fake()->numberBetween(10, 1000),
            'body' => json_encode(['event' => 'webhook', 'data' => fake()->words(5)]),
        ]);
    }

    public function get(): static
    {
        return $this->state(fn (array $attributes): array => [
            'method' => 'GET',
            'content_type' => null,
            'content_length' => 0,
            'body' => null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $body
     */
    public function withJsonBody(array $body): static
    {
        $encoded = json_encode($body);

        return $this->state(fn (array $attributes): array => [
            'method' => 'POST',
            'content_type' => 'application/json',
            'content_length' => mb_strlen($encoded !== false ? $encoded : ''),
            'body' => $encoded,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function withFormData(array $data): static
    {
        $encoded = http_build_query($data);

        return $this->state(fn (array $attributes): array => [
            'method' => 'POST',
            'content_type' => 'application/x-www-form-urlencoded',
            'content_length' => mb_strlen($encoded),
            'body' => $encoded,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function generateHeaders(string $method): array
    {
        $headers = [
            'Host' => 'viewhook.dev',
            'Accept' => '*/*',
            'User-Agent' => fake()->userAgent(),
            'X-Request-Id' => fake()->uuid(),
        ];

        if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $headers['Content-Type'] = 'application/json';
        }

        return $headers;
    }
}
