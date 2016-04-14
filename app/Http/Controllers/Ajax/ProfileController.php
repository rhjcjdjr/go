<?php

namespace App\Http\Controllers\Ajax;

use Illuminate\Http\Request;

use Auth;
use App\UserModeration;
use App\User;
use Redirect;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
	use \App\Http\Controllers\Traits\AjaxResponseTrait;
	use \App\Http\Controllers\Utils\AjaxEditProfileTrait;

	/**
	 *	edit profile via ajax
	 *
	 *	@return JsonResponse
	 */
	public function edit(Request $req)
	{
		$moderationProfile = [];
		$updateProfile = [];


		if ($inputFields = $req->input('fields'))
		{
			if (isset($inputFields['profile']) && $profile = $inputFields['profile']) {
				//	first name
				if (isset($profile['fname'])) {
					$this->checkFirstName($profile['fname']) && ($moderationProfile['fname'] = $profile['fname']);
				}
				//	last name
				if (isset($profile['lname'])) {
					$this->checkLastName($profile['lname']) && ($moderationProfile['lname'] = $profile['lname']);
				}
				//	sex
				if (isset($profile['sex'])) {
					if (($sex = $this->checkSex($profile['sex'])) || $sex === 0) {
						$updateProfile['sex'] = $sex;
					}
				}
			}

			if ( ! empty($moderationProfile)) {
				UserModeration::firstOrCreate(['uid' => Auth::user()->id])->update($moderationProfile);
			}

			if ( ! empty($updateProfile)) {
				User::where('id', '=', Auth::user()->id)->update($updateProfile);
			}

			return $this->respondOk([
				'updated' => $updateProfile,
				'moderation' => $moderationProfile,
			]);
		}

		return $this->respondEmpty();
	}

}
