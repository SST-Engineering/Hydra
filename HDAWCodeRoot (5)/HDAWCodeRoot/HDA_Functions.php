<?php
//include_once "../{$code_root}/Classes/QRCodes/qrcodes_class.php";


/* Encoding Links */

function _link_convert($str,$ky='TheCaseIsAltered'){ 
   if($ky=='')return $str; 
   $ky=str_replace(chr(32),'',$ky); 
   if(strlen($ky)<8)exit('key error'); 
   $kl=strlen($ky)<32?strlen($ky):32; 
   $k=array();for($i=0;$i<$kl;$i++){ 
   $k[$i]=ord($ky{$i})&0x1F;} 
   $j=0;for($i=0;$i<strlen($str);$i++){ 
   $e=ord($str{$i}); 
   $str{$i}=$e&0xE0?chr($e^$k[$j]):chr($e); 
   $j++;$j=$j==$kl?0:$j;} 
   return $str; 
   } 
function PRO_encode_link($s,$ky='TheCaseIsAltered') {
   return urlencode(base64_encode(_link_convert($s, $ky)));
   }

function PRO_decode_link($s,$ky='TheCaseIsAltered') {
   return _link_convert(base64_decode(urldecode($s)), $ky);

   }

   Global $ebcd_ascii;
function _init_ebcd_ascii() {
	Global $ebcd_ascii;
	if (is_array($ebcd_ascii)) return;
	$ebcd_ascii = array();
	$ebcd_ascii["40"] = " ";
	$ebcd_ascii["4A"] = "¢";
	$ebcd_ascii["4B"] = ".";
	$ebcd_ascii["4C"] = "<";
	$ebcd_ascii["4D"] = "(";
	$ebcd_ascii["4E"] = "+";
	$ebcd_ascii["4F"] = "|";
	$ebcd_ascii["5A"] = "!";
	$ebcd_ascii["5B"] = "$";
	$ebcd_ascii["5C"] = "*";
	$ebcd_ascii["5D"] = ")";
	$ebcd_ascii["5E"] = ";";
	$ebcd_ascii["5F"] = "¬";
	$ebcd_ascii["60"] = "-";
	$ebcd_ascii["61"] = "/";
	$ebcd_ascii["6A"] = "¦";
	$ebcd_ascii["6B"] = ",";
	$ebcd_ascii["6C"] = "%";
	$ebcd_ascii["6D"] = "_";
	$ebcd_ascii["6E"] = ">";
	$ebcd_ascii["6F"] = "?";
	$ebcd_ascii["79"] = "`";
	$ebcd_ascii["7A"] = ":";
	$ebcd_ascii["7B"] = "#";
	$ebcd_ascii["7C"] = "@";
	$ebcd_ascii["7D"] = "'";
	$ebcd_ascii["7E"] = "=";
	$ebcd_ascii["7F"] = " '' ";
	$ebcd_ascii["81"] = "a";
	$ebcd_ascii["82"] = "b";
	$ebcd_ascii["83"] = "c";
	$ebcd_ascii["84"] = "d";
	$ebcd_ascii["85"] = "e";
	$ebcd_ascii["86"] = "f";
	$ebcd_ascii["87"] = "g";
	$ebcd_ascii["88"] = "h";
	$ebcd_ascii["89"] = "i";
	$ebcd_ascii["91"] = "j";
	$ebcd_ascii["92"] = "k";
	$ebcd_ascii["93"] = "l";
	$ebcd_ascii["94"] = "m";
	$ebcd_ascii["95"] = "n";
	$ebcd_ascii["96"] = "o";
	$ebcd_ascii["97"] = "p";
	$ebcd_ascii["98"] = "q";
	$ebcd_ascii["99"] = "r";
	$ebcd_ascii["A1"] = "~";
	$ebcd_ascii["A2"] = "s";
	$ebcd_ascii["A3"] = "t";
	$ebcd_ascii["A4"] = "u";
	$ebcd_ascii["A5"] = "v";
	$ebcd_ascii["A6"] = "w";
	$ebcd_ascii["A7"] = "x";
	$ebcd_ascii["A8"] = "y";
	$ebcd_ascii["A9"] = "z";
	$ebcd_ascii["C0"] = "{";
	$ebcd_ascii["C1"] = "A";
	$ebcd_ascii["C2"] = "B";
	$ebcd_ascii["C3"] = "C";
	$ebcd_ascii["C4"] = "D";
	$ebcd_ascii["C5"] = "E";
	$ebcd_ascii["C6"] = "F";
	$ebcd_ascii["C7"] = "G";
	$ebcd_ascii["C8"] = "H";
	$ebcd_ascii["C9"] = "I";
	$ebcd_ascii["D0"] = "}";
	$ebcd_ascii["D1"] = "J";
	$ebcd_ascii["D2"] = "K";
	$ebcd_ascii["D3"] = "L";
	$ebcd_ascii["D4"] = "M";
	$ebcd_ascii["D5"] = "N";
	$ebcd_ascii["D6"] = "O";
	$ebcd_ascii["D7"] = "P";
	$ebcd_ascii["D8"] = "Q";
	$ebcd_ascii["D9"] = "R";
	$ebcd_ascii["E0"] = "\\";
	$ebcd_ascii["E2"] = "S";
	$ebcd_ascii["E3"] = "T";
	$ebcd_ascii["E4"] = "U";
	$ebcd_ascii["E5"] = "V";
	$ebcd_ascii["E6"] = "W";
	$ebcd_ascii["E7"] = "X";
	$ebcd_ascii["E8"] = "Y";
	$ebcd_ascii["E9"] = "Z";
	$ebcd_ascii["F0"] = "0";
	$ebcd_ascii["F1"] = "1";
	$ebcd_ascii["F2"] = "2";
	$ebcd_ascii["F3"] = "3";
	$ebcd_ascii["F4"] = "4";
	$ebcd_ascii["F5"] = "5";
	$ebcd_ascii["F6"] = "6";
	$ebcd_ascii["F7"] = "7";
	$ebcd_ascii["F8"] = "8";
	$ebcd_ascii["F9"] = "9";
	$ebcd_ascii["FF"] = "E0";
}
function PRO_ebcdic_hex_to_ascii($ebcdic_hexstring /*expecting something like F0F1....*/) {
    Global $ebcd_ascii;
	_init_ebcd_ascii();
    $asciiOut = "";    
    while(strlen($ebcdic_hexstring)>1)//F0F1F2F3F -> F1F2F3F
    {
        $thisEbcdic = substr($ebcdic_hexstring, 0, 2);//F0->F1
        //if(!is_null($ebcd_ascii[$thisEbcdic]))
        $asciiOut = $asciiOut.$ebcd_ascii[$thisEbcdic];//0->01
        $ebcdic_hexstring = substr($ebcdic_hexstring, 2);//F1F2F3F -> F2F3F
    }    

    return $asciiOut;

}
function PRO_ebcdic_to_ascii($ebcdic) {
	return PRO_ebcdic_hex_to_ascii(strtoupper(bin2hex($ebcdic)));
}


