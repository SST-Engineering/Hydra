<?php




function HDA_detectLOOKUP($lookup_look, $filename_pattern=null, $this_task, &$profile) {
   $detected = false;
   if (is_null($lookup_look) || strlen($lookup_look)==0) return false;
   $a = hda_db::hdadb()->HDA_DB_dictionary(NULL, $lookup_look);
   if (!is_null($a) && is_array($a) && count($a)==1) {
      $def = $a[0]['Definition'];
      switch ($def['Connect Type']) {
         case 'FTP':
            $ftp_look = array();
			$ftp_look['dictionary'] = $lookup_look;
			$ftp_look['filename_pattern'] = $filename_pattern;
            $detected = HDA_detectFTP($ftp_look, $this_task, $profile);
            break;
         case 'FILE':
            $file_look = array();
            $file_look['enabled']=$def['enabled'];
            $file_look['directory'] = $def['Table'];
            $file_look['filename']= (!is_null($filename_pattern))?$filename_pattern:$def['Key'];
            $file_look['cleanup'] = $def['Cleanup'];
            $detected = HDA_detectFILE($file_look, $this_task, $profile);
            break;
         case 'XML':
            $xml_look = array();
            $xml_look['enabled']=$def['enabled'];
            $xml_look['url'] = $def['Host'];
            $file_look['cleanup'] = $def['Cleanup'];
            $detected = HDA_detectXML($xml_look, $this_task, $profile);
            break;
         case 'RSS':
            $rss_look = array();
            $rss_look['enabled']=$def['enabled'];
            $rss_look['url'] = $def['Host'];
            $detected = HDA_detectRSS($rss_look, $this_task, $profile);
            break;
         }
      }
   return $detected;
   }


function HDA_detectFTP($ftp_look, $this_task, &$profile) {
   $detected = false;
   if (is_null($ftp_look) || !is_array($ftp_look)) return false;
   $ftp = new HDA_FTP();
   $e = $ftp->lookupDictionary($ftp_look['dictionary']);
   if ($e===false)  { HDA_SendErrorMail("Failed FTP connect, {$ftp->last_error} for profile {$profile['Title']}"); return false; }
   $e = $ftp->open();
   if ($e===false)  { HDA_SendErrorMail("Failed FTP connect, {$ftp->last_error} for profile {$profile['Title']}"); return false; }
   $e = $ftp->to_dst_dir();
   if ($e===false)  { HDA_SendErrorMail("Failed FTP connect, {$ftp->last_error} for profile {$profile['Title']}"); $ftp->close(); return false; }
   $s = $ftp->nlist();
   if ($s===false)  { HDA_SendErrorMail("Failed FTP connect, {$ftp->last_error} for profile {$profile['Title']}"); $ftp->close(); return false; }
   $file_mask = (is_null($ftp_look['filename_pattern']))?$ftp->ftp_filename:$ftp_look['filename_pattern'];
   if (is_null($file_mask) || strlen($file_mask)==0 || $file_mask=='*') $file_mask = "*\.*"; else $file_mask = str_replace('.','\.',$file_mask);
   $file_mask = str_replace('*', '[.]*', $file_mask);
   $ftp->ftp_mode = FTP_BINARY;
   
   $detected = array();
   foreach ($s as $f) {
      if ($f=='.' || $f=='..') continue;
      if (!preg_match("/{$file_mask}/",$f)) { continue;}
      $ft = $ftp->get_datetime($f);
      $path_info = pathinfo($f);
	  $this_ref = HDA_isUnique('UP');
      $path = HDA_TargetForFile($profile['ItemId'], $this_ref, (array_key_exists('extension',$path_info))?$path_info['extension']:"");
      $source_info = $f;
      $effective_date = hda_db::hdadb()->PRO_DB_DateTime($ft);

      $e = $ftp->read_file($path, $remote_path = "{$f}");
	  if ($e===false) {
         HDA_SendErrorMail("Failed FTP get file to {$path} {$ftp->last_error} for profile {$profile['Title']}"); 
         }
      else {
		 $detected[] = array('FileName'=>$path_info['basename'],'FilePath'=>$path,'EffectiveDate'=>$effective_date);
         }
      }
   $ftp->close();
   return $detected;
   }
function HDA_detectXML($xml_look, $this_task, &$profile) {
   if (is_null($xml_look) || !is_array($xml_look)) return false;
   if (array_key_exists('enabled',$xml_look) && $xml_look['enabled']<>1) return false;
   $xml = file_get_contents($xml_look['url']);
   
   return false;
   }
