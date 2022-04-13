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
 *   @link            http://www.blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
if (!class_exists("Twig", false)) {
  require_once CAT_PATH . "/modules/lib_twig/vendor/autoload.php";
}

if (!class_exists("CAT_Helper_Template_TwigDriver", false)) {
  class CAT_Helper_Template_TwigDriver extends \Twig\Environment
  {
    protected $debuglevel = CAT_Helper_KLogger::DEBUG;
    public $_config = [
      "loglevel" => CAT_Helper_KLogger::DEBUG,
      "show_paths_on_error" => true,
    ];
    public $loader = null;
    public $workdir = null;
    public $path = null;
    public $fallback_path = null;
    public static $_globals = [];
    protected $logger = null;

    public function __construct()
    {
      $cache_path = CAT_PATH . "/temp/cache";
      if (!file_exists($cache_path)) {
        mkdir($cache_path, 0755, true);
      }

      $this->loader = new \Twig\Loader\FilesystemLoader(
        CAT_PATH . "/templates/" . DEFAULT_TEMPLATE . "/templates/default"
        # . CAT_Registry::get("DEFAULT_THEME_VARIANT")
      );
      #->addPath($templateDir3);
      parent::__construct($this->loader /*, ["cache" => $cache_path]*/);
      #parent::__construct($compiled_path, $cache_path);
      // add custom extensions

      $this->addExtension(
        new Twig_GetBlackCatPlugins(
          CAT_Helper_Directory::sanitizePath(
            CAT_PATH . "/modules/lib_twig/Plugins"
          )
        )
      );
      $lexer = new \Twig\Lexer($this, [
        "tag_comment" => ["{*", "*}"],
        "tag_block" => ["{", "}"],
        "tag_variable" => ['{$', "}"],
      ]);
      $this->setLexer($lexer);
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

    public function output($_tpl, $data = [])
    {
      global $parser;
      $file = $parser->findTemplate($_tpl);

      $this->loader->setPaths([
        $this->paths["current"],
        $this->paths[CAT_Backend::isBackend() ? "backend" : "frontend"],
      ]);

      echo $this->render(pathinfo($file, PATHINFO_BASENAME), $data);
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
    public function get($_tpl, $data = [], $_compiler = null, $_output = false)
    {
      $this->loader->setPaths([
        $this->paths["current"],
        $this->paths[CAT_Backend::isBackend() ? "backend" : "frontend"],
      ]);

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

          $this->logger->LogDebug(sprintf("Template file [%s]", $file));
          if ($file) {
            return $this->render(pathinfo($file, PATHINFO_BASENAME), $data);
          } else {
            $this->logger->LogCrit("No template file!");
          }
        } else {
          return $this->render($_tpl, $data);
        }
      } else {
        return $this->render($_tpl, $data);
      }
    } // end function get()
  } // end class CAT_Helper_Template_TwigDriver

  class Twig_GetBlackCatPlugins extends \Twig\Extension\AbstractExtension
  {
    private static $loadedExtensions = [];
    private static $defaultMethods = [
      "getFunctions",
      "getTokenParsers",
      "getNodeVisitors",
      "getFilters",
      "getTests",
      "getOperators",
      "getName",
    ];

    public function __construct(string $extPath = "")
    {
      if (count(static::$loadedExtensions) == 0) {
        $extensionsPath = realpath($extPath);

        if (!$extensionsPath) {
          throw new Exception(
            "Plugin directory does not exist or can not be read : " .
              $extensionsPath
          );
        }
        if (is_dir($extensionsPath)) {
          foreach (
            CAT_Helper_Directory::getInstance()->getPHPFiles($extensionsPath)
            as $extension
          ) {
            if (
              pathinfo($extension, PATHINFO_FILENAME) != "index" &&
              (include_once $extension)
            ) {
              $class = pathinfo($extension, PATHINFO_FILENAME);

              foreach (get_class_methods($class) as $method) {
                if (!in_array($method, static::$defaultMethods)) {
                  static::$loadedExtensions[] = new \Twig\TwigFunction(
                    $method,
                    [$class, $method]
                  );
                }
              }
            }
          }
        }
      }
    }

    public function getFunctions()
    {
      return static::$loadedExtensions;
    }
    // function checksection($section_id)
    // {
    //   global $page_id;
    //   return CAT_Sections::section_is_active($section_id);
    // }
    // function cat_url()
    // {
    //   return CAT_Helper_Validate::getURI(CAT_URL);
    // }
  } // end class Twig_GetBlackCatPlugins
}
