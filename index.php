<?php
/*
	*********************************************************************
	* Copyright by Andre Lorbach | 2006, 2007, 2008						*
	* -> www.ultrastats.org <-											*
	*																	*
	* Use this script at your own risk!									*
	* -----------------------------------------------------------------	*
	* Main Index File													*
	*																	*
	* -> Loads the main UltraStats Site									*
	*																	*
	* All directives are explained within this file						*
	*********************************************************************
*/

// *** Default includes	and procedures *** //
define('IN_ULTRASTATS', true);
$gl_root_path = './';
include($gl_root_path . 'include/functions_db.php');
include($gl_root_path . 'include/functions_common.php');
include($gl_root_path . 'include/class_template.php');
include($gl_root_path . 'include/functions_frontendhelpers.php');

InitUltraStats();
IncludeLanguageFile( $gl_root_path . '/lang/' . $LANG . '/main.php' );
InitFrontEndDefaults();	// Only in WebFrontEnd
// ***					*** //

// --- CONTENT Vars
if ( isset($content['myserver']) ) 
	$content['TITLE'] = "Ultrastats :: Home :: Server '" . $content['myserver']['Name'] . "'";	// Title of the Page 
else
	$content['TITLE'] = "Ultrastats :: Home";
// --- 

// --- BEGIN Custom Code

// --- BEGIN LastRounds Code for front stats
$sqlquery = "SELECT " .
					STATS_ROUNDS . ".ID, " .
					STATS_ROUNDS . ".TIMEADDED, " . 
					STATS_ROUNDS . ".ROUNDDURATION, " . 
					STATS_ROUNDS . ".AxisRoundWins, " . 
					STATS_ROUNDS . ".AlliesRoundWins, " .
					STATS_ROUNDS . ".AxisGuids, " . 
					STATS_ROUNDS . ".AlliesGuids, " .
					STATS_GAMETYPES . ".NAME as GameTypeName, " . 
					STATS_GAMETYPES . ".DisplayName as GameTypeDisplayName, " . 
					STATS_MAPS . ".MAPNAME, " . 
					STATS_MAPS . ".DisplayName as MapDisplayName, " . 
					"count(" . STATS_TIME . ".PLAYERID) as PlayerCount " . 
					" FROM " . STATS_ROUNDS . 
					" INNER JOIN (" . STATS_GAMETYPES . ", " . STATS_MAPS . ", " . STATS_TIME . 
					") ON (" . 
					STATS_ROUNDS . ".GAMETYPE=" . STATS_GAMETYPES . ".ID AND " . 
					STATS_ROUNDS . ".MAPID=" . STATS_MAPS . ".ID AND " . 
					STATS_ROUNDS . ".ID=" . STATS_TIME . ".ROUNDID )" . 
					GetCustomServerWhereQuery( STATS_ROUNDS, true) . 
					" GROUP BY " . STATS_ROUNDS . ".ID " . 
					" ORDER BY TIMEADDED DESC LIMIT 10";
$result = DB_Query($sqlquery);

