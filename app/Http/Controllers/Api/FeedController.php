<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Classes\Media;

class FeedController extends Controller
{

  	public function __construct()
    {
       $this->_media_class = new Media;
    }

	/**
	 * media
	 *
	 * Get sorted media posts
	 * @param	string	sort order mode
	 * @param	int		offset (the last score, excluded)
	 * @return 	string	json format
	 *
	 */
    public function media($mode = 'createdtime', $pagesize = 20, $offset = '')
    {
    	
    	$data = array();
		
		$ids = $this->_media_class->get_sorted_ids($mode, $pagesize, $offset);
		
		
		foreach ($ids as $id) {
				
			//$media_data = (object) array();
			//$media_json = $this->_get_media_cache($id);
				
			$media_data = $this->_media_class->get($id);
			
			if (!is_null($media_data)) {
			
				// for time diff
				$created_time_diff = format_time_diff($media_data->{'created_time'});
				$media_data->{'created_time_diff'} = $created_time_diff;
				//
		
				array_push($data, $media_data);
			}
		}
		
		
		//determine the next offset
		$next_offset = '';
		if (count($data) > 0) {
			$last_media_data = end($data);
		
			switch ($mode) {
		
				case 'createdtime' 	: 	$next_offset = $last_media_data->{'created_time'};
										break;
				case 'like' 	 	: 	$next_offset = $last_media_data->{'likes'}->{'count'};
										break;
				case 'comment' 		: 	$next_offset = $last_media_data->{'comments'}->{'count'};
										break;
				default				:	$next_offset = $last_media_data->{'created_time'};
			}
		}
		//
		
		$return_data = array(	'data'			=> $data,
								'total'			=> count($ids),
								'next_offset'	=> $next_offset,
								);
		
		echo json_encode($return_data);
    	
    }
}