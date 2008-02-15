<?php
/**
 * @file
 * The module manager class
 */
class wtModuleManager
{
	var $mDB;
	function wtModuleManager()
	{
		$this->mDB=&wtDBGetConnection("ModuleManager");
	}
}

?>