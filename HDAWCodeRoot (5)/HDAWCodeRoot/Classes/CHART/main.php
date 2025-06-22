<?php

/* * *************************************************************
 * Copyright notice
 *
 * (c) 2014 Chi Hoang (info@chihoang.de)
 *  All rights reserved
 *
 * **************************************************************/

define("EPSILON",0.000001);
define("SUPER_TRIANGLE",(float)1000000000);

  // circum circle
class Circle
{
   var $x, $y, $r, $r2, $colinear;
   function __construct($x, $y, $r, $r2, $colinear)
   {
      $this->x = $x;
      $this->y = $y;
      $this->r = $r;
      $this->r2 = $r2;
      $this->colinear=$colinear;
   }
}

class visualize
{
   var $pObj;
   var $path;
   var $lib;
   var $key;
   var $p;
   
   function __construct($pObj,$path,$lib,$key,$p)
   {
      $this->pObj=$pObj;
      $this->path=$path;
      $this->lib=$lib;
      $this->key=$key;
      $this->p=$p;
   }
   
   function erropen()
   {
      print "Cannot open file";
      exit;
   }
   
   function errwrite()
   {
      print "Cannot write file";
      exit;
   }
   
   function genimage()
   {     
         // Generate the image variables
      $im = imagecreate($this->pObj->stageWidth,$this->pObj->stageHeight);
  
      $white = imagecolorallocate ($im,0xff,0xff,0xff);
      $black = imagecolorallocate($im,0x00,0x00,0x00);
      $gray_lite = imagecolorallocate ($im,0xee,0xee,0xee);
      $gray_dark = imagecolorallocate ($im,0x7f,0x7f,0x7f);
      $firebrick = imagecolorallocate ($im,0xb2,0x22,0x22);
      $blue = imagecolorallocate ($im,0x00,0x00,0xff);
      $darkorange = imagecolorallocate ($im,0xff,0x8c,0x00);
      $red = imagecolorallocate ($im,0xff,0x00,0x00);
      $green = imagecolorallocate ($im,0x00,0xff,0x00);
      
      // Fill in the background of the image
      imagefilledrectangle($im, 0, 0, $this->pObj->stageWidth, $this->pObj->stageHeight, $white);
 
      list($x1,$y1)=array($this->p->lat,$this->p->long);
      imagefilledellipse($im, $x1, $y1, 4, 4, $blue);
            
      foreach ($this->lib["set"] as $key => $arr)
      {
            list($x1,$y1) = $arr;
			if (array_key_exists($key, $this->lib["weights"]))
				imagefilledellipse($im, $x1, $y1, $this->lib["weights"][$key], $this->lib["weights"][$key], $darkorange);
            imagefilledellipse($im, $x1, $y1, 4, 4, $green);
      }
      
      foreach ($this->lib["tri"] as $key => $arr)
      {
         foreach ($arr as $ikey => $iarr)
         {
            list($x1,$y1,$x2,$y2) = $iarr;
            imageline($im,$x1+5,$y1+5,$x2+5,$y2+5,$gray_lite);
            imagefilledellipse($im, $x1, $y1, 4, 4, $red);
         }
      } 
     
//      foreach ($this->lib["midpoint"] as $key => $arr)
//      {
//            list($x1,$y1) = $arr;
//            imagefilledellipse($im, $x1, $y1, 4, 4, $darkorange);
//      }

/*        foreach ($this->search[$this->lvl]["tri"] as $key => $arr)
        {
             foreach ($arr as $ikey => $iarr)
             {
                list($x1,$y1,$x2,$y2) = $iarr;
                imagefilledellipse($im, $x1, $y1, 4, 4, $blue);
     //           imageline($im,$x1,$y1,$x2,$y2,$darkorange);
             }
       }
*/                
 
      foreach ($this->lib["voronoi"] as $key => $arr)
      {
         foreach ($arr as $ikey => $iarr)
         {  
            list($x1,$y1) = array(round($iarr[0]->x),round($iarr[0]->y));
            list($x2,$y2) = array(round($iarr[1]->x),round($iarr[1]->y));
            if (abs($x1) != SUPER_TRIANGLE 
                && abs($y1) != SUPER_TRIANGLE 
                    && abs($x2) != SUPER_TRIANGLE 
                        && abs($y2) != SUPER_TRIANGLE)
            {
                 imageline($im,$x1,$y1,$x2,$y2,$gray_dark);
            }
         }                                     
      }
/*    
//     $this->key=6;  
     $keys=array_keys($this->search["poly"]);
     foreach ($this->search["poly"] as $key => $arr) 
     {
        if ($key == $this->key )
        {      
            foreach ($arr as $ikey => $iarr)
            {  
                list($x1,$y1) = array(round($iarr[0]->x),round($iarr[0]->y));
                list($x2,$y2) = array(round($iarr[1]->x),round($iarr[1]->y));
                if (abs($x1) != SUPER_TRIANGLE && abs($y1) != SUPER_TRIANGLE && abs($x2) != SUPER_TRIANGLE && abs($y2) != SUPER_TRIANGLE)
                {
                    imageline($im,$x1,$y1,$x2,$y2,$firebrick);
                }
             } 

             foreach ($this->search["perimeter"][$key] as $ikey=>$iarr) 
             {
                list($x1,$y1) = array(round($iarr[0]->x),round($iarr[0]->y));
                list($x2,$y2) = array(round($iarr[1]->x),round($iarr[1]->y));
                if (abs($x1) != SUPER_TRIANGLE && abs($y1) != SUPER_TRIANGLE && abs($x2) != SUPER_TRIANGLE && abs($y2) != SUPER_TRIANGLE)
                {
                    imageline($im,$x1,$y1,$x2,$y2,$firebrick);
                }
             }
         }    
     }      
*/   

      foreach ($this->lib["convexpoints"] as $key => $arr) 
      {
            list($x1,$y1,$x2,$y2) = $arr; 
            if (abs($x1) != NULL && abs($y1) != NULL && abs($x2) != NULL && abs($y2) != NULL) 
            { 
                imageline($im,$x1+5,$y1+5,$x2+5,$y2+5,$red);   
            }             
      }
          
      ob_start();
      imagepng($im);
      $imagevariable = ob_get_contents();
      ob_clean();
file_put_contents("tmp/clusterdump.txt", print_r($this->lib, true));
         // write to file
      $filename = $this->path."tri_". rand(0,1000).".png";
      $fp = fopen($filename, "w");
      fwrite($fp, $imagevariable);
      if(!$fp)
      {
         $this->errwrite();   
      }
      fclose($fp);
	  return $filename;
   }
   
