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
 *   @copyright       2013, 2014, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

use Doctrine\Common\ClassLoader;
require dirname(__FILE__).'/../../../modules/lib_doctrine/Doctrine/Common/ClassLoader.php';

if ( !class_exists( 'CAT_Helper_DB' ) )
{
    set_exception_handler(array("CAT_PDOExceptionHandler", "exceptionHandler"));

    class CAT_Helper_DB extends PDO
    {
        public  static $trace    = false;

        private static $instance = NULL;
        private static $conn     = NULL;
        private static $prefix   = NULL;
        private static $qb       = NULL;

        private $lasterror       = NULL;
        private $classLoader     = NULL;

        /**
         * constructor; initializes Doctrine ClassLoader and sets up a database
         * connection
         *
         * @access public
         * @return void
         **/
    	public function __construct()
        {
            self::$prefix = defined('CAT_TABLE_PREFIX') ? CAT_TABLE_PREFIX : '';
            if(!$this->classLoader)
            {
                $this->classLoader = new ClassLoader('Doctrine', dirname(__FILE__).'/../../../modules/lib_doctrine');
                $this->classLoader->register();
            }
            $this->connect();
        }   // end function __construct()

        /**
         *
         * @access public
         * @return
         **/
        public static function getInstance()
        {
            if(!self::$instance) self::$instance = new self();
            return self::$instance;
        }   // end function getInstance()
        
        /**
         * accessor to current connection object
         **/
        public static function conn()
        {
            return self::$conn;
        }   // end function conn()

        /**
         * accessor to currently used table prefix
         **/
        public static function prefix()
        {
            return self::$prefix;
        }   // end function prefix()

        /**
         * accessor to query builder
         **/
        public static function qb()
        {
            if(!is_object(self::$qb))
                self::$qb = self::$conn->createQueryBuilder();
            // reset
            self::$qb->resetQueryParts();
            return self::$qb;
        }   // end function qb()

        /**
         * connect to the database; returns Doctrine connection
         *
         * @access public
         * @return object
         **/
    	public static function connect()
        {
            if(!self::$conn)
            {
                $config = new \Doctrine\DBAL\Configuration();
                $config->setSQLLogger(new Doctrine\DBAL\Logging\DebugStack());
                if(!defined('CAT_DB_NAME'))
                    include dirname(__FILE__).'/../../../config.php';
                $connectionParams = array(
                    'charset'  => 'utf8',
                    'dbname'   => CAT_DB_NAME,
                    'driver'   => 'pdo_mysql',
                    'host'     => CAT_DB_HOST,
                    'password' => CAT_DB_PASSWORD,
                    'user'     => CAT_DB_USERNAME,
                );
                if(CAT_DB_PORT !== '3306') $connectionParams['port'] = CAT_DB_PORT;
                if(function_exists('xdebug_disable'))
                    xdebug_disable();
                try
                {
                self::$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
            }
                catch( \PDO\PDOException $e )
                {
                    CAT_Object::printFatalError($e->message);
                }
            }
            return self::$conn;
        }   // end function connect()

        /**
         * unsets connection object
         *
         * @access protected
         * @return void
         **/
    	final protected static function disconnect()
        {
            self::$conn = NULL;
        }   // end function disconnect()

        /**
         *
         * @access public
         * @return
         **/
        public function lastInsertId()
        {
            return self::$conn->lastInsertId();
        }   // end function lastInsertId()
        
        public function prepare($statement,$driver_options=array())
        {
            $statement = str_replace(':prefix:',self::$prefix,$statement);
            return self::$conn->prepare($statement,$driver_options);
        }

        /**
         * simple query; simple but has several drawbacks
         *
         * @params string $SQL
         * @return object
         **/
    	public function query($sql,$bind=array())
        {
            $this->setError(NULL);
            try {
                if(is_array($bind))
                {
                    $stmt = $this->prepare($sql);
                    $stmt->execute($bind);
                }
                else
                {
                    $sql  = str_replace(':prefix:',self::$prefix,$sql);
                    $stmt = self::$conn->query($sql);
                }
                return new CAT_PDOStatementDecorator($stmt);
            } catch ( \Doctrine\DBAL\DBALException $e ) {
                $error = self::$conn->errorInfo();
                $this->setError(sprintf(
                    '[DBAL Error #%d] %s<br /><b>Executed Query:</b><br /><i>%s</i><br />',
					$error[1],
					$error[2],
					$sql
                ));
            } catch ( \PDOException $e ) {
                $error = self::$conn->errorInfo();
                $this->setError(sprintf(
                    '[PDO Error #%d] %s<br /><b>Executed Query:</b><br /><i>%s</i><br />',
					$error[1],
					$error[2],
					$sql
                ));
            }
            if($this->isError())
            {
                $logger = self::$conn->getConfiguration()->getSQLLogger();
                if(count($logger->queries))
                {
                    $last = array_pop($logger->queries);
                    if(is_array($last) && count($last))
                    {
                        $this->setError(sprintf(
                            "SQL Error\n".
                            "    [SQL]      %s\n".
                            "    [PARAMS]   %s\n".
                            "    [TYPES]    %s\n".
                            "    [TIME(MS)] %s",
                            $last['sql'],
                            implode(', ',$last['params']),
                            implode(', ',$last['types']),
                            $last['executionMS']
                        ));
                        throw new Exception($this->getError());
                    }
                }
            }
            return false;
        }   // end function query()




        /**
         * check for DB error
         *
         * @access public
         * @return boolean
         **/
        public function isError()
        {
            return ( $this->lasterror ) ? true : false;
        }   // end function isError()
        

        /**
         * get last DB error
         *
         * @access public
         * @return string
         **/
        public function getError()
        {
            return $this->lasterror;
        }   // end function getError()

        /**
         *
         * @access public
         * @return
         **/
        public function resetError()
        {
            $this->lasterror = NULL;
        }   // end function resetError()

        /**
         * set error message
         *
         * @access protected
         * @param  string    error message
         * @return void
         **/
    	protected function setError($error = '')
        {
            $this->lasterror = $error;
        }   // end function setError

    }
}

