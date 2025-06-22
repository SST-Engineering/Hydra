<?php

define ('MAX_NAME_WIDTH', 14);
define ("PARAM_NAME", 'HDA');
$HDA_ROOT = null;
function _hydra_($root, $loads=null, $classes=null) {
	global $HDA_ROOT;
	$HDA_ROOT = $root;
	//
	global $content, $title;
	$content = $title = "";

	//
	_set_up_session_();
	_alc_init_();
	_alc_loader_($loads, $classes);
	_validateDisplay_();
	if (isset($_REQUEST['_LOGOUT_']) || defined('_LOGOUT_')) _real_logout();
	// Reset user
	global $P;
	global $LoggedIn, $UserId, $UserCode, $UserName, $LastLoginAt, $AtLocation, $UserAllow, $DevUser;
	if (isset($P['UserId'])) {
		$LoggedIn = true;
		$UserId = $P['UserId'];
		if (isset($P['UserCode'])) $UserCode = $P['UserCode'];
		if (isset($P['UserName'])) $UserName = $P['UserName'];
		if (isset($P['LastLoginAt'])) $LastLoginAt = $P['LastLoginAt'];
		if (isset($P['AtLocation'])) $AtLocation = $P['AtLocation'];
		if (isset($P['UserAllow'])) $UserAllow = $P['UserAllow'];
		if (isset($P['DevUser'])) $DevUser = $P['DevUser'];
	}
	global $UseCookies;
	if (!$LoggedIn) {
		if ($UseCookies && isset($_COOKIE['HDAW'])) {
			$s = $_COOKIE['HDAW'];
			if (isset($s) && strlen($s)>0) {
				try {
					$ss = stripslashes($s); 
					$s = unserialize(trim($ss));
					if (isset($s) && is_array($s)) {
						foreach ($s as $k=>$p) {
							PRO_AddToParams($k, $p);
							$$k = $p;
						}
					PRO_WriteCookie();
					$LoggedIn = true;
					}
				}
				catch(Exception $e) {}
			}
		}
	}
	elseif (!isset($P['COOKIED'])) {      
		PRO_WriteCookie();
	}
	global $HDA_Product_Title;
	$HDA_Product_Title = INIT('TITLE');
	if (is_null($HDA_Product_Title) || strlen($HDA_Product_Title)==0) $HDA_Product_Title = "Trackit SST Management Services";

	global $NowInt, $NowDate;
	$NowInt = time();
	$NowDate = strtotime(date('Y-m-d 00:00',$NowInt));
	
	_discoverAction_();
	
	ResetEmailCfg(); 
	
	global $help_host;
	$help_host = INIT('HELP_HOST');	
	
	_common_plant_();
	_common_structure_();

	if (!is_null($tz = INIT('TIMEZONE'))) date_default_timezone_set($tz);
	register_shutdown_function( "alc_fatal_handler" );
	set_error_handler("alc_error_handler");
	
	// HDA DB public functions
	global $HDA_DB;
	$HDA_DB = null;
	$HDA_DB = new hda_db();
	$err = $HDA_DB->LastError();
	if ($HDA_DB->connect()===false) die("Fails in HDA connect  {$err}");
	
	ReadEmailConfig();
}
$HDA_DB = null;
$LoggedIn = false;
$LoggedInAt = NULL;
$LastLoginAt = NULL;
$UserId = NULL;
$UserCode = NULL;
$UserName = NULL;
$AtLocation = 'Not Specified';
$WarehouseId = NULL;
$UserAllow = array();
$DevUser = false;
$UseCookies = false;
$Action = '';
$ActionLine = '';
$MouseAction = NULL;
$HDA_EMAIL_CFG = array();
$HDA_SMS_CFG = array();
$help_host = null;

$HDA_AllTabList = array(
   'LD'=>array('Profiles','HDA_Profiles'),
   'PL'=>array('Monitor','HDA_PlanView'),
   'AP'=>array('Applications','HDA_AppReportForApps'),
   'US'=>array('Manage Users','HDA_ManageUsers'),
   'AD'=>array('You are Admin','HDA_AdminBody')
   );



   

