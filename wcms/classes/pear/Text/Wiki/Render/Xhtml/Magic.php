<?php

class Text_Wiki_Render_Xhtml_Magic extends Text_Wiki_Render {
    
    var $conf = array();
	
    function token($options) {
    	return print_a($options, true);
    }
}

?>