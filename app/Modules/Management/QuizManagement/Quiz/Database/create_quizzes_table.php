<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     php artisan migrate --path='/app/Modules/Management/QuizManagement/Quiz/Database/create_quizzes_table.php'
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255)->nullable();
            $table->text('description')->nullable();
            $table->integer('total_question')->nullable();
            $table->datetime('exam_start_datetime')->nullable();
            $table->datetime('exam_end_datetime')->nullable();
            $table->float('total_mark')->nullable();
            $table->float('pass_mark')->nullable();
            $table->tinyInteger('is_negative_marking')->default(0);
            $table->float('negative_value')->nullable();

            $table->bigInteger('creator')->unsigned()->nullable();
            $table->string('slug', 50)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};