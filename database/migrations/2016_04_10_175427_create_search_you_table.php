<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSearchYouTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('search_you', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->integer('oid')->unsigned()->nullable();

            $table->integer('reg_city_id')->unsigned()->nullable();
            $table->integer('reg_reg_id')->unsigned()->nullable();
            $table->integer('reg_country_id')->unsigned()->nullable();

            $table->integer('growth_from')->unsigned()->nullable();
            $table->integer('growth_to')->unsigned()->nullable();
            $table->integer('age_from')->unsigned()->nullable();
            $table->integer('age_to')->nullable();
            $table->dateTime('date_from')->nullable();
            $table->dateTime('date_to')->nullable();
            $table->enum('person_sex', [2, 1])->default(null);
            $table->text('comment')->nullable();
            $table->text('polygon_coords_serialized')->nullable();

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
        Schema::drop('search_you');
    }
}
