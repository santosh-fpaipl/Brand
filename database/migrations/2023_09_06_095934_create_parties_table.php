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
        // create and show
        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('business')->nullable();
            $table->string('gst')->nullable();
            $table->string('pan')->nullable();
            $table->string('sid')->unique(); // DG-001
            $table->string('type')->default('staff'); // staff or fabricator or manager
            $table->unsignedBigInteger('monaal_id')->nullable(); // id of Customer in Monaal Databse
            $table->text('info')->nullable();
            $table->text('tags')->nullable();
            $table->boolean('active')->default(1); // 0,1
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parties');
    }
};
