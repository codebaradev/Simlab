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
        Schema::create('student_academic_class', function (Blueprint $table) {
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('academic_class_id')->constrained('academic_classes')->onDelete('cascade');
            $table->primary(['student_id', 'academic_class_id']); // composite primary key
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_academic_class');
    }
};
