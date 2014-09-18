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
    //set_exception_handler(array("CAT_PDOExceptionHandler", "exceptionHandler"));

    class CAT_Helper_DB extends PDO
    {
        public  static $exc_trace = false;

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
    	public function __construct($opt=array())
        {
            self::$prefix = defined('CAT_TABLE_PREFIX') ? CAT_TABLE_PREFIX : '';
            if(!$this->classLoader)
            {
                $this->classLoader = new ClassLoader('Doctrine', dirname(__FILE__).'/../../../modules/lib_doctrine');
                $this->classLoader->register();
            }
            $this->connect($opt);
        }   // end function __construct()

        /**
         *
         * @access public
         * @return
         **/
        public static function getInstance($opt=array())
        {
            if(!self::$instance) self::$instance = new self($opt);
            return self::$instance;
        }   // end function getInstance()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function check()
        {
            if(self::$conn && is_object(self::$conn))
            {
                try {
                    self::$conn->query('SHOW TABLES');
                    return true;
                }
                catch ( Exception $e )
                {
                    return false;
                }
            }
        }   // end function check()

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
    	public static function connect($opt=array())
        {
            self::setExceptionHandler();
            if(!self::$conn)
            {
                $config = new \Doctrine\DBAL\Configuration();
                $config->setSQLLogger(new Doctrine\DBAL\Logging\DebugStack());
                if(!defined('CAT_DB_NAME') && file_exists(dirname(__FILE__).'/../../../config.php'))
                    include dirname(__FILE__).'/../../../config.php';
                $connectionParams = array(
                    'charset'  => 'utf8',
                    'driver'   => 'pdo_mysql',
                    'dbname'   => (isset($opt['DB_NAME'])     ? $opt['DB_NAME']     : CAT_DB_NAME),
                    'host'     => (isset($opt['DB_HOST'])     ? $opt['DB_HOST']     : CAT_DB_HOST),
                    'password' => (isset($opt['DB_PASSWORD']) ? $opt['DB_PASSWORD'] : CAT_DB_PASSWORD),
                    'user'     => (isset($opt['DB_USERNAME']) ? $opt['DB_USERNAME'] : CAT_DB_USERNAME),
                    'port'     => (isset($opt['DB_PORT'])     ? $opt['DB_PORT']     : CAT_DB_PORT    ),
                );
                if(function_exists('xdebug_disable'))
                    xdebug_disable();
                try
                {
                    self::$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
                }
                catch( \PDO\PDOException $e )
                {
                    $this->setError($e->message);
                    CAT_Object::printFatalError($e->message);
                }
            }
            self::restoreExceptionHandler();
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
        public function lastInsertId($seqname = NULL)
        {
            return self::$conn->lastInsertId($seqname);
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
            $prevhandler = set_exception_handler(array("CAT_PDOExceptionHandler", "exceptionHandler"));
            try {
                if(is_array($bind))
                {
                    // allows to replace field names in statements
                    // Example:
                    // SELECT :field: FROM...
                    // array('field'=>'myfield')
                    // => SELECT `myfield` FROM...
                    foreach($bind as $_field => $_value)
                    {
                        if(substr_count($sql,':'.$_field.':'))
                        {
                            $sql = preg_replace(
                                '~(`?)(:'.$_field.':)(`?)~i',
                                '`'.$_value.'`',
                                $sql
                            );
                            unset($bind[$_field]);
                        }
                    }
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
                        $err_msg = sprintf(
                            "[SQL Error] %s<br />\n",
                            $last['sql']
                        );
                        if(is_array($bind))
                            $err_msg .= "\n[PARAMS] "
                                     .  var_export($bind,1);
                        $this->setError($err_msg);
                        if(isset($_REQUEST['_cat_ajax']))
                            return $this->getError();
                        else
                            throw new \PDOException($this->getError());
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

        /**
         * set exception handler to internal one; make sure that this is not
         * done more than once by checking prev handler
         *
         * @access protected
         * @return void
         **/
        protected static function setExceptionHandler()
        {
            $prevhandler = set_exception_handler(array("CAT_PDOExceptionHandler", "exceptionHandler"));
            if(isset($prevhandler[0]) && $prevhandler[0] == 'CAT_PDOExceptionHandler')
                restore_exception_handler();
        }   // end function setExceptionHandler()

        /**
         * reset exception handler to previous one
         *
         * @access protected
         * @return void
         **/
        protected static function restoreExceptionHandler()
        {
            // set dummy handler to get prev
            $prev = set_exception_handler(function(){});
            // reset
            restore_exception_handler();
            // if the previous one was ours...
            if(isset($prev[0]) && $prev[0] == 'CAT_PDOExceptionHandler')
                restore_exception_handler();
        }   // end function restoreExceptionHandler()

        /***********************************************************************
         * old function names wrap new ones
         **/
        public function get_one($sql,$type=PDO::FETCH_ASSOC)
        {
            return $this->query($sql)->fetchColumn();
        }

        public function is_error()  { return $this->isError();      }
        public function get_error() { return $this->getError();     }
        public function insert_id() { return $this->lastInsertId(); }
        public function prompt_on_error($switch=true) { /* no longer supported */ }

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
    public function __call($method, $args)
    {
        return call_user_func_array(array($this, $method), $args);
    }
    /**
     * exception handler; allows to remove paths from error messages and show
     * optional stack trace if CAT_Helper_DB::$trace is true
     **/
    public static function exceptionHandler($exception)
    {

        if(CAT_Helper_DB::$exc_trace === true)
        {
            $traceline = "#%s %s(%s): %s(%s)";
            $msg   = "Uncaught exception '%s' with message '%s'<br />"
                   . "<div style=\"font-size:smaller;width:80%%;margin:5px auto;text-align:left;\">"
                   . "in %s:%s<br />Stack trace:<br />%s<br />"
                   . "thrown in %s on line %s</div>"
                   ;
            $trace = $exception->getTrace();

            foreach ($trace as $key => $stackPoint)
            {
                $trace[$key]['args'] = array_map('gettype', $trace[$key]['args']);
            }
            // build tracelines
            $result = array();
            foreach ($trace as $key => $stackPoint)
            {
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
            $msg = "[DB Exception] %s<br />";
            // filter message
            $message = $exception->getMessage();
            preg_match('~SQLSTATE\[[^\]].+?\]\s+\[[^\]].+?\]\s+(.*)~i', $message, $match);
            $msg     = sprintf(
                $msg,
                ( isset($match[1]) ? $match[1] : $message )
            );
        }

        // log or echo as you please
        CAT_Object::printFatalError($msg);
    }
}