   function tri()
   {
      if (!$handle = fopen($this->path."tri.csv", "w"))
      {
         $this->erropen();  
      }
      rewind($handle);    
      $c=0;
      foreach ($this->pObj->delaunay as $key => $arr)
      {
         foreach ($arr as $ikey => $iarr)
         {
            if ( !fwrite ( $handle, $iarr[0].",".$iarr[1]."\n" ) )
            {
               $this->errwrite();  
            }
         }
      }
      fclose($handle);   
   }
   
   function pset($path)
   {
      if (!$handle = fopen($this->path."pset.csv", "w"))
      {
         $this->erropen();  
      }
      rewind($handle);    
      $c=0;
      foreach ($this->pObj->pointset as $key => $arr)
      {
         if ( !fwrite ($handle, $arr[0].",".$arr[1]."\n" ) )
         {
            $this->errwrite(); 
         }
      }
      fclose($handle);   
   }
}

class voronoi
{
   var $stageWidth = 400;
   var $stageHeight = 400;
   var $delaunay = array();
   var $pointset = array();
   var $indices = array();
   var $cc = array();
   var $midpoint = array();

   function insidePoly($poly, $pointx, $pointy) 
    {
        $i=$j=0;
        $inside = false;
        for ($i=0,$j=count($poly)-1;$i<count($poly);$j=$i++) 
        {
            if((($poly[$i]->lat>$pointy)!=($poly[$j]->lat>$pointy)) && ($pointx<($poly[$j]->lat-$poly[$i]->lat)*($pointy-$poly[$i]->long)/($poly[$j]->long-$poly[$i]->long)+$poly[$i]->lat)) 
            {
                $inside = !$inside;   
            }
        }
        return $inside;
    }
   
   function dotproduct($x1,$y1,$x2,$y2,$px,$py)
   {
      $dx1 = $x2 - $x1;
      $dy1 = $y2 - $y1;
      $dx2 = $px - $x1;
      $dy2 = $py - $y1;
      $o = ($dx1*$dy2)-($dy1*$dx2);

      //if ($o > 0.0) return(0);
      //if ($o < 0.0) return(1);
      //return(-1);
      return $o;
   }
   
   //LEFT_SIDE = true, RIGHT_SIDE = false, 2 = COLINEAR
   function side($x1,$y1,$x2,$y2,$px,$py)
   {
      $dx1 = $x2 - $x1;
      $dy1 = $y2 - $y1;
      $dx2 = $px - $x1;
      $dy2 = $py - $y1;
      $o = ($dx1*$dy2)-($dy1*$dx2);

      if ($o > 0.0) return(0);
      if ($o < 0.0) return(1);
      return(-1);
   }

   function CircumCircle($x1,$y1,$x2,$y2,$x3,$y3)
   {
      //list($x1,$y1)=array(1,3);
      //list($x2,$y2)=array(6,5);
      //list($x3,$y3)=array(4,7);
      
      $absy1y2 = abs($y1-$y2);
      $absy2y3 = abs($y2-$y3);

      if ($absy1y2 < EPSILON)
      {
		if (($d=($y3-$y2))==0) $d = 1.0;
         $m2 = - ($x3-$x2) / $d;
         $mx2 = ($x2 + $x3) / 2.0;
         $my2 = ($y2 + $y3) / 2.0;
         $xc = ($x2 + $x1) / 2.0;
         $yc = $m2 * ($xc - $mx2) + $my2;
      }
      else if ($absy2y3 < EPSILON)
      {
		if (($d=($y2-$y1))==0) $d = 1.0;
         $m1 = - ($x2-$x1) / $d;
         $mx1 = ($x1 + $x2) / 2.0;
         $my1 = ($y1 + $y2) / 2.0;
         $xc = ($x3 + $x2) / 2.0;
         $yc = $m1 * ($xc - $mx1) + $my1;    
      }
      else
      {
		if (($d=($y2-$y1))==0) $d = 1.0;
         $m1 = - ($x2-$x1) / $d;
		if (($d=($y3-$y2))==0) $d = 1.0;
         $m2 = - ($x3-$x2) / $d;
         $mx1 = ($x1 + $x2) / 2.0;
         $mx2 = ($x2 + $x3) / 2.0;
         $my1 = ($y1 + $y2) / 2.0;
         $my2 = ($y2 + $y3) / 2.0;
		 if (($mm=($m1-$m2))==0) $mm = 1;
		 $xc = ($m1 * $mx1 - $m2 * $mx2 + $my2 - $my1) / ($mm);
		 if ($absy1y2 > $absy2y3)
		 {
			$yc = $m1 * ($xc - $mx1) + $my1;   
		 } else
		 {
			$yc = $m2 * ($xc - $mx2) + $my2;   
		 }
			 
      }
      
      $dx = $x2 - $xc;
      $dy = $y2 - $yc;
      $rsqr = $dx*$dx + $dy*$dy;
      //$rsqr = $dx+$dy;
      $r = sqrt($rsqr);
     
      /* Check for coincident points */
      if($absy1y2 < EPSILON && $absy2y3 < EPSILON)
      {
         $colinear=false; 
      } else
      {
         $colinear=true;
      }
      
/*      if ($r > 0 && $r < 10000) 
      {
        return new Circle($xc, $yc, $r, $rsqr, $colinear);    
      } else {
          return false;
      }*/
       return new Circle($xc, $yc, $r, $rsqr, $colinear);    
   }

