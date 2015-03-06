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
function templatereplace($line, $replacements = array(), &$redata = array(), $debugSrc = 'Unspecified', $anonymized = false, $questionNum = NULL, $registerdata = array(), $bStaticReplacement = false)
{
    /*
    global $clienttoken,$token,$sitename,$move,$showxquestions,$showqnumcode,$questioncode;
    global $s_lang,$errormsg,$saved_id, $languagechanger,$captchapath,$loadname;
    */
    /*
    $allowedvars = array('surveylist', 'sitename', 'clienttoken', 'rooturl', 'thissurvey', 'imageurl', 'defaulttemplate',
    'percentcomplete', 'move', 'groupname', 'groupdescription', 'question', 'showxquestions',
    'showgroupinfo', 'showqnumcode', 'questioncode', 'answer', 'navigator', 'help', 'totalquestions',
    'surveyformat', 'completed', 'notanswered', 'privacy', 'surveyid', 'publicurl',
    'templatedir', 'token', 'assessments', 's_lang', 'errormsg', 'clang', 'saved_id', 'usertemplaterootdir',
    'languagechanger', 'printoutput', 'captchapath', 'loadname');
    */
    $allowedvars = array(
    'answer',
    'assessments',
    'captchapath',
    'clienttoken',
    'completed',
    'errormsg',
    'groupdescription',
    'groupname',
    'help',
    'imageurl',
    'languagechanger',
    'loadname',
    'move',
    'navigator',
    'percentcomplete',
    'privacy',
    'question',
    's_lang',
    'saved_id',
    'showgroupinfo',
    'showqnumcode',
    'showxquestions',
    'sitename',
    'surveylist',
    'templatedir',
    'thissurvey',
    'token',
    'totalBoilerplatequestions',
    'totalquestions',
    );

    $varsPassed = array();

    foreach($allowedvars as $var)
    {
        if(isset($redata[$var])) {
            $$var = $redata[$var];
            $varsPassed[] = $var;
        }
    }
    //    if (count($varsPassed) > 0) {
    //        log_message('debug', 'templatereplace() called from ' . $debugSrc . ' contains: ' . implode(', ', $varsPassed));
    //    }
    //    if (isset($redata['question'])) {
    //        LimeExpressionManager::ShowStackTrace('has QID and/or SGA',$allowedvars);
    //    }
    //    extract($redata);   // creates variables for each of the keys in the array

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
    if (!isset($sitename)) { $sitename=Yii::app()->getConfig('sitename'); }
    if (!isset($saved_id) && isset(Yii::app()->session['survey_'.$_surveyid]['srid'])) { $saved_id=Yii::app()->session['survey_'.$_surveyid]['srid'];}
    $clang = Yii::app()->lang;

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
    if(stripos ($line,"{TEMPLATECSS}"))
    {
        if (file_exists($templatedir .DIRECTORY_SEPARATOR.'jquery-ui-custom.css'))
        {
			Yii::app()->getClientScript()->registerCssFile("{$templateurl}jquery-ui-custom.css");
        }
        elseif(file_exists($templatedir.DIRECTORY_SEPARATOR.'jquery-ui.css'))
        {
			Yii::app()->getClientScript()->registerCssFile("{$templateurl}jquery-ui.css");
        }
        else
        {
			Yii::app()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl')."jquery-ui.css");
        }

		Yii::app()->getClientScript()->registerCssFile("{$templateurl}template.css");
		if (getLanguageRTL($clang->langcode))
        {
            Yii::app()->getClientScript()->registerCssFile("{$templateurl}template-rtl.css");
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
        $surveycontact=sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$thissurvey['admin'],$thissurvey['adminemail']);
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

    if (isset($question) && is_array($question))
    {
        $question['sgq'] = (isset($question['sgq']) ? $question['sgq'] : '');
        if($question['sgq'])
            list($question['sid'],$question['gid'],$question['qid'])=explode("X",$question['sgq']);
        else
            list($question['sid'],$question['gid'],$question['qid'])=array('','','');
        $question['aid']= (isset($question['aid']) ? $question['aid'] : '');
        /**
         * This event fires before each question is rendered. 
         * Currenty 3 parameters are set:
         * @param string text The question text
         * @param string class The class for div around the whole question
         * @param string help The help text
         */
        $event = new PluginEvent('beforeQuestionRender');
        $event->set('surveyId', $question['sid']);// or $thissurvey['sid']
        $event->set('text', $question['text']);
        $event->set('class', $question['class']);
        $event->set('help', $question['help']);
        $event->set('type', $question['type']);
        $event->set('code', $question['code']);
        $event->set('qid', $question['qid']);
        $event->set('gid', $question['gid']);

        $event->set('answers',isset($answer) ? $answer : null);
        $event->set('questionhelp',isset($help) ? $help : null);

        App()->getPluginManager()->dispatchEvent($event);
        $question['text'] = $event->get('text');
        $question['class'] = $event->get('class');
        $question['help'] = $event->get('help');
        $question['mandatory'] = $event->get('mandatory',$question['mandatory']);
        $question['answers'] = $event->get('answers');
        $question['questionhelp'] = $event->get('questionhelp');

        // answer part ?
        // $answer is set with answer part
        $_question = $question['all'];
        $_question_text = $question['text'];
        $_question_help = $question['help'];
        $_question_mandatory = $question['mandatory'];
        $_question_man_message = $question['man_message'];
        $_question_valid_message = $question['valid_message'];
        $_question_file_valid_message = $question['file_valid_message'];
        $_question_essentials = $question['essentials'];
        $_getQuestionClass = $question['class'];
        $_question_man_class = $question['man_class'];
        $_question_input_error_class = $question['input_error_class'];
        $_question_number = $question['number'];
        $_question_code = $question['code'];
        $_question_type = $question['type'];
    }
    else
    {
        $_question = isset($question) ? $question : '';
        $_question_text = '';
        $_question_help = '';
        $_question_mandatory = '';
        $_question_man_message = '';
        $_question_valid_message = '';
        $_question_file_valid_message = '';
        $_question_essentials = '';
        $_getQuestionClass = '';
        $_question_man_class = '';
        $_question_input_error_class = '';
        $_question_number = '';
        $_question_code = '';
        $_question_type = '';
        $question = array_fill_keys(array('sid','gid','qid','aid','sgq'), '');
    };

    if ($_question_type == '*')
    {
        $_question_text = '<div class="em_equation">' .$_question_text. '</div>';
    }

    if (!(
    $showqnumcode == 'both' ||
    $showqnumcode == 'number' ||
    ($showqnumcode == 'choose' && !isset($thissurvey['showqnumcode'])) ||
    ($showqnumcode == 'choose' && $thissurvey['showqnumcode'] == 'B') ||
    ($showqnumcode == 'choose' && $thissurvey['showqnumcode'] == 'N')
    ))
    {
        $_question_number = '';
    };
    if (!(
    $showqnumcode == 'both' ||
    $showqnumcode == 'code' ||
    ($showqnumcode == 'choose' && !isset($thissurvey['showqnumcode'])) ||
    ($showqnumcode == 'choose' && $thissurvey['showqnumcode'] == 'B') ||
    ($showqnumcode == 'choose' && $thissurvey['showqnumcode'] == 'C')
    ))
    {
        $_question_code = '';
    }

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
            $_therearexquestions = $clang->gT("There are no questions in this survey"); // Singular
        }
        elseif ($_totalquestionsAsked == 1)
        {
            $_therearexquestions = $clang->gT("There is 1 question in this survey"); //Singular
        }
        else
        {
            $_therearexquestions = $clang->gT("There are {NUMBEROFQUESTIONS} questions in this survey.");    //Note this line MUST be before {NUMBEROFQUESTIONS}
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

    $_submitbutton = "<input class='submit' type='submit' value=' " . $clang->gT("Submit") . " ' name='move2' onclick=\"javascript:document.limesurvey.move.value = 'movesubmit';\" />";

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
        $_clearall=CHtml::htmlButton($clang->gT("Exit and clear survey"),array('type'=>'submit','id'=>"clearall",'value'=>'clearall','name'=>'clearall','class'=>'clearall button','data-confirmedby'=>'confirm-clearall','title'=>$clang->gT("This action need confirmation.")));
        $_clearall.=CHtml::checkBox("confirm-clearall",false,array('id'=>'confirm-clearall','value'=>'confirm','class'=>'hide jshide'));
        $_clearall.=CHtml::label($clang->gT("Are you sure you want to clear all your responses?"),'confirm-clearall',array('class'=>'hide jshide'));
    }
    else
    {
        $_clearall = "";
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
    }
    else
    {
        $_saveall = "";
    }
    $help= isset($question['questionhelp']) ? $question['questionhelp'] : isset($help) ? $help : "";
    if (flattenText($help, true,true) != '')
    {
        if (!isset($helpicon))
        {
            if (file_exists($templatedir . '/help.gif'))
            {
                $helpicon = $templateurl . 'help.gif';
            }
            elseif (file_exists($templatedir . '/help.png'))
            {
                $helpicon = $templateurl . 'help.png';
            }
            else
            {
                $helpicon=Yii::app()->getConfig('imageurl')."/help.gif";
            }
        }
        $_questionhelp =  "<img src='{$helpicon}' alt='Help' align='left' />".$help;
    }
    else
    {
        $_questionhelp = $help;
    }

    if (isset($thissurvey['allowprev']) && $thissurvey['allowprev'] == "N")
    {
        $_strreview = "";
    }
    else
    {
        $_strreview = $clang->gT("If you want to check any of the answers you have made, and/or change them, you can do that now by clicking on the [<< prev] button and browsing through your responses.");
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
        $_restart = "<a href='{$restarturl}'>".$clang->gT("Restart this Survey")."</a>";
    }
    else
    {
        $_restart = "";
    }

    if (isset($thissurvey['anonymized']) && $thissurvey['anonymized'] == 'Y')
    {
        $_savealert = $clang->gT("To remain anonymous please use a pseudonym as your username, also an email address is not required.");
    }
    else
    {
        $_savealert = "";
    }

    if (isset($surveyid))
    {
        if($_token)
        {
            $returnlink=Yii::app()->getController()->createUrl("survey/index/sid/{$surveyid}",array('token'=>sanitize_token($_token)));
        }
        else
        {
            $returnlink=Yii::app()->getController()->createUrl("survey/index/sid/{$surveyid}");
        }
        $_return_to_survey = "<a href='{$returnlink}'>".$clang->gT("Return to survey")."</a>";
    }
    else
    {
        $_return_to_survey = "";
    }

    // Save Form
    $_saveform = "<table class='save-survey-form'><tr class='save-survey-row save-survey-name'><td class='save-survey-label label-cell' align='right'><label for='savename'>" . $clang->gT("Name") . "</label>:</td><td class='save-survey-input input-cell'><input type='text' name='savename' id='savename' value='";
    if (isset($_POST['savename']))
    {
        $_saveform .= HTMLEscape(autoUnescape($_POST['savename']));
    }
    $_saveform .= "' /></td></tr>\n"
    . "<tr class='save-survey-row save-survey-password-1'><td class='save-survey-label label-cell' align='right'><label for='savepass'>" . $clang->gT("Password") . "</label>:</td><td class='save-survey-input input-cell'><input type='password' id='savepass' name='savepass' value='";
    if (isset($_POST['savepass']))
    {
        $_saveform .= HTMLEscape(autoUnescape($_POST['savepass']));
    }
    $_saveform .= "' /></td></tr>\n"
    . "<tr class='save-survey-row save-survey-password-2'><td class='save-survey-label label-cell' align='right'><label for='savepass2'>" . $clang->gT("Repeat password") . "</label>:</td><td class='save-survey-input input-cell'><input type='password' id='savepass2' name='savepass2' value='";
    if (isset($_POST['savepass2']))
    {
        $_saveform .= HTMLEscape(autoUnescape($_POST['savepass2']));
    }
    $_saveform .= "' /></td></tr>\n"
    . "<tr class='save-survey-row save-survey-email'><td class='save-survey-label label-cell' align='right'><label for='saveemail'>" . $clang->gT("Your email address") . "</label>:</td><td class='save-survey-input input-cell'><input type='text' id='saveemail' name='saveemail' value='";
    if (isset($_POST['saveemail']))
    {
        $_saveform .= HTMLEscape(autoUnescape($_POST['saveemail']));
    }
    $_saveform .= "' /></td></tr>\n";
    if ( isset($thissurvey['usecaptcha']) && function_exists("ImageCreate") && isCaptchaEnabled('saveandloadscreen', $thissurvey['usecaptcha']))
    {                                                                                                                                                                                                     
        $_saveform .="<tr class='save-survey-row save-survey-captcha'><td class='save-survey-label label-cell' align='right'><label for='loadsecurity'>" . $clang->gT("Security question") . "</label>:</td><td class='save-survey-input input-cell'><table class='captcha-table'><tr><td class='captcha-image' valign='middle'><img alt='' src='".Yii::app()->getController()->createUrl('/verification/image/sid/'.((isset($surveyid)) ? $surveyid : ''))."' /></td><td class='captcha-input' valign='middle' style='text-align:left'><input type='text' size='5' maxlength='3' id='loadsecurity' name='loadsecurity' value='' /></td></tr></table></td></tr>\n";
    }
    $_saveform .= "<tr><td align='right'></td><td></td></tr>\n"
    . "<tr class='save-survey-row save-survey-submit'><td class='save-survey-label label-cell'><label class='hide jshide' for='savebutton'>" . $clang->gT("Save Now") . "</label></td><td class='save-survey-input input-cell'><input type='submit' id='savebutton' name='savesubmit' class='button' value='" . $clang->gT("Save Now") . "' /></td></tr>\n"
    . "</table>";

    // Load Form
    $_loadform = "<table class='load-survey-form'><tr class='load-survey-row load-survey-name'><td class='load-survey-label label-cell' align='right'><label for='loadname'>" . $clang->gT("Saved name") . "</label>:</td><td class='load-survey-input input-cell'><input type='text' id='loadname' name='loadname' value='";
    if (isset($loadname))
    {
        $_loadform .= HTMLEscape(autoUnescape($loadname));
    }
    $_loadform .= "' /></td></tr>\n"
    . "<tr class='load-survey-row load-survey-password'><td class='load-survey-label label-cell' align='right'><label for='loadpass'>" . $clang->gT("Password") . "</label>:</td><td class='load-survey-input input-cell'><input type='password' id='loadpass' name='loadpass' value='";
    if (isset($loadpass))
    {
        $_loadform .= HTMLEscape(autoUnescape($loadpass));
    }
    $_loadform .= "' /></td></tr>\n";
    if (isset($thissurvey['usecaptcha']) && function_exists("ImageCreate") && isCaptchaEnabled('saveandloadscreen', $thissurvey['usecaptcha']))
    {
        $_loadform .="<tr class='load-survey-row load-survey-captcha'><td class='load-survey-label label-cell' align='right'><label for='loadsecurity'>" . $clang->gT("Security question") . "</label>:</td><td class='load-survey-input input-cell'><table class='captcha-table'><tr><td class='captcha-image' valign='middle'><img src='".Yii::app()->getController()->createUrl('/verification/image/sid/'.((isset($surveyid)) ? $surveyid : ''))."' alt='' /></td><td class='captcha-input' valign='middle'><input type='text' size='5' maxlength='3' id='loadsecurity' name='loadsecurity' value='' alt=''/></td></tr></table></td></tr>\n";
    }
    $_loadform .="<tr class='load-survey-row load-survey-submit'><td class='load-survey-label label-cell'><label class='hide jshide' for='loadbutton'>" . $clang->gT("Load now") . "</label></td><td class='load-survey-input input-cell'><input type='submit' id='loadbutton' class='button' value='" . $clang->gT("Load now") . "' /></td></tr></table>\n";

    // Registration Form
    if (isset($surveyid) || (isset($registerdata) && $debugSrc == 'register.php'))
    {
        if (isset($surveyid))
            $tokensid = $surveyid;
        else
            $tokensid = $registerdata['sid'];

        $_registerform = CHtml::form(array("/register/index/surveyid/{$tokensid}"), 'post');

        if (!isset($_REQUEST['lang']))
        {
            $_reglang = Survey::model()->findByPk($tokensid)->language;
        }
        else
        {
            $_reglang = returnGlobal('lang');
        }

        $_registerform .= "\n<input type='hidden' name='lang' value='" . $_reglang . "' />\n";
        $_registerform .= "<input type='hidden' name='sid' value='$tokensid' id='sid' />\n";

        $_registerform.="<table class='register register-form-table' summary='Registrationform'>\n"
        . "<tr class='register-form-row register-form-fname'><td class='register-form-label label-cell' align='right'><label for='register_firstname'>"
        . $clang->gT("First name") . "</label>:</td>"
        . "<td class='register-form-input input-cell' align='left'><input class='text' type='text' id='register_firstname' name='register_firstname'";
        if (isset($_POST['register_firstname']))
        {
            $_registerform .= " value='" . htmlentities(returnGlobal('register_firstname'), ENT_QUOTES, 'UTF-8') . "'";
        }
        $_registerform .= " /></td></tr>"
        . "<tr class='register-form-row register-form-lname'><td class='register-form-label label-cell' align='right'><label for='register_lastname'>" . $clang->gT("Last name") . "</label>:</td>\n"
        . "<td class='register-form-input input-cell' align='left'><input class='text' type='text' id='register_lastname' name='register_lastname'";
        if (isset($_POST['register_lastname']))
        {
            $_registerform .= " value='" . htmlentities(returnGlobal('register_lastname'), ENT_QUOTES, 'UTF-8') . "'";
        }
        $_registerform .= " /></td></tr>\n"
        . "<tr class='register-form-row register-form-email'><td class='register-form-label label-cell' align='right'><label for='register_email'>" . $clang->gT("Email address") . "</label>:</td>\n"
        . "<td class='register-form-input input-cell' align='left'><input class='text' type='text' id='register_email' name='register_email'";
        if (isset($_POST['register_email']))
        {
            $_registerform .= " value='" . htmlentities(returnGlobal('register_email'), ENT_QUOTES, 'UTF-8') . "'";
        }
        $_registerform .= " /></td></tr>\n";
        foreach ($thissurvey['attributedescriptions'] as $field => $attribute)
        {
            if (empty($attribute['show_register']) || $attribute['show_register'] != 'Y')
                continue;

            $_registerform .= '
            <tr class="register-form-row register-form-attribute">
            <td class="register-form-label label-cell" align="right"><label for="register_' . $field . '">' . $thissurvey['attributecaptions'][$field] . ($attribute['mandatory'] == 'Y' ? '*' : '') . '</label>:</td>
            <td class="register-form-input input-cell" align="left"><input class="text" type="text" id="register_' . $field . '" name="register_' . $field . '" /></td>
            </tr>';
        }
        if ((count($registerdata) > 1 || isset($thissurvey['usecaptcha'])) && function_exists("ImageCreate") && isCaptchaEnabled('registrationscreen', $thissurvey['usecaptcha']))
        {
            $_registerform .="<tr class='register-form-row register-form-captcha'><td class='register-form-label label-cell' align='right'><label for='loadsecurity'>" . $clang->gT("Security Question") . "</label>:</td><td class='register-form-input input-cell'><table><tr><td valign='middle'><img src='".Yii::app()->getController()->createUrl('/verification/image/sid/'.$surveyid)."' alt='' /></td><td valign='middle'><input type='text' size='5' maxlength='3' id='loadsecurity' name='loadsecurity' value='' /></td></tr></table></td></tr>\n";
        }
        $_registerform .= "<tr class='register-form-row register-form-submit'><td class='register-form-label label-cell'><label class='hide jshide' for='registercontinue'>" . $clang->gT("Continue") . "</label></td><td class='register-form-input input-cell' align='left'><input id='registercontinue' class='submit button' type='submit' value='" . $clang->gT("Continue") . "' />"
        . "</td></tr>\n"
        . "</table>\n";

        if (count($registerdata) > 1 && $registerdata['sid'] != NULL && $debugSrc == 'register.php')
        {
            $_registerform .= "<input name='startdate' type ='hidden' value='".$registerdata['startdate']."' />";
            $_registerform .= "<input name='enddate' type ='hidden' value='".$registerdata['enddate']."' />";
        }


        $_registerform .= "</form>\n";
    }
    else
    {
        $_registerform = "";
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

    if (isset($thissurvey['googleanalyticsapikey']) && trim($thissurvey['googleanalyticsapikey']) != '')
    {
        $_googleAnalyticsAPIKey = trim($thissurvey['googleanalyticsapikey']);
    }
    else
    {
        $_googleAnalyticsAPIKey = trim(getGlobalSetting('googleanalyticsapikey'));
    }
    $_googleAnalyticsStyle = (isset($thissurvey['googleanalyticsstyle']) ? $thissurvey['googleanalyticsstyle'] : '0');
    $_googleAnalyticsJavaScript = '';

    if ($_googleAnalyticsStyle != '' && $_googleAnalyticsStyle != 0 && $_googleAnalyticsAPIKey != '')
    {
        switch ($_googleAnalyticsStyle)
        {
            case '1':
                // Default Google Tracking
                $_googleAnalyticsJavaScript = <<<EOD
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '$_googleAnalyticsAPIKey']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
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
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '$_googleAnalyticsAPIKey']);
  _gaq.push(['_trackPageview','$_trackURL']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
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
    if (isset($surveyid) && isset($_SESSION['survey_'.$surveyid]) && isset($_SESSION['survey_'.$surveyid]['register_errormsg']))
    {
        $register_errormsg=$_SESSION['survey_'.$surveyid]['register_errormsg'];
    }


    // Set the array of replacement variables here - don't include curly braces
    $coreReplacements = array();
    $coreReplacements['ACTIVE'] = (isset($thissurvey['active']) && !($thissurvey['active'] != "Y"));
    $coreReplacements['AID'] = $question['aid'];
    $coreReplacements['ANSWER'] = isset($question['answers']) ? $question['answers'] : (isset($answer) ? $answer : '');
    $coreReplacements['ANSWERSCLEARED'] = $clang->gT("Answers cleared");
    $coreReplacements['ASSESSMENTS'] = $assessmenthtml;
    $coreReplacements['ASSESSMENT_CURRENT_TOTAL'] = $_assessment_current_total;
    $coreReplacements['ASSESSMENT_HEADING'] = $clang->gT("Your assessment");
    $coreReplacements['CHECKJAVASCRIPT'] = "<noscript><span class='warningjs'>".$clang->gT("Caution: JavaScript execution is disabled in your browser. You may not be able to answer all questions in this survey. Please, verify your browser parameters.")."</span></noscript>";
    $coreReplacements['CLEARALL'] = $_clearall;
    $coreReplacements['CLOSEWINDOW']  =  "<a href='javascript:%20self.close()'>".$clang->gT("Close this window")."</a>";
    $coreReplacements['COMPLETED'] = isset($redata['completed']) ? $redata['completed'] : '';    // global
    $coreReplacements['DATESTAMP'] = $_datestamp;
    $coreReplacements['ENDTEXT'] = $_endtext;
    $coreReplacements['EXPIRY'] = $_dateoutput;
    $coreReplacements['GID'] = ($question['gid']) ? $question['gid'] : Yii::app()->getConfig('gid','');// Use the gid of the question, except if we are not in question (Randomization group name)
    $coreReplacements['GOOGLE_ANALYTICS_API_KEY'] = $_googleAnalyticsAPIKey;
    $coreReplacements['GOOGLE_ANALYTICS_JAVASCRIPT'] = $_googleAnalyticsJavaScript;
    $coreReplacements['GROUPDESCRIPTION'] = $_groupdescription;
    $coreReplacements['GROUPNAME'] = $_groupname;
    $coreReplacements['LANG'] = $clang->getlangcode();
    $coreReplacements['LANGUAGECHANGER'] = isset($languagechanger) ? $languagechanger : '';    // global
    $coreReplacements['LOADERROR'] = isset($errormsg) ? $errormsg : ''; // global
    $coreReplacements['LOADFORM'] = $_loadform;
    $coreReplacements['LOADHEADING'] = $clang->gT("Load a previously saved survey");
    $coreReplacements['LOADMESSAGE'] = $clang->gT("You can load a survey that you have previously saved from this screen.")."<br />".$clang->gT("Type in the 'name' you used to save the survey, and the password.")."<br />";
    $coreReplacements['NAVIGATOR'] = isset($navigator) ? $navigator : '';    // global
    $coreReplacements['NOSURVEYID'] = (isset($surveylist))?$surveylist['nosid']:'';
    $coreReplacements['NUMBEROFQUESTIONS'] = $_totalquestionsAsked;
    $coreReplacements['PERCENTCOMPLETE'] = isset($percentcomplete) ? $percentcomplete : '';    // global
    $coreReplacements['PRIVACY'] = isset($privacy) ? $privacy : '';    // global
    $coreReplacements['PRIVACYMESSAGE'] = "<span style='font-weight:bold; font-style: italic;'>".$clang->gT("A Note On Privacy")."</span><br />".$clang->gT("This survey is anonymous.")."<br />".$clang->gT("The record of your survey responses does not contain any identifying information about you, unless a specific survey question explicitly asked for it.").' '.$clang->gT("If you used an identifying token to access this survey, please rest assured that this token will not be stored together with your responses. It is managed in a separate database and will only be updated to indicate whether you did (or did not) complete this survey. There is no way of matching identification tokens with survey responses.");
    $coreReplacements['QID'] = $question['qid'];
    $coreReplacements['QUESTION'] = $_question;
    $coreReplacements['QUESTIONHELP'] = $_questionhelp;
    $coreReplacements['QUESTIONHELPPLAINTEXT'] = strip_tags(addslashes($help)); // global
    $coreReplacements['QUESTION_CLASS'] = $_getQuestionClass;
    $coreReplacements['QUESTION_CODE'] = $_question_code;
    $coreReplacements['QUESTION_ESSENTIALS'] = $_question_essentials;
    $coreReplacements['QUESTION_FILE_VALID_MESSAGE'] = $_question_file_valid_message;
    $coreReplacements['QUESTION_HELP'] = $_question_help;
    $coreReplacements['QUESTION_INPUT_ERROR_CLASS'] = $_question_input_error_class;
    $coreReplacements['QUESTION_MANDATORY'] = $_question_mandatory;
    $coreReplacements['QUESTION_MAN_CLASS'] = $_question_man_class;
    $coreReplacements['QUESTION_MAN_MESSAGE'] = $_question_man_message;
    $coreReplacements['QUESTION_NUMBER'] = $_question_number;
    $coreReplacements['QUESTION_TEXT'] = $_question_text;
    $coreReplacements['QUESTION_VALID_MESSAGE'] = $_question_valid_message;
    $coreReplacements['REGISTERERROR'] = isset($register_errormsg) ? $register_errormsg : '';    // global
    $coreReplacements['REGISTERFORM'] = $_registerform;
    $coreReplacements['REGISTERMESSAGE1'] = $clang->gT("You must be registered to complete this survey");
    $coreReplacements['REGISTERMESSAGE2'] = $clang->gT("You may register for this survey if you wish to take part.")."<br />\n".$clang->gT("Enter your details below, and an email containing the link to participate in this survey will be sent immediately.");
    $coreReplacements['RESTART'] = $_restart;
    $coreReplacements['RETURNTOSURVEY'] = $_return_to_survey;
    $coreReplacements['SAVE'] = $_saveall;
    $coreReplacements['SAVEALERT'] = $_savealert;
    $coreReplacements['SAVEDID'] = isset($saved_id) ? $saved_id : '';   // global
    $coreReplacements['SAVEERROR'] = isset($errormsg) ? $errormsg : ''; // global - same as LOADERROR
    $coreReplacements['SAVEFORM'] = $_saveform;
    $coreReplacements['SAVEHEADING'] = $clang->gT("Save your unfinished survey");
    $coreReplacements['SAVEMESSAGE'] = $clang->gT("Enter a name and password for this survey and click save below.")."<br />\n".$clang->gT("Your survey will be saved using that name and password, and can be completed later by logging in with the same name and password.")."<br /><br />\n".$clang->gT("If you give an email address, an email containing the details will be sent to you.")."<br /><br />\n".$clang->gT("After having clicked the save button you can either close this browser window or continue filling out the survey.");
    $coreReplacements['SGQ'] = $question['sgq'];
    $coreReplacements['SID'] = Yii::app()->getConfig('surveyID','');// Allways use surveyID from config
    $coreReplacements['SITENAME'] = isset($sitename) ? $sitename : '';  // global
    $coreReplacements['SUBMITBUTTON'] = $_submitbutton;
    $coreReplacements['SUBMITCOMPLETE'] = "<strong>".$clang->gT("Thank you!")."<br /><br />".$clang->gT("You have completed answering the questions in this survey.")."</strong><br /><br />".$clang->gT("Click on 'Submit' now to complete the process and save your answers.");
    $coreReplacements['SUBMITREVIEW'] = $_strreview;
    $coreReplacements['SURVEYCONTACT'] = $surveycontact;
    $coreReplacements['SURVEYDESCRIPTION'] = (isset($thissurvey['description']) ? $thissurvey['description'] : '');
    $coreReplacements['SURVEYFORMAT'] = isset($surveyformat) ? $surveyformat : '';  // global
    $coreReplacements['SURVEYLANGAGE'] = $clang->langcode;
    $coreReplacements['SURVEYLANGUAGE'] = $clang->langcode;
    $coreReplacements['SURVEYLIST'] = (isset($surveylist))?$surveylist['list']:'';
    $coreReplacements['SURVEYLISTHEADING'] =  (isset($surveylist))?$surveylist['listheading']:'';
    $coreReplacements['SURVEYNAME'] = (isset($thissurvey['name']) ? $thissurvey['name'] : '');
    $coreReplacements['TEMPLATECSS'] = $_templatecss;
    $coreReplacements['TEMPLATEJS'] = CHtml::tag('script', array('type' => 'text/javascript', 'src' => $templateurl . 'template.js'), '');
    $coreReplacements['TEMPLATEURL'] = $templateurl;
    $coreReplacements['THEREAREXQUESTIONS'] = $_therearexquestions;
    $coreReplacements['TOKEN'] = (!$anonymized ? $_token : '');// Silently replace TOKEN by empty string
    $coreReplacements['URL'] = $_linkreplace;
    $coreReplacements['WELCOME'] = (isset($thissurvey['welcome']) ? $thissurvey['welcome'] : '');

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
    $clang = Yii::app()->lang;
    $aHtmlOptionsLoadall=array('type'=>'submit','id'=>'loadallbtn','value'=>'loadall','name'=>'loadall','class'=>"saveall submit button");
    $aHtmlOptionsSaveall=array('type'=>'submit','id'=>'saveallbtn','value'=>'saveall','name'=>'saveall','class'=>"saveall submit button");
    if($thissurvey['active'] != "Y"){
        $aHtmlOptionsLoadall['disabled']='disabled';
        $aHtmlOptionsSaveall['disabled']='disabled';
    }
    $sLoadButton=CHtml::htmlButton($clang->gT("Load unfinished survey"),$aHtmlOptionsLoadall);
    $sSaveButton=CHtml::htmlButton($clang->gT("Resume later"),$aHtmlOptionsSaveall);
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
        $sSaveAllButtons .= CHtml::htmlButton($clang->gT("Resume later"),$aHtmlOptionsSaveall);
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

// Closing PHP tag intentionally omitted - yes, it is okay
