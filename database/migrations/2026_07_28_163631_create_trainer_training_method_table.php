<?php

use App\Models\Trainer;
use App\Models\TrainingMethod;
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
        Schema::create('trainer_training_method', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Trainer::class);
            $table->foreignIdFor(TrainingMethod::class);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainer_training_method');
    }
};
