<?php


function _profileIndexReport($a, $ht = NULL) {
	global $DevUser;
	global $code_root, $home_root;
	global $_ViewHeight;
	if (is_null($ht)) $ht = $_ViewHeight-96;

	$t = "";
	$t .= "<table class=\"ptl-table\">";
	$t .= "<colgroup><col style=\"width:24px;\"><col style=\"width:240px;\"><col style=\"width:16px;\"><col style=\"width:*;\"><col style=\"width:140px;\">";
	$t .= "<col style=\"width:110px;\"></colgroup>";
	$t .= "<tr><th>&nbsp;</th><th colspan=2>Profile</th><th>Description</th><th>Category</th><th>Event Date</th></tr>";
		 
	foreach ($a as $row) {
		if ($DevUser) {
			$mouse = "onclick=\"issuePost('gotoTab-LD-{$row['ItemId']}---',event); return false;\" ";
			$mouse_img = "GoForward.jpg";
			}
		else {
			$mouse = _click_dialog("_dialogProfileIndexItem","-{$row['ItemId']}");
			$mouse_img = "CODEHELP.gif";
			}
		$t .= "<tr {$mouse} style=\"cursor:pointer;\" title=\"Click to open..\" >";
		if (is_null($row['EventCode'])) $img = 'TAG_WAITING.jpg';
		else $img = ($row['EventCode']=="{$row['ItemId']}_SUCCESS")?'TAG_ANSWER.jpg':'TAG_ALERT.jpg';
		$t .= "<th>"._emit_image($img,18)."</th>";
		$t .= "<td><span  title=\"{$row['ItemId']}\" >{$row['Title']}</span></td>";
		$t .= "<td><span {$mouse} class=\"more\" title=\"View process..\" >";
		$t .= _emit_image($mouse_img,18)."</span></td>";
		$t .= "<td>{$row['ItemText']}</td>";
		$t .= "<td><span style=\"font-size:12px;font-weight:bold;font-style:italic;color:green;\" >{$row['Category']}</span></td>";
		$t .= "<td>{$row['EventDate']}</td>";
		$t .= "</tr>";
		}
	$t .= "</table>";
	return $t;
	}

function _dialogProfileIndexItem($dialog_id='alc_index_item') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   

   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
	     list($action, $item) = explode('-',$ActionLine);
         break;
      }
   $t = _makedialoghead($dialog_id, "Profile Details", 'alc-dialog-large');
   $t .= "<tr><td>";
   $a = hda_db::hdadb()->HDA_DB_profileIndexItem($item);
   if (is_null($a)) return "";
   $t .= "<div style=\"width:100%;height:400px;overflow-x:hidden;overflow-y:auto;text-align:left;\" >";
   $t .= "<h3>{$a['Title']}</h3>";
   $t .= $a['ItemText']."<br>";
   $t .= "Contact: {$a['Owner']} {$a['Email']}<br><br>";
   $t .= "Category: {$a['Category']}<br>";
   if (!is_null($a)) {
      $t .= "Scheduled next: ".hda_db::hdadb()->PRO_DBdate_Styledate($a['Scheduled'],true)." Every {$a['Units']} {$a['RepeatInterval']} <br>";
	  }
   $t .= "Auto Trigger Pick-Up: ".hda_db::hdadb()->PRO_DBdate_Styledate($a['AutoDate'],true)." {$a['AutoText']}<br>";

   
   $t .= "Event Status: ";
   switch ($a['EventCode']) {
      case "{$a['ItemId']}_SUCCESS": $t .= "Success"; break;
      case "{$a['ItemId']}_FAILURE": $t .= "Failure"; break;
      case "{$a['ItemId']}_LATE": $t .= "Late"; break;
	  }
   $t .= " on ".hda_db::hdadb()->PRO_DBdate_Styledate($a['EventDate'],true)."<br>";
   $t .= "</div>";
   $t .= "</td></tr>";
   $t .= _makedialogclose();

   return $t;
   }

function HDA_ProfileTags($item=NULL, $metatags=NULL, $search = NULL, &$all_tags = NULL) {
   if (!is_null($item)) {
      $profile = hda_db::hdadb()->HDA_DB_ReadProfile($item);
      if (!is_null($profile) && is_array($profile)) {
	     if (!is_null($metatags) && strlen($metatags)>0) {
		    $tags = $profile['MetaTags'];
			$tags = explode(';',$tags);
		    $metatags = str_replace(',',';',$metatags);
			$metatags = explode(';',$metatags);
			$newtags = array();
			foreach($tags as $tag) { $tag = trim($tag); if (strlen($tag)>0 && $tag[0]=='$') $newtags[] = $tag; }
			foreach($metatags as $tag) if (!_in_array_icase($tag, $newtags)) $newtags[] = trim($tag);
			hda_db::hdadb()->HDA_DB_UpdateProfile($item, array('MetaTags'=>implode(';',$newtags)));
		    }
		 else return explode(';',$profile['MetaTags']);
         }
	  }
   elseif (!is_null($search)) {
      return hda_db::hdadb()->HDA_DB_searchProfileTags($search, $all_tags);
      }
   return null;
   }


