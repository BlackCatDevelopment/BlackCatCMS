<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 *
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          Website Baker Project, LEPTON Project
 * @copyright       2004-2010, Website Baker Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 *
 */


// include class.secure.php to protect this file and the whole CMS!
if (defined('LEPTON_PATH')) {
	include(LEPTON_PATH.'/framework/class.secure.php');
} else {
	$oneback = "../";
	$root = $oneback;
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= $oneback;
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) {
		include($root.'/framework/class.secure.php');
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}
// end include class.secure.php

// Load the other required class files if they are not already loaded
require_once(LEPTON_PATH."/framework/class.database.php");
require_once(LEPTON_PATH.'/framework/class.wb.php');



/**
 * pages class.
 *
 * @extends wb
 */
class pages extends wb
{
	public $current_page		= array(
			'id'		=> -1,
			'parent'	=> -1
	);
	public	$pages_editable;

	private $preferences		= array();
	private $permissions		= array();
	private $menu				= array();
	private $pages				= array();
	private $template_menu		= array();
	private $template_block		= array();

	public function __construct( $permission = false )
	{
		global $database;
		$this->db_handle = clone($database);

		$this->preferences		= array(
				'PAGE_LEVEL_LIMIT'		=> PAGE_LEVEL_LIMIT,
				'MANAGE_SECTIONS'		=> MANAGE_SECTIONS,
				'PAGE_TRASH'			=> PAGE_TRASH,
				'MULTIPLE_MENUS'		=> MULTIPLE_MENUS == false ? false : true
		);
		if ( isset( $permission ) && is_array( $permission ) )
		{
			$this->permissions		= array(
					'PAGES'					=> $permission['pages'],
					'DISPLAY_ADD_L0'		=> $permission['pages_add_l0'],
					'DISPLAY_ADD'			=> $permission['pages_add'],
					'PAGES_MODIFY'			=> $permission['pages_modify'],
					'PAGES_DELETE'			=> $permission['pages_delete'],
					'PAGES_SETTINGS'		=> $permission['pages_settings'],
					'DISPLAY_INTRO'			=> $permission['pages_intro']
			);
		}
	}

