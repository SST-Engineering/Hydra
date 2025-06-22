<?php

// Dialog Builder & Common
function _getdialogsizes($class, $inset_wd=0, $inset_ht=0) {
   global $_ViewWidth;
   global $_ViewHeight;
   $d = array();
   switch ($class) {
      case "alc-dialog-vlarge":
	  case "alc-dialog-large":
	     $d['WD_I'] = $wd_i = $_ViewWidth-50-$inset_wd;
		 $d['WD'] = $wd = "{$wd_i}px";
		 $d['HT_I'] = $ht_i = $_ViewHeight-10-$inset_ht;
		 $d['HT'] = $ht = "{$ht_i}px";
		 $d['TOP'] = $top = "25";
		 $d['LEFT'] = $left = "25";
         break;
	  case "alc-dialog-max":
	     $d['WD_I'] = $wd_i = $_ViewWidth-$inset_wd;
	     $d['WD'] = $wd = "{$wd_i}px"; //"100%";
		 $d['HT'] = $ht = "100%";
		 $d['TOP'] = $top = "0";
		 $d['LEFT'] = $left = "0";
	     break;
	  case "alc-dialog-wide":
	     $d['WD_I'] = $wd_i = $_ViewWidth-50-$inset_wd;
		 $d['WD'] = $wd = "{$wd_i}px";
		 $d['HT'] = $ht = "auto";
		 $d['TOP'] = $top = $_ViewHeight/8;
		 $d['LEFT'] = $left = "10";
	     break;
	  case "alc-dialog-halfwide":
	     $d['WD_I'] = $wd_i = $_ViewWidth/2 - $inset_wd;
		 $d['WD'] = $wd = "{$wd_i}px";
		 $d['HT'] = $ht = "auto";
		 $d['TOP'] = $top = $_ViewHeight/8;
		 $d['LEFT'] = $left = $_ViewWidth/4;
	     break;
	  case "alc-dialog-small":
	  case "alc-dialog-small-viz":
	     $d['WD_I'] = $wd_i = $_ViewWidth/6 - $inset_wd;
		 $d['WD'] = $wd = "{$wd_i}px";
		 $d['HT'] = $ht = "auto";
		 $d['TOP'] = $top = $_ViewHeight/2;
		 $d['LEFT'] = $left = $_ViewWidth/8;
	     break;
      default:
         $d['WD_I'] = $wd_i = $_ViewWidth - ($_ViewWidth/2) - $inset_wd;
		 $d['WD'] = $wd = "{$wd_i}px";
         $d['HT'] = $ht = "auto";
         $d['TOP'] = $top = $_ViewHeight/8;
         $d['LEFT'] = $left = $_ViewWidth/4;
		 break;
      }
   return $d;
   }
function _makedialoghead($dialog_id, $title, $class="", $can_close = true) {
   global $code_root, $home_root;
   global $key_mouse;
   global $_ViewWidth;
   global $_ViewHeight;
   $t = "";
   $d = _getdialogsizes($class);
   $wd_i = $d['WD_I'];
   $wd = $d['WD'];
   $ht = $d['HT'];
   $top = $d['TOP'];
   $left = $d['LEFT'];

   $t .= "<div id=\"{$dialog_id}\" class=\"hda-dialog {$class}\" style=\"width:{$wd};height:{$ht};top:{$top}px;left:{$left}px;overflow:hidden;\" >";
   $t .= "<div class=\"hda-dialog-top\" id=\"{$dialog_id}_drag\" >";
   if ($can_close) {
      $mouse = "onclick=\"HDA_HideDialog('{$dialog_id}'); return false;\" ";
	  $style = "float:  right; margin: 4px 5px 0px 0px; cursor: pointer;";
      $t .= _emit_image("CloseDialog.jpg",12,$mouse,"style=\"{$style}\"  id=\"{$dialog_id}_exit\" ", "hda-dialog-close");
      }
   $t .= "{$title}";
   $t .= "</div>";
   $wd_i -= 32;
   $t .= "<div style=\"width:{$wd};height:auto;overflow:auto;\" >";
   $t .= "<table class=\"alc-table\" style=\"width:{$wd_i}px;\">";

   return $t;
   }
function _makedialogclose() {
   $t = "";
   $t .= "</table>";
   $t .= "</div></div>";
   return $t;
   }
   
function _closeDialog($dialog_id, $save=false, $colspan=1) {
   global $code_root, $home_root;
   global $key_mouse;

   $t = "";
   if ($save !== false) {
	   $t .= "<tr><th colspan={$colspan} class=\"buttons\" style=\"background-color:white;\" >";
      $mouse = _click_dialog($dialog_id, ($save===true)?"_Save":$save);
	  $t .= "<div class=\"hda-mask-btn\" >";
      $t .= "<div title=\"Save\" {$mouse} class=\"push_button blue\"  >Save Changes</div>";
	  $t .= "</div>";
	  $t .= "</th></tr>";
      }
   return $t;
   }
   
function _dialogUserDiaryNotice($events, $dialog_id = '_dialogUserDiaryNotice') {
   $t = _makedialoghead($dialog_id, "Diary Events", 'alc-dialog-viz');
   $t .= "<tr><td>";
   $t .= "<div style=\"width:100%;height:200px;overflow-x:hidden;overflow-y:auto;text-align:left;color:blue;\">";
   foreach($events as $msg) $t .= "{$msg}<br>";
   $t .= "</div>";
   $t .= "</td></tr>";
   $t .= _makedialogclose();
   return $t;
   }
   
function _makepopup($name, $class, $inner, $titled='Code Helper') {
   global $code_root, $home_root;
   global $key_mouse;
   $t = "";
   $t .= "<div id=\"$name\" class=\"alc-dialog-popup {$class}\" style=\"display: none;\">";
   $t .= "<div class=\"alc-display-box\" id=\"{$name}_drag\">";
   $mouse = "onclick=\"HDA_HideDialog('{$name}'); return false;\" ";
   $t .= _emit_image("CloseDialog.jpg", null, $mouse, "class=\"alc-dialog-popup-exit\"  id=\"{$name}_exit\" ");
   $t .= "&nbsp;&nbsp;&nbsp;{$titled}";
   $t .= "</div>";
   $t .= $inner;
   $t .= "</div>";
   return $t;
   }