function _dialogNewProfile($dialog_id='_dialogNewProfile') {
   global $Action;
   global $_Mobile;
   global $code_root, $home_root;
   $problem = null;

   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
         break;
      case "ACTION_{$dialog_id}_Save":
         $named = PRO_ReadParam("{$dialog_id}_Title");
         $desc = PRO_ReadParam("{$dialog_id}_ItemText");
         if (!isset($named) || is_null($named) || strlen($named)==0) $problem = "New profile must be named";
         else {
            $code = hda_db::hdadb()->HDA_DB_NewProfile(_clean($named), hda_db::hdadb()->HDA_DB_textToDB($desc));
            if (is_null($code)) $problem = "Problem creating new profile {$named}";
			else return null;
            }
         break;
      }
   $t = _makedialoghead($dialog_id, "Create New Profile");
   if (!is_null($problem)) $t .= "<tr><th colspan=2>{$problem}</th></tr>";
   $mouse = "onKeyPress=\"return keyPressPost('{$dialog_id}_Save-',event)\" ";
   $t .= "<tr><th>Profile Name:</th><td><input type=\"text\" class=\"alc-dialog-name\" name=\"{$dialog_id}_Title\" value=\"\" {$mouse} ></td></tr>";
   $t .= "<tr><th>Profile Description:</th><td><textarea class=\"alc-dialog-text\" name=\"{$dialog_id}_ItemText\" value=\"\" wrap=off ></textarea></td></tr>";
   $t .= _closeDialog($dialog_id, "_Save", 2);
   $t .= _makedialogclose();

   return $t;
   }
