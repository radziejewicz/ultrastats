<?php
/*
	*********************************************************************
	* Copyright by Andre Lorbach | 2006, 2007, 2008						*
	* -> www.ultrastats.org <-											*
	*																	*
	* Use this script at your own risk!									*
	* -----------------------------------------------------------------	*
	* Chatlogging script for Iframe										*
	*																	*
	* -> Shows Chatlog of a round										*
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
	$content['TITLE'] = "Ultrastats :: Round Chatlog :: Server '" . $content['myserver']['Name'] . "'";	// Title of the Page 
else
	$content['TITLE'] = "Ultrastats :: Round Chatlog";
// --- 

// --- BEGIN Custom Code

// --- Get/Set Playersorting
if ( isset($_GET['id']) )
{
	// get and check
	$content['roundid'] = intval( DB_RemoveBadChars($_GET['id']) );
	
	if ( $content['roundid'] <= 0 )
	{
		// Invalid Guid!
		$content['iserror'] = "true";
	}
	else
	{	
		// All other Team based modes!
		$content['roundschatlog'] = "true";

		// --- Read Chatlog ;)!
		$sqlquery = "SELECT " .
							STATS_CHAT . ".PLAYERID, " . 
//							STATS_ALIASES . ".Alias, " . 
//							STATS_ALIASES . ".AliasAsHtml, " .
							STATS_CHAT . ".TextSaid " .
							" FROM " . STATS_CHAT . 
//							" INNER JOIN (" . STATS_ALIASES . 
//							") ON (" . 
//							STATS_ALIASES . ".PLAYERID=" . STATS_CHAT . ".PLAYERID) " . 
							" WHERE " . STATS_CHAT . ".ROUNDID=" . $content['roundid'] . 
							GetBannedPlayerWhereQuery(STATS_CHAT, "PLAYERID", false) . 
							" GROUP BY " . STATS_CHAT . ".TextSaid" . 
							" ORDER BY " . STATS_CHAT . ".ID ";

		// NO Order should be like said in the game
		$result = DB_Query($sqlquery);
		$content['ChatLog'] = DB_GetAllRows($result, true);
		
		if ( isset($content['ChatLog']) )
		{
			for($i = 0; $i < count($content['ChatLog']); $i++)
			{
				// --- Set CSS Class
				if ( $i % 2 == 0 )
					$content['ChatLog'][$i]['cssclass'] = "line0";
				else
					$content['ChatLog'][$i]['cssclass'] = "line1";
				// --- 

				// --- HTML Valid Text
				$content['ChatLog'][$i]['TextSaidAsHtml'] = GetPlayerNameAsHTML( $content['ChatLog'][$i]['TextSaid'] );
				// --- 
			}
			
			// Extend Player aliases
			FindAndFillTopAliases($content['ChatLog'], "PLAYERID", "Alias", "AliasAsHtml" );
		}
	}
}
else
{
	// Invalid ID!
	$content['iserror'] = "true";
}
// --- 

// --- Parsen and Output
InitTemplateParser();
$page -> parser($content, "rounds-chatlog.html");
$page -> output(); 
// --- 

?>