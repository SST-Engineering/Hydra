<?php



function HDA_Profiles() {
   global $Action;
   global $ActionLine;
   global $_Mobile;

   global $UserCode;
   global $UserName;
   global $code_root, $home_root;
   global $_ViewHeight;
   global $Tab_Menu;


   $problem = null;

   $showingProfile = PRO_ReadParam('showingProfile');
   if (!isset($showingProfile)) $showingProfile = null;

   switch ($Action) {
      case 'ACTION_backToUploads':
         PRO_FindAndClear('alc_edit_profile');
         $showingProfile = null;
         break;
      case 'ACTION_gotoTab':
         list($action, $tab, $showingProfile) = explode('-',$ActionLine);
         break;
	  case 'ACTION_jumpTo':
	     $showingProfile = PRO_ReadParam('alc_profile_stack');
		 break;
      case 'ACTION_UploadsRemovePending':
      case 'ACTION_refreshUpload':
         list($action, $item) = explode('-',$ActionLine);
         break;
      case 'ACTION_ChangeUserFilter':
         $showing_filter = PRO_ReadParam("alc_user_filter_On");
         $showing_filter = ($showing_filter=='All')?'Owner':'All';
         PRO_AddToParams("alc_user_filter_On", $showing_filter);
         break;
      }
   switch ($Action) {
      case 'ACTION_UploadsRemovePending':
         list($action, $item) = explode('-',$ActionLine);
         $pendingQ = hda_db::hdadb()->HDA_DB_pendingQ($item);
         $profile = hda_db::hdadb()->HDA_DB_ReadProfile($item);
         if (!is_null($profile) && is_array($profile) && !is_null($pendingQ) && is_array($pendingQ) && count($pendingQ)>0) {
            foreach ($pendingQ as $row) {
               hda_db::hdadb()->HDA_DB_RemovePending($row['ItemId']);
               }
            $note = "Cleared pending Q for this profile";
            hda_db::hdadb()->HDA_DB_issueNote($item, $note, 'TAG_COMPLETE');
            HDA_LogThis("{$profile['Title']} {$note}");
            }
         break;
      case 'ACTION_TriggerNow':
         list($action, $item) = explode('-',$ActionLine);
         HDA_ReportTrigger($item);
		 if (!is_null($code = hda_db::hdadb()->HDA_DB_addRunQ(
						NULL, 
						$item,
						$UserCode,					
						NULL, 
						NULL, 
						'TRIGGER',
						"Manual Trigger",
						hda_db::hdadb()->PRO_DB_dateNow()))) {
					$note = "Triggered to pending process queue";
					hda_db::hdadb()->HDA_DB_issueNote($item, $note, 'TAG_PROGRESS');
					HDA_LogThis(hda_db::hdadb()->HDA_DB_TitleOf($item)." {$note}");
					}
         break;
      case 'ACTION_RunNow':
         if (hda_db::hdadb()->HDA_DB_TakeLock('PQ')) {
            list($action, $item) = explode('-',$ActionLine);
			 if (!is_null($code = hda_db::hdadb()->HDA_DB_addRunQ(
							NULL, 
							$item,
							$UserCode,					
							NULL, 
							NULL, 
							'TRIGGER',
							"Manual Trigger and Run",
							hda_db::hdadb()->PRO_DB_dateNow()))) {
						$note = "Triggered to pending process queue\n{$in_pack['Comment']}";
						hda_db::hdadb()->HDA_DB_issueNote($item, $note, 'TAG_PROGRESS');
						HDA_LogThis(hda_db::hdadb()->HDA_DB_TitleOf($item)." {$note}");
						}
            $q = hda_db::hdadb()->HDA_DB_pendingQ(NULL, $code);
            if (!is_null($q) && is_array($q) && count($q)==1) {
               HDA_ProcessPending($q[0]);
               hda_db::hdadb()->HDA_DB_RemovePending($q[0]['ItemId']);
 
               $problem = "Ran job direct in Q0";
               }
            else $problem = "Unable to obtain job from pending queue";
            hda_db::hdadb()->HDA_DB_DropLock('PQ');
            }
         else $problem = "Unable to run now, pending actions being processed";
         break;
      }


   $t = "";
   $Tab_Menu = "";
   if (!is_null($problem)) $t .= "<tr><th style=\"color:red;\">{$problem}</th></tr>";
   if (!is_null($showingProfile)) {

	  $a = hda_db::hdadb()->HDA_DB_profileIndexItem($showingProfile);

      $others = hda_db::hdadb()->HDA_DB_OnLineSubject($UserCode, $showingProfile, $a['Title']);
      $mouse = _click_dialog("_dialogDeleteProfile", "_ToConfirm-{$a['ItemId']}");
      $Tab_Menu .= "<div style=\"float:right;\"><span  title=\"Delete profile..\"  {$mouse}>";
      $Tab_Menu .= _emit_image("DeleteThis.jpg",18);
      $Tab_Menu .= "</span>&nbsp;";
	  $Tab_Menu .= "</div>";

      $mouse = "onclick=\"issuePost('backToUploads---',event);return false; \" ";
      $Tab_Menu .= "<span style=\"cursor:pointer;height:16px;\" title=\"Back to Profile Index..\" {$mouse}>";
      $Tab_Menu .= _emit_image("GoBack.jpg",18)."</span>";
	  
	  $profile_stack = PRO_ReadParam('ProfileStack');
	  if (!is_array($profile_stack)) $profile_stack = array();
	  $profile_stack[$showingProfile] = $a['Title'];
	  if (count($profile_stack)>1) {
	     $mouse = "onchange=\"issuePost('jumpTo',event); return false;\" ";
	     $Tab_Menu .= "&nbsp;<select name=\"alc_profile_stack\" {$mouse} style=\"margin-bottom:0px;\" >";
		 foreach($profile_stack as $k=>$v) {
		    $selected = ($k==$showingProfile)?"SELECTED":"";
			$Tab_Menu .= "<option value=\"{$k}\" {$selected}>{$v}</option>";
		    }
	     $Tab_Menu .= "</select>";
		 }
	  PRO_AddToParams('ProfileStack', $profile_stack);
		 
      $mouse = "onclick=\"issuePost('refreshUpload-{$showingProfile}---',event);return false;\"  ";
      $Tab_Menu .= "&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Refresh..\" {$mouse}>";
      $Tab_Menu .= _emit_image("RefreshThis.jpg",18)."</span>&nbsp;";
		 
      $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";

      $pending = hda_db::hdadb()->HDA_DB_pendingQ($showingProfile);
      if (is_null($pending) || !is_array($pending) || count($pending)==0) $pending = null;
		 
      $mouse = _click_dialog("_dialogNote","-{$showingProfile}"); 
      $Tab_Menu .= "&nbsp;<span  title=\"Issue a note about this..\"  {$mouse}>";
      $Tab_Menu .= _emit_image("CommentOn.jpg",18);
      $Tab_Menu .= "</span>&nbsp;";

	  
	  
      $mouse = _click_dialog("_dialogEditProfile","-{$a['ItemId']}"); 
      $Tab_Menu .= "&nbsp;<span  title=\"Edit profile..\"  {$mouse}>";
      $Tab_Menu .= _emit_image("Edit.jpg",18);
      $Tab_Menu .= "</span>&nbsp;";


	  
      $mouse = _click_dialog("_dialogEditProcessPackage","-{$a['ItemId']}");
      $Tab_Menu .= "&nbsp;<span  title=\"Load and View package..\"  {$mouse} class=\"click-here\" >";
      $Tab_Menu .= _emit_image("MoveTo.jpg",18);
      $Tab_Menu .= "</span>&nbsp;";

      $mouse = _click_dialog("_dialogExportProfile","-{$a['ItemId']}");
      $Tab_Menu .= "&nbsp;<span  title=\"Export profile..\"  {$mouse}>";
      $Tab_Menu .= _emit_image("Export.jpg",18);
      $Tab_Menu .= "</span>&nbsp;";

      $mouse = _click_dialog("_dialogCopyProfile","-{$a['ItemId']}");
      $Tab_Menu .= "&nbsp;<span  title=\"Make a copy of this profile..\"  {$mouse}>";
      $Tab_Menu .= _emit_image("CopyThis.jpg",18);
      $Tab_Menu .= "</span>&nbsp;";

      $mouse = _click_dialog("_dialogGenUserLink","-{$a['ItemId']}");
      $Tab_Menu .= "&nbsp;<span  title=\"Generate a user ticket for profile access..\"  {$mouse}>";
      $Tab_Menu .= _emit_image("Trust.jpg",18);
      $Tab_Menu .= "</span>&nbsp;";

	  
      $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";
	  

         $mouse = _click_dialog("_dialogCustomImmediate","-{$showingProfile}");
         $Tab_Menu .= "&nbsp;<span title=\"Run code..\"  {$mouse} class=\"click-here\" >";
         $Tab_Menu .= _emit_image("RunNow.jpg",18);
         $Tab_Menu .= "</span>&nbsp;";

         $mouse = "onclick=\"issuePost('TriggerNow-{$showingProfile}---',event); return false;\" ";
         $Tab_Menu .= "&nbsp;<span title=\"Trigger without an upload...\" {$mouse} class=\"click-here\" >";
         $Tab_Menu .= _emit_image("TriggerThis.jpg",18);
         $Tab_Menu .= "</span>&nbsp;";
		 
         $mouse = _click_dialog("_dialogPriorityQ","-{$showingProfile}");
         $Tab_Menu .= "&nbsp;<span title=\"Change Priority Queue...\" {$mouse} class=\"click-here\" >";
         $Tab_Menu .= _emit_image("PriorityQ.jpg",18);
         $Tab_Menu .= "</span>&nbsp;";
		 
         $mouse = _click_dialog("_dialogUpload","-{$showingProfile}");
         $Tab_Menu .= "&nbsp;<span  title=\"Start uploading..\"  {$mouse} class=\"click-here\" >";
         $Tab_Menu .= _emit_image("UploadHere.jpg",18);
         $Tab_Menu .= "</span>&nbsp;";

         $mouse = _click_dialog("_dialogAutoCollect","-{$showingProfile}");
         $Tab_Menu .= "&nbsp;<span  title=\"Set Auto Collecting..\"  {$mouse} class=\"click-here\" >";
         $Tab_Menu .= _emit_image("AutoCollect.jpg",18);
         $Tab_Menu .= "</span>&nbsp;";
		 
      $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";
	  
      $mouse = _click_dialog("_dialogSetSchedule","-{$showingProfile}");
      $Tab_Menu .= "&nbsp;<span title=\"Set schedule..\" {$mouse} class=\"click-here\" >";
      $Tab_Menu .= _emit_image("Frequency.jpg",18);
      $Tab_Menu .= "</span>&nbsp;";
	  
      $mouse = _click_dialog("_dialogBlockoutList","-{$showingProfile}");
      $Tab_Menu .= "&nbsp;<span title=\"Set Blockout Dates..\" {$mouse} class=\"click-here\" >";
      $Tab_Menu .= _emit_image("BlockoutDates.jpg",18);
      $Tab_Menu .= "</span>&nbsp;";
	  
      $mouse = _click_dialog("_dialogPreProcessCustomParams","-{$showingProfile}");
      $Tab_Menu .= "&nbsp;<span title=\"Check Parameters..\"  {$mouse} class=\"click-here\" >";
      $Tab_Menu .= _emit_image("Params.jpg",18);
      $Tab_Menu .= "</span>&nbsp;";

      $mouse = _click_dialog("_dialogProfileFamily","-{$showingProfile}");
      $Tab_Menu .= "&nbsp;<span title=\"Profile relations..\" {$mouse} >";
      $Tab_Menu .= _emit_image("ReportProfiles.jpg",18);
      $Tab_Menu .= "</span>&nbsp;";

      $mouse = _click_dialog("_dialogProfileMarkers","-{$showingProfile}");
      $Tab_Menu .= "&nbsp;<span title=\"View or Clear Markers..\" {$mouse} >";
      $Tab_Menu .= _emit_image("MarkThis.jpg",18);
      $Tab_Menu .= "</span>&nbsp;";

      $mouse = _click_dialog("_dialogProfileTags","-{$showingProfile}");
      $Tab_Menu .= "&nbsp;<span title=\"View, Set and Clear Meta Tags..\" {$mouse} >";
      $Tab_Menu .= _emit_image("TagThis.jpg",18);
      $Tab_Menu .= "</span>&nbsp;";

      $mouse = _click_dialog("_dialogSelectSMS","-{$showingProfile}");
      $Tab_Menu .= "&nbsp;<span  title=\"Select SMS list for error alerts..\"  {$mouse} class=\"click-here\" >";
      $Tab_Menu .= _emit_image("SMS.jpg",18);
      $Tab_Menu .= "</span>&nbsp;";
	  
      $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";
	  
         $mouse = _click_dialog("_dialogHistory","-{$showingProfile}");
         $Tab_Menu .= "&nbsp;<span  title=\"View History..\"  {$mouse}>";
         $Tab_Menu .= _emit_image("HistoryLog.jpg",18);
         $Tab_Menu .= "</span>&nbsp;";

      $mouse = _click_dialog("_dialogConsoleLog","-{$showingProfile}");
      $Tab_Menu .= "&nbsp;<span  title=\"View Console Log..\"  {$mouse}>";
      $Tab_Menu .= _emit_image("Console.jpg",18);
      $Tab_Menu .= "</span>&nbsp;";
      $mouse = _click_dialog("_dialogDebugLog","-{$showingProfile}");
      $Tab_Menu .= "&nbsp;<span  title=\"View Debug Log..\"  {$mouse}>";
      $Tab_Menu .= _emit_image("Debug.jpg",18);
      $Tab_Menu .= "</span>&nbsp;";
	  
      $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";
	  
      $mouse = _click_dialog("_dialogShowProcessQs","-{$showingProfile}");
      $Tab_Menu .= "&nbsp;<span  title=\"Show Task Q..\"  {$mouse}>";
      $Tab_Menu .= _emit_image("BackgroundQ.jpg",18);
      $Tab_Menu .= "</span>&nbsp;";
	  
      $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";
	  
      if (is_array($others) && count($others)>0) {
         $Tab_Menu .= "&nbsp;&nbsp;<span style=\"color:blue;\">Other users reviewing this profile now:&nbsp;";
         foreach($others as $user_in_profile) $Tab_Menu .= "&nbsp;{$user_in_profile}";
         $Tab_Menu .= "</span>";
         }
      $t .= "<tr><td>"._emitUploadProfile($a, $pending)."</td></tr>";
      }
      
   if (is_null($showingProfile)) {
      $showing_filter = PRO_ReadParam("alc_user_filter_On");
      $filter = PRO_ReadParam('alc_category_filter_On');
      if (is_null($filter) || strlen($filter)==0) $filter = null;
      if (is_null($showing_filter)) $showing_filter = 'All';

         $mouse = _click_dialog("_dialogNewProfile");
         $Tab_Menu .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Create a new profile..\" {$mouse}>";
         $Tab_Menu .= _emit_image("AddThis.jpg",18)."</span>";

      $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";

         $mouse = _click_dialog("_dialogImportProfile");
         $Tab_Menu .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Import a profile..\" {$mouse}>";
         $Tab_Menu .= _emit_image("ImportThis.jpg",18)."</span>";


      $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";

         $mouse = _click_dialog("_dialogDefineCategory");
         $Tab_Menu .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Define Categories..\" {$mouse}>";
         $Tab_Menu .= _emit_image("DefineStructure.jpg",18)."</span>";
	  
         $mouse = _click_dialog("_dialogGlobals");
         $Tab_Menu .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Review Site Globals..\" {$mouse}>";
         $Tab_Menu .= _emit_image("Globals.jpg",18)."</span>";

      $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";

         $mouse = _click_dialog("_dialogCodeStudio");
         $Tab_Menu .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Coding Studio..\" {$mouse}>";
         $Tab_Menu .= _emit_image("CodeStudio.jpg",16)."</span>&nbsp;";
		 		 
      $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";

		 $mouse = _click_dialog("_dialogShowProcessQs");
         $Tab_Menu .= "&nbsp;<span  title=\"Show Task Q..\"  {$mouse}>";
         $Tab_Menu .= _emit_image("BackgroundQ.jpg",18);
         $Tab_Menu .= "</span>&nbsp;";

      $Tab_Menu .= "&nbsp;"._emit_image("SeparatorBar.jpg",18)."&nbsp;";

		 $mouse = _click_dialog("_dialogSearch");
         $Tab_Menu .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Search for..\" {$mouse}>";
         $Tab_Menu .= _emit_image("Search.jpg",18)."</span>";
		 
		 $mouse = _click_dialog("_dialogSearchTags");
         $Tab_Menu .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Search by Tags..\" {$mouse}>";
         $Tab_Menu .= _emit_image("TagThis.jpg",18)."</span>";
		 
         $mouse = _click_dialog("_dialogFilterCategory");
         $Tab_Menu .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Filter on Category..\" {$mouse}>";
         $Tab_Menu .= _emit_image("FilterThis.jpg",18)."</span>";

         $mouse = "onclick=\"issuePost('ChangeUserFilter',event); return false;\" ";
         if ($showing_filter=='All') {
            $Tab_Menu .= "&nbsp;&nbsp;Showing all {$filter} profiles";
            $Tab_Menu .= "&nbsp;<span class=\"click-here\" style=\"color:blue;\" {$mouse} >[ Show Only {$filter} Profiles Owned by You ]</span>";
            }
         else {
            $Tab_Menu .= "&nbsp;&nbsp;Showing your {$filter} profiles only";
            $Tab_Menu .= "&nbsp;<span class=\"click-here\" style=\"color:blue;\" {$mouse} >[ Show All {$filter} Profiles ]</span>";
            }
		 $t .= "<tr><td>"._profileIndexReport(hda_db::hdadb()->HDA_DB_listProfiles(($showing_filter=='All')?null:$UserCode, $filter))."</td></tr>";
		 
      }

   PRO_AddToParams('showingProfile', $showingProfile);

   return $t;
   }

