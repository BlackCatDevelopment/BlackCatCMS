<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'Dwoo' . DIRECTORY_SEPARATOR . 'Core.php';
defined('DWOO_DIRECTORY') || define('DWOO_DIRECTORY',dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR);

if(!function_exists('dwooAutoload')) {
    function dwooAutoload($class)
    {
    	if (substr($class, 0, 5) === 'Dwoo\\') {
            $file = DWOO_DIRECTORY . strtr($class, '\\', DIRECTORY_SEPARATOR).'.php';
            if(file_exists($file)) {
    		    include $file;
            }
    	}
    }

    spl_autoload_register('dwooAutoload');
}