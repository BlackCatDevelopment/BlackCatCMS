<?php

/**
 *          _     _  _ _     ______
 *         | |   | |(_) |   (_____ \
 *    _ _ _| |__ | | _| |__   ____) )
 *   | | | |  _ \| || |  _ \ / ____/
 *   | | | | |_) ) || | |_) ) (_____
 *    \___/|____/ \_)_|____/|_______)
 *
 *   @category     wblib2
 *   @package      wbQuery
 *   @author       BlackBird Webprogrammierung
 *   @copyright    (c) 2014 BlackBird Webprogrammierung
 *   @license      GNU LESSER GENERAL PUBLIC LICENSE Version 3
 *
 **/

namespace wblib;

/**
 * SQL abstraction class
 *
 * @category   wblib2
 * @package    wbQuery
 * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
 * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
 */
if ( ! class_exists( '\wblib\wbQuery', false ) )
{
    class wbQuery {

        /**
         * array of named instances
         **/
        public static $instances  = array();
        /**
         * logger
         **/
        private static $analog    = NULL;
        /**
         * log level
         **/
        public  static $loglevel  = 4;
        /**
         * indentation
         **/
        public  static $spaces    = 0;
        /**
         * default driver
         **/
        public static $driver     = 'MySQL';
        /**
         * array of options
         **/
        public        $options    = array();
        /**
         *
         **/
        private $_lastStatement   = NULL;
        /**
         *
         **/
        private $hashes           = array();

        // private to make sure that constructor can only be called
        // using getInstance()
        private function __construct() {

        }    // end function __construct()

        // no cloning!
        private function __clone() {}

        /**
         * Create an instance (i.e. a database connection)
         *
         * First argument may be an array of options that will be passed to
         * the connect() method of the driver.
         *
         * If you need to have more than one connection, you may pass an
         * optional connection name (default: 'default')
         *
         * 'connection_name' => 'myconnection'
         *
         * use with BlackCat CMS v1.2 and higher:
         * $dbh = \wblib\wbQuery::getInstance(CAT_Helper_DB::getConfig());
         *
         * @access public
         * @param  array   $options    - OPTIONAL; options to be passed to connect()
         * @return object
         **/
        public static function getInstance(array $options = array())
        {
            // convert config data for well known CMS
            if(defined('CAT_PATH'))
            {
                foreach(array_keys($options) as $key)
                {
                    $new = str_replace('DB_','',$key);
                    if($new == $key) continue;
                    if(strtolower($new) == 'username') $new = 'user';
                    if(strtolower($new) == 'password') $new = 'pass';
                    if(strtolower($new) == 'name')     $new = 'dbname';
                    $options[strtolower($new)] = $options[$key];
                    unset($options[$key]);
                }
                $options['prefix'] = CAT_TABLE_PREFIX;
            }
            $connection = isset($options['connection_name'])
                        ? $options['connection_name']
                        : 'default';
            if (!array_key_exists($connection,self::$instances))
            {
                self::log(sprintf('creating new instance with name [%s]',$connection),7);
                self::log(print_r($options,1),7);
                self::$instances[$connection] = self::__connect($options);
            }
            return self::$instances[$connection];
        }   // end function getInstance()

        /**
         * private method to establish a database connection, using the
         * appropriate driver
         *
         * @access private
         * @param  array   $options
         * @return object
         **/
        private static function __connect( $options )
        {
            $driver = isset( $options['driver'] )
                    ? $options['driver']
                    : self::$driver;
            try {
                $classname = '\\wblib\\'.$driver;
                return new $classname($options);
            } catch (\wblib\wbQueryException $e) {
                self::log($e->getMessage);
                echo $e->getMessage();
            } catch (\PDO\PDOException $e) {
                self::log($e->getMessage);
                echo $e->getMessage();
            }
        }   // end function __connect()

        /**
         * accessor to Analog (if installed)
         *
         * Note: Log messages are ignored if no Analog is available!
         *
         * @access protected
         * @param  string   $message - log message
         * @param  integer  $level   - log level; default: 3 (error)
         * @return void
         **/
        public static function log($message, $level = 3)
        {
            $class = get_called_class();
            if($class != 'wblib\wbQuery') return;
            if($level>$class::$loglevel)  return;
            if( !self::$analog && !self::$analog == -1)
            {
                if(file_exists(dirname(__FILE__).'/3rdparty/Analog/wblib.init.inc.php'))
                {
                    include_once(dirname(__FILE__).'/3rdparty/Analog/wblib.init.inc.php');
                    wblib_init_3rdparty(dirname(__FILE__).'/debug/','wbQuery',$class::$loglevel);
                    self::$analog = true;
                }
                else
                {
                    self::$analog = -1;
                }
            }
            if ( self::$analog !== -1 )
            {
                if(substr($message,0,1)=='<')
                    self::$spaces--;
                self::$spaces = ( self::$spaces > 0 ? self::$spaces : 0 );
                $line = str_repeat('    ',self::$spaces).$message;
                if(substr($message,0,1)=='>')
                    self::$spaces++;
                \Analog::log($line,$level);
            }
        }   // end function log()

    }

    class wbQueryException extends \Exception {}

interface wbQuery_DriverInterface
{
    function getDSN           ();
    function getDriverOptions ();
    function search           ( $options );
    function insert           ( $options );
    function update           ( $options );
    function replace          ( $options );
    function delete           ( $options );
    function truncate         ( $options );
    function min              ( $fieldname, $options );
    function group_by         ( $group_by );
    function limit            ( $limit );
    function map_tables       ( $tables   , $options );
    function order_by         ( $order_by );
    function parse_join       ( $tables   , $options );
    function parse_where      ( $where );
    function max              ( $fieldname, $options );
    function sqlImport        ( $import, $replace_prefix );
    function showTables       ( $ignore_prefix );
    function dumpTable        ( $table, $structure_only, $remove_prefix, $ignore );
    function dumpAllTables    ( $ignore_prefix );
}   // end interface wbQuery_DriverInterface


/**
 * default SQL driver class
 *
 * @category   wblib2
 * @package    wbQuery
 * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
 * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
 */
    class wbQuery_Driver extends \PDO
    {

        protected $dsn                  = NULL;
        protected $driver               = 'MySQL';
        protected $host                 = 'localhost';
        protected $port                 = NULL;
        protected $user                 = NULL;
        protected $pass                 = NULL;
        protected $dbname               = "mydb";
        protected $prefix               = NULL;
        protected $timeout              = 5;
        protected $force_utf8           = false;

        /**
         * error stack
         **/
        protected $errors               = array();
        protected $lasterror            = NULL;
        /**
         * statement properties
         **/
        protected $statement            = NULL;
        protected $lastInsertID         = NULL;
        /**
         * logger
         **/
        private static   $analog        = NULL;
        /**
         * space before log message
         **/
        protected static $spaces        = 0;
        /**
         * log level
         **/
        public static    $loglevel      = 4;

// ----- Operators used in WHERE-clauses -----
        public static    $operators     = array(
            '='  => '=',
            'eq' => '=',
            'ne' => '<>',
            '==' => '=',
            '!=' => '<>',
            '=~' => 'REGEXP',
            '!~' => 'NOT REGEXP',
            '~~' => 'LIKE'
        );

// ----- Conjunctions used in WHERE-clauses -----
        public static    $conjunctions  = array(
            'and'  => 'AND',
            'AND'  => 'AND',
            'OR'   => 'OR',
            'or'   => 'OR',
            '&&'   => 'AND',
            '||'   => 'OR',
        );

// ----- Known options for constructor -----
        protected $_options = array(
            array(
                'name' => 'dsn',
                'type' => 'string',
            ),
            array(
                'name' => 'host',
                'type' => 'string',
            ),
            array(
                'name' => 'port',
                'type' => 'integer',
            ),
            array(
                'name' => 'user',
                'type' => 'string',
            ),
            array(
                'name' => 'pass',
                'type' => 'plaintext',
            ),
            array(
                'name' => 'dbname',
                'type' => 'string',
            ),
            array(
                'name' => 'timeout',
                'type' => 'integer',
            ),
            array(
                'name' => 'prefix',
                'type' => 'string',
            ),
            array(
                'name' => 'force_utf8',
                'type' => 'boolean'
            ),
        );

        // ----- SQL Injection checks -----
        // Signature 1 - detects single-quote and double-dash
        const PCRE_SQL_QUOTES = '/(\%27)|(\')|(%2D%2D)|(\-\-)/i';

        // Signature 2 - detects typical SQL injection attack, such as 1'or some_boolean_expression
        const PCRE_SQL_TYPICAL = "/\w*(\%27)|'(\s|\+)*((\%6F)|o|(\%4F))((\%72)|r|(\%52))/i";

        //Signature 3 - detects use of union - good guarantee of an attack
        const PCRE_SQL_UNION = "/((\%27)|')(\s|\+)*union/i";

        //Signature 4 - detects calling of an MS SQL stored or extended procedures
        const PCRE_SQL_STORED = '/exec(\s|\+)+(s|x)p\w+/i';

        /**
         * constructor
         *
         * @access public
         * @param  array  $options
         * @return void
         **/
        public function __construct( $options = array() ) {
            $this->__initialize($options);
            if(!isset($options['db_handle']))
            {
                // ... create PDO object
                if ( $this->pass == '' ) {
                    parent::__construct( $this->dsn, $this->user, $this->getDriverOptions() );
                }
                else
                {
                    try {
                        parent::__construct( $this->dsn, $this->user, $this->pass, $this->getDriverOptions() );
                    }
                    catch (PDOException $e)
                    {
                        self::log($e->getMessage);
                        echo $e->getMessage();
                    }
                }
                // remove password
                $this->pass = '*****';
            }
        }   // end function __construct()

        /**
         * this is only to capture the statement that was passed to PDO,
         * allows to use getLastStatement()
         *
         * @access public
         * @param  string  $statement
         * @return PDO::query
         **/
        public function query($statement) {
            $this->setError( NULL ); // reset error stack
            $this->statement = $statement; // reset statement
            $this->_lastStatement = $statement;
            try {
                $result = \PDO::query($statement);
                return $result;
            } catch (PDOException $e) {
                self::log($e->getMessage);
                $this->setError($e->getMessage);
            }
        }

        /**
         * accessor to Analog (if installed)
         *
         * Note: Log messages are ignored if no Analog is available!
         *
         * @access protected
         * @param  string   $message - log message
         * @param  integer  $level   - log level; default: 3 (error)
         * @return void
         **/
        public static function log($message, $level = 3)
        {
            $class = get_called_class();
//echo "CLASS -$class- LEVEL -$level- LOGLEVEL -", $class::$loglevel, "-\n";
            if($level>$class::$loglevel)  return;
            if( !$class::$analog && !$class::$analog == -1)
            {
                if(file_exists(dirname(__FILE__).'/3rdparty/Analog/wblib.init.inc.php'))
                {
                    include_once(dirname(__FILE__).'/3rdparty/Analog/wblib.init.inc.php');
                    wblib_init_3rdparty(dirname(__FILE__).'/debug/',$class,$class::$loglevel);
                    $class::$analog = true;
                }
                else
                {
                    $class::$analog = -1;
                }
            }
            if ( $class::$analog !== -1 )
            {
                if(substr($message,0,1)=='<')
                    $class::$spaces--;
                self::$spaces = ( self::$spaces > 0 ? self::$spaces : 0 );
                $line = str_repeat('    ',self::$spaces).$message;
                if(substr($message,0,1)=='>')
                    self::$spaces++;
                \Analog::log($line,$level);
            }
        }   // end function log()

        /**
         * Create valid DSN and store it for later use
         *
         * @access public
         * @return string
         **/
        public function getDSN() {
            if ( empty( $this->dsn ) ) {
                $this->dsn = $this->driver.':host='.$this->host.';dbname='.$this->dbname;
                if ( isset( $this->port ) ) {
                    $this->dsn .= ';port='.$this->port;
                }
            }
            return $this->dsn;
        }   // end function getDSN()

        /**
         * driver classes may return an array of options passed to PDO
         *
         * by default, nothing is returned
         *
         * @access public
         * @return array
         **/
        public function getDriverOptions() {
            if($this->force_utf8) {
                return array(
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                );
            }
        }   // end function getDriverOptions()

        /**
         * Accessor to last executed statement; useful for debugging
         *
         * @access public
         * @return string
         *
         **/
    	public function getLastStatement() {
    	    return $this->_lastStatement;
    	}   // end function getLastStatement()

/*******************************************************************************
 * SQL BUILDER
 ******************************************************************************/

        /**
         * perform a search
         *
         * Usage example:
         *
         * $data = $dbh->search(array(
         *    'tables' => 'myTable',
         *    'fields' => array( 'id', 'content' ),
         *    'where'  => 'id == ? && content ne ?',
         *    'params' => array( '5', NULL )
         * ));
         *
         * Use isError() and getError() for error handling!
         *
         * @access public
         * @param  array   $options
         * @return mixed   array of result or false
         **/
        public function search ( $options )
        {
            $this->setError( NULL ); // reset error stack
            $this->statement = NULL; // reset statement

            if ( ! isset( $options['tables'] ) )
            {
                $this->setError('no tables!','fatal');
                return NULL;
            }

            // cache statement
            $hash = (md5(serialize($options)));
            if(isset($this->hashes) && count($this->hashes) && array_key_exists($hash,$this->hashes))
            {
                $this->statement = $this->hashes[$hash];
            }
            else
            {
                $tables = $this->map_tables( $options['tables'], $options );

                $fields = isset( $options['fields'] )
                        ? $options['fields']
                        : '*';

                $where  = isset( $options['where'] )
                        ? $this->parse_where( $options['where'] )
                        : NULL;

                $order  = isset( $options['order_by'] )
                        ? $this->order_by( $options['order_by'] )
                        : NULL;

                $limit  = isset( $options['limit'] )
                        ? $this->limit( $options['limit'] )
                        : NULL;

                $group  = isset( $options['group_by'] )
                        ? $this->group_by($options['group_by'])
                        : NULL;

        		// any errors so far?
        		if ( $this->isError() ) {
        		    // let the caller handle the error, just return false here
        		    $this->setError('unable to prepare the statement!','fatal');
                    return false;
        		}

                // create the statement
                $this->statement
                    = "SELECT "
                    . (
                          is_array( $fields )
                        ? implode( ', ', $fields )
                        : $fields
                      )
                    . " FROM $tables $where $group $order $limit";

                $this->hashes[$hash] = $this->statement;
            }

            if ( isset($options['params']) )
            {
                if(!is_array($options['params']))
                    $options['params'] = array($options['params']);
                $params = $this->params($options['params']);
            }
            else
            {
                $params = NULL;
            }

            self::log('executing statement (interpolated for debugging)',7);
            self::log(self::interpolateQuery($this->statement,$params),7);
            $this->_lastStatement = self::interpolateQuery($this->statement,$params);

            // create statement handle
            $stmt   = $this->prepare( $this->statement );

            if ( ! is_object( $stmt ) )
            {
                $error_info = '['.implode( "] [", $this->errorInfo() ).']';
                $this->setError( 'prepare() ERROR: '.$error_info, 'fatal'  );
                return false;
            }

            if ( $stmt->execute( $params ) )
            {
                self::log( 'returning ['.$stmt->rowCount().'] results', 7 );
                return $stmt->fetchAll( \PDO::FETCH_ASSOC );
            }
            else
            {
                if ( $stmt->errorInfo() )
                    $error = '['.implode( "] [", $stmt->errorInfo() ).']';
                $this->setError( $error, 'fatal' );
                return false;
            }

        }   // end function search()

        /**
         * insert data; returns false on error, true on success
         *
         * Use isError() and getError() to check for errors!
         *
         * @access public
         * @param  array  $options
         * @return mixed
         **/
        public function insert( $options )
        {

            if ( ! isset( $options['tables'] ) )
            {
                $this->setError('no tables!','fatal');
                return NULL;
            }
            if ( ! isset( $options['values'] ) )
            {
                $this->setError('no tables!','fatal');
                return NULL;
            }

            $this->setError( NULL ); // reset error stack
            $this->statement = NULL; // reset statement

            $do     = isset( $options['do'] )
                    ? $options['do']
                    : 'INSERT';

            $options['__is_insert'] = true;

            $params = isset( $options['params'] ) && is_array( $options['params'] )
                    ? $this->params( $options['params'] )
                    : NULL;

            // cache statement
            $hash = (md5(serialize($options)));
            if(isset($this->hashes) && count($this->hashes) && array_key_exists($hash,$this->hashes))
            {
                $this->statement = $this->hashes[$hash];
                $params = isset( $options['params'] ) && is_array( $options['params'] )
                        ? $this->params( $options['params'] )
                        : NULL;
            }
            else
            {

                $tables = $this->map_tables( $options['tables'], $options );
                $values = array();
                $fields = NULL;

                if ( isset($options['values']) )
                {
                    if ( ! is_array($options['values']) )
                        $options['values'] = array($options['values']);
                    foreach ( $options['values'] as $v )
                        $values[] = '?';
                }

                if ( isset( $options['fields'] ) ) {
                    if ( ! is_array( $options['fields'] ) )
                        $options['fields'] = array( $options['fields'] );
                    $fields = '( `'
                            . implode( '`, `', $options['fields'] )
                            . '` )';
                }

                // create the statement
                $this->statement
                    = "$do INTO $tables $fields"
                    . " VALUES ( "
                    . implode( ', ', $values )
                    . " )"
                    ;
            }

            if ( isset( $options['values'] ) && is_array( $options['values'] ) )
                foreach( $options['values'] as $value )
                    $params[] = $value;

            self::log('executing statement (interpolated for debugging)',7);
            self::log(self::interpolateQuery($this->statement,$params),7);
            $this->_lastStatement = self::interpolateQuery($this->statement,$params);

            $stmt = $this->prepare( $this->statement );

            if ( ! is_object( $stmt ) )
            {
                $error_info = '['.implode( "] [", $this->errorInfo() ).']';
                $this->setError( 'prepare() ERROR: '.$error_info, 'fatal'  );
                return false;
            }

            if ( $stmt->execute( $params ) ) {
                self::log(sprintf('statement successful: %s',$this->statement),7);
                // if it's an insert, save the id
                if ( $do == 'INSERT' ) {
                    $this->lastInsertID = $this->lastInsertId();
                }
                return true;
            }
            else
            {
                if ( $stmt->errorInfo() )
                    $error = '['.implode( "] [", $stmt->errorInfo() ).']';
                $this->setError( $error, 'fatal' );
                return false;
            }
        }   // end function insert()

        /**
         * replace data; returns false on error, true on success
         * this is a wrapper calling insert(), so use the same options here!
         *
         * Use isError() and getError() to check for errors!
         *
         * @access public
         * @param  array  $options
         * @return mixed
        **/
        public function replace ( $options )
        {
            $options['do'] = 'REPLACE';
            return $this->insert($options);
        }   // end function replace()

        /**
         * update data; returns false on error, true on success
         *
         * Use isError() and getError() to check for errors!
         *
         * @access public
         * @param  array  $options
         * @return mixed
        **/
        public function update( $options )
        {
            if ( ! isset( $options['tables'] ) )
            {
                $this->setError('no tables!','fatal');
                return NULL;
            }
            if ( ! isset( $options['values'] ) )
            {
                $this->setError('no values!','fatal');
                return NULL;
            }

            $this->setError( NULL ); // reset error stack
            $this->statement = NULL; // reset statement

            // cache statement
            $hash = (md5(serialize($options)));
            if(isset($this->hashes) && count($this->hashes) && array_key_exists($hash,$this->hashes))
            {
                $this->statement = $this->hashes[$hash];
            }
            else
            {
                $tables = $this->map_tables( $options['tables'], $options );
                $where  = isset( $options['where'] )
                        ? $this->parse_where( $options['where'] )
                        : NULL;

        		// any errors so far?
        		if ( $this->isError() ) {
        		    // let the caller handle the error, just return false here
        		    $this->setError('unable to prepare the statement!','fatal');
                    return false;
        		}

                $carr = array();
                if ( isset( $options['fields'] ) && ! is_array( $options['fields'] ) )
                    $options['fields'] = array( $options['fields'] );
                foreach ( $options['fields'] as $key )
                    $carr[] = "$key = ?";

                // create the statement
                $this->statement
                    = "UPDATE $tables SET "
                    . implode( ', ', $carr )
                    . " $where";

                $this->hashes[$hash] = $this->statement;
            }

            if ( isset( $options['values'] ) )
            {
                if ( ! is_array( $options['values'] ) )
                    $options['values'] = array($options['values']);
                foreach( $options['values'] as $value )
                    $params[] = $value;
            }

            if ( isset( $options['params'] ) )
            {
                if ( ! is_array( $options['params'] ) )
                    $options['params'] = array($options['params']);
                foreach( $options['params'] as $value )
                    $params[] = $value;
            }

            self::log('executing update statement (interpolated for debugging)',7);
            self::log(self::interpolateQuery($this->statement,$params),7);
            $this->_lastStatement = self::interpolateQuery($this->statement,$params);

            // create statement handle
            $stmt   = $this->prepare( $this->statement );

            if ( ! is_object( $stmt ) )
            {
                $error_info = '['.implode( "] [", $this->errorInfo() ).']';
                $this->setError( 'prepare() ERROR: '.$error_info, 'fatal'  );
                return false;
            }

            if ( $stmt->execute( $params ) )
            {
                return true;
            }
            else
            {
                if ( $stmt->errorInfo() )
                    $error = '['.implode( "] [", $stmt->errorInfo() ).']';
                $this->setError( $error, 'fatal' );
                return false;
            }

        }   // end function update()

        public function delete ( $options )
        {
            if ( ! isset( $options['tables'] ) )
            {
                $this->setError('no tables!','fatal');
                return NULL;
            }

            $this->setError( NULL ); // reset error stack
            $this->statement = NULL; // reset statement
            $options['__is_delete'] = true;

            // cache statement
            $hash = (md5(serialize($options)));
            if(isset($this->hashes) && count($this->hashes) && array_key_exists($hash,$this->hashes))
            {
                $this->statement = $this->hashes[$hash];
                $params = isset( $options['params'] ) && is_array( $options['params'] )
                        ? $this->params( $options['params'] )
                        : NULL;
            }
            else
            {
                $tables = $this->map_tables( $options['tables'], $options );
                $where  = isset( $options['where'] )
                        ? $this->parse_where( $options['where'] )
                        : NULL;

                // create the statement
                $this->statement
                    = "DELETE FROM $tables "
                    . " $where";

                if ( isset($options['params']) )
                {
                    if(!is_array($options['params']))
                        $options['params'] = array($options['params']);
                    $params = $this->params($options['params']);
                }
                else
                {
                    $params = NULL;
                }

                self::log('executing statement (interpolated for debugging)',7);
                self::log(self::interpolateQuery($this->statement,$params),7);
                $this->_lastStatement = self::interpolateQuery($this->statement,$params);

                $stmt = $this->prepare( $this->statement );

                if ( ! is_object( $stmt ) )
                {
                    $error_info = '['.implode( "] [", $this->errorInfo() ).']';
                    $this->setError( 'prepare() ERROR: '.$error_info, 'fatal'  );
                    return false;
                }

                if ( $stmt->execute($params) ) {
                    self::log(sprintf('statement successful: %s',$this->statement),7);
                    return true;
                }
                else
                {
                    if ( $stmt->errorInfo() )
                        $error = '['.implode( "] [", $stmt->errorInfo() ).']';
                    $this->setError( $error, 'fatal' );
                    return false;
                }
            }
        }   // end function delete()

        public function truncate ( $options ) {}

/*******************************************************************************
 * ERROR HANDLING
 ******************************************************************************/

        /**
         *
         *
         * @access public
         * @return boolean
         **/
        public function isError() {
            return isset( $this->lasterror );
        }   // end function isError()

        /**
         * Accessor to last error
         *
         * @access public
         * @param  boolean $fullstack - return the full error stack; default false
         * @return mixed   array if $fullstack is set, string otherwise
         **/
        public function getError( $fullstack = false ) {
            if ( $fullstack )
                return $this->errors;
            return $this->lasterror;
        }   // end function getError()

/*******************************************************************************
 * METHODS THAT ARE VERY SIMILAR IN ALL DRIVERS (STILL OVERLOADABLE)
 ******************************************************************************/

        /**
         * parse where conditions
         *
         * @access protected
         * @param  mixed     $where - array or scalar
         * @return mixed     parsed WHERE statement or NULL
         **/
        public function parse_where( $where ) {
            $this->log('> parse_where()',7);
            $this->log(var_export($where,1),7);
            if ( is_array( $where ) )
                $where = implode( ' AND ', $where );
            // replace conjunctions
            $string = $this->replaceConj( $where );
            // replace operators
            $string = $this->replaceOps( $string );
            if ( ! empty( $string ) ) {
                $this->log( $string, 7 );
                $this->log('< parse_where()',7);
                return ' WHERE '.$string;
            }
            $this->log('< parse_where() - returning NULL',7);
            return NULL;
        }   // end function parse_where()

        /**
         * Replace operators in string
         *
         * @access protected
         * @param  string    $string - string to convert
         * @return string
         *
         **/
        protected function replaceOps( $string ) {
            self::log('> replaceOps()',7);
            if(!defined('PCRE_OPERATORS')) // only to this once
            {
                $conj = array();
                foreach(array_keys(wbQuery_Driver::$operators) as $key)
                    $conj[] = preg_quote($key);
                define('PCRE_OPERATORS',implode('|',$conj));
            }
            self::log(sprintf('replacing (%s) from: [%s]', PCRE_OPERATORS, $string), 7);
            $new_string = preg_replace_callback(
                "/(\s{1,})(".PCRE_OPERATORS.")(\s{1,})/sx",
                function($matches) {
                    $new = ' '
                         .
                           (
                             isset(wbQuery_Driver::$operators[$matches[2]])
                             ? wbQuery_Driver::$operators[$matches[2]]
                             : $matches[2]
                           )
                         . ' ';
                    return $new;
                },
                $string
            );
            self::log('< replaceOps()',7);
            return $new_string;
        }   // end function replaceOps()

        /**
         * Replace conjunctions in string
         *
         * @access protected
         * @param  string    $string - string to convert
         * @return string
         *
         **/
        protected function replaceConj( $string )
        {
            self::log('> replaceConj()',7);
            if(!defined('PCRE_CONJUNCTIONS')) // only to this once
            {
                $conj = array();
                foreach(array_keys(wbQuery_Driver::$conjunctions) as $key)
                    $conj[] = preg_quote($key);
                define('PCRE_CONJUNCTIONS',implode('|',$conj));
            }
            self::log(sprintf('replacing (%s) from string [%s]', PCRE_CONJUNCTIONS, $string), 7);
            $new_string = preg_replace_callback(
                "/(\s{1,})(".PCRE_CONJUNCTIONS.")(\s{1,})/sx",
                function($matches) {
                    $new = $matches[1]
                         .
                           (
                             isset(wbQuery_Driver::$conjunctions[$matches[2]])
                             ? wbQuery_Driver::$conjunctions[$matches[2]]
                             : $matches[2]
                           )
                         . $matches[3];
                    return $new;
                },
                $string
            );
            self::log('< replaceConj() - '.$new_string,7);
            return $new_string;
        }   // end function replaceConj()

        /**
         * put error on error stack and set $lasterror
         *
         * @access private
         * @param  string  $error
         * @param  string  $level
         * @return void
         **/
        protected function setError( $error, $level = 'error' )
        {
            self::log('> setError()',7);
            if(is_null($error))
            {
                $this->lasterror = NULL;
                $this->errors    = array();
                self::log('< setError(reset)',7);
                return;
            }
            
            $caller = debug_backtrace();
            self::log(sprintf('text [%s], level [%s], caller: [%s]',$error,$level,var_export($caller[0],1)),7);
            $this->lasterror = $error;
            // push onto error stack
            if ( $error != NULL )
            	$this->errors[]  = $error;
            self::log('< setError()',7);
        }   // end function setError()

        /**
         * checks params for possible SQL injection code; uses setError() to log
         * positive matches
         *
         * @access protected
         * @param  array     $params - params to check
         * @return mixed     array of validated params or false
         **/
        protected function params( $params )
        {
            self::log('> params()',7);
            foreach ( $params as $i => $param )
            {
    			if ( ! $this->detectSQLInjection( $this->quote($param) ) ) {
    				// no escaping here; we're using PDO, remember?
    			    $params[$i] = $param;
    			}
    			else {
    				$this->setError('POSSIBLE SQL INJECTION DETECTED!', 'fatal');
                    self::log('< params() - return NULL',7);
    				return NULL;
    			}
            }
            self::log('PARAMS: '.var_export($params,1), 7);
            self::log('< params()',7);
            return $params;
        }   // end function params()

	    /**
	     * This method checks for typical SQL injection code
	     *
	     * @access public
         * @param  mixed $values - array of values or single value (scalar)
         * @return boolean       - returns false if no intrusion code was found
	     **/
		public function detectSQLInjection( $values )
        {
			if ( empty( $values ) )
		        return false;
			if ( is_scalar( $values ) )
			    $values = array( $values );
			foreach( $values as $value )
            {
				// check for SQL injection
				foreach(
					array( 'PCRE_SQL_TYPICAL', 'PCRE_SQL_UNION', 'PCRE_SQL_STORED' ) //'PCRE_SQL_QUOTES',
					as $constant
				) {
					if ( preg_match( constant( 'self::'.$constant ), $value ) )
                    {
		                self::log( sprintf( 'SECURITY ISSUE: suspect SQL injection -> (%s) -> [%s]',$constant,$value), 0 );
                        $this->setError('possible SQL injection!','fatal');
		                return true;
		            }
				}
			}
			// all checks passed
			return false;
		}   // end function detectSQLInjection()

        /**
         * Replaces any parameter placeholders in a query with the value of that
         * parameter. Useful for debugging. Assumes anonymous parameters from
         * $params are in the same order as specified in $query
         *
         * Source: http://stackoverflow.com/questions/210564/pdo-prepared-statements
         *
         * @access public
         * @param  string $query  The sql query with parameter placeholders
         * @param  array  $params The array of substitution parameters
         * @return string The interpolated query
         */
        public static function interpolateQuery($query, $params) {

            if ( ! is_array($params) )
                return $query;

            $keys   = array();
            $values = $params;

            # build a regular expression for each parameter
            foreach ($params as $key => $value)
            {
                if (is_string($key))
                    $keys[] = '/:'.$key.'/';
                else
                    $keys[] = '/[?]/';

                if (is_array($value))
                    $values[$key] = implode(',', $value);

                if (is_null($value))
                    $values[$key] = 'NULL';
            }
            // Walk the array to see if we can add single-quotes to strings
            array_walk($values, function(&$v, $k) { if (!is_numeric($v) && $v!="NULL") $v = "\'".$v."\'"; });
            $query = preg_replace($keys, $values, $query, 1, $count);
            return $query;
        }   // end function interpolateQuery()

        /**
         * extracts SQL statements from a string and executes them as single
         * statements
         *
         * @access public
         * @param  string  $import
         *
         **/
        public function sqlImport($import,$replace_prefix=NULL,$replace_with=NULL)
        {
            $errors = array();
            $import = preg_replace( "%/\*(.*)\*/%Us", ''          , $import );
            $import = preg_replace( "%^--(.*)\n%mU" , ''          , $import );
            $import = preg_replace( "%^$\n%mU"      , ''          , $import );
            if($replace_prefix)
                $import = preg_replace( "%".$replace_prefix."%", $replace_with, $import );
            $import = preg_replace( "%\r?\n%"       , ''          , $import );
            $import = str_replace ( '\\\\r\\\\n'    , "\n"        , $import );
            $import = str_replace ( '\\\\n'         , "\n"        , $import );
            // split into chunks
            $sql = preg_split(
                '~(insert\s+(?:ignore\s+)into\s+|update\s+|replace\s+into\s+|create\s+table|truncate\s+table|delete\s+from)~i',
                $import,
                -1,
                PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY
            );
            if(!count($sql) || !count($sql)%2)
                return false;
            // index 1,3,5... is the matched delim, index 2,4,6... the remaining string
            $stmts = array();
            for($i=0;$i<count($sql);$i++)
                $stmts[] = $sql[$i] . $sql[++$i];
            foreach ($stmts as $imp){
                if ($imp != '' && $imp != ' '){
                    $ret = $this->query($imp);
                    if($this->isError())
                        $errors[] = $this->getError();
                }
            }
            if($errors)
                $this->errors = $errors;
            return ( count($errors) ? false : true );
        }   // end function sqlImport()

        /**
         * initialize database class:
         *
         * - load driver defaults
         * - overwrite defaults with given options (if any)
         * - get valid DSN for DB connection
         *
         **/
        private final function __initialize($options) {
            foreach ( $this->_options as $opt ) {
                $key  = $opt['name'];
                $type = $opt['type'];
                if ( isset( $options[$key] ) && ! empty( $options[$key] ) ) {
                    $this->$key = $options[$key];
                }
            }
            $this->getDSN();
            return true;
        }   // end function __initialize()

    }

    class MySQL extends wbQuery_Driver implements wbQuery_DriverInterface
    {
        protected $port   = 3306;
        protected $driver = 'mysql';
        /**
         * log level
         **/
        public    static $loglevel = 4;
        /**
         * analog handler
         **/
        protected static $analog   = NULL;

        /**
         *
         * @access protected
         * @return
         **/
        public function group_by($group_by) {
            return ' GROUP BY '.$group_by;
        }   // end function group_by()


        /**
         *
         * @access protected
         * @return
         **/
        public function limit($limit) {
            return ' LIMIT '.$limit;
        }   // end function limit()

        /**
         * adds prefix to table names, handles joins
         *
         * @access protected
         * @param  mixed     $tables    - array of tables or single table name
         * @param  array     $options
         * @return string
         **/
        public function map_tables( $tables, $options = array() )
        {
            if ( is_array( $tables ) )
            {
                // join(s) defined?
                if ( isset( $options['join'] ) ) {
                    return $this->parse_join( $tables, $options );
                }
                else
                {
                    foreach ( $tables as $i => $t_name )
                    {
                        if (
                             ! empty( $this->prefix )
                             &&
                             substr_compare( $t_name, $this->prefix, 0, strlen($this->prefix), true )
                        ) {
                            $t_name = $this->prefix . $t_name;
                        }
                        $tables[$i] = $t_name . ( isset( $options['__is_delete'] ) ? '' : ' as t' . ($i+1) );
                    }
                    return implode( ', ', $tables );
                }
            }
            else
            {
                return $this->prefix . $tables . ( ( isset( $options['__is_insert'] ) || isset( $options['__is_delete'] ) ) ? NULL : ' as t1' );
            }
        }   // end function map_tables()

        /**
         * returns correct order by syntax
         *
         * @access protected
         * @return string
         **/
        public function order_by( $order_by )
        {
            return ' ORDER BY '.$order_by;
        }   // end function order_by()

        /**
         * parse join statement
         *
         *
         *
         **/
        public function parse_join( $tables, $options = array() )
        {

            $jointype = ' LEFT JOIN ';
            $join     = $options['join'];

            self::log('tables: '.var_export($tables,1),7);
            self::log('options: '.var_export($options,1),7);

            if ( ! is_array( $tables ) )
                $tables = array( $tables );

            if ( count( $tables ) > 2 && ! is_array( $join ) )
            {
                $this->setError( '$tables count > 2 and $join is not an array', 'fatal' );
                return NULL;
            }

            if ( ! is_array( $join ) )
                $join = array( $join );

            if ( count( $join ) <> ( count( $tables ) - 1 ) )
            {
                $this->setError( 'table count <> join count', 'fatal' );
                return;
            }

            $join_string = $this->prefix . $tables[0] . ' AS t1 ';

            foreach ( $join as $index => $item )
            {
                $jointype     = isset($options['jointype'])
                              ? ' '.strtoupper($options['jointype']).' '
                              : $jointype
                              ;
                if(!substr_count(strtolower($jointype),' join '))
                    $jointype .= ' JOIN ';
                $join_string .= $jointype
                             .  $this->prefix.$tables[ $index + 1 ]
                             .  ' AS t'.($index+2).' ON '
                             .  $item;
            }

            self::log(sprintf('join string before replacing ops/conj: [%s]',$join_string),7);

            $join = $this->replaceConj( $this->replaceOps( $join_string ) );

            self::log(sprintf('returning parsed join: [%s]',$join),7);

            return $join;

        }   // end function parse_join()

        /**
         * show tables
         *
         * @access public
         * @return array
         **/
    	public function showTables($ignore_prefix=false)
        {
    	    $data   = $this->query('SHOW TABLES');
    	    $tables = array();
    		while( $result = $data->fetch() )
                if($ignore_prefix || ! substr_compare($result[0],$this->prefix,0,strlen($this->prefix)))
         		$tables[] = $result[0];
    		return $tables;
    	}   // end function showTables()

        /**
         * Get the max value of a given field
         *
         * @access public
         * @param  string   $fieldname - field to check
         * @param  array    $options   - additional options (where-Statement, for example)
         * @return mixed
         *
         **/
        public function max( $fieldname, $options = array() ) {
            $data = $this->search(
                array_merge(
                    $options,
                    array(
                        'limit'  => 1,
                        'fields' => "max($fieldname) as maximum",
                    )
                )
            );
            if ( isset( $data ) && is_array( $data ) && count( $data ) > 0 ) {
                return $data[0]['maximum'];
            }
            return NULL;
        }   // end function max()

        /**
         * Get the min value of a given field
         *
         * @access public
         * @param  string   $fieldname - field to check
         * @param  array    $options   - additional options (where-Statement, for example)
         * @return mixed
         *
         **/
        public function min( $fieldname, $options = array() ) {
            $data = $this->search(
                array_merge(
                    $options,
                    array(
                        'limit'  => 1,
                        'fields' => "min($fieldname) as minimum",
                    )
                )
            );
            if ( isset( $data ) && is_array( $data ) && count( $data ) > 0 ) {
                return $data[0]['minimum'];
            }
            return NULL;
        }   // end function min()

        /**
         * allows to create an SQL dump of all tables
         *
         * by default, if 'prefix' is configured, only tables having this name
         * prefix will be dumped; set $ignore_prefix to a true value to get a
         * dump of all tables in the current connection
         *
         * @access public
         * @param  boolean $ignore_prefix - default false
         * @return string
         **/
        public function dumpAllTables($ignore_prefix=false) {
            $tables = $this->showTables($ignore_prefix);
            $output = '';
            foreach($tables as $table)
            {
                $output .= self::dumpTable($table);
            }
            return $output;
        }

        /**
         * create an SQL dump for the given table
         *
         * by default, structure and data are dumped; to get the structure only,
         * set $structure to a true value
         *
         * @access public
         * @param  string  $table          - table to dump
         * @param  boolean $structure_only - default false
         * @return string
         **/
        public function dumpTable( $table, $structure_only = false, $remove_prefix = false, $ignore = false ) {
            $output   = array();
            $fields   = "";
            $sep2     = "";
            $stmt     = $this->query("SHOW CREATE TABLE $table");
            $row      = $stmt->fetch(\PDO::FETCH_NUM);

            if($remove_prefix) //CREATE TABLE `wb_users` (
                $row[1] = preg_replace('~`'.$this->prefix.'~i', '`', $row[1]);

            $output[] = $row[1].";\n\n";

            if(!$structure_only)
            {
                $stmt   = $this->query("SELECT * FROM $table");
                $line   = '';
                while($row = $stmt->fetch(\PDO::FETCH_OBJ)){
                    // runs once per table - create the INSERT INTO clause
                    if($fields == ""){
                        $fields = "INSERT " . ( $ignore ? 'IGNORE ' : '' )
                                . "INTO `" . ( $remove_prefix ? str_ireplace($this->prefix, '', $table) : $table ) . "` (";
                        $sep    = "";
                        // grab each field name
                        foreach($row as $col => $val){
                            $fields .= $sep . "`$col`";
                            $sep     = ", ";
                        }
                        $fields .= ") VALUES";
                        $line   .= $fields . "\n";
                    }
                    // grab table data
                    $sep   = "";
                    $line .= $sep2 . "(";
                    foreach($row as $col => $val){
                        // add slashes to field content
                        $search  = array("\n", "\r");
                        $replace = array("\\n", "\\r");
                        $val     = str_replace($search, $replace, $val);
                        $line   .= $sep . \PDO::quote($val);
                        $sep     = ", ";
                    }
                    // terminate row data
                    $line .= ")";
                    $sep2  = ",\n";
                }
                if($line)
                {
                    // terminate insert data
                    $line .= ";\n";
                    $output[] = $line;
                }
            }
            return implode("\n",$output);
        }

    }   // ---------- end class MySQL ----------

}