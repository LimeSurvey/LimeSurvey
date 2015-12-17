<?php
namespace ls\helpers;
use ls\components\SurveySession;
use ls\models\SettingGlobal;
use \Yii;
use ls\models\Survey;
use \CHttpCookie;
use \CHtml;
class FrontEnd
{
    public static function makegraph($step, $total)
    {
        bp();


        Yii::app()->getClientScript()->registerCssFile(App()->publicUrl . '/styles-public/' . 'lime-progress.css');
        $size = intval(($step) / $total * 100);

        $graph = '<script type="text/javascript">
    $(document).ready(function() {
    $("#progressbar").progressbar({
    value: ' . $size . '
    });
    ;});';
        if (App()->getLocale()->orientation == 'rtl') {
            $graph .= '
        $(document).ready(function() {
        $("div.ui-progressbar-value").removeClass("ui-corner-left");
        $("div.ui-progressbar-value").addClass("ui-corner-right");
        });';
        }
        $graph .= '
    </script>

    <div id="progress-wrapper">
    <span class="hide">' . sprintf(gT('You have completed %s%% of this survey'), $size) . '</span>
    <div id="progress-pre">';
        if (App()->getLocale()->orientation == 'rtl') {
            $graph .= '100%';
        } else {
            $graph .= '0%';
        }

        $graph .= '</div>
    <div id="progressbar"></div>
    <div id="progress-post">';
        if (App()->getLocale()->orientation == 'rtl') {
            $graph .= '0%';
        } else {
            $graph .= '100%';
        }
        $graph .= '</div>
    </div>';

        if ($size == 0) // Progress bar looks dumb if 0

        {
            $graph .= '
        <script type="text/javascript">
        $(document).ready(function() {
        $("div.ui-progressbar-value").hide();
        });
        </script>';
        }

        eP();

        return $graph;
    }

    /**
     * Marks a token as completed and sends a confirmation email to the participiant.
     * If $quotaexit is set to true then the user exited the survey due to a quota
     * restriction and the according token is only marked as 'Q'
     *
     * @param boolean $quotaExit
     */
    public static function submitToken(SurveySession $session, $quotaExit = false)
    {
        $survey = $session->survey;
        $token = $session->response->tokenObject;


        if ($quotaExit == true) {
            $token->completed = 'Q';
            $token->usesleft--;
        } else {
            if ($token->usesleft <= 1) {
                // Finish the token
                if (!$token->survey->bool_anonymized) {
                    $token->completed = date('Y-m-d');
                } else {
                    $token->completed = 'Y';
                }
                if (isset($token->participant_id)) {
                    $surveyLink = SurveyLink::model()->findByAttributes([
                        'participant_id' => $token->participant_id,
                        'survey_id' => $survey->primaryKey,
                        'token_id' => $token->primaryKey
                    ]);
                    if (isset($surveyLink)) {
                        if ($token->survey->bool_anonymized) {
                            $surveyLink->date_completed = date('Y-m-d');
                        } else {
                            // Update the survey_links table if necessary, to protect anonymity, use the date_created field date
                            $surveyLink->date_completed = $surveyLink->date_created;
                        }
                        $surveyLink->save();
                    }
                }
            }
            $token->usesleft--;
        }
        $token->save();

        if ($quotaExit == false) {
            if (trim(strip_tags($survey->localizedConfirmationEmail)) != "" && $survey->bool_sendconfirmation) {
                $from = "{$survey->admin} <{$survey->adminemail}>";
                $subject = $survey->localizedConfirmationEmailSubject;

                $aReplacementVars = [];
                $aReplacementVars["ADMINNAME"] = $survey->admin;
                $aReplacementVars["ADMINEMAIL"] = $survey->adminEmail;
                //Fill with token info, because user can have his information with anonimity control
                $aReplacementVars["FIRSTNAME"] = $token->firstname;
                $aReplacementVars["LASTNAME"] = $token->lastname;
                $aReplacementVars["TOKEN"] = $token->token;
                // added survey url in replacement vars
                $aReplacementVars['SURVEYURL'] = App()->createAbsoluteUrl("survey/index", [
                    'lang' => $session->language,
                    'token' => $token->token,
                    'sid' => $survey->primaryKey
                ]);

                foreach ($token->customAttributeNames() as $attr_name) {
                    $aReplacementVars[strtoupper($attr_name)] = $token->$attr_name;
                }

                $redata = [];
                $subject = Replacements::templatereplace($subject, $aReplacementVars, $redata, null, $session);

                $subject = html_entity_decode($subject, ENT_QUOTES);


                $message = html_entity_decode(
                    Replacements::templatereplace($survey->getLocalizedConfirmationEmail(), $aReplacementVars, $redata, null, $session),
                    ENT_QUOTES
                );
                if (!$survey->bool_htmlemail) {
                    $message = strip_tags(breakToNewline($message));
                }

                //Only send confirmation email if there is a valid email address
                $sToAddress = validateEmailAddresses($token->email);
                if ($sToAddress) {
                    $aAttachments = unserialize($survey->getLocalizedAttachments());

                    $aRelevantAttachments = [];
                    /*
                     * Iterate through attachments and check them for relevance.
                     */
                    if (isset($aAttachments['confirmation'])) {
                        foreach ($aAttachments['confirmation'] as $aAttachment) {
                            $relevance = $aAttachment['relevance'];
                            // If the attachment is relevant it will be added to the mail.
                            if (LimeExpressionManager::ProcessRelevance($relevance) && file_exists($aAttachment['url'])) {
                                $aRelevantAttachments[] = $aAttachment['url'];
                            }
                        }
                    }
                    SendEmailMessage($message, $subject, $sToAddress, $from, SettingGlobal::get('sitename'), $survey->bool_htmlemail,
                        null, $aRelevantAttachments
                    );
                }
            }
        }
    }

