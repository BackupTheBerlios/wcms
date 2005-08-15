<?php

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