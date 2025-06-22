<?php
global $_validDbTypes;
$_validDbTypes = array('MYSQL'=>"MySql",'MSSQL'=>"MS SQL Server", 'ODBC'=>"ODBC", 'ORCL'=>"Oracle", 'PGSQL'=>"Postgres");
global $_validLookups;
$_validLookups = array('MYSQL'=>"MySql",'MSSQL'=>"MS SQL Server", 'ODBC'=>"ODBC", 'ORCL'=>"Oracle", 'PGSQL'=>"Postgres",
                       'FTP'=>"Updated detect or export to FTP",
                       'EMAIL'=>"Export (only) to Email",
                       'FILE'=>"Detect or Export data to a directory on this server",
			     'XML'=>"Detect or Post on an XML server",
			     'RSS'=>"RSS provider",
				'MDX'=>"MDX Analysis Services");
global $_validDbMethods;
$_validDbMethods = array('INSERT'=>"Append Records",'REPLACE'=>"Replace on Key",'CLEAR'=>"Replace all records");
global $_validSources;
$_validSources = array(
                       'UPLOAD'=>"Uploaded direct",
                       'EMAIL'=>"Attached to or included in an email",
                       'FORM'=>"Direct by web site form",
                       'RERUN'=>"Manual rerun of failed load",
                       'ROLLBACK'=>"Manual rollback or reload of data",
			       'TRIGGER'=>"Triggered by another process",
                       'SCHEDULED'=>"Scheduled",
                       'LOOKUP'=>"Method resolved by name",
                       'FTP'=>"Updated file on an ftp server",
					   'TICKET'=>"Uploaded via a Ticket User",
					   'DETECT'=>"Collected via Auto Collect",
			       'FILE'=>"Updated file in a directory",
                       'RSS'=>"RSS provider",
                       'XML'=>"Direct to this site XML listener");
global $_validThrottles;
$_validThrottles = array(
                       'SCHEDULED'=>"Check for Scheduled processes",
                       'LOOKUP'=>"Check for methods resolved by name",
                       'FTP'=>"Check for Updated file on an ftp server",
			       'FILE'=>"Checks for Updated file in a directory",
                       'RSS'=>"Checks with an RSS provider",
                       'XML'=>"Checks with an XML provider",
                       'EMAIL'=>"Checking at a mail server"
                        );
global $_validFrequencies;
$_validFrequencies = array('ANY'=>"Not Specified",
                       'HOURLY'=>"As often as hourly",
                       'DAILY'=>"As often as daily",
                       'WEEKLY'=>"As often as weekly",
                       'MONTHLY'=>"As often as monthly",
                       'YEARLY'=>"Just once a year");
global $_validColumnTypes;
$_validColumnTypes = array('any'=>"Any or unknown type", 
                           'string'=>"String or character type", 
                           'text'=>"Text long or with new lines", 
                           'date'=>"Date",
                           'int'=>"Integer type", 'real'=>"Real or Floating");

global $_validDetectRates;
$_validDetectRates = array(
                        'OFTEN'=> array("As often as possible", 0, "MIN"),
				'EVERY 2 MINS'=>array("Every 2 minutes", 2, "MIN"),
                        'EVERY 15 MINS'=>array("Every 15 minutes", 15, "MIN"),
                        'EVERY HOUR'=>array("Every Hour", 1, "HOUR"),
                        'EVERY DAY'=>array("Once a day", 1, "DAY"),
                        'EVERY WEEK'=>array("Once a week", 7, "DAY"),
                        'EVERY MONTH'=>array("Once a month", 1, "MONTH")
                        );
global $_validExportModes;
$_validExportModes = array (
				'CSV'=>"CSV",
				'XML1'=>"XML with in line meta data",
				'XML2'=>"XML with column list meta data",
				'XML1DB'=>"XML suitable for database load");
global $_validEncodings;
$_validEncodings = array (
				"UTF-16",
			//	"UTF-8",
				"ISO-8859-15",
				"ISO-8859-14",
				"ISO-8859-13",
				"ISO-8859-10",
				"ISO-8859-9",
				"ISO-8859-8",
				"ISO-8859-7",
				"ISO-8859-6",
				"ISO-8859-5",
				"ISO-8859-4",
				"ISO-8859-3",
				"ISO-8859-2",
				"ISO-8859-1",
				"UTF-8",
				"ASCII"
				);
