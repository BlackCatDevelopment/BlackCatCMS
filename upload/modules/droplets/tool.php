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
 *   @author          Website Baker Project, LEPTON Project, Black Cat Development
 *   @copyright       2004-2010, Website Baker Project
 *   @copyright       2011-2012, LEPTON Project
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         droplets
 *
 */

if (defined('CAT_PATH')) {	
	include(CAT_PATH.'/framework/class.secure.php'); 
} else {
	$root = "../";
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) { 
		include($root.'/framework/class.secure.php'); 
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}

$parser->setGlobals( array(
    'IMGURL' => CAT_URL . '/modules/droplets/css/images',
    'DOCURL' => CAT_URL . '/modules/droplets/docs/readme.html',
    'action' => CAT_ADMIN_URL . '/admintools/tool.php?tool=droplets'
) );
$parser->setPath( CAT_PATH . '/modules/droplets/templates/custom' );
$parser->setFallbackPath( CAT_PATH . '/modules/droplets/templates/default' );

global $settings;
$settings = get_settings();

global $val, $backend;
$val = CAT_Helper_Validate::getInstance();
$backend = CAT_Backend::getInstance('admintools','droplets');

if ( $val->get('_REQUEST','del','numeric') )
{
    $_POST['markeddroplet'] = $val->get('_REQUEST','del','numeric');
    $_REQUEST['delete']     = 1;
}
if ( $val->get('_REQUEST','toggle','numeric') )
{
    toggle_active( $val->get('_REQUEST','toggle','numeric') );
}
elseif ( $val->get('_REQUEST','add') )
{
    edit_droplet( 'new' );
}
elseif ( $val->get('_REQUEST','edit') && !$val->get('_REQUEST','cancel') )
{
    edit_droplet( $val->get('_REQUEST','edit') );
}
elseif ( $val->get('_REQUEST','copy','numeric') )
{
    copy_droplet( $val->get('_REQUEST','copy','numeric') );
}
elseif ( $val->get('_REQUEST','backups') && !$val->get('_REQUEST','cancel') )
{
    manage_droplet_backups();
}
elseif ( $val->get('_REQUEST','export') && !$val->get('_REQUEST','cancel') )
{
    $info = export_droplets();
    list_droplets( $info );
}
elseif ( $val->get('_REQUEST','import') && !$val->get('_REQUEST','cancel') )
{
    import_droplets();
}
elseif ( $val->get('_REQUEST','delete') && !$val->get('_REQUEST','cancel') )
{
    export_droplets();
    delete_droplets();
}
elseif ($val->get('_REQUEST','datafile','numeric') )
{
    edit_datafile( $val->get('_REQUEST','datafile','numeric') );
}
elseif ( $val->get('_REQUEST','droplet_perms','numeric') && !$val->get('_REQUEST','cancel') )
{
    edit_droplet_perms($val->get('_REQUEST','droplet_perms','numeric'));
}
elseif ( $val->get('_REQUEST','perms') && !$val->get('_REQUEST','cancel') )
{
    manage_droplet_perms();
}
else
{
    list_droplets();
}

/**
 * get a list of all droplets and show them
 **/
function list_droplets( $info = NULL )
{
    global $parser, $settings, $val, $backend;

    $groups = CAT_Users::get_groups_id();
    $rows   = CAT_Helper_Droplet::getDroplets(true);

    $backups = CAT_Helper_Directory::scanDirectory(
        CAT_Helper_Directory::sanitizePath(
            dirname(__FILE__).'/export'
        ), true, true, NULL, array('zip') );

    $parser->output( 'tool', array(
        'rows'       => $rows,
        'info'       => $info,
        'backups'    => ( ( count( $backups ) && CAT_Helper_Droplet::is_allowed( 'manage_backups', $groups ) ) ? 1 : NULL ),
        'can_export' => ( CAT_Helper_Droplet::is_allowed( 'export_droplets', $groups ) ? 1 : NULL ),
        'can_import' => ( CAT_Helper_Droplet::is_allowed( 'import_droplets', $groups ) ? 1 : NULL ),
        'can_delete' => ( CAT_Helper_Droplet::is_allowed( 'delete_droplets', $groups ) ? 1 : NULL ),
        'can_modify' => ( CAT_Helper_Droplet::is_allowed( 'modify_droplets', $groups ) ? 1 : NULL ),
        'can_perms'  => ( CAT_Helper_Droplet::is_allowed( 'manage_perms'   , $groups ) ? 1 : NULL ),
        'can_add'    => ( CAT_Helper_Droplet::is_allowed( 'add_droplets'   , $groups ) ? 1 : NULL )
    ) );

} // end function list_droplets()

