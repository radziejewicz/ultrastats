<?php
/*
	*********************************************************************
	* Copyright by Andre Lorbach | 2006, 2007, 2008						*
	* -> www.ultrastats.org <-											*
	*																	*
	* Use this script at your own risk!									*
	* -----------------------------------------------------------------	*
	* Round Detail File													*
	*																	*
	* -> Displayes details of a played round							*
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
	$content['TITLE'] = "Ultrastats :: Rounds :: Server '" . $content['myserver']['Name'] . "'";	// Title of the Page 
else
	$content['TITLE'] = "Ultrastats :: Rounds";
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
		// --- BEGIN LastRounds Code for front stats
		$sqlquery = "SELECT " .
							STATS_ROUNDS . ".TIMEADDED, " . 
							STATS_ROUNDS . ".ROUNDDURATION , " . 
							STATS_ROUNDS . ".ServerCvars , " . 
							STATS_ROUNDS . ".AxisRoundWins , " . 
							STATS_ROUNDS . ".AxisGuids , " . 
							STATS_ROUNDS . ".AlliesRoundWins , " . 
							STATS_ROUNDS . ".AlliesGuids , " . 
							STATS_GAMETYPES . ".NAME , " . 
							STATS_GAMETYPES . ".DisplayName as GameTypeDisplayName, " . 
							STATS_MAPS . ".MAPNAME ," . 
							STATS_MAPS . ".DisplayName as MapDisplayName" . 
							" FROM " . STATS_ROUNDS . 
							" INNER JOIN (" . STATS_GAMETYPES . ", " .  STATS_MAPS . 
							") ON (" . 
							STATS_GAMETYPES . ".ID=" . STATS_ROUNDS . ".GAMETYPE AND " . 
							STATS_MAPS . ".ID=" . STATS_ROUNDS . ".MAPID) " . 
							" WHERE " . STATS_ROUNDS . ".ID = " . $content['roundid'] . " " . 
							GetCustomServerWhereQuery(STATS_ROUNDS, false);
//							" GROUP BY " . STATS_PLAYERS . ".GUID");
		$result = DB_Query($sqlquery);

		$roundvars = DB_GetSingleRow($result, true);
		if ( isset($roundvars) )
		{
			// Enable Stats
			$content['roundsenabled'] = "true";

			// Copy round Variables
			$content['ServerCvars'] = $roundvars['ServerCvars'];
			$content['AxisRoundWins'] = $roundvars['AxisRoundWins'];
			$content['AlliesRoundWins'] = $roundvars['AlliesRoundWins'];
			$content['GameTypeName'] = $roundvars['NAME'];
			$content['GameTypeDisplayName'] = $roundvars['GameTypeDisplayName'];
			$content['MapName'] = $roundvars['MAPNAME'];
			$content['MapDisplayName'] = $roundvars['MapDisplayName'];

			if ( $content['GameTypeName'] == "dm" )
			{
				// Deathmatch is special handeled
				$content['rounds_dm_enabled'] = "true";

				// Get Player Stats from DB
				$content['AllPlayers'] = GetPlayerDMDetails();

				// --- Combine All Players for analysis
				if ( isset($content['AllPlayers']) )
				{
					ExtendPlayerData( $content['AllPlayers'] );
					$AllPlayers = $content['AllPlayers'];
				}
				// ---
			}
			else
			{
				// All other Team based modes!
				$content['rounds_team_enabled'] = "true";

				// --- Get and Parse Axis Players
				if ( isset($roundvars['AxisGuids']) && strlen($roundvars['AxisGuids']) > 0 )
				{
					// Get and Parse AxisGuids and Players
					$axisguids = str_replace( ";", ", ", $roundvars['AxisGuids']);

					// Get Player Stats from DB
					$content['AxisPlayers'] = GetPlayerDetails( $axisguids );
				}
				// ---

				// --- Get and Parse Allies Players
				if ( isset($roundvars['AlliesGuids']) && strlen($roundvars['AlliesGuids']) > 0 )
				{
					// Get and Parse AxisGuids and Players
//					$alliesguids = explode(";", $roundvars['AlliesGuids']);
					$alliesguids = str_replace( ";", ", ", $roundvars['AlliesGuids']);

					// Get Player Stats from DB
					$content['AlliesPlayers'] = GetPlayerDetails( $alliesguids );
				}
				// ---

				// --- Set CSS Classes for Teams
				if		( intval($content['AxisRoundWins']) > intval($content['AlliesRoundWins']) )
				{
					$content['AxisTeamClass'] = "WinnerTeam";
					$content['AlliesTeamClass'] = "LoserTeam";
				}
				else if ( intval($content['AxisRoundWins']) < intval($content['AlliesRoundWins']) )
				{
					$content['AxisTeamClass'] = "LoserTeam";
					$content['AlliesTeamClass'] = "WinnerTeam";
				}
				else
				{
					$content['AxisTeamClass'] = "DrawTeam";
					$content['AlliesTeamClass'] = "DrawTeam";
				}
				// --- 

				// --- Combine All Players for analysis
				if ( isset($content['AxisPlayers']) )
					ExtendPlayerData( $content['AxisPlayers'] );
				if ( isset($content['AlliesPlayers']) )
					ExtendPlayerData( $content['AlliesPlayers'] );

				if ( isset($content['AlliesPlayers']) && isset($content['AxisPlayers']))
					$AllPlayers = array_merge( $content['AxisPlayers'], $content['AlliesPlayers'] );
				else if ( isset($content['AxisPlayers']) )
					$AllPlayers = $content['AxisPlayers'];
				else if ( isset($content['AlliesPlayers']) )
					$AllPlayers = $content['AlliesPlayers'];
				// ---

				// --- Now we get players who played but did not finish the round
				if ( isset($AllPlayers) )
				{
					for($i = 0; $i < count($AllPlayers); $i++)
					{
						if ( isset($allplayedguids) )
							$allplayedguids .= ", " . $AllPlayers[$i]['PLAYERID'];
						else
							$allplayedguids = $AllPlayers[$i]['PLAYERID'];
					}
				}
				else
					$allplayedguids = "";

				// Get players who did not finish the job ;)!
				$content['unfinishedplayers'] = GetRoundPlayerDetails( $allplayedguids );
				if ( isset($content['unfinishedplayers']) )
				{
					// Deathmatch is special handeled
					$content['rounds_unfinished_enabled'] = "true";

					// --- Combine All Players for analysis
					ExtendPlayerData( $content['unfinishedplayers'] );
					if ( isset($AllPlayers) )
						$AllPlayers = array_merge( $AllPlayers, $content['unfinishedplayers'] );
					else
						$AllPlayers = $content['unfinishedplayers'];
					// ---

					for($i = 0; $i < count($content['unfinishedplayers']); $i++)
					{
						if ( $i % 2 == 1 ) 
							$content['unfinishedplayers'][$i]['NewTR'] = "</tr><tr>";
						else
							$content['unfinishedplayers'][$i]['NewTR'] = "";
					}
				}
				// --- 



				// --- Get Round Actions
				$sqlquery = "SELECT " .
									STATS_ROUNDACTIONS . ".PLAYERID, " . 
//									STATS_ROUNDACTIONS . ".Team, " . 
									" sum(" . STATS_ROUNDACTIONS . ".Count) as Count, " . 
									STATS_GAMEACTIONS . ".NAME , " . 
									STATS_GAMEACTIONS . ".DisplayName " . 
									" FROM " . STATS_ROUNDACTIONS . 
									" INNER JOIN (" . STATS_GAMEACTIONS . 
									") ON (" . 
									STATS_GAMEACTIONS . ".ID=" . STATS_ROUNDACTIONS . ".ACTIONID) " . 
									" WHERE " . STATS_ROUNDACTIONS . ".ROUNDID = " . $content['roundid'] . " " . 
									GetCustomServerWhereQuery(STATS_ROUNDACTIONS, false) . 
									GetBannedPlayerWhereQuery(STATS_ROUNDACTIONS, "PLAYERID", false) . 
									" GROUP BY " . STATS_ROUNDACTIONS . ".PLAYERID " . 
									", " . STATS_GAMEACTIONS . ".NAME " . 
									" ORDER BY " . STATS_GAMEACTIONS . ".NAME, Count DESC ";
				$result = DB_Query($sqlquery);
				$gameactions = DB_GetAllRows($result, true);
				if ( isset($gameactions) )
				{
					// Enable!
					$content['gameactions_enabled'] = "true";

					// Extend PlayerAliases
					FindAndFillTopAliases($gameactions, "PLAYERID", "Alias", "AliasAsHtml" );

					$iActions = 0;
					$iPlayers = 0;
					for($i = 0; $i < count($gameactions); $i++)
					{
						// Set New Action Name
						if ( !isset($content['gameactions'][$iActions]['NAME']) )
						{
							// Set first date
							$content['gameactions'][$iActions]['NAME'] = $gameactions[$i]['NAME']; 
							if ( isset($gameactions[$i]['DisplayName']) && strlen($gameactions[$i]['DisplayName']) > 0 )
								$content['gameactions'][$iActions]['DisplayName'] = $gameactions[$i]['DisplayName']; 
							else
								$content['gameactions'][$iActions]['DisplayName'] = $gameactions[$i]['NAME']; 

							// Reset Players
//							$iPlayers = 0;
						}
						else if ( $content['gameactions'][$iActions]['NAME'] != $gameactions[$i]['NAME'] )
						{
							$iActions++;

							// Set new Action
							$content['gameactions'][$iActions]['NAME'] = $gameactions[$i]['NAME']; 
							if ( isset($gameactions[$i]['DisplayName']) && strlen($gameactions[$i]['DisplayName']) > 0 )
								$content['gameactions'][$iActions]['DisplayName'] = $gameactions[$i]['DisplayName']; 
							else
								$content['gameactions'][$iActions]['DisplayName'] = $gameactions[$i]['NAME']; 
							
							// Reset Players
							$iPlayers = 0;
						}

						// Set RowSpanCount
						$content['gameactions'][$iActions]['RowSpanCount'] = $iPlayers+2;

						// Copy Round Entry
						$content['gameactions'][$iActions]['myplayers'][$iPlayers] = $gameactions[$i];

						// Next Player
						$iPlayers++;
					}


					for($i = 0; $i < count($content['gameactions']); $i++)
					{
						// --- Set CSS Class
						if ( $i % 2 == 0 )
							$content['gameactions'][$i]['cssclass'] = "line0";
						else
							$content['gameactions'][$i]['cssclass'] = "line1";
						// --- 

						// --- Set Number
						$content['gameactions'][$i]['Number'] = $i+1;
						// ---
					}
				}
				// --- 
			}


			// --- Now creat awards
			foreach( $AllPlayers as $myplayer )
			{
				if ( !isset($content['Awards'][0]) )
					$content['Awards'][0] = $myplayer;
				else
				{
					// Higher = New runner ;)!
					if ( $content['Awards'][0]['TotalKills'] < $myplayer['TotalKills'] )
						$content['Awards'][0] = $myplayer;
				}

				if ( !isset($content['Awards'][1]) )
					$content['Awards'][1] = $myplayer;
				else
				{
					// Higher = New runner ;)!
					if ( $content['Awards'][1]['TotalKilled'] < $myplayer['TotalKilled'] )
						$content['Awards'][1] = $myplayer;
				}

			}
			$content['Awards'][0]['DisplayName'] = "Most Kills";
			$content['Awards'][0]['MedalImage'] = "images/medals/thumbs/medal_pro_killer.png"; // Medal_Kills.png";
			$content['Awards'][0]['ValueTitel'] = $content['LN_TOPPLAY_KILLS'];
			$content['Awards'][0]['Value'] = $content['Awards'][0]['TotalKills'];

			$content['Awards'][1]['DisplayName'] = "Most Deaths";
			$content['Awards'][1]['MedalImage'] = "images/medals/thumbs/medal_anti_no1target.png"; // Medal_Deaths.png";
			$content['Awards'][1]['ValueTitel'] = $content['LN_TOPPLAY_Deaths'];
			$content['Awards'][1]['Value'] = $content['Awards'][1]['TotalKilled'];

			for($i = 0; $i < count($content['Awards']); $i++)
			{
				// --- Set CSS Class
				if ( $i % 2 == 0 )
					$content['Awards'][$i]['cssclass'] = "line0";
				else
					$content['Awards'][$i]['cssclass'] = "line1";
				// --- 
			}
			// --- 

			// --- Set Mapimage
			$content['MapNameFile'] = $gl_root_path . "images/maps/small/" . $content['MapName'] . ".jpg";
			if ( !is_file($content['MapNameFile']) )
				$content['MapNameFile'] = $gl_root_path . "images/maps/no-pic.jpg";
			// --- 

			// --- Set Gametype Details
			if ( strlen($content['GameTypeDisplayName']) <= 0 )
				$content['GameTypeDisplayName'] = $content['GameTypeName'];
			// --- 

			// --- Set Display Time
			$content['TimePlayed'] = date('Y-m-d H:i:s', $roundvars['TIMEADDED']);
			// --- 

			// --- Set DurationTime
			$content['DurationTime'] = GetTimeString( $roundvars['ROUNDDURATION'] );
			// --- 

		}
		else
			$content['iserror'] = "true";
			
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
$page -> parser($content, "rounds-detail.html");
$page -> output(); 
// --- 


// --- Helper functions
function GetRoundPlayerDetails( $excludemyguids )
{
	global $content;

	// Set GUID Filter
	if ( isset($excludemyguids) && strlen($excludemyguids) > 0 )
		$sqlqueryexludeguids = " AND " . STATS_PLAYER_KILLS . ".PLAYERID NOT IN (" . $excludemyguids . ")";
	else
		$sqlqueryexludeguids = "";
	
	// Get Stats for Players WITH Kills!
	$sqlquery = "SELECT " .
						"sum( " . STATS_PLAYER_KILLS . ".Kills) as TotalKills, " . 
						STATS_PLAYER_KILLS . ".PLAYERID " . 
						" FROM " . STATS_PLAYER_KILLS . 
						" WHERE " . STATS_PLAYER_KILLS . ".ROUNDID=" . $content['roundid'] . " " . 
						$sqlqueryexludeguids . 
						GetBannedPlayerWhereQuery(STATS_PLAYER_KILLS, "PLAYERID", false) . 
						" GROUP BY " . STATS_PLAYER_KILLS . ".PLAYERID " .
						" ORDER BY TotalKills DESC ";
	$result = DB_Query($sqlquery);
	$myarray = DB_GetAllRows($result, true);

	// Init excludeKillerGuids variable
	$excludeKillerGuids = "";

	if ( isset($myarray) )
	{
		$iArrCount = count($myarray);
		for($i = 0; $i < $iArrCount; $i++)
		{
			// Append to excludeKillerGuids
			if ( ($i+1) == $iArrCount ) 
				$excludeKillerGuids .= $myarray[$i]['PLAYERID']; 
			else
				$excludeKillerGuids .= $myarray[$i]['PLAYERID'] . ", "; 

			// --- Set CSS Class
			if ( $i % 2 == 0 )
				$myarray[$i]['cssclass'] = "line_alt0";
			else
				$myarray[$i]['cssclass'] = "line_alt1";
			// --- 

			// --- Set Number
			$myarray[$i]['Number'] = $i+1;
			// ---

			// --- Set TotalKill Default
			if ( !isset($myarray[$i]['TotalKills']) )
				$myarray[$i]['TotalKills'] = 0;
			// ---
		}

		// --- Now we get all players who don't have kills! 
	}

	// Set new GUID Filter
	if ( isset($excludemyguids) && strlen($excludemyguids) > 0)
	{
		if ( isset($excludeKillerGuids) && strlen($excludeKillerGuids) > 0 ) 
			$sqlqueryexludeguids = " AND " . STATS_TIME . ".PLAYERID NOT IN (" . $excludemyguids . ", " . $excludeKillerGuids . " )";
		else
			$sqlqueryexludeguids = " AND " . STATS_TIME . ".PLAYERID NOT IN (" . $excludemyguids . " )";
	}
	else if ( strlen($excludeKillerGuids) > 0 )
		$sqlqueryexludeguids = " AND " . STATS_TIME . ".PLAYERID NOT IN (" . $excludeKillerGuids . ")";
	else
		$sqlqueryexludeguids = "";

	$sqlquery = "SELECT " .
						STATS_TIME . ".PLAYERID " . 
						" FROM " . STATS_TIME . 
						" WHERE " . STATS_TIME . ".ROUNDID=" . $content['roundid'] . " " . 
						$sqlqueryexludeguids . 
						GetBannedPlayerWhereQuery(STATS_TIME, "PLAYERID", false); 
// DEBUG echo $sqlquery;
	$result = DB_Query($sqlquery);
	$myNoobPlayers = DB_GetAllRows($result, true);

	if ( isset($myNoobPlayers) )
	{
		$iArrCount = count($myNoobPlayers);
		for($i = 0; $i < $iArrCount; $i++)
		{
			// Set Current ArrayNum!
			$iPlayerArrCount = count($myarray);

			// --- Set TotalKill Default
			$myNoobPlayers[$i]['TotalKills'] = 0;
			// ---

			// --- Set CSS Class
			if ( $iPlayerArrCount % 2 == 0 )
				$myNoobPlayers[$i]['cssclass'] = "line_alt0";
			else
				$myNoobPlayers[$i]['cssclass'] = "line_alt1";
			// --- 

			// --- Set Number
			$myNoobPlayers[$i]['Number'] = $iPlayerArrCount+1;
			// ---

			// Append to $myarray
			$myarray[$iPlayerArrCount] = $myNoobPlayers[$i];
		}
	}

	// Last step is to extend PlayerAliases
	FindAndFillTopAliases($myarray, "PLAYERID", "Alias", "AliasAsHtml" );

	return $myarray;

//	}
//	else
//		return;
}

function GetPlayerDMDetails ()
{
	global $content;

	// Get Player Stats from DB
/*	$sqlquery = "SELECT " .
						"sum( " . STATS_PLAYER_KILLS . ".Kills) as TotalKills, " . 
						STATS_ALIASES . ".PLAYERID, " . 
						STATS_ALIASES . ".Alias, " . 
						STATS_ALIASES . ".AliasAsHtml " .
						" FROM " . STATS_ALIASES . 
						" INNER JOIN (" . STATS_PLAYER_KILLS . ", " . STATS_ROUNDS .  
						") ON (" . 
						STATS_ALIASES . ".PLAYERID=" . STATS_PLAYER_KILLS . ".PLAYERID AND " . 
						STATS_ROUNDS . ".ID=" . STATS_PLAYER_KILLS . ".ROUNDID)" . 
						" WHERE " . STATS_ROUNDS . ".ID=" . $content['roundid'] . " " . 
						" GROUP BY " . STATS_ALIASES . ".PLAYERID" . 
						" ORDER BY TotalKills DESC ";
*/
	$sqlquery = "SELECT " .
						"sum( " . STATS_PLAYER_KILLS . ".Kills) as TotalKills, " . 
						STATS_PLAYER_KILLS . ".PLAYERID " . 
						" FROM " . STATS_PLAYER_KILLS . 
						" WHERE " . STATS_PLAYER_KILLS . ".ROUNDID=" . $content['roundid'] . " " . 
						GetBannedPlayerWhereQuery(STATS_PLAYER_KILLS, "PLAYERID", false) . 
						" GROUP BY " . STATS_PLAYER_KILLS . ".PLAYERID" . 
						" ORDER BY TotalKills DESC ";

	$result = DB_Query($sqlquery);
	$myarray = DB_GetAllRows($result, true);

	if ( isset($myarray) )
	{
		// Extend PlayerAliases
		FindAndFillTopAliases($myarray, "PLAYERID", "Alias", "AliasAsHtml" );

		for($i = 0; $i < count($myarray); $i++)
		{
			// --- Set CSS Class
			if ( $i % 2 == 0 )
				$myarray[$i]['cssclass'] = "line0";
			else
				$myarray[$i]['cssclass'] = "line1";
			// --- 

			// --- Set Number
			$myarray[$i]['Number'] = $i+1;
			// ---

			// --- Set TotalKill Default
			if ( !isset($myarray[$i]['TotalKills']) )
				$myarray[$i]['TotalKills'] = 0;
			// ---
		}

		return $myarray;
	}
	else
		return;
}

