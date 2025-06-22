<?php

// Global Function List
global $HDA_code_functions;
$HDA_code_functions = array (
					'EXEC','EXEC_RW','EXEC_OUTPUT', 'EXEC_STATUS', 'EXEC_LAST_ERROR', 'LOOKUP_DICTIONARY',
					'FTP_FILE_WRITE','FTP_WRITE','FTP_READ','PSFTP_READ',
					'SSH2_CONNECT','SSH2_AUTH_HOSTBASED_FILE','SSH2_AUTH_PUBKEY_FILE','SSH2_AUTH_NONE','SSH2_AUTH_PASSWORD',
					'SSH2_SFTP','SFTP_READFILE',
					'RSS_READ','RSS_WRITE',
					'EXCEL_OPEN','EXCEL_RESTRICT_ROWS','EXCEL_RESTRICT_COLUMNS','EXCEL_LOAD','EXCEL_RESTRICT_SHEET',
					'EXCEL_ARRAY','EXCEL_SHEET','EXCEL_CELL','EXCEL_DATE','EXCEL_CLOSE',
					'SQL_LAST_ERROR',
					'MSSQL_CONNECT','MSSQL_QUERY','MSSQL_FETCH_ROW','MSSQL_ROW_COUNT','MSSQL_CLOSE',
					'ORCL_CONNECT','ORCL_QUERY','ORCL_FETCH_ROW','ORCL_ROW_COUNT','ORCL_CLOSE',
					'ODBC_CONNECT','ODBC_QUERY','ODBC_FETCH_ROW','ODBC_ROW_COUNT','ODBC_CLOSE',
					'MAKE_INSERT','COPY_TABLE','QUERY_TABLE',
					'STRING_REPLACE','STRING_LOWER','STRING_UPPER','STRING_LENGTH','SUB_STRING','MB_SUB_STRING','STRING_CHAR','STRING_SPLIT','TRIM','STRING_GETCSV','SPRINTF',
					'HTML_SPECIAL_CHARS','HTML_SPECIAL_CHARS_DECODE',
					'PREG_MATCH','PREG_MATCH_ALL','PREG_SPLIT','PREG_REPLACE','HTML_STRIP_TAGS',
					'URLENCODE','URLDECODE','MD5_RECORD',
					'IS_STRING','IS_NULL',
					'ROUND','CEIL','FLOOR','SORT',
					'OPEN_CHECKSUM','APPEND_CHECKSUM','CLOSE_CHECKSUM','WRITE_CHECKSUM','READ_CHECKSUM',
					'XML_TO_ARRAY','GET_XML_RECORD', 'MT940_TO_ARRAY',
					'ARRAY_TO_CSV','ARRAY_KEYS','COUNT_PROPERTIES','IN_ARRAY',
					'WORKING_DIR','WORKING_DIR_PATH','BIN_DIR_PATH','MAKE_DIRECTORY','MAKE_DIRECTORY_PATH','FETCH_TEMPLATE','GET_FILE_PATHS','PATH_INFO',
					'FILE_GET_CONTENTS','FILE_PUT_CONTENTS','FILE_EXISTS','FILE_PATH_EXISTS','FILE_DELETE',
					'FILE_OPEN_READ','FILE_READ_LINE','FILE_READ_CLOSE','FILE_CSV_LINE',
					'FILE_OPEN_WRITE','FILE_WRITE_LINE','FILE_WRITE_CLOSE','COPY_FILE','LOOKUP_FILE','MOVE_FILE',
					'UNZIP',
					'TEXT_PROCESSOR',
					'TIME','STYLE_DATETIME','STRING_TO_TIME','DATE','EFFECTIVE_DATE','DB_DATETIME','DAYS_AGO','HOURS_AGO',
					'NEXT_SCHEDULED','RESCHEDULE',
					'VALIDATE','VALUE_ADJUST',
					'CONSOLE','GET_CONSOLE',
					'GUID', 'LOCK_THIS', 'UNLOCK_THIS',
					'LAST_ERROR','ON_ERROR_CLOSE','DUMP','SEND_ERROR',
					'RUNNING_PROCESS','RUNNING_PROCESS_OWNER','PROFILE_CATEGORY','CATEGORY_PROFILES','RUN_REF','TASK_SOURCE','IS_ROLLBACK','IS_RETRY','TASK_DATE','LAST_RUN',
					'DATA_PATH','DATA_FILENAME','DETECT_SOURCE','DETECT_LOOKUP','DETECT_UPLOAD','DETECT_FILE','DETECT_TRIGGER',
					'TRIGGER_PROCESS','TRIGGER_PROCESS_ONCE','FETCH_PROFILES','READY_TO_RUN','MARKER_VALUE','LOG_THIS','EMAIL_THIS',
					'ISSUE_AUDIT', 'ISSUE_ALERT', 'ISSUE_REPORT', 'ISSUE_EVENT', 'GET_EVENTS', 'GET_SUCCESS_EVENT', 'ISSUE_MONITOR', 'LOCAL_LOG','KEEP_ALIVE'
	);


class HDA_library {
private $parent = null;
private $with_debug;
public function __destruct() {
   unset($this->parent);
   $this->parent = null;
   global $glob_mem;
   $glob_mem .= "destruct lib ".memory_get_usage(true)."\n";
   }
public function __construct($parent, $with_debug) {
   global $glob_mem;
   $glob_mem .= "construct lib ".memory_get_usage(true)."\n";
   $this->parent = $parent;
   $this->with_debug = $with_debug;
   }
public $lastRunError = null;
public function _RunTime_Exception($message) {
   $this->lastRunError = $message;
   return false;
   }
public function _do_call_LAST_ERROR($p, $vm) {
   return $this->lastRunError;
   }
public function _do_call_SET_LAST_ERROR($p, $vm) {
	$this->lastRunError = $p[0];
   return $this->lastRunError;
   }

//** CATEGORY EXEC SHELL SYSTEM
public function _do_call_EXEC_BAT($p, $vm) {
   /** EXEC_BAT("cmd"); Execute a batch file command; Return result from exec; **/
   $this->EXEC_output = "";
   $this->EXEC_status = null;
   $this->EXEC_last_error = null;
   if (count($p) <> 1) return $this->_RunTime_Exception($this->EXEC_last_error = "Wrong parameter count for EXEC_BAT(executeCmd)");
   $return = @exec($p[0], $output, $result);
   if (is_array($output)) {
	   foreach ($output as $line) $this->EXEC_output .= $line.PHP_EOL;
   }
   else
      $this->EXEC_output = $output;
   $this->EXEC_status = $result;
   return $return;
   }

public function _do_call_EXEC($p, $vm) {
   /** EXEC("cmd"); Execute a shell command; Return true/false; Get error from EXEC_LAST_ERROR(); **/
   $this->EXEC_output = "";
   $this->EXEC_status = null;
   $this->EXEC_last_error = null;
   if (count($p) <> 1) return $this->_RunTime_Exception($this->EXEC_last_error = "Wrong parameter count for EXEC(executeCmd)");
   $io_descriptors = array(
                           0=>array("pipe",'r'),
                           1=>array("pipe",'w'),
                           2=>array("pipe",'a')
                           );
   $script_filename = $_SERVER['SCRIPT_FILENAME'];
   $root_dir = pathinfo($script_filename,PATHINFO_DIRNAME);
   $working_dir = "{$root_dir}/CUSTOM/{$vm->ref}";
   $handle = @proc_open("'\"\"{$p[0]}\"\"'", $io_descriptors, $pipes, $working_dir);
   if ($handle===false) {
      return $this->_RunTime_Exception($this->EXEC_last_error = "Fails to open command");
      }
   else {
      $status = proc_get_status($handle);
      $the_close = ($status['running'])?null:$status['exitcode'];
      @fclose($pipes[0]);
      $this->EXEC_output .= stream_get_contents($pipes[1]);
      @fclose($pipes[1]);
      $this->EXEC_output .= stream_get_contents($pipes[2]);
      @fclose($pipes[2]);
      $proc_close = @proc_close($handle);
      $this->EXEC_status = (is_null($the_close))?$proc_close:$the_close;
      return true;
      }
   return false;
   }
public function _do_call_EXEC_RW($p, $vm) {
   $this->EXEC_output = "";
   $this->EXEC_status = null;
   $this->EXEC_last_error = null;
   if (count($p) <> 2) return $this->_RunTime_Exception($this->EXEC_last_error = "Wrong parameter count for EXEC(executeCmd, rw_actions)");
   try {
      $script_filename = $_SERVER['SCRIPT_FILENAME'];
      $root_dir = pathinfo($script_filename,PATHINFO_DIRNAME);
      $working_dir = "{$root_dir}/CUSTOM/{$vm->ref}";
	  $log = "{$working_dir}/exec_rw.log";
      $io_descriptors = array(
                           0=>array("pipe",'r'),
                           1=>array("pipe",'w'),
                           2=>array("file",$log,'w')
                           );
      $handle = proc_open("{$p[0]}", $io_descriptors, $pipes, $working_dir);
	  sleep(4);
	  $talk = "";
      if ($handle===false) {
         return $this->_RunTime_Exception($this->EXEC_last_error = "Fails to open command");
         }
      else {
	     $talking = true;
	     $talk_limit = 10;
         while ($talking) {
	        if ($talk_limit-- < 0) {$talking = false; break; }
			$input = null;
		    $say_reply = null;
			if (!feof($pipes[1]) && (($s=fgets($pipes[1]))!==false)) {
			   $s = trim($s);
			   if (strlen($s)==0) break;
			   $input = "{$s}";
			   }
			if (is_null($input) || strlen($input)==0) $talking=false;
			else {
	           $this->EXEC_output .= "Was asked this \"{$input}\"\n";
		       foreach($p[1] as $r=>$w) {
		          if (stripos($r, $input)!==false) {
		             $this->EXEC_output .= "Will answer \"{$w}\"\n";
			         $say_reply = "{$w}";
			         break;
			         }
				   }
			   if (is_null($say_reply)) {
		           $this->EXEC_output .= "Do not know how to reply to \"{$input}\"\n";
			       foreach($p[1] as $r=>$w) {
			          if ($r=='*') {
  			             if (!is_null($w)) {
		                    $this->EXEC_output .= "Default answer \"{$w}\"\n";
			                $say_reply = "{$w}";
					        }
				         break;
			             }
			          }
				   }
			   }
			$talk .= "{$input} ";
			if (!is_null($say_reply) && strlen($say_reply)>0) {
			   $this->EXEC_output .= "Will reply to {$input} with \"{$say_reply}\"\n";
			   fwrite($pipes[0], "{$say_reply}\n");
			   $talk .= "{$say_reply}";
			   }
			$talk .= "\n";
            $status = proc_get_status($handle);
            $the_close = $status['exitcode'];
		    if ($the_close===true) {
		       $this->EXEC_output .= "The program terminated\n";
			   $talking=false;
		       }
	        }
         $status = proc_get_status($handle);
         $the_close = ($status['running'])?null:$status['exitcode'];
         @fclose($pipes[0]);
         @fclose($pipes[1]);
         $proc_close = @proc_close($handle);
         $this->EXEC_status = (is_null($the_close))?$proc_close:$the_close;
		 $this->EXEC_output .= "Conversation was:\n".$talk;
		 $this->EXEC_output .= "\nAdditional output:\n".file_get_contents($log);
	     $this->EXEC_output .= "\nThe program terminated with a close of {$the_close}\n";
         return true;
	     }
	  }
   catch (Exception $e) {
	  $this->EXEC_output .= "Fails with exception {$e}\n";
	  }
   return false;
   }
private $EXEC_last_error = null;
private $EXEC_output = null;
private $EXEC_status = null;
function _do_call_EXEC_LAST_ERROR($p, $vm) {
   /** EXEC_LAST_ERROR(); Collect error from last shell command; Return string;  **/
   return $this->EXEC_last_error;
   }
function _do_call_EXEC_OUTPUT($p, $vm) {
   /** EXEC_OUTPUT(); Collect output from last shell command; Return string;  **/
   return $this->EXEC_output;
   }
function _do_call_EXEC_STATUS($p, $vm) {
   /** EXEC_STATUS(); Collect status from last shell command; Return string;  **/
   return $this->EXEC_status;
   }
public function _do_call_WIN_RUN($p, $vm) {
   /** WIN_RUN("exe", "cmd"); Execute a binary exe command; Return false on fail or return from cmd; ; **/
   if (count($p) <> 2) return $this->_RunTime_Exception("Wrong parameter count for WIN_RUN(bin, executeCmd)");
   try {
      return _win_proc_cmd($p[1], $p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in WIN_RUN: {$e}");
      }
   return false;
   }

// END EXEC




// CDR

public function _do_call_CDR_LOAD($p, $vm) {
   if (!class_exists('cdr_parser')) return "Unable to use CDR LOAD, no library";
   if (count($p) < 2) return $this->_RunTime_Exception("Wrong parameter count for CDR_LOAD(cdr_type, input_file[, action])");
   try {
      $this->_resolve_working_dir($vm);
      if (stripos($p[1], "\\") === false && stripos($p[1],"/")===false) $p[1] = "{$this->WORKING_DIR}/{$p[1]}";
      if (!@file_exists($p[1])) return $this->_RunTime_Exception("In CDR LOADER, unable to locate input file {$p[1]}");
      $s = @file_get_contents($p[1]);
      $cdr = new cdr_parser($p[0], $s);
	  $action = (count($p)>=2)?$p[2]:'CAPTURE';
	  switch ($action) {
	     case 'STRUCTURE':
            $result = $cdr->struct();
            if ($cdr->status !== true) return $this->_RunTime_Exception("CDR LOAD {$p[0]}: {$cdr->status}");
			return $result;
			break;
		 case 'CAPTURE':
            $db = $cdr->capture();
            if ($cdr->status !== true) return $this->_RunTime_Exception("CDR LOAD {$p[0]}: {$cdr->status}");
	        return $db;
			break;
		 case 'SCHEMA':
		    return $cdr->schema();
			break;
		 default: return $this->_RunTime_Exception("CDR LOAD unknown request type {$action}");
		 }
	  }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in CDR_LOAD: {$e}");
      }
   return false;	  
   }
   
private function _CDR_TIME($v) {
   if (preg_match("/(?P<d>[\d]{2,2})\.(?P<m>[\d]{2,2})\.(?P<y>[\d]{2,4})-(?P<g>[\d]{2,2}):(?P<i>[\d]{2,2}):(?P<s>[\d]{2,2})[:|\.]{0,1}(?<f>[\d]{0,4})[+]{0,1}[\d]*/",$v,$matches)) {
      $matches['y'] = (strlen($matches['y'])==2)?"20{$matches['y']}":$matches['y'];
	  $matches['f'] = (!array_key_exists('f',$matches) || (strlen($matches['f'])==0))?"00":$matches['f'];
      return "{$matches['y']}-{$matches['m']}-{$matches['d']} {$matches['g']}:{$matches['i']}:{$matches['s']}";
      }
   return null;
   }
   
//** CATEGORY SYSTEM
   
public function _do_call_CUSTOM_FUNCTION($p, $vm) {
   /** CUSTOM_FUNCTION("function_name"[, params...]); Call internal system function; Return false or result from system function;  **/
   if (count($p) < 1) return $this->_RunTime_Exception("Wrong parameter count for Custom_Function(function_name [, other])");
   try {
      if (!function_exists($p[0])) return $this->_RunTime_Exception("Custom Function unable to find function {$p[0]}");
	  $error = null;
      $ok = call_user_func($p[0], $p, array(&$error));
	  if ($ok===false) return $this->_RunTime_Exception("Fails to run {$p[0]}, {$ok} {$error}");
	  return $ok;
	  }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in Customer Function {$p[0]}: {$e}");
      }
   return false;	  
   }
public function _do_call_CALL_FUNCTION($p, $vm) {
   /** CALL_FUNCTION("function_name"[, params...]); Call internal system function; Return false or result from system function;  **/
   if (count($p) < 1) return $this->_RunTime_Exception("Wrong parameter count for Call_Function(function_name [, other])");
   try {
      $fn = $p[0];
      if (!function_exists($fn)) return $this->_RunTime_Exception("Call Function unable to find function {$fn}");
	  $error = null;
      $ok = $fn($p, $error);
	  if ($ok===false) return $this->_RunTime_Exception("Fails to run {$fn}, {$ok} {$error}");
	  return $ok;
	  }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in Customer Function {$fn}: {$e}");
      }
   return false;	  
   }

// DICTIONARY

public function _do_call_LOOKUP_DICTIONARY($p, $vm) {
   /** LOOKUP_DICTIONARY("global_name"); Lookup details of a global; Return false or details structure;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for LOOKUP_DICTIONARY(name)");
   $a = hda_db::hdadb()->HDA_DB_dictionary(NULL, $p[0]);
   if (is_null($a) || !is_array($a) || count($a)<>1) {
      return $this->_RunTime_Exception("LOOKUP_DICTIONARY: {$p[0]} dictionary entry not found, or not unique");
      }
   $def = $a[0]['Definition'];
   switch ($def['Connect Type']) {
      case 'FTP':
      case 'FILE':
         $def['directory'] = $def['Table'];
         $def['filemask'] = $def['Key'];
         $def['filename'] = $def['Key'];
         break;
      }
   $def['connect'] = $def['Connect Type'];
   foreach ($def as $k=>$p) { $def[strtoupper($k)] = $p; $def[strtolower($k)] = $p; }
   return $def;
   }

// END DICTIONARY

//** CATEGORY FTP, SSH

private $ftp_binary_mode = false;  

public function _do_call_FTP_FILE_WRITE($p, $vm) {
   /** FTP_FILE_WRITE("file_path_source", "ftp_connection_global"[,destination filename]); Write file via FTP; Return true/false;  **/
   return $this->_ftp_write_($p, $vm, 'FTP_FILE_WRITE');
   }
public function _do_call_FTP_WRITE($p, $vm) {
   /** FTP_FILE_WRITE("text_to_write", "ftp_connection_global"[,destination filename]); Write file via FTP; Return true/false;  **/
   return $this->_ftp_write_($p, $vm, 'FTP_WRITE');
   }
private function _ftp_write_($p, $vm, $fn) {
   $ftp = new HDA_FTP();
   if ($this->ftp_binary_mode) $ftp->ftp_mode = FTP_BINARY; else $ftp->ftp_mode = FTP_ASCII;
   if (count($p)==2 || count($p)==3) {
      $e = $ftp->lookupDictionary($p[1]);
      if ($e===false) {
         return $this->_RunTime_Exception("{$fn}: {$ftp->last_error}");
         }
      if(count($p)==3) $ftp->ftp_filename = $p[2];
      $text = $p[0];
      }
   elseif (count($p)==6) {
      $ftp->setHost($p[1]);
      $ftp->ftp_dir = $p[2];
      $ftp->ftp_filename = $p[3];
      $ftp->username = $p[4];
      $ftp->pw = $p[5];
      $text = $p[0];
      }
   else {
      return $this->_RunTime_Exception("Wrong parameter count for {$fn}");
      return false;
      }
   $e = $ftp->open();
   if ($e===false) { 
      return $this->_RunTime_Exception("{$fn}: {$ftp->last_error}");
      }
   $e = $ftp->to_dst_dir();
   if ($e===false) { 
      $ftp->close();
      return $this->_RunTime_Exception("{$fn}: {$ftp->last_error}");
      }
   if (is_null($ftp->ftp_filename) || strlen($ftp->ftp_filename)==0) $ftp->ftp_filename = $vm->ref_title().".txt";
   switch ($fn) {
      case 'FTP_WRITE':
         $from_path = $this->_resolve_temp_dir($vm)."/ftp_tmp.txt";
         @file_put_contents($from_path, $text);
		 $valid_write = $ftp->write_file($from_path);
         @unlink($from_path);
         break;
      case 'FTP_FILE_WRITE':
         $local_file = $this->_resolve_working_dir($vm)."/{$text}";
         if (!@file_exists($local_file)) {
            return $this->_RunTime_Exception("{$fn}: File {$local_file} to write to FTP server  not found");
            }
         $valid_write = $ftp->write_file($local_file);
         break;
      }
         
   if ($valid_write===false) {
      $ftp->close();
      return $this->_RunTime_Exception("{$fn}: {$ftp->last_error} ");
      }
   $ftp->close();
   return true;
   }

private function _ftp_read_($p, $vm, $fn) {
   $ftp = new HDA_FTP();
   if ($this->ftp_binary_mode) $ftp->ftp_mode = FTP_BINARY; else $ftp->ftp_mode = FTP_ASCII;
   if (count($p)==2 || count($p)==3) {
      $e = $ftp->lookupDictionary($p[1]);
      if ($e===false) {
         return $this->_RunTime_Exception("{$fn}: {$ftp->last_error}");
         }
      if (count($p)==3) $ftp->ftp_filename = $p[2];
      $local_file = $p[0];
      }
   elseif (count($p)==6) {
      $ftp->setHost($p[1]);
      $ftp->ftp_dir = $p[2];
      $ftp->ftp_filename = $p[3];
      $ftp->username = $p[4];
      $ftp->pw = $p[5];
      $local_file = $p[0];
	  $ftp->delete_after_read = false;
      }
   else {
      return $this->_RunTime_Exception("Wrong parameter count for {$fn}");
      }
   $e = $ftp->open();
   if ($e===false) { 
      return $this->_RunTime_Exception("Fails in FTP open {$fn}: {$ftp->last_error}");
      }
   $e = $ftp->to_dst_dir();
   if ($e===false) { 
      $ftp->close();
      return $this->_RunTime_Exception("Fails in setting dst dir {$fn}: {$ftp->last_error}");
      }
   $ftp_return = array();
   switch ($fn) {
      case 'FTP_READ':
         $local_file = $ftp_return['FilePath'] = $this->_resolve_temp_dir($vm)."/{$local_file}";
		 $ftp_return = $ftp->read_file($local_file);
         break;
	  case 'FTP_DELETE':
		 $ftp_return = $valid_read = $ftp->delete();
	     break;
	  case 'FTP_LIST':
	     $ftp_return = $ftp->nlist();
		 break;
	  case 'FTP_DATE':
	     $ftp_return = $ftp->get_datetime();
		 break;

      }
         
   if ($ftp_return===false) {
      $ftp->close();
      return $this->_RunTime_Exception("{$fn}: {$ftp->last_error}");
      }
   $ftp->close();
   return $ftp_return;
   }
public function _do_call_FTP_SET_BINARY($p, $vm) {
   /** FTP_SET_BINARY([true/false]); Set binary for FTP, default true; Return true/false;  **/
   $this->ftp_binary_mode = (count($p)>0)?$p[0]:true;
   }
   
public function _do_call_FTP_READ($p, $vm) {
   /** FTP_READ("local_file_path", "ftp_connection_global"[,destination filename]); Read file via FTP; Return false or details structure;  **/
   return $this->_ftp_read_($p, $vm, 'FTP_READ');
   }
public function _do_call_FTP_LIST($p, $vm) {
   /** FTP_LIST("ftp_connection_global"); List files at FTP; Return false or result structure;  **/
   $p[1] = $p[0];
   $p[0] = "";
   return $this->_ftp_read_($p, $vm, 'FTP_LIST');
   }
public function _do_call_FTP_DATE($p, $vm) {
   /** FTP_DATE("remote_file", "ftp_connection_global"); Read file date via FTP; Return false or date;  **/
   $p[2] = $p[1];
   $p[1] = $p[0];
   $p[0] = "";
   return $this->_ftp_read_($p, $vm, 'FTP_DATE');
   }
public function _do_call_FTP_DELETE($p, $vm) {
   /** FTP_DELETE("remote_file", "ftp_connection_global"); Delete via FTP; Return false/true;  **/
   $p[2] = $p[0];
   return $this->_ftp_read_($p, $vm, 'FTP_DELETE');
   }
   
private $psftp_output = null;
private $psftp_status = null;
private $psftp_last_error = null;
private $psftp_key_file = null;
public function _do_call_PSFTP_KEY_FILE($p, $vm) {
	if (count($p)==0) return $this->psftp_key_file;
	else $this->psftp_key_file = $p[0];
	}
public function _do_call_PSFTP_WRITE($p, $vm) {
   /** PSFTP_WRITE("ftp_connection_global",PUT,; Read file date via PSFTP using psftp.exe; Return false or structure result;  **/
	return $this->_do_call_PSFTP_READ($p, $vm);
	}
public function _do_call_PSFTP_DELETE($p, $vm) {
   /** PSFTP_WRITE("ftp_connection_global",PUT,; Read file date via PSFTP using psftp.exe; Return false or structure result;  **/
   $p[1] = 'DEL';
	return $this->_do_call_PSFTP_READ($p, $vm);
	}
public function _do_call_PSFTP_READ($p, $vm) {
   /** PSFTP_READ("ftp_connection_global",action[, fetch_dir, fetch_file); Read file date via PSFTP using psftp.exe; Return false or structure result;  **/
   if (count($p) < 2) return $this->_RunTime_Exception("Wrong parameter count for PSFTP_xx(psftp_lookup, action[, fetch_dir, fetch_file_name][,dst_dir, local_file])");
   try {
      $script_filename = $_SERVER['SCRIPT_FILENAME'];
      $root_dir = pathinfo($script_filename,PATHINFO_DIRNAME);
      $working_dir = "{$root_dir}/CUSTOM/{$vm->ref}/tmp";
	  if (!@file_exists($working_dir)) @mkdir($working_dir);
      $binary_root = INIT('BINARY_ROOT');
      $psftp_exe = "{$binary_root}/psftp.exe";
      if (!@file_exists($psftp_exe)) return $this->_RunTime_Exception("Unable to locate exe {$psftp_exe} for PSFTP_xx()");
	  if (is_null($psftp_key = $this->psftp_key_file)) {
		$psftp_key = "psftp_key.ppk";
		$psftp_key = "{$root_dir}/CUSTOM/{$vm->ref}/{$psftp_key}";
		}
	  else {
        if (stripos($psftp_key, "\\") === false && stripos($psftp_key,"/")===false) {
			$this->_resolve_working_dir($vm);
			$psftp_key = "{$root_dir}/{$this->WORKING_DIR}/{$psftp_key}";
			}
	    }      if (!@file_exists($psftp_key)) return $this->_RunTime_Exception("Unable to locate key {$psftp_key} for PSFTP_xx()");
      $a = hda_db::hdadb()->HDA_DB_dictionary(NULL, $p[0]);
      if (is_null($a) || !is_array($a) || count($a)<>1) 
         return $this->_RunTime_Exception("PSFTP_xx: {$p[0]} dictionary entry not found, or not unique");
         
      $def = $a[0]['Definition'];
      if ($def['Connect Type']<>'FTP') 
         return $this->_RunTime_Exception("PSFTP_xx: {$p[0]} dictionary entry not of type FTP");
	  $url = $def['Host'];
	  $port = null;
	  if (strpos($url,':')!==false) {
	     list($url, $port) = explode(':',$url);
		 $port = " -P {$port} ";
		 }
	  if (strlen($def['User'])>0) $url = "{$def['User']}@{$url}";
	  $pw = (strlen($def['PassW'])>0)?" -pw {$def['PassW']} ":null;
	  $dst_dir = ((count($p)>2) && ((is_array($p[2]))||(strlen($p[2])>0)))?$p[2]:$def['Table'];
	  $delete_dst = (array_key_exists('Cleanup',$def) && ($def['Cleanup']==1));
      $this->psftp_output = "";
      $this->psftp_status = null;
      $this->psftp_last_error = null;
      $io_descriptors = array(
                           0=>array("pipe",'r'),
                           1=>array("pipe",'w'),
                           2=>array("pipe",'a')
                           );
	  $cmd_file = "{$working_dir}\psftp_batch.bat";
	  $bat = "";
	  if (is_array($dst_dir)) {
	     foreach($dst_dir as $adir) $bat .= "cd \"{$adir}\"\r\n";
		 }
	  elseif (!is_null($dst_dir) && strlen($dst_dir)>0) $bat .= "cd \"{$dst_dir}\"\r\n";
	  $get = "get";
	  switch ($p[1]) {
	     case 'CMD':
	     case 'LIST':
		    $bat .= "ls\r\n";
			break;
		 case 'DEL':
            $dst_file = $p[3];		
            $bat .= "rm \"{$dst_file}\"\r\n";			
			break;
	     case 'MGET':
		    $get = "mget";
		 case 'GET':
		    if (count($p)<4) return $this->_RunTime_Exception("PSFTP_READ: File name for GET not given");
            $dst_file = $p[3];		
            $bat .= "lcd \"{$working_dir}\"\r\n";
            $bat .= "{$get} \"{$dst_file}\"\r\n";	
            $bat .= ($delete_dst)?"rm \"{$dst_file}\"\r\n":"";			
			break;
		 case 'PUT':
		    if (count($p)<4) return $this->_RunTime_Exception("PSFTP_WRITE: File name for source not given");
            $src_file = $p[3];		
            $bat .= "lcd \"{$working_dir}\"\r\n";
            $bat .= "put \"{$src_file}\"\r\n";	
			break;
         default: return $this->_RunTime_Exception("PSFTP_xx: Invalid request {$p[1]}");	
            break;
         }	
      $bat .= "bye\r\n";		 
	  file_put_contents($cmd_file, $bat);
      $psftp_cmd = "{$psftp_exe} -i {$psftp_key} -batch -b \"{$cmd_file}\" -v {$port} {$pw} {$url}";
	  file_put_contents("psrun.bat", $psftp_cmd);
	  switch ($p[1]) {
	     case 'CMD':
		    return array('Request'=>$psftp_cmd);
		 }
      $handle = @proc_open($psftp_cmd, $io_descriptors, $pipes, $working_dir);
      if ($handle===false) {
         return $this->_RunTime_Exception($this->psftp_last_error = "Fails to open PSFTP command");
         }
      else {
         $status = proc_get_status($handle);
         $the_close = ($status['running'])?null:$status['exitcode'];
         @fclose($pipes[0]);
         $this->psftp_output .= stream_get_contents($pipes[1]);
         @fclose($pipes[1]);
         $this->psftp_output .= stream_get_contents($pipes[2]);
         @fclose($pipes[2]);
         $proc_close = @proc_close($handle);
         $this->psftp_status = (is_null($the_close))?$proc_close:$the_close;
		 $ps_return = array('Status'=>$this->psftp_status,'Output'=>$this->psftp_output,'Request'=>$psftp_cmd);
		 switch ($p[1]) {
		    case 'LIST':
			   $lines = explode(PHP_EOL, $this->psftp_output);
			   foreach ($lines as $line) {
			      $list_ok = @preg_match("/[A-Za-z]{3,3}[\s]{1,}[\d]{1,}[\s]{1,}[\d]{2,2}[\:]{0,1}[\d]{2,2}[\s]{1,}(?P<filename>[\w\d ._\-]{1,})$/",$line,$matches);
			      if (array_key_exists('filename',$matches)) {
					$ps_return['List'][]=$matches['filename'];
					$dates_ok = @preg_match("/(?P<mnth>[\w]{3,3})[ ]{1,}(?P<day>[\d]{1,2})[ ]{1,}(?P<hr>[\d]{2,2}):(?P<min>[\d]{2,2})/",$line,$date_match);
					if (array_key_exists('mnth',$date_match)) {
						$file_date = date('Y-m-d G:i:s',strtotime("{$date_match['mnth']} {$date_match['day']} {$date_match['hr']}:{$date_match['min']}:00"));
						}
					else $file_date = date('Y-m-d G:i:s');
					$ps_return['Dates'][$matches['filename']] = $file_date;
					}
				  }
			   break;
			case 'GET':
			   $ps_return['FilePath'] = "{$working_dir}\\{$dst_file}";
			   $ps_return['FileName'] = $dst_file;
			   break;
			}
         return $ps_return;
         }
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in PSFTP_xx: {$e}");
      }
   return false;
   }

// END FTP
// ALT FTP

private $LIBRARY_FTP = null;
public function _do_call_FTP_CONNECT($p, $vm) {
   /** FTP_CONNECT("host",port,username,pw); Open an ALT FTP session; Return false/true;  **/
   if (count($p) < 1) return $this->_RunTime_Exception("Wrong parameter count for FTP_CONNECT(host[, port])");
   try {
      $this->LIBRARY_FTP = new HDA_FTP();
	  $this->LIBRARY_FTP->host = $p[0];
	  if (!is_null($p[1])) $this->LIBRARY_FTP->port = $p[1];
	  $this->LIBRARY_FTP->username = $p[2];
	  if (!is_null($p[3])) $this->LIBRARY_FTP->pw = $p[3];
	  $open = $this->LIBRARY_FTP->open();
      if ($open===false) {
         $this->LIBRARY_FTP = null;
         return $this->_RunTime_Exception("Fails in alt ftp connect {$p[0]} ");
         }
      return true;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in alt ftp connect: {$e}");
      }
   $this->LIBRARY_FTP = null;
   return false;
   }

