<?php


function HDA_EmailEnabled() {
global $HDA_EMAIL_CFG;
if ($HDA_EMAIL_CFG['EMAIL_ENABLED']==0) return false;
else return true;
}

function ReadEmailConfig() {
   global $HDA_EMAIL_CFG;
   $email_cfg = hda_db::hdadb()->HDA_DB_admin('EMAIL_CFG');
   if (!is_null($email_cfg)) {
      $email_cfg = hda_db::hdadb()->HDA_DB_unserialize($email_cfg);
      $HDA_EMAIL_CFG['SUSPEND_GET_MAIL'] = (array_key_exists('SUSPEND_GET_MAIL',$email_cfg))?$email_cfg['SUSPEND_GET_MAIL']:0;
      $HDA_EMAIL_CFG['GET_MAIL_IMAP'] = (array_key_exists('GET_MAIL_IMAP',$email_cfg))?$email_cfg['GET_MAIL_IMAP']:null;
      $HDA_EMAIL_CFG['GET_MAIL_ACCOUNT'] = (array_key_exists('GET_MAIL_ACCOUNT',$email_cfg))?$email_cfg['GET_MAIL_ACCOUNT']:null;
      $HDA_EMAIL_CFG['GET_MAIL_PASSWORD'] = (array_key_exists('GET_MAIL_PASSWORD',$email_cfg))?$email_cfg['GET_MAIL_PASSWORD']:null;
	  $HDA_EMAIL_CFG['GET_MAIL_ANYONE'] = (array_key_exists('GET_MAIL_ANYONE',$email_cfg))?$email_cfg['GET_MAIL_ANYONE']:0;
	  $HDA_EMAIL_CFG['GET_MAIL_HTML'] = (array_key_exists('GET_MAIL_HTML',$email_cfg))?$email_cfg['GET_MAIL_HTML']:0;
      $HDA_EMAIL_CFG['EMAIL_ENABLED'] = (array_key_exists('EMAIL_ENABLED',$email_cfg))?$email_cfg['EMAIL_ENABLED']:1;
      $HDA_EMAIL_CFG['GMAIL_HOST'] = (array_key_exists('GMAIL_HOST',$email_cfg))?$email_cfg['GMAIL_HOST']:null;
      $HDA_EMAIL_CFG['GMAIL_PORT'] = (array_key_exists('GMAIL_PORT',$email_cfg))?$email_cfg['GMAIL_PORT']:null;
      $HDA_EMAIL_CFG['GMAIL_USERNAME'] = (array_key_exists('GMAIL_USERNAME',$email_cfg))?$email_cfg['GMAIL_USERNAME']:null;
      $HDA_EMAIL_CFG['GMAIL_PASSWORD'] = (array_key_exists('GMAIL_PASSWORD',$email_cfg))?$email_cfg['GMAIL_PASSWORD']:null;
      $HDA_EMAIL_CFG['GMAIL_FROM'] = (array_key_exists('GMAIL_FROM',$email_cfg))?$email_cfg['GMAIL_FROM']:null;
      $HDA_EMAIL_CFG['GMAIL_FROM_NAME'] = (array_key_exists('GMAIL_FROM_NAME',$email_cfg))?$email_cfg['GMAIL_FROM_NAME']:null;
	  $HDA_EMAIL_CFG['GMAIL_AUTH'] = (array_key_exists('GMAIL_AUTH',$email_cfg))?$email_cfg['GMAIL_AUTH']:null;
	  $HDA_EMAIL_CFG['GMAIL_AUTH_TYPE'] = (array_key_exists('GMAIL_AUTH_TYPE',$email_cfg))?$email_cfg['GMAIL_AUTH_TYPE']:null;
	  $HDA_EMAIL_CFG['GMAIL_SECURE'] = (array_key_exists('GMAIL_SECURE',$email_cfg))?$email_cfg['GMAIL_SECURE']:null;
	  $HDA_EMAIL_CFG['GMAIL_REALM'] = (array_key_exists('GMAIL_REALM',$email_cfg))?$email_cfg['GMAIL_REALM']:null;
	  $HDA_EMAIL_CFG['GMAIL_WORKSTATION'] = (array_key_exists('GMAIL_WORKSTATION',$email_cfg))?$email_cfg['GMAIL_WORKSTATION']:null;
      $HDA_EMAIL_CFG['EMAIL_ERROR_ACCOUNT'] = (array_key_exists('EMAIL_ERROR_ACCOUNT',$email_cfg))?$email_cfg['EMAIL_ERROR_ACCOUNT']:"tim_s_jones@hotmail.com";
      }
   }