function _gen_pw() {
   $chars = "hdgsikehj658ABGTRuswkpjkwa4287456ghZBCLJGHDSGywbK635M84";
   $np = "";
   for ($i=0; $i<8; $i++) {
      $np .= $chars[rand(0, strlen($chars)-1)];
      }
   return $np;
   }


function HDA_noquotes($text) {
   return trim(str_replace(array("\"","\'","\n","\r","'"), array("",""," ","",""),$text));
}

function _makeSQLstring($v) {
	  if (is_string($v)) $v = str_replace("'","''",$v);
	  return $v;
	  }


function _nameToId($name) {
   return preg_replace("/[^a-zA-Z0-9]/", "_", $name);
   }

function _clean($text) {
   if (is_array($text)) {
      $a = array();
      foreach ($text as $k=>$p) $a[$k]=HDA_noquotes($p);
      return $a;
      }
   else return HDA_noquotes($text);
   }
   
function _string_lines($s) {
   $s = @str_replace(array("\f","\r"),"\n",$s);
   $lines = explode("\n",$s);
   $a = array();
   foreach ($lines as $line) {$line = trim($line); if (strlen($line)>0) $a[] = trim($line); }
   return $a;
   }

function HDA_displayTextWithLinks($txt) {
   $t = "";
   $matchUrl = implode("",array(
		"/(www|ftp:\/\/|http:\/\/|https:\/\/)" // match opening of a URL, possibly in parenthesis, quotes, etc.
           ,"[a-zA-Z0-9\-\.\/]+\.[a-zA-Z]{2,}(\/\S*)?/i"));
   $t .= preg_replace_callback($matchUrl, "_makeUrlLink_", $txt);
   return $t;
   }

