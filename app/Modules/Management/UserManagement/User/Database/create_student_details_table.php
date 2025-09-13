<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     php artisan migrate --path='/app/Modules/Management/UserManagement/User/Database/create_student_details_table.php' 
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->string('father_name', 100)->nullable();
            $table->string('mother_name', 100)->nullable();
            $table->enum('gender', ['Male','Female','Other'])->nullable();
            $table->string('guardian_number', 100)->nullable();
            $table->enum('blood_group', ['A+','A'])->nullable();
            $table->string('occupation', 100)->nullable();
            $table->string('designation', 100)->nullable();
            $table->string('organization', 100)->nullable();
            $table->string('institution', 100)->nullable();
            $table->string('class', 100)->nullable();
            $table->string('last_certificate_name', 100)->nullable();
            $table->string('reference_source', 100)->nullable();

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
        Schema::dropIfExists('student_details');
    }
};