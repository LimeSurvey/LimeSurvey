<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
* LimeSurvey
* Copyright (C) 2007-2012 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// TODO: Why needed?
require_once(Yii::app()->basePath . '/libraries/MersenneTwister.php');

function loadanswers()
{
    Yii::trace('start', 'survey.loadanswers');
    global $surveyid;
    global $thissurvey, $thisstep;
    global $clienttoken;


    $scid=Yii::app()->request->getQuery('scid');
    if (Yii::app()->request->getParam('loadall') == "reload")
    {
        $sLoadName=Yii::app()->request->getParam('loadname');
        $sLoadPass=Yii::app()->request->getParam('loadpass');
        $oCriteria= new CDbCriteria;
        $oCriteria->join="LEFT JOIN {{saved_control}} ON t.id={{saved_control}}.srid";
        $oCriteria->condition="{{saved_control}}.sid=:sid";
        $aParams=array(':sid'=>$surveyid);
        if (isset($scid)) //Would only come from email : we don't need it ....
        {
            $oCriteria->addCondition("{{saved_control}}.scid=:scid");
            $aParams[':scid']=$scid;
        }
        $oCriteria->addCondition("{{saved_control}}.identifier=:identifier");
        $aParams[':identifier']=$sLoadName;

        if (in_array(Yii::app()->db->getDriverName(), array('mssql', 'sqlsrv', 'dblib')))
        {
            // To be validated with mssql, think it's not needed
            $oCriteria->addCondition("(CAST({{saved_control}}.access_code as varchar(64))=:md5_code OR CAST({{saved_control}}.access_code as varchar(64))=:sha256_code)");
        }
        else
        {
            $oCriteria->addCondition("({{saved_control}}.access_code=:md5_code OR {{saved_control}}.access_code=:sha256_code)");
        }
        $aParams[':md5_code']=md5($sLoadPass);
        $aParams[':sha256_code']=hash('sha256',$sLoadPass);
    }
    elseif (isset($_SESSION['survey_'.$surveyid]['srid']))
    {
        $oCriteria= new CDbCriteria;
        $oCriteria->condition="id=:id";
        $aParams=array(':id'=>$_SESSION['survey_'.$surveyid]['srid']);
    }
    else
    {
        return;
    }
    $oCriteria->params=$aParams;
    $oResponses=SurveyDynamic::model($surveyid)->find($oCriteria);
    if (!$oResponses)
    {
        return false;
    }
    else
    {
        //A match has been found. Let's load the values!
        //If this is from an email, build surveysession first
        $_SESSION['survey_'.$surveyid]['LEMtokenResume']=true;

        // If survey come from reload (GET or POST); some value need to be found on saved_control, not on survey
        if (Yii::app()->request->getParam('loadall') == "reload")
        {
            $oSavedSurvey=SavedControl::model()->find(
                "sid = :sid AND identifier = :identifier AND (access_code = :access_code OR access_code = :sha256_code)",
                array(
                    ':sid' => $surveyid,
                    ':identifier' => $sLoadName,
                    ':access_code' => md5($sLoadPass),
                    ':sha256_code' => hash('sha256',$sLoadPass)
                )
            );
            // We don't need to control if we have one, because we do the test before
            $_SESSION['survey_'.$surveyid]['scid'] = $oSavedSurvey->scid;
            $_SESSION['survey_'.$surveyid]['step'] = ($oSavedSurvey->saved_thisstep>1)?$oSavedSurvey->saved_thisstep:1;
            $thisstep=$_SESSION['survey_'.$surveyid]['step']-1;// deprecated ?
            $_SESSION['survey_'.$surveyid]['srid'] = $oSavedSurvey->srid;// Seems OK without
            $_SESSION['survey_'.$surveyid]['refurl'] = $oSavedSurvey->refurl;
        }

        // Get if survey is been answered
        $submitdate=$oResponses->submitdate;
        $aRow=$oResponses->attributes;
        foreach ($aRow as $column => $value)
        {
            if ($column == "token")
            {
                $clienttoken=$value;
                $token=$value;
            }
            elseif ($column =='lastpage' && !isset($_SESSION['survey_'.$surveyid]['step']))
            {
                if(is_null($submitdate) || $submitdate=="N")
                {
                    $_SESSION['survey_'.$surveyid]['step']=($value>1? $value:1) ;
                    $thisstep=$_SESSION['survey_'.$surveyid]['step']-1;
                }
                else
                {
                    $_SESSION['survey_'.$surveyid]['maxstep']=($value>1? $value:1) ;
                }
            }
            elseif ($column == "datestamp")
            {
                $_SESSION['survey_'.$surveyid]['datestamp']=$value;
            }
            if ($column == "startdate")
            {
                $_SESSION['survey_'.$surveyid]['startdate']=$value;
            }
            else
            {
                //Only make session variables for those in insertarray[]
                if (in_array($column, $_SESSION['survey_'.$surveyid]['insertarray']) && isset($_SESSION['survey_'.$surveyid]['fieldmap'][$column]))
                {

                    if (($_SESSION['survey_'.$surveyid]['fieldmap'][$column]['type'] == 'N' ||
                    $_SESSION['survey_'.$surveyid]['fieldmap'][$column]['type'] == 'K' ||
                    $_SESSION['survey_'.$surveyid]['fieldmap'][$column]['type'] == 'D') && $value == null)
                    {   // For type N,K,D NULL in DB is to be considered as NoAnswer in any case.
                        // We need to set the _SESSION[field] value to '' in order to evaluate conditions.
                        // This is especially important for the deletenonvalue feature,
                        // otherwise we would erase any answer with condition such as EQUALS-NO-ANSWER on such
                        // question types (NKD)
                        $_SESSION['survey_'.$surveyid][$column]='';
                    }
                    else
                    {
                        $_SESSION['survey_'.$surveyid][$column]=$value;
                    }
                    if(isset($token) && !empty($token))
                    {
                        $_SESSION['survey_'.$surveyid][$column]=$value;
                    }
                }  // if (in_array(
            }  // else
        } // foreach
        return true;
    }
}

function makegraph($currentstep, $total)
{
    global $thissurvey;


    Yii::app()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl') . 'lime-progress.css');
    $size = intval(($currentstep-1)/$total*100);

    $graph='
    <div class="progress">
        <div class="progress-bar" role="progressbar" aria-valuenow="'.$size.'" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em; width: '.$size.'%;">
            '.$size.'%
        </div>
    </div>';


    return $graph;
}

/**
* This function creates the language selector for a particular survey
*
* @param mixed $sSelectedLanguage The language in which all information is shown
*/
function makeLanguageChangerSurvey($sSelectedLanguage)
{
    $surveyid = Yii::app()->getConfig('surveyID');
    Yii::app()->loadHelper("surveytranslator");

    $aSurveyLangs = Survey::model()->findByPk($surveyid)->getAllLanguages();
    if (count($aSurveyLangs)>1) // return a dropdow only of there are more than one lanagage
    {
        $aAllLanguages=getLanguageData(true);
        $aSurveyLangs=array_intersect_key($aAllLanguages,array_flip($aSurveyLangs)); // Sort languages by their locale name
        $sClass="languagechanger";
        $sHTMLCode="";
        $sAction=Yii::app()->request->getParam('action','');// Different behaviour if preview
        $sSelected="";
        if(substr($sAction,0,7)=='preview')
        {
            $route="/survey/index/sid/{$surveyid}";
            if ($sAction=='previewgroup' && intval(Yii::app()->request->getParam('gid',0)))
            {
                $route.="/action/previewgroup/gid/".intval(Yii::app()->request->getParam('gid',0));
            }
            if ($sAction=='previewquestion' && intval(Yii::app()->request->getParam('gid',0)) && intval(Yii::app()->request->getParam('qid',0)))
            {
                $route.="/action/previewquestion/gid/".intval(Yii::app()->request->getParam('gid',0))."/qid/".intval(Yii::app()->request->getParam('qid',0));
            }
            if (!is_null(Yii::app()->request->getParam('token')))
            {
                $route.="/token/".Yii::app()->request->getParam('token');
            }
            $sClass.=" previewmode";
            // Maybe add other param (for prefilling by URL): then need a real createUrl with array
#            foreach ($aSurveyLangs as $sLangCode => $aSurveyLang)
#            {
#                $sTargetURL=Yii::app()->getController()->createUrl($route."/lang/$sLangCode");
#                $aListLang[$sTargetURL]=html_entity_decode($aSurveyLang['nativedescription'], ENT_COMPAT,'UTF-8');
#                if(App()->language==$sLangCode)
#                    $sSelected=$sTargetURL;
#            }
        }
        else
        {
            $route="/survey/index/sid/{$surveyid}";
        }
        $sTargetURL=Yii::app()->getController()->createUrl($route);
        $aListLang = array();
        foreach ($aSurveyLangs as $sLangCode => $aSurveyLang)
        {
            $aListLang[$sLangCode]=html_entity_decode($aSurveyLang['nativedescription'], ENT_COMPAT,'UTF-8');
        }
        $sSelected=App()->language;
        $sClass .= ' form-control ';

        $languageChangerDatas = array(
            'sSelected' => $sSelected ,
            'aListLang' => $aListLang ,
            'sClass'    => $sClass    ,
            'sTargetURL'=> $sTargetURL,
        );

        $sHTMLCode = Yii::app()->getController()->renderPartial('/survey/system/LanguageChanger/LanguageChanger', $languageChangerDatas, true);
        return $sHTMLCode;
    }
    else
    {
        return false;
    }

}

/**
* This function creates the language selector for the public survey index page
*
* @param mixed $sSelectedLanguage The language in which all information is shown
*/
function makeLanguageChanger($sSelectedLanguage)
{
    $aLanguages=getLanguageDataRestricted(true);// Order by native
    if(count($aLanguages)>1)
    {
#        $sHTMLCode = "<select id='languagechanger' name='languagechanger' class='languagechanger' onchange='javascript:window.location=this.value'>\n";
#        foreach(getLanguageDataRestricted(true) as $sLanguageID=>$aLanguageProperties)
#        {
#            $sLanguageUrl=Yii::app()->getController()->createUrl('survey/index',array('lang'=>$sLanguageID));
#            $sHTMLCode .= "<option value='{$sLanguageUrl}'";
#            if($sLanguageID == $sSelectedLanguage)
#            {
#                $sHTMLCode .= " selected='selected' ";
#                $sHTMLCode .= ">{$aLanguageProperties['nativedescription']}</option>\n";
#            }
#            else
#            {
#                $sHTMLCode .= ">".$aLanguageProperties['nativedescription'].' - '.$aLanguageProperties['description']."</option>\n";
#            }
#        }
#        $sHTMLCode .= "</select>\n";

        $sClass= "languagechanger form-control";
        foreach ($aLanguages as $sLangCode => $aLanguage)
            $aListLang[$sLangCode]=html_entity_decode($aLanguage['nativedescription'], ENT_COMPAT,'UTF-8').' - '.$aLanguage['description'];
        $sSelected=$sSelectedLanguage;

        $sHTMLCode= CHtml::beginForm(App()->createUrl('surveys/publiclist'),'get', array('class' => 'form-horizontal'));
        $sHTMLCode.=CHtml::label(gT("Language:"), 'lang',array('class'=>'control-label col-xs-4 col-sm-8'));
        $sHTMLCode .= "<div class='col-xs-7 col-sm-2'>";
        $sHTMLCode.= CHtml::dropDownList('lang', $sSelected,$aListLang,array('class'=>$sClass));
        $sHTMLCode .= "</div>";
        $sHTMLCode .= "<div class='col-xs-1 col-sm-2'>";
        $sHTMLCode.="<button class='changelang jshide' value='changelang' id='changelangbtn' type='submit'>".gT("Change the language")."</button>";
        $sHTMLCode .= "</div>";
        $sHTMLCode.= CHtml::endForm();
        return $sHTMLCode;
    }
    else
    {
        return false;
    }
}

/**
 * Construct flash message container
 * Used in templatereplace to replace {FLASHMESSAGE} in startpage.tstpl
 *
 * @return string
 */
