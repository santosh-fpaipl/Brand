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
        Schema::create('job_work_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('product_sid');
            $table->unsignedBigInteger('fabricator_id');
            $table->string('fabricator_sid');
            $table->string('sid')->unique(); //created manually job work order id
            $table->integer('quantity')->default(0);
            $table->json('quantities');
            $table->json('message')->nullable();
            $table->date('expected_at');
            $table->json('log_status_time')->nullable();
            $table->string('status')->default('po_issued'); //po_issued,po_placed,po_completed,cancelled
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