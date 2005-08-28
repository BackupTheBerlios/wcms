<?php

/**
 * Project:     wCMS: Wiki style CMS
 * File:        $Source: /home/xubuntu/berlios_backup/github/tmp-cvs/wcms/Repository/wcms/content.php,v $
 * Revision:    $Revision: 1.7 $
 * Last Edit:   $Date: 2005/08/28 00:13:29 $
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

/* $Id: content.php,v 1.7 2005/08/28 00:13:29 streaky Exp $ */

require_once("boot.php");

$item = (!vars::get('page') ? "home_page" : vars::get('page'));
$item = preg_replace("#\W#", "", $item);

$page_content = $content->retrieve($item);
$page_handler->add_page_item($page_content['content'], $page_content['title']);
$footer = $content->create_content_footer($page_content['ident'], $page_content['last_mod'], $page_content['revision'], $page_content['title']);
$page_handler->add_footer($footer);

$page_handler->output_page($page_content['title']);

?>