function _set_up_session_() {
	global $code_root, $home_root;
	global $code_help_dir, $common_code_dir, $binary_dir, $template_dir;
	global $P;
	if (isset($_POST['HDASID'])) {
		@session_id($_POST['HDASID']);
		@error_reporting(E_ALL ^ E_WARNING);
		@session_start(); 
	}
	elseif (isset($_GET['HDASID'])) {
		@session_id($_GET['HDASID']);
		@error_reporting(E_ALL ^ E_WARNING);
		@session_start();
	} 
	else {
		@session_unset();
		@session_id("newusersession".rand());
		@error_reporting(E_ALL ^ E_WARNING);
		@session_start();
		@session_regenerate_id();
	}

	date_default_timezone_set('Europe/London');


	$code_root = (array_key_exists('root',$_GET))?$_GET['root']:NULL;
	if (is_null($code_root) && array_key_exists('HDAW_CODE_ROOT',$_SESSION)) $code_root = $_SESSION['HDAW_CODE_ROOT'];
	if (is_null($code_root)) $code_root = 'HDAWCodeRoot';
	$_SESSION['HDAW_CODE_ROOT'] = $code_root;

	$home_root = (array_key_exists('home',$_GET))?$_GET['home']:NULL;
	if (is_null($home_root) && array_key_exists('HDAW_HOME_ROOT',$_SESSION)) $home_root = $_SESSION['HDAW_HOME_ROOT'];
	if (is_null($home_root)) {
		$path = $_SERVER['PATH_TRANSLATED'];
		$home_root = basename(dirname($path));
	}
	$_SESSION['HDAW_HOME_ROOT'] = $home_root;

	// Set up other root directories
	$code_help_dir = __DIR__."/codehelp";
	$common_code_dir = __DIR__."/common";
	$binary_dir = __DIR__."/binary";
	$template_dir = __DIR__."/templates";

	$P = array();
	if (isset($_SESSION[PARAM_NAME])) {
		$P = unserialize($_SESSION[PARAM_NAME]);
	}
	if (isset($P['HDASID']) && strcmp($P['HDASID'], session_id())<>0) {
		$P = array();
		session_unset();
		session_destroy();
		error_reporting(E_ALL);
		session_start();
	}  
	foreach ($_REQUEST as $k=>$p) {
		$P[$k] = $p;
	}
	$_SESSION[PARAM_NAME] = serialize($P);

	error_reporting(E_ALL);
	// ini_set("display_errors",true);
	ini_set("ignore_repeated_errors",true);
	date_default_timezone_set('Europe/London');
}

function _common_structure_() {
	if (!file_exists("tmp")) @mkdir("tmp", 0777, true);
	if (!file_exists("tmp/xl")) @mkdir("tmp/xl", 0777, true);
	if (!file_exists("ErrorLogs")) @mkdir("ErrorLogs", 0777, true);
	if (!file_exists("Library")) @mkdir("Library", 0777, true);
	if (!file_exists("CUSTOM")) @mkdir("CUSTOM", 0777, true);
	if (!file_exists("Archive")) @mkdir("Archive", 0777, true);
	if (!file_exists("Tickets")) @mkdir("Tickets", 0777, true);
	if (!file_exists("cache")) @mkdir("cache", 0777, true);
}

