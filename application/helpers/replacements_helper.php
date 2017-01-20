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
* @param mixed $line Text to search in
* @param mixed $replacements Array of replacements:  Array( <stringtosearch>=><stringtoreplacewith>
* @param boolean $anonymized Determines if token data is being used or just replaced with blanks
* @param questionNum - needed to support dynamic JavaScript-based tailoring within questions
* @param bStaticReplacement - Default off, forces non-dynamic replacements without <SPAN> tags (e.g. for the Completed page)
* @return string  Text with replaced strings
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
        'privacy',
        's_lang',
        'saved_id',
        'showgroupinfo',
        'showqnumcode',
        'showxquestions',
        'sitelogo',
        'surveylist',
        'templatedir',
        'thissurvey',
        'token',
        'totalBoilerplatequestions',
        'totalquestions',
        'questionindex',
        'questionindexmenu',
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
    if (!isset($saved_id) && isset(Yii::app()->session['survey_'.$_surveyid]['srid'])) { $saved_id=Yii::app()->session['survey_'.$_surveyid]['srid'];}


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

            $aOtherFiles = $oTemplate->otherFiles;

            //var_dump($aCssFiles);var_dump($aJsFiles);die();

            /* RTL CSS & JS */
            if (getLanguageRTL(App()->language))
            {
                $aCssFiles = (array) $oTemplate->config->files->rtl->css->filename;
                $aJsFiles  = (array) $oTemplate->config->files->rtl->js->filename;

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
            }
            else
            {
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
    if( isset($oTemplate->config->engine->cssframework) && $oTemplate->config->engine->cssframework)
    {
        $aCssFramework = (array) $oTemplate->config->engine->cssframework;
        if( ! empty($aCssFramework) )
        {
            $surveyformat .= " ".$oTemplate->config->engine->cssframework."-engine ";
        }

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
    if (isset($surveylist) && isset($surveylist['contact']))
    {
        $surveycontact = $surveylist['contact'];
    }
    elseif (isset($surveylist) && isset($thissurvey['admin']) && $thissurvey['admin']!="")
    {
        $surveycontact=sprintf(gT("Please contact %s ( %s ) for further assistance."),$thissurvey['admin'],$thissurvey['adminemail']);
    }
    else
    {
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

    $_submitbutton = "<input class='submit btn btn-default' type='submit' value=' " . gT("Submit") . " ' name='move2' onclick=\"javascript:document.limesurvey.move.value = 'movesubmit';\" />";

    if (isset($thissurvey['surveyls_url']) and $thissurvey['surveyls_url'] != "")
    {
        if (trim($thissurvey['surveyls_urldescription']) != '')
        {
            $_linkreplace = "<a href='{$thissurvey['surveyls_url']}'>{$thissurvey['surveyls_urldescription']}</a>";
        }
        else
        {
            $_linkreplace = "<a href='{$thissurvey['surveyls_url']}'>{$thissurvey['surveyls_url']}</a>";
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
        $_clearall=CHtml::htmlButton(gT("Exit and clear survey"),array('type'=>'submit','id'=>"clearall",'value'=>'clearall','name'=>'clearall','class'=>'clearall button  btn btn-default btn-lg  col-xs-4 hidden','data-confirmedby'=>'confirm-clearall','title'=>gT("This action need confirmation.")));
        $_clearall.=CHtml::checkBox("confirm-clearall",false,array('id'=>'confirm-clearall','value'=>'confirm','class'=>'hide jshide  btn btn-default btn-lg  col-xs-4'));
        $_clearall.=CHtml::label(gT("Are you sure you want to clear all your responses?"),'confirm-clearall',array('class'=>'hide jshide  btn btn-default btn-lg  col-xs-4'));

        $_clearalllinks = '<li><a href="#" id="clearallbtnlink">'.gT("Exit and clear survey").'</a></li>';
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

    if (isset($thissurvey['allowprev']) && $thissurvey['allowprev'] == "N")
    {
        $_strreview = "";
    }
    else
    {
        $_strreview = gT("If you want to check any of the answers you have made, and/or change them, you can do that now by clicking on the [<< prev] button and browsing through your responses.");
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
        $_restart = "<a href='{$restarturl}'>".gT("Restart this Survey")."</a>";
    }
    else
    {
        $_restart = "";
    }

    if (isset($thissurvey['anonymized']) && $thissurvey['anonymized'] == 'Y')
    {
        $_savealert = gT("To remain anonymous please use a pseudonym as your username, also an email address is not required.");
    }
    else
    {
        $_savealert = "";
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
        $_return_to_survey = "<a href='{$returnlink}'>".gT("Return to survey")."</a>";
    }
    else
    {
        $_return_to_survey = "";
    }

    // Save Form
    $_saveform = "
        <div class='save-survey-form form-horizontal'>
            <div class='form-group save-survey-row save-survey-name'>
                <label class='control-label col-sm-3 save-survey-label label-cell' for='savename'>" . gT("Name:") . "</label>
                <div class='col-sm-7 save-survey-input input-cell'>
                    <input class='form-control' type='text' name='savename' id='savename' value='" . (isset($_POST['savename']) ? HTMLEscape(autoUnescape($_POST['savename'])) : '') . "' />
                    </div>
                </div>
            <div class='form-group save-survey-row save-survey-password-1'>
                <label class='control-label col-sm-3 save-survey-label label-cell' for='savepass'>" . gT("Password:") . "</label>
                <div class='col-sm-7 save-survey-input input-cell'>
                    <input class='form-control' type='password' id='savepass' name='savepass' value='" . (isset($_POST['savepass']) ? HTMLEscape(autoUnescape($_POST['savepass'])) : '')
    . "' /></div></div>\n"

    . " <div class='form-group save-survey-row save-survey-password-2'>
            <label class='control-label col-sm-3 save-survey-label label-cell' for='savepass2'>" . gT("Repeat password:") . "</label>
            <div class='col-sm-7 save-survey-input input-cell'>
                <input class='form-control' type='password' id='savepass2' name='savepass2' value='" . (isset($_POST['savepass2']) ? HTMLEscape(autoUnescape($_POST['savepass2'])) : '')

    . "' /></div></div>\n"

    . " <div class='form-group save-survey-row save-survey-email'>
            <label class='col-sm-3 control-label save-survey-label label-cell' for='saveemail'>" . gT("Your email address:") . "</label>
            <div class='col-sm-7 save-survey-input input-cell'>
                <input class='form-control' type='text' id='saveemail' name='saveemail' value='" . (isset($_POST['saveemail']) ? HTMLEscape(autoUnescape($_POST['saveemail'])) : '')

    . "' /></div></div>\n";

    if ( isset($thissurvey['usecaptcha']) && function_exists("ImageCreate") && isCaptchaEnabled('saveandloadscreen', $thissurvey['usecaptcha']))
    {
        $_saveform .="
            <div class='form-group save-survey-row save-survey-captcha'>
                <label class='control-label col-sm-3 save-survey-label label-cell' for='loadsecurity'>" . gT("Security question:") . "</label>
                <div class='col-sm-2 captcha-image'>
                    <img alt='' src='".Yii::app()->getController()->createUrl('/verification/image/sid/'.((isset($surveyid)) ? $surveyid : ''))."' />
                </div>
                <div class='col-sm-3 save-survey-input input-cell'>
                    <div class='captcha-table'>
                        <input class='form-control' type='text' size='5' maxlength='3' id='loadsecurity' name='loadsecurity' value='' />
                    </div>
                </div>
            </div>\n";
    }
    $_saveform .= "
        <div class='form-group save-survey-row save-survey-submit'>
            <!-- Needed?
            <td class='save-survey-label label-cell'>
                <label class='hide jshide' for='savebutton'>" . gT("Save Now") . "</label>
            </td>
            -->
            <div class='form-group save-survey-input input-cell'>
                <div class='col-sm-12'>
                    <input class='btn btn-default' type='submit' id='savebutton' name='savesubmit' value='" . gT("Save Now") . "' />
                </div>
            </div>
        </div>\n"
    . "</div>
    ";
    // End save form


    // Load Form
    $_loadform = "
        <div class='load-survey-form form-horizontal'>
            <div class='form-group load-survey-row load-survey-name'>
                <label class='control-label col-sm-3 load-survey-label label-cell' for='loadname'>" . gT("Saved name:") . "</label>
                <div class='col-sm-7 load-survey-input input-cell'>
                    <input class='form-control' type='text' id='loadname' name='loadname' value='' />
                </div>
            </div>
            <div class='form-group load-survey-row load-survey-password'>
                <label class='control-label col-sm-3 load-survey-label label-cell' for='loadpass'>" . gT("Password:") . "</label>
                <div class='col-sm-7 load-survey-input input-cell'>
                    <input class='form-control' type='password' id='loadpass' name='loadpass' value='' />
                </div>
            </div>
    ";

    if (isset($thissurvey['usecaptcha']) && function_exists("ImageCreate") && isCaptchaEnabled('saveandloadscreen', $thissurvey['usecaptcha']))
    {
        $_loadform .='<div class="col-sm-12 form-group">
                <label class="col-md-4 col-sm-12 control-label">
                    <p class="col-sm-6 col-md-12 remove-padding">'.gT("Please enter the letters you see below:").'</p>
                    <span class="col-sm-6 col-md-12">';
        $_loadform .=Yii::app()->getController()->widget('CCaptcha',array(
                    'buttonOptions'=>array('class'=> 'btn btn-xs btn-info'),
                    'buttonType' => 'button',
                    'buttonLabel' => gt('Reload image')
                ),true);
        $_loadform .='</span>
                </label>
                <div class="col-sm-6">
                    <div>&nbsp;</div>'
                    . CHtml::textField('loadsecurity', '', array(
                        'id' => 'captchafield',
                        'class' => 'text input-sm form-control ',
                        'required' => 'required'
                    )).'
                </div>
            </div><br/>';

    }

    $_loadform .="
            <div class='load-survey-row load-survey-submit'>
                <!-- Needed?
                    <td class='load-survey-label label-cell'>
                        <label class='hide jshide' for='loadbutton'>" . gT("Load now") . "</label>
                    </td>
                -->
                <div class='form-group col-sm-12 load-survey-input input-cell'>
                    <input type='submit' id='loadbutton' class='btn btn-default' value='" . gT("Load now") . "' />
                </div>
            </div>
        </div>
    ";

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
    $coreReplacements['CHECKJAVASCRIPT'] = "<noscript role='alert' id='checkjavascript'><p class='alert alert-danger warningjs'>".gT("Caution: JavaScript execution is disabled in your browser. You may not be able to answer all questions in this survey. Please, verify your browser parameters.")."</p></noscript>";
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
    $coreReplacements['LOADERROR'] = isset($errormsg) ? $errormsg : ''; // global
    $coreReplacements['LOADFORM'] = $_loadform;
    $coreReplacements['LOADHEADING'] = gT("Load a previously saved survey");
    $coreReplacements['LOADMESSAGE'] = gT("You can load a survey that you have previously saved from this screen.")."<br />".gT("Type in the 'name' you used to save the survey, and the password.")."<br />";
    $coreReplacements['NAVIGATOR'] = isset($navigator) ? $navigator : '';    // global
    $coreReplacements['MOVEPREVBUTTON'] = isset($moveprevbutton) ? $moveprevbutton : '';    // global
    $coreReplacements['MOVENEXTBUTTON'] = isset($movenextbutton) ? $movenextbutton : '';    // global
    $coreReplacements['NOSURVEYID'] = (isset($surveylist))?$surveylist['nosid']:'';
    $coreReplacements['NUMBEROFQUESTIONS'] = $_totalquestionsAsked;
    $coreReplacements['PERCENTCOMPLETE'] = isset($percentcomplete) ? $percentcomplete : '';    // global
    $coreReplacements['PRIVACY'] = isset($privacy) ? $privacy : '';    // global
    $coreReplacements['PRIVACYMESSAGE'] = "<span class='privacy-title'>".gT("A note on privacy")."</span><span class='privacy-body'><br />".gT("This survey is anonymous.")."<br />".gT("The record of your survey responses does not contain any identifying information about you, unless a specific survey question explicitly asked for it.").' '.gT("If you used an identifying token to access this survey, please rest assured that this token will not be stored together with your responses. It is managed in a separate database and will only be updated to indicate whether you did (or did not) complete this survey. There is no way of matching identification tokens with survey responses.").'</span>';
    $coreReplacements['QUESTION_INDEX']=isset($questionindex) ? $questionindex: '';
    $coreReplacements['QUESTION_INDEX_MENU']=isset($questionindexmenu) ? $questionindexmenu: '';
    $coreReplacements['RESTART'] = $_restart;
    $coreReplacements['RETURNTOSURVEY'] = $_return_to_survey;
    $coreReplacements['SAVE_LINKS'] = $_savelinks;
    $coreReplacements['SAVE'] = $_saveall;
    $coreReplacements['SAVEALERT'] = $_savealert;
    $coreReplacements['SAVEDID'] = isset($saved_id) ? $saved_id : '';   // global
    $coreReplacements['SAVEERROR'] = isset($errormsg) ? $errormsg : ''; // global - same as LOADERROR
    $coreReplacements['SAVEFORM'] = $_saveform;
    $coreReplacements['SAVEHEADING'] = gT("Save your unfinished survey");
    $coreReplacements['SAVEMESSAGE'] = gT("Enter a name and password for this survey and click save below.")."<br />\n".gT("Your survey will be saved using that name and password, and can be completed later by logging in with the same name and password.")."<br /><br />\n<span class='emailoptional'>".gT("If you give an email address, an email containing the details will be sent to you.")."</span><br /><br />\n".gT("After having clicked the save button you can either close this browser window or continue filling out the survey.");
    $coreReplacements['SID'] = Yii::app()->getConfig('surveyID','');// Allways use surveyID from config
    $coreReplacements['SITENAME'] = Yii::app()->getConfig('sitename');
    $coreReplacements['SITELOGO'] = $sitelogo;
    $coreReplacements['SUBMITBUTTON'] = $_submitbutton;
    $coreReplacements['SUBMITCOMPLETE'] = "<strong>".gT("Thank you!")."<br /><br />".gT("You have completed answering the questions in this survey.")."</strong><br /><br />".gT("Click on 'Submit' now to complete the process and save your answers.");
    $coreReplacements['SUBMITREVIEW'] = $_strreview;
    $coreReplacements['SURVEYCONTACT'] = $surveycontact;
    $coreReplacements['SURVEYDESCRIPTION'] = (isset($thissurvey['description']) ? $thissurvey['description'] : '');
    $coreReplacements['SURVEYFORMAT'] = isset($surveyformat) ? $surveyformat : '';  // global
    $coreReplacements['SURVEYLANGUAGE'] = App()->language;
    $coreReplacements['SURVEYLIST'] = (isset($surveylist))?$surveylist['list']:'';
    $coreReplacements['SURVEYLISTHEADING'] =  (isset($surveylist))?$surveylist['listheading']:'';
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

    $aHtmlOptionsLoadall['disabled']='';
    $aHtmlOptionsSaveall['disabled']='';

    if($thissurvey['allowsave'] == "Y")
    {
        $sLoadButton = '<li><a href="#" id="loadallbtnlink" >'.gT("Load unfinished survey").'</a></li>';
        $sSaveButton = '<li><a href="#" id="saveallbtnlink" >'.gT("Resume later").'</a></li>';
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
        $sSaveAllButtons .= '<li><a href="#" id="saveallbtnlink" '.$aHtmlOptionsSaveall['disabled'].' >'.gT("Resume later").'</a></li>';
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

    $aHtmlOptionsLoadall=array('type'=>'submit','id'=>'loadallbtn','value'=>'loadall','name'=>'loadall','class'=>"saveall btn btn-default col-xs-12 col-sm-4 submit button hidden");
    $aHtmlOptionsSaveall=array('type'=>'submit','id'=>'saveallbtn','value'=>'saveall','name'=>'saveall','class'=>"saveall btn btn-default col-xs-12 col-sm-4 submit button hidden");
    if($thissurvey['allowsave'] == "Y")
    {
        $sLoadButton=CHtml::htmlButton(gT("Load unfinished survey"),$aHtmlOptionsLoadall);
        $sSaveButton=CHtml::htmlButton(gT("Resume later"),$aHtmlOptionsSaveall);
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
        $sSaveAllButtons .= CHtml::htmlButton(gT("Resume later"),$aHtmlOptionsSaveall);
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
