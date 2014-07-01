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
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if (defined('CAT_PATH')) {	
	include(CAT_PATH.'/framework/class.secure.php'); 
} else {
	$root = "../";
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) { 
		include($root.'/framework/class.secure.php'); 
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}

global $database;

use Doctrine\Common\ClassLoader;
require dirname(__FILE__).'/../modules/lib_doctrine/Doctrine/Common/ClassLoader.php';

if (!class_exists('database', false))
{
    class database
    {

        private $classLoader     = NULL;
        private static $conn     = NULL;
        private $lasterror       = NULL;
        private $prompt_on_error = false;

        /**
         * constructor; initializes Doctrine ClassLoader and sets up a database
         * connection
         *
         * @access public
         * @return void
         **/
    	public function __construct()
        {
            if(!$this->classLoader)
            {
                $this->classLoader = new ClassLoader('Doctrine', dirname(__FILE__).'/../modules/lib_doctrine');
                $this->classLoader->register();
            }
            $this->connect();
        }   // end function __construct()

        /***********************************************************************
         * functions needed to keep the old API
         **********************************************************************/

        /**
         * connect to the database; returns Doctrine connection
         *
         * @access public
         * @return object
         **/
    	public function connect()
        {
            if(!self::$conn)
            {
                $config = new \Doctrine\DBAL\Configuration();
                $config->setSQLLogger(new Doctrine\DBAL\Logging\DebugStack());
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
    	final protected function disconnect()
        {
            self::$conn = NULL;
        }   // end function disconnect()

        /**
         * returns last error message
         *
         * @access public
         * @return string
         **/
    	public function get_error()
        {
            return $this->lasterror;
        }   // end function get_error()

        /**
         * Execute query and return the first column of the first row of
         * the result; returns NULL if no result was fetched
         *
         * @access public
         * @param  string  $sql
         * @param  flag    $type
         * @return mixed
         **/
    	public function get_one($sql,$type=PDO::FETCH_ASSOC)
        {
            $q = $this->query($sql);
            if($q && $q->rowCount())
            {
                $row = $q->fetch($type);
    			if($type==2 || preg_match('~_assoc$~i',$type))
                {
    				$temp = array_values($row);
    				return $temp[0];
    			} else {
    				return $row[0];
    			}
            }
            return NULL;
        }   // end function get_one()

        /**
         * returns last insert ID
         *
         * @access public
         * @return mixed
         **/
    	public function insert_id()
        {
            return self::$conn->lastInsertId();
        }   // end function insert_id()

        /**
         * check if there is an error
         *
         * @access public
         * @return boolean
         **/
        public function is_error()
        {
            return ( $this->lasterror ) ? true : false;
        }   // end function is_error()

        /**
         * allows to enable trigger_error() for database statements
         *
         * @access public
         * @param  boolean
         * @return void
         **/
    	public function prompt_on_error($switch=true)
        {
            $this->prompt_on_error = $switch;
        }   // end function prompt_on_error()

        /**
         * simple query; simple but has several drawbacks
         *
         * @params string $SQL
         * @return object
         **/
    	public function query($sql)
        {
            $this->setError(NULL);
            try {
                $stmt = self::$conn->query($sql);
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
            if($this->is_error() && $this->prompt_on_error)
            {
                $logger = self::$conn->getConfiguration()->getSQLLogger();
                if(count($logger->queries))
                {
                    $last = array_pop($logger->queries);
                    trigger_error(sprintf(
                        "SQL Error\n".
                        "    [SQL]      %s\n".
                        "    [PARAMS]   %s\n".
                        "    [TYPES]    %s\n".
                        "    [TIME(MS)] %s",
                        $last['sql'], $last['params'], $last['types'], $last['executionMS']
                    ), E_USER_ERROR);
                }
            }

        }   // end function query()

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

    }   // ----- end class database -----
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
