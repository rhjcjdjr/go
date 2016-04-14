<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use Auth;
use Input;
use App\User;
use Response;
use Eloquent;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{
	/**
	 *	users
	 *	
	 *	@return JsonResponse
	 */
	public function user(Request $request)
	{
		//	sleep(10);

		if (null != $id = $request->input('id')) {
			if ($id === 'me') {
				return Response::json(Auth::user());
			}
			if ($this->checkId($id)) {
				return Response::json(User::where('id', '=', $id)->first());
			}
		}

		return $this->badInputResponse();
	}

	/**
	 *	user
	 *	
	 *	@return JsonResponse
	 */
	public function users()
	{
		if (0 != $id = Input::get('id', 0)) {
			$identifiers = $this->splitId($id);
			if ($identifiers->count() > 0) {
				return User::whereIn('id', $identifiers->toArray())->get();
			}
		}

		return $this->badInputResponse();
	}




	/**
	 *	split id string with ','
	 *	
	 *	@return bool(false) | Collection
	 */
	private function splitId($id) {
		if ( ! is_string($id))
			return false;
		return collect(explode(',', $id))->filter(function($id) {
			return (bool) $this->checkId($id);
		});
	}

	/**
	 *	check if given id is valid
	 *	
	 *	@return int|bool
	 */
	private function checkId($id) {
		$id = (int) $id;
		if (is_int($id) && $id > 0) return $id;
		return false;
	}

	/**
	 *	@return JsonResponse
	 */
	private function badInputResponse($message = null) {
		$message = (string) $message;
		if (empty($message)) $message = 'unable to process request because of bad input';
		return Response::json([
			'status' => 'fail',
			'message' => $message,
		], 422);
	}
}
