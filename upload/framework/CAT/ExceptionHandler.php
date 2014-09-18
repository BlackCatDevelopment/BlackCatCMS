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
 *   @copyright       2014, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

class CAT_ExceptionHandler
{
    public function __call($method, $args)
    {
        return call_user_func_array(array($this, $method), $args);
    }
    /**
     * exception handler; allows to remove paths from error messages and show
     * optional stack trace 
     **/
    public static function exceptionHandler($exception)
    {

        $exc_class = get_class($exception);

        try {
            $logger = CAT_Helper_KLogger::instance(CAT_PATH.'/temp/logs',2);
            $logger->logFatal(sprintf(
                'Exception with message [%s] emitted in [%s] line [%s]',
                $exception->getMessage(),$exception->getFile(),$exception->getLine()
            ));
        } catch ( Exception $e ) {}

        if(isset($exc_class::$exc_trace) && $exc_class::$exc_trace === true)
        {
            $traceline = "#%s %s(%s): %s(%s)";
            $msg       = "Uncaught exception '%s' with message '%s'<br />"
                       . "<div style=\"font-size:smaller;width:80%%;margin:5px auto;text-align:left;\">"
                       . "in %s:%s<br />Stack trace:<br />%s<br />"
                       . "thrown in %s on line %s</div>"
                       ;
            $trace = $exception->getTrace();
            foreach ($trace as $key => $stackPoint) {
                $trace[$key]['args'] = array_map('gettype', $trace[$key]['args']);
            }
            // build tracelines
            $result = array();
            foreach ($trace as $key => $stackPoint) {
                $result[] = sprintf(
                    $traceline,
                    $key,
                    ( isset($stackPoint['file']) ? $stackPoint['file'] : '-' ),
                    ( isset($stackPoint['line']) ? $stackPoint['line'] : '-' ),
                    $stackPoint['function'],
                    implode(', ', $stackPoint['args'])
                );
            }
            // trace always ends with {main}
            $result[] = '#' . ++$key . ' {main}';
            // write tracelines into main template
            $msg = sprintf(
                $msg,
                get_class($exception),
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
                implode("<br />", $result),
                $exception->getFile(),
                $exception->getLine()
            );
        }
        else
        {
            // filter message
            $message = $exception->getMessage();
            $message = str_replace(
                array(
                    CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/lib_doctrine'),
                ),
                array(
                    '[path to]',
                ),
                $message
            );
            $msg = "[$exc_class] $message";
        }

        // log or echo as you please
        CAT_Object::printFatalError($msg);
    }
}