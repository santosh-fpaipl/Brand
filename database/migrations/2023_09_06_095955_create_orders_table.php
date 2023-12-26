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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('sid')->unique();
            $table->foreignId('ledger_id')->constrained();
            $table->integer('quantity')->default(0); // total of quantities
            $table->date('expected_at');
            $table->json('log_status_time')->nullable();
            // issued -> accepted, cancelled
            $table->string('status')->default('issued');
            $table->foreignId('user_id')->constrained(); // created by user
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained(); // each sku
            $table->foreignId('order_id')->constrained();
            $table->integer('quantity')->default(0); // each quantity
            $table->timestamps();
        });

        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};