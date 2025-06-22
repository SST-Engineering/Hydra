<?php
global $_CRON_LOCKS;
$_CRON_LOCKS = array(
   'EM'=>'Email',
   'CK'=>'Check Activity',
   'AQ'=>'Activity Q',
   'AT'=>'Auto Relation Trigger',
   'RP'=>'Daily Report',
   'BU'=>'Backup',
   'TG'=>'Schedule Triggers',
   'TD'=>'Auto Tidy',
   'TK'=>'Ticket Collection',
   'XC'=>'External Collection',
   'PQ'=>'Primary Q',
   'Q1'=>'Q1','Q2'=>'Q2','Q3'=>'Q3','Q4'=>'Q4','Q5'=>'Q5','Q6'=>'Q6','Q7'=>'Q7','Q8'=>'Q8','Q9'=>'Q9',
   'Q10'=>'Proxy Q'
   );
   
function HDA_AdminBody() {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;
   global $binary_dir;
   global $_ViewHeight;
   global $Tab_Menu;

   $problem = null;

   switch ($Action) {
      case 'ACTION_ADMIN_SaveReport':
         $send_to = PRO_ReadParam('DailyReportCirculation');
         $run_at = PRO_ReadParam('DailyReportHour')*60+PRO_ReadParam('DailyReportMin');
         hda_db::hdadb()->HDA_DB_admin('DailyReport', hda_db::hdadb()->HDA_DB_serialize(array('SendTo'=>$send_to,'RunAt'=>$run_at)));
         break;
      case 'ACTION_AdminTabbed':
         list($action, $admin_tab) = explode('-',$ActionLine);
         PRO_AddToParams('OnAdminTab', $admin_tab);
         break;
      }

   $admin_tab = PRO_ReadParam('OnAdminTab');
   if (is_null($admin_tab)) $admin_tab = "DAILYREPORT";
   $t = "";
   $Tab_Menu = "";
   if (!is_null($problem)) $t .= "<tr><th style=\"color:red;\" colspan=3>{$problem}</th></tr>";
   
   $mouse = "onclick=\"issuePost('AdminTabbed-DAILYREPORT');\" return false;\" ";
   $Tab_Menu .= "&nbsp;<span title=\"Daily Report Settings..\" {$mouse} >";
   $Tab_Menu .= _emit_image("DailyReport.jpg", 24);
   $Tab_Menu .= "</span>&nbsp;";

   $mouse = "onclick=\"issuePost('AdminTabbed-SMS');\" return false;\" ";
   $Tab_Menu .= "&nbsp;<span title=\"SMS Contact Lists..\" {$mouse} >";
   $Tab_Menu .= _emit_image("SMS.jpg",24);
   $Tab_Menu .= "</span>&nbsp;";

   $mouse = "onclick=\"issuePost('AdminTabbed-BACKUP');\" return false;\" ";
   $Tab_Menu .= "&nbsp;<span title=\"Auto Backup Settings..\" {$mouse} >";
   $Tab_Menu .= _emit_image("ArchiveThis.jpg",24);
   $Tab_Menu .= "</span>&nbsp;";

   $mouse = "onclick=\"issuePost('AdminTabbed-EMAILCFG');\" return false;\" ";
   $Tab_Menu .= "&nbsp;<span title=\"Email Settings..\" {$mouse} >";
   $Tab_Menu .= _emit_image("RECEIVE_EMAIL.jpg",24);
   $Tab_Menu .= "</span>&nbsp;";
   
   $mouse = "onclick=\"issuePost('AdminTabbed-EMAILREDIRECT');\" return false;\" ";
   $Tab_Menu .= "&nbsp;<span title=\"Email Redirect..\" {$mouse} >";
   $Tab_Menu .= _emit_image("RedirectEmail.jpg",24);
   $Tab_Menu .= "</span>&nbsp;";

   $mouse = "onclick=\"issuePost('AdminTabbed-TICKETING');\" return false;\" ";
   $Tab_Menu .= "&nbsp;<span title=\"External Ticketing..\" {$mouse} >";
   $Tab_Menu .= _emit_image("Trust.jpg",24);
   $Tab_Menu .= "</span>&nbsp;";

   $mouse = "onclick=\"issuePost('AdminTabbed-COLLECTING');\" return false;\" ";
   $Tab_Menu .= "&nbsp;<span title=\"External Collecting..\" {$mouse} >";
   $Tab_Menu .= _emit_image("AutoCollect.jpg",24);
   $Tab_Menu .= "</span>&nbsp;";

   $mouse = "onclick=\"issuePost('AdminTabbed-CRON');\" return false;\" ";
   $Tab_Menu .= "&nbsp;<span title=\"Cron Log..\" {$mouse} >";
   $Tab_Menu .= _emit_image("Cron.jpg",24);
   $Tab_Menu .= "</span>&nbsp;";

   $mouse = "onclick=\"issuePost('AdminTabbed-CACHE');\" return false;\" ";
   $Tab_Menu .= "&nbsp;<span title=\"Cache Stats..\" {$mouse} >";
   $Tab_Menu .= _emit_image("WinCache.jpg",24);
   $Tab_Menu .= "</span>&nbsp;";

   $mouse = "onclick=\"issuePost('AdminTabbed-PHP');\" return false;\" ";
   $Tab_Menu .= "&nbsp;<span title=\"PHP Info..\" {$mouse} >";
   $Tab_Menu .= _emit_image("PHP.jpg",24);
   $Tab_Menu .= "</span>&nbsp;";


   switch ($admin_tab) {
      default:
     case 'DAILYREPORT':
         $daily_report = hda_db::hdadb()->HDA_DB_admin('DailyReport');
         if (!is_null($daily_report)) $daily_report = hda_db::hdadb()->HDA_DB_unserialize($daily_report);
         else $daily_report = array('SendTo'=>"ADMIN",'RunAt'=>0);
   
         $t .= "<tr><th rowspan=2>";
         $t .= _emit_image("DailyReport.jpg",32);
         $t .= "Daily Reports</th>";
         $t .= "<td>Run daily at:&nbsp;";
         $run_at_hour = intval($daily_report['RunAt']/60);
         $run_at_min = intval($daily_report['RunAt'] % 60);
         $t .= "<select name=\"DailyReportHour\" >";
         for ($i = 0; $i<24; $i++) {
            $selected = ($run_at_hour==$i)?"SELECTED":"";
            $t .= "<option value=\"{$i}\" {$selected} >".sprintf("%02d",$i)."</option>";
            }
         $t .= "</select>";
         $t .= ":";
         $t .= "<select name=\"DailyReportMin\" >";
         for ($i = 0; $i<60; $i+=15) {
            $selected = ($run_at_min==$i)?"SELECTED":"";
            $t .= "<option value=\"{$i}\" {$selected} >".sprintf("%02d",$i)."</option>";
            }
         $t .= "</select>";
         $t .= "</td>";
         $t .= "<td>";
         $checked = ($daily_report['SendTo']=="0")?"CHECKED":"";
         $t .= "<input type=\"radio\" name=\"DailyReportCirculation\" value=\"0\" {$checked} >Disable Daily Report<br>";
         $checked = ($daily_report['SendTo']=="ADMIN")?"CHECKED":"";
         $t .= "<input type=\"radio\" name=\"DailyReportCirculation\" value=\"ADMIN\" {$checked}  >Admin Users Only<br>";
         $checked = ($daily_report['SendTo']=="OWNERS")?"CHECKED":"";
         $t .= "<input type=\"radio\" name=\"DailyReportCirculation\" value=\"OWNERS\" {$checked}  >Owners (creator) of profiles only<br>";
         $checked = ($daily_report['SendTo']=="ALL")?"CHECKED":"";
         $t .= "<input type=\"radio\" name=\"DailyReportCirculation\" value=\"ALL\" {$checked}  >All Users<br>";
         $t .= "</td>";
         $t .= "</tr>";
         $t .= "<tr><th colspan=3>";
		 $mouse = "onclick=\"issuePost('ADMIN_SaveReport',event);eturn false;\" ";
		 $t .= "<span class=\"push_button blue\" {$mouse}   >Save Report Settings</span>"; 
		 $t .= "</th></tr>";
         break;
	  case 'SMS':
	     $t .= _sms_list_form();
	     break;
	  case 'BACKUP':
	     $t .= _mysql_backup_form();
	     break;
	  case 'EMAILCFG':
	     $t .= _email_admin_cfg_form();
	     break;
	  case 'EMAILREDIRECT':
	     $t .= _email_redirect_form();
		 break;
	  case 'TICKETING':
	     $t .= _external_ticketing_form();
		 break;
	  case 'COLLECTING':
	     $t .= _external_collecting_form();
		 break;
	  case 'CRON':
		 global $_CRON_LOCKS;
	     switch ($Action) {
		    case "ACTION_ADMIN_ASK_LOCKS":
			   $locks = PRO_ReadParam("ADMIN_ASK_LOCK");
			   if (!is_array($locks)) $locks = array();
			   hda_db::hdadb()->HDA_DB_admin("CRON_ENABLE", hda_db::hdadb()->HDA_DB_serialize($locks));
			   foreach ($_CRON_LOCKS as $lock=>$caption) hda_db::hdadb()->HDA_DB_DropLock($lock);
			   foreach ($locks as $lock) hda_db::hdadb()->HDA_DB_TakeLock($lock);
			   break;
			case "ACTION_ADMIN_FORCE_OFF_LOCK":
			   list($action, $lock, $holder) = explode('-',$ActionLine);
			   hda_db::hdadb()->HDA_DB_DropLock($lock, $holder);
			   break;
			case "ACTION_ADMIN_DELETE_OPEN_LOG":
			   list($action, $sessid) = explode('-',$ActionLine);
			   hda_db::hdadb()->HDA_DB_cronOut("Forced Off",$sessid);
			   break;
			}
		 PRO_Clear("ADMIN_ASK_LOCK");
	     $t .= "<tr><td colspan=3><table class=\"alc-table\">";
		 $t .= "<colgroup><col style=\"width:50%;\"><col style=\"width:50%;\"></colgroup>";
		 $t .= "<tr><td>";
		 $t .= "<div style=\"width:100%;height:".($_ViewHeight-96)."px;overflow:auto;\">";
		 $t .= "<div style=\"margin:8px;border:1px solid green;padding:2px;\">";
		 $a = hda_db::hdadb()->HDA_DB_cronLog();
		 if (is_array($a)) foreach ($a as $row) {
		    $t .= "<br><b>Enter ".hda_db::hdadb()->PRO_DBdate_Styledate($row['InDate'],true)."</b><br>";
			$t .= $row['LogText'];
		    $t .= "<br><b>Exit ".hda_db::hdadb()->PRO_DBdate_Styledate($row['OutDate'],true)."</b>";
			if (is_null($row['OutDate'])) {
			   $mouse = "onclick=\"issuePost('ADMIN_DELETE_OPEN_LOG-{$row['SessionId']}---',event); return false;\" ";
			   $t .= _emit_image("DeleteThis.jpg", 10, $mouse);
			   }
			$t .= "<br>";
            }
		 $t .= "</div>";
		 $t .= (@file_exists($cron_log = "ErrorLogs/cron.html"))?file_get_contents($cron_log):"No Log";
		 $t .= "</div></td>";
		 $a = hda_db::hdadb()->HDA_DB_admin("CRON_ENABLE");
		 if (is_null($a)) $a = array();
		 else $a = hda_db::hdadb()->HDA_DB_unserialize($a);
		 $t .= "<td><div style=\"width:100%;height:".($_ViewHeight-96)."px;overflow:auto;\"><table class=\"alc-table\">";
		 foreach ($_CRON_LOCKS as $lock=>$caption) {
		    $t .= "<tr>";
			$t .= "<td>{$caption}</td>";
			$holder = hda_db::hdadb()->HDA_DB_TestLock($lock);
            if (is_null($holder)) {
			   $t .= "<td>No Lock</td>"; 
			   $checked = (in_array($lock, $a))?"CHECKED":"";
			   $t .= "<td>Request this lock: <input type=\"checkbox\" name=\"ADMIN_ASK_LOCK[]\" value=\"{$lock}\" {$checked}></td>";
			   }
			else {
			   if ($holder==session_id()) $t .= "<td>This has lock</td>";
			   else $t .= "<td>Other has lock</td>";
			   $mouse = "onclick=\"issuePost('ADMIN_FORCE_OFF_LOCK-{$lock}-{$holder}-',event); return false;\" ";
			   $t .= "<td>";
			   $t .= "<span class=\"push_button blue\" {$mouse}   >Force Release</span>"; 
			   $t .= "</td>";
			   }
			$t .= "</tr>";
		    }
		 $mouse = "onclick=\"issuePost('ADMIN_ASK_LOCKS',event); return false;\" ";
		 $t .= "<tr><th colspan=3>";
		 $t .= "<span class=\"push_button blue\" {$mouse}   >Update Lock Requests</span>"; 
		 $t .= "</th></tr>";
		 $t .= "</table></div>";
		 $t .= "</td></tr>";
		 $t .= "</table></td></tr>";
	     break;
	  case 'CACHE':
	     $t .= "<tr><td>";
	     $t .= "<IFRAME src=\"wincache.php\" scrolling=\"yes\" style=\"height:".($_ViewHeight-96)."px; width:100%; border:none;\" ></IFRAME>"; 
         $t .= "</td></tr>";
         break;
	  case 'PHP':
	     $t .= "<tr><td>";
	     $t .= "<IFRAME src=\"phpinfo.php\" scrolling=\"yes\" style=\"height:".($_ViewHeight-96)."px; width:100%; border:none;\" ></IFRAME>"; 
         $t .= "</td></tr>";
         break;
		 }
   return $t;
   }