function _textToHTML($s) {
   $s = htmlentities($s);
   return str_replace("\n","<br>",$s);
   }
function _htmlToText($s) {
   $s = str_replace("<br>","\n",$s);
   $s = preg_replace("/<[^>]*>/","",html_entity_decode($s));
   return $s;
   }

function _makeUrlLink_($matches) {
   if (strcasecmp($matches[1],"www")==0) $matches[0] = "http://".$matches[0];
   return "<a href=\"{$matches[0]}\" target=\"_blank\" >{$matches[0]}</a>";
   }

function _in_array_icase($needle, $haystack) {
   if (!is_array($haystack)) return false;
   if (is_null($needle)) return false;
   foreach ($haystack as $hay) if (strcasecmp(trim($needle), trim($hay))==0) return true;
   return false;
   }
   
function _cmp_null_empty($v, $w) {
   return ((is_null($v) || (strlen(trim($v))==0)) && (is_null($w) || (strlen(trim($w))==0)));
   }
   
function _makeUserRef($code) {
   return preg_replace("/[^\w\d]/","_",$code);
   }
   
function _is_float_val($v) {
   return preg_match("/[\d]{1,}[\.]{1,1}[\d]{1,}/",$v);
   }


function _TimeIntervalToSecs($s) {
   $ti = new DateInterval($s);
   return $ti->d*60*60*24+$ti->h*60*60+$ti->m*60+$ti->s;
   }
   
function _style_secs_time($secs) {
   if ($secs>3600) return sprintf('%d hrs %d mins %d secs',$secs/3600, ($secs % 3600)/60, $secs % 60);
   if ($secs>60) return sprintf('%d mins %d secs',($secs/60), $secs % 60);
   return "{$secs} secs";
   }

function HDA_extractDate($format, $string) {
   $dMask = array(
      'H'=>'hour',
      'i'=>'minute',
      's'=>'second',
      'y'=>'year',
      'm'=>'month',
      'd'=>'day'
      );
   $tok = strtok($string, " \n\t");
   $date = null;
   while ($tok !==false) {
      if (strlen($format)==strlen($tok)) {
         $dt = array();
         for ($i = 0; $i<strlen($format); $i++) {
            if (array_key_exists($format[$i], $dMask)) {
               if (!array_key_exists($format[$i], $dt)) $dt[$format[$i]] = "";
               $dt[$format[$i]] .= $tok[$i];
               }
            else {
               if ($format[$i] <> $tok[$i]) { $dt = null; break; }
               }
            }
 
         if (!is_null($dt) && array_key_exists('y',$dt) && array_key_exists('m',$dt) && array_key_exists('d',$dt)) {
            $time = strtotime("{$dt['y']}-{$dt['m']}-{$dt['d']}");
            if (!is_null($time) || ($time !== false) || ($time<>0)) {
               $date = hda_db::hdadb()->PRO_DB_Date($time);
               break;
               }
            }
         }
      $tok = strtok(" \n\t");
      }
   return $date;
   }
   
function _inBusinessDayRange($days=6) {
   if (is_null($days)) return true;
   $i = strtotime("first day of this month + {$days} weekdays");
   return ($i>time());
   }
function _lastBusinessDayThisMonth($days=6) {
    $i = strtotime("first day of this month + {$days} weekdays");
    return date('l jS', $i);
   }	
   
function _chmod($path) {
   try {
    //  if (@file_exists($path)) @chmod($path, 0777);
      }
    catch (Exception $e) {
       HDA_SendErrorMail("Fails in chmod on path {$path} : {$e}");
      }
   }
   
   
