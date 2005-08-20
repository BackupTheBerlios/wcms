<?php

require_once("boot.php");
//include(HEADER);

$query = explode("/", $_SERVER['PATH_INFO']);
//Var_Dump::display($query);

switch ($query[1]) {
  case "news":
    require_once(path::file()."news.php");
  break;
  default:
    require_once(path::file()."content.php");
  break;
}

//echo time();

//include(FOOTER);

?>