/**
 * let the user manage the available backups
 **/
function manage_droplet_backups()
{
    global $parser, $settings, $val, $backend;

    $groups = CAT_Users::get_groups_id();
    if ( !CAT_Helper_Droplet::is_allowed( 'manage_backups', $groups ) )
    {
        $backend->print_error( CAT_Backend::getInstance()->lang()->translate( "You don't have the permission to do this" ) );
    }

    $rows = array();
    $info = NULL;

    $dirh = CAT_Helper_Directory::getInstance();

    // recover
    $recover = $val->get('_REQUEST','recover');
    if ( $recover && file_exists( $dirh->sanitizePath( dirname( __FILE__ ) . '/export/' . $recover ) ) )
    {
        if ( !function_exists( 'droplets_upload' ) )
        {
            @include_once( dirname( __FILE__ ) . '/include.php' );
        }
        $temp_unzip = $dirh->sanitizePath( CAT_PATH . '/temp/unzip/' );
        $result     = droplets_import( $dirh->sanitizePath( dirname( __FILE__ ) . '/export/' . $recover ), $temp_unzip );
        $info       = $backend->lang()->translate( 'Successfully imported [{{count}}] Droplet(s)', array(
             'count' => $result[ 'count' ]
        ) );
    }

    // delete single backup
    $delbackup = $val->get('_REQUEST','delbackup');
    if ( $delbackup && file_exists( $dirh->sanitizePath( dirname( __FILE__ ) . '/export/' . $delbackup ) ) )
    {
        @unlink( $dirh->sanitizePath( dirname( __FILE__ ) . '/export/' . $delbackup) );
        $info = $backend->lang()->translate( 'Backup file deleted: {{file}}', array(
             'file' => $delbackup
        ) );
    }

    // delete a list of backups
    // get all marked droplets
    $marked = isset( $_POST[ 'markeddroplet' ] ) ? $_POST[ 'markeddroplet' ] : array();

    if ( count( $marked ) )
    {
        $deleted = array();
        foreach ( $marked as $file )
        {
            $file = $dirh->sanitizePath( dirname( __FILE__ ) . '/export/' . $file );
            if ( file_exists( $file ) )
            {
                @unlink( $file );
                $deleted[] = $backend->lang()->translate( 'Backup file deleted: {{file}}', array(
                     'file' => basename( $file )
                ) );
            }
        }
        if ( count( $deleted ) )
        {
            $info = implode( '<br />', $deleted );
        }
    }

    $backups = $dirh->scanDirectory( $dirh->sanitizePath( dirname( __FILE__ ) . '/export' ), true, true, NULL, array(
         'zip'
    ) );

    if ( count( $backups ) > 0 )
    {
        // sort by name
        sort( $backups );
        foreach ( $backups as $file )
        {
            // stat
            $stat   = stat( $file );
            // get zip contents
            $count  = CAT_Helper_Zip::getInstance($file)->listContent();
            $rows[] = array(
                'name' => basename( $file ),
                'size' => $stat[ 'size' ],
                'date' => strftime( '%c', $stat[ 'ctime' ] ),
                'files' => count( $count ),
                'listfiles' => implode( ", ", array_map( create_function( '$cnt', 'return $cnt["filename"];' ), $count ) ),
                'download' => sanitize_url( CAT_URL . '/modules/droplets/export/' . basename( $file ) )
            );
        }
    }

    $parser->output( 'backups', array(
        'rows' => $rows,
        'info' => $info,
        'backups' => ( count( $backups ) ? 1 : NULL )
    ) );

} // end function manage_droplet_backups()

/**
 *
 **/
