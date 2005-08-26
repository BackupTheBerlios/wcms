<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty session_urls.php outputfilter plugin
 *
 * File:     session_urls.php
 * Type:     outputfilter
 * Name:     session_urls
 * Date:     Jan 25, 2003
 * Purpose:  Rewrite URL's to stop session id's from destroying xhtml validity - by swaping & for &amp;
 * Install:  Drop into the plugin directory, call
 *           <code>$smarty->load_filter('output','session_urls');</code>
 *           from application.
 * @author   Martin Nicholls <webmasta at streakyland dot co dot uk>
 * @version  1.0
 * @param string
 * @param Smarty
 */
function smarty_outputfilter_rewrite_urls($source, &$smarty) {

  return rewrite($source);
}

?>