$content['roundsonly'] = DB_GetAllRows($result, true);
if ( isset($content['roundsonly']) )
{
	$content['lastroundsenable'] = "true";
	for($i = 0; $i < count($content['roundsonly']); $i++)
	{
		// --- Set Mapname 
		if ( strlen($content['roundsonly'][$i]['MapDisplayName']) > 0 )
			$content['roundsonly'][$i]['FinalMapDisplayName'] = $content['roundsonly'][$i]['MapDisplayName'];
		else
			$content['roundsonly'][$i]['FinalMapDisplayName'] = $content['roundsonly'][$i]['MAPNAME'];
		// --- 

		// --- Set Mapimage
		$content['roundsonly'][$i]['MapImage'] = $gl_root_path . "images/maps/thumbs/" . $content['roundsonly'][$i]['MAPNAME'] . ".jpg";
		if ( !is_file($content['roundsonly'][$i]['MapImage']) )
			$content['roundsonly'][$i]['MapImage'] = $gl_root_path . "images/maps/no-pic.jpg";
		// --- 

		// --- Set GametypeName 
		if ( isset($content['roundsonly'][$i]['GameTypeDisplayName']) )
			$content['roundsonly'][$i]['FinalGameTypeDisplayName'] = $content['roundsonly'][$i]['GameTypeDisplayName'];
		else
			$content['roundsonly'][$i]['FinalGameTypeDisplayName'] = $content['roundsonly'][$i]['GameTypeName'];
		// --- 

		// --- Set Gametypeimage
		$content['roundsonly'][$i]['GametypeImage'] = $gl_root_path . "images/gametypes/thumbs/" . $content['roundsonly'][$i]['GameTypeName'] . ".png";
		if ( !is_file($content['roundsonly'][$i]['GametypeImage']) )
			$content['roundsonly'][$i]['GametypeImage'] = $gl_root_path . "images/gametypes/no-pic.jpg";
		// --- 

		// --- Set CSS Classes for Teams
		if		( intval($content['roundsonly'][$i]['AxisRoundWins']) > intval($content['roundsonly'][$i]['AlliesRoundWins']) )
		{
			$content['roundsonly'][$i]['AxisTeamClass'] = "WinnerTeam";
			$content['roundsonly'][$i]['AlliesTeamClass'] = "LoserTeam";
		}
		else if ( intval($content['roundsonly'][$i]['AxisRoundWins']) < intval($content['roundsonly'][$i]['AlliesRoundWins']) )
		{
			$content['roundsonly'][$i]['AxisTeamClass'] = "LoserTeam";
			$content['roundsonly'][$i]['AlliesTeamClass'] = "WinnerTeam";
		}
		else
		{
			$content['roundsonly'][$i]['AxisTeamClass'] = "DrawTeam";
			$content['roundsonly'][$i]['AlliesTeamClass'] = "DrawTeam";
		}
		// --- 

		// --- Check for DM
		if ( $content['roundsonly'][$i]['GameTypeName'] == "dm" )
		{
			if		( intval($content['roundsonly'][$i]['AxisRoundWins']) > intval($content['roundsonly'][$i]['AlliesRoundWins']) )
				$content['roundsonly'][$i]['WinnerPlayerID'] = $content['roundsonly'][$i]['AxisGuids'];
			else if ( intval($content['roundsonly'][$i]['AxisRoundWins']) < intval($content['roundsonly'][$i]['AlliesRoundWins']) )
				$content['roundsonly'][$i]['WinnerPlayerID'] = $content['roundsonly'][$i]['AlliesGuids'];
			else if ( isset($content['roundsonly'][$i]['AxisGuids']) )
			{	// Another default just set AxisGuid Winner
				$content['roundsonly'][$i]['WinnerPlayerID'] = $content['roundsonly'][$i]['AxisGuids'];
			}
			else
			{
				$content['roundsonly'][$i]['WinnerPlayerID'] = -1;
				$content['roundsonly'][$i]['WinnerPlayerHtmlName'] = "Unknown";
			}
			
			if ( $content['roundsonly'][$i]['WinnerPlayerID'] != -1)
				$content['roundsonly'][$i]['WinnerPlayerHtmlName'] = GetPlayerHtmlNameFromID($content['roundsonly'][$i]['WinnerPlayerID']) ;

		}
		// ---

		// --- Set Display Time
//		$content['roundsonly'][$i]['TimePlayed'] = date('Y-m-d H:i:s', $content['roundsonly'][$i]['TIMEADDED']);
		$content['roundsonly'][$i]['TimePlayed'] = date('H:i:s', $content['roundsonly'][$i]['TIMEADDED']);
		// --- 

		// --- Set CSS Class
		if ( $i % 2 == 0 )
			$content['roundsonly'][$i]['cssclass'] = "line1";
		else
			$content['roundsonly'][$i]['cssclass'] = "line2";
		// --- 
	}


	// --- Group by Date now (Days)
	$iDays = 0;
	$iRounds = 0;
	for($i = 0; $i < count($content['roundsonly']); $i++)
	{
		// Set Group Date 
		if ( !isset($content['lastrounds'][$iDays]['Date']) )
		{
			// Set first date
			$content['lastrounds'][$iDays]['Date'] = date('Y-m-d', $content['roundsonly'][$i]['TIMEADDED']);
		}
		else if ( $content['lastrounds'][$iDays]['Date'] != date('Y-m-d', $content['roundsonly'][$i]['TIMEADDED']) )
		{
			$iDays++;

			// Copy new date
			$content['lastrounds'][$iDays]['Date'] = date('Y-m-d', $content['roundsonly'][$i]['TIMEADDED']);
			$iRounds = 0;
		}

		// Copy Round Entry
		$content['lastrounds'][$iDays]['myrounds'][$iRounds] = $content['roundsonly'][$i];
		
		// Next round
		$iRounds++;
	}
	// --- 

}
else
	$content['lastroundsenable'] = "false";
