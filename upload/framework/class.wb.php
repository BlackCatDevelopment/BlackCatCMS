<?php
/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 *
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          Website Baker Project, LEPTON Project
 * @copyright       2004-2010, Website Baker Project
 * @copyright       2013, LEPTON v2.0 Black Cat Edition Development
 * @link            http://www.lepton2.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 *
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('LEPTON_PATH')) {
	include(LEPTON_PATH.'/framework/class.secure.php');
} else {
	$oneback = "../";
	$root = $oneback;
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= $oneback;
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) {
		include($root.'/framework/class.secure.php');
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}
// end include class.secure.php

include_once(LEPTON_PATH . '/framework/class.securecms.php');

// Include PHPLIB template class
require_once(LEPTON_PATH . "/include/phplib/template.inc");

require_once(LEPTON_PATH . '/framework/class.database.php');

// Include new wbmailer class (subclass of PHPmailer)
require_once(LEPTON_PATH . "/framework/class.wbmailer.php");

// new internationalization helper class
include LEPTON_PATH.'/framework/LEPTON/Helper/I18n.php';

// new pages class
include LEPTON_PATH.'/framework/LEPTON/Pages.php';

class wb extends SecureCMS
{
    public  $password_chars      = 'a-zA-Z0-9\_\-\!\#\*\+';
    private $lep_active_sections = NULL;
    private $_handles            = NULL;
    public  $lang                = NULL;
    public  static $pg           = NULL;
    private static $depre_func   = array(
        'bind_jquery' => '<a href="https://github.com/webbird/LEPTON_2_BlackCat/wiki/get_page_headers%28%29">get_page_headers()</a>',
        'register_backend_modfiles' => '<a href="https://github.com/webbird/LEPTON_2_BlackCat/wiki/get_page_headers%28%29">get_page_headers("backend", true, "$section_name")</a>',
        'register_backend_modfiles_body' => '<a href="https://github.com/webbird/LEPTON_2_BlackCat/wiki/get_page_footers()">get_page_footers("backend")</a>',
        'register_frontend_modfiles' => '<a href="https://github.com/webbird/LEPTON_2_BlackCat/wiki/get_page_headers%28%29">get_page_headers()</a>',
        'register_frontend_modfiles_body' => '<a href="https://github.com/webbird/LEPTON_2_BlackCat/wiki/get_page_footers()">get_page_footers()</a>',
        'page_menu()' => 'show_menu2()',
        'show_menu()' => 'show_menu2()',
        'show_breadcrumbs()' => 'show_menu2()',
    );

    // General initialization public function
    // performed when frontend or backend is loaded.

    public function __construct()
    {
        global $MENU,$TEXT,$HEADING,$MESSAGE,$OVERVIEW;
		// create accessor/to language helper
  		$this->lang = $this->get_helper('I18n');
  		// load globals from old language files
		foreach( array( 'MENU', 'TEXT', 'HEADING', 'MESSAGE', 'OVERVIEW' ) as $var )
		{
		    $this->lang->addFile( LANGUAGE.'.php', NULL, $var );
		}
        self::$pg = LEPTON_Pages::getInstance();
        set_error_handler( array('wb','lepton_error_handler') );
    }   // end constructor

    public function __call($name, $arguments)
    {
        if (array_key_exists($name,self::$depre_func))
        {
            trigger_error('Method ## '.$name.'() ## is deprecated, use ## '.self::$depre_func[$name].' ## instead!',E_USER_ERROR);
        }
        else
        {
            trigger_error('Unknown method '.$name, E_USER_ERROR);
        }
    }   // end function __call()

    public static function lepton_error_handler($errno,$errstr,$errfile=NULL,$errline=NULL,array $errcontext)
    {
        if (!(error_reporting() & $errno))
        {
            return;
        }
        switch ($errno)
        {
            case E_USER_ERROR:
                echo "<b>LEPTON ERROR</b> [$errno] $errstr<br />\n";
                echo "  Fatal error on line $errline in file $errfile";
                echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
                echo "Aborting...<br />\n";
                exit(1);
                break;

            case E_USER_WARNING:
                echo "<b>LEPTON WARNING</b> [$errno] $errstr<br />\n";
                break;

            case E_USER_NOTICE:
                echo "<b>LEPTON NOTICE</b> [$errno] $errstr<br />\n";
                break;

            default:
                echo "Unknown error type: [$errno] $errstr<br />\n";
                break;
    }
        return true;
    }   // end error handler