    /**
     * Send a submit notification to the email address specified in the notifications tab in the survey settings
     */
    public static function sendSubmitNotifications($surveyid)
    {
        // @todo: Remove globals
        global $thissurvey, $maildebug, $tokensexist;

        if (trim($thissurvey['adminemail']) == '') {
            return;
        }

        $homeurl = Yii::app()->createAbsoluteUrl('/admin');

        $sitename = Yii::app()->getConfig("sitename");

        $debug = Yii::app()->getConfig('debug');
        $bIsHTML = ($thissurvey['htmlemail'] == 'Y');

        $aReplacementVars = [];

        if ($thissurvey['allowsave'] == "Y" && isset($_SESSION['survey_' . $surveyid]['scid'])) {
            $aReplacementVars['RELOADURL'] = "" . Yii::app()->getController()->createUrl("/survey/index/sid/{$surveyid}/loadall/reload/scid/" . $_SESSION['survey_' . $surveyid]['scid'] . "/loadname/" . urlencode($_SESSION['survey_' . $surveyid]['holdname']) . "/loadpass/" . urlencode($_SESSION['survey_' . $surveyid]['holdpass']) . "/lang/" . urlencode(App()->language));
            if ($bIsHTML) {
                $aReplacementVars['RELOADURL'] = "<a href='{$aReplacementVars['RELOADURL']}'>{$aReplacementVars['RELOADURL']}</a>";
            }
        } else {
            $aReplacementVars['RELOADURL'] = '';
        }

        if (!isset($_SESSION['survey_' . $surveyid]['srid'])) {
            $srid = null;
        } else {
            $srid = $_SESSION['survey_' . $surveyid]['srid'];
        }
        $aReplacementVars['ADMINNAME'] = $thissurvey['adminname'];
        $aReplacementVars['ADMINEMAIL'] = $thissurvey['adminemail'];
        $aReplacementVars['VIEWRESPONSEURL'] = Yii::app()->createAbsoluteUrl("/admin/responses/sa/view/surveyid/{$surveyid}/id/{$srid}");
        $aReplacementVars['EDITRESPONSEURL'] = Yii::app()->createAbsoluteUrl("/admin/dataentry/sa/editdata/subaction/edit/surveyid/{$surveyid}/id/{$srid}");
        $aReplacementVars['STATISTICSURL'] = Yii::app()->createAbsoluteUrl("admin/statistics", ['sa' => 'index', 'surveyid' => $surveyid]);
        if ($bIsHTML) {
            $aReplacementVars['VIEWRESPONSEURL'] = "<a href='{$aReplacementVars['VIEWRESPONSEURL']}'>{$aReplacementVars['VIEWRESPONSEURL']}</a>";
            $aReplacementVars['EDITRESPONSEURL'] = "<a href='{$aReplacementVars['EDITRESPONSEURL']}'>{$aReplacementVars['EDITRESPONSEURL']}</a>";
            $aReplacementVars['STATISTICSURL'] = "<a href='{$aReplacementVars['STATISTICSURL']}'>{$aReplacementVars['STATISTICSURL']}</a>";
        }
        $aReplacementVars['ANSWERTABLE'] = '';
        $aEmailResponseTo = [];
        $aEmailNotificationTo = [];
        $sResponseData = "";

        if (!empty($thissurvey['emailnotificationto'])) {
            $aRecipient = explode(";",
                Replacements::ReplaceFields($thissurvey['emailnotificationto'], array('ADMINEMAIL' => $thissurvey['adminemail']),
                    true));
            foreach ($aRecipient as $sRecipient) {
                $sRecipient = trim($sRecipient);
                if (validateEmailAddress($sRecipient)) {
                    $aEmailNotificationTo[] = $sRecipient;
                }
            }
        }

        if (!empty($thissurvey['emailresponseto'])) {
            // there was no token used so lets remove the token field from insertarray
            if (!isset($_SESSION['survey_' . $surveyid]['token']) && $_SESSION['survey_' . $surveyid]['insertarray'][0] == 'token') {
                unset($_SESSION['survey_' . $surveyid]['insertarray'][0]);
            }
            //Make an array of email addresses to send to
            $aRecipient = explode(";",
                Replacements::ReplaceFields($thissurvey['emailresponseto'], array('ADMINEMAIL' => $thissurvey['adminemail']), true));
            foreach ($aRecipient as $sRecipient) {
                $sRecipient = trim($sRecipient);
                if (validateEmailAddress($sRecipient)) {
                    $aEmailResponseTo[] = $sRecipient;
                }
            }

            $aFullResponseTable = getFullResponseTable($surveyid, $_SESSION['survey_' . $surveyid]['srid'],
                $session->language);
            $ResultTableHTML = "<table class='printouttable' >\n";
            $ResultTableText = "\n\n";
            $oldgid = 0;
            $oldqid = 0;
            foreach ($aFullResponseTable as $sFieldname => $fname) {
                if (substr($sFieldname, 0, 4) == 'gid_') {
                    $ResultTableHTML .= "\t<tr class='printanswersgroup'><td colspan='2'>" . strip_tags($fname[0]) . "</td></tr>\n";
                    $ResultTableText .= "\n{$fname[0]}\n\n";
                } elseif (substr($sFieldname, 0, 4) == 'qid_') {
                    $ResultTableHTML .= "\t<tr class='printanswersquestionhead'><td  colspan='2'>" . strip_tags($fname[0]) . "</td></tr>\n";
                    $ResultTableText .= "\n{$fname[0]}\n";
                } else {
                    $ResultTableHTML .= "\t<tr class='printanswersquestion'><td>" . strip_tags("{$fname[0]} {$fname[1]}") . "</td><td class='printanswersanswertext'>" . CHtml::encode($fname[2]) . "</td></tr>\n";
                    $ResultTableText .= "     {$fname[0]} {$fname[1]}: {$fname[2]}\n";
                }
            }

            $ResultTableHTML .= "</table>\n";
            $ResultTableText .= "\n\n";
            if ($bIsHTML) {
                $aReplacementVars['ANSWERTABLE'] = $ResultTableHTML;
            } else {
                $aReplacementVars['ANSWERTABLE'] = $ResultTableText;
            }
        }

        $sFrom = $thissurvey['adminname'] . ' <' . $thissurvey['adminemail'] . '>';

        $aAttachments = unserialize($thissurvey['attachments']);

        $aRelevantAttachments = [];
        /*
         * Iterate through attachments and check them for relevance.
         */
        if (isset($aAttachments['admin_notification'])) {
            foreach ($aAttachments['admin_notification'] as $aAttachment) {
                $relevance = $aAttachment['relevance'];
                // If the attachment is relevant it will be added to the mail.
                if (LimeExpressionManager::ProcessRelevance($relevance) && file_exists($aAttachment['url'])) {
                    $aRelevantAttachments[] = $aAttachment['url'];
                }
            }
        }

        $redata = compact(array_keys(get_defined_vars()));
        if (count($aEmailNotificationTo) > 0) {
            $sMessage = Replacements::templatereplace($thissurvey['email_admin_notification'], $aReplacementVars, $redata, null);
            $sSubject = Replacements::templatereplace($thissurvey['email_admin_notification_subj'], $aReplacementVars, $redata, null);
            foreach ($aEmailNotificationTo as $sRecipient) {
                if (!SendEmailMessage($sMessage, $sSubject, $sRecipient, $sFrom, $sitename, true,
                    getBounceEmail($surveyid), $aRelevantAttachments)
                ) {
                    if ($debug > 0) {
                        echo '<br />Email could not be sent. Reason: ' . $maildebug . '<br/>';
                    }
                }
            }
        }

        $aRelevantAttachments = [];
        /*
         * Iterate through attachments and check them for relevance.
         */
        if (isset($aAttachments['detailed_admin_notification'])) {
            foreach ($aAttachments['detailed_admin_notification'] as $aAttachment) {
                $relevance = $aAttachment['relevance'];
                // If the attachment is relevant it will be added to the mail.
                if (LimeExpressionManager::ProcessRelevance($relevance) && file_exists($aAttachment['url'])) {
                    $aRelevantAttachments[] = $aAttachment['url'];
                }
            }
        }
        if (count($aEmailResponseTo) > 0) {
            $sMessage = Replacements::templatereplace($thissurvey['email_admin_responses'], $aReplacementVars, $redata, null);
            $sSubject = Replacements::templatereplace($thissurvey['email_admin_responses_subj'], $aReplacementVars, $redata, null);
            foreach ($aEmailResponseTo as $sRecipient) {
                if (!SendEmailMessage($sMessage, $sSubject, $sRecipient, $sFrom, $sitename, $bIsHTML,
                    getBounceEmail($surveyid), $aRelevantAttachments)
                ) {
                    if ($debug > 0) {
                        echo '<br />Email could not be sent. Reason: ' . $maildebug . '<br/>';
                    }
                }
            }
        }


    }