function manage_droplet_perms()
{
    global $parser, $settings, $val, $backend;
    $info   = NULL;
    $groups = array();
    $rows   = array();

    $this_user_groups = CAT_Users::get_groups_id();
    if ( !CAT_Helper_Droplet::is_allowed( 'manage_droplet_perms', $this_user_groups ) )
    {
        $backend->print_error( $backend->lang()->translate( "You don't have the permission to do this" ) );
    }

    // get available groups
    $query = $backend->db()->query( 'SELECT group_id, name FROM ' . CAT_TABLE_PREFIX . 'groups ORDER BY name' );
    if ( $query->numRows() )
    {
        while ( $row = $query->fetchRow( MYSQL_ASSOC ) )
        {
            $groups[ $row[ 'group_id' ] ] = $row[ 'name' ];
        }
    }

    if ( $val->get('_REQUEST','save') || $val->get('_REQUEST','save_and_back') )
    {
        foreach ( $settings as $key => $value )
        {
            if ( $val->get('_REQUEST',$key) )
            {
                $backend->db()->query( 'UPDATE ' . CAT_TABLE_PREFIX . "mod_droplets_settings SET value='" . implode( '|',$val->get('_REQUEST',$key) ) . "' WHERE attribute='" . $key . "';" );
            }
        }
        // reload settings
        $settings = get_settings();
        $info     = $backend->lang()->translate( 'Permissions saved' );
        if ( $val->get('_REQUEST','save_and_back') )
        {
            return list_droplets( $info );
        }
    }

    foreach ( $settings as $key => $value )
    {
        $line = array();
        foreach ( $groups as $id => $name )
        {
            $line[] = '<input type="checkbox" name="' . $key . '[]" id="' . $key . '_' . $id . '" value="' . $id . '"' . ( is_in_array( $value, $id ) ? ' checked="checked"' : NULL ) . '>' . '<label for="' . $key . '_' . $id . '">' . $name . '</label>' . "\n";
        }
        $rows[] = array(
            'groups' => implode( '', $line ),
            'name'   => $backend->lang()->translate( $key )
        );
    }

    // sort rows by permission name (=text)
    $array = CAT_Helper_Array::getInstance();
    $rows  = $array->ArraySort( $rows, 'name', 'asc', true );

    $parser->output( 'permissions', array(
        'rows' => $rows,
        'info' => $info
    ) );

} // end function manage_droplet_perms()

/**
 *
 **/
function export_droplets()
{
    global $parser, $val, $backend;

    $groups = CAT_Users::get_groups_id();
    if ( !CAT_Helper_Droplet::is_allowed( 'export_droplets', $groups ) )
    {
        $backend->print_error( $backend->lang()->translate( "You don't have the permission to do this" ) );
    }

    $info = array();

    // get all marked droplets
    $marked = isset( $_POST[ 'markeddroplet' ] ) ? $_POST[ 'markeddroplet' ] : array();

    if ( isset( $marked ) && !is_array( $marked ) )
    {
        $marked = array(
             $marked
        );
    }

    if ( !count( $marked ) )
    {
        return $backend->lang()->translate( 'Please mark some Droplets to export' );
    }

    $temp_dir = CAT_PATH . '/temp/droplets/';

    // make the temporary working directory
    @mkdir( $temp_dir );

    foreach ( $marked as $id )
    {
        $result = $backend->db()->query(sprintf(
            "SELECT * FROM `%smod_droplets` WHERE id=%d",
            CAT_TABLE_PREFIX, $id
        ));
        if ( $result->numRows() > 0 )
        {
            $droplet = $result->fetchRow(MYSQL_ASSOC);
            $name    = $droplet["name"];
            $usage   = preg_replace('/[\x00-\x1F\x7F]/', "\n//", $droplet['comments']);
            if(substr($usage,-2,2)=='//')
                $usage   = substr($usage,0,-3);
            $info[]  = 'Droplet: ' . $name . '.php<br />';
            $sFile   = $temp_dir . $name . '.php';
            $fh      = fopen( $sFile, 'w' );
            fwrite( $fh, '//:' . $droplet[ 'description' ] . "\n" );
            fwrite( $fh, '//:' . $usage . "\n" );
            fwrite( $fh, $droplet[ 'code' ] );
            fclose( $fh );
            $file = NULL;
            // look for a data file
            if ( file_exists( dirname( __FILE__ ) . '/data/' . $droplet[ 'name' ] . '.txt' ) )
            {
                $file = CAT_Helper_Directory::sanitizePath( dirname( __FILE__ ) . '/data/' . $droplet[ 'name' ] . '.txt' );
            }
            elseif ( file_exists( dirname( __FILE__ ) . '/data/' . strtolower( $droplet[ 'name' ] ) . '.txt' ) )
            {
                $file = CAT_Helper_Directory::sanitizePath( dirname( __FILE__ ) . '/data/' . strtolower( $droplet[ 'name' ] ) . '.txt' );
            }
            elseif ( file_exists( dirname( __FILE__ ) . '/data/' . strtoupper( $droplet[ 'name' ] ) . '.txt' ) )
            {
                $file = CAT_Helper_Directory::sanitizePath( dirname( __FILE__ ) . '/data/' . strtoupper( $droplet[ 'name' ] ) . '.txt' );
            }
            if ( $file )
            {
                if ( !file_exists( $temp_dir . '/data' ) )
                {
                    @mkdir( $temp_dir . '/data' );
                }
                copy( $file, $temp_dir . '/data/' . basename( $file ) );
            }
        }
    }

    $filename = 'droplets';

    // if there's only a single droplet to export, name the zip-file after this droplet
    if ( count( $marked ) === 1 )
    {
        $filename = 'droplet_' . $name;
    }

    // add current date to filename
    $filename .= '_' . date( 'Y-m-d' );

    // while there's an existing file, add a number to the filename
    if ( file_exists( CAT_PATH . '/modules/droplets/export/' . $filename . '.zip' ) )
    {
        $n = 1;
        while ( file_exists( CAT_PATH . '/modules/droplets/export/' . $filename . '_' . $n . '.zip' ) )
        {
            $n++;
        }
        $filename .= '_' . $n;
    }

    $temp_file = sanitize_path( CAT_PATH . '/temp/' . $filename . '.zip' );

    // create zip
    $archive   = CAT_Helper_Zip::getInstance($temp_file)->config( 'removePath', $temp_dir );
    $file_list = $archive->create( $temp_dir );
    if ( $file_list == 0 )
    {
        list_droplets( $backend->lang()->translate( "Packaging error" ) . ' - ' . $archive->errorInfo( true ) );
    }
    else
    {
        $export_dir = sanitize_path( CAT_PATH . '/modules/droplets/export' );
        // create the export folder if it doesn't exist
        if ( !file_exists( $export_dir ) )
        {
            mkdir( $export_dir, 0777 );
        }
        if ( !copy( $temp_file, $export_dir . '/' . $filename . '.zip' ) )
        {
            echo '<div class="drfail">Unable to move the exported ZIP-File!</div>';
            $download = CAT_URL . '/temp/' . $filename . '.zip';
        }
        else
        {
            unlink( $temp_file );
            $download = sanitize_url( CAT_URL . '/modules/droplets/export/' . $filename . '.zip' );
        }
    }

    CAT_Helper_Directory::getInstance()->removeDirectory( $temp_dir );

    return $backend->lang()->translate( 'Backup created' ) . '<br /><br />' . implode( "\n", $info ) . '<br /><br /><a href="' . $download . '">Download</a>';

} // end function export_droplets()