    public function section_is_active($section_id)
    {
        global $database;
        $now = time();
        $sql = 'SELECT COUNT(*) FROM `' . TABLE_PREFIX . 'sections` ';
        $sql .= 'WHERE (' . $now . ' BETWEEN `publ_start` AND `publ_end`) OR ';
        $sql .= '(' . $now . ' > `publ_start` AND `publ_end`=0) ';
        $sql .= 'AND `section_id`=' . $section_id;
        return($database->get_one($sql) != false);
    }

    // Check whether we should show a page or not (for front-end)
    public function show_page($page)
    {
        if (!is_array($page))
        {
            $sql = 'SELECT `page_id`, `visibility`, `viewing_groups`, `viewing_users` ';
            $sql .= 'FROM `' . TABLE_PREFIX . 'pages` WHERE `page_id`=' . (int)$page;
            if (($res_pages = $database->query($sql)) != null)
            {
                if (!($page = $res_pages->fetchRow()))
                {
                    return false;
                }
            }
        }
        return($this->page_is_visible($page) && $this->page_is_active($page));
    }

    // Check if the user is already authenticated or not
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
    }

    // Modified addslashes public function which takes into account magic_quotes
    public function add_slashes($input)
    {
        if (get_magic_quotes_gpc() || (!is_string($input)))
        {
            return $input;
        }
        $output = addslashes($input);
        return $output;
    }

    // Ditto for stripslashes
    // Attn: this is _not_ the counterpart to $this->add_slashes() !
    // Use stripslashes() to undo a preliminarily done $this->add_slashes()
    // The purpose of $this->strip_slashes() is to undo the effects of magic_quotes_gpc==On
    public function strip_slashes($input)
    {
        if (!get_magic_quotes_gpc() || (!is_string($input)))
        {
            return $input;
        }
        $output = stripslashes($input);
        return $output;
    }

    // Escape backslashes for use with mySQL LIKE strings
    public function escape_backslashes($input)
    {
        return str_replace("\\", "\\\\", $input);
    }

    // Get POST data
    public function get_post($field)
    {
        return isset($_POST[$field]) ? $_POST[$field] : null;
    }

    // Get POST data and escape it
    public function get_post_escaped($field)
    {
        $result = $this->get_post($field);
        return(is_null($result)) ? null : $this->add_slashes($result);
    }

    // Get GET data
    public function get_get($field)
    {
        return isset($_GET[$field]) ? $_GET[$field] : null;
    }

    // Get SESSION data
    public function get_session($field)
    {
        return isset($_SESSION[$field]) ? $_SESSION[$field] : null;
    }

    // Get SERVER data
    public function get_server($field)
    {
        return isset($_SERVER[$field]) ? $_SERVER[$field] : null;
    }

    // Get the current users id
    public function get_user_id()
    {
        return $this->get_session('USER_ID');
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

    // Get the current users timezone
    public function get_timezone_string()
    {
        return isset($_SESSION['TIMEZONE_STRING']) ? $_SESSION['TIMEZONE_STRING'] : DEFAULT_TIMEZONESTRING;
    }   // end function get_timezone_string()

    /**
     *
     *
     *
     *
     **/
    public function get_controller($name)
    {
		// name must not contain LEPTON_...
	    $name      = preg_replace( '~^lepton_~i', '', $name );
        $args      = func_get_args();
        // remove first element (it's the name)
        array_shift($args);
	    return $this->int_get_handle('',$name,(count($args)?$args:NULL));
    }   // end function get_controller()
    
    /**
     * get accessor to helper class
     * @access public
     * @param  string $name - name of the class
     **/
	public function get_helper($name)
	{
		// name must not contain LEPTON_Helper_...
	    $name      = preg_replace( '~^lepton_helper_~i', '', $name );
        return $this->int_get_handle('Helper',$name,$args=func_get_args());
	}   // end function get_helper()

    /* ****************
     * check if one or more group_ids are in both group_lists
     *
     * @access public
     * @param mixed $groups_list1: an array or a coma seperated list of group-ids
     * @param mixed $groups_list2: an array or a coma seperated list of group-ids
     * @return bool: true there is a match, otherwise false
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
    }

    /* ****************
     * check if current user is member of at least one of given groups
     * ADMIN (uid=1) always is treated like a member of any groups
     *
     * @access public
     * @param mixed $groups_list: an array or a coma seperated list of group-ids
     * @return bool: true if current user is member of one of this groups, otherwise false
     */
    public function ami_group_member($groups_list = '')
    {
        if ($this->get_user_id() == 1)
        {
            return true;
        }
        return $this->is_group_match($groups_list, $this->get_groups_id());
    }

    /* ****************
     * set one or more bit in a integer value
     *
     * @access public
     * @param int $value: reference to the integer, containing the value
     * @param int $bits2set: the bitmask witch shall be added to value
     * @return void
     */
    public function bit_set(&$value, $bits2set)
    {
        $value |= $bits2set;
    }

    /* ****************
     * reset one or more bit from a integer value
     *
     * @access public
     * @param int $value: reference to the integer, containing the value
     * @param int $bits2reset: the bitmask witch shall be removed from value
     * @return void
     */
    public function bit_reset(&$value, $bits2reset)
    {
        $value &= ~$bits2reset;
    }

    /* ****************
     * check if one or more bit in a integer value are set
     *
     * @access public
     * @param int $value: reference to the integer, containing the value
     * @param int $bits2set: the bitmask witch shall be added to value
     * @return void
     */
    public function bit_isset($value, $bits2test)
    {
        return(($value & $bits2test) == $bits2test);
    }

    /**
     *  Validate supplied email address
     *
     */
    public function validate_email($email)
    {
        if (preg_match('/^([0-9a-zA-Z]+[-._+&])*[0-9a-zA-Z-_]+@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6}$/', $email))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     *  Print a success message which then automatically redirects the user to another page
     *
     *  @param  mixed  A string within the message, or an array with a couple of messages.
     *  @param  string  A redirect url. Default is "index.php".
     *  @param  bool  An optional flag to 'print' the footer. Default is true.
     *
     */
	public function print_success($message, $redirect = 'index.php', $auto_footer = true)
	{
		global $TEXT;

		if (true === is_array($message)){
			$message = implode("<br />", $message);
		}

		// ======================================================================================= 
		// ! Try to include the info.php  of the template to seperate old and new TemplateEngine   
		// ======================================================================================= 
		if ( file_exists(THEME_PATH.'/info.php') )
		{
			include( THEME_PATH . '/info.php' );
			// ================================================================= 
			// ! Current controller to check, if it is a new template for Dwoo   
			// ================================================================= 
			if ( isset($template_engine) && $template_engine == 'dwoo' )
			{
				global $parser;

				// =================================== 
				// ! initialize template search path   
				// =================================== 
				$parser->setPath(THEME_PATH . '/templates');
				$parser->setFallbackPath(THEME_PATH . '/templates');

				$data_dwoo['MESSAGE']			= $this->lang->translate($message);
				$data_dwoo['REDIRECT']			= $redirect;
				$data_dwoo['REDIRECT_TIMER']	= REDIRECT_TIMER;

				// ==================== 
				// ! Parse the header 	
				// ==================== 
				$parser->output('success.lte', $data_dwoo);
			}
			/**
			 * Marked as deprecated
			 * This is only for the old TE and will be removed in future versions
			*/
			else
			{
				// add template variables
				$tpl = new Template(THEME_PATH . '/templates');
				$tpl->set_file('page', 'success.htt');
				$tpl->set_block('page', 'main_block', 'main');
				$tpl->set_var('NEXT', $TEXT['NEXT']);
				$tpl->set_var('BACK', $TEXT['BACK']);
				$tpl->set_var('MESSAGE', $this->lang->translate($message) );
				$tpl->set_var('THEME_URL', THEME_URL);

				$tpl->set_block('main_block', 'show_redirect_block', 'show_redirect');
				$tpl->set_var('REDIRECT', $redirect);

				if (REDIRECT_TIMER == -1)
				{
					$tpl->set_block('show_redirect', '');
				}
				else
				{
					$tpl->set_var('REDIRECT_TIMER', REDIRECT_TIMER);
					$tpl->parse('show_redirect', 'show_redirect_block', true);
				}
				$tpl->parse('main', 'main_block', false);
				$tpl->pparse('output', 'page');
			}
		}
		// If the script couldn't include the info.php, print an error message
		else
		{
			echo 'info.php is missing in theme directory. Please check your backend theme if there is a info.php.';
			exit();
		}
		if ($auto_footer == true)
		{
			if (method_exists($this, "print_footer"))
			{
				$this->print_footer();
			}
		}
		exit();
	}

    /**
     *  Print an error message
     *
     *  @param  mixed  A string or an array within the error messages.
     *  @param  string  A redirect url. Default is "index.php".
     *  @param  bool  An optional boolean to 'print' the footer. Default is true;
     *
     */
	public function print_error($message, $link = 'index.php', $auto_footer = true)
	{
		global $TEXT;

		if (true === is_array($message)){
			$message = implode("<br />", $message);
		}

		// ======================================================================================= 
		// ! Try to include the info.php  of the template to seperate old and new TemplateEngine   
		// ======================================================================================= 
		if ( file_exists(THEME_PATH.'/info.php') )
		{
			include( THEME_PATH . '/info.php' );
			// ================================================================= 
			// ! Current controller to check, if it is a new template for Dwoo   
			// ================================================================= 
			if ( isset($template_engine) && $template_engine == 'dwoo' )
			{
				global $parser;

				// =================================== 
				// ! initialize template search path   
				// =================================== 
				$parser->setPath(THEME_PATH . '/templates');
				$parser->setFallbackPath(THEME_PATH . '/templates');

				$data_dwoo['MESSAGE']		= $this->lang->translate($message);
				$data_dwoo['LINK']			= $link;

				// ==================== 
				// ! Parse the header 	
				// ==================== 
				$parser->output('error.lte', $data_dwoo);
			}
			/**
			 * Marked as deprecated
			 * This is only for the old TE and will be removed in future versions
			*/
			else
			{

				$success_template = new Template(THEME_PATH . '/templates');
				$success_template->set_file('page', 'error.htt');
				$success_template->set_block('page', 'main_block', 'main');
				$success_template->set_var('MESSAGE', $this->lang->translate($message) );
				$success_template->set_var('LINK', $link);
				$success_template->set_var('BACK', $TEXT['BACK']);
				$success_template->set_var('THEME_URL', THEME_URL);
				$success_template->parse('main', 'main_block', false);
				$success_template->pparse('output', 'page');
			}
		}
		// If the script couldn't include the info.php, print an error message
		else
		{
			echo 'info.php is missing in theme directory. Please check your backend theme if there is a info.php.';
			exit();
		}
		if ($auto_footer == true)
		{
			if (method_exists($this, "print_footer"))
			{
				$this->print_footer();
			}
		}
		exit();
	}

    // Validate send email
    public function mail($fromaddress, $toaddress, $subject, $message, $fromname = '')
    {
        /*
         INTEGRATED OPEN SOURCE PHPMAILER CLASS FOR SMTP SUPPORT AND MORE
         SOME SERVICE PROVIDERS DO NOT SUPPORT SENDING MAIL VIA PHP AS IT DOES NOT PROVIDE SMTP AUTHENTICATION
         NEW WBMAILER CLASS IS ABLE TO SEND OUT MESSAGES USING SMTP WHICH RESOLVE THESE ISSUE (C. Sommer)

         NOTE:
         To use SMTP for sending out mails, you have to specify the SMTP host of your domain
         via the Settings panel in the backend of Website Baker
         */

        $fromaddress = preg_replace('/[\r\n]/', '', $fromaddress);
        $toaddress = preg_replace('/[\r\n]/', '', $toaddress);
        $subject = preg_replace('/[\r\n]/', '', $subject);
        $message = preg_replace('/\r\n?|\n/', '<br \>', $message);

        // create PHPMailer object and define default settings
        $myMail = new wbmailer();

        // set user defined from address
        if ($fromaddress != '')
        {
            // FROM-NAME
            if ($fromname != '')
                $myMail->FromName = $fromname;
            // FROM:
            $myMail->From = $fromaddress;
            // REPLY TO:
            $myMail->AddReplyTo($fromaddress);
        }

        // define recepient and information to send out
        // TO:
        $myMail->AddAddress($toaddress);
        // SUBJECT
        $myMail->Subject = $subject;
        // CONTENT (HTML)
        $myMail->Body = $message;
        // CONTENT (TEXT)
        $myMail->AltBody = strip_tags($message);

        // check if there are any send mail errors, otherwise say successful
        if (!$myMail->Send())
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * internal method to get a handle
     *
     * @access private
     * @param  string  $namespace - 'Helper',''
     * @param  string  $name      - class to load
     * @param  mixed   $args      - optional arguments
     *
     **/
    private function int_get_handle( $namespace, $name )
    {

        $classname = 'LEPTON_' . ( $namespace != '' ? $namespace.'_' : '' ) . $name;
        $numargs   = func_num_args();
        $argstring = '';

        if ( $numargs > 2 )
        {
            $args     = func_get_args();
            $arg_list = $args[2];
            $numargs  = count($arg_list);
            for ( $x=1; $x<$numargs; $x++ )
            {
                $argstring .= '$arg_list['.$x.']';
                if ($x != $numargs-1) $argstring .= ',';
            }
        }

		// do we already have a handle?
	    if (
				   isset($this->_handles[$classname])
			&&     isset($this->_handles[$classname]['__handle__'])
			&& is_object($this->_handles[$classname]['__handle__']) )
	    {
	        // if we have additional arguments, compare them with the ones
	        // we used in the last call
	        if (
					$argstring
				&&  isset($this->_handles[$classname]['__args__'])
				&&  $argstring == $this->_handles[$classname]['__args__'] )
	        {
	        	return $this->_handles[$classname]['__handle__'];
			}
	    }

        $filename = sanitize_path(LEPTON_PATH.'/framework/LEPTON/'.$namespace.'/'.$name.'.php');

		// check if the file exists
		if ( ! file_exists( $filename ) )
		{
		    trigger_error(sprintf("[ <b>%s</b> ] Invalid helper class name: [%s]", $_SERVER['SCRIPT_NAME'], $classname), E_USER_ERROR);
		    return false;
		}

		// okay, let's see if we already have that class
		if ( ! class_exists( $classname ) )
		{
		    @require_once $filename;
		}

  		// no handle or different arguments?
	    if (
				! isset( $this->_handles[$classname]['__handle__'] )
			||
			    (
				    $argstring
					&&  isset($this->_handles[$classname]['__args__'])
					&&  $argstring != $this->_handles[$classname]['__args__']
				)
		) {
	        // the class exists, but we don't have a handle
            try {
                $this->_handles[$classname]['__handle__'] = eval( "return new $classname($argstring);" );
            }
            catch( Exception $e ) {
                trigger_error(sprintf('Callback failed: %s($args)',$func), E_USER_ERROR);
            }
            
            if ( $argstring )
			{
                $this->_handles[$classname]['__args__'] = $argstring;
            }
		}

		if ( isset($this->_handles[$classname]['__handle__']) && is_object($this->_handles[$classname]['__handle__']) )
	    {
	        return $this->_handles[$classname]['__handle__'];
	    }
	    else {
	        trigger_error(sprintf("[ <b>%s</b> ] Invalid helper class: [%s]", $_SERVER['SCRIPT_NAME'], $name), E_USER_ERROR);
	        return false;
	    }
    
    }   // end function int_get_handle()


    /***************************************************************************
     * DEPRECATED FUNCTIONS
     * These functions are moved to LEPTON_Pages class
     **************************************************************************/
    public function page_is_visible($page) { return self::$pg->isVisible($page); }
    public function page_is_active($page)  { return self::$pg->isActive($page);  }
    public function page_link($link)       {
        if(!is_object(self::$pg))
        {
            self::$pg = LEPTON_Pages::getInstance();
        }
        return self::$pg->getLink($link);
    }

}
?>