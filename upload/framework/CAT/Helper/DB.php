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
    class CAT_Helper_DB extends PDO
    {

        private static $instance = NULL;
        private static $conn     = NULL;
        private static $prefix   = NULL;

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
                self::$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
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
            } catch ( Doctrine\DBAL\DBALException $e ) {
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
                        trigger_error(sprintf(
                            "SQL Error\n".
                            "    [SQL]      %s\n".
                            "    [PARAMS]   %s\n".
                            "    [TYPES]    %s\n".
                            "    [TIME(MS)] %s",
                            $last['sql'],
                            implode(', ',$last['params']),
                            implode(', ',$last['types']),
                            $last['executionMS']
                        ), E_USER_ERROR);
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
