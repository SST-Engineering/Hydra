<?php
/*
include_once "../{$code_root}/HDA_Graphics.php";
include_once "../{$code_root}/HDA_GridView.php";
include_once "../{$code_root}/HDA_TreeView.php";
include_once "../{$code_root}/HDA_DiaryView.php";
*/

function HDA_PlanView() {
   global $Action;
   global $ActionLine;
   global $UserCode;
   global $DevUser;
   global $code_root, $home_root;
   global $_ViewHeight;
   global $_ViewWidth;
   global $Tab_Menu;
   
   switch ($Action) {
      case "ACTION_SwitchPlanView":
	     list($action, $on_plan) = explode('-',$ActionLine);
		 PRO_AddToParams("PlanView-tab",$on_plan);
		 break;
	  case "ACTION_alc_category_filter_Clear":
	     PRO_Clear('alc_category_filter_On');
		 break;
      }
	  
   $on_plan = PRO_ReadParam("PlanView-tab");
   if (!isset($on_plan) || is_null($on_plan)) $on_plan = "LOGGING";

   $t = "";
   $Tab_Menu = "";
   if ($on_plan == 'LOGGING') {
      $mouse = _click_dialog("_dialogAddLog");
      $Tab_Menu .= "<span style=\"cursor:pointer;height:16px;\" title=\"Add a new Log Entry..\" {$mouse}>";
      $Tab_Menu .= _emit_image('AddThis.jpg',20);
	  $Tab_Menu .= "</span>";
      $mouse = _click_dialog("_dialogFilterLog");
      $Tab_Menu .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Filter Log..\" {$mouse}>";
      $Tab_Menu .= _emit_image("FilterThis.jpg", 20);
	  $Tab_Menu .= "</span>";

		$limit = PRO_ReadParam("limit_monlog");
		$from = PRO_ReadParam("from_monlog");
		if (is_null($limit)) $limit = 20;
		if (is_null($from)) $from=0;
		switch ($Action) {
		  case 'ACTION_LOGGING_ENTRIES':
			 list($action, $dir, $limit) = explode('-',$ActionLine);
			 switch ($dir) {
				 case "START": $from = 0;
					break;
				 case "BACK": $from = $from - $limit;
					if ($from<0) $from = 0;
					break;
				 case "FORWARD":
					$from = $from + $limit;
					break;
				 case "LAST":
					break;
				}
			 break;
		  }
		PRO_AddToParams("limit_monlog",$limit);
		PRO_AddToParams("from_monlog",$from);
		if ($from>0) {
		   $mouse = "onclick=\"issuePost('LOGGING_ENTRIES-START-20---',event); return false;\" ";
			$Tab_Menu .= _emit_image("ar_left_abs.gif", 18, $mouse);
			$mouse = "onclick=\"issuePost('LOGGING_ENTRIES-BACK-20---',event); return false;\" ";
			$Tab_Menu .= _emit_image("ar_left.gif", 18, $mouse);
			}
		$Tab_Menu .= "<span class=\"click-here\" style=\"color:blue;\" > [ {$limit} rows from {$from} ] </span>";
		$mouse = "onclick=\"issuePost('LOGGING_ENTRIES-FORWARD-20---',event); return false;\" ";
		$Tab_Menu .= _emit_image("ar_right.gif",18,$mouse);

      $mouse = "onclick=\"issuePost('RefreshLogger',event);return false;\"  ";
      $Tab_Menu .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Look Again..\" {$mouse}>";
      $Tab_Menu .= _emit_image("RefreshThis.jpg", 20);
	  $Tab_Menu .= "</span>";
	  $filter = PRO_ReadParam('_dialogFilterLog_SetSources');
	  global $Logging_Sources;
      if (is_array($filter)&&count($filter)<>count($Logging_Sources)) $t.= "&nbsp;<span style=\"color:red;\" >Filtering on Sources</span>";
      $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";
      }
   else {
      $mouse = "onclick=\"issuePost('SwitchPlanView-LOGGING--',event); return false;\" ";
      $Tab_Menu .= "&nbsp;<span class=\"click-here\" title=\"Show Log..\" {$mouse}>";
      $Tab_Menu .= _emit_image("Logging.jpg", 24);
      $Tab_Menu .= "</span>";
      }
   $mouse = "onclick=\"issuePost('SwitchPlanView-SCHEDULE--',event); return false;\" ";
   $Tab_Menu .= "&nbsp;<span class=\"click-here\" title=\"Show Schedule..\" {$mouse}>";
   $Tab_Menu .= _emit_image("Frequency.jpg",24);
   $Tab_Menu .= "</span>";
   $mouse = "onclick=\"issuePost('SwitchPlanView-Q---',event); return false;\" ";
   $Tab_Menu .= "&nbsp;&nbsp;<span class=\"click-here\" title=\"Show Qs..\" {$mouse}>";
   $Tab_Menu .= _emit_image("BackgroundQ.jpg",24);
   $Tab_Menu .= "</span>";
   $mouse = "onclick=\"issuePost('SwitchPlanView-TIME---',event); return false;\" ";
   $Tab_Menu .= "&nbsp;&nbsp;<span class=\"click-here\" title=\"Show Timings..\" {$mouse}>";
   $Tab_Menu .= _emit_image("Timings.jpg",24);
   $Tab_Menu .= "</span>";
   $mouse = "onclick=\"issuePost('SwitchPlanView-STATS---',event); return false;\" ";
   $Tab_Menu .= "&nbsp;&nbsp;<span class=\"click-here\" title=\"Show Q use..\" {$mouse}>";
   $Tab_Menu .= _emit_image("ReportStats.jpg",24);
   $Tab_Menu .= "</span>";
   $mouse = "onclick=\"issuePost('SwitchPlanView-EVENTS---',event); return false;\" ";
   $Tab_Menu .= "&nbsp;&nbsp;<span class=\"click-here\" title=\"Show Events..\" {$mouse}>";
   $Tab_Menu .= _emit_image("Event.jpg",24);
   $Tab_Menu .= "</span>";

   $mouse = _click_dialog("_dialogDailyLog");
   $Tab_Menu .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Last Daily Report..\" {$mouse}>";
   $Tab_Menu .= _emit_image("DailyReport.jpg",24);
   $Tab_Menu .= "</span>";

   $mouse = "onclick=\"issuePost('SwitchPlanView-TICKETS---',event); return false;\" ";
   $Tab_Menu .= "&nbsp;&nbsp;<span class=\"click-here\" title=\"Show Ticket Use..\" {$mouse}>";
   $Tab_Menu .= _emit_image("Trust.jpg",24);
   $Tab_Menu .= "</span>";

   if ($on_plan=='COLLECT') {
      $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";
      $key_press = "onKeyPress=\"return keyPressRun(openGridItem,event);\" onSelectStart=\"return true;\" ";
	  $Tab_Menu .= "&nbsp;&nbsp;<div style=\"display:inline-block;position:relative;top:-10px;\">";
      $Tab_Menu .= "&nbsp;&nbsp;Find:<input type=\"text\" {$key_press} name=\"onGridSearch\" id=\"onGridSearch\" size=56 style=\"width:200px;\">";
	  $Tab_Menu .= "</div>";
      $mouse = "onclick=\"openGridItem();return false;\"  ";
      $Tab_Menu .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Find..\" {$mouse}>";
      $Tab_Menu .= _emit_image("Search.jpg",16)."</span>";
      $mouse = "onclick=\"nextGridItem();return false;\"  ";
      $Tab_Menu .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Next..\" {$mouse}>";
      $Tab_Menu .= _emit_image("Selected.jpg",16)."</span>";
      $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";
      }
   else {
      $mouse = "onclick=\"issuePost('SwitchPlanView-COLLECT---',event); return false;\" ";
      $Tab_Menu .= "&nbsp;&nbsp;<span class=\"click-here\" title=\"Auto Collects..\" {$mouse}>";
      $Tab_Menu .= _emit_image("AutoCollect.jpg",24);
      $Tab_Menu .= "</span>";
	  }

   $mouse = "onclick=\"issuePost('SwitchPlanView-METATAGS---',event); return false;\" ";
   $Tab_Menu .= "&nbsp;&nbsp;<span class=\"click-here\" title=\"Look for metatags..\" {$mouse}>";
   $Tab_Menu .= _emit_image("TagThis.jpg",24);
   $Tab_Menu .= "</span>";

   $mouse = "onclick=\"issuePost('SwitchPlanView-AUDIT---',event); return false;\" ";
   $Tab_Menu .= "&nbsp;&nbsp;<span class=\"click-here\" title=\"Profile audit log..\" {$mouse}>";
   $Tab_Menu .= _emit_image("AuditThis.jpg",24);
   $Tab_Menu .= "</span>";
   
   if ($on_plan=='TREE') {
      $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";
	  $key_press = "onKeyPress=\"return keyPressRun(openTreeItem,event);\" onSelectStart=\"return true;\" ";
	  $Tab_Menu .= "&nbsp;&nbsp;<div style=\"display:inline-block;position:relative;top:-10px;\">";
      $Tab_Menu .= "Find:";
	  $Tab_Menu .= "<input type=\"text\" {$key_press} name=\"onTreeSearch\" id=\"onTreeSearch\" size=56 style=\"width:200px;\">";
	  $Tab_Menu .= "</div>";
      $mouse = "onclick=\"openTreeItem();return false;\"  ";
      $Tab_Menu .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Find..\" {$mouse}>";
      $Tab_Menu .= _emit_image("Search.jpg",16)."</span>";
	  $mouse = "onclick=\"nextTreeItem();return false;\"  ";
      $Tab_Menu .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Next..\" {$mouse}>";
      $Tab_Menu .= _emit_image("Selected.jpg",16)."</span>";
	  $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";
	  if ($DevUser) {
         $mouse = _click_dialog("_dialogExportTree");
         $Tab_Menu .= "&nbsp;&nbsp;<span  title=\"Export structure..\"  {$mouse}>";
         $Tab_Menu .= _emit_image("Export.jpg",24);
         $Tab_Menu .= "</span>";
         $mouse = _click_dialog("_dialogImportTree");
         $Tab_Menu .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Import structure..\" {$mouse}>";
         $Tab_Menu .= _emit_image("ImportThis.jpg",24)."</span>";
         }
      $mouse = "onclick=\"expandAllTree();return false;\"  ";
      $Tab_Menu .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Expand all..\" {$mouse}>";
      $Tab_Menu .= _emit_image("ExpandAll.jpg",25)."</span>";
      $mouse = "onclick=\"collapseAllTree();return false;\"  ";
      $Tab_Menu .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Collapse all..\" {$mouse}>";
      $Tab_Menu .= _emit_image("CollapseAll.jpg",25)."</span>";
      $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";
      }
   else {
      $mouse = "onclick=\"issuePost('SwitchPlanView-TREE---',event); return false;\" ";
      $Tab_Menu .= "&nbsp;&nbsp;<span class=\"click-here\" title=\"Relationship Manager..\" {$mouse}>";
      $Tab_Menu .= _emit_image("PlanTree.jpg",24);
      $Tab_Menu .= "</span>";
	  }

   $mouse = "onclick=\"issuePost('SwitchPlanView-INDEX---',event); return false;\" ";
   $Tab_Menu .= "&nbsp;&nbsp;<span class=\"click-here\" title=\"Profile Status..\" {$mouse}>";
   $Tab_Menu .= _emit_image("ProfileIndex.jpg",24);
   $Tab_Menu .= "</span>";
   
   $mouse = "onclick=\"issuePost('SwitchPlanView-CRON');\" return false;\" ";
   $Tab_Menu .= "&nbsp;&nbsp;<span title=\"Cron Log..\" {$mouse} >";
   $Tab_Menu .= _emit_image("Cron.jpg",24);
   $Tab_Menu .= "</span>";

   $mouse = "onclick=\"issuePost('SwitchPlanView-APPLOG');\" return false;\" ";
   $Tab_Menu .= "&nbsp;&nbsp;<span title=\"Application Log..\" {$mouse} >";
   $Tab_Menu .= _emit_image("ApplicationLog.jpg",24);
   $Tab_Menu .= "</span>";
   
   $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";
   
   $mouse = _click_dialog("_dialogFilterCategory");
   $Tab_Menu .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Filter on Category..\" {$mouse}>";
   $Tab_Menu .= _emit_image("FilterThis.jpg",18)."</span>";
   $filter = PRO_ReadParam('alc_category_filter_On');
   if (is_null($filter) || strlen($filter)==0) $filter = null;
   if (!is_null($filter)) {
      $mouse = "onclick=\"issuePost('alc_category_filter_Clear',event); return false;\" ";
      $Tab_Menu .= "&nbsp;<span class=\"click-here\" title=\"Clear Filter\" style=\"color:blue;\" {$mouse}>[ Showing only category {$filter} profiles ]</span>";
	  }
		 
   $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";
   
   if ($on_plan=='TREE') {
      $mouse = "onclick=\"saveTreeState();refreshTreeView(); return false;\" ";
      }
   else {
      $mouse = "onclick=\"issuePost('gotoTab-PL---',event); return false;\" ";
	  }
   $Tab_Menu .= "&nbsp;&nbsp;<span class=\"click-here\" title=\"Refresh..\" {$mouse}>";
   $Tab_Menu .= _emit_image("RefreshThis.jpg",20);
   $Tab_Menu .= "</span>";
   
   
   switch ($on_plan) {
      default:
	  case "LOGGING":
         $t .= "<tr><th>";
		 $t .= _monitorLog($from, $limit);
         $t .= "</th></tr>";
		 break;
	  case "APPS":
		 $t .= HDA_AppBuilder();
		 break;
      case "SCHEDULE":
         $on_schedule = hda_db::hdadb()->HDA_DB_getSchedule();
         $t .= "<tr><th><div style=\"width:100%;height:380px;overflow-y:auto;overflow-x:hidden;\"><table class=\"alc-table\">";
		 $t .= "<colgroup>";
		 $t .= "<col style=\"width:200px;\"><col style=\"width:18px;\"><col style=\"width:18px;\"><col style=\"width:18px;\">";
		 $t .= "<col style=\"width:200px;\"><col style=\"width:200px;\">";
		 $t .= "</colgroup>";
         $t .= "<tr><th colspan=2>Process</th><th>Q</th><th>&nbsp;</th><th>Next Scheduled</th><th>Schedule Request</th></tr>";
         foreach($on_schedule as $row) {
			$q = $row['Q'];
            $t .= "<tr>";
            $t .= "<td>{$row['Title']}</td>";
	        if ($DevUser) {
	           $goto_mouse = "onclick=\"issuePost('gotoTab-LD-{$row['ItemId']}---',event); return false;\" ";
		       $mouse_img = "GoForward.jpg";
			   $t .= "<td><span class=\"click-here\" title=\"Open Profile\" {$goto_mouse}>";
			   $t .= _emit_image($mouse_img, 16);
               $t .= "</span></td>";
		       }
			else $t .= "<td>&nbsp;</td>";
            $t .= "<td>{$q}</td>";
		    $goto_mouse = _click_dialog("_dialogProfileIndexItem","-{$row['ItemId']}");
		    $mouse_img = "CODEHELP.gif";
			$t .= "<td><span class=\"click-here\" title=\"Open Profile\" {$goto_mouse}>";
			$t .= _emit_image($mouse_img,16);
            $t .= "</span></td>";
            $t .= "<td>";
            $t .= hda_db::hdadb()->PRO_DBdate_Styledate($row['Scheduled'],true);
            $t .= "</td>";
            $t .= "<td>Every {$row['Units']} {$row['RepeatInterval']} </td>";
            $t .= "</tr>";
            }
         $t .= "</table></div></th></tr>";
         break;
	  case "Q":
	     $t .= "<tr><th>";
		 $t .= _showProcessQs();
		 $t .= "</th></tr>";
		 break;
	  case "QQ":
	     switch ($Action) {
		    case "ACTION_PlanRemovePending":
			   list($action, $item, $profile_item) = explode('-',$ActionLine);
               hda_db::hdadb()->HDA_DB_RemovePending($item);
               $note = "Cleared entry from pending Q for this profile";
               hda_db::hdadb()->HDA_DB_issueNote($profile_item, $note, 'TAG_COMPLETE');
               HDA_LogThis(hda_db::hdadb()->HDA_DB_TitleOf($profile_item)." {$note}");
			   break;
		    }
         $t .= "<tr><th>Pending Scheduled Jobs</th></tr>";
         $t .= "<tr><td><div style=\"height:360px;overflow-y:scroll;overflow-x:hidden;\" ><table class=\"alc-table\" > ";
         $a = hda_db::hdadb()->HDA_DB_pendingQ();
         $t .= "<tr><th>Source</th><th>State</th><th>Profile</th><th>Queue Type</th><th>Owner</th><th>&nbsp;</th><th>Issued</th><th>Source Info</th><th>File to Process</th></tr>";
         if (is_array($a)) foreach ($a as $row) {
            $t .= "<tr> ";
            $icon = _iconForSource($row['Source'], $caption);
            $t .= "<td><span  title=\"{$caption}..\" >";
            $t .= _emit_image($icon,24);
            $t .= "</span></td>";
	        switch ($row['ProcessState']) {
	           default: $icon = "TAG_WAITING.jpg"; break;
		       case 'COMPLETED': $icon = "TAG_FINISHED.jpg"; break;
		       case 'RUNNING': $icon = "TAG_PROGRESS.jpg"; break;
		       }
            $t .= "<td><span  title=\"{$row['ProcessState']}\" >";
            $t .= _emit_image($icon,24);
            $t .= "</span></td>";
            $process_title = hda_db::hdadb()->HDA_DB_TitleOf($row['ProcessItem']);
            $t .= "<td>{$process_title}</td>";
	        $t .= "<td>".((is_null($row['QueueLevel']) || $row['QueueLevel']==0)?"Normal":"Low Priority - {$row['QueueLevel']}")."</td>";
            $t .= "<td>".hda_db::hdadb()->HDA_DB_GetUserFullName($row['OwnerId'])."</td>";
			$t .= "<td>&nbsp;";
            if ($DevUser) {
               $mouse = "onclick=\"issuePost('PlanRemovePending-{$row['ItemId']}-{$row['ProcessItem']}--',event); return false;\" ";
               $t .= "<span title=\"Remove from Q..\" {$mouse} class=\"click-here\">";
               $t .= _emit_image("DeleteThis.jpg",16)."</span><br>";
               }
			$t .= "</td>";
            $t .= "<td>".hda_db::hdadb()->PRO_DBdate_Styledate($row['IssuedDate'],true)."</td>";
            $t .= "<td>{$row['SourceInfo']}</td>";
			$task = hda_db::hdadb()->HDA_DB_ReadTask($row['ItemId']);
			if (!is_null($task)) $t .= "<td>{$task['RcvFileName']}</td>";
            $t .= "</tr>";
            }
         if (is_null($a) || count($a)==0) $t .= "<tr><th colspan=9>No pending jobs</th></tr>";
         $t .= "</table></div></td></tr>";
	     break;
      case "TIME":
	     $time_view = PRO_ReadParam('PlanTimeView');
		 if (is_null($time_view)) $time_view = 'TABLE';
	     switch ($Action) {
		    case 'ACTION_PlanClearTimes': hda_db::hdadb()->HDA_DB_clearTimings(); break;
			case 'ACTION_PlanTimeView': list($action,$time_view) = explode('-',$ActionLine); PRO_AddToParams('PlanTimeView',$time_view); break;
			}
	     $t .= "<tr><td>";
		 if ($DevUser) {
	        $mouse = "onclick=\"issuePost('PlanClearTimes',event); return false;\" ";
	        $t .= "<input type=\"submit\" value=\"Clear Timings\" {$mouse}>&nbsp;&nbsp;";
			$t .= "<span class=\"push_button blue\" {$mouse}   >Clear Timings</span>&nbsp;&nbsp;"; 
			}
		 if ($time_view=='TABLE') {
		    $mouse = "onclick=\"issuePost('PlanTimeView-TREE',event); return false;\" ";
			$t .= "<span class=\"push_button blue\" {$mouse}   >Show Distribution</span>"; 
			}
		 else {
		    $mouse = "onclick=\"issuePost('PlanTimeView-TABLE',event); return false;\" ";
			$t .= "<span class=\"push_button blue\" {$mouse}   >Show Range</span>"; 
		    }
		 $t .= "</td></tr>";
         $t .= "<tr><td><div style=\"height:".($_ViewHeight-84)."px;overflow-y:scroll;overflow-x:hidden;\" ><table class=\"alc-table\" > ";
		 switch ($time_view) {
		    case 'TABLE':
		       $t .= "<tr><th>Profile</th><th>Q</th><th>Longest Elapsed</th><th>Run Count</th><th>Most Records Added</th><?tr>";
	           $a = hda_db::hdadb()->HDA_DB_timings();
		       if (is_array($a)) foreach ($a as $row) {
			      $h_time = _style_secs_time($row['HighTime']);
		          $t .= "<tr><td>{$row['Title']}</td>";
			      $t .= "<td>{$row['Q']}</td>";
			      $t .= "<td>{$h_time}</td><td>{$row['RunCount']}</td><td>{$row['HighRecordCount']}</td></tr>";
		          }
		       else $t .= "<tr><th>No timing data</th></tr>";
			   break;
			case 'TREE':
			   $t .= "<colgroup>";
			   $t .= "<col style=\"width:200px;\"/>";
			   $t .= "<col style=\"width:20px;max-width:20px;\" />";
			   $t .= "<col style=\"width:100px;\" />";
			   $t .= "<col style=\"width:200px;\" />";
			   $t .= "<col style=\"width:18px;max-width:18px;\" />";
			   $t .= "<col  />";
			   $t .= "</colgroup>";
			   $a = hda_db::hdadb()->HDA_DB_getRelations();
			   foreach ($a as $row) $t .= _planProfileTree($row, 0);
			   break;
			}
         $t .= "</table></div></td></tr>";
	     break;
	  case "STATS":
	     switch ($Action) {
		    case 'ACTION_PlanClearTimes': hda_db::hdadb()->HDA_DB_clearTimings(); break;
			}
		 if ($DevUser) {
	        $mouse = "onclick=\"issuePost('PlanClearTimes',event); return false;\" ";
	        $t .= "<tr><td>";
			$t .= "<span class=\"push_button blue\" {$mouse}   >Clear Timings</span>"; 
			$t .= "</td></tr>";
			}
         $t .= "<tr><td><table class=\"alc-table\" >";
		 $t .= "<tr><th>Q</th><th>Longest Elapsed</th><th>Total Elapsed</th><th>Total Profiles</th><?tr>";
	     $a = hda_db::hdadb()->HDA_DB_timings();
		 $qa = array();
		 for ($i=0; $i<11;$i++) $qs[$i] = array('MaxHigh'=>0,'TotalHigh'=>0,'ProfileCount'=>0);
		 if (is_array($a)) foreach ($a as $row) {
		    $qn = $row['Q'];
			if (is_null($qn) || $qn=='Normal') $qn = 0;
            $q = $qs[$qn];
			$h_time = $row['HighTime'];
			$q['MaxHigh'] = max($h_time,$q['MaxHigh']);
			$q['TotalHigh'] += $h_time;
			$q['ProfileCount']++;
			$qs[$qn] = $q;
		    }
		 for ($i=0; $i<11; $i++) {
		    $t .= "<tr>";
			$qn = ($i==0)?'Normal':$i;
			$q = $qs[$i];
			$t .= "<td>{$qn}</td>";
			$t .= "<td>"._style_secs_time($q['MaxHigh'])."</td>";
			$t .= "<td>"._style_secs_time($q['TotalHigh'])."</td>";
			$t .= "<td>{$q['ProfileCount']}</td>";
			$t .= "</tr>";
		    }
         $t .= "</table></td>";
		 $t .= "<td><div style=\"width:380px;height:380px;\">";
		 $_data = array();
		 for ($i=0; $i<11; $i++) {
		    $q = ($i==0)?'Normal':$i;
			$_data[$i]['KEY'] = $q;
		    $_data[$i]['VALUE'] = (isset($qs[$i]))?$qs[$i]['TotalHigh']:0;
			}
		 $f =  HDA_Graph($UserCode, "QUSE", "RT_BAR", $_data, $gsize=380);
		 $t .= "<img src=\"{$f}\" height=380px; >";
		 $t .= "</div></td></tr>";
	     break;
	  case 'EVENTS':
	     $t .= "<tr><td>";
		 $ev_today = PRO_ReadParam("Plan_EV_Today");
		 if (is_null($ev_today)) $ev_today = 'TODAY';
		 $checked = ($ev_today=='TODAY')?"CHECKED":"";
         $mouse = "onclick=\"issuePost('SwitchPlanView-EVENTS---',event); return false;\" ";
		 $t .= "Today only&nbsp;<input type=\"radio\" name=\"Plan_EV_Today\" value=\"TODAY\" {$checked} {$mouse}>";
         $t .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";

		 $checked = ($ev_today=='YESTERDAY')?"CHECKED":"";
         $mouse = "onclick=\"issuePost('SwitchPlanView-EVENTS---',event); return false;\" ";
		 $t .= "Yesterday&nbsp;<input type=\"radio\" name=\"Plan_EV_Today\" value=\"YESTERDAY\" {$checked} {$mouse}>";
         $t .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";

		 $checked = ($ev_today=='THISWEEK')?"CHECKED":"";
         $mouse = "onclick=\"issuePost('SwitchPlanView-EVENTS---',event); return false;\" ";
		 $t .= "This Week&nbsp;<input type=\"radio\" name=\"Plan_EV_Today\" value=\"THISWEEK\" {$checked} {$mouse}>";
         $t .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";

		 $checked = ($ev_today=='THISMONTH')?"CHECKED":"";
         $mouse = "onclick=\"issuePost('SwitchPlanView-EVENTS---',event); return false;\" ";
		 $t .= "This Month&nbsp;<input type=\"radio\" name=\"Plan_EV_Today\" value=\"THISMONTH\" {$checked} {$mouse}>";
         $t .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";

		 $checked = ($ev_today=='DAYSAGO')?"CHECKED":"";
		 $ago = PRO_ReadParam("Plan_EV_Ago");
		 if (is_null($ago)) $ago = 5;
         $mouse = "onchange=\"issuePost('SwitchPlanView-EVENTS---',event); return false;\" ";
		 $t .= "Days&nbsp;<input type=\"text\" name=\"Plan_EV_Ago\" value=\"{$ago}\" {$mouse} style=\"width:20px;\">";
         $mouse = "onclick=\"issuePost('SwitchPlanView-EVENTS---',event); return false;\" ";
		 $t .= "&nbsp;Ago&nbsp;<input type=\"radio\" name=\"Plan_EV_Today\" value=\"DAYSAGO\" {$checked} {$mouse}>";
         $t .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";

		 $checked = ($ev_today=='ANY')?"CHECKED":"";
         $mouse = "onclick=\"issuePost('SwitchPlanView-EVENTS---',event); return false;\" ";
		 $t .= "&nbsp;or all dates <input type=\"radio\" name=\"Plan_EV_Today\" value=\"ANY\" {$checked} {$mouse}>";
         $t .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";

         $mouse = "onchange=\"issuePost('SwitchPlanView-EVENTS---',event); return false;\" ";
		 $t .= "&nbsp;<select name=\"Plan_EV_Profile\" {$mouse} >";
		 $profiles = hda_db::hdadb()->HDA_DB_profileNames();
		 $t .= "<option value=\"\" SELECTED>All Profiles</option>";
		 $ev_profile = PRO_ReadParam("Plan_EV_Profile");
		 if (strlen($ev_profile)==0) $ev_profile=null;
		 foreach($profiles as $item=>$item_name) {
		    $selected = ($ev_profile==$item)?"SELECTED":"";
			$t .= "<option value=\"{$item}\" {$selected}>{$item_name}</option>";
		    }
		 $t .= "</select>";
         $t .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";
		 $mouse = _click_dialog("_dialogEventSummary","-{$ev_today}-{$ago}");
		 $t .= "<span class=\"click-here\" style=\"color:blue;\" {$mouse} >[ Summary ]</span>";
		 $t .= "</td></tr>";
         $t .= "<tr><td><div style=\"height:".($_ViewHeight-96)."px;overflow-y:scroll;overflow-x:hidden;\" ><table class=\"alc-table\" > ";
	     $a = hda_db::hdadb()->HDA_DB_events($ev_profile, null, null, $ev_today, $ago);
		 if (is_array($a)) foreach($a as $row) {
	        if ($DevUser) {
	           $goto_mouse = "onclick=\"issuePost('gotoTab-LD-{$row['ItemId']}---',event); return false;\" ";
		       $mouse_img = "GoForward.jpg";
		       }
	        else {
		       $goto_mouse = _click_dialog("_dialogProfileIndexItem","-{$row['ItemId']}");
		       $mouse_img = "CODEHELP.gif";
		       }
		    $t .= "<tr><td>".hda_db::hdadb()->PRO_DBdate_Styledate($row['IssuedDate'],true)."</td>";
			$t .= "<td>{$row['Title']}</td>";
			$t .= "<td><span class=\"click-here\" title=\"Open Profile\" {$goto_mouse}>";
			$t .= _emit_image($mouse_img,16);
            $t .= "</span></td>";
			$t .= "<td>{$row['EventCode']}</td>";
			$t .= "<td>{$row['EventValue']}<td></tr>";
			}
		 else $t .= "<tr><td>No Events Found</td></tr>";
		 $t .= "</table></div></td></tr>";
	     break;
	  case 'TICKETS':
	     $t .= _ticket_manager();
	     break;
	  case 'COLLECT':
	     $xcollect = hda_db::hdadb()->HDA_DB_admin('ExternalCollect');
		 $t .= "<tr><td>";
		 if (!is_null($xcollect)) {
		    $xcollect = hda_db::hdadb()->HDA_DB_unserialize($xcollect);
			if ($xcollect['COLLECT']=='FTP') {
               $t .= "Will form ftp url as: ftp://{$xcollect['URL']}/{$xcollect['BASEDIR']}/";
	           }
            elseif ($xcollect['COLLECT']=='MAP') {
			   $t .= "Will collect from: {$xcollect['COLLECT_POINT']}/";
			   }
	        if ($xcollect['DATEDIR']==1) $t .= date('Ymd',time())."/";
	        $t .= "Location Specified/--files to upload--";
			$t .= "&nbsp;Details in: ";
		    }
		 else {
		    $t .= "The External Collection method has not been defined, ";
			}
		 if ($DevUser) {
		    $mouse = "onclick=\"issuePost('gotoTab-AD---',event); return false; \" ";
		    $t .= " <span class=\"more\" {$mouse}>[ see You Are Admin ]</span>";
			}
	     $t .= "</td></tr>";
		 $t .= "<tr><th>";
		 $t .= _planViewGrid($filter);
		 $t .= "</th></tr>";
	     break;
	  case 'METATAGS':
	     switch ($Action) {
		    case 'ACTION_METATAGS_Open':
			   list($action, $open_tag) = explode('-',$ActionLine);
			   $open_tag = base64_decode($open_tag);
			   PRO_AddToParams('Monitor_MetaTags_Open', $open_tag);
			   break;
			case 'ACTION_METATAGS_Close':
			   PRO_Clear('Monitor_MetaTags_Open');
			   break;
			}
	     $open_tag = PRO_ReadParam('Monitor_MetaTags_Open');
	     $a = HDA_ProfileTags($item=NULL, $metatags=NULL, $match = "", $all_tags);
		 if (is_array($all_tags) && count($all_tags)>0) {
		    sort($all_tags);
            $t .= "<tr><td><div style=\"height:".($_ViewHeight-64)."px;overflow-y:scroll;overflow-x:hidden;\" ><table class=\"alc-table\" > ";
			$t .= "<colgroup>";
			$t .= "<col style=\"width:200px;\">";
			$t .= "<col style=\"width:12px;\">";
			$t .= "<col style=\"width:200px;\">";
			$t .= "<col span=\"2\">";
			$t .= "<col style=\"width:12px;\">";
			$t .= "</colgroup>";
			foreach($all_tags as $tag) {
               $a = HDA_ProfileTags($item=NULL, $metatags=NULL, $match = $tag);
			   $a_count = count($a);
			   if ($open_tag == $tag) {
				  $row_count = $a_count+1;
			      $t .= "<tr>";
                  $t .= "<td style=\"background-color:lightgray;color:blue;\" rowspan={$row_count}>";
				  $t .= "<div style=\"display:inline;float:right;color:green;\">({$a_count})</div><div style=\"display:inline;float:left;\">{$tag}</div>";
				  $t .= "</td>";
				  $mouse = "onclick=\"issuePost('METATAGS_Close---',event); return false;\" ";
				  $t .= "<td rowspan={$row_count}><span class=\"click-here\" title=\"Hide profiles..\" {$mouse} >";
				  $t .= _emit_image("arrow_left.png",12);
				  $t .= "</span></td>";
                  $t .= "<td colspan=4 style=\"color:green;\" >{$a_count} profiles here ...</td>";
                  $t .= "</tr>";
				  foreach ($a as $item=>$p) {
                     $mouse = "onclick=\"issuePost('gotoTab-LD-{$item}---',event); return false;\" ";
				     $t .= "<tr><td><span class=\"click-here\" {$mouse}>{$p['Title']}</span></td>";
					 $t .= "<td colspan=2>".str_replace(';','; ',$p['Tags'])."</td>";
                     $t .= "<td><span style=\"cursor:pointer;height:16px;\" title=\"Select this..\" {$mouse}>";
                     $t .= _emit_image("GoForward.jpg",12)."</span></td>";
					 $t .= "</tr>";
				     }
			      }
			   else {
			      $t .= "<tr>";
				  $t .= "<td><div style=\"display:inline;float:right;color:green;\">({$a_count})</div><div style=\"display:inline;float:left;\">{$tag}</div></td>";
				  $mouse = "onclick=\"issuePost('METATAGS_Open-".base64_encode($tag)."---',event); return false;\" ";
				  $t .= "<td><span class=\"click-here\" title=\"Show profiles..\" {$mouse} >";
				  $t .= _emit_image("arrow_right.png",12)."</span></td>";
                  $t .= "<td style=\"border-right:none;\" >&nbsp;</td><td style=\"border-left:none;border-right:none;\">&nbsp;</td>";
				  $t .= "<td style=\"border-left:none;border-right:none;\">&nbsp;</td><td style=\"border-left:none;\">&nbsp;</td>";
				  $t .= "</tr>";
			      }
			   }
	   	    $t .= "</table></div></td></tr>";
		    }
		 else $t .= "<tr><td>No metatags found</td></tr>";
	     break;
 	  case 'AUDIT':
	     switch ($Action) {
		    case "ACTION_BacktoAudits": PRO_Clear('OnAudit'); 
			   break;
			case "ACTION_AUDIT_Open":
			   list($action, $on_audit) = explode('-',$ActionLine);
			   PRO_AddToParams('OnAudit', $on_audit);
			   break;
			case "ACTION_AUDIT_Refresh":
			   break;
			}
	     $on_audit = PRO_ReadParam('OnAudit');
		 if (!isset($on_audit) || is_null($on_audit)) $on_audit = null;
		 if (!is_null($on_audit)) {
		    $on_audit_profile = hda_db::hdadb()->HDA_DB_ReadProfile($on_audit);
			if (is_null($on_audit_profile) || !is_array($on_audit_profile)) $on_audit = null;
			}
		 $a = hda_db::hdadb()->HDA_DB_getAudit($on_audit, $limit=100);
		 if (is_null($on_audit)) {
            $t .= "<tr><td class=\"buttons\" >";
            $mouse = _click_dialog("_dialogAuditFileTime");
            $t .= "<span style=\"cursor:pointer;height:16px;\" title=\"Yesterday Timed Loads..\" {$mouse}>";
            $t .= _emit_image("clock_big.gif",18)."</span>";
			$t .= "</td></tr>";
		    $t .= "<tr><th><div style=\"width:100%;height:".($_ViewHeight-84)."px;overflow-x:hidden;overflow-y:scroll;\"><table class=\"alc-table\">";
			$t .= "<colgroup>";
			$t .= "<col style=\"width:200px;max-width:200px;\">";
			$t .= "<col style=\"width:12px;max-width:12px;\">";
			$t .= "<col style=\"max-width:100%;\">";
			$t .= "</colgroup>";
			foreach ($a as $row) {
			   $t .= "<tr><td>";
			   $mouse = "onclick=\"issuePost('AUDIT_Open-{$row['ItemId']}---',event); return false;\" ";
			   $t .= "<div style=\"display:inline;float:right;color:green;\">({$row['Audits']})</div>";
			   $t .= "<span class=\"click-here\" {$mouse}>{$row['Title']}</span></td>";
               $t .= "<td><span style=\"cursor:pointer;height:16px;\" title=\"Audit details..\" {$mouse}>";
               $t .= _emit_image("GoForward.jpg",12)."</span></td>";
			   $t .= "<td>&nbsp;</td>";
		       $t .= "</tr>";
			   }
		    $t .= "</table></div></th></tr>";
		    }
		 else {
            $t .= "<tr><td class=\"buttons\" >";
            $mouse = "onclick=\"issuePost('BacktoAudits---',event);return false; \" ";
            $t .= "<span style=\"cursor:pointer;height:16px;\" title=\"Back to Audit Index..\" {$mouse}>";
            $t .= _emit_image("GoBack.jpg",18)."</span>";
            $t .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";
            $mouse = "onclick=\"issuePost('AUDIT_Refresh---',event);return false;\"  ";
            $t .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Refresh..\" {$mouse}>";
            $t .= _emit_image("RefreshThis.jpg",16)."</span>";
            $t .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";
            $mouse = _click_dialog("_dialogAuditFileList","-{$on_audit}");
            $t .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"File Log..\" {$mouse}>";
            $t .= _emit_image("FileList.jpg",16)."</span>";
            $t .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";
			$t .= "&nbsp;<span style=\"color:blue;\">Viewing audit trace for {$on_audit_profile['Title']}</span>";
			$t .= "</td></tr>";
		    $t .= "<tr><th><div style=\"width:100%;height:".($_ViewHeight-96)."px;overflow-x:hidden;overflow-y:scroll;\"><table class=\"alc-table\">";
			$t .= "<tr><th>Date</th><th>Time</th><th>Task Ref</th><th>Ticket</th><th>Source Data</th><th>Internal Path</th><th>Target DB</th><th>Records</th><th>Comment</th></tr>";
			foreach ($a as $row) {
			   $t .= "<tr>";
			   $date = date('d/m/y',strtotime($row['IssuedDate']));
			   $time = date('G:i:s',strtotime($row['IssuedDate']));
			   $t .= "<td>{$date}</td><td>{$time}</td>";
			   $mouse = _click_dialog("_dialogTaskDetails","-{$row['TaskId']}");
			   $t .= "<td><span class=\"click-here\" title=\"Task Details\" style=\"color:blue;\" {$mouse}>{$row['TaskId']}</span></td>";
			   $mouse = _click_dialog("_dialogTicketDetails","-{$row['TicketId']}");
			   $t .= "<td><span class=\"click-here\" title=\"Ticket Details\" style=\"color:blue;\" {$mouse}>{$row['TicketId']}</span></td>";
			   $t .= "<td>".wordwrap($row['OriginalFilePath'],50,"<br>",true)."</td>";
			   $t .= "<td>".wordwrap($row['InternalFilePath'],50,"<br>",true)."</td>";
			   $t .= "<td>".wordwrap($row['TargetDB'],50,"<br>",true)."</td>";
			   $t .= "<td>{$row['RecordCount']}</td>";
			   $t .= "<td>".wordwrap($row['ItemText'],50,"<br>",true)."</td>";
			   $t .= "</tr>";
			   }
		    $t .= "</table></div></th></tr>";
		    }
	     break;
	  case 'TREE':
		    $t .= "<tr><th>";
			$t .= _planViewTree($filter);
			$t .= "</th></tr>";
	     break;
	  case 'INDEX':
	        $t .= "<tr><th>"._profileIndexReport(hda_db::hdadb()->HDA_DB_listProfiles(null, $filter))."</th></tr>";
		 break;
	  case 'APPLOG':
	        $t .= "<tr><th>";
			$t .= _appLogMonitor();
			$t .= "</th></tr>";
		 break;
	  case 'CRON':
		    $t .= "<tr><td>";
		    $t .= "<div style=\"width:100%;height:".($_ViewHeight-96)."px;overflow:hidden;\">";
		    $t .= "<div style=\"display:inline-block;width:50%;height:".($_ViewHeight-96)."px;overflow-y:scroll;text-align:left;\">";
		    $t .= (@file_exists($cron_log = "ErrorLogs/cron.html"))?file_get_contents($cron_log):"No Log";
		    $t .= "</div>";
		    $t .= "<div style=\"display:inline-block;width:50%;height:".($_ViewHeight-96)."px;overflow-y:scroll;overflow-x:hidden;text-align:left;\">";
		    $t .= "<table   >";
			$a = hda_db::hdadb()->HDA_DB_reportAutoLog();
			if (!is_null($a)) foreach($a as $row) {
			   $t .= "<tr><td>{$row['Title']}</td><td>".hda_db::hdadb()->PRO_DBdate_Styledate($row['IssuedDate'],true)."</td><td>{$row['ItemText']}</td></tr>";
			   }
			$t .= "</table>";
		    $t .= "</div>";
			$t .= "</div>";
			$t .= "</td></tr>";
	     break;
     }
	  
	  
   return $t;
   }
   
