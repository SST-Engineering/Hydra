<?php
include_once __DIR__."/../HDAWCodeRoot/HDA_Initials.php";
include_once __DIR__."/../HDAWCodeRoot/HDA_Finals.php";
_hydra_("HDA_cron", 
	$loads=array('XML','DB','FTP','Functions','Process','ManageProfiles','CodeCompiler','CodeLibrary','Validate','Email','Logging','Soap','Graphics'),
	$classes=array('tcpdf','PDF_PARSER','FILE2TEXT','HTML2PDF','HTML_PARSER','Mail','Excel18','EasyXL','CHART'));


class HDA_CRON_Exception extends Exception {}

$t = "";

$msg = array();
$msg[] = array(false, " ++ CRON on ".hda_db::hdadb()->PRO_DBtime_Styledate($ran_job_time = time(), true)."\n");

$UserCode = 0;
$UserName = "cron";

$is_startup = array_key_exists("OnStart", $_GET);
if ($is_startup) {
   $msg[] = array(false, " ++ CRON START UP REQUEST\n");
   if (!HDA_CheckQs($error)) {
      $msg[] = array(false, hda_db::hdadb()->HDA_DB_cronUpdate(" ++ STARTING JobQ Error {$error}"));
	  }
   if (!HDA_CheckJobs($error)) {
      $msg[] = array(false, hda_db::hdadb()->HDA_DB_cronUpdate(" ++ STARTING Job Stall Error {$error}"));
	  }
 //  hda_db::hdadb()->HDA_DB_DropAllLocks();
 //  hda_db::hdadb()->HDA_DB_monitorClear();
 //  hda_db::hdadb()->HDA_DB_updatePending(null, array('ProcessState'=>'RESTART'));
   hda_db::hdadb()->HDA_DB_cronUpdate(" ++ CRON Startup request ");
   }

hda_db::hdadb()->HDA_DB_cronIn(" ++ CRON Entry checking blackout days and times ".session_id());
/*
BLACKOUT::20:00 07:00
BLACKDAYS::Sat Sun
BLOCKEVENT::Sat 20:00 21:00
*/


$using_cron = INIT('CRON')==1;
$you_are_background = $using_cron;
$blackdays = INIT('BLACKDAYS');
$days = array();
if (!is_null($blackdays)) {
	$tok = @strtok($blackdays, ' ');
	while ($tok!==false) {
		$days[] = trim($tok);
		$tok = strtok(' ');
		}
	strtok('',','); 
}
$today = date('l');
$run_today = true;
foreach ($days as $day) {
	if (preg_match("/{$day}/i", $today, $matches)) $run_today = false;
}
if (!$run_today) {
	$you_are_background = false;
	hda_db::hdadb()->HDA_DB_cronUpdate(" ++ CRON Entry blackout day {$today} is one of {$blackdays} ");
	$msg[] = array(false, " ++ CRON Entry blackout day {$today} is one of {$blackdays} ");
}
$tod = date('Gi');
$blackout = INIT('BLACKOUT');
$blackout_from = $blackout_to = null;
if (($you_are_background) && (!is_null($blackout))) {
	if (preg_match("/(?P<from_h>\d\d)\:(?P<from_m>\d\d)[ ]{1,}(?P<to_h>\d\d)\:(?P<to_m>\d\d)/",$blackout,$matches)) {
		$blackout_from = "{$matches['from_h']}{$matches['from_m']}"; $blackout_to = "{$matches['to_h']}{$matches['to_m']}";
		if ($blackout_from>$blackout_to) {
			if ((($tod>$blackout_from)&&($blackout_from<2400))||($tod<$blackout_to)) {
				$you_are_background = false;
				$msg[] = array(false, " ++ CRON Blackout Time {$tod} in {$blackout_from} to {$blackout_to}");
			}
		}
		else if (($blackout_from>$tod)&&($blackout_to<$tod)) {
			$you_are_background = false;
			$msg[] = array(false, " ++ CRON Blackout Time {$tod} in {$blackout_from} to {$blackout_to}");
		}
	}
}
$blockevent = INIT('BLOCKEVENT');		
$blocks = null;
if (!is_null($blockevent)) {
	$blocks = array();
	$tok = @strtok($blockevent, ';');
	while ($tok!==false) {
		$blocks[] = trim($tok);
		$tok = strtok(';');
		}
	strtok('',','); 
}
if (is_array($blocks)) foreach($blocks as $block) {
	$block_from = 	$block_to = null;
	if ($you_are_background) {
		if (preg_match("/(?P<day>[A-Z]{3,9})[ ]{1,}(?P<from_h>\d\d)\:(?P<from_m>\d\d)[ ]{1,}(?P<to_h>\d\d)\:(?P<to_m>\d\d)/i",$block,$matches)) {
			if (preg_match("/{$matches['day']}/i",$today)) {
				$block_from = intval("{$matches['from_h']}{$matches['from_m']}"); $block_to = intval("{$matches['to_h']}{$matches['to_m']}");
				if ($block_from<$block_to) {
					if (($tod>$block_from)&&($tod<$block_to)) {
						$you_are_background = false;
						hda_db::hdadb()->HDA_DB_cronUpdate(" ++ CRON Entry block event {$block} ");
						$msg[] = array(false, " ++ CRON BLOCK EVENT {$today} {$tod} block {$block}");
					}
				}
			}
		}
	}
}


if ($you_are_background) hda_db::hdadb()->HDA_DB_cronUpdate(" ++ CRON Entry ".session_id());
else {
	hda_db::hdadb()->HDA_DB_cronUpdate(" ++ CRON Entry blackout {$today} {$tod} block {$block}");
	$msg[] = array(false, " ++ CRON Entry blackout {$today} {$tod} block {$block}");
	}




function _cron_error_handler($errno, $errmsg) {
   global $msg;
   //$t = __debug_stack(); HDA_LogToFile("cron_err", "{$errmsg} :- {$t}");
   $msg[] = array(false, "Cron Error: {$errmsg}");
   $alcdb = new hda_db();
   if ($alcdb->connect()===true) {
      $alcdb->HDA_DB_cronUpdate(" Error {$errmsg} ");
      $alcdb->HDA_DB_DropLock(null); // drop all these locks
	  }
   return true;
   }
set_error_handler('_cron_error_handler');

if ($you_are_background) {
   $m = "Background processing";
   hda_db::hdadb()->HDA_DB_WriteWatchMessage($UserCode, 'CRON', $m);
   $msg[] = array(false, " ++ CRON is enabled ");
   if ($is_startup) {
      $a = _win_proc();
	  hda_db::hdadb()->HDA_DB_cronUpdate(" StartUp ++ CGI processes:".count($a));
      }
   set_time_limit(0);
   }
else $msg[] = array(false, " ++ CRON DISABLED");

      $a = _win_proc();
	  hda_db::hdadb()->HDA_DB_cronUpdate(" ++ CGI processes:".count($a));


if ($you_are_background) {
   if (is_null($HDA_EMAIL_CFG['SUSPEND_GET_MAIL'] || $HDA_EMAIL_CFG['SUSPEND_GET_MAIL']==0)) $msg[] = array(false, " ++ Suspended Get Mail ");
   elseif (hda_db::hdadb()->HDA_DB_TakeLock('EM')) {
      try {
         if ( HDA_getMail($HDA_EMAIL_CFG['GET_MAIL_IMAP'], $HDA_EMAIL_CFG['GET_MAIL_ACCOUNT'], $HDA_EMAIL_CFG['GET_MAIL_PASSWORD'])) {
            $msg[] = array(false, hda_db::hdadb()->HDA_DB_cronUpdate(" ++ Hydra has received new email messages on ".$HDA_EMAIL_CFG['GET_MAIL_ACCOUNT']));
            }
         if ( HDA_getMail($HDA_EMAIL_CFG['GET_MAIL_IMAP'], "load@gentia.io", "VaX3800A1")) {
            $msg[] = array(false, hda_db::hdadb()->HDA_DB_cronUpdate(" ++ Hydra has received new email messages on load@gentia.io"));
            }
         }
      catch(Exception $e) {
         $msg[] = array(false, hda_db::hdadb()->HDA_DB_cronUpdate(" ++ ".$e->getMessage()));
         }
      hda_db::hdadb()->HDA_DB_DropLock('EM');
      }
   }


if ($you_are_background) {
   if (hda_db::hdadb()->HDA_DB_TakeLock('AQ', 600)) {
      try {
         $a = hda_db::hdadb()->HDA_DB_actionFromQ();
         if (isset($a) && is_array($a) && count($a)>0) {
            $throttle = count($a); if ($throttle>5) $throttle = 5;
            $forward_to=array();
            foreach ($a as $row) if (--$throttle>=0) {
               $forward_to[$row['Method']]=true;
               switch ($row['Method']) {
                  case 'EMAIL': 
						hda_db::hdadb()->HDA_DB_cronUpdate(" Email pick up ");
				     if (!HDA_SendMail($row['Data']['Format'], $row['Data'], $err)) hda_db::hdadb()->HDA_DB_cronUpdate(" Email fails {$err} "); 
				     break;
                  case 'SOAP': 
				     if (!HDA_SendSOAP($row['Data'], $err)) hda_db::hdadb()->HDA_DB_cronUpdate(" SOAP fails {$err} "); 
				     break;
                  default: HDA_SendErrorMail("Bad entry in background Q to:{$row['Method']}"); break;
                  }
               hda_db::hdadb()->HDA_DB_removeQ($row['ItemId']);
               }
            $s =  " ++ Forward Q actions to ";
            foreach ($forward_to as $k=>$p) $s.= "{$k} ";
            $msg[] = array(false, $s);
			hda_db::hdadb()->HDA_DB_cronUpdate(" Action Qs ok ");
            }
         }
      catch(Exception $e) {
         $msg[] = array(false, hda_db::hdadb()->HDA_DB_cronUpdate(" ++ ".$e->getMessage()));
         }
      hda_db::hdadb()->HDA_DB_DropLock('AQ');
      }
  }

if ($you_are_background) {
   if (hda_db::hdadb()->HDA_DB_TakeLock('AT')) {
      try {
         $a = HDA_AllAutoTrigger();
		 foreach ($a as $p) $msg[] = array(false, $p);
		 hda_db::hdadb()->HDA_DB_cronUpdate(" Auto Triggered ");
         }
      catch (Exception $e) {
         $msg[] = array(false, hda_db::hdadb()->HDA_DB_cronUpdate(" ++ ".$e->getMessage()));
         }
      hda_db::hdadb()->HDA_DB_DropLock('AT');
      }
   }