function _sms_list_form($dialog_id='alc_sms_form') {
   global $Action;
   global $ActionLine;
   global $code_root, $home_root;
   global $key_mouse;
   $sms = hda_db::hdadb()->HDA_DB_admin('SMS');
   if (!is_null($sms)) $sms = hda_db::hdadb()->HDA_DB_unserialize($sms);
   else $sms = array();
   $on_sms = PRO_ReadParam("{$dialog_id}_OnSMS");
   switch ($Action) {
      case "ACTION_{$dialog_id}_NewName":
	     $on_sms = PRO_ReadParam("alc_new_sms_Name");
		 $on_sms = preg_replace("/[\s]/",'_',$on_sms);
	     if (!array_key_exists($on_sms, $sms)) $sms[$on_sms] = "";
         hda_db::hdadb()->HDA_DB_admin('SMS', hda_db::hdadb()->HDA_DB_serialize($sms));
		 PRO_AddToParams("{$dialog_id}_OnSMS",$on_sms);
	     break;
	  case "ACTION_{$dialog_id}_SwitchSMS":
	     list($action, $on_sms) = explode('-',$ActionLine);
		 PRO_AddToParams("{$dialog_id}_OnSMS",$on_sms);
	     break;
	  case "ACTION_{$dialog_id}_DeleteSMS":
	     list($action, $on_sms) = explode('-',$ActionLine);
		 unset($sms[$on_sms]);
		 $on_sms = null; PRO_Clear("{$dialog_id}_OnSMS");
         hda_db::hdadb()->HDA_DB_admin('SMS', hda_db::hdadb()->HDA_DB_serialize($sms));
	     break;
	  case "ACTION_{$dialog_id}_Save":
	     list($action, $on_sms) = explode('-',$ActionLine);
		 $sms[$on_sms] = PRO_ReadParam("{$dialog_id}_{$on_sms}_PhoneList");
         hda_db::hdadb()->HDA_DB_admin('SMS', hda_db::hdadb()->HDA_DB_serialize($sms));
         break;		 
	  }
	     
   $sms = hda_db::hdadb()->HDA_DB_admin('SMS');
   if (!is_null($sms)) $sms = hda_db::hdadb()->HDA_DB_unserialize($sms);
   else $sms = array();
   $on_sms = PRO_ReadParam("{$dialog_id}_OnSMS");
   $t = "";
   $t .= "<tr><td colspan=3>";
      $mouse = _click_dialog("_dialogNewSMS");
      $t .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Create a new SMS collection..\" {$mouse}>";
      $t .= _emit_image("AddThis.jpg",18)."</span>";
      $mouse = _click_dialog("_dialogImportSMSlist");
      $t .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Import SMS list..\" {$mouse}>";
      $t .= _emit_image("ImportThis.jpg",24)."</span>";
      $mouse = _click_dialog("_dialogExportSMSlist");
      $t .= "&nbsp;&nbsp;<span  title=\"Export SMS list..\"  {$mouse}>";
      $t .= _emit_image("Export.jpg",24);
      $t .= "</span>";
	  $t .= "&nbsp;&nbsp;Named SMS Collection Configuration";
   $t .= "</td></tr>";
   if (count($sms)>0) {
      $t .= "<tr><th colspan=3><table class=\"alc-table\">";
	  $t .= "<colgroup span=2><col width=\"240px\"><col width=\"*\"></colgroup>";
      $t .= "<tr><th rowspan=3> <div style=\"width:100%;height:170px;overflow-x:hidden;overflow-y:scroll;\" ><table class=\"alc-table\" >";
      foreach ($sms as $sms_name=>$sms_collection) {
         $selected_style=($sms_name==$on_sms)?"color:blue;":"";
	     $mouse="onclick=\"issuePost('{$dialog_id}_SwitchSMS-{$sms_name}---',event);return false;\" ";
	     $t .= "<tr><td><span class=\"click-here\" title=\"Select SMS Collection\" style=\"{$selected_style}\" {$mouse}>{$sms_name}</span></td>";
	     $mouse="onclick=\"issuePost('{$dialog_id}_DeleteSMS-{$sms_name}---',event);return false;\" ";
	     $t .= "<th><span class=\"click-here\" title=\"Delete SMS collection\" style=\"width:16px;\" {$mouse}>";
		 $t .= _emit_image("DeleteThis.jpg",12)."</span></th></tr>";
         }
      $t .= "</table></div></th>";
	  $t .= "<th>Enter phone numbers, one per line, in this format +447797654321#Name of recipient for reminder, e.g. <br>+447797654321#Fred Blogs</th></tr>";
      $sms_collection = (!is_null($on_sms) && array_key_exists($on_sms,$sms))?$sms[$on_sms]:"";
      $t .= "<tr><th><textarea name=\"{$dialog_id}_{$on_sms}_PhoneList\" style=\"width:100%;height:160px;overflow-y:scroll;overflow-x:hidden;\" >{$sms_collection}</textarea></th></tr>";
      $mouse = "onclick=\"issuePost('{$dialog_id}_Save-{$on_sms}---',event);return false;\" ";
	  $t .= "<tr><th><span class=\"click-here\" {$mouse}>";
	  $t .= _emit_image("Save.jpg",18)."</span></th></tr>";

	  global $HDA_SMS_CFG;
	  $problem = null;
	  switch ($Action) {
         case 'ACTION_ADMIN_SYS_SMS_SAVE':
            $a = array();
            $a['SUSPEND_SMS'] = (PRO_ReadAndClear('SUSPEND_SMS')==1)?0:1;
            $a['SMS_USERNAME'] = PRO_ReadParam('SMS_USERNAME');
            $a['SMS_PASSWORD'] = PRO_ReadParam('SMS_PASSWORD');
            $a['SMS_ACCID'] = PRO_ReadParam('SMS_ACCID');
            $a['SMS_DAYLIMIT'] = PRO_ReadAndClear('SMS_DAYLIMIT');
            $a['SMS_PROFILELIMIT'] = PRO_ReadParam('SMS_PROFILELIMIT');
            if (!hda_db::hdadb()->HDA_DB_admin('SMS_CFG', hda_db::hdadb()->HDA_DB_serialize($a))) $problem = "Error saving settings";
            ReadSMSConfig();
            break;
         case 'ACTION_ADMIN_SYS_SMS_RESET':
            ResetSMSCfg();
            if (!hda_db::hdadb()->HDA_DB_admin('SMS_CFG', hda_db::hdadb()->HDA_DB_serialize($HDA_SMS_CFG))) $problem = "Error RESET settings";
            ReadSMSConfig();
            break;
	     }
	  if (!is_null($problem)) $t .= "<tr><th colspan=3 style=\"color:red;\" >{$problem}</th></tr>";
      $t .= "<tr><th rowspan=6>SMS Config</th>";
      $checked = ($HDA_SMS_CFG['SUSPEND_SMS']==0)?"CHECKED":"";
      $t .= "<td>Enable SMS&nbsp;<input type=\"checkbox\" name=\"SUSPEND_SMS\" value=\"1\" {$checked}></td>";
      $t .= "</tr>";
      $t .= "<tr><td>SMS Username&nbsp;<input type=\"text\" {$key_mouse}  class=\"alc-intext\" name=\"SMS_USERNAME\" value=\"{$HDA_SMS_CFG['SMS_USERNAME']}\"></td></tr>";
      $t .= "<tr><td>SMS Password&nbsp;<input type=\"text\" {$key_mouse}  class=\"alc-intext\" name=\"SMS_PASSWORD\" value=\"{$HDA_SMS_CFG['SMS_PASSWORD']}\"></td></tr>";
      $t .= "<tr><td>SMS Acc Id&nbsp;<input type=\"text\" {$key_mouse}  class=\"alc-intext\" name=\"SMS_ACCID\" value=\"{$HDA_SMS_CFG['SMS_ACCID']}\"></td></tr>";
      $t .= "<tr><td>SMS Daily Limit&nbsp;<input type=\"text\" {$key_mouse}  class=\"alc-intext\" name=\"SMS_DAYLIMIT\" value=\"{$HDA_SMS_CFG['SMS_DAYLIMIT']}\"></td></tr>";
      $t .= "<tr><td>SMS Per Profile Per Day Limit&nbsp;<input type=\"text\" {$key_mouse}  class=\"alc-intext\" name=\"SMS_PROFILELIMIT\" value=\"{$HDA_SMS_CFG['SMS_PROFILELIMIT']}\"></td></tr>";
      $mouse = "onclick=\"issuePost('ADMIN_SYS_SMS_RESET---',event); return false;\" ";
      $t .= "<tr><th colspan=3>";
      $t .= "<span class=\"push_button blue\" {$mouse}   >Reset from CONFIG SMS Settings</span>"; 
      $mouse = "onclick=\"issuePost('ADMIN_SYS_SMS_SAVE---',event); return false;\" ";
      $t .= "&nbsp;<span class=\"push_button blue\" {$mouse}   >Save SMS Settings</span>"; 
	  $t .= "</th></tr>";


      $t .= "</table></th></tr>";
	  }
   return $t;
   }
   
