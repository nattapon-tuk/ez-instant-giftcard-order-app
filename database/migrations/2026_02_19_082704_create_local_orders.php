<?php

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
        Schema::create('local_orders', function (Blueprint $table) {
            $table->id();
            $table->string('localOrderId')->unique();
            $table->enum('localStatus', ['PROCESSING', 'COMPLETED', 'CANCELLED'])
                ->default('PROCESSING');
            $table->string('ezTransactionId')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('local_orders');
    }
};
