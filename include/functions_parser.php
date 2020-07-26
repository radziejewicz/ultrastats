<?php
/*
	*********************************************************************
	* Copyright by Andre Lorbach | 2006!								*
	* -> www.ultrastats.org <-											*
	*																	*
	* Use this script at your own risk!									*
	* -----------------------------------------------------------------	*
	* Core Parser functions												*
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

// Some defaults vars
$gl_newlastline = 0;
$gl_linebuffer = "";

// SQL Counters
$SQL_UDPATE_Batch_Count = 0;								// Counter for the batched UPDATE statements
$SQL_UDPATE_Direct_Count = 0;								// Counter for direct UPDATE statements
$SQL_INSERT_Count = 0;										// Counter for direct INSERT statements
$SQL_SELECT_Count = 0;										// Counter for direct SELECT statements
// ---


/*
*	Function to reset the GetLastLogFile
*/
function GetLastLogFile( $overwritepasswd = "" )
{
	global $ParserStart, $myserver, $content, $nTransferType;
	global $RUNMODE;

	// Init Header
	PrintDebugInfoHeader();

	// Set StartTime
	$ParserStart = microtime_float();
	
	// Begin Output
	PrintHTMLDebugInfo( DEBUG_INFO, "FTP", "STARTING Logfile download for Server '" . $myserver['Name'] . "'" );

	// --- PreChecks
	if (	!isset( $myserver ) ||
			!isset( $myserver['ID'] ) || 
			intval( $myserver['ID'] ) <= 0 ||
			!isset( $myserver['GameLogLocation'] ) || 
			(strlen($myserver['GameLogLocation']) <= 0 ) )
	{
		// Error, we can not go on!
		PrintHTMLDebugInfo( DEBUG_ERROR, "FTP", "Error, invalid Server or logfile location specified!" );
		return;
	}

	// --- Now we get FTP URL!
	$result = DB_Query("SELECT ftppath FROM " . STATS_SERVERS . " WHERE id = " . $myserver['ID']);
	$rows = DB_GetAllRows($result, true);
	if ( isset($rows) )
	{
		// Full ftp String
		$fullftpstr = $rows[0]['ftppath'];

		// Set Transfertype
		if ( strpos($fullftpstr, "ftp://") !== false )
			$nTransferType = TRANSFERTYPE_FTP;
		else if ( strpos($fullftpstr, "scp://") !== false )
			$nTransferType = TRANSFERTYPE_SCP;
		else
		{
			//Error!
			PrintHTMLDebugInfo( DEBUG_ERROR, "FTP", "Error, invalid FTP Location specified!" );
			return;
		}

		// Parsing the FTP Path
		$ftpvars = ParseFtpValuesFromURL( $fullftpstr );
		$ftpserver		= $ftpvars['ftpserver'];
		$ftpport		= $ftpvars['ftpport'];
		$username		= $ftpvars['username'];
		$password		= $ftpvars['password'];
		$ftppath		= $ftpvars['ftppath'];
		$ftpfilename	= $ftpvars['ftpfilename'];

		if ( strlen($username) > 0 )
		{
			// Check if we want to override the password!
			if ( strlen($overwritepasswd) > 0 )
				$password = $overwritepasswd;
		}
		else
		{
			// No user or pass given
			$username = "anonymous";
			$password = "ultrastats@2win.xx";
		}

		// Get Password from form!
		if ( $RUNMODE == RUNMODE_WEBSERVER && isset($_POST['pwd']) )
			$password = $_POST['pwd'];

		// Dbg Info
		PrintHTMLDebugInfo( DEBUG_DEBUG, "Parser", 
							" transfermode=" . $nTransferType . 
							", server=" . $ftpserver . 
							", ftpport=" . $ftpport . 
							", ftppath=" . $ftppath . 
							", username=" . $username . 
							", password=" . $password . 
							", ftpfilename=" . $ftpfilename . 
							" for Server '" . $myserver['ID'] . "' ...");

		// Connect to server
		$connid = server_connect($ftpserver, $ftpport);
//		$connid = ftp_connect($ftpserver, $ftpport, FTP_TIMEOUT);

		if ($connid)
		{
			PrintHTMLDebugInfo( DEBUG_INFO, "Parser", "Connect to '" . $ftpserver . "' was successfull!");


			
			// Set the network timeout to 10 seconds
//			ftp_set_option($connid, FTP_TIMEOUT_SEC, 10);
//			if (@ftp_login($connid, $username, $password))
			if (@server_login($connid, $username, $password))
			{	
				//Successfully connected!
				PrintHTMLDebugInfo( DEBUG_INFO, "Parser", "Logged in as User '" . $username . "' successfull!");

				// Obtain local file size
				if ( is_file($myserver['GameLogLocation']) )
					$locallogfilesize = filesize($myserver['GameLogLocation']);
				else
					$locallogfilesize = 0;

				// --- If FTP continue with this ^^
				if ( $nTransferType == TRANSFERTYPE_FTP ) 
				{
					//PASSIVE MODE Check, If enabled, set passive mode!
					if ( $myserver['FTPPassiveMode'] == true ) 
					{
						ftp_pasv ($connid, true) ;

						//Changed to passive mode!
						PrintHTMLDebugInfo( DEBUG_INFO, "Parser", "Changed to passive mode");
					}

					if (ftp_chdir($connid, $ftppath)) 
					{
						PrintHTMLDebugInfo( DEBUG_INFO, "Parser", "Path changed to '" . ftp_pwd($connid) . "'");
						PrintHTMLDebugInfo( DEBUG_INFO, "Parser", "Download of " . $ftpfilename . " - resuming from filepos " . $locallogfilesize);
						
						// Get remote filesize
						$remotefilesize = ftp_size( $connid, $ftpfilename );

						// Check if remote logfile is smaller then the local one
						if ( $remotefilesize == -1 )
						{
							// Dbg Info
							PrintHTMLDebugInfo( DEBUG_ERROR, "Parser", "Remotelogfile " . $ftpfilename . " does not exists!!");
							return;
						}
						else if ( $remotefilesize < $locallogfilesize )
						{
							// Reset logfilesize
							$locallogfilesize = 0;

							// Create a backup of the old file (Make configureable) 
							copy( $myserver['GameLogLocation'], $myserver['GameLogLocation'] . ".bak" );

							// Dbg Info
							PrintHTMLDebugInfo( DEBUG_WARN, "Parser", "Remotelogfile is smaller then the local one - this will overwrite your existing logfile now!");
						}

						// This actually downloads
						$getresult = ftp_get($connid, $myserver['GameLogLocation'], $ftpfilename, FTP_BINARY, $locallogfilesize);

						// close the connection
						ftp_close($connid);

						// Clear Filecache!
						clearstatcache();

						// Dbg Info
						PrintHTMLDebugInfo( DEBUG_INFO, "Parser", "Download of " . $ftpfilename . " finished - New Filesize = " . filesize($myserver['GameLogLocation']) );
					} 
					else 
					{ 
						//Error!
						PrintHTMLDebugInfo( DEBUG_ERROR, "FTP", "Error, Couldn't change directory '" . $ftppath . "'" );
						return;
					}
				}
				else if ( $nTransferType == TRANSFERTYPE_SCP ) 
				{	// SCP Copy is easier!
					
					// Initialize SFTP subsystem
					$sftp = ssh2_sftp($connid);
					if ($sftp)
					{
						$szFileStr = "ssh2.sftp://$sftp/$ftppath" . $ftpfilename;
						PrintHTMLDebugInfo( DEBUG_INFO, "SCP", "Download of " . $ftppath . $ftpfilename . " - resuming from filepos " . $locallogfilesize);

						// Create stream handle to the file for reading!
						$streamIn = fopen($szFileStr, 'r');

						// Create local stream handle to the file for writing!
						$streamOut = fopen($myserver['GameLogLocation'], 'a+'); //w

						// Move Pointer to last known position ^^
						fseek($streamIn, $locallogfilesize);
						fseek($streamOut, SEEK_END);
						
						// Start: For Speed analysis
						$nTimeStart = microtime_float();

						// Start reading in (kb chunks ^^
						$iDownloadCounter = 0;
						while (!feof($streamIn)) 
						{
							// Read remote
							$szIn = fread($streamIn, 65536); // Use 64K Chunks

							// Debug Abort if smaller ^^
							if ( strlen($szIn) <= 0 )
								break;

							// write local
							if (fwrite($streamOut, $szIn) === FALSE)
							{
								PrintHTMLDebugInfo( DEBUG_ERROR, "SCP", "Cannot write to file ($filename)" );
								break;
							}

							$iDownloadCounter++;
							if ( $iDownloadCounter % 128 == 0 ) // 128 = 1MB, 32 == 256KB
							{
								// End: For Speed analysis
								$nTimeEnd = microtime_float();

								$nTimeDiff = $nTimeEnd - $nTimeStart;
								$nSpeedMbit = 1024 / $nTimeDiff; 

								PrintHTMLDebugInfo( DEBUG_INFO, "SCP", "Downloaded " . ($iDownloadCounter / 128 ) . " MB - Speed = " . $nSpeedMbit . " kb/s" );
								
								// Set new Start
								$nTimeStart = microtime_float();
							}

						}

						// Close streams
						fclose($streamIn);
						fclose($streamOut);

						// WTF OMFG HWO THE HELL TO CLOSE SSH? 

						// Clear Filecache!
						clearstatcache();

						// Debug Info!
						PrintHTMLDebugInfo( DEBUG_INFO, "SCP", "Download of " . $ftpfilename . " finished - New Filesize = " . filesize($myserver['GameLogLocation']) );
					}
					else
					{
						//Error!
						PrintHTMLDebugInfo( DEBUG_ERROR, "SCP", "Could not initialize SFTP subsystem." );
						return;
					}
				}
			}
			else
			{
				// Failed! Ask for password, show FORM!
				PrintPasswordRequest();
			}
		}
		else
		{
			//Error!
			PrintHTMLDebugInfo( DEBUG_ERROR, "FTP", "Error, invalid FTP Location specified!" );
			return;
		}
	}
	else
	{
		// Error, we can not go on!
		PrintHTMLDebugInfo( DEBUG_ERROR, "FTP", "Error, FTP Connection failed!" );
		return;
	}

	// Dbg
//	PrintHTMLDebugInfo( DEBUG_INFO, "Parser", "Resetted LastLine value for Server '" . $myserver['ID'] . "' ...");
}

// Wrapper to connect!
function server_connect($server, $port)
{
	global $nTransferType;

	// Go for the FTP Login!
	if ( $nTransferType == TRANSFERTYPE_FTP ) 
		$connid = ftp_connect($server, $port, FTP_TIMEOUT);
	else if ( $nTransferType == TRANSFERTYPE_SCP ) 
	    $connid = ssh2_connect($server, $port);
	
	// return connection id
	return $connid;
}

