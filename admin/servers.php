<?php
/*
	*********************************************************************
	* Copyright by Andre Lorbach | 2006!								*
	* -> www.ultrastats.org <-											*
	*																	*
	* Use this script at your own risk!									*
	* -----------------------------------------------------------------	*
	* Server Edit Admin File											*
	*																	*
	* -> Helps to admin and manage Servers in UltraStats		*
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

IncludeLanguageFile( $gl_root_path . 'lang/' . $LANG . '/admin.php' );
// ***					*** //

// --- CONTENT Vars
$content['TITLE'] = "Ultrastats - Admin Center - Servers";	// Title of the Page 
// --- 

// --- BEGIN Custom Code
if ( isset($_GET['op']) )
{
	if ($_GET['op'] == "add") 
	{
		// Set Mode to add
		$content['ISEDITORNEWSERVER'] = "true";
		$content['SERVER_FORMACTION'] = "addnewserver";
		$content['SERVER_SENDBUTTON'] = $content['LN_ADDSERVER'];

		//PreInit these values 
		$content['SERVERID'] = "";
		$content['SERVERNAME'] = "";
		$content['SERVERIP'] = "";
		$content['PORT'] = "";
		$content['DESCRIPTION'] = "";
		$content['MODNAME'] = "";
		$content['ADMINNAME'] = "";
		$content['CLANNAME'] = "";
		$content['ADMINEMAIL'] = "";
		$content['GAMELOGLOCATION'] = "../gamelogs/server_" . ($content['NUMSERVERS']+1) . ".log";
		$content['REMOTEGAMELOGLOCATION'] = "";
		$content['SERVERLOGO'] = "";
		$content['SERVERENABLED'] = true;
		$content['PARSINGENABLED'] = true;
		$content['FTPPASSIVEENABLED'] = false;

		// In this case we need to disable the CREATE Button
		$content['BUILDFTP_DISABLED'] = "disabled"; 
	}
	else if ($_GET['op'] == "edit") 
	{
		// Set Mode to edit
		$content['ISEDITORNEWSERVER'] = "true";
		$content['SERVER_FORMACTION'] = "editserver";
		$content['SERVER_SENDBUTTON'] = $content['LN_EDITSERVER'];

		// In this case we enable the CREATE Button
		$content['BUILDFTP_DISABLED'] = ""; 

		if ( isset($_GET['id']) )
		{
			//PreInit these values 
			$content['SERVERID'] = DB_RemoveBadChars($_GET['id']);

			$sqlquery = "SELECT * " . 
						" FROM " . STATS_SERVERS . 
						" WHERE ID = " . $content['SERVERID'];

			$result = DB_Query($sqlquery);
			$rows = DB_GetAllRows($result, true);
			if ( isset($rows ) )
			{
				$content['SERVERID'] = $rows[0]['ID'];
				$content['SERVERNAME'] = $rows[0]['Name'];
				$content['SERVERIP'] = $rows[0]['IP'];
				$content['PORT'] = $rows[0]['Port'];
				$content['DESCRIPTION'] = $rows[0]['Description'];
				$content['MODNAME'] = $rows[0]['ModName'];
				$content['ADMINNAME'] = $rows[0]['AdminName'];
				$content['CLANNAME'] = $rows[0]['ClanName'];
				$content['ADMINEMAIL'] = $rows[0]['AdminEmail'];
				$content['GAMELOGLOCATION'] = $rows[0]['GameLogLocation'];
				$content['REMOTEGAMELOGLOCATION'] = $rows[0]['ftppath'];
				$content['SERVERLOGO'] = $rows[0]['ServerLogo'];
				$content['SERVERENABLED'] = $rows[0]['ServerEnabled'];
				$content['PARSINGENABLED'] = $rows[0]['ParsingEnabled'];
				$content['FTPPASSIVEENABLED'] = $rows[0]['FTPPassiveMode'];
			}
			else
			{
				$content['ISERROR'] = "true";
				$content['ERROR_MSG'] = GetAndReplaceLangStr( $content['LN_SERVER_ERROR_NOTFOUND'], $content['SERVERID'] ); 
			}
		}
		else
		{
			$content['ISERROR'] = "true";
			$content['ERROR_MSG'] = $content['LN_SERVER_ERROR_INVID'];
		}
	}
	else if ($_GET['op'] == "dbstats") 
	{
		$content['ISDBSTATS'] = "true";

		if ( isset($_GET['id']) )
		{
			//PreInit these values 
			$content['SERVERID'] = DB_RemoveBadChars($_GET['id']);

			$sqlquery = "SELECT * " . 
						" FROM " . STATS_SERVERS . 
						" WHERE ID = " . $content['SERVERID'];
			$result = DB_Query($sqlquery);
			$rows = DB_GetAllRows($result, true);
			if ( isset($rows ) )
			{
				$content['SERVERID'] = $rows[0]['ID'];
				$content['SERVERNAME'] = $rows[0]['Name'];
			}
			else
			{
				$content['ISERROR'] = "true";
				$content['ERROR_MSG'] = GetAndReplaceLangStr( $content['LN_SERVER_ERROR_NOTFOUND'], $content['SERVERID'] ); 
			}
		}
		else
		{
			$content['ISERROR'] = "true";
			$content['ERROR_MSG'] = $content['LN_SERVER_ERROR_INVID'];
		}

		// --- Create some DB Stats
		$wherequry = " WHERE SERVERID=" . $content['SERVERID'];
		$content['STATSALIASES']	= GetSingleDBEntryOnly( "SELECT count(SERVERID) as counter FROM " . STATS_ALIASES . $wherequry );
		$content['STATSCHATLINES']	= GetSingleDBEntryOnly( "SELECT count(SERVERID) as counter FROM " . STATS_CHAT . $wherequry );
		$content['STATSPLAYERS']	= GetSingleDBEntryOnly( "SELECT count(SERVERID) as counter FROM " . STATS_PLAYERS . $wherequry );
		$content['STATSKILLS']		= GetSingleDBEntryOnly( "SELECT count(SERVERID) as counter FROM " . STATS_PLAYER_KILLS . $wherequry );
		$content['STATSROUNDS']		= GetSingleDBEntryOnly( "SELECT count(SERVERID) as counter FROM " . STATS_ROUNDS . $wherequry );
		$content['STATSTIME']		= GetSingleDBEntryOnly( "SELECT count(SERVERID) as counter FROM " . STATS_TIME . $wherequry );
		// --- 
	}

	if ( isset($_POST['op']) )
	{
		if ( isset ($_POST['id']) ) { $content['SERVERID'] = DB_RemoveBadChars($_POST['id']); } else {$content['SERVERID'] = ""; }
		if ( isset ($_POST['servername']) ) { $content['SERVERNAME'] = DB_RemoveBadChars($_POST['servername']); } else {$content['SERVERNAME'] = ""; }
		if ( isset ($_POST['serverip']) ) { $content['SERVERIP'] = DB_RemoveBadChars($_POST['serverip']); } else {$content['SERVERIP'] = ""; }
		if ( isset ($_POST['port']) ) { $content['PORT'] = DB_RemoveBadChars($_POST['port']); } else {$content['PORT'] = ""; }
		if ( isset ($_POST['description']) ) { $content['DESCRIPTION'] = DB_RemoveBadChars($_POST['description']); } else {$content['DESCRIPTION'] = ""; }
		if ( isset ($_POST['modname']) ) { $content['MODNAME'] = DB_RemoveBadChars($_POST['modname']); } else {$content['MODNAME'] = ""; }
		if ( isset ($_POST['adminname']) ) { $content['ADMINNAME'] = DB_RemoveBadChars($_POST['adminname']); } else {$content['ADMINNAME'] = ""; }
		if ( isset ($_POST['clanname']) ) { $content['CLANNAME'] = DB_RemoveBadChars($_POST['clanname']); } else {$content['CLANNAME'] = ""; }
		if ( isset ($_POST['adminemail']) ) { $content['ADMINEMAIL'] = DB_RemoveBadChars($_POST['adminemail']); } else {$content['ADMINEMAIL'] = ""; }
		if ( isset ($_POST['gameloglocation']) ) { $content['GAMELOGLOCATION'] = DB_RemoveBadChars($_POST['gameloglocation']); } else {$content['GAMELOGLOCATION'] = ""; }
		if ( isset ($_POST['remotegameloglocation']) ) { $content['REMOTEGAMELOGLOCATION'] = DB_RemoveBadChars($_POST['remotegameloglocation']); } else {$content['REMOTEGAMELOGLOCATION'] = ""; }
		if ( isset ($_POST['serverlogo']) ) { $content['SERVERLOGO'] = DB_RemoveBadChars($_POST['serverlogo']); } else {$content['SERVERLOGO'] = ""; }
		if ( isset ($_POST['serverenabled']) ) { $content['SERVERENABLED'] = true; } else {$content['SERVERENABLED'] = 0; }
		if ( isset ($_POST['parsingenabled']) ) { $content['PARSINGENABLED'] = true; } else {$content['PARSINGENABLED'] = 0; }
		if ( isset ($_POST['ftppassiveenabled']) ) { $content['FTPPASSIVEENABLED'] = true; } else {$content['FTPPASSIVEENABLED'] = 0; }

		// Check mandotary values
		if ( $content['SERVERNAME'] == "" )
		{
			$content['ISERROR'] = "true";
			$content['ERROR_MSG'] = $content['LN_SERVER_ERROR_SERVEREMPTY'];
		}
		else if ( $content['SERVERIP'] == "" )
		{
			$content['ISERROR'] = "true";
			$content['ERROR_MSG'] = $content['LN_SERVER_ERROR_SERVERIPEMPTY'];
		}
		else if ( CheckUrlOrIP($content['SERVERIP']) == false )
		{
			$content['ISERROR'] = "true";
			$content['ERROR_MSG'] = $content['LN_SERVER_ERROR_INVIP'];
		}
		else if ( is_numeric($content['PORT']) == false || $content['PORT'] >= 65535)
		{
			$content['ISERROR'] = "true";
			$content['ERROR_MSG'] = $content['LN_SERVER_ERROR_INVPORT'];
		}
		else if ( file_exists($content['GAMELOGLOCATION']) == false )
		{
			// Try to create an empty file
			$handle = fopen( $content['GAMELOGLOCATION'] , "x");
			fclose($handle);

			// Try again
			if ( file_exists($content['GAMELOGLOCATION']) == false )
			{
				$content['ISERROR'] = "true";
				$content['ERROR_MSG'] = $content['LN_SERVER_ERROR_GAMEFILENOTEXISTS'];
			}
		}

		if ( !isset($content['ISERROR']) ) 
		{	
			// Everything was alright, so we go to the next step!
			if ( $_POST['op'] == "addnewserver" )
			{
				$result = DB_Query("SELECT Name FROM " . STATS_SERVERS . " WHERE 
					IP = '" . $content['SERVERIP'] . "' AND
					Port = " . $content['PORT']);
				$rows = DB_GetAllRows($result, true);
				if ( isset($rows) )
				{
					$content['ISERROR'] = "true";
					$content['ERROR_MSG'] = $content['LN_SERVER_ERROR_INDBALREADY'];
				}
				else
				{
					// Add new Server now!
					$result = DB_Query("INSERT INTO " . STATS_SERVERS . " (Name, IP, Port, Description, ModName, AdminName, AdminEmail, ClanName,  GameLogLocation, ftppath, ServerLogo, ServerEnabled, ParsingEnabled, FTPPassiveMode ) 
					VALUES ('" . $content['SERVERNAME'] . "', 
							'" . $content['SERVERIP'] . "',
							 " . $content['PORT'] . ",
							'" . $content['DESCRIPTION'] . "',
							'" . $content['MODNAME'] . "',
							'" . $content['ADMINNAME'] . "',
							'" . $content['ADMINEMAIL'] . "',
							'" . $content['CLANNAME'] . "',
							'" . $content['GAMELOGLOCATION'] . "', 
							'" . $content['REMOTEGAMELOGLOCATION'] . "', 
							'" . $content['SERVERLOGO'] . "', 
							 " . $content['SERVERENABLED'] . ",
							 " . $content['PARSINGENABLED'] . ", 
							 " . $content['FTPPASSIVEENABLED'] . "
							)");
					DB_FreeQuery($result);
					
					// Redirect!
					RedirectResult( GetAndReplaceLangStr( $content['LN_SERVER_SUCCADDED'], $content['SERVERNAME'] ) , "servers.php" );
				}
			}
			else if ( $_POST['op'] == "editserver" )
			{
				$result = DB_Query("SELECT ID FROM " . STATS_SERVERS . " WHERE ID = " . $content['SERVERID']);
				$rows = DB_GetAllRows($result, true);
				if ( !isset($rows) )
				{
					$content['ISERROR'] = "true";
					$content['ERROR_MSG'] = GetAndReplaceLangStr( $content['LN_SERVER_ERROR_NOTFOUND'], $content['SERVERID'] ); 
				}
				else
				{
					// Edit the Server now!
					$result = DB_Query("UPDATE " . STATS_SERVERS . " SET 
						Name = '" . $content['SERVERNAME'] . "', 
						IP = '" . $content['SERVERIP'] . "', 
						Port = " . $content['PORT'] . ", 
						Description = '" . $content['DESCRIPTION'] . "', 
						ModName = '" . $content['MODNAME'] . "', 
						AdminName = '" . $content['ADMINNAME'] . "', 
						AdminEmail = '" . $content['ADMINEMAIL'] . "', 
						ClanName = '" . $content['CLANNAME'] . "', 
						GameLogLocation = '" . $content['GAMELOGLOCATION'] . "',
						ftppath = '" . $content['REMOTEGAMELOGLOCATION'] . "',
						ServerLogo = '" . $content['SERVERLOGO'] . "',
						ServerEnabled = " . $content['SERVERENABLED'] . ",
						ParsingEnabled = " . $content['PARSINGENABLED'] . ", 
						FTPPassiveMode = " . $content['FTPPASSIVEENABLED'] . "
						WHERE ID = " . $content['SERVERID']);
					DB_FreeQuery($result);

					// Redirect!
					RedirectResult( GetAndReplaceLangStr( $content['LN_SERVER_SUCCEDIT'], $content['SERVERNAME'] ) , "servers.php" );
				}
			}
		}
	}
}
else
{
	// Default Mode = List Servers
	$content['LISTSERVERS'] = "true";

	// Read all Serverentries
	$sqlquery = "SELECT ID, Name " . 
				" FROM " . STATS_SERVERS . 
				" ORDER BY ID ";
	$result = DB_Query($sqlquery);
	$content['SERVERS'] = DB_GetAllRows($result, true);

	// For the eye
	$css_class = "line0";
	for($i = 0; $i < count($content['SERVERS']); $i++)
	{
		$content['SERVERS'][$i]['CSS_CLASS'] = $css_class; 
		if ( $css_class == "line0" ) { $css_class = "line1"; } else { $css_class = "line0"; }
	}
}

if ( isset($content['SERVERENABLED']) && $content['SERVERENABLED'] )
	$content['SERVERENABLED_CHECKED'] = "checked";
else
	$content['SERVERENABLED_CHECKED'] = "";

if ( isset($content['PARSINGENABLED']) && $content['PARSINGENABLED'] )
	$content['PARSINGENABLED_CHECKED'] = "checked";
else
	$content['PARSINGENABLED_CHECKED'] = "";

if ( isset($content['FTPPASSIVEENABLED']) && $content['FTPPASSIVEENABLED'] )
	$content['FTPPASSIVEENABLED_CHECKED'] = "checked";
else
	$content['FTPPASSIVEENABLED_CHECKED'] = "";
// --- END Custom Code

// --- Parsen and Output
InitTemplateParser();
$page -> parser($content, "admin/servers.html");
$page -> output(); 
// --- 

?>