function _common_plant_() {
	global $code_root, $home_root;
	global $content, $title, $key_mouse, $on_load, $post_load, $bodyclass, $bodystyle, $on_submit, $header_content_type;

	$key_mouse = "onKeyPress=\"return keyPressFalse(event);\" ";


	$on_load="";
	$post_load = "";
	$bodyclass= "wbody";
	if (!isset($on_submit)) $on_submit = "onsubmit=\"HDA_sayLoading();\"";
	$header_content_type = null;

	_include_css("style_alc");
	global $HDA_TAG_SITE;
	$HDA_TAG_SITE = INIT('TAG_SITE');
	if (!is_null($HDA_TAG_SITE) && strlen($HDA_TAG_SITE)>=3) {
	   _include_css("style_{$HDA_TAG_SITE}");
	   }

	$bodystyle="";
	global $HDA_WEB_TITLE;
	$HDA_WEB_TITLE = INIT('WEB_TITLE');
	if (is_null($HDA_WEB_TITLE)) $HDA_WEB_TITLE = "Data Management Web Service"; 
	$title = "Data Management Web Service";
	global $HDA_WEB_SUBTITLE;
	$HDA_WEB_SUBTITLE = INIT('WEB_SUBTITLE');
	if (is_null($HDA_WEB_SUBTITLE)) $HDA_WEB_SUBTITLE = " core services ";
	$sub_title = " core services ";

	global $HDA_ROOT;
	$content .= "<form enctype=\"multipart/form-data\" name=\"ALC".session_id()."\" action=\"HDAW.php?load={$HDA_ROOT}&root={$code_root}&home={$home_root}\" {$on_submit} method=\"post\">";
	$content .= "<input type=\"hidden\" name=\"HDASID\" value=\"".session_id()."\">";
}



function PRO_AddToParams($k, $p, $name=PARAM_NAME) {
   $a = array();
   if (isset($_SESSION[$name])) {
      $a = unserialize($_SESSION[$name]);
      }
   $a[$k] = $p;
   $_SESSION[$name] = serialize($a);
   global $P; $P = $a;
   return $a;
   }

function PRO_TestParam($k, $name=PARAM_NAME) {
   if (isset($_SESSION[$name])) {
      $a = unserialize($_SESSION[$name]);
      return array_key_exists($k, $a);
      }
   return false;
   }



function PRO_Clear($b, $name=PARAM_NAME) {
   if (isset($_SESSION[$name])) {
      $a = unserialize($_SESSION[$name]);
      if (is_array($b)) {
         foreach ($b as $k) {
            unset($a[$k]);
            }
         }
      else unset($a[$b]);
      $_SESSION[$name] = serialize($a);
      global $P; $P = $a;
      return $a;
      }
   return NULL;
   }


function PRO_ReadParam($k, $name=PARAM_NAME) {
   if (isset($_SESSION[$name])) {
      $a = unserialize($_SESSION[$name]);
      return (isset($a[$k]))?$a[$k]:NULL;
      }
   return NULL;
   }


function PRO_ReadAndClear($k, $name=PARAM_NAME) {
   $p = NULL;
   if (isset($_SESSION[$name])) {
      $a = unserialize($_SESSION[$name]);
      $t = isset($a[$k]);
      if ($t) {
         $p = $a[$k];
         unset($a[$k]);
         $_SESSION[$name] = serialize($a);
         global $P; $P = $a;
         }
      }
   return $p;
   }

function PRO_TestAndClear($k, $name=PARAM_NAME) {
   $p = PRO_ReadAndClear($k, $name);
   return isset($p);
   }

function PRO_FindAndClear($k, $name=PARAM_NAME) {
   global $P;
   $a = array_keys($P);
   $aa = array();
   foreach ($a as $kk) {
      if (strncmp($kk, $k, strlen($k))==0) $aa[] = $kk;
      }
   PRO_Clear($aa); 
   return $aa;
   }

function PRO_Find($k, $name=PARAM_NAME) {
   global $P;
   $a = array_keys($P);
   $aa = array();
   foreach ($a as $kk) {
      if (strncmp($kk, $k, strlen($k))==0) $aa[$kk] = $P[$kk];
      }
   return $aa;
   }

function PRO_FindMatch($k, $name=PARAM_NAME) {
   global $P;
   $a = array_keys($P);
   $aa = array();
   foreach ($a as $kk) {
      if (preg_match("/{$k}/i", $kk)) $aa[$kk] = $P[$kk];
      }
   return $aa;
   }




