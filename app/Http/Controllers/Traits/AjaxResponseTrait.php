<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;


use Auth;
use Input;
use App\User;
use Response;
use Eloquent;
use App\Http\Controllers\Controller;

trait AjaxResponseTrait
{
	/**
	 *	good response
	 *
	 *	@return JsonResponse
	 */
	private function respondOk($data, $add =[]) {
		return $this->respond($data, $add, 'ok');
	}

	/**
	 *	good response
	 *
	 *	@return JsonResponse
	 */
	private function respondError($data, $add = []) {
		return $this->respond($data, $add, 'fail');
	}

	/**
	 *	good response
	 *
	 *	@return JsonResponse
	 */
	private function respondEmpty($add = []) {
		return $this->respond([], $add, 'ok');
	}

	/**
	 *	main response
	 *
	 *	@param ...
	 *
	 *	@return JsonResponse
	 */
	private function respond($data, $add, $status) {
		return Response::json([
			'response' => [
				'data' => $data,
				'plus' => $add,
			],
			'status' => $status,
		]);
	}

}
