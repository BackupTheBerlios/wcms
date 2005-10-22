<?php

/**
 * Project:     wCMS: Wiki style CMS
 * File:        $Source: /home/xubuntu/berlios_backup/github/tmp-cvs/wcms/Repository/wcms/boot.php,v $
 * Revision:    $Revision: 1.36 $
 * Last Edit:   $Date: 2005/10/22 01:08:19 $
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
 * @version $Revision: 1.36 $
 */

/* $Id: boot.php,v 1.36 2005/10/22 01:08:19 streaky Exp $ */

require_once("classes/generic_functions.php");

$time_start = microtime_float();

$register_globals = true;
if(function_exists('ini_get')) {
	$register_globals = ini_get('register_globals');
}
if($register_globals == true){
	while (list($global) = each($GLOBALS)) {
		if (!preg_match('/^(_POST|_GET|_COOKIE|_SERVER|_FILES|GLOBALS|HTTP.*|_REQUEST|time_start)$/', $global)) {
			unset($$global);
		}
	}
	unset($global);
}

error_reporting(E_ERROR | E_PARSE);

define("WCMS_BOOTSTRAP_LOADED");

// set up some options
ini_set('arg_separator.output',     '&amp;');
ini_set('magic_quotes_runtime',     0);
ini_set('magic_quotes_sybase',      0);
ini_set('session.cache_limiter',    'none');
ini_set('session.use_only_cookies', 1);
ini_set('session.use_trans_sid',    0);

// Set Up PEAR Path
ini_set("include_path", realpath(dirname(__FILE__)).'/classes/pear/'.PATH_SEPARATOR.".");

$paths = array(
	'classes'    => 'classes',
	'data'       => 'data',
	'templates'  => 'templates',
	'images'     => 'images',
	'plugins'    => 'plugins',
);
require_once("classes/paths_class.php");
$base_paths = path::parse_paths();
path::set($base_paths['file'], $base_paths['http'], $paths);

check_dirs();

require_once(path::file("classes")."vars_class.php");

include_once(path::file("data")."settings.php");

$db_prefix = $settings['db_prefix'];

$cache_options = array(
	'data_dir'  => path::file("data")."cache/",
	'cache_tag' => md5(path::http("templates").$settings['theme']."/theme.css".filemtime(path::file("templates").$settings['theme']."/theme.css")),
);
require_once(path::file("classes")."cache_handling_class.php");
$cache = new cache_handler($cache_options);

// implement smarty object
require_once(path::file("classes")."smarty/Smarty.class.php");
$smarty = new Smarty;

$smarty->compile_check = true;
$smarty->debugging = false;

$smarty->template_dir = path::file("templates");
$smarty->compile_dir = path::file("data")."template_cache/";

// Assign some useful smarty vars
$smarty->assign("theme",         path::file("templates").$settings['theme']."/");
$smarty->assign("theme_abs",     path::http("templates").$settings['theme']."/");
$smarty->assign("site_name",     $settings['site']['long_name']);
$smarty->assign("site_url",      "http://{$_SERVER['HTTP_HOST']}".path::http());
$smarty->assign("site_root",     path::http());
$smarty->assign("site_images",   path::http("images"));
$smarty->assign("templates_abs", path::http("templates"));
$smarty->assign("page_title",    $settings['site']['long_name']);

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
$input_file = path::file("data")."database/schema";
$schema_mod = $cache->get("schema_modified", (60 * 24));

if(!file_exists("{$input_file}.xml") || !$schema_mod || $schema_mod < filemtime("{$input_file}.xml")) {
	$db_name = $manager->db->database_name;
	if(!$manager->updateDatabase("{$input_file}.xml", "{$input_file}_current.xml", array('db_name' => $db_name, 'table_prefix' => $db_prefix))) {
		die("Schema Update Error.");
	}
	$cache->set("schema_modified", filemtime("{$input_file}.xml"));
}

require_once(path::file("classes")."content_class.php");
$content = new content_handling();

require_once(path::file("classes")."page_handling_class.php");
$page_handler = new page_hander();

require_once(path::file("classes")."users_class.php");

// Initiate session handler class
$session_options = array(
	'db_object' => &$db,
);
require_once(path::file("classes")."session_class.php");
$sessions =& new session_handler($session_options);

if($_POST['login']) {
	$user_password = md5(vars::post('userpass', 'login'));
	$user_name = preg_replace("#\W#", "", vars::post('username', 'login'));
	
	$query = "SELECT * FROM {$db_prefix}users
				WHERE name = ".$db->quote($user_name, 'text')."
				AND user_password = ".$db->quote($user_password, 'text');
	
	$db->setLimit(1);
	$result = $db->query($query);
	$user_row = $result->fetchAll(MDB2_FETCHMODE_ASSOC);
	$result->free();
	
	if(count($user_row) == 1) {
		$_SESSION['credentials']['username'] = $user_name;
		$_SESSION['credentials']['password'] = $user_password;
		$_SESSION['credentials']['user_id']  = $user_row[0]['user_id'];
	} else {
		$_SESSION['credentials']['user_id']  = 0;
	}
	header("Location: {$_SERVER['PHP_SELF']}".($_SERVER['QUERY_STRING'] ? "?{$_SERVER['QUERY_STRING']}" : ""));
	exit();
}

$cache->clear();

// Extract user data & permissions
$query = "SELECT * FROM {$db_prefix}users WHERE user_id = ".$db->quote($_SESSION['credentials']['user_id'], 'integer');
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

require_once('Text/Wiki.php'); // Using include path (PEAR Class)
// instantiate a Text_Wiki object with the default rule set
$wiki =& new Text_Wiki();

// when rendering XHTML, make sure wiki links point to the base URL
$wiki->setRenderConf('xhtml', 'wikilink', 'view_url', path::http()."?page=%s");
$wiki->setRenderConf('xhtml', 'wikilink', 'new_url', path::http()."?page=%s");
$wiki->setParseConf('wikilink', 'ext_chars', true);

// setup images basedir
$wiki->setRenderConf('xhtml', 'image', 'base', path::http("images"));

$wiki->enableRule('html');

$sites = array(
	'Site' => substr(path::http(), 0, -1).'%s',
);
$wiki->setRenderConf('xhtml', 'interwiki', 'sites', $sites);

$pages = $content->get_pages_list();
$wiki->setRenderConf('xhtml', 'wikilink', 'pages', $pages['pages']);
$wiki->setRenderConf('xhtml', 'wikilink', 'titles', $pages['titles']);

$nav = "";
$nav = @file_get_contents(path::file()."nav.html");
$nav = str_replace("#root#", path::http(), $nav);
$smarty->assign("nav", $nav);

foreach ($settings['menus'] as $menu) {
	if($menu['type'] == 'content') {
		$menu_item = $content->retrieve($menu['data'], CONTENT_NO_CACHE);
		$smarty->assign("menu_content", $menu_item['content']);
		$smarty->assign("menu_title", $menu_item['title']);
		$menus .= $smarty->fetch("{$settings['theme']}/menu_item.html");
	} else {
		// Implement non-content menu items here
	}
}
$smarty->assign("menu_area", $menus);

?>