function _emitUploadProfile($a, $pending) {
   global $Action;
   global $ActionLine;
   global $UserCode;
   global $code_root, $home_root;
   $t = "";
   
   if (isset($a) && is_array($a) && array_key_exists('ItemId',$a)) {

      $t .= "<div id=\"{$a['ItemId']}_Profile\"  style=\"overflow-y:auto;\" >";
      $t .= "<h3>{$a['Title']}";
      $t .= "</h3>";
	  $t .= "<span style=\"color:gray;font-size:10px;\" >{$a['ItemId']}</span>";
      $t .= "<br>";
      $t .= "<span style=\"color:blue;font-size:12px;\">";
      $t .= "Owned by {$a['Owner']} created on ".hda_db::hdadb()->PRO_DBdate_Styledate($a['CreateDate']);
      $t .= " and last updated on ".hda_db::hdadb()->PRO_DBdate_Styledate($a['ModifiedDate'],true);
      $t .= "</span>";
      if (!is_null($a['Category']) && strlen($a['Category'])>0) {
         $t .= "<br>Assigned to category:&nbsp;";
         $t .= "<span style=\"color:green; font-size:12px; font-weight:bold; font-style:italic;\">{$a['Category']}</span>";
         }
      $mouse =  _click_dialog("_dialogProfileTags","-{$a['ItemId']}");
	  $t .= "<br><span class=\"click-here\" style=\"color:gray;font-size:10px;\" {$mouse} >Tags: {$a['MetaTags']}</span>";

      $t .= "<br><span style=\"color:black;font-size:10px;\">".HDA_displayTextWithLinks($a['ItemText'])."</span><br>";
      if (!is_null($a['Scheduled'])) {
         $t .= "Process next scheduled for ".hda_db::hdadb()->PRO_DBdate_Styledate($a['Scheduled'],true)." then every {$a['Units']} {$a['RepeatInterval']}<br>";
         }
      if (!is_null($pending)) {
         $t .= "<span style=\"color:red;\">There are outstanding process requests in the pending Q - to be processed:</span><br>";
         foreach ($pending as $row) {
            $t .= "Pending process entered by ".hda_db::hdadb()->HDA_DB_GetUserFullName($row['OwnerId'])." on ".hda_db::hdadb()->PRO_DBdate_Styledate($row['IssuedDate'],true)."<br>";
            }
         $mouse = "onclick=\"issuePost('UploadsRemovePending-{$a['ItemId']}---',event); return false;\" ";
         $t .= "<span title=\"Remove from Q..\" {$mouse} class=\"click-here\">";
         $t .= "Remove pending queue requests?&nbsp;";
         $t .= _emit_image("DeleteThis.jpg",16)."</span><br>";
         }
	  if (!is_null($a['EventDate'])) {
	     $t .= "<br/>Last event issued on ".hda_db::hdadb()->PRO_DBdate_Styledate($a['EventDate'],true)." as ";
		 $t .= str_replace("{$a['ItemId']}_","",$a['EventCode']);
	     }
	  if (!is_null($a['AutoDate'])) {
	     $t .= "<br/>Last activity check on ".hda_db::hdadb()->PRO_DBdate_Styledate($a['AutoDate'],true)." reports {$a['AutoText']}";
		 }
	  if ($a['WillCollect']==1) {
	     $t .= "<br/>Profile collects data from {$a['CollectFrom']}";
		 }
      $t .= "<br/>"._emitNotes($a['ItemId']);
      $t .= "</div>";
      }
   return $t;
   }
