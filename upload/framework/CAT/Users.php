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
        private static $PCRE_PASSWORD
            = "/^\A(?=[\.,;\:&\"\'\?\!\(\)a-zA-Z0-9]*?[A-Z])(?=[\.,;\:&\"\'\?\!\(\)a-zA-Z0-9]*?[a-z])(?=[\.,;\:&\"\'\?\!\(\)a-zA-Z0-9]*?[0-9])\S{6,}\z$/";
		
        private static $validatePasswordError = NULL;
        private static $lastValidatedPassword = NULL;
        private static $loginerror            = false;

        // user options (column names) added to the session on successful logon
        private static $sessioncols = array(
            'user_id', 'group_id', 'groups_id', 'username', 'display_name', 'email', 'home_folder'
        );
        // extended user options; will be extendable later
        // '<option_name>' => '<check validity method>'
        private static $useroptions = array(
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
                        'USERS_PROFILE_ALLOWED' =>  16, // bit to check if user can edit his profile
                    ),
                    NULL,
                    true
                );
            }
            return self::$instance;
        }   // end function getInstance()

        public function __call($method, $args)
        {
            if ( ! isset($this) || ! is_object($this) )
                return false;
            if ( method_exists( $this, $method ) )
                return call_user_func_array(array($this, $method), $args);
        }

        /**
         * handle user login
         **/
        public static function handleLogin($output=true)
        {
            global $parser;
            if ( ! is_object($parser) )
            {
                $parser = CAT_Helper_Template::getInstance('Dwoo');
            }
            $parser->setPath(CAT_THEME_PATH . '/templates');
            $parser->setFallbackPath(CAT_THEME_PATH . '/templates');

            $val   = CAT_Helper_Validate::getInstance();
            $lang  = CAT_Helper_I18n::getInstance();
            $self  = self::getInstance();

            if ( ! self::is_authenticated() )
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
                        self::setError($lang->translate('Invalid credentials'));
                    if ( ! self::$loginerror && $user == '' || $pw == '' )
                        self::setError($lang->translate('Please enter your username and password.'));
                    if ( ! self::$loginerror && strlen($user) < AUTH_MIN_LOGIN_LENGTH )
                        self::setError($lang->translate('The password you entered was too short'));
                    if ( ! self::$loginerror && ! defined('ALLOW_SHORT_PASSWORDS') && strlen($pw) < AUTH_MIN_PASS_LENGTH )
                        self::setError($lang->translate('The password you entered was too short'));

                    if ( ! self::$loginerror )
                    {
                        $query	= 'SELECT * FROM `'.CAT_TABLE_PREFIX.'users` WHERE `username` = "'.$name.'" AND `password` = "'.md5($pw).'" AND `active` = 1';
                        $result    = $self->db()->query($query);
                		if ( $result->numRows() == 1 )
                        {

                            // get default user preferences
                            $prefs = self::getDefaultUserOptions();
                            // get basic user data
                            $user  = $result->fetchRow( MYSQL_ASSOC );
                            // add this user's options
                            $prefs = array_merge(
                                $prefs,
                                self::getUserOptions($user['user_id'])
                            );

                            foreach( self::$sessioncols as $key )
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
                                $result     = $self->db()->query($query);
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
                            $self->db()->query($query);
                            return CAT_ADMIN_URL.'/start/index.php';
                        }
                        else
                        {
                            self::setError($lang->translate('Invalid credentials'));
                        }
                    }

                    if ( $val->fromSession('ATTEMPTS') > MAX_ATTEMPTS && AUTO_DISABLE_USERS )
                    {
                        // if we have a user name
                        if ( $name )
                        {
                            self::disableAccount($name);
                        }
                        return CAT_THEME_URL . '/templates/warning.html';
                    }

                    return false;
                }

                if ( ! $output )
                {
                    return false;
                }

                $username_fieldname = $val->createFieldname('username_');
				$tpl_data = array(
                    'USERNAME_FIELDNAME'    => $username_fieldname,
                    'PASSWORD_FIELDNAME'    => $val->createFieldname('password_'),
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
                    'MESSAGE'               => self::$loginerror
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
            self::$loginerror = $msg;
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
        public static function loginError()
        {
            return self::$loginerror;
        }   // end function loginError()

        /**
         * disable user account; if $user_id is not an int, it is used as name
         *
         * @access public
         * @param  mixed  $user_id
         * @return void
         **/
        public static function disableAccount($user_id)
        {
            $self  = self::getInstance();
            $self->db()->query(sprintf(
                'UPDATE `%susers` SET active = 0 WHERE '
                        . ( is_numeric($user_id) ? 'user_id' : 'username' )
                . " = '%s'",CAT_TABLE_PREFIX,$user_id));
            return $self->db()->isError();
        }   // end function disableAccount()

        /**
         *
         **/
        public static function checkPermission( $group, $perm, $redirect = false, $for = 'BE' )
        {
            // root is always allowed to do it all
            if ( self::is_root() ) return true;
            // all authenticated users are allowed to see the dashboard
            if ( $perm == 'start' && self::is_authenticated() ) return true;
            // fill permissions cache on first call
            if ( ! count(self::$permissions) )
            {
                $self = self::getInstance();
                $res  = $self->db()->query(sprintf(
                    'SELECT perm_name, perm_group, perm_bit FROM `%ssystem_permissions` WHERE perm_for=\'%s\';',
                    CAT_TABLE_PREFIX, $for
                ));
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
         *
         *
         *
         *
         **/
        public static function checkEmailExists($email)
        {
            $self    = self::getInstance();
            $results = $self->db()->query(sprintf(
                'SELECT user_id FROM `%susers` WHERE email = "%s"',
                CAT_TABLE_PREFIX,
                CAT_Helper_Validate::add_slashes( $email )
            ));
            if ( $results->numRows() > 0 )
            {
                return true;
            }
            return false;
        }   // end function checkEmailExists()

        /**
         * checks if given username already exists
         *
         * @access public
         * @param  string  $username
         * @return boolean
         **/
        public static function checkUsernameExists($username)
        {
            $self    = self::getInstance();
            $results = $self->db()->query(sprintf(
                'SELECT user_id FROM `%susers` WHERE username = "%s"',
                CAT_TABLE_PREFIX, $username
            ));
            if ( $results->numRows() > 0 )
            {
                return true;
            }
            return false;
        }   // end function checkUsernameExists()

        /**
         * create a new user
         *
         * @access public
         *
         **/
        public static function createUser($groups_id, $active, $username, $md5_password, $display_name, $email )
        {
            $self  = self::getInstance();
            $query = 'INSERT INTO `%susers` (group_id,groups_id,active,username,password,display_name,email) '
                   . "VALUES ('$groups_id', '$groups_id', '$active', '$username','$md5_password','$display_name','$email');";
            $self->db()->query(sprintf($query,CAT_TABLE_PREFIX));

            if ( $self->db()->is_error() )
            {
            	return $self->db()->get_error();
            }
            return true;
        }   // end function createUser()

        /**
         *
         * @access public
         * @return
         **/
        public static function deleteUser($user_id)
        {
            $self = self::getInstance();
       		$self->db()->query(sprintf("DELETE FROM %susers WHERE `user_id` = %d",CAT_TABLE_PREFIX,$user_id));
            return ( $self->db()->is_error() ? $self->db()->get_error() : true );
        }   // end function deleteUser()
        

        /**
         * get global settings for all users
         *
         * @access public
         * @return array
         *
         **/
        public static function getDefaultUserOptions()
        {
            $self  = self::getInstance();
            if ( ! count(self::$defaultuser) )
            {
                $result = $self->db()->query(sprintf(
                    'SELECT * FROM `%susers_options` WHERE user_id="0";',
                    CAT_TABLE_PREFIX
                ));
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
        public static function getUserOptions($user_id)
        {
            $options = array();
            $self    = self::getInstance();
            $result  = $self->db()->query(sprintf(
                'SELECT * FROM `%susers_options` WHERE user_id="%d";',
                CAT_TABLE_PREFIX, $user_id
            ));
            if($result->numRows())
            {
                while( false !== ( $row = $result->fetchRow(MYSQL_ASSOC) ) )
                {
                    $options[$row['option_name']] = $row['option_value'];
                }
            }
            return $options;
        }   // end function getUserOptions()

        public static function getExtendedOptions()
        {
            return self::$useroptions;
        }

        /**
         * save user's preferences
         *
         * @access public
         * @param  integer $user_id
         * @param  array   $options
         * @return array
         *
         **/
        public static function setUserOptions($user_id,$options)
        {
            $fields = $errors = array();
            // get extension fields
            $ext  = self::getExtendedOptions();
            $self = self::getInstance();
            // get default fields
            $desc = $self->db()->query(sprintf('DESCRIBE %susers',CAT_TABLE_PREFIX));
            while ( false !== ( $row = $desc->fetchRow(MYSQL_ASSOC) ) )
            {
                $fields[] = $row['Field'];
            }
            // save default options
            $c = 0;
            $q = "UPDATE `".CAT_TABLE_PREFIX."users` SET ";
            foreach($fields as $key)
            {
                if ( isset($options[$key]) && $options[$key] !== '' )
                {
                    $q .= "`".$key."`='".mysql_real_escape_string($options[$key])."', ";
                    $c++;
                }
            }
            $q = substr($q, 0, -2) . " WHERE `user_id`='".$user_id."'";
            if($c)
            {
                $self->db()->query($q);
                   if ($self->db()->is_error())
                {
                    $errors[] = $self->db()->get_error();
                }
            }
            // save extended options
            foreach( array_keys($ext) as $key )
            {
                if ( isset($options[$key]) && $options[$key] !== '' )
                {
                    $q  = "UPDATE `".CAT_TABLE_PREFIX."users_options` SET "
                        . "`option_value`='".mysql_real_escape_string($options[$key])."' "
                        . " WHERE `option_name`='$key' AND `user_id`='".$user_id."'";
                    $self->db()->query($q);
                       if ($self->db()->is_error())
                    {
                        $errors[] = $self->db()->get_error();
                    }
                }
            }
            return $errors;
        }   // end function setUserOptions()



        /* ****************
         * check if current user is member of at least one of given groups
         * ADMIN (uid=1) always is treated like a member of any groups
         *
         * @access public
         * @param  mixed  $groups_list: an array or a comma seperated list of group-ids
         * @return bool   true if current user is member of one of this groups, otherwise false
         */
        public static function ami_group_member($groups_list = '')
        {
            if (self::get_user_id() == 1)
            {
                return true;
            }
            return self::is_group_match($groups_list, self::get_groups_id());
        }

        // Get the current users id
        public static function get_user_id()
        {
            return CAT_Helper_Validate::getInstance()->fromSession('USER_ID','numeric');
        }

        // Get the current users group id (deprecated)
        public static function get_group_id()
        {
            return CAT_Helper_Validate::getInstance()->fromSession('GROUP_ID','numeric');
        }

        // Get the current users group ids
        public static function get_groups_id()
        {
            return explode(",", isset($_SESSION['GROUPS_ID']) ? $_SESSION['GROUPS_ID'] : '');
        }

        // Get the current users group name
        public static function get_group_name()
        {
            return implode(",", $_SESSION['GROUP_NAME']);
        }

        // Get the current users group name
        public static function get_groups_name()
        {
            return CAT_Helper_Validate::getInstance()->fromSession('GROUP_NAME','scalar');
        }

        // Get the current users username
        public static function get_username()
        {
            return CAT_Helper_Validate::getInstance()->fromSession('USERNAME','scalar');
        }

        // Get the current users display name
        public static function get_display_name()
        {
            return CAT_Helper_Validate::getInstance()->fromSession('DISPLAY_NAME','scalar');
        }

        // Get the current users email address
        public static function get_email()
        {
            return CAT_Helper_Validate::getInstance()->fromSession('EMAIL');
        }

        // Get the current users home folder
        public static function get_home_folder()
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
        public static function get_groups( $viewing_groups = array() , $admin_groups = array(), $insert_admin = true )
    	{

    		$groups				= false;
    		$viewing_groups		= is_array( $viewing_groups )	? $viewing_groups	: array( $viewing_groups );
    		$admin_groups		= is_array( $admin_groups )		? $admin_groups		: array( $viewing_groups );
            $self           = self::getInstance();

            // ================
    		// ! Getting Groups
    		// ================
            $get_groups = $self->db()->query(sprintf(
                'SELECT * FROM `%sgroups`',
                CAT_TABLE_PREFIX
            ));

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
                    'DISABLED'                => in_array( $group["group_id"], self::get_groups_id() )            ? true : false,
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
                        'DISABLED'                => in_array( $group["group_id"], self::get_groups_id() )            ? true : false,
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
        public static function get_permission($name, $type = 'system')
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
        public static function get_user_details($user_id)
        {
            $self     = self::getInstance();
            $get_user = $self->db()->query(sprintf(
                'SELECT username, display_name FROM `%susers` WHERE user_id=%d',
                CAT_TABLE_PREFIX, $user_id
            ));
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
        public static function is_root()
        {
            if (self::get_user_id() == 1)
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
        public static function is_authenticated()
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
        public static function is_group_match($groups_list1 = '', $groups_list2 = '')
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
        public static function generateRandomString( $length = 10 ) {
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
        public static function validatePassword( $password, $allow_quotes = true, $strict = false )
	    {
	        // check length
	        if ( strlen($password) < AUTH_MIN_PASS_LENGTH )
			{
                self::$validatePasswordError = self::lang()->translate('The password is too short.');
	            return false;
	        }
	        elseif ( strlen($password) > AUTH_MAX_PASS_LENGTH )
			{
                self::$validatePasswordError = self::lang()->translate('The password is too long.');
				return false;
	        }
	        // any string that doesn't have control characters (ASCII 0 - 31) - spaces allowed
	        if ( ! preg_match( '/^[^\x-\x1F]+$/D', $password, $match ) )
	        {
                self::$validatePasswordError = self::lang()->translate('Invalid password!');
				return false;
	        }
	        else
	        {
                self::$lastValidatedPassword = $match[0];
	        }
	        if ( ! $allow_quotes )
	        {
	            // don't allow quotes in the PW!
				if ( preg_match( '/(\%27)|(\')|(%2D%2D)|(\-\-)/i', $password ) )
				{
                    self::$validatePasswordError = self::lang()->translate('Invalid password!');
					return false;
				}
			}
	        // check complexity
	        if ( $strict )
	        {
                if ( ! preg_match( self::$PCRE_PASSWORD, $password ) )
	            {
                    self::$validatePasswordError = self::lang()->translate('The required password complexity is not met');
					return false;
	            }
	        }
	        // all checks done
	        return true;
	    }   // end function validatePassword()
	    
        /**
         * check for valid username:
         *
         * + must begin with a char (a-z)
         * + ...followed by at least 2 chars (a-z), numbers (0-9), _ or -
         * + must match min and max username length
         *
         * If USERS_ALLOW_MAILADDRESS is set to true, the username is checked
         * for valid mail address. If it is valid, there will be no check for
         * min. and max. length to avoid problems here.
         *
         * @access public
         * @param  string  $username
         * @return booelan
         *
         **/
        public static function validateUsername($username)
        {
            if ( CAT_Registry::exists('USERS_ALLOW_MAILADDRESS') )
                $allow_mailaddress = CAT_Registry::get('USERS_ALLOW_MAILADDRESS');
            else
                $allow_mailaddress = false;
            if ( !preg_match( '/^[a-z]{1}[a-z0-9_-]{2,}$/i', $username ) )
            {
                if ( $allow_mailaddress && CAT_Helper_Validate::getInstance()->sanitize_email($username) )
                {
                    // in case of mail address, we do not check for min and max length!
                    return true;
                }
                return false;
            }
            if ( strlen($username ) < AUTH_MIN_LOGIN_LENGTH )
                return false;
            if ( strlen($username) > AUTH_MAX_LOGIN_LENGTH )
                return false;
            return true;
        }
        
        public static function getPasswordError()
	    {
            return self::$validatePasswordError;
	    }   // end function getPasswordError()
	    
        public static function getLastValidatedPassword()
	    {
            return self::$lastValidatedPassword;
        }   // end function getLastValidatedPassword()
	}

}

?>