    /**
     * This function creates the form elements in the survey navigation bar
     * Adding a hidden input for default behaviour without javascript
     * Use button name="move" for real browser (with or without javascript) and IE6/7/8 with javascript
     */
    public static function surveyNavigator(\ls\components\SurveySession $session)
    {
        $result = [];
        $cs = App()->getClientScript();
        $cs->registerScriptFile(App()->getPublicUrl() . '/scripts/navigator.js');
        // Count down
        if ($session->survey->navigationdelay > 0 && $session->maxStep == $session->step) {
            $cs->registerScript('navigator_countdown', "navigator_countdown({$session->survey->navigationdelay});\n");
        }

        // Previous ?
        if ($session->format != Survey::FORMAT_ALL_IN_ONE
            && $session->survey->bool_allowprev
            && $session->step > 0
        ) {
            $result[] = CHtml::htmlButton(gT("Previous"), [
                'type' => 'submit',
                'id' => "moveprevbtn",
                'value' => 'prev',
                'name' => 'move',
                'accesskey' => 'p',
            ]);
        }

        // Submit
        if ($session->getStepCount() == $session->step
            || $session->format == Survey::FORMAT_ALL_IN_ONE
        ) {
            $result[] = CHtml::htmlButton(gT("Submit"), [
                'type' => 'submit',
                'id' => "movesubmitbtn",
                'value' => "submit",
                'name' => "move",
                'accesskey' => "l",
            ]);
        } else {
            $result[] = CHtml::htmlButton(gT("Next"), [
                'type' => 'submit',
                'id' => "movenextbtn",
                'value' => "next",
                'name' => "move",
                'accesskey' => "l",
            ]);
        }

        return implode(' ', $result);
    }

