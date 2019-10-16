<?php
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
* Sends email to tokens - invitations, reminders, registers, and confirmations
*
* @param integer $iSurveyID
* @param CActiveRecord[]  $aResultTokens
* @param string $sType type of notification invite|register|remind|confirm
* @return array of results
*/
function emailTokens($iSurveyID, $aResultTokens, $sType)
{
    Yii::app()->loadHelper('common');
    $oSurvey = Survey::model()->findByPk($iSurveyID);
    if (getEmailFormat($iSurveyID) == 'html') {
            $bHtml = true;
    } else {
            $bHtml = false;
    }

    $attributes = array_keys(getTokenFieldsAndNames($iSurveyID));
    $oSurveyLocale = SurveyLanguageSetting::model()->findAllByAttributes(array('surveyls_survey_id' => $iSurveyID));
    $oTokens = Token::model($iSurveyID);
    $aSurveyLangs = $oSurvey->additionalLanguages;
    array_unshift($aSurveyLangs, $oSurvey->language);

    //Convert result to associative array to minimize SurveyLocale access attempts
    foreach ($oSurveyLocale as $rows) {
        $oTempObject = array();
        foreach ($rows as $k=>$v) {
            $oTempObject[$k] = $v;
        }
        $aSurveyLocaleData[$rows['surveyls_language']] = $oTempObject;
    }

    foreach ($aResultTokens as $aTokenRow) {
        //Select language
        $aTokenRow['language'] = trim($aTokenRow['language']);
        $found = array_search($aTokenRow['language'], $aSurveyLangs);
        if ($aTokenRow['language'] == '' || $found == false) {
            $aTokenRow['language'] = $oSurvey->language;
        }
        $sTokenLanguage = $aTokenRow['language'];


        //Build recipient
        $to = array();
        $aEmailaddresses = explode(';', $aTokenRow['email']);
        foreach ($aEmailaddresses as $sEmailaddress) {
            $to[] = ($aTokenRow['firstname']." ".$aTokenRow['lastname']." <{$sEmailaddress}>");
        }


        //Populate attributes
        $fieldsarray["{SURVEYNAME}"] = $aSurveyLocaleData[$sTokenLanguage]['surveyls_title'];
        if ($fieldsarray["{SURVEYNAME}"] == '') {
                    $fieldsarray["{SURVEYNAME}"] = $aSurveyLocaleData[$oSurvey['language']]['surveyls_title'];
        }

        $fieldsarray["{SURVEYDESCRIPTION}"] = $aSurveyLocaleData[$sTokenLanguage]['surveyls_description'];
        if ($fieldsarray["{SURVEYDESCRIPTION}"] == '') {
                    $fieldsarray["{SURVEYDESCRIPTION}"] = $aSurveyLocaleData[$oSurvey['language']]['surveyls_description'];
        }

        $fieldsarray["{ADMINNAME}"] = $oSurvey->admin;
        $fieldsarray["{ADMINEMAIL}"] = $oSurvey->adminemail;
        $fieldsarray["{EXPIRY}"] = $oSurvey->expires;
        if (empty($fieldsarray["{ADMINEMAIL}"])) {
                    $fieldsarray["{ADMINEMAIL}"] = Yii::app()->getConfig('siteadminemail');
        }
        $from = $fieldsarray["{ADMINNAME}"].' <'.$fieldsarray["{ADMINEMAIL}"].'>';

        foreach ($attributes as $attributefield) {
            $fieldsarray['{'.strtoupper($attributefield).'}'] = $aTokenRow[$attributefield];
            $fieldsarray['{TOKEN:'.strtoupper($attributefield).'}'] = $aTokenRow[$attributefield];
        }

        //create urls
        $fieldsarray["{OPTOUTURL}"] = Yii::app()->getController()->createAbsoluteUrl("/optout/tokens", array("surveyid"=>$iSurveyID, "langcode"=>trim($aTokenRow['language']), "token"=>$aTokenRow['token']));
        $fieldsarray["{OPTINURL}"]  = Yii::app()->getController()->createAbsoluteUrl("/optin/tokens", array("surveyid"=>$iSurveyID, "langcode"=>trim($aTokenRow['language']), "token"=>$aTokenRow['token']));
        $fieldsarray["{SURVEYURL}"] = Yii::app()->getController()->createAbsoluteUrl("/survey/index", array("sid"=>$iSurveyID, "token"=>$aTokenRow['token'], "lang"=>trim($aTokenRow['language'])));
        $aBareboneURLs = [];
        if ($bHtml) {
            foreach (array('OPTOUT', 'OPTIN', 'SURVEY') as $key) {
                $url = $fieldsarray["{{$key}URL}"];
                $fieldsarray["{{$key}URL}"] = "<a href='{$url}'>".htmlspecialchars($url).'</a>';
                $aBareboneURLs['@@'.$key.'URL@@'] = $url;
            }
        }

        $fieldsarray["{SID}"] = $iSurveyID;

        //mail headers
        $customheaders = array('1' => "X-surveyid: ".$iSurveyID, '2' => "X-tokenid: ".$fieldsarray["{TOKEN}"]);

        global $maildebug;

        //choose appriopriate email message
        switch ($sType) {
            case 'invite':
                $sSubject = $aSurveyLocaleData[$sTokenLanguage]['surveyls_email_invite_subj'];
                $sMessage = $aSurveyLocaleData[$sTokenLanguage]['surveyls_email_invite'];
                break;
            case 'remind':
                $sSubject = $aSurveyLocaleData[$sTokenLanguage]['surveyls_email_remind_subj'];
                $sMessage = $aSurveyLocaleData[$sTokenLanguage]['surveyls_email_remind'];
                break;
            case 'register':
                $sSubject = $aSurveyLocaleData[$sTokenLanguage]['surveyls_email_register_subj'];
                $sMessage = $aSurveyLocaleData[$sTokenLanguage]['surveyls_email_register'];
                break;
            case 'confirm':
                $sSubject = $aSurveyLocaleData[$sTokenLanguage]['surveyls_email_confirm_subj'];
                $sMessage = $aSurveyLocaleData[$sTokenLanguage]['surveyls_email_confirm'];
                break;
            default:
                throw new Exception('Invalid template name');
        }
		
        $modsubject = $sSubject;
        $modmessage = $sMessage;
		
        foreach ($aBareboneURLs as $sSearch=>$sReplace) {
            $modsubject = str_replace($sSearch, $sReplace, $modsubject);
            $modmessage = str_replace($sSearch, $sReplace, $modmessage);
        }

        $modsubject = ReplaceFields($modsubject, $fieldsarray);
        $modmessage = ReplaceFields($modmessage, $fieldsarray);

        if (isset($aTokenRow['validfrom']) && trim($aTokenRow['validfrom']) != '' && convertDateTimeFormat($aTokenRow['validfrom'], 'Y-m-d H:i:s', 'U') * 1 > date('U') * 1) {
            $aResult[$aTokenRow['tid']] = array('name'=>$fieldsarray["{FIRSTNAME}"]." ".$fieldsarray["{LASTNAME}"],
                                                'email'=>$fieldsarray["{EMAIL}"],
                                                'status'=>'fail',
                                                'error'=>'Token not valid yet');

        } elseif (isset($aTokenRow['validuntil']) && trim($aTokenRow['validuntil']) != '' && convertDateTimeFormat($aTokenRow['validuntil'], 'Y-m-d H:i:s', 'U') * 1 < date('U') * 1) {
            $aResult[$aTokenRow['tid']] = array('name'=>$fieldsarray["{FIRSTNAME}"]." ".$fieldsarray["{LASTNAME}"],
                                                'email'=>$fieldsarray["{EMAIL}"],
                                                'status'=>'fail',
                                                'error'=>'Token not valid anymore');

        } else {
            if (SendEmailMessage($modmessage, $modsubject, $to, $from, Yii::app()->getConfig("sitename"), $bHtml, getBounceEmail($iSurveyID), null, $customheaders)) {
                $aResult[$aTokenRow['tid']] = array('name'=>$fieldsarray["{FIRSTNAME}"]." ".$fieldsarray["{LASTNAME}"],
                                                    'email'=>$fieldsarray["{EMAIL}"],
                                                    'status'=>'OK');

                if ($sType == 'invite' || $sType == 'register') {
                                    $oTokens->updateByPk($aTokenRow['tid'], array('sent' => dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig("timeadjust"))));
                }

                if ($sType == 'remind') {
                    $iRCount = $oTokens->findByPk($aTokenRow['tid'])->remindercount + 1;
                    $oTokens->updateByPk($aTokenRow['tid'], array('remindersent' => dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig("timeadjust"))));
                    $oTokens->updateByPk($aTokenRow['tid'], array('remindercount' => $iRCount));
                    }

            } else {

                $aResult[$aTokenRow['tid']] = array('name'=>$fieldsarray["{FIRSTNAME}"]." ".$fieldsarray["{LASTNAME}"],
                                                    'email'=>$fieldsarray["{EMAIL}"],
                                                    'status'=>'fail',
                                                    'error'=>$maildebug);
            }
        }

        unset($fieldsarray);
    }


    return $aResult;
}



