<?php

/**
 * Project:     wCMS: Wiki style CMS
 * File:        $Source: /home/xubuntu/berlios_backup/github/tmp-cvs/wcms/Repository/wcms/login.php,v $
 * Revision:    $Revision: 1.7 $
 * Last Edit:   $Date: 2005/09/06 11:14:08 $
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
 * @version $Revision: 1.7 $
 */

/* $Id: login.php,v 1.7 2005/09/06 11:14:08 streaky Exp $ */

require_once("boot.php");

if($settings['secure_login'] == true && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on")) {
	$QUERY = ($_SERVER['QUERY_STRING'] ? "?{$_SERVER['QUERY_STRING']}" : "");
	$path = "https://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}{$QUERY}";
	header("Location: {$path}");
}

$smarty->assign("breadcrumb", "/ <a href='".path::http()."' title='Go to Home'>Home</a> &lt; Login");
$response = "";
$content_output = "";

// require form page and create $form object
require_once(path::file("classes")."form_handling_class.php");
$form_options = array(
	'action'      => "login.php",
	'method'      => "post",
	'title'       => "User Login",
	'description' => "Cookies must be enabled to log in",
	'response'    => $response,
	'smarty'      => &$smarty,
);
$form = new form_handling($form_options);

$form_item = array (
	'label'       => "User Name",
	'max_length'  => 40,
	'name'        => "login[username]",
	'id'          => "login-username",
	'size'        => 30,
	'description' => "",
	'value'       => "",
	'required'    => true,
);
$form->add_text_field($form_item);

$form_item = array (
	'label'       => "User Password",
	'max_length'  => 40,
	'name'        => "login[userpass]",
	'id'          => "login-userpass",
	'size'        => 30,
	'description' => "",
	'value'       => "",
	'required'    => true,
	'type'        => 'password',
);
$form->add_text_field($form_item);

$form_item = array (
	'label'       => "Remember Me",
	'checked'     => false,
	'name'        => "login[remember]",
	'id'          => "login-remember",
	'description' => "Store my user credentials and log me in automatically",
);
$form->add_checkbox($form_item);

$form->build_fieldset("User Credentials");

$form_item = array(
	'title' => "Submit",
	'id'  => "submit_form",
	'type'  => "submit",
);
$form->add_button($form_item);

$form_item = array(
	'title' => "Reset",
	'id'  => "reset_form",
	'type'  => "reset",
);
$form->add_button($form_item);

$form->build_fieldset("Login / Clear Form");

$content_output .= $form->return_form();
unset($form);

$smarty->assign("breadcrumb", $page['breadcrumb']);
$smarty->assign("page_content", $content_output);

output_page();

?>