<?php

namespace LimeSurvey\Models\Services;

use LimeMailer;
use LimeExpressionManager;
use Permission;
use CHtml;
use CException;
use FailedEmail;
use InvalidArgumentException;
use Survey;
use Yii;
use viewHelper;
use SurveyDynamic;
use QuestionAttribute;
use QuestionGroup;

/**
 * Refactor of old sendSubmitNotifications function, to enable injection of dependencies.
 * Command object pattern, have one function called run() or similar.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
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
     * @var SessionInterface
     */
    private $session;

    /**
     * Inject dependencies so they can be mocked.
     *
     * @param array $thissurvey
     * @param LimeMailer $limeMailer Like \LimeMailer::getInstance(\LimeMailer::ResetComplete);
     */
    public function __construct(array $thissurvey, LimeMailer $limeMailer, SessionInterface $session)
    {
        if (!isset($thissurvey['htmlemail'])) {
            throw new InvalidArgumentException('Missing htmlemail in survey info');
        }

        $this->thissurvey = $thissurvey;
        $this->mailer = $limeMailer;
        $this->session = $session;
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
     * @param int $surveyId survey ID of currently used survey
     * @param array $emails Emailnotifications that should be sent ['responseTo' => [['failedEmailId' => 'failedEmailId1', 'responseid' => 'responseid1', 'recipient' => 'recipient1', 'language' => 'language1'], [...]], 'notificationTo' => [[..., ..., ...][...]]]
     * @return array
     * @throws \PHPMailer\PHPMailer\Exception
     * @throws CException
     */
    public function run(int $surveyId, array $emails = [])
    {
        //LimeMailer instance
        $this->mailer->setSurvey($surveyId);
        $this->mailer->aUrlsPlaceholders = ['VIEWRESPONSE','EDITRESPONSE','STATISTICS'];

        $responseId          = $this->getResponseId($surveyId);
        $replacementVars     = $this->getReplacementVars($surveyId, $responseId);
        $emailLanguage       = $this->getLanguage($surveyId);
        $emailNotificationTo = $this->getEmailNotificationTo($emails);
        $emailResponseTo     = $this->getEmailResponseTo($emails);

        if (count($emailNotificationTo) || count($emailResponseTo)) {
            /* Force a replacement to fill coreReplacement like {SURVEYRESOURCESURL} for example */
            $reData = ['thissurvey' => $this->thissurvey];
            templatereplace(
                "{SID}",
                [], /* No tempvars update (except old Replacement like */
                $reData /* Be sure to use current survey */
            );
        }

        // update replacement fields before we handle email sending
        LimeExpressionManager::updateReplacementFields($replacementVars);

        // admin_notification (Basic admin notification)
        if (count($emailNotificationTo) > 0) {
            $warnings = $this->processBasicAdminNotification($surveyId, $emailNotificationTo, $emails, $emailLanguage, $responseId);
            if ($warnings) {
                echo $warnings;
            }
        }

        // admin_notification (Detailed admin notification)
        if (count($emailResponseTo) > 0) {
            $warnings = $this->processDetailedAdminNotification($surveyId, $replacementVars, $emailResponseTo, $emails, $emailLanguage, $responseId);
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
    public function processBasicAdminNotification(int $surveyId, array $emailNotificationTo, array $emails, string $emailLanguage, ?int $responseId)
    {
        $this->mailer = $this->mailer::getInstance();
        $this->mailer->setTypeWithRaw('admin_notification', $emailLanguage);
        $htmlWarnings = '';
        foreach ($emailNotificationTo as $sRecipient) {
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
                $this->saveFailedEmail($failedNotificationId, $notificationRecipient, $surveyId, $responseId, 'admin_notification', $emailLanguage);
                if (empty($emails) && $this->debug && Permission::model()->hasSurveyPermission($surveyId, 'surveysettings', 'update')) {
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
    public function processDetailedAdminNotification(int $surveyId, array $replacementVars, array $emailResponseTo, array $emails, string $emailLanguage, ?int $responseId): string
    {
        // There was no token used so let's remove the token field from insertarray
        $surveyData = $this->session->get('survey_' . $surveyId, []);
        if (isset($surveyData['insertarray'][0])) {
            if (!isset($surveyData['token']) && $surveyData['insertarray'][0] === 'token') {
                unset($_SESSION['survey_' . $surveyId]['insertarray'][0]);
            }
        }
        $this->mailer = $this->mailer::getInstance();
        $this->mailer->setTypeWithRaw('admin_responses', $emailLanguage);
        $htmlWarnings = '';
        foreach ($emailResponseTo as $sRecipient) {
            /** set mailer params for @see FailedEmailController::actionResend() */
            if (!empty($emails)) {
                $failedNotificationId = $sRecipient['id'];
                $responseId = $sRecipient['responseId'];
                $responseRecipient = $sRecipient['recipient'];
                $emailLanguage = $sRecipient['language'];
                $replacementVars['ANSWERTABLE'] = $this->getResponseTableReplacement($surveyId, $responseId, $emailLanguage);
                LimeExpressionManager::updateReplacementFields($replacementVars);
                $this->mailer->setTypeWithRaw('admin_responses', $emailLanguage);
                $this->mailer->setTo($responseRecipient);
                $mailerSuccess = $this->mailer->resend(json_decode($sRecipient['resendVars'], true));
            } else {
                $failedNotificationId = null;
                $responseRecipient = $sRecipient;
                $replacementVars['ANSWERTABLE'] = $this->getResponseTableReplacement($surveyId, $responseId, $emailLanguage);
                LimeExpressionManager::updateReplacementFields($replacementVars);
                $this->mailer->setTo($responseRecipient);
                $mailerSuccess = $this->mailer->SendMessage();
            }
            if ($responseId === null) {
                throw new InvalidArgumentException("responseId can never be null");
            }
            if (!$mailerSuccess) {
                $this->result['failedEmailCount']++;
                $this->saveFailedEmail($failedNotificationId, $responseRecipient, $surveyId, $responseId, 'admin_responses', $emailLanguage);
                if (empty($emails) && $this->debug && Permission::model()->hasSurveyPermission($surveyId, 'surveysettings', 'update')) {
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

    /**
     * Create ANSWERTABLE replacement field content
     *
     * @param int $surveyId
     * @param ?int $responseId
     * @param string $emailLanguage
     * @return string
     * @throws CException
     */
    public function getResponseTableReplacement($surveyId, ?int $responseId, $emailLanguage): string
    {
        $aFullResponseTable = $this->getFullResponseTable($surveyId, $responseId, $emailLanguage);
        $ResultTableHTML = "<table class='printouttable' >\n";
        $ResultTableText = "\n\n";
        Yii::import('application.helpers.viewHelper');
        foreach ($aFullResponseTable as $sFieldname => $fname) {
            if (substr($sFieldname, 0, 4) === 'gid_') {
                $ResultTableHTML .= "\t<tr class='printanswersgroup'><td colspan='2'>" . viewHelper::flatEllipsizeText($fname[0], true, 0) . "</td></tr>\n";
                $ResultTableText .= "\n{$fname[0]}\n\n";
            } elseif (substr($sFieldname, 0, 4) === 'qid_') {
                $ResultTableHTML .= "\t<tr class='printanswersquestionhead'><td  colspan='2'>" . viewHelper::flatEllipsizeText($fname[0], true, 0) . "</td></tr>\n";
                $ResultTableText .= "\n{$fname[0]}\n";
            } else {
                $ResultTableHTML .= "\t<tr class='printanswersquestion'><td>" . viewHelper::flatEllipsizeText("{$fname[0]} {$fname[1]}", true, 0) . "</td><td class='printanswersanswertext'>" . CHtml::encode($fname[2]) . "</td></tr>\n";
                $ResultTableText .= "     {$fname[0]} {$fname[1]}: {$fname[2]}\n";
            }
        }
        $ResultTableHTML .= "</table>\n";
        $ResultTableText .= "\n\n";

        if ($this->isHtml) {
            return $ResultTableHTML;
        }
        return $ResultTableText;
    }

    /**
     * Creates an array with details on a particular response for display purposes
     * Used in Print answers, Detailed response view and Detailed admin notification email
     *
     * @param int $surveyId
     * @param ?int $responseId
     * @param string $languageCode
     * @param boolean $honorConditions Apply conditions
     * @return array
     */
    public function getFullResponseTable($surveyId, ?int $responseId, $languageCode, $honorConditions = true)
    {
        $survey = Survey::model()->findByPk($surveyId);
        if ($survey === null) {
            throw new InvalidArgumentException("Found no survey with id " . $surveyId);
        }

        $aFieldMap = \createFieldMap($survey, 'full', false, false, $languageCode);

        //Get response data
        $idrow = SurveyDynamic::model($surveyId)->findByAttributes(array('id' => $responseId));
        if ($idrow === null) {
            throw new InvalidArgumentException("Found no idrow for id " . $responseId);
        }

        // Create array of non-null values - those are the relevant ones
        $aRelevantFields = array();

        foreach ($aFieldMap as $sKey => $fname) {
            if (LimeExpressionManager::QuestionIsRelevant($fname['qid']) || $honorConditions === false) {
                $aRelevantFields[$sKey] = $fname;
            }
        }

        return $this->loopRelevantFieldRow($aRelevantFields, $languageCode, $idrow, $surveyId, $honorConditions);
    }

    /**
     * @param array $aRelevantFields
     * @param string $languageCode
     * @param SurveyDynamic $idrow
     * @param bool $honorConditions
     */
    public function loopRelevantFieldRow(array $aRelevantFields, string $languageCode, SurveyDynamic $idrow, int $surveyId, bool $honorConditions): array
    {
        $aResultTable = [];
        $oldgid = 0;
        $oldqid = 0;
        foreach ($aRelevantFields as $sKey => $fname) {
            if (!empty($fname['qid'])) {
                // TODO: Correct behaviour if $attributes is false?
                $attributes = QuestionAttribute::model()->getQuestionAttributes($fname['qid']);
                if ($attributes !== false && $this->getQuestionAttributeValue($attributes, 'hidden') == 1) {
                    continue;
                }
            }
            $question = $fname['question'];
            $subquestion = '';
            if (isset($fname['gid']) && !empty($fname['gid'])) {
                //Check to see if gid is the same as before. if not show group name
                if ($oldgid !== $fname['gid']) {
                    $oldgid = $fname['gid'];
                    if (LimeExpressionManager::GroupIsRelevant($fname['gid']) || $honorConditions === false) {
                        $aResultTable['gid_' . $fname['gid']] = array($fname['group_name'], QuestionGroup::model()->getGroupDescription($fname['gid'], $languageCode));
                    }
                }
            }
            if (!empty($fname['qid'])) {
                if ($oldqid !== $fname['qid']) {
                    $oldqid = $fname['qid'];
                    if (isset($fname['subquestion']) || isset($fname['subquestion1']) || isset($fname['subquestion2'])) {
                        $aResultTable['qid_' . $fname['sid'] . 'X' . $fname['gid'] . 'X' . $fname['qid']] = array($fname['question'], '', '');
                    } else {
                        $answer = getExtendedAnswer($surveyId, $fname['fieldname'], $idrow[$fname['fieldname']], $languageCode);
                        $aResultTable[$fname['fieldname']] = array($question, '', $answer);
                        continue;
                    }
                }
            } else {
                $answer = getExtendedAnswer($surveyId, $fname['fieldname'], $idrow[$fname['fieldname']], $languageCode);
                $aResultTable[$fname['fieldname']] = array($question, '', $answer);
                continue;
            }
            if (isset($fname['subquestion'])) {
                $subquestion = "[{$fname['subquestion']}]";
            }

            if (isset($fname['subquestion1'])) {
                $subquestion = "[{$fname['subquestion1']}]";
            }

            if (isset($fname['subquestion2'])) {
                $subquestion .= "[{$fname['subquestion2']}]";
            }

            $answer = getExtendedAnswer($surveyId, $fname['fieldname'], $idrow[$fname['fieldname']], $languageCode);
            $aResultTable[$fname['fieldname']] = array($question, $subquestion, $answer);
        }
        return $aResultTable;
    }

    /**
     * Returns the questionAttribtue value set or '' if not set
     *
     * @author: lemeur
     * @param array $questionAttributeArray
     * @param string $attributeName
     * @param ?string $language Optional: The language if the particualr attributes is localizable
     * @return string
     */
    public function getQuestionAttributeValue(array $questionAttributeArray, string $attributeName, ?string $language = '')
    {
        if ($language == '' && isset($questionAttributeArray[$attributeName])) {
            return $questionAttributeArray[$attributeName];
        } elseif ($language != '' && isset($questionAttributeArray[$attributeName][$language])) {
            return $questionAttributeArray[$attributeName][$language];
        } else {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getLanguage(int $surveyId): string
    {
        $surveyData = $this->session->get('survey_' . $surveyId, []);
        $lang = $surveyData['s_lang'] ?? null;
        return $lang ?? App()->getLanguage();
    }

    /**
     * Get replacement vars for EM
     */
    public function getReplacementVars(int $surveyId, ?int $responseId): array
    {
        $replacementVars = [];
        $replacementVars['STATISTICSURL'] = App()->getController()->createAbsoluteUrl("/admin/statistics/sa/index/surveyid/{$surveyId}");
        $replacementVars['ANSWERTABLE'] = '';

        $surveyData = $this->session->get('survey_' . $surveyId, null);
        if (!isset($surveyData['srid'])) {
            // Do nothing
        } elseif ($responseId !== null) {
            // ReplacementVars for LEM requiring a response id
            $replacementVars['EDITRESPONSEURL'] = App()->getController()->createAbsoluteUrl("/admin/dataentry/sa/editdata/subaction/edit/surveyid/{$surveyId}/id/{$responseId}");
            $replacementVars['VIEWRESPONSEURL'] = App()->getController()->createAbsoluteUrl("responses/view/", ['surveyId' => $surveyId, 'id' => $responseId]);
        }
        return $replacementVars;
    }

    /**
     * @todo What to do if null?
     * @return ?int
     */
    public function getResponseId(int $surveyId): ?int
    {
        $surveyData = $this->session->get('survey_' . $surveyId, null);
        if (!isset($surveyData['srid'])) {
            $responseId = null; /* Maybe just return ? */
        } else {
            // ReplacementVars for LEM requiring a response id
            $responseId = $surveyData['srid'];
        }
        return $responseId;
    }

    /**
     * Create array of recipients for emailnotifications
     *
     * @param array $emails
     * @return array
     */
    public function getEmailNotificationTo(array $emails): array
    {
        $emailNotificationTo = $emails['admin_notification'] ?? [];
        if (!empty($this->thissurvey['emailnotificationto']) && empty($emails)) {
            $aRecipient = explode(";", LimeExpressionManager::ProcessStepString($this->thissurvey['emailnotificationto'], array('ADMINEMAIL' => $this->thissurvey['adminemail']), 3, true));
            foreach ($aRecipient as $sRecipient) {
                $sRecipient = trim($sRecipient);
                if ($this->mailer::validateAddress($sRecipient)) {
                    $emailNotificationTo[] = $sRecipient;
                }
            }
        }
        return $emailNotificationTo;
    }

    /**
     * Emails to be sent and return values
     *
     * @param array $emails
     * @return array
     */
    public function getEmailResponseTo(array $emails): array
    {
        $emailResponseTo = $emails['admin_responses'] ?? [];
        if (!empty($this->thissurvey['emailresponseto']) && empty($emails)) {
            $aRecipient = explode(";", LimeExpressionManager::ProcessStepString($this->thissurvey['emailresponseto'], array('ADMINEMAIL' => $this->thissurvey['adminemail']), 3, true));
            foreach ($aRecipient as $sRecipient) {
                $sRecipient = trim($sRecipient);
                if ($this->mailer::validateAddress($sRecipient)) {
                    $emailResponseTo[] = $sRecipient;
                }
            }
        }
        return $emailResponseTo;
    }
}