global $_validValidators;
$_validValidators = array('KEY'=>'On Key Press', 'CAL'=>'Calendar', 'GLOB'=>'HDAW Global');

global $_DIARY_TAGS;
$_DIARY_TAGS = array(
   'SYS_MAN'=>array('System Maintenance','SYS_MAN_TAG.jpg', 1),
   'HDAW_MAN'=>array('HDAW Maintenance','HDAW_MAN_TAG.jpg', 1),
   'GEN_EVT'=>array('General Event','GEN_EVT_TAG.jpg', 0)
   );
				
function _validateSource($source) {
   global $_validSources;
   if (is_null($source)) return "Source not specified";
   if (array_key_exists($source, $_validSources)) return "Sourced {$_validSources[$source]}";
   else return "Unknown source tagged as {$source}";
   }
function _iconForSource($source, &$caption) {
   global $_validSources;
   if (is_null($source) || !array_key_exists($source, $_validSources)) {
      $caption = "Unknown Source";
      return "SOURCE_UNKNOWN.jpg";
      }
   $caption = $_validSources[$source];
   return "SOURCE_{$source}.jpg";
   }
  
function HDA_validateEncoding($s, &$t, &$error) {
   global $_validEncodings;
   $error = false;
      $chk_s = substr($s, 3, 20); $ord_0=0;
	  for ($i=0; $i<strlen($chk_s); $i+=2) if (ord($chk_s[$i])==0) $ord_0++;
      if ($ord_0>1) $s = @mb_convert_encoding($s, 'UTF-8', 'UTF-16');
   $detected_encoding = @mb_detect_encoding($s, $_validEncodings); 
   $t = "&nbsp;&nbsp;<span style=\"color:blue;\">";
   if ($detected_encoding===false) { $t .= "Unknown encoding, assume UTF-16"; $detected_encoding = 'UTF-16';}
   else $t .= "Detected {$detected_encoding} ".(($ord_0>0)?"(UTF-16)":"");
   $t .= "</span>";
   if ($detected_encoding != 'UTF-8') {
      $s = @mb_convert_encoding($s, 'UTF-8', $detected_encoding);
      $error = ($s===false);
	  }
   return $s;
   }  


function HDA_validateConnection($ref, &$error) {
   $error = "";
   $conn_to = hda_db::hdadb()->HDA_DB_dictionary($ref);
   if (is_null($conn_to) || !is_array($conn_to) || count($conn_to)<>1) {
      $error = "Fails to lookup connection reference {$ref}";
      return false;
      }
   $def = $conn_to[0]['Definition'];
   if (array_key_exists('enabled',$def) && $def['enabled']<>1) {
      $error = "Found connection {$conn_to[0]['Name']}  but it is NOT ENABLED - continue to test connection";
      }
   switch ($def['Connect Type']) {
      case 'FTP':
	     $ftp = new HDA_FTP();
		 $e = $ftp->useDictionary($ref);
		 if (!$e) { $error = $ftp->last_error; $ftp->close(); return false; }
		 $e = $ftp->open();
		 if (!$e) { $error = $ftp->last_error; $ftp->close(); return false; }
		 $e = $ftp->to_dst_dir();
		 if (!$e) { $error = $ftp->last_error; $ftp->close(); return false; }
         $ftp->close();		 
		 return true;
         break;
      case 'MYSQL':
      case 'MSSQL':
      case 'ODBC':
      case 'ORCL':
	  case 'PGSQL':
         $db_look = array();
         $db_look['db'] = $def['Connect Type'];
         $db_look['host'] = $def['Host'];
	   $db_look['dsn'] = $def['DSN'];
	   $db_look['user'] = $def['User'];
	   $db_look['pw'] = $def['PassW'];
	   $db_look['schema'] = $def['Schema'];
	   $db_look['table'] = $def['Table'];
         return HDA_testDB($db_look, $error);
         break;
      case 'FILE':
         $file_look = array();
         $file_look['directory'] = $def['Table'];
         $file_look['filename'] = $def['Key'];
         return HDA_testFILE($file_look, $error);
         break;
      case 'XML':
         $xml_look = array();
         $xml_look['url'] = $def['Host'];
         return HDA_testXML($xml_look, $error);
         break;
      case 'MDX':
         $mdx_look = array();
         $mdx_look['connection_string'] = $def['DSN'];
         return HDA_testMDX($mdx_look, $error);
         break;
      default:
         $error = "Found connection of {$conn_to[0]['Name']} of type {$conn_to[0]['Connect Type']} ";
         return false;
         break;
      }
   return false;
   }
