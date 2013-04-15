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
        private $loginerror            = false;

        // user options (column names) added to the session on successful logon
        private $sessioncols = array(
            'user_id', 'group_id', 'groups_id', 'username', 'display_name', 'email', 'home_folder'
        );
        // extended user options; will be extendable later
        // '<option_name>' => '<check validity method>'
        private $useroptions = array(
            'timezone_string'    => 'CAT_Helper_DateTime::checkTZ',
            'date_format'        => 'CAT_Helper_DateTime::checkDateformat',
            'date_format_short'  => 'CAT_Helper_DateTime::checkDateformat',
            'time_format'        => 'CAT_Helper_DateTime::checkTimeformat',
        );
        private static $permissions     = array();
        private static $defaultuser     = array();

        // singleton
        private static $instance        = NULL;

        /**
         * get singleton
         *
         * @access public
         * @return object
         **/
        public static function getInstance()
        {
            if (!self::$instance)
            {
                self::$instance = new self();
                CAT_Registry::register(
                    array(
                        'AUTH_MIN_PASS_LENGTH'  =>   6,	// minimum length of a password
                    	'AUTH_MAX_PASS_LENGTH'  => 128, // maximum length of a password
                    	'AUTH_MIN_LOGIN_LENGTH' =>   3, // minimum length of a login name
                    	'AUTH_MAX_LOGIN_LENGTH' => 128, // maximum length of a login name
                    ),
                    NULL,
                    true
                );
            }
            return self::$instance;
        }   // end function getInstance()

        /**
         * handle user login
         **/
        public function handleLogin()
        {
            global $parser, $database;
            if ( ! is_object($parser) )
            {
                $parser = CAT_Helper_Template::getInstance('Dwoo');
            }
            $parser->setPath(CAT_THEME_PATH . '/templates');
            $parser->setFallbackPath(CAT_THEME_PATH . '/templates');

            $val   = CAT_Helper_Validate::getInstance();
            $lang  = CAT_Helper_I18n::getInstance();

            if ( ! $this->is_authenticated() )
            {

                // --- login attempt ---
                if ( $val->sanitizePost('username_fieldname') )
                {

                    // get input data
                    $user = htmlspecialchars($val->sanitizePost($val->sanitizePost('username_fieldname')),ENT_QUOTES);
                    $pw   = $val->sanitizePost($val->sanitizePost('password_fieldname'));
                    $name = ( preg_match('/[\;\=\&\|\<\> ]/',$user) ? '' : $user );

                    // check common issues
                    // we do not check for too long and don't give too much hints!
                    if ( ! $name )
                        $this->setError($lang->translate('Invalid credentials'));
                    if ( ! $this->loginerror && $user == '' || $pw == '' )
                        $this->setError($lang->translate('Please enter your username and password.'));
                    if ( ! $this->loginerror && strlen($user) < AUTH_MIN_LOGIN_LENGTH )
                        $this->setError($lang->translate('Supplied password to short'));
                    if ( ! $this->loginerror && ! defined('ALLOW_SHORT_PASSWORDS') && strlen($pw) < AUTH_MIN_PASS_LENGTH )
            			$this->setError($lang->translate('The password you entered was too short'));

                    if ( ! $this->loginerror )
                    {
                        $query	= 'SELECT * FROM `'.CAT_TABLE_PREFIX.'users` WHERE `username` = "'.$name.'" AND `password` = "'.md5($pw).'" AND `active` = 1';
                		$result	= $database->query($query);
                		if ( $result->numRows() == 1 )
                        {

                            // get default user preferences
                            $prefs = $this->getDefaultUserOptions();
                            // get basic user data
                            $user  = $result->fetchRow( MYSQL_ASSOC );
                            // add this user's options
                            $prefs = array_merge(
                                $prefs,
                                $this->getUserOptions($user['user_id'])
                            );

                            foreach( $this->sessioncols as $key )
                            {
                                $_SESSION[strtoupper($key)] = $user[$key];
                            }

                            // ----- preferences -----
                            $_SESSION['LANGUAGE']
                                = ( $user['language'] != '' )
                                ? $user['language']
                                : ( isset($prefs['language']) ? $prefs['language'] : 'DE' )
                                ;

                            $_SESSION['TIMEZONE_STRING']
                                = ( isset($prefs['timezone_string']) && $prefs['timezone_string'] != '' )
                                ? $prefs['timezone_string']
                                : DEFAULT_TIMEZONE_STRING
                                ;

                            $_SESSION['DATE_FORMAT']
                                = ( isset($prefs['date_format']) && $prefs['date_format'] != '' )
                                ? $prefs['date_format']
                                : DEFAULT_DATE_FORMAT
                                ;

                            $_SESSION['TIME_FORMAT']
                                = ( isset($prefs['time_format']) && $prefs['time_format'] != '' )
                                ? $prefs['time_format']
                                : DEFAULT_TIME_FORMAT
                                ;

                			date_default_timezone_set($_SESSION['TIMEZONE_STRING']);

                            $_SESSION['SYSTEM_PERMISSIONS']		= 0;
                			$_SESSION['MODULE_PERMISSIONS']		= array();
                			$_SESSION['TEMPLATE_PERMISSIONS']	= array();
                			$_SESSION['GROUP_NAME']				= array();

                            $first_group = true;

                			foreach ( explode(",",$user['groups_id']) as $cur_group_id )
                			{
                				$query	 = "SELECT * FROM `".CAT_TABLE_PREFIX."groups` WHERE group_id = '".$cur_group_id."'";
                				$result	 = $database->query($query);
                				$results = $result->fetchRow( MYSQL_ASSOC );

                				$_SESSION['GROUP_NAME'][$cur_group_id] = $results['name'];

                				// Set system permissions
                				if($results['system_permissions'] != '')
                					$_SESSION['SYSTEM_PERMISSIONS'] = $results['system_permissions'];

                				// Set module permissions
                				if ( $results['module_permissions'] != '' )
                				{
                					if ($first_group)
                					{
                						$_SESSION['MODULE_PERMISSIONS']	= explode(',', $results['module_permissions']);
                					}
                					else
                					{
                						$_SESSION['MODULE_PERMISSIONS']	= array_intersect($_SESSION['MODULE_PERMISSIONS'], explode(',', $results['module_permissions']));
                					}
                				}

                				// Set template permissions
                				if ( $results['template_permissions'] != '' )
                				{
                					if ($first_group)
                					{
                						$_SESSION['TEMPLATE_PERMISSIONS'] = explode(',', $results['template_permissions']);
                					}
                					else
                					{
                						$_SESSION['TEMPLATE_PERMISSIONS'] = array_intersect($_SESSION['TEMPLATE_PERMISSIONS'], explode(',', $results['template_permissions']));
                					}
        }

                				$first_group = false;

                            }   // foreach ( explode(",",$user['groups_id']) as $cur_group_id )

                			// Update the users table with current ip and timestamp
                			$get_ts		= time();
                			$get_ip		= $_SERVER['REMOTE_ADDR'];
                			$query		= "UPDATE `".CAT_TABLE_PREFIX."users` SET login_when = '$get_ts', login_ip = '$get_ip' WHERE user_id = '".$user['user_id']."'";
                			$database->query($query);
                            return CAT_ADMIN_URL.'/start/index.php';
                        }
                        else
                        {
                            $this->setError($lang->translate('Invalid credentials'));
                        }
                    }

                    if ( $val->fromSession('ATTEMPTS') > MAX_ATTEMPTS && AUTO_DISABLE_USERS )
                    {
                        // if we have a user name
                        if ( $name )
                        {
                            $this->disableAccount($name);
                        }
                        return CAT_THEME_URL . '/templates/warning.html';
                    }

                    return false;
                }

                // create random fieldnames for username and password
                $salt               = md5(microtime());
                $username_fieldname	= 'username_'.substr($salt, 0, 7);
                $password_fieldname	= 'password_'.substr($salt, -7);

				$tpl_data = array(
                    'USERNAME_FIELDNAME'    => $username_fieldname,
                    'PASSWORD_FIELDNAME'    => $password_fieldname,
                    'USERNAME'              => $val->sanitizePost($username_fieldname),
                    'ACTION_URL'			=> CAT_ADMIN_URL.'/login/index.php',
                    'LOGIN_URL'				=> CAT_ADMIN_URL.'/login/index.php',
	                'DEFAULT_URL'			=> CAT_ADMIN_URL.'/start/index.php',
                    'WARNING_URL'			=> CAT_THEME_URL . '/templates/warning.html',
                    'REDIRECT_URL'			=> ADMIN_URL . '/start/index.php',
	                'FORGOTTEN_DETAILS_APP'	=> ADMIN_URL . '/login/forgot/index.php',
                    // --- database settings ---
                	'MIN_USERNAME_LEN'		=> AUTH_MIN_LOGIN_LENGTH,
                	'MAX_USERNAME_LEN'		=> AUTH_MAX_LOGIN_LENGTH,
                	'MIN_PASSWORD_LEN'		=> AUTH_MIN_PASS_LENGTH,
                	'MAX_PASSWORD_LEN'		=> AUTH_MAX_PASS_LENGTH,
                    'PAGES_DIRECTORY'       => PAGES_DIRECTORY,
                    'ATTEMPTS'              => $val->fromSession('ATTEMTPS'),
                    'MESSAGE'               => $this->loginerror
                );

				$tpl_data['meta']['LANGUAGE']	= strtolower(LANGUAGE);
				$tpl_data['meta']['CHARSET']	= (defined('DEFAULT_CHARSET')) ? DEFAULT_CHARSET : "utf-8";

                $parser->output('login.lte',$tpl_data);

            }
            else
            {
                header('Location: '.CAT_ADMIN_URL.'/start/index.php' );
            }

        }   // end function handleLogin()

        /**
         * set login error and increase number of login attempts
         *
         * @access private
         * @param  string   $msg - error message
         * @return void
         **/
        private function setError($msg)
        {
            $this->loginerror = $msg;
            if(!isset($_SESSION['ATTEMPTS']))
    			$_SESSION['ATTEMPTS'] = 0;
    		else
    			$_SESSION['ATTEMPTS'] = CAT_Helper_Validate::getInstance()->fromSession('ATTEMPTS') + 1;
        }   // end function setError()

        /**
         * get last login error
         *
         * @access public
         * @return mixed
         **/
        public function loginError()
        {
            return $this->loginerror;
        }   // end function loginError()

        /**
         * disable user account; if $user_id is not an int, it is used as name
         *
         * @access public
         * @param  mixed  $user_id
         * @return void
         **/
        public function disableAccount($user_id)
        {
            global $database;
            $query		= "UPDATE `".CAT_TABLE_PREFIX."users` SET active = 0 WHERE "
                        . ( is_numeric($user_id) ? 'user_id' : 'username' )
                        . " = '".$user_id."'";
            $database->query($query);
        }   // end function disableAccount()

        /**
         *
         **/
        public function checkPermission( $group, $perm, $redirect = false, $for = 'BE' )
        {
            // root is always allowed to do it all
            if ( $this->is_root() ) return true;
            // all authenticated users are allowed to see the dashboard
            if ( $perm == 'start' && $this->is_authenticated() ) return true;
            // fill permissions cache on first call
            if ( ! count(self::$permissions) )
            {
                global $database;
                $res = $database->query('SELECT perm_name, perm_group, perm_bit FROM '.CAT_TABLE_PREFIX."system_permissions WHERE perm_for='$for';");
                if($res->numRows())
                {
                    while( false !== ( $row = $res->fetchRow(MYSQL_ASSOC) ) )
                    {
                        $row['perm_group'] = strtolower($row['perm_group']);
                        if ( ! isset(self::$permissions[$row['perm_group']]) )
                            self::$permissions[$row['perm_group']] = array();
                        self::$permissions[$row['perm_group']][$row['perm_name']] = $row['perm_bit'];
                    }
                }
            }

            $group = strtolower($group);
            $perm  = strtolower($perm);

            // get needed bit
            $bit = self::$permissions[$group][$perm];
            if ( $bit == 0 ) return true;

            // get user perms from session
            $has = CAT_Helper_Validate::getInstance()->fromSession('SYSTEM_PERMISSIONS','numeric');
            if ( (int)$has & (int)$bit )
            {
                return true;
            }
            else
            {
                if ( $redirect )
                {
                    // cleanup session
                    // delete most critical session variables manually
                    $_SESSION['USER_ID'] = null;
                    $_SESSION['GROUP_ID'] = null;
                    $_SESSION['GROUPS_ID'] = null;
                    $_SESSION['USERNAME'] = null;
                    $_SESSION['PAGE_PERMISSIONS'] = null;
                    $_SESSION['SYSTEM_PERMISSIONS'] = null;

                    // overwrite session array
                    $_SESSION = array();

                    // delete session cookie if set
                    if (isset($_COOKIE[session_name()])) {
                        setcookie(session_name(), '', time() - 42000, '/');
                    }

                    // delete the session itself
                    session_destroy();

                    // redirect to admin login
                    die(header('Location: ' . CAT_ADMIN_URL . '/login/index.php'));
                }
                else
                {
                    return false;
                }
            }


        }   // end function checkPermission()

        /**
         * get global settings for all users
         *
         * @access public
         * @return array
         *
         **/
        public function getDefaultUserOptions()
        {
            if ( ! count(self::$defaultuser) )
            {
                global $database;
                $result = $database->query( 'SELECT * FROM '.CAT_TABLE_PREFIX.'users_options WHERE user_id="0";' );
                if($result->numRows())
                {
                    while( false !== ( $row = $result->fetchRow(MYSQL_ASSOC) ) )
                    {
                        self::$defaultuser[$row['option_name']] = $row['option_value'];
                    }
                }
            }
            return self::$defaultuser;
        }   // end function getDefaultUserOptions()

        /**
         * get user's preferences
         *
         * @access public
         * @param  integer $user_id
         * @return array
         *
         **/
        public function getUserOptions($user_id)
        {
            global $database;
            $options = array();
            $result  = $database->query( 'SELECT * FROM '.CAT_TABLE_PREFIX.'users_options WHERE user_id="'.$user_id.'";' );
            if($result->numRows())
            {
                while( false !== ( $row = $result->fetchRow(MYSQL_ASSOC) ) )
                {
                    $options[$row['option_name']] = $row['option_value'];
                }
            }
            return $options;
        }   // end function getUserOptions()

        public function getExtendedOptions()
        {
            return $this->useroptions;
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
            return CAT_Helper_Validate::getInstance()->fromSession('USER_ID','numeric');
        }

        // Get the current users group id (deprecated)
        public function get_group_id()
        {
            return CAT_Helper_Validate::getInstance()->fromSession('GROUP_ID','numeric');
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
            return CAT_Helper_Validate::getInstance()->fromSession('GROUP_NAME','scalar');
        }

        // Get the current users username
        public function get_username()
        {
            return CAT_Helper_Validate::getInstance()->fromSession('USERNAME','scalar');
        }

        // Get the current users display name
        public function get_display_name()
        {
            return CAT_Helper_Validate::getInstance()->fromSession('DISPLAY_NAME','scalar');
        }

        // Get the current users email address
        public function get_email()
        {
            return CAT_Helper_Validate::getInstance()->fromSession('EMAIL');
        }

        // Get the current users home folder
        public function get_home_folder()
        {
            return CAT_Helper_Validate::getInstance()->fromSession('HOME_FOLDER');
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
    			$system_permissions   = explode(',',$val->fromSession('SYSTEM_PERMISSIONS'));
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
         * Check if current user is superuser (the one who installed the CMS)
         *
         * @access public
         * @return boolean
         **/
        public function is_root()
        {
            if ($this->get_user_id() == 1)
                return true;
            else
                return false;
        }   // end function is_root()

        /**
         * Check if the user is already authenticated
         *
         * @access public
         * @return boolean
         **/
        public function is_authenticated()
        {
            $user_id = CAT_Helper_Validate::getInstance()->fromSession('USER_ID','numeric');
            if ($user_id)
                return true;
            else
                return false;
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
         * very simple method to generate a random string, may be used for
         * passwords (but not strong ones)
         *
         * @access public
         * @param  integer  $length (default:10)
         * @return string
         **/
        public function generateRandomString( $length = 10 ) {
            for(
                   $code_length = $length, $newcode = '';
                   strlen($newcode) < $code_length;
                   $newcode .= chr(!rand(0, 2) ? rand(48, 57) : (!rand(0, 1) ? rand(65, 90) : rand(97, 122)))
            );
            return $newcode;
        }   // end function generateRandomString()

		/**
		 * Checks for valid password. Returns boolean. The following checks are done:
		 *
		 * + min length (constant AUTH_MIN_PASS_LENGTH defined in CAT_Users)
		 * + max length (constant AUTH_MAX_PASS_LENGTH defined in CAT_Users)
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