/**
 *
 **/
function import_droplets()
{
    global $parser, $backend;

    $groups = CAT_Users::get_groups_id();
    if ( !CAT_Helper_Droplet::is_allowed( 'import_droplets', $groups ) )
    {
        $backend->print_error( $backend->lang()->translate( "You don't have the permission to do this" ) );
    }

    $problem = NULL;

    if ( count( $_FILES ) )
    {
        if ( !function_exists( 'droplets_upload' ) )
        {
            @include_once( dirname( __FILE__ ) . '/include.php' );
        }
        list( $result, $data ) = droplets_upload( 'file' );
        $info = NULL;
        if ( is_array( $data ) )
        {
            $isIndexed = array_values( $data ) === $data;
            if ( $isIndexed )
            {
                $info .= implode( '<br />', $data );
            }
            else
            {
                foreach ( $data as $key => $value )
                {
                    $info .= $key . ' -> ' . $value . "<br />";
                }
            }
        }
        if ( $result == 'error' )
        {
            $problem = $backend->lang()->translate( 'An error occurred when trying to import the Droplet(s)' ) . '<br /><br />' . $info;
        }
        else
        {
            list_droplets( $backend->lang()->translate( 'Successfully imported [{{count}}] Droplet(s)', array(
                 'count' => $data
            ) ) );
            return;
        }
    }

    $parser->output( 'import.tpl', array(
         'problem' => $problem
    ) );

} // end function import_droplets()

/**
 *
 **/
