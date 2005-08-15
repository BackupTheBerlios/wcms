<?php

require_once("boot.php");

$item_data = "
<h2 id=\"toc0\">Error 404 - Document Not Found</h2>
<p>The requested URL could not be found on this server. The link you followed is either outdated, inaccurate, or the server has been instructed not to allow access to it.</p>
<p>Your unsuccessful attempt to access <b>http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}</b> has been logged for checking and possible rectification.</p>
";
        
        $page['tag'] = "";
        $page['edit'] = "";
        $page['item'] = $item_data;
        $page['date'] = time();
        $page['footer'] = "    [ <a href='edit.php?page={$row[0]}'>Edit Page</a> ] [ Last Edited: ".date("j F Y, g:ia", $row[1])." ]
    [ <a href='?page=AccessIbility'>Accessibility</a> ] [ <a href='?page=PrivacyPolicy'>Privacy Policy</a> ]";
        $page['breadcrumb'] = $bread_crumb;


$smarty->assign("breadcrumb", $page['breadcrumb']);
$smarty->assign("edit", $page['edit']);
$smarty->assign("item_content", $item_data);
$smarty->assign("date", $page['date']);
$smarty->assign("page_footer", $page['footer']);

$content_output = $smarty->fetch("{$settings['theme']}/generic_page_item.html");

$smarty->assign("page_content", $content_output);

output_page();

?>
