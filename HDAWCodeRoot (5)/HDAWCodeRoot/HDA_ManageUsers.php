<?php
function HDA_ManageUsers() {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $UserName;
   global $UserCode;
   global $Tab_Menu;

   $problem = NULL;

   switch ($Action) {
      case 'ACTION_AD_DELUSER':
         list($action, $u) = explode('-',$ActionLine);
         if (isset($u) && strlen($u)>0) if (!hda_db::hdadb()->HDA_DB_RemoveUser($u)) $problem="Problem deleting user";
         break;
      case 'ACTION_AD_SETALLOW':
         list($action, $u, $what) = explode('-',$ActionLine);
         $uprofile = hda_db::hdadb()->HDA_DB_UserIsAllowed($u); 
         if (!isset($uprofile) || !is_array($uprofile)) $uprofile=array();
         $set_what = PRO_ReadAndClear("{$u}-{$what}");
         $uprofile[$what] = $set_what;
         hda_db::hdadb()->HDA_DB_writeUserAllow($u, $uprofile);
         break;
      case 'ACTION_AD_SETOPTION':
         list($action, $u, $what) = explode('-',$ActionLine);
         $options = hda_db::hdadb()->HDA_DB_GetUserOptions($u);
         $options[$what] = ($options[$what]+1)&1;
         hda_db::hdadb()->HDA_DB_writeUserOptions($u, $options);
         break;
      }


   $t = "";
   $Tab_Menu = "";
     
   $mouse = _click_dialog("_dialogInviteUser");
   $Tab_Menu .= "<span style=\"cursor:pointer;height:24px;\" title=\"Create a new user..\" {$mouse}>";
   $Tab_Menu .= _emit_image("AddUser.jpg",24)."</span>";
   $mouse = _click_dialog("_dialogImportUserlist");
   $Tab_Menu .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Import user list..\" {$mouse}>";
   $Tab_Menu .= _emit_image("ImportThis.jpg",24)."</span>";
   $mouse = _click_dialog("_dialogExportUserlist");
   $Tab_Menu .= "&nbsp;&nbsp;<span  title=\"Export user list..\"  {$mouse}>";
   $Tab_Menu .= _emit_image("Export.jpg",24)."</span>";
   $mouse = _click_dialog("_dialogExportAccesslist");
   $Tab_Menu .= "&nbsp;&nbsp;<span  title=\"Export user access list..\"  {$mouse}>";
   $Tab_Menu .= _emit_image("BulkRun2.jpg",24)."</span>";
   $mouse = _click_dialog("_dialogBulkTickets");
   $Tab_Menu .= "&nbsp;&nbsp;<span  title=\"Issue Tickets in Bulk..\"  {$mouse}>";
   $Tab_Menu .= _emit_image("Trust.jpg",24);
   $Tab_Menu .= "</span>";

   $a = hda_db::hdadb()->HDA_DB_AllUsers();
   if (isset($a) && is_array($a) && count($a)>0) {
      if (!is_null($problem)) $t .= "<tr><th colspan=5 style=\"color:red;\">{$problem}</th></tr>";
      $t .= "<tr><th>Email</th><th>Name</th><th>Allow Updates - has Developer Rights</th><th>Custom Access</th><th>&nbsp;</th></tr>";
      foreach ($a as $row) {
	     PRO_Clear(array("{$row['UserItem']}-ADMIN","{$row['UserItem']}-MONITOR","{$row['UserItem']}-APPER","{$row['UserItem']}-UPLOAD"));
         $t .= "<tr><td>{$row['Email']}</td><td>{$row['UserFullName']}</td>";
         $allows = $row['Allow'];
         $is_admin = array_key_exists('ADMIN',$allows) && $allows['ADMIN']==1;
         $checked = ($is_admin)?"CHECKED":NULL;
         $mouse = "onclick=\"issuePost('AD_SETALLOW-{$row['UserItem']}-ADMIN');return false;\" ";
         $t .= "<td><input type=\"checkbox\" name=\"{$row['UserItem']}-ADMIN\" value=\"1\"  {$mouse} {$checked}></td>";
		 		 
		 $mouse = _click_dialog("_dialogCustomAccess","-{$row['UserItem']}");
         $t .= "<td><span class=\"click-here\" title=\"Custom access\" {$mouse} >[ Select Access ";
         $t .= _emit_image("Edit.jpg",16)." ]</span></td>";

         $t .= "<td>";
         $mouse = "onclick=\"issuePost('AD_DELUSER-{$row['UserItem']}-USERS');return false;\" ";
         $t .= "<span title=\"Delete this user\" {$mouse} >";
         $t .= _emit_image("DeleteThis.jpg",16)."</span>";
         $t .= "</td>";
         $t .= "</tr>";
         }
      }
   else $t .= "<tr><th colspan=5>No Users</th></tr>";
   

   return $t;
   }
   