function _dialogImportProfile($dialog_id='_dialogImportProfile') {
   global $Action;
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
   if (is_null($upf_code)) { $upf_code = HDA_isUnique('XP'); PRO_AddToParams("UPLOAD_PKG_upid", $upf_code); }
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}": break;
      case "ACTION_{$dialog_id}_Save":
		 return "";
      case "ACTION_{$dialog_id}_ImportedFile";
         $import = _actionImportFileDiv($dialog_id, $problem);
         if (!is_null($import)) {
            if ($import['Extension']=='zip') {
               $pack = HDA_unzip($import['UploadedPath'], $upf_code, $problem); //pathinfo($import['UploadedPath'],PATHINFO_DIRNAME));
               if (is_null($problem)) {
                  foreach($pack as $in_pack) {
                     if (strtolower($in_pack['Filename'])=='profile.xml') {
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
            else $problem = "Can only upload a package zip file or profile xml file";
            if (!is_null($a) && is_array($a)) {
               $doThis = "MAKE_NEW";
               $aa = hda_db::hdadb()->HDA_DB_ReadProfile(NULL,  _query('Profile',$a,'Title'));
               if (!is_null($aa)) {
                  $doThis = "ASK_OVERWRITE";
                  PRO_AddToParams("UPLOAD_PKG_profile", $a);
                  PRO_AddToParams("UPLOAD_PKG_pack", $pack);
                  PRO_AddToParams("UPLOAD_PKG_id", $aa['ItemId']);
                  PRO_AddToParams("UPLOAD_PKG_import", $import);
                  $problem = "The profile <b>{$aa['Title']}</b> already exists";
                  }
               }
            else {
               $problem = "There is no profile.xml file uploaded or in the package";
               $doThis = null;
               }
            }
         else $problem = "No data fetched - {$problem}";
         break;
      case "ACTION_{$dialog_id}_MAKENEW":
         $pf_code = HDA_isUnique('PF');
         $this_is_overwrite = false;
      case "ACTION_{$dialog_id}_OVERWRITE":
         $a = PRO_ReadParam("UPLOAD_PKG_profile");
         $pack = PRO_ReadParam("UPLOAD_PKG_pack");
         $import = PRO_ReadParam("UPLOAD_PKG_import");
         if (is_null($pf_code)) { // overwrite
            $pf_code = PRO_ReadParam("UPLOAD_PKG_id");
            }
         else { // make copy
            _query('Profile', $a, 'Title', null, _query('Profile', $a, 'Title')."_Copy_".hda_db::hdadb()->PRO_DB_stampTime());
            }
         break;
       case "ACTION_{$dialog_id}_CANCEL":
         $a = null;
         $import = PRO_ReadParam("UPLOAD_PKG_import");
         PRO_Clear(array("UPLOAD_PKG_profile","UPLOAD_PKG_pack","UPLOAD_PKG_import","UPLOAD_PKG_id","UPLOAD_PKG_upid"));
         _rrmdir(pathinfo($import['UploadedPath'],PATHINFO_DIRNAME));

         break;
      }
   $t = _makedialoghead($dialog_id, "Import a Profile");
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
      $t .= "<u><b>Overwrite</b></u> - replace - the existing profile?</span></td>";
      $t .= "<th>"._emit_image("ReplaceThis.jpg",24,$mouse)."</th>";
      $t .= "</tr>";
      $mouse = _click_dialog($dialog_id,"_MAKENEW");
      $t .= "<tr>";
      $t .= "<td {$mouse}><span class=\"click-here\" >";
      $t .= "Create a <u><b>Copy</b></u> - keep existing profile?</span></td>";
      $t .= "<th>"._emit_image("AddCopy.jpg",24,$mouse)."</th>";
      $t .= "</tr>";
      $mouse = _click_dialog($dialog_id,"_CANCEL");
      $t .= "<tr>";
      $t .= "<td {$mouse} ><span class=\"click-here\">";
      $t .= "<u><b>Cancel</b></u> - discard the fetch and leave existing profile untouched?</span></td>";
      $t .= "<th>"._emit_image("CancelThis.jpg",24,$mouse)."</th>";
      $t .= "</tr>";
      $t .= _makedialogclose();

      return $t;
      }
   else {
      if (is_null($pf_code)) $pf_code = HDA_isUnique('PF');
      $code = $pf_code;
	  $profile = _xml_to_profile($a);
      $profile['ItemId'] = $pf_code;
	  $schedule = _xml_to_schedule($a);
	  if (!is_null($schedule)) {
	     hda_db::hdadb()->HDA_DB_writeSchedule($code, $schedule['Scheduled'], $schedule['RepeatInterval'], $schedule['Units'], NULL);
		 }
      hda_db::hdadb()->HDA_DB_WriteProfile($profile);
	  $note = "Imported profile"; if (!is_null($import)) $note .= "\n{$import['Comment']}";
      hda_db::hdadb()->HDA_DB_issueNote($code, $note, $tagged='TAG_INFO');
      $t .= "<tr><td colspan=2>Imported profile {$profile['Title']}</td></tr>";
      if (!is_null($pack)) {
         $t .= "<tr><td colspan=2>Found a CUSTOM package in profile</td></tr>";
         $t .= "<tr><td colspan=2><div style=\"width:100%;height:120px;overflow:scroll;\"><table class=\"alc-table\">";
         $pkg_loc = HDA_WorkingDirectory($code);
         $t .= "<tr><td colspan=2>Will locate in {$pkg_loc}</td></tr>";
         _cpp($from_dir = pathinfo($import['UploadedPath'], PATHINFO_DIRNAME), $pkg_loc);
         _rrmdir($from_dir);
         $ff = glob("{$pkg_loc}/*");
         foreach ($ff as $f) {
            $pathinfo = pathinfo($f);
            if ($pathinfo['basename']=='profile.xml') { @unlink($f); continue; }
            if ($pathinfo['basename'] == "{$upf_code}.zip") { @unlink($f); continue; }
			if ($pathinfo['basename'] == pathinfo($import['UploadedPath'], PATHINFO_BASENAME)) { @unlink($f); continue; }
            $t .= "<tr><td>{$f}</td><td>".filesize($f)." bytes</td></tr>";
            }
         $t .= "</table></div></td></tr>";
         }
      }
   $t .= "<tr><th colspan=2>";
   $t .= _closeDialog($dialog_id, "_Save");
   $t .= "</th></tr>";
   $t .= _makedialogclose();

   return $t;
   }
function _dialogExportProfile($dialog_id='alc_export_profile') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;

   $problem = null;
   $item = null;
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
	     PRO_Clear("{$dialog_id}_IncludeData");
	  case "ACTION_{$dialog_id}_Refresh":
         list($action, $item) = explode('-',$ActionLine);
         break;
      }
   if (is_null($item)) return "";
   $include_data = PRO_ReadAndClear("{$dialog_id}_IncludeData");
   $include_data = ($include_data==1);
   $a = hda_db::hdadb()->HDA_DB_ReadProfile($item);
   if (is_null($a) || !is_array($a)) $problem = "Error reading profile";
   $t = _makedialoghead($dialog_id, "Export Profile {$a['Title']}");
   $xml = _profile_to_xml($a);
   if (!is_null($problem)) $t .= "<tr><th style=\"color:red;\" colspan=2 >{$problem}</th></tr>";
   if (!is_null($a) && is_array($a)) {
      $t .= "<tr><th colspan=2><textarea class=\"alc-dialog-text\" style=\"height:200px;\" wrap=off >{$xml}</textarea></th></tr>";
      }
   $tmp_dir = "tmp/";
   $tmp_dir .= $item;
   if (!file_exists($tmp_dir)) mkdir($tmp_dir);
   $pkg = HDA_WorkingDirectory($item);
   @file_put_contents($fpath = "{$pkg}/profile.xml", $xml); _chmod($fpath);


   $problem = "zipped package pkg.zip";
   $zip = new ZipArchive();
   if ($zip->open($lib_path = "{$tmp_dir}/{$a['Title']}-pkg.zip", ZIPARCHIVE::CREATE)!==true) {
       $problem = " -- Unable to create a zip archive  ";
       $lib_path = null;
       }
   else {       
       addFolderToZip("{$pkg}", $zip, $zip_dir='', $include_data );
       }
   $zip->close();
   $t .= "<tr><td>Package found at {$pkg}</td><td>{$problem}</td></tr>";
   if (!is_null($lib_path)) $t .= "<tr><th colspan=2>"._insertDownloadFileDiv($dialog_id, $lib_path)."</th></tr>";
   $t .= "<tr><th colspan=2>";
   $t .= _closeDialog($dialog_id);
   $t .= "</th></tr>";
   $t .= _makedialogclose();

   return $t;
   }
