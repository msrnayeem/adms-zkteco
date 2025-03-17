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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->integer('day');
            $table->integer('month');
            $table->integer('year');

            $table->time('shift_start_at')->nullable();
            $table->time('user_entry_time');
            $table->boolean('is_late')->default(false);

            $table->time('shift_end_at')->nullable();
            $table->time('user_exit_time')->nullable();
            $table->boolean('is_early')->default(false);

            $table->boolean('manual_entry')->default(false);
            $table->unsignedBigInteger('manual_entry_by')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('users');
            $table->foreign('manual_entry_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