function _dialogCustomAccess($dialog_id='alc_custom_user_access') {
   global $Action;
   global $ActionLine;
   global $UserName;
   global $UserCode;
   $problem = null;
   $item = null;
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}":
	     list($action, $item) = explode('-',$ActionLine);
         break;
	  case "ACTION_{$dialog_id}_Save":
	     list ($action, $item) = explode('-', $ActionLine);
         $allow = hda_db::hdadb()->HDA_DB_UserIsAllowed($item);
		 $allow['USER_TABS'] = PRO_ReadParam("AllowTab-{$item}");
		 hda_db::hdadb()->HDA_DB_writeUserAllow($item, $allow);
		 break;
      }
   $t = _makedialoghead($dialog_id, "Select Custom Access for User");
   if (!is_null($problem)) $t .= "<tr><th style=\"color:red;\" colspan=2>{$problem}</th></tr>";
   $allow = hda_db::hdadb()->HDA_DB_UserIsAllowed($item);
   if (array_key_exists('ADMIN', $allow)&&($allow['ADMIN']==1)) $t .= "<tr><th colspan=2>User is Admin</th></tr>";
   $tab_list = _allTabList();
   $user_tabs = (array_key_exists('USER_TABS', $allow)&&!is_null($allow['USER_TABS'])&&is_array($allow['USER_TABS']))?$allow['USER_TABS']:array();
   foreach ($tab_list as $tab_key=>$tab) {
      $checked = (in_array($tab_key, $user_tabs))?"CHECKED":"";
      $t .= "<tr><td>{$tab[0]}</td><td><input type=\"checkbox\" name=\"AllowTab-{$item}[]\" value=\"{$tab_key}\" {$checked}></td></tr>";
	  }
   $mouse = _click_dialog($dialog_id,"_Save-{$item}");
   $t .= "<tr><th colspan=2>";
   $t .= "<span class=\"push_button blue\" {$mouse}   >Submit</span>"; 
   $t .= "</th></tr>";
   $t .= _makedialogclose();

   return $t;
   }
	  
   
function _dialogBulkTickets($dialog_id='alc_bulk_tickets') {
   global $Action;
   global $ActionLine;
   global $UserName;
   global $UserCode;
   $problem = null;
   $sent = "";
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}":
         break;
	  case "ACTION_{$dialog_id}_ClearProfiles":
	     PRO_Clear("{$dialog_id}_Profiles");
		 break;
	  case "ACTION_{$dialog_id}_IssueTickets":
	     $user = PRO_ReadParam("{$dialog_id}_User");
		 $user = hda_db::hdadb()->HDA_DB_FindUser($user);
		 if (!is_array($user) || count($user)<>1) $problem = "Select a user";
		 else {
		    $user = $user[0];
		    $username = $user['UserFullName'];
		    $email = $user['Email'];
			$profiles = PRO_ReadParam("{$dialog_id}_Profiles");
			if (!is_array($profiles) || count($profiles)==0) $problem = "Select profiles";
			else {
			   foreach ($profiles as $item) {
			      $profile = hda_db::hdadb()->HDA_DB_ReadProfile($item);
				  if (is_null($profile)) $problem = "Unable to get details of profile";
				  else {
		             $instructions = $profile['ItemText'];
		             $profile_title = $profile['Title'];
		             $ticket = hda_db::hdadb()->HDA_DB_makeTicket($item, $username, $email, $instructions);
			         if (!is_null($ticket)) {
			            $attach = array();
			            $xticket_file = _makeFTPTicketFile($ticket, $item, $username, $instructions);
                        }
			         else $problem = "Fails to generate ticket for {$profile_title}";
		             }
		          _sendTicket($ticket, $item, $ss);
				  $sent .= "{$ss}<br>";
				  }
			   }
			}
		 break;
      }
   $t = _makedialoghead($dialog_id, "Issue Multiple Tickets to User");
   if (!is_null($problem)) $t .= "<tr><th style=\"color:red;\">{$problem}</th></tr>";
   if (strlen($sent)>0) {
      $t .= "<tr><td><div style=\"width:100%;height:80px;overflow:auto;\">{$sent}</div></td></tr>";
	  }
   $a = hda_db::hdadb()->HDA_DB_AllUsers();
   $t .= "<tr><th>Select User:</th></tr>";
   $t .= "<tr><th><select name=\"{$dialog_id}_User\" value=\"\">";
   $t .= "<option value=null>No User Selected</option>";
   foreach($a as $row) {
      $t .= "<option value=\"{$row['UserItem']}\">{$row['Email']} - {$row['UserFullName']}</option>";
      }
   $t .= "</select></th></tr>";
   $t .= "<tr><th>Select Profiles for Tickets";
   $profiles = PRO_ReadParam("{$dialog_id}_Profiles");
   if (!is_array($profiles)) $profiles = array();
   if (count($profiles)>0) {
      $mouse = _click_dialog($dialog_id,"_ClearProfiles");
      $t .= "&nbsp;&nbsp;<span class=\"push_button blue\" {$mouse}   >Clear Selection</span>"; 
	  }
   $t .= "</th></tr>";
   $a = hda_db::hdadb()->HDA_DB_profileNames();
   $t .= "<tr><td><div style=\"width:100%;height:200px;overflow:auto;\">";
   foreach ($a as $item=>$name) {
      $checked = (in_array($item, $profiles))?"CHECKED":"";
      $t .= "<input type=\"checkbox\" name=\"{$dialog_id}_Profiles[]\" {$checked} value=\"{$item}\" >&nbsp;{$name}<br>";
      }
   $t .= "</div></td></tr>";
   $mouse = _click_dialog($dialog_id,"_IssueTickets");
   $t .= "<tr><th>";
   $t .= "<span class=\"push_button blue\" {$mouse}   >Issue Tickets</span>"; 
   $t .= "</th></tr>";
   $t .= _makedialogclose();

   return $t;
   }

