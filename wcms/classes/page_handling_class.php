<?php

/**
 * Project:     wCMS: Wiki style CMS
 * File:        $Source: /home/xubuntu/berlios_backup/github/tmp-cvs/wcms/Repository/wcms/classes/page_handling_class.php,v $
 * Revision:    $Revision: 1.2 $
 * Last Edit:   $Date: 2005/08/28 06:35:00 $
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
 * @version $Revision: 1.2 $
 */

/* $Id: page_handling_class.php,v 1.2 2005/08/28 06:35:00 streaky Exp $ */

class page_hander {

	var $header_data = array();
	var $page_items = array();
	var $footer_objects = array();

	function add_header_data($data) {
		$this->header_data[] = trim($data);
	}

	function get_header_data() {
		return implode("\n", $this->header_data);
	}

	function add_page_item($content, $title = false) {
		global $smarty, $settings;
		$smarty->assign("item_content", $content);
		$smarty->assign("item_title", $title);
		$this->page_items[] = $content_output = $smarty->fetch("{$settings['theme']}/generic_page_item.html");
	}

	function get_page() {
		return implode("\n", $this->page_items);
	}

	function output_page($page_title = "") {
		global $smarty, $settings, $time_start;
		
		$smarty->assign("page_content", $this->get_page());
		$smarty->assign("page_title", "{$settings['site']['long_name']} - {$page_title}");
		
		$smarty->assign("page_footer", implode("\n", $this->footer_objects)."\n#RENDERTIME#");
		
		$smarty->load_filter('output','rewrite_urls');
		if($type == true && file_exists(path::file("templates")."{$settings['theme']}/main_{$type}.html")) {
			$output = trim($smarty->fetch("{$settings['theme']}/main_{$type}.html"));
		} else {
			$output = trim($smarty->fetch("{$settings['theme']}/main.html"));
		}		
		$time_end = microtime_float();
		$time = $time_end - $time_start;
		echo str_replace("#RENDERTIME#", "[Render Time: {$time}s]", $output);
	}
	
	function add_footer($content) {
		$this->footer_objects[] = $content;
	}
}

?>