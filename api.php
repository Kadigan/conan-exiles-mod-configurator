<?php if ( date_default_timezone_get() == "" ) date_default_timezone_set("Europe/Warsaw"); // if your host doesn't set this up, set it up here

# configure where the mod dir is; typically (DISK:)/path/to/server/ConanSandbox/Mods
# the path MUST NOT have a trailing slash
$ModsDir = "/var/NVMe/CONAN_Dedicated_Server/ConanSandbox/Mods";

# the mods MUST be in folders under that folder, eg. {$ModsDir}/123456789/ModName.pak; if you have mods
# that don't follow ID naming (mainly because they don't come from STEAM's Workshop), use any folder names
# you wish, but keep in mind that STEAM API's mod info requests won't work on them, and the .pak file's name
# will be used as the mod name, and STEAM links (and other ext. info) will not be available for them

# this MAY be partially fixed in the future, if I ever feel like writing a PAK parser to get the mod info out of them ;)

// ================================================================================================================================================================
// ================================================================================================================================================================
// ================================================================================================================================================================
function resultJSON($type = "unknown", $msg = "", $data = ""){ echo JSON_ENCODE(['result' => $type, 'msg' => $msg, 'data' => $data]); }
function success($msg, $data = ""){ resultJSON("success", $msg, $data); exit(0); }
function error($msg)              { resultJSON("error",   $msg); }
function fatalError($msg)         { resultJSON("fatal",   $msg); exit(1); }
function fatal($msg){ fatalError($msg); }

define("MOD_FOREIGN", 255); // any type of mod not specified otherwise
define("MOD_STEAM",   1);


function readMods(){
   $Mods    = [];
   global $ModsDir;
   $steamApiMods = [];
   foreach(array_diff(scandir($ModsDir), ['.', '..']) as $entry){
      $fp = $ModsDir."/".$entry;

      if ( !is_dir($fp) )
         continue;

      $mod = ['id' => $entry, 'type' => MOD_FOREIGN, 'pak_name' => "", 'mod_time' => 0, 'mod_time_hr' => date("Y-m-d", 0)];
      if ( is_numeric($entry) ){
         // we assume it's a STEAM MOD ID
         $mod['type']            = MOD_STEAM;
         $mod['steam_state']     = "";
         $mod['steam_updated']   = 0;
         $mod['steam_name']      = "";
         $mod['update_required'] = 0;
      }

      $entries = [];
      foreach(array_diff(scandir($fp), ['.', '..']) as $entry){
         $ffp = $fp . "/". $entry;

         if ( !is_file($ffp) || is_dir($ffp) ){
            continue; // we don't want directories
         }

         if ( strtolower(substr($entry, -4)) != ".pak" )
            continue; // we only want .pak files

         $entries[] = [$entry, filemtime($ffp)];
      }
      if ( sizeof($entries) < 1 ){
         continue;
      }

      if ( sizeof($entries) > 1 )
         fatal("Mod entry '{$mod['id']}' doesn't contain exactly ONE .pak file");

      $mod['pak_name']    = $entries[0][0];
      $mod['mod_time']    = $entries[0][1];
      $mod['mod_time_hr'] = date("Y-m-d", $mod['mod_time']);

      // if all is well, add the mod to the list
      $Mods[$mod['id']] = $mod;
      if ( $mod['type'] == MOD_STEAM )
         $steamApiMods[$mod['id']] = $mod;
   }

   if ( sizeof($steamApiMods) ){
      require_once("./SteamAPI.php");
      try {
         $info = SteamAPI::fetchModInfo($steamApiMods);
         foreach($info as $modID => $modData) $Mods[$modID] = $modData;
      } catch ( Exception $e ){
         fatal($e->getMessage());
      }
   }

   return $Mods;
}