function _monitorLog($from, $limit) {
	global $code_root, $home_root;
	global $Logging_Sources;
	$t = "<table class=\"ptl-table\" >";


	$filter = PRO_ReadParam('_dialogFilterLog_SetSources');
	$sources = null;
	if (isset($filter) && is_array($filter) && count($filter)>0) $sources = $filter;
	$a = hda_db::hdadb()->HDA_DB_readLogger(NULL, $sources, $from, $limit);

	if (isset($a) && is_array($a)) {
	   $onclass = 0; foreach ($a as $row)  {
		  $t .= "<tr><th style=\"background-color:white;\" >";
		  $t .= _emit_image("SOURCE_{$row['Source']}.jpg",32);
		  $t .= "</th>";
		  $t .= "<td colspan=2>";
		  $t .= hda_db::hdadb()->PRO_DBdate_Styledate($row['IssuedDate'], true)." from ";
		  if (!is_null($row['SourceName']) && strlen($row['SourceName'])>0) $t .= $row['SourceName'];
		  else $t .= hda_db::hdadb()->HDA_DB_GetUserFullName($row['OwnerId']);
		  if (array_key_exists($row['Source'],$Logging_Sources)) $t .= $Logging_Sources[$row['Source']]['Caption'];
		  $t .= "<br><span class=\"alc-text\">{$row['ItemText']}</span>";
		  if (!is_null($row['ItemLink'])) $t .= "<span onclick=\"issuePost('LinkTo--{$row['ItemLink']}---');\" class=\"tr-link-to\">Link Here..</span>";
		  $t .= "</td>";
		  $t .= "</tr>";
		  }
	   }
	$t .= "</table>"; 
	return $t;
	}

