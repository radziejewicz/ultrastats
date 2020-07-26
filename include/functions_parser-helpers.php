<?php
/*
	*********************************************************************
	* Copyright by Andre Lorbach | 2006!								*
	* -> www.ultrastats.org <-											*
	*																	*
	* Use this script at your own risk!									*
	* -----------------------------------------------------------------	*
	* Parser helper functions											*
	*																	*
	* -> 		*
	*																	*
	* All directives are explained within this file						*
	*********************************************************************
*/

// --- Avoid directly accessing this file! 
if ( !defined('IN_ULTRASTATS') )
{
	die('Hacking attempt');
	exit;
}
// --- 

function CreateHTMLHeader()
{
	global $RUNMODE;

	// not needed in console mode
	if ( $RUNMODE == RUNMODE_COMMANDLINE )
		return;

	global $currentclass, $currentmenuclass;
	$currentclass = "line0";
	$currentmenuclass = "cellmenu1";

	print ('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
			<html>
			<head>
			<link rel="stylesheet" href="css/admin.css" type="text/css">
			</head>
			<SCRIPT language="JavaScript">
				var g_intervalID;
				function scrolldown()
				{
					scrollTo(0, 1000000);
				}
				// Always scroll down
				g_intervalID = setInterval("scrolldown()",250);
			</SCRIPT>
			<body TOPMARGIN="0" LEFTMARGIN="0" MARGINWIDTH="0" MARGINHEIGHT="0" OnLoad="scrolldown; clearInterval(g_intervalID);"><br>
			');
}

function PrintDebugInfoHeader()
{
	global $RUNMODE, $gl_root_path, $LANG;
	global $currentmenuclass;

	// Include Language file as well
	IncludeLanguageFile( $gl_root_path . 'lang/' . $LANG . '/admin.php' );

	if ( $RUNMODE == RUNMODE_COMMANDLINE )
		print ( "Num.\tFacility . \tDebug Message\n" );
	else if ( $RUNMODE == RUNMODE_WEBSERVER )
	{
	print('	<table width="100%" border="0" cellspacing="1" cellpadding="1" align="center" bgcolor="#777777">
			<tr> 
				<td class="' . $currentmenuclass . '" width="50" align="center" nowrap><B>Number</B></td>
				<td class="' . $currentmenuclass . '" width="100" align="center" nowrap><B>DebugLevel</B></td>
				<td class="' . $currentmenuclass . '" width="150" align="center" nowrap><B>Facility</B></td>
				<td class="' . $currentmenuclass . '" width="100%" align="center" ><B>DebugMessage</B></td>
			</tr>
			</table>');
	}
}

function PrintSecureUserCheck( $warningtext, $yesmsg, $nomsg, $operation )
{
	global $content, $myserver;

	// Show Accept FORM!
	print('<br><br>
			<table width="600" cellpadding="2" cellspacing="0" border="0" align="center" class="with_border">
			<tr>
				<td colspan="10" align="center" valign="top" class="title"><strong><FONT COLOR="red">' . $warningtext . '</FONT></strong></td>
			</tr>
			</table>
			<table width="600" cellpadding="2" cellspacing="1" border="0" align="center" class="with_border">
			<tr>
				<td align="center" class="line0">
					<br>
					<A HREF="parser-core.php?op=' . $operation . '&id=' . $myserver['ID'] . '&verify=yes">
					<img src="' . $content['BASEPATH'] . 'images/icons/check.png" width="16"><br>
					' . $yesmsg . '</A>
				</td>
				<td align="center" class="line1">
					<A HREF="javascript:history.back;">
					<br>
					<img src="' . $content['BASEPATH'] . 'images/icons/redo.png" width="16"><br>
					' . $nomsg . '</A>
				</td>
			</tr>
			</table>
			');
}

function PrintPasswordRequest()
{
	global $content, $myserver;

	// Show Accept FORM!
	print('<br><br>
			<form action="parser-core.php?op=getnewlogfile&id=' . $myserver['ID'] . '" method="post">
			<table width="400" cellpadding="2" cellspacing="0" border="0" align="center" class="with_border">
			<tr>
				<td colspan="10" align="center" valign="top" class="title"><strong><FONT COLOR="red">' . $content['LN_FTPLOGINFAILED'] . '</FONT></strong></td>
			</tr>
			</table>
			<table width="400" cellpadding="2" cellspacing="1" border="0" align="center" class="with_border">
			<tr>
				<td align="left" class="cellmenu1" width="150" nowrap><b>' . $content['LN_FTPPASSWORD'] . '</b></td>
				<td align="right" class="line0" width="100%"><input type="password" name="pwd" size="32" maxlength="255" value=""></td>
			</tr>
			<tr>
				<td align="center" colspan="2">
					<input type="submit" value="' . $content['LN_ADMINSEND'] . '">
				</td>
			</tr>
			</table>
			</form>
			');
}

function PrintHTMLDebugInfo( $facility, $fromwhere, $szDbgInfo )
{
	global $content, $currentclass, $currentmenuclass, $gldbgcounter, $DEBUGMODE, $RUNMODE;

	// No output in this case
	if ( $facility > $DEBUGMODE )
		return;

	if ( !isset($gldbgcounter) )
		$gldbgcounter = 0;
	$gldbgcounter++;

	if ( $RUNMODE == RUNMODE_COMMANDLINE )
		print ( $gldbgcounter . ". \t" . GetFacilityAsString($facility) . ". \t" . $fromwhere . ". \t" . $szDbgInfo . "\n" );
	else if ( $RUNMODE == RUNMODE_WEBSERVER )
	{
		print ('<table width="100%" border="0" cellspacing="1" cellpadding="1" align="center" bgcolor="#777777">
				<tr> 
					<td class="' . $currentmenuclass . '" width="50" align="center" nowrap><B>' . $gldbgcounter . '</B></td>
					<td class="' . GetDebugClassFacilityAsString($facility) . '" width="100" align="center" nowrap><B>' . GetFacilityAsString($facility) . '</B></td>
					<td class="' . $currentclass . '" width="150" align="center" nowrap><B>' . $fromwhere . '</B></td>
					<td class="' . $currentclass . '" width="100%">&nbsp;&nbsp;' . $szDbgInfo . '</td>
				</tr>
				</table>');

		// Set StyleSheetclasses
		if ( $currentclass == "line0" )
			$currentclass = "line1";
		else
			$currentclass = "line0";
		if ( $currentmenuclass == "cellmenu1" )
			$currentmenuclass = "cellmenu2";
		else
			$currentmenuclass = "cellmenu1";
	}

	//Flush php output
	flush();

	// If DEBUG_ERROR_WTF and $content['gen_phpdebug'] is set, abort!
	if ( $content['gen_phpdebug'] == 1 && $facility == DEBUG_ERROR_WTF ) 
		die ( $szDbgInfo );
}

function GetFacilityAsString( $facility )
{
	switch ( $facility )
	{
		case DEBUG_ULTRADEBUG:
			return STR_DEBUG_ULTRADEBUG;
		case DEBUG_DEBUG:
			return STR_DEBUG_DEBUG;
		case DEBUG_INFO:
			return STR_DEBUG_INFO;
		case DEBUG_WARN:
			return STR_DEBUG_WARN;
		case DEBUG_ERROR:
			return STR_DEBUG_ERROR;
		case DEBUG_ERROR_WTF:
			return STR_DEBUG_ERROR_WTF;
	}
	
	// reach here = unknown
	return "*Unknown*";
}

function GetDebugClassFacilityAsString( $facility )
{
	switch ( $facility )
	{
		case DEBUG_ULTRADEBUG:
			return "debugultradebug";
		case DEBUG_DEBUG:
			return "debugdebug";
		case DEBUG_INFO:
			return "debuginfo";
		case DEBUG_WARN:
			return "debugwarn";
		case DEBUG_ERROR:
			return "debugerror";
		case DEBUG_ERROR_WTF:
			return "debugerrorwtf";
	}
	
	// reach here = unknown
	return "*Unknown*";
}

function CreateHTMLFooter()
{
	global $content, $ParserStart, $RUNMODE;
	$RenderTime = number_format( microtime_float() - $ParserStart, 4, '.', '');
	
	// not needed in console mode
	if ( $RUNMODE == RUNMODE_COMMANDLINE )
		return;

	print ('<br><center><h3>Finished</h3><br>Total running time was ' . $RenderTime . ' seconds<br><br>
			<br></center>
			</body> 
			</html>');
}

function GetLastLogLine( $serverid )
{
	// --- Get last FilePosition
	$result = DB_Query("SELECT LastLogLine FROM " . STATS_SERVERS . " WHERE id = $serverid");
	$rows = DB_GetAllRows($result, true);
	if ( isset($rows) )
		return $rows[0]['LastLogLine'];
	else
		return 0;
}

function SetLastLogLine( $serverid, $newlastline )
{
	global $content;

	// If disabled we skip this part
	if ( $content['parser_disablelastline'] == "yes" ) 
		return;

	// --- Set the last FilePosition
	$result = DB_Query("UPDATE " . STATS_SERVERS . " SET LastLogLine = '" . $newlastline . "' WHERE ID = $serverid");
	DB_FreeQuery($result);
}

function GetSecondsFromLogLine( $logline )
{
	$tempstr = explode(" ", trim($logline));
	$timestr = explode(":", trim($tempstr[0]));
	
	if ( !isset($timestr[1]) )
	{
		PrintHTMLDebugInfo( DEBUG_ERROR_WTF, "GetSecondsFromLogLine", "Invalid LOGLINE detected: '" . $logline . "'");
		return -1;
	}

	// We only need to add them
	return ( (intval($timestr[0])*60) + intval($timestr[1]) );
}

function CheckLogLine($myLine)
{
	// First of all trim
	$myReturnLine = trim($myLine);

	// --- New check if space is missing between timestamp and rest
	$myTempArray	= explode(" ", $myReturnLine);

	if ( count($myTempArray) < 2 )
		return false;

	if ( strstr($myTempArray[0], ':') == FALSE )
		return false;

	// ---
	return true;
}

function SplitTimeFromLogLine($myLogLine)
{
	// Return the Raw Logline
	return trim( strstr( trim($myLogLine), ' ') );
}

function GetMapIDByName( $mapname )
{
	$result = DB_Query("SELECT ID FROM " . STATS_MAPS . " WHERE MAPNAME = '$mapname'");
	$myrow = DB_GetSingleRow($result, true);
	if ( isset($myrow['ID']) )
		return $myrow['ID'];
	else
		return ProcessInsertStatement( "INSERT INTO " . STATS_MAPS . " (MAPNAME, Description_id) VALUES ('$mapname', '" . $mapname . "_description')");
}

function GetGameTypeByName( $gametype )
{
	$result = DB_Query("SELECT ID FROM " . STATS_GAMETYPES . " WHERE NAME = '$gametype'");
	$myrow = DB_GetSingleRow($result, true);
	if ( isset($myrow['ID']) )
		return $myrow['ID'];
	else
		return ProcessInsertStatement( "INSERT INTO " . STATS_GAMETYPES . " (NAME, Description_id) VALUES ('$gametype', 'gametype_" . $gametype . "')");
}

function GetDamageTypeIDByName( $damagetype )
{
	$result = DB_Query("SELECT ID FROM " . STATS_DAMAGETYPES . " WHERE DAMAGETYPE = '$damagetype'");
	$myrow = DB_GetSingleRow($result, true);
	if ( isset($myrow['ID']) )
		return $myrow['ID'];
	else
	{
		PrintHTMLDebugInfo( DEBUG_ERROR_WTF, "GetDamageTypeIDByName", "Unknown DamageType detected: '" . $damagetype . "'");
		return ProcessInsertStatement( "INSERT INTO " . STATS_DAMAGETYPES . " (DAMAGETYPE, DisplayName) VALUES ('" . $damagetype . "', '" . $damagetype . "')");
	}
}

function GetWeaponIDByName( $weaponname )
{
	global $myserver;

	/* --- Hotfix for crap cod4 logging format .. damn dev noobs @iw ... 
	*	Rewritting the weapon_ids of these here: 
		gl_ak47_mp
		gl_g36c_mp
		gl_g3_mp
		gl_m14_mp
		gl_m16_mp
		gl_m4_mp
	--- */
	$search = array( "gl_ak47_mp", "gl_g36c_mp", "gl_g3_mp", "gl_m14_mp", "gl_m16_mp", "gl_m4_mp" );
	$replace = array( "ak47_gl_mp", "g36c_gl_mp", "g3_gl_mp", "m14_gl_mp", "m16_gl_mp", "m4_gl_mp" );
	$weaponname = str_replace($search, $replace, $weaponname);

	// --- First get and check the weapon id ;)!
	$result = DB_Query("SELECT ID FROM " . STATS_WEAPONS . " WHERE INGAMENAME = '$weaponname'");
	$myrow = DB_GetSingleRow($result, true);
	if ( isset($myrow['ID']) )
		$weaponid = $myrow['ID'];
	else
		$weaponid = ProcessInsertStatement( "INSERT INTO " . STATS_WEAPONS . " (INGAMENAME, Description_id) VALUES ('$weaponname', 'weapon_" . $weaponname . "')");
	// --- 

	// --- Now we check if the weapon is enabled for the server
	$result = DB_Query("SELECT SERVERID FROM " . STATS_WEAPONS_PERSERVER . " WHERE WEAPONID = " . $weaponid . " AND SERVERID = " . $myserver['ID']);
	$myrow = DB_GetSingleRow($result, true);
	if ( !isset($myrow['SERVERID']) )
		ProcessInsertStatement( "INSERT INTO " . STATS_WEAPONS_PERSERVER . " (WEAPONID, SERVERID, ENABLED) VALUES (" . $weaponid . ", " . $myserver['ID'] . ", 1)");
	// --- 
	
	// Return the weapon id
	return $weaponid; 
}

function GetActionIDByName( $actionname )
{
	$result = DB_Query("SELECT ID FROM " . STATS_GAMEACTIONS . " WHERE NAME = '$actionname'");
	$myrow = DB_GetSingleRow($result, true);
	if ( isset($myrow['ID']) )
		return $myrow['ID'];
	else
		return ProcessInsertStatement( "INSERT INTO " . STATS_GAMEACTIONS . " (NAME) VALUES ('$actionname')");
}

function GetGametypeFromInitGame($mybuffer)
{
	// +11 Chars to remove the "InitGame: \" and Create tmp Servervar Array
	$tmparray = explode( "\\", trim(substr( SplitTimeFromLogLine($mybuffer), 11)) );
	for($i = 0; $i < count($tmparray); $i+=2)
		$cvartmparray[ DB_RemoveBadChars($tmparray[$i]) ] = DB_RemoveBadChars( $tmparray[$i+1] );

	if ( isset($cvartmparray['g_gametype']) )
		return $cvartmparray['g_gametype'];
	else
	{
		PrintHTMLDebugInfo( DEBUG_ERROR_WTF, "GetGametypeFromInitGame", "Unknown GameInit detected: '" . print_r($cvartmparray) . "'");
		return "";
	}
}

function GetHitLocationTypeIDByName( $hitloaction )
{
	$result = DB_Query("SELECT ID FROM " . STATS_HITLOCATIONS . " WHERE BODYPART = '$hitloaction'");
	$rows = DB_GetAllRows($result, true);
	if ( isset($rows) )
		return $rows[0]['ID'];
	else
	{
		PrintHTMLDebugInfo( DEBUG_ERROR_WTF, "GetHitLocationTypeIDByName", "Unknown HitLocation detected: '" . $hitloaction . "'");
		return ProcessInsertStatement( "INSERT INTO " . STATS_HITLOCATIONS . " (BODYPART, DisplayName) VALUES ('" . $hitloaction . "', '" . $hitloaction . "')");
	}
}

function ProcessSelectStatement( $sqlStatement )
{
	global $SQL_SELECT_Count;

	// RUN DB Query
	$result = DB_Query( $sqlStatement );
	
	// Increment counter
	$SQL_SELECT_Count++;

	return $result;
}

function ProcessExtendedInsertStatement( $sqlStatement, $nStatementCount, $execDirect = true)
{
	global $SQL_INSERT_Count;

	$result = DB_Query( $sqlStatement );
	if ($result == FALSE)
	{
		PrintHTMLDebugInfo( DEBUG_ERROR, "ProcessInsertStatement", "INSERT Statement Error: ".$sqlStatement);
		return -1;
	}
	else
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "ProcessInsertStatement", "INSERT Statement Success: ".$sqlStatement);
		
	// Increment counter
	$SQL_INSERT_Count += $nStatementCount;

	// Get ID and free result
	$InsertID = mysql_insert_id();
	DB_FreeQuery($result);

	//Return ID
	return $InsertID;
}

function ProcessInsertStatement( $sqlStatement, $execDirect = true)
{
	global $SQL_INSERT_Count;

	$result = DB_Query( $sqlStatement );
	if ($result == FALSE)
	{
		PrintHTMLDebugInfo( DEBUG_ERROR, "ProcessInsertStatement", "INSERT Statement Error: ".$sqlStatement);
		return -1;
	}
	else
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "ProcessInsertStatement", "INSERT Statement Success: ".$sqlStatement);
		
	// Increment counter
	$SQL_INSERT_Count++;

	// Get ID and free result
	$InsertID = mysql_insert_id();
	DB_FreeQuery($result);

	//Return ID
	return $InsertID;
}

function ProcessDeleteStatement( $sqlStatement )
{
	$result = DB_Query( $sqlStatement );
	if ($result == FALSE)
	{
		PrintHTMLDebugInfo( DEBUG_ERROR, "ProcessDeleteStatement", "DELETE Statement Error: ".$sqlStatement);
		return false;
	}
	else
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "ProcessDeleteStatement", "DELETE Statement Success: ".$sqlStatement);
	DB_FreeQuery($result);

	// Done
	return true;
}

function ProcessUpdateStatement( $sqlStatement, $execDirect = false )
{
	global $content, $SQL_UDPATE_Direct_Count, $SQL_UDPATE_Batch_Count, $sqlupdatestatements;

	if ( $execDirect || $content['MYSQL_BULK_MODE'] == false ) 
	{	// Only DIRECT Update Mode atm!
		$result = DB_Query( $sqlStatement );
		if ($result == FALSE)
		{
			PrintHTMLDebugInfo( DEBUG_ERROR, "ProcessUpdateStatement", "UPDATE Statement Error: ".$sqlStatement);
			return false;
		}
		else
			PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "ProcessUpdateStatement", "UPDATE Statement Success: ".$sqlStatement);
		
		// Increment counter
		$SQL_UDPATE_Direct_Count++;

		// Free result
		DB_FreeQuery($result);
	}
	else
	{
		$sqlupdatestatements .= $sqlStatement . ";\r\n";

		// Increment counter
		$SQL_UDPATE_Batch_Count++;
	}
}

// TODO!!!!!
function ProcessQueuedUpdateStatement()
{
	// Now run bulk updates :)!
	global $content, $SQL_UDPATE_Direct_Count, $SQL_UDPATE_Batch_Count, $sqlupdatestatements;
	
	// Nothing to do!
	if ( strlen($sqlupdatestatements) <= 0 )
		return; 
	
	// Dump into file now
	$myhandle = fopen( $content['sqltmpfile'], "w" );
	if ($myhandle)
		fwrite($myhandle, $sqlupdatestatements . "\r\n");
	
	// Create command for the pipe
	if ( strlen($CFG['Pass'] > 0) )
		$myCommand = $content['MYSQLPATH'] . " -u " . $CFG['User'] . " -p" .$CFG['Pass'] . " " . $CFG['DBName'] . " < " . $content['sqltmpfile'];
	else
		$myCommand = $content['MYSQLPATH'] . " -u " . $CFG['User'] . " " . $CFG['DBName'] . " < " . $content['sqltmpfile'];

	$myOutput = shell_exec($myCommand);
	if (strlen($myOutput > 0) )
		PrintHTMLDebugInfo(DEBUG_WARN, "QueuedUpdates", "MySQL Pipe Output: " . $myOutput);
	else
		PrintHTMLDebugInfo(DEBUG_DEBUG, "QueuedUpdates", "MySQL Command: " . $myCommand . " Error Output: " . $myOutput);

	// TODO Check for PHP5 and use
	// mysqli_multi_query
}

function GetPlayerWithMostKills()
{
	global $myPlayers;
	
	if ( isset($myPlayers) && count($myPlayers) > 0 )
	{
		$highestkill = 0;
		$returnguid = "";

		// Search for the Player with most kills
		foreach ( $myPlayers as $player )
		{
			if ( $player[PLAYER_KILLS] > $highestkill )
			{
				$highestkill = $player[PLAYER_KILLS];
				$returnguid = $player[PLAYER_GUID];
			}
		}

		// Return best player
		return $returnguid;
	}
	else
		return "";
}

/*	Helper function which will generate stripped Aliases Names 
*	for all existing an new Alias Entries. Only empty ones will 
*	be generated and updates
*/
function GenerateStrippedCodeAliases()
{
	global $content;

	$sqlquery = "SELECT " .
				STATS_ALIASES . ".ID, " . 
				STATS_ALIASES . ".Alias " . 
				" FROM " . STATS_ALIASES . 
				" WHERE AliasStrippedCodes = '' ";
	$result = DB_Query( $sqlquery );
	$allaliases = DB_GetAllRows($result, true);
	if ( isset($allaliases) )
	{
		PrintHTMLDebugInfo( DEBUG_INFO, "GenerateStrippedCodeAliases", "Starting Stripped Alias Calculation for '" . count($allaliases) . "' Aliases ...");
		for($i = 0; $i < count($allaliases); $i++)
		{
			$strippedalias = DB_RemoveBadChars( StripColorCodesFromString( $allaliases[$i]['Alias'] ) );
			if ( strlen($strippedalias) <= 0 )	// matches for peoples using colorcodes only, bastards :D
				$strippedalias = "Undefined";

			ProcessUpdateStatement(	" UPDATE " . STATS_ALIASES . " SET " . 
									" AliasStrippedCodes = '" . $strippedalias . "'" . 
									" WHERE ID = " . $allaliases[$i]['ID'] );
		}
	}
}

function ReCreateAliases()
{
	global $content;

	PrintHTMLDebugInfo( DEBUG_INFO, "ReCreateAliases", "Starting Total Aliases HTML Code Calculation ...");

	$sqlquery = "SELECT " .
						STATS_ALIASES . ".ID,  " . 
						STATS_ALIASES . ".Alias " . 
						" FROM " . STATS_ALIASES;
	$result = DB_Query( $sqlquery );
	$allplayers = DB_GetAllRows($result, true);
	if ( isset($allplayers) )
	{
		for($i = 0; $i < count($allplayers); $i++)
		{
			// Create WHERE
			$wherequery = " WHERE " . STATS_ALIASES . ".ID = " . $allplayers[$i]['ID'];

			// First of all we need to clean up the mess!
			$searchfor = array( "amp;", "&lt;", "&gt;" );
			$replacewith = array( "", "<", ">" );
			$allplayers[$i]['Alias'] = str_replace ( $searchfor, $replacewith, $allplayers[$i]['Alias'] );
			
			// Now create plain alias code!
			$plainalias = GetPlayerNameAsWithHTMLCodes( DB_RemoveBadChars($allplayers[$i]['Alias']) );
			$aliasashtml = GetPlayerNameAsHTML( DB_RemoveBadChars($allplayers[$i]['Alias']) );
			$strippedalias = StripColorCodesFromString( DB_RemoveBadChars($allplayers[$i]['Alias']) );
			if ( strlen($strippedalias) <= 0 )	// matches for peoples using colorcodes only, bastards :D
				$strippedalias = "ColorCodePlayer";

			// Update Calc
			ProcessUpdateStatement("UPDATE " . STATS_ALIASES . " SET Alias = '" . $plainalias .  "', AliasAsHtml = '" . $aliasashtml . "', AliasStrippedCodes = '" . $strippedalias . "' " . $wherequery );
		}
	}
}

function CreateTopAliases( $serverid )
{
	global $content;

	// --- Now we calc 
	if ( $serverid != -1 )
	{
		PrintHTMLDebugInfo( DEBUG_INFO, "CreateTopAliases", "Starting TopAliases Calculation ...");
		$wheresinglesql1 = " WHERE " . STATS_PLAYERS . ".SERVERID = " . $serverid;
		$wheresinglesql2 = " AND " . STATS_ALIASES . ".SERVERID = " . $serverid;
		$groupbysql = " GROUP BY SERVERID ";
	}
	else
	{
		PrintHTMLDebugInfo( DEBUG_INFO, "CreateTopAliases", "Starting Total TopAliases Calculation ...");
		$wheresinglesql1 = "";
		$wheresinglesql2 = "";
		$groupbysql = "";
	}
	$whereaddupdatesql = " AND SERVERID = " . $serverid;


/*	$sqlquery = "SELECT " .
						STATS_PLAYERS . ".GUID, " . 
						STATS_ALIASES . ".ID, " . 
						"sum( " . STATS_ALIASES . ".Count) as Count " . 
						" FROM " . STATS_PLAYERS . 
						" INNER JOIN (" . STATS_ALIASES . 
						") ON (" . 
						STATS_PLAYERS . ".GUID=" . STATS_ALIASES . ".PLAYERID) " . 
						$wheresinglesql . 
						" GROUP BY " . STATS_ALIASES . ".Alias " . 
//						" GROUP BY " . STATS_ALIASES . ".Alias AND " . STATS_ALIASES . ".PLAYERID " . 
						" ORDER BY " . STATS_ALIASES . ".Count DESC ";
						*/
	$sqlquery = "SELECT " .
						STATS_PLAYERS . ".GUID " . 
						" FROM " . STATS_PLAYERS . 
						$wheresinglesql1 . 
						" GROUP BY " . STATS_PLAYERS . ".GUID ";
	$result = DB_Query( $sqlquery );
	$allplayers = DB_GetAllRows($result, true);
	if ( isset($allplayers) )
	{
		for($i = 0; $i < count($allplayers); $i++)
		{
			$sqlquery = "SELECT " .
						STATS_ALIASES . ".ID, " . 
						STATS_ALIASES . ".Alias, " . 
						"sum( " .STATS_ALIASES . ".Count) as MyCount " . 
						" FROM " . STATS_ALIASES . 
						" WHERE PLAYERID = " . $allplayers[$i]['GUID'] . " " . 
						$wheresinglesql2 . 
						" GROUP BY " . STATS_ALIASES . ".Alias " . 
						" ORDER BY MyCount DESC LIMIT 1";
			$result = DB_Query( $sqlquery );
			$mytmparray = DB_GetSingleRow($result, true);
			if ( isset($mytmparray['ID']) )
			{
				$sqlquery = " SELECT " . 
							STATS_PLAYERS_TOPALIASES . ".GUID " .
							" FROM " . STATS_PLAYERS_TOPALIASES . 
							" WHERE " . STATS_PLAYERS_TOPALIASES . ".GUID = " . $allplayers[$i]['GUID'] . 
							$whereaddupdatesql;
				$result = DB_Query( $sqlquery );
				$tmpvars = DB_GetSingleRow($result, true);

				if ( isset($tmpvars['GUID']) )
					ProcessUpdateStatement(	" UPDATE " . STATS_PLAYERS_TOPALIASES . " SET " . 
											" ALIASID = " . $mytmparray['ID'] . 
											" WHERE GUID = " . $allplayers[$i]['GUID'] . 
											$whereaddupdatesql ); 
				else	// NOT DIRECTLY, TODO LATER!
					ProcessInsertStatement(	" INSERT INTO " . STATS_PLAYERS_TOPALIASES . " (GUID, SERVERID, ALIASID) " . 
											" VALUES ( " 
											. $allplayers[$i]['GUID'] . ", " 
											. $serverid . ", " 
											. $mytmparray['ID'] . " )", false );
			}
			else
				PrintHTMLDebugInfo( DEBUG_ERROR, "CreateTopAliases", "No AliasName found for GUID " . $allplayers[$i]['GUID'] . "! This may caused by a parsing bug or logfile corruption" );
		}
	}
}

function SetMaxExecutionTime()
{
	global $RUNMODE, $MaxExecutionTime;

	if ($RUNMODE == RUNMODE_WEBSERVER)
	{
		// Max Execution time
		set_time_limit( 120 );									// Extend Execution Time
		$MaxExecutionTime = ini_get("max_execution_time") - 15; // Raised limit to -15 Seconds to be on the save side
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Gamelog", "MaxExecutionTime = $MaxExecutionTime");
	}
	else
	{
		// Unlimited
		set_time_limit( 0 );
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Gamelog", "Console Mode, unlimited Execution TIME ");
	}
}

function ParsePlayerGuid( $myArray, $arraynum, $arraynum_playername )
{
	// TODO | ADD Support for GUID by IP and NAME!
	global $content;

	if ( $content['gen_parseby'] == PARSEBY_GUIDS )
	{
		if ( $content['gen_gameversion'] == COD4 )
		{
			// Calc Guid!
			$checksum = sprintf( "%u", crc32 ( $myArray[$arraynum] ));
			return $checksum;
		}
		else
			// Kindly return GUID
			return $myArray[$arraynum];
	}
	else // if ( $content['gen_parseby'] == PARSEBY_PLAYERNAME )
	{
		$checksum = sprintf( "%u", crc32 ( $myArray[$arraynum_playername] ));
		return $checksum;
	}
}

function GetGuidsFromPlayerArray( $szTeamName ) 
{
	global $myPlayers;
	$strGuids = "";

	foreach ( $myPlayers as $player )
	{
		if ( $player[PLAYER_TEAM] == $szTeamName )
		{
			if ( strlen($strGuids) > 0 ) 
				$strGuids .= ";";
			if ( isset($player[PLAYER_GUID]) )
				$strGuids .= $player[PLAYER_GUID];
			else
				PrintHTMLDebugInfo( DEBUG_ERROR, "GetGuidsFromPlayerArray", "Invalid Player! Array='" . implode(",", $player) . "'");
		}
	}
	
	// Debug ^^!
	PrintHTMLDebugInfo( DEBUG_DEBUG, "GetGuidsFromPlayerArray", "Found guids='" . $strGuids . "' for team '" . $szTeamName . "'");

	if ( strlen($strGuids) <= 0 )
	{
		$strdebug = "";
		foreach ( $myPlayers as $player )
		{
			if ( isset($player) )
				$strdebug .= "Player: \"" . implode(",", $player) . "\"";
		}

		PrintHTMLDebugInfo( DEBUG_DEBUG, "GetGuidsFromPlayerArray", "Empty Guids? Team = '" . $szTeamName . "' myPlayers Array='" . $strdebug . "'");
	}

	// return guids
	return $strGuids;
}

/* Converted the Timemod from ramirez into UltraStats, thanks for your original Idea and Input :) 
*	This is a helper function to obtain the StartTime from the InitGame Logline, if there is one! Otherwise the old flawed time method is used. 
*	In order to get this to work, you need this in the Startup of the Server: 
*		 +sets gamestartup \"`date +"%D %T"`\"
*/
function GetCustomServerStartTime($mybuffer)
{
	// +11 Chars to remove the "InitGame: \" and Create tmp Servervar Array
	$tmparray = explode( "\\", trim(substr( SplitTimeFromLogLine($mybuffer), 11)) );
	for($i = 0; $i < count($tmparray); $i+=2)
		$cvartmparray[ DB_RemoveBadChars($tmparray[$i]) ] = DB_RemoveBadChars( $tmparray[$i+1] );

	if ( isset($cvartmparray['gamestartup']) )
	{
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "GetCustomServerStartTime", "Found custom server startup time: " . $cvartmparray['gamestartup'] );
		return $cvartmparray['gamestartup'];
	}
	else
		return "";
}


?>