function HDA_Gmail($to, $subject, $message, $reply=NULL, &$err, $attach=NULL) {
global $HDA_EMAIL_CFG;
if ($HDA_EMAIL_CFG['EMAIL_ENABLED']==0) return False;
error_reporting(E_ALL);
//error_reporting(E_STRICT);

//date_default_timezone_set(date_default_timezone_get());


$mail             = new PHPMailer();


$mail->IsSMTP();
$mail->SMTPAuth   = ($HDA_EMAIL_CFG['GMAIL_AUTH']=='NO')?false:true;												// enable SMTP authentication
$prefix = trim($HDA_EMAIL_CFG['GMAIL_SECURE']);
if (is_null($prefix) || strlen($prefix)==0) $prefix = "ssl";
if (strcasecmp($prefix,"n")==0) $prefix = "";    									
$mail->SMTPSecure = $prefix;																		                // sets the prefix to the server
$mail->Host       = $HDA_EMAIL_CFG['GMAIL_HOST'];//"smtp.gmail.com";      											// sets GMAIL as the SMTP server
$mail->Port       = $HDA_EMAIL_CFG['GMAIL_PORT'];//465;                   											// set the SMTP port for the GMAIL server
$mail->AuthType	  = (strlen($HDA_EMAIL_CFG['GMAIL_AUTH_TYPE'])==0)?"":$HDA_EMAIL_CFG['GMAIL_AUTH_TYPE'];			// NTLM, 

$mail->Username   = $HDA_EMAIL_CFG['GMAIL_USERNAME'];//"actionotes@gmail.com";  // GMAIL username
$mail->Password   = $HDA_EMAIL_CFG['GMAIL_PASSWORD'];//"mx73Project";            // GMAIL password

$mail->Realm = $HDA_EMAIL_CFG['GMAIL_REALM'];
$mail->Workstation = $HDA_EMAIL_CFG['GMAIL_WORKSTATION'];

if (!is_null($reply)) $mail->AddReplyTo($reply,"ALC Reply");
else $mail->AddReplyTo($HDA_EMAIL_CFG['GET_MAIL_ACCOUNT'],"Reply to log and upload");


$mail->From       = $HDA_EMAIL_CFG['GMAIL_FROM'];//"tim_s_jones@hotmail.com";
$mail->FromName   = $HDA_EMAIL_CFG['GMAIL_FROM_NAME'];//"Action Notes";

$mail->Subject    = $subject;

$mail->WordWrap   = 50; // set word wrap

$mail->MsgHTML($message);

if (!is_array($to)) $to = array(array($to, "Web Warehouse Management"));
$to = _redirectEmail($to);
foreach ($to as $copy_to) {
   $mail->AddAddress($copy_to[0], $copy_to[1]);
   }
   
if ((count($to)==0)||($mail->CountAddressTo()==0)) {
   file_put_contents("tmp/Email_TO_LIST_err_{$subject}.txt", print_r($to,true));
   return true;
   }
   


if (!is_null($attach)) {
   if (!is_array($attach)) $attach = array($attach);
   foreach ($attach as $an_attach) {
      switch (pathinfo($an_attach, PATHINFO_EXTENSION)) {
	     case 'zip':
		 case 'ZIP':
            $mail->AddAttachment($an_attach, $name = '', $encoding = 'binary', $type = 'application/zip');
		    break;
		 default:
            $mail->AddAttachment($an_attach); 
			break;
		}
	  }
   }
$mail->IsHTML(true); // send as HTML
$subject = preg_replace("/[^\w]/i","_",$subject);
file_put_contents("tmp/HDA_Email_Out_{$subject}.txt",print_r($mail,true));
try {
   if(!$mail->Send()) {
     $err = "Mailer Error: " . $mail->ErrorInfo;
     file_put_contents("tmp/HDA_GMail_Fail_{$subject}.txt", $mail->ErrorInfo); 
	 $mail->SmtpClose();
     return False;
     } 
   else {
	 $mail->SmtpClose();
     return True;
     } 
   }
catch (Exception $e) {
   $err =  "Mail exception : ".$e->getMessage();
      file_put_contents("tmp/HDA_GMail_Exception.txt", $mail->ErrorInfo." ".$err); 
   $mail->SmtpClose();
   return False;
   }
}


