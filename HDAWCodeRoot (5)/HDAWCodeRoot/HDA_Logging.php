<?php
global $Logging_Sources;
$Logging_Sources = array(
         'HDAW'=>array( 'Caption'=> " on Hydra Web Site"),
         'UPLOAD'=>array('Caption'=>" uploaded on Hydra Web Site"),
         'EMAIL'=>array('Caption'=> " by direct Email"),
         'PHONE'=>array('Caption'=> " from Mobile Phone"),
         'SMS'=>array('Caption'=> " by SMS"),
         'RSS'=>array('Caption'=> " via RSS Feed"),
         'XML'=>array('Caption'=> " from XML Server"),
		 'SOAP'=>array('Caption'=>" receipt or sending SOAP request"),
         'FTP'=>array('Caption'=> " from FTP Site"),
		 'TICKET'=>array('Caption'=> " from upload via a Ticket"),
		 'DETECT'=>array('Caption'=>" detected from External Collect"),
         'FILE'=>array('Caption'=> " in Hydra Web Site Directory"),
         'FORM'=>array('Caption'=> " by Hydra Web Site Form direct"),
         'SCHEDULED'=>array('Caption'=>" scheduled by Hydra Web Site"),
         'TRIGGER'=>array('Caption'=>" triggered by another profile"),
         'RERUN'=>array('Caption'=>" rerun of previous failure"),
         'ROLLBACK'=>array('Caption'=>" rollback rerun of previous successful runs"),
         'USERCODE'=>array('Caption'=>" issued from profile custom code"),
         'USER'=>array('Caption'=>" issued by user on Hydra Web Site"),
         'ALERT'=>array('Caption'=>" issued by a profile running on Hydra Web Site"),
		 'ERROR'=>array('Caption'=>" issued by Hydra as an ERROR status"),
		 'CRON'=>array('Caption'=>" issued by CRON from Hydra Web Site")
   );

function HDA_EmailThis($title, $tw, $format='ALCNotice', $user = NULL, $to_list=NULL, $include_sender=true, $attach=null) {
   global $UserCode;
   if (is_null($user)) $user = $UserCode;
   $sender = hda_db::hdadb()->HDA_DB_FindUser($user);
   if (isset($sender) && is_array($sender) && count($sender)==1) $user_name = $sender[0]['UserFullName']; else $user_name="Unknown User";
   $pusers = array();
   foreach ($to_list as $a_user) {
      $alcw_user = hda_db::hdadb()->HDA_DB_FindUser($a_user);
	  if (is_array($alcw_user) && count($alcw_user)==1) {
	     $pusers[] = array($alcw_user[0]['Email'],$alcw_user[0]['UserFullName']);
		 }
	  else $pusers[] = array($a_user, $a_user);
      }
   if (count($pusers)>0) {
      $mail = array('EMAIL'=>$pusers, 
                       'SUBJECT'=>"{$title}",
                       'MESSAGE'=>$tw,
                       'FROM'=>$user_name,
					   'ATTACH'=>$attach);
      $mail['Format'] = $format;
      $ok = hda_db::hdadb()->HDA_DB_actionToQ($user, 'EMAIL', $mail);
	  if ($ok==true) return true;
	  file_put_contents("tmp/AddToQFail.txt", print_r($mail,true));
	  return false;
      }
   }
   
function HDA_EmailTicket($mailto, $username, $profile_title, $msg, $ticket_file) {
   global $UserName;
   global $UserCode;
   $mail = array('EMAIL'=>array(array($mailto, $username)), 
                       'SUBJECT'=>"HDAW Ticket",
                       'MESSAGE'=>"Your access tickets for profile {$profile_title}<br>{$msg}",
                       'FROM'=>$UserName,
					   'ATTACH'=>$ticket_file);
  $mail['Format'] = 'ALCTicket';
  hda_db::hdadb()->HDA_DB_actionToQ($UserCode, 'EMAIL', $mail);
  }

