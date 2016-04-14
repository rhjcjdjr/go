<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\User;
use Redirect;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
	public function profile($id)
	{
		$owner = Auth::user();

		//	owner's page
		if ($owner->id == $id) {
			$user = $owner;
		} else {
			if ( ! $user = User::where('id', '=', $id)->first())
			{
				//	given id have no user. so 404?
				return abort(404);
			}
		}

		return view('profile')
					->with('mine', (int) $owner->id === (int) $id)
					->with('user', $user)
					->with('owner', $owner);
	}
}
