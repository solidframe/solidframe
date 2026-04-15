<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_balances', function (Blueprint $table): void {
            $table->string('account_id')->primary();
            $table->string('holder_name');
            $table->string('currency', 3);
            $table->bigInteger('balance')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_balances');
    }
};
