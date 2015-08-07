<?php

/**
 *          _     _  _ _     ______
 *         | |   | |(_) |   (_____ \
 *    _ _ _| |__ | | _| |__   ____) )
 *   | | | |  _ \| || |  _ \ / ____/
 *   | | | | |_) ) || | |_) ) (_____
 *    \___/|____/ \_)_|____/|_______)
 *
 *
 *   @category     wblib2
 *   @package      wbForms
 *   @author       BlackBird Webprogrammierung
 *   @copyright    (c) 2014 BlackBird Webprogrammierung
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU Lesser General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   Lesser General Public License for more details.
 *
 *   You should have received a copy of the GNU Lesser General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 **/

namespace wblib;

if(!class_exists('wbFormsBase',false))
    include dirname(__FILE__).'/wbForms.php';

/**
 * form wizard class; requires wbForms, wbLang and RainTPL
 *
 * @category   wblib2
 * @package    wbFormsWizard
 * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
 * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
 */
if (!class_exists('wbFormsWizard',false))
{
    class wbFormsWizard extends wbFormsBase
    {
        /**
         * logger
         **/
        private   static $analog     = NULL;
        /**
         * log level
         **/
        protected static $loglevel   = 0;
        /**
         * space before log message
         **/
        protected static $spaces     = 0;
        /**
         * instance
         **/
        protected static $instance   = NULL;
        /**
         * RainTPL handle
         **/
        protected static $te         = NULL;
        /**
         * global settings
         **/
        protected static $globals    = array(
            'wblib_url'       => NULL,
            'tpl_dir'         => NULL,
            'passthru_url'    => NULL,
            'config'          => NULL,
        );

        /**
         * Create an instance
         *
         * @access public
         * @return object
         **/
        public static function getInstance()
        {
            self::log('> getInstance()',7);
            if(!is_object(self::$instance))
            {
                self::log('creating new instance',7);
                self::$instance = new self();
            }
            self::log('< getInstance()',7);
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
            if($level>$class::$loglevel) return;
            if( !self::$analog && !self::$analog == -1)
            {
                if(file_exists(dirname(__FILE__).'/3rdparty/Analog/wblib.init.inc.php'))
                {
                    include_once(dirname(__FILE__).'/3rdparty/Analog/wblib.init.inc.php');
                    wblib_init_3rdparty(dirname(__FILE__).'/debug/','wbFormsWizard',$class::$loglevel);
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
         * wrapper for RainTPL
         **/
        public static function tpl()
        {
            if(!is_object(self::$te))
            {
                include dirname(__FILE__)."/3rdparty/raintpl/rain.tpl.class.php";
                \raintpl::$tpl_dir      = dirname(__FILE__)."/templates/wizard/";
                \raintpl::$tpl_ext      = 'tpl';
                \raintpl::$cache_dir    = dirname(__FILE__).'/tmp/';
                \raintpl::$path_replace = false;
                \raintpl::$langh        = \wblib\wbLang::getInstance();
                \raintpl::$langh->setPath(dirname(__FILE__).'/demo/languages');
                \raintpl::$langh->addFile( \raintpl::$langh->getLang().'.php' );
                self::$te            = new \raintpl();
                self::$te->assign(
                    array(
                        'year' => date('Y'),
                        'url'  => self::getURL()
                    )
                );
            }
            return self::$te;
        }   // end function tpl()

        /**
         *
         * @access public
         * @return
         **/
        public static function show($config=NULL)
        {
            if($config && is_array($config) && count($config))
            {
                $js   = array();
                $form = wbForms::getInstance();
                $form->configure('wizard',$config);
                $form->setForm('wizard');
                $elem = $form->getElements();
                if(count($elem))
                {
                    foreach($elem as $e)
                    {
                        $name = isset($e['name']) ? $e['name'] : 'CTRL_X';
                        if($e['type'] == 'legend')
                        {
                            $js[] = 'addElement("","fieldset","'.$e['label'].'");'."\n";
                        }
                        else
                        {
                            $options = '';
                            if($e['type'] == 'radiogroup')
                            {
                                $options = serialize($e['options']);
                            }
                            $js[] = 'addElement("'.(isset($e['id'])?$e['id']:$name).'","'.$e['type'].'","'.$e['label'].'", \''.$options.'\');';
                        }
                    }
                }
                if(is_array(self::$globals['config']) && count(self::$globals['config']))
                {
                    foreach(self::$globals['config'] as $key => $value)
                    {
                        $js[] = 'configData.'.$key.' = "'.$value.'";';
                    }
                }

                self::tpl()->assign('js',implode("\n",$js));
                //self::tpl()->assign('form',$form->getForm());
            }
            if(isset(self::$globals['tpl_dir']) && self::$globals['tpl_dir'])
                \raintpl::$tpl_dir = self::$globals['tpl_dir'];
            self::tpl()->assign('current_lang',\raintpl::$langh->getLang());
            self::tpl()->assign('passthru_url',self::$globals['passthru_url']);
            self::tpl()->draw('index');
        }   // end function show()
        

    }   // ----- end class wbFormsBase -----
}