if ($you_are_background) {
   if (hda_db::hdadb()->HDA_DB_TakeLock('RP')) {
      try {
         if (HDA_RunDailyReport()) {
            $msg[] = array(false, hda_db::hdadb()->HDA_DB_cronUpdate(" ++ Daily Report ready"));
            }
         }
      catch(Exception $e) {
         $msg[] = array(false, hda_db::hdadb()->HDA_DB_cronUpdate(" ++ ".$e->getMessage()));
         }
	  hda_db::hdadb()->HDA_DB_DropLock('RP');
	  }
   }
if ($you_are_background) {
   if (hda_db::hdadb()->HDA_DB_TakeLock('BU')) {
      try {
         if (HDA_RunAutoBackup()) {
            $msg[] = array(false, hda_db::hdadb()->HDA_DB_cronUpdate(" ++ Auto Backup Ran"));
            }
         }
      catch(Exception $e) {
         $msg[] = array(false, hda_db::hdadb()->HDA_DB_cronUpdate(" ++ ".$e->getMessage()));
         }
	  hda_db::hdadb()->HDA_DB_DropLock('BU');
	  }
   }
if ($you_are_background) {
   if (hda_db::hdadb()->HDA_DB_TakeLock('TG')) {
      try {
         if (HDA_RunTimeTriggers()) {
            $msg[] = array(false, hda_db::hdadb()->HDA_DB_cronUpdate(" ++ Triggered activity"));
            }
         }
      catch(Exception $e) {
         $msg[] = array(false, hda_db::hdadb()->HDA_DB_cronUpdate(" ++ ".$e->getMessage()));
         }
	  hda_db::hdadb()->HDA_DB_DropLock('TG');
	  }
   }

if ($you_are_background) {
   if (hda_db::hdadb()->HDA_DB_TakeLock('TD')) {
      try {
         if (HDA_AutoTidy()) {
            $msg[] = array(false, hda_db::hdadb()->HDA_DB_cronUpdate(" ++ Auto tidy"));
            }
         }
      catch(Exception $e) {
         $msg[] = array(false, hda_db::hdadb()->HDA_DB_cronUpdate(" ++ ".$e->getMessage()));
         }
      hda_db::hdadb()->HDA_DB_DropLock('TD');
	  }
   }

if ($you_are_background) {
   if (hda_db::hdadb()->HDA_DB_TakeLock('TK')) {
      try {
         if (HDA_TicketCollection($log)) {
            $msg[] = array(false, " ++ Auto ticket collection: ".log_to_string($log));
			hda_db::hdadb()->HDA_DB_cronUpdate(" Auto tickets collected");
            }
	     else $msg[] = array(false, hda_db::hdadb()->HDA_DB_cronUpdate("++ Auto ticket collection fails: ".log_to_string($log)));
         }
      catch(Exception $e) {
         $msg[] = array(false, hda_db::hdadb()->HDA_DB_cronUpdate(" ++ ".$e->getMessage()));
         }
      hda_db::hdadb()->HDA_DB_DropLock('TK');
	  }
   }
   
if ($you_are_background) {
   if (hda_db::hdadb()->HDA_DB_TakeLock('XC')) {
      try {
         if (HDA_ExternalCollection($log)) {
            $msg[] = array(false, " ++ Auto eXternal collection: ".log_to_string($log));
			hda_db::hdadb()->HDA_DB_cronUpdate(" Auto Collects ");
            }
	     else $msg[] = array(false, hda_db::hdadb()->HDA_DB_cronUpdate("++ Auto eXternal collection fails: ".log_to_string($log)));
         }
      catch(Exception $e) {
         $msg[] = array(false, hda_db::hdadb()->HDA_DB_cronUpdate(" ++ ".$e->getMessage()));
         }
	  hda_db::hdadb()->HDA_DB_DropLock('XC');
      }
   }

// JOB Qs

if ($you_are_background) {
   if (!HDA_CheckQs($error)) {
      $msg[] = array(false, hda_db::hdadb()->HDA_DB_cronUpdate(" ++ JobQ Error {$error}"));
	  }
   }
if ($you_are_background) {
   if (!HDA_CheckJobs($error)) {
      $msg[] = array(false, hda_db::hdadb()->HDA_DB_cronUpdate(" ++ Job Stall Error {$error}"));
	  }
   }

if ($you_are_background && time_to_run($ran_job_time)) {
	$this_session = session_id();
	$held_lock = hda_db::hdadb()->HDA_DB_TestLock('PQ');
   if (hda_db::hdadb()->HDA_DB_TakeLock('PQ')) {
      while (time_to_run($ran_job_time) && hda_db::hdadb()->HDA_DB_HoldingLock('PQ')) {
         $a = hda_db::hdadb()->HDA_DB_pendingQ(NULL, NULL, 0, $runnableOnly=true);
         if (isset($a) && is_array($a) && count($a)>0) {
            $row = $a[0];
			 HDA_LogThis("Cron says found {$row['Title']} ref {$row['ItemId']} State {$row['ProcessState']} {$this_session} ", $row['Source']);
            $msg[] = array(false, " ++ Pending processes in Q0");
            try {
			$msg[] = array(false, hda_db::hdadb()->HDA_DB_cronUpdate(" ++ Will process {$row['Title']} memory PQ ".memory_get_usage()));
			 HDA_LogThis("Cron says run {$row['Title']} ref {$row['ItemId']} State {$row['ProcessState']} {$this_session} ", $row['Source']);
               if (HDA_ProcessPending($row)) {
				   hda_db::hdadb()->HDA_DB_RemovePending($row['ItemId']);
				   HDA_LogThis("Cron removes {$row['Title']} ref {$row['ItemId']}  {$this_session} ", $row['Source']);
			   }
		       hda_db::hdadb()->HDA_DB_cronUpdate(" ++ Completed {$row['Title']} memory PQ ".memory_get_usage());
               }
            catch (Exception $e) {
				HDA_LogThis("Cron PQ Exception  {$this_session} ".$e->getMessage(),'CRON');
               hda_db::hdadb()->HDA_DB_RemovePending($row['ItemId']);
               hda_db::hdadb()->HDA_DB_cronUpdate("Aborted Q0 process: ".$e->getMessage());
               hda_db::hdadb()->HDA_DB_TaskComplete($row['ItemId'], $success=false);
               }
			}
		 else break;
	     }
      if (!hda_db::hdadb()->HDA_DB_DropLock('PQ')) hda_db::hdadb()->HDA_DB_cronUpdate(" ++ Forced off PQ ");
      }
   }
   

if ($you_are_background && time_to_run($ran_job_time)) {
   for ($i = 1; ($i<10) && time_to_run($ran_job_time); $i++) {
      if (hda_db::hdadb()->HDA_DB_TakeLock("Q{$i}")) {
	     while (time_to_run($ran_job_time) && hda_db::hdadb()->HDA_DB_HoldingLock("Q{$i}")) {
            $a = hda_db::hdadb()->HDA_DB_pendingQ(NULL, NULL, $i, $runnableOnly=true);
            if (isset($a) && is_array($a) && count($a)>0) {
               $msg[] = array(false, hda_db::hdadb()->HDA_DB_cronUpdate(" ++ Pending BULK processes in Q{$i}"));
               $row = $a[0];
               try {
                  $msg[] = array(false, hda_db::hdadb()->HDA_DB_cronUpdate(" ++ Will process {$row['Title']}"));
                  if (HDA_ProcessPending($row)) hda_db::hdadb()->HDA_DB_RemovePending($row['ItemId']);
		          hda_db::hdadb()->HDA_DB_cronUpdate(" ++ Completed {$row['Title']} memory Q{$i} ".memory_get_usage());
                  }
               catch (Exception $e) {
                  hda_db::hdadb()->HDA_DB_RemovePending($row['ItemId']);
                  hda_db::hdadb()->HDA_DB_cronUpdate("Aborted Q{$i} process: ".$e->getMessage());
                  hda_db::hdadb()->HDA_DB_TaskComplete($row['ItemId'], $success=false);
                  }
			   }
			else break;
			}
         if (!hda_db::hdadb()->HDA_DB_DropLock("Q{$i}")) hda_db::hdadb()->HDA_DB_cronUpdate(" ++ Forced off Q{$i} ");
		 }
      }
   }

if ($you_are_background && time_to_run($ran_job_time)) {
   if (hda_db::hdadb()->HDA_DB_TakeLock("Q10")) {
      $a = hda_db::hdadb()->HDA_DB_pendingQ(NULL, NULL, 10, $runnableOnly=true);
      if (isset($a) && is_array($a) && count($a)>0) {
	     foreach ($a as $row) {
            $msg[] = array(false, hda_db::hdadb()->HDA_DB_cronUpdate(" ++ Pending BULK processes in Q10"));
             try {
               $msg[] = array(false, hda_db::hdadb()->HDA_DB_cronUpdate(" ++ Will process {$row['Title']}"));
               if (HDA_ProcessPending($row)) hda_db::hdadb()->HDA_DB_RemovePending($row['ItemId']);
               }
            catch (Exception $e) {
               hda_db::hdadb()->HDA_DB_RemovePending($row['ItemId']);
               hhda_db::hdadb()->HDA_DB_cronUpdate("Aborted Q10 process: ".$e->getMessage());
               hda_db::hdadb()->HDA_DB_TaskComplete($row['ItemId'], $success=false);
               }
			}
		 }
      hda_db::hdadb()->HDA_DB_DropLock("Q10");
      }
   }
   
hda_db::hdadb()->HDA_DB_cronOut(" ++ CRON Exit ".session_id());

foreach ($msg as $sa) $t .= "{$sa[1]}<br>";
$t .= "Cron ran for ".(time()-$ran_job_time)." secs<br>";
@file_put_contents("ErrorLogs/cron.html",$t);
$t = "<div style=\"border:1px solid blue; width:100%; height:500px; overflow:scroll; text-align:left;\" >{$t}</div>";


$content .= $t;
_finals_();