	/**
	 * make_list function.
	 *
	 * Completely rewritten function to get pages
	 *
	 * @access public
	 * @param int $parent (default: 0)
	 * @param bool $add_sections (default: false)
	 * @return void
	 */
	public function make_list( $parent = 0 , $add_sections = false )
	{
		// ===================================================
		// ! Get objects and vars from outside this function
		// ===================================================

		if ( $add_sections )
		{
			$sql_sections		 = '';
		}

		$sql		 = 'SELECT * FROM `'.TABLE_PREFIX.'pages` ';
		$sql		.= ( $parent != 0 )					? 'WHERE `parent` = '.$parent.' ' : '';
		$sql		.= ( PAGE_TRASH != 'inline' )		?
													( $parent != 0 ) ?
														'AND `visibility` != \'deleted\' ' :
														'WHERE `visibility` != \'deleted\' ' :
														'';
		$sql		.= 'ORDER BY `level` ASC, `position` DESC';

		// ===============================
		// ! Get page list from database
		// ===============================
		$get_pages		= $this->db_handle->query($sql);
		if ( $get_pages->numRows() > 0 )
		{
			$this->pages_editable		= true;
			$temp_values				= array();
			while ( $values = $get_pages->fetchRow( MYSQL_ASSOC ) )
			{
				$temp_values[$values['page_id']]				= $values;

				if ( $add_sections )
				{
					$sql_sections	.= ' OR page_id = ' . $values['page_id'];
				}

				if ( isset($temp_values[$values['parent']]) && is_array($temp_values[$values['parent']]) )
				{
					$temp_values[$values['parent']]		= array_merge( $temp_values[$values['parent']], array( 'is_parent' => ( $values['parent'] != $parent ? true : false ) ) );
				}
				else
				{
					$temp_values[$values['parent']]['is_parent']		= $values['parent'] != 0 ? true : false;
				}
				if ( !(isset($pages) && is_array($pages)) )
				{
					$pages			= array( $values['page_id'] );
					$pages_parent	= $values['parent'];
				}
				else if ( $values['parent'] == $parent )
				{
					array_splice( $pages, 0, 0, $values['page_id'] );
				}
				else {
					$key		= array_search( $values['parent'], $pages )+1; // "+1" to add the value BEHIND the parent page in the array
					array_splice( $pages, $key, count($pages), array_merge( array( $values['page_id'] ), array_slice( $pages, $key ) ) );
				}
			}

			if ( $add_sections && $sql_sections != '' )
			{
				$sql_sections	= 'SELECT page_id, section_id, name FROM `'.TABLE_PREFIX.'sections` WHERE ' . substr( $sql_sections, 4 ) . ' ORDER BY position';
				$sections		= $this->db_handle->query($sql_sections);
				if ( $sections->numRows() > 0 )
				{
					$sections_array	= array();
					while ( $section = $sections->fetchRow( MYSQL_ASSOC ) )
					{
						if ( !( isset( $sections_array[$section['page_id']] ) && is_array( $sections_array[$section['page_id']] ) ) )
						{
							$sections_array[$section['page_id']]						= array();
						}
						$sections_array[$section['page_id']][$section['section_id']]	= array(
							'section_id'	=> $section['section_id'],
							'name'			=> $section['name']
						);
					}
				}
			}

			$pages_array	= array();
			$level			= 0;
			foreach ( $pages as $key => $page )
			{
				$this->pages[$key]						= $temp_values[$page];
				$this->pages[$key]['page_link']			= substr($temp_values[$page]['link'],strripos($temp_values[$page]['link'],'/')+1);
				$this->pages[$key]['sections']			= isset($sections_array[$page])							? $sections_array[$page] : false;
				$this->pages[$key]['cookie']			= isset( $_COOKIE['pageid_'.$this->pages[$key]['page_id']] )	?  true : false;

				// ==================
				// ! Get user perms
				// ==================
				$admin_groups		= explode(',', str_replace('_', '', $this->pages[$key]['admin_groups']));
				$admin_users		= explode(',', str_replace('_', '', $this->pages[$key]['admin_users']));
				$in_group = FALSE;
				foreach ( $this->get_groups_id() as $cur_gid)
				{
					if (in_array($cur_gid, $admin_groups))
					{
						$in_group = TRUE;
					}
				}

				// =================================================
				// ! Check user perms and count for editable sites
				// =================================================
				if ( ($in_group) || is_numeric(array_search($this->get_user_id(), $admin_users) ) )
				{
					if ( $this->pages[$key]['visibility'] == 'deleted' )
					{
						$this->pages[$key]['editable']		= false; // $this->preferences['PAGE_TRASH'] == 'inline' ? true : false;
					}
					else if ( $this->pages[$key]['visibility'] != 'deleted' )
					{
						$this->pages[$key]['editable']		= true;
					}
				}
				else
				{
					if ( $this->pages[$key]['visibility'] == 'private')
					{
						continue;
					}
					else
					{
						$this->pages[$key]['editable']		= false;
					}
				}

				$this->pages[$key]['close_parent']		= $temp_values[$page]['level'] < $level ?
																( $level - $temp_values[$page]['level'] ) : 0;
				$level									= $temp_values[$page]['level'];
			}
		}
		return $this->pages;
	}

