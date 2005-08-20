<?php

/**
 * Path handling class
 *
 */
class path {

	/**
	 * Returns an absolute path to directories
	 *
	 * @param string $name
	 * @return string file path
	 */
	function file($name = "") {
		if($name != "") {
			$path = "{$GLOBALS['_wikicms']['paths']['file_base']}{$GLOBALS['_wikicms']['paths']['paths'][$name]}/";
		} else {
			$path = $GLOBALS['_wikicms']['paths']['file_base'];
		}
		return $path;
	}

	/**
	 * Returns and absolute http path to directories (not inluding http://site.com/)
	 *
	 * @param string $name
	 * @return string path
	 */
	function http($name = "") {
		if($name != "") {
			$path = "{$GLOBALS['_wikicms']['paths']['path_base']}{$GLOBALS['_wikicms']['paths']['paths'][$name]}/";
		} else {
			$path = $GLOBALS['_wikicms']['paths']['path_base'];
		}
		return $path;
	}

	/**
	 * Set the paths for use with path::file() and path::http()
	 *
	 * @param string $file_base
	 * @param string $path_base
	 * @param array $paths
	 */
	function set($file_base, $path_base, $paths = array()) {
		$GLOBALS['_wikicms']['paths']['file_base'] = $file_base;
		$GLOBALS['_wikicms']['paths']['path_base'] = $path_base;
		$GLOBALS['_wikicms']['paths']['paths']     = $paths;
	}

	/**
	 * Fixes windows paths so they look and behave like unix paths - makes directory parsing
	 * simpler and less prone to error
	 *
	 * @param string $path
	 * @return string fixed path
	 */
	function fix_windows_paths($path) {
		$fixed_path = str_replace(array('\\\\', '\\'), array('/', '/'), $path);
		$fixed_path = (substr($fixed_path, 1, 2) == ":/" ? substr($fixed_path, 2) : $fixed_path);
		return $fixed_path;
	}

	/**
	 * Parses server config to figure out various path information
	 *
	 * @return array paths
	 */
	function parse_paths() {
		$path = ""; $i = 0;
		while (!file_exists("{$path}boot.php")) {
			$path .= "../";
			$i++;
		}
		if($_SERVER['PHP_SELF'] == "") { $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME']; }

		$http_path = dirname($_SERVER['PHP_SELF']);
		$http_path = explode("/", $http_path);
		$http_path = array_reverse($http_path);

		$j = 0;
		while ($j < $i) {
			unset($http_path[$j]);
			$j++;
		}
		$http_path   = array_reverse($http_path);
		$server_path = implode("/", $http_path)."/";
		$server_path = path::fix_windows_paths($server_path);

		if ($server_path == "//") {
			$server_path = "/";
		}
		$paths = array(
			'file' => $path,
			'http' => $server_path,
		);
		return $paths;
	}
}

?>