function _dialogTaskDetails($dialog_id='alc_task_details') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;

   $item = null;
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
         list ($action, $item) = explode('-',$ActionLine);
         break;
      }
   $t = _makedialoghead($dialog_id, "Task Details");
   $row = hda_db::hdadb()->HDA_DB_ReadTask($item);
   if (!is_null($row)) {
	  $t .= "<tr><th>Received File</th><td>{$row['RcvFile']}</td><tr>";
	  $t .= "<tr><th>Process Date</th><td>{$row['ProcessDate']}</td></tr>";
	  $t .= "<tr><th>Source</th><td>{$row['Source']}</td></tr>";
	  $t .= "<tr><th>Source Info</th><td>{$row['SourceInfo']}</td></tr>";
	  $t .= "<tr><th>Received Filename</th><td>{$row['RcvFileName']}</td></tr>";
	  $t .= "<tr><th>Final State</th><td>{$row['GoodProcess']}</td></tr>";
	  $t .= "<tr><td colspan=2><textarea style=\"width:100%;height:160px;\" >{$row['ProcessState']}</textarea></td></tr>";
      }
   else $t .= "<tr><td>Task not found</td></tr>";
   $t .= _makedialogclose();

   return $t;
   }
function _dialogEventSummary($dialog_id='alc_event_summary') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;
   $t = "";
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
         list ($action, $ev_today, $ago) = explode('-',$ActionLine);
         break;
      }
   $t = _makedialoghead($dialog_id, "Event Status");
   $t .= "<tr><td><div style=\"height:400px;overflow-y:auto;overflow-x:hidden;\">";
   $t .= "<h2>{$ev_today}</h2>";
   $t .= "<table class=\"alc-table\">";
   $a = hda_db::hdadb()->HDA_DB_eventSummary($ev_today,$ago);
   if (is_array($a)) {
      foreach($a as $row) {
	     $t .= "<tr><td>{$row['Title']}</td>";
		 switch ($row['EventValue']) {
            case '':
  			   $t .= "<td colspan=3><span style=\"color:blue;\" >No Run Event</span></td>"; break;
		    default:
		       $t .= "<td><span style=\"color:red;\" >{$row['EventCode']}</span></td><td>{$row['EventValue']}</td><td>{$row['IssuedDate']}</td></tr>";
			   break;
			}
	     }
      }
   $t .= "</table>";
   $t .= "</div>";
   $t .= _makedialogclose();
   return $t;
   }