function _schedule_inner($dialog_id, $onWhat) {
   global $Action;
   global $code_root, $home_root;
   global $key_mouse;
   global $_validFrequencies;
   $pattern = PRO_ReadParam("{$dialog_id}_pattern");
//   if (is_null($pattern) || strlen($pattern)==0) {
      $sch_w_day = PRO_ReadParam("{$dialog_id}_sch_w_day");
      if (is_null($sch_w_day)) $sch_w_day = 1;
      $sch_n_day = PRO_ReadParam("{$dialog_id}_sch_n_day");
      if (is_null($sch_n_day)) $sch_n_day = 1;
      $sch_n_week = PRO_ReadParam("{$dialog_id}_sch_n_week");
      if (is_null($sch_n_week)) $sch_n_week = 1;
      $sch_on_month = PRO_ReadParam("{$dialog_id}_sch_on_month");
      if (is_null($sch_on_month)) $sch_on_month = "on";
      $sch_nd_month = PRO_ReadParam("{$dialog_id}_sch_nd_month");
      if (is_null($sch_nd_month)) $sch_nd_month = 1;
      $sch_n_month = PRO_ReadParam("{$dialog_id}_sch_n_month");
      if (is_null($sch_n_month)) $sch_n_month = 1;
      $sch_rn_month = PRO_ReadParam("{$dialog_id}_sch_rn_month");
      if (is_null($sch_rn_month)) $sch_rn_month = 1;
      $sch_st_month = PRO_ReadParam("{$dialog_id}_sch_st_month");
      if (is_null($sch_st_month)) $sch_st_month = 1;
      $sch_dow_month = PRO_ReadParam("{$dialog_id}_sch_dow_month");
      if (is_null($sch_dow_month)) $sch_dow_month = 1;
      $sch_on_year = PRO_ReadParam("{$dialog_id}_sch_on_year");
      if (is_null($sch_on_year)) $sch_on_year = "on";
      $sch_md_year = PRO_ReadParam("{$dialog_id}_sch_md_year");
      if (is_null($sch_md_year)) $sch_md_year = 1;
      $sch_m_year = PRO_ReadParam("{$dialog_id}_sch_m_year");
      if (is_null($sch_m_year)) $sch_m_year = 1;
      $sch_dow_year = PRO_ReadParam("{$dialog_id}_sch_dow_year");
      if (is_null($sch_dow_year)) $sch_dow_year = 1;
      $sch_std_year = PRO_ReadParam("{$dialog_id}_sch_std_year");
      if (is_null($sch_std_year)) $sch_std_year = 1;
      $sch_moy_year = PRO_ReadParam("{$dialog_id}_sch_moy_year");
      if (is_null($sch_moy_year)) $sch_moy_year = 1;
      $sch_rmoy_year = PRO_ReadParam("{$dialog_id}_sch_rmoy_year");
      if (is_null($sch_rmoy_year)) $sch_rmoy_year = 1;
 /*     }
   else {
      list($sch_w_day,$sch_n_day,$sch_n_week,
           $sch_on_month,$sch_n_month,$sch_nd_month,$sch_rn_month,$sch_dow_month,$sch_st_month,
           $sch_on_year,$sch_m_year,$sch_md_year,$sch_dow_year,$sch_std_year,$sch_moy_year,$sch_rmoy_year) = explode(',',$pattern);
      }*/
   $pattern_dow = PRO_ReadParam("{$dialog_id}_pattern_dow");
   if (is_null($pattern_dow) || strlen($pattern_dow)==0) {
      $sch_d_week = PRO_ReadParam("{$dialog_id}_sch_d_week");
      if (is_null($sch_d_week) || !is_array($sch_d_week) || count($sch_d_week)==0) $sch_d_week = array(1);
      }
   else {
      $sch_d_week = explode(',',$pattern_dow);
      }
   $t = "";
   $sch_freq = PRO_ReadParam("{$dialog_id}_sch_freq");
   if (is_null($sch_freq)) $sch_freq = 'WEEKLY';
   $sch_end = PRO_ReadParam("{$dialog_id}_sch_end");
   if (is_null($sch_end)) $sch_end = "none";
   $sch_end_occurs = PRO_ReadParam("{$dialog_id}_sch_end_occurs");
   if (is_null($sch_end_occurs)) $sch_end_occurs = 1;
   $sch_end_date = PRO_ReadParam("{$dialog_id}_sch_end_date");
   if (is_null($sch_end_date)) $sch_end_date = date('2020-m-d G:i',time());
   $sch_set_start = PRO_ReadParam("{$dialog_id}_sch_set_start");
   if (is_null($sch_set_start)) $sch_set_start = date('Y-m-d G:i',time());
   $t .= "<div>";
   _include_css(array("dhtmlxcalendar","dhtmlxcalendar_dhx_skyblue"));
   _include_script("dhtmlxcalendar");

   $t .= "<table class=\"alc-table\"><colgroup span=3><col width=\"120\"><col width=\"*\"><col width=\"150\"></colgroup>";
   $t .= "<tr>";
   $t .= "<td>";
   $mouse=_click_dialog($dialog_id,"_Refresh-{$onWhat}");
   $checked = ($sch_freq=='ANY')?"CHECKED":"";
   $t .= "<input class=\"intext\" type=\"radio\" name=\"{$dialog_id}_sch_freq\" value=\"ANY\" {$checked} {$mouse} >&nbsp;{$_validFrequencies['ANY']}<br>";
   $checked = ($sch_freq=='HOURLY')?"CHECKED":"";
   $t .= "<input class=\"intext\" type=\"radio\" name=\"{$dialog_id}_sch_freq\" value=\"HOURLY\" {$checked} {$mouse} >&nbsp;{$_validFrequencies['HOURLY']}<br>";
   $checked = ($sch_freq=='DAILY')?"CHECKED":"";
   $t .= "<input class=\"intext\" type=\"radio\" name=\"{$dialog_id}_sch_freq\" value=\"DAILY\" {$checked} {$mouse} >&nbsp;{$_validFrequencies['DAILY']}<br>";
   $checked = ($sch_freq=='WEEKLY')?"CHECKED":"";
   $t .= "<input class=\"intext\" type=\"radio\" name=\"{$dialog_id}_sch_freq\" value=\"WEEKLY\" {$checked} {$mouse} >&nbsp;{$_validFrequencies['WEEKLY']}<br>";
   $checked = ($sch_freq=='MONTHLY')?"CHECKED":"";
   $t .= "<input class=\"intext\" type=\"radio\" name=\"{$dialog_id}_sch_freq\" value=\"MONTHLY\" {$checked} {$mouse} >&nbsp;{$_validFrequencies['MONTHLY']}<br>";
   $checked = ($sch_freq=='YEARLY')?"CHECKED":"";
   $t .= "<input class=\"intext\" type=\"radio\" name=\"{$dialog_id}_sch_freq\" value=\"YEARLY\" {$checked} {$mouse} >&nbsp;{$_validFrequencies['YEARLY']}<br>";
   $t .= "</td>";
   $t .= "<td>";
   $dow = array(1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday');
   $moy = array(1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December');
   $post_d = array(); for ($i=1; $i<32; $i++) $post_d[$i] = 'th';
   $post_d[1] = $post_d[21] = $post_d[31] = 'st'; $post_d[2] = $post_d[22] = 'nd'; $post_d[3] = $post_d[23] = 'rd';
   switch ($sch_freq) {
      case 'DAILY':
         PRO_Clear("{$dialog_id}_sch_w_day");
         $checked = ($sch_w_day==0)?"CHECKED":"";
         $t .= "<input class=\"intext\" type=\"radio\" name=\"{$dialog_id}_sch_w_day\" value=\"0\" {$checked}>&nbsp;";
         $t .= "Every&nbsp;<input class=\"intext\" type=\"text\" {$key_mouse} name=\"{$dialog_id}_sch_n_day\" value=\"{$sch_n_day}\" style=\"width:20px;\">&nbsp;days<br>";
         $checked = ($sch_w_day==1)?"CHECKED":"";
         $t .= "<input class=\"intext\" type=\"radio\" name=\"{$dialog_id}_sch_w_day\" value=\"1\" {$checked}>&nbsp;Every workday<br>";
         break;
      case 'WEEKLY':
         $t .= "Repeat every&nbsp;<input class=\"intext\" type=\"text\" {$key_mouse}  name=\"{$dialog_id}_sch_n_week\" value=\"{$sch_n_week}\" style=\"width:20px;\">";
         $t .= "&nbsp;week on these days:<br>";
         foreach($dow as $nday=>$day) {
            $checked = (in_array($nday,$sch_d_week))?"CHECKED":"";
            $t .= "<span style=\"white-space:nowrap;\">";
            $t .= "&nbsp;<input class=\"intext\" type=\"checkbox\" name=\"{$dialog_id}_sch_d_week[]\"  value=\"{$nday}\" {$checked} >&nbsp;{$day}";
            $t .= "</span> ";
            }
         break;
      case 'MONTHLY':
         $checked = ($sch_on_month=='repeat')?"CHECKED":"";
         $t .= "<input class=\"intext\" type=\"radio\" name=\"{$dialog_id}_sch_on_month\" value=\"repeat\" {$checked}>&nbsp;";
         $t .= "Repeat&nbsp;on&nbsp;<input class=\"intext\" type=\"text\" {$key_mouse}  name=\"{$dialog_id}_sch_nd_month\" value=\"{$sch_nd_month}\" style=\"width:20px;\">";
         $t .= "&nbsp;day&nbsp;of&nbsp;every&nbsp;<input class=\"intext\" type=\"text\" {$key_mouse}  name=\"{$dialog_id}_sch_n_month\" value=\"{$sch_n_month}\" style=\"width:20px\">";
         $t .= "&nbsp;month<br>";
         $t .= "<center><span style=\"color:blue;\">__________ or ___________</span></center><br>";
         $checked = ($sch_on_month=='on')?"CHECKED":"";
         $t .= "<input class=\"intext\" type=\"radio\" name=\"{$dialog_id}_sch_on_month\" value=\"on\" {$checked}>&nbsp;";
         $t .= "On&nbsp;<input class=\"intext\" type=\"text\"  {$key_mouse} name=\"{$dialog_id}_sch_st_month\" value=\"{$sch_st_month}\" style=\"width:20px;\">&nbsp;";
         $t .= "<select class=\"intext\" name=\"{$dialog_id}_sch_dow_month\" >";
         foreach ($dow as $nday=>$day) {
            $selected = ($sch_dow_month==$nday)?"SELECTED":"";
            $t .= "<option value=\"{$nday}\" {$selected}>{$day}</option>";
            }
         $t .= "</select>";
         $t .= "&nbsp;every&nbsp;<input class=\"intext\" type=\"text\" {$key_mouse}  name=\"{$dialog_id}_sch_rn_month}\" value=\"{$sch_rn_month}\" style=\"width:20px;\">";
         $t .= "&nbsp;month";
         break;
      case 'YEARLY':
         $checked = ($sch_on_year=='repeat')?"CHECKED":"";
         $t .= "<input class=\"intext\" type=\"radio\" name=\"{$dialog_id}_sch_on_year\" value=\"repeat\" {$checked}>&nbsp;";
         $t .= "Every&nbsp;<input class=\"intext\" type=\"text\" {$key_mouse}  name=\"{$dialog_id}_sch_md_year\" value=\"{$sch_md_year}\" style=\"width:20px;\">";
         $t .= "&nbsp;day&nbsp;of&nbsp;";
         $t .= "<select class=\"intext\" name=\"{$dialog_id}_sch_m_year\" >";
         foreach ($moy as $nm=>$month) {
            $selected = ($nm==$sch_m_year)?"SELECTED":"";
            $t .= "<option value=\"{$nm}\" {$selected}>{$month}</option>";
            }
         $t .= "</select>";
         $t .= "&nbsp;month<br>";

         $checked = ($sch_on_year=='on')?"CHECKED":"";
         $t .= "<input class=\"intext\" type=\"radio\" name=\"{$dialog_id}_sch_on_year\" value=\"on\" {$checked}>&nbsp;";
         $t .= "On&nbsp;<input class=\"intext\" type=\"text\" {$key_mouse}  name=\"{$dialog_id}_sch_std_year\" value=\"{$sch_std_year}\" style=\"width:20px;\">&nbsp;";
         $t .= "<select class=\"intext\" name=\"{$dialog_id}_sch_dow_year\"  >";
         foreach ($dow as $nday=>$day) {
            $selected = ($sch_dow_year==$nday)?"SELECTED":"";
            $t .= "<option value=\"{$nday}\" {$selected} >{$day}</option>";
            }
         $t .= "</select>";
         $t .= "&nbsp;of&nbsp;";
         $t .= "<select class=\"intext\" name=\"{$dialog_id}_sch_rmoy_year\" >";
         foreach ($moy as $nm=>$month) {
            $selected = ($sch_rmoy_year==$nm)?"SELECTED":"";
            $t .= "<option value=\"{$nm}\" {$selected}>{$month}</option>";
            }
         $t .= "</select>";
         break;
      }
   $t .= "</td>";
   $t .= "<td>";
   $t .= "Set&nbsp;start&nbsp;date&nbsp;and&nbsp;time:<br>";
   $t .= "<input class=\"intext\" type=\"text\" {$key_mouse}  name=\"{$dialog_id}_sch_set_start\" id=\"{$dialog_id}_sch_set_start\" value=\"{$sch_set_start}\" style=\"width:120px;\"><br>";
   $t .= "<center><span style=\"color:blue;\">_______________________</span></center>";
   $checked = ($sch_end=='none')?"CHECKED":"";
   $t .= "<input class=\"intext\" type=\"radio\" name=\"{$dialog_id}_sch_end\" value=\"none\" {$checked} >&nbsp;No&nbsp;end&nbsp;date<br>";
   $checked = ($sch_end=='occurs')?"CHECKED":"";
   $t .= "<input class=\"intext\" type=\"radio\" name=\"{$dialog_id}_sch_end\" value=\"occurs\" {$checked} >&nbsp;After&nbsp;";
   $t .= "<input class=\"intext\" type=\"text\" {$key_mouse}  name=\"{$dialog_id}_sch_end_occurs\" value=\"{$sch_end_occurs}\" style=\"width:20px;\" >&nbsp;occurrances<br>";
   $checked = ($sch_end=='date')?"CHECKED":"";
   $t .= "<input class=\"intext\" type=\"radio\" name=\"{$dialog_id}_sch_end\" value=\"date\" {$checked} >&nbsp;End&nbsp;by:<br>";
   $t .= "<input class=\"intext\" type=\"text\" {$key_mouse}  name=\"{$dialog_id}_sch_end_date\" id=\"{$dialog_id}_sch_end_date\" value=\"{$sch_end_date}\" style=\"width:120px;\">";
   _append_script("
   function loadEndDateCalendar() { var t = new dhtmlXCalendarObject(['{$dialog_id}_sch_end_date','{$dialog_id}_sch_set_start']);
      t.setDateFormat('%Y-%m-%d %H:%i');};
	  ");
   global $post_load;
   $post_load .= "loadEndDateCalendar();";
   $t .= "</script>";
   $t .= "</td>";
   $t .= "</tr>";
   $t .= "</table></div>";

   switch ($Action) {
      case "ACTION_{$dialog_id}_Save":
         $end_date = "";
         if ($sch_end=='none') $end_date = "9999-01-01 00:00";
         elseif ($sch_end=='date') $end_date = date('Y-m-d 00:00', strtotime($sch_end_date));
         else {}
         $event_length = 300;
         $build = "";
         $start_date = $sch_set_start;
         $start_date = date("Y-m-d G:i", strtotime($start_date));
         switch ($sch_freq) {
            case 'HOURLY':
                  $build .= "day_{$sch_n_day}___#no";
               break;
            case 'DAILY':
               if ($sch_w_day==1) {
                  $build .= "week_1___1,2,3,4,5#no";
                  }
               else {
                  $build .= "day_{$sch_n_day}___#no";
                  }
               break;
            case 'WEEKLY':
               $build .= "week_";
               $build .= "{$sch_n_week}___";
               foreach($sch_d_week as $p) $build .= "{$p},"; $build = trim($build,',');
               $build .= "#no";
               break;
            case 'MONTHLY':
               $build .= "month_";
               if ($sch_on_month=='repeat') {
                  $build .= "{$sch_n_month}___#no";
                  $start_date = date("Y-m-d G:i", strtotime(date("Y-m-{$sch_nd_month}",strtotime($start_date))));
                  }
               else {
                  $build .= "{$sch_rn_month}_";
                  $build .= "{$sch_dow_month}_";
                  $build .= "{$sch_st_month}_#no";
                  }
               break;
            case 'YEARLY':
               if ($sch_on_year=='repeat') {
                  $build .= "year_1___#no";
                  $start_date = date("Y-m-d G:i", strtotime(date("Y-{$sch_m_year}-{$sch_md_year}",strtotime($start_date))));
                  }
               else {
                  $build .= "year_1_{$sch_md_year}_{$sch_dow_year}_#no";
                  $start_date = date("Y-m-d G:i", strtotime(date("Y-{$sch_rmoy_year}-01",strtotime($start_date))));
                  }
               break;
            }
         $pattern="{$sch_w_day},{$sch_n_day},{$sch_n_week},";
         $pattern .= "{$sch_on_month},{$sch_n_month},{$sch_nd_month},{$sch_rn_month},{$sch_dow_month},{$sch_st_month},";
         $pattern .= "{$sch_on_year},{$sch_m_year},{$sch_md_year},{$sch_dow_year},{$sch_std_year},{$sch_moy_year},{$sch_rmoy_year}";
         $pattern_dow = ""; foreach ($sch_d_week as $p) $pattern_dow.="{$p},"; $pattern_dow=trim($pattern_dow,',');
         PRO_AddToParams("{$dialog_id}_pattern", $pattern);
         PRO_AddToParams("{$dialog_id}_pattern_dow", $pattern_dow);
         PRO_AddToParams("{$dialog_id}_rec_type", $build);
         PRO_AddToParams("{$dialog_id}_start_date", $start_date);
         PRO_AddToParams("{$dialog_id}_end_date", $end_date);
         PRO_AddToParams("{$dialog_id}_event_length", $event_length);
         break;
      }

   return $t;
   }


function _dialogSetSchedule($dialog_id='alc_set_schedule') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;

   global $_validFrequencies;
   $problem = null;
   $allow_frequency = 'ANY';
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
         list($action, $item) = explode('-',$ActionLine);
         break;
      case "ACTION_{$dialog_id}_Save":
         list($action, $item) = explode('-',$ActionLine);
         $on_scheduletype = PRO_ReadParam("{$dialog_id}_scheduletype");
         $on_scheduleunits = PRO_ReadParam("{$dialog_id}_scheduleunits");
         $on_schedulestart = PRO_ReadParam("{$dialog_id}_sch_set_start");
         switch ($on_scheduletype) {
		    case 'MIN':
            case 'DAY':
            case 'HOUR':
               if (!hda_db::hdadb()->HDA_DB_writeSchedule($item, $on_schedulestart, $on_scheduletype, $on_scheduleunits, NULL))
                  $problem = "Problem saving changes ";
               else $problem = "Frequency Changes Saved";
               break;
            default: 
               if (!hda_db::hdadb()->HDA_DB_clearSchedule($item))
                  $problem = "Problem clearing schedule ";
               else $problem = "Frequency cleared";
               break;
            }
         break;
      }
   $on_schedule = hda_db::hdadb()->HDA_DB_getSchedule($item);
   if (is_array($on_schedule) && count($on_schedule)==1) {
      $interval = $on_schedule[0]['RepeatInterval'];
      $units = $on_schedule[0]['Units'];
      $next = $on_schedule[0]['Scheduled'];
      $pattern = $on_schedule[0]['Pattern'];
      $sch_set_start = $on_schedule[0]['Scheduled'];
      }
   else {
      $interval = null;
      $units = 1;
      $next = hda_db::hdadb()->PRO_DB_dateNow();
      $pattern = null;
      $sch_set_start = hda_db::hdadb()->PRO_DB_dateNow();
      $send_alarm = 'NONE';
      $alarm_time = 1;
      }
   $t = _makedialoghead($dialog_id, "Set up Schedule");
   _include_css(array("dhtmlxcalendar","dhtmlxcalendar_dhx_skyblue"));
   _include_script("dhtmlxcalendar");
      
   if (!is_null($problem)) $t .= "<tr><th style=\"color:red;\">{$problem}</th></tr>";
   $t .= "<tr><td>";
   $checked = (is_null($interval))?"CHECKED":"";
   $t .= "<input type=\"radio\" name=\"{$dialog_id}_scheduletype\" value=\"\" {$checked}>Auto Schedule Disabled<br>";
   $t .= "Schedule every ";
   $t .= "<input type=\"text\" {$key_mouse}  name=\"{$dialog_id}_scheduleunits\" value=\"{$units}\" size=4>&nbsp;";
   $checked = ($interval=='MIN')?"CHECKED":"";
   $t .= "<input type=\"radio\" name=\"{$dialog_id}_scheduletype\" value=\"MIN\" {$checked}>Minute&nbsp;or&nbsp;";
   $checked = ($interval=='HOUR')?"CHECKED":"";
   $t .= "<input type=\"radio\" name=\"{$dialog_id}_scheduletype\" value=\"HOUR\" {$checked}>Hour&nbsp;or&nbsp;";
   $checked = ($interval=='DAY')?"CHECKED":"";
   $t .= "<input type=\"radio\" name=\"{$dialog_id}_scheduletype\" value=\"DAY\" {$checked}>Day<br>";

   $t .= "</td><td>";
   $t .= "Starting on ";
   $t .= "<input class=\"intext\" type=\"text\" {$key_mouse}  name=\"{$dialog_id}_sch_set_start\" id=\"{$dialog_id}_sch_set_start\" value=\"{$sch_set_start}\" style=\"width:120px;\"><br>";
   _append_script("
   function loadStartDateCalendar() { var t = new dhtmlXCalendarObject(['{$dialog_id}_sch_set_start']);
      t.setDateFormat('%Y-%m-%d %H:%i');};
	  ");
   global $post_load;
   $post_load .= "loadStartDateCalendar();";
   $t .= "</script>";
   $t .= "</td>";
   $t .= "</tr>";
   
   $t .= _closeDialog($dialog_id, "_Save-{$item}--", 2);
   $t .= _makedialogclose();
   return $t;
   }
   
function _insertDownloadFileDiv($dialog_id, $from_path) {
   $t = "";
   $t .= "<a href=\"HDAW.php?load=HDA_DownLoadFile&file={$from_path}\" target=\"_blank\" >Download</a>";
   PRO_AddToParams("alc_export_options_frompath", $from_path);
   $mouse = _click_dialog("_dialogExportOptions","-OPEN");
   $t .= "&nbsp;<span class=\"click-here\" style=\"color:blue;\" {$mouse} >[ Other Export Options ]</span>";
   return $t;
   }

function _dialogExportOptions($dialog_id='alc_export_options') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;
   $open = null;
   $problem = null;
   $from_path = PRO_ReadParam("{$dialog_id}_frompath");
   switch ($Action) {
      case "ACTION_{$dialog_id}":
         list($action, $open) = explode('-',$ActionLine);
         break;
      case "ACTION_{$dialog_id}_ExportedFile":
         $open = true;
         list($action, $method) = explode('-',$ActionLine);
         switch ($method) {
            case 'FTP':
              try {
			     $ftp = new HDA_FTP();
				 $e = $ftp->setHost($ftp_site = PRO_ReadParam("{$dialog_id}_Export_FTPsite"));
				 if ($e===false) { $problem = "Fails FTP Export: {$ftp->last_error}"; break; }
				 $ftp->username = $ftp_user = PRO_ReadParam("{$dialog_id}_Export_FTPuser");
				 $ftp->pw = PRO_ReadParam("{$dialog_id}_Export_FTPpw");
				 $e = $ftp->open();
 				 if ($e===false) { $problem = "Fails FTP Export: {$ftp->last_error}"; break; }
                 $ftp_file = PRO_ReadParam("{$dialog_id}_Export_FTPfile");
				 $ftp->ftp_mode = FTP_BINARY;
				 $e = $ftp->write_file($from_path, $ftp_file);
 				 if ($e===false) { $problem = "Fails FTP Export: {$ftp->last_error}"; $ftp->close(); break; }
				 $ftp->close();
				 $problem = "Written {$from_path} to {$ftp_file} on {$ftp->host} OK";
				 }
              catch (Exception $e) {
                 $problem = "Fails to send via ftp - ".$e->getMessage();
                 }
              break;
            case 'FILE':
               try {
                  $filename = PRO_ReadParam("{$dialog_id}_Export_File");
                  $filename = str_replace("\\","\\\\",$filename);
                  if (is_null($filename) || strlen($filename)==0) $problem = "Need a destination path";
                  elseif (!file_exists(pathinfo($filename, PATHINFO_DIRNAME))) $problem = "Destination directory for {$filename} does not exist";
                  else {
                     $f = @copy($from_path, $filename);
                     if ($f===false) $problem = "Fails to copy file {$from_path} to {$filename}";
                     else $problem = "Exported {$from_path} to {$filename} OK";
                     }
                  }
               catch (Exception $e) {
                  $problem = "Fails to export and write file direct - ".$e->getMessage();
                  }
               break;
            case 'CONN':
               try {
                  $c = hda_db::hdadb()->HDA_DB_dictionary(PRO_ReadParam("{$dialog_id}_Export_CONN"));
                  if (is_null($c) || !is_array($c) || count($c)<>1) $problem = "Unable to obtain connection details";
                  else {
                     $def = $c[0]['Definition'];
                     switch ($def['Connect Type']) {
                        case 'FTP':
						   $ftp = new HDA_FTP();
						   $e = $ftp->useDictionary($c[0]['ItemId']);
						   if ($e===false) {$problem = "Export FTP : {$ftp->last_error}"; break; }
						   $e = $ftp->open();
						   if ($e===false) {$problem = "Export FTP : {$ftp->last_error}"; break; }
						   $e = $ftp->to_dst_dir();
						   if ($e===false) {$problem = "Export FTP : {$ftp->last_error}"; $ftp->close(); break; }
						   $ftp->ftp_filename = (is_null($ftp->ftp_filename) || strlen($ftp->ftp_filename)==0)?"exported.txt":$ftp->ftp_filename;
						   $ftp->ftp_mode = FTP_BINARY;
						   $e = $ftp->write_file($from_path);
						   if ($e===false) {$problem = "Export FTP : {$ftp->last_error}"; $ftp->close(); break; }
						   $ftp->close();
						   $problem = "Exported {$from_path} to {$ftp->ftp_filename} on {$ftp->host} OK";
                           break;
                        case 'FILE':
                           $filename = $def['Table'];
                           $filename.="/{$def['Key']}";
                           $filename = str_replace("\\","/",$filename);
                           if (is_null($filename) || strlen($filename)==0) $problem = "Need a filename  {$filename}";
                           elseif (!file_exists(pathinfo($filename, PATHINFO_DIRNAME))) $problem = "Need destination directory for {$filename} to exist";
                           else {
                              $f = @copy($from_path, $filename);
                              if ($f===false) $problem = "Fails to copy file {$from_path} to {$filename}";
                              else $problem = "Copied {$from_path} to {$filename} OK";
                              }
                           break;
                        default:
                           $problem = "Export File Connections can only be FTP or FILE";
                        }
                     }
                  }
               catch (Exception $e) {
                  $problem = "Fails to export file  - ".$e->getMessage();
                  }
               break;
            }
         break;
      }
   if (is_null($open)) return "";
   $t = "";
   $t .= "<div id=\"{$dialog_id}\" class=\"alc-dialog{$_Mobile}\" >";
   $t .= "<div class=\"alc-display-box\" >Other Download Options</div>";
   $t .= "<table class=\"alc-table\">";
   if (!is_null($problem)) $t .= "<tr><th colspan=4>{$problem}</th></tr>";
   $t .= "<tr><th>";
   $t .= _emit_image("DownloadThis.jpg",24);
   $t .= "</th><th colspan=3>";
   $t .= "Download file:&nbsp;<a href=\"{$from_path}\" target=\"_blank\" >Download</a>";
   $t .= "</th></tr>";
   $t .= "<tr><th rowspan=4>";
   $t .= _emit_image("SOURCE_FTP.jpg",24);
   $t .= "</th>";
   $t .= "<td>Send to FTP site:</td>";
   $t .= "<td><input type=\"text\" {$key_mouse}  class=\"alc-dialog-name\" name=\"{$dialog_id}_Export_FTPsite\" value=\"".PRO_ReadParam("{$dialog_id}_Export_FTPsite")."\"></td>";
   $mouse = _click_dialog($dialog_id,"_ExportedFile-FTP");
   $t .= "<th rowspan=4>";
   $t .= "<span class=\"push_button blue\" {$mouse}  >FTP Send</span>"; 
   $t .= "</th></tr>";
   $t .= "<tr><td>FTP user:</td>";
   $t .= "<td><input type=\"text\" {$key_mouse}  class=\"alc-dialog-name\" name=\"{$dialog_id}_Export_FTPuser\" value=\"".PRO_ReadParam("{$dialog_id}_Export_FTPuser")."\"></td></tr>";
   $t .= "<tr><td>FTP pw:</td><td><input type=\"password\" {$key_mouse}  class=\"alc-dialog-name\" name=\"{$dialog_id}_Export_FTPpw\" value=\"\"></td></tr>";
   $t .= "<tr><td>FTP filename:</td>";
   $t .= "<td><input type=\"text\" {$key_mouse}  class=\"alc-dialog-name\" name=\"{$dialog_id}_Export_FTPfile\" value=\"".PRO_ReadParam("{$dialog_id}_Export_FTPfile")."\"></td></tr>";
   $t .= "<tr><th>";
   $t .= _emit_image("SOURCE_FILE.jpg",24);
   $t .= "</th>";
   $t .= "<td>To mapped directory:</td>";
   $t .= "<td><input type=\"text\" {$key_mouse}  class=\"alc-dialog-name\" name=\"{$dialog_id}_Export_File\" value=\"".PRO_ReadParam("{$dialog_id}_Export_File")."\"></td>";
   $mouse = _click_dialog($dialog_id,"_ExportedFile-FILE");
   $t .= "<th>";
   $t .= "<span class=\"push_button blue\" {$mouse}   >Export to File</span>"; 
   $t .= "</th></tr>";
   $t .= "<tr><th>";
   $t .= _emit_image("SOURCE_HDAW.jpg",24);
   $t .= "</th>";
   $t .= "<td>Send using a global connection:</td>";
   $a = hda_db::hdadb()->HDA_DB_dictionary();
   $t .= "<td><select name=\"{$dialog_id}_Export_CONN\" >";
   $last_conn = PRO_ReadParam("{$dialog_id}_Export_CONN");
   foreach ($a as $row) {
      $selected = ($last_conn == $row['ItemId'])?"SELECTED":"";
      $t .= "<option value=\"{$row['ItemId']}\" {$selected}>{$row['Name']}</option>";
      }
   $t .= "</select></td>";
   $mouse = _click_dialog($dialog_id,"_ExportedFile-CONN");
   $t .= "<th>";
   $t .= "<span class=\"push_button blue\" {$mouse}   >Connect &amp; Send</span>"; 
   $t .= "</th></tr>";

   $t .= _closeDialog($dialog_id, true, 4);
   $t .= _makedialogclose();

   return $t;
   }

function _insertImportFileDiv($dialog_id, $item, $up_path, $back_ref=null) {
   global $code_root, $home_root;
   global $key_mouse;
   $t = "";
   $t .= "<div><table class=\"alc-table\">";
   $t .= "<tr><th>";
   $t .= _emit_image("SOURCE_UPLOAD.jpg",24);
   $t .= "</th><td>";
   $t .= "Upload file:</td><td><input type=\"file\" name=\"{$up_path}\"  value=\"\" >";
   $mouse = _click_dialog($dialog_id,"_ImportedFile-UPLOAD-{$item}-{$up_path}-{$back_ref}");
   $t .= "</td><th>";
   $t .= "<span class=\"push_button blue\" {$mouse}   >Upload</span>"; 
   $t .= "</th></tr>";
   $t .= "<tr><th rowspan=4>";
   $t .= _emit_image("SOURCE_FTP.jpg",24);
   $t .= "</th>";
   $t .= "<td>From FTP site:</td>";
   $t .= "<td><input type=\"text\" {$key_mouse}  class=\"alc-dialog-name\" name=\"{$dialog_id}_Import_FTPsite\" value=\"".PRO_ReadParam("{$dialog_id}_Import_FTPsite")."\"></td>";
   $mouse = _click_dialog($dialog_id,"_ImportedFile-FTP-{$item}-{$up_path}-{$back_ref}");
   $t .= "<th rowspan=4>";
   $t .= "<span class=\"push_button blue\" {$mouse}   >FTP Fetch</span>"; 
   $t .= "</th></tr>";
   $t .= "<tr><td>FTP user:</td>";
   $t .= "<td><input type=\"text\" {$key_mouse}  class=\"alc-dialog-name\" name=\"{$dialog_id}_Import_FTPuser\" value=\"".PRO_ReadParam("{$dialog_id}_Import_FTPuser")."\"></td></tr>";
   $t .= "<tr><td>FTP pw:</td><td><input type=\"password\" class=\"alc-dialog-name\" name=\"{$dialog_id}_Import_FTPpw\" value=\"\"></td></tr>";
   $t .= "<tr><td>FTP filename:</td>";
   $t .= "<td><input type=\"text\" {$key_mouse}  class=\"alc-dialog-name\" name=\"{$dialog_id}_Import_FTPfile\" value=\"".PRO_ReadParam("{$dialog_id}_Import_FTPfile")."\"></td></tr>";
   $t .= "<tr><th>";
   $t .= _emit_image("SOURCE_FILE.jpg",24);
   $t .= "</th>";
   $t .= "<td>From mapped directory:</td>";
   $t .= "<td><input type=\"text\" {$key_mouse}  class=\"alc-dialog-name\" name=\"{$dialog_id}_Import_File\" value=\"".PRO_ReadParam("{$dialog_id}_Import_File")."\"></td>";
   $mouse = _click_dialog($dialog_id,"_ImportedFile-FILE-{$item}-{$up_path}-{$back_ref}");
   $t .= "<th>";
   $t .= "<span class=\"push_button blue\" {$mouse}   >File Fetch</span>"; 
   $t .= "</th></tr>";
   $t .= "<tr><th>";
   $t .= _emit_image("SOURCE_HDAW.jpg",24);
   $t .= "</th>";
   $t .= "<td>From global connection:</td>";
   $a = hda_db::hdadb()->HDA_DB_dictionary();
   $t .= "<td><select name=\"{$dialog_id}_Import_CONN\" >";
   $last_conn = PRO_ReadParam("{$dialog_id}_Import_CONN");
   foreach ($a as $row) {
      $selected = ($last_conn == $row['ItemId'])?"SELECTED":"";
      $t .= "<option value=\"{$row['ItemId']}\" {$selected}>{$row['Name']}</option>";
      }
   $t .= "</select></td>";
   $mouse = _click_dialog($dialog_id,"_ImportedFile-CONN-{$item}-{$up_path}-{$back_ref}");
   $t .= "<th>";
   $t .= "<span class=\"push_button blue\" {$mouse}   >Connect &amp; Fetch</span>"; 
   $t .= "</th></tr>";
   $t .= "<tr><th>"._emit_image("CommentOn.jpg",24)."</th>";
   $t .= "<td>Attach Comment</td>";
   $t .= "<th colspan=3><textarea style=\"width:100%;height:100px;overflow:auto;\" name=\"{$dialog_id}_Comment\" >".PRO_ReadParam("{$dialog_id}_Comment")."</textarea></th></tr>";
   $t .= "</table></div>";
   return $t;
   }
function _actionImportFileDiv($dialog_id, &$problem) {
   global $ActionLine;
   $problem = null;
   list($action, $method, $item, $up_path) = explode('-',$ActionLine);
   $a = array();
   $a['Comment'] = PRO_ReadParam("{$dialog_id}_Comment");
   switch ($method) {
      case 'UPLOAD':
        try {
           if (isset($_FILES) && isset($_FILES[$up_path]) &&
              strlen($_FILES[$up_path]['name'])>0 && isset($_FILES[$up_path]['tmp_name']) && strlen($_FILES[$up_path]['tmp_name'])>0) {
              $a['_FILE'] = $_FILES[$up_path];
              $a['Filesize'] = $_FILES[$up_path]['size'];
              $a['Path'] = $path = $_FILES[$up_path]['name'];
              $a['Path_Info'] = $path_info = pathinfo($path);
              $a['Extension'] = (array_key_exists('extension', $path_info))?$path_info['extension']:"";
              $to_path = "tmp/{$item}";
              _rrmdir($to_path);
              if (!file_exists($to_path)) @mkdir($to_path); _chmod($to_path);
              $a['UploadedPath'] = "{$to_path}/{$a['Path_Info']['basename']}";
              if(move_uploaded_file($_FILES[$up_path]['tmp_name'],$a['UploadedPath'])) return $a;
              }
           $problem = "Problem in upload - maybe, file too big {$up_path} "; $problem .= print_r($_FILES, true);
           }
        catch (Exception $e) {
           $problem = "Problem in upload - ".$e->getMessage();
           }
        break;
      case 'FTP':
        try {
		   $ftp = new HDA_FTP();
		   $ftp->setHost($ftp_site = PRO_ReadParam("{$dialog_id}_Import_FTPsite"));
		   $ftp->username = $ftp_user = PRO_ReadParam("{$dialog_id}_Import_FTPuser");
		   $ftp->pw = PRO_ReadParam("{$dialog_id}_Import_FTPpw");
		   $ftp_file = PRO_ReadParam("{$dialog_id}_Import_FTPfile");
		   $e = $ftp->open();
		   if ($e===false) { $problem = "Fails to connect to FTP site {$ftp_site}"; break; }
           $a['_FILE'] = array('FTP'=>$ftp_file);
           $a['Path_Info'] = $path_info = pathinfo($ftp_file);
           $a['Extension'] = (array_key_exists('extension', $path_info))?$path_info['extension']:"";
           $a['Path'] = $ftp_file;
           $to_path = "tmp/{$item}";
		   $e = $ftp->to_dst_dir($path_info['dirname']);
		   if ($e===false) {$problem = "Fails to fetch to dir {$ftp->last_error}"; $ftp->close(); break;}
		   $ftp->ftp_filename = $path_info['basename'];
           _rrmdir($to_path);
           if (!file_exists($to_path)) @mkdir($to_path); 
           $a['UploadedPath'] = "{$to_path}/{$a['Path_Info']['basename']}";
           $a['Filesize'] = $ftp->filesize();
		   $ftp->ftp_mode = FTP_BINARY;
           $e = $ftp->read_file($a['UploadedPath']);
           if ($e===false) {$problem = "Fails to fetch {$ftp->last_error}"; $ftp->close(); break;}
           $ftp->close();
           if (is_null($problem)) return $a;
           }
         catch (Exception $e) {
           $problem = "Fails to fetch via ftp - ".$e->getMessage();
           }
         break;
      case 'FILE':
         try {
            $to_path = "tmp/{$item}";
            _rrmdir($to_path);
            if (!file_exists($to_path)) @mkdir($to_path);
            $filename = PRO_ReadParam("{$dialog_id}_Import_File");
            $filename = str_replace("\\","\\\\",$filename);
            if (is_null($filename) || strlen($filename)==0 || !file_exists($filename)) $problem = "Need a filename or file does not exist {$filename}";
            else {
               $to_path .= "/".pathinfo($filename, PATHINFO_BASENAME);
               $f = @copy($filename, $to_path);
               if ($f===false) $problem = "Fails to copy file {$filename} to {$to_path}";
               else {
                  $a['_FILE'] = array('FILE'=>$filename);
                  $a['Extension'] = pathinfo($to_path, PATHINFO_EXTENSION);
                  $a['Path'] = $filename;
                  $a['UploadedPath'] = $to_path;
                  $a['Filesize'] = filesize($to_path);
                  $a['Path_Info'] = $path_info = pathinfo($filename);
                  return $a;
                  }
               }
            }
         catch (Exception $e) {
            $problem = "Fails to read file direct - ".$e->getMessage();
            }
         break;
      case 'CONN':
         try {
            $c = hda_db::hdadb()->HDA_DB_dictionary(PRO_ReadParam("{$dialog_id}_Import_CONN"));
            if (is_null($c) || !is_array($c) || count($c)<>1) $problem = "Unable to obtain connection details";
            else {
               $def = $c[0]['Definition'];
               switch ($def['Connect Type']) {
                  case 'FTP':
				     $ftp = new HDA_FTP();
					 $e = $ftp->useDictionary($c[0]['ItemId']);
					 if ($e===false) {$problem = "Fails FTP {$ftp->last_error}";break;}
					 $e = $ftp->open();
					 if ($e===false) {$problem = "Fails FTP {$ftp->last_error}";break;}
					 $ftp->ftp_mode = FTP_BINARY;
					 $e = $ftp->to_dst_dir();
					 if ($e===false) {$problem = "Fails FTP {$ftp->last_error}";$ftp->close();break;}
					 $to_path = "tmp/{$item}";
					 _rrmdir($to_path);
					 if (!file_exists($to_path)) @mkdir($to_path);
					 $a['_FILE'] = array('FTP'=>$ftp->ftp_filename);
					 $a['Path_Info'] = $path_info = pathinfo($ftp->ftp_filename);
					 $a['Extension'] = (array_key_exists('extension', $path_info))?$path_info['extension']:"";
					 $a['Path'] = "{$ftp->ftp_dir}/{$ftp->ftp_filename}";
					 $a['UploadedPath'] = "{$to_path}/{$a['Path_Info']['basename']}";
					 $a['Filesize'] = $ftp->filesize();
					 $e = $ftp->read_file($a['UploadedPath']);
					 if ($e===false) {$problem = "Fails FTP {$ftp->last_error}";$ftp->close();break;}
					 $ftp->close();
                     if (is_null($problem)) return $a;
                     break;
                  case 'FILE':
                     $filename = $def['Table'];
                     $filename.="/{$def['Key']}";
                     $filename = str_replace("\\","/",$filename);
                     if (is_null($filename) || strlen($filename)==0 || !file_exists($filename)) $problem = "Need a filename or file does not exist {$filename}";
                     else {
                        $to_path = "tmp/{$item}";
                        _rrmdir($to_path);
                        if (!file_exists($to_path)) @mkdir($to_path);
                        $to_path .= "/".pathinfo($filename, PATHINFO_BASENAME);
                        $f = @copy($filename, $to_path);
                        if ($f===false) $problem = "Fails to copy file {$filename} to {$to_path}";
                        else {
                           $a['_FILE'] = array('FILE'=>$filename);
                           $a['Extension'] = pathinfo($to_path, PATHINFO_EXTENSION);
                           $a['Path'] = $filename;
                           $a['UploadedPath'] = $to_path;
                           $a['Filesize'] = filesize($to_path);
                           $a['Path_Info'] = $path_info = pathinfo($filename);
                           return $a;
                           }
                        }
                     break;
                  default:
                     $problem = "Connections can only be FTP or FILE";
                  }
               }
            }
         catch (Exception $e) {
            $problem = "Fails to read file via a named connection - ".$e->getMessage();
            }
      }
   return null;
   }


function _dialogSearch($dialog_id='alc_search') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;
   $a = null;
   $match = null;
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}":
         break;
      case "ACTION_{$dialog_id}_Find":
         $a = hda_db::hdadb()->HDA_DB_findProfiles($match = PRO_ReadParam("{$dialog_id}_Match"));
         break;
      }
   $t = _makedialoghead($dialog_id, "Search for Profile");
   $mouse = "onKeyPress=\"return keyPressPost('{$dialog_id}_Find',event,'{$dialog_id}')\" ";
   $t .= "<tr><td><input type=\"text\" {$key_mouse}  name=\"{$dialog_id}_Match\" value=\"{$match}\" {$mouse} >";
   $mouse = _click_dialog($dialog_id,"_Find");
   $t .= "&nbsp;&nbsp;<span class=\"push_button blue\" {$mouse}   >Find...</span>"; 
   $t .= "</td></tr>";
   if (is_array($a) && count($a)>0) {
      $t .= "<tr><td><div style=\"width:100%;height:200px;overflow-x:hidden;overflow-y:auto;\"><table class=\"alc-table\">";
      foreach($a as $row) {
         $mouse = "onclick=\"issuePost('gotoTab-LD-{$row['ItemId']}---',event); return false;\" ";
         $t .= "<tr><td><span class=\"click-here\" {$mouse}>{$row['Title']}</span></td>";
         $t .= "<td><span style=\"cursor:pointer;height:16px;\" title=\"Select this..\" {$mouse}>";
         $t .= _emit_image("GoForward.jpg",12)."</span></td>";
         $t .= "</tr>";
         }
      $t .= "</table></div></td></tr>";
      }
   elseif (!is_null($match)) $t .= "<tr><th>No profiles match {$match}</th></tr>";
   $t .= _makedialogclose();
   return $t;
   }

