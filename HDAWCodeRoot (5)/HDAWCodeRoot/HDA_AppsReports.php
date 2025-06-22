<?php

$APP_Rollback_Cache = array();
function HDA_AppReportForApps() {
   return HDA_AppReportBuilder('apps');
   }
function HDA_AppReportForReports() {
   return HDA_AppReportBuilder('reports');
   }
function HDA_AppReportBuilder($isa = 'apps') {
   global $Action;
   global $ActionLine;
   global $UserCode;
   global $DevUser;
   global $code_root, $home_root;
   global $_ViewHeight;
   global $_ViewWidth;
   global $Tab_Menu;
   
   $showingApp = PRO_ReadParam("alc_{$isa}_showing");
   switch ($Action) {
      case "ACTION_alc_{$isa}_run":
	     list($action, $is, $showingApp) = explode('-',$ActionLine);
		 if (strlen($showingApp)==0) $showingApp = null;
		 PRO_AddToParams("alc_{$isa}_showing",$showingApp);
         PRO_Clear(array("alc_run_{$isa}_PageRow","alc_run_{$isa}_PageCount","alc_run_{$isa}_FilterField","alc_run_{$isa}_FilterValue","alc_run_{$isa}_orderColumn"));
		 break;
      case "ACTION_alc_{$isa}_delete":
	     list($action, $is, $item) = explode('-',$ActionLine);
		 hda_db::hdadb()->HDA_DB_deleteAppReport($isa, $item);
		 break;
      case "ACTION_alc_{$isa}_fix":
	     list($action, $is, $item) = explode('-',$ActionLine);
		 HDA_appFixFields($isa, $item);
		 break;
      }

   $t = "";
   $TAB_Menu = "";
   if (!is_null($showingApp)) {
      $t .= HDA_AppReportRunner($isa, $showingApp);
      }
   else {
      if ($DevUser) {
	     $mouse = _click_dialog("_dialogNewAppReport","-{$isa}");
         $Tab_Menu .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Create new..\" {$mouse}>";
         $Tab_Menu .= _emit_image("AddThis.jpg",18)."</span>";

         $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";
	     $mouse = _click_dialog("_dialogImportAppReport","-{$isa}");
         $Tab_Menu .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Import new ..\" {$mouse}>";
         $Tab_Menu .= _emit_image("ImportThis.jpg",18)."</span>";
         }
      $a = hda_db::hdadb()->HDA_DB_apps_reports($isa, $showingApp);
      if (!is_array($a) || count($a)==0) $t .= "<tr><th>No {$isa} available</th></tr>";
      else {
         $t .= "<tr><th><div style=\"width:100%;height:200px;overflow-x:hidden;overflow-y:scroll;\"><table class=\"alc-table\">";
	     $t .= "<colgroup>";
	     $t .= "<col style=\"width:200px;\"><col style=\"width:auto;\"><col style=\"width:18px;\"><col style=\"width:18px;\"><col style=\"width:18px;\">";
		 $t .= "<col style=\"width:auto;\">";
	     $t .= "</colgroup>";
	     foreach($a as $row) {
		    if ($is_valid = HDA_AppReportIsValid($isa, $row['ItemId'], $problem)) {
	           $mouse = "onclick=\"issuePost('alc_{$isa}_run-{$isa}-{$row['ItemId']}--',event);return false;\" ";
			   }
			else $mouse = "";
	        $t .= "<tr><td><span class=\"click-here\" {$mouse}>{$row['Title']}</span></td>";
		    $t .= "<td>".substr($row['ItemText'],0,80)." ...</td>";
            $t .= "<td><span style=\"cursor:pointer;height:16px;\" title=\"Run this..\" {$mouse}>";
            $t .= _emit_image("GoForward.jpg",12)."</span></td>";
		    if ($DevUser) {
               $mouse = _click_dialog("_dialogEditAppReport","-{$isa}-{$row['ItemId']}");
               $t .= "<td><span style=\"cursor:pointer;height:16px;\" title=\"Edit this..\" {$mouse}>";
               $t .= _emit_image("Edit.jpg",12)."</span></td>";
               $mouse = "onclick=\"issuePost('alc_{$isa}_delete-{$isa}-{$row['ItemId']}---',event); return false;\" ";
               $t .= "<td><span style=\"cursor:pointer;height:16px;\" title=\"Delete this..\" {$mouse}>";
               $t .= _emit_image("DeleteThis.jpg",12)."</span></td>";
		       }
			else $t .= "<td>&nbsp;</td><td>&nbsp;</td>";
			$t .= "<th>{$problem}";
			if (!$is_valid) {
	           $mouse = "onclick=\"issuePost('alc_{$isa}_fix-{$isa}-{$row['ItemId']}--',event);return false;\" ";
               $t .= "&nbsp;&nbsp;<span class=\"click-here\" style=\"color:blue;\" title=\"Fix this..\" {$mouse}>Fix this..";
               $t .= _emit_image("FixThis.jpg",12)."</span>";
			   }
			$t .= "</th>";
			$t .= "<td>Last data update by ".hda_db::hdadb()->HDA_DB_GetUserFullName($row['LastDataUpdateBy'])." on ".hda_db::hdadb()->PRO_DBdate_Styledate($row['LastDataUpdate'], true)."</td>";
		    $t .= "</tr>";
			}
	     $t .= "</table></div></th></tr>";
         }
      }
   return $t;
   }
function _dialogNewAppReport($dialog_id='alc_new_app') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $UserCode;
   global $key_mouse;

   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
	     list($action, $isa) = explode('-',$ActionLine);
         break;
	  case "ACTION_{$dialog_id}_Save":
	     list($action, $isa) = explode('-',$ActionLine);
	     $item = HDA_isUnique(($isa=='reports')?'RP':'AP');
		 hda_db::hdadb()->HDA_DB_writeAppReport($isa, $item, array('Title'=>PRO_ReadParam("{$dialog_id}_Title"),'ItemText'=>PRO_ReadParam("{$dialog_id}_ItemText"),
										'Header'=>"New Application ".PRO_ReadParam("{$dialog_id}_Title"),
										'Footer'=>"New Application ".PRO_ReadParam("{$dialog_id}_Title"),
										'UseSchema'=>"",
										'UseConnect'=>"",
										'UseTable'=>"",
										'Fields'=>array(),
										'LastDataUpdate'=>hda_db::hdadb()->PRO_DB_dateNow(),'LastDataUpdateBy'=>$UserCode,
										'CreatedBy'=>$UserCode,'CreateDate'=>hda_db::hdadb()->PRO_DB_dateNow()));
		 return "";
		 break;
      }
   $t = _makedialoghead($dialog_id, "Create New ..");
   $t .= "<tr><th>Name:</th><td><input type=\"text\" class=\"alc-dialog-name\" name=\"{$dialog_id}_Title\" value=\"\" {$key_mouse} ></td></tr>";
   $t .= "<tr><th>Description:</th><td><textarea class=\"alc-dialog-text\" name=\"{$dialog_id}_ItemText\" value=\"\" wrap=off ></textarea></td></tr>";
   $t .= _closeDialog($dialog_id, "_Save-{$isa}--", 2);
   $t .= _makedialogclose();

   return $t;
   }
function _dialogEditAppReport($dialog_id='alc_edit_app') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $UserCode;
   global $key_mouse;

   $a = null;
   $item = null;
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
	     list($action, $isa, $item) = explode('-',$ActionLine);
		 $a = hda_db::hdadb()->HDA_DB_apps_reports($isa, $item);
         break;
	  case "ACTION_{$dialog_id}_Save":
	     list($action, $isa, $item) = explode('-',$ActionLine);
		 $a = hda_db::hdadb()->HDA_DB_apps_reports($isa, $item);
		 if (is_array($a) && count($a)==1) {
            $a = $a[0];
		    $a['Title']=PRO_ReadParam("{$dialog_id}_Title");
		    $a['ItemText']=PRO_ReadParam("{$dialog_id}_ItemText");
			$a['UseConnect'] = PRO_ReadParam("{$dialog_id}_UseConnect");
			$a['UseSchema'] = PRO_ReadParam("{$dialog_id}_UseSchema");
			$a['UseTable'] = PRO_ReadParam("{$dialog_id}_UseTable");
			$a['Header'] = PRO_ReadParam("{$dialog_id}_Header");
			$a['Footer'] = PRO_ReadParam("{$dialog_id}_Footer");
			$a['Profile'] = PRO_ReadParam("{$dialog_id}_Profile");
		    hda_db::hdadb()->HDA_DB_writeAppReport($isa, $item, $a);
			hda_db::hdadb()->HDA_DB_appReportUsers($isa, $item, PRO_ReadParam("{$dialog_id}_Users"));
			}
		 return "";
		 break;
      }
   if (!is_array($a) && count($a)<>1) return "";
   if (is_null($item)) return "";
   PRO_Clear("{$dialog_id}_Users");
   $a = $a[0];
   $t = _makedialoghead($dialog_id, "Edit ..", "alc-dialog-vlarge");
   $t .= "<tr><td class=\"buttons\" colspan=2>";
   $mouse = _click_dialog("_dialogEditAppReportFields","-{$isa}-{$item}");
   $t .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Edit fields..\" {$mouse}>";
   $t .= _emit_image("Columns.jpg",24)."</span>";
   $mouse = _click_dialog("_dialogExportAppReport","-{$isa}-{$item}");
   $t .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Export ..\" {$mouse}>";
   $t .= _emit_image("Export.jpg",24)."</span>";
   $t .= "</td></tr>";
   $t .= "<tr><td>Name:</td><td><input type=\"text\" class=\"alc-dialog-name\" name=\"{$dialog_id}_Title\" value=\"{$a['Title']}\"  {$key_mouse} ></td></tr>";
   $t .= "<tr><td>Description:</td><td><textarea style=\"width:100%;height:80px;overflow-x:hidden;overflow-y:auto;\" name=\"{$dialog_id}_ItemText\" value=\"\" wrap=off >{$a['ItemText']}</textarea></td></tr>";
   $t .= "<tr><td>Connection</td>";
   $t .= "<td><select name=\"{$dialog_id}_UseConnect\">";
   $dict = hda_db::hdadb()->HDA_DB_dictionary();
   if (is_array($dict)) {
      foreach ($dict as $row) {
	     $selected = ($row['ItemId']==$a['UseConnect'])?"SELECTED":"";
	     $t .= "<option value=\"{$row['ItemId']}\" {$selected} >{$row['Name']}</option>";
	     }
      }
   $t .= "</select></td></tr>";
   $t .= "<tr><td>Target Schema</td><td><input type=\"text\" name=\"{$dialog_id}_UseSchema\" value=\"{$a['UseSchema']}\" {$key_mouse} ></td></tr>";
   $t .= "<tr><td>Target Table</td><td><input type=\"text\" name=\"{$dialog_id}_UseTable\" value=\"{$a['UseTable']}\" {$key_mouse} ></td></tr>";
   $t .= "<tr><td>Assign Users</td>";
   $t .= "<td><div style=\"width:100%;height:80px;overflow-x:hidden;overflow-y:scroll;\">";
   $all_users = hda_db::hdadb()->HDA_DB_AllUsers();
   $this_users = hda_db::hdadb()->HDA_DB_appReportUsers($isa, $item);
   foreach($all_users as $user) {
      $checked = (in_array($user['UserItem'],$this_users))?"CHECKED":"";
      $t .= "<input type=\"checkbox\" name=\"{$dialog_id}_Users[]\" value=\"{$user['UserItem']}\" {$checked}>{$user['UserFullName']}<br>";
	  }
   $t .= "</div></td></tr>";
   $t .= "<tr><td>Header</td><td colspan=2><input type=\"text\" name=\"{$dialog_id}_Header\" style=\"width:300px;\" value=\"{$a['Header']}\"  {$key_mouse} ></td></tr>";
   $t .= "<tr><td>Footer</td><td colspan=2><input type=\"text\" name=\"{$dialog_id}_Footer\" style=\"width:300px;\" value=\"{$a['Footer']}\"  {$key_mouse} ></td></tr>";
   $t .= "<tr><td>Associate Profile</td>";
   $t .= "<td><select name=\"{$dialog_id}_Profile\" >";
   $t .= "<option value=\"\" SELECTED>-- No Profile --</option>";
   $profiles = hda_db::hdadb()->HDA_DB_profileNames();
   foreach ($profiles as $profile=>$profile_name) {
      $selected = ($a['Profile']==$profile)?"SELECTED":"";
	  $t .= "<option value=\"{$profile}\" {$selected} >{$profile_name}</option>";
      }
   $t .= "</select></td></tr>";
   $t .= _closeDialog($dialog_id, "_Save-{$isa}-{$item}---", 2);
   $t .= _makedialogclose();

   return $t;
   }
