<?php
/*
	*********************************************************************
	* Copyright by Andre Lorbach | 2006, 2007, 2008						*
	* -> www.ultrastats.org <-											*
	*																	*
	* Use this script at your own risk!									*
	* -----------------------------------------------------------------	*
	* Installer File													*
	*																	*
	* -> Will help you n00b to install the stats by clicking :P			*
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

InitBasicUltraStats();
IncludeLanguageFile( $gl_root_path . '/lang/' . $LANG . '/main.php' );
//InitFrontEndDefaults();	// Only in WebFrontEnd

// Set some static values
define('MAX_STEPS', 7);
$content['web_theme'] = "default";
$content['user_theme'] = "default";
ini_set('error_reporting', E_ERROR); // NO PHP ERROR'S!
// ***					*** //

// --- CONTENT Vars
$content['TITLE'] = "Ultrastats :: Installer Step %1";
// --- 

// --- Read Vars
if ( isset($_GET['step']) )
{
	$content['INSTALL_STEP'] = intval(DB_RemoveBadChars($_GET['step']));
	if ( $content['INSTALL_STEP'] > MAX_STEPS ) 
		$content['INSTALL_STEP'] = 1;
}
else
	$content['INSTALL_STEP'] = 1;

// Set Next Step 
$content['INSTALL_NEXT_STEP'] = $content['INSTALL_STEP'];

if ( MAX_STEPS > $content['INSTALL_STEP'] )
{
	$content['NEXT_ENABLED'] = "true";
	$content['FINISH_ENABLED'] = "false";
	$content['INSTALL_NEXT_STEP']++;
}
else
{
	$content['NEXT_ENABLED'] = "false";
	$content['FINISH_ENABLED'] = "true";
}
// --- 



// --- BEGIN Custom Code

// --- Set Bar Image
	$content['BarImagePlus'] = $gl_root_path . "images/bars/bar-middle/green_middle_17.png";
	$content['BarImageLeft'] = $gl_root_path . "images/bars/bar-middle/green_left_17.png";
	$content['BarImageRight'] = $gl_root_path . "images/bars/bar-middle/green_right_17.png";
	$content['WidthPlus'] = intval( $content['INSTALL_STEP'] * (100 / MAX_STEPS) ) - 8;
	$content['WidthPlusText'] = "Installer Step " . $content['INSTALL_STEP'];
// --- 

// --- Set Title
GetAndReplaceLangStr( $content['TITLE'], $content['INSTALL_STEP'] );
// --- 

// --- Start Setup Processing
if ( $content['INSTALL_STEP'] == 2 )
{	
	// Check if file permissions are correctly
	$content['fileperm'][0]['FILE_NAME'] = $content['BASEPATH'] . "config.php"; 
	$content['fileperm'][0]['FILE_TYPE'] = "file"; 
	$content['fileperm'][1]['FILE_NAME'] = $content['BASEPATH'] . "gamelogs/"; 
	$content['fileperm'][1]['FILE_TYPE'] = "dir"; 
	$content['fileperm'][2]['FILE_NAME'] = $content['BASEPATH'] . "images/maps/"; 
	$content['fileperm'][2]['FILE_TYPE'] = "dir"; 
	$content['fileperm'][3]['FILE_NAME'] = $content['BASEPATH'] . "images/serverlogos/"; 
	$content['fileperm'][3]['FILE_TYPE'] = "dir"; 
	$content['fileperm'][4]['FILE_NAME'] = $content['BASEPATH'] . "images/weapons/"; 
	$content['fileperm'][4]['FILE_TYPE'] = "dir"; 

//	Check file by file
	$bSuccess = true;
	for($i = 0; $i < count($content['fileperm']); $i++)
	{
		// --- Set CSS Class
		if ( $i % 2 == 0 )
			$content['fileperm'][$i]['cssclass'] = "line1";
		else
			$content['fileperm'][$i]['cssclass'] = "line2";
		// --- 

		if ( $content['fileperm'][$i]['FILE_TYPE'] == "dir" ) 
		{
			// Get Permission mask
			$perms = fileperms( $content['fileperm'][$i]['FILE_NAME'] );

			// World
			$iswriteable = (($perms & 0x0004) ? true : false) && (($perms & 0x0002) ? true : false);
			if ( $iswriteable ) 
			{
				$content['fileperm'][$i]['BGCOLOR'] = "#007700";
				$content['fileperm'][$i]['ISSUCCESS'] = "Writeable"; 
			}
			else
			{
				$content['fileperm'][$i]['BGCOLOR'] = "#770000";
				$content['fileperm'][$i]['ISSUCCESS'] = "NOT Writeable"; 
				$bSuccess = false;
				echo  "mowl1";
			}
		}
		else
		{
			if ( !is_writable($content['fileperm'][$i]['FILE_NAME']) ) 
			{
				// Try to create an empty file
				$handle = fopen( $content['fileperm'][$i]['FILE_NAME'] , "x");
				fclose($handle);
			}

			if ( is_writable($content['fileperm'][$i]['FILE_NAME']) ) 
			{
				$content['fileperm'][$i]['BGCOLOR'] = "#007700";
				$content['fileperm'][$i]['ISSUCCESS'] = "Writeable"; 
			}
			else
			{
				$content['fileperm'][$i]['BGCOLOR'] = "#770000";
				$content['fileperm'][$i]['ISSUCCESS'] = "NOT Writeable"; 
				$bSuccess = false;
				echo  "mowl2";
			}
		}
	}

	if ( !$bSuccess )
	{
		$content['NEXT_ENABLED'] = "false";
		$content['RECHECK_ENABLED'] = "true";
		$content['iserror'] = "true";
		$content['errormsg'] = "One file or directory (or more) are not writeable, please check the file permissions (chmod 777)!";
	}
}
else if ( $content['INSTALL_STEP'] == 3 )
{	
	//Preinit vars
	if ( isset($_SESSION['DB_HOST']) )
		$content['DB_HOST'] = $_SESSION['DB_HOST'];
	else
		$content['DB_HOST'] = "localhost";

	if ( isset($_SESSION['DB_PORT']) )
		$content['DB_PORT'] = $_SESSION['DB_PORT'];
	else
		$content['DB_PORT'] = "3306";

	if ( isset($_SESSION['DB_NAME']) )
		$content['DB_NAME'] = $_SESSION['DB_NAME'];
	else
		$content['DB_NAME'] = "ultrastats";

	if ( isset($_SESSION['DB_PREFIX']) )
		$content['DB_PREFIX'] = $_SESSION['DB_PREFIX'];
	else
		$content['DB_PREFIX'] = "stats_";

	if ( isset($_SESSION['DB_USER']) )
		$content['DB_USER'] = $_SESSION['DB_USER'];
	else
		$content['DB_USER'] = "user";

	if ( isset($_SESSION['DB_PASS']) )
		$content['DB_PASS'] = $_SESSION['DB_PASS'];
	else
		$content['DB_PASS'] = "";

	// Check for Error Msg
	if ( isset($_GET['errormsg']) )
	{
		$content['iserror'] = "true";
		$content['errormsg'] = DB_RemoveBadChars( urldecode($_GET['errormsg']) );
	}

	// Create Gameversions List
	$content['gen_gameversion'] = COD4;
	CreateGameVersionsList();

	// Hardcoded Default for the Game is COD4 currently ^^
	$content['GAMEVERSIONS'][COD4]['selected'] = "selected";
}
else if ( $content['INSTALL_STEP'] == 4 )
{	
	// Read vars
	if ( isset($_POST['db_host']) )
		$_SESSION['DB_HOST'] = DB_RemoveBadChars($_POST['db_host']);
	else
		RevertOneStep( $content['INSTALL_STEP']-1, "Not all parameters filled (db_host)");

	if ( isset($_POST['db_port']) )
		$_SESSION['DB_PORT'] = intval(DB_RemoveBadChars($_POST['db_port']));
	else
		RevertOneStep( $content['INSTALL_STEP']-1, "Not all parameters filled (db_host)" );

	if ( isset($_POST['db_name']) )
		$_SESSION['DB_NAME'] = DB_RemoveBadChars($_POST['db_name']);
	else
		RevertOneStep( $content['INSTALL_STEP']-1, "Not all parameters filled (db_host)" );

	if ( isset($_POST['db_prefix']) )
		$_SESSION['DB_PREFIX'] = DB_RemoveBadChars($_POST['db_prefix']);
	else
		RevertOneStep( $content['INSTALL_STEP']-1, "Not all parameters filled (db_host)" );

	if ( isset($_POST['db_user']) )
		$_SESSION['DB_USER'] = DB_RemoveBadChars($_POST['db_user']);
	else
		RevertOneStep( $content['INSTALL_STEP']-1, "Not all parameters filled (db_host)" );

	if ( isset($_POST['db_pass']) )
		$_SESSION['DB_PASS'] = DB_RemoveBadChars($_POST['db_pass']);
	else
		$_SESSION['DB_PASS'] = "";

	if ( isset($_POST['gen_gameversion']) )
		$_SESSION['GEN_GAMEVER'] = intval(DB_RemoveBadChars($_POST['gen_gameversion']));
	else
		RevertOneStep( $content['INSTALL_STEP']-1, "Not all parameters filled (gen_gameversion)" );

	// Now Check database connect
	$link_id = mysql_connect( $_SESSION['DB_HOST'], $_SESSION['DB_USER'], $_SESSION['DB_PASS']);
	if (!$link_id) 
		RevertOneStep( $content['INSTALL_STEP']-1, "Connect to " .$_SESSION['DB_HOST'] . " failed! Check Servername, Port, User and Password!<br>" . DB_ReturnSimpleErrorMsg() );
	
	// Try to select the DB!
	$db_selected = mysql_select_db($_SESSION['DB_NAME'], $link_id);
	if(!$db_selected) 
		RevertOneStep( $content['INSTALL_STEP']-1, "Cannot use database  " .$_SESSION['DB_NAME'] . "! If the database does not exists, create it or check access permissions! <br>" . DB_ReturnSimpleErrorMsg());

	// Looks good, now we write the config.php file!
	ini_set('error_reporting', E_WARNING); // Enable Warnings!

	$configfile =	'<?php
					/*
						*********************************************************************
						* Copyright by Andre Lorbach | 2006, 2007, 2008						*
						* -> www.ultrastats.org <-											*
						*																	*
						* Use this script at your own risk!									*
						* -----------------------------------------------------------------	*
						* Main Configuration File											*
						*																	*
						* -> Configuration need variables for the Database connection		*
						*********************************************************************
					*/

					// --- Database options
					$CFG[\'DBServer\'] = "' . $_SESSION["DB_HOST"] . '";
					$CFG[\'Port\'] = ' . $_SESSION["DB_PORT"] . ';
					$CFG[\'DBName\'] = "' . $_SESSION["DB_NAME"] . '"; 
					$CFG[\'TBPref\'] = "' . $_SESSION["DB_PREFIX"] . '"; 
					$CFG[\'User\'] = "' . $_SESSION["DB_USER"] . '";
					$CFG[\'Pass\'] = "' . $_SESSION["DB_PASS"] . '";

					$CFG["ShowPageRenderStats"] = 1;						// If enabled, you will see Pagerender Settings
					$CFG["ShowDebugMsg"] = 0;								// Print additional debug informations!					

?>'; //<? Only for the editor ;)

	// Create file and write config into it!
	$handle = fopen( $content['BASEPATH'] . "config.php" , "w");
	if ( $handle === false ) 
		RevertOneStep( $content['INSTALL_STEP']-1, "Coult not create the configuration file " . $content['BASEPATH'] . "config.php" . "! Check File permissions!!!" );
	
	fwrite($handle, $configfile);
	fclose($handle);
}
else if ( $content['INSTALL_STEP'] == 5 )
{
	$content['sql_sucess'] = 0;
	$content['sql_failed'] = 0;

//
	// Init $totaldbdefs
	$totaldbdefs = "";

	// Read the table GLOBAL definitions 
	ImportDataFile( $content['BASEPATH'] . "contrib/db_template.txt" );

	// Append Gamespecific definitions ^^
	if ( $_SESSION['GEN_GAMEVER'] == COD || $_SESSION['GEN_GAMEVER'] == CODUO || $_SESSION['GEN_GAMEVER'] == COD2 )
		ImportDataFile( $content['BASEPATH'] . "contrib/db_template_codww2only.txt" );
	else if ( $_SESSION['GEN_GAMEVER'] == COD4 )
		ImportDataFile( $content['BASEPATH'] . "contrib/db_template_cod4only.txt" );

	// Process definitions ^^
	if ( strlen($totaldbdefs) <= 0 )
	{
		$content['failedstatements'][ $content['sql_failed'] ]['myerrmsg'] = "Error, invalid Database Defintion File (to short!), file '" . $content['BASEPATH'] . "contrib/db_template.txt" . "'! <br>Maybe the file was not correctly uploaded?";
		$content['failedstatements'][ $content['sql_failed'] ]['mystatement'] = "";
		$content['sql_failed']++;
	}

	// Replace stats_ with the custom one ;)
	$totaldbdefs = str_replace( "`stats_", "`" . $_SESSION["DB_PREFIX"], $totaldbdefs );
	
	// Now split by sql command
	$mycommands = split( ";\r\n", $totaldbdefs );
	
	// check for different linefeed
	if ( count($mycommands) <= 1 )
		$mycommands = split( ";\n", $totaldbdefs );

	//Still only one? Abort
	if ( count($mycommands) <= 1 )
	{
		$content['failedstatements'][ $content['sql_failed'] ]['myerrmsg'] = "Error, invalid Database Defintion File (no statements found!) in '" . $content['BASEPATH'] . "contrib/db_template.txt" . "'!<br> Maybe the file was not correctly uploaded, or a strange bug with your system? Contact UltraStats forums for assistance!";
		$content['failedstatements'][ $content['sql_failed'] ]['mystatement'] = "";
		$content['sql_failed']++;
	}

	// Append INSERT Statement for Config Table to set the GameVersion and Database Version ^^!
	$mycommands[count($mycommands)] = "INSERT INTO `" . $_SESSION["DB_PREFIX"] . "config` (`name`, `value`) VALUES ('gen_gameversion', '" . $_SESSION['GEN_GAMEVER'] . "')";
	$mycommands[count($mycommands)] = "INSERT INTO `" . $_SESSION["DB_PREFIX"] . "config` (`name`, `value`) VALUES ('database_installedversion', '4')";

	// --- Now execute all commands
	ini_set('error_reporting', E_WARNING); // Enable Warnings!
	InitUltraStatsConfigFile();

	// Establish DB Connection
	DB_Connect();

	for($i = 0; $i < count($mycommands); $i++)
	{
		if ( strlen(trim($mycommands[$i])) > 1 )
		{
			$result = DB_Query( $mycommands[$i], false );
			if ($result == FALSE)
			{
				$content['failedstatements'][ $content['sql_failed'] ]['myerrmsg'] = DB_ReturnSimpleErrorMsg();
				$content['failedstatements'][ $content['sql_failed'] ]['mystatement'] = $mycommands[$i];

				// --- Set CSS Class
				if ( $content['sql_failed'] % 2 == 0 )
					$content['failedstatements'][ $content['sql_failed'] ]['cssclass'] = "line1";
				else
					$content['failedstatements'][ $content['sql_failed'] ]['cssclass'] = "line2";
				// --- 

				$content['sql_failed']++;
			}
			else
				$content['sql_sucess']++;

			// Free result
			DB_FreeQuery($result);
		}
	}
}
else if ( $content['INSTALL_STEP'] == 6 )
{
	if ( isset($_SESSION['MAIN_Username']) )
		$content['MAIN_Username'] = $_SESSION['MAIN_Username'];
	else
		$content['MAIN_Username'] = "";

	$content['MAIN_Password1'] = "";
	$content['MAIN_Password2'] = "";

	// Check for Error Msg
	if ( isset($_GET['errormsg']) )
	{
		$content['iserror'] = "true";
		$content['errormsg'] = DB_RemoveBadChars( urldecode($_GET['errormsg']) );
	}
}
else if ( $content['INSTALL_STEP'] == 7 )
{
	if ( isset($_POST['username']) )
		$_SESSION['MAIN_Username'] = DB_RemoveBadChars($_POST['username']);
	else
		RevertOneStep( $content['INSTALL_STEP']-1, "Username needs to be specified" );

	if ( isset($_POST['password1']) )
		$_SESSION['MAIN_Password1'] = DB_RemoveBadChars($_POST['password1']);
	else
		$_SESSION['MAIN_Password1'] = "";

	if ( isset($_POST['password2']) )
		$_SESSION['MAIN_Password2'] = DB_RemoveBadChars($_POST['password2']);
	else
		$_SESSION['MAIN_Password2'] = "";

	if (	
			strlen($_SESSION['MAIN_Password1']) <= 4 ||
			$_SESSION['MAIN_Password1'] != $_SESSION['MAIN_Password2'] 
		)
		RevertOneStep( $content['INSTALL_STEP']-1, "Either the password does not match or is to short!" );

	// --- Now execute all commands
	ini_set('error_reporting', E_WARNING); // Enable Warnings!
	InitUltraStatsConfigFile();

	// Establish DB Connection
	DB_Connect();

	// Everything is fine, lets go create the User!
	CreateUserName( $_SESSION['MAIN_Username'], $_SESSION['MAIN_Password1'], 0 );
}

// --- 



// --- 

// --- Parsen and Output
InitTemplateParser();
$page -> parser($content, "install.html");
$page -> output(); 
// ---

// --- Helper functions

function RevertOneStep($stepback, $errormsg)
{
	header("Location: install.php?step=" . $stepback . "&errormsg=" . urlencode($errormsg) );
	exit;
}

function ImportDataFile($szFileName)
{
	global $content, $totaldbdefs;

	// Lets read the table definitions :)
	$handle = @fopen($szFileName, "r");
	if ($handle === false) 
		RevertOneStep( $content['INSTALL_STEP']-1, "Error reading the default database defintion file " . $szFileName . "! Check if the file exists!!!" );
	else
	{
		while (!feof($handle)) 
		{
			$buffer = fgets($handle, 4096);

			$pos = strpos($buffer, "--");
			if ($pos === false)
				$totaldbdefs .= $buffer; 
			else if ( $pos > 2 && strlen( trim($buffer) ) > 1 )
				$totaldbdefs .= $buffer; 
		}
	   fclose($handle);
	}
}

// ---
?>