function _dialogAuditFileTime($dialog_id='alc_audit_file_time') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;

   $item = null;
   $on_date = PRO_ReadParam("{$dialog_id}_Ondate");
   if (is_null($on_date)) $on_date = date('Y-m-d',strtotime('yesterday'));
   $afterHr = PRO_ReadParam("{$dialog_id}_afterHr");
   if (is_null($afterHr)) $afterHr = "12";
   $afterMin = PRO_ReadParam("{$dialog_id}_afterMin");
   if (is_null($afterMin)) $afterMin = "00";
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
         break;
	  case "ACTION_{$dialog_id}_Refresh":
         break;
	  case "ACTION_{$dialog_id}_Print":
	     break;
      }
   $t = _makedialoghead($dialog_id, "File Load Time Log", "alc-dialog-wide");
   $t .= "<tr><td>";
   _include_css(array("dhtmlxcalendar","dhtmlxcalendar_dhx_skyblue"));
   _include_script("dhtmlxcalendar");
   $t .= "On Date:&nbsp;<input type=\"text\" id=\"{$dialog_id}_Ondate\" name=\"{$dialog_id}_Ondate\" value=\"{$on_date}\">";
   $t .= "&nbsp;&nbsp;After Time: ";
   $t .= "<select name=\"{$dialog_id}_afterHr\" >";
   for ($i=0;$i<24;$i++) {
      $ii = sprintf('%02d',$i);
	  $selected = ($afterHr==$ii)?"SELECTED":"";
      $t .= "<option value=\"{$ii}\" {$selected}>{$ii}</option>";
	  }
   $t .= "</select> : <select name=\"{$dialog_id}_afterMin\" >";
   for ($i=0;$i<59;$i+=10) {
      $ii = sprintf('%02d',$i);
	  $selected = ($afterMin==$ii)?"SELECTED":"";
	  $t .= "<option value=\"{$ii}\" {$selected}>{$ii}</option>";
	  }
   $t .= "</select>";
   $mouse = _click_dialog($dialog_id,"_Refresh");
   $t .= "&nbsp;&nbsp;<span class=\"click-here\" title=\"Show report..\" {$mouse} >";
   $t .= _emit_image("RefreshThis.jpg",12)."</span>";
   $mouse = _click_dialog($dialog_id,"_Print");
   $t .= "&nbsp;&nbsp;<span class=\"click-here\" title=\"Print report..\" {$mouse} >";
   $t .= _emit_image("PrintThis.jpg",12)."</span>";
   _append_script("
   function loadCalendar() { var t = new dhtmlXCalendarObject(['{$dialog_id}_Ondate']);
      t.setDateFormat('%Y-%m-%d');
         };");
   global $post_load;
   $post_load .= "loadCalendar();";
   $tt = "";
   $tt .= "<div style=\"height:300px;overflow:auto;\"><table class=\"alc-table\">";
   $tt .= "<tr><th>Profile</th><th>Presented Date</th>";
   $tt .= "<th>File</th>";
   $tt .= "<th>Method</th>";
   $tt .= "</tr>";
   $a = hda_db::hdadb()->HDA_DB_reportAuditTime($on_date, "{$afterHr}:{$afterMin}");
   if (!is_null($a) && is_array($a) && count($a)>0) {
      foreach ($a as $row) {
	     $tt .= "<tr>";
		 $tt .= "<td>{$row['Title']}</td>";
		 $tt .= "<td>".hda_db::hdadb()->PRO_DBdate_Styledate($row['IssuedDate'],true)."</td>";
		 $tt .= "<td>{$row['OriginalFilePath']}</td>";
		 $tt .= "<td>".str_replace('AUDIT FILE','',$row['ItemText'])."</td>";
		 $tt .= "</tr>";
	     }
      }
   else $tt .= "<tr><td colspan=4>No Files Found</td></tr>";
   $tt .= "</table></div>";
   $t .= "{$tt}</td></tr>";
   $t .= _makedialogclose();
   switch ($Action) {
	  case "ACTION_{$dialog_id}_Print":
         HDA_PrintThis("File Load Times Log", $tt);
	     break;
      }

   return $t;
   }
