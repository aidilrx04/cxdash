<?php

use App\Models\FeedbackQuestion;
use App\Models\Trainee;
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
        Schema::create('feedback_generals', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Trainee::class);
            $table->foreignIdFor(FeedbackQuestion::class);
            $table->string('response');
            $table->string('sentiment')->nullable();
            $table->string('theme')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_generals');
    }
};
