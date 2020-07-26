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

InitUltraStats();

// WTF OMFG WHY !!!!! $_SESSION is empty here :S:S:S:S! How the fuck can this be :S
// CheckForUserLogin( false );

// Hardcoded atm
$content['REDIRSECONDS'] = 1;
// ***					*** //

// --- CONTENT Vars
if ( isset($_GET['redir']) )
{
	$content['EXTRA_METATAGS'] = '<meta HTTP-EQUIV="REFRESH" CONTENT="' . $content['REDIRSECONDS'] . '; URL=' . urldecode($_GET['redir']) . '">';
	$content['SZREDIR'] = urldecode($_GET['redir']);
}
else
{
	$_GET['redir'] = "index.php";
}

if ( isset($_GET['msg']) )
	$content['SZMSG'] = urldecode($_GET['msg']);
else
	$content['SZMSG'] = "*Unknown State";

$content['TITLE'] = "Ultrastats - Redirect to '" . $content['SZREDIR'] . "' in 5 seconds";	// Title of the Page 
// --- 

// --- Parsen and Output
IncludeLanguageFile( $gl_root_path . 'lang/' . $LANG . '/admin.php' );

InitTemplateParser();
$page -> parser($content, "admin/result.html");
$page -> output(); 
// --- 

?>