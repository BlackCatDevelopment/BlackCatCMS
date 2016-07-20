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

/**
 * form builder base class
 *
 * @category   wblib2
 * @package    wbFormsBase
 * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
 * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
 */

if (!class_exists('wblib\wbFormsBase',false))
{
    class wbFormsBase
    {
        /**
         * logger
         **/
        private   static $analog     = NULL;
        /**
         * log level
         **/
        protected static $loglevel   = 4;
        /**
         * space before log message
         **/
        protected static $spaces     = 0;
        /**
         * if no logger is available, store internally
         **/
        protected static $trace      = array();
        /**
         * instance
         **/
        protected static $instance   = NULL;
       /**
         * common attributes for every element
         **/
        protected $attributes = array(
            // HTML attributes
            'accesskey'   => NULL,
            'class'       => NULL,
            'disabled'    => false,
            'id'          => NULL,
            'label'       => NULL,
            'name'        => NULL,
            'onblur'      => NULL,
            'onchange'    => NULL,
            'onclick'     => NULL,
            'onfocus'     => NULL,
            'onselect'    => NULL,
            'readonly'    => NULL,
            'required'    => NULL,
            'style'       => NULL,
            'tabindex'    => NULL,
            'title'       => NULL,
            'type'        => NULL,
            'value'       => NULL,
        );
        /**
         * self contained attributes (no attr="attr" markup)
         **/
        protected $simple_attr    = array(
            'after'       => 1,
            'is_required' => 1,
            'label'       => 1,
            'label_style' => 1,
            'label_span'  => 1,
            'options'     => 1,
            'action'      => 1,
            'enctype'     => 1,
            'content'     => 1,
            'method'      => 1,
            'form_class'  => 1,
            'form_width'  => 1,
            'fieldset'    => 1,
            'notes'       => 1,
        );
        /**
         * internal attributes
         **/
        protected $internal_attrs = array(
            'allow'       => NULL,
            'after'       => NULL,
            'btntype'     => NULL,
            'equal_to'    => NULL,
            'infotext'    => NULL,
            'invalid'     => NULL,
            'is_group'    => NULL,
            'is_required' => NULL,
            'missing'     => NULL,
            'notes'       => NULL,
            'radio_class' => NULL,
        );
        /**
         * element types with no value
         **/
        protected static $nodata = array(
            'buttonline'  => 1,
            'fieldset'    => 1,
            'label'       => 1,
            'legend'      => 1,
            'submit'      => 1,
            'reset'       => 1,
        );
        /**
         * accessor to wbLang if available
         **/
        private   static $wblang     = NULL;
        /**
         * globals for all classes
         **/
        protected static $globals    = array(
            'add_breaks'      => true,
            'add_buttons'     => true,
            'blank_span'      => '<span class="fbblank" style="display:inline-block;width:16px;">&nbsp;</span>',
            'css_prefix'      => NULL,
            'enable_hints'    => true,
            'fallback_path'   => NULL,
            'honeypot_prefix' => 'fbhp_',
            'label_align'     => 'right',
            'path'            => NULL,
            'required_span'   => '<span class="fbrequired" style="display:inline-block;vertical-align:top;width:16px;color:#b94a48;" title="%s">*</span>',
            'token'           => NULL,
            'token_lifetime'  => NULL,
            'var'             => 'FORMS',
            'wblib_url'       => NULL,
            'workdir'         => NULL,
            'wysiwyg_editor'  => 'Aloha',
            'contentonly'     => false,
        );
        /**
         * object attributes
         **/
        public   $attr              = array();

        /**
         * make static functions OOP
         **/
        public function __call($method, $args)
        {
            self::log('> __call()',7);
            self::log(sprintf('searching for method [%s]',$method),7);
            if(count($args))
                self::log('args',var_export($args,1),7);
            if ( ! isset($this) || ! is_object($this) )
            {
                self::log('< __call() - returning false (method not found)',7);
                return false;
            }
            if ( method_exists( $this, $method ) )
            {
                self::log(sprintf('< __call() - calling method [%s]',$method),7);
                return call_user_func_array(array($this, $method), $args);
            }
            else
            {
                self::log('no such method',7);
            }
            self::log('< __call()',7);
        }   // end function __call()

        /**
         * no cloning!
         **/
        private final function __clone()     {}

        /**
         * do not allow to create an instance directly
         **/
        protected     function __construct() {}

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
                    if(isset(self::$globals['lang_path']))
                    {
                        if(is_dir(self::path(self::$globals['lang_path'])))
                        {
                            if(is_dir(self::path(self::$globals['lang_path'])))
                            {
                                self::log(sprintf('adding global lang path [%s]',self::$globals['lang_path']),7);
                                self::$wblang->addPath(self::$globals['lang_path']);
                            }
                            if(
                                   file_exists(self::path(pathinfo(self::$globals['lang_path'],PATHINFO_DIRNAME).'/languages/'.self::$wblang->current().'.php'))
                                || file_exists(self::path(pathinfo(self::$globals['lang_path'],PATHINFO_DIRNAME).'/languages/'.strtoupper(self::$wblang->current()).'.php'))
                                || file_exists(self::path(pathinfo(self::$globals['lang_path'],PATHINFO_DIRNAME).'/languages/'.strtolower(self::$wblang->current()).'.php'))
                            ) {
                                self::log(sprintf('adding file [%s]',self::$wblang->current()),7);
                                self::$wblang->addFile(self::$wblang->current());
                        }
                    }
                    }
                    $callstack = debug_backtrace();
                    $caller    = array_pop($callstack);
                    // avoid deep recursion
                    $i         = 0;
                    while(!strcasecmp(dirname(__FILE__),$caller['file']))
                    {
                        if($i>=3) break;
                        $i++;
                        $caller    = array_pop($callstack);
                    }
                    if(isset($caller['file']))
                    {
                        if(is_dir(self::path(pathinfo($caller['file'],PATHINFO_DIRNAME).'/languages')))
                        {
                            self::log(sprintf('adding path [%s]',self::path(pathinfo($caller['file'],PATHINFO_DIRNAME).'/languages')),7);
                            self::$wblang->addPath(self::path(pathinfo($caller['file'],PATHINFO_DIRNAME).'/languages'));
                        }
                        if(
                               file_exists(self::path(pathinfo($caller['file'],PATHINFO_DIRNAME).'/languages/'.self::$wblang->current().'.php'))
                            || file_exists(self::path(pathinfo($caller['file'],PATHINFO_DIRNAME).'/languages/'.strtoupper(self::$wblang->current()).'.php'))
                            || file_exists(self::path(pathinfo($caller['file'],PATHINFO_DIRNAME).'/languages/'.strtolower(self::$wblang->current()).'.php'))
                        ) {
                            self::log(sprintf('adding file [%s]',self::$wblang->current()),7);
                            self::$wblang->addFile(self::$wblang->current());
                        }
                        // This is for BlackCat CMS, filtering backend paths
                        if(isset($caller['args']) && isset($caller['args'][0]) && is_scalar($caller['args'][0]) && file_exists($caller['args'][0]))
                        {
                            if(is_dir(self::path(pathinfo($caller['args'][0],PATHINFO_DIRNAME).'/languages')))
                                self::$wblang->addPath(self::path(pathinfo($caller['args'][0],PATHINFO_DIRNAME).'/languages'));
                            if(file_exists(self::path(pathinfo($caller['args'][0],PATHINFO_DIRNAME).'/languages/'.self::$wblang->current().'.php')))
                                self::$wblang->addFile(self::$wblang->current());
                        }
                    }
                }
                catch ( wbFormsExection $e )
                {
                    self::log('Unable to load wbLang',7);
                    self::$wblang = -1;
                }
            }
            if( self::$wblang !== -1 )
            {
                self::log('< t(translated)',7);
                return self::$wblang->t($message);
            }
            else
            {
                self::log('< t(original)',7);
                return $message;
            }
        }   // end function t()

        /**
         *
         * @access public
         * @return
         **/
        public static function hint($message)
        {
            if(strlen($message) && self::$globals['enable_hints'])
            {
                $forms = '';
                if(count(wbForms::$FORMS))
                    foreach(wbForms::$FORMS as $name => $elem)
                        $forms .= sprintf(
                            '<tr><td>%s</td><td>%s</td></tr>',
                            $name,count($elem)
                        );
                else
                    $forms = '<tr><td colspan="2">none</td></tr>';

                echo sprintf('
                          <h1>%s</h1>
                          %s<br /><br />
                          <table>
                            <thead>
                              <tr><th colspan="2">Available (loaded) forms</th></tr>
                              <tr><th>Name</th><th>Elements</th></tr>
                            </thead>
                            <tbody>
                              %s
                            </tbody>
                          </table>',
                    self::t('Notice'),self::t('Notice'),self::t($message),$forms
                );
            }
        }   // end function hint()

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
                    wblib_init_3rdparty(dirname(__FILE__).'/debug/','wbForms',$class::$loglevel);
                    self::$analog = true;
                }
                else
                {
                    self::$analog = -1;
                }
            }
            if(substr($message,0,1)=='<')
                self::$spaces--;
            self::$spaces = ( self::$spaces > 0 ? self::$spaces : 0 );
            $line = str_repeat('    ',self::$spaces).$message;
            if(substr($message,0,1)=='>')
                self::$spaces++;
            if ( self::$analog !== -1 )
            {
                \Analog::log($line,$level);
            }
            else
            {
                self::$trace[] = $message;
            }
        }   // end function log()

        /**
         * fixes a path by removing //, /../ and other things
         *
         * @access public
         * @param  string  $path - path to fix
         * @return string
         **/
        public static function path( $path )
        {
            // remove / at end of string; this will make sanitizePath fail otherwise!
            $path       = preg_replace( '~/{1,}$~', '', $path );
            // make all slashes forward
            $path       = str_replace( '\\', '/', $path );
            // bla/./bloo ==> bla/bloo
            $path       = preg_replace('~/\./~', '/', $path);
            // resolve /../
            // loop through all the parts, popping whenever there's a .., pushing otherwise.
            $parts      = array();
            foreach ( explode('/', preg_replace('~/+~', '/', $path)) as $part )
            {
                if ($part === ".." || $part == '')
                    array_pop($parts);
                elseif ($part!="")
                    $parts[] = $part;
            }
            $new_path = implode("/", $parts);
            // windows
            if ( ! preg_match( '/^[a-z]\:/i', $new_path ) )
                $new_path = '/' . $new_path;
            return $new_path;
        }   // end function path()

        /**
         *
         * @access protected
         * @return
         **/
        protected static function getURL()
        {
            $class = get_called_class();
            if(isset($class::$globals['wblib_url']) && $class::$globals['wblib_url'] != '' )
                return $class::$globals['wblib_url'];
            // DOCUMENT_ROOT + SCRIPT_NAME = abs. script path
            // dirname - DOCUMENT_ROOT = rel. dir path
            // HTTP_HOST + ( dirname - DOCUMENT_ROOT ) = my url
            return
                $_SERVER['REQUEST_SCHEME']
                . '://'
                . $_SERVER['HTTP_HOST']
                . '/'
                . str_ireplace(
                      self::fixPath($_SERVER['DOCUMENT_ROOT']),
                      '',
                      str_replace('\\','/',self::fixPath(dirname(__FILE__)))
                  )
            ;
        }   // end function getURL()


        /**
         * generates an unique element name if none is given
         *
         * @access protected
         * @param  integer  $length
         * @return string
         **/
        protected static function generateName($length=8,$prefix='fbformfield_')
        {
            for(
                   $code_length = $length, $newcode = '';
                   strlen($newcode) < $code_length;
                   $newcode .= chr(!rand(0, 2) ? rand(48, 57) : (!rand(0, 1) ? rand(65, 90) : rand(97, 122)))
            );
            return $prefix.$newcode;
        }   // end function generateName()

        /**
         * replaces the placeholders in the output template with the
         * appropriate element options
         *
         * @access protected
         * @return string
         **/
        protected function replaceAttr()
        {
            $class  = get_called_class();
            $output = $class::$tpl;

            self::log(sprintf('output template for class [%s]',$class),7);
            self::log($output,7);
            self::log('element data',7);
            self::log(var_export($this->attr,1),7);

            if(isset($this->attr['id']) && $class::$id_prefix && substr($this->attr['id'],0,strlen($class::$id_prefix)) != $class::$id_prefix)
                $this->attr['id'] = $class::$id_prefix.$this->attr['id'];

            if(isset($this->attr['label']))
            {
                $this->attr['label'] = self::t($this->attr['label']);
                if(
                       !$this instanceof wbFormsElementLabel
                    && !$this instanceof wbFormsElementLegend
                    && !$this instanceof wbFormsElementButton
                    && !$this instanceof wbFormsElementInfo
                ) {
                    $this->checkAttr();
                    $label = new wbFormsElementLabel(
                        array(
                            'for'      => $this->attr['id'],
                            'label'    => $this->attr['label'],
                            'class'    => wbFormsElementLabel::$cssclass,
                            'is_radio' =>
                                (
                                    (
                                           substr($this->attr['type'],0,5) == 'radio'
                                        || substr($this->attr['type'],0,8) == 'checkbox'
                                    )
                                    ? true
                                    : false
                                ),
                        )
                    );
                    $label->checkAttr();
                    $this->attr['label'] = $label->render();
                }
            }

            if($this instanceof wbFormsElementLegend)
                $this->attr['label'] = self::t($this->attr['label']);

            if(isset($this->attr['value']))
                $this->attr['value'] = self::t($this->attr['value']);

            // enumerate CSS class; can be a protected static var
            if(!isset($this->attr['class']))
            {
                $this->attr['class'] = (
                      $class::$cssclass
                    ? $class::$cssclass
                    : (
                          isset($this->attr['type'])
                        ? 'fb'.strtolower($this->attr['type'])
                        : 'fbelement'
                      )
                );
            }

            // highlight errors
            if(isset($this->attr['name']) && isset(wbForms::$ERRORS[$this->attr['name']]))
            {
                $this->attr['class'] .= ' ui-state-highlight';
                $this->attr['notes']  = '<br /><span class="fbnote fberror">'
                                      . self::t(wbForms::$ERRORS[$this->attr['name']])
                                      . '</span>'
                                      . "\n"
                                      ;
            }

            // title attribute, used for tooltips
            if(isset($this->attr['title']))
                $this->attr['title'] = self::t($this->attr['title']);

            $replace = $with = array();
            foreach(array_keys($this->attributes) as $attr)
            {
                if(isset($this->attr[$attr]) && $this->attr[$attr] != '' && ! is_array($this->attr[$attr]))
                {
                    $replace[] = '%'.$attr.'%';
                    $with[]    = ( isset($this->simple_attr[$attr]) )
                               ? $this->attr[$attr]
                               : ' '.$attr.'="'.$this->attr[$attr].'"'
                               ;
                }
            }
            foreach(array_keys($this->internal_attrs) as $attr)
            {
                if(isset($this->attr[$attr]) && $this->attr[$attr] != '')
                {
                    $replace[] = '%'.$attr.'%';
                    $with[]    = ( isset($this->simple_attr[$attr]) )
                               ? $this->attr[$attr]
                               : ' '.$attr.'="'.$this->attr[$attr].'"'
                               ;
                }
            }

//echo "CLASS -$class- OUTPUT -", gettype($output), "-<br />";
            if(!is_scalar($output))
            {
                self::log(sprintf('ERROR: $output is not a scalar! (class [%s])',$class),7);
            }
            else
            {
                self::log('-----> replace: '.var_export($replace,1),7);
                self::log('-----> with   : '.var_export($with,1),7);
                self::log('-----> in     : '.var_export($output,1),7);

                $output = str_ireplace(
                    $replace,
                    $with,
                    $output
                );
                self::log('template after replacing normal placeholders',7);
                self::log($output,7);

                // remove any placeholders not replaced yet
                $output = preg_replace( '~%\w+%~', '', $output );
                self::log('template after removing additional placeholders',7);
                self::log($output,7);
            }

            return $output
                . (
                      (
                             wbForms::$globals['add_breaks']
                          && !$this instanceof wbFormsElementFieldset
                          && !$this instanceof wbFormsElementLegend
                          && !$this instanceof wbFormsElementLabel
                          && !$this instanceof wbFormsElementButton
                          && !$this instanceof wbFormsElementRadio
                          && !$this instanceof wbFormsElementRadiogroup
                      )
                    ? (
                          ( $this instanceof wbFormsElement && isset($this->attr['type']) && $this->attr['type'] !== 'hidden' )
                          ? "<br />\n"
                          : ''
                      )
                    : ''
                  );
        }   // end function replaceAttr()

        /**
         * accessor to $globals array; takes an array of settings as the first
         * param or param name as first and value as second
         *
         * @access public
         * @param  array|string  $global
         * @param  mixed         $value  (optional)
         * @return void
         **/
        public static function set($global,$value=NULL)
        {
            $class = get_called_class();
            if(is_array($global))
            {
                foreach($global as $key => $value)
                    $class::$globals[$key] = $value;
            }
            else
            {
                $class::$globals[$global] = $value;
            }
        }   // end function set()

        /**
         *
         * @access public
         * @return
         **/
        public static function getClass()
        {
            self::log('> getClass()',7);
            $called_class = get_called_class();
            self::log('< getClass()',7);
            return $called_class::$cssclass;
        }   // end function setClass()

        /**
         *
         * @access public
         * @return
         **/
        public static function setClass($cssclass)
        {
            self::log('> setClass()',7);
            $called_class = get_called_class();
            $called_class::$cssclass = $cssclass;
            self::log('< setClass()',7);
        }   // end function setClass()

        /**
         * set attribute value(s)
         *
         * @access public
         * @return
         **/
        public function setAttr($attr,$value)
        {
            self::log('> setAttr()',7);
            $this->attr[$attr] = $value;
            self::log('< setAttr()',7);
        }   // end function setAttr()

        /**
         *
         * @access public
         * @return
         **/
        public function setValue($value)
        {
            self::log('> setValue()',7);
            $field = $this->valueattr();
            $this->attr[$field] = self::t($value);
            self::log('< setValue()',7);
        }   // end function setValue()

        /**
         *
         * @access public
         * @return
         **/
        public function addOption($key,$value=NULL)
        {
            self::log('> addOption()',7);
            if(isset($this->attr['options']) && is_array($this->attr['options']))
                if($value)
                    $this->attr['options'][$key] = self::t($value);
                else
                    $this->attr['options'][] = self::t($key);
            self::log('< addOption()',7);
        }   // end function addOption()

        /**
		 * fixes a path by removing //, /../ and other things
		 *
		 * @access public
		 * @param  string  $path - path to fix
		 * @return string
		 **/
		public static function fixPath($path)
		{
		    $path       = preg_replace( '~/{1,}$~', '', $path );
			$path       = str_replace( '\\', '/', $path );
	        $path       = preg_replace('~/\./~', '/', $path);
	        $parts      = array();
	        foreach ( explode('/', preg_replace('~/+~', '/', $path)) as $part )
	        {
	            if ($part === ".." || $part == '')
	            {
	                array_pop($parts);
	            }
	            elseif ($part!="")
	            {
                    $part = ( mb_detect_encoding($part,'UTF-8',true) )
                          ? utf8_decode($part)
                          : $part;
	                $parts[] = $part;
	            }
	        }
	        $new_path = implode("/", $parts);
	        if ( ! preg_match( '/^[a-z]\:/i', $new_path ) ) {
				$new_path = '/' . $new_path;
			}
	        return $new_path;
		}   // end function fixPath()

    }   // ----------    end class wbFormsBase    ----------
}