function HDA_EmailResponse($subject, $mailto, $msg, $style='ALCReply') {
   global $UserCode;
   $msg = html_entity_decode($msg);
   $msg = "Email Response\n{$msg}";
   $attach = null;
   if (!is_array($mailto)) {
	   $user = hda_db::hdadb()->HDA_DB_FindUser($mailto);
	   if (!is_array($user) || count($user)<>1) {
		  $user = hda_db::hdadb()->HDA_DB_getTickets(null, null, $mailto);
		  if (!is_array($user) || count($user)<>1) $user = hda_db::hdadb()->HDA_DB_getTickets($mailto);
		  if (!is_array($user) || count($user)<>1) return;
		  $puser = array(array($user[0]['Email'],$user[0]['UserName']));
		  $head_style = "style=\"-moz-border-radius:15px;-webkit-border-radius:15px;-khtml-border-radius:15px;-ms-border-radius:15px;border-radius:15px;";
		  $head_style .= "-moz-box-shadow:rgba(0,0,0,0.50) 20px 20px 50px 5px;-webkit-box-shadow: rgba(0,0,0,0.30) 5px 5px 5px;box-shadow: rgba(0,0,0,0.50) 5px 5px 15px 0px;";
		  $head_style .= "border:2px inset rgb(150, 216, 233);\"";
		  }
	   else $puser = array(array($user[0]['Email'],$user[0]['UserFullName']));
   }
   else $puser = array($mailto);
   $mail = array('EMAIL'=>$puser, 
                       'SUBJECT'=>"Re:{$subject}",
                       'MESSAGE'=>$msg,
                       'FROM'=>'HDAW System',
					   'Format'=>$style,
					   'ATTACH'=>$attach);
   hda_db::hdadb()->HDA_DB_actionToQ($UserCode, 'EMAIL', $mail);
   }

function HDA_LogThis($msg, $source = NULL, $user=NULL, $username=NULL, $email=false) {
   global $UserCode;
   global $UserName;
   if (is_null($source)) $source = 'HDAW';
   if (is_null($user)) { $user = $UserCode; $username = $UserName; }
   else $username = hda_db::hdadb()->HDA_DB_GetUserFullName($user);
   hda_db::hdadb()->HDA_DB_writeLogger($user, $username, $source, $msg);
   if ($email) HDA_EmailThis("New Log Entry", "From {$username}:{$msg}", 'ALCLogNotice', $user, NULL, false);
   HDA_WatchThis("LOG", $msg, $user);
   }
function HDA_LogOnly($msg, $source=NULL) {
   $s = "";
   if (is_array($msg)) {
      foreach($msg as $msg_s) $s .= "{$msg_s}\n";
      }
   else $s = $msg;
   if (is_string($s)) HDA_LogThis($s, $source, NULL, NULL, false);
   }


function HDA_WatchThis($type, $msg, $user=NULL) {
   global $UserCode;
   if (is_null($user)) $user = $UserCode;
   if (strlen($msg)>44) $msg = substr($msg, 0, 44);
   hda_db::hdadb()->HDA_DB_WriteWatchMessage($user, $type, $msg);
   }

function HDA_ReportRollback($on_item, $date_range, $email=false) {
   global $UserName;
   $title = hda_db::hdadb()->HDA_DB_TitleOf($on_item);
   $note = "Rolled back ";
   $msg = "{$note} for {$title} ";
   $dates = "from ".hda_db::hdadb()->PRO_DBtime_Styledate($date_range[0],true)." to date ".hda_db::hdadb()->PRO_DBtime_Styledate($date_range[1],true);
   $note .= $dates;
   $msg .= $dates;
   $msg .= " issued by {$UserName}";
   hda_db::hdadb()->HDA_DB_issueNote($on_item, $note);
   HDA_LogThis($msg, 'ROLLBACK');
   if ($email) HDA_EmailThis("Rollback of {$title}", $msg, 'ALCNotice');
   }

function HDA_ReportRetry($on_item) {
   global $UserName;
   $title = hda_db::hdadb()->HDA_DB_TitleOf($on_item);
   $note = "Retry data run ";
   hda_db::hdadb()->HDA_DB_issueNote($on_item, $note);
   }