function _xml_to_schedule($a) {
   $schedule = null;
   if (array_key_exists('_c',$a['Profile']) && array_key_exists('Schedule',$a['Profile']['_c'])) {
       $on_schedule = hda_db::hdadb()->HDA_DB_unxml(hda_db::hdadb()->HDA_DB_toxml(array('Schedule'=>$a['Profile']['_c']['Schedule'])));
	   if (array_key_exists('Schedule',$on_schedule)) {
	      $on_schedule = $on_schedule['Schedule']['_c'];
	      $schedule = array();
	      foreach ($on_schedule as $k=>$p) {
		    $schedule[$k] = $p['_v'];
	        }
	      }
	   }
   return $schedule;
   }
function _xml_to_profile($a) {
   global $UserCode;
   $profile = array();
	  $owner = _query('Profile',$a,'Owner');
	  $owner_user = hda_db::hdadb()->HDA_DB_FindUser($owner);
      $profile['CreatedBy'] = (is_array($owner_user) && count($owner_user)==1)?$owner_user[0]['UserItem']:$UserCode;
      $profile['CreateDate'] = hda_db::hdadb()->PRO_DB_dateNow();
      $profile['Title'] = _query('Profile',$a,'Title');
      $profile['ItemText'] = _query('Profile',$a,'Description');
      $profile['Category'] = _query('Profile',$a,'Category');
	  $profile['Q'] = _query('Profile',$a,'Q');
	  $profile['SMS'] = _query('Profile',$a,'SMS');
	  $x = _query('Profile',$a,'ParamList');
	  $params = array();
	  if (is_array($x)) {
	     foreach($x as $param) {
		    $params[$param[1]['name']] = $param[0];
		    }
	     }
	  $profile['Params'] = $params;
	  $x = _query('Profile',$a,'Tickets');
	  $tickets = array();
	  if (is_array($x)) {
	     foreach($x as $ticket) {
		    $tickets[$ticket[1]['id']] = $ticket[0];
		    }
	     }
	  $profile['Tickets'] = $tickets;
   return $profile;
   }

