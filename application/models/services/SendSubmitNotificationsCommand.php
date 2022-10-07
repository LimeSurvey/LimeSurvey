<?php

namespace LimeSurvey\Models\Services;

use LimeMailer;
use LimeExpressionManager;
use Permission;
use CHtml;
use CException;
use FailedEmail;

/**
 * Refactor of old sendSubmitNotifications function, to enable injection of dependencies.
 * Command object pattern, have one function called run() or similar.
 */
class SendSubmitNotificationsCommand
{
    /** @var array Mega array, see SurveyRuntimeHelper */
    private $thissurvey;

    /** @var LimeMailer instance */
    private $limeMailer;

    /** @var bool */
    private $debug = false;

    /** @var bool Needed for ANSWERTABLE */
    private $isHtml = null;

    /**
     * Inject dependencies so they can be mocked.
     */
    public function __construct(array $thissurvey, LimeMailer $limeMailer)
    {
        $this->thissurvey = $thissurvey;
        $this->limeMailer = $limeMailer;
        $this->isHtml = $this->thissurvey['htmlemail'] === 'Y';
    }

    /**
     * Set to true to enable debug mode
     * @return void
     */
    public function setDebug(bool $value)
    {
        $this->debug = $value;
    }

    /**
     * Send a submit notification to the email address specified in the notifications tab in the survey settings
     *
     * @param int $surveyid survey ID of currently used survey
     * @param array $emails Emailnotifications that should be sent ['responseTo' => [['failedEmailId' => 'failedEmailId1', 'responseid' => 'responseid1', 'recipient' => 'recipient1', 'language' => 'language1'], [...]], 'notificationTo' => [[..., ..., ...][...]]]
     * @param bool $return whether the function should return values
     * @return array|void
     * @throws \PHPMailer\PHPMailer\Exception
     * @throws CException
     */
    public function run(int $surveyid, array $emails = [], bool $return = false)
    {

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
        if (!empty($this->thissurvey['emailnotificationto']) && empty($emails)) {
            $aRecipient = explode(";", LimeExpressionManager::ProcessStepString($this->thissurvey['emailnotificationto'], array('ADMINEMAIL' => $this->thissurvey['adminemail']), 3, true));
            foreach ($aRecipient as $sRecipient) {
                $sRecipient = trim($sRecipient);
                if ($mailer::validateAddress($sRecipient)) {
                    $aEmailNotificationTo[] = $sRecipient;
                }
            }
        }
        // // create array of recipients for emailresponses
        if (!empty($this->thissurvey['emailresponseto']) && empty($emails)) {
            $aRecipient = explode(";", LimeExpressionManager::ProcessStepString($this->thissurvey['emailresponseto'], array('ADMINEMAIL' => $this->thissurvey['adminemail']), 3, true));
            foreach ($aRecipient as $sRecipient) {
                $sRecipient = trim($sRecipient);
                if ($mailer::validateAddress($sRecipient)) {
                    $aEmailResponseTo[] = $sRecipient;
                }
            }
        }

        if (count($aEmailNotificationTo) || count($aEmailResponseTo)) {
            /* Force a replacement to fill coreReplacement like {SURVEYRESOURCESURL} for example */
            $reData = array('thissurvey' => $this->thissurvey);
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
            $this->processBasicAdminNotification();
        }

        // admin_notification (Detailed admin notification)
        if (count($aEmailResponseTo) > 0) {
            $this->processDetailedAdminNotification();
        }
        if ($return) {
            return [
                'successfullEmailCount' => $successfullEmailCount,
                'failedEmailCount'      => $failedEmailCount,
            ];
        }
    }