function HDA_detectRSS($rss_look, $this_task, &$profile) {
   if (is_null($rss_look) || !is_array($rss_look)) return false;
   if (array_key_exists('enabled',$rss_look) && $rss_look['enabled']<>1) return false;
   return false;
   }
function HDA_detectFILE($file_look, $this_task, &$profile) {
   global $UserCode;
   if (is_null($file_look) || !is_array($file_look)) return false;
   if (array_key_exists('enabled',$file_look) && $file_look['enabled']<>1) return false;
   if (!file_exists($file_look['directory'])) return false;
   $file_mask = $file_look['filename'];
   if (is_null($file_mask) || strlen($file_mask)==0 || $file_mask=='*') $file_mask = "*\.*"; else $file_mask = str_replace('.','\.',$file_mask);
   $file_mask = str_replace('*', '[\s\S]*?', $file_mask);
   $ff = glob("{$file_look['directory']}/*.*");
   if (is_null($ff) || !is_array($ff) || count($ff)==0) return false;

   $detected = array();
   foreach ($ff as $f) {
      if (!preg_match("@{$file_mask}@i", $f)) continue;
      $ft = max(filemtime($f),filectime($f));
      $path_info = pathinfo($f);
	  $this_ref = HDA_isUnique('UP');
      $path = HDA_TargetForFile($profile['ItemId'], $this_ref, (array_key_exists('extension',$path_info))?$path_info['extension']:"");
      $source_info = $path_info['filename'];
      $effective_date = hda_db::hdadb()->PRO_DB_DateTime($ft);
      if (copy($f, $path)) {
         if ($file_look['cleanup']==1) @unlink($f);
         $detected[] = array('FileName'=>$path_info['basename'], 'FilePath'=> $path);
		 }
      }
   return $detected;
   }
   
function HDA_TempDirectory($item_id) {
   $path = "tmp/{$item_id}";
   if (!file_exists($path)) mkdir($path);
   return $path;
   }   

function HDA_WorkingDirectory($item_id) {
   $path = "CUSTOM/{$item_id}";
   if (!file_exists($path)) mkdir($path);
   return $path;
   }

function HDA_TargetForFile($item_id, &$file_code, $ext='any') {
   $path = HDA_WorkingDirectory($item_id)."/FilesToProcess";
   if (!file_exists($path)) @mkdir($path); _chmod($path);
   if (is_null($file_code)) $file_code = HDA_isUnique('UP');
   return "{$path}/{$file_code}.{$ext}";
   }
   
function HDA_ProcessedFile($item_id, $from_path, $success=true) {
   if (is_null($from_path)) return true;
   $did_move = false;
   $moved = "Fails moving file {$from_path}";
   $path = null;
   if (file_exists($from_path)) {
      $path = HDA_WorkingDirectory($item_id);
      $path .= ($success)?"/Processed":"/FailedToProcess";
      if (!file_exists($path)) { @mkdir($path); _chmod($path); }
      $path .= "/".pathinfo($from_path, PATHINFO_BASENAME);
	  clearstatcache();
	  set_time_limit(0);
      sleep(1); // windows issue, due to order of closing handles, see PHP manual page for rename
      $did_move = @rename($from_path, $path);
	  if ($did_move === false) {
	     $moved .= " - rename failed to {$path}";
	     $did_move = @copy($from_path, $path);
		 if ($did_move === true) @unlink($from_path);
		 else $moved .= " - copy failed ";
		 }
      }
   else $did_move = true;
   if (!is_null($from_path)) {
      _rrmdir(pathinfo($from_path,PATHINFO_DIRNAME)."/tmp");
      }
   return ($did_move===false)?$moved:true;
   }

   
