<?php

class HDA_FTP {

public function __destruct() {
   }
public function __construct() {
   }
   
private function _catch_ftp_error($errno, $errmsg) {
   $this->last_error = $errmsg;
   return true;
   }
private $_xl_error;
public $host = null;
public $url = null;
public $port = null;
public $username = null;
public $pw = null;
public $ftp_dir = null;
public $ftp_filename = null;
public $on_dir = null;
public $ftp_mode = FTP_ASCII;
public $use_passv = true;
public $delete_after_read = false;
public $last_error = null;

private $conn = null;

public function open() {
   $this->conn = @ftp_connect($this->host,$this->port);
   if ($this->conn===false || !is_resource($this->conn)) { 
      $this->last_error = "Failed FTP connect:, {$this->host}:{$this->port} ";
      return false;
      }
   $login = @ftp_login($this->conn, $this->username, $this->pw);
   if ($login===false) { 
      $this->last_error = "Failed FTP login:, {$this->host} {$this->username} ";
      return false;
      }
   $pasv = @ftp_pasv($this->conn, $this->use_passv);
   if ($pasv===false) {
      $this->last_error .= "Fails to set passive mode on ftp";
	  return false;
      }
   $this->on_dir = ftp_pwd($this->conn);
   return true;
   }
public function lookupDictionary($name) {
   $conn_to = hda_db::hdadb()->HDA_DB_dictionary(null, $name);
   if (is_null($conn_to) || !is_array($conn_to) || count($conn_to)<>1) {
      $this->last_error = "Fails to lookup connection name {$name}";
      return false;
      }
   return $this->useDictionary($conn_to[0]['ItemId']);
   }

public function useDictionary($ref) {
   $conn_to = hda_db::hdadb()->HDA_DB_dictionary($ref);
   if (is_null($conn_to) || !is_array($conn_to) || count($conn_to)<>1) {
      $this->last_error = "Fails to lookup connection reference {$ref}";
      return false;
      }
   $def = $conn_to[0]['Definition'];
   if (array_key_exists('enabled',$def) && $def['enabled']<>1) {
      $this->last_error = "Found connection {$conn_to[0]['Name']}  but it is NOT ENABLED";
	  return false;
      }
   switch ($def['Connect Type']) {
      case 'FTP':
         $this->_parse_host($def['Host']);
         $this->username=$def['User'];
         $this->pw=$def['PassW'];
         $this->ftp_dir=$def['Table'];
         $this->ftp_filename=$def['Key'];
		 $this->use_passv = ((!array_key_exists('Passive',$def)) || ($def['Passive']<>1));
	     $this->delete_after_read = (array_key_exists('Cleanup',$def) && ($def['Cleanup']==1));
		 break;
	  default:
	     $this->last_error = "Dictionary found but not of type FTP";
		 return false;
	  }
   return true;
   }
public function setHost($host) {
   $this->_parse_host($host);
   }
private function _parse_host($host) {
   $this->url = $host;
   $url_parts = explode(':',$host);
   $this->port = (count($url_parts)==2)?$url_parts[1]:21;
   $this->host = $url_parts[0];
   }
public function close() {
   if (is_resource($this->conn)) @ftp_close($this->conn);
   $this->conn = null;
   }
public function to_dst_dir($path=null) {
   $valid_path = true;
   $error_handler = set_error_handler(array($this,'_catch_ftp_error'));
   if (is_null($path)) $path = $this->ftp_dir;
   if (!is_null($path) && strlen($path)>0 && $path[0]!='.') {
      $valid_path = @ftp_chdir($this->conn,$path);
	  if (!$valid_path) {
         $this->last_error = "FTP destination directory not found {$this->ftp_dir}";
		 }
	  }
   $this->on_dir = @ftp_pwd($this->conn);
   set_error_handler($error_handler);
   return $valid_path;
   }
public function make_dir($path) {
   if (!($valid = $this->ftp_is_dir($path))) $valid = @ftp_mkdir($this->conn, $path);
   if ($valid===false) {
      $this->last_error = "Fails to make directory {$path} at {$this->on_dir}";
	  return false;
	  }
   $this->on_dir = ftp_pwd($this->conn);
   return true;
   }
public function read_file($to_path = null, $dst_path = null) {
   $dst_path = (is_null($dst_path))?$this->ftp_filename:$dst_path;
   if (is_null($dst_path) || strlen($dst_path)==0) {
      $this->last_error = "Fails to validate dst path in dir {$this->ftp_dir} in FTP server {$this->host}";
	  return false;
      }
   $ftp_return = array();
   $ftp_return['FilePath'] = $ftp_return['FileName'] = null;
   $local_file = $ftp_return['FilePath'] = $to_path;
   $pi = pathinfo($dst_path);
   $ftp_return['FileName'] = $pi['basename'];
   $_retries = 3; $valid_read = false;
   while (($valid_read === false) && ($_retries>0)) {
      $_retries--;
	  try {
         $valid_read = @ftp_get($this->conn, $local_file, $dst_path, $this->ftp_mode);
		 if (!$valid_read) $this->last_error = "Fails ftp fetch from {$dst_path} in {$this->on_dir} to {$local_file}";
	     }
	   catch (Exception $e) {
	     $this->last_error = "Fails in ftp_get retry {$_retries}";
		 }
	  }
   if ($valid_read) {
      if (($this->delete_after_read === true)) {
	     $deleted = @ftp_delete($this->conn, $dst_path);
	     if ($deleted===false) $this->last_error = "Warning fails to delete {$dst_path} after ok read";
         }
	   return $ftp_return;
	   }
	return false;
    }
public function write_file($from_path, $to_path = null) {
   $to_path = (is_null($to_path))?$this->ftp_filename:$to_path;
   $valid_write = @ftp_put($this->conn, $to_path, $from_path, $this->ftp_mode);
   if ($valid_write===false) {
      $this->last_error = "Fails to write file {$from_path} to {$to_path} on {$this->host}";
	  return false;
	  }
   return true;
   }
public function write_file_stream($from_path, $to_path = null) {
   $to_path = (is_null($to_path))?$this->ftp_filename:$to_path;
   $valid_write = @ftp_fput($this->conn, $to_path, $from_path, $this->ftp_mode);
   if ($valid_write===false) {
      $this->last_error = "Fails to write file stream to {$to_path} on {$this->host}";
	  return false;
	  }
   return true;
   }
public function delete($path=null) {
   $path = (is_null($path))?$this->ftp_filename:$path;
   $e = @ftp_delete($this->conn, $path);
   if ($e===false) {
      $this->last_error = "Fails to delete {$path} in {$this->on_dir} on host {$this->host}";
	  return false;
	  }
   return true;
   }
public function nlist($dir = ".") {
   $alist = @ftp_nlist($this->conn, $dir);
   $valid_read = (is_array($alist));
   if ($valid_read) {
      $ftp_return = array();
	  foreach ($alist as $afile) if ($afile <> '.') $ftp_return[] = $afile;
	  return $ftp_return;
	  }
   $this->last_error = "Fails to get nlist from {$this->ftp_dir} on {$this->host}";
   return false;
   }
public function ftp_is_dir($dir) {
   $error_handler = set_error_handler(array($this,'_catch_ftp_error'));
   if ($is_dir = @ftp_chdir($this->conn, $dir)) @ftp_chdir($this->conn,'..');
   set_error_handler($error_handler);
   return $is_dir;
   }
public function get_datetime($path = null) {
   if (is_null($path)) $path = "{$this->ftp_dir}/{$this->ftp_filename}";
   $ft = @ftp_mdtm($this->conn, $path);
   if ($ft === false || $ft < 0 || is_null($ft)) {
      $ftp_path = "ftp://{$this->username}:{$this->pw}@{$this->url}{$path}";
      $ft = max(@filemtime($ftp_path), @filectime($ftp_path));
      }
   return $ft;
   }

public function filesize($path = null) {
   $path = (is_null($path))?"{$this->ftp_filename}":$path;
   return ftp_size($this->conn, $path);
   }




}


?>