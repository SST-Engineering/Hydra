<?php


$id = (array_key_exists('id', $_GET))?$_GET['id']:NULL;
if (isset($id) && !is_null($id)) {
   session_start();
   $_SESSION = array();
   session_regenerate_id();
   setcookie(session_name(),session_id());
   session_destroy();
   $_COOKIE['HDAW']=NULL;
   }



include_once __DIR__."/HDA_Initials.php";
include_once __DIR__."/HDA_Finals.php";
_hydra_("HDA_Enter");


$init_display = PRO_ReadParam('DisplayHeight');
$init_display = (!isset($init_display) || is_null($init_display) || $init_display==0);
switch ($Action) {
   case 'ACTION_BrowserResize': $init_display = true; 
      $Action = $ActionLine = null;
      break;
   default: break;
   }
if ($init_display) {
   $tt = "";
   $tt .= "<html><head>";
   $tt .= "<link rel=\"icon\" href=\"http://localhost/omnicom/favicon.ico\">";
   $tt .= "</head><body>\n";
   if (!file_exists("../{$code_root}/Scripts/core.js")) { echo "can't access core.js"; die(); }
   $script = file_get_contents("../{$code_root}/Scripts/core.js");
   $tt .= "<script type=\"text/javascript\">{$script}</script>\n";
   $tt .= "<script type=\"text/javascript\">\n";
   $tt .= "var sz=getBrowserWindowSize();\n";
   $tt .= "var ie=(Browser.ie)?1:0;\n";
   $tt .= "var ipad=(navigator.platform=='iPad')?1:0;\n";
   $tt .= "var pl=(window.orientation==0)?'P':'L';\n";
   $tt .= "document.location.href='HDAW.php?load={$HDA_ROOT}&root={$code_root}&HDASID=".session_id()."&DisplayWidth='+sz.width+'&DisplayHeight='+sz.height+'&BrowserIE='+ie+'&IPAD='+ipad+'&O='+pl+'&E'\n";
   $tt .= "</script>\n";
   $tt .= "</body></html>\n";
   echo $tt; 
   exit(0);   
   }
$screen = INIT('SCREEN_LAYOUT');
if (function_exists($screen)) $content .= $screen();
else $content .= "No screen layout entry";

_finals_();


?>