function _dialogAuditFileList($dialog_id='alc_audit_files') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;

   $item = null;
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
	  case "ACTION_{$dialog_id}_Refresh":
         list ($action, $item) = explode('-',$ActionLine);
         break;
      }
   $starting_date = PRO_ReadParam("{$dialog_id}_Startdate");
   if (is_null($starting_date)) $starting_date = hda_db::hdadb()->PRO_DB_dateNow();
   $ending_date = PRO_ReadParam("{$dialog_id}_Enddate");
   if (is_null($ending_date)) $ending_date = hda_db::hdadb()->PRO_DB_dateNow();
   $t = _makedialoghead($dialog_id, "File Log", "alc-dialog-wide");
   $t .= "<tr><td>";
   _include_css(array("dhtmlxcalendar","dhtmlxcalendar_dhx_skyblue"));
   _include_script("dhtmlxcalendar");
   $t .= "From Date:&nbsp;<input type=\"text\" id=\"{$dialog_id}_Startdate\" name=\"{$dialog_id}_Startdate\" value=\"{$starting_date}\">";
   $t .= "&nbsp;&nbsp;To Date:&nbsp;<input type=\"text\" id=\"{$dialog_id}_Enddate\" name=\"{$dialog_id}_Enddate\" value=\"{$ending_date}\">";
   $mouse = _click_dialog($dialog_id,"_Refresh-{$item}");
   $t .= "&nbsp;&nbsp;<span class=\"click-here\" title=\"Show report..\" {$mouse} >";
   $t .= _emit_image("RefreshThis.jpg",12)."</span>";
   _append_script("
   function loadCalendar() { var t = new dhtmlXCalendarObject(['{$dialog_id}_Startdate','{$dialog_id}_Enddate']);
      t.setDateFormat('%Y-%m-%d');
         };");
   global $post_load;
   $post_load .= "loadCalendar();";

   $t .= "<div style=\"height:300px;overflow:auto;\"><table class=\"alc-table\">";
   $t .= "<tr><th>Presented Date</th>";
   $t .= "<th>File</th>";
   $t .= "<th>Method</th>";
   $t .= "</tr>";
   $a = hda_db::hdadb()->HDA_DB_reportAuditFiles($item, $starting_date, $ending_date);
   if (!is_null($a) && is_array($a) && count($a)>0) {
      foreach ($a as $row) {
	     $t .= "<tr>";
		 $t .= "<td>".hda_db::hdadb()->PRO_DBdate_Styledate($row['IssuedDate'],true)."</td>";
		 $t .= "<td>{$row['OriginalFilePath']}</td>";
		 $t .= "<td>".str_replace('AUDIT FILE','',$row['ItemText'])."</td>";
		 $t .= "</tr>";
	     }
      }
   else $t .= "<tr><td colspan=3>No Files Found</td></tr>";
   $t .= "</div></table>";
   $t .= "</td></tr>";
   $t .= _makedialogclose();

   return $t;
   }	 
