<?php
/*
	*********************************************************************
	* Copyright by Andre Lorbach | 2006!								*
	* -> www.ultrastats.org <-											*
	*																	*
	* Use this script at your own risk!									*
	* -----------------------------------------------------------------	*
	* Helperfunctions for the web frontend								*
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

function CreateTopPlayersArray($maxnumber, $arrayname, $topname)
{
	global $content;

	for($i = 0; $i < $maxnumber; $i++)
	{
		$content[$arrayname][$i]['Number'] = $i;

		if ( $content[$topname] == $i )
			$content[$arrayname][$i]['selected'] = "selected";
		else
			$content[$arrayname][$i]['selected'] = "";
	}
}

function InitFrontEndDefaults()
{
	// Read the whole ServerList
	CreateServerListArray();
	
	// Create Weapon Array
	CreateWeaponArray();

	// Create Damagetype Array
	CreateDamagetypeArray();

	// Create Gametypes
	CreateGameTypeArray();

	// Read Current Server from URL
	GetAndSetCurrentServer();

	// Read Current Time from URL
//TODO	GetAndSetCurrentTime();

	// To create the current URL
	CreateCurrentUrl();

	// --- BEGIN Main Info Area
	GetAndSetGlobalInfo();
	// --- END Main Info Area
	
	// Check if install file still exists
	InstallFileReminder();
}

function InstallFileReminder()
{
	global $content;

	if ( is_file($content['BASEPATH'] . "install.php") ) 
	{
		// No Servers - display warning!
		$content['error_installfilereminder'] = "true";
	}
}

function CreateGameTypeArray()
{
	global $content;

	// Get Weapons from DB!
	$sqlquery = "SELECT " .
						STATS_GAMETYPES . ".ID as GAMETYPEID, " . 
						STATS_GAMETYPES . ".NAME, " . 
						STATS_GAMETYPES . ".DisplayName as GameTypeDisplayName " . 
						" FROM " . STATS_GAMETYPES . 
						" ORDER BY " . STATS_GAMETYPES . ".NAME ";
	$result = DB_Query($sqlquery, true, true); // Critical!
	$content['gametypes'] = DB_GetAllRows($result, true);

	//--- Set Displayname!
	for ( $i = 0; $i < count($content['gametypes']); $i++ )
	{
		if ( !isset($content['gametypes'][$i]['GameTypeDisplayName']) || strlen($content['gametypes'][$i]['GameTypeDisplayName']) <= 0 )
			$content['gametypes'][$i]['GameTypeDisplayName'] = $content['gametypes'][$i]['NAME'];
	}
	//--- Set Displayname
}

function AddToWeaponArray($weapontypeid, $weapontypename)
{
	global $content;
	if ( isset($content['weapontypes']) )
		$iNum = count($content['weapontypes']);
	else
		$iNum = 0;

	$content['weapontypes'][$iNum]['ID'] = $weapontypeid;
	$content['weapontypes'][$iNum]['Name'] = $weapontypename;
}

function CreateDamagetypeArray()
{
	global $content;

	// Get Damagetypes from DB!
	$sqlquery = "SELECT " .
						STATS_DAMAGETYPES . ".DAMAGETYPE  , " . 
						STATS_DAMAGETYPES . ".DisplayName " . 
						" FROM " . STATS_DAMAGETYPES . 
						" ORDER BY " . STATS_DAMAGETYPES . ".DisplayName, " . STATS_DAMAGETYPES . ".DAMAGETYPE";
	$result = DB_Query($sqlquery, true, true); // Critical!
	$content['damagetypes_menu'] = DB_GetAllRows($result, true);
}

function CreateWeaponArray()
{
	global $content;

	// Predefine WeaponTypes
	if (	$content['gen_gameversion'] == COD || 
			$content['gen_gameversion'] == COD2 )
	{
		AddToWeaponArray(WEAPONTYPE_MACHINEGUN, $content['LN_WEAPONTYPE_MACHINEGUN']);
		AddToWeaponArray(WEAPONTYPE_SNIPER, $content['LN_WEAPONTYPE_SNIPER']);
		AddToWeaponArray(WEAPONTYPE_PISTOL, $content['LN_WEAPONTYPE_PISTOL']);
		AddToWeaponArray(WEAPONTYPE_GRENADE, $content['LN_WEAPONTYPE_GRENADE']);
		AddToWeaponArray(WEAPONTYPE_STANDWEAPON, $content['LN_WEAPONTYPE_STANDWEAPON']);
	}
	else if ( $content['gen_gameversion'] == CODUO ) 
	{
		AddToWeaponArray(WEAPONTYPE_MACHINEGUN, $content['LN_WEAPONTYPE_MACHINEGUN']);
		AddToWeaponArray(WEAPONTYPE_SNIPER, $content['LN_WEAPONTYPE_SNIPER']);
		AddToWeaponArray(WEAPONTYPE_PISTOL, $content['LN_WEAPONTYPE_PISTOL']);
		AddToWeaponArray(WEAPONTYPE_GRENADE, $content['LN_WEAPONTYPE_GRENADE']);
		AddToWeaponArray(WEAPONTYPE_STANDWEAPON, $content['LN_WEAPONTYPE_STANDWEAPON']);
		AddToWeaponArray(WEAPONTYPE_TANK, $content['LN_WEAPONTYPE_TANK']);
		
	}
	else if ( $content['gen_gameversion'] == COD4 ) 
	{
		AddToWeaponArray(WEAPONTYPE_ASSAULT, $content['LN_WEAPONTYPE_ASSAULT']);
		AddToWeaponArray(WEAPONTYPE_MACHINEGUN, $content['LN_WEAPONTYPE_SUBMACHINEGUN']);
		AddToWeaponArray(WEAPONTYPE_LIGHTMACHINEGUN, $content['LN_WEAPONTYPE_LIGHTMACHINEGUN']);
		AddToWeaponArray(WEAPONTYPE_SHOTGUN, $content['LN_WEAPONTYPE_SHOTGUN']);
		AddToWeaponArray(WEAPONTYPE_SNIPER, $content['LN_WEAPONTYPE_SNIPER']);
		AddToWeaponArray(WEAPONTYPE_PISTOL, $content['LN_WEAPONTYPE_PISTOL']);
		AddToWeaponArray(WEAPONTYPE_GRENADE, $content['LN_WEAPONTYPE_GRENADE']);
		AddToWeaponArray(WEAPONTYPE_STANDWEAPON, $content['LN_WEAPONTYPE_STANDWEAPON']);
		AddToWeaponArray(WEAPONTYPE_SPECIAL, $content['LN_WEAPONTYPE_SPECIAL']);
		AddToWeaponArray(WEAPONTYPE_MISC, $content['LN_WEAPONTYPE_MISC']);
	}

	// Main Menu Copy!
	$content['weapontypes_menu'] = $content['weapontypes'];

	if ( isset($content['serverid']) )
	{
		// Only Weapons Played on the Server!
		$sqlquery = "SELECT " .
							STATS_WEAPONS . ".ID as WEAPONID, " . 
							STATS_WEAPONS . ".INGAMENAME  , " . 
							STATS_WEAPONS . ".DisplayName as WeaponDisplayName, " . 
							STATS_WEAPONS . ".WeaponType " . 
							" FROM " . STATS_WEAPONS . 
							" INNER JOIN (" . STATS_WEAPONS_PERSERVER . 
							") ON (" . 
							STATS_WEAPONS . ".ID=" . STATS_WEAPONS_PERSERVER . ".WEAPONID) " . 
							" WHERE " . STATS_WEAPONS_PERSERVER . ".SERVERID = " . $content['serverid'] . 
							" GROUP BY " . STATS_WEAPONS . ".ID " . 
							" ORDER BY " . STATS_WEAPONS . ".WeaponType, " . STATS_WEAPONS . ".INGAMENAME";
	}
	else
	{
		// Get Weapons from DB!
		$sqlquery = "SELECT " .
							STATS_WEAPONS . ".ID as WEAPONID, " . 
							STATS_WEAPONS . ".INGAMENAME  , " . 
							STATS_WEAPONS . ".DisplayName as WeaponDisplayName, " . 
							STATS_WEAPONS . ".WeaponType " . 
							" FROM " . STATS_WEAPONS . 
							" ORDER BY " . STATS_WEAPONS . ".WeaponType, " . STATS_WEAPONS . ".INGAMENAME";
	}
	$result = DB_Query($sqlquery, true, true); // Critical!
	$content['weapons'] = DB_GetAllRows($result, true);
	
	if ( isset($content['weapons']) )
	{
		//--- Set Displayname!
		for ( $i = 0; $i < count($content['weapons']); $i++ )
		{
			if ( !isset($content['weapons'][$i]['WeaponDisplayName']) || strlen($content['weapons'][$i]['WeaponDisplayName']) <= 0 )
				$content['weapons'][$i]['WeaponDisplayName'] = $content['weapons'][$i]['INGAMENAME'];
		}
		//--- Set Displayname

		// now we also set the weapons as Childs per weapontype!
		for ( $i = 0; $i < count($content['weapontypes']); $i++ )
		{
			foreach( $content['weapons'] as $myweapon )
			{
				if ( $content['weapontypes'][$i]['ID'] == $myweapon['WeaponType'] )
					$content['weapontypes'][$i]['myweapons'][] = $myweapon;
			}

			//--- Set Menu Enabled or Disabled
			if ( isset($content['weapontypes'][$i]['myweapons']) && count($content['weapontypes'][$i]['myweapons']) > 0 )
			{
				$content['weapontypes'][$i]['menuenabled'] = "true";
				$content['weapontypes_menu'][$i]['menuenabled'] = "true";
			}
			else
			{
				$content['weapontypes'][$i]['menuenabled'] = "false";
				$content['weapontypes_menu'][$i]['menuenabled'] = "false";
			}
			//---
		}
	}
}

function CreateCurrentUrl()
{
	global $content;
	$content['CURRENTURL'] = $_SERVER['PHP_SELF']; // . "?" . $_SERVER['QUERY_STRING']

	// Now the query string:
	if ( isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0 )
	{
		// Append ?
		$content['CURRENTURL'] .= "?";

		$queries = explode ("&", $_SERVER['QUERY_STRING']);
		$counter = 0;
		for ( $i = 0; $i < count($queries); $i++ )	// Fixed from $i = 0 to $i = 1
		{
			if ( strpos($queries[$i], "serverid") === false ) 
			{
				$tmpvars = explode ("=", $queries[$i]);
				// 4Server Selector
				$content['HIDDENVARS'][$counter]['varname'] = $tmpvars[0];
				$content['HIDDENVARS'][$counter]['varvalue'] = $tmpvars[1];

				$counter++;
			}
		}
	}
}

function CreateServerListArray()
{
	global $content;

	// Get ServerDetails
	$sqlquery = "SELECT ID, Name " . 
				"FROM " . STATS_SERVERS . " " . 
				"ORDER BY ID";
	$result = DB_Query($sqlquery, true, true); // Critical!
	$content['serverlist'] = DB_GetAllRows($result, true);
	
	if ( isset($content['serverlist']) )
	{
		$allservers['ID'] = "";
		$allservers['Name'] = "-- All Servers --";
		$splitter['ID'] = "";
		$splitter['Name'] = "-";
		
		// Resort the Array
		array_unshift( $content['serverlist'], $splitter );
		array_unshift( $content['serverlist'], $allservers );
	}
	else
	{
		// No Servers - display warning!
		$content['error_noserver'] = "true";
	}
}

function GetCustomServerWhereQuery( $customtable , $withwhere = true, $alsoreturnifempty = false )
{
	global $serverwherequery, $content;
	
	// --- Special Check for special cases
	if ( $alsoreturnifempty && !(isset($content['serverid'])) ) 
	{
		if ( $withwhere )
			return " WHERE ". $customtable. ".SERVERID = -1 ";
		else
			return " AND ". $customtable. ".SERVERID = -1 ";
	}
	// --- 

	if ( isset($content['serverid']) && isset($serverwherequery) )
	{
		if ( $withwhere )
			return " WHERE ". $customtable. ".SERVERID = " . $content['serverid'];
		else
			return " AND ". $customtable. ".SERVERID = " . $content['serverid'];
	}
	else
		return "";
}

function GetAndSetCurrentServer()
{
	global $content, $serverwherequery, $serverwherequery_and;

	if ( isset($content['serverid']) )
	{
		// Get ServerDetails
		$result = DB_Query("SELECT * FROM " . STATS_SERVERS . " WHERE ID = " . $content['serverid']);
		$content['myserver'] = DB_GetSingleRow($result, true);
		if ( isset( $content['myserver']['ID'] ) )
		{
//not needed			$content['myserver'] = $serverdetails[0];
			$content['additional_url'] = "&serverid=" . $content['serverid'];
			$serverwherequery = " WHERE SERVERID = " . $content['serverid'];
			$serverwherequery_and = " AND SERVERID = " . $content['serverid'];
			
			// Needed for preselection
			for($i = 0; $i < count($content['serverlist']); $i++)
			{
				if ( $content['serverlist'][$i]['ID'] == $content['serverid'] )
					$content['serverlist'][$i]['selected'] = "selected";
			}
		}
		else
		{
			// Empty!
			if ( !isset($content['additional_url']) )
				$content['additional_url'] = "";
			$serverwherequery = "";
			$serverwherequery_and = "";
		}
	}
	else
	{
		// Empty!
		if ( !isset($content['additional_url']) )
			$content['additional_url'] = "";
		$serverwherequery = "";
		$serverwherequery_and = "";
	}
}

function FillPlayerWithAlias(&$myplayer, $idfield)
{
	global $content;
	$sqlquery = "SELECT " .
				STATS_ALIASES . ".PLAYERID, " . 
				"sum(" . STATS_ALIASES . ".Count) as AliasCount, " .	
				STATS_ALIASES . ".Alias, " .	
				STATS_ALIASES . ".AliasAsHtml " .
				" FROM " . STATS_ALIASES . 
				" WHERE " . STATS_ALIASES . ".PLAYERID=" . $myplayer[$idfield] .
				GetCustomServerWhereQuery(STATS_ALIASES, false) . 
				" GROUP BY " . STATS_ALIASES . ".Alias " . 
				" ORDER BY AliasCount DESC LIMIT 1";
	$result = DB_Query($sqlquery);
	$aliasvars = DB_GetSingleRow($result, true);
	
	// Copy vars
	$myplayer['Alias'] = $aliasvars['Alias'];
	$myplayer['AliasAsHtml'] = $aliasvars['AliasAsHtml'];
}

function FindAndFillTopAliases(&$myplayers, $idfield, $AliasField, $AliasHtmlField)
{
	global $content;

	// Get Guids first ;)
	for($i = 0; $i < count($myplayers); $i++)
	{
		if ( isset($playerguids) )
			$playerguids .= ", ";
		else
			$playerguids = "";	// INIT!
		$playerguids .= $myplayers[$i][$idfield];
	}

	// No GUIDS, then we do not need to run thissql query!
	if ( !isset($playerguids) ) 
		return;
	
	// Set Server Where
	if ( isset($content['serverid']) ) 
		$mywhereaddon = " AND " . STATS_PLAYERS_TOPALIASES . ".SERVERID = " . $content['serverid'];
	else
		$mywhereaddon = " AND " . STATS_PLAYERS_TOPALIASES . ".SERVERID = -1";

	$sqlquery = "SELECT " .
				STATS_ALIASES . ".PLAYERID, " . 
				STATS_ALIASES . ".Alias, " . 
				STATS_ALIASES . ".AliasAsHtml " .
				" FROM " . STATS_ALIASES . 
				" INNER JOIN (" . STATS_PLAYERS_TOPALIASES . 
				") ON (" . 
//				STATS_ALIASES . ".PLAYERID=" . STATS_PLAYERS_TOPALIASES . ".GUID) " . 
				STATS_ALIASES . ".ID=" . STATS_PLAYERS_TOPALIASES . ".ALIASID) " . 
				" WHERE " . STATS_PLAYERS_TOPALIASES . ".GUID IN (" . $playerguids . ")"  . 
				$mywhereaddon . 
				" GROUP BY " . STATS_ALIASES . ".PLAYERID ";
	$result = DB_Query($sqlquery);
	$aliasvars = DB_GetAllRows($result, true);
	if ( isset($aliasvars) )
	{
		for ($i=0 ; $i<count($aliasvars); $i++)
		{
			for ($n=0 ; $n<count($myplayers); $n++)
			{
				if ( $aliasvars[$i]['PLAYERID'] == $myplayers[$n][$idfield] )
				{
					// Copy vars
					$myplayers[$n][$AliasField] = $aliasvars[$i]['Alias'];
					$myplayers[$n][$AliasHtmlField] = $aliasvars[$i]['AliasAsHtml'];
				}
			}
		}
	}

	/* OLD WAY
	for($i = 0; $i < count($myplayers); $i++)
	{
		$sqlquery = "SELECT " .
					"sum( " .STATS_ALIASES . ".Count) as Count, " . 
					STATS_ALIASES . ".Alias, " . 
					STATS_ALIASES . ".AliasAsHtml" .
					" FROM " . STATS_ALIASES . 
					" WHERE PLAYERID = " . $myplayers[$i][$idfield] . " " . 
					GetCustomServerWhereQuery(STATS_ALIASES, false) . 
					" GROUP BY " . STATS_ALIASES . ".Alias " . 
					" ORDER BY Count DESC LIMIT 1";

		$result = DB_Query( $sqlquery );
		$mytmparray = DB_GetSingleRow($result, true);
		if ( isset($mytmparray) )
		{
			// Copy vars
			$myplayers[$i][$AliasField] = $mytmparray['Alias'];
			$myplayers[$i][$AliasHtmlField] = $mytmparray['AliasAsHtml'];
		}
	}*/

}