// Wrapper to connect!
function server_login($connid, $username, $password)
{
	global $nTransferType;

	// Go for the FTP Login!
	if ( $nTransferType == TRANSFERTYPE_FTP ) 
	{
		ftp_set_option($connid, FTP_TIMEOUT_SEC, 10);
		$res = ftp_login($connid, $username, $password);
	}
	else if ( $nTransferType == TRANSFERTYPE_SCP ) 
	{
		$res = ssh2_auth_password($connid, $username, $password);
	}
	
	// return resukt
	return $res;
}

/*
*	Function to reset the LastlogLine
*/
function ResetLastLine()
{
	global $ParserStart, $myserver, $content;
	global $RUNMODE;

	// Set StartTime
	$ParserStart = microtime_float();

	// --- PreChecks
	if (	!isset( $myserver ) ||
			!isset( $myserver['ID'] ) || 
			intval( $myserver['ID'] ) <= 0 )
	{
		// Error, we can not go on!
		PrintHTMLDebugInfo( DEBUG_ERROR, "Parser", "Error, invalid Server specified!" );
		return;
	}

	// --- Set the last FilePosition to 0 
	$result = DB_Query("UPDATE " . STATS_SERVERS . " SET LastLogLine = '0' WHERE ID = " . $myserver['ID']);
	DB_FreeQuery($result);

	// Dbg
	PrintHTMLDebugInfo( DEBUG_INFO, "Parser", "Resetted LastLine value for Server '" . $myserver['ID'] . "' ...");
}


/*
*	Function to delete a server including stats!
*/
function DeleteServer()
{
	global $ParserStart, $myserver, $content;
	global $RUNMODE;

	// Set StartTime
	$ParserStart = microtime_float();

	// --- PreChecks
	if (	!isset( $myserver ) ||
			!isset( $myserver['ID'] ) || 
			intval( $myserver['ID'] ) <= 0 )
	{
		// Error, we can not go on!
		PrintHTMLDebugInfo( DEBUG_ERROR, "Parser", "Error, invalid Server specified!" );
		return;
	}

	if ( !isset($_GET['verify']) || $_GET['verify'] != "yes" )
	{
		// Print form and return from function
		PrintSecureUserCheck( $content['LN_WARNINGDELETE'], $content['LN_DELETEYES'], $content['LN_DELETENO'], "delete" );
		return;
	}
	// ---

	// First of all delete the Server Stats!
	DeleteServerStats();

	// Dbg
	PrintHTMLDebugInfo( DEBUG_INFO, "Parser", "Deleting ServerentryID'" . $myserver['ID'] . "' ...");
	
	// do the delete!
	ProcessDeleteStatement( "DELETE FROM " . STATS_SERVERS . " WHERE ID = " . $myserver['ID'] );
}

/*
*	Function to delete the stats of a server
*/
function DeleteServerStats()
{
	global $ParserStart, $myserver;
	global $RUNMODE;

	// Init Header
	PrintDebugInfoHeader();

	// Set StartTime
	$ParserStart = microtime_float();

	// PreChecks
	if (	!isset( $myserver ) ||
			!isset( $myserver['ID'] ) || 
			intval( $myserver['ID'] ) <= 0 )
	{
		// Error, we can not go on!
		PrintHTMLDebugInfo( DEBUG_ERROR, "Parser", "Error, invalid Server specified!" );
		return;
	}

	// StartDbg
	PrintHTMLDebugInfo( DEBUG_INFO, "Parser", "Starting Delete process for Server '" . $myserver['ID'] . "' ...");
	
	// Init WHEREqury so we do not delete all!
	$wherequery = "WHERE SERVERID = " . $myserver['ID'];

	// --- Start the Delete process!
	
	ProcessDeleteStatement( "DELETE FROM " . STATS_ALIASES . " " . $wherequery );
	PrintHTMLDebugInfo( DEBUG_INFO, "Parser", "Deleted '" . GetRowsAffected() . "' Aliases ( '" . STATS_ALIASES . "' table ) ...");

	ProcessDeleteStatement( "DELETE FROM " . STATS_CHAT . " " . $wherequery );
	PrintHTMLDebugInfo( DEBUG_INFO, "Parser", "Deleted '" . GetRowsAffected() . "' Chatlogs ( '" . STATS_CHAT . "' table ) ...");

	ProcessDeleteStatement( "DELETE FROM " . STATS_PLAYER_KILLS . " " . $wherequery );
	PrintHTMLDebugInfo( DEBUG_INFO, "Parser", "Deleted '" . GetRowsAffected() . "' Playerkills ( '" . STATS_PLAYER_KILLS . "' table ) ...");

	ProcessDeleteStatement( "DELETE FROM " . STATS_PLAYERS . " " . $wherequery );
	PrintHTMLDebugInfo( DEBUG_INFO, "Parser", "Deleted '" . GetRowsAffected() . "' Players ( '" . STATS_PLAYERS . "' table ) ...");

	ProcessDeleteStatement( "DELETE FROM " . STATS_ROUNDS . " " . $wherequery );
	PrintHTMLDebugInfo( DEBUG_INFO, "Parser", "Deleted '" . GetRowsAffected() . "' Rounds ( '" . STATS_ROUNDS . "' table ) ...");

	ProcessDeleteStatement( "DELETE FROM " . STATS_ROUNDACTIONS . " " . $wherequery );
	PrintHTMLDebugInfo( DEBUG_INFO, "Parser", "Deleted '" . GetRowsAffected() . "' Roundactions ( '" . STATS_ROUNDACTIONS . "' table ) ...");

	ProcessDeleteStatement( "DELETE FROM " . STATS_TIME . " " . $wherequery );
	PrintHTMLDebugInfo( DEBUG_INFO, "Parser", "Deleted '" . GetRowsAffected() . "' Time ( '" . STATS_TIME . "' table ) ...");

	ProcessDeleteStatement( "DELETE FROM " . STATS_CONSOLIDATED . " " . $wherequery );
	PrintHTMLDebugInfo( DEBUG_INFO, "Parser", "Deleted '" . GetRowsAffected() . "' Consolidationed ( '" . STATS_CONSOLIDATED . "' table ) ...");

	ProcessDeleteStatement( "DELETE FROM " . STATS_PLAYERS_TOPALIASES . " " . $wherequery );
	PrintHTMLDebugInfo( DEBUG_INFO, "Parser", "Deleted '" . GetRowsAffected() . "' Consolidationed ( '" . STATS_PLAYERS_TOPALIASES . "' table ) ...");

	ProcessDeleteStatement( "DELETE FROM " . STATS_WEAPONS_PERSERVER . " " . $wherequery );
	PrintHTMLDebugInfo( DEBUG_INFO, "Parser", "Deleted '" . GetRowsAffected() . "' Consolidationed ( '" . STATS_WEAPONS_PERSERVER . "' table ) ...");
	
	// Also delete lastLogLine
	ResetLastLine();
	// ---
	
	PrintHTMLDebugInfo( DEBUG_INFO, "Parser", "Finished Delete process");
}

/*
*	Function to process the logs for a server
*/
function RunTotalStats()
{
	global $ParserStart;

	// Init Header
	PrintDebugInfoHeader();

	// Set StartTime
	$ParserStart = microtime_float();

	//Run the Medals Generation now!
	CreateAllMedals( -1 );
	RunServerConsolidation( -1 );

	//Run Calc for TOPAliases
	GenerateStrippedCodeAliases();

	//Run Calc for TOPAliases
	CreateTopAliases( -1 );
}

/*
*	Function to process the logs for a server
*/
function OptimizeAllTables()
{
	global $ParserStart;

	// Init Header
	PrintDebugInfoHeader();

	// Set StartTime
	$ParserStart = microtime_float();

	PrintHTMLDebugInfo( DEBUG_INFO, "OptimizeAllTables", "Starting SQL Table Optimation");
	
	// Create SQL Query
	$sqlquery = " OPTIMIZE TABLE " . 
		"`" . STATS_ALIASES . "`, " . 
		"`" . STATS_CHAT . "`, " . 
		"`" . STATS_CONFIG . "`, " . 
		"`" . STATS_CONSOLIDATED . "`, " . 
		"`" . STATS_GAMEACTIONS . "`, " . 
		"`" . STATS_DAMAGETYPES . "`, " . 
		"`" . STATS_GAMETYPES . "`, " . 
		"`" . STATS_HITLOCATIONS . "`, " . 
		"`" . STATS_LANGUAGE_STRINGS . "`, " . 
		"`" . STATS_MAPS . "`, " . 
		"`" . STATS_PLAYER_KILLS . "`, " . 
		"`" . STATS_PLAYERS . "`, " . 
		"`" . STATS_ROUNDS . "`, " . 
		"`" . STATS_ROUNDACTIONS . "`, " . 
		"`" . STATS_SERVERS . "`, " . 
		"`" . STATS_TIME . "`, " . 
		"`" . STATS_USERS . "`, " . 
		"`" . STATS_WEAPONS . "`, " . 
		"`" . STATS_PLAYERS_STATIC . "`, " . 
		"`" . STATS_PLAYERS_TOPALIASES . "`, " . 
		"`" . STATS_WEAPONS_PERSERVER . "` "; 

	$result = DB_Query($sqlquery);
	$sqllines = DB_GetAllRows($result, true);

	// For the eye
	for($i = 0; $i < count($sqllines); $i++)
	{
		$tmpvar = implode(", ", $sqllines[$i]);
		PrintHTMLDebugInfo( DEBUG_INFO, "OptimizeAllTables", $tmpvar);
	}

	PrintHTMLDebugInfo( DEBUG_INFO, "OptimizeAllTables", "Finished SQL Table Optimation");
}

