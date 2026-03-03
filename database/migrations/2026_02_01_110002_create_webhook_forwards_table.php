<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('webhook_forwards', function (Blueprint $table): void {
            $table->id();
            $table->uuid('resource_id')->unique();
            $table->foreignId('request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('url_id')->constrained()->cascadeOnDelete();
            $table->text('target_url');
            $table->string('method', 10)->default('POST');
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->text('response_body')->nullable();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            // Indexes for common queries
            $table->index('request_id');
            $table->index('url_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_forwards');
    }
};