function _redirectEmail($plist) {
   $redirect = array();
   if (class_exists('hda_db') && (is_object(hda_db::hdadb()))) {
      $redirect = hda_db::hdadb()->HDA_DB_admin('REDIRECT');
      if (!is_null($redirect)) $redirect = hda_db::hdadb()->HDA_DB_unserialize($redirect);
	  }
   $to_list = array();
   foreach ($plist as $puser) {
      if (array_key_exists($puser[0], $redirect)) {
	     $redirect_collection = $redirect[$puser[0]];
	     $redirect_ulist = explode("\n",$redirect_collection);
	     foreach ($redirect_ulist as $redirect_u) {
		    $u = explode('#',$redirect_u);
			if (count($u)==1) $u[1] = "Redirect {$u[0]} {$puser[0]}";
	        $to_list[] = array($u[0], "{$u[1]}");
            }
		 }
	  else $to_list[] = $puser;
      }
   return $to_list;
   }



function HDA_EmailReport($to, $name, $msg, $file) {
   return HDA_Gmail($to, $name, $msg, $reply=NULL, $err, $file);
}


function HDA_SendErrorMail($error) {
   global $UserId;
   global $UserCode;
   global $UserName;
   global $ActionLine;
   global $HDA_EMAIL_CFG;
   global $NowInt;
   $t = "";
   $t .= "ERROR at ".((is_object(hda_db::hdadb()))?hda_db::hdadb()->PRO_DBtime_Styledate($NowInt):"No date")."\r\n";
   $t .= "User : {$UserCode} {$UserName} {$UserId} Action: {$ActionLine}\r\n";
   $t .= $error;
   $tt = "\r\n";
  // $tt .= __debug_stack();
   $tt .= "\r\n";
   $attachment = null;
   $script_filename = $_SERVER['SCRIPT_FILENAME'];
   $root_dir = pathinfo($script_filename,PATHINFO_DIRNAME);
   $filename = "{$root_dir}\\ErrorLogs\\".date('YmdGi',time()).".txt";

   if (@file_exists($filename)) @unlink($filename);
   $handle = @fopen($filename, "a+");
   if (is_resource($handle)) {
      @fwrite($handle, "{$t}{$tt}");
      @fclose($handle);
      $attachment = "{$root_dir}\\ErrorLogs\\".date('YmdGi',time())."_attach.txt";
      if (@file_exists($attachment)) @unlink($attachment);
      $handle = @fopen($attachment, "w");
      if (is_resource($handle)) {
         @fwrite($handle, "{$t}{$tt}");
         @fclose($handle);
         }
      else $attachment = null;
      }
 //  HDA_Gmail($HDA_EMAIL_CFG['EMAIL_ERROR_ACCOUNT'], "ALC Error", $t, NULL, $mail_err, $attachment);
}

function HDA_SendSystemMail($msg, $t=NULL) {
   global $HDA_EMAIL_CFG;
   if (!HDA_Gmail($HDA_EMAIL_CFG['EMAIL_ERROR_ACCOUNT'], "System Mail", "{$msg}<br>{$t}", NULL, $mail_err)) {
   $filename = "ErrorLogs/SystemMail.txt";
   if (($handle = @fopen($filename, "a+")) !== false) {
	$msg = "System Mail Failure : {$msg}";
	@fwrite($handle, $msg);
	@fclose($handle);
	}
   }
}

