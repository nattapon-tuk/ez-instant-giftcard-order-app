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
        Schema::create('ez_orders', function (Blueprint $table) {
            $table->id();
            $table->string('ezTransactionId')->unique();
            $table->string('ezOrderStatus', ['PROCESSING', 'COMPLETED', 'CANCELLED'])
                    ->default('PROCESSING');
            $table->string('redeemCode')->nullable();
            $table->boolean('isReserved')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ez_orders');
    }
};