function HDA_appPrimaryKey($use_connect, $schema, $table, &$error) {
   $key = null;
   $a = hda_db::hdadb()->HDA_DB_dictionary($use_connect);
   if (is_null($a) || !is_array($a) || count($a)==0) {$error = "Fails lookup of connection info"; return $key;}
   $def = $a[0]['Definition'];
   $host = $def['Host'];
   $user = $def['User'];
   $pw = $def['PassW'];
   $connection_info = array();
   $connection_info['ReturnDatesAsStrings'] = true;
   $connection_info['Database'] = $schema;
   $connection_info['UID']=$user; $connection_info['PWD']=$pw; 
   try {
      $conn = @sqlsrv_connect($host, $connection_info);
      if ($conn===false) {
         $error = "Fails to connect ".print_r(sqlsrv_errors(),true);
         return $key;
         }
	  }
   catch (Exception $e) {
      $error = "Fails to connect ".print_r(sqlsrv_errors(),true);
      return $key;
      }
   try {
      @sqlsrv_configure("WarningsReturnAsErrors",0);
      $query = "SELECT column_name FROM [{$schema}].INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE OBJECTPROPERTY(OBJECT_ID(constraint_name), 'IsPrimaryKey') = 1";
      $query .= " AND table_name = '{$table}'";
      $result = @sqlsrv_query($conn, $query, null, array('Scrollable'=>SQLSRV_CURSOR_STATIC));
      if ($result===false) {
         $error = "MSSQL Query Fail: ".print_r(sqlsrv_errors(),true);
         return $key;
         }
	  }
   catch (Exception $e) {
      $error = "Fails query ".print_r(sqlsrv_errors(),true);
      return $key;
      }
   $a = @sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
   if (is_array($a)) $key = $a['column_name'];
   if (is_resource($result)) @sqlsrv_free_stmt($result);
   @sqlsrv_close($conn);
   return $key;  
   }
function HDA_appSchema($isa, $use_connect, $schema, $table, &$error) {
   $details = array();
   $error = null;
   $a = hda_db::hdadb()->HDA_DB_dictionary($use_connect);
   if (is_null($a) || !is_array($a) || count($a)==0) {$error = "Fails lookup of connection info"; return $details;}
   $def = $a[0]['Definition'];
   $host = $def['Host'];
   $user = $def['User'];
   $pw = $def['PassW'];
   $connection_info = array();
   $connection_info['ReturnDatesAsStrings'] = true;
   $connection_info['Database'] = $schema;
   $connection_info['UID']=$user; $connection_info['PWD']=$pw; 
   try {
      $conn = @sqlsrv_connect($host, $connection_info);
      if ($conn===false) {
         $error = "Fails to connect ".print_r(sqlsrv_errors(),true);
         return $details;
         }
	  }
   catch (Exception $e) {
      $error = "Fails to connect ".print_r(sqlsrv_errors(),true);
      return $details;
      }
   try {
      @sqlsrv_configure("WarningsReturnAsErrors",0);
	  $query = "SELECT * FROM [{$schema}].INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='{$table}'";
      $result = @sqlsrv_query($conn, $query, null, array('Scrollable'=>SQLSRV_CURSOR_STATIC));
      if ($result===false) {
         $error = "MSSQL Query Fail: ".print_r(sqlsrv_errors(),true);
         return $details;
         }
	  }
   catch (Exception $e) {
      $error = "Fails query ".print_r(sqlsrv_errors(),true);
      return $details;
      }
   $details = array();
   $found_ID = false;
   while (true) {
      $a = @sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
      if (is_null($a) || $a===false) {break;}
	  $aa = array();
      if (is_array($a)) foreach ($a as $k=>$p) $aa[$k]=trim($p);
	  $found_ID |= (array_key_exists('COLUMN_NAME',$aa) && ($aa['COLUMN_NAME']=='ID'));
	  $details[] = $aa;
      }
   if (is_resource($result)) @sqlsrv_free_stmt($result);
   @sqlsrv_close($conn);
   switch ($isa) {
      case 'apps':
         if (!$found_ID) $error = "Table {$table} requires a nvarchar(50) field named ID";
		 break;
	  default:
	     break;
	  }
   return $details;
   }
function HDA_appFixID($isa, $use_connect, $schema, $table, &$error) {
   $data = array();
   $error = null;
   $a = hda_db::hdadb()->HDA_DB_dictionary($use_connect);
   if (is_null($a) || !is_array($a) || count($a)==0) {$error = "Fails lookup of connection info"; return $data;}
   $def = $a[0]['Definition'];
   $host = $def['Host'];
   $user = $def['User'];
   $pw = $def['PassW'];
   $connection_info = array();
   $connection_info['ReturnDatesAsStrings'] = true;
   $connection_info['Database'] = $schema;
   $connection_info['UID']=$user; $connection_info['PWD']=$pw; 
   try {
      $conn = @sqlsrv_connect($host, $connection_info);
      if ($conn===false) {
         $error = "Fails to connect ".print_r(sqlsrv_errors(),true);
         return $data;
         }
	  }
   catch (Exception $e) {
      $error = "Fails to connect ".print_r(sqlsrv_errors(),true);
      return $data;
      }
   @sqlsrv_configure("WarningsReturnAsErrors",0);
   $query = "SELECT COUNT(*) AS RCOUNT FROM [{$schema}].[dbo].[{$table}] WHERE (ID IS NULL) OR (ID NOT LIKE 'ID%') ";
   $result = @sqlsrv_query($conn, $query, null, array('Scrollable'=>SQLSRV_CURSOR_STATIC));
   try {
      if ($result===false) {
         $error = "MSSQL COUNT FixID Fail: ".print_r(sqlsrv_errors(),true);
         return $data;
         }
	  }
   catch (Exception $e) {
      $error = "Fails COUNT FixID query ".print_r(sqlsrv_errors(),true);
      return $data;
      }
   $a = @sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
   if (is_array($a)) $data = $a['RCOUNT'];
   if ($data>0) {
      if (is_resource($result)) @sqlsrv_free_stmt($result);
      $query = "UPDATE [{$schema}].[dbo].[{$table}] SET ID='ID'+REPLACE(CONVERT(varchar(46),NEWID()),'-','')";
	  $query .= " WHERE (ID IS NULL) OR (ID NOT LIKE 'ID%')";
      $result = @sqlsrv_query($conn, $query, null, array('Scrollable'=>SQLSRV_CURSOR_STATIC));
      try {
         if ($result===false) {
            $error = "MSSQL COUNT FixID Reset Fail: ".print_r(sqlsrv_errors(),true);
            return $data;
            }
	     }
      catch (Exception $e) {
         $error = "Fails COUNT FixID Reset query ".print_r(sqlsrv_errors(),true);
         return $data;
         }
      }
   if (is_resource($result)) @sqlsrv_free_stmt($result);
   @sqlsrv_close($conn);
   return $data;
   }
function HDA_appReportFetch($isa, $action, $use_connect, $schema, $table, &$error, $onRow=null, $orderBy=null, $orderWay=null, $rowsOnPage=10) {
   $data = array();
   $error = null;
   $a = hda_db::hdadb()->HDA_DB_dictionary($use_connect);
   if (is_null($a) || !is_array($a) || count($a)==0) {$error = "Fails lookup of connection info"; return $data;}
   $def = $a[0]['Definition'];
   $host = $def['Host'];
   $user = $def['User'];
   $pw = $def['PassW'];
   $connection_info = array();
   $connection_info['ReturnDatesAsStrings'] = true;
   $connection_info['Database'] = $schema;
   $connection_info['UID']=$user; $connection_info['PWD']=$pw; 
   try {
      $conn = @sqlsrv_connect($host, $connection_info);
      if ($conn===false) {
         $error = "Fails to connect ".print_r(sqlsrv_errors(),true);
         return $data;
         }
	  }
   catch (Exception $e) {
      $error = "Fails to connect ".print_r(sqlsrv_errors(),true);
      return $data;
      }
   $onSelectValue = PRO_ReadParam("alc_run_{$isa}_FilterValue");
   $onSelectField = PRO_ReadParam("alc_run_{$isa}_FilterField");
   if (strlen($onSelectValue)==0 || strlen($onSelectField)==0) $onSelectField = null;
   switch ($action) {
      case 'COUNT':
	     try {
            @sqlsrv_configure("WarningsReturnAsErrors",0);
			$query = "SELECT COUNT(*) AS RCOUNT FROM [{$schema}].[dbo].[{$table}] ";
			if (!is_null($onSelectField)) $query .= "WHERE [{$onSelectField}] LIKE '%{$onSelectValue}%' ";
            $result = @sqlsrv_query($conn, $query, null, array('Scrollable'=>SQLSRV_CURSOR_STATIC));
            if ($result===false) {
               $error = "MSSQL COUNT Query Fail: ".print_r(sqlsrv_errors(),true);
               return $data;
               }
	        }
         catch (Exception $e) {
            $error = "Fails COUNT query ".print_r(sqlsrv_errors(),true);
            return $data;
            }
         $a = @sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
         if (is_array($a)) $data = $a['RCOUNT'];
	     break;
	  case 'DATA':
	     try {
		    $this_order = (($orderWay&1)<>1)?"ASC":"DESC";
            @sqlsrv_configure("WarningsReturnAsErrors",0);
			$query = "SELECT * FROM [{$schema}].[dbo].[{$table}] ";
			if (!is_null($onSelectField)) $query .= "WHERE [{$onSelectField}] LIKE '%{$onSelectValue}%' ";
	        if (!is_null($orderBy)) {
	           $query .= " ORDER BY [{$orderBy}] ";
		       $query .= $this_order;
		       }
            $result = @sqlsrv_query($conn, $query, null, array('Scrollable'=>SQLSRV_CURSOR_STATIC));
            if ($result===false) {
               $error = "MSSQL Query Fail: {$query} ".print_r(sqlsrv_errors(),true);
               return $data;
               }
	        }
         catch (Exception $e) {
            $error = "Fails query ".print_r(sqlsrv_errors(),true);
            return $data;
            }
         $data = array();
         while (true) {
            $a = @sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
            if (is_null($a) || $a===false) {break;}
	        $aa = array();
            if (is_array($a)) foreach ($a as $k=>$p) {
			   $aa[$k]=trim($p);
			   }
	        $data[] = $aa;
            }
	     break;
	  case 'FETCH':
	  default:
         try {
		    $this_order = (($orderWay&1)<>1)?"ASC":"DESC";
            $pageRow = PRO_ReadParam("alc_run_{$isa}_PageRow");
            if (is_null($pageRow)) $pageRow = 1;
	        $endPageRow = $pageRow+$rowsOnPage;
            @sqlsrv_configure("WarningsReturnAsErrors",0);
	        $query = "SELECT * FROM ( SELECT *,ROW_NUMBER() OVER ( ORDER BY [{$orderBy}] {$this_order} ) AS RowNum FROM [{$schema}].[dbo].[{$table}] ";
			if (!is_null($onSelectField)) $query .= "WHERE ([{$onSelectField}] LIKE '%{$onSelectValue}%')";
			$query .= ") a ";
            $query .= " WHERE (RowNum >= {$pageRow}  AND RowNum <= {$endPageRow} )";
	        $query .= " ORDER BY [{$orderBy}] {$this_order} ";
            $result = @sqlsrv_query($conn, $query, null, array('Scrollable'=>SQLSRV_CURSOR_STATIC));
            if ($result===false) {
               $error = "MSSQL Query Fail: {$query} ".print_r(sqlsrv_errors(),true);
               return $data;
               }
	        }
         catch (Exception $e) {
            $error = "Fails query ".print_r(sqlsrv_errors(),true);
            return $data;
            }
         $data = array();
         while (true) {
            $a = @sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
            if (is_null($a) || $a===false) {break;}
	        $aa = array();
            if (is_array($a)) foreach ($a as $k=>$p) {
			   $aa[$k]=trim($p);
			   }
	        $data[] = $aa;
            }
         break;
      }
   if (is_resource($result)) @sqlsrv_free_stmt($result);
   @sqlsrv_close($conn);
   return $data;
   }