function delete_droplets()
{
    global $parser, $val, $backend;

    $groups = CAT_Users::get_groups_id();
    if ( !CAT_Helper_Droplet::is_allowed( 'delete_droplets', $groups ) )
    {
        $backend->print_error( $backend->lang()->translate( "You don't have the permission to do this" ) );
    }

    $errors = array();

    // get all marked droplets
    $marked = isset( $_POST[ 'markeddroplet' ] ) ? $_POST[ 'markeddroplet' ] : array();

    if ( isset( $marked ) && !is_array( $marked ) )
    {
        $marked = array(
             $marked
        );
    }

    if ( !count( $marked ) )
    {
        list_droplets( $backend->lang()->translate( 'Please mark some Droplet(s) to delete' ) );
        return; // should never be reached
    }

    foreach ( $marked as $id )
    {
        // get the name; needed to delete data file
        $query = $backend->db()->query( "SELECT name FROM " . CAT_TABLE_PREFIX . "mod_droplets WHERE id = '$id'" );
        $data  = $query->fetchRow( MYSQL_ASSOC );
        $backend->db()->query( "DELETE FROM " . CAT_TABLE_PREFIX . "mod_droplets WHERE id = '$id'" );
        if ( $backend->db()->is_error() )
        {
            $errors[] = $backend->lang()->translate( 'Unable to delete Droplet: {{id}}', array(
                 'id' => $id
            ) );
        }
        // look for a data file
        if ( file_exists( dirname( __FILE__ ) . '/data/' . $data[ 'name' ] . '.txt' ) )
        {
            @unlink( CAT_Helper_Directory::sanitizePath( dirname( __FILE__ ) . '/data/' . $data[ 'name' ] . '.txt' ) );
        }
        elseif ( file_exists( dirname( __FILE__ ) . '/data/' . strtolower( $data[ 'name' ] ) . '.txt' ) )
        {
            @unlink( CAT_Helper_Directory::sanitizePath( dirname( __FILE__ ) . '/data/' . strtolower( $data[ 'name' ] ) . '.txt' ) );
        }
        elseif ( file_exists( dirname( __FILE__ ) . '/data/' . strtoupper( $data[ 'name' ] ) . '.txt' ) )
        {
            @unlink( CAT_Helper_Directory::sanitizePath( dirname( __FILE__ ) . '/data/' . strtoupper( $data[ 'name' ] ) . '.txt' ) );
        }
    }

    list_droplets( implode( "<br />", $errors ) );
    return;

} // end function delete_droplets()

/**
 * copy a droplet
 **/
function copy_droplet( $id )
{
    global $val, $backend;

    $groups = CAT_Users::get_groups_id();

    if ( !CAT_Helper_Droplet::is_allowed( 'modify_droplets', $groups ) )
    {
        $backend->print_error( $backend->lang()->translate( "You don't have the permission to do this" ) );
    }

    $query    = $backend->db()->query( "SELECT * FROM " . CAT_TABLE_PREFIX . "mod_droplets WHERE id = '$id'" );
    $data     = $query->fetchRow( MYSQL_ASSOC );
    $tags     = array(
        '<?php',
        '?>',
        '<?'
    );
    $code     = addslashes( str_replace( $tags, '', $data[ 'code' ] ) );
    $new_name = $data[ 'name' ] . "_copy";
    $i        = 1;

    // look for doubles
    $found = $backend->db()->query( 'SELECT * FROM ' . CAT_TABLE_PREFIX . "mod_droplets WHERE name='$new_name'" );
    while ( $found->numRows() > 0 )
    {
        $new_name = $data[ 'name' ] . "_copy" . $i;
        $found    = $backend->db()->query( 'SELECT * FROM ' . CAT_TABLE_PREFIX . "mod_droplets WHERE name='$new_name'" );
        $i++;
    }

    // generate query
    $query = "INSERT INTO " . CAT_TABLE_PREFIX . "mod_droplets VALUES "
    //         ID      NAME         CODE              DESCRIPTION                            MOD_WHEN                     MOD_BY
		   . "('', '$new_name', '$code', '" . $data[ 'description' ] . "', '" . time() . "', '" . CAT_Users::get_user_id() . "', 1, 1, 1, 0, '" . $data[ 'comments' ] . "' )";

    // add new droplet
    $result = $backend->db()->query( $query );
    if ( !$backend->db()->is_error() )
    {
        $new_id = mysql_insert_id();
        return edit_droplet( $new_id );
    }
    else
    {
        echo "ERROR: ", $backend->db()->get_error();
    }
}

/**
 * edit a droplet
 **/
