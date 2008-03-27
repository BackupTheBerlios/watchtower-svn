<?php
/**
 * @file
 * Watchtower main script
 * 
 * This script is called by wtInit().
 * All necessary initialisation processes are performed here. 
 * 
 * @see 
 *  wtInit()   
 */
 
// This has to be included first, because we need wtDir()
include( dirname(__FILE__)."/wtUtil.php" );

// Include the core
include( dirname(__FILE__)."/wtCore.php" );

$WT=new wtCore();

// Setup the core
$WT->CoreDir=wtDir(dirname(__FILE__)."/../");
$WT->CoreUrl="";
$WT->RootDir=$rootDir."/";
$WT->RootUrl=$rootUrl."/";
$WT->ModuleDir=$rootDir."/modules/";
$WT->ModuleUrl=$rootUrl."/modules/";

unset($WTRootDir);

// Include all files
include( $WT->CoreDir."include/wtModule.php");
include( $WT->CoreDir."include/wtModuleManager.php");
include( $WT->CoreDir."include/wtDB.php" );
include( $WT->CoreDir."include/wtRegistry.php");
include( $WT->CoreDir."include/wtUser.php");
include( $WT->CoreDir."include/wtDBUtil.php" );
include( $WT->CoreDir."include/wtHook.php");
include( $WT->CoreDir."include/wtTheme.php");
include( $WT->CoreDir."include/wtThemeUtil.php");

// Include configuration
include( $WT->RootDir."include/wtConfig.php");

// Connect to the database
$WT->DB=new wtDB();
if(is_array($WT->DBUrl))
{
	$first=true;

	foreach($WT->DBUrl as $id=>$url)
	{
		$WT->DBConnections[$id]=new wtDB();
		$WT->DBConnections[$id]->connect($url);
		if($first)
		{
			$WT->DB=&$WT->DBConnections[$id];
			$first=false;
		}
	}

	unset($id);
	unset($url);
	unset($first);
}
else
	$WT->DB->connect($WT->DBUrl);
if(!$WT->DB->connected())
{
	// TODO: Display database connection error message.
}

$WT->ModuleManager=new wtModuleManager();

// Is the core already installed?
$ok=wtRegGetKey("Core/Installed");

if(!$ok || $WT->DevMode) // Appearantly not, (re)install it.
{
	if( wtDBInstall( $WT->CoreDir."include/wtCoreDB.php") ) // Install the core database.
	{
		wtUserGroupCreate("administrators");
		wtUserCreate("admin","admin","administrators");
		wtRegSetKey("Core/Installed", true);

		if(wtDBInstall( $WT->CoreDir."include/wtCoreModuleDB.php", wtDBGetConnection("Core") )) // Install the module database.
		{
			include($WT->CoreDir."include/wtModInstall.php");
			wtInstallModulesInDirectory($WT->CoreDir."modules"); // Finally, install the core modules.
			wtBuildModuleWeights();
		}
	}
}

unset($ok);

$WT->Theme=wtLoadTheme(NULL);

$WT->ModuleManager->read();

$WT->ModuleManager->preInit();
$WT->ModuleManager->postInit();

if(wtCallHook("Theme/OnCall", NULL)!==FALSE)
  $WT->Theme->call();


//$WT->ModuleManager->call("user");





?>