function _profile_to_xml($a) {
   $xml= "";
   $xml = "<Profile>";
   $xml .= "<Title>{$a['Title']}</Title>\n";
   $xml .= "<Description>{$a['ItemText']}</Description>\n";
   $xml .= "<Category>{$a['Category']}</Category>\n";
   $owner = hda_db::hdadb()->HDA_DB_FindUser($a['CreatedBy']);
   if (is_array($owner) && count($owner)==1)
      $xml .= "<Owner>{$owner[0]['Email']}</Owner>\n";
   $xml .= "<ParamList>\n";
   foreach ($a['Params'] as $pk=>$pv) {
      $xml .= "<Param name=\"{$pk}\">{$pv}</Param>\n";
      }
   $xml .= "</ParamList>\n";
   $xml .= "<Q>{$a['Q']}</Q>\n";
   $xml .= "<SMS>{$a['SMS']}</SMS>\n";
   $on_schedule = hda_db::hdadb()->HDA_DB_getSchedule($a['ItemId']);
   if (is_array($on_schedule) && count($on_schedule)==1) {
      $xml .= "   <Schedule>\n";
      $xml .= "      <RepeatInterval>{$on_schedule[0]['RepeatInterval']}</RepeatInterval>\n";
      $xml .= "      <Units>{$on_schedule[0]['Units']}</Units>\n";
      $xml .= "      <Scheduled>{$on_schedule[0]['Scheduled']}</Scheduled>\n";
	  $xml .= "   </Schedule>\n";
	  }
   $xml .= "<Tickets>\n";
   $tickets = hda_db::hdadb()->HDA_DB_getTickets(null, $a['ItemId']);
   if (is_array($tickets)) {
      foreach ($tickets as $ticket) {
	     $xml .= "   <Ticket id=\"{$ticket['ItemId']}\">{$ticket['UserName']}</Ticket>\n";
	     }
      }
   $xml .= "</Tickets>\n";
   $xml .= "</Profile>\n";
   return $xml;
   }
   

  
function _dialogSelectSMS($dialog_id='alc_sms_select') {
   global $Action;
   global $ActionLine;
   global $code_root, $home_root;
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
	     list($action, $item) = explode('-',$ActionLine);
         break;
	  case "ACTION_{$dialog_id}_Save":
	     list($action, $item) = explode('-',$ActionLine);
	     $sms_name = PRO_ReadParam("{$dialog_id}_SMS");
		 if (!is_null($sms_name) && strlen($sms_name)==0) $sms_name = null;
		 hda_db::hdadb()->HDA_DB_UpdateProfile($item, array('SMS'=>$sms_name));
		 break;
   }
   $onProfile = hda_db::hdadb()->HDA_DB_ReadProfile($item);
   $on_sms = $onProfile['SMS'];
   $sms = hda_db::hdadb()->HDA_DB_admin('SMS');
   if (!is_null($sms)) $sms = hda_db::hdadb()->HDA_DB_unserialize($sms);
   else $sms = array();
   if (!is_null($on_sms) && !array_key_exists($on_sms,$sms)) $on_sms = null;

   $t = _makedialoghead($dialog_id, "Select SMS List for Error Alerts");
   $mouse = _change_dialog($dialog_id,"_Save-{$item}");
   $t .= "<tr><th><select name=\"{$dialog_id}_SMS\" {$mouse}>";
   $t .= "<option value=\"\" SELECTED>Clear SMS Alert</option>";
   foreach ($sms as $sms_name=>$sms_collection) {
      $selected = ($on_sms==$sms_name)?"SELECTED":"";
	  $t .= "<option value=\"{$sms_name}\" {$selected}>{$sms_name}</option>";
	  }
   $t .= "</select></th></tr>";
   $t .= _makedialogclose();

   return $t;

   }


