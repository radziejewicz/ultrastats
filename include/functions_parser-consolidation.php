<?php
/*
	*********************************************************************
	* Copyright by Andre Lorbach | 2006!								*
	* -> www.ultrastats.org <-											*
	*																	*
	* Use this script at your own risk!									*
	* -----------------------------------------------------------------	*
	* Parser Medal functions											*
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

function SetLastUpdateTime( $serverid )
{
	global $content;

	// Run a simple update for the Server
	ProcessUpdateStatement("UPDATE " . STATS_SERVERS . " SET 
								LastUpdate = " . time() . "
								WHERE ID = " . $serverid, true); 

	// Run a simple insert globally !
	InsertOrUpdateMedalValue(	"global_lastupdate", 
								"Last Stats Update", 
								-1, 
								"global_lastupdate", 
								time(), 
								"", 
								0,
								0 );
}

function RunServerConsolidation( $serverid )
{
	global $myserver, $content;

	// Now we create overall Medals!
	if ( $serverid != -1 ) 
		PrintHTMLDebugInfo( DEBUG_INFO, "Consolidation", "Starting Server Consolidation Calculation ...");
	else
	{
		PrintHTMLDebugInfo( DEBUG_INFO, "Consolidation", "Starting Total Consolidation Calculation ...");
		
		// Init thise case we delete the total stats for this server first!
		ProcessDeleteStatement( "DELETE FROM " . STATS_CONSOLIDATED . " " . "WHERE SERVERID = " . $serverid . " AND NAME LIKE 'server_total%' " );
		PrintHTMLDebugInfo( DEBUG_INFO, "Consolidation", "Deleted '" . GetRowsAffected() . "' Consolidation data ...");
	}

	if ( $serverid != -1 )
	{
		$wheresinglesql = " WHERE SERVERID = " . $serverid;
		$whereaddsql = " AND SERVERID = " . $serverid;
		$groupbysql = " GROUP BY SERVERID "; 
	}
	else
	{
		$wheresinglesql = "";
		$whereaddsql = "";
		$groupbysql = "";
	}

	// Clean up Server Stats
	ProcessDeleteStatement("DELETE FROM " . STATS_CONSOLIDATED . " WHERE NAME LIKE 'server_%' AND SERVERID = " . $serverid);

	// ========================== Top Values =================================
	// --- Calc: server_top_map
	$sqlquery =	"SELECT " .
				"count(" . STATS_ROUNDS . ".ID) as MapCount, " .
				STATS_ROUNDS . ".MAPID, " .
				STATS_MAPS . ".MAPNAME " . 
				" FROM " . STATS_ROUNDS .
				" INNER JOIN (" . STATS_MAPS . 
				") ON (" . 
				STATS_MAPS . ".ID=" . STATS_ROUNDS . ".MAPID)" . 
				$wheresinglesql .
				" GROUP BY " . STATS_ROUNDS . ".MAPID " . 
				" ORDER BY MapCount DESC LIMIT 1";
	PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Consolidation", "server_top_map: " . $sqlquery );
	$topvalue = ReturnMedalValue($sqlquery);
	if ( isset($topvalue['MapCount']) )
		InsertOrUpdateMedalValue(	"server_top_map", 
									"Top played map", 
									$serverid, 
									"server_top_map", 
									$topvalue['MapCount'], 
									$topvalue['MAPNAME'], 
									0,
									0 );
	else
		PrintHTMLDebugInfo( DEBUG_INFO, "Consolidation", "server_top_map is empty!" );
	// --- 


	// --- Calc: server_top_gametype
	$sqlquery =	"SELECT " .
				"count(" . STATS_ROUNDS . ".ID) as GametypeCount, " .
				STATS_ROUNDS . ".GAMETYPE, " .
				STATS_GAMETYPES . ".NAME as GameTypeName " . 
				" FROM " . STATS_ROUNDS . 
				" INNER JOIN (" . STATS_GAMETYPES . 
				") ON (" . 
				STATS_GAMETYPES . ".ID=" . STATS_ROUNDS . ".GAMETYPE) " . 
				$wheresinglesql .
				" GROUP BY " . STATS_ROUNDS . ".GAMETYPE " . 
				" ORDER BY GametypeCount DESC LIMIT 1";
	PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Consolidation", "server_top_gametype: " . $sqlquery );
	$topvalue = ReturnMedalValue($sqlquery);
	if ( isset($topvalue['GametypeCount']) )
		InsertOrUpdateMedalValue(	"server_top_gametype", 
									"Top played gametype", 
									$serverid, 
									"server_top_gametype", 
									$topvalue['GametypeCount'], 
									$topvalue['GameTypeName'], 
									0,
									1 );
	else
		PrintHTMLDebugInfo( DEBUG_INFO, "Consolidation", "server_top_gametype is empty!" );
	// --- 
	// ==========================            =================================


	// ========================== Total Values =================================
	// --- Calc: server_total_rounds
	$sqlquery =	"SELECT " .
				"count(" . STATS_ROUNDS . ".ID) as MyCount " .
				" FROM " . STATS_ROUNDS . 
				$wheresinglesql .
				$groupbysql . 
				" ORDER BY MyCount DESC LIMIT 1";
	PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Consolidation", "server_total_rounds: " . $sqlquery );
	$topvalue = ReturnMedalValue($sqlquery);
	if ( isset($topvalue['MyCount']) )
		InsertOrUpdateMedalValue(	"server_total_rounds", 
									"Total played rounds", 
									$serverid, 
									"server_total_rounds", 
									$topvalue['MyCount'], 
									"", 
									0,
									0 );
	else
		PrintHTMLDebugInfo( DEBUG_INFO, "Consolidation", "server_total_rounds is empty!" );
	// --- 

	// --- Calc: server_total_players
	$sqlquery =	"SELECT " .
				"count(" . STATS_PLAYERS . ".GUID) as MyCount " .
				" FROM " . STATS_PLAYERS . 
				$wheresinglesql .
				$groupbysql . 
				" ORDER BY MyCount DESC LIMIT 1";
	PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Consolidation", "server_total_players: " . $sqlquery );
	$topvalue = ReturnMedalValue($sqlquery);
	if ( isset($topvalue['MyCount']) )
		InsertOrUpdateMedalValue(	"server_total_players", 
									"Total Players", 
									$serverid, 
									"server_total_players", 
									$topvalue['MyCount'], 
									"", 
									0,
									1 );
	else
		PrintHTMLDebugInfo( DEBUG_INFO, "Consolidation", "server_total_players is empty!" );
	// --- 


	// --- Calc: server_total_kills
	$sqlquery =	"SELECT " .
				"count(" . STATS_PLAYER_KILLS . ".ID) as MyCount " .
				" FROM " . STATS_PLAYER_KILLS . 
				$wheresinglesql .
				$groupbysql . 
				" ORDER BY MyCount DESC LIMIT 1";
	PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Consolidation", "server_total_kills: " . $sqlquery );
	$topvalue = ReturnMedalValue($sqlquery);
	if ( isset($topvalue['MyCount']) )
		InsertOrUpdateMedalValue(	"server_total_kills", 
									"Total Kills", 
									$serverid, 
									"server_total_kills", 
									$topvalue['MyCount'], 
									"", 
									0,
									2 );
	else
		PrintHTMLDebugInfo( DEBUG_INFO, "Consolidation", "server_total_kills is empty!" );
	// --- 


	// --- Calc: server_total_ratio
	$sqlquery =	"SELECT " .
				STATS_PLAYERS . ".GUID, " .
				"sum(" . STATS_PLAYERS . ".Kills) as Kills, " .
				"sum(" . STATS_PLAYERS . ".Deaths) as Deaths " .
				" FROM " . STATS_PLAYERS . 
				$wheresinglesql .
				" GROUP BY " . STATS_PLAYERS . ".GUID ";
	$result = DB_Query($sqlquery);
	$tmpplayers = DB_GetAllRows($result, true);
	if ( isset($tmpplayers) )
	{
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Consolidation", "server_total_ratio: " . $sqlquery );
		for($i = 0; $i < count($tmpplayers); $i++)
		{
			// Calc current ration
			if ( $tmpplayers[$i]['Deaths'] > 0 )
				$tmpplayers[$i]['KillRatio'] = round($tmpplayers[$i]['Kills'] / $tmpplayers[$i]['Deaths'], 2);
			else
				$tmpplayers[$i]['KillRatio'] = $tmpplayers[$i]['Kills'];
			// ---

			if ( !isset($bestration['KillRatio']) || $tmpplayers[$i]['KillRatio'] > $bestration['KillRatio'] )
			{
				// Set new best player
				$bestration['GUID'] = $tmpplayers[$i]['GUID'];
				$bestration['KillRatio'] = $tmpplayers[$i]['KillRatio'];
			}
		}

		// Insert now
		InsertOrUpdateMedalValue(	"server_total_ratio", 
									"Best Ratio", 
									$serverid, 
									"server_total_ratio", 
									($bestration['KillRatio'] * 100), // *100 to save last 2 numbers behind the , !
									"", 
									$bestration['GUID'],
									3 );
	}
	else
		PrintHTMLDebugInfo( DEBUG_INFO, "Consolidation", "server_total_ratio is empty!" );
	// --- 


	// --- Calc: server_total_time
	$sqlquery =	"SELECT " .
				"sum(" . STATS_TIME . ".TIMEPLAYED) as MyTime " .
				" FROM " . STATS_TIME . 
				$wheresinglesql .
				$groupbysql . 
				" ORDER BY MyTime DESC LIMIT 1";
	PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Consolidation", "server_total_time: " . $sqlquery );
	$topvalue = ReturnMedalValue($sqlquery);
	if ( isset($topvalue['MyTime']) )
		InsertOrUpdateMedalValue(	"server_total_time", 
									"Total time played", 
									$serverid, 
									"server_total_time", 
									$topvalue['MyTime'], 
									"", 
									0,
									4 );
	else
		PrintHTMLDebugInfo( DEBUG_INFO, "Consolidation", "server_total_time is empty!" );
	// --- 


	// ==========================              =================================

	// Finished
	PrintHTMLDebugInfo( DEBUG_INFO, "Consolidation", "Finished Consolidation Calculation...");
}

?>