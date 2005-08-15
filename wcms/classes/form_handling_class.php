<?php

class form_handling {

	var $_options = array();
	var $_temp_content = array();
	var $_temp_fieldsets = array();
	var $_temp_buttons = array();
	var $_hidden_data = array();
	var $form_array;

	function form_handling($options){
		foreach ($options as $option => $value){
			if($option != "smarty") {
				$this->_options[$option] = $value;
			} else {
				$this->smarty = $value;
			}
		}
	}

	function add_text_field($options) {
		if(!$options['type']) {
			$options['type'] = 'text';
		}
		$this->smarty->assign("form_field_label", $options['label']);
		$this->smarty->assign("form_field_max_length", $options['max_length']);
		$this->smarty->assign("form_field_name", $options['name']);
		$this->smarty->assign("form_field_id", $options['id']);
		$this->smarty->assign("form_field_type", $options['type']);
		$this->smarty->assign("form_field_size", $options['size']);
		$this->smarty->assign("form_field_description", $options['description']);
		$this->smarty->assign("form_field_value", $options['value']);
		if($options['required'] === true) {
			$this->smarty->assign("form_field_required", " <span class=\"form-required\">*</span>");
		} else {
			$this->smarty->assign("form_field_required", "");
		}
		$this->_temp_content[] = $this->smarty->fetch("default/forms/form_field_text.html");
	}

	function add_text_area($options) {
		$this->smarty->assign("form_field_label", $options['label']);#
		$this->smarty->assign("form_field_cols", $options['cols']);#
		$this->smarty->assign("form_field_name", $options['name']);#
		$this->smarty->assign("form_field_id", $options['id']);#
		$this->smarty->assign("form_field_rows", $options['rows']);
		$this->smarty->assign("form_field_description", $options['description']);
		$this->smarty->assign("form_field_value", $options['value']);
		if($options['required'] === true) {
			$this->smarty->assign("form_field_required", " <span class=\"form-required\">*</span>");
		} else {
			$this->smarty->assign("form_field_required", "");
		}
		$this->_temp_content[] = $this->smarty->fetch("default/forms/form_field_textarea.html");
	}

	function add_hidden($options) {
		$this->_hidden_data[] = "<input type=\"hidden\" name=\"{$options['name']}\" value=\"{$options['value']}\" />";
	}

	function add_button($options) {
		$this->_temp_buttons[] = "<input type=\"{$options['type']}\" class=\"form-submit\" id=\"{$options['id']}\" value=\"{$options['title']}\" />";
	}

	function add_checkbox($options) {
		$this->_temp_content[] = "<div class=\"form-item\">
 <label for=\"{$options['id']}\">{$options['label']}:</label><br />
 <label class=\"option\"><input type=\"checkbox\" class=\"form-checkbox\" id=\"{$options['id']}\" name=\"{$options['name']}\" value=\"1\"".($options['checked'] === true ? " checked=\"checked\"" : "")." /> {$options['label']}</label><br />
 <div class=\"description\">{$options['description']}</div>
</div>";
	}

	function add_dropdown($options) {
		$form = "
<div class='form-item'>
 <label for='{$options['id']}'>{$options['label']}:</label><br />
 <select name='{$options['name']}' id='{$options['id']}'>
";
		foreach ($options['item_list'] as $_item) {
			$form .= "\n <option value='{$_item['value']}'".($_item['value'] == $options['selected'] ? " selected='selected'" : "").">{$_item['title']}</option>";
		}
		$form .= "
 </select>
 <div class='description'>{$options['description']}</div>
</div>
";
		$this->_temp_content[] = $form;
	}

	function build_fieldset($legend) {
		$this->smarty->assign("form_fieldset_legend", $legend);
		$fieldset = implode("\n", $this->_temp_content);
		$this->_temp_content = array();

		$fieldset .= "\n\n".implode("\n", $this->_hidden_data);
		$this->_hidden_data = array();

		$fieldset .= "\n\n".implode("\n", $this->_temp_buttons);
		$this->_temp_buttons = array();

		$this->smarty->assign("form_fieldset_content", $fieldset);
		$this->_temp_fieldsets[] = $this->smarty->fetch("default/forms/form_fieldset_body.html");
	}


	function return_form() {
		$this->smarty->assign("form_page_title", $this->_options['title']);
		$this->smarty->assign("form_page_description", $this->_options['description']);
		$this->smarty->assign("form_page_response", $this->_options['response']);
		$this->smarty->assign("form_page_action", $this->_options['action']);
		$this->smarty->assign("form_page_method", $this->_options['method']);

		$page = implode("\n", $this->_temp_fieldsets);
		$this->_temp_fieldsets = array();

		$this->smarty->assign("form_page_content", $page);

		return $this->smarty->fetch("default/forms/form_page_body.html");
	}

	function form_array($type, $options) {
		$this->form_array[] = array(
		'type'    => $type,
		'options' => $options,
		);
	}
}

?>
