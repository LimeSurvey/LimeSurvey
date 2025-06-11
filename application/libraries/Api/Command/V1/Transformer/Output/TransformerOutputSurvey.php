<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Output;

use LimeSurvey\Models\Services\SurveyUseCaptcha;
use Survey;
use LimeSurvey\Api\Transformer\{
    Output\TransformerOutputActiveRecord,
};

class TransformerOutputSurvey extends TransformerOutputActiveRecord
{
    private TransformerOutputSurveyLanguageSettings $transformerOutputSurveyLanguageSettings;
    /**
     *  @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function __construct(
        TransformerOutputSurveyLanguageSettings $transformerOutputSurveyLanguageSettings
    ) {
        $this->transformerOutputSurveyLanguageSettings = $transformerOutputSurveyLanguageSettings;

        $this->setDataMap([
            'sid' => ['type' => 'int'],
            'gsid' => ['type' => 'int'],
            'owner_id' => ['key' => 'ownerId', 'type' => 'int'],
            'active' => ['formatter' => ['ynToBool' => true]],
            'admin' => true,
            'adminemail' => 'adminEmail',
            'language' => true,
            'expires' => [
                'formatter' => ['dateTimeToJson' => true]
            ],
            'startdate' => [
                'key' => 'startDate',
                'formatter' => ['dateTimeToJson' => true]
            ],
            'anonymized' => ['formatter' => ['ynToBool' => true]],
            'savetimings' => [
                'key' => 'saveTimings',
                'formatter' => ['ynToBool' => true]
            ],
            'additional_languages' => 'additionalLanguages',
            'datestamp' => ['formatter' => ['ynToBool' => true]],
            "usecookie" => [
                'key' => 'useCookie',
                'formatter' => ['ynToBool' => true]
            ],
            "allowregister" => [
                'key' => 'allowRegister',
                'formatter' => ['ynToBool' => true]
            ],
            "allowsave" => [
                'key' => 'allowSave',
                'formatter' => ['ynToBool' => true]
            ],
            "autonumber_start" => ['key' => 'autoNumberStart', 'type' => 'int'],
            "autoredirect" => [
                'key' => 'autoRedirect',
                'formatter' => ['ynToBool' => true]
            ],
            "allowprev" => [
                'key' => 'allowPrev',
                'formatter' => ['ynToBool' => true]
            ],
            "printanswers" => [
                'key' => 'printAnswers',
                'formatter' => ['ynToBool' => true]
            ],
            "ipaddr" => [
                'key' => 'ipAddr',
                'formatter' => ['ynToBool' => true]
            ],
            "ipanonymize" => [
                'key' => 'ipAnonymize',
                'formatter' => ['ynToBool' => true]
            ],
            "refurl" => [
                'key' => 'refUrl',
                'formatter' => ['ynToBool' => true]
            ],
            "datecreated" => [
                'key' => 'dateCreated',
                'formatter' => ['dateTimeToJson' => true]
            ],
            "publicstatistics" => [
                'key' => 'publicStatistics',
                'formatter' => ['ynToBool' => true]
            ],
            "publicgraphs" => [
                'key' => 'publicGraphs',
                'formatter' => ['ynToBool' => true]
            ],
            "listpublic" => [
                'key' => 'listPublic',
                'formatter' => ['ynToBool' => true]
            ],
            "sendconfirmation" => [
                'key' => 'sendConfirmation',
                'formatter' => ['ynToBool' => true]
            ],
            "tokenanswerspersistence" => [
                'key' => 'tokenAnswersPersistence',
                'formatter' => ['ynToBool' => true]
            ],
            "htmlemail" => [
                'key' => 'htmlEmail',
                'formatter' => ['ynToBool' => true]
            ],
            "assessments" => ['formatter' => ['ynToBool' => true]],
            "usecaptcha" => 'useCaptcha',
            "usetokens" => [
                'key' => 'useTokens',
                'formatter' => ['ynToBool' => true]
            ],
            "bounce_email" => 'bounceEmail',
            "attributedescriptions" => 'attributeDescriptions',
            "emailresponseto" => 'emailResponseTo',
            "emailnotificationto" => 'emailNotificationTo',
            "tokenlength" => ['key' => 'tokenLength', 'type' => 'int'],
            "showxquestions" => [
                'key' => 'showXQuestions',
                'formatter' => ['ynToBool' => true]
            ],
            "showgroupinfo" => 'showGroupInfo',
            "shownoanswer" => [
                'key' => 'showNoAnswer',
                'formatter' => ['ynToBool' => true]
            ],
            "showqnumcode" => 'showQNumCode',
            "bouncetime" => ['key' => 'bounceTime', 'type' => 'int'],
            "bounceprocessing" => [
                'key' => 'bounceProcessing',
                'formatter' => ['ynToBool' => true]
            ],
            "bounceaccounttype" => 'bounceAccountType',
            "bounceaccounthost" => 'bounceAccountHost',
            "bounceaccountpass" => 'bounceAccountPass',
            "bounceaccountencryption" => ['key' => 'bounceAccountEncryption'],
            "bounceaccountuser" => ['key' => 'bounceAccountUser'],
            "showwelcome" => [
                'key' => 'showWelcome',
                'formatter' => ['ynToBool' => true]
            ],
            "showprogress" => [
                'key' => 'showProgress',
                'formatter' => ['ynToBool' => true]
            ],
            "questionindex" => ['key' => 'questionIndex', 'type' => 'int'],
            "navigationdelay" => ['key' => 'navigationDelay', 'type' => 'int'],
            "nokeyboard" => [
                'key' => 'noKeyboard',
                'formatter' => ['ynToBool' => true]
            ],
            "alloweditaftercompletion" => [
                'key' => 'allowedItAfterCompletion',
                'formatter' => ['ynToBool' => true]
            ],
            "googleanalyticsstyle" => [
                'key' => 'googleAnalyticsStyle',
                'type' => 'int'
            ],
            "googleanalyticsapikey" => 'googleAnalyticsApiKey',
            "showsurveypolicynotice" => [
                'key' => 'showSurveyPolicyNotice',
                'type' => 'int'
            ],
            'template' => true,
            'format' => true,
            'othersettings' => true,
        ]);
    }

    public function transform($data, $options = [])
    {
        $options = $options ?? [];
        $survey = null;
        if (!$data instanceof Survey) {
            return null;
        }
        $survey = parent::transform($data);
        $survey['languageSettings'] = $this->transformerOutputSurveyLanguageSettings->transformAll(
            $data->languagesettings,
            $options
        );
        $survey['showGroupInfo'] = $this->convertShowGroupInfo(
            $data['showgroupinfo']
        );
        $survey['showQNumCode'] = $this->convertShowQNumCode(
            $data['showqnumcode']
        );
        $survey = $this->addOtherSettings($survey);
        return $this->transformUseCaptcha($survey);
    }

    /**
     * Converts single value of showgroupinfo to an array with
     * showGroupName and showGroupDescription keys.
     *
     * @param string $showGroupInfoValue
     * @return array
     */
    private function convertShowGroupInfo($showGroupInfoValue)
    {
        $showGroupName = in_array($showGroupInfoValue, ['B', 'N']);
        $showGroupDescription = in_array($showGroupInfoValue, ['B', 'D']);
        return [
            'showGroupName' => $showGroupName,
            'showGroupDescription' => $showGroupDescription,
        ];
    }

