<?php

 
function _soapResponse($node, $xml, $ns=null) {
   if (!is_null($ns)) $ns = "xmlns=\"{$ns}\" ";
   return _buildSoap12Response("<{$node} {$ns}>{$xml}</{$node}>");
   }

function _buildSoap12Response($xml) {
   global $soap_response_vn;
   global $soap_ns_vn;
   $t = "";
   $t .= "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
   $t .= "<soap{$soap_response_vn}:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap{$soap_response_vn}=\"{$soap_ns_vn}\">";
   $t .= "<soap{$soap_response_vn}:Body>";
   $t .= $xml;
   $t .= "</soap{$soap_response_vn}:Body>";
   $t .= "</soap{$soap_response_vn}:Envelope>";

   return $t;
   }
   
function _postSoapRequest($item, $a) {
   PRO_AddToParams('SOAP_REQUEST', $a);
   }
function _getSoapRequest($item) {
   $a = PRO_ReadParam('SOAP_REQUEST');
   PRO_Clear('SOAP_REQUEST');
   return $a;
   }
function _postSoapResponse($item, $a) {
   PRO_AddToParams('SOAP_RESPONSE', $a);
   }
function _getSoapResponse($item) {
   return PRO_ReadParam('SOAP_RESPONSE');
   }
function _postSoapXML($item, $xml) {
   PRO_AddToParams('SOAP_XML', $xml);
   }
function _getSoapXML($item) {
   return PRO_ReadParam('SOAP_XML');
   }

function POST_SOAP($url, $method, $args) {
   

   if (is_null($url)) $url = "http://localhost/HDAW/HDA_soap_accept.php";


   $client = new SoapClient(null, array(
       'location' => $url,
       'uri'      => $url));
   $output = $client->__soapCall($method,$args);

   return $output;
   }
function HDA_SoapThis($url, $method, $args) {
   global $UserCode;
   $soap = array();
   $soap['url'] = $url;
   $soap['method'] = $method;
   $soap['args'] = $args;
   return hda_db::hdadb()->HDA_DB_actionToQ($UserCode, 'SOAP', $soap);
   }

function HDA_SendSOAP($soap, &$err) {
	   file_put_contents("tmp/sendingsoap.txt", print_r($soap, true));
   $client = null;
   try {
	   if (array_key_exists('rawxml',$soap['args'])) {
	      $response = do_post_request($soap['url'], $soap['args']['rawxml']);
		  }
	   else
		  $response = POST_SOAP($soap['url'], $soap['method'], $soap['args']);
	   file_put_contents("tmp/sendsoapresponse.txt", print_r($response, true));
	   HDA_LogThis("SOAPed success to {$soap['url']}", 'SOAP');
	   }
   catch (Exception $e) {
      try {
         $lastRsp = (is_object($client))?$client->_getLastResponseHeaders():null;
		 }
	  catch (Exception $ee) {
	     $lastRsp = null;
		 }
	  file_put_contents("tmp/lastforwardrsp.txt", print_r($lastRsp,true));
      $err = "Send Soap: exception {$e} ";
	  HDA_LogThis($err, 'SOAP');
      }
   return true;
   }
   
function do_post_request( $url, $query_data ) { 
	$params = array( 'http'    => array( 'method'  => 'POST', 'header'  => "Content-type: application/soap+xml\r\n" . "Content-Length: " . strlen($query_data) . "\r\n", 'content' => $query_data ) ); 
	$context = stream_context_create( $params ); 
	$contents = @file_get_contents( $url, false, $context ); 
	return $contents; 
	} 
   
class ForwardSoapClient extends SoapClient {
   function __construct($wsdl, $options) {
      $options['trace']=1;
      parent::__construct($wsdl, $options);
	  }
   public function __doRequest($request, $location, $action, $version, $one_way=0) { 
      $request = $this->xml;
      $result = @parent::__doRequest($request, $location, $action, $version, $one_way); 
      return $result; 
      }
   private $xml;
   public function __doForwardRequest($xml) {
      $this->xml = $xml;
      }
	  
   }

?>