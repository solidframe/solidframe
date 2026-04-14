<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sagas', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('saga_type');
            $table->string('status');
            $table->json('associations');
            $table->binary('state');
            $table->timestamps();

            $table->index('saga_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sagas');
    }
};
