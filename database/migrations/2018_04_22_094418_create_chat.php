<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChat extends Migration
{
    public function up()
    {
        Schema::create('chat', function (Blueprint $table) {
            $table->increments('message_id');
            $table->string('chat_id');
            $table->string('message');
            $table->integer('type');
            $table->integer('pet_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('chat');
    }
}