function _emitNotes($item, $allow_delete=false, $dialog_id='_emitNotes') {
   global $Action;
   global $ActionLine;
   global $Note_Tags;
   global $UserCode;
   global $code_root, $home_root;
   global $key_mouse;
   $t = "";
   $limit = 10;
   switch ($Action) {
      case "ACTION_ShowAllNotes":
         list($action, $for_item) = explode('-',$ActionLine);
         if ($for_item==$item) $limit = NULL;
         break;
      case "ACTION_ShowRecentNotes":
         list($action, $for_item) = explode('-',$ActionLine);
         if ($for_item==$item) $limit = 10;
         break;
      case "ACTION_HideAllNotes":
         list($action, $for_item) = explode('-',$ActionLine);
         if ($for_item==$item) $limit = 0;
         break;
      case "ACTION_DeleteNote":
         list($action, $for_item, $noteid) = explode('-',$ActionLine);
         hda_db::hdadb()->HDA_DB_deleteNotes($for_item, $noteid);
         break;
         
      }
   $a = hda_db::hdadb()->HDA_DB_readNotes($item, NULL, NULL, NULL, $limit, $note_count);
   if (isset($a) && is_array($a) && $note_count>0) {
      $user_filter = NULL;
      $date_filter = NULL;
      $filter = PRO_ReadParam('OnFilter');
      if (isset($filter)) {$user_filter = (isset($filter[0]))?$filter[0]:NULL;$date_filter = (isset($filter[1]))?$filter[1]:NULL; }
      $t .= "<b><u>{$note_count} Notes</u></b>";
      if ($note_count>0) {
         if (!is_null($limit) && ($note_count>$limit)) {
            $t .= ($limit>0)?"&nbsp;showing most recent {$limit}&nbsp;":"&nbsp;hidden&nbsp;";
            $t .= "<span onclick=\"issuePost('ShowAllNotes-{$item}---',event);return false;\" style=\"cursor:pointer;color:blue;\" >[Show All]</span>";
            }
         if ((is_null($limit) && ($note_count>$limit)) || (!is_null($limit) && $limit==0)) {
            $t .= "&nbsp;<span onclick=\"issuePost('ShowRecentNotes-{$item}---',event);return false;\" style=\"cursor:pointer;color:blue;\" >[Most Recent Only]</span>";
            }
         if (!is_null($limit) && $limit<>0) {
            $t .= "&nbsp;<span onclick=\"issuePost('HideAllNotes-{$item}---',event);return false;\" style=\"cursor:pointer;color:blue;\" >[Hide All]</span>";
            }
         $mouse = _click_dialog("_dialogNote","-{$item}");
         $t .= "&nbsp;&nbsp;<span  title=\"Add a note..\"  {$mouse}>";
         $t .= _emit_image("CommentOn.jpg",12);
         $t .= "</span>";
         if (!is_null($user_filter)) $t .= "&nbsp;&nbsp;<span style=\"color:red;\">Filter on contributors</span>";
         if (!is_null($date_filter)&&$date_filter==1) $t .= "&nbsp;&nbsp;<span style=\"color:red;\">Filter on today</span>";
         foreach ($a as $note) 
          if ((is_null($user_filter)||!array_key_exists($note['OwnerId'],$user_filter)||$user_filter[$note['OwnerId']][1])&&
              (is_null($date_filter)||$date_filter==0||hda_db::hdadb()->PRO_DBdate_IsToday($note['IssuedDate']))) {
            $t .= "<p><span style=\"color:blue;\" >";
            if (array_key_exists('Tagged',$note) && 
			array_key_exists($note['Tagged'],$Note_Tags)) {
               $t .= "<span title=\"{$Note_Tags[$note['Tagged']]}\" >";
			   $t .= _emit_image("{$note['Tagged']}.jpg",16)."</span>";
               }
            $t .= "<b>".hda_db::hdadb()->HDA_DB_GetUserFullName($note['OwnerId'])."</b> on ".hda_db::hdadb()->PRO_DBdate_Styledate($note['IssuedDate'],true)."</span>";
            if ($allow_delete && $note['OwnerId']==$UserCode) {
               $mouse = "onclick=\"issuePost('DeleteNote-{$item}-{$note['ItemId']}---',event);return false;\" ";
               $t .= "&nbsp;&nbsp;<span title=\"Delete your note..\" $mouse >";
               $t .= _emit_image("DeleteThis.jpg",12)."</span>";
               }
            $t .= "<br><span style=\"color:black\">".HDA_displayTextWithLinks($note['ItemText'])."</span>";
            if (!is_null($note['Attachment'])) {
               $f = glob("Library/{$note['ItemId']}/{$note['Attachment']}.*");
               if (!is_null($f) && is_array($f) && count($f)==1) {
                  $t .= "<br>This note has an attachment,  <a href=\"{$f[0]}\" target=\"_blank\" >download here</a>";
                  }
               }
			$t .= "</p>";
            }
         }
      }
   else {
      $t .= "No current notes issued";
      $mouse = _click_dialog("_dialogNote","-{$item}");
      $t .= "&nbsp;&nbsp;<span  title=\"Issue a note\"  {$mouse}>";
      $t .= _emit_image("CommentOn.jpg",12);
      $t .= "</span>";
      }
   return $t;
   }

