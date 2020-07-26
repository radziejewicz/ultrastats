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
$content['TITLE'] = "Ultrastats - Admin Center - Users";	// Title of the Page 
// --- 

// --- BEGIN Custom Code
if ( isset($_GET['op']) )
{
	if ($_GET['op'] == "add") 
	{
		// Set Mode to add
		$content['ISEDITORNEWUSER'] = "true";
		$content['USER_FORMACTION'] = "addnewuser";
		$content['USER_SENDBUTTON'] = $content['LN_USER_ADD'];

		//PreInit these values 
		$content['USERNAME'] = "";
		$content['PASSWORD1'] = "";
		$content['PASSWORD2'] = "";
	}
	else if ($_GET['op'] == "edit") 
	{
		// Set Mode to edit
		$content['ISEDITORNEWUSER'] = "true";
		$content['USER_FORMACTION'] = "edituser";
		$content['USER_SENDBUTTON'] = $content['LN_USER_EDIT'];

		if ( isset($_GET['id']) )
		{
			//PreInit these values 
			$content['USERID'] = DB_RemoveBadChars($_GET['id']);

			$sqlquery = "SELECT * " . 
						" FROM " . STATS_USERS . 
						" WHERE ID = " . $content['USERID'];

			$result = DB_Query($sqlquery);
			$myuser = DB_GetSingleRow($result, true);
			if ( isset($myuser['username']) )
			{
				$content['USERID'] = $myuser['ID'];
				$content['USERNAME'] = $myuser['username'];
			}
			else
			{
				$content['ISERROR'] = "true";
				$content['ERROR_MSG'] = GetAndReplaceLangStr( $content['LN_USER_ERROR_IDNOTFOUND'], $content['USERID'] );
			}
		}
		else
		{
			$content['ISERROR'] = "true";
			$content['ERROR_MSG'] = "*Error, invalid ID, User not found";
		}
	}
	else if ($_GET['op'] == "delete") 
	{
		if ( isset($_GET['id']) )
		{
			//PreInit these values 
			$content['USERID'] = DB_RemoveBadChars($_GET['id']);

			if ( !isset($_SESSION['SESSION_USERNAME']) )
			{
				$content['ISERROR'] = "true";
				$content['ERROR_MSG'] = $content['LN_USER_ERROR_WTFOMFGGG'];
			}
			else
			{
				// Get UserInfo
				$result = DB_Query("SELECT username FROM " . STATS_USERS . " WHERE ID = " . $content['USERID'] ); 
				$myrow = DB_GetSingleRow($result, true);
				if ( !isset($myrow['username']) )
				{
					$content['ISERROR'] = "true";
					$content['ERROR_MSG'] = GetAndReplaceLangStr( $content['LN_USER_ERROR_IDNOTFOUND'], $content['USERID'] ); 
				}

				if ( $_SESSION['SESSION_USERNAME'] == $myrow['username'] ) 
				{
					$content['ISERROR'] = "true";
					$content['ERROR_MSG'] = GetAndReplaceLangStr( $content['LN_USER_ERROR_DONOTDELURSLF'], $content['USERID'] ); 
				}
				else
				{
					// do the delete!
					$result = DB_Query( "DELETE FROM " . STATS_USERS . " WHERE ID = " . $content['USERID'] );
					if ($result == FALSE)
					{
						$content['ISERROR'] = "true";
						$content['ERROR_MSG'] = GetAndReplaceLangStr( $content['LN_USER_ERROR_DELUSER'], $content['USERID'] ); 
					}
					else
						DB_FreeQuery($result);

					// Do the final redirect
					RedirectResult( GetAndReplaceLangStr( $content['LN_USER_ERROR_HASBEENDEL'], $myrow['username'] ) , "users.php" );
				}
			}
		}
		else
		{
			$content['ISERROR'] = "true";
			$content['ERROR_MSG'] = $content['LN_USER_ERROR_INVALIDID'];
		}
	}

	if ( isset($_POST['op']) )
	{
		if ( isset ($_POST['id']) ) { $content['USERID'] = DB_RemoveBadChars($_POST['id']); } else {$content['USERID'] = ""; }
		if ( isset ($_POST['username']) ) { $content['USERNAME'] = DB_RemoveBadChars($_POST['username']); } else {$content['USERNAME'] = ""; }
		if ( isset ($_POST['password1']) ) { $content['PASSWORD1'] = DB_RemoveBadChars($_POST['password1']); } else {$content['PASSWORD1'] = ""; }
		if ( isset ($_POST['password2']) ) { $content['PASSWORD2'] = DB_RemoveBadChars($_POST['password2']); } else {$content['PASSWORD2'] = ""; }

		// Check mandotary values
		if ( $content['USERNAME'] == "" )
		{
			$content['ISERROR'] = "true";
			$content['ERROR_MSG'] = $content['LN_USER_ERROR_USEREMPTY'];
		}

		if ( !isset($content['ISERROR']) ) 
		{	
			// Everything was alright, so we go to the next step!
			if ( $_POST['op'] == "addnewuser" )
			{
				$result = DB_Query("SELECT username FROM " . STATS_USERS . " WHERE username = '" . $content['USERNAME'] . "'"); 
				$myrow = DB_GetSingleRow($result, true);
				if ( isset($myrow['username']) )
				{
					$content['ISERROR'] = "true";
					$content['ERROR_MSG'] = $content['LN_USER_ERROR_USERNAMETAKEN'];
				}
				else
				{
					// Check if Password is set!
					if (	strlen($content['PASSWORD1']) <= 0 ||
							$content['PASSWORD1'] != $content['PASSWORD2'] )
					{
						$content['ISERROR'] = "true";
						$content['ERROR_MSG'] = $content['LN_USER_ERROR_PASSSHORT'];
					}

					if ( !isset($content['ISERROR']) ) 
					{	
						// Create passwordhash now :)!
						$content['PASSWORDHASH'] = md5( $content['PASSWORD1'] );

						// Add new User now!
						$result = DB_Query("INSERT INTO " . STATS_USERS . " (username, password) 
						VALUES ('" . $content['USERNAME'] . "', 
								'" . $content['PASSWORDHASH'] . "' )");
						DB_FreeQuery($result);
						
						// Do the final redirect
						RedirectResult( GetAndReplaceLangStr( $content['LN_USER_ERROR_HASBEENADDED'], $myrow['username'] ) , "users.php" );
					}
				}
			}
			else if ( $_POST['op'] == "edituser" )
			{
				$result = DB_Query("SELECT ID FROM " . STATS_USERS . " WHERE ID = " . $content['USERID']);
				$myrow = DB_GetSingleRow($result, true);
				if ( !isset($myrow['ID']) )
				{
					$content['ISERROR'] = "true";
					$content['ERROR_MSG'] = GetAndReplaceLangStr( $content['LN_USER_ERROR_IDNOTFOUND'], $content['USERID'] ); 
				}
				else
				{

					// Check if Password is enabled
					if ( isset($content['PASSWORD1']) && strlen($content['PASSWORD1']) > 0 )
					{
						if ( $content['PASSWORD1'] != $content['PASSWORD2'] )
						{
							$content['ISERROR'] = "true";
							$content['ERROR_MSG'] = $content['LN_USER_ERROR_PASSSHORT'];
						}

						if ( !isset($content['ISERROR']) ) 
						{
							// Create passwordhash now :)!
							$content['PASSWORDHASH'] = md5( $content['PASSWORD1'] );

							// Edit the User now!
							$result = DB_Query("UPDATE " . STATS_USERS . " SET 
								username = '" . $content['USERNAME'] . "', 
								password = '" . $content['PASSWORDHASH'] . "' 
								WHERE ID = " . $content['USERID']);
							DB_FreeQuery($result);
						}
					}
					else
					{
						// Edit the User now!
						$result = DB_Query("UPDATE " . STATS_USERS . " SET 
							username = '" . $content['USERNAME'] . "' 
							WHERE ID = " . $content['USERID']);
						DB_FreeQuery($result);
					}

					// Done redirect!
					RedirectResult( GetAndReplaceLangStr( $content['LN_USER_ERROR_HASBEENEDIT'], $content['USERNAME']) , "users.php" );
				}
			}
		}
	}
}
else
{
	// Default Mode = List Users
	$content['LISTUSERS'] = "true";

	// Read all Serverentries
	$sqlquery = "SELECT ID, " . 
				" username " . 
				" FROM " . STATS_USERS . 
				" ORDER BY ID ";
	$result = DB_Query($sqlquery);
	$content['USERS'] = DB_GetAllRows($result, true);

	// --- For the eye
	for($i = 0; $i < count($content['USERS']); $i++)
	{
		// --- Set CSS Class
		if ( $i % 2 == 0 )
			$content['USERS'][$i]['cssclass'] = "line0";
		else
			$content['USERS'][$i]['cssclass'] = "line1";
		// --- 
	}
	// --- 
}

// --- END Custom Code

// --- Parsen and Output
InitTemplateParser();
$page -> parser($content, "admin/users.html");
$page -> output(); 
// --- 

?>