<?php 

namespace App\Classes;
 
define("API_BASE", "https://api.instagram.com/v1");

/*
|--------------------------------------------------------------------------
| Instagram class
|
| Classes to handle interactions with instagram 
|
*/

class Instagram{
    
    /**
	 * call_api
	 *
	 * Call API wrapper function
	 * @param	string 	endpoint of instagram API
	 * @param	array 	params to be passed in API
	 * @return 	string	returned json data
	 *
	 */ 
	 public function call_api($endpoint, $params = array()) {
		
	 	return call_user_func_array(array($this, $endpoint), array($params));
	
	 }
	 
    /**
	 * users_media_recent
	 *
	 * Get user's recent media published 
	 * @param	array 	params to be passed in API
	 * @return 	string	returned json data
	 *
	 */
	 private function users_media_recent($params = array()) {
	 	 	
	 
	 	$client_id 	= \Config::get('custom.instagram_client_id');
	 	$user_id 	= \Config::get('custom.instagram_user_id');

		$params['client_id'] = $client_id;
	 
	 	$api = API_BASE.'/users/'.$user_id.'/media/recent/?'.http_build_query($params);
	 
	 	$json_data = file_get_contents($api);

	 	return $json_data;
	 }
    
 
}