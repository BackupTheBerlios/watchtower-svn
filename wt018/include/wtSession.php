<?php
/**
 * @file
 * Database session management
 */

function wtSessionOpen($save_path,$session_name)
{
	return true;
}

function wtSessionClose()
{
	return true;
}

function wtSessionRead($id)
{
	global $WT;
    $WT->DB->q("SELECT `data` FROM `@PREFIXsessions` WHERE `id`='%s'",$id);
    $r=$WT->DB->fetch();
	if($r)
    	return $r['data'];
    return "";
}

function wtSessionWrite($id,$sess_data)
{
	global $WT;
    $expires=$WT['CONFIG']['SESSION_EXPIRATION_TIME'];
    if($expires==0)
    	$expires=time()+get_cfg_var("session.gc_maxlifetime");

    $WT->DB->q("SELECT COUNT(*) as c FROM `@PREFIXsessions` WHERE `id`='%s'", $id);
    $r=$WT->DB->fetch();
    if($r['c'])
		$WT->DB->q("UPDATE `@PREFIXsessions` SET `data`='%s', `expires`=%s WHERE `id`='%s'", $sess_data, $expires, $id);
	else
    {
    	$WT->DB->q("INSERT INTO `@PREFIXsessions` (`id`,`data`, `expires`) VALUES ('%s','%s', %s)", $id, $sess_data, $expires);
    }

	return true;
}

function wtSessionDestroy($id)
{
	global $WT;
    $WT->DB->q("DELETE FROM `@PREFIXsessions` WHERE `id`='%s'",$id);
    echo $WT->DB->query;
	return true;
}

function wtSessionGC($maxlifetime)
{
	global $WT;
    $WT->DB->q("DELETE FROM `@PREFIXsessions` WHERE `expires`<%s",time() );
	return true;
}

?>