function HDA_ReportTrigger($on_item, $by_task=NULL, $by_schedule=NULL, $by_other=NULL, $email=false) {
   global $UserName;
   $title = hda_db::hdadb()->HDA_DB_TitleOf($on_item);
   $note = "Triggered ";
   if (!is_null($by_task)) $note .= "by task ".hda_db::hdadb()->HDA_DB_TitleOf($by_task);
   if (!is_null($by_schedule)) {
      $note .= "on schedule";
	  $next = hda_db::hdadb()->HDA_DB_getSchedule($on_item);
	  if (!is_null($next) && is_array($next) && count($next)==1)
	     $note .= " next scheduled at ".hda_db::hdadb()->PRO_DBdate_Styledate($next[0]['Scheduled'],true);
	  }
   if (!is_null($by_other)) $note .= "by {$by_other}";
   $msg = "Process Item {$title} {$note}";
   hda_db::hdadb()->HDA_DB_issueNote($on_item, $note);
   if (!is_null($by_task)) hda_db::hdadb()->HDA_DB_issueNote($by_task, $msg, 'TAG_PROGRESS');
   HDA_LogThis($msg, (!is_null($by_task) || !is_null($by_other))?'TRIGGER':'SCHEDULED');
   if ($email) HDA_EmailThis("Trigger of {$title}", $msg, 'ALCTriggerNotice');
   }


function HDA_ReportUpload($into, $code, $source='HDAW', $user=NULL, $email=false) {
   global $UserCode;
   global $Logging_Sources;
   if (is_null($user)) $user = $UserCode;
   $profile = hda_db::hdadb()->HDA_DB_TitleOf($into);
   HDA_WatchThis('UPLOAD', $msg = "New data for profile {$profile}", $user);
   hda_db::hdadb()->HDA_DB_writeLogger($user, $username = hda_db::hdadb()->HDA_DB_GetUserFullName($user), $source, $msg);
   $msg = "{$username} obtained new data for this profile {$profile} on ".hda_db::hdadb()->PRO_DBtime_Styledate(time(),true)." {$Logging_Sources[$source]['Caption']}";
   hda_db::hdadb()->HDA_DB_issueNote($into, $msg, $tagged='INFO', $user, $username);
   }

function HDA_ProfileCollectLate($item, $warning=true, $log=null) {
   $email_format =($warning)?'HDAWarning':'ALCAlert';
   $pusers = hda_db::hdadb()->HDA_DB_UsersOfProfile($item);
   $profile_title = hda_db::hdadb()->HDA_DB_TitleOf($item);
   if ($warning) {
      $msg = "The data for profile {$profile_title} is not available for processing";
	  }
   else {
      $msg = "The data for profile {$profile_title} did not arrive on schedule, roll-up has continued";
	  }
   if (!is_null($log)) {
      foreach ($log as $m) $msg .= "\n{$m}";
      }
	HDA_EmailThis("{$profile_title} Data Late",$msg,$email_format, null, array('ProfileCollectNotices'));
   HDA_LogOnly($msg);
   hda_db::hdadb()->HDA_DB_issueNote($item, $msg, $tagged='TAG_ALERT');
   }

   
function HDA_ProfileAlert($item, $alert, $email=false) {
      $user = hda_db::hdadb()->HDA_DB_OwnerOfProfile($item);
      $users = hda_db::hdadb()->HDA_DB_UsersOfProfile($item);
      hda_db::hdadb()->HDA_DB_writeLogger($user['UserItem'], $username = $user['UserFullName'], 'ALERT', $msg = "ALERT: {$alert}");
      HDA_WatchThis('ALERT', $subject = "Alert for ".hda_db::hdadb()->HDA_DB_TitleOf($item), $user['UserItem']);
      hda_db::hdadb()->HDA_DB_issueNote($item, $msg, $tagged='TAG_ALERT', $user['UserItem'], $username);
	  HDA_SMS($item, $msg);
      if (count($users)>0) {
         $mail = array('EMAIL'=>$users, 
                       'SUBJECT'=>$subject,
                       'MESSAGE'=>$msg,
                       'FROM'=>$username,
					   'Format'=>'ALCAlert',
					   'ATTACH'=>null);
         hda_db::hdadb()->HDA_DB_actionToQ($user['UserItem'], 'EMAIL', $mail);
         }
	  return true;
   }
   
   
   
function HDA_SMS($item, $msg, $to_sms=null) {
   if (is_null($to_sms)) {
      $profile = hda_db::hdadb()->HDA_DB_ReadProfile($item);
      if (!is_null($profile) && is_array($profile)) {
	     $to_sms = $profile['SMS'];
		 }
	  }
   if (!is_null($to_sms) && strlen($to_sms)>0) {
	  $sms = hda_db::hdadb()->HDA_DB_admin('SMS');
      if (!is_null($sms) && strlen($sms)>0) $sms = hda_db::hdadb()->HDA_DB_unserialize($sms);
	  if (is_array($sms) && array_key_exists($to_sms, $sms)) {
		 $sms_recipients = explode("\n", $sms[$to_sms]);
	     foreach ($sms_recipients as $sms_recipient) {
			$sms_no = explode('#',$sms_recipient);
			if (count($sms_no)==1) $sms_no[1] = $sms_no[0];
			_sms_($item, $sms_no, $msg);
		    }
	     }
	   return true;
	   }
   return false;
   }