function makeFlashMessage() {
    global $surveyid;
    $html = "";

    $language = Yii::app()->getLanguage();
    $originalPrefix = Yii::app()->user->getStateKeyPrefix();
    // Bug in Yii? Getting the state-key prefix changes the locale, so set the language manually after.
    Yii::app()->setLanguage($language);
    Yii::app()->user->setStateKeyPrefix('frontend');

    $mapYiiToBootstrapClass = array(
        'error' => 'danger',
        'success' => 'success',
        'notice' => 'info'
        // no warning in Yii?
    );

    foreach (Yii::app()->user->getFlashes() as $key => $message) {
        $html .= "<div class='alert alert-" . $mapYiiToBootstrapClass[$key] . " alert-dismissible flash-" . $key . "'>" . $message . "</div>\n";
    }

    Yii::app()->user->setStateKeyPrefix($originalPrefix);

    return $html;
}



/**
* checkUploadedFileValidity used in SurveyRuntimeHelper
*/
function checkUploadedFileValidity($surveyid, $move, $backok=null)
{
    global $thisstep;


    if (!isset($backok) || $backok != "Y")
    {
        $fieldmap = createFieldMap($surveyid,'full',false,false,$_SESSION['survey_'.$surveyid]['s_lang']);

        if (isset($_POST['fieldnames']) && $_POST['fieldnames']!="")
        {
            $fields = explode("|", $_POST['fieldnames']);

            foreach ($fields as $field)
            {
                if ($fieldmap[$field]['type'] == "|" && !strrpos($fieldmap[$field]['fieldname'], "_filecount"))
                {
                    $validation= getQuestionAttributeValues($fieldmap[$field]['qid']);

                    $filecount = 0;

                    $json = $_POST[$field];
                    // if name is blank, its basic, hence check
                    // else, its ajax, don't check, bypass it.

                    if ($json != "" && $json != "[]")
                    {
                        $phparray = json_decode(stripslashes($json));
                        if ($phparray[0]->size != "")
                        { // ajax
                            $filecount = count($phparray);
                        }
                        else
                        { // basic
                            for ($i = 1; $i <= $validation['max_num_of_files']; $i++)
                            {
                                if (!isset($_FILES[$field."_file_".$i]) || $_FILES[$field."_file_".$i]['name'] == '')
                                    continue;

                                $filecount++;

                                $file = $_FILES[$field."_file_".$i];

                                // File size validation
                                if ($file['size'] > $validation['max_filesize'] * 1000)
                                {
                                    $filenotvalidated = array();
                                    $filenotvalidated[$field."_file_".$i] = sprintf(gT("Sorry, the uploaded file (%s) is larger than the allowed filesize of %s KB."), $file['size'], $validation['max_filesize']);
                                    $append = true;
                                }

                                // File extension validation
                                $pathinfo = pathinfo(basename($file['name']));
                                $ext = $pathinfo['extension'];

                                $validExtensions = explode(",", $validation['allowed_filetypes']);
                                if (!(in_array($ext, $validExtensions)))
                                {
                                    if (isset($append) && $append)
                                    {
                                        $filenotvalidated[$field."_file_".$i] .= sprintf(gT("Sorry, only %s extensions are allowed!"),$validation['allowed_filetypes']);
                                        unset($append);
                                    }
                                    else
                                    {
                                        $filenotvalidated = array();
                                        $filenotvalidated[$field."_file_".$i] .= sprintf(gT("Sorry, only %s extensions are allowed!"),$validation['allowed_filetypes']);
                                    }
                                }
                            }
                        }
                    }
                    else
                        $filecount = 0;

                    if (isset($validation['min_num_of_files']) && $filecount < $validation['min_num_of_files'] && LimeExpressionManager::QuestionIsRelevant($fieldmap[$field]['qid']))
                    {
                        $filenotvalidated = array();
                        $filenotvalidated[$field] = gT("The minimum number of files has not been uploaded.");
                    }
                }
            }
        }
        if (isset($filenotvalidated))
        {
            if (isset($move) && $move == "moveprev")
                $_SESSION['survey_'.$surveyid]['step'] = $thisstep;
            if (isset($move) && $move == "movenext")
                $_SESSION['survey_'.$surveyid]['step'] = $thisstep;
            return $filenotvalidated;
        }
    }
    if (!isset($filenotvalidated))
        return false;
    else
        return $filenotvalidated;
}

/**
* Takes two single element arrays and adds second to end of first if value exists
* Why not use array_merge($array1,array_filter($array2);
*/
function addtoarray_single($array1, $array2)
{
    //
    if (is_array($array2))
    {
        foreach ($array2 as $ar)
        {
            if ($ar && $ar !== null)
            {
                $array1[]=$ar;
            }
        }
    }
    return $array1;
}

/**
* Marks a tokens as completed and sends a confirmation email to the participiant.
* If $quotaexit is set to true then the user exited the survey due to a quota
* restriction and the according token is only marked as 'Q'
*
* @param boolean $quotaexit
*/
function submittokens($quotaexit=false)
{
    $surveyid=Yii::app()->getConfig('surveyID');
    if(isset($_SESSION['survey_'.$surveyid]['s_lang']))
    {
        $thissurvey=getSurveyInfo($surveyid,$_SESSION['survey_'.$surveyid]['s_lang']);
    }
    else
    {
        $thissurvey=getSurveyInfo($surveyid);
    }
    $clienttoken = $_SESSION['survey_'.$surveyid]['token'];

    $sitename = Yii::app()->getConfig("sitename");
    $emailcharset = Yii::app()->getConfig("emailcharset");
    // Shift the date due to global timeadjust setting
    $today = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig("timeadjust"));

    // check how many uses the token has left
    $token = Token::model($surveyid)->findByAttributes(array('token' => $clienttoken));
    $token->scenario = 'FinalSubmit';  // Do not XSS filter token data

    if ($quotaexit==true)
    {
        $token->completed = 'Q';
        $token->usesleft--;
    }
    else
    {
        if ($token->usesleft <= 1)
        {
            // Finish the token
            if (isTokenCompletedDatestamped($thissurvey))
            {
                $token->completed = $today;
            } else {
                $token->completed = 'Y';
            }
            if(isset($token->participant_id))
            {
                $slquery = SurveyLink::model()->find('participant_id = :pid AND survey_id = :sid AND token_id = :tid', array(':pid'=> $token->participant_id, ':sid'=>$surveyid, ':tid'=>$token->tid));
                if ($slquery)
                {
                    if (isTokenCompletedDatestamped($thissurvey))
                    {
                        $slquery->date_completed = $today;
                    } else {
                        // Update the survey_links table if necessary, to protect anonymity, use the date_created field date
                        $slquery->date_completed = $slquery->date_created;
                    }
                    $slquery->save();
                }
            }
        }
        $token->usesleft--;
    }
    $token->save();

    if ($quotaexit==false)
    {
        if ($token && trim(strip_tags($thissurvey['email_confirm'])) != "" && $thissurvey['sendconfirmation'] == "Y")
        {
         //   if($token->completed == "Y" || $token->completed == $today)
//            {
                $from = "{$thissurvey['adminname']} <{$thissurvey['adminemail']}>";
                $subject=$thissurvey['email_confirm_subj'];

                $aReplacementVars=array();
                $aReplacementVars["ADMINNAME"]=$thissurvey['admin'];
                $aReplacementVars["ADMINEMAIL"]=$thissurvey['adminemail'];
                $aReplacementVars['ADMINEMAIL'] = $thissurvey['adminemail'];
                //Fill with token info, because user can have his information with anonimity control
                $aReplacementVars["FIRSTNAME"]=$token->firstname;
                $aReplacementVars["LASTNAME"]=$token->lastname;
                $aReplacementVars["TOKEN"]=$token->token;
                $aReplacementVars["EMAIL"]=$token->email;
                // added survey url in replacement vars
                $surveylink = Yii::app()->createAbsoluteUrl("/survey/index/sid/{$surveyid}",array('lang'=>$_SESSION['survey_'.$surveyid]['s_lang'],'token'=>$token->token));
                $aReplacementVars['SURVEYURL'] = $surveylink;

                $attrfieldnames=getAttributeFieldNames($surveyid);
                foreach ($attrfieldnames as $attr_name)
                {
                    $aReplacementVars[strtoupper($attr_name)] = $token->$attr_name;
                }

                $dateformatdatat=getDateFormatData($thissurvey['surveyls_dateformat']);
                $numberformatdatat = getRadixPointData($thissurvey['surveyls_numberformat']);
                $redata=array('thissurvey'=>$thissurvey);
                $subject=templatereplace($subject,$aReplacementVars,$redata,'email_confirm_subj', false, NULL, array(), true );

                $subject=html_entity_decode($subject,ENT_QUOTES,$emailcharset);

                if (getEmailFormat($surveyid) == 'html')
                {
                    $ishtml=true;
                }
                else
                {
                    $ishtml=false;
                }

                $message=$thissurvey['email_confirm'];
                //$message=ReplaceFields($message, $fieldsarray, true);
                $message=templatereplace($message,$aReplacementVars,$redata,'email_confirm', false, NULL, array(), true );
                if (!$ishtml)
                {
                    $message=strip_tags(breakToNewline(html_entity_decode($message,ENT_QUOTES,$emailcharset)));
                }
                else
                {
                    $message=html_entity_decode($message,ENT_QUOTES, $emailcharset );
                }

                //Only send confirmation email if there is a valid email address
            $sToAddress=validateEmailAddresses($token->email);
            if ($sToAddress) {
                $aAttachments = unserialize($thissurvey['attachments']);

                $aRelevantAttachments = array();
                /*
                 * Iterate through attachments and check them for relevance.
                 */
                if (isset($aAttachments['confirmation']))
                {
                    foreach ($aAttachments['confirmation'] as $aAttachment)
                    {
                        $relevance = $aAttachment['relevance'];
                        // If the attachment is relevant it will be added to the mail.
                        if (LimeExpressionManager::ProcessRelevance($relevance) && file_exists($aAttachment['url']))
                        {
                            $aRelevantAttachments[] = $aAttachment['url'];
                        }
                    }
                }
                $event = new PluginEvent('beforeTokenEmail');
                $event->set('survey', $surveyid);
                $event->set('type', 'confirm');
                $event->set('model', 'confirm');
                $event->set('subject', $subject);
                $event->set('to', $sToAddress);
                $event->set('body', $message);
                $event->set('from', $from);
                $event->set('bounce', getBounceEmail($surveyid));
                $event->set('token', $token->attributes);
                App()->getPluginManager()->dispatchEvent($event);
                $subject = $event->get('subject');
                $message = $event->get('body');
                $to = $event->get('to');
                $from = $event->get('from');
                $bounce = $event->get('bounce');
                if ($event->get('send', true) != false)
                {
                    SendEmailMessage($message, $subject, $to, $from, Yii::app()->getConfig("sitename"), $ishtml, $bounce, $aRelevantAttachments);
                }
            }
     //   } else {
                // Leave it to send optional confirmation at closed token
  //          }
        }
    }
}

