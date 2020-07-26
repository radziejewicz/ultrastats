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

// --- We use an medal array to save the SQL Codes, so we can use them on another page at a later step. 
function CreateMedalsSQLCode( $serverid )
{
	global $myserver, $content;

	// Now we create overall Medals!
	if ( $serverid != -1 )
	{
		$wheresinglesql = " WHERE SERVERID = " . $serverid;
		$whereaddsql = " AND SERVERID = " . $serverid;
	}
	else
	{
		$wheresinglesql = " WHERE 1 = 1 ";	// needed as we scan for banned players also | Dummy 1=1 for AND queries
		$whereaddsql = "";
	}

	// --- PRO MEDALS
	$content['medals']['medal_pro_killer']['sql'] = "SELECT " .
					STATS_PLAYER_KILLS . ".PLAYERID, " .
					" sum(" . STATS_PLAYER_KILLS . ".Kills) as AllKills" . 
					" FROM " . STATS_PLAYER_KILLS . 
					$wheresinglesql .
					GetBannedPlayerWhereQuery(STATS_PLAYER_KILLS, "PLAYERID", false) . 
					" GROUP BY " . STATS_PLAYER_KILLS . ".PLAYERID " . 
					" ORDER BY AllKills";

	$content['medals']['medal_pro_headshot']['sql'] = "SELECT " .
				STATS_PLAYER_KILLS . ".PLAYERID, " .
				" sum(" . STATS_PLAYER_KILLS . ".Kills) as AllKills" . 
				" FROM " . STATS_PLAYER_KILLS . 
				" INNER JOIN (" . STATS_HITLOCATIONS . 
				") ON (" . 
				STATS_HITLOCATIONS . ".ID=" . STATS_PLAYER_KILLS . ".HITLOCATIONID) " . 
				" WHERE " . STATS_HITLOCATIONS . ".BODYPART = '" . "head" . "'" . 
				$whereaddsql . 
				GetBannedPlayerWhereQuery(STATS_PLAYER_KILLS, "PLAYERID", false) . 
				" GROUP BY " . STATS_PLAYER_KILLS . ".PLAYERID " . 
				" ORDER BY AllKills";

	$content['medals']['medal_pro_explosivekiller']['sql'] = "SELECT " .
				STATS_PLAYER_KILLS . ".PLAYERID, " .
				" sum(" . STATS_PLAYER_KILLS . ".Kills) as AllKills" . 
				" FROM " . STATS_PLAYER_KILLS . 
				" INNER JOIN (" . STATS_DAMAGETYPES . 
				") ON (" . 
				STATS_DAMAGETYPES . ".ID=" . STATS_PLAYER_KILLS . ".DAMAGETYPEID) " . 
				" WHERE " . STATS_DAMAGETYPES . ".DAMAGETYPE IN ('MOD_GRENADE_SPLASH' )" . 
				$whereaddsql . 
				GetBannedPlayerWhereQuery(STATS_PLAYER_KILLS, "PLAYERID", false) . 
				" GROUP BY " . STATS_PLAYER_KILLS . ".PLAYERID " . 
				" ORDER BY AllKills";

	$content['medals']['medal_pro_pistol']['sql'] = "SELECT " . 
				STATS_PLAYER_KILLS . ".PLAYERID, " . 
				" sum(" . STATS_PLAYER_KILLS . ".Kills) as AllKills" . 
				" FROM " . STATS_PLAYER_KILLS . 
				" INNER JOIN (" . STATS_WEAPONS . 
				") ON (" . 
				STATS_WEAPONS . ".ID=" . STATS_PLAYER_KILLS . ".WEAPONID) " . 
				" WHERE " . STATS_WEAPONS . ".WeaponType IN ('" . WEAPONTYPE_PISTOL . "')" . 
				$whereaddsql . 
				GetBannedPlayerWhereQuery(STATS_PLAYER_KILLS, "PLAYERID", false) . 
				" GROUP BY " . STATS_PLAYER_KILLS . ".PLAYERID " . 
				" ORDER BY AllKills";

	if (	$content['gen_gameversion'] == COD || 
			$content['gen_gameversion'] == CODUO || 
			$content['gen_gameversion'] == COD2 )
	{
		$content['medals']['medal_pro_slappyhappy']['sql'] = "SELECT " .
					STATS_PLAYER_KILLS . ".PLAYERID, " .
					" sum(" . STATS_PLAYER_KILLS . ".Kills) as AllKills" . 
					" FROM " . STATS_PLAYER_KILLS . 
					" INNER JOIN (" . STATS_DAMAGETYPES . 
					") ON (" . 
					STATS_DAMAGETYPES . ".ID=" . STATS_PLAYER_KILLS . ".DAMAGETYPEID) " . 
					" WHERE " . STATS_DAMAGETYPES . ".DAMAGETYPE IN ('MOD_MELEE')" . 
					$whereaddsql . 
					GetBannedPlayerWhereQuery(STATS_PLAYER_KILLS, "PLAYERID", false) . 
					" GROUP BY " . STATS_PLAYER_KILLS . ".PLAYERID " . 
					" ORDER BY AllKills";

		$content['medals']['medal_pro_sniper']['sql'] = "SELECT " .
					STATS_PLAYER_KILLS . ".PLAYERID, " .
					" sum(" . STATS_PLAYER_KILLS . ".Kills) as AllKills" . 
					" FROM " . STATS_PLAYER_KILLS . 
					" INNER JOIN (" . STATS_WEAPONS . 
					") ON (" . 
					STATS_WEAPONS . ".ID=" . STATS_PLAYER_KILLS . ".WEAPONID) " . 
					" WHERE " . STATS_WEAPONS . ".INGAMENAME IN ('springfield_mp', 'enfield_scope_mp', 'kar98k_sniper_mp', 'mosin_nagant_sniper_mp')" . 
					$whereaddsql . 
					GetBannedPlayerWhereQuery(STATS_PLAYER_KILLS, "PLAYERID", false) . 
					" GROUP BY " . STATS_PLAYER_KILLS . ".PLAYERID " . 
					" ORDER BY AllKills";
	}
	else if($content['gen_gameversion'] == COD4)
	{
		$content['medals']['medal_pro_knifekills']['sql'] = "SELECT " . 
					STATS_PLAYER_KILLS . ".PLAYERID, " . 
					" sum(" . STATS_PLAYER_KILLS . ".Kills) as AllKills" . 
					" FROM " . STATS_PLAYER_KILLS . 
					" INNER JOIN (" . STATS_DAMAGETYPES . 
					") ON (" . 
					STATS_DAMAGETYPES . ".ID=" . STATS_PLAYER_KILLS . ".DAMAGETYPEID) " . 
					" WHERE " . STATS_DAMAGETYPES . ".DAMAGETYPE IN ('MOD_MELEE')" . 
					$whereaddsql . 
					GetBannedPlayerWhereQuery(STATS_PLAYER_KILLS, "PLAYERID", false) . 
					" GROUP BY " . STATS_PLAYER_KILLS . ".PLAYERID " . 
					" ORDER BY AllKills";

		$content['medals']['medal_pro_sniper']['sql'] = "SELECT " . 
			STATS_PLAYER_KILLS . ".PLAYERID, " . 
			" sum(" . STATS_PLAYER_KILLS . ".Kills) as AllKills" . 
			" FROM " . STATS_PLAYER_KILLS . 
			" INNER JOIN (" . STATS_WEAPONS . 
			") ON (" . 
			STATS_WEAPONS . ".ID=" . STATS_PLAYER_KILLS . ".WEAPONID) " . 
			" WHERE " . STATS_WEAPONS . ".INGAMENAME IN ('barrett_mp', 'barrett_acog_mp', 'dragunov_mp', 'dragunov_acog_mp', 'm21_mp', 'm21_acog_mp', 'm40a3_mp', 'm40a3_acog_mp', 'remington700_mp', 'remington700_acog_mp')" . 
			$whereaddsql . 
			GetBannedPlayerWhereQuery(STATS_PLAYER_KILLS, "PLAYERID", false) . 
			" GROUP BY " . STATS_PLAYER_KILLS . ".PLAYERID " . 
			" ORDER BY AllKills";

		$content['medals']['medal_pro_claymorec4']['sql'] = "SELECT " .
					STATS_PLAYER_KILLS . ".PLAYERID, " .
					" sum(" . STATS_PLAYER_KILLS . ".Kills) as AllKills" . 
					" FROM " . STATS_PLAYER_KILLS . 
					" INNER JOIN (" . STATS_WEAPONS . 
					") ON (" . 
					STATS_WEAPONS . ".ID=" . STATS_PLAYER_KILLS . ".WEAPONID) " . 
					" WHERE " . STATS_WEAPONS . ".INGAMENAME IN ('c4_mp', 'claymore_mp')" . 
					$whereaddsql . 
					GetBannedPlayerWhereQuery(STATS_PLAYER_KILLS, "PLAYERID", false) . 
					" GROUP BY " . STATS_PLAYER_KILLS . ".PLAYERID " . 
					" ORDER BY AllKills";
	}
	// --- 

	// --- ANTI Medals
	$content['medals']['medal_anti_no1target']['sql'] = "SELECT " .
				STATS_PLAYER_KILLS . ".ENEMYID, " .
				" sum(" . STATS_PLAYER_KILLS . ".Kills) as AllKills" . 
				" FROM " . STATS_PLAYER_KILLS . 
				$wheresinglesql .
				GetBannedPlayerWhereQuery(STATS_PLAYER_KILLS, "ENEMYID", false) . 
				" GROUP BY " . STATS_PLAYER_KILLS . ".ENEMYID " . 
				" ORDER BY AllKills";

	$content['medals']['medal_anti_teamkiller']['sql'] = "SELECT " .
				STATS_PLAYERS . ".GUID, " .
				" sum(" . STATS_PLAYERS . ".Teamkills) as AllKills" . 
				" FROM " . STATS_PLAYERS . 
				$wheresinglesql .
				GetBannedPlayerWhereQuery(STATS_PLAYERS, "GUID", false) . 
				" GROUP BY " . STATS_PLAYERS . ".GUID " . 
				" ORDER BY AllKills";

	$content['medals']['medal_anti_suicide']['sql'] = "SELECT " .
				STATS_PLAYERS . ".GUID, " .
				" sum(" . STATS_PLAYERS . ".Suicides) as AllKills" . 
				" FROM " . STATS_PLAYERS . 
				$wheresinglesql .
				GetBannedPlayerWhereQuery(STATS_PLAYERS, "GUID", false) . 
				" GROUP BY " . STATS_PLAYERS . ".GUID " . 
				" ORDER BY AllKills";

	$content['medals']['medal_anti_nademagnet']['sql'] = "SELECT " .
				STATS_PLAYER_KILLS . ".ENEMYID, " .
				" sum(" . STATS_PLAYER_KILLS . ".Kills) as AllKills" . 
				" FROM " . STATS_PLAYER_KILLS . 
				" INNER JOIN (" . STATS_DAMAGETYPES . 
				") ON (" . 
				STATS_DAMAGETYPES . ".ID=" . STATS_PLAYER_KILLS . ".DAMAGETYPEID) " . 
				" WHERE " . STATS_DAMAGETYPES . ".DAMAGETYPE IN ('MOD_GRENADE_SPLASH' )" . 
				$whereaddsql . 
				GetBannedPlayerWhereQuery(STATS_PLAYER_KILLS, "ENEMYID", false) . 
				" GROUP BY " . STATS_PLAYER_KILLS . ".ENEMYID " . 
				" ORDER BY AllKills";

	$content['medals']['medal_anti_whiner']['sql'] = "SELECT " .
				STATS_CHAT . ".PLAYERID, " .
				" count(" . STATS_CHAT . ".ID) as AllKills" . 
				" FROM " . STATS_CHAT . 
				" WHERE (" . ReturnWhinerQuery() . ") " . 
				$whereaddsql . 
				GetBannedPlayerWhereQuery(STATS_CHAT, "PLAYERID", false) . 
				" GROUP BY " . STATS_CHAT . ".PLAYERID " . 
				" ORDER BY AllKills";
	// ---

	// --- Set Display Names!
	$content['medals']['medal_pro_killer']['DisplayName'] = "Killer";
	$content['medals']['medal_pro_killer']['GroupedPlayerID'] = "PLAYERID";
	$content['medals']['medal_pro_headshot']['DisplayName'] = "Headshot";
	$content['medals']['medal_pro_headshot']['GroupedPlayerID'] = "PLAYERID";
	$content['medals']['medal_pro_explosivekiller']['DisplayName'] = "Explosive Killer";
	$content['medals']['medal_pro_explosivekiller']['GroupedPlayerID'] = "PLAYERID";
	$content['medals']['medal_pro_slappyhappy']['DisplayName'] = "Slappy Happy";
	$content['medals']['medal_pro_slappyhappy']['GroupedPlayerID'] = "PLAYERID";
	$content['medals']['medal_pro_sniper']['DisplayName'] = "Sniper";
	$content['medals']['medal_pro_sniper']['GroupedPlayerID'] = "PLAYERID";
	$content['medals']['medal_pro_knifekills']['DisplayName'] = "Knife Kills";
	$content['medals']['medal_pro_knifekills']['GroupedPlayerID'] = "PLAYERID";
	$content['medals']['medal_pro_pistol']['DisplayName'] = "Pistol";
	$content['medals']['medal_pro_pistol']['GroupedPlayerID'] = "PLAYERID";
	$content['medals']['medal_pro_claymorec4']['DisplayName'] = "Claymore/C4";
	$content['medals']['medal_pro_claymorec4']['GroupedPlayerID'] = "PLAYERID";
	$content['medals']['medal_anti_no1target']['DisplayName'] = "No 1 Target";
	$content['medals']['medal_anti_no1target']['GroupedPlayerID'] = "ENEMYID";
	$content['medals']['medal_anti_teamkiller']['DisplayName'] = "Teamkiller";
	$content['medals']['medal_anti_teamkiller']['GroupedPlayerID'] = "GUID";
	$content['medals']['medal_anti_suicide']['DisplayName'] = "Suicide";
	$content['medals']['medal_anti_suicide']['GroupedPlayerID'] = "GUID";
	$content['medals']['medal_anti_nademagnet']['DisplayName'] = "Nade Magnet";
	$content['medals']['medal_anti_nademagnet']['GroupedPlayerID'] = "ENEMYID";
	$content['medals']['medal_anti_whiner']['DisplayName'] = "Whiner";
	$content['medals']['medal_anti_whiner']['GroupedPlayerID'] = "PLAYERID";

	// Can't use this core yet, it only works on PHP5: foreach ($content['medals'] as $key => &$medal)
	foreach ($content['medals'] as $key => $medal)
	{
		// Set medalid!
		$content['medals'][$key]['medalid'] = $key;

		// Set configuration variable if not available!
		if ( !isset( $content[$key]) )
		{
			// Default = enabled!
			$content[$key] = "yes";
			WriteConfigValue($key);
		}
	}
	// ---
}

