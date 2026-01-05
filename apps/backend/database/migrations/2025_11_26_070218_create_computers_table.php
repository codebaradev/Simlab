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
        Schema::create('computers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->integer('computer_count');
            $table->string('name');
            $table->string('processor');
            $table->string( 'gpu');
            $table->integer('ram_capacity');
            $table->integer('ram_type');
            $table->integer('storage_capacity');
            $table->integer('storage_type');
            $table->float('display_size');
            $table->integer('display_resolution');
            $table->integer('display_refresh_rate');
            $table->integer('os');
            $table->integer('release_year');
            $table->integer('category');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('computers');
    }
};