function HDA_appReportUpdate($isa, $action, $item, $record, $use_connect, $schema, $table, &$error) {
   hda_db::hdadb()->HDA_DB_appReportDataUpdate($isa, $item);
   $sch = HDA_appSchema($isa, $use_connect,$schema,$table, $error);
   $error = null;
   $a = hda_db::hdadb()->HDA_DB_dictionary($use_connect);
   if (is_null($a) || !is_array($a) || count($a)==0) {$error = "Fails lookup of connection info"; return false;}
   $def = $a[0]['Definition'];
   $host = $def['Host'];
   $user = $def['User'];
   $pw = $def['PassW'];
   $connection_info = array();
   $connection_info['ReturnDatesAsStrings'] = true;
   $connection_info['Database'] = $schema;
   $connection_info['UID']=$user; $connection_info['PWD']=$pw; 
   try {
      $conn = @sqlsrv_connect($host, $connection_info);
      if ($conn===false) {
         $error = "Fails to connect ".print_r(sqlsrv_errors(),true);
         return false;
         }
	  }
   catch (Exception $e) {
      $error = "Fails to connect ".print_r(sqlsrv_errors(),true);
      return false;
      }
   try {
      @sqlsrv_configure("WarningsReturnAsErrors",0);
	  switch ($action) {
	     case 'UPDATE':
	        $query = "UPDATE [{$schema}].[dbo].[{$table}] SET ";
	        foreach($record as $k=>$v) {
	           $v = trim($v);
	           switch ($k) {
			      case 'UpdatedDate':
				     $query .= "[{$k}]=GETDATE(),";
					 break;
		          case 'ID': break;
			      default:
				     $data_type = 'string';
					 foreach ($sch as $fields) if ($fields['COLUMN_NAME']==$k) { $data_type = $fields['DATA_TYPE']; break; }
					 switch ($data_type) {
					    case 'date':
						case 'datetime':
						   $query .= (is_null($v) || strlen($v)==0 || $v==0)?"[{$k}]=NULL,":"[{$k}]='{$v}',";
						   break;
						default:
	                       $query .= "[{$k}]='{$v}',";
						   break;
						}
			         break;
			      }
	           }
	        $query = trim($query,",");
            $query .= " WHERE ID='{$record['ID']}' "; 
            break;
         case 'INSERT':
	        $query = "INSERT INTO [{$schema}].[dbo].[{$table}] ( ";
	        foreach($record as $k=>$v) $query .= "[{$k}],";
			$query = trim($query,',');
			$query .= ") VALUES (";
	        foreach($record as $k=>$v) {
	           switch ($k) {
			      case 'UpdatedDate':
				     $query .= "GETDATE(),";
					 break;
				  default:
	                 $v = trim($v);
				     $data_type = 'string';
					 foreach ($sch as $fields) if ($fields['COLUMN_NAME']==$k) { $data_type = $fields['DATA_TYPE']; break; }
					 switch ($data_type) {
					    case 'date':
						case 'datetime':
						   $query .= (is_null($v) || strlen($v)==0 || $v==0)?"NULL,":"'{$v}',";
						   break;
						default:
	                       $query .= "'{$v}',";
						   break;
						}
					 break;
				  }
			   }
			$query = trim($query,',');
			$query .= ")";
            break;
		 case 'DELETE':
	        $query = "DELETE FROM [{$schema}].[dbo].[{$table}] WHERE ID='{$record}' ";
		    break;
         }			
      $result = @sqlsrv_query($conn, $query, null, array('Scrollable'=>SQLSRV_CURSOR_STATIC));
      if ($result===false) {
         $error = "MSSQL Query Fail: {$query} : ".print_r(sqlsrv_errors(),true);
         return false;
         }
	  }
   catch (Exception $e) {
      $error = "Fails query ".print_r(sqlsrv_errors(),true);
      return false;
      }
   if (is_resource($result)) @sqlsrv_free_stmt($result);
   @sqlsrv_close($conn);
   return true;
   }
function _dialogEditAppReportFields($dialog_id='alc_edit_app_fields') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $UserCode;
   global $key_mouse;
   
   $a = null;
   $item = null;
   $aa = array();
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
	     list($action, $isa, $item) = explode('-',$ActionLine);
         break;
      case "ACTION_{$dialog_id}_Save":
	     list($action, $isa, $item) = explode('-',$ActionLine);
         $a = hda_db::hdadb()->HDA_DB_apps_reports($isa, $item);
		 $a = $a[0];
		 $aa = PRO_Find("{$dialog_id}-{$item}-Field");
		 foreach($aa as $k=>$v) {
		    PRO_Clear($k);
		    list($dialog, $i, $field, $column, $param) = explode('-',$k);
			$column = urldecode($column);
			$a['Fields'][$column][$param] = $v;
		    }
		 hda_db::hdadb()->HDA_DB_writeAppReport($isa, $item, $a);
		 return "";
         break;
      }
   $a = hda_db::hdadb()->HDA_DB_apps_reports($isa, $item);
   $a = $a[0];
   $schema = HDA_appSchema($isa, $a['UseConnect'],$a['UseSchema'],$a['UseTable'], $error);
   $t = _makedialoghead($dialog_id, "Edit Fields", "alc-dialog-vlarge");
   if (!is_null($error)) $t .= "<tr><th style=\"color:red;\">Fails: {$error}</th></tr>";
   elseif (count($schema)==0) $t .= "<tr><th style=\"color:red;\">Missing target table {$a['UseSchema']}.{$a['UseTable']}</th></tr>";
   $t .= "<tr><th>{$a['Title']}</th></tr>";
   $t .= "<tr><th><center><div style=\"width:1000px;height:340px;overflow:scroll;\" ><table class=\"alc-table\">";
   $key = HDA_appPrimaryKey($a['UseConnect'],$a['UseSchema'],$a['UseTable'], $error);
   if (is_null($key)) $t .= "<tr><td>Fails to find a Primary Key: {$error}</td></tr>";
   else $t .= "<tr><td>Primary Key: {$key}</td></tr>";
   $valids = hda_db::hdadb()->HDA_DB_validationCode(null);
   foreach ($schema as $field) {
      $column_name = $field['COLUMN_NAME'];
	  $url_column_name = urlencode($column_name);
 	  $t .= "<tr><td rowspan=4><span style=\"font-size:12px;font-style:bold;color:blue;\">{$column_name}</span></td>";
 	  $t .= "<td>{$field['DATA_TYPE']}</td>";
      if (array_key_exists($column_name,$a['Fields'])) {
	     $label = $a['Fields'][$column_name]['Label'];
		 $width = $a['Fields'][$column_name]['Width'];
		 $format = $a['Fields'][$column_name]['Format'];
		 $validation = $a['Fields'][$column_name]['Validation'];
		 $expression = $a['Fields'][$column_name]['Expression'];
		 $style = $a['Fields'][$column_name]['Style'];
		 $errmsg = $a['Fields'][$column_name]['ErrMsg'];
		 $editor = $a['Fields'][$column_name]['Editor'];
		 $hidden = ($a['Fields'][$column_name]['Hidden']==1);
         }
	  else {
	     $label = $column_name;
		 $width = "*";
		 $format = "*";
		 $validation = null;
		 $expression = null;
		 $style = null;
		 $errmsg = "";
		 $editor = null;
		 $hidden = false;
	     }
	  $label_style = "style=\"color:green;font-style:bold;\" ";
	  $checked = ($hidden)?"CHECKED":"";
	  $t .= "<td>Hide:&nbsp;<input type=\"radio\" name=\"{$dialog_id}-{$item}-Field-{$url_column_name}-Hidden\" {$checked} value=1 >";
	  $checked = (!$hidden)?"CHECKED":"";
	  $t .= "&nbsp;Show:&nbsp;<input type=\"radio\" name=\"{$dialog_id}-{$item}-Field-{$url_column_name}-Hidden\" {$checked} value=0 >";
	  $t .= "</td>";
	  $t .= "<td {$label_style}>Format:&nbsp;<input type=\"text\" name=\"{$dialog_id}-{$item}-Field-{$url_column_name}-Format\" value=\"{$format}\" {$key_mouse} ></td>";
	  $t .= "<td {$label_style}>Validation:&nbsp;";
	  $t .= "<select name=\"{$dialog_id}-{$item}-Field-{$url_column_name}-Validation\" >";
	  $t .= "<option value=\"\" SELECTED>-- No Validation --</option>";
	  foreach ($valids as $valid) {
	     $selected = ($validation==$valid['ItemId'])?"SELECTED":"";
		 $t .= "<option value=\"{$valid['ItemId']}\" {$selected}>{$valid['LookupId']}</option>";
		 }
	  $t .= "</select></td>";
	  $t .= "<td {$label_style}>Width:&nbsp;<input type=\"text\" name=\"{$dialog_id}-{$item}-Field-{$url_column_name}-Width\" value=\"{$width}\" size=4 {$key_mouse} ></td>";
	  $t .= "<td {$label_style}>Label:&nbsp;<input type=\"text\" name=\"{$dialog_id}-{$item}-Field-{$url_column_name}-Label\" value=\"{$label}\" size=30 {$key_mouse} ></td>";
	  $t .= "</tr><tr>";
	  $t .= "<td  {$label_style} colspan=2>Expression:&nbsp;";
	  $t .= "</td><td colspan=2>";
	  $t .= "<input type=\"text\" name=\"{$dialog_id}-{$item}-Field-{$url_column_name}-Expression\" value=\"{$expression}\" size=40 {$key_mouse} ></td>";
	  $t .= "<td  {$label_style} colspan=1>Editor:&nbsp;";
	  $t .= "</td><td colspan=1>";
	  $t .= "<select name=\"{$dialog_id}-{$item}-Field-{$url_column_name}-Editor\" >";
	  foreach (array('None'=>null,'Read Only'=>'RO','Date'=>'Date') as $editor_name=>$use_editor) {
	     $selected = ($use_editor==$editor)?"SELECTED":"";
		 $t .= "<option value=\"{$use_editor}\" {$selected}>{$editor_name}</option>";
		 }
	  $t .= "</select></td>";
	  $t .= "</tr><tr>";
	  $t .= "<td  {$label_style} colspan=2>Style:&nbsp;";
	  $t .= "</td><td colspan=4>";
	  $t .= "<input type=\"text\" name=\"{$dialog_id}-{$item}-Field-{$url_column_name}-Style\" value=\"{$style}\" size=80 {$key_mouse} ></td>";
	  $t .= "</tr><tr>";
	  $t .= "<td  {$label_style} colspan=2>Users Validation Error Message:&nbsp;";
	  $t .= "</td><td colspan=4>";
	  $t .= "<input type=\"text\" name=\"{$dialog_id}-{$item}-Field-{$url_column_name}-ErrMsg\" value=\"{$errmsg}\" size=80 {$key_mouse} ></td>";
	  $t .= "</tr>";
      }
   $t .= "</table></div></center></th></tr>";
   $t .= _closeDialog($dialog_id, "_Save-{$isa}-{$item}---", 2);
   $t .= _makedialogclose();

   return $t;
   }
   