	/**
	 * get_child_pages function.
	 *
	 * Is not needed any more, as make_list can also get only one page layer - marked as deprecated
	 *
	 * @access public
	 * @param int $page_id (default: 0)
	 * @param string $field (default: '*')
	 * @return void
	 */
	public function get_child_pages( $page_id = 0, $field = '*' )
	{
		if ( $this->permissions['PAGES'] == true)
		{

			if ( !isset($this->child_pages['editable']) )
				$this->child_pages['editable']	= 0;

			if ( is_array( $field ) )
			{
				$get_field = implode(',', $field);
			}
			// only for security remove all
			else if ( $field != '*' )
			{
				$get_field = htmlentities($field);
			}
			else $get_field = '*';

			$get_child_pages = $this->db_handle->query('SELECT '.$get_field.' FROM `'.TABLE_PREFIX.'pages` WHERE `parent` = '.$page_id.' ORDER BY `position` ASC');

			if ( $get_child_pages->numRows() > 0)
			{
				$counter = 0;
				while ( $child_page = $get_child_pages->fetchRow( MYSQL_ASSOC ))
				{
					$this->child_pages[$counter]['page_id']			= $child_page['page_id'];
					$this->child_pages[$counter]['menu_title']		= $child_page['menu_title'];
					$this->child_pages[$counter]['page_title']		= $child_page['page_title'];
					$this->child_pages[$counter]['visibility']		= $child_page['visibility'];
					$this->child_pages[$counter]['cookie']			= isset( $_COOKIE['p'.$child_page['page_id']] ) ?  true : false;
					//$this->child_pages[$counter]['active']			= $this->page_is_active($page) ? true : false;
					$this->child_pages[$counter]['view_url']		= PAGES_DIRECTORY .$child_page['link']. PAGE_EXTENSION;

					if ( $child_page['visibility'] != 'deleted' )
					{
						$this->child_pages[$counter]['editable']	= true;
						$this->child_pages['editable']				= $this->child_pages['editable']+1;
					}

					// =================================
					// ! Check if page has child pages
					// =================================
					$check_for_childs = $this->db_handle->query('SELECT `page_id` FROM `'.TABLE_PREFIX.'pages` WHERE `parent` = '.$child_page['page_id']);
					$this->child_pages[$counter]['is_parent']		= ( $check_for_childs->numRows() > 0 ) ? true : false;

					$counter++;
				}

				return $this->child_pages;
			}
			else return false;
		}
		else return false;
	}


	/**
	 * pages_list function.
	 *
	 * Function to workout to which pages a new page can be added (for Dropdown)
	 *
	 * @access public
	 * @param int $parent (default: 0)
	 * @param int $level (default: 1)
	 * @return void
	 */
	public function pages_list($parent = 0, $level = 1) {

		global $TEXT;

		// ==================================================================================
		// ! If user can add a page to level = 0, add this option to the array parent first
		// ==================================================================================
		if ( $this->permissions['DISPLAY_ADD_L0'] == true || $level == 0)
		{
			$this->parent_page[0]['disabled']			= false;
			$this->parent_page[0]['level']				= 0;
			$this->parent_page[0]['id']					= 0;
			$this->parent_page[0]['menu_title']			= $TEXT['NONE'];
			$this->parent_page[0]['page_title']			= $TEXT['NONE'];
			$this->parent_page[0]['current_is_parent']	= false;
			$this->parent_page[0]['is_parent']			= ( $this->current_page['parent']	== 0 ) ? true : false;
			$this->parent_page[0]['is_current']			= ( $this->current_page['id']		== 0 ) ? true : false;
		}
		else $this->parent_page = array();

		$this->parent_list(0);

		return $this->parent_page;

	}


	/**
	 * parent_list function.
	 *
	 * Function to get all pages for a dropdown menu
	 *
	 * @access public
	 * @param int $parent (default: 0)
	 * @return void
	 */
	public function parent_list( $parent = 0 )
	{
		$get_pages = $this->db_handle->query("SELECT * FROM " . TABLE_PREFIX . "pages WHERE parent = '$parent' AND visibility!='deleted' ORDER BY position ASC");

		while ( $page = $get_pages->fetchRow( MYSQL_ASSOC ) )
		{
			if ( $this->page_is_visible($page) == false ) continue;
			// ===================================================================================
			// ! Stop users from adding pages with a level of more than the set page level limit
			// ===================================================================================
			if ( $page['level']+1 < PAGE_LEVEL_LIMIT)
			{
				// ==================
				// ! Get user perms
				// ==================
				$admin_groups		= explode(',', str_replace('_', '', $page['admin_groups']));
				$admin_users		= explode(',', str_replace('_', '', $page['admin_users']));
				$page_trail			= explode(',',$page['page_trail']);

				$in_group = false;
				foreach ( $this->get_groups_id() as $cur_gid )
				{
					if ( in_array($cur_gid, $admin_groups) )
					{
						$in_group = true;
					}
				}
				$this->parent_page[$page['page_id']]['disabled']			= ( ($in_group) || is_numeric( array_search($this->get_user_id(), $admin_users) ) ) ? false : true;
				$this->parent_page[$page['page_id']]['level']				= $page['level'];
				$this->parent_page[$page['page_id']]['id']					= $page['page_id'];
				$this->parent_page[$page['page_id']]['menu_title']			= $page['menu_title'];
				$this->parent_page[$page['page_id']]['page_title']			= $page['page_title'];
				$this->parent_page[$page['page_id']]['current_is_parent']	= in_array( $this->current_page['id'], $page_trail ) ? true : false;
				$this->parent_page[$page['page_id']]['is_direct_parent']	= ( $this->current_page['parent']	== $page['page_id'] ) ? true : false;
				$this->parent_page[$page['page_id']]['is_current']			= ( $this->current_page['id']		== $page['page_id'] ) ? true : false;
			}
			$this->parent_page		= $this->parent_list( $page['page_id'] );
		}
		return $this->parent_page;
	}