function HDA_LogToFile($filename, $t) {
   $filename = "ErrorLogs/{$filename}.txt";
   if (($handle = @fopen($filename, "a+")) !== false) {
	@fwrite($handle, "\r\n::::::::::\r\n{$t}");
	@fclose($handle);
	}
}

function __debug_stack() {
   $t = "";
   $a = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
   $t .= print_r($a, true);
   return $t;
}

function FormatMail($tag, $params) {
   global $HDA_Product_Title;
   $t = "";
   $t .= "<span style=\"font-family:Verdana;font-style:normal;font-size:10px;\" >";
   $t .= "<table border=\"1\" style=\"border:1px solid rgb(0, 78, 130);border-collapse:collapse;cellpadding:2px;width:500px;background-color:white;color:black;\" >";
   $t .= "<tr><td style=\"border:1px solid rgb(0, 78, 130);border-collapse:collapse;background-color:rgb(150, 250, 240);color:rgb(0, 78, 130);font-style:bold;\" >Warehouse Mgmt Service</td></tr>";
   $t .= "<tr><td><span=\"color:rgb(0,78,130);\">";
   $to = $params['EMAIL'];
   if (!is_array($to)) {$t .= "{$to},"; $to = array($to,"Client");}
   elseif (count($to)==1) $t .= "{$to[0][0]} {$to[0][1]},";
   $t .= "</span><br>";
   $t .= "You have received the following from {$HDA_Product_Title}";
   $t .= "</td><tr>";
   $t .= "<tr><td style=\"border:1px solid rgb(0, 78, 130);border-collapse:collapse;\" >";
   switch ($tag) {
      default: $t .= "{$params['MESSAGE']}"; break;
	  case 'ALCAlert': $t .= "<h2><span style=\"color:red;\">ALERT Notice of Alert Issued by ALC Web Service</span></h2>"; break;
      case 'ALCNotice': $t .= "<h2>Notice of Web Service Activity</h2>"; break;
      case 'ALCLogNotice': $t .= "<h2>Notice a logged entry on the Web Service</h2>"; break;
      case 'ALCReportNote': $t .= "<h2>Notice of a Note Entry on the Web Service</h2>"; break;
      case 'ALCTriggerNotice': $t .= "<h2>Notice of a Triggered Process on the Web Service</h2>"; break;
      case 'ALCUpload': $t .= "<h2>Notice of new data offering in the Web Service</h2>"; break;
      case 'ALCProcess': $t .= "<h2>Notice of a data process on the Web Service</h2>"; break;
      case 'ALCProcessCode': $t .= "<h2>Message from a process run on the Web Service</h2>"; break;
      case 'ALCDailyReport': $t .= "<h2>Daily Report from the Web Service</h2>"; break;
      case 'ALCReply': $t .= "<h2>Automated reply from the Web Service</h2>"; break;
      case 'HDAWarning': $t .= "<h2>Warning Notice from the Web Service</h2>"; break;
      case 'ALCAlarm': $t .= "<h2>Alarm Reminder from the Web Service</h2>"; break;
	  case 'ALCTicket': $t .= "<h2>Access Ticket for data uploads from Web Service</h2>"; break;
      case 'RegisterUser': $t .= "<h2>New Account</h2>"; break;
      case 'InviteUser': $t .= "<h2>Invitation</h2>"; break;
      case 'ResetPassword': $t .= "<h2>Reset Password</h2>"; break;
      }
   $t .= "{$params['MESSAGE']}";
   $t .= "</td></tr>";
   $t .= "</table></span>";
   return $t;



   }


function HDA_SendMail($tag, $params, &$err) {
   $email_msg = "";
   $reply_to = NULL;
   $email_to = $params['EMAIL'];
   $email_subject = "{$params['SUBJECT']}";
   $email_msg = FormatMail($tag, $params);
   $err = NULL;
   if (array_key_exists('ATTACH',$params) && (is_array($params['ATTACH']) || strlen($params['ATTACH'])>0)) $attach = $params['ATTACH']; else $attach = NULL;
   if (!HDA_Gmail($email_to, $email_subject, $email_msg, $reply_to, $err, $attach)) { $err = "FAILED TO SEND :{$err}"; return false; }
   return true;
   }