function HDA_AppReportIsValid($isa, $item, &$problem) {
   $problem = null;
   $a = hda_db::hdadb()->HDA_DB_apps_reports($isa, $item);
   if (is_null($a) || !is_array($a) || count($a)<>1) {
      $problem = "App is invalid, or unable to fetch App details";
	  return false;
	  }
   $a = $a[0];
   $schema = HDA_appSchema($isa, $a['UseConnect'],$a['UseSchema'],$a['UseTable'], $error);
   if (is_null($schema) || !is_array($schema) || count($schema)==0) {
      $problem = "Unable to fetch schema details: {$error}";
	  return false;
	  }
   foreach($schema as $column) {
      if (!array_key_exists($column['COLUMN_NAME'], $a['Fields'])) {
	     $problem = "The definition is not up-to-date with the table schema, the table has a field {$column['COLUMN_NAME']} that is not mentioned in the definition";
		 return false;
		 }
      }
   foreach ($a['Fields'] as $column_name=>$column_details) {
      $found = false;
      foreach ($schema as $column) {
	     if ($found=($column_name==$column['COLUMN_NAME'])) break;
		 }
      if (!$found) {
	     $problem = "The definition specifies a field {$column_name} that can't be found in the table {$a['UseTable']} schema";
		 return false;
		 }
      }
	  
   switch ($isa) {
      case 'apps':
         HDA_appFixID($isa, $a['UseConnect'],$a['UseSchema'],$a['UseTable'], $error);
		 break;
	  default:
	      break;
	  }
	  
   return true;
   }
function HDA_appFixFields($isa, $item) {
   $a = hda_db::hdadb()->HDA_DB_apps_reports($isa, $item);
   $a = $a[0];
   $schema = HDA_appSchema($isa, $a['UseConnect'],$a['UseSchema'],$a['UseTable'], $error);
   foreach($schema as $column) {
      if (!array_key_exists($column['COLUMN_NAME'], $a['Fields'])) {
	     $column_name = $column['COLUMN_NAME'];
	     $a['Fields'][$column_name]['Label'] = $column_name;
		 $a['Fields'][$column_name]['Width'] = '*';
		 $a['Fields'][$column_name]['Format'] = '*';
		 $a['Fields'][$column_name]['Validation'] = null;
		 $a['Fields'][$column_name]['Expression'] = null;
		 $a['Fields'][$column_name]['Style'] = null;
		 $a['Fields'][$column_name]['Hidden']=false;
		 $a['Fields'][$column_name]['ErrMsg']="";
		 $a['Fields'][$column_name]['Editor']=null;
		 hda_db::hdadb()->HDA_DB_writeAppReport($isa, $item, $a);
		 return true;
		 }
      }
   foreach ($a['Fields'] as $column_name=>$column_details) {
      $found = false;
      foreach ($schema as $column) {
	     if ($found=($column_name==$column['COLUMN_NAME'])) break;
		 }
      if (!$found) {
	     unset($a['Fields'][$column_name]);
		 hda_db::hdadb()->HDA_DB_writeAppReport($isa, $item, $a);
		 return true;
		 }
      }
   return false;
   }
$last_app_error = null;
function alc_app_error_handler($errno, $errstr, $errfile, $errline, $errcontext) {
   global $last_app_error;
   $last_app_error = strip_tags($errstr);
   }
function last_app_error() {
   global $last_app_error;
   $err = $last_app_error;
   $last_app_error = null;
   return $err;
   }   

function HDA_AppReportRunner($isa, $item) {
   global $Action;
   global $ActionLine;
   global $UserCode;
   global $DevUser;
   global $code_root, $home_root;
   global $_ViewHeight;
   global $_ViewWidth;
   global $Tab_Menu;
   $rowsOnPage = 14;
   $problem = null;
   $err_handler = set_error_handler('alc_app_error_handler');
   $pageRow = PRO_ReadParam("alc_run_{$isa}_PageRow");
   $dataCount = PRO_ReadParam("alc_run_{$isa}_PageCount");
   if (is_null($pageRow)) $pageRow = 1;
   switch ($Action) {
      case "ACTION_alc_run_{$isa}_NewRecord":
	     list($action, $isa, $item) = explode('-',$ActionLine);
		 break;
      case "ACTION_alc_run_{$isa}_Save":
	     list($action, $isa, $item) = explode('-',$ActionLine);
	     if (_writeAppReportUpdates($isa, $item, $problem)) {
		    PRO_Clear("alc_run_{$isa}_onRow");
		    }
		 break;
	  case "ACTION_alc_run_{$isa}_Page":
	     list($action, $isa, $item, $page) = explode('-',$ActionLine);
		 if ( _writeAppReportUpdates($isa, $item, $problem)) {
		    switch ($page) {
		       case 0: $pageRow = 1; break;
			   case 1: $pageRow -= $rowsOnPage; break;
			   case 2: $pageRow += $rowsOnPage; break;
			   case 3: $pageRow = $dataCount-$rowsOnPage;
			   }
		    if ($pageRow>$dataCount || ($pageRow+$rowsOnPage)>$dataCount) $pageRow = $dataCount-$rowsOnPage;
		    if ($pageRow<1) $pageRow=1;
		    PRO_AddToParams("alc_run_{$isa}_PageRow",$pageRow);
			PRO_Clear("alc_run_{$isa}_onRow");
			}
		 break;
	  case "ACTION_alc_run_{$isa}_ClearFilter":
	     list($action, $isa) = explode('-',$ActionLine);
	     PRO_Clear(array("alc_run_{$isa}_FilterField","alc_run_{$isa}_FilterValue"));
	  case "ACTION_alc_run_{$isa}_Find":
	     list($action, $isa) = explode('-',$ActionLine);
		 $pageRow=1; $dataCount = 0;
		 PRO_AddToParams("alc_run_{$isa}_PageRow",$pageRow);
		 break;
	  case "ACTION_alc_run_{$isa}_Trigger":
	     list($action, $isa, $item, $profile_code) = explode('-',$ActionLine);
         HDA_ReportTrigger($profile_code, $item);
			if (!is_null($code = hda_db::hdadb()->HDA_DB_addRunQ(
						NULL, 
						$profile_code,
						$UserCode,					
						NULL, 
						NULL, 
						'TRIGGER',
						$source_info = "Application Trigger",
						hda_db::hdadb()->PRO_DB_dateNow()))) {
					HDA_ReportUpload($profile_code, $code);
					$note = "Application Trigger to pending process queue";
					hda_db::hdadb()->HDA_DB_issueNote($profile_code, $note, 'TAG_PROGRESS');
					HDA_LogThis(hda_db::hdadb()->HDA_DB_TitleOf($profile_code)." {$note}");
					PRO_Clear("alc_run_{$isa}_{$item}_Updated");
					}
		 break;
      }
   $t = "";
   $Tab_Menu = "";
   $viewStyle = 'TABLE';
   $a = hda_db::hdadb()->HDA_DB_apps_reports($isa, $item);
   $a = $a[0];
   $schema = HDA_appSchema($isa, $a['UseConnect'],$a['UseSchema'],$a['UseTable'], $error);
   if (!is_null($error)) $t .= "<tr><th style=\"color:red;\">Fails: {$error}</th></tr>";
   elseif (count($schema)==0) $t .= "<tr><th style=\"color:red;\">Missing target table {$a['UseSchema']}.{$a['UseTable']}</th></tr>";
   $data = HDA_appReportFetch($isa, 'COUNT',$a['UseConnect'],$a['UseSchema'],$a['UseTable'], $error);
   if (!is_null($error)) $t .= "<tr><th style=\"color:red;\">Fails: {$error}</th></tr>";
   elseif ($data==0) $t .= "<tr><th style=\"color:red;\">No data found in {$a['UseSchema']}.{$a['UseTable']}</th></tr>";
   PRO_AddToParams("alc_run_{$isa}_PageCount",$dataCount = $data);

   $mouse = "onclick=\"issuePost('alc_{$isa}_run---',event);return false; \" ";
   $Tab_Menu .= "<span style=\"cursor:pointer;height:16px;\" title=\"Back to Index..\" {$mouse}>";
   $Tab_Menu .= _emit_image("GoBack.jpg",18)."</span>";
   $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";
   switch ($isa) {
      case 'apps':
         $mouse = "onclick=\"issuePost('alc_run_{$isa}_Save-{$isa}-{$item}--',event);return false; \" ";
         $Tab_Menu .= "<span id=\"alc_run_{$isa}_Save\" style=\"cursor:pointer;height:16px;visibility:hidden;\" title=\"Save updates..\" {$mouse}>";
         $Tab_Menu .= _emit_image("Save.jpg",18)."</span>";
         $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";
         $mouse = "onclick=\"issuePost('alc_run_{$isa}_NewRecord-{$isa}-{$item}--',event);return false;\"  ";
         $Tab_Menu .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Add Record..\" {$mouse}>";
         $Tab_Menu .= _emit_image("AddRow.jpg",18)."</span>";
         $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";
         $mouse = _click_dialog("_dialogAppHistoryLog","-{$isa}-{$item}");
         $Tab_Menu .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"View Update Log..\" {$mouse}>";
         $Tab_Menu .= _emit_image("HistoryLog.jpg",18)."</span>";
         $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";
		 break;
	  default:
	     break;
	  }
   $mouse = "onclick=\"issuePost('alc_run_{$isa}_Page-{$isa}-{$item}-0---',event); return false;\" ";
   $Tab_Menu .= _emit_image("ar_left_abs.gif",12,$mouse);
   $mouse = "onclick=\"issuePost('alc_run_{$isa}_Page-{$isa}-{$item}-1---',event); return false;\" ";
   $Tab_Menu .= _emit_image("ar_left.gif",12,$mouse);
   $Tab_Menu .= "&nbsp;Rows {$pageRow} to ".((($pageRow+$rowsOnPage)>$dataCount)?$dataCount:($pageRow+$rowsOnPage))."&nbsp;";
   $mouse = "onclick=\"issuePost('alc_run_{$isa}_Page-{$isa}-{$item}-2---',event); return false;\" ";
   $Tab_Menu .= _emit_image("ar_right.gif",12,$mouse);
   $mouse = "onclick=\"issuePost('alc_run_{$isa}_Page-{$isa}-{$item}-3---',event); return false;\" ";
   $Tab_Menu .= _emit_image("ar_right_abs.gif",12,$mouse);
   $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";
   $Tab_Menu .= "<select name=\"alc_run_{$isa}_FilterField\" >";
   $filterField = PRO_ReadParam("alc_run_{$isa}_FilterField");
   $Tab_Menu .= "<option value=\"\" SELECTED>-- No Filter --</option>";
   foreach($schema as $column) {
      $selected = ($filterField==$column['COLUMN_NAME'])?"SELECTED":"";
	  $Tab_Menu .= "<option value=\"{$column['COLUMN_NAME']}\" {$selected}>{$a['Fields'][$column['COLUMN_NAME']]['Label']}</option>";
	  }
   $Tab_Menu .= "</select>";
   $filterValue = PRO_ReadParam("alc_run_{$isa}_FilterValue");
   $key_mouse = "onKeyPress=\"return keyPressPost('alc_run_{$isa}_Find-{$isa}-{$item}--', event, null); \" ";
   $Tab_Menu .= "&nbsp;<input type=\"text\" name=\"alc_run_{$isa}_FilterValue\" value=\"{$filterValue}\" {$key_mouse} >&nbsp;";
   $mouse = "onclick=\"issuePost('alc_run_{$isa}_Find-{$isa}-{$item}-',event); return false;\" ";
   $Tab_Menu .= _emit_image("FilterThis.jpg",12,$mouse);
   $mouse = "onclick=\"issuePost('alc_run_{$isa}_ClearFilter-{$isa}--',event);return false;\" ";
   $Tab_Menu .= "&nbsp;or&nbsp;<span class=\"click-here\" {$mouse} style=\"color:blue;\" >[ Clear Filters ]</span>";
   $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";
   
   $mouse = _click_dialog("_dialogExportAppReportData","-{$isa}-{$item}");
   $Tab_Menu .= _emit_image("DownloadHere.jpg",18,$mouse);
   $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";
   $mouse = _click_dialog("_dialogImportAppData","-{$isa}-{$item}");
   $Tab_Menu .= _emit_image("ImportThis.jpg",18,$mouse);
  
   $hasUpdates = PRO_ReadParam("alc_run_{$isa}_{$item}_Updated");
   if (!is_null($hasUpdates) && strlen($a['Profile'])>0) {
      $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";
      $mouse = "onclick=\"issuePost('alc_run_{$isa}_Trigger-{$isa}-{$item}-{$a['Profile']}--',event); return false;\" ";
      $Tab_Menu .= "&nbsp;Data changed - ready to trigger?&nbsp;<input type=\"submit\" class=\"click-here submit\" {$mouse} value=\"Trigger ".hda_db::hdadb()->HDA_DB_TitleOf($a['Profile'])."\">";
      }
      
 
   $t .= "<tr><td>";
   $t .= "<h3>{$a['Title']}</h3>";
   $t .= "<span style=\"font:gray;font-size:10px;\">{$a['ItemText']}</span>";
   $t .= "<br>";
   $t .= "<div style=\"margin-left:20px;margin-right:20px;margin-top:4px;margin-bottom:4px;padding:2px;text-align:center;font-size:12px;font-style:bold;\">{$a['Header']}</div>";
   $t .= "<table class=\"alc-table\">";
   //
      $t .= "<tr><td>";
	  switch ($viewStyle) {
		 case 'TABLE':
		    $t .= _appReportViewTable($isa, $item, $rowsOnPage, $problem);
			break;
		 case 'RECORD':
		    $t .= _appReportViewRecord($isa, $item);
			break;
		}
      $t .= "</td></tr>";
   $t .= "</table>";
   $t .= "<div style=\"margin-left:20px;margin-right:20px;margin-top:4px;margin-bottom:4px;padding:2px;text-align:center;font-size:10px;font-style:italic;\">{$a['Footer']}</div>";
   $t .= "</td></tr>";
   
   set_error_handler($err_handler);
   return $t;
   }
   