function HDA_testDB($db_look, &$error) {
   $lookup_conn = null;
   $ok = true;
   $error = "";
   $rows = array();
   switch ($db_look['db']) {
      case 'MYSQL':
         if (strcasecmp($db_look['host'], INIT('DB_HOST'))<>0 || strcasecmp($db_look['user'],INIT('DB_USER'))<>0) {
            $lookup_conn = @mysql_connect($db_look['host'], $db_look['user'], $db_look['pw'], true);
            mysql_select_db($db_look['schema'], $lookup_conn);
            if ($lookup_conn===false) $error = "Fails to connect to mysql ".mysql_error();
            }
         break;
      case 'MSSQL':
         $connection_info = array('Database'=>$db_look['schema']);
         if (strlen($db_look['user'])>0) { $connection_info['UID']=$db_look['user']; $connection_info['PWD']=$db_look['pw']; }
         $lookup_conn = @sqlsrv_connect($db_look['host'], $connection_info);
         if ($lookup_conn===false) {
            $error = "Fails to connect to MS SqlServer ";
            $error .= print_r(sqlsrv_errors(),true)."\n";
            }
         break;
      case 'ODBC':
         $lookup_conn = odbc_connect($db_look['dsn'], $db_look['user'], $db_look['pw']);
         if ($lookup_conn===false || $lookup_conn==0) {$lookup_conn = false; $error = "Fails to connect to ODBC {$db_look['dsn']}";}
         break;
      case 'ORCL':
         if (!function_exists('oci_connect')) {$lookup_conn = false; $error .= "Oracle client or PHP extension not installed "; }
         else $lookup_conn = @oci_connect($db_look['user'], $db_look['pw'], $db_look['host']);
         if ($lookup_conn===false || $lookup_conn==0) {$lookup_conn = false; $error .= "Fails to connect to Oracle {$db_look['user']}";}
         break;
	  case 'PGSQL':
	     if (!function_exists('pg_connect')) {$lookup_conn = false; $error .= "Postgres client or PHP extension not installed "; }
		 else {
			$connection_info = "";
			$connection_info .= "host={$db_look['host']} ";
			$connection_info .= "dbname={$db_look['schema']} ";
			$connection_info .= "user={$db_look['user']} ";
			$connection_info .= "password={$db_look['pw']} ";
			$lookup_conn = @pg_connect($connection_info);
			if ($lookup_conn===false || !is_resource($lookup_conn)) {$lookup_conn = false; $error .= "Fails to connect to Postgres "; }
		    }
		 break;
      }
   if ($lookup_conn === false) {
      $error .= " Fails to establish a connection to DB";
      $ok = false;
      }
   else {
      $query = null;
      if (array_key_exists('table',$db_look) && strlen($db_look['table'])>0) $query = "SELECT * FROM {$db_look['table']}";
      if (!is_null($query)) switch ($db_look['db']) {
         case 'MYSQL':
            if ($result = mysql_query($query)) {
               while ($row = mysql_fetch_array($result, MYSQL_NUM)) $rows[] = $row;
               }
            else { $ok = false; $error = "Fails select query {$query} for lookup in mysql ".mysql_error(); }
            break;
         case 'MSSQL':
            if ($result = sqlsrv_query($lookup_conn, $query)) {
               while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_NUMERIC)) $rows[] = $row;
               }
            else { $ok = false; $error = "Fails select query {$query} for lookup in ms sql "; }
            break;
         case 'ODBC':
            if ($result = odbc_exec($lookup_conn, $query)) {
               $row = array();
               while(odbc_fetch_into($result, $row)) $rows[] = $row;
               }
            else { $ok = false; $error = "Fails select query {$query} for lookup in odbc "; }
            $bad_updates = 0; $good_updates = 0;
            break;
         case 'ORCL':
            if ($result = oci_parse($lookup_conn, $query)) {
               if (oci_execute($result)) {
                  while ( $row = oci_fetch_array($result, OCI_ASSOC)) $rows[] = $row;
                  }
               else {
                  $ok = false;
                  $error = oci_error($result);
                  $error = "Oracle Query Execute Fail: {$error['message']}";
                  }
               }
            else {
               $ok = false;
               $error = oci_error($result);
               $error = "Oracle Query Parse Fail: {$error['message']}";
               }
            $bad_updates = 0; $good_updates = 0;
            break;
         }

      switch ($db_look['db']) {
         case 'MYSQL': 
            if (!is_null($lookup_conn)) {
               mysql_close($lookup_conn);
               global $conn; 
               $conn = mysql_pconnect(INIT('DB_HOST'), INIT('DB_USER'), INIT('DB_USER_PW'));
               mysql_select_db(INIT('DB_CATALOG'), $conn);
               }
            break;
         case 'MSSQL': sqlsrv_close($lookup_conn); break;
         case 'ODBC': odbc_close($lookup_conn); break;
         case 'ORCL': oci_close($lookup_conn); break;
		 case 'PGSQL': pg_close($lookup_conn); break;
         }
      }
   if (count($rows)>0) $error .= " ".count($rows)." records read";
   return $ok;
   }

