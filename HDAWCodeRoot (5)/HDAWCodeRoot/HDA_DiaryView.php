<?php

function _planViewDiary() {
   global $Action;
   global $ActionLine;
   global $DevUser;
   global $code_root, $home_root;
   global $post_load;
   global $_ViewHeight;

   
   $t = "";
   $t .= "<div id=\"planViewDiary\" class=\"dhx_cal_container\" style=\"width:100%;height:".($_ViewHeight-80)."px;overflow-x:hidden;overflow-y:hidden;\">";
		$t .= "<div class=\"dhx_cal_navline\">";
		$t .= "	<div class=\"dhx_cal_prev_button\">&nbsp;</div>";
		$t .= "	<div class=\"dhx_cal_next_button\">&nbsp;</div>";
		$t .= "	<div class=\"dhx_cal_today_button\"></div>";
		$t .= "	<div class=\"dhx_cal_date\"></div>";
		$t .= "	<div class=\"dhx_cal_tab\" name=\"day_tab\" style=\"right:204px;\"></div>";
		$t .= "	<div class=\"dhx_cal_tab\" name=\"week_tab\" style=\"right:140px;\"></div>";
		$t .= "	<div class=\"dhx_cal_tab\" name=\"month_tab\" style=\"right:76px;\"></div>";
		$t .= "</div>";
		$t .= "<div class=\"dhx_cal_header\">";
		$t .= "</div>";
		$t .= "<div class=\"dhx_cal_data\">";
		$t .= "</div>";
   $t .= "</div>";
   $t .= "<div id=\"diary_dialog\">";
   $t .= "</div>";

   _include_css("dhtmlxscheduler");
   _include_script(array("dhtmlxcommon","dhtmlxscheduler","dhtmlxdataprocessor","DiaryView"));
   $on = ($DevUser)?1:0;
   $post_load .= "runDiaryView({$on});";
   return $t;
   }
   
function _dialogDiaryEdit($dialog_id='alc_diary_edit') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $_DIARY_TAGS;

   $t = _makedialoghead($dialog_id, "Diary Event");
	$t .= "<tr><td>Event text </td><td><input type=\"text\" name=\"alc_diary_edit_description\" value=\"\" id=\"alc_diary_edit_description\"></td></tr>";
	$t .= "<tr><td>Details </td><td><input type=\"text\" name=\"alc_diary_edit_details\" value=\"\" id=\"alc_diary_edit_details\"></td></tr>";
	$t .= "<tr><td>Tagged As </td><td>";
	$t .= "<select name=\"alc_diary_edit_tag\" id=\"alc_diary_edit_tag\">";
	foreach ($_DIARY_TAGS as $k=>$va) {
	   $selected = ($k=='GEN_EVT')?"SELECTED":"";
	   $t .= "<option value=\"{$k}\" {$selected}>{$va[0]}</option>";
	   }
	$a = hda_db::hdadb()->HDA_DB_admin('BLOCKDATES');
    if (!is_null($a)) {
	   $a = hda_db::hdadb()->HDA_DB_unserialize($a);
	   foreach ($a as $i=>$p) $t .= "<option value=\"BLK_{$i}\" >{$p[0]}</option>";
	   }

	$t .= "<select>";
	$t .= "</td></tr>";
	$t .= "<tr><td>Starting </td><td><input type=\"text\" name=\"alc_diary_edit_start\" value=\"\" id=\"alc_diary_edit_start\"></td></tr>";
	$t .= "<tr><td>Ending </td><td><input type=\"text\" name=\"alc_diary_edit_end\" value=\"\" id=\"alc_diary_edit_end\"></td></tr>";
	$t .= "<tr><th colspan=2>";
	$t .= "<input type=\"button\" name=\"alc_diary_edit_save\" value=\"Save\" id=\"alc_diary_edit_save\" style='width:100px;' onclick=\"alc_diary_edit_save_form()\">";
	$t .= "<input type=\"button\" name=\"alc_diary_edit_close\" value=\"Close\" id=\"alc_diary_edit_close\" style='width:100px;' onclick=\"alc_diary_edit_close_form()\">";
	$t .= "<input type=\"button\" name=\"alc_diary_edit_delete\" value=\"Delete\" id=\"alc_diary_edit_delete\" style='width:100px;' onclick=\"alc_diary_edit_delete_event()\">";
    $t .= "</th></tr>";
   $t .= _makedialogclose();

   return $t;
   }
    
?>