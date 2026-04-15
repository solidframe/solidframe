<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_store', function (Blueprint $table): void {
            $table->id();
            $table->string('aggregate_id');
            $table->string('aggregate_type');
            $table->integer('version')->unsigned();
            $table->string('event_type');
            $table->json('payload');
            $table->string('occurred_at', 32);

            $table->unique(['aggregate_id', 'version']);
            $table->index('aggregate_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_store');
    }
};
