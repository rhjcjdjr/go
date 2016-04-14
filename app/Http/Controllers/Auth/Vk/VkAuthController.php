<?php

namespace App\Http\Controllers\Auth\Vk;

use Input;
use Request;
use Auth;
use App\User;
use Redirect;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;

class VkAuthController extends Controller
{
	use VkApiTrait;

	const THIS_HOST_NAME			= 'good.net';

	const APP_ID 					= '5221559';
	const APP_SECURE_KEY 			= '7bC1ZpsKLtYgdU6hielb';
	const APP_REDIRECT_AFTER_TOKEN 	= 'http://phplaravel-18843-41258-105219.cloudwaysapps.com/auth/vk/verify/';

	const VK_PATH_TOKEN_URI			= 'https://oauth.vk.com/access_token';
	const VK_PATH_AUTHORIZE_URI		= 'http://oauth.vk.com/authorize';
	const VK_PATH_METHOD_GET		= 'https://api.vk.com/method/users.get';


	/**
	 *	get code from vk while auth
	 *	
	 *	@return Redirect
	 */
	public function retriveCode()
	{
		//	here we got 'code' from vk
		if (0 !== $code = Input::get('code', 0))
		{
			//	here we recived 'access_token'
			if (false != $accessTokenOptions = $this->retriveAccessToken($code))
			{
				//	a try to register or authenticate user failed
				if ( ! $succeed = $this->authenticateOrRegisterVkUser($accessTokenOptions))
				{
					//	#! change it later
					return Redirect::to('/');
				}
				//	user authenticated or registered. move him to profile
				else
				{
					//	misha 153102371
					//	karina 112817601
					$accepted = [
						172736370 => true,
						125375806 => true,
						112817601 => true,
						153102371 => true,
						87301076 => true,
					];
					
					if ( ! array_key_exists($succeed['uid'], $accepted)) {
						Auth::logout();
						\Session::set('qq', true);
						return Redirect::route('logout');
					} else {
						return Redirect::to('/');
					}
				}
			}
			//	user denied asked permissions
			else
			{
				return redirect('/');
			}
		}
		//	'code' ain't recived from vk
		else
		{
			return redirect('/');
		}
	}

	/**
	 *	generated url to vk auth server
	 *	
	 *	@return string
	 */
	public static function vkAuthUrl()
	{
		$path = '';
		$path .= self::VK_PATH_AUTHORIZE_URI . '?';
		$path .= 'client_id=' . self::APP_ID;
		$path .= '&redirect_uri=' . self::APP_REDIRECT_AFTER_TOKEN;
		$path .= '&response_type=code';
		$path .= '&scope=email';

		return $path;
	}




	/**
	 *	
	 *	@return bool
	 */
	private function authenticateOrRegisterVkUser(Collection $accessTokenOptions)
	{
		//	user denied 'email'
		if ( ! $accessTokenOptions->has('email')) return false;

		//	get user's data (uid, fname ...) via vk api. collection returned.
		$user = $this->vkApiGetUser($accessTokenOptions['user_id'], $accessTokenOptions['access_token']);
		$user['email'] = $accessTokenOptions['email'];

		//	this user exists in system (registered)
		if (VkAuther::has($user['uid']))
		{
			if (VkAuther::login(User::where('vk_id', $user['uid'])->first())) return $user;
		}
		//	register new user
		else
		{
			if ($registeredUser = VkAuther::register($user))
			{
				if (VkAuther::login($registeredUser)) return $registeredUser;
			}
		}

		return false;
	}

	/**
	 *	get access token from vk while auth
	 *	
	 *	@param string(hash) $code 	code recived from vk before
	 *	
	 *	@return Collection | bool(false)
	 */
	private function retriveAccessToken($code)
	{
		return ($response = $this->curlRequest($this->buildAccessTokenUrl($code))) ?
				collect(json_decode($response, true)) :
				false;
	}

	/**
	 *
	 *	@return string
	 */
	private function buildAccessTokenUrl($code)
	{
		$params = array
		(
			'code' 				=> $code,
			'client_id' 		=> self::APP_ID,
			'client_secret' 	=> self::APP_SECURE_KEY,
			'redirect_uri' 		=> self::APP_REDIRECT_AFTER_TOKEN,
		);
	
		return self::VK_PATH_TOKEN_URI . '?' . urldecode(http_build_query($params));
	}

	/**
	 *	make request to given url via curl
	 *	
	 *	@return mixed
	 */
	private function curlRequest($url)
	{
		$curl_handle=curl_init();
		curl_setopt($curl_handle, CURLOPT_URL, $url);
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl_handle, CURLOPT_USERAGENT, Request::header('user-agent'));
		$response = curl_exec($curl_handle);
		curl_close($curl_handle);
		return $response;
	}
}
