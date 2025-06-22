<?php
//include_once "../{$code_root}/Classes/CHART/Chart.php";


function HDA_Graph($account, $report, $tag, &$_data, $gsize=600, $to_file=null) {
   $g = NULL;
   switch ($tag) {
      case 'RT_PIE':
         $g = draw_pieChart($_data, $gsize);
         break;
      case 'RT_FLATPIE':
         $g = draw_flatPieChart($_data, $gsize);
         break;
      case 'RT_LINE':
         $g = draw_lineChart($_data, $gsize);
         break;
      case 'RT_MULTILINE':
         $g = draw_multiLineChart($_data, $gsize);
         break;
      case 'RT_BAR':
         $g = draw_barChart($_data, $gsize, $to_file);
         break;
      case 'RT_MULTIBAR':
         $g = draw_multiBarChart($_data, $gsize);
         break;
	  case 'RT_ASSOC':
		 $g = draw_assocChart($_data, $gsize);
		 break;
	  case 'RT_CLUSTER':
		 $g = draw_clusterChart($_data, $gsize);
		 break;
      }
   $kill_cache = rand(1,10000);
   if (!is_null($g)) {
	  if (is_null($to_file)) {
		  $file = "tmp/{$account}";
		  if (!@file_exists($file)) @mkdir($file);
		  $file .= "/{$report}_*.png";
		  $f = glob($file);
		  if (isset($f) && is_array($f)) foreach ($f as $ff) unlink($ff);
		  $file = "tmp/{$account}/{$report}_{$kill_cache}_".time().".png";
	  }
	  else $file = "{$to_file}/{$report}_{$kill_cache}_".time().".png";
      $g->Render($file);
      }
   else $file = "Images/GraphicsError.jpg";
   return $file;
   }

function draw_getGraphics($gsize, &$size) {
   $size = (is_array($gsize))?$gsize:array($gsize, $gsize);
   $g = new pChart($size[0],$size[1]);
   $g->setFontProperties("tahoma.ttf",10);
   $g->setGraphArea(0, 0,$size[0], $size[1]);
   $g->drawGraphArea(255,255,255,FALSE);
   $g->drawFilledRoundedRectangle(7,7,$size[0]-14,$size[1]-14,5,240,240,240);
   $g->drawRoundedRectangle(5,5,$size[0]-10,$size[1]-10,5,230,230,230);
   $size[0]-=10; $size[1]-=10;
   return $g;
   }

function draw_pieChart(&$data, $gsize) {
   $g = draw_getGraphics($gsize, $size);
   PRO_DrawPieGraph($g, $data,round($size[0]/2),round($size[1]/2),round($size[0]/3));
   return $g;
}

function draw_flatPieChart(&$data, $gsize) {
   $g = draw_getGraphics($gsize, $size);
   PRO_DrawFlatPieGraph($g, $data,round($size[0]/2),round($size[1]/2),round($size[0]/3));
   return $g;
}

function draw_barChart(&$data, $gsize, $to_file=null) {
   $g = draw_getGraphics($gsize, $size);
   PRO_DrawBarGraph($g, $data, $size, $to_file);
   return $g;
}

function draw_multiBarChart(&$data, $gsize) {
   $g = draw_getGraphics($gsize, $size);
   PRO_DrawMultiBarGraph($g, $data, $size);
   return $g;
}

function draw_lineChart(&$data, $gsize) {
   $g = draw_getGraphics($gsize, $size);
   PRO_DrawLineGraph($g, $data, $size);
   return $g;
}

function draw_multiLineChart(&$data, $gsize) {
   $g = draw_getGraphics($gsize, $size);
   PRO_DrawMultiLineGraph($g, $data, $size);
   return $g;
}

function draw_assocChart(&$data, $gsize) {
   $g = draw_getGraphics($gsize, $size);
   PRO_DrawAssoc($g, $data, $size);
   return $g;
}

function draw_clusterChart(&$data, $gsize) {
   $g = draw_getGraphics($gsize, $size);
   PRO_ClusterGraph($g, $data, $size);
   return $g;
}

global $Graph_color;
$Graph_color = array(array(255,0,0),
                     array(0,125,0),
                     array(0,0,255),
                     array(46,151,224),
                     array(176,46,224),
                     array(224,46,117),
                     array(92,224,46),
                     array(224,176,46),
                     array(46,176,46),
                     array(176,224,46),
                     array(188,46,224),
			   array(255,204,0),
			   array(102,51,255),
			   array(102,0,102),
                     array(176,176,224),
                     array(224,176,117),
                     array(92,176,46),
                     array(224,92,46),
                     array(46,224,46),
                     array(176,176,46),
                     array(188,176,224),
                     array(46,224,176),
                     array(176,176,176),
                     array(188,176,176),
			   array(102,102,255)
                         );

function PRO_LookUpColor($i) {
   global $Graph_color;
   while ($i>=count($Graph_color)) $i -= count($Graph_color);
   return $Graph_color[$i];
   }
function PRO_LookUpRGBColor($i) {
   return PRO_ColorArrayAsRGB(PRO_LookUpColor($i));
   }
function PRO_ColorArrayAsRGB($a) {
   return "rgb({$a[0]},{$a[1]},{$a[2]})";
   }




?>