class readmail
{
	
   function getdecodevalue($message,$coding) {
      if ($coding == 0) { 
         $message = @imap_8bit($message); 
	   } 
	elseif ($coding == 1) { 
         $message = @imap_8bit($message); 
	   } 
	elseif ($coding == 2) { 
	   $message = @imap_binary($message); 
	   } 
	elseif ($coding == 3) { 
	   $message=@imap_base64($message); 
	   } 
	elseif ($coding == 4) { 
	   $message = @imap_qprint($message); 
	   } 
	elseif ($coding == 5) { 
	   $message = @imap_base64($message); 
	   } 
	return $message;
	}

function build_structure(&$mbox, $jk, &$s, $savedirpath="", $part_n=1, $level = -1) {
   $messages = array();
   $message = array();
   $message['type'] = $s->type;
   $message['subtype'] = $s->subtype;
   $message['encoding'] = $s->encoding;
   if (!property_exists($s, 'parts')) {
      $section = ($level>0)?"{$level}.{$part_n}":"{$part_n}";
      if(property_exists($s, 'disposition') && ($s->disposition == "ATTACHMENT")) {
	     $attach_file = null;
		 $params = (property_exists($s,'dparameters'))?$s->dparameters:((property_exists($s,'parameters'))?$s->parameters:null);
		 if (!is_null($params)) foreach ($params as $o) {
			if (strtoupper($o->attribute)=='FILENAME' || strtoupper($o->attribute)=='NAME') $attach_file = $o->value;
			}
		 if (is_null($attach_file)) $attach_file = $s->dparameters[0]->value;
         $message['filename'] = $attach_file;
         $f_path = pathinfo($message['filename']);
         $message['ext'] = $f_ext = (array_key_exists('extension',$f_path))?$f_path['extension']:"";
         $message['code'] = $f_id = $f_path['filename']; //HDA_isUnique('UP');
         $message['file'] = $filename = "{$savedirpath}{$f_id}.{$f_ext}";
	   $mege = @imap_fetchbody($mbox,$jk,$section);  
	   $fp=fopen($filename,'w');
	   $data=$this->getdecodevalue($mege,$s->encoding); 	
	   fputs($fp,$data);
	   fclose($fp);
         }
      else {
	     $bb = @imap_fetchbody($mbox,$jk,$section);
		 file_put_contents("tmp/dumpmailbody.txt",$bb);
         $message['body'][$s->subtype] = $this->getdecodevalue($bb, $s->encoding);
         }
      $messages[] = $message;
      }
   else {
      $p_n = 1; foreach ($s->parts as $part) {
		if (!is_null($part) && is_object($part)) {
			$m = $this->build_structure($mbox, $jk, $part, $savedirpath, $p_n, $level+1);$p_n++;
			$messages = array_merge($messages, $m);
			}
         }
      }
   return $messages;

   }
   
   function decodeMime($s) {
      $elems = imap_mime_header_decode($s);
	  if (is_array($elems) && count($elems)>0) return $elems[0]->text;
	  return $s;
      }

   function getdata(&$mbox, $savedirpath, $at_name=NULL){
	$messages = array();
			
	for ($jk = 1; $jk <= @imap_num_msg($mbox); $jk++) {
         $message = array();
         $message['msg']=$jk;
         $header = @imap_headerinfo($mbox, $jk);
 file_put_contents("tmp/dumpmailheader.txt", print_r($header, true));
        if (property_exists($header, 'from')) {
            $message['from'] = "";
            $message['email'] = "";
            foreach ($header->from as $from) if (property_exists($from, 'mailbox') && property_exists($from,'host')) {
               $message['from'] .= (((property_exists($from, 'personal'))?"{$from->personal}":"Unknown"));
               $message['email'] .= "{$from->mailbox}@{$from->host};";
               }
            }
         if (property_exists($header, 'subject')) $message['subject'] = $this->decodeMime($header->subject); else $message['subject'] = "No Subject";
         if (property_exists($header, 'date')) $message['date'] = $header->date;
	     $structure = @imap_fetchstructure($mbox, $jk); 
 file_put_contents("tmp/structure_{$jk}.txt", print_r($structure, true));
		 if (!is_null($structure) && is_object($structure)) {
			$message['content'] = $this->build_structure($mbox, $jk, $structure, $savedirpath);
			$messages[] = $message;
			}
         
		 @imap_delete($mbox, $jk);
 
		 }

      return $messages;
      }
}