function time_to_run($start_time) {
   if (memory_get_usage()>200000000) {
      hda_db::hdadb()->HDA_DB_cronUpdate(" ++ Time to run memory  ".memory_get_usage());
	  return false;
	  }
   return ((time() - $start_time) < 600);
   }
   
function log_to_string($log, $eol="<br>") {
   if (is_array($log)) {
      $log_s = ""; foreach ($log as $alog) $log_s .= "{$alog}{$eol}";
      return $log_s;
      }
   return $log;
   }

function HDA_AutoTidy() {
   if (!hda_db::hdadb()->HDA_DB_timeForJob('AutoTidy')) return false;
   $space_recovered = 0; $files_deleted = 0;
   try {
      // the tmp directory
      $a = _lsdir("tmp");
      foreach ($a as $file) {
         if (@filetype($file['Path']) == "dir") {
		 }
		 else if (is_dir($file['Path'])) {
		 }
		 else
         if ($file['Modified']<(time()-60*60*24)) {
            if (file_exists($file['Path'])) _rrmdir($file['Path']);
            $files_deleted++;
            $space_recovered += $file['Size'];
            }
         }
      // the CUSTOM directory
      $a = @scandir("CUSTOM"); 
      $s = "";
      foreach ($a as $aa) { 
         if ($aa != "." && $aa != "..") { 
            if (@filetype("CUSTOM/{$aa}") == "dir") {
               if (hda_db::hdadb()->HDA_DB_profileExists($aa) === false) {
                  _rrmdir("CUSTOM/{$aa}");
                  $s .= "{$aa} ";
                  }
               } 
            } 
         } 
      if (strlen($s)>0) HDA_LogThis("System clean-up removed orphaned custom directories {$s}", $source = 'HDAW', $user=0, $username=NULL);
      // the Local Logs
      $a = @scandir("CUSTOM"); 
      foreach ($a as $aa) { 
         if ($aa != "." && $aa != "..") { 
            if (@filetype("CUSTOM/{$aa}") == "dir") {
               $ff = glob("CUSTOM/{$aa}/*.log");
			   foreach ($ff as $f) {
			      $dt = max(@filemtime($f),@filectime($f));
				  if ($dt<(time()-(60*60*24*1))) @unlink($f);
                  }
               } 
            } 
         } 
      // the ProcessedFiles, FilesToProcess and FailedToProcess
	  $Processed = 0;
	  $FailedToProcess = 0;
	  $FilesToProcess = 0;
      $a = @scandir("CUSTOM"); 
      foreach ($a as $aa) { 
         if ($aa != "." && $aa != "..") { 
            if (@filetype("CUSTOM/{$aa}") == "dir") {
			   foreach(array('Processed'=>3,'FilesToProcess'=>8,'FailedToProcess'=>8,'tmp'=>3) as $indir=>$days_old) {
				   if (@file_exists("CUSTOM/{$aa}/{$indir}")) {
					  $ff = glob("CUSTOM/{$aa}/{$indir}/*");
					  foreach ($ff as $f) {
						 $dt = max(@filemtime($f),@filectime($f));
						 if ($dt<(time()-(60*60*24*$days_old))) { @unlink($f); $$indir++; $files_deleted++;}
						 }
					  }
				   }
               } 
            } 
         }
      if ($Processed>0) HDA_LogThis("System clean-up removed {$Processed} processed data files", $source = 'HDAW', $user=0, $username=NULL);
      if ($FailedToProcess>0) HDA_LogThis("System clean-up removed {$FailedToProcess} FAILED processed data files", $source = 'HDAW', $user=0, $username=NULL);
      if ($FilesToProcess>0) HDA_LogThis("System clean-up removed {$FilesToProcess} TO process data files", $source = 'HDAW', $user=0, $username=NULL);
		 
      // the Errorlogs
      $a = _lsdir("ErrorLogs");
      foreach ($a as $file) {
         if ($file['Modified']<(time()-(3*60*60*24))) {
            if (file_exists($file['Path'])) @unlink($file['Path']);
            }
         }
	  // Notes
	  hda_db::hdadb()->HDA_DB_tidyNotes();
	  // Logs
	  hda_db::hdadb()->HDA_DB_tidyLogger();
	   HDA_LogThis("System clean-up Logged items", $source = 'HDAW', $user=0, $username=NULL);
	  // Audit
	  hda_db::hdadb()->HDA_DB_tidyAudit();
	  HDA_LogThis("System clean-up Audit items", $source = 'HDAW', $user=0, $username=NULL);
	  // Apps
	  hda_db::hdadb()->HDA_DB_tidyAppLog();
	  HDA_LogThis("System clean-up App Update items", $source = 'HDAW', $user=0, $username=NULL);
	  // Events
	  hda_db::hdadb()->HDA_DB_tidyEvents();
	  // Monitor
	  //hda_db::hdadb()->HDA_DB_tidyMonitor();
	  // Checksums
	  hda_db::hdadb()->HDA_DB_tidyChecksums();
      // the Archive
      $a = _lsdir("Archive");
      foreach ($a as $file) {
         if ($file['Modified']<(time()-(3*60*60*24))) {
            if (file_exists($file['Path'])) @unlink($file['Path']);
            }
         }
	  // Collection Archive
	  _tidy_collection_archive();
      }
   catch (Exception $e) {
      HDA_SendErrorMail("Auto Tidy fails ".$e->getMessage());
      hda_db::hdadb()->HDA_DB_ranJob('AutoTidy','EVERY DAY');
      return false;
      }
   
   hda_db::hdadb()->HDA_DB_ranJob('AutoTidy','EVERY DAY');
   HDA_LogThis("System clean-up ran deleted {$files_deleted} files and {$space_recovered} bytes", $source = 'HDAW', $user=0, $username=NULL);
   return true;
   }
   
function HDA_RunAutoBackup() {
   global $code_root, $home_root;
   $backup = hda_db::hdadb()->HDA_DB_admin('Backup');
   if (is_null($backup)) return false;
   $backup = hda_db::hdadb()->HDA_DB_unserialize($backup);
   if (!array_key_exists('Backup_period',$backup)) return false;
   $a = hda_db::hdadb()->HDA_DB_jobTime('Backup');
   if (!is_null($a) && is_array($a) && array_key_exists('LastTime',$a)) {
      $run_today = false;
      $last_time = $a['LastTime'];
      if (!is_null($last_time)) {
         $dt1 = date_create($last_time);
         $dt2 = date_create();
         $interval = date_diff($dt1, $dt2);
         switch ($backup['Backup_period']) {
            case 'DAY':
               $run_today = ($interval->days !== false && $interval->days>0);
               $run_today |= ($interval->y>0 || $interval->m>0 || $interval->d>0);
               break;
            case 'WEEK':
               $run_today = ($interval->days !== false && $interval->days>0);
               if ($run_today) {
                  $w = date('N',time());
                  $run_today = ($w == $backup['Backup_periodDOW']);
                  }
               break;
            case 'MONTH':
               $run_today = ($interval->days !== false && $interval->days>0);
               if ($run_today) {
                  $m = date('j',time());
                  $run_today = ($m == $backup['Backup_periodDOM']);
                  }
               break;
            }
         if ($run_today) {
            $run_at_hour = intval($backup['Backup_time']/60);
            $run_at_min = intval($backup['Backup_time'] % 60);
            $h = date('G',time());
            $m = date('i',time());
            if ($h<$run_at_hour && $m<$run_at_min) return false;
            }
         else return false;
         }
      }
   // run the backup
   include_once("../{$code_root}/HDA_Backup.php");
   HDA_purge_backup();
   $problem = HDA_full_backup();
   $t = "Auto backup {$problem}";
   hda_db::hdadb()->HDA_DB_ranJob('Backup');
   HDA_LogThis("Auto Backup Ran", $source = 'HDAW', $user=0, $username=NULL);
   return true;
   }

function HDA_RunDailyReport() {

   if (!hda_db::hdadb()->HDA_DB_timeForJob('DailyReport')) return false;
   $daily_report = hda_db::hdadb()->HDA_DB_admin('DailyReport');
   if (is_null($daily_report)) return false;
   $daily_report = hda_db::hdadb()->HDA_DB_unserialize($daily_report);
   $run_at_hour = intval($daily_report['RunAt']/60);
   $run_at_min = intval($daily_report['RunAt'] % 60);
   $h = date('G',time());
   $m = date('i',time());
   if ($h<$run_at_hour || $m<$run_at_min) return false;

   $path = "Archive/DailyReport.pdf";
   $path_html="Archive/DailyReport.html";
   $t = _make_daily_report();
   @file_put_contents($path_html, $t);
   $html2pdf = new HTML2PDF('L', 'A4', 'en', true, 'UTF-8', 3);
   $html2pdf->pdf->SetDisplayMode('fullpage');
   $html2pdf->writeHTML($t, isset($_GET['vuehtml']));
   $html2pdf->Output($path,'F');

   hda_db::hdadb()->HDA_DB_admin('TodaysReport',$path_html);
   hda_db::hdadb()->HDA_DB_ranJob('DailyReport','EVERY DAY');
   HDA_LogThis("Daily Report Generated", $source = 'HDAW', $user=0, $username=NULL);

   $send_to = $daily_report['SendTo'];
   $send_list = null;
   $all_users = hda_db::hdadb()->HDA_DB_AllUsers();
   switch ($send_to) {
      case 'ALL':
         $send_list = array();
         foreach ($all_users as $user) $send_list[] = $user['UserItem'];
         break;
      case 'OWNERS':
         $send_list = array();
         foreach ($all_users as $user) if (array_key_exists('ADMIN', $user['Allow']) || array_key_exists('PROFILE', $user['Allow'])) 
            $send_list[] = $user['UserItem'];
         break;
      case 'ADMIN':
         $send_list = array();
         foreach ($all_users as $user) if (array_key_exists('ADMIN', $user['Allow'])) 
            $send_list[] = $user['UserItem'];
         break;
      default:
      case '0':
         break;
      }
   if (!is_null($send_list)) {
      HDA_EmailThis("Daily Report", "Attached Daily Report", $format='ALCDailyReport', $user = 0, $send_list, true, $path);
      }
   return true;
   }
   

