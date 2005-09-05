<?php

class Text_Wiki_Render_Xhtml_Code extends Text_Wiki_Render {

	var $conf = array(
	'css'      => null, // class for <pre>
	'css_code' => null, // class for generic <code>
	'css_php'  => null, // class for PHP <code>
	'css_html' => null // class for HTML <code>
	);

	/**
    * 
    * Renders a token into text matching the requested format.
    * 
    * @access public
    * 
    * @param array $options The "options" portion of the token (second
    * element).
    * 
    * @return string The text rendered from the token options.
    * 
    */

	function token($options)
	{
		$text = $options['text'];
		$attr = $options['attr'];
		$type = strtolower($attr['type']);

		$css      = $this->formatConf(' class="%s"', 'css');
		$css_code = $this->formatConf(' class="%s"', 'css_code');
		$css_php  = $this->formatConf(' class="%s"', 'css_php');
		$css_html = $this->formatConf(' class="%s"', 'css_html');
		
		$geshi_class = path::file("plugins")."geshi/geshi.php";
		
		if($type != "" && file_exists(path::file("plugins")."geshi/geshi.php") && is_readable(path::file("plugins")."geshi/geshi.php")) {

			require_once(path::file("plugins")."geshi/geshi.php");

			$geshi = new GeSHi(trim($text), $type, path::file("plugins")."geshi/geshi/");

			$geshi->set_encoding("utf-8");
			$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS, 1);
			$geshi->set_header_type(GESHI_HEADER_DIV);
			
			$geshi->enable_classes();
			$geshi->set_overall_class('geshi_code');
			
			$text = $geshi->parse_code();
			
			$style = $geshi->get_stylesheet();
			
			global $page_handler;
			$style = "<style type='text/css'>\n{$style}\n</style>";
			$page_handler->add_header_data($style);
			
		} else {
			//generic code example:
			//convert tabs to four spaces,
			//convert entities.
			$text = trim(htmlentities($text));
			$text = str_replace("\t", " &nbsp; &nbsp;", $text);
			$text = str_replace("  ", " &nbsp;", $text);
			$text = "<code{$css_code}>{$text}</code>";
		}

		return "\n{$text}\n\n";
	}
}
?>