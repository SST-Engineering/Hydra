<?php
include_once __DIR__."/HDA_Initials.php";
include_once __DIR__."/HDA_Finals.php";
_hydra_("HDA_HelpDoc", $loads = array("CodeLibrary","CodeCompiler"));
_include_css("style_help");
$title = "HDAW Coder Help";
$t = "";
$t .= "<center><div class=\"alc-view\" id=\"HelpDocView\" style=\"width:300px; height:400px; overflow:hidden; margin:10px;padding:10px;  \" >";// 

$help_with = (array_key_exists('hdoc',$_GET))?$_GET['hdoc']:null;
$show_common = (array_key_exists('common',$_GET))?$_GET['common']:null;

if (!is_null($help_with)) {
   $t .= "<div style=\"width:100%; height:100%; overflow-y:auto; overflow-x:hidden; text-align:left; \" >";
   $f = null;
   $title = "HDAW Profile Helper";
   if (strlen($help_with)>0) {
      $f = "{$code_help_dir}/hdoc/{$help_with}.*";
	  $ff = glob($f);
	  if (is_array($ff) && count($f)==1) $f = $ff[0];
      if (!file_exists($f)) { $t .= "<h2>{$help_with}</h2><br>Help on <b>{$help_with}</b> not found"; $f = null; }
      else {
	     $ext = pathinfo($f, PATHINFO_EXTENSION);
		 switch ($ext) {
		    case 'pdf':
			   $fref = INIT('HDOC_HOST').pathinfo($f,PATHINFO_BASENAME);
			   $t .= "<div style=\"width:100%; height:100%; overflow-y:hidden;\" >";
			   $t .= "<object data=\"{$fref}\" type=\"application/pdf\" width=\"100%\" height=\"100%\" > alt : <a href=\"{$fref}\">{$help_with}</a></object>";
			   $t .= "</div>";
			   break;
		    case 'rtf':
			   $fref = "HDAW.php?load=HDA_DownLoadFile&file={$f}";
			   $t .= "<div style=\"width:100%; height:100%; overflow-y:hidden;\" >";
			//   $t .= "Download or open: <a href=\"HDAW.php?load=HDA_DownLoadFile&file={$f}\" target=\"_blank\">{$help_with}</a><br>";
			   $t .= "<object data=\"{$fref}\" type=\"application/rtf\" width=\"100%\" height=\"100%\" >Download or open: <a href=\"{$fref}\">{$help_with}</a><br>";
			   $t .= "</object>";
			   $t .= "</div>";
			   break;
			default:
               $t .= file_get_contents($f);
			   break;
			}
         }
      }
   if (is_null($f)) {
      $ff = glob("{$code_help_dir}/hdoc/*.txt");
      if (is_array($ff)) {
         foreach ($ff as $f) {
            $t .= file_get_contents($f)."<br>";
            $t .= "<span style=\"font:8px;color:gray;\">From {$f}</span><br>";
            }
         }
      }
   $t .= "</div>";
   }
else if (!is_null($show_common)) {
   $t .= "<div style=\"width:100%; height:100%; overflow:scroll; white-space:nowrap; text-align:left; \" >";//
   $f = null;
   $title = "HDAW Common Code";
   $f = "{$common_code_dir}/{$show_common}";
   $f_hdoc = pathinfo($f,PATHINFO_DIRNAME)."/".pathinfo($f,PATHINFO_FILENAME).".hdoc"; 
   if (file_exists($f_hdoc)) { $f = $f_hdoc; $tt = file_get_contents($f); }
   elseif (!file_exists($f)) {$t .= "<h2>{$show_common}</h2><br>Common code not found"; $f = null; }
   else $tt = str_replace(array("\n","\r"),array("<br>",""),file_get_contents($f));
   if (!is_null($f)) {
      $t .= "<span style=\"font:8px;color:gray;\">From {$f}</span><br>";
	  $t .= "<span style=\"font-size:12px;color:black;\">";
      $t .= $tt;
	  $t .= "</span>";
      $t .= "</br>";
      }
   $t .= "</div>";
   }
else {
   $t .= "<div style=\"width:100%; height:100%; overflow-y:auto; overflow-x:hidden; text-align:left;  \" >";
   $help_check = false;
   $help_on = array();
   $ff = glob("{$code_help_dir}/*.txt");
   if (is_array($ff)) {
      foreach ($ff as $f) {
         if ($help_check) {
            $c = preg_match('#.*?codehelp/(?P<fn>[^\.]+?)\.txt#i',$f, $fn);
            if (!is_array($fn) || !array_key_exists('fn',$fn)) $t .= "Extracting name from {$f}<br>";
            elseif (!in_array(strtoupper($fn['fn']), $HDA_code_keywords)) {
               $a_fn = strtoupper($fn['fn']);
               if (!method_exists("HDA_library", "_do_call_{$a_fn}")) $t .= "<span style=\"color:red;\" > Function {$a_fn} missing</span><br>";
               $help_on[$a_fn] = true;
               }
            }
         $t .= file_get_contents($f);
         $t .= "</br>";
         }
      }
   if ($help_check) {
      $t .= "Checking for missing help<br>";
      foreach ($HDA_code_functions as $a_fn) {
         if (!array_key_exists($a_fn, $help_on)) $t .= "<span style=\"color:red;\" >Missing {$a_fn}</span><br>";
         }
      }
   $t .= "</div>";
   }
$t .= "</div></center>";
$content .= $t;
$post_load .= "AuxWindowResize();";
_finals_();

?>