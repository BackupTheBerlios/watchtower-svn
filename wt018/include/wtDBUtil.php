<?php
/**
 * @file
 * Utility and installation routines for the database
 */
 
/**
 * The user has the ability to define more than one database in wtConfig.php.
 * Some modules may want to use their own databases instead of share it with the core.
 * In that case, the module will call wtDBGetConnection with the unique identifier
 * of the database. Now, if this database is defined in wtConfig.php, the function
 * will return it, otherwise the default database will be used.
 *
 * @param $id
 *		the identifier of the desired database.
 * @return
 *		the appropriate database connection, if exists, or the base connection used
 *		by the core.
 */ 
function wtDBGetConnection($id)
{
	global $WT;
	if(isset($WT->DBConnections[$id]))
		return $WT->DBConnections[$id];
	else
		return $WT->DB;
} 
  
/** 
 * Installs or updates the database table structure.
 * Watchtower uses an internal storage system to keep track of changes inside the database.
 * Therefore it's important not to make manual changes to the database and instead use this
 * function, because it could result in strange behaviours with the internal database records. 
 * 
 * @param $file
 *		The file containing the table structure
 *	@param $db
 *		The database object to use. If NULL, the core database will be used.
 * @param $module
 *		The module owning the database tables defined in $file.
 * @return 
 * 	TRUE on success, otherwise FALSE.
 */
function wtDBInstall($file, $db=NULL, $module=NULL)
{
	include(dirname(__FILE__)."/wtDBInstall.php");
	
}
	
?>