function FillPlayerWithTime(&$myplayer, $idfield)
{
	global $content;
	$sqlquery = "SELECT " .
						"sum(" . STATS_TIME . ".TIMEPLAYED) as TotalSeconds " . 
						" FROM " . STATS_TIME . 
						" WHERE " . STATS_TIME . ".PLAYERID=" . $myplayer[$idfield] .
						GetCustomServerWhereQuery( STATS_TIME, false) . 
						" GROUP BY " . STATS_TIME . ".PLAYERID ";
	$result = DB_Query($sqlquery);
	$timevars = DB_GetSingleRow($result, true);
	
	// Copy vars
	$myplayer['TotalSeconds'] = $timevars['TotalSeconds'];
	if ( $myplayer['TotalSeconds'] > 86400 ) // If more then one day
		$content['TimePlayedString'] = GetTimeStringDays($timevars['TotalSeconds']);
	else
		$content['TimePlayedString'] = GetTimeString($timevars['TotalSeconds']);
}


function FindAndFillWithTime(&$myplayers, $idfield, $TimeSecondsField, $TimeStringField)
{
	global $content;

	// Get Guids first ;)
	for($i = 0; $i < count($myplayers); $i++)
	{
		if ( isset($playerguids) )
			$playerguids .= ", ";
		else
			$playerguids = "";	// INIT!

		$playerguids .= $myplayers[$i][$idfield];
	}

	$sqlquery = "SELECT " .
						STATS_TIME . ".PLAYERID, " . 
						"sum(" . STATS_TIME . ".TIMEPLAYED) as TotalSeconds " . 
						" FROM " . STATS_TIME . 
						" WHERE " . STATS_TIME . ".PLAYERID IN (" . $playerguids . ")" . 
						GetCustomServerWhereQuery( STATS_TIME, false) . 
						" GROUP BY " . STATS_TIME . ".PLAYERID ";
//						" ORDER BY Count DESC";
	$result = DB_Query($sqlquery);
	$timevars = DB_GetAllRows($result, true);

	if ( isset($timevars) )
	{
		for ($i=0 ; $i<count($timevars); $i++)
		{
			for ($n=0 ; $n<count($myplayers); $n++)
			{
				if ( $timevars[$i]['PLAYERID'] == $myplayers[$n][$idfield] )
				{
					// Copy vars
					$myplayers[$n][$TimeSecondsField] = $timevars[$i]['TotalSeconds'];

//!!!! echo "mowl" . $timevars[$i]['PLAYERID'] . " " . $timevars[$i]['TotalSeconds'] . " <br>";

					if ( $myplayers[$n][$TimeSecondsField] > 86400 ) // If more then one day
//						$myplayers[$n][$TimeStringField] = "mowl";
						$myplayers[$n][$TimeStringField] = GetTimeStringDays($timevars[$i]['TotalSeconds']);
					else
						$myplayers[$n][$TimeStringField] = GetTimeString($timevars[$i]['TotalSeconds']);
				}
			}
		}
	}
}