    /**
     * Caculate assessement scores
     *
     * @param mixed $surveyid
     * @param mixed $returndataonly - only returns an array with data
     */
    public static function doAssessment(Survey $survey, $returndataonly = false)
    {
        $session = App()->surveySessionManager->current;


        if (!isset($session) || !$session->survey->bool_assessments) {
            return false;
        }
        $total = 0;
        $query = "SELECT * FROM {{assessments}}
    WHERE sid={$session->surveyId} and language='{$session->language}'
    ORDER BY scope, id";

        if ($result = dbExecuteAssoc($query))   //Checked
        {
            $aResultSet = $result->readAll();
            if (count($aResultSet) > 0) {
                foreach ($aResultSet as $row) {
                    if ($row['scope'] == "G") {
                        $assessment['group'][$row['gid']][] = array(
                            "name" => $row['name'],
                            "min" => $row['minimum'],
                            "max" => $row['maximum'],
                            "message" => $row['message']
                        );
                    } else {
                        $assessment['total'][] = array(
                            "name" => $row['name'],
                            "min" => $row['minimum'],
                            "max" => $row['maximum'],
                            "message" => $row['message']
                        );
                    }
                }
                $fieldmap = createFieldMap($surveyid, "full", false, false, $session->language);
                $i = 0;
                $total = 0;
                $groups = [];
                foreach ($fieldmap as $field) {
                    if (in_array($field['type'], array('1', 'F', 'H', 'W', 'Z', 'L', '!', 'M', 'O', 'P'))) {
                        $fieldmap[$field['fieldname']]['assessment_value'] = 0;
                        if (isset($_SESSION['survey_' . $surveyid][$field['fieldname']])) {
                            if (($field['type'] == "M") || ($field['type'] == "P")) //Multiflexi choice  - result is the assessment attribute value
                            {
                                if ($_SESSION['survey_' . $surveyid][$field['fieldname']] == "Y") {
                                    $aAttributes = \ls\models\QuestionAttribute::model()->getQuestionAttributes($field['qid'],
                                        $field['type']);
                                    $fieldmap[$field['fieldname']]['assessment_value'] = (int)$aAttributes['assessment_value'];
                                    $total = $total + (int)$aAttributes['assessment_value'];
                                }
                            } else  // Single choice question
                            {
                                $usquery = "SELECT assessment_value FROM {{answers}} where qid=" . $field['qid'] . " and language='$baselang' and code=" . App()->db->quoteValue($_SESSION['survey_' . $surveyid][$field['fieldname']]);
                                $usresult = dbExecuteAssoc($usquery);          //Checked
                                if ($usresult) {
                                    $usrow = $usresult->read();
                                    $fieldmap[$field['fieldname']]['assessment_value'] = $usrow['assessment_value'];
                                    $total = $total + $usrow['assessment_value'];
                                }
                            }
                        }
                        $groups[] = $field['gid'];
                    }
                    $i++;
                }

                $groups = array_unique($groups);

                foreach ($groups as $group) {
                    $grouptotal = 0;
                    foreach ($fieldmap as $field) {
                        if ($field['gid'] == $group && isset($field['assessment_value'])) {
                            //$grouptotal=$grouptotal+$field['answer'];
                            if (isset ($_SESSION['survey_' . $surveyid][$field['fieldname']])) {
                                $grouptotal = $grouptotal + $field['assessment_value'];
                            }
                        }
                    }
                    $subtotal[$group] = $grouptotal;
                }
            }
            $assessments = "";
            if (isset($subtotal) && is_array($subtotal)) {
                foreach ($subtotal as $key => $val) {
                    if (isset($assessment['group'][$key])) {
                        foreach ($assessment['group'][$key] as $assessed) {
                            if ($val >= $assessed['min'] && $val <= $assessed['max'] && $returndataonly === false) {
                                $assessments .= "\t<!-- GROUP ASSESSMENT: Score: $val Min: " . $assessed['min'] . " Max: " . $assessed['max'] . "-->
                            <table class='assessments'>
                            <tr>
                            <th>" . str_replace(array("{PERC}", "{TOTAL}"), array($val, $total), $assessed['name']) . "
                            </th>
                            </tr>
                            <tr>
                            <td>" . str_replace(array("{PERC}", "{TOTAL}"), array($val, $total), $assessed['message']) . "
                            </td>
                            </tr>
                            </table><br />\n";
                            }
                        }
                    }
                }
            }

            if (isset($assessment['total'])) {
                foreach ($assessment['total'] as $assessed) {
                    if ($total >= $assessed['min'] && $total <= $assessed['max'] && $returndataonly === false) {
                        $assessments .= "\t\t\t<!-- TOTAL ASSESSMENT: Score: $total Min: " . $assessed['min'] . " Max: " . $assessed['max'] . "-->
                    <table class='assessments' align='center'>
                    <tr>
                    <th>" . str_replace(array("{PERC}", "{TOTAL}"), array($val, $total),
                                stripslashes($assessed['name'])) . "
                    </th>
                    </tr>
                    <tr>
                    <td>" . str_replace(array("{PERC}", "{TOTAL}"), array($val, $total),
                                stripslashes($assessed['message'])) . "
                    </td>
                    </tr>
                    </table>\n";
                    }
                }
            }
            if ($returndataonly == true) {
                return array('total' => $total);
            } else {
                return $assessments;
            }
        }
    }


    /**
     * checkCompletedQuota() returns matched quotas information for the current response
     * @param integer $surveyid - ls\models\Survey identification number
     * @param bool $return - set to true to return information, false do the quota
     * @return array - nested array, Quotas->Members->Fields, includes quota information matched in session.
     */
    public static function checkCompletedQuota($return = false)
    {
        bP();
        static $aMatchedQuotas; // EM call 2 times quotas with 3 lines of php code, then use static.
        $session = App()->surveySessionManager->current;
        if (!$aMatchedQuotas) {
            $aMatchedQuotas = [];
            $quota_info = $aQuotasInfo = getQuotaInformation($session->surveyId, $session->language);
            // $aQuotasInfo have an 'active' key, we don't use it ?
            if (!$aQuotasInfo || empty($aQuotasInfo)) {
                return $aMatchedQuotas;
            }
            // OK, we have some quota, then find if this $_SESSION have some set
            $aPostedFields = explode("|",
                Yii::app()->request->getPost('fieldnames', '')); // Needed for quota allowing update
            foreach ($aQuotasInfo as $aQuotaInfo) {
                $iMatchedAnswers = 0;
                $bPostedField = false;
                // Array of field with quota array value
                $aQuotaFields = [];
                // Array of fieldnames with relevance value : EM fill $_SESSION with default value even is unrelevant (em_manager_helper line 6548)
                $aQuotaRelevantFieldnames = [];
                foreach ($aQuotaInfo['members'] as $aQuotaMember) {
                    $aQuotaFields[$aQuotaMember['fieldname']][] = $aQuotaMember['value'];
                    $aQuotaRelevantFieldnames[$aQuotaMember['fieldname']] = isset($_SESSION['survey_' . $session->surveyId]['relevanceStatus'][$aQuotaMember['qid']]) && $_SESSION['survey_' . $session->surveyId]['relevanceStatus'][$aQuotaMember['qid']];
                }
                // For each field : test if actual responses is in quota (and is relevant)
                foreach ($aQuotaFields as $sFieldName => $aValues) {
                    $bInQuota = isset($_SESSION['survey_' . $session->surveyId][$sFieldName]) && in_array($_SESSION['survey_' . $session->surveyId][$sFieldName],
                            $aValues);
                    if ($bInQuota && $aQuotaRelevantFieldnames[$sFieldName]) {
                        $iMatchedAnswers++;
                    }
                    if (in_array($sFieldName, $aPostedFields))// Need only one posted value
                    {
                        $bPostedField = true;
                    }
                }
                // Count only needed quotas
                if ($iMatchedAnswers == count($aQuotaFields) && ($aQuotaInfo['action'] != 2 || $bPostedField)) {
                    if ($aQuotaInfo['qlimit'] == 0) { // Always add the quota if qlimit==0
                        $aMatchedQuotas[] = $aQuotaInfo;
                    } else {
                        $iCompleted = getQuotaCompletedCount($session->surveyId, $aQuotaInfo['id']);
                        if (!is_null($iCompleted) && ((int)$iCompleted >= (int)$aQuotaInfo['qlimit'])) // This remove invalid quota and not completed
                        {
                            $aMatchedQuotas[] = $aQuotaInfo;
                        }
                    }
                }
            }
        }
        if ($return) {
            return $aMatchedQuotas;
        }
        if (empty($aMatchedQuotas)) {
            return;
        }

        // Now we have all the information we need about the quotas and their status.
        // We need to construct the page and do all needed action
        $aSurveyInfo = getSurveyInfo($session->surveyId, $session->language);
        $sTemplatePath = Template::getTemplatePath($aSurveyInfo['template']);
        $sClientToken = isset($_SESSION['survey_' . $session->surveyId]['token']) ? $_SESSION['survey_' . $session->surveyId]['token'] : "";
        // $redata for Replacements::templatereplace
        $aDataReplacement = array(
            'thissurvey' => $aSurveyInfo,
            'clienttoken' => $sClientToken,
            'token' => $sClientToken,
        );

        // We take only the first matched quota, no need for each
        $aMatchedQuota = $aMatchedQuotas[0];
        // If a token is used then mark the token as completed, do it before event : this allow plugin to update token information
        $event = new PluginEvent('afterSurveyQuota');
        $event->set('surveyId', $session->surveyId);
        $event->set('responseId', $_SESSION['survey_' . $session->surveyId]['srid']);// We allways have a responseId
        $event->set('aMatchedQuotas', $aMatchedQuotas);// Give all the matched quota : the first is the active
        App()->getPluginManager()->dispatchEvent($event);
        $blocks = [];
        foreach ($event->getAllContent() as $blockData) {
            /* @var $blockData PluginEventContent */
            $blocks[] = CHtml::tag('div', array('id' => $blockData->getCssId(), 'class' => $blockData->getCssClass()),
                $blockData->getContent());
        }
        // Allow plugin to update message, url, url description and action
        $sMessage = $event->get('message', $aMatchedQuota['quotals_message']);
        $sUrl = $event->get('url', $aMatchedQuota['quotals_url']);
        $sUrlDescription = $event->get('urldescrip', $aMatchedQuota['quotals_urldescrip']);
        $sAction = $event->get('action', $aMatchedQuota['action']);
        $sAutoloadUrl = $event->get('autoloadurl', $aMatchedQuota['autoload_url']);

        // Doing the action and show the page
        if ($sAction == "1" && $sClientToken) {
            submittokens(true);
        }
        // Construct the default message
        $sMessage = Replacements::templatereplace($sMessage, [], $aDataReplacement, null);
        $sUrl = Replacements::passthruReplace($sUrl, $aSurveyInfo);
        $sUrl = Replacements::templatereplace($sUrl, [], $aDataReplacement, null);
        $sUrlDescription = Replacements::templatereplace($sUrlDescription, [], $aDataReplacement, null);

        // Construction of default message inside quotamessage class
        $sHtmlQuotaMessage = "<div class='quotamessage limesurveycore'>\n";
        $sHtmlQuotaMessage .= "\t" . $sMessage . "\n";
        $sHtmlQuotaUrl = ($sUrl) ? "<a href='" . $sUrl . "'>" . $sUrlDescription . "</a>" : "";

        // Add the navigator with Previous button if quota allow modification.
        if ($sAction == "2") {
            $sQuotaStep = App()->surveySessionManager->current->getStep(); // Surely not needed
            $sNavigator = CHtml::htmlButton(gT("Previous"), array(
                'type' => 'submit',
                'id' => "moveprevbtn",
                'value' => $sQuotaStep,
                'name' => 'move',
                'accesskey' => 'p',
                'class' => "submit button"
            ));
            //$sNavigator .= " ".CHtml::htmlButton(gT("Submit"),array('type'=>'submit','id'=>"movesubmit",'value'=>"movesubmit",'name'=>"movesubmit",'accesskey'=>'l','class'=>"submit button"));
            $sHtmlQuotaMessage .= CHtml::form(array("/survey/index", "sid" => $session->surveyId), 'post',
                array('id' => 'limesurvey', 'name' => 'limesurvey'));
            $sHtmlQuotaMessage .= renderOldTemplate($sTemplatePath . "/navigator.pstpl",
                array('NAVIGATOR' => $sNavigator, 'SAVE' => ''), $aDataReplacement);
            $sHtmlQuotaMessage .= CHtml::hiddenField('sid', $session->surveyId);
            $sHtmlQuotaMessage .= CHtml::hiddenField('token', $sClientToken);// Did we really need it ?
            $sHtmlQuotaMessage .= CHtml::endForm();
        }
        $sHtmlQuotaMessage .= "</div>\n";
        // Add the plugin message before default message
        $sHtmlQuotaMessage = implode("\n", $blocks) . "\n" . $sHtmlQuotaMessage;

        // Send page to user and end.
        sendCacheHeaders();
        if ($sAutoloadUrl == 1 && $sUrl != "") {
            header("Location: " . $sUrl);
        }
        doHeader();
        renderOldTemplate($sTemplatePath . "/startpage.pstpl", [], $aDataReplacement);
        renderOldTemplate($sTemplatePath . "/completed.pstpl",
            array("COMPLETED" => $sHtmlQuotaMessage, "URL" => $sHtmlQuotaUrl), $aDataReplacement);
        renderOldTemplate($sTemplatePath . "/endpage.pstpl", [], $aDataReplacement);
        doFooter();

        eP();
        Yii::app()->end();
    }

    /**
     * Resets all question timers by expiring the related cookie - this needs to be called before any output is done
     * @todo Make cookie survey ID aware
     */
    public static function resetTimers()
    {
        $cookie = new CHttpCookie('limesurvey_timers', '');
        $cookie->expire = time() - 3600;
        Yii::app()->request->cookies['limesurvey_timers'] = $cookie;
    }


}
