<?PHP

function HDA_getRss($url, $refresh = false, &$error) {
	$rss=new rssFetch($url, $refresh, 10, $error, $dated);
	if (!is_null($rss)) {
		$did_parse = $rss->parse();
		if ($did_parse===true) {
			$r = array('TITLE'=>array('TITLE'=>$rss->title,'LINK'=>$rss->link,'DESCRIPTION'=>$rss->description,'COPYRIGHT'=>$rss->copyright),
			'IMAGE'=>array('TITLE'=>$rss->image_title,'LINK'=>$rss->image_link,'URL'=>$rss->image_url),
			'ITEMS'=>$rss->items,'DATED'=>$dated);
			$s = "<h1>".$rss->title_s()."</h1>";
			$s .= $rss->leading_image();
			$s .= $rss->items_s();
			$r['SCREEN'] = $s;
			return $r;
		}
	}
	file_put_contents("rss\\".rssFetch::cacheFileForUrl($url).".rss",print_r($rss,true)." URL:{$url}");

	 
	return false;
}

function HDA_trendingRss($url, &$ww, &$text, $refresh = false) {
	if (($r = HDA_getRss($url, $refresh, $error)) !== false) {
		foreach ($r['ITEMS'] as $item) {
			$s = "<span style=\"color:blue;\" >{$item['TITLE']}</span>  <span style=\"color:gray;\" >{$item['DESCRIPTION']}</span>";
			$b = _word_tokens($s);
			_count_words($ww, $b);
			$text .= " ".$s;
		}
		return true;
	}
	return false;
}
function HDA_searchRss($rssID, $url, &$hits, $search, $refresh = false) {
	$slist = preg_split("/[\s,;]+/", strtoupper($search));
	if (($r = HDA_getRss($url, $refresh, $error)) !== false) {
		foreach ($r['ITEMS'] as $item) {
			$s = $item['TITLE']." ".$item['DESCRIPTION'];
			$b = _word_tokens($s);
			$ww = array();
			_count_words($ww, $b);
			$hmax = 0; foreach ($ww as $word=>$h) if (in_array($word, $slist)) $hmax += $h;
			if ($hmax>0) {
				if (!array_key_exists($url, $hits)) $hits[$url] = array();
				$hits[$rssID][] = array('ITEM'=>$item, 'HITS'=>$hmax);
			}
		}
	}
	return $hits;
}
function HDA_searchRss_Rx($rssID, $subjectID, $url, &$hits, $search, $refresh = false, $log = null) {
	$slist = preg_split("/[,;]+/", strtoupper($search));
	$patterns = array();
	foreach ($slist as $pattern) {
		if (strlen($pattern=trim($pattern))>0) $patterns[] = "\\b{$pattern}\\b";
		if (!is_null($log)) fwrite($log, "{$url} preg match {$pattern}".PHP_EOL);
	}
	if (($r = HDA_getRss($url, $refresh, $error)) !== false) {
		foreach ($r['ITEMS'] as $item) {
			$s = $item['TITLE']." ".$item['DESCRIPTION'];
			$maxh = 0;
			foreach ($patterns as $pattern) {
				$h = preg_match_all("/{$pattern}/i", $s, $matches);
				if (($h !== false) && ($h>1)) {
					$maxh += $h;
					if (!is_null($log)) fwrite($log, "{$url} matched {$h} {$s} with {$pattern} ".print_r($matches,true).PHP_EOL);
				}
			}
			if ($maxh >1) {
				if (!array_key_exists($rssID, $hits)) $hits[$rssID] = array();
				$hits[$rssID][] = array('ITEM'=>$item, 'HITS'=>$maxh);
			}
		}
	}
	return $hits;
}

function _word_tokens($s) {
	$a = array();
	$s = strip_tags($s);
	$s = preg_replace("/[^A-Z]/i","_",$s);
	$token = strtok($s, "_");

	while ($token !== false) {
		if (strlen($token)>1) {
			if (array_key_exists($token, $a)) $a[$token]++; else $a[$token]=1;
		}
		$token = strtok("_");
	} 
	return $a;
}
function _count_words(&$a, &$b) {
	foreach ($b as $word=>$hits) {
		if (array_key_exists($word, $a)) $a[$word] += $hits; else $a[$word] = $hits;
	}
	return $a;
}



