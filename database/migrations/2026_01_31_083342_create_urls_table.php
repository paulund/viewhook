<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('urls', function (Blueprint $table) {
            $table->id();
            $table->uuid('resource_id')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->timestamp('last_request_at')->nullable();

            // Webhook forwarding settings
            $table->text('forward_to_url')->nullable();
            $table->string('forward_method', 10)->default('POST');
            $table->json('forward_headers')->nullable();

            // Notification settings
            $table->boolean('notify_email')->default(false);
            $table->boolean('notify_slack')->default(false);
            $table->text('slack_webhook_url')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('urls');
    }
};
