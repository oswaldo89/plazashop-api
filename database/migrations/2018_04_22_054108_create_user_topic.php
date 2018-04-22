<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTopic extends Migration
{
    public function up()
    {
        Schema::create('user_topic', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_one');
            $table->integer('user_two');
            $table->string('topic_id');
            $table->integer('pet_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('user_topic');
    }
}
