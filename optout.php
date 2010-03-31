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

// Security Checked: POST, GET, SESSION, REQUEST, returnglobal, DB

require_once(dirname(__FILE__).'/classes/core/startup.php');    // Since this file can be directly run
require_once(dirname(__FILE__).'/config-defaults.php');
require_once(dirname(__FILE__).'/common.php');
require_once($rootdir.'/classes/core/language.php');

$surveyid=returnglobal('sid');
$postlang=returnglobal('lang');
$token=returnglobal('token');

//Check that there is a SID
if (!isset($surveyid))
{
    //You must have an SID to use this
    include "index.php";
    exit;
}

// Get passed language from form, so that we dont loose this!
if (!isset($postlang) || $postlang == "")
{
    $baselang = GetBaseLanguageFromSurveyID($surveyid);
    $clang = new limesurvey_lang($baselang);
} else {
    $clang = new limesurvey_lang($postlang);
    $baselang = $postlang;
}

$thissurvey=getSurveyInfo($surveyid,$baselang);
if ($thissurvey==false || !tableExists("tokens_{$surveyid}")){
    $html='<p>'.$clang->gT('This survey does not seem to exist.');
}
else
{
    $usquery = "SELECT emailstatus from ".db_table_name("tokens_{$surveyid}")." where token=".db_quoteall($token,true);
    $usresult = $connect->GetOne($usquery);

    if ($usresult===false)
    {
        $html='<p>'.$clang->gT('You are not a participant in this survey.');
    }
    elseif ($usresult=='OK')
    {
        $usquery = "Update ".db_table_name("tokens_{$surveyid}")." set emailstatus='OptOut' where token=".db_quoteall($token,true);
        $usresult = $connect->Execute($usquery);
        $html='<p>'.$clang->gT('You have been successfully removed from this survey.');
    }
    else
    {
        $html='<p>'.$clang->gT('You have been already removed from this survey.');
    }
}


//PRINT COMPLETED PAGE
if (!$thissurvey['template']) {$thistpl="$templaterootdir/default";} else {$thistpl="$templaterootdir/{$thissurvey['template']}";}
if (!is_dir($thistpl)) {$thistpl="$templaterootdir/default";}

sendcacheheaders();
doHeader();

foreach(file("$thistpl/startpage.pstpl") as $op)
{
    echo templatereplace($op);
}
foreach(file("$thistpl/survey.pstpl") as $op)
{
    echo "\t".templatereplace($op);
}
echo $html;
foreach(file("$thistpl/endpage.pstpl") as $op)
{
    echo templatereplace($op);
}
doFooter();

// Closing PHP tag is intentially left out (yes, it's fine!)