function _cacheRow($item, $row) {
   global $APP_Rollback_Cache;
   $APP_Rollback_Cache = PRO_ReadParam('alc_app_rollback');
   if (is_null($APP_Rollback_Cache)) $APP_Rollback_Cache = array();
   if (!array_key_exists($item, $APP_Rollback_Cache)) $APP_Rollback_Cache[$item] = array();
   if (!array_key_exists($row['ID'], $APP_Rollback_Cache[$item]) || (is_null($APP_Rollback_Cache[$item][$row['ID']]))) {
      $APP_Rollback_Cache[$item][$row['ID']] = $row;
	  PRO_AddToParams('alc_app_rollback', $APP_Rollback_Cache);
	  return false;
	  }
   else return !is_null($APP_Rollback_Cache[$item][$row['ID']]);
   }
function _unCacheRow($item, $rowId) {
   global $APP_Rollback_Cache;
   $APP_Rollback_Cache = PRO_ReadParam('alc_app_rollback');
   if (is_array($APP_Rollback_Cache) && array_key_exists($item,$APP_Rollback_Cache) && array_key_exists($rowId,$APP_Rollback_Cache[$item])) {
      $v = $APP_Rollback_Cache[$item][$rowId];
	  return $v;
      }
   return null;
   }
function _logUpdate($item, $rowId, $updateType, $row) {
   $old_row = _unCacheRow($item, $rowId);
   hda_db::hdadb()->HDA_DB_appLog($item, $rowId, $updateType, $old_row, $row);
   _cacheRow($item, $row);
   }
function _writeAppReportUpdates($isa, $item, &$problem) {
   $problem = "";
   $updates_ok = true;
   $field_problems = array();
   $a = hda_db::hdadb()->HDA_DB_apps_reports($isa, $item);
   $a = $a[0];
   $update_list = PRO_ReadParam("alc_run_{$isa}_updatedRows");
   $update_table = array();
   if (!is_null($update_list)) {
	  $update_list = explode(',',$update_list);
      foreach($update_list as $an_update) if (strlen($an_update)>0) {
		 $update_table[$an_update] = PRO_ReadParam($an_update);
         }
	   }
   if (count($update_table)>0) {
      PRO_AddToParams("alc_run_{$isa}_{$item}_Updated", true);
      $records = array();
      foreach($update_table as $an_update=>$v) if (strlen($an_update)>0) {
	     list($V,$rowId,$field) = explode('-',$an_update);
		 $records[$rowId][$field] = $v;
		 }
	  $fields = array_keys($a['Fields']);
	  $rows = array_keys($records);
	  $update_records = array();
	  foreach ($rows as $rowId) foreach ($fields as $field)  {
	     $v = (array_key_exists(_nameToId($field),$records[$rowId]))?$records[$rowId][_nameToId($field)]:PRO_ReadParam("V-{$rowId}-"._nameToId($field));
		 $update_records[$rowId][$field] = $v;
		 $is_valid = true;
		 if (($a['Fields'][$field]['Hidden'] <> 1) && (strlen($a['Fields'][$field]['Format'])>0)) switch ($format = $a['Fields'][$field]['Format'][0]) {
		    case '*': break;
			case 'w': 
			case 'd':
			   switch ($format) {
			      case 'w': $format = "/[a-zA-Z]{1,}/"; $expect = "Letters a-z, A-Z only"; break;
				  case 'd': $format = "/[\d]{1,}/"; $expect = "Digits only"; break;
				  }
			   if (!@preg_match($format, $v)) {
			      $field_problems[$field] = "Format failure on field {$a['Fields'][$field]['Label']} expected {$expect} input was {$v}<br>";
				  $is_valid = false;
				  }
			   break;
			case '/':
			   if (!@preg_match($a['Fields'][$field]['Format'], $v)) {
			      $field_problems[$field] = "Format failure on field {$a['Fields'][$field]['Label']} expected regex match on {$a['Fields'][$field]['Format']} input was {$v}<br>";
				  $is_valid = false;
				  }
			   break;
			default:
			   break;
			}
		 if ($is_valid && ($a['Fields'][$field]['Hidden'] <> 1)) {
            $validator =  hda_db::hdadb()->HDA_DB_validationCode(null, null, $a['Fields'][$field]['Validation']);
	        if (is_array($validator) && count($validator)==1) {
	           if (HDA_whatValidation($validator[0]['LookupId'], $valueType, $value, $error))
	              switch($valueType) {
				     case 'Range':
					    if ($v<$value['Min'] || $v>$value['Max']) {
						   $field_problems[$field] = "Validation failure on field {$a['Fields'][$field]['Label']} input value {$v} not in range {$value['Min']} to {$value['Max']}<br>";
						   $is_valid = false;
						   }
						break;
					 case 'SingleValue':
					    if ($v <> $value) {
						   $field_problems[$field] = "Validation failure on field {$a['Fields'][$field]['Label']} input value {$v} not set value {$value}<br>";
						   $v = $value;
						   }
						break;
					 case 'List':
					    foreach ($value as $list) {
						   if (strcasecmp($v,$list[1])==0) {$is_valid=true;break;}
						   }
					    if (!$is_valid) {
						   $field_problems[$field] = "Validation failure on field {$a['Fields'][$field]['Label']} input value {$v} not in valid list<br>";
						   }
						break;
				     }
	           }
		    }
		 if (array_key_exists($field, $field_problems)) {
		    if (strlen($a['Fields'][$field]['ErrMsg'])>0) $field_problems[$field] = "{$a['Fields'][$field]['ErrMsg']}<br>";
			}
	     $updates_ok &= $is_valid;
		 }
	  if ($updates_ok) foreach ($update_records as $recordId=>$record) {
		 $record['ID'] = $recordId;
		 $db_record = $record;
		 foreach ($record as $field=>$v) {
		    switch (strtoupper($a['Fields'][$field]['Editor'])) {
			   case 'RO': unset($db_record[$field]); break;
			   default : break;
			   }
		    }
         if (!HDA_appReportUpdate($isa, 'UPDATE', $item, $db_record, $a['UseConnect'],$a['UseSchema'],$a['UseTable'], $error)) {
		    $problem .= " {$error}<br>";
			$updates_ok = false;
			}
		 _logUpdate($item, $recordId, 'UPDATE', $record);
	     }
	  foreach ($field_problems as $field=>$issue) $problem .= "{$field}: {$issue}";
	  if (!is_null($err = last_app_error())) $problem .= "{$err}<br>";
	  }
  return $updates_ok;
  }