/**
* Send a submit notification to the email address specified in the notifications tab in the survey settings
*/
function sendSubmitNotifications($surveyid)
{
    // @todo: Remove globals
    global $thissurvey, $maildebug, $tokensexist;

    if (trim($thissurvey['adminemail'])=='')
    {
        return;
    }

    $homeurl=Yii::app()->createAbsoluteUrl('/admin');

    $sitename = Yii::app()->getConfig("sitename");

    $debug=Yii::app()->getConfig('debug');
    $bIsHTML = ($thissurvey['htmlemail'] == 'Y');

    $aReplacementVars=array();

    // TODO: What is holdpass, and is it OK to skip these lines if it is set? Related to 'Resume later' functionality
    if ($thissurvey['allowsave'] == "Y" && isset($_SESSION['survey_'.$surveyid]['scid']) && isset($_SESSION['survey_'.$surveyid]['holdpass']))
    {
        $aReplacementVars['RELOADURL']=Yii::app()->getController()->createUrl("/survey/index/sid/{$surveyid}/loadall/reload/scid/".$_SESSION['survey_'.$surveyid]['scid']."/lang/".urlencode(App()->language),array('loadname'=>$_SESSION['survey_'.$surveyid]['holdname'],'loadpass'=>$_SESSION['survey_'.$surveyid]['holdpass']));
        if ($bIsHTML)
        {
            $aReplacementVars['RELOADURL']="<a href='{$aReplacementVars['RELOADURL']}'>{$aReplacementVars['RELOADURL']}</a>";
        }
    }
    else
    {
        $aReplacementVars['RELOADURL']='';
    }

    if (!isset($_SESSION['survey_'.$surveyid]['srid']))
        $srid = null;
    else
        $srid = $_SESSION['survey_'.$surveyid]['srid'];
    $aReplacementVars['ADMINNAME'] = $thissurvey['adminname'];
    $aReplacementVars['ADMINEMAIL'] = $thissurvey['adminemail'];
    $aReplacementVars['VIEWRESPONSEURL']=Yii::app()->createAbsoluteUrl("/admin/responses/sa/view/surveyid/{$surveyid}/id/{$srid}");
    $aReplacementVars['EDITRESPONSEURL']=Yii::app()->createAbsoluteUrl("/admin/dataentry/sa/editdata/subaction/edit/surveyid/{$surveyid}/id/{$srid}");
    $aReplacementVars['STATISTICSURL']=Yii::app()->createAbsoluteUrl("/admin/statistics/sa/index/surveyid/{$surveyid}");
    if ($bIsHTML)
    {
        $aReplacementVars['VIEWRESPONSEURL']="<a href='{$aReplacementVars['VIEWRESPONSEURL']}'>{$aReplacementVars['VIEWRESPONSEURL']}</a>";
        $aReplacementVars['EDITRESPONSEURL']="<a href='{$aReplacementVars['EDITRESPONSEURL']}'>{$aReplacementVars['EDITRESPONSEURL']}</a>";
        $aReplacementVars['STATISTICSURL']="<a href='{$aReplacementVars['STATISTICSURL']}'>{$aReplacementVars['STATISTICSURL']}</a>";
    }
    $aReplacementVars['ANSWERTABLE']='';
    $aEmailResponseTo=array();
    $aEmailNotificationTo=array();
    $sResponseData="";

    if (!empty($thissurvey['emailnotificationto']))
    {
        $aRecipient=explode(";", ReplaceFields($thissurvey['emailnotificationto'],array('ADMINEMAIL' =>$thissurvey['adminemail'] ), true));
        foreach($aRecipient as $sRecipient)
        {
            $sRecipient=trim($sRecipient);
            if(validateEmailAddress($sRecipient))
            {
                $aEmailNotificationTo[]=$sRecipient;
            }
        }
    }

    if (!empty($thissurvey['emailresponseto']))
    {
        // there was no token used so lets remove the token field from insertarray
        if (!isset($_SESSION['survey_'.$surveyid]['token']) && $_SESSION['survey_'.$surveyid]['insertarray'][0]=='token')
        {
            unset($_SESSION['survey_'.$surveyid]['insertarray'][0]);
        }
        //Make an array of email addresses to send to
        $aRecipient=explode(";", ReplaceFields($thissurvey['emailresponseto'],array('ADMINEMAIL' =>$thissurvey['adminemail'] ), true));
        foreach($aRecipient as $sRecipient)
        {
            $sRecipient=trim($sRecipient);
            if(validateEmailAddress($sRecipient))
            {
                $aEmailResponseTo[]=$sRecipient;
            }
        }

        $aFullResponseTable=getFullResponseTable($surveyid,$_SESSION['survey_'.$surveyid]['srid'],$_SESSION['survey_'.$surveyid]['s_lang']);
        $ResultTableHTML = "<table class='printouttable' >\n";
        $ResultTableText ="\n\n";
        $oldgid = 0;
        $oldqid = 0;
        foreach ($aFullResponseTable as $sFieldname=>$fname)
        {
            if (substr($sFieldname,0,4)=='gid_')
            {
                $ResultTableHTML .= "\t<tr class='printanswersgroup'><td colspan='2'>".strip_tags($fname[0])."</td></tr>\n";
                $ResultTableText .="\n{$fname[0]}\n\n";
            }
            elseif (substr($sFieldname,0,4)=='qid_')
            {
                $ResultTableHTML .= "\t<tr class='printanswersquestionhead'><td  colspan='2'>".strip_tags($fname[0])."</td></tr>\n";
                $ResultTableText .="\n{$fname[0]}\n";
            }
            else
            {
                $ResultTableHTML .= "\t<tr class='printanswersquestion'><td>".strip_tags("{$fname[0]} {$fname[1]}")."</td><td class='printanswersanswertext'>".CHtml::encode($fname[2])."</td></tr>\n";
                $ResultTableText .="     {$fname[0]} {$fname[1]}: {$fname[2]}\n";
            }
        }

        $ResultTableHTML .= "</table>\n";
        $ResultTableText .= "\n\n";
        if ($bIsHTML)
        {
            $aReplacementVars['ANSWERTABLE']=$ResultTableHTML;
        }
        else
        {
            $aReplacementVars['ANSWERTABLE']=$ResultTableText;
        }
    }

    $sFrom = $thissurvey['adminname'].' <'.$thissurvey['adminemail'].'>';

    $aAttachments = unserialize($thissurvey['attachments']);

    $aRelevantAttachments = array();
    /*
     * Iterate through attachments and check them for relevance.
     */
    if (isset($aAttachments['admin_notification']))
    {
        foreach ($aAttachments['admin_notification'] as $aAttachment)
        {
            $relevance = $aAttachment['relevance'];
            // If the attachment is relevant it will be added to the mail.
            if (LimeExpressionManager::ProcessRelevance($relevance) && file_exists($aAttachment['url']))
            {
                $aRelevantAttachments[] = $aAttachment['url'];
            }
        }
    }

    $redata=compact(array_keys(get_defined_vars()));
    if (count($aEmailNotificationTo)>0)
    {
        $sMessage=templatereplace($thissurvey['email_admin_notification'],$aReplacementVars,$redata,'admin_notification',$thissurvey['anonymized'] == "Y",NULL, array(), true);
        $sSubject=templatereplace($thissurvey['email_admin_notification_subj'],$aReplacementVars,$redata,'admin_notification_subj',($thissurvey['anonymized'] == "Y"),NULL, array(), true);
        foreach ($aEmailNotificationTo as $sRecipient)
        {
        if (!SendEmailMessage($sMessage, $sSubject, $sRecipient, $sFrom, $sitename, $bIsHTML, getBounceEmail($surveyid), $aRelevantAttachments))
            {
                if ($debug>0)
                {
                    echo '<br />Email could not be sent. Reason: '.$maildebug.'<br/>';
                }
            }
        }
    }

        $aRelevantAttachments = array();
    /*
     * Iterate through attachments and check them for relevance.
     */
    if (isset($aAttachments['detailed_admin_notification']))
    {
        foreach ($aAttachments['detailed_admin_notification'] as $aAttachment)
        {
            $relevance = $aAttachment['relevance'];
            // If the attachment is relevant it will be added to the mail.
            if (LimeExpressionManager::ProcessRelevance($relevance) && file_exists($aAttachment['url']))
            {
                $aRelevantAttachments[] = $aAttachment['url'];
            }
        }
    }
    if (count($aEmailResponseTo)>0)
    {
        $sMessage=templatereplace($thissurvey['email_admin_responses'],$aReplacementVars,$redata,'detailed_admin_notification',$thissurvey['anonymized'] == "Y",NULL, array(), true);
        $sSubject=templatereplace($thissurvey['email_admin_responses_subj'],$aReplacementVars,$redata,'detailed_admin_notification_subj',$thissurvey['anonymized'] == "Y",NULL, array(), true);
        foreach ($aEmailResponseTo as $sRecipient)
        {
        if (!SendEmailMessage($sMessage, $sSubject, $sRecipient, $sFrom, $sitename, $bIsHTML, getBounceEmail($surveyid), $aRelevantAttachments))
            {
                if ($debug>0)
                {
                    echo '<br />Email could not be sent. Reason: '.$maildebug.'<br/>';
                }
            }
        }
    }


}

/**
 * submitfailed : used in em_manager_helper.php
 *
 * "Unexpected error"
 *
 * Will send e-mail to adminemail if defined.
 *
 * @param string $errormsg
 * @param string $query  Will be included in sent email
 * @return string Error message
 */
function submitfailed($errormsg = '', $query = null)
{
    global $debug;
    global $thissurvey;
    global $subquery, $surveyid;

    $completed = "<p><span class='fa fa-exclamation-triangle'></span>&nbsp;<strong>"
    . gT("Did Not Save")."</strong></p>"
    . "<p>"
    . gT("An unexpected error has occurred and your responses cannot be saved.")
    . "</p>";
    if ($thissurvey['adminemail'])
    {
        $completed .= "<p>";
        $completed .= gT("Your responses have not been lost and have been emailed to the survey administrator and will be entered into our database at a later point.");
        $completed .= "</p>";
        if ($debug>0)
        {
            $completed.='Error message: '.htmlspecialchars($errormsg).'<br />';
        }
        $email=gT("An error occurred saving a response to survey id","unescaped")." ".$thissurvey['name']." - $surveyid\n\n";
        $email .= gT("DATA TO BE ENTERED","unescaped").":\n";
        foreach ($_SESSION['survey_'.$surveyid]['insertarray'] as $value)
        {
            if (isset($_SESSION['survey_' . $surveyid][$value]))
            {
                $email .= "$value: {$_SESSION['survey_'.$surveyid][$value]}\n";
            }
            else
            {
                $email .= "$value: N/A\n";
            }
        }
        $email .= "\n".gT("SQL CODE THAT FAILED","unescaped").":\n"
        . "$subquery\n\n"
        . ($query ? $query : '') . "\n\n"  // In case we have no global subquery, but an argument to the function
        . gT("ERROR MESSAGE","unescaped").":\n"
        . $errormsg."\n\n";
        SendEmailMessage($email, gT("Error saving results","unescaped"), $thissurvey['adminemail'], $thissurvey['adminemail'], "LimeSurvey", false, getBounceEmail($surveyid));
    }
    else
    {
        $completed .= "<a href='javascript:location.reload()'>".gT("Try to submit again")."</a><br /><br />\n";
        $completed .= $subquery;
    }
    return $completed;
}

/**
 * This function builds all the required session variables when a survey is first started and
 * it loads any answer defaults from command line or from the table defaultvalues
 * It is called from the related format script (group.php, question.php, survey.php)
 * if the survey has just started.
 * @param int $surveyid
 * @param boolean $preview Defaults to false
 * @return void
 */