function HDA_CheckQs(&$error) {
	return true;
   $error = "";
   for ($i=1; $i<=10; $i++) {
      $q = "Q{$i}";
	  if (!is_null($sessionid = hda_db::hdadb()->HDA_DB_TestLock($q,$timeout=6000))) {
	     $jobs = hda_db::hdadb()->HDA_DB_monitorQ($i);
         if ($jobs==0) {
		     for ($sleep=0; ($sleep<4) && ($jobs==0); $sleep++) {
				 sleep(1); // wait for holder to load another job
				 $jobs += hda_db::hdadb()->HDA_DB_monitorQ($i);
				 }
			 }
		 if ($jobs==0) {
			$did_release = hda_db::hdadb()->HDA_DB_DropLock($q, $sessionid);
			$did_release = ($did_release)?"Did Release":"No Lock Now";
			$error .= "Forced release of jobQ {$q} {$sessionid} {$did_release} ";
			}
	     }
      }
   $q = "PQ";
   if (!is_null($sessionid = hda_db::hdadb()->HDA_DB_TestLock($q,$timeout=6000))) {
	  $jobs = hda_db::hdadb()->HDA_DB_monitorQ(0);
         if ($jobs==0) {
		     for ($sleep=0; ($sleep<4) && ($jobs==0); $sleep++) {
				 sleep(1); // wait for holder to load another job
				 $jobs += hda_db::hdadb()->HDA_DB_monitorQ(0);
				 }
			 }
      if ($jobs==0) {
		 $did_release = hda_db::hdadb()->HDA_DB_DropLock($q, $sessionid);
		 $did_release = ($did_release)?"Did Release":"No Lock Now";
	     $error .= "Forced release of jobQ {$q} {$sessionid} {$did_release} ";
		 }
	  }
   if (strlen($error)>0) return false; 
   return true;
   } 
   
function HDA_CheckJobs(&$error) {
	return true;
   $error = "";
   $a = hda_db::hdadb()->HDA_DB_getMonitor();
   $t = strtotime(hda_db::hdadb()->PRO_DB_dateNow());
   foreach ($a as $row) {
      if (strtotime($row['Pulse'])<($t-(60*60*5))) {
		 $error .= " Detected stalled job in q {$row['InQ']} ".print_r($row, true);
		 $error .= " This time {$t} ".date('G:i:s',$t)." last pulse {$row['Pulse']} ".date('G:i:s',$row['Pulse']);
		 $error .= " Profile Title: {$row['Title']}";
		 $pq = hda_db::hdadb()->HDA_DB_pendingQ(null, $row['ItemQ']);
		 $error .= " PendingQ ".print_r($pq, true);
		 $pq = (is_array($pq) && count($pq)==1)?$pq[0]['ItemId']:NULL;
		 //hda_db::hdadb()->HDA_DB_monitorClear($sessid = $row['SessionId']);
		 if (!is_null($pq)) hda_db::hdadb()->HDA_DB_updatePending($pq, array('ProcessState'=>'STALLED', 'IssuedDate'=>hda_db::hdadb()->PRO_DB_dateNow()));
		 $error .= " : "._win_kill($row['PID'])." ; ";
	     }
	  if ((($row['Status']=='ABORTED')||($row['Status']=='STALLED')) && (strtotime($row['Pulse'])<($t-(4*60)))) {
		 $err = " Detected NON ABORTING job in q {$row['InQ']} ".print_r($row, true);
		 $err .= " Profile Title: {$row['Title']}";
		 $pq = hda_db::hdadb()->HDA_DB_pendingQ(null, $row['ItemQ']);
		 $pq = (is_array($pq) && count($pq)==1)?$pq[0]['ItemId']:NULL;
		 hda_db::hdadb()->HDA_DB_monitorClear($sessid = $row['SessionId']);
		 if (!is_null($pq)) hda_db::hdadb()->HDA_DB_RemovePending($pq);
		 $q = ($row['InQ']==0)?'PQ':"Q{$row['InQ']}";
		 hda_db::hdadb()->HDA_DB_DropLock($q, true);
		 $err .= " : "._win_kill($row['PID'])." ; ";
		 HDA_LogOnly($err, 'CRON');
		 $error .= $err;
	     }
	  if ($row['Status']=='FINISHED') {
		 $err = " Detected FINISHED job in q {$row['InQ']} ".print_r($row, true);
		 $err .= " Profile Title: {$row['Title']}";
		 $pq = hda_db::hdadb()->HDA_DB_pendingQ(null, $row['ItemQ']);
		 $pq = (is_array($pq) && count($pq)==1)?$pq[0]['ItemId']:NULL;
		 hda_db::hdadb()->HDA_DB_monitorClear($sessid = $row['SessionId']);
		 if (!is_null($pq)) hda_db::hdadb()->HDA_DB_RemovePending($pq);
		 $err .= " : "._win_kill($row['PID'])." ; ";
		 HDA_LogOnly($err, 'CRON');
		 $error .= $err;
	     }
      }
   if (strlen($error)>0) {
      HDA_SendErrorMail($error); 
	  return false; 
	  }
   return true;
   }
   
function HDA_RunTimeTriggers() {
   $did_trigger = false;
   $a = hda_db::hdadb()->HDA_DB_getSchedule(null, true); // get those due only
   if (!is_null($a) && is_array($a)) {
      foreach ($a as $row) {
         $this_process = hda_db::hdadb()->HDA_DB_ReadProfile($row['ItemId']);
		 if (is_null($this_process)) {
		    hda_db::hdadb()->HDA_DB_clearSchedule($row['ItemId']);
			continue;
			}
		 $next = strtotime($row['Scheduled']);
         $due = (($next-time())<0);
         if ($due) {
            HDA_nextScheduled($this_process);
            $is_running = hda_db::hdadb()->HDA_DB_inPendingQ($this_process['ItemId']);
            if ($is_running===false) {
               HDA_ReportTrigger($this_process['ItemId'], null, true);
				if (!is_null($code = hda_db::hdadb()->HDA_DB_addRunQ(
							NULL, 
							$this_process['ItemId'], 
							$this_process['CreatedBy'],
							NULL, 
							NULL, 
							'SCHEDULED',
							"Triggered by schedule",
							hda_db::hdadb()->PRO_DB_dateNow()))) {
						$note = "Scheduled to pending process queue";
						hda_db::hdadb()->HDA_DB_issueNote($this_process['ItemId'], $note, 'TAG_PROGRESS');
						HDA_LogThis(hda_db::hdadb()->HDA_DB_TitleOf($this_process['ItemId'])." {$note}");
						$did_trigger |= true;
						}
			   }
            }
         }
      }

   return $did_trigger;
   }
   
function HDA_nextScheduled(&$this_process) {
   $on_schedule = hda_db::hdadb()->HDA_DB_getSchedule($this_process['ItemId']);
   if (is_array($on_schedule) && count($on_schedule)==1) {
      $units = (is_null($on_schedule[0]['Units']) || $on_schedule[0]['Units']<1)?1:$on_schedule[0]['Units'];
      switch (strtoupper($on_schedule[0]['RepeatInterval'])) {
	     case 'MIN':
            $next = time();
	        $next += $units*60;
		    break;
         case 'DAY':
            $next = strtotime("tomorrow");
			$next += date('G',strtotime($on_schedule[0]['Scheduled']))*60*60;
			$next += date('i',strtotime($on_schedule[0]['Scheduled']))*60;
            break;
         case 'HOUR':
         default:
            $next = strtotime("today");
			$next += date('G',strtotime($on_schedule[0]['Scheduled']))*60*60;
			$next += date('i',strtotime($on_schedule[0]['Scheduled']))*60;
			while ($next<time()) {
               $next += $units*60*60;
			   }
            break;
         }
      hda_db::hdadb()->HDA_DB_updateSchedule($this_process['ItemId'], array('Scheduled'=>hda_db::hdadb()->PRO_DB_DateTime($next)));
      return true;
      }
   return false;
   }
   
function HDA_AutoTrigger($item) {
   if (!hda_db::hdadb()->HDA_DB_relationEnabled($item)) return true; // Not enabled for rules or auto trigger
   $is_running = hda_db::hdadb()->HDA_DB_inPendingQ($item);
   if ($is_running!==false) return hda_db::hdadb()->HDA_DB_autoLog($item, "Not runnable, already in Q {$is_running}");
   if (!($is_proxy = hda_db::hdadb()->HDA_DB_relationIsProxy($item))) {
      $last_failure = hda_db::hdadb()->HDA_DB_HasFailureEvent($item, $tries);
      if ($last_failure !== false) {
         $fail_rule = hda_db::hdadb()->HDA_DB_relationFail($item);
	     if ($fail_rule == 'N') return hda_db::hdadb()->HDA_DB_autoLog($item, "Previous try failed, rule says no retry");
	     $fail_rule_wait = $fail_rule & 0xff;
	     $fail_rule_retry = $fail_rule >> 8;
	     if (((time()-strtotime($last_failure))/(60*60)) < $fail_rule_wait) return hda_db::hdadb()->HDA_DB_autoLog($item, "Too early for retry, rule says wait {$fail_rule_wait} hours");
	     if ($tries>$fail_rule_retry) return hda_db::hdadb()->HDA_DB_autoLog($item, "Too many retries, rule says max retries {$fail_rule_retry} every {$fail_rule_wait} hours");
         }
	  }
   $last_event_date = hda_db::hdadb()->HDA_DB_SuccessEventDate($item);
   $rule = hda_db::hdadb()->HDA_DB_relationRule($item);
   $default = hda_db::hdadb()->HDA_DB_relationDefault($item);
   $datadays = hda_db::hdadb()->HDA_DB_relationDataDays($item);
   $log = array();
   $adjusted = (_adjustRunDateForDataDays($last_event_date, $datadays, $log))?("No data expected, Adjusted effective run date to {$last_event_date}, "):"";
   $passed = _passesRules($last_event_date, $rule, $default, $datadays, $log);
   if ($passed===true) return true; // Already passed trigger rule;
   if (is_null($passed)) {
      if (($count = hda_db::hdadb()->HDA_DB_IsLateEvent($item))&&($count<2)) {
		 if (_expectDataFor($item, $log)) HDA_ProfileCollectLate($item, $warning=false, $log);
	     return hda_db::hdadb()->HDA_DB_autoLog($item, "{$adjusted}Late, too late for data now, last alert sent now, roll-up continues", true);
		 }
	  return false; // Late, too late for data, roll-up continues, last alert already sent
	  }
   if (_rulesWarningDue($last_event_date,$default)) {
      if (hda_db::hdadb()->HDA_DB_SetLateEvent($item)) {
	     if (_expectDataFor($item, $log)) HDA_ProfileCollectLate($item, $warning=true, $log);
         return hda_db::hdadb()->HDA_DB_autoLog($item, "{$adjusted}Late, warnings sent ", true);
		 }
	  return hda_db::hdadb()->HDA_DB_autoLog($item, "{$adjusted}Late, warning already sent");
      }
   if (hda_db::hdadb()->HDA_DB_willCollect($item)) return hda_db::hdadb()->HDA_DB_autoLog($item, "Waiting Auto Collect");
   
   $events = hda_db::hdadb()->HDA_DB_AllSuccessEvents();
   $children = hda_db::hdadb()->HDA_DB_childrenOf($item, $cat=NULL, $events);
   if (is_null($children) && !$is_proxy) return true; // Top level, no dependants,  or not proxy, so auto trigger not required
   $all_ready = true;
   if (!is_null($children)) foreach($children as $child) {
      if (($child['TActive']&1)==1) {
	     $passed = _passesRules($child['LastTask'], $child['Rule'], $child['OnDefault'], $child['DataDays'],hda_db::hdadb()->HDA_DB_IsBlockoutDate($child['ItemId']));
	     $all_ready &= ($passed || is_null($passed));
		 }
      }
   if (!$all_ready) return hda_db::hdadb()->HDA_DB_autoLog($item, "Waiting for dependants to become ready");
		if (!is_null($code = hda_db::hdadb()->HDA_DB_addRunQ(
					NULL, 
					$item,
					NULL,					
					NULL, 
					NULL, 
					'TRIGGER',
					"Auto Trigger",
					hda_db::hdadb()->PRO_DB_dateNow(),
					($is_proxy)?10:NULL))) {
			return hda_db::hdadb()->HDA_DB_autoLog($item, "{$adjusted}Auto Triggered", !$is_proxy);
			}
		else false;
   }
   