function _find_body($message, $as_html = false) {
   $plain = "";
   $html = "";
   $dd = "";
   $other = "";
   if (array_key_exists('content',$message)) {
      foreach ($message['content'] as $content) {
         if (array_key_exists('body', $content) && is_array($content['body'])) {
		     $dd .= print_r($content['body'], true);
            if (array_key_exists('HTML',$content['body'])) $html .= $content['body']['HTML'];
            if (array_key_exists('PLAIN',$content['body'])) $plain .= $content['body']['PLAIN'];
            $kk = array_keys($content['body']);
            if (count($kk)>0) { $dd .= "Other keys:".print_r($kk,true)."\n";$other .= $content['body'][$kk[0]];}
            }
         }
      }
if (strlen($plain)>0) { 
   $dd .= "Plain is set\n"; 
   $dd .= $plain;
   $dd .= "\n";
   }
if (strlen($html)>0) { 
   $dd .= "Html is set\n"; 
   $dd .= $html;
   $dd .= "\n";
   }
if (strlen($other)>0) { 
   $dd .= "Other is set\n"; 
   $dd .= $other;
   $dd .= "\n";
   }
if ($as_html && (strlen($html)>0)) {
   $dd .= "Will use html\n";
   $s = html_entity_decode($html, ENT_NOQUOTES);
   }
elseif (strlen($plain)>0) {
   $dd .= "Will use plain\n";
   $s = preg_replace("#=20[\n\r]*#","\n",$plain);
   }
elseif (strlen($other)>0) $s = $other;
   
   $dd .= "\n\n\nNow is:\n\n{$s}";
   @file_put_contents("tmp/dumpmail.txt", $dd);
  
   return $s;
   }

function _find_attachments($message) {
   $attachments = array();
   if (array_key_exists('content',$message)) {
      foreach ($message['content'] as $content) {
         if (array_key_exists('file',$content)) {
            $attachments[] = array('code'=>$content['code'],'file'=>$content['file'],'filename'=>$content['filename'],'ext'=>$content['ext']);
			}
         }
      }
   return $attachments;
   }
   

function _clean_replyMessage($message) {
   HDA_LogToFile("latestinmail",$message);
   $m = preg_replace('/\bTo:.*/','',$message);
   if (!is_null($m)) $message = $m; 
   $m = preg_replace('/\bFrom:.*/','',$message);
   if (!is_null($m)) $message = $m;
   $m = preg_replace('/\bSubject:.*/','',$message);
   if (!is_null($m)) $message = $m;
   $m = preg_replace('/\bDate:.*/','',$message);
   if (!is_null($m)) $message = $m;
   $m = preg_replace('/=\r\r\n/','',$message);
   if (!is_null($m)) $message = $m;
   $m = preg_replace('/[\r\n]+[\s\t]*[\r\n]+/',' ',$message);
   if (!is_null($m)) $message = $m;
   return $message;
   }

