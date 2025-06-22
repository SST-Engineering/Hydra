<?php


class queryXML {
   public $aa;
   public $xml;
   public $root;
   function make($s, $root='ROOT') {
      $this->xml = $s;
      $this->aa = xml2ary($s);
      $this->root = $root;
      }
   function valueOf($n, $a=null) {
      return _query($this->root, $this->aa, $n, $a, $value=NULL);
      }

   function elementExists($n) {
      return _elementExists($n, $this->aa);
      }

   function elementsOf($n) {
      return _elementsOf($n, $this->aa);
      }

   function dump() {
      return print_r($this->aa, true);
      }


   }

class _xmlListContent {
   public $tagged = "";
   public $list = array();
   public function listOf($tag, $a=NULL) {
      $aa = array();
      $this->tagged = $tag;
      if (!is_null($a) && is_array($a)) {
         foreach($a as $v) $aa[] = array('_v'=>$v);
         $this->list[$this->tagged] = $aa;
         }
      }
   public function add($v, $attributes=NULL) {
      $_a = array('_v'=>$v);
      if (!is_null($attributes)) $_a['_a']=$attributes;
      $this->list[$this->tagged][] = $_a;
      }
   public function unpack($a) {
      $ks = array_keys($a);
      if (is_array($ks) && count($ks)>0) foreach($ks as $atag) {
      if (isset($a[$atag][0])) {
         foreach($a[$atag] as $aa) {
            if (isset($aa['_a']) && is_array($aa['_a']) && count($aa['_a'])>0) {
               if (array_key_exists('_v', $aa))
                  $this->list[] = array($aa['_v'], $aa['_a']);
               }
            elseif (array_key_exists('_v', $aa)) $this->list[] = $aa['_v'];
            }
         }
      elseif (array_key_exists('_v',$a[$atag])) {
         if (array_key_exists('_a',$a[$atag])) {
            $this->list[] = array($a[$atag]['_v'], $a[$atag]['_a']);
            }
         else $this->list[] = $a[$atag]['_v'];
         }
      }
      }
   
   }



function _elementExists($index, &$root) {
   $inner = $root;
   for ($i=0; $i<count($index); $i++) {
      if (array_key_exists($index[$i], $inner) && array_key_exists('_c',$inner[$index[$i]])) $inner = &$root[$index[$i]]['_c'];
      else return false;
      }
   return true;
   }
function _elementsOf($index, &$root) {
   $inner = $root;
   for ($i=0; $i<count($index); $i++) {
      if (array_key_exists($index[$i], $inner) && array_key_exists('_c',$inner[$index[$i]])) $inner = &$root[$index[$i]]['_c'];
      else return null;
      }
   return array_keys($inner);
   }
function _query($root_name, &$root, $index, $attribute=NULL, $value=NULL) {
   if (is_null($root) || (!is_null($root_name) && !array_key_exists($root_name,$root))) return null;
   if (!is_array($index)) {
      if (is_null($root_name)) $inner = $root; else $inner = &$root[$root_name]['_c'];
      }
   else {
      if (is_null($root_name)) $inner = $root; else $inner = &$root[$root_name]['_c'];
 
      for ($i=0;$i<(count($index)-1);$i++) {
         if (!array_key_exists($index[$i],$inner)) {
            if (is_null($value)) return null;
            $inner[$index[$i]]=array('_c'=>array());
            }
         if (!array_key_exists('_c',$inner[$index[$i]])) return null;
         $inner = &$inner[$index[$i]]['_c'];
         }
      $index = $index[count($index)-1];
      }
   if (is_null($attribute)) {
      if (!is_null($value)) {
         if (is_array($value)) {
            $a = array();
            foreach($value as $v) $a[] = array('_v'=>$v);
            $inner[$index]=$a;
            }
         elseif ($value instanceof _xmlListContent) {
            $inner[$index]['_c']=$value->list;
            }
         else $inner[$index]['_v']=$value;
         }
      elseif (array_key_exists($index, $inner)) { 
         if (array_key_exists('_c',$inner[$index])) {
            $xml_list = new _xmlListContent();
            $xml_list->unpack($inner[$index]['_c']);
            return $xml_list->list;
            }
         elseif (isset($inner[$index][0])) {
            $value = array();
            foreach($inner[$index] as $a) {
               if (array_key_exists('_v',$a)) $value[] = $a['_v'];
               elseif (array_key_exists('_c',$a)) {
                  $xml_list = new _xmlListContent();
                  $xml_list->unpack($a['_c']);
                  $value[] = $xml_list->list;
                  }
               }
            }
         elseif (array_key_exists('_v',$inner[$index]) && !is_null($inner[$index]['_v'])) $value = $inner[$index]['_v'];
         }
      return $value;
      }
   elseif (array_key_exists($index,$inner)) {
      if (!array_key_exists('_a',$inner[$index])) $inner[$index]['_a']=array();
      if (!is_null($value)) $inner[$index]['_a'][$attribute]=$value;
      return (array_key_exists($attribute,$inner[$index]['_a']))?$inner[$index]['_a'][$attribute]:null;
      }
   }


