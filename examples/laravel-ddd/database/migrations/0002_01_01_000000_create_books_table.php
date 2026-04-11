<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('author');
            $table->string('isbn', 13);
            $table->string('status')->default('available');
            $table->string('borrower')->nullable();
            $table->timestamps();

            $table->index('author');
            $table->index('status');
            $table->unique('isbn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
