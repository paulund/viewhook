<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Log;

describe('SetLogContext middleware', function (): void {
    it('sets user_id context with resource_id for authenticated requests', function (): void {
        Log::spy();

        $user = User::factory()->create();

        $this->actingAs($user)->get(route('urls.index'));

        Log::shouldHaveReceived('withContext')->with(['user_id' => $user->resource_id]);
    });

    it('sets user_id context to null for unauthenticated requests', function (): void {
        Log::spy();

        $this->get(route('login'));

        Log::shouldHaveReceived('withContext')->with(['user_id' => null]);
    });
});