function _dialogNewSMS($dialog_id='alc_new_sms') {
   global $Action;
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
	     break;
      }
   $t = _makedialoghead($dialog_id, "New SMS Phone List Collection");
   $mouse = "onKeyPress=\"return keyPressPost('{$dialog_id}_NewName',event)\" ";
   $t .= "<tr><td>New Collection Name&nbsp;<input type=\"text\" name=\"alc_new_sms_Name\" {$mouse} ></td></tr>";
   $t .= _closeDialog($dialog_id, "_NewName", 2);
   $t .= _makedialogclose();

   return $t;
   }
   
   function _dialogImportSMSlist($dialog_id='alc_import_smslist') {
   global $Action;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;
   $problem = null;
   $tt = null;
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
         break;
      case "ACTION_{$dialog_id}_UploadList":
         $problem = HDA_UploadSMS($up_path='UploadSMS', $into_file);
         if (is_null($problem)) {
            $xml = file_get_contents($into_file);
            $a = xml2ary($xml);
            $sms_list = _query('SMS', $a, 'SMSLIST');
            if (is_null($sms_list) || !is_array($sms_list)) $problem = "Unexpected layout in xml for an SMS list ".print_r($a,true);
            else {
               $sms = hda_db::hdadb()->HDA_DB_admin('SMS');
               if (!is_null($sms)) $sms = hda_db::hdadb()->HDA_DB_unserialize($sms);
               else $sms = array();
			   $new_sms = array();
			   $tt = "";
               foreach ($sms_list as $user) {
			      if (array_key_exists('name',$user[1])) {
				     if (!array_key_exists($sms_uname = $user[1]['name'],$new_sms)) $new_sms[$sms_uname] = "";
					 $sms_u = trim($user[0]);
					 $new_sms[$sms_uname] .= "{$sms_u}\n";
					 $tt .= "{$sms_uname}: {$sms_u}\n";
					 }
				  }
			   foreach ($new_sms as $k=>$p) {
			      $sms[$k] = $p;
			      }
               hda_db::hdadb()->HDA_DB_admin('SMS', hda_db::hdadb()->HDA_DB_serialize($sms));
               }
            }
         break;
      }

   $t = _makedialoghead($dialog_id, "Import SMS List");
   if (!is_null($problem)) $t .= "<tr><th style=\"color:red;\" >{$problem}</th></tr>";
   if (is_null($tt)) {
      $t .= "<tr><th>Import list:</th><td colspan=2><input type=\"file\" name=\"UploadSMS\" \" value=\"\" ></td></tr>";
      $mouse = _click_dialog($dialog_id, "_UploadList");
      $t .= "<tr><th colspan=3>";
      $t .= "<span class=\"push_button blue\" {$mouse}   >Upload SMS List</span>"; 
	  $t .= "</th></tr>";
      }
   else {
      $t .= "<tr><td colspan=3><textarea class=\"alc-dialog-text\" style=\"height:300px;\" >{$tt}</textarea></td></tr>";
      }
   $t .= _makedialogclose();

   return $t;
   }

function _dialogExportSMSlist($dialog_id='alc_export_smslist') {
   global $Action;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;
   $problem = null;
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
         break;
      }
   $t = _makedialoghead($dialog_id, "Export SMS List");
   $sms = hda_db::hdadb()->HDA_DB_admin('SMS');
   if (!is_null($sms)) $sms = hda_db::hdadb()->HDA_DB_unserialize($sms);
   else $sms = array();
   $xml = "";
   $xml .= "<SMS>\n";
   $xml .= "   <SMSLIST>\n";
   foreach ($sms as $sms_name=>$sms_collection) {
	  $sms_ulist = explode("\n",$sms_collection);
	  foreach ($sms_ulist as $sms_u) {
         $xml .= "      <SMSPHONE name=\"{$sms_name}\">{$sms_u}</SMSPHONE>\n";
         }
      }
   $xml .= "   </SMSLIST>\n";
   $xml .= "</SMS>\n";
   $t .= "<tr><th colspan=3><textarea class=\"alc-dialog-text\" style=\"height:200px;\" wrap=off >{$xml}</textarea></th></tr>";
   $lib_dir = "tmp/";
   $lib_path = "{$lib_dir}/smslist.xml";
   @file_put_contents($lib_path, $xml);_chmod($lib_path);
   $t .= "<tr><th colspan=3><a href=\"{$lib_path}\" target=\"_blank\" >Download</a></th></tr>";

   $t .= _makedialogclose();

   return $t;
   }
   
