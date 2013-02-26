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

        // singleton
        private static $instance        = NULL;

        public static function getInstance()
        {
            if (!self::$instance)
            {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /* ****************
         * check if current user is member of at least one of given groups
         * ADMIN (uid=1) always is treated like a member of any groups
         *
         * @access public
         * @param  mixed  $groups_list: an array or a comma seperated list of group-ids
         * @return bool   true if current user is member of one of this groups, otherwise false
         */
        public function ami_group_member($groups_list = '')
        {
            if ($this->get_user_id() == 1)
            {
                return true;
            }
            return $this->is_group_match($groups_list, $this->get_groups_id());
        }

        // Get the current users id
        public function get_user_id()
        {
            return $_SESSION['USER_ID'];
        }

        // Get the current users group id (deprecated)
        public function get_group_id()
        {
            return $_SESSION['GROUP_ID'];
        }

        // Get the current users group ids
        public function get_groups_id()
        {
            return explode(",", isset($_SESSION['GROUPS_ID']) ? $_SESSION['GROUPS_ID'] : '');
        }

        // Get the current users group name
        public function get_group_name()
        {
            return implode(",", $_SESSION['GROUP_NAME']);
        }

        // Get the current users group name
        public function get_groups_name()
        {
            return $_SESSION['GROUP_NAME'];
        }

        // Get the current users username
        public function get_username()
        {
            return $_SESSION['USERNAME'];
        }

        // Get the current users display name
        public function get_display_name()
        {
            return $_SESSION['DISPLAY_NAME'];
        }

        // Get the current users email address
        public function get_email()
        {
            return $_SESSION['EMAIL'];
        }

        // Get the current users home folder
        public function get_home_folder()
        {
            return $_SESSION['HOME_FOLDER'];
        }

    	/**
    	 * get_groups function.
    	 *
    	 * Function to get all groups as viewers and as admins
    	 *
    	 * @access public
    	 * @param  array  $viewing_groups (default: array())
    	 * @param  array  $admin_groups   (default: array())
    	 * @param  bool   $insert_admin   (default: true)
    	 * @return void
    	 */
    	public function get_groups( $viewing_groups = array() , $admin_groups = array(), $insert_admin = true )
    	{
            global $database;

    		$groups				= false;
    		$viewing_groups		= is_array( $viewing_groups )	? $viewing_groups	: array( $viewing_groups );
    		$admin_groups		= is_array( $admin_groups )		? $admin_groups		: array( $viewing_groups );

            // ================
    		// ! Getting Groups
    		// ================
    		$get_groups = $database->query("SELECT * FROM " . CAT_TABLE_PREFIX . "groups");

    		// ==============================================
    		// ! Insert admin group and current group first
    		// ==============================================
    		$admin_group_name	= $get_groups->fetchRow( MYSQL_ASSOC );

    		if ( $insert_admin )
    		{
    			$groups['viewers'][0] = array(
    				'VALUE'		=> 1,
    				'NAME'		=> $admin_group_name['name'],
    				'CHECKED'	=> true,
    				'DISABLED'	=> true
    			);
    			$groups['admins'][0] = array(
    				'VALUE'		=> 1,
    				'NAME'		=> $admin_group_name['name'],
    				'CHECKED'	=> true,
    				'DISABLED'	=> true
    			);
    		}

    		$counter	= 1;

    		while ( $group = $get_groups->fetchRow( MYSQL_ASSOC ) )
    		{
    			$system_permissions			= explode( ',', $group['system_permissions']);
    			array_unshift( $system_permissions, 'placeholder' );
    			$module_permissions			= explode( ',', $group['module_permissions']);
    			array_unshift( $module_permissions, 'placeholder' );
    			$template_permissions		= explode( ',', $group['template_permissions']);
    			array_unshift( $template_permissions, 'placeholder' );

    			$groups['viewers'][$counter] =	array(
    				'VALUE'					=> $group['group_id'],
    				'NAME'					=> $group['name'],
    				'CHECKED'				=> is_numeric( array_search($group['group_id'], $viewing_groups) )	? true : false,
    				'DISABLED'				=> in_array( $group["group_id"], $this->get_groups_id() )			? true : false,
    				'system_permissions'	=> array_flip( $system_permissions ),
    				'module_permissions'	=> array_flip( $module_permissions ),
    				'template_permissions'	=> array_flip( $template_permissions )
    			);

    			// ===============================================
    			// ! Check if the group is allowed to edit pages
    			// ===============================================
    			$system_permissions = explode(',', $group['system_permissions']);
    			if ( is_numeric( array_search('pages_modify', $system_permissions) ) )
    			{
    				$groups['admins'][$counter]		=	array(
    					'VALUE'					=> $group['group_id'],
    					'NAME'					=> $group['name'],
    					'CHECKED'				=> is_numeric( array_search($group['group_id'], $admin_groups) )	? true : false,
    					'DISABLED'				=> in_array( $group["group_id"], $this->get_groups_id() )			? true : false,
    					'system_permissions'	=> array_flip( explode(',',$group['system_permissions']) ),
    					'module_permissions'	=> array_flip( explode(',',$group['module_permissions']) ),
    					'template_permissions'	=> array_flip( explode(',',$group['template_permissions']) )
    				);
    			}
    			$counter++;
    		}
    		return $groups;
    	}   // end function get_groups()

    	/**
    	 * Return a system permission
    	 *
    	 * @access public
    	 * @param  string  $name
    	 * @param  string  $type
    	 * @return boolean
    	 **/
    	public function get_permission($name, $type = 'system')
        {
    		// Append to permission type
    		$type .= '_permissions';
    		// Check if we have a section to check for
    		if($name == 'start')
            {
    			return true;
    		}
            else
            {
                $val = CAT_Helper_Validate::getInstance();
    			// Set system permissions var
    			$system_permissions   = $val->fromSession('SYSTEM_PERMISSIONS');
    			// Set module permissions var
    			$module_permissions   = $val->fromSession('MODULE_PERMISSIONS');
    			// Set template permissions var
    			$template_permissions = $val->fromSession('TEMPLATE_PERMISSIONS');
    			// Return true if system perm = 1
    			if (isset($$type) && is_array($$type) && is_numeric(array_search($name, $$type)))
                {
    				if($type == 'system_permissions') return true;
                    else       					      return false;
    			}
                else
                {
    				if($type == 'system_permissions') return false;
    				else                              return true;
    			}
    		}
    	}   // end function get_permission()

        /**
         * get user details
         *
         * @access public
         * @param  integer $user_id
         * @return array
         **/
    	public function get_user_details($user_id)
        {
            global $database;
    		$query_user = "SELECT username,display_name FROM ".CAT_TABLE_PREFIX."users WHERE user_id = '$user_id'";
    		$get_user   = $database->query($query_user);
    		if($get_user->numRows() != 0)
            {
    			$user = $get_user->fetchRow(MYSQL_ASSOC);
    		}
            else
            {
    			$user['display_name'] = 'Unknown';
    			$user['username']     = 'unknown';
    		}
    		return $user;
    	}   // end function get_user_details()

        /**
         * Check if the user is already authenticated
         *
         * @access public
         * @return boolean
         **/
        public function is_authenticated()
        {
            if (isset($_SESSION['USER_ID']) && $_SESSION['USER_ID'] != "" && is_numeric($_SESSION['USER_ID']))
            {
                return true;
            }
            else
            {
                return false;
            }
        }   // end function is_authenticated()

        /**
         * check if one or more group_ids are in both group_lists
         *
         * @access public
         * @param  mixed   $groups_list1: an array or a coma seperated list of group-ids
         * @param  mixed   $groups_list2: an array or a coma seperated list of group-ids
         * @return boolean true there is a match, otherwise false
         */
        public function is_group_match($groups_list1 = '', $groups_list2 = '')
        {
            if ($groups_list1 == '')
            {
                return false;
            }
            if ($groups_list2 == '')
            {
                return false;
            }
            if (!is_array($groups_list1))
            {
                $groups_list1 = explode(',', $groups_list1);
            }
            if (!is_array($groups_list2))
            {
                $groups_list2 = explode(',', $groups_list2);
            }

            return(sizeof(array_intersect($groups_list1, $groups_list2)) != 0);
        }   // end function is_group_match()

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