function xml2ary(&$string) {
    $parser = xml_parser_create();
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parse_into_struct($parser, $string, $vals, $index);
    xml_parser_free($parser);

    $mnary=array();
    $ary=&$mnary;
    foreach ($vals as $r) {
        $t=$r['tag'];
        if ($r['type']=='open') {
            if (isset($ary[$t])) {
                if (isset($ary[$t][0])) $ary[$t][]=array(); else $ary[$t]=array($ary[$t], array());
                $cv=&$ary[$t][count($ary[$t])-1];
            } else $cv=&$ary[$t];
            if (isset($r['attributes'])) {foreach ($r['attributes'] as $k=>$v) $cv['_a'][$k]=$v;}
            $cv['_c']=array();
            $cv['_c']['_p']=&$ary;
            $ary=&$cv['_c'];

        } elseif ($r['type']=='complete') {
            if (isset($ary[$t])) { // same as open
                if (isset($ary[$t][0])) $ary[$t][]=array(); else $ary[$t]=array($ary[$t], array());
                $cv=&$ary[$t][count($ary[$t])-1];
            } else $cv=&$ary[$t];
            if (isset($r['attributes'])) {foreach ($r['attributes'] as $k=>$v) $cv['_a'][$k]=$v;}
            $cv['_v']=(isset($r['value']) ? $r['value'] : '');

        } elseif ($r['type']=='close') {
            $ary=&$ary['_p'];
        }
    }    
    
    _del_p($mnary);
    return $mnary;
}

// _Internal: Remove recursion in result array
function _del_p(&$ary) {
    foreach ($ary as $k=>$v) {
        if ($k==='_p') unset($ary[$k]);
        elseif (is_array($ary[$k])) _del_p($ary[$k]);
    }
}

// Array to XML
function ary2xml($cary, $d=0, $forcetag='') {
    $res=array();
    if (is_array($cary)) foreach ($cary as $tag=>$r) {
        if (isset($r[0])) {
            $res[]=ary2xml($r, $d, $tag);
        } else {
            if ($forcetag) $tag=$forcetag;
            $sp=str_repeat("\t", $d);
            $res[]="$sp<$tag";
            if (isset($r['_a'])) {foreach ($r['_a'] as $at=>$av) $res[]=" $at=\"$av\"";}
            $res[]=">".((isset($r['_c'])) ? "\n" : '');
            if (isset($r['_c'])&&is_array($r['_c'])&&count($r['_c'])>0) $res[]=ary2xml($r['_c'], $d+1);
            elseif (isset($r['_v'])) $res[]=$r['_v'];
            $res[]=(isset($r['_c']) ? $sp : '')."</$tag>\n";
        }
        
    }
    return implode('', $res);
}

// Insert element into array
function ins2ary(&$ary, $element, $pos) {
    $ar1=array_slice($ary, 0, $pos); $ar1[]=$element;
    $ary=array_merge($ar1, array_slice($ary, $pos));
}

class xmlStreamer {
	
	private $xmlReader;
	public $recordTag = null;
	public $only = null;
	public $only_value = null;
	
	function open($path) {
		libxml_disable_entity_loader( false );
		$retries = 0;
		while ($retries<3) {
			try{
				$this->xmlReader = new XMLReader();
				if ($this->xmlReader->open($path)===false) {
					$retries++;
					}
				else return true;
				}
			catch (Exception $e) {
				$retries++;
				}
			}
		return "Fails open {$path}";
	}
	function close() {
		$this->xmlReader->close();
		return true;
		}
		
