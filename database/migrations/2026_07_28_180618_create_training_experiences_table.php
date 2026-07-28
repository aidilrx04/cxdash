<?php

use App\Models\Trainer;
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
        Schema::create('training_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Trainer::class);
            $table->string('program_name');
            $table->string('client');
            $table->date('date_start');
            $table->date('date_end');
            $table->integer('participant_count');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_experiences');
    }
};