function buildsurveysession($surveyid,$preview=false)
{
    Yii::trace('start', 'survey.buildsurveysession');
    global $secerror, $clienttoken;
    global $tokensexist;
    global $move, $rooturl;

    $sLangCode=App()->language;
    $languagechanger=makeLanguageChangerSurvey($sLangCode);
    if(!$preview)
    {
        $preview=Yii::app()->getConfig('previewmode');
    }

    $thissurvey = getSurveyInfo($surveyid,$sLangCode);

    if ($thissurvey['nokeyboard']=='Y')
    {
        includeKeypad();
        $kpclass = "text-keypad";
    }
    else
    {
        $kpclass = '';
    }

    // $thissurvey['template'] already fixed by model : but why put this in session ?
    $_SESSION['survey_'.$surveyid]['templatename'] = $thissurvey['template'];
    $_SESSION['survey_'.$surveyid]['templatepath'] = getTemplatePath($thissurvey['template']).DIRECTORY_SEPARATOR;

    $sTemplatePath = $_SESSION['survey_'.$surveyid]['templatepath'];

    $oTemplate = Template::model()->getInstance('', $surveyid);
    $sTemplatePath = $oTemplate->path;
    $sTemplateViewPath = $oTemplate->viewPath;

    /**
    * This method has multiple outcomes that virtually do the same thing
    * Possible scenarios/subscenarios are =>
    *   - No token required & no captcha required
    *   - No token required & captcha required
    *       > captcha may be wrong
    *   - token required & captcha required
    *       > token may be wrong/used
    *       > captcha may be wrong
    */

    $scenarios = array(
        "tokenRequired" => ($tokensexist == 1),
        "captchaRequired" => (isCaptchaEnabled('surveyaccessscreen',$thissurvey['usecaptcha']) && !isset($_SESSION['survey_'.$surveyid]['captcha_surveyaccessscreen']))
    );

    /**
    *   Set subscenarios depending on scenario outcome
    */
    $subscenarios = array(
        "captchaCorrect" => false,
        "tokenValid" => false
    );
    //Check the scenario for token required
    if($scenarios['tokenRequired'])
    {
        //Check for the token-validity
        if ($thissurvey['alloweditaftercompletion'] == 'Y' ) {
            $oTokenEntry = Token::model($surveyid)->findByAttributes(array('token'=>$clienttoken));
        } else {
            $oTokenEntry = Token::model($surveyid)->usable()->incomplete()->findByAttributes(array('token' => $clienttoken));
        }

        $subscenarios['tokenValid'] = ((!empty($oTokenEntry) && ($clienttoken != "")));
    }
    else
    {
        $subscenarios['tokenValid'] = true;
    }

    //Check the scenario for captcha required
    if($scenarios['captchaRequired'])
    {
        //Check if the Captcha was correct
        $loadsecurity = returnGlobal('loadsecurity',true);
        $captcha = Yii::app()->getController()->createAction('captcha');
        $subscenarios['captchaCorrect'] = $captcha->validate($loadsecurity, false);
    }
    else
    {
        $subscenarios['captchaCorrect'] = true;
        $loadsecurity = false;
    }

    //RenderWay defines which html gets rendered to the user_error
    // Possibilities are main,register,correct
    $renderCaptcha = "";
    $renderToken = "";

    //Define array to render the partials
    $aEnterTokenData = array();
    $aEnterTokenData['bNewTest'] =  false;
    $aEnterTokenData['bDirectReload'] =  false;
    $aEnterTokenData['error'] = $secerror;
    $aEnterTokenData['iSurveyId'] = $surveyid;
    $aEnterTokenData['sKpClass'] = $kpclass; // ???
    $aEnterTokenData['sLangCode'] = $sLangCode;
    if (isset($_GET['bNewTest']) && $_GET['newtest'] == "Y"){
        $aEnterTokenData['bNewTest'] =  true;
    }
    // If this is a direct Reload previous answers URL, then add hidden fields
    if (isset($loadall) && isset($scid) && isset($loadname) && isset($loadpass)) {
        $aEnterTokenData['bDirectReload'] =  true;
        $aEnterTokenData['sCid'] =  $scid;
        $aEnterTokenData['sLoadname'] =  htmlspecialchars($loadname);
        $aEnterTokenData['sLoadpass'] =  htmlspecialchars($loadpass);
    }

    $FlashError = "";

    // Scenario => Captcha required
    if($scenarios['captchaRequired'] && !$preview) {
        $FlashError = '';

        //Apply the captchaEnabled flag to the partial
        $aEnterTokenData['bCaptchaEnabled'] = true;
        // IF CAPTCHA ANSWER IS NOT CORRECT OR NOT SET
        if (!$subscenarios['captchaCorrect']) {
            if ($loadsecurity) {
                // was a bad answer
                $FlashError.=gT("Your answer to the security question was not correct - please try again.")."<br/>\n";
            }
            $renderCaptcha='main';
        }
        else {
            $_SESSION['survey_'.$surveyid]['captcha_surveyaccessscreen']=true;
            $renderCaptcha='correct';
        }
    }

    // Scenario => Token required
    if ($scenarios['tokenRequired'] && !$preview){
        //Test if token is valid
        list($renderToken, $FlashError, $aEnterTokenData) = testIfTokenIsValid($subscenarios, $thissurvey, $aEnterTokenData, $clienttoken);
    }

    //If there were errors, display through yii->FlashMessage
    if($FlashError !== ""){
        $aEnterTokenData['errorMessage'] = $FlashError;
    }

    $renderWay = getRenderWay($renderToken, $renderCaptcha);
    $redata = compact(array_keys(get_defined_vars()));
    renderRenderWayForm($renderWay, $redata, $scenarios, $sTemplateViewPath, $aEnterTokenData, $surveyid);

    // Reset all the session variables and start again
    resetAllSessionVariables($surveyid);

    // Multi lingual support order : by REQUEST, if not by Token->language else by survey default language
    if (returnGlobal('lang',true))
    {
        $language_to_set=returnGlobal('lang',true);
    }
    elseif (isset($oTokenEntry) && $oTokenEntry)
    {
        // If survey have token : we have a $oTokenEntry
        // Can use $oTokenEntry = Token::model($surveyid)->findByAttributes(array('token'=>$clienttoken)); if we move on another function : this par don't validate the token validity
        $language_to_set=$oTokenEntry->language;
    }
    else
    {
        $language_to_set = $thissurvey['language'];
    }
    // Always SetSurveyLanguage : surveys controller SetSurveyLanguage too, if different : broke survey (#09769)
    SetSurveyLanguage($surveyid, $language_to_set);

    UpdateGroupList($surveyid, $_SESSION['survey_'.$surveyid]['s_lang']);

    $totalquestions = Question::model()->getTotalQuestions($surveyid);

    $iTotalGroupsWithoutQuestions = QuestionGroup::model()->getTotalGroupsWithoutQuestions($surveyid);

    // Fix totalquestions by substracting Test Display questions
    $iNumberofQuestions = Question::model()->getNumberOfQuestions($surveyid);

    $_SESSION['survey_'.$surveyid]['totalquestions'] = $totalquestions - (int) reset($iNumberofQuestions);

    // 2. SESSION VARIABLE: totalsteps
    setTotalSteps($surveyid, $thissurvey, $totalquestions);

    // Break out and crash if there are no questions!
    if ($totalquestions == 0 || $iTotalGroupsWithoutQuestions > 0)
    {
        $redata = compact(array_keys(get_defined_vars()));
        breakOutAndCrash($redata, $sTemplateViewPath, $totalquestions, $iTotalGroupsWithoutQuestions, $thissurvey);
    }

    //Perform a case insensitive natural sort on group name then question title of a multidimensional array
    //    usort($arows, 'groupOrderThenQuestionOrder');

    //3. SESSION VARIABLE - insertarray
    //An array containing information about used to insert the data into the db at the submit stage
    //4. SESSION VARIABLE - fieldarray
    //See rem at end..

    if ($tokensexist == 1 && $clienttoken)
    {
        $_SESSION['survey_'.$surveyid]['token'] = $clienttoken;
    }

    if ($thissurvey['anonymized'] == "N")
    {
        $_SESSION['survey_'.$surveyid]['insertarray'][]= "token";
    }

    $qtypes=getQuestionTypeList('','array');
    $fieldmap=createFieldMap($surveyid,'full',true,false,$_SESSION['survey_'.$surveyid]['s_lang']);

    //$seed = ls\mersenne\getSeed($surveyid, $preview);

    // Randomization groups for groups
    list($fieldmap, $randomized1) = randomizationGroup($surveyid, $fieldmap, $preview);

    // Randomization groups for questions
    list($fieldmap, $randomized2) = randomizationQuestion($surveyid, $fieldmap, $preview);

    $randomized = $randomized1 || $randomized2;;

    if ($randomized === true)
    {
        $fieldmap = finalizeRandomization($fieldmap);

        $_SESSION['survey_'.$surveyid]['fieldmap-' . $surveyid . $_SESSION['survey_'.$surveyid]['s_lang']] = $fieldmap;
        $_SESSION['survey_'.$surveyid]['fieldmap-' . $surveyid . '-randMaster'] = 'fieldmap-' . $surveyid . $_SESSION['survey_'.$surveyid]['s_lang'];
    }

    // TMSW Condition->Relevance:  don't need hasconditions, or usedinconditions

    $_SESSION['survey_'.$surveyid]['fieldmap']=$fieldmap;

    initFieldArray($surveyid, $fieldmap);

    // Prefill questions/answers from command line params
    prefillFromCommandLine($surveyid);

    if (isset($_SESSION['survey_'.$surveyid]['fieldarray'])) $_SESSION['survey_'.$surveyid]['fieldarray']=array_values($_SESSION['survey_'.$surveyid]['fieldarray']);

    //Check if a passthru label and value have been included in the query url
    checkPassthruLabel($surveyid, $preview, $fieldmap);

    Yii::trace('end', 'survey.buildsurveysession');
    //traceVar($_SESSION['survey_' . $surveyid]);
}

/**
 * Check if a passthru label and value have been included in the query url
 * @param int $surveyid
 * @param boolean $preview
 * @return void
 */
function checkPassthruLabel($surveyid, $preview, $fieldmap)
{
    $oResult=SurveyURLParameter::model()->getParametersForSurvey($surveyid);
    foreach($oResult->readAll() as $aRow)
    {
        if(isset($_GET[$aRow['parameter']]) && !$preview)
        {
            $_SESSION['survey_'.$surveyid]['urlparams'][$aRow['parameter']]=$_GET[$aRow['parameter']];
            if ($aRow['targetqid']!='')
            {
                foreach ($fieldmap as $sFieldname=>$aField)
                {
                    if ($aRow['targetsqid']!='')
                    {
                        if ($aField['qid']==$aRow['targetqid'] && $aField['sqid']==$aRow['targetsqid'])
                        {
                            $_SESSION['survey_'.$surveyid]['startingValues'][$sFieldname]=$_GET[$aRow['parameter']];
                            $_SESSION['survey_'.$surveyid]['startingValues'][$aRow['parameter']]=$_GET[$aRow['parameter']];
                        }
                    }
                    else
                    {
                        if ($aField['qid']==$aRow['targetqid'])
                        {
                            $_SESSION['survey_'.$surveyid]['startingValues'][$sFieldname]=$_GET[$aRow['parameter']];
                            $_SESSION['survey_'.$surveyid]['startingValues'][$aRow['parameter']]=$_GET[$aRow['parameter']];
                        }
                    }
                }

            }
        }
    }
}

/**
 * Prefill startvalues from command line param
 * @param integer $surveyid
 * @return void
 */
function prefillFromCommandLine($surveyid)
{
    $reservedGetValues= array('token','sid','gid','qid','lang','newtest','action');
    $startingValues=array();
    if (isset($_GET))
    {
        foreach ($_GET as $k=>$v)
        {
            if (!in_array($k,$reservedGetValues) && isset($_SESSION['survey_'.$surveyid]['fieldmap'][$k]))
            {
                $startingValues[$k] = $v;
            }
            else
            {   // Search question codes to use those for prefilling.
                foreach($_SESSION['survey_'.$surveyid]['fieldmap'] as $sgqa => $details)
                {
                    if ($details['title'] == $k)
                    {
                        $startingValues[$sgqa] = $v;
                    }
                }
            }
        }
    }
    $_SESSION['survey_'.$surveyid]['startingValues']=$startingValues;
}

/**
 * @param array $fieldmap
 * @param integer $surveyid
 * @return void
 */
function initFieldArray($surveyid, array $fieldmap)
{
    foreach ($fieldmap as $key => $field)
    {
        if (isset($field['qid']) && $field['qid']!='')
        {
            $_SESSION['survey_'.$surveyid]['fieldnamesInfo'][$field['fieldname']]=$field['sid'].'X'.$field['gid'].'X'.$field['qid'];
            $_SESSION['survey_'.$surveyid]['insertarray'][]=$field['fieldname'];
            //fieldarray ARRAY CONTENTS -
            //            [0]=questions.qid,
            //            [1]=fieldname,
            //            [2]=questions.title,
            //            [3]=questions.question
            //            [4]=questions.type,
            //            [5]=questions.gid,
            //            [6]=questions.mandatory,
            //            [7]=conditionsexist,
            //            [8]=usedinconditions
            //            [8]=usedinconditions
            //            [9]=used in group.php for question count
            //            [10]=new group id for question in randomization group (GroupbyGroup Mode)

            if (!isset($_SESSION['survey_'.$surveyid]['fieldarray'][$field['sid'].'X'.$field['gid'].'X'.$field['qid']]))
            {
                //JUST IN CASE : PRECAUTION!
                //following variables are set only if $style=="full" in createFieldMap() in common_helper.
                //so, if $style = "short", set some default values here!
                if (isset($field['title']))
                    $title = $field['title'];
                else
                    $title = "";

                if (isset($field['question']))
                    $question = $field['question'];
                else
                    $question = "";

                if (isset($field['mandatory']))
                    $mandatory = $field['mandatory'];
                else
                    $mandatory = 'N';

                if (isset($field['hasconditions']))
                    $hasconditions = $field['hasconditions'];
                else
                    $hasconditions = 'N';

                if (isset($field['usedinconditions']))
                    $usedinconditions = $field['usedinconditions'];
                else
                    $usedinconditions = 'N';

                $_SESSION['survey_'.$surveyid]['fieldarray'][$field['sid'].'X'.$field['gid'].'X'.$field['qid']]=array($field['qid'],
                $field['sid'].'X'.$field['gid'].'X'.$field['qid'],
                $title,
                $question,
                $field['type'],
                $field['gid'],
                $mandatory,
                $hasconditions,
                $usedinconditions);
            }
            if (isset($field['random_gid']))
            {
                $_SESSION['survey_'.$surveyid]['fieldarray'][$field['sid'].'X'.$field['gid'].'X'.$field['qid']][10] = $field['random_gid'];
            }
        }
    }
}