function _dialogSearchTags($dialog_id='alc_tag_search') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;
   $a = null;
   $match = null;
   $all_tags = null;
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}_Find":
         $a = HDA_ProfileTags($item=NULL, $metatags=NULL, $match = PRO_ReadParam("{$dialog_id}_Match"), $all_tags);
         break;
      case "ACTION_{$dialog_id}":
	  case "ACTION_{$dialog_id}_FindSelection":
	     $tags = PRO_ReadParam("{$dialog_id}_AllTags");
		 if (is_null($tags) || !is_array($tags) || count($tags)==0) $tags = array();
		 $a = HDA_ProfileTags($item=NULL, $metatags=NULL, $match = implode(';',$tags), $all_tags);
	     PRO_AddToParams("{$dialog_id}_Match", $match);
		 break;
      }
   $t = _makedialoghead($dialog_id, "Search for Tagged Profiles", "alc-dialog-large" );
   $mouse = "onKeyPress=\"return keyPressPost('{$dialog_id}_Find',event,'{$dialog_id}')\" ";
   $t .= "<tr><td><input type=\"text\" name=\"{$dialog_id}_Match\" value=\"{$match}\" {$mouse} style=\"width:90%;\" >";
   $mouse = _click_dialog($dialog_id,"_Find");
   $t .= "&nbsp;&nbsp;<span title=\"Find..\" {$mouse}>";
   $t .= _emit_image("GoNow.jpg",18)."</span>";
   $t .= "</td></tr>";
   if (is_array($a) && count($a)>0) {
      $t .= "<tr><td><div style=\"width:100%;height:200px;overflow-x:hidden;overflow-y:auto;\"><table class=\"alc-table\">";
      foreach($a as $item=>$p) {
         $mouse = "onclick=\"issuePost('gotoTab-LD-{$item}---',event); return false;\" ";
         $t .= "<tr><td><span class=\"click-here\" {$mouse}>{$p['Title']}</span></td>";
		 $t .= "<td>".str_replace(';','; ',$p['Tags'])."</td>";
         $t .= "<td><span style=\"cursor:pointer;height:16px;\" title=\"Select this..\" {$mouse}>";
         $t .= _emit_image("GoForward.jpg",12)."</span></td>";
         $t .= "</tr>";
         }
      $t .= "</table></div></td></tr>";
      }
   elseif (!is_null($match)) $t .= "<tr><th style=\"color:red;\">No profiles match {$match}</th></tr>";
   if (!is_null($all_tags) && is_array($all_tags) && count($all_tags)>0) {
      $t .= "<tr><th>Registered Tags:</th></tr>";
      $t .= "<tr><th><div style=\"width:100%;height:140px;overflow-x:hidden;overflow-y:auto;text-align:left;\">";
	  $set_tags = PRO_ReadParam("{$dialog_id}_AllTags");
	  if (is_null($set_tags) || !is_array($set_tags)) $set_tags = array();
	  foreach ($all_tags as $tag) {
	     $tag = trim($tag);
		 $checked = (in_array($tag, $set_tags))?"CHECKED":"";
	     $t .= "<label style=\"display:inline-block\"><input type=\"checkbox\" name=\"{$dialog_id}_AllTags[]\" value=\"{$tag}\" {$checked} >{$tag}</label>&nbsp;&nbsp;";
		 }
	  $t .= "</div></th></tr>";
	  $t .= "<tr><th>";
      $mouse = _click_dialog($dialog_id,"_FindSelection");
      $t .= "&nbsp;&nbsp;<span class=\"push_button blue\" {$mouse}   >Find Selected</span>"; 
	  $t .= "</th></tr>";
      }
   $t .= _makedialogclose();
   return $t;
   }

   
function _dialogFilterCategory($dialog_id='alc_category_filter') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}":
         break;
      case "ACTION_{$dialog_id}_Change":
         $onFilter = PRO_ReadParam("{$dialog_id}_On");
         if (is_null($onFilter) || strlen($onFilter)==0) PRO_Clear("{$dialog_id}_On");
		 return "";
         break;
	  case "ACTION_{$dialog_id}_Clear":
	     PRO_CLear("{$dialog_id}_On");
		 break;

      }
   $t = _makedialoghead($dialog_id, "Filter on Category");
   $struct = hda_db::hdadb()->HDA_DB_admin('Structure');
   if (is_null($struct) || strlen($struct)==0) $struct = array(); else $struct = hda_db::hdadb()->HDA_DB_unserialize($struct);
   $onFilter = PRO_ReadParam("{$dialog_id}_On");
   if (is_null($onFilter) || strlen($onFilter)==0) $onFilter = null;
   $mouse = "onchange=\"issuePost('{$dialog_id}_Change',event); return false;\" ";
   $t .= "<tr><th><select name=\"{$dialog_id}_On\" {$mouse} >";
   $selected = (is_null($onFilter))?"SELECTED":"";
   $t .= "<option value=\"\" {$selected} >No Filter</option>";
   foreach($struct as $struct_item) {
      $selected = ($onFilter==$struct_item)?"SELECTED":"";
      $t .= "<option value=\"{$struct_item}\" {$selected}>{$struct_item}</option>";
      }
   $t .= "</select></th></tr>";
   $t .= _makedialogclose();

   return $t;
   }
   
function _dialogPriorityQ($dialog_id='alc_priority_q') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;
   $problem = null;
   $item = null;
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}":
	     list($action, $item) = explode('-',$ActionLine);
         break;
      case "ACTION_{$dialog_id}_Save":
 	     list($action, $item) = explode('-',$ActionLine);
         $profile = hda_db::hdadb()->HDA_DB_ReadProfile($item);
		 if (!is_null($profile) && is_array($profile)) {
            hda_db::hdadb()->HDA_DB_UpdateProfile($item,array('Q'=>PRO_ReadParam("{$dialog_id}_PriorityQ")));
			}
         break;

      }
   $t = _makedialoghead($dialog_id, "Change priority run queue");
   $profile = hda_db::hdadb()->HDA_DB_ReadProfile($item);
   $isProxy = hda_db::hdadb()->HDA_DB_relationIsProxy($item);
   if ($isProxy) {
      $t .= "<tr><td>>This profile is marked as a proxy process, it will always be assigned to the proxy queue</td></tr>>";
	  }
   else {
      $in_q = 0;
      if (!is_null($profile) && is_array($profile)) $in_q = $profile['Q'];
      else $problem = "Fails to read profile details";
      if (is_null($in_q) || ($in_q<0) || ($in_q)>9) $in_q = 0;

      if (!is_null($problem)) $t .= "<tr><th style=\"color:red;\" >{$problem}</th></tr>";
      $t .= "<tr><th>Note: higher values indicate lower priority, Normal is high prioprity, 9 is Lowest priority</th></tr>";
      $mouse = _change_dialog($dialog_id,"_Save-{$item}");
      $t .= "<tr><th><select name=\"{$dialog_id}_PriorityQ\" {$mouse}>";
      for ($i=0; $i<10; $i++) {
         $caption = ($i==0)?"Normal":$i;
         $selected = ($in_q==$i)?"SELECTED":"";
	     $t .= "<option value=\"{$i}\" {$selected} >{$caption}</option>";
         }
      $t .= "</select></th></tr>";
	  }
   $t .= _makedialogclose();

   return $t;
   }
   
function _dialogDefineCategory($dialog_id='alc_category_manager') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;
   $problem = null;
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}":
         break;
      case "ACTION_{$dialog_id}_Save":
         $s = PRO_ReadParam("{$dialog_id}_structure");
         if (!is_null($s)) $struct = explode("\n", $s); else $struct = array();
         $a = array(); foreach ($struct as $struct_item) $a[] = preg_replace("/\W/","_",trim($struct_item));
         $struct = hda_db::hdadb()->HDA_DB_serialize($a);
         if (!hda_db::hdadb()->HDA_DB_admin('Structure', $struct)) $problem = "Fails to save structure";
         else $problem = "Structure saved";
         break;

      }
   $t = _makedialoghead($dialog_id, "Define Profile Categories");
   $struct = hda_db::hdadb()->HDA_DB_admin('Structure');
   if (is_null($struct) || strlen($struct)==0) $struct = array(); else $struct = hda_db::hdadb()->HDA_DB_unserialize($struct);
   if (!is_null($problem)) $t .= "<tr><th style=\"color:red;\" >{$problem}</th></tr>";
   $t .= "<tr><th>Add a structure name, one per line</th></tr>";
   $t .= "<tr><th><textarea name=\"{$dialog_id}_structure\" style=\"width:98%;\" rows=10 >";
   foreach($struct as $struct_item) $t .= "{$struct_item}\n";
   $t .= "</textarea>";
   $t .= "</th></tr>";
   $t .= _closeDialog($dialog_id, true);
   $t .= _makedialogclose();

   return $t;
   }
   

   
function _dialogGlobals($dialog_id='alc_globals') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;
   $problem = null;
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}":
	  case "ACTION_{$dialog_id}_Refresh":
         break;
      case "ACTION_{$dialog_id}_DeleteValidation":
         list($action, $item) = explode('-',$ActionLine);
         hda_db::hdadb()->HDA_DB_validationDelete($item);
         break;
      case "ACTION_{$dialog_id}_DeleteDef":
         list($action, $def_item) = explode('-',$ActionLine);
         hda_db::hdadb()->HDA_DB_dictionaryDelete($def_item);
         break;
      case "ACTION_{$dialog_id}_Save":
         $problem = "Changes saved";
         break;

      }
   $t = _makedialoghead($dialog_id, "Site Globals");
   if (!is_null($problem)) $t .= "<tr><th style=\"color:red;\" >{$problem}</th></tr>";
   $showing_globals = PRO_ReadParam("{$dialog_id}_ShowingGlobals");
   if (is_null($showing_globals)) $showing_globals = 'Connections';
   $t .= "<tr><th>";
   $mouse = _click_dialog($dialog_id,"_Refresh");
   foreach (array('Connections','Validations') as $k) {
      $checked = ($showing_globals == $k)?"CHECKED":"";
	  $t .= "&nbsp;&nbsp;<input type=\"radio\" name=\"{$dialog_id}_ShowingGlobals\" {$checked} {$mouse} value=\"{$k}\" >{$k}";
      }
   $t .= "</th></tr>";
   switch ($showing_globals) {
      default:
      case 'Connections':
         $t .= _global_connections_form($dialog_id); break;
	  case 'Validations':
		 $t .= _global_validation_form($dialog_id); break;
      }
   $t .= _closeDialog($dialog_id, true);
   $t .= _makedialogclose();

   return $t;
   }