function CreateAllMedals( $serverid )
{
	global $myserver, $content;

	// Now we create overall Medals!
	if ( $serverid != -1 ) 
		PrintHTMLDebugInfo( DEBUG_INFO, "Medal", "Starting Medal Calculation for Server " . $serverid . " ...");
	else
		PrintHTMLDebugInfo( DEBUG_INFO, "Medal", "Starting Total Medal Calculation ...");

	// Create Medal SQL Code!
	CreateMedalsSQLCode($serverid);

	// Clean up Medals
	ProcessDeleteStatement("DELETE FROM " . STATS_CONSOLIDATED . " WHERE NAME LIKE 'medal_%' AND SERVERID = " . $serverid);

	// ========================== PRO MEDALS =================================

	// --- Calc: medal_pro_killer
	if ( $content["medal_pro_killer"] == "yes" ) 
	{
		$sqlquery =	$content['medals']['medal_pro_killer']['sql'] . " DESC LIMIT 1";
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Medal", "medal_pro_killer: " . $sqlquery );

		$topplayer = ReturnMedalValue($sqlquery);
		if ( isset($topplayer['PLAYERID']) && $topplayer['AllKills'] > 0 )
			InsertOrUpdateMedalValue(	"medal_pro_killer", 
										$content['medals']['medal_pro_killer']['DisplayName'], 
										$serverid, 
										"medal_pro_killer", 
										$topplayer['AllKills'], 
										"Kills", 
										$topplayer['PLAYERID'],
										0 );
		else
			PrintHTMLDebugInfo( DEBUG_INFO, "Medal", "medal_pro_killer is empty!" );
	}
	// --- 

	// --- Calc: medal_pro_headshot
	if ( $content["medal_pro_headshot"] == "yes" ) 
	{
		$sqlquery =	$content['medals']['medal_pro_headshot']['sql'] . " DESC LIMIT 1";
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Medal", "medal_pro_headshot: " . $sqlquery );

		$topplayer = ReturnMedalValue($sqlquery);
		if ( isset($topplayer['PLAYERID']) && $topplayer['AllKills'] > 0 )
			InsertOrUpdateMedalValue(	"medal_pro_headshot", 
										$content['medals']['medal_pro_headshot']['DisplayName'], 
										$serverid, 
										"medal_pro_headshot", 
										$topplayer['AllKills'], 
										"Kills", 
										$topplayer['PLAYERID'],
										1 );
		else
			PrintHTMLDebugInfo( DEBUG_INFO, "Medal", "medal_pro_headshot is empty!" );
	}
	// --- 
	
	// --- Calc: medal_pro_explosivekiller
	if ( $content["medal_pro_explosivekiller"] == "yes" ) 
	{
		$sqlquery =	$content['medals']['medal_pro_explosivekiller']['sql'] . " DESC LIMIT 1";
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Medal", "medal_pro_explosivekiller: " . $sqlquery );

		$topplayer = ReturnMedalValue($sqlquery);
		if ( isset($topplayer['PLAYERID']) && $topplayer['AllKills'] > 0 )
			InsertOrUpdateMedalValue(	"medal_pro_explosivekiller", 
										$content['medals']['medal_pro_explosivekiller']['DisplayName'], 
										$serverid, 
										"medal_pro_explosivekiller", 
										$topplayer['AllKills'], 
										"Kills", 
										$topplayer['PLAYERID'],
										2 );
		else
			PrintHTMLDebugInfo( DEBUG_INFO, "Medal", "medal_pro_explosivekiller is empty!" );
	}
	// --- 

	if (	$content['gen_gameversion'] == COD || 
			$content['gen_gameversion'] == CODUO || 
			$content['gen_gameversion'] == COD2 )
	{
		// --- Calc: medal_pro_slappyhappy
		if ( $content["medal_pro_slappyhappy"] == "yes" ) 
		{
			$sqlquery =	$content['medals']['medal_pro_slappyhappy']['sql'] . " DESC LIMIT 1";
			PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Medal", "medal_pro_slappyhappy: " . $sqlquery );

			$topplayer = ReturnMedalValue($sqlquery);
			if ( isset($topplayer['PLAYERID']) && $topplayer['AllKills'] > 0 )
				InsertOrUpdateMedalValue(	"medal_pro_slappyhappy", 
											$content['medals']['medal_pro_slappyhappy']['DisplayName'], 
											$serverid, 
											"medal_pro_slappyhappy", 
											$topplayer['AllKills'], 
											"Kills", 
											$topplayer['PLAYERID'],
											3 );
			else
				PrintHTMLDebugInfo( DEBUG_INFO, "Medal", "medal_pro_slappyhappy is empty!" );
		}
		// --- 

		// --- Calc: medal_pro_sniper
		if ( $content["medal_pro_sniper"] == "yes" ) 
		{
			$sqlquery =	$content['medals']['medal_pro_sniper']['sql'] . " DESC LIMIT 1";
			PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Medal", "medal_pro_sniper: " . $sqlquery );

			$topplayer = ReturnMedalValue($sqlquery);
			if ( isset($topplayer['PLAYERID']) && $topplayer['AllKills'] > 0 )
				InsertOrUpdateMedalValue(	"medal_pro_sniper", 
											$content['medals']['medal_pro_sniper']['DisplayName'], 
											$serverid, 
											"medal_pro_sniper", 
											$topplayer['AllKills'], 
											"Kills", 
											$topplayer['PLAYERID'],
											4 );
			else
				PrintHTMLDebugInfo( DEBUG_INFO, "Medal", "medal_pro_sniper is empty!" );
		}
		// --- 

	}
	else if($content['gen_gameversion'] == COD4)
	{
		// --- Calc: medal_pro_knifekills 
		if ( $content["medal_pro_knifekills"] == "yes" ) 
		{
			$sqlquery =	$content['medals']['medal_pro_knifekills']['sql'] . " DESC LIMIT 1";
			PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Medal", "medal_pro_knifekills: " . $sqlquery ); 

			$topplayer = ReturnMedalValue($sqlquery); 
			if ( isset($topplayer['PLAYERID']) && $topplayer['AllKills'] > 0 ) 
				InsertOrUpdateMedalValue(  "medal_pro_knifekills", 
										   $content['medals']['medal_pro_knifekills']['DisplayName'], 
										   $serverid, 
										   "medal_pro_knifekills", 
										   $topplayer['AllKills'], 
										   "Kills", 
										   $topplayer['PLAYERID'], 
										   3 ); 
			else 
				PrintHTMLDebugInfo( DEBUG_INFO, "Medal", "medal_pro_knifekills is empty!" ); 
		}
		// ---

		// --- Calc: medal_pro_sniper 
		if ( $content["medal_pro_sniper"] == "yes" ) 
		{
			$sqlquery =	$content['medals']['medal_pro_sniper']['sql'] . " DESC LIMIT 1";
			PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Medal", "medal_pro_sniper: " . $sqlquery ); 

			$topplayer = ReturnMedalValue($sqlquery); 
			if ( isset($topplayer['PLAYERID']) ) 
			InsertOrUpdateMedalValue(   "medal_pro_sniper", 
							   $content['medals']['medal_pro_sniper']['DisplayName'], 
							   $serverid, 
							   "medal_pro_sniper", 
							   $topplayer['AllKills'], 
							   "Kills", 
							   $topplayer['PLAYERID'], 
							   4 ); 
			else 
				PrintHTMLDebugInfo( DEBUG_INFO, "Medal", "medal_pro_sniper is empty!" ); 
		}
		// ---
	}

	// --- Calc: medal_pro_pistol
	if ( $content["medal_pro_pistol"] == "yes" ) 
	{
		// Credits to "SaCkS" ;), I just added the static defs into functions_constats.php
		$sqlquery =	$content['medals']['medal_pro_pistol']['sql'] . " DESC LIMIT 1";
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Medal", "medal_pro_pistol: " . $sqlquery );

		$topplayer = ReturnMedalValue($sqlquery);
		if ( isset($topplayer['PLAYERID']) && $topplayer['AllKills'] > 0 )
			InsertOrUpdateMedalValue(	"medal_pro_pistol", 
										$content['medals']['medal_pro_pistol']['DisplayName'], 
										$serverid, 
										"medal_pro_pistol", 
										$topplayer['AllKills'], 
										"Kills", 
										$topplayer['PLAYERID'],
										5 );
		else
			PrintHTMLDebugInfo( DEBUG_INFO, "Medal", "medal_pro_pistol is empty!" );
	}
	// --- 

	// Special COD4 Medals!
	if($content['gen_gameversion'] == COD4)
	{
		// --- Calc: medal_pro_claymorec4
		if ( $content["medal_pro_claymorec4"] == "yes" ) 
		{
			// Credits for this medal go to: [-UFC-]James Ryan (matze1)
			$sqlquery =	$content['medals']['medal_pro_claymorec4']['sql'] . " DESC LIMIT 1";
			PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Medal", "medal_pro_claymorec4: " . $sqlquery );

			$topplayer = ReturnMedalValue($sqlquery);
			if ( isset($topplayer['PLAYERID']) )
				InsertOrUpdateMedalValue(	"medal_pro_claymorec4", 
											$content['medals']['medal_pro_claymorec4']['DisplayName'], 
											$serverid, 
											"medal_pro_claymorec4", 
											$topplayer['AllKills'], 
											"Kills", 
											$topplayer['PLAYERID'],
											4 );
			else
				PrintHTMLDebugInfo( DEBUG_INFO, "Medal", "medal_pro_claymorec4 is empty!" );
		}
		// --- 
	}
	// ==========================            =================================


	// ========================== ANTI MEDALS =================================
	// --- Calc: medal_anti_no1target
	if ( $content["medal_anti_no1target"] == "yes" ) 
	{
		$sqlquery =	$content['medals']['medal_anti_no1target']['sql'] . " DESC LIMIT 1";
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Medal", "medal_anti_no1target: " . $sqlquery );

		$topplayer = ReturnMedalValue($sqlquery);
		if ( isset($topplayer['ENEMYID']) && $topplayer['AllKills'] > 0 )
			InsertOrUpdateMedalValue(	"medal_anti_no1target", 
										$content['medals']['medal_anti_no1target']['DisplayName'], 
										$serverid, 
										"medal_anti_no1target", 
										$topplayer['AllKills'], 
										"Deaths", 
										$topplayer['ENEMYID'],
										0 );
		else
			PrintHTMLDebugInfo( DEBUG_INFO, "Medal", "medal_anti_no1target is empty!" );
	}
	// --- 

	// --- Calc: medal_anti_teamkiller
	if ( $content["medal_anti_teamkiller"] == "yes" ) 
	{
		$sqlquery =	$content['medals']['medal_anti_teamkiller']['sql'] . " DESC LIMIT 1";
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Medal", "medal_anti_teamkiller: " . $sqlquery );

		$topplayer = ReturnMedalValue($sqlquery);
		if ( isset($topplayer['GUID']) && $topplayer['AllKills'] > 0 )
			InsertOrUpdateMedalValue(	"medal_anti_teamkiller", 
										$content['medals']['medal_anti_teamkiller']['DisplayName'], 
										$serverid, 
										"medal_anti_teamkiller", 
										$topplayer['AllKills'], 
										"Kills", 
										$topplayer['GUID'],
										1 );
		else
			PrintHTMLDebugInfo( DEBUG_INFO, "Medal", "medal_anti_teamkiller is empty!" );
	}
	// --- 

	// --- Calc: medal_anti_suicide
	if ( $content["medal_anti_suicide"] == "yes" ) 
	{
		$sqlquery =	$content['medals']['medal_anti_suicide']['sql'] . " DESC LIMIT 1";
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Medal", "medal_anti_suicide: " . $sqlquery );

		$topplayer = ReturnMedalValue($sqlquery);
		if ( isset($topplayer['GUID']) && $topplayer['AllKills'] > 0 )
			InsertOrUpdateMedalValue(	"medal_anti_suicide", 
										$content['medals']['medal_anti_suicide']['DisplayName'], 
										$serverid, 
										"medal_anti_suicide", 
										$topplayer['AllKills'], 
										"Deaths", 
										$topplayer['GUID'],
										2 );
		else
			PrintHTMLDebugInfo( DEBUG_INFO, "Medal", "medal_anti_suicide is empty!" );
	}
	// --- 

	// --- Calc: medal_anti_nademagnet
	if ( $content["medal_anti_nademagnet"] == "yes" ) 
	{
		$sqlquery =	$content['medals']['medal_anti_nademagnet']['sql'] . " DESC LIMIT 1";
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Medal", "medal_anti_nademagnet: " . $sqlquery );

		$topplayer = ReturnMedalValue($sqlquery);
		if ( isset($topplayer['ENEMYID']) && $topplayer['AllKills'] > 0 )
			InsertOrUpdateMedalValue(	"medal_anti_nademagnet", 
										$content['medals']['medal_anti_nademagnet']['DisplayName'], 
										$serverid, 
										"medal_anti_nademagnet", 
										$topplayer['AllKills'], 
										"Deaths", 
										$topplayer['ENEMYID'],
										3 );
		else
			PrintHTMLDebugInfo( DEBUG_INFO, "Medal", "medal_anti_nademagnet is empty!" );
	}
	// --- 

	// --- Calc: medal_anti_whiner
	if ( $content["medal_anti_whiner"] == "yes" ) 
	{
		$sqlquery =	$content['medals']['medal_anti_whiner']['sql'] . " DESC LIMIT 1";
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Medal", "medal_anti_whiner: " . $sqlquery );

		$topplayer = ReturnMedalValue($sqlquery);
		if ( isset($topplayer['PLAYERID']) && $topplayer['AllKills'] > 0 )
			InsertOrUpdateMedalValue(	"medal_anti_whiner", 
										$content['medals']['medal_anti_whiner']['DisplayName'], 
										$serverid, 
										"medal_anti_whiner", 
										$topplayer['AllKills'], 
										"Whining chats", 
										$topplayer['PLAYERID'],
										4 );
		else
			PrintHTMLDebugInfo( DEBUG_INFO, "Medal", "medal_anti_whiner is empty!" );
	}
	// --- 

	// ==========================            =================================

	// Finished
	PrintHTMLDebugInfo( DEBUG_INFO, "Medal", "Finished Medal Calculation...");
}