function _dialogCopyProfile($dialog_id='alc_copy_profile') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $UserCode;

   $item = null;
   $new_item = null;
   $did_copy = false;
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}": 
         list($action, $item) = explode('-',$ActionLine);
         $a = hda_db::hdadb()->HDA_DB_ReadProfile($item);
         if (is_null($a) || !is_array($a)) return "";
         $a['ItemId'] = $new_item = HDA_isUnique('PF');
         $a['Title'] = "Copy_of_{$a['Title']}";
         PRO_AddToParams("{$dialog_id}_Copy",$a);
         break;
      case "ACTION_{$dialog_id}_Save":
         list($action, $item) = explode('-',$ActionLine);
         $a = PRO_ReadParam("{$dialog_id}_Copy");
         $a['Title'] = PRO_ReadParam("{$dialog_id}_Title");
         $a['ItemText'] = PRO_ReadParam("{$dialog_id}_ItemText");
         hda_db::hdadb()->HDA_DB_writeProfile($a);
         $dir_src = HDA_WorkingDirectory($item);
         $dir_dst = HDA_WorkingDirectory($a['ItemId']);
         $copy_files = PRO_ReadParam("{$dialog_id}_CopyFiles");
         if (!is_null($copy_files) && is_array($copy_files)) {
            foreach ($copy_files as $f) {
               if (file_exists("{$dir_src}/{$f}")) copy("{$dir_src}/{$f}", "{$dir_dst}/{$f}");
               }
            }
         $did_copy = true;
         break;
      }
   if (is_null($item)) return "";
   $t = _makedialoghead($dialog_id, "Make a Profile Copy");
   if (!$did_copy) {
      $mouse = "onKeyPress=\"return keyPressPost('{$dialog_id}_Save-{$item}',event)\" ";
      $t .= "<tr><th>Copy Profile Name:</th><td><input type=\"text\" class=\"alc-dialog-name\" name=\"{$dialog_id}_Title\" value=\"{$a['Title']}\" {$mouse} ></td></tr>";
      $t .= "<tr><th>Profile Description:</th><td><textarea class=\"alc-dialog-text\" name=\"{$dialog_id}_ItemText\" wrap=off >{$a['ItemText']}</textarea></td></tr>";
      $t .= "<tr><th colspan=2>Profile also has these package files, select to copy</th></tr>";
	  $t .= "<tr><td colspan=2><div style=\"width:100%;height:160px;overflow-x:hidden;overflow-y:auto;\"><table class=\"alc-table\">";
      $dir_src = HDA_WorkingDirectory($item);
      $ff = glob("{$dir_src}/*.*");
      foreach ($ff as $f) if (is_file($f)){
         $f = strtolower(pathinfo($f, PATHINFO_BASENAME));
         switch ($f) {
            case 'alcode.alc': 
               $t .= "<tr><td>ALC Code</td><td><input type=\"checkbox\" name=\"{$dialog_id}_CopyFiles[]\" value=\"alcode.alc\" CHECKED></td></tr>";
               break;
            default:
               $t .= "<tr><td>{$f}</td><td><input type=\"checkbox\" name=\"{$dialog_id}_CopyFiles[]\" value=\"{$f}\"></td></tr>";
               break;
            }
         }
	  $t .= "</table></div></td></tr>";
      $t .= "<tr><th colspan=2>";
      $t .= _closeDialog($dialog_id, "_Save-{$item}");
      $t .= "</th></tr>";
      }
   else {
      $t .= "<tr><th colspan=2>New profile {$a['Title']}</th></tr>";
      $dir_src = HDA_WorkingDirectory($a['ItemId']);
      $ff = glob("{$dir_src}/*.*");
 	  $t .= "<tr><td colspan=2><div style=\"width:100%;height:160px;overflow-x:hidden;overflow-y:auto;\"><table class=\"alc-table\">";
      foreach ($ff as $f) if (is_file($f)){
         $t .= "<tr><td colspan=2>{$f} copied</td></tr>";
         }
	  $t .= "</table></div></td></tr>";
      }
   $t .= _makedialogclose();


   return $t;
   }



function _dialogDeleteProfile($dialog_id='alc_confirm_delete') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;

   $item = null;
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}": break;
      case "ACTION_{$dialog_id}_ToConfirm":
         list($action, $item) = explode('-',$ActionLine);
         break;
      case "ACTION_{$dialog_id}_Delete":
         list($action, $item) = explode('-',$ActionLine);
         $a = hda_db::hdadb()->HDA_DB_ReadProfile($item);
         hda_db::hdadb()->HDA_DB_DeleteProfile($item);
          _rrmdir(HDA_WorkingDirectory($item));
         if (!is_null($a) && is_array($a))  HDA_LogThis("Profile {$a['Title']} deleted");
         $item = null;
         global $showingProfile;
         $showingProfile = null; 
		 PRO_Clear('showingProfile');
         break;
      }
   if (is_null($item)) return "";
   $a = hda_db::hdadb()->HDA_DB_ReadProfile($item);
   if (is_null($a) || !is_array($a)) return "";
   $t = _makedialoghead($dialog_id, "Confirm Delete Profile");
   $t .= "<tr><td>";
   $t .= "Are you sure you want to delete profile {$a['Title']}? This will also delete all uploaded files and notes for this profile.";
   $mouse = "onclick=\"issuePost('{$dialog_id}_Delete-{$item}---',event); return false;\" ";
   $t .= "<br><center><b><span title=\"Confirm Delete..\" {$mouse} class=\"click-here\"  >";
   $t .= "Yes delete this "._emit_image("DeleteThis.jpg",24,$mouse);
   $t .= "</span></b></center><br>";
   $t .= "</td></th>";
   $t .= _makedialogclose();

   return $t;
   }