	function xmlRecord() {
		while (($record = $this->nextRecord()) !== false) {
			if (is_null($this->only) || ($record[$this->only]==$this->only_value)) {
				return $record;
				}
		}
		return false;
	}
	function nextRecord() {
		$record = false;
		$field = null;
		while ($this->xmlReader->read()) {
			if ($this->xmlReader->nodeType == XMLREADER::END_ELEMENT) {
				if ($this->xmlReader->localName == $this->recordTag) return $record;
			}
			else if ($this->xmlReader->nodeType == XMLREADER::ELEMENT) {
				if ($this->xmlReader->localName == $this->recordTag) $record = array();
				if (is_array($record)) {
					$field = $this->xmlReader->localName;
					while ($this->xmlReader->moveToNextAttribute()) {
						$att = $this->xmlReader->name; $att_v = $this->xmlReader->value;
						$record[$field.'__'.$att] = $att_v;
					}
					$this->xmlReader->read();
					$value = $this->xmlReader->value;
					$record[$field] = $value;
					}
				}
			}
		
		return false;
	}
	

}

/** 
* Convert an xml file to an associative array (including the tag attributes): 
* 
* @param Str $xml file/string. 
*/ 
class xmlToArrayParser { 
/** 
* The array created by the parser which can be assigned to a variable with: $varArr = $domObj->array. 
* 
* @var Array 
*/ 
public $array; 
private $parser; 
private $pointer; 

/** 
* $domObj = new xmlToArrayParser($xml); 
* 
* @param Str $xml file/string 
*/ 
public function __construct($xml) { 
$this->pointer =& $this->array; 
$this->parser = xml_parser_create("UTF-8"); 
xml_set_object($this->parser, $this); 
xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false); 
xml_set_element_handler($this->parser, "tag_open", "tag_close"); 
xml_set_character_data_handler($this->parser, "cdata"); 
xml_parse($this->parser, ltrim($xml)); 
} 

private function tag_open($parser, $tag, $attributes) { 
$this->convert_to_array($tag, '_'); 
$idx=$this->convert_to_array($tag, 'cdata'); 
if(isset($idx)) { 
$this->pointer[$tag][$idx] = Array('@idx' => $idx,'@parent' => &$this->pointer); 
$this->pointer =& $this->pointer[$tag][$idx]; 
}else { 
$this->pointer[$tag] = Array('@parent' => &$this->pointer); 
$this->pointer =& $this->pointer[$tag]; 
} 
if (!empty($attributes)) { $this->pointer['_'] = $attributes; } 
} 

/** 
* Adds the current elements content to the current pointer[cdata] array. 
*/ 
private function cdata($parser, $cdata) { 
if(isset($this->pointer['cdata'])) { $this->pointer['cdata'] .= $cdata;} 
else { $this->pointer['cdata'] = $cdata;} 
} 

private function tag_close($parser, $tag) { 
$current = & $this->pointer; 
if(isset($this->pointer['@idx'])) {unset($current['@idx']);} 
$this->pointer = & $this->pointer['@parent']; 
unset($current['@parent']); 
if(isset($current['cdata']) && count($current) == 1) { $current = $current['cdata'];} 
else if(empty($current['cdata'])) { unset($current['cdata']); } 
} 

/** 
* Converts a single element item into array(element[0]) if a second element of the same name is encountered. 
*/ 
private function convert_to_array($tag, $item) { 
if(isset($this->pointer[$tag][$item])) { 
$content = $this->pointer[$tag]; 
$this->pointer[$tag] = array((0) => $content); 
$idx = 1; 
}else if (isset($this->pointer[$tag])) { 
$idx = count($this->pointer[$tag]); 
if(!isset($this->pointer[$tag][0])) { 
foreach ($this->pointer[$tag] as $key => $value) { 
unset($this->pointer[$tag][$key]); 
$this->pointer[$tag][0][$key] = $value; 
}}}else $idx = null; 
return $idx; 
} 
} 


class mt940 {

	private $_fileName;
	private $_lastTag = '';
	
	public $refNumber = null;
	public $relatedRef = null;
	public $accountNumber;
	public $accountName;
	public $ownerName;
	public $extractNumber;
	public $openBalance;
	public $interimOpenBalance;
	public $closeBalance;
	public $interimCloseBalance;
	public $availableBalance;
	public $forwardAvailableBalance;
	public $timeStamp;
	public $valueDate;
	public $currCode;
	public $amount;
	public $orderingCustomer;
	public $orderingInstitution;
	public $intermediary;
	public $senderToRcvInfo;
	public $info = '';
     public $leading_lines = "";
	 
	 public $debug_log = "";
	
	public $operations = array();
	public $records = array();
	public $format = "mt940";
	public $headers = false;
	
