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

            $table->text('file_name');
            $table->string('program_title');
            $table->string('client_name');
            $table->string('trainer_name');
            $table->string('total_participants');
            $table->string('total_evaluation');
            $table->string('overall_satisfaction');
            $table->string('status');
            $table->string('pss_score');
            $table->string('file_path');

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