function _dialogEditProfile($dialog_id='alc_edit_profile') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;

   $problem = null;
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
         list($action, $item) = explode('-',$ActionLine);
         break;
      case "ACTION_{$dialog_id}_Save":
         list($action, $item) = explode('-',$ActionLine);
		 $onProfile = hda_db::hdadb()->HDA_DB_ReadProfile($item);
         $onProfile['Title'] = PRO_ReadParam("{$dialog_id}_Title");
         $onProfile['ItemText'] = PRO_ReadParam("{$dialog_id}_ItemText");
         $onProfile['Category'] = PRO_ReadParam("{$dialog_id}_Category");
		 $onProfile['CreatedBy'] = PRO_ReadParam("{$dialog_id}_Owner");
         if (!hda_db::hdadb()->HDA_DB_WriteProfile($onProfile)) $problem = "Problem saving profile {$onProfile['Title']}";
         else $problem = "Saved changes for profile";
         break;
      }
   $onProfile = hda_db::hdadb()->HDA_DB_ReadProfile($item);
   $t = _makedialoghead($dialog_id, "Update Profile");
   if (!is_null($problem)) $t .= "<tr><th style=\"color:red;\" colspan=3>{$problem}</th></tr>";
   $t .= "<tr><th>Profile Name:</th><td colspan=2><input type=\"text\" {$key_mouse} class=\"alc-dialog-name\" name=\"{$dialog_id}_Title\" value=\"{$onProfile['Title']}\" ></td></tr>";
   $t .= "<tr><th>Profile Description:</th><td colspan=2><textarea class=\"alc-dialog-text\" name=\"{$dialog_id}_ItemText\" value=\"\"  wrap=off >{$onProfile['ItemText']}</textarea></td></tr>";
   $struct = hda_db::hdadb()->HDA_DB_admin('Structure');
   if (!is_null($struct)) {
      $struct = hda_db::hdadb()->HDA_DB_unserialize($struct);
      if (is_array($struct) && count($struct)>0) {
         $t .= "<tr><th>Profile Category:</th><td colspan=2>";
         $t .= "<select name=\"{$dialog_id}_Category\" >";
         $selected = (is_null($onProfile['Category']) || strlen($onProfile['Category'])==0)?"SELECTED":"";
         $t .= "<option value=\"\" {$selected}>Not Specified</option>";
         foreach ($struct as $struct_item) {
            $selected = ($onProfile['Category']==$struct_item)?"SELECTED":"";
            $t .= "<option value=\"{$struct_item}\" {$selected}>{$struct_item}</option>";
            }
         $t .= "</select>";
         $t .= "</td></tr>";
         }
      }
   $t .= "<tr><th>Profile Owner:</th><td><select name=\"{$dialog_id}_Owner\" >";
   $a = hda_db::hdadb()->HDA_DB_AllUsers();
   if (is_array($a)) {
      foreach($a as $user) if (array_key_exists('ADMIN',$user['Allow'])&&$user['Allow']['ADMIN']==1) {
	     $selected = ($user['UserItem']==$onProfile['CreatedBy'])?"SELECTED":"";
		 $t .= "<option value=\"{$user['UserItem']}\" {$selected} >{$user['UserFullName']}</option>";
		 }
	  }
   $t .= "</select></td></tr>";

   $t .= "<tr><th colspan=3>";

   $t .= _closeDialog($dialog_id, "_Save-{$item}");
   $t .= "</th></tr>";
   $t .= _makedialogclose();

   return $t;
   }



