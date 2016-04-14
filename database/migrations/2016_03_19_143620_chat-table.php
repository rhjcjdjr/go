<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->integer('oid')->unsigned()->nullable();
            $table->text('text');
            $table->enum('visible', [0, 1])->default(1);
            $table->bigInteger('ts')->default(null);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('oid')->references('id')->on('users')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('chat');
    }
}
