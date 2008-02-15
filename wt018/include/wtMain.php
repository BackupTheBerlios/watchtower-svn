<?php
/**
 * @file
 * Watchtower main script
 */

// Include the core
include( dirname(__FILE__)."/wtCore.php" );
$WT=new wtCore();
$WT->IncludeDir=dirname(__FILE__)."/";

// Include all files
include( $WT->IncludeDir."wtUtil.php" );
include( $WT->IncludeDir."wtModuleManager.php");
include( $WT->IncludeDir."wtDB.php" );
include( $WT->IncludeDir."wtRegistry.php");
include( $WT->IncludeDir."wtUser.php");
include( $WT->IncludeDir."wtDBUtil.php" );

// Include configuration
include( dirname(__FILE__)."/wtConfig.php");

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

// Is the database okay? 
$ok=wtRegGetKey("Core/Installed");
if(!$ok) // Appearantly not, (re)install it.
{
	
	if( wtDBInstall( $WT->IncludeDir."wtCoreDB.php") )
	{
		wtUserGroupCreate("administrators");
		wtUserCreate("admin","admin","administrators");	
		wtRegSetKey("Core/Installed", true);
	}
}
unset($ok);

// Setup the core
$WT->Dir=$WT->IncludeDir."../";
$WT->Url="";

$WT->ModuleManager=new wtModuleManager();

?>