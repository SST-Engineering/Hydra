<?php

$HDA_ROOT='rss';
$code_root = "HDAWCodeRoot";
include_once "../{$code_root}/HDA_Initials.php";
include_once "../{$code_root}/HDA_XML.php";
include_once "../{$code_root}/HDA_DB.php";

include_once "../{$code_root}/HDA_Functions.php";
include_once "../{$code_root}/HDA_Process.php";
include_once "../{$code_root}/HDA_CodeCompiler.php";
include_once "../{$code_root}/HDA_Validate.php";
include_once "../{$code_root}/HDA_Email.php";
include_once "../{$code_root}/HDA_Logging.php";
_hydra_("HDA_Enter");
/*
items is:
['title'] ['link'] ['guid'] ['description'] ['date']
*/

$content = NULL;
header("Content-Type: application/xml; charset=ISO-8859-1");

global $HDA_Product_Title;
$feed_title = $HDA_Product_Title;
$feed_description = "Latest process news";

$hosting_link = INIT('HOST');

$news_feed = array();
$ff = glob("RssFeed/*");
foreach ($ff as $f_profile) {
   if (is_dir($f_profile)) {
      $profile_id = pathinfo($f_profile, PATHINFO_FILENAME);
      $a = hda_db::hdadb()->HDA_DB_ReadProfile($profile_id);
      if (is_null($a) || !is_array($a)) remove_dir($f_profile);
      else {
         $f_items = glob($f_profile."/*.*");
         foreach ($f_items as $f) {
            $subject = pathinfo($f, PATHINFO_FILENAME);
            $age = filectime($f);
            if ($age < (time()-24*60*60)) { unlink($f); }
            else {
               $news_feed[] = array('Title'=>$subject, 'Age'=>$age, 'Content'=>$f, 'Profile'=>$a['Title'], 'ItemId'=>$a['ItemId']);
               }
            }
         }
      }
   else unlink($f_profile);
   }
$items = array();
for ($i = 0; $i<count($news_feed); $i++) {
   $item = array();
   $item['title'] ="<![CDATA[From: {$news_feed[$i]['Profile']} Subject: {$news_feed[$i]['Title']}]]>";
   $item['link'] ="";
   $item['guid'] ="{$news_feed[$i]['ItemId']}-{$i}";
   $s = file_get_contents($news_feed[$i]['Content']);
   $item['description'] ="<![CDATA[{$s}]]>";
   $item['date']=$news_feed[$i]['Age'];
   $items[] = $item;
   }

$t = "";
$t .= HDA_rss_open($feed_title, $feed_description, $hosting_link);
$t .= HDA_rss_items($items);
$t .= HDA_rss_close();
echo $t;

function remove_dir($adir) {
   $ff = glob($adir."/*");
   foreach ($ff as $f) {
      if (is_dir($f)) remove_dir($f);
      else unlink($f);
      }
   rmdir($adir);
   }

function HDA_RSStime_RFC822($time) {
   $time = intVal($time); 
   return date("D, d M Y H:i:s", $time)." GMT";
   }

function HDA_rss_open($feed_title, $feed_description, $hosting_link) {
   $t = "";
   $t .= "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>";
      $t .= "<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">";
      $t .= "<channel>";
      $t .= "<title>{$feed_title}</title>";
      $t .= "<link>{$hosting_link}</link>";
      $t .= "<description>{$feed_description}</description>";
      $t .= "<language>en-us</language>";
      $t .= "<pubDate>".HDA_RSStime_RFC822(time())."</pubDate>";
      $t .= "<atom:link href=\"{$hosting_link}rss.php\" rel=\"self\" type=\"application/rss+xml\" />";
      $t .= "<image>";
      $t .= "<title>A La Carte</title>";
      $t .= "<url>{$hosting_link}Images/LoginBanner.png</url>";
      $t .= "<width>156</width>";
      $t .= "<height>42</height>";
      $t .= "<link>{$hosting_link}</link>";
      $t .= "</image>";
   return $t;
   }

function HDA_rss_items($items) {
   $t = "";
   foreach ($items as $item) {
      $t .= "<item>";
      $t .= "<title>{$item['title']}</title>";
      $t .= "<link>";
      $t .= "</link>";
      $t .= "<description>{$item['description']}</description>";
      $t .= "<guid>";
      $t .= "</guid>";
      $t .= "<pubDate>".HDA_RSStime_RFC822($item['date'])."</pubDate>";
      $t .= "</item>";
      }
   return $t;
   }

function HDA_rss_close() {
   $t = "";
   $t .= "</channel></rss>";
   return $t;
   }


$content = NULL;
include_once "../{$code_root}/HDA_Finals.php";
?>