/**
 * @param array $aEnterTokenData
 * @param array $subscenarios
 * @param int $surveyid
 * @param boolean $loadsecurity
 * @todo This does not work for some reason, copied the code back. See bug #11739.
 * @return string[] ($renderCaptcha, $FlashError)
 */
function testCaptcha(array $aEnterTokenData, array $subscenarios, $surveyid, $loadsecurity)
{
    $FlashError = '';

    //Apply the captchaEnabled flag to the partial
    $aEnterTokenData['bCaptchaEnabled'] = true;
    // IF CAPTCHA ANSWER IS NOT CORRECT OR NOT SET
    if (!$subscenarios['captchaCorrect'])
    {
        if ($loadsecurity)
        { // was a bad answer
            $FlashError.=gT("Your answer to the security question was not correct - please try again.")."<br/>\n";
        }
        $renderCaptcha='main';
    }
    else{
        $_SESSION['survey_'.$surveyid]['captcha_surveyaccessscreen']=true;
        $renderCaptcha='correct';
    }

    return array ($renderCaptcha, $FlashError);
}

/**
 * Randomization group for groups
 * @param int $surveyid
 * @param array $fieldmap
 * @param boolean $preview
 * @return array ($fieldmap, $randomized)
 */
function randomizationGroup($surveyid, array $fieldmap, $preview)
{
    // Randomization groups for groups
    $aRandomGroups=array();
    $aGIDCompleteMap=array();

    // First find all groups and their groups IDS
    $criteria = new CDbCriteria;
    $criteria->addColumnCondition(array('sid' => $surveyid, 'language' => $_SESSION['survey_'.$surveyid]['s_lang']));
    $criteria->addCondition("randomization_group != ''");
    $oData = QuestionGroup::model()->findAll($criteria);

    foreach($oData as $aGroup)
    {
        $aRandomGroups[$aGroup['randomization_group']][] = $aGroup['gid'];
    }

    // Shuffle each group and create a map for old GID => new GID
    foreach ($aRandomGroups as $sGroupName=>$aGIDs)
    {
        $aShuffledIDs=$aGIDs;
        shuffle($aShuffledIDs);
        $aGIDCompleteMap=$aGIDCompleteMap+array_combine($aGIDs,$aShuffledIDs);
    }
    $_SESSION['survey_' . $surveyid]['groupReMap'] = $aGIDCompleteMap;

    $randomized = false;    // So we can trigger reorder once for group and question randomization
    // Now adjust the grouplist
    if (count($aRandomGroups)>0 && !$preview)
    {
        $randomized = true;    // So we can trigger reorder once for group and question randomization
        // Now adjust the grouplist
        Yii::import('application.helpers.frontend_helper', true);   // make sure frontend helper is loaded
        UpdateGroupList($surveyid, $_SESSION['survey_'.$surveyid]['s_lang']);
        // ... and the fieldmap

        // First create a fieldmap with GID as key
        foreach ($fieldmap as $aField)
        {
            if (isset($aField['gid']))
            {
                $GroupFieldMap[$aField['gid']][]=$aField;
            }
            else{
                $GroupFieldMap['other'][]=$aField;
            }
        }
        // swap it
        foreach ($GroupFieldMap as $iOldGid => $fields)
        {
            $iNewGid = $iOldGid;
            if (isset($aGIDCompleteMap[$iOldGid]))
            {
                $iNewGid = $aGIDCompleteMap[$iOldGid];
            }
            $newGroupFieldMap[$iNewGid] = $GroupFieldMap[$iNewGid];
        }
        $GroupFieldMap = $newGroupFieldMap;
        // and convert it back to a fieldmap
        unset($fieldmap);
        foreach($GroupFieldMap as $aGroupFields)
        {
            foreach ($aGroupFields as $aField)
            {
                if (isset($aField['fieldname'])) {
                    $fieldmap[$aField['fieldname']] = $aField;  // isset() because of the shuffled flag above
                }
            }
        }
    }

    return array($fieldmap, $randomized);
}

/**
 * Randomization group for questions
 * @param int $surveyid
 * @param array $fieldmap
 * @param boolean $preview
 * @return array ($fieldmap, $randomized)
 */
function randomizationQuestion($surveyid, array $fieldmap, $preview)
{
    $randomized = false;
    // Find all defined randomization groups through question attribute values
    $randomGroups=array();
    if (in_array(Yii::app()->db->getDriverName(), array('mssql', 'sqlsrv', 'dblib')))
    {
        $rgquery = "SELECT attr.qid, CAST(value as varchar(255)) as value FROM {{question_attributes}} as attr right join {{questions}} as quests on attr.qid=quests.qid WHERE attribute='random_group' and CAST(value as varchar(255)) <> '' and sid=$surveyid GROUP BY attr.qid, CAST(value as varchar(255))";
    }
    else
    {
        $rgquery = "SELECT attr.qid, value FROM {{question_attributes}} as attr right join {{questions}} as quests on attr.qid=quests.qid WHERE attribute='random_group' and value <> '' and sid=$surveyid GROUP BY attr.qid, value";
    }
    $rgresult = dbExecuteAssoc($rgquery);

    foreach($rgresult->readAll() as $rgrow)
    {
        // Get the question IDs for each randomization group
        $randomGroups[$rgrow['value']][] = $rgrow['qid'];
    }

    // If we have randomization groups set, then lets cycle through each group and
    // replace questions in the group with a randomly chosen one from the same group
    if (count($randomGroups) > 0 && !$preview)
    {
        $randomized   = true;    // So we can trigger reorder once for group and question randomization
        $copyFieldMap = array();
        $oldQuestOrder = array();
        $newQuestOrder = array();
        $randGroupNames = array();
        foreach ($randomGroups as $key=>$value)
        {
            $oldQuestOrder[$key] = $randomGroups[$key];
            $newQuestOrder[$key] = $oldQuestOrder[$key];
            // We shuffle the question list to get a random key->qid which will be used to swap from the old key
            shuffle($newQuestOrder[$key]);
            $randGroupNames[] = $key;
        }

        // Loop through the fieldmap and swap each question as they come up
        foreach ($fieldmap as $fieldkey => $fieldval)
        {
            $found = 0;
            foreach ($randomGroups as $gkey => $gval)
            {
                // We found a qid that is in the randomization group
                if (isset($fieldval['qid']) && in_array($fieldval['qid'],$oldQuestOrder[$gkey]))
                {
                    // Get the swapped question
                    $idx = array_search($fieldval['qid'],$oldQuestOrder[$gkey]);
                    foreach ($fieldmap as $key => $field)
                    {
                        if (isset($field['qid']) && $field['qid'] == $newQuestOrder[$gkey][$idx])
                        {
                            $field['random_gid'] = $fieldval['gid'];   // It is possible to swap to another group
                            $copyFieldMap[$key]  = $field;
                        }
                    }
                    $found = 1;
                    break;
                } else
                {
                    $found = 2;
                }
            }
            if ($found == 2)
            {
                $copyFieldMap[$fieldkey]=$fieldval;
            }
            reset($randomGroups);
        }
        $fieldmap = $copyFieldMap;
    }

    return array($fieldmap, $randomized);
}

/**
 * Stuff?
 * @param array $fieldmap
 * @return array Fieldmap
 */
function finalizeRandomization($fieldmap)
{
    // reset the sequencing counts
    $gseq = -1;
    $_gid = -1;
    $qseq = -1;
    $_qid = -1;
    $copyFieldMap = array();
    foreach ($fieldmap as $key => $val)
    {
        if ($val['gid'] != '')
        {
            if (isset($val['random_gid']))
            {
                $gid = $val['random_gid'];
            } else {
                $gid = $val['gid'];
            }
            if ($gid != $_gid)
            {
                $_gid = $gid;
                ++$gseq;
            }
        }

        if ($val['qid'] != '' && $val['qid'] != $_qid)
        {
            $_qid = $val['qid'];
            ++$qseq;
        }

        if ($val['gid'] != '' && $val['qid'] != '')
        {
            $val['groupSeq']    = $gseq;
            $val['questionSeq'] = $qseq;
        }

        $copyFieldMap[$key] = $val;
    }
    return $copyFieldMap;
}

/**
 * Test if token is valid
 * @param array $subscenarios
 * @param array $thissurvey
 * @param array $aEnterTokenData
 * @param string $clienttoken
 * @return string[] ($renderToken, $FlashError)
 */
function testIfTokenIsValid(array $subscenarios, array $thissurvey, array $aEnterTokenData, $clienttoken)
{
    $FlashError = '';
    if(!$subscenarios['tokenValid'])
    {
        //Check if there is a clienttoken set
        if((!isset($clienttoken) || $clienttoken==""))
        {
            if (isset($thissurvey) && $thissurvey['allowregister'] == "Y")
            {
                $renderToken='register';
            }
            else
            {
                $renderToken='main';
            }
        }
        else
        { //token was wrong
            $errorMsg= ""
            . gT("The token you have provided is either not valid, or has already been used.")."<br /><br />\n"
            . sprintf( gT("For further information please contact %s"), $thissurvey['adminname'])
            . "(<a href='mailto:".$thissurvey['adminemail']."'>"
            . $thissurvey['adminemail']."</a>)";

            $FlashError .= $errorMsg;

            $renderToken='main';
        }
    }
    else
    {
        $aEnterTokenData['visibleToken'] =  $clienttoken;
        $aEnterTokenData['token'] =  $clienttoken;
        $renderToken='correct';
    }
    return array($renderToken, $FlashError, $aEnterTokenData);
}

/**
 * Returns which way should be rendered
 * @param string $renderToken
 * @param string $renderCaptcha
 * @return string
 */
function getRenderWay($renderToken, $renderCaptcha)
{
    $renderWay = "";
    if($renderToken!==$renderCaptcha)
    {
        if($renderToken==="register")
        {
            $renderWay="register";
        }
        if($renderCaptcha==="correct" || $renderToken==="correct")
        {
            $renderWay="main";
        }
        if($renderCaptcha==="")
        {
            $renderWay=$renderToken;
        }
        else if($renderToken==="")
        {
            $renderWay=$renderCaptcha;
        }
    }
    else
    {
        $renderWay=$renderToken;
    }
    return $renderWay;
}

/**
 * Render token, captcha or register form
 * @param string $renderWay
 * @param array $redata
 * @param array $scenarios
 * @param string $sTemplateViewPath
 * @param array $aEnterTokenData
 * @param int $surveyid
 * @return void
 */
function renderRenderWayForm($renderWay, array $redata, array $scenarios, $sTemplateViewPath, $aEnterTokenData, $surveyid)
{
    switch($renderWay){
        case "main": //Token required, maybe Captcha required
            sendCacheHeaders();
            doHeader();
            echo templatereplace(file_get_contents($sTemplateViewPath."startpage.pstpl"),array(),$redata,'frontend_helper[875]');
            echo templatereplace(file_get_contents($sTemplateViewPath."survey.pstpl"),array(),$redata,'frontend_helper[877]');

            // render token form
            if($scenarios['tokenRequired']){
                doFRender('/survey/frontpage/enterToken', $aEnterTokenData, false);
            } else {
                App()->getController()->renderPartial('/survey/frontpage/enterCaptcha', $aEnterTokenData);
            }

            echo templatereplace(file_get_contents($sTemplateViewPath."endpage.pstpl"),array(),$redata,'frontend_helper[1645]');
            doFooter();
            Yii::app()->end();
            break;
        case "register": //Register new user
            // Add the event and test if done
            Yii::app()->runController("register/index/sid/{$surveyid}");
            Yii::app()->end();
            echo templatereplace(file_get_contents($sTemplateViewPath."register.pstpl"),array(),$redata,'frontend_helper[1751]');
            break;
        case "correct": //Nothing to hold back, render survey
        default:
            break;
    }
}