function edit_droplet( $id )
{
    global $parser, $val, $backend;

    $groups = CAT_Users::get_groups_id();

    if ( $id == 'new' && !CAT_Helper_Droplet::is_allowed( 'add_droplets', $groups ) )
    {
        $backend->print_error( $backend->lang()->translate( "You don't have the permission to do this" ) );
    }
    else
    {
        if ( !CAT_Helper_Droplet::is_allowed( 'modify_droplets', $groups ) )
        {
            $backend->print_error( $backend->lang()->translate( "You don't have the permission to do this" ) );
        }
    }

    $problem  = NULL;
    $info     = NULL;
    $problems = array();

    if ( $val->get('_REQUEST','cancel') )
    {
        return list_droplets();
    }

    if ( $id != 'new' )
    {
        $query        = $backend->db()->query( "SELECT * FROM " . CAT_TABLE_PREFIX . "mod_droplets WHERE id = '$id'" );
        $data         = $query->fetchRow( MYSQL_ASSOC );
    }
    else
    {
        $data = array(
            'name' => '',
            'active' => 1,
            'description' => '',
            'code' => '',
            'comments' => ''
        );
    }

    if ( $val->get('_REQUEST','save') || $val->get('_REQUEST','save_and_back') )
    {
        // check the code before saving
        if ( !check_syntax( stripslashes( $val->get('_POST','code') ) ) )
        {
            $problem      = $backend->lang()->translate( 'Please check the syntax!' );
            $data         = $_POST;
            $data['code'] = (htmlspecialchars($data['code']));
        }
        else
        {
            // syntax okay, check fields and save
            if ( $val->sanitizePost( 'name' ) == '' )
            {
                $problems[] = $backend->lang()->translate( 'Please enter a name!' );
            }
            if ( $val->sanitizePost( 'code' ) == '' )
            {
                $problems[] = $backend->lang()->translate( 'You have entered no code!' );
            }

            if ( !count( $problems ) )
            {
                $continue      = true;
                $title         = $val->sanitizePost( 'name',NULL,true );
                $active        = $val->sanitizePost( 'active' );
                $show_wysiwyg  = $val->sanitizePost( 'show_wysiwyg' );
                $description   = $val->sanitizePost( 'description',NULL,true );
                $tags          = array(
                    '<?php',
                    '?>',
                    '<?'
                );
                $content       = str_replace( $tags, '', $val->sanitizePost( 'code' ) );
                $comments      = $val->sanitizePost( 'comments',NULL,true );
                $modified_when = time();
                $modified_by   = CAT_Users::get_user_id();
                if ( $id == 'new' )
                {
                    // check for doubles
                    $query = $backend->db()->query( "SELECT * FROM " . CAT_TABLE_PREFIX . "mod_droplets WHERE name = '$title'" );
                    if ( $query->numRows() > 0 )
                    {
                        $problem  = $backend->lang()->translate( 'There is already a droplet with the same name!' );
                        $continue = false;
                        $data     = $_POST;
                        $data['code'] = stripslashes( $_POST[ 'code' ] );
                    }
                    else
                    {
						$code  = $val->add_slashes( $content );
						// generate query
						$query = "INSERT INTO " . CAT_TABLE_PREFIX . "mod_droplets VALUES "
							   . "(''," . "'$title', " . "'$code', " . "'$description', " . "'$modified_when', " . "'$modified_by', " . "'$active',1,1, '$show_wysiwyg', '$comments' )";
					    $result = $backend->db()->query( $query );
					    if ( $backend->db()->is_error() )
					    {
					        echo "ERROR: ", $backend->db()->get_error();
					    }

                    }
                }
                else
                {
                    // Update row
                    $backend->db()->query( "UPDATE " . CAT_TABLE_PREFIX . "mod_droplets SET name = '$title', active = '$active', show_wysiwyg = '$show_wysiwyg', description = '$description', code = '"
                                    . $val->add_slashes( $content )
                                    . "', comments = '$comments', modified_when = '$modified_when', modified_by = '$modified_by' WHERE id = '$id'"
                    );
                    // reload Droplet data
                    $query = $backend->db()->query( "SELECT * FROM " . CAT_TABLE_PREFIX . "mod_droplets WHERE id = '$id'" );
                    $data  = $query->fetchRow( MYSQL_ASSOC );
                }
                if ( $continue )
                {
                    // Check if there is a db error
                    if ( $backend->db()->is_error() )
                    {
                        $problem = $backend->db()->get_error();
                    }
                    else
                    {
                        if ( $id == 'new' || $val->get('_REQUEST','save_and_back') )
                        {
                            list_droplets( $backend->lang()->translate( 'The Droplet was saved' ) );
                            return; // should never be reached
                        }
                        else
                        {
                            $info = $backend->lang()->translate( 'The Droplet was saved' );
                        }
                    }
                }
            }
            else
            {
                $problem = implode( "<br />", $problems );
            }
        }
    }

    defined( "ENT_HTML401" ) or define( "ENT_HTML401", 0 );
    defined( "ENT_COMPAT" )  or define( "ENT_COMPAT", 2 );
    $data[ 'code' ] = htmlspecialchars( $data[ 'code' ], ENT_COMPAT | ENT_HTML401, 'UTF-8', false );

    $parser->output( 'edit.tpl', array(
        'problem' => $problem,
        'info' => $info,
        'data' => $data,
        'id'   => $id,
        'name' => $data[ 'name' ]
    ) );
} // end function edit_droplet()