function _win_kill($pid){ 
   try {
     $wmi=new COM("winmgmts:{impersonationLevel=impersonate}!\\\\.\\root\\cimv2"); 
     $procs=$wmi->ExecQuery("SELECT * FROM Win32_Process WHERE ProcessId={$pid}");
	 $a = array();
	 foreach($procs as $proc) { $a[] = array('Proc'=>$proc, 'Name'=>$proc->Name, 'PID'=>$proc->ProcessId); }
     if (count($a)==1) {
       $proc = $a[0]['Proc'];
       if (strcmp($proc->Name, "php-cgi.exe")==0) {	   
          $err = $proc->Terminate(); 
		  if ($err>0) return _win_proc_cmd("-k {$pid}");
		  return "Attempt to kill  {$pid}";
		  }
	   return "Attempt to kill non php cgi {$pid}";
	   }
	 }
	catch (Exception $e) {
	 return $e;
	 }
	return "To kill {$pid} - no php cgi processes found";
    } 
	
function _win_proc() {
   try {
     $wmi=new COM("winmgmts:{impersonationLevel=impersonate}!\\\\.\\root\\cimv2"); 
     $procs=$wmi->ExecQuery("SELECT * FROM Win32_Process WHERE Name='php-cgi.exe'");
	 $a = array();
	 $err = 0;
     foreach($procs as $proc) {
	   $aa = array();
	   $err = $proc->SetPriority(16384);
	   $aa['PID'] = $proc->ProcessId; $aa['NAME'] = $proc->Name; $aa['DATE'] = $proc->CreationDate; $aa['PRIORITY'] = $proc->Priority; $aa['ERR'] = $err;
	   $a[] = $aa;
	   }
	 if ($err>0) _win_proc_cmd();
	 }
   catch (Exception $e) {
     _win_proc_cmd();
     $a = array();
     }
   return $a;
   }
function _win_proc_cmd($cmd=null, $exe="RunWimCim.exe") {
   try {
      $io_descriptors = array(
                           0=>array("pipe",'r'),
                           1=>array("pipe",'w'),
                           2=>array("pipe",'a')
                           );
      $script_filename = $_SERVER['SCRIPT_FILENAME'];
      $root_dir = pathinfo($script_filename,PATHINFO_DIRNAME);
      $working_dir = "{$root_dir}/tmp";
	  $bindir = "{$root_dir}\\..\\HDAWCodeRoot\\binary\\{$exe}";
      $handle = @proc_open("{$bindir} {$cmd}", $io_descriptors, $pipes, $working_dir);
      if ($handle===false) {
         return "Fails to open command";
         }
      else {
	     $s = "";
         $status = proc_get_status($handle);
         $the_close = ($status['running'])?null:$status['exitcode'];
         @fclose($pipes[0]);
         $s .= stream_get_contents($pipes[1]);
         @fclose($pipes[1]);
         $s .= stream_get_contents($pipes[2]);
         @fclose($pipes[2]);
         $proc_close = @proc_close($handle);
         $s = (is_null($the_close))?$proc_close:$the_close;
	    }
	 }
   catch (Exception $e) {
     $s .= $e;
     }
   return $s;
   }   
function _win_proc_editor($profile) {
   $script_filename = $_SERVER['SCRIPT_FILENAME'];
   $root_dir = pathinfo($script_filename,PATHINFO_DIRNAME);
   $working_dir = $root_dir."/CUSTOM/{$profile}";
   $editor_path = INIT('EDITOR');
   $editor_dir = pathinfo($editor_path, PATHINFO_DIRNAME);
   $editor_exe = pathinfo($editor_path, PATHINFO_BASENAME);
   $bat = $editor_dir[0].":\n";
   $bat .= "cd {$editor_dir}\n";
   $bat .= "{$editor_exe} -nosession {$working_dir}/alcode.alc\n";
   $bat_cmd = $working_dir."/editor";
   file_put_contents("{$bat_cmd}.bat", $bat);
   $bat_cmd = str_replace("\\","/",$bat_cmd);
   return $bat_cmd;
   }   
   
