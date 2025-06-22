<?php

// Full Backup

function HDA_purge_backup() {
    $HDA_BACKUP = hda_db::hdadb()->HDA_DB_admin('Backup');
    if (!is_null($HDA_BACKUP) && strlen($HDA_BACKUP)>0) $HDA_BACKUP = hda_db::hdadb()->HDA_DB_unserialize($HDA_BACKUP);
    else return;
    if (!array_key_exists('Backup_purge', $HDA_BACKUP)) return;
    if ($HDA_BACKUP['Backup_purge']==0) return;
    if (!array_key_exists('Backup_dir',$HDA_BACKUP)) $HDA_BACKUP['Backup_dir'] = INIT('DB_BACKUP_DIR');
    if (is_null($HDA_BACKUP['Backup_dir'])) $HDA_BACKUP['Backup_dir'] = "backup";
    hda_db::hdadb()->HDA_DB_admin('Backup', hda_db::hdadb()->HDA_DB_serialize($HDA_BACKUP));
    $dump_dir = $HDA_BACKUP['Backup_dir'];
    if (!file_exists($dump_dir)) return;
    $dump_dir .= "/*.*";
    $ff = glob($dump_dir);
    if (!is_array($ff) || count($ff)==0) return;
    foreach ($ff as $f) {
      $at_time = max(filemtime($f),filectime($f));
      $days_ago = intval((time() - $at_time)/(60*60*24));
      if ($days_ago>$HDA_BACKUP['Backup_purge']) @unlink($f);
      }
   }

function HDA_full_backup() {
    $HDA_BACKUP = hda_db::hdadb()->HDA_DB_admin('Backup');
    if (!is_null($HDA_BACKUP) && strlen($HDA_BACKUP)>0) $HDA_BACKUP = hda_db::hdadb()->HDA_DB_unserialize($HDA_BACKUP);
    else $HDA_BACKUP = array();
    if (!array_key_exists('Backup_dir',$HDA_BACKUP)) $HDA_BACKUP['Backup_dir'] = INIT('DB_BACKUP_DIR');
    if (is_null($HDA_BACKUP['Backup_dir'])) $HDA_BACKUP['Backup_dir'] = "backup";
    hda_db::hdadb()->HDA_DB_admin('Backup', hda_db::hdadb()->HDA_DB_serialize($HDA_BACKUP));
    $dump_dir = $HDA_BACKUP['Backup_dir'];
    if (!file_exists($dump_dir)) mkdir($dump_dir);
    $dump_dir .= "/";
    $collection = array();
    $problem = "";
    $time = time();
    $zip_directories = array();
      $a = hda_db::hdadb()->HDA_DB_getProfiles();
	  if (is_array($a)) {
	     foreach ($a as $row) {
			$xml = _profile_to_xml($row);
            $pkg = HDA_WorkingDirectory($row['ItemId']);
            @file_put_contents($fpath = "{$pkg}/profile.xml", $xml); _chmod($fpath);
			$zip = new ZipArchive();
			if ($zip->open($lib_path = "{$pkg}/pkg.zip", ZIPARCHIVE::CREATE)!==true) {
				$lib_path = null;
				}
			else {       
				$zip->addFile($fpath, "profile.xml");
				$zip->addFile("{$pkg}/alcode.alc", "alcode.alc");
				}
			if ($zip->close()===true) $zip_directories[$row['ItemId']] = "{$pkg}/pkg.zip";
			}
		 }
    if (file_exists($dir='CUSTOM')) {
       $backupfile="{$dump_dir}{$dir}_{$time}.zip";
       $zip = new ZipArchive();
       if ($zip->open($backupfile, ZIPARCHIVE::CREATE)!==true) {
          $problem .= " -- Unable to create a zip archive {$backupfile} ";
          }
       else {       
	      foreach ($zip_directories as $itemid=>$pkgzip) {
		    if (!file_exists($pkgzip)) $problem .= " --- Unable to find {$pkgzip} to add to archive ";
			elseif ($zip->addFile($pkgzip, "{$itemid}.zip")!==true) $problem .= " --- Unable to add {$pkgzip} to archive ";
			}
		  if ($zip->close() !==true) $problem .= "Unable to close {$backupfile} ";
          $collection[] = $backupfile;
          }
       }
    $backupfile = "{$dump_dir}DailyBackup ".date('Ymd')."*.sql";
	$ff = glob($backupfile);
    if (is_array($ff) && count($ff)==1) $collection[] = $ff[0];

    if (count($collection)>0) {
       $backupfile = "{$dump_dir}backup_{$time}.zip";
       $zip = new ZipArchive();
       if ($zip->open($backupfile, ZIPARCHIVE::CREATE)!==true) {
          $problem .= " -- Unable to create a zip archive {$backupfile} ";
          }
       else {       
          foreach ($collection as $file) {
             $pathinfo = pathinfo($file);
             if (file_exists($file)) $zip->addFile($file, $pathinfo['basename']);
             }
          }
       $zip->close();
       foreach ($collection as $file) { if (file_exists($file)) @unlink($file);  }
       }
    else $problem .= " -- No files or directories backed up";
    if (strlen($problem)==0) $problem = null;
          
    $HDA_BACKUP['Last_Backup_Time'] = $time;
    $HDA_BACKUP['Last_Backup_File'] = (is_null($problem))?basename($backupfile):null;
    $HDA_BACKUP['Last_Backup_Error'] = (is_null($problem))?"Successful backup to {$backupfile}":$problem;
    hda_db::hdadb()->HDA_DB_admin('Backup', hda_db::hdadb()->HDA_DB_serialize($HDA_BACKUP));
    return $HDA_BACKUP['Last_Backup_Error'];
   }


