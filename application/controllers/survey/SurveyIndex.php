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
*/

class SurveyIndex extends CAction
{
    public $oTemplate;

    public function run()
    {
        useFirebug();
        $this->action();
    }

    /**
     *
     * todo: this function is toooo long, to many things happening here. Should be refactored asap!
     * @return void
     * @throws CException
     * @throws CHttpException
     */
    public function action()
    {
        global $surveyid;
        global $thissurvey, $thisstep;
        global $clienttoken, $tokensexist, $token;

        $this->loadRequiredHelpersAndLibraries();
        $param       = $this->getParameters(func_get_args(), $_POST);
        $surveyid    = (int) $param['sid'];
        $thisstep    = (int) $param['thisstep'];
        $move        = getMove();

        /* Newtest must be done bedore all other action */
        if (isset($param['newtest']) && $param['newtest'] == "Y") {
            killSurveySession($surveyid);
            resetQuestionTimers($surveyid);
        }

        /* Get client token by POST or GET value */
        $clienttoken = trim((string)$param['token']);
        /* If not set : get by SESSION to avoid multiple submit of same token in different navigator */
        if (empty($clienttoken) && !empty($_SESSION['survey_' . $surveyid]['token'])) {
            $clienttoken = $_SESSION['survey_' . $surveyid]['token'];
        }

        $oSurvey = Survey::model()->findByPk($surveyid);

        if (empty($oSurvey)) {
            $event = new PluginEvent('onSurveyDenied');
            $event->set('surveyId', $surveyid);
            $event->set('reason', 'surveyDoesNotExist');
            App()->getPluginManager()->dispatchEvent($event);
            throw new CHttpException(404, gT("The survey in which you are trying to participate does not seem to exist."));
            /* Alt solution */
            //~ header("HTTP/1.0 404 Not Found",true,404);
            //~ Yii::app()->twigRenderer->renderTemplateFromFile("layout_errors.twig",
                //~ array('aSurveyInfo' =>array(
                    //~ 'aError'=>array(
                        //~ 'error'=>gT('404: Not Found'),
                        //~ 'title'=>gT('This survey does not seem to exist'),
                        //~ 'message'=>gT("The survey in which you are trying to participate does not seem to exist. It may have been deleted or the link you were given is outdated or incorrect.")
                    //~ ),
                //~ )), false);
        }

        Yii::app()->setConfig('surveyID', $surveyid);
        Yii::app()->setConfig('move', $move);
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts') . "survey_runtime.js");

        if (is_null($thissurvey) && !is_null($surveyid)) {
            $thissurvey = getSurveyInfo($surveyid);
        }

        // unused vars in this method (used in methods using compacted method vars)
        $loadname = $param['loadname'];
        $loadpass = $param['loadpass'];
        $sitename = Yii::app()->getConfig('sitename');

        $surveyExists   = ($oSurvey != null);
        $isSurveyActive = ($surveyExists && $oSurvey->isActive);

        // collect all data in this method to pass on later
        $redata = compact(array_keys(get_defined_vars()));
        $redata['popuppreview'] = Yii::app()->request->getParam('popuppreview', false);

        $canPreviewSurvey = $this->canUserPreviewSurvey($surveyid);

        if ($redata['popuppreview'] && !$canPreviewSurvey) {
            $message = gT("We are sorry but you don't have permissions to do this.", 'unescaped');
            if (Permission::model()->getUserId()) {
                throw new CHttpException(403, $message);
            }
            throw new CHttpException(401, $message);
        }

        $previewmode = false;
        if (isset($param['action']) && (in_array($param['action'], array('previewgroup', 'previewquestion')))) {
            if (!$canPreviewSurvey) {
                $aErrors  = array(gT('Error'));
                $message = gT("We are sorry but you don't have permissions to do this.", 'unescaped');
                if (Permission::model()->getUserId()) {
                    throw new CHttpException(403, $message);
                }
                throw new CHttpException(401, $message);
            } else {
                killSurveySession($surveyid);
                // Check if group exists in this survey
                $arGroup = QuestionGroup::model()->find("sid = :sid and gid = :gid", [":sid" => $surveyid, ":gid" => intval($param['gid'])]);
                if (empty($arGroup)) {
                    throw new CHttpException(400, gT("Invalid group ID"));
                }
                if ($param['action'] == 'previewquestion') {
                    $previewmode = 'question';
                    // Check if question exists in this survey and group
                    $arQuestion = Question::model()->find("sid = :sid and qid = :qid and gid = :gid", [":sid" => $surveyid, ":qid" => intval($param['qid']), ":gid" => intval($param['gid'])]);
                    if (empty($arQuestion)) {
                        throw new CHttpException(400, gT("Invalid question ID"));
                    }
                }
                if ((intval($param['gid']) && $param['action'] == 'previewgroup')) {
                    $previewmode = 'group';
                }
            }
        }

        Yii::app()->setConfig('previewmode', $previewmode);


        // Token Object
        // Get token
        if (!isset($token) && isset($clienttoken)) {
            $token = $clienttoken;
        }

        //SEE IF SURVEY USES TOKENS
        if ($oSurvey->hasTokensTable) {
            $tokensexist = 1;
        }

        // maintenance mode
        $sMaintenanceMode = getGlobalSetting('maintenancemode');
        if ($sMaintenanceMode == 'hard') {
            if ($previewmode === false) {
                Yii::app()->twigRenderer->renderTemplateFromFile("layout_maintenance.twig", array('oSurvey' => Survey::model()->findByPk($surveyid), 'aSurveyInfo' => $thissurvey), false);
            }
        } elseif ($sMaintenanceMode == 'soft') {
            if ($move === null) {
                if ($previewmode === false) {
                    Yii::app()->twigRenderer->renderTemplateFromFile("layout_maintenance.twig", array('oSurvey' => Survey::model()->findByPk($surveyid), 'aSurveyInfo' => $thissurvey), false);
                }
            }
        }

        if ($tokensexist == 1 && isset($token) && $token != "" && tableExists("{{tokens_" . $surveyid . "}}") && !$previewmode) {
            // check also if it is allowed to change survey after completion
            if ($thissurvey['alloweditaftercompletion'] == 'Y') {
                $oToken = $tokenInstance = Token::model($surveyid)->editable()->findByAttributes(array('token' => $token));
            } else {
                $oToken = $tokenInstance = Token::model($surveyid)->usable()->incomplete()->findByAttributes(array('token' => $token));
            }
            if (empty($tokenInstance)) {
                $oToken = Token::model($surveyid)->findByAttributes(array('token' => $token));
            }
            if (empty($oToken)) {
                // #16142 quick fix : unset invalid token
                $token = null;
            }
        }

        // If the session was already initiated before accessing the survey with a token,
        // force the session to be rebuilt to take the token into account.
        if (empty($_SESSION['survey_' . $surveyid]['token']) && $token) {
            buildsurveysession($surveyid);
        }

        $this->loadLimesurveyLang($surveyid);

        // Set the language of the survey, either from POST, GET parameter of session var
        // Keep the old value, because SetSurveyLanguage update $_SESSION
        $sOldLang = $_SESSION['survey_' . $surveyid]['s_lang'] ?? ""; // Keep the old value, because SetSurveyLanguage update $_SESSION

        $sDisplayLanguage = Yii::app()->getConfig('defaultlang');
        if (!empty($param['lang'])) {
            $sDisplayLanguage = $param['lang']; // $param take lang from returnGlobal and returnGlobal sanitize langagecode
        } elseif (isset($_SESSION['survey_' . $surveyid]['s_lang'])) {
            $sDisplayLanguage = $_SESSION['survey_' . $surveyid]['s_lang'];
        } elseif (!empty($oToken)) {
            $sDisplayLanguage = $oToken->language;
        } elseif ($oSurvey) {
            $sDisplayLanguage = $oSurvey->language;
        }

        if ($surveyid && $surveyExists) {
            SetSurveyLanguage($surveyid, $sDisplayLanguage);
        }

        /* Launch beforeSurveyPage before all renderExitMessage */
        $beforeSurveyPageEvent = new PluginEvent('beforeSurveyPage');
        $beforeSurveyPageEvent->set('surveyId', $surveyid);
        App()->getPluginManager()->dispatchEvent($beforeSurveyPageEvent);

        if ($this->isClientTokenDifferentFromSessionToken($clienttoken, $surveyid)) {
            $sReloadUrl = $this->getController()->createUrl("/survey/index/sid/{$surveyid}", array('token' => $clienttoken, 'lang' => App()->language, 'newtest' => 'Y'));
            $aErrors    = array(gT('Access code mismatch'));
            $asMessage  = array(gT('The access code you provided doesn\'t match the one in your session.'));
            $aUrl       = array(
                            'url' => $sReloadUrl,
                            'type' => 'restart-survey',
                            'description' => gT("Click here to start the survey.")
                            );

            killSurveySession($surveyid);

            App()->getController()->renderExitMessage(
                $surveyid,
                'restart-survey',
                $asMessage,
                $aUrl,
                $aErrors
            );

            $this->_createNewUserSessionAndRedirect($surveyid, $redata, __LINE__, $asMessage);
        } elseif (!$clienttoken) {
            $clienttoken = $_SESSION['survey_' . $surveyid]['token'] ?? ""; // Fix for #12003
        }

        if ($tokensexist != 1) {
            $tokensexist = 0;
            unset($_POST['token']);
            unset($param['token']);
            unset($token);
            unset($clienttoken);
        }

        // No test for response update
        if ($this->isSurveyFinished($surveyid)) {
            killSurveySession($surveyid);
            $aReloadUrlParam = array('lang' => App()->language, 'newtest' => 'Y');
            if (!empty($clienttoken)) {
                $aReloadUrlParam['token'] = $clienttoken;
            }
            //todo: this url is never shown to the participant in case of renderExitMessage (see below)
            $restartUrl = $this->getController()->createUrl("/survey/index/sid/{$surveyid}", $aReloadUrlParam);
            $aUrl     = array(
                            'url' => $restartUrl,
                            'type' => 'restart-survey',
                            'description' => gT("Click here to start the survey.")
                        );

            //Use case: if participant has possibility to run survey again
            //instead of showing an error message, redirect participant to restart survey
            //check if inherit value is set to 'Y'
            $alloweditaftercompletion = ($thissurvey['alloweditaftercompletion'] == 'Y') ||
                $oSurvey->getIsAllowEditAfterCompletion() || $thissurvey['tokenanswerspersistence'] == 'Y';
            if ($alloweditaftercompletion) {
                $this->getController()->redirect($restartUrl);
            }

            $aErrors  = array(gT('Previous session is set to be finished.'));
            $aMessage = array(gT('Your browser reports that it was used previously to answer this survey.
            We are resetting the session so that you can start from the beginning.'),);
            App()->getController()->renderExitMessage(
                $surveyid,
                'restart-survey',
                $aMessage,
                $aUrl,
                $aErrors
            );
        }

        if ($this->surveyCantBeViewedWithCurrentPreviewAccess($surveyid, $isSurveyActive, $surveyExists)) {
            $bPreviewRight = $this->userHasPreviewAccessSession($surveyid);

            if ($bPreviewRight === false) {
                $event    = new PluginEvent('onSurveyDenied');
                $event->set('surveyId', $surveyid);
                $event->set('reason', 'noPreviewPermission');

                App()->getPluginManager()->dispatchEvent($event);
                if (Permission::model()->getUserId()) {
                    throw new CHttpException(403, gT("We are sorry but you don't have permissions to do this.", 'unescaped'));
                }
                throw new CHttpException(401, gT("We are sorry but you don't have permissions to do this.", 'unescaped'));
            }
        }

        // TODO can this be moved to the top?
        // (Used to be global, used in ExpressionManager, merged into amVars. If not filled in === '')
        // can this be added in the first computation of $redata?
        if (isset($_SESSION['survey_' . $surveyid]['srid'])) {
            $saved_id = $_SESSION['survey_' . $surveyid]['srid'];
        }

        // recompute $redata since $saved_id used to be a global
        $redata = compact(array_keys(get_defined_vars()));

        if ($this->didSessionTimeout($surveyid)) {
            $aErrors = array(gT('We are sorry but your session has expired.'));
            $aMessage = array(
                gT("Either you have been inactive for too long, you have cookies disabled for your browser, or there were problems with your connection."),
            );

            $aReloadUrlParam = array('lang' => App()->language, 'newtest' => 'Y');
            if (!empty($clienttoken)) {
                $aReloadUrlParam['token'] = $clienttoken;
            }
            $aUrl = array(
                'url' => $this->getController()->createUrl("/survey/index/sid/{$surveyid}", $aReloadUrlParam),
                'type' => 'restart-survey',
                'description' => gT("Click here to start the survey.")
            );

            $event = new PluginEvent('onSurveyDenied');
            $event->set('surveyId', $surveyid);
            $event->set('reason', 'sessionExpired');
            App()->getPluginManager()->dispatchEvent($event);

            App()->getController()->renderExitMessage(
                $surveyid,
                'session-timeout',
                $aMessage,
                $aUrl,
                $aErrors
            );
        };

        //CHECK FOR REQUIRED INFORMATION (sid)
        if ($surveyid && $surveyExists) {
            LimeExpressionManager::SetSurveyId($surveyid); // must be called early - it clears internal cache if a new survey is being used

            if ($previewmode) {
                LimeExpressionManager::SetPreviewMode($previewmode);
            }

            // Update the Session var only if needed
            if (App()->language != $sOldLang) {
                UpdateGroupList($surveyid, App()->language); // to refresh the language strings in the group list session variable
                updateFieldArray(); // to refresh question titles and question text
            }
        }

        //GET BASIC INFORMATION ABOUT THIS SURVEY
        $thissurvey = getSurveyInfo($surveyid, $_SESSION['survey_' . $surveyid]['s_lang']);
        EmCacheHelper::init($thissurvey);
        /* Unsure it still work, and surely better in afterFindSurvey */
        if (!is_null($beforeSurveyPageEvent->get('template'))) {
            $thissurvey['templatedir'] = $beforeSurveyPageEvent->get('template');
        }

        //SET THE TEMPLATE DIRECTORY
        if ($thissurvey['template'] == 'inherit') {
            /* Load default theme (Global settings -> Default theme) */
            $oTemplate  = Template::model()->getInstance();
        } else {
            $oTemplate  = Template::model()->getInstance('', $surveyid);
        }
        $timeadjust = Yii::app()->getConfig("timeadjust");

        //MAKE SURE SURVEY HASN'T EXPIRED
        if ($thissurvey['expiry'] != '' and dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust) > $thissurvey['expiry'] && $thissurvey['active'] != 'N' && !$previewmode) {
            $aErrors = array(gT('Error'));
            $aMessage = array(
                gT("We are sorry but the survey is expired and no longer available.")
            );

            $event = new PluginEvent('onSurveyDenied');
            $event->set('surveyId', $surveyid);
            $event->set('reason', 'surveyNoLongerAvailable');

            App()->getPluginManager()->dispatchEvent($event);
            App()->getController()->renderExitMessage(
                $surveyid,
                'survey-expiry',
                $aMessage,
                null,
                $aErrors
            );
        }

        //MAKE SURE SURVEY IS ALREADY VALID
        if ($thissurvey['startdate'] != '' and dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust) < $thissurvey['startdate'] && $thissurvey['active'] != 'N' && !$previewmode) {
            $aErrors  = array(gT('Error'));
            $aMessage = array(
                gT("This survey is not yet started.")
            );

            $event = new PluginEvent('onSurveyDenied');
            $event->set('surveyId', $surveyid);
            $event->set('reason', 'surveyNotYetStarted');
            App()->getPluginManager()->dispatchEvent($event);
            App()->getController()->renderExitMessage(
                $surveyid,
                'survey-notstart',
                $aMessage,
                null,
                $aErrors
            );
        }

        //CHECK FOR PREVIOUSLY COMPLETED COOKIE
        //If cookies are being used, and this survey has been completed, a cookie called "PHPSID[sid]STATUS" will exist (ie: SID6STATUS) and will have a value of "COMPLETE"
        $sCookieName = "LS_" . $surveyid . "_STATUS";
        if (!$previewmode && isset($_COOKIE[$sCookieName]) && $_COOKIE[$sCookieName] == "COMPLETE" && $thissurvey['usecookie'] == "Y" && $tokensexist != 1) {
            $aErrors  = array(gT('Error'));
            $aMessage = array(
                gT("You have already completed this survey.")
            );

            $event = new PluginEvent('onSurveyDenied');
            $event->set('surveyId', $surveyid);
            $event->set('reason', 'alreadyCompleted');
            App()->getPluginManager()->dispatchEvent($event);

            App()->getController()->renderExitMessage(
                $surveyid,
                'survey-notstart',
                $aMessage,
                null,
                $aErrors
            );
        }

        //LOAD SAVED SURVEY
        if (Yii::app()->request->getParam('loadall') == "reload") {
            $aLoadErrorMsg = array();
            $sLoadName     = Yii::app()->request->getParam('loadname');
            $sLoadPass     = Yii::app()->request->getParam('loadpass');

            if (isset($sLoadName) && !$sLoadName) {
                $aLoadErrorMsg['name'] = gT("You did not provide a name.");
            }

            if (isset($sLoadPass) && !$sLoadPass) {
                $aLoadErrorMsg['password'] = gT("You did not provide a password.");
            }

            if (!$isSurveyActive) {
                $aLoadErrorMsg['password'] = gT("You cannot reload responses because the survey is not activated, yet.");
            }

            // if security question answer is incorrect
            // Not called if scid is set in GET params (when using email save/reload reminder URL)
            // && Yii::app()->request->isPostRequest ?
            if (isCaptchaEnabled('saveandloadscreen', $thissurvey['usecaptcha']) && is_null(Yii::app()->request->getQuery('scid'))) {
                $sLoadSecurity  = Yii::app()->request->getPost('loadsecurity');

                if (empty($sLoadSecurity)) {
                    $aLoadErrorMsg['captchaempty'] = gT("You did not answer to the security question.");
                } elseif (
                    !Yii::app()->request->getPost('loadsecurity')
                    || !isset($_SESSION['survey_' . $surveyid]['secanswer'])
                    || Yii::app()->request->getPost('loadsecurity') != $_SESSION['survey_' . $surveyid]['secanswer']
                ) {
                        $aLoadErrorMsg['captcha'] = gT("The answer to the security question is incorrect.");
                }
            }

            if (FailedLoginAttempt::model()->isLockedOut(FailedLoginAttempt::TYPE_TOKEN)) {
                $aLoadErrorMsg['tooManyRetries'] = sprintf(gT('You have exceeded the number of maximum access code validation attempts. Please wait %d minutes before trying again.'), App()->getConfig('timeOutParticipants') / 60);
            }

            if (empty($aLoadErrorMsg)) {
                LimeExpressionManager::SetDirtyFlag();
                buildsurveysession($surveyid);

                // If no name and pass are set in the request, don't try to load an answer. Just show the loading form.
                if (empty(Yii::app()->request->getParam('loadname')) && empty(Yii::app()->request->getParam('loadpass'))) {
                    Yii::app()->setConfig('move', "loadall"); // Show loading form
                } else {
                    if (loadanswers()) {
                        Yii::app()->setConfig('move', 'reload');
                        $move = "reload"; // SurveyRunTimeHelper use $move in $arg
                    } else {
                        $aLoadErrorMsg['matching'] = gT("There is no matching saved response.");
                    }
                }

                randomizationGroupsAndQuestions($surveyid);
                initFieldArray($surveyid, $_SESSION['survey_' . $surveyid]['fieldmap']);
            }
            usleep(rand(Yii::app()->getConfig("minforgottenpasswordemaildelay"), Yii::app()->getConfig("maxforgottenpasswordemaildelay")));
            if (count($aLoadErrorMsg)) {
                FailedLoginAttempt::model()->addAttempt(FailedLoginAttempt::TYPE_TOKEN);
                Yii::app()->setConfig('move', "loadall"); // Show loading form
            }
        }

        //Allow loading of saved survey
        if (Yii::app()->getConfig('move') == "loadall") {
            /* Construction of the form */
            $aLoadForm['aCaptcha']['show'] = false;

            // save current survey data when clicking on "Load unfinished survey"
            Yii::import('application.helpers.SurveyRuntimeHelper');
            $SurveyRuntimeHelper = new SurveyRuntimeHelper();
            $SurveyRuntimeHelper->saveAllIfNeeded();

            if (isCaptchaEnabled('saveandloadscreen', $oSurvey->usecaptcha)) {
                $aLoadForm['aCaptcha']['show'] = true;
                $aLoadForm['aCaptcha']['sImageUrl'] = Yii::app()->getController()->createUrl('/verification/image', array('sid' => $surveyid));
            }

            if (!empty($clienttoken)) {
                $aLoadForm['sHiddenField'] = CHtml::hiddenField('token', $clienttoken);
            }

            $aLoadForm['aErrors']    = empty($aLoadErrorMsg) ? null : $aLoadErrorMsg; // Set tit to null if empty
            $thissurvey['aLoadForm'] = $aLoadForm;
            //$oTemplate->registerAssets();
            $thissurvey['include_content'] = 'load';
            $thissurvey['trackUrlPageName'] = 'load';
            Yii::app()->twigRenderer->renderTemplateFromFile("layout_global.twig", array('oSurvey' => Survey::model()->findByPk($surveyid), 'aSurveyInfo' => $thissurvey), false);
        }

        //check if token is in a valid time frame
        //Check if TOKEN is used for EVERY PAGE
        //This function fixes a bug where users able to submit two surveys/votes
        //by checking that the token has not been used at each page displayed.
        // bypass only this check at first page (Step=0) because
        // this check is done in buildsurveysession and error message
        // could be more interresting there (takes into accound captcha if used)
        if ($tokensexist == 1 && isset($token) && $token != "" && tableExists("{{tokens_" . $surveyid . "}}") && !$previewmode) {
            if (empty($tokenInstance) && $oToken) {
                $now = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", Yii::app()->getConfig("timeadjust"));

                // This can not happen (TokenInstance must fix this)
                if ($oToken->completed != 'N' && !empty($oToken->completed)) {
                    $sError = gT("This invitation has already been used.");
                } elseif ($oToken->usesleft < 1) {
                    $sError = gT("This invitation has no uses left.");
                } elseif (strtotime((string) $now) < strtotime((string) $oToken->validfrom)) {
                    $sError = gT("This invitation is not valid yet.");
                } elseif (strtotime((string) $now) > strtotime((string) $oToken->validuntil)) {
                    $sError = gT("This invitation is not valid anymore.");
                } else {
                    // This can not happen
                    $sError = gT("This is a controlled survey. You need a valid access code to participate.");
                }

                $aMessage = array(
                    gT("We are sorry but you are not allowed to enter this survey.")
                );

                $event = new PluginEvent('onSurveyDenied');
                $event->set('surveyId', $surveyid);
                $event->set('reason', 'invalidToken');
                App()->getPluginManager()->dispatchEvent($event);

                App()->getController()->renderExitMessage(
                    $surveyid,
                    'survey-notstart',
                    $aMessage,
                    null,
                    array($sError)
                );
            }
        }

        //Check to see if a refering URL has been captured.
        if (!isset($_SESSION['survey_' . $surveyid]['refurl'])) {
            $_SESSION['survey_' . $surveyid]['refurl'] = getReferringUrl(); // do not overwrite refurl
        }

        // Let's do this only if
        //  - a saved answer record hasn't been loaded through the saved feature
        //  - the survey is not anonymous
        //  - the survey is active
        //  - a token information has been provided
        //  - the survey is setup to allow token-response-persistence

        if (!isset($_SESSION['survey_' . $surveyid]['srid']) && $thissurvey['anonymized'] == "N" && $thissurvey['active'] == "Y" && isset($token) && $token != '') {
            // load previous answers if any (dataentry with nosubmit)
                $oResponses = Response::model($surveyid)->findAllByAttributes(array(
                'token' => $token
                ), array('order' => 'id DESC'));

            if (!empty($oResponses)) {

                /**
                 * We fire the response selection event when at least 1 response was found.
                 * If there is just 1 response the plugin still has to option to choose
                 * NOT to use it.
                 */
                $event = new PluginEvent('beforeLoadResponse');
                $event->set('responses', $oResponses);
                $event->set('surveyId', $surveyid);
                App()->pluginManager->dispatchEvent($event);

                $oResponse = $event->get('response');

                // If $oResponse is false we act as if no response was found.
                // This allows a plugin to deny continuing a response.
                if ($oResponse !== false) {
                    // If plugin does not set a response we use the first one found, (this replicates pre-plugin behavior)
                    if (!isset($oResponse) && (!isset($oResponses[0]->submitdate) || $thissurvey['alloweditaftercompletion'] == 'Y') && $thissurvey['tokenanswerspersistence'] == 'Y') {
                        $oResponse = $oResponses[0];
                    }

                    if (isset($oResponse)) {
                        $_SESSION['survey_' . $surveyid]['srid'] = $oResponse->id;

                        if (!empty($oResponse->lastpage)) {
                            $_SESSION['survey_' . $surveyid]['LEMtokenResume'] = true;

                            // If the response was completed and user is allowed to edit after completion start at the beginning and not at the last page - just makes more sense
                            if (!($oResponse->submitdate && $thissurvey['alloweditaftercompletion'] == 'Y')) {
                                $_SESSION['survey_' . $surveyid]['step'] = $oResponse->lastpage;
                            }
                        }

                        buildsurveysession($surveyid);

                        // alloweditaftercompletion
                        if (!empty($oResponse->submitdate)) {
                            $_SESSION['survey_' . $surveyid]['maxstep'] = $_SESSION['survey_' . $surveyid]['totalsteps'];
                        }

                        loadanswers();
                        randomizationGroupsAndQuestions($surveyid);
                        initFieldArray($surveyid, $_SESSION['survey_' . $surveyid]['fieldmap']);
                    }
                }
            }
        }

        // Preview action : Preview right already tested before
        if ($previewmode == 'previewgroup' || $previewmode == 'previewquestion') {
            // Unset all SESSION: be sure to have the last version
            unset($_SESSION['fieldmap-' . $surveyid . App()->language]); // Needed by createFieldMap: else fieldmap can be outdated
            unset($_SESSION['survey_' . $surveyid]);

            if ($param['action'] == 'previewgroup') {
                $thissurvey['format'] = 'G';
            } elseif ($param['action'] == 'previewquestion') {
                $thissurvey['format'] = 'S';
            }

            buildsurveysession($surveyid, true);
            randomizationGroupsAndQuestions($surveyid, true);
            initFieldArray($surveyid, $_SESSION['survey_' . $surveyid]['fieldmap']);
        }

        $popuppreview = (Yii::app()->request->getParam("popuppreview", false) == "true");
        // Reset the question timers in preview
        if (!$isSurveyActive || $previewmode) {
            resetQuestionTimers($surveyid);
        }

        sendSurveyHttpHeaders();

        //Send local variables to the appropriate survey type
        unset($redata);
        $redata = compact(array_keys(get_defined_vars()));
        Yii::import('application.helpers.SurveyRuntimeHelper');
        $tmp = new SurveyRuntimeHelper();
        // try {
            $tmp->run($surveyid, $redata);
        // } catch (WrongTemplateVersionException $ex) {
        //     echo $ex->getMessage();
        // }
    }

