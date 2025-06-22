<?php

function _planViewTree($filter) {
   global $Action;
   global $ActionLine;
   global $DevUser;
   global $UserCode;
   global $code_root, $home_root;
   global $post_load;
   global $_ViewHeight;
   
   switch ($Action) {
      case "ACTION_TreeView_Save":
	     $id = PRO_ReadParam('onSelectId');
		 $ok = hda_db::hdadb()->HDA_DB_UpdateProfile($id, array('ItemText'=>PRO_ReadParam('onSelectNotes')));
		 $rule = PRO_ReadParam('onSelectPassed');
		 $rule = ($rule=='D')?PRO_ReadParam('onSelectPassedAgo'):$rule;
		 $ok &= hda_db::hdadb()->HDA_DB_relationRule($id, $rule);
		 $ok &= hda_db::hdadb()->HDA_DB_relationEnabled($id, (PRO_ReadParam('onSelectEnabled') | PRO_ReadParam('onSelectProxy')));
		 $fail = PRO_ReadParam('onSelectFail');
		 $fail =($fail=='H')?((PRO_ReadParam('onSelectFailRetry')<<8)+PRO_ReadParam('onSelectFailAgo')):$fail;
		 $ok &= hda_db::hdadb()->HDA_DB_relationFail($id, $fail);
		 $def = PRO_ReadParam('onSelectDefault');
		 $def = ($def=='H')?((PRO_ReadParam('onSelectDefaultDays')).":".PRO_ReadParam('onSelectDefaultTod')):$def;
		 $ok &= hda_db::hdadb()->HDA_DB_relationDefault($id, $def);
		 PRO_AddToParams('onUpdateStatus',($ok)?'Ok':'Failed update');
		 $days = PRO_ReadParam('onSelectDataDay');
		 $datadays = 0;
		 if (is_array($days)) foreach($days as $day) $datadays |= (1<<$day);
		 $ok &= hda_db::hdadb()->HDA_DB_relationDataDays($id,$datadays);
	     break;
      case "ACTION_TreeView_EventReset":
	     $id = PRO_ReadParam('onSelectId');
	     hda_db::hdadb()->HDA_DB_ResetSysEvents($id);
		 break;
	  case "ACTION_alc_category_filter_Clear":
	     PRO_Clear(array('onSelectNotes','onSelectDetails','onSelectImage','onUpdateStatus'));
	     break;
      }
   $enabled = ($DevUser)?"":"disabled='disabled'";
   $readonly = ($DevUser)?"":" READONLY DISABLED ";
   $t = "";
   $t .= "<table style=\"border:none\" class=\"tree-view-table\" >";
   $t .= "<colgroup>";
   $t .= "<col style=\"width:70%;\">";
   $t .= "<col style=\"width:30%;\">";
   $t .= "</colgroup>";
   $t .= "<tr><td>";
   $t .= "<div id=\"planViewTree\" style=\"width:100%;height:".($_ViewHeight-70)."px;overflow-x:hidden;overflow-y:auto;\">";
   $t .= "<input type=\"hidden\" id=\"savedTree\" value=\"\">";
   _include_css("dhtmlxtree");
   _include_script(array("dhtmlxcommon","dhtmlxtree","dhtmlxdataprocessor","TreeView"));
   $t .= "</div>";
   $t .= "</td>";
   $t .= "<td  >";
   $t .= "<div style=\"width:100%;height:".($_ViewHeight-70)."px;overflow-x:hidden;overflow-y:hidden;\">";
   $t .= "<div class=\"alc-display-div\" style=\"margin:4px;font-style:normal;font-size:10px;padding-bottom:0px;\" >";
   $t .= "<table style=\"border:none\" class=\"tree-view-table\" >";
   $t .= "<tr><td><input type=\"text\" id=\"onSelectTitle\" style=\"background-color:lightgray;color:blue;\" value=\"\" size=56>";
   if ($DevUser) {
      $mouse = "onclick=\"gotoSelectedItem('gotoTab-LD',event); return false;\" ";
      $t .= "&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Select this..\" {$mouse}>";
      $t .= _emit_image("GoForward.jpg",18)."</span>";
	  }
   $t .= "</td></tr>";
   $t .= "<input type=\"hidden\" id=\"onSelectId\" name=\"onSelectId\" value=\"".PRO_ReadParam('onSelectId')."\" >";
   $t .= "<input type=\"hidden\" id=\"onOpenState\" name=\"onOpenState\" value=\"".PRO_ReadParam('onOpenState')."\" >";
   $t .= "<tr><td>";
   $t .= "<div id=\"onSelectError\" ></div>";
   $t .= "<div  style=\"width:392px;height:340px;\" >";
   $t .= "<div id=\"TreeView_DetailsDiv\" style=\"display:inline;\" >";
   $t .= "<div style=\"float:left;\">"._emit_image("TAG_QUESTION.jpg",24, null, "id=\"onSelectImage\"")."</div>";
   if ($DevUser) {
      $mouse = "onclick=\"issuePost('TreeView_EventReset-'+getValue('onSelectId')+'--',event);return false;\" ";
      $t .= "<div style=\"float:right;padding-right:8px;\">";
	  $t .= "&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Reset Events..\" {$mouse}>";
      $t .= _emit_image("ResetThis.jpg",16)."</span>";
	  $t .= "</div>";
      }
   $t .= "<div style=\"width:392px;height:60px;overflow-x:hidden;overflow-y:hidden;display:inline;\" id=\"onSelectDetails\" name=\"onSelectDetails\"  >";
   $t .= PRO_ReadParam('onSelectDetails');
   $t .= "</div>";
   $mouse = _click_dialog("_dialogCheckRules","-'+getValue('onSelectId')");
   $t .= "<div style=\"float:right;padding-right:8px;\">";
   $t .= "&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Check Rules..\" {$mouse}>";
   $t .= _emit_image("QuestionThis.jpg",16)."</span>";
   $t .= "</div>";
   $t .= "<br>";
   $t .= "<textarea style=\"width:380px;height:40px;\" id=\"onSelectNotes\" name=\"onSelectNotes\"  >".PRO_ReadParam('onSelectNotes')."</textarea><br>";
   $t .= "<div style=\"color:black;\" >";
   $checked = (PRO_ReadParam('onSelectEnabled'))?"CHECKED":"";
   PRO_Clear('onSelectEnabled');
   $t .= "Mark as Rules Enabled: <input type=\"checkbox\" id=\"onSelectEnabled\" name=\"onSelectEnabled\" value=\"1\" {$checked} {$enabled}  >";
   $checked = (PRO_ReadParam('onSelectProxy'))?"CHECKED":"";
   PRO_Clear('onSelectProxy');
   $t .= "&nbsp;"._emit_image("SeparatorBar.jpg",14)."&nbsp;";
   $t .= "Mark as Proxy: <input type=\"checkbox\" id=\"onSelectProxy\" name=\"onSelectProxy\" value=\"2\" {$checked} {$enabled} >";
   $t .= "&nbsp;"._emit_image("SeparatorBar.jpg",14)."&nbsp;";
   $t .= "<span id=\"onSelectCollect\" style=\"color:blue;\" title=\"\" ></span>";
   $t .= "<br>";
   $t .= "<div style=\"width:380px;height:90px;border:1px solid gray;padding:2px;\">";
   $t .= "Report as <span style=\"color:green;\">Ready &amp; Success</span> if last run success within:<br>";
   $rule = PRO_ReadParam('onSelectPassed');
   if (is_null($rule)) $rule = 'T';
   $checked = ($rule=='M')?"CHECKED":"";
   $t .= "This Month:<input type=\"radio\" id=\"onSelectPassedM\" name=\"onSelectPassed\" value=\"M\" {$checked}  {$enabled} >";
   $checked = ($rule=='W')?"CHECKED":"";
   $t .= "&nbsp;"._emit_image("SeparatorBar.jpg",14)."&nbsp;";
   $t .= "&nbsp;This Week:<input type=\"radio\" id=\"onSelectPassedW\" name=\"onSelectPassed\"  value=\"W\" {$checked}  {$enabled} >";
   $checked = ($rule=='T')?"CHECKED":"";
   $t .= "&nbsp;"._emit_image("SeparatorBar.jpg",14)."&nbsp;";
   $t .= "&nbsp;Today:<input type=\"radio\" id=\"onSelectPassedT\" name=\"onSelectPassed\"  value=\"T\" {$checked}  {$enabled} >";
   $checked = ($rule=='Y')?"CHECKED":"";
   $t .= "&nbsp;"._emit_image("SeparatorBar.jpg",14)."&nbsp;";
   $t .= "&nbsp;Yesterday:<input type=\"radio\" id=\"onSelectPassedY\" name=\"onSelectPassed\"  value=\"Y\" {$checked}  {$enabled} ><br>";
   $t .= "or Days <input type=\"text\" id=\"onSelectPassedAgo\"  name=\"onSelectPassedAgo\" value=\"1\" style=\"width:30px;text-align:right;\" {$readonly} >";
   $checked = ($rule=='D')?"CHECKED":"";
   $t .= "&nbsp;ago:<input type=\"radio\" id=\"onSelectPassedD\" value=\"D\" name=\"onSelectPassed\"  {$checked}  {$enabled} >";
   $checked = ($rule=='P')?"CHECKED":"";
   $t .= "&nbsp;"._emit_image("SeparatorBar.jpg",14)."&nbsp;";
   $t .= "&nbsp;or Anytime:<input type=\"radio\" id=\"onSelectPassedP\" name=\"onSelectPassed\"  value=\"P\" {$checked} {$enabled}  ><br>";
   $t .= "Expect data on days:";
   PRO_Clear('onSelectDataDay');
   foreach(array(1=>'Mo',2=>'Tu',3=>'We',4=>'Th',5=>'Fr',6=>'Sa',7=>'Su') as $nd=>$sd) 
      $t .= "&nbsp;{$sd}<input type=\"checkbox\" id=\"onSelectDataDay{$nd}\" name=\"onSelectDataDay[]\" value=\"{$nd}\" {$enabled}>";
   $t .= "</div>";
   $t .= "<div style=\"width:380px;height:22px;border:1px solid gray;padding:2px;\">";
   $t .= "Retry on <span style=\"color:red;\">Failure</span>: ";
   $fails = PRO_ReadParam('onSelectFail');
   if (is_null($fails)) $fails = 'N';
   $checked = ($fails=='N')?"CHECKED":"";
   $t .= "&nbsp;Never: <input type=\"radio\" id=\"onSelectFailN\" name=\"onSelectFail\"  value=\"N\" {$checked}  {$enabled} >";
   $t .= "&nbsp;"._emit_image("SeparatorBar.jpg",14)."&nbsp;";
   $t .= "&nbsp;or in <input type=\"text\" id=\"onSelectFailAgo\"  name=\"onSelectFailAgo\" value=\"1\" style=\"width:30px;text-align:right;\"  {$readonly} >";
   $checked = ($fails=='H')?"CHECKED":"";
   $t .= "&nbsp;hours:<input type=\"radio\" id=\"onSelectFailH\" value=\"H\" name=\"onSelectFail\"  {$checked}  {$enabled} >";
   $t .= "&nbsp;max <input type=\"text\" id=\"onSelectFailRetry\"  name=\"onSelectFailRetry\" value=\"3\" style=\"width:30px;text-align:right;\"  {$readonly} > retries";
   $t .= "</div>";
   $t .= "<div style=\"width:380px;height:22px;border:1px solid gray;padding:2px;\">";
   $t .= "Default <span style=\"color:blue;\">Pass</span>: ";
   $defaultPass = PRO_ReadParam('onSelectDefault');
   if (is_null($defaultPass)) $defaultPass = 'N';
   $checked = ($defaultPass=='N')?"CHECKED":"";
   $t .= "&nbsp;Never: <input type=\"radio\" id=\"onSelectDefaultN\" name=\"onSelectDefault\"  value=\"N\" {$checked}  {$enabled} >";
   $t .= "&nbsp;"._emit_image("SeparatorBar.jpg",14)."&nbsp;";
   $t .= "&nbsp;or <input type=\"radio\" id=\"onSelectDefaultH\" value=\"H\" name=\"onSelectDefault\"  {$checked}  {$enabled} >";
   $checked = ($defaultPass=='H')?"CHECKED":"";
   $t .= "&nbsp;after <input type=\"text\" id=\"onSelectDefaultDays\"  name=\"onSelectDefaultDays\" value=\"0\" style=\"width:20px;text-align:right;\" {$readonly} >";
   $t .= "&nbsp;days or today after:";
   $t .= "&nbsp;<input type=\"text\" id=\"onSelectDefaultTod\"  name=\"onSelectDefaultTod\" value=\"12:00\" style=\"width:34px;text-align:right;\" {$readonly} >";
   $t .= "</div>";
   $t .= "</div>";
   if ($DevUser) {
      $mouse = "onclick=\"saveTreeState();issuePost('TreeView_Save',event); return false;\" ";
      $t .= "<center><span title=\"Save\" {$mouse} class=\"click-here\"  >";
      $t .= _emit_image("Save.jpg",18)."</span>";
      $t .= "&nbsp;<span style=\"color:blue;\">".PRO_ReadParam('onUpdateStatus')."</span></center>";
	  }
   $t .= "</div>";
   $t .= "</div>";
   $t .= "</td></tr>";
   $t .= "</table>";
   $t .= "</div>";
   $t .= "</div>";
   $t .= "</td>";
   $t .= "</tr>";
   $t .= "</table>";
   $on = ($DevUser)?'1':'0';
   $post_load .= "runTreeView({$on}, '{$filter}','{$UserCode}');";
   return $t;
   }
   
