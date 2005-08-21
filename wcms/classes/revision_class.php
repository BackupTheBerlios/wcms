<?php

/**
 * Project:     wCMS: Wiki style CMS
 * File:        $Source: /home/xubuntu/berlios_backup/github/tmp-cvs/wcms/Repository/wcms/classes/revision_class.php,v $
 * Revision:    $Revision: 1.2 $
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
 * @version $Revision: 1.2 $
 */

/* $Id: revision_class.php,v 1.2 2005/08/21 18:00:27 streaky Exp $ */

class revision_history {

	function get_next_revision($old_revision, $major_change = false) {
		if($old_revision == false or $old_revision == 0){
			$old_revision == "1.0";
		}
		$revision = (string) $old_revision;
		$revision = explode(".", $revision);
		if($major_change === true) {
			$revision[0] += 1;
			$revision[1] = 0;
			$new_revision = implode(".", $revision);
		} else {
			$revision[0] = ($revision[0] ? $revision[0] : 1);
			$revision[1] = ($revision[1] ? $revision[1] + 1 : 1);
			$new_revision = implode(".", $revision);
		}
		return $new_revision;
	}

	function get_revision_history($tag) {
		if(is_readable(path::file("data")."wiki_history/{$tag}.hist")) {
			$ArrayData = file_get_contents(path::file("data")."wiki_history/{$tag}.hist");
			$ArrayData = '$data = '.trim($ArrayData).';';
			@eval($ArrayData);
			if(is_array($data)){
				$history = $data;
			} else {
				$history = array();
			}
		} else {
			$history = array();
		}
		return $history;
	}

	function add_revision_history($history, $revision, $diff, $comment, $user = 1) {
		$history[$revision] = array(
				'revision' => $revision,
				'diff'	   => $diff,
				'user'     => 1,
				'comment'  => $comment,
		);
		return $history;
	}

	function store_revision_history($tag, $history) {
		if (!is_array($history)) {
			return false;
		}
		$data = var_export($history, true);
		return file_put_contents(path::file("data")."wiki_history/{$tag}.hist", $data);
	}
}

?>