function HDA_ProcessPendingResponse(&$this_process, $msg, $success, $proxy=false) {
   global $Logging_Sources;
   if (!$proxy) {
      $logn = "Completed processing ";
      $logn .= ($success)?" with success ":" with error ";
      hda_db::hdadb()->HDA_DB_issueNote($this_process['ProfileItem'], $logn, $tagged='TAG_INFO');
      $logn .= $this_process['Title'];
      HDA_LogThis($logn, $this_process['Source']);
	  $email_style = 'ALCNotice';
	  switch ($this_process['Source']) {
	     case 'EMAIL':
		    $email_style = 'ALCReply';
		 case 'TICKET':
            $msg = _textToHTML($msg);
            $msgn = ($success)?"Success":"Error";
            $msgn .= " in processing background request issued on ".hda_db::hdadb()->PRO_DBdate_Styledate($this_process['IssuedDate'],true)."<br/>{$msg}";
            $msgn .= "<br/>Uploaded or Detected file reference {$this_process['RcvFile']} via {$Logging_Sources[$this_process['Source']]['Caption']}";
            HDA_EmailResponse($this_process['Title'], array('System_JobQ','Emailed Job Response'), $msgn, $email_style);
			break;
		 }      
      if ($success) hda_db::hdadb()->HDA_DB_timings($this_process['ProfileItem'], $this_process['EndTime']-$this_process['StartTime'], null);
	  }
   return true;
   }
function HDA_ProcessPending($row) {
   $in_q = $row['QueueLevel'];
   $proxy = ($in_q==10);
   $success = false;
   $process_item_id = $row['ProfileItem'];
   hda_db::hdadb()->HDA_DB_ClearSuccess($process_item_id);
   hda_db::hdadb()->HDA_DB_RunPending($row['ItemId']);
   $row['StartTime']=time();
   $temp_file = $work_file = $row['RcvFile'];
   $orig_filename = $row['RcvFileName'];
   $working_file = null;
   $this_session = session_id();
   if (!$proxy) HDA_LogThis("Start processing of {$row['Title']} ref {$row['ItemId']} State {$row['ProcessState']} sess {$this_session}", $row['Source']);
   $the_log = "Process start ".hda_db::hdadb()->PRO_DBtime_Styledate(time(),true)."\n";
   
   if ((!is_null($temp_file))&&(strlen($temp_file)>0)) {
      $working_file = HDA_ProcessRcvFile($the_log,  $row);
      if (is_null($working_file)) {
		 if (!$proxy) HDA_LogThis("Fails process {$row['Title']} ref {$row['ItemId']} State {$row['ProcessState']} sess {$this_session}", $row['Source']);
         $the_log .= "Fails to process check the received file on path {$temp_file}\n";
         $msg = "<br><span style=\"color:red;\"><b>Process failed at stage 1 - validating the input file";
         $msg .= "</b></span><br>";
         $msg .= _textToHTML($the_log)."<br>";
         HDA_ProcessedFile($process_item_id, $temp_file, false);
         HDA_OnError('PROCESS', $the_log, $row);
         hda_db::hdadb()->HDA_DB_TaskComplete($row['ItemId'], $success);
         HDA_ProcessPendingResponse($row, $msg, $success, $proxy);
         return true;
         }
      hda_db::hdadb()->HDA_DB_updateRcvFile($row['ItemId'], $working_file);
      }
   if (!$proxy) HDA_LogThis("Running {$row['Title']} ref {$row['ItemId']} State {$row['ProcessState']} sess {$this_session}", $row['Source']);
   $exit_code = HDA_CustomCode($the_log, $row);
   if (!$proxy) HDA_LogThis("Ends code Execute {$row['Title']} ref {$row['ItemId']} {$exit_code} State {$row['ProcessState']} sess {$this_session}", $row['Source']);
   hda_db::hdadb()->reconnect_sql();
   if (strlen($the_log)>40048) $the_log = substr($the_log, 0, 40048)." ... truncated";
   if (($exit_code === 0 && $exit_code !== false) || ($exit_code===true)) {
   }
   else {
      $msg = "<br><span style=\"color:red;\"><b>Process failed - CUSTOM ALCODE failed - with exit code {$exit_code}";
      $msg .= "</b></span><br>";
      $msg .= _textToHTML($the_log)."<br>";
      HDA_ProcessedFile($process_item_id, $working_file, false);
      HDA_OnError('ALCODE', $the_log, $row);
      hda_db::hdadb()->HDA_DB_TaskComplete($row['ItemId'], $success);
      HDA_ProcessPendingResponse($row, $msg, $success, $proxy);
      return true;
      }
   if (($ok=HDA_ProcessedFile($process_item_id, $temp_file, $success=true)) !== true) {
      $the_log .= "Process complete but fails to move data to Processed directory {$ok}\n";
      }

   $the_log .= "Finished background processing\n";
   hda_db::hdadb()->HDA_DB_TaskComplete($row['ItemId'], $success=true);
   $row['EndTime'] = time();
   HDA_ProcessPendingResponse($row, "Process Complete\n{$the_log}", $success, $proxy);
   if (!$proxy) HDA_LogThis("Ends ProcessPending {$row['Title']} ref {$row['ItemId']} sess {$this_session}", $row['Source']);
   return true;
   }