	/**
	 * get_addons function.
	 *
	 * Function to get all addons
	 *
	 * @access public
	 * @param int $selected (default: 1) - name or directory of the the addon to be selected in a dropdown
	 * @param string $type (default: '') - type of addon - can be an array
	 * @param string $function (default: '') - function of addon- can be an array
	 * @param string $permissions (default: '') - array(!) of directories to check permissions
	 * @param string $order (default: 'name') - value to handle "ORDER BY" for database request of addons
	 * @return void
	 */
	public function get_addons( $selected = 1 , $type = '', $function = '' , $permissions = '' , $order = 'name' )
	{
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

		$addons = $this->db_handle->query("SELECT * FROM " . TABLE_PREFIX . "addons WHERE ".$get_type.$get_function." ORDER BY ".htmlspecialchars( $order ) );
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
	 * get_groups function.
	 *
	 * Function to get all groups as viewers and as admins
	 *
	 * @access public
	 * @param array $viewing_groups (default: array())
	 * @param array $admin_groups (default: array())
	 * @param bool $insert_admin (default: true)
	 * @return void
	 */
	public function get_groups( $viewing_groups = array() , $admin_groups = array(), $insert_admin = true )
	{
		$groups				= false;
		$viewing_groups		= is_array( $viewing_groups )	? $viewing_groups	: array( $viewing_groups );
		$admin_groups		= is_array( $admin_groups )		? $admin_groups		: array( $viewing_groups );
		// ================
		// ! Getting Groups
		// ================
		$get_groups = $this->db_handle->query("SELECT * FROM " . TABLE_PREFIX . "groups");

		// ==============================================
		// ! Insert admin group and current group first
		// ==============================================
		$admin_group_name	= $get_groups->fetchRow( MYSQL_ASSOC );

		if ( $insert_admin )
		{
			$groups['viewers'][0] = array(
				'VALUE'		=> 1,
				'NAME'		=> $admin_group_name['name'],
				'CHECKED'	=> true,
				'DISABLED'	=> true
			);
			$groups['admins'][0] = array(
				'VALUE'		=> 1,
				'NAME'		=> $admin_group_name['name'],
				'CHECKED'	=> true,
				'DISABLED'	=> true
			);
		}

		$counter	= 1;
		while ( $group = $get_groups->fetchRow( MYSQL_ASSOC ) )
		{
			$system_permissions			= explode( ',', $group['system_permissions']);
			array_unshift( $system_permissions, 'placeholder' );
			$module_permissions			= explode( ',', $group['module_permissions']);
			array_unshift( $module_permissions, 'placeholder' );
			$template_permissions			= explode( ',', $group['template_permissions']);
			array_unshift( $template_permissions, 'placeholder' );

			$groups['viewers'][$counter]		=	array(
				'VALUE'					=> $group['group_id'],
				'NAME'					=> $group['name'],
				'CHECKED'				=> is_numeric( array_search($group['group_id'], $viewing_groups) )	? true : false,
				'DISABLED'				=> in_array( $group["group_id"], $this->get_groups_id() )			? true : false,
				'system_permissions'	=> array_flip( $system_permissions ),
				'module_permissions'	=> array_flip( $module_permissions ),
				'template_permissions'	=> array_flip( $template_permissions )
			);

			// ===============================================
			// ! Check if the group is allowed to edit pages
			// ===============================================
			$system_permissions = explode(',', $group['system_permissions']);
			if ( is_numeric( array_search('pages_modify', $system_permissions) ) )
			{
				$groups['admins'][$counter]		=	array(
					'VALUE'					=> $group['group_id'],
					'NAME'					=> $group['name'],
					'CHECKED'				=> is_numeric( array_search($group['group_id'], $admin_groups) )	? true : false,
					'DISABLED'				=> in_array( $group["group_id"], $this->get_groups_id() )			? true : false,
					'system_permissions'	=> array_flip( explode(',',$group['system_permissions']) ),
					'module_permissions'	=> array_flip( explode(',',$group['module_permissions']) ),
					'template_permissions'	=> array_flip( explode(',',$group['template_permissions']) )
				);
			}
			$counter++;
		}
		return $groups;
	}

	/**
	 * get_template_menus function.
	 *
	 * Function to get all menus of an template
	 *
	 * @access public
	 * @param mixed $template (default: DEFAULT_TEMPLATE)
	 * @param int $selected (default: 1)
	 * @return void
	 */
	public function get_template_menus( $template = DEFAULT_TEMPLATE , $selected = 1)
	{
		global $TEXT;

		// =============================================
		// ! Include template info file (if it exists)
		// =============================================
		if ( $this->preferences['MULTIPLE_MENUS'] != false )
		{
			$template_location = ( $template != '') ?
				LEPTON_PATH . '/templates/' . $template . '/info.php' :
				LEPTON_PATH . '/templates/' . DEFAULT_TEMPLATE . '/info.php';

			if ( file_exists($template_location) )
			{
				require($template_location);
			}
			// =========================
			// ! Check if $menu is set
			// =========================
			if ( !isset($menu[1]) || $menu[1] == '' )
			{
				$menu[1]	= $TEXT['MAIN'];
			}

			// ================================
			// ! Add menu options to the list
			// ================================
			foreach ( $menu AS $number => $name )
			{
				$this->template_menu[$number] = array(
					'NAME'			=> $name,
					'VALUE'			=> $number,
					'SELECTED'		=> ( $selected == $number || $selected == $name ) ? true : false
				);
			}
			return $this->template_menu;
		}
		else return false;
	}


	/**
	 * get_template_blocks function.
	 *
	 * Function to get all menus of an template
	 *
	 * @access public
	 * @param mixed $template (default: DEFAULT_TEMPLATE)
	 * @param int $selected (default: 1)
	 * @return void
	 */
	public function get_template_blocks( $template = DEFAULT_TEMPLATE , $selected = 1)
	{
		global $TEXT;

		// =============================================
		// ! Include template info file (if it exists)
		// =============================================
		if ( SECTION_BLOCKS != false )
		{
			$template_location = ( $template != '' ) ?
				LEPTON_PATH . '/templates/' . $template . '/info.php' :
				LEPTON_PATH . '/templates/' . DEFAULT_TEMPLATE . '/info.php';

			if ( file_exists($template_location) )
			{
				require($template_location);
			}

			// =========================
			// ! Check if $menu is set
			// =========================
			if ( !isset($block[1]) || $block[1] == '' )
			{
				$block[1]	= $TEXT['MAIN'];
			}

			// ================================
			// ! Add menu options to the list
			// ================================
			foreach ( $block AS $number => $name )
			{
				$this->template_block[$number] = array(
					'NAME'			=> $name,
					'VALUE'			=> $number,
					'SELECTED'		=> ( $selected == $number || $selected == $name ) ? true : false
				);
				if ( $selected == $number || $selected == $name )
				{
					$this->current_block	= array(
						'name'		=> $name,
						'id'		=> $number
					);
				}
			}
			return $this->template_block;
		}
		else return false;
	}

}

?>