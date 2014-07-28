<?php

/**
 * This file is part of Black Cat CMS Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          Website Baker Project, LEPTON Project
 * @copyright       2004-2010, Website Baker Project
 * @copyright       2013, Black Cat Development
 * @link            http://blackcat-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 *
 */


// include class.secure.php to protect this file and the whole CMS!
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
// end include class.secure.php

if ( !defined('ORDERING_CLASS_LOADED') ) { define('ORDERING_CLASS_LOADED', true); }

// ====================================================================== 
// ! Load the other required class files if they are not already loaded   
// ====================================================================== 
require_once(CAT_PATH."/framework/class.database.php");

if(!class_exists('order'))
{
    class order
    {
    	private $table			= '';
    	private $order_field	= '';
    	private $id_field		= '';
    	private $common_field	= '';

    	// =====================
    	// ! Get the db values
    	// =====================
    	public function order($table, $order_field='position', $id_field = 'id', $common_field = '')
    	{
    		$this->table			= $table;
    		$this->order_field 		= $order_field;
    		$this->id_field			= $id_field;
    		$this->common_field		= $common_field;
    	}

    	// =================
    	// ! Move a row up
    	// =================
    	public function move_up($id)
    	{
    		global $database;

    		// get current record
    		$sql		 = 'SELECT `'.$this->order_field.'`';
    		if ($this->common_field != '')
    		{
    			$sql	.= ',`'.$this->common_field.'`';
    		}
    		$sql		.= ' FROM `'.$this->table.'` WHERE `'.$this->id_field.'`='.(int)$id;

    		if ( ($res_current = $database->query($sql)) != false )
    		{
    			if ( ($rec_current = $res_current->fetchRow()) != false )
    			{
    				// get previous record
    				$sql		 = 'SELECT `'.$this->id_field.'`,`'.$this->order_field.'` ';
    				$sql		.= 'FROM `'.$this->table.'` ';
    				$sql		.= 'WHERE (`'.$this->order_field.'`<'.$rec_current[$this->order_field].') ';
    				if($this->common_field != '')
    				{
    					$sql	.= 'AND (`'.$this->common_field.'`=\''.$rec_current[$this->common_field].'\') ';
    				}
    				$sql		.= 'ORDER BY `'.$this->order_field.'` DESC';
    				if( ($res_prev = $database->query($sql)) != false )
    				{
    					if( ($rec_prev = $res_prev->fetchRow()) != false )
    					{
    						// update current record
    						$sql		 = 'UPDATE `'.$this->table.'` ';
    						$sql		.= 'SET `'.$this->order_field.'`='.$rec_prev[$this->order_field].' ';
    						$sql		.= 'WHERE `'.$this->id_field.'`='.$id;
    						if( $database->query($sql) )
    						{
    							// update previous record
    							$sql	 = 'UPDATE `'.$this->table.'` ';
    							$sql	.= 'SET `'.$this->order_field.'`='.$rec_current[$this->order_field].' ';
    							$sql	.= 'WHERE `'.$this->id_field.'`='.$rec_prev[$this->id_field];
    							if( $database->query($sql) )
    							{
    								return true;
    							}
    						}
    					}
    				}
    			}
    		}
    		return false;
    	}

    	// ===================
    	// ! Move a row down
    	// ===================
    	public function move_down($id)
    	{
    		global $database;
    		// get current record
    		$sql		 = 'SELECT `'.$this->order_field.'`';
    		if($this->common_field != '')
    		{
    			$sql	.= ',`'.$this->common_field.'`';
    		}
    		$sql		.= ' FROM `'.$this->table.'` WHERE `'.$this->id_field.'`='.(int)$id;

    		if( ($res_current = $database->query($sql)) != false )
    		{
    			if( ($rec_current = $res_current->fetchRow()) != false )
    			{
    				// get next record
    				$sql		 = 'SELECT `'.$this->id_field.'`,`'.$this->order_field.'` ';
    				$sql		.= 'FROM `'.$this->table.'` ';
    				$sql		.= 'WHERE (`'.$this->order_field.'`>'.$rec_current[$this->order_field].') ';
    				if($this->common_field != '')
    				{
    					$sql	.= 'AND (`'.$this->common_field.'`=\''.$rec_current[$this->common_field].'\') ';
    				}
    				$sql		.= 'ORDER BY `'.$this->order_field.'` ASC';
    				if( ($res_next = $database->query($sql)) != false )
    				{
    					if( ($rec_next = $res_next->fetchRow()) != false )
    					{
    						// update current record
    						$sql	 = 'UPDATE `'.$this->table.'` ';
    						$sql	.= 'SET `'.$this->order_field.'`='.$rec_next[$this->order_field].' ';
    						$sql	.= 'WHERE `'.$this->id_field.'`='.$id;
    						if( $database->query($sql) )
    						{
    							// update next record
    							$sql	 = 'UPDATE `'.$this->table.'` ';
    							$sql	.= 'SET `'.$this->order_field.'`='.$rec_current[$this->order_field].' ';
    							$sql	.= 'WHERE `'.$this->id_field.'`='.$rec_next[$this->id_field];
    							if( $database->query($sql) )
    							{
    								return true;
    							}
    						}
    					}
    				}
    			}
    		}
    		return false;
    	}

    	// ============================
    	// ! Get new number for order
    	// ============================
    	public function get_new( $cf_value = '' )
    	{
    		global $database;

    		// Get last order
    		$sql		 = 'SELECT MAX(`'.$this->order_field.'`) FROM `'.$this->table.'` ';
    		if( $cf_value != '' )
    		{
    			$sql	.= 'WHERE `'.$this->common_field.'`=\''.$cf_value.'\'';
    		}

    		return intval( $database->get_one($sql) ) + 1;
    	}

    	// ==========================================================================================
    	// ! Clean ordering (should be called time by time if rows in the middle have been deleted)
    	// ==========================================================================================
    	public function clean( $cf_value = '' )
    	{
    		global $database;

    		// Loop through all records and give new order
    		$sql		 = 'SELECT `'.$this->id_field.'` FROM `'.$this->table.'` ';
    		if( $cf_value != '' )
    		{
    			$sql	.= 'WHERE `'.$this->common_field.'`=\''.$cf_value.'\'';
    		}
    		$sql		.= 'ORDER BY `'.$this->order_field.'` ASC';

    		if ( ($res_ids = $database->query($sql)) )
    		{
    			$count = 1;
    			while( ($rec_id = $res_ids->fetchRow()) )
    			{
    				$sql	 = 'UPDATE `'.$this->table.'` SET `'.$this->order_field.'`='.($count++).' ';
    				$sql	.= 'WHERE `'.$this->id_field.'`='.$rec_id[$this->id_field];
    				if ( !$database->query($sql) )
    				{
    					return false;
    				}
    			}
    			return true;
    		}
    		return false;
    	}

    	// ==========================================================================================
    	// ! Function for reordering all sections/pages given by an array
    	// ==========================================================================================
    	public function reorder_by_array($new_array)
    	{
    		global $database;

    		if(is_array($new_array))
    		{
    			$counter = 1;
    			foreach ( $new_array as $id )
    			{
                    if($id == '') { continue; }
    				if ( is_numeric( $id ) )
    				{
                        $q = sprintf(
                            'UPDATE `%s` SET `%s`="%s" WHERE `%s`="%s"',
                            $this->table, $this->order_field, ($counter++), $this->id_field, $id
                        );
    					$database->query($q);
    					if ( $database->is_error() )
    					{
    						return $database->get_error();
    					}
    				}
    				else
                    {
                        return false;
                    }
    			}
    			return true;
    		}
    		return false;
    	}   // end function reorder_by_array()

    }
}
?>