   function inside(Circle $c, $x, $y)
   {
      $dx = $x - $c->x;
      $dy = $y - $c->y;
      $drsqr = $dx * $dx + $dy * $dy;
      //$drsqr = $dx+$dy;
      
      //$inside = ($drsqr <= $c->r2) ? true : false;
      $inside = (($drsqr-$c->r2) <= EPSILON) ? true : false;
      //$inside = $inside & $c->colinear;
      //$inside = $inside & ($c->r > EPSILON) ? true : false; 
      return $inside;
   }
   
   function getEdges($n, $x, $y)
   {
      /*
         Set up the supertriangle
         This is a triangle which encompasses all the sample points.
         The supertriangle coordinates are added to the end of the
         vertex list. The supertriangle is the first triangle in
         the triangle list.
      */
      
      $x[$n+0] = -SUPER_TRIANGLE;
      $y[$n+0] = SUPER_TRIANGLE;
      $x[$n+1] = 0;
      $y[$n+1] = -SUPER_TRIANGLE;
      $x[$n+2] = SUPER_TRIANGLE;
      $y[$n+2] = SUPER_TRIANGLE;
    
      // indices       
      $v = array(); 
      $v[] = array($n,$n+1,$n+2);
      
      //sort buffer
      $complete = array();
      $complete[] = false;
      
      /*
         Include each point one at a time into the existing mesh
      */
      foreach ($x as $key => $arr)
      {        
         /*
            Set up the edge buffer.
            If the point (xp,yp) lies inside the circumcircle then the
            three edges of that triangle are added to the edge buffer
            and that triangle is removed.
         */

         $edges=array();
         foreach ($v as $vkey => $varr)
         {  
            //if ($complete[$vkey]) continue;
            list($vi,$vj,$vk)=array($v[$vkey][0],$v[$vkey][1],$v[$vkey][2]);
            $c=$this->CircumCircle($x[$vi],$y[$vi],$x[$vj],$y[$vj],$x[$vk],$y[$vk]);
   
            $d1=abs(pow(($c->x-$x[$vi]),2)+pow(($c->y-$y[$vi]),2)-$c->r2-pow($this->weights[$vi],2));  
            $r1=sqrt($d1);
            $p1=pow(($c->x-$x[$key]),2)+pow(($c->y-$y[$key]),2)-$c->r2-pow($this->weights[$key],2)-$r1;
            
            $d2=abs(pow(($c->x-$x[$vj]),2)+pow(($c->y-$y[$vj]),2)-$c->r2-pow($this->weights[$vj],2));  
            $r2=sqrt($d2);
            $p2=pow(($c->x-$x[$key]),2)+pow(($c->y-$y[$key]),2)-$c->r2-pow($this->weights[$key],2)-$r2;
         
            $d3=abs(pow(($c->x-$x[$vk]),2)+pow(($c->y-$y[$vk]),2)-$c->r2-pow($this->weights[$vk],2));
            $r3=sqrt($d3);
            $p3=pow(($c->x-$x[$key]),2)+pow(($c->y-$y[$key]),2)-$c->r2-pow($this->weights[$key],2)-$r3;
                    
            //if ($c->r > EPSILON && $inside)
            if ($p1<0 && $p2<0 && $p3<0)
            {    
  /*              $x[$vi]+=$this->weights[$vi];
                $y[$vi]+=$this->weights[$vi];

                $x[$vj]+=$this->weights[$vj];
                $y[$vj]+=$this->weights[$vj];

                $x[$vk]+=$this->weights[$vk];
                $y[$vk]+=$this->weights[$vk];
 */
//                $x[$key]+=$this;
//                $y[$key]+=$this->weights[$key];

//                $this->weights[$key]-=$this->weights[$key];

                $edges[]=array($vi,$vj);
                $edges[]=array($vj,$vk);
                $edges[]=array($vk,$vi); 
                unset($v[$vkey]);
                //unset($complete[$vkey]);   
            }
         }
         
         /*
            Tag multiple edges
            Note: if all triangles are specified anticlockwise then all
            interior edges are opposite pointing in direction.
         */
         $edges=array_values($edges);
         foreach ($edges as $ekey => $earr)
         {   
            foreach ($edges as $ikey => $iarr)
            {
               if ($ekey != $ikey)
               {
                  if (($earr[0] == $iarr[1]) && ($earr[1] == $iarr[0]))
                  {
                     unset($edges[$ekey]);
                     unset($edges[$ikey]);
                     
                  }                   
                  elseif (($earr[0] == $iarr[0]) && ($earr[1] == $iarr[1]))
                  {
                     unset($edges[$ekey]);
                     unset($edges[$ikey]);
                  }
               }
            }
         }
         
        $sort=array();
        foreach ($edges as $ekey=>$earr) 
        {
            list($vi,$vj)=array($edges[$ekey][0],$edges[$ekey][1]);
            $angle=$this->dotproduct($x[$vi],$y[$vi],$x[$vj],$y[$vj],$this->stageWidth/2,$this->stageHeight/2);
            $sort[]=$angle;
        }
        array_multisort($sort, SORT_ASC, SORT_NUMERIC, $edges);           
               
        /*
        Form new triangles for the current point
        Skipping over any tagged edges.
        All edges are arranged in clockwise order.
        */
        $complete=array_values($complete);
        $v=array_values($v);
        $ntri=count($v);
        $edges=array_values($edges);
        foreach ($edges as $ekey => $earr)
        {   
            if ($edges[$ekey][0]!=$key && $edges[$ekey][1]!=$key) 
            {
                     $v[] = array($edges[$ekey][0],$edges[$ekey][1],$key);
                     $complete[$ntri++]=0;
            }
        }
        $sort=array();
        foreach ($v as $vkey=>$varr) 
        {
            list($vi,$vj,$vk)=array($v[$vkey][0],$v[$vkey][1],$v[$vkey][2]);
            $sort[]=$this->dotproduct($x[$vi],$y[$vi],$x[$vj],$y[$vj],$x[$vk],$y[$vk],$this->stageWidth/2,$this->stageHeight/2);
        }
        array_multisort($sort, SORT_ASC, SORT_NUMERIC, $v);           
      }
      
      $sort=array();      
      foreach ($v as $key => $arr)
      {
         $this->indices[$key]=$arr;
         $this->indices[$key][]=$arr[0];
      
         $this->delaunay[$key]=array(array($x[$arr[0]],$y[$arr[0]],$x[$arr[1]],$y[$arr[1]]),
                                 array($x[$arr[1]],$y[$arr[1]],$x[$arr[2]],$y[$arr[2]]),
                                 array($x[$arr[2]],$y[$arr[2]],$x[$arr[0]],$y[$arr[0]])                                 
                                 );   
                                 
         $dx=$x[$arr[1]]-$x[$arr[0]]; 
         $dy=$y[$arr[1]]-$y[$arr[0]]; 
         $this->dist[$key][]=$dx*$dx+$dy*$dy; 
         $dx=$x[$arr[2]]-$x[$arr[1]]; 
         $dy=$y[$arr[2]]-$y[$arr[1]]; 
         $this->dist[$key][]=$dx*$dx+$dy*$dy; 
         $dx=$x[$arr[0]]-$x[$arr[2]]; 
         $dy=$y[$arr[0]]-$y[$arr[2]]; 
         $this->dist[$key][]=$dx*$dx+$dy*$dy; 
      }
      return $v;
   }
 
