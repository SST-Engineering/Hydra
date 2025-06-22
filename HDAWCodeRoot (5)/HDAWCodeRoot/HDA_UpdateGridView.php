<?php
include_once __DIR__."/HDA_Initials.php";
include_once __DIR__."/HDA_Finals.php";
_hydra_("HDA_UpdateGridView", $loads = array('XML','DB','Functions','Process','ManageProfiles','Email','Logging'));


$xml = "";

switch (PRO_ReadParam('taction')) {
   case 'load':
      $category = $_GET['cat'];
	  $enabled = $_GET['enabled'];
	  $ch = ($enabled)?'ch':'ro';
	  $lch = ($enabled)?'ed':'ro';
	  $display = (array_key_exists('display',$_GET))?"\n":"";
      $a = hda_db::hdadb()->HDA_DB_listCollects((strlen($category)==0)?NULL:$category);
      $xml = "<?xml version='1.0' encoding='iso-8859-1' ?>{$display}";
	  $xml .= "<rows>";
	  $xml .= "<head>";
	  $xml .= "<column width=\"300\" type=\"ro\" align=\"right\" color=\"white\" sort=\"str\">Profile Title</column>";
	  $xml .= "<column width=\"20\" type=\"ro\" align=\"right\" color=\"white\" sort=\"str\"></column>";
	  $xml .= "<column width=\"400\" type=\"{$lch}\" align=\"right\" color=\"white\" sort=\"str\">Location</column>";
	  $xml .= "<column width=\"56\" type=\"{$ch}\" align=\"left\" color=\"white\" >Collect Enabled</column>";
	  $xml .= "<column width=\"56\" type=\"ro\" align=\"left\" color=\"white\" >Tickets</column>";
	  $xml .= "<column width=\"56\" type=\"ro\" align=\"left\" color=\"white\" >Proxy</column>";
	  $xml .= "<column width=\"56\" type=\"ro\" align=\"left\" color=\"white\" >Rules Enabled</column>";
	  $xml .= "<column width=\"300\" type=\"ro\" align=\"left\" color=\"white\" >Validity Checks</column>";
	  $xml .= "</head>";
	  foreach ($a as $row) {
	     $xml .= "<row id=\"{$row['ItemId']}\">";
		 $xml .= "<cell><![CDATA[{$row['Title']}]]></cell>";
		 $xml .= "<cell>";
	     if ($enabled) {
	        $goto_mouse = "onclick=\"issuePost('gotoTab-LD-{$row['ItemId']}---',event); return false;\" ";
		    $mouse_img = "GoForward.jpg";
		    }
	     else {
		    $goto_mouse = _click_dialog("_dialogProfileIndexItem","-{$row['ItemId']}");
		    $mouse_img = "CODEHELP.gif";
		    }
		 $xml .= "<![CDATA[<span class=\"click-here\" {$goto_mouse} title=\"Open Profile\">";
         $xml .= _emit_image($mouse_img,16);
		 $xml .= "</span>]]>";
		 $xml .= "</cell>";
		 $xml .= "<cell>";
		 $xml .= "<![CDATA[".substr($row['ItemText'],0,32)."]]>";
		 $xml .= "</cell>";
		 $xml .= "<cell style=\"text-align:center;\" >";
		 $xml .= (($row['WillCollect']&1)==1)?1:0;
		 $xml .= "</cell>";
		 $xml .= "<cell style=\"text-align:center;\" >{$row['Tickets']}</cell>";
		 $xml .= "<cell style=\"text-align:center;\" >{$row['IsProxy']}</cell>";
		 $xml .= "<cell style=\"text-align:center;\" >{$row['AutoRules']}</cell>";
		 $sanity = "";
		 if ($row['Tickets']>0) {
		    if ($row['IsProxy']=='Yes') $sanity .= "Allowing Ticket Access (for uploads) and Set as Proxy? - Invalid<br>";
		    if ($row['WillCollect']<>0) $sanity .= "Allowing Ticket Access (for uploads) and Auto Collection? - Clash Data Management<br>";
			}
	     if ($row['IsProxy']=='Yes') {
		    if ($row['WillCollect']<>0) $sanity .= "Set as Proxy and Auto Collection? - Invalidt<br>";
			}
	     if (strlen($sanity)>0) $sanity = "<span style=\"color:red;\">{$sanity}</span>";
		// if (strlen($row['AutoLog'])>0) $sanity .= "<span style=\"color:gray;\">{$row['AutoLog']}</span>";
         $mouse = _click_dialog("_dialogCheckRules","-{$row['ItemId']}");
         $sanity .= "<div style=\"float:right;padding-right:8px;\">";
         $sanity .= "&nbsp;<span style=\"cursor:pointer;height:16px;\" title=\"Check Rules..\" {$mouse}>";
         $sanity .= _emit_image("QuestionThis.jpg",16)."</span>";
         $sanity .= "</div>";
		 $xml .= "<cell style=\"text-align:left;\" ><![CDATA[<span style=\"font-size:10px;width:300;white-space:normal;\"> {$sanity}</span>]]></cell>";
		 $xml .= "</row>";
		 }
	  $xml .= "</rows>";
	  break;
   default:
      $xml .= "<data>";
      $update = $_GET['!nativeeditor_status'];
      $id = $_GET['gr_id'];
      $xml .= "<action type='{$update}' sid='{$id}' tid='{$id}'/>";
      $xml .= "</data>";
	  break;
   case 'update':
      $xml .= "<data>";
      $update = $_GET['!nativeeditor_status'];
      $id = $_GET['gr_id'];
      $xml .= "<action type='{$update}' sid='{$id}' tid='{$id}'/>";
      $xml .= "</data>";
      hda_db::hdadb()->HDA_DB_autoCollect($id, $xname=$_GET['c2'], $enabled=$_GET['c3']);
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
   }
echo $xml;
$content = null;
_finals_();
?>