<?php

namespace App;

use Eloquent;

class UserModeration extends Eloquent
{
    public $table = 'users_moderation';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['uid', 'fname', 'lname'];

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
}
