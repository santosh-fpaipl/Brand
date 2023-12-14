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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('sid')->unique();

            $table->unsignedBigInteger('product_id'); // dsa app
            $table->string('product_sid'); // dsa app
            $table->unsignedBigInteger('fabricator_id'); // fabricator app
            $table->string('fabricator_sid');// fabricator app

            $table->integer('quantity')->default(0); // total of quantities
            $table->json('quantities'); // grid of option & range with qty

            $table->json('message')->nullable(); // chat for this po

            $table->date('expected_at');
            $table->json('log_status_time')->nullable();
            
            // draft -> issued -> completed, cancelled
            $table->string('status')->default('draft'); 
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_work_orders');
    }
};