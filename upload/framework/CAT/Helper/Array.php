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

if (defined("CAT_PATH")) {
    include CAT_PATH . "/framework/class.secure.php";
} else {
    $root = "../";
    $level = 1;
    while ($level < 10 && !file_exists($root . "framework/class.secure.php")) {
        $root .= "../";
        $level += 1;
    }
    if (file_exists($root . "framework/class.secure.php")) {
        include $root . "framework/class.secure.php";
    } else {
        trigger_error(
            sprintf(
                "[ <b>%s</b> ] Can't include class.secure.php!",
                $_SERVER["SCRIPT_NAME"]
            ),
            E_USER_ERROR
        );
    }
}

if (!class_exists("CAT_Helper_Array")) {
    if (!class_exists("CAT_Object", false)) {
        @include dirname(__FILE__) . "/../Object.php";
    }

    class CAT_Helper_Array extends CAT_Object
    {
        private static $Needle = null;
        private static $Key = null;
        protected $_config = ["loglevel" => 8];
        private static $instance;

        public function __call($method, $args)
        {
            if (!isset($this) || !is_object($this)) {
                return false;
            }
            if (method_exists($this, $method)) {
                return call_user_func_array([$this, $method], $args);
            }
        }

        public static function getInstance()
        {
            if (!self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        private static function filter_callback($v)
        {
            return !isset($v[self::$Key]) || $v[self::$Key] !== self::$Needle;
        }

        /**
         * allows to reorder the $_FILES array if the 'multiple' attribute
         * was set on the file upload field
         */
        public function ArrayDiverse($vector)
        {
            $result = [];
            foreach ($vector as $key1 => $value1) {
                foreach ($value1 as $key2 => $value2) {
                    $result[$key2][$key1] = $value2;
                };
            }
            return $result;
        }

        /**
         * encode all entries of an multidimensional array into utf8
         * PHP 8.2 compatible replacement for utf8_encode()
         */
        public static function ArrayEncodeUTF8($dat)
        {
            if (is_string($dat)) {
                return mb_detect_encoding($dat, "UTF-8", true)
                    ? $dat
                    : mb_convert_encoding($dat, "UTF-8", "ISO-8859-1");
            }

            if (!is_array($dat)) {
                return $dat;
            }

            $ret = [];
            foreach ($dat as $i => $d) {
                $ret[$i] = self::ArrayEncodeUTF8($d);
            }
            return $ret;
        }

        public static function ArrayFilterByKey(&$array, $key, $value)
        {
            $result = [];
            foreach ($array as $k => $elem) {
                if (isset($elem[$key]) && $elem[$key] == $value) {
                    $result[] = $array[$k];
                    unset($array[$k]);
                }
            }
            return $result;
        }

        public static function ArrayRemove($Needle, &$Haystack, $NeedleKey = "")
        {
            if (!is_array($Haystack)) {
                return false;
            }
            reset($Haystack);
            self::$Needle = $Needle;
            self::$Key = $NeedleKey;
            $Haystack = array_filter($Haystack, "self::filter_callback");
        }

        /**
         * sort an array
         */
        public static function ArraySort(
            $array,
            $index,
            $order = "asc",
            $natsort = false,
            $case_sensitive = false
        ) {
            if (is_array($array) && count($array) > 0) {
                $temp = [];
                foreach (array_keys($array) as $key) {
                    $temp[$key] = $array[$key][$index] ?? null;
                }

                if (!$natsort) {
                    $order === "asc" ? asort($temp) : arsort($temp);
                } else {
                    $case_sensitive ? natsort($temp) : natcasesort($temp);
                    if ($order !== "asc") {
                        $temp = array_reverse($temp, true);
                    }
                }

                $sorted = [];
                foreach (array_keys($temp) as $key) {
                    $sorted[$key] = $array[$key];
                }
                return $sorted;
            }
            return $array;
        }

        public static function ArraySearchRecursive(
            $Needle,
            $Haystack,
            $NeedleKey = "",
            $Strict = false,
            $Path = []
        ) {
            if (!is_array($Haystack)) {
                return false;
            }

            foreach ($Haystack as $Key => $Val) {
                if (is_array($Val)) {
                    $SubPath = self::ArraySearchRecursive(
                        $Needle,
                        $Val,
                        $NeedleKey,
                        $Strict,
                        $Path
                    );
                    if ($SubPath !== false) {
                        return array_merge($Path, [$Key], $SubPath);
                    }
                } elseif (
                    (!$Strict &&
                        $Val == $Needle &&
                        $Key == ($NeedleKey ?: $Key)) ||
                    ($Strict &&
                        $Val === $Needle &&
                        $Key == ($NeedleKey ?: $Key))
                ) {
                    return array_merge($Path, [$Key]);
                }
            }
            return false;
        }

        public static function ArrayUniqueRecursive($array)
        {
            $set = [];
            $out = [];

            foreach ($array as $key => $val) {
                if (is_array($val)) {
                    $out[$key] = self::ArrayUniqueRecursive($val);
                } elseif (!isset($set[$val])) {
                    $out[$key] = $val;
                    $set[$val] = true;
                } else {
                    $out[$key] = $val;
                }
            }
            return $out;
        }
    }
}
