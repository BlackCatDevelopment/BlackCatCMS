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

if ( ! class_exists( 'CAT_Helper_Image', false ) ) {
	class CAT_Helper_Image extends CAT_Object
	{
        private static $instance = array();

		public function __construct() {
	        if ( ! class_exists( 'Image', false ) ) {
	            include dirname(__FILE__).'/../../../modules/lib_images/inc/class.Images.php';
			}
			if ( ! defined('CAT_PATH') ) {
			    include dirname(__FILE__).'/../../../config.php';
			}
	    }

        public function __call($method, $args)
        {
            if ( ! isset($this) || ! is_object($this) )
                return false;
            if ( method_exists( $this, $method ) )
                return call_user_func_array(array($this, $method), $args);
        }

        /**
         * get instance; forwards to login page if the user is not logged in
         *
         * @access public
         * @return object
         **/
        public static function getInstance()
        {
            if (!self::$instance)
            {
                self::$instance = new self();
            }
            return self::$instance;
        }   // end function getInstance()

        /**
         * check if GD is available
         *
         * @access public
         * @return boolean
         **/
        public static function check_gd() {
            $gd_support = extension_loaded('gd');
            if ($gd_support) return true;
            return false;
        }   // end function check_gd()

        /**
         * checks for imageftbbox GD function
         *
         * @access public
         * @return boolean
         **/
        public static function check_imageftbbox() {
            return function_exists('imageftbbox');
        }   // end function check_imageftbbox()
        
        /**
         * checks for TTF support in GD
         *
         * @access public
         * @return boolean
         **/
        public static function check_ttf() {
            if(!self::check_gd()) return false;
            $gd_info = gd_info();
            return $gd_info['FreeType Support'];
        }   // end function check_ttf()

        /**
         *
         * @access public
         * @return
         **/
        public static function check_jpg() {
            if(!self::check_gd()) return false;
            $gd_info = gd_info();
            return
                  (isset($gd_info['JPG Support']) || isset($gd_info['JPEG Support']))
                ? true
                : false;
        }   // end function check_jpg()
        
        /**
         * checks for PNG support in GD
         *
         * @access public
         * @return boolean
         **/
        public static function check_png() {
            if(!self::check_gd()) return false;
            $gd_info = gd_info();
            return $gd_info['PNG Support'];
        }   // end function check_png()

        /**
         * checks for GIF (create) support in GD
         *
         * @access public
         * @return boolean
         **/
        public static function check_gif() {
            if(!self::check_gd()) return false;
            $gd_info = gd_info();
            return $gd_info['GIF Create Support'];
        }   // end function check_gif()
        
	    
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
		 * @param string $method
		 *				 fit = Fits image into width and height while keeping original aspect ratio. Expect your image not to use the full area.
		 *				 crop = Crops image to fill the area while keeping original aspect ratio. Expect your image to get, well, cropped.
		 *               fill = Fits image into the area without taking care of any ratios. Expect your image to get deformed.
	     *
	     **/
	    public function make_thumb( $source, $destination, $height, $width = NULL, $method = 'fit', $extension = NULL )
		{
		
	        $dest_path = pathinfo( $destination, PATHINFO_DIRNAME );
			$dest_file = pathinfo( $destination, PATHINFO_FILENAME );
			$image     = new Image($source);
			
			$image->setPathToTempFiles( CAT_PATH.'/temp' );

			$image->setNewFileType($extension);

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
			
			$image->resize( $width, $height, $method );
			return $image->save( $dest_file, $dest_path, $extension );
	    }   // end function make_thumb()
	    
	    /**
	     * wrapper to rotate() method of class.Images.php
	     **/
		public function rotate( $source, $destination, $degrees = 90 )
		{
		    $dest_path = pathinfo( $destination, PATHINFO_DIRNAME );
			$dest_file = pathinfo( $destination, PATHINFO_FILENAME );
			$image     = new Image($source);

			$image->setPathToTempFiles( CAT_PATH.'/temp' );
	        $image->rotate( 90, 100 );
	        return $image->save( $dest_file, $dest_path );
		}   // end function rotate()
		
		public function watermark( $source, $destination, $watermark = NULL, $opacity = 50 )
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
			$image->writeWatermark($opacity);
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

	    
	}   // class CAT_Helper_Image
}