function _appReportViewTable($isa, $item, $rowsOnPage=10, $problem) {
   global $Action;
   global $ActionLine;
   global $DevUser;
   global $code_root, $home_root;
   global $post_load;
   global $_ViewHeight;
   global $_ViewWidth;

   $a = hda_db::hdadb()->HDA_DB_apps_reports($isa, $item);
   $a = $a[0];
   $schema = HDA_appSchema($isa, $a['UseConnect'],$a['UseSchema'],$a['UseTable'], $error);
   switch($Action) {
      case "ACTION_alc_run_{$isa}_Select":
	     if (_writeAppReportUpdates($isa, $item, $problem)) {
	        list($action, $onRow) = explode('-',$ActionLine);
		    PRO_AddToParams("alc_run_{$isa}_onRow",$onRow);
			}
		 break;
      case "ACTION_alc_run_{$isa}_Delete":
	     list($action, $row) = explode('-',$ActionLine);
		 HDA_appReportUpdate($isa, 'DELETE', $item, $row, $a['UseConnect'],$a['UseSchema'],$a['UseTable'], $error);
		 _logUpdate($item, $row, 'DELETE', null);
		 break;
      case "ACTION_alc_run_{$isa}_Order":
	     list($action, $orderColumn) = explode('-',$ActionLine);
		 PRO_AddToParams("alc_run_{$isa}_orderColumn",$orderColumn);
		 $orderWay = PRO_ReadParam("alc_run_{$isa}_orderWay");
		 $orderWay = ($orderWay+1)&1;
		 PRO_AddToParams("alc_run_{$isa}_orderWay",$orderWay);
	     _writeAppReportUpdates($isa, $item, $problem);
		 break;
      case "ACTION_alc_run_{$isa}_Save":
		 break;
      }
   switch ($isa) {
      case 'apps':
         $onRow = PRO_ReadParam("alc_run_{$isa}_onRow");
		 break;
	  default:
	     $onRow = 'ID';
	     break;
	  }
	  
   $orderColumn = PRO_ReadParam("alc_run_{$isa}_orderColumn");
   $orderWay = PRO_ReadParam("alc_run_{$isa}_orderWay");
   if (is_null($orderColumn)) {
      foreach ($a['Fields'] as $column_name=>$column_details) {
	     if ($column_details['Hidden']<>1) {
		    $orderColumn = $column_name; 
			break;
			}
	     }
	  $orderWay = 0;
      PRO_AddToParams("alc_run_{$isa}_orderColumn",$orderColumn);
	  PRO_AddToParams("alc_run_{$isa}_orderWay",$orderWay);
      }
   $t = "";
   $t .= "<span style=\"color:red;text-align:center;\">{$problem}</span><br>";
   
   $tt = "";
   $tt .= "<div id=\"appViewTable\" style=\"width:".($_ViewWidth-34)."px;height:".($_ViewHeight-180)."px;overflow:auto;\">";
   $tt .= "<table class=\"alc-table\">";

   switch ($isa) {
      case 'apps':
		 _include_css(array("dhtmlxcalendar","dhtmlxcalendar_dhx_skyblue"));
		 _include_script(array("dhtmlxcalendar","AppGridView"));
         $t .= "<input type=\"hidden\" name=\"alc_run_{$isa}_updatedRows\" id=\"alc_run_{$isa}_updatedRows\" value=\"\">";
		 $t .= $tt;
         $t .= "<colgroup>";
         $t .= "<col style=\"width:18px;\">";
         $t .= "<col style=\"width:18px;\">";
         $t .= "<col style=\"width:18px;\">";
		 break;
	  case 'reports':
	  default:
	     $t .= $tt;
         $t .= "<colgroup>";
	  }
	  
   foreach ($a['Fields'] as $column_name=>$column_details) {
      if ($column_details['Hidden']<>1) {
		    switch ($column_details['Width']) {
			   case '*':
                  $t .= "<col style=\"width:auto;\">";
				  break;
			   default:
                  $t .= "<col style=\"width:{$column_details['Width']}px;\">";
				  break;
			   }
		}
      }
   $t .= "</colgroup>";
   $t .= "<tr>";
   
   switch ($isa) {
      case 'apps':
         $t .= "<th>&nbsp;</th>";
         $t .= "<th>&nbsp;</th>";
         $t .= "<th>&nbsp;</th>";
		 break;
	  default:
	     break;
      }
	  
   $validations = array();
   foreach ($a['Fields'] as $column_name=>$column_details) {
      $validator =  hda_db::hdadb()->HDA_DB_validationCode(null, null, $column_details['Validation']);
	  if (is_array($validator) && count($validator)==1) {
	     if (HDA_whatValidation($validator[0]['LookupId'], $valueType, $value, $error))
	        $validations[$column_name] = array($valueType, $value);
	     }
	  }
   foreach ($schema as $column) {
	  $column_name = $column['COLUMN_NAME'];
	  if ($a['Fields'][$column_name]['Hidden']<>1) {
		    $mouse = "onclick=\"issuePost('alc_run_{$isa}_Order-{$column_name}--',event); return false;\" ";
            $t .= "<th style=\"background-color:lightgray;border:2px groove white;\" {$mouse} >";
			if ($column_name==$orderColumn) {
			   $orderImg = (($orderWay&1)<>1)?"sort_asc.gif":"sort_desc.gif";
			   $t .= "<div style=\"display:inline;float:right;\">";
			   $t .= _emit_image($orderImg,10)."</div>";
			   }
			$t .= "<span>{$a['Fields'][$column_name]['Label']}</span>";
			$t .= "</th>";
         }	     
	  }
   $t .= "</tr>";
   $data = HDA_appReportFetch($isa, 'FETCH',$a['UseConnect'],$a['UseSchema'],$a['UseTable'], $error, $onRow, $orderColumn, $orderWay, $rowsOnPage);
   switch ($Action) {
     case "ACTION_alc_run_{$isa}_Undo":
	     list($action, $app_item, $app_row) = explode('-',$ActionLine);
	     $undo_row = _unCacheRow($app_item, $app_row);
         if (is_array($undo_row)) for ($i = 0; $i<count($data); $i++) {
		    if ($data[$i]['ID']==$undo_row['ID']) {
			   $data[$i] = $undo_row;
			   $fields = array_keys($undo_row);
			   foreach ($fields as $field) {
			      $f = "V-{$undo_row['ID']}-"._nameToId($field);
			      $post_load .= "updatedField('{$isa}','{$f}');";
				  }
			   }
            }		 
	     break;
      case "ACTION_alc_run_{$isa}_NewRecord":
	     list($action, $is, $item) = explode('-',$ActionLine);
		 $newrow = array();
	     foreach ($schema as $field) {
	        $column_name = $field['COLUMN_NAME'];
			if (array_key_exists($column_name, $validations)) {
			   switch ($validations[$column_name][0]) {
			      case 'Range': 
				     $newrow[$column_name] = $validations[$column_name][1]['Min'];
				     break;
				  case 'SingleValue':
				     $newrow[$column_name] = $validations[$column_name][1];
				     break;
				  case 'List':
				     $newrow[$column_name] = (count($validations[$column_name][1])>0)?$validations[$column_name][1][0][1]:"";
					 break;
				  default: 
				     $newrow[$column_name] = null;
				     break;
				 }
			   }
			else {
			   switch ($field['DATA_TYPE']) {
			      case 'datetime':
				  case 'date':
				     $newrow[$column_name] = null;
					 break;
				  case 'float':
				  case 'decimal':
				  case 'real':
				  case 'numeric':
				     $newrow[$column_name] = 0.0;
					 break;
				  case 'int':
				     $newrow[$column_name] = 0;
				  case 'nvarchar':
				  case 'varchar':
				     $newrow[$column_name] = "";
					 break;
				  default:
			         $newrow[$column_name] = null;
					 break;
				  }
			   }
		    }
		 $newrow['ID'] = $onRow = HDA_isUnique('ID');
		 array_unshift($data, $newrow);
		 PRO_AddToParams("alc_run_{$isa}_onRow",$onRow);
		 $db_row = $newrow;
		 foreach ($a['Fields'] as $column_name=>$details) {
		    switch ($details['Editor']) {
			   case 'RO': unset($db_row[$column_name]); break;
			   default: break;
			   }
		    }
		 HDA_appReportUpdate($isa, 'INSERT', $item, $db_row, $a['UseConnect'],$a['UseSchema'],$a['UseTable'], $error);
		 _logUpdate($item, $onRow, 'INSERT', $newrow);
		 if (strlen($error)>0) $t .= "<tr><td style=\"color:red;\" colspan=".(count($a['Fields'])+3).">{$error}</td></tr>";
		 break;
      }
   foreach ($data as $row) {
      $t .= "<tr>";
	  switch ($isa) {
	     case 'apps':
	        $select_mouse = "onclick=\"issuePost('alc_run_{$isa}_Select-{$row['ID']}---',event);return false;\" ";
	        $t .= "<td {$select_mouse} ><span class=\"click-here\" title=\"Select Row..\" >";
	        $t .= ($selectedRow = ($onRow==$row['ID']))?_emit_image("arrow_right.png",10):"&nbsp;";
	        $t .= "</span>";
	        if ($selectedRow) {
		       $is_cached = _cacheRow($item, $row);
	           $mouse = "onclick=\"issuePost('alc_run_{$isa}_Delete-{$row['ID']}---',event);return false;\" ";
	           $t .= "<td {$mouse} ><span class=\"click-here\" title=\"Delete Row..\" >";
	           $t .= _emit_image("DeleteThis.jpg",10);
	           $t .= "</span>";
	           $mouse = "onclick=\"issuePost('alc_run_{$isa}_Undo-{$item}-{$row['ID']}---',event);return false;\" ";
		       $vis = ($is_cached)?"visible":"hidden";
	           $t .= "<td {$mouse} ><span class=\"click-here\" title=\"Undo..\" id=\"alc_run_{$isa}_Undo\" style=\"visibility:{$vis}\" >";
	           $t .= _emit_image("CancelThis.jpg",10);
	           $t .= "</span>";
		       }
	        else $t .= "<td>&nbsp;</td><td>&nbsp;</td>";
            break;
		 default:
		    break;
		 }
		 
	  $calendar_count = 1;
	  foreach ($schema as $field) {
	     $column_name = $field['COLUMN_NAME'];
		 $style = $a['Fields'][$column_name]['Style'];
	     $use_validate =(array_key_exists($column_name, $validations))?$validations[$column_name][0]:'NONE';
         if ($selectedRow) {
			$help = "NO Validation Specified";
			$f = "V-{$row['ID']}-"._nameToId($column_name);
			$mouse = "onchange=\"updatedField('{$isa}', '{$f}'); return false;\" ";
		    $key_press = "onKeyPress=\"return keyPressUpdate('{$isa}','{$f}', event);\" ";
			$add_editor = $a['Fields'][$column_name]['Editor'];
			if ($a['Fields'][$column_name]['Hidden']<>1) {
				  switch (strtoupper($add_editor)) {
				     case 'RO': break;
					 default:
				        switch (strtolower($field['DATA_TYPE'])) {
				           case 'datetime':
					       case 'date':
						       $add_editor = 'DATE';
					           break;
						   }
					 }
				  switch (strtoupper($add_editor)) { 
					 case 'DATE':
					     $format = $a['Fields'][$column_name]['Format'];
						 switch ($format) {
						    default:
						       if (!@preg_match("/[%yYmMdDhHi]{1,}/",$format)) {
						          $format = "%Y-%m-%d %H:%i";
							      }
							   break;
						    case '*': 
							   $format = "%Y-%m-%d %H:%i";
							   break;
							case 'Ymd':
							case 'YYYYmmdd':
							case 'yyyymmdd':
							case 'ymd':
							   $format = "%Y%m%d";
							   break;
						    }
						 _append_script("
				         function loadCalendar_{$calendar_count}() { var t = new dhtmlXCalendarObject(['{$f}']);
                            t.setDateFormat('{$format}');
                            t.attachEvent(\"onClick\",function(date){
                               updatedField('{$isa}','{$f}');
                               });};");
                         global $post_load;
                         $post_load .= "loadCalendar_{$calendar_count}();";
					     $calendar_count++;
						 $key_press = "";
					     break;
					 }
				  switch ($use_validate) {
					    case 'Range': 
						   $help = "Valid values in range from {$validations[$column_name][1]['Min']} to {$validations[$column_name][1]['Max']}";
						   $style= ($row[$column_name]<$validations[$column_name][1]['Min'] || $row[$column_name]>$validations[$column_name][1]['Max'])?"style=\"color:red;\"":"";
			               $t .= "<td style=\"{$style}\" ><input type=\"text\" name=\"{$f}\" value=\"{$row[$column_name]}\" {$mouse} {$key_press} {$style} title=\"{$help}\" ></td>";
						   break;
						case 'SingleValue':
						   $help = "Valid value only {$validations[$column_name][1]}";
						   if($row[$column_name]<>$validations[$column_name][1]) {
			                  $t .= "<td><input type=\"text\" name=\"{$f}\" value=\"{$row[$column_name]}\" {$mouse}  style=\"color:red;\" {$key_press}  title=\"{$help}\" ></td>";
							  }
						   else $t .= "<td>{$validations[$column_name][1]}</td><input type=\"hidden\" name=\"{$f}\" value=\"{$validations[$column_name][1]}\">"; 
						   break;
						case 'List':
						   $help = "Select value from list";
						   $t .= "<td title=\"{$help}\"  style=\"{$style}\" >";
						   $ok_value = false;
						   foreach($validations[$column_name][1] as $list)  {
						      if (_cmp_null_empty($row[$column_name],$list[1])) $ok_value = true;
						      elseif (($row[$column_name]==trim($list[1])) || (strcasecmp($row[$column_name], trim($list[1]))==0)) $ok_value = true;
							  if ($ok_value) break;
							  }
						   if (!$ok_value) {
						      $t .= "<span style=\"color:red;\">{$row[$column_name]} not valid  from </span>";
							  }
						   $t .= "<select name=\"{$f}\" {$mouse}>";
						   foreach($validations[$column_name][1] as $list) {
						      $valid_list_item = trim($list[0]);
							  $valid_list_key = trim($list[1]);
						      $selected = ($valid_list_key==$row[$column_name])?"SELECTED":"";
							  $t .= "<option value=\"{$valid_list_key}\" {$selected} >{$valid_list_item}</option>";
							  }
						   $t .= "</select></td>";
						   break;
						default: 
						   switch (strtoupper($add_editor)) {
						      case 'RO': 
							     $t .= "<td {$select_mouse}  style=\"{$style}\" >".$row[$column_name]."</td>"; 
								 $t .= "<input type=\"hidden\" name=\"{$f}\" id=\"{$f}\" value=\"{$row[$column_name]}\"  >";
							     break;
							  default:
			                     $t .= "<td style=\"{$style}\" ><input type=\"text\" name=\"{$f}\" id=\"{$f}\" value=\"{$row[$column_name]}\" {$mouse}  {$key_press} title=\"{$help}\" ></td>";
								 break;
							  }
						   break;
						}
					 }
				  else $t .= "<input type=\"hidden\" name=\"{$f}\" id=\"{$f}\" value=\"{$row[$column_name]}\"  >";
			      }
		       elseif ($a['Fields'][$column_name]['Hidden']<>1) {
			      $v = $row[$column_name];
				  switch ($use_validate) {
				     case 'List':
						foreach($validations[$column_name][1] as $list) {
						   $valid_list_item = trim($list[0]);
						   $valid_list_key = trim($list[1]);
						   if ($valid_list_key==$row[$column_name]) $v = $valid_list_item;
						   }
					    break;
					 default:
					    break;
				     }
				  //$v = str_replace("<",'&lt',$v); $v = str_replace(">",'&gt',$v);
				  $v = htmlspecialchars($v);
			      $t .= "<td {$select_mouse}  style=\"{$style}\" >{$v}</td>";
				  }
			
		 }
      $t .= "</tr>";
	  }
   $t .= "</table></div>";
   
   return $t;
   }	  
   
   