/**
 *
 **/
function edit_droplet_perms( $id )
{
    global $parser, $val, $backend;

    // look if user can set permissions
    $this_user_groups = CAT_Users::get_groups_id();
    if ( !CAT_Helper_Droplet::is_allowed( 'manage_perms', $this_user_groups ) )
    {
        $backend->print_error( $backend->lang()->translate( "You don't have the permission to do this" ) );
    }

    $info = NULL;

    // get available groups
    $query = $backend->db()->query( 'SELECT group_id, name FROM ' . CAT_TABLE_PREFIX . 'groups ORDER BY name' );
    if ( $query->numRows() )
    {
        while ( $row = $query->fetchRow( MYSQL_ASSOC ) )
        {
            $groups[ $row[ 'group_id' ] ] = $row[ 'name' ];
        }
    }

    // save perms
    if ( $val->get('_REQUEST','save') || $val->get('_REQUEST','save_and_back') )
    {
        $edit = (
					  $val->get('_REQUEST','edit_groups')
					? ( is_array($val->get('_REQUEST','edit_groups')) ? implode('|',$val->get('_REQUEST','edit_groups')) : $val->get('_REQUEST','edit_groups') )
					: NULL
				);
        $view = (
					  $val->get('_REQUEST','view_groups')
					? ( is_array($val->get('_REQUEST','view_groups')) ? implode('|',$val->get('_REQUEST','view_groups')) : $val->get('_REQUEST','view_groups') )
					: NULL
				);
        $backend->db()->query( 'REPLACE INTO ' . CAT_TABLE_PREFIX . "mod_droplets_permissions VALUES( '$id', '$edit', '$view' );" );
        $info = $backend->lang()->translate( 'The Droplet was saved' );
        if ( $val->get('_REQUEST','save_and_back') )
        {
            return list_droplets( $info );
        }
    }

    // get droplet data
    $query = $backend->db()->query( "SELECT * FROM " . CAT_TABLE_PREFIX . "mod_droplets AS t1 LEFT OUTER JOIN ".CAT_TABLE_PREFIX."mod_droplets_permissions AS t2 ON t1.id=t2.id WHERE t1.id = '$id'" );
    $data  = $query->fetchRow( MYSQL_ASSOC );

    foreach ( array(
        'edit_groups',
        'view_groups'
    ) as $key )
    {
        $allowed_groups = ( isset( $data[ $key ] ) ? explode( '|', $data[ $key ] ) : array ());
        $line           = array();
        foreach ( $groups as $gid => $name )
        {
            $line[] = '<input type="checkbox" name="' . $key . '[]" id="' . $key . '_' . $gid . '" value="' . $gid . '"' . ( ( is_in_array( $allowed_groups, $gid ) || !count( $allowed_groups ) ) ? ' checked="checked"' : NULL ) . '>' . '<label for="' . $key . '_' . $gid . '">' . $name . '</label>' . "\n";
        }
        $rows[] = array(
            'groups' => implode( '', $line ),
            'name' => $backend->lang()->translate( $key )
        );
    }

    $parser->output( 'droplet_permissions.tpl', array(
        'rows' => $rows,
        'info' => $info,
        'id'   => $id
    ) );

} // end function edit_droplet_perms()

/**
 * edit a droplet's datafile
 **/