function HDA_ProcessRcvFile(&$the_log,  &$this_process) {
   $path = $this_process['RcvFile'];
   $the_log .= "Reading file: {$path}\n";
   if (file_exists($path)) {
      $the_log .= "File {$path} found ok\n";
      }
   else { $the_log .= "File {$path} not found\n"; return null; }
   
   $this_type = strtoupper(pathinfo($path,PATHINFO_EXTENSION));
   switch ($this_type) {
      case 'GZ':
	     $pathinfo = pathinfo($path);
	     $fh = fopen($topath="{$pathinfo['dirname']}/{$pathinfo['filename']}",'w');
	     $a = gzfile($path);
		 foreach($a as $line) {
		    fwrite($fh, "{$line}\n");
			}
		 fclose($fh);
		 $the_log .= "Decompressed file to {$topath}\n";
		 return $topath;
		 break;
      case 'ZIP':   
		  $the_pack = HDA_unzip($path, $this_process['ItemId'], $problem, pathinfo($path,PATHINFO_DIRNAME)."/tmp", true);
		  if (is_null($the_pack) || !is_array($the_pack) || count($the_pack)==0) {
			 $the_log .= "Unable to obtain upload file from zip: {$problem}\n";
			 return null;
			 }
		  else if ((count($the_pack)<>1)&&(INIT('MULTIPLE_UPLOADS')=='ZIP')) {
			  return $path;
		  }
		  else if (count($the_pack)<>1) {
			 $the_log .= "The zip file contains more than one upload candidate, can only process single files for this profile\n";
			 return null;
			 }
		  $the_log .= "Unzipped uploaded file ok, with one file type of {$the_pack[0]['EXT']} in file {$the_pack[0]['Title']}\n";
		  return $the_pack[0]['Path'];
      }     
   return $path;
   }
   
function make_version_of($path) {
  if (file_exists($path)) {
	 $exists_count = glob($path);
	 $path = pathinfo($path,PATHINFO_DIRNAME)."/".pathinfo($path,PATHINFO_FILENAME)."[".count($exists_count)."].".pathinfo($path,PATHINFO_EXTENSION);
	 }
   return $path;
   }