function _real_logout() {
   global $P;
   setcookie('HDAW',"",time()-(100*60*60*24));
   $_COOKIE['HDAW']=NULL;
   unset($_COOKIE['Tracker']);
   try {
   session_unset();
      session_id("newusersession".rand());
      session_regenerate_id();
   }
   catch (Exception $e) {}
   $P = array();
   $_SESSION[PARAM_NAME] = serialize($P);
   unset($_POST['HDASID']);
   $_POST = array();
   }

function _alc_init_() {
   if (!isset($_SESSION['HDA_INIT'])) {
      $a = array();
      $configs = glob("cfg/alc_init*.cfg");
      if (isset($configs) && (count($configs)==1)) $config_file = $configs[0]; 
      else $config_file= "cfg/alc_init.cfg";
      if ($f_init = fopen($config_file,"r")) {
        while (!feof($f_init)) {
           $l = fgets($f_init);
           $kp = explode("::",$l);
           if (count($kp)==2) {
              $kp[1][strlen($kp[1])-1] = ' '; // take off new line 
              $a[$kp[0]] = trim($kp[1]);
              }
           }
        fclose($f_init);
        }
      else {
        echo "Missing configuration file alc_init.cfg";
        }
      $_SESSION['HDA_INIT'] = serialize($a);
      }
   }
   
$HDA_Default_Includes = array(
						'Logging','AdminBody','YourAccount','ManageUsers','ManageProfiles','Profiles','PlanView','Dialogs',
						'AppsReports','XML','Email','Functions','FTP','Process','CodeCompiler','CodeLibrary','Validate','rssreader','Soap',
						'Graphics','GridView','TreeView');
// ,'TCPDF'
$HDA_Default_Classes = array('Chart','Mail','TCPDF','PDF_PARSER','HTML2PDF','HTML_PARSER','PDF','Excel18','EasyXL','FILE2TEXT');
$HDA_Default_Scripts = array('core','Actions','popup_window');
$HDA_Add_Scripts = array();
$HDA_InLine_Script = "";
$HDA_Add_Styles = array();
$HDA_Preload_Images = array('bg_main.png','bg_days.png','arrow_left.png','arrow_right.png','arrow_left_hover.png','arrow_right_hover.png','background-images-0.jpg');
$Note_Tags = array('TAG_INFO'=>'Information',
                     'TAG_DISCUSSION'=>'General Discussion', 
                     'TAG_ALERT'=>'Alert',
                     'TAG_ANNOUNCE'=>'Announcement',
                     'TAG_ANSWER'=>'An Answer',
                     'TAG_QUESTION'=>'A Question',
                     'TAG_FINISHED'=>'Complete or Closed',
                     'TAG_PROGRESS'=>'Work in Progress',
                     'TAG_WAITING'=>'Waiting..',
                     'TAG_EVENT'=>'Event');

function _alc_loader_($loads=null, $classes=null) {
	global $includes;
	global $code_root, $home_root;
	$includes = (array_key_exists('HDA_INCLUDES',$_SESSION))?$_SESSION['HDA_INCLUDES']:null;
	if (is_null($includes)) {
		$include_list = INIT('INCLUDE');
		$include_list = (!is_null($include_list))?explode(',',$include_list):array();
		$includes = array();
		foreach ($include_list as $include) {
			if (file_exists($f = "{$include}.php")) $includes[] = $f;
			elseif (file_exists($f = "../{$home_root}/{$include}.php")) $includes[] = $f;
			elseif (file_exists($f = "../{$code_root}/{$include}.php")) $includes[] = $f;
			else { echo "Missing include file {$include}"; exit; }
		}
		$_SESSION['HDA_INCLUDES'] = $includes;
	}
	$use_db = INIT('USE_DB');
	switch ($use_db) {
		case 'MYSQL':
		default: $db = __DIR__."\HDA_MYSQL_DB.php";
			break;
		case 'MSSQL':
			$db =  __DIR__."\HDA_MS_SS_DB.php";
			break;
	}

	include_once $db;
	global $HDA_Default_Includes, $HDA_Default_Classes;
	$loads = (is_null($loads))?$HDA_Default_Includes:$loads;
	foreach ($loads as $load) include_once __DIR__."/HDA_{$load}.php";
	$classes = (is_null($classes))?$HDA_Default_Classes:$classes;
	foreach ($classes as $class) {
		include_once __DIR__."/Classes/{$class}/{$class}_class.php";
	}

	foreach ($includes as $include) include_once "{$include}";
	global $HDA_Preload_Images;
	foreach ($HDA_Preload_Images as $img) _cache_image($img);
   }