function HDA_getMail($since_date=0) {
   global $HDA_EMAIL_CFG;
	  file_put_contents("tmp/getmail.txt",print_r($HDA_EMAIL_CFG,true));
   if (is_null($HDA_EMAIL_CFG['SUSPEND_GET_MAIL']) || $HDA_EMAIL_CFG['SUSPEND_GET_MAIL']==0) return false;
   $incoming_mail = false;
   $last_date = 0;
   $savedirpath = "tmp/";
   $connection = @imap_open("{".$HDA_EMAIL_CFG['GET_MAIL_IMAP']."}", $HDA_EMAIL_CFG['GET_MAIL_ACCOUNT'],$HDA_EMAIL_CFG['GET_MAIL_PASSWORD']);
   if ($connection === false) {
        // HDA_SendErrorMail("Get Mail fails open ".$HDA_EMAIL_CFG['GET_MAIL_IMAP']." error details: ".print_r(@imap_errors(),true));
	  file_put_contents("tmp/connectmail.txt",print_r($connection,true));
		HDA_LogToFile("GetMail_".date('Ymd'), print_r(@imap_errors(),true));
      }
   else {
      $get_mail_failures = 0;
      $msgs=new readmail(); 
      $messages = $msgs->getdata($connection,$savedirpath, $at_name=NULL); 
	  file_put_contents("tmp/dumpmessages.txt",print_r($messages,true));
      $incoming_mail = (count($messages)>0);
      for ($i=0; $i<count($messages); $i++) {
         if (array_key_exists('email',$messages[$i])) {
		    $profiles = null;
            $email = explode(';',$messages[$i]['email']);
			HDA_LogOnly("Email received from {$email[0]} subject {$messages[$i]['subject']}",'EMAIL');
			$user = hda_db::hdadb()->HDA_DB_FindKnownEmailUser($email[0]);
			if (!is_null($user)) $profiles = hda_db::hdadb()->HDA_DB_profileNames();
			elseif ($HDA_EMAIL_CFG['GET_MAIL_ANYONE']==1) {
			   $user = hda_db::hdadb()->HDA_DB_FindAnonymousEmailUser();
			   $profiles = hda_db::hdadb()->HDA_DB_profileNames('ANONYMOUS_EMAIL');
			   }
			if (is_null($user)) {
			   HDA_LogOnly("Email from unknown user {$email[0]} subject {$messages[$i]['subject']}",'ERROR');
			   file_put_contents("tmp/UnknownEmail.txt", print_r($messages[$i], true));
			   @imap_delete($connection, $messages[$i]['msg']);
			   continue;
               }
			if (!is_array($profiles)) {
			   HDA_LogOnly("Email from user {$email[0]} subject {$messages[$i]['subject']} - no profiles exist",'ERROR');
			   @imap_delete($connection, $messages[$i]['msg']);
			   continue;
			   }
            $name = trim($messages[$i]['subject']);
            $date = $messages[$i]['date'];
            $last_date = max($last_date, strtotime($date));
            $message = _find_body($messages[$i], $HDA_EMAIL_CFG['GET_MAIL_HTML']==1);
            $attachments = _find_attachments($messages[$i]);
            $code = NULL;
            $profile = array();
            $msg = "Your email {$name} was received on ".hda_db::hdadb()->PRO_DBdate_Styledate($date,true)."<br>";
			if (count($profiles)==1) foreach ($profiles as $profile_id=>$profile_title) {
			   $profile[] = array($profile_id, $profile_title);
               }				  
            if (count($profile)==0) foreach ($profiles as $profile_id=>$profile_title) {
			   if (strcasecmp($profile_title, $name)==0) $profile[] = array($profile_id, $profile_title);
               }				  
            if (count($profile)==0) foreach ($profiles as $profile_id=>$profile_title) {
               $found = stripos($profile_title, $name);
               if ($found !== false) $profile[] = array($profile_id, $profile_title);
			   }
            if (count($profile)==0) foreach ($profiles as $profile_id=>$profile_title) {
               $found = (stripos($name, $profile_title)!==false) || (stripos($name, _nameToId($profile_title))!==false);
               if ($found === true) $profile[] = array($profile_id, $profile_title);
			   }
            if (count($profile)<>1) {
               $msg .= "FAIL: You are enabled for multiple profiles, unable to find a unique profile name in your email message subject {$name} to assign to this file";
               if (count($profile)>0) {
                  $msg .= "<br>Possible profile name matches include: ";
                  foreach ($profile as $a_profile) $msg .= " {$a_profile[1]} or "._nameToId($a_profile[1])."<br>";
					HDA_EmailResponse($name, $email[0], $msg);
                  }
			   @imap_delete($connection, $messages[$i]['msg']);
			   continue;
               }
            if (!is_null($user)) { 
               if (isset($attachments) && is_array($attachments) && count($attachments)>0) {
				   if ((count($attachments)>1) && (INIT('MULTIPLE_UPLOADS')=='ZIP')) {
						$path_info = pathinfo($attachments[0]['file']);
						$path = HDA_TargetForFile($profile[0][0], $path_info['filename'], 'zip');
						$files = array(); foreach ($attachments as $attachment) {
							$files[] = $attachment['file'];
							$msg .= "Attach {$attachment['file']} ";
						}
						HDA_Zip($path, $files, $problem);
						$msg .= "Emailed multiple files, {$problem}, to zip {$path}";
						$attachments = array(array('filename'=>$path,'code'=>$attachment[0]['code'],'ext'=>'zip','file'=>$path));
				   }
			      foreach ($attachments as $attachment) {
			         $msg .= "Will process attachment {$attachment['filename']}<br>";
                     $path_info = pathinfo($attachment['filename']);
                     $source_info = "{$path_info['basename']} {$name} ";
				     $uploaded_code=$attachment['code'];
                     rename($attachment['file'], $path = HDA_TargetForFile($profile[0][0], $uploaded_code, $attachment['ext']));
					if (!is_null($code = hda_db::hdadb()->HDA_DB_addRunQ(
								$uploaded_code, 
								$profile_item = $profile[0][0],
								$user[0],					
								$path, 
								$path_info['basename'], 
								'EMAIL',
								$source_info = "EMAIL:{$source_info} SENDER:{$email[0]}",
								hda_db::hdadb()->PRO_DB_dateNow()))) {
						$note = "Email attachment of {$attachment['filename']} to pending process queue";
						hda_db::hdadb()->HDA_DB_issueNote($profile_item, $note, 'TAG_PROGRESS');
						HDA_LogThis(hda_db::hdadb()->HDA_DB_TitleOf($profile_item)." {$note}");
						$msg .= "This has been added to the process queue  and assigned to profile {$profile[0][1]}";
						HDA_ReportUpload($profile_item, $code, $source='EMAIL', $user[0]);
						}
					 }
                  }
               else {
                  // no atttachments, look for data as message
			      $uploaded_code =  HDA_isUnique('UP');
                  $msg .= "Your email was received without attachments - will process message body<br>"; 
                  $source_info = "{$name}";
                  $path = HDA_TargetForFile($profile_item = $profile[0][0], $uploaded_code, 'txt');
				  $build_filename = "EmailBody_{$uploaded_code}_{$i}_".date('YmdGis').".txt";
                  @file_put_contents($path, $message); _chmod($path);
					if (!is_null($code = hda_db::hdadb()->HDA_DB_addRunQ(
								$uploaded_code, 
								$profile_item,
								$user[0],					
								$path, 
								$build_filename, 
								'EMAIL',
								$source_info = "EMAIL:{$source_info} SENDER:{$email[0]}",
								hda_db::hdadb()->PRO_DB_dateNow()))) {
						$note = "Email body to pending process queue";
						hda_db::hdadb()->HDA_DB_issueNote($profile_item, $note, 'TAG_PROGRESS');
						HDA_LogThis(hda_db::hdadb()->HDA_DB_TitleOf($profile_item)." {$note}");
						$msg .= "This has been added to the process queue  and assigned to profile {$profile[0][1]}";
						HDA_ReportUpload($profile_item, $code, $source='EMAIL', $user[0]);
						}
                  }
               HDA_EmailResponse($name, $email[0], $msg);
			   }
            @imap_delete($connection, $messages[$i]['msg']);
            }
         }
      @imap_expunge($connection);
      @imap_close($connection);
      if ($last_date<=$since_date) $incoming_mail = false;
      }  
         
   return $incoming_mail;
      
   
   }
			











?>