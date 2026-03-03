<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Request;
use App\Models\Url;
use App\Models\User;
use Illuminate\Database\Seeder;

final class DevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create urls for the test user
        $urls = Url::factory()
            ->count(3)
            ->for($user)
            ->create();

        // Add requests to each url
        foreach ($urls as $url) {
            Request::factory()
                ->count(random_int(5, 15))
                ->for($url)
                ->create();

            // Update last_request_at
            $lastRequest = $url->requests()->latest()->first();
            if ($lastRequest) {
                $url->update(['last_request_at' => $lastRequest->created_at]);
            }
        }

        // Create an empty url
        Url::factory()
            ->for($user)
            ->create(['name' => 'Empty Url']);

        $this->command->info("Created {$user->email} with ".$urls->count().' urls');
    }
}