function _append_script($s) {
	global $HDA_InLine_Script;
	$HDA_InLine_Script .= $s;
}
function _include_script($scripts) {
	global $HDA_Add_Scripts;
	if (!is_array($scripts)) $scripts = array($scripts);
	foreach ($scripts as $script) $HDA_Add_Scripts[] = $script;
}
function _emit_script($script) {
	global $code_root, $home_root;
	if (!@file_exists($f=$script)) {
		if (!@file_exists($f="Scripts/{$script}.js")) {
			if (!@file_exists($f="cache/{$script}.js")) {
				if (@file_exists($fx="../{$code_root}/Scripts/{$script}.js")) {
					@copy($fx, $f);
				}
			}
		}
	}
	return "<script type=\"text/javascript\" src=\"{$f}\"></script>";
}
function _include_css($styles) {
	global $HDA_Add_Styles;
	if (!is_array($styles)) $styles = array($styles);
	foreach ($styles as $style) $HDA_Add_Styles[] = $style;

}
function _emit_css($css) {
	global $code_root, $home_root;
	if (!@file_exists($f="cache/{$css}.css")) {
		if (!@file_exists($f="css/{$css}.css")) {
			if (@file_exists($fx="../{$code_root}/css/{$css}.css")) {
				@copy($fx, $f = "cache/{$css}.css");
			}
			else $f = "{$css}.css";
		}
	}
	return "<link rel=\"STYLESHEET\" type=\"text/css\" href=\"{$f}\">";
}
function _cache_image($img) {
	global $code_root;
	try {
		if (@file_exists("cache/{$img}")) return;
	}
	catch (Exception $e) {}
	try {
		if (!@file_exists($f = "Images/{$img}")) $f = "../{$code_root}/Images/{$img}";
		if (@file_exists($f)) @copy($f, "cache/{$img}");
		}
	catch(Exception $e) {}
}

function _emit_image($img, $ht=18, $mouse=null, $attributes=null, $class=null) {
	global $code_root;
	if (!@file_exists($f = "cache/{$img}")) {
		if (!@file_exists($f = "Images/{$img}")) {
			if (@file_exists($fx = "../{$code_root}/Images/{$img}")) {
				@copy($fx, $f = "cache/{$img}");
			}
			else $f = $img;
		}
	}
	if (!is_null($ht)) $ht = "height={$ht}px";
	if (is_null($class)) $class = "alc-img-icon";
	return "<img class=\"{$class}\" src=\"{$f}\" {$attributes} {$ht} {$mouse} >";
}

function _click_dialog($dialog, $params=null) {
	return "onclick=\"issuePost('{$dialog}{$params}---',event); return false;\" ";
}
function _change_dialog($dialog, $params=null) {
	return "onchange=\"issuePost('{$dialog}{$params}---',event); return false;\" ";
}