/*
*	Function to process the logs for a server
*/
function RunParserNow()
{
	global $gl_newlastline, $gl_linebuffer, $ParserStart, $myserver;
	global $SQL_UDPATE_Batch_Count, $SQL_UDPATE_Direct_Count, $SQL_INSERT_Count, $SQL_SELECT_Count;
	global $RUNMODE, $MaxExecutionTime;

	// Init Header
	PrintDebugInfoHeader();

	// Set StartTime
	$ParserStart = microtime_float();

	// PreChecks
	if (	!isset( $myserver ) ||
			!isset( $myserver['ID'] ) || 
			intval( $myserver['ID'] ) <= 0 )
	{
		// Error, we can not go on!
		PrintHTMLDebugInfo( DEBUG_ERROR, "Parser", "Error, invalid Server specified!" );
		return;
	}

	/* moved to helpers 
	if ($RUNMODE == RUNMODE_WEBSERVER)
	{
		// Max Execution time
		set_time_limit( 120 );									// Extend Execution Time
		$MaxExecutionTime = ini_get("max_execution_time") - 10; // -10 Seconds to be on the save side and have enough time  to finish the site!
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Gamelog", "MaxExecutionTime = $MaxExecutionTime");
	}
	else
	{
		// Unlimited
		set_time_limit( 0 );
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Gamelog", "Console Mode, unlimited Execution TIME ");
	}*/

	// Some defaults
	$gl_newlastline = 0;
	$gl_linebuffer = "";
	$currentseconds = 0;
	$gl_totallogtimesecs = 0;			// The total time of the whole log!

	// StartDbg
	PrintHTMLDebugInfo( DEBUG_INFO, "Gamelog", "Starting Parser...");
	
	// Get LastLogLine Value
	$db_lastlogline = GetLastLogLine( $myserver['ID'] );
	PrintHTMLDebugInfo( DEBUG_INFO, "Gamelog", "Last parsed Line was " . $db_lastlogline);

	// --- First Loop - Get linecount
	$myhandle = fopen( $myserver['GameLogLocation'], "r");
	if ($myhandle)
	{
		if (feof ($myhandle)) 
			PrintHTMLDebugInfo( DEBUG_WARN, "Gamelog", "Error, file is empty " . $myserver['GameLogLocation'] );

		PrintHTMLDebugInfo( DEBUG_INFO, "Gamelog", "Opening for counting the lines..." );

		while (!feof ($myhandle))
		{
			$gl_linebuffer = fgets($myhandle, 1024);
			$gl_newlastline++;
			
			// We parse new logs only and we ignore empty lines and lines with less chars then 5!
			if ( $gl_newlastline > $db_lastlogline && strlen($gl_linebuffer) > 5 )
			{
				// --- BEGIN TimeHandling
				// Get the seconds from the logline
				$lastseconds = $currentseconds;
				$currentseconds = GetSecondsFromLogLine( $gl_linebuffer );

				if ( !isset($initseconds) )
				{	// First entry
					$initseconds = $currentseconds;
				}
				else
				{
					// Server was restarted and new data is available
					if ( $currentseconds < $lastseconds )
					{
						// Add to global time
						$gl_totallogtimesecs += ($lastseconds - $initseconds);

						// reinit initseconds!
						$initseconds = $currentseconds;
					}
				}
				// --- END TimeHandling
			}
		}

		fclose($myhandle);
		PrintHTMLDebugInfo( DEBUG_INFO, "Gamelog", "has $gl_newlastline lines ");
		PrintHTMLDebugInfo( DEBUG_DEBUG, "Gamelog", "Total Time player in the logfile is '" . $gl_totallogtimesecs . "' seconds");
		PrintHTMLDebugInfo( DEBUG_INFO, "Gamelog", "Closing ..." );

		if ($db_lastlogline == 0)
			PrintHTMLDebugInfo( DEBUG_INFO, "Gamelog", "First time, processing whole Gamelogfile, this could take some time...");
		elseif ($gl_newlastline == $db_lastlogline)
		{	
			// Nothing new
			PrintHTMLDebugInfo( DEBUG_INFO, "Gamelog", "NOTHING changed since last time...");
			return;
		}
		elseif ($gl_newlastline < $db_lastlogline)
		{	
			// logfile is smaller then before, start from beginning
			PrintHTMLDebugInfo( DEBUG_WARN, "Gamelog", "Logfile is smaller then last time, start processing from beginning...");
			$db_lastlogline = 0;
		}
	}
	else
	{
		PrintHTMLDebugInfo( DEBUG_ERROR, "Gamelog", "Could not open the game logfile ".$CFG['Gamelogfile']." - Check File name and path");
		return;
	}

	// Get the last file modification time
	$gl_logfiletimemod = filemtime( $myserver['GameLogLocation'] );

	// Copy Last line of the file
	$gl_MaxLineCount = $gl_newlastline;
	// ---

	// --- Second Loop, processing Round by round now!
	$myhandle = fopen( $myserver['GameLogLocation'], "r");
	if ($myhandle)
	{
		// --- Init some vars
		$currentgametype = "";
		$CurrentGame[] = "";
		$currentline = 0;
		$arrayline = 0;
		$initgameseconds = 0;			// Used to compare
		$currentseconds = -1;			// Reinit | Update! Changed to -1, because otherwise the first round could have been skipped
		$currenttotalseconds = 0;		// Needed to determine the time the round started

		// 0 means search for "InitGame:"
		// 1 means search for "ShutdownGame:" to get a whole game and then process it!
		$findmode = 0;
		// --- 
		
		if (feof ($myhandle)) 
			PrintHTMLDebugInfo( DEBUG_WARN, "Gamelog", "Error, file is empty " . $myserver['GameLogLocation'] );
		
		PrintHTMLDebugInfo( DEBUG_INFO, "Gamelog", "Opening Gamelogfile for parsing...");

		while (!feof ($myhandle))
		{
			// A logline was never more then 1024 bytes, so it's enough buffer
			$gl_linebuffer = fgets($myhandle, 1024);

			if ( $currentline < $db_lastlogline )
			{
				// Repeat until new file position is reached
				$currentline++;
			}
			else
			{
				// --- NEW, we check if the line is valid! Should at least has 5 chars to be valid!
				if ( strlen( $gl_linebuffer ) > 5 )
				{
					// --- BEGIN TimeHandling
					// Get the seconds from the logline
					$lastseconds = $currentseconds;
					$currentseconds = GetSecondsFromLogLine( $gl_linebuffer );

					// Add to current running total time
					if ( $currentseconds > $lastseconds )
						$currenttotalseconds += ($currentseconds - $lastseconds );
					else if ( $currentseconds < $lastseconds )
					{
						//LogTime was less then befor, then the server was restarted 
						$logfilerestart = true;
						PrintHTMLDebugInfo( DEBUG_WARN, "Gamelog", "Attention, gamelogtime was restarted from " . $lastseconds . " seconds to " . $currentseconds . " seconds");
					}
					else
					{
						// Nothing to handle yet
					}
					// --- END TimeHandling

					if ( $currentseconds != -1 ) // Init starts at -1
					{
						if ( ($findmode == 0) && (preg_match ("/InitGame:/", $gl_linebuffer)) )
						{
							$findmode = 1;	// From here start copying the game session into the buffer
							PrintHTMLDebugInfo( DEBUG_DEBUG, "Gamelog", "Gameround found at Line $currentline");

							// Store the seconds for comparison
							$initgameseconds = $currentseconds;

							// Important for further handling, get the current Gametype!
							$currentgametype = GetGametypeFromInitGame($gl_linebuffer);
							
                            // BEGIN TimeMod, thx to Ramirez!
							$custserverstarttime = GetCustomServerStartTime($gl_linebuffer);
							// END TimeMod

							// Importent, copy the first line here manually! Otherwise InitGame will be missing!
							$CurrentGame[$arrayline] = $gl_linebuffer;
							$arrayline++;
						}
						elseif ($findmode == 1)
						{
							/*	Ok Cod2 isn't so clean in shutdown and Initgame as I thought before. 
							*	From my logfile analysis, the logging can be different from gametype to gametype. 
							*	
							*	$lastseconds > $currentseconds 
								= The GameLogFile proberly was corrupted, or the Server crashed. For us the Round ends here!
							*	(preg_match("/ShutdownGame:/", $gl_linebuffer) && $lastseconds == $currentseconds)
								= If the Init and ExitSeconds are the same, the "ExitLevel: executed" is missing so we also quit the round here. 
							*	(preg_match ("/ExitLevel: executed/", $gl_linebuffer))
								= ExitLevel is a RoundFinish in ANY case!
							*	($currentgametype == "dm" || $currentgametype == "tdm") && (preg_match ("/ShutdownGame:/", $gl_linebuffer))
								= If we reach this, it proberly was an "Unclean mapshutdown" - anyway, the session ends here
							*	( isset($roundfilerestart) && $roundfilerestart == true )
								= If a server is restarted, we need to finish the session exactly HERE as well ;)!
							*		
							*/

							if (	($lastseconds > $currentseconds) ||
									(preg_match("/ShutdownGame:/", $gl_linebuffer) && $lastseconds == $currentseconds) ||
									(preg_match ("/ExitLevel: executed/", $gl_linebuffer)) || 
									(
										($currentgametype == "dm" || $currentgametype == "tdm") &&
										(preg_match ("/ShutdownGame:/", $gl_linebuffer))
									) ||
									( isset($logfilerestart) && $logfilerestart == true )
								)
							{
								$findmode = 0;								// Set findmode back to 0
								$gl_newlastline = $currentline;				// Ser lastline counter only if a complete game was found!
								
								// BEGIN TimeMod, thx to Ramirez!
								if ( strlen($custserverstarttime) > 0 ) 
									$realstarttime = strtotime($custserverstarttime) + GetSecondsFromLogLine( $gl_linebuffer );
								else
								{
									// Needed to find the time when the round started
									$realstarttime = $gl_logfiletimemod	- ( $gl_totallogtimesecs - $currenttotalseconds );
								}
								// END TimeMod

								// Now process the Round
								$processingtime = ProcessGameRound($CurrentGame, $realstarttime);			
								PrintHTMLDebugInfo( DEBUG_DEBUG, "Gamelog", "Round ended at $currentline and has been processed...");

								// Delete array
								unset($CurrentGame);						

								// Write FileCounter into database
								SetLastLogLine($myserver['ID'], $gl_newlastline);

								// Keep user informed where processing is
								PrintHTMLDebugInfo( DEBUG_INFO, "Gamelog", "Processed in $processingtime | " . 
																			" $gl_newlastline lines of $gl_MaxLineCount | " .
																			$SQL_SELECT_Count . ", " . $SQL_INSERT_Count. ", " .
																			($SQL_UDPATE_Direct_Count+$SQL_UDPATE_Batch_Count) . "\tSEL,INS,UPT");
								
								//Disable special logfile restart mode
								if ( isset($logfilerestart) && $logfilerestart == true )
									$logfilerestart = false;

								// --- If we run in Webserver Mode, we need to check for the Scripttimeout!
								if ($RUNMODE == RUNMODE_WEBSERVER)
								{
									//Flush php output
									flush();

									//Check for script timeout
									if ( ( microtime_float() - $ParserStart) > $MaxExecutionTime)
									{
										print ('<br><center><B>Timelimit hit (' . $MaxExecutionTime . ' seconds).</B><br>
												Please click <B><a href="' . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'] . '">here</A></B> to resume the update process.
												This site will automatically reload in 5 seconds.<br></center>
												<script language="Javascript">function reload() { location = "' . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'] . '"; } setTimeout("reload()", 5000);</script>');

										// Run an update of the LastUpdate Time
										SetLastUpdateTime( $myserver['ID'] );

										// Return from the function
										return;
									}
								}
								// --- 

								// Debug Break
		//						return;
							}
							else
							{
								$CurrentGame[$arrayline] = $gl_linebuffer;
								$arrayline++;
							}
						}
					}
				}
				else
				{
					if ( strlen($gl_linebuffer) > 1 )
						PrintHTMLDebugInfo( DEBUG_WARN, "Gamelog", "Logline was to short: '". $gl_linebuffer . "'");
					else	// Only debug then
						PrintHTMLDebugInfo( DEBUG_DEBUG, "Gamelog", "Logline was to short: '". $gl_linebuffer . "'");

				}

				// Increment current linecounter
				$currentline++;
			}
		}
		
		//Finished
		PrintHTMLDebugInfo( DEBUG_INFO, "Gamelog", "GameLogFile '". $myserver['GameLogLocation'] . "' has been fully parsed");

		//Run the Medals Generation now!
		CreateAllMedals( $myserver['ID'] );
		RunServerConsolidation( $myserver['ID'] );

		//Run Calc for TOPAliases
		CreateTopAliases( $myserver['ID'] );

		// --- Again process queued Update Statements
		ProcessQueuedUpdateStatement();
		// --- 

		// Run an update of the LastUpdate Time
		SetLastUpdateTime( $myserver['ID'] );
	}
	else
	{
		PrintHTMLDebugInfo( DEBUG_ERROR, "Gamelog", "Could not open the game logfile ".  $myserver['GameLogLocation'] . " for parsing - Check File name and path");
		return;
	}
	// ---
}

function ProcessGameRound($myRoundArray, $myrealstarttime)
{
	global $content, $myPlayers, $myRound, $myKills, $gl_newlastline;
	global $SQL_UDPATE_Batch_Count, $SQL_UDPATE_Direct_Count, $SQL_INSERT_Count, $SQL_SELECT_Count;

	// Reset Counters
	$SQL_UDPATE_Batch_Count = 0;
	$SQL_UDPATE_Direct_Count = 0;								
	$SQL_INSERT_Count = 0;
	$SQL_SELECT_Count = 0;
	
	// ReInit Values					
	unset ($myPlayers);
	unset ($myRound[ROUND_ALLIES_GUIDS]);		// why the fuck unset it as well? 
	unset ($myRound[ROUND_AXIS_GUIDS]);			// why the fuck unset it as well? 
	unset ($myRound);
	unset ($myKills);							

	// --- Experimental, lock all needed tables: 
	PrintHTMLDebugInfo( DEBUG_DEBUG, "ProcessGameRound", "Locking Database Tables...");
	DB_Exec( "LOCK TABLES " . STATS_ALIASES . ", " . STATS_CHAT . ", " . STATS_GAMEACTIONS . ", " . 
							  STATS_PLAYER_KILLS . ", " . STATS_PLAYERS . ", " . STATS_ROUNDS . ", " . 
							  STATS_ROUNDACTIONS . ", " . STATS_TIME . " WRITE" );
	// ---

	// Get processing Starttime
	$ProcessedTime_start = microtime_float();

	// Local variables
	$myLogLineCounter	= 0;
	$timeroundend = $timeroundbegin = 0;
	$initgame = false;

	// Debug the Round StartTime
	PrintHTMLDebugInfo( DEBUG_DEBUG, "ProcessGameRound", "Round processing started - Round was played at '" . date('Y-m-d h:i:s', $myrealstarttime) . "'");

	foreach( $myRoundArray as $mybuffer )
	{
		if ( CheckLogLine($mybuffer) )
		{
			// DebugLogPrint
			PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "ProcessGameRound", "Parsing Logline " . ($gl_newlastline+$myLogLineCounter) . ": '" . SplitTimeFromLogLine($mybuffer) . "'");

			// --- Now the processing starts
			if (preg_match ("/InitGame:/", $mybuffer) && $initgame == false)	// Only do first time
			{
				// DebugLogPrint
				PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "ProcessGameRound", "Parsing InitGame Logline");

				// This is the BeginTime
				$timeroundend = $timeroundbegin = GetSecondsFromLogLine( $mybuffer );

				// Create the Round and some variables
				Parser_RoundInit( $myrealstarttime, SplitTimeFromLogLine($mybuffer) );

				// Set INIT true to avoid reoccurence
				$initgame = true;
			}
			else
			{
				// We set the timeend each logfile 
				$timeroundend = GetSecondsFromLogLine( $mybuffer );

				// Split the time from the logfile
				$myLogArray = explode(";", SplitTimeFromLogLine($mybuffer) );

				switch ( $myLogArray[PARSER_TYPE] )
				{
					case "J":	// Join: 592:07 J;185269;5;^2|OCG|^1UnDead
						Parser_AddPlayer($myLogArray);
						break;
					case "Q":	// Quit: 592:49 Q;185269;5;^2|OCG|^1UnDead
						$timelasted = $timeroundend - $timeroundbegin;
						if ( $timelasted <= 0 )
						{
							PrintHTMLDebugInfo( DEBUG_ERROR_WTF, "ProcessGameRound", "NEGATIV Time for RemovePlayer returned! - " . $timeroundend . " - " . $timeroundbegin . " logline = '" . $mybuffer . "'");
							break;
						}
						Parser_RemovePlayer($myLogArray, $timeroundend - $timeroundbegin);
						break;
					case "say":		// Chat: 59:22 say;14352;25;|OCG|Anarchy; No we're not atm
					case "sayteam":	// Chat: 62:34 sayteam;14352;25;|OCG|Anarchy; take it south
						Parser_AddChatLine($myLogArray);
						break;
					case "K":	// Kill: 776:47 K;185269;7;allies;^2|OCG|^1UnDead;186276;8;axis;^2|OCG|^9CerealKilla;mp40_mp;135;MOD_HEAD_SHOT;head
						Parser_AddKillAndDeath($myLogArray);
						break;
					case "D":	// Damage: 777:42 D;185269;7;allies;^2|OCG|^1UnDead;186276;8;axis;^2|OCG|^9CerealKilla;mp44_mp;60;MOD_RIFLE_BULLET;neck
//						PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "ProcessGameRound", "Damage LogLine ignored");
						Parser_ProcessDamage($myLogArray);
						break;
					case "Weapon":	// Weapon Pickup: 1525:07 Weapon;360486;0;-=]E.Z.C[=-Delta|PsB;frag_grenade_german_mp
						// TODO: Well does this care? 
						PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "ProcessGameRound", "WeaponPickup LogLine ignored");
						break;
					case "W":	// Round Win: 782:28 W;axis;107521;^2|OCG|^4STINKYPETE;186276;^2|OCG|^9CerealKilla
						Parser_AddRoundWin($myLogArray);
						break;
					case "L":	// Round Loss: 782:28 L;allies;185269;^2|OCG|^1UnDead;104252;^2[OCG]^4Paulaner_Pils
						Parser_AddRoundLoss($myLogArray);
						break;
					case "A":	/*		Actions: 
									*	1620:56 A;0;3;allies;^3-=]E.Z.C[=-^1meLONE;bomb_plant
									*	1620:56 A;0;4;axis;^3-=]E.Z.C-T[=-^1ThorHal;bomb_defuse
									*	1620:56 A;0;11;axis;^1 Nehraje Dodge ale jeho kamar;Exploit abuser was suicided by server
									*	1111:32 A;0;4;allies;Highland Thunder;radio_capture
									*	1275:25 A;0;1;axis;ficku#41;radio_destroy
								*/
						// TODO: Maybe later we log this 
						Parser_AddRoundAction($myLogArray);
						break;
				}
			}
		}
		else
			PrintHTMLDebugInfo( DEBUG_ERROR, "ProcessGameRound", "Invalid LogFormat: '$mybuffer'");
		
		// Next line
		$myLogLineCounter++;
	}	
	// --- BEGIN Final End analysis
	
	// Create the Round and some variables
	PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "ProcessGameRound", "Roundtime: Roundend=$timeroundend - Roundbegin=$timeroundbegin - Roundseconds = " . ($timeroundend - $timeroundbegin) );
	Parser_FinalizeRound( $timeroundend - $timeroundbegin );