function _dialogTicketDetails($dialog_id='alc_ticket_details') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;

   $item = null;
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
         list ($action, $item) = explode('-',$ActionLine);
         break;
      }
   $t = _makedialoghead($dialog_id, "Ticket Details");
   $a = hda_db::hdadb()->HDA_DB_getTickets($item);
   if (!is_null($a) && is_array($a) && count($a)==1) {
      $row = $a[0];
	  $t .= "<tr><th>Username</th><td>{$row['UserName']}</td><tr>";
	  $t .= "<tr><th>Email</th><td>{$row['Email']}</td></tr>";
	  $t .= "<tr><th>Last Use</th><td>";
	  if (is_array($row['LastData'])) {
		  foreach($row['LastData'] as $s_details) $t .= "{$s_details}<br>";
		  }
	  else $t .= "&nbsp;";
	  $t .= "</td></tr>";
	  $t .= "<tr><td colspan=2><textarea style=\"width:100%;height:160px;\" >{$row['Instructions']}</textarea></td></tr>";
      }
   else $t = "<tr><td>Ticket not found</td></tr>";
   $t .= _makedialogclose();

   return $t;
   }	 

function _planProfileTree($row, $level) { 
   global $code_root, $home_root; 
   $t = "";
   $indent_style = ($level==0)?"color:blue;font-weight:bold;":"color:green;font-weight:normal;";
   $t .= "<tr><td><span style=\"padding-left:{$level}px;{$indent_style}\">{$row['Title']}</span></td>"; 
   $time = hda_db::hdadb()->HDA_DB_timings($row['ItemId']);
   if (!is_array($time) || count($time)==0) $t .= "<td colspan=2>No Time Set</td>";
   else {
      $t .= "<td>{$time[0]['Q']}</td>";
      $t .= "<td>"._style_secs_time($time[0]['HighTime'])."</td>";
	  }
   $ev = hda_db::hdadb()->HDA_DB_SuccessEventDate($row['ItemId']);
   $t .= "<td>Last Success Event: ".((!is_null($ev))?$ev:"not issued")."</td>";
   $passes = _passesRules($ev, hda_db::hdadb()->HDA_DB_relationRule($row['ItemId']), hda_db::hdadb()->HDA_DB_relationDefault($row['ItemId']),hda_db::hdadb()->HDA_DB_relationDataDays($row['ItemId']));
   if (is_null($ev)) {
      $ev_img = "TAG_WAITING.jpg"; $caption = "Waiting, no events, no task runs";
      }
   elseif ($passes===true) {
      $ev_img = "Ready.jpg"; $caption = "Passed Rules and Ready";
	  }
   elseif ($passes===false) {
      $ev_img = "DataFailure.jpg"; $caption = "Failed Trigger Rules, data too late";
	  }
   elseif (is_null($passes)) {
      $ev_img = "AlertWaiting.jpg"; $caption = "Failed Rules, data late";
	  }
   $t .= "<td>"._emit_image($ev_img,18)."</td>";
   $t .= "<td>{$caption}</td>";
   $t .= "<td>{$row['AutoLog']}</td>";
   $t .= "</tr>";
   if (is_array($row['Children']) && count($row['Children'])>0) foreach ($row['Children'] as $child) $t .= _planProfileTree($child, $level+40);
   return $t;
   }
function _dialogShowProcessQs($dialog_id='_dialogShowProcessQs') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   $t = _makedialoghead($dialog_id, "Task Qs", 'alc-dialog-vlarge');
   $t .= "<tr><td>";
   $t .= _showProcessQs($dialog_id);
   $t .= "</td></tr>";
   $t .= _makedialogclose();
   return $t;
   }
function _showProcessQs($dialog_id='_dialogShowProcessQs') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $_ViewHeight;
   global $DevUser;

   $problem = null;
   $process_item = null;
   switch ($Action) {
      case "ACTION_{$dialog_id}":
         list($action, $process_item) = explode('-',$ActionLine);
         break;
	  case "ACTION_{$dialog_id}_AbortMonitor":
         list($action, $process_item, $sessid) = explode('-',$ActionLine);
		 $status = hda_db::hdadb()->HDA_DB_monitor(NULL, NULL, $sessid, 'ABORTED');
	     break;
	  case "ACTION_{$dialog_id}_QRemove":
	     list($action, $process_item, $q_item, $in_q) = explode('-',$ActionLine);
		 hda_db::hdadb()->HDA_DB_RemovePending($q_item);
         $note = "Cleared pending Q for this profile";
         hda_db::hdadb()->HDA_DB_issueNote($process_item, $note, 'TAG_COMPLETE');
		 break;
      }
   $t = "";
   $t .= "<table class=\"alc-table\">";
   if (!is_null($problem)) $t .= "<tr><th colspan=1 style=\"color:red;\" >{$problem}</th></tr>";
   $t .= "<tr><td class=\"buttons\" >";
   $mouse = _click_dialog($dialog_id,"-{$process_item}");
   $t .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Refresh..\" {$mouse}>";
   $t .= _emit_image("RefreshThis.jpg",16)."</span>";
   $t .= "</td></tr>";
   $t .= "<tr><th style=\"background-color:lightgray;color:blue;\" >Monitored Jobs</th></tr>";
   $a = hda_db::hdadb()->HDA_DB_getMonitor();
   if (is_array($a) && count($a)>0) { 
      $t .= "<tr><td><div style=\"height:400px;overflow-y:scroll;overflow-x:scroll;\" ><table class=\"alc-table\" > ";
	  $t .= "<tr>";
	  $t .= "<th>Profile</th><th>ID</th><th>Entered On</th><th>Status</th><th>Msg</th><th>Source</th><th>Pulse</th><th>Q</th><th>&nbsp;</th>";
      $t .= "<th>Process State</th><th>Queue Type</th><th>Owner</th><th>Issued</th><th>Source Info</th>";
	  $t .= "</tr>";
      foreach($a as $row) {
	     $process_title = $row['Title'];
         $t .= "<tr>";
	     $t .= "<td>{$process_title}</td><td>{$row['ItemId']}</td>";
		 $t .= "<td>".hda_db::hdadb()->PRO_DBdate_Styledate($row['EntryTime'],true)."</td>";
		 $t .= "<td>{$row['Status']}</td>";
		 $t .= "<td>{$row['ItemText']}</td>";
         $icon = _iconForSource($row['Source'], $caption);
         $t .= "<td><span  title=\"{$caption}..\" >";
         $t .= _emit_image($icon,24);
         $t .= "</span></td>";
		 $t .= "<td>".hda_db::hdadb()->PRO_DBdate_Styletime($row['Pulse'])."</td>";
		 $t .= "<td>{$row['InQ']}</td>";
		 $t .= "<td>";
		switch ($row['Status']) {
		   case 'RUNNING':
			  $mouse = _click_dialog($dialog_id,"_AbortMonitor-{$process_item}-{$row['SessionId']}");
			  $t .= "<span class=\"click-here\" {$mouse}>[ Abort ]</span>";
			  break;
		   default: $t .= "&nbsp;";
		   }
		 $t .= "</td>";
	     switch ($row['ProcessState']) {
	        default: $icon = "TAG_WAITING.jpg"; break;
	   	    case 'COMPLETED': $icon = "TAG_FINISHED.jpg"; break;
		    case 'RUNNING': $icon = "TAG_PROGRESS.jpg"; break;
			case 'ABORTED': $icon = "TAG_ALERT.jpg"; break;
		    }
         $t .= "<td><span  title=\"{$row['ProcessState']}\" >";
         $t .= _emit_image($icon,24);
         $t .= "</span></td>";
	     $t .= "<td>".((is_null($row['QueueLevel']) || $row['QueueLevel']==0)?"Normal":"Low Priority - {$row['QueueLevel']}")."</td>";
         $t .= "<td>".hda_db::hdadb()->HDA_DB_GetUserFullName($row['OwnerId'])."</td>";
         $t .= "<td>".hda_db::hdadb()->PRO_DBdate_Styledate($row['IssuedDate'],true)."</td>";
         $t .= "<td>{$row['SourceInfo']}</td>";
		$mouse = _click_dialog($dialog_id,"_QRemove-{$process_item}-{$row['ItemId']}-PQ");
		$t .= "<th><span style=\"click-here\" title=\"Remove from Q\" {$mouse} >";
		$t .= _emit_image("DeleteThis.jpg",18);
		$t .= "</span></th>";
	     $t .= "</tr>";
		}
	}
    $t .= "</table></div></td></tr>";


   return $t;
   }
   