function _dialogNote($dialog_id='_dialogNote') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;

   global $UserName;

   $note_on = null;
   switch ($Action) {
      case "ACTION_{$dialog_id}":
         list($action, $note_on) = explode('-',$ActionLine);
         break;
      case "ACTION_{$dialog_id}_Save":
         list($action, $note_on) = explode('-',$ActionLine);
         hda_db::hdadb()->HDA_DB_issueNote($note_on, $msg = PRO_ReadParam("{$dialog_id}_NoteText"), $tagged = PRO_ReadParam("{$dialog_id}_TAG"));
         break;
      }
   if (is_null($note_on)) return "";
   $t = _makedialoghead($dialog_id, "Add a Note");
   $t .= "<tr><th><textarea class=\"alc-dialog-text\" name=\"{$dialog_id}_NoteText\"  ></textarea></th></tr>";
   $t .= "<tr><td class=\"tag-buttons\" >";
   $t .= _emitTagSelect("{$dialog_id}_TAG");
   $t .= "</td></tr>";
   $t .= _closeDialog($dialog_id, "_Save-{$note_on}");
   $t .= _makedialogclose();

   return $t;
   }

function _emitTagSelect($tag_id='TYPE') {
   global $code_root, $home_root;
   global $key_mouse;
   global $Note_Tags;
   $t = "";
   $selected = PRO_ReadParam("{$tag_id}");
   if (!isset($selected)||is_null($selected)||!array_key_exists($selected,$Note_Tags)) { $kk = array_keys($Note_Tags); $selected = $kk[0];}
   $t .= "<input type=\"hidden\" id=\"{$tag_id}\" name=\"{$tag_id}\" value=\"{$selected}\" >";
   $t .= "<div class=\"alc-set-tag\" id=\"{$tag_id}_SELECT\" >";
   $t .= "<div class=\"alc-say-tag\" >Tagged as <i><span id=\"{$tag_id}_CAPTION\">{$Note_Tags[$selected]}</span></i></div>";
   foreach ($Note_Tags as $k=>$p) {
      $mouse = "onclick=\"HDA_setLoadTags('{$k}','{$p}','{$tag_id}');\" ";
      if ($k==$selected) {
         $t .= "<div id=\"{$tag_id}_{$k}_TAG_ICON\" class=\"alc-tag-item-selected\" {$mouse} title=\"{$p}\" >";
         }
      else {
         $t .= "<div class=\"alc-tag-item\" id=\"{$tag_id}_{$k}_TAG_ICON\" {$mouse} title=\"{$p}\" >";
         }
      $t .= _emit_image("{$k}.jpg",32)."</div>";
      }
   $t .= "</div>";

   return $t;
   }

