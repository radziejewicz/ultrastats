<?php
global $content;

// Global Stuff
$content['LN_ADMINADD'] = "Add";
$content['LN_ADMINEDIT'] = "Edit";
$content['LN_ADMINDELETE'] = "Delete";
$content['LN_ADMINSEND'] = "Send";

// LoginPage
$content['LN_ADMINLOGIN'] = "Admin login";
$content['LN_USERNAME'] = "Username";
$content['LN_PASSWORD'] = "Password";
$content['LN_SAVEASCOOKIE'] = "Stay Online (Save in Cookie)";

// Main Page
$content['LN_ADMINCENTER'] = "Admin Center";
$content['LN_NUMSERVERS'] = "Number of Servers";
$content['LN_LASTDBUPDATE'] = "Last Database Update";
$content['LN_ADMINGENCONFIG'] = "General Configurations";
$content['LN_CURRDBVERSION'] = "Internal Database Version";

// Main Options
$content['LN_ADMINFRONTEND'] = "Frontend Options";
$content['LN_GEN_WEB_STYLE'] = "Select your favourite Theme";
$content['LN_GEN_WEB_TOPPLAYERS'] = "How many Player should be listed per Page?";
$content['LN_GEN_WEB_DETAILLISTTOPPLAYERS'] = "How many Player should be listed in Detail Views?";
$content['LN_GEN_WEB_TOPROUNDS'] = "How many Rounds do you show per page?";
$content['LN_GEN_WEB_MAXPAGES'] = "Maximum number of Pages, if paging is needed!";
$content['LN_GEN_WEB_MINKILLS'] = "How many kills must a Player have to be listed?";
$content['LN_GEN_WEB_MINTIME'] = "How long must a Player have played to be listed?";
$content['LN_GEN_WEB_SHOWMEDALS'] = "Show Medals on Mainpage";
$content['LN_ADMINPARSER'] = "Parser Options";
$content['LN_PARSER_DEBUGMODE'] = "Debug Mode";
$content['LN_PARSER_DISABLELASTLOGLINE'] = "Disabled Write of LastLogLine (For Debug only)";
$content['LN_GEN_WEB_MAXMAPSPERPAGE'] = "How many maps do you want per page in the Serverstats?";
$content['LN_GEN_GAMEVERSION'] = "Game Version";
$content['LN_GEN_PARSEBYTYPE'] = "Parse Players by";
$content['LN_GEN_PHPDEBUG'] = "Enable PHP Debugging";
$content['LN_PARSER_ENABLECHATLOGGING'] = "Enable Chat Logging";
	$content['LN_ADMINMEDALS'] = "Medal Options";
	$content['LN_ADMINMEDALSENABLE'] = "Enable '%1' Medal";

// Server Page
$content['LN_ADMINCENTER_SERVER'] = "Server Admin Center";
$content['LN_CHANGESERVER'] = "Change Server Config";
$content['LN_ADDSERVER'] = "Add Server";
$content['LN_EDITSERVER'] = "Edit Server";
$content['LN_SERVERNUMBER'] = "Number";
$content['LN_SERVERACTION'] = "Actions";
$content['LN_SERVER'] = "Server";
$content['LN_SERVERID'] = "Server ID";
$content['LN_SERVERNAME'] = "Servername";
$content['LN_SERVERIP'] = "Server IP";
$content['LN_SERVERTOOLS'] = "Server Tools";
$content['LN_PORT'] = "Server Port";
$content['LN_DESCRIPTION'] = "Description";
$content['LN_MODNAME'] = "Modname";
$content['LN_ADMINNAME'] = "Adminname";
$content['LN_ADMINEMAIL'] = "Adminemail";
$content['LN_CLANNAME'] = "Clanname";
$content['LN_LASTLINE'] = "LastLogLine";
$content['LN_GAMELOGLOCATION'] = "Gamelog Location";
$content['LN_REMOTEGAMELOGLOCATION'] = "Remote FTP Gamelog Location";
$content['LN_SERVERENABLED'] = "Server Enabled";
$content['LN_PARSINGENABLED'] = "Parsing Enabled";
$content['LN_ADMINPARSESTATS'] = "Run Parser";
$content['LN_ADMINDELETESTATS'] = "Empty Stats";
$content['LN_ADMINGETNEWLOG'] = "Get New Logfile";
$content['LN_ADMINRESETLASTLOGLINE'] = "Reset Last Logline to 0";
$content['LN_ADMINDBSTATS'] = "Server DB Statistics";
$content['LN_STATSALIASES'] = "Total Aliases";
$content['LN_STATSCHATLINES'] = "Chat Lines";
$content['LN_STATSPLAYERS'] = "Total players";
$content['LN_STATSKILLS'] = "Total Kills";
$content['LN_STATSROUNDS'] = "Played Rounds";
$content['LN_STATSTIME'] = "Total played Time";
$content['LN_SERVER_ERROR_INVID'] = "Error, invalid ServerID, Server not found";
$content['LN_SERVER_ERROR_NOTFOUND'] = "Error, Server '%1' was not found";
$content['LN_SERVER_ERROR_SERVEREMPTY'] = "Error, Servername was empty";
$content['LN_SERVER_ERROR_SERVERIPEMPTY'] = "Error, Serverip was empty";
$content['LN_SERVER_ERROR_INVIP'] = "Error, Invalid Serverip";
$content['LN_SERVER_ERROR_INVPORT'] = "Error, Port can only be a numberic value from 1 to 65535";
$content['LN_SERVER_ERROR_GAMEFILENOTEXISTS'] = "Error, the Gamelogfile does not exists. Please verify it is at the loaction you specified";
$content['LN_SERVER_ERROR_INDBALREADY'] = "Error, this Gameserver is already in the Database!";
$content['LN_SERVER_SUCCEDIT'] = "Server '%1' has been successfully edited";
$content['LN_SERVER_SUCCADDED'] = "Server '%1' has been successfully added";
$content['LN_SERVERLOGO'] = "ServerLogo";
$content['LN_RUNTOTALUPDATE'] = "Run Total/Final Calculations";
$content['LN_SERVERLIST'] = "ServerList";
$content['LN_DATABASEOPT'] = "Optimize Database Tables";
$content['LN_BUILDFTPSTRING'] = "Create";
$content['LN_FTPPASSIVEENABLED'] = "User FTP Passive Mode";
$content['LN_ADDITIONALFUNCTIONS'] = "Additional Functions";
$content['LN_CREATEALIASES'] = "Create Aliases HTML Code";
$content['LN_CALCMEDALSONLY'] = "Calculate Medals";
$content['LN_ADMINCREATEALIASES'] = "Create Top Aliases";

