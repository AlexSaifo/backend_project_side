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
        Schema::create('expert_detail', function (Blueprint $table) {
            $table->id();
            $table->string('skills');
            $table->double('rating')->default(0);
            $table->integer('ratings')->default(0);
            $table->double('cost');

            $table->unsignedBigInteger('user_id')->uniqid();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('profile_picture')->nullable();
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
        Schema::dropIfExists('experts');
    }
};
