<?php
// Hydra core - SQL Server version

function hdadb() {
   global $HDA_DB;
   return $HDA_DB;
   }

class HDA_MSSQL {
	public function __destruct() {
	}
	public function __construct() {
		$this->connect();
	}
	public $lastError = null;
	public function _RunTime_Exception($message) {
		$this->lastError = $message;
		return false;
	}
	public $SQL_QUERY_ERROR = null;
	public $SQL_QUERY_CONN = false;
	public $SQL_QUERY_RESULT = null;
	public function SQL_LAST_ERROR() {
	/** SQL_LAST_ERROR(); Return last sql error; Return error string;  **/
		return $this->SQL_QUERY_ERROR;
	}
	public function LastError() {
		return $this->SQL_LAST_ERROR()." ".$this->lastError;
	}
	public function reconnect_sql() {
		return true;
	}	
	public function _connect($src_def) {
		$connection_info = array();
		$connection_info['ReturnDatesAsStrings'] = true;
		$connection_info['TransactionIsolation'] = SQLSRV_TXN_READ_UNCOMMITTED;
		if (array_key_exists('Schema',$src_def) && !is_null($src_def['Schema']) && strlen($src_def['Schema'])>0) 
		$connection_info['Database'] = $src_def['Schema'];
		if (strlen($src_def['User'])>0) { $connection_info['UID']=$src_def['User']; $connection_info['PWD']=$src_def['PassW']; }
		$this->SQL_QUERY_CONN = @sqlsrv_connect($src_def['Host'], $connection_info);
		if ($this->SQL_QUERY_CONN===false) {
			return $this->_RunTime_Exception($this->SQL_QUERY_ERROR = "Fails to connect to ms sql source ".print_r(@sqlsrv_errors(),true));
		}
		@sqlsrv_configure("WarningsReturnAsErrors",0);
		@sqlsrv_configure("ReturnDatesAsStrings",true);
		return true;
	}
	
	public function close() {
		if ($this->SQL_QUERY_CONN===false) return $this->_RunTime_Exception("Connection failed to open");
		if (is_resource($this->SQL_QUERY_RESULT)) @sqlsrv_free_stmt($this->SQL_QUERY_RESULT);
		@sqlsrv_close($this->SQL_QUERY_CONN);
		$this->SQL_QUERY_RESULT = false;
		$this->SQL_QUERY_CONN = false;
		return true; 
	}	
	
	public function _query($q) {
		if ($this->SQL_QUERY_CONN===false) return $this->_RunTime_Exception("Connection failed to open");
		try {
			@sqlsrv_configure("WarningsReturnAsErrors",0);
			if (is_resource($this->SQL_QUERY_RESULT)) @sqlsrv_free_stmt($this->SQL_QUERY_RESULT);
			$this->SQL_QUERY_RESULT = @sqlsrv_query($this->SQL_QUERY_CONN, $q, null, array('Scrollable'=>SQLSRV_CURSOR_STATIC,'QueryTimeout'=>600));
			if ($this->SQL_QUERY_RESULT===false) {
				$this->SQL_QUERY_ERROR = "MSSQL Query Fail: ".print_r(sqlsrv_errors(),true);
				return $this->_RunTime_Exception("Query fails to MS SQL {$q} error {$this->SQL_QUERY_ERROR}");
				}
			}
		catch (Exception $e) {
			$this->SQL_QUERY_ERROR = "Fails query ".print_r(sqlsrv_errors(),true);
			return $this->_RunTime_Exception("Query fails to MS SQL {$q} error {$e}");
			}
		return true;
	}	

	public function _fetch_row() {
		if ($this->SQL_QUERY_CONN===false) return $this->_RunTime_Exception("Connection failed to open");
		if ($this->SQL_QUERY_RESULT===false) return $this->_RunTime_Exception("Query failed to execute");
		try {
			$a = @sqlsrv_fetch_array($this->SQL_QUERY_RESULT, SQLSRV_FETCH_ASSOC);
			if (is_null($a) || $a===false) return false;
			if (is_array($a)) foreach ($a as $k=>$p) $a[$k]=trim($p);
		}
		catch (Exception $e) {
			$this->_RunTime_Exception("Fetch Row error {$e}");
		}
		return $a;
	}
	
	public function _fetch_all() {
		$a = array();
		while (($r=$this->_fetch_row()) !== false) {
			$a[] = $r;
		}
		return $a;
	}
	
	public function _goodSQLstring($v) {
	  if (is_string($v)) $v = str_replace("'","''",$v);
	  return $v;
	  }
	
	public function _limit($n, &$top, &$limit) {
		$top = "TOP ({$n})"; $limit = "";
		return $top;
	}	
	
}

class HDA_DB extends HDA_MSSQL {
	
	public $DB_VERSION = 1;
	
	public static function hdadb() {
		global $HDA_DB;
		return $HDA_DB;
	}
	
	
	public function connect() {
		$src_def['Host'] = INIT('SQL_HOST');
		$src_def['Schema'] = INIT('SQL_CATALOG');
		$src_def['User'] = INIT('SQL_USER');
		$src_def['PassW'] = INIT('SQL_USER_PW');
		return $this->_connect($src_def);
	}
	

public function HDA_DB_CHANGES() {
   $seen = PRO_ReadParam('DONE_DB_VERSION');
   if (!is_null($seen)) return true;
   PRO_AddToParams('DONE_DB_VERSION',$this->DB_VERSION);

   $query = "SELECT MAX(Version) as [m] FROM hda_db ";
   if ($result=$this->_query($query)) {
      if ($row=$this->_fetch_row()) {
         if ($row['m']==$this->DB_VERSION) return true;
         }
      }
// DO UPDATE

// END UPDATE
   $now = $this->PRO_DB_dateNow();
   $query = "INSERT INTO hda_db (Version,IssuedDate) VALUES({$this->DB_VERSION},'{$now}') ";
   if ($result=$this->_query($query)) return true;
   HDA_SendErrorMail("Updating DB Version {$this->DB_VERSION} ".print_r(sqlsrv_errors(),true)." {$query} ");
   return false;
   }

public function PRO_DB_stampTime() {
   return date('Y_m_d_G_i_s',time());
   }

public function PRO_DB_dateNow() {
   return date('Y-m-d G:i:s');
   }

public function PRO_DB_Date($time) {
   if (!isset($time) || is_null($time) || ($time==0)) return NULL;
   $time = intVal($time); 
   return date('Y-m-d 00:00:00',$time);
   }

public function PRO_DB_DateTime($time) {
   if (!isset($time) || is_null($time) || $time==0) return NULL;
   $time = intVal($time); 
   return date('Y-m-d G:i:s',$time);
   }


public function PRO_DBdate_Styledate($date, $at=false) {
   $time = strtotime($date);
   if (!isset($date) || is_null($date) || ($time==0)) return "No Date Set";
   return $this->PRO_DBtime_Styledate(strtotime($date), $at);
   }

public function PRO_DBdate_Styletime($date) {
   return $this->PRO_DBtime_Styletime(strtotime($date));
   }
public function PRO_DBtime_Styletime($time, $secs="") {
   return date("G:i{$secs}", $time);
   }
public function PRO_DBdate_IsToday($date) {
   return $this->PRO_DB_Date(strtotime($date))==$this->PRO_DB_Date(time());
   }

public function PRO_DBtime_Styledate($time, $at=false) {
   if (!isset($time) || is_null($time) || ($time==0)) return "No date set";
   $time = intVal($time); 
   if ($at) $t = " - G:i"; else $t = "";
   $t = date("D, jS M y".$t, $time);
   return str_replace('-',"at",$t);
   }


public function HDA_DB_serialize($p) {
   if (is_null($p) || !is_array($p)) $p = array();
   $p = serialize($p);
   $p = trim($p);
   return $p;
   }

public function HDA_DB_unserialize($p) {
   if (is_null($p) || strlen($p)==0) return array();
   $p = str_replace("'","\"",$p);
   return unserialize($p);
   }

public function HDA_DB_unxml($p, $default=null) {
   if (is_null($p) || strlen($p)==0) return (is_null($default))?array():$default;
   $s = $this->HDA_DB_textFromDB($p);
   return xml2ary($s); 
   }

public function HDA_DB_toxml($a, $default=null) {
   if (is_null($a) || !is_array($a) || count($a)==0) $a = (is_null($default))?array():$default;
   return $this->HDA_DB_textToDB(ary2xml($a));
   }  

public function HDA_DB_textToDB($t) {
   if (is_null($t)) return "";
   return urlencode($t);
   }
public function HDA_DB_textFromDB($t) {
   if (is_null($t)) return "";
   return urldecode($t);
   }

public function HDA_DB_AllRawRows($table) {
	$query = "SELECT * FROM {$table}";
   if ($result = $this->_query($query)) {
	  return $this->_fetch_all();
      }
   HDA_SendErrorMail("Fetch All Rows ".print_r(sqlsrv_errors(),true)." {$query} ");
   return false;
}
	
	//LOCKS
	
	public function HDA_DB_TakeLock($lock='SY', $timeout=6000) {
		$lock_id = "{$lock}";
		$this_sess = session_id();
		$query = "SELECT * FROM hda_lock WHERE Lockid='{$lock_id}'  ";
		if (!is_null($timeout)) $query .= " AND (DATEDIFF(s,LockDate,GetDate())<{$timeout})";
		if ($result = $this->_query($query)) {
			if ($row=$this->_fetch_row()) {return false;}
		}
		$query = "INSERT INTO hda_lock (LockId,LockDate,SessionId) VALUES('{$lock_id}',GetDate(),'{$this_sess}') ";
		if ($result = $this->_query($query)) {
			$query = "SELECT * FROM hda_lock WHERE Lockid='{$lock_id}' AND SessionId='{$this_sess}'  ";
			if ($result = $this->_query($query)) {
				if ($row=$this->_fetch_row()) {return true;}
			}
		}
		else {$this->LastError(); }
		return false;
	}
	