public function _do_call_FTP_READFILE($p, $vm) {
   /** FTP_READFILE(local_file_path, remote absolute filepath); Read a file from an FTP session; Return false or file contents;  **/
   if (count($p) <> 2) return $this->_RunTime_Exception("Wrong parameter count for FTP_READFILE(remote_absolute_filepath)");
   if (is_null($this->LIBRARY_FTP)) return $this->_RunTime_Exception("Call FTP_READFILE with no FTP connection");
   try {
      return $this->LIBRARY_FTP->read_file($p[0],$p[1]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in FTP_READFILE: {$e}");
      }
   return false;
   }
   
public function _do_call_FTP_DIR($p, $vm) {
   /** FTP_DIR(remote absolute dir path); Get a directory listing from an SFTP session; Return false or list structure;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for FTP_DIR(dirpath)");
   if (is_null($this->LIBRARY_FTP)) return $this->_RunTime_Exception("Call FTP_DIR with no FTP connection");
   try {
       return $this->LIBRARY_FTP->nlist($p[0]);
       }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in FTP_DIR: {$e}");
      }
   return false;
   }

public function _do_call_FTP_RMDIR($p, $vm) {
   /** FTP_RMDIR(remote absolute dir path); Remove a directory in an FTP session; Return false/true;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for FTP_RMDIR(dirpath)");
   if (is_null($this->LIBRARY_FTP)) return $this->_RunTime_Exception("Call FTP_RMDIR with no FTP connection");
   try {
      return $this->LIBRARY_FTP->delete($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in FTP_RMDIR: {$e}");
      }
   return false;
   }
public function _do_call_FTP_STAT($p, $vm) {
   /** FTP_STAT(remote absolute dir path); Do stat on a file in FTP session; Return false/true;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for FTP_STAT(path)");
   if (is_null($this->LIBRARY_FTP)) return $this->_RunTime_Exception("Call FTP_STAT with no FTP connection");
   try {
	  $stat = array();
      $stat['mtime'] = $this->LIBRARY_FTP->get_datetime($p[0]);
	  $stat['size'] = $this->LIBRARY_FTP->filesize($p[0]);
	  return $stat;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in FTP_STAT: {$e}");
      }
   return false;
   }


public function _do_call_FTP_CLOSE($p, $vm) {
   /** FTP_CLOSE(); Close an FTP session; Return false/true;  **/
   $this->LIBRARY_FTP = null;
   return true;
   }

// END ALT FTP

// SSH2
private $LIBRARY_SSH2 = null;
private $USE_SSH2 = true;
public function _do_call_USE_SSH2($p, $vm) {
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for USE SSH2(true false)");
   $this->USE_SSH2 = $p[0] && function_exists('ssh2_connect');
   return $this->USE_SSH2;
}
private $LIBRARY_SECLIB = null;
private function _seclib_SSH2($p, $vm) {
   if (count($p) < 1) return $this->_RunTime_Exception("Wrong parameter count for NET_SFTP(host[, port])");
   try {
      $p[1] = (count($p)==2)?$p[1]:22;
      $this->LIBRARY_SECLIB = new Net_SFTP($p[0],$p[1]);
      if ($this->LIBRARY_SECLIB===false) {
         $this->LIBRARY_SECLIB = null;
         return $this->_RunTime_Exception("Fails in net sftp connect {$p[0]} ");
         }
      return true;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in NET_SFTP: {$e}");
      }
   $this->LIBRARY_SECLIB = null;
   return false;
}
private function _seclib_LOGIN($p, $vm) {
   if (count($p) < 1) return $this->_RunTime_Exception("Wrong parameter count for NET LOGIN(username[ ,password])");
   if (is_null($this->LIBRARY_SECLIB)) return $this->_RunTime_Exception("Call NET LOGIN with no Net SECLIB connection");
   try {
      $result = (count($p)==2)?$this->LIBRARY_SECLIB->login($p[0], $p[1]):$this->LIBRARY_SECLIB->login($p[0]);
      if ($result === false) {
         $this->LIBRARY_SECLIB = null;
         return $this->_RunTime_Exception("Fails in NET LOGIN in SECLIB {$p[0]},****** ");
         }
      return true;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in  NET LOGIN in SECLIB: {$e}");
      }
   $this->LIBRARY_SECLIB = null;
   return false;
}
private function _seclib_SFTP($p, $vm) {
   if (is_null($this->LIBRARY_SECLIB)) return $this->_RunTime_Exception("Call NET SFTP with no Net SECLIB connection");
   return true;
}
private function _seclib_SFTP_READFILE($p, $vm) {
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for SECLIB SFTP_READFILE(remote_absolute_filepath)");
   if (is_null($this->LIBRARY_SECLIB)) return $this->_RunTime_Exception("Call SECLIB SFTP_READFILE with no Net SECLIB connection");
   try {
      return $this->LIBRARY_SECLIB->get($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SECLIB SFTP_READFILE: {$e}");
      }
   return false;
}
private function _seclib_SFTP_WRITEFILE($p, $vm) {
   if (count($p) <> 2) return $this->_RunTime_Exception("Wrong parameter count for SECLIB SFTP_WRITEFILE(localfile, remote absolute filepath)");
   if (is_null($this->LIBRARY_SECLIB)) return $this->_RunTime_Exception("Call SECLIB SFTP_WRITEFILE with no Net SECLIB connection");
   try {
      return $this->LIBRARY_SECLIB->put($p[1], $p[0], NET_SFTP_LOCAL_FILE);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SECLIB SFTP_WRITEFILE: {$e}");
      }
   return false;
}
private function _seclib_SFTP_DIR($p, $vm) {
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for SECLIB SFTP_DIR(remote absolute filepath)");
   if (is_null($this->LIBRARY_SECLIB)) return $this->_RunTime_Exception("Call SECLIB SFTP_DIR with no Net SECLIB connection");
   try {
      $a = $this->LIBRARY_SECLIB->nlist($p[0]);
	  $aa = array();
      // List all the files
      foreach ($a as $file) {
         if (substr("{$file}", 0, 1) != "."){
            $aa[]=$file;
            }
         }
	  return $aa;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SECLIB SFTP_DIR: {$e}");
      }
   return false;
}
private function _seclib_SFTP_MKDIR($p, $vm) {
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for SECLIB SFTP_MKDIR(remote absolute filepath)");
   if (is_null($this->LIBRARY_SECLIB)) return $this->_RunTime_Exception("Call SECLIB SFTP_MKDIR with no Net SECLIB connection");
   try {
      return $this->LIBRARY_SECLIB->mkdir($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SECLIB SFTP_MKDIR: {$e}");
      }
   return false;
}
private function _seclib_SFTP_STAT($p, $vm) {
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for SECLIB SFTP_STAT(remote absolute filepath)");
   if (is_null($this->LIBRARY_SECLIB)) return $this->_RunTime_Exception("Call SECLIB SFTP_STAT with no Net SECLIB connection");
   try {
      return $this->LIBRARY_SECLIB->stat($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SECLIB SFTP_STAT: {$e}");
      }
   return false;
}
private function _seclib_SFTP_RMDIR($p, $vm) {
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for SECLIB SFTP_RMDIR(remote absolute filepath)");
   if (is_null($this->LIBRARY_SECLIB)) return $this->_RunTime_Exception("Call SECLIB SFTP_RMDIR with no Net SECLIB connection");
   try {
      return $this->LIBRARY_SECLIB->rmdir($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SECLIB SFTP_RMDIR: {$e}");
      }
   return false;
}
private function _seclib_SFTP_DELETE($p, $vm) {
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for SECLIB SFTP_DELETE(remote absolute filepath)");
   if (is_null($this->LIBRARY_SECLIB)) return $this->_RunTime_Exception("Call SECLIB SFTP_DELETE with no Net SECLIB connection");
   try {
      return $this->LIBRARY_SECLIB->delete($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SECLIB SFTP_DELETE: {$e}");
      }
   return false;
}
private function _seclib_SFTP_CLOSE($p, $vm) {
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for SECLIB SFTP_CLOSE()");
   if (is_null($this->LIBRARY_SECLIB)) return $this->_RunTime_Exception("Call SECLIB SFTP_CLOSE with no Net SECLIB connection");
   try {
      $this->LIBRARY_SECLIB->disconnect();
	  $this->LIBRARY_SECLIB = null;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SECLIB SFTP_CLOSE: {$e}");
      }
   return false;
}

public function _do_call_SSH2_CONNECT($p, $vm) {
   /** SSH2_CONNECT("host"[,port]); Open a SSH session; Return false/true;  **/
   if (!$this->USE_SSH2) return $this->_seclib_SSH2($p, $vm);
   if (count($p) < 1) return $this->_RunTime_Exception("Wrong parameter count for SSH2_CONNECT(host[, port])");
   try {
      $this->LIBRARY_SSH2 = (count($p)==2)?@ssh2_connect($p[0],$p[1]):@ssh2_connect($p[0]);
      if ($this->LIBRARY_SSH2===false) {
         $this->LIBRARY_SSH2 = null;
         return $this->_RunTime_Exception("Fails in ssh2 connect {$p[0]} ");
         }
      return true;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SSH2_CONNECT: {$e}");
      }
   $this->LIBRARY_SSH2 = null;
   return false;
   }
public function _do_call_SSH2_AUTH_HOSTBASED_FILE($p, $vm) {
   /** SSH2_AUTH_HOSTBASED_FILE(username,hostname,pubfilefilename,privkeyfile,passphrase,local_username); Authorise a SSH session; Return false/true;  **/
   if (count($p) <> 6) return $this->_RunTime_Exception("Wrong parameter count for SSH2_AUTH_HOSTBASED_FILE(username,hostname,pubfilefilename,privkeyfile,passphrase,local_username)");
   if (is_null($this->LIBRARY_SSH2)) return $this->_RunTime_Exception("Call SSH2_AUTH_HOSTBASED_FILE with no SSH2 connection");
   try {
      if (@ssh2_auth_hostbased_file($this->LIBRARY_SSH2, $p[0], $p[1], $p[2], $p[3], $p[4], $p[5])===false) {
         $this->LIBRARY_SSH2 = null;
         return $this->_RunTime_Exception("Fails in ssh2 auth by hostbased file {$p[0]},{$p[1]},{$p[2]},{$p[3]},{$p[4]},{$p[5]} ");
         }
      return true;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SSH2_AUTH_HOSTBASED_FILE: {$e}");
      }
   $this->LIBRARY_SSH2 = null;
   return false;
   }
public function _do_call_SSH2_AUTH_PUBKEY_FILE($p, $vm) {
   /** SSH2_AUTH_PUBKEY_FILE(username,pubfilefilename,privkeyfile,passphrase); Authorise a SSH session; Return false/true;  **/
   if (count($p) <> 4) return $this->_RunTime_Exception("Wrong parameter count for SSH2_AUTH_PUBKEY_FILE(username,pubfilefilename,privkeyfile,passphrase)");
   if (is_null($this->LIBRARY_SSH2)) return $this->_RunTime_Exception("Call SSH2_AUTH_PUBKEY_FILE with no SSH2 connection");
   try {
       if (@ssh2_auth_pubkey_file($this->LIBRARY_SSH2, $p[0], $p[1], $p[2], $p[3])===false) {
         $this->LIBRARY_SSH2 = null;
         return $this->_RunTime_Exception("Fails in ssh2 auth by pubkey file {$p[0]},{$p[1]},{$p[2]},{$p[3]} ");
         }
      return true;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SSH2_AUTH_PUBKEY_FILE: {$e}");
      }
   $this->LIBRARY_SSH2 = null;
   return false;
   }
public function _do_call_SSH2_AUTH_PASSWORD($p, $vm) {
   /** SSH2_AUTH_PASSWORD(username,password); Authorise a SSH session; Return false/true;  **/
   if (!$this->USE_SSH2) return $this->_seclib_LOGIN($p, $vm);
   if (count($p) <> 2) return $this->_RunTime_Exception("Wrong parameter count for SSH2_AUTH_PASSWORD(username,password)");
   if (is_null($this->LIBRARY_SSH2)) return $this->_RunTime_Exception("Call SSH2_AUTH_PASSWORD with no SSH2 connection");
   try {
      $result = @ssh2_auth_password($this->LIBRARY_SSH2, $p[0], $p[1]);
      if ($result === false) {
         $this->LIBRARY_SSH2 = null;
         return $this->_RunTime_Exception("Fails in ssh2 auth by password {$p[0]},****** ");
         }
      return true;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SSH2_AUTH_PASSWORD: {$e}");
      }
   $this->LIBRARY_SSH2 = null;
   return false;
   }
public function _do_call_SSH2_AUTH_NONE($p, $vm) {
   /** SSH2_AUTH_NONE(username); Authorise a SSH session; Return false/true;  **/
   if (!$this->USE_SSH2) return $this->_seclib_LOGIN($p, $vm);
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for SSH2_AUTH_NONE(username)");
   if (is_null($this->LIBRARY_SSH2)) return $this->_RunTime_Exception("Call SSH2_AUTH_NONE with no SSH2 connection");
   try {
      return @ssh2_auth_none($this->LIBRARY_SSH2, $p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SSH2_AUTH_NONE: {$e}");
      }
   $this->LIBRARY_SSH2 = null;
   return false;
   }

private $LIBRARY_SFTP = null;
public function _do_call_SSH2_SFTP($p, $vm) {
   /** SSH2_SFTP(); Set up an SFTP connection in a SSH session; Return false/true;  **/
   if (!$this->USE_SSH2) return $this->_seclib_SFTP($p, $vm);
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for SSH2_SFTP()");
   if (is_null($this->LIBRARY_SSH2)) return $this->_RunTime_Exception("Call SSH2_SFTP with no SSH2 connection");
   try {
      $this->LIBRARY_SFTP =  @ssh2_sftp($this->LIBRARY_SSH2);
      if ($this->LIBRARY_SFTP===false) {
         $this->LIBRARY_SFTP = null;
         return $this->_RunTime_Exception("Call SSH2_SFTP fails");
         }
      return true;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SSH2_SFTP: {$e}");
      }
   $this->LIBRARY_SFTP = null;
   return false;
   }
public function _do_call_SFTP_READFILE($p, $vm) {
   /** SFTP_READFILE(remote absolute filepath); Read a file from an SFTP session; Return false or file contents;  **/
   if (!$this->USE_SSH2) return $this->_seclib_SFTP_READFILE($p, $vm);
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for SFTP_READFILE(remote_absolute_filepath)");
   if (is_null($this->LIBRARY_SSH2)) return $this->_RunTime_Exception("Call SFTP_READFILE with no SSH2 connection");
   if (is_null($this->LIBRARY_SFTP)) return $this->_RunTime_Exception("Call SFTP_READFILE with no SFTP connection");
   try {
      $sftp = $this->LIBRARY_SFTP;
      $stream = @fopen("ssh2.sftp://{$sftp}{$p[0]}", 'r');
      if (! $stream) {
         return $this->_RunTime_Exception("Call SFT_READFILE: Could not open file: {$p[0]}");
         }
      $size = @filesize("ssh2.sftp://{$sftp}{$p[0]}"); 
      $s = '';
      $read = 0;
      $len = $size;
      while ($read < $len && ($buf = fread($stream, $len - $read))) {
         $read += strlen($buf);
         $s .= $buf;
         } 
      @fclose($stream);
      return $s;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SFTP_READFILE: {$e}");
      }
   return false;
   }
public function _do_call_SFTP_WRITEFILE($p, $vm) {
   /** SFTP_READFILE(localfile, remote absolute filepath); Write a local file to SFTP session; Return false or true;  **/
   if (!$this->USE_SSH2) return $this->_seclib_SFTP_WRITEFILE($p, $vm);
   if (count($p) <> 2) return $this->_RunTime_Exception("Wrong parameter count for SFTP_WRITEFILE(localfile,remote_absolute_filepath)");
   if (is_null($this->LIBRARY_SSH2)) return $this->_RunTime_Exception("Call SFTP_WRITEFILE with no SSH2 connection");
   if (is_null($this->LIBRARY_SFTP)) return $this->_RunTime_Exception("Call SFTP_WRITEFILE with no SFTP connection");
   try {
	  if (!@file_exists($p[0])) return $this->_RunTime_Exception("Call SFT_WRITEFILE: Could not open source file: {$p[0]}");
	  $s = file_get_contents($p[0]);
	  
      $sftp = $this->LIBRARY_SFTP;
      $stream = @fopen("ssh2.sftp://{$sftp}{$p[1]}", 'w');
      if (!$stream) {
         return $this->_RunTime_Exception("Call SFT_WRITEFILE: Could not open destination file: {$p[1]}");
         }
      $len = strlen($s);
      for ($written = 0, $fwrite=0; ($written < $len) && ($fwrite!==false); $written += $fwrite) {
        $fwrite = fwrite($stream, substr($s, $written));
		}
      @fclose($stream);
      return true;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SFTP_WRITEFILE: {$e}");
      }
   return false;
   }
   
public function _do_call_SFTP_DIR($p, $vm) {
   /** SFTP_DIR(remote absolute dir path); Get a directory listing from an SFTP session; Return false or list structure;  **/
   if (!$this->USE_SSH2) return $this->_seclib_SFTP_DIR($p, $vm);
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for SFTP_DIR(dirpath)");
   if (is_null($this->LIBRARY_SSH2)) return $this->_RunTime_Exception("Call SFTP_DIR with no SSH2 connection");
   if (is_null($this->LIBRARY_SFTP)) return $this->_RunTime_Exception("Call SFTP_DIR with no SFTP connection");
   try {
      $sftp = $this->LIBRARY_SFTP;
      $dir = "ssh2.sftp://{$sftp}{$p[0]}";
      $a = array();
	  $retries = 0;
	  $handle=false;
	  while (($retries < 3)&&($handle===false)) {
		try {
         $handle = opendir($dir);
	     if ($handle===false) $retries++;
		 }
		catch (Exception $e) {
			$retries++;
			if ($retries>3) throw new Exception("Fails to opendir ssh {$p[0]}, exceeded retries {$e}");
			}
		}
	  if ($handle===false) throw new Exception("No valid handle for opendir ssh to open {$p[0]} retries {$retries} ");
      // List all the files
      while (false !== ($file = @readdir($handle))) {
         if (substr("{$file}", 0, 1) != "."){
            $a[]=$file;
            }
         }
      @closedir($handle); 
      return $a;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SFTP_DIR for {$p[0]}: {$e}");
      }
   return false;
   }
public function _do_call_SFTP_MKDIR($p, $vm) {
   /** SFTP_READFILE(remote absolute filepath); Make directory on SFTP session; Return false or true;  **/
   if (!$this->USE_SSH2) return $this->_seclib_SFTP_MKDIR($p, $vm);
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for SFTP_MKDIR(remote_absolute_filepath)");
   if (is_null($this->LIBRARY_SSH2)) return $this->_RunTime_Exception("Call SFTP_MKDIR with no SSH2 connection");
   if (is_null($this->LIBRARY_SFTP)) return $this->_RunTime_Exception("Call SFTP_MKDIR with no SFTP connection");
   try {
		$sftp = $this->LIBRARY_SFTP;
		return @mkdir("ssh2.sftp://{$sftp}{$p[0]}");
		}
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SFTP_MKDIR for {$p[0]}: {$e}");
      }
   return false;
	}
public function _do_call_SFTP_STAT($p, $vm) {
   /** SFTP_STAT(remote absolute path); Do file stat in an SFTP session; Return false/true;  **/
   if (!$this->USE_SSH2) return $this->_seclib_SFTP_STAT($p, $vm);
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for SFTP_STAT(path)");
   if (is_null($this->LIBRARY_SSH2)) return $this->_RunTime_Exception("Call SFTP_STAT with no SSH2 connection");
   if (is_null($this->LIBRARY_SFTP)) return $this->_RunTime_Exception("Call SFTP_STAT with no SFTP connection");
   try {
		$errs = error_reporting(E_ERROR | E_WARNING);
		$stat = @ssh2_sftp_stat($this->LIBRARY_SFTP, $p[0]);
		if ($stat===false) {
		$sftp = $this->LIBRARY_SFTP;
		$stat = @stat("ssh2.sftp://{$sftp}{$p[0]}");
		}
		error_reporting($errs);
		return $stat;
		/*
		$filesize = $statinfo['size'];
		$group = $statinfo['gid'];
		$owner = $statinfo['uid'];
		$atime = $statinfo['atime'];
		$mtime = $statinfo['mtime'];
		$mode = $statinfo['mode'];
		*/
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SFTP_STAT: {$e}");
      }
   return false;
   }
public function _do_call_SFTP_RMDIR($p, $vm) {
   /** SFTP_RMDIR(remote absolute dir path); Remove a directory in an SFTP session; Return false/true;  **/
   if (!$this->USE_SSH2) return $this->_seclib_SFTP_RMDIR($p, $vm);
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for SFTP_RMDIR(dirpath)");
   if (is_null($this->LIBRARY_SSH2)) return $this->_RunTime_Exception("Call SFTP_RMDIR with no SSH2 connection");
   if (is_null($this->LIBRARY_SFTP)) return $this->_RunTime_Exception("Call SFTP_RMDIR with no SFTP connection");
   try {
      $sftp = $this->LIBRARY_SFTP;
      return $this->_sftp_rmdir($sftp, $p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SFTP_RMDIR: {$e}");
      }
   return false;
   }
private function _sftp_rmdir($sftp, $dir) {
   if (is_dir($base_dir = "ssh2.sftp://{$sftp}{$dir}")) {
	   $handle = opendir($base_dir);
	   if (is_resource($handle)) {
		  while (false !== ($file = @readdir($handle))) {
			 if (substr("{$file}", 0, 1) != "."){
				if (is_dir("{$base_dir}/{$file}")) $this->_sftp_rmdir($sftp, "{$dir}/{$file}");
				else @ssh2_sftp_unlink($sftp, "{$dir}/{$file}");
				}
			 }
		  @closedir($handle); 
		  $ok = @ssh2_sftp_rmdir($sftp, $dir);
		  return $ok;
		  }
	  }
   else return (file_exists($base_dir))?@ssh2_sftp_unlink($sftp, $dir):false;
   return true;
   }
public function _do_call_SFTP_DELETE($p, $vm) {
   /** SFTP_DELETE(); Delete a remote file; Return false/true;  **/
   if (!$this->USE_SSH2) return $this->_seclib_SFTP_DELETE($p, $vm);
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for SFTP_DELETE(filepath)");
   if (is_null($this->LIBRARY_SSH2)) return $this->_RunTime_Exception("Call SFTP_DELETE with no SSH2 connection");
   if (is_null($this->LIBRARY_SFTP)) return $this->_RunTime_Exception("Call SFTP_DELETE with no SFTP connection");
   try {
		return @ssh2_sftp_unlink($this->LIBRARY_SFTP, $p[0]);
   }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SFTP_DELETE for {$p[0]}: {$e}");
      }
   return false;
   }

public function _do_call_SFTP_CLOSE($p, $vm) {
   /** SFTP_CLOSE(); Close an SFTP session; Return false/true;  **/
   if (!$this->USE_SSH2) return $this->_seclib_SFTP_CLOSE($p, $vm);
   $this->LIBRARY_SFTP = null;
   $this->LIBRARY_SECLIB = null;
   return true;
   }

public function _do_call_SSH2_CLOSE($p, $vm) {
   /** SSH2_CLOSE(); Close an SSH2 session; Return false/true;  **/
   if (!$this->USE_SSH2) return $this->_seclib_SFTP_CLOSE($p, $vm);
   $this->LIBRARY_SSH2 = null;
   $this->LIBRARY_SECLIB = null;
   return true;
   }


// END SSH2

//** CATEGORY RSS, SOAP

public function _do_call_RSS_READ($p, $vm) {
   /** RSS_READ(rss_connect_global); Read from an rss stream; Return false or result structure;  **/
   if (count($p)==1) {
      $a = hda_db::hdadb()->HDA_DB_dictionary(NULL, $p[0]);
      if (is_null($a) || !is_array($a) || count($a)<>1) {
         return $this->_RunTime_Exception("RSS_READ: {$p[0]} dictionary entry not found, or not unique");
         return false;
         }
      $def = $a[0]['Definition'];
      if ($def['Connect Type']<>'RSS') {
         return $this->_RunTime_Exception("RSS_READ: {$p[0]} dictionary entry not of type RSS");
         return false;
         }
      $url = $def['Host'];
      }
   else {
      return $this->_RunTime_Exception("Wrong parameter count for RSS_READ");
      return false;
      }
   return HDA_getRss($url);
   }

public function _do_call_RSS_WRITE($p, $vm) {
   /** RSS_WRITE(subject, text); Write to this current RSS service; Return false/true;  **/
   if (count($p) <> 2) return $this->_RunTime_Exception("Wrong parameter count for RSS_WRITE(subject, text)");
   $to_path = "RssFeed/{$vm->profile['ItemId']}";
   if (!@file_exists($to_path)) @mkdir($to_path);
   $s = preg_replace("/\W/"," ",trim($p[0]));
   $s = trim(substr($s, 0, 18));
   $to_path .= "/{$s}.txt";
   @file_put_contents($to_path, $p[1]);
   return false;
   }

// END RSS

// ** CATEGORY EASYXL

private $_LIBRARY_EASYXL = null;
private $_EASYXL_QUICK_SHEET = false;
private $_EASYXL_XLFile = null;
public function _do_call_EASYXL_OPEN($p, $vm) {
   /** EASYXL_OPEN(path to excel file); Open an excel session; Return false/true;  **/
   try {
	$this->_LIBRARY_EASYXL = new HDA_EasyXL();
	$path = $p[0];
	if (!@file_exists($path)) {
		$this->_resolve_working_dir($vm);
		$path = $this->WORKING_DIR."/{$path}";
		}
	if (!@file_exists($path)) return $this->_RunTime_Exception("Fails in EASYXL_OPEN - file {$path} not found");
	$this->_EASYXL_XLFile = $path;
	$is_open = $this->_LIBRARY_EASYXL->open($path);
	if ($is_open) return true;
     return $this->_RunTime_Exception("Fails in EASYXL_OPEN: {$is_open} ".$this->_LIBRARY_EASYXL->last_error());
   }
   catch(Exception $e) {
     return $this->_RunTime_Exception("Fails in EASYXL_OPEN: {$e} ".$this->_LIBRARY_EASYXL->last_error());
    }
}
public function _do_call_EASYXL_POSITION_TO_COORD($p, $vm) {
	$m = preg_match("/(?P<column>[A-Z]{1,})(?P<row>[\d]{1,})/i",$p[0],$matches);
	if (!$m) throw new Exception("Invalid cell address {$p[0]}");
	$matches['column'] = PHPExcel_Cell::columnIndexFromString($matches['column'])-1;
	$matches['row'] = $matches['row']-1;
	return $matches;
}

public function _do_call_EASYXL_COLUMN_INDEX($p, $vm) {
	return PHPExcel_Cell::columnIndexFromString($p[0])-1;
}
public function _do_call_EASYXL_EXCEL_ADDRESS($p, $vm) {
	return PHPExcel_Cell::stringFromColumnIndex($p[0]-1).($p[1]-1);
}
public function _do_call_EASYXL_LOAD($p, $vm) {
   /** EASYXL_LOAD(sheet); Open an excel sheet session; Return false/true;  **/
   try {
		$this->_LIBRARY_EASYXL->load($p[0]);
		return true;
   }
   catch(Exception $e) {
     return $this->_RunTime_Exception("Fails in EASYXL_LOAD: {$e} ".$this->_LIBRARY_EASYXL->last_error());
    }
}
public function _do_call_EASYXL_VALIDATE($p, $vm) {
	try {
		$this->_LIBRARY_EASYXL->validate();
		return true;
	}
	catch (Exception $e) {
     return $this->_RunTime_Exception("Fails in VALIDATE: {$e} ".$this->_LIBRARY_EASYXL->last_error());
	}
}
public function _do_call_EASYXL_ARRAY($p, $vm) {
	try {
	return $this->_LIBRARY_EASYXL->asArray();
	}
	catch(Exception $e) {
     return $this->_RunTime_Exception("Fails in EASYXL_ARRAY: {$e} ".$this->_LIBRARY_EASYXL->last_error());
	}
}
public function _do_call_EASYXL_WRITE_TABLE($p, $vm) {
	try {
	$s =  $this->_LIBRARY_EASYXL->WriteTable($p[0],$p[1]);
	}
	catch(Exception $e) {
     return $this->_RunTime_Exception("Fails in EASYXL_WRITE_TABLE: {$e} ".$this->_LIBRARY_EASYXL->last_error());
	}
}
public function _do_call_EASYXL_SAVEAS($p, $vm) {
	try {
	return $this->_LIBRARY_EASYXL->saveAs($p[0],$p[1]);
	}
	catch(Exception $e) {
     return $this->_RunTime_Exception("Fails in EASYXL_SAVEAS: {$e} ".$this->_LIBRARY_EASYXL->last_error());
	}
}
public function _do_call_EASYXL_SHEET_LIMITS($p, $vm) {
	return $this->_LIBRARY_EASYXL->sheet_limits();
}
public function _do_call_EASYXL_CELL($p, $vm) {
	try {
		if (count($p)==1) {
			$a = $this->_do_call_EASYXL_POSITION_TO_COORD($p, $vm);
		}
		else $a = array('row'=>($p[0]-1), 'column'=>$p[1]);
		$v = $this->_LIBRARY_EASYXL->getCellAt($a['row'], $a['column']);
		return $v;
	}
	catch (Exception $e) {
     return $this->_RunTime_Exception("Fails in EASYXL_CELL: {$e} ".$this->_LIBRARY_EASYXL->last_error());
	}
}
public function _do_call_EASYXL_CELL_DETAILS($p, $vm) {
	try {
		if (count($p)==1) {
			$a = $this->_do_call_EASYXL_POSITION_TO_COORD($p, $vm);
		}
		else $a = array('row'=>($p[0]-1), 'column'=>$p[1]);
		$v = $this->_LIBRARY_EASYXL->getCellDetails($a['row'], $a['column']);
		return $v;
	}
	catch (Exception $e) {
     return $this->_RunTime_Exception("Fails in EASYXL_CELL: {$e} ".$this->_LIBRARY_EASYXL->last_error());
	}
}
public function _do_call_EASYXL_GETFORMAT($p, $vm) {
	try {
		if (count($p)==1) {
			$a = $this->_do_call_EASYXL_POSITION_TO_COORD($p, $vm);
		}
		else $a = array('row'=>($p[0]-1), 'column'=>$p[1]);
		$v = $this->_LIBRARY_EASYXL->getFormat($a['row'], $a['column']);
		return $v;
	}
	catch (Exception $e) {
     return $this->_RunTime_Exception("Fails in EASYXL_GETFORMAT: {$e} ".$this->_LIBRARY_EASYXL->last_error());
	}
}
public function _do_call_EASYXL_SETFORMAT($p, $vm) {
	try {
		if (count($p)==2) {
			$a = $this->_do_call_EASYXL_POSITION_TO_COORD($p, $vm);
			$f = $p[1];
		}
		else {$a = array('row'=>($p[0]-1), 'column'=>$p[1]); $f = $p[2];}
		$v = $this->_LIBRARY_EASYXL->setFormat($a['row'], $a['column'], $f);
		return $v;
	}
	catch (Exception $e) {
     return $this->_RunTime_Exception("Fails in EASYXL_SETFORMAT: {$e} ".$this->_LIBRARY_EASYXL->last_error());
	}
}
public function _do_call_EASYXL_SETCOLUMNFORMAT($p, $vm) {
	try {
		$ci = $this->_do_call_EASYXL_COLUMN_INDEX($p, $vm); 
		$v = $this->_LIBRARY_EASYXL->setColumnFormat($ci, $p[1]);
		return $v;
	}
	catch (Exception $e) {
     return $this->_RunTime_Exception("Fails in EASYXL_SETCOLUMNFORMAT: {$e} ".$this->_LIBRARY_EASYXL->last_error());
	}
}
public function _do_call_EASYXL_SETCOLUMNSTYLE($p, $vm) {
	try {
		$ci = $this->_do_call_EASYXL_COLUMN_INDEX($p, $vm); 
		$style = $p[1];
		$v = $this->_LIBRARY_EASYXL->setColumnStyle($ci, null,$style['fill']['color'],null);
		return $v;
	}
	catch (Exception $e) {
     return $this->_RunTime_Exception("Fails in EASYXL_SETCOLUMNCOLOR: {$e} ".$this->_LIBRARY_EASYXL->last_error());
	}
}
public function _do_call_EASYXL_GETTYPE($p, $vm) {
	try {
		if (count($p)==1) {
			$a = $this->_do_call_EASYXL_POSITION_TO_COORD($p, $vm);
		}
		else $a = array('row'=>($p[0]-1), 'column'=>$p[1]);
		$v = $this->_LIBRARY_EASYXL->getCellType($a['row'], $a['column']);
		return $v;
	}
	catch (Exception $e) {
     return $this->_RunTime_Exception("Fails in EASYXL_GETTYPE: {$e} ".$this->_LIBRARY_EASYXL->last_error());
	}
}
public function _do_call_EASYXL_SETTYPE($p, $vm) {
	try {
		if (count($p)==2) {
			$a = $this->_do_call_EASYXL_POSITION_TO_COORD($p, $vm);
			$type = $p[1];
		}
		else {$a = array('row'=>($p[0]-1), 'column'=>$p[1]); $type = $p[2];}
		$v = $this->_LIBRARY_EASYXL->setCellType($a['row'], $a['column'], $type);
		return $v;
	}
	catch (Exception $e) {
     return $this->_RunTime_Exception("Fails in EASYXL_SETTYPE: {$e} ".$this->_LIBRARY_EASYXL->last_error());
	}
}
public function _do_call_EASYXL_SETCELLVALUE($p, $vm) {
	try {
		if (count($p)==2) {
			$a = $this->_do_call_EASYXL_POSITION_TO_COORD($p, $vm);
			$value = $p[1];
		}
		else {$a = array('row'=>$p[0]-1, 'column'=>$p[1]);$value = $p[2];}
		$nvalue =   $this->_LIBRARY_EASYXL->setCellAt($a['row'],$a['column'],$value);
		return $nvalue;
	}
	catch (Exception $e) {
     return $this->_RunTime_Exception("Fails in EASYXL_SetCELL: {$e}  ".$this->_LIBRARY_EASYXL->last_error());
	}
}
public function _do_call_EASYXL_SETCELLHTML($p, $vm) {
	try {
		if (count($p)==2) {
			$a = $this->_do_call_EASYXL_POSITION_TO_COORD($p, $vm);
			$value = $p[1];
		}
		else {$a = array('row'=>$p[0]-1, 'column'=>$p[1]);$value = $p[2];}
		$nvalue =   $this->_LIBRARY_EASYXL->setCellAt($a['row'],$a['column'],$value, true);
		return $nvalue;
	}
	catch (Exception $e) {
     return $this->_RunTime_Exception("Fails in EASYXL_SetCELL_HTML: {$e}  ".$this->_LIBRARY_EASYXL->last_error());
	}
}
public function _do_call_EASYXL_COLUMN_WIDTH($p, $vm) {
	try {
		$this->_LIBRARY_EASYXL->setColumnWidth($p[0],$p[1]);
		return true;
	}
	catch (Exception $e) {
     return $this->_RunTime_Exception("Fails in EASYXL_SetCELL: {$e}  ".$this->_LIBRARY_EASYXL->last_error());
	}
}
public function _do_call_EASYXL_SETCELL_COLOR($p, $vm) {
	try {
		if (count($p)==3) {
			$a = $this->_do_call_EASYXL_POSITION_TO_COORD($p, $vm);
			$fore = $p[1];$back=$p[2];
		}
		else {$a = array('row'=>$p[0]-1, 'column'=>$p[1]);$fore = $p[2];$back=$p[3];}
		$this->_LIBRARY_EASYXL->setStyle($a['row'],$a['column'],HDA_EasyXL::COLOR_YELLOW,HDA_EasyXL::COLOR_BLUE,null);
		return true;
	}
	catch (Exception $e) {
     return $this->_RunTime_Exception("Fails in EASYXL_SetCELL: {$e}  ".$this->_LIBRARY_EASYXL->last_error());
	}
}
public function _do_call_EASYXL_SETCELL_STYLE($p, $vm) {
	try {
		if (count($p)==2) {
			$a = $this->_do_call_EASYXL_POSITION_TO_COORD($p, $vm);
			$style = $p[1];
		}
		else {$a = array('row'=>$p[0]-1, 'column'=>$p[1]);$style = $p[2];}
		$this->_LIBRARY_EASYXL->setStyle($a['row'],$a['column'],null,$style['fill']['color'],null);

		return true;
	}
	catch (Exception $e) {
     return $this->_RunTime_Exception("Fails in EASYXL_SetCELL: {$e}  ".$this->_LIBRARY_EASYXL->last_error());
	}
}
public function _do_call_EASYXL_SET_TITLE_CELLS($p, $vm) {
	try {
		$split = preg_match("/(?P<fc>[A-Z]{1,})(?P<fr>[\d]{1,})\:(?P<tc>[A-Z]{1,})(?P<tr>[\d]{1,})/i",$p[1],$matches);
		if ($split === false) throw new Exception("Bad title range ".$p[1]);
		$fc = PHPExcel_Cell::columnIndexFromString($matches['fc'])-1;
		$tc = PHPExcel_Cell::columnIndexFromString($matches['tc'])-1;
		$c = preg_match("/(?P<coln>[A-Z]{1,})(?P<rn>[\d]{1,})/i",$p[0], $match_cell);
		if ($c===false) throw new Exception("Invalid cell address ".$p[0]);
		if (is_null($match_cell['coln']) || is_null($match_cell['rn'])) throw new Exception("Invalid cell address ".$p[0]);
		$coln = PHPExcel_Cell::columnIndexFromString($match_cell['coln'])-1;
		$this->_LIBRARY_EASYXL->setTitleCells($match_cell['rn']-1,$coln,$matches['fr']-1,$fc,$matches['tr']-1,$tc,$p[2],$p[3]);
	}
	catch (Exception $e) {
     return $this->_RunTime_Exception("Fails in EASYXL_SET_TITLE_CELLS: {$e} ".$this->_LIBRARY_EASYXL->last_error());
	}
}
public function _do_call_EASYXL_MAKE_SHEET($p, $vm) {
	try {
		return $this->_LIBRARY_EASYXL->makeSheet($p[0]);
	}
	catch (Exception $e) {
     return $this->_RunTime_Exception("Fails in EASYXL_MAKE_SHEET: {$e} ".$this->_LIBRARY_EASYXL->last_error());
	}
}
public function _do_call_EASYXL_GET_SHEETS($p, $vm) {
	try {
		return $this->_LIBRARY_EASYXL->getSheets();
	}
	catch (Exception $e) {
     return $this->_RunTime_Exception("Fails in EASYXL_GET_SHEETS: {$e} ".$this->_LIBRARY_EASYXL->last_error());
	}
}
public function _do_call_EASYXL_QUICK_SHEET($p, $vm) {
	$this->_EASYXL_QUICK_SHEET = $p[0];
	return true;
}
public function _do_call_EASYXL_SHEET($p, $vm) {
	try {
		if (!$this->_EASYXL_QUICK_SHEET) {
			$tmp = "tmp/".uniqid()."_tmp_easy_xl";
			$this->_LIBRARY_EASYXL->saveAs($tmp,"xlsb");
			$this->_LIBRARY_EASYXL->close();
			$this->_LIBRARY_EASYXL = new HDA_EasyXL();
			$is_open = $this->_LIBRARY_EASYXL->open($tmp.".xlsb");
		}
		else {
			$is_open = true;
			$this->LIB_CONSOLE("Switched sheet to {$p[0]}");
		}
		$this->_LIBRARY_EASYXL->load($p[0]);
		return $is_open;
	}
	catch (Exception $e) {
     return $this->_RunTime_Exception("Fails in EASYXL_SHEET: {$e} ".$this->_LIBRARY_EASYXL->last_error());
	}
}
public function _do_call_EASYXL_SELECT_SHEET($p, $vm) {
   try {
		$this->_LIBRARY_EASYXL->openSheet($p[0]);
		return true;
   }
   catch(Exception $e) {
     return $this->_RunTime_Exception("Fails in EASYXL_SELECT_SHEET: {$e} ".$this->_LIBRARY_EASYXL->last_error());
    }
}
public function _do_call_EASYXL_DELETE_SHEET($p, $vm) {
	try {
		$this->_LIBRARY_EASYXL->removeSheet($p[0]);
	}
	catch (Exception $e) {
     return $this->_RunTime_Exception("Fails in EASYXL_DELETE SHEET: {$e} ".$this->_LIBRARY_EASYXL->last_error());
	}
}
public function _do_call_EASYXL_CLOSE($p, $vm) {
	try {
		$pi = pathinfo($p[0]);
		$this->_LIBRARY_EASYXL->saveAs($pi['dirname']."\\".$pi['filename'],$pi['extension']);
		return $this->_LIBRARY_EASYXL->close();
	}
	catch (Exception $e) {
     return $this->_RunTime_Exception("Fails in EASYXL_CLOSE: {$e} ".$this->_LIBRARY_EASYXL->last_error());
	}
}
public function _do_call_EASYXL_REMOVE_ROWS($p, $vm) {
	try {
		$r1 = $p[0]; $nRows = $p[1];
		return $this->_LIBRARY_EASYXL->removeRows($r1, $nRows);
	}
	catch (Exception $e) {
     return $this->_RunTime_Exception("Fails in EASYXL_REMOVE_ROWS: {$e} ".$this->_LIBRARY_EASYXL->last_error());
	}
}
public function _do_call_EASYXL_GET_ROW($p, $vm) {
	try {
		return $this->_LIBRARY_EASYXL->getRow($p[0]);
	}
	catch (Exception $e) {
     return $this->_RunTime_Exception("Fails in EASYXL_GET_ROW: {$e} ".$this->_LIBRARY_EASYXL->last_error());
	}
}
public function _do_call_EASYXL_DEBUG($p, $vm) {
	return $this->_LIBRARY_EASYXL->debug();
}

//** CATEGORY EXCEL

private $_LIBRARY_XL = null;
private $_XL_METHOD = null;
private $_USE_EASYXL = true;
public function _do_call_USE_EASYXL($p, $vm) {
	$this->_USE_EASYXL = $p[0];
	return true;
}
public function _do_call_USING_EASYXL($p,$vm) {
	return $this->_USE_EASYXL;
}
public function _do_call_XL_OPEN($p, $vm) {return ($this->_USE_EASYXL)?$this->_do_call_EASYXL_OPEN($p, $vm):$this->_do_call_EXCEL_OPEN($p, $vm);}
public function _do_call_EXCEL_OPEN($p, $vm) {
   /** EXCEL_OPEN(path to excel file, [method], [data_only]); Open an excel session; Return false/true;  **/
   if (count($p) < 1) return $this->_RunTime_Exception("Wrong parameter count for EXCEL_OPEN(excel file[, method])");
   try {
      $path = $p[0];
      if (!@file_exists($path)) {
         $this->_resolve_working_dir($vm);
         $path = $this->WORKING_DIR."/{$path}";
         }
      if (!@file_exists($path)) return $this->_RunTime_Exception("Fails in EXCEL_OPEN - file {$path} not found");
	  $method = (count($p)>1)?$p[1]:null;
	  $data_only = (count($p)>2)?$p[2]:true;
      $this->_LIBRARY_XL = new HDA_XL_Grid($path, $method, $data_only);
	  $this->_XL_METHOD = $method;
      if (is_null($this->_LIBRARY_XL) || !is_object($this->_LIBRARY_XL)) {
         $this->_LIBRARY_XL = null;
         return $this->_RunTime_Exception("Fails in EXCEL_OPEN");
         }
		$this->_LIBRARY_XL->SetCacheMethod("CELLS");
      return true;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in EXCEL_OPEN: {$e}");
      }
   return false;
   }
public function _do_call_XL_GET_SHEETS($p, $vm) {return ($this->_USE_EASYXL)?$this->_do_call_EASYXL_GET_SHEETS($p, $vm):$this->_do_call_EXCEL_GET_SHEETS($p, $vm);}
public function _do_call_EXCEL_GET_SHEETS($p, $vm) {
   /** EXCEL_GET_SHEETS(); Get the sheet list from an open excel session; Return false or list structure;  **/
   if (count($p)<>0) return $this->_RunTime_Exception("Wrong parameter count for EXCEL_GET_SHEETS()");
   if (is_null($this->_LIBRARY_XL)) return $this->_RunTime_Exception("Bad call EXCEL_GET_SHEETS - excel file not open");
   try {
      return $this->_LIBRARY_XL->GetSheets();
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in EXCEL_GET_SHEETS: {$e}");
      }
   return false;
   }
public function _do_call_XL_MAKE_SHEET($p, $vm) {return ($this->_USE_EASYXL)?$this->_do_call_EASYXL_MAKE_SHEET($p, $vm):$this->_do_call_EXCEL_MAKE_SHEET($p, $vm);}
public function _do_call_EXCEL_MAKE_SHEET($p, $vm) {
   /** EXCEL_MAKE_SHEET(title); Make new sheet in open excel session; Return false or sheet index;  **/
   if (count($p)<>1) return $this->_RunTime_Exception("Wrong parameter count for EXCEL_MAKE_SHEET()");
   if (is_null($this->_LIBRARY_XL)) return $this->_RunTime_Exception("Bad call EXCEL_MAKE_SHEET - excel file not open");
   try {
      return $this->_LIBRARY_XL->MakeSheet($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in EXCEL_MAKE_SHEET: {$e}");
      }
   return false;
   }
public function _do_call_XL_DELETE_SHEET($p, $vm) {return ($this->_USE_EASYXL)?$this->_do_call_EASYXL_DELETE_SHEET($p, $vm):$this->_do_call_EXCEL_DELETE_SHEET($p, $vm);}
public function _do_call_EXCEL_DELETE_SHEET($p, $vm) {
   /** EXCEL_DELETE_SHEET(title); Delete sheet in open excel session; Return false or true;  **/
   if (count($p)<>1) return $this->_RunTime_Exception("Wrong parameter count for EXCEL_DELETE_SHEET()");
   if (is_null($this->_LIBRARY_XL)) return $this->_RunTime_Exception("Bad call EXCEL_DELETE_SHEET - excel file not open");
   try {
      return $this->_LIBRARY_XL->DeleteSheet($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in EXCEL_DELETE_SHEET: {$e}");
      }
   return false;
   }
public function _do_call_XL_RESTRICT_ROWS($p, $vm) {return ($this->_USE_EASYXL)?true:$this->_do_call_EXCEL_RESTRICT_ROWS($p, $vm);}
public function _do_call_EXCEL_RESTRICT_ROWS($p, $vm) {
   /** EXCEL_RESTRICT_ROWS(row_start, row_end); Restrict the rows in an open excel session; Return false/true;  **/
   if (count($p)<>2) return $this->_RunTime_Exception("Wrong parameter count for EXCEL_RESTRICT_ROWS(row_start, row_end)");
   if (is_null($this->_LIBRARY_XL)) return $this->_RunTime_Exception("Bad call EXCEL_RESTRICT_ROWS - excel file not open");
   try {
      $this->_LIBRARY_XL->RestrictLoad(array($p[0], $p[1]), null, null);
      return true;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in EXCEL_RESTRICT_ROWS: {$e}");
      }
   return false;
   }
private function _xl_range($start_column, $end_column, $first_letters = '')
 {
   if (strlen($start_column)==1 && strlen($end_column)==1) return range($start_column,$end_column);
   $columns = array();
   $length = strlen($end_column);
   $letters = range('A', 'Z');

   // Iterate over 26 letters.
   foreach ($letters as $letter) {
       // Paste the $first_letters before the next.
       $column = $first_letters . $letter;

       // Add the column to the final array.
       $columns[] = $column;

       // If it was the end column that was added, return the columns.
       if ($column == $end_column)
           return $columns;
   }

   // Add the column children.
   foreach ($columns as $column) {
       // Don't itterate if the $end_column was already set in a previous itteration.
       // Stop iterating if you've reached the maximum character length.
       if (!in_array($end_column, $columns) && strlen($column) < $length) {
           $new_columns = $this->_xl_range('A', $end_column, $column);
           // Merge the new columns which were created with the final columns array.
           $columns = array_merge($columns, $new_columns);
       }
   }

   return $columns;
 }

public function _do_call_XL_RESTRICT_COLUMNS($p, $vm) {return ($this->_USE_EASYXL)?true:$this->_do_call_EXCEL_RESTRICT_COLUMNS($p, $vm);}
public function _do_call_EXCEL_RESTRICT_COLUMNS($p, $vm) {
   /** EXCEL_RESTRICT_COLUMNS(col_start, col_end); Restrict the columns in an open excel session; Return false/true;  **/
   if (count($p)<>2) return $this->_RunTime_Exception("Wrong parameter count for EXCEL_RESTRICT_COLUMNS(col_start, col_end)");
   if (is_null($this->_LIBRARY_XL)) return $this->_RunTime_Exception("Bad call EXCEL_RESTRICT_COLUMNS - excel file not open");
   try {
      $this->_LIBRARY_XL->RestrictLoad(null, $this->_xl_range($p[0], $p[1]), null);
      return true;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in EXCEL_RESTRICT_COLUMNS: {$e}");
      }
   return false;
   }
public function _do_call_XL_RESTRICT_SHEET($p, $vm) {return ($this->_USE_EASYXL)?true:$this->_do_call_EXCEL_RESTRICT_SHEET($p, $vm);}
public function _do_call_EXCEL_RESTRICT_SHEET($p, $vm) {
   if (count($p)<>1) return $this->_RunTime_Exception("Wrong parameter count for EXCEL_RESTRICT_SHEET(sheetname)");
   if (is_null($this->_LIBRARY_XL)) return $this->_RunTime_Exception("Bad call EXCEL_RESTRICT_SHEET - excel file not open");
   try {
	   if (!is_array($p[0])) $sheets = array($p[0]); else $sheets = $p[0];
      $this->_LIBRARY_XL->RestrictLoad(null, null, $sheets);
      return true;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in EXCEL_RESTRICT_SHEET: {$e}");
      }
   return false;
   }
public function _do_call_XL_LOAD($p, $vm) {return ($this->_USE_EASYXL)?$this->_do_call_EASYXL_LOAD($p,$vm):$this->_do_call_EXCEL_LOAD($p, $vm);}
public function _do_call_EXCEL_LOAD($p, $vm) {
   /** EXCEL_LOAD(); Load an open excel session; After open and restricts; Return false/true;  **/
   if (is_null($this->_LIBRARY_XL)) return $this->_RunTime_Exception("Bad call EXCEL_LOAD - excel file not open");
   try {
      $this->_LIBRARY_XL->Load();
	  if (count($p)==1) $this->_LIBRARY_XL->SetSheet($p[0]);
      return true;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in EXCEL_LOAD: {$e}");
      }
   return false;
   }
public function _do_call_XL_SHEET($p, $vm) {return ($this->_USE_EASYXL)?$this->_do_call_EASYXL_SHEET($p, $vm):$this->_do_call_EXCEL_SHEET($p, $vm);}
public function _do_call_EXCEL_SHEET($p, $vm) {
   /** EXCEL_SHEET(sheet_name); Restrict the sheet in an open excel session; Return false/true;  **/
   if (count($p)<>1) return $this->_RunTime_Exception("Wrong parameter count for EXCEL_SHEET(n)");
   if (is_null($this->_LIBRARY_XL)) return $this->_RunTime_Exception("Bad call EXCEL_SHEET - excel file not open");
   try {
      return $this->_LIBRARY_XL->SetSheet($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in EXCEL_SHEET: {$e}");
      }
   return false;
   }
public function _catch_xl_error($errno, $errmsg) {
   $this->_xl_error = $errmsg;
 //  throw new Exception($this->_xl_error);
   return true;
   }
private $_xl_error;

public function _do_call_XL_ARRAY($p, $vm) {return ($this->_USE_EASYXL)?$this->_do_call_EASYXL_ARRAY($p,$vm):$this->_do_call_EXCEL_ARRAY($p, $vm);}
public function _do_call_EXCEL_ARRAY($p, $vm) {
   /** EXCEL_ARRAY(); Get current open and loaded excel session as an array; Return false or array structure;  **/
   if (count($p)>1) return $this->_RunTime_Exception("Wrong parameter count for EXCEL_ARRAY([field map])");
   if (is_null($this->_LIBRARY_XL)) return $this->_RunTime_Exception("Bad call EXCEL_ARRAY - excel file not open");
   try {
	   $field_map = (count($p)==1)?$p[0]:null;
      $error_handler = set_error_handler(array($this,'_catch_xl_error'));
      $a = $this->_LIBRARY_XL->AsNoCalcArray();
      set_error_handler($error_handler);
	  if (!is_array($a)) throw new Exception($this->_xl_error);
	  if (!is_null($field_map)) {
		  $aa = array();
		  $aa[] = array(); // dummy as xl rows count from 1
		  foreach($a as $row) {
			$nrow = array();
			foreach($field_map as $field=>$cell) {
				$nrow[$field] = (array_key_exists($cell, $row))?$row[$cell]:null;
			}
			$aa[] = $nrow;
		  }
		  return $aa;
	  }
	  return $a;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in EXCEL_ARRAY: {$e}");
      }
   return null;
   }
public function _do_call_XL_WRITE_TABLE($p, $vm) {return ($this->_USE_EASYXL)?$this->_do_call_EASYXL_WRITE_TABLE($p,$vm):$this->_do_call_EXCEL_WRITE_TABLE($p, $vm);}
public function _do_call_EXCEL_WRITE_TABLE($p, $vm) {
   /** EXCEL_WRITE_TABLE(table, start row); Write table to surrent sheet; Return false or true;  **/
   if (count($p)<>2) return $this->_RunTime_Exception("Wrong parameter count for EXCEL_WRITE_TABLE([table], start_row)");
   if (is_null($this->_LIBRARY_XL)) return $this->_RunTime_Exception("Bad call EXCEL_WRITE_TABLE - excel file not open");
   try {
	   $ok = $this->_LIBRARY_XL->WriteTable($p[0], $p[1]);
	}
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in EXCEL_WRITE_TABLE: {$e}");
      }
   return null;
   }
public function _do_call_XL_SHEET_LIMITS($p, $vm) {return ($this->_USE_EASYXL)?$this->_do_call_EASYXL_SHEET_LIMITS($p,$vm):$this->_do_call_SHEET_LIMITS($p, $vm);}
public function _do_call_SHEET_LIMITS($p, $vm) {
   /** SHEET_LIMITS; Get highest row and column; Return false or struct;  **/
   if (count($p)<>0) return $this->_RunTime_Exception("Wrong parameter count for SHEET_LIMITS()");
   if (is_null($this->_LIBRARY_XL)) return $this->_RunTime_Exception("Bad call SHEET_LIMITS - excel file not open");
   try {
      $limits = $this->_LIBRARY_XL->GetSheetLimits();
	  $limits['COLUMNS'] = PHPExcel_Cell::columnIndexFromString($limits['COLUMNS']);
	  return $limits;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SHEET_LIMITS: {$e}");
      }
   return null;
   }
public function _do_call_XL_UNFREEZE_PANE($p, $vm) {return ($this->_USE_EASYXL)?true:$this->_do_call_UNFREEZE_PANE($p, $vm);}
public function _do_call_UNFREEZE_PANE($p, $vm) {
   /** Unfreeze pane  **/
   if (count($p)<>0) return $this->_RunTime_Exception("Wrong parameter count for unfreeze pane");
   if (is_null($this->_LIBRARY_XL)) return $this->_RunTime_Exception("Bad call UNFREEZE PANE - excel file not open");
   try {
      return $this->_LIBRARY_XL->UnFreezePane();
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in UnFreeze: {$e}");
      }
   return null;
   }
public function _do_call_XL_SET_COLUMN_WIDTH($p, $vm) {return ($this->_USE_EASYXL)?$this->_do_call_EASYXL_COLUMN_WIDTH($p,$vm):$this->_do_call_EXCEL_COLUMN_WIDTH($p, $vm);}
public function _do_call_EXCEL_COLUMN_WIDTH($p, $vm) {
   /** EXCEL_CELL(excel_coord[, column]); Excel coord or row/column pair; Get cell value from an open and loaded excel session; Return false or value;  **/
   if (count($p)<>2) return $this->_RunTime_Exception("Wrong parameter count for EXCEL_COLUMN_WIDTH[column,width])");
   if (is_null($this->_LIBRARY_XL)) return $this->_RunTime_Exception("Bad call EXCEL_COLUMN_WIDTH - excel file not open");
   try {
	  return true;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in EXCEL_COLUMN_WIDTH: {$e}");
      }
   return null;
   } 
public function _do_call_XL_CELL($p, $vm) {return ($this->_USE_EASYXL)?$this->_do_call_EASYXL_CELL($p,$vm):$this->_do_call_EXCEL_CELL($p, $vm);}
public function _do_call_EXCEL_CELL($p, $vm) {
   /** EXCEL_CELL(excel_coord[, column]); Excel coord or row/column pair; Get cell value from an open and loaded excel session; Return false or value;  **/
   if (count($p)<1) return $this->_RunTime_Exception("Wrong parameter count for EXCEL_CELL(coord, [column])");
   if (is_null($this->_LIBRARY_XL)) return $this->_RunTime_Exception("Bad call EXCEL_CELL - excel file not open");
   try {
      $v = (count($p)==2)?$this->_LIBRARY_XL->GetCellValue($p[0], $p[1]):$this->_LIBRARY_XL->GetCellValue($p[0], null, true);
	  return $v;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in EXCEL_CELL: {$e}");
      }
   return null;
   } 
public function _do_call_XL_SET_HTML_CELL($p, $vm) {return ($this->_USE_EASYXL)?$this->_do_call_EASYXL_SETCELLHTML($p,$vm):$this->_do_call_SET_EXCEL_CELL($p, $vm);}
public function _do_call_XL_SET_CELL($p, $vm) {return ($this->_USE_EASYXL)?$this->_do_call_EASYXL_SETCELLVALUE($p,$vm):$this->_do_call_SET_EXCEL_CELL($p, $vm);}
public function _do_call_SET_EXCEL_CELL($p, $vm) {
   /** SET_EXCEL_CELL(excel_coord, value[, column]); Excel coord or row/column pair; Set cell value from an open and loaded excel session; Return false or value;  **/
   if (count($p)<2) return $this->_RunTime_Exception("Wrong parameter count for SET EXCEL_CELL(coord, value[column])");
   if (is_null($this->_LIBRARY_XL)) return $this->_RunTime_Exception("Bad call SET EXCEL_CELL - excel file not open");
   try {
      $v = (count($p)==3)?$this->_LIBRARY_XL->SetCellValue($p[0], $p[1], $p[2]):$this->_LIBRARY_XL->SetCellValue($p[0],$p[1]);
	  return $v;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SET EXCEL_CELL: {$e}");
      }
   return null;
   }
public function _do_call_XL_SET_TITLE($p, $vm) {return ($this->_USE_EASYXL)?$this->_do_call_EASYXL_SET_TITLE_CELLS($p,$vm):$this->_do_call_SET_EXCEL_TITLE_CELLS($p, $vm);}
public function _do_call_SET_EXCEL_TITLE_CELLS($p, $vm) {
   /** SET_EXCEL_CELL($cell, $range, $title, $colour); Excel coord or row/column pair; Write merged column title **/
   if (count($p)<2) return $this->_RunTime_Exception("Wrong parameter count for SET EXCEL_TITLE_CELLS($row, $range, $title, $colour");
   if (is_null($this->_LIBRARY_XL)) return $this->_RunTime_Exception("Bad call SET EXCEL TITLE CELL - excel file not open");
   try {
      $v = $this->_LIBRARY_XL->SetCellTitle($p[0], $p[1], $p[2], $p[3]);
	  return $v;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SET EXCEL_TITLE_CELLS: {$e}");
      }
   return null;
	}
public function _do_call_XL_SET_CELL_STYLE($p, $vm) {return ($this->_USE_EASYXL)?$this->_do_call_EASYXL_SETCELL_STYLE($p,$vm):$this->_do_call_SET_EXCEL_CELL_STYLE($p, $vm);}
public function _do_call_SET_EXCEL_CELL_STYLE($p, $vm) {
   /** SET_EXCEL_CELL_STYLE(excel_coord, value[, column]); Excel coord or row/column pair; Get cell value from an open and loaded excel session; Return false or value;  **/
   if (count($p)<2) return $this->_RunTime_Exception("Wrong parameter count for EXCEL_CELL(coord, value[column])");
   if (is_null($this->_LIBRARY_XL)) return $this->_RunTime_Exception("Bad call EXCEL_CELL - excel file not open");
   try {
	   $style = $p[1];
      $v = (count($p)==3)?$this->_LIBRARY_XL->SetCellStyle($p[0], $style, $p[2]):$this->_LIBRARY_XL->SetCellStyle($p[0],$style);
	  return $v;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SET EXCEL_CELL STYLE: {$e}");
      }
   return null;
   }
public function _do_call_XL_SET_COLUMN_STYLE($p, $vm) {return ($this->_USE_EASYXL)?true:$this->_do_call_EXCEL_SET_COLUMN_STYLE($p, $vm);}
public function _do_call_EXCEL_SET_COLUMN_STYLE($p, $vm) {
   /** EXCEL_SET_COLUMN_STYLE(column array);  Return false or true;  **/
   if (count($p)<1) return $this->_RunTime_Exception("Wrong parameter count for EXCEL_SET_COLUMN_STYLE(stylearray)");
   if (is_null($this->_LIBRARY_XL)) return $this->_RunTime_Exception("Bad call EXCEL_SET_COLUMN_STYLE - excel file not open");
   try {
      $v = $this->_LIBRARY_XL->SetColumnStyle($p[0]);
	  return $v;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in EXCEL_SET_COLUMN_STYLE: {$e}");
      }
   return null;
	}
public function _do_call_XL_SET_SHARED_STYLE($p, $vm) {return ($this->_USE_EASYXL)?$this->_do_call_EASYXL_SETCELL_STYLE($p,$vm):$this->_do_call_EXCEL_SET_SHARED_STYLE($p, $vm);}
public function _do_call_EXCEL_SET_SHARED_STYLE($p, $vm) {
   /** EXCEL_SET_SHARED_STYLE(loc, color);  Return false or true;  **/
   if (count($p)!=2) return $this->_RunTime_Exception("Wrong parameter count for EXCEL_SET_SHARED_STYLE(loc, color)");
   if (is_null($this->_LIBRARY_XL)) return $this->_RunTime_Exception("Bad call EXCEL_SET_SHARED_STYLE - excel file not open");
   try {
	   $style = $p[1];
      $v = $this->_LIBRARY_XL->SetSharedStyle($p[0],$style);
	  return $v;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in EXCEL_SET_SHARED_STYLE: {$e}");
      }
   return null;
	}
public function _do_call_lookupColor($p, $vm) {
	return constant("HDA_EasyXL::COLOR_{$p[0]}");
}
public function _do_call_XL_GET_HASH($p, $vm) {return ($this->_USE_EASYXL)?0:$this->_do_call_EXCEL_GET_HASH($p, $vm);}
public function _do_call_EXCEL_GET_HASH($p, $vm) {
	return $p[0]->getHashCode();
}
public function _do_call_XL_USE_SHARED_STYLE($p, $vm) {return ($this->_USE_EASYXL)?$this->_do_call_EASYXL_SETCELL_STYLE($p,$vm):$this->_do_call_EXCEL_USE_SHARED_STYLE($p, $vm);}
public function _do_call_EXCEL_USE_SHARED_STYLE($p, $vm) {
   /** EXCEL_SET_SHARED_STYLE(style, loc);  Return false or true;  **/
   if (count($p)!=2) return $this->_RunTime_Exception("Wrong parameter count for EXCEL_SET_SHARED_STYLE(style, loc)");
   if (is_null($this->_LIBRARY_XL)) return $this->_RunTime_Exception("Bad call EXCEL_SET_SHARED_STYLE - excel file not open");
   try {
      $v = $this->_LIBRARY_XL->UseSharedStyle($p[0], $p[1]);
	  return $v;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in EXCEL_SET_SHARED_STYLE: {$e}");
      }
   return null;
	}
public function _do_call_XL_CELL_TYPE($p, $vm) {return ($this->_USE_EASYXL)?$this->_do_call_EASYXL_GETTYPE($p, $vm):$this->_do_call_EXCEL_CELL_TYPE($p, $vm);}
public function _do_call_EXCEL_CELL_TYPE($p, $vm) {
   /** EXCEL_CELL_TYPE(excel_coord[, column]); Excel coord or row/column pair; Get cell type from an open and loaded excel session; Return false or value;  **/
   if (count($p)<1) return $this->_RunTime_Exception("Wrong parameter count for EXCEL_CELL_TYPE(coord, [column])");
   if (is_null($this->_LIBRARY_XL)) return $this->_RunTime_Exception("Bad call EXCEL_CELL_TYPE - excel file not open");
   try {
      $v = (count($p)==2)?$this->_LIBRARY_XL->GetCellType($p[0], $p[1]):$this->_LIBRARY_XL->GetCellType($p[0]);
	  return $v;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in EXCEL_CELL_TYPE: {$e}");
      }
   return null;
   }
public function _do_call_XL_CELL_FORMAT($p, $vm) {return ($this->_USE_EASYXL)?$this->_do_call_EASYXL_GETFORMAT($p,$vm):$this->_do_call_EXCEL_CELL_FORMAT($p, $vm);}
public function _do_call_EXCEL_CELL_FORMAT($p, $vm) {
   /** EXCEL_CELL_FORMAT(excel_coord[, column]); Excel coord or row/column pair; Get cell type from an open and loaded excel session; Return false or value;  **/
   if (count($p)<1) return $this->_RunTime_Exception("Wrong parameter count for EXCEL_CELL_FORMAT(coord, [column])");
   if (is_null($this->_LIBRARY_XL)) return $this->_RunTime_Exception("Bad call EXCEL_CELL_FORMAT - excel file not open");
   try {
	  return "";
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in EXCEL_CELL_FORMAT: {$e}");
      }
   return null;
   }
public function _do_call_XL_SET_CELL_TYPE($p, $vm) {return ($this->_USE_EASYXL)?$this->_do_call_EASYXL_SETTYPE($p,$vm):$this->_do_call_SET_EXCEL_CELL_TYPE($p, $vm);}
public function _do_call_SET_EXCEL_CELL_TYPE($p, $vm) {
   /** SET_EXCEL_CELL_TYPE(excel_coord[, column]); Excel coord or row/column pair; Get cell type from an open and loaded excel session; Return false or value;  **/
   if (count($p)<1) return $this->_RunTime_Exception("Wrong parameter count for SET_EXCEL_CELL_TYPE(coord, [column])");
   if (is_null($this->_LIBRARY_XL)) return $this->_RunTime_Exception("Bad call SET_EXCEL_CELL_TYPE - excel file not open");
   try {
      $v = (count($p)==2)?$this->_LIBRARY_XL->SetCellType($p[0], $p[1]):$this->_LIBRARY_XL->SetCellType($p[0]);
	  return $v;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SET_EXCEL_CELL_TYPE: {$e}");
      }
   return null;
   }
public function _do_call_XL_REMOVE_ROWS($p, $vm) {return ($this->_USE_EASYXL)?$this->_do_call_EASYXL_REMOVE_ROWS($p,$vm):false;}
public function _do_call_XL_CLOSE($p, $vm) {return ($this->_USE_EASYXL)?$this->_do_call_EASYXL_CLOSE($p,$vm):$this->_do_call_EXCEL_CLOSE($p, $vm);}
public function _do_call_XL_SAVEAS($p, $vm) {return ($this->_USE_EASYXL)?$this->_do_call_EASYXL_SAVEAS($p,$vm):$this->_do_call_EXCEL_CLOSE($p, $vm);}
public function _do_call_EXCEL_CLOSE($p, $vm) {
   /** EXCEL_CLOSE(); Close an open and loaded excel session; Return false/true;  **/
   if (count($p)>1) return $this->_RunTime_Exception("Wrong parameter count for EXCEL_CLOSE()");
   if (is_null($this->_LIBRARY_XL)) return $this->_RunTime_Exception("Bad call EXCEL_CLOSE - excel file not open");
   try {
	  if ((count($p)==1) && ($p[0] != null)) {	
		$this->_LIBRARY_XL->SaveAs($p[0]);
	  }
	  else 
		$this->_LIBRARY_XL->Close();
	  $this->_LIBRARY_XL->ReleaseMemory();
      $this->_LIBRARY_XL = null; 
      return true;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in EXCEL_CLOSE: {$e}");
      }
   return false;
   }
   
public function _do_call_XL_RELEASE_MEMORY($p, $vm) {return ($this->_USE_EASYXL)?true:$this->_do_call_RELEASE_XL_MEMORY($p, $vm);}
public function _do_call_RELEASE_XL_MEMORY($p, $vm) {
	try {
		$this->_do_call_RELEASE_MEMORY($p, $vm);
		if (!is_null($this->_LIBRARY_XL)) {
			$this->_LIBRARY_XL->SaveAs($p[0]);
			$this->_LIBRARY_XL->ReleaseMemory();
			$this->_LIBRARY_XL = new HDA_XL_Grid($p[0], $this->_XL_METHOD, false);
			$this->_LIBRARY_XL->SetCacheMethod($this->_XL_METHOD);
			$this->_LIBRARY_XL->Load();
			$this->_LIBRARY_XL->SetSheet($p[1]);
			}
	}
	catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in EXCEL Release Memory: {$e}");
	}
	return true;
}
   
public function _do_call_EXCEL_DATE($p, $vm) {
   /** EXCEL_DATE(date value from excel); Convert an Excel date type to unix time; Return false or value;  **/
   try {
		$n = PHPExcel_Shared_Date::ExcelToPHP($p[0]);
		if (count($p)==1) return $n;
		return $n;
		$d = DateTime::createFromFormat($date($p[1],$n),$n);
		if ($d===false) {
			$e = print_r(DateTime::getLastErrors(), true);
			$this->CONSOLE_log .= date('G:i:s')."> {$p[0]} {$p[1]} {$e}\n";
			return false;
		}
		return $d->getTimeStamp();
		}
   catch (Exception $e) {
		$this->lastRunError = $e;
      return false;
      }
   return false;
   }
public function _do_call_XL_EXCEL_ADDRESS($p, $vm) {return ($this->_USE_EASYXL)?$this->_do_call_EASYXL_EXCEL_ADDRESS($p, $vm):$this->_do_call_EXCEL_ADDRESS($p, $vm);}
public function _do_call_EXCEL_ADDRESS($p, $vm) {
	return PHPExcel_Cell::stringFromColumnIndex($p[0]).$p[1];
}
public function _do_call_XL_COLUMN_INDEX($p, $vm) {return ($this->_USE_EASYXL)?$this->_do_call_EASYXL_COLUMN_INDEX($p, $vm):$this->_do_call_EXCEL_COLUMN_INDEX($p, $vm);}
public function _do_call_EXCEL_COLUMN_INDEX($p, $vm) {
	return PHPExcel_Cell::columnIndexFromString($p[0])-1;
}
public function _do_call_XL_POSITION_TO_COORD($p, $vm) {return ($this->_USE_EASYXL)?$this->_do_call_EASYXL_POSITION_TO_COORD($p, $vm):$this->_do_call_EXCEL_POSITION_TO_COORD($p, $vm);}
public function _do_call_EXCEL_POSITION_TO_COORD($p, $vm) {
	$m = preg_match("/(?P<column>[A-Z]{1,})(?P<row>[\d]{1,})/i",$p[0],$matches);
	if (!$m) throw new Exception("Invalid cell address {$p[0]}");
	$matches['column'] = PHPExcel_Cell::columnIndexFromString($matches['column'])-1;
	$matches['row'] = $matches['row']-1;
	return $matches;
}

public function _do_call_EXCEL_MERGE($p, $vm) {
   try {
		$file1 = $p[0]; $sheet1 = $p[1]; $file2 = $p[2]; $sheet2 = $p[3];
		$lib1 = new HDA_XL_Grid($file1, "CELLS", false);
		$lib2 = new HDA_XL_GRID($file2, "CELLS", false);
		$lib1->RestrictLoad(null, null, array($sheet1));
		$lib2->RestrictLoad(null, null, array($sheet2));
		$lib1->Load();
		$lib2->Load();
		$lib1->SetSheet($sheet1);
		$lib2->SetSheet($sheet2);
		$file1limits = $lib1->GetSheetLimits();
		$file2limits = $lib2->GetSheetLimits();
		$t = print_r($file2limits, true);
		$rows = $file2limits["ROWS"];
		$columns = PHPExcel_Cell::columnIndexFromString($file2limits["COLUMNS"]);
		$t .= "Columns ".$columns; $t .= " Rows ".$rows;
		$dst_row = $rows+1;
		for ($r = 2; $r<$rows; $r++) {
			for ($c = 0; $c<$columns;  $c++) {
				$column = PHPExcel_Cell::stringFromColumnIndex($c);
				$cell = $lib2->getCell($column.$r);
				if ($cell!==false) {
					$lib1->setCellValue($column.$dst_row, $cell->getValue());
					$style = $cell->getStyle();
					if ($style != null AND $style !== false) {
						$lib1->Worksheet()->duplicateStyle($style, $column.$dst_row);
					}
				}
			}
			$dst_row += 1;
		}
		//$lib1->Close();
		$lib2->Close();
		$lib1->SaveAs($p[4]);
		return $rows;
		}
   catch (Exception $e) {
		$this->lastRunError = $e;
		return $this->_RunTime_Exception("Fails in EXCEL MERGE: {$e}");
		return false;
		}
	return false;
}
// END XLS

//** CATEGORY FILE SYSTEM

public function _do_call_UNZIP($p, $vm) {
   /** UNZIP(file_path[, keep in directories[, delimiter]]); Unzip a file; Return false or result structure;  **/
   if (count($p)<1) return $this->_RunTime_Exception("Wrong parameter count for UNZIP(path[,keep_dir[,delimiter],lib_dir,keep_struct])");
   $this->_resolve_working_dir($vm);
   if (stripos($p[0], "\\") === false && stripos($p[0],"/")===false) $p[0] = "{$this->WORKING_DIR}/{$p[0]}";
   if (!@file_exists($p[0])) return $this->_RunTime_Exception("Requested file to unzip does not exist in UNZIP(path)");
   try {
      $lib_dir = (count($p)>3)?$p[3]:null;
	  $delimiter = (count($p)>2)?$p[2]:"";
	  $as_dir = (count($p)>4)?$p[4]:false;
	  $dir_filename = (count($p)>1)?$p[1]:false;
      $pack = HDA_unzip($p[0], $vm->ref, $problem, $lib_dir, $dir_filename, $delimiter, $as_dir);
      if (!is_null($problem)) return $this->_RunTime_Exception("Fails to unzip {$p[0]}: {$problem}");
      if (!is_array($pack)) return $this->_RunTime_Exception("Fails to unzip {$p[0]}");
      return $pack;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in UNZIP: {$e}");
      }
   return false;
   }
   
public function _do_call_ZIP($p, $vm) {
   /** ZIP(zip_file_path, file_list_to_zip); Zip a list of files; Return false/true;  **/
   if (count($p)<2) return $this->_RunTime_Exception("Wrong parameter count for ZIP(zip_path, file_list)");
   $this->_resolve_working_dir($vm);
   if (stripos($p[0], "\\") === false && stripos($p[0],"/")===false) $p[0] = "{$this->WORKING_DIR}/{$p[0]}";
   try {
	  if (!is_array($p[1])) return $this->_RunTime_Exception("Fails to zip to {$p[0]}: second param should be a file list");
      $pack = HDA_zip($p[0], $p[1], $problem);
      if (!is_null($problem)) return $this->_RunTime_Exception("Fails to zip to {$p[0]}: {$problem}");
      return $p[0];
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in ZIP: {$e}");
      }
   return false;
   }
   
public function _do_call_ZIP_DIRECTORY($p, $vm) {
   /** ZIP_DIRECTORY(zip_file_path, dir); Zip a dir; Return false/true;  **/
   if (count($p)<2) return $this->_RunTime_Exception("Wrong parameter count for ZIP_DIRECTORY(zip_path, dir)");
   try {
      $ok = HDA_zipDirectory($p[0], $p[1], $problem);
      if (!$ok) return $this->_RunTime_Exception("Fails to zip {$p[1]} to {$p[0]}: {$problem}");
      return true;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in ZIP_DIRECTORY: {$e}");
      }
   return false;
   }
   
public function _do_call_UNGZ($p, $vm) {
   /** UNGZ(gz_file_path, to_path); decompress gz; Return false/true;  **/
   if (count($p)<2) return $this->_RunTime_Exception("Wrong parameter count for UNGZ(gz_path, to_path");
   try {
	     $pathinfo = pathinfo($p[0]);
	     $fh = fopen($topath="{$p[1]}/{$pathinfo['filename']}",'w');
	     $a = gzfile($p[0]);
		 foreach($a as $line) {
		    fwrite($fh, "{$line}\n");
			}
		 fclose($fh);
		 return $topath;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in UNGZ: {$e}");
      }
   return false;
	}

// END ZIP

//** CATEGORY DATABASE
// SQL


// POSTGRES
private $PGSQL_QUERY_CONN = false;
private $PGSQL_QUERY_RESULT = false;


public function _do_call_PGSQL_CONNECT($p, $vm) {
   /** PGSQL_CONNECT(postgres_connect_global); Open a Postgres session; Return false/true;  **/
   /** PGSQL_CONNECT(host, database, user, password); Open a Postgres session; Return false/true;  **/
   if (count($p) == 1) {
      $a = hda_db::hdadb()->HDA_DB_dictionary(NULL, $p[0]);
      if (is_null($a) || !is_array($a) || count($a)<>1) {
         return $this->_RunTime_Exception("PGSQL_CONNECT using lookup {$p[0]}, dictionary entry not found");
         return false;
         }
      $def = $a[0]['Definition'];
      $p[0] = $def['Host'];
      $p[1] = $def['Schema'];
      $p[2] = $def['User'];
      $p[3] = $def['PassW'];
      }
   elseif (count($p) <> 4) return $this->_RunTime_Exception("Wrong parameter count for PGSQL_CONNECT(host, database, user, password)");
   $this->PGSQL_QUERY_CONN = null;
   $connection_info = "";
   $connection_info .= "host={$p[0]} ";
   $connection_info .= "dbname={$p[1]} ";
   $connection_info .= "user={$p[2]} ";
   $connection_info .= "password={$p[3]} ";
   try {
      $this->PGSQL_QUERY_CONN = pg_connect($connection_info);
      if ($this->PGSQL_QUERY_CONN===false) {
         $this->SQL_QUERY_ERROR = "Fails to connect ".pg_last_error();
         return $this->_RunTime_Exception("Fails to connect to Postgres");
         }
	  }
   catch (Exception $e) {
      $this->SQL_QUERY_ERROR = "Fails to connect ".pg_last_error();
      return $this->_RunTime_Exception("Fails to connect to Postgres");
      }
   return true;
   }

public function _do_call_PGSQL_QUERY($p, $vm) {
   /** PGSQL_QUERY(query_string); Execute a query on an open Postgres session; Return false/true;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for PGSQL_QUERY(query)");
   if ($this->PGSQL_QUERY_CONN===false) return $this->_RunTime_Exception("Connection failed to open");
   try {
      if (is_resource($this->PGSQL_QUERY_RESULT)) @pg_free_result($this->PGSQL_QUERY_RESULT);
      $this->PGSQL_QUERY_RESULT = @pg_query($this->PGSQL_QUERY_CONN, $p[0]);
      if ($this->PGSQL_QUERY_RESULT===false) {
         $this->SQL_QUERY_ERROR = "MSSQL Query Fail: ".pg_last_error();
         return $this->_RunTime_Exception("Query fails to Postgres {$p[0]} error {$this->SQL_QUERY_ERROR}");
         }
	  }
   catch (Exception $e) {
      $this->SQL_QUERY_ERROR = "Fails query ".pg_last_error();
      return $this->_RunTime_Exception("Query fails to Postgres {$p[0]} error {$e}");
      }
   return true;
   }
   
public function _do_call_PGSQL_FETCH_ALL($p, $vm) {
   /** PGSQL_FETCH_ALL(); Get all rows after an execute query on an open Postgres session; Return false or rows structure;  **/
   if ($this->PGSQL_QUERY_CONN===false) return $this->_RunTime_Exception("Connection failed to open");
   if ($this->PGSQL_QUERY_RESULT===false) return $this->_RunTime_Exception("Query failed to execute");
   try {
      return pg_fetch_all($this->PGSQL_QUERY_RESULT);
	  }
   catch (Exception $e) {
      $this->_RunTime_Exception("Fetch All Rows error {$e}");
      }
   return $a;
   }
   
public function _do_call_PGSQL_CLOSE($p, $vm) {
   /** PGSQL_CLOSE(); Close an open Postgres session; Return false/true;  **/
   if ($this->PGSQL_QUERY_CONN===false) return $this->_RunTime_Exception("Connection failed to open");
   pg_close($this->PGSQL_QUERY_CONN);
   $this->PGSQL_QUERY_RESULT = false;
   $this->PGSQL_QUERY_CONN = false;
   return true; 
   }
// END POSTGRES

// MICROSOFT SQLSERVER MSSQL
private $MSSQL_QUERY_CONN = false;
private $MSSQL_QUERY_RESULT = false;


public function _do_call_MSSQL_CONNECT($p, $vm) {
   /** MSSQL_CONNECT(mssql_connect_global); Open an MSSQL session; Return false/true;  **/
   /** MSSQL_CONNECT(host, database, user, password); Open an MSSQL session; Return false/true;  **/
   if (count($p) == 1) {
      $a = hda_db::hdadb()->HDA_DB_dictionary(NULL, $p[0]);
      if (is_null($a) || !is_array($a) || count($a)<>1) {
         return $this->_RunTime_Exception("MSSQL_CONNECT using lookup {$p[0]}, dictionary entry not found");
         return false;
         }
      $def = $a[0]['Definition'];
      $p[0] = $def['Host'];
      $p[1] = $def['Schema'];
      $p[2] = $def['User'];
      $p[3] = $def['PassW'];
      }
   elseif (count($p) <> 4) return $this->_RunTime_Exception("Wrong parameter count for MSSQL_CONNECT(host, database, user, password)");
   $this->MSSQL_QUERY_CONN = null;
   $connection_info = array();
   $connection_info['ReturnDatesAsStrings'] = true;
   if (!is_null($p[1]) && strlen($p[1])>0)  $connection_info['Database'] = $p[1];
   if (strlen($p[2])>0) { $connection_info['UID']=$p[2]; $connection_info['PWD']=$p[3]; }
   try {
      $this->MSSQL_QUERY_CONN = @sqlsrv_connect($p[0], $connection_info);
      if ($this->MSSQL_QUERY_CONN===false) {
         $this->SQL_QUERY_ERROR = "Fails to connect ".print_r(sqlsrv_errors(),true);
         return $this->_RunTime_Exception("Fails to connect to MS SqlServer");
         }
	  }
   catch (Exception $e) {
      $this->SQL_QUERY_ERROR = "Fails to connect ".print_r(sqlsrv_errors(),true);
      return $this->_RunTime_Exception("Fails to connect to MS SqlServer");
      }
   return true;
   }
public function _do_call_MSSQL_QUERY($p, $vm) {
   /** MSSQL_QUERY(query_string); Execute a query on an open MSSQL session; Return false/true;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for MSSQL_QUERY(query)");
   if ($this->MSSQL_QUERY_CONN===false) return $this->_RunTime_Exception("Connection failed to open");
   try {
      @sqlsrv_configure("WarningsReturnAsErrors",0);
      if (is_resource($this->MSSQL_QUERY_RESULT)) @sqlsrv_free_stmt($this->MSSQL_QUERY_RESULT);
      $this->MSSQL_QUERY_RESULT = @sqlsrv_query($this->MSSQL_QUERY_CONN, $p[0], null, array('Scrollable'=>SQLSRV_CURSOR_STATIC,'QueryTimeout' => 60000));
      if ($this->MSSQL_QUERY_RESULT===false) {
         $this->SQL_QUERY_ERROR = "MSSQL Query Fail: ".print_r(sqlsrv_errors(),true);
         return $this->_RunTime_Exception("Query fails to MS SQL {$p[0]} error {$this->SQL_QUERY_ERROR}");
         }
	  }
   catch (Exception $e) {
      $this->SQL_QUERY_ERROR = "Fails query ".print_r(sqlsrv_errors(),true);
      return $this->_RunTime_Exception("Query fails to MS SQL {$p[0]} error {$e}");
      }
   return true;
   }
public function _do_call_MSSQL_ROW_COUNT($p, $vm) {
   /** MSSQL_ROW_COUNT(); Get row count after an execute query on an open MSSQL session; Return false or row count;  **/
   if ($this->MSSQL_QUERY_CONN===false) return $this->_RunTime_Exception("Connection failed to open");
   if ($this->MSSQL_QUERY_RESULT===false) return $this->_RunTime_Exception("Query failed to execute");
   $rows = sqlsrv_num_rows($this->MSSQL_QUERY_RESULT);
   if ($rows===false) {
      $this->SQL_QUERY_ERROR = "Row Count fails ".print_r(sqlsrv_errors(),true);
      return $this->_RunTime_Exception("MSSQL_ROW_COUNT fails error {$this->SQL_QUERY_ERROR}");
      }
   return $rows;
   }
public function _do_call_MSSQL_ROWS_AFFECTED($p, $vm) {
   /** MSSQL_ROWS_AFFECTED(); Rows affected by last non fetch query; Return false or row count;  **/
   if ($this->MSSQL_QUERY_CONN===false) return $this->_RunTime_Exception("Connection failed to open");
   if ($this->MSSQL_QUERY_RESULT===false) return $this->_RunTime_Exception("Query failed to execute");
   $rows = sqlsrv_rows_affected($this->MSSQL_QUERY_RESULT);
   if ($rows===false) {
      $this->SQL_QUERY_ERROR = "Row Count fails ".print_r(sqlsrv_errors(),true);
      return $this->_RunTime_Exception("MSSQL_ROWS_AFFECTED fails error {$this->SQL_QUERY_ERROR}");
      }
   return $rows;
   }
public function _do_call_MSSQL_FETCH_ROW($p, $vm) {
   /** MSSQL_FETCH_ROW(); Get row after an execute query on an open MSSQL session; Return false or row structure;  **/
   if ($this->MSSQL_QUERY_CONN===false) return $this->_RunTime_Exception("Connection failed to open");
   if ($this->MSSQL_QUERY_RESULT===false) return $this->_RunTime_Exception("Query failed to execute");
   try {
      $a = @sqlsrv_fetch_array($this->MSSQL_QUERY_RESULT, SQLSRV_FETCH_ASSOC);
      if (is_null($a) || $a===false) return false;
      if (is_array($a)) foreach ($a as $k=>$p) $a[$k]=trim($p);
	  }
   catch (Exception $e) {
      $this->_RunTime_Exception("Fetch Row error {$e}");
      }
   return $a;
   }
public function _do_call_MSSQL_CLOSE($p, $vm) {
   /** MSSQL_CLOSE(); Close an open MSSQL session; Return false/true;  **/
   if ($this->MSSQL_QUERY_CONN===false) return $this->_RunTime_Exception("Connection failed to open");
   if (is_resource($this->MSSQL_QUERY_RESULT)) @sqlsrv_free_stmt($this->MSSQL_QUERY_RESULT);
   @sqlsrv_close($this->MSSQL_QUERY_CONN);
   $this->MSSQL_QUERY_RESULT = false;
   $this->MSSQL_QUERY_CONN = false;
   return true; 
   }
public function _do_call_MSSQL_STACK($p, $vm) {
   /** MSSQL_STACK(); Save current result;  **/
   $result = $this->MSSQL_QUERY_RESULT;
   $this->MSSQL_QUERY_RESULT = false;
   return $result; 
   }
public function _do_call_MSSQL_UNSTACK($p, $vm) {
   /** MSSQL_STACK(); Save current result;  **/
   $result = $this->MSSQL_QUERY_RESULT;
   $this->MSSQL_QUERY_RESULT = $p[0];
   return $result; 
   }

// ORACLE ORCL
private $ORCL_QUERY_CONN = false;
private $ORCL_QUERY_RESULT = false;

public function _do_call_ORCL_CONNECT($p, $vm) {
   /** ORCL_CONNECT(orcl_connect_global); Open an ORACLE session; Return false/true;  **/
   /** ORCL_CONNECT(user, password, connect_info); Open an ORACLE session; Return false/true;  **/
   if (count($p) == 1) {
      $a = hda_db::hdadb()->HDA_DB_dictionary(NULL, $p[0]);
      if (is_null($a) || !is_array($a) || count($a)<>1) {
         return $this->_RunTime_Exception("ORCL_CONNECT using lookup {$p[0]}, dictionary entry not found");
         return false;
         }
      $def = $a[0]['Definition'];
      $p[2] = $def['Host'];
      $p[0] = $def['User'];
      $p[1] = $def['PassW'];
      }
   elseif (count($p)==2) $p[2] = null;
   elseif (count($p) <>3) return $this->_RunTime_Exception("Wrong parameter count for ORCL_CONNECT(user, password [,connect_info])");
   $use_host = (!is_null($p[2]) && strlen($p[2])>0);
   $this->ORCL_QUERY_CONN = false;
   try {
	   $this->ORCL_QUERY_CONN = ($use_host)?@oci_connect($p[0],$p[1],$p[2]):@oci_connect($p[0], $p[1]);
	   if ($this->ORCL_QUERY_CONN===false) {
		  $err = @oci_error();
		  $this->SQL_QUERY_ERROR = "Fails to connect {$err['message']}";
		  return $this->_RunTime_Exception("Fails to connect to Oracle");
		  }
	  }
   catch (Exception $e) {
      $this->SQL_QUERY_ERROR = "Fails ORCL connect {$e}";
      return $this->_RunTime_Exception("Connect fails to ORCL {$p[0]} error {$e}");
      }
   return true;
   }
public function _do_call_ORCL_QUERY($p, $vm) {
   /** ORCL_QUERY(query_string); Execute a query on an open ORACLE session; Return false/true;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for ORCL_QUERY(query)");
   try {
	   $this->ORCL_QUERY_RESULT = oci_parse($this->ORCL_QUERY_CONN, "ALTER SESSION SET NLS_TIMESTAMP_FORMAT = 'YYYY-MM-DD HH24:MI:SS'");
	   @oci_execute($this->ORCL_QUERY_RESULT);
	   $this->ORCL_QUERY_RESULT = oci_parse($this->ORCL_QUERY_CONN, "ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD HH24:MI:SS'");
	   @oci_execute($this->ORCL_QUERY_RESULT);
	   $this->ORCL_QUERY_RESULT = oci_parse($this->ORCL_QUERY_CONN, "ALTER SESSION SET NLS_TIME_TZ_FORMAT = 'HH:MI:SS AM TZR'");
	   @oci_execute($this->ORCL_QUERY_RESULT);
	   $this->ORCL_QUERY_RESULT = @oci_parse($this->ORCL_QUERY_CONN, $p[0]);
	   if ($this->ORCL_QUERY_RESULT===false) {
		  $err = @oci_error($this->ORCL_QUERY_CONN);
		  $this->SQL_QUERY_ERROR = "Oracle Query Parse Fail: {$err['message']}";
		  return $this->_RunTime_Exception("Query (parse) fails to Oracle {$p[0]} error {$this->SQL_QUERY_ERROR}");
		  }
	   $ok = @oci_execute($this->ORCL_QUERY_RESULT);
	   if ($ok===false) {
		  $err = @oci_error($this->ORCL_QUERY_RESULT);
		  $this->SQL_QUERY_ERROR = "Oracle Query Execute Fail: {$err['message']}";
		  return $this->_RunTime_Exception("Query (execute) fails to Oracle {$p[0]} error {$this->SQL_QUERY_ERROR}");
		  }
	  }
   catch (Exception $e) {
      $this->SQL_QUERY_ERROR = "Fails ORCL query {$e}";
      return $this->_RunTime_Exception("Query fails to ORCL {$p[0]} error {$e}");
      }
   return true;
   }
public function _do_call_ORCL_ROW_COUNT($p, $vm) {
   /** ORCL_ROW_COUNT(); get row count after an execute query on an open ORACLE session; Return false or row count;  **/
   if ($this->ORCL_QUERY_CONN===false) return $this->_RunTime_Exception("Connection failed to open");
   if ($this->ORCL_QUERY_RESULT===false) return $this->_RunTime_Exception("Query failed to execute");
   try {
      return @oci_num_rows($this->ORCL_QUERY_RESULT);
      }
   catch (Exception $e) {
      $this->SQL_QUERY_ERROR = "Fails ORCL row count {$e}";
      return $this->_RunTime_Exception("Row Count fails to ORCL error {$e}");
      }
   }
public function _do_call_ORCL_FETCH_ROW($p, $vm) {
   /** ORCL_FETCH_ROW(); Fetch next row after an execute query on an open ORACLE session; Return false or row structure;  **/
   if ($this->ORCL_QUERY_CONN===false) return $this->_RunTime_Exception("Connection failed to open");
   if ($this->ORCL_QUERY_RESULT===false) return $this->_RunTime_Exception("Query failed to execute");
   try {
	   $a = @oci_fetch_array($this->ORCL_QUERY_RESULT, OCI_ASSOC);
	   if (is_null($a) || $a===false) return false;
	   if (is_array($a)) foreach ($a as $k=>$p) $a[$k]=trim($p);
	   return $a;
      }
   catch (Exception $e) {
      $this->SQL_QUERY_ERROR = "Fails ORCL fetch row {$e}";
      return $this->_RunTime_Exception("Fetch Row fails to ORCL error {$e}");
      }
   return false;
   }
public function _do_call_ORCL_CLOSE($p, $vm) {
   /** ORCL_CLOSE(); Close an open ORACLE session; Return false/true;  **/
   if ($this->ORCL_QUERY_CONN===false) return $this->_RunTime_Exception("Connection failed to open");
   try {
      @oci_close($this->ORCL_QUERY_CONN);
	  }
   catch (Exception $e) {
      $this->SQL_QUERY_ERROR = "Fails ORCL Close {$e}";
      return $this->_RunTime_Exception("Close fails to ORCL error {$e}");
      }
   $this->ORCL_QUERY_CONN = false;
   $this->ORCL_QUERY_RESULT = false;
   return true; 
   }

// ODBC
private $ODBC_QUERY_CONN = false;
private $ODBC_QUERY_RESULT = false;

public function _do_call_ODBC_CONNECT($p, $vm) {
   /** ODBC_CONNECT(odbc_global_connect); Open an ODBC session; Return false/true;  **/
   /** ODBC_CONNECT(dsn, user, password); Open an ODBC session; Return false/true;  **/
   if (count($p) == 1) {
      $a = hda_db::hdadb()->HDA_DB_dictionary(NULL, $p[0]);
      if (is_null($a) || !is_array($a) || count($a)<>1) {
         return $this->_RunTime_Exception("ODBC_CONNECT using lookup {$p[0]}, dictionary entry not found");
         return false;
         }
      $def = $a[0]['Definition'];
      $p[0] = $def['DSN'];
      $p[1] = $def['User'];
      $p[2] = $def['PassW'];
      }
   elseif (count($p) <> 3) return $this->_RunTime_Exception("Wrong parameter count for ODBC_CONNECT(dsn, user, password)");
   $this->ODBC_QUERY_CONN = false;
   $this->ODBC_QUERY_CONN = @odbc_connect($p[0], $p[1], $p[2]);
   if ($this->ODBC_QUERY_CONN===false) {
      $this->SQL_QUERY_ERROR = "Fails to connect ".@odbc_errormsg();
      return $this->_RunTime_Exception("Fails to connect to ODBC {$this->SQL_QUERY_ERROR} ");
      }
   return true;
   }
public function _do_call_ODBC_QUERY($p, $vm) {
   /** ODBC_QUERY(query_string); Execute a query on an open ODBC session; Return false/true;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for ODBC_QUERY(query)");
   if ($this->ODBC_QUERY_CONN===false) return $this->_RunTime_Exception("Connection failed to open");
   $this->ODBC_QUERY_RESULT = @odbc_exec($this->ODBC_QUERY_CONN, $p[0]);
   if ($this->ODBC_QUERY_RESULT===false) {
      $this->SQL_QUERY_ERROR = @odbc_errormsg($this->ODBC_QUERY_CONN);
      $this->SQL_QUERY_ERROR = "ODBC Query Fail: {$this->SQL_QUERY_ERROR}";
      return $this->_RunTime_Exception("Query fails to ODBC {$p[0]} error {$this->SQL_QUERY_ERROR}");
      }
   return true;
   }
public function _do_call_ODBC_ROW_COUNT($p, $vm) {
   /** ODBC_ROW_COUNT(); Get row count after an execute query on an open ODBC session; Return false or row count;  **/
   if ($this->ODBC_QUERY_CONN===false) return $this->_RunTime_Exception("Connection failed to open");
   if ($this->ODBC_QUERY_RESULT===false) return $this->_RunTime_Exception("Query failed to execute");
   return @odbc_num_rows($this->ODBC_QUERY_RESULT);
   }
public function _do_call_ODBC_FETCH_ROW($p, $vm) {
   /** ODBC_FETCH_ROW(); Fetch row after an execute query on an open ODBC session; Return false or row structure;  **/
   if ($this->ODBC_QUERY_CONN===false) return $this->_RunTime_Exception("Connection failed to open");
   if ($this->ODBC_QUERY_RESULT===false) return $this->_RunTime_Exception("Query failed to execute");
   $a = @odbc_fetch_array($this->ODBC_QUERY_RESULT);
   if (is_null($a) || $a===false) return false;
   if (is_array($a)) foreach ($a as $k=>$p) $a[$k]=trim($p);
   return $a;
   }
public function _do_call_ODBC_CLOSE($p, $vm) {
   /** ODBC_CLOSE(); Close an open ODBC session; Return false/true;  **/
   if ($this->ODBC_QUERY_CONN===false) return $this->_RunTime_Exception("Connection failed to open");
   @odbc_close($this->ODBC_QUERY_CONN);
   $this->ODBC_QUERY_CONN = false;
   $this->ODBC_QUERY_RESULT = false;
   return true; 
   }

// GENERAL SQL
public function _do_call_MAKE_INSERT($p, $vm) {
   /** MAKE_INSERT(field_value_structure, table_name[, field_map[, add_fields]); Return a SQL INSERT statement; Return false or insert string;  **/
   $use_lookup = null;
   $add_fields = null;
   if (count($p) == 4) $add_fields = $p[3];
   if (count($p) > 2) $use_lookup = $p[2];
   elseif (count($p) <> 2) return $this->_RunTime_Exception("Wrong parameter count for MAKE_INSERT(array, table_name[, lookup_table[,additional_fields]])");
   if (!is_array($p[0])) return $this->_RunTime_Exception("First parameter to MAKE_INSERT must be array");
   if (!is_null($add_fields) && !is_array($add_fields)) return $this->_RunTime_Exception("Additional Fields to MAKE_INSERT must be array");
   if (!is_null($use_lookup) && !is_array($use_lookup)) return $this->_RunTime_Exception("Lookup fields to MAKE_INSERT must be array");
   return $this->_make_insert($p[0], $p[1], $use_lookup, $add_fields);
   }
private function _make_insert($record, $table, $use_lookup, $add_fields) {
   $field_order = array();
   $s = "INSERT INTO {$table} (";
   foreach($record as $k=>$v) {
      if (is_null($use_lookup))
         { $s .= "[{$k}],"; $field_order[] = $k; }
      elseif (is_array($use_lookup) && array_key_exists($k, $use_lookup)) 
         { $s .= "[{$use_lookup[$k]}],"; $field_order[] = $k; }
      }
   if (!is_null($add_fields)) {
      foreach($add_fields as $k=>$v) {
	     if (is_array($use_lookup) && array_key_exists($k, $use_lookup)) $k = $use_lookup[$k];
         if (!in_array($k, $field_order)) { $s .= "[{$k}],"; $field_order[] = $k; }
         }
      }
   $s = trim($s, ',');
   $s .= ") VALUES (";
   foreach ($field_order as $k) {
      if (array_key_exists($k, $record)) $v = $record[$k];
	  elseif (is_array($add_fields) && array_key_exists($k, $add_fields)) $v = $add_fields[$k];
	  else $v = null;
	  if (!is_null($v)) $v = trim($v);
      if (is_null($v)) $s .= "NULL,";
      elseif (is_string($v)) { 
	     $v = trim($v); 
		 $v = str_replace(array("'"),"''",$v); 
		 $v = str_replace(array("\""),"",$v); 
		 $s .= "'{$v}',"; 
		 }
      elseif (is_object($v) && method_exists($v, "__toString")) $s .= "'".strval($v)."',";
      else $s .= "'{$v}',";
      }
   $s = trim($s, ',');
   $s .= ")";
   return $s;
   }
public function _do_call_MAKE_UPDATE($p, $vm) {
   /** MAKE_UPDATE(field_value_structure, table_name, where_on_field); Return a SQL UPDATE statement; Return false or update string;  **/
   if (count($p) <> 3) return $this->_RunTime_Exception("Wrong parameter count for MAKE_UPDATE(array, table_name, on_field)");
   if (!is_array($p[0])) return $this->_RunTime_Exception("First parameter to MAKE_UPDATE must be array");
   return $this->_make_update($p[0], $p[1], $p[2]);
   }
private function _make_update($record, $table, $on_field) {
   $s = "UPDATE {$table} SET";
   foreach($record as $k=>$v) {
      $s .= "[{$k}]=";
	  if (!is_null($v)) $v = trim($v);
      if (is_null($v)) $s .= "NULL,";
      elseif (is_string($v)) { 
	     $v = trim($v); 
		 $v = str_replace(array("'"),"''",$v); 
		 $v = str_replace(array("\""),"",$v); 
		 $s .= "'{$v}',"; 
		 }
      elseif (is_object($v) && method_exists($v, "__toString")) $s .= "'".strval($v)."',";
      else $s .= "'{$v}',";
      }
   $s = trim($s, ',');
   $s .= " WHERE $on_field='{$record[$on_field]}' ";
   return $s;
   }
public function _do_call_COPY_TABLE($p, $vm) {
   /** COPY_TABLE(param_block); Copy a SQL table to another; Return false/true;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for COPY_TABLE(param_block)");
   if (!is_array($p[0])) return $this->_RunTime_Exception("Expects parameter to COPY_TABLE(param_block) to be a variable with properties");
   $this->SQL_QUERY_ERROR = "";
   // Extract from param block;
   $src_connect = (array_key_exists('src_conn', $p[0]))?$p[0]['src_conn']:null;
   if (is_null($src_connect)) return $this->_RunTime_Exception("In Copy_Table expects parameter to have property \"src_conn\" ");
   $dst_connect = (array_key_exists('dst_conn', $p[0]))?$p[0]['dst_conn']:null;
   if (is_null($dst_connect)) return $this->_RunTime_Exception("In Copy_Table expects parameter to have property \"dst_conn\" ");
   $src_query = (array_key_exists('src_query', $p[0]))?$p[0]['src_query']:null;
   if (is_null($src_query)) return $this->_RunTime_Exception("In Copy_Table expects parameter to have property \"src_query\" ");
   $dst_table = (array_key_exists('dst_table_name', $p[0]))?$p[0]['dst_table_name']:null;
   if (is_null($dst_table)) return $this->_RunTime_Exception("In Copy_Table expects parameter to have property \"dst_table_name\" ");
   $checksum = (array_key_exists('checksum', $p[0]))?$p[0]['checksum']:null;
   if (!is_null($checksum)) {
      if (is_array($checksum) && array_key_exists('Field',$checksum) && array_key_exists('ProcessId',$checksum)) {
         $use_checksum = $checksum['Field'];
         $use_checksum_pid = $checksum['ProcessId'];
         }
      else return $this->_RunTime_Exception("In Copy_Table the property checksum should have properties Field and ProcessId  ");
      }
   else $use_checksum = null;
   $use_lookup = (array_key_exists('use_lookup', $p[0]))?$p[0]['use_lookup']:null;
   $add_fields = (array_key_exists('add_fields', $p[0]))?$p[0]['add_fields']:null;
   $unique_key = (array_key_exists('add_unique_key', $p[0]))?$p[0]['add_unique_key']:null;
   if (!is_null($unique_key)) {
      if (is_array($unique_key) && array_key_exists('Field',$unique_key) && array_key_exists('Columns',$unique_key) && is_array($unique_key['Columns'])) {
         $unique_key_field = $unique_key['Field'];
         $unique_key_columns = $unique_key['Columns'];
         $unique_key_separator = (array_key_exists('Separator',$unique_key))?$unique_key['Separator']:"";
         }
      else
         return $this->_RunTime_Exception("In Copy_Table the property add_unique_key should have properties Field and Columns also Columns should have numeric properties ");
      }
   $truncate = (array_key_exists('truncate', $p[0]))?$p[0]['truncate']:false;
   $create_query = (array_key_exists('create', $p[0]))?$p[0]['create']:false;
   $rollback = (array_key_exists('rollback', $p[0]))?$p[0]['rollback']:false;
   $validate = (array_key_exists('validate', $p[0]))?$p[0]['validate']:false;
   $process = (array_key_exists('process', $p[0]))?$p[0]['process']:null;
   if (!is_null($process) && $process!==false) {
      if (!is_array($process)) return $this->_RunTime_Exception("In Copy_Table the property process should have field properties  ");
      }
   $batch = (array_key_exists('batch', $p[0]))?$p[0]['batch']:false;
   $batch = (is_null($batch))?false:$batch;
   $batch_unc = (array_key_exists('batch_unc', $p[0]))?$p[0]['batch_unc']:null;
   $batch_path = (array_key_exists('batch_path', $p[0]))?$p[0]['batch_path']:null;
   $batch_path = (is_string($batch_path) && strlen($batch_path)>0)?$batch_path:null;
   $batch_field_order = (array_key_exists('batch_field_order', $p[0]))?$p[0]['batch_field_order']:null;
   if ($batch && (is_null($batch_field_order) || !is_array($batch_field_order))) 
      return $this->_RunTime_Exception("Requested batch load without a valid batch_field_order specified ");
   $batch_delimiter = (array_key_exists('batch_delimiter', $p[0]))?$p[0]['batch_delimiter']:',';
   $verbose = (array_key_exists('verbose', $p[0]))?$p[0]['verbose']:false;
   $copy_limit = (array_key_exists('copy_limit', $p[0]))?$p[0]['copy_limit']:null;
   if (!is_null($copy_limit) && $copy_limit<=0) $copy_limit = null;
   if (!is_null($copy_limit) && $verbose) $this->parent->_trace("Will use a copy LIMIT of {$copy_limit} records");

   // LOOKUP Dictionaries
   $src = hda_db::hdadb()->HDA_DB_dictionary(NULL, $src_connect);
   if (is_null($src) || !is_array($src) || count($src)<>1) {
      return $this->_RunTime_Exception("COPY_TABLE using source lookup {$src_connect}, dictionary entry not found");
      return false;
      }
   $src_def = $src[0]['Definition'];
   $dst = hda_db::hdadb()->HDA_DB_dictionary(NULL, $dst_connect);
   if (is_null($dst) || !is_array($dst) || count($dst)<>1) {
      return $this->_RunTime_Exception("COPY_TABLE using destination lookup {$dst_connect}, dictionary entry not found");
      return false;
      }
   $dst_def = $dst[0]['Definition'];

   // CONNECT TO SOURCE
   switch ($src_def["Connect Type"]) {
      case "ORCL": 
         $src_connect_id = @oci_connect($src_def['User'],$src_def['PassW'],$src_def['Host']);
         if ($src_connect_id===false) {
            $err = @oci_error();
            return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Fails to connect to Oracle {$err['message']}");
            }
         $src_query_result = oci_parse($src_connect_id, "ALTER SESSION SET NLS_TIMESTAMP_FORMAT = 'YYYY-MM-DD HH24:MI:SS'");
if ($src_query_result==false) echo "parse nls_timestamp_format ".oci_error($src_connect_id)."<br>";
         $ok = @oci_execute($src_query_result);
if ($ok==false) echo "execute nls_timestamp_format ".oci_error($src_query_result)."<br>";
         $src_query_result = oci_parse($src_connect_id, "ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD HH24:MI:SS'");
if ($src_query_result==false) echo "parse nls_date_format ".oci_error($src_connect_id)."<br>";
         $ok = @oci_execute($src_query_result);
if ($ok==false) echo "execute nls_date_format ".print_r(oci_error($src_query_result))."<br>";
         $src_query_result = oci_parse($src_connect_id, "ALTER SESSION SET NLS_TIME_TZ_FORMAT = 'HH:MI:SS AM TZR'");
if ($src_query_result==false) echo "parse nls_time_format ".oci_error($src_connect_id)."<br>";
         $ok = @oci_execute($src_query_result);
if ($ok==false) echo "execute nls_time_format ".oci_error($src_query_result)."<br>";
         $src_query_result = @oci_parse($src_connect_id, $src_query);
         if ($src_query_result===false) {
            $err = @oci_error($src_connect_id);
            return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Oracle Query Parse Fail: {$err['message']}");
            }
         $ok = @oci_execute($src_query_result);
         if ($ok===false) {
            $err = @oci_error($src_query_result);
            return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Oracle Query Execute Fail: {$err['message']}");
            }
         break;
      case "MSSQL":
         $src_connect_id = null;
         $connection_info = array();
         $connection_info['ReturnDatesAsStrings'] = true;
         if (array_key_exists('Schema',$src_def) && !is_null($src_def['Schema']) && strlen($src_def['Schema'])>0) 
            $connection_info['Database'] = $src_def['Schema'];
         if (strlen($src_def['User'])>0) { $connection_info['UID']=$src_def['User']; $connection_info['PWD']=$src_def['PassW']; }
         $src_connect_id = @sqlsrv_connect($src_def['Host'], $connection_info);
         if ($src_connect_id===false) {
            return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Fails to connect to ms sql source ".print_r(@sqlsrv_errors(),true));
            }
         @sqlsrv_configure("WarningsReturnAsErrors",0);
         @sqlsrv_configure("ReturnDatesAsStrings",true);
         $src_query_result = @sqlsrv_query($src_connect_id, $src_query, null, array('Scrollable'=>SQLSRV_CURSOR_STATIC,'QueryTimeout' => 60000));
         if ($src_query_result===false) {
            return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "MSSQL Query Fail: ".print_r(@sqlsrv_errors(),true));
            }
         break;
      case "ODBC":
         $src_connect_id = false;
         $src_connect_id = @odbc_connect($src_def['DSN'], $src_def['User'], $src_def['PassW']);
         if ($src_connect_id===false) {
            return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Fails to connect to ODBC source ".odbc_errormsg());
            }
         $src_query_result = @odbc_exec($src_connect_id, $src_query);
         if ($src_query_result===false) {
            $this->SQL_QUERY_ERROR = @odbc_errormsg($this->ODBC_QUERY_CONN);
            return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "ODBC Query Fail: {$this->SQL_QUERY_ERROR}");
            }
         break;
      default: 
         return $this->_RunTime_Exception("COPY_TABLE using source lookup {$src_connect}, dictionary entry not a Db Type");
         return false;
      }

   $dst_connect_id = null;
   // CREATE
   if ($create_query !== false) {
      switch ($dst_def["Connect Type"]) {
         case "ORCL":
            $dst_connect_id = @oci_connect($dst_def['User'],$dst_def['PassW'],$dst_def['Host']);
            if ($dst_connect_id===false) {
               $err = @oci_error();
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Fails to connect to Oracle {$err['message']}");
               }
            $dst_query_result = @oci_parse($dst_connect_id, $create_query);
            if ($dst_query_result===false) {
               $err = @oci_error($dst_connect_id);
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Oracle Create Query Parse Fail: {$err['message']}");
               }
            $ok = @oci_execute($dst_query_result);
            if ($ok===false) {
               $err = @oci_error($dst_query_result);
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Oracle Create Query Execute Fail: {$err['message']}");
               }
            break;
         case "MSSQL":
            $dst_connect_id = null;
            $connection_info = array();
            $connection_info['ReturnDatesAsStrings'] = true;
            if (array_key_exists('Schema',$dst_def) && !is_null($dst_def['Schema']) && strlen($dst_def['Schema'])>0) 
               $connection_info['Database'] = $dst_def['Schema'];
            if (strlen($dst_def['User'])>0) { $connection_info['UID']=$dst_def['User']; $connection_info['PWD']=$dst_def['PassW']; }
            $dst_connect_id = @sqlsrv_connect($dst_def['Host'], $connection_info);
            if ($dst_connect_id===false) {
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Fails to connect to ms sql destination ".print_r(@sqlsrv_errors(),true));
               }
            sqlsrv_configure("WarningsReturnAsErrors",0);
            $dst_query_result = @sqlsrv_query($dst_connect_id, $create_query, null, array('Scrollable'=>SQLSRV_CURSOR_STATIC,'QueryTimeout' => 60000));
            if ($dst_query_result===false) {
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "MSSQL Table Create Fail: ".print_r(@sqlsrv_errors(),true));
               }
            break;
         case "ODBC":
            $dst_connect_id = false;
            $dst_connect_id = @odbc_connect($dst_def['DSN'], $dst_def['User'], $dst_def['PassW']);
            if ($dst_connect_id===false) {
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Fails to connect to ODBC destination ".@odbc_errormsg());
               }
            $dst_query_result = @odbc_exec($dst_connect_id, $create_query);
            if ($dst_query_result===false) {
               $this->SQL_QUERY_ERROR = @odbc_errormsg($dst_connect_id);
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "ODBC Create Query Fail: {$this->SQL_QUERY_ERROR}");
               }
            break;
         default: 
            return $this->_RunTime_Exception("COPY_TABLE using destination lookup {$dst_connect}, dictionary entry not a Db Type");
            return false;
         }
      }


   // TRUNCATE
   if ($truncate !== false) {
      if ($truncate===true) $truncate_query = "TRUNCATE TABLE {$dst_table}";
      else $truncate_query = $truncate;

      switch ($dst_def["Connect Type"]) {
         case "ORCL":
            if (is_null($dst_connect_id)) {
               $dst_connect_id = @oci_connect($dst_def['User'],$dst_def['PassW'],$dst_def['Host']);
               if ($dst_connect_id===false) {
                  $err = @oci_error();
                  return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Fails to connect to Oracle {$err['message']}");
                  }
               }
            $dst_query_result = @oci_parse($dst_connect_id, $truncate_query);
            if ($dst_query_result===false) {
               $err = @oci_error($dst_connect_id);
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Oracle Truncate Query Parse Fail: {$err['message']}");
               }
            $ok = @oci_execute($dst_query_result);
            if ($ok===false) {
               $err = @oci_error($dst_query_result);
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Oracle Truncate Query Execute Fail: {$err['message']}");
               }
            break;
         case "MSSQL":
            if (is_null($dst_connect_id)) {
               $connection_info = array();
               $connection_info['ReturnDatesAsStrings'] = true;
               if (array_key_exists('Schema',$dst_def) && !is_null($dst_def['Schema']) && strlen($dst_def['Schema'])>0) 
                  $connection_info['Database'] = $dst_def['Schema'];
               if (strlen($dst_def['User'])>0) { $connection_info['UID']=$dst_def['User']; $connection_info['PWD']=$dst_def['PassW']; }
               $dst_connect_id = @sqlsrv_connect($dst_def['Host'], $connection_info);
               }
            if ($dst_connect_id===false) {
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Fails to connect to ms sql destination ".print_r(@sqlsrv_errors(),true));
               }
            @sqlsrv_configure("WarningsReturnAsErrors",0);
            $dst_query_result = @sqlsrv_query($dst_connect_id, $truncate_query, null, array('Scrollable'=>SQLSRV_CURSOR_STATIC,'QueryTimeout' => 60000));
            if ($dst_query_result===false) {
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "MSSQL Table Truncate Fail: ".print_r(@sqlsrv_errors(),true));
               }
            break;
         case "ODBC":
            if (is_null($dst_connect_id)) {
               $dst_connect_id = @odbc_connect($dst_def['DSN'], $dst_def['User'], $dst_def['PassW']);
               if ($dst_connect_id===false) {
                  return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Fails to connect to ODBC destination ".@odbc_errormsg());
                  }
               }
            $dst_query_result = @odbc_exec($dst_connect_id, $truncate_query);
            if ($dst_query_result===false) {
               $this->SQL_QUERY_ERROR = @odbc_errormsg($dst_connect_id);
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "ODBC Truncate Query Fail: {$this->SQL_QUERY_ERROR}");
               }
            break;
         default: 
            return $this->_RunTime_Exception("COPY_TABLE using destination lookup {$dst_connect}, dictionary entry not a Db Type");
            return false;
         }
      }

   // CONNECT TO DST
   if (is_null($dst_connect_id)) {
      switch ($dst_def["Connect Type"]) {
         case "ORCL":
            $dst_connect_id = @oci_connect($dst_def['User'],$dst_def['PassW'],$dst_def['Host']);
            if ($dst_connect_id===false) {
               $err = @oci_error();
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Fails to connect to Oracle {$err['message']}");
               }
            break;
         case "MSSQL":
            $connection_info = array();
            $connection_info['ReturnDatesAsStrings'] = true;
            if (array_key_exists('Schema',$dst_def) && !is_null($dst_def['Schema']) && strlen($dst_def['Schema'])>0) 
               $connection_info['Database'] = $dst_def['Schema'];
            if (strlen($dst_def['User'])>0) { $connection_info['UID']=$dst_def['User']; $connection_info['PWD']=$dst_def['PassW']; }
            $dst_connect_id = @sqlsrv_connect($dst_def['Host'], $connection_info);
            if ($dst_connect_id===false) {
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Fails to connect to ms sql destination ".print_r(@sqlsrv_errors(),true));
               }
            @sqlsrv_configure("WarningsReturnAsErrors",0);
            break;
         case "ODBC":
            $dst_connect_id = @odbc_connect($dst_def['DSN'], $dst_def['User'], $dst_def['PassW']);
            if ($dst_connect_id===false) {
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Fails to connect to ODBC destination ".@odbc_errormsg());
               }
            break;
         default: 
            return $this->_RunTime_Exception("COPY_TABLE using destination lookup {$dst_connect}, dictionary entry not a Db Type");
            return false;
         }
      }

   // BATCH
   if ($batch !== false) {
      if (!is_null($batch_path)) {
         $batch_file = $batch_path;
         $batch_file = "{$batch_file}/{$vm->ref}";
         if (!file_exists($batch_file)) @mkdir($batch_file);
         $batch_file .= "/output.txt";
         }
      elseif (!is_null($batch_unc)) {
         $batch_file = "\\\\{$batch_unc}\\{$vm->ref}";
         if (!file_exists($batch_file)) @mkdir($batch_file);
         $batch_file .= "/output.txt";
         }
      else {
         $this->_resolve_working_dir($vm);
         $batch_file = "{$this->WORKING_DIR}/output.txt";
         }
      $batch_stream = @fopen($batch_file, 'w');
      if ($batch_stream===false)
         return $this->_RunTime_Exception("COPY_TABLE requested batch stream, fails to open {$batch_file} for output");
      if (is_null($batch_path)) {
         if (!is_null($batch_unc))
            $base = "";
         else {
            $base = substr($_SERVER['SCRIPT_FILENAME'], 0, strlen($_SERVER['SCRIPT_FILENAME']) - strlen(strrchr($_SERVER['SCRIPT_FILENAME'], "\\")));
            $base .= "/";
            }
         $batch_file = "{$base}{$batch_file}";
         $batch_file = preg_replace("#/#i","\\",$batch_file);
         }
      if ($verbose) $this->parent->_trace("Using batch path of {$batch_file}");
      }
 
   // COPY
   $copying = true;
   $record_count = 0;
   $checksum = 0;
   // ROLLBACK
   if ($rollback !== false) {
      $rollback_query = "_DELETE FROM {$dst_table} WHERE ";
      foreach($rollback as $field=>$v) $rollback_query .= " {$field}='{$v}' AND";
      $rollback_query = trim(trim($rollback_query,"AND"),"_");
      if ($verbose) $this->parent->_trace("Will use rollback query {$rollback_query}");
      }
   else $rollback_query = null;

   while ($copying) {
      set_time_limit(0);
      $a = null;
      switch ($src_def["Connect Type"]) {
         case "ORCL": 
            $a = @oci_fetch_array($src_query_result, OCI_ASSOC);
            if ($a===false || is_null($a)) $copying = false;
            break;
         case "MSSQL":
            $a = @sqlsrv_fetch_array($src_query_result, SQLSRV_FETCH_ASSOC);
            if ($a===false) {
               $this->_copy_table_rollback($dst_connect_id, $rollback_query, $dst_def["Connect Type"]);
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Fails to fetch record {$record_count} ".print_r(@sqlsrv_errors(),true));
               }
            if (is_null($a)) $copying = false;
            break;
         case "ODBC":
            $a = odbc_fetch_array($src_query_result);
            if ($a===false) {
               $this->_copy_table_rollback($dst_connect_id, $rollback_query, $dst_def["Connect Type"]);
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "ODBC fail to fetch record {$record_count} ".odbc_errormsg());
               }
            if (is_null($a)) $copying = false;
            break;
         }
      // VALIDATE
      if (!is_null($validate) && $validate!==false && is_array($validate) && is_array($a)) {
         foreach($a as $field=>$v) {
            if (array_key_exists($field,$validate) && is_array($validate[$field]) && array_key_exists('Using',$validate[$field])) {
               $result = HDA_validate($validate[$field]['Using'], $v, $error);
               if ($result===false) {
                  if (!array_key_exists('Proxy',$validate[$field])) {
                     $this->_copy_table_rollback($dst_connect_id, $rollback_query, $dst_def["Connect Type"]);
                     return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "In Copy_Table at record {$record_count} fails to validate field {$field} with value {$v} with error {$error}");
                     }
                  else {
                     $this->parent->_trace("Copy Table: validate for {$field} fails at record {$record_count} with value {$v} and error {$error} will continue using PROXY {$validate[$field]['Proxy']}");
                     $a[$field] = $validate[$field]['Proxy'];
                     }
                  }
               }
            }
         }
      // PROCESS
      if (is_array($process) && is_array($a) && $copying) {
         foreach($process as $field=>$code) {
            if (array_key_exists($field,$a)) $a[$field] = $this->_value_adjust($a[$field],$code);
            }
         }

      // WRITE CHECKSUM
      if (!is_null($use_checksum) && $copying && is_array($a) && array_key_exists($use_checksum, $a)) {
         $chk = trim($a[$use_checksum]);
         $checksum += crc32($chk);
         }

      // FORM UNIQUE KEY
      if (!is_null($unique_key) && $copying && is_array($a)) {
         $ukey = "";
         for ($i = 0; $i<count($unique_key_columns); $i++) {
            if (!array_key_exists($unique_key_columns[$i], $a))
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "In Copy_Table at record {$record_count} building a unique key the column {$unique_key_columns[$i]} does not exist");
            $ukey .= trim($a[$unique_key_columns[$i]])."{$unique_key_separator}";
            }
         $ukey = trim($ukey,$unique_key_separator);
         $add_fields[$unique_key_field] = $ukey;
         }

      // BATCH
      if($batch!==false && $copying) {
         if (is_array($a)) {
            $s = "";
            foreach ($batch_field_order as $field=>$p) {
               if (is_null($use_lookup) || (is_array($use_lookup) && array_key_exists($field,$use_lookup))) {
                  if (array_key_exists($field, $a)) $v = $a[$field];
                  elseif (is_array($add_fields) && array_key_exists($field, $add_fields)) $v = $add_fields[$field];
                  else $v = "";
                  }
               else $v = "";
               $s .= "".trim($v)."{$batch_delimiter}";
               }
            $s = trim($s,$batch_delimiter)."\n";
            $bytes_written = @fwrite($batch_stream, $s);
            if ($bytes_written===false) return $this->_RunTime_Exception("In Copy_Table, fails to write record {$record_count} to batch file");
            $record_count++;
            if (!is_null($copy_limit) && ($record_count>=$copy_limit)) $copying = false;
            }
         }
      elseif ($copying) {
         // WRITE RECORD
         $insert_query = ($copying && !is_null($a) && $a!==false)?$this->_make_insert($a, $dst_table, $use_lookup, $add_fields):null;
         if (!is_null($insert_query)) {
            switch ($dst_def["Connect Type"]) {
               case "ORCL": 
                  $dst_query_result = @oci_parse($dst_connect_id, $insert_query);
                  if ($dst_query_result===false) {
                     $err = @oci_error($dst_connect_id);
                     $this->SQL_QUERY_ERROR = "Oracle Insert Query Parse Fail: {$err['message']}";
                     $this->_copy_table_rollback($dst_connect_id, $rollback_query, $dst_def["Connect Type"]);
                     return $this->_RunTime_Exception( $this->SQL_QUERY_ERROR );
                     }
                  $ok = @oci_execute($dst_query_result);
                  if ($ok===false) {
                     $err = @oci_error($dst_query_result);
                     $this->SQL_QUERY_ERROR = "Oracle Insert Query Execute Fail: {$err['message']}";
                     $this->_copy_table_rollback($dst_connect_id, $rollback_query, $dst_def["Connect Type"]);
                     return $this->_RunTime_Exception( $this->SQL_QUERY_ERROR  );
                     }
                  break;
               case "MSSQL":
                  $dst_query_result = @sqlsrv_query($dst_connect_id, $insert_query, null, array('Scrollable'=>SQLSRV_CURSOR_STATIC,'QueryTimeout' => 60000));
                  if ($dst_query_result===false) {
                     $this->SQL_QUERY_ERROR = "MSSQL Insert Query Fail record {$record_count} with insert {$insert_query}: ".print_r(@sqlsrv_errors(),true);
                     $this->_copy_table_rollback($dst_connect_id, $rollback_query, $dst_def["Connect Type"]);
                     return $this->_RunTime_Exception($this->SQL_QUERY_ERROR);
                     }
                  break;
               case "ODBC":
                  $dst_query_result = @odbc_exec($dst_connect_id, $insert_query);
                  if ($dst_query_result===false) {
                     $this->SQL_QUERY_ERROR = @odbc_errormsg($dst_connect_id);
                     $this->SQL_QUERY_ERROR = "ODBC Insert Query Fail: {$this->SQL_QUERY_ERROR}";
                     $this->_copy_table_rollback($dst_connect_id, $rollback_query, $dst_def["Connect Type"]);
                     return $this->_RunTime_Exception( $this->SQL_QUERY_ERROR  );
                     }
                  break;
               }
            if ($dst_query_result===false) $copying = false;
            else $record_count++;
			if (($record_count % 100)==0) hda_db::hdadb()->HDA_DB_putMonitorMessage("Copied {$record_count} records");
            if (!is_null($copy_limit) && ($record_count>$copy_limit)) $copying = false;
            }
         }
      }

   switch ($src_def["Connect Type"]) {
      case "ORCL": @oci_close($src_connect_id);break;
      case "MSSQL": @sqlsrv_close($src_connect_id); break;
      case "ODBC": @odbc_close($src_connect_id); break;
      }
   if ($verbose) $this->parent->_trace("Copy Table: copies {$record_count} records");

   // BATCH
   if ($batch !== false) {
      @fclose($batch_stream);
      switch ($dst_def["Connect Type"]) {
         case "ORCL":
            $bulkload_query = "BULK INSERT {$dst_table} FROM '{$batch_file}' WITH (FIELDTERMINATOR='{$batch_delimiter}' ,ROWTERMINATOR='\n') ";
            $dst_query_result = @oci_parse($dst_connect_id, $bulkload_query);
            if ($dst_query_result===false) {
               $err = @oci_error($dst_connect_id);
               $this->_copy_table_rollback($dst_connect_id, $rollback_query, $dst_def["Connect Type"]);
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Oracle Bulk Load Query Parse Fail: {$err['message']}");
               }
            $ok = @oci_execute($dst_query_result);
            if ($ok===false) {
               $err = @oci_error($dst_query_result);
               $this->_copy_table_rollback($dst_connect_id, $rollback_query, $dst_def["Connect Type"]);
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Oracle Bulk Load Execute Fail: {$err['message']}");
               }
            break;
         case "MSSQL":
            $bulkload_query = "BULK INSERT {$dst_table} FROM '{$batch_file}' WITH (FIELDTERMINATOR='{$batch_delimiter}',ROWTERMINATOR='\n') ";
            $dst_query_result = @sqlsrv_query($dst_connect_id, $bulkload_query, null, array('Scrollable'=>SQLSRV_CURSOR_STATIC,'QueryTimeout' => 60000));
            if ($dst_query_result===false) {
               $errs = print_r(@sqlsrv_errors(),true);
               $this->_copy_table_rollback($dst_connect_id, $rollback_query, $dst_def["Connect Type"]);
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "MSSQL Bulk Load Fail: {$errs}");
               }
            break;
         case "ODBC":
            $bulkload_query = "BULK INSERT {$dst_table} FROM '{$batch_file}' WITH (FIELDTERMINATOR='{$batch_delimiter}')";
            $dst_query_result = @odbc_exec($dst_connect_id, $bulkload_query);
            if ($dst_query_result===false) {
               $errs = @odbc_errormsg($dst_connect_id);
               $this->_copy_table_rollback($dst_connect_id, $rollback_query, $dst_def["Connect Type"]);
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "ODBC Bulk Load Query Fail: {$errs}");
               }
            break;
         }
      }

   // CHECKSUM
   if (!is_null($use_checksum)) {
      $dst_checksum = $use_checksum;
      if (!is_null($use_lookup) && array_key_exists($use_checksum,$use_lookup)) $dst_checksum = $use_lookup[$use_checksum];
      $dst_checksum_pid = $use_checksum_pid;
      $checksum_pid = null;
      if (!is_null($use_lookup) && array_key_exists($use_checksum_pid,$use_lookup)) $dst_checksum_pid = $use_lookup[$use_checksum_pid];
      if (!is_null($add_fields) && array_key_exists($use_checksum_pid,$add_fields)) $checksum_pid = $add_fields[$use_checksum_pid];
      if (is_null($checksum_pid)) 
         return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Checksum: unable to get value for checksum ident: missing checksum field in add fields?");
      $check_query = "SELECT {$dst_checksum} FROM {$dst_table} WHERE {$dst_checksum_pid}='{$checksum_pid}'";
      $validated_checksum = 0;
      $checksum_records = 0;
      switch ($dst_def["Connect Type"]) {
         case "ORCL": 
            $dst_query_result = @oci_parse($dst_connect_id, $check_query);
            if ($dst_query_result===false) {
               $err = @oci_error($dst_connect_id);
               $this->_copy_table_rollback($dst_connect_id, $rollback_query, $dst_def["Connect Type"]);
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Oracle Checksum Query Parse Fail: {$err['message']}");
               }
            $ok = @oci_execute($dst_query_result);
            if ($ok===false) {
               $err = @oci_error($dst_query_result);
               $this->_copy_table_rollback($dst_connect_id, $rollback_query, $dst_def["Connect Type"]);
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Oracle Checksum Query Execute Fail: {$err['message']}");
               }
            while ($a = @oci_fetch_array($dst_query_result, OCI_ASSOC)) {
               $chk = trim($a[$dst_checksum]);
               $validated_checksum += crc32($chk);$checksum_records++;
               }
            break;
         case "MSSQL": 
            $dst_query_result = @sqlsrv_query($dst_connect_id, $check_query, null, array('Scrollable'=>SQLSRV_CURSOR_STATIC,'QueryTimeout' => 60000));
            if ($dst_query_result===false) {
               $this->SQL_QUERY_ERROR = "MSSQL Checksum Query Fail: ".print_r(@sqlsrv_errors(),true);
               $this->_copy_table_rollback($dst_connect_id, $rollback_query, $dst_def["Connect Type"]);
               return $this->_RunTime_Exception( $this->SQL_QUERY_ERROR  );
               }
            while ($a = @sqlsrv_fetch_array($dst_query_result, SQLSRV_FETCH_ASSOC)) {
               $chk = trim($a[$dst_checksum]);
               $validated_checksum += crc32($chk);$checksum_records++;
               }
            break;
         case "ODBC":
            $dst_query_result = @odbc_exec($dst_connect_id, $check_query);
            if ($dst_query_result===false) {
               $this->SQL_QUERY_ERROR = @odbc_errormsg($dst_connect_id);
               $this->_copy_table_rollback($dst_connect_id, $rollback_query, $dst_def["Connect Type"]);
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "ODBC Checksum Query Fail: {$this->SQL_QUERY_ERROR}");
               }
            while ($a = @odbc_fetch_array($dst_query_result)) {
               $chk = trim($a[$dst_checksum]);
               $validated_checksum += crc32($chk);$checksum_records++;
               }
            break;
         }

      }
   if (!is_null($use_checksum)) {
      if ($record_count<>$checksum_records) $err = "Mismatch between copied records {$record_count} and checksum records {$checksum_records} ";
      elseif ($checksum <> $validated_checksum) $err = "Checksums differ {$checksum} and {$validated_checksum}";
      else $err = false; 
      if ($err !== false) {
         $this->_copy_table_rollback($dst_connect_id, $rollback_query, $dst_def["Connect Type"]);
         return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Checksum {$use_checksum} check: {$err}");
         }
      }

   switch ($dst_def["Connect Type"]) {
      case "ORCL": @oci_close($dst_connect_id);break;
      case "MSSQL": @sqlsrv_close($dst_connect_id); break;
      case "ODBC": @odbc_close($dst_connect_id); break;
      }

   if ($verbose) $this->parent->_trace("Completed copy to {$dst_table} of {$record_count} records");
   return $record_count;
   }
private function _copy_table_rollback($dst_connect_id, $rollback_query, $connect_type) {
   if (is_null($rollback_query) || strlen($rollback_query)==0 || is_null($dst_connect_id)) return;
   switch ($connect_type) {
      case "ORCL":
         $dst_query_result = @oci_parse($dst_connect_id, $rollback_query);
         if ($dst_query_result===false) {
            $err = @oci_error($dst_connect_id);
            $this->_RunTime_Exception("Oracle Rollback Query Parse Fail: {$err['message']}");
            }
         $ok = @oci_execute($dst_query_result);
         if ($ok===false) {
            $err = @oci_error($dst_query_result);
            $this->_RunTime_Exception("Oracle Rollback Execute Fail: {$err['message']}");
            }
         @oci_close($dst_connect_id);
         break;
      case "MSSQL":
         $dst_query_result = @sqlsrv_query($dst_connect_id, $rollback_query, null, array('Scrollable'=>SQLSRV_CURSOR_STATIC,'QueryTimeout' => 60000));
         if ($dst_query_result===false) {
            $this->_RunTime_Exception("MSSQL Rollback Query Fail: ".print_r(@sqlsrv_errors(),true));
            }
         @sqlsrv_close($dst_connect_id);
         break;
      case "ODBC":
         $dst_query_result = @odbc_exec($dst_connect_id, $rollback_query);
         if ($dst_query_result===false) {
            $err = @odbc_errormsg($dst_connect_id);
            $this->_RunTime_Exception("ODBC Rollback Query Fail: {$err}");
            }
         @odbc_close($dst_connect_id);
         break;
      }
   }
public function _do_call_QUERY_TABLE_OPEN($p, $vm) {
   /** QUERY_TABLE(param_block $p); Execute a query $p.src_query on $p.src_connect with data as $p.no_data_query, $; Return false or result structure;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for QUERY_TABLE(param_block)");
   if (!is_array($p[0])) return $this->_RunTime_Exception("Expects parameter to QUERY_TABLE(param_block) to be a variable with properties");
   $this->SQL_QUERY_ERROR = "";
   // Extract from param block;
   $src_connect = (array_key_exists('src_conn', $p[0]))?$p[0]['src_conn']:null;
   if (is_null($src_connect)) return $this->_RunTime_Exception("In Query_Table expects parameter to have property \"src_conn\" ");
   $verbose = (array_key_exists('verbose', $p[0]))?$p[0]['verbose']:false;

   // LOOKUP Dictionaries
   $src = hda_db::hdadb()->HDA_DB_dictionary(NULL, $src_connect);
   if (is_null($src) || !is_array($src) || count($src)<>1) {
      return $this->_RunTime_Exception("QUERY_TABLE using source lookup {$src_connect}, dictionary entry not found");
      return false;
      }
   $src_def = $src[0]['Definition'];

   // CONNECT TO SOURCE
	 $src_connect_id = null;
	 $connection_info = array();
	 $connection_info['ReturnDatesAsStrings'] = true;
	 $connection_info['TransactionIsolation'] = SQLSRV_TXN_READ_UNCOMMITTED;
	 if (array_key_exists('Schema',$src_def) && !is_null($src_def['Schema']) && strlen($src_def['Schema'])>0) 
		$connection_info['Database'] = $src_def['Schema'];
	 if (strlen($src_def['User'])>0) { $connection_info['UID']=$src_def['User']; $connection_info['PWD']=$src_def['PassW']; }
	 $src_connect_id = @sqlsrv_connect($src_def['Host'], $connection_info);
	 if ($src_connect_id===false) {
		return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Fails to connect to ms sql source ".print_r(@sqlsrv_errors(),true));
		}
	 @sqlsrv_configure("WarningsReturnAsErrors",0);
	 @sqlsrv_configure("ReturnDatesAsStrings",true);
	return $src_connect_id;
}

public function _do_call_QUERY_TABLE_QUERY($p, $vm) {
	$src_connect_id = $p[0]['connect_id'];
   $this->SQL_QUERY_ERROR = "";
   // Extract from param block;
   $src_query = (array_key_exists('src_query', $p[0]))?$p[0]['src_query']:null;
   if (is_null($src_query)) return $this->_RunTime_Exception("In Query_Table expects parameter to have property \"src_query\" ");
   $verbose = (array_key_exists('verbose', $p[0]))?$p[0]['verbose']:false;

	 @sqlsrv_configure("WarningsReturnAsErrors",0);
	 @sqlsrv_configure("ReturnDatesAsStrings",true);
	 $src_query_result = @sqlsrv_query($src_connect_id, $src_query, null, array('Scrollable'=>SQLSRV_CURSOR_FORWARD,'QueryTimeout' => 60000));
	 if ($src_query_result===false) {
		return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "MSSQL Query Fail: {$src_query} Error: ".print_r(@sqlsrv_errors(),true));
		}
	return $src_query_result;
}
public function _do_call_QUERY_TABLE_RELEASE($p, $vm) {
	if (is_resource($p[0])) @sqlsrv_free_stmt($p[0]);
	return true;
}

public function _do_call_QUERY_RECORD_FETCH($p, $vm) {
	$src_query_result = $p[0]['query_result'];
	$a = sqlsrv_fetch_array( $src_query_result, SQLSRV_FETCH_ASSOC);
	if ($a===false) {
	   return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Fails to fetch record  ".print_r(@sqlsrv_errors(),true));
	   }
	return $a;
}

public function _do_call_QUERY_TABLE_FETCH($p, $vm) {
	$src_connect_id = $p[0]['connect_id'];
	$src_query_result = $p[0]['query_result'];
	$a = @sqlsrv_fetch_array($src_query_result, SQLSRV_FETCH_ASSOC);
	if ($a===false) {
	   return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Fails to fetch record  ".print_r(@sqlsrv_errors(),true));
	   }
	return $a;
}
public function _do_call_QUERY_TABLE_CLOSE($p, $vm) {
	if (array_key_exists('query_result', $p[0])) @sqlsrv_free_stmt($p[0]['query_result']);
	@sqlsrv_close($p[0]['connect_id']); 
	return true;
   }



public function _do_call_QUERY_TABLE($p, $vm) {
   /** QUERY_TABLE(param_block $p); Execute a query $p.src_query on $p.src_connect with data as $p.no_data_query, $; Return false or result structure;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for QUERY_TABLE(param_block)");
   if (!is_array($p[0])) return $this->_RunTime_Exception("Expects parameter to QUERY_TABLE(param_block) to be a variable with properties");
   $this->SQL_QUERY_ERROR = "";
   // Extract from param block;
   $src_connect = (array_key_exists('src_conn', $p[0]))?$p[0]['src_conn']:null;
   if (is_null($src_connect)) return $this->_RunTime_Exception("In Query_Table expects parameter to have property \"src_conn\" ");
   $src_query = (array_key_exists('src_query', $p[0]))?$p[0]['src_query']:null;
   if (is_null($src_query)) return $this->_RunTime_Exception("In Query_Table expects parameter to have property \"src_query\" ");
   $verbose = (array_key_exists('verbose', $p[0]))?$p[0]['verbose']:false;

   // LOOKUP Dictionaries
   $src = hda_db::hdadb()->HDA_DB_dictionary(NULL, $src_connect);
   if (is_null($src) || !is_array($src) || count($src)<>1) {
      return $this->_RunTime_Exception("QUERY_TABLE using source lookup {$src_connect}, dictionary entry not found");
      return false;
      }
   $src_def = $src[0]['Definition'];

   $do_fetch = false;
   $no_data_query = (array_key_exists('no_data_query',$p[0]))?$p[0]['no_data_query']:false;
   // CONNECT TO SOURCE
   switch ($src_def["Connect Type"]) {
      case "ORCL": 
         $src_connect_id = @oci_connect($src_def['User'],$src_def['PassW'],$src_def['Host']);
         if ($src_connect_id===false) {
            $err = @oci_error();
            return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Fails to connect to Oracle {$err['message']}");
            }
         $src_query_result = @oci_parse($src_connect_id, $src_query);
         if ($src_query_result===false) {
            $err = @oci_error($src_connect_id);
            return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Oracle Query Parse Fail: {$err['message']}");
            }
         $ok = @oci_execute($src_query_result);
         if ($ok===false) {
            $err = @oci_error($src_query_result);
            return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Oracle Query Execute Fail: {$err['message']}");
            }
         break;
      case "MSSQL":
         $src_connect_id = null;
         $connection_info = array();
         $connection_info['ReturnDatesAsStrings'] = true;
		 $connection_info['TransactionIsolation'] = SQLSRV_TXN_READ_UNCOMMITTED;
         if (array_key_exists('Schema',$src_def) && !is_null($src_def['Schema']) && strlen($src_def['Schema'])>0) 
            $connection_info['Database'] = $src_def['Schema'];
         if (strlen($src_def['User'])>0) { $connection_info['UID']=$src_def['User']; $connection_info['PWD']=$src_def['PassW']; }
         $src_connect_id = @sqlsrv_connect($src_def['Host'], $connection_info);
         if ($src_connect_id===false) {
            return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Fails to connect to ms sql source ".print_r(@sqlsrv_errors(),true));
            }
         @sqlsrv_configure("WarningsReturnAsErrors",0);
         @sqlsrv_configure("ReturnDatesAsStrings",true);
         $src_query_result = @sqlsrv_query($src_connect_id, $src_query, null, array('Scrollable'=>SQLSRV_CURSOR_STATIC,'QueryTimeout' => 60000));
         if ($src_query_result===false) {
            return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "MSSQL Query Fail: {$src_query} Error: ".print_r(@sqlsrv_errors(),true));
            }
         $do_fetch = sqlsrv_num_rows($src_query_result);

         if ($do_fetch !== false && $do_fetch>0) $do_fetch = true;
         break;
      case "ODBC":
         $src_connect_id = false;
         $src_connect_id = @odbc_connect($src_def['DSN'], $src_def['User'], $src_def['PassW']);
         if ($src_connect_id===false) {
            return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Fails to connect to ODBC source ".odbc_errormsg());
            }
         $src_query_result = @odbc_exec($src_connect_id, $src_query);
         if ($src_query_result===false) {
            $this->SQL_QUERY_ERROR = @odbc_errormsg($this->ODBC_QUERY_CONN);
            return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "ODBC Query Fail: {$this->SQL_QUERY_ERROR}");
            }
         break;
      default: 
         return $this->_RunTime_Exception("QUERY_TABLE using source lookup {$src_connect}, dictionary entry not a Db Type");
         return false;
      }


   $fetching = $do_fetch && !$no_data_query;
   $records = array();
   $record_count = 0;
   while ($fetching) {
      $a = null;
      $record_count++;
      switch ($src_def["Connect Type"]) {
         case "ORCL": 
            $a = @oci_fetch_array($src_query_result, OCI_ASSOC);
            if ($a===false) {
               $err = @oci_error($dst_connect_id);
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Oracle fails fetch record {$record_count} : {$err['message']}");
               }
            if (is_null($a)) $fetching = false;
            break;
         case "MSSQL":
            $a = @sqlsrv_fetch_array($src_query_result, SQLSRV_FETCH_ASSOC);
            if ($a===false) {
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Fails to fetch record {$record_count} ".print_r(@sqlsrv_errors(),true));
               }
            if (is_null($a)) $fetching = false;
            break;
         case "ODBC":
            $a = odbc_fetch_array($src_query_result);
            if ($a===false) {
               return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "ODBC fail to fetch record {$record_count} ".odbc_errormsg());
               }
            if (is_null($a)) $fetching = false;
            break;
         }
      if (!is_null($a)) $records[] = $a;
      }

   switch ($src_def["Connect Type"]) {
      case "ORCL": @oci_close($src_connect_id);break;
      case "MSSQL": @sqlsrv_free_stmt($src_query_result);@sqlsrv_close($src_connect_id); break;
      case "ODBC": @odbc_close($src_connect_id); break;
      }
   $record_count = count($records);
   if ($verbose) $this->parent->_trace("Completed query fetched {$record_count} records");
   return $records;
   }
// ends query_table

private $SQL_QUERY_ERROR = null;
public function _do_call_SQL_LAST_ERROR($p, $vm) {
   /** SQL_LAST_ERROR(); Return last sql error; Return error string;  **/
   return $this->SQL_QUERY_ERROR;
   }

//** CATEGORY STRING

public function _do_call_STRING_LINES($p, $vm) {
   /** STRING_LINES(string); Return an array of lines from string; Return false or line structure;  **/
   if (count($p) < 1) return $this->_RunTime_Exception("Wrong parameter count for STRING_LINES(string, trim)");
   try {
      $s = @str_replace(array("\f","\r"),"\n",$p[0]);
	  $lines = explode("\n",$s);
	  $a = array();
	  $do_trim = (count($p)==2)?$p[1]:true;
	  foreach ($lines as $line) {
		if ($do_trim) $line = trim($line); 
		if (strlen($line)>0) $a[] = $line;
		}
	  return $a;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in STRING_LINES: {$e}");
      }
   return null;
   }

public function _do_call_STRING_REPLACE($p, $vm) {
   /** STRING_REPLACE(pattern, replace, string); Return a string after replace pattern; Return false or new string;  **/
   if (count($p) <> 3) return $this->_RunTime_Exception("Wrong parameter count for STRING_REPLACE(stringPattern, stringReplacement, target)");
   try {
      return @str_replace($p[0], $p[1], $p[2]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in STRING_REPLACE: {$e}");
      }
   return null;
   }

public function _do_call_STRING_UPPER($p, $vm) {
   /** STRING_UPPER(string); Convert a string to upper case; Return false or new string;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for STRING_UPPER(string)");
   try {
      return @strtoupper($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in STRING_UPPER: {$e}");
      }
   return false;
   }

public function _do_call_STRING_LOWER($p, $vm) {
   /** STRING_LOWER(string); Convert a string to lower case; Return false or new string;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for STRING_LOWER(string)");
   try {
      return @strtolower($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in STRING_LOWER: {$e}");
      }
   return false;
   }

public function _do_call_STRING_UCFIRST($p, $vm) {
   /** STRING_UCFIRST(string); Convert first char to upper; Return false or new string;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for STRING_UCFIRST(string)");
   try {
      return @ucfirst($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in STRING_UCFIRST: {$e}");
      }
   return false;
   }

public function _do_call_STRING_LENGTH($p, $vm) {
   /** STRING_LENGTH(string); length of string; Return false or length;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for STRING_LENGTH(string)");
   try {
      return @strlen($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in STRING_LENGTH: {$e}");
      }
   return false;
   }

public function _do_call_SUB_STRING($p, $vm) {
   /** SUB_STRING(string, index, length); Extract sub string from index for length in string; Return false or new string;  **/
   if (count($p) < 2) return $this->_RunTime_Exception("Wrong parameter count for SUB_STRING(string, index, length)");
   try {
      return (count($p)==3)?@substr($p[0],$p[1],$p[2]):@substr($p[0],$p[1]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SUB_STRING: {$e}");
      }
   return false;
   }
public function _do_call_MB_SUB_STRING($p, $vm) {
   /** SUB_STRING(string, index, length); Extract sub string from index for length in string; Return false or new string;  **/
   if (count($p) < 2) return $this->_RunTime_Exception("Wrong parameter count for MB_SUB_STRING(string, index, length)");
   try {
      return (count($p)==3)?@mb_strimwidth($p[0],$p[1],$p[2]):@substr($p[0],$p[1]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in MB_SUB_STRING: {$e}");
      }
   return false;
   }

public function _do_call_STRING_CHAR($p, $vm) {
   /** STRING_CHAR(string, index); Get character at index in string; Return false or character;  **/
   if (count($p) <> 2) return $this->_RunTime_Exception("Wrong parameter count for STRING_CHAR(targetString, charIndex)");
   try {
      return ($p[1]<@strlen($p[0]) && $p[1]>=0)?$p[0][$p[1]]:"";
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in STRING_CHAR: {$e}");
      }
   return false;
   }
public function _do_call_STRING_ARRAY($p, $vm) {
   /** STRING_ARRAY(string [,chunk]); Make a string an array of chunk (default single) characters; Return false or array struccture of characters;  **/
   if (count($p) < 1 || count($p)>2) return $this->_RunTime_Exception("Wrong parameter count for STRING_ARRAY(targetString [,chunk length])");
   try {
      return (count($p)==2)?str_split($p[0],$p[1]):str_split($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in STRING_ARRAY: {$e}");
      }
   return false;
   }

public function _do_call_STRING_SPLIT($p, $vm) {
   /** STRING_SPLIT(split_on, string); Split a string on pattern; Return false or array structure of parts;  **/
   if (count($p) <> 2) return $this->_RunTime_Exception("Wrong parameter count for STRING_SPLIT(splitChar,target)");
   try {
      return ($p[0][0]=='/')?@preg_split($p[0], $p[1]):@explode($p[0], $p[1]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in STRING_SPLIT: {$e}");
      }
   return false;
   }

public function _do_call_TRIM($p, $vm) {
   /** TRIM(string[, charlist]); Trim whitespace front and back from string, or trim chars as given; Return false or new string;  **/
   if (count($p) < 1) return $this->_RunTime_Exception("Wrong parameter count for TRIM(string [,string charlist])");
   try {
      return (count($p)==2)?trim($p[0],$p[1]):trim($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in TRIM: {$e}");
      }
   return "";
   }
   
public function _do_call_SPRINTF($p, $vm) {
   /** SPRINTF(format, value[, values]); As C vsprinf; Return false or string;  **/
   if (count($p) < 2) return $this->_RunTime_Exception("Wrong parameter count for SPRINTF(format, value[, values])");
   try {
      for ($i=1; $i<count($p); $i++) $pp[] = $p[$i];
      return vsprintf($p[0],$pp);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SPRINTF: {$e}");
      }
   return false;
   }


public function _do_call_URLENCODE($p, $vm) {
   /** URLENCODE(string); Encode a string for transit; Return false or new string;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for URLENCODE(string)");
   try {
      return hda_db::hdadb()->HDA_DB_textToDB($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in URLENCODE: {$e}");
      }
   return $p[0];
   }

public function _do_call_URLDECODE($p, $vm) {
   /** URLDECODE(string); Decode an urlencoded string; Return false or new string;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for URLDECODE(string)");
   try {
      return hda_db::hdadb()->HDA_DB_textFromDB($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in URLDECODE: {$e}");
      }
   return $p[0];
   }
public function _do_call_TODB($p, $vm) {
   /** TODB(array);   **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for TODB(array)");
   try {
	   $s = serialize($p[0]);
      return hda_db::hdadb()->HDA_DB_textToDB($s);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in TODB: {$e}");
      }
   return $p[0];
   }
public function _do_call_FROMDB($p, $vm) {
   /** TODB(array);   **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for FROMDB(string)");
   try {
      $s = hda_db::hdadb()->HDA_DB_textFromDB($p[0]);
	  return unserialize($s);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in FROMDB: {$e}");
      }
   return $p[0];
   }
	
public function _do_call_MD5_RECORD($p, $vm) {
   /** MD5_RECORD(array); Serialize record, get MD5 hash from resulting string; Return false or hash string;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for MD5_RECORD(record)");
   try {
	  if (!is_array($p[0])) return $this->_RunTime_Exception("Record must be array structure for MD5_RECORD(record)");
      $s = hda_db::hdadb()->HDA_DB_serialize($p[0]);
	  return md5($s);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in MD5_RECORD: {$e}");
      }
   return false;
	}
public function _do_call_HEX_STRING($p, $vm) {
   /** HEX_STRING(string, encode); Encode Decode string to hex; Return false or string;  **/
   if (count($p) <> 2) return $this->_RunTime_Exception("Wrong parameter count for HEX_STRING(string, encode)");
   try {
      $s = ($p[1]===true)?BIN2HEX($p[0]):$this->_hex_2_bin($p[0]);
	  return $s;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in HEX_STRING: {$e}");
      }
   return false;
	}
private function _hex_2_bin($h) {
  if (!is_string($h)) return null;
  $r='';
  for ($a=0; $a<strlen($h); $a+=2) { $r.=chr(hexdec($h{$a}.$h{($a+1)})); }
  return $r;
  }
public function _do_call_BASE64_STRING($p, $vm) {
   /** BASE64_STRING(string, encode); Encode Decode string to base64; Return false or string;  **/
   if (count($p) <> 2) return $this->_RunTime_Exception("Wrong parameter count for BASE64_STRING(string, encode)");
   try {
      $s = ($p[1]===true)?BASE64_ENCODE($p[0]):BASE64_DECODE($p[0]);
	  return $s;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in BASE64_STRING: {$e}");
      }
   return false;
	}
public function _do_call_EBCDIC_TO_ASCII($p, $vm) {
   /** EBCDIC_TO_ASCII($ebcdic_binary); convert ebcdic binary to ascii text; Return false or text;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for EBCDIC_TO_ASCII(ebcdic_binary)");
   try {
		$a = PRO_ebcdic_to_ascii($p[0]);
		return $a;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in EBCDIC_TO_ASCII: {$e}");
      }
   return false;
}
public function _do_call_Make_ASCII($p, $vm) {
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for make_ASCII(string)");
	try {
		ini_set('mbstring.substitute_character', 'none');
		$s = preg_replace('/[^[:print:]\n]/u', '', mb_convert_encoding($p[0], 'UTF-8', 'UTF-8'));
		return $s;
	}
	catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in make_ascii: {$e}");
	}
	return false;
}
public function _do_call_GEN_KEY($p, $vm) {
   /** GEN_KEY(); generate a key for encrypt; Return false or key;  **/
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for GEN_KEY()");
   try {
	   $key = Crypto::CreateNewRandomKey();
	   return $key;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in GEN_KEY: {$e}");
      }
   return false;
	}
public function _do_call_ENCRYPT($p, $vm) {
   /** ENCRYPT(string, key); encrypt string; Return false or encrypted string;  **/
   if (count($p) <> 2) return $this->_RunTime_Exception("Wrong parameter count for ENCRYPT(string, key)");
   try {
	   $ciphertext = Crypto::Encrypt($p[0], $p[1]);
	   return $ciphertext;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in ENCRYPT: {$e}");
      }
   return false;
	}
public function _do_call_DECRYPT($p, $vm) {
   /** DECRYPT(encrypt_string, key); decrypt string; Return false or decrypted string;  **/
   if (count($p) <> 2) return $this->_RunTime_Exception("Wrong parameter count for DECRYPT(encrypted_string, key)");
   try {
	   $plaintext = Crypto::Decrypt($p[0], $p[1]);
	   return $plaintext;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in DECRYPT: {$e}");
      }
   return false;
	}
public function _do_call_GPG_ENCRYPT($p, $vm) {
   /** GPG_ENCRYPT(string, pub_key); encrypt string using public key; Return false or string;  **/
   if (count($p) <>2) return $this->_RunTime_Exception("Wrong parameter count for GPG_ENCRYPT(string, key)");
   try {
		$gpg = new GPG();
		$pub_key = new GPG_Public_Key($p[1]);
		$k = "";
		$k .= "{$pub_key->version}\n";
		$k .= "{$pub_key->fp}\n";
		$k .= "{$pub_key->key_id}\n";
		$k .= "{$pub_key->user}\n";
		$k .= "{$pub_key->public_key}\n";
		$k .= "{$pub_key->type}\n";
		file_put_contents("pgp_key_details.txt",$k);
		$encrypted = $gpg->encrypt($pub_key,$p[0]);
	   return $encrypted;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in GPG_ENCRYPT: {$e}");
      }
   return false;
	}
public function _do_call_IS_STRING($p, $vm) {
   /** IS_STRING(var); Test if var is a string; Return false/true;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for IS_STRING(entity)");
   try {
      return is_string($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in IS_STRING: {$e}");
      }
   return false;
   }
public function _do_call_IS_NUMERIC($p, $vm) {
   /** IS_NUMERIC(var); Test if var can be considered numeric; Return false/true;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for IS_NUMERIC(entity)");
   try {
      return is_numeric($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in IS_NUMERIC: {$e}");
      }
   return false;
   }
public function _do_call_IS_INTEGER($p, $vm) {
   /** IS_INTEGER(var); Test if var can be considered integer (also tests first for numeric); Return false/true;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for IS_INTEGER(entity)");
   try {
      return (is_numeric($p[0]) && filter_var($p[0], FILTER_VALIDATE_INT)); 
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in IS_INTEGER: {$e}");
      }
   return false;
   }
public function _do_call_IS_FLOAT($p, $vm) {
   /** IS_FLOAT(var); Test if var can be considered a float (also tests first for numeric); Return false/true;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for IS_FLOAT(entity)");
   try {
      return (is_numeric($p[0]) && filter_var($p[0], FILTER_VALIDATE_FLOAT));
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in IS_FLOAT: {$e}");
      }
   return false;
   }

public function _do_call_IS_NULL($p, $vm) {
   /** IS_NULL(var); Test if var can be considered as null; Return false/true;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for IS_NULL(entity)");
   try {
      return is_null($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in IS_NULL: {$e}");
      }
   return false;
   }
public function _do_call_IS_EMPTY($p, $vm) {
   /** IS_NULL(var); Test if var can be considered as smpty - null or string len zero; Return false/true;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for IS_EMPTY(entity)");
   try {
      return (is_null($p[0]) or (strlen(trim($p[0]))==0));
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in IS_EMPTY: {$e}");
      }
   return false;
   }
public function _do_call_IS_ARRAY($p, $vm) {
   /** IS_ARRAY(var); Test if var can be considered as an array structure; Return false/true;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for IS_ARRAY(entity)");
   try {
      return is_array($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in IS_ARRAY: {$e}");
      }
   return false;
   }

public function _do_call_STRTOK($p, $vm) {
   /** STRTOK(token, token_delimiter); Split a string to tokens; Return false or structure of token list;  **/
   if (count($p) <> 2) return $this->_RunTime_Exception("Wrong parameter count for STRTOK(string, token_del)");
   try {
      $a = array();
      $tok = @strtok($p[0], $p[1]);
      while ($tok!==false) {
         $a[] = trim($tok," \n");
         $tok = strtok($p[1]);
         }
      strtok('',','); // release memory in strtok
      return $a;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in STRTOK: {$e}");
      }
   return false;
   }
public function _do_call_STRING_GETCSV($p, $vm) {
   /** STRING_GETCSV(string [,delimiter[, enclsure[, escape]]]); Get csv structure from string; Return false or structure;  **/
   if (count($p) < 1) return $this->_RunTime_Exception("Wrong parameter count for STRING_GETCSV(string[, delimiter[,enclosure[,escape]]])");
   try {
      $delimiter = (count($p)>1 && strlen($p[1])>0)?$p[1][0]:',';
      $enclosure = (count($p)>2 && strlen($p[2])>0)?$p[2][0]:'"';
      $escape = (count($p)==4 && strlen($p[3])>0)?$p[3][0]:chr(0);
      return @str_getcsv($p[0],$delimiter,$enclosure,$escape);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in STRING_GETCSV: {$e}");
      }
   return false;
   }

public function _do_call_PREG_MATCH($p, $vm) {
   /** PREG_MATCH(pattern, string); Execute preg_match; Return false/true;  **/
   if (count($p) <> 2) return $this->_RunTime_Exception("Wrong parameter count for PREG_MATCH(pattern, target)");
   try {
      if (strlen($p[0])>0 && $p[0][0]<>"/") $p[0] = "/{$p[0]}/";
      $result = @preg_match($p[0],$p[1],$matches);
      $error = $vm->_last_vm_error;
      if ($result===false) return $this->_RunTime_Exception("Preg_match error {$error} with pattern {$p[0]} and target {$p[1]}");
      if ($result==0) return false;
      return $matches;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in PREG_MATCH: {$e}");
      }
   return false;
   }
public function _do_call_PREG_MATCH_ALL($p, $vm) {
   /** PREG_MATCH_ALL(pattern, string); Execute preg_match_all; Return false or matched structure;  **/
   if (count($p) <> 2) return $this->_RunTime_Exception("Wrong parameter count for PREG_MATCH_ALL(pattern, target)");
   try {
      if (strlen($p[0])>0 && $p[0][0]<>"/") $p[0] = "/{$p[0]}/";
      $result = @preg_match_all($p[0],$p[1],$matches);
      $error = $vm->_last_vm_error;
      if ($result===false) return $this->_RunTime_Exception("Preg_match_all error {$error} with pattern {$p[0]} and target {$p[1]}");
       if ($result==0) return false;
     return $matches;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in PREG_MATCH_ALL: {$e}");
      }
   return false;
   }

public function _do_call_PREG_REPLACE($p, $vm) {
   /** PREG_REPLACE(pattern, replace, string); Replace in string on preg match; Return false or new string;  **/
   if (count($p) <> 3) return $this->_RunTime_Exception("Wrong parameter count for PREG_REPLACE(pattern, replace, target)");
   try {
      if (strlen($p[0])>0 && $p[0][0]<>"/") $p[0] = "/{$p[0]}/";
      $result = @preg_replace($p[0],$p[1],$p[2]);
      if (is_null($result)) return false;
      return $result;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in PREG_REPLACE: {$e}");
      }
   return false;
   }
public function _do_call_PREG_SPLIT($p, $vm) {
   /** PREG_SPLIT(pattern, string); Slit a string using regex; Return false or split string structure;  **/
   if (count($p) < 2) return $this->_RunTime_Exception("Wrong parameter count for PREG_SPLIT(pattern, target[,skip0])");
   try {
      if (strlen($p[0])>0 && $p[0][0]<>"/") $p[0] = "/{$p[0]}/";
      $a = @preg_split($p[0],$p[1]);
      if (count($p)==2 || (count($p)==3 && $p[2]===true)) @array_splice($a,0,1);
      return $a;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in PREG_SPLIT: {$e}");
      }
   return false;
   }
   
public function _do_call_HTML_STRIP_TAGS($p, $vm) {
   /** HTML_STRIP_TAGS(html_str); Remove html tags; Return false or string;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for HTML_STRIP_TAGS(str)");
   try {
	   return html_entity_decode(htmlspecialchars_decode(strip_tags($p[0])));
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in HTML_STRIP_TAGS: {$e}");
      }
   return false;
}
public function _do_call_HTML_SPECIAL_CHARS($p, $vm) {
   /** HTML_SPECIAL_CHARS(html_str); Remove html tags; Return false or string;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for HTML_SPECIAL_CHARS(str)");
   try {
	   return htmlspecialchars($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in HTML_SPECIAL_CHARS: {$e}");
      }
   return false;
}
public function _do_call_HTML_SPECIAL_CHARS_DECODE($p, $vm) {
   /** HTML_SPECIAL_CHARS_DECODE(html_str); Remove html tags; Return false or string;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for HTML_SPECIAL_CHARS_DECODE(str)");
   try {
	   return htmlspecialchars_decode($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in HTML_SPECIAL_CHARS_DECODE: {$e}");
      }
   return false;
}
public function _do_call_ARRAY_TO_CSV($p, $vm) {
   /** ARRAY_TO_CSV(array[, keys_true_false]); Convert an array to csv, prepend field name option; Return false or csv string;  **/
   if (count($p) < 1) return $this->_RunTime_Exception("Wrong parameter count for ARRAY_TO_CSV(array[,keys])");
   try {
      if (!is_array($p[0]) || count($p[0])==0) return "";
      if (count($p)==2 && $p[1]===true) {
         $s = "";
         $keys = array_keys($p[0]);
         for ($i=0; $i<count($keys); $i++) {
            $s .= "[{$keys[$i]}] {$p[0][$keys[$i]]},";
            }
         $s = trim($s, ",");
         return $s;
         }
	  elseif (count($p)==2 && !is_null($p[1])) {
	     $s = "";
		 foreach ($p[0] as $pp) $s .= "{$p[1]}{$pp}{$p[1]},";
		 $s = trim($s, ',');
		 return $s;
	     }
      else return @implode(",", $p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in ARRAY_TO_CSV: {$e}");
      }
   return false;
   }

public function _do_call_SERIAL_STRING($p, $vm) {
   /** SERIAL_STRING(array); Convert an array to string; Return false or string;  **/
   if (count($p) < 1) return $this->_RunTime_Exception("Wrong parameter count for SERIAL_STRING(array)");
   try {
      if (!is_array($p[0]) || count($p[0])==0) return "";
	  return hda_db::hdadb()->HDA_DB_serialize($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SERIAL_STRING: {$e}");
      }
   return false;
   }
   
public function _do_call_UNSERIAL_STRING($p, $vm) {
   /** UNSERIAL_STRING(serial_string); Convert a serialized string back to array; Return false or array structure;  **/
   if (count($p) < 1) return $this->_RunTime_Exception("Wrong parameter count for UNSERIAL_STRING(string)");
   try {
      if (strlen($p[0])==0) return array();
	  return hda_db::hdadb()->HDA_DB_unserialize($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in UNSERIAL_STRING: {$e}");
      }
   return false;
   }


public function _do_call_ARRAY_KEYS($p, $vm) {
   /** ARRAY_KEYS(array); Get the properties of a structure; Return false or list structure of properties;  **/
   if (count($p) < 1) return $this->_RunTime_Exception("Wrong parameter count for ARRAY_KEYS(array[, search_value])");
   try {
      return (count($p)==1)?@array_keys($p[0]):@array_keys($p[0], $p[1]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in ARRAY_KEYS: {$e}");
      }
   return false;
   }
public function _do_call_IN_ARRAY($p, $vm) {
   /** IN_ARRAY(value, array)  **/
   if (count($p) < 2) return $this->_RunTime_Exception("Wrong parameter count for IN_ARRAY(search_value,array)");
   try {
      return (is_array($p[1]))?@in_array($p[0],$p[1]):false;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in IN_ARRAY: {$e}");
      }
   return false;
   }
public function _do_call_LIKE_IN_ARRAY($p, $vm) {
   /** LIKE_IN_ARRAY(value, array)  **/
   if (count($p) < 2) return $this->_RunTime_Exception("Wrong parameter count for LIKE_IN_ARRAY(search_value,array)");
   try {
	   $value = trim($p[0]);
      if (is_array($p[1])) {
		  foreach ($p[1] as $v) {
			  if (strcasecmp($value, trim($v))==0) return true;
		  }
		  return false;
	  }
	  else return false;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in LIKE_IN_ARRAY: {$e}");
      }
   return false;
}
public function _do_call_COUNT_PROPERTIES($p, $vm) {
   /** COUNT_PROPERTIES(array); Get count of the properties of a structure; Return false or count;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for COUNT_PROPERTIES(array)");
   try {
      return (is_array($p[0]))?count($p[0]):0;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in COUNT_PROPERTIES: {$e}");
      }
   return false;
   }
public function _do_call_ARRAY_TO_RECORDS($p, $vm) {
   /** ARRAY_TO_RERCORDS(array, field map]); Take structure and produce records array; Return false or record array;  **/
   if (count($p) < 2) return $this->_RunTime_Exception("Wrong parameter count for ARRAY_TO_RECORDS(array, field map)");
   try {
      if (!is_array($p[0]))return $this->_RunTime_Exception("Fails in ARRAY_TO_RECRODS: not array"); 
	  $a = array();
	  $field_map = array();
	  foreach($p[1] as $db_field=>$map_field) $field_map[$map_field] = $db_field;
	  foreach($p[0] as $row) {
		  $aa = array();
		  foreach($row as $field=>$v) if (array_key_exists($field, $field_map)) $aa[$field_map[$field]] = $v;
		  $a[] = $aa;
	  }
	  return $a;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in ARRAY_TO_RECORDS: {$e}");
      }
   return false;
   }

// END STRINGS

//** CATEGORY SYSTEM

private $LIBRARY_CHECKSUM = 0;
public function _do_call_OPEN_CHECKSUM($p, $vm) {
   /** OPEN_CHECKSUM([string]);Open a checksum session, with an optional initial string; Return false/true;  **/
   if (count($p) > 1) return $this->_RunTime_Exception("Wrong parameter count for OPEN_CHECKSUM([string])");
   try {
      $this->LIBRARY_CHECKSUM = 0;
      if (count($p)==1) $this->LIBRARY_CHECKSUM += crc32($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in OPEN_CHECKSUM: {$e}");
      }
   return true;
   }

public function _do_call_APPEND_CHECKSUM($p, $vm) {
    /** APPEND_CHECKSUM(string); Append a string into an open checksum session; Return false/true;  **/
  if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for APPEND_CHECKSUM(string)");
   try {
      $this->LIBRARY_CHECKSUM += crc32($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in APPEND_CHECKSUM: {$e}");
      }
   return true;
   }


public function _do_call_CLOSE_CHECKSUM($p, $vm) {
   /** CLOSE_CHECKSUM();Close a checksum session, return checksum value; Return false or crc value;  **/
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for CLOSE_CHECKSUM()");
   try {
      return $this->LIBRARY_CHECKSUM;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in CLOSE_CHECKSUM: {$e}");
      }
   return false;
   }

public function _do_call_WRITE_CHECKSUM($p, $vm) {
   /** WRITE_CHECKSUM(with_key); Register a checksum value with key; Return false/true;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for WRITE_CHECKSUM(process_id)");
   try {
      return hda_db::hdadb()->HDA_DB_WriteChecksum($vm->ref, $p[0], $this->LIBRARY_CHECKSUM);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in WRITE_CHECKSUM: {$e}");
      }
   return false;
   }

public function _do_call_READ_CHECKSUM($p, $vm) {
   /** READ_CHECKSUM(with_key); Get a registered checksum value from key; Return false or checksum value;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for READ_CHECKSUM(process_id)");
   try {
      $chk = hda_db::hdadb()->HDA_DB_ReadChecksum($vm->ref, $p[0]);
      if ($chk===false) throw new HDA_RunTime_Exception("Fails to find checksum for {$vm->ref} process \"{$p[0]}\" ");
      return $chk;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in READ_CHECKSUM: {$e}");
      }
   return false;
   }



//** CATEGORY RSS, SOAP

// XML

public function _do_call_XML_STREAMER($p, $vm) {
   /** XML_STREAMER(path, openNode, onlyNode, onlyValue); Convert an XML file into records; Return false or true;  **/
   if (count($p) <> 4) return $this->_RunTime_Exception("Wrong parameter count for XML_STREAMER(path, openTag, onlyTag, onlyValue)");
   try {
      $this->_XML_STREAM_READER = new xmlStreamer();
	  $this->_XML_STREAM_READER->recordTag = $p[1];
	  $this->_XML_STREAM_READER->only = $p[2];
	  $this->_XML_STREAM_READER->only_value = $p[3];
		$this->_resolve_working_dir($vm);
        if (stripos($p[0], "\\") === false && stripos($p[0],"/")===false) $p[0] = "{$this->WORKING_DIR}/{$p[0]}";
	  return $this->_XML_STREAM_READER->open($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in XML_STREAMER: {$e}");
      }
   return false;
   }
public function _do_call_XML_STREAM_RECORD($p, $vm) {
   /** XML_STREAM_RECORD(); Next xml record or false;  **/
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for XML_STREAM_RECORD()");
   try {
	   return $this->_XML_STREAM_READER->xmlRecord();
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in XML_STREAM_RECORD: {$e}");
      }
   return false;
}
public function _do_call_XML_STREAM_CLOSE($p, $vm) {
	if (!is_null($this->_XML_STREAM_READER)) $this->_XML_STREAM_READER->close();
	return true;
}
private $_XML_STREAM_READER = null;


public function _do_call_XML_TO_ARRAY($p, $vm) {
   /** XML_TO_ARRAY(string); Convert an XML atring into an array structure; Return false or array structure;  **/
   if (count($p) < 1) return $this->_RunTime_Exception("Wrong parameter count for XML_TO_ARRAY(string)");
   try {
      $domObj = new xmlToArrayParser($p[0]);
      return $domObj->array;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in XML_TO_ARRAY: {$e}");
      }
   return false;
   }

public function _do_call_GET_XML_RECORD($p, $vm) {
   /** GET_XML_RECORD(xml_structure, tag, index); Get the record from an xml structure (XML_TO_ARRAY) with tag and index; Return false or record structure;  **/
   if (count($p) <> 3) return $this->_RunTime_Exception("Wrong parameter count for GET_XML_RECORD(record_array, record_tag, count_index)");
   try {
      return $this->_get_xml_record($p[0],$p[1],$p[2]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in GET_XML_RECORD: {$e}");
      }
   return false;
   }
private function _get_xml_record($a, $tag, $idx) {
   if (!is_array($a)) return false;
   foreach ($a as $k=>$p) {
      if ($k=='cdata') continue;
      if (strtoupper($k)==strtoupper($tag)) {
         if (is_array($p) && array_key_exists(0, $p) && ($idx < count($p))) return $p[$idx];
         elseif (is_array($p) && ($idx==0)) return $p;
         else return false;
         }
      else {
         $aa = $this->_get_xml_record($p, $tag, $idx);
         if (is_array($aa)) return $aa;
         }
      }
   return false;
   }
function _do_call_XML_SEEK($p, $vm) {
   /** XML_SEEK(string, open_node, filter_node, value); Extract from an XML string; Return false or record structure;  **/
   if (count($p) <> 4) return $this->_RunTime_Exception("Wrong parameter count for XML_SEEK(string, open_node, filter_node, value)");
   try {
    if (preg_match_all("/<{$p[1]}[\s\S]{0,}?>(?P<el>[\s\S]{1,}?)<\/{$p[1]}>/i",$p[0], $matches) && is_array($matches) && array_key_exists('el',$matches)) {
		 $p[0] = null;
		  gc_enable(); gc_collect_cycles() ; $mem = memory_get_peak_usage(); $this->_do_call_ISSUE_MONITOR(array("Extracting xml mem {$mem}"),$vm);
		$aa = array();
	     foreach ($matches['el'] as $node) {
			if (preg_match("/<{$p[2]}>{$p[3]}<\/{$p[2]}>/i", $node)) {
				if (preg_match_all("/<(?P<el>[^>]{1,})>(?P<elv>[\s\S]{0,}?)<\/[^>]{1,}>/i",$node, $nodes) && is_array($nodes)) {
					$qq = array(); for($i=0; $i<count($nodes['el']);$i++) {
					$qq[trim($nodes['el'][$i])] = trim($nodes['elv'][$i]);
					}
					$aa[$p[1]][] = $qq;
					$this->_do_call_ISSUE_MONITOR(array("Seek ".count($aa[$p[1]])),$vm);
				  }
				}
			}
		 return $aa;
		 }
	else $this->_RunTime_Exception("Unable to find node {$p[1]} in xml");
  
	}
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in XML_SEEK: {$e}");
      }
   return false;
   }
function _do_call_XML_EXTRACT($p, $vm) {
   /** XML_EXTRACT(string, tag[, match_nodes); Extract from an XML string; Return false or record structure;  **/
   if (count($p) < 2) return $this->_RunTime_Exception("Wrong parameter count for XML_EXTRACT(string, tag [,match_nodes])");
   try {
      $only = (count($p)==3)?$p[2]:null;
      $aa = array();
      if (preg_match_all("/<{$p[1]}[\s\S]{0,}?>(?P<el>[\s\S]{1,}?)<\/{$p[1]}>/i",$p[0], $matches) && is_array($matches) && array_key_exists('el',$matches)) {
		 $p[0] = null;
		  gc_enable(); gc_collect_cycles() ; $mem = memory_get_peak_usage(); $this->_do_call_ISSUE_MONITOR(array("Extracting xml mem {$mem}"),$vm);
	     foreach ($matches['el'] as $node) {
	        if (preg_match_all("/<(?P<el>[^>]{1,})>(?P<elv>[\s\S]{0,}?)<\/[^>]{1,}>/i",$node, $nodes) && is_array($nodes)) {
			   $qq = array(); for($i=0; $i<count($nodes['el']);$i++) {
			      $qq[trim($nodes['el'][$i])] = trim($nodes['elv'][$i]);
				  }
			   if (!is_null($only)) foreach ($only as $filter=>$v) {
			      if (is_array($qq) && (!array_key_exists($filter, $qq) || $qq[$filter]<>$v)) $qq = null;
			      }
			   if (is_array($qq)) $aa[$p[1]][] = $qq;
			   }
		    }
		 return $aa;
	     }
	  else return $aa;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in XML_EXTRACT: {$e}");
      }
   return false;
   }
   
public function _do_call_BUILD_XML($p, $vm) {
   /** BUILD_XML(structure); Build an XML string from a structure; Return false or xml string;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for BUILD_XML(structure)");
   try {
      return $this->_build_xml($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in BUILD_XML: {$e}");
      }
   return false;
   }
private function _build_xml($a) {
   $s = "";
   if (is_array($a)) {
      foreach ($a as $node=>$v) {
	     $s .= "<{$node}>".$this->_build_xml($v)."</{$node}>\n";
	     }
      }
   else $s = $a;
   return $s;
   }
   
public function _do_call_JSON_PARSE($p, $vm) {
   /** JSON_PARSE(string json); Get structure from a JSON format file; Return false or record structure;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for JSON_PARSE(string)");
   try {
	   $a = json_decode($p[0], true);
	   return $a;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in JSON_PARSE: {$e}");
      }
   return false;
   }

   
//** CATEGORY MT940

// MT940
public function _do_call_MT940_TO_ARRAY($p, $vm) {
   /** MT940_TO_ARRAY(mt940_file_path[,format]); Get structure from an MT940 format file; Return false or record structure;  **/
   if (count($p) < 1) return $this->_RunTime_Exception("Wrong parameter count for MT940_TO_ARRAY(filename[,format])");
   $this->_resolve_working_dir($vm);
   try {
      if (!@file_exists($mtFile = $p[0])) {
         $mtFile = "{$this->WORKING_DIR}/{$p[0]}";
         }
      if (!file_exists($mtFile) || !is_file($mtFile) ) 
         return $this->_RunTime_Exception("Input file missing for MT940_TO_ARRAY");
	  $format = (count($p)>1)?$p[1]:"mt940";
	  $headers = (count($p)>2)?$p[2]:false;
      return mt940ToArray($mtFile, $format, $headers);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in MT940_TO_ARRAY: {$e}");
      }
   return false;
   }

// END MT940

//** CATEGORY PDF

public function _do_call_PDF_TO_TEXT($p, $vm) {
   /** PDF_TO_TEXT(pdf_file); Parse the pdf to plain text, return string or false  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for PDF_TO_TEXT(pdf_file)");
   try {
		$this->_resolve_working_dir($vm);
        if (stripos($p[0], "\\") === false && stripos($p[0],"/")===false) $p[0] = "{$this->WORKING_DIR}/{$p[0]}";
		/*
		$a = new PDF2Text();
		$a->setFilename($p[0]);
		return $a->output();
		*/
		$parser = new \Smalot\PdfParser\Parser();
		$pdf    = $parser->parseFile($p[0]);
		$text = $pdf->getText();
		$lines = explode("\n", $text);
		$text = "";
		foreach ($lines as $line) $text .= $line.PHP_EOL;
		return $text;

		}
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in PDF_TO_TEXT: {$e}");
      }
   return false;
   }
public function _do_call_FILE_TO_TEXT($p, $vm) {
   /** FILE_TO_TEXT(pdf_file); Parse a file to plain text, return string or false  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for FILE_TO_TEXT(_file)");
   try {
		$this->_resolve_working_dir($vm);
        if (stripos($p[0], "\\") === false && stripos($p[0],"/")===false) $p[0] = "{$this->WORKING_DIR}/{$p[0]}";
		$a = new Filetotext($p[0]);
//		$a->setUnicode(false);
		return $a->convertToText();
		}
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in FILE_TO_TEXT: {$e}");
      }
   return false;
   }
public function _do_call_HTML_TO_PDF($p, $vm) {
   /** HTML_TO_PDF(pdf_file, pdf options); Parse a file to plain text, return string or false  **/
   if (count($p) < 1) return $this->_RunTime_Exception("Wrong parameter count for HTML_TO_PDF(_file)");
   try {
		$this->_resolve_working_dir($vm);
        if (stripos($p[0], "\\") === false && stripos($p[0],"/")===false) $p[0] = "{$this->WORKING_DIR}/{$p[0]}";
		$data = file_get_contents($p[0]);
		$opts = (count($p)>1)?$p[1]:"L";
        $html2pdf = new HTML2PDF($opts, 'A4', 'en', true, 'UTF-8', 3);
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $html2pdf->writeHTML($data, isset($_GET['vuehtml']));
		$outfile = pathinfo($p[0],PATHINFO_FILENAME);
		$outdir = pathinfo($p[0],PATHINFO_DIRNAME);
        $html2pdf->Output("{$outdir}/{$outfile}.pdf",'F');
		return true;
		}
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in HTML_TO_PDF: {$e}");
      }
   return false;
   }
public function _do_call_HTML_TO_TEXT($p, $vm) {
   /** HTML_TO_TEXT(html_file); Parse a file to plain text, return string or false  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for HTML_TO_TEXT(_file)");
   try {
		$this->_resolve_working_dir($vm);
        if (stripos($p[0], "\\") === false && stripos($p[0],"/")===false) $p[0] = "{$this->WORKING_DIR}/{$p[0]}";
		$html2text = new HTML2TEXT($p[0], true);
		return $html2text->get_text();
		}
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in HTML_TO_TEXT: {$e}");
      }
   return false;
   }

// END PDF

//** CATEGORY FILE SYSTEM

private $WORKING_DIR = null;

public function _do_call_TEMP_DIR($p, $vm) {
   return $this->_resolve_temp_dir($vm);
   }
public function _resolve_temp_dir($vm) {
   return HDA_TempDirectory($vm->ref);
   }

public function _do_call_WORKING_DIR($p, $vm) {
   /** WORKING_DIR([set_as_directory]); Get or Set the working directory for this session; Return false or working dir path;  **/
   if (count($p)==0) {
      $this->_resolve_working_dir($vm);
      }
   elseif (count($p)==1) {
      $this->WORKING_DIR = $p[0];
      }
   else return $this->_RunTime_Exception("Wrong parameter count for WORKING_DIR(path)");
   return $this->WORKING_DIR;
   }
public function _resolve_working_dir($vm) {
   if (is_null($this->WORKING_DIR)) {
      $this->WORKING_DIR = HDA_WorkingDirectory($vm->ref);
      }
   return $this->WORKING_DIR;
   }
public function _do_call_WORKING_DIR_PATH($p, $vm) {
   /** WORKING_DIR_PATHPATH(); Get the absolute working directory for this session; Return false or working dir path;  **/
   $script_filename = $_SERVER['SCRIPT_FILENAME'];
   $root_dir = pathinfo($script_filename,PATHINFO_DIRNAME);
   $work_dir = $this->_resolve_working_dir($vm);
   $working_dir = "{$root_dir}\\{$work_dir}";
   $working_dir = str_replace("/","\\",$working_dir);
   return $working_dir;
   }
public function _do_call_BIN_DIR_PATH($p, $vm) {
   /** BIN_DIR_PATH(); Get the absolute binary directory for this session; Return false or binary dir path;  **/
   if (count($p) > 1) return $this->_RunTime_Exception("Wrong parameter count for BIN_DIR_PATH([bin.exe])");
   $script_filename = $_SERVER['SCRIPT_FILENAME'];
   $root_dir = pathinfo($script_filename,PATHINFO_DIRNAME);
  // $bin_dir = "{$root_dir}\\binary";
   $bin_dir = INIT('BINARY_ROOT');
   $bin_dir = (count($p)==1)?"{$bin_dir}\\{$p[0]}":$bin_dir;
   return $bin_dir;
   }
public function _do_call_SNIPPET_DIR_PATH($p, $vm) {
   /** SNIPPET_DIR_PATH(); Get the absolute directory for snippets for this session; Return false or snippet dir path;  **/
   if (count($p) > 1) return $this->_RunTime_Exception("Wrong parameter count for SNIPPET_DIR_PATH([snippet_file])");
   global $template_dir;
   $snippet_dir = (count($p)==1)?"{$template_dir}\\{$p[0]}":$template_dir;
   return $snippet_dir;
   }

public function _do_call_MAKE_DIRECTORY($p, $vm) {
   /** MAKE_DIRECTORY(path); Make (mkdir) a directory path; Return false/true;  **/
   $this->_resolve_working_dir($vm);
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for MAKE_DIRECTORY(path)");
   try {
      if (stripos($p[0], $this->WORKING_DIR) === false) $p[0] = "{$this->WORKING_DIR}/{$p[0]}";
      if (!@file_exists($p[0])) @mkdir($p[0]); 
      _chmod($p[0]);
      return true;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in MAKE_DIRECTORY: {$e}");
      }
   return false;
   }
public function _do_call_MAKE_DIRECTORY_PATH($p, $vm) {
   /** MAKE_DIRECTORY_PATH(path); Make (mkdir) an absolute directory path; Return false on fail, clean directory path on success;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for MAKE_DIRECTORY_PATH(path)");
   try {
		$parts = preg_split("#[\\\/]+#",rtrim($p[0],"\\/"));
		if ((count($parts)>1) && ($p[0][0]=='\\' && $p[0][1]=='\\')) {
		   $root = $path = "\\\\{$parts[1]}";
		   $offset = 2;
		   }
		else {
		   $root = $path = $parts[0];
		   $offset = 1;
		   }   
		// Make clean path:
		for ($i=$offset; $i<count($parts); $i++) {
		   $path = rtrim("{$path}/{$parts[$i]}", "\\/");
		   }
		if (@file_exists($path)) return $path;
	  if (!$this->_mkdir_path($root, $parts, $offset)) return false;
      return $path;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in MAKE_DIRECTORY_PATH: {$e}");
      }
   return false;
   }
function _mkdir_path($root, $parts, $offset, $back=0) {
   $path = $root;
   for ($i=$offset; $i<(count($parts)-$back); $i++) {
	  $path = rtrim("{$path}/{$parts[$i]}", "\\/");
	  }
   if (@file_exists($path)) return true;
   elseif ($back>=(count($parts)-$offset)) return $this->_RunTime_Exception("Fails in MAKE_DIRECTORY_PATH at {$path}");
   elseif ($this->_mkdir_path($root, $parts, $offset, $back+1)) {
       $this->CONSOLE_log .= "Making {$path}\n";
      @mkdir($path = "{$path}");_chmod($path);
   //   @mkdir($path = "{$path}/{$parts[$i]}");_chmod($path);
	  return true;
      }
   else return false;
   }

public function _do_call_FETCH_TEMPLATE($p, $vm) {
   /** FETCH_TEMPLATE(template file name); Get contents of the template; Return false or file contents;  **/
   $this->_resolve_working_dir($vm);
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for FETCH_TEMPLATE(path)");
   try {
      global $template_dir;
      return (strlen($p[0])>0 && @file_exists("{$template_dir}/{$p[0]}"))?@file_get_contents("{$template_dir}/{$p[0]}"):"";
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in FETCH_TEMPLATE: {$e}");
      }
   return "";
   }


public function _do_call_FILE_GET_CONTENTS($p, $vm) {
   /** FILE_GET_CONTENTS(file_path); Get contents of the file; Return false or file contents;  **/
   $this->_resolve_working_dir($vm);
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for FILE_GET_CONTENTS(path)");
   try {
      if (stripos($p[0], "\\") === false && stripos($p[0],"/")===false) $p[0] = "{$this->WORKING_DIR}/{$p[0]}";
	  @clearstatcache();
      if (strlen($p[0])>0 && @file_exists($p[0]) && @is_file($p[0])) {
         _chmod($p[0]);
         $s = @file_get_contents($p[0]);
		 return $s;
         }
      return $this->_RunTime_Exception("Fails in FILE_GET_CONTENTS: File {$p[0]} not found");
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in FILE_GET_CONTENTS: {$e}");
      }
   return false;
   }

public function _do_call_FILE_PUT_CONTENTS($p, $vm) {
   /** FILE_PUT_CONTENTS(file_path, string); Put string into file; Return false/true;  **/
   $this->_resolve_working_dir($vm);
   if (count($p) <> 2) return $this->_RunTime_Exception("Wrong parameter count for FILE_PUT_CONTENTS(path,string)");
   try {
      if (strlen($p[0])>0) {
         if (stripos($p[0], "\\") === false && stripos($p[0],"/")===false) $p[0] = "{$this->WORKING_DIR}/{$p[0]}";
         @file_put_contents($p[0], $p[1]); _chmod($p[0]);
         return true;
         }
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in FILE_PUT_CONTENTS: {$e}");
      }
   return false;
   }
public function _do_call_LOOKUP_FILE($p, $vm) {
   /** LOOKUP_FILE(global_file_connect[, filename]); Lookup file details in a global connect; Return false or file details;  **/
   if (count($p)==0 || count($p)>2) return $this->_RunTime_Exception("Wrong parameter count for LOOKUP_FILE(dictionary_name[, filename])");
   try {
       $a = hda_db::hdadb()->HDA_DB_dictionary(NULL, $p[0]);
       if (is_null($a) || !is_array($a) || count($a)<>1) 
          return $this->_RunTime_Exception("LOOKUP_FILE: {$p[0]} dictionary entry not found, or not unique");
         
       $def = $a[0]['Definition'];
       if ($def['Connect Type']<>'FILE') 
          return $this->_RunTime_Exception("LOOKUP_FILE: {$p[0]} dictionary entry not of type FILE");
       $dir = $def['Table'];
       $fname = (count($p)==2 && !is_null($p[1]) && strlen($p[1])>0)?$p[1]:$def['Key'];
	   return array('FilePath'=>"{$dir}/{$fname}",'FileName'=>$fname);
     }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in LOOKUP_FILE: {$e}");
      }
   return false;

   }
public function _do_call_STAT_FILE($p, $vm) {
   /** STAT_FILE(filepath); Return false or file details;  **/
   if (count($p)<>1) return $this->_RunTime_Exception("Wrong parameter count for STAT_FILE(filename)");
   try {
	   return @stat($p[0]);
   }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in STAT: {$e}");
      }
    return false;
	}
public function _do_call_COPY_FILE($p, $vm) {
   /** COPY_FILE(src_file_path, dst_file or [,global_file_connect, dst_filename]); Copy a file; Return false/true;  **/
   $this->_resolve_working_dir($vm);
   if (count($p) < 2) return $this->_RunTime_Exception("Wrong parameter count for COPY_FILE(src_file, dst_file [or dictionary lookup, dst_file_name])");
   try {
      if (stripos($p[0], "\\") === false && stripos($p[0],"/")===false) $p[0] = "{$this->WORKING_DIR}/{$p[0]}";
      if (!@file_exists($p[0])) return $this->_RunTime_Exception("In COPY_FILE: Copy From File: {$p[0]} not found");
	  switch (count($p)) {
	     case 2: $dst_file = $p[1]; break;
		 case 3: 
            $a = hda_db::hdadb()->HDA_DB_dictionary(NULL, $p[1]);
            if (is_null($a) || !is_array($a) || count($a)<>1) 
               return $this->_RunTime_Exception("COPY_FILE: {$p[1]} dictionary entry not found, or not unique");
         
            $def = $a[0]['Definition'];
            if ($def['Connect Type']<>'FILE') 
               return $this->_RunTime_Exception("COPY_FILE: {$p[1]} dictionary entry not of type FILE");
            $dir = $def['Table'];
            $fname = (!is_null($p[2]) && strlen($p[2])>0)?$p[2]:$def['Key'];
			$dst_file = "{$dir}/{$fname}";
		    break;
		 default: return $this->_RunTime_Exception("Wrong parameter count for COPY_FILE(src_file, dst_file [or dictionary lookup, dst_file_name])");
		    break;
	     }
	  return @copy($p[0],$dst_file);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in COPY_FILE: {$e}");
      }
   return false;
   }
public function _do_call_MOVE_FILE($p, $vm) {
   /** MOVE_FILE(src_file_path, dst_file or [,global_file_connect, dst_filename]); Move/rename a file; Return false/true;  **/
   $this->_resolve_working_dir($vm);
   if (count($p) < 2) return $this->_RunTime_Exception("Wrong parameter count for MOVE_FILE(src_file, dst_file [or dictionary lookup, dst_file_name])");
   try {
      if (stripos($p[0], "\\") === false && stripos($p[0],"/")===false) $p[0] = "{$this->WORKING_DIR}/{$p[0]}";
      if (!@file_exists($p[0])) return $this->_RunTime_Exception("In MOVE_FILE: Move From File: {$p[0]} not found");
	  switch (count($p)) {
	     case 2: $dst_file = $p[1]; break;
		 case 3: 
            $a = hda_db::hdadb()->HDA_DB_dictionary(NULL, $p[1]);
            if (is_null($a) || !is_array($a) || count($a)<>1) 
               return $this->_RunTime_Exception("MOVE_FILE: {$p[1]} dictionary entry not found, or not unique");
         
            $def = $a[0]['Definition'];
            if ($def['Connect Type']<>'FILE') 
               return $this->_RunTime_Exception("MOVE_FILE: {$p[1]} dictionary entry not of type FILE");
            $dir = $def['Table'];
            $fname = (!is_null($p[2]) && strlen($p[2])>0)?$p[2]:$def['Key'];
			$dst_file = trim("{$dir}/{$fname}","/");
		    break;
		 default: return $this->_RunTime_Exception("Wrong parameter count for MOVE_FILE(src_file, dst_file [or dictionary lookup, dst_file_name])");
		    break;
	     }
	  return @rename($p[0],$dst_file);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in MOVE_FILE: {$e}");
      }
   return false;
   }
public function _do_call_RENAME($p, $vm) {
   if (count($p) <> 2) return $this->_RunTime_Exception("Wrong parameter count for RENAME(src, dst)");
   try {
	   $this->CONSOLE_log .= date('G:i:s')."> RENAME {$p[0]} to {$p[1]}\n";
	   if (!@file_exists($p[0])) $this->CONSOLE_log .= date('G:i:s')."> RENAME MISSING {$p[0]}\n";
	else $ok = @rename($p[0], $p[1]);
	return true;
	}
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in RENAME: {$e}");
      }
   return false;
	}
public function _do_call_COPY_ALL($p, $vm) {
   /** COPY_ALL(src, dst); copy all files and directory structure; Return false/true;  **/
   if (count($p) <> 2) return $this->_RunTime_Exception("Wrong parameter count for COPY_ALL(src, dst)");
   try {
	$this->_recurse_copy($p[0], $p[1]);
	return true;
	}
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in COPY_ALL: {$e}");
      }
   return false;
   }
private function _recurse_copy($src, $dst) {
    $dir = opendir($src); 
    if (!@file_exists($dst)) @mkdir($dst); 
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            if ( is_dir($src . '/' . $file) ) { 
                $this->_recurse_copy($src . '/' . $file,$dst . '/' . $file); 
            } 
            else { 
                @copy($src . '/' . $file,$dst . '/' . $file); 
            } 
        } 
    } 
    closedir($dir); 
   } 
   
public function _do_call_FILE_EXISTS($p, $vm) {
   /** FILE_EXISTS(file_path); Test if a file exists; Return false/true;  **/
   $this->_resolve_working_dir($vm);
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for FILE_EXISTS(localpath)");
   try {
      if (stripos($p[0], "\\") === false && stripos($p[0],"/")===false) $p[0] = "{$this->WORKING_DIR}/{$p[0]}";
      return (strlen($p[0])>0)?@file_exists($p[0]):false;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in FILE_EXISTS: {$e}");
      }
   return false;
   }
public function _do_call_IS_DIR($p, $vm) {
   /** IS_DIR(file_path); Test if a filepath is dir; Return false/true;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for IS_DIR(localpath)");
   try {
      return is_dir($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in IS_DIR: {$e}");
      }
   return false;
   }
public function _do_call_FILE_PATH_EXISTS($p, $vm) {
   /** FILE_PATH_EXISTS(file_path); Test if an absolute file path exists; Return false/true;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for FILE_PATH_EXISTS(path)");
   try {
      return (strlen($p[0])>0)?@file_exists($p[0]):false;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in FILE_PATH_EXISTS: {$e}");
      }
   return false;
   }
public function _do_call_FILE_DELETE($p, $vm) {
   /** FILE_DELETE(file_path); Delete a file; Return false/true;  **/
   $this->_resolve_working_dir($vm);
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for FILE_DELETE(localpath)");
   try {
      if (stripos($p[0], "\\") === false && stripos($p[0],"/")===false) $p[0] = "{$this->WORKING_DIR}/{$p[0]}";
	  if ((strlen($p[0])>0) && (@file_exists($p[0]))) return @unlink($p[0]);
      return false;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in FILE_DELETE: {$e}");
      }
   return false;
   }
public function _do_call_DIR_DELETE($p, $vm) {
	try {
		rmdir($p[0]);
		return true;
		}
	catch (Exception $e) {
		return $this->_RunTime_Exception("Fails in DIR_DELETE: {$e}");
		}
	return false;
	}
public function _do_call_TEMP_DELETE($p, $vm) {
   /** TEMP_DELETE([mask]); Delete files in the temp directory; Return false/true;  **/
   $path = $this->_resolve_temp_dir($vm);
   if (count($p) > 1) return $this->_RunTime_Exception("Wrong parameter count for TEMP_DELETE([mask])");
   try {
      $mask = (count($p)==1)?$p[0]:'*.*';
	  array_map('unlink', glob("{$path}/{$mask}")); 
      return true;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in TEMP_DELETE: {$e}");
      }
   return false;
   }
public function _do_call_GET_FILE_PATHS($p, $vm) {
   /** GET_FILE_PATHS(in_directory); Get file list in directory; Return false or file list structure;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for GET_FILE_PATHS(in_directory)");
   try {
      if (@file_exists($p[0])) {
         $ff = @glob("{$p[0]}/*");
         $a = array();
         foreach ($ff as $f) if (is_file($f)) $a[] = $f;
         return $a;
         }
      else return $this->_RunTime_Exception("Fails in GET_FILE_PATHS: the directory {$p[0]} does not exist");
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in GET_FILE_PATHS: {$e}");
      }
   return false;
   }
   private function glob_recursive($directory, &$directories = array()) {
			 foreach(glob($directory, GLOB_ONLYDIR | GLOB_NOSORT) as $folder) {
				 $directories[] = $folder;
				 $this->glob_recursive("{$folder}/*", $directories);
			 }
		  return $directories;
		 }
public function _do_call_GET_ALL_FILES($p, $vm) {
   /** GET_ALL_FILES(in_directory, ext); Search recursively in directories for files with ext; Return false or file list structure;  **/
   if (count($p) < 1) return $this->_RunTime_Exception("Wrong parameter count for GET ALL FILES(dir, ext)");
   try {
      $directory = $p[0];
	  if (count($p)==1) $extension = '*'; else $extension = $p[1];
		 $this->glob_recursive($directory, $directories);
		 $files = array ();
		 foreach($directories as $directory) {
				 foreach(glob("{$directory}/*.{$extension}") as $file) {
					 $files[] = $file;
			 } 
		 }
		 return $files;
       }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in GET_ALL_FILES: {$e}");
      }
   return false;
}
public function _do_call_GET_ALL_DIRS($p, $vm) {
   /** GET_ALL_FILES(in_directory, ext); Search recursively in directories for files with ext; Return false or file list structure;  **/
   if (count($p) < 1) return $this->_RunTime_Exception("Wrong parameter count for GET ALL DIRS(dir)");
   try {
      $directory = $p[0];
	  $directories = array();
	  foreach(glob("{$directory}/*", GLOB_ONLYDIR | GLOB_NOSORT) as $folder) {
		 $directories[] = $folder;
		 }
	   return $directories;
       }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in GET_ALL_DIRS: {$e}");
      }
   return false;
}

public function _do_call_PATH_INFO($p, $vm) {
   /** PATH_INFO(file_path); Get file details; Return false or file detail structure;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for PATH_INFO(path)");
   try {
      if (!is_string($p[0])) return $this->_RunTime_Exception("PATH_INFO expects a string for the path: ".print_r($p[0],true));
      $path_info = @pathinfo($p[0]); 
      if (is_null($path_info) || !is_array($path_info)) return false;
	  $path_info['exists'] = @file_exists($p[0]);
	  $path_info['path'] = $p[0];
      return $path_info;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in PATH_INFO: {$e}");
      }
   return false;
   }
public function _do_call_BASENAME($p, $vm) {
	return pathinfo($p[0],PATHINFO_BASENAME);
}

private $LIB_FILE_READ_OPEN = null;
private $LIB_FILE_READ_LINES = array();
private $LIB_FILE_READ_LINEN = 0;
public function _do_call_FILE_OPEN_READ($p, $vm) {
   /** FILE_OPEN_READ(path); Open a file read session; Return false/true;  **/
   $this->_resolve_working_dir($vm);
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for FILE_OPEN_READ(path)");
   try {
      if (!is_null($this->LIB_FILE_READ_OPEN)) @fclose($this->LIB_FILE_READ_OPEN);
      if (stripos($p[0], "\\") === false && stripos($p[0],"/")===false) $p[0] = "{$this->WORKING_DIR}/{$p[0]}";
      $this->LIB_FILE_READ_OPEN = (strlen($p[0])>0)?@fopen($p[0], 'r'):null;
      return (!is_null($this->LIB_FILE_READ_OPEN));
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in FILE_OPEN_READ: {$e}");
      }
   return false;
   }
public function _do_call_FILE_READ_LINE($p, $vm) {
   /** FILE_READ_LINE(); Read a line from an open file read session; Return false or string;  **/
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for FILE_READ_LINE()");
   try {
      return @fgets($this->LIB_FILE_READ_OPEN);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in FILE_READ_LINE: {$e}");
      }
   return false;
   }
public function _do_call_FILE_CSV_LINE($p, $vm) {
   /** FILE_CSV_LINE([delimiter[,enclosure[,escape]]]); Read a line as csv from an open file read session; Return false or string;  **/
   if ($this->LIB_FILE_READ_OPEN===false || is_null($this->LIB_FILE_READ_OPEN)) return $this->_RunTime_Exception("File not open for FILE_CSV_LINE()");
   $delimiter = (count($p)>0 && strlen($p[0])>0)?$p[0][0]:',';
   $enclosure = (count($p)>1 && strlen($p[1])>0)?$p[1][0]:'"';
   $escape = (count($p)==3 && strlen($p[2])>0)?$p[2][0]:chr(0);
   $s = fgets($this->LIB_FILE_READ_OPEN);
   if ($s===false) return false;
   $a = str_getcsv($s, $delimiter, $enclosure, $escape);
   //$a = @fgetcsv($this->LIB_FILE_READ_OPEN, 0, $delimiter, $enclosure, $escape);
   return $a;
   }
public function _do_call_FILE_READ_CLOSE($p, $vm) {
   /** FILE_READ_CLOSE(); Close an open file read session; Return false/true;  **/
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for FILE_READ_CLOSE()");
   try {
      if (!is_null($this->LIB_FILE_READ_OPEN)) @fclose($this->LIB_FILE_READ_OPEN);
      $this->LIB_FILE_READ_OPEN = null;
      return true;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in FILE_READ_CLOSE: {$e}");
      }
   return false;
   }
public function _do_call_FILE_EOF($p, $vm) {
   /** FILE_EOF(); Test for EOF; Return false/true;  **/
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for FILE_EOF()");
   try {
      return @feof($this->LIB_FILE_READ_OPEN);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in FILE_EOF: {$e}");
      }
   return false;
   }
   
private $LIB_FILE_WRITE_OPEN = null;
private $LIB_FILE_WRITE_PATH = null;
public function _do_call_FILE_OPEN_WRITE($p, $vm) {
   /** FILE_OPEN_WRITE(path[, mode]); Open a file write session; Return false/true;  **/
   $this->_resolve_working_dir($vm);
   if (count($p) > 2) return $this->_RunTime_Exception("Wrong parameter count for FILE_OPEN_WRITE(path, [mode])");
   if (strlen($p[0])==0) return $this->_RunTime_Exception("Empty filename for write open");
   try {
	  $mode = (count($p)==2)?$p[1]:'w';
      if (!is_null($this->LIB_FILE_WRITE_OPEN)) @fclose($this->LIB_FILE_WRITE_OPEN);
      if (stripos($p[0], "\\") === false && stripos($p[0],"/")===false) $p[0] = "{$this->WORKING_DIR}/{$p[0]}";
      $this->LIB_FILE_WRITE_PATH = $p[0];
      //if (file_exists($this->LIB_FILE_WRITE_PATH)) @unlink($this->LIB_FILE_WRITE_PATH);
      $this->LIB_FILE_WRITE_OPEN = @fopen($this->LIB_FILE_WRITE_PATH, $mode); _chmod($this->LIB_FILE_WRITE_PATH);
      return (!is_null($this->LIB_FILE_WRITE_OPEN));
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in FILE_OPEN_WRITE: {$e}");
      }
   return false;
   }
public function _do_call_FILE_WRITE_LINE($p, $vm) {
   /** FILE_WRITE_LINE(string); Write a line to an open file write session; Return false/true;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for FILE_WRITE_LINE(string)");
   try {
      $s = "{$p[0]}";
      $s .= "\r\n";
      $bytes_written = @fwrite($this->LIB_FILE_WRITE_OPEN, $s, strlen($s));
      return ($bytes_written !== false)?true:false;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in FILE_WRITE_LINE: {$e}");
      }
   return false;
   }
public function _do_call_FILE_WRITE_CLOSE($p, $vm) {
   /** FILE_WRITE_CLOSE(); Close an open file write session; Return false/true;  **/
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for FILE_WRITE_CLOSE()");
   try {
      if (!is_null($this->LIB_FILE_WRITE_OPEN)) @fclose($this->LIB_FILE_WRITE_OPEN);
      $this->LIB_FILE_WRITE_OPEN = null;
      return true;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in FILE_WRITE_CLOSE: {$e}");
      }
   return false;
   }
   
function _do_call_DISK_SPACE($p, $vm) {
   /** DISK_SPACE(dir_name); Return disk space free and total in dir; Return false or result structure;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for DISK_SPACE(dir_name)");
   try {
      $d = array();
	  $d['FREE'] = disk_free_space($p[0]);
	  $d['TOTAL'] = disk_total_space($p[0]);
	  return $d;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in DISK SPACE: {$e}");
      }
   return false;
   }

//END FILE SYSTEM
//** CATEGORY SYSTEM

public function _do_call_on_error_close($p, $vm) {
   /** ON_ERROR_CLOSE(); Close off all open resources; Return false/true;  **/
   return $this->close_library_resources();
   }
public function close_library_resources() {
   if (!is_null($this->LIB_FILE_READ_OPEN) && is_resource($this->LIB_FILE_READ_OPEN)) @fclose($this->LIB_FILE_READ_OPEN);
   $this->LIB_FILE_READ_OPEN = null;
   if (!is_null($this->LIB_FILE_WRITE_OPEN) && is_resource($this->LIB_FILE_WRITE_OPEN)) @fclose($this->LIB_FILE_WRITE_OPEN);
   $this->LIB_FILE_WRITE_OPEN = null;
   if (!is_null($this->ODBC_QUERY_CONN) && is_resource($this->ODBC_QUERY_CONN)) @odbc_close($this->ODBC_QUERY_CONN);
   $this->ODBC_QUERY_CONN = false;
   if (!is_null($this->ORCL_QUERY_CONN) && is_resource($this->ORCL_QUERY_CONN)) @oci_close($this->ORCL_QUERY_CONN);
   $this->ORCL_QUERY_CONN = false;
   if (!is_null($this->MSSQL_QUERY_CONN) && is_resource($this->MSSQL_QUERY_CONN)) @sqlsrv_close($this->MSSQL_QUERY_CONN);
   $this->MSSQL_QUERY_CONN = false;
   return true;
   }
   
public function _do_call_send_error($p, $vm) {
   /** SEND_ERROR(string); Send an error message by email to system owner; Return false/true;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for SEND_ERROR(message)");
   try {
      HDA_SendErrorMail($p[0]);
      }
   catch(Exception $e) {
      return $this->_RunTime_Exception("Fails in SEND_ERROR: {$e}");
      }
   return false;
   }

// END ON ERROR

// General VALUE ADJUSTMENTS

public function _do_call_text_convert($p, $vm) {
   /** TEXT_CONVERT(text); Return a new string in asci from input; Return false or string;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for TEXT_CONVERT(text)");
   try {
		$s = HDA_validateEncoding($p[0], $t, $error);
		return $s;
   }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in TEXT_CONVERT: {$e}");
      }
   return $p[0];
   }


public function _do_call_value_adjust($p, $vm) {
   /** VALUE_ADJUST(input_value, mod_code); Return a format new value from input; Return false or string;  **/
   if (count($p) <> 2) return $this->_RunTime_Exception("Wrong parameter count for VALUE_ADJUST(input_value, mod_code)");
   try {
      return $this->_value_adjust($p[0], $p[1]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in VALUE_ADJUST: {$e}");
      }
   return $p[0];
   }
private function _make_date_pattern($p_part, $p_len) {
   $p_len_1 = $p_len_2 = $p_len;
   switch($p_part) {
      case 'Y': 
	    $p_len_1 = ($p_len!=1)?$p_len:2; $p_len_2 = 4; 
		return "(?P<year>[\d]{{$p_len_1},{$p_len_2}})";
	  case 'M': 
	    $p_len_1 = ($p_len>1)?$p_len:1; $p_len_2 = ($p_len>1)?"":2;
		return ($p_len>2)?"(?P<mth>[\w]{3,{$p_len}})":"(?P<mth>[\d]{{$p_len_1},{$p_len_2}})";
	  case 'D': 
	    $p_len_1 = $p_len; $p_len_2 = 2;
		return "(?P<day>[\d]{{$p_len_1},{$p_len_2}})";
	  case 'G': 
	    $p_len_1 = $p_len; $p_len_2 = 2;
		return "(?P<ghr>[\d]{{$p_len_1},{$p_len_2}})";
	  case 'H': 
	    $p_len_1 = $p_len; $p_len_2 = 2;
		return "(?P<hr>[\d]{{$p_len_1},{$p_len_2}})";
	  case 'I': 
	    $p_len_1 = $p_len; $p_len_2 = 2;
		return "(?P<min>[\d]{{$p_len_1},{$p_len_2}})";
	  case 'S': 
	    $p_len_1 = $p_len; $p_len_2 = 2;
		return "(?P<sec>[\d]{{$p_len_1},{$p_len_2}})";
	  }
   return "[{$p_part}]{{$p_len},{$p_len}}";
   }
   
private function _make_date_from_s($v, $m) {
   $trm = null;
   $pattern = "";
   $m .= "-";
   $p_part = null;
   $p_len = 0;
   for ($i=0; $i<strlen($m); $i++) {
      $p = strtoupper($m[$i]);
      if (!is_null($p_part) && ($p_part!=$p)) { $pattern .= $this->_make_date_pattern($p_part, $p_len); $p_len = 0; }
	  $p_part = $p; $p_len++;
      }
   $pattern = trim($pattern, '-');
   if (@preg_match("#{$pattern}#", $v, $matches)==1) {
      if (!array_key_exists('day',$matches)) $matches['day'] = "01";
      if (!array_key_exists('mth',$matches)) $matches['mth'] = "01";
      if (!array_key_exists('year',$matches)) $matches['year'] = date('Y',time());
      $v = "{$matches['year']}-{$matches['mth']}-{$matches['day']}";
	  if (array_key_exists('hr',$matches)||array_key_exists('ghr',$matches)) {
	     $v .= " ";
	     $v .= (array_key_exists('hr',$matches))?$matches['hr']:"";
	     $v .= (array_key_exists('ghr',$matches))?$matches['ghr']:"";
		 $v .= ":";
	     $v .= (array_key_exists('min',$matches))?$matches['min']:"00";
		 $v .= ":";
	     $v .= (array_key_exists('sec',$matches))?$matches['sec']:"00";
	     }
      }
   return $v;
   }

private function _make_date_from($v, $m) {
   $y_len = 0;
   $m_len = 0; 
   $d_len = 0;
   $h_len = 0;
   $g_len = 0;
   $i_len = 0;
   $s_len = 0;
   $trm = null;
   $pattern = "";
   $m .= "-";
   for ($i=0; $i<strlen($m); $i++) {
      switch(strtoupper($m[$i])) {
         case 'Y': $y_len++; break;
         case 'M': $m_len++; 
                   break;
         case 'D': $d_len++; break;
		 case 'H': $h_len++; break;
		 case 'G': $g_len++; break;
		 case 'I': $i_len++; break;
		 case 'S': $s_len++; break;
         default: 
            $trm = $m[$i]; 
            if ($y_len>0) { $pattern .= "(?P<year>[\d]{2,4})"; $y_len = 0; }
            if ($d_len==1 || $d_len==2) { $pattern .= "(?P<day>[\d]{1,2})"; $d_len = 0; }
            if ($m_len==1 || $m_len==2) { $pattern .= "(?P<mth>[\d]{1,2})"; $m_len = 0; }
            if ($m_len==3) { $pattern .= "(?P<mth>[\w]{3,3})"; $m_len = 0; }
            if ($h_len>0) { $pattern .= "(?P<hr>[\d]{1,2})"; $h_len = 0; }
            if ($g_len>0) { $pattern .= "(?P<ghr>[\d]{1,2})"; $g_len = 0; }
            if ($i_len>0) { $pattern .= "(?P<min>[\d]{1,2})"; $i_len = 0; }
            if ($s_len>0) { $pattern .= "(?P<sec>[\d]{1,2})"; $s_len = 0; }
			$pattern .= "{$trm}";
			$trm = null;
            break;
         }
      }
	  echo "<pre>{$pattern}</pre>";
   $pattern = trim($pattern, '-');
   if (@preg_match("#{$pattern}#", $v, $matches)==1) {
      if (!array_key_exists('day',$matches)) $matches['day'] = "01";
      if (!array_key_exists('mth',$matches)) $matches['mth'] = "01";
      if (!array_key_exists('year',$matches)) $matches['year'] = date('Y',time());
      $v = "{$matches['year']}-{$matches['mth']}-{$matches['day']}";
	  if (array_key_exists('hr',$matches)||array_key_exists('ghr',$matches)) {
	     $v .= " ";
	     $v .= (array_key_exists('hr',$matches))?$matches['hr']:"";
	     $v .= (array_key_exists('ghr',$matches))?$matches['ghr']:"";
		 $v .= ":";
	     $v .= (array_key_exists('min',$matches))?$matches['min']:"00";
		 $v .= ":";
	     $v .= (array_key_exists('sec',$matches))?$matches['sec']:"00";
	     }
      }
   return $v;
   }
private function _value_adjust($v, $code) { 
   try {
      $mod = explode('#',$code);
      $negate = false;
      switch ($mod[0]) {
         default: return $v;
         case 'NEGATE_NUMBER':
            $negate = true;
         case 'NUMBER':
		    $s = @preg_match("/\([ ]*(?P<num>[0-9\.\,]{1,})[ ]*\)/",$v, $matches);
			if ($s!==false && is_array($matches) && array_key_exists('num',$matches)) {
			   $s = @preg_replace("/,/","",$matches['num']);
			   return  ($negate)?"-{$s}":$s;
			   }
		//	if (preg_match("/^[0-9\.\-,]{1,}/",trim($v),$matches) ==false) return $v;
            $s = @preg_replace("/[^0-9\.\-\,]/","",$v);
            $s = @preg_match("/(?P<neg>[\-]{0,1})[0]{0,}(?P<num>[0-9\.\,]{1,})/",$s,$matches);
            if ($s!==false && is_array($matches) && array_key_exists('num',$matches)) {
               $s = $matches['num'];
			   if ((strlen($s)==0)||($s[0]=='.')) $s = "0{$s}";
               if (array_key_exists('neg',$matches) && $matches['neg']=='-') {
                  $s = ($negate)?$s:"-{$s}";
                  }
               elseif ($negate) $s = "-{$s}";
               }
           return $s;
           break;
         case 'EURO_TO_EN_NUMBER':
		    $s = trim($v);
			$s = explode("\n",$s);
			$s = $s[0];
            $s = @preg_replace("/[^0-9\.\-,]/u","",$s);
            $s = @preg_replace("/\./","!",$s);
            $s = @preg_replace("/,/",".",$s);
            $s = @preg_replace("/!/",",",$s);
            $s = @preg_replace("/\"/","",$s);
         //   $s = @floatval($s);
         //   if ($negate) $s = -$s;
            return $s;
            break;
         case 'FORMAT_NUMBER':
            $thou = '';
            $dplaces = 2;
            $dpt = '.';
            if (count($mod)>1) {
               $format = @preg_match("/(?P<thou>[ \.,]){0,1}(?P<dplaces>[\d]{0,2})(?P<dpt>[\.,]{0,1})/",$mod[1],$matches);
               if ($format !== false) { $thou = $matches['thou']; $dplaces = $matches['dplaces']; $dpt = $matches['dpt']; }
               }
            return number_format($v,$dplaces,$dpt,$thou);
            break;
         case 'DATE':
            if (count($mod)==2) {
               $s = @preg_replace("#/#","-",$v);
               $s = @preg_replace("/\"/","",$s);
               $tm = @strtotime($s);
               }
            elseif (count($mod)==3) {
				$s = $this->_make_date_from_s($v, $mod[2]);
               $tm = strtotime($s);
               }
            else return $v;
            return @date($mod[1],$tm);
            break;
		 case 'XLDATE':
		    return @date($mod[1], PHPExcel_Shared_Date::ExcelToPHP($v));
		 case 'CDR_DATE':
		    return $this->_CDR_TIME($v);
		    break;
		 case 'ISO_CURRENCY':
		    switch($v) {
			   case '124': case 124: $v = 'CAD'; break;
			   case '756': case 756: $v = 'CHF'; break;
			   case '947': case 947: $v = 'CHE'; break;
			   case '156': case 156: $v = 'CNY'; break;
			   case '978': case 978: $v = 'EUR'; break;
			   case '826': case 826: $v = 'GBP'; break;
			   case '344': case 344: $v = 'HKD'; break;
			   case '356': case 356: $v = 'INR'; break;
			   case '376': case 376: $v = 'ILS'; break;
			   case '484': case 484: $v = 'MXN'; break;
			   case '554': case 554: $v = 'NZD'; break;
			   case '036': case '36': case 36: $v = 'AUD'; break;
			   case '643': case 643: $v = 'RUB'; break;
			   case '752': case 752: $v = 'SGD'; break;
			   case '840': case 840: $v = 'USD'; break;
			   case '710': case 710: $v = 'ZAR'; break;
			   case '784': case 784: $v = 'AED'; break;
			   case '949': case 949: $v = 'TRY'; break;
			   case '392': case 393: $v = 'YEN'; break;
			   case '702': case 702: $v = 'SGD'; break;
			   }
			return $v;
		    break;
         case 'CURRENCY_SYMBOL':
            $s = @preg_match("/(?P<neg>[\-]{0,1})(?P<symbol>[^0-9]*)(?P<num>[0-9\.,]{0,})/",$v,$matches);
			$curr_s = "XX"; $curr_n=0;
            if ($s!==false && is_array($matches) && array_key_exists('symbol',$matches)) {
			   $curr_s = $matches['symbol'];
               $curr_n = 0;
               for ($i=0; $i<strlen($curr_s);$i++) {
			      $curr_n = ($curr_n<<8)+ord($curr_s[$i]);
				  }
               switch ((int)$curr_n) {
			      case 163:
				  case hexdec('c382c2a3'):
				  case 0xc382c2a3:
			      case 0xc2a3: return "GBP"; break;
			      case 164:
				  case 0xc282c2ac:
				  case hexdec('c282c2ac'):
			      case 0xc280: return "EUR"; break;
			      case 36: return "USD"; break;
			      case 0xc2a5: return "YEN"; break;
				  case 0x180bf: return "THB"; break;
				  case 0x144a9: return "KRW"; break;
				  case 0x144aa: return "ILS"; break;
				  case 0x144ab: return "DNG"; break;
				  case 0x144b9: return "INR"; break;
			      }
			   switch ($curr_s) {
			      case "\x01\x80\xbf":
			      case "àž¿": return "THB";
				  case "\xc2\x82\xc2\xac":
				  case "\xe2\x82\xac":
				  case "\xc3\x82\xc2\x80":
				  case "\xc2\x80":
                  case "â‚¬":
                  case "€": return "EUR";
                  case "$": return "USD";
				  case "\xc2\xa3":
				  case "\xc3\x82\xc2\xa3":
				  case "Â£":
                  case "£": return "GBP";
				  case "\x01\x44\xa9":
				  case "â©": return "KRW";
				  case "\x01\x44\xaa":
				  case "âª": return "ILS";
				  case "\x01\x44\xab":
				  case "â«": return "DNG";
				  case "\x01\x44\xb9":
				  case "â¹": return "INR";
                  }
			   switch (sprintf("%x",$curr_n)) {
			      case "c282c2ac": return "EUR";
				  }
			   }
            return sprintf("%x",$curr_n);
            break;
         }
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in VALUE_ADJUST: {$e}");
      }
   return $p[0];
   }
// End VALUE ADJUSTMENTS

// SORTING

public function _do_call_SORT($p, $vm) {
   /** SORT(structure[, ASC/DESC[, VALUE/KEY]]); Sort a structure; Return false or new structure;  **/
   if (count($p) < 1) return $this->_RunTime_Exception("Wrong parameter count for SORT(struct [,ASC/DESC[,sort_type]])");
   try {
      $order = (count($p)>1)?$p[1]:'ASC';
	  switch ($order = strtoupper($order)) {
	     case 'ASC':
		 case 'DESC': break;
		 default: return $this->_RunTime_Exception("Fails in SORT, order not known, should be either ASC or DESC, given: {$order}");
		 }
	  $sort_type = (count($p)>2)?$p[2]:'VALUE';
	  $s = $p[0];
	  switch ($sort_type = strtoupper($sort_type)) {
	     case 'VALUE':
		    switch ($order) {
			   case 'ASC': return (asort($s))?$s:false; 
			   case 'DESC': return (rsort($s))?$s:false; 
			   }
			break;
		 case 'KEY':
		    switch ($order) {
			   case 'ASC': return (ksort($s))?$s:false; 
			   case 'DESC': return (krsort($s))?$s:false; 
			   }
			break;
		 case 'KEY_VALUE':
		    switch ($order) {
			   case 'ASC': return (array_multisort($s, SORT_ASC))?$s:false; 
			   case 'DESC': return (array_multisort($s, SORT_DESC))?$s:false; 
			   }
			break;
	     default: return $this->_RunTime_Exception("Fails in SORT, type not known, should be either VALUE or KEY, given: {$sort_type}");
		 }
      return false;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SORT: {$e}");
      }
   return false;
   }


// End SORTING

// TEXT PROCESSOR

public function _do_call_text_processor_split($p, $vm) {
   if (count($p) <>2) return $this->_RunTime_Exception("Wrong parameter count for TEXT_PROCESSOR_SPLIT(record_section_pattern,input string)");
   try {
      $s = str_replace(array("\r\n","\n\r","\r","\n"),PHP_EOL,$p[1]);
      $result = @preg_split("/(?P<section>{$p[0]})/i",$s,-1,PREG_SPLIT_DELIM_CAPTURE);
      if ($result===false) return false;
      if (!is_array($result)) return false;
      if(count($result)<2) return false;
      $split_capture = $result[1];
      $aa = array();
      for ($i=2; $i<count($result);$i+=2) {
         $aa[] = "{$split_capture}{$result[$i]}";
         }
      return $aa;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in TEXT_PROCESSOR_SPLIT: {$e}");
      }
   return false;
   }

public function _do_call_text_processor_filedef($p, $vm) {
   $this->_resolve_working_dir($vm);
   if (count($p) <>1) return $this->_RunTime_Exception("Wrong parameter count for TEXT_PROCESSOR_FILEDEF(definition_file)");
   try {
      $defFile = "{$this->WORKING_DIR}/{$p[0]}";
      if (!@file_exists($defFile) || !is_file($defFile) ) 
         return $this->_RunTime_Exception("Definition file missing for TEXT_PROCESSOR_FILEDEF");
      $def = @file_get_contents($defFile);
      $def = hda_db::hdadb()->HDA_DB_unserialize($def);
      if (!is_array($def) || !array_key_exists('Fields',$def) || !is_array($def['Fields']))
         return $this->_RunTime_Exception("Invalid format in definition file for TEXT_PROCESSOR_FILEDEF {$p[0]}");
      return $def;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in TEXT_PROCESSOR_FILEDEF: {$e}");
      }
   return false;
   }

   

public function _do_call_text_processor_tabdefs($p, $vm) {
   $this->_resolve_working_dir($vm);
   if (count($p) <>1) return $this->_RunTime_Exception("Wrong parameter count for TEXT_PROCESSOR_TABDEFS(definition_file)");
   try {
      $defFile = "{$this->WORKING_DIR}/{$p[0]}";
      if (!@file_exists($defFile) || !is_file($defFile) ) 
         return $this->_RunTime_Exception("Definition file missing for TEXT_PROCESSOR_TABDEFS");
      $def = @file_get_contents($defFile);
      $def = hda_db::hdadb()->HDA_DB_unserialize($def);
      if (!is_array($def) || !array_key_exists('Fields',$def) || !is_array($def['Fields']))
         return $this->_RunTime_Exception("Invalid format in definition file for TEXT_PROCESSOR_TABDEFS {$p[0]}");
      $record = array();
      foreach ($def['Fields'] as $field=>$fdef) {
         $record[$field]['Tab'] = $fdef['Tab'];
         $record[$field]['Line'] = $fdef['Line'];
         if (array_key_exists('Match',$fdef)) {
            $matches = explode('--||--',$fdef['Match']);
            $apply_regx = "/.*?";
            for ($i=0; $i<count($matches); $i++) {
               $matches[$i] = trim($matches[$i]);
               $apply_regx .= "{$matches[$i]}.*?";
               }
            $apply_regx .= "(?P<line>[^\n]*)";
            $apply_regx .= ".*/si";
            $record[$field]['Match'] = $apply_regx;
            }
         else $record[$field]['Match'] = null;
         }
      return $record;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in TEXT_PROCESSOR_TABDEFS: {$e}");
      }
   return false;
   }

public function _do_call_text_processor($p, $vm) {
   $this->_resolve_working_dir($vm);
   if (count($p) < 2) return $this->_RunTime_Exception("Wrong parameter count for TEXT_PROCESSOR(string,def_file,all_file)");
   try {
      if (is_null($p[1])) return array(); // skip text_processor use, no fields defined, maybe a custom regex use only
      $defFile = "{$this->WORKING_DIR}/{$p[1]}";
      if (!@file_exists($defFile) || !is_file($defFile) ) 
         return $this->_RunTime_Exception("Input or Definition files missing for TEXT_PROCESSOR");
      $def = @file_get_contents($defFile);
      $def = hda_db::hdadb()->HDA_DB_unserialize($def);
      if (!is_array($def) || !array_key_exists('Fields',$def) || !is_array($def['Fields']))
         return $this->_RunTime_Exception("Invalid format in definition file for TEXT_PROCESSOR {$p[1]}");
      $all_file_fields = (count($p)==3 && $p[2]==true);
      $record = array();
      foreach ($def['Fields'] as $field=>$fdef) {
         if ($all_file_fields && array_key_exists('InFile',$fdef) && $fdef['InFile']<>'FILE_FIELD') continue;
         if (!$all_file_fields && array_key_exists('InFile',$fdef) && $fdef['InFile']=='FILE_FIELD') continue;
         $rx = explode('--||--',$fdef['Match']);
         $apply_regx = "/.*?";
         for ($i=0; $i<count($rx); $i++) {
            $rx[$i] = trim($rx[$i]);
            if (strlen($rx[$i])>0) $apply_regx .= "{$rx[$i]}.*?";
            }
         $apply_regx .= "(?P<line>[^\n]*)";
         $apply_regx .= "/si";
         $ok = @preg_match($apply_regx, $p[0], $matches);
         if ($ok===false) {
            return $this->_RunTime_Exception("Failed to match condition for field {$field} in TEXT_PROCESSOR");
            }
         if ($ok>0) {
            if (!array_key_exists('line',$matches))
               return $this->_RunTime_Exception("Failed find field {$field} in TEXT_PROCESSOR");
            $line = $matches['line'];
			$record[$field] = (is_string($line) && is_numeric($fdef['Start']) && is_numeric($fdef['Length']))?@trim(@substr($line, $fdef['Start'],$fdef['Length'])):null;
            }
         else $record[$field] = null;
         }
      return $record;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in TEXT_PROCESSOR: {$e}");
      }
   return false;
   }

// END TEXT PROCESSOR

//** CATEGORY DATE TIMES

public function _do_call_TIME($p, $vm) {
   /** TIME(); Get unix time; Return false or time;  **/
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for TIME()");
   try {
      return @time();
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in TIME: {$e}");
      }
   return false;
   }

public function _do_call_STYLE_DATETIME($p, $vm) {
   /** STYLE_DATETIME([unix_time]); Form a parsable date time; Return false or string;  **/
   if (count($p) >1) return $this->_RunTime_Exception("Wrong parameter count for STYLE_DATETIME([optional time])");
   try {
      return (count($p)==0)?hda_db::hdadb()->PRO_DBtime_Styledate(@time(),true):hda_db::hdadb()->PRO_DBtime_Styledate($p[0],true);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in STYLE_DATETIME: {$e}");
      }
   return false;
   }

public function _do_call_DB_DATETIME($p, $vm) {
   /** DB_DATETIME([unix_time]); Form a date time for a DB write; Return false or string;  **/
   if (count($p) >1) return $this->_RunTime_Exception("Wrong parameter count for DB_DATETIME([optional time])");
   try {
      return (count($p)==0)?hda_db::hdadb()->PRO_DB_DateTime(@time(),true):hda_db::hdadb()->PRO_DB_DateTime($p[0],true);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in DB_DATETIME: {$e}");
      }
   return false;
   }


public function _do_call_STRING_TO_TIME($p, $vm) {
   /** STRING_TO_TIME(parsable_datetime_string); Parse to a unix time; Return false or time;  **/
   if (count($p) <>1) return $this->_RunTime_Exception("Wrong parameter count for STRING_TO_TIME(string)");
   try {
      return @strtotime($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in STRING_TO_TIME: {$e}");
      }
   return false;
   }
   
public function _do_call_DATETIME($p, $vm) {
   /** DATETIME(string_datetime, timezone); Get unix time; Return false or time;  **/
   if (count($p) <>2) return $this->_RunTime_Exception("Wrong parameter count for DATETIME(string_date,zone)");
   try {
      $dt = new DateTime($p[0], new DateTimeZone($p[1]));
      return $dt->getTimestamp();
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in DATETIME: {$e}");
      }
   return false;
   }
   
public function _do_call_BUSINESS_TIME($p, $vm) {
   /** BUSINESS_TIME(datetime_string[, add_week_days]); Return unix time of next week day; Return false or time;  **/
   if (count($p) >2) return $this->_RunTime_Exception("Wrong parameter count for BUSINESS_TIME(string-to-date, add weekdays)");
   try {
      $days = (count($p)==1)?0:$p[1];
      $time = @strtotime($p[0]);
	  while (date('N',$time)>5) $time = strtotime("+1 day",$time);
	  $i = 1;
	  while ($i<$days) {
	     $time = strtotime("+1 day",$time);
		 if (date('N',$time)<6) $i++;
		 }
      return $time;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in BUSINESS_TIME: {$e}");
      }
   return false;
   }

public function _do_call_DAYS_AGO($p, $vm) {
   /** DAYS_AGO(datetime); Return number of days between today and given date; Return false or days;  **/
   if (count($p)<>1) return $this->_RunTime_Exception("Wrong parameter count for DAYS_AGO(adate)");
   try {
      $d1 = new DateTime();
      $d2 = new DateTime($p[0]);
      $int = $d1->diff($d2);
      return $int->days;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in DAYS_AGO: {$e}");
      }
   return false;
   }
   
public function _do_call_HOURS_AGO($p, $vm) {
   /** HOURS_AGO([unix_time]); Number of hours between now and given date; Return false or hours;  **/
   if (count($p)<>1) return $this->_RunTime_Exception("Wrong parameter count for HOURS_AGO(adate)");
   try {
      $d1 = new DateTime();
      $d2 = new DateTime($p[0]);
      $int = $d1->diff($d2);
      return ($int->days * 24) + $int->h;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in HOURS_AGO: {$e}");
      }
   return false;
   }

public function _do_call_DATE($p, $vm) {
   /** DATE(format[,unix_time]); Get value from time (or now) given a format; Return false or string/number;  **/
   if (count($p)>2 || count($p)<1) return $this->_RunTime_Exception("Wrong parameter count for DATE(formatString,[optional time] )");
   try {
      if (is_string($p[0])) {
	     if (count($p)==2) {
			$n = floor($p[1]+0);
 		    if (is_numeric($n)) return @date($p[0], $n);
			else return $this->_RunTime_Exception("Invalid parameters for DATE({$p[0]},{$p[1]} )");
			}
		 return @date($p[0]);
		 }
      return $this->_RunTime_Exception("Invalid parameters for DATE({$p[0]})");
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in DATE: {$e}");
      }
   return false;
   }

public function _do_call_PARSE_DATE($p, $vm) {
   /** PARSE_DATE(datetime_string, target_format, source_format); Form a new format date time; Return false or string;  **/
   if (count($p)<>3) return $this->_RunTime_Exception("Wrong parameter count for PARSE_DATE(date_string, target_format, source_format )");
   try {
      return @date($p[1], strtotime($this->_make_date_from($p[0], $p[2])));
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in PARSE_DATE: {$e}");
      }
   return false;
   }


public function _do_call_EFFECTIVE_DATE() {
   /** EFFECTIVE_DATE(); Return time of running process effective date; Return false or time;  **/
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for EFFECTIVE_DATE()");
   try {
      if (is_null($vm->ref)) return false;
      $a = hda_db::hdadb()->HDA_DB_pendingQ($vm->ref);
      if (!is_null($a) && is_array($a) && count($a)==1) {
         return $a[0]['EffectiveDate'];
         }
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in EFFECTIVE_DATE: {$e}");
      }
   return "Unknown";
   }

// END DATE TIME

//** CATEGORY MATH

public function _do_call_ROUND($p, $vm) {
   /** ROUND(real_num[, precision]); Round to an (optional precision, default 2); Return false or number;  **/
   if (count($p) < 1) return $this->_RunTime_Exception("Wrong parameter count for ROUND(real_num[, precision])");
   try {
      $precision = (count($p)==2)?$p[1]:2;
      $result = sprintf("%.{$precision}f",$p[0]);
      return $result;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in ROUND: {$e}");
      }
   return false;
   }
public function _do_call_ABS($p, $vm) {
   /** ABS(real_num); Get absolute value; Return false or number;  **/
   if (count($p) < 1) return $this->_RunTime_Exception("Wrong parameter count for ABS(real_num)");
   try {
      return ABS($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in ABS: {$e}");
      }
   return false;
   }
public function _do_call_CEIL($p, $vm) {
   /** CEIL(real_num); Round to an upper integer; Return false or number;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for CEIL(real_num)");
   try {
      return ceil($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in CEIL: {$e}");
      }
   return false;
   }
public function _do_call_FLOOR($p, $vm) {
   /** FLOOR(real_num); Round down to an integer; Return false or number;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for FLOOR(real_num)");
   try {
      return floor($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in FLOOR: {$e}");
      }
   return false;
   }
public function _do_call_MAX($p, $vm) {
   /** MAX(this,that); Return max of this or that; Return false or number/string;  **/
   if (count($p) <> 2) return $this->_RunTime_Exception("Wrong parameter count for MAX(this, that)");
   try {
      return max($p[0],$p[1]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in MAX: {$e}");
      }
   return false;
   }
public function _do_call_RAND($p, $vm) {
   /** RAND([min,max]); Get random number between (optionally) min and max values; Return false or number;  **/
   if ((count($p) <> 2) && (count($p) <> 0)) return $this->_RunTime_Exception("Wrong parameter count for RAND([min,max])");
   try {
      return (count($p)==2)?rand($p[0],$p[1]):rand();
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in RAND: {$e}");
      }
   return false;
   }


// END MATH

//** CATEGORY SYSTEM

public function _do_call_VALIDATE($p, $vm) {
   /** VALIDATE(global_validation_name, value); Test a value validates in the given global validation; Return false/true;  **/
   if (count($p) <> 2) return $this->_RunTime_Exception("Wrong parameter count for VALIDATE");
   try {
      $result = HDA_validate($p[0], $p[1], $error);
      if ($result===false) $result = $error;
      return $result;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in VALIDATE: {$e}");
      }
   return false;
   }
   
public function _do_call_VALID_LIST($p, $vm) {
   /** VALID_LIST(global_validation_name); Get a list in global validators; Return false or value;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for VALID_LIST");
   try {
      $result = HDA_whatValidation($p[0], $value_type, $value, $error);
      return ($result)?$value:false;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in VALID_LIST: {$e}");
      }
   return false;
   }

// END VALIDATION

// INTERFACE

private $CONSOLE_log = "";
private function LIB_CONSOLE($msg) {
	$this->CONSOLE_log .= date('G:i:s')."> {$msg}\n";
	}
	
public function _do_call_CONSOLE($p, $vm) {
   /** CONSOLE(string); Write string to console; Return false/true;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for CONSOLE");
   try {
      $this->CONSOLE_log .= date('G:i:s')."> {$p[0]}\n";
      return $p[0];
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in CONSOLE: {$e}");
      }
   return false;
   }
public function _do_call_GET_CONSOLE($p, $vm) {
   /** GET_CONSOLE(); Get current strings written to console; Return false or string;  **/
   if (count($p) > 0) return $this->_RunTime_Exception("Wrong parameter count for GET_CONSOLE");
   try {
      return "{$this->CONSOLE_log}";
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in GET CONSOLE: {$e}");
      }
   return "";
   }
public function _do_call_CLEAR_CONSOLE($p, $vm) {
   /** CLEAR_CONSOLE(); Clear console; Return false or true;  **/
   if (count($p) > 0) return $this->_RunTime_Exception("Wrong parameter count for CLEAR_CONSOLE");
   try {
      $this->clear_console();
      return true;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in CLEAR CONSOLE: {$e}");
      }
   return true;
   }
public function get_console() {
   return $this->CONSOLE_log;
   }
public function clear_console() {
   $this->CONSOLE_log = "";
   }

public function _do_call_DUMP($p, $vm) {
   /** DUMP(var[, return_as_string); Write string to console; Return false or true or dump value;  **/
   if (count($p) > 2) return $this->_RunTime_Exception("Wrong parameter count for DUMP(structure[, return_as_string])");
   try {
      $s = $this->_dump("", $p[0]);
      if ((count($p)==1) || ($p[1]===false)) {
         $this->CONSOLE_log .= "{$s}\n";
         return true;
         }
      else return $s;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in DUMP: {$e}");
      }
   return false;
   }
private function _dump($key, $a) {
   $s = "";
   if (is_array($a)) {
      $s .= "{$key}:\n";
      foreach($a as $k=>$p) {
         $s .= $this->_dump("{$key}.[{$k}]", $p);
         }
      }
   elseif (is_object($a)) {
      $s .= "{$key} ".print_r($a, true)."\n";
      }
   else{
      $s .= "{$key} ";
      if ($a===true) $s .= "true";
      elseif ($a===false) $s .= "false";
      elseif (is_null($a)) $s .= "null";
      else $s .= "{$a}";
      $s .= "\n";
      }
   return $s;
   }
   

public function _do_call_GUID($p, $vm) {
   /** GUID(); Get a unique ident; Return false or guid;  **/
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for GUID()");
   try {
      return str_replace('.','',@uniqid('ID',true));
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in GUID: {$e}");
      }
   return null;
   }
   
public function _do_call_WHOAMI($p, $vm) {
   /** WHOAMI(); Get this session details; Return false or session details structure;  **/
   global $UserName;
   global $HDA_Product_Title;
   $a['UserName'] = $UserName;
   $a['Profile'] = $vm->ref_title();
   $a['Ref'] = $vm->ref;
   $a['Title'] = $HDA_Product_Title;
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for WHOAMI()");
   try {
      $a['Host'] = INIT('HOST');
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in WHOAMI: {$e}");
      }
   return $a;
   }
public function _do_call_PID($p, $vm) {
	return getmypid();
}
public function _do_call_WHATAMI($p, $vm) {
   /** WHATAMI(); Get this config item details; Return false or config details value;  **/
   return INIT($p[0]);
   }
   
public function _do_call_LOCK_THIS($p, $vm) {
   /** LOCK_THIS(); Get an exclusive lock, true or false  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for LOCK_THIS(lock_name)");
   try {
      $fp = fopen("tmp/lock_file_{$p[0]}.txt",'w');
	  if ($fp!==false) {
		  if (flock($fp, LOCK_EX)) {$this->_locks[$p[0]] = $fp; return true; }
		  fclose($fp);
		}
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in LOCK_THIS: {$e}");
      }
   return false;
   }

private $_locks = array();
public function _do_call_UNLOCK_THIS($p, $vm) {
   /** UNLOCK_THIS(); Release exclusive lock, true or false  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for UNLOCK_THIS(lock_name)");
   try {
	   if (array_key_exists($p[0],$this->_locks)) {
		$fp = $this->_locks[$p[0]];
		flock($fp, LOCK_UN);
		fclose($fp);
	   }
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in UNLOCK_THIS: {$e}");
      }
   return false;
   }


public function _do_call_RUNNING_PROCESS($p, $vm) {
   /** RUNNING_PROCESS(); Get title of this running process; Return false or title;  **/
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for RUNNING_PROCESS()");
   try {
      return $vm->ref_title();
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in RUNNING_PROCESS: {$e}");
      }
   return "";
   }
public function _do_call_PROFILE_CATEGORY($p, $vm) {
   /** PROFILE_CATEGORY(); Get category of this running process; Return false or category name;  **/
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for PROFILE_CATEGORY()");
   try {
      if (!is_null($vm->ref)) {
         $a = hda_db::hdadb()->HDA_DB_ReadProfile($vm->ref);
         if (is_array($a)) {
            return $a['Category'];
            }
         }
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in PROFILE_CATEGORY: {$e}");
      }
   return false;
   }
public function _do_call_CATEGORY_PROFILES($p, $vm) {
   /** CATEGORY_PROFILES(category_name); Get process details of all profiles in this category; Return false or category list;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for CATEGORY_PROFILES(category_name)");
   try {
      $a = hda_db::hdadb()->HDA_DB_getProfiles($user=NULL, $category=$p[0]);
	  if (is_array($a) && count($a)>0) {
	     $aa = array();
	     foreach($a as $row) {
		    $aa[] = array('Title'=>$row['Title'], 'Ref'=>$row['ItemId']);
		    }
	     return $aa;
	     }
	  return false;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in CATEGORY_PROFILES: {$e}");
      }
   return false;
   }


public function _do_call_RUNNING_PROCESS_OWNER($p, $vm) {
   /** RUNNING_PROCESS_OWNER(); Get owner details of this running process; Return false or owner details;  **/
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for RUNNING_PROCESS_OWNER()");
   try {
      if (!is_null($vm->ref)) {
         $a = hda_db::hdadb()->HDA_DB_ReadProfile($vm->ref);
         if (is_array($a)) {
            $a = hda_db::hdadb()->HDA_DB_FindUser($a['CreatedBy']);
            if (is_array($a) && count($a)==1)
               return array('Owner'=>$a[0]['UserFullName'],'Email'=>$a[0]['Email']);
            }
         }
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in RUNNING_PROCESS_OWNER: {$e}");
      }
   return array('Owner'=>'Unknown User', 'Email'=>null);
   }


public function _do_call_RUN_REF($p, $vm) {
   /** RUN_REF(); Get ref of this running process; Return false or string ref;  **/
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for RUN_REF()");
   try {
      return $vm->ref;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in RUN_REF: {$e}");
      }
   return NULL;
   }

public function _do_call_TASK_SOURCE($p, $vm) {
   /** TASK_SOURCE(); Get source of this running process; Return false or source string;  **/
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for TASK_SOURCE()");
   try {
      if (is_null($vm->ref)) return false;
      $a = hda_db::hdadb()->HDA_DB_pendingQ($vm->ref);
      if (!is_null($a) && is_array($a) && count($a)==1) {
         $a = hda_db::hdadb()->HDA_DB_ReadTask($a[0]['ItemId']);
         if (!is_null($a)) {
            return $a['Source'];
            }
         }
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in TASK_SOURCE: {$e}");
      }
   return NULL;
   }
public function _do_call_IS_ROLLBACK($p, $vm) {
   /** IS_ROLLBACK(); Test if this process is running a rollback; Return false/true;  **/
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for IS_ROLLBACK()");
   try {
      if (is_null($vm->ref)) return false;
      $a = hda_db::hdadb()->HDA_DB_pendingQ($vm->ref);
      if (!is_null($a) && is_array($a) && count($a)==1) {
         return ($a[0]['Source']=='ROLLBACK');
         }
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in IS_ROLLBACK: {$e}");
      }
   return false;
   
   }
public function _do_call_IS_RETRY($p, $vm) {
   /** IS_RETRY(); Test if this process is running a retry/rerun; Return false/true;  **/
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for IS_RETRY()");
   try {
      if (is_null($vm->ref)) return false;
      $a = hda_db::hdadb()->HDA_DB_pendingQ($vm->ref);
      if (!is_null($a) && is_array($a) && count($a)==1) {
         return ($a[0]['Source']=='RERUN');
         }
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in IS_RETRY: {$e}");
      }
   return false;
   
   }

public function _do_call_TASK_DATE($p, $vm) {
   /** TASK_DATE(); Return the effective date of this process; Return false or time;  **/
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for TASK_DATE()");
   try {
      if (is_null($vm->ref)) return false;
      $a = hda_db::hdadb()->HDA_DB_pendingQ($vm->ref);
      if (!is_null($a) && is_array($a) && count($a)==1) {
         $a = hda_db::hdadb()->HDA_DB_ReadTask($a[0]['ItemId']);
         if (!is_null($a)) {
            return $a['EffectiveDate'];
            }
         }
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in TASK_DATE: {$e}");
      }
   return NULL;
   }

public function _do_call_LAST_RUN($p, $vm) {
   /** LAST_RUN(); Get time of this process last ran; Return false or time;  **/
   if (count($p) > 1) return $this->_RunTime_Exception("Wrong parameter count for LAST_RUN([profile_name])");
   try {
      if (count($p)==1) {
         $ref = hda_db::hdadb()->HDA_DB_lookUpProfile($p[0]);
         }
      else $ref = $vm->ref;
      if (is_null($ref)) return NULL;
      return hda_db::hdadb()->HDA_DB_LastRunOf($ref);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in LAST_RUN: {$e}");
      }
   return NULL;
   }


public function _do_call_NEXT_SCHEDULED($p, $vm) {
   /** NEXT_SCHEDULED(HOUR/DAY [,Number of[, Starting]]); Set the next scheduled run time for this process; Return false or next scheduled time;  **/
   if (count($p) < 1 || count($p) > 3) 
      return $this->_RunTime_Exception("Wrong parameter count for NEXT_SCHEDULED(HOUR|DAY [,Units[,StartDateTime]])");
   try {
      $next = (count($p)>2)?strtotime($p[2]):time();
      $units = (count($p)>1)?$p[1]:1;
      switch (strtoupper($p[0])) {
         case "DAY":
            $next += $units*24*60*60;
            break;
         case "HOUR":
            $next += $units*60*60;
            break;
         default: return $this->_RunTime_Exception("Wrong first parameter for NEXT_SCHEDULED must be DAY or HOUR");
         }
      $next = hda_db::hdadb()->PRO_DB_DateTime($next);
      hda_db::hdadb()->HDA_DB_writeSchedule($vm->ref, $next, strtoupper($p[0]), $units, NULL);
      return $next;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in NEXT_SCHEDULED: {$e}");
      }
   return false;
   }

public function _do_call_RESCHEDULE($p, $vm) {
   /** RESCHEDULE(next_miinutes); Reschedule this process for next_minutes time; Return false or next scheduled time;  **/
   if (count($p)<>1) return $this->_RunTime_Exception("Wrong parameter count for RESCHEDULE(next_minutes)");
   try {
      $on_schedule = hda_db::hdadb()->HDA_DB_getSchedule($vm->ref);
      if (is_array($on_schedule) && count($on_schedule)==1) {
         $units = $on_schedule[0]['Units'];
         $rate = $on_schedule[0]['RepeatInterval'];
         }
      else {
         $rate = "HOUR"; $units = 1;
         }
      $next = time()+($p[0]*60);
      $next = hda_db::hdadb()->PRO_DB_DateTime($next);
      hda_db::hdadb()->HDA_DB_writeSchedule($vm->ref, $next, $rate, $units, NULL);
      return $next;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in RESCHEDULE: {$e}");
      }
   return false;
   }


public function _do_call_DETECT_LOOKUP($p, $vm) {
   /** DETECT_LOOKUP(global_connect_name[, filemask]); Detect if this process is triggered by a file on this connection; Return false or detect structure;  **/
   if (count($p) < 1 || count($p)>2) return $this->_RunTime_Exception("Wrong parameter count for DETECT_LOOKUP(lookup_id [,filemask])");
   try {
      if (is_null($vm->ref)) return false;
      $filemask = (count($p)==2)?$p[1]:null;
      return HDA_detectLookup($p[0], $filemask, $vm->task, $vm->profile);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in DETECT_LOOKUP: {$e}");
      }
   return NULL;
   }

   
//** CATEGORY RSS, SOAP

public function _do_call_DETECT_SOAP($p, $vm) {
   /** DETECT_SOAP(); Detect if this process is triggered by a SOAP message; Return false or soap structure;  **/
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for DETECT_SOAP()");
   try {
      $detected = _getSoapRequest($vm->ref);
      return $detected;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in DETECT_SOAP: {$e}");
      }
   return false;
   }
public function _do_call_DETECT_SOAPXML($p, $vm) {
   /** DETECT_SOAPXML(); Detect if this process is triggered by a SOAP message; Return false or xml;  **/
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for DETECT_SOAPXML()");
   try {
      $detected = _getSoapXML($vm->ref);
      return $detected;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in DETECT_SOAPXML: {$e}");
      }
   return false;
   }
public function _do_call_ISSUE_SOAP($p, $vm) {
   /** ISSUE_SOAP(); Register response to a soap request; Return false/true;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for ISSUE_SOAP(response)");
   try {
      _postSoapResponse($vm->ref, $p[0]);
      return true;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in ISSUE_SOAP: {$e}");
      }
   return false;
   }
public function _do_call_SOAP_THIS($p, $vm) {
   /** SOAP_THIS(to_url, method, args); Post a soap request; Return false/soap response;  **/
   if (count($p)<>3) return $this->_RunTime_Exception("Wrong parameter count for SOAP_THIS(url, method, args)");
   try {
      if (function_exists('HDA_SoapThis')) {
         return HDA_SoapThis($p[0], $p[1], $p[2]);
         }
      return $this->_RunTime_Exception("Fails in SOAP_THIS: No function HDA_SoapThis");
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SOAP_THIS: {$e}");
      }
   return false;
   }
public function _do_call_RAW_SOAP($p, $vm) {
   /** RAW_SOAP(to_url, xml); Post a soap request; Return false/soap response;  **/
   if (count($p)<>2) return $this->_RunTime_Exception("Wrong parameter count for RAW_SOAP(url, xml)");
   try {
	  $soap = array();
	  $soap['url'] = $p[0];
	  $soap['args'] = array();
	  $soap['args']['rawxml'] = $p[1];
	  if (HDA_SendSOAP($soap, $response)) return $response;
	  else
		return $this->_RunTime_Exception("Fails in RAW_SOAP: {$response} {$p[0]} {$p[1]}");
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in RAW_SOAP: {$e}");
      }
   return false;
   }
public function _do_call_POST_SOAP($p, $vm) {
   /** POST_SOAP($p); Post a soap request to $p.url, destination method $p.method, to $p.target; Return false or soap response structure;  **/

   if (array_key_exists('url', $p[0])) $url = $p[0]['url'];
   elseif (array_key_exists('server',$p[0]))
      $url = "http://{$p[0]['server']}/HDAW/HDA_soap_accept.php";
   else
      $url = "http://localhost/HDAW/HDA_soap_accept.php";
   $args = $p[1];
   
   $use_function = $p[0]['method'];
   if (array_key_exists('target', $p[0])) {
      if (strlen($p[0]['target'])==0)
	     $use_function = 'anonymous';
	  else
	     $use_function = $p[0]['target'];
	  $args['_soap_method'] = $p[0]['method'];
	  $a = array();
	  foreach ($args as $prop=>$v) {
	     $a[] = "{$prop}|{$v}";
	     }
	  $args = $a;
	  }
   $output = false;  
   try {
	   $client = new SoapClient(null, array(
		   'location' => $url,
		   'uri'      => $url));
	   $output = $client->__soapCall($use_function,$args);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in POST_SOAP: {$e}");
      }

   return $output;
   }
public function _do_call_UNPACK_SOAP($p, $vm) {
   /** UNPACK_SOAP(); Unpack to structure this SOAP response; Return false or structure;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for UNPACK_SOAP()");
   try {
      return $this->_unpack_soap($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in UNPACK_SOAP: {$e}");
      }
   return false;
   }
private function _unpack_soap($a) {
   if (is_array($a)) {
      $b = array();
	  foreach ($a as $field=>$v) $b[$field] = $this->_unpack_soap($v);
	  return $b;
	  }
   if (is_object($a)) {
      return get_object_vars($a);
      }
   else return $a;
   }
   
public function _do_call_UNPACK_SOAP_FILE($p, $vm) {
   /** UNPACK_SOAP_FILE(string); base64_decode(string); Return false or string;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for UNPACK_SOAP_FILE()");
   try {
      return base64_decode($p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in UNPACK_SOAP_FILE: {$e}");
      }
   return false;
   }  

//** CATEGORY SYSTEM

public function _do_call_POST_TICKET($p, $vm) {
   /** POST_TICKET(ticket, file); Post a ticket request; Return response string;  **/
   $s = _postTicket($p[0], $p[1], $error);
   if ($s===false) return $error;
   return $s;
   }
   
public function _do_call_DETECT_UPLOAD($p, $vm) {
   /** DETECT_UPLOAD(); Detect if this process is triggered by an upload; Return false or upload details structure;  **/
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for DETECT_UPLOAD()");
   try {
      if (is_null($vm->ref)) return $this->_RunTime_Exception("DETECT_UPLOAD: no profile reference");
      if (is_null($vm->task)) return $this->_RunTime_Exception("DETECT_UPLOAD: no task job Q reference");
      $detected = false;
      $a = hda_db::hdadb()->HDA_DB_ReadTask($vm->task);
      if (!is_null($a)) {
	     $detected = array();
         if (!is_null($a['RcvFileName'])) {
            $detected['FileName'] =  $a['RcvFileName'];
            }
         if (!is_null($a['RcvFile']) && file_exists($a['RcvFile'])) {
            $detected['FilePath'] = $a['RcvFile'];
            }
		 if (!is_null($a['SourceInfo'])) $detected['SourceInfo'] = $a['SourceInfo'];
		 if (!array_key_exists('FilePath', $detected)) $detected = false;
         }
      return $detected;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in DETECT_UPLOAD: {$e}");
      }
   return false;
   }
public function _do_call_DETECT_UPDATE($p, $vm) {
   /** DETECT_UPDATE(app_name); Detect if this process is triggered by an update to an application; Return false or data;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for DETECT_UPDATE(app_name)");
   try {
	  $a = hda_db::hdadb()->HDA_DB_apps(null, $p[0]);
	  if (!is_array($a) || (count($a)<>1)) return $this->_RunTime_Exception("Unable to locate app {$p[0]} or not unique in DETECT_UPDATE ".print_r($a,true));
	  $item = $a[0]['ItemId'];
	  $data = hda_db::hdadb()->HDA_DB_appLogItems($item);
      return $data;
	  }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in DETECT_UPDATE: {$e}");
      }
   return false;
   }
   
public function _do_call_DATA_PATH($p, $vm) {
   /** DATA_PATH(); Data path of this upload; Return false or path string;  **/
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for DATA_PATH()");
   try {
      $uploaded = $this->_do_call_DETECT_UPLOAD($p, $vm);
	  if ($uploaded !== false && is_array($uploaded) && array_key_exists('FilePath', $uploaded)) return $uploaded['FilePath'];
	  return null;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in DATA_PATH: {$e}");
      }
   return null;
   }

public function _do_call_DATA_FILENAME($p, $vm) {
   /** DATA_FILENAME(); Filename of this upload; Return false or filename string;  **/
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for DATA_FILENAME()");
   try {
      $uploaded = $this->_do_call_DETECT_UPLOAD($p, $vm);
	  if ($uploaded !== false && is_array($uploaded) && array_key_exists('FileName', $uploaded)) return $uploaded['FileName'];
	  return null;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in DATA_FILENAME: {$e}");
      }
   return null;
   }
   
public function _do_call_DETECT_TRIGGER($p, $vm) {
   /** DETECT_TRIGGER(); Detect if this process was triggered; Return false or trigger details;  **/
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for DETECT_TRIGGER()");
   try {
      if (is_null($vm->ref)) return false;
      if (is_null($vm->task)) return false;
      $detected = false;
      $a = hda_db::hdadb()->HDA_DB_ReadTask($vm->task);
      if (!is_null($a)) {
		  file_put_contents("trigger.txt", print_r($a, true));
	     if ($a['Source']=='TRIGGER') {
            if (!is_null($a['RcvFileName'])) {
               $detected['FileName'] =  $a['RcvFileName'];
               }
            if (!is_null($a['RcvFile']) && file_exists($a['RcvFile'])) {
               $detected['FilePath'] = $a['RcvFile'];
               }
			$detected['TriggerType'] = 'TRIGGER';
			$detected['TriggerByTitle'] = $a['SourceInfo'];
			$detected['TriggerByRef'] = hda_db::hdadb()->HDA_DB_lookUpProfile($a['SourceInfo']);
			}
	     else if ($a['Source']=='SCHEDULED') {
			$detected['TriggerType'] = 'SCHEDULED';
			}
		 else $detected['TriggerType'] = 'MANUAL';
         }
      return $detected;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in DETECT_TRIGGER: {$e}");
      }
   return false;
   }



public function _do_call_DETECT_SOURCE($p, $vm) {
   /** DETECT_SOURCE(); Source of this upload; Return false or source string;  **/
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for DETECT_SOURCE()");
   try {
      if (is_null($vm->ref)) return false;
      $a = hda_db::hdadb()->HDA_DB_pendingQ($vm->ref);
      if (!is_null($a) && is_array($a) && count($a)==1) {
         return $a[0]['Source'];
         }
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in DETECT_SOURCE: {$e}");
      }
   return NULL;
   }

public function _do_call_GET_RUN_Q($p, $vm) {
   if (count($p) > 1) return $this->_RunTime_Exception("Wrong parameter count for GET_RUN_Q([Q Num])");
   try {
      $a = hda_db::hdadb()->HDA_DB_pendingQ(NULL, NULL, $p[0]);
      if (is_null($a)) {
         return $this->_RunTime_Exception("Unable to get run q");
         }
	  return $a;
	  }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in GET_RUN_Q: {$e}");
      }
   return false;
 	}
public function _do_call_BEST_RUN_Q($p, $vm) {
	try {
		$qs = hda_db::hdadb()->HDA_DB_bestQEntry($p[0], $p[1], $vm->ref);
		return $qs;
	}
	catch (Exception $e) {
      $this->_RunTime_Exception("Fails in BEST RUN Q: {$e}");
	  file_put_contents("tmp/best_exception.txt",$e);
	  return 5;
	}
}
public function _do_call_TRIGGER_PROCESS_ONCE($p, $vm) {
   /** TRIGGER_PROCESS_ONCE(profile_name); Trigger process given, if not already in Q; Return false/true;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for TRIGGER_PROCESS_ONCE(profile_name)");
   try {
      $a = hda_db::hdadb()->HDA_DB_ReadProfile(NULL, $p[0]);
      if (is_null($a)) {
         return $this->_RunTime_Exception("Unable to find profile {$p[0]} for a trigger_process");
         }
      $running = hda_db::hdadb()->HDA_DB_inPendingQ($a['ItemId']);
	  if ($running===false) return $this->_do_call_TRIGGER_PROCESS($p, $vm);
	  return $running;
	  }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in TRIGGER_PROCESS_ONCE: {$e}");
      }
   return false;
   }
public function _do_call_VALIDATE_Q_JOB($p, $vm) {
	try {
		$file_in_q = hda_db::hdadb()->HDA_DB_isDuplicateInQ($p[0], $vm->ref);
		if ($file_in_q===false) return true;
		return "File {$p[0]} already in q status {$file_in_q}";
		}
	catch (Exception $e) {
		return $this->_RunTime_Exception("Fails in Validate Q Job: {$e}");
		}
	return true;
	}
public function _do_call_TRIGGER_PROCESS($p, $vm) {
   /** TRIGGER_PROCESS(profile_name[, data_path, use_filename, as_source_info]); Trigger given profile with data, filename and source info; Return false/true;  **/
   global $UserCode;
   if (count($p) < 1) return $this->_RunTime_Exception("Wrong parameter count for TRIGGER_PROCESS(profile_name[, data_path, use_filename, source_info])");
   try {
      $a = hda_db::hdadb()->HDA_DB_ReadProfile(NULL, $p[0]);
      if (is_null($a)) {
         return $this->_RunTime_Exception("Unable to find profile {$p[0]} for a trigger_process");
         }
      $item = $a['ItemId'];
      if (count($p)>2 && !is_null($p[1])) {
         $data_path = $p[1];
         $data_filename = $p[2];
		 clearstatcache();
		 $uploaded_code=HDA_isUnique('UP');
         if (!@file_exists($data_path)) return $this->_RunTime_Exception("Request to trigger profile {$p[0]}, but data file {$p[1]} not found");
		 $path = HDA_TargetForFile($item, $uploaded_code, pathinfo($data_path,PATHINFO_EXTENSION));
         if (!@copy($data_path, $path)) return $this->_RunTime_Exception("Request to trigger profile {$p[0]}, but data file {$p[1]} could not be copied to workspace");
         }
      else { $path = $data_path = $data_filename = NULL; }
	  $source_info = (count($p)>3)?$p[3]:$vm->ref_title();
	  $qLevel = $a['Q'];
	  $qLevel = (count($p)==5 && $p[4]>=0 && $p[4]<11)?$p[4]:$qLevel;
	  if ($qLevel==0) $qLevel=null;
      HDA_ReportTrigger($item, NULL, NULL, "code execute in Q {$qLevel} ".$vm->ref_title());
	  $data_path = str_replace("\\","\\\\",$data_path);
		if (!is_null($code = hda_db::hdadb()->HDA_DB_addRunQ(
					NULL, 
					$item, 
					$UserCode,
					$path, 
					$data_filename, 
					'TRIGGER',
					$source_info,
					hda_db::hdadb()->PRO_DB_dateNow(),
					$qLevel))) {
				// HDA_ReportUpload($item, $code);
				$note = "Added upload of {$data_filename} to pending process queue {$qLevel}";
				hda_db::hdadb()->HDA_DB_issueNote($item, $note, 'TAG_PROGRESS');
				HDA_LogThis(hda_db::hdadb()->HDA_DB_TitleOf($item)." {$note}");
				}
		else return $this->_RunTime_Exception("Fails in TRIGGER_PROCESS: Fails to add to run Q");
      return true;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in TRIGGER_PROCESS: {$e}");
      }
   return false;
   }

public function _do_call_LOOKUP_PROFILE($p, $vm) {
   /** LOOKUP_PROFILE(); Fetch title of profile; Return false or title;  **/
   if (count($p) <> 1) return $this->_RunTime_Exception("Wrong parameter count for LOOKUP_PROFILE(id)");
   try {
	   return hda_db::hdadb()->HDA_DB_TitleOf($p[0]);
   }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in LOOKUP_PROFILE: {$e}");
      }
   return false;
   }
public function _do_call_FETCH_PROFILES($p, $vm) {
   /** FETCH_PROFILES(); Fetch children of profile; Return false or structure list;  **/
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for FETCH_PROFILES()");
   try {
      $a = hda_db::hdadb()->HDA_DB_childrenOf($vm->ref);
      if (!is_null($a)) {
	     $aa = array();
		 foreach ($a as $row) {
		    $passes = ((($row['TActive']&1)==1) && _passesRules($row['LastTask'],$row['Rule'],$row['OnDefault'],$row['DataDays']));
		    $aa[] = array('ProfileName'=>$row['Title'], 'Ready'=>$passes, 'LastRun'=>$row['LastTask']);
		    }
         return $aa;
         }
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in FETCH_PROFILES: {$e}");
      }
   return false;
   }
public function _do_call_READY_TO_RUN($p, $vm) {
   /** READY_TO_RUN(); Get Q status of children of this profile; Return false or details structure;  **/
   if (count($p) <> 0) return $this->_RunTime_Exception("Wrong parameter count for READY_TO_RUN()");
   try {
      $a = hda_db::hdadb()->HDA_DB_childrenOf($vm->ref);
      if (!is_null($a)) {
	     $passes = true;
		 foreach ($a as $row) {
		    $passes &= (($row['TActive']==1) && _passesRules($row['LastTask'],$row['Rule'],$row['OnDefault'],$row['DataDays']));
		    }
         return $passes;
         }
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in READY_TO_RUN: {$e}");
      }
   return false;
   }
public function _do_call_MARKER_VALUE($p, $vm) {
   /** MARKER_VALUE(ident_key[, value[, expire_days]]); Get or set marker; Return false or marker value string;  **/
   if (count($p) < 1 || count($p)>3) return $this->_RunTime_Exception("Wrong parameter count for marker_value(id[, value[, expires_days])");
   try {
      if (count($p)==1) return hda_db::hdadb()->HDA_DB_readMarker($p[0]);
	  else {
	     $expires = (count($p)==3)?$p[2]:0;
	     return hda_db::hdadb()->HDA_DB_writeMarker($p[0], $p[1], $expires, $vm->ref);
		 }
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in MARKER_VALUE: {$e}");
      }
   return false;
   }


public function _do_call_LOG_THIS($p, $vm) {
   /** LOG_THIS(message); Write message to common log; Return false/true;  **/
   if (count($p)<>1) return $this->_RunTime_Exception("Wrong parameter count for LOG_THIS(message)");
   try {
      if (function_exists('HDA_LogThis')) {
         return call_user_func('HDA_LogThis', $p[0], "USERCODE");
         }
      return false;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in LOG_THIS: {$e}");
      }
   return false;
   }
   

public function _do_call_EMAIL_THIS($p, $vm) {
   /** EMAIL_THIS(mail_to, subject, message[, attach_file_path]); Write message to common log; Return false/true;  **/
   if (count($p)<3) return $this->_RunTime_Exception("Wrong parameter count for EMAIL_THIS(mailto, subject, message)");
   try {
      if (function_exists('HDA_EmailThis')) {
         $p[0] = str_replace("\\","",$p[0]);
		 if (count($p)==5) {
			 $attach = $p[4];
		 }
		 else if (count($p)==4) {
			if (is_array($p[3])) {
				$attach = array();
				foreach ($p[3] as $f) {
					if (!@file_exists($f)) {
						$this->_resolve_working_dir($vm);
						$f = "{$this->WORKING_DIR}/{$f}";
						if (@file_exists($f)) $attach[] = $f;
						}
					else $attach[] = $f;
					$this->LIB_CONSOLE("Attach {$f}");
					}
				if (count($attach)==0) $attach = null;
				}
		    else if (!@file_exists($attach = $p[3])) {
               $this->_resolve_working_dir($vm);
               $attach = "{$this->WORKING_DIR}/{$p[3]}";
			   if (!@file_exists($attach)) $attach = null;
			   }
		    }
		 else $attach = null;
		 $to_list = (is_array($p[0]))?$p[0]:array($p[0]);
         call_user_func('HDA_EmailThis', $p[1], $p[2], 'ALCProcessCode', NULL, $to_list, true, $attach);
         return true;
         }
      return false;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in EMAIL_THIS: {$e}");
      }
   return false;
   }
   
public function _do_call_ISSUE_AUDIT($p, $vm) {
   /** ISSUE_AUDIT(message[, filename[, table[,record_count]]]); Write message to audit log; Return false/true;  **/
   if (count($p)<1) return $this->_RunTime_Exception("Wrong parameter count for ISSUE_AUDIT(message[, file[, table[,rcount]]])");
   try {
      $p[0] = (!is_string($p[0]))?"":$p[0];
	  $p[1] = (count($p)>1 && is_string($p[1]))?$p[1]:null;
	  $p[2] = (count($p)>2 && is_string($p[2]))?$p[2]:null;
	  $p[3] = (count($p)>3 && is_numeric($p[3]))?$p[3]:0;
	  hda_db::hdadb()->HDA_DB_audit($vm->ref, $vm->task, null, $p[1], null, $p[2], $p[3], $p[0]);
      return false;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in ISSUE_AUDIT: {$e}");
      }
   return false;
   }
   
public function _do_call_ISSUE_ALERT($p, $vm) {
   /** ISSUE_ALERT(message); Write message to alert/sms log; Return false/true;  **/
   if (count($p)<>1) return $this->_RunTime_Exception("Wrong parameter count for ISSUE_ALERT(message)");
   try {
      if (function_exists('HDA_ProfileAlert')) return HDA_ProfileAlert($vm->ref, $p[0]);
      return false;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in ISSUE_ALERT: {$e}");
      }
   return false;
   }
   
public function _do_call_ISSUE_REPORT($p, $vm) {
   /** ISSUE_REPORT(record_count); Write last record process count to timings db to common log; Return false/true;  **/
   if (count($p)<>1) return $this->_RunTime_Exception("Wrong parameter count for ISSUE_REPORT(record_count)");
   try {
      return hda_db::hdadb()->HDA_DB_timings($vm->ref, null, $p[0]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in ISSUE_REPORT: {$e}");
      }
   return false;
   }
public function _do_call_ISSUE_EVENT($p, $vm) {
   /** ISSUE_EVENT(event_code, event_value); Write event key/value pair to event register log; Return false/true;  **/
   if (count($p)<>2) return $this->_RunTime_Exception("Wrong parameter count for ISSUE_EVENT(event_code, event_value)");
   try {
      return hda_db::hdadb()->HDA_DB_events($vm->ref, $p[0], $p[1]);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in ISSUE_EVENT: {$e}");
      }
   return false;
   }
public function _do_call_GET_EVENTS($p, $vm) {
   /** GET_EVENTS(event_code[, today,ago[,for_profile]]); Get events for optional profile (today or up to days ago); Return false or event structure list;  **/
   if (count($p)<1) return $this->_RunTime_Exception("Wrong parameter count for GET_EVENTS(event_code [,today,ago[, profile_name])");
   try {
      $profile_name = $vm->ref;
      $item = (count($p)==4)?hda_db::hdadb()->HDA_DB_lookUpProfile($profile_name = $p[3]):$vm->ref; 
	  if (is_null($item)) return $this->_RunTime_Exception("Error locating profile name {$profile_name} in GET_EVENTS");
      return hda_db::hdadb()->HDA_DB_events($item, $p[0], NULL, (count($p)>1)?$p[1]:null, (count($p)>2)?$p[2]:1);
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in GET_EVENTS: {$e}");
      }
   return false;
   }
public function _do_call_GET_SUCCESS_EVENT($p, $vm) {
   /** GET_SUCCESS_EVENT([profile_name]); Get last success event for optional profile name or this; Return false or event structure details;  **/
   if (count($p)>1) return $this->_RunTime_Exception("Wrong parameter count for GET_SUCCESS_EVENT([profile_name])");
   try {
      $profile_name = $vm->ref;
      $item = (count($p)==1)?hda_db::hdadb()->HDA_DB_lookUpProfile($profile_name = $p[0]):$vm->ref; 
	  if (is_null($item)) return $this->_RunTime_Exception("Error locating profile name {$profile_name} in GET_SUCCESS_EVENT");
      return hda_db::hdadb()->HDA_DB_events($item, "{$item}_SUCCESS");
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in GET_EVENTS: {$e}");
      }
   return false;
   }
public function _do_call_SYS_EVENT_STATUS($p, $vm) {
   /** SYS_EVENT_STATUS(TODAY/YESTERDAY[, mask]); Get the system events; Return false or details structure;  **/
   if (count($p)<1) return $this->_RunTime_Exception("Wrong parameter count for SYS_EVENT_STATUS(today or yesterday [, event_mask])");
   try {
      hda_db::hdadb()->HDA_DB_events($vm->ref, "{$vm->ref}_SUCCESS", "_SYS_SUCCESS_MARK_");
	  switch ($p[0] = strtoupper($p[0])) {
	     default: $p[0] = 'TODAY';
		 case 'TODAY':
		 case 'YESTERDAY':
		 }
	  $mask = (count($p)==2)?$p[1]:null;
      $a = hda_db::hdadb()->HDA_DB_eventSummary($p[0],$days_ago=0,$mask);
	  $aa = array();
	  if (is_array($a)) {
	     foreach($a as $row) if (!HDA_CheckRules($row['ItemId'],$log)) {
		    switch ($row['EventValue']) {
               case '':
  			      $aa[] = array('Profile'=>$row['Title'], 'Status'=>'Not Run'); break;
		       default:
		          $aa[] = array('Profile'=>$row['Title'], 'Status'=>"{$row['EventCode']} {$row['EventValue']}"); break;
			   }
		    }
		 return $aa;
	     }
	  return false;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in SYS_EVENT_SUMMARY: {$e}");
      }
   return false;
   }

public function _do_call_LOCAL_LOG($p, $vm) {
   return true; // switched off
   if (count($p)<>1) return $this->_RunTime_Exception("Wrong parameter count for LOCAL_LOG(msg)");
   try {
      $this->_resolve_working_dir($vm);
      $f = "{$this->WORKING_DIR}/run_log_{$vm->task}.log";
	  $s = (@file_exists($f))?@file_get_contents($f):"";
	  $s .= hda_db::hdadb()->PRO_DB_dateNow().": {$p[0]}\n";
      @file_put_contents($f, $s);
      return true;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in LOCAL LOG: {$e}");
      }
   return false;
   }

   
public function _do_call_ISSUE_MONITOR($p, $vm) {
   /** ISSUE_MONITOR(message); Write message to monitor log and keep process alive; Return false/true;  **/
   if (count($p)<>1) return $this->_RunTime_Exception("Wrong parameter count for ISSUE_MONITOR(msg)");
   try {
      hda_db::hdadb()->HDA_DB_putMonitorMessage($p[0], $vm->on_debug_line);
      return true;
      }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in ISSUE_MONITOR: {$e}");
      }
   return false;
   }


public function _do_call_KEEP_ALIVE($p, $vm) {
   /** KEEP_ALIVE(); Keep process alive (say to monitor still running); Return false/true;  **/
   set_time_limit(0);
   hda_db::hdadb()->HDA_DB_putMonitorMessage("Pulsed keep alive", $vm->on_debug_line);
   hda_db::hdadb()->HDA_DB_KeepLock();
   return true;
   }
   
public function _do_call_SLEEP($p, $vm) {
   /** SLEEP(seconds); Sleep for seconds; Return false/true;  **/
   sleep($p[0]);
   return true;
   }

public function _do_call_FORCE_ERROR($p, $vm) {
   $this->no_method();
   return true;
   }
   
public function _do_call_TRIGGER_ERROR($p, $vm) {
   $error = (count($p)==1)?$p[0]:"Triggered Error";
   trigger_error($error);
   return true;
   }
   
public function _do_call_RELEASE_MEMORY($p, $vm) {
	gc_enable(); gc_collect_cycles() ; $mem = array(memory_get_peak_usage(), memory_get_usage());
	return $mem;
}
   


//** CATEGORY UPDATES
public function _do_call_HYDRA_RAW_ROWS($p, $vm) {
   if (count($p)<>1) return $this->_RunTime_Exception("Wrong parameter count for HYDRA_RAW_ROWS(hydra_table)");
   try {
      $e = hda_db::hdadb()->HDA_DB_AllRawRows($p[0]);
      if ($e===false) return $this->_RunTime_Exception("Fails to get table data from {$p[0]}");
	  return $e;
	  }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in HYDRA_RAW_ROWS: {$e}");
      }
   return false;
}
public function _do_call_HYDRA_WRITE_SQL($p, $vm) {
   if (count($p)<>1) return $this->_RunTime_Exception("Wrong parameter count for HYDRA_WRITE_SQL($query)");
   try {
		$src_def['Host'] = INIT('SQL_HOST');
		$src_def['Schema'] = INIT('SQL_CATALOG');
		$src_def['User'] = INIT('SQL_USER');
		$src_def['PassW'] = INIT('SQL_USER_PW');
		$connection_info = array();
		$connection_info['ReturnDatesAsStrings'] = true;
		$connection_info['TransactionIsolation'] = SQLSRV_TXN_READ_UNCOMMITTED;
		if (array_key_exists('Schema',$src_def) && !is_null($src_def['Schema']) && strlen($src_def['Schema'])>0) 
		$connection_info['Database'] = $src_def['Schema'];
		if (strlen($src_def['User'])>0) { $connection_info['UID']=$src_def['User']; $connection_info['PWD']=$src_def['PassW']; }
		$SQL_QUERY_CONN = @sqlsrv_connect($src_def['Host'], $connection_info);
		if ($SQL_QUERY_CONN===false) {
			return $this->_RunTime_Exception($SQL_QUERY_ERROR = "Fails to connect to ms sql source ".print_r(@sqlsrv_errors(),true));
		}
		@sqlsrv_configure("WarningsReturnAsErrors",0);
		@sqlsrv_configure("ReturnDatesAsStrings",true);
		@sqlsrv_configure("WarningsReturnAsErrors",0);
		$SQL_QUERY_RESULT = @sqlsrv_query($SQL_QUERY_CONN, $p[0], null, array('Scrollable'=>SQLSRV_CURSOR_STATIC,'QueryTimeout' => 60000));
		if ($SQL_QUERY_RESULT===false) {
			$SQL_QUERY_ERROR = "MSSQL Query Fail: ".print_r(sqlsrv_errors(),true);
			return $this->_RunTime_Exception("Query fails to MS SQL {$p[0]} error {$SQL_QUERY_ERROR}");
			}
		@sqlsrv_close($SQL_QUERY_CONN);
		return true;
	  }
   catch (Exception $e) {
      return $this->_RunTime_Exception("Fails in HDRA_WRITE_SQL: {$e}");
      }
   return false;
}

}



?>