<?php

/**
 * Project:     wCMS: Wiki style CMS
 * File:        $Source: /home/xubuntu/berlios_backup/github/tmp-cvs/wcms/Repository/wcms/content.php,v $
 * Revision:    $Revision: 1.6 $
 * Last Edit:   $Date: 2005/08/24 19:19:13 $
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
 * @version $Revision: 1.6 $
 */

/* $Id: content.php,v 1.6 2005/08/24 19:19:13 streaky Exp $ */

require_once("boot.php");

$item = (!vars::get('page') ? "home_page" : vars::get('page'));
$item = preg_replace("#\W#", "", $item);

$query = "SELECT * FROM content WHERE cont_ident = ".$db->quote($item, 'text');
$db->setLimit(1);
$result = $db->query($query);
$rows = $result->fetchAll(MDB2_FETCHMODE_ASSOC);
$result->free();
if (count($result) < 1) {
	$page = rewrite("edit.php?page={$item}");
	header("Location: {$page}");
	exit();
} else {
	$row = $rows[0];

	// load the class file
	require_once('Text/Wiki.php'); // Using include path (PEAR Class)

	// instantiate a Text_Wiki object with the default rule set
	$wiki =& new Text_Wiki();

	// when rendering XHTML, make sure wiki links point to a
	// specific base URL
	$wiki->setRenderConf('xhtml', 'wikilink', 'view_url', path::http()."?page=%s");
	$wiki->setRenderConf('xhtml', 'wikilink', 'new_url', path::http()."?page=%s");
	$wiki->setParseConf('wikilink', 'ext_chars', true);

	// setup images basedir
	$wiki->setRenderConf('xhtml', 'image', 'base', path::http("images"));

	require_once(path::file("data")."pages.php");
	$bread_crumb = bread_crumb();

	// enable use of <html> - not so clever for public editable sites!
	$wiki->enableRule('html');

	// set an array of pages that exist in the wiki
	// and tell the XHTML renderer about them
	$query = ("SELECT cont_id, cont_ident, cont_title FROM content");
	$result = $db->query($query);
	$rows = $result->fetchAll(MDB2_FETCHMODE_ASSOC);
	$result->free();
	foreach($rows as $tag_item) {
		$tag = $tag_item['cont_ident'];
		$pages[] = $tag;
		$titles[$tag]['title'] = $tag_item['cont_title'];
	}
	
	$sites = array(
		'wikipedia' => "http://en.wikipedia.org/wiki/%s",
	);
	$wiki->setRenderConf('xhtml', 'interwiki', 'sites', $sites);
	$wiki->setRenderConf('xhtml', 'wikilink', 'pages', $pages);
	$wiki->setRenderConf('xhtml', 'wikilink', 'titles', $titles);

	$revision = ($row['cont_revision'] == "" ? "1.0" : $row['cont_revision']);
	$item_data = $wiki->transform($row['cont_content'], 'Xhtml');
	$item_title = ($row['cont_title'] ? $row['cont_title'] : "No Title!");
	
	$footer = "";
	if($perms['wiki']['edit_pages'] == true) {
		$footer .= "
	<div style='text-align: right;'>
	  <a href='".path::http()."edit.php?page={$item}'><img src='".path::http("images")."icons/edit-trans.png' alt='Edit' title='Edit Page: {$item}' /></a>
	  <a href='".path::http()."delete.php?page={$item}'><img src='".path::http("images")."icons/error-trans.png' alt='Delete' title='Delete Page: {$item}' /></a>
	  <a href='".path::http()."history.php?page={$item}'><img src='".path::http("images")."icons/history-trans.png' alt='History' title='Last Edited: ".date("j F Y, g:ia", $row['cont_timestamp']).",\nRevision {$revision},\nClick to view history' /></a>
	</div>
";
	}
}

$smarty->assign("breadcrumb", $bread_crumb);
$smarty->assign("edit", "");
$smarty->assign("item_content", $item_data);
$smarty->assign("item_title", $item_title);
$smarty->assign("date", $row['cont_timestamp']);
$smarty->assign("page_footer", $footer);

$content_output = $smarty->fetch("{$settings['theme']}/generic_page_item.html");

$smarty->assign("page_content", $content_output);
$smarty->assign("page_title", "{$settings['site']['long_name']} - {$item_title}");

output_page();

?>