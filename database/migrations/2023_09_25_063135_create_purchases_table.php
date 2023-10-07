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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_work_order_id')->constrained();
            $table->string('job_work_order_sid');
            $table->unsignedBigInteger('product_id');
            $table->string('product_sid');
            $table->unsignedBigInteger('fabricator_id');
            $table->string('fabricator_sid');
            $table->unsignedBigInteger('sale_id')->nullable(); // monaal
            $table->string('sale_sid')->nullable(); // monaal
            $table->string('sid')->unique(); //created manually purchase sid
            $table->string('invoice_no')->nullable(); // my purchase id
            $table->string('invoice_date')->timestamp();
            $table->integer('quantity')->default(0);
            $table->json('quantities');
            $table->integer('loss_quantity')->default(0);
            $table->json('loss_quantities')->nullable();
            $table->json('message')->nullable();
            $table->json('log_status_time')->nullable();
            $table->string('time_difference')->nullable(); // In days
            $table->string('status')->default('cutting'); //'cutting','production','packing','ready','requested','completed','cancelled'
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
