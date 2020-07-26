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

// --- Default includes	and procedures --- //
define('IN_ULTRASTATS', true);
$gl_root_path = './../';
include($gl_root_path . 'include/functions_db.php');
include($gl_root_path . 'include/functions_common.php');
include($gl_root_path . 'include/class_template.php');

InitUltraStats();
CheckForUserLogin( true );
// ---					--- //

// --- BEGIN Custom Code

// Set Defaults
$content['uname'] = "";
$content['pass'] = "";

// Set Referer
if ( isset($_GET['referer']) )
	$szRedir = $_GET['referer'];
else
	$szRedir = "index.php"; // Default

if ( isset($_POST['op']) )
{
	// Set Referer
	if ( isset($_POST['url']) )
	{
		if ( $_POST['url'] == "" )
			$szRedir = "index.php"; // Default
		else
			$szRedir = DB_RemoveBadChars($_POST['url']);
	}
	else
		$szRedir = "index.php"; // Default


	if ( $_POST['op'] == "login" )
	{
		// TODO: $my_rememberme = $_POST['rememberme'];
		if ( isset($_POST['uname']) && isset($_POST['pass']) )
		{
			// Set Username and password
			$content['uname'] = DB_RemoveBadChars($_POST['uname']);
			$content['pass'] = DB_RemoveBadChars($_POST['pass']);

			if ( !CheckUserLogin( $content['uname'], $content['pass']) )
			{
				$content['ISERROR'] = "true";
				$content['ERROR_MSG'] = "*Wrong username or password!";
			}
			else
				RedirectPage( $szRedir );
		}
		else
		{
			$content['ISERROR'] = "true";
			$content['ERROR_MSG'] = "*Username or password not given";
		}
	}
}

if ( isset($_GET['op']) && $_GET['op'] == "logoff" )
{
	// logoff in this case
	DoLogOff();
}
// --- END Custom Code

// --- CONTENT Vars
$content['REDIR_LOGIN'] = $szRedir;
$content['TITLE'] = "Ultrastats - Admin Login";	// Title of the Page 
// --- 

// --- Parsen and Output
IncludeLanguageFile( $gl_root_path . 'lang/' . $LANG . '/admin.php' );

InitTemplateParser();
$page -> parser($content, "admin/login.html");
$page -> output(); 
// --- 

?>