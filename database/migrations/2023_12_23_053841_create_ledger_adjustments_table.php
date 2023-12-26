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
        Schema::create('ledger_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('ledger_id');
            $table->integer('order_qty')->default(0);
            $table->integer('ready_qty')->default(0);
            $table->integer('demand_qty')->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledger_adjustments');
    }
};