   function edgemidpoint($arr)
   {
        return array(($arr[0]->x+$arr[1]->x)/2,($arr[0]->y+$arr[1]->y)/2);
   }
   
   function buildtree($input) 
   {
        $this->ktemp=$this->kset = array();
        $lvl=0; 
        $this->kset[$lvl]=$input;
        
        do {            
            $distance=array();
            $this->make_delaunay($this->kset[$lvl],$lvl);
                      
            foreach ($this->delaunay as $key => $arr) 
            {
                $tri=array();
                list($x1,$y1,$x2,$y2)=array($arr[0][0],$arr[0][1],$arr[0][2],$arr[0][3]);
                $tri[] = sqrt( pow(($x2 - $x1), 2) + pow(($y2 - $y1),2) ); 
                list($x1,$y1,$x2,$y2)=array($arr[1][0],$arr[1][1],$arr[1][2],$arr[1][3]);
                $tri[] = sqrt( pow(($x2 - $x1), 2) + pow(($y2 - $y1),2) ); 
                list($x1,$y1,$x2,$y2)=array($arr[2][0],$arr[2][1],$arr[2][2],$arr[2][3]);
                $tri[] = sqrt( pow(($x2 - $x1), 2) + pow(($y2 - $y1),2) ); 
                asort($tri);
                $top=array_keys($tri);
                $distance[$key][$top[0]] = $tri[$top[0]];                    
            }
            $sortX = array(); 
            foreach($distance as $key => $arr)
            {
                $top=array_keys($arr);
                $sortX[$key]=$arr[$top[0]];
            } 
            array_multisort($sortX, SORT_ASC, SORT_NUMERIC, $distance);

            $points=array();
            $c=0;
            foreach ($distance as $key => $arr) 
            {
                $top=array_keys($arr);
                if ($arr[$top[0]]>0) 
                {
                    $this->ktemp[$lvl][]=$this->delaunay[$key][$top[0]];
                    $c++;
                }
                if ($c>1) break;
            }

            $parent=array();        
            $c=0;
            $temp=$aux=$this->kset[$lvl];
            foreach ($this->ktemp[$lvl] as $key => $arr) 
            {
                list($x1,$y1,$x2,$y2)=$arr;
                foreach ($this->kset[$lvl] as $ikey=>$iarr) 
                {
                    if ($x1==$iarr[0] && $y1==$iarr[1] ||
                        $x2==$iarr[0] && $y2==$iarr[1] 
                        ) {
                            $parent[$ikey]=$iarr;
                            unset($aux[$ikey]);
                            unset($temp[$ikey]);
                            $c++;
                    }
                    if ($c>1) break;
                }   
            }
            $edge=array_keys($parent);
            $test=false;   
            foreach ($parent as $key=>$arr) 
            {
                if (count($arr)>3) 
                {
                    $test=true;
                    $root=$key;       
                    break;
                }
            }
            if ($test==false) 
            {
                 $root=array_shift($edge);
            } else 
            {
                foreach ($edge as $key=>$arr) 
                {
                    if ($root==$arr) 
                    {
                        foreach ($parent[$root] as $ikey=>$iarr) 
                        {
                            if (is_array($iarr)) 
                            {
                                $push[]=array($ikey,$parent[$root][$ikey]);
                                unset($edge[$key]);
                                unset($parent[$root][$ikey]);
                            }
                        }
                    }
                }
            }
            $aux[$root]=$parent[$root];
            
            foreach ($edge as $ikey=>$iarr) 
            {
                $aux[$root]["_".$iarr]=$parent[$iarr];
                if (!empty($push)) 
                {
                    foreach ($push as $iikey=>$iiarr) 
                    {                     
                        if (count($aux[$root]["_".$iarr])<3) 
                        {
                            list($iiikey,$iiiarr)=$iiarr;
                            $aux[$root]["_".$iarr][$iiikey]=$iiiarr; 
                            unset($push[$iikey]); 
                        }
                    }  
                }
            }    
            $lvl++;  
            $this->kset[$lvl]=$aux;
            $done=count($temp);
        } while ($done>3 && count($this->kset[$lvl])!=count($this->kset[$lvl-1]));
 
        $size=count($this->kset);
        echo "Build tree:done. Tree size:$size\r\n";
        return array($this->kset[$size-1],$size);
   }
   