function HDA_DataFilesToQ($profile_item, $uploadedPath, $source, &$problem, $comment="", $user=NULL, $effective_date=NULL, $original_path = NULL) {
   global $UserCode;
   if (is_null($user)) $user = $UserCode;
   if (is_null($effective_date)) $effective_date = hda_db::hdadb()->PRO_DB_dateNow();
   $problem = "";
   $all_success = true;
   $pathinfo = pathinfo($uploadedPath);
   if (!array_key_exists('extension',$pathinfo)) $pathinfo['extension']="";
   $the_pack = array();
   switch (strtoupper($pathinfo['extension'])) {
      case 'GZ':
	     $fh = fopen($topath="{$pathinfo['dirname']}/{$pathinfo['filename']}",'w');
	     $a = gzfile($uploadedPath);
		 foreach($a as $line) {
		    fwrite($fh, "{$line}\n");
			}
		 fclose($fh);
		 $upathinfo = pathinfo($pathinfo['filename']);
         if (!array_key_exists('extension',$upathinfo)) $upathinfo['extension']="";
		 $the_pack = array(array('EXT'=>$upathinfo['extension'], 
		                         'Filename'=>$upathinfo['basename'], 
								 'Path'=>$topath,
								 'Comment'=>$comment));
		 break;
      case 'ZIP':
         $the_pack = HDA_unzip($uploadedPath, $profile_item, $problem, null, false, false);
         if (is_null($the_pack) || !is_array($the_pack) || count($the_pack)==0) {
            $problem .= "Unable to obtain upload file from zip: {$problem}";
			$all_success = false;
            }
         else {
            for ($i=0; $i<count($the_pack); $i++) {
			   $the_pack[$i]['UploadedCode'] = HDA_isUnique('UP');
			   $the_pack[$i]['Comment'] = $comment;
			   }
            }
		 if ((count($the_pack)==1)||(INIT('MULTIPLE_UPLOADS')!='ZIP')) break;
      default:
         $the_pack = array(array('EXT'=>$pathinfo['extension'],
							  'Filename'=>$pathinfo['basename'],
							  'Path'=>$uploadedPath,
							  'UploadedCode'=>HDA_isUnique('UP'),
							  'Comment'=>$comment));
         break;
      }
   foreach ($the_pack as $in_pack) {
      $path = HDA_TargetForFile($profile_item, $in_pack['UploadedCode'], $in_pack['EXT']);
	  $path = make_version_of($path);
      if (!copy($in_pack['Path'], $path)) {
         $problem .= "Failed to copy {$in_pack['Path']} to pending process Q {$path}";
		 $all_success = false;
         }
      else {
		if (!is_null($code = hda_db::hdadb()->HDA_DB_addRunQ(
					$in_pack['UploadedCode'], 
					$profile_item,
					$user,					
					$path, 
					$in_pack['Filename'], 
					$source,
					$source_info = $original_path,
					$effective_date))) {
				// HDA_ReportUpload($profile_item, $code);
				$problem = "Registered Upload of {$in_pack['Filename']} Ok";
				$note = "Added upload of {$in_pack['Filename']} to pending process queue\n{$in_pack['Comment']}";
				hda_db::hdadb()->HDA_DB_issueNote($profile_item, $note, 'TAG_PROGRESS');
				HDA_LogThis(hda_db::hdadb()->HDA_DB_TitleOf($profile_item)." {$note}");
				}
        else { $all_success = false; $problem = "Fails to add to pending Q"; }
        }
      }
   return $all_success;
   }

   
 function HDA_CustomCode(&$the_log, &$this_process, $runFile='alcode.alc', $and_run=1) {
   $ok = true;
   $loc_path = HDA_WorkingDirectory($this_process['ProfileItem']);
   $ff = glob("{$loc_path}/{$runFile}");
   if (isset($ff) && is_array($ff) && count($ff)==1) {
	  $profile = hda_db::hdadb()->HDA_DB_ReadProfile($this_process['ProfileItem']);
	  $params_list = $profile['Params'];
      $_copy_params = $params_list;
      $exit_code = HDA_CompilerExecute($this_process, $loc_path, $runFile, $params_list, $and_run, $the_log);

      if (($exit_code === 0 && $exit_code !== false) || ($exit_code===true)) {
         $the_log .= "Executed CUSTOM CODE {$ff[0]} with exit code SUCCESS\n";
		 if (!is_null($params_list) && is_array($params_list)) foreach($params_list as $k=>$v) $_copy_params[$k]=$v;
         hda_db::hdadb()->HDA_DB_UpdateProfile($this_process['ProfileItem'], array('Params'=>$_copy_params));
		 if ((($and_run&1)==1) && ($exit_code!==0)) hda_db::hdadb()->HDA_DB_EventSuccess($this_process['ProfileItem']);
         }
      else {
         $the_log .= "FAILED to Execute CUSTOM CODE {$ff[0]} with exit status {$ok}\n";
		 if (($and_run&1)==1) hda_db::hdadb()->HDA_DB_ClearSuccess($this_process['ProfileItem']);
         }
      }
   else $the_log .= "Fails to find {$runFile} in the custom package at {$loc_path}\n";

   return (($exit_code === 0 && $exit_code !== false) || ($exit_code===true));
   }

function HDA_OnError($error_code, &$the_log, &$this_process, $run_file='alcode.err', $and_run=1) {
   }
   
function _expectDataFor($item, &$log) {
   if (!is_array($log)) $log = array();
   if (!hda_db::hdadb()->HDA_DB_relationEnabled($item)) { return true; }
   $datadays = hda_db::hdadb()->HDA_DB_relationDataDays($item);
   $today = date('N');
   if (($datadays & (1<<$today))<>0) {
      $log[] = hda_db::hdadb()->HDA_DB_TitleOf($item). " Expect data today ".date('l');
      return true; // expect data today
	  }
   $log[] = hda_db::hdadb()->HDA_DB_TitleOf($item)." Data not required today ".date('l');
   return false;
   }
   
