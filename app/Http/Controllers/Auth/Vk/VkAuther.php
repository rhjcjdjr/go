<?php

namespace App\Http\Controllers\Auth\Vk;

use Hash;
use Auth;
use Eloquent;
use App\User;
use Illuminate\Support\Collection;

class VkAuther extends User
{
    protected $table = 'users';

	/**
	 *	find out if user with given vkId exists
	 *	
	 *	@param int $vkId 	user's vk id
	 *	
	 *	@return bool
	 */
    public static function has($vkId)
    {
    	return (bool) self::where('vk_id', '=', $vkId)->count() > 0;
    }

	/**
	 *	log in user in system
	 *	
	 *	@param User $user     user's model to login
	 *	
	 *	@return bool
	 */
    public static function login(User $user)
    {
        $authed = Auth::attempt([
            'email' => $user->email,
            'password' => $user->vk_id . $user->email
        ], true);

        return $authed;
    }

   	/**
	 *	register new user. user represented with Collection (attributes)
     *  creates new user and adds him to db. nothing more happens here.
     *  
     *  required fields are: 'uid', 'first_name', 'last_name', 'email'
     *  
     *  password field generated of: Hash::make($vk_id . $email)
	 *	
	 *	@param Collection $user     user attributes
	 *	
	 *	@return bool
	 */
    public static function register(Collection $user)
    {
        foreach($required = ['uid', 'first_name', 'last_name', 'email'] as $k => $field) {
            //  required field doesn't given
            if ( ! $user->has($field)) return false;
        }

        $freshUser = new self;

        $freshUser->vk_id =         (int) $user['uid'];
        $freshUser->fname =         $user['first_name'];
        $freshUser->lname =         $user['last_name'];
        $freshUser->email =         $user['email'];
        $freshUser->pic   =         $user->has('photo_200') ? $user['photo_200'] : null;
        $freshUser->pic_small =     $user->has('photo_50') ? $user['photo_50'] : null;
        $freshUser->sex =           $user->has('sex') ? $user['sex'] : null;
        $freshUser->password =      Hash::make($freshUser->vk_id . $freshUser->email);

        if ($freshUser->save())
            return $freshUser;
        return false;
    }
}