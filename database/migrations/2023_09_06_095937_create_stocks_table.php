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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique(); // unique SKU for this stock
            $table->integer('quantity')->default(0); // available-in-hand for sale (updated by purchase and sale)
            // $table->integer('roq')->default(1); // Re-Order quantity
            // $table->integer('incoming')->default(0); // order placed yet to recevied (updated by purchase)
            // $table->integer('outgoing')->default(0); // reserved for sale yet to dispatch (updated by sale)
            $table->unsignedBigInteger('product_id')->nullable(); // product 
            $table->string('product_sid')->nullable(); // product sid
            $table->unsignedBigInteger('product_option_id')->nullable(); // product color
            $table->unsignedBigInteger('product_range_id')->nullable(); // product size
            $table->boolean('active')->default(true); // enable/disable this stock
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};