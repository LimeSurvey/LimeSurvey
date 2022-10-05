<?php

namespace LimeSurvey\Models\Services;

/**
 * Refactor of old sendSubmitNotifications function, to enable injection of dependencies.
 */
class SendSubmitNotificationsCommand
{
    /** @var array Mega array, see SurveyRuntimeHelper */
    private $thissurvey;

    /** @var LimeMailer instance */
    private $limeMailer;

    /**
     * Inject dependencies so they can be mocked.
     */
    public function __construct(array $thissurvey, LimeMailer $limeMailer)
    {
        $this->thissurvey = $thissurvey;
        $this->limeMailer = $limeMailer;
    }

    /**
     * Command object pattern, have one function called run() or similar.
     */
    public function run()
    {
        // @todo: Remove globals
        global $thissurvey;
        $bIsHTML = ($thissurvey['htmlemail'] === 'Y'); // Needed for ANSWERTABLE
        $debug = App()->getConfig('debug');

        //LimeMailer instance
        $mailer = \LimeMailer::getInstance(\LimeMailer::ResetComplete);
        $mailer->setSurvey($surveyid);
        $mailer->aUrlsPlaceholders = ['VIEWRESPONSE','EDITRESPONSE','STATISTICS'];

        //emails to be sent and return values
        $aEmailNotificationTo = $emails['admin_notification'] ?? [];
        $aEmailResponseTo = $emails['admin_responses'] ?? [];
        $failedEmailCount = 0;
        $successfullEmailCount = 0;

        //replacementVars for LEM
        $aReplacementVars = array();
        $aReplacementVars['STATISTICSURL'] = App()->getController()->createAbsoluteUrl("/admin/statistics/sa/index/surveyid/{$surveyid}");
        $aReplacementVars['ANSWERTABLE'] = '';

        if (!isset($_SESSION['survey_' . $surveyid]['srid'])) {
            $responseId = null; /* Maybe just return ? */
        } else {
            //replacementVars for LEM requiring a response id
            $responseId = $_SESSION['survey_' . $surveyid]['srid'];
            $aReplacementVars['EDITRESPONSEURL'] = App()->getController()->createAbsoluteUrl("/admin/dataentry/sa/editdata/subaction/edit/surveyid/{$surveyid}/id/{$responseId}");
            $aReplacementVars['VIEWRESPONSEURL'] = App()->getController()->createAbsoluteUrl("responses/view/", ['surveyId' => $surveyid, 'id' => $responseId]);
        }

        // set email language
        $emailLanguage = null;
        if (isset($_SESSION['survey_' . $surveyid]['s_lang'])) {
            $emailLanguage = $_SESSION['survey_' . $surveyid]['s_lang'];
        }

        // create array of recipients for emailnotifications
        if (!empty($thissurvey['emailnotificationto']) && empty($emails)) {
            $aRecipient = explode(";", LimeExpressionManager::ProcessStepString($thissurvey['emailnotificationto'], array('ADMINEMAIL' => $thissurvey['adminemail']), 3, true));
            foreach ($aRecipient as $sRecipient) {
                $sRecipient = trim($sRecipient);
                if ($mailer::validateAddress($sRecipient)) {
                    $aEmailNotificationTo[] = $sRecipient;
                }
            }
        }
        // // create array of recipients for emailresponses
        if (!empty($thissurvey['emailresponseto']) && empty($emails)) {
            $aRecipient = explode(";", LimeExpressionManager::ProcessStepString($thissurvey['emailresponseto'], array('ADMINEMAIL' => $thissurvey['adminemail']), 3, true));
            foreach ($aRecipient as $sRecipient) {
                $sRecipient = trim($sRecipient);
                if ($mailer::validateAddress($sRecipient)) {
                    $aEmailResponseTo[] = $sRecipient;
                }
            }
        }

        if (count($aEmailNotificationTo) || count($aEmailResponseTo)) {
            /* Force a replacement to fill coreReplacement like {SURVEYRESOURCESURL} for example */
            $reData = array('thissurvey' => $thissurvey);
            templatereplace(
                "{SID}",
                array(), /* No tempvars update (except old Replacement like */
                $reData /* Be sure to use current survey */
            );
        }

        // update replacement fields before we handle email sending
        LimeExpressionManager::updateReplacementFields($aReplacementVars);

        // admin_notification (Basic admin notification)
        if (count($aEmailNotificationTo) > 0) {
            $mailer = LimeMailer::getInstance();
            $mailer->setTypeWithRaw('admin_notification', $emailLanguage);
            foreach ($aEmailNotificationTo as $sRecipient) {
                /** set mailer params for @see FailedEmailController::actionResend() */
                if (!empty($emails)) {
                    $failedNotificationId = $sRecipient['id'];
                    $responseId = $sRecipient['responseId'];
                    $notificationRecipient = $sRecipient['recipient'];
                    $emailLanguage = $sRecipient['language'];
                    $mailer->setTypeWithRaw('admin_notification', $emailLanguage);
                    $mailer->setTo($notificationRecipient);
                    $mailerSuccess = $mailer->resend(json_decode($sRecipient['resendVars'],true));
                } else {
                    $failedNotificationId = null;
                    $notificationRecipient = $sRecipient;
                    $mailer->setTo($notificationRecipient);
                    $mailerSuccess = $mailer->SendMessage();
                }
                if (!$mailerSuccess) {
                    $failedEmailCount++;
                    saveFailedEmail($failedNotificationId, $notificationRecipient, $surveyid, $responseId, 'admin_notification', $emailLanguage, $mailer);
                    if (empty($emails) && $debug > 0 && Permission::model()->hasSurveyPermission($surveyid, 'surveysettings', 'update')) {
                        /* Find a better way to show email error … */
                        echo CHtml::tag("div",
                            ['class' => 'alert alert-danger'],
                            sprintf(gT("Basic admin notification could not be sent because of error: %s"), $mailer->getError()));
                    }
                } else{
                    $successfullEmailCount++;
                    //preserve failedEmail if it exists
                    failedEmailSuccess($failedNotificationId);
                }
            }
        }

        // admin_notification (Detailed admin notification)
        if (count($aEmailResponseTo) > 0) {
            // there was no token used so lets remove the token field from insertarray
            if (isset($_SESSION['survey_' . $surveyid]['insertarray'][0])) {
                if (!isset($_SESSION['survey_' . $surveyid]['token']) && $_SESSION['survey_' . $surveyid]['insertarray'][0] === 'token') {
                    unset($_SESSION['survey_' . $surveyid]['insertarray'][0]);
                }
            }
            $mailer = \LimeMailer::getInstance();
            $mailer->setTypeWithRaw('admin_responses', $emailLanguage);
            foreach ($aEmailResponseTo as $sRecipient) {
                /** set mailer params for @see FailedEmailController::actionResend() */
                if (!empty($emails)) {
                    $failedNotificationId = $sRecipient['id'];
                    $responseId = $sRecipient['responseId'];
                    $responseRecipient = $sRecipient['recipient'];
                    $emailLanguage = $sRecipient['language'];
                    $aReplacementVars['ANSWERTABLE'] = getResponseTableReplacement($surveyid, $responseId, $emailLanguage, $bIsHTML);
                    LimeExpressionManager::updateReplacementFields($aReplacementVars);
                    $mailer->setTypeWithRaw('admin_responses', $emailLanguage);
                    $mailer->setTo($responseRecipient);
                    $mailerSuccess = $mailer->resend(json_decode($sRecipient['resendVars'],true));
                } else {
                    $failedNotificationId = null;
                    $responseRecipient = $sRecipient;
                    $aReplacementVars['ANSWERTABLE'] = getResponseTableReplacement($surveyid, $responseId, $emailLanguage, $bIsHTML);
                    LimeExpressionManager::updateReplacementFields($aReplacementVars);
                    $mailer->setTo($responseRecipient);
                    $mailerSuccess = $mailer->SendMessage();
                }
                if (!$mailerSuccess) {
                    $failedEmailCount++;
                    saveFailedEmail($failedNotificationId, $responseRecipient, $surveyid, $responseId, 'admin_responses', $emailLanguage, $mailer);
                    if (empty($emails) && $debug > 0 && Permission::model()->hasSurveyPermission($surveyid, 'surveysettings', 'update')) {
                        /* Find a better way to show email error … */
                        echo CHtml::tag("div",
                            ['class' => 'alert alert-danger'],
                            sprintf(gT("Detailed admin notification could not be sent because of error: %s"), $mailer->getError()));
                    }
                } else {
                    $successfullEmailCount++;
                    failedEmailSuccess($failedNotificationId);
                }
            }
        }
        if ($return) {
            return [
                'successfullEmailCount' => $successfullEmailCount,
                'failedEmailCount'      => $failedEmailCount,
            ];
        }
    }
}