function GetAndSetMaxKillRation()
{
	global $content;

// !!!! TODO! Make a new PLAYER Table where we store a total skill value per server


	// --- Lets get the MAX KillRatio first
	$sqlquery = "SELECT " .
						"sum(" . STATS_PLAYERS . ".Kills) as Kills, " .
						"sum(" . STATS_PLAYERS . ".Deaths) as Deaths, " .
						"sum(" . STATS_PLAYERS . ".Kills) / sum(" . STATS_PLAYERS . ".Deaths) as MaxKillRatio " .	// TRUE l33tAGE!
//						"round(AVG( " . STATS_PLAYERS . ".KillRatio),2) as MaxKillRatio " .
						" FROM " . STATS_PLAYERS . 
						" WHERE Kills > " . $content['web_minkills'] .
						GetCustomServerWhereQuery(STATS_PLAYERS, false) . 
						GetBannedPlayerWhereQuery(STATS_PLAYERS, "GUID", false) . 
						" GROUP BY " . STATS_PLAYERS . ".GUID " . 
						" ORDER BY MaxKillRatio DESC LIMIT 1";
	$result = DB_Query($sqlquery);
	$tmpvars = DB_GetSingleRow($result, true);
	if ( isset($tmpvars['MaxKillRatio']) )
	{
		// Copy var
		$content['MaxKillRatio'] = $tmpvars['MaxKillRatio'];
		$content['MaxPixelWidth'] = 100;		// Set MaxWidth in Pixel
	}
	// --- 
}