function ReadSMSConfig() {
   global $HDA_SMS_CFG;
   $sms_cfg = hda_db::hdadb()->HDA_DB_admin('SMS_CFG');
   if (!is_null($sms_cfg)) {
      $sms_cfg = hda_db::hdadb()->HDA_DB_unserialize($sms_cfg);
      $HDA_SMS_CFG['SUSPEND_SMS'] = $sms_cfg['SUSPEND_SMS'];
      $HDA_SMS_CFG['SMS_USERNAME'] = $sms_cfg['SMS_USERNAME'];
      $HDA_SMS_CFG['SMS_PASSWORD'] = $sms_cfg['SMS_PASSWORD'];
      $HDA_SMS_CFG['SMS_ACCID'] = $sms_cfg['SMS_ACCID'];
      $HDA_SMS_CFG['SMS_DAYLIMIT'] = $sms_cfg['SMS_DAYLIMIT'];
      $HDA_SMS_CFG['SMS_PROFILELIMIT'] = $sms_cfg['SMS_PROFILELIMIT'];
      }
   }

function _sms_($item, $sms_no, $msg) {
   global $HDA_SMS_CFG;
   if (hda_db::hdadb()->HDA_DB_profileExists($item)) hda_db::hdadb()->HDA_DB_issueNote($item, "SMS to {$sms_no[1]} on {$sms_no[0]}" , $tagged='TAG_ALERT');
   if ($HDA_SMS_CFG['SUSPEND_SMS']==1) return false;
   if (!hda_db::hdadb()->HDA_DB_throttle('SMS',$HDA_SMS_CFG['SMS_DAYLIMIT']) || !hda_db::hdadb()->HDA_DB_throttle($item,$HDA_SMS_CFG['SMS_PROFILELIMIT'])) {
      hda_db::hdadb()->HDA_DB_writeLogger(0, "SYSTEM", 'ALERT', "EXCEEDED SMS LIMIT");
	  return false;
      }
   $user = $HDA_SMS_CFG['SMS_USERNAME'];
   $password = $HDA_SMS_CFG['SMS_PASSWORD']; // "LXbWETHaHJSHcA";
   $api_id = $HDA_SMS_CFG['SMS_ACCID']; // "3384570";
   $baseurl ="http://api.clickatell.com";
  
   $text = urlencode($msg);
   $to = $sms_no[0];
   if (is_null($to) || strlen($to)==0) return false;
   $to = trim($to);
   $to = trim($to, '+');
  
   // auth call
   $url = "$baseurl/http/auth?user=$user&password=$password&api_id=$api_id";
  
   // do auth call
   $ret = file($url);
  
   // explode our response. return string is on first line of the data returned
   $sess = explode(":",$ret[0]);
   if ($sess[0] == "OK") {
  
         $sess_id = trim($sess[1]); // remove any whitespace
         $url = "$baseurl/http/sendmsg?session_id=$sess_id&to=$to&text=$text";
  
         // do sendmsg call
         $ret = file($url);
         $send = explode(":",$ret[0]);
  
         if ($send[0] <> "ID") {
             HDA_SendErrorMail("SMS  Fails to send message to {$to}");
             } 
        } 
	else {
         HDA_SendErrorMail("SMS  Authentication failure: {$ret[0]}, to: {$to}");
        }
    }

   
function HDA_PrintThis($print_hdr, $t, $paper='A4') {
   global $code_root;
   $path = "tmp/".HDA_isUnique('PDF').".data";
   file_put_contents("tmp/style_pdf.css",file_get_contents("../{$code_root}/css/style_pdf.css"));
   $stylefile = "<link rel=\"stylesheet\" type=\"text/css\" href=\"tmp/style_pdf.css\"  MEDIA=screen>";
   $t = "{$stylefile} {$t}";
   @file_put_contents($path, $t);
   _print_this($path, $print_hdr, $paper);
   }
   
