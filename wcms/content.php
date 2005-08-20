<?php

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
	$wiki->setRenderConf('xhtml', 'wikilink', 'view_url', "?page=");
	$wiki->setRenderConf('xhtml', 'wikilink', 'new_url', "?page=");
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

	$footer = "
	<div style='text-align: right;'>
	  <a href='".path::http()."edit.php?page={$item}'><img src='".path::http("images")."icons/edit-trans.png' alt='Edit' title='Edit Page: {$item}' /></a>
	  <a href='".path::http()."delete.php?page={$item}'><img src='".path::http("images")."icons/error-trans.png' alt='Delete' title='Delete Page: {$item}' /></a>
	  <a href='".path::http()."history.php?page={$item}'><img src='".path::http("images")."icons/history-trans.png' alt='History' title='Last Edited: ".date("j F Y, g:ia", $row['cont_timestamp']).",\nRevision {$revision},\nClick to view history' /></a>
	</div>
";
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