<?php

/**
 * Project:     wCMS: Wiki style CMS
 * File:        $Source: /home/xubuntu/berlios_backup/github/tmp-cvs/wcms/Repository/wcms/classes/session_class.php,v $
 * Revision:    $Revision: 1.4 $
 * Last Edit:   $Date: 2005/09/02 09:34:51 $
 * By:          $Author: streaky $
 *
 *  Copyright © 2005 Martin Nicholls
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @link http://wcms.berlios.de/
 * @copyright 2005 Martin Nicholls
 * @author Martin Nicholls <webmasta at streakyland dot co dot uk>
 * @package wCMS
 * @version $Revision: 1.4 $
 */

/* $Id: session_class.php,v 1.4 2005/09/02 09:34:51 streaky Exp $ */

/**
 * Basic session handling class, options can be overwrriden by
 * passing an options array when creating session object.
 */
class session_handler {

	/**
     * MDB2 Object used by the class to access the database.
     *
     * @var     object
     * @access  private
     */
	var $_db_object;

	/**
     * Class option store - default options can be overwritten by the class contructor
     *
     * @var     array
     * @access  private
     */
	var $_session_options = array(
		'session_lifetime' => 240,
		'gc_probability'   => 1,
		'gc_divisor'       => 3
	);

	/**
     * Session data storge var (unused)
     *
     * @var     array
     * @access  private
     */
	var $_session_data = array();

	/**
	 * Class constructor - sets the session handler to this object, sets up class and
	 * session options and handles starting the session.
	 *
	 * @param array $options
	 * @return session_handler
	 */
	function session_handler($options) {

		// extract the class options, overwriting where set
		foreach ($options as $key => $value) {
			if($key != "db_object") {
				$this->_session_options[$key] = $value;
			} else {
				$this->_db_object = $value;
			}
		}

		// The next 2 options options set probablity for garbage collection
		// for example gc_probability / gc_divisor = 1 / 3 = 33.3%
		// so 33% of visits will force garbage collection.
		// or 10 / 100 = 1 / 10 = 10% for example - don't set too lean unless your site has high traffic
		ini_set("session.gc_probability", $this->_session_options['gc_probability']);
		ini_set("session.gc_divisor",     $this->_session_options['gc_divisor']);

		// Set this session save handler to this object
		session_set_save_handler(
		array(&$this, 'session_open'),
		array(&$this, 'session_close'),
		array(&$this, 'session_read'),
		array(&$this, 'session_write'),
		array(&$this, 'session_destroy'),
		array(&$this, 'session_gc')
		);

		// Start the session
		session_start();
	}

	function session_open($save_path, $session_name) {
		return true;
	}

	function session_close() {
		return true;
	}

	/**
     * Read session data from database tables if session already open, else
     * create a new session in the database by inserting a new row. Called
     * by PHP on session_start()
     *
     * @param string $sid
     * @return array
     */
	function session_read($sid) {
		global $db_prefix;
		$query = "SELECT * FROM {$db_prefix}sessions WHERE session_id = ".$this->_db_object->quote($sid, 'text');
		$this->_db_object->setLimit(1);
		$result = $this->_db_object->query($query);
		$rows = $result->fetchAll(MDB2_FETCHMODE_ASSOC);
		$result->free();
		if(count($rows) < 1){
			$query = "INSERT INTO {$db_prefix}sessions (session_id, user_id, session_data, session_modified) VALUES (".$this->_db_object->quote($sid, 'text').", ".$this->_db_object->quote(0, 'integer').", ".$this->_db_object->quote('', 'text').", ".$this->_db_object->quote(time(), 'integer').")";
			$this->_db_object->query($query);
			return array();
		} else {
			return $rows[0]['session_data'];
		}
	}

	function session_write($sid, $data) {
		global $db_prefix;
		$query = "UPDATE {$db_prefix}sessions SET
                  user_id = ".         $this->_db_object->quote(0, 'integer').",
                  session_data = ".    $this->_db_object->quote($data, 'text').",
                  session_modified = ".$this->_db_object->quote(time(), 'integer')."
                  WHERE session_id = ".$this->_db_object->quote($sid, 'text');
		$this->_db_object->query($query);
		return true;
	}

	function session_destroy($sid) {
		global $db_prefix;
		$query = "DELETE FROM {$db_prefix}sessions WHERE session_id = ".$this->_db_object->quote($sid, 'text');
		$this->_db_object->query($query);
		return true;
	}

	/**
     * Garbage collection class function - handles removal of old sessions (Called by
     * PHP internally, based on session.gc_probability and session.gc_divisor values)
     *
     * @param int $lifetime
     * @return bool
     */
	function session_gc($lifetime) {
		global $db_prefix;
		$query = "DELETE FROM {$db_prefix}sessions WHERE session_modified < ".$this->_db_object->quote((time() - $this->_session_options['session_lifetime']), 'integer');
		$this->_db_object->query($query);
		return true;
	}
}

?>