/**
 * Resets all session variables for this survey
 * @param int $surveyid
 * @return void
 */
function resetAllSessionVariables($surveyid)
{
    Yii:app()->session->regenerateID(true);
    unset($_SESSION['survey_'.$surveyid]['grouplist']);
    unset($_SESSION['survey_'.$surveyid]['fieldarray']);
    unset($_SESSION['survey_'.$surveyid]['insertarray']);
    unset($_SESSION['survey_'.$surveyid]['fieldnamesInfo']);
    unset($_SESSION['survey_'.$surveyid]['fieldmap-' . $surveyid . '-randMaster']);
    unset($_SESSION['survey_'.$surveyid]['groupReMap']);
    $_SESSION['survey_'.$surveyid]['fieldnamesInfo'] = Array();
}

/**
 * The number of "pages" that will be presented in this survey
 * The number of pages to be presented will differ depending on the survey format
 * Set totalsteps in session
 * @param int $surveyid
 * @param array $thissurvey
 * @return void
 */
function setTotalSteps($surveyid, array $thissurvey, $totalquestions)
{
    switch($thissurvey['format'])
    {
        case "A":
            $_SESSION['survey_'.$surveyid]['totalsteps']=1;
            break;
        case "G":
            if (isset($_SESSION['survey_'.$surveyid]['grouplist']))
            {
                $_SESSION['survey_'.$surveyid]['totalsteps']=count($_SESSION['survey_'.$surveyid]['grouplist']);
            }
            break;
        case "S":
            $_SESSION['survey_'.$surveyid]['totalsteps']=$totalquestions;
    }
}

/**
 * @todo Rename
 * @todo Move HTML to view
 * @param array $redata
 * @param string $sTemplateViewPath
 * @param int $totalquestions
 * @param int $iTotalGroupsWithoutQuestions
 * @param array $thissurvey
 * @return void
 */
function breakOutAndCrash(array $redata, $sTemplateViewPath, $totalquestions, $iTotalGroupsWithoutQuestions, array $thissurvey)
{
    sendCacheHeaders();
    doHeader();

    echo templatereplace(file_get_contents($sTemplateViewPath."startpage.pstpl"),array(),$redata,'frontend_helper[1914]');
    echo templatereplace(file_get_contents($sTemplateViewPath."survey.pstpl"),array(),$redata,'frontend_helper[1915]');
    echo "\t<div id='wrapper'>\n"
    ."\t<p id='tokenmessage'>\n"
    ."\t".gT("This survey cannot be tested or completed for the following reason(s):")."<br />\n";
    echo "<ul>";
    if ($totalquestions == 0){
        echo '<li>'.gT("There are no questions in this survey.").'</li>';
    }
    if ($iTotalGroupsWithoutQuestions > 0){
        echo '<li>'.gT("There are empty question groups in this survey - please create at least one question within a question group.").'</li>';
    }
    echo "</ul>"
    ."\t".sprintf(gT("For further information please contact %s"), $thissurvey['adminname'])
    ." (<a href='mailto:{$thissurvey['adminemail']}'>"
    ."{$thissurvey['adminemail']}</a>)<br /><br />\n"
    ."\t</p>\n"
    ."\t</div>\n";

    echo templatereplace(file_get_contents($sTemplateViewPath."endpage.pstpl"),array(),$redata,'frontend_helper[1925]');
    doFooter();
    Yii::app()->end();
}

/**
* This function creates the form elements in the survey navigation bar
* Adding a hidden input for default behaviour without javascript
* Use button name="move" for real browser (with or without javascript) and IE6/7/8 with javascript
*/
function surveymover()
{
    $surveyid=Yii::app()->getConfig('surveyID');
    $thissurvey=getSurveyInfo($surveyid);

    $sMoveNext="movenext";
    $sMovePrev="";
    $iSessionStep=(isset($_SESSION['survey_'.$surveyid]['step']))?$_SESSION['survey_'.$surveyid]['step']:false;
    $iSessionMaxStep=(isset($_SESSION['survey_'.$surveyid]['maxstep']))?$_SESSION['survey_'.$surveyid]['maxstep']:false;
    $iSessionTotalSteps=(isset($_SESSION['survey_'.$surveyid]['totalsteps']))?$_SESSION['survey_'.$surveyid]['totalsteps']:false;
    $sClass="submit button";
    $sSurveyMover = "";

    // Count down
    if ($thissurvey['navigationdelay'] > 0 && ($iSessionMaxStep!==false && $iSessionMaxStep == $iSessionStep))
     {
        $sClass.=" disabled";
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."/navigator-countdown.js");
        App()->getClientScript()->registerScript('navigator_countdown',"navigator_countdown(" . $thissurvey['navigationdelay'] . ");\n",CClientScript::POS_BEGIN);
     }

    // Previous ?
    if ($thissurvey['format'] != "A" && ($thissurvey['allowprev'] != "N")
        && $iSessionStep
        && !($iSessionStep == 1 && $thissurvey['showwelcome'] == 'N')
        && !Yii::app()->getConfig('previewmode')
    )
    {
        $sMovePrev="moveprev";
     }

    // Submit ?
    if ($iSessionStep && ($iSessionStep == $iSessionTotalSteps)
        || $thissurvey['format'] == 'A'
        )
    {
        $sMoveNext="movesubmit";
    }

    // todo Remove Next if needed (exemple quota show previous only: maybe other, but actually don't use surveymover)
    if(Yii::app()->getConfig('previewmode'))
    {
        $sMoveNext="";
    }

    $sClass .= " btn btn-lg ";

    // Construction of mover
    $sMovePrevButton = '';
    if($sMovePrev){
        $sLangMoveprev=gT("Previous");
        //$sSurveyMover.= CHtml::htmlButton($sLangMoveprev,array('type'=>'submit','id'=>"{$sMovePrev}btn",'value'=>$sMovePrev,'name'=>$sMovePrev,'accesskey'=>'p','class'=>$sClass));
        $sMovePrevButton = CHtml::htmlButton($sLangMoveprev,array('type'=>'submit','id'=>"{$sMovePrev}btn",'value'=>$sMovePrev,'name'=>$sMovePrev,'accesskey'=>'p','class'=>$sClass." btn-default"));
    }
    if($sMovePrev && $sMoveNext){
        $sSurveyMover .= " ";
    }

    $sMoveNextButton = '';
    if($sMoveNext){

        if($sMoveNext=="movesubmit"){
            $sLangMovenext=gT("Submit");
            $sAccessKeyNext='l';// Why l ?
        }else{
            $sLangMovenext=gT("Next");
            $sAccessKeyNext='n';
        }

        //$sSurveyMover.= CHtml::htmlButton($sLangMovenext,array('type'=>'submit','id'=>"{$sMoveNext}btn",'value'=>$sMoveNext,'name'=>$sMoveNext,'accesskey'=>$sAccessKeyNext,'class'=>$sClass));
        $sMoveNextButton = CHtml::htmlButton($sLangMovenext,array('type'=>'submit','id'=>"{$sMoveNext}btn",'value'=>$sMoveNext,'name'=>$sMoveNext,'accesskey'=>$sAccessKeyNext,'class'=>$sClass." btn-primary"));
     }
    //return $sSurveyMover;
    return array('sMovePrevButton' => $sMovePrevButton, 'sMoveNextButton'=>$sMoveNextButton);
}

/**
* Caculate assessement scores
*
* @param mixed $surveyid
* @param boolean $returndataonly - only returns an array with data
*/
function doAssessment($surveyid, $returndataonly=false)
{


    $baselang=Survey::model()->findByPk($surveyid)->language;
    if(Survey::model()->findByPk($surveyid)->assessments!="Y")
    {
        return false;
    }
    $total=0;
    if (!isset($_SESSION['survey_'.$surveyid]['s_lang']))
    {
        $_SESSION['survey_'.$surveyid]['s_lang']=$baselang;
    }
    $query = "SELECT * FROM {{assessments}}
    WHERE sid=$surveyid and language='".$_SESSION['survey_'.$surveyid]['s_lang']."'
    ORDER BY scope, id";

    if ($result = dbExecuteAssoc($query))   //Checked
    {
        $aResultSet=$result->readAll();
        if (count($aResultSet) > 0)
        {
            foreach($aResultSet as $row)
            {
                if ($row['scope'] == "G")
                {
                    $assessment['group'][$row['gid']][]=array("name"=>$row['name'],
                    "min"=>$row['minimum'],
                    "max"=>$row['maximum'],
                    "message"=>$row['message']);
                }
                else
                {
                    $assessment['total'][]=array( "name"=>$row['name'],
                    "min"=>$row['minimum'],
                    "max"=>$row['maximum'],
                    "message"=>$row['message']);
                }
            }
            $fieldmap=createFieldMap($surveyid, "full",false,false,$_SESSION['survey_'.$surveyid]['s_lang']);
            $i=0;
            $total=0;
            $groups=array();
            foreach($fieldmap as $field)
            {
                if (in_array($field['type'],array('1','F','H','W','Z','L','!','M','O','P')))
                {
                    $fieldmap[$field['fieldname']]['assessment_value']=0;
                    if (isset($_SESSION['survey_'.$surveyid][$field['fieldname']]))
                    {
                        if (($field['type'] == "M") || ($field['type'] == "P")) //Multiflexi choice  - result is the assessment attribute value
                        {
                            if ($_SESSION['survey_'.$surveyid][$field['fieldname']] == "Y")
                            {
                                $aAttributes=getQuestionAttributeValues($field['qid']);
                                $fieldmap[$field['fieldname']]['assessment_value']=(int)$aAttributes['assessment_value'];
                                $total=$total+(int)$aAttributes['assessment_value'];
                            }
                        }
                        else  // Single choice question
                        {
                            $usquery = "SELECT assessment_value FROM {{answers}} where qid=".$field['qid']." and language='$baselang' and code=".dbQuoteAll($_SESSION['survey_'.$surveyid][$field['fieldname']]);
                            $usresult = dbExecuteAssoc($usquery);          //Checked
                            if ($usresult)
                            {
                                $usrow = $usresult->read();
                                $fieldmap[$field['fieldname']]['assessment_value']=$usrow['assessment_value'];
                                $total=$total+$usrow['assessment_value'];
                            }
                        }
                    }
                    $groups[]=$field['gid'];
                }
                $i++;
            }

            $groups=array_unique($groups);

            foreach($groups as $group)
            {
                $grouptotal=0;
                foreach ($fieldmap as $field)
                {
                    if ($field['gid'] == $group && isset($field['assessment_value']))
                    {
                        //$grouptotal=$grouptotal+$field['answer'];
                        if (isset ($_SESSION['survey_'.$surveyid][$field['fieldname']]))
                        {
                            $grouptotal=$grouptotal+$field['assessment_value'];
                        }
                    }
                }
                $subtotal[$group]=$grouptotal;
            }
        }
        $assessments = "";
        if (isset($subtotal) && is_array($subtotal))
        {
            foreach($subtotal as $key=>$val)
            {
                if (isset($assessment['group'][$key]))
                {
                    foreach($assessment['group'][$key] as $assessed)
                    {
                        if ($val >= $assessed['min'] && $val <= $assessed['max'] && $returndataonly===false)
                        {
                            $assessments .= "\t<!-- GROUP ASSESSMENT: Score: $val Min: ".$assessed['min']." Max: ".$assessed['max']."-->
                            <table class='assessments'>
                            <tr>
                            <th>".str_replace(array("{PERC}", "{TOTAL}"), array($val, $total), $assessed['name'])."
                            </th>
                            </tr>
                            <tr>
                            <td>".str_replace(array("{PERC}", "{TOTAL}"), array($val, $total), $assessed['message'])."
                            </td>
                            </tr>
                            </table><br />\n";
                        }
                    }
                }
            }
        }

        if (isset($assessment['total']))
        {
            foreach($assessment['total'] as $assessed)
            {
                if ($total >= $assessed['min'] && $total <= $assessed['max'] && $returndataonly===false)
                {
                    $assessments .= "\t\t\t<!-- TOTAL ASSESSMENT: Score: $total Min: ".$assessed['min']." Max: ".$assessed['max']."-->
                    <table class='assessments' align='center'>
                    <tr>
                    <th>".str_replace(array("{PERC}", "{TOTAL}"), array($val, $total), stripslashes($assessed['name']))."
                    </th>
                    </tr>
                    <tr>
                    <td>".str_replace(array("{PERC}", "{TOTAL}"), array($val, $total), stripslashes($assessed['message']))."
                    </td>
                    </tr>
                    </table>\n";
                }
            }
        }
        if ($returndataonly==true)
        {
            return array('total'=>$total);
        }
        else
        {
            return $assessments;
        }
    }
}

