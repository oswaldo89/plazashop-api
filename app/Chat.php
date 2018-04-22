<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    public $table = "chat";
    protected $fillable = ['chat_id', 'message','type','pet_id'];
}
