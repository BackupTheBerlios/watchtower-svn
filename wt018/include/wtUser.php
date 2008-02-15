<?php
/**
 * @file
 * User related functions
 */
 
function wtUserLoginStatus($user=NULL)
{
	if($user==NULL) // Self
	{
		
	}
	
	else
	{
	}
}

/**
 * Creates a new user *
 * @param $name
 *		the name of the new user (case-sensitive)
 * @param $password
 *		the user password.
 *	@param $groups
 *		an array of initial groups the user belongs to or NULL for no groups.
 * @return
 *		A positive value on success, -1 if the user already exists, FALSE on error
 */
function wtUserCreate($name, $password, $groups=NULL)
{
	global $WT;
	if( wtUserExists($name) )
		return -1;

 	$pw_hash=wtHash($password);
 	
 	if(!$WT->DB->q("INSERT INTO `@P@users` (`name`,`password`) VALUES ('%s','%s')",$name, $pw_hash))
 		return false;
 	
 	if($groups!==NULL)
 	{
 		if(!is_array($groups))
 			$groups=array($groups);
 		
 		foreach($groups as $group)
 			wtUserAssignToGroup($group,$name);		
 	}
 		 	
}

/** 
 * Checks if a user exists in the database
 *
 * @param $name
 *		The user name
 * @return
 * 	TRUE if a record of this users exists in the database, else FALSE.
 */
function wtUserExists($name)
{
	global $WT;
	if ($WT->DB->count("SELECT `name` FROM `@P@users` WHERE `name`='%s'", $name) > 0)
		return true;
	else
		return false;
}

/**
 * Assigns a user to a group
 * 
 * @param $group
 *		The name of the group to assign the user to
 *	@return
 * 	TRUE on success, -1 if the user is already assigned to this group, FALSE on error
 */

function wtUserAssignToGroup($group, $user=NULL)
{
	if( !wtUserExists($user) )
		return -1;
		
	global $WT;
	$WT->DB->q("SELECT `gid` FROM `@P@user_groups` WHERE `name`='%s' LIMIT 1", $group);
	$gid=$WT->DB->fetch();
	if(!$gid)
		return FALSE;
	$gid=$gid['gid'];
	
	if( $WT->DB->count("SELECT `rid` FROM `@P@user_group_members` WHERE `user`='%s' AND `gid`='%s'", $user, $gid) > 0)
		return -1;
		
	$WT->DB->q("INSERT INTO `@P@user_group_members` (`gid`,`user`) VALUES ('%s','%s')",$gid, $user);
}

/**
 * Creates a new user group *
 * @param $name
 *		the name of the new group
 * @param $desc
 *		Optional: The group description
 * @return
 *		An integer containing the unique group identifier (GID), -1 if the group already exists, FALSE on error
 */
function wtUserGroupCreate($name, $desc="")
{
	global $WT;
	if( $WT->DB->count("SELECT `name` FROM `@P@user_groups` WHERE `name`='%s'", $name) > 0)
	{
		return -1;
	}
	
	if(!$WT->DB->q("INSERT INTO `@P@user_groups` (`name`,`description`) VALUES ('%s','%s')",$name, $desc))
 		return false;	
 	
 	$WT->DB->q("SELECT `gid` FROM `@P@user_groups` ORDER BY `gid` DESC LIMIT 1");
 	$gid = $WT->DB->fetch();
 	if(!$gid)
 		return false;
 	return $gid['gid']; 	
}

?>