function _ticket_manager() {
   global $Action;
   global $ActionLine;
   global $UserCode;
   global $DevUser;
   global $code_root, $home_root;
   global $_ViewHeight;
   global $_ViewWidth;
   
   $problem = null;
   $selected_mailto = array();
   switch($Action) {
      case "ACTION_Tkt_Man_SelectAllMailTo":
         $a = hda_db::hdadb()->HDA_DB_getTickets();
	     foreach($a as $row) $selected_mailto[] = $row['ItemId'];
		 PRO_AddToParams('Tkt_MailTo',$selected_mailto);
		 break;
	  case "ACTION_Tkt_Man_ClearAllMailTo":
	     PRO_Clear('Tkt_MailTo');
		 break;
	  case "ACTION_Tkt_Man_SendAllMailTo":
	     $selected_mailto = PRO_ReadParam('Tkt_MailTo');
         if (is_array($selected_mailto) && count($selected_mailto)>0) {
		    $problem = "Sending ".count($selected_mailto)." tickets: ";
			foreach ($selected_mailto as $ticket) {
			   _sendTicket($ticket, null, $response);
			   $problem .= "{$response}; ";
			   }
		    }
	     break;
	  case "ACTION_Tkt_Man_Delete":
	     list($action,$ticket) = explode('-',$ActionLine);
		 hda_db::hdadb()->HDA_DB_deleteTicket($ticket);
		 break;
	  }
	  
   $a = hda_db::hdadb()->HDA_DB_getTickets();
   $t = "";
   $selected_mailto = PRO_ReadParam('Tkt_MailTo');
   if (!is_array($selected_mailto)) $selected_mailto = array();
   PRO_Clear('Tkt_MailTo');
   if (!is_null($problem)) $t .= "<tr><td>{$problem}</td><tr>";
   if (is_array($a) && count($a)>0) {
      if ($DevUser) {
         $t .= "<tr><td class=\"buttons\" ><span style=\"color:blue;padding:2px;\" >";
	     $mouse = "onclick = \"issuePost('Tkt_Man_SelectAllMailTo',event);return false;\" ";
	     $t .= "&nbsp;&nbsp;<span class=\"click-here\" {$mouse}>[ Select All MailTo ]</span>";
	     $mouse = "onclick = \"issuePost('Tkt_Man_ClearAllMailTo',event);return false;\" ";
	     $t .= "&nbsp;&nbsp;<span class=\"click-here\" {$mouse}>[ Clear All MailTo ]</span>";
         $mouse = "onclick = \"issuePost('Tkt_Man_SendAllMailTo',event);return false;\" ";
	     $t .= "&nbsp;&nbsp;<span class=\"click-here\" {$mouse}>[ Send Tickets to Selected MailTo ]</span>";
         $mouse = _click_dialog("_dialogQuickTkts");
	     $t .= "&nbsp;&nbsp;<span class=\"click-here\" {$mouse}>[ Distribute Ticket Packs to All ]</span>";
	     $t .= "</span>";
         $t .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";
         $mouse = _click_dialog("_dialogExportTickets");
	     $t .= "&nbsp;&nbsp;<span class=\"click-here\" style=\"color:green;\" {$mouse}>[ Export Tickets as XML ]</span>";
	     $t .= "</span>";
         $mouse = _click_dialog("_dialogImportTickets");
	     $t .= "&nbsp;&nbsp;<span class=\"click-here\" style=\"color:green;\" {$mouse}>[ Import &amp; Merge Tickets ]</span>";
	     $t .= "</span>";
		 $t .= "</td></tr>";
		 }
      $t .= "<tr><td><div style=\"height:".($_ViewHeight-96)."px;overflow-y:scroll;overflow-x:hidden;\" ><table class=\"alc-table\" > ";
	  $t .= "<tr><th>Profile</th><th>Issued To</th><th colspan=2>Mail To</th><th>Issued Date</th><th>Issued By</th><th>&nbsp;</th><th>Last Used</th><th>Last Use Detail</th></tr>";
	  $aa = array();
	  foreach ($a as $row) {
	     $aa[$row['ProfileId']][] = $row;
	     }
	  foreach ($aa as $item=>$rows) {
	     if ($DevUser) {
	        $goto_mouse = "onclick=\"issuePost('gotoTab-LD-{$item}---',event); return false;\" ";
		    $mouse_img = "GoForward.jpg";
		    }
	     else {
		    $goto_mouse = _click_dialog("_dialogProfileIndexItem","-{$item}");
		    $mouse_img = "CODEHELP.gif";
		    }
		 $t .= "<tr><td rowspan=".(count($rows)+1).">{$rows[0]['Title']}</td>";
			$t .= "<td colspan=8>";
			$t .= "<span class=\"click-here\" title=\"Open Profile\" {$goto_mouse}>";
			$t .= _emit_image($mouse_img,16);
            $t .= "</span>";
			if ($DevUser) {
               $new_mouse = _click_dialog("_dialogGenUserLink","-{$item}");
			   $t .= "&nbsp;&nbsp;<span class=\"click-here\" title=\"New Ticket for this profile\" {$new_mouse}>";
			   $t .= _emit_image("New.jpg",16);
               $t .= "</span>";
			   }
			$t .= "</td>";
		 $t .= "</tr>";
		 foreach($rows as $row) {
	        $t .= "<tr>";
	        $t .= "<td>{$row['UserName']}</td>";
		    $t .= "<td>{$row['Email']}</td>";
			$checked = (in_array($row['ItemId'], $selected_mailto))?"CHECKED":"";
			$t .= "<td><input type=\"checkbox\" name=\"Tkt_MailTo[]\" value=\"{$row['ItemId']}\" {$checked}></td>";
		    $t .= "<td>".hda_db::hdadb()->PRO_DBdate_Styledate($row['IssuedDate'])."</td>";
		    $t .= "<td>".hda_db::hdadb()->HDA_DB_GetUserFullName($row['CreatedBy'])."</td>";
			if ($DevUser) {
			   $mouse = "onclick=\"issuePost('Tkt_Man_Delete-{$row['ItemId']}--',event); return false;\" ";
			   $t .= "<td><span class=\"click-here\" title=\"Delete Ticket\" {$mouse}>";
			   $t .= _emit_image("DeleteThis.jpg",16);
               $t .= "</span></td>";
			   }
			else $t .= "<td>&nbsp;</td>";
	        if (!is_null($row['LastUseDate'])) {
			   $t .= "<td>".hda_db::hdadb()->PRO_DBdate_Styledate($row['LastUseDate'],true)."</td>";
		       if (is_array($row['LastData'])) {
			      $t .= "<td>";
			      foreach($row['LastData'] as $s_details) $t .= "{$s_details}<br>";
			      $t .= "</td>";
			      }
			   else $t .= "<td>&nbsp;</td>";
			   }
	        else $t .= "<td colspan=2>Not used yet</td>";
	        $t .= "</tr>";
	        }
		  }
	  $t .= "</table></div></td></tr>";
	  }
	else $t .= "<tr><td>No Tickets Issued</td></tr>";
   
   return $t;
   }
   
function _quickSendTickets(&$log) {
   $log = array();
   $tkts = array();
   $tktusers = array();
   $problem = "";
   $a = hda_db::hdadb()->HDA_DB_getTickets();
   foreach($a as $row) {
      if (!array_key_exists($row['Email'], $tkts)) $tkts[$row['Email']] = array();
	  $tkts[$row['Email']][] = _makeFTPTicketFile($row['ItemId'], $row['ProfileId'], $row['UserName'], $row['Instructions']);
	  $tktusers[$row['Email']] = $row['UserName'];
      }
   $work = "tmp/quick_tickets";
   rrmdir($work);
   if (!file_exists($work)) @mkdir($work);
   foreach($tkts as $email=>$files) {
      $id = HDA_isUnique('ZP');
	  @mkdir($zip = "{$work}/{$id}");
      $tmp = "{$zip}/tickets.zip";
	  $log[] = "Zip tickets for {$email}";
	  if (!HDA_zip($tmp, $files, $error)) { $log[] = $error; $problem .= "{$error}<br>"; }
	  else {
	     if (@file_exists($tmp)) { @rename($tmp, $no_zip = "{$zip}/ticket_pkg.pkg"); $attach[] = $no_zip; }
		 global $binary_dir;
		 if (@file_exists($bin_zip = "{$binary_dir}/HDAWTicketRunner.alcb")) $attach[] = $bin_zip;
		 $msg = "Attached to this email is a (zipped) package of all your valid tickets for use outside of direct access to HDAW ";
		 $msg .= "and requires the use of an application that may be attached here. <br>";
		 $msg .= "The ticket package has the extension pkg, you need to rename this to zip and uncompress. <br>";
		 $msg .= "The file HDAWTicketRunner.alcb is a zip file and contains your external ticket app for use ";
		 $msg .= "when beyond direct Web access to HDAW. Rename HDAWTicketRunner.alcb to ALCBTicketRunner.zip and Unzip HDAWTicketRunner.zip to expose HDAW Ticket Runner.exe <br>";
		 if (strlen($email)>0) {
			HDA_EmailTicket($email, $username = $tktusers[$email], "Ticket Package", $msg, $attach);
			$log[] = "Ticket for package sent to {$username} at {$email}";
			}
		 }
      }
   return (strlen($problem)==0)?null:$problem;
   }