function _adjustRunDateForDataDays(&$run_date, $datadays = 0x3f, &$log) {
   if (is_null($run_date)) return $run_date;
   $today = date('N');
   if (($datadays & (1<<$today))<>0) {
      if (!is_null($log)) $log[] = "Expect data today ".date('l');
      return false; // expect data today
	  }
   $last_day = date('N',strtotime("{$run_date} + 1 day"));
   $from = min($today, $last_day);
   $to = max($today, $last_day);
   for ($i=$from; $i<$to; $i++) if (($datadays & (1<<$i))<>0) {
      if (!is_null($log)) $log[] = "Expected data between last run on ".date('l',strtotime($run_date))." and today";
	  return false; // expected data between last run and today
	  }
   // otherwise adjust run date to today
   $tod = date('G:i',strtotime($run_date));
   $run_date = date("Y-m-d {$tod}");
   if (!is_null($log)) $log[] = "Data not expected today, Adjusted effective last run date to {$run_date}";
   return true;
   }
   
function _passesRules($run_date, $rule, $default=NULL, $datadays = 0x3f, &$log=NULL) {
   $passed = false;
   if (is_null($datadays)) $datadays = 0x3f;
   _adjustRunDateForDataDays($run_date, $datadays, $log);
   $run_time = (is_null($run_date))?0:strtotime($run_date);
   switch ($rule) {
      default:
	     if (is_numeric($rule)) $passed = ($run_time>strtotime("{$rule} days ago")); 
		 if ($passed && !is_null($log)) $log[] = "Passed run time rule, last run date {$run_date} later than {$rule} days ago";
		 break;
      case 'T': 
	     $passed = ($run_time>strtotime('today')); 
		 if ($passed && !is_null($log)) $log[] = "Passed run time rule, last run date {$run_date} is today";
	     break;
	  case 'Y': 
	     $passed = ($run_time>strtotime('yesterday')); 
		 if ($passed && !is_null($log)) $log[] = "Passed run time rule, last run date {$run_date} is later than yesterday";
		 break;
	  case 'M': 
	     $passed = (date('n',$run_time) == date('n')); 
		 if ($passed && !is_null($log)) $log[] = "Passed run time rule, last run date {$run_date} is this month";
		 break;
	  case 'W': 
	     $passed = ($run_time>strtotime('last Sunday')); 
		 if ($passed && !is_null($log)) $log[] = "Passed run time rule, last run date {$run_date} is this week";
		 break;
	  case 'P': 
	     $passed = true; 
		 if ($passed && !is_null($log)) $log[] = "Passed run time rule, last run date {$run_date} is ok with anytime rule";
		 break;
	  }
   if ($passed===true) return true;
   if (!is_null($default)&&($default<>'N')) {
      $DTod = explode(':', $default);
	  $days_ago = $DTod[0];
	  $tod_hr = 0; $tod_min = 0;
	  if (count($DTod)>1) $tod_hr = $DTod[1];
	  if (count($DTod)>2) $tod_min = $DTod[2];
	  if (($days_ago>0) && ($run_time<strtotime("{$days_ago} days ago"))) {
	     if (!is_null($log)) $log[] = "Default pass fails, last run date {$run_date} less than {$days_ago} days ago";
	     $passed = false;
		 }
	  elseif (strtotime($wait_until = sprintf('%02d:%02d',$tod_hr,$tod_min))>time()) {
	     if (!is_null($log)) $log[] = "Default pass fails, current time ".date('G:i')." earlier than wait until {$wait_until}";
	     $passed = false;
		 }
	  else {
	     if (!is_null($log)) $log[] = "Will issue a Default pass, last run date {$run_date} more than {$days_ago} days ago or now later than {$wait_until}";
	     $passed = null;
		 }
      }
   return $passed;
   }
function _rulesWarningDue($run_date, $default) {
   $due = false;
   $run_time = (is_null($run_date))?0:strtotime($run_date);
   if (!is_null($default)&&($default<>'N')) {
      $DTod = explode(':', $default);
	  $days_ago = $DTod[0];
	  if ($days_ago>0) $days_ago++;
	  $tod_hr = 0; $tod_min = 0;
	  if (count($DTod)>1) $tod_hr = $DTod[1];
	  if (count($DTod)>2) $tod_min = $DTod[2];
	  $tod_min -=30; //  half hour warning
	  if ($tod_min<0) {$tod_hr -= 1; $tod_min = (60+$tod_min); }
	  if ($tod_hr<0) {$tod_hr=(24+$tod_hr); $days_ago++; }
	  if (($days_ago>0) && ($run_time<strtotime("{$days_ago} days ago"))) $due = true;
	  else {
	     $warn_start_time = strtotime(sprintf('%02d:%02d',$tod_hr,$tod_min));
	     if (($run_time<$warn_start_time) && (time()>$warn_start_time)) $due = true;
		 }
      }
   return $due;
   }

