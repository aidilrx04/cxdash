<?php

use App\Models\Specialization;
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
        Schema::create('specialization_trainer', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Trainer::class);
            $table->foreignIdFor(Specialization::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('specialization_trainer');
    }
};