   function make_delaunay($input,$lvl) 
   {
        $this->delaunay = array();
        $this->indices = array();
        $this->pointset = $input;
        $this->weights = array();
        $this->convexhull = array();
        $this->convexpoints = array();
        $w=$x=$y=$sortX = array();

        foreach($input as $key => $arr)
        {
            $sortX[$key] = $arr[0];
        } 
        array_multisort($sortX, SORT_ASC, SORT_NUMERIC, $input);
         
        foreach ($input as $key => $arr)
        {
            list($x[],$y[],$this->weights[]) = array($arr[0],$arr[1],$arr[2]);
        }

        $this->weights[]=0;
        $this->weights[]=0;
        $this->weights[]=0;

        $result=$this->getEdges(count($input), $x, $y);

        $n=count($this->pointset); 
        foreach ($this->indices as $key => $arr) 
        { 
             foreach ($this->indices as $ikey => $iarr) 
             { 
                if ($key != $ikey) 
                { 
                   if ( ($arr[0]==$iarr[1] && $arr[1]==$iarr[0]) || 
                        ($arr[0]==$iarr[2] && $arr[1]==$iarr[1]) || 
                        ($arr[0]==$iarr[3] && $arr[1]==$iarr[2]) || 
                                      
                        ($arr[1]==$iarr[1] && $arr[2]==$iarr[0]) || 
                        ($arr[1]==$iarr[2] && $arr[2]==$iarr[1]) || 
                        ($arr[1]==$iarr[3] && $arr[2]==$iarr[2]) || 
                         
                        ($arr[2]==$iarr[1] && $arr[3]==$iarr[0]) || 
                        ($arr[2]==$iarr[2] && $arr[3]==$iarr[1]) || 
                        ($arr[2]==$iarr[3] && $arr[3]==$iarr[2])                               
                      ) 
                   { 
                      if ($arr[0] >= $n || $arr[1] >= $n || $arr[2] >= $n || $arr[3] >= $n) 
                      {    
                         $this->convexhull[$key]=$this->delaunay[$key]; 
                      } 
                   } 
               }
           }
        }       

       foreach ($this->convexhull as $key => $arr) 
       { 
             foreach ($arr as $ikey => $iarr) 
             { 
                list($x1,$y1,$x2,$y2)=$iarr; 
                if (abs($x1) != SUPER_TRIANGLE 
                    && abs($y1) != SUPER_TRIANGLE 
                        && abs($x2) != SUPER_TRIANGLE 
                            && abs($y2) != SUPER_TRIANGLE) 
                { 
                   foreach ($this->pointset as $iikey => $iiarr) 
                   { 
                      list($_x,$_y,$_w)=$iiarr;
                      if (array($_x,$_y)==array($x1,$y1)) 
                      { 
                         $this->convexpoints[$key]=$iarr;
                      } 
                   } 
                } 
             }
        } 
        
        $deleted=array();
        foreach ($this->delaunay as $key => $arr)
        {     
            foreach ($this->delaunay as $ikey => $iarr)
            {  if (!array_key_exists($key, $this->convexhull)) continue;
               if ( $key!=$ikey && !$this->convexhull[$key] &&
                    ($arr[0][0]!=$iarr[0][0] && $arr[0][1]!=$iarr[0][1]) &&
                    ($arr[0][0]!=$iarr[0][2] && $arr[0][1]!=$iarr[0][3]) && 
                    ($arr[0][0]!=$iarr[1][2] && $arr[0][1]!=$iarr[1][3]) && 
                                  
                    ($arr[1][0]!=$iarr[0][0] && $arr[1][1]!=$iarr[0][1]) && 
                    ($arr[1][0]!=$iarr[0][2] && $arr[1][1]!=$iarr[0][3]) && 
                    ($arr[1][0]!=$iarr[1][2] && $arr[1][1]!=$iarr[1][3]) && 
                                                 
                    ($arr[2][0]!=$iarr[0][0] && $arr[2][1]!=$iarr[0][1]) && 
                    ($arr[2][0]!=$iarr[0][2] && $arr[2][1]!=$iarr[0][3]) && 
                    ($arr[2][0]!=$iarr[1][2] && $arr[2][1]!=$iarr[1][3])                          
                  ) 
               { 
                   $points=array();
                   $points[]=new Point($iarr[0][0],$iarr[0][1]);
                   $points[]=new Point($iarr[0][2],$iarr[0][3]);
                   $points[]=new Point($iarr[1][2],$iarr[1][3]);
                   
                   $tt=$this->insidePoly($points,$arr[0][0],$arr[0][1]);
                   if ($tt)
                   {
                       unset($this->delaunay[$key]);
                       unset($this->indices[$key]);
                       $deleted[]=$key;
                       break;
                   }    
                   $tt=$this->insidePoly($points,$arr[1][0],$arr[1][1]);
                   if ($tt)
                   {
                       unset($this->delaunay[$key]);
                       unset($this->indices[$key]);
                       $deleted[]=$key;
                       break;
                   }     
                   $tt=$this->insidePoly($points,$arr[2][0],$arr[2][1]);
                   if ($tt)
                   {
                       unset($this->delaunay[$key]);
                       unset($this->indices[$key]);
                       $deleted[]=$key;
                       break;
                   }                 
                }                    
            }
        }
        
        $set=$this->pointset;
        foreach ($set as $key=>$arr) 
        {
            $tabu=false;
            foreach ($this->indices as $ikey=>$iarr) 
            {
                foreach ($iarr as $iikey=>$iiarr) 
                {                
                    if ($key==$iiarr) 
                    {
                        $tabu=true;
                        break;
                    }
                }
                if ($tabu) break;
            }
            if (!$tabu) 
            {
                unset($set[$key]);
            }
        }
        $set=array_values($set);

        $this->delaunay = array();
        $this->indices = array();
        $this->weights = array();
        $w=$x=$y=$sortX = array();

        foreach($set as $key => $arr)
        {
            $sortX[$key] = $arr[0];
        } 
        array_multisort($sortX, SORT_ASC, SORT_NUMERIC, $set);
         
        foreach ($set as $key => $arr)
        {
            list($x[],$y[],$this->weights[]) = array($arr[0],$arr[1],$arr[2]);
        }

        $this->weights[]=0;
        $this->weights[]=0;
        $this->weights[]=0;
        
        $result=$this->getEdges(count($set), $x, $y);  
        
        $old=array();
        $loop=0;
        if ($lvl==0) 
        {
            do {
                if (count($set)<3) break;
                $old[count($set)]=true;
                $deleted=array();
                foreach ($this->delaunay as $key => $arr)
                {     
                    foreach ($this->delaunay as $ikey => $iarr)
                    {  if (!array_key_exists($key, $this->convexhull)) continue;
                       if ( $key!=$ikey && !$this->convexhull[$key] &&
                            ($arr[0][0]!=$iarr[0][0] && $arr[0][1]!=$iarr[0][1]) &&
                            ($arr[0][0]!=$iarr[0][2] && $arr[0][1]!=$iarr[0][3]) && 
                            ($arr[0][0]!=$iarr[1][2] && $arr[0][1]!=$iarr[1][3]) && 
                                          
                            ($arr[1][0]!=$iarr[0][0] && $arr[1][1]!=$iarr[0][1]) && 
                            ($arr[1][0]!=$iarr[0][2] && $arr[1][1]!=$iarr[0][3]) && 
                            ($arr[1][0]!=$iarr[1][2] && $arr[1][1]!=$iarr[1][3]) && 
                                                         
                            ($arr[2][0]!=$iarr[0][0] && $arr[2][1]!=$iarr[0][1]) && 
                            ($arr[2][0]!=$iarr[0][2] && $arr[2][1]!=$iarr[0][3]) && 
                            ($arr[2][0]!=$iarr[1][2] && $arr[2][1]!=$iarr[1][3])                          
                          ) 
                       { 
                           $points=array();
                           $points[]=new Point($iarr[0][0],$iarr[0][1]);
                           $points[]=new Point($iarr[0][2],$iarr[0][3]);
                           $points[]=new Point($iarr[1][2],$iarr[1][3]);
                           
                           $tt=$this->insidePoly($points,$arr[0][0],$arr[0][1]);
                           if ($tt)
                           {
                               unset($this->delaunay[$key]);
                               unset($this->indices[$key]);
                               $deleted[]=$key;
                               break;
                           }    
                           $tt=$this->insidePoly($points,$arr[1][0],$arr[1][1]);
                           if ($tt)
                           {
                               unset($this->delaunay[$key]);
                               unset($this->indices[$key]);
                               $deleted[]=$key;
                               break;
                           }     
                           $tt=$this->insidePoly($points,$arr[2][0],$arr[2][1]);
                           if ($tt)
                           {
                               unset($this->delaunay[$key]);
                               unset($this->indices[$key]);
                               $deleted[]=$key;
                               break;
                           }                 
                        }                    
                    }
                }
                
                $set=$this->pointset;
                foreach ($set as $key=>$arr) 
                {
                    $tabu=false;
                    foreach ($this->indices as $ikey=>$iarr) 
                    {
                        foreach ($iarr as $iikey=>$iiarr) 
                        {                
                            if ($key==$iiarr) 
                            {
                                $tabu=true;
                                break;
                            }
                        }
                        if ($tabu) break;
                    }
                    if (!$tabu) 
                    {
                        unset($set[$key]);
                    }
                }
                $set=array_values($set);

                $this->delaunay = array();
                $this->indices = array();
                $this->weights = array();
                $w=$x=$y=$sortX = array();

                foreach($set as $key => $arr)
                {
                    $sortX[$key] = $arr[0];
                } 
                array_multisort($sortX, SORT_ASC, SORT_NUMERIC, $set);
                 
                foreach ($set as $key => $arr)
                {
                    list($x[],$y[],$this->weights[]) = array($arr[0],$arr[1],$arr[2]);
                }

                $this->weights[]=0;
                $this->weights[]=0;
                $this->weights[]=0;
                
                $result=$this->getEdges(count($set), $x, $y);  
                $loop++;
                
            } while ($loop<20);
        }
   }   
   