// --- END LastRounds Code for front stats

// --- BEGIN TopPlayers Code for front stats
$sqlquery =		"SELECT " .
				STATS_PLAYERS . ".GUID, " . 
				"sum(" . STATS_PLAYERS . ".Kills) as Kills, " . 
				"sum(" . STATS_PLAYERS . ".Deaths) as Deaths, " . 
//					"round(AVG( " . STATS_PLAYERS . ".KillRatio),2) as KillRatio " .
				"sum(" . STATS_PLAYERS . ".Kills) / sum(" . STATS_PLAYERS . ".Deaths) as KillRatio " .	// TRUE l33tAGE!
//					STATS_PLAYERS . ".KillRatio " .
//					STATS_ALIASES . ".Alias, " . 
//					STATS_ALIASES . ".AliasAsHtml " .
				" FROM " . STATS_PLAYERS . 
//					" INNER JOIN (" . STATS_ALIASES . 
//					") ON (" . 
//					STATS_ALIASES . ".PLAYERID=" . STATS_PLAYERS . ".GUID) " . 
				" WHERE Kills > " . $content['web_minkills'] .
				GetCustomServerWhereQuery(STATS_PLAYERS, false) . 
				GetBannedPlayerWhereQuery(STATS_PLAYERS, "GUID", false) . 
				" GROUP BY " . STATS_PLAYERS . ".GUID " .
				" ORDER BY Kills DESC LIMIT 20";
//echo $sqlquery;
$result = DB_Query($sqlquery);

$content['topplayers'] = DB_GetAllRows($result, true);
if ( isset($content['topplayers']) )
{
	// Extend PlayerAliases
	FindAndFillTopAliases($content['topplayers'], "GUID", "Alias", "AliasAsHtml" );
	
	// Extend with Time Played
	FindAndFillWithTime($content['topplayers'], "GUID", "TimeSeconds", "TimePlayedString" );

	// --- Lets get the MAX KillRatio first
	GetAndSetMaxKillRation();
	// --- 


	$content['topplayersenable'] = "true";
	for($i = 0; $i < count($content['topplayers']); $i++)
	{
		// --- Set Number
		$content['topplayers'][$i]['Number'] = $i+1;
		// ---

/*		// --- Set Ratio
		if ( $content['topplayers'][$i]['Deaths'] > 0 )
			$content['topplayers'][$i]['KillRatio'] = round($content['topplayers'][$i]['Kills'] / $content['topplayers'][$i]['Deaths'], 2);
		else
			$content['topplayers'][$i]['KillRatio'] = $content['topplayers'][$i]['Kills'];
*/		// ---

		// --- Set CSS Class
		if ( $i % 2 == 0 )
			$content['topplayers'][$i]['cssclass'] = "line1";
		else
			$content['topplayers'][$i]['cssclass'] = "line2";
		// --- 

		// --- Set KillRation Values and Bars
		$content['topplayers'][$i]['BarImageKillRatioMinus'] = $gl_root_path . "images/bars/bar-small/red_middle_9.png";
		$content['topplayers'][$i]['BarImageKillRatioPlus'] = $gl_root_path . "images/bars/bar-small/green_middle_9.png";

		if ( isset($content['MaxKillRatio']) )
		{
			// Now we set the Width of the images
			if ( $content['topplayers'][$i]['KillRatio'] > 1 )
			{
				$content['topplayers'][$i]['KillRatioWidthMinus'] = $content['MaxPixelWidth'];
				$content['topplayers'][$i]['KillRatioWidthMinusText'] = "Ratio " . $content['topplayers'][$i]['KillRatio'];
			}
			else
			{
				$content['topplayers'][$i]['KillRatioWidthMinus'] = intval($content['topplayers'][$i]['KillRatio'] * $content['MaxPixelWidth']);
				$content['topplayers'][$i]['KillRatioWidthMinusText'] = "Ratio " . $content['topplayers'][$i]['KillRatio'] . " - " . $content['topplayers'][$i]['KillRatioWidthMinus'] . "% of 1:0 Ratio";;
			}

			if ( $content['topplayers'][$i]['KillRatio'] < 1 )
			{
				$content['topplayers'][$i]['KillRatioWidthPlus'] = "0";
				$content['topplayers'][$i]['KillRatioWidthPlusText'] = "";
			}
			else
			{
				$content['topplayers'][$i]['KillRatioWidthPlus'] = intval( ($content['topplayers'][$i]['KillRatio'] / ($content['MaxKillRatio']/$content['MaxPixelWidth'])) );
				if ( $content['topplayers'][$i]['KillRatioWidthPlus'] > 100 ) 
					$content['topplayers'][$i]['KillRatioWidthPlus'] = 100;

				$content['topplayers'][$i]['KillRatioWidthPlusText'] = "Ratio " . $content['topplayers'][$i]['KillRatio'] . " - " . $content['topplayers'][$i]['KillRatioWidthPlus'] . "% of best Ratio (Which is " . $content['MaxKillRatio'] . ")";
			}
		}
		else
		{
			$content['topplayers'][$i]['KillRatioWidthMinus'] = "0";
			$content['topplayers'][$i]['KillRatioWidthPlus'] = "0";
		}
		// --- 

	}
}
else
	$content['topplayersenable'] = "false";
