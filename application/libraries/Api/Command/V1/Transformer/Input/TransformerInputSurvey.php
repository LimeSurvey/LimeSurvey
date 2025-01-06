<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;
use LimeSurvey\Models\Services\SurveyUseCaptcha;

class TransformerInputSurvey extends Transformer
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function __construct()
    {
        $this->setDataMap([
            'sid' => ['type' => 'int'],
            'gsid' => ['type' => 'int'],
            'ownerId' => ['key' => 'owner_id', 'type' => 'int'],
            'active' => [
                'formatter' => ['ynToBool' => ['revert' => true]],
                'range' => [true, false]
            ],
            'language' => ['filter' => 'trim'],
            'admin' => ['length' => ['min' => 1, 'max' => 50]],
            'adminEmail' => ['key' => 'adminemail', 'filter' => 'trim'],
            'expires' => [
                'date' => true,
                'formatter' => [
                    'dateTimeToJson' => [
                        'revert' => true,
                        'clearWithEmptyString' => true
                    ]
                ]
            ],
            'startDate' => [
                'key' => 'startdate',
                'date' => true,
                'formatter' => [
                    'dateTimeToJson' => [
                        'revert' => true,
                        'clearWithEmptyString' => true
                    ]
                ]
            ],
            'anonymized' => ['formatter' => ['ynToBool' => ['revert' => true]]],
            'saveTimings' => [
                'key' => 'savetimings',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            //'additionalLanguages' => 'additional_languages',
            'languages' => true,
            'datestamp' => [
                'filter' => 'trim',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'useCookie' => [
                'key' => 'usecookie',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'allowRegister' => [
                'key' => 'allowregister',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'htmlEmail' => [
                'key' => 'htmlemail',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'allowSave' => [
                'key' => 'allowsave',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'autoNumberStart' => [
                'key' => 'autonumber_start', 'type' => 'int', 'numerical'
            ],
            'autoRedirect' => [
                'key' => 'autoredirect',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'allowPrev' => [
                'key' => 'allowprev',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'printAnswers' => [
                'key' => 'printanswers',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'ipAddr' => [
                'key' => 'ipaddr',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'ipAnonymize' => [
                'key' => 'ipanonymize',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'refUrl' => [
                'key' => 'refurl',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'dateCreated' => [
                'key' => 'datecreated',
                'date',
                'formatter' => ['dateTimeToJson' => ['revert' => true]]
            ],
            'publicStatistics' => [
                'key' => 'publicstatistics',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'publicGraphs' => [
                'key' => 'publicgraphs',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'listPublic' => [
                'key' => 'listpublic',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'sendConfirmation' => [
                'key' => 'sendconfirmation',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'tokenAnswersPersistence' => [
                'key' => 'tokenanswerspersistence',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'assessments' => [
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'useCaptcha' => [
                'key' => 'usecaptcha',
                'range' => [
                    'A', 'B', 'C', 'D', 'X', 'R', 'S', 'N', 'E', 'F', 'G', 'H',
                    'I', 'J', 'K', 'L', 'M', 'O', 'P', 'T', 'U',
                    '1', '2', '3', '4', '5', '6'
                ]
            ],
            'useCaptchaAccess' => [
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'useCaptchaRegistration' => [
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'useCaptchaSaveLoad' => [
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'useTokens' => [
                'key' => 'usetokens',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'bounceEmail' => ['key' => 'bounce_email', 'filter' => 'trim'],
            'attributeDescriptions' => 'attributedescriptions',
            'emailResponseTo' => 'emailresponseto',
            'emailNotificationTo' => 'emailnotificationto',
            'tokenLength' => [
                'key' => 'tokenlength',
                'type' => 'int',
                'numerical' => ['min' => -1]
            ],
            'showXQuestions' => [
                'key' => 'showxquestions',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'showGroupInfo' => 'showgroupinfo',
            'showNoAnswer' => [
                'key' => 'shownoanswer',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'showQNumCode' => 'showqnumcode',
            'bounceTime' => [
                'key' => 'bouncetime', 'type' => 'int', 'numerical'
            ],
            'bounceProcessing' => [
                'key' => 'bounceprocessing', 'range' => ['L', 'N', 'G']
            ],
            'bounceAccountType' => 'bounceaccounttype',
            'bounceAccountHost' => 'bounceaccounthost',
            'bounceAccountPass' => 'bounceaccountpass',
            'bounceAccountEncryption' => 'bounceaccountencryption',
            'bounceAccountUser' => 'bounceaccountuser',
            'showWelcome' => [
                'key' => 'showwelcome',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'showProgress' => [
                'key' => 'showprogress',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'questionIndex' => [
                'key' => 'questionindex',
                'type' => 'int',
                'numerical' => ['min' => -1, 'max' => 2]
            ],
            'navigationDelay' => [
                'key' => 'navigationdelay', 'type' => 'int', 'numerical'
            ],
            'noKeyboard' => [
                'key' => 'nokeyboard',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'allowedItAfterCompletion' => [
                'key' => 'alloweditaftercompletion',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'googleAnalyticsStyle' => [
                'key' => 'googleanalyticsstyle',
                'type' => 'int',
                'numerical' => ['min' => 0, 'max' => 3]
            ],
            'googleAnalyticsApiKey' => [
                'key' => 'googleanalyticsapikey',
                'pattern' => '/^[a-zA-Z\-\d]*$/'
            ],
            'showSurveyPolicyNotice' => [
                'key' => 'showsurveypolicynotice',
                'type' => 'int',
                'range' => [0, 1, 2]
            ],
            'template' => true,
            'format' => ['range' => ['G', 'S', 'A', 'I']]
        ]);
    }

    public function transform($data, $options = [])
    {
        $survey = parent::transform($data);
        if (is_array($survey)) {
            if (array_key_exists('showgroupinfo', $survey)) {
                $survey['showgroupinfo'] = $this->convertShowGroupInfo(
                    $survey['showgroupinfo']
                );
            }
            if (array_key_exists('showqnumcode', $survey)) {
                $survey['showqnumcode'] = $this->convertShowQNumCode(
                    $survey['showqnumcode']
                );
            }
            $survey['additional_languages'] = $this->transformLanguages($survey);

            //useCaptcha
            $useCaptchaExists = (
                array_key_exists('useCaptchaAccess', $survey) ||
                array_key_exists('useCaptchaRegistration', $survey) ||
                array_key_exists('useCaptchaSaveLoad', $survey)
                );
            if ($useCaptchaExists && !empty($options)) {
                $survey['usecaptcha'] = $this->transformCaptcha(
                    $survey,
                    $options
                );
            }
        }
        return $survey;
    }

    /**
     * @param $survey
     * @return string
     */
    public function transformLanguages($survey){
        $index = array_search($survey->language, $survey->languages);
        unset($survey->languages[$index]); //exclude baselanguage
        //transform to string
        return implode(' ', $survey->languages);
    }

    /**
     * Converts incoming values for showGroupName and showGroupDescription
     * into a single value for the 'showgroupinfo' prop.
     * @param array $showGroupInfoValueArray
     * @return string
     */
    private function convertShowGroupInfo($showGroupInfoValueArray)
    {
        $showGroupName = $showGroupInfoValueArray['showGroupName'];
        $showGroupDescription = $showGroupInfoValueArray['showGroupDescription'];
        if ($showGroupName && $showGroupDescription) {
            $combinedValue = 'B';
        } elseif ($showGroupName) {  // implies $showGroupName is true and $showGroupDescription is false
            $combinedValue = 'N';
        } elseif ($showGroupDescription) {  // implies $showGroupName is false and $showGroupDescription is true
            $combinedValue = 'D';
        } else {
            $combinedValue = 'X';  // both are false
        }

        return $combinedValue;
    }

    /**
     * Converts incoming values for showNumber and showCode
     * into a single value for the 'showqnumcode' prop.
     * @param array $showQNumCodeValueArray
     * @return string
     */
    private function convertShowQNumCode($showQNumCodeValueArray)
    {
        $showNumber = $showQNumCodeValueArray['showNumber'];
        $showCode = $showQNumCodeValueArray['showCode'];
        if ($showNumber && $showCode) {
            $combinedValue = 'B';
        } elseif ($showNumber) {
            $combinedValue = 'N';
        } elseif ($showCode) {
            $combinedValue = 'C';
        } else {
            $combinedValue = 'X';
        }

        return $combinedValue;
    }

    /**
     * Transforms the three values for useCaptcha into one.
     *
     * @param array $mappedData
     * @param array $options
     * @return string
     */
    private function transformCaptcha($mappedData, $options)
    {
        $surveyUseCaptchaService = new SurveyUseCaptcha($options['surveyID'], null);

        return $surveyUseCaptchaService->reCalculateUseCaptcha($mappedData);
    }
}
