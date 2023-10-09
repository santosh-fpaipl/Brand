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
        /**
         * Finished Goods
         */
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();

            $table->string('sku')->unique(); // unique SKU for this stock (it contains id of product-option-range)
            $table->integer('quantity')->default(0); // available-in-hand for sale (updated by purchase and sale)
            // $table->integer('roq')->default(1); // Re-Order quantity
            // $table->integer('incoming')->default(0); // order placed yet to recevied (updated by purchase)
            // $table->integer('outgoing')->default(0); // reserved for sale yet to dispatch (updated by sale)
            
            // As per dsa
            $table->string('product_sid')->nullable(); // product sid 
            $table->unsignedBigInteger('product_id')->nullable(); // product
            $table->unsignedBigInteger('product_option_id')->nullable(); // product color
            $table->unsignedBigInteger('product_range_id')->nullable(); // product size

            // For ecom app
            $table->boolean('active')->default(true); // enable/disable this stock
            $table->text('note')->nullable(); // remarks for dead stock
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