function HDA_testFILE($file_look, &$error) {
   $error = "";
   $path = $file_look['directory'];
   if (!file_exists($path)) {
      $error = "In test connection to File, directory {$path} not found";
      return false;
      }
   $path .= "/{$file_look['filename']}";
   $ff = glob($path);
   if (!is_array($ff) || count($ff)==0) {
      $error = "In test connection to File, no files match in {$path}";
      return false;
      }
   return true;
   }

function HDA_testXML($xml_look, &$error) {
   $error = "Test connection via XML not implemented";
   return false;
   }

function HDA_testMDX($mdx_look, &$error) {
   $error = "Test connection to MDX not implemented";
   return false;
   }


function HDA_whatValidation($ref, &$valueType, &$value, &$error) {
   $valueType = null;
   $value = null;
   $a = hda_db::hdadb()->HDA_DB_validationCode($ref);
   if (is_null($a) || !is_array($a) || count($a)==0) {
      $error = "Can't find validation details";
      return false;
      }
   $a = $a[0]['ItemValue'];
   $valueType = $a['ValueType'];
   switch ($a['Method']) {
      case 'ACTUAL':
	     switch ($valueType) {
		    case 'List':
			   $list = array();
			   foreach ($a['Value'] as $v) foreach($v as $vv) {
			      if (strpos($vv, '#')!==false) {
				     $list[] = explode('#',$vv);
				     }
				  else $list[] = array($vv, $vv);
			      }
			   $values['List'] = $list;
			   break;
			default:
               $values[$valueType] = $a['Value'];
			   break;
			}
         break;
      case 'LOOKUP':
         $lookup = hda_db::hdadb()->HDA_DB_dictionary($a['Connection']);
         if (is_null($lookup) || !is_array($lookup) || count($lookup)<>1) {
            $error = "Unable to find lookup connection for validation of {$ref}";
            return false;
            }
         $def = $lookup[0]['Definition'];
         if (array_key_exists('enabled',$def) && $def['enabled']<>1) {
            $error = "Found lookup connection {$lookup[0]['Name']} for validation of {$ref} but it is NOT ENABLED";
            return false;
            }
         switch ($def['Connect Type']) {
            case 'FTP':
               $values = HDA_lookupFTP($ref, $error);
               break;
            case 'MYSQL':
            case 'MSSQL':
            case 'ODBC':
            case 'ORCL':
			case 'PGSQL':
		    $db_look = array();
               $db_look['db'] = $def['Connect Type'];
		    $db_look['host'] = $def['Host'];
		    $db_look['dsn'] = $def['DSN'];
		    $db_look['user'] = $def['User'];
		    $db_look['pw'] = $def['PassW'];
		    $db_look['schema'] = $def['Schema'];
		    $db_look['table'] = $def['Table'];
               $db_look['query'] = $a['Query'];
               $values = HDA_lookupDB($db_look, $error);
               break;
            case 'FILE':
               $file_look = array();
               $file_look['directory'] = $def['Table'];
               $file_look['filename'] = $def['Key'];
               $values = HDA_lookupFILE($file_look, $error);
               break;
            case 'XML':
               $xml_look = array();
               $xml_look['url'] = $def['Host'];
               $values = HDA_lookupXML($xml_look, $error);
               break;
            default:
               $error = "Found connection of {$lookup[0]['Name']} for validation of {$ref} but it is of type {$lookup[0]['Connect Type']} which is not valid for lookup validation";
               return false;
               break;
            }
         if (is_null($values)) {
            $error = "Fails to obtain validation info for {$ref} from {$lookup[0]['Name']}: {$error}";
            return false;
            }
         break;
      }
   switch ($valueType) {
      case 'SingleValue':
      case 'Pattern':
         $value = $values['Value'];
         break;
      case 'List':
         $value = $values['List'];
         break;
      case 'Range':
         $value = $values['Range'];
         break;
      }
   return true;
   }