class rssFetch {
  function __construct($url, $refresh = false, $numItems = 20, &$error, &$dated) {
	  $this->url    = $url; // "http://feeds.bbci.co.uk/news/rss.xml?edition=int";
	  $this->numItems = $numItems;
	  $this->timeFormat = "j F Y, g:ia";
	  $this->cached  = "rss/" . md5($this->url).".xml";

	  // download the feed iff a cached version is missing or too old
	  if((!file_exists($this->cached)) || $refresh) {
		if(($feed_contents = rssFetch::http_get_contents($url, $error)) !== false) {
		  // write feed contents to cache file
		  $fp = fopen($this->cached, 'w');
		  fwrite($fp, $feed_contents);
		  fclose($fp);
			clearstatcache();
		}
	  }
	  $dated = filemtime($this->cached);
  }
  static function cacheFileForUrl($url) {
	  return  md5($url).".xml";
  }
  static function removeCache($url) {
	  if (file_exists($f = "rss/" . md5($url).".xml")) @unlink($f);
  }
  var $url;
  var $numItems;
  var $timeFormat;
  var $cached;


  
  var $title="";
  var $link = "";
  var $description = "";
  var $copyright = "";
  var $image_title = "";
  var $image_link = "";
  var $image_url = "";
  var $items = array();
  
  function title_s() {
	  $t = "";
	  $t .= "<a title=\"{$this->description}\" href=\"{$this->link}\" target=\"_blank\">";
	  $t .= $this->title;
	  $t .= "</a>";
	  return $t;
  }
  function leading_image() {
		$t = "<p><a title=\"{$this->image_title}\" href=\"{$this->image_link}\"><img src=\"{$this->image_url}\" alt=\"\"></a></p>";
		return $t;
  }
  function copyright_s() {
	  	$t = "<p><small>&copy;{$this->copyright}</small></p>";

  }
  function items_s() {
	  $t = "";
	  foreach ($this->items as $itemdata) {
		$t .= "<p>";
		if (array_key_exists('THUMBNAIL',$itemdata) && is_array($itemdata['THUMBNAIL']) && array_key_exists('URL',$itemdata['THUMBNAIL'])) {
			$t .= "<img src=\"{$itemdata['THUMBNAIL']['URL']}\" alt=\"\" height={$itemdata['THUMBNAIL']['HEIGHT']} width={$itemdata['THUMBNAIL']['WIDTH']}>";
		}
		$t .= "<b><a href=\"{$itemdata['LINK']}\" target=\"_blank\">";
		$t .= $itemdata['TITLE'];
		$t .= "</a></b><br>\n";
		$t .= $itemdata['DESCRIPTION']."<br>\n";
		$t .= "<i>{$itemdata['DATE']}</i></p>\n\n";
	  }
	return $t;
  }
  function parse() {
	  if (($rss_parser = new rssParser($this->cached))===false) {  return false; }

	  // read feed data from cache file
	  $feeddata = $rss_parser->getRawOutput();
	  if (array_key_exists('CHANNEL',$feeddata) && count($feeddata['CHANNEL'])>0) {
		  $kk = array_keys($feeddata['CHANNEL']);
		  if (is_array($kk) && count($kk)>0) $key = $kk[0];
		  $c0 = $feeddata['CHANNEL'][$key];
		  if (!is_null($c0) && is_array($c0)) {
			  $this->title = (array_key_exists('TITLE',$c0))?htmlspecialchars(stripslashes($c0['TITLE'])):"Untitled";
			  $this->link = (array_key_exists('LINK',$c0))?$c0['LINK']:"";
			  $this->description = (array_key_exists('DESCRIPTION',$c0))?htmlspecialchars(stripslashes(strip_tags($c0['DESCRIPTION']))):"";
			  $this->copyright = (array_key_exists('COPYRIGHT',$c0))?htmlspecialchars($c0['COPYRIGHT']):"";

			  if(array_key_exists('IMAGE',$c0)) {
				$img0 = $c0['IMAGE'];
				$this->image_title = (array_key_exists('TITLE',$img0))?htmlspecialchars($img0['TITLE']):"";
				$this->image_link = (array_key_exists('LINK',$img0))?$img0['LINK']:"";
				$this->image_url = (array_key_exists('URL',$img0))?$img0['URL']:"";
			  }


			  $count = 0;
			  if (array_key_exists('ITEM',$c0)) foreach($c0['ITEM'] as $itemdata) {
				  $this->items[] = array('LINK'=>$itemdata['LINK'],
				  'TITLE'=>htmlspecialchars(stripslashes($itemdata['TITLE'])),
				  'DESCRIPTION'=>htmlspecialchars(stripslashes(strip_tags($itemdata['DESCRIPTION']))),
				  'DATE'=>date($this->timeFormat, strtotime($itemdata['PUBDATE'])),
				  'THUMBNAIL'=>$itemdata['THUMBNAIL']
				  );
				if(++$count >= $this->numItems) break;
			  }
			return true;
		  }
	  }
	return false;
  }
  
