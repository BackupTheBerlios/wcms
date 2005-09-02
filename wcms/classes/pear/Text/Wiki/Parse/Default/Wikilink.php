<?php

/**
* 
* Parse for links to wiki pages.
* 
* @category Text
* 
* @package Text_Wiki
* 
* @author Paul M. Jones <pmjones@php.net>
* 
* @license LGPL
* 
* @version $Id: Wikilink.php,v 1.3 2005/09/02 09:55:05 streaky Exp $
* 
*/

/**
* 
* Parse for links to wiki pages.
*
* Wiki page names are typically in StudlyCapsStyle made of
* WordsSmashedTogether.
*
* You can also create described links to pages in this style:
* [WikiPageName nice text link to use for display]
*
* The token options for this rule are:
*
* 'page' => the wiki page name.
* 
* 'text' => the displayed link text.
* 
* 'anchor' => a named anchor on the target wiki page.
* 
* @category Text
* 
* @package Text_Wiki
* 
* @author Paul M. Jones <pmjones@php.net>
* 
*/

class Text_Wiki_Parse_Wikilink extends Text_Wiki_Parse {
    
    var $conf = array (
    	'ext_chars' => false
    );
    
    /**
    * 
    * Constructor.
    * 
    * We override the Text_Wiki_Parse constructor so we can
    * explicitly comment each part of the $regex property.
    * 
    * @access public
    * 
    * @param object &$obj The calling "parent" Text_Wiki object.
    * 
    */
    
    function Text_Wiki_Parse_Wikilink(&$obj)
    {
        parent::Text_Wiki_Parse($obj);
        

		$either = "_A-Za-z0-9\xc0-\xfe";
		
        // build the regular expression for finding WikiPage names.
        $this->described_regex =
            "(!?" .            // START WikiPage pattern (1)
            "[$either]+" .      // 1+ alpha or digit
             ")" .              // END WikiPage pattern (/1)
            "((\#" .           // START Anchor pattern (2)(3)
            "[$either]" .      // 1 alpha
            "(" .              // start sub pattern (4)
            "[-_$either:.]*" . // 0+ dash, alpha, digit, underscore, colon, dot
            "[-_$either]" .    // 1 dash, alpha, digit, or underscore
            ")?)?)";           // end subpatterns (/4)(/3)(/2)
            
        $this->encased_regex =
            "(!?" .            // START WikiPage pattern (1)
            "[$either]+" .      // 1+ alpha or digit
 //            ")" .              // END WikiPage pattern (/1)
            "?)";           // end subpatterns (/4)(/3)(/2)
            
                // build the regular expression for finding WikiPage names.
       /* $this->standalone_regex =
            "(!?" .            // START WikiPage pattern (1)
            "[$either]" .       // 1 upp$eitherer
            "[$either]*" .     // 0+ alpha or digit
            "[$lower]+" .      // 1+ lower or digit
            "[$upper]" .       // 1 upper
            "[$either]*" .     // 0+ or more alpha or digit
            ")" .              // END WikiPage pattern (/1)
            "((\#" .           // START Anchor pattern (2)(3)
            "[$either]" .      // 1 alpha
            "(" .              // start sub pattern (4)
            "[-_$either:.]*" . // 0+ dash, alpha, digit, underscore, colon, dot
            "[-_$either]" .    // 1 dash, alpha, digit, or underscore
            ")?)?)";           // end subpatterns (/4)(/3)(/2)*/
    }
    
    
    /**
    * 
    * First parses for described links, then for standalone links.
    * 
    * @access public
    * 
    * @return void
    * 
    */
    
    function parse()
    {
        // described wiki links
        $tmp_regex = '/\[' . $this->described_regex . ' (.+?)\]/';
        $this->wiki->source = preg_replace_callback(
            $tmp_regex,
            array(&$this, 'processDescr'),
            $this->wiki->source
        );
        
        $tmp_regex = '/\[' . $this->encased_regex . '\]/';
        $this->wiki->source = preg_replace_callback(
            $tmp_regex,
            array(&$this, 'processEncased'),
            $this->wiki->source
        );
        
        // standalone wiki links
        /*if ($this->getConf('ext_chars')) {
			$either = "A-Za-z0-9\xc0-\xfe";
		} else {
			$either = "A-Za-z0-9";
		}*/
		
        /*$tmp_regex = '/(^|[^{$either}\-_])' . $this->standalone_regex . '/';
        $this->wiki->source = preg_replace_callback(
            $tmp_regex,
            array(&$this, 'processStandalone'),
            $this->wiki->source
        );*/
        
    }
    
    
    /**
    * 
    * Generate a replacement for described links.
    * 
    * @access public
    *
    * @param array &$matches The array of matches from parse().
    *
    * @return A delimited token to be used as a placeholder in
    * the source text, plus any text priot to the match.
    *
    */
    
    function processDescr(&$matches)
    {
        // set the options
        $options = array(
            'page'   => $matches[1],
            'text'   => $matches[5],
            'anchor' => $matches[3],
        );
        
        // create and return the replacement token and preceding text
        return $this->wiki->addToken($this->rule, $options); // . $matches[7];
    }
    
    
    /**
    * 
    * Generate a replacement for standalone links.
    * 
    * 
    * @access public
    *
    * @param array &$matches The array of matches from parse().
    *
    * @return A delimited token to be used as a placeholder in
    * the source text, plus any text prior to the match.
    *
    */
    
    function processEncased(&$matches)
    {
    
        // when prefixed with !, it's explicitly not a wiki link.
        // return everything as it was.
        if ($matches[2]{0} == '!') {
            return $matches[1] . substr($matches[2], 1) . $matches[3];
        }
        
        // set the options
        $options = array(
            'page' => $matches[1],
            'text' => false,
            'anchor' => false
        );
                
        // create and return the replacement token and preceding text
        return $this->wiki->addToken($this->rule, $options);
    }
}

?>