function _dialogExportTree($dialog_id='alc_export_structure') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;

   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
	     break;
      }
   $t = _makedialoghead($dialog_id, "Export Structure");
   $lib_path = "tmp/trees";
   if (!@file_exists($lib_path)) @mkdir($lib_path);
   $lib_path .= "/structure.xml";
   $t .= "<tr><td><textarea style=\"width:100%;height:160px;overflow-x:hidden;overflow-y:auto;\" >";
   $xml = hda_db::hdadb()->HDA_DB_getRelationTable();
   file_put_contents($lib_path, $xml);
   $t .= $xml;
   $t .= "</textarea></td></tr>";

   if (!is_null($lib_path)) $t .= "<tr><th>"._insertDownloadFileDiv($dialog_id, $lib_path)."</th></tr>";
   $t .= "<tr><th>";
   $t .= _closeDialog($dialog_id);
   $t .= "</th></tr>";
   $t .= _makedialogclose();

   return $t;
   }
function _dialogImportTree($dialog_id='alc_import_structure') {
   global $Action;
   global $_Mobile;
   global $code_root, $home_root;
   $problem = null;
   $a = null;
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}":
      case "ACTION_{$dialog_id}_Save":
         break;
      case "ACTION_{$dialog_id}_ImportedFile";
         $import = _actionImportFileDiv($dialog_id, $problem);
         if (!is_null($import)) {
            if ($import['Extension']=='xml') {
               $s = file_get_contents($import['UploadedPath']);
			   $xml = new xmlToArrayParser($s);
               $a = _treeToArray($xml->array);
               }
            else $problem = "Can only upload a structure xml file";
            }
         else $problem = "No data fetched - {$problem}";
         break;
      }
   $t = _makedialoghead($dialog_id, "Import Structure");
   $upf_code = null;
   if (!is_null($problem)) $t .= "<tr><th style=\"color:red;\" colspan=2 >{$problem}</th></tr>";
   if (is_null($a)) {
      $t .= "<tr><th>"._insertImportFileDiv($dialog_id, "tree", "UploadStructure")."</th></tr>"; 
      }
   else {
      $t .= "<tr><td><textarea style=\"width:100%;height:160px;overflow-x:hidden;overflow-y:auto;\">";
	  $t .= $a;
	  $t .= "</textarea></td></tr>";
      }
   $t .= "<tr><th>";
   $t .= _closeDialog($dialog_id);
   $t .= "</th></tr>";
   $t .= _makedialogclose();

   return $t;
   }   
   