function HDA_CheckRules($item, &$log) {
   $log = array();
   $pass = 'FAIL';
   if (!hda_db::hdadb()->HDA_DB_relationEnabled($item)) { $log[] = "Not enabled for rules"; return $pass; }
   if (!($is_proxy = hda_db::hdadb()->HDA_DB_relationIsProxy($item))) {
      $last_failure = hda_db::hdadb()->HDA_DB_HasFailureEvent($item, $tries);
      if ($last_failure !== false) {
	     $log[] = "Has a current Failure event on ".hda_db::hdadb()->PRO_DBdate_Styledate($last_failure,true)." after {$tries} retries";
         $fail_rule = hda_db::hdadb()->HDA_DB_relationFail($item);
	     if ($fail_rule == 'N') { $log[] = "Previous try failed, rule says no retry"; return $pass; }
	     $fail_rule_wait = $fail_rule & 0xff;
	     $fail_rule_retry = $fail_rule >> 8;
	     if (((time()-strtotime($last_failure))/(60*60)) < $fail_rule_wait) { $log[] = "Too early for retry, rule says wait {$fail_rule_wait} hours"; return $pass;}
	     if ($tries>$fail_rule_retry) { $log[] = "Too many retries, rule says max retries {$fail_rule_retry} every {$fail_rule_wait} hours"; return $pass; }
         }
	  }
   else $log[] = "Marked as proxy";
   $last_event_date = hda_db::hdadb()->HDA_DB_SuccessEventDate($item);
   $log[] = "Last Success event ".((!is_null($last_event_date))?hda_db::hdadb()->PRO_DBdate_Styledate($last_event_date,true):"None");
   $rule = hda_db::hdadb()->HDA_DB_relationRule($item);
   $default = hda_db::hdadb()->HDA_DB_relationDefault($item);
   $datadays = hda_db::hdadb()->HDA_DB_relationDataDays($item);
   if (_adjustRunDateForDataDays($last_event_date, $datadays, $log)) $log[] = "No data expected, Adjusted effective run date to {$last_event_date}, ";
   $passed = _passesRules($last_event_date, $rule, $default, $datadays,  $log);
   if ($passed===true) { $log[] = "Already passed trigger rule"; $pass = 'PASS'; }
   if (is_null($passed)) {
      $pass = 'WAIT';
      $count = hda_db::hdadb()->HDA_DB_CheckForLateEvent($item);
	  if ($count===false) $log[] = "No Late event issued";
	  switch ($count) {
	     case 1: $log[] = "Data Late, warnings due"; break;
		 case 2: $log[] = "Data Too Late Now"; break;
		 default: $log[] = "Data Late, marker count {$count}"; break;
		 }
	  }
   if (_rulesWarningDue($last_event_date,$default)) {
      $log[] = "Late Warnings due or sent";
      }
   if (hda_db::hdadb()->HDA_DB_willCollect($item)) $log[] = "Waiting Auto Collect";
   
   $events = hda_db::hdadb()->HDA_DB_AllSuccessEvents();
   $children = hda_db::hdadb()->HDA_DB_childrenOf($item, $cat=NULL, $events);
   if (is_null($children) && !$is_proxy) { $log[] = "Top level, no dependants,  or not proxy, so auto trigger not required"; return $pass; }
   $all_ready = true;
   $wait_for = "";
   if (!is_null($children)) foreach($children as $child) {
      if (($child['TActive']&1)==1) {
	     $passed = _passesRules(hda_db::hdadb()->HDA_DB_SuccessEventDate($child['ItemId']), $child['Rule'], $child['OnDefault'], $child['DataDays']);
		 if ($passed===false) $wait_for .= "{$child['Title']}; ";
	     $all_ready &= ($passed || is_null($passed));
		 }
      }
   if (!$all_ready) { $log[] = "Waiting for dependants: {$wait_for} to become ready"; $pass='FAIL';}
   $is_running = hda_db::hdadb()->HDA_DB_inPendingQ($item);
   if ($is_running!==false) { $log = "Not runnable, already in Q {$is_running}"; $pass='PASS'; }
   return $pass;
   }
   
   


?>