function _validateDisplay_() {
   global $__DisplayHeight_;
   global $__DisplayWidth_;
   global $_DisplayHeight;
   global $_DisplayWidth;
   global $_ViewHeight;
   global $_ViewWidth;
   global $_Mobile;
   $__DisplayHeight_ = $_DisplayHeight = PRO_ReadParam('DisplayHeight');
   if (!isset($__DisplayHeight_)) $__DisplayHeight_=$_DisplayHeight = 600;
   $__DisplayWidth_ = $_DisplayWidth = PRO_ReadParam('DisplayWidth');
   if (!isset($__DisplayWidth_)) $__DisplayWidth_=$_DisplayWidth = 700;

   $w = INIT('SCREEN_ADJUST_W'); if (is_null($w)) $w = 30;
   $h = INIT('SCREEN_ADJUST_H'); if (is_null($h)) $h = 98;
   $_DisplayHeight-=32; $_ViewHeight = $_DisplayHeight-$h;
   $_DisplayWidth-=32; $_ViewWidth = $_DisplayWidth-$w;

   $_Mobile=($_DisplayWidth<600)?"-mob":NULL;
   }



   
function _allTabList() {
   global $HDA_AllTabList;
   $tabs = $HDA_AllTabList;
   if (function_exists('_customScreenTabList')) $tabs = _customScreenTabList($tabs);
   return $tabs;
   };

function _screenTabList(&$what_tab) {
   global $UserCode;
   $myTabList = PRO_ReadParam('MyTabList');
   if (is_null($myTabList)) {
      $allow = hda_db::hdadb()->HDA_DB_UserIsAllowed($UserCode);
	  $myTabList = _allTabList();
	  if (!array_key_exists('USER_TABS', $allow)) {
	      $DevUser = (array_key_exists('ADMIN',$allow)&&($allow['ADMIN']==1));
		  $AppUser = (array_key_exists('APPER',$allow)&&($allow['APPER']==1));
		  $MonUser = (array_key_exists('MONITOR',$allow)&&($allow['MONITOR']==1));
		  $tabs = array();
		  if ($DevUser) {
		     foreach ($myTabList as $k=>$tab) $tabs[] = $k;
			 }
		  elseif ($AppUser) {
		     $tabs[] = 'AP'; $tabs[] = 'RP';
			 }
		  elseif ($MonUser) {
		     $tabs[] = 'IN';
			 }
		  $allow['USER_TABS'] = $tabs;
		  hda_db::hdadb()->HDA_DB_writeUserAllow($UserCode, $allow);
		  }
	  $useTabList = array();
	  foreach ($allow['USER_TABS'] as $tab) {
		 if (array_key_exists($tab, $myTabList)) $useTabList[$tab] = $myTabList[$tab];
	     }
	  $myTabList = $useTabList;	     
	  }
   PRO_AddToParams('MyTabList',$myTabList);
   $what_tab = PRO_ReadParam('HDA_TAB');
   if (!isset($what_tab)) $what_tab = 'PL';
   if (!array_key_exists($what_tab, $myTabList)) {
      $these_tabs = array_keys($myTabList);
      $what_tab = (count($these_tabs)>0)?$these_tabs[0]:null;
      }
	  
   PRO_AddToParams('HDA_TAB', $what_tab);
   return $myTabList;
   }
function _customScreenTabList($tablist) {
   $custom_tabs = INIT('CUSTOM_TABS');
   if (!is_null($custom_tabs)) {
      $tabs = explode(',',$custom_tabs);
	  foreach ($tabs as $tab) {
	     $tab_bits = explode('-',$tab);
		 if (count($tab_bits)==3) {
		    $tablist[$tab_bits[0]] = array($tab_bits[1],$tab_bits[2]);
		    }
	     }
      }
   return $tablist;
   }   






function PRO_WriteCookie() {
   global $UserId;
   global $UserName;
   global $UserCode;
   global $LastLoginAt;
   global $WarehouseId;
   global $AtLocation;
   $s = array();
   $s['UserCode'] = $UserCode;
   $s['UserId'] = $UserId;      
   $s['UserName'] = $UserName;
   $s['LastLoginAt'] = date('Y-m-d G:i:s',time());
   $s['WarehouseId'] = $WarehouseId;
   $s['AtLocation'] = $AtLocation;
   setcookie('HDAW',serialize($s),time()+30*60*60*24);
   PRO_AddToParams('COOKIED',true);
   }