function HDA_AllAutoTrigger() {
   $msg[] = "Auto Trigger";
   $a = hda_db::hdadb()->HDA_DB_profileNames();
   foreach ($a as $item=>$title) {
      $status = HDA_AutoTrigger($item);
	  if ($status===true || $status===false) continue;
      $msg[] = "{$title}: {$status}";
	  }
   return $msg;
   }

   
function HDA_TicketCollection(&$log) {
   $log = array();
   $profiles = array();
   $dates = array();
   $source_files = array();
   $xticket = hda_db::hdadb()->HDA_DB_admin('ExternalTicket');
   if (!is_null($xticket)) $xticket = hda_db::hdadb()->HDA_DB_unserialize($xticket); else { $log[] = "Ticket Collection Not Enabled"; return false; }
   $date_dir = "D".date('Ymd',time());
   if (!array_key_exists('COLLECT_POINT',$xticket) || strlen($xticket['COLLECT_POINT'])==0) { $log[] = "Ticket Collection Not Enabled"; return false; }
   $a = hda_db::hdadb()->HDA_DB_dictionary(NULL, trim($xticket['COLLECT_POINT']));
   if (!is_null($a) && is_array($a) && count($a)==1) {
      $def = $a[0]['Definition'];
	  }
   else { $log[] = "Ticket Collection unable to resolve collection point {$xticket['COLLECT POINT']}"; return false; }
   // Check for Archiving
   $arch_to = null;
   if (array_key_exists('TKTARCH',$xticket)&&$xticket['TKTARCH']=='ARCH') {
	  $a = hda_db::hdadb()->HDA_DB_dictionary(NULL, trim($xticket['TKTARCH_POINT']));
      if (is_array($a) && count($a)==1) {
		 $def = $a[0]['Definition'];
	     $arch_to = trim($def['Table']);
		 $arch_to .= "/{$date_dir}";
		 if (!file_exists($arch_to)) @mkdir($arch_to);
	     }
      }
   switch ($def['Connect Type']) {
      case 'FTP':
	     $ftp = new HDA_FTP();
		 $e = $ftp->lookupDictionary(trim($xticket['COLLECT_POINT']));
		 if ($e===false) { $log[] = "Fails to initialize FTP from collection glob {$xticket['COLLECT_POINT']}"; return false; }
		 $e = $ftp->open();
		 $ftp->to_dst_dir();
	     $target = $ftp->on_dir;
		 $target .= ($xticket['DATEDIR']==1)?("/{$date_dir}"):"";
		 $e = ($xticket['DATEDIR']==1)?$ftp->to_dst_dir($date_dir):true;
		 if ($e===false) { $log[] = "Ticket FTP: {$ftp->last_error}"; $ftp->close(); return false; } 
		 $s = $ftp->nlist();
         if ($s===false) { $log[] = "Ticket FTP: {$ftp->last_error}"; $ftp->close(); return false; } 
		 $ftp->ftp_mode = FTP_BINARY;
		 $dir = "tmp/collections";
		 if (!@file_exists($dir)) @mkdir($dir);
		 $dir .= "/{$date_dir}";
		 if (!@file_exists($dir)) @mkdir($dir);
		 foreach ($s as $f) {
			if ($f=='.' || $f=='..') continue;
			$profiles[] = $fn = pathinfo($f, PATHINFO_FILENAME);
			if (!@file_exists("{$dir}/{$fn}")) {
			        $log[] = "Ticket FTP makes {$dir}/{$fn}";
                    @mkdir("{$dir}/{$fn}");
                    }
		    }
		 $receipts = array();
		 foreach ($profiles as $profile) {
            $s = $ftp->nlist("{$profile}");
            if ($s===false) { $log[] = "Ticket FTP: {$ftp->last_error}"; $ftp->close(); return false; }
		    foreach ($s as $f) {
			   if ($f=='.' || $f=='..') continue;
		       $pi = pathinfo($f);
			   $remote_path = "{$ftp->on_dir}/{$profile}/{$pi['basename']}";
			   $dst_path = "{$dir}/{$profile}/{$pi['basename']}";
			   $log[] = "Will ftp fetch {$remote_path} into {$dst_path}";
			   $e = $ftp->read_file($dst_path,$remote_path);
			   if ($e===false) {
			      $log[] = hda_db::hdadb()->HDA_DB_autoLog($profile, $receipts[$profile][] = "Ticket FTP: {$ftp->last_error}", true); 
			      }
			   else {
                  $ft = $ftp->get_datetime($remote_path);
			      $dates[$pi['basename']] = hda_db::hdadb()->PRO_DB_DateTime($ft);
			      $source_files[$pi['basename']] = $remote_path;
				  $receipts[$profile][] = "Collected file {$f}";
				  }
			   }
			}
		 $ftp->close();
	     break;
	  case 'FILE':
	     $profiles = array();
	     $source = $def['Table'];
		 $source .= ($xticket['DATEDIR']==1)?("/{$date_dir}"):"";
		 $s = glob("{$source}/*");
         if ($s===false) { $log[] = "Failed FILE MAP get profile list, {$source}"; return false; }
		 $dir = "tmp/collections";
		 if (!@file_exists($dir)) @mkdir($dir);
		 $dir .= "/{$date_dir}";
		 if (!@file_exists($dir)) @mkdir($dir);
		 foreach ($s as $f) {
			if ($f=='.' || $f=='..') continue;
			$profiles[] = $fn = pathinfo($f, PATHINFO_FILENAME);
			if (!@file_exists("{$dir}/{$fn}")) {
                    @mkdir("{$dir}/{$fn}");
                    }
		    }
		 foreach ($profiles as $profile) {
            $s = glob("{$source}/{$profile}/*.*");
            if ($s===false) { $log[] = hda_db::hdadb()->HDA_DB_autoLog($profile, "Failed FILE MAP get profile files, {$source} {$profile}", true); return false; }
		    foreach ($s as $f) {
			   if ($f=='.' || $f=='..') continue;
		       $pi = pathinfo($f);
			   if (!@file_exists($target_dir = "{$dir}/{$profile}")) @mkdir($target_dir);
			   $target_file = "{$target_dir}/{$pi['basename']}";
               $ft = max(fileatime($f),filectime($f));
			   $dates[$pi['basename']] = hda_db::hdadb()->PRO_DB_DateTime($ft);
			   if (@rename($f, $target_file)===false) {
			      $log[] = hda_db::hdadb()->HDA_DB_autoLog($profile, $receipts[$profile][] = "Fail in FILE MAP file collect (rename) from {$f} to {$target_file}", true); 
			      }
			   else $source_files[$pi['basename']] = $f;
			   }
			}
	     break;
	  default: $log[] = "Unknown collection method"; return false;
	  }
   // Do archive
   if (!is_null($arch_to)) _replicate_fs($log, $dir, null, $arch_to, false);
   // Check for tickets here
   $profiles_here = array(); foreach($profiles as $profile) {
		$profile_id = hda_db::hdadb()->HDA_DB_lookUpProfile($profile);
		$t_read = hda_db::hdadb()->HDA_DB_getTickets(null, $profile_id);
		if (is_array($t_read) && count($t_read)>0) $profiles_here[] = $profile; // Handle ticket here
		}
   // Check for pass through
   if (strlen($passes = $xticket['PASSTHROUGH'])>0) {
       $passes = explode(',',$passes);
	   $log[] = "Will pass through unknown tickets to ".print_r($passes, true);
	   $clean_pass_files = array();
	   foreach ($passes as $pass) {
          $a = hda_db::hdadb()->HDA_DB_dictionary(NULL, $pass);
          if (!is_null($a) && is_array($a) && count($a)==1) {
             $def = $a[0]['Definition'];
             switch ($def['Connect Type']) {
			    case 'FTP':
				    $log[] = "Will replicate into FTP {$pass}";
					$ftp = new HDA_FTP(); $ftp->ftp_mode = FTP_BINARY;
					$e = $ftp->lookupDictionary($pass);
					if ($e===false) $log[] = "Failed to pass through ticket on FTP {$pass}";
					else {
						$e = $ftp->open();
						$ftp->ftp_mode = FTP_BINARY;
						foreach ($profiles as $profile) if (!in_array($profile,$profiles_here)) {
						   $e = $ftp->to_dst_dir();
						   $e = $ftp->make_dir($date_dir);
						   $e = $ftp->to_dst_dir($date_dir);
						   $e = $ftp->make_dir($to_dir = "{$profile}");
						   if ($e===false) $log[] = hda_db::hdadb()->HDA_DB_autoLog($profile, $receipts[$profile][] = "Failed to makedir {$profile} in {$ftp->on_dir}", true);
						   $e = $ftp->to_dst_dir($to_dir);
						   $log[] = hda_db::hdadb()->HDA_DB_autoLog($profile, $receipts[$profile][] = "Now current dir {$ftp->on_dir}", true);
						   $from_files = glob("{$dir}/{$profile}/*.*");
						   foreach ($from_files as $from_file) {
						      $log[] = hda_db::hdadb()->HDA_DB_autoLog($profile, $receipts[$profile][] = "Will pass on {$from_file} to {$ftp->on_dir}", true);
							  $e = $ftp->write_file($from_file, "{$ftp->on_dir}/".pathinfo($from_file,PATHINFO_BASENAME));
							  if ($e===false) $log[] = hda_db::hdadb()->HDA_DB_autoLog($profile, $receipts[$profile][] = "Failed to write pass on {$pass} {$from_file}", true);
							  else {
							     $log[] = "Replicated {$from_file} to ".pathinfo($from_file,PATHINFO_BASENAME)." in {$ftp->on_dir}";
								 }
							  }
						   }
						}
					$ftp->close();
					break;
				case 'FILE':
				    $log[] = "Will replicate into MAP {$pass}";
					$to_base_dir = $def['Table'];
					foreach ($profiles as $profile) if (!in_array($profile,$profiles_here)) {
					    $to_dir = "{$to_base_dir}/{$date_dir}";
						$e = true;
						if (!file_exists($to_dir)) $e &= @mkdir($to_dir);
						$to_dir = "{$to_dir}/{$profile}";
						if (!file_exists($to_dir)) $e &= @mkdir($to_dir);
						if ($e===false) $log[] = hda_db::hdadb()->HDA_DB_autoLog($profile, $receipts[$profile][] = "Failed to makedir {$profile} in {$to_dir}", true);
						$from_files = glob("{$dir}/{$profile}/*.*");
						foreach ($from_files as $from_file) {
							$log[] = "Replicates {$from_file} to directory {$to_dir}";
							$e = @copy($from_file, "{$to_dir}/".pathinfo($from_file,PATHINFO_BASENAME));
							if ($e===false) $log[] = hda_db::hdadb()->HDA_DB_autoLog($profile, $receipts[$profile][] = "Failed to write pass on {$pass} {$from_file}", true);
							}
						}
					break;
				}
			}
		else $log[] = "Fails to find global entry {$pass} to replicate to";
	    }
	  }
   foreach ($profiles as $profile) if (!in_array($profile, $profiles_here)) {
      $files = glob("{$dir}/{$profile}/*.*");
	  foreach ($files as $file) @unlink($file);
      }
   $profiles = $profiles_here;
	  
   foreach ($profiles as $profile) {
      $pdir = "{$dir}/{$profile}";
      $zips = glob("{$pdir}/*.zip");
	  foreach ($zips as $zip) {
	     $pack = HDA_unzip($zip, $profile, $problem, $pdir);
		 $log[] = hda_db::hdadb()->HDA_DB_autoLog($profile, $receipts[$profile][] = "Ticket uploaded a zip file {$zip}: {$problem}", true);
		 foreach($pack as $in_pack) {
		     $pi = pathinfo($in_pack['Path']);
			 $dates[$pi['basename']] = $dates[pathinfo($zip,PATHINFO_BASENAME)];
		    }
		 @unlink($zip);
	     }
      }
   foreach ($profiles as $profile) {
      $pdir = "{$dir}/{$profile}";
      $tickets = glob("{$pdir}/*.tkt");
      foreach($tickets as $ticket) {
	     $path_info = pathinfo($ticket);
		 $tcrc = file_get_contents($ticket);
		 $tcrc = _link_convert($tcrc);
		 $tcrc = explode('|',$tcrc);
		 $file = "{$pdir}/{$path_info['filename']}";
		 if (count($tcrc)!=4) {
		    $log[] = hda_db::hdadb()->HDA_DB_autoLog($profile, $receipts[$profile][] = "Badly formed ticket file in {$ticket}", true);
			$crcv = null;
			}
		 elseif (!@file_exists($file)) {
		    $source_files[$path_info['basename']] = null;
			$source_files[$path_info['filename']] = null;
			$crcv = null;
			}
		 else {
		    $crc = file_get_contents($file);
		    $crcv = 0;
		    for ($i=0; $i<strlen($crc); $i++) $crcv += ord($crc[$i]);
			}
		 if (is_null($crcv) || ($crcv != $tcrc[0])) $log[] = $receipts[$profile][] = "Ticket mismatch {$ticket}, crc error or data file {$file} missing";
		 elseif ($profile != $tcrc[2]) $log[] = $receipts[$profile][] = "Profile mismatch in {$ticket}, {$profile} {$tcrc[2]}";
         else {
		    $profile_id = hda_db::hdadb()->HDA_DB_lookUpProfile($profile);
		    $t_read = hda_db::hdadb()->HDA_DB_getTickets(null, $profile_id);
			if (!is_array($t_read) || count($t_read)<1) 
			   $log[] = hda_db::hdadb()->HDA_DB_autoLog($profile, $receipts[$profile][] = "Unable to locate valid registered ticket for profile ({$profile} {$profile_id}) ").hda_db::hdadb()->HDA_DB_TitleOf($profile_id)." ".print_r($t_read,true);
			else {
			  $valid_user = null;
			  foreach ($t_read as $row) if ($row['UserName']==$tcrc[3]) $valid_user = $row;
			  if (!is_null($valid_user)) {
				  $effective_date = (array_key_exists($path_info['filename'],$dates))?$dates[$path_info['filename']]:hda_db::hdadb()->PRO_DB_dateNow();
				  HDA_DataFilesToQ($profile_id, $file, 'TICKET', $this_logged, $comment="Collected from ticket {$valid_user['ItemId']}", $valid_user['ItemId'], $effective_date, $ticket);
				  $this_logged .= "Registering collected file {$path_info['filename']} uploaded by {$tcrc[3]}, effective date {$effective_date}\n";
				  $log[] = hda_db::hdadb()->HDA_DB_autoLog($profile, $receipts[$profile][] = $this_logged);
				  hda_db::hdadb()->HDA_DB_useTicket($profile_id, $valid_user['ItemId'], array($this_logged));
				  }
			   else 
			      $log[] = hda_db::hdadb()->HDA_DB_autoLog($profile, $receipts[$profile][] = "Unable to validate ticket for ({$profile} {$profile_id}) sent by {$tcrc[3]}").hda_db::hdadb()->HDA_DB_TitleOf($profile_id)." ".print_r($t_read,true);
			   }
		    }
		 HDA_LogOnly($log, 'TICKET');
	     @unlink($ticket);
		 if (@file_exists($file)) @unlink($file);
	     }
      }
   switch ($def['Connect Type']) {
      case 'FTP':
	     $ftp = new HDA_FTP(); $ftp->ftp_mode = FTP_BINARY;
		 $e = $ftp->lookupDictionary(trim($xticket['COLLECT_POINT']));
		 if ($e===false) { $log[] = "Fails to initialize FTP from collection glob {$xticket['COLLECT_POINT']}"; return false; }
		 $e = $ftp->open();
		 $ftp->to_dst_dir();
	     $target = $ftp->on_dir;
		 $target .= ($xticket['DATEDIR']==1)?("/{$date_dir}"):"";
		 $e = $ftp->to_dst_dir($date_dir);
		 foreach ($source_files as $fileid=>$remote_path) if (!is_null($remote_path)) {
		    $e = $ftp->delete($remote_path);
			if ($e===false) $log[] = "Ticket FTP, {$ftp->last_error}, fails delete {$remote_path} at {$ftp->on_dir}";
			}
	     $target = "{$xticket['BASEDIR']}";
		 $ftp->ftp_dir = $target;
		 $e = $ftp->to_dst_dir();
		 if ($e===false) { $log[] = "Ticket FTP: {$ftp->last_error}"; $ftp->close(); return false; } 
         $target = "receipts";
		 $e = $ftp->make_dir($target);
	     if ($e===false) $log[] = "Ticket FTP, {$ftp->last_error}, receipts fails to make {$target} at {$ftp->on_dir}";
	     foreach($receipts as $profile=>$p) {
		    $s = hda_db::hdadb()->PRO_DBtime_Styledate(time(),true)."\r\n";
		    foreach($p as $ss) $s .= "{$ss}\r\n";
		    $ftp->make_dir($to_file = "{$target}/{$profile}");
		    $to_file .= "/receipt.log";
		    $tempHandle = fopen('php://temp', 'r+');
		    @fwrite($tempHandle, $s);
		    @rewind($tempHandle);        
		    $ftp->write_file_stream($tempHandle, $to_file);
		    }
		 $ftp->close();
		 break;
	  }
  
   return true;
   }
   
