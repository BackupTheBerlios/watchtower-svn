<?php
/**
 *	@file
 * 	see wtDBInstall() in wtDBUtil.php
 */

	$result=true;
	if(!file_exists($file))
		$result=FALSE;
	include($file);
	if(!isset($DB))
		$result=FALSE;

	if($result)
	{
	global $WT;

	$coreDB=&$WT->DB;

	if($db==NULL)
		$db=&$WT->DB;
	if(!$db->connected())
		$result=FALSE;
		if($result)
		{

	if($module==NULL)
		$qmod="NULL";
	else
		$qmod="'".$module."'";

	foreach($DB as $table_id=>$table)
	{		// Check if the table exists
		if(!isset($table['index']) || $table['index'])
		{
			$table_exists=$coreDB->count("SELECT `tid` FROM `@P@table_index` WHERE `tid`='".$db->Prefix.$table_id."'");
			$table_exists=($table_exists>0?true:false);
		}
		else
		{
			//$table_exists=false; // Always treat non-indiced tables as not existing.
			$db->q("SHOW TABLES FROM `".$db->Name."` LIKE '@P@%s'",$table_id);
			$table_exists=$db->fetch();
		}
		
		if($table_exists)
		{
			if(!isset($table['index']) || $table['index']) // The table is indiced.
			{ 
				$qs=array();
				foreach($table['fields'] as $field_id=>$field)
				{
					$esc_field=mysql_real_escape_string($field);
										
					$coreDB->q("SELECT `info` FROM `@P@table_field_index` WHERE `fid`='".$field_id."' AND `tid`='".$db->Prefix.$table_id."' LIMIT 1");			
					$field_exists=$coreDB->fetch();
					
					if(!$field_exists) // This is a new field, because it has no record.
					{
						//if($coreDB->q("ALTER TABLE `@P@".$table_id."` ADD `".$field_id."` ".$field))
						if($db->q("ALTER TABLE `@P@".$table_id."` ADD `".$field_id."` ".$field))
						{
							$qs[]="INSERT INTO `@P@table_field_index` (`fid`,`tid`,`info`) VALUES ('".$field_id."','".$db->Prefix.$table_id."','".$esc_field."')";
						}	
					}
					else // This field is recorded already.
					{
						if($field_exists['info'] != $esc_field) // The recorded field has been manipulated.
						{
							//if($coreDB->q("ALTER TABLE `@P@".$table_id."` MODIFY `".$field_id."` ".$field))
							if($db->q("ALTER TABLE `@P@".$table_id."` MODIFY `".$field_id."` ".$field))
							{
								$qs[]="UPDATE `@P@table_field_index` SET `info`='".$esc_field."' WHERE `fid`='".$field_id."' AND `tid`='".$db->Prefix.$table_id."'";
							}
						}
					}
				}
				foreach($qs as $q) // Perform the queries.
				{
					//$db->q($q);
					$coreDB->q($q);
                    
				}
			}
            else
            {
                $db->q("SHOW COLUMNS FROM `@P@%s`",$table_id);
                $qs=array();
                $found_fields=array();
                while($row=$db->fetch())
                {
                	$name=$row['Field'];

                    $found=false;
                    foreach($table['fields'] as $field_id=>$field)
                    {
                    	if($field_id==$name)
                        {
                        	$found_fields[$field_id]=true;
                        	$qs[]="ALTER TABLE `@P@".$table_id."` MODIFY `".$field_id."` ".$field;
                        	$found=true;
                        	break;
                        }
                    }
                }

                foreach($table['fields'] as $field_id=>$field)
                {
                	if(!isset($found_fields[$field_id]))
                    	$qs[]="ALTER TABLE `@P@".$table_id."` ADD `".$field_id."` ".$field;
                }

                foreach($qs as $q) // Perform the queries.
				{
					$db->q($q);
				}

            }
		}
		else
		{
			$cqs=array();
			$qs=array();

			$qs[0]="CREATE TABLE `@P@".$table_id."` (";
			if(!isset($table['index']) || $table['index'])
				$cqs[]="INSERT INTO `@P@table_index` (`tid`,`module`,`database`,`prefix`) VALUES ('".$db->Prefix.$table_id."',".$qmod.",'".$db->Name."','".$db->Prefix."') ON DUPLICATE KEY UPDATE `module`=".$qmod.", `database`='".$db->Name."', `prefix`='".$db->Prefix."'";

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
					$cqs[]="INSERT INTO `@P@table_field_index` (`fid`,`tid`,`info`) VALUES ('".$field_id."','".$db->Prefix.$table_id."','".$esc_field."')";
				}
			}

			if(isset($table['primary_key']))
				$qs[0].=", PRIMARY KEY(`".$table['primary_key']."`)";

			$qs[0].=")";

			$ok=true;
			foreach($qs as $q) // Perform the queries
			{
				if($ok)
				{
					$ok=$db->q($q);
					if(!$ok)
					{
						// Query error
					}
				}
			}

			if($ok)
			{
				foreach($cqs as $q)
				{
					$coreDB->q($q);
				}
			}
			else
			{
				
			}

		}
	}
	}
	}

	$result= TRUE;

?>