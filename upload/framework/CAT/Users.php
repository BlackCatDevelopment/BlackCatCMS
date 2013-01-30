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
 *   @author          Black Cat Development
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if ( ! class_exists( 'CAT_Object', false ) ) {
    @include dirname(__FILE__).'/Object.php';
}

if ( ! class_exists( 'CAT_Users', false ) )
{
	class CAT_Users extends CAT_Object
	{
	
	    // Checking password complexity
		// This regular expression will tests if the input consists of 6 or more
		// letters, digits, underscores and hyphens.
		// The input must contain at least one upper case letter, one lower case
		// letter and one digit.
		private $PCRE_PASSWORD = "/^\A(?=[\.,;\:&\"\'\?\!\(\)a-zA-Z0-9]*?[A-Z])(?=[\.,;\:&\"\'\?\!\(\)a-zA-Z0-9]*?[a-z])(?=[\.,;\:&\"\'\?\!\(\)a-zA-Z0-9]*?[0-9])\S{6,}\z$/";
		
		private $validatePasswordError = NULL;
		private $lastValidatedPassword = NULL;

		/**
		 * Checks for valid password. Returns boolean. The following checks are done:
		 *
		 * + min length (constant AUTH_MIN_PASS_LENGTH defined in sys.constants.php)
		 * + max length (constant AUTH_MAX_PASS_LENGTH defined in sys.constants.php)
		 * + is a string (spaces allowed), no control characters
		 * + if $allow_quotes = false: no quotes
		 * + if $strict = true: consists of 6 or more letters, digits, underscores
		 *                and hyphens; must contain at least one upper case letter,
		 *                one lower case letter and one digit
		 *
		 * Use method getPasswordError() to get an error message on return value false
		 *
		 * @access public
		 * @param  string  $password
		 * @param  boolean $allow_quotes (default: true)
		 * @param  boolean $strict       (default: false)
		 * @return boolean
		 *
		 */
	    public function validatePassword( $password, $allow_quotes = true, $strict = false )
	    {
			// make sure sys.constants.php is loaded
			if ( ! defined( 'AUTH_MIN_PASS_LENGTH' ) )
			{
			    include dirname(__FILE__).'/../sys.constants.php';
			}
	        // check length
	        if ( strlen($password) < AUTH_MIN_PASS_LENGTH )
			{
			    $this->validatePasswordError = $this->lang()->translate('The password is too short.');
	            return false;
	        }
	        elseif ( strlen($password) > AUTH_MAX_PASS_LENGTH )
			{
			    $this->validatePasswordError = $this->lang()->translate('The password is too long.');
				return false;
	        }
	        // any string that doesn't have control characters (ASCII 0 - 31) - spaces allowed
	        if ( ! preg_match( '/^[^\x-\x1F]+$/D', $password, $match ) )
	        {
	            $this->validatePasswordError = $this->lang()->translate('Invalid password!');
				return false;
	        }
	        else
	        {
	            $this->lastValidatedPassword = $match[0];
	        }
	        if ( ! $allow_quotes )
	        {
	            // don't allow quotes in the PW!
				if ( preg_match( '/(\%27)|(\')|(%2D%2D)|(\-\-)/i', $password ) )
				{
					$this->validatePasswordError = $this->lang()->translate('Invalid password!');
					return false;
				}
			}
	        // check complexity
	        if ( $strict )
	        {
	            if ( ! preg_match( $this->PCRE_PASSWORD, $password ) )
	            {
	                $this->validatePasswordError = $this->lang()->translate('The required password complexity is not met');
					return false;
	            }
	        }
	        // all checks done
	        return true;
	    }   // end function validatePassword()
	    
	    public function getPasswordError()
	    {
	        return $this->validatePasswordError;
	    }   // end function getPasswordError()
	    
	    public function getLastValidatedPassword()
	    {
	        return $this->lastValidatedPassword;
	    }
	}

}

?>