function HDA_validLogin($userId, $pw) {
   global $LoggedIn;
   global $UserId;
   global $UserCode;
   global $UserName;
   global $WarehouseId;
   global $LastLoginAt;
   global $UserAllow;
   global $DevUser;
   
   $LoggedIn = false;
   if ((strlen($userId)==0) || (strlen($pw)==0)) return false;
   $userRow = hda_db::hdadb()->HDA_DB_FindUser($userId);
   if (!isset($userRow)) return false;
   if (!is_array($userRow) || count($userRow)<>1) return false;
   $userRow = $userRow[0];
   if (!password_verify($pw, $userRow['PW'])) return false;
   PRO_AddToParams('UserId', $UserId = $userRow['Email']);
   PRO_AddToParams('UserCode', $UserCode=$userRow['UserItem']);
   PRO_AddToParams('UserName', $UserName=$userRow['UserFullName']);
   PRO_AddToParams('LastLoginAt', $LastLoginAt=$userRow['LastLoginDate']);
   PRO_AddToParams('WarehouseId', $WarehouseId = $userRow['WarehouseId']);
   PRO_AddToParams('UserAllow', $UserAllow = $userRow['Allow']);
   PRO_AddToParams('DevUser', $DevUser = (array_key_exists('ADMIN',$UserAllow)&&($UserAllow['ADMIN']==1)));
   hda_db::hdadb()->HDA_DB_sayLoggedOn($UserCode); 
   $LoggedIn = true;
   return true;
   }



function HDA_Invite($email, $name, $by_name, $with_msg=NULL) {
   global $HDA_Product_Title;
   if (strlen($email)<2 || strlen($name)==0) return "Please provide a valid email address and user name";
   $pw = _gen_pw();
   $email_msg = "Welcome {$name} to {$HDA_Product_Title}. <br>You have been invited to join by {$by_name}:<br>";
   $email_msg .= "Your Email identity (your Username) is: {$email} <br>";
   $email_msg .= "Your initial password is the following 8 characters: $pw <br>";
   if (!is_null($with_msg) && strlen($with_msg)>0) {
      $email_msg .= "{$by_name} included the following message with the invite<br>{$with_msg}<br>";
      }
   $email_msg .= "You can change your password once you have logged in by selecting <i><u>Your Account</u></i><br>";
   
   $email_msg .= "<br>Thank you for using {$HDA_Product_Title}<br>";
   
   $ok = HDA_SendMail('InviteUser', array('EMAIL'=>$email, 'SUBJECT'=>'Invite', 'MESSAGE'=>$email_msg),$err);
   if ($ok===true) {
      hda_db::hdadb()->HDA_DB_RegisterUser($email, $name, $pw, $allow=NULL);
      HDA_SendSystemMail("New user {$email} {$name} invited by {$by_name}");
	  return null;
      }
   return $err;
   }
/*
function _outQR($item, $ticketid, $size=32) {
   global $code_root, $home_root;
   $t = "";
   $qr_image = "tmp/{$ticketid}";
   if (!@file_exists($qr_image)) @mkdir($qr_image);
   $timestamp=date('YmdGis',time());
   $qr_image .= "/qr-{$timestamp}.png";
   QRcode::png($qr_code="{$item}-{$ticketid}-{$timestamp}",$qr_image);
   $t .= "<span title=\"{$qr_code}\"><img class=\"alc-img-icon\" src=\"{$qr_image}\" height={$size}px ></span>";
   return $t;
   }
 */  
function HDA_UploadAdminFile($up_path, $dir, &$into_file) {
   global $UserCode;
   global $UserName;
   $failure = NULL;
   $n = $up_path;
   $lib_dir = "{$dir}/";
   $into_file = $lib_dir;
   if (!file_exists($lib_dir)) mkdir($lib_dir);
   if (isset($_FILES) && isset($_FILES[$n])) {
      if (strlen($_FILES[$n]['name'])>0) {
         if ((isset($_FILES[$n]['tmp_name'])) && ($_FILES[$n]['tmp_name']<>"")) {
            if ($_FILES[$n]['size']>INIT('MAX_UPLOAD_FILESIZE')) {
               $failure = "<p align=center><b>File size limit for upload is ".INIT('MAX_UPLOAD_FILESIZE')."</b><br>";
               $failure .= "Attempt to upload {$_FILES[$n]['size']} bytes from {$_FILES[$n]['name']}";
               $failure .= "</p>";
               }                     
            else {
               $bin_path = "{$lib_dir}{$_FILES[$n]['name']}";
               if (file_exists($bin_path)) unlink($bin_path);
               if(move_uploaded_file($_FILES[$n]['tmp_name'],$bin_path)) {
                  $into_file = $bin_path;
                  }
               else $failure = "Fails to upload {$bin_path}";
               }
            }
         }
      }
   return $failure;
   }
   
