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
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("login_id")->nullable();
            $table->unsignedBigInteger("request_id")->nullable();
            $table->unsignedBigInteger("transaction_id")->nullable();
            $table->string('type');
            $table->enum('status', ['pending', 'active', 'close'])->default('pending');
            $table->string('module', 20);
            $table->tinyText('memo');
            $table->string('status_trs_old');
            $table->string('status_trs_new');
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
        Schema::dropIfExists('approvals');
    }
};
