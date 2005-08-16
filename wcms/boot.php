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

// Set Up PEAR Path
ini_set("include_path", realpath(dirname(__FILE__)).'/classes/pear/'.PATH_SEPARATOR.".");

require_once("classes/paths_class.php");
$base_paths = path::parse_paths();
$paths = array(
	'classes'   => 'classes',
	'data'      => 'data',
	'templates' => 'templates',
	'images'    => 'images',
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
$smarty->assign("theme_abs", path::path("templates").$settings['theme']."/");
$smarty->assign("site_name", $settings['site']['long_name']);
$smarty->assign("site_url", SITEURL);
$smarty->assign("site_root", path::path());
$smarty->assign("templates_abs", path::path("templates"));
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

	print_a($_SERVER);
	print_a($GLOBALS['_wikicms']);

?>