<?php 

namespace App\Classes;

use Redis;

/*
|--------------------------------------------------------------------------
| Media class
|
| Classes to handle media data
|
*/

class Media{

	private $_media_key   			= 'media';
	private $_createdtime_rank_key  = 'createdtime_rank';
	private $_like_rank_key  		= 'like_rank';
	private $_comment_rank_key  	= 'comment_rank';
	private $_user_fav_key   		= 'fav';
    
    /**
	 * _get_media_key
	 *
	 * get the redis key for media data
	 * @param   string	instagram media id (if applicable)
	 * @return 	string	redis key
	 *
	 */
    private function _get_media_key($id = '') {
    
    	if ($id != '') {
    		return $this->_media_key.'-'.$id;
    	} else {
    		return '';
    	}
	}

    /**
	 * _get_user_fav_key
	 *
	 * get the redis key for user_fav data
	 * @param   string	username
	 * @return 	string	redis key
	 *
	 */
    private function _get_user_fav_key($username = '') {
    
    	if ($username != '') {
    		return $this->_user_fav_key.'-'.md5($username);
    	} else {
    		return '';
    	}
	}
	/**
	 * set
	 *
	 * Set media data into redis
	 * @param	object	object data according to instagram media structure
	 * @return 	string	instagram media id
	 *
	 */
	 public function set($data) {
		
		    	
    	$id  = $data->{'id'};
    	
    	if ($id != '') {
    	
    		// set media data
    		$key = $this->_get_media_key($id);
    		$json_data = json_encode($data);
    	
    		$ret = Redis::set($key, $json_data);
						
			if ($ret != 'OK') {
				return 0;
			}
			//
			
			// set created_time sorted list for sorting
			$ret = Redis::zadd($this->_createdtime_rank_key, $data->{'created_time'}, $id);
			//
			
			// set like count sorted list for sorting
			$ret = Redis::zadd($this->_like_rank_key, $data->{'likes'}->{'count'}, $id);
			//

			// set comment count sorted list for sorting
			$ret = Redis::zadd($this->_comment_rank_key, $data->{'comments'}->{'count'}, $id);
			//			
			
			
		}
			
    	
    	return $id;
	
	 }

	/**
	 * get
	 *
	 * Get a piece of media data
	 * @param	string	instagram media id
	 * @return 	object	data object of a media
	 *
	 */
	 public function get($id) {
		
		
    	if ($id != '') {
    	
    		// set media data
    		$key = $this->_get_media_key($id);
    		
    		$json_data = Redis::get($key);
						
			if ($json_data != '') {
				return json_decode($json_data);
			}
		}
			
    	return (object) array();
	
	 }


	/**
	 * get_sorted_ids
	 *
	 * get media ids, sorted by mode
	 * @param	string	sort order mode
	 * @param	int		number of ids returned
	 * @param	int		offset score
	 * @return 	array	instagram media id
	 *
	 */
    function get_sorted_ids($mode = 'createdtime', $count = 10, $offset = 0) {
        
    	// define redis key
    	$key = '';
    	switch($mode) {
    		
    		case 'createdtime' 	: 	$key = $this->_createdtime_rank_key;
    								break;
    		case 'like' 		: 	$key = $this->_like_rank_key;
    								break;
    		case 'comment' 		: 	$key = $this->_comment_rank_key;
    								break;
    		default				: 	$key = $this->_createdtime_rank_key;
    	
    	}
    	//
    	
    	// fix offset_score for sort desc
    	if ($offset == 0) {
    		$offset = '+inf';
    	} else {
    		$offset = '('.$offset;
    	}
    	//
    	
    	// get from redis
    	$ids = Redis::zrevrangebyscore($key, $offset, '-inf', array('limit'=> array(0,$count)));
    	
    	return $ids;
    }

	/**
	 * set
	 *
	 * Set fav media of a user
	 * @param	string	username
	 * @param	string	media id
	 * @return 	int		success (1) or failed (0)
	 *
	 */
	 public function set_fav($username = '', $id = '') {
		
		if ($username == '') return 0;
		if ($id == '') return 0;
		
		$key = $this->_get_user_fav_key($username);
		
		// check whether it is already set
		$score = Redis::zscore($key, $id);
		if ($score != '') 
			return 1;
		//
		
		//use current time as score
		$ret = Redis::zadd($key, time(), $id); 
		
		if ($ret == 1)
			return 1;
		
		return 0;
	 }

	/**
	 * unset
	 *
	 * unSet fav media of a user
	 * @param	string	username
	 * @param	string	media id
	 * @return 	int		success (1) or failed (0)
	 *
	 */
	 public function unset_fav($username = '', $id = '') {
		
		if ($username == '') return 0;
		if ($id == '') return 0;
		
		$key = $this->_get_user_fav_key($username);
				
		//use current time as score
		$ret = Redis::zrem($key, $id); 		
		if ($ret == 1)
			return 1;
		
		return 0;
	 }	

	/**
	 * get_user_fav
	 *
	 * get fav ids of a user
	 * @param	string	username
	 * @param	string	media id
	 * @param	int		number of ids returned
	 * @param	int		offset score	 
	 * @return 	int		success (1) or failed (0)
	 *
	 */
	 public function get_user_fav_ids($username = '', $count = 10, $offset = 0) {

		$ids = array();
		
		if ($username == '') return $ids;
		
		$key = $this->_get_user_fav_key($username);

    	// fix offset_score for sort desc
    	if ($offset == 0) {
    		$offset = '+inf';
    	} else {
    		$offset = '('.$offset;
    	}
    	//

		// get from redis
		if ($count == 'all') {
			$ids = Redis::zrevrangebyscore($key, '+inf', '-inf');
		} else {
			$ids = Redis::zrevrangebyscore($key, $offset, '-inf', array('limit'=> array(0,$count)));
		}
		
		return $ids;
	 }	

	/**
	 * get_user_fav
	 *
	 * get the addtime (score) of a user fav media
	 * @param	string	username
	 * @param	string	media id
	 * @return 	int		addtime timestamp
	 *
	 */
	 public function get_fav_addtime($username, $id) {

		if ($username == '') return '';
		
		$key = $this->_get_user_fav_key($username);
   	
		$addtime = Redis::zscore($key, $id);

		return $addtime;
	 }	

}