	public function __construct($fileName, $fmt, $hdr) {
		$this->_fileName = $fileName;
		$this->format = $fmt;
		$this->headers = $hdr;
		$this->parse();
	}
	
	public function parse() {
		$tab = $this->prepareFile();
		foreach($tab as $line)
			$this->parseLine($line);
	}

	public function getXML() {
		$xml = "<{$this->format}>\n";
		foreach ( get_object_vars($this) as $key => $value) {
			if ($key{0} != '_') {
				$xml .= $this->createXML($key, $value, 0);
			}
		}
		$xml .= "</{$this->format}>";
		return $xml;
	}	
	
	public function getArray() {
	   $a = array();
	   $a['refNumber'] = $this->refNumber;
	   $a['relatedRef'] = $this->relatedRef;
	   $a['accountNumber'] = $this->accountNumber;
	   $a['accountName'] = $this->accountName;
	   $a['ownerName'] = $this->ownerName;
	   $a['extractNumber'] = $this->extractNumber;
	   $a['valueDate'] = $this->valueDate;
	   $a['currCode'] = $this->currCode;
	   $a['amount'] = $this->amount;
	   $a['orderingCustomer'] = $this->orderingCustomer;
	   $a['orderingInstitution'] = $this->orderingInstitution;
	   $a['intermediary'] = $this->intermediary;
	   $a['senderToRcvInfo'] = $this->senderToRcvInfo;
	   $aa = $this->openBalance;
	   if (is_array($aa)) foreach ($aa as $k=>$v) $a["openBalance_{$k}"]=$v;
	   $aa = $this->closeBalance;
	   if (is_array($aa)) foreach ($aa as $k=>$v) $a["closeBalance_{$k}"]=$v;
	   $aa = $this->availableBalance;
	   if (is_array($aa)) foreach ($aa as $k=>$v) $a["availableBalance_{$k}"]=$v;
	   $aa = $this->interimOpenBalance;
	   if (is_array($aa)) foreach ($aa as $k=>$v) $a["interimOpenBalance_{$k}"]=$v;
	   $aa = $this->interimCloseBalance;
	   if (is_array($aa)) foreach ($aa as $k=>$v) $a["interimCloseBalance_{$k}"]=$v;
	   $a['info'] = $this->info;
	   $a['leading_lines']= $this->leading_lines;
	   $a['transactions'] = $this->operations;
	   $a['time_stamp'] = $this->timeStamp;
	   return $a;
	   }
	   
	public function getRecords() {
	   $this->putRecord();
	   return $this->records;
	   }
	private function putRecord() {
	   if (!is_null($this->refNumber)) {
	      $this->records[] = $this->getArray();
		  $this->refNumber = null;
		  $this->operations = array();
	      }
	   }
	   
	public function dump() {
		echo '<pre>';
		var_dump($this);
		echo '</pre>';
	}
	
	private function createXML($key, $value, $level) {
		$indent = '';
		for($i=0;$i<=$level;$i++)
				$indent .= "\t";
		if (is_array($value)) {
			$xml = "$indent<$key>\n";
			foreach($value as $subKey => $subVal) {
				if (is_numeric($subKey))
					$subKey = substr($key, 0, -1);
				$xml .= $this->createXML($subKey, $subVal, $level+1);
			}
			$xml .= "$indent</$key>\n";
		} else {
			$xml = "$indent<$key>".trim($value)."</$key>\n";
		}
		return $xml;
	}
	
	private function parseLine($line) {
		$tag = substr($line, 1, strpos($line, ':', 1)-1);
		$value = trim(substr($line, strpos($line, ':', 1)+1));
		switch(strtoupper($tag)) {
			case '13D':
				$this->timeStamp = $value;
				break;
			case '20':
			    $this->putRecord();
				$this->refNumber = $value;
				break;
			case '21':
				$this->relatedRef = $value;
				break;
			case '25':
			case '25A':
				$this->accountNumber = $value;
				break;
			case '28C':
				$this->extractNumber = $value;
				break;
			case '32A':
				$this->valueDate = substr($value,0,6);
				$this->currCode = substr($value,6, 3);
				$this->amount = substr($value, 9);
				break;
			case 'NS':
				$code = substr($value, 0, 2);
				if ($code == '22')
					$this->ownerName = substr($value, 2);
				else if ($code == '23')
					$this->accountName = substr($value, 2);
				break;
			case '50K':
			case '50A':
				$this->orderingCustomer = $value;
				break;
			case '52A':
				$this->orderingInstitution = $value;
				break;
			case '56A':
				$this->intermediary = $value;
				break;
			case '60F':
				$this->openBalance = $this->parseBalance($value);
				break;
			case '60M':
				$this->interimOpenBalance = $this->parseBalance($value);
				break;
			case '62F':
				$this->closeBalance = $this->parseBalance($value);
				break;
			case '62M':
				$this->interimCloseBalance = $this->parseBalance($value);
				break;
			case '64':
				$this->availableBalance = $this->parseBalance($value);
				break;	
            case '65':
                $this->forwardAvailableBalance = $this->parseBalance($value);
                break;				
			case '61':
				$this->parseOperation($value);
				break;		
			case '72':
				$this->senderToRcvInfo = $value;
				break;
			case '86':
				if ($this->_lastTag == '61')
					$this->parseTransaction($value);
				else
					$this->info .= $value;
				break;
			default:
                     $this->leading_lines .= $line;
				break;
		}
		$this->_lastTag = $tag;
	}

