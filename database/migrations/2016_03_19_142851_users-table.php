<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('fname');
            $table->string('lname');
            $table->string('vk_id')->unique();
            $table->string('pic');
            $table->enum('sex', [1, 2])->nullable()->default(null);
            $table->string('pic_small');
            $table->string('email')->unique();
            $table->string('password', 60);
            $table->rememberToken();
            $table->softDeletes();
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
        Schema::drop('users');
    }
}