function _dialogEditProcessPackage($dialog_id='alc_process_package') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;

   $process_item = null;
   $uploaded_code = null;
   $import = null;
   $problem = null;
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
         list($action, $process_item) = explode('-',$ActionLine);
         $uploaded_code = HDA_isUnique('PK');
         break;
      case "ACTION_{$dialog_id}_ImportedFile";
         list($action, $method, $uploaded_code, $up_path, $process_item) = explode('-',$ActionLine);
         $import = _actionImportFileDiv($dialog_id, $problem);
         PRO_AddToParams("{$process_item}_package", $import); 
         break;
      case "ACTION_{$dialog_id}_Again":
         list($action, $process_item) = explode('-',$ActionLine);
         $import = null;
         $uploaded_code = HDA_isUnique('PK');
         PRO_Clear(array("{$process_item}_package","{$process_item}_upid"));
         break;
      case "ACTION_{$dialog_id}_CancelUpload":
         list($action, $process_item, $uploaded_code) = explode('-',$ActionLine);
         $import = PRO_ReadParam("{$process_item}_package");
         PRO_Clear(array("{$process_item}_package","{$process_item}_upid"));
         if (!is_null($import))_rrmdir(pathinfo($import['UploadedPath'],PATHINFO_DIRNAME));
         $process_item = null;
         break;
      }
   if (is_null($process_item)) return "";
   $t = _makedialoghead($dialog_id,"Load and View Package");
   if (!is_null($import)) {
      $to_path = HDA_WorkingDirectory($process_item);
      switch (strtolower($import['Extension'])) {
         case 'zip':
            HDA_unzip($import['UploadedPath'], $process_item, $problem, $lib_dir = $to_path, $keep_dir=false);
            if (!is_null($problem)) $t .= "<tr><th colspan=3><span style=\"color:red;\">{$problem}</span></th></tr>";
            else $t .= "<tr><th colspan=3>Loaded {$import['Path']} ok</th></tr>";
            break;
         default:
            _cpp($import['UploadedPath'], $to_path);
            break;
         }
	  hda_db::hdadb()->HDA_DB_issueNote($process_item, "Uploaded process package\n{$import['Comment']}");

      $mouse = _click_dialog($dialog_id,"_Again-{$process_item}");
      $t .= "<tr><th colspan=3>Import another?&nbsp;";
      $t .= "<span class=\"push_button blue\" {$mouse}   >Again</span>"; 
	  $t .= "</th><tr>";
      }
   else {
      if (!is_null($problem)) $t .= "<tr><th colspan=3><span style=\"color:red;\">{$problem}</span></th></tr>";
      $t .= "<tr><th>Upload</th>";
      $t .= "<th colspan=2>"._insertImportFileDiv($dialog_id, $uploaded_code, "UploadPackage", $process_item)."</th></tr>"; 
      }
   $t .= "<tr><th>You can view package here:</th>";
   $mouse = _click_dialog("_dialogCustomPackageView","-{$process_item}");
   $t .= "<td colspan=2><span  title=\"View the package..\"  {$mouse} class=\"click-here\" >";
   $t .= "View:&nbsp;"._emit_image("Inspect.jpg",16);
   $t .= "</span>";
   $t .= "</td></tr>";
   $t .= _makedialogclose();


   return $t;
   }

function _dialogCustomPackageView($dialog_id='alc_package_view') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;

   $problem = null;
   $files = null;
   $process_item = null;
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
         list($action, $process_item) = explode('-',$ActionLine);
         $package_to = HDA_WorkingDirectory($process_item);
         $files = _lsdir($package_to);
         break;
      case "ACTION_{$dialog_id}_DeletePackage":
         list($action, $process_item) = explode('-',$ActionLine);
         $package_to = HDA_WorkingDirectory($process_item);
         _rrmdir($package_to);
         $package_to = HDA_WorkingDirectory($process_item);
         $files = _lsdir($package_to);
         break;
      case "ACTION_{$dialog_id}_DeleteFile":
         list($action, $process_item, $enc) = explode('-',$ActionLine);
         $path = base64_decode($enc);
         if (!@unlink($path)) $problem = "Failed to delete file {$path}";
         $package_to = HDA_WorkingDirectory($process_item);
         $files = _lsdir($package_to);
         break;
         
      }
   if (is_null($process_item)) return "";
   
   $t = _makedialoghead($dialog_id, "Viewing Package");
   if (!is_null($problem)) $t .= "<tr><th colspan=4 style=\"color:red;\" >{$problem}</th></tr>";
   $t .= "<tr><th colspan=4><div style=\"width:100%;height:200px;overflow-x:hidden;overflow-y:auto;border:none;\" ><table class=\"alc-table\" >";
   if (!is_null($files) && is_array($files) && count($files)>0) foreach($files as $file)  {
      $t .= "<tr><td colspan=1>".str_ireplace($package_to,'',$file['Path'])."</td>";
      $t .= "<td>{$file['Size']} bytes</td>";
      $t .= "<td>".hda_db::hdadb()->PRO_DBtime_Styledate($file['Modified'],true)."</td>";
      $t .= "<td>";
      $enc = base64_encode($file['Path']);
      $mouse = _click_dialog($dialog_id,"_DeleteFile-{$process_item}-{$enc}");
         $t .= "<span title=\"DELETE This File ..\" {$mouse} class=\"click-here\" style=\"color:red;\" >";
         $t .= _emit_image("DeleteThis.jpg",16);
         $t .= "</span>";
      $t .= "</td>";
      $t .= "</tr>";
      }
   $t .= "</table></div></th></tr>";
   if (count($files)==0) $t .= "<tr><th colspan=4 style=\"color:blue;\">Package is empty..</th></tr>";
   else {
      $t .= "<tr><th colspan=4>";
      $mouse = _click_dialog($dialog_id,"_DeletePackage-{$process_item}");
         $t .= "<span title=\"DELETE ALL ..\" {$mouse} class=\"click-here\" style=\"color:red;\" >";
         $t .= "Delete all package:&nbsp;";
         $t .= _emit_image("DeleteThis.jpg",24);
         $t .= "</span>";
      $t .= "</th></tr>";
      }
   $t .= _makedialogclose();


   return $t;
   }



?>