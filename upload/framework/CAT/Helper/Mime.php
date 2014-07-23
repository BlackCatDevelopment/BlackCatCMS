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

if (!class_exists('CAT_Helper_Mime'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Helper_Mime extends CAT_Object
    {
        private static $instance;
        private static $mimetypes = array();
        private static $allowed   = array();
        private static $suffixes  = array();
        protected $_config        = array( 'loglevel' => 8 );

        public static function getInstance()
        {
            if (!self::$instance)
            {
                self::$instance = new self();
            }
            return self::$instance;
        }   // end function getInstance()

        /**
         * retrieve known Mime types from the DB; only entries with registered
         * suffixes and labels are considered
         *
         * @access public
         * @return array
         **/
        public static function getMimeTypes()
        {
            $self = self::getInstance();
            $self->log()->LogDebug('getting known mimetypes from DB');
            $res = $self->db()->query(
                'SELECT * FROM `:prefix:mimetypes` WHERE `mime_suffixes` IS NOT NULL AND `mime_label` IS NOT NULL'
            );
            if($res)
            {
                while(false!==($row=$res->fetch()))
                {
                    $self->log()->LogDebug('current row',$row);
                    $suffixes = explode('|',$row['mime_suffixes']);
                    $self->log()->LogDebug('suffixes',$suffixes);
                    foreach($suffixes as $suffix)
                    {
                        if ( $suffix == '' ) continue;
                        if ( ! isset(self::$mimetypes[$suffix]) )
                            self::$mimetypes[$suffix] = array();
                        self::$mimetypes[$suffix][] = $row['mime_type'];
                    }
                }
            }
            $self->log()->LogDebug('registered mime types',self::$mimetypes);
            return self::$mimetypes;
        }   // end function getMimeTypes()

        /**
         * retrieve allowed Mime types; we use the 'upload_allowed' entry in
         * the settings table combined with the list of known Mime types here
         *
         * @access public
         * @param  string  $filter - optional filter, for example, 'image/*'
         * @return array
         **/
        public static function getAllowedMimeTypes($filter=NULL)
        {
            if(!count(self::$allowed))
            {
                $self = self::getInstance();
                if(!count(self::$mimetypes)) self::getMimeTypes();

                $self->log()->LogDebug('getting allowed upload mimetypes from settings');
                if(CAT_Registry::exists('UPLOAD_ALLOWED'))
                {
                    $suffixes = explode(',', CAT_Registry::get('UPLOAD_ALLOWED'));
                    $self->log()->logDebug('allowed suffixes:',$suffixes);
                    for($i=0;$i<count($suffixes);$i++)
                    {
                        $suffix = $suffixes[$i];
                        if (isset(self::$mimetypes[$suffix]))
                        {
                            foreach(array_values(self::$mimetypes[$suffix]) as $type)
                            {
                                if(!in_array($type,self::$allowed))
                                    self::$allowed[] = $type;
                                if(!array_key_exists($suffix,self::$suffixes))
                                    self::$suffixes[$suffix] = $type;
                            }
                        }
                    }
                }
                $self->log()->LogDebug('allowed',self::$allowed);
            }
            if($filter)
            {
                $self->log()->LogDebug(sprintf('using filter (preg_match) [~^%s~]',$filter),self::$allowed);
                $temp = array();
                foreach(self::$allowed as $type)
                    if( preg_match( '~^'.$filter.'~', $type ) )
                        $temp[] = $type;
                return $temp;
            }
            return self::$allowed;
        }   // end function getAllowedMimeTypes()

        /**
         * retrieve a list of suffixes; we use the 'upload_allowed' entry in
         * the settings table combined with the list of known Mime types here
         *
         * @access public
         * @param  string  $filter - optional filter, for example, 'image/*'
         * @return array
         **/
        public static function getAllowedFileSuffixes($filter=NULL)
        {
            $self = self::getInstance();
            if(!count(self::$suffixes))
                self::getAllowedMimeTypes();
            if($filter)
            {
                $self->log()->LogDebug(sprintf('using filter (preg_match) [~^%s~]',$filter),self::$suffixes);
                $temp = array();
                foreach(self::$suffixes as $suffix => $type)
                    if( preg_match( '~^'.$filter.'~', $type ) )
                        $temp[] = $suffix;
                return $temp;
            }
            return self::$suffixes;
        }   // end function getAllowedFileSuffixes()

    }   // ----- class CAT_Helper_Mime -----
}