// Server FTP Builder
$content['LN_ADMINCENTER_FTPBUILDER'] = "FTP Builder";
$content['LN_ADMINCENTER_FTPBUILDER_DES'] = "This window will help you building a valid FTP Url and verify that it is working. Click on the Verify FTP Url button if you want to verify the FTP Url.";
$content['LN_FTPBUILD_SERVERIP'] = "FTP ServerIP";
$content['LN_FTPBUILD_SERVERPORT'] = "FTP ServerPort";
$content['LN_FTPBUILD_USERNAME'] = "Username";
$content['LN_FTPBUILD_PASSWORD'] = "Password (Optional)";
$content['LN_FTPBUILD_PATHTOGAMELOG'] = "Path to the gamelog";
$content['LN_FTPBUILD_GAMELOGFILENAME'] = "Gamelog Filename";
$content['LN_FTPBUILD_ENABLEPASSIVE'] = "Enable FTP Passive Mode";
$content['LN_FTPBUILD_GENERATE_FTPURL'] = "Generate FTP Url";
$content['LN_FTPBUILD_VERIFY_FTPURL'] = "Verify FTP Url";
$content['LN_FTPBUILD_SAVE_FTPURL'] = "Save FTP Url";
$content['LN_FTPBUILD_PREVIEW'] = "FTP Url Preview";
$content['LN_FTPBUILD_VERIFY'] = "FTP Url Verify";
$content['LN_FTPBUILD_SAVEDCLOE'] = "New FTP Path has been successfully saved. This Window will close automatically in 5 seconds, and reload the ServerAdmin. If not manually click on the Close Button!";

// Parser Page
$content['LN_ADMINCENTER_PARSER'] = "Server Parsing";
$content['NO_INFRAME_POSSIBLE'] = "Unfortunately, your browser doesn't support Inframes and the Parser can not run!";
$content['LN_EMBEDDED_PARSER'] = "Running embedded parser";
$content['LN_WARNINGDELETE'] = "Warning! If you delete the Server, all it's stats will be deleted as well!";
$content['LN_DELETEYES'] = "Click here to continue deleting the Server";
$content['LN_DELETENO'] = "Click here to return to the previous page";
$content['LN_FTPLOGINFAILED'] = "FTP Login failed, or no password given.";
$content['LN_FTPPASSWORD'] = "FTP Password";

