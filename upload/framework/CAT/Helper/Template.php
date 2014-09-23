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
 *   @link            http://www.blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if (!class_exists('CAT_Helper_Template'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }
    class CAT_Helper_Template extends CAT_Object
    {

        protected $debuglevel      = CAT_Helper_KLogger::CRIT;
        protected $_config         = array( 'loglevel' => CAT_Helper_KLogger::CRIT );
        private   static $_drivers = array();
        private   static $_driver  = NULL;
        protected static $template_menus = array();

        public function __construct($compileDir = null, $cacheDir = null)
        {
            parent::__construct( $compileDir, $cacheDir );

            // get current working directory
            $callstack = debug_backtrace();
            $this->workdir
                = ( isset( $callstack[0] ) && isset( $callstack[0]['file'] ) )
                ? realpath( dirname( $callstack[0]['file'] ) )
                : realpath( dirname(__FILE__) );

            if (
                 file_exists( $this->workdir.'/templates' )
            ) {
                $this->setPath( $this->workdir.'/templates' );
            }
        }   // end function __construct()

        /**
         *
         *
         *
         *
         **/
        public static function getInstance( $driver )
        {
            if ( ! (strcasecmp(substr($driver, strlen($driver) - strlen('driver')),'driver')===0) )
                $driver .= 'Driver';

            if ( ! file_exists( dirname(__FILE__).'/Template/'.$driver.'.php' ) )
            {
                $s = new self();
                $s->printFatalError( $s->lang->translate( 'No such template driver: ['.$driver.']' ) );
            }
            self::$_driver = $driver;
            if ( ! isset(self::$_drivers[$driver]) || ! is_object(self::$_drivers[$driver]) )
            {
                require dirname(__FILE__).'/Template/DriverDecorator.php';
                require dirname(__FILE__).'/Template/'.$driver.'.php';
                $driver = 'CAT_Helper_Template_'.$driver;
                self::$_drivers[$driver] = new CAT_Helper_Template_DriverDecorator( new $driver() );
                self::$_drivers[$driver]->setGlobals(
                      array(
                              'CAT_ADMIN_URL' => CAT_ADMIN_URL,
                              'CAT_URL' => CAT_URL,
                              'CAT_PATH' => CAT_PATH,
                              'LEPTON_URL' => CAT_URL,
                        	  'CAT_PATH' => CAT_PATH,
                        	  'CAT_THEME_URL' => CAT_THEME_URL,
                        	  'URL_HELP' => URL_HELP,
                      )
                  );
                $defs = get_defined_constants(true);
                foreach($defs['user'] as $const => $value ) {
                    if(preg_match('~^DEFAULT_~',$const)) { // DEFAULT_CHARSET etc.
                        self::$_drivers[$driver]->setGlobals($const,$value);
                        continue;
                    }
                    if(preg_match('~^WEBSITE_~',$const)) { // WEBSITE_HEADER etc.
                        self::$_drivers[$driver]->setGlobals($const,$value);
                        continue;
                    }
                    if(preg_match('~^SHOW_~',$const)) { // SHOW_SEARCH etc.
                        self::$_drivers[$driver]->setGlobals($const,$value);
                        continue;
                    }
                    if(preg_match('~^FRONTEND_~',$const)) { // FRONTEND_LOGIN etc.
                        self::$_drivers[$driver]->setGlobals($const,$value);
                        continue;
                    }
                    if(preg_match('~_FORMAT$~',$const)) { // DATE_FORMAT etc.
                        self::$_drivers[$driver]->setGlobals($const,$value);
                        continue;
                    }
                    if(preg_match('~^ENABLE_~',$const)) { // ENABLE_HTMLPURIFIER etc.
                        self::$_drivers[$driver]->setGlobals($const,$value);
                        continue;
                    }
                }
                // This is for old language strings
                global $HEADING, $TEXT, $MESSAGE;
                foreach ( array( 'TEXT', 'HEADING', 'MESSAGE' ) as $global ) {
                    if ( isset(${$global}) && is_array(${$global}) ) {
                        self::$_drivers[$driver]->setGlobals( $global, ${$global} );
                    }
                }

            }
            return self::$_drivers[$driver];
        }   // end function getInstance()

    	/**
         *
         * @access public
         * @return
         **/
        public static function get_template_block_name($template = DEFAULT_TEMPLATE, $selected = 1)
        {
            if ( SECTION_BLOCKS != false )
    		{
    			$template_location = ( $template != '' ) ?
    				CAT_PATH . '/templates/' . $template . '/info.php' :
    				CAT_PATH . '/templates/' . DEFAULT_TEMPLATE . '/info.php';

    			if ( file_exists($template_location) )
    			{
    				require($template_location);
    			}

                $driver = self::getInstance(self::$_driver);

    			return ( isset($block[$selected]) ? $block[$selected] : $driver->lang()->translate('Main') );
    			}
        }   // end function get_template_block_name()
        

    	/**
    	 * get_template_blocks function.
    	 *
    	 * Function to get all blocks of an template
    	 *
    	 * @access public
    	 * @param  mixed  $template (default: DEFAULT_TEMPLATE)
    	 * @param  int    $selected (default: 1)
    	 * @return void
    	 */
    	public static function get_template_blocks( $template = DEFAULT_TEMPLATE , $selected = 1)
    	{
    		// =============================================
    		// ! Include template info file (if it exists)
    		// =============================================
    		if ( SECTION_BLOCKS != false )
    		{
    			$template_location = ( $template != '' ) ?
    				CAT_PATH . '/templates/' . $template . '/info.php' :
    				CAT_PATH . '/templates/' . DEFAULT_TEMPLATE . '/info.php';

    			if ( file_exists($template_location) )
    			{
    				require($template_location);
    			}

                $driver = self::getInstance(self::$_driver);

    			// =========================
    			// ! Check if $block is set
    			// =========================
    			if ( !isset($block[1]) || $block[1] == '' )
    			{
    				$block[1]	= $driver->lang()->translate('Main');
    			}

    			// ================================
    			// ! Add block options to the list
    			// ================================
    			foreach ( $block as $number => $name )
    			{
    				$driver->template_block[$number] = array(
    					'NAME'			=> $name,
    					'VALUE'			=> $number,
    					'SELECTED'		=> ( $selected == $number || $selected == $name ) ? true : false
    				);
    				if ( $selected == $number || $selected == $name )
    				{
    					self::getInstance(self::$_driver)->current_block	= array(
    						'name'		=> $name,
    						'id'		=> $number
    					);
    				}
    			}
    			return $driver->template_block;
    		}
    		else return false;
    	}   // end function get_template_blocks()

    	/**
    	 * get all menus of an template
    	 *
    	 * @access public
    	 * @param  mixed $template (default: DEFAULT_TEMPLATE)
    	 * @param  int   $selected (default: 1)
    	 * @return void
    	 */
    	public static function get_template_menus( $template = DEFAULT_TEMPLATE , $selected = 1)
    	{
    		if ( CAT_Registry::get('MULTIPLE_MENUS') !== false )
    		{
    			$template_location = ( $template != '') ?
    				CAT_PATH . '/templates/' . $template . '/info.php' :
    				CAT_PATH . '/templates/' . CAT_Registry::get('DEFAULT_TEMPLATE') . '/info.php';

    			if ( file_exists($template_location) )
    			{
    				require($template_location);
    			}
    			if ( !isset($menu[1]) || $menu[1] == '' )
    			{
    				$menu[1]	= 'Main';
    			}
    			foreach ( $menu AS $number => $name )
    			{
    				self::$template_menus[$number] = array(
    					'NAME'			=> $name,
    					'VALUE'			=> $number,
    					'SELECTED'		=> ( $selected == $number || $selected == $name ) ? true : false
    				);
    			}
    			return self::$template_menus;
    		}
    		else
            {
                return false;
            }
    	}   // end function get_template_menus()

        /**
         * this method checks for existance of 'register_frontend_modfiles' in
         * a template file; not used yet
         *
         * @access public
         * @param  string  $file - file to check
         * @return boolean
         *
         **/
    	public function isOldTemplate($file)
    	{
    		if ( ! file_exists( $file ) )
    		{
    			return false;
    		}
    		$suffix = pathinfo( $file, PATHINFO_EXTENSION );
    		if ( $suffix == 'php' )
    		{
    			$string = implode( '', file($file) );
    			if ( $string )
    			{
    				$tokens  = token_get_all($string);
    				foreach( $tokens as $i => $token )
    				{
    					if ( is_array($token) )
    					{
    						if ( strcasecmp( $token[1], 'register_frontend_modfiles' ) == 0 )
    						{
    							return true;
    						}
    					}
    				}
    				return false;
    			}
    		}
    		return true;
    	}	// end function isOldTemplate()


    }
}

