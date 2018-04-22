<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserTopic extends Model
{
    protected $fillable = ['user_one', 'user_two','topic_id','product_id'];
}
