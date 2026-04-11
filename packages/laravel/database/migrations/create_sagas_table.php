<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        $table = config('solidframe.saga.store.table', 'sagas');

        Schema::connection($this->getConnection())->create($table, function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('saga_type');
            $table->string('status');
            $table->json('associations');
            $table->json('state');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $table = config('solidframe.saga.store.table', 'sagas');

        Schema::connection($this->getConnection())->dropIfExists($table);
    }

    public function getConnection(): ?string
    {
        return config('solidframe.saga.store.connection');
    }
};
