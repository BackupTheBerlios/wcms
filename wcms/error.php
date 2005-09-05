<?php

/**
 * Project:     wCMS: Wiki style CMS
 * File:        $Source: /home/xubuntu/berlios_backup/github/tmp-cvs/wcms/Repository/wcms/error.php,v $
 * Revision:    $Revision: 1.4 $
 * Last Edit:   $Date: 2005/09/05 10:32:50 $
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
 * @version $Revision: 1.4 $
 */

/* $Id: error.php,v 1.4 2005/09/05 10:32:50 streaky Exp $ */

require_once("boot.php");

$item_data = "
<h2 id=\"toc0\">Error 404 - Document Not Found</h2>
<p>The requested URL could not be found on this server. The link you followed is either outdated, inaccurate, or the server has been instructed not to allow access to it.</p>
<p>Your unsuccessful attempt to access <b>http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}</b> has been logged for checking and possible rectification.</p>
";
        
require_once("boot.php");

$smarty->assign("breadcrumb", "<a href='".path::http()."'>Home</a> > Error");

$page_handler->add_page_item($item_data, "Error (File Not Found)");
$page_handler->add_footer("&nbsp;");

$page_handler->output_page("Error (File Not Found)");

?>