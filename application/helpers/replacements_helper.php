<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
* If you use this functions put your replacement strings into the $replacements variable
* instead of using global variables
* NOTE - Don't do any embedded replacements in this function.  Create the array of replacement values and
* they will be done in batch at the end
*
* @param string $line Text to search in
* @param string[] $replacements Array of replacements:  Array( <stringtosearch>=><stringtoreplacewith>
* @param mixed[] $redata : array of global var used in the function
* @param void $debugSrc deprecated
* @param boolean $anonymized Determines if token data is being used or just replaced with blanks
* @param integer|null $questionNum - needed to support dynamic JavaScript-based tailoring within questions
* @param void $registerdata - deprecated
* @param boolean bStaticReplacement - Default off, forces non-dynamic replacements without <SPAN> tags (e.g. for the Completed page)
* @param object|string - the template object to be used
* @return string Text with replaced strings
*/
function templatereplace($line, $replacements = array(), &$redata = array(), $debugSrc = 'Unspecified', $anonymized = false, $questionNum = NULL, $registerdata = array(), $bStaticReplacement = false, $oTemplate='')
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

    foreach($allowedvars as $var)
    {
        if(isset($redata[$var])) {
            $$var = $redata[$var];
            $varsPassed[] = $var;
        }
    }
    // Local over-rides in case not set above
    if (!isset($showgroupinfo)) { $showgroupinfo = Yii::app()->getConfig('showgroupinfo'); }
    if (!isset($showqnumcode)) { $showqnumcode = Yii::app()->getConfig('showqnumcode'); }
    $_surveyid = Yii::app()->getConfig('surveyID');
    if (!isset($showxquestions)) { $showxquestions = Yii::app()->getConfig('showxquestions'); }
    if (!isset($s_lang)) { $s_lang = (isset(Yii::app()->session['survey_'.$_surveyid]['s_lang']) ? Yii::app()->session['survey_'.$_surveyid]['s_lang'] : 'en'); }
    if($_surveyid && !isset($thissurvey))
    {
        $thissurvey=getSurveyInfo($_surveyid,$s_lang);
    }
    if (!isset($captchapath)) { $captchapath = ''; }


    Yii::app()->loadHelper('surveytranslator');

    if (isset($thissurvey['sid'])) {
        $surveyid = $thissurvey['sid'];
    }

    // lets sanitize the survey template
    if(isset($thissurvey['templatedir']))
    {
        $templatename=$thissurvey['templatedir'];
    }
    else
    {
        $templatename=Yii::app()->getConfig('defaulttemplate');
    }
    if(!isset($templatedir)) $templatedir = getTemplatePath($templatename);
    if(!isset($templateurl)) $templateurl = getTemplateURL($templatename)."/";
    if (!$anonymized && isset($thissurvey['anonymized'])) {
        $anonymized=($thissurvey['anonymized']=="Y");
    }
    // TEMPLATECSS
    $_templatecss="";
    $_templatejs="";

    /**
     * Template css/js files from the template config files are loaded.
     * It use the asset manager (so user never need to empty the cache, even if template is updated)
     * If debug mode is on, no asset manager is used.
     *
     * oTemplate is defined in controller/survey/index
     *
     * If templatereplace is called from the template editor, a $oTemplate is provided.
     */
    if ($oTemplate === '')
    {
        $oTemplate = Template::model()->getInstance($templatename);
    }

    if(stripos ($line,"{TEMPLATECSS}"))
    {
        // This package is created in model TemplateConfiguration::createTemplatePackage
        if(!YII_DEBUG ||  Yii::app()->getConfig('use_asset_manager'))
        {
            Yii::app()->clientScript->registerPackage( 'survey-template' );
        }
        else
        {

            // In debug mode, the Asset Manager is not used
            // So, dev don't need to update the directory date to get the new version of their template.
            // They must think about refreshing their brower's cache (ctrl + F5)
            /* @todo : need to regsiter the packages of 'survey-template' */
            $aOtherFiles = $oTemplate->otherFiles;

            //var_dump($aCssFiles);var_dump($aJsFiles);die();
            $aCssFiles = (array) $oTemplate->config->files->css->filename;
            $aJsFiles  = (array) $oTemplate->config->files->js->filename;

            foreach($aCssFiles as $sCssFile)
            {
                if (file_exists($oTemplate->path .DIRECTORY_SEPARATOR. $sCssFile))
                {
                    Yii::app()->getClientScript()->registerCssFile("{$templateurl}$sCssFile");
                }
            }
            foreach($aJsFiles as $sJsFile)
            {
                if (file_exists($oTemplate->path .DIRECTORY_SEPARATOR. $sJsFile))
                {
                    Yii::app()->getClientScript()->registerScriptFile("{$templateurl}$sJsFile");
                }
            }
            /* RTL|LTR CSS & JS */
            $dir=getLanguageRTL(App()->language) ? 'rtl' : 'ltr';
            if (isset($oTemplate->config->files->$dir)){
                $aCssFilesDir = isset($oTemplate->config->files->$dir->css->filename) ? (array)$oTemplate->config->files->$dir->css->filename : array();
                $aJsFilesDir  = isset($oTemplate->config->files->$dir->js->filename) ? (array)$oTemplate->config->files->$dir->js->filename : array();
                foreach($aCssFilesDir as $sCssFile)
                {
                    if (file_exists($oTemplate->path .DIRECTORY_SEPARATOR. $sCssFile))
                    {
                        Yii::app()->getClientScript()->registerCssFile("{$templateurl}$sCssFile");
                    }
                }
                foreach($aJsFilesDir as $sJsFile)
                {
                    if (file_exists($oTemplate->path .DIRECTORY_SEPARATOR. $sJsFile))
                    {
                        Yii::app()->getClientScript()->registerScriptFile("{$templateurl}$sJsFile");
                    }
                }
            }
        }
    }

    // surveyformat
    if (isset($thissurvey['format']))
    {
        $surveyformat = str_replace(array("A", "S", "G"), array("allinone", "questionbyquestion", "groupbygroup"), $thissurvey['format']);
    }
    else
    {
        $surveyformat = "";
    }
    if( ! empty($oTemplate->cssFramework->name) )
    {
        $surveyformat .= " ".$oTemplate->cssFramework->name."-engine ";
    }


    if ((isset(Yii::app()->session['step']) && Yii::app()->session['step'] % 2) && $surveyformat!="allinone")
    {
        $surveyformat .= " page-odd";
    }

    if (isset($thissurvey['questionindex']) && $thissurvey['questionindex'] > 0 && $surveyformat!="allinone" && (isset(Yii::app()->session['step']) && Yii::app()->session['step']>0)){
        $surveyformat .= " withindex";
    }
    if (isset($thissurvey['showprogress']) && $thissurvey['showprogress']=="Y"){
        $surveyformat .= " showprogress";
    }
    if (isset($thissurvey['showqnumcode'])){
        $surveyformat .= " showqnumcode-".$thissurvey['showqnumcode'];
    }
    // real survey contact
    if(isset($thissurvey['admin']) && $thissurvey['admin']!=""){
        $surveycontact=sprintf(gT("Please contact %s ( %s ) for further assistance."),$thissurvey['admin'],encodeEmail($thissurvey['adminemail']));
    }elseif(Yii::app()->getConfig("siteadminname")){
        $surveycontact=sprintf(gT("Please contact %s ( %s ) for further assistance."),Yii::app()->getConfig("siteadminname"),encodeEmail(Yii::app()->getConfig("siteadminemail")));
    }else{
        $surveycontact="";
    }

    // If there are non-bracketed replacements to be made do so above this line.
    // Only continue in this routine if there are bracketed items to replace {}
    if (strpos($line, "{") === false) {
        // process string anyway so that it can be pretty-printed
        return LimeExpressionManager::ProcessString($line, $questionNum, NULL, false, 1, 1, true);
    }

    if (
    $showgroupinfo == 'both' ||
    $showgroupinfo == 'name' ||
    ($showgroupinfo == 'choose' && !isset($thissurvey['showgroupinfo'])) ||
    ($showgroupinfo == 'choose' && $thissurvey['showgroupinfo'] == 'B') ||
    ($showgroupinfo == 'choose' && $thissurvey['showgroupinfo'] == 'N')
    )
    {
        $_groupname = isset($groupname) ? $groupname : '';
    }
    else
    {
        $_groupname = '';
    };
    if (
    $showgroupinfo == 'both' ||
    $showgroupinfo == 'description' ||
    ($showgroupinfo == 'choose' && !isset($thissurvey['showgroupinfo'])) ||
    ($showgroupinfo == 'choose' && $thissurvey['showgroupinfo'] == 'B') ||
    ($showgroupinfo == 'choose' && $thissurvey['showgroupinfo'] == 'D')
    )
    {
        $_groupdescription = isset($groupdescription) ? $groupdescription : '';
    }
    else
    {
        $_groupdescription = '';
    };

    if(!isset($totalquestions)) $totalquestions = 0;
    $_totalquestionsAsked = $totalquestions;
    if (
    $showxquestions == 'show' ||
    ($showxquestions == 'choose' && !isset($thissurvey['showxquestions'])) ||
    ($showxquestions == 'choose' && $thissurvey['showxquestions'] == 'Y')
    )
    {
        if ($_totalquestionsAsked < 1)
        {
            $_therearexquestions = gT("There are no questions in this survey"); // Singular
        }
        elseif ($_totalquestionsAsked == 1)
        {
            $_therearexquestions = gT("There is 1 question in this survey"); //Singular
        }
        else
        {
            $_therearexquestions = gT("There are {NUMBEROFQUESTIONS} questions in this survey.");    //Note this line MUST be before {NUMBEROFQUESTIONS}
        };
        $_therearexquestions = "<div class='question-count-text'>".$_therearexquestions."</div>";
    }
    else
    {
        $_therearexquestions = '';
    };


    if (isset($token))
    {
        $_token = $token;
    }
    elseif (isset($clienttoken))
    {
        $_token = htmlentities($clienttoken, ENT_QUOTES, 'UTF-8');  // or should it be URL-encoded?
    }
    else
    {
        $_token = '';
    }

    // Expiry
    if (isset($thissurvey['expiry']))
    {
        $dateformatdetails=getDateFormatData($thissurvey['surveyls_dateformat']);
        Yii::import('application.libraries.Date_Time_Converter', true);
        $datetimeobj = new Date_Time_Converter($thissurvey['expiry'],"Y-m-d") ;
        $_dateoutput=$datetimeobj->convert($dateformatdetails['phpdate']);
    }
    else
    {
        $_dateoutput = '-';
    }

    if (isset($thissurvey['surveyls_url']) and $thissurvey['surveyls_url'] != "")
    {
        if (trim($thissurvey['surveyls_urldescription']) != '')
        {
            $_linkreplace = App()->twigRenderer->render("/survey/system/url",array(
                'url'=>$thissurvey['surveyls_url'],
                'description'=>$thissurvey['surveyls_urldescription'],
                'type'=>"survey-endurl",
                'coreClass'=>"ls-endurl",
            ),true);
        }
        else
        {
            $_linkreplace = App()->twigRenderer->render("/survey/system/url",array(
                'url'=>$thissurvey['surveyls_url'],
                'description'=>$thissurvey['surveyls_url'],
                'type'=>"survey-endurl",
                'coreClass'=>"ls-endurl ls-surveyurl",
            ),true);
        }
    }
    else
    {
        $_linkreplace='';
    }

    if(isset($thissurvey['sid']) && isset($_SESSION['survey_'.$thissurvey['sid']]['srid']) && $thissurvey['active']=='Y')
    {
        $iscompleted=SurveyDynamic::model($surveyid)->isCompleted($_SESSION['survey_'.$thissurvey['sid']]['srid']);
    }
    else
    {
        $iscompleted=false;
    }
    if (isset($surveyid) && !$iscompleted)
    {
        $aClearAll=doHtmlClearAll();
        $_clearall = $aClearAll['button'];
        $_clearalllinks = $aClearAll['link'];
    }
    else
    {
        $_clearall = "";
        $_clearalllinks = '';
    }

    if (isset(Yii::app()->session['datestamp']))
    {
        $_datestamp = Yii::app()->session['datestamp'];
    }
    else
    {
        $_datestamp = '-';
    }
    if (isset($thissurvey['allowsave']) and $thissurvey['allowsave'] == "Y")
    {
        $_saveall = doHtmlSaveAll(isset($move)?$move:NULL);
        $_savelinks = doHtmlSaveLinks(isset($move)?$move:NULL);
    }
    else
    {
        $_saveall = "";
        $_savelinks = "";
    }

    if(isset($surveyid))
    {
        $restartparam=array();
        if($_token)
            $restartparam['token']=sanitize_token($_token);// urlencode with needed with sanitize_token
        if (Yii::app()->request->getQuery('lang'))
            $restartparam['lang']=sanitize_languagecode(Yii::app()->request->getQuery('lang'));
        elseif($s_lang)
            $restartparam['lang']=$s_lang;
        $restartparam['newtest']="Y";
        $restarturl=Yii::app()->getController()->createUrl("survey/index/sid/$surveyid",$restartparam);
        $_restart = App()->twigRenderer->render("/survey/system/url",array(
            'url'=>$restarturl,
            'description'=>gT("Restart this Survey"),
            'type'=>"survey-restart",
            'coreClass'=>"ls-restart",
        ),true);
    }
    else
    {
        $_restart = "";
    }

    if (isset($surveyid))
    {
        if($_token)
        {
            $returnlink=Yii::app()->getController()->createUrl("survey/index/sid/{$surveyid}",array('token'=>Token::sanitizeToken($_token)));
        }
        else
        {
            $returnlink=Yii::app()->getController()->createUrl("survey/index/sid/{$surveyid}");
        }
        if(isset(Yii::app()->session['survey_'.$_surveyid]['step'])){
            $returndescription = gT("Return to survey");
        }else{
            $returndescription = gT("Go to survey");
        }
        $_return_to_survey = App()->twigRenderer->render("/survey/system/url",array(
            'url'=>$returnlink,
            'description'=>$returndescription,
            'type'=>"survey-return",
            'coreClass'=>"ls-return",
        ),true);
    }
    else
    {
        $_return_to_survey = "";
    }

    // Assessments
    $assessmenthtml="";
    if (isset($surveyid) && !is_null($surveyid) && function_exists('doAssessment'))
    {
        $assessmentdata = doAssessment($surveyid, true);
        $_assessment_current_total = $assessmentdata['total'];
        if(stripos ($line,"{ASSESSMENTS}")){
            $assessmenthtml=doAssessment($surveyid, false);
        }
    }
    else
    {
        $_assessment_current_total = '';
    }
    if(isset($thissurvey['googleanalyticsapikey']) && $thissurvey['googleanalyticsapikey'] === "9999useGlobal9999")
    {
        $_googleAnalyticsAPIKey = trim(getGlobalSetting('googleanalyticsapikey'));
    }
    else if (isset($thissurvey['googleanalyticsapikey']) && trim($thissurvey['googleanalyticsapikey']) != '')
    {
        $_googleAnalyticsAPIKey = trim($thissurvey['googleanalyticsapikey']);
    }
    else
    {
        $_googleAnalyticsAPIKey = "";

    }
    $_googleAnalyticsStyle = (isset($thissurvey['googleanalyticsstyle']) ? $thissurvey['googleanalyticsstyle'] : '1');
    $_googleAnalyticsJavaScript = '';

    if ($_googleAnalyticsStyle != '' && $_googleAnalyticsStyle != 0 && $_googleAnalyticsAPIKey != '')
    {
        switch ($_googleAnalyticsStyle)
        {
            case '1':
                // Default Google Tracking
                $_googleAnalyticsJavaScript = <<<EOD
<script>
(function(i,s,o,g,r,a,m){ i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments) },i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

ga('create', '$_googleAnalyticsAPIKey', 'auto');  // Replace with your property ID.
ga('send', 'pageview');

</script>

EOD;
                break;
            case '2':
                // SurveyName-[SID]/[GSEQ]-GroupName - create custom GSEQ based upon page step
                $moveInfo = LimeExpressionManager::GetLastMoveResult();
                if (is_null($moveInfo)) {
                    $gseq='welcome';
                }
                else if ($moveInfo['finished'])
                    {
                        $gseq='finished';
                    }
                    else if (isset($moveInfo['at_start']) && $moveInfo['at_start'])
                        {
                            $gseq='welcome';
                        }
                        else if (is_null($_groupname))
                            {
                                $gseq='printanswers';
                            }
                            else
                            {
                                $gseq=$moveInfo['gseq']+1;
                }
                $_trackURL = htmlspecialchars($thissurvey['name'] . '-[' . $surveyid . ']/[' . $gseq . ']-' . $_groupname);
                $_googleAnalyticsJavaScript = <<<EOD
<script>
(function(i,s,o,g,r,a,m){ i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){ (i[r].q=i[r].q||[]).push(arguments) }
,i[r].l=1*new Date();a=s.createElement(o), m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
 })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

ga('create', '$_googleAnalyticsAPIKey', 'auto');
ga('send', 'pageview');
ga('send', 'pageview', '$_trackURL');

</script>
EOD;
                break;
        }
    }

    $_endtext = '';
    if (isset($thissurvey['surveyls_endtext']) && trim($thissurvey['surveyls_endtext'])!='')
    {
        $_endtext = $thissurvey['surveyls_endtext'];
    }

    $sitelogo = '';

    if(!empty($oTemplate->siteLogo))
    {
        if (file_exists ($oTemplate->path.'/'.$oTemplate->siteLogo ))
        {
            $sitelogo= '<img class="img-responsive" src="'.App()->getAssetManager()->publish( $oTemplate->path.'/'.$oTemplate->siteLogo).'" alt=""/>';
        }
    }

    // Set the array of replacement variables here - don't include curly braces
    $coreReplacements = array();
    $coreReplacements['ACTIVE'] = (isset($thissurvey['active']) && !($thissurvey['active'] != "Y"));
    $coreReplacements['ANSWERSCLEARED'] = gT("Answers cleared");
    $coreReplacements['ASSESSMENTS'] = $assessmenthtml;
    $coreReplacements['ASSESSMENT_CURRENT_TOTAL'] = $_assessment_current_total;
    $coreReplacements['ASSESSMENT_HEADING'] = gT("Your assessment");
    $coreReplacements['CHECKJAVASCRIPT'] = App()->twigRenderer->render("/survey/system/no-javascript",array(),true);
    $coreReplacements['CLEARALL'] = $_clearall;
    $coreReplacements['CLEARALL_LINKS'] = $_clearalllinks;
    $coreReplacements['CLOSEWINDOW'] = ''; // Obsolete tag - keep this line for compatibility reaons
    $coreReplacements['COMPLETED'] = isset($redata['completed']) ? $redata['completed'] : '';    // global
    $coreReplacements['DATESTAMP'] = $_datestamp;
    $coreReplacements['ENDTEXT'] = $_endtext;
    $coreReplacements['EXPIRY'] = $_dateoutput;
    $coreReplacements['ADMINNAME'] = isset($thissurvey['admin']) ? $thissurvey['admin'] : '';
    $coreReplacements['ADMINEMAIL'] = isset($thissurvey['adminemail']) ? $thissurvey['adminemail'] : '';
    $coreReplacements['GID'] = Yii::app()->getConfig('gid','');// Use the gid of the question, except if we are not in question (Randomization group name)
    $coreReplacements['GOOGLE_ANALYTICS_API_KEY'] = $_googleAnalyticsAPIKey;
    $coreReplacements['GOOGLE_ANALYTICS_JAVASCRIPT'] = $_googleAnalyticsJavaScript;
    $coreReplacements['GROUPDESCRIPTION'] = $_groupdescription;
    $coreReplacements['GROUPNAME'] = $_groupname;
    $coreReplacements['LANG'] = App()->language;
    $coreReplacements['LANGUAGECHANGER'] = isset($languagechanger) ? $languagechanger : '';    // global
    $coreReplacements['FLASHMESSAGE'] = makeFlashMessage();  // TODO: Really generate this each time function is called? Only relevant for startpage.tstpl
    $coreReplacements['NAVIGATOR'] = isset($navigator) ? $navigator : '';    // global
    $coreReplacements['MOVEPREVBUTTON'] = isset($moveprevbutton) ? $moveprevbutton : '';    // global
    $coreReplacements['MOVENEXTBUTTON'] = isset($movenextbutton) ? $movenextbutton : '';    // global
    $coreReplacements['NUMBEROFQUESTIONS'] = $_totalquestionsAsked;
    $coreReplacements['PERCENTCOMPLETE'] = isset($percentcomplete) ? $percentcomplete : '';    // global
    $coreReplacements['PRIVACYHEADING'] = App()->twigRenderer->render("/survey/system/privacy/heading",array(),true);
    $coreReplacements['PRIVACYMESSAGE'] = App()->twigRenderer->render("/survey/system/privacy/message",array(),true);
    /* Another solution to remove index from global */
    //~ $coreReplacements['QUESTION_INDEX']=isset($questionindex) ? $questionindex: '';
    //~ $coreReplacements['QUESTION_INDEX_MENU']=isset($questionindexmenu) ? $questionindexmenu: '';
    /* indexItems is static but not rendering, seem better to call it here ? */
    $coreReplacements['QUESTION_INDEX']=isset($questionindex) ? $questionindex: '';
    $coreReplacements['QUESTION_INDEX_MENU']=isset($questionindexmenu) ? $questionindexmenu: '';
    $coreReplacements['RESTART'] = $_restart;
    $coreReplacements['RETURNTOSURVEY'] = $_return_to_survey;
    $coreReplacements['SAVE_LINKS'] = $_savelinks;
    $coreReplacements['SAVE'] = $_saveall;
    $coreReplacements['SAVEDID'] = isset(Yii::app()->session['survey_'.$_surveyid]['srid']) ? Yii::app()->session['survey_'.$_surveyid]['srid']: '';
    $coreReplacements['SID'] = Yii::app()->getConfig('surveyID','');// Allways use surveyID from config
    $coreReplacements['SITENAME'] = Yii::app()->getConfig('sitename');
    $coreReplacements['SITELOGO'] = $sitelogo;
    $coreReplacements['SURVEYCONTACT'] = $surveycontact;
    $coreReplacements['SURVEYDESCRIPTION'] = (isset($thissurvey['description']) ? $thissurvey['description'] : '');
    $coreReplacements['SURVEYFORMAT'] = isset($surveyformat) ? $surveyformat : '';  // global
    $coreReplacements['SURVEYLANGUAGE'] = $surveylanguage = App()->language;
    $coreReplacements['SURVEYNAME'] = (isset($thissurvey['name']) ? $thissurvey['name'] : Yii::app()->getConfig('sitename'));
    $coreReplacements['SURVEYRESOURCESURL'] = (isset($thissurvey['sid']) ? Yii::app()->getConfig("uploadurl").'/surveys/'.$thissurvey['sid'].'/' : '');
    $coreReplacements['TEMPLATECSS'] = $_templatecss;
    $coreReplacements['TEMPLATEJS'] = $_templatejs;
    $coreReplacements['TEMPLATEURL'] = $templateurl;
    $coreReplacements['THEREAREXQUESTIONS'] = $_therearexquestions;
    $coreReplacements['TOKEN'] = (!$anonymized ? $_token : '');// Silently replace TOKEN by empty string
    $coreReplacements['URL'] = $_linkreplace;
    $coreReplacements['WELCOME'] = (isset($thissurvey['welcome']) ? $thissurvey['welcome'] : '');
    $coreReplacements['CLOSE_TRANSLATION'] = gT('Close');
    if(!isset($replacements['QID']))
    {
        Yii::import('application.helpers.SurveyRuntimeHelper');
        $coreReplacements = array_merge($coreReplacements, SurveyRuntimeHelper::getQuestionReplacement(null));   // so $replacements overrides core values
    }
    if (!is_null($replacements) && is_array($replacements))
    {
        $doTheseReplacements = array_merge($coreReplacements, $replacements);   // so $replacements overrides core values
    }
    else
    {
        $doTheseReplacements = $coreReplacements;
    }



    $line = Yii::app()->twigRenderer->renderTemplateFromString( $line, array('aSurveyInfo'=>$thissurvey), false);

    // Now do all of the replacements - In rare cases, need to do 3 deep recursion, that that is default
    $line = LimeExpressionManager::ProcessString($line, $questionNum, $doTheseReplacements, false, 3, 1, false, true, $bStaticReplacement);

    return $line;

}