function _dialogQuickTkts($dialog_id='alc_quick_tkts') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;

   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
	     $problem = _quickSendTickets($log);
	     break;
      }
   $t = _makedialoghead($dialog_id, "Bulk Distribute Tickets");
   if (!is_null($problem)) $t .= "<tr><th style=\"color:red;\" colspan=2 >{$problem}</th></tr>";
   else {
      $t .= "<tr><td><textarea style=\"width:100%;height:160px;overflow-x:hidden;overflow-y:auto;\">";
	  foreach($log as $line) $t .= "{$line}\n";
	  $t .= "</textarea></td></tr>";
      }
   $t .= "<tr><th>";
   $t .= _closeDialog($dialog_id);
   $t .= "</th></tr>";
   $t .= _makedialogclose();


   return $t;
   }
   
function _dialogExportTickets($dialog_id='alc_export_tickets') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;

   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
	     break;
      }
   $t = _makedialoghead($dialog_id, "Export Tickets");
   $lib_path = "tmp/tickets";
   if (!@file_exists($lib_path)) @mkdir($lib_path);
   $lib_path .= "/tickets.xml";
   $t .= "<tr><td><textarea style=\"width:100%;height:160px;overflow-x:hidden;overflow-y:auto;\" >";
   $xml = "";
   $xml = "<Tickets>\n";
   $a = hda_db::hdadb()->HDA_DB_getTickets();
   foreach($a as $row) {
      $xml .= "<Ticket>\n";
	  $xml .= "<UserName>{$row['UserName']}</UserName>\n";
	  $xml .= "<Email>{$row['Email']}</Email>\n";
	  $xml .= "<Title>{$row['Title']}</Title>\n";
	  $xml .= "<Instructions>{$row['Instructions']}</Instructions>\n";
	  $xml .= "</Ticket>\n";
      }
   $xml .= "</Tickets>";
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
   
function _dialogImportTickets($dialog_id='alc_import_tickets') {
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
               $a = _ticketsToArray($xml->array);
               }
            else $problem = "Can only upload a ticket xml file";
            }
         else $problem = "No data fetched - {$problem}";
         break;
      }
   $t = _makedialoghead($dialog_id, "Import &amp; Merge Tickets");
   $upf_code = null;
   if (!is_null($problem)) $t .= "<tr><th style=\"color:red;\" colspan=2 >{$problem}</th></tr>";
   if (is_null($a)) {
      $t .= "<tr><th>"._insertImportFileDiv($dialog_id, "tkts", "UploadedTickets")."</th></tr>"; 
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
function _ticketsToArray($a) {
   $s = "";
   if (array_key_exists("Tickets", $a)) {
      $fails = $wins = $existing = 0;
      $a = $a['Tickets']; 
	  $a = $a['Ticket'];
	  foreach ($a as $row) {
	     $aa = array();
	     $aa['ProfileId'] = hda_db::hdadb()->HDA_DB_lookUpProfile($row['Title']);
		 if (!is_null($aa['ProfileId'])) {
		    $aa['UserName'] = (!is_array($row['UserName']))?$row['UserName']:"";
			$aa['Email'] = $row['Email'];
			$aa['Instructions'] = $row['Instructions'];
		    $exists = hda_db::hdadb()->HDA_DB_getTickets(null, $aa['ProfileId'], $aa['Email']);
			if (is_array($exists)&&count($exists)>0) $existing++;
			elseif (!is_null($exists)) {
			   $ticket = hda_db::hdadb()->HDA_DB_makeTicket($aa['ProfileId'], $aa['UserName'], $aa['Email'], $aa['Instructions']);
			   if ($ticket!==false) {
			      $wins++;
				  $profile_title = $row['Title']; $item = $aa['ProfileId'];
	              if (@!file_exists($xticket_file="Tickets/{$ticket}/{$profile_title}.tkt")) _makeFTPTicketFile($ticket, $item, $aa['UserName'], $aa['Instructions']);
			      }
			   else $fails++;
			   }
			else $fails++;
		    }
		 else {
		    $s .= "Ticket for {$row['Title']} not created, profile not found\n";
		    $fails++;
			}
	     }
	  $s .= "\n{$wins} updates, {$fails} failed updates, {$existing} already existing\n";
      }
   return $s;
   }

function _dialogAddLog($dialog_id='alc_log_add') {
   global $Action;
   global $_Mobile;
   global $code_root, $home_root;

   global $UserCode;
   global $UserName;
   $request = null;
   switch ($Action) {
      case "ACTION_{$dialog_id}":
         $request = true;
         break;
      case "ACTION_{$dialog_id}_AddLog":
         $text = PRO_ReadParam("{$dialog_id}_LogText");
         HDA_LogThis($text, 'USER');
         PRO_Clear("{$dialog_id}_LogText");
         break;
      }
   if (is_null($request)) return "";
   $t = _makedialoghead($dialog_id, "Add a log entry");
   $t .= "<tr><th>";
   $t .= "<textarea class=\"alc-dialog-text\" name=\"{$dialog_id}_LogText\"  >".PRO_ReadParam("{$dialog_id}_LogText")."</textarea>";
   $t .= "</th></tr>";
   $t .= "<tr><th>";
   $mouse = "onclick=\"issuePost('{$dialog_id}_AddLog---',event); return false; \" ";
   $t .= "<span class=\"push_button blue\" {$mouse}   >Submit</span>"; 
   $t .= "</th></tr>";
   $t .= _makedialogclose();

   return $t;
   }

function _dialogFilterLog($dialog_id='alc_log_filter') {
   global $Action;
   global $_Mobile;
   global $code_root, $home_root;
   global $Logging_Sources;

   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
         break;
      case "ACTION_{$dialog_id}_FilterLog":
         PRO_Clear('LAST_LOGGED');
		 return "";
         break;
      case "ACTION_{$dialog_id}_ClearAll":
	     PRO_AddToParams("{$dialog_id}_SetSources",array());
         PRO_Clear('LAST_LOGGED');
         break;
      case "ACTION_{$dialog_id}_ShowAll":
	     PRO_Clear("{$dialog_id}_SetSources");
         PRO_Clear('LAST_LOGGED');
         break;
      }
   $t = _makedialoghead($dialog_id, "Filter Sources log");
   $set_sources = PRO_ReadParam("{$dialog_id}_SetSources");
   if (!isset($set_sources) || !is_array($set_sources)) foreach ($Logging_Sources as $k=>$p) $set_sources[]=$k;
   $i = 0;
   foreach ($Logging_Sources as $k=>$p) {
      $t .= (($i&1)==0)?"<tr>":"";
      $t .= "<td>"._emit_image("SOURCE_{$k}.jpg",24);
	  $t .= (($i&1)==1)?"&nbsp;&nbsp;":"";
	  $t .= "{$k} {$p['Caption']}";
	  $t .= "</td>";
	  $checked = (in_array($k, $set_sources))?"CHECKED":"";
	  $t .= "<td><input type=\"checkbox\" name=\"{$dialog_id}_SetSources[]\" style=\"border-right:1px solid black;\" value=\"{$k}\" {$checked}></td>";
      $t .= (($i&1)==1)?"</tr>":"";
	  $i++;
      }
   $t .= "<tr><td colspan=4>";
   $mouse = _click_dialog($dialog_id,"_ShowAll");
   $t .= "&nbsp;<span class=\"click-here\" {$mouse}>[ Show All Sources ]</span>";
   $mouse = _click_dialog($dialog_id,"_ClearAll");
   $t .= "&nbsp;<span class=\"click-here\" {$mouse}>[ Clear All Sources ]</span>";
   $t .= "</td></tr>";
   $t .= "<tr><th colspan=4>";
   $mouse = "onclick=\"issuePost('{$dialog_id}_FilterLog---',event); return false;\" ";
   $t .= "<span class=\"push_button blue\" {$mouse}   >Submit</span>"; 
   $t .= "</th></tr>";
   $t .= _makedialogclose();

   return $t;
   }

function _dialogDailyLog($dialog_id='alc_log_daily') {
   global $Action;
   global $_Mobile;
   global $code_root, $home_root;
   $report = null;

   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}":
         $report_path = hda_db::hdadb()->HDA_DB_admin('TodaysReport');
		 $report = (!is_null($report_path) && @file_exists($report_path))?@file_get_contents($report_path):"No Report Found";
         break;
	  case "ACTION_{$dialog_id}_RefreshReport":
	     $report = _make_daily_report();
	     break;
	  case "ACTION_{$dialog_id}_Print":
	     _print_this(_daily_pdf_report(), "Daily Report");
	     break;
      }
   if (is_null($report)) return "";
   $t = _makedialoghead($dialog_id, "Last daily log issued", "alc-dialog-vlarge");
   $t .= "<tr><td colspan=2>";
   $mouse = _click_dialog($dialog_id,"_RefreshReport");
   $t .= "<span class=\"click-here\" title=\"Get an up to date report ...\" {$mouse}>";
   $t .= _emit_image("RefreshThis.jpg",16);
   $t .= "</span>";
   $mouse = _click_dialog($dialog_id,"_Print");
   $t .= "&nbsp;&nbsp;<span class=\"click-here\" title=\"Print report..\" {$mouse} >";
   $t .= _emit_image("PrintThis.jpg",12)."</span>";
   $t .= "</td></tr>";
   $t .= "<tr><td colspan=2>";
   $t .= "<div style=\"width:100%; height:400px; overflow-y:auto; overflow-x:auto; border:none;\" >";
   $t .= $report;
   $t .= "</div>";
   $t .= "</td></tr>";
   $t .= _makedialogclose();

   return $t;
   }


   
   
?>