    /**
     * Saves a failed email whenever processing and sensing an email fails or overwrites a found entry with updated values
     *
     * @param int|null $id Id of failed email
     * @param string $recipient
     * @param int $surveyId
     * @param int $responseId
     * @param string $emailType
     * @param string $language
     * @param LimeMailer $mailer
     * @return bool
     */
    public function saveFailedEmail(?int $id, string $recipient, int $surveyId, int $responseId, string $emailType, string $language, LimeMailer $mailer): bool
    {
        $failedEmailModel = new FailedEmail();
        $errorMessage = $mailer->getError();
        $resendVars = json_encode($mailer->getResendEmailVars());
        if (isset($id)) {
            $failedEmail = $failedEmailModel->findByPk($id);
            if (isset($failedEmail)) {
                $failedEmail->surveyid = $surveyId;
                $failedEmail->error_message = $errorMessage;
                $failedEmail->status = 'SEND FAILED';
                $failedEmail->updated = date('Y-m-d H:i:s');
                return $failedEmail->save(false);
            }
        }
        $failedEmailModel->recipient = $recipient;
        $failedEmailModel->surveyid = $surveyId;
        $failedEmailModel->responseid = $responseId;
        $failedEmailModel->email_type = $emailType;
        $failedEmailModel->language = $language;
        $failedEmailModel->error_message = $errorMessage;
        $failedEmailModel->created = date('Y-m-d H:i:s');
        $failedEmailModel->status = 'SEND FAILED';
        $failedEmailModel->updated = date('Y-m-d H:i:s');
        $failedEmailModel->resend_vars = $resendVars;

        return $failedEmailModel->save(false);
    }

    /**
     * @param int $id The id of the failed email
     * @return bool
     */
    public function failedEmailSuccess($id)
    {
        $model = new FailedEmail();
        $failedEmail = $model->findByPk($id);
        if (isset($failedEmail)) {
            $failedEmail->status = 'SEND SUCCESS';
            $failedEmail->updated = date('Y-m-d H:i:s');
            return $failedEmail->save();
        }
        return false;
    }

    /**
     */
    public function processBasicAdminNotification()
    {
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
                $mailerSuccess = $mailer->resend(json_decode($sRecipient['resendVars'], true));
            } else {
                $failedNotificationId = null;
                $notificationRecipient = $sRecipient;
                $mailer->setTo($notificationRecipient);
                $mailerSuccess = $mailer->SendMessage();
            }
            if (!$mailerSuccess) {
                $failedEmailCount++;
                $this->saveFailedEmail($failedNotificationId, $notificationRecipient, $surveyid, $responseId, 'admin_notification', $emailLanguage, $mailer);
                if (empty($emails) && $this->debug && Permission::model()->hasSurveyPermission($surveyid, 'surveysettings', 'update')) {
                    /* Find a better way to show email error … */
                    echo CHtml::tag(
                        "div",
                        ['class' => 'alert alert-danger'],
                        sprintf(gT("Basic admin notification could not be sent because of error: %s"), $mailer->getError())
                    );
                }
            } else {
                $successfullEmailCount++;
                //preserve failedEmail if it exists
                $this->failedEmailSuccess($failedNotificationId);
            }
        }
    }

    public function processDetailedAdminNotification()
    {
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
                $aReplacementVars['ANSWERTABLE'] = getResponseTableReplacement($surveyid, $responseId, $emailLanguage, $isHtml);
                LimeExpressionManager::updateReplacementFields($aReplacementVars);
                $mailer->setTypeWithRaw('admin_responses', $emailLanguage);
                $mailer->setTo($responseRecipient);
                $mailerSuccess = $mailer->resend(json_decode($sRecipient['resendVars'], true));
            } else {
                $failedNotificationId = null;
                $responseRecipient = $sRecipient;
                $aReplacementVars['ANSWERTABLE'] = getResponseTableReplacement($surveyid, $responseId, $emailLanguage, $isHtml);
                LimeExpressionManager::updateReplacementFields($aReplacementVars);
                $mailer->setTo($responseRecipient);
                $mailerSuccess = $mailer->SendMessage();
            }
            if (!$mailerSuccess) {
                $failedEmailCount++;
                $this->saveFailedEmail($failedNotificationId, $responseRecipient, $surveyid, $responseId, 'admin_responses', $emailLanguage, $mailer);
                if (empty($emails) && $this->debug && Permission::model()->hasSurveyPermission($surveyid, 'surveysettings', 'update')) {
                    /* Find a better way to show email error … */
                    echo CHtml::tag(
                        "div",
                        ['class' => 'alert alert-danger'],
                        sprintf(gT("Detailed admin notification could not be sent because of error: %s"), $mailer->getError())
                    );
                }
            } else {
                $successfullEmailCount++;
                $this->failedEmailSuccess($failedNotificationId);
            }
        }
    }
}