function _appReportViewRecord($isa, $item) {
   global $Action;
   global $ActionLine;
   global $DevUser;
   global $code_root, $home_root;
   global $post_load;
   global $_ViewHeight;

   $t = "";
   
   return $t;
   }	  

function _dialogAppHistoryLog($dialog_id='alc_app_history') {
   global $Action;
   global $ActionLine;
   global $UserCode;
   global $code_root, $home_root;
   
   $item = null;
   $problem = null;
   switch ($Action) {
      case "ACTION_{$dialog_id}":
	     list($action, $isa, $item) = explode('-',$ActionLine);
		 break;
      case "ACTION_{$dialog_id}_Revert":
	     list($action, $isa, $item, $url_post) = explode('-',$ActionLine);
		 $post = hda_db::hdadb()->HDA_DB_unserialize(base64_decode($url_post));
		 $a = hda_db::hdadb()->HDA_DB_apps_reports($isa, $item);
         if (!HDA_appReportUpdate($isa, 'UPDATE', $item, $post, $a[0]['UseConnect'],$a[0]['UseSchema'],$a[0]['UseTable'], $problem)) {
			}
		 else _logUpdate($item, $post['ID'], 'UPDATE', $post);
		 break;
      default: return "";
	     break;
	   }
   
   $a = hda_db::hdadb()->HDA_DB_appReportLogItems($isa, $item);
   $t = _makedialoghead($dialog_id, "Updates History", "alc-dialog-vlarge");
   if (!is_null($problem)) $t .= "<tr><td style=\"color:red;\">{$problem}</td></tr>";
   if (!is_array($a) || count($a)==0) {
      $t .= "<tr><th>No updates in log for this application</th></tr>";
	  }
   else {
      $t .= "<tr><td><div style=\"height:300px;overflow:auto;\"><table class=\"alc-table\">";
      foreach ($a as $row) {
         $t .= "<tr>";
	     $t .= "<td>".hda_db::hdadb()->PRO_DBdate_Styledate($row['UpdateDate'], true)."</td>";
	     $t .= "<td>{$row['UpdateByName']}</td>";
	     $t .= "</tr>";
	     $can_rollback = true;
		 $schema_error = "";
	     foreach ($row['PostUpdate'] as $column_name=>$v) {
		    switch ($column_name) {
			   case 'RowNum': 
			   case 'ID':
			      break;
			   default:
		          if (!array_key_exists($column_name,$row['PreUpdate'])) {
				     $can_rollback = false;
					 $schema_error .= "{$column_name}; ";
					 }
				  break;
			   }
			}
	     if ($can_rollback) {
		    $is_changed = false;
	        $t .= "<tr><td style=\"border-bottom:2px solid green;\">";
	        foreach ($row['PreUpdate'] as $column_name=>$v) if (array_key_exists($column_name, $row['PostUpdate'])) {
			   switch ($column_name) {
			      case 'RowNum':
				  case 'ID':
				     break;
				  default:
				     if ($v <> $row['PostUpdate'][$column_name]) {
					    $is_changed = true;
					    $v = (is_null($v) || strlen($v)==0)?"Null or Empty":$v;
					    $v1 = $row['PostUpdate'][$column_name];
						$v1 = (is_null($v1) || strlen($v1)==0)?"Null or Empty":$v1;
	                    $t .= "<span style=\"font-style:bold;\">{$column_name}:</span>";
						$t .= "<span style=\"color:green;font-style:italic;\">{$v}</span>";
						$t .= "=>";
						$t .= "<span style=\"color:blue;font-style:bold;\">{$v1}</span>; ";
						}
					 break;
				  }
	           }
			if (!$is_changed) $t .= "<span style=\"font-style:italic;color:green;\">Identical</span>";
			$t .= "<br><div style=\"color:gray; \">";
			foreach ($row['PostUpdate'] as $column_name=>$v) $t .= "{$column_name}=>{$v}; ";
			$t .= "</div>";
			$t .= "</td><td style=\"border-bottom:2px solid green;\">";
			if ($is_changed) {
			   $url_post = base64_encode(hda_db::hdadb()->HDA_DB_serialize($row['PostUpdate']));
	           $mouse = _click_dialog($dialog_id, "_Revert-{$isa}-{$item}-{$url_post}");
               $t .= "<span style=\"cursor:pointer;height:16px;\" title=\"Revert to these values..\" {$mouse}>";
               $t .= _emit_image("Revert.jpg",24)."</span>";
			   }
			$t .= "</td></tr>";
		    }
	     else {
		    $t .= "<tr><td colspan=2 style=\"border-bottom:2px solid green;\">";
			$t .= "<div>Mismatch on column names, schema changed<br> {$schema_error}";
			$t .= "</div></td></tr>";
			}
         }
	   $t .= "</table></div></td></tr>";
	   }
   $t .= _makedialogclose();

   return $t;
   }
   
function _dialogImportAppReport($dialog_id='alc_import_app') {
   global $Action;
   global $ActionLine;
   global $UserCode;
   global $UserName;
   global $_Mobile;
   global $code_root, $home_root;

   $problem = null;
   $pack = null;
   $a = null;
   $doThis = null;
   $this_is_overwrite = true;
   $upf_code = PRO_ReadParam("UPLOAD_PKG_upid");
   $pf_code = null;
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}":
      case "ACTION_{$dialog_id}_Save":
	     list($action, $isa) = explode('-',$ActionLine);
		 PRO_AddToParams("UPLOAD_PKG_isa", $isa);
         if (is_null($upf_code)) { $upf_code = HDA_isUnique(($isa=='apps')?'AP':'RP'); PRO_AddToParams("UPLOAD_PKG_upid", $upf_code); }
         break;
      case "ACTION_{$dialog_id}_ImportedFile";
	     $isa = PRO_ReadParam("UPLOAD_PKG_isa");
         $import = _actionImportFileDiv($dialog_id, $problem);
         if (!is_null($import)) {
            if ($import['Extension']=='zip') {
               $pack = HDA_unzip($import['UploadedPath'], $upf_code, $problem, pathinfo($import['UploadedPath'],PATHINFO_DIRNAME));
               if (is_null($problem)) {
                  foreach($pack as $in_pack) {
                     if (strtolower($in_pack['Filename'])=='app.xml') {
                        $s = file_get_contents($in_pack['Path']);
                        $a = xml2ary($s);
                        break;
                        }
                     }
                  }
               }
            elseif ($import['Extension']=='xml') {
               $s = file_get_contents($import['UploadedPath']);
               $a = xml2ary($s);
               }
            else $problem = "Can only upload a package zip file or app xml file";
            if (!is_null($a) && is_array($a)) {
               $doThis = "MAKE_NEW";
               $aa = hda_db::hdadb()->HDA_DB_apps_reports($isa, NULL,  _query($isa,$a,'Title'));
               if (!is_null($aa) && is_array($aa) && count($aa)>0) {
                  $doThis = "ASK_OVERWRITE";
                  PRO_AddToParams("UPLOAD_PKG_profile", $a);
                  PRO_AddToParams("UPLOAD_PKG_pack", $pack);
                  PRO_AddToParams("UPLOAD_PKG_id", $aa[0]['ItemId']);
                  PRO_AddToParams("UPLOAD_PKG_import", $import);
                  $problem = "The App <b>{$aa[0]['Title']}</b> already exists";
                  }
               }
            else {
               $problem = "There is no app.xml file uploaded or in the package";
               $doThis = null;
               }
            }
         else $problem = "No data fetched - {$problem}";
         break;
      case "ACTION_{$dialog_id}_MAKENEW":
	     $isa = PRO_ReadParam("UPLOAD_PKG_isa");
         $pf_code = HDA_isUnique(($isa=='apps')?'AP':'RP');
         $this_is_overwrite = false;
      case "ACTION_{$dialog_id}_OVERWRITE":
	     $isa = PRO_ReadParam("UPLOAD_PKG_isa");
         $a = PRO_ReadParam("UPLOAD_PKG_profile");
         $pack = PRO_ReadParam("UPLOAD_PKG_pack");
         $import = PRO_ReadParam("UPLOAD_PKG_import");
         if (is_null($pf_code)) { // overwrite
            $pf_code = PRO_ReadParam("UPLOAD_PKG_id");
            }
         else { // make copy
            _query($isa, $a, 'Title', null, _query($isa, $a, 'Title')."_Copy_".hda_db::hdadb()->PRO_DB_stampTime());
            }
         break;
       case "ACTION_{$dialog_id}_CANCEL":
	     $isa = PRO_ReadParam("UPLOAD_PKG_isa");
         $a = null;
         $import = PRO_ReadParam("UPLOAD_PKG_import");
         PRO_Clear(array("UPLOAD_PKG_profile","UPLOAD_PKG_pack","UPLOAD_PKG_import","UPLOAD_PKG_id","UPLOAD_PKG_upid","UPLOAD_PKG_isa"));
         _rrmdir(pathinfo($import['UploadedPath'],PATHINFO_DIRNAME));

         break;
      }
   $t = _makedialoghead($dialog_id, "Import App or Report..");
   if (!is_null($problem)) $t .= "<tr><th style=\"color:red;\" colspan=2 >{$problem}</th></tr>";
   if (is_null($a)) {
      $t .= "<tr><th>"._insertImportFileDiv($dialog_id, $upf_code, "UploadProfile")."</th></tr>"; 
      }
   elseif ($doThis=='ASK_OVERWRITE') {
      $t .= "<tr><th colspan=2>";
      $t .= _emit_image("QuestionThis.jpg",24);
      $t .= "Do you want to:</th></tr>";
      $mouse = _click_dialog($dialog_id,"_OVERWRITE");
      $t .= "<tr>";
      $t .= "<td {$mouse} ><span class=\"click-here\"  >";
      $t .= "<u><b>Overwrite</b></u> - replace - the existing App or Report?</span></td>";
      $t .= "<th>"._emit_image("ReplaceThis.jpg",24,$mouse)."</th>";
      $t .= "</tr>";
      $mouse = _click_dialog($dialog_id,"_MAKENEW");
      $t .= "<tr>";
      $t .= "<td {$mouse}><span class=\"click-here\" >";
      $t .= "Create a <u><b>Copy</b></u> - keep existing App or Report?</span></td>";
      $t .= "<th>"._emit_image("AddCopy.jpg",24,$mouse)."</th>";
      $t .= "</tr>";
      $mouse = _click_dialog($dialog_id,"_CANCEL");
      $t .= "<tr>";
      $t .= "<td {$mouse} ><span class=\"click-here\">";
      $t .= "<u><b>Cancel</b></u> - discard the fetch and leave existing App or Report untouched?</span></td>";
      $t .= "<th>"._emit_image("CancelThis.jpg",24,$mouse)."</th>";
      $t .= "</tr>";
      $t .= _makedialogclose();

      return $t;
      }
   else {
      if (is_null($pf_code)) $pf_code = HDA_isUnique(($isa=='apps')?'AP':'RP');
      $code = $pf_code;
	  $app = _xml_to_app($isa, $a, $users);
      $app['ItemId'] = $pf_code;
      hda_db::hdadb()->HDA_DB_writeAppReport($isa, $code, $app);
	  if (!is_null($users) && is_array($users) && count($users)>0) hda_db::hdadb()->HDA_DB_appReportUsers($isa, $code, $users);
      $t .= "<tr><td colspan=2>Imported App or Report {$app['Title']}</td></tr>";
	  }
   $t .= _closeDialog($dialog_id, "_Save-{$isa}---", 2);
   $t .= _makedialogclose();

   return $t;
   }
