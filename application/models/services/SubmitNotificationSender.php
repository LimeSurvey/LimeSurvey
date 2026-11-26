<?php

namespace LimeSurvey\Models\Services;

use CHtml;
use LimeExpressionManager;
use LimeMailer;
use Permission;

/**
 * Sends the basic and the detailed admin notification of a response.
 *
 * At the end of a survey, sendForCurrentResponse() sends the notifications of the response in the
 * participant session to the recipients from the survey settings. Notifications that could not be
 * sent are stored by saveFailedEmail() with their MIME body, resend() sends these stored emails
 * again (see FailedEmailController::actionResend()).
 */
class SubmitNotificationSender
{
    /** @var string Email type of the basic admin notification */
    public const TYPE_BASIC = 'admin_notification';

    /** @var string Email type of the detailed admin notification (with the answers) */
    public const TYPE_DETAILED = 'admin_responses';

    /** @var string[] Placeholders in the email templates that LimeMailer turns into links */
    private const URL_PLACEHOLDERS = ['VIEWRESPONSE', 'EDITRESPONSE', 'STATISTICS'];

    /** @var int */
    private int $surveyId;

    /** @var int Number of emails sent by the current send or resend */
    private int $successCount = 0;

    /** @var int Number of emails that could not be sent by the current send or resend */
    private int $failedCount = 0;

    /**
     * Create a sender for the notifications of one survey
     *
     * @param int $surveyId ID of the survey the responses belong to
     */
    public function __construct(int $surveyId)
    {
        $this->surveyId = $surveyId;
        // For getResponseTableReplacement(), saveFailedEmail() and failedEmailSuccess()
        App()->loadHelper('frontend');
        App()->loadHelper('replacements');
    }

    /**
     * Send the notifications of the response in the participant session to the recipients
     * from the survey settings. Failed emails are stored for a later resend, and if debug mode is
     * on, the error is shown to users who may update the survey settings.
     *
     * @param array $surveyInfo Survey info like from getSurveyInfo(), in the language of the response
     * @return array{successfullEmailCount: int, failedEmailCount: int} Number of emails sent and failed
     * @throws \PHPMailer\PHPMailer\Exception
     * @throws \CException
     */
    public function sendForCurrentResponse(array $surveyInfo): array
    {
        $this->resetCounts();
        $this->resetMailer();
        $sessionState = SurveySessionState::forSurvey($this->surveyId);
        $responseId = $sessionState->getResponseId();
        $language = $sessionState->getLanguage();
        $recipientsByType = [
            self::TYPE_BASIC => $this->getRecipients($surveyInfo['emailnotificationto'] ?? '', $surveyInfo['adminemail'] ?? ''),
            self::TYPE_DETAILED => $this->getRecipients($surveyInfo['emailresponseto'] ?? '', $surveyInfo['adminemail'] ?? ''),
        ];
        if (empty(array_filter($recipientsByType))) {
            return $this->getCounts();
        }

        // Force a replacement to fill the core replacements like {SURVEYRESOURCESURL}
        $templateData = ['thissurvey' => $surveyInfo];
        templatereplace("{SID}", [], $templateData);
        $replacements = $this->getReplacements($responseId);
        // Same response and language for all recipients of both notification types: Build the answer table only once
        $replacements['ANSWERTABLE'] = getResponseTableReplacement(
            $this->surveyId,
            $responseId,
            $language,
            ($surveyInfo['htmlemail'] ?? '') === 'Y'
        );

        foreach ($recipientsByType as $type => $recipients) {
            if (empty($recipients)) {
                continue;
            }
            $mailer = LimeMailer::getInstance();
            $mailer->addAndReplaceReplacement($replacements);
            $mailer->setTypeWithRaw($type, $language);
            foreach ($recipients as $recipient) {
                $mailer->setTo($recipient);
                if ($mailer->SendMessage()) {
                    $this->successCount++;
                    continue;
                }
                $this->failedCount++;
                saveFailedEmail(null, $recipient, $this->surveyId, $responseId, $type, $language, $mailer);
                $this->showSendError($type, (string) $mailer->getError());
            }
        }
        return $this->getCounts();
    }

