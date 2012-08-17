<?php

/**
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
 *
 * @reformatted     2011-10-26
 *
 */

class lepton_init_page
{
	public $title        = "Hello World";
	public $section_type = "wrapper";
	public $file_content = '';
	public $file_name 	 = "Hello-World.php";
	public $language 	 = "DE";
	public $url 		 = "";
	private $db 		 = NULL;
	private $errors 	 = array();
	private $query_str 	 = "";

	public function __construct( &$db )
	{
		$this->db = $db;

		$this->file_content = '<?php' . "\n";
		$this->file_content .= "\$page_id=%s;
require('../index.php');
?>";

	}

	public function build_page()
	{
		$fp = fopen( "../" . PAGES_DIRECTORY . "/hello-world.php", 'w' );
		if ( $fp ) {
			$content = sprintf( $this->file_content, '1' );
			fwrite( $fp, $content, strlen( $content ) );
			fclose( $fp );
		}

		$values = array(
			'page_id' => 1,
			'position' => 1,
			'module' => "wrapper",
			'block' => 1
		);

		$this->query_str = $this->__build_insert( "sections", $values );
		$this->db->query( $this->query_str );
		$this->__test_error();

		$values = array(
			'section_id' => 1,
			'page_id' => 1,
			'url' => $this->url,
			'height' => "800",
			'width' => '630',
			'wtype' => 'iframe'
		);

		$this->query_str = $this->__build_insert( "mod_wrapper", $values );
		$this->db->query( $this->query_str );
		$this->__test_error();

		$values = array(
			'link' => "/hello-world",
			'page_title' => "hello world",
			'menu_title' => "hello world",
			'page_trail' => 1,
			'root_parent' => 1,
			'searching' => 1,
			'admin_groups' => 1,
			'viewing_groups' => 1,
			'modified_by' => 1,
			'modified_when' => TIME(),
			'language' => $this->language,
			'visibility' => 'public',
			'menu' => '1',
			'target' => '_new',
			'description' => '',
			'keywords' => '',
			'admin_users' => '',
			'viewing_users' => ''
		);

		$this->query_str = $this->__build_insert( "pages", $values );
		$this->db->query( $this->query_str );
		$this->__test_error();
	}

	private function __build_insert( $table_name, &$values )
	{
		$q = "INSERT into `" . TABLE_PREFIX . $table_name . "` (`";
		$q .= implode( "`,`", array_keys( $values ) ) . "`) VALUES ('";
		$q .= implode( "','", array_values( $values ) ) . "')";
		return $q;
	}

	private function __test_error()
	{
		if ( $this->db->is_error() )
			die( "class init_page - error: <b>" . $this->db->get_error() . "</b>\n<br />Last query was: " . $this->query_str );
	}

}
?>