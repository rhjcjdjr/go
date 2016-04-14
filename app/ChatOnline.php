<?php

namespace App;

use Auth;
use Eloquent;
use App\User;

class ChatOnline extends Eloquent
{
    public $table = 'chat_online';

    public $timestamps = false;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['uid', 'last_activity'];

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


    /**
     *  relate to User model
     *
     *  @return Model
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'uid', 'id');
    }

    /**
     *  
     *
     *  @return Model
     */
    public static function updateCurrent($delta = 0) {
        self::firstOrCreate(['uid' => Auth::user()->id])->update(['last_activity' => time() - $delta]);
    }

    /**
     *  
     *
     *  @return Model
     */
    public static function active($delta = 15) {
        return self::where('last_activity', '>', time() - $delta)->with('user')->get();
    }
}
