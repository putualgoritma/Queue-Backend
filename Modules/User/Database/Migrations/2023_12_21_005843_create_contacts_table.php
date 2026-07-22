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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string("name", 100)->nullable(false);
            $table->string("card_id", 50)->nullable();
            $table->date("birthday")->nullable();
            $table->enum('sex', ['M', 'F'])->default('M');
            $table->string("email", 100)->nullable();
            $table->string("phone", 100)->nullable();
            $table->string("phone2", 100)->nullable();
            $table->tinyText("description")->nullable();
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
        Schema::dropIfExists('contacts');
    }
};