function ALCE_UploadForm($up_path='UploadForm', $partnerid, &$into_file) {
   return HDA_UploadAdminFile($up_path, "../ALCE_PAGES/{$partnerid}", $into_file);
   }
function ALCE_UploadTicket($up_path='UploadTicket', &$into_file) {
   return HDA_UploadAdminFile($up_path, "../ALCE_PAGES/tickets", $into_file);
   }

function HDA_UploadBin($up_path='UploadBin') {
   global $binary_dir;
   return HDA_UploadAdminFile($up_path, $binary_dir, $into_file);
   }
function HDA_UploadTemplate($up_path='UploadTemplate') {
   global $template_dir;
   return HDA_UploadAdminFile($up_path, $template_dir, $into_file);
   }
function HDA_UploadCommon($up_path='UploadCommon') {
   global $common_code_dir;
   return HDA_UploadAdminFile($up_path, $common_code_dir, $into_file);
   }
function HDA_UploadValidations($up_path='UploadValidations', &$into_file) {
   return HDA_UploadAdminFile($up_path, "tmp", $into_file);
   }
function HDA_UploadConnections($up_path='UploadConnections', &$into_file) {
   return HDA_UploadAdminFile($up_path, "tmp", $into_file);
   }
function HDA_UploadUsers($up_path='UploadUsers', &$into_file) {
   return HDA_UploadAdminFile($up_path, "tmp", $into_file);
   }
function HDA_UploadSMS($up_path='UploadSMS', &$into_file) {
   return HDA_UploadAdminFile($up_path, "tmp", $into_file);
   }
function HDA_UploadRedirect($up_path='UploadRedirect', &$into_file) {
   return HDA_UploadAdminFile($up_path, "tmp", $into_file);
   }
function HDA_UploadICS($up_path='UploadICS', &$into_file) {
   return HDA_UploadAdminFile($up_path, "tmp", $into_file);
   }


function HDA_UploadRestore($up_path='UploadRestore', &$into_file) {
   return HDA_UploadAdminFile($up_path, "tmp", $into_file);
   }


function rrmdir($dir) {
   if (is_null($dir) || strlen($dir)==0 || substr($dir, 0, 6) <> 'CUSTOM') return;
   _rrmdir($dir);
   }
function _rrmdir($dir) {
   if (@is_dir($dir)) { 
      $objects = @scandir($dir); 
      foreach ($objects as $object) { 
         if ($object != "." && $object != "..") { 
            if (@filetype($dir."/".$object) == "dir") _rrmdir($dir."/".$object); else @unlink($dir."/".$object); 
            } 
         } 
      @reset($objects); 
      @rmdir($dir); 
      } 
   elseif (@file_exists($dir)) @unlink($dir);
   } 
function _lsdir($dir, $depth=0) {
   $a = array();
   if (@is_dir($dir)) {
      $objects = @scandir($dir); 
      foreach ($objects as $object) { 
         if ($object != "." && $object != "..") { 
            $a = @array_merge($a, _lsdir($dir."/".$object, $depth+1)); 
            } 
         } 
      @reset($objects); 
      if ($depth>0) $a[] = array('Path'=>$dir, 'Size'=>@filesize($dir), 'Modified'=>max(@filemtime($dir),@filectime($dir)));
      }
   elseif (@file_exists($dir)) {
      $a[] = array('Path'=>$dir, 'Size'=>@filesize($dir), 'Modified'=>max(@filemtime($dir),@filectime($dir)));
      }
   return $a;
   }