function _dialogExportAppReport($dialog_id='alc_export_app') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;

   $problem = null;
   $item = null;
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
	  case "ACTION_{$dialog_id}_Refresh":
         list($action, $isa, $item) = explode('-',$ActionLine);
         break;
      }
   if (is_null($item)) return "";
   $a = hda_db::hdadb()->HDA_DB_apps_reports($isa, $item);
   if (is_null($a) || !is_array($a) || count($a)<>1) { $problem = "Error reading app"; $a = null; }
   else $a = $a[0];
   $t = _makedialoghead($dialog_id, "Export App {$a['Title']}");
   if (!is_null($a)) $xml = _app_to_xml($isa, $a);
   if (!is_null($problem)) $t .= "<tr><th style=\"color:red;\" colspan=2 >{$problem}</th></tr>";
   if (!is_null($a) && is_array($a)) {
      $t .= "<tr><th colspan=2><textarea class=\"alc-dialog-text\" style=\"height:200px;\" wrap=off >{$xml}</textarea></th></tr>";
      }
   $tmp_dir = "tmp/";
   $tmp_dir .= $item;
   if (!file_exists($tmp_dir)) mkdir($tmp_dir);
   @file_put_contents($fpath = "{$tmp_dir}/app.xml", $xml); _chmod($fpath);


   $t .= "<tr><th colspan=2>"._insertDownloadFileDiv($dialog_id, $fpath)."</th></tr>";
   $t .= _closeDialog($dialog_id, true, 2);
   $t .= _makedialogclose();

   return $t;
   }
   
function _xml_to_app($isa, $a, &$users) {
   global $UserCode;
   $app = array();
	  $owner = _query($isa,$a,'Owner');
	  $owner_user = hda_db::hdadb()->HDA_DB_FindUser($owner);
      $app['CreatedBy'] = (is_array($owner_user) && count($owner_user)==1)?$owner_user[0]['UserItem']:$UserCode;
      $app['CreateDate'] = hda_db::hdadb()->PRO_DB_dateNow();
      $app['Title'] = _query($isa,$a,'Title');
      $app['ItemText'] = _query($isa,$a,'Description');
	  $app['Header'] = hda_db::hdadb()->HDA_DB_textFromDB(_query($isa,$a,'Header'));
	  $app['Footer'] = hda_db::hdadb()->HDA_DB_textFromDB(_query($isa,$a,'Footer'));
	  $app['Fields'] = hda_db::hdadb()->HDA_DB_unserialize(hda_db::hdadb()->HDA_DB_textFromDB(_query($isa,$a,'Fields')));
	  $app['UseSchema'] = _query($isa,$a,'UseSchema');
	  $app['UseTable'] = _query($isa,$a,'UseTable');
	  $use_connect = hda_db::hdadb()->HDA_DB_dictionary(NULL, _query($isa,$a,'UseConnect'));
	  if (!is_null($use_connect) && is_array($use_connect) && count($use_connect)==1) $use_connect = $use_connect[0]['ItemId'];
	  else $use_connect = "";
	  $app['UseConnect'] = $use_connect;
	  $x = _query($isa,$a,'Users');
	  $users = array();
	  if (is_array($x)) {
	     foreach($x as $user_e) {
		    $user = hda_db::hdadb()->HDA_DB_FindUser($user_e);
			if (is_array($user) && count($user)==1) $users[] = $user[0]['UserItem'];
		    }
	     }

   return $app;
   }

function _app_to_xml($isa, $a) {
   $xml= "";
   $xml = "<{$isa}>";
   $xml .= "<Title>{$a['Title']}</Title>\n";
   $xml .= "<Description>{$a['ItemText']}</Description>\n";
   $owner = hda_db::hdadb()->HDA_DB_FindUser($a['CreatedBy']);
   if (is_array($owner) && count($owner)==1)
      $xml .= "<Owner>{$owner[0]['Email']}</Owner>\n";
   $xml .= "<Header>".hda_db::hdadb()->HDA_DB_textToDB($a['Header'])."</Header>\n";
   $xml .= "<Footer>".hda_db::hdadb()->HDA_DB_textToDB($a['Footer'])."</Footer>\n";
   $xml .= "<Fields>".hda_db::hdadb()->HDA_DB_textToDB(hda_db::hdadb()->HDA_DB_serialize($a['Fields']))."</Fields>\n";
   $xml .= "<UseSchema>{$a['UseSchema']}</UseSchema>\n";
   $xml .= "<UseTable>{$a['UseTable']}</UseTable>\n";
   $d = hda_db::hdadb()->HDA_DB_dictionary($a['UseConnect']);
   if (is_null($d) || !is_array($d) || count($d)<>1) $use_connect = "";
   else $use_connect = $d[0]['Name'];
   $xml .= "<UseConnect>{$use_connect}</UseConnect>\n";
   $xml .= "<Users>\n";
   $users = hda_db::hdadb()->HDA_DB_appReportUsers($isa, $a['ItemId']);
   foreach($users as $user) {
      $user_e = hda_db::hdadb()->HDA_DB_FindUser($user);
	  if (is_array($user_e) && count($user_e)==1) $xml .= "   <User>{$user_e[0]['Email']}</User>\n";
      }
   $xml .= "</Users>\n";
   $xml .= "</{$isa}>\n";
   return $xml;
   }
   
function _dialogImportAppData($dialog_id='_dialogImportAppData') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;

   $problem = null;
   $item = null;
   $data = null;
   $filename = null;
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
         list($action, $isa, $item) = explode('-',$ActionLine);
         break;
      case "ACTION_{$dialog_id}_Import":
         list($action, $up_path, $isa, $item) = explode('-',$ActionLine);
         try {
           if (isset($_FILES) && isset($_FILES[$up_path]) &&
              strlen($_FILES[$up_path]['name'])>0 && isset($_FILES[$up_path]['tmp_name']) && strlen($_FILES[$up_path]['tmp_name'])>0) {
              $loc_path = "tmp/{$item}"; if (!file_exists($loc_path)) mkdir($loc_path);
              move_uploaded_file($_FILES[$up_path]['tmp_name'],$filename="{$loc_path}/{$item}.csv");
              }
           else { $problem = "Problem in upload - maybe, file too big "; $problem .= print_r($_FILES, true); }
           }
         catch (Exception $e) {
           $problem = "Problem in upload - ".$e->getMessage();
           }
         break;
      }
	if (is_null($filename)) {}
    else if(!file_exists($filename) || !is_readable($filename))
        $problem = "Bad file";
	else {
		$header = NULL;
		$data = array();
		if (($handle = fopen($filename, 'r')) !== FALSE) {
				$delimiter=',';
			while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
				if(!$header)
					$header = $row;
				else
					$data[] = array_combine($header, $row);
			}
			fclose($handle);
		}
	}

   if (is_null($item)) return "";
   $a = hda_db::hdadb()->HDA_DB_apps_reports($isa, $item);
   if (is_null($a) || !is_array($a) || count($a)<>1) { $problem = "Error reading app"; $a = null; }
   else $a = $a[0];
   $t = _makedialoghead($dialog_id, "Import Data {$a['Title']}");
         $t .= "<tr><td colspan=2>";
         $t .= "Upload data file:&nbsp;<input type=\"file\" name=\"AppData\"  value=\"\" >";
         $mouse = _click_dialog($dialog_id,"_Import-AppData-{$isa}-{$item}--");
		 $t .= "<span class=\"push_button blue\" {$mouse} >Upload</span>"; 
		 $t .= "</td></tr>";
   if (is_array($data)) {
	   $rows_added = 0;
	   foreach ($data as $record) {
		   if (!HDA_appReportUpdate($isa, 'INSERT', $item, $record, $a['UseConnect'],$a['UseSchema'],$a['UseTable'], $error))
			   $t .= "<tr><td colspan=2>Insert fails {$error}</td></tr>";
			else $rows_added++;
	   }
	   $t .= "<tr><td colspan=2>Rows Inserted {$rows_added}</td></tr>";
   }
   $t .= _closeDialog($dialog_id, true, 2);
   $t .= _makedialogclose();

   return $t;
}
 
function _dialogExportAppReportData($dialog_id='alc_export_app_data') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;

   $problem = null;
   $item = null;
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
	  case "ACTION_{$dialog_id}_Refresh":
         list($action, $isa, $item) = explode('-',$ActionLine);
         break;
      }
   if (is_null($item)) return "";
   $a = hda_db::hdadb()->HDA_DB_apps_reports($isa, $item);
   if (is_null($a) || !is_array($a) || count($a)<>1) { $problem = "Error reading app"; $a = null; }
   else $a = $a[0];
   $t = _makedialoghead($dialog_id, "Export Data {$a['Title']}");
   $tmp_dir = "tmp/";
   $tmp_dir .= $item;
   if (!file_exists($tmp_dir)) mkdir($tmp_dir);
   $onRow = PRO_ReadParam("alc_run_{$isa}_onRow");
   $orderColumn = PRO_ReadParam("alc_run_{$isa}_orderColumn");
   $orderWay = PRO_ReadParam("alc_run_{$isa}_orderWay");
   $data = HDA_appReportFetch($isa, 'DATA',$a['UseConnect'],$a['UseSchema'],$a['UseTable'], $error, $onRow, $orderColumn, $orderWay);
   $fpath = "{$tmp_dir}/{$a['Title']}.csv";
   $fp = fopen($fpath, 'w');
   if (count($data)>0) {
      $s = "";
      foreach ($data[0] as $k=>$v) $s .= "\"{$k}\","; $s = trim($s,',');
	  fwrite($fp, "{$s}\r\n");
      }
   foreach ($data as $row) {
      $s = "";
      foreach ($row as $k=>$v) $s .= "\"{$v}\","; $s = trim($s,',');
	  fwrite($fp, "{$s}\r\n");
      }
   fclose($fp);
   $t .= "<tr><td colspan=2>Download ".count($data)." rows</td></tr>";
   $t .= "<tr><th colspan=2>"._insertDownloadFileDiv($dialog_id, $fpath)."</th></tr>";
   $t .= _closeDialog($dialog_id, true, 2);
   $t .= _makedialogclose();

   return $t;
   }
   

function _appLogMonitor() {
   global $_ViewWidth;
   global $_ViewHeight;
   $t = "";
   $t .= "<div style=\"width:".($_ViewWidth-30)."px;height:".($_ViewHeight-60)."px;overflow:auto;\" ><table class=\"alc-table\">";
   $a = hda_db::hdadb()->HDA_DB_apps_reports('apps');
   foreach ($a as $row) {
	  $t .= "<tr><td><span class=\"click-here\" >{$row['Title']}</span></td>";
      $t .= "<td>".substr($row['ItemText'],0,80)." ...</td>";
	  $t .= "<td>Last data update by ".hda_db::hdadb()->HDA_DB_GetUserFullName($row['LastDataUpdateBy'])." on ".hda_db::hdadb()->PRO_DBdate_Styledate($row['LastDataUpdate'], true)."</td>";
	  $t .= "</tr>";
      }
   $t .= "</table></div>";

   return $t;
   }
   
?>