function _dialogCustomImmediate($dialog_id='_dialogCustomImmediate') {
   global $Action;
   global $ActionLine;
   global $UserCode;
   global $_Mobile;
   global $code_root, $home_root;
   global $_ViewHeight;
   global $_ViewWidth;
 
   $process_item = null;
   $problem = null;
   $can_revert = false;
   $and_run = 3;
   $_on_win = PRO_ReadParam("{$dialog_id}_RunWin");
   if (!isset($_on_win) || is_null($_on_win)) $_on_win = 'PRO';
   PRO_Clear("{$dialog_id}_RunWin");
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}_Refresh":
      case "ACTION_{$dialog_id}":
         list($action,$process_item) = explode('-',$ActionLine);
         $loc_path = HDA_WorkingDirectory($process_item);
         $ff = "{$loc_path}/alcode.alc";
         if (!file_exists($ff)) {
            @file_put_contents($ff, "// ALCODE.ALC - generated ".hda_db::hdadb()->PRO_DBdate_Styledate(hda_db::hdadb()->PRO_DB_dateNow(),true)."\n");_chmod($ff);
            }
         if (file_exists($ff)) {
            PRO_AddToParams("{$dialog_id}_CodeText", str_replace(array("\xe2\x80\x9c","\xe2\x80\x9d","\xe2\x80\x98","\xe2\x80\x99"), array("\"","\"","'","'"), file_get_contents($ff)));
            }
		 $last_monitor = hda_db::hdadb()->HDA_DB_monitorRead(null, $process_item);
		 $log_text = "";
		 if (is_array($last_monitor)) {
		    switch ($last_monitor['Status']) {
			   case 'FINISHED':
			      $log_text .= "Last monitored run on ".hda_db::hdadb()->PRO_DBdate_Styledate($last_monitor['EntryTime'], true)." Finished\n";
		          $ff = "{$loc_path}/console.log";
		          $log_text .= (@file_exists($ff))?@file_get_contents($ff):"";
				  break;
			   case 'RUNNING':
			      $log_text .= "Last monitored run on ".hda_db::hdadb()->PRO_DBdate_Styledate($last_monitor['EntryTime'], true)." RUNNING\n";
			      break;
			   default:
			      $log_text .= "Last monitored run on ".hda_db::hdadb()->PRO_DBdate_Styledate($last_monitor['EntryTime'], true)." status {$last_monitor['Status']}\n";
				  break;
			   }
		    }
         PRO_AddToParams("{$dialog_id}_LOG_Text", $log_text);   
         break;
      case "ACTION_{$dialog_id}_Layout":
         list($action, $process_item) = explode('-',$ActionLine);
         $loc_path = HDA_WorkingDirectory($process_item);
         $ff = "{$loc_path}/alcode.alc";
         @file_put_contents($ff, PRO_ReadParam("{$dialog_id}_CodeText"));_chmod($ff);
         $s = HDA_CodeLayout($loc_path, 'alcode.alc');
         PRO_AddToParams("{$dialog_id}_CodeText", str_replace(array("\xe2\x80\x9c","\xe2\x80\x9d","\xe2\x80\x98","\xe2\x80\x99"), array("\"","\"","'","'"), $s));
         $can_revert = true;
         break;
      case "ACTION_{$dialog_id}_Run":
         list($action, $process_item, $and_run) = explode('-',$ActionLine);
         $and_run |= ($_on_win=='DEV')?2:0;
         $the_log = "";
         $loc_path = HDA_WorkingDirectory($process_item);
         $ff = "{$loc_path}/alcode.alc";
         @file_put_contents($ff, PRO_ReadParam("{$dialog_id}_CodeText"));_chmod($ff);
		 $this_process = hda_db::hdadb()->HDA_DB_runQEntry($process_item);
         HDA_CustomCode($the_log, $this_process, "alcode.alc", $and_run);
		 hda_db::hdadb()->HDA_DB_RemovePending($this_process['ItemId']);
         global $CONSOLE_log;
         PRO_AddToParams("{$dialog_id}_LOG_Text", "{$CONSOLE_log}\n{$the_log}");   
         break;
		 
      case "ACTION_{$dialog_id}_RunMonitor":
         list($action, $process_item, $and_run) = explode('-',$ActionLine);
         $and_run |= ($_on_win=='DEV')?2:0;
         $loc_path = HDA_WorkingDirectory($process_item);
         $ff = "{$loc_path}/alcode.alc";
         PRO_AddToParams("{$dialog_id}_LOG_Text", "Monitor .. Running .. ");   
         @file_put_contents($ff, PRO_ReadParam("{$dialog_id}_CodeText"));_chmod($ff);
		 $this_process = hda_db::hdadb()->HDA_DB_runQEntry($process_item);
         HDA_CustomCode($the_log, $this_process, "alcode.alc", $and_run);
		 hda_db::hdadb()->HDA_DB_RemovePending($this_process['ItemId']);
         global $CONSOLE_log;
         PRO_AddToParams("{$dialog_id}_LOG_Text", "{$CONSOLE_log}\n{$the_log}");   
 		break;
      case "ACTION_{$dialog_id}_EndMonitor":
         list($action, $process_item) = explode('-',$ActionLine);
         $loc_path = HDA_WorkingDirectory($process_item);
		 $ff = "{$loc_path}/console.log";
		 $the_log = (@file_exists($ff))?@file_get_contents($ff):"";
         PRO_AddToParams("{$dialog_id}_LOG_Text", $the_log);   
		 break;
      }
   if (is_null($process_item)) return "";
   $t = "";
   $t .= _makedialoghead($dialog_id, "Run alcode.alc", 'alc-dialog-max'); 
   $sz = _getdialogsizes('alc-dialog-max');
   $code_win_ht = $_ViewHeight-160;
   $code_win_wt = $sz['WD_I']-20;
   if (!is_null($problem)) $t .= "<tr><th colspan=3>{$problem}</th></tr>";
   $mouse = "onkeypress=\"return keyPressEscape();\" ";
   $t .= "<tr><th colspan=3><textarea id=\"{$dialog_id}_CodeText\" name=\"{$dialog_id}_CodeText\" dialog_id=\"{$dialog_id}\" style=\"width:{$code_win_wt}px;margin:4px;height:{$code_win_ht}px;overflow:scroll;resize:none;\" wrap=off {$mouse} >".PRO_ReadParam("{$dialog_id}_CodeText")."</textarea></th></tr>";
   $t .= "<tr>";
   
   $t .= "<th colspan=3  class=\"buttons\" >";
   $t .= "<div class=\"hda-mask-btn\" style=\"padding:6px;height:38px; style:inline-block; flow:inline;top:-10px; \"  >";
   $has_editor = INIT('EDITOR');
   if (!is_null($has_editor) && file_exists(str_replace("\"","",$has_editor))) {
      $bat_file = _win_proc_editor($process_item);
      $mouse = "onclick=\"HDA_run('{$bat_file}'); return false;\" ";   
      $t .= "<span class=\"push_button blue\" {$mouse}  style=\"margin-bottom:8px;margin-top:0px;max-width:200px;\" >Edit with Notepad++</span>&nbsp;&nbsp;"; 
	  }
   $mouse = _click_dialog($dialog_id,"_Run-{$process_item}-0");
   $t .= "<span class=\"push_button blue\" {$mouse} title=\"Save updates only..\" style=\"margin-bottom:8px;margin-top:4px;\" >Save Only</span>";
   $mouse = _click_dialog($dialog_id,"_RunMonitor-{$process_item}-5");
   $t .= "&nbsp;&nbsp;<span class=\"push_button blue\" {$mouse}  style=\"margin-bottom:8px;margin-top:4px;\" >Save &amp; Run</span>"; 
   
   $checked = ($_on_win=='DEV')?"CHECKED":"";
   $t .= "<div style=\"display:inline-block;position:relative;margin-bottom:-8px;margin-top:2px;top:4px;\">";
      $t .= "&nbsp;&nbsp;<span class=\"push_button blue\" title=\"Switch dumping debug\" style=\"width:300px;display:inline-block; \" >";
   $t .= "Dump&nbsp;<label class=\"switch switch-green\"  style=\"display:inline-block;\" >";
     $t .= "<input type=\"checkbox\" class=\"switch-input\" name=\"{$dialog_id}_RunWin\" value=\"DEV\" {$checked}>";
     $t .= "<span class=\"switch-label\" data-on=\"On\" data-off=\"Off\"  ></span>";
    $t .= " <span class=\"switch-handle\" ></span>";
   $t .= "</label>";
   $t .= "</span>";
   $t .= "</div>";
   $mouse = _click_dialog($dialog_id, "_Layout-{$process_item}");
   $t .= "<span class=\"push_button blue\" {$mouse} title=\"Tidy code layout..\" style=\"margin-bottom:8px;margin-top:4px;\" >Tidy Code</span>";
   if ($can_revert) {
      $mouse = _click_dialog($dialog_id,"_Refresh-{$process_item}-0");
	  $t .= "<span class=\"push_button blue\" {$mouse} title=\"Revert layout..\" style=\"margin-bottom:8px;margin-top:4px;\" >Revert Layout</span>";
      }

   $t .= "</div>";
   $t .= "</th></tr>";
	$src_load = "HDAW.php?load=HDA_ReadMonitor&ACTION_CodeMonitor_Start&CODE={$process_item}&USERCODE={$UserCode}&ETIME=0&RUN={$and_run}&";
	$tt_run = "";
	$code_monitor_wt = 320;
	$code_result_wt = $code_win_wt - $code_monitor_wt;
      $tt_run .= "<div id=\"RunMonitor\" style=\"flow:in-line; float:right;margin-top:8px; text-align:left; margin-right:0px;height:120px; width:{$code_monitor_wt}px; border:medium ridge #f0f0f0;\">";
      $tt_run .= "<IFRAME src=\"{$src_load}\" scrolling=\"no\" style=\"text-align:left; height:120px; width:100%; border:none\"></IFRAME>";
      $tt_run .= "</div>";
   $t .= "<tr><th colspan=1>{$tt_run}</th>";
   $t .= "<th colspan=2><textarea name=\"{$dialog_id}_OutText\" style=\"width:{$code_result_wt}px;height:120px;\" >".PRO_ReadParam("{$dialog_id}_LOG_Text")."</textarea></th></tr>";
   $t .= _makedialogclose();

   return $t;
   }