/**
 * form builder class
 *
 * @category   wblib2
 * @package    wbForms
 * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
 * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
 */
if (!class_exists('wblib\wbForms',false))
{
    /**
     * form builder form class
     *
     * @category   wblib2
     * @package    wbForms
     * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbForms extends wbFormsBase
    {
        /**
         * list of additional CSS to load
         * public because wbFormsJQuery class uses it
         **/
        public    static $CSS        = array();
        /**
         *
         **/
        public    static $INLINECSS  = array();
        /**
         * same for JavaScripts
         **/
        public    static $JS         = array();
        /**
         * inline JavaScript
         **/
        public    static $INLINEJS   = array();
        /**
         * array of known forms
         **/
        protected static $FORMS      = array();
        /**
         *
         **/
        protected static $CURRENT    = NULL;
        /**
         * current form elements (list of objects)
         **/
        protected static $ELEMENTS   = array();
        /**
         * validated form data
         **/
        protected static $DATA       = array();
        /**
         * list of errors
         **/
        protected static $ERRORS     = array();
        /**
         * list of already loaded files
         **/
        protected static $LOADED     = array();
        /**
         * form attribute defaults
         **/
        protected        $attributes = array();
        /**
         *
         **/
        public           $attr       = array();
        /**
         * output template
         **/
        protected static $tpl        = NULL;

        /**
         *
         * @access public
         * @return
         **/
        public static function destroy()
        {
            self::$instance = NULL;
            self::$CSS      = array();
            self::$INLINECSS  = array();
            self::$JS         = array();
            self::$INLINEJS   = array();
            self::$FORMS      = array();
            self::$CURRENT    = NULL;
            self::$ELEMENTS   = array();
            self::$DATA       = array();
            self::$ERRORS     = array();
            self::$LOADED     = array();
        }   // end function destroy()

        /**
         * Create an instance
         *
         * @access public
         * @param  string  $name - optional name, default: 'default'
         * @return object
         **/
        public static function getInstance()
        {
            self::log('> getInstance()',7);
            if(!is_object(self::$instance))
            {
                self::log('creating new instance',7);
                self::$instance = new wbForms();
                wbFormsJQuery::init();
            }
            // default paths to search inc.forms.php
            $callstack = debug_backtrace();
            self::$globals['workdir']
                = ( isset($callstack[1]) && isset($callstack[1]['file']) )
                ? self::path(realpath(dirname($callstack[0]['file'])))
                : self::path(realpath(dirname(__FILE__)));
            self::$globals['path']          = self::$globals['workdir'].'/forms';
            self::$globals['fallback_path'] = self::$globals['workdir'].'/forms';

            // read and/or recreate a token file; a new token will be created
            // every 24 hours
            $tokenfile = self::$globals['workdir'].'/.token';
            if(!file_exists($tokenfile) || (filemtime($tokenfile)<(time()-24*60*60)))
            {
                $token = self::generateName(16,NULL);
                $fh    = fopen($tokenfile,'w');
                fwrite($fh,$token);
                fclose($fh);
            }
            $fh    = fopen($tokenfile,'r');
            self::$globals['token'] = fread($fh,filesize($tokenfile));
            fclose($fh);

            self::log('< getInstance()',7);
            return self::$instance;
        }   // end function getInstance()

        /**
         * Create an instance by loading the form configuration(s) from file
         *
         * This is a wrapper combining resetGlobals(), loadFile() and
         * getInstance() into one call
         *
         * @access public
         * @param  string  $name - optional name, default: 'default'
         * @param  string  $file - file name
         * @param  string  $path - optional search path
         * @param  string  $var  - optional var name (default: '$FORMS')
         * @return object
         **/
        public static function getInstanceFromFile($file='inc.forms.php',$path=NULL,$var=NULL)
        {
            self::log('> getInstanceFromFile()',7);
            $obj = self::getInstance();
            self::loadFile($file,$path,$var);
            self::log('< getInstanceFromFile()',7);
            return $obj;
        }   // end function getInstanceFromFile()

        /**
         * configures a form based on an array
         *
         * @access public
         * @param  string  $formname
         * @param  array   $array    - array of form elements
         * @return void
         **/
        public static function configure($formname,$array)
        {
            self::log(sprintf('> configure(%s)',$formname),7);
            self::log(var_export($array,1),7);
            self::$FORMS[$formname] = $array;
            self::log('< configure()',7);
        }   // end function configure()

        /**
         * load form configuration from a file
         *
         * @access public
         * @param  string  $file - file name
         * @param  string  $path - optional search path
         * @param  string  $var  - optional var name (default: '$FORMS')
         * @return void
         **/
        public static function loadFile($file='inc.forms.php', $path=NULL, $var=NULL)
        {
            self::log('> loadFile()',7);
            self::log(sprintf('params: file [%s], path [%s], var [%s]',$file,$path,$var),7);
            $var = ( $var ? $var : self::$globals['var'] );
            if(!file_exists($file))
            {
                $search_paths = array(
                    self::$globals['workdir'],
                    self::$globals['path'],
                    self::$globals['fallback_path']
                );
                if($path)
                    array_unshift( $search_paths, self::path($path) );
                foreach($search_paths as $path)
                {
                    if(file_exists($path.'/'.$file))
                    {
                        $file = $path.'/'.$file;
                        break;
                    }
                }
            }
            if(!file_exists($file))
                throw new wbFormsException(
                    sprintf(
                        "Configuration file [%s] not found in the possible search paths!\n[%s]",
                        $file,
                        var_export($search_paths,1)
                    )
                );

            if(isset(self::$LOADED[$file]))
            {
                self::log('already loaded',7);
            }
            else
            {
                try
                {
                    self::log(sprintf('loading file [%s]',$file),7);
                    include $file;
                    $ref = NULL;
                    eval("\$ref = & \$".$var.";");
                    if (isset($ref) && is_array($ref)) {
                        self::log('adding form data',7);
                        self::log(var_export($ref,1),7);
                        self::$FORMS = array_merge(self::$FORMS, $ref);
                    }
                    self::$LOADED[$file] = 1;
                }
                catch ( wbFormsException $e )
                {
                    self::log(sprintf('unable to load the file, exception [%s]',$e->getMessage()),3);
                }
            }

            self::log('< loadFile()',7);
        }   // end function loadFile()

        /**
         * allows to add custom css files; they will be loaded into the HTML
         * header using JavaScript (jQuery)
         *
         * @access public
         * @param  string  $url
         * @return
         **/
        public static function addCSSLink($url)
        {
            self::log('> addCSSLink()',7);
            self::$CSS[] = $url;
            self::log('< addCSSLink()',7);
        }   // end function addCSSLink()

        /**
         * allows to add custom CSS; will be loaded into the HTML
         * header using JavaScript (jQuery) or getHeaders()
         *
         * @access public
         * @param  string  $css
         * @return
         **/
        public static function addCSS($css)
        {
            self::log('> addCSS()',7);
            self::$INLINECSS[] = $css;
            self::log('< addCSS()',7);
        }   // end function addCSSLink()

        /**
         * allows to add custom css files; they will be loaded into the HTML
         * header using JavaScript (jQuery)
         *
         * @access public
         * @param  string  $url
         * @return
         **/
        public static function addJSLink($url,$attr=NULL)
        {
            self::log('> addJSLink()',7);
            self::$JS[] = array(
                'src'  => $url,
                'attr' => $attr,
            );
            self::log('< addJSLink()',7);
        }   // end function addJSLink()

        /**
         *
         *
         *
         *
         **/
        public static function addJS($code)
        {
            self::log('> addJS()',7);
            self::$INLINEJS[] = $code;
            self::log('< addJS()',7);
        }   // end function addJS()

        /**
         * add an element to the current form
         *
         * @access public
         * @param  array   $elem
         * @param  string  $elem_name  - insert after 'name'
         * @param  string  $where      - insert 'before'|'after'|'top', default 'after'
         * @param  string  $find_in    - 'ELEMENTS','FORMS'
         * @return void
         **/
        public static function addElement($elem,$elem_name=NULL,$where='after',$find_in='ELEMENTS')
        {
            self::log('> addElement()',7);

            $name      = self::current();
            $array_ref =& self::${$find_in};
            $element   = NULL;

            self::log(sprintf('current form: %s',$name),7);

            // check for name etc.
            self::checkAttributes($elem);

            // check if element exists
            if(!self::hasElement($elem['name'],$find_in))
            {
                $i = self::getInsertPosition($where,$find_in,$elem_name);

                self::log(sprintf('insert position [%s]',$i),7);
                if(!isset($array_ref[$name]) || !is_array($array_ref[$name]))
                    $array_ref[$name] = array();
                array_splice($array_ref[$name],$i,0,array($elem)); //insert

                // 'create' element
                if(isset($elem['type']) && $find_in == 'ELEMENTS')
                {
                    self::log(sprintf('creating element of type [%s]',$elem['type']),7);
                    $classname = '\wblib\wbFormsElement'.ucfirst(strtolower($elem['type']));
                    self::log(sprintf('classname [%s]',$classname),7);
                    if(class_exists($classname))
                        $element = $classname::get($elem);
                }

                if(!$element)
                {
                    if($find_in == 'ELEMENTS')
                    {
                        // create a placeholder, als array_splice does not work
                        // with objects as replacement
                        array_splice(self::$ELEMENTS[$name],$i,0,'EMPTY');
                        $element =  wbFormsElement::get($elem);
                    }
                    else
                    {
                        $element = $elem;
                    }
                }

                if(!isset($array_ref[$name]) || !count($array_ref[$name]) || $i == -1)
                {
                    self::log('adding at bottom',7);
                    self::log(sprintf('array [%s] current count [%s] key [%s]',$find_in,count($array_ref[$name]),key($array_ref[$name])),7);
                    array_push($array_ref[$name], $element);
                }
                else
                {
                    self::log(sprintf('adding at position [%d]',$i),7);
                    $array_ref[$name][$i] = $element;
                }

                self::log('< addElement() (inside if)',7);
                return $element;
            }
            self::log('< addElement()',7);
        }   // end function addElement()

        /**
         * add a form field; adds to self::$FORMS array
         *
         * @access public
         * @param  array   $field      - field definition (like in inc.forms.php)
         * @param  string  $field_name - insert after 'name'
         * @param  string  $where      - insert 'before' or 'after', default 'after'
         * @return void
         * @return
         **/
        public static function addField($field,$field_name=NULL,$where='after')
        {
            self::log('> addField()',7);

            $name    = self::current();
            $element = NULL;

            self::log(sprintf('current form: %s',$name),7);

            // check for name etc.
            self::checkAttributes($field);

            if(!self::hasElement($field['name'],'FORMS'))
            {
                $i = -1;
            }

            self::log('< addField()',7);
        }   // end function addField()

        /**
         *
         * @access private
         * @return
         **/
        private static function checkAttributes(&$elem)
        {
            if(!isset($elem['name']))
                $elem['name'] = wbFormsElement::generateName();
            // always generate the names of honeypot fields!
            if($elem['type']=='honeypot')
            {
                $elem['name'] = wbFormsElement::generateName(10,self::$globals['honeypot_prefix']);
                $elem['type'] = 'hidden';
            }
            if(!isset($elem['type']))
                $elem['type'] = 'text';
            if(!isset($elem['id']))
            {
                $elem['id'] = $elem['name'];
                if(!substr_compare($elem['id'],'[]',-2,2))
                    $elem['id'] = substr($elem['id'],0,(strlen($elem['id'])-2));
            }
        }   // end function checkAttributes()

        /**
         *
         * @access public
         * @return
         **/
        public static function current()
        {
            return self::$CURRENT;
        }   // end function current()

        /**
         * creates an empty form (if a form is created from scratch instead of
         * a config file)
         *
         * @access public
         * @return
         **/
        public static function createForm($name)
        {
            self::$FORMS[$name] = array();
            self::setForm($name);
        }   // end function createForm()

        /**
         *
         * @access public
         * @return
         **/
        public function createHoneypots($count=1)
        {
            self::log('> createHoneypots()',7);
            $name = self::$CURRENT;
            if(!$name)
            {
                self::hint('[createHoneypots()] No form set; you need to use <tt>setForm(&lt;NAME&gt;)</tt> first!');
                self::log('< createHoneypots() - exit (no form set)',7);
                exit;
            }
            if(!count(self::$ELEMENTS[$name]))
                $this->setForm($name);
            if(is_numeric($count) && $count>0)
                for($i=1;$i<=$count;$i++)
                    $this->addElement(array('type'=>'honeypot'));
            self::log('< createHoneypots()',7);
        }   // end function createHoneypots()

        /**
         * gets insert position for new element
         *
         * @access private
         * @param  string  $where - 'before','after'
         * @param  string  $ref   - array to reference ('ELEMENTS','FORMS')
         * @param  string  $elem_name - optional
         * @return
         **/
        private static function getInsertPosition($where,$ref,$elem_name=NULL)
        {
            self::log('> getInsertPosition()',7);
            self::log(sprintf('where [%s] ref [%s] elem name [%s]',$where,$ref,$elem_name),7);
            $i = self::getPosition($ref,$elem_name);
            if($elem_name && $i)
            {
                if($where=='after')
                    $i++;
                if($where=='before')
                    $i--;
            }
            else
            {
                $arr =& self::${$ref};
                if($where=='before')
                    $i = key(end($arr));
                if($where=='top')
                {
                    $array_ref =& self::${$ref};
                    $name      =  self::current();
                    foreach($array_ref[$name] as $index => $item)
                    {
                        if(
                               is_object($item)
                            && !$this instanceof wbFormsElementLabel
                            && !$this instanceof wbFormsElementLegend
                            && !$this instanceof wbFormsElementButton
                            && !$this instanceof wbFormsElementInfo
                        ){
                            $i = $index;
                            break;
                        }
                        else
                        {
                            if(!in_array($item['type'],array('label','legend','button','info')))
                            {
                                $i = $index;
                                break;
                            }
                        }
                    }
                }
            }
            self::log(sprintf('insert position [%s]',$i),7);
            self::log('< getInsertPosition()',7);
            return $i;
        }   // end function getInsertPosition()

        /**
         *
         *
         *
         *
         **/
        private static function getPosition($ref,$elem_name=NULL)
        {
            self::log('> getPosition()',7);
            self::log(sprintf('ref [%s] elem name [%s]',$ref,$elem_name),7);
            $i = -1;
            if($elem_name)
            {
                self::log(sprintf('searching for element [%s]',$elem_name),7);
                $i    = self::hasElement($elem_name,$ref);
                self::log(sprintf('pos [%s]',$i),7);
            }
            self::log(sprintf('position [%s]',$i),7);
            self::log('< getPosition()',7);
            return $i;
        }   // end function getInsertPosition()

        /**
         * returns a list of available forms
         *
         * @access public
         * @return array
         **/
        public static function listForms()
        {
            $forms = array_keys(self::$FORMS);
            return $forms;
        }   // end function listForms()

        /**
         *
         * @access public
         * @return
         **/
        public static function moveElement($elem_name,$insert_after,$ref='ELEMENTS')
        {
            self::log('> moveElement()',7);
            if(!$ref || $ref=='') $ref = 'ELEMENTS';
            $name = self::current();
            $pos  = 'after';
            $i    = self::getPosition($ref,$elem_name); // current pos
            $n    = NULL;
            if($insert_after && $insert_after!=='')
            {
                if(self::hasElement($insert_after,$ref))
                {
                    $n = self::getPosition($ref,$insert_after);
                }
                if(!$n)
                {
                    if(in_array($insert_after,array('top','bottom')))
                    {
                        $pos = $insert_after;
                    }
                    else
                    {
                        if(is_numeric($insert_after) && count(self::${$ref}[$name])<=$insert_after)
                        {
                            $n = $insert_after;
                        }
                    }
                }
            }
            $elem = array_splice(self::${$ref}[$name],$i,1);
            self::addElement($elem[0],$insert_after,$pos,$ref);
            self::log('< moveElement()',7);
        }   // end function moveElement()

        /**
         * set attribute value(s)
         *
         * @access public
         * @return
         **/
        public function setAttr($attr,$value)
        {
            $f = wbFormsElementForm::get();
            $f->attr[$attr] = $value;
        }

        /**
         *
         * @access public
         * @return
         **/
        public static function setData($data)
        {
            self::log('> setData()',7);
            self::log(var_export($data,1),7);
            if(!is_array($data))
                return false;
            $name = self::$CURRENT;
            foreach($data as $elem => $value)
            {
                self::log(sprintf('searching for element [%s]',$elem),7);
                if(!self::hasElement($elem))
                    continue;
                $obj   = self::getElement($elem);
                $field = $obj->valueattr();
                self::log(sprintf('setting key [%s] to value [%s]',$field,var_export($value,1)),7);
                $obj->setAttr($field,$value);
            }
            self::log('< setData()',7);
        }   // end function setData()

        /**
         * set error text shown above the form
         *
         * @access public
         * @param  string  $msg
         * @return
         **/
        public static function setError($msg)
        {
            self::log('> setError()',7);
            self::log('< setError()',7);
            return self::setInfo($msg,'ui-state-error ui-corner-all fberror');
        }   // end function setInfo()

        /**
         * set info text shown above the form
         *
         * @access public
         * @param  string  $info
         * @return
         **/
        public static function setInfo($info,$class=NULL)
        {
            self::log('> setInfo()',7);
            $name = self::$CURRENT;
            if(!self::hasElement('__wbForms_info__'))
                self::addElement(
                    array(
                        'type'  => 'info',
                        'name'  => '__wbForms_info__',
                        'label' => $info,
                        'class' => ( $class ? $class : NULL )
                    ),
                    NULL,
                    'before'
                );
            self::log('< setInfo()',7);
        }   // end function setInfo()

        /**
         * loads the form elements; this allows to call printHeaders() to load
         * all the CSS and JS into the <head>
         *
         * @access public
         * @param  string  $name   - form name
         * @return boolean
         **/
        public static function setForm($name='default')
        {
            self::log('> setForm()',7);
            if(isset(self::$FORMS[$name]))
            {
                self::log(sprintf('Current form [%s]',$name),7);
                wbForms::$CURRENT = $name;
                if(!count(self::$FORMS[$name]))
                {
                    self::log(sprintf('Required form [%s] has no elements!',$name),4);
                    self::log('< setForm(false) - no elements',7);
                    return false;
                }
                // initialize elements
                foreach(self::$FORMS[$name] as $elem)
                    if(is_array($elem))
                        self::addElement($elem);
                self::log('< setForm(true) (init)',7);
                return true;
            }
            self::log(sprintf('No such form! [%s]',$name),3);
            self::log('< setForm(false)',7);
            return false;
        }   // end function setForm()

        /**
         * removes an element from $find_in, where $find_in is one of
         * 'ELEMENTS' (default), 'FORMS'
         *
         * @access public
         * @param  string  $elem_name  - element to remove
         * @param  string  $find_in    - 'ELEMENTS','FORMS'
         * @return void
         **/
        public static function removeElement($elem_name,$find_in='ELEMENTS')
        {
            self::log('> removeElement()',7);

            $name      = self::current();
            $array_ref =& self::${$find_in};

            if(self::hasElement($elem_name,$find_in))
            {
                $index = self::getPosition($find_in,$elem_name);
                self::log(sprintf('removing element [%s] from [%s] at position [%s]',$elem_name,$find_in,$index),7);
                array_splice($array_ref[$name],$index,1);
            }

            self::log('< removeElement()',7);
        }   // end function removeElement()

        /**
         *
         * @access public
         * @return
         **/
        public static function resetForm($name='default')
        {
            self::log('> resetForm()',7);
            if(isset(self::$FORMS[$name]))
                wbFormsElementForm::reset($name);
            self::log('< resetForm()',7);
        }   // end function resetForm()

        /***********************************************************************
         *    NON STATIC METHODS
         **********************************************************************/

        /**
         * get (render) the form; direct output (echo) by default
         *
         * @access public
         * @param  string  $name   - form to render
         * @param  boolean $return - set to true to return the HTML
         * @return string
         **/
        public function getForm($name=NULL,$return=true)
        {

            self::log('> getForm()',7);

            if(!$name) $name = self::$CURRENT;
            if(!$name)
            {
                self::hint('[getForm()] No form set; you need to use <tt>setForm(&lt;NAME&gt;)</tt> first!');
                self::log('< getForm() - exit (no form set)',7);
                exit;
            }

            if(!count(self::$ELEMENTS[$name]))
                $this->setForm($name);

            if($this->isSent() && !$this->isValidated())
                $this->validateForm();

            self::log('elements: '.var_export(self::$ELEMENTS,1),7);

            // render elements
            $elements = array();
            foreach(self::$ELEMENTS[$name] as $elem)
            {
                if(is_object($elem))
                {
                    $elements[] = $elem->render();
                }
            }

            // add hidden element to check if the form was sent
            if(!self::hasElement('submit_'.$name))
                $elements[] = wbFormsElement::get(array('type'=>'hidden','name'=>'submit_'.$name,'value'=>1))->render();

            // CSRF token
            wbFormsProtect::$config['token_lifetime'] = self::$globals['token_lifetime'];
            $elements[] = wbFormsElement::get(wbFormsProtect::getToken(self::$globals['token']))->render();

            if(self::$globals['contentonly'])
                return implode('',$elements);

            // make sure we have a submit button
            if(!self::hasButton() && self::$globals['add_buttons'])
                $elements[] = wbFormsElementSubmit::get(array('label'=>'Submit'))->render();

            self::log('< getForm()',7);

            // finish the form
            if ( count($elements) )
            {
                if (!$return) echo   wbFormsElementForm::get()->render(implode('',$elements));
                else          return wbFormsElementForm::get()->render(implode('',$elements));
            }
        }   // end function getForm()

        /**
         *
         * @access public
         * @return
         **/
        public static function printForm($name=NULL)
        {
            return self::getForm($name,false);
        }   // end function printForm()

        /**
         *
         * @access public
         * @return
         **/
        public function getHeaders()
        {
            return wbFormsJQuery::getHeaders();
        }   // end function getHeaders()

        /**
         * returns the validated form data
         *
         * @access public
         * @param  boolean $always_return - returns valid data even if the form is not valid
         * @param  boolean $get_empty     - retrieve empty values too; default: false
         * @return array
         **/
        public static function getData($always_return=true,$get_empty=false)
        {
            self::log('> getData()',7);
            $formname = self::current();
            if(!isset(self::$FORMS[$formname]['__is_valid']))
            {
                self::log('calling isValid');
                self::isValid($get_empty);
                self::$FORMS[$formname]['__is_valid'] = false;
            }
            if(!self::$FORMS[$formname]['__is_valid']&&!$always_return)
            {
                self::log('< getData(invalid)',7);
                return NULL;
            }
            return self::$DATA[$formname];
            self::log('< getData()',7);
        }   // end function getData()

        /**
         *
         * @access public
         * @return
         **/
        public static function getDisplayname($formname=NULL)
        {
            self::log('> getDisplayname()',7);
            if(!$formname)
                $formname = self::current();
            if(isset(self::$FORMS[$formname]['display_name']))
                return self::$FORMS[$formname]['display_name'];
            else
                return $formname;
            self::log('< getDisplayname()',7);
        }   // end function getDisplayname()

        /**
         * tries to find element $name in ELEMENTS array; returns the
         * element (object) on success, false otherwise
         *
         * @access public
         * @param  string  $name - element name
         * @return mixed
         **/
        public static function getElement($name)
        {
            self::log('> getElement()',7);
            $formname = self::current();
            self::log(sprintf('searching for element with name [%s], form [%s]',$name,$formname),7);
            if(($index=self::hasElement($name))!==false)
            {
                self::log(sprintf('element found at index [%d]',$index),7);
                self::log(var_export(self::$ELEMENTS[$formname][$index],1),7);
                self::log('< getElement()',7);
                return self::$ELEMENTS[$formname][$index];
            }
            self::log('< getElement() not found',7);
        }   // end function getElement()

        /**
         *
         * @access public
         * @return
         **/
        public static function getElements($ignore_hidden=false,$ignore_labels=false,$find_in='ELEMENTS')
        {
            self::log('> getElements()',7);
            $formname  = self::current();
            $array_ref =& self::${$find_in};
            if(isset($array_ref[$formname]) && count($array_ref[$formname]))
            {
                if($ignore_hidden || $ignore_labels)
                {
                    $elem = array();
                    foreach($array_ref[$formname] as $item)
                    {
                        if($ignore_hidden && ( is_array($item) && $item['type'] == 'hidden') || (is_object($item) && $item->attr['type'] == 'hidden') )
                            continue;
                        if($ignore_labels && ( is_array($item) && $item['type'] == 'legend') || (is_object($item) && $item->attr['type'] == 'legend') )
                            continue;
                        $elem[] = $item;
                    }
                    self::log(sprintf('< getElements() - [%s] elements returned (filtered)',count($elem)),7);
                    return $elem;
                }
                self::log(sprintf('< getElements() - [%s] elements returned',count($array_ref[$formname])),7);
                return $array_ref[$formname];
            }
            self::log('< getElements() - no elements',7);
            return false;
        }   // end function getElements()


        /**
         *
         * @access public
         * @return
         **/
        public static function getErrors()
        {
echo "wbForms::getErrors()<textarea cols=\"100\" rows=\"20\" style=\"width: 100%;\">";
print_r( self::$ERRORS );
echo "</textarea>";
        }   // end function getErrors()

        /**
         *
         * @access public
         * @return
         **/
        public static function getFields()
        {

        }   // end function getFields()


        /**
         * check if the current form has at least one button of the given type
         *
         * @access public
         * @param  string  $type - button type; default: 'submit'
         * @return boolean
         **/
        public static function hasButton($type='submit')
        {
            self::log('> hasButton()',7);
            $name = self::current();
            if(!count(self::$ELEMENTS[$name]))
                $this->setForm($name);
            $class = '\wblib\wbFormsElement'.ucfirst(strtolower($type));
            if(count(self::$ELEMENTS[$name]))
            {
                foreach(self::$ELEMENTS[$name] as $elem)
                {
                    if($elem instanceof $class)
                    {
                        self::log('> hasButton(true)',7);
                        return true;
                    }
                }
            }
            self::log('> hasButton(false)',7);
            return false;
        }   // end function hasButton()

        /**
         * checks if an element with given $name is already defined; searches
         * in ELEMENTS array by default, set $find_in to 'FORMS' to search
         * in fields array
         *
         * @access public
         * @param  string  $name
         * @param  string  $find_in
         * @return
         **/
        public static function hasElement($name,$find_in='ELEMENTS')
        {
            self::log('> hasElement()',7);
            $formname  = self::current();
            $array_ref =& self::${$find_in};
            self::log(sprintf('hasElement() - checking for element [%s] in form [%s], array [%s]',$name,$formname,$find_in),7);
            if(isset($array_ref[$formname]) && count($array_ref[$formname]))
            {
                foreach($array_ref[$formname] as $index => $elem)
                {
                    if($find_in=='ELEMENTS' && !is_object($elem)) continue;
                    if($find_in=='ELEMENTS')
                    {
                        self::log(sprintf('current element [%s]',$elem->attr['id']),7);
                        if(
                               ( isset($elem->attr['id'])   && $elem->attr['id']   == $name )
                            || ( isset($elem->attr['name']) && $elem->attr['name'] == $name )
                        ) {
                            self::log(sprintf('found at index [%d]',$index),7);
                            self::log('< hasElement() (inside if)',7);
                            return $index;
                        }
                    }
                    else
                    {
                        if(
                               ( isset($elem['name'])   && $elem['name']   == $name )
                        ) {
                            self::log(sprintf('current element [%s]',$elem['name']),7);
                            self::log(sprintf('found at index [%d]',$index),7);
                            self::log('< hasElement() (inside if)',7);
                            return $index;
                        }
                    }
                }
            }
            self::log('not found',7);
            self::log('< hasElement()',7);
            return false;
        }   // end function hasElement()

        /**
         *
         * @access public
         * @return
         **/
        public static function hasElementOfType($type,$find_in='ELEMENTS')
        {
            self::log('> hasElementOfType()',7);
            $formname  = self::current();
            $array_ref =& self::${$find_in};
            if(isset($array_ref[$formname]) && count($array_ref[$formname]))
            {
                foreach($array_ref[$formname] as $index => $elem)
                {
                    if($find_in=='ELEMENTS' && !is_object($elem)) continue;
                    if($find_in=='ELEMENTS')
                    {
                        self::log(sprintf('current element [%s]',$elem->attr['id']),7);
                        if(
                               ( isset($elem->attr['type']) && $elem->attr['type'] == $type )
                        ) {
                            self::log(sprintf('found at index [%d]',$index),7);
                            self::log('< hasElementOfType() (inside if)',7);
                            return $index;
                        }
                    }
                    else
                    {
                        if(
                               ( isset($elem['type']) && $elem['type'] == $type )
                        ) {
                            self::log(sprintf('current element [%s]',$elem['name']),7);
                            self::log(sprintf('found at index [%d]',$index),7);
                            self::log('< hasElementOfType() (inside if)',7);
                            return $index;
                        }
                    }
                }
            }
            self::log('not found',7);
            self::log('< hasElementOfType()',7);
        }   // end function hasElementOfType()

        /**
         * check if the form was submitted; checks for submit button by default
         * (default name 'submit_<FORMNAME>')
         *
         * @access public
         * @param  string  $check_field_name - optional field to check
         * @return boolean
         **/
        public static function isSent($check_field_name=NULL)
        {
            self::log('> isSent()',7);
            $formname = self::current();
            if(!$check_field_name)
                $check_field_name = 'submit_'.$formname;
            self::log(sprintf('checking field [%s]',$check_field_name),7);

            $form = wbFormsElementForm::get();
            if( $form->attr['method'] == 'post' )
                $ref =& $_POST;
            else
                $ref =& $_GET;

            if(isset($ref) && isset($ref[$check_field_name]))
            {
                self::log('< isSent(true)',7);
                return true;
            }
            self::log('< isSent(false)',7);
            return false;
        }   // end function isSent()

        /**
         *
         * @access public
         * @return
         **/
        public static function isValid($get_empty=false)
        {
            self::log('> isValid()',7);
            $formname = self::current();
            if(!isset(self::$FORMS[$formname]['__is_valid']))
                self::validateForm($get_empty);
            return isset(self::$FORMS[$formname]['__is_valid'])
                 ? self::$FORMS[$formname]['__is_valid']
                 : false;
        }   // end function isValid()

        /**
         *
         * @access public
         * @return
         **/
        public static function isValidated()
        {
            self::log('> isValidated()',7);
            $formname = self::current();
            self::log('< isValidated()',7);
            return isset(self::$FORMS[$formname]['__is_valid'])
                 ? true
                 : false;
        }   // end function isValidated()


        /**
         * validates the form data
         *
         * @access public
         * @param  boolean $get_empty - retrieve empty values too; default: false
         * @return
         **/
        public static function validateForm($get_empty=false)
        {
            self::log('> validateForm()',7);
            $formname = self::current();
            // reset errors
            self::$ERRORS = array();
            // reset form data
            self::$DATA[$formname] = array();

            $form = wbFormsElementForm::get();
            if( $form->attr['method'] == 'post' )
                $ref =& $_POST;
            else
                $ref =& $_GET;

            self::log('incoming form data:',7);
            self::log(var_export($ref,1),7);

            // retrieve registered elements
            $elements = self::$FORMS[$formname];

            // validate token
            if(wbFormsProtect::checkToken(self::$globals['token']))
            {
                if(!count($elements))
                {
                    self::hint('The given form seems to have no elements!');
                    self::log('< validateForm(false)',7);
                    return false;
                }

                // honeypot fields
                foreach(array_keys($ref) as $name)
                {
                    if(
                        !  substr_compare($name,self::$globals['honeypot_prefix'],0,strlen(self::$globals['honeypot_prefix']))
                        && $ref[$name] != ''
                    ) {
                        wbForms::setError(
                            self::t('You filled a honeypot field. Are you sure you are a human?')
                        );
                        self::log('honeypot field found',7);
                        self::log('< validateForm(false)',7);
                        return false;
                    }
                }

                // check
                foreach($elements as $elem)
                {
                    if(is_object($elem)) { continue; }
                    if(isset($elem['type']) && array_key_exists($elem['type'],self::$nodata) )
                        continue;
                    if(!isset($elem['name']))
                    {
                        if(isset($elem['id']))
                        {
                            $elem['name'] = $elem['id'];
                        }
                        else
                        {
                            self::log('missing element name!',1);
                            continue;
                        }
                    }
                    // remove [] from array fields
                    $elem['name'] = str_replace('[]','',$elem['name']);
                    // check required
                    if(isset($elem['required'])&&!isset($ref[$elem['name']]))
                    {
                        self::$ERRORS[$elem['name']]
                            = isset($elem['missing'])
                            ? self::t($elem['missing'])
                            : self::t('Please insert a value for this field')
                            ;
                        self::log(sprintf('no data for required field [%s]',$elem['name']),7);
                        continue;
                    }
                    // check allowed
                    if(isset($elem['allow']) && isset($ref[$elem['name']]) && $ref[$elem['name']] !== '')
                    {
                        // scalar; example: 'number', 'string', 'int:5:15'
                        if(is_scalar($elem['allow']))
                        {
                            if(substr_count($elem['allow'],':'))
                                list($type,$opt) = explode(':',$elem['allow'],2);
                            else
                                $type = $elem['allow']; $opt = NULL;

                            $method = 'check_'.$type;
                            if(method_exists('\wblib\wbFormsProtect',$method))
                            {
                                if( ($value=wbFormsProtect::$method($ref[$elem['name']],$opt)) === false )
                                {
                                    self::$ERRORS[$elem['name']]
                                        = isset($elem['invalid'])
                                        ? self::t($elem['invalid'])
                                        : self::t('You passed an invalid value').( self::$loglevel==7 ? ' (method: '.$method.')' : '' )
                                        ;
                                    self::log(sprintf('invalid data for field [%s], allowed [%s]',$elem['name'],$elem['allow']),7);
                                    continue;
                                }
                                else
                                {
                                    $ref[$elem['name']] = $value;
                                }
                            }
                            elseif(is_callable('is_'.$type))
                            {
                                $method = 'is_'.$type;
                                if(!$method($ref[$elem['name']]))
                                {
                                    self::$ERRORS[$elem['name']]
                                        = isset($elem['invalid'])
                                        ? self::t($elem['invalid'])
                                        : self::t('You passed an invalid value').( self::$loglevel==7 ? ' (method: '.$method.')' : '' )
                                        ;
                                    self::log(sprintf('invalid data for field [%s], allowed [%s]',$elem['name'],$elem['allow']),7);
                                    continue;
                                }
                            }
                            else
                            {
                                self::log(sprintf('Invalid check method [%s]!',$method),1);
                                self::log(sprintf('Invalid check method [%s]! (element [%s])',$method,$elem['name']),7);
                                continue;
                            }
                        }
                        // array of allowed values
                        elseif(is_array($elem['allow']))
                        {
                            if(!wbFormsProtect::check_array($ref[$elem['name']],$elem['allow']))
                            {
                                self::$ERRORS[$elem['name']]
                                    = isset($elem['invalid'])
                                    ? self::t($elem['invalid'])
                                    : self::t('You passed an invalid value')
                                    ;
                                self::log(sprintf('invalid data for field [%s], allowed [%s]',$elem['name'],implode(', ',$elem['allow'])),7);
                                continue;
                            }
                        }
                    }   // end check allowed

                    // add to validated data
                    if(isset($ref[$elem['name']]) || $get_empty )
                    {
                        self::log(sprintf(
                            'storing data for field [%s], value:',$elem['name']
                        ), (isset($ref[$elem['name']])?$ref[$elem['name']]:'')
                        ,7);
                        if(isset($ref[$elem['name']]) && $ref[$elem['name']] !== '')
                            self::$DATA[$formname][$elem['name']] = $ref[$elem['name']];
                        elseif($get_empty)
                            self::$DATA[$formname][$elem['name']] = '';
                    }
                }
                if(count(self::$ERRORS))
                {
                    self::log('< validateForm(false)',7);
                    self::$FORMS[$formname]['__is_valid'] = false;
                    return false;
                }
                self::log('< validateForm(true)',7);
                self::$FORMS[$formname]['__is_valid'] = true;
                return true;
            }
            else
            {
                self::log('invalid token!',7);
            }
            self::log('< validateForm()',7);
        }   // end function validateForm()

        /**
         *
         * @access public
         * @return
         **/
        public static function dump()
        {
            $formname = self::current();
            print_r(self::$FORMS[$formname]);
        }   // end function dump()

    }   // ----------      end class wbForms      ----------

    /**
     * form builder element base class
     *
     * @category   wblib2
     * @package    wbFormsElement
     * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbFormsElement extends wbFormsBase
    {
        /**
         * output template
         **/
        protected static $tpl = NULL;
        /**
         * default css class
         **/
        protected static $cssclass = NULL;
        /**
         * id prefix (empty by default)
         **/
        protected static $id_prefix = NULL;
         /**
         * creates a new form element
         *
         * @access public
         * @param  array  $options
         * @return object
         **/
        protected function __construct($options=array())
        {
            self::log('> __construct()',7);
            $this->init();
            $class = get_called_class();
            self::log(sprintf('element class %s',$class),7);
            if($class::$tpl == '')
                $class::$tpl =
                     // markup for <label>
                     "%label%"
                     // markup for required fields
                   .  "%is_required%"
                     // default attributes
                   . "<input%type%%name%%id%%class%%style%%title%%value%%required%"
                     // more attributes
                   . "%tabindex%%accesskey%%disabled%%readonly%%onblur%%onchange%%onclick%%onfocus%%onselect% /> %after%"
                   . "\n"
                     // errors and other infos
                   . "%notes%"
                   ;

            foreach($this->attributes as $key => $default)
            {
                if(isset($options[$key]))
                    $this->attr[$key] = $options[$key];
                else
                    if($default)
                        $this->attr[$key] = $default;
            }

            foreach(array_keys($this->internal_attrs) as $key)
            {
                if(isset($options[$key]))
                    $this->attr[$key] = $options[$key];
            }

            self::log(sprintf('element of type [%s] initialized with attr',get_class($this)),7);
            self::log(var_export($this->attr,1),7);
            self::log('< __construct()',7);
        }   // end function __construct()

        /**
         * function prototype, element classes may override this to add
         * custom attributes
         *
         * @access public
         * @return object (chainable)
         **/
        public function init() { return $this; }   // end function init()

        /**
         * allows to override the default CSS class for an element
         *
         * @access public
         * @param  string  $class
         * @return void
         **/
        public static function setClass($css)
        {
            self::log('> setClass()',7);
            $called = get_called_class();
            $called::$cssclass = $css;
            self::log('< setClass()',7);
        }   // end function setClass()

        /**
         * allows to set a prefix that is added to the element ID (only if no
         * explicit ID is given)
         *
         * @access public
         * @param  string  $prefix
         * @return void
         **/
        public static function setIDPrefix($prefix)
        {
            self::log('> setIDPrefix()',7);
            $class = get_called_class();
            $class::$id_prefix = $prefix;
            self::log('< setIDPrefix()',7);
        }   // end function setIDPrefix()

        /**
         * allows to override the output template
         *
         * @access public
         * @return
         **/
        public static function setTemplate($tpl)
        {
            self::log('> setTemplate()',7);
            $class = get_called_class();
            $class::$tpl = $tpl;
            self::log('< setTemplate()',7);
        }   // end function setTemplate()
        
        /**
         * returns an instance (normally called 'getInstance()')
         *
         * @access public
         * @param  array  $options
         * @return
         **/
        public static function get($options=array())
        {
            self::log('> get()',7);
            $class = '\\'.get_called_class();
            self::log(sprintf('creating element of class [%s]',$class),7);
            self::log(sprintf('< get(%s)',$class),7);
            return new $class($options);
        }   // end function get()

        /**
         * checks for required attributes like 'id' and 'name'
         *
         * @access public
         * @return void
         **/
        public function checkAttr()
        {
            if(
                   $this instanceof wbFormsElementRadiogroup
                || $this instanceof wbFormsElementCheckboxgroup
//                || $this instanceof wbFormsElementSelect
//                || $this instanceof wbFormsElementImageselect
            ) {
                if(!isset($this->attr['options']) || !is_array($this->attr['options']))
                    $this->attr['options'] = array();
            }
            if(isset($this->attr['required']) && $this->attr['required'] !== false)
            {
                #if(!isset($this->attr['is_group']) || !$this->attr['is_group'])
                    $this->attr['is_required'] = sprintf(self::$globals['required_span'],self::t('This item is required'));
                $this->attr['required'] = 'required'; // valid XHTML
            }
            else
            {
                if(!isset($this->attr['is_group']) || !$this->attr['is_group'])
                    $this->attr['is_required'] = self::$globals['blank_span'];
            }
            self::log('attributes: '.var_export($this->attr,1),7);
        }   // end function checkAttr()

        /**
         * default render method for most element types; for those who need
         * different markup, this method must be overridden
         *
         * @access public
         * @return string  - HTML
         **/
        public function render()
        {
            return $this->replaceAttr();
    	}   // end function render()

        /**
         *
         * @access public
         * @return
         **/
        public function valueattr()
        {
            return 'value';
        }   // end function valueattr()

    }   // ----------   end class wbFormsElement  ----------

    /**
     * form builder form element class
     *
     * @category   wblib2
     * @package    wbFormsElementForm
     * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbFormsElementForm extends wbFormsElement
    {
        private static   $instances  = array();
        protected        $attributes = array(
            // <form> attributes
            'action'       => NULL,
            'method'       => NULL,
            'id'           => NULL,
            'name'         => NULL,
            'class'        => NULL,
            'enctype'      => NULL,
            // internal attributes
            'content'      => NULL,
            'form_class'   => NULL,
            'form_width'   => NULL,
            'fieldset'     => NULL,
            'buttonline'   => NULL,
        );
        public                $attr = array(
            'action'       => NULL,
            'method'       => 'post',
            'id'           => NULL,
            'name'         => NULL,
            'class'        => 'ui-widget',
            'enctype'      => 'application/x-www-form-urlencoded',
            // internal attributes
            'content'      => NULL,
            'form_class'   => 'fbform',
            'form_width'   => '',
        );
        /**
         * output template
         **/
        protected static $tpl
            = "<div class=\"%form_class%\" style=\"width:%form_width%\">\n<form action=\"%action%\" enctype=\"%enctype%\" method=\"%method%\" %name%%id%%class%%style%>\n%content%\n%fieldset%\n%buttonline%</form>\n</div>\n";

        public function init()
        {
            $this->attr['id']   = wbForms::current();
            $this->attr['name'] = wbForms::current();
            return $this;
        }   // end function init()

        /**
         * returns an instance (normally called 'getInstance()')
         *
         * @access public
         * @param  array  $options
         * @return
         **/
        public static function get($options=array())
        {
            $name = wbForms::current();
            if(!isset(self::$instances[$name]))
                self::$instances[$name] = new self($options);
            return self::$instances[$name];
        }   // end function get()

        /**
         * render the form
         *
         * @access public
         * @param  string  $elements
         * @return string
         **/
        public function render()
        {
            self::log('> wbFormsElementForm::render()',7);
            $elements = func_get_arg(0);
            // check enctype
            if($this->attr['method'] == 'post') $this->attr['enctype'] = 'multipart/form-data';
            // render form
            $this->attr['content']  = $elements;
            // make sure to close the buttonline
            $this->attr['content'] .= wbFormsElementButtonline::get()->close();
            // make sure to close last fieldset
            $this->attr['fieldset'] = wbFormsElementFieldset::get()->close();
            $output  = $this->replaceAttr();
            // add jQuery elements
            $output .= wbFormsJQuery::render();
            self::log('< render()',7);
            return $output;
        }   // end function render()

        /**
         *
         * @access public
         * @return
         **/
        public static function reset($name)
        {
            unset(self::$instances[$name]);
        }   // end function reset()


    }   // ----------   end class wbFormsElementForm  ----------