   function make_voronoi () 
   {
        $this->edges = array();
        $this->cc = array();
        $this->voronoi = array();
        $this->polygone = array();
        $this->border = array();
        $this->midpoint = array();
                
        foreach ($this->delaunay as $key => $arr)
        {       
             $this->cc[$key]=$this->CircumCircle($arr[0][0],$arr[0][1],
                                                 $arr[0][2],$arr[0][3],
                                                 $arr[1][2],$arr[1][3]);
        }    

          foreach ($this->indices as $key => $arr)
          {
             foreach ($this->indices as $ikey => $iarr)
             {
                if ($key != $ikey)
                {
                   if ( ($arr[0]==$iarr[1] && $arr[1]==$iarr[0]) ||
                        ($arr[0]==$iarr[2] && $arr[1]==$iarr[1]) ||
                        ($arr[0]==$iarr[3] && $arr[1]==$iarr[2]) ||
                                     
                        ($arr[1]==$iarr[1] && $arr[2]==$iarr[0]) ||
                        ($arr[1]==$iarr[2] && $arr[2]==$iarr[1]) ||
                        ($arr[1]==$iarr[3] && $arr[2]==$iarr[2]) ||
                        
                        ($arr[2]==$iarr[1] && $arr[3]==$iarr[0]) ||
                        ($arr[2]==$iarr[2] && $arr[3]==$iarr[1]) ||
                        ($arr[2]==$iarr[3] && $arr[3]==$iarr[2])     
                      )
                   {
                      $this->voronoi[$key][$ikey]=array($this->cc[$key], $this->cc[$ikey]); 
                      $this->edges[]=array($this->cc[$key],$this->cc[$ikey]);
                   }
                }
             }
          }
          
          foreach ($this->edges as $key => $arr) 
          {
                list($x1,$y1)=array($arr[0]->x,$arr[0]->y);
                list($x2,$y2)=array($arr[1]->x,$arr[1]->y);
                if ($x1>=0 && $x1<=$this->stageWidth && $x2>=0 &&
                     $x2<=$this->stageWidth && $y1>=0 && $y1<=$this->stageHeight 
                      && $y2>=0 && $y2<=$this->stageHeight 
              )
              {
                    $this->midpoint[$key]=$this->edgemidpoint($arr);
              } else 
              {
                    $this->border[$key]=$this->edgemidpoint($arr);   
              }        
          }
          
          foreach ($this->midpoint as $key => $arr) 
          {
                list($x1,$y1)=$arr;
                $distance=array();
                foreach ($this->pointset as $ikey => $iarr) 
                {
                    list($x2,$y2)=$iarr;
                    $distance[$ikey] = sqrt( pow(($x2 - $x1), 2) + pow(($y2 - $y1),2) );  
                }
                asort($distance);      
                $keys=array_keys($distance);
                $this->polygone[$keys[0]][]=$this->edges[$key];
                $this->polygone[$keys[1]][]=$this->edges[$key];
          }
 
          $this->perimeter=array();
          foreach ($this->border as $key => $arr) 
          {
                list($x1,$y1)=$arr;
                $distance=array();
                foreach ($this->pointset as $ikey => $iarr) 
                {
                    list($x2,$y2)=$iarr;
                    $distance[$ikey] = sqrt( pow(($x2 - $x1), 2) + pow(($y2 - $y1),2) );  
                }
                asort($distance);      
                $keys=array_keys($distance);
                $this->polygone[$keys[0]][]=$this->edges[$key];
                $this->perimeter[$keys[1]][]=$this->edges[$key];                
          }
  
          foreach ($this->polygone as $keys=>$arr) 
          {
                foreach ($arr as $ikey=>$iarr) 
                {
                     foreach ($arr as $iikey=>$iiarr) 
                     {
                        if ($ikey!=$iikey)
                        {                     
                            if ($iarr[0]->x == $iiarr[1]->x && $iarr[0]->y == $iiarr[1]->y 
                            && $iarr[1]->x == $iiarr[0]->x && $iarr[1]->y == $iiarr[0]->y) 
                            {
                                unset($arr[$ikey]);
                                unset($this->polygone[$keys][$ikey]);
                            }   
                         }
                     }
                }
          }
            
          foreach ($this->perimeter as $keys=>$arr) 
          {
                foreach ($arr as $ikey=>$iarr) 
                {
                     foreach ($arr as $iikey=>$iiarr) 
                     {
                        if ($ikey!=$iikey)
                        {                 
                            if ($iarr[0]->x == $iiarr[1]->x && $iarr[0]->y == $iiarr[1]->y 
                            && $iarr[1]->x == $iiarr[0]->x && $iarr[1]->y == $iiarr[0]->y) 
                            {
                                unset($arr[$ikey]);
                                unset($this->perimeter[$keys][$ikey]);
                            }          
                        }
                     }
                }
          }          
          return $this->voronoi;     
   }  
    