function _dialogPreProcessCustomParams($dialog_id='alc_custom_param_check') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;

   global $code_root, $home_root;
   $add_param = false;
   $update_a = false;
   $item = null;
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}":
      case "ACTION_{$dialog_id}_SubmitParam":
      case "ACTION_{$dialog_id}_DelParam":
      case "ACTION_{$dialog_id}_AddParam":
      case "ACTION_{$dialog_id}_EditParam":
         list($action, $item) = explode('-',$ActionLine);
         break;
      }
   $profile = hda_db::hdadb()->HDA_DB_ReadProfile($item);
   $params_list = $profile['Params'];
   switch ($Action) {
      case "ACTION_{$dialog_id}_SubmitParam":
         $k = PRO_ReadParam("{$dialog_id}_Pname");
         $v =  PRO_ReadParam("{$dialog_id}_Pvalue");
         if (!array_key_exists($k, $params_list)) $a[] = array($v, array('name'=>$k));
         $params_list[$k] = $v;
         $update_a = true;
         break;
      case "ACTION_{$dialog_id}_DelParam":
         list($action, $item, $k_param) = explode('-',$ActionLine);
         unset($params_list[$k_param]);
         $update_a = true;
         break;
      case "ACTION_{$dialog_id}_AddParam":
         PRO_AddToParams("{$dialog_id}_Pvalue", "New_Value");
         PRO_AddToParams("{$dialog_id}_Pname", "#New_Parameter");
         $add_param = true;
         break;
      case "ACTION_{$dialog_id}_EditParam":
         list($action, $item, $k_param) = explode('-',$ActionLine);
         PRO_AddToParams("{$dialog_id}_Pvalue", $params_list[$k_param]);
         PRO_AddToParams("{$dialog_id}_Pname", $k_param);
         $add_param = $k_param;
         break;
      }
   if ($update_a) {
      hda_db::hdadb()->HDA_DB_UpdateProfile($item,array('Params'=>$params_list));
      }

   $t = _makedialoghead($dialog_id,"Set up Parameters");
   $t .= "<tr><td class=\"buttons\" colspan=3>";
   $mouse = _click_dialog($dialog_id,"_AddParam-{$item}");
      $t .= "<span title=\"Add Parameter..\" {$mouse} class=\"click-here\" >";
      $t .= _emit_image("AddThis.jpg",24);
      $t .= "</span>";
   $t .= "</td></tr>";
   $t .= "<tr><td colspan=3><div style=\"width:90%;height:200px;overflow-y:auto;overflow-x:hidden;\"><table class=\"alc-table\">";
   if ($add_param !== false) {
      $t .= "<tr><td><input type=\"text\"  class=\"alc-dialog-name\" {$key_mouse} name=\"{$dialog_id}_Pname\" value=\"".PRO_ReadParam("{$dialog_id}_Pname")."\" ></td>";
      $mouse = "onKeyPress=\"return keyPressPost('{$dialog_id}_SubmitParam-{$item}--',event,'{$dialog_id}')\" ";
      $t .= "<td><input type=\"text\"  class=\"alc-dialog-name\" {$mouse} name=\"{$dialog_id}_Pvalue\" value=\"".PRO_ReadParam("{$dialog_id}_Pvalue")."\" ></td>";
      $t .= "<td>";
      $mouse = _click_dialog($dialog_id,"_SubmitParam-{$item}");
      $t .= "<span title=\"Submit Parameter\" {$mouse} class=\"click-here\" >";
      $t .= _emit_image("Save.jpg",16);
      $t .= "</span>";
      $t .= "</td></tr>";
      }
   foreach ($params_list as $k_param=>$v_param) {
      $t .= "<tr><td>{$k_param}</td><td>{$v_param}</td><td>";
      $mouse = _click_dialog($dialog_id,"_DelParam-{$item}-{$k_param}");
      $t .= "<span title=\"Delete Parameter\" {$mouse} class=\"click-here\" >";
      $t .= _emit_image("DeleteThis.jpg",16);
      $t .= "</span>";
      $mouse = _click_dialog($dialog_id,"_EditParam-{$item}-{$k_param}");
      $t .= "<span title=\"Edit Parameter\" {$mouse} class=\"click-here\" >";
      $t .= _emit_image("Edit.jpg",16);
      $t .= "</span>";
      $t .= "</td></tr>";
      }
   $t .= "</table></div></td></tr>";
   $t .= _makedialogclose();
   return $t;
   }


