<?php

class Text_Wiki_Parse_Magic extends Text_Wiki_Parse {
	
	function Text_Wiki_Parse_Magic(&$obj)
	{
		parent::Text_Wiki_Parse($obj);
		
		$this->regex =
			'/' .												   // START regex
			"\\(\\(" .										 // double open-parens
			"\:" .                          // leading colon means magic
			"(" .												   // START function name
			"[a-z]+" .                     // Function name
			")" .												   // END function name
			"((?:" .												   // START options
			" " .                          // Space delimiter
			"[a-zA-Z0-9]+" .               // Text of options
			")*)" .                         // END options
			"\\)\\)" .									   // double close-parens
			'/';												   // END regex
			
			//echo($this);
	}
	
	function process(&$matches)
	{
		
		// set the options
		$options = explode(" ",$matches[2]);
		array_shift($options);
		array_unshift($options,$matches[1]);
		
		// return a token placeholder
		return $this->wiki->addToken($this->rule, $options);
	}
}

?>