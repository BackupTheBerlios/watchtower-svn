<?php
/**
 * @file
 * Registry functions
 */

function wtRegGetKey($path)
{
	global $WT;
	$WT->DB->q("SELECT `value` FROM `@P@registry` WHERE `path`='%s'", $path);
	$r=$WT->DB->fetch();
	return $r['value'];
}

function wtRegSetKey($path, $value)
{
	global $WT;
	if($value==NULL)
		$WT->DB->q("DELETE FROM `@P@registry` WHERE `path`='%s'",$path);	
	else
		$WT->DB->q("INSERT INTO `@P@registry` (`path`,`value`) VALUES ('%s','%s') ON DUPLICATE KEY UPDATE `value`='%s'", $path, $value, $value);
}

?>