function _global_connections_form($dialog_id) {
   global $code_root, $home_root;
   global $key_mouse;

   $t = "";
      $a = hda_db::hdadb()->HDA_DB_dictionary();
      $t .= "<tr><td colspan=3 class=\"buttons\" >";
      $mouse = _click_dialog("_dialogAdminEditDef","_NEW");
      $t .= "<span style=\"cursor:pointer;height:16px;\" title=\"Create connection entry..\" {$mouse}>";
      $t .= _emit_image("AddThis.jpg",16)."</span>";
      $mouse = _click_dialog("_dialogExportConnections");
      $t .= "&nbsp;&nbsp;<span  title=\"Export connections..\"  {$mouse}>";
      $t .= _emit_image("Export.jpg",16);
      $t .= "</span>";
      $mouse =  _click_dialog("_dialogImportConnections");
      $t .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Import connections..\" {$mouse}>";
      $t .= _emit_image("ImportThis.jpg",16)."</span>";
      $t .= "</td></tr>";
         $t .= "<tr><th colspan=3><div style=\"width:100%; height:240px; overflow:scroll;\" ><table class=\"alc-table\">";
         $t .= "<tr><th>Name</th><td>Definition</td><td>&nbsp;</td></tr>";
         if (is_null($a) || count($a)==0) $t .= "<tr><th colspan=3>No connection dictionary entries</th></tr>";
         else foreach($a as $row) {
            $mouse = _click_dialog("_dialogAdminEditDef","_EDIT-{$row['ItemId']}");
            $t .= "<tr><td><span class=\"click-here\" {$mouse}>{$row['Name']}</span></td><td><span class=\"click-here\" {$mouse}>";
            $def = $row['Definition'];
            $ctype = $def['Connect Type'];
            $fields = _translateLookupFields($ctype);
            foreach ($def as $k=>$p) {
               if (array_key_exists($k, $fields)) switch ($k) {
                  case 'PassW': $t .= "{$fields[$k][0]}:&nbsp;*****; "; break;
                  default: $t .= "{$fields[$k][0]}:&nbsp;{$p}; "; break;
                  }
               }
            $t .= "</span></td>";
            $t .= "<td>";
            $t .= "<span title=\"Edit definition..\" {$mouse}>";
			$t .= _emit_image("Edit.jpg",12)."</span>&nbsp;";
            $mouse = _click_dialog($dialog_id,"_DeleteDef-{$row['ItemId']}");
            $t .= "<span title=\"Delete definition..\" {$mouse}>";
			$t .= _emit_image("DeleteThis.jpg",12)."</span>";
            $t .= "</td>";
            $t .= "</tr>";
            }
         $t .= "</table></div></th></tr>";

   return $t;
   }
function _global_validation_form($dialog_id) {
   global $code_root, $home_root;
   global $key_mouse;

   $t = "";
      $t .= "<tr><td colspan=3 class=\"buttons\" >";
      $mouse = _click_dialog("_dialogAdminNewValidation","_NEW");
      $t .= "<span style=\"cursor:pointer;height:16px;\" title=\"Create validation entry..\" {$mouse}>";
      $t .= _emit_image("AddThis.jpg",16)."</span>";
      $mouse = _click_dialog("_dialogExportValidations");
      $t .= "&nbsp;&nbsp;<span  title=\"Export validations..\"  {$mouse}>";
      $t .= _emit_image("Export.jpg",16);
      $t .= "</span>";
      $mouse = _click_dialog("_dialogImportValidations");
      $t .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Import validations..\" {$mouse}>";
      $t .= _emit_image("ImportThis.jpg",16)."</span>";
      $t .= "</td></tr>";
   $t .= "<tr><th colspan=3><div style=\"width:100%; height:150px; overflow:auto;\"><table class=\"alc-table\">";
   $a = hda_db::hdadb()->HDA_DB_validationCode(null, null);
   if (is_null($a) || !is_array($a) || count($a)==0) $t .= "<tr><th>No entries for validations</th></tr>";
   foreach ($a as $row) {
      $t .= "<tr>";
      $t .= "<td>{$row['LookupId']}</td>";
      $t .= "<td>{$row['ItemValue']['Method']}</td>";
      $t .= "<td>";
      switch ($row['ItemValue']['Method']) {
         case 'ACTUAL':
            $t .= "Actual Values Defined {$row['ItemValue']['ValueType']}<br/>";
            switch ($row['ItemValue']['ValueType']) {
               case 'Pattern':
                  $t .= "{$row['ItemValue']['Value']['Value']}";
                  break;
               case 'SingleValue':
                  $t .= "{$row['ItemValue']['Value']['Value']}";
                  break;
               case 'Range':
                  $t .= "{$row['ItemValue']['Value']['Range']['Min']} : {$row['ItemValue']['Value']['Range']['Max']}";
                  break;
               case 'List':
                  $list = $row['ItemValue']['Value']['List'];
                  if (is_array($list)) foreach ($list as $v) $t .= "{$v}; ";
                  break;
               }
            break;
         case 'LOOKUP':
            $t .= "Lookup values externally:<br/>";
            $a = hda_db::hdadb()->HDA_DB_dictionary($row['ItemValue']['Connection']);
            if (!is_null($a) && is_array($a) && count($a)==1) {
               $t .= "{$a[0]['Name']}";
               $t .= " using query: ".substr($row['ItemValue']['Query'], 0 , 24)." ... ";
               }
            else $t .= "Unable to find connection entry in dictionary";
            break;
         }
      $t .= "</td><td>";
      $mouse = _click_dialog("_dialogAdminNewValidation","_EDIT-{$row['ItemId']}");
      $t .= "<span title=\"Edit definition..\" {$mouse}>";
	  $t .= _emit_image("Edit.jpg",12)."</span>&nbsp;";
      $mouse = _click_dialog($dialog_id,"_DeleteValidation-{$row['ItemId']}");
      $t .= "&nbsp;<span title=\"Delete validation entry..\" {$mouse}>";
	  $t .= _emit_image("DeleteThis.jpg",12)."</span>";
      $t .= "</td></tr>";
      }
   $t .= "</table></div></th></tr>";
   return $t;
   }
   
function _translateLookupFields($ctype) {
   global $_validExportModes;
   global $_validDbMethods;
   $_fields = array();
   switch ($ctype) {
      case 'FTP':
         $_fields['Host'] = array("FTP server url", NULL);
         $_fields['User'] = array("User Login", NULL);
         $_fields['PassW'] = array("User Password",NULL);
         $_fields['Table'] = array("Target Directory",NULL);
         $_fields['Key'] = array("Filename or Filename mask pattern", NULL);
         $_fields['Cleanup'] = array("On detect, delete found file after upload",'CHECK');
		 $_fields['Passive'] = array("Non-passive mode (default is passive, unchecked)",'CHECK');
         break;
      case 'FILE':
         $_fields['Table'] = array("Target site directory",NULL);
         $_fields['Key'] = array("Filename or Filename mask pattern",NULL);
         $_fields['Cleanup'] = array("On detect, delete found file after upload",'CHECK');
         break;
      case 'XML':
         $_fields['Host'] = array("XML server url",NULL);
         $_fields['Key'] = array("Table Name",NULL);
         $_fields['Cleanup'] = array("On detect, delete found file after upload",'CHECK');
         break;
      case 'ODBC':
         $_fields['DSN'] = array("DSN info",NULL);
         $_fields['Host'] = array("DB server url",NULL);
         $_fields['User'] = array("User Login",NULL);
         $_fields['PassW'] = array("User Password",NULL);
         $_fields['Schema'] = array("Database Schema",NULL);
         $_fields['Table'] = array("Target table",NULL);
         break;
      case 'MYSQL':
      case 'MSSQL':
      case 'ORCL':
	  case 'PGSQL':
         $_fields['DSN'] = array("Connection info",NULL);
         $_fields['Host'] = array("DB server url",NULL);
         $_fields['User'] = array("User Login",NULL);
         $_fields['PassW'] = array("User Password",NULL);
         $_fields['Schema'] = array("Database Schema",NULL);
         $_fields['Table'] = array("Target table",NULL);
         break;
      case 'EMAIL':
         $_fields['User'] = array("Email account",NULL);
         $_fields['Table'] = array("Email subject",NULL);
         $_fields['DSN'] = array("Email message",NULL);
         break;
      case 'RSS':
         $_fields['Host'] = array("RSS channel url",NULL);
         break;
      case 'MDX':
         $_fields['DSN'] = array("Connection String",NULL);
         break;
      }
   return $_fields;
   }

function _dialogImportConnections($dialog_id='alc_import_connections') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;

   global $UserCode;
   $problem = null;
   $item = null;
   $import = false;
   $overwrite = PRO_ReadParam("{$dialog_id}_Duplicates");
   if (!isset($overwrite) || is_null($overwrite)) $overwrite='OVERWRITE';
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}":
         $import = true;
         break;
      case "ACTION_{$dialog_id}_Upload":
         $import = true;
         $result = HDA_UploadConnections('UploadConnections', $into_path);
         if (is_null($result)) {
            $ext = strtolower(pathinfo($into_path,PATHINFO_EXTENSION));
            if ($ext<>'xml') $problem = "File upload for connections not of type xml";
            elseif (!file_exists($into_path)) $problem = "Problem with connections file upload";
            else {
               $xml = file_get_contents($into_path);
               $xml_parser = new xmlToArrayParser($xml);
               $item = $xml_parser->array;
               if (is_array($item) && array_key_exists('GlobalConnections', $item)) $item = $item['GlobalConnections'];
               if (is_array($item) && array_key_exists('Lookup', $item)) $item = $item['Lookup'];
               if (!is_array($item)) $problem = "Fails to unpack connections xml file {$item}";
               }
            }
         else $problem = "Problem uploading connection file xml";
         break;
      }
   if (!$import) return "";
   $t = _makedialoghead($dialog_id,"Import Connections");
   if (!is_null($problem)) $t .= "<tr><th style=\"color:red;\" colspan=2 >{$problem}</th></tr>";
   $t .= "<tr><th colspan=2>For duplicate entries:&nbsp;";
   foreach (array('OVERWRITE'=>"Overwrite",'COPY'=>"Make Copy",'SKIP'=>"Skip Entry") as $k=>$p) {
      $checked = ($k==$overwrite)?"CHECKED":"";
      $t .= "<input type=\"radio\" name=\"{$dialog_id}_Duplicates\" value=\"{$k}\" {$checked}>{$p}&nbsp;";
      }
   $t .= "</th></tr>";
   $t .= "<tr><th>Import connections:</th><td><input type=\"file\" name=\"UploadConnections\" \" value=\"\" ></td></tr>";
   $mouse = _click_dialog($dialog_id,"_Upload");
   $t .= "<tr><th colspan=2>";
   $t .= "<span class=\"push_button blue\" {$mouse} >Upload Connections XML</span>"; 
   $t .= "</th></tr>";
   if (is_array($item)) {
      $entries = array();
      if (array_key_exists('LookupId',$item)) $item = array($item);
      foreach ($item as $entry) if (is_array($entry)) {
         if (array_key_exists('cdata',$entry)) unset($entry['cdata']);
         $entries[] = $entry;
         }
      $t .= "<tr><th colspan=2><textarea style=\"height:300px;width:100%;overflow:scroll;resize:none;\" wrap=off >";
      foreach ($entries as $entry) {
         $exists = hda_db::hdadb()->HDA_DB_dictionary(null, $entry['LookupId']);
         $will_write = false;
         $enabled = true;
         $t .= "{$entry['LookupId']}&nbsp;";
         if (!is_null($exists) && is_array($exists) && count($exists)==1) {
            $t .= "- already exists - ";
            if ($overwrite=='SKIP') { $will_write = false; $t .= "skipping"; }
            elseif ($overwrite=='COPY') { $t .= "make copy"; $entry['LookupId'] .= "_COPY"; $will_write = HDA_isUnique('DR'); $enabled = false;}
            else { $t .= "overwrite"; $will_write = $exists[0]['ItemId']; $enabled = $exists[0]['Definition']['enabled']; }
            }
         else { $t .= "- new entry -"; $will_write = HDA_isUnique('DR'); $enabled = $entry['Enabled']; }
         if ($will_write!==false) {
            $item_value = array();
            $item_value['UserItem'] = $UserCode;
            $item_value['IssuedDate'] = hda_db::hdadb()->PRO_DB_dateNow();
            $item_value['Name'] = $entry['LookupId'];
            $item_value['ItemText'] = (is_string($entry['LookupDesc']))?$entry['LookupDesc']:"";
            $item_value['Definition'] = array();
            $ctype = $entry['ConnectType'];
            $item_value['Definition']['Connect Type'] = $ctype;
            $item_value['Definition']['enabled'] = $enabled;
            $fields =  _translateLookupFields($ctype);
            foreach ($fields as $k=>$p) {
               if (array_key_exists($k, $entry) && (!is_array($entry[$k]) || count($entry[$k])>0)) {
			      $item_value['Definition'][$k] = $entry[$k];
				  }
               }
            if (hda_db::hdadb()->HDA_DB_dictionary($will_write, $entry['LookupId'], $item_value)) $t .= " ok ";
            else $t .= " fails in DB update ";
            }
         $t .= "\n";
         }
      $t .= "</textarea></th></tr>";
      }
   $t .= _makedialogclose();

   return $t;
   }


function _dialogExportConnections($dialog_id='alc_export_connections') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;

   $problem = null;
   $item = null;
   $export = false;
   switch ($Action) {
      default:return "";
      case 'ACTION_alc_export_connections':
         $export = true;
         break;
      }
   if (!$export) return "";
   $t = _makedialoghead($dialog_id,"Export Global Connection");
   $xml = "<GlobalConnections>\n";
   $a = hda_db::hdadb()->HDA_DB_dictionary(null, null);
   if (is_null($a) || !is_array($a) || count($a)==0) $problem = "Problem reading connection entries, or no entries";
   else foreach($a as $row) {
      $xml .= "  <Lookup>\n";
      $xml .= "    <LookupId>{$row['Name']}</LookupId>\n";
      $xml .= "    <LookupDesc>{$row['ItemText']}</LookupDesc>\n";
      $value_item = $row['Definition'];
      $xml .= "    <ConnectType>{$value_item['Connect Type']}</ConnectType>\n";
	  $xml .= "    <Enabled>{$value_item['enabled']}</Enabled>\n";
      $fields = _translateLookupFields($value_item['Connect Type']);
      foreach($fields as $k=>$p) {
         if (array_key_exists($k, $value_item)) $xml .= "    <{$k}>{$value_item[$k]}</{$k}>\n";
         }
      $xml .= "  </Lookup>\n";
      }
   $xml .= "</GlobalConnections>\n";
   if (!is_null($problem)) $t .= "<tr><th style=\"color:red;\" colspan=2 >{$problem}</th></tr>";
   if (!is_null($a) && is_array($a)) {
      $t .= "<tr><th colspan=2><textarea style=\"width:100%;height:200px;overflow:auto;resize:none;\" wrap=off >{$xml}</textarea></th></tr>";
      }
   $lib_dir = "tmp/";
   $lib_dir .= "GlobalConnections";
   if (!@file_exists($lib_dir)) mkdir($lib_dir);
   $lib_path = "{$lib_dir}/connections.xml";
   @file_put_contents($lib_path, $xml); _chmod($lib_path);
   $t .= "<tr><th colspan=2><a href=\"{$lib_path}\" target=\"_blank\" >Download</a></th></tr>";
   $t .= _makedialogclose();

   return $t;
   }

function _dialogAdminEditDef($dialog_id='alc_admin_edit_def') {
   global $Action;
   global $ActionLine;
   global $UserCode;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;

   $problem = null;
   $def_item = null;
   $a = null;
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}_NEW":
         $a = array();
         $a['Name'] = "New Definition Name";
         $a['ItemText'] = "";
		 $a['ItemId'] = $def_item = HDA_isUnique('DR');
         $def = array();
         $def['Connect Type'] = "FTP";
         $def['enabled'] = 0;
         $def['Host'] = "";
         $def['PassW'] = "";
         $def['User'] = "";
         $def['DSN'] =  "";
         $def['Table'] =  "";
         $def['Schema'] =  "";
         $def['Method'] = "";
         $def['Key'] = "";
		 $def['Cleanup'] = "";
         $a['Definition'] = $def;
         $a = array($a);
		 break;
      case "ACTION_{$dialog_id}_EDIT":
         list($action, $def_item) = explode('-',$ActionLine);
         break;
      case "ACTION_{$dialog_id}_TestDef":
      case "ACTION_{$dialog_id}_Refresh":
      case "ACTION_{$dialog_id}_Save":
         list($action, $def_item) = explode('-',$ActionLine);
         $a = hda_db::hdadb()->HDA_DB_dictionary($def_item);
         if (is_null($a)|| !is_array($a) || count($a)<> 1) {
		    $a = array();
			$a[0]['ItemId'] = $def_item;
			$a[0]['Definition'] = array();
			$a[0]['UserItem'] = $UserCode;
			$a[0]['IssuedDate'] = hda_db::hdadb()->PRO_DB_dateNow();
		    }
         $def = $a[0]['Definition'];
         $a[0]['Name'] = PRO_ReadParam("{$dialog_id}_Name");
         $a[0]['ItemText'] = PRO_ReadParam("{$dialog_id}_ItemText");
         $ctype = PRO_ReadParam("{$dialog_id}_CTYPE");
         $def['Connect Type'] = $ctype;
         $def['enabled'] = PRO_ReadAndClear("{$dialog_id}_enabled");
         $fields = _translateLookupFields($ctype);
         foreach($fields as $k=>$p) {
            switch ($k) {
               default:
                  $def[$k] = PRO_ReadParam("{$dialog_id}_{$k}");
                  break;
               }
            }
         $a[0]['Definition'] = $def;
         hda_db::hdadb()->HDA_DB_dictionary($def_item, NULL, $a[0]);
         break;
      }
   switch ($Action) {
      case "ACTION_{$dialog_id}_Save":
	     return "";
		 break;
      case "ACTION_{$dialog_id}_TestDef":
         list($action, $def_item) = explode('-',$ActionLine);
         HDA_validateConnection($def_item, $problem);
         if (is_null($problem) || strlen($problem)==0) $problem = "Tested ok";
         break;
      }
   
   if (is_null($def_item)) return "";
   if (is_null($a)) $a = hda_db::hdadb()->HDA_DB_dictionary($def_item);
   if (is_null($a)|| !is_array($a) || count($a)<> 1) $problem = "Fails to find dictionary entry {$def_item}";
   $t = _makedialoghead($dialog_id,"Edit Connection Definition");
   if (!is_null($problem)) $t .= "<tr><th colspan=2 style=\"color:red;\" >{$problem}</th></tr>";
   if (!is_null($a) && is_array($a) && count($a)==1) {
      $row = $a[0];
      $t .= "<tr><td class=\"head\" colspan=2>Name:&nbsp;<input type=\"text\" {$key_mouse}   class=\"alc-intext\" name=\"{$dialog_id}_Name\" value=\"{$row['Name']}\"></td></tr>";
      $t .= "<tr><td colspan=2>";
      $t .= "<textarea name=\"{$dialog_id}_ItemText\" style=\"width:100%;height:80px;overflow:scroll;\">{$row['ItemText']}</textarea>";
      $t .= "</td></tr>";
      $def = $row['Definition'];
      $ctype = (array_key_exists('Connect Type',$def))?$def['Connect Type']:"Unknown";
      $t .= "<tr><td colspan=2>";
	  $t .= "<table class=\"alc-table\">";
      global $_validLookups;
      $mouse = _click_dialog($dialog_id,"_Refresh-{$def_item}");
	  $on_odd = 0;
      foreach ($_validLookups as $lookupType=>$lookupType_s) {
         $checked = ($ctype==$lookupType)?"CHECKED":"";
		 if ($on_odd==0) $t .= "<tr>";
         $t .= "<td><input type=\"radio\" name=\"{$dialog_id}_CTYPE\" value=\"{$lookupType}\" {$mouse} {$checked}>&nbsp;{$lookupType_s}</td>";
		 if ($on_odd==1) $t .= "</tr>";
		 $on_odd = (($on_odd+1)&1);
         }
	  $t .= "</table>";
      $t .= "</td></tr>";
      $t .= "<tr><th colspan=2>";
      $checked = ($def['enabled']<>1)?"CHECKED":"";
      $t .= "<input type=\"radio\" name=\"{$dialog_id}_enabled\" value=\"0\" {$checked}>&nbsp;Disable Lookup";
      $checked = ($def['enabled']==1)?"CHECKED":"";
      $t .= "&nbsp;&nbsp;<input type=\"radio\" name=\"{$dialog_id}_enabled\" value=\"1\" {$checked}>&nbsp;Enable Lookup";
      $t .= "</th></tr>";
      $fields = _translateLookupFields($ctype);
      foreach ($fields as $k=>$p) {
         $t .= "<tr><th>{$fields[$k][0]}</th>";
         $v = (array_key_exists($k, $def))?$def[$k]:"";
         switch($k) {
            case 'PassW':
               $t .= "<td><input type=\"password\" name=\"{$dialog_id}_{$k}\" value=\"{$v}\" ></td>";
               break;
            default: 
               if (is_null($fields[$k][1]))
                  $t .= "<td><input type=\"text\" {$key_mouse}   class=\"alc-dialog-name\" name=\"{$dialog_id}_{$k}\" value=\"{$v}\" ></td>";
               elseif ($fields[$k][1]=='CHECK') {
                  $checked = ($v==1)?"CHECKED":"";
                  PRO_Clear("{$dialog_id}_{$k}");
                  $t .= "<td><input type=\"checkbox\" name=\"{$dialog_id}_{$k}\" value=\"1\" {$checked}></td>";
                  }
               elseif (is_array($fields[$k][1])) {
                  $selectFrom = $fields[$k][1];
                  $t .= "<td><select name=\"{$dialog_id}_{$k}\" >";
                  foreach ($selectFrom as $kk=>$pp) {
                     $selected = ($kk==$v)?"SELECTED":"";
                     $t .= "<option value=\"{$kk}\" {$selected} >{$pp}</option>";
                     }
                  $t .= "</select></td>";
                  }
               break;
            }
         $t .= "</tr>";
         }
      }
   $mouse = _click_dialog($dialog_id,"_TestDef-{$def_item}");
   $t .= "<tr><th colspan=2>";
   $t .= "<span class=\"push_button blue\" {$mouse} >Test connection</span>"; 
   $t .= "</th></tr>";
   $t .= _closeDialog($dialog_id, "_Save-{$def_item}---", 2);
   $t .= _makedialogclose();

   return $t;
   }

