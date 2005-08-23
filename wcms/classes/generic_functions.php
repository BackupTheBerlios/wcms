<?php

/**
 * Project:     wCMS: Wiki style CMS
 * File:        $Source: /home/xubuntu/berlios_backup/github/tmp-cvs/wcms/Repository/wcms/classes/generic_functions.php,v $
 * Revision:    $Revision: 1.13 $
 * Last Edit:   $Date: 2005/08/23 09:36:27 $
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
 * @version $Revision: 1.13 $
 */

/* $Id: generic_functions.php,v 1.13 2005/08/23 09:36:27 streaky Exp $ */

/**
 * Return or output print_r() but with htmlenties and pre tags
 *
 * @param array $var
 * @param bool_type $return
 * @return string optional output
 */
function print_a($var, $return = false) {
	$data = "<pre>".htmlentities(print_r($var, true))."</pre>";
	if($return == true) {
		return $data;
	} else {
		echo $data;
	}
}

function fix_windows_paths($path) {
	$fixed_path = str_replace(array('\\\\', '\\'), array('/', '/'), $path);
	$fixed_path = (substr($fixed_path, 1, 2) == ":/" ? substr($fixed_path, 2) : $fixed_path);
	return $fixed_path;
}

function bread_crumb() {
/*	global $crumbs;
	$ret = "";
	foreach ($crumbs as $page => $crumb){
		if("?{$_SERVER['QUERY_STRING']}" == $page) {
			foreach($crumb as $title => $url) {
				if("?{$_SERVER['QUERY_STRING']}" != $url) {
					$ret .= "/ <a href='{$url}' title='Go to {$title}'>{$title}</a> ";
				} else {
					$ret .= "/ {$title} /";
				}
			}
			break(1);
		}
	}
	if($ret == "") {
		$ret .= "/ Home /";
	}*/
	return $ret;
}

function main_links() {
	$ret = "";
}

function get_memory_usage(){
	if(function_exists("memory_get_usage")){
		$memusage = memory_get_usage();
		$memunit = 'b';
		if ($memusage > 1024){
			$memusage = $memusage / 1024;
			$memunit = 'kb';
		}
		if ($memusage > 1024){
			$memusage = $memusage / 1024;
			$memunit = 'mb';
		}
		if ($memusage > 1024){
			$memusage = $memusage / 1024;
			$memunit = 'gb';
		}
		return (number_format($memusage, 0).$memunit);
	} else {
		return ('Unknown');
	}
}

function stripslashes_deep($value) {
	$value = is_array($value) ?
	array_map('stripslashes_deep', $value) :
	stripslashes($value);

	return $value;
}

function rewrite($content){
	/*$pattern = "|/edit\.php\?page=(\w+)|";
	$content = preg_replace($pattern, "/edit/\${1}/", $content);

	$pattern = "|/delete\.php\?page=(\w+)|";
	$content = preg_replace($pattern, "/delete/\${1}/", $content);

	$pattern = "|/history\.php\?page=(\w+)|";
	$content = preg_replace($pattern, "/history/\${1}/", $content);

	$pattern = "|\?page=(\w+)|";
	$content = preg_replace($pattern, "/content/\${1}.html", $content);*/
	return $content;
}

/**
 * Ensures required data directories exist and are writable
 *
 */
function check_dirs() {
	if(is_dir(path::file("data")) && is_writable(path::file("data"))) {
		$dirs = "cache|template_cache|database|logs|history";
		$dirs = explode("|", $dirs);
		foreach ($dirs as $dir){
			if(!is_dir(path::file("data").$dir)) {
				@mkdir(path::file("data").$dir);
			}
			if(!is_writable(path::file("data").$dir)) {
				@chmod(path::file("data").$dir, 0777);
			}
			if(!is_dir(path::file("data").$dir)){
				die("directory ".path::file("data")."{$dir} does not exist and could not be created");
			}
			if(!is_writable(path::file("data").$dir)) {
				die("directory ".path::file("data")."{$dir} is not writable and could not be chmodded by the server");
			}
		}
	} else {
		die("directory ".path::file("data")." either doesn't exist or isn't writable");
	}
}

/**
 * Load required filters and output final page from main template
 *
 */
function output_page($type = false) {
	global $smarty, $settings;
	$smarty->load_filter('output','rewrite_urls');

	if($type && file_exists("{$settings['theme']}/main_{$type}.html") && is_readable("{$settings['theme']}/main_{$type}.html")) {
		$output = trim($smarty->fetch("{$settings['theme']}/main_{$type}.html"));
	} else {
		$output = trim($smarty->fetch("{$settings['theme']}/main.html"));
	}

	//$etag = md5($output);
	//$length = strlen($output);
	header("ETag: {$etag}");
	//header("Content-Length: {$length}");
	
	echo $output;

	//echo "Memory Usage: ".get_memory_usage();
}

if(!function_exists("file_put_contents")) {
	function file_put_contents($filename, $data) {
		if (($h = @fopen($filename, 'w+')) === false) {
			return false;
		}
		if (($bytes = @fwrite($h, $data)) === false) {
			return false;
		}
		fclose($h);
		return $bytes;
	}
}

function handle_pear_error($error_obj) {
	print '<pre><b>PEAR-Error</b><br />';
	echo $error_obj->getMessage().': '.$error_obj->getUserinfo();
	print '</pre>';
}

?>