function HDA_unzip($path, $profile, &$problem, $lib_dir = NULL, $keep_dir=false, $append_to_name="", $as_dirstruct=false) {
   $problem = NULL;
   $zip = zip_open($path);
   $in_pack = array();
   $problem = "";
   if (is_resource($zip)) {
		$problem .= "Opened {$path} ";
      while ($zip_entry = zip_read($zip)) {
        $this_pack=array();
        $this_pack['Title']= zip_entry_name($zip_entry); $this_pack['Size']=zip_entry_filesize($zip_entry);
        $this_pack['EXT'] = strtolower(pathinfo($this_pack['Title'], PATHINFO_EXTENSION));
        $this_pack['Filename'] = pathinfo($this_pack['Title'], PATHINFO_BASENAME);
		$problem .= print_r($this_pack,true);
        if (is_null($lib_dir)) {
           $lib_dir = "tmp/";
           $lib_dir .= $profile;
           }
        if (!file_exists($lib_dir)) mkdir($lib_dir, 0777, true);
		$dst_dir = $lib_dir;
        if (!is_null($this_pack['Filename']) && strlen($this_pack['Filename'])>0) { // && strlen($this_pack['EXT'])>0) {
		   if ($keep_dir) {
		      $as_dir = pathinfo($this_pack['Title'], PATHINFO_DIRNAME);
			  $this_pack['DirName'] = $as_dir;
			  $as_dir = str_replace(array('/','\\','.'),"_",$as_dir);
			  $this_pack['Filename'] = "{$append_to_name}_{$as_dir}_{$this_pack['Filename']}";
		      }
		   elseif ($as_dirstruct) {
		      $as_dir = pathinfo($this_pack['Title'], PATHINFO_DIRNAME);
			  $this_pack['DirName'] = $as_dir;
			  if (!file_exists($dst_dir = "{$lib_dir}/{$as_dir}")) @mkdir($dst_dir, 0777, true);
		      }
           $this_pack['Path'] = "{$dst_dir}/{$this_pack['Filename']}";
           if ((strlen($this_pack['Filename'])>0) && ($this_pack['Size']>0)) {
		      $fh = fopen($this_pack['Path'],'w');
			  while ($s = zip_entry_read($zip_entry, 32000)) {
                 fputs($fh, $s);
                 }
              fclose($fh);				 
              $this_pack['Result']= $this_pack['Size']; //@file_put_contents($this_pack['Path'], zip_entry_read($zip_entry, $this_pack['Size']));
              _chmod($this_pack['Path']);
              }
           else if (strlen($this_pack['Filename'])>0 && !$as_dirstruct) {
              @file_put_contents($this_pack['Path'], '');
              $this_pack['Result'] = null;
              }
           $in_pack[] = $this_pack;
		   $problem .= "Unpacked ".print_r($in_pack,true);
		   }
        zip_entry_close($zip_entry);
		$problem .= " Closed Entry ";
        }
		$problem .= " Closing Zip ";
		zip_close($zip);
      }
   else $problem = "Fails to open zip file error {$zip} ".zipFileErrMsg($zip) ;
   $problem = null;
   return $in_pack;
   }

function HDA_zip($path, $files, &$problem) {
   $problem = null;
   $zip = new ZipArchive();
   if ($zip->open($path, ZIPARCHIVE::CREATE)!==true) {
      $problem = "Unable to create a zip archive";
      }
   else {
		foreach ($files as $file) {
			if (file_exists($file)) {
				$pathinfo = pathinfo($file);
				$zip->addFile($file, $pathinfo['basename']);
				if ($zip===false) {
					$problem = "Fails to add file {$file} to zip {$p[0]}";
					return false;
					}
				}
			}
		}
	$zip->close();
   return (is_null($problem));
   }


function _cpp( $source, $destination ) { 
   if ( is_dir( $source ) ) { 
      if (!file_exists($destination)) @mkdir( $destination ); _chmod($destination);
      $directory = dir( $source ); 
      while ( FALSE !== ( $readdirectory = $directory->read() ) ) { 
         if ( $readdirectory == '.' || $readdirectory == '..' ) { continue; } 
         $PathDir = $source . '/' . $readdirectory; 
         if ( is_dir( $PathDir ) ) { 
            _cpp( $PathDir, $destination . '/' . $readdirectory ); 
            continue; 
            } 
         copy( $PathDir, $destination . '/' . $readdirectory ); 
         } 
      $directory->close(); 
      }
   else { 
      copy( $source, $destination."/".pathinfo($source,PATHINFO_BASENAME) ); 
      } 
   } 