    /**
     * Converts single value of showqnumcode to an array with
     * showNumber and showCode keys.
     * @param string $showQNumCodeValue
     * @return array
     */
    private function convertShowQNumCode($showQNumCodeValue)
    {
        $showNumber = in_array($showQNumCodeValue, ['B', 'N']);
        $showCode = in_array($showQNumCodeValue, ['B', 'C']);
        return [
            'showNumber' => $showNumber,
            'showCode' => $showCode,
        ];
    }

    /**
     * Transforms useCaptcha into three values.
     *  -- survey access
     *  -- registration
     *  -- save and load
     *
     * @param array $survey
     * @return array
     */
    private function transformUseCaptcha($survey)
    {
        $surveyUseCaptcha = new SurveyUseCaptcha();
        $threeValues = $surveyUseCaptcha->convertUseCaptchaFromDB($survey['useCaptcha']);
        $survey['useCaptchaAccess'] = ($threeValues['surveyAccess'] == 'Y');
        $survey['useCaptchaRegistration'] = ($threeValues['registration'] == 'Y');
        $survey['useCaptchaSaveLoad'] = ($threeValues['saveAndLoad'] == 'Y');

        return $survey;
    }

    /**
     * Extracts code prefix settings from the othersettings JSON field and adds them to the survey array.
     *
     * This function parses the othersettings JSON string from the survey array and extracts
     * specific prefix settings for questions, subquestions, and answers. These settings are then
     * added as separate keys to the survey array for easier access.
     *
     * @param array $survey The survey array containing the othersettings JSON string
     * @return array The modified survey array with extracted prefix settings
     */
    private function addOtherSettings($survey)
    {
        $otherSettingsObj = json_decode($survey['othersettings']);
        if (is_object($otherSettingsObj)) {
            $survey['question_code_prefix'] = $otherSettingsObj->question_code_prefix;
            $survey['subquestion_code_prefix'] = $otherSettingsObj->subquestion_code_prefix;
            $survey['answer_code_prefix'] = $otherSettingsObj->answer_code_prefix;
        }

        return $survey;
    }
}