/*******************************************************************************
 * special field types
 ******************************************************************************/

    /**
     * form builder info element class
     *
     * @category   wblib2
     * @package    wbFormsElementInfo
     * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbFormsElementInfo extends wbFormsElement
    {
        protected static $tpl
            = '<div%class%%style%><span style="float:left;margin-right:.3em;" class="ui-icon ui-icon-info"></span>%label%</div>';
        protected static $cssclass = 'fbinfo ui-widget ui-widget-content ui-corner-all ui-helper-clearfix ui-state-highlight';
        /**
         * adds select specific attributes
         **/
        public function init()
        {
            $this->attributes['label'] = NULL;
            return $this;
        }
    }   // ---------- end class wbFormsElementInfo ----------

    /**
     * form builder fieldset element class
     *
     * @category   wblib2
     * @package    wbFormsElementFieldset
     * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbFormsElementFieldset extends wbFormsElement
    {
        private   static $is_open  = false;
        protected static $tpl      = '<fieldset%class%%style%>';
        protected static $cssclass = 'ui-widget ui-widget-content ui-corner-all ui-helper-clearfix';
        public function init()
        {
            $this->attr['style'] = 'margin-bottom:15px;';
            return $this;
        }   // end function init()

        /**
         * open a <fieldset>; this also closes any fieldset that was opened
         * before
         *
         * please note that fieldsets cannot be nested!
         *
         * @access public
         * @return string
         **/
        public function open()
        {
            $close = self::close();
            self::$is_open = true;
            return $close.$this->replaceAttr()."\n";
        }   // end function open()

        /**
         * closes a <fieldset>
         *
         * @access public
         * @return string
         **/
        public function close()
        {
            if(self::$is_open)
            {
                self::$is_open = false;
                return '</fieldset>';
            }
        }   // end function close()
    }   // ---------- end class wbFormsElementFieldset ----------

    /**
     * form builder legend element class; auto-opens a fieldset
     *
     * @category   wblib2
     * @package    wbFormsElementLegend
     * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbFormsElementLegend extends wbFormsElement
    {
        protected static $tpl
            = "<legend%id%%class%%style%>%label%</legend>\n";
        protected static $cssclass = 'ui-widget ui-widget-header ui-corner-all';
        public function init()
        {
            //$this->attr['style'] = 'padding: 5px 10px;';
            return $this;
        }
        /**
         *
         * @access public
         * @return
         **/
        public function render()
        {
             return
                   wbFormsElementFieldset::get()->open()
                 . $this->replaceAttr();
        }   // end function render()
    }   // ---------- end class wbFormsElementLegend ----------

    /**
     * form builder label element class
     *
     * @category   wblib2
     * @package    wbFormsElementLabel
     * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbFormsElementLabel extends wbFormsElement
    {
        protected static $tpl
            = '<label%for%%class%%style%>%label%</label>';
        protected static $cssclass = 'fblabel';
        /**
         * adds select specific attributes
         **/
        public function init()
        {
            $this->attributes['is_radio'] = NULL;
            $this->attributes['for']      = NULL;
            $this->attributes['label']    = NULL;
            unset($this->attributes['type']);
            return $this;
        }
        public function render()
        {
            $this->checkAttr();
            if(!isset($this->attr['for']))
                $this->attr['for'] = $this->attr['id'];
            // remove style from radio fields
            if(isset($this->attr['style']))
                if($this->attr['is_radio'] && $this->attr['style'] == '' )
                    unset( $this->attr['style'] );
            // remove class from radio fields
/*
            if(isset($this->attr['class']))
                if($this->attr['is_radio'] && $this->attr['class'] == self::$class)
                    unset( $this->attr['class'] );
*/
            return $this->replaceAttr();
        }   // end function render()

    }   // ---------- end class wbFormsElementLabel ----------

    /**
     *
     **/
    class wbFormsElementColor extends wbFormsElement
    {
        /**
         *
         * @access public
         * @return
         **/
        public function render()
        {
            $this->checkAttr();
            $this->attr['type']  = 'text';
            $this->attr['class'] = 'fbcolorpicker';
            return $this->replaceAttr();
        }   // end function init()

    }

    /**
     * form builder select element class
     *
     * @category   wblib2
     * @package    wbFormsElementSelect
     * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbFormsElementSelect extends wbFormsElement
    {
        protected static $tpl = "%label%%is_required%\n<select%name%%id%%class%%style%%title%%multiple%%tabindex%%accesskey%%disabled%%readonly%%required%%onblur%%onchange%%onclick%%onfocus%%onselect%>\n%options%</select> %after%\n";
        /**
         * adds select specific attributes
         **/
        public function init()
        {
            $this->attributes['options']  = NULL;
            $this->attributes['selected'] = NULL;
            $this->attributes['multiple'] = NULL;
            return $this;
        }
        /**
         * render select
         **/
        public function render()
        {
            $this->checkAttr();
            if(!isset($this->attr['options'])) $this->attr['options'] = array();
            $options   = array();
            $isIndexed = array_values($this->attr['options']) === $this->attr['options'];
            $sel       = array();
            if(isset($this->attr['selected']))
                $sel[$this->attr['selected']] = 'selected="selected"';
            if(isset($this->attr['multiple']))
                $this->attr['multiple'] = 'multiple="multiple"';
            if(count($this->attr['options']))
            {
                $opt_group_open = false;
                if($isIndexed)
                {
                    foreach($this->attr['options'] as $item)
                    {
                        $options[] = '<option value="'.$item.'" '.( isset($sel[$item]) ? $sel[$item] : '' ).'>'.$this->t($item).'</option>'."\n";
                    }
                }
                else
                {
                    foreach($this->attr['options'] as $value => $item)
                    {
                        $options[] = '<option value="'.$value.'" '.( isset($sel[$value]) ? $sel[$value] : '' ).'>'.$this->t($item).'</option>'."\n";
                    }
                }
            }
            $this->attr['options'] = implode('',$options);
            return $this->replaceAttr();
        }   // end function render()
        /**
         * returns the name of the attribute that contains the selected value
         * ('selected')
         **/
        public function valueattr()
        {
            return 'selected';
        }   // end function valueattr()
    }   // ---------- end class wbFormsElementSelect ----------

    /**
     * form builder select country class
     *
     * @category   wblib2
     * @package    wbFormsElementCountryselect
     * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbFormsElementCountryselect extends wbFormsElementSelect
    {
        // created:
        // http://www.countries-list.info/Download-List
        protected static $countryList = array(
        	"AF" => "Afghanistan",
        	"AX" => "Alandinseln",
        	"AL" => "Albanien",
        	"DZ" => "Algerien",
        	"UM" => "Amerikanisch-Ozeanien",
        	"AS" => "Amerikanisch-Samoa",
        	"VI" => "Amerikanische Jungferninseln",
        	"AD" => "Andorra",
        	"AO" => "Angola",
        	"AI" => "Anguilla",
        	"AQ" => "Antarktis",
        	"AG" => "Antigua und Barbuda",
        	"AR" => "Argentinien",
        	"AM" => "Armenien",
        	"AW" => "Aruba",
        	"AZ" => "Aserbaidschan",
        	"AU" => "Australien",
        	"BS" => "Bahamas",
        	"BH" => "Bahrain",
        	"BD" => "Bangladesch",
        	"BB" => "Barbados",
        	"BY" => "Belarus",
        	"BE" => "Belgien",
        	"BZ" => "Belize",
        	"BJ" => "Benin",
        	"BM" => "Bermuda",
        	"BT" => "Bhutan",
        	"BO" => "Bolivien",
        	"BA" => "Bosnien und Herzegowina",
        	"BW" => "Botsuana",
        	"BV" => "Bouvetinsel",
        	"BR" => "Brasilien",
        	"VG" => "Britische Jungferninseln",
        	"IO" => "Britisches Territorium im Indischen Ozean",
        	"BN" => "Brunei Darussalam",
        	"BG" => "Bulgarien",
        	"BF" => "Burkina Faso",
        	"BI" => "Burundi",
        	"CL" => "Chile",
        	"CN" => "China",
        	"CK" => "Cookinseln",
        	"CR" => "Costa Rica",
        	"CI" => "Côte d’Ivoire",
        	"CD" => "Demokratische Republik Kongo",
        	"KP" => "Demokratische Volksrepublik Korea",
        	"DE" => "Deutschland",
        	"DM" => "Dominica",
        	"DO" => "Dominikanische Republik",
        	"DJ" => "Dschibuti",
        	"DK" => "Dänemark",
        	"EC" => "Ecuador",
        	"SV" => "El Salvador",
        	"ER" => "Eritrea",
        	"EE" => "Estland",
        	"FK" => "Falklandinseln",
        	"FJ" => "Fidschi",
        	"FI" => "Finnland",
        	"FR" => "Frankreich",
        	"GF" => "Französisch-Guayana",
        	"PF" => "Französisch-Polynesien",
        	"TF" => "Französische Süd- und Antarktisgebiete",
        	"FO" => "Färöer",
        	"GA" => "Gabun",
        	"GM" => "Gambia",
        	"GE" => "Georgien",
        	"GH" => "Ghana",
        	"GI" => "Gibraltar",
        	"GD" => "Grenada",
        	"GR" => "Griechenland",
        	"GL" => "Grönland",
        	"GP" => "Guadeloupe",
        	"GU" => "Guam",
        	"GT" => "Guatemala",
        	"GG" => "Guernsey",
        	"GN" => "Guinea",
        	"GW" => "Guinea-Bissau",
        	"GY" => "Guyana",
        	"HT" => "Haiti",
        	"HM" => "Heard- und McDonald-Inseln",
        	"HN" => "Honduras",
        	"IN" => "Indien",
        	"ID" => "Indonesien",
        	"IQ" => "Irak",
        	"IR" => "Iran",
        	"IE" => "Irland",
        	"IS" => "Island",
        	"IM" => "Isle of Man",
        	"IL" => "Israel",
        	"IT" => "Italien",
        	"JM" => "Jamaika",
        	"JP" => "Japan",
        	"YE" => "Jemen",
        	"JE" => "Jersey",
        	"JO" => "Jordanien",
        	"KY" => "Kaimaninseln",
        	"KH" => "Kambodscha",
        	"CM" => "Kamerun",
        	"CA" => "Kanada",
        	"CV" => "Kap Verde",
        	"KZ" => "Kasachstan",
        	"QA" => "Katar",
        	"KE" => "Kenia",
        	"KG" => "Kirgisistan",
        	"KI" => "Kiribati",
        	"CC" => "Kokosinseln",
        	"CO" => "Kolumbien",
        	"KM" => "Komoren",
        	"CG" => "Kongo",
        	"HR" => "Kroatien",
        	"CU" => "Kuba",
        	"KW" => "Kuwait",
        	"LA" => "Laos",
        	"LS" => "Lesotho",
        	"LV" => "Lettland",
        	"LB" => "Libanon",
        	"LR" => "Liberia",
        	"LY" => "Libyen",
        	"LI" => "Liechtenstein",
        	"LT" => "Litauen",
        	"LU" => "Luxemburg",
        	"MG" => "Madagaskar",
        	"MW" => "Malawi",
        	"MY" => "Malaysia",
        	"MV" => "Malediven",
        	"ML" => "Mali",
        	"MT" => "Malta",
        	"MA" => "Marokko",
        	"MH" => "Marshallinseln",
        	"MQ" => "Martinique",
        	"MR" => "Mauretanien",
        	"MU" => "Mauritius",
        	"YT" => "Mayotte",
        	"MK" => "Mazedonien",
        	"MX" => "Mexiko",
        	"FM" => "Mikronesien",
        	"MC" => "Monaco",
        	"MN" => "Mongolei",
        	"ME" => "Montenegro",
        	"MS" => "Montserrat",
        	"MZ" => "Mosambik",
        	"MM" => "Myanmar",
        	"NA" => "Namibia",
        	"NR" => "Nauru",
        	"NP" => "Nepal",
        	"NC" => "Neukaledonien",
        	"NZ" => "Neuseeland",
        	"NI" => "Nicaragua",
        	"NL" => "Niederlande",
        	"AN" => "Niederländische Antillen",
        	"NE" => "Niger",
        	"NG" => "Nigeria",
        	"NU" => "Niue",
        	"NF" => "Norfolkinsel",
        	"NO" => "Norwegen",
        	"MP" => "Nördliche Marianen",
        	"OM" => "Oman",
        	"TL" => "Osttimor",
        	"PK" => "Pakistan",
        	"PW" => "Palau",
        	"PS" => "Palästinensische Gebiete",
        	"PA" => "Panama",
        	"PG" => "Papua-Neuguinea",
        	"PY" => "Paraguay",
        	"PE" => "Peru",
        	"PH" => "Philippinen",
        	"PN" => "Pitcairn",
        	"PL" => "Polen",
        	"PT" => "Portugal",
        	"PR" => "Puerto Rico",
        	"KR" => "Republik Korea",
        	"MD" => "Republik Moldau",
        	"RW" => "Ruanda",
        	"RO" => "Rumänien",
        	"RU" => "Russische Föderation",
        	"RE" => "Réunion",
        	"SB" => "Salomonen",
        	"ZM" => "Sambia",
        	"WS" => "Samoa",
        	"SM" => "San Marino",
        	"SA" => "Saudi-Arabien",
        	"SE" => "Schweden",
        	"CH" => "Schweiz",
        	"SN" => "Senegal",
        	"RS" => "Serbien",
        	"CS" => "Serbien und Montenegro",
        	"SC" => "Seychellen",
        	"SL" => "Sierra Leone",
        	"ZW" => "Simbabwe",
        	"SG" => "Singapur",
        	"SK" => "Slowakei",
        	"SI" => "Slowenien",
        	"SO" => "Somalia",
        	"HK" => "Sonderverwaltungszone Hongkong",
        	"MO" => "Sonderverwaltungszone Macao",
        	"ES" => "Spanien",
        	"LK" => "Sri Lanka",
        	"BL" => "St. Barthélemy",
        	"SH" => "St. Helena",
        	"KN" => "St. Kitts und Nevis",
        	"LC" => "St. Lucia",
        	"MF" => "St. Martin",
        	"PM" => "St. Pierre und Miquelon",
        	"VC" => "St. Vincent und die Grenadinen",
        	"SD" => "Sudan",
        	"SR" => "Suriname",
        	"SJ" => "Svalbard und Jan Mayen",
        	"SZ" => "Swasiland",
        	"SY" => "Syrien",
        	"ST" => "São Tomé und Príncipe",
        	"ZA" => "Südafrika",
        	"GS" => "Südgeorgien und die Südlichen Sandwichinseln",
        	"TJ" => "Tadschikistan",
        	"TW" => "Taiwan",
        	"TZ" => "Tansania",
        	"TH" => "Thailand",
        	"TG" => "Togo",
        	"TK" => "Tokelau",
        	"TO" => "Tonga",
        	"TT" => "Trinidad und Tobago",
        	"TD" => "Tschad",
        	"CZ" => "Tschechische Republik",
        	"TN" => "Tunesien",
        	"TM" => "Turkmenistan",
        	"TC" => "Turks- und Caicosinseln",
        	"TV" => "Tuvalu",
        	"TR" => "Türkei",
        	"UG" => "Uganda",
        	"UA" => "Ukraine",
        	"ZZ" => "Unbekannte oder ungültige Region",
        	"HU" => "Ungarn",
        	"UY" => "Uruguay",
        	"UZ" => "Usbekistan",
        	"VU" => "Vanuatu",
        	"VA" => "Vatikanstadt",
        	"VE" => "Venezuela",
        	"AE" => "Vereinigte Arabische Emirate",
        	"US" => "Vereinigte Staaten",
        	"GB" => "Vereinigtes Königreich",
        	"VN" => "Vietnam",
        	"WF" => "Wallis und Futuna",
        	"CX" => "Weihnachtsinsel",
        	"EH" => "Westsahara",
        	"CF" => "Zentralafrikanische Republik",
        	"CY" => "Zypern",
        	"EG" => "Ägypten",
        	"GQ" => "Äquatorialguinea",
        	"ET" => "Äthiopien",
        	"AT" => "Österreich",
        );


       /**
         *
         * @access public
         * @return
         **/
        public function render()
        {
            if(!isset($this->attr['options']))
                $this->attr['options'] = self::$countryList;
            $this->checkAttr();
            return parent::render();
        }   // end function init()
    }

    /**
     * form builder select element class
     *
     * @category   wblib2
     * @package    wbFormsElementSelect
     * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbFormsElementImageselect extends wbFormsElementSelect
    {
        /**
         *
         * @access public
         * @return
         **/
        public function render()
        {
            $this->checkAttr();
//            wbFormsJQuery::attach($this->attr['id'],'imagepicker');
            return parent::render();
        }   // end function init()

    }

    /**
     * form builder textarea element class
     *
     * @category   wblib2
     * @package    wbFormsElementTextarea
     * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbFormsElementTextarea extends wbFormsElement
    {
        protected static $tpl =
            "%is_required%<input%type%%name%%id%%class%%style%%title%%value%%required%%checked%%tabindex%%accesskey%%disabled%%readonly%%onblur%%onchange%%onclick%%onfocus%%onselect% />%label%";
        public function init()
        {
            wbFormsElementTextarea::$tpl
                = '%label%%is_required%'
                . '<textarea %name%%id%%class%%style%%title%'
                . '%tabindex%%accesskey%%disabled%%readonly%%required%%onblur%%onchange%%onclick%%onfocus%%onselect%>'
                . '%value%'
                . '</textarea> %after%'
                ;
            $this->simple_attr['value'] = 1;
        }
    }   // ---------- end class wbFormsElementTextarea ----------

    /**
     * form builder radio element class
     *
     * @category   wblib2
     * @package    wbFormsElementRadio
     * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbFormsElementRadio extends wbFormsElement
    {
        protected static $cssclass = 'fbradio';
        protected static $tpl = NULL;
        public function init()
        {
            $this->attributes['checked']  = NULL;
            $this->attributes['is_group'] = false;
            return $this;
        }
        /**
         *
         * @access public
         * @return
         **/
        public function valueattr()
        {
            return 'checked';
        }   // end function valueattr()
    }   // ---------- end class wbFormsElementRadio ----------

    /**
     * form builder checkbox element class
     *
     * @category   wblib2
     * @package    wbFormsElementCheckbox
     * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbFormsElementCheckbox extends wbFormsElement
    {
        protected static $cssclass = 'fbcheckbox';
        protected static $tpl = "<span %class%>%label_span%</span>%is_required%<input%type%%name%%id%%class%%style%%title%%value%%required%%checked%%tabindex%%accesskey%%disabled%%readonly%%onblur%%onchange%%onclick%%onfocus%%onselect% />";
                 // markup for <label>
               //. "%label%"

        public function init()
        {
            $this->attributes['label_span']  = NULL;
            $this->attr['is_group']          = false;
        }
        public function render()
        {
            $this->checkAttr();
            wbFormsJQuery::attach($this->attr['id'],'button');
            $this->attr['label_span'] = self::t($this->attr['label']);
            return $this->replaceAttr();
        }
        /**
         *
         * @access public
         * @return
         **/
        public function valueattr()
        {
            return 'checked';
        }   // end function valueattr()

    }   // ---------- end class wbFormsElementCheckbox ----------

    /**
     * form builder button line element
     *
     * @category   wblib2
     * @package    wbFormsElementButtonline
     * @copyright  Copyright (c) 2015 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbFormsElementButtonline extends wbFormsElement
    {
        private   static $is_open = false;
        protected static $tpl     = '<div%class%%style%>';
        public           $attr    = array(
            'class' => 'fbbuttonline ui-corner-all'
        );
        public function is_open()
        {
            return self::$is_open;
        }   // end function is_open()
        /**
         * open a buttonline (div); this also closes any buttonline that was
         * opened before
         *
         * please note that buttonlines cannot be nested!
         *
         * @access public
         * @return string
         **/
        public function open()
        {
            // close fieldset
            $fclose = wbFormsElementFieldset::get()->close();
            $close  = self::close();
            self::$is_open = true;
            return $fclose.$close.$this->replaceAttr()."\n";
        }   // end function open()

        /**
         * closes a buttonline
         *
         * @access public
         * @return string
         **/
        public function close()
        {
            if(self::$is_open)
            {
                self::$is_open = false;
                return '</div>';
            }
        }   // end function close()

    }   // ---------- end class wbFormsElementButtonline ----------

    /**
     * form builder button element; used for submit, reset
     *
     * @category   wblib2
     * @package    wbFormsElementButton
     * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbFormsElementButton extends wbFormsElement
    {
        public $attr = array(
            'value' => 1,
        );
        // valid types: button|submit|reset
        public static $tpl = '<button%type%%name%%id%%value%%tabindex%%accesskey%%class%%style%%disabled%%readonly%%onblur%%onchange%%onclick%%onfocus%%onselect%%title%>%label%</button>';
        public static $cssclass = 'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary';
        public function render() {
            $open = NULL;
            $bl   = wbFormsElementButtonline::get();
            if(!$bl->is_open()) $open = $bl->open();
            return $open.parent::render();
        }
    }   // ---------- end class wbFormsElementButton ----------

    /**
     * form builder button element; used for submit, reset
     *
     * @category   wblib2
     * @package    wbFormsElementButton
     * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbFormsElementReset extends wbFormsElementButton
    {
        public function init() {
            $this->attr['onclick'] = "$('#".wbForms::current()."').trigger('reset')";
            $this->attr['id']      = 'reset_'.wbForms::current();
            $this->attr['btntype'] = 'reset';
            wbFormsJQuery::attach($this->attr['id'], 'button', 'icons: { primary: "ui-icon-closethick" }');
        }
    }   // ---------- end class wbFormsElementReset ----------

    /**
     * form builder button element; used for submit, reset
     *
     * @category   wblib2
     * @package    wbFormsElementButton
     * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbFormsElementSubmit extends wbFormsElementButton
    {
        public function init() {
            $this->attr['onclick'] = "$('#".wbForms::current()."').submit()";
            $this->attr['id']      = 'submit_'.wbForms::current().'_btn';
            $this->attr['type']    = 'submit';
            wbFormsJQuery::addComponent('button');
            wbFormsJQuery::attach($this->attr['id'], 'button', 'icons: { primary: "ui-icon-circle-check" }');
        }
    }   // ---------- end class wbFormsElementSubmit ----------

    /**
     * form builder radio group class
     *
     * groups a list of radio elements
     *
     * @category   wblib2
     * @package    wbFormsElementRadioGroup
     * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbFormsElementRadiogroup extends wbFormsElement
    {
        protected static $number = 0;
        protected static $tpl    = '<div class="radiogroup" %title%%id%><span %class%>%label_span%</span>%is_required%%options%</div>';
        public function init()
        {
            $this->attributes['type']        = 'radio';
            $this->attributes['checked']     = 'checked';
            $this->attributes['class']       = wbFormsElementLabel::getClass();
            $this->attributes['options']     = array();
            $this->attributes['label_span']  = NULL;
            wbFormsJQuery::addComponent('buttonset');
            return $this;
        }
        /**
         * render a group of radio input fields
         **/
        public function render()
        {
            $this->checkAttr();
            self::$number++;
            $options   = array();
            // indexed means 0 => value, 1 => value, ...
            $isIndexed = array_values($this->attr['options']) === $this->attr['options'];
            foreach( $this->attr['options'] as $value => $key )
            {
                if($isIndexed) // value is the index and should be ignored, so key is the value
                {
                    $value = $key;
                }
                $checked   = NULL;
                $class     = NULL;
                $title     = NULL;

                if(isset($this->attr['checked'])    && $this->attr['checked'] == $value)
                    $checked = 'checked';
                if(is_array($this->attr['checked']) && is_scalar($value) && isset($this->attr['checked'][$value]))
                    $checked = 'checked';
                if(is_array($this->attr['checked']) && in_array($value,$this->attr['checked']))
                    $checked = 'checked';
                if(isset($this->attr['radio_class']))
                    $class   = $this->attr['radio_class'];
                //
                if(is_array($value))
                {
                    $temp    = $value;
                    $title   = isset($temp['title'])   ? $temp['title']   : NULL;
                    $value   = isset($temp['value'])   ? $temp['value']   : NULL;
                    $key     = isset($temp['label'])   ? $temp['label']   : NULL;
                    $checked = isset($temp['checked']) ? $temp['checked'] : $checked;
                    $checked = ( is_array($this->attr['checked']) && in_array($value,$this->attr['checked']))
                             ? true
                             : false;
                }
                $options[] = wbFormsElementRadio::get(
                    array(
                        'is_group' => true,
                        'type'     => str_replace('group', '', $this->attr['type'] ),
                        'name'     => $this->attr['name'],
                        'id'       => $this->attr['id'].'_'.$value,
                        'label'    => $key,
                        'value'    => $value,
                        'checked'  => $checked,
                        'class'    => $class,
                        'title'    => htmlspecialchars($title)
                    ))->render();
            }

            self::log('Radiogroup elements:',7);
            self::log(var_export($options,1),7);
            $this->attr['options']    = implode( "\n", $options );
            $this->attr['id']         = $this->attr['type'].'_'.self::$number;
            $this->attr['label_span'] = self::t($this->attr['label']);
            unset($this->attr['label']);
            wbFormsJQuery::attach($this->attr['id'],'buttonset');
            return $this->replaceAttr();
        }
        /**
         *
         * @access public
         * @return
         **/
        public function valueattr()
        {
            return 'checked';
        }   // end function valueattr()
    }   // ---------- end class wbFormsElementRadiogroup ----------


    /**
     * form builder checkbox group class
     *
     * groups a list of checkbox elements
     *
     * @category   wblib2
     * @package    wbFormsElementCheckboxgroup
     * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbFormsElementCheckboxgroup extends wbFormsElementRadiogroup
    {
        public function init()
        {
            parent::init();
            $this->attributes['type'] = 'checkbox';
            return $this;
        }
    }   // ---------- end class wbFormsElementCheckboxgroup ----------

    /**
     * form builder honeypot element class; adds a honeypot (hidden) field
     * to the form as some basic spam protection
     *
     * @category   wblib2
     * @package    wbFormsElementHoneypot
     * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbFormsElementHoneypot extends wbFormsElement
    {
        /**
         * output template
         **/
        protected static $tpl = NULL;
        public function init()
        {
            self::log('> wbFormsElementHoneypot::init()',7);
            self::$tpl =
                 "<div class=\"fbhide\" style=\"display:none;\">"
               . wbFormsElement::$tpl
               . "</div>"
               ;
            parent::init();
            return $this;
        }   // end function init()

        public function render()
        {
            $this->checkAttr();
            $this->attr = array_merge($this->attr, array(
                'type'  => 'text',
                'label' => 'Please leave this blank',
            ));
            return $this->replaceAttr();
        }   // end function render()
    }   // ---------- end class wbFormsElementHoneypot ----------


    /**
     * form builder date element class; uses jQuery DatePicker
     *
     * @category   wblib2
     * @package    wbFormsElementDate
     * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbFormsElementDate extends wbFormsElement
    {
        protected static $cssclass = 'fbdatetime';
        public function render()
        {
            $this->checkAttr();
            $this->attr['type'] = 'text';
            return $this->replaceAttr();
        }   // end function render()
    }   // ---------- end class wbFormsElementDate ----------

    /**
     * form builder WYSIWYG element class; uses Aloha Editor
     *
     * @category   wblib2
     * @package    wbFormsElementWysiwyg
     * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbFormsElementWysiwyg extends wbFormsElementTextarea
    {
        public function init()
        {
            switch(self::$globals['wysiwyg_editor'])
            {
                case 'TinyMCE':
                default:
            // ----- enable this for using TinyMCE (1 line) ----
            wbForms::addJSLink('//tinymce.cachefly.net/4.0/tinymce.min.js');
                    #wbForms::addCSS(
                    break;
            }
            $this->attr['style'] = 'width:300px;height:300px;';
        }
        public function render()
        {
            $this->checkAttr();
            switch(self::$globals['wysiwyg_editor'])
            {
                case 'TinyMCE':
                default:
            // ----- enable this for using TinyMCE (1 line) ----
            wbForms::addJS("tinymce.init({selector:'textarea#".$this->attr['id']."'});");
                    break;
            }
            return $this->replaceAttr();
        }   // end function render()
    }   // ---------- end class wbFormsElementWysiwyg ----------

    /**
     * form builder jQuery interface class
     *
     * @category   wblib2
     * @package    wbFormsJQuery
     * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbFormsJQuery extends wbFormsBase
    {
        protected static $tpl               = NULL;
        /**
         * template to load scripts into the header
         **/
        private   static $script_tpl        = '<script type="text/javascript" src="%s"></script>';
        /**
         * template to load scripts via JS
         **/
        private   static $script_js_tpl     = NULL;
        /**
         *
         **/
        private   static $inline_script_tpl = NULL;
        /**
         * template to load CSS into header
         **/
        private   static $css_tpl           = NULL;
        /**
         *
         **/
        private   static $inline_css_tpl    = NULL;
        /**
         * template to append something to head
         **/
        private   static $append_tpl        = NULL;
        /**
         * template for component loading
         **/
        private   static $comp_tpl          = NULL;
        /**
         * list of UI components to load
         **/
        private   static $ui_components     = array();
        /**
         * attach components to fields
         **/
        private   static $attach_comp       = array();
        /**
         *
         **/
        private   static $scripts           = array();
        /**
         * global code (for multiple forms)
         **/
        private   static $global_code_sent  = NULL;
        /**
         * globals for jQuery config
         **/
        protected static $globals           = array(
            // to use hosted scripts, we defined CDNs here
            'CDNs' => array(
                'jquery'         => '//ajax.googleapis.com/ajax/libs/jquery/2/jquery.min.js',
                'jqueryui'       => '//ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js',
                'jqueryuithemes' => '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/%s/jquery-ui.css',
                'select2'        => '//cdn.jsdelivr.net/select2/3.4.8/select2.min.js',
            ),
            'enabled'            => true,
            'load_ui_theme'      => true,
            'disable_tooltips'   => false,
            'prefer_locals'      => true,    // prefer scripts located in 3rdparty folder
            'ui_theme'           => 'redmond', // default UI theme
            //'sel_css'  => '//cdn.jsdelivr.net/select2/3.4.8/select2.css',
        );

        public static function init()
        {
            self::log('> jQuery::init()',7);
            wbFormsJQuery::$tpl
                = '<script type="text/javascript">%s</script>';
            self::log('< jQuery::init()',7);
        }   // end function init()

        /**
         * attach an UI component to a form field
         *
         * @access public
         * @param  string  $name    - component name
         * @return void
         **/
        public static function addComponent($name)
        {
            self::log('> jQuery::addComponent()',7);
            if ( ! isset(self::$ui_components[$name]) )
            {
                self::log(sprintf('adding UI component [%s]',$name),7);
                array_push(
                    self::$ui_components,
                    $name
                );
            }
            self::log('< jQuery::addComponent()',7);
        }   // end function addComponent()

        /**
         * attach an UI component to a field
         *
         * @access public
         * @param  string  $id   - element id
         * @param  string  $comp - component name
         * @param  string  $opt  - options to be passed to the element
         * @return
         **/
        public static function attach($id,$comp,$opt=NULL)
        {
            self::log('> jQuery::attach()',7);
            if(!isset(self::$attach_comp[$id]))
                self::$attach_comp[$id] = array();
            array_push(
                self::$attach_comp[$id],
                array( $comp => $opt )
            );
            self::log('< jQuery::attach()',7);
        }   // end function attach()

        /**
         *
         * @access public
         * @return
         **/
        public static function getHeaders()
        {
            self::log('> jQuery::getHeaders()',7);
            $seen_src = array();
            // jQuery UI
            $output
                = sprintf(
                      wbFormsJQuery::$css_tpl,
                      sprintf(self::$globals['CDNs']['jqueryuithemes'],self::$globals['ui_theme'])
                  );
            // linked CSS
            if(count(wbForms::$CSS))
                $output  .= implode("\n",array_map(
                    function($url) {
                        return sprintf(
                            wbFormsJQuery::$css_tpl,
                            $url
                        );
                    },
                    array_values(self::unique(wbForms::$CSS))
                ));
            // inline CSS
            if(count(wbForms::$INLINECSS))
                $output  .= implode("\n",array_map(
                    function($css) {
                        return sprintf(
                            wbFormsJQuery::$inline_css_tpl,
                            $css
                        );
                    },
                    array_values(self::unique(wbForms::$INLINECSS))
                ));
            // jQuery JS
            $output  .= implode("\n",array_map(
                function($script) {
                    return sprintf(
                        wbFormsJQuery::$script_tpl,
                        $script, NULL
                    );
                },
                array_values(self::$scripts)
            ));
            // other JS
            if(count(wbForms::$JS))
            {
                foreach(wbForms::$JS as $item)
                {
                    if(!isset($seen_src[$item['src']]))
                    {
                        $output.= sprintf(
                            wbFormsJQuery::$script_tpl,
                            $item['src'], $item['attr']
                        );
                        $seen_src[$item['src']] = 1;
                    }
                }
            }
            // inline JS
            $output  .= implode("\n",array_map(
                function($script) {
                    return sprintf(
                        wbFormsJQuery::$script_tpl,
                        $script
                    );
                },
                array_values(wbForms::$INLINEJS)
            ));

            self::log('headers returned',7);
            self::log($output,7);
            define('WBLIB2_HEADERS_SENT',1);
            self::log('< jQuery::getHeaders()',7);
            return $output;
        }   // end function getHeaders()

        /**
         *
         * @access public
         * @return
         **/
        public static function render()
        {
            self::log('> jQuery::render()',7);
            if(!self::$globals['enabled']) return;

            // set base URL
            if(!defined('WBLIB_URL'))
            {
                if(wbFormsBase::$globals['wblib_url'])
                    define('WBLIB_URL',wbFormsBase::$globals['wblib_url']);
                else
                    define('WBLIB_URL',wbFormsBase::getURL());
            }

            $r_code = '';

            // avoid to add global code more than once
            if(!self::$global_code_sent)
            {
                $code1 = 'var wbforms_ui_css = "'
                       . self::$globals['CDNs']['jqueryuithemes']
                       . '";'."\n    "
                       . 'var wbforms_ui_theme = "'
                       . self::$globals['ui_theme']
                       . '";'."\n"
                       ;
                if(self::$globals['load_ui_theme'] === false)
                {
                    $code1 = 'var wbforms_disable_ui = true;'."\n";
                }
                if(self::$globals['disable_tooltips'] === true)
                {
                    $code1 .= '            var wbforms_disable_tooltips = true;'."\n";
                }
                #foreach(self::$globals as $key => $value)
                #    $code1 .= $space.'if(typeof wbforms_'.$key.' == "undefined" ) { var wbforms_'.$key.' = "'.$value.'"; }'."\n";
                // add any other JS
                $code1 .= implode("\n", wbForms::$INLINEJS);
                self::$global_code_sent = true;
                self::log('global coded generated',7);
                $r_code = self::getHeaders();
                // create config for require.js
                $r_code .= "
        <script type=\"text/javascript\">
            var WBLIB_URL = '".WBLIB_URL."';
            ".$code1."
        </script>
        <script src=\"".WBLIB_URL."/3rdparty/js/requirejs/require.js\" data-main=\"".WBLIB_URL."/3rdparty/js/loader\"></script>
";
            }
            self::log('< jQuery::render()',7);
            return $r_code; //sprintf(self::$tpl,$code);
        }   // end function render()

        private static function unique($array)
        {
        		$set = array();
        		$out = array();
        		foreach ( $array as $key => $val ) {
        			if ( is_array($val) ) {
        			    $out[$key] = self::unique($val);
        			}
        			else {
        			    $seen_val = strtolower($val);
        			    if( ! isset($set[$seen_val]) ) {
    						$out[$key] = $val;
    					}
    					$set[$seen_val] = 1;
                	}
        		}
        		return $out;
       		}   // end function unique()

    }   // ---------- end class wbFormsJQuery ----------

    /**
     * form builder protection class
     *
     * @category   wblib2
     * @package    wbFormsProtect
     * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbFormsProtect extends wbFormsBase
    {
        private static $token_lifetime = 86400;
        private static   $token        = NULL;
        /**
         * CSRF protection default settings; SHOULD be overwritten by caller
         **/
        public  static $config = array(
			'secret'          => '!p"/.m4fk{ay{£1R0W0O',
			'token_lifetime'  => '86400',
			'token_fieldname' => 'fbseqmagictoken',
            'dynamic'         => 'aq23S(X/<)PYSZ#;',
            'redirect_to'     => NULL,
        );