// --- END TopPlayers Code for front stats

// --- BEGIN Server Details Code 
if ( isset($content['myserver']) )
{
	// Copy some variables for Server Info
	$content['server_name'] = $content['myserver']['Name'];
	$content['server_ipport'] = $content['myserver']['IP'] . ":" . $content['myserver']['Port'];
	$content['server_description'] = $content['myserver']['Description'];
	$content['server_modname'] = $content['myserver']['ModName'];
	$content['server_adminname'] = $content['myserver']['AdminName'];
	$content['server_clanname'] = $content['myserver']['ClanName'];

	// Server Logo Check
	if ( isset($content['myserver']['ServerLogo']) && strlen($content['myserver']['ServerLogo']) > 0 )
		$content['serverlogoimg'] = $content['BASEPATH'] . "images/serverlogos/" . $content['myserver']['ServerLogo'];
	else
		$content['serverlogoimg'] = $content['BASEPATH'] . "images/main/ultrastatslogo.png";

	// Enable Server Details
	$content['servertotalsenable'] = "true";

	// --- Get Total Values
	$sqlquery = "SELECT " .
						STATS_CONSOLIDATED . ".NAME, " . 
						STATS_CONSOLIDATED . ".DisplayName, " . 
						STATS_CONSOLIDATED . ".VALUE_INT, " . 
						STATS_CONSOLIDATED . ".VALUE_TXT, " . 
						STATS_CONSOLIDATED . ".DescriptionID, " . 
						STATS_LANGUAGE_STRINGS . ".TEXT as Description " .
						" FROM " . STATS_CONSOLIDATED . 
						" LEFT OUTER JOIN (" . STATS_LANGUAGE_STRINGS . 
						") ON (" . 
						STATS_LANGUAGE_STRINGS . ".STRINGID =" . STATS_CONSOLIDATED . ".DescriptionID) " . 
						" WHERE " . STATS_CONSOLIDATED . ".NAME LIKE 'server_total%' " .
						GetCustomServerWhereQuery(STATS_CONSOLIDATED, false, true) . 
						" ORDER BY " . STATS_CONSOLIDATED . ".SortID";
	$result = DB_Query($sqlquery);

	$content['server_totals'] = DB_GetAllRows($result, true);
	if ( isset($content['server_totals']) )
	{
		for($i = 0; $i < count($content['server_totals']); $i++)
		{
			// --- Set Number
			$content['server_totals'][$i]['Number'] = $i+1;
			// ---

			// --- Check for TimeField
			if ( strpos($content['server_totals'][$i]['NAME'], "time") !== false )
				$content['server_totals'][$i]['VALUE_INT'] = GetTimeStringDays($content['server_totals'][$i]['VALUE_INT']);
			// ---

			// --- Set CSS Class
			if ( $i % 2 == 0 )
				$content['server_totals'][$i]['cssclass'] = "line1";
			else
				$content['server_totals'][$i]['cssclass'] = "line2";
			// --- 

		}
	}
	// --- 

	// --- Get TOP Values
	$sqlquery = "SELECT " .
						STATS_CONSOLIDATED . ".NAME, " . 
						STATS_CONSOLIDATED . ".DisplayName, " . 
						STATS_CONSOLIDATED . ".VALUE_INT, " . 
						STATS_CONSOLIDATED . ".VALUE_TXT, " . 
						STATS_CONSOLIDATED . ".DescriptionID, " . 
						STATS_LANGUAGE_STRINGS . ".TEXT as Description " .
						" FROM " . STATS_CONSOLIDATED . 
						" LEFT OUTER JOIN (" . STATS_LANGUAGE_STRINGS . 
						") ON (" . 
						STATS_LANGUAGE_STRINGS . ".STRINGID =" . STATS_CONSOLIDATED . ".DescriptionID) " . 
						" WHERE " . STATS_CONSOLIDATED . ".NAME LIKE 'server_top%' " .
						GetCustomServerWhereQuery(STATS_CONSOLIDATED, false, true) . 
						" ORDER BY " . STATS_CONSOLIDATED . ".SortID";
	$result = DB_Query($sqlquery);
	$content['server_top'] = DB_GetAllRows($result, true);
	if ( isset($content['server_top']) )
	{
		// Enable
		$content['servertopsenable'] = "true";

		for($i = 0; $i < count($content['server_top']); $i++)
		{
			if ( $content['server_top'][$i]['NAME'] == "server_top_map" ) 
			{
				$content['server_top_map_count'] = $content['server_top'][$i]['VALUE_INT'];
				$content['server_top_map'] = $content['server_top'][$i]['VALUE_TXT'];

				// --- Set Mapimage
				$content['server_top_map_picture'] = $gl_root_path . "images/maps/small/" . $content['server_top'][$i]['VALUE_TXT'] . ".jpg";
				if ( !is_file($content['server_top_map_picture']) )
					$content['server_top_map_picture'] = $gl_root_path . "images/maps/no-pic.jpg";
				// --- 
			}
			else if ( $content['server_top'][$i]['NAME'] == "server_top_gametype" ) 
			{
				$content['server_top_gametype_count'] = $content['server_top'][$i]['VALUE_INT'];
				$content['server_top_gametype'] = $content['server_top'][$i]['VALUE_TXT'];

				// --- Set Mapimage
				$content['server_top_gametype_picture'] = $gl_root_path . "images/gametypes/small/" . $content['server_top'][$i]['VALUE_TXT'] . ".png";
				if ( !is_file($content['server_top_gametype_picture']) )
					$content['server_top_gametype_picture'] = $gl_root_path . "images/gametypes/no-pic.jpg";
				// --- 
			}

			// --- Set CSS Class
			if ( $i % 2 == 0 )
				$content['server_top'][$i]['cssclass'] = "line1";
			else
				$content['server_top'][$i]['cssclass'] = "line2";
			// --- 
		}
	}
	// --- 
}
else
{
	// Set Image default
	$content['serverlogoimg'] = $content['BASEPATH'] . "images/main/ultrastatslogo.png";

	// --- BEGIN ServerList Area
	$sqlquery = "SELECT " .
						"(" . STATS_SERVERS . ".ID) as ServerID, " . 
						"(" . STATS_SERVERS . ".NAME) as ServerName, " . 
						"(" . STATS_SERVERS . ".IP) as ServerIP, " . 
						"(" . STATS_SERVERS . ".Port) as ServerPort, " . 
						STATS_SERVERS . ".LastUpdate " . 
						" FROM " . STATS_SERVERS . 
						" ORDER BY ID ";
	$result = DB_Query( $sqlquery );

	$content['SERVERS'] = DB_GetAllRows($result, true);
	if ( isset($content['SERVERS']) )
	{
		$content['serverlistenabled'] = "true";

		for($i = 0; $i < count($content['SERVERS']); $i++)
		{
			// Last Time
			$content['SERVERS'][$i]['LastUpdate_Formatted'] = date('Y-m-d H:i:s', $content['SERVERS'][$i]['LastUpdate']);

			// --- Set CSS Class
			if ( $i % 2 == 0 )
				$content['SERVERS'][$i]['cssclass'] = "line1";
			else
				$content['SERVERS'][$i]['cssclass'] = "line2";
			// --- 
		}
	}
	// --- END Main Info Area
}
// --- END Server Details Code 