function GetAndSetGlobalInfo()
{
	global $content;

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
						" WHERE " . STATS_CONSOLIDATED . ".NAME LIKE 'global_%' " .
						" ORDER BY " . STATS_CONSOLIDATED . ".SortID";
	$result = DB_Query($sqlquery);

	$content['globals'] = DB_GetAllRows($result, true, true); // Critical!
	if ( isset($content['globals']) )
	{
		for($i = 0; $i < count($content['globals']); $i++)
		{
			if ( $content['globals'][$i]['NAME'] == "global_lastupdate" )
				$content['global_lastupdate_TimeFormat'] = date('Y-m-d H:i:s', $content['globals'][$i]['VALUE_INT']);
		}
	}
	// --- 

	// --- Get Total Server Values

	$serverwheresql = GetCustomServerWhereQuery( STATS_CONSOLIDATED, false);
	if ( strlen($serverwheresql) <= 0 ) 
		$serverwheresql = " AND " . STATS_CONSOLIDATED . ".SERVERID = -1 ";

	$sqlquery = "SELECT " .
						STATS_CONSOLIDATED . ".NAME, " . 
						STATS_CONSOLIDATED . ".DisplayName, " . 
						STATS_CONSOLIDATED . ".VALUE_INT " . 
//						STATS_CONSOLIDATED . ".VALUE_TXT " . 
						" FROM " . STATS_CONSOLIDATED .
						" WHERE " . STATS_CONSOLIDATED . ".NAME LIKE 'server_total%' " .
						$serverwheresql . 
						" ORDER BY " . STATS_CONSOLIDATED . ".SortID";
	$result = DB_Query($sqlquery);

	$content['GLOBALTOTALS'] = DB_GetAllRows($result, true);
	if ( isset($content['GLOBALTOTALS']) )
	{
		for($i = 0; $i < count($content['GLOBALTOTALS']); $i++)
		{
			if ( $content['GLOBALTOTALS'][$i]['NAME'] == "server_total_ratio" )
			{
				// Set Ratio new!
				$content['GLOBALTOTALS'][$i]['VALUE_INT'] = $content['GLOBALTOTALS'][$i]['VALUE_INT'] / 100;

				$content['MaxKillRatio'] = $content['GLOBALTOTALS'][$i]['VALUE_INT'];
				$content['MaxPixelWidth'] = 100;		// Set MaxWidth in Pixel
			}
			else if ( $content['GLOBALTOTALS'][$i]['NAME'] == "server_total_time" )
			{
				$content['GLOBALTOTALS'][$i]['VALUE_TXT'] = GetTimeStringDays( $content['GLOBALTOTALS'][$i]['VALUE_INT'] );
				$content['GLOBALTOTALS'][$i]['VALUE_INT'] = "";	// Reset to filter out!
			}
		}
	}
	// --- 
}

