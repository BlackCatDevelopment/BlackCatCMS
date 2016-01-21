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
 *   @copyright       2013, 2014, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if ( !class_exists( 'CAT_Helper_Addons' ) )
{
    if ( !class_exists( 'CAT_Object', false ) )
    {
        @include dirname( __FILE__ ) . '/../Object.php';
    }

    class CAT_Helper_Addons extends CAT_Object
    {
        // array to store config options
        protected      $_config = array(
                           'loglevel' => 4
                       );
        protected      $debugLevel = 4;
        private static $error = NULL;
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
                               'module_guid',
                               'module_link',
                               'module_variants'
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
                               'template_guid',
                               'template_variants',
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
                               'module_function'
                           ),
                           'template' => array(
                               'template_author',
                               'template_name',
                               'template_directory',
                               'template_version',
                               'template_function'
                           ),
                           'language' => array(
                               'language_code',
                               'language_name',
                               'language_version',
                               'language_author'
                           )
                       );
        private static $module_functions = array( 'page', 'library', 'tool', 'snippet', 'wysiwyg', 'widget' );
        private static $template_functions = array(
                           'template', // frontend
                           'theme'     // backend
                       );

        public function __construct()
        {
        }   // end constructor

        public function __call( $method, $args )
        {
            if ( !isset( $this ) || !is_object( $this ) )
                return false;
            if ( method_exists( $this, $method ) )
                return call_user_func_array( array(
                     $this,
                    $method
                ), $args );
        }   // end __call()

        public static function getInstance()
        {
            if ( !self::$instance )
            {
                self::$instance = new self();
            }
            return self::$instance;
        } // end function getInstance()

        /**
         * allows to get addon details by using the ID as set in the DB;
         * returns array on success, false on fail
         *
         * @access public
         * @param  integer $id
         * @return mixed
         **/
        public static function getAddonByID($id)
        {
            $self  = self::getInstance();
            $addon = $self->db()->query(
                'SELECT * FROM `:prefix:addons` WHERE addon_id=:id',
                array('id'=>$id)
            );
            if ( $addon->rowCount() > 0 )
                return $addon->fetchRow();
            return NULL;
        }   // end function getAddonByID()

        /**
         * gets the details of an addons; uses the directory name to find the
         * addon in the DB
         *
         * @access public
         * @param  string  $directory
         * @return mixed   array on success, NULL otherwise
         **/
        public static function getAddonDetails($directory)
        {
            $self  = self::getInstance();
            $addon = $self->db()->query(
                'SELECT * FROM `:prefix:addons` WHERE directory=:dir',
                array('dir'=>$directory)
            );
            if ( $addon->rowCount() > 0 )
            {
                return $addon->fetch(PDO::FETCH_ASSOC);
            }
            return NULL;
        } // end function getAddonDetails()

        /**
         *
         * @access public
         * @return
         **/
        public static function getAddonVariant($field_to_select,$field_to_check,$check_for,$tablename,$section_id)
        {
            $self = self::getInstance();
            $q    = $self->db()->query(
                  'SELECT :field_to_select: FROM `:prefix:'.$tablename.'` '
                . 'WHERE `section_id`=:section_id '
                . 'AND `:field_to_check:`=:check_for',
                array(
                    'field_to_select' => $field_to_select,
                    'field_to_check'  => $field_to_check,
                    'check_for'       => $check_for,
                    'section_id'      => $section_id
                )
            );
            return ( $self->db()->isError() ? NULL : $q->fetchColumn() );
        }   // end function getAddonVariant()

        /**
         * get_addons function.
         *
         * Function to get all addons
         *
         * @access public
         * @param int    $selected    (default: 1)      - name or directory of the the addon to be selected in a dropdown
         * @param string $type        (default: '')     - type of addon - can be an array
         * @param string $function    (default: '')     - function of addon- can be an array
         * @param string $order       (default: 'name') - value to handle "ORDER BY" for database request of addons
         * @param boolean $check_permission (default: false) - wether to check module permissions (BE call) or not
         * @return array
         */
        public static function get_addons( $selected = 1, $type = '', $function = '', $order = 'name', $check_permission = false )
        {
            $self = self::getInstance();

            if ( CAT_Backend::isBackend() )
                $check_permission = true;
            $and          = '';
            $get_type     = '';
            $get_function = '';
            $where        = '';

            if ( is_array( $type ) )
            {
                $get_type = '( ';
                $and      = ' AND ';
                foreach ( $type as $item )
                {
                    $get_type .= 'type = \'' . htmlspecialchars( $item ) . '\'' . $and;
                }
                $get_type = substr( $get_type, 0, -5 ) . ' )';
            }
            else if ( $type != '' )
            {
                $and      = ' AND ';
                $get_type = 'type = \'' . htmlspecialchars( $type ) . '\'';
            }

            if ( is_array( $function ) )
            {
                $get_function = $and . '( ';
                foreach ( $function as $item )
                {
                    $get_function .= 'function = \'' . htmlspecialchars( $item ) . '\' AND ';
                }
                $get_function = substr( $get_function, 0, -5 ) . ' )';
            }
            else if ( $function != '' )
            {
                $get_function = $and . 'function = \'' . htmlspecialchars( $function ) . '\'';
            }

            if ( $get_type || $get_function )
                $where = 'WHERE ';

            // ==================
            // ! Get all addons
            // ==================
            $addons_array = array();
            $addons       = $self->db()->query(
                sprintf(
                    "SELECT * FROM `:prefix:addons` %s%s%s ORDER BY 'type' ASC, '%s' ASC",
                    $where, $get_type, $get_function, htmlspecialchars($order)
                )
            );
            if ( $addons->rowCount() > 0 )
            {
                $counter = 1;
                while ( $addon = $addons->fetchRow() )
                {
                    if ( !$check_permission || ( $addon['type'] != 'language' && CAT_Users::get_permission( $addon['directory'], $addon['type'] ) ) || $addon['type'] == 'language' )
                    {
                        $addons_array[ $counter ] = array_merge( $addon, array(
                            'VALUE' => $addon['directory'],
                            'NAME' => $addon['name'],
                            'SELECTED' => ( $selected == $counter || $selected == $addon['name'] || $selected == $addon['directory'] ) ? true : false
                        ) );
                        $counter++;
                    }
                }
            }
            // reorder array
            $addons_array = CAT_Helper_Array::ArraySort( $addons_array, $order, 'asc', true );

            return $addons_array;
        } // end function get_addons()

        /*******************************************************************************
         * The following methods are derived from DropletsExtension module
         ******************************************************************************/

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
        public static function register_page_title( $page_id, $module_name, $module_directory )
        {
            return register_addon_header( $page_id, $module_name, $module_directory, 'title' );
        } // end function register_page_title()

        /**
         * Unregister the Addon in $module_directory for $page_id for sending
         * a page title to BC
         *
         * @param integer $page_id
         * @param string $module_directory
         * @return boolean true on success
         */
        public static function unregister_page_title( $page_id, $module_directory )
        {
            return unregister_addon_header( $page_id, $module_directory, 'title' );
        } // end function unregister_page_title()

        /**
         * Check if the Addon in $module_directory is registered for $page_id
         * to sending a page title to BC
         *
         * @param integer $page_id
         * @param string $module_directory
         * @return boolen on success
         */
        public static function is_registered_page_title( $page_id, $module_directory )
        {
            return is_registered_addon_header( $page_id, $module_directory, 'title' );
        } // end function is_registered_page_title(

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
        public static function register_page_description( $page_id, $module_name, $module_directory )
        {
            return register_addon_header( $page_id, $module_name, $module_directory, 'description' );
        } // end function register_page_description()

        /**
         * Unregister the Addon in $module_directory for $page_id for sending
         * a page description to BC
         *
         * @param integer $page_id
         * @param string $module_directory
         * @return boolean true on success
         */
        public static function unregister_page_description( $page_id, $module_directory )
        {
            return unregister_addon_header( $page_id, $module_directory, 'description' );
        } // end function unregister_page_description()

        /**
         * Check if the Addon in $module_directory is registered for $page_id
         * to sending a page description to BC
         *
         * @param integer $page_id
         * @param string $module_directory
         * @return boolen on success
         */
        public static function is_registered_page_description( $page_id, $module_directory )
        {
            return is_registered_addon_header( $page_id, $module_directory, 'description' );
        } // end function is_registered_page_description()

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
        public static function register_page_keywords( $page_id, $module_name, $module_directory )
        {
            return register_addon_header( $page_id, $module_name, $module_directory, 'keywords' );
        } // end function register_page_keywords()

        /**
         * Unregister the Addon in $module_directory for $page_id for sending
         * page keywords to BC
         *
         * @param integer $page_id
         * @param string $module_directory
         * @return boolean true on success
         */
        public static function unregister_page_keywords( $page_id, $module_directory )
        {
            return unregister_addon_header( $page_id, $module_directory, 'keywords' );
        } // end function unregister_page_keywords()

        /**
         * Check if the Addon in $module_directory is registered for $page_id
         * to sending page keywords to BC
         *
         * @param integer $page_id
         * @param string $module_directory
         * @return boolen on success
         */
        public static function is_registered_page_keywords( $page_id, $module_directory )
        {
            return is_registered_addon_header( $page_id, $module_directory, 'keywords' );
        } // end function is_registered_page_keywords()

        /**
         * Get the page title for $page_id and the registered addon
         *
         * @param integer $page_id
         * @return boolean true on success
         */
        public static function get_page_title( $page_id )
        {
            return get_addon_page_title( $page_id );
        } // end function get_page_title()

        /**
         * Get the page description for $page_id and the registered addon
         *
         * @param integer $page_id
         * @return boolean true on success
         */
        public static function get_page_description( $page_id )
        {
            return get_addon_page_description( $page_id );
        } // end function get_addon_page_description()

        /**
         * Get the page keywords for $page_id and the registered addon
         *
         * @param integer $page_id
         * @return boolean true on success
         */
        public static function get_page_keywords( $page_id )
        {
            return get_addon_page_keywords( $page_id );
        } // end function get_addon_page_description()

        /*******************************************************************************
         * End of derived methods
         ******************************************************************************/

        /**
         * This function performs pre-installation checks for Addon installations
         * The requirements can be specified via the array $PRECHECK which needs to
         * be defined in the optional Add-on file precheck.php.
         *
         * @access public
         * @param  string  $temp_addon_file
         * @param  string  $temp_path
         * @param  boolean $delete_on_fail - delete $temp_addon_file if the checks fail; default: true
         * @param  boolean $always_return_result - by default, the function returns void on success. Set this to true to receive the result as HTML
         */
        public static function preCheckAddon( $temp_addon_file, $temp_path = NULL, $delete_on_fail = true, $always_return_result = false )
        {
            global $parser;

            // path to the temporary Add-on folder
            if ( $temp_path == '' )
                $temp_path = CAT_PATH . '/temp/unzip';

            // check if file precheck.php exists for the Add-On uploaded via WB installation routine
            if ( !file_exists( $temp_path . '/precheck.php' ) )
                return;

            // unset any previous declared PRECHECK array
            unset( $PRECHECK );

            // include Add-On precheck.php file
            include( $temp_path . '/precheck.php' );

            // check if there are any Add-On requirements to check for
            if ( !( isset( $PRECHECK ) && count( $PRECHECK ) > 0 ) )
                return;

            // sort precheck array
            $PRECHECK      = self::sortPreCheckArray( $PRECHECK );
            $failed_checks = 0;
            $msg           = array();

            // check requirements
            foreach ( $PRECHECK as $key => $value )
            {
                switch ( $key )
                {

                    // check required CMS version
                    case 'CAT_VERSION':
                    case 'WB_VERSION':
                    case 'LEPTON_VERSION':
                    case 'VERSION':
                        list( $status, $msg[] ) = self::checkCMSVersion( $key, $value );
                        // increase counter if required
                        if ( !$status )
                            $failed_checks++;
                        break;

                    // check prerequisite modules
                    case 'CAT_ADDONS':
                    case 'WB_ADDONS':
                        list( $status, $add_to_msg ) = self::checkAddons( $PRECHECK[ $key ] );
                        if ( count( $add_to_msg ) )
                            $msg = array_merge( $msg, $add_to_msg );
                        if ( !$status )
                            $failed_checks++;
                        break;

                    // check required PHP version
                    case 'PHP_VERSION':
                        if ( isset( $value['VERSION'] ) )
                        {
                            // obtain operator for string comparison if exist
                            $operator = ( isset( $value['OPERATOR'] ) && trim( $value['OPERATOR'] ) != '' ) ? $value['OPERATOR'] : '>=';

                            // compare versions and extract actual status
                            $status = self::versionCompare( PHP_VERSION, $value['VERSION'], $operator );
                            $msg[]  = array(
                                'check' => 'PHP-' . self::getInstance()->lang()->translate( 'Version' ),
                                'required' => htmlentities( $operator ) . '&nbsp;' . $value['VERSION'],
                                'actual' => PHP_VERSION,
                                'status' => $status
                            );

                            // increase counter if required
                            if ( !$status )
                                $failed_checks++;

                        }
                        break;

                    // check prerequisite PHP extensions
                    case 'PHP_EXTENSIONS':
                        if ( is_array( $PRECHECK['PHP_EXTENSIONS'] ) )
                        {
                            foreach ( $PRECHECK['PHP_EXTENSIONS'] as $extension )
                            {
                                $status = extension_loaded( strtolower( $extension ) );
                                $msg[]  = array(
                                     'check' => '&nbsp; ' . self::getInstance()->lang()->translate( 'Extension' ) . ': ' . htmlentities( $extension ),
                                    'required' => self::getInstance()->lang()->translate( 'installed' ),
                                    'actual' => ( $status ) ? self::getInstance()->lang()->translate( 'installed' ) : self::getInstance()->lang()->translate( 'not_installed' ),
                                    'status' => $status
                                );

                                // increase counter if required
                                if ( !$status )
                                    $failed_checks++;
                            }
                        }
                        break;

                    // check required php.ini settings
                    case 'PHP_SETTINGS':
                        if ( is_array( $PRECHECK['PHP_SETTINGS'] ) )
                        {
                            foreach ( $PRECHECK['PHP_SETTINGS'] as $setting => $values )
                            {
                                $actual_setting = ( $temp = ini_get( $setting ) ) ? $temp : 0;
                                $status         = ( $actual_setting == $values );
                                $msg[]          = array(
                                     'key' => 'PHP_SETTINGS',
                                    'check' => '&nbsp;&nbsp; ' . ( $setting ),
                                    'required' => $values,
                                    'actual' => $actual_setting,
                                    'status' => $status
                                );

                                // increase counter if required
                                if ( !$status )
                                    $failed_checks++;
                            }
                        }
                        break;

                    // custom checks; in fact, these are done in precheck.php
                    case 'CUSTOM_CHECKS':
                        if ( is_array( $PRECHECK['CUSTOM_CHECKS'] ) )
                        {
                            foreach ( $PRECHECK['CUSTOM_CHECKS'] as $custom_key => $values )
                            {
                                $status = ( true === array_key_exists( 'STATUS', $values ) ) ? $values['STATUS'] : false;
                                $msg[]  = array(
                                     'check' => $custom_key,
                                    'required' => $values['REQUIRED'],
                                    'actual' => $values['ACTUAL'],
                                    'status' => $status
                                );
                            }

                            // increase counter if required
                            if ( !$status )
                                $failed_checks++;
                        }
                        break;

                    default:
                        break;

                }
            }

            // if all requirements are met und $always_return_result is false...
            if ( $failed_checks == 0 && $always_return_result === false )
                return true;

            // output summary table
            $summary       = array();
            $addons_header = false;
            foreach ( $msg as $check )
            {
                $style = $check['status'] ? 'color: #46882B;' : 'color: #C00;';
                foreach ( $check as $key => $value )
                {
                    $line = array();
                    if ( $key == 'status' )
                    {
                        continue;
                    }
                    $line[] = array(
                         'value' => $value
                    );
                }
                $summary[] = array_merge( $check, array(
                     'style' => $style
                ), $line );
            }

            $self = self::getInstance();
            $info = self::checkInfo($temp_path);

            $parser->setPath( CAT_PATH . '/templates/' . DEFAULT_TEMPLATE . '/' );
            $parser->setFallbackPath( dirname( __FILE__ ) . '/templates/Addons' );
            $output = $parser->get( 'summary', array(
                'addon' => $info['module_name'],
                'heading' => ( $failed_checks ? $self->lang()->translate( 'Pre installation check failed' ) : $self->lang()->translate( 'Pre installation check successful' ) ),
                'message' => ( $failed_checks ? $self->lang()->translate( 'Installation failed. Your system does not fulfill the defined requirements. Please fix the issues summarized below and try again.' ) : '' ),
                'summary' => $summary,
                'fail' => ( $failed_checks ? true : false )
            ) );


            if ( $delete_on_fail )
            {
                // delete the temp unzip directory
                CAT_Helper_Directory::removeDirectory( $temp_path );
                // delete the temporary zip file of the Add-on
                if ( file_exists( $temp_addon_file ) )
                    unlink( $temp_addon_file );
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
        public static function getVersion( $version, $strip_suffix = true )
        {
            // replace comma by decimal point
            $version = str_replace( ',', '.', $version );

            // convert version into major.minor.revision numbering system
            list( $major, $minor, $revision ) = explode( '.', $version, 3 );

            // convert versioning style 5.21 into 5.2.1
            if ( $revision == '' && strlen( intval( $minor ) ) == 2 )
            {
                $revision = substr( $minor, -1 );
                $minor    = substr( $minor, 0, 1 );
            }

            // extract possible non numerical suffix from revision part (e.g. Alpha, Beta, RC1)
            $suffix = strtoupper( trim( substr( $revision, strlen( intval( $revision ) ) ) ) );

            // return standard version number (minor and revision numbers may not exceed 999)
            return sprintf( '%d.%03d.%03d%s', (int) $major, (int) minor, (int) $revision, ( ( $strip_suffix == false && $suffix != '' ) ? '_' . $suffix : '' ) );

        } // end function getVersion()

        /**
         * removes/replaces known substrings in version string with their
         * weights
         *
         * @access public
         * @param  string  $version
         * @return string
         */
        public static function getVersion2( $version )
        {
            $version = strtolower( $version );

            foreach ( self::$states as $value => $keys )
            {
                $version = str_replace( $keys, $value, $version );
            }

            $version = str_replace( " ", "", $version );

            /**
             *	Force the version-string to get at least 4 terms.
             *	E.g. 2.7 will become 2.7.0.0
             */
            $temp_array = explode( ".", $version );
            $n          = count( $temp_array );
            if ( $n < 4 )
            {
                for ( $i = 0; $i < ( 4 - $n ); $i++ )
                    $version = $version . ".0";
            }
            // remove leading letters ('v1.2.3' => '1.2.3')
            $version = preg_replace('~^[a-z]+~i','',$version);
            return $version;
        } // end function getVersion2()

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
        public static function versionCompare( $version1, $version2, $operator = '>=' )
        {
            return version_compare( self::getVersion2( $version1 ), self::getVersion2( $version2 ), $operator );
        } // end versionCompare()


        /**
         * check module permissions for current user
         *
         * @access public
         * @param  string  $module - module to check
         * @return
         **/
        public static function checkModulePermissions( $module )
        {
            if ( CAT_Users::is_root() )
                return true;
            return CAT_Users::get_permission( $module, 'module' );
        } // end function checkModulePermissions()

        /**
         * This function is used to install an uploaded module
         *
         * @access public
         * @param  string  $tmpfile - name of the uploaded file
         * @param  string  $name    - name
         * @return
         **/
        public static function installUploaded( $tmpfile, $name )
        {

            $self = self::getInstance();
            $self->log()->LogDebug( sprintf( 'handle upload, file [%s], name [%s]', $tmpfile, $name ) );

            // Set temp vars
            $temp_dir   = CAT_PATH . '/temp/';
            $temp_unzip = $temp_dir . '/unzip_' . pathinfo( $tmpfile, PATHINFO_FILENAME ) . '/';
            $temp_file  = $temp_dir . $name;

            // Try to upload the file to the temp dir
            if ( !move_uploaded_file( $tmpfile, $temp_file ) )
            {
                CAT_Helper_Directory::removeDirectory( $temp_unzip );
                CAT_Helper_Directory::removeDirectory( $temp_file );
                self::printError( 'Unable to install. Cannot move uploaded file' );
                return false;
            }

            $self->log()->LogDebug( sprintf( 'uploaded file was moved to [%s], call installModule()', $temp_file ) );

            return self::installModule( $temp_file, false, true );

        } // end function installUploaded()


        /**
         * This function is used to install a module (addon); requires an
         * already existing ZIP file. Use installUploaded() to handle uploads.
         *
         * @access public
         * @param
         **/
        public static function installModule( $zipfile, $silent = false, $remove_zip_on_error = false )
        {
            // keep old modules happy
            global $wb, $admin, $database, $backend;
            if ( !is_object( $admin ) && is_object( $backend ) )
                $admin =& $backend;
            // keep old modules happy

            $self      = self::getInstance();
            $self->log()->LogDebug('installModule');

            $extension = pathinfo( $zipfile, PATHINFO_EXTENSION );
            $sourcedir = pathinfo( $zipfile, PATHINFO_DIRNAME );

            // Set temp vars
            $temp_dir   = CAT_PATH . '/temp/';
            $temp_unzip = $temp_dir . '/unzip_' . pathinfo( $zipfile, PATHINFO_FILENAME ) . '/';

            $self->log()->LogDebug( sprintf( 'file extension [%s], source dir [%s], remove zip [%s]', $extension, $sourcedir, $remove_zip_on_error ) );
            $self->log()->LogDebug( sprintf( 'temp dir [%s], unzip dir [%s]', $temp_dir, $temp_unzip ) );

            // Check for language or template/module
            if ( $extension == 'php' )
            {
                $temp_unzip = $zipfile;
            }
            elseif ( $extension == 'zip' )
            {
                $self->log()->LogDebug( sprintf( 'creating temp. unzip dir [%s]', $temp_unzip ) );
                CAT_Helper_Directory::createDirectory( $temp_unzip );

                $self->log()->LogDebug( sprintf( 'zip file [%s], output dir [%s]', $zipfile, $temp_unzip ) );

                // Setup the PclZip object and unzip the files to the temp unzip folder
                $list = CAT_Helper_Zip::getInstance( $zipfile )->config( 'Path', CAT_Helper_Directory::sanitizePath( $temp_unzip ) )->extract();

                // check if anything was extracted
                if ( !$list )
                {
                    $self->log()->LogDebug(sprintf('No $list from ZIP-Helper, removing [%s]', $temp_unzip));
                    CAT_Helper_Directory::removeDirectory( $temp_unzip );
                    if ( $remove_zip_on_error )
                        CAT_Helper_Directory::removeDirectory( $zipfile );
                    if ( !$silent )
                        self::printError( 'Unable to extract the file. Please check the ZIP format.' );
                    return false;
                }
                // check for info.php
                if ( !file_exists( $temp_unzip . '/info.php' ) )
                {
                    // check subfolders for info.php
                    $info = CAT_Helper_Directory::getInstance(1)
                            ->maxRecursionDepth(4)
                            ->findFile('info.php',$temp_unzip);
                    if ( !$info )
                    {
                        $self->log()->LogDebug(sprintf('No info.php found, removing [%s]', $temp_unzip));
                        CAT_Helper_Directory::removeDirectory( $temp_unzip );
                        if ( $remove_zip_on_error )
                            CAT_Helper_Directory::removeDirectory( $zipfile );
                        if ( !$silent )
                            self::printError( 'Invalid installation file. No info.php found. Please check the ZIP format.' );
                        return false;
                    }
                    else
                    {
                        $temp_infofile = pathinfo( $info, PATHINFO_DIRNAME );
                        $self->log()->LogDebug(sprintf('set $temp_infofile to [%s]', $temp_infofile));
                    }
                }
                else
                {
                    $temp_infofile = $temp_unzip;
                }
            }
            // unknown extension
            else
            {
                $self->log()->LogDebug(sprintf('Unknown extension [%s], "php" or "zip" expected, removing [%s]',$extension,$temp_unzip));
                CAT_Helper_Directory::removeDirectory( $temp_unzip );
                if ( $remove_zip_on_error )
                    CAT_Helper_Directory::removeDirectory( $zipfile );
                if ( !$silent )
                    self::printError( 'Invalid installation file. Wrong extension. Please check the ZIP format.' );
                return false;
            }

            // Check the info.php file / language file
            $precheck_errors = NULL;
            if ( $addon_info = self::checkInfo( $temp_infofile ) )
            {
                $precheck_errors = self::preCheckAddon( $zipfile, $temp_infofile, false );
            }
            else
            {
                $self->log()->LogDebug(sprintf('Unable to load info file [%s], removing [%s]',$temp_infofile,$temp_unzip));
                CAT_Helper_Directory::removeDirectory( $temp_unzip );
                if ( $remove_zip_on_error )
                    CAT_Helper_Directory::removeDirectory( $zipfile );
                if ( !$silent )
                {
                    self::printError( $self->lang()->translate( 'Invalid installation file. {{error}}', array(
                         'error' => $self->lang()->translate('Unable to find info.php')
                    ) ) );
                }
                return false;
            }

            // precheck failed
            if ( $precheck_errors != '' && !is_bool( $precheck_errors ) )
            {
                $self->log()->LogDebug(sprintf('Pre-installation check(s) failed, removing [%s]',$temp_unzip));
                CAT_Helper_Directory::removeDirectory( $temp_unzip );
                if ( !$silent )
                    self::printError( $precheck_errors, $_SERVER['SCRIPT_NAME'], false );
                return false;
            }

            // So, now we have done all preinstall checks, lets see what to do next
            $addon_directory
                = $addon_info['addon_function'] == 'language'
                ? $addon_info['module_code'] . '.php'
                : $addon_info['module_directory'];

            // Set module directory
            $addon_dir = CAT_PATH . '/' . $addon_info['addon_function'] . 's/' . $addon_directory;
            $action    = 'install';

            if ( file_exists( $addon_dir ) && $addon_info['addon_function'] != 'language' )
            {
                $action        = 'upgrade';
                // look for old info.php
                $previous_info = self::checkInfo( $addon_dir );
                if ( $previous_info )
                {
                    // compare versions
                    if ( self::versionCompare( $previous_info['module_version'], $addon_info['module_version'], '>=' ) )
                    {
                        $self->log()->LogDebug(sprintf('Version check found no difference between installed and uploaded version, removing [%s]', $temp_unzip));
                        CAT_Helper_Directory::removeDirectory( $temp_unzip );
                        if ( $remove_zip_on_error )
                            CAT_Helper_Directory::removeDirectory( $zipfile );
                        if ( !$silent )
                            self::printError( 'Same or newer version already installed' );
                        else
                            self::$error = 'Same or newer version already installed (installed: '.$previous_info['module_version'].'; to install: '.$addon_info['module_version'].')';
                        return false;
                    }
                }
            }

            // Make sure the module dir exists, and chmod if needed
            if ( $addon_info['addon_function'] != 'language' )
            {
                $self->log()->LogDebug(sprintf('Creating addon directory [%s]', $addon_dir));
                CAT_Helper_Directory::createDirectory( $addon_dir );
                // copy files from temp folder
                // we use $temp_infofile here as source as it is the folder the
                // info.php file resides
                if ( CAT_Helper_Directory::copyRecursive( $temp_infofile, $addon_dir ) !== true )
                {
                    $self->log()->LogDebug(sprintf('Copy failed, removing [%s]',$temp_unzip));
                    CAT_Helper_Directory::removeDirectory( $temp_unzip );
                    if ( $remove_zip_on_error )
                        CAT_Helper_Directory::removeDirectory( $zipfile );
                    if ( !$silent )
                        self::printError( 'Unable to install - error copying files' );
                    return false;
                }
                // remove temp
                $self->log()->LogDebug(sprintf('removing [%s]',$temp_unzip));
                CAT_Helper_Directory::removeDirectory( $temp_unzip );
                if ( $remove_zip_on_error )
                    CAT_Helper_Directory::removeDirectory( $zipfile );
            }

            // load the module info into the database
            if ( !self::loadModuleIntoDB( $addon_dir, $action, self::checkInfo( $addon_dir ) ) )
            {
                $self->log()->LogDebug(sprintf('Loading module into DB failed, removing [%s]',$temp_unzip));
                CAT_Helper_Directory::removeDirectory( $temp_unzip );
                CAT_Helper_Directory::removeDirectory( $addon_dir );
                if ( !$silent )
                    self::printError( $self->db()->getError() );
                return false;
            }

            // Run the modules install // upgrade script if there is one
            if ( file_exists( $addon_dir . '/' . $action . '.php' ) )
            {
                $self->log()->LogDebug(sprintf('Running [%s]',$addon_dir.'/'.$action.'.php'));
                require( $addon_dir . '/' . $action . '.php' );
            }

            if ( $action == 'install' && $addon_info['addon_function'] == 'language' )
            {
                $target = CAT_Helper_Directory::sanitizePath( $addon_dir );
                // for manual install...
                if ( $zipfile !== $target )
                {
                    rename( $zipfile, $addon_directory );
                    CAT_Helper_Directory::setPerms( $addon_directory );
                }
            }

            // set module permissions
            if ( ( $addon_info['addon_function'] == 'module' && ( $addon_info['module_function'] == 'page' || $addon_info['module_function'] == 'tool' ) ) || $addon_info['addon_function'] == 'template' )
            {
                self::setModulePermissions( $addon_info );
            }

            return true;

        } // end function installModule()

        /**
         *
         * @access public
         * @return
         **/
        public static function uninstallModule( $type, $addon_name )
        {
            // keep old modules happy
            global $wb, $admin, $database;

            switch ( $type )
            {
                case 'languages':
                    // is default or used by current user
                    if ( $addon_name == DEFAULT_LANGUAGE || $addon_name == LANGUAGE )
                    {
                        $temp = array(
                            'name' => $addon_name,
                            'type' => $addon_name == DEFAULT_LANGUAGE ? self::getInstance()->lang()->translate( 'standard language' ) : self::getInstance()->lang()->translate( 'current language' )
                        );
                        return self::getInstance()->lang()->translate( 'Cannot uninstall this language <span class="highlight_text">{{name}}</span> because it is the {{type}}!', $temp );
                    }
                    // used by other users
                    $query_users = self::getInstance()->db()->query(
                        "SELECT `user_id` FROM `:prefix:users` WHERE language=:lang LIMIT 1",
                        array('lang'=>$addon_name)
                    );
                    if ( $query_users->rowCount() > 0 )
                    {
                        return self::getInstance()->lang()->translate( 'Cannot uninstall this language <span class="highlight_text">{{name}}</span> because it is in use!', array(
                             'name' => $addon_name
                        ) );
                    }
                    break;

                case 'modules':
                    // check if the module is still in use
                    $usage  = self::getModuleUsage($addon_name);
                    if(count($usage))
                    {
                        $temp   = explode( ";", self::getInstance()->lang()->translate( 'this page;these pages' ) );
                        $add    = count($usage) == 1 ? $temp[0] : $temp[1];
                        $values = array(
                            'type' => self::getInstance()->lang()->translate( 'Module' ),
                            'type_name' => $type,
                            'pages_string' => $add,
                            'count'        => count($usage),
                            'name' => $addon_name
                        );
                        $pages  = array();
                        foreach($usage as $i => $data)
                        {
                            $pages[] = sprintf( '<a href="%s">%s</a>', $data['page_link'], $data['menu_title'] );
                        }
                        $values['pages'] = implode( '<br />', $pages );
                        return self::getInstance()->lang()->translate( 'Cannot uninstall module <span class="highlight_text">{{name}}</span> because it is in use on {{pages_string}}:<br /><br />{{pages}}', $values );
                    }
                    //  some modules cannot be removed (used by system)
                    if ( !self::isRemovable( $addon_name ) )
                        return self::getInstance()->lang()->translate( 'Cannot uninstall module <span class="highlight_text">{{name}}</span> because it is marked as mandatory!', array(
                             'name' => $addon_name
                        ) );
                    if ( ( defined( 'WYSIWYG_EDITOR' ) ) && ( $addon_name == WYSIWYG_EDITOR ) )
                    {
                        return self::getInstance()->lang()->translate( 'Cannot uninstall module <span class="highlight_text">{{name}}</span> because it is the standard WYSWIWYG editor!', array(
                             'name' => $addon_name
                        ) );
                    }
                    break;

                case 'templates':
                    if ( $addon_name == DEFAULT_THEME || $addon_name == DEFAULT_TEMPLATE )
                    {
                        $temp = array(
                            'name' => $addon_name,
                            'type' => $addon_name == DEFAULT_TEMPLATE ? self::getInstance()->lang()->translate( 'default template' ) : self::getInstance()->lang()->translate( 'default backend theme' )
                        );
                        return self::getInstance()->lang()->translate( 'Cannot uninstall template <span class="highlight_text">{{name}}</span> because it is the {{type}}!', $temp );
                    }
                    $info = self::getInstance()->db()->query(
                        "SELECT `page_id`, `page_title` FROM `:prefix:pages` WHERE template=:name order by page_title",
                        array('name'=>$addon_name)
                    );
                    if ( $info->rowCount() > 0 )
                    {
                        $msg_template_str  = 'Cannot uninstall template <span class="highlight_text">{{name}}</span> because it is still in use on {{pages}}:';
                        $temp              = explode( ';', self::getInstance()->lang()->translate( 'this page;these pages' ) );
                        $add               = $info->rowCount() == 1 ? $temp[ 0 ] : $temp[ 1 ];
                        $page_template_str = "<li><a href='../pages/settings.php?page_id={{id}}'>{{title}}</a></li>";

                        $values = array(
                            'pages' => $add,
                            'name' => $addon_name
                        );
                        $msg    = self::getInstance()->lang()->translate( $msg_template_str, $values );

                        $page_names = '<ul>';
                        while ( $data = $info->fetchRow() )
                        {
                            $page_info = array(
                                'id' => $data['page_id'],
                                'title' => $data['page_title']
                            );
                            $page_names .= self::getInstance()->lang()->translate( $page_template_str, $page_info );
                        }
                        $page_names .= '</ul>';
                        return $msg . $page_names;
                    }
                    break;

                default:
                    break;
            } // end switch

            // all checks succeeded, try to uninstall
            if ( file_exists( CAT_PATH . '/' . $type . '/' . $addon_name . '/uninstall.php' ) )
                require CAT_PATH . '/' . $type . '/' . $addon_name . '/uninstall.php';

            // Remove entry from DB
            if ( $type != 'languages' )
            {
                self::getInstance()->db()->query(
                    "DELETE FROM `:prefix:addons` WHERE directory=:dir AND type=:type",
                    array('dir'=>$addon_name, 'type'=>substr($type,0,-1) )
                );
                if ( self::getInstance()->db()->isError() )
                    return self::getInstance()->db()->getError();
                $stmt = self::getInstance()->db()->query(
                    'SELECT * FROM `:prefix:groups` WHERE group_id <> 1'
                );
                if ( $stmt->rowCount() > 0 )
                {
                    while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) )
                    {
                        $gid         = $row['group_id'];
                        $file        = $addon_name;
                        // get current value
                        $permissions = explode( ',', $row[ substr( $type, 0, -1 ) . '_permissions'] );
                        // remove uninstalled module
                        if ( in_array( $file, $permissions ) )
                        {
                            $i = array_search( $file, $permissions );
                            array_splice( $permissions, $i, 1 );
                            $permissions = array_unique( $permissions );
                            asort( $permissions );
                            // Update the database
                            $addon_permissions = implode( ',', $permissions );
                            self::getInstance()->db()->query(
                                sprintf(
                                    "UPDATE `:prefix:groups` SET %s_permissions=:perm WHERE group_id=:id",
                                    substr( $type, 0, -1 )
                                ),
                                array('perm'=>$addon_permissions,'id'=>$gid)
                            );
                        }
                    }
                }
                // Try to delete the module dir
                $mod_dir = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/'.$type.'/'.$addon_name);
                if (
                       !CAT_Helper_Directory::removeDirectory($mod_dir)
                    && is_dir($mod_dir)
                ) {
                    return self::getInstance()->lang()->translate( 'Cannot uninstall - unable to delete the directory!' );
                }
            }
            else
            {
                self::getInstance()->db()->query(
                    "DELETE FROM `:prefix:addons` WHERE directory=:dir AND type=:type",
                    array('dir'=>$addon_name, 'type'=>substr($type,0,-1) )
                );
                if ( self::getInstance()->db()->isError() )
                    return self::getInstance()->db()->getError();
                unlink( CAT_PATH . '/languages/' . $addon_name . '.php' );
            }

            return true;
        } // end function uninstallModule()

        /**
         * loads the module data into the database
         *
         * @access public
         * @return
         **/
        public static function loadModuleIntoDB( $addon_dir, $action, $addon_info = array() )
        {

            $self = self::getInstance();
            $self->log()->logDebug( sprintf( 'addon dir [%s], action [%s]', $addon_dir, $action ) );

            // load info.php again to have current values
            if ( !count( $addon_info ) && file_exists( $addon_dir . '/info.php' ) )
                $addon_info = self::checkInfo( $addon_dir );

            $self->log()->logDebug( 'addon info:', $addon_info );

            if ( $action == 'install' )
            {
                if ( isset( $addon_info['module_name'] ) )
                {

                    if ( !isset( $addon_info['module_function'] ) )
                        $addon_info['module_function'] = $addon_info['addon_function'];

                    $options = array(
                        'time' => time(),
                        'dir'  => $addon_info['module_directory'],
                        'name' => $addon_info['module_name'],
                        'desc' => $addon_info['module_description'],
                        'type' => $addon_info['addon_function'],
                        'func' => strtolower( $addon_info['module_function'] ),
                        'ver'  => $addon_info['module_version'],
                        'plat' => ( isset( $addon_info['module_platform'] ) ? $addon_info['module_platform'] : '' ),
                        'auth' => ( isset( $addon_info['module_author']   ) ? $addon_info['module_author']   : '' ),
                        'lic'  => ( isset( $addon_info['module_license']  ) ? $addon_info['module_license']  : '' ),
                        'guid' => ( isset( $addon_info['module_guid']     ) ? $addon_info['module_guid']     : '' ),
                    );

                    $module_function = strtolower( $addon_info['module_function'] );
                    $do              = 'insert';
                    // Check that it doesn't already exist
                    $q = $self->db()->query(
                        "SELECT COUNT(*) FROM `:prefix:addons` WHERE `type`='module' AND `directory`=:dir AND `function`=:func",
                        array('dir'=>$addon_info['module_directory'], 'func'=>$module_function)
                    );
                    if ( $q->rowCount() )
                        $cnt = $q->fetchColumn();
                    if($cnt > 0 )
                    {
                        $sql = "UPDATE `:prefix:addons` SET `upgraded`=:time, "
                             . "`directory`=:dir, `name`=:name, `description`=:desc, "
                             . "`type`=:type, `function`=:func, `version`=:ver, "
                             . "`platform`=:plat, `author`=:auth, `license`=:lic, `guid`=:guid"
                             ;
                        $do  = 'update';
                    }
                    else
                    {
                        $sql = "INSERT INTO `:prefix:addons` VALUES "
                             . "( NULL, :type, :dir, :name, :desc, :func, :ver, :guid, :plat, :auth, :lic, :insttime, :time, :removable, :bundled )";
                        $options['insttime'] = time();
                        $options['removable'] = 'Y';
                        $options['bundled'] = 'N';
                    }

                    if ( $do == 'update' )
                    {
                        $sql .= " WHERE `type`='module' AND `directory`=:dir2";
                        $options['dir2'] = $addon_info['module_directory'];
                    }

                    $self->db()->query($sql,$options);

                    if ( $self->db()->isError() )
                        return false;
                }
                else
                {
                    $self->log()->logWarn( sprintf( 'Unable to add module in dir [%s] to database, missing attr module_name!', $addon_dir ) );
                }
            }

            if ( $action == 'upgrade' && isset($addon_info['module_name']) )
            {
                $sql = "UPDATE `:prefix:addons` SET `upgraded`=:time, `version`=:ver WHERE `directory`=:dir";
                $self->db()->query( $sql, array('time' => time(), 'ver' => $addon_info['module_version'], 'dir' => $addon_info['module_directory'] ) );
            }

            return true;
        } // end function loadModuleIntoDB()

        /**
         * let admin set access permissions for modules of type 'page' and 'tool'
         *
         * @access public
         * @return
         **/
        public static function setModulePermissions( $addon_info )
        {

            $self = self::getInstance();

            $check_permission = $addon_info['addon_function'] . '_permissions';

            // get groups
            $stmt = $self->db()->query('SELECT * FROM `:prefix:groups` WHERE group_id <> 1');

            if ( $stmt->rowCount() > 0 )
            {

                $group_ids      = CAT_Helper_Validate::sanitizePost( 'group_id' );
                $allowed_groups = array();

                // get marked groups
                if ( is_array( $group_ids ) )
                    foreach ( $group_ids as $gid )
                        $allowed_groups[ $gid ] = $gid;

                // get all known groups
                $groups = array();
                while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) )
                {
                    $groups[ $row['group_id'] ] = $row;
                    $gid                          = $row['group_id'];
                    // add newly installed module to any group that's NOT in the $allowed_groups array
                    if ( !array_key_exists( $gid, $allowed_groups ) )
                    {
                        // get current value
                        $addons   = explode( ',', $groups[ $gid ][ $check_permission ] );
                        // add newly installed module
                        $addons[] = $addon_info['module_directory'];
                        $addons   = array_unique( $addons );
                        asort( $addons );
                        // Update the database
                        $addon_permissions = implode( ',', $addons );
                        $self->db()->query(
                            sprintf(
                                'UPDATE `:prefix:groups` SET `%s`=:val WHERE `group_id`=:id',
                                $check_permission
                            ),
                            array('val'=>$addon_permissions, 'id'=>$gid)
                        );
                        if ( $self->db()->isError() )
                        {
                            self::printError( $self->db()->getError() );
                            return false;
                        }
                    }
                }
                return true;
            }
            else
            {
                return true;
            }

        } // end function setModulePermissions()

        /**
         * gets the sections and pages a module is used on
         *
         * @access public
         * @param  string  $addon_name
         * @return
         **/
        public static function getModuleUsage($addon_name)
        {
            $info = self::getInstance()->db()->query(
                "SELECT `section_id`, `page_id` FROM `:prefix:sections` WHERE module=:mod",
                array('mod'=>$addon_name)
            );
            if ( $info->rowCount() > 0 )
            {
                $usage = $info->fetchAll(PDO::FETCH_ASSOC);
                for($i=count($usage)-1; $i>=0; $i--)
                {
                    // skip negative page id's
                    if ( substr( $usage[$i]['page_id'], 0, 1 ) == '-' )
                    {
                        unset($usage[$i]);
                    }
                    else
                    {
                        $usage[$i]['page_link']  = CAT_Helper_Page::getLink($usage[$i]['page_id']);
                        $usage[$i]['menu_title'] = CAT_Helper_Page::properties($usage[$i]['page_id'],'menu_title');
                    }
                }
                return $usage;
            }
            return array();
        }   // end function getModuleUsage()
        

        /**
         *  Try to get the current version of a given Modul.
         *
         *  @param  string   $modulename - module directory name
         *  @param  boolean  $source     - true reads from database, false from info.php
         *  @return string   the version as string, if not found returns null
         *
         */
        public static function getModuleVersion( $modulename, $source = true )
        {
            global $database;
            $version = null;
            $self    = self::getInstance();
            if ( $source != true )
            {
                $sql     = "SELECT `version` FROM `:prefix:addons` WHERE `directory`=:dir";
                $version = $self->db()->query($sql,array('dir'=>$modulename))->fetchColumn();
            }
            else
            {
                $info_file = CAT_PATH . '/modules/' . $modulename . '/info.php';
                if ( file_exists( $info_file ) )
                {
                    $module_version = null;
                    require( $info_file );
                    $version =& $module_version;
                }
            }
            return $version;
        } // end function getModuleVersion()


        /**
         * This function is used to upgrade a module (addon); function was moved
         * from functions.php
         *
         * @access public
         * @param  string  $directory - module path
         * @param  boolean $upgrade   - default is 'false'
         **/
        public static function upgradeModule( $directory, $upgrade = false )
        {
            global $database, $admin;
            global $module_license, $module_author, $module_name, $module_directory,
                   $module_version, $module_function, $module_guid, $module_description, $module_platform;
            $fields = array(
                'version'     => $module_version,
                'description' => $module_description,
                'platform'    => $module_platform,
                'author'      => $module_author,
                'license'     => $module_license,
                'guid'        => $module_guid,
                'dir'         => $module_directory,
            );
            $sql    = 'UPDATE `:prefix:addons` SET ';
            foreach ( $fields as $key => $value )
                $sql .= "`$key`=:$key,";
            $sql = substr($sql,0,-1) . " WHERE `directory`=:dir";

            $self = self::getInstance();
            $self->db()->query($sql,$fields);

            if ( $self->db()->isError() )
            {
                $admin->print_error( $self->db()->getError() );
                self::getInstance()->log()->logDebug( 'database error: ' . $self->db()->getError() );
            }
        } // end function upgradeModule()

        /**
         * checks if a module is installed
         *
         * @access public
         * @param  string  $module  - module name or directory name
         * @param  string  $version - (optional) version to check (>=)
         * @param  string  $type    - default 'module'
         * @return boolean
         **/
        public static function isModuleInstalled( $module, $version = NULL, $type = 'module' )
        {
            $self = self::getInstance();
            $q    = $self->db()->query(
                'SELECT * FROM `:prefix:addons` WHERE type=:type AND ( directory=:dir OR name=:name )',
                array('type'=>$type, 'dir'=>$module, 'name'=>$module)
            );
            if ( !is_object($q) || !$q->rowCount() )
                return false;

            // note: if there's more than one, the first match will be returned!
            while ( $addon = $q->fetchRow() )
            {
                if ( $version && self::versionCompare( $addon['version'], $version ) )
                    return true;

                // name over directory
                if ( $addon['name'] == $module )
                    return true;

                if ( $addon['directory'] == $module )
                    return true;

            }
            return false;
        } // end function isModuleInstalled()

        /**
         *
         * @access public
         * @return
         **/
        public static function isRemovable( $module )
        {
            $self = self::getInstance();
            $q    = $self->db()->query(
                'SELECT * FROM `:prefix:addons` WHERE type=:type AND ( directory=:dir OR name=:name ) LIMIT 1',
                array('type'=>"module", 'dir'=>$module, 'name'=>$module)
            );
            if ( !$q->rowCount() )
                return false;
            $row = $q->fetchRow();
            if ( $row['removable'] != 'Y' )
                return false;
            return true;
        } // end function isRemovable()


        /**
         *
         * @access public
         * @return
         **/
        public static function isTemplateInstalled( $module, $version = NULL )
        {
            return self::isModuleInstalled( $module, $version, 'template' );
        } // end function isTemplateInstalled()



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
        public static function sec_register_file( $module, $filepath )
        {
            global $admin;
            if ( !CAT_Backend::isBackend() && !is_object( $admin ) && !defined( 'CAT_INSTALL' ) )
            {
                self::getInstance()->log()->logCrit( "sec_register_file() called outside admin context!" );
                self::$error = "sec_register_file() called outside admin context!";
                return false;
            }
            // check permissions
            if ( !CAT_Users::checkPermission( 'Addons', 'modules_install' ) && !defined( 'CAT_INSTALL' ) )
            {
                self::getInstance()->log()->logCrit( "sec_register_file() called without modules_install perms!" );
                self::$error = "sec_register_file() called without modules_install perms!";
                return false;
            }
            // this will remove ../.. from $filepath
            $filepath = CAT_Helper_Directory::sanitizePath( $filepath );
            if ( !is_dir( CAT_PATH . '/modules/' . $module ) )
            {
                self::getInstance()->log()->logCrit( "sec_register_file() called for non existing module [$module] (path: [$filepath])" );
                self::$error = "sec_register_file() called for non existing module [$module] (path: [$filepath])";
                return false;
            }
            if ( !file_exists( CAT_Helper_Directory::sanitizePath( CAT_PATH . '/modules/' . $module . '/' . $filepath ) ) )
            {
                self::getInstance()->log()->logCrit( "sec_register_file() called for non existing file [$filepath] (module: [$module])" );
                self::$error = "sec_register_file() called for non existing file [$filepath] (module: [$module])";
                return false;
            }
            $self = self::getInstance();
            $q    = $self->db()->query(
                'SELECT * FROM `:prefix:addons` WHERE directory=:dir',
                array('dir'=>$module)
            );
            if ( !$q->rowCount() )
            {
                self::getInstance()->log()->logCrit( "sec_register_file() called for non existing module [$module] (path: [$filepath]) - not found in addons table!" );
                self::$error = "sec_register_file() called for non existing module [$module] (path: [$filepath]) - not found in addons table!";
                return false;
            }
            $row      = $q->fetchRow();
            // remove trailing / from $filepath
            $filepath = preg_replace( '~^/~', '', $filepath );
            $q        = $self->db()->query(
                'SELECT * FROM `:prefix:class_secure` WHERE module=:mod AND filepath=:path',
                array('mod'=> $row['addon_id'], 'path'=>'/modules/'.$module.'/'.$filepath)
            );
            if ( !$q->rowCount() )
            {
                $self->db()->query(
                    'REPLACE INTO `:prefix:class_secure` VALUES ( :id, :path )',
                    array('id'=> $row['addon_id'], 'path'=> '/modules/'.$module.'/'.$filepath )
                );
                return ( $self->db()->isError() ? false : true );
            }
            return true;
        } // end function sec_register_file()

        /**
         * This function is used to check info.php
         * Also used for language files
         *
         * @access public
         * @param  string  Any valid directory(-path)
         **/
        public static function checkInfo( $directory )
        {
            $self = self::getInstance();
            $self->log()->LogDebug(sprintf('checking info.php for $directory [%s]',$directory));
            if ( is_dir( $directory ) && file_exists( $directory . '/info.php' ) )
            {
                $self->log()->LogDebug('$directory is a directory and info.php found');
                // get header info
                $link = NULL;
                ini_set( 'auto_detect_line_endings', true );
                $file = fopen( $directory . '/info.php', 'r' );
                if ( $file )
                {
                    while ( $line = fgets( $file ) )
                    {
                        if ( preg_match( '/\@link\s+(.*)/i', $line, $matches ) )
                        {
                            $link = trim( $matches[ 1 ] );
                            break;
                        }
                    }
                    fclose( $file );
                }

                require( $directory . '/info.php' );

                if ( isset( $module_function ) && in_array( strtolower( $module_function ), self::$module_functions ) )
                {
                    $return_values = array(
                         'addon_function' => 'module'
                    );
                }
                else if ( isset( $template_function ) && in_array( strtolower( $template_function ), self::$template_functions ) )
                {
                    $return_values = array(
                         'addon_function' => 'template'
                    );
                }
                else
                {
                    self::$error = 'Invalid info.php - neither $module_function nor $template_function set';
                    $self->log()->logDebug( self::$error );
                    return false;
                }
                // Check if the file is valid
                foreach ( self::$info_vars_mandatory[ $return_values['addon_function'] ] as $varname )
                {
                    if ( !isset( ${$varname} ) )
                    {
                        self::$error = 'Invalid info.php - mandatory var ' . $varname . ' not set';
                        $self->log()->logDebug( self::$error );
                        return false;
                    }
                    else
                    {
                        // rename keys
                        $key                   = str_ireplace( array(
                             'template_'
                        ), array(
                             'module_'
                        ), $varname );
                        $return_values[ $key ] = ${$varname};
                    }
                }
                // add empty keys
                foreach ( self::$info_vars_full[ $return_values['addon_function'] ] as $varname )
                {
                    $key = str_ireplace( array(
                         'template_'
                    ), array(
                         'module_'
                    ), $varname );
                    if ( !isset( $returnvalues[ $key ] ) )
                    {
                        $return_values[ $key ] = isset( ${$varname} ) ? ${$varname} : '';
                    }
                }
                // check platform (WB/LEPTON/BC)
                if(isset($lepton_platform)&&!isset($module_platform))
                {
                    $return_values['cms_name'] = 'LEPTON';
                }
                if(isset($module_platform))
                {
                    if(!self::versionCompare($module_platform,'2.x','<='))
                        $return_values['cms_name'] = 'WebsiteBaker';
                    else
                        $return_values['cms_name'] = 'BlackCat CMS';
                }
                if(!isset($return_values['cms_name']))
                    $return_values['cms_name'] = 'unknown';
                // link to module homepage
                if ( $link )
                    $return_values['module_link'] = $link;
                return $return_values;
            }
            elseif ( file_exists( $directory ) && pathinfo( $directory, PATHINFO_EXTENSION ) == 'php' )
            {
                $self->log()->LogDebug('$directory is a file and has "php" suffix');
                // Check if the file is valid
                $content = file_get_contents( $directory );
                if ( strpos( $content, '<?php' ) === false )
                {
                    self::$error = 'Invalid language file - missing PHP delimiter';
                    $self->log()->logDebug( self::$error );
                    return false;
                }

                $return_values = array(
                     'addon_function' => 'language',
                    'module_directory' => pathinfo( $directory, PATHINFO_FILENAME )
                );
                require( $directory );

                foreach ( self::$info_vars_mandatory['language'] as $varname )
                {
                    if ( !isset( ${$varname} ) )
                    {
                        self::$error = 'Invalid language file - var ' . $varname . ' not set';
                        $self->log()->logDebug( self::$error );
                        return false;
                    }
                    else
                    {
                        // rename keys
                        $key                   = str_ireplace( array(
                             'language_'
                        ), array(
                             'module_'
                        ), $varname );
                        $return_values[ $key ] = ${$varname};
                    }
                }
                $return_values['module_description'] = $language_name;
                return $return_values;
            }
            else
            {
                self::$error = 'invalid directory/language file or info.php is missing, check of language file failed';
                $self->log()->logDebug( self::$error );
                return false;
            }
        } // end function checkInfo()

        /**
         * returns the last error
         *
         * @access public
         * @return string
         **/
        public static function getError()
        {
            return self::getInstance()->lang()->translate( self::$error );
        }   // end function getError()

        /**
         * find available libraries; path names must begin with 'lib_'
         *
         *
         *
         **/
        public static function getLibraries( $type = NULL )
        {
            $dir  = CAT_Helper_Directory::sanitizePath( CAT_PATH . '/modules' );
            $libs = array();
            if ( $handle = opendir( $dir ) )
            {
                while ( false !== ( $file = readdir( $handle ) ) )
                {
                    if ( $file != "." && $file != ".." )
                    {
                        if ( is_dir( $dir . '/' . $file ) && preg_match( '#^lib_#', $file ) && file_exists( $dir . '/' . $file . '/info.php' ) )
                        {
                            $module_directory = $module_name = $library_function = NULL;
                            include $dir . '/' . $file . '/info.php';
                            if ( $type !== NULL && $library_function === NULL )
                            {
                                continue;
                            }
                            if ( $type !== NULL && $library_function !== $type )
                            {
                                continue;
                            }
                            $libs[] = array(
                                'name' => $module_name,
                                'dir' => $module_directory,
                                'function' => $library_function
                            );
                        }
                    }
                }
                closedir( $handle );
            }
            return $libs;
        } // end function getLibraries()

