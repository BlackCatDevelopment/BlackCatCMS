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
 * @copyright       2012, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 *
 */

if ( ! class_exists( 'LEPTON_Object', false ) ) {
    @include dirname(__FILE__).'/../Object.php';
}

if ( ! class_exists( 'LEPTON_Helper_Image', false ) ) {
	class LEPTON_Helper_Image extends LEPTON_Object
	{

		public function __construct() {
	        if ( ! class_exists( 'Image', false ) ) {
	            include dirname(__FILE__).'/../../../modules/lib_images/inc/class.Images.php';
			}
			if ( ! defined('LEPTON_PATH') ) {
			    include dirname(__FILE__).'/../../../config.php';
			}
	    }
	    
	    /**
	     * creates a thumbnail by resizing the original image and storing a copy
	     * of it
	     *
	     * if no width is given, $height will be treated as the required image
	     * size, and the values vor $width and $height are calculated by
	     * analyzing the original image
	     *
	     * @access public
	     * @param  string  $source      - original image (path/filename)
	     * @param  string  $destination - path and filename for the thumbnail
	     * @param  integer $height      - thumbnail height
	     * @param  integer $width       - (optional) thumbnail width
	     *
	     **/
	    public function make_thumb( $source, $destination, $height, $width = NULL )
		{
		
	        $dest_path = pathinfo( $destination, PATHINFO_DIRNAME );
			$dest_file = pathinfo( $destination, PATHINFO_FILENAME );
			$image     = new Image($source);
			
			$image->setPathToTempFiles( LEPTON_PATH.'/temp' );
			
			// if no width is given...
			if ( $width == '' )
			{
				$width = $height;  // default
                $h_t_w = $image->getRatioHeightToWidth();
                if ( $h_t_w > 1 ) // higher than wide
                {
					$width  = intval($image->getWidth() * $h_t_w);
				}
				else              // wider than high
				{
				    $height = intval($image->getHeight() * $h_t_w);
				}
			}
			
			$image->resize( $width, $height );
			return $image->save( $dest_file, $dest_path );
	    }   // end function make_thumb()
	    
	    /**
	     * wrapper to rotate() method of class.Images.php
	     **/
		public function rotate( $source, $destination, $degrees = 90 )
		{
		    $dest_path = pathinfo( $destination, PATHINFO_DIRNAME );
			$dest_file = pathinfo( $destination, PATHINFO_FILENAME );
			$image     = new Image($source);

			$image->setPathToTempFiles( LEPTON_PATH.'/temp' );
	        $image->rotate( 90, 100 );
	        return $image->save( $dest_file, $dest_path );
		}   // end function rotate()
		
		public function watermark( $source, $destination, $watermark = NULL )
	    {
	        $dest_path = pathinfo( $destination, PATHINFO_DIRNAME );
			$dest_file = pathinfo( $destination, PATHINFO_FILENAME );
			$image     = new Image($source);
			
			// default watermark
			if ( $watermark == '' )
			{
			    if ( file_exists( dirname(__FILE__).'/../../../media/watermark.png' ) )
			    {
			        $watermark = dirname(__FILE__).'/../../../media/watermark.png';
			    }
			    else
			    {
			        return false;
			    }
			}
			
		    // add the watermark file
			$w_mark = $image->addWatermark( $watermark );
			// resize the watermark to match image size
			// deactivated because of strange results when using transparent
			// images; this is a known issue; maybe we can reactivate this later
			// $w_mark->resize($image->getWidth(),$image->getHeight());
			// write it in the main image
			$image->writeWatermark();
			// save new image
			return $image->save( $dest_file, $dest_path );
	    }   // end function watermark()
	    
	    /***********************************************************************
							CONVENIENCE METHODS
	    ***********************************************************************/
	    
	    /**
	     * wrapper to getHeight()
	     **/
	    public function getHeight( $source )
	    {
	        $image = new Image($source);
	        return $image->getHeight();
	    }   // end function getHeight()
	    
	    /**
	     * wrapper to getWidth()
	     **/
	    public function getWidth( $source )
	    {
	        $image = new Image($source);
	        return $image->getWidth();
	    }   // end function getHeight()
	    
	    /**
	     * rotate image 45 degrees
	     **/
	    public function rotate45 ( $source, $destination )
	    {
	        return $this->rotate( $source, $destination, 45 );
	    }   // end function rotate45()
	    
	    /**
	     * rotate image 90 degrees
	     **/
	    public function rotate90 ( $source, $destination )
	    {
	        // 90 is default, so we don't need the param here
	        return $this->rotate( $source, $destination );
	    }   // end function rotate90()
	    
	    /**
	     * rotate image 180 degrees
	     **/
	    public function rotate180 ( $source, $destination )
	    {
	        return $this->rotate( $source, $destination, 180 );
	    }   // end function rotate180()

	    
	}   // class LEPTON_Helper_Image
}