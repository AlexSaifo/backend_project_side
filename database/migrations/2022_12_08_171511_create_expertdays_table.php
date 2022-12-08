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
        Schema::create('expertdays', function (Blueprint $table) {
            $table->id();
            $table->dateTime('start_day');
            $table->dateTime('end_day');
            $table->foreignId('experts_id')->constrained()->onDelete('cascade');
            $table->foreignId('weekdays_id')->constrained()->onDelete('cascade');
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
