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

if (!class_exists('CAT_Helper_Protect'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Helper_Protect extends CAT_Object
    {
        private static $instance;
        private static $purifier;
        private static $csrf;

        public static function getInstance()
        {
            if (!self::$instance)
            {
                self::$instance = new self();
            }
            return self::$instance;
        }   // end function getInstance()

        //**************************************************************************
        // interface to HTMLPurifier
        //**************************************************************************
        public function purify($content,$config=NULL)
        {
            return CAT_Helper_Protect::getPurifier($config)->purify($content);
        }   // end function purify()

        /**
         * include/enable HTMLPurifier
         *
         * @access private
         * @param  $config - optional config array passed to HTMLPurifier
         * @return object
         **/
        private static function getPurifier($config=NULL)
        {
            if ( is_object(self::$purifier) ) return self::$purifier;
            if ( ! class_exists('HTMLPurifier', false) )
            {
                $path = CAT_Helper_Directory::getInstance()->sanitizePath(CAT_PATH . '/modules/lib_htmlpurifier/htmlpurifier/library/HTMLPurifier.auto.php');
                if ( ! file_exists( $path ) )
                {
                    CAT_Object::getInstance()->printFatalError('Missing library HTMLPurifier!');
            }
                include $path;
            }
            $pconfig = HTMLPurifier_Config::createDefault();
            if($config && is_array($config))
            {
                foreach($config as $key => $val)
                {
                    $pconfig->set($key,$val);
                }
            }
            $pconfig->set('AutoFormat.Linkify', TRUE);
            $pconfig->set('URI.Disable',false);
            // allow most HTML but not all (no forms, for example)
            $pconfig->set('HTML.Allowed','a[href|title],abbr[title],acronym[title],b,blockquote[cite],br,caption,cite,code,dd,del,dfn,div,dl,dt,em,h1,h2,h3,h4,h5,h6,i,img[src|alt|title|class],ins,kbd,li,ol,p,pre,s,strike,strong,sub,sup,table,tbody,td,tfoot,th,thead,tr,tt,u,ul,var');
            self::$purifier = new HTMLPurifier($pconfig);
            return self::$purifier;
        }

        /**
         * enable csrf-magic by including csrf-magic.php
         * will throw a fatal error if the lib is not available!
         *
         * @access public
         * @return void
         **/
        public function enableCSRFMagic()
        {
            if ( is_object(self::$csrf) ) return self::$csrf;
            if ( ! function_exists('csrf_ob_handler') )
            {
                $path = CAT_Helper_Directory::sanitizePath(CAT_PATH . '/modules/lib_csrfmagic/csrf-magic.php');
                if ( ! file_exists( $path ) )
                    $this->printFatalError('Missing library CSRF-Magic!');
                include $path;
                CAT_Registry::set('CSRF_PROTECTION_ENABLED',true,true);
            }
        }   // end function enableCSRFMagic()

        /*
         * creates tokens for CSRF protection and stores it in the session
         * requirements: an active session must be available
         *
         * uses csrf-magic if available; returns NULL if not
         *
         * @access public
         * @param  string  $mode - this is for backward compatibility with WB
         * @return string
         */
    	public static function createToken($mode = 'POST')
    	{
            // for backward compatibility...
            if((is_string($mode) && strtolower($mode) == 'post') || ($mode === true))
    			return '';
            // We return an empty string here, just to keep WB modules happy.
            // The CSRF protection will be added automatically to the Backend,
            // so there's no need to do it this way.

            $path = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/lib_csrfmagic/csrf-magic.php');
            if ( file_exists( $path ) )
            {
                if ( ! function_exists('csrf_get_tokens') )
                    include_once $path;
                return csrf_get_tokens();
    		}
            else
            {
        		// no token without csrf-magic!
                return NULL;
    		}
    	}   // end function createToken()

        /*
         * checks received token against session-stored tokens
         *
         * requirements: an active session must be available
         * 
         * uses csrf-magic if available; returns true if not
         *
         * @access public
         * @param  string  $token - token to check
         * @return boolean - true if numbers matches against one of the stored tokens
         */
        public static function checkToken($token)
    	{
    		if (!TOKEN_LIFETIME) return true;

            // for backward compatibility with WB...
            if((is_string($token) && strtolower($token) == 'post') || ($token === true))
    			return true;
            // We return true here, just to keep WB modules happy.
            // The CSRF protection will be added automatically to the Backend,
            // so there's no need to do it this way.

            $path = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/lib_csrfmagic/csrf-magic.php');
            if ( file_exists( $path ) )
            {
                if ( ! function_exists('csrf_check_token') )
                    include_once $path;
                return csrf_check_token($token);
    		}
            else
    		{
        		// no token without csrf-magic!
                return true;
    	}
    	}   // end function checkToken()

        /**
         * generate salt
         *
         * @access private
         * @return string
         **/
    	private function _generate_salt()
    	{
    		// server depending values
     		$salt  = ( isset($_SERVER['SERVER_SIGNATURE']) ) ? $_SERVER['SERVER_SIGNATURE'] : 'BL';
    		$salt .= ( isset($_SERVER['SERVER_SOFTWARE']) )  ? $_SERVER['SERVER_SOFTWARE']  : 'A';
    		$salt .= ( isset($_SERVER['SERVER_NAME']) )      ? $_SERVER['SERVER_NAME']      : 'CK';
    		$salt .= ( isset($_SERVER['SERVER_ADDR']) )      ? $_SERVER['SERVER_ADDR']      : 'C';
    		$salt .= ( isset($_SERVER['SERVER_PORT']) )      ? $_SERVER['SERVER_PORT']      : 'AT';
    		$salt .= PHP_VERSION;
    		$salt .= time();
    		return $salt;
        }

    }   // ----- end class CAT_Helper_Protect -----
}