function PRO_postAction($act) {
   PRO_AddToParams($act,0);
   }

function _discoverAction_() {
   global $Action;
   global $ActionLine;
   global $MouseAction;
   $a = PRO_FindAndClear('ACTION_');
   if (isset($a) && is_array($a) && count($a)>0) {
      $ActionLine = $a[0];
      if ($ActionLine[strlen($ActionLine)-2]=='_') {
         $ActionLine[strlen($ActionLine)-1]='x';
         $x = $_POST[$ActionLine];
         $ActionLine[strlen($ActionLine)-1]='y';
         $y = $_POST[$ActionLine];
         $MouseAction = array($x, $y);
         $ActionLine = substr($ActionLine, 0, strlen($ActionLine)-2);
         }
      $action = explode('-', $ActionLine);
      $Action = $action[0];
      PRO_AddToParams('Action', $Action);
      PRO_AddToParams('ActionLine', $ActionLine);
      }
   return $Action;
}

function PRO_onAction() {
   return PRO_ReadParam('Action');
   }






function INIT($k) {
   if (!array_key_exists('HDA_INIT', $_SESSION)) _alc_init_();
   $a = unserialize($_SESSION['HDA_INIT']);
   return (array_key_exists($k, $a))?$a[$k]:NULL;
   }



function ResetEmailCfg() {
   global $HDA_EMAIL_CFG;
   $HDA_EMAIL_CFG['SUSPEND_GET_MAIL'] = INIT('SUSPEND_GET_MAIL');
   $HDA_EMAIL_CFG['GET_MAIL_IMAP'] = INIT('GET_MAIL_IMAP');
   $HDA_EMAIL_CFG['GET_MAIL_ACCOUNT'] = INIT('GET_MAIL_ACCOUNT');
   $HDA_EMAIL_CFG['GET_MAIL_PASSWORD'] = INIT('GET_MAIL_PASSWORD');
   $HDA_EMAIL_CFG['GET_MAIL_ANYONE'] = INIT('GET_MAIL_ANYONE');
   $HDA_EMAIL_CFG['GET_MAIL_HTML'] = INIT('GET_MAIL_HTML');
   $HDA_EMAIL_CFG['EMAIL_ENABLED'] = INIT('EMAIL_ENABLED');
   $HDA_EMAIL_CFG['GMAIL_HOST'] = INIT('GMAIL_HOST');
   $HDA_EMAIL_CFG['GMAIL_PORT'] = INIT('GMAIL_PORT');
   $HDA_EMAIL_CFG['GMAIL_USERNAME'] = INIT('GMAIL_USERNAME');
   $HDA_EMAIL_CFG['GMAIL_PASSWORD'] = INIT('GMAIL_PASSWORD');
   $HDA_EMAIL_CFG['GMAIL_FROM'] = INIT('GMAIL_FROM');
   $HDA_EMAIL_CFG['GMAIL_FROM_NAME'] = INIT('GMAIL_FROM_NAME');
   $HDA_EMAIL_CFG['GMAIL_AUTH'] = INIT('GMAIL_AUTH');
   $HDA_EMAIL_CFG['GMAIL_AUTH_TYPE'] = INIT('GMAIL_AUTH_TYPE');
   $HDA_EMAIL_CFG['GMAIL_SECURE'] = INIT('GMAIL_SECURE');
   $HDA_EMAIL_CFG['GMAIL_REALM'] = INIT('GMAIL_REALM');
   $HDA_EMAIL_CFG['GMAIL_WORKSTATION'] = INIT('GMAIL_WORKSTATION');
   $HDA_EMAIL_CFG['EMAIL_ERROR_ACCOUNT'] = "tim_s_jones@hotmail.com";
   }



