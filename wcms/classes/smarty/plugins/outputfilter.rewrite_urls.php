<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty rewrite_urls outputfilter plugin
 *
 * File:     outputfilter.rewrite_urls.php<br>
 * Type:     outputfilter<br>
 * Name:     rewrite_urls<br>
 * Date:     Jan 25, 2003<br>
 * Purpose:  Rewrite URL's for mod_rewite and other such purposes
 * Install:  Drop into the plugin directory, call
 *           <code>$smarty->load_filter('output','rewrite_urls');</code>
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