function _dialogAdminNewValidation($dialog_id='alc_admin_new_validation') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;



   $problem = null;
   $lookupid = $item = null;
   $value = null;
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}":
         break;
      case "ACTION_{$dialog_id}_TEST":
         $lookupid = PRO_ReadParam("{$dialog_id}_LookupId");
         $test_value = PRO_ReadParam("{$dialog_id}_TestValue");
         if (HDA_validate($lookupid, $test_value, $problem)) $problem = "Validated OK";
      case "ACTION_{$dialog_id}_REFRESH":
         $value = array();
         $value['Method'] = PRO_ReadParam("{$dialog_id}_Method");
         $value['ValueType'] = PRO_ReadParam("{$dialog_id}_ValueType");
         $lookupid = PRO_ReadParam("{$dialog_id}_LookupId");
         $item = PRO_ReadParam("{$dialog_id}_ItemId");
         $value['Value'] = array();
         $value['Value']['Value'] = PRO_ReadParam("{$dialog_id}_V"); 
         $value['Value']['Range'] = array();
         $value['Value']['Range'] = array('Min'=>PRO_ReadParam("{$dialog_id}_Vmin"),'Max'=>PRO_ReadParam("{$dialog_id}_Vmax")); 
         $list = explode("\n",PRO_ReadParam("{$dialog_id}_List"));
         $value['Value']['List'] = $list;
         $value['Connection'] = PRO_ReadParam("{$dialog_id}_Connection");
         $value['Query'] = PRO_ReadParam("{$dialog_id}_Query");
         break;
      case "ACTION_{$dialog_id}_NEW":
         $item = HDA_isUnique('VC');
         PRO_FindAndClear("{$dialog_id}_");
         $lookupid = "ValidationIdent";
         $value = array();
         $value['Method'] = 'ACTUAL';
         $value['ValueType'] = 'SingleValue';
         $value['Value'] = array();
         $value['Value']['Value'] = "undefined";
         $value['Value']['List'] = array();
         $value['Value']['Range'] = array();
         $value['Value']['Range']['Min']=0; $value['Value']['Range']['Max']=1;
         $value['Connection'] = $value['Query'] = "";
         break;
      case "ACTION_{$dialog_id}_EDIT":
         list($action, $item) = explode('-',$ActionLine);
         PRO_FindAndClear("{$dialog_id}_");
         $a = hda_db::hdadb()->HDA_DB_validationCode(null, null, $item);
         if (!is_array($a) || count($a)<>1) $problem = "Problem reading validation code";
         else { $lookupid = $a[0]['LookupId']; $value = $a[0]['ItemValue']; }
         break;
      case "ACTION_{$dialog_id}_Save":
         $item = PRO_ReadParam("{$dialog_id}_ItemId");
         $value = array();
         $value['Method'] = PRO_ReadParam("{$dialog_id}_Method");
         $value['ValueType'] = PRO_ReadParam("{$dialog_id}_ValueType");
         switch ($value['Method']) {
            case 'ACTUAL':
               switch ($value['ValueType']) {
                  case 'Pattern':
                  case 'SingleValue': $value['Value']['Value'] = PRO_ReadParam("{$dialog_id}_V"); break;
                  case 'Range': $value['Value']['Range'] = array('Min'=>PRO_ReadParam("{$dialog_id}_Vmin"),'Max'=>PRO_ReadParam("{$dialog_id}_Vmax")); break;
                  case 'List': 
                     $list = explode("\n",PRO_ReadParam("{$dialog_id}_List"));
                     $value['Value']['List'] = $list;
                     break;
                  }
               break;
            case 'LOOKUP':
               $value['Connection'] = PRO_ReadParam("{$dialog_id}_Connection");
               $value['Query']= PRO_ReadParam("{$dialog_id}_Query");
               break;
            }
         $a = hda_db::hdadb()->HDA_DB_validationCode($lookupid = PRO_ReadParam("{$dialog_id}_LookupId"), $value, $item);
         if ($a===false) $problem = "Problem saving validation definition";
         break;
      }
   if (is_null($value)) return "";
   PRO_AddToParams("{$dialog_id}_ItemId", $item);
   $t = _makedialoghead($dialog_id,"Edit Validation Definition");
   if (!is_null($problem)) $t .= "<tr><th colspan=2 style=\"color:red;\" >{$problem}</th></tr>";
   $select_change = _change_dialog($dialog_id,"_REFRESH");
   $t .= "<tr><td class=\"head\" colspan=2>Name:&nbsp;";
   $t .= "<input type=\"text\" {$key_mouse}  class=\"alc-intext\" name=\"{$dialog_id}_LookupId\" value=\"{$lookupid}\"></td></tr>";
   $t .= "<tr><td>Where are the values?<br/><select name=\"{$dialog_id}_Method\" {$select_change} >";
   foreach (array('ACTUAL'=>"Values defined here",'LOOKUP'=>"Values defined externally") as $method=>$method_caption) {
      $selected = ($method==$value['Method'])?"SELECTED":"";
      $t .= "<option value=\"{$method}\" {$selected}>{$method_caption}</option>";
      }
   $t .= "</select></td>";
   $t .= "<td>What type of value?<br/><select name=\"{$dialog_id}_ValueType\"  {$select_change} >";
   foreach (array('SingleValue'=>"Single Value", 'Range'=>"Range of values", 'List'=>"List of values", 'Pattern'=>"Pattern match") as $k=>$p) {
      $selected = ($k==$value['ValueType'])?"SELECTED":"";
      $t .= "<option value=\"{$k}\" {$selected}>{$p}</option>";
      }
   $t .= "</select></td>";
   switch ($value['Method']) {
      case 'ACTUAL':
         switch ($value['ValueType']) {
            case 'Pattern':
            case 'SingleValue': 
               $t .= "<tr><td colspan=2>";
               $t .= "Value:<br/><input type=\"text\" {$key_mouse}  class=\"alc-intext\" value=\"{$value['Value']['Value']}\" name = \"{$dialog_id}_V\">"; 
               $t .= "</td></tr>";
               break;
            case 'Range': 
               $t .= "<tr><td colspan=2>";
               $t .= "Min Value:&nbsp;<input type=\"text\" {$key_mouse}  class=\"alc-intext\" value=\"{$value['Value']['Range']['Min']}\" name = \"{$dialog_id}_Vmin\">"; 
               $t .= "</td></tr>";
               $t .= "<tr><td colspan=2>";
               $t .= "Max Value:&nbsp;<input type=\"text\" {$key_mouse}  class=\"alc-intext\" value=\"{$value['Value']['Range']['Max']}\" name = \"{$dialog_id}_Vmax\">"; 
               $t .= "</td></tr>";
               break;
            case 'List': 
               $t .= "<tr><td colspan=2>List each value on a line:<br>";
               $t .= "<textarea rows=6 cols=50 name=\"{$dialog_id}_List\">";
               if (isset($value['Value']['List']) && is_array($value['Value']['List'])) foreach ($value['Value']['List'] as $v) {
                  $t .= "{$v}\n";
                  }
               $t .= "</textarea>";
               $t .= "</td></tr>";
               break;
            }
         break;
      case 'LOOKUP':
         $t .= "<tr><td colspan=2>Connection Name:<br/>";
         $a = hda_db::hdadb()->HDA_DB_dictionary();
         if (!is_null($a) && is_array($a) && count($a)>0) {
            $t .= "<select name=\"{$dialog_id}_Connection\" >";
            $t .= "<option value=\"\" SELECTED>No connection selected</option>";
            foreach ($a as $row) {
               $selected = ($row['ItemId']==$value['Connection'])?"SELECTED":"";
               $t .= "<option value=\"{$row['ItemId']}\" {$selected}>{$row['Name']}</option>";
               }
            $t .= "</select>";
            $t .= "<br/>Enter query (for database lookups) to return single column table:<br/>";
            $t .= "<textarea name=\"{$dialog_id}_Query\" style=\"width:100%;height:80px;\" >{$value['Query']}</textarea>";
            }
         else {
            $t .= "No connection dictionary entries found";
            }
         $t .= "</td></tr>";
         break;
      }
   $t .= "<tr><th class=\"head\" colspan=2>Test Validation</th></tr>";
   $t .= "<tr><td colspan=2>Value: <input type=\"text\" {$key_mouse}  class=\"alc-intext\" name=\"{$dialog_id}_TestValue\" >";
   $mouse = _click_dialog($dialog_id,"_TEST");
   $t .= "<span class=\"push_button blue\" {$mouse} >Test</span>"; 
   $t .= "</td></tr>";
   $t .= _closeDialog($dialog_id, true, 2);
   $t .= _makedialogclose();

   return $t;
   }

function _dialogImportValidations($dialog_id='alc_import_validations') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;

   $problem = null;
   $item = null;
   $import = false;
   $overwrite = PRO_ReadParam("{$dialog_id}_Duplicates");
   if (!isset($overwrite) || is_null($overwrite)) $overwrite='OVERWRITE';
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}":
         $import = true;
         break;
      case "ACTION_{$dialog_id}_Upload":
         $import = true;
         $result = HDA_UploadValidations('UploadValidations', $into_path);
         if (is_null($result)) {
            $ext = strtolower(pathinfo($into_path,PATHINFO_EXTENSION));
            if ($ext<>'xml') $problem = "File upload for validations not of type xml";
            elseif (!file_exists($into_path)) $problem = "Problem with validations file upload";
            else {
               $xml = file_get_contents($into_path);
               $xml_parser = new xmlToArrayParser($xml);
               $item = $xml_parser->array;
               if (is_array($item) && array_key_exists('GlobalValidations', $item)) $item = $item['GlobalValidations'];
               if (is_array($item) && array_key_exists('Lookup', $item)) $item = $item['Lookup'];
               if (!is_array($item)) $problem = "Fails to unpack validations xml file {$item}";
               }
            }
         else $problem = "Problem uploading validation file xml";
         break;
      }
   if (!$import) return "";
   $t = _makedialoghead($dialog_id,"Import Validation");
   if (!is_null($problem)) $t .= "<tr><th style=\"color:red;\" colspan=2 >{$problem}</th></tr>";
   $t .= "<tr><th colspan=2>For duplicate entries:&nbsp;";
   foreach (array('OVERWRITE'=>"Overwrite",'COPY'=>"Make Copy",'SKIP'=>"Skip Entry") as $k=>$p) {
      $checked = ($k==$overwrite)?"CHECKED":"";
      $t .= "<input type=\"radio\" name=\"{$dialog_id}_Duplicates\" value=\"{$k}\" {$checked}>{$p}&nbsp;";
      }
   $t .= "</th></tr>";
   $t .= "<tr><th>Import validations:</th><td><input type=\"file\" name=\"UploadValidations\" \" value=\"\" ></td></tr>";
   $mouse = _click_dialog($dialog_id,"_Upload");
   $t .= "<tr><th colspan=2>";
   $t .= "<span class=\"push_button blue\" {$mouse} >Upload Validation XML</span>"; 
   $t .= "</th></tr>";
   if (is_array($item)) {
      $entries = array();
      if (array_key_exists('LookupId',$item)) $item = array($item);
      foreach ($item as $entry) if (is_array($entry)) {
         if (array_key_exists('cdata',$entry)) unset($entry['cdata']);
         $entries[] = $entry;
         }
      $t .= "<tr><th colspan=2><textarea style=\"height:300px;width:100%;overflow:scroll;resize:none;\" wrap=off >";
      foreach ($entries as $entry) {
         $exists = hda_db::hdadb()->HDA_DB_validationCode($entry['LookupId']);
         $will_write = false;
         $t .= "{$entry['LookupId']}&nbsp;";
         if (!is_null($exists) && is_array($exists) && count($exists)==1) {
            $t .= "- already exists - ";
            if ($overwrite=='SKIP') { $will_write = false; $t .= "skipping"; }
            elseif ($overwrite=='COPY') { $t .= "make copy"; $entry['LookupId'] .= "_COPY"; $will_write = HDA_isUnique('VC'); }
            else { $t .= "overwrite"; $will_write = $exists[0]['ItemId']; }
            }
         else { $t .= "- new entry -"; $will_write = HDA_isUnique('VC'); }
         if ($will_write!==false) {
            $item_value = array();
            $item_value['Method'] = $entry['Method'];
            $item_value['ValueType'] = $entry['ValueType'];
            $item_value['Value'] = array();
            $item_value['Value']['Value'] = (array_key_exists('Value',$entry))?$entry['Value']:"";
            $item_value['Value']['Range'] = array();
            $item_value['Value']['Range']['Min'] = (array_key_exists('Range',$entry) && array_key_exists('Min',$entry['Range']))?$entry['Range']['Min']:-1;
            $item_value['Value']['Range']['Max'] = (array_key_exists('Range',$entry) && array_key_exists('Max',$entry['Range']))?$entry['Range']['Max']:-1;
            $item_value['Value']['List'] = (array_key_exists('List',$entry))?$entry['List']:array();
            $item_value['Connection'] = "";
            if (array_key_exists('Connection',$entry) && strlen($entry['Connection'])>0) {
               $re_link = $entry['Connection'];
               $t .= "- re link connection {$re_link} - ";
               $d_link = hda_db::hdadb()->HDA_DB_dictionary(null, $re_link);
               if (!is_null($d_link) && is_array($d_link) && count($d_link)==1) $item_value['Connection'] = $d_link[0]['ItemId'];
               }
            $item_value['Query'] = (array_key_exists('Query',$entry))?$entry['Query']:"";
            if (hda_db::hdadb()->HDA_DB_validationCode($entry['LookupId'], $item_value, $will_write)) $t .= " ok ";
            else $t .= " fails in DB update ";
            }
         $t .= "\n";
         }
      $t .= "</textarea></th></tr>";
      }
   $t .= _makedialogclose();

   return $t;
   }


function _dialogExportValidations($dialog_id='alc_export_validations') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;

   $problem = null;
   $item = null;
   switch ($Action) {
      default:return "";
      case 'ACTION_alc_export_validations':
         break;
      }
   $t = _makedialoghead($dialog_id, "Export Validations");
   $xml = "<GlobalValidations>\n";
   $a = hda_db::hdadb()->HDA_DB_validationCode(null, null);
   if (is_null($a) || !is_array($a) || count($a)==0) $problem = "Problem reading validation entries, or no entries";
   else foreach($a as $row) {
      $xml .= "  <Lookup>\n";
      $xml .= "    <LookupId>{$row['LookupId']}</LookupId>\n";
      $value_item = $row['ItemValue'];
      $xml .= "    <Method>{$value_item['Method']}</Method>\n";
      $xml .= "    <ValueType>{$value_item['ValueType']}</ValueType>\n";
      if (array_key_exists('Value',$value_item)) {
	     if (array_key_exists('Value',$value_item['Value'])) $xml .= "    <Value>{$value_item['Value']['Value']}</Value>\n";
		 if (array_key_exists('Range',$value_item['Value'])) {
            $xml .= "    <Range>\n";
            $xml .= "      <Min>{$value_item['Value']['Range']['Min']}</Min>\n";
            $xml .= "      <Max>{$value_item['Value']['Range']['Max']}</Max>\n";
            $xml .= "    </Range>\n";
			}
		 if (array_key_exists('List',$value_item['Value'])) {
			foreach ($value_item['Value']['List'] as $v) $xml .= "      <List>".trim($v)."</List>\n";
			}
         }
      if (array_key_exists('Connection',$value_item)) {
         $c = hda_db::hdadb()->HDA_DB_dictionary($value_item['Connection']);
         if (!is_null($c) && is_array($c) && count($c)==1) {
            $xml .= "    <Connection>{$c[0]['Name']}</Connection>\n";
            if (strlen($value_item['Query'])>0) $xml .= "    <Query>{$value_item['Query']}</Query>\n";
            }
         }
      $xml .= "  </Lookup>\n";
      }
   $xml .= "</GlobalValidations>\n";
   if (!is_null($problem)) $t .= "<tr><th style=\"color:red;\" colspan=2 >{$problem}</th></tr>";
   if (!is_null($a) && is_array($a)) {
      $t .= "<tr><th colspan=2><textarea style=\"width:100%;height:200px;overflow:auto;resize:none;\" wrap=off >{$xml}</textarea></th></tr>";
      }
   $lib_dir = "tmp/";
   $lib_dir .= "GlobalValidations";
   if (!@file_exists($lib_dir)) mkdir($lib_dir);
   $lib_path = "{$lib_dir}/validations.xml";
   @file_put_contents($lib_path, $xml); _chmod($lib_path);
   $t .= "<tr><th colspan=2><a href=\"{$lib_path}\" target=\"_blank\" >Download</a></th></tr>";
   $t .= _makedialogclose();

   return $t;
   }

function _dialogBlockoutList($dialog_id='alc_blockout_list') {
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
	  case "ACTION_{$dialog_id}_Save":
         list ($action, $item) = explode('-',$ActionLine);
		 $a = PRO_ReadParam("{$dialog_id}_list");
		 if (is_array($a)) {
		    $dates = 0;
			foreach($a as $i) $dates |= (1<<$i);
			hda_db::hdadb()->HDA_DB_relationDataDates($item, $dates);
		    }
         break;	  
      }
   PRO_Clear("{$dialog_id}_list");
   $t = _makedialoghead($dialog_id, "Blockout Data Days");
   $a = hda_db::hdadb()->HDA_DB_admin('BLOCKDATES');
   if (!is_null($a)) $a = hda_db::hdadb()->HDA_DB_unserialize($a);
   $dates = hda_db::hdadb()->HDA_DB_relationDataDates($item);
   if (is_null($a)) $t .= "<tr><td>No blockout dates enabled</td></tr>";
   else foreach($a as $i=>$p) {
      $checked = (($dates & (1<<$i))!=0)?"CHECKED":"";
      $t .= "<tr><td>{$p[0]}</td><td><input type=\"checkbox\" name=\"{$dialog_id}_list[]\" value=\"{$i}\" {$checked}></td></tr>";
      }
   $t .= _closeDialog($dialog_id, "_Save-{$item}---", 2);
   $t .= _makedialogclose();

   return $t;
   }

function _dialogConsoleLog($dialog_id='alc_show_console') {
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
   $t = _makedialoghead($dialog_id, "Console Log");
   $t .= "<tr><td><textarea style=\"width:100%;height:300px;overflow:auto;\" >";
   $dir = HDA_WorkingDirectory($item);
   $s = (@file_exists("{$dir}/console.log"))?@file_get_contents("{$dir}/console.log"):"--No console log--";
   $t .= $s;
   $t .= "</textarea></td></tr>";
   $t .= _makedialogclose();

   return $t;
   }
function _dialogDebugLog($dialog_id='alc_show_debug') {
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
   $t = _makedialoghead($dialog_id, "Debug Log");
   $t .= "<tr><td><textarea style=\"width:100%;height:300px;overflow:auto;\" >";
   $dir = HDA_WorkingDirectory($item);
   $s = (@file_exists("{$dir}/debug.log"))?@file_get_contents("{$dir}/debug.log"):"--No debug log--";
   $t .= $s;
   $t .= "</textarea></td></tr>";
   $t .= _makedialogclose();

   return $t;
   }
function _dialogCheckRules($dialog_id='alc_check_rules') {
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
   $t = _makedialoghead($dialog_id, "Rules Check ".hda_db::hdadb()->HDA_DB_TitleOf($item));
   $pass = HDA_CheckRules($item, $log);
   $t .= "<tr><td><textarea style=\"width:100%;height:300px;overflow:auto;\" >";
   foreach($log as $line) {
      $t .= "{$line}\n";
      }
   $t .= "</textarea></td></tr>";
   $t .= _makedialogclose();

   return $t;
   }
   
