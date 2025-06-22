<?php
include_once __DIR__."/HDA_Initials.php";
include_once __DIR__."/HDA_Finals.php";
_hydra_("HDA_Watchdog", $loads = array('XML','DB','Functions','Process','CodeCompiler','Validate','Email','Logging'));




$t = "";
$pulse = PRO_ReadParam('WatchdogPulse');
if (!isset($pulse)) $pulse = 0;
else {$pulse += 2; if ($pulse>20) $pulse = 0;}
PRO_AddToParams('WatchdogPulse', $pulse);


$log = PRO_ReadParam('WatchdogLog');
if (!isset($log)) $log = array();
else $log = hda_db::hdadb()->HDA_DB_unserialize($log);
_include_script("Watchdog");

$back_color = ($pulse==0)?"yellow":"green";
$t .= "<div style=\"position:absolute;display:inline-block;top:0;left:0;margin:0; padding:0; width:100%; height:10; background-color:".$back_color.";\">";
$t .= "<div style=\"display:inline-block; float:left; background-color:blue; width:2px; height:10; margin-left:".($pulse*5)."%;\">";
$t .= "</div>";
$t .= "</div>";

$msg = array();

$using_cron = INIT('CRON')==1;
$you_are_background = false;
if (!$using_cron) $you_are_background = hda_db::hdadb()->HDA_DB_TakeLock('CRON');
if ($you_are_background) {
   $m = "Background processing";
   hda_db::hdadb()->HDA_DB_WriteWatchMessage($UserCode, 'CRON', $m);
   }

if ($pulse==0) {
   try {
      $a = hda_db::hdadb()->HDA_DB_whoOnline();
      if (isset($a) && is_array($a) && count($a)>0) {
         $t .= "<script type=\"text/javascript\">\n";
         $t .= "SayOnlineCount('".count($a)."');\n";
         $t .= "</script>";
         foreach ($a as $row) {
            $k = 'LOGON:'.$row['UserItem'];
            $read = array_key_exists($k, $log);
            if (!$read || $log[$k]>($NowInt-300)) {
               if (!$read) $log[$k]=$NowInt;
               $user = hda_db::hdadb()->HDA_DB_GetUserFullName($row['UserItem']);
               $msg[] = array($read, " ++ ".date('G:i', strtotime($row['Logon'])).": {$user} logged on");
               }
            }
         }
      }
   catch (Exception $e) {
      $msg[] = array(false, " ++ ".$e->getMessage());
      }
   }



if ($pulse==20) {
   try {
      $a = hda_db::hdadb()->HDA_DB_GetWatchMessages($ago=3);
      if (isset($a) && is_array($a) && count($a)>0) foreach ($a as $row) {
         $k = $row['WatchMessage'];
         $read = array_key_exists($k, $log);
         if (!$read) $log[$k]=$NowInt;
         $user = hda_db::hdadb()->HDA_DB_GetUserFullName($row['SentFrom']);
         $msg[] = array($read, " ++ ".date('G:i', strtotime($row['IssuedDate'])).": {$row['Message']} - {$user}");   
         }
      }
   catch (Exception $e) {
      $msg[] = array(false, " ++ ".$e->getMessage());
      }
   }
else {
   $x_msg = PRO_ReadParam('WATCH_MSGS');
   if (!isset($x_msg) || strlen($x_msg)==0) $x_msg = array();
   else $x_msg = hda_db::hdadb()->HDA_DB_unserialize($x_msg);
   for ($i=0; $i<count($x_msg); $i++) { $x_msg[$i][0]=true; $msg[] = $x_msg[$i]; }
   }
PRO_AddToParams('WATCH_MSGS', hda_db::hdadb()->HDA_DB_serialize($msg));


$t .= "<script type=\"text/javascript\">\n";
$msg[] = array(true, "  Watchdog: ");
$tt = "var msgs = [ ";
foreach ($msg as $m) {
   $msg_color = ($m[0])?"watchdog-read-text":"watchdog-unread-text"; 
   $tt .= "'<span class=\"{$msg_color}\">{$m[1]}</span>',";
   }
$tt[strlen($tt)-1]=' ';
$t .= "{$tt}];\n";

$t .= "</script>";

$on_load.="Watchdog_Pulse('".session_id()."');";
PRO_AddToParams('WatchdogLog', hda_db::hdadb()->HDA_DB_serialize($log));

$content .= $t;
$content_iframe_embedded = true;
_finals_();

?>