<?php

class Text_Wiki_Render_Xhtml_Magic extends Text_Wiki_Render {

	var $conf = array();

	function token($options) {
		return print_a($options, true);

		if($options[0] == "thumb") {
			$sub_opts = explode("|", $options[1]);
			foreach ($sub_opts as $opt) {
				$this_opts = explode("=", $opt);
			}
			unset($options);
			foreach ($this_opts as $option){
				$options[$option['0']] = $option[1];
			}
			if(isset($options['file']) && file_exists(path::file("images").$options['file'])) {
				if(!isset($options['maxw'])) {
					$options['maxw'] = 20000;
				} else {
					$options['maxw'] = intval($options['maxw']);
				}
				if(!isset($options['maxh'])) {
					$options['maxh'] = 20000;
				} else {
					$options['maxh'] = intval($options['maxh']);
				}
				return $this->create_thumb($options['file'], $options['maxw'], $options['maxh']);
			}
		}
	}
	
	function create_thumb($filename, $max_width, $max_height) {

		$size = GetImageSize(path::file("images").$filename); // Read the size
		$width = $size[0];
		$height = $size[1];

		// Proportionally resize the image to the
		// max sizes specified above

		$x_ratio = $max_width / $width;
		$y_ratio = $max_height / $height;

		if( ($width <= $max_width) && ($height <= $max_height)) {
			$tn_width = $width;
			$tn_height = $height;
		} elseif (($x_ratio * $height) < $max_height) {
			$tn_height = ceil($x_ratio * $height);
			$tn_width = $max_width;
		} else {
			$tn_width = ceil($y_ratio * $width);
			$tn_height = $max_height;
		}
		return "<img src='".path::http("images")."{$filename}' style='height: {$tn_width}; width: {$tn_height};' />";
	}
}

?>