// User Page
$content['LN_USER_CENTER'] = "User Admin Center";
$content['LN_USER_NAME'] = "Username";
$content['LN_USER_ADD'] = "Add User";
$content['LN_USER_EDIT'] = "Edit User";
$content['LN_USER_PASSWORD1'] = "Password";
$content['LN_USER_PASSWORD2'] = "Repeat Password";
$content['LN_USER_ERROR_IDNOTFOUND'] = "Error, User with ID '%1' , was not found";
$content['LN_USER_ERROR_WTFOMFGGG'] = "Error, erm wtf you don't have a username omfg pls mowl?";
$content['LN_USER_ERROR_DONOTDELURSLF'] = "Error, you can not DELETE YOURSELF!";
$content['LN_USER_ERROR_DELUSER'] = "Error deleting the User!";
$content['LN_USER_ERROR_INVALIDID'] = "Error, invalid ID, User not found";
$content['LN_USER_ERROR_HASBEENDEL'] = "User '%1' has been successfully DELETED!";
$content['LN_USER_ERROR_USEREMPTY'] = "Error, Username was empty";
$content['LN_USER_ERROR_USERNAMETAKEN'] = "Error, this Username is already taken!";
$content['LN_USER_ERROR_PASSSHORT'] = "Error, Password was to short, or did not match";
$content['LN_USER_ERROR_HASBEENADDED'] = "User '%1' has been successfully added";
$content['LN_USER_ERROR_HASBEENEDIT'] = "User '%1' has been successfully edited";

// General Options
$content['LN_GEN_LANGUAGE'] = "Select default language";

// Players Page
$content['LN_PLAYER_EDITOR'] = "Players Editor";
$content['LN_PLAYER_NAME'] = "Top used Name";
$content['LN_PLAYER_GUID'] = "GUID";
$content['LN_PLAYER_PBGUID'] = "Punkbuster GUID";
$content['LN_PLAYER_CLANMEMBER'] = "Is Clanmember?";
$content['LN_PLAYER_BANNED'] = "Banned Player?";
$content['LN_PLAYER_BANREASON'] = "Banned reason!";
$content['LN_PLAYER_EDIT'] = "Edit Player";
$content['LN_PLAYER_DELETE'] = "Delete Player";
$content['LN_PLAYER_ERROR_NOTFOUND'] = "Error, Player with GUID '%1' was not found";
$content['LN_PLAYER_ERROR_INVID'] = "Error, invalid Player ID!";
$content['LN_PLAYER_ERROR_PLAYERIDEMPTY'] = "Error, player ID was empty!";
$content['LN_PLAYER_ERROR_NOTFOUND'] = "Error, player with GUID '%1' was not found in the database!";
$content['LN_PLAYER_SUCCEDIT'] = "Player with GUID '%1' qwas successfully edited!";
$content['LN_PLAYER_FILTER'] = "Filter Players by ";
$content['LN_PLAYER_DOFILTER'] = "Do Filter";
$content['LN_WARNING_DELETEPLAYER'] = "Warning, deleting this Player will also delete his statistics, kills, deaths just everything that has something todo with this player ;)!";
$content['LN_PLAYER_SQLCMD'] = "SQL Command";
$content['LN_PLAYER_SQLTABLE'] = "SQL Table";
$content['LN_PLAYER_AFFECTEDRECORD'] = "Affected records";
$content['LN_PLAYER_DELETED'] = "deleted";
$content['LN_PLAYER_BACKPLAYERLIST'] = "Back to Playerlist";

// Upgrade Page
$content['LN_DBUPGRADE_TITLE'] = "Upgrading UltraStats Database";
$content['LN_DBUPGRADE_DBFILENOTFOUND'] = "The database upgrade file '%1' could not be found in the contrib folder! Please check all files, you may have forgottem to upload the database upgrade files.";
$content['LN_DBUPGRADE_DBDEFFILESHORT'] = "The Database upgrade files where empty or no SQL commands found! Please check all files, you may habe forgottem to upload the database upgrade files.";
$content['LN_DBUPGRADE_WELCOME'] = "Welcome to the Database Upgrade";
$content['LN_DBUPGRADE_BEFORESTART'] = "Before you start upgrading your databasem you should create a Full Database Backup, just in case. Anything else will be done automatically by the upgrade Script.";
$content['LN_DBUPGRADE_CURRENTINSTALLED'] = "Current Installed Database Version";
$content['LN_DBUPGRADE_TOBEINSTALLED'] = "Do be Installed Database Version";
$content['LN_DBUPGRADE_HASBEENDONE'] = "Database Update has been performed, see the results below";
$content['LN_DBUPGRADE_SUCCESSEXEC'] = "Successfully executed statements";
$content['LN_DBUPGRADE_FAILEDEXEC'] = "Failed statements";
$content['LN_DBUPGRADE_ONESTATEMENTFAILED'] = "At least one statement failed, see error reasons below";
$content['LN_DBUPGRADE_ERRMSG'] = "Error Message";
$content['LN_DBUPGRADE_ULTRASTATSDBVERSION'] = "UltraStats Database Version";

?>