function HDA_validate($ref, $value, &$error) {
   $a = hda_db::hdadb()->HDA_DB_validationCode($ref);
   if (is_null($a) || !is_array($a) || count($a)==0) return true;
   $a = $a[0]['ItemValue'];
   switch ($a['Method']) {
      case 'ACTUAL':
	     switch ($a['ValueType']) {
		    case 'List':
			   $list = array();
			   foreach ($a['Value'] as $v) foreach($v as $vv) {
			      if (strpos($vv, '#')!==false) {
				     $list[] = explode('#',$vv);
				     }
				  else $list[] = array($vv, $vv);
			      }
			   $values['List'] = $list;
			   break;
			default:
               $values[$a['ValueType']] = $a['Value'];
			   break;
			}
         break;
      case 'LOOKUP':
         $lookup = hda_db::hdadb()->HDA_DB_dictionary($a['Connection']);
         if (is_null($lookup) || !is_array($lookup) || count($lookup)<>1) {
            $error = "Unable to find lookup connection for validation of {$ref}";
            return false;
            }
         $def = $lookup[0]['Definition'];
         if (array_key_exists('enabled',$def) && $def['enabled']<>1) {
            $error = "Found lookup connection {$lookup[0]['Name']} for validation of {$ref} but it is NOT ENABLED";
            return false;
            }
         switch ($def['Connect Type']) {
            case 'FTP':
               $values = HDA_lookupFTP($ref, $error);
               break;
            case 'MYSQL':
            case 'MSSQL':
            case 'ODBC':
            case 'ORCL':
			case 'PGSQL':
		    $db_look = array();
               $db_look['db'] = $def['Connect Type'];
		    $db_look['host'] = $def['Host'];
		    $db_look['dsn'] = $def['DSN'];
		    $db_look['user'] = $def['User'];
		    $db_look['pw'] = $def['PassW'];
		    $db_look['schema'] = $def['Schema'];
		    $db_look['table'] = $def['Table'];
               $db_look['query'] = $a['Query'];
               $values = HDA_lookupDB($db_look, $error);
               break;
            case 'FILE':
               $file_look = array();
               $file_look['directory'] = $def['Table'];
               $file_look['filename'] = $def['Key'];
               $values = HDA_lookupFILE($file_look, $error);
               break;
            case 'XML':
               $xml_look = array();
               $xml_look['url'] = $def['Host'];
               $values = HDA_lookupXML($xml_look, $error);
               break;
            default:
               $error = "Found connection of {$lookup[0]['Name']} for validation of {$ref} but it is of type {$lookup[0]['Connect Type']} which is not valid for lookup validation";
               return false;
               break;
            }
         if (is_null($values)) {
            $error = "Fails to obtain validation info for {$ref} from {$lookup[0]['Name']}: {$error}";
            return false;
            }
         break;
      }
   switch ($a['ValueType']) {
      case 'SingleValue':
	     $values = $values['SingleValue'];
         if ($value == $values['Value'] || strcmp(trim($value), trim($values['Value']))==0) return true;
         $error = "Validation failed for {$ref} with value {$value} should be {$values['Value']}";
         break;
      case 'Pattern':
	     $values = $values['Pattern'];
         if (pre_match("/".$values['Value']."/",$value)) return true;
         $error = "Validation failed for {$ref} with value {$value} pattern match with {$values['Value']}";
         break;
      case 'List':
         $in_list = false;
         foreach ($values['List'] as $v) if ($value==trim($v[1])) {$in_list = true; break; }
         if ($in_list) return true;
         $error = "Validation failed for {$ref} with value {$value} not in list ";
         if (count($values['List'])==0) $error .= "because list empty";
         for ($i=0; $i<count($values['List']) && $i<5; $i++) $error .= " {$values['List'][$i][1]} ";
         if ($i==5) $error .= " more entries.. ";
         break;
      case 'Range':
         if ($value >= $values['Range']['Min'] && $value <= $values['Range']['Max']) return true;
         $error = "Validation failed for {$ref} with value {$value} not in range {$values['Range']['Min']}:{$values['Range']['Max']}";
         break;
      default:
         $error = "Do not know how to validate for lookup type of {$a['ValueType']} for {$ref}";
         return false;
      }
   return false;
   }


