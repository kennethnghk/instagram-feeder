<?php

namespace App\Http\Controllers\Tool;

use Redis;
use App\Http\Controllers\Controller;
use App\Classes\Instagram;
use App\Classes\Media;

define("IMPORT_LIMIT", 200); // total number of posts to be imported
define("IMPORT_STEP", 20);  // retrive count each time

class MediaController extends Controller
{
    /**
     * Reset all data in redis
     *
     * @param  
     * @return 
     */
    public function postReimportdata()
    {
    	
    	// flushall data
    	Redis::flushall();

		// import media posts
		$instagram_class = new Instagram;

		$max_id = '';
		$media_count = 0;
		
		while ($media_count < IMPORT_LIMIT) {
			
			echo "media_count: $media_count, $max_id <br />";
			
			$params = array( 'count'   => IMPORT_STEP);
			
			if ($max_id != '') 
				$params['max_id'] = $max_id;
			
			$json_data = $instagram_class->call_api('users_media_recent', $params);
			list($count, $next_max_id) = $this->_import_data($json_data);
			
			if ($count > 0) {
				$max_id = $next_max_id;
				$media_count += $count;
			} else {
				break;
			}
		}
		//

    }
    
    /**
	 * _import_data
	 *
	 * Import data from json
	 * @param	string 	json from API
	 * @return 	int		number of media imported
	 * @return 	string	next media id for impport
	 *
	 */
	private function _import_data($json_data = '') 
	{
		// no data
		if ($json_data == '') 
			return array(0, '');

		$media_class = new Media;
		$feed_data = json_decode($json_data);
	
		// looping posts
		$count = 0;
	    if (isset($feed_data->{'data'}) && is_array($feed_data->{'data'})) {
	    	
	    	foreach ($feed_data->{'data'} as $media) {
	    		
	    		// for speed up, eliminated some unused attributes
	    		unset($media->{'attribution'});
	    		unset($media->{'tags'});
	    		unset($media->{'location'});
	    		unset($media->{'filter'});
	    		unset($media->{'comments'}->{'data'});
	    		unset($media->{'likes'}->{'data'});
	    		unset($media->{'images'}->{'thumbnail'});
	    		unset($media->{'users_in_photo'});
	    		unset($media->{'caption'}->{'created_time'});
	    		unset($media->{'caption'}->{'from'});
	    		unset($media->{'caption'}->{'id'});
	    		unset($media->{'user'});
	    		//
	    		
	    		$media_class->set($media);	
	    		$count++;    		
	    	}
	    	
	    } 
	    //
	    
	    $next_max_id = isset($feed_data->{'pagination'}->{'next_max_id'}) ? $feed_data->{'pagination'}->{'next_max_id'} : '';
	    
	    return array($count, $next_max_id);
	}	
}