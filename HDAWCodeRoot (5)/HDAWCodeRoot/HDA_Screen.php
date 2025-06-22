<?php




function HDA_ScreenLayout() {
   global $Action;
   global $ActionLine;

   global $LoggedIn;
   global $UserCode;
   global $UserName;
   global $onProfile;
   global $DevUser;
   global $AtLocation;
   global $WarehouseId;
   global $HDA_Product_Title;

   global $_DisplayHeight;
   global $_DisplayWidth;
   global $_ViewHeight;
   global $_ViewWidth;
   global $_Mobile;

   $problem = null;
   global $code_root, $home_root;
   $t = "";

   switch ($Action) {
      case 'ACTION_Logout':
         hda_db::hdadb()->PRO_DB_RealLogout($UserCode);
         _real_logout();
         $LoggedIn = false;
         break;
	  case 'ACTION_gotoTab':
      case 'ACTION_Tabbed':
         list($action, $toTab) = explode('-',$ActionLine);
         PRO_AddToParams('HDA_TAB', $toTab);
         break;
      }
   $HDA_Product_Title = INIT('TITLE');
   if (is_null($HDA_Product_Title) || strlen($HDA_Product_Title)==0) $HDA_Product_Title = "Trackit SST Management Services";
   $HDA_Product_Icon = INIT('BANNER_ICON');
   if (is_null($HDA_Product_Icon)) $HDA_Product_Icon = 'ALCBanner.jpg';
   
   $this_emit = (!$LoggedIn)?_dialogLogin("_dialogLogin"):null;

   if (!$LoggedIn) {
      hda_db::hdadb()->HDA_DB_Bootstrap();
	  $mask_class = 'hda-mask-login';
      }
   else {
      hda_db::hdadb()->HDA_DB_CHANGES();
	  $mask_class = null;

      $t .= "<div class=\"alc-banner\" >";
      $t .= "<div style=\"float:left;height:42px;overflow:hidden;\">";//
	  //<img src=\"getimage.php?root={$code_root}&home={$home_root}&img={$HDA_Product_Icon}\" height=38px; >";
         $t .= "<div class=\"alc-banner-coname hda-imprint-text\" >&nbsp;&nbsp;{$HDA_Product_Title}";
         $t .= "</div></div>";
      $t .= "<div style=\"float:right;color:darkgrey;\">";
      $t .= "<center>{$UserName}<br>";
	  $mouse = _click_dialog("_dialogYourAccount");
	$t .= "<div class=\"hda-btn-blue\" style=\"overflow:hidden;\" >"._emit_image("Username.jpg",34,$mouse,"title=\"Your Account\"",'hda-btn-icon')."</div>";
	  $mouse = "onclick=\"issuePost('Logout',event); return false; \" ";
	$t .= "&nbsp;<div class=\"hda-btn-blue\" style=\"overflow:hidden;\" >"._emit_image("Exit.jpg",34,$mouse,"title=\"Logout\"",'hda-btn-icon')."</div>";
      $t .= "</center></div>";
      $t_msgblk = "<div id=\"Watchdog_Message\" class=\"alc-watch-block\" style=\"margin-top:0px;\" >"; 
      $t_msgblk .= "<div id=\"Watchdog_Message_Text\" class=\"alc-watch-text\" style=\"margin-left:0px;\" ></div>";
      $t_msgblk .= "<div id=\"Watchdog_Message_TextLen\" style=\"display:none;position:absolute;top:-500;left:-1000;font-size:10px;whitespace:no-wrap;white-space:nowrap; \"></div>";
      $t_msgblk .= "</div>";
      $t.= "<DIV class=\"alc-msg-block\" style=\"width:".($_ViewWidth-124)."px;float:left;margin-top:-4px;\" >{$t_msgblk}</DIV>";
      $t .= "</div>";

	  $tab_list = _screenTabList($what_tab);
      hda_db::hdadb()->HDA_DB_stayOnline($UserCode, $what_tab);
	   if (preg_match("/ACTION_(?P<dialog>_dialog[A-Za-z0-9]{1,})/",$Action,$matches)) {
		   $dialog = $matches['dialog'];
		   $this_emit .= $dialog($dialog);
	   }
      global $key_mouse;
	  global $Tab_Menu;
      $inner_div_style = "border:none; height:".($_ViewHeight-38)."px;width:".($_ViewWidth-16)."px;margin:6px;overflow-x:hidden;overflow-y:auto;";
      $use_table_class = "alc-table";
	  if (strlen($this_emit)==0) {
		  $tab_body = (!is_null($what_tab))?call_user_func($tab_list[$what_tab][1]):'No pages are valid for this user';
		  $_SESSION['body'] = $tab_body;
		  $_SESSION['tabs'] = $Tab_Menu;
	  }
	  else {
		  $len = strlen($this_emit);
		  $tab_body = $_SESSION['body'];
		  $Tab_Menu = $_SESSION['tabs'];
	  }
	  

      $t .= "<div class=\"alc-view\" id=\"HDA_VIEW\" style=\"height:{$_ViewHeight}px;width:{$_ViewWidth}px;padding:0px;background-color:white;\" {$key_mouse} >";
	  $t .= "<div class=\"hda-tab-menu\" style=\"height:24px;width:".($_ViewWidth-16)."px;\" >{$Tab_Menu}</div>";
      $t .= "<div style=\"{$inner_div_style}\"  >";
      $t .= "<table class=\"{$use_table_class}\"  >";
	  $t .= $tab_body;
      $t .= "</table></div></div>"; // HDA_VIEW


      $t .= "<div style=\" height:22px;width:{$_ViewWidth}px;position:relative;margin-top:8px; \" >";
	  $tabw = $_ViewWidth-360;
	  $t .= "<div style=\" height:22px; width:{$tabw}px;position:relative; float:left; text-align:left;\" >";
	  $t .= "<div class='hda-tab-buttons'>";
	  foreach ($tab_list as $tab=>$tab_array) {
         $mouse = "onclick=\"issuePost('Tabbed-{$tab}---');\" ";
         $class = ($what_tab==$tab)?"class=\"active\" ":"";
		 $t .= "<span {$class} title=\"{$tab_array[0]}\" {$mouse} >{$tab_array[0]}</span>";
	  }
    $t .= "</div>";
	  $t .= "</div>";
	  
      $t .= "<div id=\"WatchDog\" style=\"flow:in-line; float:right;margin-top:8px; margin-right:0px;height:10px; width:32px; background-color:green; border:medium ridge #f0f0f0;\">";
      $t .= "<IFRAME src=\"HDAW.php?load=HDA_Watchdog&HDASID=".session_id()."\" scrolling=\"no\" style=\"height:10px; width:100%; border:none\"></IFRAME>";
      $t .= "</div>";
      $t .= "<DIV style=\"flow:in-line; float:right; margin-top:10px;padding-right:4px;padding-left:4px;color:darkgrey;\">";
      $onlineP = PRO_ReadParam('OnLineCountP');
      $t .= "<input type=\"hidden\" name=\"OnLineCountP\" id=\"OnLineCountP\" value=\"{$onlineP}\" >";
$t .= (INIT('MASTER')=='Y')?"MASTER&nbsp;&nbsp;":"";$t .= INIT('USE_DB')."&nbsp;&nbsp;";
	  $t .= date_default_timezone_get()."&nbsp;&nbsp;";
      if (!isset($onlineP) || strlen($onlineP)==0) $onlineP="&nbsp;&nbsp;&nbsp;";
      $t .= "Online users: <span class=\"onclick\" id=\"OnLineCount\" name=\"OnLineCount\" >{$onlineP}</span>";
	  $mouse = _click_dialog("_dialogShowUsers");
      $t .= "<span class=\"click-here\" {$mouse} >[ Show Users ]</span>";
      $t .= "</div>";
      $t .= "</div>";

      }
   $tt = "<center>";
   $tt .= "<div  style=\"position:relative; padding:2px; border:none; width:{$_DisplayWidth}px; height:{$_DisplayHeight}px; \" >"; // outer
   $tt .= "<div class=\"alc-body\" id=\"alc_body\" >";
   
   if (is_null($mask_class)) $mask_class = (strlen($this_emit)==0)?"alc-mask hda-background":"alc-masked hda-background";
   $tt .= "<div class=\"{$mask_class}\" id=\"alc_mask\" >";
   $tt .= $t;
   $tt .= "</div>"; // mask
   $tt .= $this_emit;

      // LOADING PROGRESS
      // ----------------
      $tt .= "<div id=\"pad_loading\" class=\"alc-dialog\" style=\"background-color:white;display:none;\" >";
      $tt .= "<p>Loading ...</p>";
      $tt .= "</div>";


   $tt .= "</div>"; // body
      
   $tt .= "</div>"; // outer
   $tt .= "</center>";

   return $tt;
   }


