<?php

function _dialogYourAccount($dialog_id = 'hda_your_account') {
   global $Action, $ActionLine;
   global $UserCode;
   global $key_mouse;
   global $code_root, $home_root;
   $options = hda_db::hdadb()->HDA_DB_GetUserOptions($UserCode);
   switch($Action) {
		case "ACTION_{$dialog_id}":
			break;
		default: return "";
		case "ACTION_{$dialog_id}_CHANGE_PROFILE":
			list($action, $change_what) = explode('-',$ActionLine);
			switch ($change_what) {
				case 'EMAIL_ME':
					$to_set = PRO_ReadAndClear('EMAIL_ME');
					$to_set = (!isset($to_set) || is_null($to_set) || $to_set<>1)?0:1;
					$options['EMAIL_ME'] = $to_set;
					hda_db::hdadb()->HDA_DB_writeUserOptions($UserCode, $options);
					break;
				}
			break;
		}
   PRO_Clear('EMAIL_ME');
   $t = _makedialoghead($dialog_id, "Your Account");
   $t .= "<colgroup><col style=\"width:32px;\"><col style=\"width:*;\" ></colgroup>";
   $mouse = _click_dialog("_dialogChangePW");
   $t .= "<tr><td><span class=\"click-here\" title=\"Change Password..\" {$mouse}>";
   $t .= _emit_image("Password.jpg",32)."</span>";
   $t .= "</td><td><span class=\"click-here\" {$mouse}>Change Password</span></td></tr>";
   $mouse = _click_dialog("_dialogChangeUName");
   $t .= "<tr><td><span class=\"click-here\" title=\"Change Username..\" {$mouse}>";
   $t .= _emit_image("Username.jpg",32)."</span>";
   $t .= "</td><td><span class=\"click-here\" {$mouse}>Change User Name</span></td></tr>";

   $checked = (array_key_exists('EMAIL_ME',$options) && ($options['EMAIL_ME']==1))?"CHECKED":"";
   $t .= "<tr><td colspan=2>Send change notifications by email to me";
   $mouse = _click_dialog($dialog_id,"_CHANGE_PROFILE-EMAIL_ME");
   $t .= "&nbsp;<input type=\"checkbox\" name=\"EMAIL_ME\" value=\"1\" {$checked} {$mouse} ></td></tr>";
   $mouse = _click_dialog("_dialogEmailDetails");
   $t .= "<tr><td><span class=\"click-here\" title=\"Update Options..\" {$mouse}>";
   $t .= _emit_image("Details.jpg",32)."</span>";
   $t .= "</td><td><span class=\"click-here\" {$mouse}>Email notify options</span></td></tr>";
   $t .= _makedialogclose();

   return $t;
   }


function _dialogChangePW($dialog_id = 'alc_change_pw') {
   global $Action;
   global $UserCode;
   global $key_mouse;
   $problem = null;
   $request = null;
   switch ($Action) {
      case "ACTION_{$dialog_id}":
         $request = true;
         break;
      case "ACTION_{$dialog_id}_CHANGE_PW":
         $request = true;
         $pw1 = PRO_ReadAndClear('YT_PW1');
         $pw2 = PRO_ReadAndClear('YT_PW2');
         if (isset($pw1) && isset($pw2) && strlen($pw1)==strlen($pw2) && $pw1==$pw2) {
            if (hda_db::hdadb()->HDA_DB_AddPassword($UserCode,password_hash($pw1,PASSWORD_DEFAULT)))
               $problem = "Password Changed";
            else $problem = "Failed to change password";
            }
        elseif (isset($pw1) || isset($pw2)) $problem = "Password mismatch - try again";      
        break;
      }
   if (is_null($request)) return "";

   $t = _makedialoghead($dialog_id, "Change Password");
   if (!is_null($problem)) $t .= "<tr><th colspan=2 style=\"color:red;\">{$problem}</th></tr>";
   $t .= "<tr><th>New Password:</th><td><input type=\"password\" class=\"alc-dialog-name\" name=\"YT_PW1\" value=\"\" {$key_mouse}></td></tr>";
   $t .= "<tr><th>Confirm Password:</th><td><input type=\"password\" class=\"alc-dialog-name\" name=\"YT_PW2\" value=\"\" {$key_mouse}></td></tr>";
   $t .= _closeDialog($dialog_id, "_CHANGE_PW", 2);
   $t .= _makedialogclose();

   return $t;
   }


