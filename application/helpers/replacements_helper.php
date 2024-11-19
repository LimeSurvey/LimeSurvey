<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
* LimeSurvey
* Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/

/**
* This function replaces keywords in a text and is mainly intended for templates
* Replacement done on this function can not be used in Expression for condition or equation
* If you want keywords available on both replacement and condition, use LimeExpressionManager::setValueToKnowVar
* Or add it in LimeExpressionManager->setVariableAndTokenMappingsForExpressionManager
* If you use this functions put your replacement strings into the $replacements variable
* instead of using global variables
* NOTE - Don't do any embedded replacements in this function.  Create the array of replacement values and
* they will be done in batch at the end
*
* @param string $line Text to search in
* @param string[] $replacements Array of replacements:  Array( <stringtosearch>=><stringtoreplacewith>
* @param mixed[] $redata : array of global var used in the function
* @param null $debugSrc unused
* @param null $anonymized unused (all done in EM now)
* @param integer|null $questionNum - needed to support dynamic JavaScript-based tailoring within questions
* @param null|void $registerdata - deprecated
* @param boolean bStaticReplacement - Default off, forces non-dynamic replacements without <SPAN> tags (e.g. for the Completed page)
* @param object|string - the template object to be used
* @return string Text with replaced strings
*
* @psalm-suppress UndefinedVariable
*/
function templatereplace($line, $replacements = array(), &$redata = array(), $debugSrc = null, $anonymized = null, $questionNum = null, $registerdata = array(), $bStaticReplacement = false, $oTemplate = '')
{
    $allowedvars = array(
        'assessments',
        'captchapath',
        'clienttoken',
        'completed',
        'errormsg',
        'groupdescription',
        'groupname',
        'imageurl',
        'languagechanger',
        'loadname',
        'move',
        'navigator',
        'moveprevbutton',
        'movenextbutton',
        'percentcomplete',
        's_lang',
        'showgroupinfo',
        'showqnumcode',
        'showxquestions',
        'sitelogo',
        'templatedir',
        'thissurvey',
        'token',
        'totalBoilerplatequestions',
        'questionindex',
        'questionindexmenu',
        'totalquestions',
        'flashmessage'
    );
    $varsPassed = array();

    foreach ($allowedvars as $var) {
        if (isset($redata[$var])) {
            $$var = $redata[$var];
            $varsPassed[] = $var;
        }
    }
    // Local over-rides in case not set above
    if (!isset($showgroupinfo)) {
        $showgroupinfo = Yii::app()->getConfig('showgroupinfo');
    }
    $_surveyid = $_SESSION['LEMsid'];

    if ($_surveyid) {
        $totalgroups = QuestionGroup::model()->getTotalGroupsWithQuestions($_surveyid);
    } else {
        $totalgroups = "";
    }

    if (!isset($showxquestions)) {
        $showxquestions = Yii::app()->getConfig('showxquestions');
    }
    if (!isset($s_lang)) {
        $s_lang = (Yii::app()->session['survey_' . $_surveyid]['s_lang'] ?? 'en');
    }
    if ($_surveyid && !isset($thissurvey)) {
        $thissurvey = getSurveyInfo($_surveyid, $s_lang);
    }

    Yii::app()->loadHelper('surveytranslator');

    if (isset($thissurvey['sid'])) {
        $surveyid = $thissurvey['sid'];
    }

    // lets sanitize the survey template
    if (isset($thissurvey['templatedir'])) {
        $templatename = $thissurvey['templatedir'];
    } else {
        $templatename = App()->getConfig('defaulttheme');
    }
    if (!isset($templateurl)) {
        $templateurl = getTemplateURL($templatename) . "/";
    }

    /**
     * Template css/js files from the template config files are loaded.
     * It use the asset manager (so user never need to empty the cache, even if template is updated)
     * If debug mode is on, no asset manager is used.
     *
     * oTemplate is defined in controller/survey/index
     *
     * If templatereplace is called from the template editor, a $oTemplate is provided.
     */
    if ($oTemplate === '') {
        $oTemplate = Template::model()->getInstance($templatename);
    }

    // surveyformat
    if (isset($thissurvey['format'])) {
        $surveyformat = str_replace(array("A", "S", "G"), array("allinone", "questionbyquestion", "groupbygroup"), (string) $thissurvey['format']);
    } else {
        $surveyformat = "";
    }
    if (!empty($oTemplate->cssFramework->name)) {
        $surveyformat .= " " . $oTemplate->cssFramework->name . "-engine ";
    }


    if ((isset(Yii::app()->session['step']) && Yii::app()->session['step'] % 2) && $surveyformat != "allinone") {
        $surveyformat .= " page-odd";
    }

    if (isset($thissurvey['questionindex']) && $thissurvey['questionindex'] > 0 && $surveyformat != "allinone" && (isset(Yii::app()->session['step']) && Yii::app()->session['step'] > 0)) {
        $surveyformat .= " withindex";
    }
    if (isset($thissurvey['showprogress']) && $thissurvey['showprogress'] == "Y") {
        $surveyformat .= " showprogress";
    }
    if (isset($thissurvey['showqnumcode'])) {
        $surveyformat .= " showqnumcode-" . $thissurvey['showqnumcode'];
    }
    // real survey contact
    if (isset($thissurvey['admin']) && $thissurvey['admin'] != "") {
        $surveycontact = sprintf(gT("Please contact %s ( %s ) for further assistance."), $thissurvey['admin'], encodeEmail($thissurvey['adminemail']));
    } elseif (Yii::app()->getConfig("siteadminname")) {
        $surveycontact = sprintf(gT("Please contact %s ( %s ) for further assistance."), Yii::app()->getConfig("siteadminname"), encodeEmail(Yii::app()->getConfig("siteadminemail")));
    } else {
        $surveycontact = "";
    }

    // If there are non-bracketed replacements to be made do so above this line.
    // Only continue in this routine if there are bracketed items to replace {}
    if (strpos($line, "{") === false) {
        // process string anyway so that it can be pretty-printed
        return LimeExpressionManager::ProcessString($line, $questionNum, null, 1, 1, true);
    }

    if (
        $showgroupinfo == 'both' ||
        $showgroupinfo == 'name' ||
        ($showgroupinfo == 'choose' && !isset($thissurvey['showgroupinfo'])) ||
        ($showgroupinfo == 'choose' && $thissurvey['showgroupinfo'] == 'B') ||
        ($showgroupinfo == 'choose' && $thissurvey['showgroupinfo'] == 'N')
    ) {
        $_groupname = $groupname ?? '';
    } else {
        $_groupname = '';
    };
    if (
        $showgroupinfo == 'both' ||
        $showgroupinfo == 'description' ||
        ($showgroupinfo == 'choose' && !isset($thissurvey['showgroupinfo'])) ||
        ($showgroupinfo == 'choose' && $thissurvey['showgroupinfo'] == 'B') ||
        ($showgroupinfo == 'choose' && $thissurvey['showgroupinfo'] == 'D')
    ) {
        $_groupdescription = $groupdescription ?? '';
    } else {
        $_groupdescription = '';
    };

    if (!isset($totalquestions)) {
        $totalquestions = 0;
    }
    $_totalquestionsAsked = $totalquestions;
    if (
        $showxquestions == 'show' ||
        ($showxquestions == 'choose' && !isset($thissurvey['showxquestions'])) ||
        ($showxquestions == 'choose' && $thissurvey['showxquestions'] == 'Y')
    ) {
        if ($_totalquestionsAsked < 1) {
            $_therearexquestions = gT("There are no questions in this survey"); // Singular
        } elseif ($_totalquestionsAsked == 1) {
            $_therearexquestions = gT("There is 1 question in this survey"); //Singular
        } else {
            $_therearexquestions = gT("There are {NUMBEROFQUESTIONS} questions in this survey."); //Note this line MUST be before {NUMBEROFQUESTIONS}
        };
        $_therearexquestions = "<div class='question-count-text'>" . $_therearexquestions . "</div>";
    } else {
        $_therearexquestions = '';
    };

    if (isset($token)) {
        $_token = $token;
    } elseif (isset($clienttoken)) {
        $_token = htmlentities((string) $clienttoken, ENT_QUOTES, 'UTF-8'); // or should it be URL-encoded?
    } else {
        $_token = '';
    }

    // Expiry
    if (isset($thissurvey['expiry'])) {
        $dateformatdetails = getDateFormatData($thissurvey['surveyls_dateformat']);
        Yii::import('application.libraries.Date_Time_Converter', true);
        $datetimeobj = new Date_Time_Converter($thissurvey['expiry'], "Y-m-d H:i:s");
        $_dateoutput = $datetimeobj->convert($dateformatdetails['phpdate']);
    } else {
        $_dateoutput = '-';
    }

    $_linkreplace = '';

    if (isset($thissurvey['sid']) && isset($_SESSION['survey_' . $thissurvey['sid']]['srid']) && $thissurvey['active'] == 'Y') {
        $iscompleted = $thissurvey['iscompleted'] = SurveyDynamic::model($surveyid)->isCompleted($_SESSION['survey_' . $thissurvey['sid']]['srid']);
    } else {
        $iscompleted = $thissurvey['iscompleted'] = false;
    }

    if (isset($surveyid) && isset($_SESSION['survey_' . $surveyid]['srid'])) {
        $_quexmlpdf = CHtml::link(gT("Save as PDF"), array("/printanswers/view/surveyid/{$surveyid}/printableexport/quexmlpdf"), array('data-bs-toggle' => 'tooltip', 'data-bs-placement' => 'right', 'title' => gT("Note: Print will not include items on this page")));
    } else {
        $_quexmlpdf = "";
    }

    $_clearall = "";

    if (isset(Yii::app()->session['datestamp'])) {
        $_datestamp = Yii::app()->session['datestamp'];
    } else {
        $_datestamp = '-';
    }

    $_saveall = "";
    $aSaveAllButtons = "";
    $_restart = "";
    $_return_to_survey = "";

    $_googleAnalyticsStyle = ($thissurvey['googleanalyticsstyle'] ?? '1');
    $_endtext = '';
    if (isset($thissurvey['surveyls_endtext']) && trim((string) $thissurvey['surveyls_endtext']) != '') {
        $_endtext = $thissurvey['surveyls_endtext'];
    }

    $sitelogo = '';

    if (!empty($oTemplate->siteLogo)) {
        if (file_exists($oTemplate->path . $oTemplate->siteLogo)) {
            $sitelogo = '<img class="img-fluid site-surveylist-logo custom custom-margin top-15 bottom-15" src="' . App()->getAssetManager()->publish($oTemplate->path . $oTemplate->siteLogo) . '" alt=""/>';
        }
    }

    // Set the array of replacement variables here - don't include curly braces
    $coreReplacements = array();
    if (isset($thissurvey['sid']) && !empty($_SESSION['survey_' . $thissurvey['sid']])) {
        $coreReplacements = getStandardsReplacementFields($thissurvey);
    }

    /* This set tempVars in Expression, even if quetsion exist : replace value*/
    $coreReplacements['ACTIVE'] = (isset($thissurvey['active']) && !($thissurvey['active'] != "Y"));
    $coreReplacements['ANSWERSCLEARED'] = gT("Answers cleared");
    $coreReplacements['ASSESSMENT_HEADING'] = gT("Your assessment");
    $coreReplacements['CHECKJAVASCRIPT'] = '';
    $coreReplacements['CLEARALL'] = $_clearall;
    $coreReplacements['QUEXMLPDF'] = $_quexmlpdf;
    $coreReplacements['CLOSEWINDOW'] = ''; // Obsolete tag - keep this line for compatibility reaons
    $coreReplacements['COMPLETED'] = $redata['completed'] ?? ''; // global
    $coreReplacements['DATESTAMP'] = $_datestamp;
    $coreReplacements['ENDTEXT'] = $_endtext;
    $coreReplacements['EXPIRY'] = $_dateoutput;
    $coreReplacements['ADMINNAME'] = $thissurvey['admin'] ?? '';
    $coreReplacements['ADMINEMAIL'] = $thissurvey['adminemail'] ?? '';
    $coreReplacements['GID'] = Yii::app()->getConfig('gid', ''); // Use the gid of the question, except if we are not in question (Randomization group name)
    $coreReplacements['GROUPDESCRIPTION'] = $_groupdescription;
    $coreReplacements['GROUPNAME'] = $_groupname;
    $coreReplacements['NAVIGATOR'] = $navigator ?? ''; // global
    $coreReplacements['MOVEPREVBUTTON'] = $moveprevbutton ?? ''; // global
    $coreReplacements['MOVENEXTBUTTON'] = $movenextbutton ?? ''; // global
    $coreReplacements['NUMBEROFQUESTIONS'] = $_totalquestionsAsked;
    $coreReplacements['NUMBEROFGROUPS'] = $totalgroups;
    $coreReplacements['PERCENTCOMPLETE'] = $percentcomplete ?? ''; // global
    $coreReplacements['PRIVACYHEADING'] = '';
    $coreReplacements['PRIVACYMESSAGE'] = '';
    /* Another solution to remove index from global */
    //~ $coreReplacements['QUESTION_INDEX']=isset($questionindex) ? $questionindex: '';
    //~ $coreReplacements['QUESTION_INDEX_MENU']=isset($questionindexmenu) ? $questionindexmenu: '';
    /* indexItems is static but not rendering, seem better to call it here ? */
    $coreReplacements['QUESTION_INDEX'] = $questionindex ?? '';
    $coreReplacements['QUESTION_INDEX_MENU'] = $questionindexmenu ?? '';
    $coreReplacements['RESTART'] = $_restart;
    $coreReplacements['RETURNTOSURVEY'] = $_return_to_survey;
    $coreReplacements['SAVE'] = $_saveall ?? '';
    $coreReplacements['SITELOGO'] = $sitelogo;
    $coreReplacements['SURVEYCONTACT'] = $surveycontact;
    $coreReplacements['SURVEYDESCRIPTION'] = ($thissurvey['description'] ?? '');
    $coreReplacements['SURVEYFORMAT'] = $surveyformat ?? ''; // global
    $coreReplacements['SURVEYLANGUAGE'] = $surveylanguage = App()->language;
    $coreReplacements['SURVEYNAME'] = ($thissurvey['name'] ?? Yii::app()->getConfig('sitename'));
    $coreReplacements['SURVEYRESOURCESURL'] = (isset($thissurvey['sid']) ? Yii::app()->getConfig("uploadurl") . '/surveys/' . $thissurvey['sid'] . '/' : '');
    $coreReplacements['TEMPLATEURL'] = $templateurl;
    $coreReplacements['THEREAREXQUESTIONS'] = $_therearexquestions;
    $coreReplacements['URL'] = $_linkreplace;
    $coreReplacements['WELCOME'] = ($thissurvey['welcome'] ?? '');
    $coreReplacements['CLOSE_TRANSLATION'] = gT('Close');
    if (!is_null($replacements) && is_array($replacements)) {
        $doTheseReplacements = array_merge($coreReplacements, $replacements); // so $replacements overrides core values
    } else {
        $doTheseReplacements = $coreReplacements;
    }

    // Now do all of the replacements - In rare cases, need to do 3 deep recursion, that that is default
    $line = LimeExpressionManager::ProcessString($line, $questionNum, $doTheseReplacements, 3, 1, false, true, $bStaticReplacement);

    return $line;
}

