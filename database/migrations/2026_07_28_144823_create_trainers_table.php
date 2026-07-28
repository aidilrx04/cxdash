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
        Schema::create('trainers', function (Blueprint $table) {
            $table->id();

            $table->string('full_name')->nullable();
            $table->string('home_address')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->integer('years_experience')->nullable();
            $table->string('profile_picture')->nullable();
            $table->text('notable_clients')->nullable();
            $table->string('avg_evaluation_score')->nullable();
            $table->string('fee_structure')->nullable();
            $table->text('professional_summary')->nullable();
            $table->text('additional_info')->nullable();
            $table->string('cv_path')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainers');
    }
};