function HDA_ReplicateCollection(&$log, $xcollect, $arch_to, $and_delete=true) {
   $log = array();
   $log[] = "Will replicate collections to {$xcollect['FSSITES']}";
   $site_list = $xcollect['FSSITES'];
   $s = explode(',',$site_list);
   $sites = array();
   foreach($s as $site) {
      $site = trim($site);
      if (strlen($site)>0) {
	     $a = hda_db::hdadb()->HDA_DB_dictionary(NULL, $site);
         if (is_array($a) && count($a)==1) {
		    $def = $a[0]['Definition'];
	        $sites[] = array($site, $def['Table']);
			}
		 }
	  }
   if (count($sites)==0) {
      $log[] = "No valid or defined global sites to replicate to";
	  return false;
      }
   $dates = array();
   $source_files = array();
   $date_dir = date('Ymd',time());
   $delete_sources = ((array_key_exists('CLEANUP',$xcollect)) && ($xcollect['CLEANUP']==1));
   $source = $xcollect['COLLECT_POINT'];
   $source .= ($xcollect['DATEDIR']==1)?("/{$date_dir}"):"";
   $log[] = "Collecting from {$source}";
   $did_replicate = true;
   $collection = array();
   $fn = date('Ymd'); $fdate = date('Y-m-d G:i'); $fh = fopen("tmp/replicate_log_{$fn}.log", 'a');
   $site_dirs = array();
	foreach ($sites as $site) {
		$to = $site[1];
		$to .= ($xcollect['DATEDIR']==1)?("/{$date_dir}"):"";
		$site_dirs[] = $to;
	}
   _replicate_to_sites($site_dirs, $source, $arch_to, $and_delete, $fh, $fdate);
   if (($n = _collect_fs($collection, $source, $fh, $fdate))>0) {
		fwrite($fh, "{$fdate}: Will process {$n} collected files\n");
		$this_arch = $arch_to;
		foreach ($sites as $site) {
			$to_dir = $site[1];
			$to_dir .= ($xcollect['DATEDIR']==1)?("/{$date_dir}"):"";
			if (!file_exists($to_dir)) @mkdir($to_dir);
			$did_replicate &= _replicate_fs($collection, $source, $to_dir, $this_arch, false, $fh, $fdate);
			$this_arch = null;
			}
		if ($did_replicate && $and_delete) _replicate_fs($collection, $source, null, null, $and_delete, $fh, $fdate);
		}
   fclose($fh);
   return $did_replicate;
   }
   
