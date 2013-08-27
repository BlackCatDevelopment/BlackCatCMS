<?php

/**
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 * 
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 *   @author          Website Baker Project, LEPTON Project, Black Cat Development
 * @copyright       2004-2010, Website Baker Project
 *   @copyright       2011-2012, LEPTON Project
 * @copyright       2013, Black Cat Development
 * @link            http://blackcat-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if (defined('CAT_PATH')) {	
	include(CAT_PATH.'/framework/class.secure.php'); 
} else {
	$root = "../";
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) { 
		include($root.'/framework/class.secure.php'); 
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}

require_once(CAT_PATH.'/framework/functions.php');


global $database;
	
if ( ! class_exists('database', false ) )
{
    class database {
	
	private $error = '';
	private $connected = false;     
	private	$db_handle = false;
	private	$prompt_on_error = false;
	private	$override_session_check = false;
	
	/**
	 * Constructor of the class database
	 */
	public function __construct() {
		$this->connect();
	} // __construct()
	
	/**
	 * Destructor of the class database
	 */
	public function __destruct() {
		
	} // __desctruct()
	
	/**
	 * Set error
	 * @param string
	 */
	protected function set_error($error = '') {
		$this->error = $error;
	} // set_error()
	
	/**
	 * Return the last error
	 * @return string
	 */
	public function get_error() {
		return $this->error;
	} // get_error()
	
	/**
	 * Check if there occured any error
	 * @return boolean
	 */
	public function is_error() {
		return (!empty($this->error)) ? true : false;
	} // is_error()
	
	/**
	 * Set the MySQL DB handle
	 * 
	 * @param resource $db_handle
	 */
	protected function set_db_handle($db_handle) {
	    $this->db_handle = $db_handle;
	} // set_db_handle()
	
	/**
	 * Get the MySQL DB handle
	 * 
	 * @return resource or boolean false if no connection is established
	 */
	protected function get_db_handle() {
	    return $this->db_handle;
	} // get_db_handle()
	
	/**
	 * Set the connection state
	 * 
	 * @param boolean $connected
	 */
	protected function set_connected($connected) {
	    $this->connected = $connected;
	} // set_connected()
	
	/**
	 * Check if the connection is established
	 * 
	 * @return boolean
	 */
	protected function is_connected() {
	    return $this->connected;
	} // is_connected()
	
	/**
	 * Establish the connection to the desired database defined in /config.php
	 * 
	 * This function does not connect multiple times, if the connection is
	 * already established the existing database handle will be used.
	 * 
	 * @return boolean
	 */
	final function connect() {
		// use CAT_DB_PORT only if it differ from the standard port 3306
		$host = (CAT_DB_PORT !== '3306') ? CAT_DB_HOST.':'.CAT_DB_PORT : CAT_DB_HOST;
		if (false !== ($db_handle = mysql_connect($host, CAT_DB_USERNAME, CAT_DB_PASSWORD))) {
			// database connection is established
			$this->set_db_handle($db_handle);
			if (!mysql_select_db(CAT_DB_NAME, $this->get_db_handle())) {
				// error, can't select the DB
				$this->set_error(sprintf("[MySQL Error] Retrieved a valid handle (<b>%s</b>) but can't select the database (<b>%s</b>)!", $this->get_db_handle(), CAT_DB_NAME));
				trigger_error($this->get_error(), E_USER_ERROR);
			}
			else {
				$this->set_connected(true);
                mysql_query('SET CHARACTER SET utf8');
			}
		}
		else {
			// error, got no handle - beware, password may be empty!
			$this->set_db_handle(false);
			$pass = CAT_DB_PASSWORD;
			$pass = (empty($pass)) ? '- not set -' : CAT_DB_PASSWORD;
			$this->set_error('[MySQL Error] Got no handle for database connection! Please check your database settings!');
			trigger_error($this->get_error(), E_USER_ERROR);
		}
		return $this->is_connected();
	} // connect()
	
	/**
	 * Disconnect the established database connection.
	 * 
	 * Normally it is not neccessary to call this function, the database
	 * connection will be automatically closed by server.
	 * @return BOOL
	 */
	final protected function disconnect() {
		if ($this->is_connected()) {
			if (!mysql_close($this->get_db_handle())) {
				$this->set_error(sprintf('[MySQL Error #%d] %s', mysql_errno($this->get_db_handle()), mysql_error($this->get_db_handle())));
				return false;
			}
		}
		return true;
	} // disconnect()
	
	/**
	 * Switch prompting of errors on or off
	 * If $switch=true the database will trigger each error.
	 * 
	 * @param boolean $switch
	 */
	public function prompt_on_error($switch=true) {
		$this->prompt_on_error = $switch;
	} // prompt_on_error()
	
	/**
	 * Exec a SQL query and return a handle to queryMySQL
	 * @param STR $SQL - the query string to execute
	 * @return RESOURCE or NULL for error
	 */
	public function query($SQL) {
		if (!isset($_SESSION['CAT_SESSION']) && !$this->override_session_check) $this->__initSession();
        // reset error
        $this->set_error(NULL);
		$query = new queryMySQL();
		if (false !== $query->query($SQL, $this->get_db_handle())) {
			// proper execution of the query
			return $query;
		}
		else {
			$caller  = debug_backtrace();
            $errfile = $caller[0]['file'];
            $errfile = str_ireplace( array(CAT_PATH,'\\'), array('/abs/path/to','/'), $errfile );
			$this->set_error(sprintf(	'MySQL Query executed from file <b>%s</b> in line <b>%s</b>:<br />[MySQL Error #%d] %s<br /><b>Executed Query:</b><br /><i>%s</i><br />', 
										$errfile,
										$caller[0]['line'], 
										mysql_errno($this->get_db_handle()), 
										mysql_error($this->get_db_handle()), 
										$SQL));
			if (true === $this->prompt_on_error) {
				trigger_error($this->get_error(), E_USER_ERROR);
			}
			return null;
		}
	} // query()
	
	/**
	 *	Execute a SQL query and return the first row of the result array
	 *
	 *	@param	string	Any SQL-Query or statement
	 *	@param	const	Type for the fetchRow-method for the return-array.
	 *					Could be one of the following constants:
	 *					MYSQL_ASSOC, MYSQL_NUM or MYSQL_BOTH (default).
	 *					Any other value will result in MYSQL_BOTH.
	 *
	 *	@return	mixed 	Value of the table-field or NULL for error
	 */
	public function get_one($SQL, $type=MYSQL_BOTH) {
		$query = $this->query($SQL);
		if (($query !== null) && ($query->numRows() > 0)) {
			
			switch( $type ) {				
				case MYSQL_BOTH:
				case MYSQL_NUM:
				case MYSQL_ASSOC:
					break;			
				default:
					$type = MYSQL_BOTH;
			}
			
			$rows = $query->fetchRow($type);
			
			if ($type === MYSQL_ASSOC) {
				$temp = array_values($rows);
				return $temp[0];
			} else {
				return $rows[0];
			}
		}	
		return null;
	} // get_one()
	
	private function __initSession() {
		if (defined('SESSION_STARTED') && !isset($_SESSION['CAT_SESSION'])) {
			// $_SESSION for class.database.php
			$_SESSION['CAT_SESSION'] = true;
		}
	} // __initSession()
	
    } // class database
}

if ( ! class_exists('queryMySQL', false ) )
{
    final class queryMySQL {
	
	private $query_result = false;
	
	/**
	 * Execute a MySQL query statement and return the resource or false on error
	 * 
	 * @param string $SQL query
	 * @param resource $db_handle - a valid link identifier
	 * 
	 * @return resource
	 */
	public function query($SQL, $db_handle) {
		$this->query_result = mysql_query($SQL, $db_handle);
		return $this->query_result;
	} // query()
	
	/**
	 * Return the number of rows of the query result
	 * @return INT
	 */
	public function numRows() {
		return mysql_num_rows($this->query_result);
	} // numRows()
	
	/**
	 * Fetch a Row from the result array
	 * 
	 * Specify $array_type you want to get back: MYSQL_BOTH, MYSQL_NUM or MYSQL_ASSOC
	 * this function return FALSE if there is no further row.
	 * @param INT $array_type
	 * @return ARRAY
	 */
	public function fetchRow($array_type = MYSQL_BOTH) {
		return mysql_fetch_array($this->query_result, $array_type);
	} // fetchRow()
	
    } // class queryMySQL
}