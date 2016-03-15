<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Classes\Media;

class UserController extends Controller
{

  	public function __construct()
    {
       $this->_media_class = new Media;
    }


	/**
	 * Output_data
	 *
	 * Output data of feed
	 * @param	int		success (1) or failed (0)
	 * @param	string	error msg
	 * @param	array	data to return 
	 * @return 	string	json format return
	 *
	 */	
	public function output_data ($success = 0, $errmsg = '', $data = array()) {
		
		$ret_data = array( 'success' => $success);
		
		if ($errmsg != '') 
			$ret_data['errmsg'] = $errmsg;
		
		if (!empty($data)) 
			$ret_data = array_merge($ret_data, $data);
		
		return json_encode($ret_data);
	}


	/**
	 * postAdd
	 *
	 * Add fav of a user
	 * @param	string	username
	 * @param	string	instagram media id
	 * @return 	int		success (1) or failed (0)
	 *
	 */
    public function postAddfav(Request $request)
    { 
    	$username 	= $request->input('username');
    	$id 		= $request->input('id');
    	
    	if ($username == '') 
    		return $this->output_data(0, 'No username input');

    	if ($id == '') 
    		return $this->output_data(0, 'No id input');    		
    	
    	$media_data = $this->_media_class->get($id); // check media exist
    	if (isset($media_data->{'id'})) {
    		
    		$ret = $this->_media_class->set_fav($username, $id);
    		
    		$ids = $this->_media_class->get_user_fav_ids($username, 'all'); // get updated list
    		
    		return $this->output_data(1, '', array('data' => $ids));   
    								  
    	} else {
    		return $this->output_data(0, 'No such id');  
    	}
    }

	/**
	 * postRemovefav
	 *
	 * Add fav of a user
	 * @param	string	username
	 * @param	string	instagram media id
	 * @return 	int		success (1) or failed (0)
	 *
	 */
    public function postRemovefav(Request $request)
    {
    	$username 	= $request->input('username');
    	$id 		= $request->input('id');
    	
    	if ($username == '') 
    		return $this->output_data(0, 'No username input');

    	if ($id == '') 
    		return $this->output_data(0, 'No id input'); 
    	
    	$media_data = $this->_media_class->get($id); // check media exist
    	if (isset($media_data->{'id'})) {
    		
    		$ret = $this->_media_class->unset_fav($username, $id);
    		    		
    		$ids = $this->_media_class->get_user_fav_ids($username, 'all'); // get updated list
    		
			return $this->output_data(1, '', array('data' => $ids));  
    		
    	} else {
    		return $this->output_data(0, 'No such id'); 
    	}
    }

	/**
	 * getFavids
	 *
	 * Get fav media ids of a user, according to addtime desc
	 * @param	string	username
	 * @return 	string	ids in json
	 *
	 */
    public function postFavids(Request $request)
    {
    	
    	$username 	= $request->input('username');
    	
    	if ($username == '') 
    		return $this->output_data(0, 'No username input');
		
    	$ids = $this->_media_class->get_user_fav_ids($username, 'all');
    	
    	return $this->output_data(1, '', array('data' => $ids));  
    }
    
    /**
	 * postFavmedia
	 *
	 * Get sorted media posts of user fav
	 * @param	string	sort order mode
	 * @param	int		offset (the last score, excluded)
	 * @return 	string	json format
	 *
	 */
    public function postFavmedia(Request $request, $pagesize = 20, $offset = '')
    {
    	
    	$username 	= $request->input('username');    	
    	if ($username == '') 
    		return $this->output_data(0, 'No username input');
    	
    	// manipulate data
    	$data = array();
		$ids = $this->_media_class->get_user_fav_ids($username, $pagesize, $offset);
		foreach ($ids as $id) {
								
			$media_data = $this->_media_class->get($id);
			
			if (!is_null($media_data)) {
			
				// for time diff
				$created_time_diff = format_time_diff($media_data->{'created_time'});
				$media_data->{'created_time_diff'} = $created_time_diff;
				//
		
				array_push($data, $media_data);
			}
		}
		//
		
		
		//determine the next offset
		$next_offset = '';
		if (count($data) > 0) {
			$last_media_data = end($data);
			$next_offset = $this->_media_class->get_fav_addtime($username, $last_media_data->{'id'});
		}
		//
		
		$return_data = array(	'data'			=> $data,
								'total'			=> count($ids),
								'next_offset'	=> $next_offset,
								);
		
		return $this->output_data(1, '', $return_data);  
    	
    }
}