<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     php artisan migrate --path='/app/Modules/Management/QuizManagement/Quiz/Database/create_quiz_participations_table.php' 
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quiz_participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->string('session_token')->unique();

            // Participant Information
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('organization')->nullable();

            // Quiz Attempt Details
            $table->json('answers')->nullable(); // Stores question_id => answer_option_id(s)
            $table->decimal('obtained_marks', 8, 2)->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->integer('duration')->nullable(); // Duration in seconds
            $table->string('submit_reason')->default('Normal completion');

            // Status Tracking
            $table->enum('status', ['active', 'completed', 'expired'])->default('active');
            $table->boolean('is_completed')->default(false);
            $table->boolean('is_passed')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['quiz_id', 'email']);
            $table->index(['quiz_id', 'is_completed']);
            $table->index('session_token');
            $table->bigInteger('creator')->unsigned()->nullable();
            $table->string('slug', 50)->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_participations');
    }
};
