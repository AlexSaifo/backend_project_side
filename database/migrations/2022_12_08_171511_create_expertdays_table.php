<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expert_days', function (Blueprint $table) {
            $table->id();
            $table->dateTime('start_day');
            $table->dateTime('end_day');
            $table->foreignId('users_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('weekdays_id')->uniqid();
            $table->foreign('weekdays_id')->references('id')->on('week_days')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expertdays');
    }
};