// Backup MySQL

function HDA_mysql_dump($dump_dir=null, &$error, $dump_type='Backup') {
    $HDA_BACKUP = hda_db::hdadb()->HDA_DB_admin($dump_type);
    if (!is_null($HDA_BACKUP) && strlen($HDA_BACKUP)>0) $HDA_BACKUP = hda_db::hdadb()->HDA_DB_unserialize($HDA_BACKUP);
    else $HDA_BACKUP = array();
    if (!array_key_exists('zip',$HDA_BACKUP)) $HDA_BACKUP['zip'] = 'plain';
    if (!array_key_exists('Except_Tables',$HDA_BACKUP)) {
       $HDA_BACKUP['Except_Tables'] = array();
       }
    if (!array_key_exists('Only_Tables',$HDA_BACKUP)) {
       $HDA_BACKUP['Only_Tables'] = array();
       }
    if (!array_key_exists('No_Data', $HDA_BACKUP)) {
       $HDA_BACKUP['No_Data'] = array("alc_lock","alc_q","alc_pending","alc_watch","alc_online","alc_job_times");
       }
	$HDA_BACKUP['No_Data'] = true; // Overwrite
    if (is_null($dump_dir)) {
       if (!array_key_exists('Backup_dir',$HDA_BACKUP)) $HDA_BACKUP['Backup_dir'] = INIT('DB_BACKUP_DIR');
       if (is_null($HDA_BACKUP['Backup_dir'])) $HDA_BACKUP['Backup_dir'] = "backup";
       $dump_dir = $HDA_BACKUP['Backup_dir'];
       if (!file_exists($dump_dir)) mkdir($dump_dir);
       $dump_dir .= "/";
       }

    $error=null;
    $db = INIT('DB_CATALOG');
    $db_host = INIT('DB_HOST');

    $mem_limit = 8 * 1024000;
    
    // set max string size before writing to file
    if (@ini_get("memory_limit")) {
    	$max_size=500000*ini_get("memory_limit"); 
    } else {
    	ini_set("memory_limit",$mem_limit/1024000);
    	$max_size=500000*($mem_limit/1024000);
    }
        
    // set backupfile name
    $time=time();
    if ($HDA_BACKUP['zip']=="gzip") $backupfile="SQL_{$db}_{$time}.gz";
        else $backupfile="SQL_{$db}_{$time}.sql";
    $backupfile=$dump_dir.$backupfile;
                    

    //create comment
    $out="# HDAW dump of internal mysql database '{$db}' on host '{$db_host}'\n";
    $out.="# backup date and time: ".hda_db::hdadb()->PRO_DBtime_Styledate($time, true)."\n"; 
    // set and log character set
    $characterSet = HDA_DB_set_character_set();
    $out.="### used character set: " . $characterSet . " ###\n";
    $out.="set names " . $characterSet . ";\n\n";

    // print "use database" 
    $out.="CREATE DATABASE IF NOT EXISTS {$db}`;\n\n";
    $out.="USE `{$db}`;\n";
    // get auto_increment values and names of all tables
    $res=hda_db::hdadb()->mysql->query("show table status");
    $all_tables=array();
    while($row=$res->fetch_assoc()) {
       $skipped_table = false;
       if (count($HDA_BACKUP['Only_Tables'])>0) {
          if (in_array($row['Name'], $HDA_BACKUP['Only_Tables'])) $all_tables[] = $row;
          else $skipped_table = true;
          }
       elseif (!in_array($row['Name'], $HDA_BACKUP['Except_Tables'])) $all_tables[]=$row;
       else $skipped_table = true;
       if ($skipped_table) $out.="### skipping table {$row['Name']}\n";
       }

    // get table structures
    foreach ($all_tables as $table) {
       $res1=hda_db::hdadb()->mysql->query("SHOW CREATE TABLE `".$table['Name']."`");
       $tmp=$res1->fetch_assoc();
       $table_sql[$table['Name']]=$tmp["Create Table"];
       }

    // find foreign keys
    $fks=array();
    if (isset($table_sql)) {
       foreach($table_sql as $tablenme=>$table) {
          $tmp_table=$table;
          // save all tables, needed for creating this table in $fks
          while (($ref_pos=strpos($tmp_table," REFERENCES "))>0) {
             $tmp_table=substr($tmp_table,$ref_pos+12);
             $ref_pos=strpos($tmp_table,"(");
             $fks[$tablenme][]=substr($tmp_table,0,$ref_pos);
             }
          }
       }

    // order $all_tables and check for ring constraints
    $all_tables_copy = $all_tables;
    $all_tables=HDA_DB_order_sql_tables($all_tables,$fks);
    $ring_contraints = false;

    // ring constraints found
    if ($all_tables===false) {
       $ring_contraints = true;
       $all_tables = $all_tables_copy;
        	
       $out.="\n# ring constraints workaround\n";
       $out.="SET FOREIGN_KEY_CHECKS=0;\n"; 
			$out.="SET AUTOCOMMIT=0;\n";
			$out.="START TRANSACTION;\n"; 
       }
    unset($all_tables_copy);

    // as long as no error occurred
    if (is_null($error)) {
        foreach ($all_tables as $row) {
            $tablename=$row['Name'];
            $auto_incr[$tablename]=$row['Auto_increment'];

            // don't backup tables in non data list
            if (((is_bool($HDA_BACKUP['No_Data']) && $HDA_BACKUP['No_Data']==true)) || (is_array($HDA_BACKUP['No_Data']) && in_array($tablename,$HDA_BACKUP['No_Data'])))  {
               $out.="### Skipping data dump for {$tablename}\n";
               continue;
               }
            $out.="\n\n";
                // export tables
            $out.="### structure of table `".$tablename."` ###\n\n";
            $out.="DROP TABLE IF EXISTS `".$tablename."`;\n\n";
            $out.=$table_sql[$tablename];

                // add auto_increment value
            if ($auto_incr[$tablename]) {
                $out.=" AUTO_INCREMENT=".$auto_incr[$tablename];
                }
            $out.=";";
            $out.="\n\n\n";

                // export data
            if (is_null($error)) {
                $out.="### data of table `".$tablename."` ###\n\n";

                // check if field types are NULL or NOT NULL
                $res3=hda_db::hdadb()->mysql->query("show columns from `".$tablename."`");

                $res2=hda_db::hdadb()->mysql->query("select * from `".$tablename."`");
                if ($res2) {
	             for ($j=0;$j<$res2->num_rows;$j++){
	                $out .= "insert into `".$tablename."` values (";
	                $row2=$res2->fetch_array(MYSQLI_NUM);
	                        // run through each field
	                for ($k=0;$k<$nf=$res2->field_count;$k++) {
	                            // identify null values and save them as null instead of ''
	                   if (is_null($row2[$k])) $out .="null"; else $out .="'".mysql_real_escape_string($row2[$k])."'";
	                   if ($k<($nf-1)) $out .=", ";
	                   }
	                $out .=");\n";
	
	                        // if saving is successful, then empty $out, else set error flag
	                if (strlen($out)>$max_size) {
	                   if ($out=PMBP_save_to_file($backupfile,$HDA_BACKUP['zip'],$out,"a")) $out=""; 
                         else $error=" Fails to write to backup file";
	                   }
	                }
                   } 
                else {
                   $error =  "MySQL error: ".mysql_error();
                   }

                } 

             // if saving is successful, then empty $out, else set error flag
             if (strlen($out)>$max_size) {
                if ($out=HDA_DB_save_to_file($backupfile,$HDA_BACKUP['zip'],$out,"a")) $out=""; 
                else $error=" Fails to save to backup";
                }
             } // for each table
            
           } // no error
        
        if (is_null($error)) {
           // if db contained ring constraints        
	     if ($ring_contraints) {
		   $out.="\n\n# ring constraints workaround\n";
		   $out .= "SET FOREIGN_KEY_CHECKS=1;\n"; 
		   $out .= "COMMIT;\n"; 
		   }

		// save to file
           if ($backupfile=HDA_DB_save_to_file($backupfile,$HDA_BACKUP['zip'],$out,"a")) {
               if ($HDA_BACKUP['zip']=="zip") {
                  // create zip file in file system
                  $zip = new ZipArchive();
                  if ($zip->open($backupfile.".zip", ZIPARCHIVE::CREATE)!==true) {
                     $error = "Unable to create a zip archive";
                     }
                  else {       
                     $pathinfo = pathinfo($backupfile);
                     $zip->addFile($backupfile, $pathinfo['basename']);
                     }
                  $zip->close();
                  @unlink($backupfile);
                  $backupfile .= ".zip";
                  }
              } 
            else {
               $error = " Fails to write to backup file";
               }
        
           }

      if (!is_null($error)) {
         @unlink($backupfile);
         $backupfile = null;
         }

    return $backupfile;

   }

