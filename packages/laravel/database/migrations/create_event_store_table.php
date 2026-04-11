<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        $table = config('solidframe.event_sourcing.event_store.table', 'event_store');

        Schema::connection($this->getConnection())->create($table, function (Blueprint $table): void {
            $table->id();
            $table->string('aggregate_id');
            $table->string('aggregate_type');
            $table->unsignedInteger('version');
            $table->string('event_type');
            $table->json('payload');
            $table->timestamp('occurred_at');

            $table->unique(['aggregate_id', 'version']);
            $table->index('aggregate_id');
        });
    }

    public function down(): void
    {
        $table = config('solidframe.event_sourcing.event_store.table', 'event_store');

        Schema::connection($this->getConnection())->dropIfExists($table);
    }

    public function getConnection(): ?string
    {
        return config('solidframe.event_sourcing.event_store.connection');
    }
};
