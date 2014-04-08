<?php
	chdir('../');
	$_SERVER['SCRIPT_NAME'] = 'index.php';
	$_GET['r'] = 'admin';
	include 'index.php';
?>