function readModlist(){
   global $ModsDir;
   $fp = $ModsDir."/modlist.txt";
   if ( !file_exists($fp) || !is_file($fp) )
      return "";

   $modlistData = [];
   foreach(explode("\n", file_get_contents($fp)) as $line){
      $line = trim($line);
      if ( $line == "" )
         continue;

      if ( 0 != preg_match("/^([0-9]+)\/[^.]+$/i", $line, $regs) )
         $line = $regs[1];

      $tmp = explode("/", $line);
      if ( sizeof($tmp) == 2 )
         $line = $tmp[0]; // should be the folder name, we don't care for anything else

      $modlistData[] = $line;
   }

   return $modlistData;
}

function listSteamApiMods(){
   $Mods = [];
   foreach(readMods(true) as $modID => $modInfo){
      if ( !is_numeric($modID) )
         continue;

      $Mods[$modID] = $modInfo;
   }

   return $Mods;
}
// ================================================================================================================================================================
if ( !file_exists($ModsDir) || !is_dir($ModsDir) )
   fatal("The ModsDir path '{$ModsDir}' doesn't exist, isn't a directory, or isn't readable"); // error out, we can't proceed

if ( substr($ModsDir, -1) == "/" )
   $ModsDir = substr($ModsDir, 0, -1); // strip the trailing slash, if any

$allowedActions = ['read'  => false,  # read: read modlist
                   'save'  => 'mods', # save: save modlist, array required
                   'clear' => false,  # clear: clear the modlist
                   'list'  => false]; # list: list mods

$action = "undefined";
$mods   = [];
foreach(['action', 'mods'] as $variable){
   if ( isset($_GET[$variable]) && trim($_GET[$variable]) != "" ){
      switch($variable){
         case 'mods':
            $$variable = explode(",", trim($_GET[$variable]));
         break;

         case 'action':
            $tmp = trim($_GET[$variable]);
            if ( !isset( $allowedActions[$tmp] ) )
               break;

            $$variable = $tmp;
         break;

         default:
            $$variable = trim($_GET[$variable]);
         break;
      }
   }
}

# check that action is one of expected actions
if ( $action == "undefined" )
   fatal("Unknown action specified, or no action specified");

# check that the requirements for the action are met
$requirement = $allowedActions[$action];
if ( $requirement !== false && empty($$requirement) )
   fatal("Action '{$action}' requires parameters");

// done with sanity checks
// ================================================================================================================================================================

switch($action){
   case 'list':
      success("list", readMods());
   break;

   case 'read':
      success("read", readModList());
   break;

   case 'save':
      $modsInstalled = readMods();
      $modsToSave = [];
      foreach($mods as $modID){
         if ( !isset($modsInstalled[$modID]) )
            fatal("Mod '{$modID}' doesn't exist in the list of installed mods, cannot continue");

         $modsToSave[] = $modID."/".$modsInstalled[$modID]['pak_name'];
      }

      if ( sizeof($modsToSave) < 1 )
         fatal("Resultant modlist.txt is empty");

      $result = file_put_contents($ModsDir."/modlist.txt", implode("\r\n", $modsToSave), LOCK_EX);
      if ( $result === false ){
         // failed to save
         if ( function_exists("posix_getpwuid") ){
            $processUser  = posix_getpwuid(posix_geteuid());
            $processGroup = posix_getpwuid(posix_getegid());
            user_error("Access attempted as {$processUser['name']}:{$processGroup['name']}");
         }
         fatal("Unable to save modlist.txt - permission issue?");
      }
      success("save");
   break;

   case 'clear':
      $result = file_put_contents($ModsDir."/modlist.txt", "", LOCK_EX);
      if ( $result === false ){
         // failed to save
         if ( function_exists("posix_getpwuid") ){
            $processUser  = posix_getpwuid(posix_geteuid());
            $processGroup = posix_getpwuid(posix_getegid());
            user_error("Access attempted as {$processUser['name']}:{$processGroup['name']}");
         }
         fatal("Unable to save modlist.txt - permission issue?");
      }
      success("clear");
   break;

   default:
      success($action);
   break;
}


fatal("Unexpected end of script");
