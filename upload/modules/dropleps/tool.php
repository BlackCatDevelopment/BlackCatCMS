<?php

/**
 * This file is part of an ADDON for use with LEPTON Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 * @module          dropleps
 * @author          LEPTON Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see info.php of this module
 * @version         $Id$
 *
 */

// include class.secure.php to protect this file and the whole CMS!
if ( defined( 'WB_PATH' ) )
{
    include( WB_PATH . '/framework/class.secure.php' );
}
else
{
    $root  = "../";
    $level = 1;
    while ( ( $level < 10 ) && ( !file_exists( $root . '/framework/class.secure.php' ) ) )
    {
        $root .= "../";
        $level += 1;
    }
    if ( file_exists( $root . '/framework/class.secure.php' ) )
    {
        include( $root . '/framework/class.secure.php' );
    }
    else
    {
        trigger_error( sprintf( "[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER[ 'SCRIPT_NAME' ] ), E_USER_ERROR );
    }
}
// end include class.secure.php


$parser->setGlobals( array(
    'IMGURL' => WB_URL . '/modules/dropleps/css/images',
    'DOCURL' => WB_URL . '/modules/dropleps/docs/readme.html',
    'action' => ADMIN_URL . '/admintools/tool.php?tool=dropleps'
) );
$parser->setPath( WB_PATH . '/modules/dropleps/templates/custom' );
$parser->setFallbackPath( WB_PATH . '/modules/dropleps/templates/default' );

$admin->lang->debug( true );

global $settings;
$settings = get_settings();

if ( isset( $_REQUEST[ 'del' ] ) && is_numeric( $_REQUEST[ 'del' ] ) )
{
    $_POST[ 'markeddroplet' ] = $_REQUEST[ 'del' ];
    $_REQUEST[ 'delete' ]     = 1;
}
if ( isset( $_REQUEST[ 'toggle' ] ) && is_numeric( $_REQUEST[ 'toggle' ] ) )
{
    toggle_active( $_REQUEST[ 'toggle' ] );
}
elseif ( isset( $_REQUEST[ 'add' ] ) )
{
    edit_droplep( 'new' );
}
elseif ( isset( $_REQUEST[ 'edit' ] ) && !isset( $_REQUEST[ 'cancel' ] ) )
{
    edit_droplep( $_REQUEST[ 'edit' ] );
}
elseif ( isset( $_REQUEST[ 'copy' ] ) && is_numeric( $_REQUEST[ 'copy' ] ) )
{
    copy_droplep( $_REQUEST[ 'copy' ] );
}
elseif ( isset( $_REQUEST[ 'backups' ] ) && !isset( $_REQUEST[ 'cancel' ] ) )
{
    manage_backups();
}
elseif ( isset( $_REQUEST[ 'export' ] ) && !isset( $_REQUEST[ 'cancel' ] ) )
{
    $info = export_dropleps();
    list_dropleps( $info );
}
elseif ( isset( $_REQUEST[ 'import' ] ) && !isset( $_REQUEST[ 'cancel' ] ) )
{
    import_dropleps();
}
elseif ( isset( $_REQUEST[ 'delete' ] ) && !isset( $_REQUEST[ 'cancel' ] ) )
{
    export_dropleps();
    delete_dropleps();
}
elseif ( isset( $_REQUEST[ 'datafile' ] ) && is_numeric( $_REQUEST[ 'datafile' ] ) )
{
    edit_datafile( $_REQUEST[ 'datafile' ] );
}
elseif ( isset( $_REQUEST[ 'droplep_perms' ] ) && is_numeric( $_REQUEST[ 'droplep_perms' ] ) && !isset( $_REQUEST[ 'cancel' ] ) )
{
    edit_droplep_perms( $_REQUEST[ 'droplep_perms' ] );
}
elseif ( isset( $_REQUEST[ 'perms' ] ) && !isset( $_REQUEST[ 'cancel' ] ) )
{
    manage_perms();
}
else
{
    list_dropleps();
}

/**
 * get a list of all dropleps and show them
 **/
function list_dropleps( $info = NULL )
{
    global $admin, $parser, $database, $settings;

    // check for global read perms
    $groups = $admin->get_groups_id();

    $dirh    = $admin->get_helper( 'Directory' );
    $backups = $dirh->scanDirectory( $dirh->sanitizePath( dirname( __FILE__ ) . '/export' ), true, true, NULL, array(
         'zip'
    ) );

    $rows = array();

    $fields = 't1.id, name, code, description, active, comments, view_groups, edit_groups';
    $query  = $database->query( "SELECT $fields FROM " . TABLE_PREFIX . "mod_droplets AS t1 LEFT OUTER JOIN " . TABLE_PREFIX . "mod_dropleps_permissions AS t2 ON t1.id=t2.id ORDER BY name ASC" );

    if ( $query->numRows() )
    {
        while ( $droplet = $query->fetchRow( MYSQL_ASSOC ) )
        {
            // the current user needs global edit permissions, or specific edit permissions to see this droplep
            if ( !is_allowed( 'modify_dropleps', $groups ) )
            {
                // get edit groups for this droplep
                if ( $droplet[ 'edit_groups' ] )
                {
                    if ( $admin->get_user_id() != 1 && !is_in_array( $droplet[ 'edit_groups' ], $groups ) )
                    {
                        continue;
                    }
                    else
                    {
                        $droplet[ 'user_can_modify_this' ] = true;
                    }
                }
            }
            $comments = str_replace( array(
                "\r\n",
                "\n",
                "\r"
            ), '<br />', $droplet[ 'comments' ] );
            if ( !strpos( $comments, "[[" ) ) //
            {
                $comments = '<span class="usage">' . $admin->lang->translate( 'Use' ) . ": [[" . $droplet[ 'name' ] . "]]</span><br />" . $comments;
            }
            $comments                  = str_replace( array(
                "[[",
                "]]"
            ), array(
                '<b>[[',
                ']]</b>'
            ), $comments );
            $droplet[ 'valid_code' ]   = check_syntax( $droplet[ 'code' ] );
            $droplet[ 'comments' ]     = $comments;
            // droplet included in search?
	        $droplet['is_in_search'] = $admin->get_helper('DropLEP')->is_registered_for_search($droplet['name']);
            // is there a data file for this droplet?
            if ( file_exists( dirname( __FILE__ ) . '/data/' . $droplet[ 'name' ] . '.txt' ) || file_exists( dirname( __FILE__ ) . '/data/' . strtolower( $droplet[ 'name' ] ) . '.txt' ) || file_exists( dirname( __FILE__ ) . '/data/' . strtoupper( $droplet[ 'name' ] ) . '.txt' ) )
            {
                $droplet[ 'datafile' ] = true;
            }
            array_push( $rows, $droplet );
        }
    }

    $parser->output( 'index.lte', array(
        'rows'       => $rows,
        'info'       => $info,
        'backups'    => ( ( count( $backups ) && is_allowed( 'manage_backups', $groups ) ) ? 1 : NULL ),
        'can_export' => ( is_allowed( 'export_dropleps', $groups ) ? 1 : NULL ),
        'can_import' => ( is_allowed( 'import_dropleps', $groups ) ? 1 : NULL ),
        'can_delete' => ( is_allowed( 'delete_dropleps', $groups ) ? 1 : NULL ),
        'can_modify' => ( is_allowed( 'modify_dropleps', $groups ) ? 1 : NULL ),
        'can_perms'  => ( is_allowed( 'manage_perms', $groups ) ? 1 : NULL ),
        'can_add'    => ( is_allowed( 'add_dropleps', $groups ) ? 1 : NULL )
    ) );

} // end function list_dropleps()

/**
 *
 **/
function manage_backups()
{
    global $admin, $parser, $database, $settings;

    $groups = $admin->get_groups_id();
    if ( !is_allowed( 'manage_backups', $groups ) )
    {
        $admin->print_error( $admin->lang->translate( "You don't have the permission to do this" ) );
    }

    $rows = array();
    $info = NULL;

    $dirh = $admin->get_helper( 'Directory' );

    // recover
    if ( isset( $_REQUEST[ 'recover' ] ) && file_exists( $dirh->sanitizePath( dirname( __FILE__ ) . '/export/' . $_REQUEST[ 'recover' ] ) ) )
    {
        if ( !function_exists( 'dropleps_upload' ) )
        {
            @include_once( dirname( __FILE__ ) . '/include.php' );
        }
        $temp_unzip = $dirh->sanitizePath( WB_PATH . '/temp/unzip/' );
        $result     = dropleps_import( $dirh->sanitizePath( dirname( __FILE__ ) . '/export/' . $_REQUEST[ 'recover' ] ), $temp_unzip );
        $info       = $admin->lang->translate( 'Successfully imported [{{count}}] Droplep(s)', array(
             'count' => $result[ 'count' ]
        ) );
    }

    // delete single backup
    if ( isset( $_REQUEST[ 'delbackup' ] ) && file_exists( $dirh->sanitizePath( dirname( __FILE__ ) . '/export/' . $_REQUEST[ 'delbackup' ] ) ) )
    {
        @unlink( $dirh->sanitizePath( dirname( __FILE__ ) . '/export/' . $_REQUEST[ 'delbackup' ] ) );
        $info = $admin->lang->translate( 'Backup file deleted: {{file}}', array(
             'file' => $_REQUEST[ 'delbackup' ]
        ) );
    }

    // delete a list of backups
    // get all marked dropleps
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
                $deleted[] = $admin->lang->translate( 'Backup file deleted: {{file}}', array(
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
            $count  = $admin->get_helper('Zip',$file)->listContent();
            $rows[] = array(
                'name' => basename( $file ),
                'size' => $stat[ 'size' ],
                'date' => strftime( '%c', $stat[ 'ctime' ] ),
                'files' => count( $count ),
                'listfiles' => implode( ", ", array_map( create_function( '$cnt', 'return $cnt["filename"];' ), $count ) ),
                'download' => sanitize_url( WB_URL . '/modules/dropleps/export/' . basename( $file ) )
            );
        }
    }

    $parser->output( 'backups.lte', array(
        'rows' => $rows,
        'info' => $info,
        'backups' => ( count( $backups ) ? 1 : NULL )
    ) );

} // end function manage_backups()

/**
 *
 **/
function manage_perms()
{
    global $admin, $parser, $database, $settings;
    $info   = NULL;
    $groups = array();
    $rows   = array();

    $this_user_groups = $admin->get_groups_id();
    if ( !is_allowed( 'manage_perms', $this_user_groups ) )
    {
        $admin->print_error( $admin->lang->translate( "You don't have the permission to do this" ) );
    }

    // get available groups
    $query = $database->query( 'SELECT group_id, name FROM ' . TABLE_PREFIX . 'groups ORDER BY name' );
    if ( $query->numRows() )
    {
        while ( $row = $query->fetchRow( MYSQL_ASSOC ) )
        {
            $groups[ $row[ 'group_id' ] ] = $row[ 'name' ];
        }
    }

    if ( isset( $_REQUEST[ 'save' ] ) || isset( $_REQUEST[ 'save_and_back' ] ) )
    {
        foreach ( $settings as $key => $value )
        {
            if ( isset( $_REQUEST[ $key ] ) )
            {
                $database->query( 'UPDATE ' . TABLE_PREFIX . "mod_dropleps_settings SET value='" . implode( '|', $_REQUEST[ $key ] ) . "' WHERE attribute='" . $key . "';" );
            }
        }
        // reload settings
        $settings = get_settings();
        $info     = $admin->lang->translate( 'Permissions saved' );
        if ( isset( $_REQUEST[ 'save_and_back' ] ) )
        {
            return list_dropleps( $info );
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
            'name' => $admin->lang->translate( $key )
        );
    }

    // sort rows by permission name (=text)
    $array = $admin->get_helper( 'Array' );
    $rows  = $array->ArraySort( $rows, 'name', 'asc', true );

    $parser->output( 'permissions.lte', array(
        'rows' => $rows,
        'info' => $info
    ) );

} // end function manage_perms()

/**
 *
 **/
function export_dropleps()
{
    global $admin, $parser, $database;

    $groups = $admin->get_groups_id();
    if ( !is_allowed( 'export_dropleps', $groups ) )
    {
        $admin->print_error( $admin->lang->translate( "You don't have the permission to do this" ) );
    }

    $info = array();

    // get all marked dropleps
    $marked = isset( $_POST[ 'markeddroplet' ] ) ? $_POST[ 'markeddroplet' ] : array();

    if ( isset( $marked ) && !is_array( $marked ) )
    {
        $marked = array(
             $marked
        );
    }

    if ( !count( $marked ) )
    {
        return $admin->lang->translate( 'Please mark some Dropleps to export' );
    }

    $temp_dir = WB_PATH . '/temp/dropleps/';

    // make the temporary working directory
    @mkdir( $temp_dir );

    foreach ( $marked as $id )
    {
        $result = $database->query( "SELECT * FROM " . TABLE_PREFIX . "mod_droplets WHERE id='$id'" );
        if ( $result->numRows() > 0 )
        {
            $droplet = $result->fetchRow( MYSQL_ASSOC );
            $name    = $droplet[ "name" ];
            $info[]  = 'Droplep: ' . $name . '.php<br />';
            $sFile   = $temp_dir . $name . '.php';
            $fh      = fopen( $sFile, 'w' );
            fwrite( $fh, '//:' . $droplet[ 'description' ] . "\n" );
            fwrite( $fh, '//:' . str_replace( "\n", " ", $droplet[ 'comments' ] ) . "\n" );
            fwrite( $fh, $droplet[ 'code' ] );
            fclose( $fh );
            $file = NULL;
            // look for a data file
            if ( file_exists( dirname( __FILE__ ) . '/data/' . $droplet[ 'name' ] . '.txt' ) )
            {
                $file = $admin->get_helper( 'Directory' )->sanitizePath( dirname( __FILE__ ) . '/data/' . $droplet[ 'name' ] . '.txt' );
            }
            elseif ( file_exists( dirname( __FILE__ ) . '/data/' . strtolower( $droplet[ 'name' ] ) . '.txt' ) )
            {
                $file = $admin->get_helper( 'Directory' )->sanitizePath( dirname( __FILE__ ) . '/data/' . strtolower( $droplet[ 'name' ] ) . '.txt' );
            }
            elseif ( file_exists( dirname( __FILE__ ) . '/data/' . strtoupper( $droplet[ 'name' ] ) . '.txt' ) )
            {
                $file = $admin->get_helper( 'Directory' )->sanitizePath( dirname( __FILE__ ) . '/data/' . strtoupper( $droplet[ 'name' ] ) . '.txt' );
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

    $filename = 'dropleps';

    // if there's only a single droplet to export, name the zip-file after this droplet
    if ( count( $marked ) === 1 )
    {
        $filename = 'droplep_' . $name;
    }

    // add current date to filename
    $filename .= '_' . date( 'Y-m-d' );

    // while there's an existing file, add a number to the filename
    if ( file_exists( WB_PATH . '/modules/dropleps/export/' . $filename . '.zip' ) )
    {
        $n = 1;
        while ( file_exists( WB_PATH . '/modules/dropleps/export/' . $filename . '_' . $n . '.zip' ) )
        {
            $n++;
        }
        $filename .= '_' . $n;
    }

    $temp_file = sanitize_path( WB_PATH . '/temp/' . $filename . '.zip' );

    // create zip
    $archive   = $admin->get_helper( 'Zip', $temp_file )->config( 'removePath', $temp_dir );
    $file_list = $archive->create( $temp_dir );
    if ( $file_list == 0 )
    {
        list_dropleps( $admin->lang->translate( "Packaging error" ) . ' - ' . $archive->errorInfo( true ) );
    }
    else
    {
        $export_dir = sanitize_path( WB_PATH . '/modules/dropleps/export' );
        // create the export folder if it doesn't exist
        if ( !file_exists( $export_dir ) )
        {
            mkdir( $export_dir, 0777 );
        }
        if ( !copy( $temp_file, $export_dir . '/' . $filename . '.zip' ) )
        {
            echo '<div class="drfail">Unable to move the exported ZIP-File!</div>';
            $download = WB_URL . '/temp/' . $filename . '.zip';
        }
        else
        {
            unlink( $temp_file );
            $download = sanitize_url( WB_URL . '/modules/dropleps/export/' . $filename . '.zip' );
        }
    }

    rm_full_dir( $temp_dir );

    return $admin->lang->translate( 'Backup created' ) . '<br /><br />' . implode( "\n", $info ) . '<br /><br /><a href="' . $download . '">Download</a>';

} // end function export_dropleps()

/**
 *
 **/
function import_dropleps()
{
    global $admin, $parser, $database;

    $groups = $admin->get_groups_id();
    if ( !is_allowed( 'import_dropleps', $groups ) )
    {
        $admin->print_error( $admin->lang->translate( "You don't have the permission to do this" ) );
    }

    $problem = NULL;

    if ( count( $_FILES ) )
    {
        if ( !function_exists( 'dropleps_upload' ) )
        {
            @include_once( dirname( __FILE__ ) . '/include.php' );
        }
        list( $result, $data ) = dropleps_upload( 'file' );
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
            $problem = $admin->lang->translate( 'An error occurred when trying to import the Droplep(s)' ) . '<br /><br />' . $info;
        }
        else
        {
            list_dropleps( $admin->lang->translate( 'Successfully imported [{{count}}] Droplep(s)', array(
                 'count' => $data
            ) ) );
            return;
        }
    }

    $parser->output( 'import.lte', array(
         'problem' => $problem
    ) );

} // end function import_dropleps()

/**
 *
 **/
function delete_dropleps()
{
    global $admin, $parser, $database;

    $groups = $admin->get_groups_id();
    if ( !is_allowed( 'delete_dropleps', $groups ) )
    {
        $admin->print_error( $admin->lang->translate( "You don't have the permission to do this" ) );
    }

    $errors = array();

    // get all marked dropleps
    $marked = isset( $_POST[ 'markeddroplet' ] ) ? $_POST[ 'markeddroplet' ] : array();

    if ( isset( $marked ) && !is_array( $marked ) )
    {
        $marked = array(
             $marked
        );
    }

    if ( !count( $marked ) )
    {
        list_dropleps( $admin->lang->translate( 'Please mark some Dropleps to delete' ) );
        return; // should never be reached
    }

    foreach ( $marked as $id )
    {
        // get the name; needed to delete data file
        $query = $database->query( "SELECT name FROM " . TABLE_PREFIX . "mod_droplets WHERE id = '$id'" );
        $data  = $query->fetchRow( MYSQL_ASSOC );
        $database->query( "DELETE FROM " . TABLE_PREFIX . "mod_droplets WHERE id = '$id'" );
        if ( $database->is_error() )
        {
            $errors[] = $admin->lang->translate( 'Unable to delete droplep: {{id}}', array(
                 'id' => $id
            ) );
        }
        // look for a data file
        if ( file_exists( dirname( __FILE__ ) . '/data/' . $data[ 'name' ] . '.txt' ) )
        {
            @unlink( $admin->get_helper( 'Directory' )->sanitizePath( dirname( __FILE__ ) . '/data/' . $data[ 'name' ] . '.txt' ) );
        }
        elseif ( file_exists( dirname( __FILE__ ) . '/data/' . strtolower( $data[ 'name' ] ) . '.txt' ) )
        {
            @unlink( $admin->get_helper( 'Directory' )->sanitizePath( dirname( __FILE__ ) . '/data/' . strtolower( $data[ 'name' ] ) . '.txt' ) );
        }
        elseif ( file_exists( dirname( __FILE__ ) . '/data/' . strtoupper( $data[ 'name' ] ) . '.txt' ) )
        {
            @unlink( $admin->get_helper( 'Directory' )->sanitizePath( dirname( __FILE__ ) . '/data/' . strtoupper( $data[ 'name' ] ) . '.txt' ) );
        }
    }

    list_dropleps( implode( "<br />", $errors ) );
    return;

} // end function delete_dropleps()

/**
 * copy a droplep
 **/
function copy_droplep( $id )
{
    global $database, $admin;

    $groups = $admin->get_groups_id();
    if ( !is_allowed( 'modify_dropleps', $groups ) )
    {
        $admin->print_error( $admin->lang->translate( "You don't have the permission to do this" ) );
    }

    $query    = $database->query( "SELECT * FROM " . TABLE_PREFIX . "mod_droplets WHERE id = '$id'" );
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
    $found = $database->query( 'SELECT * FROM ' . TABLE_PREFIX . "mod_droplets WHERE name='$new_name'" );
    while ( $found->numRows() > 0 )
    {
        $new_name = $data[ 'name' ] . "_copy" . $i;
        $found    = $database->query( 'SELECT * FROM ' . TABLE_PREFIX . "mod_droplets WHERE name='$new_name'" );
        $i++;
    }

    // generate query
    $query = "INSERT INTO " . TABLE_PREFIX . "mod_droplets VALUES "
    //         ID      NAME         CODE              DESCRIPTION                            MOD_WHEN                     MOD_BY
		   . "(''," . "'$new_name', " . "'$code', " . "'" . $data[ 'description' ] . "', " . "'" . time() . "', " . "'" . $admin->get_user_id() . "', " . "1,1,1,0,'" . $data[ 'comments' ] . " )";

    // add new droplet
    $result = $database->query( $query );
    if ( !$database->is_error() )
    {
        $new_id = mysql_insert_id();
        return edit_droplep( $new_id );
    }
    else
    {
        echo "ERROR: ", $database->get_error();
    }
}

/**
 * edit a droplep
 **/
function edit_droplep( $id )
{
    global $admin, $parser, $database;

    $groups = $admin->get_groups_id();

    if ( $id == 'new' && !is_allowed( 'add_dropleps', $groups ) )
    {
        $admin->print_error( $admin->lang->translate( "You don't have the permission to do this" ) );
    }
    else
    {
        if ( !is_allowed( 'modify_dropleps', $groups ) )
        {
            $admin->print_error( $admin->lang->translate( "You don't have the permission to do this" ) );
        }
    }

    $problem  = NULL;
    $info     = NULL;
    $problems = array();

    if ( isset( $_POST[ 'cancel' ] ) )
    {
        return list_dropleps();
    }

    if ( $id != 'new' )
    {
        $query        = $database->query( "SELECT * FROM " . TABLE_PREFIX . "mod_droplets WHERE id = '$id'" );
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

    if ( isset( $_POST[ 'save' ] ) || isset( $_POST[ 'save_and_back' ] ) )
    {
        // check the code before saving
        if ( !check_syntax( stripslashes( $_POST[ 'code' ] ) ) )
        {
            $problem      = $admin->lang->translate( 'Please check the syntax!' );
            $data         = $_POST;
            $data['code'] = (htmlspecialchars($data['code']));
        }
        else
        {
            // syntax okay, check fields and save
            if ( $admin->get_post( 'name' ) == '' )
            {
                $problems[] = $admin->lang->translate( 'Please enter a name!' );
            }
            if ( $admin->get_post( 'code' ) == '' )
            {
                $problems[] = $admin->lang->translate( 'You have entered no code!' );
            }

            if ( !count( $problems ) )
            {
                $continue      = true;
                $title         = $admin->add_slashes( $admin->get_post( 'name' ) );
                $active        = $admin->get_post( 'active' );
                $show_wysiwyg  = $admin->get_post( 'show_wysiwyg' );
                $description   = $admin->add_slashes( $admin->get_post( 'description' ) );
                $tags          = array(
                    '<?php',
                    '?>',
                    '<?'
                );
                $content       = str_replace( $tags, '', $admin->get_post( 'code' ) );
                $comments      = $admin->add_slashes( $admin->get_post( 'comments' ) );
                $modified_when = time();
                $modified_by   = $admin->get_user_id();
                if ( $id == 'new' )
                {
                    // check for doubles
                    $query = $database->query( "SELECT * FROM " . TABLE_PREFIX . "mod_droplets WHERE name = '$title'" );
                    if ( $query->numRows() > 0 )
                    {
                        $problem  = $admin->lang->translate( 'There is already a droplep with the same name!' );
                        $continue = false;
                        $data     = $_POST;
                        $data['code'] = stripslashes( $_POST[ 'code' ] );
                    }
                    else
                    {
						$code  = $admin->add_slashes( $content );
						// generate query
						$query = "INSERT INTO " . TABLE_PREFIX . "mod_droplets VALUES "
							   . "(''," . "'$title', " . "'$code', " . "'$description', " . "'$modified_when', " . "'$modified_by', " . "'$active',1,1, '$show_wysiwyg', '$comments' )";
					    $result = $database->query( $query );
					    if ( $database->is_error() )
					    {
					        echo "ERROR: ", $database->get_error();
					    }
                        
                    }
                }
                else
                {
                    // Update row
                    $database->query( "UPDATE " . TABLE_PREFIX . "mod_droplets SET name = '$title', active = '$active', show_wysiwyg = '$show_wysiwyg', description = '$description', code = '"
                                    . $admin->add_slashes( $content )
                                    . "', comments = '$comments', modified_when = '$modified_when', modified_by = '$modified_by' WHERE id = '$id'"
                    );
                    // reload Droplep data
                    $query = $database->query( "SELECT * FROM " . TABLE_PREFIX . "mod_droplets WHERE id = '$id'" );
                    $data  = $query->fetchRow( MYSQL_ASSOC );
                }
                if ( $continue )
                {
                    // Check if there is a db error
                    if ( $database->is_error() )
                    {
                        $problem = $database->get_error();
                    }
                    else
                    {
                        if ( $id == 'new' || isset( $_POST[ 'save_and_back' ] ) )
                        {
                            list_dropleps( $admin->lang->translate( 'The Droplep was saved' ) );
                            return; // should never be reached
                        }
                        else
                        {
                            $info = $admin->lang->translate( 'The Droplep was saved' );
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

    $parser->output( 'edit.lte', array(
        'problem' => $problem,
        'info' => $info,
        'data' => $data,
        'id'   => $id,
        'name' => $data[ 'name' ]
    ) );
} // end function edit_droplep()

/**
 *
 **/
function edit_droplep_perms( $id )
{
    global $admin, $parser, $database;

    // look if user can set permissions
    $this_user_groups = $admin->get_groups_id();
    if ( !is_allowed( 'manage_perms', $this_user_groups ) )
    {
        $admin->print_error( $admin->lang->translate( "You don't have the permission to do this" ) );
    }

    $info = NULL;

    // get available groups
    $query = $database->query( 'SELECT group_id, name FROM ' . TABLE_PREFIX . 'groups ORDER BY name' );
    if ( $query->numRows() )
    {
        while ( $row = $query->fetchRow( MYSQL_ASSOC ) )
        {
            $groups[ $row[ 'group_id' ] ] = $row[ 'name' ];
        }
    }

    // save perms
    if ( isset( $_REQUEST[ 'save' ] ) || isset( $_REQUEST[ 'save_and_back' ] ) )
    {
        $edit = (
					  isset($_REQUEST['edit_groups'])
					? ( is_array($_REQUEST['edit_groups']) ? implode('|',$_REQUEST['edit_groups']) : $_REQUEST['edit_groups'] )
					: NULL
				);
        $view = (
					  isset($_REQUEST['view_groups'])
					? ( is_array($_REQUEST['view_groups']) ? implode('|',$_REQUEST['view_groups']) : $_REQUEST['view_groups'] )
					: NULL
				);
        $database->query( 'REPLACE INTO ' . TABLE_PREFIX . "mod_dropleps_permissions VALUES( '$id', '$edit', '$view' );" );
        $info = $admin->lang->translate( 'The Droplep was saved' );
        if ( isset( $_REQUEST[ 'save_and_back' ] ) )
        {
            return list_dropleps( $info );
        }
    }

    // get droplep data
    $query = $database->query( "SELECT * FROM " . TABLE_PREFIX . "mod_droplets AS t1 LEFT OUTER JOIN ".TABLE_PREFIX."mod_dropleps_permissions AS t2 ON t1.id=t2.id WHERE t1.id = '$id'" );
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
            'name' => $admin->lang->translate( $key )
        );
    }

    $parser->output( 'droplep_permissions.lte', array(
        'rows' => $rows,
        'info' => $info,
        'id'   => $id
    ) );

} // end function edit_droplep_perms()

/**
 * edit a droplep's datafile
 **/
function edit_datafile( $id )
{
    global $admin, $parser, $database;
    $info = $problem = NULL;

    $groups = $admin->get_groups_id();
    if ( !is_allowed( 'modify_dropleps', $groups ) )
    {
        $admin->print_error( $admin->lang->translate( "You don't have the permission to do this" ) );
    }

    if ( isset( $_POST[ 'cancel' ] ) )
    {
        return list_dropleps();
    }

    $query = $database->query( "SELECT name FROM " . TABLE_PREFIX . "mod_droplets WHERE id = '$id'" );
    $data  = $query->fetchRow( MYSQL_ASSOC );

    // find the file
    if ( file_exists( dirname( __FILE__ ) . '/data/' . $data[ 'name' ] . '.txt' ) )
    {
        $file = $admin->get_helper( 'Directory' )->sanitizePath( dirname( __FILE__ ) . '/data/' . $data[ 'name' ] . '.txt' );
    }
    elseif ( file_exists( dirname( __FILE__ ) . '/data/' . strtolower( $data[ 'name' ] ) . '.txt' ) )
    {
        $file = $admin->get_helper( 'Directory' )->sanitizePath( dirname( __FILE__ ) . '/data/' . strtolower( $data[ 'name' ] ) . '.txt' );
    }
    elseif ( file_exists( dirname( __FILE__ ) . '/data/' . strtoupper( $data[ 'name' ] ) . '.txt' ) )
    {
        $file = $admin->get_helper( 'Directory' )->sanitizePath( dirname( __FILE__ ) . '/data/' . strtoupper( $data[ 'name' ] ) . '.txt' );
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
            $info = $admin->lang->translate( 'The datafile has been saved' );
            if ( isset( $_POST[ 'save_and_back' ] ) )
            {
                return list_dropleps( $info );
            }
        }
        else
        {
            $problem = $admin->lang->translate( 'Unable to write to file [{{file}}]', array(
                 'file' => str_ireplace( $admin->get_helper( 'Directory' )->sanitizePath( WB_PATH ), 'WB_PATH', $file )
            ) );
        }
    }

    $parser->output( 'edit_datafile.lte', array(
         'info' => $info,
        'problem' => $problem,
        'name' => $data[ 'name' ],
        'id' => $id,
        'contents' => htmlspecialchars( $contents )
    ) );
} // end function edit_droplep()


/**
 *
 **/
function toggle_active( $id )
{
    global $admin, $parser, $database;

    $groups = $admin->get_groups_id();
    if ( !is_allowed( 'modify_dropleps', $groups ) )
    {
        $admin->print_error( $admin->lang->translate( "You don't have the permission to do this" ) );
    }

    $query = $database->query( "SELECT * FROM " . TABLE_PREFIX . "mod_droplets WHERE id = '$id'" );
    $data  = $query->fetchRow( MYSQL_ASSOC );

    $new = ( $data[ 'active' ] == 1 ) ? 0 : 1;

    $database->query( 'UPDATE ' . TABLE_PREFIX . "mod_droplets SET active='$new' WHERE id = '$id'" );

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
 *
 **/
function is_allowed( $perm, $gid )
{
    global $admin, $settings;
    // admin is always allowed to do all
    if ( $admin->get_user_id() == 1 )
    {
        return true;
    }
    if ( !array_key_exists( $perm, $settings ) )
    {
        return false;
    }
    else
    {
        $value = $settings[ $perm ];
        if ( !is_array( $value ) )
        {
            $value = array(
                 $value
            );
        }
        return is_in_array( $value, $gid );
    }
    return false;
} // end function is_allowed()

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
    global $admin, $database;
    $settings = array();
    $query    = $database->query( 'SELECT * FROM ' . TABLE_PREFIX . 'mod_dropleps_settings' );
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