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

if (!class_exists('CAT_Helper_Validate'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Helper_Validate extends CAT_Object
    {
        protected      $_config             = array( 'loglevel' => 8 );
        private static $instance;

        public static function getInstance()
        {
            if (!self::$instance)
            {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __call($method, $args)
        {
            if ( ! isset($this) || ! is_object($this) )
                return false;
            if ( method_exists( $this, $method ) )
                return call_user_func_array(array($this, $method), $args);
        }

        /**
         * check a value as type
         *
         * @access public
         * @param  mixed  $value
         * @param  string $as
         * @return mixed
         **/
        public static function check($value,$as)
        {
            $func = 'is_'.$as;
            if ( ! function_exists($func) )
            {
                CAT_Object::getInstance()->printFatalError( 'No such validation method: '.$as );
            }
            if ( ! $func($value) ) return false;
            return $value;
        }

        /**
         * global method to get data from globals
         *
         * @access public
         * @param  string  $global - name of the superglobal
         * @param  string  $key    - name of the key/var to get
         * @param  string  $require - value type (scalar, numeric, array)
         * @return mixed
         **/
        public static function get( $global, $key, $require = NULL, $escape = false )
        {
            self::getInstance()->log()->logDebug(sprintf('Get key [%s] from global var [%s] validate as [%s]',$key,$global,$require));
            $glob = array();
            if ( isset($GLOBALS[$global]) )
            {
                $glob =& $GLOBALS[$global];
            }
            $value = isset($glob[$key]) ? $glob[$key] : NULL;
            if ( $value && $require )
            {
                $value = self::check($value,$require);
            }
            if ( $value && $escape )
            {
                $value = self::add_slashes($value);
            }
            self::$instance->log()->logDebug('returning value:',$value);
            return $value;
        }   // end function get()

        /**
         * Modified addslashes function which takes into account magic_quotes
         *
         * @access public
         * @param  string  $input
         * @return string
         **/
        public static function add_slashes($input)
        {
            if (get_magic_quotes_gpc() || (!is_string($input)))
            {
                return $input;
            }
            $output = addslashes($input);
            return $output;
        }   // end function add_slashes()

        /**
         * Modified stripslashes function which takes into account magic_quotes
         *
         * @access public
         * @param  string  $input
         * @return string
         **/
        public static function strip_slashes($input)
        {
            if (!get_magic_quotes_gpc() || (!is_string($input)))
            {
                return $input;
            }
            $output = stripslashes($input);
            return $output;
        }   // end function strip_slashes()

        /**
         * create a random fieldname
         *
         * @access public
         * @param  prefix - static prefix, i.e. 'username_'
         * @param
         **/
        public static function createFieldname($prefix,$offset=NULL,$length=12)
        {
            if ( substr($prefix,-1,1) != '_' ) $prefix .= '_';
            $salt      = strtolower(md5(uniqid(rand(),true)));
            $offset    = ( $offset === NULL ) ? rand(1,12) : $offset;
            $fieldname = $prefix.substr($salt,$offset,$length);
            return $fieldname;
        }   // end function createFieldname()

        /**
         * dump all items; you should NEVER use this method in production code!
         *
         *
         *
         **/
        public function dump() {

            echo "<h2>CAT_Helper_Validate DUMP</h2>",
                 "<h3>\$_GET Array</h3>";
            var_dump($_GET);
            echo "<h3>\$_POST Array</h3>";
            var_dump($_POST);
            echo "<h3>\$_SERVER Array</h3>";
            var_dump($_SERVER);

        }   // end function dump()

        /**
         * replaces CAT_PATH with CAT_URL in given $url
         * if CAT_URL is stored without a scheme (relative URI), the current
         * scheme will be added before replacement
         *
         * @access public
         * @param  string $url
         * @return string
         **/
        public static function uri2path($url)
        {
            return str_ireplace(self::sanitize_url(self::getURI(CAT_URL)),CAT_Helper_Directory::sanitizePath(CAT_PATH),self::sanitize_url($url));
        }   // end function uri2path(()

        /**
         * replaces CAT_URL with CAT_PATH in given $path
         *
         * @access public
         * @param  string $path
         * @return string
         **/
        public static function path2uri($path)
        {
            return str_ireplace(CAT_Helper_Directory::sanitizePath(CAT_PATH),self::sanitize_url(self::getURI(CAT_URL)),CAT_Helper_Directory::sanitizePath($path));
        }   // end function path2uri(()


        /**
         * if CAT_URL does not contain a scheme (scheme relative URL), the
         * appropriate scheme is added here
         *
         * @access public
         * @param  string  $url
         * @return string
         **/
        public static function getURI($url)
        {
            $rel_parsed = parse_url($url);
            if(!array_key_exists('scheme',$rel_parsed ) || $rel_parsed['scheme']=='')
                $url = (isset($_SERVER['HTTPS']) ? 'https:' : 'http:') . $url;
            return $url;
        }   // end function getURI()

        /**
         * Get POST data
         *
         * TODO: add sanitize/validate
         *
         * @access public
         * @param  string  $field - fieldname
         * @param  string  $require - value type (scalar, numeric, array)
         * @param  boolean $escape  - use add_slashes(); default: false
         * @return mixed
         **/
        public static function sanitizePost( $field, $require=NULL, $escape = false )
        {
            self::$instance->log()->logDebug(sprintf('get field [%s] from $_POST, require type [%s], escape [%s]',$field,$require,$escape));
            return self::get('_POST',$field,$require,$escape);
        }   // end function sanitizePost()

        /**
         * Get GET data
         *
         * TODO: add sanitize/validate
         *
         * @access public
         * @param  string  $field - fieldname
         * @param  string  $require - value type (scalar, numeric, array)
         * @return mixed
         **/
        public static function sanitizeGet($field,$require=NULL,$escape=false)
        {
            self::$instance->log()->logDebug(sprintf('get field [%s] from $_GET, require type [%s], escape [%s]',$field,$require,$escape));
            return self::get('_GET',$field,$require,$escape);
        }   // end function sanitizeGet()

        /**
         * convenience function to meet the names of the other ones
         **/
        public static function sanitizeSession($field,$require=NULL,$escape=false)
        {
            return self::get('_SESSION',$field,$require,$escape);
        }   // end function sanitizeSession()

        /**
         * Get SESSION data
         *
         * @access public
         * @param  string  $field - fieldname
         * @param  string  $require - value type (scalar, numeric, array)
         * @return mixed
         **/
        public static function fromSession($field,$require=NULL,$escape=false)
        {
            return self::get('_SESSION',$field,$require,$escape);
        }   // end function fromSession()

        /**
         * Get SERVER data
         *
         * @access public
         * @param  string  $field - fieldname
         * @param  string  $require - value type (scalar, numeric, array)
         * @return mixed
         **/
        public static function sanitizeServer($field,$require=NULL,$escape=false)
        {
            return self::get('_SERVER',$field,$require,$escape);
        }   // end function sanitizeServer()

        /**
         * check if string is a MD5 hash
         *
         * @access public
         * @param  string  $md5
         * @return boolean
         **/
        public static function isValidMD5($md5 ='') {
            return strlen($md5) == 32 && ctype_xdigit($md5);
        }   // end function isValidMD5()

        //*********************************************************************
        // convenience methods; just wrap filter_var
        //*********************************************************************
        public static function sanitize_string($string)
        {
            return filter_var($string, FILTER_SANITIZE_STRING);
        }

        public static function sanitize_email($address)
        {
            return filter_var($address, FILTER_SANITIZE_EMAIL);
        }

        /**
         * sanitize URL by removing /../ and similiar constructs
         * takes optional second param to use filter_var(); please note that
         * this will remove any Umlauts!
         *
         * @access public
         * @param  string  $address
         * @param  boolean $use_filter - default false
         * @return string
         **/
        public static function sanitize_url($address,$use_filter=false)
        {
            if($use_filter)
                $address = filter_var($address, FILTER_SANITIZE_URL);
            // fix for protocol relative URLs
            if (substr($address, 0, 2) == "//") 
                $address = (isset($_SERVER['HTTPS']) ? 'https:' : 'http:')
                         . $address;
            // href="http://..." ==> href isn't relative
            $rel_parsed = parse_url($address);
            if(!isset($rel_parsed['path']) || $rel_parsed['path'] == '') return '';
            $path       = $rel_parsed['path'];
            $path       = preg_replace('~/\./~', '/', $path); // bla/./bloo ==> bla/bloo
            if(!isset($rel_parsed['host']) ) $rel_parsed['host'] = CAT_URL;
            // resolve /../
            // loop through all the parts, popping whenever there's a .., pushing otherwise.
            $parts      = array();
            foreach ( explode('/', preg_replace('~/+~', '/', $path)) as $part )
                if ($part === ".." || $part == '')
                    array_pop($parts);
                elseif ($part!="")
                    $parts[] = $part;
            if( !is_array($rel_parsed) || !array_key_exists('scheme',$rel_parsed) )
                $rel_parsed['scheme'] =  (isset($_SERVER['HTTPS']) ? 'https' : 'http');
            $url = $rel_parsed['scheme'] . '://'
                 . $rel_parsed['host']
                 . ( isset($rel_parsed['port']) ? ':'.$rel_parsed['port'] : NULL )
                 . "/" . implode("/", $parts);
            return $url;
        }   // end function sanitize_url()

        public static function validate_string($string)
        {
            return filter_var($string, FILTER_VALIDATE_STRING);
        }
        public static function validate_ip($ip)
        {
            return filter_var($ip, FILTER_VALIDATE_IP);
        }
        public static function validate_email($address)
        {
            return filter_var($address, FILTER_VALIDATE_EMAIL);
        }
        public static function validate_url($address)
        {
            return filter_var($address, FILTER_VALIDATE_URL);
        }
    }
}
