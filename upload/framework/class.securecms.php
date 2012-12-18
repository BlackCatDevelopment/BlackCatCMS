<?php
 /**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          Frank Heyne for the LEPTON Project 
 * @copyright       2011, Frank Heyne, LEPTON Project
 * @link            http://www.lepton2.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 *
 */

class SecureCMS {
	var $_salt   = '';

	function SecureCMS()
	{
		$this->_salt = $this->_generate_salt();
	}

	function _generate_salt()
	{
		// server depending values
 		$salt  = ( isset($_SERVER['SERVER_SIGNATURE']) ) ? $_SERVER['SERVER_SIGNATURE'] : 'L';
		$salt .= ( isset($_SERVER['SERVER_SOFTWARE']) ) ? $_SERVER['SERVER_SOFTWARE'] : 'E';
		$salt .= ( isset($_SERVER['SERVER_NAME']) ) ? $_SERVER['SERVER_NAME'] : 'P';
		$salt .= ( isset($_SERVER['SERVER_ADDR']) ) ? $_SERVER['SERVER_ADDR'] : 'T';
		$salt .= ( isset($_SERVER['SERVER_PORT']) ) ? $_SERVER['SERVER_PORT'] : 'ON';
		$salt .= PHP_VERSION;
		$salt .= time();
		return $salt;
	}

/*
 * creates Tokens for CSRF protection
 * @access public
 * @return string
 *
 * requirements: an active session must be available
 * should be called only once for a page!
 */
	function getToken()
	{
		if (function_exists('microtime')) {
			list($usec, $sec) = explode(" ", microtime());
			$time = (string)((float)$usec + (float)$sec);
		} else {
			$time = (string)time();
		}
		$token = substr(md5($time . $this->_salt), 0, 21) . "z" . substr($time, 0, 10);
		(isset($_SESSION['Tokens'])) ? $_SESSION['Tokens'][] = $token : $_SESSION['Tokens'] = array($token);
		return $token;
	}

/*
 * checks received token against session-stored tokens
 * @access public
 * @return bool:    true if numbers matches against one of the stored tokens
 *
 * requirements: an active session must be available
 * this check will prevent from multiple sending a form. history.back() also will never work
 */
	function checkToken()
	{
		if (!LEPTOKEN_LIFETIME) return true;
		
		$timelimit = (string) (time() - LEPTOKEN_LIFETIME);  
		$retval = false;
		if (isset($_GET['leptoken'])) {
			$tok = $_GET['leptoken'];
		} elseif (isset($_GET['amp;leptoken'])) {
			$tok = $_GET['amp;leptoken'];
		} elseif (isset($_POST['leptoken'])) {
			$tok = $_POST['leptoken'];
		} elseif (isset($_POST['amp;leptoken'])) {
			$tok = $_POST['amp;leptoken'];
		} else {
			return $retval;
		}
		if (isset($_SESSION['Tokens'])) 
		{
			// delete dated tokens, except the last one
			$token = $_SESSION['Tokens'][0];
			while (($timelimit > substr($token, -10)) and (count($_SESSION['Tokens']) > 1)) {
				array_shift($_SESSION['Tokens']);
				$token = $_SESSION['Tokens'][0];	
			}

			$tokens = $_SESSION['Tokens'];
			foreach ($tokens as $i => $token) {
				$retval = ($tok == $token);
				if ($retval) {
					break;
				}
			}
		}
		
		return $retval;
	}
}

?>