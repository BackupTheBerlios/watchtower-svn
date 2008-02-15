<?php
/** 
 *	@file
 * 	see wtDBInstall() in wtDBUtil.php
 */


	if(!file_exists($file))
		return FALSE;
	include($file);
	if(!isset($DB))
		return FALSE;
		
	global $WT;
	if($db==NULL)
		$db=&$WT->DB;
	if(!$db->connected())
		return FALSE;
		
	if($module==NULL)
		$qmod="NULL";
	else
		$qmod="'".$module."'";
	
	
	
	foreach($DB as $table_id=>$table)
	{
		// Check if the table exists
		if(!isset($table['index']) || $table['index'])
		{
			$table_exists=$db->count("SELECT `tid` FROM `@P@table_index` WHERE `tid`='".$table_id."'");
			$table_exists=($table_exists>0?true:false);
		}
		else 
		{
			$table_exists=false; // Always treat non-indiced tables as not existing. 
			
			//$db->q("SHOW TABLES FROM `".$db->Name."` LIKE '@P@%s'",$table_id);
			//$table_exists=$db->fetch();
		}	
		
		if($table_exists) 
		{
			if(!isset($table['index']) || $table['index']) // The table is indiced.
			{
				$qs=array();			
				foreach($table['fields'] as $field_id=>$field)
				{
					$esc_field=mysql_real_escape_string($field);
					$db->q("SELECT `info` FROM `@P@table_field_index` WHERE `fid`='".$field_id."' AND `tid`='".$table_id."' LIMIT 1");				
					$field_exists=$db->fetch();
					if(!$field_exists) // This is a new field, because it has no record.
					{
						if($db->q("ALTER TABLE `@P@".$table_id."` ADD `".$field_id."` ".$field))
						{
							$qs[]="INSERT INTO `@P@table_field_index` (`fid`,`tid`,`info`) VALUES ('".$field_id."','".$table_id."','".$esc_field."')";
						}
					}
					else // This field is recorded already.
					{
						if($field_exists['info'] != $esc_field) // The recorded field has been manipulated. 
						{
							if($db->q("ALTER TABLE `@P@".$table_id."` MODIFY `".$field_id."` ".$field))
							{
								$qs[]="UPDATE `@P@table_field_index` SET `info`='".$esc_field."' WHERE `fid`='".$field_id."' AND `tid`='".$table_id."'";
							}
						}
					}
				}
				foreach($qs as $q) // Perform the queries.
				{
					$db->q($q);				
				}
			}			
		}
		else
		{
			$qs=array();
			$qs[0]="CREATE TABLE `@P@".$table_id."` (";
			if(!isset($table['index']) || $table['index'])
				$qs[1]="INSERT INTO `@P@table_index` (`tid`,`module`) VALUES ('".$table_id."',".$qmod.") ON DUPLICATE KEY UPDATE `module`=".$qmod;
			 
			$first=true;
			foreach($table['fields'] as $field_id=>$field)
			{
				$esc_field=mysql_real_escape_string($field);
					
				if($first)
					$first=false;
				else
					$qs[0].=", ";
				$qs[0].="`".$field_id."` ".$field;
				
				if(!isset($table['index']) || $table['index']) 
				{
					$qs[]="INSERT INTO `@P@table_field_index` (`fid`,`tid`,`info`) VALUES ('".$field_id."','".$table_id."','".$esc_field."')";				
				}
			}
			
			if(isset($table['primary_key']))
				$qs[0].=", PRIMARY KEY(`".$table['primary_key']."`)";
				
			$qs[0].=")";
			
			foreach($qs as $q) // Perform the queries
			{
				$db->q($q);				
			}
			
		}
	}

	return TRUE;

?>