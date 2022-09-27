<?php

if(!class_exists('CAT_Helper_Template_PHPDriver',false) )
{
    class CAT_Helper_Template_PHPDriver
    {

        protected $debuglevel      = CAT_Helper_KLogger::CRIT;
        public    $_config         = array( 'loglevel' => CAT_Helper_KLogger::CRIT, 'show_paths_on_error' => true );
        public    $workdir         = NULL;
        public    $path            = NULL;
        public    $fallback_path   = NULL;
        public    static $_globals = array();
        protected $logger          = NULL;

        public function __construct()
        {
            if(!class_exists('CAT_Helper_KLogger',false)) {
                include dirname(__FILE__).'/../../../framework/CAT/Helper/KLogger.php';
    		}
            $this->logger = new CAT_Helper_KLogger(CAT_PATH.'/temp/logs', $this->debuglevel);
        }   // end function __construct()

        public function output(string $tpl, ?array $data=[]) : void
        {
            echo $this->get($tpl,$data);
        }

        /**
         * parse template
         *
         * @access public
         * @param  string $tpl
         * @param  array  $replacements
         * @return string
         **/
        public function get(string $tpl, ?array $data=[]) : string
        {
            $data = $this->getData($data);                 // merge data
            $t = CAT_Object::parser()->findTemplate($tpl); // find template
            ob_start();
                include $t;
                $output = ob_get_contents();
            ob_end_clean();
            // handle {translate()}
            $trans_regexp = "#{(lang|translate)\(\'([^\{\'].*?)\'\)\}#im";
            $string       = preg_replace_callback(
                $trans_regexp,
                function($match) { return $this->translate($match[2]); },
                $output
            );
            return $string;
        }

        /**
         * merge passed data with global data
         *
         * @access private
         * @param  array   $data
         * @return array
         **/
        private function getData(?array $data=[]) : array
        {
            if(is_array(self::$_globals) && count(self::$_globals)) {
                if(is_array($data)) {
                    $this->logger->LogDebug('Adding globals to data');
                    return array_merge(self::$_globals, $data);
                } else {
                    return self::$_globals;
                }
            }
            return [];
        }

        private function translate($msg) : string
        {
echo "FILE [",__FILE__,"] FUNC [",__FUNCTION__,"] LINE [",__LINE__,"]<br /><textarea style=\"width:100%;height:200px;color:#000;background-color:#fff;\">";
print_r($msg);
echo "</textarea><br />";
            return CAT_Object::lang()->translate($msg);
        }
    }
}