function _replicate_to_sites($site_dirs, $from, $arch_to, $and_delete, $fh, $fdate) {
	$ff = glob("{$from}/*");
	foreach ($ff as $f) {
		if (is_dir($f)) {
			$next_dirs = array();
			foreach ($site_dirs as $to) {
				if (!file_exists($to)) @mkdir($to);
				$to_next = "{$to}\\".pathinfo($f,PATHINFO_BASENAME);
				if (!file_exists($to_next)) {
					fwrite($fh, "{$fdate}: REP: Replicate directory {$f} to {$to_next}\n");
					if (!@mkdir($to_next)) fwrite($fh, "{$fdate}: REP: Fails to make directory in destination {$to_next}\n");
					}
				if (!is_null($arch_to)) {
					$arch_to_next = "{$arch_to}\\".pathinfo($f,PATHINFO_BASENAME);
					if (!file_exists($arch_to_next) && !@mkdir($arch_to_next)) { 
						fwrite($fh, "{$fdate}: REP: Fails to make ARCHIVE directory in {$arch_to_next}\n"); 
						$arch_to_next = null; 
						}
				}
				$next_dirs[] = $to_next;
			}
			_replicate_to_sites($next_dirs, $f, $arch_to_next, $and_delete, $fh, $fdate);
		}
		else {
			try {
				$fsize = filesize($f); 
				fwrite($fh, "{$fdate}: REP: Will collect {$f} size {$fsize} from {$from}\n");
				foreach ($site_dirs as $to) {
					fwrite($fh, "{$fdate}: REP: Will Copy {$f} to {$to} size {$fsize}\n");
					$this_copy = @chunked_copy($f, $to_f = "{$to}\\".pathinfo($f,PATHINFO_BASENAME), $fsize, $fh);
					if (!$this_copy) fwrite($fh, "{$fdate}: REP: FAILS Copy {$f} to {$to_f} size {$fsize}\n");
					else fwrite($fh, "{$fdate}: REP: Copied {$f} to {$to_f} size {$fsize}\n");
				}
				if (!is_null($arch_to)) {
					@chunked_copy($f, $to_f = "{$arch_to}/".pathinfo($f,PATHINFO_BASENAME), $fsize, $fh);
					fwrite($fh, "{$fdate}: REP: Archived Copy {$f} to {$to_f}\n");
				}
				if ($and_delete) {
					@unlink($f);
					fwrite($fh, "{$fdate}: REP: Deleted {$f} \n");
				}
			}
			catch (Exception $e) {
				fwrite($fh, "{$fdate}: REP: Fails access to {$f} from {$from}\n");
			}
		}
	}

}
   
function _collect_fs(&$collection, $from, $fh, $fdate) {
	$fcount = 0;
	$ff = glob("{$from}/*");
	$collection[$from] = $ff;
	foreach ($ff as $f) {
		if (is_dir($f)) $fcount += _collect_fs($collection, $f, $fh, $fdate);
		else {
			try {
				$fsize = filesize($f); 
				$collection[$from][] = $f;
				fwrite($fh, "{$fdate}: Will collect {$f} size {$fsize} from {$from}\n");
				$fcount++;
			}
			catch (Exception $e) {
				fwrite($fh, "{$fdate}: Fails access to {$f} from {$from}\n");
			}
		}
	}
    return $fcount;
}


   
function _replicate_fs(&$collection, $from, $to, $arch_to, $and_delete, $fh, $fdate) {
   $did_copy = true;
   $ff = null;
   if (!array_key_exists($from, $collection)) fwrite($fh, "{$fdate}: Collection list invalid - missing {$from}\n");
   else $ff = $collection[$from];
   if (is_array($ff)) foreach ($ff as $f) {
      if (is_dir($f)) {
	     if (!is_null($to)) {
	        $to_next = "{$to}\\".pathinfo($f,PATHINFO_BASENAME);
		    if (!file_exists($to_next)) {
				fwrite($fh, "{$fdate}: Replicate directory {$f} to {$to_next}\n");
				if (!@mkdir($to_next)) fwrite($fh, "{$fdate}: Fails to make directory in destination {$to_next}\n");
				}
			}
		 else $to_next = null;
		 if (!is_null($arch_to)) {
		    $arch_to_next = "{$arch_to}\\".pathinfo($f,PATHINFO_BASENAME);
		    if (!file_exists($arch_to_next) && !@mkdir($arch_to_next)) { 
				fwrite($fh, "{$fdate}: Fails to make ARCHIVE directory in {$arch_to_next}\n"); 
				$arch_to_next = null; 
				}
			}
	     else $arch_to_next = null;
	     $did_copy &= _replicate_fs($collection, $f, $to_next, $arch_to_next, $and_delete, $fh, $fdate);
		 }
	  else {
	     if (!is_null($to_f = $to)) {
		    $fsize = filesize($f); 
			fwrite($fh, "{$fdate}: Copy {$f} ({$fsize} bytes) to {$to_f}\n");
			try {
	           $this_copy = @chunked_copy($f, $to_f = "{$to}\\".pathinfo($f,PATHINFO_BASENAME), $fsize, $fh);
			   if (!$this_copy) fwrite($fh, "{$fdate}: FAILS Copy {$f} to {$to_f}\n");
			   $did_copy &= $this_copy;
			   }
			catch (Exception $e) {
			   fwrite($fh, "{$fdate}: FAILS EXCEPTION Copy {$f} to {$to}\n");
			   }
			fwrite($fh, "{$fdate}: Completed Copy {$f} to {$to_f}\n");
			}
		 if (!is_null($arch_to)) {
		    $fsize = filesize($f); 
		    @chunked_copy($f, $to_f = "{$arch_to}/".pathinfo($f,PATHINFO_BASENAME), $fsize, $fh);
			fwrite($fh, "{$fdate}: Archived Copy {$f} to {$to_f}\n");
			}
			if ($and_delete) @unlink($f);
		 if ($did_copy && $and_delete && @file_exists($f)) {
			@unlink($f);
			fwrite($fh, "{$fdate}: DELETED {$f}\n");
			}
		 }
      }
   return $did_copy;
   }   
   
function chunked_copy($src, $dst, $fsize, $fh_log) {
    # write with buffer, 1 meg at a time, adjustable.
	if ($fsize < 10000000) {
		return @copy($src, $dst);
	}
    $buffer_size = 1048576; 
	try {
	fwrite($fh_log, "Will copy in chunks {$src} size {$fsize}\n");
    $fin = fopen($src, "rb"); #source
    $fout = fopen($dst, "w"); #destination
    while(!feof($fin)) {
		$blk = fread($fin, $buffer_size);
		if ($blk===false) {
			fwrite($fh_log, "Chunk copy fails, read fails on {$src}");
			return false;
			}
        $wrote = fwrite($fout, $blk);
		if ($wrote===false) {
			fwrite($fh_log, "Chunk copy fails, write fails on {$dst}");
			return false;
			}
    }
    fclose($fin);
    fclose($fout);
	}
	catch(Exception $e) {
	fwrite($fh_log, "Chunk copy exception ".$e->getMessage()." \n");
	}
    return true;
}   
   
   
   
   
function _tidy_collection_archive($months_ago=1) {
	$log = array();
	$xcollect = hda_db::hdadb()->HDA_DB_admin('ExternalCollect');
	$date_ago = strtotime("{$months_ago} month ago");
	if (!is_null($xcollect)) $xcollect = hda_db::hdadb()->HDA_DB_unserialize($xcollect); else { $log[] = "External Collection Not Enabled"; return false; }
	// Check for Archiving
	$arch_to = null;
	if (array_key_exists('FSARCH',$xcollect)&&$xcollect['FSARCH']=='ARCH') {
		$a = hda_db::hdadb()->HDA_DB_dictionary(NULL, trim($xcollect['FSARCH_POINT']));
		if (is_array($a) && count($a)==1) {
			$def = $a[0]['Definition'];
			$arch_to = trim($def['Table']);
			$dd = glob("{$arch_to}/*");
			foreach($dd as $d) {
				$dir_date = pathinfo($d, PATHINFO_BASENAME);
				if (preg_match("/^[\d]{8,8}$/",$dir_date)) {
					if (strtotime($dir_date)<$date_ago) {
						_rrmdir("{$arch_to}/{$dir_date}");
						$log[] = $dir_date;
						}
					}
				}
			}
		}
	return $log;
	}

