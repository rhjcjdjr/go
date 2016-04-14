<?php

namespace App\Http\Controllers\Ajax;

use Illuminate\Http\Request;

use Auth;
use Input;
use App\User;
use Redirect;
use Response;
use Exception;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\SearchYou;


class SearchYouController extends Controller
{
	use \App\Http\Controllers\Traits\AjaxResponseTrait;
	use \App\Http\Controllers\Utils\AjaxProcessSearchYouInputTrait;


	/**
	 *	add new search request
	 *
	 */
	public function new()
	{
		$i = Input::all();
		$insert = [];

		//	check age
		if (null !== ($age = Input::get('age', null))) {
			if (isset($age['age_from']) && isset($age['age_to'])) {
				if ($this->checkAge($age['age_from'], $age['age_to'])) {
					//	return Response::json(['a' => 'age is ok']);
					$insert['age_to'] = $age['age_to'];
					$insert['age_from'] = $age['age_from'];
				}
			}
		}

		//	check growth
		if (null !== ($growth = Input::get('growth', null))) {
			if (isset($growth['growth_from']) && isset($growth['growth_to'])) {
				if ($this->checkGrowth($growth['growth_from'], $growth['growth_to'])) {
					//	return Response::json(['a' => 'growth is ok']);
					$insert['growth_to'] = $growth['growth_to'];
					$insert['growth_from'] = $growth['growth_from'];
				}
			}
		}

		//	check date
		if (null !== ($date = Input::get('date', null))) {
			if (isset($date['date_from']) && isset($date['date_to'])) {
				if ($timestamps = $this->checkDate($date['date_from'], $date['date_to'], true)) {
					//	return Response::json(['a' => 'date is ok']);
					$insert['date_to'] = $timestamps['date_to'];
					$insert['date_from'] = $timestamps['date_from'];
				}
			}
		}

		//	check sex
		if (null !== ($sex = Input::get('sex', null))) {
			$sex = (int) $sex;
			if ($sex === 1 || $sex === 2) {
				//	return Response::json(['a' => 'sex is ok']);
				$insert['person_sex'] = $sex;
			}
		}

		//	check comment
		if (null !== ($comment = Input::get('comment', null))) {
			$comment = (string) $comment;
			//	return Response::json(['a' => 'sex is ok']);
			$insert['comment'] = $comment;
		}

		//	check polygon coords
		if (null !== ($polygons = Input::get('polygons', null))) {
			$insert['polygon_coords_serialized'] = $polygons;
		}

		//	check visibility
		if ($visibility = Input::get('visibility', false)) {
			$insert['visible'] = (bool) $visibility;
		}

		$insert['oid'] = Auth::user()->id;
		$insert['ts'] = time();

		try {
			SearchYou::create($insert);
		} catch(Exception $e) {
			return $this->respondError([
				'message' => 'Unknown error occured while processing',
			]);
		}

		return $this->respondOk([
			'input' => $insert,
		]);
	}

	/**
	 *	mine search requests
	 *
	 *	!WARNING: No select limit is set. To be fixed.
	 */
	public function mineSearches() {
		$myId = Auth::user()->id;

		$searches = SearchYou::where('oid', '=', $myId)
						->orderBy('ts')
							->get();

		return $this->respondOk([
			'searches' => $searches,
		]);
	}
}