/**
 * This function replaces keywords in a text
 * Replacement done on this function can not be used in Expression for condition or equation
 * If you want keywords available on both replacement and condition, use LimeExpressionManager::setValueToKnowVar
 * Or add it in LimeExpressionManager->setVariableAndTokenMappingsForExpressionManager
 * @psalm-suppress UndefinedVariable TODO
 */
function getStandardsReplacementFields($thissurvey)
{
    $surveyid = $_SESSION['LEMsid'];

    Yii::app()->loadHelper('surveytranslator');

    if (isset($thissurvey['sid'])) {
        $surveyid = $thissurvey['sid'];
    }

    // surveyformat
    if (isset($thissurvey['format'])) {
        $surveyformat = str_replace(array("A", "S", "G"), array("allinone", "questionbyquestion", "groupbygroup"), (string) $thissurvey['format']);
    } else {
        $surveyformat = "";
    }

    if ((isset(Yii::app()->session['step']) && Yii::app()->session['step'] % 2) && $surveyformat != "allinone") {
        $surveyformat .= " page-odd";
    }

    if (isset($thissurvey['questionindex']) && $thissurvey['questionindex'] > 0 && $surveyformat != "allinone" && (isset(Yii::app()->session['step']) && Yii::app()->session['step'] > 0)) {
        $surveyformat .= " withindex";
    }

    if (isset($thissurvey['showprogress']) && $thissurvey['showprogress'] == "Y") {
        $surveyformat .= " showprogress";
    }

    if (isset($thissurvey['showqnumcode'])) {
        $surveyformat .= " showqnumcode-" . $thissurvey['showqnumcode'];
    }

    // real survey contact
    if (isset($thissurvey['admin']) && $thissurvey['admin'] != "") {
        $surveycontact = sprintf(gT("Please contact %s ( %s ) for further assistance."), $thissurvey['admin'], encodeEmail($thissurvey['adminemail']));
    } elseif (Yii::app()->getConfig("siteadminname")) {
        $surveycontact = sprintf(gT("Please contact %s ( %s ) for further assistance."), Yii::app()->getConfig("siteadminname"), encodeEmail(Yii::app()->getConfig("siteadminemail")));
    } else {
        $surveycontact = "";
    }

    // Expiry
    if (isset($thissurvey['expiry'])) {
        $dateformatdetails = getDateFormatData($thissurvey['surveyls_dateformat']);
        Yii::import('application.libraries.Date_Time_Converter', true);
        $datetimeobj = new Date_Time_Converter($thissurvey['expiry'], "Y-m-d H:i:s");
        $_dateoutput = $datetimeobj->convert($dateformatdetails['phpdate']);
    } else {
        $_dateoutput = '-';
    }

    $_linkreplace = '';

    if (isset(Yii::app()->session['datestamp'])) {
        $_datestamp = Yii::app()->session['datestamp'];
    } else {
        $_datestamp = '-';
    }
    $_assessment_current_total = '';
    if (!empty($thissurvey['assessments']) && $thissurvey['assessments'] == "Y") {
        $assessmentdata = doAssessment($surveyid);
        $_assessment_current_total = $assessmentdata['datas']['total_score'] ?? gT("Unkown");
    }

    $oSurvey = Survey::model()->findByPk($surveyid);
    $totalquestions = $oSurvey->countTotalQuestions;

    // Set the array of replacement variables here - don't include curly braces
    $coreReplacements = array();
    $coreReplacements['NUMBEROFGROUPS'] = QuestionGroup::model()->getTotalGroupsWithQuestions($surveyid);
    $coreReplacements['NUMBEROFQUESTIONS'] = $totalquestions;
    $coreReplacements['ACTIVE'] = (isset($thissurvey['active']) && !($thissurvey['active'] != "Y"));
    $coreReplacements['DATESTAMP'] = $_datestamp;
    $coreReplacements['EXPIRY'] = $_dateoutput;
    $coreReplacements['ADMINNAME'] = $thissurvey['admin'] ?? '';
    $coreReplacements['ADMINEMAIL'] = $thissurvey['adminemail'] ?? '';
    $coreReplacements['GID'] = Yii::app()->getConfig('gid', ''); // Use the gid of the question, except if we are not in question (Randomization group name)
    $coreReplacements['NAVIGATOR'] = $navigator ?? ''; // global
    $coreReplacements['MOVEPREVBUTTON'] = $moveprevbutton ?? ''; // global
    $coreReplacements['MOVENEXTBUTTON'] = $movenextbutton ?? ''; // global
    $coreReplacements['PERCENTCOMPLETE'] = $percentcomplete ?? ''; // global
    $coreReplacements['PRIVACYHEADING'] = '';
    $coreReplacements['PRIVACYMESSAGE'] = '';
    /* Another solution to remove index from global */
    //~ $coreReplacements['QUESTION_INDEX']=isset($questionindex) ? $questionindex: '';
    //~ $coreReplacements['QUESTION_INDEX_MENU']=isset($questionindexmenu) ? $questionindexmenu: '';
    /* indexItems is static but not rendering, seem better to call it here ? */
    $coreReplacements['QUESTION_INDEX'] = $questionindex ?? '';
    $coreReplacements['QUESTION_INDEX_MENU'] = $questionindexmenu ?? '';
    $coreReplacements['SURVEYCONTACT'] = $surveycontact;
    $coreReplacements['SURVEYDESCRIPTION'] = ($thissurvey['description'] ?? '');
    $coreReplacements['SURVEYFORMAT'] = $surveyformat ?? ''; // global
    $coreReplacements['SURVEYLANGUAGE'] = $surveylanguage = App()->language;
    $coreReplacements['SURVEYNAME'] = ($thissurvey['name'] ?? Yii::app()->getConfig('sitename'));
    $coreReplacements['SURVEYRESOURCESURL'] = (isset($thissurvey['sid']) ? Yii::app()->getConfig("uploadurl") . '/surveys/' . $thissurvey['sid'] . '/' : '');
    $coreReplacements['URL'] = $_linkreplace;
    $coreReplacements['WELCOME'] = ($thissurvey['welcome'] ?? '');
    $coreReplacements['CLOSE_TRANSLATION'] = gT('Close');
    $coreReplacements['ASSESSMENT_CURRENT_TOTAL'] = $_assessment_current_total;
    $coreReplacements['TEMPLATEURL'] = Template::model()->getInstance(null, $surveyid)->templateURL;
    return $coreReplacements;
}