function edit_datafile( $id )
{
    global $parser, $val, $backend;
    $info = $problem = NULL;

    $groups = CAT_Users::get_groups_id();
    if ( !CAT_Helper_Droplet::is_allowed( 'modify_droplets', $groups ) )
    {
        $backend->print_error( $backend->lang()->translate( "You don't have the permission to do this" ) );
    }

    if ( $val->get('_REQUEST','cancel') )
    {
        return list_droplets();
    }

    $query = $backend->db()->query( "SELECT name FROM " . CAT_TABLE_PREFIX . "mod_droplets WHERE id = '$id'" );
    $data  = $query->fetchRow( MYSQL_ASSOC );

    // find the file
    if ( file_exists( dirname( __FILE__ ) . '/data/' . $data[ 'name' ] . '.txt' ) )
    {
        $file = CAT_Helper_Directory::getInstance()->sanitizePath( dirname( __FILE__ ) . '/data/' . $data[ 'name' ] . '.txt' );
    }
    elseif ( file_exists( dirname( __FILE__ ) . '/data/' . strtolower( $data[ 'name' ] ) . '.txt' ) )
    {
        $file = CAT_Helper_Directory::getInstance()->sanitizePath( dirname( __FILE__ ) . '/data/' . strtolower( $data[ 'name' ] ) . '.txt' );
    }
    elseif ( file_exists( dirname( __FILE__ ) . '/data/' . strtoupper( $data[ 'name' ] ) . '.txt' ) )
    {
        $file = CAT_Helper_Directory::getInstance()->sanitizePath( dirname( __FILE__ ) . '/data/' . strtoupper( $data[ 'name' ] ) . '.txt' );
    }

    // slurp file
    $contents = implode( '', file( $file ) );

    if ( isset( $_POST[ 'save' ] ) || isset( $_POST[ 'save_and_back' ] ) )
    {
        $new_contents = htmlentities( $_POST[ 'contents' ] );
        // create backup copy
        copy( $file, $file . '.bak' );
        $fh = fopen( $file, 'w' );
        if ( is_resource( $fh ) )
        {
            fwrite( $fh, $new_contents );
            fclose( $fh );
            $info = $backend->lang()->translate( 'The datafile has been saved' );
            if ( isset( $_POST[ 'save_and_back' ] ) )
            {
                return list_droplets( $info );
            }
        }
        else
        {
            $problem = $backend->lang()->translate( 'Unable to write to file [{{file}}]', array(
                 'file' => str_ireplace( CAT_Helper_Directory::getInstance()->sanitizePath( CAT_PATH ), 'CAT_PATH', $file )
            ) );
        }
    }

    $parser->output( 'edit_datafile.tpl', array(
         'info' => $info,
        'problem' => $problem,
        'name' => $data[ 'name' ],
        'id' => $id,
        'contents' => htmlspecialchars( $contents )
    ) );
} // end function edit_droplet()


/**
 *
 **/
function toggle_active( $id )
{
    global $parser, $val, $backend;

    $groups = CAT_Users::get_groups_id();
    if ( !CAT_Helper_Droplet::is_allowed( 'modify_droplets', $groups ) )
    {
        $backend->print_error( $backend->lang()->translate( "You don't have the permission to do this" ) );
    }

    $query = $backend->db()->query( "SELECT * FROM " . CAT_TABLE_PREFIX . "mod_droplets WHERE id = '$id'" );
    $data  = $query->fetchRow( MYSQL_ASSOC );

    $new = ( $data[ 'active' ] == 1 ) ? 0 : 1;

    $backend->db()->query( 'UPDATE ' . CAT_TABLE_PREFIX . "mod_droplets SET active='$new' WHERE id = '$id'" );

} // end function toggle_active()

/**
 * checks if any item of $allowed is in $current
 **/
function is_in_array( $allowed, $current )
{
    if ( !is_array( $allowed ) )
    {
        if ( substr_count( $allowed, '|' ) )
        {
            $allowed = explode( '|', $allowed );
        }
        else
        {
            $allowed = array(
                 $allowed
            );
        }
    }
    if ( !is_array( $current ) )
    {
        if ( substr_count( $current, '|' ) )
        {
            $current = explode( '|', $current );
        }
        else
        {
            $current = array(
                 $current
            );
        }
    }
    foreach ( $allowed as $gid )
    {
        if ( in_array( $gid, $current ) )
        {
            return true;
        }
    }
    return false;
} // end function is_in_array()

/**
 * check the syntax of given code
 **/
function check_syntax( $code )
{
    return @eval( 'return true;' . $code );
}

/**
 * get the module settings from the DB; returns array
 **/
function get_settings()
{
    global $backend;
    $settings = array();
    $query    = $backend->db()->query( 'SELECT * FROM ' . CAT_TABLE_PREFIX . 'mod_droplets_settings' );
    if ( $query->numRows() )
    {
        while ( $row = $query->fetchRow( MYSQL_ASSOC ) )
        {
            if ( substr_count( $row[ 'value' ], '|' ) )
            {
                $row[ 'value' ] = explode( '|', $row[ 'value' ] );
            }
            $settings[ $row[ 'attribute' ] ] = $row[ 'value' ];
        }
    }
    return $settings;
} // end function get_settings()

?>