<?php

use App\Enums\Schedule\StatusEnum;
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
            $table->integer('status')->default(StatusEnum::PENDING->value)->change();
            $table->boolean('is_open')->default(false)->change();
            $table->dropForeign(['room_id']);
            $table->dropColumn('room_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->integer('status')->default(null)->change();
            $table->boolean('is_open')->default(null)->change();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
        });
    }
};
