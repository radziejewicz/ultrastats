<?php
/*
	*********************************************************************
	* Copyright by Andre Lorbach | 2006, 2007, 2008						*
	* -> www.ultrastats.org <-											*
	*																	*
	* Use this script at your own risk!									*
	* -----------------------------------------------------------------	*
	* Player Details File												*
	*																	*
	* -> Shows Details for each player !								*
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

// --- BEGIN Custom Code

// Set default
$content['iserror'] = false;

// --- Check for the GUID first!
if ( isset($_GET['id']) )
{
	// get and check
	$content['playerguid'] = DB_RemoveBadChars($_GET['id']);
	if (
			!is_numeric($content['playerguid']) 
				|| 
			( $content['playerguid'] > 4294967296 && $content['playerguid'] <= 0 )
		)
	{
		// Invalid Guid!
		$content['iserror'] = "true";
	}
	else
	{	
		// --- Get Top Aliases
		// !Important Change! We read the aliases first so I have the most used ALIAS for the next SQL Statement!
		$sqlquery = "SELECT " .
					"sum( " .STATS_ALIASES . ".Count) as Count, " . 
					STATS_ALIASES . ".Alias as Aliases_Alias, " . 
					STATS_ALIASES . ".AliasAsHtml as Aliases_AliasAsHtml" .
					" FROM " . STATS_ALIASES . 
					" WHERE PLAYERID = " . $content['playerguid'] . " " . 
					GetCustomServerWhereQuery(STATS_ALIASES, false) . 
					" GROUP BY " . STATS_ALIASES . ".Alias " . 
					" ORDER BY Count DESC";
		$result = DB_Query( $sqlquery );
		$content['aliases'] = DB_GetAllRows($result, true);
		if ( isset($content['aliases']) )
		{
			for($i = 0; $i < count($content['aliases']); $i++)
			{
				// --- Set CSS Class
				if ( $i % 2 == 0 )
					$content['aliases'][$i]['cssclass'] = "line1";
				else
					$content['aliases'][$i]['cssclass'] = "line2";
				// --- 
			}
		}
		// --- 

		if ( isset($content['aliases']) )
		{
			// --- BEGIN PlayerDetails Code for front stats
			$sqlquery = "SELECT " .
								STATS_PLAYERS . ".GUID, " . 
								STATS_PLAYERS_STATIC . ".PBGUID as PBGuid, " . 
								"sum( " . STATS_PLAYERS . ".Kills) as Kills, " . 
								"sum( " . STATS_PLAYERS . ".Deaths) as Deaths, " . 
								"sum( " . STATS_PLAYERS . ".Teamkills) as Teamkills, " .
								"sum( " . STATS_PLAYERS . ".Suicides) as Suicides, " . 
//								"round(AVG( " . STATS_PLAYERS . ".KillRatio),2) as KillRatio " .
								"sum(" . STATS_PLAYERS . ".Kills) / sum(" . STATS_PLAYERS . ".Deaths) as KillRatio " .	// TRUE l33tAGE!
//								STATS_ALIASES . ".Alias, " .
//								STATS_ALIASES . ".AliasAsHtml " .
								" FROM " . STATS_PLAYERS . 
								" LEFT OUTER JOIN (" . STATS_PLAYERS_STATIC . 
								") ON (" . 
								STATS_PLAYERS_STATIC . ".GUID=" . STATS_PLAYERS . ".GUID) " . 
								" WHERE " . STATS_PLAYERS . ".GUID = " . $content['playerguid'] . " " . 
//								" AND " . STATS_ALIASES . ".Count = " . $content['aliases'][0]['Count'] .
								GetCustomServerWhereQuery(STATS_PLAYERS, false) . // GetCustomServerWhereQuery(STATS_ALIASES, false) . 
								GetBannedPlayerWhereQuery(STATS_PLAYERS, "GUID", false) . 
								" GROUP BY " . STATS_PLAYERS . ".GUID "; // . 
//								" ORDER BY AliasCount DESC";
			$result = DB_Query($sqlquery);
			$playervars = DB_GetSingleRow($result, true);

			if ( isset($playervars['GUID']) )
			{
				// Extend Array with Aliases
				FillPlayerWithAlias( $playervars, "GUID" );

				// Extend with Time Played
				FillPlayerWithTime( $playervars, "GUID" ); 

				// Valid GUID, go on
				$content['playerenabled'] = "true";

				// Set Playervars 
				if ( isset($playervars['PBGuid']) && strlen($playervars['PBGuid']) > 0 )
				{
					// Enable Showing GUID
					$content['EnableShowPBGuid'] = true;
					$content['PBGuid'] = substr($playervars['PBGuid'], 24); // Only show last 8 digits for security reasons!
				}
				else
				{
					// Disable Showing GUID
					$content['EnableShowPBGuid'] = false;
					$content['PBGuid'] = "";
				}
				$content['Kills'] = $playervars['Kills'];
				$content['Deaths'] = $playervars['Deaths'];
				$content['Teamkills'] = $playervars['Teamkills'];
				$content['Suicides'] = $playervars['Suicides'];

				// --- Lets get the MAX KillRatio first
				GetAndSetMaxKillRation();
				// --- 


				// --- Set KillRation Values and Bars
				$content['KillRatio'] = $playervars['KillRatio'];
				$content['BarImageKillRatioMinus'] = $gl_root_path . "images/bars/bar-small/red_middle_9.png";
				$content['BarImageKillRatioPlus'] = $gl_root_path . "images/bars/bar-small/green_middle_9.png";

				if ( isset($content['MaxKillRatio']) )
				{
					// Now we set the Width of the images
					if ( $content['KillRatio'] > 1 )
					{
						$content['KillRatioWidthMinus'] = $content['MaxPixelWidth'];
						$content['KillRatioWidthMinusText'] = "";
					}
					else
					{
						$content['KillRatioWidthMinus'] = intval($content['KillRatio'] * $content['MaxPixelWidth']);
						$content['KillRatioWidthMinusText'] =  $content['KillRatioWidthMinus'] . "% of 1:0 Ratio";;
					}

					if ( $content['KillRatio'] < 1 )
					{
						$content['KillRatioWidthPlus'] = "0";
						$content['KillRatioWidthPlusText'] = "";
					}
					else
					{
						$content['KillRatioWidthPlus'] = intval( ($content['KillRatio'] / ($content['MaxKillRatio']/$content['MaxPixelWidth'])) );
						$content['KillRatioWidthPlusText'] = $content['KillRatioWidthPlus'] . "% of best Ratio (Which is " . $content['MaxKillRatio'] . ")";
					}
				}
				else
				{
					$content['KillRatioWidthMinus'] = "0";
					$content['KillRatioWidthPlus'] = "0";
				}
				// --- 

				$content['Alias'] = $playervars['Alias'];
				$content['AliasAsHtml'] = $playervars['AliasAsHtml'];

				
				// --- Get Favourite MAP
				// Get Rounds first
				$sqlquery = "SELECT " . STATS_ROUNDS . ".ID " . 
							" FROM " . STATS_ROUNDS . 
							" INNER JOIN (" . STATS_PLAYER_KILLS . ") ON (" . 
							STATS_PLAYER_KILLS . ".ROUNDID=" . STATS_ROUNDS . ".ID)" . 
							" WHERE " . STATS_PLAYER_KILLS . ".PLAYERID=" . $content['playerguid'] . 
							GetCustomServerWhereQuery( STATS_ROUNDS, false) . 
							" GROUP BY " . STATS_ROUNDS . ".ID";

				$result = DB_Query( $sqlquery );
				$tmparray = DB_GetAllRows($result, true);
				if ( isset($tmparray) )
				{
					foreach ($tmparray as $singleentry)
					{
						if ( isset($myrounds) ) { $myrounds .= ", "; } else { $myrounds = ""; }
						$myrounds .= $singleentry['ID'];
					}

					$sqlquery = "SELECT " . STATS_ROUNDS . ".MAPID, " . 
											"Count(" . STATS_ROUNDS . ".MAPID) as mapcount, " . 
											STATS_MAPS . ".MAPNAME, " . 
											STATS_MAPS . ".DisplayName " . 
											" FROM " . STATS_ROUNDS . 
											" INNER JOIN (" . STATS_MAPS . ") ON (" . 
											STATS_MAPS . ".ID=" . STATS_ROUNDS . ".MAPID) " . 
											" WHERE " . STATS_ROUNDS . ".ID IN (" . $myrounds . ")" . 
											" GROUP BY " . STATS_ROUNDS . ".MAPID" . 
											" ORDER BY mapcount DESC";
					$result = DB_Query( $sqlquery );
					$maparray = DB_GetAllRows($result, true);
					$content['PLAYER_MAPNAME'] = $maparray[0]['MAPNAME'];
					$content['PLAYER_MAPCOUNT'] = $maparray[0]['mapcount'];

					// --- Set Mapimage
					$content['PLAYER_MapImage'] = $gl_root_path . "images/maps/small/" . $content['PLAYER_MAPNAME'] . ".jpg";
					if ( !is_file($content['PLAYER_MapImage']) )
						$content['PLAYER_MapImage'] = $gl_root_path . "images/maps/no-pic.jpg";
					// --- 

					// --- Set DisplayName
					if ( isset($maparray['DisplayName']) )
						$content['PLAYER_FinalMapDisplayName'] = $maparray[0]['DisplayName'];
					else
						$content['PLAYER_FinalMapDisplayName'] = $content['PLAYER_MAPNAME'];
					// --- 
					
					// Set True
					$content['isfavmap'] = "true";
				}
				// --- 

				// --- Get Favourite Weapon
				$sqlquery = "SELECT " . STATS_PLAYER_KILLS . ".WEAPONID, " . 
										"Sum(" . STATS_PLAYER_KILLS . ".Kills) as TotalKills, " . 
										STATS_WEAPONS . ".INGAMENAME, " . 
										STATS_WEAPONS . ".DisplayName " . 
										" FROM " . STATS_PLAYER_KILLS . 
										" INNER JOIN (" . STATS_WEAPONS . ") ON (" . 
										STATS_WEAPONS . ".ID=" . STATS_PLAYER_KILLS . ".WEAPONID) " . 
										" WHERE " . STATS_PLAYER_KILLS . ".PLAYERID=" .  $content['playerguid'] . 
										GetCustomServerWhereQuery( STATS_PLAYER_KILLS, false) . 
										" GROUP BY " . STATS_WEAPONS . ".INGAMENAME" . 
										" ORDER BY TotalKills DESC";

				$result = DB_Query( $sqlquery );
				// Get Player Weapons, for later use
				$content['playerweapons'] = DB_GetAllRows($result, true);

				if ( isset($content['playerweapons']) )
				{
					$content['PLAYER_WEAPONID'] = $content['playerweapons'][0]['INGAMENAME'];
					$content['PLAYER_WEAPONCOUNT'] = $content['playerweapons'][0]['TotalKills'];

					// --- Set Weaponimage
					$tmpWeaponimg = ReturnWeaponBaseName($content['PLAYER_WEAPONID']);
					$content['PLAYER_WeaponImage'] = $gl_root_path . "images/weapons/normal/" . $tmpWeaponimg . ".png";
					if ( !is_file($content['PLAYER_WeaponImage']) )
						$content['PLAYER_WeaponImage'] = $gl_root_path . "images/weapons/no-pic.jpg";
					// --- 

					// --- Set DisplayName
					if ( isset($content['playerweapons'][0]['DisplayName']) && strlen($content['playerweapons'][0]['DisplayName']) > 0 )
						$content['PLAYER_FinalWeaponDisplayName'] = $content['playerweapons'][0]['DisplayName'];
					else
						$content['PLAYER_FinalWeaponDisplayName'] = $content['PLAYER_WEAPONID'];
					// --- 
					
					// Set True
					$content['isfavweapon'] = "true";
				}
				// --- 

				// --- Top Used Weapons
				$sqlquery = "SELECT " . STATS_PLAYER_KILLS . ".WEAPONID, " . 
										"Sum(" . STATS_PLAYER_KILLS . ".Kills) as TotalKills, " . 
										STATS_WEAPONS . ".INGAMENAME, " . 
										STATS_WEAPONS . ".DisplayName " . 
										" FROM " . STATS_PLAYER_KILLS . 
										" INNER JOIN (" . STATS_WEAPONS . ") ON (" . 
										STATS_WEAPONS . ".ID=" . STATS_PLAYER_KILLS . ".WEAPONID) " . 
										" WHERE " . STATS_PLAYER_KILLS . ".PLAYERID=" .  $content['playerguid'] . 
										GetCustomServerWhereQuery( STATS_PLAYER_KILLS, false) . 
										" GROUP BY " . STATS_WEAPONS . ".INGAMENAME" . 
										" ORDER BY TotalKills DESC";
				$result = DB_Query( $sqlquery );
				$content['weaponstats'] = DB_GetAllRows($result, true);
				if ( isset($content['weaponstats']) )
				{
					$content['isweaponstats'] = "true";
					
					// Set Max Percent for bars
					$maxpercent = $content['weaponstats'][0]['TotalKills'];
					$maxwidth = 200;

					for($i = 0; $i < count($content['weaponstats']); $i++)
					{
						// --- Set DisplayName
						if ( !isset($content['weaponstats'][$i]['DisplayName']) || strlen($content['weaponstats'][$i]['DisplayName']) <= 0 )
							$content['weaponstats'][$i]['DisplayName'] = $content['weaponstats'][$i]['INGAMENAME'];
						// --- 

						// --- Set Weaponimage
						$tmpWeaponimg = ReturnWeaponBaseName($content['weaponstats'][$i]['INGAMENAME']);
						$content['weaponstats'][$i]['WeaponImage'] = $gl_root_path . "images/weapons/thumbs/" . $tmpWeaponimg . ".png";
						if ( !is_file($content['weaponstats'][$i]['WeaponImage']) )
							$content['weaponstats'][$i]['WeaponImage'] = $gl_root_path . "images/weapons/no-pic.jpg";
						// --- 

						// --- Set CSS Class
						if ( $i % 2 == 0 )
							$content['weaponstats'][$i]['cssclass'] = "line1";
						else
							$content['weaponstats'][$i]['cssclass'] = "line2";
						// --- 

						// --- Set Bar Image
						$content['weaponstats'][$i]['KillBarPercent'] = intval(($content['weaponstats'][$i]['TotalKills'] / $maxpercent) * 100);
						$content['weaponstats'][$i]['KillBarWidth'] = $content['weaponstats'][$i]['KillBarPercent'] - 11; // Percentage Bar !
	//					$content['weaponstats'][$i]['KillBarWidth'] = $maxwidth * ( $content['weaponstats'][$i]['KillBarPercent'] / 100 );

						$content['weaponstats'][$i]['BarImageLeft'] = $gl_root_path . "images/bars/bar-small/green_left_9.png";
						$content['weaponstats'][$i]['BarImageMiddle'] = $gl_root_path . "images/bars/bar-small/green_middle_9.png";
						$content['weaponstats'][$i]['BarImageRight'] = $gl_root_path . "images/bars/bar-small/green_right_9.png";
						// --- 
					}
				}
				// --- 

				// --- Top played Map
				if ( isset($myrounds) )
				{
					$sqlquery = "SELECT " . STATS_ROUNDS . ".MAPID, " . 
											"Count(" . STATS_ROUNDS . ".MAPID) as mapcount, " . 
											STATS_MAPS . ".MAPNAME, " . 
											STATS_MAPS . ".DisplayName " . 
											" FROM " . STATS_ROUNDS . 
											" INNER JOIN (" . STATS_MAPS . ") ON (" . 
											STATS_MAPS . ".ID=" . STATS_ROUNDS . ".MAPID) " . 
											" WHERE " . STATS_ROUNDS . ".ID IN (" . $myrounds . ")" . 
											" GROUP BY " . STATS_ROUNDS . ".MAPID" . 
											" ORDER BY mapcount DESC LIMIT 10";

					$result = DB_Query( $sqlquery );
					$content['mapstats'] = DB_GetAllRows($result, true);

					if ( isset($content['mapstats']) )
					{
						$content['ismapstats'] = "true";

						// Set Max Percent for bars
						$maxpercent = $content['mapstats'][0]['mapcount'];
						$maxwidth = 200;

						for($i = 0; $i < count($content['mapstats']); $i++)
						{
							// --- Set Mapimage
							$content['mapstats'][$i]['MapImage'] = $gl_root_path . "images/maps/thumbs/" . $content['mapstats'][$i]['MAPNAME'] . ".jpg";
							if ( !is_file($content['mapstats'][$i]['MapImage']) )
								$content['mapstats'][$i]['MapImage'] = $gl_root_path . "images/maps/no-pic.jpg";
							// --- 

							// --- Set DisplayName
							if ( isset($content['mapstats'][$i]['DisplayName']) && strlen($content['mapstats'][$i]['DisplayName']) > 0 )
								$content['mapstats'][$i]['FinalMapDisplayName'] = $content['mapstats'][$i]['DisplayName'];
							else
								$content['mapstats'][$i]['FinalMapDisplayName'] = $content['mapstats'][$i]['MAPNAME'];
							// --- 

							// --- Set CSS Class
							if ( $i % 2 == 0 )
								$content['mapstats'][$i]['cssclass'] = "line1";
							else
								$content['mapstats'][$i]['cssclass'] = "line2";
							// --- 

							// --- Set Bar Image
							$content['mapstats'][$i]['KillBarPercent']	= intval(($content['mapstats'][$i]['mapcount'] / $maxpercent) * 100);
							$content['mapstats'][$i]['KillBarWidth']	= $content['mapstats'][$i]['KillBarPercent'] - 9; // Percentage Bar !

							$content['mapstats'][$i]['BarImageLeft']	= $gl_root_path . "images/bars/bar-small/green_left_9.png";
							$content['mapstats'][$i]['BarImageMiddle']	= $gl_root_path . "images/bars/bar-small/green_middle_9.png";
							$content['mapstats'][$i]['BarImageRight']	= $gl_root_path . "images/bars/bar-small/green_right_9.png";
							// --- 
						}
						
					}
				}
				// --- 



				// --- Top Victims
				$sqlquery = "SELECT " . STATS_PLAYER_KILLS . ".ENEMYID, " . 
										"Sum(" . STATS_PLAYER_KILLS . ".Kills) as TotalKills " . 
//										STATS_ALIASES . ".PLAYERID, " . 
//										"(" . STATS_ALIASES . ".Alias) as EnemyAlias, " . 
//										"(" . STATS_ALIASES . ".AliasAsHtml) as EnemyAliasHtml " . 
										" FROM " . STATS_PLAYER_KILLS . 
//										" INNER JOIN (" . STATS_ALIASES . ") ON (" . 
//										STATS_PLAYER_KILLS . ".ENEMYID=" . STATS_ALIASES . ".PLAYERID) " . 
										" WHERE " . STATS_PLAYER_KILLS . ".PLAYERID=" .  $content['playerguid'] . 
										GetCustomServerWhereQuery( STATS_PLAYER_KILLS, false) . 
										" GROUP BY " . STATS_PLAYER_KILLS . ".ENEMYID" . 
										" ORDER BY TotalKills DESC LIMIT 15";
				$result = DB_Query( $sqlquery );
				$content['victimstats'] = DB_GetAllRows($result, true);
				if ( isset($content['victimstats']) )
				{
					$content['isvictimstats'] = "true";

					// Extend PlayerAliases
					FindAndFillTopAliases($content['victimstats'], "ENEMYID", "EnemyAlias", "EnemyAliasHtml" );

					// Set Max Percent for bars
					$maxpercent = $content['victimstats'][0]['TotalKills'];
					$maxwidth = 200;

					for($i = 0; $i < count($content['victimstats']); $i++)
					{
						// --- Set CSS Class
						if ( $i % 2 == 0 )
							$content['victimstats'][$i]['cssclass'] = "line1";
						else
							$content['victimstats'][$i]['cssclass'] = "line2";
						// --- 

						// --- Set Bar Image
						$content['victimstats'][$i]['KillBarPercent'] = intval(($content['victimstats'][$i]['TotalKills'] / $maxpercent) * 100);
						$content['victimstats'][$i]['KillBarWidth'] = $content['victimstats'][$i]['KillBarPercent'] - 9; // Percentage Bar !

						$content['victimstats'][$i]['BarImageLeft'] = $gl_root_path . "images/bars/bar-small/green_left_9.png";
						$content['victimstats'][$i]['BarImageMiddle'] = $gl_root_path . "images/bars/bar-small/green_middle_9.png";
						$content['victimstats'][$i]['BarImageRight'] = $gl_root_path . "images/bars/bar-small/green_right_9.png";
						// --- 
					}
				}
				// --- 

				// --- Top Killers
				$sqlquery = "SELECT " . STATS_PLAYER_KILLS . ".PLAYERID, " . 
										"Sum(" . STATS_PLAYER_KILLS . ".Kills) as TotalKills " . 
	//									STATS_ALIASES . ".PLAYERID, " . 
//										"(" . STATS_ALIASES . ".Alias) as KillerAlias, " . 
//										"(" . STATS_ALIASES . ".AliasAsHtml) as KillerAliasHtml " . 
										" FROM " . STATS_PLAYER_KILLS . 
//										" INNER JOIN (" . STATS_ALIASES . ") ON (" . 
//										STATS_PLAYER_KILLS . ".PLAYERID=" . STATS_ALIASES . ".PLAYERID) " . 
										" WHERE " . STATS_PLAYER_KILLS . ".ENEMYID=" .  $content['playerguid'] . GetCustomServerWhereQuery( STATS_PLAYER_KILLS, false) . 
										" GROUP BY " . STATS_PLAYER_KILLS . ".PLAYERID" . 
										" ORDER BY TotalKills DESC LIMIT 15";
				$result = DB_Query( $sqlquery );
				$content['killedstats'] = DB_GetAllRows($result, true);
				if ( isset($content['killedstats']) )
				{
					$content['iskilledbystats'] = "true";

					// Extend PlayerAliases
					FindAndFillTopAliases($content['killedstats'], "PLAYERID", "KillerAlias", "KillerAliasHtml" );

					// Set Max Percent for bars
					$maxpercent = $content['killedstats'][0]['TotalKills'];
					$maxwidth = 200;

					for($i = 0; $i < count($content['killedstats']); $i++)
					{
						// --- Set CSS Class
						if ( $i % 2 == 0 )
							$content['killedstats'][$i]['cssclass'] = "line1";
						else
							$content['killedstats'][$i]['cssclass'] = "line2";
						// --- 

						// --- Set Bar Image
						$content['killedstats'][$i]['KillBarPercent'] = intval(($content['killedstats'][$i]['TotalKills'] / $maxpercent) * 100);
						$content['killedstats'][$i]['KillBarWidth'] = $content['killedstats'][$i]['KillBarPercent'] - 9; // Percentage Bar !

						$content['killedstats'][$i]['BarImageLeft'] = $gl_root_path . "images/bars/bar-small/green_left_9.png";
						$content['killedstats'][$i]['BarImageMiddle'] = $gl_root_path . "images/bars/bar-small/green_middle_9.png";
						$content['killedstats'][$i]['BarImageRight'] = $gl_root_path . "images/bars/bar-small/green_right_9.png";
						// --- 
					}
				}
				// --- 


				// --- Read Last 10 Chats from player ;)!
				$sqlquery = "SELECT " .
									STATS_CHAT . ".ROUNDID, " .
									STATS_CHAT . ".TextSaid " .
									" FROM " . STATS_CHAT . 
									" WHERE " . STATS_CHAT . ".PLAYERID=" . $content['playerguid'] . 
									GetCustomServerWhereQuery( STATS_CHAT, false) . 
									" ORDER BY " . STATS_CHAT . ".ID DESC " .
									" LIMIT 10 ";

				// NO Order should be like said in the game
				$result = DB_Query($sqlquery);
				$content['ChatLog'] = DB_GetAllRows($result, true);
				if ( isset($content['ChatLog']) )
				{
					$content['ischatlog'] = "true";

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
				}
				// --- 

				
				// --- Top HitLocations Killed!
				// PreInit All HitLocations
				$sqlquery = "SELECT " . STATS_HITLOCATIONS . ".ID, " . 
							STATS_HITLOCATIONS . ".BODYPART, " . 
							STATS_HITLOCATIONS . ".DisplayName " . 
							" FROM " . STATS_HITLOCATIONS;
				$result = DB_Query( $sqlquery );
				$hitlocations = DB_GetAllRows($result, true);
				if ( isset($hitlocations) )
				{
					for($i = 0; $i < count($hitlocations); $i++)
					{
						$content['KILLEDDETAILS'][0][ $hitlocations[$i]['BODYPART'] ] = $hitlocations[$i]['BODYPART'];
						$content['KILLEDDETAILS'][0][ $hitlocations[$i]['BODYPART'] . "_display" ] = $hitlocations[$i]['DisplayName'];
						$content['KILLEDDETAILS'][0][ $hitlocations[$i]['BODYPART'] . "_level" ] = 0;
					}
				}

				// Get Detailed info for each bodypart
				$sqlquery = "SELECT sum(" . STATS_PLAYER_KILLS . ".Kills) as TotalKills, " . 
							STATS_HITLOCATIONS . ".ID, " . 
							STATS_HITLOCATIONS . ".BODYPART, " . 
							STATS_HITLOCATIONS . ".DisplayName " . 
							" FROM " . STATS_PLAYER_KILLS . 
							" INNER JOIN (" . STATS_HITLOCATIONS . 
							") ON (" . 
							STATS_HITLOCATIONS . ".ID=" . STATS_PLAYER_KILLS . ".HITLOCATIONID) " . 
							" WHERE " . STATS_PLAYER_KILLS . ".PLAYERID=" .  $content['playerguid'] . GetCustomServerWhereQuery( STATS_PLAYER_KILLS, false) . 
							" GROUP BY " . STATS_HITLOCATIONS . ".ID" . 
							" ORDER BY TotalKills DESC";
				$result = DB_Query( $sqlquery );
				$content['KILLEDDETAILS'][0]['hitlocations'] = DB_GetAllRows($result, true);
				if ( isset($content['KILLEDDETAILS'][0]['hitlocations']) )
				{
					for($i = 0; $i < count($content['KILLEDDETAILS'][0]['hitlocations']); $i++)
					{
						// Set default props
						$content['KILLEDDETAILS'][0][ $content['KILLEDDETAILS'][0]['hitlocations'][$i]['BODYPART'] ] = $content['KILLEDDETAILS'][0]['hitlocations'][$i]['BODYPART'];
						$content['KILLEDDETAILS'][0][ $content['KILLEDDETAILS'][0]['hitlocations'][$i]['BODYPART'] . "_display" ] = $content['KILLEDDETAILS'][0]['hitlocations'][$i]['DisplayName'];

						if ( !isset($content['KILLEDDETAILS'][0]['hitlocations'][$i]['TotalKills']) )
							$content['KILLEDDETAILS'][0]['hitlocations'][$i]['TotalKills'] = 0;

						$content['KILLEDDETAILS'][0]['hitlocations'][$i]['Number'] = $i+1;
						// --- 

						// --- Set CSS Class
						if ( $i % 2 == 0 )
							$content['KILLEDDETAILS'][0]['hitlocations'][$i]['cssclass'] = "line1";
						else
							$content['KILLEDDETAILS'][0]['hitlocations'][$i]['cssclass'] = "line2";
						// --- 
						
						// --- Calc Hitlocations and Round up by 10
						if ( $content['Kills'] > 0 )
							$tmpval = intval( $content['KILLEDDETAILS'][0]['hitlocations'][$i]['TotalKills'] / ($content['Kills'] / 100));
						else 
							$tmpval = 0;
						if ( $tmpval > 0 )
						{
							$tmpval += 10;
							$tmpval = intval($tmpval/10) * 10;
						}
						$content['KILLEDDETAILS'][0][ $content['KILLEDDETAILS'][0]['hitlocations'][$i]['BODYPART'] . "_level" ] = $tmpval;
					}
				}
				// --- 


				// --- Top HitLocations Killed BY Others
				// PreInit All HitLocations
				$sqlquery = "SELECT " . STATS_HITLOCATIONS . ".ID, " . 
							STATS_HITLOCATIONS . ".BODYPART, " . 
							STATS_HITLOCATIONS . ".DisplayName " . 
							" FROM " . STATS_HITLOCATIONS;
				$result = DB_Query( $sqlquery );
				$hitlocations = DB_GetAllRows($result, true);
				if ( isset($hitlocations) )
				{
					for($i = 0; $i < count($hitlocations); $i++)
					{
						$content['KILLEDBYDETAILS'][0][ $hitlocations[$i]['BODYPART'] ] = $hitlocations[$i]['BODYPART'];
						$content['KILLEDBYDETAILS'][0][ $hitlocations[$i]['BODYPART'] . "_display" ] = $hitlocations[$i]['DisplayName'];
						$content['KILLEDBYDETAILS'][0][ $hitlocations[$i]['BODYPART'] . "_level" ] = 0;
					}
				}

				// Get Detailed info for each bodypart
				$sqlquery = "SELECT sum(" . STATS_PLAYER_KILLS . ".Kills) as TotalKills, " . 
							STATS_HITLOCATIONS . ".ID, " . 
							STATS_HITLOCATIONS . ".BODYPART, " . 
							STATS_HITLOCATIONS . ".DisplayName " . 
							" FROM " . STATS_PLAYER_KILLS . 
							" INNER JOIN (" . STATS_HITLOCATIONS . 
							") ON (" . 
							STATS_HITLOCATIONS . ".ID=" . STATS_PLAYER_KILLS . ".HITLOCATIONID) " . 
							" WHERE " . STATS_PLAYER_KILLS . ".ENEMYID=" . $content['playerguid'] . GetCustomServerWhereQuery( STATS_PLAYER_KILLS, false) . 
							" GROUP BY " . STATS_HITLOCATIONS . ".ID" . 
							" ORDER BY TotalKills DESC";
				$result = DB_Query( $sqlquery );
				$content['KILLEDBYDETAILS'][0]['hitlocations'] = DB_GetAllRows($result, true);
				if ( isset($content['KILLEDBYDETAILS'][0]['hitlocations']) )
				{
					for($i = 0; $i < count($content['KILLEDBYDETAILS'][0]['hitlocations']); $i++)
					{
						// Set default props
						$content['KILLEDBYDETAILS'][0][ $content['KILLEDBYDETAILS'][0]['hitlocations'][$i]['BODYPART'] ] = $content['KILLEDBYDETAILS'][0]['hitlocations'][$i]['BODYPART'];
						$content['KILLEDBYDETAILS'][0][ $content['KILLEDBYDETAILS'][0]['hitlocations'][$i]['BODYPART'] . "_display" ] = $content['KILLEDBYDETAILS'][0]['hitlocations'][$i]['DisplayName'];

						if ( !isset($content['KILLEDBYDETAILS'][0]['hitlocations'][$i]['TotalKills']) )
							$content['KILLEDBYDETAILS'][0]['hitlocations'][$i]['TotalKills'] = 0;

						$content['KILLEDBYDETAILS'][0]['hitlocations'][$i]['Number'] = $i+1;
						// --- 

						// --- Set CSS Class
						if ( $i % 2 == 0 )
							$content['KILLEDBYDETAILS'][0]['hitlocations'][$i]['cssclass'] = "line1";
						else
							$content['KILLEDBYDETAILS'][0]['hitlocations'][$i]['cssclass'] = "line2";
						// --- 
						
						// --- Calc Hitlocations and Round up by 10

						if ( $content['Kills'] > 0 )
							$tmpval = intval( $content['KILLEDBYDETAILS'][0]['hitlocations'][$i]['TotalKills'] / ($content['Kills'] / 100));
						else 
							$tmpval = 0;

						if ( $tmpval > 0 )
						{
							$tmpval += 10;
							$tmpval = intval($tmpval/10) * 10;
						}
						$content['KILLEDBYDETAILS'][0][ $content['KILLEDBYDETAILS'][0]['hitlocations'][$i]['BODYPART'] . "_level" ] = $tmpval;
					}
				}
				// --- 


				// --- Last player rounds Others
				$sqlquery = "SELECT " .
									STATS_ROUNDS . ".ID, " .
									STATS_ROUNDS . ".TIMEADDED, " . 
									STATS_ROUNDS . ".ROUNDDURATION, " . 
									STATS_ROUNDS . ".AxisRoundWins, " . 
									STATS_ROUNDS . ".AlliesRoundWins, " .
									STATS_PLAYER_KILLS . ".PLAYERID, " . 
									STATS_GAMETYPES . ".NAME as GameTypeName, " . 
									STATS_GAMETYPES . ".DisplayName as GameTypeDisplayName, " . 
									STATS_MAPS . ".MAPNAME ," . 
									STATS_MAPS . ".DisplayName as MapDisplayName" . 
									" FROM " . STATS_ROUNDS . 
									" INNER JOIN (" . STATS_GAMETYPES . ", " . STATS_MAPS . ", " . STATS_PLAYER_KILLS .
									") ON (" . 
									STATS_GAMETYPES . ".ID=" . STATS_ROUNDS . ".GAMETYPE AND " . 
									STATS_MAPS . ".ID=" . STATS_ROUNDS . ".MAPID AND " . 
									STATS_PLAYER_KILLS . ".ROUNDID=" . STATS_ROUNDS . ".ID)" . 
									" WHERE " . STATS_PLAYER_KILLS . ".PLAYERID=" . $content['playerguid'] . 
									GetCustomServerWhereQuery( STATS_ROUNDS, false) . 
									" GROUP BY " . STATS_ROUNDS . ".ID" . 
									" ORDER BY TIMEADDED DESC LIMIT 10";
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

						// --- Set GametypeName 
						if ( isset($content['lastrounds'][$i]['GameTypeDisplayName']) )
							$content['lastrounds'][$i]['FinalGameTypeDisplayName'] = $content['lastrounds'][$i]['GameTypeDisplayName'];
						else
							$content['lastrounds'][$i]['FinalGameTypeDisplayName'] = $content['lastrounds'][$i]['GameTypeName'];
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
			$content['iserror'] = "true";
		// --- END LastRounds Code for front stats
	}
}
else
{
	// Invalid Guid!
	$content['iserror'] = "true";
}
// --- 

// --- CONTENT Vars
if ( $content['iserror'] == "true" )
{
		$content['TITLE'] = "Ultrastats :: Player Derails :: Error invalid GUID";
}
else
{
	if ( isset($content['myserver']) ) 
		$content['TITLE'] = "Ultrastats :: Player Derails for '" . $playervars['Alias'] . "' :: Server '" . $content['myserver']['Name'] . "'";	// Title of the Page 
	else
		$content['TITLE'] = "Ultrastats :: Player Derails for '" . $playervars['Alias'] . "'";
}
// --- 

// --- Parsen and Output
InitTemplateParser();
$page -> parser($content, "players-detail.html");
$page -> output(); 
// --- 

?>