function _validTabAction($tab) {
   global $HDA_AllTabList;
   if (is_null($tab)) return "Unknown";
   if (array_key_exists($tab, $HDA_AllTabList)) return $HDA_AllTabList[$tab][0];
   return $tab;
   }

function _dialogShowUsers($dialog_id='alc_users') {
   global $Action;
   global $_Mobile;
   global $code_root, $home_root;


   $users = null;
   switch ($Action) {
      case "ACTION_{$dialog_id}":
         $users = hda_db::hdadb()->HDA_DB_whoOnline();
         if (!is_array($users)) $users = null;
         break;
      }
   if (is_null($users)) return "";
   $t = _makedialoghead($dialog_id, "Online Users");
   $t .= "<table class=\"alc-table\" >";
   $t .= "<tr><th>User</th><th>Logged on</th><th>Last Activity</th><th>Currently</th><th>Subject</th></tr>";
   foreach ($users as $user) {
      $t .= "<tr>";
      $t .= "<td>".hda_db::hdadb()->HDA_DB_GetUserFullName($user['UserItem'])."</td>";
      $t .= "<td>".hda_db::hdadb()->PRO_DBdate_Styledate($user['Logon'],true)."</td>";
      $t .= "<td>".hda_db::hdadb()->PRO_DBdate_Styledate($user['Activity'], true)."</td>";
      $t .= "<td>"._validTabAction($user['Doing'])."</td>";
      $t .= "<td>{$user['OnTitle']}</td>";
      $t .= "</tr>";
      }
   $t .= "</table>";
   $t .= _makedialogclose();

   return $t;
   }

