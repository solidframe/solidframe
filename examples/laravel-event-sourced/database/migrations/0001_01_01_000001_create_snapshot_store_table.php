<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('snapshot_store', function (Blueprint $table): void {
            $table->id();
            $table->string('aggregate_id')->unique();
            $table->string('aggregate_type');
            $table->integer('version')->unsigned();
            $table->json('state');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('snapshot_store');
    }
};