function HDA_lookupDB($db_look, &$error) {
   $lookup_conn = null;
   $ok = true;
   $error = "";
   $rows = array();
   switch ($db_look['db']) {
      case 'MYSQL':
         if (strcasecmp($db_look['host'], INIT('DB_HOST'))<>0 || strcasecmp($db_look['user'],INIT('DB_USER'))<>0) {
            $lookup_conn = mysql_connect($db_look['host'], $db_look['user'], $db_look['pw'], true);
            mysql_select_db($db_look['schema'], $lookup_conn);
            if ($lookup_conn===false) $error = "Fails to connect to mysql ".mysql_error();
            }
         break;
      case 'MSSQL':
         $connection_info = array('Database'=>$db_look['schema']);
         if (strlen($db_look['user'])>0) { $connection_info['UID']=$db_look['user']; $connection_info['PWD']=$db_look['pw']; }
         $lookup_conn = sqlsrv_connect($db_look['host'], $connection_info);
         if ($lookup_conn===false) $error = "Fails to connect to MS SqlServer";
         break;
      case 'ODBC':
         $lookup_conn = odbc_connect($db_look['dsn'], $db_look['user'], $db_look['pw']);
         if ($lookup_conn===false || $lookup_conn==0) {$lookup_conn = false; $error = "Fails to connect to ODBC {$dsn}";}
         break;
      case 'ORCL':
         $lookup_conn = oci_connect($db_look['user'], $db_look['pw']);
         if ($lookup_conn===false || $lookup_conn==0) {$lookup_conn = false; $error = "Fails to connect to Oracle {$db_look['user']}";}
         break;
      }
   if ($lookup_conn === false) {
      $error .= " Fails to establish a lookup connection to DB";
      $ok = false;
      }
   else {
      if (!is_null($db_look['query']) && strlen($db_look['query'])>0) {
         $query = $db_look['query'];
         }
      else $query = "SELECT * FROM {$db_look['table']}";
      switch ($db_look['db']) {
         case 'MYSQL':
            if ($result = mysql_query($query)) {
               while ($row = mysql_fetch_array($result, MYSQL_NUM)) $rows[] = $row;
               }
            else { $ok = false; $error = "Fails select query {$query} for lookup in mysql ".mysql_error(); }
            break;
         case 'MSSQL':
            if ($result = sqlsrv_query($lookup_conn, $query)) {
               while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_NUMERIC)) $rows[] = $row;
               }
            else { $ok = false; $error = "Fails select query {$query} for lookup in ms sql ".print_r(sqlsrv_errors(),true); }
            break;
         case 'ODBC':
            if ($result = odbc_exec($lookup_conn, $query)) {
               $row = array();
               while(odbc_fetch_into($result, $row)) $rows[] = $row;
               }
            else { $ok = false; $error = "Fails select query {$query} for lookup in odbc "; }
            $bad_updates = 0; $good_updates = 0;
            break;
         case 'ORCL':
            if ($result = oci_parse($lookup_conn, $query)) {
               if (oci_execute($result)) {
                  while ( $row = oci_fetch_array($result, OCI_NUM)) $rows[] = $row;
                  }
               else {
                  $ok = false;
                  $error = oci_error($result);
                  $error = "Oracle Query Execute Fail: {$error['message']}";
                  }
               }
            else {
               $ok = false;
               $error = oci_error($result);
               $error = "Oracle Query Parse Fail: {$error['message']}";
               }
            $bad_updates = 0; $good_updates = 0;
            break;
         }

      switch ($db_look['db']) {
         case 'MYSQL': 
            if (!is_null($lookup_conn)) {
               mysql_close($lookup_conn);
               global $conn; 
               $conn = mysql_pconnect(INIT('DB_HOST'), INIT('DB_USER'), INIT('DB_USER_PW'));
               mysql_select_db(INIT('DB_CATALOG'), $conn);
               }
            break;
         case 'MSSQL': sqlsrv_close($lookup_conn); break;
         case 'ODBC': odbc_close($lookup_conn); break;
         case 'ORCL': oci_close($lookup_conn); break;
         }
      }
   if ($ok && count($rows)>0) {
      $values = array();
      $values['Value'] = $rows[0][0];

      $values['Range'] = array();
      $values['Range']['Min'] = $rows[0][0];
      if (count($rows[0])==2) $values['Range']['Max'] = $rows[0][1];
      elseif (count($rows)>1) $values['Range']['Max'] = $rows[1][0];

      $values['List'] = array();
      foreach ($rows as $row) {
	     $r = array($row[0], $row[0]);
		 if (count($row)==2) $r[1] = $row[1];
	     $values['List'][] = $r;
		 }

      return $values;
      }
   return NULL;
   }