function HDA_DB_get_character_set() {
	$res = hda_db::hdadb()->mysql->query("SHOW VARIABLES LIKE 'character_set_database'");
	$obj=$res->fetch_assoc();
	if($obj['Value']) {
	   return $obj['Value'];	
	   } 
      else {
		return "utf8";
	   }
   }

function HDA_DB_set_character_set() {
	$characterSet = HDA_DB_get_character_set();
	@mysql_query("set names " . $characterSet);
	return $characterSet;
}


function HDA_DB_order_sql_tables($tables,$fks) {
    // do not order if no contraints exist
    if (!count($fks)) return $tables;

    // order
    $new_tables=array();
    $existing=array();
    $modified=TRUE;
    while(count($tables) && $modified==TRUE) {
        $modified=FALSE;
        foreach($tables as $key=>$row) {
            // delete from $tables and add to $new_tables
            if (isset($fks[$row['Name']])) {
                foreach($fks[$row['Name']] as $needed) {
                    // go to next table if not all needed tables exist in $existing
                    if(!in_array($needed,$existing)) continue 2;
                }
            }
            
            // delete from $tables and add to $new_tables
            $existing[]=$row['Name'];
            $new_tables[]=$row;
            prev($tables);
            unset($tables[$key]);
            $modified=TRUE;
        }
    }

    if (count($tables)) {
        // probably there are 'circles' in the constraints, because of that no proper backups can be created
        // This will be fixed sometime later through using 'alter table' commands to add the constraints after generating the tables.
        // Until now I just add the lasting tables to $new_tables, return them and print a warning
        foreach($tables as $row) $new_tables[]=$row;
        return false;
    }
    return $new_tables;
}

function HDA_DB_save_to_file($backupfile,$zip,&$fileData,$mode) {
	// save to a gzip file
    if ($zip=="gzip") {
        if ($zp=@gzopen($backupfile,$mode."wb9")) {
            @gzwrite($zp,$fileData);
            @gzclose($zp);            
            return $backupfile;
        } else {
            return FALSE;
        }

    // save to a plain text file (uncompressed)
    } else {
        if ($zp=@fopen($backupfile,$mode)) {
            @fwrite($zp,$fileData);
            @fclose($zp);
            return $backupfile;
        } else {
            return FALSE;
        }
    }
}





?>