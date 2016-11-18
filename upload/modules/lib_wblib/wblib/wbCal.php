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
 *   @package      wbCal
 *   @author       BlackBird Webprogrammierung
 *   @copyright    (c) 2016 BlackBird Webprogrammierung
 *   @license      GNU LESSER GENERAL PUBLIC LICENSE Version 3
 *
 **/

namespace wblib;

require dirname(__FILE__).'/3rdParty/Carbon/Carbon.php';
require dirname(__FILE__).'/3rdParty/Carbon/CarbonInterval.php';
use \Carbon\Carbon;
use \Carbon\CarbonInterval;


if ( ! class_exists( 'wbCal', false ) )
{
    class wbCal
    {
        /**
         *
         **/
        private          $events   = array();
        /**
         *
         **/
        private          $today    = array();

        /**
         * array of named instances
         **/
        private static   $instance = NULL;
        /**
         * logger
         **/
        private static   $analog   = NULL;
        /**
         * weekdays; will be translated by wbLang
         **/
        private static $weekdays = array(
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday',
        );
        /**
         * space before log message
         **/
        protected static $spaces   = 0;
        /**
         * accessor to wbLang
         **/
        protected static $wblang   = NULL;
        /**
         * accessor to RainTPL
         **/
        protected static $te       = NULL;
        /**
         * log level
         **/
        public    static $loglevel = 0;
        /**
         * format strings for human readable output
         **/
        public    static $strings = array(
            'y' => '[[{{number}} |year|years]]',
            'm' => '[[{{number}} |month|months]]',
            'd' => '[[{{number}} |day|days]]',
            'h' => '[[{{number}} |hour|hours]]',
            'i' => '[[{{number}} |minute|minutes]]',
            's' => '[[{{number}} |second|seconds]]',
        );


        // private to make sure that constructor can only be called
        // using getInstance()
        private function __construct() {
            $this->today = array(
                'day'  => date('d'),
                'mon'  => date('m'),
                'year' => date('Y'),
                'row'  => 1,
            );
        }    // end function __construct()

        // no cloning!
        private function __clone() {}

        /**
         * Create an instance
         *
         * @access public
         * @return object
         **/
        public static function getInstance()
        {
            if(!is_object(self::$instance))
            {
                self::$instance = new self();
            }
            return self::$instance;
        }   // end function getInstance()

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
            //if($class != 'wblib\wbValidate') return;
            if($level>$class::$loglevel)     return;
            if( !self::$analog && !self::$analog == -1)
            {
                if(file_exists(dirname(__FILE__).'/3rdparty/Analog/wblib.init.inc.php'))
                {
                    include_once(dirname(__FILE__).'/3rdparty/Analog/wblib.init.inc.php');
                    wblib_init_3rdparty(dirname(__FILE__).'/debug/','wbCal',$class::$loglevel);
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

        /**
         * accessor to wbLang (if available)
         *
         * returns the original message if wbLang is not available
         *
         * @access protected
         * @param  string    $msg
         * @return string
         **/
        public static function t($message)
        {
            if(!is_scalar($message)) return $message;
            self::log('> t()',7);
            if( !self::$wblang && !self::$wblang == -1)
            {
                self::log('Trying to load wbLang',7);
                try
                {
                    @include_once dirname(__FILE__).'/wbLang.php';
                    self::$wblang = wbLang::getInstance();
                    self::log(sprintf('wbLang loaded, current language [%s]',self::$wblang->current()),7);
                    self::$wblang->addPath(dirname(__FILE__).'/languages');
                    self::$wblang->addFile(self::$wblang->current());
                }
                catch ( wbCalExection $e )
                {
                    self::log('Unable to load wbLang',7);
                    self::$wblang = -1;
                }
            }
            if( self::$wblang !== -1 )
            {
                if(func_num_args() > 1) {
                    $args = func_get_args();
                    self::log('< t(translated) message with arguments',7);
                    return call_user_func_array(array(self::$wblang, 't'), $args);
                }
                self::log('< t(translated) string only (no arguments)',7);
                return self::$wblang->t($message);
            }
            else
            {
                self::log('< t(original)',7);
                return $message;
            }
        }   // end function t()

        /**
         * wrapper for RainTPL
         **/
        public static function tpl()
        {
            if(!is_object(self::$te))
            {
                // configure RainTPL
                include dirname(__FILE__)."/3rdparty/raintpl/rain.tpl.class.php";
                \raintpl::$tpl_dir      = dirname(__FILE__)."/templates/wbCal/";
                \raintpl::$tpl_ext      = 'tpl';
                \raintpl::$cache_dir    = dirname(__FILE__).'/tmp/';
                \raintpl::$path_replace = false;

                // add language handler
                if(!self::$wblang) self::t('ignore'); // just to load wbLang

                \raintpl::$langh = self::$wblang;
                \raintpl::$langh->setPath(dirname(__FILE__).'/languages');
                \raintpl::$langh->addFile( \raintpl::$langh->getLang().'.php' );

                self::$te = new \raintpl();
            }
            return self::$te;
        }   // end function tpl()

        /**
         *
         * @access public
         * @return
         **/
        public function addEvent($event)
        {
            if(!isset($event) || !is_array($event) || !count($event))
                return; // no event data, nothing to do
            self::initEvent($event);
            $this->events[$event['row']] = $event;
        }   // end function addEvent()

        /**
         *
         * @access public
         * @return
         **/
        public function getEvents($start=NULL,$end=NULL,$by=NULL)
        {

            if(!self::isTimestamp($start)) $start = NULL;
            if(!self::isTimestamp($end))   $end   = NULL;

            if(!$start && !$end)
                return $this->events; // get'em all

            $events = array_filter(
                $this->events,
                function ($e) use( $start, $end ) {
                    return (
                        $e['timestamp'] >= $start && $e['timestamp'] <= $end
                        ? true
                        : false
                    );
                }
            );

            if($by)
            {
                switch($by)
                {
                    case 'day':
                        $data = array();
                        foreach($events as $e)
                        {
                            if(!isset($data[$e['mday']])) $data[$e['mday']] = array();
                            $data[$e['mday']][] = $e;
                        }
                        return $data;
                        break;
                }
            }

            return $events;

        }   // end function getEvents()

        /**
         *
         * @access public
         * @return
         **/
        public function getToday()
        {
            return $this->today;
        }   // end function getToday()

        /**
         *
         **/
        public static function formatDuration($start,$end)
        {
            $diff = self::getDiffFromTimestamp($start,$end);
            if(!is_object($diff) || !$diff instanceof \DateInterval)
                return false;
            $parts = array ();
            foreach (self::$strings as $key => $string) {
                if ($diff->$key != 0) {
//echo "key -$key- val -", $diff->$key, "-<br />";
                    $parts[] = self::t($string,array('number'=>$diff->$key));
                }
            }

            $output = '';
            $cnt = count($parts);
            foreach ($parts as $i => $part) {
                $output .= $part.($i < $cnt-2 ? ', ' : ($cnt == 2 ? '' : ($i == $cnt-2 ? ' '.self::t('and').' ' : '')));
            }
            return $output;
        }

        /**
         *
         **/
        public static function getDiffFromTimestamp($start,$end)
        {
            $t1  = \Carbon\Carbon::createFromTimestamp($start);
            $t2  = \Carbon\Carbon::createFromTimestamp($end);
            return $t2->diff($t1);
        }

        /**
         *
         * @access public
         * @return
         **/
        public static function isTimestamp($string)
        {
            if(!$string) return false;
            try {
                new \DateTime('@' . $string);
            } catch(Exception $e) {
                return false;
            }
            return true;
        }   // end function isTimestamp()
        
        /**
         *
         * @access public
         * @return
         **/
        public function renderDay($tpl=NULL)
        {
            if(!$tpl) $tpl = 'currentday';
            $event = array();
            self::initEvent($event);
            self::tpl()->assign($event);
            self::tpl()->draw($tpl);
        }   // end function renderDay()

        /**
         * render month sheet; defaults to current month
         *
         * @access public
         * @param  integer  $mon  - month
         * @param  integer  $year - year
         * @return void
         **/
        public function renderMonth($mon=NULL,$year=NULL)
        {
            if(!$mon)  $mon  = date('m');
            if(!$year) $year = date('Y');
            $start   = strtotime(implode('-',array($year,$mon,1)));
            $end     = mktime(0,0,0,$mon,date('t'),$year);
            $events  = $this->getEvents($start,$end,'day');
            $weekNum = date('W',$end) - date('W', strtotime(date('Y-m-01',$start))) + 1;
            $weeks   = array();
            $week    = date('W',$start); // first week in this month

#echo "<textarea style=\"width:100%;height:200px;color:#000;background-color:#fff;\">";
#print_r( $events );
#echo "</textarea>";

            foreach(range(1,$weekNum) as $n)
            {
                $fow = getdate(strtotime("$year-W$week-1")); // first day of week
                $low = getdate(strtotime("$year-W$week-7")); // last day of week

                $weeks[$week] = array();

                foreach(range(1,7) as $i)
                {
                    $weeks[$week][] = getdate(strtotime("$year-W$week-$i"));
                }

                $week++;
            }

            $tpldata = array(
                'mon'    => $mon,
                'year'   => $year,
                'weeks'  => $weeks,
                'month'  => self::t(date('F',$start)),
                'days'   => array_map(array('self','t'),self::$weekdays),
                'events' => $events,
            );

            self::tpl()->assign($tpldata);
            self::tpl()->draw('month');
        }   // end function renderMonth()
        
/*
getdate

"seconds" 	Numerische Repräsentation der Sekunden 	zwischen 0 und 59
"minutes" 	Numerische Repräsentation der Minuten 	zwischen 0 und 59
"hours" 	Numerische Repräsentation der Stunden 	zwischen 0 und 23
"mday" 	Numerische Repräsentation des Monatstags 	zwischen 1 und 31
"wday" 	Numerische Repräsentation des Wochentags 	zwischen 0 (für Sonntag) und 6 (für Sonnabend)
"mon" 	Numerische Repräsentation des Monats 	zwischen 1 und 12
"year" 	Eine vollständige numerische Repräsentation der Jahreszahl (vierstellig) 	Beispiele: 1999 oder 2003
"yday" 	Numerische Repräsentation des Tages des Jahres 	zwischen 0 und 365
"weekday" 	Eine vollständige textuelle Repräsentation des Wochentags 	zwischen Sonntag und Sonnabend
"month" 	Eine vollständige textuelle Repräsentation des Monatsnamens, wie Januar oder März 	zwischenJanuar und Dezember
0 	Sekunden seit der Unix Epoche, ähnlich den Werten, die von der Funktion time() zurückgegeben und von der Funktion date() verwendet werden. 	Abhängig vom System, typischerweise ein Wert zwischen -2147483648 und 2147483647.
*/

        /**
         * checks the keys of the $event array and adds defaults for missing
         * ones; if $event is completely empty, it will be filled with the
         * data for the current day (=today)
         *
         * @access private
         * @param  array    &$event
         * @return void
         **/
        private static function initEvent(&$event)
        {
            if(isset($event['timestamp']) && self::isTimestamp($event['timestamp']))
            {
                $event = getdate($event['timestamp']);
            }

            $event['year']      = ( isset($event['year'])    ? $event['year']    : date('Y') );
            $event['mon']       = ( isset($event['mon'])     ? sprintf('%02d',$event['mon'])     : date('m') );
            $event['mday']      = ( isset($event['mday'])    ? $event['mday']    : date('d') );
            $event['weekday']   = ( isset($event['weekday']) ? self::t($event['weekday']) : self::t(date('l')) );
            $event['month']     = ( isset($event['month'])   ? self::t($event['month'])   : self::t(date('F')) );
            $event['weeknum']   = date('W');

            $event['timestamp'] = ( isset($event['timestamp']) ? $event['timestamp'] : strtotime(implode('-',array($event['year'],$event['mon'],$event['mday']))) );

            $event['row']       = ( isset($event['row'])     ? $event['row']     : 1         );
            $event['title']     = ( isset($event['title'])   ? $event['title']   : ''        );
            $event['url']       = ( isset($event['url'])     ? $event['url']     : ''        );
            $event['details']   = ( isset($event['details']) ? $event['details'] : ''        );
        }   // end function initEvent()


        
    }
}