/**
 * decorates PDOStatement object with old WB methods numRows() and fetchRow()
 * for backward compatibility
 **/
class CAT_PDOStatementDecorator
{
    private $pdo_stmt = NULL;
    public function __construct($stmt)
    {
        $this->pdo_stmt = $stmt;
    }
    // route all other method calls directly to PDOStatement
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->pdo_stmt, $method), $args);
    }
    public function numRows()
    {
        return $this->pdo_stmt->rowCount();
    }
    public function fetchRow($type=PDO::FETCH_ASSOC)
    {
        return $this->pdo_stmt->fetch();
    }
}

class CAT_PDOExceptionHandler
{
    /**
     * exception handler; allows to remove paths from error messages and show
     * optional stack trace if CAT_Helper_DB::$trace is true
     **/
    function exceptionHandler($exception) {

        if(CAT_Helper_DB::$trace)
        {
            $traceline = "#%s %s(%s): %s(%s)";
            $msg   = "Uncaught exception '%s' with message '%s'<br />"
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
            // template
            $msg = "[DB] %s<br /><span style=\"font-size:smaller\">in %s:%s</span><br />";
            // filter message
            $message = $exception->getMessage();
            preg_match('~SQLSTATE\[[^\]].+?\]\s+\[[^\]].+?\]\s+(.*)~i', $message, $match);
            // filter path from file
            $file    = $exception->getFile();
            $file    = str_replace(
                array(
                    CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/lib_doctrine'),
                    CAT_Helper_Directory::sanitizePath(CAT_PATH)
                ),
                array(
                    '[path to]',
                    '[path to]'
                ),
                CAT_Helper_Directory::sanitizePath($file)
            );
            $msg     = sprintf(
                $msg,
                $match[1],
                $file,
                $exception->getLine()
            );
        }

        // log or echo as you please
        CAT_Object::printFatalError($msg);
    }
}
