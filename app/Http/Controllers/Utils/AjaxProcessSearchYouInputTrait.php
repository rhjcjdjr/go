<?php

namespace App\Http\Controllers\Utils;

use Illuminate\Http\Request;


use Auth;
use Input;
use DateTime;
use App\User;
use Response;
use Eloquent;
use App\Http\Controllers\Controller;

trait AjaxProcessSearchYouInputTrait
{
	private function checkAge($from, $to) {
		if ( ! $from || ! $to) return false;
		
		$from = (int) $from;
		$to = (int) $to;

		if ($from < 0 || $from > 101) return false;
		if ($to < 0 || $to > 101) return false;

		return true;
	}

	private function checkGrowth($from, $to) {
		if ( ! $from || ! $to) return false;
		
		$from = (int) $from;
		$to = (int) $to;

		if ($from < 0 || $from > 190) return false;
		if ($to < 0 || $to > 190) return false;

		return true;
	}

	private function checkDate($from, $to, $toTimestamp = true) {
		if ( ! $from || ! $to) return false;
		
		$from = DateTime::createFromFormat('H:i d/m/Y', $from);
		$to = DateTime::createFromFormat('H:i d/m/Y', $to);

		if (false == $from || false == $to) return false;

		if ($toTimestamp) {
			return [
				'date_from' => $from,
				'date_to' => $to
			];
		}

		return true;
	}
}
