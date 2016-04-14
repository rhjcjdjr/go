<?php

namespace App\Http\Controllers\Utils;

use Illuminate\Http\Request;


use Auth;
use Input;
use App\User;
use Response;
use Eloquent;
use App\Http\Controllers\Controller;

trait AjaxEditProfileTrait
{
	private function checkFirstName($arg) {
		return preg_match('/^[a-zA-Zа-яА-ЯёЁ-]{3,40}$/iu', trim($arg));
	}

	private function checkLastName($arg) {
		return preg_match('/^[a-zA-Zа-яА-ЯёЁ-]{3,40}$/iu', trim($arg));
	}

	private function checkSex($arg) {
		//	sent like text
		if (preg_match('/^male|female$/i', trim($arg))) {
			if ($arg == 'female') return 1;
			if ($arg == 'male') return 2;
		}
		//	sent like values
		if ((int)$arg == 1) return 1;
		if ((int)$arg == 2) return 2;
		//	...
		return 0;
	}
}
