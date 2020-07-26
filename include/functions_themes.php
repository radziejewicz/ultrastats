<?php
/*
	*********************************************************************
	* Copyright by Andre Lorbach | 2006!								*
	* -> www.ultrastats.org <-											*
	*																	*
	* Use this script at your own risk!									*
	* -----------------------------------------------------------------	*
	* Theme specific functions											*
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

function CreateLanguageList()
{
	global $gl_root_path, $content;

	$alldirectories = list_directories( $gl_root_path . "lang/");
	for($i = 0; $i < count($alldirectories); $i++)
	{
		// --- gen_lang
		$content['LANGUAGES'][$i]['langcode'] = $alldirectories[$i];
		if ( $content['gen_lang'] == $alldirectories[$i] )
			$content['LANGUAGES'][$i]['selected'] = "selected";
		else
			$content['LANGUAGES'][$i]['selected'] = "";
		// ---

		// --- user_lang
		$content['USERLANG'][$i]['langcode'] = $alldirectories[$i];
		if ( $content['user_lang'] == $alldirectories[$i] )
			$content['USERLANG'][$i]['is_selected'] = "selected";
		else
			$content['USERLANG'][$i]['is_selected'] = "";
		// ---

	}
}

function CreateThemesList()
{
	global $gl_root_path, $content;

	$alldirectories = list_directories( $gl_root_path . "themes/");
	for($i = 0; $i < count($alldirectories); $i++)
	{
		// --- web_theme
		$content['STYLES'][$i]['StyleName'] = $alldirectories[$i];
		if ( $content['web_theme'] == $alldirectories[$i] )
			$content['STYLES'][$i]['selected'] = "selected";
		else
			$content['STYLES'][$i]['selected'] = "";
		// ---

		// --- user_theme
		$content['USERSTYLES'][$i]['StyleName'] = $alldirectories[$i];
		if ( $content['user_theme'] == $alldirectories[$i] )
			$content['USERSTYLES'][$i]['is_selected'] = "selected";
		else
			$content['USERSTYLES'][$i]['is_selected'] = "";
		// ---
	}
}

function list_directories($directory) 
{
	$result = array();
	if (! $directoryHandler = @opendir ($directory)) 
		DieWithFriendlyErrorMsg( "list_directories: directory \"$directory\" doesn't exist!");

	while (false !== ($fileName = @readdir ($directoryHandler))) 
	{
		if	( is_dir( $directory . $fileName ) && ( $fileName != "." && $fileName != ".." ))
			@array_push ($result, $fileName);
	}

	if ( @count ($result) === 0 ) 
		DieWithFriendlyErrorMsg( "list_directories: no directories in \"$directory\" found!");
	else 
	{
		sort ($result);
		return $result;
	}
}

function InitThemeAbout( $themename ) 
{
	global $content, $gl_root_path;
	$szAboutFile = $gl_root_path . "themes/" . $themename . "/about.txt";
	if ( is_file( $szAboutFile ) )
	{	//Read About Info!
		$aboutfile  = fopen($szAboutFile, 'r');
		if (!feof ($aboutfile)) 
		{
			while (!feof ($aboutfile))
			{
				$tmpline = fgets($aboutfile, 1024);
				if (!isset($content["theme_madeby"]) )
					$content["theme_madeby"] = substr( trim($tmpline), 0, 25);
				else if (!isset($content["theme_madebylink"]) )
					$content["theme_madebylink"] = substr( trim($tmpline), 0, 256);
				else
				{
					$content["theme_madebyenable"] = "true";
					break;
				}
			}
		}
		fclose($aboutfile);
	}
	else
		$content["theme_madebyenable"] = "false";
}

function VerifyTheme( $newtheme ) 
{ 
	global $content, $gl_root_path;

	if ( is_dir( $gl_root_path . "themes/" . $newtheme ) )
	{
		// return success!
		return true;
	}
	else
		return false;
}

?>