<?php
include_once __DIR__."/HDA_Initials.php";
include_once __DIR__."/HDA_Finals.php";
_hydra_("HDA_Printer", $loads = array("XML","DB","Functions","Process","CodeCompiler","Validate","Email","Logging"));


include_once "../{$code_root}/Classes/HTML2PDF/html2pdf_class.php";

$file = $_GET['file'];
$file = urldecode($file);
$hdr = $_GET['title'];
$hdr = urldecode($hdr);
$paper = (array_key_exists('paper',$_GET))?substr($_GET['paper'],0,2):'A4';
if (!@file_exists($file)) die("Unable to find data for report");
$data = file_get_contents($file);

        $html2pdf = new HTML2PDF('L', $paper, 'en', true, 'UTF-8', 3);
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $html2pdf->writeHTML($data, isset($_GET['vuehtml']));
ob_start();
ob_clean();
        $html2pdf->Output('','I');

$content = NULL;
$title = "Printing";
_finals_();
exit;
?>