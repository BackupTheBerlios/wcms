<?php

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
    'value'       => $row['cont_content'],
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