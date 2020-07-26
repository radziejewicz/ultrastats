<?php
/*
	*********************************************************************
	* Copyright by Andre Lorbach | 2006-2008!								*
	* -> www.ultrastats.org <-											*
	*																	*
	* Use this script at your own risk!									*
	* -----------------------------------------------------------------	*
	* Some constants													*
	*																	*
	* -> Stuff which has to be static and predefined					*
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

// --- Some custom defines
define('RUNMODE_COMMANDLINE', 1);
define('RUNMODE_WEBSERVER', 2);

define('DEBUG_ULTRADEBUG', 5);
define('DEBUG_DEBUG', 4);
define('DEBUG_INFO', 3);
define('DEBUG_WARN', 2);
define('DEBUG_ERROR', 1);
define('DEBUG_ERROR_WTF', 0);

define('STR_DEBUG_ULTRADEBUG', "UltraDebug");
define('STR_DEBUG_DEBUG', "Debug");
define('STR_DEBUG_INFO', "Information");
define('STR_DEBUG_WARN', "Warning");
define('STR_DEBUG_ERROR', "Error");
define('STR_DEBUG_ERROR_WTF', "WTF OMFG");
// --- 

// --- Game Version defines
define('COD', 0);
define('CODUO', 1);
define('COD2', 2);
define('COD4', 3);

define('LN_GEN_COD', "Call of Duty");
define('LN_GEN_CODUO', "Call of Duty - United Offense");
define('LN_GEN_COD2', "Call of Duty 2");
define('LN_GEN_COD4', "Call of Duty 4 - Modern Warfare");
// --- 

// ---
define('PARSEBY_GUIDS', 0);
define('PARSEBY_PLAYERNAME', 1);

define('LN_GEN_PARSEBY_GUIDS', "Guids");
define('LN_GEN_PARSEBY_PLAYERNAME', "Playername");
// ---

// --- Some CONSTANT Values for the Parser
define('PARSER_TYPE', 0);
define('PARSER_GUID', 1);

define('JOIN_CLIENTID', 2);
define('JOIN_CLIENTNAME', 3);

define('QUIT_CLIENTID', 2);
define('QUIT_CLIENTNAME', 3);

define('CHAT_CLIENTID', 2);
define('CHAT_CLIENTNAME', 3);
define('CHAT_MESSAGE', 4);

define('KILL_OPFER_GUID', 1);
define('KILL_OPFER_ID', 2);
define('KILL_OPFER_TEAM', 3);
define('KILL_OPFER_NAME', 4);
define('KILL_ATTACKER_GUID', 5);
define('KILL_ATTACKER_ID', 6);
define('KILL_ATTACKER_TEAM', 7);
define('KILL_ATTACKER_NAME', 8);
define('KILL_ATTACKER_WEAPON', 9);
define('KILL_DAMAGE', 10);
define('KILL_DAMAGE_TYPE', 11);
define('KILL_DAMAGE_LOCATION', 12);

define('DAMAGE_OPFER_GUID', 1);
define('DAMAGE_OPFER_ID', 2);
define('DAMAGE_OPFER_TEAM', 3);
define('DAMAGE_OPFER_NAME', 4);
define('DAMAGE_ATTACKER_GUID', 5);
define('DAMAGE_ATTACKER_ID', 6);
define('DAMAGE_ATTACKER_TEAM', 7);
define('DAMAGE_ATTACKER_NAME', 8);

define('RWIN_TEAM', 1);
define('RLOS_TEAM', 1);

define('ACTION_CLIENTID', 2);
define('ACTION_CLIENT_TEAM', 3);
define('ACTION_CLIENT_NAME', 4);
define('ACTION_THEACTION', 5);
// --- 

// --- Constants for the processing
define('PLAYER_GUID', 0);
define('PLAYER_ID', 1);
define('PLAYER_NAME', 2);
define('PLAYER_TEAM', 3);
define('PLAYER_KILLS', 4);
define('PLAYER_DEATHS', 5);
define('PLAYER_TKS', 6);
define('PLAYER_SUICIDES', 7);
define('PLAYER_DBID', 8);
define('PLAYER_PBGUID', 9);

define('ROUND_GUID', 0);
define('ROUND_TIMESTAMP', 1);
define('ROUND_TIMEYEAR', 2);
define('ROUND_TIMEMONTH', 3);
define('ROUND_GAMETYPE', 4);
define('ROUND_MAPID', 5);
define('ROUND_SERVERCVARS', 6);
define('ROUND_AXIS_WINS', 7);
define('ROUND_AXIS_GUIDS', 8);
define('ROUND_ALLIES_WINS', 9);
define('ROUND_ALLIES_GUIDS', 10);
define('ROUND_DBID', 11);
define('ROUND_TOTALKILLS', 12);
define('ROUND_DURATION', 13);

define('DBKILLS_ATTACKERGUID', 0);
define('DBKILLS_OPFERGUID', 1);
define('DBKILLS_WEAPONID', 2);
define('DBKILLS_DAMAGETYPE', 3);
define('DBKILLS_HITLOCATION', 5);
define('DBKILLS_COUNT', 6);

define('TEAM_ALLIES', "allies");
define('TEAM_AXIS', "axis");
define('TEAM_WTF', "WTFOMFGBBQ");

define('MYSQLPATH_LINUX', "/usr/bin/mysql");			// For *nix:	mysql -u username -ppasswort database < stats.sql 
define('MYSQLPATH_WINDOWS', "D:\mysql\bin\mysql.exe");		// For Windows:	mysqld.exe -u username -ppasswort database < stats.sql 
// --- 

// --- TRANSFER Constants
define('FTP_TIMEOUT', 10);

define('TRANSFERTYPE_FTP', 0);
define('TRANSFERTYPE_SCP', 1);
// --- 

// --- WeaponTypes
define('WEAPONTYPE_MACHINEGUN', 0);	// MachineGun, Maschinenpistolen
define('WEAPONTYPE_SNIPER', 1);		// Sniper, Scharfschützengewehre
define('WEAPONTYPE_PISTOL', 2);		// Pistol, Pistole
define('WEAPONTYPE_GRENADE', 3);	// Grenade, Granaten
define('WEAPONTYPE_STANDWEAPON', 4);
define('WEAPONTYPE_TANK', 5);		// Panzer
define('WEAPONTYPE_MISC', 6);		// Misc, Sonstiges
define('WEAPONTYPE_ASSAULT', 7);		// Sturmgewehre, Assault Rifle
define('WEAPONTYPE_LIGHTMACHINEGUN', 8);// Light MachineGun, Leichte Maschinengewehre
define('WEAPONTYPE_SHOTGUN', 9);		// Shotgun, Schrotflinten
define('WEAPONTYPE_SPECIAL', 10);		// Special weapons like Claymore, RPG etc.
// --- 

?>