<?php

/**
 * Project:     wCMS: Wiki style CMS
 * File:        $Source: /home/xubuntu/berlios_backup/github/tmp-cvs/wcms/Repository/wcms/classes/content_class.php,v $
 * Revision:    $Revision: 1.9 $
 * Last Edit:   $Date: 2005/09/02 09:22:47 $
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
 * @version $Revision: 1.9 $
 */

/* $Id: content_class.php,v 1.9 2005/09/02 09:22:47 streaky Exp $ */

class content_handling {

	var $_options = array(
		'content_table' => 'content',
	);

	function content_handling($options = array()) {
		define("CONTENT_NO_CACHE", -1);
		foreach ($options as $option => $value){
			$this->_options[$option] = $value;
		}
	}

	function retrieve($ident = "home_page", $cache_timeout = CONTENT_NO_CACHE) {
		global $db, $cache, $wiki, $db_prefix;
		$ident = preg_replace("#\W#", "", $ident);
		$ret = $cache->get("wcontent_{$ident}", $cache_timeout);
		if(!$ret) {
			$query = "SELECT * FROM {$db_prefix}{$this->_options['content_table']} WHERE cont_ident = ".$db->quote($ident, 'text');
			$db->setLimit(1);
			$result = $db->query($query);
			$rows = $result->fetchAll(MDB2_FETCHMODE_ASSOC);
			$result->free();
			$row = $rows[0];
			$content = $wiki->transform($row['cont_content'], 'Xhtml');
			
			$family_tree = $this->get_content_tree($row['cont_parent_id'], $ident, $row['cont_id'], $row['cont_title']);
			
			$ret = array(
				'content'     => $content,
				'title'       => $row['cont_title'],
				'last_mod'    => $row['cont_timestamp'],
				'ident'       => $ident,
				'id'          => $row['cont_id'],
				'revision'    => $row['cont_revision'],
				'parent'      => $row['cont_parent_id'],
				'settings'    => $row['cont_settings'],
				'family_tree' => $family_tree,
			);
			$cache->set("wcontent_{$ident}", $ret);
		}
		return $ret;
	}

	function create($ident = "home_page") {

	}

	function get_secure_media($id = false) {

	}

	function _parse_input($content) {

	}

	function get_pages_list() {
		global $cache, $db, $db_prefix;
		$ret = $cache->get("content_pages");
		if(!$ret) {
			$query = ("SELECT cont_ident, cont_title FROM {$db_prefix}content");
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

	function get_content_tree($parent_id, $ident, $id, $title) {
		global $cache, $db;
		$ret = $cache->get("wcontent_{$ident}_tree");
		if(!$ret) {
			$data[] = array(
				'ident'  => $ident,
				'id'     => $id,
				'title'  => $title,
				'parent' => $parent_id
			);
			$no_parents = false; $i = 0;
			while ($no_parents == false && $i < 50) {
				if($parent_id == $id || $parent_id == false) {
					$no_parents == true;
				} else {
					// get parents data
					$parent = $this->_get_parent($parent_id);
					$data[] = $parent;
					$parent_id = $parent['parent'];
					$id = $parent['id'];
				}
				$i++;
			}
			$ret = $data;
			$cache->set("wcontent_{$ident}_tree", $ret);
		}
		return $ret;
	}

	function _get_parent($id) {
		global $db;
		$query = "SELECT cont_ident, cont_title, cont_id, cont_parent_id FROM {$db_prefix}content WHERE cont_id = ".$db->quote($id, 'integer');
		$db->setLimit(1);
		$result = $db->query($query);
		$rows = $result->fetchAll(MDB2_FETCHMODE_ASSOC);
		$result->free();
		$row = $rows[0];
		$ret = array(
			'ident'  => $row['cont_ident'],
			'id'     => $row['cont_id'],
			'title'  => $row['cont_title'],
			'parent' => $row['cont_parent_id'],
		);
		return $ret;
	}
}

?>