// This function replaces field names in a text with the related values
// (e.g. for email and template functions)
function ReplaceFields ($text,$fieldsarray, $bReplaceInsertans=true, $staticReplace=true)
{

    if ($bReplaceInsertans)
    {
        $replacements = array();
        foreach ( $fieldsarray as $key => $value )
        {
            $replacements[substr($key,1,-1)] = $value;
        }
        $text = LimeExpressionManager::ProcessString($text, NULL, $replacements, false, 2, 1, false, false, $staticReplace);
    }
    else
    {
        foreach ( $fieldsarray as $key => $value )
        {
            $text=str_replace($key, $value, $text);
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
    while (strpos($line,"{PASSTHRU:") !== false)
    {
        $p1 = strpos($line,"{PASSTHRU:"); // startposition
        $p2 = $p1 + 10; // position of the first arg char
        $p3 = strpos($line,"}",$p1); // position of the last arg char

        $cmd=substr($line,$p1,$p3-$p1+1); // extract the complete passthru like "{PASSTHRU:myarg}"
        $arg=substr($line,$p2,$p3-$p2); // extract the arg to passthru (like "myarg")

        // lookup for the fitting arg
        $sValue='';
        if (isset($_SESSION['survey_'.$thissurvey['sid']]['urlparams'][$arg]))
        {
            $sValue=urlencode($_SESSION['survey_'.$thissurvey['sid']]['urlparams'][$arg]);
        }
        $line=str_replace($cmd, $sValue, $line); // replace
    }

    return $line;
}

/**
 * "Calculate" HTML for save links?
 *
 * @param string $move ?
 * @return string ?
 */
function doHtmlSaveLinks($move="")
{
    static $aSaveAllButtons=array();
    if(isset($aSaveAllButtons[$move]))
        return $aSaveAllButtons[$move];

    $surveyid=Yii::app()->getConfig('surveyID');
    $thissurvey=getsurveyinfo($surveyid);

    if($thissurvey['allowsave'] == "Y")
    {
        $submit=ls_json_encode(array(
                'loadall'=>'loadall'
            ));
        $sLoadButton=App()->twigRenderer->render("/survey/system/actionLink/saveLoad",array(
            'submit'=>$submit,
            'class'=>'ls-link-action ls-link-loadall'
        ),true);
        $submit=ls_json_encode(array(
                'saveall'=>'saveall'
            ));
        $sSaveButton=App()->twigRenderer->render("/survey/system/actionLink/saveSave",array(
            'submit'=>$submit,
            'class'=>'ls-link-action ls-link-saveall'
        ),true);
    }
    else
    {
        $sLoadButton = '';
        $sSaveButton = '';
    }


    // Fill some test here, more clear ....
    $bTokenanswerspersistence=$thissurvey['tokenanswerspersistence'] == 'Y' && tableExists('tokens_'.$surveyid);
    $bAlreadySaved=isset($_SESSION['survey_'.$surveyid]['scid']);
    $iSessionStep=(isset($_SESSION['survey_'.$surveyid]['step'])? $_SESSION['survey_'.$surveyid]['step'] : false );
    $iSessionMaxStep=(isset($_SESSION['survey_'.$surveyid]['maxstep'])? $_SESSION['survey_'.$surveyid]['maxstep'] : false );

    $sSaveAllButtons="";
    // Find out if the user has any saved data
    if ($thissurvey['format'] == 'A')
    {
        if ( !$bTokenanswerspersistence && !$bAlreadySaved )
        {
            $sSaveAllButtons .= $sLoadButton;
        }
        $sSaveAllButtons .= $sSaveButton;
    }
    elseif (!$iSessionStep) //Welcome page, show load (but not save)
    {
        if (!$bTokenanswerspersistence && !$bAlreadySaved )
        {
            $sSaveAllButtons .= $sLoadButton;
        }
        if($thissurvey['showwelcome']=="N")
        {
            $sSaveAllButtons .= $sSaveButton;
        }
    }
    elseif ($iSessionMaxStep==1 && $thissurvey['showwelcome']=="N")//First page, show LOAD and SAVE
    {
        if (!$bTokenanswerspersistence && !$bAlreadySaved )
        {
            $sSaveAllButtons .= $sLoadButton;
        }
        $sSaveAllButtons .= $sSaveButton;
    }
    elseif ($move != "movelast") // Not on last page or submited survey
    {
        $sSaveAllButtons .= $sSaveButton;
    }
    $aSaveAllButtons[$move]=$sSaveAllButtons;
    return $aSaveAllButtons[$move];
}

/**
* doHtmlSaveAll return HTML part of saveall button in survey
* @param string $move :
* @return string
**/
function doHtmlSaveAll($move="")
{
    static $aSaveAllButtons=array();
    if(isset($aSaveAllButtons[$move]))
        return $aSaveAllButtons[$move];
    $surveyid=Yii::app()->getConfig('surveyID');
    $thissurvey=getsurveyinfo($surveyid);

    if($thissurvey['allowsave'] == "Y")
    {
        $sLoadButton=App()->twigRenderer->render("/survey/system/actionButton/saveLoad",array(
            'value'=>'loadall',
            'name'=>'loadall',
            'class'=>'ls-saveaction ls-loadall'
        ),true);
        $sSaveButton=App()->twigRenderer->render("/survey/system/actionButton/saveSave",array(
            'value'=>'saveall',
            'name'=>'saveall',
            'class'=>'ls-saveaction ls-saveall'
        ),true);
        App()->getClientScript()->registerScript("activateActionLink","activateActionLink();\n",CClientScript::POS_END);
    }
    else
    {
        $sLoadButton = '';
        $sSaveButton = '';
    }

    // Fill some test here, more clear ....
    $bTokenanswerspersistence=$thissurvey['tokenanswerspersistence'] == 'Y' && tableExists('tokens_'.$surveyid);
    $bAlreadySaved=isset($_SESSION['survey_'.$surveyid]['scid']);
    $iSessionStep=(isset($_SESSION['survey_'.$surveyid]['step'])? $_SESSION['survey_'.$surveyid]['step'] : false );
    $iSessionMaxStep=(isset($_SESSION['survey_'.$surveyid]['maxstep'])? $_SESSION['survey_'.$surveyid]['maxstep'] : false );

    $sSaveAllButtons="";
    // Find out if the user has any saved data
    if ($thissurvey['format'] == 'A')
    {
        if ( !$bTokenanswerspersistence && !$bAlreadySaved )
        {
            $sSaveAllButtons .= $sLoadButton;
        }
        $sSaveAllButtons .= $sSaveButton;
    }
    elseif (!$iSessionStep) //Welcome page, show load (but not save)
    {
        if (!$bTokenanswerspersistence && !$bAlreadySaved )
        {
            $sSaveAllButtons .= $sLoadButton;
        }
        if($thissurvey['showwelcome']=="N")
        {
            $sSaveAllButtons .= $sSaveButton;
        }
    }
    elseif ($iSessionMaxStep==1 && $thissurvey['showwelcome']=="N")//First page, show LOAD and SAVE
    {
        if (!$bTokenanswerspersistence && !$bAlreadySaved )
        {
            $sSaveAllButtons .= $sLoadButton;
        }
        $sSaveAllButtons .= $sSaveButton;
    }
    elseif ($move != "movelast") // Not on last page or submited survey
    {
        $sSaveAllButtons .= $sSaveButton;
    }
    $aSaveAllButtons[$move]=$sSaveAllButtons;
    return $aSaveAllButtons[$move];
}

/**
 * ClearALl link and button
 *
 * @return array
 */
function doHtmlClearAll(){
    /* one of the reason of seaparation : call each tim we use templatereplace */
    static $aClearAll=array();
    if(empty($aClearAll)){
        $aClearAll['button']=App()->twigRenderer->render("/survey/system/actionButton/clearAll",array(
            'value'=>'clearall',
            'name'=>'move',
            'class'=>'ls-clearaction ls-clearall',
            'confirmedby'=>'confirm-clearall',
            'confirmvalue'=>'confirm',
            ),true);
        $submit=ls_json_encode(array(
                'clearall'=>'clearall'
            ));
        $confirm=ls_json_encode(array(
                'confirm-clearall'=>'confirm'
            ));
        $aClearAll['link'] = App()->twigRenderer->render("/survey/system/actionLink/clearAll",array(
            'class'=>'ls-link-action ls-link-clearall',
            'submit'=>$submit,
            'confirm'=>$confirm,
        ),true);
        // To replace javascript confirm : https://ethaizone.github.io/Bootstrap-Confirmation/ or http://bootboxjs.com/documentation.html#bb-confirm-dialog or https://nakupanda.github.io/bootstrap3-dialog/ or ....
        /* Don't do it in core actually, but put some language*/
        App()->getClientScript()->registerScript("activateConfirmLanguage","$.extend(LSvar.lang,".ls_json_encode(array('yes'=>gT("Yes"),'no'=>gT("No"))).")",CClientScript::POS_BEGIN);
        App()->getClientScript()->registerScript("activateActionLink","activateActionLink();\n",CClientScript::POS_END);
        App()->getClientScript()->registerScript("activateConfirmButton","activateConfirmButton();\n",CClientScript::POS_END);
    }
    return $aClearAll;
}