function ReturnMedalValue( $mySqlCommand )
{
	$result = DB_Query($mySqlCommand);
	$tmpvar = DB_GetSingleRow($result, true);
	if ( isset($tmpvar) )
		return $tmpvar;
	else
		return;
}

function InsertOrUpdateMedalValue( $myname, $mydisplayname, $myserverid, $DescriptionStringID, $valueInt, $valueStr, $PlayerID, $SortID )
{
	PrintHTMLDebugInfo( DEBUG_DEBUG, "InsertOrUpdateMedalValue", "Adding/Updating New Medal '" . $myname . "', PlayerID '" . $PlayerID . "'");
	$wherequery =	"WHERE SERVERID = " . $myserverid . " AND 
					NAME = '" . $myname . "'"; 

	$result = DB_Query("SELECT ID FROM " . STATS_CONSOLIDATED . " " . $wherequery );
	$rows = DB_GetAllRows($result, true);
	if ( isset($rows) )
	{
		// Update Calc
		ProcessUpdateStatement("UPDATE " . STATS_CONSOLIDATED . " SET 
									PLAYER_ID = " . $PlayerID . ", 
									VALUE_INT = " . $valueInt . ", 
									VALUE_TXT = '" . $valueStr . "' " . $wherequery, true );
	}
	else
	{
		// Insert New
		ProcessInsertStatement("INSERT INTO " . STATS_CONSOLIDATED . " (NAME, SERVERID, DisplayName, DescriptionID, VALUE_INT, VALUE_TXT, PLAYER_ID, SortID) 
		VALUES (
			 '" . $myname . "', 
			 " . $myserverid . ", 
			 '" . $mydisplayname . "', 
			 '" . $DescriptionStringID . "', 
			 " . $valueInt . ", 
			 '" . $valueStr . "', 
			 " . $PlayerID . ", 
			 " . $SortID . ")");
	}
}


function ReturnWhinerQuery()
{
	// Helper function which returns whiner words as a list for a query! 
	$whining = Array(
						'noob', 'cheat', 'camper', 'hurensohn', 'fucking', 'wallhacker', 'deine mudda', 
						'nap', 'nerd', 'gay', 'hure', 'bastard', 'spasst', 'fick', 'sucker', 'arsch', 'pisser',
						'luckor', 'sau', 'wixer', 'bettnässer', 'n00b', 'hoden', 'pissnelke', 'huras', 'deine mutter',
						'mowl', 'bitch', 'slut', 'motherfuck', 'assi', 'drecks', 'nigg', 'fresse', 'spack', 
						'shout up', 'stfu', 'hax0r', 'n00b', 'hate', 'sheiss', 'Affe', 'negg', 'lutscher',
						'idiot', 'hdf' //, '', 'n00b', 'hate', 'sheiss', 'Affe', 'negg', 'lutscher',
					);
	foreach ($whining as $myword )
	{
		if ( !isset($whiningstr) )
			$whiningstr = "TextSaid LIKE '%" . $myword . "%' ";
		else
			$whiningstr .= " OR TextSaid LIKE '%" . $myword . "%' ";
	}

	// return string
	return $whiningstr;
}
?>