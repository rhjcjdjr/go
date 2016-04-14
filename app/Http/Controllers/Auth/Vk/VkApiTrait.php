<?php

namespace App\Http\Controllers\Auth\Vk;

trait VkApiTrait
{
	/**
	 *	get user via vk api
	 *
	 *	@param int $vkId			user's vk id
	 *	@param string(hash) $token 	access_token from vk server
	 *
	 *	@return bool(false) | Collection
	 */
    protected function vkApiGetUser($vkId, $token)
    {
		$params = array
		(
			'uids'         => $vkId,
			'fields'       => 'uid,first_name,last_name,photo_50,photo_200,sex',
			'access_token' => $token
		);
		
		$url = self::VK_PATH_METHOD_GET . '?' . urldecode(http_build_query($params));

		if ($response = $this->curlRequest($url))
		{
			$response = collect(json_decode($response, true));

			return ($response->has('response') && count($response['response']) == 1) ?
					collect($response['response'][0]) :
					false;
		}

		return false;
    }
}