<?php

namespace wblib;

require dirname(__FILE__).'/Analog.php';

function wblib_init_3rdparty($path,$classname,$level=\Analog::URGENT)
{
    // remove namespace
    if(substr_count($classname,'\\'))
        $classname = substr($classname,(strrpos($classname, '\\')+1));

    if(file_exists($path.$classname.'.debug.log'))
        unlink($path.$classname.'.debug.log');

    $handler = array(
        \Analog::URGENT => \Analog\Handler\File::init($path.$classname.'.errors.log'),
        \Analog::ALERT => \Analog\Handler\File::init($path.$classname.'.errors.log'),
        \Analog::CRITICAL => \Analog\Handler\File::init($path.$classname.'.errors.log'),
        \Analog::ERROR => \Analog\Handler\File::init($path.$classname.'.errors.log'),
        \Analog::WARNING => \Analog\Handler\File::init($path.$classname.'.warning.log'),
        \Analog::NOTICE => \Analog\Handler\File::init($path.$classname.'.warning.log'),
        \Analog::INFO => \Analog\Handler\File::init($path.$classname.'.warning.log'),
    );

    if( $level === \Analog::DEBUG ) {
/*
        $handler[\Analog::DEBUG] = \Analog\Handler\LevelBuffer::init(
                    \Analog\Handler\File::init(
                        $path.$classname.'.debug.log'
                    ),
                    \Analog::DEBUG
                );
*/
        $handler[\Analog::DEBUG] = \Analog\Handler\File::init($path.$classname.'.debug.log');
    }

    \Analog::handler(\Analog\Handler\Multi::init($handler));
}   // end function wblib_init_3rdparty()