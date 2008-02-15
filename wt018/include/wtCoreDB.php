<?php
/**
 *	@file
 *		This file contains the core database structure as needed for wtDBInstall().
 */

$DB=array(
	'table_index'=>array( // Table table_index
		'fields'=>array(
			'tid'=>"VARCHAR(255) NOT NULL",
			'module'=>"VARCHAR(255) NULL"
		),
		'primary_key'=>"tid",
		'index'=>false // Since this is the index table, do not self-index.
	),
	
	'table_field_index'=>array( // Table table_field_index
		'fields'=>array(
			'rid'=>"INT NOT NULL AUTO_INCREMENT",
			'fid'=>"VARCHAR(255) NOT NULL",
			'tid'=>"VARCHAR(255) NOT NULL",
			'info'=>"VARCHAR(255) NULL",
		),
		'primary_key'=>"rid",
		'index'=>false // Since this is the index table, do not self-index.
	),
		
	'registry'=>array( // Table registry
		'fields'=>array( 
			'path'=>"VARCHAR(255) NOT NULL",
			'value'=>"VARCHAR(255) NULL"
			),
		'primary_key'=>"path"
	),
	
	'users'=>array( // Table users
		'fields'=>array(
			'name'=>"VARCHAR(255) NOT NULL",
			'password'=>"VARCHAR(255) NULL"
			),
		'primary_key'=>"name"
	),

	'user_groups'=>array( // Table user_groups
		'fields'=>array(
			'gid'=>"INT NOT NULL AUTO_INCREMENT",
			'name'=>"VARCHAR(255) NOT NULL"
			),
		'primary_key'=>"gid"
	),
	
	'user_group_members'=>array( // Table user_group_members
		'fields'=>array(
			'rid'=>"INT NOT NULL AUTO_INCREMENT",
			'gid'=>"INT NOT NULL",
			'user'=>"VARCHAR(255) NOT NULL"
		),
		'primary_key'=>"rid"
	)
);


?>