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
         * Product Catelog (Using DS Api for related data using product_sid)
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
            $table->string('product_option_sid')->nullable(); // product color sid
            $table->unsignedBigInteger('product_range_id')->nullable(); // product size
            $table->string('product_range_sid')->nullable(); // product size sid

            // For ecom app
            $table->boolean('active')->default(true); // enable/disable this stock
            $table->text('note')->nullable(); // remarks for dead stock
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ledgers', function (Blueprint $table) {
            $table->id();
            $table->string('sid')->unique();
            $table->string('name'); // ledger name -> catelog name + party name
            $table->string('product_sid'); // ds app
            $table->unsignedBigInteger('product_id'); // ds app
            $table->foreignId('party_id')->constrained(); // fabricator party id
            $table->bigInteger('balance_qty'); // Total(order-demand) 
            $table->bigInteger('demandable_qty'); // Total(ready-demand) 
            $table->timestamps();
            $table->unique(['product_id', 'party_id']);
        });

        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->foreignId('ledger_id')->constrained();
            $table->unsignedBigInteger('sender_id');
            $table->foreign('sender_id')->references('id')->on('parties'); // staff, fabri, manager
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('recevied_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
        Schema::dropIfExists('ledgers');
        Schema::dropIfExists('chats');
    }
};