    private function getParameters($args = array(), $post = array())
    {
        $param = array();
        if (isset($args[0]) && $args[0] == __CLASS__) {
            array_shift($args);
        }
        $iArgCount = count($args);
        if ($iArgCount % 2 == 0) {
            for ($i = 0; $i < $iArgCount; $i += 2) {
                //Sanitize input from URL with returnGlobal
                $param[$args[$i]] = returnGlobal($args[$i], true);
            }
        }

        // Need some $param (else PHP notice)
        foreach (array('lang', 'action', 'newtest', 'qid', 'gid', 'sid', 'loadname', 'loadpass', 'scid', 'thisstep', 'move', 'token') as $sNeededParam) {
            $param[$sNeededParam] = returnGlobal($sNeededParam, true);
        }

        return $param;
    }

    private function loadRequiredHelpersAndLibraries()
    {
        //Load helpers, libraries and config vars
        Yii::app()->loadHelper("database");
        Yii::app()->loadHelper("frontend");
        Yii::app()->loadHelper("surveytranslator");
    }

    private function loadLimesurveyLang($mvSurveyIdOrBaseLang)
    {
        $oSurvey = Survey::model()->findByPk($mvSurveyIdOrBaseLang);
        if ($oSurvey) {
            $baselang = $oSurvey->language;
        } elseif (!empty($mvSurveyIdOrBaseLang)) {
            $baselang = $mvSurveyIdOrBaseLang;
        } else {
            $baselang = Yii::app()->getConfig('defaultlang');
        }

        App()->setLanguage($baselang);
    }

