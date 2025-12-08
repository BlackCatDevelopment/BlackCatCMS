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
 *   @copyright       2016, Black Cat Development
 *   @link            https://blackcat-cms.org
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

        public function __call($method, $args)
        {
            if ( ! isset($this) || ! is_object($this) )
                return false;
            if ( method_exists( $this, $method ) )
                return call_user_func_array(array($this, $method), $args);
        }

        /**
         * check given date format
         *
         * @access public
         * @param  string $df
         * @return boolean
         **/
        public static function checkDateformat($date_format)
        {
            $date_format_key	= str_replace(' ', '|', $date_format);
            $date_formats       = CAT_Helper_DateTime::getDateFormats();
            return array_key_exists( $date_format_key, $date_formats );
        }   // end function checkDateformat()

        public static function checkTimeformat($time_format)
        {
            $time_format_key	= str_replace(' ', '|', $time_format);
            $time_formats       = CAT_Helper_DateTime::getTimeFormats();
            return array_key_exists($time_format_key, $time_formats);
        }   // end function checkTimeformat()

        /**
         * check given timezone
         * the timezone string must match a value in the table
         *
         * @access public
         * @param  string  $tz
         * @return boolean
         **/
        public static function checkTZ($tz)
        {
            $timezone_table     = CAT_Helper_DateTime::getTimezones();
            if ( in_array($tz, $timezone_table) )
            	return true;
            return false;
        }   // end function checkTZ()

        /**
         * returns formatted date
         *
         * @access public
         * @param  string  $t    - optional timestamp
         * @param  boolean $long - get long format (default:false)
         * @return string
         **/
        public static function getDate($t=NULL,$long=false)
        {
            $format = ( $long == true )
                    ? self::getDefaultDateFormatLong()
                    : self::getDefaultDateFormatShort();
            return self::strftime($format,($t?$t:time()));
        }   // end function getDate()

        /**
         * returns formatted time
         *
         * @access public
         * @param  string  $t   - optional timestamp
         * @return string
         **/
        public static function getTime($t=NULL)
        {
            $format = self::getDefaultTimeFormat();
            return self::strftime($format,($t?$t:time()));
        }   // end function getTime()

        /**
         * returns formatted date and time
         *
         * @access public
         * @param  string  $t   - optional timestamp
         * @return string
         **/
        public static function getDateTime($t=NULL)
        {
            return self::strftime(
                sprintf(
                    '%s %s',
                    self::getDefaultDateFormatShort(),
                    self::getDefaultTimeFormat()
                ),
                ($t?$t:time())
            );
        }   // end function getDateTime()

        /**
         * get currently used timezone string
         **/
        public static function getTimezone()
        {
            $tz = CAT_Helper_Validate::getInstance()->fromSession('TIMEZONE_STRING');
            return
                isset($tz)
                ? $tz
                : DEFAULT_TIMEZONE_STRING;
        }

        /**
         * returns a list of known timezones, using DateTimeZone::listIdentifiers()
         **/
        public static function getTimezones()
        {
            return DateTimeZone::listIdentifiers();
        }   // end function getTimezones()

        /**
         * returns a list of known time formats
         **/
        public static function getTimeFormats()
        {
            global $user_time,$language_time;
            $actual_time = time();
            $TIME_FORMATS = array(
                '%I:%M|%p' => self::strftime('%I:%M %p', $actual_time),
                '%H:%M:%S' => self::strftime('%H:%M:%S', $actual_time),
                '%H:%M'    => self::strftime('%H:%M'   , $actual_time),
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
        public static function getDateFormats()
        {
            global $user_time, $language_date_long, $language_date_short;
            $actual_time = time();
            $locale      = setlocale(LC_ALL, 0);
            $ord         = date('S', $actual_time);
            $ord_long    = (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
                         ? '%#d #O# %B, %Y'
                         : '%e #O# %B, %Y';
            $j_short     = (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
                         ? '%#d.%-m.%Y'
                         : '%e.%-m.%Y';
            $long        = (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
                         ? '%A, %#d %B, %Y'
                         : '%A, %e %B, %Y';
            if ( defined('LANGUAGE') ) setlocale(LC_ALL, LANGUAGE);
            $DATE_FORMATS = array(
                '%A,|%e|%B,|%Y' => utf8_encode(self::strftime($long, $actual_time)),
                '%e|%B,|%Y'     => utf8_encode(self::strftime(str_replace(' #O#', $ord, $ord_long), $actual_time)).' (jS F, Y)',
                '%d|%m|%Y'      => utf8_encode(self::strftime('%d %m %Y',      $actual_time)).' (d M Y)',
                '%b|%d|%Y'      => utf8_encode(self::strftime('%b %d %Y',      $actual_time)).' (M d Y)',
                '%a|%b|%d,|%Y'  => utf8_encode(self::strftime('%a %b %d, %Y',  $actual_time)).' (D M d, Y)',
                '%d-%m-%Y'      => utf8_encode(self::strftime('%d-%m-%Y',      $actual_time)).' (D-M-Y)',
                '%m-%d-%Y'      => utf8_encode(self::strftime('%m-%d-%Y',      $actual_time)).' (M-D-Y)',
                '%d.%m.%Y'      => utf8_encode(self::strftime('%d.%m.%Y',      $actual_time)).' (D.M.Y)',
                '%m.%d.%Y'      => utf8_encode(self::strftime('%m.%d.%Y',      $actual_time)).' (M.D.Y)',
                '%d/%m/%Y'      => utf8_encode(self::strftime('%d/%m/%Y',      $actual_time)).' (D/M/Y)',
                '%m/%d/%Y'      => utf8_encode(self::strftime('%m/%d/%Y',      $actual_time)).' (M/D/Y)',
                #'%e.%-m.%Y'     => utf8_encode(strftime($j_short,        $actual_time)).' (j.n.Y)',
                '%a, %d %b %Y %H:%M:%S %z' => utf8_encode(self::strftime('%a, %d %b %Y %H:%M:%S %z',      $actual_time)).' (r)',
                '%A,|%d.|%B|%Y' => utf8_encode(self::strftime('%A, %d. %B %Y',  $actual_time)),        // German date
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
            if ( defined('LANGUAGE') ) setlocale(LC_ALL, $locale);
            return $DATE_FORMATS;
        }   // enc function getDateFormats()

        /**
         * returns the default time format:
         *   - checks $_SESSION['TIME_FORMAT'] first;
         *   - checks DEFAULT_TIME_FORMAT constant as next;
         *   - checks $language_time var set in current language file last
         *   - returns 'H:i' by default if none of the above is available
         **/
        public static function getDefaultTimeFormat()
        {
            global $language_time;
            // user defined format
            if ( isset ($_SESSION['CAT_TIME_FORMAT']) ) return $_SESSION['CAT_TIME_FORMAT'];
            // default format
            if ( defined('CAT_DEFAULT_TIME_FORMAT') )   return CAT_DEFAULT_TIME_FORMAT;
            // language file
            if ( isset($language_time) )          return $language_time;
            // global default
            return '%H:%M';
        }   // end function getDefaultTimeFormat()

        /**
         * returns the default date format (short)
         *   - checks $_SESSION['DATE_FORMAT'] first;
         *   - checks DEFAULT_DATE_FORMAT constant next;
         *   - checks $language_date_short var set in current language file last
         *   - returns 'D-M-Y' by default if none of the above is set
         **/
        public static function getDefaultDateFormatShort()
        {
            global $language_date_short;
            // user defined format
            if ( isset ($_SESSION['CAT_DATE_FORMAT']) )       return $_SESSION['CAT_DATE_FORMAT'];
            // default format short
            if ( defined('CAT_DEFAULT_DATE_FORMAT_SHORT') )   return CAT_DEFAULT_DATE_FORMAT_SHORT;
            // language file
            if ( isset($language_date_short) )            return $language_date_short;
            // default format
            if ( defined('CAT_DEFAULT_DATE_FORMAT') )         return CAT_DEFAULT_DATE_FORMAT;
            // global default
            return '%d-%m-%Y';
        }   // end function getDefaultDateFormatShort()

        public static function getDefaultDateFormatLong()
        {
            global $language_date_long;
            $format = NULL;
            if ( defined('CAT_DEFAULT_DATE_FORMAT') ) $format = CAT_DEFAULT_DATE_FORMAT;
            elseif ( isset($language_date_long) ) $format = $language_date_long;
            else                                  $format = '%x';
            $format .= ' ' . self::getDefaultTimeFormat();
            return $format;
        }   // end function getDefaultDateFormatLong()

        /**
        * Locale-formatted strftime using \IntlDateFormatter (PHP 8.1 compatible)
        * This provides a cross-platform alternative to strftime() for when it will be removed from PHP.
        * Note that output can be slightly different between libc sprintf and this function as it is using ICU.
        *
        * Usage:
        * use function \PHP81_BC\strftime;
        * echo strftime('%A %e %B %Y %X', new \DateTime('2021-09-28 00:00:00'), 'fr_FR');
        *
        * Original use:
        * \setlocale('fr_FR.UTF-8', LC_TIME);
        * echo \strftime('%A %e %B %Y %X', strtotime('2021-09-28 00:00:00'));
        *
        * @param  string $format Date format
        * @param  integer|string|DateTime $timestamp Timestamp
        * @return string
        * @author BohwaZ <https://bohwaz.net/>
        */
        public static function strftime(string $format, ?string $timestamp = null, ?string $locale = null): string
        {
            if (null === $timestamp) {
                $timestamp = new \DateTime();
            } elseif (is_numeric($timestamp)) {
                $timestamp = date_create('@' . $timestamp);
                if ($timestamp) {
                    $timestamp->setTimezone(new \DateTimezone(date_default_timezone_get()));
                }
            } elseif (is_string($timestamp)) {
                $timestamp = date_create($timestamp);
            }

            if (!($timestamp instanceof \DateTimeInterface)) {
                throw new \InvalidArgumentException('$timestamp argument is neither a valid UNIX timestamp, a valid date-time string or a DateTime object.');
            }

            $locale = substr((string) $locale, 0, 5);

            $intl_formats = [
                '%a' => 'EEE',	// An abbreviated textual representation of the day	Sun through Sat
                '%A' => 'EEEE',	// A full textual representation of the day	Sunday through Saturday
                '%b' => 'MMM',	// Abbreviated month name, based on the locale	Jan through Dec
                '%B' => 'MMMM',	// Full month name, based on the locale	January through December
                '%h' => 'MMM',	// Abbreviated month name, based on the locale (an alias of %b)	Jan through Dec
            ];

            $intl_formatter = function (\DateTimeInterface $timestamp, string $format) use ($intl_formats, $locale) {
                $tz = $timestamp->getTimezone();
                $date_type = \IntlDateFormatter::FULL;
                $time_type = \IntlDateFormatter::FULL;
                $pattern = '';

                // %c = Preferred date and time stamp based on locale
                // Example: Tue Feb 5 00:45:10 2009 for February 5, 2009 at 12:45:10 AM
                if ($format == '%c') {
                    $date_type = \IntlDateFormatter::LONG;
                    $time_type = \IntlDateFormatter::SHORT;
                }
                // %x = Preferred date representation based on locale, without the time
                // Example: 02/05/09 for February 5, 2009
                elseif ($format == '%x') {
                    $date_type = \IntlDateFormatter::SHORT;
                    $time_type = \IntlDateFormatter::NONE;
                }
                // Localized time format
                elseif ($format == '%X') {
                    $date_type = \IntlDateFormatter::NONE;
                    $time_type = \IntlDateFormatter::MEDIUM;
                } else {
                    $pattern = $intl_formats[$format];
                }

                return (new \IntlDateFormatter($locale, $date_type, $time_type, $tz, null, $pattern))->format($timestamp);
            };

            // Same order as https://www.php.net/manual/en/function.strftime.php
            $translation_table = [
                // Day
                '%a' => $intl_formatter,
                '%A' => $intl_formatter,
                '%d' => 'd',
                '%e' => function ($timestamp) {
                    return sprintf('% 2u', $timestamp->format('j'));
                },
                '%j' => function ($timestamp) {
                    // Day number in year, 001 to 366
                    return sprintf('%03d', $timestamp->format('z')+1);
                },
                '%u' => 'N',
                '%w' => 'w',

                // Week
                '%U' => function ($timestamp) {
                    // Number of weeks between date and first Sunday of year
                    $day = new \DateTime(sprintf('%d-01 Sunday', $timestamp->format('Y')));
                    return sprintf('%02u', 1 + ($timestamp->format('z') - $day->format('z')) / 7);
                },
                '%V' => 'W',
                '%W' => function ($timestamp) {
                    // Number of weeks between date and first Monday of year
                    $day = new \DateTime(sprintf('%d-01 Monday', $timestamp->format('Y')));
                    return sprintf('%02u', 1 + ($timestamp->format('z') - $day->format('z')) / 7);
                },

                // Month
                '%b' => $intl_formatter,
                '%B' => $intl_formatter,
                '%h' => $intl_formatter,
                '%m' => 'm',

                // Year
                '%C' => function ($timestamp) {
                    // Century (-1): 19 for 20th century
                    return floor($timestamp->format('Y') / 100);
                },
                '%g' => function ($timestamp) {
                    return substr($timestamp->format('o'), -2);
                },
                '%G' => 'o',
                '%y' => 'y',
                '%Y' => 'Y',

                // Time
                '%H' => 'H',
                '%k' => function ($timestamp) {
                    return sprintf('% 2u', $timestamp->format('G'));
                },
                '%I' => 'h',
                '%l' => function ($timestamp) {
                    return sprintf('% 2u', $timestamp->format('g'));
                },
                '%M' => 'i',
                '%p' => 'A', // AM PM (this is reversed on purpose!)
                '%P' => 'a', // am pm
                '%r' => 'h:i:s A', // %I:%M:%S %p
                '%R' => 'H:i', // %H:%M
                '%S' => 's',
                '%T' => 'H:i:s', // %H:%M:%S
                '%X' => $intl_formatter, // Preferred time representation based on locale, without the date

                // Timezone
                '%z' => 'O',
                '%Z' => 'T',

                // Time and Date Stamps
                '%c' => $intl_formatter,
                '%D' => 'm/d/Y',
                '%F' => 'Y-m-d',
                '%s' => 'U',
                '%x' => $intl_formatter,
            ];

            $out = preg_replace_callback('/(?<!%)(%[a-zA-Z])/', function ($match) use ($translation_table, $timestamp) {
                if ($match[1] == '%n') {
                    return "\n";
                } elseif ($match[1] == '%t') {
                    return "\t";
                }

                if (!isset($translation_table[$match[1]])) {
                    throw new \InvalidArgumentException(sprintf('Format "%s" is unknown in time format', $match[1]));
                }

                $replace = $translation_table[$match[1]];

                if (is_string($replace)) {
                    return $timestamp->format($replace);
                } else {
                    return $replace($timestamp, $match[1]);
                }
            }, $format);

            $out = str_replace('%%', '%', $out);
            return $out;
        }
    }
}