function _dialogHistory($dialog_id='alc_profile_history') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;

   $item = null;
   $problem = null;
   $limit = 10;
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}":
         list($action, $item, $limit) = explode('-',$ActionLine);
         break;
      }
   if (is_null($item)) return "";
   $ff = glob("CUSTOM/{$item}/console_*.log");
   $t = _makedialoghead($dialog_id,"Task History");
   rsort($ff);
   foreach ($ff as $f) {
		 
        $t .= "<tr><td>{$f}</td>";
		if (preg_match("/[\s\S]{1,}console_(?P<dt>[\d]{14,14}).log/",$f,$dt)) {
		 $t .= "<td>".date('Y-m-d G:i', strtotime($dt['dt']))."</td>";
		 $t .= "<td>";
         $mouse = _click_dialog("_dialogProcessLog","-{$item}-{$dt['dt']}");
         $t .= "<span title=\"History log..\" {$mouse} class=\"click-here\" >";
         $t .= _emit_image("Log.jpg",24, null," class=\"floatright\" ");
		 $t .= "</span>";
		 $t .= "</td>";
		 }
		$t .= "</tr>";
		}
   $t .= _makedialogclose();

   return $t;
   }

function _dialogProcessLog($dialog_id='alc_process_log') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;

   global $code_root, $home_root;

   $item = null;
   $problem = null;
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}":
         list($action, $item, $dt) = explode('-',$ActionLine);
         break;
      }
   if (is_null($item)) return "";
   $s = file_get_contents("CUSTOM/{$item}/console_{$dt}.log");
   $t = _makedialoghead($dialog_id,"Task Console ");
   $t .= "<tr><td><textarea style=\"width:100%;height:200px;resize:none;overflow:auto;\" wrap=off READONLY>{$s}</textarea></td></tr>";
   $t .= _makedialogclose();

   return $t;
   
   }

function _dialogUpload($dialog_id='alc_upload') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;

   global $UserCode;

   $problem = null;
   $uploaded_code = null;
   $profile_item = null;
   $import = null;
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}_ImportedFile";
         list($action, $method, $uploaded_code, $up_path, $profile_item) = explode('-',$ActionLine);
         $import = _actionImportFileDiv($dialog_id, $problem);
         PRO_AddToParams("{$profile_item}_import", $import); 
         break;
      case "ACTION_{$dialog_id}":
         list($action, $profile_item) = explode('-',$ActionLine);
         $uploaded_code = HDA_isUnique('UP');
         PRO_AddToParams("{$profile_item}_upid", $uploaded_code); 
         break;
      }
   
   if (is_null($profile_item)) return "";
   $t = _makedialoghead($dialog_id,"Upload");
   if (!is_null($problem)) $t .= "<tr><th colspan=2 style=\"color:red;\" >{$problem}</th></tr>";
   if (!is_null($import)) {
      $t .= "<tr><th colspan=2>Upload Complete</th></tr>";
      $t .= "<tr><th>Reference</th><td>{$uploaded_code}</td></tr>";
      $t .= "<tr><td colspan=2>";
      $t .= "Size: {$import['Filesize']}<br>";
	  $t .= "Imported to {$import['UploadedPath']}<br>";
      foreach ($import['_FILE'] as $k=>$p) $t .= "{$k}:{$p} ";
      $t .= "<br>";
      foreach ($import['Path_Info'] as $k=>$p) $t .= "{$k}:{$p} ";
      $t .= "</td></tr>";
      $profile = hda_db::hdadb()->HDA_DB_ReadProfile($profile_item);
	  if (!HDA_DataFilesToQ($profile_item, $import['UploadedPath'], 'UPLOAD', $problem, $import['Comment'], $user=null, $effective_date=null, $import['Path'])) {
		 $t .= "<tr><th colspan=2><span style=\"color:red;\">{$problem}</span></th></tr>";
	     }
	  elseif (strlen($problem)>0) $t .= "<tr><th colspan=2>{$problem}</th></tr>";
      _rrmdir(pathinfo($import['UploadedPath'],PATHINFO_DIRNAME));
      PRO_Clear(array("{$profile_item}_import","{$profile_item}_upid"));
      }
   else {
      $t .= "<tr><th>"._insertImportFileDiv($dialog_id, $uploaded_code, "UploadPath", $profile_item)."</th></tr>"; 
      }
   $t .= _makedialogclose();

   return $t;
   }

