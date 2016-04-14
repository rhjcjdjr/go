<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use Auth;
use Input;
use Session;
use App\Chat;
use Response;
use Eloquent;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ChatController extends Controller
{
	/**
	 *	show chat page
	 *	
	 *	@return View
	 */
	public function chat()
	{
		return view('chat')->with('owner', Auth::user());
	}

	/**
	 *	post new chat message
	 *	
	 *	@return JsonResponse
	 */
	public function create()
	{
		//	sleep(7);
		//	fields to be not empty
		$required = ['text'];

		$createAttributes = collect([
			'ts' => time(),
			'oid' => Auth::user()->id,
			'text' => Input::get('text', false),
			'visible' => Input::get('visible', 1),
		]);

		foreach($required as $field) {
			//!	BE CAREFULL WITH 'OR' HERE
			if (false === $currentAttr = $createAttributes->get($field, false) or empty($currentAttr)) {
				return $this->unableToCreateMessageResponse();
			}
		}

		if ($messageCreated = Chat::create($createAttributes->toArray()))
			return $this->messageCreatedResponse($messageCreated);
		return $this->unknownErrorWhileCreatingMessageResponse();
	}





	/**
	 *	respond ok
	 *	
	 *	@return JsonResponse
	 */
	private function messageCreatedResponse(Eloquent $messageCreated) {
		$messageCreated['user'] = Auth::user();
		return Response::json([
			'status' => 'ok',
			'message' => $messageCreated,
			'input' => Input::all(),
		]);
	}

	/**
	 *	respond with error
	 *	
	 *	@return JsonResponse
	 */
	private function unableToCreateMessageResponse() {
		return Response::json([
			'status' => 'fail',
			'message' => 'unable to create message because of bad input',
			'input' => Input::all(),
		], 422);
	}

	/**
	 *	respond with error
	 *	
	 *	@return JsonResponse
	 */
	private function unknownErrorWhileCreatingMessageResponse() {
		return Response::json([
			'status' => 'fail',
			'message' => 'unable to create message because of unknown error',
			'input' => Input::all(),
		], 500);
	}
}
