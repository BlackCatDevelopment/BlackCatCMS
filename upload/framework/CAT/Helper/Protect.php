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
        }

        //**************************************************************************
        // interface to HTMLPurifier
        //**************************************************************************
        public function purify($content,$config=NULL)
        {
            return CAT_Helper_Protect::getPurifier($config)->purify($content);
        }

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

        public function enableCSRFMagic()
        {
            if ( is_object(self::$csrf) ) return self::$csrf;
            if ( ! function_exists('csrf_ob_handler') )
            {
                $path = CAT_Helper_Directory::getInstance()->sanitizePath(CAT_PATH . '/modules/lib_csrfmagic/csrf-magic.php');
                if ( ! file_exists( $path ) )
                {
                    CAT_Object::getInstance()->printFatalError('Missing library CSRF-Magic!');
                }
                include $path;
            }
        }

    }   // ----- end class CAT_Helper_Protect -----
}