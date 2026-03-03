<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('resource_id')->unique();
            $table->foreignId('url_id')->constrained()->cascadeOnDelete();
            $table->string('method', 10);
            $table->string('path', 2048)->default('/');
            $table->string('content_type', 255)->nullable();
            $table->unsignedInteger('content_length')->default(0);
            $table->json('headers');
            $table->json('query_params')->nullable();
            $table->text('body')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index(['url_id', 'created_at']);
            $table->index('method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
