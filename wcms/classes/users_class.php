<?php

class users {
	
	function perms($name) {
		if($GLOBALS['_wikicms']['user']['perms'][$name] == true){
			return true;
		} else {
			return false;
		}
	}
	
	function set_perms($perms = array()) {
		$GLOBALS['_wikicms']['user']['perms'] = $perms;
	}
	
	function set_user($user_data = array()) {
		$GLOBALS['_wikicms']['user']['data'] = $user_data;
	}
}

?>