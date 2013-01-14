<?php

/**
 *
 * @module          initial_page
 * @author          Ralf Hertsch, Dietrich Roland Pehlke 
 * @copyright       2010-2011, Ralf Hertsch, Dietrich Roland Pehlke
 * @link            http://www.LEPTON-cms.org
 * @license         copyright, all rights reserved
 * @license_terms   please see info.php of this module
 *
 *
 */
 
 // include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {	
	include(WB_PATH.'/framework/class.secure.php'); 
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



class patchStartPage {
	
	private $temp_file;
	private $backup_file;
	private $original_file;
	
	public function __construct() {
		$this->temp_file = ADMIN_PATH .'/start/index.php.tmp';
		$this->original_file= ADMIN_PATH .'/start/index.php';
		$this->backup_file = ADMIN_PATH .'/start/index.php.bak';	
	}
	
	/**
	 * check if /backend/start/index.php is patched
	 *
	 * @param STR $filename
	 * @return BOOL
	 */
	public function isPatched() {
		if (file_exists($this->original_file)) {	
			$lines = file($this->original_file);
			foreach ($lines as $line) {
				if (strpos($line , "initial_page" ) > 0)
					return true;
			}
			return false;
		}
		return false;
	} // isPatched()

	/**
	 * unpatch /start/index.php
	 *
	 * @return BOOL
	 */
	function unPatch() {
		if (!file_exists($this->backup_file)) {
			return false;  // No backup, can't do anything
		}
		if (file_exists($this->temp_file)) unlink($this->temp_file);
		if (rename($this->original_file, $this->temp_file)) {
			if (rename($this->backup_file, $$this->original_file)) {
				unlink($this->temp_file);
				return true;
			} 
			else { 
				return false;
			}
		} 
		else {
			return false;
		}
	}

	/**
	 * insert patch into /backend/start/index.php
	 *
	 * @return BOOL
	 */
	function doPatch() {
		$returnvalue = false;		
		$addline = "\n\n// exec initial_page ";
		$addline .= "\n".'if(file_exists(WB_PATH .\'/modules/initial_page/classes/c_init_page.php\') && isset($_SESSION[\'USER_ID\'])) { ';
		$addline .= "\n\trequire_once (WB_PATH .'/modules/initial_page/classes/c_init_page.php'); ";
		$addline .= "\n\t".'$ins = new c_init_page($database, $_SESSION[\'USER_ID\'], $_SERVER[\'SCRIPT_NAME\']);';
		$addline .= "\n}\n\n";
		if(file_exists($this->original_file)) {	
			$lines = file ($this->original_file);
			$handle = fopen ($this->temp_file, 'w');
			if ($handle !== false) {
				foreach ($lines as $line) {
					if (fwrite ($handle, $line) == true) { 
						if (strpos($line, "require('../../config.php');" ) === 0) { 
							$returnvalue = true;
							fwrite($handle, $addline);
						}
					}
					else {
						fclose($handle);
						return false;	
					}
				}
				fclose ($handle);
				if (rename($this->original_file, $this->backup_file)) { 
					if (rename($this->temp_file, $this->original_file)) { 
						return $returnvalue;
					} 
					else { 
						return false;
					}
				}
			}
		}
		return false;
	} // doPatch()
	
} // class patch_start_page