	public function HDA_DB_DropAllLocks() {
		$query = "DELETE FROM hda_lock";
		$this->_query($query);
		$rows = sqlsrv_rows_affected($this->SQL_QUERY_RESULT);
		return ($rows===false)?0:$rows;
	}
	public function HDA_DB_DropLock($lock='SY', $force = null) {
		$query = "SELECT * FROM hda_lock WHERE  LockId IS NOT NULL ";
		if (!is_null($lock)) $query .= " AND LockId='{$lock}' ";
		if (is_null($force)) $query .= " AND SessionId='".session_id()."' ";
		else $query .= " AND SessionId='{$force}' ";
		$this->_query($query);
		$row = $this->_fetch_row();
		if (($row===false) || (count($row)==0)) return true;
		$query = "DELETE FROM hda_lock WHERE  LockId IS NOT NULL ";
		if (!is_null($lock)) $query .= " AND LockId='{$lock}' ";
		if (is_null($force)) $query .= " AND SessionId='".session_id()."' ";
		else $query .= " AND SessionId='{$force}' ";
		$this->_query($query);
		return true;
	}
	public function HDA_DB_TestLock($lock, $timeout=null) {
		$query = "SELECT SessionId FROM hda_lock WHERE LockId='{$lock}' ";
		if (!is_null($timeout)) $query .= " AND (TO_SECONDS('".$this->PRO_DB_dateNow()."')-TO_SECONDS(LockDate))>{$timeout}";
		if ($result = $this->_query($query)) {
			if ($row=$this->_fetch_row()) return $row['SessionId'];
			return null;
			}
		HDA_SendErrorMail("Test Lock ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}
	public function HDA_DB_HoldingLock($lock) {
		$query = "UPDATE hda_lock SET LockDate=GetDate() WHERE LockId='{$lock}' AND SessionId='".session_id()."'";
		if ($result = $this->_query($query)) {
			$query = "SELECT * FROM hda_lock WHERE LockId='{$lock}' AND SessionId='".session_id()."'  ";
			if ($result = $this->_query($query)) {
				$row=$this->_fetch_row();
				if (is_array($row) && count($row)>0) return true;
				}
			return false;
			}
		//HDA_SendErrorMail("Holding Lock ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;   
	}	
	
	
	//END LOCKS
	
	// CRON
	
	public function HDA_DB_cronIn($msg) {
		$this->HDA_DB_cronTidy();
		$query = "INSERT INTO hda_cron (SessionId, InDate, OutDate, LogText) VALUES ('".session_id()."','".$this->PRO_DB_dateNow()."',NULL,'".$this->HDA_DB_textToDB($msg)."')";
		if ($result=$this->_query($query)) return true;
		HDA_SendErrorMail("Cron IN ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}
	public function HDA_DB_cronOut($msg, $sessid=null) {
		$sessid = (is_null($sessid))?session_id():$sessid;
		$query = "UPDATE hda_cron SET OutDate='".$this->PRO_DB_dateNow()."',LogText=SUBSTRING(CONCAT(LogText,'".$this->HDA_DB_textToDB($msg)."'),1,8000) ";
		$query .= " WHERE SessionId='{$sessid}' ";
		if ($result=$this->_query($query)) return true;
		HDA_SendErrorMail("Cron OUT ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}
	public function HDA_DB_cronUpdate($msg) {
		$query = "UPDATE hda_cron SET LogText=SUBSTRING(CONCAT(LogText,'".$this->HDA_DB_textToDB($msg)."'),1,8000) ";
		$query .= " WHERE SessionId='".session_id()."' ";
		if ($result=$this->_query($query)) return $msg;
		HDA_SendErrorMail("Cron UPDATE ".print_r(sqlsrv_errors(),true)." {$query} ");
		return $msg;
	}
	public function HDA_DB_cronTidy($ago=20) {
		$since = $this->PRO_DB_DateTime(strtotime("{$ago} minutes ago"));
		$since_in = $this->PRO_DB_DateTime(strtotime("1 days ago"));
		$query = "DELETE FROM hda_cron WHERE (OutDate<'{$since}') OR (InDate<'{$since_in}') ";
		if ($result=$this->_query($query)) return true;
		HDA_SendErrorMail("Cron TIDY ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}   
	public function HDA_DB_cronLog() {
		$query = "SELECT * FROM hda_cron ORDER BY OutDate ASC";
		if ($result = $this->_query($query)) {
			$a = array();
			$aa = $this->_fetch_all();
			foreach ($aa as $row) {
				$row['LogText'] = str_replace('|',"<br>",$this->HDA_DB_textFromDB($row['LogText']));
				$a[] = $row;
				}
			return $a;
			}
		HDA_SendErrorMail("Cron Get Log ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}   

	
	//END CRON
	
	// Q - Tasks like email sending
	
	public function HDA_DB_actionToQ($user, $toQ, $data) {
		if (is_null($data)) return false;
		$item = $this->HDA_isUnique('AQ');
		$query = "INSERT INTO hda_q (UserItem,Method,ItemId,Data) VALUES('{$user}','{$toQ}', '{$item}', ";
		$query .= "'"; $query .= $this->HDA_DB_textToDB($this->HDA_DB_serialize($data)); $query .= "')";
		if ($result = $this->_query($query)) {
			return true;
			}
		HDA_SendErrorMail("Adding to Q ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}

	public function HDA_DB_actionFromQ($byMethod=NULL) {
		$query = "SELECT * FROM hda_q ";
		if (!is_null($byMethod)) {
			$query .= "WHERE ";
			$query .= "Method='{$byMethod}' ";
			}
		if ($result = $this->_query($query)) {
			$a = array();
			$aa = $this->_fetch_all();
			foreach ($aa as $row) {
				$row['Data'] = $this->HDA_DB_unserialize($this->HDA_DB_textFromDB($row['Data']));
				$a[] = $row;
				}
			return $a;
			}
		HDA_SendErrorMail("Fetch Q ".print_r(sqlsrv_errors(),true)." {$query} ");
		return NULL;
	}

	public function HDA_DB_removeQ($item) {
		$query = "DELETE FROM hda_q WHERE ItemId='{$item}' ";
		if ($result = $this->_query($query)) {
			return true;
			}
		HDA_SendErrorMail("Remove Q Item ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}

	// END Q
	// USERS
	
	public function HDA_DB_FindUser($user, $by_name=null) {
		$query = "SELECT * FROM hda_users";
		if (!is_null($user)) $query .= " WHERE ((LOWER(Email)=LOWER('{$user}')) OR (UserItem='{$user}'))";
		elseif (!is_null($by_name)) $query .= " WHERE UserFullName='{$by_name}' ";
		if ($result = $this->_query($query)) {
			$a = array();
			if ($row = $this->_fetch_row()) {
				$row['Allow'] =  (!is_null($row['Allow']) && strlen($row['Allow'])>0)?$this->HDA_DB_unserialize($row['Allow']):array();
				$row['Profiles'] =  (!is_null($row['Profiles']) && strlen($row['Profiles'])>0)?$this->HDA_DB_unserialize($row['Profiles']):array();
				$a[] = $row;
				}
			return $a;
			}
		HDA_SendErrorMail( "Find User ".print_r(sqlsrv_errors(),true)." ".$query); 
		return NULL;
	}

	public function HDA_DB_AllUsers() {
		$query = "SELECT * FROM hda_users ";
		if ($result = $this->_query($query)) {
			$a = array();
			while ($row = $this->_fetch_row()) {
				$row['Allow'] =  (!is_null($row['Allow']) && strlen($row['Allow'])>0)?$this->HDA_DB_unserialize($row['Allow']):array();
				$row['Profiles'] =  (!is_null($row['Profiles']) && strlen($row['Profiles'])>0)?$this->HDA_DB_unserialize($row['Profiles']):array();
				$a[] = $row;
				}
			return $a;
			}
		HDA_SendErrorMail("Fetch all users ".print_r(sqlsrv_errors(),true)." {$query} ");
		return NULL;
	}
	public function HDA_DB_RegisterUser($email, $uname, $pw, $allow=NULL) {
		if ($this->HDA_DB_FindUser($email)) return false;
		$query = "INSERT INTO hda_users (UserItem,Email,UserFullName,PW,Allow,Profiles,CreateDate,UpdateDate,LastLoginDate) ";
		$code = $this->HDA_isUnique('U');
		$query .= " VALUES (";
		$query .= "'{$code}',";
		$query .= "'{$email}',";
		$query .= "'{$uname}',";
		$query .= "'".password_hash($pw, PASSWORD_DEFAULT)."',";
		$query .= (!is_null($allow))?"'".$this->HDA_DB_serialize($allow)."',":"NULL,";
		$query .= "NULL,";
		$query .= "GetDate(),";
		$query .= "GetDate(),";
		$query .= "GetDate() ) ";
		if ($result=$this->_query($query)) {
			return true;
			}
		HDA_SendErrorMail("Register New User".print_r(sqlsrv_errors(),true)." {$query}");
		return false;
	}  
	public function HDA_DB_InsertUser($email, $uname, $pw, $allow=NULL) {
		if ($this->HDA_DB_FindUser($email)) return false;
		$query = "INSERT INTO hda_users (UserItem, Email,UserFullName,PW,Allow,Profiles,CreateDate,UpdateDate,LastLoginDate) ";
		$code = $this->HDA_isUnique('U');
		$query .= " VALUES( ";
		$query .= "'{$code}',";
		$query .= "'{$email}',";
		$query .= "'{$uname}',";
		$query .= "'{$pw}',";
		$query .= (!is_null($allow))?"'".$this->HDA_DB_serialize($allow)."',":"NULL,";
		$query .= "NULL,";
		$query .= "GetDate(),";
		$query .= "GetDate(),";
		$query .= "GetDate() ) ";
		if ($result=$this->_query($query)) {
			return true;
			}
		HDA_SendErrorMail("Insert Imported New User".print_r(sqlsrv_errors(),true)." {$query}");
		return false;
	}  

	public function HDA_DB_Bootstrap() {
		$a = $this->HDA_DB_AllUsers();
		if (isset($a) && is_array($a) && count($a)==0) {
			return $this->HDA_DB_RegisterUser('tim_s_jones@hotmail.com','Tim Jones','Gentia', array('ADMIN'=>1));
			}
		return false;
	}


	public function HDA_DB_Register($email, $fullname, $pw) {
		$uid = $this->HDA_DB_FindUser($email);
		if (isset($uid) && is_array($uid) && count($uid)==1) {
			return false;
			}
		$code = $this->HDA_isUnique('U');
		$query = "INSERT INTO hda_users (UserItem,Email,UserFullName,PW,CreateDate,UpdateDate,LastLoginDate) ";
		$query .= " VALUES( ";
		$query .= "'{$code}',";
		$query .= "'{$email}',";
		$query .= "'{$fullname}',";
		$query .= "'".password_hash($pw, PASSWORD_DEFAULT)."',";
		$query .= "GetDate(),";
		$query .= "GetDate(),";
		$query .= "NULL ) ";
		if ($result=$this->_query($query)) {
			return true;
			}
		HDA_SendErrorMail("Register User".print_r(sqlsrv_errors(),true)." {$query}");
		return false;
	}

	public function HDA_DB_GetUserFullName($user, $uname=NULL) {
		if (is_null($uname)) {
			if ($user=="0") return "System";
			$query = "SELECT UserFullName as fname FROM hda_users WHERE UserItem='{$user}' ";
			if ($result = $this->_query($query)) {
				if ($row = $this->_fetch_row()) return $row['fname'];
				else {
					$ticket = $this->HDA_DB_getTickets($user);
					if (is_array($ticket)&&count($ticket)==1) return "Ticket User {$ticket[0]['UserName']}";
					return "Unknown User";
					}
				}
			HDA_SendErrorMail("Get UserName ".print_r(sqlsrv_errors(),true)." {$query}");
			return "User Not Found";
			}
		else {
			$query = "UPDATE hda_users SET UserFullName='{$uname}' WHERE UserItem='{$user}' ";
			if ($result = $this->_query($query)) return true;
			HDA_SendErrorMail("Change UserName ".print_r(sqlsrv_errors(),true)." {$query} ");
			return false;
			}
	}

	public function HDA_DB_AddPassword($user, $pw) {
		$query = "UPDATE hda_users SET PW='{$pw}' WHERE UserItem='{$user}' ";
		if ($result = $this->_query($query)) return true;
		return false;
	}

	public function HDA_DB_RemoveUser($user) {
		$query = "DELETE FROM hda_users WHERE UserItem='{$user}' ";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Delete User ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}

	public function HDA_DB_UserExists($user) {
		$query = "SELECT * FROM hda_users";
		if (isset($user)) $query .= " WHERE ((LOWER(Email)=LOWER('{$user}')) OR (UserItem='".$user."'))";
		if ($result = $this->_query($query)) {
			if ($row = $this->_fetch_row()) return true;
			return false;
			}
		HDA_SendErrorMail( "User Exists ".print_r(sqlsrv_errors(),true)." ".$query); 
		return true;
	}


	public function HDA_DB_whoOnline() {
		$this->HDA_DB_TidyOnline();
		$query = "SELECT * FROM hda_online ";
		if ($result = $this->_query($query)) {
			return $this->_fetch_all();
			}
		HDA_SendErrorMail("Test Online ".print_r(sqlsrv_errors(),true)." {$query} ");
		return NULL;
	}

	public function HDA_DB_stayOnline($user, $inTab) {
		global $LastLoginAt;
		if (is_null($LastLoginAt) || strlen($LastLoginAt)==0 || strtotime($LastLoginAt)==0) {
			$this->HDA_DB_sayLoggedOn($user);
			PRO_AddToParams('LastLoginAt', $LastLoginAt = $this->PRO_DB_dateNow()); 
			}
		$now = $this->PRO_DB_dateNow();
		$query = "IF EXISTS(SELECT UserItem FROM hda_online WHERE UserItem='{$user}') ";
		$query .= "UPDATE hda_online SET Activity='{$now}', Doing='{$inTab}', OnItem=NULL, OnTitle=NULL ";
		$query .= "WHERE UserItem='{$user}' ";
		$query .= " ELSE ";
		$query .= " INSERT INTO hda_online (UserItem,Logon,Activity,Doing) VALUES ('{$user}','{$now}','{$now}','{$inTab}');";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Stay Online ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}

	public function HDA_DB_OnLineSubject($user, $on_item, $on_title=null) {
		$a = array();
		$query = "SELECT UserItem FROM hda_online WHERE (UserItem != '{$user}') AND (OnItem='{$on_item}')";
		if ($result = $this->_query($query)) {
		while ($row = $this->_fetch_row()) {
			$a[] = $this->HDA_DB_GetUserFullName($row['UserItem']);
			}
		}
		$now = $this->PRO_DB_dateNow();
		$query = "UPDATE hda_online SET Activity='{$now}', OnItem='{$on_item}', OnTitle= ";
		$query .= (is_null($on_title))?"NULL":"'{$on_title}'";
		$query .= "WHERE UserItem='{$user}' ";
		@$this->_query($query);
		return $a;
	}


	public function HDA_DB_TidyOnline() {
		$ago = date('Y-m-d G:i',strtotime("1 hour ago"));
		$query = "DELETE FROM hda_online WHERE  Activity<'{$ago}'";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Tidy Online ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}

	public function PRO_DB_RealLogout($user) {
		$query = "DELETE FROM hda_online WHERE UserItem='{$user}' ";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Real Logout ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}

	public function HDA_DB_registerOnline($user, $inTab) {
		$now = $this->PRO_DB_dateNow();
		$query = "IF EXISTS(SELECT UserItem FROM hda_online WHERE UserItem='{$user}') ";
		$query .= "UPDATE hda_online SET";
		$query .= "  UserItem='{$user}', Logon='{$now}', Activity='{$now}', Doing='{$inTab}' ";
		$query .= " ELSE ";
		$query .= " INSERT INTO hda_online (UserItem,Logon,Activity,Doing) VALUES ('{$user}','{$now}','{$now}','{$inTab}');";
		if ($result = $this->_query($query)) {
			return true;
		}
		HDA_SendErrorMail("Register Online ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}

	public function HDA_DB_sayLoggedOn($user) {
		$now = $this->PRO_DB_dateNow();
		$query = "UPDATE hda_users SET LastLoginDate='{$now}' WHERE UserItem='{$user}' ";
		if ($result=$this->_query($query)) return true;
		HDA_SendErrorMail("Say Logged In ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}
	
	
	public function HDA_DB_FindKnownEmailUser($email) {
		$user = $this->HDA_DB_FindUser($email);
		if (is_array($user) && count($user)==1) return array($user[0]['UserItem'], $user[0]['Email'],$user[0]['UserFullName']);
		$a = $this->HDA_DB_getTickets(null, null, $email);
		if (is_array($a)&&count($a)>0) return array($a[0]['ItemId'],$a[0]['Email'],$a[0]['UserName']);
		return null;
	}
	public function HDA_DB_FindAnonymousEmailUser() {
		$user = $this->HDA_DB_FindUser(null,'ANONYMOUS_EMAIL_USER');
		if (is_array($user) && count($user)==1) return array($user[0]['UserItem'], $user[0]['Email'],$user[0]['UserFullName']);
		return null;
	}

	public function HDA_DB_UsersOfProfile($item) {
		$plist = array();
		$a = $this->HDA_DB_OwnerOfProfile($item);
		if (is_array($a)) $plist[$a['Email']] = array($a['UserFullName'],$a['UserItem']);
		$a = $this->HDA_DB_getTickets($item);
		if (is_array($a)) foreach ($a as $ticket) {
			if (strlen($ticket['Email'])>0) $plist[$ticket['Email']] = array($ticket['UserName'],$ticket['ItemId']);
			}
		$pusers = array();
		foreach ($plist as $euser=>$nuser) $pusers[] = array($euser,$nuser[0],$nuser[1]);
		return $pusers;
	}



	public function HDA_DB_UserIsAllowed($user) {
		$a = $this->HDA_DB_FindUser($user);
		$allow = array();
		if (isset($a) && is_array($a) && count($a)==1) {
			$allow = $a[0]['Allow'];
			}
		return $allow;
	}


	public function HDA_DB_writeUserAllow($user, $allow) {
		$allow= $this->HDA_DB_serialize($allow);
		$query = "UPDATE hda_users SET Allow='{$allow}' WHERE UserItem='{$user}' ";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Write User Permissions (allow) ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}

	public function HDA_DB_writeUserOptions($user, $options) {
		$options= $this->HDA_DB_serialize($options);
		$query = "UPDATE hda_users SET Profiles='{$options}' WHERE UserItem='{$user}' ";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Write User Options ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}

	public function HDA_DB_GetUserOptions($user) {
		$a = $this->HDA_DB_FindUser($user);
		$options = array();
		if (isset($a) && is_array($a) && count($a)==1) {
			$options = $a[0]['Profiles'];
			if (!array_key_exists('EMAIL_ME',$options)) {
				$options = array('EMAIL_ME'=>1);
				$this->HDA_DB_writeUserOptions($user, $options);
				}
			}
		return $options;
	}

	//END USERS
	
	// WATCHDOG
	public function HDA_DB_TidyWatchMessages() {
		$ago = date('Y-m-d G:i',strtotime("6 minutes ago"));
		$query = "DELETE FROM hda_watch WHERE IssuedDate<'{$ago}'";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Tidy Watch Msgs ".print_r(sqlsrv_errors(),true)." {$query}");
		return false;
	}

	public function HDA_DB_WriteWatchMessage($user, $code, $msg) {
		$now = $this->PRO_DB_dateNow();
		$watch_msg = "{$user}-{$code}";
		$query = "DELETE FROM hda_watch WHERE  WatchMessage='{$watch_msg}' AND Message='{$msg}'";
		$result=$this->_query($query);
		$query = " INSERT INTO hda_watch  ";
		$query .= " (SentFrom,WatchMessage,IssuedDate,Message) VALUES ('{$user}','{$watch_msg}','{$now}','{$msg}');";
		if ($result=$this->_query($query)) return true;
		HDA_SendErrorMail("Write Watch Msg ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}


	public function HDA_DB_GetWatchMessages($ago) {
		$this->HDA_DB_TidyWatchMessages();
		$ago = date('Y-m-d G:i', strtotime("{$ago} minutes ago"));
		$query = "SELECT * FROM hda_watch WHERE  ";
		$query .= "(IssuedDate>'{$ago}' )";
		if ($result = $this->_query($query)) {
			return $this->_fetch_all();
			}
		HDA_SendErrorMail("DB Get Watch Messages:".print_r(sqlsrv_errors(),true)." {$query} ");
		return NULL;
	}

	
	// END WATCHDOG


	// ITEMS and PROFILES
	

	public function HDA_DB_TitleOf($item) {
		$query = "SELECT Title FROM hda_profiles WHERE ItemId='{$item}' ";
		if ($result = $this->_query($query)) {
			if ($row = $this->_fetch_row()) return $row['Title'];
			return $item;
			}
		HDA_SendErrorMail("Title Of ".print_r(sqlsrv_errors(),true)." {$query} ");
		return $item;
	}


	public function HDA_DB_profileNames($like=null) {
		$query = "SELECT ItemId, Title FROM hda_profiles ";
		if (!is_null($like)) $query .= "WHERE Title Like '%{$like}%' ";
		$query .= " ORDER BY Title ASC";
		$a = array();
		if ($result = $this->_query($query)) {
			while ($row = $this->_fetch_row()) {
				$a[$row['ItemId']] = $row['Title'];
				}
			return $a;
			}
		HDA_SendErrorMail("Get profile names ".print_r(sqlsrv_errors(),true)." {$query} ");
		return $a;
	}
	public function HDA_DB_profileIds() {
		$query = "SELECT ItemId FROM hda_profiles ORDER BY Title ASC";
		$a = array();
		if ($result = $this->_query($query)) {
			while ($row = $this->_fetch_row()) {
				$a[] = $row['ItemId'];
				}
			return $a;
			}
		HDA_SendErrorMail("Get profile Ids ".print_r(sqlsrv_errors(),true)." {$query} ");
		return $a;
	}

	public function HDA_DB_lookUpProfile($named) {
		$query = "SELECT ItemId FROM hda_profiles WHERE (Title='{$named}') OR (ItemId='{$named}') ";
		if ($result = $this->_query($query)) {
			if ($row = $this->_fetch_row()) {
				return $row[0];
				}
			return null;
			}
		HDA_SendErrorMail("Look up profile by title ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}
	public function HDA_DB_listProfiles($user=NULL, $category=NULL) {
		$query = "SELECT hda_profiles.*, EventCode,hda_events.IssuedDate as EventDate FROM hda_profiles  ";
		$query .= " LEFT JOIN hda_events ON hda_profiles.ItemId=hda_events.ItemId ";
		$query .= "  WHERE hda_profiles.ItemId IS NOT NULL ";
		if (!is_null($user)) $query .= " AND CreatedBy='{$user}' ";
		if (!is_null($category)) {$category = $this->HDA_DB_textToDB($category); $query .= " AND Category='{$category}' "; }
		$query .= " ORDER BY Title ASC";
		if ($result = $this->_query($query)) {
			$a = array();
			while ($row = $this->_fetch_row()) {
				$row['ItemText'] = $this->HDA_DB_textFromDB($row['ItemText']);
				$row['Category'] = $this->HDA_DB_textFromDB($row['Category']);
				$row['MetaTags'] = $this->HDA_DB_textFromDB($row['MetaTags']);
				$row['SMS'] = $this->HDA_DB_textFromDB($row['SMS']);
				$row['Params'] = $this->HDA_DB_unserialize($this->HDA_DB_textFromDB($row['Params']));
				$a[] = $row;
				}
			return $a;
			}
		HDA_SendErrorMail("List Profiles ".print_r(sqlsrv_errors(),true)." {$query} ");
		return NULL;
	}
	public function HDA_DB_profileIndexItem($item) {
		$query = "SELECT hda_profiles.*,";
		$query .= " hda_users.UserFullName as 'Owner',hda_users.Email as 'Email',";
		$query .= " tev.EventDate,tev.EventCode, ";
		$query .= " hda_schedule.Scheduled, hda_schedule.Units, hda_schedule.RepeatInterval,";
		$query .= " hda_auto_log.IssuedDate as 'AutoDate', hda_auto_log.ItemText as 'AutoText',";
		$query .= " hda_collects.ItemText as 'CollectFrom', hda_collects.Status as 'WillCollect' ";
		$query .= "   FROM hda_profiles ";
		$query .= "LEFT JOIN hda_users ON hda_profiles.CreatedBy = hda_users.UserItem ";
		$query .= "LEFT JOIN hda_schedule ON hda_profiles.ItemId=hda_schedule.ItemId ";
		$query .= "LEFT JOIN hda_auto_log ON hda_profiles.ItemId=hda_auto_log.ItemId ";
		$query .= "LEFT JOIN ";
		$query .= "(SELECT ItemId as EventId,EventCode,IssuedDate as EventDate FROM hda_events ";
		$query .= "WHERE (EventCode LIKE '%_SUCCESS') OR (EventCode LIKE '%_FAILURE') OR (EventCode LIKE '%_LATE')) AS tev ";
		$query .= "ON hda_profiles.ItemId=tev.EventId ";
		$query .= "LEFT JOIN hda_collects ON hda_collects.ItemId=hda_profiles.ItemId ";
		$query .= "WHERE hda_profiles.ItemId = '{$item}' ";
		if ($result = $this->_query($query)) {
			if ($row = $this->_fetch_row()) {
				$row['ItemText'] = $this->HDA_DB_textFromDB($row['ItemText']);
				$row['Category'] = $this->HDA_DB_textFromDB($row['Category']);
				$row['AutoText'] = $this->HDA_DB_textFromDB($row['AutoText']);
				$row['CollectFrom'] = $this->HDA_DB_textFromDB($row['CollectFrom']);
				$row['MetaTags'] = $this->HDA_DB_textFromDB($row['MetaTags']);
				return $row;
				}
			return null;
			}
		HDA_SendErrorMail("Profile Index Item ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}

	public function HDA_DB_getProfiles($user=NULL, $category=NULL) {
		$query = "SELECT * FROM hda_profiles WHERE ItemId IS NOT NULL ";
		if (!is_null($user)) $query .= " AND CreatedBy='{$user}' ";
		if (!is_null($category)) {$category = $this->HDA_DB_textToDB($category); $query .= " AND Category='{$category}' "; }
		$query .= " ORDER BY Title ASC";
		if ($result = $this->_query($query)) {
			$a = array();
			while ($row = $this->_fetch_row()) {
				$row['ItemText'] = $this->HDA_DB_textFromDB($row['ItemText']);
				$row['Category'] = $this->HDA_DB_textFromDB($row['Category']);
				$row['MetaTags'] = $this->HDA_DB_textFromDB($row['MetaTags']);
				$row['SMS'] = $this->HDA_DB_textFromDB($row['SMS']);
				$row['Params'] = $this->HDA_DB_unserialize($this->HDA_DB_textFromDB($row['Params']));
				$a[] = $row;
				}
			return $a;
			}
		HDA_SendErrorMail("Get Profiles ".print_r(sqlsrv_errors(),true)." {$query} ");
		return NULL;
	}

	public function HDA_DB_findProfiles($like) {
		$query = "SELECT ItemId, Title FROM hda_profiles WHERE Title Like '%{$like}%' ORDER BY Title ASC";
		if ($result = $this->_query($query)) {
			return $this->_fetch_all();
			}
		HDA_SendErrorMail("Find Profiles ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}

	public function HDA_DB_searchProfileTags($like, &$all_tags) {
		$all_tags = array();
		$like = explode(';',str_replace(',',';',$like));
		$query = "SELECT ItemId, Title, MetaTags FROM hda_profiles";
		if ($result = $this->_query($query)) {
			$a = array();
			while ($row = $this->_fetch_row()) {
				$tags_s = $this->HDA_DB_textFromDB($row['MetaTags']);
				$tags = explode(';',$tags_s);
				foreach($tags as $tag) { $tag = trim($tag); if (strlen($tag)>0 && !_in_array_icase($tag,$all_tags)) $all_tags[] = $tag; }
				foreach($like as $tag) {
					if (_in_array_icase($tag, $tags)) $a[$row['ItemId']] = array('Title'=>$row['Title'], 'Tags'=>$tags_s);
					elseif (stripos($tags_s, $tag)!==false) $a[$row['ItemId']] = array('Title'=>$row['Title'], 'Tags'=>$tags_s);
					else {
						$tag = preg_replace("/[\s]*/","",$tag);
						foreach ($tags as $a_tag) {
							if (stripos(preg_replace("/[\s]*/","",$a_tag),$tag)!==false) $a[$row['ItemId']] = array('Title'=>$row['Title'], 'Tags'=>$tags_s);
							}
						}
					}
				}
			return $a;
			}
		HDA_SendErrorMail("Search Tags ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}

	public function HDA_DB_ReadProfile($code, $named=null) {
		$query = "SELECT * FROM hda_profiles WHERE ItemId IS NOT NULL ";
		if (!is_null($code)) $query .= "AND ItemId='{$code}' ";
		if (!is_null($named)) $query .= "AND Title='{$named}' ";
		if ($result = $this->_query($query)) {
			if ($row = $this->_fetch_row()) {
				$row['ItemText'] = $this->HDA_DB_textFromDB($row['ItemText']);
				$row['Category'] = $this->HDA_DB_textFromDB($row['Category']);
				$row['MetaTags'] = $this->HDA_DB_textFromDB($row['MetaTags']);
				$row['SMS'] = $this->HDA_DB_textFromDB($row['SMS']);
				$row['Params'] = $this->HDA_DB_unserialize($this->HDA_DB_textFromDB($row['Params']));
				return $row;
				}
			return NULL;
			}
		HDA_SendErrorMail("Read Profile ".print_r(sqlsrv_errors(),true)." {$query} ");
		return NULL;
	}

	public function HDA_DB_profileExists($code) {
		$query = "SELECT * FROM hda_profiles WHERE ItemId='{$code}' ";
		if ($result = $this->_query($query)) {
			if ($row = $this->_fetch_row()) return true;
			else return false;
			}
		HDA_SendErrorMail("Profile Exists Test ".print_r(sqlsrv_errors(),true)." {$query} ");
		return true; // SAFE GUARD, USED BY CLEANUP, MUST BE A POSITIVE NON FAILURE, PROFILE MISSING
	}

	public function HDA_DB_OwnerOfProfile($item) {
		$query = "SELECT hda_users.Email,hda_users.UserFullName,hda_users.UserItem FROM hda_users ";
		$query .= "JOIN hda_profiles ON hda_profiles.CreatedBy=hda_users.UserItem WHERE hda_profiles.ItemId='{$item}' ";
		if ($result = $this->_query($query)) {
			if ($row = $this->_fetch_row()) return $row;
			return null;
			}
		HDA_SendErrorMail("Get Profile Owner ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}

	public function HDA_DB_WriteProfile($item, $user=NULL) {
		global $UserCode;
		if (is_null($user)) $user = $UserCode;
		$code = $item['ItemId'];
		$fields = array();
		$fields['ModifiedDate'] = "'".$this->PRO_DB_dateNow()."'"; 
		$fields['ModifiedBy']="'{$user}'";
		foreach ($item as $k=>$p) {
			switch ($k) {
				default:
					if (is_null($p)) $fields[$k] = "NULL,"; 
					else {
						switch ($k) {
							case 'Title': $fields[$k] = "'"._clean($p)."'"; break;
							case 'Category':
							case 'MetaTags':
							case 'SMS':
							case 'ItemText': $fields[$k] = "'".$this->HDA_DB_textToDB($p)."'"; break;
							case 'Params':
									$fields[$k] = "'".$this->HDA_DB_textToDB($this->HDA_DB_serialize($p))."'";
									break;
							}
						}
					break;
				case 'Tickets':
					foreach ($p as $tid=>$tuser) {
						$this->HDA_DB_makeTicket($item['ItemId'], $tuser, null, null, $tid);
						}
					break;
				}
			}
		$query = "IF EXISTS (SELECT ItemId FROM hda_profiles WHERE ItemId='{$code}') ";
		$query .= "UPDATE hda_profiles SET ";
		foreach($fields as $field=>$value) $query .= "[{$field}]={$value},"; $query = trim($query, ','); $query .= " ";
		$query .= " WHERE ItemId='{$code}' ";
		$query .= "ELSE INSERT INTO hda_profiles (ItemId,";
		foreach($fields as $field=>$value) $query .= "[{$field}],"; $query = trim($query, ','); $query .= ") ";
		$query .= "VALUES ('{$code}',";
		foreach($fields as $field=>$value) $query .= "{$value},"; $query = trim($query, ','); $query .= ") ";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Write Profile ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}

	public function HDA_DB_UpdateProfile($item, $a, $user=NULL) {
		global $UserCode;
		if (is_null($user)) $user = $UserCode;
		if ($user<>0) {
			$a['ModifiedDate']=$this->PRO_DB_dateNow();
			$a['ModifiedBy']=$user;
			}
		$query = "UPDATE hda_profiles SET ";
		foreach ($a as $k=>$p) {
			$query .= "{$k}=";
			if (is_null($p)) $query .= "NULL,"; 
			else {
				switch ($k) {
					case 'Title': $p = _clean($p); break;
					case 'Category':
					case 'MetaTags':
					case 'ItemText': $p = $this->HDA_DB_textToDB($p); break;
					case 'Params':
						$p = $this->HDA_DB_textToDB($this->HDA_DB_serialize($p));
						break;
					}
				$query .= "'{$p}',";
				}
			}
		$query[strlen($query)-1]=' ';
		$query .= " WHERE ItemId='{$item}' ";
		if ($result = $this->_query($query)) {return true;}
		HDA_SendErrorMail("Update Profile ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}

	public function HDA_DB_DeleteProfile($item) {
		$query = "DELETE FROM hda_profiles WHERE ItemId='{$item}' ";
		if ($result = $this->_query($query)) {
			$query = "DELETE FROM hda_tree WHERE ItemId='{$item}' OR ParentId='{$item}' ";
			if ($this->_query($query)===false) HDA_SendErrorMail("Delete Profile - from tree ".print_r(sqlsrv_errors(),true)." {$query} ");
			$this->HDA_DB_deleteNotes($item);
			$a = $this->HDA_DB_getTickets($ticket = NULL, $item, $forEmail=NULL);
			if (is_array($a) && count($a)>0) {
				foreach ($a as $row) {
					$this->HDA_DB_deleteTicket($row['ItemId']);
					}
				}
			$query = "DELETE FROM hda_events WHERE ItemId='{$item}' ";
			if ($this->_query($query)===false) HDA_SendErrorMail("Delete Events, delete profile ".print_r(sqlsrv_errors(),true)." {$query} ");
			$query = "DELETE FROM hda_auto_log WHERE ItemId='{$item}' ";
			if ($this->_query($query)===false) HDA_SendErrorMail("Delete profile, auto collect ".print_r(sqlsrv_errors(),true)." {$query} ");
			$this->HDA_DB_clearSchedule($item);
			return true;
			}
		HDA_SendErrorMail("Delete Profile ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}

	public function HDA_DB_NewProfile($named, $desc) {
		global $UserCode;
		$code = $this->HDA_isUnique('PF');
		$query = "INSERT INTO hda_profiles ";
		$query .= "(ItemId, Title, ItemText, MetaTags, Q, SMS, Params, CreatedBy, CreateDate, ModifiedBy, ModifiedDate) ";;
		$query .= "VALUES (";
		$query .= "'{$code}','{$named}','{$desc}','',0,NULL, ";
		$query .= "'".$this->HDA_DB_textToDB($this->HDA_DB_serialize(array()))."',";
		$query .= "'{$UserCode}', ";
		$query .= "'".$this->PRO_DB_dateNow()."', ";
		$query .= "'{$UserCode}', ";
		$query .= "'".$this->PRO_DB_dateNow()."' )";
		if ($result = $this->_query($query)) {
			return $code;
			}
		HDA_SendErrorMail("Create Profile ".print_r(sqlsrv_errors(),true)." {$query} ");
		return NULL;
	}


	// END PROFILES
	
	// RELATION TREE
	

	public function _invalidateTreeFrom($item) {
		$a = $this->HDA_DB_allParentsOf($item);
		foreach ($a as $parent) {
			$this->HDA_DB_ResetSysEvents($parent);
			}
		}

	public function HDA_DB_Relation($item, $parent = NULL, $note = NULL) {
		if (!is_null($parent)) {
			if ($this->HDA_DB_profileExists($parent)===false) {
				$query = "DELETE FROM hda_tree WHERE ItemId='{$item}' ";
				}
			else {
				$query = "IF EXISTS(SELECT ItemId FROM hda_tree WHERE ItemId='{$item}') ";
				$query .= " UPDATE hda_tree SET ParentId='{$parent}'  WHERE ItemId='{$item}' ";
				$query .= " ELSE ";
				$query = "INSERT INTO hda_tree (ItemId,ParentId,ItemText) VALUES ('{$item}','{$parent}',NULL) ";
				}
			}
		elseif (!is_null($note)) {
			$note = $this->HDA_DB_textToDB($note);
			$query = "IF EXISTS(SELECT ItemId FROM hda_tree WHERE ItemId='{$item}') ";
			$query .= " UPDATE hda_tree SET ItemText='{$note}'  WHERE ItemId='{$item}' ";
			$query .= " ELSE ";
			$query .= "INSERT INTO hda_tree (ItemId,ParentId,ItemText) VALUES ('{$item}',NULL,'{$note}') ";
			}
		else return false;
		HDA_RecordThis("Add Relation", $query);
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Add Relation ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}
	public function HDA_DB_childrenOf($item, $cat=NULL, &$events) {
		$query = "SELECT hda_profiles.ItemId, Title, hda_tree.Enabled AS 'TActive', [Rule], OnDefault, OnFail, DataDays, hda_auto_log.ItemText AS 'AutoLog' ";
		$query .= "   FROM hda_profiles JOIN hda_tree ON (hda_profiles.ItemId=hda_tree.ItemId) ";
		$query .= " LEFT JOIN hda_auto_log ON hda_profiles.ItemId=hda_auto_log.ItemId ";
		$query .= "WHERE ";
		if (!is_null($cat)) $query .= "Category='{$cat}' AND ";
		$query .= " (hda_tree.ParentId='{$item}' AND hda_tree.ItemId<>'{$item}') ";
		$query .= " ORDER BY Title ASC";
		if ($result = $this->_query($query)) {
			$a = array();
			while ($row = $this->_fetch_row()) if ($item!=$row['ItemId']) {
				$row['Children'] = $this->HDA_DB_childrenOf($row['ItemId'], $cat, $events);
				$row['LastTask'] = (array_key_exists($row['ItemId'], $events))?$events[$row['ItemId']]:null;
				$row['AutoLog'] = $this->HDA_DB_textFromDB($row['AutoLog']);
				if (is_null($row['DataDays'])) $row['DataDays'] = 0x3f;
				if (is_null($row['Rule'])) $row['Rule'] = 'T';
				if (is_null($row['OnFail'])) $row['OnFail'] = 'N';
				if (is_null($row['OnDefault'])) $row['OnDefault'] = 'N';
				$a[$row['ItemId']] = $row;
				}
			return (count($a)>0)?$a:null;
			}
		HDA_SendErrorMail("Get Relations - children of ".print_r(sqlsrv_errors(),true)." {$query}");
		return null;
	}
	public function HDA_DB_parentOf($item) {
		$query = "SELECT hda_profiles.Title,hda_profiles.ItemId FROM hda_profiles INNER JOIN hda_tree ON hda_profiles.ItemId=hda_tree.ParentId WHERE hda_tree.ItemId='{$item}' AND hda_tree.ItemId<>hda_tree.ParentId";
		if ($result = $this->_query($query)) {
			if ($row = $this->_fetch_row()) return $row;
			return null;
			}
		HDA_SendErrorMail("Relation Parent ".print_r(sqlsrv_errors(),true)." {$query}");
		return null;
	}
	public function HDA_DB_allParentsOf($item) {
		$query = "SELECT ParentId FROM hda_tree WHERE ItemId='{$item}' ";
		if ($result = $this->_query($query)) {
			$a = array();
			if ($row = $this->_fetch_row()) {
				$a = array_merge(array($row[0]), $this->HDA_DB_allParentsOf($row[0]));
				}
			return $a;
			}
		HDA_SendErrorMail("Relation all Parents ".print_r(sqlsrv_errors(),true)." {$query}");
		return array();
	}
	public function HDA_DB_getRelationTable() {
		$query = "SELECT * FROM hda_tree";
		if ($result = $this->_query($query)) {
			$aa = "<tree>";
			while ($row = $this->_fetch_row()) {
				$aa .= "<item>";
				$aa .= "<name>".$this->HDA_DB_TitleOf($row['ItemId'])."</name>";
				if ($row['ParentId']!='X') $aa .= "<parent>".$this->HDA_DB_TitleOf($row['ParentId'])."</parent>";
				$aa .= "<note>".urlencode($this->HDA_DB_textFromDB($row['ItemText']))."</note>";
				$aa .= "<enabled>{$row['Enabled']}</enabled>";
				$aa .= "<rule>{$row['Rule']}</rule>";
				$aa .= "<fail>{$row['OnFail']}</fail>";
				$aa .= "<default>{$row['OnDefault']}</default>";
				$aa .= "<datadays>{$row['DataDays']}</datadays>";
				$aa .= "</item>";
				}
			$aa .= "</tree>";
			return $aa;
			}
		HDA_SendErrorMail("Relation Table ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}
	public function HDA_DB_getRelationTableText() {
		$query = "SELECT * FROM hda_tree";
		if ($result = $this->_query($query)) {
			$aa = array();
			while ($row = $this->_fetch_row()) {
				$row['Name'] = $this->HDA_DB_TitleOf($row['ItemId']);
				$row['Parent'] = $this->HDA_DB_TitleOf($row['ParentId']);
				$aa[] = $row;
				}
			return $aa;
			}
		HDA_SendErrorMail("Relation Table ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}
	public function HDA_DB_putRelationTable($row) {
		$query = "IF EXISTS (SELECT ItemId FROM hda_tree WHERE ItemId='{$row['ItemId']}') ";
		$query .= "UPDATE hda_tree SET ";
		foreach ($row as $field=>$v) {
			switch ($field) {
				default: $query .= "{$field}='{$v}',"; break;
				case 'ItemText': $query .= "{$field}='".$this->HDA_DB_textToDB($v)."',"; break;
				}
			}
		$query = trim($query,','); $query .= " WHERE ItemId='{$row['ItemId']}' ";
		$query .= " ELSE INSERT INTO hda_tree (";
		foreach($row as $field=>$v) $query .= "[{$field}],"; 
		$query .= " ) VALUES ( ";
		foreach($row as $field=>$v) $query .= "'{$v}',"; $query = trim($query,',');
		$query .= ") ";
		HDA_RecordThis("Put Relation", $query);
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Put Relation ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}
	public function HDA_DB_getRelations($cat = NULL) {
		$events = $this->HDA_DB_AllSuccessEvents();
		$query = "SELECT hda_profiles.ItemId, Title, hda_tree.Enabled AS 'TActive', [Rule], OnDefault, OnFail, DataDays, hda_auto_log.ItemText AS 'AutoLog' ";
		$query .= "   FROM hda_profiles LEFT JOIN hda_tree ON hda_profiles.ItemId=hda_tree.ItemId ";
		$query .= " LEFT JOIN hda_auto_log ON hda_profiles.ItemId=hda_auto_log.ItemId ";
		$query .= "WHERE ";
		if (!is_null($cat)) $query .= "Category='{$cat}' AND ";
		$query .= " hda_profiles.ItemId IN (SELECT ParentId FROM hda_tree) AND hda_profiles.ItemId NOT IN (SELECT hda_tree.ItemId FROM hda_tree WHERE hda_tree.ParentId<>'X')";
		$query .= " ORDER BY Title ASC";
		if ($result = $this->_query($query)) {
			$a = array();
			while ($row=$this->_fetch_row()) {
				$row['Children'] = $this->HDA_DB_childrenOf($row['ItemId'], $cat, $events);
				$row['LastTask'] = (array_key_exists($row['ItemId'], $events))?$events[$row['ItemId']]:null;
				$row['AutoLog'] = $this->HDA_DB_textFromDB($row['AutoLog']);
				if (is_null($row['DataDays']) || $row['DataDays']=='') $row['DataDays'] = 0x3f;
				if (is_null($row['Rule']) || $row['Rule']=='') $row['Rule'] = 'T';
				if (is_null($row['OnFail']) || $row['OnFail']=='') $row['OnFail'] = 'N';
				if (is_null($row['OnDefault']) || $row['OnDefault']=='') $row['OnDefault'] = 'N';
				$a[$row['ItemId']] = $row;
				}
			$query = "SELECT hda_profiles.ItemId, Title, hda_tree.Enabled AS 'TActive', [Rule], OnDefault, OnFail, DataDays, hda_auto_log.ItemText AS 'AutoLog' ";
			$query .= "	  FROM hda_profiles LEFT JOIN hda_tree ON hda_profiles.ItemId=hda_tree.ItemId  ";
			$query .= " LEFT JOIN hda_auto_log ON hda_profiles.ItemId=hda_auto_log.ItemId ";
			$query .= "WHERE ";
			if (!is_null($cat)) $query .= "Category='{$cat}' AND ";
			$query .= "hda_profiles.ItemId NOT IN (SELECT hda_tree.ItemId FROM hda_tree WHERE hda_tree.ParentId<>'X' UNION SELECT ParentId FROM hda_tree) ";
			$query .= " ORDER BY Title ASC";
			if ($result_orphans = $this->_query($query)) {
				$result_orphans = $this->_fetch_all();
				foreach ($result_orphans as $row) {
					$row['Children'] = null;
					$row['LastTask'] = (array_key_exists($row['ItemId'], $events))?$events[$row['ItemId']]:null;
					$row['AutoLog'] = $this->HDA_DB_textFromDB($row['AutoLog']);
					if (is_null($row['DataDays']) || $row['DataDays']=='') $row['DataDays'] = 0x3f;
					if (is_null($row['Rule']) || $row['Rule']=='') $row['Rule'] = 'T';
					if (is_null($row['OnFail']) || $row['OnFail']=='') $row['OnFail'] = 'N';
					if (is_null($row['OnDefault']) || $row['OnDefault']=='') $row['OnDefault'] = 'N';
					$a[$row['ItemId']] = $row;
					}
				}
			else { HDA_SendErrorMail("Get Relations - orphans ".print_r(sqlsrv_errors(),true)." {$query} "); return null; }
			return $a;
			}
		HDA_SendErrorMail("Get Relations - parents ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}

	public function HDA_DB_relationRule($item, $rule = NULL) {
		if (is_null($rule)) {
			$query = "SELECT [Rule] FROM hda_tree WHERE ItemId='{$item}' ";
			if ($result = $this->_query($query)) {
				if ($row = $this->_fetch_row()) return $row['Rule'];
				return "T";
				}
			HDA_SendErrorMail("Get Relation Rule ".print_r(sqlsrv_errors(),true)." {$query}");
			return "";
			}
		else {
			$query = "IF EXISTS(SELECT ItemId FROM hda_tree WHERE ItemId='{$item}') ";
			$query .= " UPDATE hda_tree SET [Rule] ='{$rule}' WHERE ItemId='{$item}' ";
			$query .= " ELSE ";
			$query .= "INSERT INTO hda_tree (ItemId,ParentId,Rule) VALUES ('{$item}','X','{$rule}') ";
			HDA_RecordThis("Set Relation Rule", $query);
			if ($result = $this->_query($query)) return true;
			HDA_SendErrorMail("Set Relation Rule ".print_r(sqlsrv_errors(),true)." {$query}");
			return false;
			}
	}
	public function HDA_DB_relationFail($item, $rule = NULL) {
		if (is_null($rule)) {
			$query = "SELECT OnFail FROM hda_tree WHERE ItemId='{$item}' ";
			if ($result = $this->_query($query)) {
				if ($row = $this->_fetch_row()) return $row['OnFail'];
				return "N";
				}
			HDA_SendErrorMail("Get Relation Fail Rule ".print_r(sqlsrv_errors(),true)." {$query}");
			return "";
			}
		else {
			$query = "IF EXISTS(SELECT ItemId FROM hda_tree WHERE ItemId='{$item}') ";
			$query .= " UPDATE hda_tree SET OnFail ='{$rule}' WHERE ItemId='{$item}' ";
			$query .= " ELSE ";
			$query .= "INSERT INTO hda_tree (ItemId,ParentId,OnFail) VALUES ('{$item}','X','{$rule}') ";
			HDA_RecordThis("Set Relation Fail Rule", $query);
			if ($result = $this->_query($query)) return true;
			HDA_SendErrorMail("Set Relation Fail Rule ".print_r(sqlsrv_errors(),true)." {$query}");
			return false;
			}
	}
	public function HDA_DB_relationDefault($item, $rule = NULL) {
		if (is_null($rule)) {
			$query = "SELECT OnDefault FROM hda_tree WHERE ItemId='{$item}' ";
			if ($result = $this->_query($query)) {
				if ($row = $this->_fetch_row()) return $row['OnDefault'];
				return "N";
				}
			HDA_SendErrorMail("Get Relation Default Rule ".print_r(sqlsrv_errors(),true)." {$query}");
			return "";
			}
		else {
			$query = "IF EXISTS (SELECT ItemId FROM hda_tree WHERE ItemId='{$item}') ";
			$query .= " UPDATE hda_tree SET OnDefault='{$rule}' WHERE WHERE ItemId='{$item}' ";
			$query .= " ELSE ";
			$query .= "INSERT INTO hda_tree (ItemId,ParentId,OnDefault) VALUES ('{$item}','X','{$rule}') ";
			HDA_RecordThis("Set Relation Default Rule", $query);
			if ($result = $this->_query($query)) return true;
			HDA_SendErrorMail("Set Relation Default Rule ".print_r(sqlsrv_errors(),true)." {$query}");
			return false;
			}
	}

	public function HDA_DB_relationEnabled($item, $enabled=null) {
		if (is_null($enabled)) {
			$query = "SELECT Enabled FROM hda_tree WHERE ItemId='{$item}' ";
			if ($result = $this->_query($query)) {
				if ($row = $this->_fetch_row()) return (($row['Enabled']&1)==1);
				return true;
				}
			HDA_SendErrorMail("Get Relation Enabled ".print_r(sqlsrv_errors(),true)." {$query}");
			return false;
			}
		else {
			$query = "IF EXISTS (SELECT ItemId FROM hda_tree WHERE ItemId='{$item}') ";
			$query .= " UPDATE hda_tree SET Enabled='{$enabled}' WHERE WHERE ItemId='{$item}' ";
			$query .= " ELSE ";
			$query .= "INSERT INTO hda_tree (ItemId,ParentId,Enabled) VALUES ('{$item}','X','{$enabled}') ";
			HDA_RecordThis("Set Relation Enable", $query);
			if ($result = $this->_query($query)) return true;
			HDA_SendErrorMail("Set Relation Enable ".print_r(sqlsrv_errors(),true)." {$query}");
			return false;
			}
	}
	public function HDA_DB_relationIsProxy($item) {
		$query = "SELECT Enabled FROM hda_tree WHERE ItemId='{$item}' ";
		if ($result = $this->_query($query)) {
			if ($row = $this->_fetch_row()) return (($row['Enabled']&2)==2);
			return false;
			}
		HDA_SendErrorMail("Get Relation Enabled ".print_r(sqlsrv_errors(),true)." {$query}");
		return false;
	}

	public function HDA_DB_relationDataDays($item, $datadays=NULL) {
		if (is_null($datadays)) {
			$query = "SELECT DataDays FROM hda_tree WHERE ItemId='{$item}' ";
			if ($result = $this->_query($query)) {
				if ($row = $this->_fetch_row()) return (!is_null($row['DataDays']))?($row['DataDays']&0xff):0xff;
				return 0xff;
				}
			HDA_SendErrorMail("Get Relation Data Days Rule ".print_r(sqlsrv_errors(),true)." {$query}");
			return 0xff;
			}
		else {
			$query = "IF EXISTS (SELECT ItemId FROM hda_tree WHERE ItemId='{$item}') ";
			$query .= " UPDATE hda_tree SET DataDays=(ifnull(DataDays,0) & 0xff00) | {$datadays} WHERE WHERE ItemId='{$item}' ";
			$query .= " ELSE ";
			$query .= "INSERT INTO hda_tree (ItemId,ParentId,DataDays) VALUES ('{$item}','X','{$datadays}') ";
			HDA_RecordThis("Set Relation Data Days Rule", $query);
			if ($result = $this->_query($query)) return true;
			HDA_SendErrorMail("Set Relation Data Days Rule ".print_r(sqlsrv_errors(),true)." {$query}");
			return false;
			}
	}   
	public function HDA_DB_relationDataDates($item, $datadates=NULL) {
		if (is_null($datadates)) {
			$query = "SELECT DataDays FROM hda_tree WHERE ItemId='{$item}' ";
			if ($result = $this->_query($query)) {
				if ($row = $this->_fetch_row()) return (!is_null($row['DataDays']))?(($row['DataDays']>>8)&0xff):1;
				return 1;
				}
			HDA_SendErrorMail("Get Relation Data Dates Rule ".print_r(sqlsrv_errors(),true)." {$query}");
			return 1;
			}
		else {
			$datadates = $datadates<<8;
			$query = "SELECT DataDays FROM hda_tree WHERE ItemId='{$item}' ";
			if ($result = $this->_query($query)) {
				if ($row = $this->_fetch_row()) {
					$datadates |= ($row['DataDays']&0xff);
					$query = "UPDATE hda_tree SET DataDays='{$datadates}' WHERE ItemId='{$item}' ";
				}
				else {
					$query = "INSERT INTO hda_tree (ItemId,ParentId,DataDays) VALUES ('{$item}','X','{$datadates}') ";
					}
				HDA_RecordThis("Set Relation Data Dates Rule", $query);
				if ($result = $this->_query($query)) return true;
				HDA_SendErrorMail("Set Relation Data Dates Rule ".print_r(sqlsrv_errors(),true)." {$query}");
				return false;
				}
			}  
	}


	public function HDA_DB_getRelationRules($item) {
		$query = "SELECT * FROM hda_tree WHERE ItemId='{$item}' ";
		if ($result = $this->_query($query)) {
			if ($row = $this->_fetch_row()) {
				$row['IsProxy'] = (($row['Enabled']&2)==2);
				$row['IsEnabled'] = (($row['Enabled']&1)==1);
				}
			else {
				$row['IsProxy'] = false;
				$row['IsEnabled'] = false;
				$row['OnDefault'] = 'N';
				$row['OnFail'] = 'N';
				$row['Rule'] = 'T';
				$row['DataDays'] = null;
				}
			if (is_null($row['DataDays'])) $row['DataDays'] = 0x3f;
			return $row;
			}
		HDA_SendErrorMail("Get Relation All Rules ".print_r(sqlsrv_errors(),true)." {$query}");
		return false;
	}
	
	
	// END RELATION TREE
	

	
	// AUTO LOG
	public function HDA_DB_autoLog($item, $log=NULL, $and_audit=false) {
		if (is_null($log)) {
			$query = "SELECT * FROM hda_auto_log ";
			if (!is_null($item)) $query .= "WHERE ItemId='{$item}' ";
			$query .= "ORDER BY IssuedDate DESC";
			if ($result = $this->_query($query)) {
				$a = array();
				while ($row = $this->fetch_row()) {
					$row['ItemText'] = $this->HDA_DB_textFromDB($row['ItemText']);
					$a[] = $row;
					}
				return $a;
				}
			HDA_SendErrorMail("Get Auto Log ".print_r(sqlsrv_errors(),true)." {$query} ");
			return null;
			}
		else {
			$itemtext = $this->HDA_DB_textToDB($log);
			$query = "IF EXISTS (SELECT ItemId FROM hda_auto_log WHERE ItemId='{$item}') ";
			$query .= "UPDATE hda_auto_log SET IssuedDate=GetDate(),";
			$query .= "ItemText='{$itemtext}' WHERE ItemId='{$item}' ";
			$query .= "ELSE INSERT INTO hda_auto_log (ItemId, IssuedDate,ItemText) VALUES ('{$item}',GetDate(),'{$itemtext}') ";
			if ($result = $this->_query($query)) return $log;
			HDA_SendErrorMail("Set Auto Log ".print_r(sqlsrv_errors(),true)." {$query} ");
			return $log;
			}
		return false;
	}
	public function HDA_DB_reportAutoLog() {
		$query = "SELECT hda_auto_log.IssuedDate,hda_auto_log.ItemText,hda_auto_log.ItemId,hda_profiles.Title FROM hda_auto_log ";
		$query .= " LEFT JOIN hda_profiles ON hda_profiles.ItemId=hda_auto_log.ItemId ";
		$query .= " ORDER BY hda_profiles.Title ASC";
		if ($result = $this->_query($query)) {
			$a = array();
			while ($row = $this->_fetch_row()) {
				$row['ItemText'] = $this->HDA_DB_textFromDB($row['ItemText']);
				$row['Title'] = $this->HDA_DB_textFromDB($row['Title']);
				$a[] = $row;
				}
			return $a;
			}
		HDA_SendErrorMail("Report Auto Log ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}
	
	
	// END AUTO LOG
	
	
	// TASKS

	public function HDA_DB_updateRcvFile($code, $rcvFile) {
		$query = "UPDATE hda_runQ SET RcvFile='{$rcvFile}' WHERE ItemId='{$code}' ";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Update rcv file in runQ ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}


	public function HDA_DB_TaskComplete($code, $success=true) {
		$success = ($success)?1:0;
		$now = $this->PRO_DB_dateNow();
		$query = "UPDATE hda_runQ SET ProcessResult={$success},ProcessDate='{$now}',ProcessState='FINISHED' ";
		$query .= " WHERE ItemId='{$code}' ";
		if ($result = $this->_query($query)) {
			return true;
			}
		HDA_SendErrorMail("Register Complete Task ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}


	public function HDA_DB_ReadTask($code) {
		$query = "SELECT * FROM hda_runQ WHERE ItemId='{$code}' ";
		if ($result = $this->_query($query)) {
			if ($row = $this->_fetch_row()) {
				$row['SourceInfo'] = $this->HDA_DB_textFromDB($row['SourceInfo']);
				return $row;
				}
			return null;
			}
		HDA_SendErrorMail("Read Task ".print_r(sqlsrv_errors(),true)." {$query} ");
		return NULL;
	}

	
	// END TASKS
	
	// LOGGING
	
	public function HDA_DB_UpdatesToday() {
		$now = $this->PRO_DB_dateNow();
		$a = array();
		foreach (array('hda_profiles') as $table) {
			$query = "SELECT Title,ModifiedDate,ModifiedBy FROM {$table} WHERE (HOUR(TIMEDIFF(ModifiedDate,'{$now}'))<24) ";
			if ($result = $this->_query($query)) {
				while ($row = $this->_fetch_row()) {
					$a[] = $row;
					}
				}
			else HDA_SendErrorMail("Updates Today ".print_r(sqlsrv_errors(),true)." {$query} ");
			}
		return $a;
	}


	public function HDA_DB_LastRunOf($item) {
		$query = "SELECT IssuedDate FROM hda_events WHERE (ItemId='{$item}') AND ((EventCode LIKE '%_SUCCESS') OR (EventCode LIKE '%_FAILURE')) ";
		if ($result = $this->_query($query)) {
			if ($row = $this->_fetch_row()) return $row[0];
			return NULL;
			}
		HDA_SendErrorMail("Last Run Of ".print_r(sqlsrv_errors(),true)." {$query} ");
		return NULL;
	}

	public function HDA_DB_tidyLogger($ago=3) {
		$since = $this->PRO_DB_Date(strtotime("{$ago} days ago"));
		$query = "DELETE FROM hda_log WHERE IssuedDate<'{$since}' ";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Tidy Log Tracks ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}


	public function HDA_DB_readLogger($item=NULL, $sources=NULL, $from=0, $limit=20) {
		$query = "WITH A AS (SELECT ROW_NUMBER() OVER (ORDER BY IssuedDate DESC) as ROWN, ";
		$query .= " * FROM hda_log WHERE ItemId IS NOT NULL ";
		if (!is_null($item)) $query .= "AND ItemId='{$item}' ";
		if (!is_null($sources)) {
			if (!is_array($sources)) $query .= "AND Source='{$sources}' ";
			elseif (count($sources)>0) {
				$in_s = "";
				foreach($sources as $k) $in_s.="'{$k}',";
				if (strlen($in_s)>0) {
					$in_s[strlen($in_s)-1]=' ';
					$query .= "AND Source IN ({$in_s}) ";
					}
				}
			}
		$query .= ") SELECT ";
		if ($limit>0) $query .= " TOP({$limit}) ";
		$query .= " * FROM A WHERE ROWN>{$from} ";
		if ($result=$this->_query($query)) {
			$a=array();
			while($row = $this->_fetch_row()) {
				$row['ItemText'] = $this->HDA_DB_textFromDB($row['ItemText']);
				$a[] = $row;
				}
			return $a;
			}
		HDA_SendErrorMail("Read Log Tracks ".print_r(sqlsrv_errors(),true)." {$query}");
		return NULL;
	}


	public function HDA_DB_writeLogger($user, $username, $source, $text, $itemlink=NULL, $as_date=NULL, $itemref=NULL) {
		$now = $this->PRO_DB_dateNow();
		$query = "INSERT INTO hda_log (ItemId,Source,OwnerId,IssuedDate,SourceName,ItemLink,ItemRef,ItemText) ";
		$query .= "VALUES('".$this->HDA_isUnique('TR')."',";
		$query .= "'{$source}',";
		$query .= "'{$user}',";
		$query .= (!is_null($as_date))?"'{$as_date}',":"'{$now}',";
		$query .= "'{$username}',";
		$query .= (!is_null($itemlink))?"'{$itemlink}',":"'',";
		$query .= (!is_null($itemref))?"'{$itemref}',":"'',";
		$query .= "'".$this->HDA_DB_textToDB($text)."') ";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Write Log Track ".print_r(sqlsrv_errors(),true)." {$query}");
		return false;
	}	
	// END LOGGING
	
	
	// JOBS and SCHEDULE
	
	public function HDA_DB_updateJobTimes($code, $a) {
		$a['ItemId'] = $code;
		$fields = array();
		$fields['ItemId'] = "'{$code}'";
		foreach ($a as $k=>$p) {
			$fields[$k] = (is_null($p))?"NULL":"'{$p}'";
			}
		$query = "IF EXISTS (SELECT ItemId FROM hda_job_times WHERE ItemId='{$code}' ) ";
		$query .= "UPDATE hda_job_times SET ";
		foreach($fields as $field=>$v) $query .= "[{$field}]={$v},"; $query = trim($query,',');
		$query .= " WHERE ItemId='{$code}' ";
		$query .= "ELSE INSERT INTO hda_job_times (";
		foreach($fields as $field=>$v) $query .= "[{$field}],"; $query = trim($query,',');
		$query .= ") VALUES (";
		foreach($fields as $field=>$v) $query .= "{$v},"; $query = trim($query,',');
		$query .= ") ";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Update Job Times ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}

	public function HDA_DB_ranJob($code, $rate=NULL, $period=NULL, $units=NULL) {
		global $_validDetectRates;
		$a['ItemId'] = $code;
		$a['LastTime'] = $this->PRO_DB_dateNow();
		if (!is_null($rate) && array_key_exists($rate, $_validDetectRates)) {
			$a['Period'] = $_validDetectRates[$rate][2];
			$a['PeriodUnits'] = $_validDetectRates[$rate][1];
			}
		elseif (!is_null($period) && !is_null($units)) {
			$a['Period'] = $period;
			$a['PeriodUnits'] = $units;
			}
		else {
			$query = "UPDATE hda_job_times SET LastTime='".$this->PRO_DB_dateNow()."' WHERE ItemId='{$code}' ";
			if ($result = $this->_query($query)) return true;
			HDA_SendErrorMail("Update Job Times ".print_r(sqlsrv_errors(),true)." {$query} ");
			return false;
			}
		return $this->HDA_DB_updateJobTimes($code, $a);
	}

	public function HDA_DB_timeForJob($code) {
		$query = "SELECT * FROM hda_job_times WHERE ItemId='{$code}' ";
		if ($result = $this->_query($query)) {
			if ($row = $this->_fetch_row()) {
				$last_time = $row['LastTime'];
				if (is_null($last_time)) return true;
				$dt1 = date_create($last_time);
				$dt2 = date_create();
				$interval = date_diff($dt1, $dt2);
				$run_now = false;
				switch ($row['Period']) {
					case 'MIN': 
						$run_now = ($interval->days !== false && $interval->days>0);
						$run_now |= ($interval->y>0 || $interval->m>0 || $interval->d>0 || $interval->h>0 || $interval->i>=$row['PeriodUnits']);
						break;
					case 'HOUR':
						$run_now = ($interval->days !== false && $interval->days>0);
						$run_now |= ($interval->y>0 || $interval->m>0 || $interval->d>0 || $interval->h>=$row['PeriodUnits']);
						break;
					case 'DAY':
						$run_now = ($interval->days !== false && $interval->days>$row['PeriodUnits']);
						$run_now |= ($interval->y>0 || $interval->m>0 || $interval->d>=$row['PeriodUnits']);
						break;
					case 'MONTH':
						$run_now = ($interval->y>0 || $interval->m>=$row['PeriodUnits']);
						break;
					}
				return $run_now;  
				}
			return true;
			}
		HDA_SendErrorMail("Time For Job query ".print_r(sqlsrv_errors(),true)." {$query} ");
		return true;
	}

	public function HDA_DB_jobTime($code) {
		$query = "SELECT * FROM hda_job_times WHERE ItemId='{$code}' ";
		if ($result = $this->_query($query)) {
			if ($row = $this->_fetch_row()) return $row;
			return NULL;
			}
		HDA_SendErrorMail("Getting Job Time ".print_r(sqlsrv_errors(),true)." {$query} ");
		return NULL;
	}


	public function HDA_DB_clearSchedule($item) {
		$query = "DELETE FROM hda_schedule WHERE ItemId='{$item}' ";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Clear Schedule ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}
	public function HDA_DB_writeSchedule($item, $datetime, $interval='HOUR', $units = 1, $pattern = null) {
		$fields = array();
		$fields['Pattern'] = (is_null($pattern))?"NULL":"'".$this->HDA_DB_textToDB($this->HDA_DB_serialize($pattern))."'";
		$fields['ItemId'] = "'{$item}'"; $fields['Scheduled']="'{$datetime}'"; $fields['RepeatInterval']="'{$interval}'"; $fields['Units']="'{$units}'";
		$query = "IF EXISTS (SELECT ItemId FROM hda_schedule WHERE ItemId='{$item}') ";
		$query .= "UPDATE hda_schedule SET ";
		foreach($fields as $field=>$v) $query .= "[{$field}]={$v},"; $query = trim($query,',');
		$query .= "ELSE INSERT INTO hda_schedule (";
		foreach($fields as $field=>$v) $query .= "[{$field}],"; $query = trim($query,',');
		$query .= ") VALUES (";
		foreach($fields as $field=>$v) $query .= "{$v},"; $query = trim($query,',');
		$query .= ") ";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Write to Schedule ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}
	public function HDA_DB_updateSchedule($item, $a) {
		if (is_array($a)) {
			$query = "UPDATE hda_schedule SET ";
			foreach ($a as $k=>$p) {
				switch ($k) {
					case 'Pattern':
						if (is_null($p)) $query .= "Pattern=NULL,";
						else $query .= "Pattern='".$this->HDA_DB_textToDB($this->HDA_DB_serialize($p))."',";
						break;
					default:
						if (is_null($p)) $query .= "{$k}=NULL,";
						else $query .= "{$k}='{$p}',";
						break;
					}
				}
			$query = trim($query,',');
			$query .= " WHERE ItemId='{$item}' ";
			if ($result = $this->_query($query)) return true;
			HDA_SendErrorMail("Update to Schedule ".print_r(sqlsrv_errors(),true)." {$query} ");
			}
		return false;
	}
	public function HDA_DB_getSchedule($item=null, $due_only=false) {
		$query = "SELECT hda_schedule.*,hda_profiles.Title,hda_profiles.Q";
		$query .= " FROM hda_schedule LEFT JOIN hda_profiles ON hda_profiles.ItemId = hda_schedule.ItemId ";
		$query .= " WHERE hda_schedule.ItemId IS NOT NULL ";
		if (!is_null($item)) $query .= "AND hda_schedule.ItemId='{$item}' ";
		if ($due_only) $query .= "AND Scheduled < GetDate() ";
		$query .= " ORDER BY Scheduled ASC";
		if ($result = $this->_query($query)) {
			$a = array();
			while($row = $this->_fetch_row()) {
				if (!is_null($row['Pattern'])) $row['Pattern'] = $this->HDA_DB_unserialize($this->HDA_DB_textFromDB($row['Pattern']));
				$row['Title'] = $this->HDA_DB_textFromDB($row['Title']);
				$a[] = $row;
				}
			return $a;
			}
		HDA_SendErrorMail("Get Schedule ".print_r(sqlsrv_errors(),true)." ($query} ");
		return NULL;
	}

	
	// END JOBS
	
	// NOTES
	
	public function HDA_DB_issueNote($parent, $note, $tagged=NULL, $user=NULL, $username=NULL) {
		global $UserCode;
		global $UserName;
		if (is_null($user)) { $user = $UserCode; $username= $UserName; }
		$code = $this->HDA_isUnique('NT');
		$query = "INSERT INTO hda_notes (ItemId, OwnerId, IssuedDate, ItemText, Tagged, NoteRelative) ";
		$query .= " VALUES(";
		$query .= "'{$code}', ";
		$query .= "'{$user}', ";
		$query .= "'".$this->PRO_DB_dateNow()."', ";
		$query .= "'".$this->HDA_DB_textToDB($note)."', ";
		$query .= "'{$tagged}', ";
		$query .= "'{$parent}' ";
		$query .= ")";
		if ($this->_query($query)) return $code;
		HDA_SendErrorMail("Issue Note ".print_r(sqlsrv_errors(),true)." {$query} ");
		return NULL;
	}
	public function HDA_DB_readNotes($in_item=NULL, $item=NULL, $since=NULL, $before=NULL, $limit=NULL, &$count=0) {
		if (!is_null($limit)) {
			$query = "SELECT Count(ItemId) as [c] FROM hda_notes WHERE ItemId IS NOT NULL ";
			if (!is_null($in_item)) $query .= "AND NoteRelative='{$in_item}' ";
			if (!is_null($item)) $query .= "AND ItemId='{$item}' ";
			if (!is_null($since)) $query .= "AND IssuedDate>'{$since}' ";
			if (!is_null($before)) $query .= "AND IssuedDate<'{$before}' ";
			if (($result=$this->_query($query)) && ($row=$this->_fetch_row())) $count=$row['c'];
			}
		$query = "SELECT TOP({$limit}) * FROM hda_notes WHERE ItemId IS NOT NULL ";
		if (!is_null($in_item)) $query .= "AND NoteRelative='{$in_item}' ";
		if (!is_null($item)) $query .= "AND ItemId='{$item}' ";
		if (!is_null($since)) $query .= "AND IssuedDate>'{$since}' ";
		if (!is_null($before)) $query .= "AND IssuedDate<'{$before}' ";
		$query .= "ORDER BY IssuedDate DESC ";
		if ($result = $this->_query($query)) {
			$a = array();
			while ($row=$this->_fetch_row()) {
				$row['ItemText'] = $this->HDA_DB_textFromDB($row['ItemText']);
				$a[] = $row;
				}
			if (is_null($limit)) $count=count($a);
			return $a;
			}
		HDA_SendErrorMail("Read Note ".print_r(sqlsrv_errors(),true)." {$query} ");
		return NULL;
	}
	public function HDA_DB_updateNote($item, $a) {
		if (array_key_exists('ItemText', $a)) $a['ItemText'] = $this->HDA_DB_textToDB($a['ItemText']);
		$query = "UPDATE hda_notes SET ";
		foreach ($a as $k=>$p) {
			$query .= "{$k} = '{$p}',";
			}
		$query[strlen($query)-1]=' ';
		$query .= " WHERE ItemId='{$item}' ";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Update note ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}
	public function HDA_DB_tidyNotes($ago=10) {
		$since = date('Y-m-d', strtotime("{$ago} days ago"));
		$query = "DELETE FROM hda_notes WHERE ";
		$query .= "(IssuedDate<'{$since}')";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Tidy Notes ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}
	public function HDA_DB_deleteNotes($in_item, $note_item=NULL) {
		$query = "DELETE FROM hda_notes WHERE NoteRelative='{$in_item}' ";
		if (!is_null($note_item)) $query .= "AND ItemId='{$note_item}' ";
		if ($result = $this->_query($query)) {
			return true;
			}
		HDA_SendErrorMail("Delete notes ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}	
	
	// END NOTES
	
	
	// RUN Q
	public function HDA_DB_addRunQ($code, $profileId, $user, $rcv_file, $rcv_file_name, $source, $source_info, $effective_date, $qLevel  = NULL) {
		global $UserCode;
		$now = $this->PRO_DB_dateNow();
		$profile = hda_db::hdadb()->HDA_DB_ReadProfile($profileId);
		if (is_null($qLevel)) $qLevel =(!is_null($profile) && is_array($profile))?$profile['Q']:0;
		if (is_null($code)) $code = $this->HDA_isUnique('PQ');
		if (is_null($effective_date)) $effective_date = $now;
		if (is_null($user)) $user = $UserCode;
		$source_info = $this->HDA_DB_textToDB($source_info);
		$title = _clean($profile['Title']);
		$q_set = array('ItemId'=>$code,'OwnerId'=>$user, 'ProfileItem'=>"$profileId", 'ProcessState'=>'ReadyToRun', 'Title'=>$title, 
		'QueueLevel'=>$qLevel,'RcvFile'=>$rcv_file,'RcvFileName'=>$rcv_file_name,'Source'=>$source,'SourceInfo'=>$source_info,
		'IssuedDate'=>$now,'EffectiveDate'=>$effective_date, 'ProcessResult'=>0);
		$query = "IF EXISTS(SELECT ItemId FROM hda_runQ WHERE ItemId='{$code}') ";
		$query .= "UPDATE hda_runQ SET  ";
		foreach ($q_set as $col=>$v) {
			$query .= "{$col}=";
			$query .= (is_null($v))?"NULL":"'{$v}'";
			$query .= ",";
		}
		$query = trim($query,',');
		$query .= " WHERE ItemId='{$code}'";
		$query .= " ELSE ";
		$query .= " INSERT INTO hda_runQ (";
		foreach ($q_set as $col=>$v) $query .= "{$col},";
		$query = trim($query,',');
		$query .= ") VALUES (";
		foreach ($q_set as $col=>$v) {
			$query .= (is_null($v))?"NULL":"'{$v}'";
			$query .= ",";
		}
		$query = trim($query,',');
		$query .= ")";
		
		if ($result=$this->_query($query)) return $code;
		HDA_SendErrorMail("Add to pending Q ".print_r(sqlsrv_errors(),true)." {$query} ");
		return NULL;
	}
	public function HDA_DB_runQEntry($profileId) {
		$code = $this->HDA_DB_addRunQ(NULL, $profileId, NULL, NULL, NULL, 'HDAW', NULL, NULL, NULL);
		$a = $this->HDA_DB_pendingQ(NULL, $code);
		if (is_array($a) && count($a)==1) return $a[0];
		return NULL;
	}

	public function HDA_DB_updatePending($code, $a) {
		$query = "UPDATE hda_runQ SET ";
		foreach ($a as $k=>$p) {
			$query .= (is_null($p))?("{$k}=NULL,"):("{$k}='{$p}',");
			}
		$query = trim($query, ",");
		if (!is_null($code)) $query .= " WHERE ItemId='{$code}' ";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Update run Q ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}


	public function HDA_DB_pendingQ($forProfileItem=NULL, $forEntry=NULL, $qLevel=NULL, $getRunnable=false) {
		$query = "SELECT  * FROM hda_runQ WHERE (ProfileItem IS NOT NULL) ";
		if (!is_null($forProfileItem)) $query .= " AND (ProfileItem='{$forProfileItem}') ";
		if (!is_null($forEntry)) $query .= " AND (ItemId='{$forEntry}') ";
		if ($qLevel===0) $query .= " AND ((QueueLevel IS NULL) OR (QueueLevel=0)) ";
		elseif (!is_null($qLevel)) $query .= " AND (QueueLevel={$qLevel})";
		if ($getRunnable) $query .= " AND ((ProcessState='ReadyToRun') OR (ProcessState='RESTART')) ";
		$query .= " ORDER BY IssuedDate ASC";
		if ($result = $this->_query($query)) {
			$a = array();
			while ($row = $this->_fetch_row()) {
				$row['SourceInfo'] = $this->HDA_DB_textFromDB($row['SourceInfo']);
				$a[] = $row;
				}
			return $a;
			}
		HDA_SendErrorMail("Get run Q ".print_r(sqlsrv_errors(),true)." {$query} ");
		return NULL;
	}

	public function HDA_DB_inPendingQ($profile_item) {
		$query = "SELECT * FROM hda_runQ WHERE (ProfileItem ='{$profile_item}' ) ";
		if ($result = $this->_query($query)) {
			if ($row = $this->_fetch_row()) return ((is_null($row['ProcessState']))?'WAITING':$row['ProcessState']);
			return false;
			}
		HDA_SendErrorMail("Test in Q ".print_r(sqlsrv_errors(),true)." {$query} ");
		return NULL;
	}



	public function HDA_DB_RunPending($item) {
		$query = "UPDATE hda_runQ SET ProcessState='RUNNING' WHERE ItemId='{$item}' ";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Will Run Pending ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}

	public function HDA_DB_RemovePending($item=NULL) {
		HDA_LogThis("Remove Pending del {$item}");
		$query = "DELETE FROM hda_runQ WHERE ItemId='{$item}' ";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Remove pending item ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}

	// END RUN Q
	
	
	// AUDIT TIMES
	
	public function HDA_DB_clearTimings() {
		$query = "DELETE FROM hda_times";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Clear Times ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}  
	public function HDA_DB_timings($item=NULL, $time=NULL, $records=NULL) {
		if (is_null($time) && is_null($records)) {
			$query = "SELECT hda_times.*,hda_profiles.Q,hda_profiles.Title FROM hda_times LEFT JOIN hda_profiles ON hda_times.ItemId=hda_profiles.ItemId ";
			if (!is_null($item)) $query .= " WHERE hda_profiles.ItemId = '{$item}' ";
			$query .= " ORDER BY hda_times.HighTime DESC";
			if ($result = $this->_query($query)) {
				$a=array();
				while ($row = $this->_fetch_row()) {
					$row['Title'] = $this->HDA_DB_textFromDB($row['Title']);
					$a[] = $row;
					}
				return $a;
				}
			HDA_SendErrorMail("Get Timings ".print_r(sqlsrv_errors(),true)." {$query} ");
			return null;
			}
		else {
			$query = "SELECT * FROM hda_times WHERE ItemId='{$item}' ";
			$max = $time_count = $high_count = 0;
			if ($result = $this->_query($query)) {
				if ($row = $this->_fetch_row()) {
					$max = $row['HighTime'];
					$time_count = $row['RunCount'];
					$high_count = $row['HighRecordCount'];
					}
				}
			if (!is_null($time)) $max = max($time, $max);
			if (!is_null($time)) $time_count++;
			if (!is_null($records)) $high_count = max($high_count, $records);
			$fields = array();
			$fields['ItemId'] = "'{$item}'";
			$fields['HighTime'] = "'{$max}'";
			$fields['RunCount'] = "'{$time_count}'";
			$fields['HighRecordCount'] = "'{$high_count}'";
			$query = "IF EXISTS (SELECT ItemId FROM hda_times WHERE ItemId='{$item}') ";
			$query .= "UPDATE hda_times SET ";
			foreach($fields as $field=>$v) $query .= "[{$field}]={$v},"; $query = trim($query,',');
			$query .= " WHERE ItemId='{$item}' ";
			$query .= " ELSE INSERT INTO hda_times (";
			foreach($fields as $field=>$v) $query .= "[{$field}],"; $query = trim($query,',');
			$query .= ") VALUES (";
			foreach($fields as $field=>$v) $query .= "{$v},"; $query = trim($query,',');
			$query .= ") ";
			if ($result = $this->_query($query)) return true;
			}
		HDA_SendErrorMail("Set Timings ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}	
	// END AUDIT TIMES
	
	// AUDIT FILES
	
	public function HDA_DB_reportEvents() {
		$query = "SELECT hda_events.*,hda_profiles.Title,hda_profiles.Category,hda_tree.Enabled as 'TreeEnabled' FROM hda_events ";
		$query .= "LEFT JOIN hda_profiles ON hda_profiles.ItemId=hda_events.ItemId ";
		$query .= "LEFT JOIN hda_tree ON hda_events.ItemId=hda_tree.ItemId ";
		$query .= "WHERE ((EventCode LIKE '%_SUCCESS') OR (EventCode LIKE '%_FAILURE') OR (EventCode LIKE '%_LATE')) ";
		$query .= " AND (DATE(hda_events.IssuedDate)=CURDATE()) ";
		$query .= " AND ((hda_tree.Enabled & 2)=0) ";
		if ($result = $this->_query($query)) {
			$a = array();
			while ($row = $this->_fetch_row()) {
				$row['EventValue'] = $this->HDA_DB_textFromDB($row['EventValue']);
				$row['Title'] = $this->HDA_DB_textFromDB($row['Title']);
				$row['Category'] = $this->HDA_DB_textFromDB($row['Category']);
				$a[] = $row;
				}
			return $a;
			}
		HDA_SendErrorMail("Report Events ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}
	public function HDA_DB_reportAutoTrigger() {
		$query = "SELECT hda_auto_log.*,hda_profiles.Title,hda_profiles.Category,hda_tree.Enabled as 'TreeEnabled' FROM hda_auto_log ";
		$query .= "LEFT JOIN hda_profiles ON hda_profiles.ItemId=hda_auto_log.ItemId ";
		$query .= "LEFT JOIN hda_tree ON hda_auto_log.ItemId=hda_tree.ItemId ";
		$query .= " WHERE (DATE(hda_auto_log.IssuedDate)=CURDATE()) ";
		$query .= " AND ((hda_tree.Enabled & 2)=0) ";
		if ($result = $this->_query($query)) {
			$a = array();
			while ($row = $this->_fetch_row()) {
				$row['Title'] = $this->HDA_DB_textFromDB($row['Title']);
				$row['ItemText'] = $this->HDA_DB_textFromDB($row['ItemText']);
				$row['Category'] = $this->HDA_DB_textFromDB($row['Category']);
				$a[] = $row;
				}
			return $a;
			}
		HDA_SendErrorMail("Report Auto ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}
	public function HDA_DB_reportAudit() {
		$query = "SELECT hda_audit.*,hda_profiles.Title,hda_profiles.Category,hda_tree.Enabled as 'TreeEnabled' FROM hda_audit ";
		$query .= "LEFT JOIN hda_profiles ON hda_profiles.ItemId=hda_audit.ItemId ";
		$query .= "LEFT JOIN hda_tree ON hda_audit.ItemId=hda_tree.ItemId ";
		$query .= " WHERE (DATE(hda_audit.IssuedDate)=CURDATE()) ";
		$query .= " AND ((hda_tree.Enabled & 2)=0) ";
		$query .= " ORDER BY Title,IssuedDate DESC";
		if ($result = $this->_query($query)) {
			$a = array();
			while ($row = $this->_fetch_row()) {
				$row['Title'] = $this->HDA_DB_textFromDB($row['Title']);
				$row['ItemText'] = $this->HDA_DB_textFromDB($row['ItemText']);
				$row['OriginalFilePath'] = $this->HDA_DB_textFromDB($row['OriginalFilePath']);
				$row['TargetDB'] = $this->HDA_DB_textFromDB($row['TargetDB']);
				$row['Category'] = $this->HDA_DB_textFromDB($row['Category']);
				$a[] = $row;
				}
			return $a;
			}
		HDA_SendErrorMail("Report Audit ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}
	public function HDA_DB_reportAuditFiles($item=null, $starting_date=null, $ending_date=null) {
		$source_like = $this->HDA_DB_textToDB("Load Init");
		$source_or_like = $this->HDA_DB_textToDB("AUDIT FILE");
		$query = "SELECT hda_audit.OriginalFilePath,hda_audit.IssuedDate,hda_audit.ItemText,hda_profiles.Title FROM hda_audit ";
		$query .= "LEFT JOIN hda_profiles ON hda_profiles.ItemId=hda_audit.ItemId ";
		$query .= "WHERE hda_audit.ItemId IS NOT NULL AND hda_audit.OriginalFilePath IS NOT NULL  ";
		if (!is_null($item)) $query .= " AND hda_audit.ItemId='{$item}' ";
		if (!is_null($starting_date)) $query .= " AND hda_audit.IssuedDate>='{$starting_date}' ";
		if (!is_null($ending_date)) $query .= " AND hda_audit.IssuedDate<='{$ending_date}' ";
		$query .= "AND (hda_audit.ItemText LIKE '{$source_like}%' OR hda_audit.ItemText LIKE '{$source_or_like}%') ";
		$query .= "ORDER BY hda_audit.IssuedDate DESC";
		if ($result = $this->_query($query)) {
			$a = array();
			while ($row = $this->_fetch_row()) {
				$row['OriginalFilePath'] = $this->HDA_DB_textFromDB($row['OriginalFilePath']);
				$row['ItemText'] = $this->HDA_DB_textFromDB($row['ItemText']);
				$row['Title'] = $this->HDA_DB_textFromDB($row['Title']);
				$a[] = $row;
				}
			return $a;
			}
		HDA_SendErrorMail("Audit File List ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}
	public function HDA_DB_reportAuditTime($use_date = null, $tod = "12:00", $ago = 1) {
		if (is_null($use_date)) $use_date = date('Y-m-d', strtotime("{$ago} days ago"));
		$query = "SELECT hda_profiles.Title, hda_audit.OriginalFilePath, hda_audit.IssuedDate, hda_audit.ItemText FROM hda_audit";
		$query .= " LEFT JOIN hda_profiles ON (hda_profiles.ItemId=hda_audit.ItemId) ";
		$query .= "WHERE hda_audit.OriginalFilePath IS NOT NULL AND hda_audit.ItemText LIKE '%AUDIT%' ";
		$query .= " AND hda_audit.IssuedDate>DATE('{$use_date}') AND TIME(hda_audit.IssuedDate)>'{$tod}' ";
		if ($result = $this->_query($query)) {
			$a = array();
			while ($row = $this->_fetch_row()) {
				$row['Title'] = $this->HDA_DB_textFromDB($row['Title']);
				$row['OriginalFilePath'] = $this->HDA_DB_textFromDB($row['OriginalFilePath']);
				$row['ItemText'] = $this->HDA_DB_textFromDB($row['ItemText']);
				$a[] = $row;
				}
			return $a;
			}
		HDA_SendErrorMail("Report Audit Time ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}
   	
	// END AUDIT FILES
	
	// EVENTS AND TICKET USE
	
	public function HDA_DB_reportTickets() {
		$since = $this->PRO_DB_Date(strtotime('yesterday'));
		$query = "SELECT hda_tickets.UserName,hda_tickets.LastData,hda_tickets.LastUseDate,hda_profiles.Title FROM hda_tickets ";
		$query .= "LEFT JOIN hda_profiles ON hda_profiles.ItemId=hda_tickets.ProfileId ";
		$query .= " WHERE (DATE(hda_tickets.LastUseDate)>'{$since}') ";
		$query .= " ORDER BY Title,LastUseDate DESC";
		if ($result = $this->_query($query)) {
			$a = array();
			while ($row = $this->_fetch_row()) {
				$row['Title'] = $this->HDA_DB_textFromDB($row['Title']);
				$row['LastData'] = $this->HDA_DB_textFromDB($row['LastData']);
				$row['UserName'] = $this->HDA_DB_textFromDB($row['UserName']);
				$a[] = $row;
				}
			return $a;
			}
		HDA_SendErrorMail("Report Tickets ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}

	public function HDA_DB_eventSummary($today='TODAY',$days_ago=0, $filter=null) {
		switch ($today) {
			default: $qd = "(IssuedDate IS NOT NULL)"; break;
			case 'TODAY': $d = date('Y-m-d', strtotime('today')); $qd = "(IssuedDate > '{$d}')"; break;
			case 'YESTERDAY': $d = date('Y-m-d', strtotime('yesterday')); $qd = "(CAST(IssuedDate AS DATE)='{$d}') "; break;
			case 'THISMONTH':
				$month = date('n', time());
				$year = date('Y', time());
				$qd = "((DATEPART(month,IssuedDate)={$month}) AND (DATEPART(YEAR,IssuedDate)={$year})) ";
				break;
			case 'THISWEEK':
				$week = date('W'); $year = date('Y');
				$qd = "((DATEPART(WEEK,IssuedDate)={$week}) AND (DATEPART(YEAR,IssuedDate)={$year})) ";
				break;
			case 'DAYSAGO':
				$ago = date ('Y-m-d',strtotime("{$days_ago} days ago"));
				$qd = "(IssuedDate>'{$ago}') ";
			break;
			}
		if (is_null($filter) || !is_array($filter) || count($filter)==0) $filter = array('LATE','FAILURE');
		$query = "SELECT hda_profiles.Title,hda_events.EventCode,hda_events.EventValue,hda_events.IssuedDate FROM hda_profiles ";
		$query .= " LEFT JOIN hda_events ON hda_profiles.ItemId=hda_events.ItemId ";
		$query .= " WHERE {$qd} ";
		$query .= " AND ( ";
		foreach ($filter as $ev) {
			$query .= " (EventCode LIKE '%_{$ev}') OR";
			}
		$query = trim($query,'OR')." ) ";
		$query .= " ORDER BY Title ASC";
		if ($result = $this->_query($query)) {
			$a = array();
			while ($row = $this->_fetch_row()) {
				$row['EventValue'] = $this->HDA_DB_textFromDB($row['EventValue']);
				$a[] = $row;
				}
			return $a;
			}
		HDA_SendErrorMail("Read Event Summary".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}


	public function HDA_DB_events($item=NULL, $code=NULL, $code_value=NULL, $today='TODAY', $days_ago = 0) {
		if (is_null($code_value)) {
			$query = "SELECT hda_events.*,hda_profiles.Title FROM hda_events ";
			$query .= " JOIN hda_profiles on hda_profiles.ItemId=hda_events.ItemId";
			$query .= " WHERE hda_events.ItemId IS NOT NULL ";
			if (!is_null($item)) $query .= "AND hda_events.ItemId='{$item}' ";
			if (!is_null($code)) $query .= "AND EventCode='{$code}' ";
			switch (strtoupper($today)) {
				case 'TODAY':
					$query .= "AND DATE(IssuedDate)=CURRENT_DATE()";
					break;
				case 'YESTERDAY':
					$date = date('Y-m-d', strtotime('yesterday'));
					$query .= "AND DATE(IssuedDate)='{$date}' ";
					break;
				case 'THISMONTH':
					$month = date('n', time());
					$year = date('Y', time());
					$query .= "AND (MONTH(IssuedDate)='{$month}') AND (YEAR(IssuedDate)='{$year}') ";
					break;
				case 'THISWEEK':
					$week = date('W'); $year = date('Y');
					$query .= "AND ((DATEPART(WEEK,IssuedDate)={$week}) AND (DATEPART(YEAR,IssuedDate)={$year})) ";
					break;
				case 'DAYSAGO':
					$ago = date('Y-m-d',strtotime("{$days_ago} days ago"));
					$query .= "AND (IssuedDate > '{$ago}') ";
					break;
				}
			$query .= " ORDER BY Title ASC";
			if ($result = $this->_query($query)) {
				$a = array();
				while ($row = $this->_fetch_row()) {
					$row['EventValue'] = $this->HDA_DB_textFromDB($row['EventValue']);
					$a[] = $row;
					}
				return (count($a)>0)?$a:null;
				}
			HDA_SendErrorMail("Read Event ".print_r(sqlsrv_errors(),true)." {$query} ");
			return null;
			}
		else {
			$query = "IF EXISTS (SELECT ItemId FROM hda_events WHERE ItemId='{$item}') ";
			$query .= "UPDATE hda_events SET IssuedDate=GetDate(),EventCode='{$code}',EventValue='".$this->HDA_DB_textToDB($code_value)."' ";
			$query .= "WHERE  ItemId='{$item}' ELSE INSERT INTO hda_events (ItemId, IssuedDate, EventCode, EventValue) ";
			$query .= "VALUES ('{$item}', GetDate(), '{$code}', '".$this->HDA_DB_textToDB($code_value)."') ";
			if ($result=$this->_query($query)) return true;
			HDA_SendErrorMail("Write Event ".print_r(sqlsrv_errors(),true)." {$query} ");
			return false;
			}
	}
	public function HDA_DB_ResetSysEvents($item) {
		if ($this->HDA_DB_relationEnabled($item)) {
			$query = "DELETE FROM hda_events WHERE ItemId='{$item}' AND (EventCode='{$item}_SUCCESS' OR EventCode='{$item}_FAILURE' OR EventCode='{$item}_LATE') ";
			if ($result = $this->_query($query)) return true;
			HDA_SendErrorMail("Clear Sys Events ".print_r(sqlsrv_errors(),true)." {$query} ");
			}
		return false;
	}

	public function HDA_DB_ClearSuccess($item) {
		if ($this->HDA_DB_relationEnabled($item)) {
			$this->_invalidateTreeFrom($item);
			$query = "DELETE FROM hda_events WHERE ItemId='{$item}' AND (EventCode='{$item}_SUCCESS' OR EventCode='{$item}_LATE') ";
			if ($result = $this->_query($query)) {
				$query = "IF EXISTS (SELECT ItemId FROM hda_events WHERE ItemId='{$item}') ";
				$query .= "UPDATE hda_events SET IssuedDate=GetDate(),EventValue=EventValue+1 ";
				$query .= "WHERE  ItemId='{$item}' ELSE INSERT INTO hda_events (ItemId, IssuedDate, EventCode, EventValue) ";
				$query .= " VALUES ('{$item}',GetDate(),'{$item}_FAILURE',1) ";
				if ($result = $this->_query($query)) return true;
				}
			HDA_SendErrorMail("Clear Success Event ".print_r(sqlsrv_errors(),true)." {$query} ");
			}
		return false;
	}
	public function HDA_DB_EventSuccess($item) {
		if ($this->HDA_DB_relationEnabled($item)) {
			$this->_invalidateTreeFrom($item);
			$this->HDA_DB_events($item, $code="{$item}_SUCCESS", $code_value="_SYS_SUCCESS_MARK_");
			$query = "DELETE FROM hda_events WHERE ItemId='{$item}' AND (EventCode='{$item}_FAILURE'  OR EventCode='{$item}_LATE') ";
			if ($result = $this->_query($query)) return true;
			HDA_SendErrorMail("Set Success Event ".print_r(sqlsrv_errors(),true)." {$query} ");
			}	  
	}
	public function HDA_DB_SetLateEvent($item, $count=1) {
		$query = "IF EXISTS(SELECT ItemId FROM hda_events WHERE ItemId='{$item}') ";
		$query .= " UPDATE hda_events SET EventValue={$count},IssuedDate=GetDate() ";
		$query .= " ELSE ";
		$query .= "INSERT INTO hda_events (ItemId,IssuedDate,EventCode,EventValue) VALUES ('{$item}',GetDate(),'{$item}_LATE',0) ";
		if ($result = $this->_query($query)) {
			return ($this->mysql->affected_rows==1);
			}
		HDA_SendErrorMail("Set Late Event ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}
	public function HDA_DB_IsLateEvent($item) {
		$since = $this->PRO_DB_Date(strtotime("today"));
		$query = "DELETE FROM hda_events WHERE (IssuedDate<'{$since}') AND (EventCode='{$item}_LATE')";
		$this->_query($query);
		$a = $this->HDA_DB_events($item, $code="{$item}_LATE", NULL, NULL, NULL);
		if (is_array($a) && count($a)==1) {
			$this->HDA_DB_SetLateEvent($item, 2);
			return $a[0]['EventValue'];
			}
		return 0;
	}
	public function HDA_DB_CheckForLateEvent($item) {
		$since = $this->PRO_DB_Date(strtotime("today"));
		$query = "SELECT EventValue FROM hda_events WHERE (ItemId='{$item}') AND (EventCode='{$item}_LATE') AND (IssuedDate>'{$since}')";
		if ($result = $this->_query($query)) {
			if ($row = $this->_fetch_row()) return $row[0];
			return false;
			}
		HDA_SendErrorMail("Check Late Event ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}



	public function HDA_DB_SuccessEventDate($item) {
		$a = $this->HDA_DB_events($item, $code="{$item}_SUCCESS", NULL, NULL, NULL);
		if (is_array($a) && count($a)==1) return $a[0]['IssuedDate'];
		return null;
	}
	public function HDA_DB_HasFailureEvent($item, &$tries) {
		$a = $this->HDA_DB_events($item, $code="{$item}_FAILURE", NULL, NULL, NULL);
		if (is_array($a) && count($a)==1) {$tries = $a[0]['EventValue']; return $a[0]['IssuedDate'];}
		return false;
	}
	public function HDA_DB_AllSuccessEvents() {
		$query = "SELECT ItemId, IssuedDate FROM hda_events WHERE EventValue='_SYS_SUCCESS_MARK_' ";
		if ($result = $this->_query($query)) {
			$a = array();
			while ($row = $this->_fetch_row()) $a[$row['ItemId']] = $row['IssuedDate'];
			return $a;
			}
		HDA_SendErrorMail("Get all SUCCESS events ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}
	public function HDA_DB_tidyEvents($ago=40) {
		$since = date('Y-m-d', strtotime("{$ago} days ago"));
		$query = "DELETE FROM hda_events WHERE ";
		$query .= "(IssuedDate<'{$since}') AND (EventValue<>'_SYS_SUCCESS_MARK_')";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Tidy Events ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}
	
	// END EVENT REPORTS
	
	// ADMIN
	public function HDA_DB_admin($code, $a=NULL) {
		if (is_null($a)) {
			$query = "SELECT ItemText as [t] FROM hda_admin WHERE ItemId='{$code}'";
			if ($result = $this->_query($query)) {
				if ($row = $this->_fetch_row()) return $this->HDA_DB_textFromDB($row['t']);
				return NULL;
				}
			HDA_SendErrorMail("Admin Fetch ".print_r(sqlsrv_errors(),true)." {$query} ");
			return NULL;
			}
		else {
			$query = "IF EXISTS(SELECT ItemId FROM hda_admin WHERE ItemId='{$code}') ";
			$query .= "UPDATE hda_admin SET  ItemText='".$this->HDA_DB_textToDB($a)."' ";
			$query .= "WHERE ItemId='{$code}' ";
			$query .= "ELSE INSERT INTO hda_admin (ItemId, ItemText) ";
			$query .= "VALUES ('{$code}','".$this->HDA_DB_textToDB($a)."' )";
			if ($result = $this->_query($query)) return true;
			HDA_SendErrorMail("Update Admin ".print_r(sqlsrv_errors(),true)." {$query} ");
			return false;
			}
	}

	public function HDA_DB_throttle($key, $limit) {
		$query = "SELECT KeyCount FROM hda_throttles WHERE ItemId='{$key}' AND IssuedDate=CURDATE()";
		$count = 0;
		if ($result = $this->_query($query)) {
			if ($row = $this->_fetch_row()) $count = $row[0];
			if ($count<=$limit) {
				$count++;
				$query = "IF EXISTS (SELECT ItemId FROM hda_throttles WHERE ItemId='{$key}') ";
				$query .= "UPDATE hda_throttles SET KeyCount={$count},IssuedDate=CURDATE()";
				$query .= "WHERE ItemId='{$key}' ELSE INSERT INTO hda_throttles (ItemId, KeyCount, IssuedDate) ";
				$query .= "('{$key}', {$count}, GetDate()) ";
				if ($result = $this->_query($query)) return true;
				HDA_SendErrorMail("Throttle Inc ".print_r(sqlsrv_errors(),true)." {$query} ");
				return false;
				}
			return false;
			}
		HDA_SendErrorMail("Trottle Check ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}	
	// END ADMIN
	
	
	// MONITOR
	
	public function HDA_DB_monitorRegister($item, $name, $q, $pendingQ, $sessid, $pid=0) {
	   if (!is_numeric($pid)) $pid = 0;
	   $query = "DELETE FROM hda_monitor WHERE ItemId='{$item}' ";
	   $this->_query($query);
	   $query = "INSERT INTO hda_monitor (ItemId,Title,EntryTime,SessionId,PID,Status,Pulse,InQ,ItemQ,Mem,ItemText)";
	   $query .=" VALUES('{$item}','{$name}',GetDate(),'{$sessid}','{$pid}','WAITING',GetDate(),'{$q}','{$pendingQ}',".memory_get_usage().",'Register:') ";
	   if ($result=$this->_query($query)) return true;
	   HDA_SendErrorMail("Register Monitor Entry ".print_r(sqlsrv_errors(),true)." {$query} ");
	   return false;
	   }
	public function HDA_DB_monitorFind($item) {
	   $query = "SELECT SessionId FROM hda_monitor WHERE ItemId='{$item}' ";
	   if ($result = $this->_query($query)) {
		  $a = array();
		  while ($row = $this->_fetch_row()) {
			 $a[] = $row;
			 }
		  return $a;
		  }
	   HDA_SendErrorMail("Find Monitor ".print_r(sqlsrv_errors(),true)." {$query} ");
	   return null;
	   }
	public function HDA_DB_monitor($line=NULL, $instr=NULL, $sessid=NULL, $status='RUNNING') {
	   if (is_null($sessid)) $sessid = session_id();
	   $row = $this->HDA_DB_monitorRead($sessid);
	   $insert = false;
	   switch ($status) {
		  case 'FINISHED':
		  case 'WAITING':
			 $insert = true;
			 break;
		  case 'ABORTED':
		  case 'RUNNING':
			 $insert = (is_array($row) && ($row['Status'] != $status));
			 break;
		  case 'GONE':
			 $insert = is_array($row);
			 break;
		  }
	   if ($insert) {
		  $query = "UPDATE hda_monitor SET  Status='{$status}', ItemText='Monitor:'";
		  if (!is_null($line)) $query .= ", LineNumber='{$line}' ";
		  if (!is_null($instr)) $query .= ", InstrCount='{$instr}' ";
		  $query .= ",Pulse=GetDate(), ";
		  $query .= "Mem=".memory_get_usage()." ";
		  $query .= "WHERE SessionId='{$sessid}' ";
		  if ($result = $this->_query($query)) return $status;
		  HDA_SendErrorMail("Monitor ".print_r(sqlsrv_errors(),true)." {$query} ");
		  }
	   return (!is_null($row))?$row['Status']:$status;
	   }
	public function HDA_DB_monitorRead($sessid=NULL, $item=NULL, $pid=NULL) {
	   $query = "SELECT * FROM hda_monitor WHERE ";
	   if (!is_null($sessid)) $query .= "SessionId='{$sessid}' ";
	   elseif (!is_null($item))  $query .= "ItemId='{$item}' ";
	   elseif (!is_null($pid))  $query .= "PID='{$pid}' ";
	   if ($result = $this->_query($query)) {
		  if ($row = $this->_fetch_row()) return $row;
		  return null;
		  }
	   HDA_SendErrorMail("Monitor Read ".print_r(sqlsrv_errors(),true)." {$query} ");
	   return null;
	   }
	public function HDA_DB_getMonitor() {
	   $query = "SELECT * FROM hda_monitor  ";
	   if ($result = $this->_query($query)) {
		  $a = array();
		  while ($row = $this->_fetch_row()) $a[] = $row;
		  return $a;
		  }
	   HDA_SendErrorMail("Monitor Read ".print_r(sqlsrv_errors(),true)." {$query} ");
	   return null;
	   }
	public function HDA_DB_putMonitorMessage($msg, $on_line=null, $sessid=null) {
	   if (is_null($sessid)) $sessid = session_id();
	   $query = "UPDATE hda_monitor SET  ItemText='Put: {$msg}'";
	   if (!is_null($on_line)) $query .= ",LineNumber={$on_line}";
	   $query .= ",Pulse=GetDate() ";
	   $query .= ",Mem=".memory_get_usage()." ";
	   $query .= " WHERE SessionId='{$sessid}' ";
	   if ($result = $this->_query($query)) return true;
	   return null;
	   }

	public function HDA_DB_monitorClear($sessid = NULL, $item = NULL) {
	   $query = "DELETE FROM hda_monitor WHERE ItemId IS NOT NULL ";
	   if (!is_null($item)) $query .= "AND (ItemId='{$item}') ";
	   elseif (!is_null($sessid)) $query .= "AND (SessionId='{$sessid}') ";
	   if ($result = $this->_query($query)) return true;
	   HDA_SendErrorMail("Clear Monitor ".print_r(sqlsrv_errors(),true)." {$query} ");
	   return false;
	   }
	   
	public function HDA_DB_tidyMonitor($ago=6) {
	   $since = $this->PRO_DB_DateTime(strtotime("{$ago} hours ago"));
	   $pulsed = $this->PRO_DB_DateTime(strtotime("1 hour ago"));
	   $query = "DELETE FROM hda_monitor WHERE (EntryTime<'{$since}') AND (Pulse<'{$pulsed}') ";
	   if ($result = $this->_query($query)) return true;
	   HDA_SendErrorMail("Tidy Monitor ".print_r(sqlsrv_errors(),true)." {$query} ");
	   return false;
	   }
	public function HDA_DB_monitorQ($q=0) {
	   $query = "SELECT COUNT(*) AS InQCount FROM hda_monitor WHERE InQ='{$q}' ";
	   if ($result = $this->_query($query)) {
		  if ($row = $this->_fetch_row()) return $row['InQCount'];
		  return 0;
		  }
	   HDA_SendErrorMail("In Q Count ".print_r(sqlsrv_errors(),true)." {$query} ");
	   return 0;
	   }
	
	// END MONITOR
	
	// MARKERS
	
	public function HDA_DB_readMarkers($item=null) {
		$query = "SELECT * FROM hda_markers WHERE ItemId IS NOT NULL ";
		if (!is_null($item)) $query .= "AND ProfileItem='{$item}' ";
		if ($result = $this->_query($query)) {
			$a = array();
			while ($row = $this->_fetch_row()) {
				$row['ItemId'] = $this->HDA_DB_textFromDB($row['ItemId']);
				$row['ItemText'] = $this->HDA_DB_textFromDB($row['ItemText']);
				$a[] = $row;
				}
			return $a;
			}
		HDA_SendErrorMail("Read Markers ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}

	public function HDA_DB_clearMarkers($item = null) {
		$query = "DELETE FROM hda_markers WHERE ItemId IS NOT NULL ";
		if (!is_null($item)) $query .= "AND ProfileItem='{$item}' ";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Clear Markers ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}

	public function HDA_DB_readMarker($id) {
		$id = $this->HDA_DB_textToDB($id);
		$query = "SELECT ItemText FROM hda_markers WHERE ItemId='{$id}' ";
		if ($result = $this->_query($query)) {
			if ($row = $this->_fetch_row()) {
				return $this->HDA_DB_textFromDB($row['ItemText']);
				}
			return null;
			}
		HDA_SendErrorMail("Read Marker ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}

	public function HDA_DB_writeMarker($id, $value, $expires=1, $by_profile=null) {
		$id = $this->HDA_DB_textToDB($id);
		$value = $this->HDA_DB_textToDB($value);
		$query = "IF EXISTS(SELECT ItemId FROM hda_markets WHERE ItemId='{$id}') ";
		$query .= "UPDATE hda_markers SET ";
		$by_profile = (!is_null($by_profile))?"'{$by_profile}'":"NULL";
		$query .= "ProfileItem={$by_profile},Expires='{$expires}',IssuedDate=GetDate(),ItemText='{$value}' ";
		$query .= "WHERE ItemId='{$id}' ";
		$query .= "ELSE INSERT INTO hda_markers (ItemId, ProfileItem, Expires,IssuedDate,ItemText) ";
		$query .= "VALUES ('{$id}', {$by_profile}, '{$expires}', GetDate(), '{$value}') ";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Read Marker ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}	
	// END MARKERS
	
	
	// COLLECTS
	
	public function HDA_DB_listCollects($category=NULL) {
		$query = "SELECT hda_profiles.ItemId,hda_profiles.Title, hda_collects.ItemText, hda_collects.Status as 'WillCollect', hda_tree.Enabled as 'TreeEnabled', tkt.Tickets, hda_auto_log.ItemText as AutoLog ";
		$query .= " FROM hda_profiles LEFT JOIN hda_collects ON hda_profiles.ItemId=hda_collects.ItemId ";
		$query .= " LEFT JOIN hda_tree ON hda_tree.ItemId=hda_profiles.ItemId ";
		$query .= " LEFT JOIN (SELECT ProfileId, Count(*) AS 'Tickets' FROM hda_tickets GROUP BY hda_tickets.ProfileId) as tkt ON tkt.ProfileId=hda_profiles.ItemId ";
		$query .= " LEFT JOIN hda_auto_log ON hda_profiles.ItemId=hda_auto_log.ItemId ";
		if (!is_null($category)) {$category = $this->HDA_DB_textToDB($category); $query .= "WHERE hda_profiles.Category='{$category}' ";}
		$query .= " ORDER BY Title DESC";
		if ($result = $this->_query($query)) {
			$a = array();
			while ($row = $this->_fetch_row()) {
				$row['ItemText'] = $this->HDA_DB_textFromDB($row['ItemText']);
				$row['AutoLog'] = $this->HDA_DB_textFromDB($row['AutoLog']);
				$row['Tickets'] = (!is_null($row['Tickets']) && $row['Tickets']>0)?$row['Tickets']:"";
				$row['IsProxy'] = (($row['TreeEnabled']&2)==2)?"Yes":"";
				$row['AutoRules'] = (($row['TreeEnabled']&1)==1)?"Yes":"";
				$a[] = $row;
				}
			return $a;
			}
		HDA_SendErrorMail("List Collects ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}
	public function HDA_DB_willCollect($item) {
		$query = "SELECT Status FROM hda_collects WHERE (ItemId='{$item}') AND (Status=1) ";
		if ($result = $this->_query($query)) {
			if ($row = $this->_fetch_row()) return true;
			return false;
			}
		HDA_SendErrorMail("Will Collect Test ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}

	public function HDA_DB_autoCollect($item=NULL, $xname=NULL, $enabled=0) {
		if (is_null($xname)) {
			$query = "SELECT * FROM hda_collects WHERE ItemId IS NOT NULL ";
			if (!is_null($item)) $query .= "AND (ItemId='{$item}') ";
			if ($enabled===true) $query .= "AND (Status=1) ";
			if ($result=$this->_query($query)) {
				$a = array();
				while ($row = $this->_fetch_row()) {
					$row['ItemText'] = $this->HDA_DB_textFromDB($row['ItemText']);
					$row['Title'] = $this->HDA_DB_TitleOf($row['ItemId']);
					$a[] = $row;
					}
				return $a;
				}
			HDA_SendErrorMail("Auto Collect fetch ".print_r(sqlsrv_errors(),true)." {$query} ");
			}
	elseif (!is_null($item)) {
		$enabled = ($enabled===true || $enabled==1)?1:0;
		$query = "IF EXISTS(SELECT ItemId FROM hda_collects WHERE ItemId='{$item}') ";
		$query .= "UPDATE hda_collects SET Status={$enabled},";
		$query .= "ItemText='".$this->HDA_DB_textToDB($xname)."'";
		$query .= "WHERE ItemId='{$item}' ELSE INSERT INTO hda_collects (ItemId, Status, ItemText) ";
		$query .= "VALUES ('{$item}', {$enables}, '".$this->HDA_DB_textToDB($xname)."') ";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Auto Collect set ".print_r(sqlsrv_errors(),true)." {$query} ");
		}
	return null;
	}

	// END COLLECTS
	
	// TICKETS
	
	public function HDA_DB_getTickets($ticket = NULL, $item = NULL, $forEmail=NULL) {
		$query = "SELECT hda_profiles.Title,hda_tickets.* FROM hda_tickets JOIN hda_profiles ON hda_profiles.ItemId=hda_tickets.ProfileId WHERE hda_tickets.ItemId IS NOT NULL ";
		if (!is_null($ticket)) $query .= " AND hda_tickets.ItemId='{$ticket}' ";
		if (!is_null($item)) $query .= " AND hda_tickets.ProfileId='{$item}' ";
		if (!is_null($forEmail)) $query .= " AND hda_tickets.Email LIKE '".$this->HDA_DB_textToDB($forEmail)."' ";
		$query .= " ORDER BY Title ASC";
		if ($result = $this->_query($query)) {
			$a = array();
			while ($row = $this->_fetch_row()) {
				$row['UserName'] = $this->HDA_DB_textFromDB($row['UserName']);
				$row['Email'] = $this->HDA_DB_textFromDB($row['Email']);
				$row['PW'] = $this->HDA_DB_textFromDB($row['PW']);
				$row['LastData'] = $this->HDA_DB_unserialize($this->HDA_DB_textFromDB($row['LastData']));
				$row['Instructions'] = $this->HDA_DB_textFromDB($row['Instructions']);
				$a[] = $row;
				}
			return $a;
			}
		HDA_SendErrorMail("Get Tickets ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}

	public function HDA_DB_countTickets($item) {
		$query = "SELECT count(*) FROM hda_tickets WHERE ProfileId='{$item}' ";
		if ($result = $this->_query($query)) {
			if ($row = $result->fetch_array(MYSQLI_NUM)) return $row[0];
			return 0;
			}
		HDA_SendErrorMail("Count Tickets ".print_r(sqlsrv_errors(),true)." {$query} ");
		return 0;
	}

	public function HDA_DB_updateTicket($ticket, $username=NULL, $email=NULL, $pw=NULL) {
		$query = "UPDATE hda_tickets SET ";
		if (!is_null($username)) $query .= "UserName='".$this->HDA_DB_textToDB($username)."',";
		if (!is_null($email)) $query .= "Email='".$this->HDA_DB_textToDB($email)."',";
		if (!is_null($pw)) $query .= "PW='".$this->HDA_DB_textToDB($pw)."',";
		$query = trim($query, ',');
		$query .= " WHERE ItemId='{$ticket}' ";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Update Ticket ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}

	public function HDA_DB_useTicket($item, $ticket, $data) {
	$query = "UPDATE hda_tickets SET LastUseDate=GetDate(),LastData='".$this->HDA_DB_textToDB($this->HDA_DB_serialize($data))."' WHERE ItemId='{$ticket}' ";
	if ($result = $this->_query($query)) {
		$s = "";
		foreach($data as $line) $s .= "{$line}; \n";
		return true;
		}
	HDA_SendErrorMail("Use Ticket ".print_r(sqlsrv_errors(),true)." {$query} ");
	return false;
	}

	public function HDA_DB_makeTicket($item, $username, $email, $instructions, $ticket=null) {
		global $UserCode;
		if (is_null($ticket)) $ticket = $this->HDA_isUnique('TK');
		else {
			$a = $this->HDA_DB_getTickets($ticket);
			if (is_array($a) && count($a)>0) {
				if (is_null($email)) $email = $a[0]['Email'];
				if (is_null($instructions)) $instructions = $a[0]['Instructions'];
				}
			}
		$query .= "IF EXISTS(SELECT ItemId FROM hda_tickets WHERE ItemId='{$ticket}') ";
		$query .= "UPDATE  hda_tickets SET CreatedBy='{$UserCode}', IssuedDate=GetDate(),  ProfileId='{$item}',";
		$username = $this->HDA_DB_textToDB($username);
		$email = $this->HDA_DB_textToDB($email);
		$instructions = $this->HDA_DB_textToDB($instructions);
		$query .= "UserName='{$username}',Email='{$email}',Instructions='{$instructions}' ";
		$query .= "WHERE ItemId='{$ticket}' ";
		$query .= "ELSE INSERT INTO hda_tickets (ItemId, CreateBy, IssuedDate, PrifileId, UserName, Email, Instructions) ";
		$query .= "VALUES ('{$ticket}', '{$userCode}',GetDate(),'{$item}','{$username}','{$email}','{$instructions}') ";
		if ($result = $this->_query($query)) return $ticket;
		HDA_SendErrorMail("Make Ticket ".print_r(sqlsrv_errors(),true). " {$query} ");
		return NULL;
	}

	public function HDA_DB_deleteTicket($ticket) {
		$query = "DELETE FROM hda_tickets WHERE ItemId='{$ticket}' ";
		if ($result = $this->_query($query)) {
			if (@file_exists("Tickets/{$ticket}")) {
				$ff = glob("Tickets/{$ticket}/*.*");
				foreach($ff as $f) @unlink($f);
				}
			return true;
			}
		HDA_SendErrorMail("Delete Ticket ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}

	// END TICKETS
	
	
	// AUDIT
	
	public function HDA_DB_audit($item, $taskid=NULL, $ticketid = NULL, $srcPath = NULL, $dstPath = NULL, $targetDB = NULL, $rCount = 0, $event = "") {
		$query = "INSERT INTO hda_audit (ItemId,IssuedDate,RecordCount,TaskId,TicketId,OriginalFilePath,InternalFilePath,TargetDB,ItemText) ";
		$query .= " VALUES('{$item}',GetDate(),{$rCount},";
		$query .= (!is_null($taskid))? "'{$taskid}',":"NULL,";
		$query .= (!is_null($ticketid))?"'{$ticketid}',":"NULL,";
		$query .= (!is_null($srcPath))?"'".$this->HDA_DB_textToDB($srcPath)."',":"NULL,";
		$query .= (!is_null($dstPath))?"'".$this->HDA_DB_textToDB($dstPath)."',":"NULL,";
		$query .= (!is_null($targetDB))?"'".$this->HDA_DB_textToDB($targetDB)."',":"NULL,";
		$query .= "'".$this->HDA_DB_textToDB($event)."') ";
		if ($result=$this->_query($query)) return true;
		HDA_SendErrorMail("Write Audit Entry ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}

	public function HDA_DB_tidyAudit($ago=40) {
		$since = $this->PRO_DB_Date(strtotime("{$ago} days ago"));
		$query = "DELETE FROM hda_audit WHERE IssuedDate<'{$since}' ";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Tidy Log Tracks ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}


	public function HDA_DB_getAudit($item = NULL, $limit = 0) {
		if (is_null($item)) {
			$query = "SELECT Title,t.id as ItemId,t.n as Audits FROM hda_profiles JOIN (SELECT hda_audit.ItemId as id,count(*)as n FROM hda_audit GROUP BY ItemId) as t on t.id=hda_profiles.ItemId order by title asc";
			if ($result = $this->_query($query)) {
				$a = array();
				while($row = $this->_fetch_row()) {
					$a[] = $row;
					}
				return $a;
				}
			HDA_SendErrorMail("Get Audit, get profile ids ".print_r(sqlsrv_errors(),true)." {$query}");
			}
		else {
			$query = "SELECT * FROM hda_audit WHERE ItemId='{$item}' ORDER BY IssuedDate DESC";
			if ($limit>0) $query .= " LIMIT {$limit}";
			if ($result = $this->_query($query)) {
				$a = array();
				while ($row = $this->_fetch_row()) {
					$row['OriginalFilePath'] = $this->HDA_DB_textFromDB($row['OriginalFilePath']);
					$row['InternalFilePath'] = $this->HDA_DB_textFromDB($row['InternalFilePath']);
					$row['TargetDB'] = $this->HDA_DB_textFromDB($row['TargetDB']);
					$row['ItemText'] = $this->HDA_DB_textFromDB($row['ItemText']);
					$a[] = $row;
					}
				return $a;
				}
			else HDA_SendErrorMail("Get Audit ".print_r(sqlsrv_errors(),true)." {$query} ");
			}
		return null;
	}

	
	// END AUDIT
	
	// DICTIONARY
	
	public function HDA_DB_dictionary($code=NULL, $title=NULL, $a=NULL) {
		if (is_null($a)) {
			$query = "SELECT * FROM hda_dictionary ";
			if (!is_null($code)) $query .= "WHERE ItemId='{$code}' ";
			elseif (!is_null($title)) $query .= "WHERE Name='{$title}' ";
			if ($result = $this->_query($query)) {
				$a = array();
				while ($row=$this->_fetch_row()) {
					$row['Definition'] = $this->HDA_DB_unserialize($this->HDA_DB_textFromDB($row['Definition']));
					$row['ItemText'] = $this->HDA_DB_textFromDB($row['ItemText']);
					$a[] = $row;
					}
				return $a;
				}
			HDA_SendErrorMail("Dictionary fetch ".print_r(sqlsrv_errors(),true)." {$query} ");
			return NULL;
			}
		else {
			if (is_null($a['Name']) || strlen($a['Name'])==0) return NULL;
			$a['Definition'] = $this->HDA_DB_textToDB($this->HDA_DB_serialize($a['Definition']));
			$a['ItemText'] = $this->HDA_DB_textToDB($a['ItemText']);
			if (is_null($code)) {
				$b = $this->HDA_DB_dictionary(NULL, $a['Name']);
				if (is_array($b) && count($b)==1) {
					$code = $a['ItemId'] = $b[0]['ItemId'];
					$do = 'U';
					}
				else {
					$code = $a['ItemId'] = $this->HDA_isUnique('DR');
					$do = 'I';
					}
				}
			else {
				$b = $this->HDA_DB_dictionary($code, NULL);
				if (is_array($b) && count($b)==0) $do = 'I'; else $do = 'U';
				}
			if ($do=='I') {
				global $UserCode;
				$a['UserItem'] = $UserCode;
				$a['IssuedDate'] = $this->PRO_DB_dateNow();
				$query = "INSERT INTO hda_dictionary (ItemId, UserItem, Name, IssuedDate, ItemText, Definition) ";
				$query .= " VALUES ('{$code}','{$a['UserItem']}','{$a['Name']}','{$a['IssuedDate']}','{$a['ItemText']}','{$a['Definition']}')";
				}
			else {
				$query = "UPDATE hda_dictionary SET ";
				$query .= "UserItem='{$a['UserItem']}',";
				$query .= "Name='{$a['Name']}',";
				$query .= "IssuedDate='{$a['IssuedDate']}',";
				$query .= "ItemText='{$a['ItemText']}',";
				$query .= "Definition='{$a['Definition']}' ";
				$query .= "WHERE ItemId='{$code}' ";
				}
			if ($result = $this->_query($query)) return $this->HDA_DB_dictionary($code);
			HDA_SendErrorMail("Update Dictionary ".print_r(sqlsrv_errors(),true)." {$query} ");
			return $a;
			}
	}

	public function HDA_DB_dictionaryDelete($code) {
		$query = "DELETE FROM hda_dictionary WHERE ItemId='{$code}' ";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Delete in Dictionary ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}

	// END DICTIONARY
	// VALIDATION

	public function HDA_DB_validationCode($lookup, $value=NULL, $code=NULL) {
		if (is_null($value)) {
			$query = "SELECT * FROM hda_validations ";
			if (!is_null($code)) $query .= "WHERE ItemId='{$code}' ";
			elseif (!is_null($lookup)) $query .= "WHERE LookupId='".$this->HDA_DB_textToDB($lookup)."' ";
			if ($result = $this->_query($query)) {
				$a = array();
				while ($row = $this->_fetch_row()) {
					$row['LookupId'] = $this->HDA_DB_textFromDB($row['LookupId']);
					$row['ItemValue'] = $this->HDA_DB_unserialize($this->HDA_DB_textFromDB($row['ItemValue']));
					$a[] = $row;
					}
				return $a;
				}
			HDA_SendErrorMail("Validation Lookup ".print_r(sqlsrv_errors(),true)." {$query} ");
			return NULL;
			}
		else {
			$lookup = $this->HDA_DB_textToDB($lookup);
			$value = $this->HDA_DB_textToDB($this->HDA_DB_serialize($value));
			$now = $this->PRO_DB_dateNow();
			if (is_null($code)) $do = 'I';
			else {
				$a = $this->HDA_DB_validationCode(null, null, $code);
				if (is_array($a) && count($a)==1) $do = 'U';
				else $do = 'I'; 
				}
			if ($do == 'I') {
				if (is_null($code)) $code = $this->HDA_isUnique('VC');
				$query = "INSERT INTO hda_validations (ItemId,LookupId,ItemValue,CreateDate) ";
				$query .= "VALUES ('{$code}','{$lookup}','{$value}','{$now}') ";
				}
			else {
				$query = "UPDATE hda_validations SET LookupId='{$lookup}',ItemValue='{$value}',CreateDate='{$now}' WHERE ItemId='{$code}' ";
				}
			if ($result = $this->_query($query)) return true;
			HDA_SendErrorMail("Validation Write ".print_r(sqlsrv_errors(),true)." {$query} ");
			return false;
			}
	}	
	
	public function HDA_DB_validationDelete($code) {
		$query = "DELETE FROM hda_validations WHERE ItemId='{$code}' ";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Validation Delete ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}

	// END VALIDATION
	
	// APPS
	
	public function HDA_DB_apps($item=NULL, $named=NULL) {
		global $DevUser;
		global $UserCode;
		$query = "SELECT * FROM hda_apps ";
		if (!$DevUser) {
			$query .= "LEFT JOIN hda_apps_users ON hda_apps.ItemId=hda_apps_users.ItemId ";
			$query .= "WHERE hda_apps_users.UserItem='{$UserCode}' ";
			}
		else $query .=  " WHERE ItemId IS NOT NULL ";
		if (!is_null($item)) $query .= "AND hda_apps.ItemId='{$item}' ";
		if (!is_null($named)) $query .= "AND hda_apps.Title LIKE '".$this->HDA_DB_textToDB($named)."' ";
		$query .= "ORDER BY Title ASC";
		if ($result = $this->_query($query)) {
			$a = array();
			while ($row = $this->_fetch_row()) {
				$row['Title'] = $this->HDA_DB_textFromDB($row['Title']);
				$row['ItemText'] = $this->HDA_DB_textFromDB($row['ItemText']);
				$row['Header'] = $this->HDA_DB_textFromDB($row['Header']);
				$row['Footer'] = $this->HDA_DB_textFromDB($row['Footer']);
				$row['Fields'] = $this->HDA_DB_unserialize($this->HDA_DB_textFromDB($row['Fields']));
				$a[] = $row;
				}
			return $a;
			}
		HDA_SendErrorMail("Get Apps ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}
	public function HDA_DB_writeApp($item, $a) {
		$query = "MERGE hda_apps as Target ";
		$query .= "USING hda_apps as Source ";
		$query .= "ON (Target.ItemId=Source.ItemId) AND (Source.ItemId='{$item}') ";
		$query .= "WHEN MATCHED THEN SET ";
		$query .= "ItemId='{$item}',";
		$title = (array_key_exists('Title',$a)?$this->HDA_DB_textToDB($a['Title']):"Untitled");
		$header = (array_key_exists('Header',$a)?$this->HDA_DB_textToDB($a['Header']):"");
		$footer = (array_key_exists('Footer',$a)?$this->HDA_DB_textToDB($a['Footer']):"");
		$itemtext = (array_key_exists('ItemText',$a)?$this->HDA_DB_textToDB($a['ItemText']):"");
		$query .= "Title='{$title}',";
		$query .= "Header='{$header}',";
		$query .= "Footer='{$footer}',";
		$query .= "ItemText='{$itemtext}',";
		if (!array_key_exists('Fields',$a)) $a['Fields'] = array();
		$fields = $this->HDA_DB_textToDB($this->HDA_DB_serialize($a['Fields']));
		$query .= "Fields='{$fields}',";
		$field_list = array();
		foreach ($a as $k=>$v) {
			switch ($k) {
				case 'ItemId':
				case 'Title':
				case 'Header':
				case 'Footer':
				case 'ItemText':
				case 'Fields': break;
				default:
					$query .= "{$k}='{$v}',";
					$field_list[$k]="'{$v}'";
					break;
				case 'LastDataUpdate': 
					if (is_null($v) || strlen($v)==0) {$query .= "{$k}=NULL,"; $field_list[$k]="NULL";}
					else {$query .= "{$k}='{$v}',"; $field_list[$k]="'{$v}'";}
					break;
				}
			}
		$query = trim($query,",");
		$query .= " WHEN NOT MATCHED THEN INSERT ";
		$query .= " (ItemId,Title,Header,Footer,ItemText,Fields,";
		foreach($field_list as $k=>$v) $query .= "{$k},";
		$query = trim($query,",");
		$query .= " VALUES (";
		foreach($field_list as $k=>$v) $query .= "{$v},";
		$query = trim($query,",");
		$query .= ")";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Update App ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}
	public function HDA_DB_appDataUpdate($item) {
		global $UserCode;
		$query = "UPDATE hda_apps SET LastDataUpdateBy='{$UserCode}', LastDataUpdate='".$this->PRO_DB_dateNow()."' WHERE ItemId='{$item}' ";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("App Data Update ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
		}
	public function HDA_DB_deleteApp($item) {
		$query = "DELETE FROM hda_apps WHERE ItemId='{$item}' ";
		if ($result = $this->_query($query)) {
			$query = "DELETE FROM hda_apps_users WHERE ItemId='{$item}' ";
			$this->_query($query);
			return true;
			}
		HDA_SendErrorMail("Delete App ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}
	public function HDA_DB_appUsers($item, $a=null) {
		if (is_null($a)) {
			$query = "SELECT UserItem FROM hda_apps_users WHERE ItemId='{$item}' ";
			if ($result = $this->_query($query)) {
				$a = array();
				while ($row=$this->_fetch_row()) {
					$a[] = $row[0];
					}
				return $a;
				}
			HDA_SendErrorMail("Get App Users ".print_r(sqlsrv_errors(),true)." {$query} ");
			return null;
		}
	else {
		$query = "DELETE FROM hda_apps_users WHERE ItemId='{$item}' ";
		$this->_query($query);
		foreach ($a as $user) {
			$query = "INSERT INTO hda_apps_users SET ItemId='{$item}',UserItem='{$user}',Allow=0 ";
			$this->_query($query);
			}
		return true;
		}
	}
	public function HDA_DB_appLog($item, $rowId, $typeUpdate, $preUpdate, $postUpdate) {
		global $UserCode;
		$query = "INSERT INTO hda_apps_log (AppId, UpdateDate, UpdateBy, UpdatedRow, PreUpdate, PostUpdate, UpdateType) VALUES ";
		$query .= "('{$item}','".$this->PRO_DB_dateNow()."','{$UserCode}','{$rowId}',";
		if (!is_null($preUpdate)) $query .= "'".$this->HDA_DB_textToDB($this->HDA_DB_serialize($preUpdate))."',";
		else $query .= "NULL,";
		if (!is_null($postUpdate)) $query .= "'".$this->HDA_DB_textToDB($this->HDA_DB_serialize($postUpdate))."',";
		else $query .= "NULL,";
		$query .= "'{$typeUpdate}')";
			if ($result = $this->_query($query)) {
			return true;
			}
		HDA_SendErrorMail("App Log Insert ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}
	public function HDA_DB_appLogItems($item, $rowId=null) {
		$query = "SELECT * FROM hda_apps_log WHERE AppId='{$item}' ";
		if (!is_null($rowId)) $query .= " AND UpdatedRow='{$rowId}' ";
		$query .= "ORDER BY UpdateDate DESC";
		if ($result = $this->_query($query)) {
			$a = array();
			while ($row = $this->_fetch_row()) {
				$row['PreUpdate'] = $this->HDA_DB_unserialize($this->HDA_DB_textFromDB($row['PreUpdate']));
				$row['PostUpdate'] = $this->HDA_DB_unserialize($this->HDA_DB_textFromDB($row['PostUpdate']));
				$row['UpdateByName'] = $this->HDA_DB_GetUserFullName($row['UpdateBy']);
				$a[] = $row;
				}
			return $a;
			}
		HDA_SendErrorMail("Fetch App Log ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}



	//   ----------------------
	public function HDA_DB_apps_reports($isa, $item=NULL, $named=NULL) {
		global $DevUser;
		global $UserCode;
		$query = "SELECT * FROM hda_{$isa} ";
		if (!$DevUser) {
			$query .= "LEFT JOIN hda_{$isa}_users ON hda_{$isa}.ItemId=hda_{$isa}_users.ItemId ";
			$query .= "WHERE hda_{$isa}_users.UserItem='{$UserCode}' ";
			}
		else $query .=  " WHERE ItemId IS NOT NULL ";
		if (!is_null($item)) $query .= "AND hda_{$isa}.ItemId='{$item}' ";
		if (!is_null($named)) $query .= "AND hda_{$isa}.Title LIKE '".$this->HDA_DB_textToDB($named)."' ";
		$query .= "ORDER BY Title ASC";
		if ($result = $this->_query($query)) {
			$a = array();
			while ($row = $this->_fetch_row()) {
				$row['Title'] = $this->HDA_DB_textFromDB($row['Title']);
				$row['ItemText'] = $this->HDA_DB_textFromDB($row['ItemText']);
				$row['Header'] = $this->HDA_DB_textFromDB($row['Header']);
				$row['Footer'] = $this->HDA_DB_textFromDB($row['Footer']);
				$row['Fields'] = $this->HDA_DB_unserialize($this->HDA_DB_textFromDB($row['Fields']));
				$a[] = $row;
				}
			return $a;
			}
		HDA_SendErrorMail("Get {$isa} ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}
	public function HDA_DB_writeAppReport($isa, $item, $a) {
		$query = "IF EXISTS (SELECT ItemId FROM hda_{$isa} WHERE ItemId='{$item}') ";
		$query .= " UPDATE hda_{$isa} SET ";
		$query .= "ItemId='{$item}',";
		$title = (array_key_exists('Title',$a)?$this->HDA_DB_textToDB($a['Title']):"Untitled");
		$header = (array_key_exists('Header',$a)?$this->HDA_DB_textToDB($a['Header']):"");
		$footer = (array_key_exists('Footer',$a)?$this->HDA_DB_textToDB($a['Footer']):"");
		$itemtext = (array_key_exists('ItemText',$a)?$this->HDA_DB_textToDB($a['ItemText']):"");
		$query .= "Title='{$title}',";
		$query .= "Header='{$header}',";
		$query .= "Footer='{$footer}',";
		$query .= "ItemText='{$itemtext}',";
		if (!array_key_exists('Fields',$a)) $a['Fields'] = array();
		$fields = $this->HDA_DB_textToDB($this->HDA_DB_serialize($a['Fields']));
		$query .= "Fields='{$fields}',";
		$field_list = array();
		foreach ($a as $k=>$v) {
			switch ($k) {
				case 'ItemId':
				case 'Title':
				case 'Header':
				case 'Footer':
				case 'ItemText':
				case 'Fields': break;
				default:
					$query .= "{$k}='{$v}',";
					$field_list[$k]="'{$v}'";
					break;
				case 'LastDataUpdate': 
					if (is_null($v) || strlen($v)==0) {$query .= "{$k}=NULL,"; $field_list[$k]="NULL";}
					else {$query .= "{$k}='{$v}',"; $field_list[$k]="'{$v}'";}
					break;
				}
			}
		$query = trim($query,",");
		$query .= " WHERE ItemId='{$item}' ELSE INSERT INTO hda_{$isa} ";
		$query .= " (ItemId,Title,Header,Footer,ItemText,Fields,";
		foreach($field_list as $k=>$v) $query .= "{$k},";
		$query = trim($query,",");
		$query .= ")";
		$query .= " VALUES ('{$item}','{$title}','{$header}','{$footer}','{$itemtext}','{$fields}',";
		foreach($field_list as $k=>$v) $query .= "{$v},";
		$query = trim($query,",");
		$query .= ")";
		if ($result = $this->_query($query)) { return true;}
		HDA_SendErrorMail("Update {$isa} ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}
	public function HDA_DB_appReportDataUpdate($isa, $item) {
		global $UserCode;
		$query = "UPDATE hda_{$isa} SET LastDataUpdateBy='{$UserCode}', LastDataUpdate='".$this->PRO_DB_dateNow()."' WHERE ItemId='{$item}' ";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Data {$isa} Update ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}
	public function HDA_DB_deleteAppReport($isa, $item) {
		$query = "DELETE FROM hda_{$isa} WHERE ItemId='{$item}' ";
		if ($result = $this->_query($query)) {
			$query = "DELETE FROM hda_{$isa}_users WHERE ItemId='{$item}' ";
			$this->_query($query);
			return true;
			}
		HDA_SendErrorMail("Delete {$isa} ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}
	public function HDA_DB_appReportUsers($isa, $item, $a=null) {
		if (is_null($a)) {
			$query = "SELECT UserItem FROM hda_{$isa}_users WHERE ItemId='{$item}' ";
			if ($result = $this->_query($query)) {
				$a = array();
				while ($row=$this->_fetch_row()) {
					$a[] = $row[0];
					}
				return $a;
				}
			HDA_SendErrorMail("Get {$isa} Users ".print_r(sqlsrv_errors(),true)." {$query} ");
			return null;
			}
		else {
		$query = "DELETE FROM hda_{$isa}_users WHERE ItemId='{$item}' ";
		$this->_query($query);
		foreach ($a as $user) {
			$query = "INSERT INTO hda_{$isa}_users SET ItemId='{$item}',UserItem='{$user}',Allow=0 ";
			$this->_query($query);
			}
		return true;
		}
	}
	public function HDA_DB_appReportLog($isa, $item, $rowId, $preUpdate, $postUpdate) {
		global $UserCode;
		$query = "INSERT INTO hda_{$isa}_log (AppId, UpdateDate, UpdateBy, UpdatedRow, PreUpdate, PostUpdate) VALUES ";
		$query .= "('{$item}','".$this->PRO_DB_dateNow()."','{$UserCode}','{$rowId}',";
		if (!is_null($preUpdate)) $query .= "'".$this->HDA_DB_textToDB($this->HDA_DB_serialize($preUpdate))."',";
		else $query .= "NULL,";
		if (!is_null($postUpdate)) $query .= "'".$this->HDA_DB_textToDB($this->HDA_DB_serialize($postUpdate))."'";
		else $query .= "NULL";
		$query .= ")";
		if ($result = $this->_query($query)) {
			return true;
			}
		HDA_SendErrorMail("Log {$isa} Insert ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}
	public function HDA_DB_appReportLogItems($isa, $item, $rowId=null) {
		$query = "SELECT * FROM hda_{$isa}_log WHERE AppId='{$item}' ";
		if (!is_null($rowId)) $query .= " AND UpdatedRow='{$rowId}' ";
		$query .= "ORDER BY UpdateDate DESC";
		if ($result = $this->_query($query)) {
			$a = array();
			while ($row = $this->_fetch_row()) {
				$row['PreUpdate'] = $this->HDA_DB_unserialize($this->HDA_DB_textFromDB($row['PreUpdate']));
				$row['PostUpdate'] = $this->HDA_DB_unserialize($this->HDA_DB_textFromDB($row['PostUpdate']));
				$row['UpdateByName'] = $this->HDA_DB_GetUserFullName($row['UpdateBy']);
				$a[] = $row;
				}
			return $a;
			}
		HDA_SendErrorMail("Fetch {$isa} Log ".print_r(sqlsrv_errors(),true)." {$query} ");
		return null;
	}
	//   -----------------------

	public function HDA_DB_tidyAppLog($ago=10) {
		$since = $this->PRO_DB_Date(strtotime("{$ago} days ago"));
		$query = "DELETE FROM hda_apps_log WHERE UpdateDate<'{$since}' ";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Tidy App Updates ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}

	// END APPS
	

	
	// CHECKSUMS
	
	public function HDA_DB_WriteChecksum($code, $guid, $checksum) {
		$now  = $this->PRO_DB_dateNow();
		$query = "IF EXISTS(SELECT Guid FROM hda_checksums WHERE Guid='{$guid}' AND ProfileId='{$code}') ";
		$query .= "UPDATE hda_checksums SET Checksum={$checksum} ";
		$query .= "ELSE INSERT INTO hda_checksums (Guid,ProfileId,CreateDate,Checksum) ";
		$query .= "VALUES ('{$guid}','{$code}','{$now}',{$checksum}) ";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Fails to write checksum ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}

	public function HDA_DB_ReadChecksum($code, $guid) {
		$query = "SELECT Checksum FROM hda_checksums WHERE Guid='{$guid}' AND ProfileId='{$code}' ";
		if ($result = $this->_query($query)) {
			if ($row = $this->_fetch_row()) return $row[0];
			return false;
			}
		HDA_SendErrorMail("Fails to read checksum ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}

	public function HDA_DB_tidyChecksums($ago = 3) {
		$since = $this->PRO_DB_Date(strtotime("{$ago} days ago"));
		$query = "DELETE FROM hda_checksums WHERE ";
		$query .= "CreateDate<'{$since}'";
		if ($result = $this->_query($query)) return true;
		HDA_SendErrorMail("Tidy Checksums ".print_r(sqlsrv_errors(),true)." {$query} ");
		return false;
	}


	
	// END CHECKSUMS
	
	
	

	public function HDA_isUnique($code) {
		return str_replace(".","",uniqid($code, true));
		}

	
}


?>