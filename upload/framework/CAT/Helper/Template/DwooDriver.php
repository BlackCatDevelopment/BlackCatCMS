<?php
@ini_set("display_errors", "1");
@ini_set("display_startup_errors", "1");
error_reporting(E_ALL);

register_shutdown_function(function () {
    $e = error_get_last();
    if (
        $e &&
        in_array($e["type"], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])
    ) {
        if (!headers_sent()) {
            http_response_code(500);
            header("Content-Type: text/plain; charset=utf-8");
        }
        echo "FATAL: {$e["message"]}\n{$e["file"]}:{$e["line"]}\n";
    }
});
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
 *   @link            http://www.blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if (!class_exists("Dwoo", false)) {
    include CAT_PATH . "/modules/lib_dwoo/dwoo/dwooAutoload.php";
}

if (!class_exists("CAT_Helper_Template_DwooDriver", false)) {
    class CAT_Helper_Template_DwooDriver extends Dwoo
    {
        protected $debuglevel = CAT_Helper_KLogger::CRIT;
        public $_config = [
            "loglevel" => CAT_Helper_KLogger::CRIT,
            "show_paths_on_error" => true,
        ];
        public $workdir = null;
        public $path = null;
        public $fallback_path = null;
        public static $_globals = [];
        protected $logger = null;
        public array $paths = [];

        public function __construct()
        {
            $cache_path = CAT_PATH . "/temp/cache";
            if (!file_exists($cache_path)) {
                mkdir($cache_path, 0755, true);
            }
            $compiled_path = CAT_PATH . "/temp/compiled";
            if (!file_exists($compiled_path)) {
                mkdir($compiled_path, 0755, true);
            }
            parent::__construct($compiled_path, $cache_path);
            // we need our own logger instance here as the driver does not
            // inherit from CAT_Object
            if (!class_exists("CAT_Helper_KLogger", false)) {
                include dirname(__FILE__) .
                    "/../../../framework/CAT/Helper/KLogger.php";
            }
            $this->logger = new CAT_Helper_KLogger(
                CAT_PATH . "/temp/logs",
                $this->debuglevel
            );
        } // end function __construct()

        public function output(
            $_tpl,
            $data = [],
            Dwoo_ICompiler $compiler = null
        ) {
            echo $this->get($_tpl, $data, $compiler);
        }

        /**
         * this overrides and extends the original get() method Dwoo provides:
         * - use the template search and fallback paths
         *
         * @access public
         * @param  see original Dwoo docs
         * @return see original Dwoo docs
         *
         **/
        public function get(
            $_tpl,
            $data = [],
            $_compiler = null,
            $_output = false
        ) {
            // add globals to $data array
            if (is_array(self::$_globals) && count(self::$_globals)) {
                if (is_array($data)) {
                    $this->logger->LogDebug("Adding globals to data");
                    $data = array_merge(self::$_globals, $data);
                } else {
                    $data = self::$_globals;
                }
            }
            if (!is_object($_tpl)) {
                if (!file_exists($_tpl) || is_dir($_tpl)) {
                    global $parser;
                    $file = $parser->findTemplate($_tpl);
                    $this->logger->LogDebug(
                        sprintf("Template file [%s]", $file)
                    );
                    if ($file) {
                        return parent::get(
                            realpath($file),
                            $data,
                            $_compiler,
                            $_output
                        );
                    } else {
                        $this->logger->LogCrit("No template file!");
                    }
                } else {
                    return parent::get($_tpl, $data, $_compiler, $_output);
                }
            } else {
                return parent::get($_tpl, $data, $_compiler, $_output);
            }
        } // end function get()
    } // end class CAT_Helper_Template_DwooDriver
}
