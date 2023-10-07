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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained();
            $table->text('print')->nullable()->unique();
            $table->string('fname');
            $table->string('lname')->nullable();
            $table->string('contacts'); // json array
            $table->string('line1');
            $table->string('line2')->nullable();
            $table->string('district');
            $table->string('state');
            $table->string('country');
            $table->string('pincode');
            $table->unsignedBigInteger('district_id');
            $table->unsignedBigInteger('state_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
