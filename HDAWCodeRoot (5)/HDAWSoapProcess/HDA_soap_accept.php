<?php
include_once __DIR__."/../HDAWCodeRoot/HDA_Initials.php";
include_once __DIR__."/../HDAWCodeRoot/HDA_Finals.php";
_hydra_("HDA_soap_accept", 
	$loads=array('XML','DB','FTP','Functions','Process','ManageProfiles','CodeCompiler','Validate','Email','Logging','Soap'));

include_once "soapserver.php";

class SOAP_BaseServiceClass {

 
    function __construct() {

     }
	 
    public function setRawXML($xml) {
	   $this->rawxml = $xml;
	   }
	private $rawxml = null;
	
	protected function _runProfile($for_node, $a) {
	  $item = hda_db::hdadb()->HDA_DB_lookUpProfile("SOAP_{$for_node}");
	  if (is_null($item)) return "No profile code match for {$for_node}";
      $process = hda_db::hdadb()->HDA_DB_Read($item);
      if (!is_null($process)) {
         $the_log = "Soap request {$for_node} runs {$item}";
		 _postSoapRequest($item, $a);
		 _postSoapXML($item, $this->rawxml);
         HDA_CustomCode($the_log, $process, $runFile='alcode.alc', $and_run=3);
		 return _getSoapResponse($item);
         }
	  return "No {$for_node} Soap handler found";
	  }
	  
   protected function _unpackArg($a) {
      if (is_object($a)) {
	     return (array)$a;
	     }
	  else return $a;
	  }
   public function anonymous() {
      $aa = func_get_args();
	  $args = array();
	  foreach($aa as $a) {
	     $arg = explode('|',$a);
		 $args[$arg[0]] = $arg[1];
	     }
	  if (array_key_exists('_soap_method', $args)) {
	     $p = $this->_runProfile($args['_soap_method'], $args);
	     $this->raw_response = $p;
	     $this->response = _soapResponse("anonymousResponse", "<anonymousResult>{$p}</anonymousResult>", "http://open-tec.com/");
		 }
	  else {
	     $p = "No soap method specified";
	     $this->raw_response = $p;
	     $this->response = _soapResponse("anonymousResponse", "<anonymousResult>{$p}</anonymousResult>", "http://open-tec.com/");
         }		 
	  return "";
	  }
   public $response = "";
   public $raw_response = "";
	  
}


$soap_response_vn = "";
$soap_ns_vn = "http://schemas.xmlsoap.org/soap/envelope/";
$soap_version = (array_key_exists('soap_version', $_GET))?$_GET['soap_version']:"1_2";
switch ($soap_version) {
   default:
   case "1_1": $soap_version = SOAP_1_1; break;
   case "1_2": 
      $soap_version = SOAP_1_2; 
	  $soap_response_vn = "12"; 
	  $soap_ns_vn = "http://www.w3.org/2003/05/soap-envelope";
	  break;
   case "1_0": $soap_version = SOAP_1_0; break;
   }

$content = "";
ini_set("soap.wsdl_cache_enabled", "0");
$fnd = date('YmdGis');
 try {
   $uri = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
   $srv = new SoapServer(null, array('uri' => $uri,'soap_version'=>$soap_version));

   $parser = xml_parser_create("UTF-8"); 
   if (!xml_parse($parser,$data = file_get_contents('php://input'),true)){ 
      throw new Exception( "Cannot parse XML: ". 
      xml_error_string(xml_get_error_code($parser)). 
       " at line: ".xml_get_current_line_number($parser). 
       ", column: ".xml_get_current_column_number($parser)); 
      }
file_put_contents("tmp/soap_rcv_{$fnd}.txt", $data);	  
	$srv_build = new SOAP_ServiceClass();
	$srv_build->setRawXML($data);
    $srv->setObject($srv_build);
	ob_start();
	$srv->handle($data);
	$content = $srv_build->response;
	ob_end_clean();
	$content = trim($content);
file_put_contents($ffile = "tmp/soap_out_{$fnd}.txt", $content);	
	$len = strlen($content);
$len = filesize($ffile);
	$len += 16;
	ob_start();
	header("Content-Type: application/soap+xml");
	header("Content-Length: ".$len);
	echo $content; echo "                                                 ";
	ob_flush();
    }
 catch (Exception $e) {
   ob_end_clean();
   ob_start();
   header("Content-Type: application/soap+xml"); 
   header("Status: 500"); 
   $resp = "<SOAP-ENV:Envelope xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\"><SOAP-ENV:Body><SOAP-ENV:Fault>";
   $resp .= "<faultcode>500</faultcode>";
   $resp .= "<faultstring>".$e->getMessage()."</faultstring>";
   $resp .= "</SOAP-ENV:Fault></SOAP-ENV:Body></SOAP-ENV:Envelope>";
   echo $resp;
   ob_flush();
   //
   die(); // temp send no response
   //
   die($resp);
   }
 exit;
 


?>