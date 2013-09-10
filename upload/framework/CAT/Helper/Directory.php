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

if ( ! class_exists( 'CAT_Object', false ) ) {
    @include dirname(__FILE__).'/../Object.php';
}

if ( ! class_exists( 'CAT_Helper_Directory', false ) ) {
	class CAT_Helper_Directory extends CAT_Object
	{
	
	    protected static $recurse = true;
        protected static $max_recursion_depth = 100;
	    protected static $prefix  = NULL;
	    protected static $suffix_filter = array();
	    protected static $skip_dirs     = array();
        protected static $skip_files    = array();
        protected static $current_depth = 0;

        private static $instance;

        public static function getInstance()
        {
            if (!self::$instance)
                self::$instance = new self();
            else
                self::reset();
            return self::$instance;
        }

        public function __call($method, $args)
        {
            if ( ! isset($this) || ! is_object($this) )
                return false;
            if ( method_exists( $this, $method ) )
                return call_user_func_array(array($this, $method), $args);
        }
	    
        /**
         * copy directory structure with files
         *
         * @access public
         * @param  string  $dirsource
         * @param  string  $dirdest
         **/
        public static function copyRecursive( $dirsource, $dirdest )
        {
            if (is_dir($dirsource))
                $dir_handle = dir($dirsource);
            else
                return false;

            if ( ! is_object($dir_handle) )
                return false;

            while ($file = $dir_handle->read())
            {
                if ($file != "." && $file != "..")
                {
                    if (!is_dir($dirsource . "/" . $file))
                    {
                        copy($dirsource . "/" . $file, $dirdest . '/' . $file);
                        if ($file != '.svn' && $file != '.git')
                            CAT_Helper_Directory::setPerms($dirdest . "/" . $file);
                    }
                    else
                    {
                        CAT_Helper_Directory::createDirectory( $dirdest . '/' . $file );
                        self::copyRecursive($dirsource . "/" . $file, $dirdest . '/' . $file);
                    }
                }
            }
            $dir_handle->close();
            return true;
        }   // end function copyRecursive()

        /**
         *
         *
         *
         *
         **/
        public static function findDirectories( $pattern, $dir, $remove_dir = false )
        {
            $list  = self::scanDirectory( $dir, false, false );
            $dirs  = array();
            // sort list
            sort($list);
            foreach($list as $entry)
            {
                if( preg_match( "~^$pattern$~i", pathinfo($entry,PATHINFO_BASENAME) ) )
                {
                    $dirs[] = $remove_dir
                            ? str_ireplace( $dir, '', $entry )
                            : $entry;
                }
            }
            return $dirs;
        }   // end function findDirectories()
	    
        /**
         * find file with given name; returns file path if found, false if not
         *
         * @access public
         * @param  string  $file - file to find
         * @param  string  $dir  - directory to scan
         * @return mixed
         **/
        public static function findFile( $file, $dir, $ignore_suffix = false )
        {
            $list = self::scanDirectory( $dir, true, true );
            // sort list
            sort($list);
            foreach($list as $entry)
            {
                // direct match
                if( preg_match( "~^$file$~i", pathinfo($entry,PATHINFO_BASENAME) ) )
                {
                    return $entry;
                }
                // match with suffix ignored
                if ( $ignore_suffix && pathinfo($file,PATHINFO_FILENAME) == pathinfo($entry,PATHINFO_FILENAME) )
                {
                    return $entry;
                }
            }
            return false;
        }   // end function findFile()

        /**
         *
         *
         *
         *
         **/
        public static function findFiles( $pattern, $dir, $remove_dir = false )
        {
            $list  = self::scanDirectory( $dir, true, true );
            $files = array();
            // sort list
            sort($list);
            foreach($list as $entry)
            {
                if( preg_match( "~^$pattern$~i", pathinfo($entry,PATHINFO_BASENAME) ) )
                {
                    $files[] = $remove_dir
                             ? str_ireplace( $dir, '', $entry )
                             : $entry;
                }
            }
            return $files;
        }   // end function findFiles()

        /**
         *
         * @access public
         * @return
         **/
        public static function getFilesOlderThan( $time, $dir, $remove_dir = false )
        {
            $list  = self::scanDirectory( $dir, true, true ); // get all files
            $files = array();
            // sort list
            sort($list);
            foreach($list as $entry)
            {
                $stat = stat($entry);
                if( $stat['mtime'] < $time )
                {
                    $files[] = $remove_dir
                             ? str_ireplace( $dir, '', $entry )
                             : $entry;
                }
            }
            return $files;
        }   // end function getFilesOlderThan()

        /**
         *
         **/
        public static function getMode($for='file')
        {
            $mode = NULL;
            if (OPERATING_SYSTEM != 'windows')
            {
                if ($for=='directory')
                {
                    $mode = CAT_Registry::exists('OCTAL_DIR_MODE')
                          ? CAT_Registry::get('OCTAL_DIR_MODE')
                          : self::defaultDirMode()
                          ;
                }
                else
                {
                    $mode = CAT_Registry::exists('OCTAL_FILE_MODE')
                          ? CAT_Registry::get('OCTAL_FILE_MODE')
                          : self::defaultFileMode();
                }
            }
            return $mode;
        }   // end function getMode()
	    
	    /**
	     * shortcut method for scanDirectory( $dir, $remove_prefix, true, true )
	     **/
		public static function getFiles( $dir, $remove_prefix = NULL )
		{
		    return self::scanDirectory( $dir, true, true, $remove_prefix );
		}   // end function getFiles()
		
		/**
	     * shortcut method for scanDirectory( $dir, $remove_prefix, false, false )
	     **/
		public static function getDirectories( $dir, $remove_prefix = NULL )
		{
		    return self::scanDirectory( $dir, false, false, $remove_prefix );
		}   // end function getFiles()
		
	    /**
	     * shortcut method for scanDirectory( $dir, $remove_prefix, true, true, array('php') )
	     **/
		public static function getPHPFiles( $dir, $remove_prefix = NULL )
		{
		    return self::scanDirectory( $dir, true, true, $remove_prefix, array('php') );
		}   // end function getPHPFiles()

		/**
	     * shortcut method for scanDirectory( $dir, $remove_prefix, true, true, array('lte','htt','tpl') )
	     **/
		public static function getTemplateFiles( $dir, $remove_prefix = NULL )
		{
		    return self::scanDirectory( $dir, true, true, $remove_prefix, array('lte','htt','tpl') );
		}   // end function getTemplateFiles()

		/**
         * convert bytes to human readable string
         *
         * @access public
         * @param  integer $bytes
         * @return string
         **/
        public static function byte_convert($bytes)
        {
        	$symbol = array(' bytes', ' KB', ' MB', ' GB', ' TB');
        	$exp = 0;
        	$converted_value = 0;
        	if ($bytes > 0)
        	{
        		$exp = floor( log($bytes) / log(1024));
        		$converted_value = ($bytes / pow( 1024, floor($exp)));
        	}
        	return sprintf('%.2f '.$symbol[$exp], $converted_value);
        }   // end function byte_convert()

        /**
         * get file size
         *
         * @access public
         * @param  string  $file
         * @param  boolean $convert - call byte_convert(); default: false
         * @return string
         **/
        public static function getSize($file,$convert=false)
        {
            if(is_dir($file)) return false;
        	$size = filesize($file);
        	if ($size < 0)
        	if (!(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN'))
        		$size = trim(`stat -c%s $file`);
        	else{
        		$fsobj = new COM("Scripting.FileSystemObject");
        		$f = $fsobj->GetFile($file);
        		$size = $file->Size;
        	}
            if($size && $convert) $size = self::byte_convert($size);
        	return $size;
        }   // end function getSize()

		/**
		 * fixes a path by removing //, /../ and other things
		 *
		 * @access public
		 * @param  string  $path - path to fix
		 * @return string
		 **/
		public static function sanitizePath( $path )
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
	            {
	                array_pop($parts);
	            }
	            elseif ($part!="")
	            {
	                $parts[] = $part;
	            }
	        }

	        $new_path = implode("/", $parts);
	        // windows
	        if ( ! preg_match( '/^[a-z]\:/i', $new_path ) ) {
				$new_path = '/' . $new_path;
			}

	        return $new_path;
		
		}   // end function sanitizePath()
		
		/**
		 * scans a directory
		 *
		 * @access public
		 * @param  string  $dir - directory to scan
		 * @param  boolean $with_files    - list files too (true) or not (false); default: false
		 * @param  boolean $files_only    - list files only (true) or not (false); default: false
		 * @param  string  $remove_prefix - will be removed from the path names; default: NULL
		 * @param  array   $suffixes      - list of suffixes; only if $with_files = true
		 * @param  array   $skip_dirs     - list of directories to skip
		 *
		 * Examples:
		 *   - get a list of all subdirectories (no files)
		 *     $dirs = $obj->scanDirectory( <DIR> );
		 *
		 *   - get a list of files only
		 *     $files = $obj->scanDirectory( <DIR>, NULL, true, true );
		 *
		 *   - get a list of files AND directories
		 *     $list = $obj->scanDirectory( <DIR>, NULL, true );
		 *
		 *   - remove a path prefix
		 *     $list = $obj->scanDirectory( '/my/abs/path/to', '/my/abs/path' );
		 *     => result is /to/subdir1, /to/subdir2, ...
		 *
		 **/
		public static function scanDirectory( $dir, $with_files = false, $files_only = false, $remove_prefix = NULL, $suffixes = array(), $skip_dirs = array(), $skip_files = array() ) {

			$dirs = array();

			// make sure $suffixes is an array
            if ( $suffixes && is_scalar($suffixes) ) {
                $suffixes = array( $suffixes );
			}
			if ( ! count($suffixes) && count( self::$suffix_filter ) ) {
			    $suffixes = self::$suffix_filter;
			}
			// make sure $skip_dirs is an array
			if ( $skip_dirs && is_scalar($skip_dirs) ) {
			    $skip_dirs = array( $skip_dirs );
			}
			if ( ! count($skip_dirs) && count( self::$skip_dirs ) )
			{
			    $skip_dirs = self::$skip_dirs;
			}
            // same for $skip_files
            if ( $skip_files && is_scalar($skip_files) ) {
			    $skip_files = array( $skip_files );
			}
			if ( ! count($skip_files) && count( self::$skip_files ) )
			{
			    $skip_files = self::$skip_files;
			}
			if ( ! $remove_prefix && self::$prefix )
			{
			    $remove_prefix = self::$prefix;
			}

            if ( self::$current_depth > self::$max_recursion_depth ) { return array(); }

			if (false !== ($dh = opendir( $dir ))) {
                while( false !== ($file = readdir($dh))) {
                    if ( ! preg_match( '#^\.#', $file ) ) {
						if ( count($skip_dirs) && in_array( pathinfo( $dir.'/'.$file, (is_dir($dir.'/'.$file)?PATHINFO_BASENAME:PATHINFO_DIRNAME)), $skip_dirs) )
						{
						    continue;
						}
#if ( count($skip_files) )
#    echo "checking -", pathinfo($dir.'/'.$file,PATHINFO_BASENAME), "- against -", print_r($skip_files), "-<br />";
                        if ( count($skip_files) && in_array( pathinfo($dir.'/'.$file,PATHINFO_BASENAME), $skip_files) )
						{
						    continue;
						}
                        if ( is_dir( $dir.'/'.$file ) ) {
                            if ( ! $files_only ) {
                                $dirs[]  = str_ireplace( $remove_prefix, '', $dir.'/'.$file );
                            }
                            if ( self::$recurse )
                            {
                            	// recurse
                                self::$current_depth++;
                            	$subdirs = self::scanDirectory( $dir.'/'.$file, $with_files, $files_only, $remove_prefix, $suffixes, $skip_dirs, $skip_files );
                            	$dirs    = array_merge( $dirs, $subdirs );
                                self::$current_depth--;
							}
                        }
                        elseif ( $with_files ) {
                            if ( ! count($suffixes) || in_array( pathinfo($file,PATHINFO_EXTENSION), $suffixes ) )
                            {
                            	$dirs[]  = str_ireplace( $remove_prefix, '', $dir.'/'.$file );
							}
                        }
#                        else {
#                        }
                    }
                }
            }
            return $dirs;
        }   // end function scanDirectory()

		/**
		 *
		 **/
		public static function setPrefix( $prefix )
		{
		    if ( is_scalar($prefix) )
		    {
		        self::$prefix = $prefix;
		        return;
			}
			// reset
			if ( is_null($prefix) )
			{
			    self::$prefix = NULL;
			}
            if(self::$instance) return self::$instance;
		}   // end function setPrefix()

        /**
         *
         **/
		public static function setRecursion( $bool )
		{
		    if ( is_bool($bool) )
		    {
		        self::$recurse = $bool;
			}
            if(self::$instance) return self::$instance;
		}   // end function setRecursion()

        /**
         *
         **/
		public static function maxRecursionDepth( $number = 100 )
		{
		    if ( is_numeric($number) )
		    {
		        self::$max_recursion_depth = $number;
			}
            if(self::$instance) return self::$instance;
		}   // end function setRecursion()

        /**
         *
         **/
        public static function setSkipFiles($files)
        {
            // reset
		    if ( is_null( $files ) )
		    {
		        self::$skip_files = array();
		        return;
			}
		    // make sure $dirs is an array
            if ( $files && is_scalar($files) )
                $files = array( $files );
			if ( is_array($files) )
			    self::$skip_files = $files;
            if(self::$instance) return self::$instance;
        }   // end function setSkipFiles()
		
		/**
		 *
		 **/
		public static function setSkipDirs( $dirs )
		{
		    // reset
		    if ( is_null( $dirs ) )
		    {
		        self::$skip_dirs = array();
		        return;
			}
		    // make sure $dirs is an array
            if ( $dirs && is_scalar($dirs) ) {
                $dirs = array( $dirs );
			}
			if ( is_array($dirs) )
			{
			    self::$skip_dirs = $dirs;
			}
            if(self::$instance) return self::$instance;
		}   // end function setSkipDirs()
		
		/**
		 *
		 **/
		public static function setSuffixFilter( $suffixes )
		{
		    // reset
		    if ( is_null( $suffixes ) )
		    {
		        self::$suffix_filter = array();
		        return;
			}
		    // make sure $suffixes is an array
            if ( $suffixes && is_scalar($suffixes) ) {
                $suffixes = array( $suffixes );
			}
			if ( is_array($suffixes) )
			{
			    self::$suffix_filter = $suffixes;
			}
            if(self::$instance) return self::$instance;
		}   // end function setSuffixFilter()
		
		/**
		 * set directory or file to read-only; used for index.php
		 *
		 * @access public
		 * @param  string $directory
		 * @return void
		 *
		 **/
        public static function setReadOnly($item)
	    {
	        // Only chmod if os is not windows
	        if (OPERATING_SYSTEM != 'windows')
	        {
                $mode = (int) octdec( '644' );
	            if (file_exists($item))
	            {
	                $umask = umask(0);
	                chmod($item, $mode);
	                umask($umask);
	                return true;
	            }
	            else
	            {
	                return false;
	            }
	        }
	        else
	        {
	            return true;
	        }
            if(self::$instance) return self::$instance;
	    }   // function setReadOnly()
	    
        /**
         * This method creates index.php files in every subdirectory of a given path
         *
         * @access public
         * @param  string  directory to start with
         * @return void
         *
         **/
        public static function recursiveCreateIndex( $dir )
        {
            if ( $handle = dir($dir) )
            {
                if ( ! file_exists( $dir . '/index.php' ) )
                {
                    $fh = fopen( $dir.'/index.php', 'w' );
                    fwrite( $fh, '<' . '?' . 'php' . "\n" );
        	        fwrite( $fh, self::_class_secure_code() );
        	        fclose( $fh );
                }

                while ( false !== ( $file = $handle->read() ) )
                {
                    if ( $file != "." && $file != ".." )
                    {
                        if( is_dir( $dir.'/'.$file ) )
                        {
                            self::recursiveCreateIndex( $dir.'/'.$file );
                        }
                    }
                }
                $handle->close();
                return true;
            }
            else {
                return false;
            }
        }   // end function recursiveCreateIndex()


		/**
		 * Create directories recursive
		 *
		 * @access public
		 * @param string   $dir_name - directory to create
		 * @param ocatal   $dir_mode - access mode
		 * @return boolean result of operation
		 *
		 * @todo ---check for valid dir name---
		 **/
		public static function createDirectory( $dir_name, $dir_mode = NULL, $createIndex = false )
		{
             if ( ! $dir_mode )
             {
                 $dir_mode = CAT_Registry::exists('OCTAL_DIR_MODE')
                           ? CAT_Registry::get('OCTAL_DIR_MODE')
                           : (int) octdec(self::defaultDirMode());
             }
		     if ( $dir_name != '' && !is_dir($dir_name) )
		     {
		         $umask = umask(0);
		         mkdir($dir_name, $dir_mode, true);
		         umask($umask);
		         if ( $createIndex )
		         {
			         self::recursiveCreateIndex( $dir_name );
		         }
		         return true;
		     }
		     return false;
		 }   // end function createDirectory()

		/**
		 * remove directory recursively
		 *
		 * @access public
		 * @param  string  $directory
		 * @return boolean
		 *
		 **/
	    public static function removeDirectory($directory)
	    {
	        // If suplied dirname is a file then unlink it
	        if (is_file($directory))
	        {
	            return unlink($directory);
	        }
	        // Empty the folder
	        if (is_dir($directory))
	        {
	            $dir = dir($directory);
	            while (false !== $entry = $dir->read())
	            {
	                // Skip pointers
	                if ($entry == '.' || $entry == '..')
	                {
	                    continue;
	                }
	                // recursive delete
	                if (is_dir($directory . '/' . $entry))
	                {
	                    self::removeDirectory($directory . '/' . $entry);
	                }
	                else
	                {
	                    unlink($directory . '/' . $entry);
	                }
	            }
	            // Now delete the folder
	            $dir->close();
	            return rmdir($directory);
	        }
	    }   // end function removeDirectory()
        /**
         * move directory with all contents by first copying it and then
         * removing the source
         *
         * @access public
         * @param  string  $src
         * @param  string  $target
         * @return
         **/
        public static function moveDirectory($src,$target,$createIndex = false)
        {
            if(!is_dir($target))
                self::createDirectory($target,NULL,$createIndex);
            if(self::copyRecursive($src,$target)===true)
                if(self::removeDirectory($src)===true)
                    return true;
            return false;
        }   // end function moveDirectory()
        
	    
        /**
         * set access perms for directory; the perms are set in the backend,
         * so there's no param for this
         *
         * @access public
         * @param  string  $directory
         * @return void
         **/
        public static function setPerms($directory)
        {
            $mode  = self::getMode();
            if ( $mode === NULL ) return;

            $umask = umask(0);
            if (!is_dir($directory))
            {
                if ( file_exists($directory) )
                {
                    chmod($directory, $mode);
                    umask($umask);
                }
            }
            else {
                // Open the directory then loop through its contents
                $dir = dir($directory);
                while (false !== $entry = $dir->read())
                {
                    if (!preg_match('~^.~',$entry) && is_dir("$directory/$entry"))
                    {
                        chmod("$directory/$entry",self::getMode('directory'));
                        self::setPerms($directory . '/' . $entry);
                    }
                }
                $dir->close();
            }
            // Restore the umask
            umask($umask);
        }

        /**
         *
         * @access public
         * @return
         **/
        public static function is_empty($directory,$ignore_index=false)
        {
            if (!is_readable($directory)) return NULL;
            $handle = opendir($directory);
            if (!is_resource($handle))    return NULL;
            while (false !== ($entry = readdir($handle)))
            {
                if ($entry != "." && $entry != "..")
                {
                    if( $ignore_index && $entry == 'index.php')
                    {
                        continue;
                    }
                    return false;
                }
            }
            return true;
        }   // end function is_empty()
        

	    /**
	     * check if directory is world-writable
	     * hopefully more secure than is_writable()
	     *
	     * @access public
	     * @param  string  $directory
	     * @return boolean
	     *
	     **/
		public static function is_world_writable($directory)
		{
		    if ( ! is_dir( $directory ) )
		    {
		        return false;
			}
		    return ( substr(sprintf('%o', fileperms($directory)), -1) == 7 ? true : false );
		}   // end function is_world_writable()
		
		/**
		 * If the configuration setting 'string_dir_mode' is missing, we need
		 * a default value that fits most cases.
		 *
         * @access public
         * @return string
         **/
        public static function defaultDirMode() {
            return (
                  (OPERATING_SYSTEM != 'windows')
                ? '0755'
                : '0777'
            );
        }   // end function defaultDirMode()

        /**
         *
         * @access public
         * @return
         **/
        public static function defaultFileMode() {
            // we've already created some new files, so just check the perms they've got
            $check_for = dirname(__FILE__).'/../../../temp/logs/index.php';
            if ( file_exists($check_for) ) {
                $default_file_mode = octdec( '0'.substr(sprintf('%o', fileperms($check_for)), -3) );
            } else {
                $default_file_mode = '0777';
            }
            return $default_file_mode;
        }   // end function defaultFileMode()

        /**
         *
         * @access public
         * @return
         **/
        public static function reset() {
            // reset to defaults
            self::$instance->setRecursion(true);
            self::$instance->maxRecursionDepth();
            self::$instance->setPrefix(NULL);
            self::$instance->setSkipFiles(NULL);
            self::$instance->setSkipDirs(NULL);
            self::$instance->setSuffixFilter(NULL);
        }   // end function reset()

		/**
		 *
		 *
		 *
		 *
		 **/
		private static function _class_secure_code()
		{
			return "
// include class.secure.php to protect this file and the whole CMS!
if (defined('CAT_PATH')) {
	include(CAT_PATH.'/framework/class.secure.php');
} else {
	\$oneback = \"../\";
	\$root = \$oneback;
	\$level = 1;
	while ((\$level < 10) && (!file_exists(\$root.'/framework/class.secure.php'))) {
		\$root .= \$oneback;
		\$level += 1;
	}
	if (file_exists(\$root.'/framework/class.secure.php')) {
		include(\$root.'/framework/class.secure.php');
	} else {
		trigger_error(sprintf(\"[ <b>%s</b> ] Can't include class.secure.php!\", \$_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}
// end include class.secure.php
";
		}   // end function _class_secure_code()

	}
}

?>
