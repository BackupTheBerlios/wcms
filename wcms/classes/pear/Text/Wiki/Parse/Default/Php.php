<?php

/**
* 
* Parses for blocks of PHP code.
* 
* @category Text
* 
* @package Text_Wiki
* 
* @author Paul M. Jones <pmjones@php.net>
* 
* @license LGPL
* 
* @version $Id: Php.php,v 1.1 2005/08/21 15:56:16 streaky Exp $
* 
*/

/**
* 
* Parses for blocks of PHP code.
* 
* This class implements a Text_Wiki_Parse to find source text marked as
* HTML to be redndred as-is.  The block start is marked by <html> on its
* own line, and the block end is marked by </html> on its own line.
*
* @category Text
* 
* @package Text_Wiki
* 
* @author Paul M. Jones <pmjones@php.net>
* 
*/

class Text_Wiki_Parse_Php extends Text_Wiki_Parse {
    
    
    /**
    * 
    * The regular expression used to parse the source text and find
    * matches conforming to this rule.  Used by the parse() method.
    * 
    * @access public
    * 
    * @var string
    * 
    * @see parse()
    * 
    */
    
    var $regex = '/^\<php\>\n(.+)\n\<\/php\>(\s|$)/Umsi';
    
    
    /**
    * 
    * Generates a replacement for the matched text.  Token options are:
    * 
    * 'text' => The text of the PHP to be rendered as-is.
    * 
    * @access public
    *
    * @param array &$matches The array of matches from parse().
    *
    * @return A delimited token to be used as a placeholder in
    * the source text, plus any text following the HTML block.
    *
    */
    
    function process(&$matches)
    {    
        $options = array('text' => $matches[1]);
        return $this->wiki->addToken($this->rule, $options) . $matches[2];
    }
}
?>