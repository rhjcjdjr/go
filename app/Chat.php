<?php

namespace App;

use Eloquent;


class Chat extends Eloquent
{
    /**
     * @var string
     */
    protected $table = 'chat';
    
    /**
     * @var array
     */
    protected $fillable = ['oid', 'text', 'visible', 'ts'];

    /**
     * @var array
     */
    protected $hidden = ['deleted_at', 'updated_at', 'oid'];


    /**
     *  relate to User model
     *
     *  @return Model
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'oid', 'id');
    }


    /**
     *  get slice of messages up-ordered. if input is invalid -
     *  empty collection returned (not false or null!)
     *
     *  @param unsigned int $fromId  id of chat-message to begin with
     *  @param unsigned int $count   count of chat-message to receive
     *
     *  @return Collection
     */
    public static function sliceUpFrom($fromId, $count = 10)
    {
        if ( ! self::checkSliseCount($count)) return collect(null);

        return self::where('id', '<', $fromId)
                                        ->orderBy('id', 'desc')
                                            ->take($count)
                                                ->get()
                                                    ->keyBy('id')
                                                        ->sort();
    }

    /**
     *  get slice of messages down-ordered. if input is invalid -
     *  empty collection returned (not false or null!)
     *
     *  @info  if $fromId = 1 and $count = 4 then 4 rows below 1 is returned 
     *
     *  @param unsigned int $fromId  id of chat-message to begin with
     *  @param unsigned int $count   count of chat-message to receive
     *
     *  @return Collection
     */
    public static function sliceDownFrom($fromId, $count = 10)
    {
        if ( ! self::checkSliseCount($count)) return collect(null);

        return self::where('id', '>', $fromId)
                                        ->orderBy('id', 'asc')
                                            ->take($count)
                                                ->get()
                                                    ->keyBy('id');
    }










    /**
     *  helpers of model
     *
     *  prefix 'ci' stands for 'checkInput'
     */


    /**
     *  check input value. if slice limit is too big (> 500) or
     *  negative (0 <) - reject querying db
     *
     *  @return boolean
     */
    private static function checkSliseCount($count)
    {
        $count = (int) $count;

        //  prevent DoS attack. allow < 500 raws pur time
        if ($count < 0 or $count > 500)
            return false;
        return true;
    }
}