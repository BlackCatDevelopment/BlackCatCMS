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
 *   @copyright       2015, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if (!class_exists('CAT_Helper_GitHub'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Helper_GitHub extends CAT_Object
    {
        // array to store config options
        protected $_config         = array( 'loglevel' => 8 );

        private static $ch         = NULL;
        private static $curl_error = NULL;

        /**
         * initializes CUrl
         *
         * @access public
         * @param  string  $url - optional
         * @return object  curl connection
         **/
        public static function curl_init($url=NULL)
        {
            if(self::$ch) return self::$ch;
            $headers = array(
                'User-Agent: php-curl'
            );
            self::$ch = curl_init();
            curl_setopt(self::$ch, CURLOPT_FOLLOWLOCATION, true    );
            curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, true    );
            curl_setopt(self::$ch, CURLOPT_SSL_VERIFYHOST, false   );
            curl_setopt(self::$ch, CURLOPT_SSL_VERIFYPEER, false   );
            curl_setopt(self::$ch, CURLOPT_MAXREDIRS     , 2       );
            curl_setopt(self::$ch, CURLOPT_HTTPHEADER    , $headers);
            if(defined('GITHUB_PROXY'))
                curl_setopt(self::$ch, CURLOPT_PROXY, GITHUB_PROXY);
            if(defined('GITHUB_PROXY_PORT'))
                curl_setopt(self::$ch, CURLOPT_PROXYPORT, GITHUB_PROXY_PORT);
            if($url)
                curl_setopt(self::$ch, CURLOPT_URL, $url);
            return self::$ch;
        }   // end function curl_init()

        /**
         *
         * @access public
         * @return
         **/
        public static function getZip($dlurl,$path)
        {
            $ch   = self::curl_init();
            curl_setopt($ch, CURLOPT_URL, $dlurl);
            $data = curl_exec($ch);
            if(curl_error($ch))
            {
                self::setError(trim(curl_error($ch)));
                return false;
            }
            if(curl_getinfo($ch,CURLINFO_HTTP_CODE)==302) // handle redirect
            {
                preg_match('/Location:(.*?)\n/', $data, $matches);
                $newUrl = trim(array_pop($matches));
                curl_setopt($ch, CURLOPT_URL, $newUrl);
                $data  = curl_exec($ch);
                if(curl_error($ch))
                {
                    self::setError(trim(curl_error($ch)));
                    return false;
                }
            }

            if(!$data || curl_error($ch)) {
                self::setError(trim(curl_error($ch)));
                return false;
            }

            if(!is_dir($path)) mkdir($path,0770);
            $file = pathinfo($dlurl,PATHINFO_BASENAME);
            $fd   = fopen($path.'/'.$file, 'w');
            fwrite($fd, $data);
            fclose($fd);

            if(filesize($path.'/'.$file)) return true;
            else                          self::setError('Filesize '.filesize($path.'/'.$file));

            return false;
        }   // end function getZip()
        

        /**
         * retrieve GitHub info about the given repository;
         * throws Exception on error
         *
         * @access public
         * @param  string  $org  - organisation name
         * @param  string  $repo - repository name
         * @param  string  $url  - sub url
         * @return json
         **/
        public static function retrieve($org,$repo,$url)
        {
            $url  = sprintf('https://api.github.com/repos/%s/%s/%s',
                    $org, $repo, $url);
            try {
                //echo "retrieve url: $url<br />";
                curl_setopt($connection,CURLOPT_URL,$url);
                $result = json_decode(curl_exec($connection), true);
                if(isset($result['documentation_url']))
                    self::printError( "GitHub Error: ", $result['message'], "<br />URL: $url<br />" );
                return $result;
            } catch ( Exception $e ) {
                self::printError( "CUrl error: ", $e->getMessage(), "<br />" );
            }
        }

        public static function retrieve_remote_file_size($url)
        {
             $ch = self::curl_init();
             curl_setopt($ch, CURLOPT_HEADER, TRUE);
             curl_setopt($ch, CURLOPT_NOBODY, TRUE);
             curl_setopt($ch, CURLOPT_URL, $url);
             $data = curl_exec($ch);
             $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
             return $size;
        }

        /**
         *
         * @access public
         * @return
         **/
        public static function getError()
        {
            return self::$curl_error;
        }   // end function getError()

        /**
         *
         * @access public
         * @return
         **/
        public static function resetError()
        {
            self::$curl_error = NULL;
        }   // end function resetError()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function setError($error)
        {
            self::$curl_error = $error;
        }   // end function setError()
        

    } // class CAT_Helper_GitHub

} // if class_exists()