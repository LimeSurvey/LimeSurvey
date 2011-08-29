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
 * $Id: quexmlsurvey.php 9607 2010-12-08 22:59:51Z azammitdcarf $
 */

//Ensure script is not run directly, avoid path disclosure
include_once("login_check.php");
include_once(dirname(__FILE__)."/classes/quexml/quexmlpdf.php");

//---------------------
// Comes from http://fr2.php.net/tempnam
function tempdir($dir, $prefix='', $mode=0700)
{
    if (substr($dir, -1) != '/') $dir .= '/';

    do
    {
        $path = $dir.$prefix.mt_rand(0, 9999999);
    } while (!mkdir($path, $mode));

    return $path;
}

global $tempdir;

$surveyid = $_GET['sid'];

// Set the language of the survey, either from GET parameter of session var
if (isset($_GET['lang']))
{
    $_GET['lang'] = preg_replace("/[^a-zA-Z0-9-]/", "", $_GET['lang']);
    if ($_GET['lang']) $surveyprintlang = $_GET['lang'];
} else
{
    $surveyprintlang=GetbaseLanguageFromSurveyid($surveyid);
}

// Setting the selected language for printout
$clang = new limesurvey_lang($surveyprintlang);

$quexmlpdf = new queXMLPDF(PDF_PAGE_ORIENTATION, 'mm', PDF_PAGE_FORMAT, true, 'UTF-8', false);

set_time_limit(120);

$noheader = true;

include_once("export_structure_quexml.php");

$quexmlpdf->create($quexmlpdf->createqueXML($quexml));

//NEED TO GET QID from $quexmlpdf
$qid = intval($quexmlpdf->getQuestionnaireId());

$zipdir=tempdir($tempdir);

$f1 = "$zipdir/quexf_banding_{$qid}_{$surveyprintlang}.xml";
$f2 = "$zipdir/quexmlpdf_{$qid}_{$surveyprintlang}.pdf";
$f3 = "$zipdir/quexml_{$qid}_{$surveyprintlang}.xml";
$f4 = "$zipdir/readme.txt";

file_put_contents($f1, $quexmlpdf->getLayout());
file_put_contents($f2, $quexmlpdf->Output("quexml_$qid.pdf", 'S'));
file_put_contents($f3, $quexml);
file_put_contents($f4, $clang->gT('This archive contains a PDF file of the survey, the queXML file of the survey and a queXF banding XML file which can be used with queXF: http://quexf.sourceforge.net/ for processing scanned surveys.'));

require_once("classes/phpzip/phpzip.inc.php");
$z = new PHPZip();
$zipfile="$tempdir/quexmlpdf_{$qid}_{$surveyprintlang}.zip";
$z->Zip($zipdir, $zipfile);

unlink($f1);
unlink($f2);
unlink($f3);
unlink($f4);
rmdir($zipdir);

header('Content-Type: application/zip');
header('Content-Transfer-Encoding: binary');
header('Content-Disposition: attachment; filename="quexmlpdf_' . $qid . '_' . $surveyprintlang . '.zip"'); 
$len = filesize($zipfile);
header("Content-Length: $len");
header("Pragma: public");
// load the file to send:
ob_clean();
flush();
readfile($zipfile);
unlink($zipfile);

exit();