function GetPlayerDetails ( $myguids )
{
	global $content;

	// Get Player Stats from DB
	$sqlquery = "SELECT " .
						"sum( " . STATS_PLAYER_KILLS . ".Kills) as TotalKills, " . 
						STATS_PLAYER_KILLS . ".PLAYERID " . 
						" FROM " . STATS_PLAYER_KILLS . 
						" WHERE " . STATS_PLAYER_KILLS . ".ROUNDID=" . $content['roundid'] . " " . 
						" AND " . STATS_PLAYER_KILLS . ".PLAYERID IN (" . $myguids . ")" . 
						GetBannedPlayerWhereQuery(STATS_PLAYER_KILLS, "PLAYERID", false) . 
						" GROUP BY " . STATS_PLAYER_KILLS . ".PLAYERID" . 
						" ORDER BY TotalKills DESC ";

/*						"sum( " . STATS_PLAYER_KILLS . ".Kills) as TotalKills, " . 
						STATS_ALIASES . ".PLAYERID, " . 
						STATS_ALIASES . ".Alias, " . 
						STATS_ALIASES . ".AliasAsHtml " .
						" FROM " . STATS_ALIASES . 
						" INNER JOIN (" . STATS_PLAYER_KILLS . ", " . STATS_ROUNDS .  
						") ON (" . 
						STATS_ALIASES . ".PLAYERID=" . STATS_PLAYER_KILLS . ".PLAYERID AND " . 
						STATS_ROUNDS . ".ID=" . STATS_PLAYER_KILLS . ".ROUNDID)" . 
						" WHERE " . STATS_ROUNDS . ".ID=" . $content['roundid'] . " " . 
						" AND " . STATS_PLAYER_KILLS . ".PLAYERID IN (" . $myguids . ")" . 
						" GROUP BY " . STATS_ALIASES . ".PLAYERID" . 
						" ORDER BY TotalKills DESC ";*/
	$result = DB_Query($sqlquery);
	$myarray = DB_GetAllRows($result, true);

	if ( isset($myarray) )
	{
		// Extend PlayerAliases
		FindAndFillTopAliases($myarray, "PLAYERID", "Alias", "AliasAsHtml" );

		for($i = 0; $i < count($myarray); $i++)
		{
			// --- Set CSS Class
			if ( $i % 2 == 0 )
				$myarray[$i]['cssclass'] = "line0";
			else
				$myarray[$i]['cssclass'] = "line1";
			// --- 

			// --- Set Number
			$myarray[$i]['Number'] = $i+1;
			// ---

			// --- Set TotalKill Default
			if ( !isset($myarray[$i]['TotalKills']) )
				$myarray[$i]['TotalKills'] = 0;
			// ---
			
		}

		return $myarray;
	}
	else
		return;
}