    /**
     * Send stored failed notifications again. The stored MIME body and subject are sent as they are,
     * so the email is the same as the one that failed. Each stored entry is updated with the result.
     *
     * @param array $failedEmailsByType Failed notifications by type (TYPE_BASIC, TYPE_DETAILED):
     *     [type => [['id' => failed email ID, 'responseId' => response ID, 'recipient' => address,
     *     'language' => language code, 'resendVars' => JSON of LimeMailer::getResendEmailVars()], …]]
     * @return array{successfullEmailCount: int, failedEmailCount: int} Number of emails sent and failed
     * @throws \PHPMailer\PHPMailer\Exception
     * @throws \CException
     */
    public function resend(array $failedEmailsByType): array
    {
        $this->resetCounts();
        $this->resetMailer();
        foreach ([self::TYPE_BASIC, self::TYPE_DETAILED] as $type) {
            if (empty($failedEmailsByType[$type])) {
                continue;
            }
            $mailer = LimeMailer::getInstance();
            foreach ($failedEmailsByType[$type] as $failedEmail) {
                $mailer->setTypeWithRaw($type, $failedEmail['language']);
                $mailer->setTo($failedEmail['recipient']);
                if ($mailer->resend(json_decode((string) $failedEmail['resendVars'], true))) {
                    $this->successCount++;
                    failedEmailSuccess($failedEmail['id']);
                    continue;
                }
                $this->failedCount++;
                saveFailedEmail(
                    $failedEmail['id'],
                    $failedEmail['recipient'],
                    $this->surveyId,
                    $failedEmail['responseId'],
                    $type,
                    $failedEmail['language'],
                    $mailer
                );
            }
        }
        return $this->getCounts();
    }

    /**
     * Start with a new mailer for this survey
     *
     * @return void
     */
    private function resetMailer(): void
    {
        $mailer = LimeMailer::getInstance(LimeMailer::ResetComplete);
        $mailer->setSurvey($this->surveyId);
        $mailer->aUrlsPlaceholders = self::URL_PLACEHOLDERS;
    }

    /**
     * Get the valid email addresses from a recipient setting of the survey
     *
     * @param string|null $setting Recipients separated by semicolons, may contain expressions
     * @param string|null $adminEmail Value of the {ADMINEMAIL} placeholder
     * @return string[] The valid addresses, empty if the setting is empty
     */
    private function getRecipients(?string $setting, ?string $adminEmail): array
    {
        if (empty($setting)) {
            return [];
        }
        $recipients = LimeExpressionManager::ProcessStepString($setting, ['ADMINEMAIL' => $adminEmail], 3, true);
        return LimeMailer::validateAddresses($recipients);
    }

    /**
     * Get the replacements for the links to the statistics and to the response
     *
     * @param int|null $responseId ID of the response, null if the session has none: Then there are no response links
     * @return array<string, string> Replacement values by placeholder name
     */
    private function getReplacements(?int $responseId): array
    {
        $controller = App()->getController();
        $replacements = [
            'STATISTICSURL' => $controller->createAbsoluteUrl("/admin/statistics/sa/index/surveyid/{$this->surveyId}"),
        ];
        if ($responseId !== null) {
            $replacements['EDITRESPONSEURL'] = $controller->createAbsoluteUrl("/admin/dataentry/sa/editdata/subaction/edit/surveyid/{$this->surveyId}/id/{$responseId}");
            $replacements['VIEWRESPONSEURL'] = $controller->createAbsoluteUrl("responses/view/", ['surveyId' => $this->surveyId, 'id' => $responseId]);
        }
        return $replacements;
    }

    /**
     * Show why a notification could not be sent, in debug mode and only to users who may update
     * the survey settings
     *
     * @param string $type TYPE_BASIC or TYPE_DETAILED
     * @param string $error The mailer error
     * @return void
     */
    protected function showSendError(string $type, string $error): void
    {
        if (
            App()->getConfig('debug') <= 0
            || !Permission::model()->hasSurveyPermission($this->surveyId, 'surveysettings', 'update')
        ) {
            return;
        }
        if ($type === self::TYPE_BASIC) {
            $message = gT("Basic admin notification could not be sent because of error: %s");
        } else {
            $message = gT("Detailed admin notification could not be sent because of error: %s");
        }
        echo CHtml::tag("div", ['class' => 'alert alert-danger'], sprintf($message, $error));
    }

    /**
     * Start counting the sent and failed emails from zero
     *
     * @return void
     */
    private function resetCounts(): void
    {
        $this->successCount = 0;
        $this->failedCount = 0;
    }

    /**
     * Get the number of emails sent and failed since the last resetCounts()
     *
     * @return array{successfullEmailCount: int, failedEmailCount: int} The counts, with the keys used by the resend result views
     */
    private function getCounts(): array
    {
        return [
            'successfullEmailCount' => $this->successCount,
            'failedEmailCount' => $this->failedCount,
        ];
    }
}
