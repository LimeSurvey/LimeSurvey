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
 * Tokens Controller
 *
 * This controller performs token actions
 *
 * @package       LimeSurvey
 * @subpackage    Backend
 */
class Tokens extends SurveyCommonAction
{
    /**
     * Show token index page, handle token database
     * @param int $surveyid  The survey ID
     * @return void
     */
    public function index(int $surveyid, $limit = 50, $start = 0)
    {
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'tokens.js', LSYii_ClientScript::POS_BEGIN);
        $iSurveyId = $surveyid;
        $iSurveyId = (int) $iSurveyId;
        $survey = Survey::model()->findByPk($iSurveyId);

        //TODO : check if it does something different than the model function
        $thissurvey = getSurveyInfo($iSurveyId);
        if (
            !Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'read') && !Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'create') && !Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update')
            && !Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'export') && !Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'import')
            && !Permission::model()->hasSurveyPermission($iSurveyId, 'surveysettings', 'update')
        ) {
            Yii::app()->session['flashmessage'] = gT("You do not have permission to access this page.");
            $this->getController()->redirect(array("/surveyAdministration/view/surveyid/{$iSurveyId}"));
        }

        Yii::app()->loadHelper("surveytranslator");

        $aData = array();
        $aData['surveyprivate'] = $thissurvey['anonymized'];
        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyId . ")";
        $aData['sidemenu']["token_menu"] = true;

        //new topbar in use here
        $topbarData = TopbarConfiguration::getTokensTopbarData($iSurveyId);
        $aData['topbar']['showDelButton'] = true;
        $topbarData = array_merge($topbarData, $aData['topbar']);
        $aData['topbar']['middleButtons'] = Yii::app()->getController()->renderPartial(
            '/surveyAdministration/partial/topbar_tokens/leftSideButtons',
            $topbarData,
            true
        );
        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/surveyAdministration/partial/topbar_tokens/rightSideButtons',
            $topbarData,
            true
        );

        // this data is for table
        if (!$survey->hasTokensTable) {
            $this->newtokentable($iSurveyId);
        }
        Yii::import('application.libraries.Date_Time_Converter', true);
        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);

        $limit = (int) $limit;
        $start = (int) $start;

        $tkcount = Token::model($iSurveyId)->count();
        $next = $start + $limit;
        $last = $start - $limit;
        $end = $tkcount - $limit;

        if ($end < 0) {
            $end = 0;
        }
        if ($last < 0) {
            $last = 0;
        }
        if ($next >= $tkcount) {
            $next = $tkcount - $limit;
        }
        if ($end < 0) {
            $end = 0;
        }
        $order = Yii::app()->request->getPost('order', 'tid');
        $order = preg_replace('/[^_ a-z0-9-]/i', '', (string) $order);

        $aData['next'] = $next;
        $aData['last'] = $last;
        $aData['end'] = $end;
        $searchstring = Yii::app()->request->getPost('searchstring');

        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['searchstring'] = $searchstring;
        $aData['surveyid'] = $iSurveyId;

        $aData['bgc'] = "";
        $aData['limit'] = $limit;
        $aData['start'] = $start;
        $aData['order'] = $order;
        $aData['surveyprivate'] = $aData['thissurvey']['anonymized'];
        $aData['dateformatdetails'] = $dateformatdetails;
        $aLanguageCodes = Survey::model()->findByPk($iSurveyId)->getAllLanguages();
        $aLanguages = array();

        foreach ($aLanguageCodes as $aCode) {
            $aLanguages[$aCode] = getLanguageNameFromCode($aCode, false);
        }

        $aData['aLanguages']                    = $aLanguages;
        $aData['title_bar']['title']            = $survey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyId . ")";
        $aData['sidemenu']["token_menu"]        = true;
        $aData['sidemenu']['state'] = false;

        /// FOR GRID View
        $model = TokenDynamic::model($iSurveyId);
        $model->bEncryption = true;
        $filterForm = Yii::app()->request->getPost('TokenDynamic', false);
        if ($filterForm) {
            $model->setAttributes($filterForm, false);
        }

        $aData['model'] = $model;

        // Set number of page
        if (isset($_POST['pageSizeTokenView'])) {
            Yii::app()->user->setState('pageSizeTokenView', (int) $_POST['pageSizeTokenView']);
        }

        $aData['massiveAction'] = App()->getController()->renderPartial('/admin/token/massive_actions/_selector', $aData, true, false);

        // CHECK TO SEE IF A Survey participants table EXISTS FOR THIS SURVEY
        $aData['surveyActivationFeedback'] = Yii::app()->request->getParam('surveyActivationFeedback', null);
        if (!$survey->hasTokensTable) {
            $this->newtokentable($iSurveyId);
        } else {
            $aData['queries'] = Token::model($iSurveyId)->summary();

            $this->renderWrappedTemplate('token', array('surveyParticipantView'), $aData);
        }
    }

    /**
     * tokens::bounceprocessing()
     * @param int $iSurveyId
     * @return void
     */
    public function bounceprocessing($iSurveyId)
    {
        $iSurveyId = (int) $iSurveyId;
        $survey = Survey::model()->findByPk($iSurveyId);
        if (!$survey->hasTokensTable) {
            eT("No survey participants table.");
            return;
        }
        $thissurvey = getSurveyInfo($iSurveyId);

        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update')) {
            eT("We are sorry but you don't have permissions to do this.");
            return;
        }

        if ($thissurvey['bounceprocessing'] != 'N' || ($thissurvey['bounceprocessing'] == 'G' && getGlobalSetting('bounceaccounttype') != 'off')) {
            if (!function_exists('imap_open')) {
                eT("The imap PHP library is not installed or not activated. Please contact your system administrator.");
                return;
            }
            $bouncetotal = 0;
            $checktotal = 0;
            if ($thissurvey['bounceprocessing'] == 'G') {
                $accounttype    = strtoupper((string) getGlobalSetting('bounceaccounttype'));
                $hostname       = getGlobalSetting('bounceaccounthost');
                $username       = getGlobalSetting('bounceaccountuser');
                $pass           = LSActiveRecord::decryptSingle(getGlobalSetting('bounceaccountpass'));
                $hostencryption = strtolower((string) getGlobalSetting('bounceencryption'));
            } else {
                $accounttype    = strtoupper((string) $thissurvey['bounceaccounttype']);
                $hostname       = $thissurvey['bounceaccounthost'];
                $username       = $thissurvey['bounceaccountuser'];
                $pass           = LSActiveRecord::decryptSingle($thissurvey['bounceaccountpass']);
                $hostencryption = strtolower((string) $thissurvey['bounceaccountencryption']);
            }

            @list($hostname, $port) = explode(':', (string) $hostname);

            if (empty($port)) {
                if ($accounttype == "IMAP") {
                    switch ($hostencryption) {
                        case "off":
                        case "tls":
                            $hostname = $hostname . ":143";
                            break;
                        case "ssl":
                            $hostname = $hostname . ":993";
                            break;
                    }
                } else {
                    switch ($hostencryption) {
                        case "off":
                        case "tls":
                            $hostname = $hostname . ":110";
                            break;
                        case "ssl":
                            $hostname = $hostname . ":995";
                            break;
                    }
                }
            } else {
                $hostname = $hostname . ":" . $port;
            }

            $flags = "";
            switch ($accounttype) {
                case "IMAP":
                    $flags .= "/imap";
                    break;

                case "POP":
                    $flags .= "/pop3";
                    break;
            }

            switch ($hostencryption) { // novalidate-cert to have personal CA , maybe option.
                case "off":
                    $flags .= "/notls"; // Really Off
                    break;
                case "ssl":
                    $flags .= "/ssl/novalidate-cert";
                    break;
                case "tls":
                    $flags .= "/tls/novalidate-cert";
                    break;
            }

            $mbox = @imap_open('{' . $hostname . $flags . '}INBOX', $username, $pass);
            if ($mbox) {
                imap_errors();
                $count = imap_num_msg($mbox);
                if ($count > 0) {
                    $aMessageIDs = imap_search($mbox, 'UNSEEN', SE_UID);
                    if ($aMessageIDs === false) {
                        $aMessageIDs = array();
                    }
                    foreach ($aMessageIDs as $sMessageID) {
                        $header = explode("\r\n", imap_body($mbox, $sMessageID, FT_UID | FT_PEEK)); // Don't mark messages as read
                        $iSurveyIdBounce = '';
                        foreach ($header as $item) {
                            if (preg_match('/^X-surveyid/', $item)) {
                                $iSurveyIdBounce = explode(": ", $item);
                            }

                            if (preg_match('/^X-tokenid/', $item)) {
                                $tokenBounce = explode(": ", $item);

                                if ($iSurveyId == $iSurveyIdBounce[1]) {
                                    $condn  = array('token' => $tokenBounce[1]);
                                    $record = Token::model($iSurveyId)->findByAttributes($condn);

                                    if (!empty($record) && $record->emailstatus != 'bounced') {
                                        $record->emailstatus = 'bounced';
                                        $record->save();
                                        $bouncetotal++;
                                    }

                                    imap_body($mbox, $sMessageID, FT_UID); // Mark as read
                                    if (isset($thissurvey['bounceremove']) && $thissurvey['bounceremove']) {
                                        imap_delete($mbox, $sMessageID, FT_UID); // Mark for delete
                                    }
                                }
                            }
                        }
                        $checktotal++;
                    }
                }
                imap_close($mbox);

                if ($bouncetotal > 0) {
                    printf(gT("%s unread messages were scanned out of which %s were marked as bounce by the system."), $checktotal, $bouncetotal);
                    eT("You can now close this modal box.");
                } else {
                    printf(gT("%s unread messages were scanned, none were marked as bounce by the system."), $checktotal);
                    eT("You can now close this modal box.");
                }
            } else {
                $sSettingsUrl = App()->createUrl('admin/tokens/sa/bouncesettings/surveyid/' . $iSurveyId);
                eT("Failed to open the inbox of the bounce email account.");
                echo "<br><strong>";
                printf(gT("Please %s check your settings %s."), '<a href="' . $sSettingsUrl . '" title="Bounce settings" >', '</a>');
                echo "</strong><br> <br/>";
                eT("Error message returned by IMAP:");
                echo "<br>";
                $aErrors = @imap_errors();

                foreach ($aErrors as $sError) {
                    echo $sError . '<br/>';
                }
                echo "<br><br/>";
                eT("You can now close this modal box.");
            }
        } else {
            eT("Bounce processing is deactivated either application-wide or for this survey in particular.");
            return;
        }
    }

    /**
     * @return boolean
     */
    public function deleteMultiple()
    {
        $aTokenIds = json_decode(Yii::app()->getRequest()->getPost('sItems', '')) ?? [];
        $iSid = Yii::app()->getRequest()->getPost('sid');
        if (!Permission::model()->hasSurveyPermission($iSid, 'tokens', 'delete')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        Yii::import('application.helpers.admin.ajax_helper', true);
        $deletedTokenCount = TokenDynamic::model($iSid)->deleteRecords($aTokenIds);
        if ($deletedTokenCount > 0) {
            ls\ajax\AjaxHelper::outputSuccess(ngT('Deleted {n} survey participant.|Deleted {n} survey participants.', $deletedTokenCount));
        } else {
            ls\ajax\AjaxHelper::outputError(gT('Could not delete survey participants.'));
        }
        return true;
    }

    /**
     * Deletes a participant from survey.
     *
     * The parameter 'sItem' can either be an array of ids or just a single id (int)
     *
     * @todo separate this function in two: one for single delete and one for multiple delete
     *
     * @return bool
     * @throws CHttpException
     */
    public function deleteToken()
    {
        $this->requirePostRequest();

        $aTokenId = Yii::app()->getRequest()->getParam('sItem');
        $iSid = (int) Yii::app()->getRequest()->getParam('sid');
        if (!Permission::model()->hasSurveyPermission($iSid, 'tokens', 'delete')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        $deletedTokenCount = TokenDynamic::model($iSid)->deleteRecords(array($aTokenId));
        if ($deletedTokenCount > 0) {
            Yii::app()->setFlashMessage(ngT('Deleted {n} survey participant.|Deleted {n} survey participants.', $deletedTokenCount));
        } else {
            Yii::app()->setFlashMessage(gT('Could not delete survey participant.'), 'error');
        }
        $this->getController()->redirect(array("admin/tokens", "sa" => "index", "surveyid" => $iSid));
    }

    /**
     * Browse Tokens
     * @param int $iSurveyId
     * @param int $limit
     * @param int $start
     * @return void
     */
    public function browse($iSurveyId, $limit = 50, $start = 0)
    {
        $iSurveyId = (int) $iSurveyId;
        $survey = Survey::model()->findByPk($iSurveyId);

        /* Check permissions */
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'read')) {
            Yii::app()->session['flashmessage'] = gT("You do not have permission to access this page.");
            $this->getController()->redirect(array("/admin/tokens/sa/index/surveyid/{$iSurveyId}"));
        }

        // TODO: Why needed?
        App()->clientScript->registerPackage('bootstrap-switch');

        if (!$survey->hasTokensTable) {
            $this->newtokentable($iSurveyId);
        }

        /* build JS variable to hide buttons forbidden for the current user */
        $aData = [];

        //new topbar in use here
        $topbarData = TopbarConfiguration::getTokensTopbarData($iSurveyId);
        $aData['topbar']['showDelButton'] = Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'delete');
        $topbarData = array_merge($topbarData, $aData['topbar']);
        $aData['topbar']['middleButtons'] = Yii::app()->getController()->renderPartial(
            '/surveyAdministration/partial/topbar_tokens/leftSideButtons',
            $topbarData,
            true
        );
        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/surveyAdministration/partial/topbar_tokens/rightSideButtons',
            $topbarData,
            true
        );

        // Javascript
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'tokens.js', LSYii_ClientScript::POS_BEGIN);

        Yii::app()->loadHelper('surveytranslator');
        Yii::import('application.libraries.Date_Time_Converter', true);
        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);

        $limit = (int) $limit;
        $start = (int) $start;

        $tkcount = Token::model($iSurveyId)->count();
        $next = $start + $limit;
        $last = $start - $limit;
        $end = $tkcount - $limit;

        if ($end < 0) {
            $end = 0;
        }
        if ($last < 0) {
            $last = 0;
        }
        if ($next >= $tkcount) {
            $next = $tkcount - $limit;
        }
        if ($end < 0) {
            $end = 0;
        }
        $order = Yii::app()->request->getPost('order', 'tid');
        $order = preg_replace('/[^_ a-z0-9-]/i', '', (string) $order);

        $aData['next'] = $next;
        $aData['last'] = $last;
        $aData['end'] = $end;
        $searchstring = Yii::app()->request->getPost('searchstring');

        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['searchstring'] = $searchstring;
        $aData['surveyid'] = $iSurveyId;
        $aData['bgc'] = "";
        $aData['limit'] = $limit;
        $aData['start'] = $start;
        $aData['order'] = $order;
        $aData['surveyprivate'] = $aData['thissurvey']['anonymized'];
        $aData['dateformatdetails'] = $dateformatdetails;
        $aLanguageCodes = Survey::model()->findByPk($iSurveyId)->getAllLanguages();
        $aLanguages = array();

        foreach ($aLanguageCodes as $aCode) {
            $aLanguages[$aCode] = getLanguageNameFromCode($aCode, false);
        }

        $aData['aLanguages']                    = $aLanguages;
        $aData['title_bar']['title']            = $survey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyId . ")";
        $aData['sidemenu']["token_menu"]        = true;
        $aData['sidemenu']['state'] = false;

        /// FOR GRID View
        $model = TokenDynamic::model($iSurveyId);
        $model->bEncryption = true;
        $filterForm = Yii::app()->request->getPost('TokenDynamic', false);
        if ($filterForm) {
            $model->setAttributes($filterForm, false);
        }

        $aData['model'] = $model;

        // Set number of page
        if (isset($_POST['pageSizeTokenView'])) {
            Yii::app()->user->setState('pageSizeTokenView', (int) $_POST['pageSizeTokenView']);
        }

        $aData['massiveAction'] = App()->getController()->renderPartial('/admin/token/massive_actions/_selector', $aData, true, false);

        // seems table
        $this->renderWrappedTemplate('token', array('browse'), $aData);
    }

    /**
     * The fields with a value "lskeep" will not be updated
     */
    public function editMultiple()
    {
        $aTokenIds = json_decode(Yii::app()->request->getPost('sItems', '')) ?? [];
        $iSurveyId = Yii::app()->request->getPost('sid');
        $aResults = array();

        if (Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update')) {
            // CHECK TO SEE IF A Survey participants table EXISTS FOR THIS SURVEY
            if (tableExists('{{tokens_' . $iSurveyId . '}}')) {
                // First we create the array of fields to update
                $aData = array();
                $aResults['global']['result'] = true;
                // Valid from
                if (trim(Yii::app()->request->getPost('validfrom', 'lskeep')) != 'lskeep') {
                    if (trim(Yii::app()->request->getPost('validfrom', 'lskeep')) == '') {
                        $aData['validfrom'] = null;
                    } else {
                        $aData['validfrom'] = date('Y-m-d H:i:s', strtotime(trim((string) $_POST['validfrom'])));
                    }
                }

                // Valid until
                if (trim(Yii::app()->request->getPost('validuntil', 'lskeep')) != 'lskeep') {
                    if (trim(Yii::app()->request->getPost('validuntil', '')) == '') {
                        $aData['validuntil'] = null;
                    } else {
                        $aData['validuntil'] = date('Y-m-d H:i:s', strtotime(trim((string) $_POST['validuntil'])));
                    }
                }

                // Email
                if (trim(Yii::app()->request->getPost('email', 'lskeep')) != 'lskeep') {
                    $isValid = preg_match('/^([a-zA-Z0-9.!#$%&’*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+))(,([a-zA-Z0-9.!#$%&’*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)))*$/', Yii::app()->request->getPost('email', ''));
                    if ($isValid) {
                        $aData['email'] = Yii::app()->request->getPost('email', '');
                    } else {
                        $aData['email'] = 'lskeep';
                    }
                }

                // Core Fields
                $aCoreTokenFields = array('firstname', 'lastname', 'emailstatus', 'token', 'language', 'sent', 'remindersent', 'remindercount', 'completed', 'usesleft');
                foreach ($aCoreTokenFields as $sCoreTokenField) {
                    if (trim(App()->request->getPost($sCoreTokenField, 'lskeep')) != 'lskeep') {
                        $value = App()->request->getPost($sCoreTokenField);
                        if ($sCoreTokenField == 'language' && empty($value)) {
                            continue;
                        }
                        $aData[$sCoreTokenField] = $value;
                    }
                }

                // Attibutes fields
                $attrfieldnames = GetParticipantAttributes($iSurveyId);
                foreach ($attrfieldnames as $attr_name => $desc) {
                    if (trim(Yii::app()->request->getPost($attr_name, 'lskeep')) != 'lskeep') {
                        $value = App()->request->getPost($attr_name, '');
                        if ($desc['mandatory'] == 'Y' && trim($value) == '') {
                            Yii::app()->setFlashMessage(sprintf(gT('%s cannot be left empty'), $desc['description']), 'error');
                            $this->getController()->refresh();
                        }
                        $aData[$attr_name] = $value;
                    }
                }

                if (count($aData) > 0) {
                    foreach ($aTokenIds as $iTokenId) {
                        $iTokenId = (int) $iTokenId;
                        $token = Token::model($iSurveyId)->find('tid=' . $iTokenId)->decrypt();

                        foreach ($aData as $k => $v) {
                            $token->$k = $v;
                        }

                        $bUpdateSuccess = $token->encryptSave(true);
                        if ($bUpdateSuccess) {
                            $aResults[$iTokenId]['status']    = true;
                            $aResults[$iTokenId]['message']   = gT('Updated');
                        } else {
                            $aResults[$iTokenId]['status']    = false;
                            $aResults[$iTokenId]['message']   = '';

                            foreach ($token->getErrors() as $key => $error) {
                                $aResults[$iTokenId]['message']   .= $key . ': ' . $error[0];
                            }
                        }
                    }
                } else {
                    $aResults['global']['result']  = false;
                    $aResults['global']['message'] = gT('Nothing to update');
                }
            } else {
                $aResults['global']['result']  = false;
                $aResults['global']['message'] = gT('No participant table found for this survey!');
            }
        } else {
            $aResults['global']['result'] = false;
            $aResults['global']['message'] = gT("We are sorry but you don't have permissions to do this.");
        }


        Yii::app()->getController()->renderPartial('/admin/token/massive_actions/_update_results', array('aResults' => $aResults));
    }

    /**
     * Add new token form, also adding new participant.
     *
     * @param int $iSurveyId
     * @return void
     * @throws CException
     */
    public function addnew(int $iSurveyId)
    {
        $aData = array();
        App()->getClientScript()->registerScriptFile(App()
            ->getConfig('adminscripts') . 'tokens.js', LSYii_ClientScript::POS_BEGIN);
        $iSurveyId = (int) $iSurveyId;
        $survey = Survey::model()->findByPk($iSurveyId);

        // Check permission
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'create')) {
            App()->session['flashmessage'] = gT("You do not have permission to access this page.");
            $this->getController()->redirect(array("/surveyAdministration/view/surveyid/{$iSurveyId}"));
        }

        if (!$survey->hasTokensTable) {
            // If no tokens table exists
            $this->newtokentable($iSurveyId);
        }
        App()->loadHelper("surveytranslator");

        $dateformatdetails = getDateFormatData(App()->session['dateformat']);

        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title . " ("
            . gT("ID") . ":" . $iSurveyId . ")";
        $aData['sidemenu']["token_menu"] = true;
        App()->getClientScript()->registerScriptFile(App()
            ->getConfig('adminscripts') . 'tokens.js', LSYii_ClientScript::POS_BEGIN);
        $request = App()->request;
        $subAction = $request->getPost('subaction');
        if ($subAction == 'inserttoken') {
            // TODO: This part could be refactored into function like "insertToken()"
            Yii::import('application.libraries.Date_Time_Converter');

            // Fix up dates and match to database format
            if (trim($request->getPost('validfrom', '')) == '') {
                $validfrom = null;
            } else {
                $datetimeobj = new Date_Time_Converter(
                    trim($request->getPost('validfrom', '')),
                    $dateformatdetails['phpdate'] . ' H:i'
                );
                $validfrom = $datetimeobj->convert('Y-m-d H:i:s');
            }

            if (trim(App()->request->getPost('validuntil', '')) == '') {
                $validuntil = null;
            } else {
                $datetimeobj = new Date_Time_Converter(
                    trim($request->getPost('validuntil', '')),
                    $dateformatdetails['phpdate'] . ' H:i'
                );
                $validuntil = $datetimeobj->convert('Y-m-d H:i:s');
            }
            /** @var string : used to find if token already exist */
            $sanitizedtoken = Token::sanitizeToken($request->getPost('token'));
            $aData = array(
                'firstname' => $request->getPost('firstname'),
                'lastname' => $request->getPost('lastname'),
                'email' => $request->getPost('email'),
                'emailstatus' => $request->getPost('emailstatus'),
                'token' => $sanitizedtoken,
                'language' => sanitize_languagecode($request->getPost('language')),
                'sent' => $request->getPost('sent'),
                'remindersent' => $request->getPost('remindersent'),
                'completed' => $request->getPost('completed'),
                'usesleft' => $request->getPost('usesleft'),
                'validfrom' => $validfrom,
                'validuntil' => $validuntil,
            );

            // Add attributes
            $attrfieldnames = Survey::model()->findByPk($iSurveyId)->tokenAttributes;
            $aTokenFieldNames = App()->db->getSchema()->getTable("{{tokens_$iSurveyId}}", true);
            $aTokenFieldNames = array_keys($aTokenFieldNames->columns);
            foreach ($attrfieldnames as $attr_name => $desc) {
                if (!in_array($attr_name, $aTokenFieldNames)) {
                    continue;
                }
                $value = App()->getRequest()->getPost($attr_name, '');
                if ($desc['mandatory'] == 'Y' && trim($value) == '') {
                    App()->setFlashMessage(sprintf(gT('%s cannot be left empty'), $desc['description']), 'error');
                    $this->getController()->refresh();
                }
                $aData[$attr_name] = App()->getRequest()->getPost($attr_name);
            }

            if (!empty($sanitizedtoken) && Token::model($iSurveyId)->findByToken($sanitizedtoken)) {
                $aData['success'] = false;
                $aData['errors'] = array(
                    'token' => array(gT("There is already an entry with that exact access code in the table.
                     The same access code cannot be used in multiple entries."))
                );
            } else {
                // AutoExecute
                $token = Token::create($iSurveyId);
                $token->setAttributes($aData, false);
                $inresult = $token->encryptSave(true);
                $aData['success'] = $inresult;
                $aData['errors'] = $token->getErrors();
            }

            $aData['thissurvey'] = getSurveyInfo($iSurveyId);
            $aData['surveyid'] = $iSurveyId;
            $aData['iTokenLength'] = !empty(Token::model($iSurveyId)->survey->oOptions->tokenlength)
                ? Token::model($iSurveyId)->survey->oOptions->tokenlength
                : 15;

            $aData['sidemenu']['state'] = false;
            $aData['title_bar']['sSubaction'] = $subAction;
            $aData['title_bar']['active'] = true;

            $aData['topBar']['name'] = 'tokensTopbar_view';

            if ($aData['success']) {
                if ($request->getPost('close-after-save')) {
                    // confirmed
                    $redirectUrl = Yii::app()->createUrl(
                        "admin/tokens",
                        [
                            "sa" => 'index',
                            "surveyid" => $iSurveyId,
                        ]
                    );
                } else {
                    $redirectUrl = Yii::app()->createUrl("/admin/tokens/sa/edit", ["iSurveyId" => $iSurveyId, "iTokenId" => $token->tid]);
                }
                Yii::app()->setFlashMessage(gT("The survey participant was successfully added."));
                $this->getController()->redirect($redirectUrl);
            }
            $this->renderWrappedTemplate('token', array('addtokenpost'), $aData);
        } else {
            $this->handletokenform($iSurveyId, "addnew");
        }
    }

    /**
     * Edit Tokens
     * @param int $iSurveyId
     * @param int $iTokenId
     * @param null $deprecated in 6.5.5, used before to show partial view, move to Yii::app()->request->getIsAjaxRequest()
     * @return false|null
     */
    public function edit($iSurveyId, $iTokenId, $deprecated = null)
    {
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'tokens.js', LSYii_ClientScript::POS_BEGIN);
        $iSurveyId = (int) $iSurveyId;
        $iTokenId = (int) $iTokenId;
        $survey = Survey::model()->findByPk($iSurveyId);
        $request = Yii::app()->request;
        $ajax = $request->getIsAjaxRequest();
        $redirect = $request->getPost('close-after-save');
        // Check permission
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update')) {
            if ($ajax) {
                eT("You do not have permission to access this page.");
                return false;
            } else {
                Yii::app()->session['flashmessage'] = gT("You do not have permission to access this page.");
                $this->getController()->redirect(array("/surveyAdministration/view/surveyid/{$iSurveyId}"));
            }
        }

        if (!$survey->hasTokensTable) {
            // If no tokens table exists
            $this->newtokentable($iSurveyId);
        }

        Yii::app()->loadHelper("surveytranslator");
        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);

        if ($request->getPost('subaction')) {
            Yii::import('application.helpers.admin.ajax_helper', true);
            Yii::import('application.libraries.Date_Time_Converter', true);
            $aTokenData = [];

            // validfrom
            if (trim($request->getPost('validfrom', '')) == '') {
                $_POST['validfrom'] = null;
            } else {
                $datetimeobj = new Date_Time_Converter(trim($request->getPost('validfrom', '')), $dateformatdetails['phpdate'] . ' H:i');
                $_POST['validfrom'] = $datetimeobj->convert('Y-m-d H:i:s');
            }

            // validuntil
            if (trim($request->getPost('validuntil', '')) == '') {
                $_POST['validuntil'] = null;
            } else {
                $datetimeobj = new Date_Time_Converter(trim($request->getPost('validuntil', '')), $dateformatdetails['phpdate'] . ' H:i');
                $_POST['validuntil'] = $datetimeobj->convert('Y-m-d H:i:s');
            }

            // completed
            if (trim($request->getPost('completed', '')) == 'N' || trim($request->getPost('completed', '')) == '') {
                $_POST['completed'] = 'N';
            } elseif (trim($request->getPost('completed', '')) == 'Y') {
                $_POST['completed'] = 'Y';
            } else {
                $datetimeobj = new Date_Time_Converter(trim($request->getPost('completed', '')), $dateformatdetails['phpdate'] . ' H:i');
                $_POST['completed'] = $datetimeobj->convert('Y-m-d H:i');
            }

            //sent
            if (trim($request->getPost('sent', '')) == 'N') {
                $_POST['sent'] = 'N';
            } else {
                $datetimeobj = new Date_Time_Converter(trim($request->getPost('sent', '')), $dateformatdetails['phpdate'] . ' H:i');
                $_POST['sent'] = $datetimeobj->convert('Y-m-d H:i');
            }

            // remindersent
            if (trim($request->getPost('remindersent', '')) == 'N') {
                $_POST['remindersent'] = 'N';
            } else {
                $datetimeobj = new Date_Time_Converter(trim($request->getPost('remindersent', '')), $dateformatdetails['phpdate'] . ' H:i');
                $_POST['remindersent'] = $datetimeobj->convert('Y-m-d H:i');
            }

            $aTokenData['firstname'] = $request->getPost('firstname');
            $aTokenData['lastname'] = $request->getPost('lastname');
            $aTokenData['email'] = $request->getPost('email');
            $aTokenData['emailstatus'] = $request->getPost('emailstatus');
            /* @var string : used to find if token already exist */
            $sSanitizedToken = Token::sanitizeToken($request->getPost('token'));
            $aTokenData['token'] = $sSanitizedToken;
            $aTokenData['language'] = $request->getPost('language');
            $aTokenData['sent'] = $request->getPost('sent');
            $aTokenData['completed'] = $request->getPost('completed');
            $aTokenData['usesleft'] = $request->getPost('usesleft');
            $aTokenData['validfrom'] = $request->getPost('validfrom');
            $aTokenData['validuntil'] = $request->getPost('validuntil');
            $aTokenData['remindersent'] = $request->getPost('remindersent');
            $aTokenData['remindercount'] = intval($request->getPost('remindercount'));
            $udresult = Token::model($iSurveyId)->findAll("tid <> :tid and token <> '' and token = :token", [':tid' => $iTokenId, ':token' => $sSanitizedToken]);
            $sOutput = '';
            if (count($udresult) == 0) {
                $thissurvey = getSurveyInfo($iSurveyId);
                $aAdditionalAttributeFields = $thissurvey['attributedescriptions'];
                foreach ($aAdditionalAttributeFields as $attr_name => $desc) {
                    $value = $request->getPost($attr_name, '');
                    if ($desc['mandatory'] == 'Y' && trim($value) == '') {
                        /* All this part is disable via JS, no way to submit : issue #19548 https://bugs.limesurvey.org/view.php?id=19548*/
                        $warningString = sprintf(gT("Notice: Field '%s' (%s) was left empty, even though it is a mandatory attribute."), $desc['description'], $attr_name);
                        if ($ajax) {
                            $sOutput .= $warningString . '<br>';
                        } else {
                            App()->setFlashMessage($warningString, 'warning');
                            $redirect = false; /* Do not redirecty to allow editing */
                        }
                    }
                    $aTokenData[$attr_name] = $request->getPost($attr_name);
                }

                if (!empty($sOutput)) {
                    \ls\ajax\AjaxHelper::outputError($sOutput);
                }

                $token = Token::model($iSurveyId)->findByPk($iTokenId);
                $token->decrypt();
                foreach ($aTokenData as $k => $v) {
                    $token->$k = $v;
                }
                $result = $token->encryptSave(true);

                if ($result) {
                    if ($ajax) {
                        \ls\ajax\AjaxHelper::outputSuccess(gT('The survey participant was successfully updated.'));
                        // App->end in AjaxHelper
                    }
                    App()->setFlashMessage(gT('The survey participant was successfully updated.'), 'success');
                    if ($redirect) {
                        $redirectUrl = Yii::app()->createUrl(
                            "admin/tokens",
                            [
                                "sa" => 'index',
                                "surveyid" => $iSurveyId,
                            ]
                        );
                        $this->getController()->redirect($redirectUrl);
                    }
                } else {
                    if ($ajax) {
                        $errors = $token->getErrors();
                        $firstError = reset($errors);
                        \ls\ajax\AjaxHelper::outputError($firstError[0]);
                        // App->end in AjaxHelper
                    }
                    App()->setFlashMessage(CHtml::errorSummary($token, '<div><strong>' . gT('The survey participant was not updated.') . '</strong></div>'), 'danger');
                }
            } else {
                if ($ajax) {
                    \ls\ajax\AjaxHelper::outputError(gT('There is already an entry with that exact access code in the table. The same access code cannot be used in multiple entries.'));
                    // App->end in AjaxHelper
                }
                App()->setFlashMessage(gT('There is already an entry with that exact access code in the table. The same access code cannot be used in multiple entries.'), 'error');
            }
        }
        $this->handletokenform($iSurveyId, "edit", $iTokenId, $ajax);
    }

    /**
     * Delete tokens
     * @param int $iSurveyID
     * @return void
     */
    public function delete($iSurveyID)
    {
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'tokens.js', LSYii_ClientScript::POS_BEGIN);
        $iSurveyID = (int) $iSurveyID;
        $sTokenIDs = Yii::app()->request->getPost('tid', '');
        $survey = Survey::model()->findByPk($iSurveyID);
        /* Check permissions */
        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'update')) {
            Yii::app()->session['flashmessage'] = gT("You do not have permission to access this page.");
            $this->getController()->redirect(array("/surveyAdministration/view/surveyid/{$iSurveyID}"));
        }
        if (!$survey->hasTokensTable) {
            // If no tokens table exists
            $this->newtokentable($iSurveyID);
        }

        $beforeTokenDelete = new PluginEvent('beforeTokenDelete');
        $beforeTokenDelete->set('sTokenIds', $sTokenIDs);
        $beforeTokenDelete->set('iSurveyID', $iSurveyID);
        App()->getPluginManager()->dispatchEvent($beforeTokenDelete);

        if (Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'delete')) {
            $aTokenIds = explode(',', $sTokenIDs); //Make the tokenids string into an array

            //Delete any survey_links
            SurveyLink::model()->deleteTokenLink($aTokenIds, $iSurveyID);

            //Then delete the tokens
            Token::model($iSurveyID)->deleteByPk($aTokenIds);
        }
    }

    /**
     * Add dummy tokens form
     * @param int $iSurveyId
     * @param string $subaction
     * @return void
     * @throws Exception
     */
    public function addDummies(int $iSurveyId, string $subaction = '')
    {
        $iSurveyId = (int) $iSurveyId;
        $survey = Survey::model()->findByPk($iSurveyId);
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'create')) {
            Yii::app()->session['flashmessage'] = gT("You do not have permission to access this page.");
            $this->getController()->redirect(array("/surveyAdministration/view/surveyid/{$iSurveyId}"));
        }

        if (!$survey->hasTokensTable) {
            // If no tokens table exists
            $this->newtokentable($iSurveyId);
        }
        Yii::app()->loadHelper("surveytranslator");


        $aData = array();
        $aData['sidemenu']['state'] = false;
        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyId . ")";
        $aData['sidemenu']["token_menu"] = true;

        // Save Button
        $aData['topBar']['showSaveButton'] = true;
        // Save And Close Button
        $aData['topBar']['showSaveAndCloseButton'] = true;
        // White Close Button
        $aData['topBar']['showWhiteCloseButton'] = true;
        $aData['topBar']['closeUrl'] = Yii::app()->createUrl(
            "admin/tokens",
            [
                "sa" => 'index',
                "surveyid" => $iSurveyId,
            ]
        );

        if (!empty($subaction) && $subaction == 'add') {
            $message = '';
            $this->getController()->loadLibrary('Date_Time_Converter');
            $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);

            //Fix up dates and match to database format
            if (trim(Yii::app()->request->getPost('validfrom', '')) == '') {
                $aData['validfrom'] = null;
            } else {
                $datetimeobj = new Date_Time_Converter(trim(Yii::app()->request->getPost('validfrom', '')), $dateformatdetails['phpdate'] . ' H:i');
                $aData['validfrom'] = $datetimeobj->convert('Y-m-d H:i:s');
            }
            if (trim(Yii::app()->request->getPost('validuntil', '')) == '') {
                $aData['validuntil'] = null;
            } else {
                $datetimeobj = new Date_Time_Converter(trim(Yii::app()->request->getPost('validuntil', '')), $dateformatdetails['phpdate'] . ' H:i');
                $aData['validuntil'] = $datetimeobj->convert('Y-m-d H:i:s');
            }

            $aData['firstname'] = App()->request->getPost('firstname');
            $aData['lastname'] = App()->request->getPost('lastname');
            $aData['email'] = App()->request->getPost('email');
            $aData['token'] = '';
            $aData['language'] = sanitize_languagecode(App()->request->getPost('language'));
            $aData['sent'] = 'N';
            $aData['remindersent'] = 'N';
            $aData['completed'] = 'N';
            $aData['usesleft'] = App()->request->getPost('usesleft');
            $aData['amount'] = App()->request->getPost('amount');
            $aData['tokenlength'] = App()->request->getPost('tokenlen');

            // add attributes
            $cntAttributeErrors = 0;
            $attrfieldnames = getTokenFieldsAndNames($iSurveyId, true);
            foreach ($attrfieldnames as $attr_name => $desc) {
                $value = App()->request->getPost($attr_name, '');
                if ($desc['mandatory'] == 'Y' && trim($value) == '') {
                    Yii::app()->setFlashMessage(sprintf(gT('%s cannot be left empty'), $desc['description']), 'error');
                    $cntAttributeErrors += 1;
                }
                $aData[$attr_name] = $value;
            }

            $aData['amount'] = (int) App()->request->getPost('amount');
            $aData['tokenlength'] = (int) App()->request->getPost('tokenlen');

            // Fill an array with all existing tokens
            $existingtokens = array();
            $tokenModel     = Token::model($iSurveyId);
            $criteria       = $tokenModel->getDbCriteria();
            $criteria->select = 'token';
            $criteria->distinct = true;
            $command = $tokenModel->getCommandBuilder()->createFindCommand($tokenModel->getTableSchema(), $criteria);
            $result  = $command->query();
            while ($tokenRow = $result->read()) {
                $existingtokens[$tokenRow['token']] = true;
            }
            $result->close();

            $invalidtokencount = 0;
            $newDummyToken = 0;
            while ($newDummyToken < $aData['amount'] && $invalidtokencount < 50) {
                $token = Token::create($iSurveyId);
                $token->setAttributes($aData, false);

                $token->firstname = str_replace('{TOKEN_COUNTER}', $newDummyToken, (string) $token->firstname);
                $token->lastname = str_replace('{TOKEN_COUNTER}', $newDummyToken, (string) $token->lastname);
                $token->email = str_replace('{TOKEN_COUNTER}', $newDummyToken, (string) $token->email);

                $token->generateToken($aData['tokenlength']);

                $existingtokens[$token->token] = true;
                $token->encryptSave(true);
                $newDummyToken++;
            }
            $aData['thissurvey'] = getSurveyInfo($iSurveyId);
            $aData['surveyid'] = $iSurveyId;
            if ($newDummyToken === 0) {
                $aData['success'] = false;
                Yii::app()->session['flashmessage'] = gT("No dummy participants were added.");
                $this->getController()->redirect(array("/admin/tokens/sa/browse/surveyid/{$iSurveyId}"));
            } elseif ($cntAttributeErrors > 0) { // attribute validation errors
                $aData['dateformatdetails'] = getDateFormatData(Yii::app()->session['dateformat'], App()->language);
                $aData['aAttributeFields'] = getParticipantAttributes($iSurveyId);

                $aData['showSaveButton'] = true;
                $aData['topBar']['name'] = 'tokensTopbar_view';
                $aData['topBar']['rightSideView'] = 'tokensTopbarRight_view';

                $this->renderWrappedTemplate('token', array('dummytokenform'), $aData);
            } elseif (!$invalidtokencount) {
                $aData['success'] = true;
                Yii::app()->session['flashmessage'] = gT("New dummy participants were added.");
                //admin/tokens/sa/browse/surveyid/652779//
                $this->getController()->redirect(array("admin/tokens", "sa" => "index", "surveyid" => $iSurveyId));
            } else {
                $aData['success'] = false;
                $aData['topBar']['name'] = 'tokensTopbar_view';
                $message = array(
                    'title' => gT("Failed"),
                    'message' => "<p>" . sprintf(gT("Only %s new dummy participants were added after %s trials."), $newDummyToken, $invalidtokencount)
                        . gT("Try with a bigger access code length.") . "</p>"
                        . "\n<input type='button' value='"
                        . gT("Browse participants") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/browse/surveyid/$iSurveyId") . "', '_top')\" />\n"
                );
                $this->renderWrappedTemplate('token', array('message' => $message), $aData);
            }
        } else {
            // default values
            $aData['firstname'] = '';
            $aData['lastname'] = '';
            $aData['email'] = '';
            $aData['token'] = '';
            $aData['language'] = Survey::model()->findByPk($iSurveyId)->language;
            $aData['sent'] = 'N';
            $aData['remindersent'] = 'N';
            $aData['completed'] = 'N';
            $aData['usesleft'] = 1;
            $aData['validfrom'] = null;
            $aData['validuntil'] = null;
            $aData['amount'] = 100;
            $aData['tokenlength'] = !empty(Token::model($iSurveyId)->survey->oOptions->tokenlength) ? Token::model($iSurveyId)->survey->oOptions->tokenlength : 15;

            $thissurvey = getSurveyInfo($iSurveyId);
            $aData['thissurvey'] = $thissurvey;
            $aData['surveyid'] = $iSurveyId;

            $aData['dateformatdetails'] = getDateFormatData(Yii::app()->session['dateformat'], App()->language);
            $aData['aAttributeFields'] = getParticipantAttributes($iSurveyId);

            $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
                '/surveyAdministration/partial/topbar/surveyTopbarRight_view',
                [
                    'showSaveAndCloseButton' => true,
                    'showWhiteCloseButton' => true,
                    // Save button doesn’t apply to this screen
                    'showSaveButton' => false,
                    'closeUrl' => Yii::app()->createUrl(
                        "admin/tokens",
                        [
                            "sa" => 'index',
                            "surveyid" => $iSurveyId,
                        ]
                    )
                ],
                true
            );

            $this->renderWrappedTemplate('token', array('dummytokenform'), $aData);
        }
    }

    /**
     * Handle managetokenattributes action
     * @param int $iSurveyId
     * @return void
     */
    public function managetokenattributes($iSurveyId)
    {
        $iSurveyId = (int) $iSurveyId;
        $oSurvey = Survey::model()->findByPk($iSurveyId);

        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update') && !Permission::model()->hasSurveyPermission($iSurveyId, 'surveysettings', 'update')) {
            Yii::app()->session['flashmessage'] = gT("You do not have permission to access this page.");
            $this->getController()->redirect(array("/surveyAdministration/view/surveyid/{$iSurveyId}"));
        }
        // CHECK TO SEE IF A Survey participants table EXISTS FOR THIS SURVEY
        $bTokenExists = $oSurvey->hasTokensTable;
        if (!$bTokenExists) {
            //If no tokens table exists
            $this->newtokentable($iSurveyId);
        }
        Yii::app()->loadHelper("surveytranslator");

        $aData = array();
        $aData['sidemenu']['state'] = false;
        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyId . ")";
        $aData['sidemenu']["token_menu"] = true;

        $aData['thissurvey'] = $oSurvey->attributes;
        $aData['surveyid'] = $iSurveyId;
        $aMandatoryAttributes = $oSurvey->getTokenEncryptionOptions();
        $aAttributes = getAttributeFieldNames($iSurveyId);
        $aData['tokenfields'] = array_merge(array_keys($aMandatoryAttributes['columns']), $aAttributes);

        $aMandatoryList = array();
        $aAttributesDesc = $oSurvey->decodedAttributedescriptions;
        foreach ($aMandatoryAttributes['columns'] as $key => $attribute) {
            $aAttributesDesc[$key] = array(
                'coreattribute' => true,
                'mandatory' => $key == 'email' ? true : false,
                'encrypted' => $attribute,
                'show_register' => true,
                'description' => gT('Mandatory attribute'),
                'cpdbmap' => ''
            );
            $aMandatoryList[] = $key;
        }
        $aData['tokenfielddata'] = $aAttributesDesc;
        // Prepare token field list for dropDownList
        $tokenfieldlist = array();
        foreach ($aData['tokenfields'] as $tokenfield) {
            if (isset($aData['tokenfielddata'][$tokenfield]) && array_key_exists('description', $aData['tokenfielddata'][$tokenfield])) {
                $description = $aData['tokenfielddata'][$tokenfield]['description'];
            } else {
                $description = "";
            }
            $description = sprintf(gT("Attribute %s (%s)"), str_replace("attribute_", "", (string) $tokenfield), $description);
            if (!in_array($tokenfield, $aMandatoryList)) {
                $tokenfieldlist[] = array("id" => $tokenfield, "description" => $description);
            }
        }
        $aData['tokenfieldlist'] = $tokenfieldlist;
        $languages = array_merge((array) Survey::model()->findByPk($iSurveyId)->language, Survey::model()->findByPk($iSurveyId)->additionalLanguages);
        $captions = array();
        foreach ($languages as $language) {
            $captions[$language] = SurveyLanguageSetting::model()->findByAttributes(array('surveyls_survey_id' => $iSurveyId, 'surveyls_language' => $language))->attributeCaptions;
        }
        $aData['languages'] = $languages;
        $aData['tokencaptions'] = $captions;
        $aData['nrofattributes'] = 0;
        $oToken = TokenDynamic::model($iSurveyId)->find();
        $aData['examplerow'] = $oToken;
        $aData['aCPDBAttributes'][''] = gT('(none)');
        foreach (ParticipantAttributeName::model()->getCPDBAttributes() as $aCPDBAttribute) {
            $aData['aCPDBAttributes'][$aCPDBAttribute['attribute_id']] = $aCPDBAttribute['attribute_name'];
        }

        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/surveyAdministration/partial/topbar/surveyTopbarRight_view',
            [
                'showCloseButton' => true,
                'closeUrl' => Yii::app()->createUrl(
                    "admin/tokens",
                    [
                        "sa" => 'index',
                        "surveyid" => $iSurveyId,
                    ]
                )
            ],
            true
        );

        $sodium = Yii::app()->sodium;
        $aData['bEncrypted'] = $sodium->bLibraryExists;
        $this->renderWrappedTemplate('token', array('managetokenattributes'), $aData);
    }

    /**
     * Update token attributes
     * @param int $iSurveyId
     * @return void
     */
    public function updatetokenattributes($iSurveyId)
    {
        $iSurveyId = (int) $iSurveyId;
        $oSurvey = Survey::model()->findByPk($iSurveyId);
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update') && !Permission::model()->hasSurveyPermission($iSurveyId, 'surveysettings', 'update')) {
            Yii::app()->session['flashmessage'] = gT("You do not have permission to access this page.");
            $this->getController()->redirect(array("/surveyAdministration/view/surveyid/{$iSurveyId}"));
        }
        if (!$oSurvey->hasTokensTable) {
            // If no tokens table exists
            $this->newtokentable($iSurveyId);
        }

        $number2add = sanitize_int(Yii::app()->request->getPost('addnumber'), 1, 100);
        $tokenattributefieldnames = getAttributeFieldNames($iSurveyId);
        $i = 1;

        for ($b = 0; $b < $number2add; $b++) {
            while (in_array('attribute_' . $i, $tokenattributefieldnames) !== false) {
                $i++;
            }
            $tokenattributefieldnames[] = 'attribute_' . $i;
            Yii::app()->db->createCommand(Yii::app()->db->getSchema()->addColumn("{{tokens_" . intval($iSurveyId) . "}}", 'attribute_' . $i, 'text'))->execute();
        }

        Yii::app()->db->schema->getTable($oSurvey->tokensTableName, true); // Refresh schema cache just in case the table existed in the past
        LimeExpressionManager::SetDirtyFlag(); // so that knows that survey participants tables have changed

        Yii::app()->session['flashmessage'] = sprintf(gT("%s field(s) were successfully added."), $number2add);
        Yii::app()->getController()->redirect(array("/admin/tokens/sa/managetokenattributes/surveyid/$iSurveyId"));
    }

    /**
     * Delete token attributes
     * @param int $iSurveyId
     * @return void
     */
    public function deletetokenattributes($iSurveyId)
    {
        $iSurveyId = (int) $iSurveyId;
        $oSurvey = Survey::model()->findByPk($iSurveyId);
        // CHECK TO SEE IF A Survey participants table EXISTS FOR THIS SURVEY
        if (!$oSurvey->hasTokensTable) {
            Yii::app()->session['flashmessage'] = gT("No survey participants table.");
            $this->getController()->redirect($this->getController()->createUrl("/surveyAdministration/view/surveyid/{$iSurveyId}"));
        }
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update') && !Permission::model()->hasSurveyPermission($iSurveyId, 'surveysettings', 'update')) {
            Yii::app()->session['flashmessage'] = gT("You do not have permission to access this page.");
            $this->getController()->redirect($this->getController()->createUrl("/surveyAdministration/view/surveyid/{$iSurveyId}"));
        }

        $aData = array();
        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['surveyid'] = $iSurveyId;
        $confirm = Yii::app()->request->getPost('confirm', '');
        $cancel = Yii::app()->request->getPost('cancel', '');
        $tokenfields = getAttributeFieldNames($iSurveyId);
        $sAttributeToDelete = Yii::app()->request->getPost('deleteattribute', '');
        if (!in_array($sAttributeToDelete, $tokenfields)) {
            $sAttributeToDelete = false;
        }
        if ($cancel == 'cancel') {
            Yii::app()->getController()->redirect(Yii::app()->getController()->createUrl("/admin/tokens/sa/managetokenattributes/surveyid/$iSurveyId"));
        } elseif ($confirm != 'confirm' && $sAttributeToDelete) {
            $aData['sidemenu']['state'] = false;
            $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyId . ")";
            $topbarData = TopbarConfiguration::getTokensTopbarData($iSurveyId);
            $aData['topbar']['middleButtons'] = Yii::app()->getController()->renderPartial(
                '/surveyAdministration/partial/topbar_tokens/leftSideButtons',
                $topbarData,
                true
            );
            $this->renderWrappedTemplate('token', array('message' => array(
                'title' => sprintf(gT("Delete participant attribute %s"), $sAttributeToDelete),
                'message' => "<p>" . gT("If deleted, all information stored in this attribute field will be lost.") . "</p>\n"
                    . CHtml::form(array("admin/tokens/sa/deletetokenattributes/surveyid/{$iSurveyId}"), 'post', array('id' => 'attributenumber'))
                    . CHtml::hiddenField('deleteattribute', $sAttributeToDelete)
                    . CHtml::hiddenField('sid', $iSurveyId)
                    . CHtml::htmlButton(gT('Cancel'), array('type' => 'submit', 'value' => 'cancel', 'name' => 'cancel', 'class' => 'btn btn-outline-secondary'))
                    . '&nbsp;'
                    . CHtml::htmlButton(gT('Delete attribute'), array('type' => 'submit', 'value' => 'confirm', 'name' => 'confirm', 'class' => 'btn btn-danger'))
                    . CHtml::endForm()
            )), $aData);
        } elseif ($sAttributeToDelete) {
            // Update field attributedescriptions in survey table
            $aTokenAttributeDescriptions = $oSurvey->decodedAttributedescriptions;
            unset($aTokenAttributeDescriptions[$sAttributeToDelete]);
            Survey::model()->updateByPk($iSurveyId, array('attributedescriptions' => json_encode($aTokenAttributeDescriptions)));

            $sTableName = "{{tokens_" . intval($iSurveyId) . "}}";
            Yii::app()->db->createCommand(Yii::app()->db->getSchema()->dropColumn($sTableName, $sAttributeToDelete))->execute();
            Yii::app()->db->schema->getTable($sTableName, true); // Refresh schema cache
            LimeExpressionManager::SetDirtyFlag();
            Yii::app()->session['flashmessage'] = sprintf(gT("Attribute %s was deleted."), $sAttributeToDelete);
            Yii::app()->getController()->redirect(Yii::app()->getController()->createUrl("/admin/tokens/sa/managetokenattributes/surveyid/$iSurveyId"));
        } else {
            Yii::app()->session['flashmessage'] = gT("The selected attribute was invalid.");
            Yii::app()->getController()->redirect(Yii::app()->getController()->createUrl("/admin/tokens/sa/managetokenattributes/surveyid/$iSurveyId"));
        }
    }

    /**
     * updatetokenattributedescriptions action
     * @param int $iSurveyId
     * @return void
     */
    public function updatetokenattributedescriptions($iSurveyId)
    {
        $iSurveyId = (int)$iSurveyId;
        $oSurvey = Survey::model()->findByPk($iSurveyId);
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update') && !Permission::model()->hasSurveyPermission($iSurveyId, 'surveysettings', 'update')) {
            Yii::app()->session['flashmessage'] = gT("You do not have permission to access this page.");
            $this->getController()->redirect(["/surveyAdministration/view/surveyid/{$iSurveyId}"]);
        }

        $languages = array_merge((array)$oSurvey->language, $oSurvey->additionalLanguages);
        $fieldcontents = [];
        $captions = [];
        $aTokenencryptionoptions['enabled'] = 'Y';

        // find default attributes
        $aMandatoryAttributes = $oSurvey->getTokenEncryptionOptions();
        // default attributes
        foreach ($aMandatoryAttributes['columns'] as $column => $fieldname) {
            $aOptionsBeforeChange[$column]['encrypted'] = $aMandatoryAttributes['columns'][$column];

            $fieldcontents[$column] = [
                'encrypted' => Yii::app()->request->getPost('encrypted_' . $column) == '1' ? 'Y' : 'N',
            ];

            $aOptionsAfterChange[$column]['encrypted'] = $fieldcontents[$column]['encrypted'];
            $aTokenencryptionoptions['columns'][$column] = $fieldcontents[$column]['encrypted'];
        }

        // find custom attribute column names
        $tokenattributefieldnames = getAttributeFieldNames($iSurveyId);
        // custom attributes
        foreach ($tokenattributefieldnames as $fieldname) {
            if (isset(json_decode((string) $oSurvey->attributedescriptions)->$fieldname->encrypted)) {
                $aOptionsBeforeChange[$fieldname]['encrypted'] = json_decode((string) $oSurvey->attributedescriptions)->$fieldname->encrypted;
            } else {
                $aOptionsBeforeChange[$fieldname]['encrypted'] = 'N';
            }
            $fieldcontents[$fieldname] = [
                'description'   => strip_tags(Yii::app()->request->getPost('description_' . $fieldname, '')),
                'mandatory'     => Yii::app()->request->getPost('mandatory_' . $fieldname) == '1' ? 'Y' : 'N',
                'encrypted'     => Yii::app()->request->getPost('encrypted_' . $fieldname) == '1' ? 'Y' : 'N',
                'show_register' => Yii::app()->request->getPost('show_register_' . $fieldname) == '1' ? 'Y' : 'N',
                'cpdbmap'       => Yii::app()->request->getPost('cpdbmap_' . $fieldname)
            ];
            $aOptionsAfterChange[$fieldname]['encrypted'] = $fieldcontents[$fieldname]['encrypted'];
            foreach ($languages as $language) {
                $fieldNameValue = Yii::app()->request->getPost("caption_" . $fieldname . "_" . $language);
                $captions[$language][$fieldname] = $fieldNameValue;
            }
        }


        // custom token attributes realtime decryption/encryption
        // encryption/decryption MUST be done in a one synchronous step, either all succeeded or none
        $oDB = Yii::app()->db;
        $oTransaction = $oDB->beginTransaction();
        try {
            $this->updateEncryption($iSurveyId, $aOptionsAfterChange);

            // save token encryption options if everything was ok
            Survey::model()->updateByPk($iSurveyId, ['attributedescriptions' => json_encode($fieldcontents)]);

            foreach ($languages as $language) {
                $ls = SurveyLanguageSetting::model()->findByAttributes(['surveyls_survey_id' => $iSurveyId, 'surveyls_language' => $language]);
                $ls->surveyls_attributecaptions = !empty($captions[$language]) ? json_encode($captions[$language]) : '';
                $ls->save();
            }

            $oTransaction->commit();
            Yii::app()->session['flashmessage'] = gT('Participant attribute descriptions were successfully updated.');
        } catch (\Exception $e) {
            $oTransaction->rollback();
            return false;
        }


        //admin/tokens/sa/browse/surveyid/652779//
        $this->getController()->redirect(["/admin/tokens/sa/managetokenattributes/surveyid/{$iSurveyId}"]);
    }

    /**
     * Updates Token encryption settings based on $iSurveyId and configuration $aEncryptionSettings
     * @param string|int $iSurveyId
     * @param array $aEncryptionSettings The new attribute encryption status
     */
    public function updateEncryption($iSurveyId, $aEncryptionSettings)
    {
        $iSurveyId = (int) $iSurveyId;
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update') && !Permission::model()->hasSurveyPermission($iSurveyId, 'surveysettings', 'update')) {
            Yii::app()->session['flashmessage'] = gT("You do not have permission to access this page.");
            $this->getController()->redirect(array("/surveyAdministration/view/surveyid/{$iSurveyId}"));
        }
        $oDB = Yii::app()->db;
        $oSurvey = Survey::model()->findByPk($iSurveyId);
        $oTokens = Token::model($iSurveyId)->findAll();
        $aTokenencryptionoptions['enabled'] = 'Y';

        // find default attributes
        $aDefaultAttributes = $oSurvey->getTokenEncryptionOptions();
        // default attributes
        foreach ($aDefaultAttributes['columns'] as $column => $attributeName) {
            $aEncryptionSettingsOld[$column]['encrypted'] = $aDefaultAttributes['columns'][$column];

            $aEncryptionSettings[$column] = [
                'encrypted' => $aEncryptionSettings[$column]['encrypted'] === 'Y' ? 'Y' : 'N',
            ];
            $aTokenencryptionoptions['columns'][$column] = $aEncryptionSettings[$column]['encrypted'];
        }

        // custom attributes
        $aCustomAttributes = $oSurvey->getTokenAttributes();
        foreach ($aCustomAttributes as $column => $attributeName) {
            if (isset($attributeName['encrypted'])) {
                $aEncryptionSettingsOld[$column]['encrypted'] = $attributeName['encrypted'];
            } else {
                $aEncryptionSettingsOld[$column]['encrypted'] = 'N';
            }
            if (isset($aEncryptionSettings[$column])) {
                $aEncryptionSettings[$column] = [
                    'encrypted' => $aEncryptionSettings[$column]['encrypted'] === 'Y' ? 'Y' : 'N',
                ];
            }
        }

        foreach ($oTokens as $token) {
            $aUpdateData = [];
            foreach ($aEncryptionSettings as $column => $value) {
                if ($aEncryptionSettingsOld[$column]['encrypted'] == 'Y' && $aEncryptionSettings[$column]['encrypted'] == 'N') {
                    $aUpdateData[$column] = LSActiveRecord::decryptSingle($token->$column);
                } elseif ($aEncryptionSettingsOld[$column]['encrypted'] == 'N' && $aEncryptionSettings[$column]['encrypted'] == 'Y') {
                    $aUpdateData[$column] = LSActiveRecord::encryptSingle($token->$column);
                }
            }

            if (!empty($aUpdateData)) {
                $oDB->createCommand()->update('{{tokens_' . $iSurveyId . '}}', $aUpdateData, "tid=" . $token->tid);
            }
        }
        Survey::model()->updateByPk($iSurveyId, array('tokenencryptionoptions' => json_encode($aTokenencryptionoptions)));
    }

    /**
     * Handle email action
     * @param int $iSurveyId
     * @return void
     */
    public function email(int $iSurveyId)
    {
        $iSurveyId = (int) $iSurveyId;
        $aData = array();
        $survey = Survey::model()->findByPk($iSurveyId);

        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update')) {
            Yii::app()->session['flashmessage'] = gT("You do not have permission to access this page.");
            $this->getController()->redirect(array("/surveyAdministration/view/surveyid/{$iSurveyId}"));
        }

        if (!$survey->hasTokensTable) {
            // If no tokens table exists, redirect to create token page.
            $this->newtokentable($iSurveyId);
        }

        $aData['sidemenu']['state'] = false;
        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyId . ")";
        $aData['sidemenu']["token_menu"] = true;

        $aTokenIds = $this->getTokenIds();
        $sSubAction = $this->getSubAction();
        $bIsInvitation = $sSubAction == 'invite';

        Yii::app()->loadHelper('surveytranslator');
        Yii::app()->loadHelper('/admin/htmleditor');
        Yii::app()->session['FileManagerContext'] = "edit:emailsettings:{$iSurveyId}";
        initKcfinder();
        Yii::app()->loadHelper('replacements');

        $aSurveyLangs = Survey::model()->findByPk($iSurveyId)->additionalLanguages;
        $sBaseLanguage = Survey::model()->findByPk($iSurveyId)->language;
        array_unshift($aSurveyLangs, $sBaseLanguage);
        $aTokenFields = getTokenFieldsAndNames($iSurveyId, true);
        $iAttributes = 0;
        $bHtml = (getEmailFormat($iSurveyId) == 'html');

        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        foreach ($aSurveyLangs as $sSurveyLanguage) {
            $aData['thissurvey'][$sSurveyLanguage] = getSurveyInfo($iSurveyId, $sSurveyLanguage);
        }
        $aData['surveyid'] = $iSurveyId;
        $aData['sSubAction'] = $sSubAction;
        $aData['bEmail'] = $bIsInvitation;
        $aData['aSurveyLangs'] = $aData['surveylangs'] = $aSurveyLangs;
        $aData['baselang'] = $sBaseLanguage;
        $aData['tokenfields'] = array_keys($aTokenFields);
        $aData['nrofattributes'] = $iAttributes;
        $aData['tokenids'] = $aTokenIds;
        $aData['ishtml'] = $bHtml;
        $aData['reminderbutton'] = (Yii::app()->request->getParam('action') == "remind");

        $iMaxEmails = Yii::app()->getConfig('maxemails');

        $aData['topBar']['name'] = 'tokensTopbar_view';

        // TODO: Rename 'ok' to something meaningful.
        if (!Yii::app()->request->getPost('ok')) {
            $this->clearEmailSessionCache($iSurveyId);
            $this->showInviteOrReminderEmailForm($iSurveyId, $aSurveyLangs, $aData);
        } else {
            $aData['topBar']['hide'] = true;

            $SQLemailstatuscondition   = $this->getSQLemailstatuscondition();
            $SQLremindercountcondition = $this->getSQLremindercountcondition();
            $SQLreminderdelaycondition = $this->getSQLreminderdelaycondition($bIsInvitation);

            $tokenDynamic = TokenDynamic::model($iSurveyId);
            if (Yii::app()->request->getPost('partialonly')) {
                $tokenDynamic->with([
                    'responses' => [
                        'select' => false,
                        'joinType' => 'INNER JOIN',
                        'condition' => 'responses.submitdate IS NULL',
                    ],
                ]);
            }

            $ctresult = $tokenDynamic->findUninvited($aTokenIds, 0, $bIsInvitation, $SQLemailstatuscondition, $SQLremindercountcondition, $SQLreminderdelaycondition);
            $ctcount = count($ctresult);

            $emresult = array_slice($ctresult, 0, $iMaxEmails);
            $emcount = count($emresult);

            foreach ($aSurveyLangs as $language) {
                $sSubject[$language] = Yii::app()->request->getPost('subject_' . $language, '');
                $sMessage[$language] = Yii::app()->request->getPost('message_' . $language, '');
                if ($bHtml) {
                    $sMessage[$language] = html_entity_decode((string) $sMessage[$language], ENT_QUOTES, Yii::app()->getConfig("emailcharset"));
                }
            }

            $tokenoutput = "";
            $bInvalidDate = false;
            $bSendError = false;
            if ($emcount > 0) {
                $mail = \LimeMailer::getInstance(\LimeMailer::ResetComplete);
                $mail->setSurvey($iSurveyId);
                $mail->emailType = $sSubAction;
                $mail->replaceTokenAttributes = true;
                foreach ($emresult as $emrow) {
                    $mailLanguage = $emrow['language'];
                    if (empty($mailLanguage)) {
                        $mailLanguage = $sBaseLanguage;
                    }
                    if (!in_array($mailLanguage, Survey::model()->findByPk($iSurveyId)->getAllLanguages())) {
                        $mailLanguage = $sBaseLanguage;
                        $tokenoutput .= CHtml::tag(
                            "div",
                            array('class' => 'text-danger'),
                            sprintf(gT("Invalid language %s for token ID: %s."), $emrow['language'], $emrow['tid'])
                        );
                    }
                    $mail = \LimeMailer::getInstance();
                    if ($this->tokenIsSetInEmailCache($iSurveyId, $emrow['tid'], $bIsInvitation)) {
                        // The email has already been send this session, skip.
                        // Happens if user reloads page or double clicks on "Send".
                        if ($bIsInvitation) {
                            $tokenoutput .= sprintf(gT("Invitation %s skipped, already sent."), $emrow['tid']) . "<br/>";
                        } else {
                            $tokenoutput .= sprintf(gT("Reminder %s skipped, already sent."), $emrow['tid']) . "<br/>";
                        }
                        continue;
                    }
                    $mail->setToken($emrow['token']);
                    $mail->setFrom(Yii::app()->request->getPost('from_' . $mailLanguage));
                    $mail->rawSubject = $sSubject[$mailLanguage];
                    $mail->rawBody = $sMessage[$mailLanguage];

                    // If "Bypass date control before sending email" is disabled, check the token validity range
                    if (!Yii::app()->request->getPost('bypassdatecontrol')) {
                        $now = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig('timeadjust'));
                        $fieldsarray = [
                            '{FIRSTNAME}' => $emrow['firstname'],
                            '{LASTNAME}' => $emrow['lastname'],
                            '{EMAIL}' => $emrow['email'],
                        ];
                        if (trim((string) $emrow['validfrom']) != '' && strtotime((string) $emrow['validfrom']) > strtotime((string) $now)) {
                            $tokenoutput .= $emrow['tid'] . " " . htmlspecialchars((string) ReplaceFields(gT("Email to {FIRSTNAME} {LASTNAME} ({EMAIL}) delayed: Access code is not yet valid.", 'unescaped'), $fieldsarray)) . "<br />";
                            $bInvalidDate = true;
                            continue;
                        } elseif (trim((string) $emrow['validuntil']) != '' && strtotime((string) $emrow['validuntil']) < strtotime((string) $now)) {
                            $tokenoutput .= $emrow['tid'] . " " . htmlspecialchars((string) ReplaceFields(gT("Email to {FIRSTNAME} {LASTNAME} ({EMAIL}) skipped: Access code is not valid anymore.", 'unescaped'), $fieldsarray)) . "<br />";
                            $bInvalidDate = true;
                            continue;
                        }
                    }

                    // If all checks passed, send the email
                    $success = $mail->sendMessage();
                    $stringInfo = CHtml::encode("{$emrow['tid']}: {$emrow['firstname']} {$emrow['lastname']} ({$emrow['email']}).");
                    if ($success) {
                        // Load token to set as sent, no need to check existence ? we just send the email
                        $oToken = Token::model($iSurveyId)->findByPk($emrow['tid'])->decrypt();
                        if ($bIsInvitation) {
                            $tokenoutput .= gT("Invitation sent to:");
                            $oToken->sent = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig("timeadjust"));
                        } else {
                            $tokenoutput .= gT("Reminder sent to:");
                            $oToken->remindersent = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig("timeadjust"));
                            $oToken->remindercount++;
                        }
                        $tokenSaveError = "";
                        if (
                            !$oToken->save( // Save only uncrypted value, no need to crypt/encryt
                                true, // Validate but only the date part
                                array(
                                    'sent',
                                    'remindersent',
                                    'remindercount'
                                )
                            )
                        ) {
                            // Add the error when try to save token
                            $tokenSaveError = CHtml::errorSummary(
                                $oToken,
                                CHtml::tag("div", array('class' => 'text-danger'), sprintf(gT("An error occurred when saving the sent date for this participant (ID: %s)."), $emrow['tid']))
                            );
                        }
                        // Mark token email as send this session.
                        // NB: This cache is cleared on form page for invitation/reminder.
                        $sType = $bIsInvitation ? 'i' : 'r';
                        $_SESSION[$this->getEmailCacheName($iSurveyId)][$sType][$emrow['tid']] = 1;

                        //Update central participant survey_links
                        if (!empty($emrow['participant_id'])) {
                            $slquery = SurveyLink::model()->find('participant_id = :pid AND survey_id = :sid AND token_id = :tid', array(':pid' => $emrow['participant_id'], ':sid' => $iSurveyId, ':tid' => $emrow['tid']));
                            if (!is_null($slquery)) {
                                $slquery->date_invited = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig("timeadjust"));
                                $slquery->save();
                            }
                        }
                        $tokenoutput .= $stringInfo . "<br />\n";
                        if (Yii::app()->getConfig("emailsmtpdebug") > 1) {
                            $tokenoutput .= $mail->getDebug('html');
                        }
                        $tokenoutput .= $tokenSaveError;
                    } else {
                        $tokenoutput .= $stringInfo . CHtml::tag("span", array('class' => "text-danger"), sprintf(gT("Error message: %s"), $mail->getError())) . "<br>\n";
                        if (Yii::app()->getConfig("emailsmtpdebug") > 0) {
                            $tokenoutput .= $mail->getDebug('html');
                        }
                        $bSendError = true;
                    }

                    unset($fieldsarray);
                }
                // Closes a still active SMTP connection if it exists
                $mail->smtpClose();
                $aViewUrls = array();
                $aData['tokenoutput'] = $tokenoutput;

                if ($ctcount > $emcount) {
                    $i = 0;
                    if (isset($aTokenIds)) {
                        while ($i < $iMaxEmails) {
                            array_shift($aTokenIds);
                            $i++;
                        }
                        $aData['tids'] = implode('|', $aTokenIds);
                    }

                    $aData['lefttosend'] = $ctcount - $iMaxEmails;
                    $aData['nosidebodyblock'] = true;
                    $aViewUrls[] = 'emailwarning';
                } else {
                    if (!$bInvalidDate && !$bSendError) {
                        $aData['tokenoutput'] .= "<strong class='result success text-success'>" . gT("All emails were sent.") . "<strong>";
                    } else {
                        $aData['tokenoutput'] .= "<strong class='result warning text-danger'>" . gT("Not all emails were sent:") . "<strong><ul class='list-unstyled'>";
                        if ($bInvalidDate) {
                            $aData['tokenoutput'] .= "<li>" . gT("Some entries had a validity date set which was not yet valid or not valid anymore.") . "</li>";
                        }
                        if ($bSendError) {
                            $aData['tokenoutput'] .= "<li>" . gT("Some emails were not sent because the server did not accept the email(s) or some other error occurred.") . "</li>";
                        }
                        $aData['tokenoutput'] .= '</ul>';
                        $aData['tokenoutput'] .= '<p class="mt-3"><a href="' . App()->createUrl('admin/tokens/sa/index/surveyid/' . $iSurveyId) . '" title="" class="btn btn-outline-secondary">' . gT("Ok") . '</a></p>';
                    }
                }
                $aViewUrls[] = 'emailpost';
                $this->renderWrappedTemplate('token', $aViewUrls, $aData);
            } else {
                $aData['sidemenu']['state'] = false;

                $this->renderWrappedTemplate(
                    'token',
                    array(
                        'message' => array(
                            'title' => gT("Warning"),
                            'message' => gT("There were no eligible emails to send. This will be because none satisfied the criteria of:")
                                . "<br/>&nbsp;<ul class='list-unstyled'><li>" . gT("having a valid email address") . "</li>"
                                . "<li>" . gT("not having been sent an invitation already") . "</li>"
                                . "<li>" . gT("not having already completed the survey") . "</li>"
                                . "<li>" . gT("having an access code") . "</li>"
                                . "<li>" . gT("having at least one use left") . "</li></ul>"
                                . '<p class="mt-3"><a href="' . App()->createUrl('admin/tokens/sa/index/surveyid/' . $iSurveyId) . '" title="" class="btn btn-cancel ">' . gT("Cancel") . '</a></p>'
                        )
                    ),
                    $aData
                );
            }
        }
    }

    /**
     * Export Dialog
     * @param int $iSurveyId
     * @return void
     */
    public function exportdialog(int $iSurveyId)
    {
        $iSurveyId = (int)$iSurveyId;
        $survey = Survey::model()->findByPk($iSurveyId);
        $aData = array();

        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyId . ")";
        $aData['sidemenu']["token_menu"] = true;
        $aData['sidemenu']['state'] = false;
        $aData['showDownloadButton'] = true;

        // CHECK TO SEE IF A Survey participants table EXISTS FOR THIS SURVEY
        $iSurveyId = (int) $iSurveyId;
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'export')) {
            Yii::app()->session['flashmessage'] = gT("You do not have permission to access this page.");
            $this->getController()->redirect(array("/surveyAdministration/view/surveyid/{$iSurveyId}"));
        }
        if (!$survey->hasTokensTable) {
            // If no tokens table exists
            $this->newtokentable($iSurveyId);
        }

        if (!is_null(Yii::app()->request->getPost('submit'))) {
            Yii::app()->loadHelper("export");
            tokensExport($iSurveyId);
        } else {
            $aData['surveyid'] = $iSurveyId;
            $aData['thissurvey'] = getSurveyInfo($iSurveyId); // For tokenbar view
            $aData['sAction'] = App()->createUrl("admin/tokens", array("sa" => "exportdialog", "surveyid" => $iSurveyId));
            $aData['aButtons'] = array(
                gT('Export participants') => array(
                    'type' => 'submit',
                    'name' => 'submit',
                ),
            );
            $oSurvey = Survey::model()->findByPk($iSurveyId);

            $aOptionsStatus = array('0' => gT('All participants'), '1' => gT('Completed'), '2' => gT('Not completed'));
            if (!$oSurvey->isAnonymized && $oSurvey->isActive) {
                $aOptionsStatus['3'] = gT('Not started');
                $aOptionsStatus['4'] = gT('Started but not yet completed');
            }

            $oTokenLanguages = Token::model($iSurveyId)->findAll(array('select' => 'language', 'group' => 'language'));
            $aFilterByLanguage = array('' => gT('All'));
            foreach ($oTokenLanguages as $oTokenLanguage) {
                $sLanguageCode = sanitize_languagecode($oTokenLanguage->language);
                $aFilterByLanguage[$sLanguageCode] = getLanguageNameFromCode($sLanguageCode, false);
            }
            // The following array does not seem to be used at all by the view, yet
            $aData['aSettings'] = array(
                'tokenstatus' => array(
                    'type' => 'select',
                    'label' => gT('Survey status:'),
                    'options' => $aOptionsStatus,
                ),
                'invitationstatus' => array(
                    'type' => 'select',
                    'label' => gT('Invitation status:'),
                    'options' => array(
                        '0' => gT('All'),
                        '1' => gT('Invited'),
                        '2' => gT('Not invited'),
                    ),
                ),
                'reminderstatus' => array(
                    'type' => 'select',
                    'label' => gT('Reminder status:'),
                    'options' => array(
                        '0' => gT('All'),
                        '1' => gT('Reminder(s) sent'),
                        '2' => gT('No reminder(s) sent'),
                    ),
                ),
                'tokenlanguage' => array(
                    'type' => 'select',
                    'label' => gT('Filter by language:'),
                    'options' => $aFilterByLanguage,
                ),
                'filteremail' => array(
                    'type' => 'string',
                    'label' => gT('Filter by email address:'),
                    'help' => gT('Only export entries which contain this string in the email address.'),
                ),
                'tokendeleteexported' => array(
                    'type' => 'checkbox',
                    'label' => gT('Delete exported participants:'),
                    'help' => gT('Warning: Deleted participants entries cannot be recovered.'),
                ),
                'maskequations' => array(
                    'type' => 'checkbox',
                    'label' => gT('Quote equations'),
                    'help' => gT('Warning: Quotes all content that starts with an equal sign to prevent CSV injections'),
                ),
            );

            // White Close Button
            //$aData['showWhiteCloseButton'] = true;
            $aData['closeUrl'] = Yii::app()->createUrl(
                "admin/tokens",
                [
                    "sa" => 'index',
                    "surveyid" => $iSurveyId,
                ]
            );

            //$aData['topBar']['name'] = 'tokensTopbar_view';
            //$aData['topBar']['rightSideView'] = 'tokensTopbarRight_view';

            $topbarData = TopbarConfiguration::getTokensTopbarData($iSurveyId);
            $aData['topbar']['showWhiteCloseButton'] = true;
            $aData['topbar']['showDownloadButton'] = true;
            $aData['topbar']['closeUrl'] = Yii::app()->createUrl(
                "admin/tokens",
                [
                    "sa" => 'index',
                    "surveyid" => $iSurveyId,
                ]
            );
            $topbarData = array_merge($topbarData, $aData['topbar']);
            $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
                '/surveyAdministration/partial/topbar_tokens/rightSideButtons',
                $topbarData,
                true
            );

            $this->renderWrappedTemplate('token', array('exportdialog'), $aData);
        }
    }

    /**
     * Performs a ldap import
     * @param int $iSurveyId
     * @return void
     */
    public function importldap($iSurveyId)
    {
        $iSurveyId = (int) $iSurveyId;
        $survey = Survey::model()->findByPk($iSurveyId);
        $aData = array();
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'import')) {
            Yii::app()->session['flashmessage'] = gT("You do not have permission to access this page.");
            $this->getController()->redirect(array("/surveyAdministration/view/surveyid/{$iSurveyId}"));
        }
        if (!$survey->hasTokensTable) {
            // If no tokens table exists
            $this->newtokentable($iSurveyId);
        }


        $aData['sidemenu']['state'] = false;
        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyId . ")";
        $aData['sidemenu']["token_menu"] = true;
        $aData['topBar']['name'] = 'tokensTopbar_view';

        Yii::app()->loadConfig('ldap');
        Yii::app()->loadHelper('ldap');

        $tokenoutput = '';

        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['iSurveyId'] = $aData['surveyid'] = $iSurveyId;
        $aData['ldap_queries'] = Yii::app()->getConfig('ldap_queries');
        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/surveyAdministration/partial/topbar/surveyTopbarRight_view',
            [
                'showCloseButton' => true,
                'closeUrl' => Yii::app()->createUrl(
                    "admin/tokens",
                    [
                        "sa" => 'index',
                        "surveyid" => $iSurveyId,
                    ]
                )
            ],
            true
        );

        if (!Yii::app()->request->getPost('submit')) {
            $this->renderWrappedTemplate('token', array('ldapform'), $aData);
        } else {
            $filterduplicatetoken = (Yii::app()->request->getPost('filterduplicatetoken') && (Yii::app()->request->getPost('filterduplicatetoken') == 'on' || Yii::app()->request->getPost('filterduplicatetoken') == '1'));
            $filterblankemail = (Yii::app()->request->getPost('filterblankemail') && (Yii::app()->request->getPost('filterblankemail') == 'on' || Yii::app()->request->getPost('filterblankemail') == '1'));

            $ldap_queries = Yii::app()->getConfig('ldap_queries');
            $ldap_server = Yii::app()->getConfig('ldap_server');

            $duplicatelist = array();
            $invalidemaillist = array();
            $tokenoutput .= "\t<tr><td colspan='2' height='4'><strong>"
                . gT("Uploading LDAP Query") . "</strong></td></tr>\n"
                . "\t<tr><td align='center'>\n";
            $ldapq = Yii::app()->request->getPost('ldapQueries'); // the ldap query id

            $ldap_server_id = $ldap_queries[$ldapq]['ldapServerId'];
            if (
                isset($ldap_server[$ldap_server_id]['encoding']) &&
                $ldap_server[$ldap_server_id]['encoding'] != 'utf-8' &&
                $ldap_server[$ldap_server_id]['encoding'] != 'UTF-8'
            ) {
                $ldapencoding = $ldap_server[$ldap_server_id]['encoding'];
            } else {
                $ldapencoding = '';
            }

            // define $attrlist: list of attributes to read from users' entries
            $attrparams = array(
                'firstname_attr', 'lastname_attr',
                'email_attr', 'token_attr', 'language'
            );

            $aTokenAttr = getAttributeFieldNames($iSurveyId);
            foreach ($aTokenAttr as $thisattrfieldname) {
                $attridx = substr((string) $thisattrfieldname, 10); // the 'attribute_' prefix is 10 chars long
                $attrparams[] = "attr" . $attridx;
            }

            foreach ($attrparams as $id => $attr) {
                if (
                    array_key_exists($attr, $ldap_queries[$ldapq]) &&
                    $ldap_queries[$ldapq][$attr] != ''
                ) {
                    $attrlist[] = $ldap_queries[$ldapq][$attr];
                }
            }

            // Open connection to server
            $ds = ldap_getCnx($ldap_server_id);

            if ($ds) {
                // bind to server
                $resbind = ldap_bindCnx($ds, $ldap_server_id);

                if ($resbind) {
                    $ResArray = array();
                    $resultnum = ldap_doTokenSearch($ds, $ldapq, $ResArray, $iSurveyId);
                    $xz = 0; // imported token count
                    $xv = 0; // meet minim requirement count
                    $xy = 0; // check for duplicates
                    $duplicatecount = 0; // duplicate tokens skipped count
                    $invalidemailcount = 0;

                    if ($resultnum >= 1) {
                        foreach ($ResArray as $responseGroupId => $responseGroup) {
                            for ($j = 0; $j < $responseGroup['count']; $j++) {
                                // first let's initialize everything to ''
                                $myfirstname = '';
                                $mylastname = '';
                                $myemail = '';
                                $mylanguage = '';
                                $mytoken = '';
                                $myattrArray = array();

                                // The first 3 attrs MUST exist in the ldap answer
                                // ==> send PHP notice msg to apache logs otherwise
                                $meetminirequirements = true;
                                if (
                                    isset($responseGroup[$j][$ldap_queries[$ldapq]['firstname_attr']]) &&
                                    isset($responseGroup[$j][$ldap_queries[$ldapq]['lastname_attr']])
                                ) {
                                    // minimum requirement for ldap
                                    // * at least a firstanme
                                    // * at least a lastname
                                    // * if filterblankemail is set (default): at least an email address
                                    $myfirstname = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['firstname_attr']]);
                                    $mylastname = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['lastname_attr']]);
                                    if (isset($responseGroup[$j][$ldap_queries[$ldapq]['email_attr']])) {
                                        $myemail = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['email_attr']]);
                                        ++$xv;
                                    } elseif ($filterblankemail !== true) {
                                        $myemail = '';
                                        ++$xv;
                                    } else {
                                        $meetminirequirements = false;
                                    }
                                } else {
                                    $meetminirequirements = false;
                                }

                                // The following attrs are optional
                                if (isset($responseGroup[$j][$ldap_queries[$ldapq]['token_attr']])) {
                                    $mytoken = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['token_attr']]);
                                }

                                foreach ($aTokenAttr as $thisattrfieldname) {
                                    $attridx = substr((string) $thisattrfieldname, 10); // the 'attribute_' prefix is 10 chars long
                                    if (
                                        isset($ldap_queries[$ldapq]['attr' . $attridx]) &&
                                        isset($responseGroup[$j][$ldap_queries[$ldapq]['attr' . $attridx]])
                                    ) {
                                        $myattrArray[$attridx] = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['attr' . $attridx]]);
                                    }
                                }

                                if (isset($responseGroup[$j][$ldap_queries[$ldapq]['language']])) {
                                    $mylanguage = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['language']]);
                                }

                                // In case LDAP Server encoding isn't UTF-8, let's translate
                                // the strings to UTF-8
                                if ($ldapencoding != '') {
                                    $myfirstname = @mb_convert_encoding((string) $myfirstname, "UTF-8", $ldapencoding);
                                    $mylastname = @mb_convert_encoding((string) $mylastname, "UTF-8", $ldapencoding);
                                    foreach ($aTokenAttr as $thisattrfieldname) {
                                        $attridx = substr((string) $thisattrfieldname, 10); // the 'attribute_' prefix is 10 chars long
                                        @mb_convert_encoding((string) $myattrArray[$attridx], "UTF-8", $ldapencoding);
                                    }
                                }

                                // Now check for duplicates or bad formatted email addresses
                                $dupfound = false;
                                $invalidemail = false;
                                if ($filterduplicatetoken) {
                                    $dupquery = "SELECT count(tid) from {{tokens_" . intval($iSurveyId) . "}} where email=:email and firstname=:firstname and lastname=:lastname";
                                    $dupresult = Yii::app()->db->createCommand($dupquery)->bindParam(":email", $myemail, PDO::PARAM_STR)->bindParam(":firstname", $myfirstname, PDO::PARAM_STR)->bindParam(":lastname", $mylastname, PDO::PARAM_STR)->queryScalar();
                                    if ($dupresult > 0) {
                                        $dupfound = true;
                                        $duplicatelist[] = $myfirstname . " " . $mylastname . " (" . $myemail . ")";
                                        $xy++;
                                    }
                                }
                                if ($filterblankemail && $myemail == '') {
                                    $invalidemail = true;
                                    $invalidemaillist[] = $myfirstname . " " . $mylastname . " ( )";
                                } elseif ($myemail != '' && !validateEmailAddress($myemail)) {
                                    $invalidemail = true;
                                    $invalidemaillist[] = $myfirstname . " " . $mylastname . " (" . $myemail . ")";
                                }

                                if ($invalidemail) {
                                    ++$invalidemailcount;
                                } elseif ($dupfound) {
                                    ++$duplicatecount;
                                } elseif ($meetminirequirements === true) {
                                    // No issue, let's import
                                    $iq = "INSERT INTO {{tokens_" . intval($iSurveyId) . "}} \n"
                                        . "(firstname, lastname, email, emailstatus, token, language";

                                    foreach ($aTokenAttr as $thisattrfieldname) {
                                        $attridx = substr((string) $thisattrfieldname, 10); // the 'attribute_' prefix is 10 chars long
                                        if (!empty($myattrArray[$attridx])) {
                                            $iq .= ", " . Yii::app()->db->quoteColumnName($thisattrfieldname);
                                        }
                                    }
                                    $iq .= ") \n"
                                        . "VALUES (" . Yii::app()->db->quoteValue($myfirstname) . ", " . Yii::app()->db->quoteValue($mylastname) . ", " . Yii::app()->db->quoteValue($myemail) . ", 'OK', " . Yii::app()->db->quoteValue($mytoken) . ", " . Yii::app()->db->quoteValue($mylanguage) . "";

                                    foreach ($aTokenAttr as $thisattrfieldname) {
                                        $attridx = substr((string) $thisattrfieldname, 10); // the 'attribute_' prefix is 10 chars long
                                        if (!empty($myattrArray[$attridx])) {
                                            $iq .= ", " . Yii::app()->db->quoteValue($myattrArray[$attridx]) . "";
                                        } // dbquote_all encloses str with quotes
                                    }
                                    $iq .= ")";
                                    $ir = Yii::app()->db->createCommand($iq)->execute();
                                    if (!$ir) {
                                        $duplicatecount++;
                                    }
                                    $xz++;
                                    // or die ("Couldn't insert line<br />\n$buffer<br />\n".htmlspecialchars($connect->ErrorMsg())."<pre style='text-align: left'>$iq</pre>\n");
                                }
                            } // End for each entry
                        } // End foreach responseGroup
                    } // End of if resnum >= 1

                    $aData['duplicatelist'] = $duplicatelist;
                    $aData['invalidemaillist'] = $invalidemaillist;
                    $aData['invalidemailcount'] = $invalidemailcount;
                    $aData['resultnum'] = $resultnum;
                    $aData['xv'] = $xv;
                    $aData['xy'] = $xy;
                    $aData['xz'] = $xz;

                    $this->renderWrappedTemplate('token', array('ldappost'), $aData);
                } else {
                    $aData['showCloseButton'] = true;
                    $aData['topBar']['rightSideView'] = 'tokensTopbarRight_view';

                    $sErrorMessage = ldap_error($ds);
                    /* Try to get more error @see https://github.com/adldap/adLDAP/pull/142 */
                    if (defined('LDAP_OPT_DIAGNOSTIC_MESSAGE')) {
                        if (ldap_get_option($ds, LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error)) {
                            $sErrorMessage .= ' - ' . $extended_error;
                        }
                    }
                    $aData['sError'] = sprintf(gT("Can't bind to the LDAP directory. Error message: %s"), ldap_error($ds));
                    $this->renderWrappedTemplate('token', array('ldapform'), $aData);
                }
                try {
                    @ldap_close($ds);
                } catch (Exception $e) {
                    // No handling needed
                }
            } else {
                $aData['sError'] = gT("Can't connect to the LDAP directory");
                $this->renderWrappedTemplate('token', array('ldapform'), $aData);
            }
        }
    }

    /**
     * import from csv
     * @param int $iSurveyId
     * @return void
     */
    public function import($iSurveyId)
    {
        $aData = array();
        $iSurveyId = (int) $iSurveyId;
        $survey = Survey::model()->findByPk($iSurveyId);
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'import')) {
            Yii::app()->session['flashmessage'] = gT("You do not have permission to access this page.");
            $this->getController()->redirect(array("/surveyAdministration/view/surveyid/{$iSurveyId}"));
        }
        if (!$survey->hasTokensTable) {
            // If no tokens table exists
            $this->newtokentable($iSurveyId);
        }


        $aData['sidemenu']['state'] = false;
        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyId . ")";
        $aData['sidemenu']["token_menu"] = true;
        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/surveyAdministration/partial/topbar/surveyTopbarRight_view',
            [
                'showCloseButton' => true,
                'closeUrl' => Yii::app()->createUrl(
                    "admin/tokens",
                    [
                        "sa" => 'index',
                        "surveyid" => $iSurveyId,
                    ]
                )
            ],
            true
        );
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'tokensimport.js');
        $aEncodings = aEncodingsArray();

        if (Yii::app()->request->isPostRequest) {
            $sUploadCharset = Yii::app()->request->getPost('csvcharset');
            if (!array_key_exists($sUploadCharset, $aEncodings)) {
                // Validate sUploadCharset
                $sUploadCharset = 'auto';
            }
            $bFilterDuplicateToken = Yii::app()->request->getPost('filterduplicatetoken');
            $bFilterBlankEmail = Yii::app()->request->getPost('filterblankemail');
            $bAllowInvalidEmail = Yii::app()->request->getPost('allowinvalidemail');

            $aAttrFieldNames = getAttributeFieldNames($iSurveyId);
            $aDuplicateList = array();
            $aInvalidTokenList = array();
            $aInvalidEmailList = array();
            $aInvalidFormatList = array();
            $aPluginErrorMessageList = array();
            $aModelErrorList = array();
            $aFirstLine = array();

            // Check file size and redirect on error
            $uploadValidator = new LimeSurvey\Models\Services\UploadValidator();
            $uploadValidator->redirectOnError('the_file', \Yii::app()->createUrl('admin/tokens', array('sa' => 'import', 'surveyid' => $iSurveyId)));

            $oFile = CUploadedFile::getInstanceByName("the_file");
            $sPath = Yii::app()->getConfig('tempdir');
            $sFileName = $sPath . '/' . randomChars(20);
            if (strtolower((string) $oFile->getExtensionName()) != 'csv') {
                Yii::app()->setFlashMessage(gT("Only CSV files are allowed."), 'error');
            } elseif (!@$oFile->saveAs($sFileName)) {
                Yii::app()->setFlashMessage(sprintf(gT("Upload file not found. Check your permissions and path (%s) for the upload directory"), $sPath), 'error');
            } else {
                $iRecordImported = 0;
                $iRecordCount =  0;
                $iRecordOk = 0;
                $iInvalidEmailCount = 0; // Count invalid email imported
                // This allows to read file with MAC line endings too
                @ini_set('auto_detect_line_endings', '1');
                // open it and trim the endings
                $aTokenListArray = file($sFileName, FILE_SKIP_EMPTY_LINES);
                $sBaseLanguage = Survey::model()->findByPk($iSurveyId)->language;
                if (!Yii::app()->request->getPost('filterduplicatefields') || (Yii::app()->request->getPost('filterduplicatefields') && count(Yii::app()->request->getPost('filterduplicatefields')) == 0)) {
                    $aFilterDuplicateFields = array('firstname', 'lastname', 'email');
                } else {
                    $aFilterDuplicateFields = Yii::app()->request->getPost('filterduplicatefields');
                }
                $sSeparator = Yii::app()->request->getPost('separator');
                $aMissingAttrFieldName = $aInvalideAttrFieldName = array();
                foreach ($aTokenListArray as $buffer) {
                    $buffer = @mb_convert_encoding((string) $buffer, "UTF-8", $sUploadCharset);
                    if ($iRecordCount == 0) {
                        // Parse first line (header) from CSV
                        $buffer = removeBOM($buffer);
                        // We alow all field except tid because this one is really not needed.
                        $aAllowedFieldNames = Token::model($iSurveyId)->tableSchema->getColumnNames();
                        if (($kTid = array_search('tid', $aAllowedFieldNames)) !== false) {
                            unset($aAllowedFieldNames[$kTid]);
                        }
                        // Some header don't have same column name
                        $aReplacedFields = array(
                            'invited' => 'sent',
                            'reminded' => 'remindersent',
                        );
                        switch ($sSeparator) {
                            case 'comma':
                                $sSeparator = ',';
                                break;
                            case 'semicolon':
                                $sSeparator = ';';
                                break;
                            default:
                                $comma = substr_count((string) $buffer, ',');
                                $semicolon = substr_count((string) $buffer, ';');
                                if ($semicolon > $comma) {
                                    $sSeparator = ';';
                                } else {
                                    $sSeparator = ',';
                                }
                        }
                        $aFirstLine = str_getcsv((string) $buffer, $sSeparator, '"');
                        $aFirstLine = array_map('trim', $aFirstLine);
                        $aIgnoredColumns = array();
                        // Now check the first line for invalid fields
                        foreach ($aFirstLine as $index => $sFieldname) {
                            $aFirstLine[$index] = preg_replace("/(.*) <[^,]*>$/", "$1", $sFieldname);
                            $sFieldname = $aFirstLine[$index];
                            if (!in_array($sFieldname, $aAllowedFieldNames)) {
                                $aIgnoredColumns[] = $sFieldname;
                            }
                            if (array_key_exists($sFieldname, $aReplacedFields)) {
                                $aFirstLine[$index] = $aReplacedFields[$sFieldname];
                            }
                            // Attribute not in list
                            if (strpos($aFirstLine[$index], 'attribute_') !== false and !in_array($aFirstLine[$index], $aAttrFieldNames) and Yii::app()->request->getPost('showwarningtoken')) {
                                $aInvalideAttrFieldName[] = $aFirstLine[$index];
                            }
                        }
                        //compare attributes with source csv
                        if (Yii::app()->request->getPost('showwarningtoken')) {
                            $aMissingAttrFieldName = array_diff($aAttrFieldNames, $aFirstLine);
                            // get list of mandatory attributes
                            $allAttrFieldNames = getParticipantAttributes($iSurveyId);
                            //if it isn't mandantory field we don't need to show in warning
                            if (!empty($aAttrFieldNames)) {
                                if (!empty($aMissingAttrFieldName)) {
                                    foreach ($aMissingAttrFieldName as $index => $AttrFieldName) {
                                        if (isset($allAttrFieldNames[$AttrFieldName]) and strtolower((string) $allAttrFieldNames[$AttrFieldName]["mandatory"]) != "y") {
                                            unset($aMissingAttrFieldName[$index]);
                                        }
                                    }
                                }
                                if (isset($aInvalideAttrFieldName) and !empty($aInvalideAttrFieldName)) {
                                    foreach ($aInvalideAttrFieldName as $index => $AttrFieldName) {
                                        if (isset($allAttrFieldNames[$AttrFieldName]) and strtolower((string) $allAttrFieldNames[$AttrFieldName]["mandatory"]) != "y") {
                                            unset($aInvalideAttrFieldName[$index]);
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        $line = str_getcsv($buffer, $sSeparator, '"');

                        if (count($aFirstLine) != count($line)) {
                            $aInvalidFormatList[] = sprintf(gT("Line %s"), $iRecordCount);
                            $iRecordCount++;
                            continue;
                        }
                        $aWriteArray = array_combine($aFirstLine, $line);

                        //kick out ignored columns
                        foreach ($aIgnoredColumns as $column) {
                            unset($aWriteArray[$column]);
                        }
                        $bDuplicateFound = false;
                        $bInvalidEmail = false;
                        $bInvalidToken = false;
                        $aWriteArray['email'] = isset($aWriteArray['email']) ? trim($aWriteArray['email']) : "";
                        $aWriteArray['firstname'] = $aWriteArray['firstname'] ?? "";
                        $aWriteArray['lastname'] = $aWriteArray['lastname'] ?? "";
                        $aWriteArray['language'] = $aWriteArray['language'] ?? $sBaseLanguage;

                        // First check if we can skip because the email is blank
                        if ($bFilterBlankEmail && $aWriteArray['email'] == '') {
                            $bInvalidEmail = true;
                            $aInvalidEmailList[] = sprintf(gT("Line %s : %s %s"), $iRecordCount, CHtml::encode($aWriteArray['firstname']), CHtml::encode($aWriteArray['lastname']));
                            $iRecordCount++;
                            continue;
                        }

                        if ($bFilterDuplicateToken) {
                            $aParams = array();
                            $oCriteria = new CDbCriteria();
                            $oCriteria->condition = "";
                            foreach ($aFilterDuplicateFields as $field) {
                                if (isset($aWriteArray[$field])) {
                                    $oCriteria->addCondition("{$field} = :{$field}");
                                    $aParams[":{$field}"] = $aWriteArray[$field];
                                }
                            }
                            if (!empty($aParams)) {
                                $oCriteria->params = $aParams;
                            }
                            $dupresult = TokenDynamic::model($iSurveyId)->count($oCriteria);
                            if ($dupresult > 0) {
                                $bDuplicateFound = true;
                                $aDuplicateList[] = sprintf(gT("Line %s : %s %s (%s)"), $iRecordCount, $aWriteArray['firstname'], $aWriteArray['lastname'], $aWriteArray['email']);
                            }
                        }

                        if (!$bDuplicateFound && $aWriteArray['email'] != '') {
                            $aEmailAddresses = preg_split("/(,|;)/", $aWriteArray['email']);
                            foreach ($aEmailAddresses as $sEmailaddress) {
                                if (!validateEmailAddress($sEmailaddress)) {
                                    if ($bAllowInvalidEmail) {
                                        $iInvalidEmailCount++;
                                        if (empty($aWriteArray['emailstatus']) || strtoupper($aWriteArray['emailstatus'] == "OK")) {
                                            $aWriteArray['emailstatus'] = "invalid";
                                        }
                                    } else {
                                        $bInvalidEmail = true;
                                        $aInvalidEmailList[] = sprintf(gT("Line %s : %s %s (%s)"), $iRecordCount, CHtml::encode($aWriteArray['firstname']), CHtml::encode($aWriteArray['lastname']), CHtml::encode($aWriteArray['email']));
                                    }
                                }
                            }
                        }

                        if (!$bDuplicateFound && !$bInvalidEmail && isset($aWriteArray['token']) && trim((string) $aWriteArray['token']) != '') {
                            if (trim((string) $aWriteArray['token']) != Token::sanitizeToken($aWriteArray['token'])) {
                                $aInvalidTokenList[] = sprintf(gT("Line %s : %s %s (%s) - token : %s"), $iRecordCount, CHtml::encode($aWriteArray['firstname']), CHtml::encode($aWriteArray['lastname']), CHtml::encode($aWriteArray['email']), CHtml::encode($aWriteArray['token']));
                                $bInvalidToken = true;
                            }
                            // We always search for duplicate token (it's in model. Allow to reset or update token ?
                            if (Token::model($iSurveyId)->count("token=:token", array(":token" => $aWriteArray['token']))) {
                                $bDuplicateFound = true;
                                $aDuplicateList[] = sprintf(gT("Line %s : %s %s (%s) - token : %s"), $iRecordCount, CHtml::encode($aWriteArray['firstname']), CHtml::encode($aWriteArray['lastname']), CHtml::encode($aWriteArray['email']), CHtml::encode($aWriteArray['token']));
                            }
                        }

                        // Dispatch beforeTokenImport event
                        $aOptions = array(
                            'csvcharset' => $sUploadCharset,
                            'filterduplicatetoken' => $bFilterDuplicateToken,
                            'filterblankemail' => $bFilterBlankEmail,
                            'allowinvalidemail' => $bAllowInvalidEmail,
                            'filterduplicatefields' => $aFilterDuplicateFields,
                            'separator' => $sSeparator,
                            'showwarningtoken' => Yii::app()->request->getPost('showwarningtoken'),
                        );
                        $aEventResult = $this->dispatchBeforeTokenImport($iSurveyId, $aWriteArray, $iRecordCount, $aOptions);
                        $bPluginReportedError = $aEventResult['pluginReportedError'];
                        $bImportDone = $aEventResult['importDone'];

                        if ($bPluginReportedError) {
                            // If plugin says import is not valid, append the error
                            $sErrorMessage = $aEventResult['errorMessage'];
                            $aPluginErrorMessageList[$sErrorMessage][] = $aEventResult['tokenSpecificErrorMessage'];
                        } else {
                            // If plugin says import is OK, replace token data from the event
                            $aWriteArray = $aEventResult['token'];
                        }

                        if (!$bDuplicateFound && !$bInvalidEmail && !$bInvalidToken && !$bPluginReportedError && !$bImportDone) {
                            // unset all empty value
                            foreach ($aWriteArray as $key => $value) {
                                if ($aWriteArray[$key] == "") {
                                    unset($aWriteArray[$key]);
                                }
                                if (substr((string) $value, 0, 1) == '"' && substr((string) $value, -1) == '"') {
                                    // Fix CSV quote
                                    $aWriteArray[$key] = substr((string) $value, 1, -1);
                                }
                            }
                            // Some default value : to be moved to Token model rules in future release ?
                            // But think we have to accept invalid email etc ... then use specific scenario
                            $oToken = Token::create($iSurveyId);
                            if ($bAllowInvalidEmail) {
                                $oToken->scenario = 'allowinvalidemail';
                            }
                            foreach ($aWriteArray as $key => $value) {
                                $oToken->$key = $value;
                            }
                            if (!$oToken->encryptSave(true)) {
                                $aModelErrorList[] = array('line' => $iRecordCount, 'errors' => CHtml::errorSummary($oToken, '', '', ['class' => 'text-start']));
                            } else {
                                $bImportDone = true;
                            }
                        }
                        if ($bImportDone) {
                            $iRecordImported++;
                        }
                        $iRecordOk++;
                    }
                    $iRecordCount++;
                }
                $iRecordCount = $iRecordCount - 1;
                unlink($sFileName);
                $aData['aTokenListArray'] = $aTokenListArray; // Big array in memory, just for success ?
                $aData['iRecordImported'] = $iRecordImported;
                $aData['iRecordOk'] = $iRecordOk;
                $aData['iRecordCount'] = $iRecordCount;
                $aData['aFirstLine'] = $aFirstLine; // Seem not needed
                $aData['aDuplicateList'] = $aDuplicateList;
                $aData['aInvalidTokenList'] = $aInvalidTokenList;
                $aData['aInvalidFormatList'] = $aInvalidFormatList;
                $aData['aInvalidEmailList'] = $aInvalidEmailList;
                $aData['aPluginErrorMessageList'] = $aPluginErrorMessageList;
                $aData['aModelErrorList'] = $aModelErrorList;
                $aData['iInvalidEmailCount'] = $iInvalidEmailCount;
                $aData['thissurvey'] = getSurveyInfo($iSurveyId);
                $aData['iSurveyId'] = $aData['surveyid'] = $iSurveyId;
                $aData['aInvalideAttrFieldName'] = $aInvalideAttrFieldName;
                $aData['aMissingAttrFieldName'] = $aMissingAttrFieldName;
                $this->renderWrappedTemplate('token', array('csvimportresult'), $aData);
                Yii::app()->end();
            }
        }

        // If there are error with file : show the form
        $aData['aEncodings'] = $aEncodings;
        asort($aData['aEncodings']);
        $aData['iSurveyId'] = $iSurveyId;
        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['surveyid'] = $iSurveyId;
        $aTokenTableFields = getTokenFieldsAndNames($iSurveyId);
        unset($aTokenTableFields['sent']);
        unset($aTokenTableFields['remindersent']);
        unset($aTokenTableFields['remindercount']);
        unset($aTokenTableFields['usesleft']);
        foreach ($aTokenTableFields as $sKey => $sValue) {
            if ($sValue['description'] != $sKey) {
                $sValue['description'] .= ' - ' . $sKey;
            }
            $aNewTokenTableFields[$sKey] = $sValue['description'];
        }
        $aData['aTokenTableFields'] = $aNewTokenTableFields;

        // Get default character set from global settings
        $thischaracterset = getGlobalSetting('characterset');
        // If no encoding was set yet, use the old "auto" default
        if ($thischaracterset == "") {
            $thischaracterset = "auto";
        }
        $aData['thischaracterset'] = $thischaracterset;

        $this->renderWrappedTemplate('token', array('csvupload'), $aData);
    }

    /**
     * Generate tokens
     * @param int $iSurveyId
     * @return void
     */
    public function tokenify($iSurveyId)
    {
        $iSurveyId = (int) $iSurveyId;
        $survey = Survey::model()->findByPk($iSurveyId);
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update')) {
            Yii::app()->session['flashmessage'] = gT("You do not have permission to access this page.");
            $this->getController()->redirect(array("/surveyAdministration/view/surveyid/{$iSurveyId}"));
        }
        if (!$survey->hasTokensTable) {
            // If no tokens table exists
            $this->newtokentable($iSurveyId);
        }
        $aData = array();
        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['surveyid'] = $iSurveyId;

        $aData['sidemenu']['state'] = false;
        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyId . ")";
        $aData['sidemenu']["token_menu"] = true;
        $aData['topBar']['hide'] = true;

        if (!Yii::app()->request->getParam('ok')) {
            $aData['sidemenu']['state'] = false;
            $this->renderWrappedTemplate('token', array('message' => array(
                'title' => gT("Create access codes"),
                'message' => gT("Clicking 'Yes' will generate access codes for all those in this participant list that have not been issued one. Continue?") . "<br /><br />\n"
                    . "<button class='btn btn-outline-secondary' type='submit' value='"
                    . gT("Yes") . "' onclick='" . convertGETtoPOST($this->getController()->createUrl("admin/tokens/sa/tokenify/surveyid/$iSurveyId", array('ok' => 'Y'))) . "' >" . gT("Yes") . "</button>\n"
                    . "<input class='btn btn-outline-secondary' type='submit' value='"
                    . gT("No") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/index/surveyid/$iSurveyId") . "', '_top')\" />\n"
                    . "<br />\n"
            )), $aData);
        } else {
            //get token length from survey settings
            $newtoken = Token::model($iSurveyId)->generateTokens($iSurveyId);
            $newtokencount = $newtoken['0'];
            $neededtokencount = $newtoken['1'];
            if ($neededtokencount > $newtokencount) {
                $aData['success'] = false;
                $message = ngT('Only {n} access code has been created.|Only {n} access codes have been created.', $newtokencount)
                    . ngT('Need {n} access code.|Need {n} access codes.', $neededtokencount);
                $message .= '<p class="mt-3"><a href="' . App()->createUrl('admin/tokens/sa/index/surveyid/' . $iSurveyId) . '" title="" class="btn btn-outline-secondary ">' . gT("Ok") . '</a></p>';
            } else {
                $aData['success'] = true;
                $message = ngT('{n} access code has been created.|{n} access codes have been created.', $newtokencount);
                $message .= '<p class="mt-3"><a href="' . App()->createUrl('admin/tokens/sa/index/surveyid/' . $iSurveyId) . '" title="" class="btn btn-outline-secondary ">' . gT("Ok") . '</a></p>';
            }
            $this->renderWrappedTemplate('token', array('message' => array(
                'title' => gT("Create access codes"),
                'message' => $message
            )), $aData);
        }
    }

    /**
     * Remove Token Database
     * @param int $iSurveyId
     * @return void
     */
    public function kill(int $iSurveyId)
    {
        $iSurveyId = (int) $iSurveyId;
        $survey = Survey::model()->findByPk($iSurveyId);
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'surveysettings', 'update') && !Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'delete')) {
            Yii::app()->session['flashmessage'] = gT("You do not have permission to access this page.");
            $this->getController()->redirect(array("/surveyAdministration/view/surveyid/{$iSurveyId}"));
        }
        if (!$survey->hasTokensTable) {
            // If no tokens table exists
            $this->newtokentable($iSurveyId);
        }
        $aData = array();
        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['surveyid'] = $iSurveyId;
        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyId . ")";

        $datestamp = time();
        $date = date('YmdHis', $datestamp); //'His' adds 24hours+minutes to name to allow multiple deactiviations in a day
        $DBDate = "date('Y-m-d H:i:s', $datestamp)";
        $userID = Yii::app()->user->getId();
        /* If there is not a $_POST value of 'ok', then ask if the user is sure they want to
           delete the tokens table */
        $oldtable = "tokens_$iSurveyId";
        $newtable = "old_tokens_{$iSurveyId}_$date";
        $newtableDisplay = Yii::app()->db->tablePrefix . $newtable;

        $aData['topBar']['hide'] = true;

        if (!Yii::app()->request->getPost('ok')) {
            $aData['sidemenu']['state'] = false;
            $aData['backupTableName']   = $newtableDisplay;
            $this->renderWrappedTemplate('token', 'deleteParticipantsTable', $aData);
            return;
        }
        /* The user has confirmed they want to delete the tokens table */
        Yii::app()->db->createCommand()->renameTable("{{{$oldtable}}}", "{{{$newtable}}}");

        $archivedTokenSettings = new ArchivedTableSettings();
        $archivedTokenSettings->survey_id = $iSurveyId;
        $archivedTokenSettings->user_id = $userID;
        $archivedTokenSettings->tbl_name = $newtable;
        $archivedTokenSettings->tbl_type = 'token';
        $archivedTokenSettings->created = $DBDate;
        $archivedTokenSettings->properties = $aData['thissurvey']['tokenencryptionoptions'];
        $archivedTokenSettings->attributes = json_encode($aData['thissurvey']['attributedescriptions']);
        $archivedTokenSettings->save();

        //Remove any survey_links to the CPDB
        SurveyLink::model()->deleteLinksBySurvey($iSurveyId);

        $aData['sidemenu']['state'] = false;
        $aData['backupTableName'] = $newtableDisplay;
        $this->renderWrappedTemplate('token', 'afterDeleteParticipantsTable', $aData);
        LimeExpressionManager::SetDirtyFlag(); // so that knows that survey participants tables have changed
    }

    /**
     * Bounce Settings Action.
     * @param int $iSurveyId
     * @return void
     */
    public function bouncesettings(int $iSurveyId)
    {
        $iSurveyId = (int) $iSurveyId;
        $survey = Survey::model()->findByPk($iSurveyId);
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update')) {
            Yii::app()->session['flashmessage'] = gT("You do not have permission to access this page.");
            $this->getController()->redirect(array("/surveyAdministration/view/surveyid/{$iSurveyId}"));
        }
        if (!$survey->hasTokensTable) {
            // If no tokens table exists
            $this->newtokentable($iSurveyId);
        }
        $aData['thissurvey'] = $aData['settings'] = getSurveyInfo($iSurveyId);
        $aData['surveyid'] = $iSurveyId;

        if (Yii::app()->request->getPost('save') == 'save') {
            $fieldvalue = array(
                "bounceprocessing" => Yii::app()->request->getPost('bounceprocessing'),
                "bounce_email" => Yii::app()->request->getPost('bounce_email'),
            );

            if (Yii::app()->request->getPost('bounceprocessing') == 'L') {
                $fieldvalue['bounceaccountencryption'] = Yii::app()->request->getPost('bounceaccountencryption');
                $fieldvalue['bounceaccountuser'] = Yii::app()->request->getPost('bounceaccountuser');
                if (Yii::app()->request->getPost('bounceaccountpass') != 'somepassword') {
                    $fieldvalue['bounceaccountpass'] = LSActiveRecord::encryptSingle(Yii::app()->request->getPost('bounceaccountpass'));
                }
                $fieldvalue['bounceaccounttype'] = Yii::app()->request->getPost('bounceaccounttype');
                $fieldvalue['bounceaccounthost'] = Yii::app()->request->getPost('bounceaccounthost');
            }

            $survey = Survey::model()->findByAttributes(array('sid' => $iSurveyId));
            foreach ($fieldvalue as $k => $v) {
                $survey->$k = $v;
            }
            $survey->save();
            App()->user->setFlash('bouncesettings', gT("Bounce settings have been saved."));

            $this->getController()->redirect(array("admin/tokens/sa/bouncesettings/surveyid/{$iSurveyId}"));
        }

        $aData['sidemenu']["token_menu"] = true;

        $aData['sidemenu']['state'] = false;
        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyId . ")";

        $topbarData = TopbarConfiguration::getTokensTopbarData($iSurveyId);
        $aData['topbar']['showSaveButton'] = true;
        $aData['topbar']['showWhiteCloseButton'] = true;
        $aData['topbar']['closeUrl'] = Yii::app()->createUrl(
            "admin/tokens",
            [
                "sa" => 'index',
                "surveyid" => $iSurveyId,
            ]
        );
        $topbarData = array_merge($topbarData, $aData['topbar']);
        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/surveyAdministration/partial/topbar_tokens/rightSideButtons',
            $topbarData,
            true
        );

        $this->renderWrappedTemplate('token', array('bounce'), $aData);
    }

    public function prepExportToCPDB()
    {
        $exportedItems = Yii::app()->request->getPost('itemsid', []);
        if (is_array($exportedItems)) {
            $_FILESexportedItems = json_encode($exportedItems);
        }
        Yii::app()->session['participantid'] = $exportedItems;
        return;
    }

    /**
     * Handle token form for addnew/edit actions
     * @param int $iSurveyId
     * @param string $subaction
     * @param integer $iTokenId
     * @param null $deprecated in 6.5.5, used before to show partial view, move to Yii::app()->request->getIsAjaxRequest()
     * @return void
     */
    public function handletokenform($iSurveyId, $subaction, $iTokenId = "", $deprecated = null)
    {
        $oSurvey = Survey::model()->findByPk($iSurveyId);
        if (!$oSurvey->hasTokensTable) {
            // If no tokens table exists
            $this->newtokentable($iSurveyId);
        }
        Yii::app()->loadHelper("surveytranslator");

        if ($iTokenId) {
            $aData['tokenid'] = $iTokenId;
            $token = Token::model($iSurveyId)->findByPk($iTokenId)->decrypt();
            $aData['tokendata'] = $token;
        } else {
            $aData['tokenid'] = null;
            $aData['tokendata'] = Token::create($iSurveyId)->getAttributes();
        }

        $aData['iTokenLength'] = !empty(Token::model($iSurveyId)->survey->oOptions->tokenlength) ? Token::model($iSurveyId)->survey->oOptions->tokenlength : 15;
        /* @var boolean is it an ajax request */
        $ajax = App()->request->getIsAjaxRequest();

        $thissurvey = $oSurvey->attributes;
        $aAdditionalAttributeFields = $oSurvey->decodedAttributedescriptions;
        $aTokenFieldNames = Yii::app()->db->getSchema()->getTable("{{tokens_$iSurveyId}}", true);
        $aTokenFieldNames = array_keys($aTokenFieldNames->columns);
        $aData['attrfieldnames'] = array();
        foreach ($aAdditionalAttributeFields as $sField => $aAttrData) {
            if (in_array($sField, $aTokenFieldNames)) {
                if (array_key_exists('description', $aAttrData) && $aAttrData['description'] == '') {
                    $aAttrData['description'] = $sField;
                }
                $aData['attrfieldnames'][(string) $sField] = $aAttrData;
            }
        }
        foreach ($aTokenFieldNames as $sTokenFieldName) {
            if (strpos($sTokenFieldName, 'attribute_') === 0 && (!isset($aData['attrfieldnames']) || !isset($aData['attrfieldnames'][$sTokenFieldName]))) {
                $aData['attrfieldnames'][$sTokenFieldName] = array('description' => $sTokenFieldName, 'mandatory' => 'N');
            }
        }

        $aData['thissurvey'] = $thissurvey;
        $aData['surveyid'] = $iSurveyId;
        $aData['token_subaction'] = $subaction;
        $aData['dateformatdetails'] = getDateFormatData(Yii::app()->session['dateformat']);

        $aData['sidemenu']['state'] = false;
        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyId . ")";
        $aData['sidemenu']["token_menu"] = true;

        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/surveyAdministration/partial/topbar/surveyTopbarRight_view',
            [
                'showGreenSaveAndCloseButton' => true,
                'showSaveButton' => true,
                'showWhiteCloseButton' => true,
                'closeUrl' => Yii::app()->createUrl(
                    "admin/tokens",
                    [
                        "sa" => 'index',
                        "surveyid" => $iSurveyId,
                    ]
                )
            ],
            true
        );

        if ($ajax) {
            $aData['oSurvey'] = $oSurvey;
            $aData['ajax'] = true;
            $this->getController()->renderPartial('/admin/token/tokenform', $aData, false, false);
        } else {
            $this->renderWrappedTemplate('token', array('tokenform'), $aData);
        }
    }

    /**
     * Show dialogs and create a new tokens table
     * @param int $iSurveyId
     * @return void
     */
    private function newtokentable($iSurveyId)
    {
        $aSurveyInfo = getSurveyInfo($iSurveyId);
        $survey = Survey::model()->findByPk($iSurveyId);
        if (
            !Permission::model()->hasSurveyPermission($iSurveyId, 'surveysettings', 'update') &&
            !Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'create')
        ) {
            Yii::app()->session['flashmessage'] = gT("Survey participants have not been initialised for this survey.");
            $this->getController()->redirect(array("/surveyAdministration/view/surveyid/{$iSurveyId}"));
        }

        //The survey participants table already exist ?
        if ($survey->hasTokensTable) {
            Yii::app()->session['flashmessage'] = gT("Participant table already exist for this survey.");
            $this->getController()->redirect(array("/surveyAdministration/view/surveyid/{$iSurveyId}"));
        }

        // The user have right to create token, then don't test right after
        Yii::import('application.helpers.admin.token_helper', true);

        $aData = array();
        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['surveyid'] = $iSurveyId;
        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyId . ")";
        $aData['sidemenu']['state'] = false;
        // enable encryption for newly created token tables only
        $aTokenencryptionoptions = $survey->getTokenEncryptionOptions();
        $aTokenencryptionoptions['enabled'] = 'Y';

        $aData['topBar']['hide'] = true;

        // Update table, must be CRSF controlled
        if (Yii::app()->request->getPost('createtable') === "Y") {
            Survey::model()->updateByPk($iSurveyId, array('tokenencryptionoptions' => ls_json_encode($aTokenencryptionoptions)));
            Token::createTable($iSurveyId);
            LimeExpressionManager::SetDirtyFlag(); // LimeExpressionManager needs to know about the new survey participants table
            $this->renderWrappedTemplate('token', array('message' => array(
                'title' => gT("Survey participants"),
                'message' => gT("A participant table has been created for this survey.") . " (\"" . Yii::app()->db->tablePrefix . "tokens_$iSurveyId\")<br /><br />\n"
                    . "<input type='submit' class='btn btn-outline-secondary' value='"
                    . gT("Continue") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/index/surveyid/$iSurveyId") . "', '_top')\" />\n"
            )), $aData);
        } elseif (returnGlobal('restoretable') === "Y" && Yii::app()->request->getPost('oldtable')) {
            /* Restore a previously deleted tokens table */
            $archivedTable = Yii::app()->request->getPost('oldtable');
            $archivedTableSettings = ArchivedTableSettings::model()->findByAttributes(['tbl_name' => $archivedTable]);

            // Set the $tokenencryptionoptions from the encryption column in ArchivedTableSettings
            if (isset($archivedTableSettings)) {
                $tokenencryptionoptions = $archivedTableSettings->properties;
                $tokenencryptionoptionsOld = $aTokenencryptionoptions;
                $tokenencryptionoptionsArray = json_decode((string) $tokenencryptionoptions, true);
                foreach ($tokenencryptionoptionsArray as $tokenencryptionoption) {
                    // if the encryption status is unknown
                    if ($tokenencryptionoption === 'unknown') {
                        // for later use in refactoring
                        // $tokenencryptionoptions = json_encode($tokenencryptionoptionsOld);
                        $tokenencryptionoptionsArray = $tokenencryptionoptionsOld;
                    }
                }
                $attributedescriptions = $archivedTableSettings->attributes;
                $attributedescriptionsOld = $aSurveyInfo['attributedescriptions'];
                $attributedescriptionsArray = json_decode((string) $attributedescriptions, true) ?? [];
                foreach ($attributedescriptionsArray as $attributedescription) {
                    // if the encryption status is unknown
                    if ($attributedescription === 'unknown') {
                        $attributedescriptions =  json_encode($attributedescriptionsOld);
                        $attributedescriptionsArray = $attributedescriptionsOld;
                    }
                }

                $oDB = Yii::app()->db;
                $oTransaction = $oDB->beginTransaction();
                try {
                    $aEncryptionSettings = [];
                    foreach ($tokenencryptionoptionsArray['columns'] as $column => $encrypted) {
                        $aEncryptionSettings[$column]['encrypted'] = $encrypted;
                    }
                    foreach ($attributedescriptionsArray as $attribute => $description) {
                        $aEncryptionSettings[$attribute]['encrypted'] = $description['encrypted'];
                    }
                    Yii::app()->db->createCommand()->renameTable("{{{$archivedTable}}}", "{{tokens_" . (int)$iSurveyId . "}}");
                    $archivedTableSettings->delete();
                    $this->updateEncryption($iSurveyId, $aEncryptionSettings);
                    Survey::model()->updateByPk($iSurveyId, ['attributedescriptions' => $attributedescriptions]);
                    // Refresh schema cache just in case the table existed in the past
                    Yii::app()->db->schema->getTable("{{tokens_" . (int)$iSurveyId . "}}", true);
                    //Add any survey_links from the renamed table
                    SurveyLink::model()->rebuildLinksFromTokenTable($iSurveyId);
                    $oTransaction->commit();

                    $this->renderWrappedTemplate(
                        'token',
                        [
                            'message' => [
                                'title'   => gT("Import old participant table"),
                                'message' => gT("A survey participants table has been created for this survey and the old participants were imported.") . " (\"" . Yii::app()->db->tablePrefix . "tokens_$iSurveyId" . "\")<br /><br />\n"
                                    . "<input type='submit' class='btn btn-outline-secondary' value='"
                                    . gT("Continue") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/index/surveyid/$iSurveyId") . "', '_top')\" />\n"
                            ]
                        ],
                        $aData
                    );
                } catch (\Exception $e) {
                    $oTransaction->rollback();
                    return;
                }

                LimeExpressionManager::SetDirtyFlag(); // so that knows that survey participants tables have changed
            } else {
                $this->renderWrappedTemplate(
                    'token',
                    [
                        'message' => [
                            'class' => 'message-box-error',
                            'title'   => gT("Import failed"),
                            'message' => gT("There are no matching settings to start the restoration of the participant table.")
                        ]
                    ],
                    $aData
                );
            }
        } else { //this is the case when "allowPublicRegistration" = Y and no token table has been created
            Yii::app()->loadHelper('database');
            Survey::model()->updateByPk($iSurveyId, array('tokenencryptionoptions' => ls_json_encode($aTokenencryptionoptions)));
            $result = Yii::app()->db->createCommand(dbSelectTablesLike("{{old_tokens_" . intval($iSurveyId) . "_%}}"))->queryAll();
            $tcount = count($result);
            if ($tcount > 0) {
                foreach ($result as $rows) {
                    $oldlist[] = substr((string) reset($rows), strlen((string) Yii::app()->db->tablePrefix), (strlen((string) reset($rows)) - strlen((string) Yii::app()->db->tablePrefix)));
                }
                $aData['oldlist'] = $oldlist;
            }

            $aData['tcount'] = $tcount;
            $aData['databasetype'] = Yii::app()->db->getDriverName();
            $aData['sidemenu']["token_menu"] = true;
            $aData['topBar']['hide'] = true;

            $this->renderWrappedTemplate('token', 'tokenwarning', $aData);
        }
        Yii::app()->end();
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     * @param boolean $sRenderFile
     * @return void
     * @throws CHttpException
     */
    protected function renderWrappedTemplate($sAction = 'token', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        $aData['imageurl'] = App()->getConfig('adminimageurl');
        $aData['display']['menu_bars'] = false;
        $aData['subaction'] = gT('Survey participants');

        if (!empty($aData['surveyid'])) {
            $aData['returnUrl'] = Yii::app()->createUrl('admin/tokens/sa/index/surveyid/' . $aData['surveyid']);
        }

        parent::renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }

    /**
     * @return string SQL condition
     */
    protected function getSQLemailstatuscondition()
    {
        $request = Yii::app()->request;
        if ($request->getPost('bypassbademails') == '1') {
            return "emailstatus = 'OK'";
        } else {
            return "emailstatus <> 'OptOut' OR emailstatus IS NULL";
        }
    }

    /**
     * @return string SQL condition
     */
    protected function getSQLremindercountcondition()
    {
        $condition = "";
        $request = Yii::app()->request;
        if (
            $request->getPost('maxremindercount')
            && $request->getPost('maxremindercount') != ''
            && intval($request->getPost('maxremindercount')) != 0
        ) {
            $condition = "remindercount < " . intval($request->getPost('maxremindercount'));
        }
        return $condition;
    }

    /**
     * @param boolean $bEmail
     * @return string SQL condition
     */
    protected function getSQLreminderdelaycondition($bEmail)
    {
        $condition = "";
        $request = Yii::app()->request;
        if (!$bEmail) {
            if (
                $request->getPost('minreminderdelay')
                && $request->getPost('minreminderdelay') != ''
                && intval($request->getPost('minreminderdelay')) != 0
            ) {
                // Yii::app()->request->getPost('minreminderdelay') in days (86400 seconds per day)
                $timeadjust = Yii::app()->getConfig("timeadjust");
                $compareddate = dateShift(
                    date(
                        "Y-m-d H:i:s",
                        time() - 86400 * intval($request->getPost('minreminderdelay'))
                    ),
                    "Y-m-d H:i",
                    $timeadjust
                );
                $condition = " ( "
                    . " (remindersent = 'N' AND sent < '" . $compareddate . "') "
                    . " OR "
                    . " (remindersent < '" . $compareddate . "'))";
            }
        }
        return $condition;
    }

    /**
     * @return array
     */
    protected function getTokenIds()
    {
        $aTokenIds = Yii::app()->request->getPost('tokenids', false);
        $aTokenIds = $aTokenIds === false ? Yii::app()->request->getQuery('tokenids', false) : $aTokenIds;

        if (!empty($aTokenIds)) {
            $aTokenIds = explode('|', (string) $aTokenIds);
            $aTokenIds = array_filter($aTokenIds);
            $aTokenIds = array_map('sanitize_int', $aTokenIds);
        }
        $aTokenIds = array_unique(array_filter((array) $aTokenIds));
        return $aTokenIds;
    }

    /**
     * @return string
     */
    protected function getSubAction()
    {
        $sSubAction = Yii::app()->request->getParam('action', 'invite');
        $sSubAction = !in_array($sSubAction, array('invite', 'remind')) ? 'invite' : $sSubAction;
        return $sSubAction;
    }

    /**
     * This method echos HTML and ends.
     * @param int   $iSurveyId
     * @param array $aSurveyLangs
     * @param array $aData
     * @return void
     */
    protected function showInviteOrReminderEmailForm(int $iSurveyId, array $aSurveyLangs, array $aData)
    {
        $SQLemailstatuscondition = $this->getSQLemailstatuscondition();
        $sSubAction = $this->getSubAction();
        $bHtml = (getEmailFormat($iSurveyId) == 'html');
        $bEmail = $sSubAction == 'invite';
        $aTokenIds = $this->getTokenIds();
        $aData['warnings'] = [];
        // Fill empty email template by default text
        foreach ($aSurveyLangs as $sSurveyLanguage) {
            $aData['thissurvey'][$sSurveyLanguage] = getSurveyInfo($iSurveyId, $sSurveyLanguage);
            $bDefaultIsNeeded = empty($aData['surveylangs'][$sSurveyLanguage]["email_{$sSubAction}"]) || empty($aData['surveylangs'][$sSurveyLanguage]["email_{$sSubAction}_subj"]);
            if ($bDefaultIsNeeded) {
                $sNewlines = ($bHtml) ? 'html' : 'text'; // This broke included style for admin_detailed_notification
                $aDefaultTexts = templateDefaultTexts($sSurveyLanguage, 'unescaped', $sNewlines);
                if (empty($aData['thissurvey'][$sSurveyLanguage]["email_{$sSubAction}"])) {
                    if ($sSubAction == 'invite') {
                        $aData['thissurvey'][$sSurveyLanguage]["email_{$sSubAction}"] = $aDefaultTexts["invitation"];
                    } elseif ($sSubAction == 'remind') {
                        $aData['thissurvey'][$sSurveyLanguage]["email_{$sSubAction}"] = $aDefaultTexts["reminder"];
                    }
                }
            }
            // Check if all attachment are here : add a warning in case
            if (!empty($aData['thissurvey'][$sSurveyLanguage]['attachments'])) {
                $attachments = $aData['thissurvey'][$sSurveyLanguage]['attachments'];
                if (is_string($attachments)) {
                    $attachments = unserialize($attachments);
                }
                $type = ($sSubAction == 'invite') ? 'invitation' : 'reminder';
                if (!empty($attachments[$type]) && is_array($attachments[$type])) {
                    foreach ($attachments[$type] as $attachment) {
                        if (!LimeMailer::attachmentExist($iSurveyId, $attachment, false)) {
                            $aData['warnings'][] = sprintf(
                                gT("There a an issue with attachment with language %s. You can review it on %semail template%s."), 
                                $sSurveyLanguage,
                                "<a href='" . App()->createUrl("admin/emailtemplates", ["sa=>index", "surveyid" => $iSurveyId]) ."'>",
                                "</a>"
                            );
                            break;
                        }
                    }
                }
            }
        }
        if (empty($aData['tokenids'])) {
            $aTokens = TokenDynamic::model($iSurveyId)->findUninvitedIDs($aTokenIds, 0, $bEmail, $SQLemailstatuscondition);
            foreach ($aTokens as $aToken) {
                $aData['tokenids'][] = $aToken;
            }
        }

        if (Yii::app()->request->getParam('action') == "remind") {
            // Send Reminders Button
            $aData['topbar']['showSendReminderButton'] = true;
        } else {
            // Send Invitation Button
            $aData['topbar']['showSendInvitationButton'] = true;
        }

        $topbarData = TopbarConfiguration::getTokensTopbarData($iSurveyId);
        $aData['topbar']['showWhiteCloseButton'] = true;
        $aData['topbar']['closeUrl'] = Yii::app()->createUrl(
            "admin/tokens",
            [
                "sa" => 'index',
                "surveyid" => $iSurveyId,
            ]
        );
        $topbarData = array_merge($topbarData, $aData['topbar']);
        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/surveyAdministration/partial/topbar_tokens/rightSideButtons',
            $topbarData,
            true
        );

        $this->renderWrappedTemplate('token', array($sSubAction), $aData);
    }

    /**
     * Reminders that are send are stored in session, so that they
     * are not send twice by accident in case of an unpredicted page
     * reload.
     * @param int $iSurveyId
     * @return void
     */
    protected function clearEmailSessionCache($iSurveyId)
    {
        $cacheName = $this->getEmailCacheName($iSurveyId);
        unset($_SESSION[$cacheName]);
    }

    /**
     * @param int $iSurveyId
     * @return string Cache name, like survey_1234_email_cache
     */
    protected function getEmailCacheName($iSurveyId)
    {
        return sprintf(
            'survey_%d_email_cache',
            (int) $iSurveyId
        );
    }

    /**
     * Returns true if this $token is set in cache for $iSurveyId.
     * Being set means the email has already been send.
     * @param int $iSurveyId
     * @param string $token
     * @param boolean $bIsInvitation
     * $return  boolean
     */
    protected function tokenIsSetInEmailCache($iSurveyId, $tid, $bIsInvitation)
    {
        $sType = $bIsInvitation ? 'i' : 'r';
        $cacheName = $this->getEmailCacheName($iSurveyId);
        return isset($_SESSION[$cacheName])
            && isset($_SESSION[$cacheName][$sType][$tid])
            && $_SESSION[$cacheName][$sType][$tid] > 0;
    }

    /**
     * Dispatches the beforeTokenImport event
     * @return array
     */
    protected function dispatchBeforeTokenImport($iSurveyId, $aToken, $iRecordCount, $aOptions)
    {
        $aParams = array_merge(
            array(
                'csvcharset' => 'UTF-8',
                'filterduplicatetoken' => false,
                'filterblankemail' => true,
                'allowinvalidemail' => false,
                'filterduplicatefields' => array(),
                'separator' => ',',
                'showwarningtoken' => true,
            ),
            $aOptions
        );
        $event = new PluginEvent('beforeTokenImport');
        $event->set('importType', 'CSV');
        $event->set('surveyId', $iSurveyId);
        $event->set('params', $aParams);
        $event->set('recordCount', $iRecordCount);
        $event->set('token', $aToken);
        $event->set('importDone', false);
        $event->set('importValid', true);
        App()->getPluginManager()->dispatchEvent($event);

        $bPluginReportedError = !$event->get('importValid');
        $bImportDone = $event->get('importDone');

        $sTokenSpecificErrorMessage = '';
        $sErrorMessage = '';
        if ($bPluginReportedError) {
            $sErrorMessage = $event->get('errorMessage');
            if (empty($sErrorMessage)) {
                $sErrorMessage = gT("%s records with other errors");
            }

            $sTokenSpecificErrorMessage = $event->get('tokenSpecificErrorMessage');
            if (empty($sTokenSpecificErrorMessage)) {
                $sTokenSpecificErrorMessage = sprintf(gT("Line %s : %s %s (%s)"), $iRecordCount, $aToken['firstname'], $aToken['lastname'], $aToken['email']);
            }
        }

        $aResult = array(
            'pluginReportedError' => $bPluginReportedError,
            'importDone' => $bImportDone,
            'errorMessage' => $sErrorMessage,
            'tokenSpecificErrorMessage' => $sTokenSpecificErrorMessage,
            'token' => $event->get('token')
        );

        return $aResult;
    }
}
