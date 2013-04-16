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

if (!class_exists('CAT_Helper_Addons'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Helper_Addons extends CAT_Object
    {

        private static $dirh;
        private        $error = NULL;
        private static $instance = NULL;
        private static $states = array(
            '.0' => 'dev',
            '.1' => 'preview',
            '.2' => 'alpha',
            '.5' => 'beta',
            '.8' => 'rc',
            '.9' => 'final'
        );
        private static $info_vars_full = array(
            'module' => array(
                'module_license',
                'module_author',
                'module_name',
                'module_home',
                'module_directory',
                'module_version',
                'module_function',
                'module_description',
                'module_platform',
                'module_guid'
            ),
            'template' => array(
                'template_license',
                'template_author',
                'template_name',
                'template_home',
                'template_directory',
                'template_version',
                'template_function',
                'template_description',
                'template_platform',
                'template_guid'
                //'theme_directory'
            ),
            'language' => array(
                'language_license',
                'language_code',
                'language_name',
                'language_version',
                'language_platform',
                'language_author',
                'language_guid'
            )
        );
        private static $info_vars_mandatory = array(
            'module' => array(
                'module_author',
                'module_name',
                'module_directory',
                'module_version',
                'module_function',
            ),
            'template' => array(
                'template_author',
                'template_name',
                'template_directory',
                'template_version',
                'template_function',
            ),
            'language' => array(
                'language_code',
                'language_name',
                'language_version',
                'language_author',
            )
        );
        private static $module_functions   = array(
            'page',
            'library',
            'tool',
            'snippet',
            'wysiwyg',
            'widget'
        );
        private static $template_functions = array(
            'template',   // frontend
            'theme'       // backend
        );

        public function __construct()
        {
            // we need our own instance here, because this helper is used by
            // the installer, which does not have a get_helper() method
            self::$dirh = new CAT_Helper_Directory();
        }

        public static function getInstance()
        {
            if (!self::$instance)
            {
                self::$instance = new self();
            }
            return self::$instance;
        }

    	/**
    	 * get_addons function.
    	 *
    	 * Function to get all addons
    	 *
    	 * @access public
    	 * @param int    $selected    (default: 1)      - name or directory of the the addon to be selected in a dropdown
    	 * @param string $type        (default: '')     - type of addon - can be an array
    	 * @param string $function    (default: '')     - function of addon- can be an array
    	 * @param string $permissions (default: '')     - array(!) of directories to check permissions
    	 * @param string $order       (default: 'name') - value to handle "ORDER BY" for database request of addons
    	 * @return array
    	 */
    	public function get_addons( $selected = 1 , $type = '', $function = '' , $permissions = '' , $order = 'name' )
    	{
            global $database;

    		$and				= '';
    		$get_type			= '';
    		$get_function		= '';

    		if ( is_array($type) )
    		{
    			$get_type		 = '( ';
    			$and			= ' AND ';
    			foreach ( $type as $item)
    			{
    				$get_type	.= 'type = \''.htmlspecialchars( $item).'\''.$and;
    			}
    			$get_type		= substr($get_type, 0, -5).' )';
    		}
    		else if ( $type != '')
    		{
    			$and			= ' AND ';
    			$get_type		= 'type = \''.htmlspecialchars( $type ).'\'';
    		}

    		if ( is_array($function) )
    		{
    			$get_function		 = $and.'( ';
    			foreach ( $function as $item)
    			{
    				$get_function	.= 'function = \''.htmlspecialchars( $item).'\' AND ';
    			}
    			$get_function		= substr($get_function, 0, -5).' )';
    		}
    		else if ( $function != '')
    		{
    			$get_function		= $and.'function = \''.htmlspecialchars( $function ).'\'';
    		}

    		// ==================
    		// ! Get all addons
    		// ==================
    		$addons_array = array();

    		$addons = $database->query("SELECT * FROM " . CAT_TABLE_PREFIX . "addons WHERE ".$get_type.$get_function." ORDER BY ".htmlspecialchars( $order ) );
    		if ( $addons->numRows() > 0 )
    		{
    			$counter = 1;
    			while ( $addon = $addons->fetchRow( MYSQL_ASSOC ) )
    			{
    				if ( ( is_array( $permissions ) && !is_numeric( array_search($addon['directory'], $permissions) ) ) || !is_array( $permissions ) )
    				{
    					$addons_array[$counter]	= array(
    						'VALUE'			=> $addon['directory'],
    						'NAME'			=> $addon['name'],
    						'SELECTED'		=> ( $selected == $counter || $selected == $addon['name'] || $selected == $addon['directory'] ) ? true : false
    					);
    					$counter++;
    				}
    			}
    		}
    		return $addons_array;
    	}

        /**
         * Register the Addon $module_name in  $module_directory for $page_id
         * for sending a page title to BC before displaying the page.
         *
         * The registered Addon must have the file headers.load.php in the
         * $module_directory. BC will call the function
         *
         *	 $module_directory_get_page_title($page_id)
         *
         * to get the page title provided by the Addon.
         *
         * @param integer $page_id
         * @param string $module_name
         * @param string $module_directory
         * @return boolean true on success
         */
        public function register_page_title($page_id, $module_name, $module_directory)
        {
            return register_addon_header($page_id, $module_name, $module_directory, 'title');
        } // register_page_title()

        /**
         * Unregister the Addon in $module_directory for $page_id for sending
         * a page title to BC
         *
         * @param integer $page_id
         * @param string $module_directory
         * @return boolean true on success
         */
        public function unregister_page_title($page_id, $module_directory)
        {
            return unregister_addon_header($page_id, $module_directory, 'title');
        }

        /**
         * Check if the Addon in $module_directory is registered for $page_id
         * to sending a page title to BC
         *
         * @param integer $page_id
         * @param string $module_directory
         * @return boolen on success
         */
        public function is_registered_page_title($page_id, $module_directory)
        {
            return is_registered_addon_header($page_id, $module_directory, 'title');
        }

        /**
         * Register the Addon $module_name in  $module_directory for $page_id
         * for sending a page descriptions to BC before displaying the page.
         *
         * The registered Addon must have the file headers.load.php in the
         * $module_directory. BC will call the function
         *
         *	 $module_directory_get_page_description($page_id)
         *
         * to get the page description provided by the Addon.
         *
         * @param integer $page_id
         * @param string $module_name
         * @param string $module_directory
         * @return boolean true on success
         */
        public function register_page_description($page_id, $module_name, $module_directory)
        {
            return register_addon_header($page_id, $module_name, $module_directory, 'description');
        } // register_page_description()

        /**
         * Unregister the Addon in $module_directory for $page_id for sending
         * a page description to BC
         *
         * @param integer $page_id
         * @param string $module_directory
         * @return boolean true on success
         */
        public function unregister_page_description($page_id, $module_directory)
        {
            return unregister_addon_header($page_id, $module_directory, 'description');
        }

        /**
         * Check if the Addon in $module_directory is registered for $page_id
         * to sending a page description to BC
         *
         * @param integer $page_id
         * @param string $module_directory
         * @return boolen on success
         */
        public function is_registered_page_description($page_id, $module_directory)
        {
            return is_registered_addon_header($page_id, $module_directory, 'description');
        }

        /**
         * Register the Addon $module_name in  $module_directory for $page_id
         * for sending page keywords to BC before displaying the page.
         *
         * The registered Addon must have the file headers.load.php in the
         * $module_directory. BC will call the function
         *
         *	 $module_directory_get_page_keywords($page_id)
         *
         * to get the page keywords provided by the Addon.
         *
         * @param integer $page_id
         * @param string $module_name
         * @param string $module_directory
         * @return boolean true on success
         */
        public function register_page_keywords($page_id, $module_name, $module_directory)
        {
            return register_addon_header($page_id, $module_name, $module_directory, 'keywords');
        } // register_page_keywords()

        /**
         * Unregister the Addon in $module_directory for $page_id for sending
         * page keywords to BC
         *
         * @param integer $page_id
         * @param string $module_directory
         * @return boolean true on success
         */
        public function unregister_page_keywords($page_id, $module_directory)
        {
            return unregister_addon_header($page_id, $module_directory, 'keywords');
        }

        /**
         * Check if the Addon in $module_directory is registered for $page_id
         * to sending page keywords to BC
         *
         * @param integer $page_id
         * @param string $module_directory
         * @return boolen on success
         */
        public function is_registered_page_keywords($page_id, $module_directory)
        {
            return is_registered_addon_header($page_id, $module_directory, 'keywords');
        }

        /**
         * Get the page title for $page_id and the registered addon
         *
         * @param integer $page_id
         * @return boolean true on success
         */
        public function get_page_title($page_id)
        {
            return get_addon_page_title($page_id);
        } // get_page_title()

        /**
         * Get the page description for $page_id and the registered addon
         *
         * @param integer $page_id
         * @return boolean true on success
         */
        public function get_page_description($page_id)
        {
            return get_addon_page_description($page_id);
        } // get_addon_page_description()

        /**
         * Get the page keywords for $page_id and the registered addon
         *
         * @param integer $page_id
         * @return boolean true on success
         */
        public function get_page_keywords($page_id)
        {
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
        public function preCheckAddon($temp_addon_file, $temp_path = NULL, $delete_on_fail = true, $always_return_result = false)
        {
            global $parser, $database;

            // path to the temporary Add-on folder
            if ($temp_path == '')
            {
                $temp_path = CAT_PATH . '/temp/unzip';
            }

            // check if file precheck.php exists for the Add-On uploaded via WB installation routine
            if (!file_exists($temp_path . '/precheck.php'))
            {
                return;
            }

            // unset any previous declared PRECHECK array
            unset($PRECHECK);

            // include Add-On precheck.php file
            include($temp_path . '/precheck.php');

            // check if there are any Add-On requirements to check for
            if (!(isset($PRECHECK) && count($PRECHECK) > 0))
            {
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

                    // check required CMS version
                    case 'CAT_VERSION':
                    case 'WB_VERSION':
                    case 'LEPTON_VERSION':
                        if (isset($value['VERSION']))
                        {
                            $this_version = CAT_VERSION;
                            // obtain operator for string comparison if exist
                            $operator = (isset($value['OPERATOR']) && trim($value['OPERATOR']) != '') ? $value['OPERATOR'] : '>=';
                            // compare versions and extract actual status
                            $status   = $this->versionCompare(CAT_VERSION, $value['VERSION'], $operator);
                            $msg[]    = array(
                                'check' => sprintf('CMS-%s: ', $this->lang()->translate('Version')),
                                'required' => sprintf('%s %s', htmlentities($operator), $value['VERSION']),
                                'actual' => $this_version,
                                'status' => $status
                            );
                            // increase counter if required
                            if (!$status)
                                $failed_checks++;
                        }
                        break;

                    // check prerequisite modules
                    case 'CAT_ADDONS':
                        if (is_array($PRECHECK['CAT_ADDONS']))
                        {
                            foreach ($PRECHECK['CAT_ADDONS'] as $addon => $values)
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
                                $table   = CAT_TABLE_PREFIX . 'addons';
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
                                $msg[]          = array(
                                    'key' => 'PHP_SETTINGS',
                                    'check' => '&nbsp;&nbsp; ' . ($setting),
                                    'required' => $value,
                                    'actual' => $actual_setting,
                                    'status' => $status
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
            if ($failed_checks == 0 && $always_return_result === false)
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
                    if ($key == 'status')
                    {
                        continue;
                    }
                    $line[] = array(
                        'value' => $value
                    );
                }
                $summary[] = array_merge($check, array(
                    'style' => $style
                ), $line);
            }

            $parser->setPath(dirname(__FILE__) . '/templates/Addons');
            $output = $parser->get('summary.lte', array(
                'heading' => ($failed_checks ? $this->lang()->translate('Precheck failed') : $this->lang()->translate('Precheck successful')),
                'message' => ($failed_checks ? $this->lang()->translate('Installation failed. Your system does not fulfill the defined requirements. Please fix the issues summarized below and try again.') : ''),
                'summary' => $summary,
                'fail' => ($failed_checks ? true : false)
            ));


            if ($delete_on_fail)
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

        } // end function preCheckAddon()

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

        } // end function getVersion()

        /**
         * removes/replaces known substrings in version string with their
         * weights
         *
         * @access public
         * @param  string  $version
         * @return string
         */
        public function getVersion2($version)
        {
            $version = strtolower($version);

            foreach (self::$states as $value => $keys)
            {
                $version = str_replace($keys, $value, $version);
            }

            $version = str_replace(" ", "", $version);

            /**
             *	Force the version-string to get at least 4 terms.
             *	E.g. 2.7 will become 2.7.0.0
             */
            $temp_array = explode(".", $version);
            $n          = count($temp_array);
            if ($n < 4)
            {
                for ($i = 0; $i < (4 - $n); $i++)
                    $version = $version . ".0";
            }

            return $version;
        }   // end function getVersion2()

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
        } // end versionCompare()

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
            $this->log()->logDebug('module directory [' . $directory . '], install flag [' . $install . ']');
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
                    $sqlwhere        = "WHERE `type` = 'module' AND `directory` = '" . $module_directory . "'";
                    $sql             = "SELECT COUNT(*) FROM `" . CAT_TABLE_PREFIX . "addons` " . $sqlwhere;
                    if ($database->get_one($sql))
                    {
                        $sql = "UPDATE `" . CAT_TABLE_PREFIX . "addons` SET ";
                    }
                    else
                    {
                        $sql      = "INSERT INTO `" . CAT_TABLE_PREFIX . "addons` SET ";
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
                    $this->log()->logDebug('SQL: ' . $sql);
                    if ($database->is_error())
                    {
                        $admin->print_error($database->get_error());
                        $this->log()->logDebug('database error: ' . $database->get_error());
                    }
                    /**
                     *  Run installation script
                     *
                     */
                    if ($install == true)
                    {
                        if (file_exists($directory . '/install.php'))
                        {
                            $this->log()->logDebug('require install.php');
                            require($directory . '/install.php');
                        }
                    }
                }
                else
                {
                    $this->log()->logDebug('var $module_name not set, unable to install module');
                }
            }
        } // end function installModule()

        /**
         * This function is used to install a template (addon); function was moved
         * from functions.php
         *
         * @access public
         * @param  string  Any valid directory(-path)
         **/
        public function installTemplate($directory)
        {
            global $database, $admin;
            $this->log()->logDebug('template directory [' . $directory . ']');
            if (is_dir($directory) && file_exists($directory . '/info.php'))
            {
                global $template_license, $template_directory, $template_author, $template_version, $template_function, $template_description, $template_platform, $template_name, $template_guid;
                require($directory . "/info.php");
                // Check that it doesn't already exist
                if (isset($template_name))
                {
                    $sqlwhere = "WHERE `type` = 'template' AND `directory` = '" . $template_directory . "'";
                    $sql      = "SELECT COUNT(*) FROM `" . CAT_TABLE_PREFIX . "addons` " . $sqlwhere;
                    if ($database->get_one($sql))
                    {
                        $sql = "UPDATE `" . CAT_TABLE_PREFIX . "addons` SET ";
                    }
                    else
                    {
                        $sql      = "INSERT INTO `" . CAT_TABLE_PREFIX . "addons` SET ";
                        $sqlwhere = "";
                    }
                    $sql .= "`directory` = '" . mysql_real_escape_string($template_directory) . "',";
                    $sql .= "`name` = '" . mysql_real_escape_string($template_name) . "',";
                    $sql .= "`description`= '" . mysql_real_escape_string($template_description) . "',";
                    $sql .= "`type`= 'template',";
                    $sql .= "`function` = '" . mysql_real_escape_string($template_function) . "',";
                    $sql .= "`version` = '" . mysql_real_escape_string($template_version) . "',";
                    $sql .= "`platform` = '" . mysql_real_escape_string($template_platform) . "',";
                    $sql .= "`author` = '" . mysql_real_escape_string($template_author) . '\', ';
                    $sql .= "`license` = '" . mysql_real_escape_string($template_license) . "' ";
                    if (isset($template_guid))
                    {
                        $sql .= ", `guid` = '" . mysql_real_escape_string($template_guid) . "' ";
                    }
                    else
                    {
                        $sql .= ", `guid` = '' ";
                    }
                    $sql .= $sqlwhere;
                    $this->log()->logDebug('SQL: ' . $sql);
                    $database->query($sql);
                    if ($database->is_error())
                    {
                        $admin->print_error($database->get_error());
                        $this->log()->logDebug('database error: ' . $database->get_error());
                    }
                }
                else
                {
                    $this->log()->logDebug('var $module_name not set, unable to install module');
                }
            }
        } // end function installTemplate()


        /**
         * This function is used to install a language (addon); function was moved
         * from functions.php
         *
         * @access public
         * @param  string  Any valid file(-path)
         **/
        public function installLanguage($file)
        {
            global $database, $admin;
            if (file_exists($file) && preg_match('#^([A-Z]{2}.php)#', basename($file)))
            {
                $language_license  = null;
                $language_code     = null;
                $language_version  = null;
                $language_guid     = null;
                $language_name     = null;
                $language_author   = null;
                $language_platform = null;
                require($file);
                if (isset($language_name))
                {
                    if ((!isset($language_license)) || (!isset($language_code)) || (!isset($language_version)) || (!isset($language_guid)))
                    {
                        $admin->print_error($MESSAGE["LANG_MISSING_PARTS_NOTICE"], $language_name);
                    }
                    // Check that it doesn't already exist
                    $sqlwhere = 'WHERE `type` = \'language\' AND `directory` = \'' . $language_code . '\'';
                    $sql      = 'SELECT COUNT(*) FROM `' . CAT_TABLE_PREFIX . 'addons` ' . $sqlwhere;
                    if ($database->get_one($sql))
                    {
                        $sql = 'UPDATE `' . CAT_TABLE_PREFIX . 'addons` SET ';
                    }
                    else
                    {
                        $sql      = 'INSERT INTO `' . CAT_TABLE_PREFIX . 'addons` SET ';
                        $sqlwhere = '';
                    }
                    $sql .= '`directory` = \'' . $language_code . '\', ';
                    $sql .= '`name` = \'' . $language_name . '\', ';
                    $sql .= '`type`= \'language\', ';
                    $sql .= '`version` = \'' . $language_version . '\', ';
                    $sql .= '`platform` = \'' . $language_platform . '\', ';
                    $sql .= '`author` = \'' . addslashes($language_author) . '\', ';
                    $sql .= '`license` = \'' . addslashes($language_license) . '\', ';
                    $sql .= '`guid` = \'' . $language_guid . '\', ';
                    $sql .= '`description` = \'\'  ';
                    $sql .= $sqlwhere;
                    $database->query($sql);
                    if ($database->is_error())
                    {
                        $admin->print_error($database->get_error());
                        $this->log()->logDebug('database error: ' . $database->get_error());
                    }
                }
            }
        } // end function installLanguage()

        /**
         *  Try to get the current version of a given Modul.
         *
         *  @param  string   $modulename - module directory name
         *  @param  boolean  $source     - true reads from database, false from info.php
         *  @return string   the version as string, if not found returns null
         *
         */
        public function getModuleVersion($modulename, $source = true)
        {
            global $database;
            $version = null;
            if ($source != true)
            {
                $sql = "SELECT `version` FROM `" . CAT_TABLE_PREFIX . "addons` WHERE `directory`='" . $modulename . "'";
                $version = $database->get_one($sql);
            }
            else
            {
                $info_file = CAT_PATH . '/modules/' . $modulename . '/info.php';
                if (file_exists($info_file))
                {
                    $module_version = null;
                    require($info_file);
                    $version = &$module_version;
                }
            }
            return $version;
        }   // end function getModuleVersion()


        /**
         * This function is used to upgrade a module (addon); function was moved
         * from functions.php
         *
         * @access public
         * @param  string  Any valid file(-path)
         **/
        public function upgradeModule($directory, $upgrade = false)
        {
            global $database, $admin;
            global $module_license, $module_author, $module_name, $module_directory, $module_version, $module_function, $module_guid, $module_description, $module_platform;
            $fields = array(
                'version' => $module_version,
                'description' => mysql_real_escape_string($module_description),
                'platform' => $module_platform,
                'author' => mysql_real_escape_string($module_author),
                'license' => mysql_real_escape_string($module_license),
                'guid' => mysql_real_escape_string($module_guid)
            );
            $sql    = 'UPDATE `' . CAT_TABLE_PREFIX . 'addons` SET ';
            foreach ($fields as $key => $value)
                $sql .= "`" . $key . "`='" . $value . "',";
            $sql = substr($sql, 0, -1) . " WHERE `directory`= '" . $module_directory . "'";
            $database->query($sql);

            if ($database->is_error())
            {
                $admin->print_error($database->get_error());
                $this->log()->logDebug('database error: ' . $database->get_error());
            }
        } // end function upgradeModule()

        /**
         * checks if a module is installed
         *
         * @access public
         * @param  string  $module  - module name or directory name
         * @param  string  $version - (optional) version to check (>=)
         * @return boolean
         **/
        public function isModuleInstalled($module,$version=NULL)
        {
            global $database;
            $sql = 'SELECT * FROM `' . CAT_TABLE_PREFIX . 'addons` WHERE type="module" AND ( directory="'.$module.'" OR name="'.$module.'" )';
            $q = $database->query($sql);
            if (!$q->numRows())
            {
                return false;
            }
            // note: if there's more than one, the first match will be returned!
            while ( $addon = $q->fetchRow( MYSQL_ASSOC ) )
			{
                if($version && $this->versionCompare($addon['version'],$version))
                {
                    return true;
                }
                // name over directory
                if($addon['name']==$module)
                {
                    return true;
                }
                if($addon['directory']==$module)
                {
                    return true;
                }
            }
            return false;
        }   // end function isModuleInstalled()


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
        public function sec_register_file($module, $filepath)
        {
            global $database, $admin;
            // check for admin context
            if (!is_object($admin))
            {
                error_log("sec_register_file() called outside admin context!", 0);
                return false; 
            }
            // check permissions
            if (!$admin->get_permission('modules_install'))
            {
                error_log("sec_register_file() called without modules_install perms!", 0);
                return false;
            }
            // this will remove ../.. from $filepath
            $filepath = self::$dirh->sanitizePath($filepath);
            if (!is_dir(CAT_PATH . '/modules/' . $module))
            {
                error_log("sec_register_file() called for non existing module [$module] (path: [$filepath])", 0);
                return false;
            }
            if (!file_exists(self::$dirh->sanitizePath(CAT_PATH . '/modules/' . $module . '/' . $filepath)))
            {
                error_log("sec_register_file() called for non existing file [$filepath] (module: [$module])", 0);
                return false;
            }
            if (!$database)
            {
                $database = new database();
            }
            $q = $database->query('SELECT * FROM ' . CAT_TABLE_PREFIX . 'addons WHERE directory = "' . $module . '"');
            if (!$q->numRows())
            {
                error_log("sec_register_file() called for non existing module [$module] (path: [$filepath]) - not found in addons table!", 0);
                return false;
            }
            $row = $q->fetchRow();
            // remove trailing / from $filepath
            $filepath = preg_replace( '~^/~', '', $filepath );
            $q   = $database->query('SELECT * FROM ' . CAT_TABLE_PREFIX . 'class_secure WHERE module="' . $row['addon_id'] . '" AND filepath="/modules/' . $module . '/' . $filepath . '"');
            if (!$q->numRows())
            {
                $database->query('REPLACE INTO ' . CAT_TABLE_PREFIX . 'class_secure VALUES ( "' . $row['addon_id'] . '", "/modules/' . $module . '/' . $filepath . '" )');
            }
        } // end function sec_register_file()


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
                'CAT_VERSION',
                'CAT_VERSION',
                'CAT_ADDONS',
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
        } // end function __sortPreCheckArray()


        /**
         * This function is used to check info.php
         *
         * @access public
         * @param  string  Any valid directory(-path)
         **/
        public function checkInfo($directory)
        {
            if (is_dir($directory) && file_exists($directory . '/info.php'))
            {

                require($directory . '/info.php');

                if (isset($module_function) && in_array(strtolower($module_function), self::$module_functions))
                {
                    $return_values = array(
                        'addon_function' => 'module'
                    );
                }
                else if (isset($template_function) && in_array(strtolower($template_function), self::$template_functions))
                {
                    $return_values = array(
                        'addon_function' => 'template'
                    );
                }
                else
                {
                    $this->error = 'Invalid info.php - neither $module_function nor $template_function set';
                    $this->log()->logDebug($this->error);
                    return false;
                }
                // Check if the file is valid
                foreach (self::$info_vars_mandatory[$return_values['addon_function']] as $varname)
                {
                    if (!isset(${$varname}))
                    {
                        $this->error = 'Invalid info.php - mandatory var ' . $varname . ' not set';
                        $this->log()->logDebug($this->error);
                        return false;
                    }
                    else
                    {
                        $return_values[$varname] = ${$varname};
                    }
                }
                return $return_values;
            }
            elseif (file_exists($directory) && pathinfo($directory, PATHINFO_EXTENSION) == 'php')
            {
                // Check if the file is valid
                $content = file_get_contents($directory);
                if (strpos($content, '<?php') === false)
                {
                    $this->error = 'Invalid language file - missing PHP delimiter';
                    $this->log()->logDebug($this->error);
                    return false;
                }

                $return_values = array(
                    'addon_function' => 'language'
                );
                require($directory);

                foreach (self::$info_vars_mandatory['language'] as $varname)
                {
                    if (!isset(${$varname}))
                    {
                        $this->error = 'Invalid language file - var ' . $varname . ' not set';
                        $this->log()->logDebug($this->error);
                        return false;
                    }
                    else
                    {
                        $return_values[$varname] = ${$varname};
                    }
                }
                return $return_values;
            }
            else
            {
                $this->error = 'invalid directory/language file or info.php is missing, check of language file failed';
                $this->log()->logDebug($this->error);
                return false;
            }
        } // end function checkInfo()

        public function getError()
        {
            return $this->lang()->translate($this->error);
        }

        /**
         * find available libraries; path names must begin with 'lib_'
         *
         *
         *
         **/
        public function getLibraries($type=NULL)
        {
            $dir  = self::$dirh->sanitizePath(CAT_PATH.'/modules');
            $libs = array();
            if ( $handle = opendir($dir) ) {
                while ( false !== ( $file = readdir($handle) ) ) {
                    if ( $file != "." && $file != ".." ) {
                        if (
                               is_dir( $dir.'/'.$file )
                            && preg_match( '#^lib_#', $file )
                            && file_exists( $dir.'/'.$file.'/info.php' )
                        ) {
                            $module_directory = $module_name = $library_function = NULL;
                            include $dir.'/'.$file.'/info.php';
                            if ( $type !== NULL && $library_function === NULL)
                            {
                                continue;
                            }
                            if ( $type !== NULL && $library_function !== $type )
                            {
                                continue;
                            }
                            $libs[] = array( 'name' => $module_name, 'dir' => $module_directory, 'function' => $library_function );
                        }
                    }
                }
                closedir($handle);
            }
            return $libs;
        }

    } // class CAT_Helper_Addons

} // if class_exists()
