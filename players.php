<?php
/*
	*********************************************************************
	* Copyright by Andre Lorbach | 2006, 2007, 2008						*
	* -> www.ultrastats.org <-											*
	*																	*
	* Use this script at your own risk!									*
	* -----------------------------------------------------------------	*
	* PlayerList File													*
	*																	*
	* -> Players are listed in different ways on this site				*
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
	$content['TITLE'] = "Ultrastats :: Players :: Server '" . $content['myserver']['Name'] . "'";	// Title of the Page 
else
	$content['TITLE'] = "Ultrastats :: Players";
// --- 

// --- BEGIN Custom Code

// --- Read Vars
if ( isset($_GET['start']) )
	$content['current_pagebegin'] = intval(DB_RemoveBadChars($_GET['start']));
else
	$content['current_pagebegin'] = 0;

//Get/Set Playersorting
$content['sorting'] = "Kills";
if ( isset($_GET['sorting']) )
{
	// Check if valid
	if (	$_GET['sorting'] == "Kills" ||
			$_GET['sorting'] == "Deaths" ||
			$_GET['sorting'] == "Teamkills" ||
			$_GET['sorting'] == "Suicides" ||
			$_GET['sorting'] == "KillRatio") // || 
//			$_GET['sorting'] == "TotalSeconds" )	// FUCKING TODO!!
	{
		// Set new Sorting
		$content['sorting'] = DB_RemoveBadChars($_GET['sorting']);
	}
}
// ---

// --- Now get the players 
	// --- First get the Count and Set Pager Variables
	$sqlquery = "SELECT " .
						"count(" . STATS_PLAYERS . ".GUID) as AllPlayersCount " . 
						" FROM " . STATS_PLAYERS . 
						" WHERE Kills > " . $content['web_minkills'] . " " . 
						GetCustomServerWhereQuery(STATS_PLAYERS, false) . 
						GetBannedPlayerWhereQuery(STATS_PLAYERS, "GUID", false) . 
						" GROUP BY GUID " . 
						" ORDER BY " . $content['sorting'] . " DESC ";
	$content['players_count'] = DB_GetRowCount( $sqlquery );
	if ( $content['players_count'] > $content['web_topplayers'] ) 
	{
		$pagenumbers = $content['players_count'] / $content['web_topplayers'];

		// Check PageBeginValue
		if ( $content['current_pagebegin'] > $content['players_count'] )
			$content['current_pagebegin'] = 0;

		// Enable Player Pager
		$content['players_pagerenabled'] = "true";
	}
	else
	{
		$content['current_pagebegin'] = 0;
		$pagenumbers = 0;
	}
	// --- 
// --- Now the final query !
$sqlquery = "SELECT " .
					STATS_PLAYERS . ".GUID, " . 
					"sum(" . STATS_PLAYERS . ".Kills) as Kills, " . 
					"sum(" . STATS_PLAYERS . ".Deaths) as Deaths, " . 
					"sum(" . STATS_PLAYERS . ".Teamkills) as Teamkills, " .
					"sum(" . STATS_PLAYERS . ".Suicides) as Suicides, " . 
//					"sum(" . STATS_TIME . ".TIMEPLAYED) as TotalSeconds, " . 
//					"round(AVG( " . STATS_PLAYERS . ".KillRatio),2) as KillRatio " .
					"sum(" . STATS_PLAYERS . ".Kills) / sum(" . STATS_PLAYERS . ".Deaths) as KillRatio " .	// TRUE l33tAGE!
//					STATS_ALIASES . ".Alias, " . 
//					STATS_ALIASES . ".AliasAsHtml " .
					" FROM " . STATS_PLAYERS . 
//					" INNER JOIN (" . STATS_TIME . 
//					") ON (" . 
//					STATS_PLAYERS . ".GUID=" . STATS_TIME . ".PLAYERID) " . 
					" WHERE Kills > " . $content['web_minkills'] . " " . 
					GetCustomServerWhereQuery(STATS_PLAYERS, false) . 
					GetBannedPlayerWhereQuery(STATS_PLAYERS, "GUID", false) . 
//					GetCustomServerWhereQuery( STATS_TIME, false) . 
					" GROUP BY " . STATS_PLAYERS . ".GUID " . 
					" ORDER BY " . $content['sorting'] . " DESC " . 
					" LIMIT " . $content['current_pagebegin'] . " , " . $content['web_topplayers'];

$result = DB_Query($sqlquery);
$content['players'] = DB_GetAllRows($result, true);
if ( isset($content['players']) )
{
	// Enable Player Stats
	$content['playersenabled'] = "true";

	// Extend PlayerAliases
	FindAndFillTopAliases($content['players'], "GUID", "Alias", "AliasAsHtml" );

	// Extend with Time Played
	FindAndFillWithTime($content['players'], "GUID", "TotalSeconds", "TimePlayedString" );

	// --- Find Max Time Value first
	for($i = 0; $i < count($content['players']); $i++)
	{
		if ( !isset($maxpercent) )
			$maxpercent = $content['players'][$i]['TotalSeconds'];
		else if ( $content['players'][$i]['TotalSeconds'] > $maxpercent )
			$maxpercent = $content['players'][$i]['TotalSeconds'];
	}

	// This makes up to 125pix width bars
	$maxpercent = intval( $maxpercent / 100 );
	// --- 

//	// --- Lets get the MAX KillRatio first
	GetAndSetMaxKillRation();
//	// --- 

	for($i = 0; $i < count($content['players']); $i++)
	{
		// --- Set Number
		$content['players'][$i]['Number'] = $i+1 + $content['current_pagebegin']; 
		// ---

/*		// --- Set Ratio
		if ( $content['players'][$i]['Deaths'] > 0 )
			$content['players'][$i]['KillRatio'] = round($content['players'][$i]['Kills'] / $content['players'][$i]['Deaths'], 2);
		else
			$content['players'][$i]['KillRatio'] = $content['players'][$i]['Kills'];
		// ---
*/
		// --- Set CSS Class
		if ( $i % 2 == 0 )
			$content['players'][$i]['cssclass'] = "line1";
		else
			$content['players'][$i]['cssclass'] = "line2";
		// --- 

		// --- Set Bar Image
		$content['players'][$i]['KillBarWidth'] = intval($content['players'][$i]['TotalSeconds'] / $maxpercent); //
		$content['players'][$i]['BarImageLeft'] = $gl_root_path . "images/bars/bar-small/green_left_9.png";
		$content['players'][$i]['BarImageMiddle'] = $gl_root_path . "images/bars/bar-small/green_middle_9.png";
		$content['players'][$i]['BarImageRight'] = $gl_root_path . "images/bars/bar-small/green_right_9.png";
		// --- 


		// --- Set KillRation Values and Bars
		$content['players'][$i]['KillRatio'] = $content['players'][$i]['KillRatio'];
		$content['players'][$i]['BarImageKillRatioMinus'] = $gl_root_path . "images/bars/bar-small/red_middle_9.png";
		$content['players'][$i]['BarImageKillRatioPlus'] = $gl_root_path . "images/bars/bar-small/green_middle_9.png";

		if ( isset($content['MaxKillRatio']) )
		{
			// Now we set the Width of the images
			if ( $content['players'][$i]['KillRatio'] > 1 )
			{
				$content['players'][$i]['KillRatioWidthMinus'] = $content['MaxPixelWidth'];
				$content['players'][$i]['KillRatioWidthMinusText'] = "";
			}
			else
			{
				$content['players'][$i]['KillRatioWidthMinus'] = intval($content['players'][$i]['KillRatio'] * $content['MaxPixelWidth']);
				$content['players'][$i]['KillRatioWidthMinusText'] =  $content['players'][$i]['KillRatioWidthMinus'] . "% of 1:0 Ratio";;
			}

			if ( $content['players'][$i]['KillRatio'] < 1 )
			{
				$content['players'][$i]['KillRatioWidthPlus'] = "0";
				$content['players'][$i]['KillRatioWidthPlusText'] = "";
			}
			else
			{
				$content['players'][$i]['KillRatioWidthPlus'] = intval( ($content['players'][$i]['KillRatio'] / ($content['MaxKillRatio']/$content['MaxPixelWidth'])) );
				if ( $content['players'][$i]['KillRatioWidthPlus'] > 100 ) 
					$content['players'][$i]['KillRatioWidthPlus'] = 100;

				$content['players'][$i]['KillRatioWidthPlusText'] = $content['players'][$i]['KillRatioWidthPlus'] . "% of best Ratio (Which is " . $content['MaxKillRatio'] . ")";
			}
		}
		else
		{
			$content['players'][$i]['KillRatioWidthMinus'] = "0";
			$content['players'][$i]['KillRatioWidthPlus'] = "0";
		}
		// --- 
	}

	// --- Now we create the Pager ;)!
		// Fix for now of the list exceeds $CFG['MAX_PAGES_COUNT'] pages
		if ($pagenumbers > $content['web_maxpages'])
		{
			$content['PLAYERS_MOREPAGES'] = "*(More then " . $content['web_maxpages'] . " pages found)";
			$pagenumbers = $content['web_maxpages'];
		}
		else
			$content['PLAYERS_MOREPAGES'] = "&nbsp;";

		for ($i=0 ; $i < $pagenumbers ; $i++)
		{
			$content['PLAYERPAGES'][$i]['mypagebegin'] = ($i * $content['web_topplayers']);

			if ($content['current_pagebegin'] == $content['PLAYERPAGES'][$i]['mypagebegin'])
				$content['PLAYERPAGES'][$i]['mypagenumber'] = "<B>-> ".($i+1)." <-</B>";
			else
				$content['PLAYERPAGES'][$i]['mypagenumber'] = $i+1;

			// --- Set CSS Class
			if ( $i % 2 == 0 )
				$content['PLAYERPAGES'][$i]['cssclass'] = "line1";
			else
				$content['PLAYERPAGES'][$i]['cssclass'] = "line2";
			// --- 
		}
	// ---

//	PLAYERS_MOREPAGES
}
else
	$content['playersenabled'] = "false";
// --- 

// --- Parsen and Output
InitTemplateParser();
$page -> parser($content, "players.html");
$page -> output(); 
// --- 

?>