  static function http_get_contents($url, &$error)
	{
	$ch = curl_init();
	  curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	  curl_setopt($ch, CURLOPT_URL, $url);
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	  if(FALSE === ($retval = curl_exec($ch))) {
		file_put_contents("rss/rss_error.txt",  $error = "Rss error {$url} ".curl_error($ch));
	  } 
	return $retval;
	  
	}
}



  class rssParser
  {
	var $state_parsing = null;
	var $last_data = "";
    // keeps track of current and preceding elements
    var $tags = array('CHANNEL'=>array());

    // array containing all feed data
    var $output = array();

    // return value for display functions
    var $retval = "";

    var $errorlevel = 0;

    // constructor for new object
    function __construct($file)
    {
      $errorlevel = error_reporting();
      error_reporting($errorlevel & ~E_NOTICE);

      // instantiate xml-parser and assign event handlers
      $xml_parser = xml_parser_create("");
      xml_set_object($xml_parser, $this);
      xml_set_element_handler($xml_parser, "rss_startElement", "rss_endElement");
      xml_set_character_data_handler($xml_parser, "rss_parseData");

      // open file for reading and send data to xml-parser
      $data = preg_match("/^http/i", $file) ? rssFetch::http_get_contents($file, $error) : ((file_exists($file))?file_get_contents($file):false);
	  if ($data !== false) xml_parse($xml_parser, $data);

      // dismiss xml parser
      xml_parser_free($xml_parser);

      error_reporting($errorlevel);
	  return ($data===false)?null:$this;
    }

    function rss_startElement($parser, $tagname, $attrs=array())
    {
		$tagname = str_ireplace("MEDIA:","",strtoupper($tagname));


		switch ($tagname) {
			case 'CHANNEL':
				$this->tags['CHANNEL'][] = array('TITLE'=>"",'LINK'=>"",'DESCRIPTION'=>"",'IMAGE'=>array(),'ITEM'=>array());
				$this->state_parsing='CHANNEL';
				break;
			case 'ITEM':
				$this->tags['CHANNEL'][count($this->tags['CHANNEL'])-1]['ITEM'][] = 
					array('TITLE'=>"",'LINK'=>"",'DESCRIPTION'=>"",'IMAGE'=>array(),'PUBDATE'=>"",'URL'=>"",'THUMBNAIL'=>"");
				$this->state_parsing='ITEM';
				break;
			case 'IMAGE':
				$this->state_parsing=$this->state_parsing."_IMAGE";
				break;
			case 'DESCRIPTION':
			case 'URL':
			case 'LINK':
			case 'TITLE':
			case 'COPYRIGHT':
			case 'PUBDATE':
				break;
			case 'THUMBNAIL':
				$a = &$this->tags['CHANNEL'][count($this->tags['CHANNEL'])-1];
				switch ($this->state_parsing) {
					case 'CHANNEL':  break;
					case 'ITEM': 
						$a = &$a['ITEM'][count($a['ITEM'])-1]; break;
					case 'CHANNEL_IMAGE':
						$a = &$a['IMAGE']; break;
					case 'ITEM_IMAGE':
						$a = &$a['ITEM'][count($a['ITEM'])-1]['IMAGE']; break;
					default: $a = null;
				}
				if (!is_null($a)) {
					$a[$tagname] = $attrs;
				}
				break;
			default: return;
		}
		$this->last_data = "";
    }

    function rss_endElement($parser, $tagname)
    {
		$tagname = str_ireplace("MEDIA:","",strtoupper($tagname));
		$a = &$this->tags['CHANNEL'][count($this->tags['CHANNEL'])-1];
		switch ($this->state_parsing) {
			case 'CHANNEL':  break;
			case 'ITEM': 
				$a = &$a['ITEM'][count($a['ITEM'])-1]; break;
			case 'CHANNEL_IMAGE':
				$a = &$a['IMAGE']; break;
			case 'ITEM_IMAGE':
				$a = &$a['ITEM'][count($a['ITEM'])-1]['IMAGE']; break;
			default: return;
		}
		switch ($tagname) {
			case 'DESCRIPTION':
			case 'URL':
			case 'LINK':
			case 'TITLE':
			case 'COPYRIGHT':
			case 'PUBDATE':
				$a[$tagname] = stripslashes($this->last_data);
				$this->last_data = "";
				break;
			case 'IMAGE':
				$this->state_parsing = str_replace('_IMAGE','',$this->state_parsing);
				break;
			case 'ITEM':
				$this->state_parsing = 'CHANNEL';
				break;
			case 'CHANNEL':
				$this->state_parsing = null;
				break;
			default: return;
		}
   }

    function rss_parseData($parser, $data)
    {
      // return if data contains no text
      if(!trim($data)) return;

      $this->last_data .= $data ;
    }





    // return raw data as array
    function getRawOutput($output_encoding='UTF-8')
    {
      return $this->tags;
    }
  }
?>