//exit;
	// --- END Final End analysis

//	sleep( 1 );

	// --- Experimental, unlock tables: 
	PrintHTMLDebugInfo( DEBUG_DEBUG, "ProcessGameRound", "Unlocking Database Tables...");
	DB_Exec( "UNLOCK TABLES" );
	// ---

	// Get processing Endtime
	return $ProcessedTime = number_format( microtime_float() - $ProcessedTime_start, 4, '.', '');
}

/*	----------------------------------------------------*/
/*	Function to add a Player into the current GameRound.
	SampleLogPrint: InitGame: \g_antilag\1\g_gametype\ctf\g_needpass\0\gamename\Call of Duty 2\mapname\mp_decoy\protocol\115\scr_friendlyfire\0\scr_killcam\0\shortversion\1.0\sv_allowAnonymous\0\sv_floodProtect\1\sv_hostname\^2|OCG|^1CTF ^324/7\sv_maxclients\36\sv_maxPing\200\sv_maxRate\18000\sv_minPing\0\sv_privateClients\4\sv_pure\1\sv_voice\0
*/
function Parser_RoundInit( $therealstarttime, $buffer )
{
	global $myRound, $myserver;
	PrintHTMLDebugInfo( DEBUG_DEBUG, "Parser_RoundInit", "Starting a new Round");

	// +11 Chars to remove the "InitGame: \"
	$myRound[ROUND_SERVERCVARS] = trim( substr($buffer, 11) );

	// Create tmp Servervar Array
	$tmparray = explode( "\\", $myRound[ROUND_SERVERCVARS] );
	for($i = 0; $i < count($tmparray); $i+=2)
		$gameinitarray[ DB_RemoveBadChars($tmparray[$i]) ] = DB_RemoveBadChars( $tmparray[$i+1] );

	// Set Values we know
	$myRound[ROUND_TIMESTAMP]	= $therealstarttime; 
	$myRound[ROUND_TIMEYEAR]	= intval( date("Y", $therealstarttime) );
	$myRound[ROUND_TIMEMONTH]	= intval( date("m", $therealstarttime) );

	$myRound[ROUND_GAMETYPE]	= DB_RemoveBadChars( $gameinitarray['g_gametype'] );
	$myRound[ROUND_MAPID]		= DB_RemoveBadChars( $gameinitarray['mapname'] );

	// Init Values!
	$myRound[ROUND_AXIS_WINS] = 0;
//	$myRound[ROUND_AXIS_GUIDS] = "";
	$myRound[ROUND_ALLIES_WINS] = 0;
//	$myRound[ROUND_ALLIES_GUIDS] = "";
	$myRound[ROUND_TOTALKILLS] = 0;

	// Now Insert into the Database
	$myRound[ROUND_DBID] = ProcessInsertStatement("INSERT INTO " . STATS_ROUNDS . " (SERVERID, TIMEADDED, ROUNDDURATION, GAMETYPE, MAPID, ServerCvars) VALUES (
		'" . $myserver['ID'] . "', 
		 " . $myRound[ROUND_TIMESTAMP] . ", 
		 -1, 
		'" . GetGameTypeByName( $myRound[ROUND_GAMETYPE] ) . "', 
		 " . GetMapIDByName( $myRound[ROUND_MAPID] ) . ", 
		'" . DB_RemoveParserSpecialBadChars($myRound[ROUND_SERVERCVARS]) . "'
		)");
}
/*	----------------------------------------------------*/

