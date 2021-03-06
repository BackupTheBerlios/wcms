<?php

/**
 * Project:     wCMS: Wiki style CMS
 * File:        $Source: /home/xubuntu/berlios_backup/github/tmp-cvs/wcms/Repository/wcms/edit.php,v $
 * Revision:    $Revision: 1.11 $
 * Last Edit:   $Date: 2005/09/04 15:22:27 $
 * By:          $Author: streaky $
 *
 *  Copyright � 2005 Martin Nicholls
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
 * @version $Revision: 1.11 $
 */

/* $Id: edit.php,v 1.11 2005/09/04 15:22:27 streaky Exp $ */

require_once("boot.php");

$item = (!vars::get('page') ? "home_page" : vars::get('page'));
$item = preg_replace("#\W#", "", $item);

if($perms['wiki']['edit_pages'] != true) {
	$page = rewrite(path::http()."?page={$item}");
	header("Location: {$page}");
} else {

	if ($_POST['wikiedit']) {

		$_POST['wikiedit']['tag'] = preg_replace("#\W#", "", vars::post('page_tag', 'wikiedit'));

		// assign the WHERE clause
		$old_tag = vars::post('old_tag', 'wikiedit');
		$where = "tag = ".$db->quote($old_tag, 'text');

		if (vars::post('old_tag', 'wikiedit') != "") {

			$query = "SELECT * FROM {$db_prefix}content WHERE cont_ident = ".$db->quote($old_tag, 'text');
			$db->setLimit(1);
			$result = $db->query($query);
			$rows = $result->fetchAll(MDB2_FETCHMODE_ASSOC);
			$result->free();

			include_once 'Text/Diff.php'; // Using include path (PEAR Class)
			include_once 'Text/Diff/Renderer.php'; // Using include path (PEAR Class)
			include_once 'Text/Diff/Renderer/inline.php'; // Using include path (PEAR Class)

			$before = $rows[0]['cont_content'];
			$after = trim(vars::post('page_content', 'wikiedit'));

			$diff = &new Text_Diff(explode("\n", strip_tags($before)), explode("\n", strip_tags($after)));

			/* Output the diff in unified format. */
			$renderer = &new Text_Diff_Renderer_inline();
			$diff_out = $renderer->render($diff);

			$store = $diff_out;

			$diff_out = ($diff_out ? "<blockquote>".nl2br($diff_out)."</blockquote>" : "");

			require_once(path::file("classes")."revision_class.php");
			$revisions = new revision_history;

			$rev = $rows[0]['cont_revision'];
			$new_rev = $revisions->get_next_revision($rev, (bool) vars::post('change_major', 'wikiedit'));

			$history = $revisions->get_revision_history($rows[0]['cont_ident']);

			$history = $revisions->add_revision_history($history, $new_rev, $store, trim(vars::post('change_desc', 'wikiedit')));
			$revisions->store_revision_history($item, $history);

			// attempt the update
			$query = "UPDATE {$db_prefix}content SET
                  cont_title = ".		$db->quote(vars::post('page_title', 'wikiedit'), 'text').",
                  cont_ident = ".		$db->quote(vars::post('page_tag', 'wikiedit'), 'text').",
                  cont_revision = ".	$db->quote($new_rev, 'text').",
                  cont_timestamp = ".	$db->quote(time(), 'integer').",
                  cont_content = ".		$db->quote(trim(vars::post('page_content', 'wikiedit')), 'text').",
                  cont_parent_id = ".	$db->quote(trim(vars::post('parent_ident', 'wikiedit')), 'integer')."
                  WHERE cont_ident = ".	$db->quote(vars::post('old_tag', 'wikiedit'), 'text');
			$db->query($query);

		} else {
			$cont_id = $db->nextId('cont_id ');
			$query = "INSERT INTO {$db_prefix}content (cont_id, cont_ident, cont_timestamp, cont_content, cont_title, cont_parent_id) VALUES ({$cont_id}, ".$db->quote(vars::post('page_tag', 'wikiedit'), 'text').", ".$db->quote(time(), 'integer').", ".$db->quote(vars::post('page_content', 'wikiedit'), 'text').", ".$db->quote(vars::post('page_title', 'wikiedit'), 'text').", ".$db->quote(vars::post('parent_ident', 'wikiedit'), 'integer').")";
			$db->query($query);
		}
		$tag = vars::post('page_tag', 'wikiedit');
		$page = rewrite("?page={$tag}");

		$cache->clear("wcontent_");
		$cache->clear("content_pages_");
		
		// header("Location: {$page}");
	}

	$query = ("SELECT cont_id, cont_ident, cont_title FROM {$db_prefix}content ORDER BY cont_ident");
	$result = $db->query($query);
	$rows = $result->fetchAll(MDB2_FETCHMODE_ASSOC);
	$result->free();
	foreach($rows as $tag_item) {
		$pages[] = array(
		'value'    => $tag_item['cont_id'],
		'title' => $tag_item['cont_ident'],
		);
		if($tag_item['cont_ident'] == "home_page") {
			$home_id = $tag_item['cont_id'];
		}
	}

	$path = path::http();
	$smarty->assign("breadcrumb", "/ <a href='{$path}'>Home</a> / Editing: {$tag} /");

	$query = "SELECT * FROM {$db_prefix}content WHERE cont_ident = ".$db->quote($item, 'text');
	$db->setLimit(1);
	$result = $db->query($query);
	$rows = $result->fetchAll(MDB2_FETCHMODE_ASSOC);
	$result->free();

	$row = $rows[0];

	// require form page and create $form object
	require_once(path::file("classes")."form_handling_class.php");
	$form_options = array(
		'action'      => path::http()."edit.php".($_SERVER['QUERY_STRING'] ? "?{$_SERVER['QUERY_STRING']}" : ""),
		'method'      => "post",
		'title'       => "Editing: ".($row['content_title'] != "" ? $row['contenttitle'] : $item),
		'description' => "",
		'response'    => ($diff_out ? "<h3>Changes Saved:</h3> (New revision: {$new_rev}){$diff_out}<hr />" : ""),
		'smarty'      => &$smarty,
	);
	$form = new form_handling($form_options);

	include(path::file("classes")."forms/edit_content_form.php");

	$content_output .= $form->return_form();
	unset($form);

	$smarty->assign("breadcrumb", $page['breadcrumb']);
	$smarty->assign("page_content", $content_output);

	output_page();
}

?>