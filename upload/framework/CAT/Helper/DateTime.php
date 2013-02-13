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

if (!class_exists('CAT_Helper_DateTime'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Helper_DateTime extends CAT_Object
    {
        private static $instance;

        public static function getInstance()
        {
            if (!self::$instance)
            {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * returns a list of known timezones, using DateTimeZone::listIdentifiers()
         **/
        public function getTimezones()
        {
            return DateTimeZone::listIdentifiers();
        }   // end function getTimezones()

        /**
         * returns a list of known time formats
         **/
        public function getTimeFormats()
        {
            global $user_time,$language_time;
            $actual_time = time();
            $TIME_FORMATS = array(
                'g:i|A' => date('g:i A', $actual_time),
                'g:i|a' => date('g:i a', $actual_time),
                'H:i:s' => date('H:i:s', $actual_time),
                'H:i'   => date('H:i', $actual_time),
            );
            if(isset($user_time) AND $user_time == true) {
           		$TIME_FORMATS['system_default'] = date(DEFAULT_TIME_FORMAT, $actual_time).' (System Default)';
                $TIME_FORMATS = array_reverse($TIME_FORMATS, true);
            }
            if(isset($language_time) && !array_key_exists($language_time,$TIME_FORMATS))
            {
                $TIME_FORMATS[$language_time] = date($language_time,$actual_time);
            }
            return $TIME_FORMATS;
        }   // end function getTimeFormats()

        /**
         * returns a list of known date formats
         **/
        public function getDateFormats()
        {
            global $user_time, $language_date_long, $language_date_short;
            $actual_time = time();
            $DATE_FORMATS = array(
                'l,|jS|F,|Y' => date('l, jS F, Y', $actual_time),
                'jS|F,|Y'    => date('jS F, Y', $actual_time).' (jS F, Y)',
                'd|M|Y'      => date('d M Y', $actual_time).' (d M Y)',
                'M|d|Y'      => date('M d Y', $actual_time).' (M d Y)',
                'D|M|d,|Y'   => date('D M d, Y', $actual_time).' (D M d, Y)',
                'd-m-Y'      => date('d-m-Y', $actual_time).' (D-M-Y)',
                'm-d-Y'      => date('m-d-Y', $actual_time).' (M-D-Y)',
                'd.m.Y'      => date('d.m.Y', $actual_time).' (D.M.Y)',
                'm.d.Y'      => date('m.d.Y', $actual_time).' (M.D.Y)',
                'd/m/Y'      => date('d/m/Y', $actual_time).' (D/M/Y)',
                'm/d/Y'      => date('m/d/Y', $actual_time).' (M/D/Y)',
                'j.n.Y'      => date('j.n.Y', $actual_time).' (j.n.Y)',
                'r'          => date('r'    , $actual_time).' (r)',
                'l,|d.|F|Y'  => date('l, d. F Y', $actual_time),        // German date
            );
            if(isset($user_time) && $user_time == true)
            {
		        $DATE_FORMATS['system_default'] = date(DEFAULT_DATE_FORMAT, $actual_time).' (System Default)';
                $DATE_FORMATS = array_reverse($DATE_FORMATS, true);
	        }
            if(isset($language_date_long) && !array_key_exists($language_date_long,$DATE_FORMATS))
            {
                $DATE_FORMATS[$language_date_long] = date($language_date_long,$actual_time);
            }
            if(isset($language_date_short) && !array_key_exists($language_date_short,$DATE_FORMATS))
            {
                $DATE_FORMATS[$language_date_short] = date($language_date_short,$actual_time);
            }
            return $DATE_FORMATS;
        }   // enc function getDateFormats()

        /**
         * returns the default time format:
         *   - checks $_SESSION['TIME_FORMAT'] first;
         *   - checks DEFAULT_TIME_FORMAT constant as next;
         *   - checks $language_time var set in current language file last
         *   - returns 'H:i' by default if none of the above is available
         **/
        public function getDefaultTimeFormat()
        {
            global $language_time;
            if ( isset ($_SESSION['TIME_FORMAT']) ) return $_SESSION['TIME_FORMAT'];
            if ( defined('DEFAULT_TIME_FORMAT') ) return DEFAULT_TIME_FORMAT;
            if ( isset($language_time) )          return $language_time;
            return 'H:i';
        }

        /**
         * returns the default date format (short)
         *   - checks $_SESSION['DATE_FORMAT'] first;
         *   - checks DEFAULT_DATE_FORMAT constant next;
         *   - checks $language_date_short var set in current language file last
         *   - returns 'D-M-Y' by default if none of the above is set
         **/
        public function getDefaultDateFormatShort()
        {
            global $language_date_short;
            if ( isset ($_SESSION['DATE_FORMAT']) ) return $_SESSION['DATE_FORMAT'];
            if ( defined('DEFAULT_DATE_FORMAT') )   return DEFAULT_DATE_FORMAT;
            if ( isset($language_date_short) )    return $language_date_short;
            return 'D-M-Y';
        }

        public function getDefaultDateFormatLong()
        {
            global $language_date_long;
            if ( defined('DEFAULT_DATE_FORMAT') ) return DEFAULT_DATE_FORMAT;
            if ( isset($language_date_long) )     return $language_date_long;
            return 'r';
        }
    }
}
