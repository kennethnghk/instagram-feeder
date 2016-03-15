<?php
	/**
	 * format_time_diff
	 *
	 * get time diff label (eg. '1h', '7d', 5m');
	 * @param	int		created_time timestamp
	 * @return 	string	time diff label
	 *
	 */
	function format_time_diff($created_time) {
	
		$sec = time() - $created_time;
	
		$label = '';
		if ($sec >= 86400) { // day
			$label = floor($sec/86400).'d';
		} else if ($sec >= 3600) { // hour
			$label = floor($sec/3600).'h';
		} else if ($sec >= 60) { // min
			$label = floor($sec/60).'m';
		} else {
			$label = $sec.'s';
		}
		
		return $label;
	}
?>