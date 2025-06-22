<?php

function _planViewGrid($filter) {
   global $Action;
   global $ActionLine;
   global $DevUser;
   global $code_root, $home_root;
   global $post_load;
   global $_ViewHeight;

   
   $t = "";
   $t .= "<div id=\"planViewGrid\" style=\"width:100%;height:".($_ViewHeight-80)."px;overflow-x:hidden;overflow-y:hidden;\"></div>";
   $t .= "<input type=\"hidden\" id=\"savedGrid\" value=\"\">";
   _include_css(array("dhtmlxgrid","dhtmlxgrid_dhx_skyblue","dhtmlxcalendar","dhtmlxcalendar_dhx_skyblue"));
   _include_script(array("dhtmlxcommon","dhtmlxgrid","dhtmlxgridcell","dhtmlxgrid_nxml","dhtmlxcalendar","dhtmlxgrid_excell_dhxcalendar","dhtmlxdataprocessor","GridView"));
   $on = ($DevUser)?1:0;
   $post_load .= "runGridView({$on}, '{$filter}');";
   return $t;
   }
   


    
?>