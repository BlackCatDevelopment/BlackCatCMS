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
 *   Please note: This class is based on class.upload.php by verot.net and uses
 *   some of the great work of Colin Verot. We removed all the image
 *   manipulation stuff as we wanted to have an "upload only" class, and added
 *   some more security features using the (external) getID3 library.
 *
 */

if ( ! class_exists( 'CAT_Helper_Upload' ) )
{
    if ( ! class_exists( 'CAT_Object', false ) )
    {
        @include dirname(__FILE__).'/../Object.php';
    }
    class CAT_Helper_Upload extends CAT_Object
    {

        protected $debugLevel      = 8; // 8 = OFF; 7 = DEBUG

        /**
        * Uploaded file name
        *
        * @access public
        * @var string
        */
        var $file_src_name;
    
        /**
        * Uploaded file name body (i.e. without extension)
        *
        * @access public
        * @var string
        */
        var $file_src_name_body;
    
        /**
        * Uploaded file name extension
        *
        * @access public
        * @var string
        */
        var $file_src_name_ext;
    
        /**
        * Uploaded file MIME type
        *
        * @access public
        * @var string
        */
        var $file_src_mime;
    
        /**
        * Uploaded file size, in bytes
        *
        * @access public
        * @var double
        */
        var $file_src_size;
    
        /**
        * Holds eventual PHP error code from $_FILES
        *
        * @access public
        * @var string
        */
        var $file_src_error;
    
        /**
        * Uloaded file name, including server path
        *
        * @access public
        * @var string
        */
        var $file_src_pathname;
    
        /**
        * Uloaded file name temporary copy
        *
        * @access private
        * @var string
        */
        var $file_src_temp;
    
        /**
        * Destination file name
        *
        * @access public
        * @var string
        */
        var $file_dst_path;
    
        /**
        * Destination file name
        *
        * @access public
        * @var string
        */
        var $file_dst_name;
    
        /**
        * Destination file name body (i.e. without extension)
        *
        * @access public
        * @var string
        */
        var $file_dst_name_body;
    
        /**
        * Destination file extension
        *
        * @access public
        * @var string
        */
        var $file_dst_name_ext;
    
        /**
        * Destination file name, including path
        *
        * @access public
        * @var string
        */
        var $file_dst_pathname;
    
        /**
        * Flag set after instanciating the class
        *
        * Indicates if the file has been uploaded properly
        *
        * @access public
        * @var bool
        */
        var $uploaded;
    
        /**
        * Flag stopping PHP upload checks
        *
        * Indicates whether we instanciated the class with a filename, in which case
        * we will not check on the validity of the PHP *upload*
        *
        * This flag is automatically set to true when working on a local file
        *
        * Warning: for uploads, this flag MUST be set to false for security reason
        *
        * @access public
        * @var bool
        */
        var $no_upload_check;
    
        /**
        * Flag set after calling a process
        *
        * Indicates if the processing, and copy of the resulting file went OK
        *
        * @access public
        * @var bool
        */
        var $processed;
    
        /**
        * Holds eventual error message in plain english
        *
        * @access public
        * @var string
        */
        var $error;
    
        /**
        * Holds an HTML formatted log
        *
        * @access public
        * @var string
        */
        var $log;
    
    // overiddable processing variables
    
        /**
        * Set this variable to replace the name body (i.e. without extension)
        *
        * @access public
        * @var string
        */
        var $file_new_name_body;
    
        /**
        * Set this variable to append a string to the file name body
        *
        * @access public
        * @var string
        */
        var $file_name_body_add;
    
        /**
        * Set this variable to prepend a string to the file name body
        *
        * @access public
        * @var string
        */
        var $file_name_body_pre;
    
        /**
        * Set this variable to change the file extension
        *
        * @access public
        * @var string
        */
        var $file_new_name_ext;
    
        /**
        * Set this variable to format the filename (spaces changed to _)
        *
        * @access public
        * @var boolean
        */
        var $file_safe_name;
    
        /**
        * Forces an extension if the source file doesn't have one
        *
        * If the file is an image, then the correct extension will be added
        * Otherwise, a .txt extension will be chosen
        *
        * @access public
        * @var boolean
        */
        var $file_force_extension;
    
        /**
        * Set this variable to false if you don't want to check the MIME against the allowed list
        *
        * This variable is set to true by default for security reason
        *
        * @access public
        * @var boolean
        */
        var $mime_check;
    
        /**
        * Set this variable to false in the init() function if you don't want to check the MIME 
        * with Fileinfo PECL extension. On some systems, Fileinfo is known to be buggy, and you
        * may want to deactivate it in the class code directly.
        *
        * You can also set it with the path of the magic database file.
        * If set to true, the class will try to read the MAGIC environment variable
        *   and if it is empty, will default to '/usr/share/file/magic'
        * If set to an empty string, it will call finfo_open without the path argument
        *
        * This variable is set to true by default for security reason
        *
        * @access public
        * @var boolean
        */
        var $mime_fileinfo;
    
        /**
        * Set this variable to false in the init() function if you don't want to check the MIME 
        * with UNIX file() command
        *
        * This variable is set to true by default for security reason
        *
        * @access public
        * @var boolean
        */
        var $mime_file;
    
        /**
        * Set this variable to false in the init() function if you don't want to check the MIME 
        * with the magic.mime file
        *
        * The function mime_content_type() is deprecated and not secure, so
        * this variable is set to false by default for security reasons
        *
        * @access public
        * @var boolean
        */
        var $mime_magic;
    
        /**
         * This variable is used as default if no mime type can be detected.
         *
         * Defaults to 'application/octet-stream' as this is deactivated in
         * most cases
         *
         * @access public
         * @var string
         **/
        var $mime_default_type;
    
        /**
        * Set this variable to false if you don't want to turn dangerous scripts into simple text files
        *
        * @access public
        * @var boolean
        */
        var $no_script;
    
        /**
        * Set this variable to true to allow automatic renaming of the file
        * if the file already exists
        *
        * Default value is true
        *
        * For instance, on uploading foo.ext,<br>
        * if foo.ext already exists, upload will be renamed foo_1.ext<br>
        * and if foo_1.ext already exists, upload will be renamed foo_2.ext<br>
        *
        * Note that this option doesn't have any effect if {@link file_overwrite} is true
        *
        * @access public
        * @var bool
        */
        var $file_auto_rename;
    
        /**
        * Set this variable to true to allow automatic creation of the destination
        * directory if it is missing (works recursively)
        *
        * Default value is true
        *
        * @access public
        * @var bool
        */
        var $dir_auto_create;
    
        /**
        * Set this variable to true to allow automatic chmod of the destination
        * directory if it is not writeable
        *
        * Default value is true
        *
        * @access public
        * @var bool
        */
        var $dir_auto_chmod;
    
        /**
        * Set this variable to the default chmod you want the class to use
        * when creating directories, or attempting to write in a directory
        *
        * Default value is 0777 (without quotes)
        *
        * @access public
        * @var bool
        */
        var $dir_chmod;
    
        /**
        * Set this variable tu true to allow overwriting of an existing file
        *
        * Default value is false, so no files will be overwritten
        *
        * @access public
        * @var bool
        */
        var $file_overwrite;
    
        /**
        * List of MIME types per extension
        *
        * @access private
        * @var array
        */
        var $mime_types;
    
        /**
        * Allowed MIME types
        *
        * Default is a selection of safe mime-types, but you might want to change it
        *
        * Simple wildcards are allowed, such as image/* or application/*
        * If there is only one MIME type allowed, then it can be a string instead of an array
        *
        * @access public
        * @var array OR string
        */
        var $allowed;
    
        /**
        * Forbidden MIME types
        *
        * Default is a selection of safe mime-types, but you might want to change it
        * To only check for forbidden MIME types, and allow everything else, set {@link allowed} to array('* / *') without the spaces
        *
        * Simple wildcards are allowed, such as image/* or application/*
        * If there is only one MIME type forbidden, then it can be a string instead of an array
        *
        * @access public
        * @var array OR string
        */
        var $forbidden;

        // array to store config options
        protected $_config         = array( 'loglevel' => 8 );

        /**
         * instance array; one instance per file
         **/
        private static $instances = array();

        public static function getInstance( $file )
        {
            $instance_name = $file;
            if ( is_array($file) && isset($file['name']) )
            {
                $instance_name = $file['name'];
            }
            if (!self::$instances||!isset(self::$instances[$instance_name]))
            {
                self::$instances[$instance_name] = new self($file);
            }
            return self::$instances[$instance_name];
        }   // end function getInstance()
    
        /**
        * Init or re-init all the processing variables to their default values
        *
        * This function is called in the constructor, and after each call of {@link process}
        *
        * @access private
        */
        function init()
        {
            // overiddable variables
            $this->file_new_name_body        = null;            // replace the name body
            $this->file_name_body_add        = null;            // append to the name body
            $this->file_name_body_pre        = null;            // prepend to the name body
            $this->file_new_name_ext        = null;            // replace the file extension
            $this->file_safe_name            = true;            // format safely the filename
            $this->file_force_extension        = true;            // forces extension if there isn't one
            $this->file_overwrite            = false;        // allows overwritting if the file already exists
            $this->file_auto_rename            = true;            // auto-rename if the file already exists
            $this->dir_auto_create            = true;            // auto-creates directory if missing
            $this->dir_auto_chmod            = true;            // auto-chmod directory if not writeable
            $this->dir_chmod                = 0777;            // default chmod to use

            $this->no_script                = true;            // turns scripts into test files
            $this->mime_check                = true;            // checks the mime type against the allowed list

            // these are the different MIME detection methods. if one of these method doesn't work on your
            // system, you can deactivate it here; just set it to false
            $this->mime_fileinfo            = true;            // MIME detection with Fileinfo PECL extension
            $this->mime_file                 = true;            // MIME detection with UNIX file() command
            $this->mime_magic                = false;           // MIME detection with mime_magic (mime_content_type())

            // get the default max size from php.ini
            $this->file_max_size_raw        = trim(ini_get('upload_max_filesize'));
            $this->file_max_size            = $this->getsize($this->file_max_size_raw);
    
            $this->forbidden                = array();
            $this->allowed                   = array();
            $this->mime_types                = array();
            $this->mime_default_type         = 'application/octet-stream';

            $this->mime_types = CAT_Helper_Mime::getMimeTypes();
            $this->log()->LogDebug('registered mime types',$this->mime_types);
    
            // allow to override default settings
            if(CAT_Registry::get('UPLOAD_ENABLE_MIMECHECK')=='false')
                $this->mime_check = false;
            if(CAT_Registry::get('UPLOAD_MIME_DEFAULT_TYPE')=='false')
                $this->mime_default_type = false;

            $this->allowed = CAT_Helper_Mime::getAllowedMimeTypes();

        }   // end function init()
    
        /**
        * Constructor. Checks if the file has been uploaded
        *
        * The constructor takes $_FILES['form_field'] array as argument
        * where form_field is the form field name
        *
        * The constructor will check if the file has been uploaded in its temporary location, and
        * accordingly will set {@link uploaded} (and {@link error} is an error occurred)
        *
        * If the file has been uploaded, the constructor will populate all the variables holding the upload
        * information (none of the processing class variables are used here).
        * You can have access to information about the file (name, size, MIME type...).
        *
        *
        * Alternatively, you can set the first argument to be a local filename (string)
        * This allows processing of a local file, as if the file was uploaded
        *
        * @access private
        * @param  array  $file $_FILES['form_field']
        *    or   string $file Local filename
        */
        function __construct( $file )
        {
            $this->file_src_name        = '';
            $this->file_src_name_body    = '';
            $this->file_src_name_ext    = '';
            $this->file_src_mime        = '';
            $this->file_src_size        = '';
            $this->file_src_error        = '';
            $this->file_src_pathname    = '';
            $this->file_src_temp        = '';
            $this->file_dst_path        = '';
            $this->file_dst_name        = '';
            $this->file_dst_name_body    = '';
            $this->file_dst_name_ext    = '';
            $this->file_dst_pathname    = '';
            $this->uploaded                = true;
            $this->no_upload_check        = false;
            $this->processed            = true;
            $this->error                = '';
            $this->allowed                = array();
            $this->forbidden            = array();
            $this->init();
            $info                        = null;
            $mime_from_browser            = null;

            // display some system information
            if ( $this->debugLevel == 7 )
            {
                $this->log()->logDebug('system information');
                if (function_exists('ini_get_all'))
                {
                    $inis            = ini_get_all();
                    $open_basedir    = (    array_key_exists('open_basedir', $inis) 
                                        && array_key_exists('local_value', $inis['open_basedir'])
                                      && !empty($inis['open_basedir']['local_value'])
                                    )
                                    ? $inis['open_basedir']['local_value']
                                    : false;
                }
                else
                {
                    $open_basedir = false;
                }
                $this->log()->logDebug( 'operating system       : ' . PHP_OS );
                $this->log()->logDebug( 'PHP version               : ' . PHP_VERSION );
                $this->log()->logDebug( 'open_basedir              : ' . (!empty($open_basedir) ? $open_basedir : 'no restriction') );
                $this->log()->logDebug( 'upload_max_filesize: ' . $this->file_max_size_raw . ' (' . $this->file_max_size . ' bytes)' );
            }

            // check if we sent a local filename rather than a $_FILE element
            if (!is_array($file))
            {
                if (empty($file))
                {
                    $this->uploaded = false;
                    $this->error = 'File error. Please try again.';
                }
                else
                {
                    $this->no_upload_check = TRUE;
                    // this is a local filename, i.e.not uploaded
                    $this->log()->logDebug( 'Source is a local file ' . $file );

                    if ($this->uploaded && !file_exists($file))
                    {
                        $this->uploaded = false;
                        $this->error = 'Local file doesn\'t exist.';
                    }

                    if ($this->uploaded && !is_readable($file))
                    {
                        $this->uploaded = false;
                        $this->error = 'Local file is not readable.';
                    }

                    if ($this->uploaded)
                    {
                        $this->file_src_pathname = $file;
                        $this->file_src_name       = basename($file);
                        $this->log()->logDebug( 'local file name OK' );
                        preg_match('/\.([^\.]*$)/', $this->file_src_name, $extension);
                        if (is_array($extension) && sizeof($extension) > 0)
                        {
                            $this->file_src_name_ext     = strtolower($extension[1]);
                            $this->file_src_name_body    = substr($this->file_src_name, 0, ((strlen($this->file_src_name) - strlen($this->file_src_name_ext)))-1);
                        }
                        else
                        {
                            $this->file_src_name_ext     = '';
                            $this->file_src_name_body    = $this->file_src_name;
                        }
                        $this->file_src_size = (file_exists($file) ? filesize($file) : 0);
                    }
                    $this->file_src_error = 0;
                }
            }
            else
            {
                // this is an element from $_FILE, i.e. an uploaded file
                $this->log()->logDebug( 'source is an uploaded file' );
                if ($this->uploaded)
                {
                    $this->file_src_error = (
                        isset($file['error'][0])
                        ? trim($file['error'][0])
                        : ( isset($file['error']) ? trim($file['error']) : NULL )
                    );
                    switch($this->file_src_error)
                    {
                        case UPLOAD_ERR_OK:
                            // all is OK
                            $this->log()->logDebug( 'upload OK' );
                            break;
                        case UPLOAD_ERR_INI_SIZE:
                            $this->uploaded = false;
                            $this->error = 'File upload error (the uploaded file exceeds the upload_max_filesize directive in php.ini).';
                            break;
                        case UPLOAD_ERR_FORM_SIZE:
                            $this->uploaded = false;
                            $this->error = 'File upload error (the uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form).';
                            break;
                        case UPLOAD_ERR_PARTIAL:
                            $this->uploaded = false;
                            $this->error = 'File upload error (the uploaded file was only partially uploaded).';
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            $this->uploaded = false;
                            $this->error = 'File upload error (no file was uploaded).';
                            break;
                        case @UPLOAD_ERR_NO_TMP_DIR:
                            $this->uploaded = false;
                            $this->error = 'File upload error (missing a temporary folder).';
                            break;
                        case @UPLOAD_ERR_CANT_WRITE:
                            $this->uploaded = false;
                            $this->error = 'File upload error (failed to write file to disk).';
                            break;
                        case @UPLOAD_ERR_EXTENSION:
                            $this->uploaded = false;
                            $this->error = 'File upload error (file upload stopped by extension).';
                            break;
                        default:
                            $this->uploaded = false;
                            $this->error = 'File upload error (unknown error code) ('.$this->file_src_error.')';
                    }
                }

                if ($this->uploaded)
                {
                    $this->file_src_pathname = $file['tmp_name'];
                    $this->file_src_name       = $file['name'];
                    if ($this->file_src_name == '')
                    {
                        $this->uploaded = false;
                        $this->error = 'File upload error. Unable to determine source file name.';
                        $this->log()->logError( 'Unable to determine source file name' );
                    }
                }
    
                if ($this->uploaded)
                {
                    $this->log()->logDebug( '- file name OK' );
                    preg_match('/\.([^\.]*$)/', $this->file_src_name, $extension);
                    if (is_array($extension) && sizeof($extension) > 0)
                    {
                        $this->file_src_name_ext     = strtolower($extension[1]);
                        $this->file_src_name_body    = substr($this->file_src_name, 0, ((strlen($this->file_src_name) - strlen($this->file_src_name_ext)))-1);
                    }
                    else
                    {
                        $this->file_src_name_ext     = '';
                        $this->file_src_name_body    = $this->file_src_name;
                    }
                    $this->file_src_size = $file['size'];
                    $mime_from_browser   = $file['type'];
                }
            }
    
            if ($this->uploaded)
            {
                $this->log()->logDebug( 'determining MIME type' );
                $this->file_src_mime = null;
    
                // we try to determine the mime type using different methods, from most secure to very unsecure
                // we NEVER use the mime type sent by the browser as this only uses the suffix which can be spoofed
                $this->getMimeType();

                $this->log()->logDebug( 'file_src_name      : ' . $this->file_src_name  );
                $this->log()->logDebug( 'file_src_name_body : ' . $this->file_src_name_body  );
                $this->log()->logDebug( 'file_src_name_ext  : ' . $this->file_src_name_ext  );
                $this->log()->logDebug( 'file_src_pathname  : ' . $this->file_src_pathname  );
                $this->log()->logDebug( 'file_src_mime      : ' . $this->file_src_mime  );
                $this->log()->logDebug( 'file_src_size      : ' . $this->file_src_size . ' (max= ' . $this->file_max_size . ')' );
                $this->log()->logDebug( 'file_src_error     : ' . $this->file_src_error  );
            }
        }   // end function __construct()

        /**
        * Decodes sizes
        *
        * @access private
        * @param  string  $size  Size in bytes, or shorthand byte options
        * @return integer Size in bytes
        */
        function getsize($size)
        {
            $last = strtolower($size{strlen($size)-1});
            switch($last)
            {
                case 'g':
                    $size *= 1024;
                case 'm':
                    $size *= 1024;
                case 'k':
                    $size *= 1024;
            }
            return $size;
        }

        /**
        * Actually uploads the file, and act on it according to the set processing class variables
        *
        * This function copies the uploaded file to the given location, eventually performing actions on it.
        * Typically, you can call {@link process} several times for the same file,
        * for instance to create a resized image and a thumbnail of the same file.
        * The original uploaded file remains intact in its temporary location, so you can use {@link process} several times.
        * You will be able to delete the uploaded file with {@link clean} when you have finished all your {@link process} calls.
        *
        * According to the processing class variables set in the calling file, the file can be renamed,
        * and if it is an image, can be resized or converted.
        *
        * When the processing is completed, and the file copied to its new location, the
        * processing class variables will be reset to their default value.
        * This allows you to set new properties, and perform another {@link process} on the same uploaded file
        *
        * If the function is called with a null or empty argument, then it will return the content of the picture
        *
        * It will set {@link processed} (and {@link error} is an error occurred)
        *
        * @access public
        * @param  string $server_path Optional path location of the uploaded file, with an ending slash
        * @return string Optional content of the image
        */
        function process($server_path = null)
        {
            $this->error                = '';
            $this->processed            = true;
            $return_mode                = false;
            $return_content                = null;
            // clean up dst variables
            $this->file_dst_path        = '';
            $this->file_dst_pathname    = '';
            $this->file_dst_name        = '';
            $this->file_dst_name_body    = '';
            $this->file_dst_name_ext    = '';
            // clean up some parameters
            $this->file_max_size        = $this->getsize($this->file_max_size);
            // copy some variables as we need to keep them clean
            $file_src_name                = $this->file_src_name;
            $file_src_name_body            = $this->file_src_name_body;
            $file_src_name_ext            = $this->file_src_name_ext;
            if (!$this->uploaded)
            {
                $this->error            = 'File not uploaded. Can\'t carry on a process.';
                $this->processed        = false;
            }
            if ($this->processed)
            {
                if (empty($server_path) || is_null($server_path))
                {
                    $this->log()->logDebug( 'process file and return the content' );
                    $return_mode = true;
                }
                else
                {
                    if(strtolower(substr(PHP_OS, 0, 3)) === 'win')
                    {
                        if (substr($server_path, -1, 1) != '\\') $server_path = $server_path . '\\';
                    }
                    else
                    {
                        if (substr($server_path, -1, 1) != '/') $server_path = $server_path . '/';
                    }
                    $this->log()->logDebug(sprintf('process file to server path [%s]', $server_path));
                }
            }
            if ($this->processed)
            {
                // checks file max size
                if ($this->file_src_size > $this->file_max_size)
                {
                    $this->processed = false;
                    $this->error = 'File too big.';
                }
                else
                {
                    $this->log()->logDebug( 'file size OK' );
                }
            }
            if ($this->processed)
            {
                // turn dangerous scripts into text files
                if ($this->no_script)
                {
                    // if the file has no extension, we try to guess it from the MIME type
                    if ($this->file_force_extension && empty($file_src_name_ext))
                    {
                        if ($key = array_search($this->file_src_mime, $this->mime_types))
                        {
                            $file_src_name_ext = $key;
                            $file_src_name = $file_src_name_body . '.' . $file_src_name_ext;
                            $this->log()->logDebug( 'file renamed as ' . $file_src_name_body . '.' . $file_src_name_ext . '!' );
                        }
                    }
                    // if the file is text based, or has a dangerous extension, we rename it as .txt
                    if ((((substr($this->file_src_mime, 0, 5) == 'text/' && $this->file_src_mime != 'text/rtf') 
                    || strpos($this->file_src_mime, 'javascript') !== false)  && (substr($file_src_name, -4) != '.txt'))
                    || preg_match('/\.(php|pl|py|cgi|asp|js)$/i', $this->file_src_name)
                    || $this->file_force_extension && empty($file_src_name_ext))
                    {
                        $this->file_src_mime = 'text/plain';
                        if ($this->file_src_name_ext) $file_src_name_body = $file_src_name_body . '.' . $this->file_src_name_ext;
                        $file_src_name_ext = 'txt';
                        $file_src_name = $file_src_name_body . '.' . $file_src_name_ext;
                        $this->log()->logDebug( 'script renamed as ' . $file_src_name_body . '.' . $file_src_name_ext . '!' );
                    }
                }
                if ($this->mime_check && empty($this->file_src_mime))
                {
                    $this->processed = false;
                    $this->error = 'MIME type can\'t be detected.';
                }
                else if ($this->mime_check && !empty($this->file_src_mime) && strpos($this->file_src_mime, '/') !== false)
                {
                    list($m1, $m2) = explode('/', $this->file_src_mime);
                    $this->log()->logDebug(sprintf('checking mime type [%s]',$this->file_src_mime));
                    $allowed = false;
                    // check wether the mime type is allowed
                    if (!is_array($this->allowed)) $this->allowed = array($this->allowed);
                    foreach($this->allowed as $k => $v)
                    {
                        list($v1, $v2) = explode('/', $v);
                        $this->log()->logDebug(sprintf('checking allowed %s/%s against %s/%s',$v1,$v2,$m1,$m2));
                        if (($v1 == '*' && $v2 == '*') || ($v1 == $m1 && ($v2 == $m2 || $v2 == '*')))
                        {
                            $allowed = true;
                            break;
                        }
                    }
                    // check wether the mime type is forbidden
                    if (!is_array($this->forbidden)) $this->forbidden = array($this->forbidden);
                    foreach($this->forbidden as $k => $v)
                    {
                        list($v1, $v2) = explode('/', $v);
                        $this->log()->logDebug(sprintf('checking forbidden %s/%s against %s/%s',$v1,$v2,$m1,$m2));
                        if (($v1 == '*' && $v2 == '*') || ($v1 == $m1 && ($v2 == $m2 || $v2 == '*')))
                        {
                            $allowed = false;
                            break;
                        }
                    }
                    if (!$allowed)
                    {
                        $this->processed = false;
                        $this->error = 'Incorrect type of file. Mime type ['.$this->file_src_mime.'] is forbidden';
                    }
                    else
                    {
                        $this->log()->logDebug( 'file mime OK : ' . $this->file_src_mime );
                    }
                }
                else
                {
                    $this->log()->logDebug( 'file mime (not checked) : ' . $this->file_src_mime );
                }
            }
            if ($this->processed)
            {
                $this->file_dst_path            = $server_path;
                // repopulate dst variables from src
                $this->file_dst_name            = $file_src_name;
                $this->file_dst_name_body        = $file_src_name_body;
                $this->file_dst_name_ext        = $file_src_name_ext;
                if ($this->file_overwrite) $this->file_auto_rename = false;
                if (!is_null($this->file_new_name_body))
                { // rename file body
                    $this->file_dst_name_body = $this->file_new_name_body;
                    $this->log()->logDebug( 'new file name body : ' . $this->file_new_name_body );
                }
                if (!is_null($this->file_new_name_ext))
                { // rename file ext
                    $this->file_dst_name_ext  = $this->file_new_name_ext;
                    $this->log()->logDebug( 'new file name ext : ' . $this->file_new_name_ext );
                }
                if (!is_null($this->file_name_body_add))
                { // append a string to the name
                    $this->file_dst_name_body  = $this->file_dst_name_body . $this->file_name_body_add;
                    $this->log()->logDebug( 'file name body append : ' . $this->file_name_body_add );
                }
                if (!is_null($this->file_name_body_pre))
                { // prepend a string to the name
                    $this->file_dst_name_body  = $this->file_name_body_pre . $this->file_dst_name_body;
                    $this->log()->logDebug( 'file name body prepend : ' . $this->file_name_body_pre );
                }
                if ($this->file_safe_name)
                { // formats the name
                    $this->file_dst_name_body = strtr($this->file_dst_name_body, 'ŠŽšžŸÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖØÙÚÛÜÝàáâãäåçèéêëìíîïñòóôõöøùúûüýÿ', 'SZszYAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy');
                    $this->file_dst_name_body = strtr($this->file_dst_name_body, array('Þ' => 'TH', 'þ' => 'th', 'Ð' => 'DH', 'ð' => 'dh', 'ß' => 'ss', 'Œ' => 'OE', 'œ' => 'oe', 'Æ' => 'AE', 'æ' => 'ae', 'µ' => 'u'));
                    $this->file_dst_name_body = preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $this->file_dst_name_body);
                    $this->log()->logDebug( 'file name safe format' );
                }
                $this->log()->logDebug( 'destination variables' );
                if (empty($this->file_dst_path) || is_null($this->file_dst_path))
                {
                    $this->log()->logDebug( 'file_dst_path        : n/a' );
                }
                else
                {
                    $this->log()->logDebug( 'file_dst_path        : ' . $this->file_dst_path );
                }
                $this->log()->logDebug( 'file_dst_name_body    : ' . $this->file_dst_name_body );
                $this->log()->logDebug( 'file_dst_name_ext     : ' . $this->file_dst_name_ext );
                // set the destination file name
                $this->file_dst_name = $this->file_dst_name_body . (!empty($this->file_dst_name_ext) ? '.' . $this->file_dst_name_ext : '');
                if (!$return_mode)
                {
                    if (!$this->file_auto_rename)
                    {
                        $this->log()->logDebug( 'no auto_rename if same filename exists' );
                        $this->file_dst_pathname = $this->file_dst_path . $this->file_dst_name;
                    }
                    else
                    {
                        $this->log()->logDebug( 'checking for auto_rename' );
                        $this->file_dst_pathname = $this->file_dst_path . $this->file_dst_name;
                        $body = $this->file_dst_name_body;
                        $ext = '';
                        // if we have changed the extension, then we add our increment before
                        if ($file_src_name_ext != $this->file_src_name_ext)
                        {
                            if (substr($this->file_dst_name_body, -1 - strlen($this->file_src_name_ext)) == '.' . $this->file_src_name_ext)
                            {
                                $body = substr($this->file_dst_name_body, 0, strlen($this->file_dst_name_body) - 1 - strlen($this->file_src_name_ext));
                                $ext = '.' . $this->file_src_name_ext;
                            }
                        }
                        $cpt = 1;
                        while (@file_exists($this->file_dst_pathname))
                        {
                            $this->file_dst_name_body = $body . '_' . $cpt . $ext;
                            $this->file_dst_name = $this->file_dst_name_body . (!empty($this->file_dst_name_ext) ? '.' . $this->file_dst_name_ext : '');
                            $cpt++;
                            $this->file_dst_pathname = $this->file_dst_path . $this->file_dst_name;
                        }
                        if ($cpt>1) $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;auto_rename to ' . $this->file_dst_name . '<br />';
                    }
                    $this->log()->logDebug( 'destination file details' );
                    $this->log()->logDebug( 'file_dst_name        : ' . $this->file_dst_name );
                    $this->log()->logDebug( 'file_dst_pathname    : ' . $this->file_dst_pathname );
                    if ($this->file_overwrite)
                    {
                        $this->log()->logDebug( 'no overwrite checking' );
                    }
                    else
                    {
                        if (@file_exists($this->file_dst_pathname))
                        {
                            $this->processed = false;
                            $this->error = $this->file_dst_name . ' already exists. Please change the file name.';
                        }
                        else
                        {
                            $this->log()->logDebug( '- ' . $this->file_dst_name . ' doesn\'t already exist' );
                        }
                    }
                }
            }
            if ($this->processed)
            {
                // if we have already moved the uploaded file, we use the temporary copy as source file, and check if it exists
                if (!empty($this->file_src_temp))
                {
                    $this->log()->logDebug( 'use the temp file instead of the original file since it is a second process' );
                    $this->file_src_pathname   = $this->file_src_temp;
                    if (!file_exists($this->file_src_pathname))
                    {
                        $this->processed = false;
                        $this->error = 'No correct temp source file. Can\'t carry on a process.';
                    }
                // if we haven't a temp file, and that we do check on uploads, we use is_uploaded_file()
                }
                else if (!$this->no_upload_check)
                {
                    if (!is_uploaded_file($this->file_src_pathname))
                    {
                        $this->processed = false;
                        $this->error = 'No correct uploaded source file. Can\'t carry on a process.';
                    }
                // otherwise, if we don't check on uploaded files (local file for instance), we use file_exists()
                }
                else
                {
                    if (!file_exists($this->file_src_pathname))
                    {
                        $this->processed = false;
                        $this->error = 'No correct uploaded source file. Can\'t carry on a process.';
                    }
                }
                // checks if the destination directory exists, and attempt to create it
                if (!$return_mode)
                {
                    if ($this->processed && !file_exists($this->file_dst_path))
                    {
                        if ($this->dir_auto_create)
                        {
                            $this->log()->logDebug( '- ' . $this->file_dst_path . ' doesn\'t exist. Attempting creation:' );
                            if (!$this->rmkdir($this->file_dst_path, $this->dir_chmod))
                            {
                                $this->log()->logDebug( '--> failed' );
                                $this->processed = false;
                                $this->error = 'Destination directory can\'t be created. Can\'t carry on a process.';
                            }
                            else
                            {
                                $this->log()->logDebug( '--> success' );
                            }
                        }
                        else
                        {
                            $this->error = 'Destination directory doesn\'t exist. Can\'t carry on a process.';
                        }
                    }
                    if ($this->processed && !is_dir($this->file_dst_path))
                    {
                        $this->processed = false;
                        $this->error = 'Destination path is not a directory. Can\'t carry on a process.';
                    }
                    // checks if the destination directory is writeable, and attempt to make it writeable
                    $hash = md5($this->file_dst_name_body . rand(1, 1000));
                    if ($this->processed && !($f = @fopen($this->file_dst_path . $hash . (!empty($this->file_dst_name_ext) ? '.' . $this->file_dst_name_ext : ''), 'a+')))
                    {
                        if ($this->dir_auto_chmod)
                        {
                            $this->log()->logDebug( '- ' . $this->file_dst_path . ' is not writeable. Attempting chmod:' );
                            if (!@chmod($this->file_dst_path, $this->dir_chmod))
                            {
                                $this->log()->logDebug( '--> failed' );
                                $this->processed = false;
                                $this->error = 'Destination directory can\'t be made writeable. Can\'t carry on a process.';
                            }
                            else
                            {
                                $this->log()->logDebug( '--> success' );
                                if (!($f = @fopen($this->file_dst_path . $hash . (!empty($this->file_dst_name_ext) ? '.' . $this->file_dst_name_ext : ''), 'a+')))
                                { // we re-check
                                    $this->processed = false;
                                    $this->error = 'Destination directory can\'t be made writeable. Can\'t carry on a process.';
                                }
                                else
                                {
                                    @fclose($f);
                                }
                            }
                        }
                        else
                        {
                            $this->processed = false;
                            $this->error = 'Destination path is not a writeable. Can\'t carry on a process.';
                        }
                    }
                    else
                    {
                        if ($this->processed) @fclose($f);
                        @unlink($this->file_dst_path . $hash . (!empty($this->file_dst_name_ext) ? '.' . $this->file_dst_name_ext : ''));
                    }
                    // if we have an uploaded file, and if it is the first process, and if we can't access the file directly (open_basedir restriction)
                    // then we create a temp file that will be used as the source file in subsequent processes
                    // the third condition is there to check if the file is not accessible *directly* (it already has positively gone through is_uploaded_file(), so it exists)
                    if (!$this->no_upload_check && empty($this->file_src_temp) && !@file_exists($this->file_src_pathname))
                    {
                        $this->log()->logDebug( '- attempting to use a temp file:' );
                        $hash = md5($this->file_dst_name_body . rand(1, 1000));
                        if (move_uploaded_file($this->file_src_pathname, $this->file_dst_path . $hash . (!empty($this->file_dst_name_ext) ? '.' . $this->file_dst_name_ext : '')))
                        {
                            $this->file_src_pathname = $this->file_dst_path . $hash . (!empty($this->file_dst_name_ext) ? '.' . $this->file_dst_name_ext : '');
                            $this->file_src_temp = $this->file_src_pathname;
                            $this->log()->logDebug( '--> file created' );
                            $this->log()->logDebug( 'temp file is: ' . $this->file_src_temp );
                        }
                        else
                        {
                            $this->log()->logDebug( '--> failed' );
                            $this->processed = false;
                            $this->error = 'Can\'t create the temporary file. Can\'t carry on a process.';
                        }
                    }
                }
            }
            if ($this->processed)
            {
                if (!$return_mode)
                {
                    // copy the file to its final destination. we don't use move_uploaded_file here
                    // if we happen to have open_basedir restrictions, it is a temp file that we copy, not the original uploaded file
                    if (!copy($this->file_src_pathname, $this->file_dst_pathname))
                    {
                        $this->processed = false;
                        $this->error = 'Error copying file on the server. copy() failed.';
                    }
                }
                else
                {
                    // returns the file, so that its content can be received by the caller
                    $return_content = @file_get_contents($this->file_src_pathname);
                    if ($return_content === FALSE)
                    {
                        $this->processed = false;
                        $this->error = 'Error reading the file.';
                    }
                }
            }
            if ($this->processed)
            {
                $this->log()->logDebug( 'process OK' );
            }
            else
            {
                $this->log()->logDebug( 'error: ' . $this->error );
            }
            // we reinit all the vars
            $this->init();
            // we may return the image content
            if ($return_mode) return $return_content;
        }
    
        /**
        * Deletes the uploaded file from its temporary location
        *
        * When PHP uploads a file, it stores it in a temporary location.
        * When you {@link process} the file, you actually copy the resulting file to the given location, it doesn't alter the original file.
        * Once you have processed the file as many times as you wanted, you can delete the uploaded file.
        * If there is open_basedir restrictions, the uploaded file is in fact a temporary file
        *
        * You might want not to use this function if you work on local files, as it will delete the source file
        *
        * @access public
        */
        function clean()
        {
            @unlink($this->file_src_pathname);
        }

        /**
         * Uploads a list of files, contained in $_FILES[$param_name]
         *
         * @access public
         * @param  string  $param_name - the fieldname of <input type="file" />
         *                    default: 'files'
         * @param  string  $folder     - destination folder
         * @param  boolean $ajax       - AJAX request (if true, returns JSON)
         * @param  boolean $overwrite  - allow overwrite or not (default)
         * @return
         **/
        public static function uploadAll($param_name='files',$folder=NULL,$ajax=false,$overwrite=false)
        {
            if(!$folder || $folder == '')
                if(!$ajax)
                    self::printError('You must pass a folder!');
                else
                    return false;

            $files  = array();
            $errors = array();
            $ok     = array();
            $upload = isset($_FILES[$param_name])
                    ? $_FILES[$param_name]
                    : null
                    ;

            if ($upload && is_array($upload))
            {
                if(isset($upload['name']))
                    $files[] = self::getInstance($upload);
                else
                    foreach ($upload as $file)
                        $files[] = self::getInstance($file);
            }

            if(is_array($files) && count($files))
            {
                foreach($files as $file)
                {
                    $file->file_overwrite = $overwrite;
                    $file->process($folder);
                    if ($file->processed)
                        $ok[$file->file_dst_name] = $file->file_src_size;
                    else
                        $errors[$file->file_src_name] = $file->error;
                }
            }
            return array( $ok, $errors );
        }   // end function uploadAll()

        /**
         *
         * @access public
         * @return
         **/
        public static function getError() {
            return $this->error;
        }   // end function getError()
        
        /**
         *
         * @access public
         * @return
         **/
        public function getMimeType()
        {
            // most secure method, uses file header
            // see http://getid3.sourceforge.net/ for a list of supported file types
            if (file_exists(CAT_PATH.'/modules/lib_getid3/getid3/getid3.php'))
            {
                $this->log()->logDebug( '- Checking MIME type with getID3 library' );
                $mime = $this->getID3Mime();
            }
            // quite secure on *NIX systems
            elseif ($this->mime_file && substr(PHP_OS, 0, 3) != 'WIN')
            {
                $this->log()->logDebug( 'Checking MIME type with UNIX file() command' );
                $mime = $this->getUNIXMime();
            }
            // still quite secure...
            elseif ($this->mime_fileinfo)
            {
                $this->log()->logDebug( '- Checking MIME type with PECL extension' );
                $mime = $this->getPECLMime();
            }
            // NOT secure! Uses suffix only!
            elseif ($this->mime_magic)
            {
                $this->log()->logDebug( '- Checking MIME type with mime.magic file (mime_content_type())' );
                $mime = $this->getMagicMime();
            }
            if($mime)
                $this->file_src_mime = $mime;
            else
                $this->file_src_mime = $this->mime_default_type;
        }   // end function getMimeType()
        

        /**
         *
         * @access public
         * @return
         **/
        public function getID3Mime()
        {

            $mime     = NULL;
        	$filename = realpath($this->file_src_pathname);

        	if (!file_exists($filename))
            {
        		$this->error = 'File does not exist: "'.htmlentities($filename);
        		return false;
        	}
            elseif (!is_readable($filename))
            {
        		$this->error = 'File is not readable: "'.htmlentities($filename);
        		return false;
        	}

        	require_once CAT_PATH.'/modules/lib_getid3/getid3/getid3.php';

        	$getID3 = new getID3;

        	if ($fp = fopen($filename, 'rb'))
            {
        		$getID3->openfile($filename);
        		if (empty($getID3->info['error']))
                {

        			// ID3v2 is the only tag format that might be prepended in front of files, and it's non-trivial to skip, easier just to parse it and know where to skip to
        			getid3_lib::IncludeDependency(GETID3_INCLUDEPATH.'module.tag.id3v2.php', __FILE__, true);
        			$getid3_id3v2 = new getid3_id3v2($getID3);
        			$getid3_id3v2->Analyze();

        			fseek($fp, $getID3->info['avdataoffset'], SEEK_SET);
        			$formattest = fread($fp, 16);  // 16 bytes is sufficient for any format except ISO CD-image
        			fclose($fp);

        			$DeterminedFormatInfo = $getID3->GetFileFormat($formattest);
        			$mime = $DeterminedFormatInfo['mime_type'];

        		}
                else
                {
        			$this->error = 'Failed to getID3->openfile "'.htmlentities($filename);
        		}
        	}
            else
            {
        		$this->error = 'Failed to fopen "'.htmlentities($filename);
        	}
            $this->log()->logDebug( 'MIME type detected as [' . $mime . '] by getID3 library' );
        	return $mime;
        }   // end function getID3Mime()

        /**
         *
         * @access public
         * @return
         **/
        public function getPECLMime()
        {
            $this->log()->logDebug( '- Checking MIME type with Fileinfo PECL extension' );
            $mime = NULL;

            if (function_exists('finfo_open'))
            {
                if ($this->mime_fileinfo !== '')
                {
                    if ($this->mime_fileinfo === true)
                    {
                        if (getenv('MAGIC') === FALSE)
                        {
                            if (substr(PHP_OS, 0, 3) == 'WIN')
                            {
                                $path = realpath(ini_get('extension_dir') . '/../') . 'extras/magic';
                            }
                            else
                            {
                                $path = '/usr/share/file/magic';
                            }
                            $this->log()->logDebug( 'MAGIC path defaults to ' . $path );
                        }
                        else
                        {
                            $path = getenv('MAGIC');
                            $this->log()->logDebug( 'MAGIC path is set to ' . $path . ' from MAGIC variable' );
                        }
                    }
                    else
                    {
                        $path = $this->mime_fileinfo;
                        $this->log()->logDebug( 'MAGIC path is set to ' . $path );
                    }
                    $f = @finfo_open(FILEINFO_MIME, $path);
                }
                else
                {
                    $this->log()->logDebug( 'MAGIC path will not be used' );
                    $f = @finfo_open(FILEINFO_MIME);
                }
                if (is_resource($f))
                {
                    $mime = finfo_file($f, realpath($this->file_src_pathname));
                    finfo_close($f);
                    $this->log()->logDebug( 'MIME type detected as ' . $mime . ' by Fileinfo PECL extension' );
                    if (preg_match("/^([\.-\w]+)\/([\.-\w]+)(.*)$/i", $mime))
                    {
                        $mime = preg_replace("/^([\.-\w]+)\/([\.-\w]+)(.*)$/i", '$1/$2', $mime);
                        $this->log()->logDebug( 'MIME validated as ' . $mime );
                    }
                }
                else
                {
                    $this->log()->logDebug( 'Fileinfo PECL extension failed (finfo_open)' );
                }
            }   // end if (function_exists('finfo_open'))
            elseif (@class_exists('finfo'))
            {
                $f = new finfo( FILEINFO_MIME );
                if ($f)
                {
                    $mime = $f->file(realpath($this->file_src_pathname));
                    $this->log()->logDebug( 'MIME type detected as ' . $mime . ' by Fileinfo PECL extension' );
                    if (preg_match("/^([\.-\w]+)\/([\.-\w]+)(.*)$/i", $mime))
                    {
                        $mime = preg_replace("/^([\.-\w]+)\/([\.-\w]+)(.*)$/i", '$1/$2', $mime);
                        $this->log()->logDebug( 'MIME validated as ' . $mime );
                    }
                }
                else
                {
                    $this->log()->logDebug( 'Fileinfo PECL extension failed (finfo)' );
                }
            }
            else
            {
                $this->log()->logDebug( 'Fileinfo PECL extension not available' );
            }

            return $mime;

        }   // end function getPECLMime()
        
        /**
         *
         * @access public
         * @return
         **/
        public function getUNIXMime()
        {
            $mime = NULL;

            // we've already checked this above, but the method may be called
            // from outside
            if (substr(PHP_OS, 0, 3) != 'WIN')
            {
                if (function_exists('exec'))
                {
                    if (strlen($mime = @exec("file -bi ".escapeshellarg($this->file_src_pathname))) != 0)
                    {
                        $mime = trim($mime);
                        $this->log()->logDebug( 'MIME type detected as ' . $mime . ' by UNIX file() command' );
                        if (preg_match("/^([\.-\w]+)\/([\.-\w]+)(.*)$/i", $mime))
                        {
                            $mime = preg_replace("/^([\.-\w]+)\/([\.-\w]+)(.*)$/i", '$1/$2', $mime);
                            $this->log()->logDebug( 'MIME validated as ' . $mime );
                        }
                    }
                    else
                    {
                        $this->log()->logDebug( 'UNIX file() command failed' );
                    }
                }
                else
                {
                    $this->log()->logDebug( 'PHP exec() function is disabled' );
                }
            }
            else
            {
                $this->log()->logDebug( 'UNIX file() command not availabled' );
            }

            return $mime;

        }   // end function getUNIXMime()

        /**
         *
         * @access public
         * @return
         **/
        public function getMagicMime()
        {
            $mime = NULL;

            if (function_exists('mime_content_type'))
            {
                $mime = mime_content_type($this->file_src_pathname);
                $this->log()->logDebug( 'MIME type detected as ' . $mime . ' by mime_content_type()' );
                if (preg_match("/^([\.-\w]+)\/([\.-\w]+)(.*)$/i", $mime))
                {
                    $mime = preg_replace("/^([\.-\w]+)\/([\.-\w]+)(.*)$/i", '$1/$2', $mime);
                    $this->log()->logDebug( 'MIME validated as ' . $mime );
                }
            }
            else
            {
                $this->log()->logDebug( 'mime_content_type() is not available' );
            }

            return $mime;

        }   // end function getMagicMime()
        

    }
}

?>