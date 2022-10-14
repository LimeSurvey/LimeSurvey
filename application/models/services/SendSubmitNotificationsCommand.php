<?php

namespace LimeSurvey\Models\Services;

use LimeMailer;
use LimeExpressionManager;
use Permission;
use CHtml;
use CException;
use FailedEmail;
use InvalidArgumentException;

/**
 * Refactor of old sendSubmitNotifications function, to enable injection of dependencies.
 * Command object pattern, have one function called run() or similar.
 */
class SendSubmitNotificationsCommand
{
    /** @var array Mega array, see SurveyRuntimeHelper */
    private $thissurvey;

    /** @var LimeMailer instance */
    private $mailer;

    /** @var bool */
    private $debug = false;

    /** @var bool Needed for ANSWERTABLE */
    private $isHtml = true;

    /** @var array<string, int> */
    private $result = [
        'failedEmailCount'      => 0,
        'successfullEmailCount' => 0
    ];

    /**
     * Inject dependencies so they can be mocked.
     *
     * @param array $thissurvey
     * @param LimeMailer $limeMailer Like \LimeMailer::getInstance(\LimeMailer::ResetComplete);
     */
    public function __construct(array $thissurvey, LimeMailer $limeMailer)
    {
        $this->thissurvey = $thissurvey;
        $this->mailer = $limeMailer;
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
     * @return array
     * @throws \PHPMailer\PHPMailer\Exception
     * @throws CException
     */
    public function run(int $surveyid, array $emails = [])
    {
        //LimeMailer instance
        $this->mailer->setSurvey($surveyid);
        $this->mailer->aUrlsPlaceholders = ['VIEWRESPONSE','EDITRESPONSE','STATISTICS'];

        //emails to be sent and return values
        $aEmailNotificationTo = $emails['admin_notification'] ?? [];
        $aEmailResponseTo = $emails['admin_responses'] ?? [];

        //replacementVars for LEM
        $aReplacementVars = [];
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
        $emailLanguage = App()->getLanguage();
        if (isset($_SESSION['survey_' . $surveyid]['s_lang'])) {
            $emailLanguage = $_SESSION['survey_' . $surveyid]['s_lang'];
        }

        // create array of recipients for emailnotifications
        if (!empty($this->thissurvey['emailnotificationto']) && empty($emails)) {
            $aRecipient = explode(";", LimeExpressionManager::ProcessStepString($this->thissurvey['emailnotificationto'], array('ADMINEMAIL' => $this->thissurvey['adminemail']), 3, true));
            foreach ($aRecipient as $sRecipient) {
                $sRecipient = trim($sRecipient);
                if ($this->mailer::validateAddress($sRecipient)) {
                    $aEmailNotificationTo[] = $sRecipient;
                }
            }
        }
        // // create array of recipients for emailresponses
        if (!empty($this->thissurvey['emailresponseto']) && empty($emails)) {
            $aRecipient = explode(";", LimeExpressionManager::ProcessStepString($this->thissurvey['emailresponseto'], array('ADMINEMAIL' => $this->thissurvey['adminemail']), 3, true));
            foreach ($aRecipient as $sRecipient) {
                $sRecipient = trim($sRecipient);
                if ($this->mailer::validateAddress($sRecipient)) {
                    $aEmailResponseTo[] = $sRecipient;
                }
            }
        }

        if (count($aEmailNotificationTo) || count($aEmailResponseTo)) {
            /* Force a replacement to fill coreReplacement like {SURVEYRESOURCESURL} for example */
            $reData = ['thissurvey' => $this->thissurvey];
            templatereplace(
                "{SID}",
                [], /* No tempvars update (except old Replacement like */
                $reData /* Be sure to use current survey */
            );
        }

        // update replacement fields before we handle email sending
        LimeExpressionManager::updateReplacementFields($aReplacementVars);

        // admin_notification (Basic admin notification)
        if (count($aEmailNotificationTo) > 0) {
            $warnings = $this->processBasicAdminNotification($surveyid, $aEmailNotificationTo, $emails, $emailLanguage, $responseId);
            if ($warnings) {
                echo $warnings;
            }
        }

        // admin_notification (Detailed admin notification)
        if (count($aEmailResponseTo) > 0) {
            $warnings = $this->processDetailedAdminNotification($surveyid, $aReplacementVars, $aEmailResponseTo, $emails, $emailLanguage, $responseId);
            if ($warnings) {
                echo $warnings;
            }
        }

        return $this->result;
    }

    /**
     * Saves a failed email whenever processing and sensing an email fails or overwrites a found entry with updated values
     *
     * @param ?int $id Id of failed email
     * @param string $recipient
     * @param int $surveyId
     * @param int $responseId
     * @param string $emailType
     * @param string $language
     * @return bool
     */
    public function saveFailedEmail(?int $id, string $recipient, int $surveyId, int $responseId, string $emailType, string $language): bool
    {
        $failedEmailModel = new FailedEmail();
        $errorMessage = $this->mailer->getError();
        $resendVars = json_encode($this->mailer->getResendEmailVars());
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
     * @param ?int $id The id of the failed email
     * @return bool
     */
    public function failedEmailSuccess($id = -1)
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
     * @return string HTML warnings
     */
    public function processBasicAdminNotification(int $surveyid, array $aEmailNotificationTo, array $emails, string $emailLanguage, ?int $responseId)
    {
        $this->mailer = $this->mailer::getInstance();
        $this->mailer->setTypeWithRaw('admin_notification', $emailLanguage);
        $htmlWarnings = '';
        foreach ($aEmailNotificationTo as $sRecipient) {
            /** set mailer params for @see FailedEmailController::actionResend() */
            if (!empty($emails)) {
                $failedNotificationId = $sRecipient['id'];
                $responseId = $sRecipient['responseId'];
                $notificationRecipient = $sRecipient['recipient'];
                $emailLanguage = $sRecipient['language'];
                $this->mailer->setTypeWithRaw('admin_notification', $emailLanguage);
                $this->mailer->setTo($notificationRecipient);
                $mailerSuccess = $this->mailer->resend(json_decode($sRecipient['resendVars'], true));
            } else {
                $failedNotificationId = null;
                $notificationRecipient = $sRecipient;
                $this->mailer->setTo($notificationRecipient);
                $mailerSuccess = $this->mailer->SendMessage();
            }
            if ($responseId === null) {
                throw new InvalidArgumentException("responseId can never be null");
            }

            if (!$mailerSuccess) {
                $this->result['failedEmailCount']++;
                $this->saveFailedEmail($failedNotificationId, $notificationRecipient, $surveyid, $responseId, 'admin_notification', $emailLanguage); 
                if (empty($emails) && $this->debug && Permission::model()->hasSurveyPermission($surveyid, 'surveysettings', 'update')) {
                    /* Find a better way to show email error … */
                    $htmlWarnings .= CHtml::tag(
                        "div",
                        ['class' => 'alert alert-danger'],
                        sprintf(gT("Basic admin notification could not be sent because of error: %s"), $this->mailer->getError())
                    );
                }
            } else {
                $this->result['successfullEmailCount']++;
                //preserve failedEmail if it exists
                $this->failedEmailSuccess($failedNotificationId);
            }
        }
        return $htmlWarnings;
    }

    /**
     * @return string
     */
    public function processDetailedAdminNotification(int $surveyid, array $aReplacementVars, array $aEmailResponseTo, array $emails, string $emailLanguage, ?int $responseId): string
    {
        // There was no token used so let's remove the token field from insertarray
        if (isset($_SESSION['survey_' . $surveyid]['insertarray'][0])) {
            if (!isset($_SESSION['survey_' . $surveyid]['token']) && $_SESSION['survey_' . $surveyid]['insertarray'][0] === 'token') {
                unset($_SESSION['survey_' . $surveyid]['insertarray'][0]);
            }
        }
        $this->mailer = $this->mailer::getInstance();
        $this->mailer->setTypeWithRaw('admin_responses', $emailLanguage);
        $htmlWarnings = '';
        foreach ($aEmailResponseTo as $sRecipient) {
            /** set mailer params for @see FailedEmailController::actionResend() */
            if (!empty($emails)) {
                $failedNotificationId = $sRecipient['id'];
                $responseId = $sRecipient['responseId'];
                $responseRecipient = $sRecipient['recipient'];
                $emailLanguage = $sRecipient['language'];
                $aReplacementVars['ANSWERTABLE'] = getResponseTableReplacement($surveyid, $responseId, $emailLanguage, $this->isHtml);
                LimeExpressionManager::updateReplacementFields($aReplacementVars);
                $this->mailer->setTypeWithRaw('admin_responses', $emailLanguage);
                $this->mailer->setTo($responseRecipient);
                $mailerSuccess = $this->mailer->resend(json_decode($sRecipient['resendVars'], true));
            } else {
                $failedNotificationId = null;
                $responseRecipient = $sRecipient;
                $aReplacementVars['ANSWERTABLE'] = getResponseTableReplacement($surveyid, $responseId, $emailLanguage, $this->isHtml);
                LimeExpressionManager::updateReplacementFields($aReplacementVars);
                $this->mailer->setTo($responseRecipient);
                $mailerSuccess = $this->mailer->SendMessage();
            }
            if ($responseId === null) {
                throw new InvalidArgumentException("responseId can never be null");
            }
            if (!$mailerSuccess) {
                $this->result['failedEmailCount']++;
                $this->saveFailedEmail($failedNotificationId, $responseRecipient, $surveyid, $responseId, 'admin_responses', $emailLanguage);
                if (empty($emails) && $this->debug && Permission::model()->hasSurveyPermission($surveyid, 'surveysettings', 'update')) {
                    /* Find a better way to show email error … */
                    $htmlWarnings = CHtml::tag(
                        "div",
                        ['class' => 'alert alert-danger'],
                        sprintf(gT("Detailed admin notification could not be sent because of error: %s"), $this->mailer->getError())
                    );
                }
            } else {
                $this->result['successfullEmailCount']++;
                $this->failedEmailSuccess($failedNotificationId);
            }
        }
        return $htmlWarnings;
    }
}
