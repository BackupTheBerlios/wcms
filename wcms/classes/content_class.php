<?php

/**
 * Project:     wCMS: Wiki style CMS
 * File:        $Source: /home/xubuntu/berlios_backup/github/tmp-cvs/wcms/Repository/wcms/classes/content_class.php,v $
 * Revision:    $Revision: 1.1 $
 * Last Edit:   $Date: 2005/08/28 00:13:29 $
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
 * @version $Revision: 1.1 $
 */

/* $Id: content_class.php,v 1.1 2005/08/28 00:13:29 streaky Exp $ */

class content_handling {

	var $_options = array(
	'content_table' => 'content',
	);

	function content_handling(&$mdb2_object, $options = array()) {
		foreach ($options as $option => $value){
			$this->_options[$option] = $value;
		}
	}

	function retrieve($ident = "home_page") {
		global $db, $cache, $wiki;
		$ident = preg_replace("#\W#", "", $ident);
		$ret = $cache->get("wcontent_{$ident}");
		if(!$ret) {
			$query = "SELECT * FROM {$this->_options['content_table']} WHERE cont_ident = ".$db->quote($ident, 'text');
			$db->setLimit(1);
			$result = $db->query($query);
			$rows = $result->fetchAll(MDB2_FETCHMODE_ASSOC);
			$result->free();
			$row = $rows[0];
			$content = $wiki->transform($row['cont_content'], 'Xhtml');
			$ret = array(
			'content'  => $content,
			'title'    => $row['cont_title'],
			'last_mod' => $row['cont_timestamp'],
			'ident'    => $ident,
			'id'       => $row['cont_id'],
			'revision' => $row['cont_revision'],
			'parent'   => $row['parent_id'],
			'settings' => $row['cont_settings'],
			);
			$cache->set("wcontent_{$ident}", $ret);
		}
		return $ret;
	}

	function set($ident = "home_page") {

	}

	function create($ident = "home_page") {

	}

	function get_secure_media($id = false) {

	}

	function _parse_input($content) {

	}

	function get_pages_list() {
		global $cache, $db;
		$ret = $cache->get("content_pages");
		if(!$ret) {
			$query = ("SELECT cont_ident, cont_title FROM content");
			$result = $db->query($query);
			$rows = $result->fetchAll(MDB2_FETCHMODE_ASSOC);
			$result->free();
			foreach($rows as $tag_item) {
				$tag = $tag_item['cont_ident'];
				$pages[] = $tag;
				$titles[$tag]['title'] = $tag_item['cont_title'];
			}
			$ret = array(
				'pages'  => $pages,
				'titles' => $titles,
			);
			$cache->set("content_pages", $ret);
		}
		return $ret;
	}

	function create_content_footer($ident, $last_mod, $revision, $item_title) {
		global $perms;
		$buttons = array();
		if($perms['wiki']['edit_pages'] == true) {
			$buttons[] = "<a href='".path::http()."edit.php?page={$ident}'><img src='".path::http("images")."icons/edit-trans.png' alt='Edit' title='Edit Page: {$ident}' /></a>";
			//$buttons[] = "<a href='".path::http()."delete.php?page={$item}'><img src='".path::http("images")."icons/error-trans.png' alt='Delete' title='Delete Page: {$item}' /></a>";
			$buttons[] = "<a href='".path::http()."history.php?page={$ident}'><img src='".path::http("images")."icons/history-trans.png' alt='History' title='Last edited ".date("j F Y, g:ia", $last_mod).", revision {$revision}' /></a>";
		}
		return implode(" ", $buttons);
	}
}

?>