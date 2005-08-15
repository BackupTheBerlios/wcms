<?php

$whitelist = "";
require_once("../boot.php");

$file = $_SERVER['QUERY_STRING'];
$file = str_replace(array("..", "//"), array("", ""), $file);
$file = str_replace(array("..", "//"), array("", ""), $file);
$file = str_replace(array("..", "//"), array("", ""), $file);

$length = strlen(path::path("images"));
if(substr($file, 0, $length) == path::path("images")){
	echo "OK";
}

$file = str_replace(path::path("images"), path::file(), $file);

echo $file;

?>
