<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_transactions', function (Blueprint $table): void {
            $table->id();
            $table->string('account_id');
            $table->string('type');
            $table->bigInteger('amount');
            $table->string('description')->default('');
            $table->string('related_account_id')->nullable();
            $table->string('occurred_at', 32);

            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_transactions');
    }
};