// -----------------------------------------------------------------------------
// methods moved from module.functions.php
// -----------------------------------------------------------------------------

        /**
         * old name: edit_module_css()
         *
         * generates a form button (for both backend.css and frontend.css if
         * available) to call the 'Edit CSS' functionality based on page_id,
         * section_id and given mod_dir
         *
         * if $print is false (default), the result is returned, printed
         * (=echoed) otherwise
         *
         * @access public
         * @param  string  $mod_dir
         * @param  boolean $print
         * @return mixed
         **/
    	public static function getEditModuleCSSForm($mod_dir,$print=false)
        {
    		global $page_id, $section_id;
    		if(!file_exists(CAT_PATH.'/backend/addons/edit_module_files.php')) return;
    		if(!file_exists(CAT_PATH.'/modules/'.$mod_dir.'/info.php'))        return;
            if(!self::checkModulePermissions($mod_dir))                        return;
            $buttons = array();
            $content = '';
            foreach(array('frontend.css','backend.css') as $file)
                if(file_exists(CAT_PATH.'/modules/'.$mod_dir.'/'.$file))
                    $buttons[] = $file;
    		if(count($buttons))
            {
                foreach($buttons as $button)
                {
                    $content .= '
        			<form class="edit_module_file" name="edit_module_file" action="'.CAT_URL.'/modules/edit_module_files.php" method="post">
        				<input type="hidden" name="page_id" value="'.$page_id.'" />
        				<input type="hidden" name="section_id" value="'.$section_id.'" />
        				<input type="hidden" name="mod_dir" value="'.$mod_dir.'" />
        				<input type="hidden" name="edit_file" value="'.$button.'" />
        				<input type="hidden" name="action" value="edit" />
        				<button type="submit" class="edit_module_file mod_'.$mod_dir.'_edit_css">'.
                        self::getInstance()->lang()->translate('Edit').' '.$button.
                        '</button>
        			</form>
                    ';
                }
                if($content)
                    $content .= '<br style="clear:left;" />';
                if($print)
                    echo $content;
                else
                    return $content;
            }
        }   // end function getEditModuleCSSForm()

        /**
         *
         * @access public
         * @return
         **/
        public static function getEditModuleFilesButton($mod_dir,$print=false)
        {
            global $page_id, $section_id;
            $self    = self::getInstance();
            if(!$self->checkModulePermissions($mod_dir)) return;
            $content = '';
            $path    = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/'.$mod_dir);
            // find JS files
            $js      = CAT_Helper_Directory::getInstance()
                       ->maxRecursionDepth(5)
                       ->setSuffixFilter(array('js'))
                       ->scanDirectory($path,true,true,$path);
            // find CSS files
            $css     = CAT_Helper_Directory::getInstance()
                       ->maxRecursionDepth(5)
                       ->setSuffixFilter(array('css'))
                       ->scanDirectory($path,true,true,$path);
            if(count($js) || count($css))
                $content = '
                    <form style="float:right" method="post" action="'.CAT_URL.'/backend/addons/edit_module_files.php" name="edit_module_file">
                		<input type="hidden" value="'.$page_id.'" name="page_id">
                		<input type="hidden" value="'.$section_id.'" name="section_id">
                		<input type="hidden" value="'.$mod_dir.'" name="mod_dir">
                		<input type="submit" value="'.$self->lang()->translate('Edit module file(s)').'">
                	</form>';
            if($print)
                echo $content;
            else
                return $content;
        }   // end function getEditModuleFilesButton()
        

