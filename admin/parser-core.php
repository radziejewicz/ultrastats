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
	* -> This is the core of the parser, highly l33t part				*
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
CheckForUserLogin( true );
// ***					*** //

// --- BEGIN Custom Code

// Additional Includes
include($gl_root_path . 'include/functions_parser.php');
include($gl_root_path . 'include/functions_parser-helpers.php');
include($gl_root_path . 'include/functions_parser-medals.php');
include($gl_root_path . 'include/functions_parser-consolidation.php');

// Include languages as well!
IncludeLanguageFile( $gl_root_path . 'lang/' . $LANG . '/admin.php' );

// Now the processing Part
if ( isset($_GET['op']) )
	$parseroperation = DB_RemoveBadChars($_GET['op']);
else
	$parseroperation = "";

if ( isset($_GET['op']) )
{
	if ( isset($_GET['id']) && is_numeric($_GET['id']) )
	{
		$serverid = DB_RemoveBadChars($_GET['id']);

		// Get ServerDetails first 
		$result = DB_Query("SELECT * FROM " . STATS_SERVERS . " WHERE ID = " . $serverid);
		$serverdetails = DB_GetAllRows($result, true);

		if ( isset( $serverdetails ) )
		{
			// Get Parser OP and Set myserver ref!
			$myserver = $serverdetails[0];

			// Set StartTime
			$ParserStart = microtime_float();

			// From here the Parsing Operation Starts!
			CreateHTMLHeader();

			// Set MaxExecutionTime first!
			SetMaxExecutionTime();

			// Server found - now check for the action
			if		( $parseroperation == 'updatestats' )
			{
				// Run Parser from here!
				RunParserNow();

				print ('<br><center><a href="parser.php?op=runtotals" target="_top"><img src="' . $content["BASEPATH"] . 'images/icons/gears_run.png">&nbsp; ' . $content["LN_RUNTOTALUPDATE"] . '</a></center>');
			}
			else if ( $parseroperation == 'delete' )
			{
				// Delete Server
				DeleteServer();
			}
			else if ( $parseroperation == 'deletestats' )
			{
				// Delete Server Stats
				DeleteServerStats();
			}
			else if ( $parseroperation == 'resetlastlogline' )
			{
				// Reset last line
				ResetLastLine();
			}
			else if ( $parseroperation == 'getnewlogfile' )
			{
				// Reset last line
				GetLastLogFile();
			}
			else if ( $parseroperation == 'createaliases' )
			{
				//Run Calc for TOPAliases
				CreateTopAliases( $myserver['ID'] );
			}
			else
			{
				DieWithErrorMsg("Error, empty or unknown Action specified - '" . $parseroperation . "'!");
			}

			//Terminate Websitefooter
			CreateHTMLFooter();
		}
		else
			DieWithErrorMsg("Error, Server with ID '$serverid' not found in database");
	}
	else if ( 
				$parseroperation == 'runtotals' ||
				$parseroperation == 'createaliases' ||
				$parseroperation == 'calcmedalsonly' ||
				$parseroperation == 'databaseopt'
			)
	{
		// From here the Parsing Operation Starts!
		CreateHTMLHeader();

		// Set MaxExecutionTime first!
		SetMaxExecutionTime();

		if ( $parseroperation == 'runtotals' )
		{
			// To calc aliases and stuff 
			RunTotalStats();
		}
		else if ( $parseroperation == 'createaliases' )
		{
			// Set StartTime
			$ParserStart = microtime_float();

			// Create New Aliases!
			ReCreateAliases();
		}
		else if ( $parseroperation == 'calcmedalsonly' )
		{
			// Set StartTime
			$ParserStart = microtime_float();

			//Run the Medals Generation now!
			CreateAllMedals( -1 );
		}
		else if ( $parseroperation == 'databaseopt' )
		{
			// Optimize SQL Tables
			OptimizeAllTables();
		}

		//Terminate Websitefooter
		CreateHTMLFooter();
	}
	else
		DieWithErrorMsg("Error, no or invalid Server ID given");
}
// --- 

?>