function HDA_lookupFTP($ref, &$error) {
   $error = "";
   $ftp = new HDA_FTP();
   $e = $ftp->useDictionary($ref);
   if (!$e) { $error = $ftp->last_error; return NULL; }
   $e = $ftp->open();
   if (!$e) { $error = $ftp->last_error; return NULL; }
   $e = $ftp->to_dst_dir();
   if (!$e) { $error = $ftp->last_error; return NULL; }		 
   
   $local_file = "/WorkingTemp";
   if (!file_exists($local_file)) mkdir($local_file);
   $local_file .= "/{$ftp->ftp_filename}";
   $valid_read = $ftp->read_file($local_file);
   $ftp->close();
   if ($valid_read===false) {
      $error = $ftp->last_error;
      return NULL;
      }
   $t = file_get_contents($local_file);
   unlink($local_file);
   $values = _lookup_get_values_from_text($t);
   if (!is_null($values)) return $values;
   $error = "Failed in FTP lookup no values in file";
   return NULL;
   }

function _lookup_get_values_from_text($t) {
   if (!is_null($t) && strlen($t)>0) {
      $values = array();
      $values['Value'] = $t;

      $values['Range'] = array();
      $v = explode(';',$t);
      if (is_array($v) && count($v)>1) {
         $values['Range']['Min'] = $v[0];
         $values['Range']['Max'] = $v[1];
         }
      else $values['Range']['Min'] = $value['Range']['Max'] = -1;

      $values['List'] = $v;
      return $values;
      }
   return NULL;
   }


function HDA_lookupFILE($file_look, &$error) {
   $error = "";
   $path = $file_look['directory']."/".$file_look['filename'];
   if (!file_exists($path)) {
      $error = "In validate from File, file {$path} does not exist";
      return NULL;
      }
   $t = file_get_contents($path);
   $values = _lookup_get_values_from_text($t);
   if (!is_null($values)) return $values;
   $error = "Failed in FILE lookup from file {$path} no values in file";
   return NULL;
   }

function HDA_lookupXML($file_look, &$error) {
   $error = "Lookup validation via XML not implemented";
   return NULL;
   }






?>