function ExtendPlayerData ( &$myplayers )
{
	global $content, $gl_root_path;

	for($i = 0; $i < count($myplayers); $i++)
	{
		if ( isset($playerguids) ) { $playerguids .= ", "; } else { $playerguids = ""; }
		$playerguids .= $myplayers[$i]['PLAYERID'];
	}


	// Get Player Stats from DB
	$sqlquery = "SELECT " .
						STATS_PLAYER_KILLS . ".ENEMYID , " . 
						"count( " . STATS_PLAYER_KILLS . ".PLAYERID ) as TotalKilled " . 
						" FROM " . STATS_PLAYER_KILLS . 
//							" WHERE " . STATS_PLAYER_KILLS . ".ENEMYID =" . $myplayers[$i]['PLAYERID'] . 
//							" AND " . STATS_PLAYER_KILLS . ".ROUNDID=" . $content['roundid'];
						" WHERE " . STATS_PLAYER_KILLS . ".ENEMYID IN (" . $playerguids . ")" . 
						" AND " . STATS_PLAYER_KILLS . ".ROUNDID=" . $content['roundid'] . 
						GetBannedPlayerWhereQuery(STATS_PLAYER_KILLS, "PLAYERID", false) . 
						" GROUP BY " . STATS_PLAYER_KILLS . ".ENEMYID";
	$result = DB_Query($sqlquery);
	$tmpvars = DB_GetAllRows($result, true);

	// Assign TotalKilled Values
	for($n = 0; $n < count($myplayers); $n++)
		$myplayers[$n]['TotalKilled'] = 0;

	if ( isset($tmpvars) )
	{
		// Loop through found kills
		for($i = 0; $i < count($tmpvars); $i++)
		{
			for($n = 0; $n < count($myplayers); $n++)
			{
				if ( $myplayers[$n]['PLAYERID'] == $tmpvars[$i]['ENEMYID'] )
					$myplayers[$n]['TotalKilled'] = $tmpvars[$i]['TotalKilled'];
			}
		}

		// --- Set MaxWidth in Pixel
		$content['MaxPixelWidth'] = 100;

		// --- Calc Ratio!
		for($n = 0; $n < count($myplayers); $n++)
		{
			if ( $myplayers[$n]['TotalKilled'] > 0 )
				$myplayers[$n]['KillRatio'] = round($myplayers[$n]['TotalKills'] / $myplayers[$n]['TotalKilled'], 2);
			else
				$myplayers[$n]['KillRatio'] = $myplayers[$n]['TotalKills'];

			if ( !isset($content['MyMaxKillRatio']) || $myplayers[$n]['KillRatio'] > $content['MyMaxKillRatio'] ) 
				$content['MyMaxKillRatio'] = $myplayers[$n]['KillRatio'];
		}
		// ---

		for($n = 0; $n < count($myplayers); $n++)
		{
			// --- Set KillRation Values and Bars
			$myplayers[$n]['BarImageKillRatioMinus'] = $gl_root_path . "images/bars/bar-small/red_middle_9.png";
			$myplayers[$n]['BarImageKillRatioPlus'] = $gl_root_path . "images/bars/bar-small/green_middle_9.png";

			if ( isset($content['MyMaxKillRatio']) )
			{
				// Now we set the Width of the images
				if ( $myplayers[$n]['KillRatio'] > 1 )
				{
					$myplayers[$n]['KillRatioWidthMinus'] = $content['MaxPixelWidth'];
					$myplayers[$n]['KillRatioWidthMinusText'] = "";
				}
				else
				{
					$myplayers[$n]['KillRatioWidthMinus'] = intval($myplayers[$n]['KillRatio'] * $content['MaxPixelWidth']);
					$myplayers[$n]['KillRatioWidthMinusText'] =  $myplayers[$n]['KillRatioWidthMinus'] . "% of 1:0 Ratio";;
				}

				if ( $myplayers[$n]['KillRatio'] < 1 )
				{
					$myplayers[$n]['KillRatioWidthPlus'] = "0";
					$myplayers[$n]['KillRatioWidthPlusText'] = "";
				}
				else
				{
					$myplayers[$n]['KillRatioWidthPlus'] = intval( ($myplayers[$n]['KillRatio'] / ($content['MyMaxKillRatio']/$content['MaxPixelWidth'])) );
					if ( $myplayers[$n]['KillRatioWidthPlus'] > 100 ) 
						$myplayers[$n]['KillRatioWidthPlus'] = 100;

					$myplayers[$n]['KillRatioWidthPlusText'] = $myplayers[$n]['KillRatioWidthPlus'] . "% of best Ratio (Which is " . $content['MyMaxKillRatio'] . ")";
				}
			}
			else
			{
				$myplayers[$n]['KillRatioWidthMinus'] = "0";
				$myplayers[$n]['KillRatioWidthPlus'] = "0";
			}
			// --- 
		}

	}
}

// ---
?>