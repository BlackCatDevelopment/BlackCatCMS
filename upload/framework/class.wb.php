<?php
/**
 * This file is part of Black Cat CMS Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 *
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          Website Baker Project, LEPTON Project
 * @copyright       2004-2010, Website Baker Project
 * @copyright       2013, Black Cat Development
 * @link            http://blackcat-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 *
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('CAT_PATH')) {
	include(CAT_PATH.'/framework/class.secure.php');
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

require_once(CAT_PATH . '/framework/class.database.php');

// Include new wbmailer class (subclass of PHPmailer)
require_once(CAT_PATH . "/framework/class.wbmailer.php");

// new internationalization helper class
include CAT_PATH.'/framework/CAT/Helper/I18n.php';

// new pages class
include CAT_PATH.'/framework/CAT/Pages.php';

// new users class
include CAT_PATH.'/framework/CAT/Users.php';

class wb
{
    public  $password_chars      = 'a-zA-Z0-9\_\-\!\#\*\+';
    private $lep_active_sections = NULL;
    private $_handles            = NULL;
    public  $lang                = NULL;
    public  $users               = NULL;
    public  $pg                  = NULL;
    
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
        global $TEXT,$HEADING,$MESSAGE,$OVERVIEW;
		// create accessor/to language helper
  		$this->lang  = CAT_Helper_I18n::getInstance(LANGUAGE);
  		// load globals from old language files
		foreach( array( 'MENU', 'TEXT', 'HEADING', 'MESSAGE', 'OVERVIEW' ) as $var )
		{
		    $this->lang->addFile( LANGUAGE.'.php', NULL, $var );
		}
        $this->pg    = CAT_Pages::getInstance(-1);
        $this->users = CAT_Users::getInstance();
        set_error_handler( array('wb','cat_error_handler') );
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

    /**
     * custom error handler
     **/
    public static function cat_error_handler($errno,$errstr,$errfile=NULL,$errline=NULL,array $errcontext)
    {
        if (!(error_reporting() & $errno))
        {
            return;
        }
        // check for AJAX call
        if ( CAT_Helper_Validate::getInstance()->get('_REQUEST','_cat_ajax') )
        {
            return;
        }
        global $parser;
        // replace path in $errfile and $errstr to protect the data
        $errfile = str_ireplace( array(CAT_PATH,'\\'), array('/abs/path/to','/'), $errfile );
        $errstr  = str_ireplace( array(CAT_PATH,'\\'), array('/abs/path/to','/'), $errstr  );
        $output  = NULL;
        $fatal   = false;
        switch ($errno)
        {
            case E_USER_ERROR:
                $output = "<b>Black Cat CMS ERROR</b><br />\n"
                        . "&nbsp;&nbsp;[ERRNO:$errno] $errstr<br />\n"
                        . "&nbsp;&nbsp;Fatal error on line $errline in file $errfile<br />"
                        . "&nbsp;&nbsp;PHP Version " . PHP_VERSION . " (" . PHP_OS . ")<br />\n"
                        . "Aborting...<br />\n";
                $fatal  = true;
                break;

            case E_USER_WARNING:
                $output = "<b>Black Cat CMS WARNING</b><br />\n&nbsp;&nbsp;[$errno] $errstr<br />\n";
                break;

            case E_USER_NOTICE:
                $output = "<b>Black Cat CMS NOTICE</b><br />\n&nbsp;&nbsp;[$errno] $errstr<br />\n";
                break;

            default:
                $output = "<b>Black Cat CMS NOTICE</b><br />\n&nbsp;&nbsp;Unknown error type:<br />\n&nbsp;&nbsp;[$errno] $errstr<br />\n";
                break;
        }   // end switch
        if ( defined('CAT_DEBUG') && true === CAT_DEBUG )
        {
                $output .= "<textarea cols=\"100\" rows=\"20\" style=\"width: 100%;\">"
                        .  print_r( debug_backtrace(),1 )."</textarea>";
        }
        if ( $fatal )
    {
            if ( !headers_sent() ) {
                echo header('content-type:text/html');
    }
            if ( is_object($parser) )
    {
    			$parser->setPath(CAT_THEME_PATH . '/templates');
    			$parser->setFallbackPath(CAT_THEME_PATH . '/templates');
    			$parser->output('error_page.lte', array( 'MESSAGE' => $output, 'LINK' => '' ));
                }
            else {
                echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=windows-1250">
  <title>Black Cat CMS Error Message</title>
  </head>
  <body>', $output, '</body></html>';
            }
            exit;
        }
        else
        {
            echo $output;
    }
        return false;
    }   // end error handler

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
		// name must not contain CAT_...
	    $name      = preg_replace( '~^cat_~i', '', $name );
        $args      = func_get_args();
        // remove first element (it's the name)
        array_shift($args);
	    return $this->int_get_handle('',$name,$args=func_get_args());
    }   // end function get_controller()
    
    /**
     * get accessor to helper class
     * @access public
     * @param  string $name - name of the class
     **/
	public function get_helper($name)
	{
		// name must not contain CAT_Helper_...
	    $name      = preg_replace( '~^cat_helper_~i', '', $name );
        return $this->int_get_handle('Helper',$name,$args=func_get_args());
	}   // end function get_helper()

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
		global $TEXT, $parser;

		if (true === is_array($message)){
			$message = implode("<br />", $message);
		}

				$parser->setPath(CAT_THEME_PATH . '/templates');
				$parser->setFallbackPath(CAT_THEME_PATH . '/templates');

				$data_dwoo['MESSAGE']			= $this->lang->translate($message);
				$data_dwoo['REDIRECT']			= $redirect;
				$data_dwoo['REDIRECT_TIMER']	= REDIRECT_TIMER;

				// ==================== 
				// ! Parse the header 	
				// ==================== 
				$parser->output('success.lte', $data_dwoo);

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

        $classname = 'CAT_' . ( $namespace != '' ? $namespace.'_' : '' ) . $name;
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

        $filename = sanitize_path(CAT_PATH.'/framework/CAT/'.$namespace.'/'.$name.'.php');

		// check if the file exists
		if ( ! file_exists( $filename ) )
		{
		    trigger_error(sprintf("[ <b>%s</b> ] Invalid %s name: [%s]", $_SERVER['SCRIPT_NAME'], ($namespace=='Helper'?'helper':'controller'), $classname), E_USER_ERROR);
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
     * These functions are moved to other classes
     **************************************************************************/

    /* moved to CAT_Object */
    public function print_error($message, $link = 'index.php', $auto_footer = true)
    {
        CAT_Pages::getInstance(-1)->printError($message,$link);
    }

    /* moved to CAT_Helper_Mail */
    public function mail($fromaddress, $toaddress, $subject, $message, $fromname = '')
    {
        return CAT_Helper_Mail::getInstance('PHPMailer')->sendMail($fromaddress, $toaddress, $subject, $message, $fromname);
    }
    public function page_is_visible($page) { return CAT_Pages::getInstance(-1)->isVisible($page); }
    public function page_is_active($page)  { return CAT_Pages::getInstance(-1)->isActive($page);  }
    public function page_link($link)       { return CAT_Pages::getInstance(-1)->getLink($link);   }
    public function show_page($page)       { return CAT_Pages::getInstance(-1)->show_page($page); }

    /* moved to CAT_Sections */
    public function section_is_active($section_id) { return CAT_Sections::getInstance()->section_is_active($section_id); }

    /* moved to CAT_Users */
    public function get_user_id()          { return CAT_Users::getInstance()->get_user_id();      }
    public function get_group_id()         { return CAT_Users::getInstance()->get_group_id();     }
    public function get_groups_id()        { return CAT_Users::getInstance()->get_groups_id();    }
    public function get_group_name()       { return CAT_Users::getInstance()->get_group_name();   }
    public function get_groups_name()      { return CAT_Users::getInstance()->get_groups_name();  }
    public function get_username()         { return CAT_Users::getInstance()->get_username();     }
    public function get_display_name()     { return CAT_Users::getInstance()->get_display_name(); }
    public function get_email()            { return CAT_Users::getInstance()->get_email();        }
    public function get_home_folder()      { return CAT_Users::getInstance()->get_home_folder();  }
    public function is_authenticated()     { return CAT_Users::getInstance()->is_authenticated(); }

    public function is_group_match($groups_list1 = '', $groups_list2 = '')
    {
        return CAT_Users::getInstance()->is_group_match($groups_list1,$groups_list2);
    }
    public function get_groups($viewing_groups = array() , $admin_groups = array(), $insert_admin = true)
    {
         return CAT_Users::getInstance()->get_groups($viewing_groups,$admin_groups,$insert_admin);
    }

}
?>