<?php

/**
 *
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 *
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON v2.0 Black Cat Edition Development
 * @copyright       2013, LEPTON v2.0 Black Cat Edition Development
 * @link            http://www.lepton2.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 *
 */

if ( ! class_exists( 'LEPTON_Object', false ) ) {
    @include dirname(__FILE__).'/../Object.php';
}

if ( ! class_exists( 'LEPTON_Helper_Directory', false ) ) {
	class LEPTON_Helper_Directory extends LEPTON_Object
	{
	
	    protected $recurse = true;
	    protected $prefix  = NULL;
	    protected $suffix_filter = array();
	    protected $skip_dirs     = array();
        protected $skip_files    = array();
	    
	    /**
	     * shortcut method for scanDirectory( $dir, $remove_prefix, true, true )
	     **/
		public function getFiles( $dir, $remove_prefix = NULL )
		{
		    return $this->scanDirectory( $dir, true, true, $remove_prefix );
		}   // end function getFiles()
		
		/**
	     * shortcut method for scanDirectory( $dir, $remove_prefix, false, false )
	     **/
		public function getDirectories( $dir, $remove_prefix = NULL )
		{
		    return $this->scanDirectory( $dir, false, false, $remove_prefix );
		}   // end function getFiles()
		
	    /**
	     * shortcut method for scanDirectory( $dir, $remove_prefix, true, true, array('php') )
	     **/
		public function getPHPFiles( $dir, $remove_prefix = NULL )
		{
		    return $this->scanDirectory( $dir, true, true, $remove_prefix, array('php') );
		}   // end function getPHPFiles()

		/**
	     * shortcut method for scanDirectory( $dir, $remove_prefix, true, true, array('lte','htt','tpl') )
	     **/
		public function getTemplateFiles( $dir, $remove_prefix = NULL )
		{
		    return $this->scanDirectory( $dir, true, true, $remove_prefix, array('lte','htt','tpl') );
		}   // end function getTemplateFiles()

		/**
		 * fixes a path by removing //, /../ and other things
		 *
		 * @access public
		 * @param  string  $path - path to fix
		 * @return string
		 **/
		public function sanitizePath( $path )
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
		function scanDirectory( $dir, $with_files = false, $files_only = false, $remove_prefix = NULL, $suffixes = array(), $skip_dirs = array(), $skip_files = array() ) {

			$dirs = array();

			// make sure $suffixes is an array
            if ( $suffixes && is_scalar($suffixes) ) {
                $suffixes = array( $suffixes );
			}
			if ( ! count($suffixes) && count( $this->suffix_filter ) ) {
			    $suffixes = $this->suffix_filter;
			}
			// make sure $skip_dirs is an array
			if ( $skip_dirs && is_scalar($skip_dirs) ) {
			    $skip_dirs = array( $skip_dirs );
			}
			if ( ! count($skip_dirs) && count( $this->skip_dirs ) )
			{
			    $skip_dirs = $this->skip_dirs;
			}
            // same for $skip_files
            if ( $skip_files && is_scalar($skip_files) ) {
			    $skip_files = array( $skip_files );
			}
			if ( ! count($skip_files) && count( $this->skip_files ) )
			{
			    $skip_files = $this->skip_files;
			}
			if ( ! $remove_prefix && $this->prefix )
			{
			    $remove_prefix = $this->prefix;
			}

			if (false !== ($dh = opendir( $dir ))) {
                while( false !== ($file = readdir($dh))) {
                    if ( ! preg_match( '#^\.#', $file ) ) {
						if ( count($skip_dirs) && in_array( pathinfo($dir.'/'.$file,PATHINFO_DIRNAME), $skip_dirs) )
						{
						    continue;
						}
                        if ( count($skip_files) && in_array( pathinfo($dir.'/'.$file,PATHINFO_FILENAME), $skip_files) )
						{
						    continue;
						}
                        if ( is_dir( $dir.'/'.$file ) ) {
                            if ( ! $files_only ) {
                                $dirs[]  = str_ireplace( $remove_prefix, '', $dir.'/'.$file );
                            }
                            if ( $this->recurse )
                            {
                            	// recurse
                            	$subdirs = $this->scanDirectory( $dir.'/'.$file, $with_files, $files_only, $remove_prefix, $suffixes, $skip_dirs );
                            	$dirs    = array_merge( $dirs, $subdirs );
							}
                        }
                        elseif ( $with_files ) {
                            if ( ! count($suffixes) || in_array( pathinfo($file,PATHINFO_EXTENSION), $suffixes ) )
                            {
                            	$dirs[]  = str_ireplace( $remove_prefix, '', $dir.'/'.$file );
							}
                        }
                    }
                }
            }
            return $dirs;
        }   // end function scanDirectory()

		/**
		 *
		 **/
		public function setPrefix( $prefix )
		{
		    if ( is_scalar($prefix) )
		    {
		        $this->prefix = $prefix;
		        return;
			}
			// reset
			if ( is_null($prefix) )
			{
			    $this->prefix = NULL;
			}
		}   // end function setPrefix()

        /**
         *
         **/
		public function setRecursion( $bool )
		{
		    if ( is_bool($bool) )
		    {
		        $this->recurse = $bool;
			}
		}   // end function setRecursion()

        /**
         *
         **/
        public function setSkipFiles($files)
        {
            // reset
		    if ( is_null( $files ) )
		    {
		        $this->skip_files = array();
		        return;
			}
		    // make sure $dirs is an array
            if ( $files && is_scalar($files) ) {
                $files = array( $files );
			}
			if ( is_array($files) )
			{
			    $this->skip_files = $files;
			}
        }   // end function setSkipFiles()
		
		/**
		 *
		 **/
		public function setSkipDirs( $dirs )
		{
		    // reset
		    if ( is_null( $dirs ) )
		    {
		        $this->skip_dirs = array();
		        return;
			}
		    // make sure $dirs is an array
            if ( $dirs && is_scalar($dirs) ) {
                $dirs = array( $dirs );
			}
			if ( is_array($dirs) )
			{
			    $this->skip_dirs = $dirs;
			}
		}   // end function setSkipDirs()
		
		/**
		 *
		 **/
		public function setSuffixFilter( $suffixes )
		{
		    // reset
		    if ( is_null( $suffixes ) )
		    {
		        $this->suffix_filter = array();
		        return;
			}
		    // make sure $suffixes is an array
            if ( $suffixes && is_scalar($suffixes) ) {
                $suffixes = array( $suffixes );
			}
			if ( is_array($suffixes) )
			{
			    $this->suffix_filter = $suffixes;
			}
		}   // end function setSuffixFilter()
		
		/**
		 * set directory or file to read-only; used for index.php
		 *
		 * @access public
		 * @param  string $directory
		 * @return void
		 *
		 **/
        public function setReadOnly($item)
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
	    }   // function setReadOnly()
	    
        /**
         * This method creates index.php files in every subdirectory of a given path
         *
         * @access public
         * @param  string  directory to start with
         * @return void
         *
         **/
        public function recursiveCreateIndex( $dir )
        {
            if ( $handle = opendir($dir) )
            {
                if ( ! file_exists( $dir . '/index.php' ) )
                {
                    $fh = fopen( $dir.'/index.php', 'w' );
                    fwrite( $fh, '<' . '?' . 'php' . "\n" );
        	        fwrite( $fh, $this->_class_secure_code() );
        	        fclose( $fh );
                }

                while ( false !== ( $file = readdir($handle) ) )
                {
                    if ( $file != "." && $file != ".." )
                    {
                        if( is_dir( $dir.'/'.$file ) )
                        {
                            $this->recursiveCreateIndex( $dir.'/'.$file );
                        }
                    }
                }
                closedir($handle);
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
		 *  THIS METHOD WAS MOVED TO LEPTON_Helper_Addons!
		 *
		 * @internal ralf 2011-08-05 - added recursive parameter for mkdir()
		 * @todo ralf 2011-08-05     - checking for !is_dir() is not a good idea, perhaps $dirname
		 * is not a valid path, i.e. a file - any better ideas?
		  */
		function createDirectory( $dir_name, $dir_mode = OCTAL_DIR_MODE, $createIndex = false )
		{
		     if ( $dir_name != '' && !is_dir($dir_name) )
		     {
		         $umask = umask(0);
		         mkdir($dir_name, $dir_mode, true);
		         umask($umask);
		         if ( $createIndex )
		         {
			         $this->recursiveCreateIndex( $dir_name );
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
	    public function removeDirectory($directory)
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
	                    $this->removeDirectory($directory . '/' . $entry);
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
	     * check if directory is world-writable
	     * hopefully more secure than is_writable()
	     *
	     * @access public
	     * @param  string  $directory
	     * @return boolean
	     *
	     **/
		public function is_world_writable($directory)
		{
		    if ( ! is_dir( $directory ) )
		    {
		        return false;
			}
		    return ( substr(sprintf('%o', fileperms($directory)), -1) == 7 ? true : false );
		}   // end function is_world_writable()
		
		/**
		 *
		 *
		 *
		 *
		 **/
		private function _class_secure_code()
		{
			return "
// include class.secure.php to protect this file and the whole CMS!
if (defined('LEPTON_PATH')) {
	include(LEPTON_PATH.'/framework/class.secure.php');
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