<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\User;
use Redirect;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth\Vk\VkAuthController as VkAuth;



class SearchController extends Controller
{
	/**
	 *	show search page
	 *
	 */
	public function search()
	{
		return view('search')->with(['vk_app_id' => VkAuth::APP_ID]);
	}

	/**
	 *	show mine searches page
	 *
	 */
	public function searchOf()
	{
		return view('search-of')->with(['vk_app_id' => VkAuth::APP_ID]);
	}
}
