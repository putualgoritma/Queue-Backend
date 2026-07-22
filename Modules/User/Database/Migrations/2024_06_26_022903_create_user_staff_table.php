<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_staff', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('depertement_id')->unsigned();
            $table->bigInteger('job_id')->unsigned();
            $table->enum('status', ['permanent', 'contract'])->default('permanent');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('depertement_id')->references('id')->on('depertements')->onDelete('cascade');
            $table->foreign('job_id')->references('id')->on('jobs')->onDelete('cascade');
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
        Schema::dropIfExists('user_staff');
    }
};