	private function parseOperation($value) {
		$rx = "(?P<yr>[\d]{2,2})(?P<mth>[\d]{2,2})(?P<day>[\d]{2,2})(?P<emth>[\d]{0,2})(?P<eday>[\d]{0,2})(?P<reversal>[R]{0,1})(?P<ind>[C|D]{1,1})(?P<amt>[\d]{1,},[\d]{0,2})(?P<info>[\s\S]{0,})";
		if (preg_match("/{$rx}/i",$value,$matches)) {
		$this->operations[] = array (
			'date'			=> $matches['yr'] . '-' .$matches['mth'] . '-' .$matches['day'],
			'accountDate'	=> $matches['emth'] . '-' .$matches['eday'],
			'indicator'		=> $matches['reversal'].$matches['ind'],
			'amount'		=> $matches['amt'],
			'info'			=> $matches['info'].PHP_EOL
			);
		}
		
	}
	
	private function parseBalance($value) {
		$rx = "(?P<ind>[C|D]{1,1})(?P<yr>[\d]{2,2})(?P<mth>[\d]{2,2})(?P<day>[\d]{2,2})(?P<cur>[\w]{3,3})(?P<amt>[\d,]{1,})";
		if (preg_match("/{$rx}/i",$value,$matches)) {
		return array (
			'indicator' 	=> $matches['ind'],
			'date' 			=> $matches['yr'] . '-' .$matches['mth'] . '-' .$matches['day'],
			'currency' 		=> $matches['cur'],
			'amount' 		=> $matches['amt']
		);
		}
	}
	
	private function parseTransaction($value) {
		$transaction = array (
			'code'			=> substr($value, 0, 3),
			'typeCode'		=> '',
			'number'		=> '',
			'title'			=> '',
			'contName'		=> '',
			'contAccount'	=> '',
			'value'			=> $value
		);
		$delimiter = substr($value, 3, 1);
		$tab = (strlen($delimiter)>0 && strlen($value)>4)?explode($delimiter, substr($value, 4)):array();
		foreach($tab as $line) {
			$subTag = substr($line, 0, 2);
			$subVal = substr($line, 2);
			switch($subTag) {
				case '00':
					$transaction['typeCode'] = $subVal;
					break;
				case '10':
					$transaction['number'] = $subVal;
					break;
				case '20':
				case '21':
				case '22':
				case '23':
				case '24':
				case '25':						
				case '26':						
					$transaction['title'] .= $subVal;
					break;
				case '27':
				case '28':						
				case '29':						
					$transaction['contName'] .= $subVal;
					break;
				case '38':
					$transaction['contAccount'] = $subVal;
					break;		
				default:
					break;
			}
		}	
		for ($i = 0; $i<count($this->operations); $i++) {
			if (!array_key_exists('details', $this->operations[$i])) $this->operations[$i]['details'] = $transaction;
		}
	}
	
	private function prepareFile() {
		if ($this->headers !== false) {
			$s = $this->parseHeaders($this->_fileName);
			if ($s!==false) {
				$fname = pathinfo($this->_fileName,PATHINFO_BASENAME);
				file_put_contents($this->_fileName = "tmp/{$fname}",$s);
			}
		}
		$tab = file($this->_fileName);
		$tags = array();
		$tmp = '';
		foreach($tab as $line) {			
			if ($line{0} == ':' && $tmp != '') {
				$tags[] = $tmp;
				$tmp = '';
			}	
			$tmp .= $line;
		}
		$tags[] = $tmp;
		return $tags;
	}

