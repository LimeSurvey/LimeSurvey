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
     * @var null|string[] $saveData : data when save try to submit save form
     */
    public $saveData = null;

    function getSaveFormDatas($iSurveyId)
    {
        //Show 'SAVE FORM' only when click the 'Save so far' button the first time, or when duplicate is found on SAVE FORM.
        //~ global $errormsg, $thissurvey, $surveyid, $clienttoken, $thisstep;
        $thisstep    = $_SESSION['survey_' . $iSurveyId]['step'] ?? 0;
        $clienttoken = $_SESSION['survey_' . $iSurveyId]['token'] ?? '';

        $aSaveForm['aErrors'] = $this->aSaveErrors;
        $this->launchSaveFormEvent($iSurveyId);
        /* Construction of the form */
        $aSaveForm['aCaptcha']['show'] = false;
        if (isCaptchaEnabled('saveandloadscreen', Survey::model()->findByPk($iSurveyId)->usecaptcha)) {
            $aSaveForm['aCaptcha']['show'] = true;
            $aSaveForm['aCaptcha']['sImageUrl'] = Yii::app()->getController()->createUrl('/verification/image', array('sid' => $iSurveyId));
        }

        $aSaveForm['sHiddenField'] = CHtml::hiddenField('thisstep', $thisstep);
        $aSaveForm['sHiddenField'] .= CHtml::hiddenField('savesubmit', 'save');

        if ($clienttoken) {
            $aSaveForm['sHiddenField'] .= CHtml::hiddenField('token', $clienttoken);
        }

        return $aSaveForm;
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
        $this->saveData = array(
            'identifier'  => App()->request->getPost('savename'),
            'email' => App()->request->getPost('saveemail'),
            'clearpassword' => App()->request->getPost('savepass'),
            'clearpasswordconfirm' => App()->request->getPost('savepass2'),
        );

        // Check that the required fields have been completed.
        $errormsg = '';
        if (empty($this->saveData['identifier'])) {
            $this->aSaveErrors[] = gT("You must supply a name for this saved session.");
        }
        if (empty($this->saveData['clearpassword'])) {
            $this->aSaveErrors[] = gT("You must supply a password for this saved session.");
        }
        if ($this->saveData['clearpassword'] != $this->saveData['clearpasswordconfirm']) {
            $this->aSaveErrors[] = gT("Your passwords do not match.");
        }
        $duplicate = SavedControl::model()->findByAttributes(array('sid' => $surveyid, 'identifier' => $this->saveData['identifier']));
        // Check name
        if (
            strpos((string) $this->saveData['identifier'], '/') !== false || strpos((string) $this->saveData['identifier'], '/') !== false || strpos((string) $this->saveData['identifier'], '&') !== false || strpos((string) $this->saveData['identifier'], '&') !== false
            || strpos((string) $this->saveData['identifier'], '\\') !== false || strpos((string) $this->saveData['identifier'], '\\') !== false
        ) {
            $this->aSaveErrors[] = gT("You may not use slashes or ampersands in your name or password.");
        } elseif (!empty($duplicate) && $duplicate->count() > 0) {
            $this->aSaveErrors[] = gT("This name has already been used for this survey. You must use a unique save name.");
        }

        // Check captcha
        if (isCaptchaEnabled('saveandloadscreen', $thissurvey['usecaptcha'])) {
            if (
                !Yii::app()->request->getPost('loadsecurity')
                || !isset($_SESSION['survey_' . $surveyid]['secanswer'])
                || Yii::app()->request->getPost('loadsecurity') != $_SESSION['survey_' . $surveyid]['secanswer']
            ) {
                $this->aSaveErrors[] = gT("The answer to the security question is incorrect.");
            }
        }
        $this->launchSaveFormEvent($surveyid, 'validate');
        if (empty($this->aSaveErrors)) {
            //INSERT BLANK RECORD INTO "survey_x" if one doesn't already exist
            if (!isset($_SESSION['survey_' . $surveyid]['srid'])) {
                $today = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s");
                $sdata = array(
                    "datestamp"     => $today,
                    "ipaddr"        => getIPAddress(),
                    "startlanguage" => $_SESSION['survey_' . $surveyid]['s_lang'],
                    "refurl"        => ($_SESSION['survey_' . $surveyid]['refurl'] ?? getenv('HTTP_REFERER'))
                );

                if (SurveyDynamic::model($thissurvey['sid'])->insert($sdata)) {
                    $srid = getLastInsertID($survey->responsesTableName);
                    $_SESSION['survey_' . $surveyid]['srid'] = $srid;
                } else {
                    // TODO: $this->aSaveErrors
                    $this->aSaveErrors[] = "Unable to insert record into survey table.";
                }
            }

            //CREATE ENTRY INTO "saved_control"
            $saved_control                 = new SavedControl();
            $saved_control->sid            = $surveyid;
            $saved_control->srid           = $_SESSION['survey_' . $surveyid]['srid'];
            $saved_control->identifier     = $this->saveData['identifier'];
            $saved_control->access_code    = password_hash($this->saveData['clearpassword'], PASSWORD_DEFAULT);
            $saved_control->email          = $this->saveData['email'];
            $saved_control->ip             = ($thissurvey['ipaddr'] == 'Y') ? getIPAddress() : '';
            $saved_control->saved_thisstep = $thisstep;
            $saved_control->status         = 'S';
            $saved_control->saved_date     = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s");

            if (isset($_SESSION['survey_' . $surveyid]['refurl'])) {
                $saved_control->refurl = $_SESSION['survey_' . $surveyid]['refurl'];
            } else {
                $saved_control->refurl = getenv("HTTP_REFERER");
            }

            if ($saved_control->save()) {
                $scid = getLastInsertID('{{saved_control}}');
                $_SESSION['survey_' . $surveyid]['scid'] = $scid;
            } else {
                // TODO: $this->aSaveErrors
                $this->aSaveErrors[] = "Unable to insert record into saved_control table.";
            }

            $_SESSION['survey_' . $surveyid]['holdname'] = $this->saveData['identifier']; //Session variable used to load answers every page. Unsafe - so it has to be taken care of on output
            $_SESSION['survey_' . $surveyid]['holdpass'] = $this->saveData['clearpassword']; //Session variable used to load answers every page. Unsafe - so it has to be taken care of on output

            //Email if needed
            if ($this->saveData['email'] && validateEmailAddress($this->saveData['email'])) {
                $mailer = new \LimeMailer();
                $mailer->setSurvey($thissurvey['sid']);
                $mailer->emailType = 'savesurveydetails';
                $mailer->isHTML(false);
                $mailer->Subject = gT("Saved Survey Details", "unescaped") . " - " . $thissurvey['name'];
                $message  = gT("Thank you for saving your survey in progress.  The following details can be used to return to this survey and continue where you left off.  Please make sure to remember your password - we cannot retrieve it for you.", "unescaped");
                $message .= "\n\n" . $thissurvey['name'] . "\n\n";
                $message .= gT("Name", "unescaped") . ": " . Yii::app()->getRequest()->getPost('savename') . "\n";
                $message .= gT("Password", "unescaped") . ": ***************\n\n";
                $message .= gT("Reload your survey by clicking on the following link (or pasting it into your browser):", "unescaped") . "\n";
                $aParams  = array('scid' => $scid, 'lang' => App()->language);
                if (!empty($clienttoken)) {
                    $aParams['token'] = $clienttoken;
                }
                $message .= Yii::app()->getController()->createAbsoluteUrl("/survey/index/sid/{$surveyid}/loadall/reload", $aParams);
                $mailer->Body = $message;
                $mailer->addAddress($this->saveData['email']);
                if (!$mailer->sendMessage()) {
                    $errormsg .= gT('Error: Email failed, this may indicate a PHP Mail Setup problem on the server. Your survey details have still been saved, however you will not get an email with the details. You should note the "name" and "password" you just used for future reference.');
                    if (Permission::model()->hasSurveyPermission($thissurvey['sid'], 'surveysettings')) {
                        $errormsg .= sprintf(gT("Email error message %s"), $mailer->getError());
                        if (trim((string) $thissurvey['adminemail']) == '') {
                            $errormsg .= gT('(Reason: Administrator email address empty)');
                        }
                    }
                }
            }
            return array('success' => true, 'message' => gT('Your survey was successfully saved.'));
        }

        $aSaveForm['success']     = false;
        $aSaveForm['aSaveErrors'] = $this->aSaveErrors;
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
            $query = "UPDATE " . $survey->timingsTableName . " SET "
            . "interviewtime = (CASE WHEN interviewtime IS NULL THEN 0 ELSE interviewtime END) + " . $passedTime
            . " WHERE id = " . $_SESSION['survey_' . $thissurvey['sid']]['srid'];
        } else {
            $aColumnNames = SurveyTimingDynamic::model($thissurvey['sid'])->getTableSchema()->columnNames;
            $setField .= "time";
            if (!in_array($setField, $aColumnNames)) {
                die('Invalid last group timing fieldname');
            }
            $setField = Yii::app()->db->quoteColumnName($setField);
            $query = "UPDATE " . $survey->timingsTableName . " SET "
            . "interviewtime =  (CASE WHEN interviewtime IS NULL THEN 0 ELSE interviewtime END) + " . $passedTime . ","
            . $setField . " =  (CASE WHEN $setField IS NULL THEN 0 ELSE $setField END) + " . $passedTime
            . " WHERE id = " . $_SESSION['survey_' . $thissurvey['sid']]['srid'];
        }
        Yii::app()->db->createCommand($query)->execute();
    }

    /**
     * Launch the event SaveForm
     * @param $state
     * @return void
     */
    private function launchSaveFormEvent($surveyid, $state = 'show')
    {
        $saveSurveyEvent = new PluginEvent('saveSurveyForm');
        $saveSurveyEvent->set('surveyid', $surveyid);
        $saveSurveyEvent->set('state', $state);
        $saveSurveyEvent->set('aSaveErrors', $this->aSaveErrors);
        $saveSurveyEvent->set('saveData', $this->saveData);
        Yii::app()->getPluginManager()->dispatchEvent($saveSurveyEvent);
        $this->aSaveErrors = $saveSurveyEvent->get('aSaveErrors');
        $this->saveData = $saveSurveyEvent->get('saveData');
    }
}