function _dialogEditText($dialog_id='alc_edit_text') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;

   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
         list ($action, $k, $id, $f) = explode('-',$ActionLine);
		 $item = "{$k}-{$id}-{$f}";
         break;
	  case "ACTION_{$dialog_id}_Save":
         list ($action,  $k, $id, $f) = explode('-',$ActionLine);
		 $item = "{$k}-{$id}-{$f}";
		 PRO_AddToParams($item, hda_db::hdadb()->HDA_DB_textToDB(PRO_ReadParam("{$dialog_id}_txt")));
		 return "";
         break;
      }
   $t = _makedialoghead($dialog_id, "Edit..");
   $t .= "<tr><td><textarea name=\"{$dialog_id}_txt\" style=\"width:100%;height:300px;overflow:auto;\" >";
   $t .= hda_db::hdadb()->HDA_DB_textFromDB(PRO_ReadParam($item));
   $t .= "</textarea></td></tr>";
   $t .= _closeDialog($dialog_id, "_Save-{$item}---");
   $t .= _makedialogclose();

   return $t;
   }


function _codeHelperInner() {
   global $Action;
   global $ActionLine;
   global $code_root, $home_root;
   global $key_mouse;
   global $code_help_dir;
   global $HDA_code_functions;
   global $HDA_code_keywords;
   global $help_host;
   $t = "";

   $t .= "<div class=\"alc-display-box\" style=\"background-color:white;background-image:none;text-align:left;font-size:10px;color:black;\" >";
   $onFunction = PRO_ReadParam('help_on_function');
   $mouse="onchange=\"show_help('help_on_function', '{$help_host}');\" ";
   $t .= "<br>Functions:&nbsp;<select class=\"intext\" id=\"help_on_function\" name=\"help_on_function\" {$mouse} style=\"font-size:10px;color:black;\" >";
   $a = $HDA_code_functions;
   sort($a, SORT_STRING);
   foreach ($a as $k) {
      $selected=($k==$onFunction)?"SELECTED":"";
      $t .= "<option value=\"{$k}\" {$selected}>{$k}</option>";
      }
   $t .= "</select>";
   $onKeyword = PRO_ReadParam('help_on_keyword');
   $mouse="onchange=\"show_help('help_on_keyword', '{$help_host}');\" ";
   $t .= "&nbsp;&nbsp;Keywords:&nbsp;<select class=\"intext\" id=\"help_on_keyword\" name=\"help_on_keyword\" {$mouse} style=\"font-size:10px;color:black;\">";
   $a = $HDA_code_keywords;
   sort($a, SORT_STRING);
   foreach ($a as $k) {
      $selected=($k==$onKeyword)?"SELECTED":"";
      $t .= "<option value=\"{$k}\" {$selected}>{$k}</option>";
      }
   $t .= "</select>";
   $t .= "<br><div id=\"show_code_help\" style=\"width:100%;height:100px;overflow:auto; font_size:10px;color:black; border:1px solid blue;\" >";
   $t .= "</div>";
   $mouse = "onchange=\"show_auto_help('code_helper','help_on_keyword','help_on_function'); return false;\" ";
   $t .= "<input class=\"intext\" type=\"text\" {$key_mouse}  id=\"code_helper\" {$mouse} style=\"visibility:hidden;\" >";
   $t .= "</div>";
   return $t;
   }
function _catch_studio_preg_error($errno, $errmsg) {
   global $_studio_preg_error;
   $_studio_preg_error = $errmsg;
   return true;
   }
