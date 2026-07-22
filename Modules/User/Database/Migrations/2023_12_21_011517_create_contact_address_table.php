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
        Schema::create('contact_address', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("contact_id")->nullable(false);
            $table->tinyText("location")->nullable();
            $table->unsignedBigInteger("subdistrict_id")->nullable();
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
        Schema::dropIfExists('contact_address');
    }
};