// -----------------------------------------------------------------------------
// private methods
// -----------------------------------------------------------------------------
        /**
         *
         * @access private
         * @return
         **/
        private static function checkAddons( $addons )
        {
            if ( is_array( $addons ) )
            {
                $self = self::getInstance();
                foreach ( $addons as $addon => $values )
                {
                    if ( is_array( $values ) )
                    {
                        // extract module version and operator
                        $version  = ( isset( $values['VERSION'] )  && trim( $values['VERSION'] )  != '' ) ? $values['VERSION']  : '';
                        $operator = ( isset( $values['OPERATOR'] ) && trim( $values['OPERATOR'] ) != '' ) ? $values['OPERATOR'] : '>=';
                    }
                    else
                    {
                        // no version and operator specified (only check if addon exists)
                        $addon    = strip_tags( $values );
                        $version  = '';
                        $operator = '';
                    }

                    // defaults
                    $inst_version = NULL;
                    $status       = false;
                    $addon_status = $self->lang()->translate( 'Not installed' );

                    // check if addon is installed
                    if ( self::isModuleInstalled( $addon ) )
                    {
                        $inst_version = self::getModuleVersion( $addon );
                        $status       = true;
                        $addon_status = $self->lang()->translate( 'Installed' );
                        // compare version if required
                        if ( $version != '' )
                        {
                            $status       = self::versionCompare( $inst_version, $version, $operator );
                            $addon_status = $inst_version;
                        }
                    }

                    // provide addon status
                    $msg[] = array(
                        'key'      => 'ADDONS',
                        'check'    => '&nbsp;&nbsp;&nbsp; ' . htmlentities( $addon ),
                        'required' => ( $version != '' ) ? $operator . '&nbsp;' . $version : $self->lang()->translate( 'installed' ),
                        'actual'   => $addon_status,
                        'status'   => $status
                    );
                }
                return array(
                    $status,
                    $msg
                );
            }
            return array(
                 true,
                ''
            );
        } // end function checkAddons()


        /**
         *
         * @access private
         * @param  array   $value -> 'VERSION' => x, 'OPERATOR' => y
         * @return
         **/
        private static function checkCMSVersion( $key, $value )
        {
            $check_version = $value['VERSION'];
            switch ( $key )
            {
                case 'WB_VERSION': // we support WB 2.8.3
                    $this_version = '2.8.3';
                    break;
                case 'LEPTON_VERSION': // we support LEPTON 1.x
                    $this_version = '1.2';
                    break;
                default:
                    $this_version = CAT_Registry::get( 'CAT_VERSION' );
                    break;
            }
            // obtain operator for string comparison if exist
            $operator = ( isset( $value['OPERATOR'] ) && trim( $value['OPERATOR'] ) != '' ) ? $value['OPERATOR'] : '>=';
            // compare versions and extract actual status
            $status   = self::versionCompare( $this_version, $value['VERSION'], $operator );
            $msg      = array(
                'check' => sprintf( 'CMS-%s: ', self::getInstance()->lang()->translate( 'Version' ) ),
                'required' => sprintf( '%s %s', htmlentities( $operator ), $value['VERSION'] ),
                'actual' => $this_version,
                'status' => $status
            );
            return array(
                $status,
                $msg
            );
        } // end function checkCMSVersion()


        /**
         * sort the $PRECHECK array by keys
         *
         * @access private
         * @param  array
         * @return array
         *
         **/
        private static function sortPreCheckArray( $precheck_array )
        {
            // define desired precheck order
            $key_order = array(
                'CAT_VERSION',
                'LEPTON_VERSION',
                'WB_VERSION',
                'CAT_ADDONS',
                'WB_ADDONS',
                'PHP_VERSION',
                'PHP_EXTENSIONS',
                'PHP_SETTINGS',
                'CUSTOM_CHECKS'
            );

            $temp_array = array();
            foreach ( $key_order as $key )
            {
                if ( !isset( $precheck_array[$key] ) )
                    continue;
                $temp_array[$key] = $precheck_array[$key];
            }
            return $temp_array;
        } // end function sortPreCheckArray()

    } // class CAT_Helper_Addons

} // if class_exists()
