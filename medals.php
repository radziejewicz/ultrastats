<?php
/*
	*********************************************************************
	* Copyright by Andre Lorbach | 2006, 2007, 2008						*
	* -> www.ultrastats.org <-											*
	*																	*
	* Use this script at your own risk!									*
	* -----------------------------------------------------------------	*
	* Weapon Detail	File												*
	*																	*
	* -> Display statistics per Weapon									*
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
include($gl_root_path . 'include/functions_parser-medals.php');

InitUltraStats();
IncludeLanguageFile( $gl_root_path . '/lang/' . $LANG . '/main.php' );
InitFrontEndDefaults();	// Only in WebFrontEnd
// ***					*** //

// --- CONTENT Vars
if ( isset($content['myserver']) ) 
{
	$content['TITLE'] = "Ultrastats :: Medal :: Server '" . $content['myserver']['Name'] . "'";	// Title of the Page 
	$serverid = $content['myserver']['ID'];
}
else
{
	$content['TITLE'] = "Ultrastats :: Medal ";
	$serverid = -1;
}
// --- 

// --- BEGIN Custom Code

// --- Read Vars
if ( isset($_GET['mostkills_start']) )
	$content['current_mostkills_pagebegin'] = intval(DB_RemoveBadChars($_GET['mostkills_start']));
else
	$content['current_mostkills_pagebegin'] = 0;

if ( isset($_GET['killedby_start']) )
	$content['current_killedby_pagebegin'] = intval(DB_RemoveBadChars($_GET['killedby_start']));
else
	$content['current_killedby_pagebegin'] = 0;
// --- 

// --- Init MedalSQLCode!
CreateMedalsSQLCode($serverid);
// --- 

// --- Get/Set Playersorting
if ( isset($_GET['id']) )
{
	// get and check
	$content['medalid'] = DB_RemoveBadChars($_GET['id']);
	
	// --- BEGIN Basic Medal Info's 
	$sqlquery = "SELECT " .
						STATS_CONSOLIDATED . ".NAME, " . 
						STATS_CONSOLIDATED . ".DisplayName, " . 
						STATS_CONSOLIDATED . ".VALUE_INT, " . 
						STATS_CONSOLIDATED . ".VALUE_TXT, " . 
						STATS_CONSOLIDATED . ".DescriptionID, " . 
						STATS_CONSOLIDATED . ".PLAYER_ID " . 
						" FROM " . STATS_CONSOLIDATED . 
						" WHERE " . STATS_CONSOLIDATED . ".NAME = '" . $content['medalid'] . "' " .
						GetCustomServerWhereQuery(STATS_CONSOLIDATED, false, true); 
	$result = DB_Query($sqlquery);
	$tmpvars = DB_GetSingleRow($result, true);
	if ( isset($tmpvars) )
	{
		// Enable Stats
		$content['medalsenabled'] = "true";

		// Append to title!
		$content['TITLE'] .= " :: '" . $tmpvars['DisplayName'] . "' Medal";

		// Copy var names!
		$content['medalname'] = $tmpvars['NAME'];
		$content['medaldisplayname'] = $tmpvars['DisplayName'];
		$content['LN_MEDAL_DETAILS'] = GetAndReplaceLangStr( $content['LN_MEDAL_DETAILS'], "'" . $content['medaldisplayname'] . "'");

		// --- Get Description 
		$content['medaldescription'] = GetTextFromDescriptionID( $tmpvars['DescriptionID'], $content['LN_MEDAL_NODESCRIPTION'] );

		// --- Most kills with this Weapon
			// --- First get the Count and Set Pager Variables
			$sqlquery = $content['medals'][$content['medalid']]['sql'] . " DESC";
			$result = DB_Query($sqlquery);
			$content['mostkills_count'] = DB_GetRowCountByResult( $result );
			$tmpvars = DB_GetSingleRow($result, true);
			$content['mostskills_maxkills'] = $tmpvars['AllKills'];

			if ( $content['mostkills_count'] > $content['web_detaillistsplayers'] ) 
			{
				$mostkills_pagenumbers = $content['mostkills_count'] / $content['web_detaillistsplayers'];

				// Check PageBeginValue
				if ( $content['current_mostkills_pagebegin'] > $content['mostkills_count'] )
					$content['current_mostkills_pagebegin'] = 0;

				// Enable Player Pager
				$content['mostkills_pagerenabled'] = "true";
			}
			else
			{
				$content['current_mostkills_pagebegin'] = 0;
				$mostkills_pagenumbers = 0;
			}
			// --- 

		$sqlquery = $content['medals'][$content['medalid']]['sql'] . 
							" DESC LIMIT " . $content['current_mostkills_pagebegin'] . " , " . $content['web_detaillistsplayers'];
//		echo $sqlquery;
		$result = DB_Query($sqlquery);
		$content['mostkills'] = DB_GetAllRows($result, true);
		if ( isset($content['mostkills']) )
		{
			$content['mostkillssenabled'] = "true";

			// Extend PlayerAliases
			FindAndFillTopAliases($content['mostkills'], $content['medals'][$content['medalid']]['GroupedPlayerID'], "Alias", "AliasAsHtml" );

			// Set Max Percent for bars
			$maxpercent = $content['mostskills_maxkills']; // $content['mostkills'][0]['AllKills'];

			for($i = 0; $i < count($content['mostkills']); $i++)
			{
				// --- Set Number
				$content['mostkills'][$i]['Number'] = $i+1 + $content['current_mostkills_pagebegin'];
				// ---

				// --- Set CSS Class
				if ( $i % 2 == 0 )
					$content['mostkills'][$i]['cssclass'] = "line1";
				else
					$content['mostkills'][$i]['cssclass'] = "line2";
				// --- 

				// --- Set Bar Image
				$content['mostkills'][$i]['KillBarPercent'] = intval(($content['mostkills'][$i]['AllKills'] / $maxpercent) * 100);
				$content['mostkills'][$i]['KillBarWidth'] = $content['mostkills'][$i]['KillBarPercent'] - 9; // Percentage Bar !

				$content['mostkills'][$i]['BarImageLeft'] = $gl_root_path . "images/bars/bar-small/blue_left_9.png";
				$content['mostkills'][$i]['BarImageMiddle'] = $gl_root_path . "images/bars/bar-small/blue_middle_9.png";
				$content['mostkills'][$i]['BarImageRight'] = $gl_root_path . "images/bars/bar-small/blue_right_9.png";
				// --- 
			}

			// --- Now we create the Pager ;)!
				// Fix for now of the list exceeds $CFG['MAX_PAGES_COUNT'] pages
				if ($mostkills_pagenumbers > $content['web_maxpages'])
				{
					$content['MOSTKILLS_MOREPAGES'] = "*(More then " . $content['web_maxpages'] . " pages found)";
					$mostkills_pagenumbers = $content['web_maxpages'];
				}
				else
					$content['MOSTKILLS_MOREPAGES'] = "&nbsp;";

				for ($i=0 ; $i < $mostkills_pagenumbers ; $i++)
				{
					$content['MOSTKILLSPAGES'][$i]['mypagebegin'] = ($i * $content['web_detaillistsplayers']);

					if ($content['current_mostkills_pagebegin'] == $content['MOSTKILLSPAGES'][$i]['mypagebegin'])
						$content['MOSTKILLSPAGES'][$i]['mypagenumber'] = "<B>-> ".($i+1)." <-</B>";
					else
						$content['MOSTKILLSPAGES'][$i]['mypagenumber'] = $i+1;

					// --- Set CSS Class
					if ( $i % 2 == 0 )
						$content['MOSTKILLSPAGES'][$i]['cssclass'] = "line1";
					else
						$content['MOSTKILLSPAGES'][$i]['cssclass'] = "line2";
					// --- 
				}
			// ---
		}
		// --- 
	}
	else
		$content['iserror'] = "true";
	// ---
}
else
{
	// Invalid ID!
	$content['iserror'] = "true";
}
// --- 

// --- Parsen and Output
InitTemplateParser();
$page -> parser($content, "medals.html");
$page -> output(); 
// --- 
?>