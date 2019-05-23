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
*
//Security Checked: POST, GET, SESSION, REQUEST, returnGlobal, DB

Redesigned 7/25/2006 - swales

1.  Save Feature (// --> START NEW FEATURE - SAVE)

How it used to work
-------------------
1. The old save method would save answers to the "survey_x" table only when the submit button was clicked.
2. If "allow saves" was turned on then answers were temporarily recorded in the "saved" table.

Why change this feature?
------------------------
If a user did not complete a survey, ALL their answers were lost since no submit (database insert) was performed.


Save Feature redesign
---------------------
Benefits
Partial survey answers are saved (provided at least Next/Prev/Last/Submit/Save so far clicked at least once).

Details.
1. The answers are saved in the "survey_x" table only.  The "saved" table is no longer used.
2. The "saved_control" table has new column (srid) that points to the "survey_x" record it corresponds to.
3. Answer are saved every time you move between pages (Next,Prev,Last,Submit, or Save so far).
4. Only the fields modified on the page are updated. A new hidden field "modfields" store which fields have changed. - REVERTED
5. Answered are reloaded from the database after the save so that if some other answers were modified by someone else
the updates would be picked up for the current page.  There is still an issue if two people modify the same
answer at the same time.. the 'last one to save' wins.
6. The survey_x datestamp field is updated every time the record is updated.
7. Template can now contain {DATESTAMP} to show the last modified date/time.
8. A new field 'submitdate' has been added to the survey_x table and is written when the submit button is clicked.
9. Save So Far now displays on Submit page. This allows the user one last chance to create a saved_control record so they
can return later.

Notes
-----
1. A new column SRID has been added to saved_control.
2. saved table no longer exists.
*/



class Save
{
    /**
     * @var string[] $aErrors :list of errors when try saving
     */
    public $aSaveErrors = array();
    /**
     * Show the save form
     * @param integer $iSurveyId
     *
     * @return void
     */
    function showsaveform($iSurveyId)
    {
        //Show 'SAVE FORM' only when click the 'Save so far' button the first time, or when duplicate is found on SAVE FORM.
        //~ global $errormsg, $thissurvey, $surveyid, $clienttoken, $thisstep;
        $thisstep    = isset($_SESSION['survey_'.$iSurveyId]['step']) ? $_SESSION['survey_'.$iSurveyId]['step'] : 0;
        $clienttoken = isset($_SESSION['survey_'.$iSurveyId]['token']) ? $_SESSION['survey_'.$iSurveyId]['token'] : '';

        $aData = array(
            'surveyid'=>$iSurveyId,
            'clienttoken'=>$clienttoken, // send in caller, call only one time
        );

        $oSurvey   = Survey::model()->findByPk($iSurveyId);
        $sTemplate = $oSurvey->template;
        $oTemplate = Template::model()->getInstance($sTemplate);

        $aReplacements['SAVEHEADING'] = App()->getController()->renderPartial("/survey/frontpage/saveForm/heading", array(), true);
        $aReplacements['SAVEMESSAGE'] = App()->getController()->renderPartial("/survey/frontpage/saveForm/message", array(), true);

        if ($oSurvey->anonymized == "Y") {
            $aReplacements['SAVEALERT'] = App()->getController()->renderPartial("/survey/frontpage/saveForm/anonymized", array(), true);
        }

        if (!empty($this->aSaveErrors)) {
            $aReplacements['SAVEERROR'] = App()->getController()->renderPartial("/survey/frontpage/saveForm/error", array('aSaveErrors'=>$this->aSaveErrors), true);
        } else {
                $aReplacements['SAVEERROR'] = "";
        }

        /* Construction of the form */
        if (isCaptchaEnabled('saveandloadscreen', Survey::model()->findByPk($iSurveyId)->usecaptcha)) {
            $captcha = Yii::app()->getController()->createUrl('/verification/image', array('sid'=>$iSurveyId));
        } else {
            $captcha = null;
        }

        $saveForm  = CHtml::beginForm(array("/survey/index", "sid"=>$iSurveyId), 'post', array('id'=>'form-save', 'class'=>'ls-form'));
        $saveForm .= CHtml::hiddenField('savesubmit', 'save');
        $saveForm .= App()->getController()->renderPartial("/survey/frontpage/saveForm/form", array('captcha'=>$captcha), true);

        if ($clienttoken) {
            $saveForm .= CHtml::hiddenField('token', $clienttoken);
        }

        $saveForm .= CHtml::endForm();

        $aReplacements['SAVEFORM'] = $saveForm;
        $content                   = templatereplace(file_get_contents($oTemplate->pstplPath."save.pstpl"), $aReplacements, $aData);

        App()->getController()->layout           = "survey";
        App()->getController()->sTemplate        = $sTemplate;
        App()->getController()->aGlobalData      = $aData;
        App()->getController()->aReplacementData = $aReplacements;

        App()->getController()->render("/survey/system/display", array(
            'content'=>$content,
        ));
        Yii::app()->end();
    }


    function getSaveFormDatas($iSurveyId)
    {
        //Show 'SAVE FORM' only when click the 'Save so far' button the first time, or when duplicate is found on SAVE FORM.
        //~ global $errormsg, $thissurvey, $surveyid, $clienttoken, $thisstep;
        $thisstep    = isset($_SESSION['survey_'.$iSurveyId]['step']) ? $_SESSION['survey_'.$iSurveyId]['step'] : 0;
        $clienttoken = isset($_SESSION['survey_'.$iSurveyId]['token']) ? $_SESSION['survey_'.$iSurveyId]['token'] : '';

        $oSurvey   = Survey::model()->findByPk($iSurveyId);
        $sTemplate = $oSurvey->template;
        $oTemplate = Template::model()->getInstance($sTemplate);


        $aSaveForm['aErrors'] = $this->aSaveErrors;

        /* Construction of the form */
        $aSaveForm['aCaptcha']['show'] = false;
        if (isCaptchaEnabled('saveandloadscreen', Survey::model()->findByPk($iSurveyId)->usecaptcha)) {
            $aSaveForm['aCaptcha']['show'] = true;
            $aSaveForm['aCaptcha']['sImageUrl'] = Yii::app()->getController()->createUrl('/verification/image', array('sid'=>$iSurveyId));
        }

        $aSaveForm['sHiddenField'] = CHtml::hiddenField('thisstep', $thisstep);
        $aSaveForm['sHiddenField'] .= CHtml::hiddenField('savesubmit', 'save');

        if ($clienttoken) {
            $aSaveForm['sHiddenField'] .= CHtml::hiddenField('token', $clienttoken);
        }

        return $aSaveForm;
    }

    function savedcontrol()
    {
        //This data will be saved to the "saved_control" table with one row per response.
        // - a unique "saved_id" value (autoincremented)
        // - the "sid" for this survey
        // - the "srid" for the survey_x row id
        // - "saved_thisstep" which is the step the user is up to in this survey
        // - "saved_ip" which is the ip address of the submitter
        // - "saved_date" which is the date ofthe saved response
        // - an "identifier" which is like a username
        // - a "password"
        // - "fieldname" which is the fieldname of the saved response
        // - "value" which is the value of the response
        //We start by generating the first 5 values which are consistent for all rows.

        global $surveyid, $thissurvey, $errormsg, $publicurl, $sitename, $clienttoken, $thisstep;

        $timeadjust = getGlobalSetting('timeadjust');
        $survey = Survey::model()->findByPk($surveyid);


        //Check that the required fields have been completed.
        $errormsg = '';
        if (!Yii::app()->request->getPost('savename')) {
            $this->aSaveErrors[] = gT("You must supply a name for this saved session.");
        }
        if (!Yii::app()->request->getPost('savepass')) {
            $this->aSaveErrors[] = gT("You must supply a password for this saved session.");
        }
        if (Yii::app()->request->getPost('savepass') != Yii::app()->request->getPost('savepass2')) {
            $this->aSaveErrors[] = gT("Your passwords do not match.");
        }

        // if security question asnwer is incorrect
        if (isCaptchaEnabled('saveandloadscreen', $thissurvey['usecaptcha'])) {
            if (!Yii::app()->request->getPost('loadsecurity')
             || !isset($_SESSION['survey_'.$surveyid]['secanswer'])
             || Yii::app()->request->getPost('loadsecurity') != $_SESSION['survey_'.$surveyid]['secanswer']) {
                $this->aSaveErrors[] = gT("The answer to the security question is incorrect.");
            }
        }

        if (!empty($this->aSaveErrors)) {
            return;
        }
        $saveName = Yii::app()->request->getPost('savename');
        $duplicate = SavedControl::model()->findByAttributes(array('sid' => $surveyid, 'identifier' => $saveName));
        if (strpos($saveName, '/') !== false || strpos($saveName, '/') !== false || strpos($saveName, '&') !== false || strpos($saveName, '&') !== false
            || strpos($saveName, '\\') !== false || strpos($saveName, '\\') !== false) {
            $this->aSaveErrors[] = gT("You may not use slashes or ampersands in your name or password.");
            return;
        } elseif (!empty($duplicate) && $duplicate->count() > 0) {
// OK - AR count
            $this->aSaveErrors[] = gT("This name has already been used for this survey. You must use a unique save name.");
            return;
        } elseif (!empty($_POST['saveemail']) && !validateEmailAddress($_POST['saveemail'])) {
// Check if the email address is valid
            $errormsg .= gT("This is not a valid email address. Please provide a valid email address or leave it empty.")."<br />\n";
            return;
        } else {
            //INSERT BLANK RECORD INTO "survey_x" if one doesn't already exist
            if (!isset($_SESSION['survey_'.$surveyid]['srid'])) {
                $today = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust);
                $sdata = array(
                    "datestamp" => $today,
                    "ipaddr" => getIPAddress(),
                    "startlanguage" => $_SESSION['survey_'.$surveyid]['s_lang'],
                    "refurl" => ((isset($_SESSION['survey_'.$surveyid]['refurl'])) ? $_SESSION['survey_'.$surveyid]['refurl'] : getenv('HTTP_REFERER'))
                );
                if (SurveyDynamic::model($thissurvey['sid'])->insert($sdata)) {
                    $srid = getLastInsertID($survey->responsesTableName);
                    $_SESSION['survey_'.$surveyid]['srid'] = $srid;
                } else {
                    safeDie("Unable to insert record into survey table.<br /><br />");
                }
            }
            //CREATE ENTRY INTO "saved_control"
            $today = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust);
            $saved_control = new SavedControl;
            $saved_control->sid = $surveyid;
            $saved_control->srid = $_SESSION['survey_'.$surveyid]['srid'];
            $saved_control->identifier = $_POST['savename']; // Binding does escape, so no quoting/escaping necessary
            $saved_control->access_code = hash('sha256', $_POST['savepass']);
            $saved_control->email = $_POST['saveemail'];
            $saved_control->ip = ($thissurvey['ipaddr'] == 'Y') ?getIPAddress() : '';
            $saved_control->saved_thisstep = $thisstep;
            $saved_control->status = 'S';
            $saved_control->saved_date = $today;
            if (isset($_SESSION['survey_'.$surveyid]['refurl'])) {
                $saved_control->refurl = $_SESSION['survey_'.$surveyid]['refurl'];
            } else {
                $saved_control->refurl = getenv("HTTP_REFERER");
            }

            if ($saved_control->save()) {
                $scid = getLastInsertID('{{saved_control}}');
                $_SESSION['survey_'.$surveyid]['scid'] = $scid;
            } else {
                safeDie("Unable to insert record into saved_control table.<br /><br />");
            }

            $_SESSION['survey_'.$surveyid]['holdname'] = $_POST['savename']; //Session variable used to load answers every page. Unsafe - so it has to be taken care of on output
            $_SESSION['survey_'.$surveyid]['holdpass'] = $_POST['savepass']; //Session variable used to load answers every page. Unsafe - so it has to be taken care of on output

            //Email if needed
            if (isset($_POST['saveemail']) && validateEmailAddress($_POST['saveemail'])) {
                $subject  = gT("Saved Survey Details")." - ".$thissurvey['name'];
                $message  = gT("Thank you for saving your survey in progress.  The following details can be used to return to this survey and continue where you left off.  Please keep this e-mail for your reference - we cannot retrieve the password for you.");
                $message .= "\n\n".$thissurvey['name']."\n\n";
                $message .= gT("Name").": ".$_POST['savename']."\n";
                $message .= gT("Password").": ".$_POST['savepass']."\n\n";
                $message .= gT("Reload your survey by clicking on the following link (or pasting it into your browser):")."\n";
                $aParams = array('scid'=>$scid, 'lang'=>App()->language, 'loadname'=>$_POST['savename'], 'loadpass'=>$_POST['savepass']);
                if (!empty($clienttoken)) {
                    $aParams['token'] = $clienttoken;
                }
                $message .= Yii::app()->getController()->createAbsoluteUrl("/survey/index/sid/{$surveyid}/loadall/reload", $aParams);

                $from = "{$thissurvey['adminname']} <{$thissurvey['adminemail']}>";
                if (SendEmailMessage($message, $subject, $_POST['saveemail'], $from, $sitename, false, getBounceEmail($surveyid))) {
                    $emailsent = "Y";
                } else {
                    $errormsg .= gT('Error: Email failed, this may indicate a PHP Mail Setup problem on the server. Your survey details have still been saved, however you will not get an email with the details. You should note the "name" and "password" you just used for future reference.');
                    if (trim($thissurvey['adminemail']) == '') {
                        $errormsg .= gT('(Reason: Administrator email address empty)');
                    }
                }
            }
            return gT('Your survey was successfully saved.');
        }
    }

    /**
     * Clone of savesubmit() but returns datas for twig
     */
    function saveSurvey()
    {
        //This data will be saved to the "saved_control" table with one row per response.
        // - a unique "saved_id" value (autoincremented)
        // - the "sid" for this survey
        // - the "srid" for the survey_x row id
        // - "saved_thisstep" which is the step the user is up to in this survey
        // - "saved_ip" which is the ip address of the submitter
        // - "saved_date" which is the date ofthe saved response
        // - an "identifier" which is like a username
        // - a "password"
        // - "fieldname" which is the fieldname of the saved response
        // - "value" which is the value of the response
        //We start by generating the first 5 values which are consistent for all rows.

        global $surveyid, $thissurvey, $errormsg, $publicurl, $sitename, $clienttoken, $thisstep;
        $survey = Survey::model()->findByPk($surveyid);

        $aSaveForm  = array();
        $timeadjust = getGlobalSetting('timeadjust');

        // Check that the required fields have been completed.
        $errormsg = '';
        if (!Yii::app()->request->getPost('savename')) {
            $this->aSaveErrors[] = gT("You must supply a name for this saved session.");
        }
        if (!Yii::app()->request->getPost('savepass')) {
            $this->aSaveErrors[] = gT("You must supply a password for this saved session.");
        }
        if (Yii::app()->request->getPost('savepass') != Yii::app()->request->getPost('savepass2')) {
            $this->aSaveErrors[] = gT("Your passwords do not match.");
        }

        $saveName  = Yii::app()->request->getPost('savename');
        $duplicate = SavedControl::model()->findByAttributes(array('sid' => $surveyid, 'identifier' => $saveName));

        // Check name
        if (strpos($saveName, '/') !== false || strpos($saveName, '/') !== false || strpos($saveName, '&') !== false || strpos($saveName, '&') !== false
            || strpos($saveName, '\\') !== false || strpos($saveName, '\\') !== false) {

            $this->aSaveErrors[] = gT("You may not use slashes or ampersands in your name or password.");

        } elseif (!empty($duplicate) && $duplicate->count() > 0) {

            $this->aSaveErrors[] = gT("This name has already been used for this survey. You must use a unique save name.");
        }

        // Check captcha
        if (isCaptchaEnabled('saveandloadscreen', $thissurvey['usecaptcha'])) {

            if (!Yii::app()->request->getPost('loadsecurity')
                || !isset($_SESSION['survey_'.$surveyid]['secanswer'])
                || Yii::app()->request->getPost('loadsecurity') != $_SESSION['survey_'.$surveyid]['secanswer']
            ) {
                $this->aSaveErrors[] = gT("The answer to the security question is incorrect.");
            }
        }

        if (empty($this->aSaveErrors)) {
            //INSERT BLANK RECORD INTO "survey_x" if one doesn't already exist

            if (!isset($_SESSION['survey_'.$surveyid]['srid'])) {
                $today = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust);
                $sdata = array(
                    "datestamp"     => $today,
                    "ipaddr"        => getIPAddress(),
                    "startlanguage" => $_SESSION['survey_'.$surveyid]['s_lang'],
                    "refurl"        => ((isset($_SESSION['survey_'.$surveyid]['refurl'])) ? $_SESSION['survey_'.$surveyid]['refurl'] : getenv('HTTP_REFERER'))
                );

                if (SurveyDynamic::model($thissurvey['sid'])->insert($sdata)) {
                    $srid                                  = getLastInsertID($survey->responsesTableName);
                    $_SESSION['survey_'.$surveyid]['srid'] = $srid;
                } else {
                    // TODO: $this->aSaveErrors
                    $this->aSaveErrors[] = "Unable to insert record into survey table.";
                }
            }

            //CREATE ENTRY INTO "saved_control"
            $saved_control                 = new SavedControl;
            $saved_control->sid            = $surveyid;
            $saved_control->srid           = $_SESSION['survey_'.$surveyid]['srid'];
            $saved_control->identifier     = $_POST['savename']; // Binding does escape, so no quoting/escaping necessary
            $saved_control->access_code    = hash('sha256', $_POST['savepass']);
            $saved_control->email          = $_POST['saveemail'];
            $saved_control->ip             = ($thissurvey['ipaddr'] == 'Y') ?getIPAddress() : '';
            $saved_control->saved_thisstep = $thisstep;
            $saved_control->status         = 'S';
            $saved_control->saved_date     = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust);

            if (isset($_SESSION['survey_'.$surveyid]['refurl'])) {
                $saved_control->refurl = $_SESSION['survey_'.$surveyid]['refurl'];
            } else {
                $saved_control->refurl = getenv("HTTP_REFERER");
            }

            if ($saved_control->save()) {
                $scid                                  = getLastInsertID('{{saved_control}}');
                $_SESSION['survey_'.$surveyid]['scid'] = $scid;
            } else {
                // TODO: $this->aSaveErrors
                $this->aSaveErrors[] = "Unable to insert record into saved_control table.";
            }

            $_SESSION['survey_'.$surveyid]['holdname'] = $_POST['savename']; //Session variable used to load answers every page. Unsafe - so it has to be taken care of on output
            $_SESSION['survey_'.$surveyid]['holdpass'] = $_POST['savepass']; //Session variable used to load answers every page. Unsafe - so it has to be taken care of on output

            //Email if needed
            if (isset($_POST['saveemail']) && validateEmailAddress($_POST['saveemail'])) {

                $subject  = gT("Saved Survey Details")." - ".$thissurvey['name'];
                $message  = gT("Thank you for saving your survey in progress.  The following details can be used to return to this survey and continue where you left off.  Please keep this e-mail for your reference - we cannot retrieve the password for you.");
                $message .= "\n\n".$thissurvey['name']."\n\n";
                $message .= gT("Name").": ".$_POST['savename']."\n";
                $message .= gT("Password").": ".$_POST['savepass']."\n\n";
                $message .= gT("Reload your survey by clicking on the following link (or pasting it into your browser):")."\n";
                $aParams  = array('scid'=>$scid, 'lang'=>App()->language, 'loadname'=>$_POST['savename'], 'loadpass'=>$_POST['savepass']);

                if (!empty($clienttoken)) {
                    $aParams['token'] = $clienttoken;
                }

                $message .= Yii::app()->getController()->createAbsoluteUrl("/survey/index/sid/{$surveyid}/loadall/reload", $aParams);
                $from     = "{$thissurvey['adminname']} <{$thissurvey['adminemail']}>";

                if (SendEmailMessage($message, $subject, $_POST['saveemail'], $from, $sitename, false, getBounceEmail($surveyid))) {
                    $emailsent = "Y";
                } else {

                    $errormsg .= gT('Error: Email failed, this may indicate a PHP Mail Setup problem on the server. Your survey details have still been saved, however you will not get an email with the details. You should note the "name" and "password" you just used for future reference.');
                    if (trim($thissurvey['adminemail']) == '') {
                        $errormsg .= gT('(Reason: Administrator email address empty)');
                    }
                }
            }

            return array('success' => true, 'message' => gT('Your survey was successfully saved.'));
        }

        if (!empty($this->aSaveErrors)) {
            $aSaveForm['success']     = false;
            $aSaveForm['aSaveErrors'] = $this->aSaveErrors;
        }

        return $aSaveForm;
    }

    /**
     * This functions saves the answer time for question/group and whole survey.
     * [ It compares current time with the time in $_POST['start_time'] ]
     * The times are saved in table: {prefix}{surveytable}_timings
     * @return void
     */
    function set_answer_time()
    {
        global $thissurvey;
        $survey = Survey::model()->findByPk($thissurvey['sid']);

        if (!isset($_POST['start_time'])) {
            return; // means haven't passed welcome page yet.
        }

        if (isset($_POST['lastanswer'])) {
            $setField = $_POST['lastanswer'];
        } elseif (isset($_POST['lastgroup'])) {
            $setField = $_POST['lastgroup'];
        }
        $passedTime = str_replace(',', '.', round(microtime(true) - $_POST['start_time'], 2));
        if (!isset($setField)) {
//we show the whole survey on one page - we don't have to save time for group/question
            $query = "UPDATE ".$survey->timingsTableName." SET "
            ."interviewtime = (CASE WHEN interviewtime IS NULL THEN 0 ELSE interviewtime END) + ".$passedTime
            ." WHERE id = ".$_SESSION['survey_'.$thissurvey['sid']]['srid'];

        } else {
            $aColumnNames = SurveyTimingDynamic::model($thissurvey['sid'])->getTableSchema()->columnNames;
            $setField .= "time";
            if (!in_array($setField, $aColumnNames)) {
                die('Invalid last group timing fieldname');
            }
            $setField = Yii::app()->db->quoteColumnName($setField);
            $query = "UPDATE ".$survey->timingsTableName." SET "
            ."interviewtime =  (CASE WHEN interviewtime IS NULL THEN 0 ELSE interviewtime END) + ".$passedTime.","
            .$setField." =  (CASE WHEN $setField IS NULL THEN 0 ELSE $setField END) + ".$passedTime
            ." WHERE id = ".$_SESSION['survey_'.$thissurvey['sid']]['srid'];
        }
        Yii::app()->db->createCommand($query)->execute();
    }
}
