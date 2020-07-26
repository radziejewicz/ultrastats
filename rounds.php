<?php
/*
	*********************************************************************
	* Copyright by Andre Lorbach | 2006, 2007, 2008						*
	* -> www.ultrastats.org <-											*
	*																	*
	* Use this script at your own risk!									*
	* -----------------------------------------------------------------	*
	* Rounds Index File													*
	*																	*
	* -> Shows list of all played rounds								*
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
{
	$content['TITLE'] = "Ultrastats :: Rounds :: Server '" . $content['myserver']['Name'] . "'";	// Title of the Page 
	$content['rounddetailsspan'] = "5";
}
else
{
	$content['TITLE'] = "Ultrastats :: Rounds";
	$content['ShowServer'] = "true";
	$content['rounddetailsspan'] = "7";
}
// --- 

// --- BEGIN Custom Code

// --- Read Vars
if ( isset($_GET['start']) )
	$content['current_pagebegin'] = intval(DB_RemoveBadChars($_GET['start']));
else
	$content['current_pagebegin'] = 0;


// ---

// --- Get/Set Sorting
if ( isset($_GET['id']) && strlen($_GET['id']) > 0 )
{
	// Set new Sorting
	$content['sorting'] = DB_RemoveBadChars($_GET['id']);
	$strsortingsql = " WHERE " . STATS_GAMETYPES . ".NAME = '" . $content['sorting'] . "'" . 
					 GetCustomServerWhereQuery( STATS_ROUNDS, false);
}
else
{
	$strsortingsql = GetCustomServerWhereQuery( STATS_ROUNDS, true);
	$content['sorting'] = ""; // Set empty, we have no sorting!
}
// --- 

// --- BEGIN LastRounds Code for front stats
	// --- First get the Count and Set Pager Variables
	$sqlquery = "SELECT " .
						"count(" . STATS_ROUNDS . ".ID) as AllRoundCount " . 
						" FROM " . STATS_ROUNDS . 
						" INNER JOIN (" . STATS_GAMETYPES .  
						") ON (" . 
						STATS_GAMETYPES . ".ID=" . STATS_ROUNDS . ".GAMETYPE ) " . 
						$strsortingsql . 
						" GROUP BY " . STATS_ROUNDS . ".ID " . 
						" ORDER BY TIMEADDED DESC ";
	$content['rounds_count'] = DB_GetRowCount( $sqlquery );
	if ( $content['rounds_count'] > $content['web_toprounds'] ) 
	{
		$pagenumbers = $content['rounds_count'] / $content['web_toprounds'];

		// Check PageBeginValue
		if ( $content['current_pagebegin'] > $content['rounds_count'] )
			$content['current_pagebegin'] = 0;

		// Enable Player Pager
		$content['rounds_pagerenabled'] = "true";
	}
	else
	{
		$content['current_pagebegin'] = 0;
		$pagenumbers = 0;
	}
	// --- 

// Now the real Query begins
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
					STATS_SERVERS . ".Name as ServerName, " . 
					STATS_SERVERS . ".ID as ServerID, " . 
					"count(" . STATS_TIME . ".PLAYERID) as PlayerCount " . 
					" FROM " . STATS_ROUNDS . 
					" INNER JOIN (" . STATS_GAMETYPES . ", " . STATS_MAPS . ", " . STATS_TIME . ", " . STATS_SERVERS . 
					") ON (" . 
					STATS_GAMETYPES . ".ID=" . STATS_ROUNDS . ".GAMETYPE AND " . 
					STATS_ROUNDS . ".MAPID=" . STATS_MAPS . ".ID AND " . 
					STATS_ROUNDS . ".ID=" . STATS_TIME . ".ROUNDID AND " . 
					STATS_ROUNDS . ".SERVERID=" . STATS_SERVERS . ".ID )" . 
					$strsortingsql . 
					" GROUP BY " . STATS_ROUNDS . ".ID " . 
					" ORDER BY TIMEADDED DESC " .
					" LIMIT " . $content['current_pagebegin'] . " , " . $content['web_toprounds'];

$result = DB_Query($sqlquery);
$content['roundsonly'] = DB_GetAllRows($result, true);
if ( isset($content['roundsonly']) )
{
	// Set roundsenabled true
	$content['roundsenabled'] = "true";
	
	// --- Set Sorting Option
	if ( isset($content['sorting']) )
		$content['titlesortedby'] = $content['LN_ROUNDS_BYGAMETYPE'] . " " . $content['sorting'];
	else
		$content['titlesortedby'] = $content['LN_ROUNDS_BYDATE'];
	// ---  
	
	// --- Set Variables for all rounds
	for($i = 0; $i < count($content['roundsonly']); $i++)
	{
		// --- Set Mapname 
		if ( strlen($content['roundsonly'][$i]['MapDisplayName']) > 0 )
			$content['roundsonly'][$i]['FinalMapDisplayName'] = $content['roundsonly'][$i]['MapDisplayName'];
		else
			$content['roundsonly'][$i]['FinalMapDisplayName'] = $content['roundsonly'][$i]['MAPNAME'];
		// --- 

		if ( isset($content['ShowServer']) )
		{
			// --- Set ServerName
			if ( strlen($content['roundsonly'][$i]['ServerName']) > 25 )
				$content['roundsonly'][$i]['ServerName'] = substr( $content['roundsonly'][$i]['ServerName'], 0, 22 ) . " ...";
			
			$content['roundsonly'][$i]['ShowServer'] = "true";
			// --- 
		}

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
	// --- 

	// --- Group by Date now (Days)
	$iDays = 0;
	$iRounds = 0;
	for($i = 0; $i < count($content['roundsonly']); $i++)
	{
		// Set Group Date 
		if ( !isset($content['allrounds'][$iDays]['Date']) )
		{
			// Set first date
			$content['allrounds'][$iDays]['Date'] = date('Y-m-d', $content['roundsonly'][$i]['TIMEADDED']);
		}
		else if ( $content['allrounds'][$iDays]['Date'] != date('Y-m-d', $content['roundsonly'][$i]['TIMEADDED']) )
		{
			$iDays++;

			// Copy new date
			$content['allrounds'][$iDays]['Date'] = date('Y-m-d', $content['roundsonly'][$i]['TIMEADDED']);
			$iRounds = 0;
		}

		// Copy Round Entry
		$content['allrounds'][$iDays]['myrounds'][$iRounds] = $content['roundsonly'][$i];
		
		// Next round
		$iRounds++;
	}

	// --- Now we create the Pager ;)!
		// Fix for now of the list exceeds $CFG['MAX_PAGES_COUNT'] pages
		if ($pagenumbers > $content['web_maxpages'])
		{
			$content['ROUNDS_MOREPAGES'] = "*(More then " . $content['web_maxpages'] . " pages found)";
			$pagenumbers = $content['web_maxpages'];
		}
		else
			$content['ROUNDS_MOREPAGES'] = "&nbsp;";

		for ($i=0 ; $i < $pagenumbers ; $i++)
		{
			$content['ROUNDPAGES'][$i]['mypagebegin'] = ($i * $content['web_toprounds']);

			if ($content['current_pagebegin'] == $content['ROUNDPAGES'][$i]['mypagebegin'])
				$content['ROUNDPAGES'][$i]['mypagenumber'] = "<B>-> ".($i+1)." <-</B>";
			else
				$content['ROUNDPAGES'][$i]['mypagenumber'] = $i+1;

			// --- Set CSS Class
			if ( $i % 2 == 0 )
				$content['ROUNDPAGES'][$i]['cssclass'] = "line1";
			else
				$content['ROUNDPAGES'][$i]['cssclass'] = "line2";
			// --- 
		}
	// ---


}
else
	$content['roundsenabled'] = "false";

// --- 

// --- Parsen and Output
InitTemplateParser();
$page -> parser($content, "rounds.html");
$page -> output(); 
// --- 

?>