	private function parseHeaders($fpath) {
		$s = file_get_contents($fpath);
		$h = $this->headers;
		if (preg_match_all("/\{{$h}[\s\S]{1,}?\{4:(?P<c>[\s\S]{1,}?)-\}/",$s, $matches)!==false) {
			$ss = "";
			if (array_key_exists('c',$matches)) {
				foreach($matches['c'] as $sss) $ss .= "{$sss}";
		//		$ss = print_r($matches['c'],true);
			}
			return $ss;
		}
		return false;
	}
}

function mt940ToXML($filename, $format="mt940", $headers=false) {
   $mt940 = new mt940($filename, $format, $headers);
   $xml = $mt940->getXML();
   return $xml;
   }
function mt940ToArray($filename, $format="mt940", $headers=false) {
   $mt940 = new mt940($filename, $format, $headers);
   return $mt940->getRecords();
   }
function _flat_array($a, $field, $skip) {
   $aa = array();
   foreach($a as $k=>$p) {
      if (in_array($k, $skip)) continue;
      if (!is_array($p)) $aa["{$field}{$k}"] = $p;
      else {
         if (is_numeric($k)) {
            $ops = _flat_array($p, "", $skip);
            $aa['transactions'][] = $ops;
            }
         else {
		    $bb =  _flat_array($p, "{$field}{$k}_", $skip);
		    $aa = array_merge($aa,$bb);
			}
         }
      }
   return $aa;
   }
   
class iCal { 

	
	function iCalDecoder($file) {
		$ical = file_get_contents($file);
		preg_match_all('/(BEGIN:VEVENT.*?END:VEVENT)/si', $ical, $result, PREG_PATTERN_ORDER);
		for ($i = 0; $i < count($result[0]); $i++) {
			$tmpbyline = explode("\r\n", $result[0][$i]);
			
			foreach ($tmpbyline as $item) {
				$tmpholderarray = explode(":",$item);
				if (count($tmpholderarray) >1) { 
					$majorarray[$tmpholderarray[0]] = $tmpholderarray[1];
				}
				
			}
			/*
				lets just finish what we started..
			*/
			if (preg_match('/DESCRIPTION:(.*)END:VEVENT/si', $result[0][$i], $regs)) {
				$majorarray['DESCRIPTION'] = str_replace("  ", " ", str_replace("\r\n", "", $regs[1]));
			} 
			$icalarray[] = $majorarray;
			unset($majorarray);
			 
			 
		}
		return $icalarray;
	}
	
function HDA_ICS_ITEM($username, $user_mail, $item, $startdate, $text, $category) {
   $t = "";
   $ics_date = strtotime($startdate);
   $v_type = "VEVENT";
   $ics_end_date = $ics_date+(60*60);
   $t .= "BEGIN:{$v_type}\r\n";
   $t .= "DTSTAMP:".$this->PRO_ICS_DT(time())."\r\n";
   $t .= "UID:{$item}\r\n";
   $t .= "ORGANIZER;CN={$username}:MAILTO:{$user_mail}\r\n";
   $t .= "DTSTART:".$this->PRO_ICS_DT($ics_date)."\r\n";
   $t .= "DTEND:".$this->PRO_ICS_DT($ics_end_date)."\r\n";
   $t .= "SUMMARY:{$text}\r\n";
   $t .= "CATEGORIES:{$category}\r\n";
   $t .= "END:{$v_type}\r\n";
   return $t;
}

function HDA_ICS_HEAD() {
   $t = "";
   $t .= "BEGIN:VCALENDAR\r\n";
   $t .= "VERSION:2.0\r\n";
   $t .= "PRODID:-//ExcelInBusiness Ltd//HDAW//EN\r\n";
   $t .= "CALSCALE:GREGORIAN\r\n";
   $t .= "METHOD:PUBLISH\r\n";
   $t .= "X-WR-CALNAME;VALUE=TEXT:HDAW\r\n";
   $t .= "X-WR-CALDESC;VALUE=TEXT:HDAW Diary\r\n";
   $t .= "X-WR-TIMEZONE;VALUE=TEXT:Europe/London\r\n";
   return $t;
}

function HDA_ICS_TAIL() {
   $t = "";
   $t .= "END:VCALENDAR\r\n";
   return $t;
}

function PRO_ICS_DT($time) {
   $t = date('Y',$time).date('m',$time).date('d',$time)."T".date('H',$time).date('i',$time)."00Z";
   return $t;
}


}

?>