// This function replaces field names in a text with the related values
// (e.g. for email and template functions)
function ReplaceFields($text, $fieldsarray, $bReplaceInsertans = true, $staticReplace = true)
{

    if ($bReplaceInsertans) {
        $replacements = array();
        foreach ($fieldsarray as $key => $value) {
            $replacements[substr((string) $key, 1, -1)] = $value;
        }
        $text = LimeExpressionManager::ProcessString($text, null, $replacements, 2, 1, false, false, $staticReplace);
    } else {
        foreach ($fieldsarray as $key => $value) {
            $text = str_replace($key, $value, (string) $text);
        }
    }
    return $text;
}


/**
* passthruReplace() takes a string and looks for {PASSTHRU:myarg} variables
*  which it then substitutes for parameter data sent in the initial URL and stored
*  in the session array containing responses
*
* @param mixed $line   string - the string to iterate, and then return
* @param mixed $thissurvey     string - the string containing the surveyinformation
* @return string This string is returned containing the substituted responses
*
*/
function PassthruReplace($line, $thissurvey)
{
    while (strpos((string) $line, "{PASSTHRU:") !== false) {
        $p1 = strpos((string) $line, "{PASSTHRU:"); // startposition
        $p2 = $p1 + 10; // position of the first arg char
        $p3 = strpos((string) $line, "}", $p1); // position of the last arg char

        $cmd = substr((string) $line, $p1, $p3 - $p1 + 1); // extract the complete passthru like "{PASSTHRU:myarg}"
        $arg = substr((string) $line, $p2, $p3 - $p2); // extract the arg to passthru (like "myarg")

        // lookup for the fitting arg
        $sValue = '';
        if (isset($_SESSION['survey_' . $thissurvey['sid']]['urlparams'][$arg])) {
            $sValue = urlencode((string) $_SESSION['survey_' . $thissurvey['sid']]['urlparams'][$arg]);
        }
        $line = str_replace($cmd, $sValue, (string) $line); // replace
    }

    return $line;
}
