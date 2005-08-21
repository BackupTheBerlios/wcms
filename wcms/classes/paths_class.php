<?php

/**
 * Project:     wCMS: Wiki style CMS
 * File:        $Source: /home/xubuntu/berlios_backup/github/tmp-cvs/wcms/Repository/wcms/classes/paths_class.php,v $
 * Revision:    $Revision: 1.3 $
 * Last Edit:   $Date: 2005/08/21 18:00:27 $
 * By:          $Author: streaky $
 *
 *  Copyright © 2005 Martin Nicholls
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @link http://wcms.berlios.de/
 * @copyright 2005 Martin Nicholls
 * @author Martin Nicholls <webmasta at streakyland dot co dot uk>
 * @package wCMS
 * @version $Revision: 1.3 $
 */

/* $Id: paths_class.php,v 1.3 2005/08/21 18:00:27 streaky Exp $ */

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