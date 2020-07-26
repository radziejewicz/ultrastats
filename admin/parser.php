<?php
/*
	*********************************************************************
	* Copyright by Andre Lorbach | 2006!								*
	* -> www.ultrastats.org <-											*
	*																	*
	* Use this script at your own risk!									*
	* -----------------------------------------------------------------	*
	* This is the main Parser File										*
	*																	*
	* -> This is some kind of a wrapper for the core core parser		*
	*																	*
	* All directives are explained within this file						*
	*********************************************************************
*/

// *** Default includes	and procedures *** //
define('IN_ULTRASTATS', true);
$gl_root_path = './../';
include($gl_root_path . 'include/functions_db.php');
include($gl_root_path . 'include/functions_common.php');
include($gl_root_path . 'include/class_template.php');

InitUltraStats();
CheckForUserLogin( false );
// ***					*** //

// --- CONTENT Vars
$content['TITLE'] = "Ultrastats - Admin Center - Parser";	// Title of the Page 
// --- 

// --- BEGIN Custom Code

// Now the processing Part
if ( isset($_GET['op']) )
	$content['parseroperation'] = DB_RemoveBadChars($_GET['op']);
else
	$content['parseroperation'] = "";

if ( isset($_GET['op']) )
{
	if ( isset($_GET['id']) && is_numeric($_GET['id']) )
	{
		$content['serverid'] = DB_RemoveBadChars($_GET['id']);

		// Get ServerDetails first 
		$result = DB_Query("SELECT * FROM " . STATS_SERVERS . " WHERE ID = " . $content['serverid']);
		$content['SERVER'] = DB_GetAllRows($result, true);
		$content['GameLogLocation'] = $content['SERVER'][0]['GameLogLocation'];
		$content['LastLogLine'] = $content['SERVER'][0]['LastLogLine'];

		if ( isset( $content['SERVER'] ) )
		{
			// Server found - now check for the action
			if (	$content['parseroperation'] == 'updatestats' || 
					$content['parseroperation'] == 'delete' || 
					$content['parseroperation'] == 'deletestats' || 
					$content['parseroperation'] == 'createaliases' || 
					$content['parseroperation'] == 'getnewlogfile' || 
					$content['parseroperation'] == 'resetlastlogline' 
				)
			{
				// Set Embedded Parser to True
				$content['RUNPARSER'] = "true";
			}
		}
		else
		{
			$content['ISERROR'] = "true";
			$content['ERROR_MSG'] = "*Error, Server with ID '$serverid' not found in database";
		}
	}
	else if ( 
				$content['parseroperation'] == 'runtotals' ||
				$content['parseroperation'] == 'createaliases' ||
				$content['parseroperation'] == 'calcmedalsonly' ||
				$content['parseroperation'] == 'databaseopt'
			) 
	{
		// Set Embedded Parser to True
		$content['RUNPARSER'] = "true";
	}
	else
	{
		$content['ISERROR'] = "true";
		$content['ERROR_MSG'] = "*Error, no or invalid Server ID given";
	}
}
// --- 

// --- Parsen and Output
IncludeLanguageFile( $gl_root_path . 'lang/' . $LANG . '/admin.php' );

InitTemplateParser();
$page -> parser($content, "admin/parser.html");
$page -> output(); 
// --- 

?>