/*	----------------------------------------------------*/
/*	Function to add a Player into the current GameRound.
	SampleLogPrint: InitGame: \g_antilag\1\g_gametype\ctf\g_needpass\0\gamename\Call of Duty 2\mapname\mp_decoy\protocol\115\scr_friendlyfire\0\scr_killcam\0\shortversion\1.0\sv_allowAnonymous\0\sv_floodProtect\1\sv_hostname\^2|OCG|^1CTF ^324/7\sv_maxclients\36\sv_maxPing\200\sv_maxRate\18000\sv_minPing\0\sv_privateClients\4\sv_pure\1\sv_voice\0
*/ 
function Parser_FinalizeRound( $roundlastedtime )
{
	global $content, $myRound, $myPlayers, $myKills, $myserver;
	PrintHTMLDebugInfo( DEBUG_DEBUG, "Parser_FinalizeRound", "Finalizing the Round - TotalKills " . $myRound[ROUND_TOTALKILLS]);

	// Don't count 0-Time and KillRounds
	if ( $myRound[ROUND_TOTALKILLS] <= 0 || $roundlastedtime == 0 )
	{
		// No kill rounds are ignored and removed from the database
		ProcessDeleteStatement("DELETE FROM " . STATS_ROUNDS . " WHERE ID = " . $myRound[ROUND_DBID]);
	}
	else
	{
		if ( $myRound[ROUND_GAMETYPE] == "dm" )
		{
			// We write the winner into both Values, axis and allies
			$axisguids = $alliexguids = GetPlayerWithMostKills();
		}
		else
		{
			// Set Axis and Allies Guids
			if ( isset($myRound[ROUND_AXIS_GUIDS]) )
				$axisguids = implode(";", $myRound[ROUND_AXIS_GUIDS]);
			else
				$axisguids = GetGuidsFromPlayerArray("axis");
			if ( isset($myRound[ROUND_ALLIES_GUIDS]) )
				$alliexguids = implode(";", $myRound[ROUND_ALLIES_GUIDS]);
			else
				$alliexguids = GetGuidsFromPlayerArray("allies");

			// --- IW messed up again and just removed round finish loglines, so we Count at least TDM (WAR) ourself!
			if ( $content['gen_gameversion'] == COD4 && $myRound[ROUND_GAMETYPE] == "war" ) 
			{	
				$KillsAllies = 0;
				$KillsAxis = 0;
				foreach ( $myPlayers as $player )
				{
					if		( strlen($alliexguids) > 0 && strpos($alliexguids, $player[PLAYER_GUID]) !== false )
						$KillsAllies += $player[PLAYER_KILLS];
					if ( strlen($axisguids) > 0 && strpos($axisguids, $player[PLAYER_GUID]) !== false )
						$KillsAxis += $player[PLAYER_KILLS];
				}

				// Copy Wins back!
				$myRound[ROUND_AXIS_WINS] = $KillsAxis;
				$myRound[ROUND_ALLIES_WINS] = $KillsAllies;
			}
			// ---

			if ( strlen($axisguids) <= 0 )
				PrintHTMLDebugInfo( DEBUG_WARN, "Parser_FinalizeRound", "Axis Guids Empty! ");
			if ( strlen($alliexguids) <= 0 )
				PrintHTMLDebugInfo( DEBUG_WARN, "Parser_FinalizeRound", "Allies Guids Empty! ");
		}

		// Now Do the UpdateStatement
		ProcessUpdateStatement("UPDATE " . STATS_ROUNDS . " SET 
			ROUNDDURATION = $roundlastedtime, 
			AxisRoundWins = " . $myRound[ROUND_AXIS_WINS] . ", 
			AxisGuids = '" . $axisguids . "',
			AlliesRoundWins = " . $myRound[ROUND_ALLIES_WINS] . ", 
			AlliesGuids = '" . $alliexguids . "' 
			WHERE ID = " . $myRound[ROUND_DBID] );
	}

	// --- Now calc the queued Update Statements
	ProcessQueuedUpdateStatement();
	// --- 

	// --- Process all Kill Inserts at once here

	// First we build a large insert string
	if ( isset($myKills) && count($myKills) > 0 )
	{
		$strInsert = "INSERT INTO " . STATS_PLAYER_KILLS . " (SERVERID, ROUNDID, PLAYERID, ENEMYID, WEAPONID, DAMAGETYPEID, HITLOCATIONID, Kills) VALUES ";
		
		// Loop through all KillEntries
		$iKillCount = count($myKills);
		$iTmp = 0;
		foreach ($myKills as $myKillEntry )
		{
			$strInsert .= "( " .  
							$myserver['ID'] . ", " . 
							$myRound[ROUND_DBID] . ", " . 
							$myKillEntry[DBKILLS_ATTACKERGUID] . ", " . 
							$myKillEntry[DBKILLS_OPFERGUID] . ", " . 
							$myKillEntry[DBKILLS_WEAPONID] . ", " . 
							$myKillEntry[DBKILLS_DAMAGETYPE] . ", " . 
							$myKillEntry[DBKILLS_HITLOCATION] . ", " . 
							$myKillEntry[DBKILLS_COUNT] . ") "; 
			
			// Unset Entry!
			unset( $myKills[ $iTmp ] );

			// Append ", " unless its the last entry
			$iTmp++;
			if ( $iKillCount > $iTmp )
				$strInsert .= ", ";

		}
		
		// Append last ";"!
		$strInsert .= ";";
		
		// Debug Support
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Parser_FinalizeRound", "Extended Insert Statement (LARGE!) = '$strInsert'");

		// Process the statement now!
		ProcessExtendedInsertStatement( $strInsert, $iTmp );
	}
	// --- 

	// --- Now we finish all opening user session
	if ( count($myPlayers) > 0)
	{
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Parser_FinalizeRound", "Finish Session for '" . count($myPlayers) . "' players");
		foreach ( $myPlayers as $player )
		{
			if ( Parser_PlayerAnalyzeAndSave( $player, $roundlastedtime ) == false ) 
				unset( $myPlayers[$player[PARSER_GUID]] ); // This will correctly UNSET the Player!
		
			if ( isset($player) )
			{
				// 1111111111111111111 Remove the Array Entry
				if ( isset($player[PLAYER_GUID]) )
					unset( $myPlayers[ $player[PLAYER_GUID] ] );
			}
		}
	}
	else
		PrintHTMLDebugInfo( DEBUG_DEBUG, "Parser_FinalizeRound", "NO Players found");
	// --- 
}
/*	----------------------------------------------------*/


/*	----------------------------------------------------*/
/*	Function to add a Player into the current GameRound.
	SampleLogPrint: J;185269;5;^2|OCG|^1UnDead
	Description:
	Type:	J	
	GUID:	185269	
	Client ID:	5	
	Client Name:	^2|OCG|^1UnDead
*/
function Parser_AddPlayer( $myArray )
{
	global $myPlayers, $myserver;
	
	// Store PBGUID and generate 32Bit Checksum Guid for the stats database!
	$playerpbguid = $myArray[PARSER_GUID];
	$myArray[PARSER_GUID] = ParsePlayerGuid( $myArray, PARSER_GUID, JOIN_CLIENTNAME );

	// --- Check for GUID 0
	if ( $myArray[PARSER_GUID] == 0 )
	{
		PrintHTMLDebugInfo( DEBUG_DEBUG, "Parser_AddPlayer", "LogLine Ignored, PlayerID = 0");
		return;
	}
	// --- 

	// --- Check if already exists!
	if ( isset($myPlayers[$myArray[PARSER_GUID]]) )
	{
		PrintHTMLDebugInfo( DEBUG_WARN, "Parser_AddPlayer", "Player Array '" . implode(",", $myPlayers[$myArray[PARSER_GUID]]) . "' is already on the server! Possible duplicate GUID!");
		return;
	}
	// --- 

	PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Parser_AddPlayer", "Player with ID '" . $myArray[PARSER_GUID] . "' joined ");

	// --- Starting the Code
	
	// Set Values we know
	$myPlayers[ $myArray[PARSER_GUID] ][PLAYER_GUID] = $myArray[PARSER_GUID];
	$myPlayers[ $myArray[PLAYER_ID] ][PLAYER_ID] = $myArray[JOIN_CLIENTID];
	$myPlayers[ $myArray[PARSER_GUID] ][PLAYER_NAME] = $myArray[JOIN_CLIENTNAME];

	// Init Values!
	$myPlayers[ $myArray[PARSER_GUID] ][PLAYER_TEAM] = "";
	$myPlayers[ $myArray[PARSER_GUID] ][PLAYER_KILLS] = 0;
	$myPlayers[ $myArray[PARSER_GUID] ][PLAYER_DEATHS] = 0;
	$myPlayers[ $myArray[PARSER_GUID] ][PLAYER_TKS] = 0;
	$myPlayers[ $myArray[PARSER_GUID] ][PLAYER_SUICIDES] = 0;
	$myPlayers[ $myArray[PARSER_GUID] ][PLAYER_PBGUID] = $playerpbguid;
	

	// Add Alias and increment Counter
	$wherequery =  "WHERE SERVERID = " . $myserver['ID'] . " AND 
					PLAYERID = " . $myArray[PARSER_GUID] . " AND 
					Alias = '" . DB_RemoveBadChars($myArray[JOIN_CLIENTNAME]) . "'";

	$result = DB_Query("SELECT * FROM " . STATS_ALIASES . " " . $wherequery );
	$rows = DB_GetAllRows($result, true);
	if ( isset($rows) )
	{
		// Update Calc
		ProcessUpdateStatement("UPDATE " . STATS_ALIASES . " SET Count = Count + 1 " . $wherequery );
	}
	else
	{
		// Insert New
		ProcessInsertStatement("INSERT INTO " . STATS_ALIASES . " (SERVERID, PLAYERID, Alias, AliasAsHtml, Count) 
		VALUES (
			 " . $myserver['ID'] . ", 
			 " . $myArray[PARSER_GUID] . ", 
			 '" . GetPlayerNameAsWithHTMLCodes(DB_RemoveBadChars($myArray[JOIN_CLIENTNAME])) . "', 
			 '" . GetPlayerNameAsHTML(DB_RemoveBadChars($myArray[JOIN_CLIENTNAME])) . "', 
			 " . "1" . "
				)");
	}
	// --- 
	
	// Check and add static data
	Parser_AddStaticPlayerData( $myPlayers[ $myArray[PARSER_GUID] ] );
}
/*	----------------------------------------------------*/


/*	----------------------------------------------------*/
/*	Helper function to create thwe static record of a player
*/
function Parser_AddStaticPlayerData( $myCurrPlayer )
{
	// --- Check for GUID 0
	if ( $myCurrPlayer[PARSER_GUID] == 0 )
		return;
	// --- 

	// --- Starting the Code
	$wherequery =  "WHERE GUID = " . $myCurrPlayer[PLAYER_GUID]; 
	$result = DB_Query("SELECT * FROM " . STATS_PLAYERS_STATIC . " " . $wherequery );
	$rows = DB_GetAllRows($result, true);
	if ( !isset($rows) )
	{
		PrintHTMLDebugInfo( DEBUG_DEBUG, "Parser_AddStaticPlayerData", "Adding static entry for Player with ID '" . $myCurrPlayer[PLAYER_GUID] . "'");
		// Insert New
		ProcessInsertStatement("INSERT INTO " . STATS_PLAYERS_STATIC . " (GUID, PBGUID) 
		VALUES (
			 " . $myCurrPlayer[PLAYER_GUID] . ", 
			 '" . $myCurrPlayer[PLAYER_PBGUID] . "' 
				)");
	}
	// --- 
}
/*	----------------------------------------------------*/



/*	----------------------------------------------------*/
/*	Function to remove a Player from the current GameRound.
	SampleLogPrint: Q;185269;5;^2|OCG|^1UnDead
	Description:
	Type:	Q	
	GUID:	185269	
	Client ID:	5	
	Client Name:	^2|OCG|^1UnDead
*/
function Parser_RemovePlayer( $myArray, $timeplayed )
{
	global $myPlayers;
	
	// Convert GUID into 32Bit Number
	$myArray[PARSER_GUID] = ParsePlayerGuid( $myArray, PARSER_GUID, QUIT_CLIENTNAME );

	// --- Check for GUID 0
	if ( $myArray[PARSER_GUID] == 0 )
	{
		PrintHTMLDebugInfo( DEBUG_DEBUG, "Parser_RemovePlayer", "LogLine Ignored, PlayerID = 0");
		return;
	}
	// --- 

	// Check if the Player is valid
	if ( !isset($myPlayers[$myArray[PARSER_GUID]]) )
	{
		PrintHTMLDebugInfo( DEBUG_WARN, "Parser_RemovePlayer", "Player with GUID '" . $myArray[PARSER_GUID] . "' is not in the current playerlist!");
		return;
	}
	
	// DebugOutput
	PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Parser_RemovePlayer", "Player disconnected with ID '" . $myArray[PARSER_GUID] . "'");

	if ( isset($myPlayers[$myArray[PARSER_GUID]]) )
	{
		// Now we finish his session
		Parser_PlayerAnalyzeAndSave( $myPlayers[$myArray[PARSER_GUID]], $timeplayed );

		// Remove the Array Entry
		unset( $myPlayers[$myArray[PARSER_GUID]] );
	}
}
/*	----------------------------------------------------*/

/*	----------------------------------------------------*/
/*	Function to add a ChatLine into the database
	SampleLogPrint: say;14352;25;|OCG|Anarchy; No we're not atm
	Description:
	Type:	say	
	GUID:	14352	
	Client ID:	25	
	Client Name:	|OCG|Anarchy	
	Message:	No we're not atm	

	SampleLogPrint: sayteam;14352;25;|OCG|Anarchy; take it south
	Description:
	Type:	sayteam	
	GUID:	14352	
	Client ID:	25	
	Client Name:	|OCG|Anarchy	
	Message:	take it south
*/
function Parser_AddChatLine( $myArray )
{
	global $content, $myPlayers, $myserver, $myRound;

	if ( $content['parser_chatlogging'] == "no" )
	{
		// User doesn't want chat logging
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Parser_AddChatLine", "LogLine Ignored, ChatLogging disabled!");

		return;
	}

	// Convert GUID into 32Bit Number
	$myArray[PARSER_GUID] = ParsePlayerGuid( $myArray, PARSER_GUID, CHAT_CLIENTNAME );

	// --- Check for GUID 0
	if ( !isset($myArray[PARSER_GUID]) || $myArray[PARSER_GUID] == 0 )
	{
		PrintHTMLDebugInfo( DEBUG_DEBUG, "Parser_AddChatLine", "LogLine Ignored, PlayerID empty or  0!");
		return;
	}
	// --- 

	// Check Len
	$strchatmsg = substr($myArray[CHAT_MESSAGE], 1);
	if ( strlen($strchatmsg) > 1 )
	{
		// Get the Player entry
		if ( isset($myPlayers[ $myArray[PARSER_GUID] ]) && isset( $myPlayers[ $myArray[PARSER_GUID] ][PLAYER_GUID] ) )
		{
			$currentplayer = $myPlayers[ $myArray[PARSER_GUID] ];
			// --- Insert new Chat Record
			ProcessInsertStatement("INSERT INTO " . STATS_CHAT . " (PLAYERID, SERVERID, ROUNDID, TextSaid) 
			VALUES (
				 " . $currentplayer[PLAYER_GUID] . ", 
				 " . $myserver['ID'] . ", 
				 " . $myRound[ROUND_DBID] . ", 
				 '" . DB_RemoveBadChars( $strchatmsg ) . "' 
				)");
		}
		else
			PrintHTMLDebugInfo( DEBUG_ERROR, "Parser_AddChatLine", "Error, PlayerID '" . $myArray[PARSER_GUID] . "' not found in the Array!");
	}
	else
		PrintHTMLDebugInfo( DEBUG_DEBUG, "Parser_AddChatLine", "Chatline ignored due missing Chatcontent: '" . $strchatmsg . "'");
	// --- 

	PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Parser_AddChatLine", "Adding Chatline of PlayerID '" . $myArray[PARSER_GUID] . "'");
}
/*	----------------------------------------------------*/


/*	----------------------------------------------------*/
/*	Function to add a RoundAction into the Database
	SampleLogPrint: A;0;2;allies;^3[IW]^1Ned^1 Man;bel_alive_tick
	Description:
	Type: A 
	Client GUID: 0 
	Client ID: 2 
	Client Team: axis 
	Client Name: ^3[IW]^1Ned^1 Man 
	Action: bel_alive_tick
*/
function Parser_AddRoundAction( $myArray )
{
	global $myPlayers, $myserver, $myRound;

	// Convert GUID into 32Bit Number
	$myArray[PARSER_GUID] = ParsePlayerGuid( $myArray, PARSER_GUID, ACTION_CLIENT_NAME );

	// --- Check for GUID 0
	if ( $myArray[PARSER_GUID] == 0 )
	{
		PrintHTMLDebugInfo( DEBUG_DEBUG, "Parser_AddRoundAction", "LogLine Ignored, GUID was 0!");
		return;
	}
	// --- 

	// --- Making ActionEntry
	PrintHTMLDebugInfo( DEBUG_DEBUG, "Parser_AddRoundAction", "Adding Action '" . $myArray[ACTION_THEACTION] . "' for PlayerID '" . $myArray[KILL_OPFER_GUID] . "'");
	$wherequery =  "WHERE SERVERID = " . $myserver['ID'] . " AND 
					ROUNDID = " . $myRound[ROUND_DBID] . " AND 
					PLAYERID = " . $myArray[PARSER_GUID] . " AND 
					Team = '" . $myArray[ACTION_CLIENT_TEAM] . "' AND 
					ACTIONID = " . GetActionIDByName( $myArray[ACTION_THEACTION] );

	$result = DB_Query("SELECT * FROM " . STATS_ROUNDACTIONS . " " . $wherequery );
	$rows = DB_GetAllRows($result, true);
	if ( isset($rows) )
	{
		// Update Calc
		ProcessUpdateStatement("UPDATE " . STATS_ROUNDACTIONS . " SET Count = Count + 1 " . $wherequery );
	}
	else
	{
		// Insert New
		ProcessInsertStatement("INSERT INTO " . STATS_ROUNDACTIONS . " (SERVERID, ROUNDID, PLAYERID, Team, ACTIONID, Count) 
		VALUES (
			 " . $myserver['ID'] . ", 
			 " . $myRound[ROUND_DBID] . ", 
			 " . $myArray[PARSER_GUID] . ", 
			 '" . $myArray[ACTION_CLIENT_TEAM] . "', 
			 " . GetActionIDByName( $myArray[ACTION_THEACTION] ) . ", 
			 " . "1" . ")");
	}
	// --- 
}
/*	----------------------------------------------------*/


/*	----------------------------------------------------*/
/*	Function to process Damage Log Entry!
	SampleLogPrint: D;8009b97035dd53ef8ad66c5d93f94099;5;axis;Nobby;a2993e2462b16041f32d33f751b95bde;6;allies;<Easy> Bruzzl3r;m4_reflex_mp;30;MOD_RIFLE_BULLET;torso_lower
	Description:
	Type:	D	
	Attackee GUID:	8009b97035dd53ef8ad66c5d93f94099	
	Attackee ID:	5	
	Attackee Team:	axis	
	Attackee Name:	Nobby
	Opfer GUID:	a2993e2462b16041f32d33f751b95bde	
	Opfer ID:	6	
	Opfer Team:	allies	
	Opfer Name:	<Easy> Bruzzl3r	
	Attacker Weapon:	m4_reflex_mp	
	Damage:	30	
	Damage Type:	MOD_RIFLE_BULLET	
	Damage Location:	torso_lower
*/
function Parser_ProcessDamage( $myArray )
{
	global $myPlayers, $myserver, $myRound;

	// Convert GUID into 32Bit Number
	$myArray[DAMAGE_OPFER_GUID] = ParsePlayerGuid( $myArray, DAMAGE_OPFER_GUID, DAMAGE_OPFER_NAME );
	$myArray[DAMAGE_ATTACKER_GUID] = ParsePlayerGuid( $myArray, DAMAGE_ATTACKER_GUID, DAMAGE_ATTACKER_NAME );

	// --- Check and Set Team of Opfer! 
	if ( $myArray[DAMAGE_OPFER_GUID] != 0 )
	{
		$opfer = &$myPlayers[ $myArray[DAMAGE_OPFER_GUID] ];
		if (	
				(isset($opfer) && isset($opfer[PLAYER_GUID]) )
				&& 
				( 
					strlen($opfer[PLAYER_TEAM]) <= 0
					|| 
					$opfer[PLAYER_TEAM] != $myArray[DAMAGE_OPFER_TEAM]
				)
			)
		{
			// Set Team!
			$opfer[PLAYER_TEAM] = $myArray[DAMAGE_OPFER_TEAM];
		}
		// Debug print ^^
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Parser_ProcessDamage", "Set Team '" . $myArray[DAMAGE_OPFER_TEAM] . "' on player GUID '" . $myArray[DAMAGE_OPFER_GUID] . "'");
	}
	else
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Parser_ProcessDamage", "Attackee ignored, has GUID 0!");
	// --- 

	// --- Check and Set Team of Attacker! 
	if ( $myArray[DAMAGE_ATTACKER_GUID] != 0 )
	{
		$attacker = &$myPlayers[ $myArray[DAMAGE_ATTACKER_GUID] ];
		if (
				(isset($attacker) && isset($attacker[PLAYER_GUID]) )
				&& 
				( 
					strlen($attacker[PLAYER_TEAM]) <= 0
					|| 
					$attacker[PLAYER_TEAM] != $myArray[DAMAGE_ATTACKER_TEAM]
				)
			)
		{
			// Set Team!
			$attacker[PLAYER_TEAM] = $myArray[DAMAGE_ATTACKER_TEAM];
		}
		// Debug print ^^
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Parser_ProcessDamage", "Set Team '" . $myArray[DAMAGE_ATTACKER_TEAM] . "' on player GUID '" . $myArray[DAMAGE_ATTACKER_GUID] . "'");
	}
	else
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Parser_ProcessDamage", "Attackee ignored, has GUID 0!");
	// --- 


}
/*	----------------------------------------------------*/


/*	----------------------------------------------------*/
/*	Function to add Kill and Death of Players
	SampleLogPrint: K;185269;7;allies;^2|OCG|^1UnDead;186276;8;axis;^2|OCG|^9CerealKilla;mp40_mp;135;MOD_HEAD_SHOT;head
	Description:
	Type:	K	
	Attackee GUID:	185269	
	Attackee ID:	7	
	Attackee Team:	allies	
	Attackee Name:	^2|OCG|^1UnDead	
	Attacker GUID:	186276	
	Attacker ID:	8	
	Attacker Team:	axis	
	Attacker Name:	^2|OCG|^9CerealKilla	
	Attacker Weapon:	mp40_mp	
	Damage:	135	
	Damage Type:	MOD_HEAD_SHOT	
	Damage Location:	head
*/
function Parser_AddKillAndDeath( $myArray )
{
	global $myPlayers, $myserver, $myRound, $myKills;

	// Convert GUID into 32Bit Number
	$myArray[KILL_OPFER_GUID] = ParsePlayerGuid( $myArray, KILL_OPFER_GUID, KILL_OPFER_NAME );
	$myArray[KILL_ATTACKER_GUID] = ParsePlayerGuid( $myArray, KILL_ATTACKER_GUID, KILL_ATTACKER_NAME );

	// --- Check for GUID 0
	if ( $myArray[KILL_OPFER_GUID] == 0 || $myArray[KILL_ATTACKER_GUID] == 0 )
	{
		PrintHTMLDebugInfo( DEBUG_DEBUG, "Parser_AddKillAndDeath", "LogLine Ignored, either Attacker or Attackee has GUID 0!");
		return;
	}
	// --- 
	
	// --- Special Check for Duplicated GUID
	if ( $myArray[KILL_DAMAGE_TYPE] != "MOD_SUICIDE" && 
		($myArray[KILL_OPFER_GUID] == $myArray[KILL_ATTACKER_GUID]))
	{
		PrintHTMLDebugInfo( DEBUG_DEBUG, "Parser_AddKillAndDeath", "Kill ignore, Attacker and Opfer have the same GUID - and no it is not a suicide kill!");
		// No selfkill but same guid? nono duplicated guid, not counted
		return;
	}
	// --- 

	// --- Special Check for invalid player guids (like duplicated guids!
	if ( !isset($myPlayers[$myArray[KILL_OPFER_GUID]]) )
	{
		// Opfer not found, we don't count this!
		PrintHTMLDebugInfo( DEBUG_ERROR, "Parser_AddKillAndDeath", "Error, Player Opfer '" . $myArray[KILL_OPFER_GUID] . " already left the server, possible Double GUID! Kill may not be NOT Counted!");

		// In this case, we do NOT count!
//		return; // TODO: Was commented out, why?
	}
	else
	{
		// Set Opfer reference!
		$opfer = &$myPlayers[ $myArray[KILL_OPFER_GUID] ];
	}

	if ( !isset($myPlayers[$myArray[KILL_ATTACKER_GUID]]) )
	{
		// Attacker not found, we don't count this!
		PrintHTMLDebugInfo( DEBUG_ERROR, "Parser_AddKillAndDeath", "Error, Player Attacker '" . $myArray[KILL_OPFER_GUID] . " already left the server, possible Double GUID! Kill is NOT Counted!");

		// In this case, we do NOT count!
		return; // TODO: Was commented out, why?
	}
	// --- 

	// Set Attacker reference!
	$attacker = &$myPlayers[ $myArray[KILL_ATTACKER_GUID] ];

	// --- Extra BUG Check for COD4! 
	// They fucking broke logging for the KILL Entry, damn IW noobs -.-! So if teams are empty, we set them manually !
	if ( strlen($myArray[KILL_OPFER_TEAM]) <= 0 )
	{
		if ( isset($opfer) && isset($opfer[PLAYER_TEAM]) && strlen($opfer[PLAYER_TEAM]) > 0 )
			$myArray[KILL_OPFER_TEAM] = $opfer[PLAYER_TEAM]; 
		else
		{
			$myArray[KILL_OPFER_TEAM] = TEAM_WTF; 
		}
	}
	if ( strlen($myArray[KILL_ATTACKER_TEAM]) <= 0 )
	{
		if ( isset($attacker) && isset($attacker[PLAYER_TEAM]) && strlen($attacker[PLAYER_TEAM]) > 0 )
			$myArray[KILL_ATTACKER_TEAM] = $attacker[PLAYER_TEAM]; 
		else
		{
			if ( $myArray[KILL_OPFER_TEAM] == TEAM_ALLIES ) 
				$myArray[KILL_ATTACKER_TEAM] = TEAM_AXIS; 
			else if ( $myArray[KILL_OPFER_TEAM] == TEAM_AXIS ) 
				$myArray[KILL_ATTACKER_TEAM] = TEAM_ALLIES; 
			else if ( $myArray[KILL_OPFER_TEAM] == TEAM_WTF ) // WTF default lol! God I hate the bugged logformat from IW so much ... 
			{
				PrintHTMLDebugInfo( DEBUG_ERROR, "Parser_AddKillAndDeath", "Setting Teams to default, attacker may be empty!");
				$myArray[KILL_ATTACKER_TEAM] = TEAM_ALLIES; 
				$myArray[KILL_OPFER_TEAM] = TEAM_AXIS; 
			}
		}
	}
	// Extra Check!
	if ( $myArray[KILL_OPFER_TEAM] == TEAM_WTF )
	{	
		// Set another default in this case!
				PrintHTMLDebugInfo( DEBUG_ERROR, "Parser_AddKillAndDeath", "Setting Teams to default, opfer may be empty!");
		$myArray[KILL_ATTACKER_TEAM] = TEAM_ALLIES; 
		$myArray[KILL_OPFER_TEAM] = TEAM_AXIS; 
	}
	// --- 


	// --- Making KillEntry
	if (
			$myArray[KILL_DAMAGE_TYPE] != "MOD_SUICIDE" 
			&& 
			(
				// Suicide or Teamkill isn't counted
				$myArray[KILL_OPFER_TEAM] != $myArray[KILL_ATTACKER_TEAM]
				||
				// Dont count in DM 
				$myRound[ROUND_GAMETYPE] == "dm"
			)
		)
	{
		// --- NEW CODE to Count Kill & Deaths - INSERTS will be done with ONE big EXTENDED INSERT later. This should speed up SQL processing!
		
		// Helper variables
		$bFoundKillEntry = false;
		$nArrayId = 0;
		
		// Create KillEntry
		$newKillEntry[DBKILLS_ATTACKERGUID] = $myArray[KILL_ATTACKER_GUID];
		$newKillEntry[DBKILLS_OPFERGUID] = $myArray[KILL_OPFER_GUID];
		$newKillEntry[DBKILLS_WEAPONID] = GetWeaponIDByName( $myArray[KILL_ATTACKER_WEAPON] );
		$newKillEntry[DBKILLS_DAMAGETYPE] = GetDamageTypeIDByName( $myArray[KILL_DAMAGE_TYPE] );
		$newKillEntry[DBKILLS_HITLOCATION] = GetHitLocationTypeIDByName( $myArray[KILL_DAMAGE_LOCATION]);
		
		// Searcjh for existing occurence in KillsArray
		if ( isset($myKills) )
		{
			// Assign Count to ArrayID
			$nArrayId = count($myKills);
			
			//Loop through through array
//			foreach ($myKills as &$myKillEntry ) | Only supported by PHP5 and higher
//			foreach ($myKills as $myKillEntry ) | OLD BUG!
			for ($ix = 0; $ix < $nArrayId; $ix++)
			{
				if (	$myKills[$ix][DBKILLS_ATTACKERGUID] == $newKillEntry[DBKILLS_ATTACKERGUID] && 
						$myKills[$ix][DBKILLS_OPFERGUID] == $newKillEntry[DBKILLS_OPFERGUID] && 
						$myKills[$ix][DBKILLS_WEAPONID] == $newKillEntry[DBKILLS_WEAPONID] && 
						$myKills[$ix][DBKILLS_DAMAGETYPE] == $newKillEntry[DBKILLS_DAMAGETYPE] && 
						$myKills[$ix][DBKILLS_HITLOCATION] == $newKillEntry[DBKILLS_HITLOCATION] )
				{
					// Increment KillCount
					$myKills[$ix][DBKILLS_COUNT]++;
					$bFoundKillEntry = true;
					break;
				}
			}
		}

		if ( !$bFoundKillEntry )
		{	// If new KillEntry add to array
			$newKillEntry[DBKILLS_COUNT]= 1;
			$myKills[$nArrayId] = $newKillEntry;
		}
		// --- 

		// Increment Player Kill Count!
		if ( isset( $attacker[PLAYER_KILLS] ) ) 
			$attacker[PLAYER_KILLS]++;
		else
		{
			PrintHTMLDebugInfo( DEBUG_ERROR_WTF, "Parser_AddKillAndDeath", "attacked = " . print_r($attacker) );
		}

		// Also Increment total Round Killcount!
		$myRound[ROUND_TOTALKILLS]++;
	}
	else
		PrintHTMLDebugInfo( DEBUG_DEBUG, "Parser_AddKillAndDeath", "Killentry ignored as it was a Teamkill or Suicide");
	// --- 

	// --- Make Entries for the Opfer
	PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Parser_AddKillAndDeath", "Adding Kill for PlayerID '" . $myArray[KILL_OPFER_GUID] . "'");
	
	// Set Current Team if empty
//	if ( isset($opfer[PLAYER_TEAM]) && strlen($opfer[PLAYER_TEAM]) <= 0 )
//		$opfer[PLAYER_TEAM] = $myArray[KILL_OPFER_TEAM];

	// Set Death or Suicides
	if ( $myArray[KILL_DAMAGE_TYPE] == "MOD_SUICIDE" && isset($opfer[PLAYER_SUICIDES]) ) 
		$opfer[PLAYER_SUICIDES]++;
	else if ( isset($opfer[PLAYER_DEATHS]) )
		$opfer[PLAYER_DEATHS]++;
	// --- 

	// --- Make Additional Entries for the Killer
	PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Parser_AddKillAndDeath", "Adding Death for PlayerID '" . $myArray[KILL_ATTACKER_GUID] . "'");
	if ($myArray[KILL_DAMAGE_TYPE] != "MOD_SUICIDE" && isset($attacker[PLAYER_TEAM]) )	// Counted suicide already!
	{
		// Set Current Team if empty
		if ( strlen($attacker[PLAYER_TEAM]) <= 0 )
			$attacker[PLAYER_TEAM] = $myArray[KILL_ATTACKER_TEAM];

		if ( $myArray[KILL_ATTACKER_TEAM] == $myArray[KILL_OPFER_TEAM] && ($myRound[ROUND_GAMETYPE] != "dm" ) )	// Dont count in DM 
			$attacker[PLAYER_TKS]++;
	}
	// --- 
}
/*	----------------------------------------------------*/


/*	----------------------------------------------------*/
/*	Function to add RoundWin - only for Round based Gametypes
	SampleLogPrint: W;axis;107521;^2|OCG|^4STINKYPETE;186276;^2|OCG|^9CerealKilla
	Description:
	Type:	W	
	Team:	axis	
	GUID + Players:	107521;^2|OCG|^4STINKYPETE;186276;^2|OCG|^9CerealKilla
*/
function Parser_AddRoundWin( $myArray )
{
	global $myRound;

	PrintHTMLDebugInfo( DEBUG_DEBUG, "Parser_AddRoundWin", "Adding RoundWin");

	// Add RoundWin
	if ( $myArray[RWIN_TEAM] == "axis" ) 
	{
		$winningteam = ROUND_AXIS_GUIDS;
		$losingteam = ROUND_ALLIES_GUIDS;
		$myRound[ROUND_AXIS_WINS]++;
	}
	else
	{
		$winningteam = ROUND_ALLIES_GUIDS;
		$losingteam = ROUND_AXIS_GUIDS;
		$myRound[ROUND_ALLIES_WINS]++;
	}

	// Create tmp Winner PlayerList now
	for($i = 2; $i < count($myArray); $i+=2)
		$tmpplayers[ DB_RemoveBadChars($myArray[$i]) ] = DB_RemoveBadChars($myArray[$i]);

	// Add guids to the winner team
	if ( isset($tmpplayers) && count($tmpplayers) > 0 )
	{
		foreach ($tmpplayers as $myguid )
		{
			// Add if not already there
			if ( $myguid != 0) 
			{
				if ( !isset($myRound[$winningteam]) )								// Add in any case
					$myRound[$winningteam][$myguid] = $myguid;
				else if ( !array_key_exists($myguid, $myRound[$winningteam]) )		// Add only if not already there
					$myRound[$winningteam][$myguid] = $myguid;
			}
		}
	}

	// If loser Team exists, we remove changed players guids
	if (	isset($myRound[$losingteam]) && 
			count($myRound[$losingteam]) > 0 &&
			isset($tmpplayers) && 
			count($tmpplayers) > 0 )
	{
		foreach ( $myRound[$losingteam] as $myguid )
		{
			// Remove from!
			if ( array_key_exists($myguid, $tmpplayers) )
				unset($myRound[$losingteam][$myguid]);
		}
	}

	if (isset($myRound[$winningteam]))
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Parser_AddRoundWin", "Winning Team Playercount: " . count($myRound[$winningteam]) );
	if (isset($myRound[$losingteam]))
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Parser_AddRoundWin", "Losing Team Playercount: " . count($myRound[$losingteam]) );
}
/*	----------------------------------------------------*/


/*	----------------------------------------------------*/
/*	Function to add a RoundLoss - only for Round based Gametypes
	SampleLogPrint: L;allies;185269;^2|OCG|^1UnDead;104252;^2[OCG]^4Paulaner_Pils
	Description:
	Type:	L	
	Team:	allies	
	GUID + Players:	185269;^2|OCG|^1UnDead;104252;^2[OCG]^4Paulaner_Pils
*/
function Parser_AddRoundLoss( $myArray )
{
	global $myRound;

	PrintHTMLDebugInfo( DEBUG_DEBUG, "Parser_AddRoundLoss", "Adding RoundLoss");

	// Add RoundLoss
	if ( $myArray[RLOS_TEAM] == "axis" ) 
	{
		$losingteam = ROUND_AXIS_GUIDS;
		$winningteam = ROUND_ALLIES_GUIDS;
	}
	else
	{
		$losingteam = ROUND_ALLIES_GUIDS;
		$winningteam = ROUND_AXIS_GUIDS;
	}

	// Create tmp Loser PlayerList now
	for($i = 2; $i < count($myArray); $i+=2)
		$tmpplayers[ DB_RemoveBadChars($myArray[$i]) ] = DB_RemoveBadChars($myArray[$i]);

	// Add guids to the loser team
	if ( isset($tmpplayers) && count($tmpplayers) > 0 )
	{
		foreach ($tmpplayers as $myguid )
		{
			// Add if not already there
			if ( $myguid != 0) 
			{
				if ( !isset($myRound[$losingteam]) )									// Add in any case
					$myRound[$losingteam][$myguid] = $myguid;
				else if ( !array_key_exists($myguid, $myRound[$losingteam]) )			// Add only if not already there
					$myRound[$losingteam][$myguid] = $myguid;
			}
		}
	}

	// If Winner Team exists, we remove changed players guids
	if (	isset($myRound[$winningteam]) && 
			count($myRound[$winningteam]) > 0 &&
			isset($tmpplayers) && 
			count($tmpplayers) > 0 )
	{
		foreach ( $myRound[$winningteam] as $myguid )
		{
			// Remove from!
			if ( array_key_exists($myguid, $tmpplayers) )
				unset($myRound[$winningteam][$myguid]);
		}
	}
	
	if (isset($myRound[$winningteam]))
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Parser_AddRoundLoss", "Winning Team Playercount: " . count($myRound[$winningteam]) );
	if (isset($myRound[$losingteam]))
		PrintHTMLDebugInfo( DEBUG_ULTRADEBUG, "Parser_AddRoundLoss", "Losing Team Playercount: " . count($myRound[$losingteam]) );
}
/*	----------------------------------------------------*/


/*	----------------------------------------------------*/
/*	Function to Finish the Stats for a Player 
*/
function Parser_PlayerAnalyzeAndSave( $myPlayer, $timeplayed )
{
	global $myRound, $myserver;

	if ( !isset($myPlayer) )
	{
		PrintHTMLDebugInfo( DEBUG_ERROR, "PlayerAnalyzeAndSave", "Error PlayerArray is empty wtf timeplayer='" .$timeplayed . "'");
		return false;
	}

	// Error Check
	if ( !isset($myPlayer[PLAYER_GUID]) || strlen($myPlayer[PLAYER_GUID]) <= 0 )
	{
		PrintHTMLDebugInfo( DEBUG_ERROR, "PlayerAnalyzeAndSave", "Invalid Player ID! Array='" . implode(",", $myPlayer) . "'");

//	global $myPlayers;
//	print_r ($myPlayers);
//	exit; 
		
		// Return true, so the player gets deleted in any case!
		return true;
	}

	// DebugInfo
	PrintHTMLDebugInfo( DEBUG_DEBUG, "PlayerAnalyzeAndSave", "Finish Stats for Player '" . $myPlayer[PLAYER_GUID] . "'");

	// --- Update the Player record
	$wherequery =  "WHERE GUID = " . $myPlayer[PLAYER_GUID] . " AND 
					SERVERID = " . $myserver['ID'] . " AND 
					Time_Year = " . $myRound[ROUND_TIMEYEAR] . " AND 
					Time_Month = " . $myRound[ROUND_TIMEMONTH];

	$result = DB_Query("SELECT * FROM " . STATS_PLAYERS . " " . $wherequery );
	$myrow = DB_GetSingleRow($result, true);
	if ( isset($myrow['GUID']) )
	{
		// Calc Total Values
		$totalkills = $myrow['Kills'] + $myPlayer[PLAYER_KILLS];
		$totaldeaths = $myrow['Deaths'] + $myPlayer[PLAYER_DEATHS];
		$totaltks = $myrow['Teamkills'] + $myPlayer[PLAYER_TKS];
		$totalsuicides = $myrow['Suicides'] + $myPlayer[PLAYER_SUICIDES];
		if ( $totaldeaths > 0 )
			$killratio = $totalkills / $totaldeaths;
		else
			$killratio = $totalkills;

		// We go for the update
		ProcessUpdateStatement("UPDATE " . STATS_PLAYERS . " SET 
			Kills = $totalkills, 
			Deaths = $totaldeaths,
			Teamkills = $totaltks, 
			Suicides = $totalsuicides, 
			KillRatio = $killratio " . $wherequery );
	}
	else
	{
		// Calc Total Values
		if ( $myPlayer[PLAYER_DEATHS] > 0 )
			$killratio = $myPlayer[PLAYER_KILLS] / $myPlayer[PLAYER_DEATHS];
		else
			$killratio = $myPlayer[PLAYER_KILLS];

		// We add a NEW entry
		$myPlayer[PLAYER_DBID] = ProcessInsertStatement("INSERT INTO " . STATS_PLAYERS . " (GUID, SERVERID, Time_Year, Time_Month, Kills, Deaths, Teamkills, Suicides, KillRatio) 
		VALUES (
			 " . $myPlayer[PLAYER_GUID] . ", 
			 " . $myserver['ID'] . ", 
			 " . $myRound[ROUND_TIMEYEAR] . ", 
			 " . $myRound[ROUND_TIMEMONTH] . ", 
			 " . $myPlayer[PLAYER_KILLS] . ", 
			 " . $myPlayer[PLAYER_DEATHS] . ", 
			 " . $myPlayer[PLAYER_TKS] . ", 
			 " . $myPlayer[PLAYER_SUICIDES] . ", 
			 " .$killratio . "
			)");
	}
	// ---

	// --- Set the TimeRecord for the Player
	$wherequery =  "WHERE SERVERID = " . $myserver['ID'] . " AND 
					Time_Year = " . $myRound[ROUND_TIMEYEAR] . " AND 
					Time_Month = " . $myRound[ROUND_TIMEMONTH] . " AND 
					ROUNDID = " . $myRound[ROUND_DBID] . " AND 
					PLAYERID = " . $myPlayer[PLAYER_GUID];
	$result = DB_Query("SELECT TIMEPLAYED FROM " . STATS_TIME . " " . $wherequery );
	$myrow = DB_GetSingleRow($result, true);
	if ( isset($myrow['TIMEPLAYED']) )
	{
		// We go for the update
		ProcessUpdateStatement("UPDATE " . STATS_TIME . " SET TIMEPLAYED = TIMEPLAYED + $timeplayed " . $wherequery);
	}
	else
	{
		// We add a NEW entry
		ProcessInsertStatement("INSERT INTO " . STATS_TIME . " (SERVERID, Time_Year, Time_Month, ROUNDID, PLAYERID, TIMEPLAYED) 
		VALUES (
			 " . $myserver['ID'] . ", 
			 " . $myRound[ROUND_TIMEYEAR] . ", 
			 " . $myRound[ROUND_TIMEMONTH] . ", 
			 " . $myRound[ROUND_DBID] . ", 
			 " . $myPlayer[PLAYER_GUID] . ", 
			 " . $timeplayed . "
			)");
	}
	// ---
	
	// Return success
	return true;
}
/*	----------------------------------------------------*/



?>