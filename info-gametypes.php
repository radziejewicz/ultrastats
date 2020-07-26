<?php
/*
	*********************************************************************
	* Copyright by Andre Lorbach | 2006, 2007, 2008						*
	* -> www.ultrastats.org <-											*
	*																	*
	* Use this script at your own risk!									*
	* -----------------------------------------------------------------	*
	* Gametypes File													*
	*																	*
	* -> Here you can list by gametype									*
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
	$content['TITLE'] = "Ultrastats :: GametypeInfo :: Server '" . $content['myserver']['Name'] . "'";	// Title of the Page 
else
	$content['TITLE'] = "Ultrastats :: GametypeInfo";
// --- 

// --- BEGIN Custom Code

// --- Get/Set Playersorting
if ( isset($_GET['id']) )
{
	// get and check
	$content['gametypeid'] = DB_RemoveBadChars($_GET['id']);
	
	// --- BEGIN LastRounds Code for front stats
	$sqlquery = "SELECT " .
						STATS_GAMETYPES . ".NAME, " . 
						STATS_GAMETYPES . ".DisplayName, " . 
						STATS_LANGUAGE_STRINGS . ".TEXT as Description " .
						" FROM " . STATS_GAMETYPES . 
						" LEFT OUTER JOIN (" . STATS_LANGUAGE_STRINGS . 
						") ON (" . 
						STATS_LANGUAGE_STRINGS . ".STRINGID =" . STATS_GAMETYPES . ".Description_id) " . 
						" WHERE " . STATS_GAMETYPES . ".NAME  = '" . $content['gametypeid'] . "' " . 
						" LIMIT 1 ";
	$result = DB_Query($sqlquery);
	$gametypevars = DB_GetSingleRow($result, true);
	if ( isset($gametypevars['NAME']) )
	{	
		// Enable Stats
		$content['gametypeenabled'] = "true";

		// --- Set Gametypename 
		if ( strlen($gametypevars['DisplayName']) > 0 )
			$content['GametypeDisplayName'] = $gametypevars['DisplayName'];
		else
			$content['GametypeDisplayName'] = $gametypevars['NAME'];
		// --- 

		// --- Set Gametypeimage
		$content['GametypeImage'] = $gl_root_path . "images/gametypes/normal/" . $content['gen_gameversion_picpath'] . "/" . $gametypevars['NAME'] . ".png";
		if ( !is_file($content['GametypeImage']) )
			$content['GametypeImage'] = $gl_root_path . "images/gametypes/no-pic.jpg";
		// --- 

		// --- Copy other values
		if ( isset($gametypevars['Description']) && strlen($gametypevars['Description']) > 0 )
			$content['Description'] = $gametypevars['Description'];
		else
			$content['Description'] = $content['LN_GAMETYPE_NODESCRIPTION'];
		$content['NAME'] = $gametypevars['NAME'];
		// --- 


		// --- Last Map Rounds 
		$sqlquery = "SELECT " .
							STATS_ROUNDS . ".ID, " .
							STATS_ROUNDS . ".TIMEADDED, " . 
							STATS_ROUNDS . ".ROUNDDURATION, " . 
							STATS_ROUNDS . ".AxisRoundWins, " . 
							STATS_ROUNDS . ".AlliesRoundWins, " .
							STATS_PLAYER_KILLS . ".PLAYERID, " . 
//							STATS_GAMETYPES . ".NAME as GameTypeName, " . 
//							STATS_GAMETYPES . ".DisplayName as GameTypeDisplayName, " . 
							STATS_MAPS . ".MAPNAME ," . 
							STATS_MAPS . ".DisplayName as MapDisplayName" . 
							" FROM " . STATS_ROUNDS . 
							" INNER JOIN (" . STATS_GAMETYPES . ", " . STATS_MAPS . ", " . STATS_PLAYER_KILLS .
							") ON (" . 
							STATS_GAMETYPES . ".ID=" . STATS_ROUNDS . ".GAMETYPE AND " . 
							STATS_MAPS . ".ID=" . STATS_ROUNDS . ".MAPID AND " . 
							STATS_PLAYER_KILLS . ".ROUNDID=" . STATS_ROUNDS . ".ID)" . 
							" WHERE " . STATS_GAMETYPES . ".NAME = '" . $content['gametypeid'] . "'" . 
							GetCustomServerWhereQuery( STATS_ROUNDS, false) . 
							" GROUP BY " . STATS_ROUNDS . ".ID" . 
							" ORDER BY TIMEADDED DESC LIMIT 20";
		$result = DB_Query($sqlquery);

		$content['lastrounds'] = DB_GetAllRows($result, true);
		if ( isset($content['lastrounds']) )
		{
			$content['lastroundsenable'] = "true";
			for($i = 0; $i < count($content['lastrounds']); $i++)
			{
				// --- Set Mapname 
				if ( strlen($content['lastrounds'][$i]['MapDisplayName']) > 0 )
					$content['lastrounds'][$i]['FinalMapDisplayName'] = $content['lastrounds'][$i]['MapDisplayName'];
				else
					$content['lastrounds'][$i]['FinalMapDisplayName'] = $content['lastrounds'][$i]['MAPNAME'];
				// --- 

				// --- Set Mapimage
				$content['lastrounds'][$i]['MapImage'] = $gl_root_path . "images/maps/thumbs/" . $content['lastrounds'][$i]['MAPNAME'] . ".jpg";
				if ( !is_file($content['lastrounds'][$i]['MapImage']) )
					$content['lastrounds'][$i]['MapImage'] = $gl_root_path . "images/maps/no-pic.jpg";
				// --- 

				// --- Set Display Time
				$content['lastrounds'][$i]['TimePlayed'] = date('Y-m-d H:i:s', $content['lastrounds'][$i]['TIMEADDED']);
				// --- 

				// --- Set Display Time
				$content['lastrounds'][$i]['Number'] = $i+1;
				// --- 

				// --- Set CSS Class
				if ( $i % 2 == 0 )
					$content['lastrounds'][$i]['cssclass'] = "line1";
				else
					$content['lastrounds'][$i]['cssclass'] = "line2";
				// --- 
			}
		}
		// --- 
	}
	else
		$content['iserror'] = "true";
}
else
{
	// Invalid ID!
	$content['iserror'] = "true";
}
// --- 

// --- Parsen and Output
InitTemplateParser();
$page -> parser($content, "info-gametypes.html");
$page -> output(); 
// --- 
?>