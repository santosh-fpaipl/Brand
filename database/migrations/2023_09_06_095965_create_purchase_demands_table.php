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
        Schema::create('purchase_demands', function (Blueprint $table) {
            $table->id();
            $table->string('sid')->unique();
            $table->foreignId('ledger')->constrained();
            $table->integer('quantity')->default(0); // total of quantities
            $table->date('expected_at');
            $table->json('log_status_time')->nullable();
            // issued -> accepted, cancelled
            $table->string('status')->default('issued'); 
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('purchase_demand_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained(); // each sku
            $table->foreignId('purchase_demand_id')->constrained();
            $table->integer('quantity')->default(0); // each quantity
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_demand_items');
        Schema::dropIfExists('purchase_demands');
    }
};