$_studio_preg_error = null;

 function _dialogCodeStudio($dialog_id='alc_code_studio') {
   $t = "";
   global $Action;
   global $ActionLine;
   global $UserCode;
   global $UserId;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;
   global $code_help_dir;
   global $common_code_dir;
   global $template_dir;

   $t = "";
   $in_studio = PRO_ReadParam("{$dialog_id}_studio");
   if (is_null($in_studio)) $in_studio = "Common";
   $this_snippet = PRO_ReadParam("{$dialog_id}_snippet");
   $can_revert = false;
   $encoding_s = null;
   $problem = null;
   switch ($Action) {
      default: return "";
      case "ACTION_{$dialog_id}":
	     break;
	  case "ACTION_{$dialog_id}_View";
	     list($action, $in_studio) = explode('-',$ActionLine);
         break;
      case "ACTION_{$dialog_id}_DeleteCommon":
         list($action, $tmp_n) = explode('-',$ActionLine);
         $ff = base64_decode($tmp_n);
         $problem = (@unlink($ff)===false)?"Problem deleting common code {$ff}":"Deleted common code {$ff}";
         break;
	  case "ACTION_{$dialog_id}_run_rx":
         $error_state = error_reporting(0);
         $error_handler = set_error_handler('_catch_studio_preg_error');
	     $ok = @preg_match("/".PRO_ReadParam("{$dialog_id}_rx_pattern")."/", PRO_ReadParam("{$dialog_id}_rx_target"), $matches);
         $error = error_get_last();
		 global $_studio_preg_error;
         $error = "{$error['message']} {$_studio_preg_error}";
         set_error_handler($error_handler);
         error_reporting($error_state);
		 if ($ok === false) $tt = "Fails in match\n {$error}";
		 else $tt = print_r($matches, true);
		 PRO_AddToParams("{$dialog_id}_rx_result",$tt);
	     break;
	  case "ACTION_{$dialog_id}_rx_snippet":
	     $tt = "// ALCODE Snippet generated from Code Studio RexEx ".hda_db::hdadb()->PRO_DBtime_Styledate(time(),true)."\n";
		 $tt .= "\$a = literal {";
		 $tt .= PRO_ReadParam("{$dialog_id}_rx_target");
		 $tt .= "};\n";
		 $tt .= "\$matched = preg_match(\"/".PRO_ReadParam("{$dialog_id}_rx_pattern")."/\", \$a);\n";
		 $tt .= "if \$matched is not false begin\n";
		 $tt .= "   console(\"RegEx matched ok\");\n";
		 $tt .= "   dump(\$matched);\n";
		 $tt .= "   exit true;\n";
		 $tt .= "   end\n";
		 $tt .= "else console(\"RegEx fails to match \"+last_error());\n";
		 $tt .= "exit false;\n";
		 $this_snippet = "{$template_dir}/"._makeUserRef($UserId);
		 if (!@file_exists($this_snippet)) @mkdir($this_snippet);
		 file_put_contents($this_snippet .= "/snippet_rx_".date('Y-m-d-G-i',time()).".alc", $tt);
		 $in_studio = 'Snippets';
	     break;
	  case "ACTION_{$dialog_id}_strtotime_go":
	     $tt = strtotime(PRO_ReadParam("{$dialog_id}_strtotime"));
		 PRO_AddToParams("{$dialog_id}_strtotime_time",$tt);
		 break;
	  case "ACTION_{$dialog_id}_strtotime_snippet":
	     $tt = "// ALCODE Snippet generated from Code Studio String to Time ".hda_db::hdadb()->PRO_DBtime_Styledate(time(),true)."\n";
		 $tt .= "\$a = string_to_time(\"".PRO_ReadParam("{$dialog_id}_strtotime")."\");\n";
		 $tt .= "if \$a is not false begin\n";
		 $tt .= "   console(\"String parsed to a time ok\");\n";
		 $tt .= "   console(\"Unix time is {\$a}, \"+style_datetime(\$a));\n";
		 $tt .= "   exit true;\n";
		 $tt .= "   end\n";
		 $tt .= "else console(\"Fails to parse date  \"+last_error());\n";
		 $tt .= "exit false;\n";
		 $this_snippet = "{$template_dir}/"._makeUserRef($UserId);
		 if (!@file_exists($this_snippet)) @mkdir($this_snippet);
		 file_put_contents($this_snippet .= "/snippet_totime_".date('Y-m-d-G-i',time()).".alc", $tt);
		 $in_studio = 'Snippets';
		 $Action = $ActionLine = "ACTION_{$dialog_id}_snippet_Refresh";
	     break;
	  case "ACTION_{$dialog_id}_date_go":
	     $tt = date(PRO_ReadParam("{$dialog_id}_dateformat"), time());
		 PRO_AddToParams("{$dialog_id}_date", $tt);
		 break;
	  case "ACTION_{$dialog_id}_date_snippet":
	     $tt = "// ALCODE Snippet generated from Code Studio Date ".hda_db::hdadb()->PRO_DBtime_Styledate(time(),true)."\n";
		 $tt .= "\$a = date(\"".PRO_ReadParam("{$dialog_id}_dateformat")."\");\n";
		 $tt .= "if \$a is not false begin\n";
		 $tt .= "   console(\"Date format is valid ok {\$a} \");\n";
		 $tt .= "   exit true;\n";
		 $tt .= "   end\n";
		 $tt .= "else console(\"Date format not valid \"+last_error());\n";
		 $tt .= "exit false;\n";
		 $this_snippet = "{$template_dir}/"._makeUserRef($UserId);
		 if (!@file_exists($this_snippet)) @mkdir($this_snippet);
		 file_put_contents($this_snippet .= "/snippet_date_".date('Y-m-d-G-i',time()).".alc", $tt);
		 $in_studio = 'Snippets';
		 $Action = $ActionLine = "ACTION_{$dialog_id}_snippet_Refresh";
	     break;
	  case "ACTION_{$dialog_id}_snippet_new":
	  case "ACTION_{$dialog_id}_snippet":
      case "ACTION_{$dialog_id}_snippet_Refresh":
      case "ACTION_{$dialog_id}_snippet_Layout":
      case "ACTION_{$dialog_id}_snippet_Save":
	     break;
      case "ACTION_{$dialog_id}_Encoding":
         list($action, $up_path) = explode('-',$ActionLine);
		 PRO_Clear("{dialog_id}_EncodingS");
         try {
           if (isset($_FILES) && isset($_FILES[$up_path]) &&
              strlen($_FILES[$up_path]['name'])>0 && isset($_FILES[$up_path]['tmp_name']) && strlen($_FILES[$up_path]['tmp_name'])>0) {
              $loc_path = "tmp/{$UserCode}"; if (!file_exists($loc_path)) mkdir($loc_path);
              move_uploaded_file($_FILES[$up_path]['tmp_name'],$ff="{$loc_path}/encoding.txt");
              $encoding_s =  file_get_contents($ff);
			  PRO_AddToParams("{$dialog_id}_EncodingS", $encoding_s);
              }
           else { $problem = "Problem in upload - maybe, file too big "; $problem .= print_r($_FILES, true); }
           }
         catch (Exception $e) {
           $problem = "Problem in upload - ".$e->getMessage();
           }
         break;
      case "ACTION_{$dialog_id}_EncodingRefresh":
	     break;
	  case "ACTION_{$dialog_id}_ShowDoc":
	  case "ACTION_{$dialog_id}_ShowFun":
	  case "ACTION_{$dialog_id}_LibFunc":
	  case "ACTION_{$dialog_id}_ToggleDocWin":
	  case "ACTION_{$dialog_id}_Category":
	  case "ACTION_{$dialog_id}_Function":
	     break;
	  }
   switch ($Action) {
	  case "ACTION_{$dialog_id}_snippet_new":
	     $f = "{$template_dir}/"._makeUserRef($UserId);
		 if (!file_exists($f)) @mkdir($f);
		 $f .= "/Snippet_".date('Y-m-d-G-i',time()).".alc";
		 file_put_contents($f, "// ALCODE Snippet generated ".hda_db::hdadb()->PRO_DBtime_Styledate(time(),true)."\n");
		 $this_snippet = $f;
	     break;
	  case "ACTION_{$dialog_id}_snippet":
	     list($action, $enc, $do) = explode('-',$ActionLine);
		 switch ($do) {
		    case 0:
			case 1: $this_snippet = base64_decode($enc);
			   break;
			case 2: @unlink(base64_decode($enc));
			   $this_snippet = null;
			   break;
			}
      case "ACTION_{$dialog_id}_snippet_Refresh":
	     if (!is_null($this_snippet)) {
            PRO_Clear("{$dialog_id}_snippet_DEV_Text");
            PRO_AddToParams("{$dialog_id}_snippet_CodeText", str_replace(array("\xe2\x80\x9c","\xe2\x80\x9d","\xe2\x80\x98","\xe2\x80\x99"), array("\"","\"","'","'"), file_get_contents($this_snippet)));
            }
         break;
      case "ACTION_{$dialog_id}_snippet_Layout":
		 $filename = pathinfo($this_snippet, PATHINFO_FILENAME);
		 $path = pathinfo($this_snippet, PATHINFO_DIRNAME);
		 $basename = pathinfo($this_snippet, PATHINFO_BASENAME);
		 $new_name = PRO_ReadParam("{$dialog_id}_snippet_name");
		 if (strcmp($new_name,$filename)<>0) {
		    if (@rename($this_snippet, "{$path}/{$new_name}.alc")===true) {
			   $filename = $new_name;
			   $basename = "{$new_name}.alc";
			   $this_snippet = "{$path}/{$basename}";
			   }
			}
         @file_put_contents($this_snippet, PRO_ReadParam("{$dialog_id}_snippet_CodeText"));_chmod($this_snippet);
         $s = HDA_CodeLayout($path, $basename);
         PRO_AddToParams("{$dialog_id}_snippet_CodeText", str_replace(array("\xe2\x80\x9c","\xe2\x80\x9d","\xe2\x80\x98","\xe2\x80\x99"), array("\"","\"","'","'"), $s));
         $can_revert = true;
         break;
      case "ACTION_{$dialog_id}_snippet_Save":
		 $filename = pathinfo($this_snippet, PATHINFO_FILENAME);
		 $path = pathinfo($this_snippet, PATHINFO_DIRNAME);
		 $basename = pathinfo($this_snippet, PATHINFO_BASENAME);
		 $new_name = PRO_ReadParam("{$dialog_id}_snippet_name");
		 if (strcmp($new_name,$filename)<>0) {
		    if (@rename($this_snippet, "{$path}/{$new_name}.alc")===true) {
			   $filename = $new_name;
			   $basename = "{$new_name}.alc";
			   $this_snippet = "{$path}/{$basename}";
			   }
			}
         @file_put_contents($this_snippet, PRO_ReadParam("{$dialog_id}_snippet_CodeText"));_chmod($this_snippet);
		 $the_log = "";
		 $params_list = array();
		 $process = array('ItemId'=>"{$UserCode}-snippet",'Title'=>$filename);
         $the_result = HDA_CompilerExecute($process, $path, $basename, $params_list, $and_run=3, $the_log);
         PRO_AddToParams("{$dialog_id}_snippet_DEV_Text", "{$the_log}"); 
         break;
		    
      }
   PRO_AddToParams("{$dialog_id}_snippet",$this_snippet);
   PRO_AddToParams("{$dialog_id}_studio", $in_studio);
   $t .= _makedialoghead($dialog_id, "Code Studio", "alc-dialog-vlarge");
   if (!is_null($problem)) $t .= "<tr><th style=\"color:red;\">{$problem}</th></tr>";
   $t .= "<tr><td class=\"buttons\">";
   foreach (array("Common","RegEx","Dates","Snippets", "Encoding", "Functions") as $sel_in_studio) {
      $class = ($in_studio==$sel_in_studio)?"alc-subcmd-selected":"alc-subcmd";
	  $mouse = _click_dialog($dialog_id,"_View-{$sel_in_studio}");
      $t .= "&nbsp;<span class=\"click-here {$class}\" {$mouse}>{$sel_in_studio}</span>&nbsp;";
	  $t .= "&nbsp;"._emit_image("SeparatorBar.jpg",12)."&nbsp;";
	  }
   $t .= "</td></tr>";
   switch ($in_studio) {
      case 'Common':
         $t .= "<tr><td class=\"buttons\" >";
         $mouse = _click_dialog("_dialogAdminUploadCommon");
         $t .= "<span style=\"cursor:pointer;height:16px;\" title=\"Upload common code..\" {$mouse}>";
         $t .= _emit_image("UploadHere.jpg",16)."</span>";
         $t .= "</td></tr>";
         $t .= "<tr><th><div style=\"width:90%; height:300px; overflow-y:auto; overflow-x:hidden;\" ><table class=\"alc-table\">";
         $t .= "<tr><th>Name</th><th>hdoc</th><th>Updated</th><th>Size</th><th>&nbsp;</th></tr>";
         $a = glob("{$common_code_dir}/*.txt");
         if (is_null($a) || count($a)==0) $t .= "<tr><th colspan=4>No common code available</th></tr>";
         else for ($i=0; $i<count($a); $i++) {
		    $filetime = max(filemtime($a[$i]),filectime($a[$i]));
			$pathinfo = pathinfo($a[$i]);
            $enc = base64_encode($a[$i]);
            $mouse_open = _click_dialog("_dialogAdminViewCommon");
            $t .= "<tr><td><span class=\"click-here\" {$mouse_open}>{$pathinfo['basename']}</span></td>";
			if (file_exists($pathinfo['dirname']."/".$pathinfo['filename'].".hdoc")) {
               $win="height=520,width=360,top=50,left=50,resizable=1";
               $help_url = "HDAW.php?load=HDA_HelpDoc&common=".$pathinfo['filename'].".hdoc&E";
               $mouse = "onclick=\"openWindow('{$help_url}','HDAW','{$win}'); return false; \" ";
               $t .= "<td><span class=\"click-here\" {$mouse} title=\"Code Writer Doc..\" >";
			   $t .= _emit_image("HelpDoc.jpg",24)."</span></td>";
			   }
			else $t .= "<td>&nbsp;</td>";
            $t .= "<td><span class=\"click-here\" {$mouse_open}>".hda_db::hdadb()->PRO_DBtime_Styledate($filetime,true)."</span></td>";
            $t .= "<td><span class=\"click-here\" {$mouse_open}>".filesize($a[$i])." bytes</span></td>";
            $t .= "<td>";
            $t .= "<span title=\"View code..\" {$mouse_open}>";
			$t .= _emit_image("Edit.jpg",12)."</span>";
            $mouse = _click_dialog($dialog_id,"_DeleteCommon-{$enc}");
            $t .= "<span title=\"Delete code..\" {$mouse}>";
			$t .= _emit_image("DeleteThis.jpg",12)."</span>&nbsp;";
            $t .= "</td>";
            $t .= "</tr>";
            }
         $t .= "</table></div></th></tr>";
	     break;
	  case 'RegEx':
	     $t .= "<tr><td><table class=\"alc-table\">";
	     $t .= "<tr><td>Target String:</td>";
		 $t .= "<th colspan=2><textarea name=\"{$dialog_id}_rx_target\" style=\"width:100%;\" rows=4>".PRO_ReadParam("{$dialog_id}_rx_target")."</textarea></th>";
		 $t .= "</tr>";
		 $t .= "<tr><td>Regular Expression:</td>";
		 $t .= "<td><input type=\"text\" {$key_mouse}  class=\"alc-intext\" name=\"{$dialog_id}_rx_pattern\"  style=\"font-size:12px;width:100%;\"  value=\"".PRO_ReadParam("{$dialog_id}_rx_pattern")."\"></td>";
		 $mouse = _click_dialog($dialog_id,"_run_rx");
		 $t .= "<th><span class=\"click-here\" {$mouse}>";
		 $t .= _emit_image("RunNow.jpg",24)."</span>";
		 $mouse = _click_dialog($dialog_id,"_rx_snippet");
		 $t .= "<span class=\"click-here\" {$mouse} title=\"Send to code snippets...\" style=\"float:right;\" >";
		 $t .= _emit_image("CodeSnippet.jpg",24)."</span>";
		 $t .= "</th></tr>";
		 $t .= "<tr><td>Result:</td><th colspan=2><textarea name=\"{$dialog_id}_rx_result\"  style=\"width:100%;\" rows=4>".PRO_ReadParam("{$dialog_id}_rx_result")."</textarea></th>";
		 $t .= "</tr>";
		 $t .= "<tr><th colspan=3><div style=\"width:100%; height:200px; overflow:auto;font-size:12px;\">";
		 $t .= file_get_contents("{$code_help_dir}/hdoc/rx.htm");
		 $t .= "</th></tr>";
		 $t .= "</table></td></tr>";
	     break;
	  case 'Dates':
	     $t .= "<tr><td><div class=\"alc-display-div\" > <table class=\"alc-table\">";
		 $t .= "<tr><th style=\"font-size:16;color:blue;font-wieght:bold;\" >Get a Unix time from a string</th>";
         $key_press = "onKeyPress=\"return keyPressPost('{$dialog_id}_strtotime_go',event, '{$dialog_id}')\" ";
		 $mouse = _click_dialog($dialog_id,"_strtotime_go");
		 $t .= "<td>Date String as input to string_to_time():<br>Input string:&nbsp;";
		 $t .= "<input class=\"alc-intext\" name=\"{$dialog_id}_strtotime\" style=\"width:60%;font-size:12px;\" value=\"".PRO_ReadParam("{$dialog_id}_strtotime")."\" {$key_press}>";
		 $t .= "&nbsp;<span class=\"click-here\" {$mouse}>";
		 $t .= _emit_image("GoNow.jpg",16);
         $t .= "</span>";
		 $mouse = _click_dialog($dialog_id,"_strtotime_snippet");
		 $t .= "<span class=\"click-here\" {$mouse} title=\"Send to code snippets...\" style=\"float:right;\" >";
		 $t .= _emit_image("CodeSnippet.jpg",24);
         $t .= "</span>";
		 $t .= "<br>";
		 $t .= "Result in unix time: <input type=\"text\" {$key_mouse}  name=\"{$dialog_id}_strtotime_time\" style=\"font-size:12px;\" value=\"".PRO_ReadParam("{$dialog_id}_strtotime_time")."\">";
		 $t .= " is the date: <input type=\"text\" {$key_mouse}  name=\"{$dialog_id}_strtotime_date\" style=\"font-size:12px;\" value=\"".hda_db::hdadb()->PRO_DBtime_Styledate(PRO_ReadParam("{$dialog_id}_strtotime_time"),true)."\"><br>";
		 $t .= "</td>";
		 $t .= "<td rowspan=2><div style=\"width:180px;font-size:12px;overflow-y:scroll;\"><b><u>Examples</u></b><br>";
		 if (@file_exists($f="{$code_help_dir}/hdoc/strtotime_examples.htm")) $t .= @file_get_contents($f);
		 $t .= "</div></td>";
		 $t .= "</tr>";
		 $t .= "<tr><th colspan=2><div style=\"width:100%; height:100px; overflow:auto;font-size:12px;\">";
		 if (@file_exists($f="{$code_help_dir}/hdoc/strtotime.htm")) $t .= @file_get_contents($f);
		 $t .= "</th></tr></table></div></td></tr>";
		 
	     $t .= "<tr><td><div class=\"alc-display-div\" > <table class=\"alc-table\">";
		 $t .= "<tr><th style=\"font-size:16;color:blue;font-wieght:bold;\">Get a date time string from a Unix time</th>";
		 $t .= "<td>Output formats as in function date(format [,time]):<br>Input format:&nbsp;";
         $key_press = "onKeyPress=\"return keyPressPost('{$dialog_id}_date_go',event,'{$dialog_id}')\" ";
		 $t .= "<input type=\"text\" class=\"alc-intext\" name=\"{$dialog_id}_dateformat\"  style=\"font-size:12px;width:60%;\"  value=\"".PRO_ReadParam("{$dialog_id}_dateformat")."\" {$key_press}>";
		 $mouse = _click_dialog($dialog_id,"_date_go");
		 $t .= "&nbsp;&nbsp;<span class=\"click-here\" {$mouse}>";
		 $t .= _emit_image("GoNow.jpg",16);
         $t .= "</span>";
		 $mouse = _click_dialog($dialog_id,"_date_snippet");
		 $t .= "<span class=\"click-here\" {$mouse} title=\"Send to code snippets...\" style=\"float:right;\" >";
		 $t .= _emit_image("CodeSnippet.jpg",24);
         $t .= "</span>";
		 $t .= "<br>";
		 $t .= "Result:&nbsp;<input type=\"text\" {$key_mouse}  name=\"{$dialog_id}_date\"  style=\"width:60%;\" value=\"".PRO_ReadParam("{$dialog_id}_date")."\"><br>";
		 $t .= "</td>";
		 $t .= "<td rowspan=2><div style=\"width:180px;font-size:12px;overflow-y:scroll;\"><b><u>Examples</u></b><br>";
		 if (@file_exists($f="{$code_help_dir}/hdoc/date_examples.htm")) $t .= @file_get_contents($f);
		 $t .= "</div></td>";
		 $t .= "</tr>";
		 $t .= "<tr><th colspan=2><div style=\"width:100%; height:100px; overflow:auto;font-size:12px;\">";
		 if (@file_exists($f="{$code_help_dir}/hdoc/date.htm")) $t .= @file_get_contents($f);
		 $t .= "</th></tr>";
		 $t .= "</table></div></td></tr>";
	     break;
	  case 'Snippets':
	     $t .= "<tr><td style=\"border:none;\" ><div style=\"width:100%;height:100px;overflow:hidden;border:none;\"><table class=\"alc-table\" style=\"border:none;\" >";
		 $t .= "<colgroup width=\"100%\">";
		    $t .= "<col width=\"50%\">";
		    $t .= "<col width=\"50%\">";
	     $t .= "</colgroup>";
		 $t .= "<tr style=\"border:none;\" ><td style=\"border:none;\" ><div style=\"height:90px;overflow-y:auto;flow:inline;\"><table class=\"alc-table\" style=\"border:none;\" >";
		 $t .= "<tr><td style=\"height:18px;color:green;font-size:12px;\" colspan=4>Standard Snippets</td></tr>";
		 $ff = glob("{$template_dir}/*.*");
		 foreach ($ff as $f) if (is_file($f)) {
		    $pathinfo = pathinfo($f);
			if (!array_key_exists('extension',$pathinfo)) continue;
			if (strtolower($pathinfo['extension'])<>'alc') continue;
		    $t .= "<tr><td>{$pathinfo['filename']}</td>";
		    $filetime = max(filemtime($f),filectime($f));
            $filesize = filesize($f);
			$t .= "<td>".hda_db::hdadb()->PRO_DBtime_Styledate($filetime)."</td>";
			$t .= "<td>{$filesize} bytes</td>";
			$t .= "<td>";
			$enc = base64_encode($f);
            $mouse = _click_dialog($dialog_id,"_snippet-{$enc}-0-");
            $t .= "<span style=\"cursor:pointer;height:16px;\" title=\"Review snippet..\" {$mouse}>";
            $t .= _emit_image("Edit.jpg",12)."</span>";
			$t .= "</td>";
			$t .= "</tr>";
		    }
		 $t .= "</table></div></td>";
		 $t .= "<td style=\"border:none;\" ><div style=\"height:90px;overflow-y:auto;flow:inline;\"><table class=\"alc-table\" style=\"border:none;\" >";
		 $t .= "<tr><td style=\"height:18px;color:green;font-size:12px;\" colspan=4>";
         $mouse = _click_dialog($dialog_id,"_snippet_new");
         $t .= "&nbsp;&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Create a new snippet..\" {$mouse}>";
         $t .= _emit_image("AddThis.jpg",12)."</span>";

         $t .= "&nbsp;"._emit_image("SeparatorBar.jpg",12)."&nbsp;";
		 $t .= " Your Snippets</td></tr>";
		 $ff = glob("{$template_dir}/"._makeUserRef($UserId)."/*.*");
		 foreach ($ff as $f) if (is_file($f)) {
		    $pathinfo = pathinfo($f);
			if (!array_key_exists('extension',$pathinfo)) continue;
			if (strtolower($pathinfo['extension'])<>'alc') continue;
		    $t .= "<tr><td>{$pathinfo['filename']}</td>";
		    $filetime = max(filemtime($f),filectime($f));
            $filesize = filesize($f);
			$t .= "<td>".hda_db::hdadb()->PRO_DBtime_Styledate($filetime)."</td>";
			$t .= "<td>{$filesize} bytes</td>";
			$t .= "<td>";
			$enc = base64_encode($f);
            $mouse = _click_dialog($dialog_id,"_snippet-{$enc}-1-");
            $t .= "<span style=\"cursor:pointer;height:16px;\" title=\"Edit snippet..\" {$mouse}>";
            $t .= _emit_image("Edit.jpg",12)."</span>";
            $mouse = _click_dialog($dialog_id,"_snippet-{$enc}-2-");
            $t .= "<span style=\"cursor:pointer;height:16px;\" title=\"Delete snippet..\" {$mouse}>";
            $t .= _emit_image("DeleteThis.jpg",12)."</span>";
			$t .= "</td>";
			$t .= "</tr>";
		    }
		 $t .= "</table></div></td></tr>";
		 $t .= "</table></div></td></tr>";
		 if (!is_null($this_snippet)) {
		    $t .= "<tr><td><input type=\"text\" {$key_mouse}  class=\"alc-intext\" name=\"{$dialog_id}_snippet_name\" value=\"".pathinfo($this_snippet, PATHINFO_FILENAME)."\">&nbsp;&nbsp;";
            $mouse = _click_dialog($dialog_id,"_snippet_Save");
            $t .= "<span class=\"click-here\" {$mouse} title=\"Save &amp; Run..\"  >";
			$t .= _emit_image("GoNow.jpg",16)."</span>";
	        $t .= "&nbsp;"._emit_image("SeparatorBar.jpg",12)."&nbsp;";

            $win="height=520,width=360";
            $help_url = "HDAW.php?load=HDA_HelpDoc";
            $mouse = "onclick=\"openWindow('{$help_url}','HDAW','{$win}'); return false; \" ";
            $t .= "&nbsp;<span class=\"click-here\" {$mouse} title=\"Code Writer Doc..\" >";
			$t .= _emit_image("HelpDoc.jpg",16)."</span>";
	        $t .= "&nbsp;"._emit_image("SeparatorBar.jpg",12)."&nbsp;";
	        $t .= "&nbsp;"._emit_image("SeparatorBar.jpg",12)."&nbsp;";
            $mouse = _click_dialog($dialog_id,"_snippet_Layout");
            $t .= "&nbsp;<span class=\"click-here\" {$mouse} title=\"Tidy..\">";
			$t .= _emit_image("CleanCode.jpg",16)."</span>";
            if ($can_revert) {
               $mouse = _click_dialog($dialog_id,"_snippet_Refresh");
               $t .= "&nbsp;<span class=\"click-here\" {$mouse} title=\"Revert Tidy..\">";
			   $t .= _emit_image("Revert.jpg",16)."</span>";
               }
			$t .= "</td></tr>";
		    $mouse = "onkeypress=\"return keyPressEscape();\" ";
            $t .= "<tr><th colspan=3><textarea id=\"{$dialog_id}_snippet_CodeText\" name=\"{$dialog_id}_snippet_CodeText\" dialog_id=\"{$dialog_id}\" style=\"width:100%;height:120px;overflow:scroll;resize:none;\" wrap=off {$mouse}>".PRO_ReadParam("{$dialog_id}_snippet_CodeText")."</textarea></th></tr>";
            $t .= "<tr><th colspan=3><textarea name=\"{$dialog_id}_snippet_OutText\" style=\"width:95%;height:80px;\" >".PRO_ReadParam("{$dialog_id}_snippet_DEV_Text")."</textarea></th></tr>";
			}
	     break;
      case 'Encoding':
         $t .= "<tr><td>";
         $t .= "Upload example file:&nbsp;<input type=\"file\" name=\"ImportedExample\"  value=\"\" >";
         $mouse = _click_dialog($dialog_id,"_Encoding-ImportedExample");
		 $t .= "<span class=\"push_button blue\" {$mouse} >Upload</span>"; 
		 $mouse = _change_dialog($dialog_id,"_EncodingRefresh");
		 $t .= "&nbsp;&nbsp;<select name=\"{$dialog_id}_SelectEncoding\" {$mouse}>";
		 $select_encoding = PRO_ReadParam("{$dialog_id}_SelectEncoding");
		 $t .= "<option value=\"\">Auto Detect</option>";
		 if (is_null($select_encoding) || $select_encoding=="") $select_encoding = "";
		 global $_validEncodings;
		 foreach ($_validEncodings as $use_encoding) {
		    $selected = ($use_encoding==$select_encoding)?"SELECTED":"";
			$t .= "<option value=\"{$use_encoding}\" {$selected}>{$use_encoding}</option>";
			}
	     $t .= "</select>";
		 $utf16_checked = (PRO_ReadAndClear("{$dialog_id}_UseUTF16"))?"CHECKED":null;
		 $mouse = _click_dialog($dialog_id,"_EncodingRefresh");
		 $t .= "&nbsp;&nbsp;<input type=\"checkbox\" name=\"{$dialog_id}_UseUTF16\" {$utf16_checked} {$mouse}>Pre filter using UTF16";
		 $t .= "</td></tr>";
		 $encoding_s = PRO_ReadParam("{$dialog_id}_EncodingS");
		 if (isset($encoding_s) && !is_null($encoding_s)) {
		    $t .= "<tr><td><div style=\"width:100%;height:180px;overflow-x:hidden;overflow-y:scroll;\" ><table class=\"alc-table\">";
		    $encoding_s = substr($encoding_s, 0, min(1024, strlen($encoding_s)));
			$t1 = ""; $t2 = "";
			for ($i=0; $i<strlen($encoding_s); $i+=64) {
			   for ($j=$i; $j<strlen($encoding_s) && ($j<($i+64)); $j++) {
			      $t2 .= sprintf("%02x",ord($encoding_s[$j]))." ";
				  }
			   $s = substr($encoding_s, $i, 64);
			   $s = str_replace(array("\n","\r"),"|",$s);
			   $t1 .= "{$s}<br>"; $t2 .= "<br>";
			   }
			$t .= "<tr><td>{$t1}</td><td>{$t2}</td></tr>";
			$t .= "</table></div></td></tr>";
			if ($select_encoding != "") {
			   $tt = "Using {$select_encoding}";
	           $ord_0 = 0; for ($i=1; $i<strlen($encoding_s); $i+=2) if (ord($encoding_s[$i])==0) $ord_0++;
			   if ($ord_0>0) $tt .= " Detected double byte ";
               if (!is_null($utf16_checked)) {
			      $tt .= " and Using UTF16 "; 
				  $encoding_s = @mb_convert_encoding($encoding_s, 'UTF-8', 'UTF-16');
				  }
			   $error = false;
			   $ss = @mb_convert_encoding($encoding_s, 'UTF-8', $select_encoding);
			   if ($ss===false) { $error = true; $tt .= "Fails"; }
			   $s =($error)?"Error {$tt}\n{$ss}":$ss;
			   }
			else {
			   $s = HDA_validateEncoding($encoding_s, $tt, $error);
			   }
			$t .= "<tr><td>Encoding test result: {$tt} ".(($error)?"Error":"Ok")."</td></tr>";
		    $t .= "<tr><td><div style=\"width:100%;height:180px;overflow-x:hidden;overflow-y:scroll;\" ><table class=\"alc-table\">";
			$t1 = ""; $t2 = "";
			for ($i=0; $i<strlen($s); $i+=64) {
			   for ($j=$i; $j<strlen($s) && ($j<($i+64)); $j++) {
			      $t1 .= "{$s[$j]} "; $t2 .= sprintf("%02x", ord($s[$j]))." ";
				  }
			   $t1 .= "<br>"; $t2 .= "<br>";
			   }
			$t .= "<tr><td>{$t1}</td><td>{$t2}</td></tr>";
			$t .= "</table></div></td></tr>";
			}
	     break;
	  case 'Functions':
	     global $code_help_dir;
	     switch ($Action) {
		    case "ACTION_{$dialog_id}_ShowDoc":
			   list($action, $doc) = explode('-',$ActionLine);
			   $doc = base64_decode($doc);
			   PRO_AddToParams("{$dialog_id}_OnDoc",$doc);
			   break;
			case "ACTION_{$dialog_id}_ShowFun":
			   list($action, $fn) = explode('-',$ActionLine);
			   PRO_AddToParams("{$dialog_id}_OnFunc", $fn);
			   break;
			case "ACTION_{$dialog_id}_LibFunc":
			   PRO_Clear("{$dialog_id}_OnDoc");
			   break;
			case "ACTION_{$dialog_id}_ToggleDocWin":
			   $showingDocWin = PRO_ReadParam("{$dialog_id}_OnDocWin");
			   $showingDocWin = ($showingDocWin=='M')?'V':'M';
			   PRO_AddToParams("{$dialog_id}_OnDocWin",$showingDocWin);
			   break;
			}

		    $t .= "<tr><td>Library Function&nbsp;";
			$lib = fopen("../{$code_root}/HDA_CodeLibrary.php",'r');
			$mouse = _change_dialog($dialog_id,"_Category");
			$t .= "Categories: <select class=\alc-intext\" name=\"{$dialog_id}_CategoryFunc\" {$mouse}>";
			$on_category = PRO_ReadParam("{$dialog_id}_CategoryFunc");
			$showingFunc = PRO_ReadParam("{$dialog_id}_OnFunc");
			if (is_null($on_category)) $on_category = "Other";
			$parse_category = "Other";
			$fns_list = array();
			$fns_list['Other'] = array();
			$t .= "<option value=\"Other\" SELECTED>Other</option>";
			while (($s = fgets($lib))!==false) {
			   if (preg_match("#^//\*\* CATEGORY (?P<cat>[\s\S]{1,})$#", $s, $matches)) {
			      $selected = (($parse_category=trim($matches['cat']))==$on_category)?"SELECTED":"";
			      $t .= "<option value=\"{$parse_category}\" {$selected}>{$parse_category}</option>";
			      if (!array_key_exists($parse_category, $fns_list)) $fns_list[$parse_category] = array();
				  }
			   elseif (preg_match("#^[\s]{0,}/\*\* (?P<fn>[\s\S]{1,})\*\*/#", $s, $matches)) {
			      $fns_list[$parse_category][] = $matches['fn'];
			      }
			   }
			$t .= "</select></td></tr>";
		    $t .= "<tr><td><div style=\"width:100%;height:80px;overflow-x:hidden;overflow-y:scroll;\" ><table class=\"alc-table\">";
		    $t .= "<colgroup><col span=8 style=\"width:100px;background-color:white;\"></colgroup>";
		    $on_odd = 0;
			$fnt = null;
			foreach($fns_list[$on_category] as $fn)  {
			   preg_match("#(?P<fn>[\w\d_]{1,})(?P<fnd>[\s\S]{1,})#",$fn,$matches);
		       if ($on_odd == 0) $t .= "<tr>";
			   if ($matches['fn']==$showingFunc) $fnt = $fn;
			   $on_style = ($matches['fn'] == $showingFunc)?"background-color:lightgray;color:blue;":"";
		       $mouse = _click_dialog($dialog_id,"_ShowFun-{$matches['fn']}");
			   $t .= "<td class=\"click-here\" {$mouse} style=\"{$on_style}\" ><span class=\"click-here\" >{$matches['fn']}</span></td>";
			   if ($on_odd == 7) $t .= "</tr>";
			   $on_odd = ((++$on_odd)>7)?0:$on_odd;
			   }
	        $t .= "</table></div></td></tr>";
			if (!is_null($fnt)) {
			   $t .= "<tr><td>";
			   $fnt_lines = explode(';',$fnt);
			   foreach ($fnt_lines as $fnt_line) $t .= "{$fnt_line}<br>";
			   if (file_exists($help_file = "{$code_help_dir}/{$showingFunc}.txt")) $t .= file_get_contents($help_file);
			   $t .= "</td></tr>";
			   }
		    
			break;
	  }
   $t .= _makedialogclose();

   return $t;
   }
function _dialogAdminUploadBinary($dialog_id='alc_admin_upload_bin') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;

   $problem = null;
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}":
         break;
      case "ACTION_{$dialog_id}_Upload":
         $problem = HDA_UploadBin("UploadBin");
         break;
      }
   $t = _makedialoghead($dialog_id,"Upload/View loaded binary executables");
   if (!is_null($problem)) $t .= "<tr><th colspan=1 style=\"color:red;\">{$problem}</th><tr>";
   $t .= "<tr><td>Upload *.bin:&nbsp;<input type=\"file\" name=\"UploadBin\" \" value=\"\" >";
   $mouse = _click_dialog($dialog_id,"_Upload");
   $t .= "&nbsp;<span class=\"push_button blue\" {$mouse} >Upload</span>"; 
   $t .= "</td></tr>";
   $t .= "<tr><td colspan=1><div style=\"width:100%;height:200px;overflow-y:auto;overflow-x:hidden;\"><table class=\"alc-table\">";
   $ff = glob("bin/*.exe");
   foreach ($ff as $f) {
      $t .= "<tr><td>".pathinfo($f,PATHINFO_BASENAME)."</td>";
      $fstat = stat($f);
      if (is_array($fstat)) {
         $t .= "<td>".hda_db::hdadb()->PRO_DBtime_Styledate($fstat['mtime'], true)."</td><td>{$fstat['size']}</td>";
         }
      $t .= "</tr>";
      }
   $t .= "</table></div></td></tr>";

   $t .= _makedialogclose();


   return $t;
   }
