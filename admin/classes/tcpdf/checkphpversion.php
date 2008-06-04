<?php
if (version_compare(PHP_VERSION, '5.0.0', '<') && version_compare(PHP_VERSION, '4.0.0', '>'))
{
  require_once('tcpdf_php4.php');
}
else if (version_compare(PHP_VERSION, '5.0.0', '>'))
{
  require_once('tcpdf.php');
}
?>
