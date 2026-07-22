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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string("code", 50)->nullable();
            $table->unsignedBigInteger("contact_id")->nullable();
            $table->unsignedBigInteger("role_id")->nullable();
            $table->enum('status', ['register', 'registered', 'activate', 'activated', 'close', 'closed'])->default('register');
            $table->enum('type', ['admin', 'member', 'staff'])->default('member');
            $table->date('register')->nullable();
            $table->date('approved')->nullable();
            $table->date('activated')->nullable();
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
        Schema::dropIfExists('users');
    }
};