function _dialogAdminImportICS($dialog_id='alc_import_ics') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;

   $problem = null;
   $imported = null;
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}":
         break;
      case "ACTION_{$dialog_id}_Upload":
         $problem = HDA_UploadICS("UploadICS", $imported);
         break;
      }
   $t = _makedialoghead($dialog_id,"Import ICAL (.ics) Data");
   if (!is_null($problem)) $t .= "<tr><th colspan=1 style=\"color:red;\">{$problem}</th><tr>";
   $t .= "<tr><td>Upload *.ics:&nbsp;<input type=\"file\" name=\"UploadICS\" \" value=\"\" >";
   $mouse = _click_dialog($dialog_id,"_Upload");
   $t .= "&nbsp;<span class=\"push_button blue\" {$mouse} >Upload</span>"; 
   $t .= "</td></tr>";
   $t .= "<tr><th>Import to:&nbsp;<select name=\"{$dialog_id}_onTag\" >";
   $onTag = PRO_ReadParam("{$dialog_id}_onTag");
   if (is_null($onTag)) $onTag = 0;
   $a = hda_db::hdadb()->HDA_DB_admin('BLOCKDATES');
   $a = hda_db::hdadb()->HDA_DB_unserialize($a);
   foreach ($a as $i=>$p) {
      $selected = ($i==$onTag)?"SELECTED":"";
	  $t .= "<option value=\"{$i}\" {$selected}>{$p[0]}</option>";
	  }
   $t .= "</select></td></tr>";
   $t .= "<tr><td colspan=1><textarea style=\"width:100%;height:200px;overflow-y:auto;overflow-x:hidden;\">";
   if (!is_null($imported)) {
      $ics = new iCal();
	  $a = $ics->iCalDecoder($imported);
	  if (!is_array($a) || count($a)==0) $t .= "Unable to extract dates from import ics";
	  else {
	     foreach ($a as $p) {
		    $kk = array_keys($p);
			$date = null; $text = null;
			foreach ($kk as $k) {
			   if (preg_match("/DTSTART.*/",$k)) $date = $p[$k];
			   if (preg_match("/SUMMARY.*/",$k)) $text = $p[$k];
			   }
		    if (!is_null($date)) {
			   $date = date('Y-m-d', strtotime($date));
			   $t .= "{$date} : {$text}\n";
	           hda_db::hdadb()->HDA_DB_updateDiary(null,array('StartDate'=>$date,'EndDate'=>$date,'Title'=>$text,'ItemText'=>"No Data Day",'Tagged'=>"BLK_{$onTag}"));
			   }
		    }
		 }
	  @unlink($imported);
      }
   $t .= "</textarea></td></tr>";

   $t .= _makedialogclose();


   return $t;
   }
function _dialogAdminExportICS($dialog_id='alc_export_ics') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;
   
   global $UserName;
   global $UserId;

   $problem = null;
   $imported = null;
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}":
         break;
      }
   $t = _makedialoghead($dialog_id,"Export ICAL (.ics) Data");
   if (!is_null($problem)) $t .= "<tr><th colspan=1 style=\"color:red;\">{$problem}</th><tr>";
   $mouse = _change_dialog($dialog_id);
   $t .= "<tr><th>Export from:&nbsp;<select name=\"{$dialog_id}_onTag\" {$mouse} >";
   $onTag = PRO_ReadParam("{$dialog_id}_onTag");
   if (is_null($onTag)) $onTag = 0;
   $a = hda_db::hdadb()->HDA_DB_admin('BLOCKDATES');
   $a = hda_db::hdadb()->HDA_DB_unserialize($a);
   foreach ($a as $i=>$p) {
      $selected = ($i==$onTag)?"SELECTED":"";
	  $t .= "<option value=\"{$i}\" {$selected}>{$p[0]}</option>";
	  }
   $t .= "</select></td></tr>";
   $diary = hda_db::hdadb()->HDA_DB_readDiary(null, "BLK_{$onTag}");
   $f = null;
   $t .= "<tr><td colspan=1><textarea style=\"width:100%;height:200px;overflow-y:auto;overflow-x:hidden;\">";
   if (is_null($diary) || !is_array($diary) || count($diary)==0) $t .= "No dates for {$a[$onTag][0]}\n";
   else {
      $f = "tmp\BLK_{$onTag}";
	  if (!@file_exists($f)) @mkdir($f);
	  $f .= "\diarydates.ics";
	  $ical = new iCal();
	  $s = $ical->HDA_ICS_HEAD();
      foreach($diary as $row) {
         $t .= "{$row['StartDate']} {$row['Title']}\n";
		 $s .= $ical->HDA_ICS_ITEM($UserName, $UserId, $row['ItemId'], $row['StartDate'], $row['Title'], "BLK_{$onTag}");
         }
	  $s .= $ical->HDA_ICS_TAIL();
	  file_put_contents($f, $s);
      }
   $t .= "</textarea></td></tr>";
   if (!is_null($f)) {
      $t .= "<tr><th><a href=\"{$f}\" target=\"_blank\" >Download</a></th></tr>";
	  }
   $t .= _makedialogclose();


   return $t;
   }
   
function _dialogGenUserLink($dialog_id='alc_gen_user_link') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;
   $problem = null;
   $item = null;
   $xticket_file = null;
   $ticket = null;
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}":
	     list($action, $item) = explode('-',$ActionLine);
		 PRO_Clear(array("{$dialog_id}_UserName","{$dialog_id}_Email","{$dialog_id}_Instructions"));
		 $a = hda_db::hdadb()->HDA_DB_ReadProfile($item);
		 if (is_array($a)) PRO_AddToParams("{$dialog_id}_Instructions",$a['ItemText']);
         break;
	  case "ACTION_{$dialog_id}_UpdateTicket":
	     list($action, $item, $ticket) = explode('-',$ActionLine);
		 $ticket_r = hda_db::hdadb()->HDA_DB_getTickets($ticket, $item);
		 if (!is_array($ticket_r) || count($ticket_r)<>1) $problem = "Problem obtaining ticket information";
		 else {
		    PRO_AddToParams("{$dialog_id}_UserName", $ticket_r[0]['UserName']);
			PRO_AddToParams("{$dialog_id}_Email", $ticket_r[0]['Email']);
			PRO_AddToParams("{$dialog_id}_Instructions", $ticket_r[0]['Instructions']);
		    }
	     break;
	  case "ACTION_{$dialog_id}_GetTicket":
	     list($action, $item, $ticket) = explode('-',$ActionLine);
		 $ticket_r = hda_db::hdadb()->HDA_DB_getTickets($ticket, $item);
		 if (!is_array($ticket_r) || count($ticket_r)<>1) $problem = "Problem obtaining ticket information";
		 else {
			global $post_load;
	        $profile_title = hda_db::hdadb()->HDA_DB_TitleOf($item);
	        if (@!file_exists($xticket_file="Tickets/{$ticket}/{$profile_title}.tkt")) _makeFTPTicketFile($ticket, $item, $ticket_r[0]['UserName'], $ticket_r[0]['Instructions']);
			if (@file_exists($xticket_file)) {
			   $post_load .= "openWindow('HDAW.php?load=HDA_DownLoadFile&file={$xticket_file}','FTPTicket_{$ticket}');";
			   }
		    }
	     break;
	  case "ACTION_{$dialog_id}_GenTicket":
	     list($action, $item) = explode('-',$ActionLine);
		 $username = PRO_ReadParam("{$dialog_id}_UserName");
		 $email = PRO_ReadParam("{$dialog_id}_Email");
		 $instructions = PRO_ReadParam("{$dialog_id}_Instructions");
		 $profile_title = hda_db::hdadb()->HDA_DB_TitleOf($item);
		 if (strlen($username)==0) $problem = "Must include a username string (used for ticket reference)";
		 else {
		    $ticket = hda_db::hdadb()->HDA_DB_makeTicket($item, $username, $email, $instructions);
			if (!is_null($ticket)) {
			   $attach = array();
			   $xticket_file = _makeFTPTicketFile($ticket, $item, $username, $instructions);
               }
			else $problem = "Fails to generate ticket";
		    }
		 break;
	  case "ACTION_{$dialog_id}_SendNow":
	     list($action, $item, $ticket) = explode('-',$ActionLine);
		 _sendTicket($ticket, $item, $problem);
	     break;
	  case "ACTION_{$dialog_id}_DelTicket":
	     list($action, $item, $del_item) = explode('-',$ActionLine);
		 hda_db::hdadb()->HDA_DB_deleteTicket($del_item);
		 break;
      }
   $profile_title = hda_db::hdadb()->HDA_DB_TitleOf($item);
   $t = _makedialoghead($dialog_id,"Generate an End User Ticket for {$profile_title}");
   if (!is_null($problem)) $t .= "<tr><th colspan=2 style=\"color:red;\">{$problem}</th><tr>";
   $t .= "<tr><td>End User Name:</td><td><input type=\"text\" {$key_mouse}  name=\"{$dialog_id}_UserName\" size=38 value=\"".PRO_ReadParam("{$dialog_id}_UserName")."\"></td></tr>";
   $t .= "<tr><td>End User Email (for distribution):</td><td><input type=\"text\" {$key_mouse}  name=\"{$dialog_id}_Email\" size=38 value=\"".PRO_ReadParam("{$dialog_id}_Email")."\"></td></tr>";
   $t .= "<tr><td>Instructions to user:</td>";
   $t .= "<td><textarea name=\"{$dialog_id}_Instructions\" style=\"width:100%;height:80px;overflow-y:scroll;overflow-x:hidden;border:none;\">";
   $t .= PRO_ReadParam("{$dialog_id}_Instructions")."</textarea></td></tr>";
   $mouse = _click_dialog($dialog_id,"_GenTicket-{$item}");
   $t .= "<tr><td>&nbsp;</td><th>";
   $t .= "<span class=\"push_button blue\" {$mouse} >Generate Ticket ".((!is_null($xticket_file))?"Again":"")."</span>"; 
   $t .= "</th></tr>";
   if (!is_null($xticket_file)) {
      $t .= "<tr><td>Download FTP ticket for ".($send_to = PRO_ReadParam("{$dialog_id}_UserName"))."</td>";
	  $t .= "<td><a href=\"HDAW.php?load=HDA_DownLoadFile&file={$xticket_file}\" target=\"_blank\">Download FTP Ticket</a></td></tr>";
	  $t .= "<tr><td>Distribute this ticket to {$send_to} now ..</td>";
	  $mouse = _click_dialog($dialog_id,"_SendNow-{$item}-{$ticket}");
	  $t .= "<td><span class=\"click-here\" title=\"Send Now\" {$mouse} >";
	  $t .= _emit_image("SendNow.jpg",16);
	  $t .= "&nbsp;".PRO_ReadParam("{$dialog_id}_Email");
	  $t .= "</span></td></tr>";
	  }
	  
   $t .= "<tr><td colspan=2><div style=\"width:100%;height:200px;overflow-y:auto;overflow-x:hidden;\"><table class=\"alc-table\">";
   $a = hda_db::hdadb()->HDA_DB_getTickets(NULL, $item);
   $t .= "<tr><th colspan=5>Other Tickets Issued for profile {$profile_title}</th></tr>";
   if (is_array($a) && count($a)>0) {
      foreach ($a as $row) {
         $t .= "<tr>";
		 $mouse = _click_dialog($dialog_id,"_UpdateTicket-{$item}-{$row['ItemId']}");
		 $t .= "<td><span class=\"click-here\" {$mouse}>{$row['UserName']}</span></td>";
		 $t .= "<td><span class=\"click-here\" {$mouse}>".hda_db::hdadb()->PRO_DBdate_Styledate($row['IssuedDate'],true)."</span></td>";
		 $t .= "<td><span class=\"click-here\" {$mouse}>{$row['Email']}</span></td>";
		 $mouse = _click_dialog($dialog_id,"_GetTicket-{$item}-{$row['ItemId']}");
         $t .= "<th><span class=\"click-here\" {$mouse} title=\"Download Ticket..\" >";
		 $t .= _emit_image("Download.jpg",12)."</span></th>";
		 $mouse = _click_dialog($dialog_id,"_DelTicket-{$item}-{$row['ItemId']}");
         $t .= "<th><span class=\"click-here\" {$mouse} title=\"Delete Ticket..\" >";
		 $t .= _emit_image("DeleteThis.jpg",12)."</span></th>";
		 $t .= "</tr>";
		 }
	  }
   else $t .= "<tr><th colspan=5>No tickets issued</th></tr>";
   $t .= "</table></div></td></tr>";

   $t .= _makedialogclose();


   return $t;
   }
function _sendTicket($ticket, $item=null, &$problem) {
   $ticket_r = hda_db::hdadb()->HDA_DB_getTickets($ticket, $item);
   if (!is_array($ticket_r) || count($ticket_r)<>1) $problem = "Problem obtaining ticket information";
   else {
      if (is_null($item)) $item = $ticket_r[0]['ProfileId'];
	  $profile_title = hda_db::hdadb()->HDA_DB_TitleOf($item);
	  if (@!file_exists($xticket_file="Tickets/{$ticket}/{$profile_title}.tkt")) _makeFTPTicketFile($ticket, $item, $ticket_r[0]['UserName'], $ticket_r[0]['Instructions']);

      if (@file_exists("Tickets/{$ticket}")) {
	     if (@file_exists($xticket_file="Tickets/{$ticket}/{$profile_title}.tkt")) $attach[] = $xticket_file;
		 global $binary_dir;
		 if (@file_exists($bin_zip = "{$binary_dir}/HDAWTicketRunner.alcb")) $attach[] = $bin_zip;
		 $msg = "Attached to this email is a tkt file for use outside of direct access to HDAW ";
		 $msg .= "and requires the use of an application that is attached here. The file HDAWTicketRunner.alcb is a zip file and contains your external ticket app for use ";
		 $msg .= "when beyond direct Web access to HDAW. Unzip HDAWTicketRunner.alcb to expose HDAW Ticket Runner.exe <br>";
		 $msg .= "<br>{$ticket_r[0]['Instructions']}";
		 if (strlen($email=$ticket_r[0]['Email'])>0) {
			HDA_EmailTicket($email, $username = $ticket_r[0]['UserName'], hda_db::hdadb()->HDA_DB_TitleOf($item), $msg, $attach);
			$problem = "Ticket for {$profile_title} sent to {$username} at {$email}";
			}
		 }
	  }
   }

function _makeFTPTicketFile($ticket, $item, $username, $instructions) {
   $xticket = hda_db::hdadb()->HDA_DB_admin('ExternalTicket');
   if (is_null($xticket) || strlen($xticket)==0) return null;
   $xticket = hda_db::hdadb()->HDA_DB_unserialize($xticket);
   if (!array_key_exists('VN',$xticket)) return null;
   $ticket_s = "";
   $ticket_s .= "{$xticket['VN']}\n";
   $ticket_s .= "{$ticket}\n";
   $ticket_s .= "{$item}\n";
   $ticket_s .= hda_db::hdadb()->HDA_DB_TitleOf($item)."\n";
   $ticket_s .= "{$username}\n";
   $ticket_s .= "{$xticket['URL']}\n";
   $ticket_s .= "{$xticket['UNAME']}\n";
   $ticket_s .= "{$xticket['PW']}\n";
   $ticket_s .= "{$xticket['BASEDIR']}\n";
   $ticket_s .= "{$xticket['DATEDIR']}\n";
   $ticket_s .= "INSTR>{$instructions}<INSTR";
   $ticket_s = _link_convert($ticket_s);
   $ticket_file = "Tickets/{$ticket}"; if (!@file_exists($ticket_file)) @mkdir($ticket_file);
   file_put_contents($ticket_file = "{$ticket_file}/".hda_db::hdadb()->HDA_DB_TitleOf($item).".tkt", $ticket_s);
   return $ticket_file;
   }
   

function _dialogAdminUploadCommon($dialog_id='alc_admin_upload_common') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;
   global $common_code_dir;

   $problem = null;
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}":
         break;
      case "ACTION_{$dialog_id}_Upload":
         $problem = HDA_UploadCommon("UploadCommon");
         break;
      }
   $t = _makedialoghead($dialog_id,"Upload/View loaded common code");
   if (!is_null($problem)) $t .= "<tr><th colspan=1 style=\"color:red;\">{$problem}</th><tr>";
   $t .= "<tr><td>Upload :&nbsp;<input type=\"file\" name=\"UploadCommon\" \" value=\"\" >";
   $mouse = _click_dialog($dialog_id,"_Upload");
   $t .= "&nbsp;<span class=\"push_button blue\" {$mouse} >Upload</span>"; 
   $t .= "</td></tr>";
   $t .= "<tr><td colspan=1><div style=\"width:100%;height:200px;overflow-y:auto;overflow-x:hidden;\"><table class=\"alc-table\">";
   $ff = glob("{$common_code_dir}/*.*");
   foreach ($ff as $f) {
      $t .= "<tr><td>".pathinfo($f,PATHINFO_BASENAME)."</td>";
      $fstat = stat($f);
      if (is_array($fstat)) {
         $t .= "<td>".hda_db::hdadb()->PRO_DBtime_Styledate($fstat['mtime'], true)."</td><td>{$fstat['size']}</td>";
         }
      $t .= "</tr>";
      }
   $t .= "</table></div></td></tr>";

   $t .= _makedialogclose();


   return $t;
   }

function _dialogAdminViewCommon($dialog_id='alc_common_immediate') {
   global $Action;
   global $ActionLine;
   global $_Mobile;
   global $code_root, $home_root;
   global $key_mouse;

   $t = "";
   $problem = null;
   $tmp_n = null;
   $can_revert = false;
   switch ($Action) {
      default:return "";
      case "ACTION_{$dialog_id}":
      case "ACTION_{$dialog_id}_Refresh":
         PRO_Clear("{$dialog_id}_common_DEV_Text");
         list($action, $tmp_n) = explode('-',$ActionLine);
         $ff = base64_decode($tmp_n);
         PRO_AddToParams("{$dialog_id}_common_filepath",$ff);
         PRO_AddToParams("{$dialog_id}_common_CodeText", str_replace(array("\xe2\x80\x9c","\xe2\x80\x9d","\xe2\x80\x98","\xe2\x80\x99"), array("\"","\"","'","'"), file_get_contents($ff)));
         break;
      case "ACTION_{$dialog_id}_Layout":
         $ff = PRO_ReadParam("{$dialog_id}_common_filepath");
         @file_put_contents($ff, PRO_ReadParam("{$dialog_id}_common_CodeText"));_chmod($ff);
         $s = HDA_CodeLayout(pathinfo($ff,PATHINFO_DIRNAME), pathinfo($ff, PATHINFO_BASENAME));
         PRO_AddToParams("{$dialog_id}_common_CodeText", str_replace(array("\xe2\x80\x9c","\xe2\x80\x9d","\xe2\x80\x98","\xe2\x80\x99"), array("\"","\"","'","'"), $s));
         $can_revert = true;
         break;
      case "ACTION_{$dialog_id}_Save":
         $ff = PRO_ReadParam("{$dialog_id}_common_filepath");
         @file_put_contents($ff, PRO_ReadParam("{$dialog_id}_common_CodeText"));_chmod($ff);
         $the_log = HDA_CodeParser($ff);
         PRO_AddToParams("{$dialog_id}_common_DEV_Text", "{$the_log}"); 
         break;
      }
   $t .= _makedialoghead($dialog_id,"Edit/View loaded common code", 'alc-dialog-vlarge');

   $t .= "<tr><th>Viewing common ".PRO_ReadParam("{$dialog_id}_common_filepath")."</th></tr>";
   if (!is_null($problem)) $t .= "<tr><th colspan=3>{$problem}</th></tr>";
   $mouse = "onkeypress=\"return keyPressEscape();\" ";
   $t .= "<tr><th colspan=3><textarea id=\"{$dialog_id}_common_CodeText\" name=\"{$dialog_id}_common_CodeText\" dialog_id=\"alc_common_immediate\" style=\"width:100%;height:220px;overflow:scroll;resize:none;\" wrap=off {$mouse} >".PRO_ReadParam("{$dialog_id}_common_CodeText")."</textarea></th></tr>";
   $t .= "<tr><th colspan=3  class=\"buttons\" >";
   $t .= "<div style=\"padding-bottom:6px;height:32px; style:inline-block; flow:inline; \" >";
   $mouse = _click_dialog($dialog_id,"_Save");
   $t .= "<span class=\"push_button blue\" {$mouse}  title=\"Save updates only..\" style=\"margin-bottom:8px;margin-top:4px;\" >Save &amp; Compile</span>"; 



   $t .= "<div class=\"alc-border-box alc-rounded-box\" style=\"padding:2px; margin:4px; display:inline-block; flow:inline; width:140px; \" >";
   $t .= "Tidy Code:";
   $mouse = _click_dialog($dialog_id,"_Layout");
   $t .= "&nbsp;<span class=\"click-here\" {$mouse} title=\"Tidy..\">";
   $t .= _emit_image("CleanCode.jpg",18)."</span>";
   if ($can_revert) {
      $enc = base64_encode(PRO_ReadParam("{$dialog_id}_common_filepath"));
      $mouse = _click_dialog($dialog_id,"_Refresh-{$enc}");
      $t .= "&nbsp;<span class=\"click-here\" {$mouse} title=\"Revert Tidy..\">";
	  $t .= _emit_image("Revert.jpg",18)."</span>";
      }
   $t .= "</div>";

   $t .= "</div>";
   $t .= "</th></tr>";
   $t .= "<tr><th colspan=3><textarea name=\"{$dialog_id}_common_OutText\" style=\"width:95%;height:120px;\" >".PRO_ReadParam("{$dialog_id}_common_DEV_Text")."</textarea></th></tr>";
   $t .= _makedialogclose();


   return $t;
   }

?>