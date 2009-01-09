<?php
/*
* LimeSurvey
* Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
* 
* $Id$
*/


include_once("login_check.php");

if (!isset($surveyid))
{
	returnglobal('sid');
}

if (!isset($lid))
{
	returnglobal('lid');
}

if ($action == "exportsurvresources" && $surveyid) {
	require("classes/phpzip/phpzip.inc.php");
	$z = new PHPZip();
	$resourcesdir="$publicdir/upload/surveys/$surveyid/";
	$zipfile="$tempdir/resources-survey-$surveyid.zip";
	$z -> Zip($resourcesdir, $zipfile);
	if (is_file($zipfile)) {
		//Send the file for download!
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

		header("Content-Type: application/force-download");
		header( "Content-Disposition: attachment; filename=resources-survey-$surveyid.zip" );
		header( "Content-Description: File Transfer");
		@readfile($zipfile);

		//Delete the temporary file
		unlink($zipfile);

	}
}
if ($action == "exportlabelresources" && $lid) {
	require("classes/phpzip/phpzip.inc.php");
	$z = new PHPZip();
	$resourcesdir="$publicdir/upload/labels/$lid/";
	$zipfile="$tempdir/resources-labelset-$lid.zip";
	$z -> Zip($resourcesdir, $zipfile);
	if (is_file($zipfile)) {
		//Send the file for download!
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

		header("Content-Type: application/force-download");
		header( "Content-Disposition: attachment; filename=resources-label-$lid.zip" );
		header( "Content-Description: File Transfer");
		@readfile($zipfile);

		//Delete the temporary file
		unlink($zipfile);

	}
}


?>