function _print_this($fn, $print_hdr, $paper) {
   global $post_load;
   $enc_title = urlencode($print_hdr);
   $enc_fn = urlencode($fn);
   $post_load .= "openWindow('HDAW.php?load=HDA_Printer&file={$enc_fn}&paper={$paper}&title={$enc_title}','Print');";
   }


function _daily_pdf_report($path=null) {
   $t = _make_daily_report();
   $path = (is_null($path))?"tmp/".HDA_isUnique('PDF').".data":"{$path}.data";
   @file_put_contents($path, $t);
   return $path;
   }

function _make_daily_report() {
   return (function_exists('_custom_daily_report'))?_custom_daily_report():_standard_daily_report();
   }
   
function _standard_daily_report() {
   global $Logging_Sources;
   $t = "";
   $t .= "<h2>Daily Report for ".hda_db::hdadb()->PRO_DBtime_Styledate(time(),true)."</h2><br>";

   $t .= "<table style=\"width:100%; border:1px solid green;\" >";
   $t .= "<tr><th>Profile</th><th>Presented Date</th>";
   $t .= "<th>File</th>";
   $t .= "<th>Method</th>";
   $t .= "</tr>";
   $a = hda_db::hdadb()->HDA_DB_reportAuditTime(date('Y-m-d',strtotime("yesterday")), "00:00");
   if (!is_null($a) && is_array($a) && count($a)>0) {
      foreach ($a as $row) {
	     $t .= "<tr>";
		 $t .= "<td>{$row['Title']}</td>";
		 $t .= "<td>".hda_db::hdadb()->PRO_DBdate_Styledate($row['IssuedDate'],true)."</td>";
		 $t .= "<td>{$row['OriginalFilePath']}</td>";
		 $t .= "<td>".str_replace('AUDIT FILE','',$row['ItemText'])."</td>";
		 $t .= "</tr>";
	     }
      }
   else $t .= "<tr><td>No Files Found</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
   $t .= "</table>";

   $t_tasks = "<tr><th colspan=3 style=\"background-color:rgb(180, 230, 233);\" >Event Report</th></tr>";
   $a = hda_db::hdadb()->HDA_DB_reportEvents();
   $tasks_ran_late = $tasks_ran_success = $tasks_ran_failed = 0;
   if (is_null($a) || count($a)==0) {
      $t_tasks .= "<tr><td>No system success, failure or late events</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
      }
   else {
      $aa = array();
      foreach ($a as $row) {
		 $aa[$row['Category']][] = $row;
		 }
	  foreach ($aa as $category=>$rows) {
	     $t_tasks .= "<tr><th colspan=3>Category: {$category}</th></tr>";
         foreach ($rows as $row) {
            $t_tasks .= "<tr>";
            $t_tasks .= "<td>{$row['Title']}</td>";
			$t_tasks .= "<td>".hda_db::hdadb()->PRO_DBdate_Styletime($row['IssuedDate'])."</td>";
			$t_tasks .= "<td>";
			list($id, $evcode) = explode('_',$row['EventCode']);
			switch($evcode) {
			   case 'SUCCESS': $tasks_ran_success++; $t_tasks .= "Success"; break;
			   case 'FAILURE': $tasks_ran_failed++; $t_tasks .= "Failed"; break;
			   case 'LATE': $tasks_ran_late++; $t_tasks .= "Late"; break;
			   }
			$t_tasks .= "</td>";
            $t_tasks .= "</tr>";
            }
		 }
      }
   $t .= "<table style=\"width:100%; border:1px solid green;\" >";
   $t .= "<tr><th style=\"background-color:rgb(180, 230, 233);\" >Summary</th></tr>";
   $t .= "<tr><td >Tasks Success {$tasks_ran_success}&nbsp;Failed {$tasks_ran_failed}&nbsp;Late {$tasks_ran_late}</td></tr>";
   $t .= "</table>";
   
   $t .= "<table style=\"width:100%; border:1px solid green;\" >";
   $t .= $t_tasks;   
   $t .= "</table>";
   
   $t .= "<table style=\"width:100%; border:1px solid green;\" >";
   $t .= "<tr><th colspan=3 style=\"background-color:rgb(180, 230, 233);\" >Auto Trigger Report</th></tr>";
   $a = hda_db::hdadb()->HDA_DB_reportAutoTrigger();
   if (is_null($a) || count($a)==0) {
      $t .= "<tr><td>No Auto Trigger Activity</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
      }
   else {
      $aa = array();
      foreach ($a as $row) {
		 $aa[$row['Category']][] = $row;
		 }
	  foreach ($aa as $category=>$rows) {
	     $t .= "<tr><th colspan=3>Category: {$category}</th></tr>";
         foreach ($rows as $row) {
            $t .= "<tr>";
            $t .= "<td colspan=1>{$row['Title']}</td>";
			$t .= "<td>".hda_db::hdadb()->PRO_DBdate_Styletime($row['IssuedDate'])."</td>";
			$t .= "<td colspan=1>{$row['ItemText']}</td>";
            $t .= "</tr>";
            }
		 }
      }
   $t .= "</table>";
   
   $t .= "<table style=\"width:100%; border:1px solid green;\" >";
   $t .= "<tr><th colspan=4 style=\"background-color:rgb(180, 230, 233);\" >Audit Report</th></tr>";
   $a = hda_db::hdadb()->HDA_DB_reportAudit();
   if (is_null($a) || count($a)==0) {
      $t .= "<tr><td>No Audit Entries</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
      }
   else {
      $aa = array();
      foreach ($a as $row) {
		 $aa[$row['Title']][] = $row;
		 }
	  foreach ($aa as $title=>$rows) {
	     $t .= "<tr><th colspan=4>{$title}</th></tr>";
	     for ($i=0; ($i<5) && ($i<count($rows)); $i++) {
		   $row = $rows[$i];
           $t .= "<tr>";
		   $t .= "<td>".hda_db::hdadb()->PRO_DBdate_Styletime($row['IssuedDate'])."</td>";
		   $t .= "<td colspan=1>".wordwrap($row['ItemText'], 50, "<br>", true)."</td>";
		   $t .= "<td colspan=1>".wordwrap($row['OriginalFilePath'], 50, "<br>", true)."</td>";
           $t .= "<td>".wordwrap($row['TargetDB'], 50, "<br>", true)."</td>";
           $t .= "</tr>";
            }
		 }
      }
   $t .= "</table>";
   
   $t .= "<table style=\"width:100%; border:1px solid green;\" >";
   $t .= "<tr><th colspan=3 style=\"background-color:rgb(180, 230, 233);\" >Ticket Use Report</th></tr>";
   $a = hda_db::hdadb()->HDA_DB_reportTickets();
   if (is_null($a) || count($a)==0) {
      $t .= "<tr><td>No Ticket Entries</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
      }
   else {
      $aa = array();
      foreach ($a as $row) {
		 $aa[$row['Title']][] = $row;
		 }
	  foreach ($aa as $title=>$rows) {
	     $t .= "<tr><th rowspan=".(count($rows)+1).">{$title}</th><td>&nbsp;</td><td>&nbsp;</td></tr>";
	     foreach ($rows as $row) {
           $t .= "<tr>";
		   $t .= "<td>".hda_db::hdadb()->PRO_DBdate_Styletime($row['LastUseDate'])."</td>";
		   $t .= "<td>{$row['UserName']}</td>";
		   $use_s = "";
		   $use = $row['LastData'];
		   if (!is_null($use) && strlen($use)>0) { $use = hda_db::hdadb()->HDA_DB_unserialize($use); foreach($use as $a_use) $use_s .= "{$a_use}<br>"; }
		   
		   $t .= "<td>{$use_s}</td>";
           $t .= "</tr>";
           }
		 }
      }
   $t .= "</table>";
   $t .= "Relation Table<br>";
   $a = hda_db::hdadb()->HDA_DB_getRelationTableText();
   if (is_array($a)&&(count($a)>0)) {
      $t .= "<table style=\"width:100%; border:1px solid green;\" >";
	  $t .= "<tr>";
	  foreach ($a[0] as $field=>$v) {
	     $t .= "<th>{$field}</th>";
		 }
	  $t .= "</tr>";
	  foreach ($a as $row) {
	     $t .= "<tr>";
		 foreach ($row as $field=>$v) $t .= "<td>{$v}</td>";
		 $t .= "</tr>";
	     }
	  $t .= "</table>";
      }
   return $t;
   }

function HDA_RecordThis($tag, $t) {
   global $UserName;
   global $UserId;
   $date = date('Y-m-d G:i:s');
   $fh = fopen("ErrorLogs/Record.txt", 'a');
   if (is_resource($fh)) {
      fputs($fh, "{$date}:{$UserName}:{$UserId}:{$tag}:{$t}\n");
      }
   fclose($fh);
   }






?>