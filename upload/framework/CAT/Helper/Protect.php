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

        public static function getInstance()
        {
            if (!self::$instance)
            {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function purify($content)
        {
            return CAT_Helper_Protect::getPurifier()->purify($content);
        }

        private static function getPurifier()
        {
            if ( is_object(self::$purifier) ) return self::$purifier;
            if ( ! class_exists('HTMLPurifier', false) )
            {
                include CAT_PATH . '/modules/lib_htmlpurifier/htmlpurifier/library/HTMLPurifier.auto.php';
            }
            $config = HTMLPurifier_Config::createDefault();
            // add some configuration
            // this is for target="xxx" as we allow to choose
            $config->set('Attr.AllowedFrameTargets', array('_blank','_self','_parent','_top'));
            self::$purifier = new HTMLPurifier();
            return self::$purifier;
        }
    }   // ----- end class CAT_Helper_Protect -----
}