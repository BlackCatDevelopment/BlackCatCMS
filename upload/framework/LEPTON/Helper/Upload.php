<?php

/**
 *
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 *
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * Based on class.upload.php (Version 0.31) of Colin Verot <colin@verot.net>
 *
 * @author			LEPTON Project
 * @copyright		2010-2011, LEPTON Project
 * @link			http://www.lepton2.org
 * @license			http://www.gnu.org/licenses/gpl.html
 * @license_terms	please see LICENSE and COPYING files in your package
 * @version			$Id$
 *
 */

if ( ! class_exists( 'LEPTON_Helper_Upload' ) )
{
	if ( ! class_exists( 'LEPTON_Object', false ) )
	{
		@include dirname(__FILE__).'/../Object.php';
	}
	class LEPTON_Helper_Upload extends LEPTON_Object
	{
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
		* The function mime_content_type() will be deprecated,
		* and this variable will be set to false in a future release
		*
		* This variable is set to true by default for security reason
		*
		* @access public
		* @var boolean
		*/
		var $mime_magic;
	
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
			$this->file_new_name_body		= null;			// replace the name body
			$this->file_name_body_add		= null;			// append to the name body
			$this->file_name_body_pre		= null;			// prepend to the name body
			$this->file_new_name_ext		= null;			// replace the file extension
			$this->file_safe_name			= true;			// format safely the filename
			$this->file_force_extension		= true;			// forces extension if there isn't one
			$this->file_overwrite			= false;		// allows overwritting if the file already exists
			$this->file_auto_rename			= true;			// auto-rename if the file already exists
			$this->dir_auto_create			= true;			// auto-creates directory if missing
			$this->dir_auto_chmod			= true;			// auto-chmod directory if not writeable
			$this->dir_chmod				= 0777;			// default chmod to use

			$this->no_script				= true;			// turns scripts into test files
			$this->mime_check				= true;			// checks the mime type against the allowed list

			// these are the different MIME detection methods. if one of these method doesn't work on your
			// system, you can deactivate it here; just set it to false
			$this->mime_fileinfo			= true;			// MIME detection with Fileinfo PECL extension
			$this->mime_file 				= true;			// MIME detection with UNIX file() command
			$this->mime_magic				= true;			// MIME detection with mime_magic (mime_content_type())

			// get the default max size from php.ini
			$this->file_max_size_raw		= trim(ini_get('upload_max_filesize'));
			$this->file_max_size			= $this->getsize($this->file_max_size_raw);
	
			$this->forbidden				= array();
			$this->allowed					= array(
				'application/arj',
				'application/excel',
				'application/gnutar',
				'application/mspowerpoint',
				'application/msword',
				'application/octet-stream',
				'application/onenote',
				'application/pdf',
				'application/plain',
				'application/postscript',
				'application/powerpoint',
				'application/rar',
				'application/rtf',
				'application/vnd.ms-excel',
				'application/vnd.ms-excel.addin.macroEnabled.12',
				'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
				'application/vnd.ms-excel.sheet.macroEnabled.12',
				'application/vnd.ms-excel.template.macroEnabled.12',
				'application/vnd.ms-office',
				'application/vnd.ms-officetheme',
				'application/vnd.ms-powerpoint',
				'application/vnd.ms-powerpoint.addin.macroEnabled.12',
				'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
				'application/vnd.ms-powerpoint.slide.macroEnabled.12',
				'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
				'application/vnd.ms-powerpoint.template.macroEnabled.12',
				'application/vnd.ms-word',
				'application/vnd.ms-word.document.macroEnabled.12',
				'application/vnd.ms-word.template.macroEnabled.12',
				'application/vnd.oasis.opendocument.chart',
				'application/vnd.oasis.opendocument.database',
				'application/vnd.oasis.opendocument.formula',
				'application/vnd.oasis.opendocument.graphics',
				'application/vnd.oasis.opendocument.graphics-template',
				'application/vnd.oasis.opendocument.image',
				'application/vnd.oasis.opendocument.presentation',
				'application/vnd.oasis.opendocument.presentation-template',
				'application/vnd.oasis.opendocument.spreadsheet',
				'application/vnd.oasis.opendocument.spreadsheet-template',
				'application/vnd.oasis.opendocument.text',
				'application/vnd.oasis.opendocument.text-master',
				'application/vnd.oasis.opendocument.text-template',
				'application/vnd.oasis.opendocument.text-web',
				'application/vnd.openofficeorg.extension',
				'application/vnd.openxmlformats-officedocument.presentationml.presentation',
				'application/vnd.openxmlformats-officedocument.presentationml.slide',
				'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
				'application/vnd.openxmlformats-officedocument.presentationml.template',
				'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
				'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
				'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
				'application/vocaltec-media-file',
				'application/wordperfect',
				'application/x-bittorrent',
				'application/x-bzip',
				'application/x-bzip2',
				'application/x-compressed',
				'application/x-excel',
				'application/x-gzip',
				'application/x-latex',
				'application/x-midi',
				'application/xml',
				'application/x-msexcel',
				'application/x-rar',
				'application/x-rar-compressed',
				'application/x-rtf',
				'application/x-shockwave-flash',
				'application/x-sit',
				'application/x-stuffit',
				'application/x-troff-msvideo',
				'application/x-zip',
				'application/x-zip-compressed',
				'application/zip',
				'audio/*',
				'image/*',
				'multipart/x-gzip',
				'multipart/x-zip',
				'text/plain',
				'text/rtf',
				'text/richtext',
				'text/xml',
				'video/*'
			);
	
			$this->mime_types				= array(
				'jpg' => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'jpe' => 'image/jpeg',
				'gif' => 'image/gif',
				'png' => 'image/png',
				'bmp' => 'image/bmp',
				'flv' => 'video/x-flv',
				'js' => 'application/x-javascript',
				'json' => 'application/json',
				'tiff' => 'image/tiff',
				'css' => 'text/css',
				'xml' => 'application/xml',
				'doc' => 'application/msword',
				'docx' => 'application/msword',
				'xls' => 'application/vnd.ms-excel',
				'xlt' => 'application/vnd.ms-excel',
				'xlm' => 'application/vnd.ms-excel',
				'xld' => 'application/vnd.ms-excel',
				'xla' => 'application/vnd.ms-excel',
				'xlc' => 'application/vnd.ms-excel',
				'xlw' => 'application/vnd.ms-excel',
				'xll' => 'application/vnd.ms-excel',
				'ppt' => 'application/vnd.ms-powerpoint',
				'pps' => 'application/vnd.ms-powerpoint',
				'rtf' => 'application/rtf',
				'pdf' => 'application/pdf',
				'html' => 'text/html',
				'htm' => 'text/html',
				'php' => 'text/html',
				'txt' => 'text/plain',
				'mpeg' => 'video/mpeg',
				'mpg' => 'video/mpeg',
				'mpe' => 'video/mpeg',
				'mp3' => 'audio/mpeg3',
				'wav' => 'audio/wav',
				'aiff' => 'audio/aiff',
				'aif' => 'audio/aiff',
				'avi' => 'video/msvideo',
				'wmv' => 'video/x-ms-wmv',
				'mov' => 'video/quicktime',
				'zip' => 'application/zip',
				'tar' => 'application/x-tar',
				'swf' => 'application/x-shockwave-flash',
				'odt' => 'application/vnd.oasis.opendocument.text',
				'ott' => 'application/vnd.oasis.opendocument.text-template',
				'oth' => 'application/vnd.oasis.opendocument.text-web',
				'odm' => 'application/vnd.oasis.opendocument.text-master',
				'odg' => 'application/vnd.oasis.opendocument.graphics',
				'otg' => 'application/vnd.oasis.opendocument.graphics-template',
				'odp' => 'application/vnd.oasis.opendocument.presentation',
				'otp' => 'application/vnd.oasis.opendocument.presentation-template',
				'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
				'ots' => 'application/vnd.oasis.opendocument.spreadsheet-template',
				'odc' => 'application/vnd.oasis.opendocument.chart',
				'odf' => 'application/vnd.oasis.opendocument.formula',
				'odb' => 'application/vnd.oasis.opendocument.database',
				'odi' => 'application/vnd.oasis.opendocument.image',
				'oxt' => 'application/vnd.openofficeorg.extension',
				'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
				'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
				'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',
				'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
				'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
				'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
				'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',
				'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
				'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
				'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
				'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
				'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
				'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
				'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
				'potm' => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
				'ppam' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
				'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
				'sldm' => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
				'thmx' => 'application/vnd.ms-officetheme',
				'onetoc' => 'application/onenote',
				'onetoc2' => 'application/onenote',
				'onetmp' => 'application/onenote',
				'onepkg' => 'application/onenote',
			);
		}
	
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
		function LEPTON_Helper_Upload( $file )
		{
			$this->file_src_name		= '';
			$this->file_src_name_body	= '';
			$this->file_src_name_ext	= '';
			$this->file_src_mime		= '';
			$this->file_src_size		= '';
			$this->file_src_error		= '';
			$this->file_src_pathname	= '';
			$this->file_src_temp		= '';
			$this->file_dst_path		= '';
			$this->file_dst_name		= '';
			$this->file_dst_name_body	= '';
			$this->file_dst_name_ext	= '';
			$this->file_dst_pathname	= '';
			$this->uploaded				= true;
			$this->no_upload_check		= false;
			$this->processed			= true;
			$this->error				= '';
			$this->log					= '';
			$this->allowed				= array();
			$this->forbidden			= array();
			$this->init();
			$info						= null;
			$mime_from_browser			= null;
			// determines the supported MIME types, and matching image format
			$this->image_supported		= array();
			if ($this->gdversion())
			{
				if (imagetypes() & IMG_GIF) {
					$this->image_supported['image/gif'] = 'gif';
				}
				if (imagetypes() & IMG_JPG) {
					$this->image_supported['image/jpg'] = 'jpg';
					$this->image_supported['image/jpeg'] = 'jpg';
					$this->image_supported['image/pjpeg'] = 'jpg';
				}
				if (imagetypes() & IMG_PNG) {
					$this->image_supported['image/png'] = 'png';
					$this->image_supported['image/x-png'] = 'png';
				}
				if (imagetypes() & IMG_WBMP) {
					$this->image_supported['image/bmp'] = 'bmp';
					$this->image_supported['image/x-ms-bmp'] = 'bmp';
					$this->image_supported['image/x-windows-bmp'] = 'bmp';
				}
			}
			// display some system information
			if (empty($this->log))
			{
				$this->log .= '<b>system information</b><br />';
				if (function_exists('ini_get_all'))
				{
					$inis			= ini_get_all();
					$open_basedir	= (	array_key_exists('open_basedir', $inis) 
										&& array_key_exists('local_value', $inis['open_basedir'])
										&& !empty($inis['open_basedir']['local_value']) )
											? $inis['open_basedir']['local_value'] : false;
				}
				else
				{
					$open_basedir = false;
				}
				$gd				= $this->gdversion() ? $this->gdversion(true) : 'GD not present';
				$supported		= trim((in_array('png', $this->image_supported) ? 'png' : '') . ' ' . (in_array('jpg', $this->image_supported) ? 'jpg' : '') . ' ' . (in_array('gif', $this->image_supported) ? 'gif' : '') . ' ' . (in_array('bmp', $this->image_supported) ? 'bmp' : ''));
				$this->log		.= '-&nbsp;operating system   	: ' . PHP_OS . '<br />';
				$this->log		.= '-&nbsp;PHP version   		: ' . PHP_VERSION . '<br />';
				$this->log		.= '-&nbsp;GD version    		: ' . $gd . '<br />';
				$this->log		.= '-&nbsp;supported image types   : ' . (!empty($supported) ? $supported : 'none') . '<br />';
				$this->log		.= '-&nbsp;open_basedir  		: ' . (!empty($open_basedir) ? $open_basedir : 'no restriction') . '<br />';
				$this->log		.= '-&nbsp;upload_max_filesize	: ' . $this->file_max_size_raw . ' (' . $this->file_max_size . ' bytes)<br />';
			}
			if (!$file)
			{
				$this->uploaded = false;
				$this->error = 'File error. Please try again.';
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
					$this->log .= '<b>Source is a local file ' . $file . '</b><br />';

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
						$this->file_src_pathname   = $file;
						$this->file_src_name  	= basename($file);
						$this->log .= '- local file name OK<br />';
						preg_match('/\.([^\.]*$)/', $this->file_src_name, $extension);
						if (is_array($extension) && sizeof($extension) > 0)
						{
							$this->file_src_name_ext 	= strtolower($extension[1]);
							$this->file_src_name_body	= substr($this->file_src_name, 0, ((strlen($this->file_src_name) - strlen($this->file_src_name_ext)))-1);
						}
						else
						{
							$this->file_src_name_ext 	= '';
							$this->file_src_name_body	= $this->file_src_name;
						}
						$this->file_src_size = (file_exists($file) ? filesize($file) : 0);
					}
					$this->file_src_error = 0;
				}
			}
			else
			{
				// this is an element from $_FILE, i.e. an uploaded file
				$this->log .= '<b>source is an uploaded file</b><br />';
				if ($this->uploaded)
				{
					$this->file_src_error    	= trim($file['error']);
					switch($this->file_src_error)
					{
						case UPLOAD_ERR_OK:
							// all is OK
							$this->log .= '- upload OK<br />';
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
					$this->file_src_pathname   = $file['tmp_name'];
					$this->file_src_name  	= $file['name'];
					if ($this->file_src_name == '')
					{
						$this->uploaded = false;
						$this->error = 'File upload error. Please try again.';
					}
				}
	
				if ($this->uploaded)
				{
					$this->log .= '- file name OK<br />';
					preg_match('/\.([^\.]*$)/', $this->file_src_name, $extension);
					if (is_array($extension) && sizeof($extension) > 0)
					{
						$this->file_src_name_ext 	= strtolower($extension[1]);
						$this->file_src_name_body	= substr($this->file_src_name, 0, ((strlen($this->file_src_name) - strlen($this->file_src_name_ext)))-1);
					}
					else
					{
						$this->file_src_name_ext 	= '';
						$this->file_src_name_body	= $this->file_src_name;
					}
					$this->file_src_size = $file['size'];
					$mime_from_browser = $file['type'];
				}
			}
	
			if ($this->uploaded)
			{
				$this->log .= '<b>determining MIME type</b><br />';
				$this->file_src_mime = null;
	
				// checks MIME type with Fileinfo PECL extension
				if (!$this->file_src_mime || !is_string($this->file_src_mime) || empty($this->file_src_mime) || strpos($this->file_src_mime, '/') === FALSE)
				{
					if ($this->mime_fileinfo)
					{
						$this->log .= '- Checking MIME type with Fileinfo PECL extension<br />';
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
										$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;MAGIC path defaults to ' . $path . '<br />';
									}
									else
									{
										$path = getenv('MAGIC');
										$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;MAGIC path is set to ' . $path . ' from MAGIC variable<br />';
									}
								}
								else
								{
									$path = $this->mime_fileinfo;
									$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;MAGIC path is set to ' . $path . '<br />';
								}
								$f = @finfo_open(FILEINFO_MIME, $path);
							}
							else
							{
								$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;MAGIC path will not be used<br />';
								$f = @finfo_open(FILEINFO_MIME);
							}
							if (is_resource($f))
							{
								$mime = finfo_file($f, realpath($this->file_src_pathname));
								finfo_close($f);
								$this->file_src_mime = $mime;
								$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;MIME type detected as ' . $this->file_src_mime . ' by Fileinfo PECL extension<br />';
								if (preg_match("/^([\.-\w]+)\/([\.-\w]+)(.*)$/i", $this->file_src_mime))
								{
									$this->file_src_mime = preg_replace("/^([\.-\w]+)\/([\.-\w]+)(.*)$/i", '$1/$2', $this->file_src_mime);
									$this->log .= '-&nbsp;MIME validated as ' . $this->file_src_mime . '<br />';
								}
								else
								{
									$this->file_src_mime = null;
								}
							}
							else
							{
								$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;Fileinfo PECL extension failed (finfo_open)<br />';
							}
						}
						elseif (@class_exists('finfo'))
						{
							$f = new finfo( FILEINFO_MIME );
							if ($f)
							{
								$this->file_src_mime = $f->file(realpath($this->file_src_pathname));
								$this->log .= '- MIME type detected as ' . $this->file_src_mime . ' by Fileinfo PECL extension<br />';
								if (preg_match("/^([\.-\w]+)\/([\.-\w]+)(.*)$/i", $this->file_src_mime))
								{
									$this->file_src_mime = preg_replace("/^([\.-\w]+)\/([\.-\w]+)(.*)$/i", '$1/$2', $this->file_src_mime);
									$this->log .= '-&nbsp;MIME validated as ' . $this->file_src_mime . '<br />';
								}
								else
								{
									$this->file_src_mime = null;
								}
							}
							else
							{
								$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;Fileinfo PECL extension failed (finfo)<br />';
							}
						}
						else
						{
							$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;Fileinfo PECL extension not available<br />';
						}
					}
					else
					{
						$this->log .= '- Fileinfo PECL extension deactivated<br />';
					}
				}
	
				// checks MIME type with shell if unix access is authorized
				if (!$this->file_src_mime || !is_string($this->file_src_mime) || empty($this->file_src_mime) || strpos($this->file_src_mime, '/') === FALSE)
				{
					if ($this->mime_file)
					{
						$this->log .= '- Checking MIME type with UNIX file() command<br />';
						if (substr(PHP_OS, 0, 3) != 'WIN')
						{
							if (function_exists('exec'))
							{
								if (strlen($mime = @exec("file -bi ".escapeshellarg($this->file_src_pathname))) != 0)
								{
									$this->file_src_mime = trim($mime);
									$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;MIME type detected as ' . $this->file_src_mime . ' by UNIX file() command<br />';
									if (preg_match("/^([\.-\w]+)\/([\.-\w]+)(.*)$/i", $this->file_src_mime))
									{
										$this->file_src_mime = preg_replace("/^([\.-\w]+)\/([\.-\w]+)(.*)$/i", '$1/$2', $this->file_src_mime);
										$this->log .= '-&nbsp;MIME validated as ' . $this->file_src_mime . '<br />';
									}
									else
									{
										$this->file_src_mime = null;
									}
								}
								else
								{
									$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;UNIX file() command failed<br />';
								}
							}
							else
							{
								$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;PHP exec() function is disabled<br />';
							}
						}
						else
						{
							$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;UNIX file() command not availabled<br />';
						}
					}
					else
					{
						$this->log .= '- UNIX file() command is deactivated<br />';
					}
				}

				// checks MIME type with mime_magic
				if (!$this->file_src_mime || !is_string($this->file_src_mime) || empty($this->file_src_mime) || strpos($this->file_src_mime, '/') === FALSE)
				{
					if ($this->mime_magic)
					{
						$this->log .= '- Checking MIME type with mime.magic file (mime_content_type())<br />';
						if (function_exists('mime_content_type'))
						{
							$this->file_src_mime = mime_content_type($this->file_src_pathname);
							$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;MIME type detected as ' . $this->file_src_mime . ' by mime_content_type()<br />';
							if (preg_match("/^([\.-\w]+)\/([\.-\w]+)(.*)$/i", $this->file_src_mime))
							{
								$this->file_src_mime = preg_replace("/^([\.-\w]+)\/([\.-\w]+)(.*)$/i", '$1/$2', $this->file_src_mime);
								$this->log .= '-&nbsp;MIME validated as ' . $this->file_src_mime . '<br />';
							}
							else
							{
								$this->file_src_mime = null;
							}
						}
						else
						{
							$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;mime_content_type() is not available<br />';
						}
					}
					else
					{
						$this->log .= '- mime.magic file (mime_content_type()) is deactivated<br />';
					}
				}

				// default to MIME from browser (or Flash)
				if (!empty($mime_from_browser) && !$this->file_src_mime || !is_string($this->file_src_mime) || empty($this->file_src_mime))
				{
					$this->file_src_mime =$mime_from_browser;
					$this->log .= '- MIME type detected as ' . $this->file_src_mime . ' by browser<br />';
					if (preg_match("/^([\.-\w]+)\/([\.-\w]+)(.*)$/i", $this->file_src_mime))
					{
						$this->file_src_mime = preg_replace("/^([\.-\w]+)\/([\.-\w]+)(.*)$/i", '$1/$2', $this->file_src_mime);
						$this->log .= '-&nbsp;MIME validated as ' . $this->file_src_mime . '<br />';
					}
					else
					{
						$this->file_src_mime = null;
					}
				}

				// we need to work some magic if we upload via Flash
				if ($this->file_src_mime == 'application/octet-stream' || !$this->file_src_mime || !is_string($this->file_src_mime) || empty($this->file_src_mime) || strpos($this->file_src_mime, '/') === FALSE)
				{
					if ($this->file_src_mime == 'application/octet-stream') $this->log .= '- Flash may be rewriting MIME as application/octet-stream<br />';
					$this->log .= '- Try to guess MIME type from file extension (' . $this->file_src_name_ext . '): ';
					if (array_key_exists($this->file_src_name_ext, $this->mime_types)) $this->file_src_mime = $this->mime_types[$this->file_src_name_ext];
					if ($this->file_src_mime == 'application/octet-stream')
					{
						$this->log .= 'doesn\'t look like anything known<br />';
					}
					else
					{
						$this->log .= 'MIME type set to ' . $this->file_src_mime . '<br />';
					}
				}

				if (!$this->file_src_mime || !is_string($this->file_src_mime) || empty($this->file_src_mime) || strpos($this->file_src_mime, '/') === FALSE)
				{
					$this->log .= '- MIME type couldn\'t be detected! (' . (string) $this->file_src_mime . ')<br />';
				}
				$this->log .= '<b>source variables</b><br />';
				$this->log .= '- You can use all these before calling process()<br />';
				$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_src_name    	: ' . $this->file_src_name . '<br />';
				$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_src_name_body    : ' . $this->file_src_name_body . '<br />';
				$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_src_name_ext	: ' . $this->file_src_name_ext . '<br />';
				$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_src_pathname	: ' . $this->file_src_pathname . '<br />';
				$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_src_mime    	: ' . $this->file_src_mime . '<br />';
				$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_src_size    	: ' . $this->file_src_size . ' (max= ' . $this->file_max_size . ')<br />';
				$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_src_error   	: ' . $this->file_src_error . '<br />';
			}
		}

		/**
		* Returns the version of GD
		*
		* @access public
		* @param  boolean  $full Optional flag to get precise version
		* @return float GD version
		*/
		function gdversion($full = false)
		{
			static $gd_version = null;
			static $gd_full_version = null;
			if ($gd_version === null)
			{
				if (function_exists('gd_info'))
				{
					$gd = gd_info();
					$gd = $gd["GD Version"];
					$regex = "/([\d\.]+)/i";
				}
				else
				{
					ob_start();
					phpinfo(8);
					$gd = ob_get_contents();
					ob_end_clean();
					$regex = "/\bgd\s+version\b[^\d\n\r]+?([\d\.]+)/i";
				}
				if (preg_match($regex, $gd, $m))
				{
					$gd_full_version = (string) $m[1];
					$gd_version = (float) $m[1];
				}
				else
				{
					$gd_full_version = 'none';
					$gd_version = 0;
				}
			}
			if ($full)
			{
				return $gd_full_version;
			}
			else
			{
				return $gd_version;
			}
		}

		/**
		* Creates directories recursively
		*
		* @access private
		* @param  string  $path Path to create
		* @param  integer $mode Optional permissions
		* @return boolean Success
		*/
		function rmkdir($path, $mode = 0777)
		{
			return is_dir($path) || ( $this->rmkdir(dirname($path), $mode) && $this->_mkdir($path, $mode) );
		}

		/**
		* Creates directory
		*
		* @access private
		* @param  string  $path Path to create
		* @param  integer $mode Optional permissions
		* @return boolean Success
		*/
		function _mkdir($path, $mode = 0777)
		{
			$old = umask(0);
			$res = @mkdir($path, $mode);
			umask($old);
			return $res;
		}

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
			$this->error				= '';
			$this->processed			= true;
			$return_mode				= false;
			$return_content				= null;
			// clean up dst variables
			$this->file_dst_path		= '';
			$this->file_dst_pathname	= '';
			$this->file_dst_name		= '';
			$this->file_dst_name_body	= '';
			$this->file_dst_name_ext	= '';
			// clean up some parameters
			$this->file_max_size		= $this->getsize($this->file_max_size);
			// copy some variables as we need to keep them clean
			$file_src_name				= $this->file_src_name;
			$file_src_name_body			= $this->file_src_name_body;
			$file_src_name_ext			= $this->file_src_name_ext;
			if (!$this->uploaded)
			{
				$this->error			= 'File not uploaded. Can\'t carry on a process.';
				$this->processed		= false;
			}
			if ($this->processed)
			{
				if (empty($server_path) || is_null($server_path))
				{
					$this->log .= '<b>process file and return the content</b><br />';
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
					$this->log .= '<b>process file to '  . $server_path . '</b><br />';
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
					$this->log .= '- file size OK<br />';
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
							$this->log .= '- file renamed as ' . $file_src_name_body . '.' . $file_src_name_ext . '!<br />';
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
						$this->log .= '- script renamed as ' . $file_src_name_body . '.' . $file_src_name_ext . '!<br />';
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
					$allowed = false;
					// check wether the mime type is allowed
					if (!is_array($this->allowed)) $this->allowed = array($this->allowed);
					foreach($this->allowed as $k => $v)
					{
						list($v1, $v2) = explode('/', $v);
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
						if (($v1 == '*' && $v2 == '*') || ($v1 == $m1 && ($v2 == $m2 || $v2 == '*')))
						{
							$allowed = false;
							break;
						}
					}
					if (!$allowed)
					{
						$this->processed = false;
						$this->error = 'Incorrect type of file.';
					}
					else
					{
						$this->log .= '- file mime OK : ' . $this->file_src_mime . '<br />';
					}
				}
				else
				{
					$this->log .= '- file mime (not checked) : ' . $this->file_src_mime . '<br />';
				}
			}
			if ($this->processed)
			{
				$this->file_dst_path			= $server_path;
				// repopulate dst variables from src
				$this->file_dst_name			= $file_src_name;
				$this->file_dst_name_body		= $file_src_name_body;
				$this->file_dst_name_ext		= $file_src_name_ext;
				if ($this->file_overwrite) $this->file_auto_rename = false;
				if (!is_null($this->file_new_name_body))
				{ // rename file body
					$this->file_dst_name_body = $this->file_new_name_body;
					$this->log .= '- new file name body : ' . $this->file_new_name_body . '<br />';
				}
				if (!is_null($this->file_new_name_ext))
				{ // rename file ext
					$this->file_dst_name_ext  = $this->file_new_name_ext;
					$this->log .= '- new file name ext : ' . $this->file_new_name_ext . '<br />';
				}
				if (!is_null($this->file_name_body_add))
				{ // append a string to the name
					$this->file_dst_name_body  = $this->file_dst_name_body . $this->file_name_body_add;
					$this->log .= '- file name body append : ' . $this->file_name_body_add . '<br />';
				}
				if (!is_null($this->file_name_body_pre))
				{ // prepend a string to the name
					$this->file_dst_name_body  = $this->file_name_body_pre . $this->file_dst_name_body;
					$this->log .= '- file name body prepend : ' . $this->file_name_body_pre . '<br />';
				}
				if ($this->file_safe_name)
				{ // formats the name
					$this->file_dst_name_body = strtr($this->file_dst_name_body, 'ŠŽšžŸÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖØÙÚÛÜÝàáâãäåçèéêëìíîïñòóôõöøùúûüýÿ', 'SZszYAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy');
					$this->file_dst_name_body = strtr($this->file_dst_name_body, array('Þ' => 'TH', 'þ' => 'th', 'Ð' => 'DH', 'ð' => 'dh', 'ß' => 'ss', 'Œ' => 'OE', 'œ' => 'oe', 'Æ' => 'AE', 'æ' => 'ae', 'µ' => 'u'));
					$this->file_dst_name_body = preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $this->file_dst_name_body);
					$this->log .= '- file name safe format<br />';
				}
				$this->log .= '- destination variables<br />';
				if (empty($this->file_dst_path) || is_null($this->file_dst_path))
				{
					$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_dst_path    	: n/a<br />';
				}
				else
				{
					$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_dst_path    	: ' . $this->file_dst_path . '<br />';
				}
				$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_dst_name_body    : ' . $this->file_dst_name_body . '<br />';
				$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_dst_name_ext	: ' . $this->file_dst_name_ext . '<br />';
				// set the destination file name
				$this->file_dst_name = $this->file_dst_name_body . (!empty($this->file_dst_name_ext) ? '.' . $this->file_dst_name_ext : '');
				if (!$return_mode)
				{
					if (!$this->file_auto_rename)
					{
						$this->log .= '- no auto_rename if same filename exists<br />';
						$this->file_dst_pathname = $this->file_dst_path . $this->file_dst_name;
					}
					else
					{
						$this->log .= '- checking for auto_rename<br />';
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
					$this->log .= '- destination file details<br />';
					$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_dst_name    	: ' . $this->file_dst_name . '<br />';
					$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_dst_pathname	: ' . $this->file_dst_pathname . '<br />';
					if ($this->file_overwrite)
					{
						$this->log .= '- no overwrite checking<br />';
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
							$this->log .= '- ' . $this->file_dst_name . ' doesn\'t exist already<br />';
						}
					}
				}
			}
			if ($this->processed)
			{
				// if we have already moved the uploaded file, we use the temporary copy as source file, and check if it exists
				if (!empty($this->file_src_temp))
				{
					$this->log .= '- use the temp file instead of the original file since it is a second process<br />';
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
							$this->log .= '- ' . $this->file_dst_path . ' doesn\'t exist. Attempting creation:';
							if (!$this->rmkdir($this->file_dst_path, $this->dir_chmod))
							{
								$this->log .= ' failed<br />';
								$this->processed = false;
								$this->error = 'Destination directory can\'t be created. Can\'t carry on a process.';
							}
							else
							{
								$this->log .= ' success<br />';
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
							$this->log .= '- ' . $this->file_dst_path . ' is not writeable. Attempting chmod:';
							if (!@chmod($this->file_dst_path, $this->dir_chmod))
							{
								$this->log .= ' failed<br />';
								$this->processed = false;
								$this->error = 'Destination directory can\'t be made writeable. Can\'t carry on a process.';
							}
							else
							{
								$this->log .= ' success<br />';
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
						$this->log .= '- attempting to use a temp file:';
						$hash = md5($this->file_dst_name_body . rand(1, 1000));
						if (move_uploaded_file($this->file_src_pathname, $this->file_dst_path . $hash . (!empty($this->file_dst_name_ext) ? '.' . $this->file_dst_name_ext : '')))
						{
							$this->file_src_pathname = $this->file_dst_path . $hash . (!empty($this->file_dst_name_ext) ? '.' . $this->file_dst_name_ext : '');
							$this->file_src_temp = $this->file_src_pathname;
							$this->log .= ' file created<br />';
							$this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;temp file is: ' . $this->file_src_temp . '<br />';
						}
						else
						{
							$this->log .= ' failed<br />';
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
				$this->log .= '- <b>process OK</b><br />';
			}
			else
			{
				$this->log .= '- <b>error</b>: ' . $this->error . '<br />';
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
			$this->log .= '<b>cleanup</b><br />';
			$this->log .= '- delete temp file '  . $this->file_src_pathname . '<br />';
			@unlink($this->file_src_pathname);
		}
	}
}

?>