/*******************************************************************************
 * CHECK FOR ALLOWED DATA TYPES, USING PHP FILTERS
 ******************************************************************************/

        public static function check_boolean($var)
        {
            if (!is_bool($var))
                $var = filter_var($var, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            return filter_var($var,FILTER_VALIDATE_BOOLEAN,FILTER_NULL_ON_FAILURE);
        }   // end function check_boolean()

        public static function check_email($var)
        {
            return filter_var($var,FILTER_VALIDATE_EMAIL);
        }   // end function check_email()

        public static function check_url($var)
        {
            return filter_var($var,FILTER_VALIDATE_URL);
        }   // end function check_url()

        public static function check_string($var)
        {
            if( is_callable(array('\wblib\wbValidateValidate', 'as_string')) === true )
            {
                return \wblib\wbValidateValidate::as_string($var);
            }
            else
            {
                return filter_var($var,FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_LOW|FILTER_FLAG_ENCODE_AMP);
            }
        }   // end function check_string()

/*******************************************************************************
 * CHECK FOR ALLOWED DATA TYPES, USING OWN FILTERS
 ******************************************************************************/

        public static function check_array($incoming,$allowed)
        {
            $isIndexed = array_values($allowed) === $allowed;
            if(!$isIndexed)
                return in_array($incoming,$allowed);
            else
                return in_array($incoming,array_keys($allowed));
        }   // end function check_array()

        public static function check_html($var)
        {
            $dom = new \DOMDocument;
            $dom->loadHtml($var); // see docs for load, loadXml, loadHtml and loadHtmlFile
            if (!count(libxml_get_errors())) {
                return $var;
            }
            return false;
        }   // end function check_html()

        public static function check_int($var,$opt='')
        {
            if(substr_count($var,':'))
            {
                list($min,$max) = explode(':',$opt);
                if($min||$max) $opt = array();
                if($min&&is_numeric((int)$min)) $opt['options']['min_range'] = $min;
                if($max&&is_numeric((int)$max)) $opt['options']['max_range'] = $max;
            }
            return filter_var($var,FILTER_VALIDATE_INT,$opt);
        }   // end function check_int()

        public static function check_number($var)
        {
            return is_numeric($var) ? $var : false;
        }

        public static function check_plain($var,$opt='')
        {
            // in fact, we do not check anything here!
            return $var;
        }

        public static function checkToken($dynamic=NULL)
        {
            self::log('> checkToken()',7);
            if(!$dynamic)
            {
                self::log('No dynamic part given, less security!',4);
                $dynamic = self::$config['dynamic'];
            }
            if(!isset($_POST[self::$config['token_fieldname']]))
            {
                self::log('Missing token in form data!',1);
                self::log('< checkToken(false) - missing',7);
                return false;
            }
            // get token from hidden field
            $token = $_POST[self::$config['token_fieldname']];
            $parts = explode( '-', $token );
            if(count($parts)!==3)
            {
                self::log(sprintf('Invalid token - parts [%s], should be [3]',count($parts)),1);
                self::log('< checkToken(false) - parts count',7);
                return false;
            }
            $tokentime
                = ( self::$config['token_lifetime'] )
                ? self::$config['token_lifetime']
                : self::$token_lifetime
                ;
			// secret time should not extend one day and not drop below 1 hour
			if ( ! is_numeric($tokentime) || $tokentime > 86400 || $tokentime < 36000 ) {
			    self::log(sprintf('Invalid token lifetime [%s] given; using the default (86400 = 1 day)',$tokentime),5);
                self::log('< checkToken(false) - lifetime',7);
				$tokentime = 86400;
			}
            list( $token, $hash, $time ) = $parts;
            // check if token is expired
            if ($time < ( time() - $tokentime ) )
            {
                self::log(sprintf('Invalid token - is expired! (%s < %s)',$time,(time()-$tokentime)),4);
                self::log('< checkToken(false) - expired',7);
                return false;
            }
            // check the secret
            $secret = self::createSecret($dynamic);
            if ( $hash != sha1( $secret.'-'.$dynamic.'-'.$token ) )
            {
                self::log('Invalid token - secret not matched!',1);
                self::log('< checkToken(false) - secret',7);
                return false;
            }
            else {
                self::log('< checkToken(true)',7);
				return true;
			}
        }   // end function checkToken()


        /**
         * generates a new token; use $force if you wish to overwrite an already
         * generated token
         *
         * @access public
         * @param  string  $dynamic
         * @param  boolean $force
         * @return
         **/
        public static function createToken($dynamic=NULL,$force=false)
        {
            self::log(sprintf('> createToken("%s")',$dynamic),7);
            if(self::$token && self::$token!='' && !$force)
        {
                self::log('return already generated token',7);
                self::log(sprintf('< createToken("%s")',self::$token),7);
                return self::$token;
            }
            if(!$dynamic)
            {
                self::log('No dynamic part given, less security!',4);
                $dynamic = self::$config['dynamic'];
            }
            // create a random token
            $token    = dechex(mt_rand());
            // create a hash using the secret, the dynamic part, and the random token
            $hash     = sha1( self::createSecret($dynamic).'-'.$dynamic.'-'.$token );
            // now, at least, create the token
            self::$token = $token.'-'.$hash.'-'.time();
            self::log(sprintf('< createToken("%s")',self::$token),7);
            return self::$token;
        }   // end function createToken()

        /**
         *
         * @access public
         * @return
         **/
        public static function getToken($dynamic=NULL)
        {
            self::log('> getToken()',7);
            self::log('< getToken()',7);
            return array(
                'type'  => 'hidden',
                'name'  => self::$config['token_fieldname'],
                'value' => self::createToken($dynamic),
            );
        }   // end function getToken()

		/**
		 * creates a secret that contains server and user agent specific data
		 * as part of the challenge
		 *
		 * -> add some extra randomness to the configured secret
		 *
		 * @access private
		 * @param  string   $dynamic
		 * @return string
		 **/
		private static function createSecret($dynamic)
        {
            self::log(sprintf('> createSecret(%s)',$dynamic),7);
		    $secret     = self::$config['secret'];
			$tokentime
                = ( self::$config['token_lifetime'] )
                ? self::$config['token_lifetime']
                : self::$token_lifetime
                ;
			// secret time should not extend one day and not drop below 1 hour
			if ( ! is_numeric($tokentime) || $tokentime > 86400 || $tokentime < 36000 ) {
			    self::log('createSecret() - Invalid token lifetime given; using the default (86400 = 1 day)',4);
				$tokentime = 86400;
			}
			$TimeSeed    = floor( time() / $tokentime ) * $tokentime;
			$DomainSeed  = $_SERVER['SERVER_NAME'];
			$Seed        = $TimeSeed + $DomainSeed;

			// use some server specific data
			$serverdata  = ( isset( $_SERVER['SERVER_SIGNATURE'] ) )   ? $_SERVER['SERVER_SIGNATURE']     : 'qN';
			$serverdata .= ( isset( $_SERVER['SERVER_SOFTWARE'] ) )    ? $_SERVER['SERVER_SOFTWARE']      : 'qp';
			$serverdata .= ( isset( $_SERVER['SERVER_NAME'] ) ) 	   ? $_SERVER['SERVER_NAME'] 		  : 'q8';
			$serverdata .= ( isset( $_SERVER['SERVER_ADDR'] ) ) 	   ? $_SERVER['SERVER_ADDR'] 		  : 'q&';
			$serverdata .= ( isset( $_SERVER['SERVER_PORT'] ) ) 	   ? $_SERVER['SERVER_PORT'] 		  : 'q1';
			$serverdata .= ( isset( $_SERVER['SERVER_ADMIN'] ) )	   ? $_SERVER['SERVER_ADMIN'] 		  : 'q!';
			$serverdata .= PHP_VERSION;

			// add some browser data
			$browser     = ( isset($_SERVER['HTTP_USER_AGENT']) )      ? $_SERVER['HTTP_USER_AGENT']      : 'xc';
			$browser    .= ( isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : 'x9';
			$browser    .= ( isset($_SERVER['HTTP_ACCEPT_ENCODING']) ) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : 'x?';
			$browser	.= ( isset($_SERVER['HTTP_ACCEPT_CHARSET']) )  ? $_SERVER['HTTP_ACCEPT_CHARSET']  : 'xB';

			// add seed to current secret
			$secret     .= md5($Seed).md5($serverdata).md5($browser);
            self::log(sprintf('< createSecret(%s)',$secret),7);
			return $secret;
		}   // end function createSecret()

        /**
         *
         *
         *
         *
         **/
		private static function terminateSession($strict=true,$reason=NULL)
        {
		    // unset session variables
		    if (isset($_SESSION)) {
		        $_SESSION = array();
		    }
		    if (isset($HTTP_SESSION_VARS)) {
		        $HTTP_SESSION_VARS = array();
		    }
		    // unset globals
		    unset( $_REQUEST );
            unset( $_POST    );
            unset( $_GET     );
            unset( $_SERVER  );

		    session_unset();

			if (self::$config['redirect_to'] && self::$config['redirect_to'] !== '')
            {
			    if ( ! headers_sent() )
                {
		            header("Location: " . self::$config['redirect_to'] );
		        }
				else
                {
		            self::log('Unable to redirect. Headers already sent.',4);
		        }
		        if ( $strict ) { die; }
            }
            else {
	            self::log('Unable to redirect. No redirect location.',4);
	        }
	        if ( $strict ) { die; }
		}   // end function terminateSession()

    }   // ---------- end class wbFormsProtect() ----------

    /**
     * form builder exception class; prints exceptions to log file
     *      (level 'error' = 3)
     *
     * @category   wblib2
     * @package    wbFormsException
     * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbFormsException extends \Exception {
        public function __construct($message, $code = 0)
        {
            wbFormsBase::log($message,3);
            parent::__construct($message, $code);
        }
    }

}       // ---------- end class_exists('wbForms') ----------