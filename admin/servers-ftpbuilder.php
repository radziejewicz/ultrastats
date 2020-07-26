<?php
/*
	*********************************************************************
	* Copyright by Andre Lorbach | 2006!								*
	* -> www.ultrastats.org <-											*
	*																	*
	* Use this script at your own risk!									*
	* -----------------------------------------------------------------	*
	* Server Edit Admin File											*
	*																	*
	* -> Helps to admin and manage Servers in UltraStats		*
	*																	*
	* All directives are explained within this file						*
	*********************************************************************
*/

// *** Default includes	and procedures *** //
define('IN_ULTRASTATS', true);
$gl_root_path = './../';
include($gl_root_path . 'include/functions_db.php');
include($gl_root_path . 'include/functions_common.php');
include($gl_root_path . 'include/class_template.php');

InitUltraStats();
CheckForUserLogin( false );

IncludeLanguageFile( $gl_root_path . 'lang/' . $LANG . '/admin.php' );
// ***					*** //

// --- CONTENT Vars
$content['TITLE'] = "Ultrastats - Admin Center - Servers";	// Title of the Page 
// --- 

// --- BEGIN Custom Code
if ( isset($_GET['id']) || isset($_POST['id']) )
{
	//PreInit these values 
	$content['SERVERID'] = DB_RemoveBadChars($_GET['id']);

	$sqlquery = "SELECT ID, ftppath, FTPPassiveMode " . 
				" FROM " . STATS_SERVERS . 
				" WHERE ID = " . $content['SERVERID'];

	$result = DB_Query($sqlquery);
	$rows = DB_GetAllRows($result, true);
	if ( isset($rows ) )
	{
		// --- only then we continue

		if ( isset($_POST['verify']) || isset($_POST['save']) )
		{
			// Read in parameters
			if ( isset($_POST['id']) ) { $content['SERVERID'] = DB_RemoveBadChars($_POST['id']); } else {$content['SERVERID'] = ""; }
			if ( isset($_POST['serverip']) ) { $content['SERVERIP'] = DB_RemoveBadChars($_POST['serverip']); } else {$content['SERVERIP'] = ""; }
			if ( isset($_POST['serverport']) ) { $content['SERVERPORT'] = DB_RemoveBadChars($_POST['serverport']); } else {$content['SERVERPORT'] = ""; }
			if ( isset($_POST['username']) ) { $content['USERNAME'] = DB_RemoveBadChars($_POST['username']); } else {$content['USERNAME'] = ""; }
			if ( isset($_POST['password']) ) { $content['PASSWORD'] = DB_RemoveBadChars($_POST['password']); } else {$content['PASSWORD'] = ""; }
			if ( isset($_POST['pathtogamelog']) ) { $content['PATHTOGAMELOG'] = DB_RemoveBadChars($_POST['pathtogamelog']); } else {$content['PATHTOGAMELOG'] = ""; }
			if ( isset($_POST['gamelogfilename']) ) { $content['GAMELOGFILENAME'] = DB_RemoveBadChars($_POST['gamelogfilename']);} else {$content['GAMELOGFILENAME'] = "";}
			if ( isset($_POST['ftppassiveenabled']) ) { $content['FTPPASSIVEENABLED'] = true; } else {$content['FTPPASSIVEENABLED'] = 0; }

			if ( isset($_POST['verify']) )
			{
				// Start testing the FTP Connection
				$content['verifyresults'] = "<b>Starting FTP Verification...</b><br><br>";
				$content['ISVERIFY'] = "true";

				$connid = ftp_connect($content['SERVERIP'], $content['SERVERPORT'], FTP_TIMEOUT);
				if ($connid)
				{
					// Set the network timeout to 10 seconds
					ftp_set_option($connid, FTP_TIMEOUT_SEC, 10);

					$content['verifyresults'] .= "<li>Connect to '" . $content['SERVERIP'] . "' was successfull!<br>";
					if (@ftp_login($connid, $content['USERNAME'] , $content['PASSWORD']))
					{	
						//Successfully connected!
						$content['verifyresults'] .= "<li>Logged in as User '" . $content['USERNAME']  . "' successfull!<br>";
						
						//If enabled, set passive mode!
						if ( $content['FTPPASSIVEENABLED'] == true ) 
						{
							ftp_pasv ($connid, true) ;

							//Changed to passive mode!
							$content['verifyresults'] .= "<li>Changed to passive mode<br>";
						}
						
						// Change directory
						if (ftp_chdir($connid, $content['PATHTOGAMELOG'])) 
						{
							$content['verifyresults'] .= "<li>Path successfully changed to '" . ftp_pwd($connid) . "'<br>";

							// Get remote filesize
							$remotefilesize = ftp_size( $connid, $content['GAMELOGFILENAME'] );
							if ( $remotefilesize == -1 )
								$content['verifyresults'] .= "<li><font color=\"red\"><B>ERROR</B></font> Remotelogfile " . $content['GAMELOGFILENAME'] . " does not exists!!<br>";
							else
								$content['verifyresults'] .= "<li>Awesome, Remotelogfile " . $content['GAMELOGFILENAME'] . " does exists!<br><br><b>You did everything right dude ;)</b><br>";
						}
						else
							$content['verifyresults'] .= "<li><font color=\"red\"><B>ERROR</B></font> Couldn't change to the path  " . $content['PATHTOGAMELOG'] . "!<br>";
					}
					else
						$content['verifyresults'] .= "<li><font color=\"red\"><B>ERROR</B></font> Couldn't login with user " . $content['USERNAME'] . "!<br>";
				}
				else
					$content['verifyresults'] .= "<li><font color=\"red\"><B>ERROR</B></font> Couldn't connect to server " . $content['SERVERIP'] . " on Port " . $content['SERVERPORT'] . " !<br>";
			}
			if ( isset($_POST['save']) )
			{
				// Create FTP String!
				if ( isset($content['PASSWORD']) && strlen($content['PASSWORD']) > 0 )
					$szPassword = ":" . $content['PASSWORD'];
				else
					$szPassword = "";
				
				$szFtpUrl = 'ftp://'
							.	$content['USERNAME']
							.	$szPassword
							.	'@'
							.	$content['SERVERIP']
							.	':'
							.	$content['SERVERPORT']
							.	$content['PATHTOGAMELOG']
							.	$content['GAMELOGFILENAME'];

				// Edit the Server now!
				$result = DB_Query("UPDATE " . STATS_SERVERS . " SET 
					ftppath = '" . $szFtpUrl . "', 
					FTPPassiveMode = " . $content['FTPPASSIVEENABLED'] . " 
					WHERE ID = " . $content['SERVERID']);
				DB_FreeQuery($result);

				// USed to display the saved dialog
				$content['ISSAVED'] = "true";

				// Redirect!
//				RedirectResult( GetAndReplaceLangStr( $content['LN_SERVER_SUCCEDIT'], $content['SERVERNAME'] ) , "servers.php" );
			}
/*
			if ( !isset($content['ISERROR']) ) 
			{	
				// Everything was alright, so we go to the next step!


				// Redirect!
	//			RedirectResult( GetAndReplaceLangStr( $content['LN_SERVER_SUCCEDIT'], $content['SERVERNAME'] ) , "servers.php" );
			}*/

		}
		else
		{
			// Set Parameters from DB
			$content['FTPPASSIVEENABLED'] = $rows[0]['FTPPassiveMode'];
			$currentftppath = $rows[0]['ftppath'];
			if ( isset($currentftppath) && strlen($currentftppath) > 0 )
			{
				// Start parsing the FTP Path
				$ftpvars = ParseFtpValuesFromURL( $currentftppath );
				$content['SERVERIP']		= $ftpvars['ftpserver'];
				$content['SERVERPORT']		= $ftpvars['ftpport'];
				$content['USERNAME']		= $ftpvars['username'];
				$content['PASSWORD']		= $ftpvars['password'];
				$content['PATHTOGAMELOG']	= $ftpvars['ftppath'];
				$content['GAMELOGFILENAME'] = $ftpvars['ftpfilename'];
			}
			else
			{
				// Nothign specified, fill with defaults
				$content['SERVERIP']		= "127.0.0.1";
				$content['SERVERPORT']		= "21";
				$content['USERNAME']		= "user";
				$content['PASSWORD']		= "";
				$content['PATHTOGAMELOG']	= "/.callofduty2/main/";
				$content['GAMELOGFILENAME'] = "Server1_mp.log";
			}
		}
		// --- 
	}
	else
	{
		$content['ISERROR'] = "true";
		$content['ERROR_MSG'] = GetAndReplaceLangStr( $content['LN_SERVER_ERROR_NOTFOUND'], $content['SERVERID'] ); 
	}
}
else
{
	$content['ISERROR'] = "true";
	$content['ERROR_MSG'] = $content['LN_SERVER_ERROR_INVID'];
}

if ( isset($content['FTPPASSIVEENABLED']) && $content['FTPPASSIVEENABLED'] )
	$content['FTPPASSIVEENABLED_CHECKED'] = "checked";
else
	$content['FTPPASSIVEENABLED_CHECKED'] = "";
// --- END Custom Code

// --- Parsen and Output
InitTemplateParser();
$page -> parser($content, "admin/servers-ftpbuilder.html");
$page -> output(); 
// --- 

?>