<?php

namespace App\Http\Controllers\Ajax;

use Illuminate\Http\Request;


use Auth;
use Input;
use Image;
use App\UploadedAudio;
use App\UploadedVideo;
use App\UploadedImage;
use Session;
use App\Chat;
use App\ChatOnline;
use Response;
use Eloquent;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class UploadController extends Controller {

	use \App\Http\Controllers\Traits\AjaxResponseTrait;


	//	allowed exts
	private $audioExt = ['mp3'];
	private $videoExt = ['mp4'];
	private $imageExt = ['jpg', 'jpeg', 'png'];

	//	allowed sizes
	private $imageSize = 12000 * 1024;
	private $audioSize = 12000 * 1024;
	private $videoSize = 80000 * 1024;

	//	internal path
	private $videoDir;
	private $audioDir;
	private $imageDir;
	private $imageMdDir;
	private $imageMinDir;

	//	url path
	private $videoUrl;
	private $audioUrl;
	private $imageUrl;
	private $imageMdUrl;
	private $imageMinUrl;


	private function init() {
		$this->videoDir = public_path() . '/files/video/';
		$this->audioDir = public_path() . '/files/audio/';
		$this->imageDir = public_path() . '/files/image/';
		$this->imageMdDir = public_path() . '/files/image/md/';
		$this->imageMinDir = public_path() . '/files/image/min/';

		$this->videoUrl = url('/files/video') . '/';
		$this->audioUrl = url('/files/audio') . '/';
		$this->imageUrl = url('/files/image') . '/';
		$this->imageMdUrl = url('/files/image/md') . '/';
		$this->imageMinUrl = url('/files/image/min') . '/';
	}

	public function upload($type, Request $req) {
		$response = null;

		$this->init();
		
		if ( ! $this->checkSimple($req))
			return $this->respondError('no file with any of (audio, image, video) names were given');

		switch ($type) {
			case 'audio': $response = $this->audio($req, $req->file('audio')); break;
			case 'video': $response = $this->video($req, $req->file('video')); break;
			case 'image': $response = $this->image($req, $req->file('image')); break;
			default: $response = $this->respondError('invalid url given [ajax/upload/(audio|video|image) are accepted]');
		}

		return $response;
	}

	public function image($req, $image) {

		//	check if image is an image
		if ( ! $image->isValid() || ! $imageInfo = $this->checkImage($image)) {
			return $this->respondError('invalid image file given');
		}

		try {
			$moved = $image->move($this->imageDir, $imageInfo['name_created']);
			
			//	make min copy of image
			$min = Image::make($moved);
			$min->resize(50, null, function($constraint) {
				$constraint->aspectRatio();
				$constraint->upsize();
			});
			//	md
			$md = Image::make($moved);
			$md->resize(200, null, function($constraint) {
				$constraint->aspectRatio();
				$constraint->upsize();
			});

			if ( ! file_exists($this->imageMdDir)) {
				mkdir($this->imageMdDir, 0777, true);
			}
			if ( ! file_exists($this->imageMinDir)) {
				mkdir($this->imageMinDir, 0777, true);
			}

			$md->save($this->imageMdDir . $moved->getBasename(), 100);
			$min->save($this->imageMinDir . $moved->getBasename(), 100);
		} catch(Exception $e) {
			return $this->respondError('unable to save the file for some reason');
		}


		$sendBack = $imageInfo +
					['url' => $this->imageUrl.$moved->getBasename()] +
					['url_md' => $this->imageMdUrl.$moved->getBasename()] +
					['url_min' => $this->imageMinUrl.$moved->getBasename()];

		UploadedImage::tie($sendBack['url']);
		UploadedImage::tie($sendBack['url_md']);
		UploadedImage::tie($sendBack['url_min']);
		
		return $this->respondOk($sendBack);
	}

	public function audio($req, $audio) {

		//	check if image is an image
		if ( ! $audio->isValid() || ! $audioInfo = $this->checkAudio($audio)) {
			return $this->respondError('invalid audio file given');
		}

		try {
			$moved = $audio->move($this->audioDir, $audioInfo['name_created']);
		} catch(Exception $e) {
			return $this->respondError('unable to save the file for some reason');
		}


		$sendBack = $audioInfo + ['url' => $this->audioUrl . $moved->getBasename()];

		UploadedAudio::tie($sendBack['url']);
		
		return $this->respondOk($sendBack);
	}

	public function video($req, $video) {

		//	check if image is an image
		if ( ! $video->isValid() || ! $videoInfo = $this->checkVideo($video)) {
			return $this->respondError('invalid video file given');
		}

		try {
			$moved = $video->move($this->videoDir, $videoInfo['name_created']);
		} catch(Exception $e) {
			return $this->respondError('unable to save the file for some reason');
		}
		
		$sendBack = $videoInfo + ['url' => $this->videoUrl.$moved->getBasename()];

		UploadedVideo::tie($sendBack['url']);

		return $this->respondOk($sendBack);
	}



	private function checkAudio($audio) {

		if ((int)$audio->getClientSize() > $this->audioSize) return false;

		//	server uploaded ext
		$ext = $audio->getExtension();
		$mime = $audio->getClientMimeType();
		$name = $audio->getClientOriginalName();
		$clientExt = $audio->getClientOriginalExtension();

		if ( ! in_array($clientExt, $this->audioExt)) return false;

		return [
			'client_ext' => $clientExt,
			'name_created' => date('d_m_Y_') . md5(time() . $name) . '.' . $clientExt,
		];
	}

	private function checkVideo($video) {

		if ((int)$video->getClientSize() > $this->videoSize) return false;

		//	server uploaded ext
		$ext = $video->getExtension();
		$mime = $video->getClientMimeType();
		$name = $video->getClientOriginalName();
		$clientExt = $video->getClientOriginalExtension();

		if ( ! in_array($clientExt, $this->videoExt)) return false;

		return [
			'client_ext' => $clientExt,
			'name_created' => date('d_m_Y_') . md5(time() . $name) . '.' . $clientExt,
		];
	}

	private function checkImage($image) {

		if ((int)$image->getClientSize() > $this->imageSize) return false;

		//	server uploaded ext
		$ext = $image->getExtension();
		$mime = $image->getClientMimeType();
		$name = $image->getClientOriginalName();
		$clientExt = $image->getClientOriginalExtension();

		//	simple check for image
		if ( ! in_array($clientExt, $this->imageExt) || ! $imageCheck = Image::make($image)) return false;

		return [
			'mime' => $imageCheck->mime(),
			'width' => $imageCheck->width(),
			'height' => $imageCheck->height(),
			'client_ext' => $clientExt,
			'name_origin' => $name,
			'name_created' => date('d_m_Y_') . md5(time() . $name) . '.' . $clientExt,
		];
	}





	private function checkSimple($req) {
		return (bool) $this->checkHasAnyFile($req);
	}

	private function checkHasAnyFile($req) {
		$has = false;
		if ($req->hasFile('image')) $has = 'image';
		if ($req->hasFile('audio')) $has = 'audio';
		if ($req->hasFile('video')) $has = 'video';
		return $has;
	}
}