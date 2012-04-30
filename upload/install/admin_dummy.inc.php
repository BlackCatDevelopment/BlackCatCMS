<?php

/**
 *	Dummy class to allow modules' install scripts to call $admin->print_error
 *
 */
class admin_dummy
{
	/**
	 *	Public var that holds the message
	 *
	 */
	public $error='';
	public $lang='';
	
	public function __construct() {
	    include dirname(__FILE__).'/../framework/LEPTON/Helper/I18n.php';
	    $this->lang = new LEPTON_Helper_I18n();
	}

	/**
	 *	Public function to "setup" the message.
	 *
	 *	@param	string	Any message-string.
	 *	@return	nothing
	 */
	public function print_error($message)
	{
		$this->error=$message;
	}

	/**
	 *	Need for e.g. installing dropleps.
	 *
	 */
	public function get_user_id()
	{
		return 1;
	}
}