function ResetSMSCfg() {
   global $HDA_SMS_CFG;
   $HDA_SMS_CFG['SUSPEND_SMS'] = INIT('SUSPEND_SMS');
   $HDA_SMS_CFG['SMS_USERNAME'] = INIT('SMS_USERNAME');
   $HDA_SMS_CFG['SMS_PASSWORD'] = INIT('SMS_PASSWORD');
   $HDA_SMS_CFG['SMS_ACCID'] = INIT('SMS_ACCID');
   $HDA_SMS_CFG['SMS_DAYLIMIT'] = INIT('SMS_DAYLIMIT');
   $HDA_SMS_CFG['SMS_PROFILELIMIT'] = INIT('SMS_PROFILELIMIT');
   }


// COMMON
  function _alc_error_details() {
     $error = "";
	$has_db = false;
	$pid = getmypid();
	global $HDA_DB;
	if (is_null($HDA_DB)) {
	   if (class_exists('hda_db') && (!is_object(hda_db::hdadb()))) {
		  $HDA_DB = new hda_db();
		  if ($HDA_DB->connect()===false) $an_error .= " (db) {$HDA_DB->last_error}";
		  else $has_db = true;
		  }
	   }
	else {
	   if (class_exists('hda_db') && (is_object(hda_db::hdadb()))) {
		  $has_db = true;
		  }
	   }
	if ($has_db) {
		hda_db::hdadb()->HDA_DB_DropLock(null, null);
		$_dir = pathinfo($_SERVER['SCRIPT_FILENAME'],PATHINFO_DIRNAME);
		$in_job = hda_db::hdadb()->HDA_DB_monitorRead(null, null, $pid);
		if (!is_null($in_job)) {
			$log_ext = $in_job['ItemId'];
			$pendingq = $in_job['ItemQ'];
			$error .= print_r($error, true)."\n";
			$error .= "Shutdown report for job in q {$in_job['InQ']} ".print_r($in_job, true);
			$error .= " Profile Title: ".hda_db::hdadb()->HDA_DB_TitleOf($in_job['ItemId']);
			hda_db::hdadb()->HDA_DB_monitorClear($in_job['SessionId']);
			if (!is_null($pendingq)) hda_db::hdadb()->HDA_DB_RemovePending($pendingq);
			try {
			   $loc_path = "{$_dir}/CUSTOM/{$in_job['ItemId']}";
			   //@file_put_contents("{$loc_path}/debug.log", @file_get_contents("{$loc_path}/debug.log")."\n{$error}");
			   @file_put_contents("{$loc_path}/console.log", @file_get_contents("{$loc_path}/console.log")."\n{$error}");
			   }
			catch (Exception $e) {
			   }
			}
	   }
	 $error = "For PID {$pid}, ".(($has_db)?"With DB":"No DB access")."\n{$error}";
	 return $error;
     }

  function alc_fatal_handler() {
	 $error = error_get_last();
	 if (!is_null($error)) {
	    $log_ext = "Fatal".date('Ymdhis',time());
	    $pid = getmypid();
		$error = print_r($error, true)."\nPID:{$pid}\n";
		$error .= _alc_error_details();
	    $_dir = pathinfo($_SERVER['SCRIPT_FILENAME'],PATHINFO_DIRNAME);
	    @file_put_contents($_dir."\\ErrorLogs\\{$log_ext}.log", $error = "Fatal error ".session_id()."\n{$error}");
		if (function_exists('HDA_SendErrorMail')) HDA_SendErrorMail($error);
		}
	return true;
     }


  function alc_error_handler($errno, $errstr, $errfile, $errline, $errcontext) {
     try {
        $an_error = "HDA ERR HAN : {$errno} {$errstr} {$errfile} {$errline}";
		$an_error .= "\n"._alc_error_details()."\n";
        if (function_exists("HDA_SendErrorMail")) HDA_SendErrorMail($an_error);
        return true;
        }
     catch (Exception $e) {
        echo "Exception in error handling {$e->message}\n";
        echo "HDA ERR HAN : {$errno} {$errstr} {$errfile} {$errline}\n";
        print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
        die();
        }
     return false;
     }



?>