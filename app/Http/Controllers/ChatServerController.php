<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Input;
use Session;
use App\Chat;
use App\ChatOnline;
use Response;
use Eloquent;
use Exception;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ChatServerController extends Controller {

	/**
	 *	@var int
	 */
	const DELTA = 20;

	/**
	 *	@var int(microseconds)
	 */
	const MIN_DELAY = 10000;

	/**
	 *	@var int
	 */
	const CHUNK_SIZE = 10;

	/**
	 *	@var int(timestamp)
	 */
	private $stamp;

	/**
	 *	@var int
	 */
	private $lastId;

	/**
	 *	@var int
	 */
	private $inputTs;

	/**
	 *	@var int
	 */
	private $outputTs;

	/**
	 *	@var array
	 */
	private $stack;


	/**
	 *	Init
	 */
	public function init($req)
	{
		$req->getSession()->save();

		//	init
		$this->stamp = time();
		$this->outputTs = null;
		$this->stack = [];

		//!	suppose first request (ts isn't set) 
		$this->inputTs = Input::has('ts') ? (int) Input::get('ts') : $this->stamp - 4;
		$this->lastId = Input::has('last') ? $this->setLastId((int) Input::get('last')) : null;
	}

	/**
	 *	listen
	 *	
	 *	@return JsonResponse
	 */
	public function listen(Request $req)
	{
		//	current user
		//	list of those current's is the online users list
		ChatOnline::updateCurrent(-5);

		
		try {
			$this->init($req);
		}
		catch(Exception $e) {
			return $this->responseError('there was an error while processing request');
		}


		while(time() - $this->stamp < self::DELTA)
		{
			try
			{
				if ($response = $this->listening()) return $response;
			}
			catch(Exception $e) {
				return $this->responseError($e->getMessage());
				return $this->responseError('there was an error while processing request');
			}
		}


		return $this->responseEmpty();
	}

	/**
	 *	listening process
	 *	
	 *	@return JsonResponse
	 */
	private function listening()
	{
		//	listen new messages
		if ($messages = $this->tryMessages($this->getLastId()))
		{
			return $this->responseOk('message', $messages);
		}

		//	listen users online state
		$this->tryOnlineUsers();


		//	listen something else...

		usleep(self::MIN_DELAY);
		//	sleep(7);

		return 0;
	}

	/**
	 *	ok response
	 *	
	 *	@param string $type 	type of response data. js event of type $type will be triggerred
	 *							after response returned
	 *	@param array $data 		response data
	 *	
	 *	@return JsonResponse
	 */
	private function responseOk($type, $data) {
		return $this->responseMain([
			'type' => $type,
			'data' => $data,
			'last' => $this->getLastId(),
		]);
	}

	/**
	 *	empty response
	 *	
	 *	@return JsonResponse
	 */
	private function responseEmpty() {
		return $this->responseMain([], 'ok');
	}

	/**
	 *	
	 *	
	 *	@return JsonResponse
	 */
	private function responseError($message = 'an error occurred while processing request') {
		return $this->responseMain([
			'message' => $message
		], 'fail');
	}

	/**
	 *	empty response
	 *	
	 *	@return JsonResponse
	 */
	private function responseMain($responseData = [], $status = 'ok') {
		return Response::json([
			'status' => $status,
			'response' => $responseData,
			'output_ts' => $this->outputTs,
			'input_ts' => $this->inputTs,
			'online' => $this->tryOnlineUsers(),
			'push' => [],
		]);
	}

	/**
	 *	get chat online users
	 *	
	 *	@return JsonResponse
	 */
	private function tryOnlineUsers() {
		if (count($online = ChatOnline::active(self::DELTA + 2)) > 0) {
			return $online;
		} else {
			return [];
		}
	}

	/**
	 *	get chat messages
	 *	
	 *	@param int $messageId 		last message id that was sent
	 *	
	 *	@return JsonResponse
	 */
	private function tryMessages($messageId = null) {

		//	check id
		if (($messageId = $this->checkId($messageId)) === false) {
			$messageId = null;
		}


		//!	can be dangerous
		$ts = $this->inputTs;

		//	if last message id is set than use it to load messages
		if (null !== $messageId) {
			$messages = Chat::where('id', '>', $messageId)->take(self::CHUNK_SIZE)->get();
		} else {
			$messages = Chat::where('ts', '>', $ts)->take(self::CHUNK_SIZE)->get();
		}

		if ($messagesCount = count($messages)) {
			for ($key = 0; $key < $messagesCount; $key++) {
				//	the message was sent by the owner - ignore it
				//	he already has it shown
				if ( (int) $messages[$key]->user->id === (int) Auth::user()->id) {
					unset($messages[$key]);
					continue;
				}

				//	if message invisible than unset the user from response
				if (isset($messages[$key])) {
					if ( (int) $messages[$key]->visible === 0)
						unset($messages[$key]->user);
				}
			}
		}

		$this->outputTs = $this->inputTs;

		if (count($messages)) {
			$this->setLastId($messages->last()->id);
			$this->outputTs = (int) $messages->last()->ts;
			return $messages;
		}
	}

	/**
	 *	check id
	 *	
	 *	not bool, not empty string ..., only int >= 0
	 *	
	 *	@return JsonResponse
	 */
	private function checkId($id) {
		return (is_int($id) && $id >= 0) ? $id : false;
	}

	/**
	 *	set last message id
	 *	
	 *	@return void
	 */
	private function setLastId($id) {
		if (($id = $this->checkId($id)) === false) {
			throw new Exception('unable to process because of bad input [id must be int >= 0]');
		}
		return $this->lastId = $id;
	}

	/**
	 *	get last message id
	 *	
	 *	@return int
	 */
	private function getLastId() {
		return $this->lastId;
	}

	/**
	 *	get stack
	 *	
	 *	@return array
	 */
	private function getStack() {
		return $this->stack;
	}

	/**
	 *	get stack
	 *	
	 *	@return array
	 */
	private function setStack($type = 'data', $data = []) {
		if (array_key_exists($type, $this->stack)) {
			$this->stack[$type] = array_merge($this->stack[$type], $data->toArray());
		} else {
			$this->stack[$type] = [$data];
		}
	}
}