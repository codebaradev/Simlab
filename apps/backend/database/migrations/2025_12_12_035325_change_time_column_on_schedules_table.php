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
        Schema::table('schedules', function (Blueprint $table) {
            $table->date('start_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->dropColumn(['start_datetime', 'end_datetime']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->dropColumn(['start_date', 'start_time', 'end_time']);
        });
    }
};
