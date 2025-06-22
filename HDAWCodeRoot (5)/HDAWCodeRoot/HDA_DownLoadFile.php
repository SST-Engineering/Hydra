<?php
$file = $_GET['file'];
header ("Content-type: octet/stream");
$filename = pathinfo($file, PATHINFO_BASENAME);
header ("Content-disposition: attachment; filename=".$filename.";");
header("Content-Length: ".filesize($file));
readfile($file);
exit;
?>