   function main($pointset=0,$stageWidth=400,$stageHeight=400)
   {
      $this->stageWidth = $stageWidth;
      $this->stageHeight = $stageHeight;    
      $this->pointset = array();
      
      if ($pointset==0)
      {         
         for ($i=0; $i<15; $i++) 
         {
            list($x,$y,$w)=array(rand(1,$this->stageWidth),(float)rand(1,$this->stageHeight),(float)rand(10,100));
            $this->pointset[]=array($x,$y,$w);
         }
      } else
      { 
         $this->pointset=$pointset;   
      }
      return $this->pointset;
   }
}
 
 //Point class, storage of lat/long-pairs
class Point {
    public $lat;
    public $long;
    function __construct($lat, $long) 
    {
        $this->lat = $lat;
        $this->long = $long;
    }
}

class korder
{
    function main ($pObj) 
    {
        // Generate the image variables
        $im = imagecreate($pObj->stageWidth,$pObj->stageHeight);

        $white = imagecolorallocate ($im,0xff,0xff,0xff);
        $black = imagecolorallocate($im,0x00,0x00,0x00);
        $gray_lite = imagecolorallocate ($im,0xee,0xee,0xee);
        $gray_dark = imagecolorallocate ($im,0x7f,0x7f,0x7f);
        $firebrick = imagecolorallocate ($im,0xb2,0x22,0x22);
        $blue = imagecolorallocate ($im,0x00,0x00,0xff);
        $darkorange = imagecolorallocate ($im,0xff,0x8c,0x00);
        $red = imagecolorallocate ($im,0xff,0x00,0x00);

        // Fill in the background of the image
        imagefilledrectangle($im, 0, 0, $pObj->stageWidth, $pObj->stageHeight, $white);
 
        foreach ($pObj->kset as $key=>$arr) 
        {
            $pObj->make_delaunay($arr,$key);
            $pObj->make_voronoi();

            foreach ($pObj->delaunay as $ikey => $iarr)
            {
                 foreach ($iarr as $iikey => $iiarr)
                 {
                     list($x1,$y1,$x2,$y2) = $iiarr;
    //                     imageline($im,$x1+5,$y1+5,$x2+5,$y2+5,$gray_lite);
                     imagefilledellipse($im, $x1, $y1, 4, 4, $darkorange);
                 }
            }

            foreach ($pObj->voronoi as $ikey => $iarr)
            {
                 foreach ($iarr as $iikey => $iiarr)
                 {  
                    list($x1,$y1) = array(round($iiarr[0]->x),round($iiarr[0]->y));
                    list($x2,$y2) = array(round($iiarr[1]->x),round($iiarr[1]->y));
                    if (abs($x1) != SUPER_TRIANGLE 
                        &&  abs($y1) != SUPER_TRIANGLE 
                            && abs($x2) != SUPER_TRIANGLE 
                                && abs($y2) != SUPER_TRIANGLE)
                        {
                            imageline($im,$x1,$y1,$x2,$y2,$gray_dark);
                        }
                 }                                     
            }
        }

        ob_start();
        imagepng($im);
        $imagevariable = ob_get_contents();
        ob_clean();

         // write to file
        $filename = "tmp/tri_". rand(0,1000).".png";
        $fp = fopen($filename, "w");
        fwrite($fp, $imagevariable);
        if(!$fp)
        {
             die("Cannot open file!");   
        }
        fclose($fp);
    }
}

