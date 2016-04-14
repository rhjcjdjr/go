<?php


/*
 *
 *	Problems:
 *
 *		done: - :		2. In case of big delay messages are delivered not in the order
 
 *		done: + :		1. Can not logout
 		comment:		(After Auth::logout() you are to return redirect(), not Redirect::route(). At second case request
 						somehow is not finished, and session not destroyed.)

 *		done: + :		3. Mobile view issue. Top menu displayed incorrectly
 *		done: + :		4. Anonimous message pending status animation is broken
 *
 */

use App\Http\Controllers\Auth\Vk\VkAuthController as VkAuth;

$vkAppId = VkAuth::APP_ID;


/**
 *	web middleware group
 *
 */
Route::group(['middleware' => 'web'], function()
{

	/**
	 *
	 *	logout
	 */
	Route::get('logout', ['as' => 'logout', 'uses' => function()
	{
		if (Session::has('qq')) {
			$qq = true;
		} else {
			$qq = false;
		}
		
		Session::flush();
        	Auth::logout();

	    return redirect('login')->with('qq', $qq);
	}]);

	/**
	 *
	 *	index '/'
	 */
	Route::get('/', ['as' => 'home', 'uses' => function()
	{
		if (Auth::check())
			return Redirect::route('chat');
	    return Redirect::route('login');
	}]);

	/**
	 *
	 *	signup
	 */
	Route::get('login', ['middleware' => 'guest', 'as' => 'login', 'uses' => function()
	{
		return view('login')->with(['vk' => VkAuth::vkAuthUrl()]);
	}]);

	/**
	 *
	 *	profile
	 */
	//	Route::get('id{id}', ['middleware' => 'auth', 'as' => 'profile', 'uses'=>'ProfileController@profile']);

	/**
	 *
	 *	search
	 */
	Route::get('search', ['middleware' => 'auth', 'as' => 'search', 'uses'=>'SearchController@search']);

	/**
	 *
	 *	mine search
	 */
	Route::get('search-of', ['middleware' => 'auth', 'as' => 'search-of', 'uses'=>'SearchController@searchOf']);


	/**
	 *
	 *	chat
	 */
	Route::get('chat', ['middleware' => 'auth', 'uses' => 'ChatController@chat', 'as' => 'chat']);



	/**
	 *	Auth group
	 *
	 *	prefix "auth"
	 *
	 *	namespace "Auth"
	 */
	Route::group(['prefix' => 'auth', 'namespace' => 'Auth'], function()
	{
		/**
		 *	Vk group
		 *
		 *	prefix "vk"
		 *
		 *	namespace "Vk"
		 */
		Route::group(['prefix' => 'vk', 'namespace' => 'Vk'], function()
		{
			//	verify
			Route::get('verify', ['middleware' => 'guest', 'uses' => 'VkAuthController@retriveCode']);
		});
	});


	/**
	 *	Ajax group
	 *
	 *	prefix "ajax"
	 *
	 *	namespace "Ajax"
	 */
	Route::group(['prefix' => 'ajax', 'namespace' => 'Ajax', 'middleware' => 'auth'], function()
	{
		Route::get('chat/messages', ['uses' => 'ChatController@messages']);
		Route::get('chat/online', ['uses' => 'ChatController@online']);

		Route::post('search/new', ['uses' => 'SearchYouController@new']);
		Route::post('search/mine', ['uses' => 'SearchYouController@mineSearches']);
	});
});




Route::group(['prefix' =>'chat', 'middleware' => 'web'], function()
{
	/**
	 *
	 *	post new chat message
	 */
	Route::post('new', ['middleware' => 'auth', 'uses' => 'ChatController@create']);

	/**
	 *
	 *	chat push server
	 */
	Route::match(['post', 'head'], 'pool', ['middleware' => 'auth', 'uses' => 'ChatServerController@listen']);
});


Route::group(['prefix' =>'api', 'middleware' => ['web', 'auth']], function()
{
	/**
	 *
	 *	api
	 */
	Route::get('users', ['uses' => 'ApiController@users']);
});