function _treeToArray($a) {
   $s = "";
   if (array_key_exists("tree", $a)) {
      $fails = $wins = 0;
      $a = $a['tree']; 
	  $a = $a['item'];
	  foreach ($a as $row) {
	     $aa = array();
	     $aa['ItemId'] = hda_db::hdadb()->HDA_DB_lookUpProfile($row['name']);
		 $aa['ParentId'] = (array_key_exists('parent',$row))?hda_db::hdadb()->HDA_DB_lookUpProfile($row['parent']):'X';
		 if (!is_null($aa['ItemId']) && !is_null($aa['ParentId'])) {
		    $aa['ItemText'] = (!is_array($row['note']))?urldecode($row['note']):"";
			$aa['Enabled'] = $row['enabled'];
			$aa['Rule'] = (!is_array($row['rule']))?$row['rule']:'T';
			$aa['OnFail'] = (!is_array($row['fail']))?$row['fail']:'N';
			$aa['OnDefault'] = (!is_array($row['default']))?$row['default']:'N';
			$aa['DataDays'] = (!is_array($row['datadays']))?$row['datadays']:0x13f;
			if (hda_db::hdadb()->HDA_DB_putRelationTable($aa)) $wins++; else $fails++;
		    }
		 else {
		    $s .= print_r($row,true);
			$s .= print_r($aa, true);
		    $fails++;
			}
	     }
	  $s .= "\n{$wins} updates, {$fails} failed updates";
      }
   return $s;
   }




    
?>