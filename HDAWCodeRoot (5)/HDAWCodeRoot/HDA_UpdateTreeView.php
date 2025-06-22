<?php
include_once __DIR__."/HDA_Initials.php";
include_once __DIR__."/HDA_Finals.php";
_hydra_("HDA_UpdateTreeView", $loads = array('XML','DB','Functions','Process','ManageProfiles','Email','Logging'));


$xml = "";

$UserCode = PRO_ReadParam('user');
$UserName = hda_db::hdadb()->HDA_DB_GetUserFullName($UserCode);

switch (PRO_ReadParam('taction')) {
   case 'load':
      $category = $_GET['cat'];
	  $display = (array_key_exists('display',$_GET))?"\n":"";
      $a = hda_db::hdadb()->HDA_DB_getRelations((strlen($category)==0)?NULL:$category);
      $xml = "<?xml version='1.0' encoding='iso-8859-1' ?><tree id='0'>{$display}";
	  $child = (is_array($a) && count($a)>0)?1:0;
      $xml .= "<item id=\"ALL\" text=\"All {$category} Profiles\" child=\"{$child}\" open=\"1\" select=\"1\" >{$display}";
	  $xml .= _outChildTree($a, $a, $display);
      $xml .= "</item>{$display}";
      $xml .= "</tree>{$display}";
	  break;
   default:
      $xml .= "<data>";
      $update = $_GET['!nativeeditor_status'];
      $id = $_GET['tr_id'];
      $pid = $_GET['tr_pid'];
      $xml .= "<action type='{$update}' sid='{$id}' tid='{$id}'/>";
      $xml .= "</data>";
	  break;
   case 'find':
      if (array_key_exists('id',$_GET)) $id = $_GET['id'];
      else $id = "All";
	  $seq = (array_key_exists('seq',$_GET))?$_GET['seq']:0;
      $id = urldecode($id);
	  $id = str_replace('*','%',$id);
	  $id = trim($id,"% \"'");
      $a = hda_db::hdadb()->HDA_DB_findProfiles($id);
	  $xml .= "<data>";
	  $xml .= "<pfid>";
	  if (is_array($a) && count($a)>0) {
	     if ($seq>=count($a)) $seq = 0;
	     $xml .= $a[$seq]['ItemId'];
		 }
	  $xml .= "</pfid>";
	  $xml .= "<seq>{$seq}</seq>";
	  $xml .= "</data>";
	  break;
   case 'update':
      $xml .= "<data>";
      $update = $_GET['!nativeeditor_status'];
      $id = $_GET['tr_id'];
      $pid = $_GET['tr_pid'];
      $xml .= "<action type='{$update}' sid='{$id}' tid='{$id}'/>";
      $xml .= "</data>";
	  hda_db::hdadb()->HDA_DB_Relation($id, $pid); 
	  HDA_LogOnly("Profile Relation Update of ".hda_db::hdadb()->HDA_DB_TitleOf($id)." parent is ".hda_db::hdadb()->HDA_DB_TitleOf($pid));
      hda_db::hdadb()->_invalidateTreeFrom($id);	  
	  break;
   case 'details':
      $id = $_GET['id'];
	  $a = hda_db::hdadb()->HDA_DB_profileIndexItem($id);
	  $note = (is_array($a))?$a['ItemText']:"";
      $xml .= "<data>";
	  $xml .= "<note>".urlencode($note)."</note>";
	  $status = "";
	  $img = 'TAG_QUESTION.jpg';
	  if (preg_match("/[\w]{1,}_(?P<ev>SUCCESS|FAILURE|LATE)/",$a['EventCode'],$ev)) {
		  $status .= "Last Event:{$ev['ev']}<br>";
		  switch ($ev['ev']) {
			  case 'SUCCESS': $img = 'TAG_ANSWER.jpg'; break;
			  case 'FAILURE': $img = 'DataFailure.jpg'; break;
			  case 'WAITING': $img = 'TAG_WAITING.jpg'; break;
			}
		}
	  _emit_image($img,24); // ensure in cache
	  $xml .= "<Image>".urlencode("cache/{$img}")."</Image>";
	  if (is_null($a['Scheduled'])) $status .= "Not Scheduled";
	  else $status .= "Next scheduled ".hda_db::hdadb()->PRO_DBdate_Styledate($a['Scheduled'],true)." then every Every {$a['Units']} {$a['RepeatInterval']}";
	  $xml .= "<Status>".urlencode($status)."</Status>";
	  $xml .= "<autolog>{$a['AutoText']}</autolog>";
	  $rules = hda_db::hdadb()->HDA_DB_getRelationRules($id);
	  $xml .= "<rule>{$rules['Rule']}</rule>";
	  $xml .= "<fail_rule>{$rules['OnFail']}</fail_rule>";
	  $xml .= "<def_rule>{$rules['OnDefault']}</def_rule>";
	  $xml .= "<enabled>{$rules['IsEnabled']}</enabled>";
	  $xml .= "<proxy>{$rules['IsProxy']}</proxy>";
	  $xml .= "<datadays>{$rules['DataDays']}</datadays>";
	  $xml .= "<event>".($date = hda_db::hdadb()->HDA_DB_SuccessEventDate($id))."</event>";
	  $xml .= "<pass>".(_passesRules($date, $rules['Rule'], $rules['OnDefault'], $rules['DataDays'], $log))."</pass>";
	  $xml .= "<collect>".(($a['WillCollect']==1)?urlencode($a['CollectFrom']):"")."</collect>";
	  $xml .= "</data>";
	  break;
   }
function _outChildTree(&$a, &$children, $display) {
   $t = ""; $log = array();
   foreach ($children as $item=>$row) {
      $enabled = (is_null($row['TActive']) || ($row['TActive']=='') || (($row['TActive']&1)==1));
	  $proxy = (!is_null($row['TActive']) && (($row['TActive']&2)==2));
	  $child = (is_array($row['Children']) && count($row['Children'])>0)?1:0;
	  if (!$enabled) {
	     $pass=false;
	     $style = "color:gray;";
		 $last_run = "Not Enabled";
		 }
	  else {
	     $pass = _passesRules($last_run = hda_db::hdadb()->HDA_DB_SuccessEventDate($item), $row['Rule'], $row['OnDefault'], $row['DataDays'], $log);
	     $style =  ($pass)?"color:green;":((is_null($pass))?"color:blue;":"color:red;");
		 }
	  if ($proxy) $imgs = "im0=\"iconProxyP.gif\" im1=\"iconProxyP.gif\" im2=\"iconProxyP.gif\" ";
	  else $imgs = (!$pass)?"im0=\"iconFlag.gif\" im1=\"iconFlag.gif\" im2=\"iconFlag.gif\" ":"";
	  $enabled = ($enabled)?1:0;
	  $rule = (is_null($row['Rule']))?'T':$row['Rule'];
      $t .= "<item id=\"{$row['ItemId']}\" text=\"{$row['Title']}\" child=\"{$child}\" {$imgs} style=\"font-weight:normal;{$style}\" tooltip=\"{$last_run}\" enabled=\"{$enabled}\" rule=\"{$rule}\" >{$display}";
      if ($child==1) $t .= _outChildTree($a, $row['Children'], $display);
	  $t .= "</item>{$display}";
      }
   return $t;
   }
echo $xml;
$content = null;
_finals_();
?>