function _dialogAutoCollect($dialog_id='alc_collect') {
   global $Action;
   global $ActionLine;
   global $UserCode;
   global $_Mobile;
   global $code_root, $home_root;

   global $code_root, $home_root;


   $problem = null;
   $process_item = null;
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}":
	     list($action, $process_item) = explode('-',$ActionLine);
         break;
	  case "ACTION_{$dialog_id}_Save":
	     list($action, $process_item) = explode('-',$ActionLine);
		 if (hda_db::hdadb()->HDA_DB_autoCollect($process_item, PRO_ReadParam("{$dialog_id}_xname"), PRO_ReadParam("{$dialog_id}_enabled"))) $problem = "Saved";
		 else $problem = "Problem saving changes";
         break;
	  
      }
   $t = _makedialoghead($dialog_id,"Auto Data Collection",'alc-dialog-halfwide');
   if (!is_null($problem)) $t .= "<tr><th colspan=2 style=\"color:red;\" >{$problem}</th></tr>";
   $a = hda_db::hdadb()->HDA_DB_autoCollect($process_item);
   if (!is_array($a) || count($a)!=1) {
      $xname = "";
	  $enabled = false;
	  }
   else {
      $xname = $a[0]['ItemText'];
	  $enabled = (($a[0]['Status']&1)==1);
	  }
   PRO_Clear("{$dialog_id}_enabled");
   $mouse = "onkeypress=\"return keyPressPost('{$dialog_id}_Save-{$process_item}--', event, 'alc_collect');\" ";
   $t .= "<tr><td>Locate:&nbsp;<input type=\"text\" name=\"{$dialog_id}_xname\" {$mouse} style=\"width:500px;\" value=\"{$xname}\"></td>";
   $checked = ($enabled)?"CHECKED":"";
   $t .= "<td><input type=\"checkbox\" name=\"{$dialog_id}_enabled\" {$checked} value=\"1\" ></td>";
   $mouse = _click_dialog($dialog_id, "_Save-{$process_item}");
   $t .= "<th><span class=\"click-here\" {$mouse} >";
   $t .= _emit_image("Save.jpg",18)."</span></th>";
   $t .= "</tr>";
      
   $t .= _makedialogclose();

   return $t;
   }
   
function _dialogProfileFamily($dialog_id='alc_profile_family') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;

   $problem = null;
   $item = null;
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}":
         list($action, $item) = explode('-',$ActionLine);
         break;
      }
   
   $t = _makedialoghead($dialog_id,"Profile Family");
   $t .= "<tr><td>";
   $t .= "<div style=\"height:300px;overflow-y:auto;overflow-x:hidden;width:100%;\" ><table class=\"alc-table\">";
   $t .= "<colgroup>";
   $t .= "<col style=\"width:80px;\"><col style=\"width:18px;\">";
   $t .= "<col style=\"width:80px;\"><col style=\"width:80px;\"><col style=\"width:18px;\">";
   $t .= "</colgroup>";
   $t .= "<tr><th colspan=2>Parent</th><th>&nbsp;</th><th colspan=2>Dependants</th></tr>";
   $parent = hda_db::hdadb()->HDA_DB_parentOf($item);
   $t .= "<tr>";
   if (is_array($parent)) {
      $mouse = "onclick=\"issuePost('gotoTab-LD-{$parent['ItemId']}---',event); return false;\" ";
      $t .= "<td><span class=\"click-here\" {$mouse} >{$parent['Title']}</span></td>";
      $t .= "<th><span {$mouse} class=\"more\" title=\"View profile..\" >";
	  $t .= _emit_image("GoForward.jpg",16)."</span></th>";
	  }
   else $t .= "<td colspan=2><span style=\"color:green;font-style:italic;\">Top Level</span></td>";
   $t .= "<td colspan=3>".hda_db::hdadb()->HDA_DB_TitleOf($item)."</td>";
   $t .= "</tr>";
   $tasks = hda_db::hdadb()->HDA_DB_AllSuccessEvents();
   $children = hda_db::hdadb()->HDA_DB_childrenOf($item, NULL, $tasks);
   if (is_array($children)) foreach ($children as $child) {
      $mouse = "onclick=\"issuePost('gotoTab-LD-{$child['ItemId']}---',event); return false;\" ";
      $t .= "<tr><td colspan=3></td><td><span class=\"click-here\" {$mouse} ></span>{$child['Title']}</td>";
      $t .= "<th><span {$mouse} class=\"more\" title=\"View profile..\" >";
	  $t .= _emit_image("GoForward.jpg",16)."</span></th>";
	  $t .= "</tr>";
	  }
   $t .= "</td></tr>";
   $t .= _makedialogclose();

   return $t;
   }
   
function _dialogProfileMarkers($dialog_id='alc_markers') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;


   $item = null;
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}":
         list($action, $item) = explode('-',$ActionLine);
         break;
	  case "ACTION_{$dialog_id}_Clear";
          list($action, $item) = explode('-',$ActionLine);
		  hda_db::hdadb()->HDA_DB_clearMarkers($item);
		  break;
     }
   if (is_null($item)) return "";
   $t = _makedialoghead($dialog_id,"Current Profile Markers");
   $markers = hda_db::hdadb()->HDA_DB_readMarkers($item);
   if (!is_null($markers) && is_array($markers) && count($markers)>0) {
      $t .= "<tr><th><div style=\"width:100%;height:200px;overflow-y:auto;overflow-x:hidden;\" ><table class=\"alc-table\">";
	  foreach ($markers as $row) {
	     $t .= "<tr><td>{$row['ItemId']}</td><td>{$row['ItemText']}</td><td>".hda_db::hdadb()->PRO_DBdate_Styledate($row['IssuedDate'],true)."</td></tr>";
	     }
	  $t .= "</table></div></th></tr>";
	  $mouse =  _click_dialog($dialog_id, "_Clear-{$item}");
	  $t .= "<tr><th>";
      $t .= "<span class=\"push_button blue\" {$mouse}   >Clear All</span>"; 
	  $t .= "</th></tr>";
      }
   else $t .= "<tr><th>No marker key value pairs for this profile</th></tr>";
   $t .= _makedialogclose();

   return $t;
   }

function _dialogProfileTags($dialog_id='alc_tags') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;


   $item = null;
   $problem = null;
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}":
         list($action, $item) = explode('-',$ActionLine);
         break;
	 case "ACTION_{$dialog_id}_Save":
         list($action, $item) = explode('-',$ActionLine);
		 $tags = explode("\n",PRO_ReadParam("{$dialog_id}_Tags"));
		 $set_tags = array();
		 foreach ($tags as $tag) {
		    $tag = trim($tag);
			if (strlen($tag)>0) $set_tags[] = $tag;
		    }
		 $problem = (hda_db::hdadb()->HDA_DB_UpdateProfile($item, array('MetaTags'=>implode(';',$set_tags))))?"Saved metatags":"Fails to save metatags";
         break;
     }
   if (is_null($item)) return "";
   $t = _makedialoghead($dialog_id,"Current Profile Tags");
   if (!is_null($problem)) $t .= "<tr><th style=\"color:red;\">{$problem}</th></tr>";
   $t .= "<tr><th>Permanent tags begin with $ otherwise they are lost on the next code run,<br> Enter one tag per line, or delimit with ';'</th></tr>";
   $tags = HDA_ProfileTags($item, $metatags=NULL, $search = NULL);
   $t .= "<tr><th><textarea name=\"{$dialog_id}_Tags\" style=\"width:100%;height:200px;overflow-y:auto;overflow-x:hidden;\" >";
   if (is_array($tags)) {
	  foreach ($tags as $tag) $t .= "{$tag}\n";
	  }
   $t .= "</textarea></th></tr>";
   $t .= _closeDialog($dialog_id, "_Save-{$item}---");      
   $t .= _makedialogclose();

   return $t;
   }

?>