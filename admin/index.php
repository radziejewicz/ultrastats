<?php
/*
	*********************************************************************
	* Copyright by Andre Lorbach | 2006!								*
	* -> www.ultrastats.org <-											*
	*																	*
	* Use this script at your own risk!									*
	* -----------------------------------------------------------------	*
	* Admin main File											*
	*																	*
	* -> Helps the admin to manage his UltraStats		*
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
include($gl_root_path . 'include/functions_parser-medals.php');

InitUltraStats();
CheckForUserLogin( false );
IncludeLanguageFile( $gl_root_path . 'lang/' . $LANG . '/admin.php' );
// ***					*** //

// --- CONTENT Vars
$content['TITLE'] = "Ultrastats - Admin Center";	// Title of the Page 
// --- 

// --- BEGIN Custom Code

// Some more includes needed
include($gl_root_path . 'include/functions_frontendhelpers.php');

// Create TopPlayers Array
CreateTopPlayersArray( 200, "TOPROUNDS", "web_toprounds" );
CreateTopPlayersArray( 200, "TOPPLAYERS", "web_topplayers" );
CreateTopPlayersArray( 200, "TOPLISTPLAYERS", "web_detaillistsplayers" );
CreateTopPlayersArray( 100, "WEBMAXPAGES", "web_maxpages" );
CreateTopPlayersArray( 50, "WEBMAXMAPSPERPAGE", "web_maxmapsperpage" );

// Create DebugModes
CreateDebugModes();

// --- Init MedalSQLCode!
CreateMedalsSQLCode(-1);

// Can't use this core yet, it only works on PHP5: foreach ($content['medals'] as $key => &$medal)
foreach ($content['medals'] as $key => $medal)
{
	$content['medals'][$key]['AdminDisplayName'] = GetAndReplaceLangStr( $content['LN_ADMINMEDALSENABLE'], $medal['DisplayName']);

	if ($content[$medal['medalid']] == "yes") 
		$content['medals'][$key]['MedalChecked'] = "checked"; 
	else 
		$content['medals'][$key]['MedalChecked'] = "";
}	
// --- 

// Some other things which need to be done
if ($content['web_medals'] == "yes") { $content['web_medals_checked'] = "checked"; } else { $content['web_medals_checked'] = ""; }
if ($content['parser_disablelastline'] == "yes") { $content['parser_disablelastline_checked'] = "checked"; } else { $content['parser_disablelastline_checked'] = ""; }
if ($content['gen_phpdebug'] == "yes") { $content['gen_phpdebug_checked'] = "checked"; } else { $content['gen_phpdebug_checked'] = ""; }
if ($content['parser_chatlogging'] == "yes") { $content['parser_chatlogging_checked'] = "checked"; } else { $content['parser_chatlogging_checked'] = ""; }

// Now the processing Part
if ( isset($_POST['op']) )
{
	// Read Gen Config Vars
	if ( isset ($_POST['gen_lang']) )
	{ 
		$tmpvar = DB_RemoveBadChars($_POST['gen_lang']); 
		if ( VerifyLanguage($tmpvar) )
			$content['gen_lang'] = $tmpvar;
	}
	if ( isset ($_POST['gen_gameversion']) ) { $content['gen_gameversion'] = Intval(DB_RemoveBadChars($_POST['gen_gameversion'])); }
	if ( isset ($_POST['gen_parseby']) ) { $content['gen_parseby'] = Intval(DB_RemoveBadChars($_POST['gen_parseby'])); }
	if ( isset ($_POST['gen_phpdebug']) ) { $content['gen_phpdebug'] = "yes"; } else { $content['gen_phpdebug'] = "no"; } 

	// Read Parser Config Vars
	if ( isset ($_POST['parser_debugmode']) ) { $content['parser_debugmode'] = DB_RemoveBadChars($_POST['parser_debugmode']); }
	if ( isset ($_POST['parser_disablelastline']) ) { $content['parser_disablelastline'] = "yes"; } else { $content['parser_disablelastline'] = "no"; } 
	if ( isset ($_POST['parser_chatlogging']) ) { $content['parser_chatlogging'] = DB_RemoveBadChars($_POST['parser_chatlogging']); }  else { $content['parser_chatlogging'] = "no"; } 

	// Read WEB Config Vars
	if ( isset ($_POST['web_theme']) ) { $content['web_theme'] = DB_RemoveBadChars($_POST['web_theme']); }
	if ( isset ($_POST['web_toprounds']) ) { $content['web_toprounds'] = DB_RemoveBadChars($_POST['web_toprounds']); } 
	if ( isset ($_POST['web_topplayers']) ) { $content['web_topplayers'] = DB_RemoveBadChars($_POST['web_topplayers']); } 
	if ( isset ($_POST['web_detaillistsplayers']) ) { $content['web_detaillistsplayers'] = DB_RemoveBadChars($_POST['web_detaillistsplayers']); } 
	if ( isset ($_POST['web_minkills']) && is_numeric($_POST['web_minkills']) ) { $content['web_minkills'] = DB_RemoveBadChars($_POST['web_minkills']); }
	if ( isset ($_POST['web_mintime']) && is_numeric($_POST['web_mintime'])) { $content['web_mintime'] = DB_RemoveBadChars($_POST['web_mintime']); } 
	if ( isset ($_POST['web_maxpages']) && is_numeric($_POST['web_maxpages'])) { $content['web_maxpages'] = DB_RemoveBadChars($_POST['web_maxpages']); } 
	if ( isset ($_POST['web_maxmapsperpage']) && is_numeric($_POST['web_maxmapsperpage'])) { $content['web_maxmapsperpage'] = DB_RemoveBadChars($_POST['web_maxmapsperpage']); } 
	if ( isset ($_POST['web_medals']) ) { $content['web_medals'] = "yes"; } else { $content['web_medals'] = "no"; }

	// Write Gen Config Vars
	WriteConfigValue( "gen_lang" );
	WriteConfigValue( "gen_gameversion" );
	WriteConfigValue( "gen_parseby" );
	WriteConfigValue( "gen_phpdebug" );

	// Read Parser Config Vars
	WriteConfigValue( "parser_debugmode" );
	WriteConfigValue( "parser_disablelastline" );
	WriteConfigValue( "parser_chatlogging" );

	// Write Web Config vars	
	WriteConfigValue( "web_theme" );
	WriteConfigValue( "web_toprounds" );
	WriteConfigValue( "web_topplayers" );
	WriteConfigValue( "web_detaillistsplayers" );
	WriteConfigValue( "web_minkills" );
	WriteConfigValue( "web_mintime" );
	WriteConfigValue( "web_maxpages" );
	WriteConfigValue( "web_maxmapsperpage" );
	WriteConfigValue( "web_medals" );

	// Write Medal Config Vars
	foreach ($content['medals'] as $key => $medal)
	{
		if ( isset ($_POST[$key]) ) 
			$content[$key] = "yes"; 
		else 
			$content[$key] = "no";
		//Write into DB!
		WriteConfigValue( $key );
	}

	// Done and redirect
	RedirectResult( "Configuration Values have been successfully saved", "index.php" );
}

// --- 

// --- Parsen and Output
InitTemplateParser();
$page -> parser($content, "admin/index.html");
$page -> output(); 
// --- 

?>