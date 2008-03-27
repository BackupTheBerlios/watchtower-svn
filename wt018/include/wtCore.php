<?php
/**
 * @brief The Watchtower core class.
 * 
 * This class holds all information about Watchtower. This includes configurations,
 * and all other components integrated into the Watchtower core.
 * Normally you would create an instance of wtCore and fill it with the required information.
 * However, in most cases the function wtInit() will suit the needs in automating these processes.
 * If you are interested in the details of the initialization process, wtMain.php might be a good
 * resource.   
 * 
 * @see 
 *  wtInit() 
 *  wtMain.php   
 *  
 */
class wtCore
{
	var $Version="0.18"; // The core version 

	var $DBUrl;

	var $DB;
	var $DBConnections=array();

	var $CoreDir; // This is the directory where the core is installed.
	var $RootDir; // This is the root directory of the installation instance.
	var $ModuleDir; // Module installation directory;

	var $Hooks=array(); // Function hook lookup
	
	var $DevMode=false; // Developer mode, will slow the system down if enabled and is only recommended for testing purposes.
}

?>