// --- BEGIN PRO Medals Code
$sqlquery = "SELECT " .
					STATS_CONSOLIDATED . ".NAME, " . 
					STATS_CONSOLIDATED . ".DisplayName, " . 
					STATS_CONSOLIDATED . ".VALUE_INT, " . 
					STATS_CONSOLIDATED . ".VALUE_TXT, " . 
					STATS_CONSOLIDATED . ".DescriptionID, " . 
//					STATS_LANGUAGE_STRINGS . ".TEXT as Description, " .
					STATS_CONSOLIDATED . ".PLAYER_ID " . 
					" FROM " . STATS_CONSOLIDATED . 
//					" LEFT JOIN (" . STATS_LANGUAGE_STRINGS . 
//					") ON (" . 
//					STATS_LANGUAGE_STRINGS . ".STRINGID =" . STATS_CONSOLIDATED . ".DescriptionID) " . 
					" WHERE " . STATS_CONSOLIDATED . ".NAME LIKE 'medal_pro%' " .
					GetCustomServerWhereQuery(STATS_CONSOLIDATED, false, true) . 
					" ORDER BY " . STATS_CONSOLIDATED . ".SortID";
$result = DB_Query($sqlquery);

$content['medals_pro'] = DB_GetAllRows($result, true);
if ( isset($content['medals_pro']) )
{
	// Extend PlayerAliases
	FindAndFillTopAliases($content['medals_pro'], "PLAYER_ID", "Alias", "AliasAsHtml" );

	$content['medalsproenable'] = "true";
	for($i = 0; $i < count($content['medals_pro']); $i++)
	{
		// --- Get Description 
		$content['medals_pro'][$i]['Description'] = GetTextFromDescriptionID( $content['medals_pro'][$i]['DescriptionID'], $content['LN_NODESCRIPTION'] );

		// --- Set Number
		$content['medals_pro'][$i]['Number'] = $i+1;
		// ---

		// --- Set TR break | 6 Medals per row! -> !Only if more then 6Medals exist!
		if ( ($i+1) % 6 == 0 && $i > 6 )
			$content['medals_pro'][$i]['rowend'] = "<td width=\"50%\">&nbsp;</td></tr><tr><td width=\"50%\">&nbsp;</td>";
		else
			$content['medals_pro'][$i]['rowend'] = "";
		// ---
	}
}
else
	$content['medalsproenable'] = "false";
