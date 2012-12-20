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

if (!class_exists('LEPTON_Helper_Addons')) {

    if (!class_exists('LEPTON_Object', false)) {
	    @include dirname(__FILE__).'/../Object.php';
	}
	require_once WB_PATH.'/modules/lib_lepton/pages_load/library.php';
	
	class LEPTON_Helper_Addons extends LEPTON_Object	{

	    /**
	     * Register the Addon $module_name in  $module_directory for $page_id
	     * for sending a page title to LEPTON before displaying the page.
	     *
	     * The registered Addon must have the file headers.load.php in the
	     * $module_directory. LEPTON will call the function
	     * 
	     *     $module_directory_get_page_title($page_id)
	     * 
	     * to get the page title provided by the Addon.
	     * 
	     * @param integer $page_id
	     * @param string $module_name
	     * @param string $module_directory
	     * @return boolean true on success
	     */
	    public function register_page_title($page_id, $module_name, $module_directory) {
	        return register_addon_header($page_id, $module_name, $module_directory, 'title');
	    } // register_page_title()
	    
	    /**
	     * Unregister the Addon in $module_directory for $page_id for sending
	     * a page title to LEPTON
	     * 
	     * @param integer $page_id
	     * @param string $module_directory
	     * @return boolean true on success
	     */
	    public function unregister_page_title($page_id, $module_directory) {
	        return unregister_addon_header($page_id, $module_directory, 'title');
	    }
	    
	    /**
	     * Check if the Addon in $module_directory is registered for $page_id
	     * to sending a page title to LEPTON
	     * 
	     * @param integer $page_id
	     * @param string $module_directory
	     * @return boolen on success
	     */
	    public function is_registered_page_title($page_id, $module_directory) {
	        return is_registered_addon_header($page_id, $module_directory, 'title');
	    }
	    
	    /**
	     * Register the Addon $module_name in  $module_directory for $page_id
	     * for sending a page descriptions to LEPTON before displaying the page.
	     *
	     * The registered Addon must have the file headers.load.php in the
	     * $module_directory. LEPTON will call the function
	     *
	     *     $module_directory_get_page_description($page_id)
	     *
	     * to get the page description provided by the Addon.
	     *
	     * @param integer $page_id
	     * @param string $module_name
	     * @param string $module_directory
	     * @return boolean true on success
	     */
	    public function register_page_description($page_id, $module_name, $module_directory) {
	        return register_addon_header($page_id, $module_name, $module_directory, 'description');
	    } // register_page_description()
	     
	    /**
	     * Unregister the Addon in $module_directory for $page_id for sending
	     * a page description to LEPTON
	     *
	     * @param integer $page_id
	     * @param string $module_directory
	     * @return boolean true on success
	     */
	    public function unregister_page_description($page_id, $module_directory) {
	        return unregister_addon_header($page_id, $module_directory, 'description');
	    }
	     
	    /**
	     * Check if the Addon in $module_directory is registered for $page_id
	     * to sending a page description to LEPTON
	     *
	     * @param integer $page_id
	     * @param string $module_directory
	     * @return boolen on success
	     */
	    public function is_registered_page_description($page_id, $module_directory) {
	        return is_registered_addon_header($page_id, $module_directory, 'description');
	    }
	     
	    /**
	     * Register the Addon $module_name in  $module_directory for $page_id
	     * for sending page keywords to LEPTON before displaying the page.
	     *
	     * The registered Addon must have the file headers.load.php in the
	     * $module_directory. LEPTON will call the function
	     *
	     *     $module_directory_get_page_keywords($page_id)
	     *
	     * to get the page keywords provided by the Addon.
	     *
	     * @param integer $page_id
	     * @param string $module_name
	     * @param string $module_directory
	     * @return boolean true on success
	     */
	    public function register_page_keywords($page_id, $module_name, $module_directory) {
	        return register_addon_header($page_id, $module_name, $module_directory, 'keywords');
	    } // register_page_keywords()
	     
	    /**
	     * Unregister the Addon in $module_directory for $page_id for sending
	     * page keywords to LEPTON
	     *
	     * @param integer $page_id
	     * @param string $module_directory
	     * @return boolean true on success
	     */
	    public function unregister_page_keywords($page_id, $module_directory) {
	        return unregister_addon_header($page_id, $module_directory, 'keywords');
	    }
	     
	    /**
	     * Check if the Addon in $module_directory is registered for $page_id
	     * to sending page keywords to LEPTON
	     *
	     * @param integer $page_id
	     * @param string $module_directory
	     * @return boolen on success
	     */
	    public function is_registered_page_keywords($page_id, $module_directory) {
	        return is_registered_addon_header($page_id, $module_directory, 'keywords');
	    }
	     
	    /**
	     * Get the page title for $page_id and the registered addon
	     *
	     * @param integer $page_id
	     * @return boolean true on success
	     */
	    public function get_page_title($page_id) {
	        return get_addon_page_title($page_id);
	    } // get_page_title()
	     
	    /**
	     * Get the page description for $page_id and the registered addon
	     *
	     * @param integer $page_id
	     * @return boolean true on success
	     */
	    public function get_page_description($page_id) {
	        return get_addon_page_description($page_id);
	    } // get_addon_page_description()
	    	  
	    /**
	     * Get the page keywords for $page_id and the registered addon
	     *
	     * @param integer $page_id
	     * @return boolean true on success
	     */
	    public function get_page_keywords($page_id) {
	        return get_addon_page_keywords($page_id);
	    } // get_addon_page_description()
	    
	    /**
	     * This funtion performs pre-installation checks for Addon installations
     	 * The requirements can be specified via the array $PRECHECK which needs to
     	 * be defined in the optional Add-on file precheck.php.
     	 *
     	 * @access public
     	 * @param  string  $temp_addon_file
     	 * @param  string  $temp_path
     	 * @param  boolean $delete_on_fail - delete $temp_addon_file if the checks fail; default: true
     	 * @param  boolean $always_return_result - by default, the function returns void on success. Set this to true to receive the result as HTML
		 */
	    public function preCheckAddon( $temp_addon_file, $temp_path = NULL, $delete_on_fail = true, $always_return_result = false )
		{
		
		    global $parser, $database;
		    
		    // path to the temporary Add-on folder
		    if ($temp_path == '')
		    {
		        $temp_path = LEPTON_PATH . '/temp/unzip';
		    }

		    // check if file precheck.php exists for the Add-On uploaded via WB installation routine
		    if ( ! file_exists($temp_path . '/precheck.php') )
		    {
		        return;
			}

		    // unset any previous declared PRECHECK array
		    unset($PRECHECK);

		    // include Add-On precheck.php file
		    include($temp_path . '/precheck.php');

		    // check if there are any Add-On requirements to check for
		    if ( ! ( isset($PRECHECK) && count($PRECHECK) > 0) ) {
		        return;
			}

		    // sort precheck array
		    $PRECHECK      = $this->__sortPreCheckArray($PRECHECK);
		    $failed_checks = 0;
		    $msg           = array();
		    
		    // check requirements
		    foreach ($PRECHECK as $key => $value)
		    {
		        switch ($key)
		        {

					// check required LEPTON version
		            case 'LEPTON_VERSION':
		                if (isset($value['VERSION']))
		                {
		                    // obtain operator for string comparison if exist
		                    $operator = (isset($value['OPERATOR']) && trim($value['OPERATOR']) != '') ? $value['OPERATOR'] : '>=';
		                    // compare versions and extract actual status
		                    $status   = $this->versionCompare(LEPTON_VERSION, $value['VERSION'], $operator);
		                    $msg[]    = array(
		                        'check' => sprintf('LEPTON-%s: ', $this->lang()->translate('Version') ),
		                        'required' => sprintf('%s %s', htmlentities($operator), $value['VERSION']),
		                        'actual' => LEPTON_VERSION,
		                        'status' => $status
		                    );
		                    // increase counter if required
		                    if (!$status)
		                        $failed_checks++;
		                }
		                break;

					// check prerequisite modules
		            case 'LEPTON_ADDONS':
		                if (is_array($PRECHECK['LEPTON_ADDONS']))
		                {
		                    foreach ($PRECHECK['LEPTON_ADDONS'] as $addon => $values)
		                    {
		                        if (is_array($values))
		                        {
		                            // extract module version and operator
		                            $version  = (isset($values['VERSION']) && trim($values['VERSION']) != '') ? $values['VERSION'] : '';
		                            $operator = (isset($values['OPERATOR']) && trim($values['OPERATOR']) != '') ? $values['OPERATOR'] : '>=';
		                        }
		                        else
		                        {
		                            // no version and operator specified (only check if addon exists)
		                            $addon    = strip_tags($values);
		                            $version  = '';
		                            $operator = '';
		                        }

		                        // check if addon is listed in WB database
		                        $table   = TABLE_PREFIX . 'addons';
		                        $sql     = "SELECT * FROM `$table` WHERE `directory` = '" . addslashes($addon) . "'";
		                        $results = $database->query($sql);

		                        $status       = false;
		                        $addon_status = $this->lang()->translate('Not installed');
		                        if ($results && $row = $results->fetchRow())
		                        {
		                            $status       = true;
		                            $addon_status = $this->lang()->translate('Installed');

		                            // compare version if required
		                            if ($version != '')
		                            {
		                                $status       = $this->versionCompare($row['version'], $version, $operator);
		                                $addon_status = $row['version'];
		                            }
		                        }

		                        // provide addon status
		                        $msg[] = array(
		                            'check' => '&nbsp; ' . $this->lang()->translate('Addon') . ': ' . htmlentities($addon),
		                            'required' => ($version != '') ? $operator . '&nbsp;' . $version : $this->lang()->translate('installed'),
		                            'actual' => $addon_status,
		                            'status' => $status
		                        );

		                        // increase counter if required
		                        if (!$status)
		                            $failed_checks++;
		                    }
		                }
		                break;

					// check required PHP version
		            case 'PHP_VERSION':
		                if (isset($value['VERSION']))
		                {
		                    // obtain operator for string comparison if exist
		                    $operator = (isset($value['OPERATOR']) && trim($value['OPERATOR']) != '') ? $value['OPERATOR'] : '>=';

		                    // compare versions and extract actual status
		                    $status = $this->versionCompare(PHP_VERSION, $value['VERSION'], $operator);
		                    $msg[]  = array(
		                        'check' => 'PHP-' . $this->lang()->translate('Version'),
		                        'required' => htmlentities($operator) . '&nbsp;' . $value['VERSION'],
		                        'actual' => PHP_VERSION,
		                        'status' => $status
		                    );

		                    // increase counter if required
		                    if (!$status)
		                        $failed_checks++;

		                }
		                break;

					// check prerequisite PHP extensions
		            case 'PHP_EXTENSIONS':
		                if (is_array($PRECHECK['PHP_EXTENSIONS']))
		                {
		                    foreach ($PRECHECK['PHP_EXTENSIONS'] as $extension)
		                    {
		                        $status = extension_loaded(strtolower($extension));
		                        $msg[]  = array(
		                            'check' => '&nbsp; ' . $this->lang()->translate('Extension') . ': ' . htmlentities($extension),
		                            'required' => $this->lang()->translate('installed'),
		                            'actual' => ($status) ? $this->lang()->translate('installed') : $this->lang()->translate('not_installed'),
		                            'status' => $status
		                        );

		                        // increase counter if required
		                        if (!$status)
		                            $failed_checks++;
		                    }
		                }
		                break;

					// check required php.ini settings
		            case 'PHP_SETTINGS':
		                if (is_array($PRECHECK['PHP_SETTINGS']))
		                {
		                    foreach ($PRECHECK['PHP_SETTINGS'] as $setting => $value)
		                    {
		                        $actual_setting = ($temp = ini_get($setting)) ? $temp : 0;
		                        $status         = ($actual_setting == $value);
		                        $msg[] = array(
		                            'key'      => 'PHP_SETTINGS',
		                            'check'    => '&nbsp;&nbsp; ' . ($setting),
		                            'required' => $value,
		                            'actual'   => $actual_setting,
		                            'status'   => $status
		                        );

		                        // increase counter if required
		                        if (!$status)
		                            $failed_checks++;
		                    }
		                }
		                break;

					// custom checks; in fact, these are done in precheck.php
		            case 'CUSTOM_CHECKS':
		                if (is_array($PRECHECK['CUSTOM_CHECKS']))
		                {
		                    foreach ($PRECHECK['CUSTOM_CHECKS'] as $key => $values)
		                    {
		                        $status = (true === array_key_exists('STATUS', $values)) ? $values['STATUS'] : false;
		                        $msg[]  = array(
		                            'check' => $key,
		                            'required' => $values['REQUIRED'],
		                            'actual' => $values['ACTUAL'],
		                            'status' => $status
		                        );
		                    }

		                    // increase counter if required
		                    if (!$status)
		                        $failed_checks++;
		                }
		                break;
		        }
		    }

		    // if all requirements are met und $always_return_result is false...
		    if ( $failed_checks == 0 && $always_return_result === false )
		    {
		        return;
			}

		    // output summary table
		    $summary = array();
		    foreach ($msg as $check)
		    {
		        $style = $check['status'] ? 'color: #46882B;' : 'color: #C00;';
		        foreach ($check as $key => $value)
		        {
		            $line = array();
		            if ( $key == 'status' )
		            {
		                continue;
					}
					$line[] = array( 'value' => $value );
		        }
		        $summary[] = array_merge(
								$check,
								array( 'style' => $style ),
								$line
							 );
		    }
		    
		    $parser->setPath( dirname(__FILE__).'/templates/Addons' );
		    $output = $parser->get(
				'summary.lte',
				array(
				    'heading' => (
						  $failed_checks
						? $this->lang()->translate('Precheck failed')
						: $this->lang()->translate('Precheck successful')
					),
				    'message' => (
						  $failed_checks
						? $this->lang()->translate(
							'Installation failed. Your system does not fulfill the defined requirements. Please fix the issues summarized below and try again.'
						  )
						: ''
					),
					'summary' => $summary,
					'fail'    => ( $failed_checks ? true : false ),
				)
			);


		    if ( $delete_on_fail )
		    {
		        // delete the temp unzip directory
		        rm_full_dir($temp_path);

		        // delete the temporary zip file of the Add-on
		        if (file_exists($temp_addon_file))
		        {
		            unlink($temp_addon_file);
		        }
		    }

		    return $output;
		    
		}   // end function preCheckAddon()

	    /**
	     * This funtion creates a version string following the major.minor.revision convention
	     * The minor and revision part of the version may not exceed 999 (three digits)
	     * An optional suffix part can be added after revision (requires $strip_suffix = false)
	     *
	     * EXAMPLES: input --> output
	     *	5 --> 5.000000; 5.0 --> 5.000000; 5.0.0 --> 5.000000
	     * 	5.2 --> 5.002000; 5.20 --> 5.002000; 5.2.0 --> 5.002000
	     * 	5.21 --> 5.002001; 5.2.1 --> 5.002001;
	     * 	5.27.1 --> 5.027001; 5.2.71 --> 5.002071;
	     * 	5.27.1 rc1 --> 5.027001_RC1 ($strip_suffix:= false)
	     *
	     * @access public
	     * @param  string  $version
	     * @param  boolean $strip_suffix - default true
	     * @return string
	     *
	     */
		public function getVersion($version, $strip_suffix = true)
		{
		    // replace comma by decimal point
		    $version = str_replace(',', '.', $version);

		    // convert version into major.minor.revision numbering system
		    list($major, $minor, $revision) = explode('.', $version, 3);

		    // convert versioning style 5.21 into 5.2.1
		    if ($revision == '' && strlen(intval($minor)) == 2)
		    {
		        $revision = substr($minor, -1);
		        $minor    = substr($minor, 0, 1);
		    }

		    // extract possible non numerical suffix from revision part (e.g. Alpha, Beta, RC1)
		    $suffix = strtoupper(trim(substr($revision, strlen(intval($revision)))));

		    // return standard version number (minor and revision numbers may not exceed 999)
		    return sprintf('%d.%03d.%03d%s', (int) $major, (int) minor, (int) $revision, (($strip_suffix == false && $suffix != '') ? '_' . $suffix : ''));

		}   // end function getVersion()

		/**
		 *	As "version_compare" it self seems only got trouble
		 *	within words like "Alpha", "Beta" a.s.o. this function
		 *	only modify the version-string in the way that these words are replaced by values/numbers.
		 *
		 *	E.g:	"1.2.3 Beta2" => "1.2.3.22"
		 *			"0.1.1 ALPHA" => "0.1.1.1"
		 *
		 *	Notice:	Please keep in mind, that this will not correct the way "version_control"
		 *			handel "1 < 1.0 < 1.0.0 < 1.0.0.0" and will not correct missformed version-strings
		 *			below 2.7, e.g. "1.002 released candidate 2.3"
		 *
		 *	@since	2.8.0 RC2
		 *	@notice	2.8.2	Keys in $states have change within a leading dot to get correct results
		 *					within a compare with problematic versions like e.g. "1.1.10 > 1.1.8 rc".
		 *
		 *	@param	string	A versionstring
		 *	@return	string	The modificated versionstring
		 *
		 */
		function getVersion2($version = "")
		{
		    $states = array(
				'.0' => 'dev',
		        '.1' => "alpha",
		        '.2' => "beta",
		        '.4' => "rc",
		        '.8' => "final"
		    );

		    $version = strtolower($version);

		    foreach ($states as $value => $keys)
		        $version = str_replace($keys, $value, $version);

		    $version = str_replace(" ", "", $version);

		    /**
		     *	Force the version-string to get at least 4 terms.
		     *	E.g. 2.7 will become 2.7.0.0
		     *
		     */
		    $temp_array = explode(".", $version);
		    $n          = count($temp_array);
		    if ($n < 4)
		    {
		        for ($i = 0; $i < (4 - $n); $i++)
		            $version = $version . ".0";
		    }

		    return $version;
		}

	    /**
	     * This function performs a comparison of two provided version strings
	     * The versions are first converted into a string following the major.minor.revision
	     * convention; the converted strings are passed to version_compare()
	     *
	     * @access public
	     * @param  string  $version1
	     * @param  string  $version2
	     * @param  string  $operator - default '>='
	     */
		function versionCompare($version1, $version2, $operator = '>=')
		{
		    return version_compare($this->getVersion2($version1), $this->getVersion2($version2), $operator);
		}   // end versionCompare()
		
		/**
		 * This function is used to install a module (addon); function was moved
		 * from functions.php
		 *
		 * @access public
		 * @param  string  Any valid directory(-path)
     	 * @param  boolean Call the install-script of the module? Default: false
		 **/
		public function installModule($directory, $install = false)
		{
	        global $database, $admin;
	        $this->log()->logDebug( 'module directory ['.$directory.'], install flag ['.$install.']' );
	        if (is_dir($directory) && file_exists($directory . "/info.php"))
	        {
	            global $module_name, $module_license, $module_author, $module_directory, $module_version, $module_function, $module_description, $module_platform, $module_guid, $lepton_platform;
	            /**
	             * @internal frankH 2011-08-02 - added $lepton_platform, can be removed when addons are built only for LEPTON
	             */
	            if (isset($lepton_platform) && ($lepton_platform != ''))
	                $module_platform = $lepton_platform;
	            require($directory . "/info.php");
	            if (isset($module_name))
	            {
	                $module_function = strtolower($module_function);
	                // Check that it doesn't already exist
	                $sqlwhere = "WHERE `type` = 'module' AND `directory` = '" . $module_directory . "'";
	                $sql = "SELECT COUNT(*) FROM `" . TABLE_PREFIX . "addons` " . $sqlwhere;
	                if ($database->get_one($sql))
	                {
	                    $sql = "UPDATE `" . TABLE_PREFIX . "addons` SET ";
	                }
	                else
	                {
	                    $sql = "INSERT INTO `" . TABLE_PREFIX . "addons` SET ";
	                    $sqlwhere = '';
	                }
	                $sql .= "`directory` = '" . mysql_real_escape_string($module_directory) . "',";
	                $sql .= "`name` = '" . mysql_real_escape_string($module_name) . "',";
	                $sql .= "`description`= '" . mysql_real_escape_string($module_description) . "',";
	                $sql .= "`type`= 'module',";
	                $sql .= "`function` = '" . mysql_real_escape_string(strtolower($module_function)) . "',";
	                $sql .= "`version` = '" . mysql_real_escape_string($module_version) . "',";
	                $sql .= "`platform` = '" . mysql_real_escape_string($module_platform) . "',";
	                $sql .= "`author` = '" . mysql_real_escape_string($module_author) . "',";
	                $sql .= "`license` = '" . mysql_real_escape_string($module_license) . "'";
	                if (isset($module_guid))
	                {
	                    $sql .= ", `guid` = '" . mysql_real_escape_string($module_guid) . "'";
	                }
	                $sql .= $sqlwhere;
	                $database->query($sql);
	                $this->log()->logDebug( 'SQL: '.$sql );
	                if ($database->is_error())
	                {
	                    $admin->print_error($database->get_error());
	                    $this->log()->logDebug( 'database error: '.$database->get_error() );
					}
	                /**
	                 *  Run installation script
	                 *
	                 */
	                if ($install == true)
	                {
	                    if (file_exists($directory . '/install.php'))
	                    {
	                        $this->log()->logDebug( 'require install.php' );
	                        require($directory . '/install.php');
						}
	                }
	            }
	            else {
	                $this->log()->logDebug( 'var $module_name not set, unable to install module' );
	            }
	        }
		}   // end function installModule()

        /**
         * Allows modules to register a file which should be allowed to load the
         * config.php directly.
         *
         * This is only allowed in installation context!
         *
         * @access public
         * @param  string  $module   - module name
         * @param  string  $filepath - relative file path
         **/
        public function sec_register_file( $module, $filepath )
        {
            global $database;
            global $wb;
            // this will remove ../.. from $filepath
            $filepath = $wb->get_helper('Directory')->sanitizePath($filepath);
            if ( ! is_dir( LEPTON_PATH.'/modules/'.$module ) )
            {
                error_log("sec_register_file() called for non existing module [$module] (path: [$filepath])", 0);
                return false;
            }
            if ( ! file_exists( $wb->get_helper('Directory')->sanitizePath(LEPTON_PATH.'/modules/'.$module.'/'.$filepath) ) )
            {
                error_log("sec_register_file() called for non existing file [$filepath] (module: [$module])", 0);
                return false;
            }
            if ( ! $database )
            {
                $database = new database();
            }
            $q = $database->query( 'SELECT * FROM '.TABLE_PREFIX.'addons WHERE directory = "'.$module.'"' );
            if ( ! $q->numRows() )
            {
                error_log("sec_register_file() called for non existing module [$module] (path: [$filepath]) - not found in addons table!", 0);
                return false;
            }
            $row = $q->fetchRow();
            $q   = $database->query( 'SELECT * FROM '.TABLE_PREFIX.'class_secure WHERE module="'.$row['addon_id'].'" AND filepath="'.$filepath.'"' );
            if ( ! $q->numRows() )
            {
                $database->query( 'INSERT INTO '.TABLE_PREFIX.'class_secure VALUES ( "", "'.$row['addon_id'].'", "/modules/'.$module.'/'.$filepath.'" )' );
            }
        }   // end function sec_register_file()


		/**
		 * sort the $PRECHECK array by keys
		 *
		 * @access private
		 * @param  array
		 * @return array
		 *
		 **/
		private function __sortPreCheckArray($precheck_array)
		{
		    /**
		     * This funtion sorts the precheck array to a common format
		     */
		    // define desired precheck order
		    $key_order = array(
		        'LEPTON_VERSION',
		        'LEPTON_VERSION',
		        'LEPTON_ADDONS',
		        'PHP_VERSION',
		        'PHP_EXTENSIONS',
		        'PHP_SETTINGS',
		        'CUSTOM_CHECKS'
		    );

		    $temp_array = array();
		    foreach ($key_order as $key)
		    {
		        if (!isset($precheck_array[$key]))
		            continue;
		        $temp_array[$key] = $precheck_array[$key];
		    }
		    return $temp_array;
		}   // end function __sortPreCheckArray()
	    	  
	    
	} // class LEPTON_Helper_Addons
	
} // if class_exists()	
