<?php
 $on_submit = "";
include_once __DIR__."/HDA_Initials.php";
include_once __DIR__."/HDA_Finals.php";
_hydra_("HDA_ReadMonitor", $loads = array('DB','Functions','Process','Email','Logging'));





$monitor_time = PRO_ReadParam('ETIME');
$process_item = PRO_ReadParam('CODE');
$UserCode = PRO_ReadParam('USERCODE');
if (is_null($monitor_time) || $monitor_time==0) $monitor_time=time();
PRO_AddToParams('ETIME',$monitor_time);
$elapsed_time = _style_secs_time(time() - $monitor_time);
$to_kill_list = hda_db::hdadb()->HDA_DB_monitorFind($process_item);
$to_kill = null;
if (is_array($to_kill_list) && count($to_kill_list)>0) {
   $to_kill = $to_kill_list[0]['SessionId'];
   }
   
   
$t = "<div style=\"text-align:left;\" >"; 

$mouse = "onclick=\"issuePost('ReadMonitor---',event); return false;\" ";

$t .= "<span class=\"click-here\" style=\"color:blue;\" {$mouse}>[ Refresh ]</span><br>";

switch ($Action) {
	case "ACTION_ReadMonitor": $t .= "{$ActionLine}<br>"; break;
}

$row = (!is_null($to_kill))?hda_db::hdadb()->HDA_DB_monitorRead($to_kill):null;



$t .= "Monitoring {$row['Title']}<br> ";

 if (!is_null($row)) {
    $elapsed_time = _style_secs_time(time()-$monitor_time);
    $t .= "Elapsed Time: {$elapsed_time}<br>";
	$t .= "Last Pulse: {$row['Pulse']}<br>";
	$t .= "Message {$row['ItemText']}<br>";
	$t .= "Status: {$row['Status']}<br>";
    }
else $status = "Waiting for process {$process_item}";

$t .= "</div>";
$content .= $t;
$content_iframe_embedded = true;



_finals_();

?>