    private function isClientTokenDifferentFromSessionToken($clientToken, $surveyid)
    {
        return $clientToken != '' && isset($_SESSION['survey_' . $surveyid]['token']) && $clientToken != $_SESSION['survey_' . $surveyid]['token'];
    }

    private function isSurveyFinished($surveyid)
    {
        return isset($_SESSION['survey_' . $surveyid]['finished']) && $_SESSION['survey_' . $surveyid]['finished'] === true;
    }

    private function surveyCantBeViewedWithCurrentPreviewAccess($surveyid, $bIsSurveyActive, $bSurveyExists)
    {
        $bSurveyPreviewRequireAuth = Yii::app()->getConfig('surveyPreview_require_Auth');
        return $surveyid && $bIsSurveyActive === false && $bSurveyExists && isset($bSurveyPreviewRequireAuth) && $bSurveyPreviewRequireAuth == true && !$this->canUserPreviewSurvey($surveyid);
    }

    private function didSessionTimeout($surveyid)
    {
        return (!isset($_SESSION['survey_' . $surveyid]['step']) && null !== App()->request->getPost('thisstep'));
    }

    private function canUserPreviewSurvey($iSurveyID)
    {
        return Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'read');
    }

    private function userHasPreviewAccessSession($iSurveyID)
    {
        return (isset($_SESSION['USER_RIGHT_PREVIEW']) && ($_SESSION['USER_RIGHT_PREVIEW'] == $iSurveyID));
    }
}

/* End of file survey.php */
/* Location: ./application/controllers/survey.php */
