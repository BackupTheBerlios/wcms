<?php

require_once("boot.php");

$smarty->assign("breadcrumb", "/ <a href='/' title='Go to Home'>Home</a> / User Login /");
$response = "";
$content_output = "";

if($_POST['login']) {
	$user_password = md5(vars::post('userpass', 'login'));
	$user_name = preg_replace("#\W#", "", vars::post('username', 'login'));
	
	$query = "SELECT * FROM users
				WHERE name = ".$db->quote($user_name, 'text')."
				AND password = ".$db->quote($user_password, 'text');
	
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
	header("Location: {$_SERVER['PHP_SELF']}");
	exit("Debug: Redirecting to {$_SERVER['PHP_SELF']}");
}

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