function HDA_ExternalCollection(&$log) {
   $log = array();
   $xcollect = hda_db::hdadb()->HDA_DB_admin('ExternalCollect');
   $date_dir = date('Ymd',time());
   if (!is_null($xcollect)) $xcollect = hda_db::hdadb()->HDA_DB_unserialize($xcollect); else { $log[] = date('G:i: ')."External Collection Not Enabled"; return false; }
   // Check for Archiving
   $arch_to = null;
   if (array_key_exists('FSARCH',$xcollect)&&$xcollect['FSARCH']=='ARCH') {
	  $a = hda_db::hdadb()->HDA_DB_dictionary(NULL, trim($xcollect['FSARCH_POINT']));
      if (is_array($a) && count($a)==1) {
		 $def = $a[0]['Definition'];
	     $arch_to = trim($def['Table']);
		 $arch_to .= "/{$date_dir}";
		 if (!file_exists($arch_to)) @mkdir($arch_to);
	     }
      }
   // Deal with FS Mode and Replicate
   if (array_key_exists('FSMODE',$xcollect)&&$xcollect['FSMODE']=='FS') return HDA_ReplicateCollection($log, $xcollect, $arch_to, true);
   // Collect Here
   $profiles = array();
   $dates = array();
   $source_files = array();
   $delete_sources = ((array_key_exists('CLEANUP',$xcollect)) && ($xcollect['CLEANUP']==1));
   
   $a = hda_db::hdadb()->HDA_DB_autoCollect(null, null, true);
   if (!is_array($a) || count($a)==0) { $log[] = date('G:i: ')."No profiles required for auto collect"; return false; }
   
   switch ($xcollect['COLLECT']) {
      case 'FTP':
	     $ftp = new HDA_FTP();
		 $ftp->setHost($xcollect['URL']);
		 $ftp->username = $xcollect['UNAME'];
		 $ftp->pw = $xcollect['PW'];
	     $from_dir = "{$xcollect['BASEDIR']}";
		 $to_date_dir = ($xcollect['DATEDIR']==1)?("/{$date_dir}"):"";
		 $from_dir .= $to_date_dir;
		 $ftp->ftp_dir = $from_dir;
		 $e = $ftp->open();
		 if ($e===false)  { $log[] = date('G:i: ')."XCollect FTP: {$ftp->last_error}"; break; }
		 $e = $ftp->to_dst_dir();
		 if ($e===false)  { $log[] = date('G:i: ')."XCollect FTP: {$ftp->last_error}"; break; }
		 $to_dir = "tmp/collections";
		 if (!@file_exists($to_dir)) @mkdir($to_dir);
		 $to_dir .= $to_date_dir;
		 if (!@file_exists($to_dir)) @mkdir($to_dir);
		 $ftp->ftp_mode = FTP_BINARY;
		 $ftp->delete_after_read = false;
		 foreach ($a as $row) {
		    if (!_expectDataFor($row['ItemId'], $log)) { continue; }
			//
		    $ff = $ftp->nlist("{$row['ItemText']}");
			if ($ff===false) continue;
			if (!@file_exists("{$to_dir}/{$row['ItemId']}")) @mkdir("{$to_dir}/{$row['ItemId']}");
			foreach ($ff as $f) {
			   if ($f=='.' || $f=='..') continue;
		       $pi = pathinfo($f);
			   $home_path = make_version_of($home_path = "{$to_dir}/{$row['ItemId']}/{$pi['basename']}");
			   $e = $ftp->read_file($home_path, $remote_path = "{$row['ItemText']}/{$pi['basename']}");
			   if ($e===false) {
			      $log[] = date('G:i: ').hda_db::hdadb()->HDA_DB_autoLog($row['ItemId'], "Fail in file collect {$f}: {$ftp->last_error}", true); 
			      }
			   else {
                  $ft = $ftp->get_datetime($remote_path);
			      $dates[$pi['basename']] = hda_db::hdadb()->PRO_DB_DateTime($ft);
			      $source_files[$row['ItemId']][] = array($home_path, $remote_path);
                  }				  
			   }
			}
		 $ftp->close();
		 break;
	  case 'MAP':
	     clearstatcache();
	     set_time_limit(0);
         sleep(1); // windows issue, due to order of closing handles, see PHP manual page for rename
	     $source = $xcollect['COLLECT_POINT'];
		 $source .= ($xcollect['DATEDIR']==1)?("/{$date_dir}"):"";
		 $log[] = date('G:i: ')."Collecting from {$source}";
		 $to_dir = "tmp/collections";
		 if (!@file_exists($to_dir)) @mkdir($to_dir);
		 $to_dir .= "/{$date_dir}";
		 if (!@file_exists($to_dir)) @mkdir($to_dir);
		 foreach ($a as $row) {
		    if (!_expectDataFor($row['ItemId'], $log)) { continue; }
			if (!@file_exists("{$to_dir}/{$row['ItemId']}")) @mkdir("{$to_dir}/{$row['ItemId']}");
		    $ff = glob("{$source}/{$row['ItemText']}/*");
			foreach ($ff as $f) {
			   if ($f=='.' || $f=='..') continue;
		       $pi = pathinfo($f);
			   $home_path = make_version_of($home_path = "{$to_dir}/{$row['ItemId']}/{$pi['basename']}");
               $ft = max(fileatime($f),filectime($f));
			   $dates[$pi['basename']] = hda_db::hdadb()->PRO_DB_DateTime($ft);
	           clearstatcache();
	           set_time_limit(0);
               sleep(1); // windows issue, due to order of closing handles, see PHP manual page for rename
			   try {
			      if ($delete_sources) {
				     if (@file_exists($home_path)) $log[] = date('G:i: ').hda_db::hdadb()->HDA_DB_autoLog($row['ItemId'], "Collecting {$home_path} file already exists, rename will fail", true);
			         $moved = @rename($f, $home_path);
					 if (!$moved) {
			            $log[] = date('G:i: ').hda_db::hdadb()->HDA_DB_autoLog($row['ItemId'], "Fail in FILE MAP file collect (rename) from {$f} to {$home_path}", true); 
			            $moved = @copy($f, $home_path);
						if (!$moved) {
			               $log[] = date('G:i: ').hda_db::hdadb()->HDA_DB_autoLog($row['ItemId'], "Fail in FILE MAP file collect (copy) from {$f} to {$home_path}", true); 
			               }
				        else $source_files[$row['ItemId']][] = array($home_path, $f);
			            }
				     else $source_files[$row['ItemId']][] = array($home_path, $f);
				     }
			      else {
				     if (@file_exists($home_path)) $log[] = date('G:i: ').hda_db::hdadb()->HDA_DB_autoLog($row['ItemId'], "Collecting {$home_path} file already exists, copy will fail", true);
			         $moved = @copy($f, $home_path);
					 if (!$moved) {
			            $log[] = date('G:i: ').hda_db::hdadb()->HDA_DB_autoLog($row['ItemId'], "Fail in FILE MAP file collect (copy) from {$f} to {$home_path}", true); 
			            }
				     else $source_files[$row['ItemId']][] = array($home_path, $f);
			         }
			      }
			   catch (Exception $e) {
			      hda_db::hdadb()->HDA_DB_autoLog($row['ItemId'], "Collect exception (copy,rename) ".$e->getMessage(),true);
			      }
			   }
			}
	     break;
	  default: $log[] = date('G:i: ')."Unknown collection method"; return false;
	  }
   // Trigger Here:
   foreach ($source_files as $item=>$files) {
      foreach($files as $file_set) {
	     $path_info = pathinfo($file_set[0]);
		 $effective_date = (array_key_exists($path_info['basename'],$dates))?$dates[$path_info['basename']]:hda_db::hdadb()->PRO_DB_dateNow();
		 HDA_LogThis("Trigger {$item} for file {$file_set[0]}", "DETECT");
         if (HDA_DataFilesToQ($item, $file_set[0], 'DETECT', $problem, $comment="Collected from External Collection", $user=NULL, $effective_date, $file_set[1])) {	 
		    $log[] = date('G:i: ')."{$problem}";
		    $log[] = date('G:i: ')."Registering collected file {$file_set[1]}, effective date {$effective_date}";
		    if (@file_exists($file_set[0])) @unlink($file_set[0]);
		    $log[] = date('G:i: ').hda_db::hdadb()->HDA_DB_autoLog($item, "Collected {$file_set[1]} into {$file_set[0]} for profile ".hda_db::hdadb()->HDA_DB_TitleOf($item), true);
			}
		 else $log[] = date('G:i: ').hda_db::hdadb()->HDA_DB_autoLog($item, "Fails to add to Q {$file_set[1]} as {$file_set[0]} for profile ".hda_db::hdadb()->HDA_DB_TitleOf($item)." because {$problem}", true);
		 }
	  }
	if ($delete_sources) {
	   clearstatcache();
	   set_time_limit(0);
       sleep(1); // windows issue, due to order of closing handles, see PHP manual page for rename
       switch ($xcollect['COLLECT']) {
         case 'FTP':
			 $ftp = new HDA_FTP();
			 $ftp->setHost($xcollect['URL']);
			 $ftp->username = $xcollect['UNAME'];
			 $ftp->pw = $xcollect['PW'];
			 $from_dir = "{$xcollect['BASEDIR']}";
			 $from_dir .= ($xcollect['DATEDIR']==1)?("/{$date_dir}"):"";
			 $ftp->ftp_dir = $from_dir;
			 $e = $ftp->open();
			 if ($e===false)  { $log .= "XCollect FTP: delete source :  {$ftp->last_error}\n"; break; }
			 $e = $ftp->to_dst_dir();
			 if ($e===false)  { $log .= "XCollect FTP: delete source :  {$ftp->last_error}\n"; break; }
			 foreach ($source_files as $item=>$files) if (is_array($files)) foreach ($files as $file_set) {
			    if (is_array($file_set)&&!is_null($file_set[1])) {
			       $log[] = date('G:i: ')."Will delete {$item} {$file_set[1]}";
			       $e = $ftp->delete($file_set[1]);
				   if ($e===false) { $log[] = date('G:i: ')."XCollect FTP : delete source {$item}: {$file_set[1]} : {$ftp->last_error}"; }
				   }
				}
			 $ftp->close();
		    break;
		  case 'MAP':
			foreach ($source_files as $item=>$files) {
			   if (is_array($files)) foreach ($files as $file_set) {
			      if (is_array($file_set)&&@file_exists($file_set[1])) {
				     try {
					    $log[] = date('G:i: ')."Delete {$file_set[1]}";
				        @unlink($file_set[1]);
						if (file_exists($file_set[1])) $log[] = date('G:i: ')."No delete of {$file_set[1]}";
						}
					 catch (Exception $e) {
					    $log[] = date('G:i: ').hda_db::hdadb()->HDA_DB_autoLog($item, "Fails to delete collected file {$file_set[1]}: ".$e->getMessage(), true);
					    }
					 }
				  }
			   }
		    break;
	      }
	   }
   $dstamp = date("Ymd"); $filename = "ErrorLogs\collect_{$dstamp}.log";
   $fh = fopen($filename,'a');
   fwrite($fh, log_to_string($log, "\n"));
   fclose($fh);
   return true;
   }
  
   
?>