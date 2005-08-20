<?php

header("Cache-Control: must-revalidate");
header("Expires: ".gmdate("D, d M Y H:i:s", time() + (60 * 60 * 24 * 3))." GMT");

$register_globals = true;
if(function_exists('ini_get')) {
	$register_globals = ini_get('register_globals');
}
if($register_globals == true){
	while (list($global) = each($GLOBALS)) {
		if (!preg_match('/^(_POST|_GET|_COOKIE|_SERVER|_FILES|GLOBALS|HTTP.*|_REQUEST|whitelist)$/', $global)) {
			unset($$global);
		}
	}
	unset($global);
}

error_reporting(E_ERROR | E_PARSE);

// Set Up PEAR Path
ini_set("include_path", realpath(dirname(__FILE__)).'/classes/pear/'.PATH_SEPARATOR.".");

require_once("classes/paths_class.php");
$base_paths = path::parse_paths();
$paths = array(
	'classes'    => 'classes',
	'data'       => 'data',
	'templates'  => 'templates',
	'images'     => 'images',
	'plugins'    => 'plugins',
);
path::set($base_paths['file'], $base_paths['http'], $paths);

require_once("classes/generic_functions.php");

check_dirs();

require_once(path::file("classes")."vars_class.php");

include_once(path::file("data")."settings.php");

// implement smarty object
require_once(path::file("classes")."smarty/Smarty.class.php");
$smarty = new Smarty;

$smarty->compile_check = true;
$smarty->debugging = false;

$smarty->template_dir = path::file("templates");
$smarty->compile_dir = path::file("data")."template_cache/";

// $smarty->assign("", );
$smarty->assign("theme", path::file("templates").$settings['theme']."/");
$smarty->assign("theme_abs", path::http("templates").$settings['theme']."/");
$smarty->assign("site_name", $settings['site']['long_name']);
$smarty->assign("site_url", SITEURL);
$smarty->assign("site_root", path::http());
$smarty->assign("templates_abs", path::http("templates"));
$smarty->assign("page_title", $settings['site']['long_name']);

$dsn = $settings['dsn'];
require_once('MDB2/Schema.php'); // Using include path (PEAR Class)
PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'handle_pear_error');
$db =& MDB2::connect($dsn);
// With PEAR::isError you can differentiate between an error or
// a valid connection.
if (PEAR::isError($db)) {
	die();
}

$manager =& MDB2_Schema::factory($db);
//$input_file = path::file("data")."database/db_schema.xml";
// lets create the database using 'data/db_schema.xml'
// if you have already run this you should have 'data/db_schema.xml.current' - this should not be deleted -
// in that case MDB2 will just compare the two schemas and make any necessary modifications to the existing DB structure
//$manager->updateDatabase($input_file, "{$input_file}.current");

require_once(path::file("classes")."users_class.php");

// Initiate session handler class
require_once(path::file("classes")."session_class.php");
$session_options = array(
'db_object' => &$db,
);
$sessions =& new session_handler($session_options);

// Extract user data & permissions
$query = "SELECT * FROM users WHERE user_id = ".$db->quote($_SESSION['credentials']['user_id'], 'integer');
$db->setLimit(1);
$result = $db->query($query);
$users_row = $result->fetchAll(MDB2_FETCHMODE_ASSOC);
$result->free();
$perms = unserialize($users_row[0]['permissions']);

if(is_readable(path::file("plugins")."bbclone/")) {
	//define("_BBC_PAGE_NAME", "Test");
	define("_BBCLONE_DIR", path::file("plugins")."bbclone/");
	define("COUNTER", _BBCLONE_DIR."mark_page.php");
	if (is_readable(COUNTER)) {
		require_once(COUNTER);
	}
}

/* Get Navbar and parse the content */
$query = "SELECT * FROM content WHERE cont_ident = ".$db->quote("navbar", 'text');
$db->setLimit(1);
$result = $db->query($query);
$rows = $result->fetchAll(MDB2_FETCHMODE_ASSOC);
$result->free();
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

$wiki->setRenderConf('xhtml', 'list', 'css_ul', "nav");

$navbar_data = $wiki->transform($row['cont_content'], 'Xhtml');

$smarty->assign("nav_ul", $navbar_data);
$wiki->setRenderConf('xhtml', 'list', 'css_ul', null);
/* End navbar code */

?>
