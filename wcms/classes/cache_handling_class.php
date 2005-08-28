<?php

/**
 * Project:     wCMS: Wiki style CMS
 * File:        $Source: /home/xubuntu/berlios_backup/github/tmp-cvs/wcms/Repository/wcms/classes/cache_handling_class.php,v $
 * Revision:    $Revision: 1.7 $
 * Last Edit:   $Date: 2005/08/28 02:23:52 $
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
 * @version $Revision: 1.7 $
 */

/* $Id: cache_handling_class.php,v 1.7 2005/08/28 02:23:52 streaky Exp $ */

class cache_handler {
	
	var $_options = array(
		'data_dir'        => 'cache/',
		'cache_tag'       => '',
		'file_perms'      => 0777,
		'file_locking'    => true,
		'lock_attempts'   => 500,
	);
	
	function cache_handler($options = array()) {
		foreach ($options as $option => $value){
			$this->_options[$option] = $value;
		}
	}
	
	function set($tag, $data) {
		$data = serialize($data);
		$data = "<?php\n{$data}\n?>";
		
		$data_dir = $this->_options['data_dir'];
		$cache_tag = $this->_options['cache_tag'];
		$cache_file_name = "{$data_dir}{$tag}_{$cache_tag}.cache.php";
		
		return $this->_write_file($cache_file_name, $data);
	}
	
	function get($tag, $timeout = false) {
		$data_dir = $this->_options['data_dir'];
		$cache_tag = $this->_options['cache_tag'];
		$cache_file_name = "{$data_dir}{$tag}_{$cache_tag}.cache.php";
		if(file_exists($cache_file_name)) {
			if ($timeout == true && (filemtime($cache_file_name) + ($timeout * 60)) < time()) {
				@unlink($cache_file_name);
				return false;
			} else {
				$data = $this->_read_file($cache_file_name);
				$data = str_replace(array("<?php\n", "\n?>"), "", $data);
				return unserialize($data);
			}
		} else {
			return false;
		}
	}
	
	function clear($pattern) {
		$this->_delete_pattern("{$pattern}*.cache.php");
	}
	
	function _write_file($file_name, $content) {
		$attempt = 0;
        while ($attempt < $this->_options['lock_attempts']) {
            $fp = @fopen($file_name, "wb");
            if ($fp) {
                if ($this->_options['file_locking'] == true) {
                	@flock($fp, LOCK_EX);
                }
                $file_size = strlen($content);
                @fwrite($fp, $content, $file_size);
                @chmod($file_name, $this->_options['file_perms']);
				@touch($file_name);
                if ($this->_options['file_locking'] == true) {
                	@flock($fp, LOCK_UN);
                }
                @fclose($fp);
                return true;
            }
            $attempt ++;
        }
		return false;
	}
	
	function _read_file($file_name) {
        $fp = @fopen($file_name, "rb");
        if ($this->_options['file_locking'] == true) {
        	@flock($fp, LOCK_SH);
        }
        if ($fp) {
            $contents = @fread($fp, filesize($file_name));
            if ($this->_options['file_locking'] == true) {
            	@flock($fp, LOCK_UN);
            }
            @fclose($fp);
            return $contents;
        }
        return false;
	}
	
	function _delete_pattern($pattern = "*.cache.php") {
		$dir = $this->_options['data_dir'];
		$deleted = false;
		$pattern = str_replace(array("\*", "\?"), array(".*", "."), preg_quote($pattern));
		if (substr($dir, -1) != "/") {
			$dir .= "/";
		}
		if (is_dir($dir)) {
			$d = opendir($dir);
			while ($file = readdir($d)) {
				if (is_file($dir.$file) && ereg("^{$pattern}$", $file)) {
					if (unlink($dir.$file)) {
						$deleted[] = $file;
					}
				}
			}
			closedir($d);
			return true;
		} else {
			return false;
		}
	}
}

?>