// --- END PRO Medals Code

// --- BEGIN ANTI Medals Code
$sqlquery = "SELECT " .
					STATS_CONSOLIDATED . ".NAME, " . 
					STATS_CONSOLIDATED . ".DisplayName, " . 
					STATS_CONSOLIDATED . ".VALUE_INT, " . 
					STATS_CONSOLIDATED . ".VALUE_TXT, " . 
					STATS_CONSOLIDATED . ".DescriptionID, " . 
//					STATS_LANGUAGE_STRINGS . ".TEXT as Description, " .
					STATS_CONSOLIDATED . ".PLAYER_ID " . 
					" FROM " . STATS_CONSOLIDATED . 
//					" LEFT JOIN (" . STATS_LANGUAGE_STRINGS . 
//					") ON (" . 
//					STATS_LANGUAGE_STRINGS . ".STRINGID =" . STATS_CONSOLIDATED . ".DescriptionID) " . 
					" WHERE " . STATS_CONSOLIDATED . ".NAME LIKE 'medal_anti%' " .
					GetCustomServerWhereQuery(STATS_CONSOLIDATED, false, true) . 
					" ORDER BY " . STATS_CONSOLIDATED . ".SortID";
$result = DB_Query($sqlquery);

$content['medals_anti'] = DB_GetAllRows($result, true);
if ( isset($content['medals_anti']) )
{
	// Extend PlayerAliases
	FindAndFillTopAliases($content['medals_anti'], "PLAYER_ID", "Alias", "AliasAsHtml" );

	$content['medalsantienable'] = "true";
	for($i = 0; $i < count($content['medals_anti']); $i++)
	{
		// --- Get Description 
		$content['medals_anti'][$i]['Description'] = GetTextFromDescriptionID( $content['medals_anti'][$i]['DescriptionID'], $content['LN_NODESCRIPTION'] );

		// --- Set Number
		$content['medals_anti'][$i]['Number'] = $i+1;
		// ---

		// --- Set TR break | 6 Medals per row!
		if ( ($i+1) % 6 == 0 )
			$content['medals_anti'][$i]['rowend'] = "<td width=\"50%\">&nbsp;</td></tr><tr><td width=\"50%\">&nbsp;</td>";
		else
			$content['medals_anti'][$i]['rowend'] = "";
		// ---
	}
}
else
	$content['medalsantienable'] = "false";
// --- END ANTI Medals Code

// --- 

// --- Parsen and Output
InitTemplateParser();
$page -> parser($content, "index.html");
$page -> output(); 
// --- 

?>