/* Helper function which will return Text from the DescriptionID in the correct language - with Fallback! */
function GetTextFromDescriptionID( $szDescriptionID, $szDefault )
{
	global $content, $LANG, $LANG_EN;

	// --- Try to get the text in custom language! 
	$sqlquery = "SELECT " .
						STATS_LANGUAGE_STRINGS . ".STRINGID, " .
						STATS_LANGUAGE_STRINGS . ".TEXT as Description " .
						" FROM " . STATS_LANGUAGE_STRINGS . 
						" WHERE " . STATS_LANGUAGE_STRINGS . ".LANG = '" . strtoupper($LANG) . "' AND " . STATS_LANGUAGE_STRINGS . ".STRINGID = '" . $szDescriptionID . "' " . 
						" LIMIT 1 ";
	$result = DB_Query($sqlquery);
	$textvars = DB_GetSingleRow($result, true);
	if ( isset($textvars['STRINGID']) )
		return $textvars['Description'];
	else
	{
		// FallBack, try to optain ENGLISH String!
		$sqlquery = "SELECT " .
							STATS_LANGUAGE_STRINGS . ".STRINGID, " .
							STATS_LANGUAGE_STRINGS . ".TEXT as Description " .
							" FROM " . STATS_LANGUAGE_STRINGS . 
							" WHERE " . STATS_LANGUAGE_STRINGS . ".LANG = '" . strtoupper($LANG_EN) . "' AND " . STATS_LANGUAGE_STRINGS . ".STRINGID = '" . $szDescriptionID . "' " . 
							" LIMIT 1 ";
		$result = DB_Query($sqlquery);
		$textvars = DB_GetSingleRow($result, true);
		if ( isset($textvars['STRINGID']) )
			return $textvars['Description'];
		else
			return $szDefault;
	}
}

function ReturnWeaponBaseName($weaponnameid)
{
	$arraySearch = array ("_grip",	"_acog","_gl",	"_silencer","_reflex",	"_crouch",	"_stand",	"_20mm",	"_ffar",	"_bipod");
	$arrayReplace = array ("",		"",		"",		"",			"",			"",			"",			"",			"",			"");
	
	// return result
	$stReturn = str_replace($arraySearch, $arrayReplace, $weaponnameid);
	return $stReturn;
}

?>