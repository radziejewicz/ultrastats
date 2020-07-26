<?php
/*
	*********************************************************************
	* Copyright by Andre Lorbach | 2006-2008!							*
	* -> www.ultrastats.org <-											*
	*																	*
	* Use this script at your own risk!									*
	* -----------------------------------------------------------------	*
	* Common needed functions											*
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

// --- Basic Includes
include($gl_root_path . 'include/functions_constants.php');
include($gl_root_path . 'include/functions_themes.php');
// --- 

// --- Define Basic vars
$RUNMODE = RUNMODE_WEBSERVER;
$DEBUGMODE = DEBUG_INFO;

// --- Disable ARGV setting @webserver!
ini_set( "register_argc_argv", "Off" );
// --- 

// Default language
$LANG_EN = "en";	// Used for fallback
$LANG = "en";		// Default language

// Default Template vars
$content['BUILDNUMBER'] = "0.2.144";
$content['TITLE'] = "Ultrastats - Release " . $content['BUILDNUMBER'];	// Title of the Page 
$content['BASEPATH'] = $gl_root_path;
$content['EXTRA_METATAGS'] = "";
// --- 

function InitBasicUltraStats()
{
	// Needed to make global
	global $CFG, $gl_root_path, $content;

	// check RunMode first!
	CheckAndSetRunMode();

	// Get and Set RunTime Informations
	InitRuntimeInformations();

	// Set the default line sep
	SetLineBreakVar();

	// Start the PHP Session
	StartPHPSession();
}

function InitUltraStatsConfigFile()
{
	// Needed to make global
	global $CFG, $gl_root_path, $content;

	if ( file_exists($gl_root_path . 'config.php') && GetFileLength($gl_root_path . 'config.php') > 0 )
	{
		// Include the main config
		include_once($gl_root_path . 'config.php');
		
		// Easier DB Access
		define('STATS_ALIASES', $CFG['TBPref'] . "aliases");
		define('STATS_CHAT', $CFG['TBPref'] . "chat");
		define('STATS_CONFIG', $CFG['TBPref'] . "config");
		define('STATS_CONSOLIDATED', $CFG['TBPref'] . "consolidated");
		define('STATS_GAMEACTIONS', $CFG['TBPref'] . "gameactions");
		define('STATS_DAMAGETYPES', $CFG['TBPref'] . "damagetypes");
		define('STATS_GAMETYPES', $CFG['TBPref'] . "gametypes");
		define('STATS_HITLOCATIONS', $CFG['TBPref'] . "hitlocations");
		define('STATS_LANGUAGE_STRINGS', $CFG['TBPref'] . "language_strings");
		define('STATS_MAPS', $CFG['TBPref'] . "maps");
		define('STATS_PLAYER_KILLS', $CFG['TBPref'] . "player_kills");
		define('STATS_PLAYERS', $CFG['TBPref'] . "players");
		define('STATS_ROUNDS', $CFG['TBPref'] . "rounds");
		define('STATS_ROUNDACTIONS', $CFG['TBPref'] . "roundactions");
		define('STATS_SERVERS', $CFG['TBPref'] . "servers");
		define('STATS_TIME', $CFG['TBPref'] . "time");
		define('STATS_USERS', $CFG['TBPref'] . "users");
		define('STATS_WEAPONS', $CFG['TBPref'] . "weapons");
		define('STATS_PLAYERS_STATIC', $CFG['TBPref'] . "players_static");
		define('STATS_PLAYERS_TOPALIASES', $CFG['TBPref'] . "players_topalias");
		define('STATS_WEAPONS_PERSERVER', $CFG['TBPref'] . "weapons_perserver");

		// For ShowPageRenderStats
		if ( $CFG['ShowPageRenderStats'] == 1 )
		{
			$content['ShowPageRenderStats'] = "true";
			InitPageRenderStats();
		}
	}
	else
	{
		// Check for installscript!
		if ( file_exists($content['BASEPATH'] . "install.php") ) 
			$strinstallmsg = '<br><br>' 
							. '<center><b>Click <a href="' . $content['BASEPATH'] . 'install.php">here</a> to Install UltraStats!</b><br><br>'
							. 'See the Installation Guides for more Details!<br>'
							. '<a href="docs/installation.htm" target="_blank">English Installation Guide</a>&nbsp;|&nbsp;'
							. '<a href="docs/installation_de.htm" target="_blank">German Installation Guide</a><br><br>' 
							. 'Also take a look to the <a href="docs/readme.htm" target="_blank">Readme</a> for some basics around UltraStats!<br>'
							. '</center>';
		else
			$strinstallmsg = "";
		DieWithErrorMsg( 'Error, main configuration file is missing!' . $strinstallmsg );
	}
}

function GetFileLength($szFileName)
{
	if ( is_file($szFileName) )
		return filesize($szFileName);
	else
		return 0;
}

function InitUltraStats()
{
	// Needed to make global
	global $CFG, $gl_root_path, $content;

	// Init Basics which do not need a database
	InitBasicUltraStats();
	
	// Will init the config file!
	InitUltraStatsConfigFile();

	// Establish DB Connection
	DB_Connect();

	// Now load the Page configuration values
	InitConfigurationValues();

	// Now Create Themes List because we haven't the config before!
	CreateThemesList();

	// Create Language List
	CreateLanguageList();

	// Create Gameversions List
	CreateGameVersionsList();

	// Create ParseByTypes List
	CreateParseByTypesList();

	// --- Created Banned Players Filter
	CreateBannedPlayerFilter();
	// --- 

	// --- Enable PHP Debug Mode 
	InitPhpDebugMode();
	// --- 
}

function InitPhpDebugMode()
{
	global $content;

	// --- Set Global DEBUG Level!
	if ( $content['gen_phpdebug'] == "yes" )
		ini_set( "error_reporting", E_ALL ); // ALL PHP MESSAGES!
	else
		ini_set( "error_reporting", E_ERROR ); // ONLY PHP ERROR'S!
	// --- 
}


function CreateGameVersionsList()
{
	global $content;

	// --- gen_gameversion
	$content['GAMEVERSIONS'][COD]['gamever'] = COD;
	$content['GAMEVERSIONS'][COD]['gamevertitle'] = LN_GEN_COD;
	if ( $content['gen_gameversion'] == $content['GAMEVERSIONS'][COD]['gamever'] ) { $content['GAMEVERSIONS'][COD]['selected'] = "selected"; } else { $content['GAMEVERSIONS'][COD]['selected'] = ""; }

	$content['GAMEVERSIONS'][CODUO]['gamever'] = CODUO;
	$content['GAMEVERSIONS'][CODUO]['gamevertitle'] = LN_GEN_CODUO;
	if ( $content['gen_gameversion'] == $content['GAMEVERSIONS'][CODUO]['gamever'] ) { $content['GAMEVERSIONS'][CODUO]['selected'] = "selected"; } else { $content['GAMEVERSIONS'][CODUO]['selected'] = ""; }

	$content['GAMEVERSIONS'][COD2]['gamever'] = COD2;
	$content['GAMEVERSIONS'][COD2]['gamevertitle'] = LN_GEN_COD2;
	if ( $content['gen_gameversion'] == $content['GAMEVERSIONS'][COD2]['gamever'] ) { $content['GAMEVERSIONS'][COD2]['selected'] = "selected"; } else { $content['GAMEVERSIONS'][COD2]['selected'] = ""; }

	$content['GAMEVERSIONS'][COD4]['gamever'] = COD4;
	$content['GAMEVERSIONS'][COD4]['gamevertitle'] = LN_GEN_COD4;
	if ( $content['gen_gameversion'] == $content['GAMEVERSIONS'][COD4]['gamever'] ) { $content['GAMEVERSIONS'][COD4]['selected'] = "selected"; } else { $content['GAMEVERSIONS'][COD4]['selected'] = ""; }
	// ---
}

function CreateParseByTypesList()
{
	global $content;

	// --- gen_gameversion
	$content['PARSEBYTYPES'][PARSEBY_GUIDS]['parsebytype'] = PARSEBY_GUIDS;
	$content['PARSEBYTYPES'][PARSEBY_GUIDS]['parsebytypetitle'] = LN_GEN_PARSEBY_GUIDS;
	if ( $content['gen_parseby'] == $content['PARSEBYTYPES'][PARSEBY_GUIDS]['parsebytype'] ) { $content['PARSEBYTYPES'][PARSEBY_GUIDS]['selected'] = "selected"; } else { $content['PARSEBYTYPES'][PARSEBY_GUIDS]['selected'] = ""; }

	$content['PARSEBYTYPES'][PARSEBY_PLAYERNAME]['parsebytype'] = PARSEBY_PLAYERNAME;
	$content['PARSEBYTYPES'][PARSEBY_PLAYERNAME]['parsebytypetitle'] = LN_GEN_PARSEBY_PLAYERNAME;
	if ( $content['gen_parseby'] == $content['PARSEBYTYPES'][PARSEBY_PLAYERNAME]['parsebytype'] ) { $content['PARSEBYTYPES'][PARSEBY_PLAYERNAME]['selected'] = "selected"; } else { $content['PARSEBYTYPES'][PARSEBY_PLAYERNAME]['selected'] = ""; }
	// ---
}

function CheckAndSetRunMode()
{
	global $RUNMODE;
	// Set to command line mode if argv is set! 
//FICKU PHP!	if ( isset($_SERVER["argc"]) && $_SERVER["argc"] > 1 )
	if ( !isset($_SERVER["GATEWAY_INTERFACE"]) )
		$RUNMODE = RUNMODE_COMMANDLINE;
}

function InitRuntimeInformations()
{
	global $content;

	$content['sqltmpfile'] = $content['BASEPATH'] . "tmp.sql";

	if ( strpos(PHP_OS, "WIN") !== false )
	{
		// Windows 
		$content['MYSQLPATH'] = MYSQLPATH_WINDOWS;
	}
	else
	{
		// Linux
		$content['MYSQLPATH'] = MYSQLPATH_WINDOWS;
	}

	// Check Access to the file!
	if ( is_file($content['MYSQLPATH']) )
	{
		// Try to create file if not there
		if ( !is_file($content['sqltmpfile']) ) 
		{
			$handle = fopen( $content['sqltmpfile'] , "x");
			fclose($handle);
		}

		// Check if writeable
		if ( is_writable($content['sqltmpfile']) ) 
			$content['MYSQL_BULK_MODE'] = true;
		else
			$content['MYSQL_BULK_MODE'] = false;
	}
	else
		$content['MYSQL_BULK_MODE'] = false;

// DEBUG TEST!
	$content['MYSQL_BULK_MODE'] = false;
}

function CreateDebugModes()
{
	global $content;

	$content['DBGMODES'][0]['DisplayName'] = STR_DEBUG_ULTRADEBUG;
	if ( $content['parser_debugmode'] == $content['DBGMODES'][0]['DisplayName'] ) { $content['DBGMODES'][0]['selected'] = "selected"; } else { $content['DBGMODES'][0]['selected'] = ""; }
	$content['DBGMODES'][1]['DisplayName'] = STR_DEBUG_DEBUG;
	if ( $content['parser_debugmode'] == $content['DBGMODES'][1]['DisplayName'] ) { $content['DBGMODES'][1]['selected'] = "selected"; } else { $content['DBGMODES'][1]['selected'] = ""; }
	$content['DBGMODES'][2]['DisplayName'] = STR_DEBUG_INFO;
	if ( $content['parser_debugmode'] == $content['DBGMODES'][2]['DisplayName'] ) { $content['DBGMODES'][2]['selected'] = "selected"; } else { $content['DBGMODES'][2]['selected'] = ""; }
	$content['DBGMODES'][3]['DisplayName'] = STR_DEBUG_WARN;
	if ( $content['parser_debugmode'] == $content['DBGMODES'][3]['DisplayName'] ) { $content['DBGMODES'][3]['selected'] = "selected"; } else { $content['DBGMODES'][3]['selected'] = ""; }
	$content['DBGMODES'][4]['DisplayName'] = STR_DEBUG_ERROR;
	if ( $content['parser_debugmode'] == $content['DBGMODES'][4]['DisplayName'] ) { $content['DBGMODES'][4]['selected'] = "selected"; } else { $content['DBGMODES'][4]['selected'] = ""; }
}


function InitServerCount()
{
	global $content;

	$result = DB_Query("SELECT count(id) as servercount FROM " . STATS_SERVERS);
	$rows = DB_GetAllRows($result, true);

	if ( isset($rows ) )
		$content['NUMSERVERS'] = $rows[0]['servercount'];
	else
		$content['NUMSERVERS'] = "0";
}

function InitLastDatabaseUpdateTime()
{
	global $content;
	if ( isset($content['last_dbupdate']) )
		$content['LASTDBUPDATE'] = print date('Y-m-d h:i:s', $content['last_dbupdate']);
	else
		$content['LASTDBUPDATE'] = "Never";
}

function InitFrontEndVariables()
{
	global $content;

	$content['MENU_FOLDER_OPEN'] = "image=" . $content['BASEPATH'] . "images/icons/folder_closed.png";
	$content['MENU_FOLDER_CLOSED'] = "overimage=" . $content['BASEPATH'] . "images/icons/folder.png";
	$content['MENU_HOMEPAGE'] = "image=" . $content['BASEPATH'] . "images/icons/home.png";
	$content['MENU_LINK'] = "image=" . $content['BASEPATH'] . "images/icons/link.png";
	$content['MENU_PREFERENCES'] = "image=" . $content['BASEPATH'] . "images/icons/preferences.png";
	$content['MENU_ADMINENTRY'] = "image=" . $content['BASEPATH'] . "images/icons/star_blue.png";
	$content['MENU_ADMINLOGOFF'] = "image=" . $content['BASEPATH'] . "images/icons/exit.png";
	$content['MENU_ADMINUSERS'] = "image=" . $content['BASEPATH'] . "images/icons/businessmen.png";
	$content['MENU_ADMINPLAYERS'] = "image=" . $content['BASEPATH'] . "images/icons/businessman_preferences.png";
	$content['MENU_ADMINSERVERS'] = "image=" . $content['BASEPATH'] . "images/icons/server.png";
	$content['MENU_SEARCH'] = "image=" . $content['BASEPATH'] . "images/icons/view.png";
	$content['MENU_SEARCH'] = "image=" . $content['BASEPATH'] . "images/icons/view.png";
	$content['MENU_SEARCH'] = "image=" . $content['BASEPATH'] . "images/icons/view.png";
	$content['MENU_SELECTION_DISABLED'] = "image=" . $content['BASEPATH'] . "images/icons/selection.png";
	$content['MENU_SELECTION_ENABLED'] = "image=" . $content['BASEPATH'] . "images/icons/selection_delete.png";

	// Get and Set ServerID Value!
	if ( isset($_GET['serverid']) )
	{
		if ( intval($_GET['serverid']) > 0 )
			$content['serverid'] = $_GET['serverid'];
	}

}

// Lang Helper for Strings with ONE variable
function GetAndReplaceLangStr( $strlang, $param1 = "", $param2 = "", $param3 = "", $param4 = "", $param5 = "" )
{
	$strfinal = str_replace ( "%1", $param1, $strlang );
	if ( strlen($param2) > 0 )
		$strfinal = str_replace ( "%1", $param2, $strfinal );
	if ( strlen($param3) > 0 )
		$strfinal = str_replace ( "%1", $param3, $strfinal );
	if ( strlen($param4) > 0 )
		$strfinal = str_replace ( "%1", $param4, $strfinal );
	if ( strlen($param5) > 0 )
		$strfinal = str_replace ( "%1", $param5, $strfinal );
	
	// And return
	return $strfinal;
}

function InitConfigurationValues()
{
	global $content, $LANG;

	$result = DB_Query("SELECT * FROM " . STATS_CONFIG);
	$rows = DB_GetAllRows($result, true, true);

	if ( isset($rows ) )
	{
		for($i = 0; $i < count($rows); $i++)
			$content[ $rows[$i]['name'] ] = $rows[$i]['value'];
	}
	// General defaults 
	// --- Language Handling
	if ( !isset($content['gen_lang']) ) { $content['gen_lang'] = "en"; }
	if ( VerifyLanguage($content['gen_lang']) )
		$LANG = $content['gen_lang'];
	else
	{
		// Fallback!
		$LANG = "en";
		$content['gen_lang'] = "en";
	}
	
	// Now check for custom LANG!
	if ( isset($_SESSION['CUSTOM_LANG']) && VerifyLanguage($_SESSION['CUSTOM_LANG']) )
	{
		$content['user_lang'] = $_SESSION['CUSTOM_LANG'];
		$LANG = $content['user_lang'];
	}
	else
		$content['user_lang'] = $content['gen_lang'];
	// --- 

	// --- Game Version
	// Set Default!	- TODO, set in install.php!
	if ( !isset($content['gen_gameversion']) ) 
	{
		$content['gen_gameversion'] = COD2; 
		$content['gen_gameversion_picpath'] = "cod"; 
	}
	else
	{
		if (	$content['gen_gameversion'] == COD || 
				$content['gen_gameversion'] == CODUO || 
				$content['gen_gameversion'] == COD2 )
			$content['gen_gameversion_picpath'] = "cod"; 
		else if($content['gen_gameversion'] == COD4)
			$content['gen_gameversion_picpath'] = "cod4"; 
	}
	// --- 

	// --- Parseby Type
	// Set Default!	- TODO, set in install.php!
	if ( !isset($content['gen_parseby']) ) { $content['gen_parseby'] = PARSEBY_GUIDS; }
	// --- 

	// --- PHP Debug Mode
	if ( !isset($content['gen_phpdebug']) ) { $content['gen_phpdebug'] = "no"; }
	// --- 

	// Web defaults 
	// --- Theme Handling
	if ( !isset($content['web_theme']) ) { $content['web_theme'] = "default"; }
	if ( isset($_SESSION['CUSTOM_THEME']) && VerifyTheme($_SESSION['CUSTOM_THEME']) )
		$content['user_theme'] = $_SESSION['CUSTOM_THEME'];
	else
		$content['user_theme'] = $content['web_theme'];

	//Init Theme About Info ^^
	InitThemeAbout($content['user_theme']);
	// --- 
	if ( !isset($content['web_toprounds']) ) { $content['web_toprounds'] = 50; }
	if ( !isset($content['web_topplayers']) ) { $content['web_topplayers'] = 50; }
	if ( !isset($content['web_detaillistsplayers']) ) { $content['web_detaillistsplayers'] = 20; }
	if ( !isset($content['web_minkills']) ) { $content['web_minkills'] = 25; }
	if ( !isset($content['web_mintime']) ) { $content['web_mintime'] = 600; }
	if ( !isset($content['web_maxpages']) ) { $content['web_maxpages'] = 25; }
	if ( !isset($content['web_maxmapsperpage']) ) { $content['web_maxmapsperpage'] = 10; }
	if ( !isset($content['web_medals']) ) { $content['web_medals'] = "yes"; }

	// Admin Interface
	if ( !isset($content['admin_maxplayers']) ) { $content['admin_maxplayers'] = 30; }
	if ( !isset($content['admin_maxpages']) ) { $content['admin_maxpages'] = 20; }

	// Parser defaults 
	if ( !isset($content['parser_debugmode']) ) { $content['parser_debugmode'] = STR_DEBUG_INFO; } SetDebugModeFromString( $content['parser_debugmode'] );
	if ( !isset($content['parser_disablelastline']) ) { $content['parser_disablelastline'] = "no"; }
	if ( !isset($content['parser_chatlogging']) ) { $content['parser_chatlogging'] = "yes"; }

	// Database Version Checker! 
	if ( $content['database_internalversion'] > $content['database_installedversion'] )
	{	
		// Database is out of date, we need to upgrade
		$content['database_forcedatabaseupdate'] = "yes"; 
	}

	// Init other things which are needed
	InitServerCount();
	InitLastDatabaseUpdateTime();
	InitFrontEndVariables();
}

function SetDebugModeFromString( $facility )
{
	global $DEBUGMODE;

	switch ( $facility )
	{
		case STR_DEBUG_ULTRADEBUG:
			$DEBUGMODE = DEBUG_ULTRADEBUG;
			break;
		case STR_DEBUG_DEBUG:
			$DEBUGMODE = DEBUG_DEBUG;
			break;
		case STR_DEBUG_INFO:
			$DEBUGMODE = DEBUG_INFO;
			break;
		case STR_DEBUG_WARN:
			$DEBUGMODE = DEBUG_WARN;
			break;
		case STR_DEBUG_ERROR:
			$DEBUGMODE = DEBUG_ERROR;
			break;
	}
}


function InitPageRenderStats()
{
	global $gl_starttime, $querycount;
	$gl_starttime = microtime_float();
	$querycount = 0;
}

function FinishPageRenderStats( &$mycontent)
{
	global $gl_starttime, $querycount;

	$endtime = microtime_float();
	$mycontent['PAGERENDERTIME'] = number_format($endtime - $gl_starttime, 4, '.', '');
	$mycontent['TOTALQUERIES'] = $querycount;
}

function microtime_float()
{
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}

function SetLineBreakVar()
{
	// Used for some functions
	global $RUNMODE, $linesep;

	if		( $RUNMODE == RUNMODE_COMMANDLINE )
		$linesep = "\r\n";
	else if	( $RUNMODE == RUNMODE_WEBSERVER )
		$linesep = "<br>";
}

function CheckUrlOrIP($ip) 
{
	$long = ip2long($ip); 
	if ( $long == -1 ) 
		return false; 
	else
		return true; 
}

function DieWithErrorMsg( $szerrmsg )
{
	global $RUNMODE, $content;
	if		( $RUNMODE == RUNMODE_COMMANDLINE )
	{
		print("\n\n\t\tCritical Error occured\n");
		print("\t\tErrordetails:\t" . $szerrmsg . "\n");
		print("\t\tTerminating now!\n");
	}
	else if	( $RUNMODE == RUNMODE_WEBSERVER )
	{
		print("<html><head><link rel=\"stylesheet\" href=\"" . $content['BASEPATH'] . "admin/css/admin.css\" type=\"text/css\"></head><body>");
		print("<table width=\"600\" align=\"center\" class=\"with_border\"><tr><td><center><H3><font color='red'>Critical Error occured</font></H3><br></center>");
		print("<B>Errordetails:</B><BR>" .  $szerrmsg);
		print("</td></tr></table>");
	}
	exit;
}

function DieWithFriendlyErrorMsg( $szerrmsg )
{
	//TODO: Make with template
	print("<html><body>");
	print("<center><H3><font color='red'>Error occured</font></H3><br></center>");
	print("<B>Errordetails:</B><BR>" .  $szerrmsg);
	exit;
}


function InitTemplateParser()
{
	global $page, $gl_root_path;
	// -----------------------------------------------
	// Create Template Object and set some variables for the templates
	// -----------------------------------------------
	$page = new Template();
	$page -> set_path ( $gl_root_path . "templates/" );
}

function VerifyLanguage( $mylang ) 
{ 
	global $gl_root_path;

	if ( is_dir( $gl_root_path . 'lang/' . $mylang ) )
		return true;
	else
		return false;
}

function IncludeLanguageFile( $langfile ) 
{
	global $LANG, $LANG_EN; 

	if ( file_exists( $langfile ) )
		include( $langfile );
	else
	{
		$langfile = str_replace( $LANG, $LANG_EN, $langfile );
		include( $langfile );
	}
}

function RedirectPage( $newpage )
{
	header("Location: $newpage");
	exit;
}

function RedirectResult( $szMsg, $newpage )
{
	header("Location: result.php?msg=" . urlencode($szMsg) . "&redir=" . urlencode($newpage));
	exit;
}

function GetTimeString($mysecs)
{
	// Time Played
	if (intval($mysecs) > 0 )
	{
		// Hours
		$h = $mysecs /3600;
		$HOURS = sprintf("%02d",$h);

		$m = ($h - $HOURS) * 60;
		$MIN = sprintf("%02d",$m);

		$s = ($m - $MIN) * 60 ;
		$SEC = sprintf("%02d",$s);
		return "$HOURS:$MIN:$SEC";
	}
	// END Time Played  
}

function GetTimeStringDays($mysecs)
{
	// Time Played
	if (intval($mysecs) > 0 )
	{
		// Days
		$DAYS = intval($mysecs / 86400);
		
		// Hours
		$h = intval( ($mysecs - ($DAYS*86400)) / 3600 );
		$HOURS = sprintf("%02d",$h);

		// Minutes
		$m = intval(($mysecs - (($DAYS*86400) + ($h*3600))) / 60);
		$MIN = sprintf("%02d",$m);
		
		// Seconds
		$s = intval($mysecs - (($DAYS*86400) + ($h*3600) + ($m*60)));
		$SEC = sprintf("%02d",$s);
		
		// Return
		return "$DAYS Days - $HOURS:$MIN:$SEC";
	}
	// END Time Played  
}

function GetPlayerHtmlNameFromID($myplayedid)
{
	global $serverwherequery_and;

	if ( !isset($myplayedid) ||	intval($myplayedid) <= 0 )
		return false;
	else
	{
		// Try to get Playname from DB
		// Get ServerDetails
		$result = DB_Query("SELECT Alias, AliasAsHtml FROM " . STATS_ALIASES . " WHERE PLAYERID = " . $myplayedid . $serverwherequery_and . " ORDER BY Count DESC LIMIT 1");
		$playerdetails = DB_GetAllRows($result, true);
		if ( isset( $playerdetails ) )
		{
			if ( !isset($playerdetails[0]['AliasAsHtml']) )
				return $playerdetails[0]['Alias'];
			else
				return $playerdetails[0]['AliasAsHtml'];
		}
		else
			return false;
	}
}

function StripColorCodesFromString($mystr)
{
	// --- First check if there is a ^ char in the name | if not skip the following processing
	$test_pos = strpos($mystr, "^", 0);
	if ($test_pos === false)
		return $mystr;
	// ---
	
	// Tricky REGEX :)| \^ matches a ^ and [^\^] matches any character except ^. So we also take care on double ^^ 
	return preg_replace("/\^[^\^]/", "", $mystr);
}

function GetPlayerNameAsWithHTMLCodes($myName)
{
	// First of all replace special characters with valid HTML representations!
	$searchfor = array( "&", "<", ">" );
	$replacewith = array( "&amp;", "&lt;", "&gt;" );
	$myName = str_replace ( $searchfor, $replacewith, $myName);
	return $myName;
}

function GetPlayerNameAsHTML($myName)
{
	// Local vars
	$finished = false;
	$strposbegin = 0;
	$strposend = 0;

	$colorcode = "";
	$tempstr = "";
	$tempreplace = "";
	$myHtmlName = $myName;
	
	// First of all replace special characters with valid HTML representations!
	$myName = GetPlayerNameAsWithHTMLCodes( $myName );

	// --- First check if there is a ^ char in the name | if not skip the following processing
	$test_pos = strpos($myName, "^", $strposbegin);
	if ($test_pos === false)
		return $myName;
	// ---

	// Find all color codes and make them "seeable" for html
	while ($finished == false)
	{
		$strposbegin = strpos($myName, "^", $strposbegin);
		$strend = strpos($myName, "^", ($strposbegin+1) );

		if ($strend === false)
			$finished = true;

		$colorcode = substr($myName, $strposbegin, 2);
		$tempreplace = "<font color=\"".GetColourNameFromCode($colorcode)."\">";

		if ($finished)		
			$tempstr = substr($myName, $strposbegin+2);								// Whole string
		else
			$tempstr = substr($myName, $strposbegin+2, $strend - $strposbegin -2);	// Only Part of string
		
		$tempreplace .= $tempstr;
		$tempreplace .= "</font>";

		$myHtmlName = str_replace($colorcode.$tempstr, $tempreplace, $myHtmlName);
		$strposbegin++;
	}
	return $myHtmlName;
}

function GetColourNameFromCode($MyColourCode)
{
	switch($MyColourCode)
	{
		case "^0":
			return "#000000";	// BLACK 
		case "^1":
			return "#FF0000";	// RED 
		case "^2":
			return "#80FF00";	// GREEN 
		case "^3":
			return "#FFFF00";	// YELLOW 
		case "^4":
			return "#0000FF";	// BLUE 
		case "^5":
			return "#00FFFF";	// CYAN 
		case "^6":
			return "#FF00FF";	// MAGENTA 
		case "^7":
			return "#FFFFFF";	// WHITE 
		case "^8":
			return "#AD19D5";	// PURPLE 
		case "^9":
			return "#A7F4F1";	// CYAN 
		case "^a":
			return "#48E2DC";	// Cyan 
		case "^b":
			return "#FAE3A9";	// Orange 
		case "^c":
			return "#E5A7E5";	// Pink 
		case "^d":
			return "#DEF7FE";	// Blue 
		case "^e":
			return "#D71BBC";	// Pink 
		case "^f":
			return "#CFFAFB";	// Blue 
		case "^g":
			return "#D1F783";	// Green 
		case "^h":
			return "#A91859";	// Red 
		case "^i":
			return "#789BCB";	// Blue 
		case "^j":
			return "#9BEE44";	// Green 
		case "^k":
			return "#FEFCDA";	// Tan 
		case "^l":
			return "#EEFBFD";	// Blue 
		case "^m":
			return "#DB79DA";	// Pink 
		case "^n":
			return "#ACC997";	// Green 
		case "^o":
			return "#E9C9CB";	// Pink 
		case "^p":
			return "#84D6C0";	// Cyan 
		case "^q":
			return "#FAEEF1";	// Tan 
		case "^r":
			return "#E5A7E5";	// Pink 
		case "^s":
			return "#6115A3";	// Blue 
		case "^t":
			return "#B31164";	// Red 
		case "^u":
			return "#846FD0";	// Blue 
		case "^v":
			return "#5BE593";	// Green 
		case "^w":
			return "#000000";	// Black 
		case "^x":
			return "#0C2C4C";	// Blue 
		case "^y":
			return "#48E2DC";	// Blue 
		case "^z":
			return "#EFD497";	// Orange 
		case "^A":
			return "#7918CE";	// Purple 
		case "^B":
			return "#9CED8B";	// Green 
		case "^C":
			return "#E3AA87";	// Orange 
		case "^D":
			return "#889CB8";	// Blue 
		case "^E":
			return "#CEEF99";	// Green 
		case "^F":
			return "#1F5C9A";	// Blue 
		case "^G":
			return "#A8EB95";	// Green 
		case "^H":
			return "#D5CCCC";	// Pink 
		case "^I":
			return "#C0F0F0";	// Blue 
		case "^J":
			return "#F5FCEF";	// Gray 
		case "^K":
			return "#F6FEDC";	// Green 
		case "^L":
			return "#F1DAF7";	// Purple 
		case "^M":
			return "#44E0BA";	// Cyan 
		case "^N":
			return "#EBDA6F";	// Orange 
		case "^O":
			return "#FFFFFF";	// White 
		case "^P":
			return "#35D936";	// Green 
		case "^Q":
			return "#F79DF1";	// Pink 
		case "^R":
			return "#7686D7";	// Blue 
		case "^S":
			return "#9CED8B";	// Green 
		case "^T":
			return "#DDAB9F";	// Orange 
		case "^U":
			return "#CDA3E5";	// Purple 
		case "^V":
			return "#DBFAF8";	// Gray 
		case "^W":
			return "#9F25B8";	// Purple 
		case "^X":
			return "#6FF2EE";	// Cyan 
		case "^Y":
			return "#D4F353";	// Green 
		case "^Z":
			return "#F8EEFD";	// Purple 
		case "^!":
			return "#000000";	// BLACK 
		case "^@":
			return "#F7D43B";	// ORANGE 
		case "^#":
			return "#000000";	// BLACK 
		case "^$":
			return "#C71819";	// RED 
		case "^&":
			return "#A39DC5";	// BLUE 
		case "^*":
			return "#F7FCFB";	// WHITE 
		case "^(":
			return "#CD4624";	// PINK 
		case "^)":
			return "#40E3E7";	// CYAN 
		case "^_":
			return "#A82526";	// RED 
		case "^-":
			return "#8A8789";	// GRAY 
		case "^+":
			return "#FFFFFF";	// WHITE 
		case "^=":
			return "#BED4C9";	// CYAN 
		case "^{":
			return "#CD4624";	// RED 
		case "^[":
			return "#A5EEE1";	// CYAN 
		case "^}":
			return "#6E939B";	// BLUE 
		case "^]":
			return "#89EEE4";	// CYAN 
		case "^|":
			return "#D6F7B5";	// GREEN 
		case "^\\":
			return "#D6ABCE";	// PINK 
		case "^:":
			return "#FFFFFF";	// WHITE 
		case "^\'":
			return "#8A8789";	// GRAY 
		case "^<":
			return "#2CE02F";	// GREEN 
		case "^,":
			return "#DDE8D6";	// GRAY 
		case "^>":
			return "#81F2F1";	// CYAN 
		case "^.":
			return "#A39DC5";	// BLUE 
		case "^?":
			return "#D6F7B5";	// GREEN 
		case "^/":
			return "#411959";	// PURPLE
	}
}

// --- FTP Helper functions
function ParseFtpValuesFromURL( $ftpUrl ) 
{
	//preinit return array
	$ftpvalues['ftpserver'] = "";
	$ftpvalues['ftpport'] = 21;
	$ftpvalues['username'] = "";
	$ftpvalues['password'] = "";
	$ftpvalues['ftppath'] = "";
	$ftpvalues['ftpfilename'] = "";

	if ( strpos($ftpUrl, "@") !== false )
	{	//Username and maybe password is given
		$tmparray = explode("@", $ftpUrl);

		// Set Username 
		$ftpvalues['username'] = substr( $tmparray[0], 6 );

		// Check if Password is given
		if ( strpos($ftpvalues['username'], ":") !== false )
		{
			$tmparray2 = explode(":", $ftpvalues['username']);
			$ftpvalues['username'] = $tmparray2[0];
			$ftpvalues['password'] = $tmparray2[1];
		}
		
		// Get FTP Servername
		$ftpvalues['ftpserver'] = substr( $tmparray[1], 0,  strpos($tmparray[1], "/") );

		// Get FTP Path
		$ftpvalues['ftppath'] = substr( $tmparray[1], strpos($tmparray[1], "/"), strrpos($tmparray[1], "/")-strpos($tmparray[1], "/")+1 );

		// Get the Logfilename
		$ftpvalues['ftpfilename'] = substr( $tmparray[1], strrpos($tmparray[1], "/")+1 );
	}
	else
	{	
		// Get FTP Servername
		$ftpvalues['ftpserver'] = substr( $ftpUrl, 5,  strpos($ftpUrl, "/") );
		
		// Get FTP Path
		$ftpvalues['ftppath'] = substr( $ftpUrl, strpos($ftpUrl, "/"), strrpos($ftpUrl, "/") );

		// Get the Logfilename
		$ftpvalues['ftpfilename'] = substr( $ftpUrl, strrpos($ftpUrl, "/") );
	}
	
	// Check if FTP Server Port is specified
	if ( strpos($ftpvalues['ftpserver'], ":") !== false )
	{
		$tmparray2 = explode(":", $ftpvalues['ftpserver']);
		$ftpvalues['ftpserver'] = $tmparray2[0];
		$ftpvalues['ftpport'] = intval($tmparray2[1]);
	}

	// return results
	return $ftpvalues;
}

function CleanUpArray(&$myArray)
{
	array_splice($myArray);
	
	// Unset mainentry!
	unset($myArray);
}

// --- BEGIN Usermanagement Function --- 
function StartPHPSession()
{
	global $RUNMODE;
	if ( $RUNMODE == RUNMODE_WEBSERVER )
	{
		// This will start the session
		if (session_id() == "")
			session_start();

		if ( !isset($_SESSION['SESSION_STARTED']) )
			$_SESSION['SESSION_STARTED'] = "true";
	}
}

function CheckForUserLogin( $isloginpage, $isUpgradePage = false )
{
	global $content; 

	if ( isset($_SESSION['SESSION_LOGGEDIN']) )
	{
		if ( !$_SESSION['SESSION_LOGGEDIN'] ) 
			RedirectToUserLogin();
		else
		{
			$content['SESSION_LOGGEDIN'] = "true";
			$content['SESSION_USERNAME'] = $_SESSION['SESSION_USERNAME'];
		}

		// New, Check for database Version and may redirect to updatepage!
		if (	isset($content['database_forcedatabaseupdate']) && 
				$content['database_forcedatabaseupdate'] == "yes" && 
				$isUpgradePage == false 
			)
				RedirectToDatabaseUpgrade();
	}
	else
	{
		if ( $isloginpage == false )
			RedirectToUserLogin();
	}

}

function CreateUserName( $username, $password, $access_level )
{
	$md5pass = md5($password);
	$result = DB_Query("SELECT username FROM " . STATS_USERS . " WHERE username = '" . $username . "'");
	$rows = DB_GetAllRows($result, true);
	if ( isset($rows) )
	{
		DieWithFriendlyErrorMsg( "User $username already exists!" );

		// User not created!
		return false;
	}
	else
	{
		// Create User
		$result = DB_Query("INSERT INTO " . STATS_USERS . " (username, password, access_level) VALUES ('$username', '$md5pass', $access_level)");
		DB_FreeQuery($result);

		// Success
		return true;
	}
}

function CheckUserLogin( $username, $password )
{
	global $content, $CFG;

	// TODO: SessionTime and AccessLevel check

	$md5pass = md5($password);
	$sqlselect = "SELECT access_level FROM " . STATS_USERS . " WHERE username = '" . $username . "' and password = '" . $md5pass . "'";
	$result = DB_Query($sqlselect);
	$rows = DB_GetAllRows($result, true);
	if ( isset($rows) )
	{
		$_SESSION['SESSION_LOGGEDIN'] = true;
		$_SESSION['SESSION_USERNAME'] = $username;
		$_SESSION['SESSION_ACCESSLEVEL'] = $rows[0]['access_level'];
		
		$content['SESSION_LOGGEDIN'] = "true";
		$content['SESSION_USERNAME'] = $username;

		// Success !
		return true;
	}
	else
	{
		if ( $CFG['ShowDebugMsg'] == 1 )
			DieWithFriendlyErrorMsg( "Debug Error: Could not login user '" . $username . "' <br><br><B>Sessionarray</B> <pre>" . var_export($_SESSION, true) . "</pre><br><B>SQL Statement</B>: " . $sqlselect );
		
		// Default return false
		return false;
	}
}

function DoLogOff()
{
	global $content;

	unset( $_SESSION['SESSION_LOGGEDIN'] );
	unset( $_SESSION['SESSION_USERNAME'] );
	unset( $_SESSION['SESSION_ACCESSLEVEL'] );

	// Redir to Index Page
	RedirectPage( "index.php");
}

function RedirectToUserLogin()
{
	// TODO Referer
	header("Location: login.php?referer=" . $_SERVER['PHP_SELF']);
	exit;
}

function RedirectToDatabaseUpgrade()
{
	// TODO Referer
	header("Location: upgrade.php"); // ?referer=" . $_SERVER['PHP_SELF']);
	exit;
}


// --- END Usermanagement Function --- 


// --- BEGIN Banned Player Filter --- 
function CreateBannedPlayerFilter()
{
	global $content;

	// Get Weapons from DB!
	$sqlquery = "SELECT " .
						STATS_PLAYERS_STATIC . ".GUID, " . 
						STATS_PLAYERS_STATIC . ".ISBANNED, " . 
						STATS_PLAYERS_STATIC . ".BanReason " . 
						" FROM " . STATS_PLAYERS_STATIC . 
						" WHERE " . STATS_PLAYERS_STATIC . ".ISBANNED = 1 ";
	$result = DB_Query($sqlquery);
	$content['bannedplayers'] = DB_GetAllRows($result, true);

	if ( isset($content['bannedplayers']) )
	{
		//--- Set Displayname!
		for ( $i = 0; $i < count($content['bannedplayers']); $i++ )
		{
			if ( isset($content['bannedplayers_guids']) )
				$content['bannedplayers_guids'] .= ", " . $content['bannedplayers'][$i]['GUID'];
			else
				$content['bannedplayers_guids'] = $content['bannedplayers'][$i]['GUID'];
		}
		//---
	}
	else
		$content['bannedplayers_guids'] = "";
}

function GetBannedPlayerWhereQuery( $customtable, $customplayerfield, $withwhere = true, $alsoreturnifempty = false )
{
	global $content;
	
	// --- Special Check for special cases
	if ( isset($content['bannedplayers_guids']) && strlen($content['bannedplayers_guids']) > 0 )
	{
		if ( $withwhere )
			return " WHERE ". $customtable. "." . $customplayerfield . " NOT IN (" . $content['bannedplayers_guids'] . ") ";
		else
			return " AND ". $customtable. "." . $customplayerfield . " NOT IN (" . $content['bannedplayers_guids'] . ") ";
	}
	else
		return "";
	// --- 
}
// --- 

?>