function _dialogLogin($dialog_id = '_dialogLogin') {
   global $Action, $ActionLine;
   global $LoggedIn;
   global $_Mobile;
   global $code_root, $home_root;
   global $UserName;
   $problem = null;
   $is_reset = false;
   switch ($Action) {
      case "ACTION_{$dialog_id}_Login":
	     $e = PRO_ReadParam("{$dialog_id}_Email");
		 $p = PRO_ReadAndClear("{$dialog_id}_PW");
         if (HDA_validLogin($e, $p)) {
            HDA_WatchThis("LOGIN", "{$UserName} Logged In");
            }
		 elseif (function_exists('CustomValidLogin') && CustomValidLogin($e,$p)) {
		    HDA_WatchThis("LOGIN", "{$UserName} Logged In");
		    }
         else {
            $problem = "Invalid Login or Password"; 
            }
		 PRO_Clear('MyTabList');
		 $Action = $ActionLine = "";
         break;
	  case "ACTION_{$dialog_id}_Reset":
	     $is_reset = 'Reset';
		 break;
	  case "ACTION_{$dialog_id}_newPW":
		 $email = PRO_ReadParam("{$dialog_id}_Email");
         if (!isset($email) || is_null($email) || strlen($email)<3) $problem = "Must provide an email address";
         else {
            $user = hda_db::hdadb()->HDA_DB_FindUser($email);
            if (is_null($user) || !is_array($user) || count($user)<>1) {
               $problem = "The email address {$email} does not seem to be registered with this site";
               }
		    else {
			   $newpw = _gen_pw();
			   $msg = "You requested a new password reset for {$user[0]['UserFullName']}<br>Your new password is the following 8 characters:<br>{$newpw}<br>";
               $pusers[] = array($email,$user[0]['UserFullName']);
               $mail = array('EMAIL'=>$pusers, 
                       'SUBJECT'=>"Reset Password",
                       'MESSAGE'=>$msg,
                       'FROM'=>$user[0]['UserFullName']);
               if (!HDA_SendMail('ResetPassword',$mail, $err)) {
                  $problem = "Failed to send new password to {$email}, password unchanged";
                  }
               else {
				  $problem = "New Password sent to {$email}";
				  $e = hda_db::hdadb()->HDA_DB_AddPassword($user[0]['UserItem'], password_hash($newpw,PASSWORD_DEFAULT));
				  if ($e===false) {$problem = "Failed to update password, ignore new password sent in email, password unchanged";}
			      }
			   }
		    }
         break;
      }
   global $HDA_Product_Title;
   if ($LoggedIn) return "";
   $t = _makedialoghead($dialog_id, $HDA_Product_Title, '', $can_close=false);
   switch ($is_reset) {
	   default: {
		   if (!is_null($problem)) $t .= "<tr><th colspan=2 style=\"color:red;\">{$problem}</th></tr>";
		   $t .= "<tr><th>Email Address:</th><td><input type=\"text\" class=\"alc-dialog-name\" name=\"{$dialog_id}_Email\" ></td></tr>";
		   $mouse = "onKeyPress=\"return keyPressPost('{$dialog_id}_Login',event)\" ";
		   $t .= "<tr><th>Password:</th><td><input type=\"password\"  class=\"alc-dialog-name\" name=\"{$dialog_id}_PW\" {$mouse} >";
		   $mouse = _click_dialog($dialog_id,"_Reset");
		   $t .= "&nbsp;&nbsp;<span class=\"click-here\" style=\"color:blue;\" {$mouse} >[ Forgot Password? ]</span></td></tr>";
		   $mouse = _click_dialog($dialog_id,"_Login");
		   $t .= "<tr><th  class=\"buttons\" colspan=2>";
		   $t .= "<span class=\"push_button blue\" {$mouse}   >Submit</span>"; 
		   $t .= "</th></tr>";
		   break;
	   }
	   case 'Reset': {
		   $t .= "<tr><th style=\"color:red;\" colspan=2>{$problem}</th></tr>";
		   $t .= "<tr><td>Your registered email address:</td><td><input type=\"text\" name=\"{$dialog_id}_Email\" size=30></td></tr>";
		   $mouse = _click_dialog($dialog_id,"_newPW");
		   $t .= "<tr><th colspan=2><span class=\"click-here\" style=\"color:blue;\" {$mouse}>[ Reset Now ]</th></tr>";
		   break;
	   }
   }
	   
   $t .= _makedialogclose();
   return $t;
   }
   
function HDA_CustomPage($page, $isa, $a) {
   if (function_exists($page)) return call_user_func($page, $isa, $a);
   return null;
   }
   




?>