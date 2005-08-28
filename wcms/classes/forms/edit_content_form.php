<?php

/**
 * Project:     wCMS: Wiki style CMS
 * File:        $Source: /home/xubuntu/berlios_backup/github/tmp-cvs/wcms/Repository/wcms/classes/forms/edit_content_form.php,v $
 * Revision:    $Revision: 1.3 $
 * Last Edit:   $Date: 2005/08/28 05:25:54 $
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
 * @version $Revision: 1.3 $
 */

/* $Id: edit_content_form.php,v 1.3 2005/08/28 05:25:54 streaky Exp $ */

$form_item = array (
    'label'       => "Page Title",
    'max_length'  => 80,
    'name'        => "wikiedit[page_title]",
    'id'          => "wikiedit-page_title",
    'size'        => 60,
    'description' => "The main title of the page you are creating",
    'value'       => ($row['cont_title'] != "" ? $row['cont_title'] : $item),
    'required'    => true,
);
$form->add_text_field($form_item);

$form_item = array (
    'label'       => "Page Identifier",
    'max_length'  => 80,
    'name'        => "wikiedit[page_tag]",
    'id'          => "wikiedit-page_tag",
    'size'        => 60,
    'description' => "The identity tag of the item you are editing",
    'value'       => $item,
    'required'    => true,
);
$form->add_text_field($form_item);

$form_item = array (
    'label'       => "Page Content",
    'cols'        => 80,
    'name'        => "wikiedit[page_content]",
    'id'          => "wikiedit-page_content",
    'rows'        => 32,
    'description' => "The content of the page you are creating (Wiki parsed on display)",
    'value'       => utf8_entities($row['cont_content']),
    'required'    => true,
);
$form->add_text_area($form_item);

$form_item = array (
    'label'       => "Parent Identifier",
    'name'        => "wikiedit[parent_ident]",
    'id'          => "wikiedit-parent_ident",
    'description' => "The identity tag of this page's parent",
    'item_list'   => $pages,
    'selected'    => ($row['cont_parent_id'] != "" && $row['cont_parent_id'] != 0 ? $row['cont_parent_id'] : $home_id),
);
$form->add_dropdown($form_item);

$form->build_fieldset("Page Content");

$form_item = array (
    'label'       => "Changes Description",
    'cols'        => 80,
    'name'        => "wikiedit[change_desc]",
    'id'          => "wikiedit-change_desc",
    'rows'        => 7,
    'description' => "Description of the changes you made",
    'value'       => "",
    'required'    => false,
);
$form->add_text_area($form_item);

$form_item = array (
    'label'       => "Major Changes",
    'checked'     => false,
    'name'        => "wikiedit[change_major]",
    'id'          => "wikiedit-change_major",
    'description' => "Are the changes you made major (Almost total rewrite)",
);
$form->add_checkbox($form_item);

$form->build_fieldset("Change Tracking");

$form_item = array (
    'name'  => "wikiedit[old_tag]",
    'value' => (!count($rows) ? "" : $item),
);
$form->add_hidden($form_item);

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

$form->build_fieldset("Submit Form");

?>