function _dialogChangeUName($dialog_id='alc_change_uname') {
   global $Action;
   global $UserCode;
   global $UserName;
   $request = true;
   switch ($Action) {
      case "ACTION_{$dialog_id}":
         $request = true;
         break;
      case "ACTION_{$dialog_id}_CHANGE_UNAME":
        if (hda_db::hdadb()->HDA_DB_GetUserFullName($UserCode, $UserName = PRO_ReadParam('YT_UNAME'))) PRO_AddToParams('UserName',$UserName); else $UserName=PRO_ReadParam('UserName');
        break;
      }
   if (is_null($request)) return "";
   $t = _makedialoghead($dialog_id, "Change Your User Name");
   $t .= "<tr><th>New Name:</th><td><input type=\"text\" class=\"alc-dialog-name\" name=\"YT_UNAME\" value=\"\" ></td></tr>";
   $mouse = "onclick=\"issuePost('{$dialog_id}_CHANGE_UNAME',event);return false;\" ";
   $t .= "<tr><th colspan=2>";
   $t .= "<span class=\"push_button blue\" {$mouse}   >Submit</span>"; 
   $t .= "</th></tr>";
   $t .= _makedialogclose();

   return $t;
   }

function _dialogEmailDetails($dialog_id='alc_email_details') {
   global $Action;
   global $UserCode;
   $request = true;
   switch ($Action) {
      case "ACTION_{$dialog_id}":
         $request = true;
         break;
      case "ACTION_{$dialog_id}_EMAIL_OPTS":
         $options = hda_db::hdadb()->HDA_DB_GetUserOptions($UserCode);
         $opts = PRO_ReadAndClear('EMAIL_OPTS');
         if (!is_null($opts) && is_array($opts)) {
            $to_set = array();
            foreach ($opts as $k) $to_set[$k]=true;
            $options['EMAIL_OPTS'] = $to_set;
            }
         hda_db::hdadb()->HDA_DB_writeUserOptions($UserCode, $options);
         break;
      }
   PRO_Clear('EMAIL_OPTS');
   if (is_null($request)) return "";
   $t = _makedialoghead($dialog_id, "Select Email Notification Options");
   $t .= "<tr><th>Send me email notifications about:</th></tr>";
      $email_opts = array(
                  'ALCNotice'=>"General HDAW Activity",
                  'ALCLogNotice'=>"A new Log entry in HDAW",
                  'ALCReportNote'=>"A new Note added in HDAW",
                  'ALCTriggerNotice'=>"When a profile or process is Triggered", 
                  'ALCUpload'=>"When data is uploaded or provided for a profile",
                  'ALCProcess'=>"When a process completes",
                  'ALCProcessCode'=>"When a profile or process sends me an email",
                  'ALCDailyReport'=>"Send the daily report",
                  'HDAWarning'=>"Profile or process warning messages",
                  'ALCAlarm'=>"Profile or process alarm reminders");
   $options = hda_db::hdadb()->HDA_DB_GetUserOptions($UserCode);
   $my_email_opts = (array_key_exists('EMAIL_OPTS',$options))?$options['EMAIL_OPTS']:NULL;
   if (is_null($my_email_opts) || !is_array($my_email_opts)) {
      $my_email_opts = array();
      foreach ($email_opts as $k=>$p) $my_email_opts[$k]=true;
      }
   foreach ($email_opts as $k=>$caption) {
      $checked = (array_key_exists($k, $my_email_opts) && $my_email_opts[$k]===true)?"CHECKED":"";
      $t .= "<tr><td>{$caption}&nbsp;<input type=\"checkbox\" name=\"EMAIL_OPTS[]\" value=\"{$k}\" {$checked}></td></tr>";
      }
   $mouse = _click_dialog($dialog_id,"_EMAIL_OPTS");
   $t .= "<tr><th>";
   $t .= "<span class=\"push_button blue\" {$mouse}   >Submit</span>"; 
   $t .= "</th></tr>";
   $t .= _makedialogclose();

   return $t;
   }



?>