/**
* Update SESSION VARIABLE: grouplist
* A list of groups in this survey, ordered by group name.
* @param int surveyid
* @param string language
* @param integer $surveyid
*/
function UpdateGroupList($surveyid, $language)
{

    unset ($_SESSION['survey_'.$surveyid]['grouplist']);
    $query = "SELECT * FROM {{groups}} WHERE sid=$surveyid AND language='".$language."' ORDER BY group_order";
    $result = dbExecuteAssoc($query) or safeDie ("Couldn't get group list<br />$query<br />");  //Checked
    $groupList = array();
    foreach ($result->readAll() as $row)
    {
        $group = array(
            'gid'         => $row['gid'],
            'group_name'  => $row['group_name'],
            'description' =>  $row['description']);
        $groupList[] = $group;
        $gidList[$row['gid']] = $group;
    }

    if (!Yii::app()->getConfig('previewmode') && isset($_SESSION['survey_'.$surveyid]['groupReMap']) && count($_SESSION['survey_'.$surveyid]['groupReMap'])>0)
    {
        // Now adjust the grouplist
        $groupRemap = $_SESSION['survey_'.$surveyid]['groupReMap'];
        $groupListCopy = $groupList;
        foreach ($groupList as $gseq => $info) {
            $gid = $info['gid'];
            if (isset($groupRemap[$gid])) {
                $gid = $groupRemap[$gid];
            }
            $groupListCopy[$gseq] = $gidList[$gid];
        }
        $groupList = $groupListCopy;
     }
     $_SESSION['survey_'.$surveyid]['grouplist'] = $groupList;
}

/**
* FieldArray contains all necessary information regarding the questions
* This function is needed to update it in case the survey is switched to another language
* @todo: Make 'fieldarray' obsolete by replacing with EM session info
*/
function UpdateFieldArray()
{
    global $surveyid;


    if (isset($_SESSION['survey_'.$surveyid]['fieldarray']))
    {
        foreach ($_SESSION['survey_'.$surveyid]['fieldarray'] as $key => $value)
        {
            $questionarray = &$_SESSION['survey_'.$surveyid]['fieldarray'][$key];
            $query = "SELECT title, question FROM {{questions}} WHERE qid=".$questionarray[0]." AND language='".$_SESSION['survey_'.$surveyid]['s_lang']."'";
            $usrow = Yii::app()->db->createCommand($query)->queryRow();
            if ($usrow)
            {
                $questionarray[2]=$usrow['title'];
                $questionarray[3]=$usrow['question'];
            }
            unset($questionarray);
        }
    }
}

/**
* checkCompletedQuota() returns matched quotas information for the current response
* @param integer $surveyid - Survey identification number
* @param bool $return - set to true to return information, false do the quota
* @return array|void - nested array, Quotas->Members->Fields, includes quota information matched in session.
*/
function checkCompletedQuota($surveyid,$return=false)
{
    /* Check if session is set */
    if (!isset(App()->session['survey_'.$surveyid]['srid']))
    {
        return;
    }
    /* Check is Response is already submitted : only when "do" the quota: allow to send information about quota */
    $oResponse=Response::model($surveyid)->findByPk(App()->session['survey_'.$surveyid]['srid']);
    if(!$return && $oResponse && !is_null($oResponse->submitdate))
    {
        return;
    }
    static $aMatchedQuotas; // EM call 2 times quotas with 3 lines of php code, then use static.
    if(!$aMatchedQuotas)
    {
        $aMatchedQuotas=array();
        $quota_info=$aQuotasInfo = getQuotaInformation($surveyid, $_SESSION['survey_'.$surveyid]['s_lang']);
        // $aQuotasInfo have an 'active' key, we don't use it ?
        if(!$aQuotasInfo || empty($aQuotasInfo))
            return $aMatchedQuotas;
        // OK, we have some quota, then find if this $_SESSION have some set
        $aPostedFields = explode("|",Yii::app()->request->getPost('fieldnames','')); // Needed for quota allowing update
        foreach ($aQuotasInfo as $aQuotaInfo)
        {
            if(!$aQuotaInfo['active'])
                continue;
            if(count($aQuotaInfo['members'])===0)
                continue;
            $iMatchedAnswers=0;
            $bPostedField=false;
            // Array of field with quota array value
            $aQuotaFields=array();
            // Array of fieldnames with relevance value : EM fill $_SESSION with default value even is unrelevant (em_manager_helper line 6548)
            $aQuotaRelevantFieldnames=array();
            // To count number of hidden questions
            $aQuotaQid=array();
            foreach ($aQuotaInfo['members'] as $aQuotaMember)
            {
                $aQuotaFields[$aQuotaMember['fieldname']][] = $aQuotaMember['value'];
                $aQuotaRelevantFieldnames[$aQuotaMember['fieldname']]=isset($_SESSION['survey_'.$surveyid]['relevanceStatus'][$aQuotaMember['qid']]) && $_SESSION['survey_'.$surveyid]['relevanceStatus'][$aQuotaMember['qid']];
                $aQuotaQid[]=$aQuotaMember['qid'];
            }
            $aQuotaQid=array_unique($aQuotaQid);
            // For each field : test if actual responses is in quota (and is relevant)
            foreach ($aQuotaFields as $sFieldName=>$aValues)
            {
                $bInQuota=isset($_SESSION['survey_'.$surveyid][$sFieldName]) && in_array($_SESSION['survey_'.$surveyid][$sFieldName],$aValues);
                if($bInQuota && $aQuotaRelevantFieldnames[$sFieldName])
                {
                    $iMatchedAnswers++;
                }
                if(in_array($sFieldName,$aPostedFields))// Need only one posted value
                    $bPostedField=true;
            }
            // Condition to count quota : Answers are the same in quota + an answer is submitted at this time (bPostedField) OR all questions is hidden (bAllHidden)
            $bAllHidden=QuestionAttribute::model()->countByAttributes(array('qid'=>$aQuotaQid),'attribute=:attribute',array(':attribute'=>'hidden'))==count($aQuotaQid);
            if($iMatchedAnswers==count($aQuotaFields) && ( $bPostedField || $bAllHidden) )
            {
                if($aQuotaInfo['qlimit'] == 0)
                { // Always add the quota if qlimit==0
                    $aMatchedQuotas[]=$aQuotaInfo;
                }
                else
                {
                    $iCompleted=getQuotaCompletedCount($surveyid, $aQuotaInfo['id']);
                    if(!is_null($iCompleted) && ((int)$iCompleted >= (int)$aQuotaInfo['qlimit'])) // This remove invalid quota and not completed
                        $aMatchedQuotas[]=$aQuotaInfo;
                }
            }
        }
    }
    if ($return)
        return $aMatchedQuotas;
    if(empty($aMatchedQuotas))
        return;

    // Now we have all the information we need about the quotas and their status.
    // We need to construct the page and do all needed action
    $aSurveyInfo=getSurveyInfo($surveyid, $_SESSION['survey_'.$surveyid]['s_lang']);

    $oTemplate = Template::model()->getInstance('', $surveyid);
    $sTemplatePath = $oTemplate->path;
    $sTemplateViewPath = $oTemplate->viewPath;


    $sClientToken=isset($_SESSION['survey_'.$surveyid]['token'])?$_SESSION['survey_'.$surveyid]['token']:"";
    // $redata for templatereplace
    $aDataReplacement = array(
        'thissurvey'=>$aSurveyInfo,
        'clienttoken'=>$sClientToken,
        'token'=>$sClientToken,
    );

    // We take only the first matched quota, no need for each
    $aMatchedQuota=$aMatchedQuotas[0];
    // If a token is used then mark the token as completed, do it before event : this allow plugin to update token information
    $event = new PluginEvent('afterSurveyQuota');
    $event->set('surveyId', $surveyid);
    $event->set('responseId', $_SESSION['survey_'.$surveyid]['srid']);// We allways have a responseId
    $event->set('aMatchedQuotas', $aMatchedQuotas);// Give all the matched quota : the first is the active
    App()->getPluginManager()->dispatchEvent($event);
    $blocks = array();
    foreach ($event->getAllContent() as $blockData)
    {
        /* @var $blockData PluginEventContent */
        $blocks[] = CHtml::tag('div', array('id' => $blockData->getCssId(), 'class' => $blockData->getCssClass()), $blockData->getContent());
    }
    // Allow plugin to update message, url, url description and action
    $sMessage=$event->get('message',$aMatchedQuota['quotals_message']);
    $sUrl=$event->get('url',$aMatchedQuota['quotals_url']);
    $sUrlDescription=$event->get('urldescrip',$aMatchedQuota['quotals_urldescrip']);
    $sAction=$event->get('action',$aMatchedQuota['action']);
    $sAutoloadUrl=$event->get('autoloadurl',$aMatchedQuota['autoload_url']);

    // Doing the action and show the page
    if ($sAction == "1" && $sClientToken)
        submittokens(true);
    // Construct the default message
    $sMessage = templatereplace($sMessage,array(),$aDataReplacement, 'QuotaMessage', $aSurveyInfo['anonymized']!='N', NULL, array(), true );
    $sUrl = passthruReplace($sUrl, $aSurveyInfo);
    $sUrl = templatereplace($sUrl,array(),$aDataReplacement, 'QuotaUrl', $aSurveyInfo['anonymized']!='N', NULL, array(), true );
    $sUrlDescription = templatereplace($sUrlDescription,array(),$aDataReplacement, 'QuotaUrldescription', $aSurveyInfo['anonymized']!='N', NULL, array(), true );

    // Construction of default message inside quotamessage class
    $sHtmlQuotaMessage = "<div class='quotamessage limesurveycore'>\n";
    $sHtmlQuotaMessage.= "\t".$sMessage."\n";
    $sHtmlQuotaUrl=($sUrl)? "<a href='".$sUrl."'>".$sUrlDescription."</a>" : "";

    // Add the navigator with Previous button if quota allow modification.
    if ($sAction == "2")
    {
        $sQuotaStep = isset($_SESSION['survey_'.$surveyid]['step'])?$_SESSION['survey_'.$surveyid]['step']:0; // Surely not needed
        $sNavigator = CHtml::htmlButton(gT("Previous"),array('type'=>'submit','id'=>"moveprevbtn",'value'=>$sQuotaStep,'name'=>'move','accesskey'=>'p','class'=>"submit button btn btn-default"));
        //$sNavigator .= " ".CHtml::htmlButton(gT("Submit"),array('type'=>'submit','id'=>"movesubmit",'value'=>"movesubmit",'name'=>"movesubmit",'accesskey'=>'l','class'=>"submit button"));
        $sHtmlQuotaMessage.= CHtml::form(array("/survey/index","sid"=>$surveyid), 'post', array('id'=>'limesurvey','name'=>'limesurvey','class'=>'survey-form-container QuotaMessage'));
        $sHtmlQuotaMessage.= templatereplace(file_get_contents($sTemplateViewPath."/navigator.pstpl"),array('MOVEPREVBUTTON'=>$sNavigator,'SAVE'=>''),$aDataReplacement);
        $sHtmlQuotaMessage.= CHtml::hiddenField('sid',$surveyid);
        $sHtmlQuotaMessage.= CHtml::hiddenField('token',$sClientToken);// Did we really need it ?
        $sHtmlQuotaMessage.= CHtml::endForm();
    }

    $sHtmlQuotaMessage.= "</div>\n";
    // Add the plugin message before default message
    $sHtmlQuotaMessage = implode("\n", $blocks) ."\n". $sHtmlQuotaMessage;

    // Send page to user and end.
    sendCacheHeaders();
    if($sAutoloadUrl == 1 && $sUrl != "")
    {
        if ($sAction == "1")
            killSurveySession($surveyid);
        header("Location: ".$sUrl);
    }
    doHeader();
    echo templatereplace(file_get_contents($sTemplateViewPath."/startpage.pstpl"),array(),$aDataReplacement);
    echo templatereplace(file_get_contents($sTemplateViewPath."/completed.pstpl"),array("COMPLETED"=>$sHtmlQuotaMessage,"URL"=>$sHtmlQuotaUrl),$aDataReplacement);
    echo templatereplace(file_get_contents($sTemplateViewPath."/endpage.pstpl"),array(),$aDataReplacement);
    doFooter();
    if ($sAction == "1")
        killSurveySession($surveyid);
    Yii::app()->end();
}

