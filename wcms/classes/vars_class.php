<?php

/**
 * Return a request variable.
 *
 * If Magic Quotes is enabled, stripslashes is applied.
 *
 * @author       Aidan Lister <aidan@php.net>
 * @version      1.0.0
 */
class vars {
    /**
     * Handle the fetching
     *
     * Other methods are just wrappers of this function
     */
    function _request($global, $name, $array = false) {
        // Array of request superglobals
        $request = array(
                'cookie' => $_COOKIE,
                'post' => $_POST,
                'get' => $_GET
        );
        
        if($array) {
          // Check if the variable exists
          if (!isset($request[$global][$array][$name])) {
              $request[$global][$array][$name] = null;
          }
          // Save the variable
          $var = $request[$global][$array][$name];
        } else {
          // Check if the variable exists
          if (!isset($request[$global][$name])) {
              $request[$global][$name] = null;
          }
          // Save the variable
          $var = $request[$global][$name];
        }

        // If Magic Quotes is enabled, apply stripslashes
        if (get_magic_quotes_gpc() && $var !== null) {
            $var = stripslashes($var);
        }
 
        return $var;
    }
 
 
    /**
     * Return a POST variable.
     */
    function post($var, $array = false) {
        return Vars::_request('post', $var, $array);
    }
 
 
    /**
     * Return a GET variable.
     */
    function get($var, $array = false) {
        return Vars::_request('get', $var, $array);
    }
 
 
    /**
     * Return a COOKIE variable.
     */
    function cookie($var, $array = false)
    {
        return Vars::_request('cookie', $var, $array);
    }
}

?>