function _dialogInviteUser($dialog_id='alc_invite_user') {
   global $Action;
   global $ActionLine;
   global $UserName;
   global $UserCode;
   $problem = null;
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}":
         break;
      case "ACTION_{$dialog_id}_SENDINVITE":
         $send_to = PRO_ReadAndClear("{$dialog_id}_NEW_EMAIL");
         $named_as = PRO_ReadAndClear("{$dialog_id}_NEW_NAME");
         $with_msg = PRO_ReadAndClear("{$dialog_id}_NEW_MSG");
         $problem = HDA_Invite($send_to, $named_as, $UserName, $with_msg);
         if (is_null($problem)) {
            $problem = "Invitation has been sent to {$named_as} on address {$send_to}";
            }
         break;
      }
   $t = _makedialoghead($dialog_id, "Invite New User");
   if (!is_null($problem)) $t .= "<tr><th colspan=2 style=\"color:red;\" >{$problem}</th></tr>";
   $t .= "<tr><th>Users email:</th><td><input type=\"text\" class=\"alc-dialog-name\" name=\"{$dialog_id}_NEW_EMAIL\" value=\"\" ></td></tr>";
   $t .= "<tr><th>Users name:</th><td><input type=\"text\" class=\"alc-dialog-name\" name=\"{$dialog_id}_NEW_NAME\" value=\"\" ></td></tr>";
   $t .= "<tr><th>Include message:</th><td><textarea name=\"{$dialog_id}_NEW_MSG\" style=\"width:300px;height:200px;\" ></textarea></td></tr>";
   $mouse = _click_dialog($dialog_id,"_SENDINVITE");
   $t .= "<tr><th colspan=2>";
   $t .= "<span class=\"push_button blue\" {$mouse}   >Send Invite Now</span>"; 
   $t .= "</th></tr>";
   $t .= _makedialogclose();

   return $t;
   }

function _dialogImportUserlist($dialog_id='alc_import_userlist') {
   global $Action;
   global $_Mobile;
   global $code_root, $home_root;
   $problem = null;
   $tt = null;
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
         break;
      case "ACTION_{$dialog_id}_UploadList":
         $problem = HDA_UploadUsers($up_path='UploadUsers', $into_file);
         if (is_null($problem)) {
            $xml = file_get_contents($into_file);
            $a = xml2ary($xml);
            $users = _query('Users', $a, 'UserList');
            if (is_null($users) || !is_array($users)) $problem = "Unexpected layout in xml for a user list";
            else {
               $tt = ""; $allow = array();
               foreach ($users as $user) {
                  $tt .= "{$user[0]} ";
                  foreach ($user[1] as $k=>$p) {
                     switch ($k) {
                        case 'name':
                        case 'pw':
                           $tt .= "{$k}=>{$p} ";
                           break;
                        default:
                           $allow[strtoupper($k)] = ($p=='yes')?"1":"0"; 
                           $tt .= "{$k}=>"; $tt .= ($p=='yes')?"1":"0"; $tt .= " ";
                           break;
                        }
                     }
                  if (!hda_db::hdadb()->HDA_DB_InsertUser($user[0], $user[1]['name'], $user[1]['pw'], $allow)) {
                     $tt .= "- already exists, skipped - ";
                     }
                  else $tt .= "ADDED USER";
                  $tt .= "\n";
                  }
               }
            }
         break;
      }

   $t = _makedialoghead($dialog_id, "Import User List");
   if (!is_null($problem)) $t .= "<tr><th style=\"color:red;\" >{$problem}</th></tr>";
   if (is_null($tt)) {
      $t .= "<tr><th>Import list:</th><td colspan=2><input type=\"file\" name=\"UploadUsers\" \" value=\"\" ></td></tr>";
      $mouse = _click_dialog($dialog_id,"_UploadList");
      $t .= "<tr><th colspan=3>";
      $t .= "<span class=\"push_button blue\" {$mouse}   >Upload User List</span>"; 
	  $t .= "</th></tr>";
      }
   else {
      $t .= "<tr><td colspan=3><textarea class=\"alc-dialog-text\" style=\"height:300px;\" >{$tt}</textarea></td></tr>";
      }
   $t .= _makedialogclose();

   return $t;
   }

