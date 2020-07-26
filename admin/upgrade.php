<?php
/*
	*********************************************************************
	* Copyright by Andre Lorbach | 2006!								*
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
$gl_root_path = './../';
include($gl_root_path . 'include/functions_db.php');
include($gl_root_path . 'include/functions_common.php');
include($gl_root_path . 'include/class_template.php');
include($gl_root_path . 'include/functions_frontendhelpers.php');

InitUltraStats();
CheckForUserLogin( false, true );	// Second Param needed, this is the db upgrade page

IncludeLanguageFile( $gl_root_path . 'lang/' . $LANG . '/admin.php' );
// ***					*** //

// --- CONTENT Vars
$content['TITLE'] = "Ultrastats - Admin Center - Database Upgrade";	// Title of the Page 
// --- 

// --- BEGIN Custom Code

if ( isset($content['database_forcedatabaseupdate']) && $content['database_forcedatabaseupdate'] == "yes" ) 
{
	if ( isset($_GET['op']) )
	{
		if ($_GET['op'] == "upgrade") 
		{
			// Lets start teh fun ;)!
			$content['UPGRADE_RUNNING'] = "1";

			$content['sql_sucess'] = 0;
			$content['sql_failed'] = 0;
			$totaldbdefs = "";
			
			// +1 so we start at the right DB Version!
			for( $i = $content['database_installedversion']+1; $i <= $content['database_internalversion']; $i++ )
			{
				$myfilename = "db_update_v" . $i . ".txt";

				// Lets read the table definitions :)
				$handle = @fopen($content['BASEPATH'] . "contrib/" . $myfilename, "r");
				if ($handle === false) 
				{
					$content['ISERROR'] = "true";
					$content['ERROR_MSG'] = GetAndReplaceLangStr( $content['LN_DBUPGRADE_DBFILENOTFOUND'], $myfilename ); 
				}
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

			if ( !isset($content['ISERROR']) )
			{
				if ( strlen($totaldbdefs) <= 0 )
				{
					$content['ISERROR'] = "true";
					$content['ERROR_MSG'] = $content['LN_DBUPGRADE_DBDEFFILESHORT']; 
				}
			
				// Replace stats_ with the custom one ;)
				$totaldbdefs = str_replace( "`stats_", "`" . $CFG['TBPref'], $totaldbdefs );
			
				// Now split by sql command
				$mycommands = split( ";\r\n", $totaldbdefs );
			
				// check for different linefeed
				if ( count($mycommands) <= 1 )
					$mycommands = split( ";\n", $totaldbdefs );
	
				//Still only one? Abort
				if ( count($mycommands) <= 1 )
				{
					$content['ISERROR'] = "true";
					$content['ERROR_MSG'] = $content['LN_DBUPGRADE_DBDEFFILESHORT']; 
				}

				if ( !isset($content['ISERROR']) )
				{
					// --- Now execute all commands
					ini_set('error_reporting', E_WARNING); // Enable Warnings!

					for($i = 0; $i < count($mycommands); $i++)
					{
						if ( strlen(trim($mycommands[$i])) > 1 )
						{
							// Check for gametype related statements
							if ( strpos($mycommands[$i], "-- Cod4 only%$&1337&%&") !== false )
							{
								if ( $content['gen_gameversion'] != COD4 )
									continue; // Ignore this database statement!
								else 
									$mycommands[$i] = str_replace("-- Cod4 only%$&1337&%&", "", $mycommands[$i]); // Remove appendix!
							}

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
					// --- 

					// --- Upgrade Database Version in Config Table
					$content['database_installedversion'] = $content['database_internalversion'];
					WriteConfigValue( "database_installedversion" );
					// --- 
				}
			}
		}
		else
			$content['UPGRADE_DEFAULT'] = "1";
	}
	else
		$content['UPGRADE_DEFAULT'] = "1";


}
else 
	$content['UPGRADE_DEFAULT'] = "0";


// disable running to be save! ;)
if ( isset($content['ISERROR']) )
	$content['UPGRADE_RUNNING'] = "0";

// --- END Custom Code

// --- Parsen and Output
InitTemplateParser();
$page -> parser($content, "admin/upgrade.html");
$page -> output(); 
// --- 

?>