class nearestneighbor
{
    public $pObj;
    public $tree;
    public $p;
    
    function show ($pObj,$find,$root,$p) 
    {
        $pObj->make_delaunay($pObj->kset[$root],$root);
        $pObj->make_voronoi();

        $lib["poly"]=$pObj->polygone;       
        $lib["voronoi"]=$pObj->voronoi;
        $lib["tri"]=$pObj->delaunay;
        $lib["perimeter"]=$pObj->perimeter;
        $lib["set"]=$pObj->pointset;
        $lib["convexpoints"]=$pObj->convexpoints;
        $lib["convexhull"]=$pObj->convexhull;
        $lib["weights"]=$pObj->weights;
 
        $temp=explode("_",$find[0]);
        $temp=array_reverse($temp);
        foreach ($temp as $key) 
        {
            foreach ($lib["poly"] as $ikey=>$iarr) 
            {
                if ($key==$ikey) 
                {
                    break;   
                }
            }
            if ($key==$ikey) 
            {
                break;   
            }
        }   
        $vis=new visualize($pObj,"tmp/",$lib,$key,$p);
        return $vis->genimage(); 
    }
    
    function querytree ($root, $tree, $p)
    {
        do {
            list($x1,$y1)=array($p->lat,$p->long);
            $distance=array();
            foreach ($tree as $key=>$arr) 
            {
                foreach ($arr as $ikey=>$iarr) 
                {
                    if (is_array($iarr))
                    {
                        $temp=ltrim($ikey,'_');
                        $result=$this->querytree($key, array($temp=>$iarr), $p); 
                        $distance[$key."_".$result[0]]=$result[1];
                    } else 
                    {
                        list($x2,$y2)=$arr;
                        $distance[$key] = sqrt( pow(($x2 - $x1), 2) + pow(($y2 - $y1),2) );  
                    }
                }
            }
            asort($distance);      
            $keys=array_keys($distance);  
            return array($keys[0],$distance[$keys[0]]);                
        } while (true);        
    }

    function insidePoly($poly, $pointx, $pointy) 
    {
        $i=$j=0;
        $inside = false;
        for ($i=0,$j=count($poly)-1;$i<count($poly);$j=$i++) 
        {
            if((($poly[$i]->y>$pointy)!=($poly[$j]->y>$pointy)) && ($pointx<($poly[$j]->x-$poly[$i]->x)*($pointy-$poly[$i]->y)/($poly[$j]->y-$poly[$i]->y)+$poly[$i]->x)) 
            {
                $inside = !$inside;   
            }
        }
        return $inside;
    }

   function dotproduct($x1,$y1,$x2,$y2,$px,$py)
   {
      $dx1 = $x2 - $x1;
      $dy1 = $y2 - $y1;
      $dx2 = $px - $x1;
      $dy2 = $py - $y1;
      $o = ($dx1*$dy2)-($dy1*$dx2);

      //if ($o > 0.0) return(0);
      //if ($o < 0.0) return(1);
      //return(-1);
      return $o;
   }

    function polytest($pObj,$root,$p) 
    {
        $pObj->make_delaunay($pObj->kset[$root],$root);
        $pObj->make_voronoi();

        $lib["poly"]=$pObj->polygone;       
        $lib["voronoi"]=$pObj->voronoi;
        $lib["tri"]=$pObj->delaunay;
        $lib["perimeter"]=$pObj->perimeter;
        $lib["set"]=$pObj->pointset;
        $lib["convexpoints"]=$pObj->convexpoints;
        $lib["convexhull"]=$pObj->convexhull;
        $lib["weights"]=$pObj->weights;
        
        foreach ($lib["poly"] as $key=>$arr) 
        {
               $sorted=$sort=$poly=array();
               $number=false;
               
               foreach ($arr as $ikey=>$iarr) 
               {
                    $sort[]=$this->dotproduct($iarr[0]->x,$iarr[0]->y,$iarr[1]->x,$iarr[1]->y,$pObj->stageWidth/2,$pObj->stageHeight/2);
                    $poly[]=array($iarr[0],$iarr[1]); 
               }
               foreach ($lib["perimeter"][$key] as $ikey=>$iarr) 
               {
                    $sort[]=$this->dotproduct($iarr[0]->x,$iarr[0]->y,$iarr[1]->x,$iarr[1]->y,$pObj->stageWidth/2,$pObj->stageHeight/2);
                    $poly[]=array($iarr[0],$iarr[1]);                
               }
               array_multisort($sort, SORT_DESC, SORT_NUMERIC, $poly);

               foreach ($poly as $ikey=>$iarr) 
               {
                   $sorted[]=$iarr[0];
                   $sorted[]=$iarr[1];
               }
               
/*               foreach ($lib["perimeter"][$key] as $ikey=>$iarr) 
               {
                    $poly[]=$iarr[0]; 
                    $poly[]=$iarr[1];
               }*/
               $number=$this->insidePoly($sorted,$p->lat,$p->long);
               if ($number!=false) 
               {
                   $number=$key;
                   break;
               }
        }
        
        $vis=new visualize($pObj,"tmp/",$lib,$number,$p);
        $vis->genimage(); 

        return $number;   
    }
    
    function main ($pObj,$tree,$p) 
    {
        $this->pObj=$pObj;
        $this->tree=$tree;
        $find=$this->querytree(0,$tree,$p);
        $path=implode("/",explode("_",$find[0]));   
        echo "Point:$path\r\n";   
        return $find;
    }
}
?>