function _dialogExportUserlist($dialog_id='alc_export_userlist') {
   global $Action;
   global $_Mobile;
   global $code_root, $home_root;
   $problem = null;
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
         break;
      }
   $t = _makedialoghead($dialog_id, "Export User List");
   $xml = "";
   $xml .= "<Users>\n";
   $xml .= "  <UserList>\n";
   $a = hda_db::hdadb()->HDA_DB_AllUsers();
   if (isset($a) && is_array($a) && count($a)>0) {
      foreach ($a as $row) {
         $xml .= "   <User ";
         $xml .= "name=\"{$row['UserFullName']}\" ";
         $xml .= "pw=\"{$row['PW']}\" ";
         $xml .= "admin=\"";$xml .= (array_key_exists('ADMIN',$row['Allow']))?"yes":"no"; $xml .= "\" ";
         $xml .= " >{$row['Email']}</User>\n";
         }
      }
   $xml .= "  </UserList>\n";
   $xml .= "</Users>\n";
   $t .= "<tr><th colspan=3><textarea class=\"alc-dialog-text\" style=\"height:200px;\" wrap=off >{$xml}</textarea></th></tr>";
   $lib_dir = "tmp/";
   $lib_path = "{$lib_dir}/userlist.xml";
   @file_put_contents($lib_path, $xml);_chmod($lib_path);
   $t .= "<tr><th colspan=3>";
   //$t .= "<a href=\"{$lib_path}\" target=\"_blank\" >Download</a>";
   $t .= "<a href=\"HDAW.php?load=HDA_DownLoadFile&file={$lib_path}\" target=\"_blank\">Download</a>";
   $t .= "</th></tr>";

   $t .= _makedialogclose();

   return $t;
   }
   

function _dialogExportAccesslist($dialog_id='alc_export_accesslist') {
   global $Action;
   global $_Mobile;
   global $code_root, $home_root;
   $problem = null;
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
         break;
      }
   $t = _makedialoghead($dialog_id, "Export Permission List");
   $csv = "";
   $csv .= "UserName,EMail,IsAdmin,AllowTabs\r\n";
   $a = hda_db::hdadb()->HDA_DB_AllUsers();
  $tab_list = _screenTabList($what_tab);
  if (isset($a) && is_array($a) && count($a)>0) {
      foreach ($a as $row) {
         $csv .= "\"{$row['UserFullName']}\",";
         $csv .= "\"{$row['Email']}\",";
         $csv .= (array_key_exists('ADMIN',$row['Allow']))?"\"yes\",":"\"no\",";
		 $csv .= "\"";
		 if (array_key_exists('USER_TABS',$row['Allow']) && is_array($row['Allow']['USER_TABS'])) 
			 foreach ($row['Allow']['USER_TABS'] as $allowTab) {
			 if (array_key_exists($allowTab, $tab_list)) {
				$tab = $tab_list[$allowTab][0];
				$csv .= "{$tab};";
			 }
		 }
		 $csv = trim($csv,";"); $csv .= "\"\r\n";
         }
      }
   $t .= "<tr><th colspan=3><textarea class=\"alc-dialog-text\" style=\"height:200px;\" wrap=off >{$csv}</textarea></th></tr>";
   $lib_dir = "tmp/";
   $lib_path = "{$lib_dir}/accesslist.csv";
   @file_put_contents($lib_path, $csv);_chmod($lib_path);
   $t .= "<tr><th colspan=3>";
   //$t .= "<a href=\"{$lib_path}\" target=\"_blank\" >Download</a>";
   $t .= "<a href=\"HDAW.php?load=HDA_DownLoadFile&file={$lib_path}\" target=\"_blank\">Download</a>";
   $t .= "</th></tr>";

   $t .= _makedialogclose();

   return $t;
   }
   





?>