function _email_redirect_form($dialog_id='alc_redirect_form') {
   global $Action;
   global $ActionLine;
   global $code_root, $home_root;
   global $key_mouse;
   $redirect = hda_db::hdadb()->HDA_DB_admin('REDIRECT');
   if (!is_null($redirect)) $redirect = hda_db::hdadb()->HDA_DB_unserialize($redirect);
   else $redirect = array();
   $on_redirect = PRO_ReadParam("{$dialog_id}_OnRedirect");
   switch ($Action) {
	  case "ACTION_{$dialog_id}_SwitchRedirect":
	     list($action, $enc) = explode('-',$ActionLine);
		 $on_redirect = base64_decode($enc);
		 PRO_AddToParams("{$dialog_id}_OnRedirect",$on_redirect);
	     break;
	  case "ACTION_{$dialog_id}_DeleteRedirect":
	     list($action, $enc) = explode('-',$ActionLine);
		 $on_redirect = base64_decode($enc);
		 unset($redirect[$on_redirect]);
		 $on_redirect = null; PRO_Clear("{$dialog_id}_OnRedirect");
         hda_db::hdadb()->HDA_DB_admin('REDIRECT', hda_db::hdadb()->HDA_DB_serialize($redirect));
	     break;
	  case "ACTION_{$dialog_id}_Save":
	     list($action, $enc) = explode('-',$ActionLine);
		 $on_redirect = base64_decode($enc);
		 $redirect[$on_redirect] = PRO_ReadParam("{$dialog_id}_EmailList");
         hda_db::hdadb()->HDA_DB_admin('REDIRECT', hda_db::hdadb()->HDA_DB_serialize($redirect));
         break;		 
	  }
   $redirect = hda_db::hdadb()->HDA_DB_admin('REDIRECT');
   if (!is_null($redirect)) $redirect = hda_db::hdadb()->HDA_DB_unserialize($redirect);
   else $redirect = array();
   $on_redirect = PRO_ReadParam("{$dialog_id}_OnRedirect");
   $t = "";
   $t .= "<tr><td colspan=3>";
      $mouse = _click_dialog("_dialogNewRedirectEmail");
      $t .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Create a new Email Redirection..\" {$mouse}>";
      $t .= _emit_image("AddThis.jpg",18)."</span>";
      $mouse = _click_dialog("_dialogImportRedirectEmailList");
      $t .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Import Redirect list..\" {$mouse}>";
      $t .= _emit_image("ImportThis.jpg",24)."</span>";
      $mouse = _click_dialog("_dialogExportRedirectEmailList");
      $t .= "&nbsp;&nbsp;<span  title=\"Export Redirect list..\"  {$mouse}>";
      $t .= _emit_image("Export.jpg",24);
      $t .= "</span>";
	  $t .= "&nbsp;&nbsp;Named Email Redirections";
   $t .= "</td></tr>";
   if (count($redirect)>0) {
      $t .= "<tr><th colspan=3><table class=\"alc-table\">";
	  $t .= "<colgroup span=2><col width=\"240px\"><col width=\"*\"></colgroup>";
      $t .= "<tr><th rowspan=3> <div style=\"width:100%;height:170px;overflow-x:hidden;overflow-y:scroll;\" ><table class=\"alc-table\" >";
      foreach ($redirect as $redirect_name=>$redirect_collection) {
         $selected_style=(strcmp($redirect_name,$on_redirect)==0)?"color:blue;":"";
		 $enc = base64_encode($redirect_name);
	     $mouse="onclick=\"issuePost('{$dialog_id}_SwitchRedirect-{$enc}---',event);return false;\" ";
	     $t .= "<tr><td><span class=\"click-here\" title=\"Select Redirect Collection\" style=\"{$selected_style}\" {$mouse}>{$redirect_name}</span></td>";
	     $mouse="onclick=\"issuePost('{$dialog_id}_DeleteRedirect-{$enc}---',event);return false;\" ";
	     $t .= "<th><span class=\"click-here\" title=\"Delete Redirect collection\" style=\"width:16px;\" {$mouse}>";
		 $t .= _emit_image("DeleteThis.jpg",12)."</span></th></tr>";
         }
      $t .= "</table></div></th>";
	  $t .= "<th>Enter Emails, one per line, in this format mailto@host.com#Name of recipient, e.g. <br>toYou@gmail.com#Joe Smith</th></tr>";
      $redirect_collection = (!is_null($on_redirect) && array_key_exists($on_redirect,$redirect))?$redirect[$on_redirect]:"";
      $t .= "<tr><th><textarea name=\"{$dialog_id}_EmailList\" style=\"width:100%;height:160px;overflow-y:scroll;overflow-x:hidden;\" >{$redirect_collection}</textarea></th></tr>";
      $enc = base64_encode($on_redirect);
      $mouse = "onclick=\"issuePost('{$dialog_id}_Save-{$enc}---',event);return false;\" ";
	  $t .= "<tr><th><span class=\"click-here\" {$mouse}>";
	  $t .= _emit_image("Save.jpg",18)."</span></th></tr>";

      $t .= "</table></th></tr>";
	  }
   return $t;
   }
   
function _dialogNewRedirectEmail($dialog_id='alc_new_redirect') {
   global $Action;
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}": break;
      case "ACTION_{$dialog_id}_NewName":
		 $redirect = hda_db::hdadb()->HDA_DB_admin('REDIRECT');
		 if (!is_null($redirect)) $redirect = hda_db::hdadb()->HDA_DB_unserialize($redirect);
		 else $redirect = array();
	     $on_redirect = PRO_ReadParam("alc_new_redirect_Name");
		 $on_redirect = preg_replace("/[\s]/",'_',$on_redirect);
	     if (!array_key_exists($on_redirect, $redirect)) $redirect[$on_redirect] = "";
         hda_db::hdadb()->HDA_DB_admin('REDIRECT', hda_db::hdadb()->HDA_DB_serialize($redirect));
		 PRO_AddToParams("alc_redirect_form_OnRedirect",$on_redirect);
		 return "";
      }
   $t = _makedialoghead($dialog_id, "New Email Redirect Collection");
   $mouse = "onKeyPress=\"return keyPressPost('{$dialog_id}_NewName',event)\" ";
   $t .= "<tr><td>New Collection Name&nbsp;<input type=\"text\" name=\"alc_new_redirect_Name\" {$mouse} ></td></tr>";
   $t .= _closeDialog($dialog_id, "_NewName", 2);
   $t .= _makedialogclose();

   return $t;
   }
   
function _dialogImportRedirectEmailList($dialog_id='alc_import_redirectlist') {
   global $Action;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;
   $problem = null;
   $tt = null;
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
         break;
      case "ACTION_{$dialog_id}_UploadList":
         $problem = HDA_UploadRedirect($up_path='UploadRedirect', $into_file);
         if (is_null($problem)) {
            $xml = file_get_contents($into_file);
            $a = xml2ary($xml);
            $redirect_list = _query('REDIRECT', $a, 'REDIRECTLIST');
            if (is_null($redirect_list) || !is_array($redirect_list)) $problem = "Unexpected layout in xml for a Redirect Email list ".print_r($a,true);
            else {
               $redirect = hda_db::hdadb()->HDA_DB_admin('REDIRECT');
               if (!is_null($redirect)) $redirect = hda_db::hdadb()->HDA_DB_unserialize($redirect);
               else $redirect = array();
			   $new_redirect = array();
			   $tt = "";
               foreach ($redirect_list as $user) {
			      if (array_key_exists('name',$user[1])) {
				     if (!array_key_exists($redirect_uname = $user[1]['name'],$new_redirect)) $new_redirect[$redirect_uname] = "";
					 $redirect_u = trim($user[0]);
					 $new_redirect[$redirect_uname] .= "{$redirect_u}\n";
					 $tt .= "{$redirect_uname}: {$redirect_u}\n";
					 }
				  }
			   foreach ($new_redirect as $k=>$p) {
			      $redirect[$k] = $p;
			      }
               hda_db::hdadb()->HDA_DB_admin('REDIRECT', hda_db::hdadb()->HDA_DB_serialize($redirect));
               }
            }
         break;
      }

   $t = _makedialoghead($dialog_id, "Import EMAIL Redirect List");
   if (!is_null($problem)) $t .= "<tr><th style=\"color:red;\" >{$problem}</th></tr>";
   if (is_null($tt)) {
      $t .= "<tr><th>Import list:</th><td colspan=2><input type=\"file\" name=\"UploadRedirect\" \" value=\"\" ></td></tr>";
      $mouse = _click_dialog($dialog_id, "_UploadList");
      $t .= "<tr><th colspan=3>";
      $t .= "<span class=\"push_button blue\" {$mouse}   >Upload Email Redirect List</span>"; 
	  $t .= "</th></tr>";
      }
   else {
      $t .= "<tr><td colspan=3><textarea class=\"alc-dialog-text\" style=\"height:300px;\" >{$tt}</textarea></td></tr>";
      }
   $t .= _makedialogclose();

   return $t;
   }

function _dialogExportRedirectEmailList($dialog_id='alc_export_redirectlist') {
   global $Action;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;
   $problem = null;
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
         break;
      }
   $t = _makedialoghead($dialog_id, "Export EMAIL Redirect List");
   $redirect = hda_db::hdadb()->HDA_DB_admin('REDIRECT');
   if (!is_null($redirect)) $redirect = hda_db::hdadb()->HDA_DB_unserialize($redirect);
   else $redirect = array();
   $xml = "";
   $xml .= "<REDIRECT>\n";
   $xml .= "   <REDIRECTLIST>\n";
   foreach ($redirect as $redirect_name=>$redirect_collection) {
	  $redirect_ulist = explode("\n",$redirect_collection);
	  foreach ($redirect_ulist as $redirect_u) {
         $xml .= "      <EMAIL name=\"{$redirect_name}\">{$redirect_u}</EMAIL>\n";
         }
      }
   $xml .= "   </REDIRECTLIST>\n";
   $xml .= "</REDIRECT>\n";
   $t .= "<tr><th colspan=3><textarea class=\"alc-dialog-text\" style=\"height:200px;\" wrap=off >{$xml}</textarea></th></tr>";
   $lib_dir = "tmp/";
   $lib_path = "{$lib_dir}/redirectlist.xml";
   @file_put_contents($lib_path, $xml);_chmod($lib_path);
   $t .= "<tr><th colspan=3><a href=\"{$lib_path}\" target=\"_blank\" >Download</a></th></tr>";

   $t .= _makedialogclose();

   return $t;
   }
   



