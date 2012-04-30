<?php

/**
 *
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 *
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 * @version         $Id: I18n.php 1538 2011-12-27 13:37:59Z webbird $
 *
 */

if ( ! class_exists( 'LEPTON_Object', false ) ) {
    @include dirname(__FILE__).'/../Object.php';
}
if ( ! class_exists( 'LEPTON_Helper_Directory', false ) ) {
    @include dirname(__FILE__).'/Directory.php';
}

if ( ! class_exists( 'LEPTON_Helper_Zip', false ) )
{

	class LEPTON_Helper_Zip extends LEPTON_Object
	{
	
	    // holds the PclZip object
	    private   $zip;
	    // holds the Directory helper object
	    private   $dirh;
	    //
	    protected $_config = array(
	        // ----- PclZip create options: -----
	        // PCLZIP_OPT_ADD_PATH, "/abs/path/to"
			// ability to insert a path
			// do not use by default
			'addPath' => false,
	        // PCLZIP_OPT_REMOVE_PATH, "/usr/local/user"
	        // removes path parts from files
	        // by default, we remove WB_PATH
	        'removePath' => WB_PATH,
	        // PCLZIP_OPT_REMOVE_ALL_PATH
	        // removes complete path info from all files
	        // do not use by default
	        'removeAllPath' => false,
	        // PCLZIP_OPT_COMMENT, "Comment"
	        // set a comment in the PKZIP archive
	        // not used by default
	        'setComment' => false,
	        // ----- PclZip extract options: -----
	        // PCLZIP_OPT_PATH, "extract/folder/"
	        // we set this to our temp dir by default
	        //'Path' => WB_PATH.'/temp',
	        // other:
  			//   PCLZIP_OPT_ADD_PATH
  			//   PCLZIP_OPT_REMOVE_PATH
  			//   PCLZIP_OPT_REMOVE_ALL_PATH
  			// see above
	    );
	    
	    /**
	     * constructor; creates an internal PclZip object
	     **/
		public function __construct( $zipfile = NULL ) {
		    $this->dirh = new LEPTON_Helper_Directory();
			if ( ! class_exists( 'PclZip', false ) ) {
			    define( 'PCLZIP_TEMPORARY_DIR', $this->dirh->sanitizePath( WB_PATH.'/temp' ) );
				@include $this->dirh->sanitizePath( WB_PATH.'/modules/lib_lepton/pclzip/pclzip.lib.php' );
			}
			$this->config( 'Path', $this->dirh->sanitizePath( WB_PATH.'/temp' ) );
		    $this->zip = new PclZip($zipfile);
		    return $this->zip;
		}   // end function __construct()
		
		/**
		 * accessor to create() method; only argument is the file list (or a
		 * directory to archive)
		 * All PclZip options have to be set using $zip_helper->config()!
		 *
		 * @access public
		 * @param  mixed  $p_filelist
		 *                An array of filenames or dirnames,
		 *					or
		 *				  A string containing the a filename or a dirname,
		 *					or
		 *				  A string containing a list of filename or dirname
		 *				  separated by a comma.
		 *
		 **/
		public function create($p_filelist)
		{
		    // generate function call
			$options = array();
			$ret     = NULL;

			if ( isset($this->_config['addPath']) && $this->_config['addPath'] != '' )
			{
			    $options[] = 'PCLZIP_OPT_ADD_PATH, "'.$this->dirh->sanitizePath($this->_config['addPath']).'"';
			}
			if ( isset($this->_config['removePath']) && $this->_config['removePath'] != '' )
			{
			    $options[] = 'PCLZIP_OPT_REMOVE_PATH, "'.$this->dirh->sanitizePath($this->_config['removePath']).'"';
			}
			if ( isset($this->_config['setComment']) && $this->_config['setComment'] != '' )
			{
			    $options[] = 'PCLZIP_OPT_COMMENT, "'.$this->_config['setComment'] . '"';
			}
			if ( isset($this->_config['removeAllPath']) && $this->_config['removeAllPath'] != '' )
			{
			    $options[] = 'PCLZIP_OPT_REMOVE_ALL_PATH';
			}
			
			if ( is_scalar($p_filelist) )
			{
			    $p_filelist = $this->dirh->sanitizePath($p_filelist);
			}
			
			$code = '$ret = $this->zip->create( $p_filelist'
			   . (
			   		( is_array($options) && count($options) )
				  ? ', ' . implode( ', ', $options )
				  : ''
				 )
			   . ' );';

			eval ( $code );
			return $ret;
			
		}   // end function create()
		
		/**
		 * accessor to extract() method
		 * All PclZip options have to be set using $zip_helper->config()!
		 *
		 * @access public
		 * 
		 **/
		public function extract()
		{
		
		    // generate function call
			$options = array( 'PCLZIP_OPT_PATH, "'.$this->dirh->sanitizePath($this->_config['Path']).'"' );
			$ret     = NULL;

			if ( isset($this->_config['addPath']) && $this->_config['addPath'] != '' )
			{
			    $options[] = 'PCLZIP_OPT_ADD_PATH, "'.$this->dirh->sanitizePath($this->_config['addPath']).'"';
			}
			if ( isset($this->_config['removePath']) && $this->_config['removePath'] != '' )
			{
			    $options[] = 'PCLZIP_OPT_REMOVE_PATH, "'.$this->dirh->sanitizePath($this->_config['removePath']).'"';
			}
			if ( isset($this->_config['removeAllPath']) && $this->_config['removeAllPath'] != '' )
			{
			    $options[] = 'PCLZIP_OPT_REMOVE_ALL_PATH';
			}
			
			$code = '$ret = $this->zip->extract( '
			   . (
			   		( is_array($options) && count($options) )
				  ? implode( ', ', $options )
				  : ''
				 )
			   . ' );';

			eval ( $code );
			return $ret;
			
		}   // end function extract()
		
		/**
		 * accessor to PclZip->listContent()
		 **/
		public function listContent()
  		{
  		    return $this->zip->listContent();
  		}   // end function listContent()
  		
  		/**
		 * accessor to PclZip->errorInfo()
		 **/
  		public function errorInfo()
  		{
  		    return $this->zip->errorInfo();
  		}

	}   // end class

}   // class_exists()