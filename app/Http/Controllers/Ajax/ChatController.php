<?php

namespace App\Http\Controllers\Ajax;

use Illuminate\Http\Request;


use Auth;
use Input;
use Session;
use App\Chat;
use App\ChatOnline;
use Response;
use Eloquent;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ChatController extends Controller
{
	use \App\Http\Controllers\Traits\AjaxResponseTrait;

	/**
	 *	get list of latest messages 
	 *	
	 *	@return JsonResponse
	 */
	public function messages()
	{
		$limit = Input::get('limit', 10);


		if (Input::has('from') && Input::has('dir'))
		{
			if ((int) Input::get('dir') * 1)
				$messages = Chat::sliceDownFrom(Input::get('from'), $limit);
			else
				$messages = Chat::sliceUpFrom(Input::get('from'), $limit);
		}
		else
		{
			$messages = Chat::orderBy('id', 'desc')->take($limit)->get()->reverse();
		}

		if (count($messages))
		{
			foreach ($messages as $key => $message) {
				if ( ! $message->visible) {
					//	user not loaded, its cool, we don't need him as message is hidden
				} else {
					//	here we loadin' user
					$message->user;
				}
			}

			return $this->respondOk(
						$this->toArray($messages),
						['last' => $messages->last()->id]
			);
		}
		return $this->respondEmpty();
	}


	public function online() {
		if ($active = ChatOnline::active() and count($active)) {
			return $this->respondOk($this->toArray($active));
		}
		return $this->respondEmpty();
	}

	private function toArray($o) {
		$rv = [];
		foreach ($o as $value) {
			$rv[] = $value;
		}
		return $rv;
	}
}