function HDA_zipDirectory($zipArchive, $dir, &$problem) {
   $problem = "";
   $zip = new ZipArchive();
   if ($zip->open($zipArchive, ZIPARCHIVE::CREATE)!==true) {
          $problem .= " -- Unable to create a zip archive {$zipArchive} ";
	  return false;
      }
   else {       
      addFolderToZip("{$dir}", $zip, $zipdir = "{$dir}");
      }
   $zip->close();
   return true;
   }

function addFolderToZip($root, $zipArchive, $zipdir='', $include_dir = true) {
   if (is_dir($root)) {
      $ff = glob("{$root}/*");
      foreach ($ff as $f) {
         if (is_dir($f)) {
            if ($f !=='.' && $f !=='..') {
               if (!empty($zipdir)) $zipArchive->addEmptyDir($zipdir);
               $path = (empty($zipdir))?"":"{$zipdir}/";
               if ($include_dir) addFolderToZip($f, $zipArchive, $path.pathinfo($f,PATHINFO_FILENAME));
               }
            }
         else {
            $path = (empty($zipdir))?"":"{$zipdir}/";
            $zipArchive->addFile($f, $path.pathinfo($f,PATHINFO_BASENAME));
            } 
         }
      }
   elseif (is_file($root)) $zipArchive->addFile($root, $zipdir.$root);
}

function zipFileErrMsg($errno) {
   // using constant name as a string to make this function PHP4 compatible
   $zipFileFunctionsErrors = array(
      'ZIPARCHIVE::ER_MULTIDISK' => 'Multi-disk zip archives not supported.',
      'ZIPARCHIVE::ER_RENAME' => 'Renaming temporary file failed.',
      'ZIPARCHIVE::ER_CLOSE' => 'Closing zip archive failed',
      'ZIPARCHIVE::ER_SEEK' => 'Seek error',
      'ZIPARCHIVE::ER_READ' => 'Read error',
      'ZIPARCHIVE::ER_WRITE' => 'Write error',
      'ZIPARCHIVE::ER_CRC' => 'CRC error',
      'ZIPARCHIVE::ER_ZIPCLOSED' => 'Containing zip archive was closed',
      'ZIPARCHIVE::ER_NOENT' => 'No such file.',
      'ZIPARCHIVE::ER_EXISTS' => 'File already exists',
      'ZIPARCHIVE::ER_OPEN' => 'Can\'t open file',
      'ZIPARCHIVE::ER_TMPOPEN' => 'Failure to create temporary file.',
      'ZIPARCHIVE::ER_ZLIB' => 'Zlib error',
      'ZIPARCHIVE::ER_MEMORY' => 'Memory allocation failure',
      'ZIPARCHIVE::ER_CHANGED' => 'Entry has been changed',
      'ZIPARCHIVE::ER_COMPNOTSUPP' => 'Compression method not supported.',
      'ZIPARCHIVE::ER_EOF' => 'Premature EOF',
      'ZIPARCHIVE::ER_INVAL' => 'Invalid argument',
      'ZIPARCHIVE::ER_NOZIP' => 'Not a zip archive',
      'ZIPARCHIVE::ER_INTERNAL' => 'Internal error',
      'ZIPARCHIVE::ER_INCONS' => 'Zip archive inconsistent',
      'ZIPARCHIVE::ER_REMOVE' => 'Can\'t remove file',
      'ZIPARCHIVE::ER_DELETED' => 'Entry has been deleted',
      );
   $errmsg = 'unknown';
   foreach ($zipFileFunctionsErrors as $constName => $errorMessage) {
      if (defined($constName) and constant($constName) === $errno) {
         return 'Zip File Function error: '.$errorMessage;
         }
      }
   return 'Zip File Function error: unknown';
   }

function HDA_isUnique($code) {
   return uniqid($code);
   }

   
?>