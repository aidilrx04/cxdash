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
        Schema::create('training_reports', function (Blueprint $table) {
            $table->id();

            $table->string('file_path');
            $table->text('file_name')->nullable();
            $table->string('program_title')->nullable();
            $table->string('client_name')->nullable();
            $table->string('trainer_name')->nullable();
            $table->string('total_participants')->nullable();
            $table->string('total_evaluation')->nullable();
            $table->string('overall_satisfaction')->nullable();
            $table->string('status')->nullable();
            $table->string('pss_score')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_reports');
    }
};