/**
* encodeEmail : encode admin email in public part
*
* @param mixed $mail
* @param mixed $text
* @param mixed $class
* @param mixed $params
*/
function encodeEmail($mail, $text="", $class="", $params=array())
{
    $encmail ="";
    for($i=0; $i<strlen($mail); $i++)
    {
        $encMod = rand(0,2);
        switch ($encMod)
        {
            case 0: // None
                $encmail .= substr($mail,$i,1);
                break;
            case 1: // Decimal
                $encmail .= "&#".ord(substr($mail,$i,1)).';';
                break;
            case 2: // Hexadecimal
                $encmail .= "&#x".dechex(ord(substr($mail,$i,1))).';';
                break;
        }
    }

    if(!$text)
    {
        $text = $encmail;
    }
    return $text;
}

/**
* GetReferringUrl() returns the referring URL
* @return string
*/
function GetReferringUrl()
{
    // read it from server variable
    if(isset($_SERVER["HTTP_REFERER"]))
    {
        if (!Yii::app()->getConfig('strip_query_from_referer_url'))
        {
            return $_SERVER["HTTP_REFERER"];
        }
        else
        {
            $aRefurl = explode("?",$_SERVER["HTTP_REFERER"]);
            return $aRefurl[0];
        }
    }
    else
    {
        return null;
    }
}

/**
* Shows the welcome page, used in group by group and question by question mode
*/
function display_first_page() {
    global $token, $surveyid, $thissurvey, $navigator;
    $totalquestions = $_SESSION['survey_'.$surveyid]['totalquestions'];

    // Fill some necessary var for template
    $aNavigator = surveymover();
    $moveprevbutton = $aNavigator['sMovePrevButton'];
    $movenextbutton = $aNavigator['sMoveNextButton'];
    $navigator = $moveprevbutton.' '.$movenextbutton;

    $sitename = Yii::app()->getConfig('sitename');
    $languagechanger=makeLanguageChangerSurvey(App()->language);

    sendCacheHeaders();
    doHeader();

    LimeExpressionManager::StartProcessingPage();
    LimeExpressionManager::StartProcessingGroup(-1, false, $surveyid);  // start on welcome page

    $redata = compact(array_keys(get_defined_vars()));

    $oTemplate = Template::model()->getInstance('', $surveyid);
    $sTemplatePath = $oTemplate->path;
    $sTemplateViewPath = $oTemplate->viewPath;
    echo templatereplace(file_get_contents($sTemplateViewPath."startpage.pstpl"),array(),$redata,'frontend_helper[2757]');
    echo CHtml::form(array("/survey/index","sid"=>$surveyid), 'post', array('id'=>'limesurvey','name'=>'limesurvey','autocomplete'=>'off', 'class'=>'frontend_helper'));

    echo templatereplace(file_get_contents($sTemplateViewPath."welcome.pstpl"),array(),$redata,'frontend_helper[2762]')."\n";

    if ($thissurvey['anonymized'] == "Y")
    {
        echo templatereplace(file_get_contents($sTemplateViewPath."/privacy.pstpl"),array(),$redata,'frontend_helper[2765]')."\n";
    }

    echo templatereplace(file_get_contents($sTemplateViewPath."navigator.pstpl"),array(),$redata,'frontend_helper[2767]');


    echo "\n<input type='hidden' name='sid' value='$surveyid' id='sid' />\n";
    if (isset($token) && !empty($token)) {
        echo "\n<input type='hidden' name='token' value='$token' id='token' />\n";
    }
    echo "\n<input type='hidden' name='lastgroupname' value='_WELCOME_SCREEN_' id='lastgroupname' />\n"; //This is to ensure consistency with mandatory checks, and new group test
    $loadsecurity = returnGlobal('loadsecurity',true);
    if (isset($loadsecurity)) {
        echo "\n<input type='hidden' name='loadsecurity' value='$loadsecurity' id='loadsecurity' />\n";
    }
    $_SESSION['survey_'.$surveyid]['LEMpostKey'] = mt_rand();
    echo "<input type='hidden' name='LEMpostKey' value='{$_SESSION['survey_'.$surveyid]['LEMpostKey']}' id='LEMpostKey' />\n";
    echo "<input type='hidden' name='thisstep' id='thisstep' value='0' />\n";

    echo "<!--frontendhelper --></form>";
    echo templatereplace(file_get_contents($sTemplateViewPath."endpage.pstpl"),array(),$redata,'frontend_helper[2782]');


    echo LimeExpressionManager::GetRelevanceAndTailoringJavaScript();
    LimeExpressionManager::FinishProcessingPage();
    doFooter();
    echo "<!-- end of frontend_helper /  display_first_page -->";
}

/**
* killSurveySession : reset $_SESSION part for the survey
* @param int $iSurveyID
*/
function killSurveySession($iSurveyID)
{
    // Unset the session
    unset($_SESSION['survey_'.$iSurveyID]);
    // Force EM to refresh
    LimeExpressionManager::SetDirtyFlag();
}

/**
* Resets all question timers by expiring the related cookie - this needs to be called before any output is done
* @todo Make cookie survey ID aware
*/
function resetTimers()
{
    $cookie=new CHttpCookie('limesurvey_timers', '');
    $cookie->expire = time()- 3600;
    Yii::app()->request->cookies['limesurvey_timers'] = $cookie;
}

/**
* Set the public survey language
* Control if language exist in this survey, else set to survey default language
* if $surveyid <= 0 : set the language to default site language
* @param int $surveyid
* @param string $sLanguage
*/
function SetSurveyLanguage($surveyid, $sLanguage)
{
    $surveyid=sanitize_int($surveyid);
    $default_language = Yii::app()->getConfig('defaultlang');

    if (isset($surveyid) && $surveyid>0)
    {
        $default_survey_language= Survey::model()->findByPk($surveyid)->language;
        $additional_survey_languages = Survey::model()->findByPk($surveyid)->getAdditionalLanguages();
        if (
            empty($sLanguage)                                       //check if there
            || (!in_array($sLanguage, $additional_survey_languages))  //Is the language in the survey-language array
            || ($default_survey_language == $sLanguage)              //Is the $default_language the chosen language?
         )
        {
            // Language not supported, fall back to survey's default language
            $_SESSION['survey_'.$surveyid]['s_lang'] = $default_survey_language;
        } else {
            $_SESSION['survey_'.$surveyid]['s_lang'] =  $sLanguage;
        }
        App()->setLanguage($_SESSION['survey_'.$surveyid]['s_lang']);
        $thissurvey=getSurveyInfo($surveyid, @$_SESSION['survey_'.$surveyid]['s_lang']);
        Yii::app()->loadHelper('surveytranslator');
        LimeExpressionManager::SetEMLanguage($_SESSION['survey_'.$surveyid]['s_lang']);
    }
    else
    {
        if(!$sLanguage)
        {
            $sLanguage=$default_language;
        }
        $_SESSION['survey_'.$surveyid]['s_lang'] = $sLanguage;
        App()->setLanguage($_SESSION['survey_'.$surveyid]['s_lang']);
    }

}

/**
* getMove get move button clicked
**/
function getMove()
{
    $aAcceptedMove=array('default','movenext','movesubmit','moveprev','saveall','loadall','clearall','changelang');
    // We can control is save and load are OK : todo fix according to survey settings
    // Maybe allow $aAcceptedMove in Plugin
    $move=Yii::app()->request->getParam('move');
    foreach($aAcceptedMove as $sAccepteMove)
    {
        if(Yii::app()->request->getParam($sAccepteMove))
            $move=$sAccepteMove;
    }
    if($move=='clearall' && App()->request->getPost('confirm-clearall')!='confirm'){
            $move="clearcancel";
    }
    if($move=='default')
    {
        $surveyid=Yii::app()->getConfig('surveyID');
        $thissurvey=getsurveyinfo($surveyid);
        $iSessionStep=(isset($_SESSION['survey_'.$surveyid]['step']))?$_SESSION['survey_'.$surveyid]['step']:false;
        $iSessionTotalSteps=(isset($_SESSION['survey_'.$surveyid]['totalsteps']))?$_SESSION['survey_'.$surveyid]['totalsteps']:false;
        if ($iSessionStep && ($iSessionStep == $iSessionTotalSteps)|| $thissurvey['format'] == 'A')
        {
            $move="movesubmit";
        }
        else
        {
            $move="movenext";
        }
    }
    return $move;
}

/**
 * Get the margin class for side-body div depending
 * on side-menu behaviour config and page (edit or not
 * etc).
 *
 * @param boolean $sideMenustate - False for pages with collapsed side-menu
 * @return string
 */
function getSideBodyClass($sideMenustate = false)
{
    $sideMenuBehaviour = getGlobalSetting('sideMenuBehaviour');

    $class = "";

    if ($sideMenuBehaviour == 'adaptive' || $sideMenuBehaviour == '')
    {
        // Adaptive and closed, as in edit question
        if (!$sideMenustate)
        {
            $class = 'side-body-margin';
        }
    }
    elseif ($sideMenuBehaviour == 'alwaysClosed')
    {
        $class = 'side-body-margin';
    }
    elseif ($sideMenuBehaviour == 'alwaysOpen')
    {
        // No margin class
    }
    else
    {
        throw new \CException("Unknown value for sideMenuBehaviour: $sideMenuBehaviour");
    }

    return $class;
}


/**
 * Render the question view.
 *
 * By default, it just renders the required core view from application/views/survey/...
 * If the Survey template is configured to overwrite the question views, then the function will check if the required view exist in the template directory
 * and then will use this one to render the question.
 *
 * @param string    $sView      name of the view to be rendered.
 * @param array     $aData      data to be extracted into PHP variables and made available to the view script
 * @param boolean   $bReturn    whether the rendering result should be returned instead of being displayed to end users (should be always true)
 */
 function doFRender($sView, $aData, $bReturn=true)
{
    global $thissurvey;
    if(isset($thissurvey['template']))
    {
        $sTemplate = $thissurvey['template'];
        $oTemplate = Template::model()->getInstance($sTemplate);                // we get the template configuration
        if($oTemplate->overwrite_question_views===true && Yii::app()->getConfig('allow_templates_to_overwrite_views'))                         // If it's configured to overwrite the views
        {
            $requiredView = $oTemplate->viewPath.ltrim($sView, '/');            // Then we check if it has its own version of the required view
            if( file_exists($requiredView.'.php') )                             // If it the case, the function will render this view
            {
                Yii::setPathOfAlias('survey.template.view', $requiredView);     // to render a view from an absolute path outside of application/, path alias must be used.
                $sView = 'survey.template.view';                                // See : http://www.yiiframework.com/doc/api/1.1/CController#getViewFile-detail
            }
        }
    }
    return Yii::app()->getController()->renderPartial($sView, $aData, $bReturn);
}

/**
 * For later use, don't remove.
 * @return array<string>
 */
function cookieConsentLocalization()
{
    return array(
        gT('This website uses cookies. By continuing this survey you approve the data protection policy of the service provider.'),
        gT('OK'),
        gT('View policy'),
        gT('Please be patient until you are forwarded to the final URL.')
    );
}