function _mysql_backup_form() {
   global $Action;
   global $ActionLine;
   global $code_root, $home_root;
   global $key_mouse;
   $problem = null;
   $t = "";
   include_once("../{$code_root}/HDA_Backup.php");
   $backup = hda_db::hdadb()->HDA_DB_admin('Backup');
   if (!is_null($backup)) $backup = hda_db::hdadb()->HDA_DB_unserialize($backup);
   else $backup = array();
   switch ($Action) {
      case 'ACTION_ADMIN_Backup_Now':
         $problem = HDA_full_backup();
         break;
      case 'ACTION_ADMIN_Backup_Save':
         $backup['Backup_time'] = PRO_ReadParam('backup_timeHr')*60 + PRO_ReadParam('backup_timeMn');
         $backup['Backup_period'] = PRO_ReadParam('backup_period');
         $backup['Backup_periodDOM'] = PRO_ReadParam('backup_periodDOM');
         $backup['Backup_periodDOW'] = PRO_ReadParam('backup_periodDOW');
         $backup['Backup_dir'] = PRO_ReadParam('backup_dir');
         $backup['Backup_purge'] = PRO_ReadParam('backup_purge');
         $backup['zip'] = PRO_ReadParam('backup_zip');
         hda_db::hdadb()->HDA_DB_updateJobTimes('Backup', array('Period'=>$backup['Backup_period'],'PeriodUnits'=>1,'LastTime'=>hda_db::hdadb()->PRO_DB_DateTime(0)));
         hda_db::hdadb()->HDA_DB_admin('Backup', hda_db::hdadb()->HDA_DB_serialize($backup));
         break;
      }
   if (!is_null($problem)) $t .= "<tr><th style=\"color:red;\" >{$problem}</th></tr>";
   $t .= "<tr><td>";
   $last_time = hda_db::hdadb()->HDA_DB_jobTime('Backup');
   if (!is_null($last_time)) $t .= "Last scheduled back up run on ".hda_db::hdadb()->PRO_DBdate_Styledate($last_time['LastTime'],true)."<br>";
   if (array_key_exists('Last_Backup_Time',$backup)) $t .= "Last actual backup run on ".hda_db::hdadb()->PRO_DBtime_Styledate($backup['Last_Backup_Time'],true)."<br>";
   if (array_key_exists('Last_Backup_File', $backup)) $t .= "Last backup to file {$backup['Last_Backup_File']}<br>";
   if (array_key_exists('Last_Backup_Error', $backup)) $t .= "Last backup status {$backup['Last_Backup_Error']}";
   $t .= "</td></tr>";

   $t .= "<tr><th class=\"head\">Backup Schedule</th></tr>";
   $t .= "<tr><td>";
   $backup_time = (array_key_exists('Backup_time',$backup))?$backup['Backup_time']:0;
   $backup_period = (array_key_exists('Backup_period',$backup))?$backup['Backup_period']:'NONE';
   $backup_periodDOM = (array_key_exists('Backup_periodDOM',$backup))?$backup['Backup_periodDOM']:1;
   $backup_periodDOW = (array_key_exists('Backup_periodDOW',$backup))?$backup['Backup_periodDOW']:1;
   $checked = ($backup_period=='NONE')?"CHECKED":NULL;
   $t .= "<input type=\"radio\" name=\"backup_period\" value=\"NONE\" {$checked}>&nbsp;Disable Backup<br>";
   $checked = ($backup_period=='MONTH')?"CHECKED":NULL;
   $t .= "<span style=\"white-space:nowrap;\">";
   $t .= "Run Monthly&nbsp;<input type=\"radio\" name=\"backup_period\" value=\"MONTH\" {$checked}>&nbsp;on&nbsp;day&nbsp;of&nbsp;month&nbsp;";
   $t .= "<select name=\"backup_periodDOM\" >";
   for ($i = 1; $i<32; $i++) {
      $selected = (!is_null($checked) && $backup_periodDOM==$i)?"SELECTED":"";
      $t .= "<option value=\"{$i}\" {$selected}>{$i}</option>";
      }
   $t .= "</select></span>";
   $checked = ($backup_period=='WEEK')?"CHECKED":NULL;
   $t .= "<span style=\"white-space:nowrap;\">";
   $t .= "&nbsp;<span style=\"color:blue;\"><u><b>or</b></u></span>&nbsp;Run Weekly&nbsp;<input type=\"radio\" name=\"backup_period\" value=\"WEEK\" {$checked}>&nbsp;on&nbsp;day of week&nbsp;";
   $t .= "<select name=\"backup_periodDOW\" >";
   foreach (array(1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday') as $dow=>$down) {
      $selected = (!is_null($checked) && $alarm_time==$dow)?"SELECTED":"";
      $t .= "<option value=\"{$dow}\" {$selected}>{$down}</option>";
      }
   $t .= "</select></span>";
   $checked = ($backup_period=='DAY')?"CHECKED":NULL;
   $t .= "<span style=\"white-space:nowrap;\">";
   $t .= "&nbsp;<span style=\"color:blue;\"><u><b>or</b></u></span>&nbsp;Run Daily&nbsp;<input type=\"radio\" name=\"backup_period\" value=\"DAY\" {$checked}>";
   $t .= "</span><br><span style=\"white-space:nowrap;\">";
   $t .= "Run&nbsp;at&nbsp;time of day&nbsp;";
   $t .= "<select name=\"backup_timeHr\" >";
   for ($h = 0; $h<24; $h++) {
      $selected = (!is_null($checked) && intval($backup_time/60)==$h)?"SELECTED":"";
      $t .= "<option value=\"{$h}\" {$selected}>".sprintf("%02d",$h)."</option>";
      }
   $t .= "</select>:";
   $t .= "<select name=\"backup_timeMn\" >";
   for ($m = 0; $m<60; $m+=15) {
      $selected = (!is_null($checked) && intval($backup_time % 60)==$m)?"SELECTED":"";
      $t .= "<option value=\"{$m}\" {$selected}>".sprintf("%02d",$m)."</option>";
      }
   $t .= "</select></span>";
   $t .= "</td></tr>";
   $t .= "<tr><th class=\"head\">Backup Directory and Compression</th></tr>";
   $backup['Backup_dir'] = (array_key_exists('Backup_dir',$backup))?$backup['Backup_dir']:INIT('DB_BACKUP_DIR');
   if (is_null($backup['Backup_dir']) || strlen($backup['Backup_dir'])==0) $backup['Backup_dir'] = "backup";
   $t .= "<tr><td>Backup directory:<input type=\"text\" {$key_mouse}  class=\"alc-intext\" name=\"backup_dir\" value=\"{$backup['Backup_dir']}\">";
   $t .= "&nbsp;&nbsp;Purge backup directory after:&nbsp;";
   $purge_backup = (array_key_exists('Backup_purge',$backup))?$backup['Backup_purge']:0;
   $t .= "<select name=\"backup_purge\" >";
   foreach (array(0=>"Never",1=>"1 day",3=>"3 days",7=>"7 days",14=>"2 Weeks",31=>"1 Month",62=>"2 Months") as $d=>$caption) {
      $selected = ($purge_backup==$d)?"SELECTED":"";
      $t .= "<option value=\"{$d}\" {$selected}>{$caption}</option>";
      }
   $t .= "</select>";
   $t .= "</td></tr>";
   $t .= "<tr><td>Compress SQL backup queries as:&nbsp;";
   $checked = (array_key_exists('zip',$backup) && $backup['zip']=='zip')?"CHECKED":"";
   $t .= "<input type=\"radio\" name=\"backup_zip\" value=\"zip\" {$checked} >&nbsp;Save as zip compressed";
   $checked = (array_key_exists('zip',$backup) && $backup['zip']=='gzip')?"CHECKED":"";
   $t .= "<input type=\"radio\" name=\"backup_zip\" value=\"gzip\" {$checked} >&nbsp;Save as gzip compressed";
   $checked = (!array_key_exists('zip',$backup) || $backup['zip']=='plain')?"CHECKED":"";
   $t .= "<input type=\"radio\" name=\"backup_zip\" value=\"plain\" {$checked} >&nbsp;Save as plain text, not compressed";
   $t .= "</td></tr>";
   $mouse = "onclick=\"issuePost('ADMIN_Backup_Save',event); return false;\" ";
   $t .= "<tr><th>";
   $t .= "<span class=\"push_button blue\" {$mouse}   >Save Backup Settings</span>"; 
   $t .= "</th></tr>";
   $t .= "<tr><td>";
   $mouse = "onclick=\"issuePost('ADMIN_Backup_Now',event); return false;\" ";
   $t .= "<span title=\"Backup NOW\" {$mouse} class=\"click-here\" >";
   $t .= "Backup NOW?:";
   $t .= "&nbsp;&nbsp;"._emit_image("ArchiveThis.jpg",32);
   $t .= "</span>";
   $t .= "</td></tr>";
   return $t;
   }



function _email_admin_cfg_form() {
   global $Action;
   global $ActionLine;
   global $HDA_EMAIL_CFG;
   global $code_root, $home_root;
   global $key_mouse;
   $problem = null;
   switch ($Action) {
      case 'ACTION_ADMIN_SYS_EMAIL_TEST':
         list($action, $test_what) = explode('-',$ActionLine);
         switch ($test_what) {
            case 'IMAP':
               $c = imap_open("{".PRO_ReadParam('GET_MAIL_IMAP')."}", PRO_ReadParam('GET_MAIL_ACCOUNT'), PRO_ReadParam('GET_MAIL_PASSWORD'));
               if ($c === false) {
                  $problem = "Get Mail fails open ".PRO_ReadParam('GET_MAIL_IMAP')." error details: ".print_r(imap_errors(),true);
                  }
               else {
                  $problem = "Connect OK";
                  imap_close($c);
                  }
               break;
            case 'SMTP':
               $HDA_EMAIL_CFG['GMAIL_HOST'] = PRO_ReadParam('GMAIL_HOST');
               $HDA_EMAIL_CFG['GMAIL_PORT'] = PRO_ReadParam('GMAIL_PORT');
               $HDA_EMAIL_CFG['GMAIL_USERNAME'] = PRO_ReadParam('GMAIL_USERNAME');
               $HDA_EMAIL_CFG['GMAIL_PASSWORD'] = PRO_ReadParam('GMAIL_PASSWORD');
               $HDA_EMAIL_CFG['GMAIL_FROM'] = PRO_ReadParam('GMAIL_FROM');
               $HDA_EMAIL_CFG['GMAIL_FROM_NAME'] = PRO_ReadParam('GMAIL_FROM_NAME');
               $HDA_EMAIL_CFG['GMAIL_AUTH'] = PRO_ReadParam('GMAIL_AUTH');
               $HDA_EMAIL_CFG['GMAIL_AUTH_TYPE'] = PRO_ReadParam('GMAIL_AUTH_TYPE');
               $HDA_EMAIL_CFG['GMAIL_SECURE'] = PRO_ReadParam('GMAIL_SECURE');
               $HDA_EMAIL_CFG['GMAIL_REALM'] = PRO_ReadParam('GMAIL_REALM');
               $HDA_EMAIL_CFG['GMAIL_WORKSTATION'] = PRO_ReadParam('GMAIL_WORKSTATION');
               $HDA_EMAIL_CFG['EMAIL_ERROR_ACCOUNT'] = PRO_ReadParam('EMAIL_ERROR_ACCOUNT');
               if (!HDA_Gmail($HDA_EMAIL_CFG['EMAIL_ERROR_ACCOUNT'], "Test Email", "Requested a test email", NULL, $err)) {
                  $problem = "Failed to send test email {$err}";
                  }
               else $problem = "A test email has been sent to {$HDA_EMAIL_CFG['EMAIL_ERROR_ACCOUNT']}";
               break;
            }
         break;
      case 'ACTION_ADMIN_SYS_EMAIL_SAVE':
         $a = array();
         $a['SUSPEND_GET_MAIL'] = PRO_ReadAndClear('SUSPEND_GET_MAIL');
         $a['GET_MAIL_IMAP'] = PRO_ReadParam('GET_MAIL_IMAP');
         $a['GET_MAIL_ACCOUNT'] = PRO_ReadParam('GET_MAIL_ACCOUNT');
         $a['GET_MAIL_PASSWORD'] = PRO_ReadParam('GET_MAIL_PASSWORD');
		 $a['GET_MAIL_ANYONE'] = PRO_ReadParam('GET_MAIL_ANYONE');
		 $a['GET_MAIL_HTML'] = PRO_ReadParam('GET_MAIL_HTML');
         $a['EMAIL_ENABLED'] = PRO_ReadAndClear('EMAIL_ENABLED');
         $a['GMAIL_HOST'] = PRO_ReadParam('GMAIL_HOST');
         $a['GMAIL_PORT'] = PRO_ReadParam('GMAIL_PORT');
         $a['GMAIL_USERNAME'] = PRO_ReadParam('GMAIL_USERNAME');
         $a['GMAIL_PASSWORD'] = PRO_ReadParam('GMAIL_PASSWORD');
         $a['GMAIL_FROM'] = PRO_ReadParam('GMAIL_FROM');
         $a['GMAIL_FROM_NAME'] = PRO_ReadParam('GMAIL_FROM_NAME');
		 $a['GMAIL_AUTH'] = PRO_ReadParam('GMAIL_AUTH');
		 $a['GMAIL_AUTH_TYPE'] = PRO_ReadParam('GMAIL_AUTH_TYPE');
		 $a['GMAIL_SECURE'] = PRO_ReadParam('GMAIL_SECURE');
		 $a['GMAIL_REALM'] = PRO_ReadParam('GMAIL_REALM');
		 $a['GMAIL_WORKSTATION'] = PRO_ReadParam('GMAIL_WORKSTATION');
         $a['EMAIL_ERROR_ACCOUNT'] = PRO_ReadParam('EMAIL_ERROR_ACCOUNT');
         if (!hda_db::hdadb()->HDA_DB_admin('EMAIL_CFG', hda_db::hdadb()->HDA_DB_serialize($a))) $problem = "Error saving settings";
         ReadEmailConfig();
         break;
      case 'ACTION_ADMIN_SYS_EMAIL_RESET':
         ResetEmailCfg();
         if (!hda_db::hdadb()->HDA_DB_admin('EMAIL_CFG', hda_db::hdadb()->HDA_DB_serialize($HDA_EMAIL_CFG))) $problem = "Error RESET settings";
         ReadEmailConfig();
         break;
      }
   $t = "";
   $t .= "<tr><td><table class=\"alc-table\">";
   if (!is_null($problem)) $t .= "<tr><th colspan=2 style=\"color:red;\">{$problem}</th></tr>";
   $t .= "<tr><th rowspan=7>Receive Mail INBOX</th>";
   $checked = ($HDA_EMAIL_CFG['SUSPEND_GET_MAIL']==1)?"CHECKED":"";
   $t .= "<td>Enable Read Mail&nbsp;<input type=\"checkbox\" name=\"SUSPEND_GET_MAIL\" value=\"1\" {$checked}></td>";
   $t .= "</tr>";
   $t .= "<tr><td>IMAP INBOX&nbsp;<input type=\"text\" {$key_mouse}  class=\"alc-intext\" name=\"GET_MAIL_IMAP\" value=\"{$HDA_EMAIL_CFG['GET_MAIL_IMAP']}\"></td></tr>";
   $t .= "<tr><td>IMAP INBOX ACCOUNT&nbsp;<input type=\"text\" {$key_mouse}  class=\"alc-intext\" name=\"GET_MAIL_ACCOUNT\" value=\"{$HDA_EMAIL_CFG['GET_MAIL_ACCOUNT']}\"></td></tr>";
   $t .= "<tr><td>IMAP INBOX ACCOUNT PASSWORD&nbsp;<input type=\"password\" {$key_mouse}  class=\"alc-intext\" name=\"GET_MAIL_PASSWORD\" value=\"{$HDA_EMAIL_CFG['GET_MAIL_PASSWORD']}\"></td></tr>";
   $anyone = (array_key_exists('GET_MAIL_ANYONE',$HDA_EMAIL_CFG))?$HDA_EMAIL_CFG['GET_MAIL_ANYONE']:0;
   $checked = ($anyone==1)?"":"CHECKED";
   $t .= "<tr><td>IMAP INBOX ACCEPT&nbsp;From Known Users Only:&nbsp;<input type=\"radio\" name=\"GET_MAIL_ANYONE\" value=\"0\" {$checked}>";
   $checked = ($anyone==1)?"CHECKED":"";
   $t .= "&nbsp;or from ANYONE:&nbsp;<input type=\"radio\" name=\"GET_MAIL_ANYONE\" value=\"1\" {$checked}></td></tr>";
   $use_html = (array_key_exists('GET_MAIL_HTML',$HDA_EMAIL_CFG))?$HDA_EMAIL_CFG['GET_MAIL_HTML']:0;
   $checked = ($use_html==0)?"CHECKED":"";
   $t .= "<tr><td>IMAP ACCEPT USE HTML&nbsp;always Plain:<input type=\"radio\" name=\"GET_MAIL_HTML\" value=\"0\" {$checked}>";
   $checked = ($use_html==1)?"CHECKED":"";
   $t .= "&nbsp;or look for HTML first:&nbsp;<input type=\"radio\" name=\"GET_MAIL_HTML\" value=\"1\" {$checked}>";
   $checked = ($use_html==2)?"CHECKED":"";
   $t .= "&nbsp;or IGNORE message body:&nbsp;<input type=\"radio\" name=\"GET_MAIL_HTML\" value=\"2\" {$checked}>";
   $t .= "</td></tr>";
   $mouse = "onclick=\"issuePost('ADMIN_SYS_EMAIL_TEST-IMAP---',event); return false;\" ";
   $t .= "<tr><th>";
   $t .= "<span class=\"push_button blue\" {$mouse}   >Test Settings</span>"; 
   $t .= "</th></tr>";
   $t .= "<tr><th rowspan=13>Sending Mail OUTBOX</th>";
   $checked = ($HDA_EMAIL_CFG['EMAIL_ENABLED']==1)?"CHECKED":"";
   $t .= "<td>Enable Sending Email&nbsp;<input type=\"checkbox\" name=\"EMAIL_ENABLED\" value=\"1\" {$checked}></td>";
   $t .= "</tr>";
   $t .= "<tr><td>SMTP HOST&nbsp;<input type=\"text\" {$key_mouse}  class=\"alc-intext\" name=\"GMAIL_HOST\" value=\"{$HDA_EMAIL_CFG['GMAIL_HOST']}\"></td></tr>";
   $t .= "<tr><td>SMTP PORT&nbsp;<input type=\"text\" {$key_mouse}  class=\"alc-intext\" name=\"GMAIL_PORT\" value=\"{$HDA_EMAIL_CFG['GMAIL_PORT']}\"></td></tr>";
   $t .= "<tr><td>SMTP ACCOUNT USERNAME&nbsp;<input type=\"text\" {$key_mouse}  class=\"alc-intext\" name=\"GMAIL_USERNAME\" value=\"{$HDA_EMAIL_CFG['GMAIL_USERNAME']}\"></td></tr>";
   $t .= "<tr><td>SMTP ACCOUNT PASSWORD&nbsp;<input type=\"password\" {$key_mouse}  class=\"alc-intext\" name=\"GMAIL_PASSWORD\" value=\"{$HDA_EMAIL_CFG['GMAIL_PASSWORD']}\"></td></tr>";
   $t .= "<tr><td>SMTP FROM EMAIL&nbsp;<input type=\"text\" {$key_mouse}  class=\"alc-intext\" name=\"GMAIL_FROM\" value=\"{$HDA_EMAIL_CFG['GMAIL_FROM']}\"></td></tr>";
   $t .= "<tr><td>SMTP FROM NAME&nbsp;<input type=\"text\" {$key_mouse}  class=\"alc-intext\" name=\"GMAIL_FROM_NAME\" value=\"{$HDA_EMAIL_CFG['GMAIL_FROM_NAME']}\"></td></tr>";
   $t .= "<tr><td>SMTP AUTH ON&nbsp;<input type=\"text\" {$key_mouse}  class=\"alc-intext\" name=\"GMAIL_AUTH\" value=\"{$HDA_EMAIL_CFG['GMAIL_AUTH']}\"></td></tr>";
   $t .= "<tr><td>SMTP AUTH TYPE&nbsp;<input type=\"text\" {$key_mouse}  class=\"alc-intext\" name=\"GMAIL_AUTH_TYPE\" value=\"{$HDA_EMAIL_CFG['GMAIL_AUTH_TYPE']}\"></td></tr>";
   $t .= "<tr><td>SMTP SECURE&nbsp;<input type=\"text\" {$key_mouse}  class=\"alc-intext\" name=\"GMAIL_SECURE\" value=\"{$HDA_EMAIL_CFG['GMAIL_SECURE']}\"></td></tr>";
   $t .= "<tr><td>SMTP REALM&nbsp;<input type=\"text\" {$key_mouse}  class=\"alc-intext\" name=\"GMAIL_REALM\" value=\"{$HDA_EMAIL_CFG['GMAIL_REALM']}\"></td></tr>";
   $t .= "<tr><td>SMTP WORKSTATION&nbsp;<input type=\"text\" {$key_mouse}  class=\"alc-intext\" name=\"GMAIL_WORKSTATION\" value=\"{$HDA_EMAIL_CFG['GMAIL_WORKSTATION']}\"></td></tr>";
   $mouse = "onclick=\"issuePost('ADMIN_SYS_EMAIL_TEST-SMTP---',event); return false;\" ";
   $t .= "<tr><th>";
   $t .= "<span class=\"push_button blue\" {$mouse}   >Test Settings</span>"; 
   $t .= "</th></tr>";
   $t .= "<tr><th rowspan=1>Send System Crash Reports</th>";
   $t .= "<td>Email Crash Report Account&nbsp;<input type=\"text\" {$key_mouse}  class=\"alc-intext\" name=\"EMAIL_ERROR_ACCOUNT\" value=\"{$HDA_EMAIL_CFG['EMAIL_ERROR_ACCOUNT']}\"></td>";
   $t .= "</tr>";
   $mouse = "onclick=\"issuePost('ADMIN_SYS_EMAIL_RESET---',event); return false;\" ";
   $t .= "<tr><th colspan=2>";
   $t .= "<span class=\"push_button blue\" {$mouse}   >Reset from Config Email Settings</span>"; 
   $mouse = "onclick=\"issuePost('ADMIN_SYS_EMAIL_SAVE---',event); return false;\" ";
   $t .= "&nbsp;<span class=\"push_button blue\" {$mouse}   >Save Email Settings</span>"; 
   $t .= "</th></tr>";
   $t .= "</table></td></tr>";
   return $t;
   }

$External_Ticket_Version = 1;
function _external_ticketing_form() {
   global $Action;
   global $External_Ticket_Version;
   $xfields =  array('VN'=>$External_Ticket_Version, 'FTP'=>1, 'URL'=>'','UNAME'=>'','PW'=>'',
					'BASEDIR'=>'','DATEDIR'=>0,'COLLECT_POINT'=>'',
					'PASSTHROUGH'=>'','TKTARCH'=>'','TKTARCH_POINT'=>'');
   $xticket = hda_db::hdadb()->HDA_DB_admin('ExternalTicket');
   if (!is_null($xticket)) $xticket = hda_db::hdadb()->HDA_DB_unserialize($xticket);
   else $xticket = $xfields;
   foreach ($xfields as $field=>$v) if (!array_key_exists($field,$xticket)) $xticket[$field]=$v;
   switch ($Action) {
      case 'ACTION_ADMIN_EXTERNAL_TICKET_SAVE':
	     $xticket['VN'] = $External_Ticket_Version;
		 $xticket['FTP'] = PRO_ReadParam('tkt_ftp_type');
	     $xticket['URL'] = PRO_ReadParam('tkt_ftp_url');
	     $xticket['UNAME'] = PRO_ReadParam('tkt_ftp_uname');
	     $xticket['PW'] = PRO_ReadParam('tkt_ftp_pw');
	     $xticket['BASEDIR'] = PRO_ReadParam('tkt_ftp_basedir');
		 $xticket['DATEDIR'] = PRO_ReadParam('tkt_ftp_datedir');
		 $xticket['COLLECT_POINT'] = PRO_ReadParam('tkt_glob_collect_point');
		 $xticket['PASSTHROUGH'] = PRO_ReadParam('tkt_pass_to');
		 $xticket['TKTARCH'] = PRO_ReadParam('tkt_fs_arch');
		 $xticket['TKTARCH_POINT'] = PRO_ReadParam('tkt_fs_arch_point');
         hda_db::hdadb()->HDA_DB_admin('ExternalTicket', hda_db::hdadb()->HDA_DB_serialize($xticket));
	     break;
	  }
   global $key_mouse;
   PRO_Clear('tkt_ftp_datedir');
   $t = "<tr><th><table class=\"alc-table\"> ";
   $t .= "<tr><th colspan=2>Destination FTP Site for Ticket Generation</th></tr>";
   $t .= "<tr><td>FTP Entry Url:&nbsp;</td>";
   $t .= "<td><input type=\"text\" {$key_mouse} name=\"tkt_ftp_url\" value=\"{$xticket['URL']}\" size=80 ></td></tr>";
   //
   $t .= "<tr><td>FTP Username Auth:&nbsp;</td>";
   $t .= "<td><input type=\"text\" {$key_mouse} name=\"tkt_ftp_uname\" value=\"{$xticket['UNAME']}\" ></td>";
   $t .= "</tr>";
   //
   $t .= "<tr><td>FTP Password Auth:&nbsp;</td>";
   $t .= "<td><input type=\"password\" {$key_mouse} name=\"tkt_ftp_pw\" value=\"{$xticket['PW']}\" ></td><td>&nbsp;</td>";
   $t .= "</tr>";
   //
   $t .= "<tr><td colspan=1>Target Base Directory:&nbsp;</td>";
   $t .= "<td colspan=3><input type=\"text\" {$key_mouse} name=\"tkt_ftp_basedir\" value=\"{$xticket['BASEDIR']}\" size=80 >&nbsp;";
   $t .= "</td>";
   $t .= "</tr>";
   //
   $t .= "<tr>";
   if (strlen($xticket['URL'])>0) {
      $t .= "<tr><td colspan=2>Example will form ftp url as: ftp://{$xticket['URL']}/{$xticket['BASEDIR']}/";
	  if ($xticket['DATEDIR']==1) $t .= "D".date('Ymd',time())."/";
	  $t .= "ProfileName/--file to upload--";
	  $t .= "</td>";
	  }
   $t .= "</tr>";
   $t .= "<tr><th colspan=2>Ticket Collection Configuration</th></tr>";
   $t .= "<tr><td>Collect tickets from this global connection:</td>";
   $t .= "<td><input type=\"text\" {$key_mouse} name=\"tkt_glob_collect_point\" value=\"{$xticket['COLLECT_POINT']}\" size=80></td></tr>";
   $t .= "<tr><td colspan=2>Add a Ymd Date Directory Structure?&nbsp;<input type=\"checkbox\" name=\"tkt_ftp_datedir\" ".(($xticket['DATEDIR']==1)?"CHECKED":"")." value=\"1\"></td></tr>";
   $t .= "<tr><td>Pass Unknown Tickets to connection list:</td>";
   $t .= "<td><input type=\"text\" name=\"tkt_pass_to\" value=\"{$xticket['PASSTHROUGH']}\" size=80></td></tr>";
   $t .= "<tr><th colspan=2>Ticket Archive</th></tr>";
   $checked = (!array_key_exists('TKTARCH',$xticket)||$xticket['TKTARCH']<>'ARCH')?"CHECKED":"";
   $t .= "<tr><td><input type=\"radio\" name=\"tkt_fs_arch\" value=\"\" {$checked}>&nbsp;No Archive</td>";
   $arch = (array_key_exists('TKTARCH_POINT',$xticket))?$xticket['TKTARCH_POINT']:"";
   $checked = (array_key_exists('TKTARCH',$xticket)&&$xticket['TKTARCH']=='ARCH')?"CHECKED":"";
   $t .= "<td><input type=\"radio\" name=\"tkt_fs_arch\" value=\"ARCH\" {$checked}>&nbsp;Archive to Site: &nbsp;";
   $t .= "<input type=\"text\" {$key_mouse} name=\"tkt_fs_arch_point\" value=\"{$arch}\" size=80></td></tr>";
   $mouse = "onclick=\"issuePost('ADMIN_EXTERNAL_TICKET_SAVE---',event); return false;\" ";
   $t .= "<tr><th colspan=2>";
   $t .= "<span class=\"push_button blue\" {$mouse}   >Save Changes</span>"; 
   $t .= "</th></tr>";
   $t .= "</table></th></tr>";
   return $t;
   }

function _external_collecting_form() {
   global $Action;
   $xcollect = hda_db::hdadb()->HDA_DB_admin('ExternalCollect');
   if (!is_null($xcollect)) $xcollect = hda_db::hdadb()->HDA_DB_unserialize($xcollect);
   else $xcollect = array('FTP'=>1, 'URL'=>'','UNAME'=>'','PW'=>'','BASEDIR'=>'','DATEDIR'=>0,
							'COLLECT'=>'FTP','COLLECT_POINT'=>'','CLEANUP'=>0,
							'FSMODE'=>'','FSSITES'=>'','FSARCH'=>'','FSARCH_POINT'=>'');
   switch ($Action) {
      case 'ACTION_ADMIN_EXTERNAL_COLLECT_SAVE':
		 $xcollect['FTP'] = PRO_ReadParam('clk_ftp_type');
	     $xcollect['URL'] = PRO_ReadParam('clk_ftp_url');
	     $xcollect['UNAME'] = PRO_ReadParam('clk_ftp_uname');
	     $xcollect['PW'] = PRO_ReadParam('clk_ftp_pw');
	     $xcollect['BASEDIR'] = PRO_ReadParam('clk_ftp_basedir');
		 $xcollect['DATEDIR'] = PRO_ReadParam('clk_ftp_datedir');
		 $xcollect['COLLECT'] = PRO_ReadParam('clk_ftp_collect');
		 $xcollect['COLLECT_POINT'] = PRO_ReadParam('clk_ftp_collect_point');
		 $xcollect['CLEANUP'] = PRO_ReadParam('clk_ftp_cleanup');
		 $xcollect['FSMODE'] = PRO_ReadParam('clk_fs_mode');
		 $xcollect['FSSITES'] = PRO_ReadParam('clk_fs_sites');
		 $xcollect['FSARCH'] = PRO_ReadParam('clk_fs_arch');
		 $xcollect['FSARCH_POINT'] = PRO_ReadParam('clk_fs_arch_point');
         hda_db::hdadb()->HDA_DB_admin('ExternalCollect', hda_db::hdadb()->HDA_DB_serialize($xcollect));
	     break;
	  }
   global $key_mouse;
   PRO_Clear(array('clk_ftp_datedir', 'clk_ftp_cleanup', 'clk_fs_mode'));
   $t = "<tr><th><table class=\"alc-table\"> ";
   $t .= "<tr><th colspan=2>Configuration for External FTP-Method Collected Uploads</th></tr>";
   $t .= "<tr><td>FTP Entry Url:&nbsp;</td>";
   $t .= "<td><input type=\"text\" {$key_mouse} name=\"clk_ftp_url\" value=\"{$xcollect['URL']}\" size=80 ></td></tr>";
   //
   $t .= "<tr><td>FTP Username Auth:&nbsp;</td>";
   $t .= "<td><input type=\"text\" {$key_mouse} name=\"clk_ftp_uname\" value=\"{$xcollect['UNAME']}\" ></td>";
   $t .= "</tr>";
   //
   $t .= "<tr><td>FTP Password Auth:&nbsp;</td>";
   $t .= "<td><input type=\"password\" {$key_mouse} name=\"clk_ftp_pw\" value=\"{$xcollect['PW']}\" ></td><td>&nbsp;</td>";
   $t .= "</tr>";
   //
   $t .= "<tr><td colspan=1>Target Base Directory:&nbsp;</td>";
   $t .= "<td colspan=3><input type=\"text\" {$key_mouse} name=\"clk_ftp_basedir\" value=\"{$xcollect['BASEDIR']}\" size=80 >&nbsp;";
   $t .= "</td>";
   $t .= "</tr>";
   //
   $t .= "<tr>";
   if (strlen($xcollect['URL'])>0) {
      $t .= "<tr><td colspan=2>Example will form ftp url as: ftp://{$xcollect['URL']}/{$xcollect['BASEDIR']}/";
	  if ($xcollect['DATEDIR']==1) $t .= date('Ymd',time())."/";
	  $t .= "PF--reference--/--file to upload--";
	  $t .= "</td>";
	  }
   $t .= "</tr>";
   $t .= "<tr><th colspan=2>Auto Collection Configuration</th></tr>";
   $checked = ($xcollect['COLLECT']=='FTP')?"CHECKED":"";
   $t .= "<tr><td><input type=\"radio\" name=\"clk_ftp_collect\" value=\"FTP\" {$checked}>&nbsp;Collect using FTP</td>";
   $checked = ($xcollect['COLLECT']=='MAP')?"CHECKED":"";
   $t .= "<td><input type=\"radio\" name=\"clk_ftp_collect\" value=\"MAP\" {$checked}>&nbsp;Collect from mapped directory&nbsp;";
   $t .= "<input type=\"text\" {$key_mouse} name=\"clk_ftp_collect_point\" value=\"{$xcollect['COLLECT_POINT']}\" size=80>";
   $t .= "&nbsp;Add a Ymd Date Directory Structure?&nbsp;<input type=\"checkbox\" name=\"clk_ftp_datedir\" ".(($xcollect['DATEDIR']==1)?"CHECKED":"")." value=\"1\">";
   $t .= "&nbsp;Remove source files after collection?&nbsp;<input type=\"checkbox\" name=\"clk_ftp_cleanup\" ".(($xcollect['CLEANUP']==1)?"CHECKED":"")." value=\"1\">";
   $t .= "</td></tr>";
   $t .= "<tr><th colspan=2>File Server Mode Replicate</th></tr>";
   $checked = (!array_key_exists('FSMODE',$xcollect)||$xcollect['FSMODE']<>'FS')?"CHECKED":"";
   $t .= "<tr><td><input type=\"radio\" name=\"clk_fs_mode\" value=\"\" {$checked}>&nbsp;Normal Collect</td>";
   $sites = (array_key_exists('FSSITES',$xcollect))?$xcollect['FSSITES']:"";
   $checked = (array_key_exists('FSMODE',$xcollect)&&$xcollect['FSMODE']=='FS')?"CHECKED":"";
   $t .= "<td><input type=\"radio\" name=\"clk_fs_mode\" value=\"FS\" {$checked}>&nbsp;Replicate to Sites: &nbsp;";
   $t .= "<input type=\"text\" {$key_mouse} name=\"clk_fs_sites\" value=\"{$sites}\" size=80></td></tr>";
   $t .= "<tr><th colspan=2>Collection Archive</th></tr>";
   $checked = (!array_key_exists('FSARCH',$xcollect)||$xcollect['FSARCH']<>'ARCH')?"CHECKED":"";
   $t .= "<tr><td><input type=\"radio\" name=\"clk_fs_arch\" value=\"\" {$checked}>&nbsp;No Archive</td>";
   $arch = (array_key_exists('FSARCH_POINT',$xcollect))?$xcollect['FSARCH_POINT']:"";
   $checked = (array_key_exists('FSARCH',$xcollect)&&$xcollect['FSARCH']=='ARCH')?"CHECKED":"";
   $t .= "<td><input type=\"radio\" name=\"clk_fs_arch\" value=\"ARCH\" {$checked}>&nbsp;Archive to Site: &nbsp;";
   $t .= "<input type=\"text\" {$key_mouse} name=\"clk_fs_arch_point\" value=\"{$arch}\" size=80></td></tr>";
   $mouse = "onclick=\"issuePost('ADMIN_EXTERNAL_COLLECT_SAVE---',event); return false;\" ";
   $t .= "<tr><th colspan=2>";
   $t .= "<span class=\"push_button blue\" {$mouse}   >Save Changes</span>"; 
   $t .= "</th></tr>";
   $t .= "</table></th></tr>";
   return $t;
   }


?>