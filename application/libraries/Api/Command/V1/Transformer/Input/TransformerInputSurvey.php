<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

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
                'formatter' => ['dateTimeToJson' => ['revert' => true]]
            ],
            'startDate' => [
                'key' => 'startdate',
                'date' => true,
                'formatter' => ['dateTimeToJson' => ['revert' => true]]
            ],
            'anonymized' => ['formatter' => ['ynToBool' => ['revert' => true]]],
            'saveTimings' => [
                'key' => 'savetimings',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'additionalLanguages' => 'additional_languages',
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
            'useCaptchaAccess' => null,
            'useCaptchaRegistration' => null,
            'useCaptchaSaveLoad' => null,
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
            'showGroupInfo' => [
                'key' => 'showgroupinfo', 'range' => ['B', 'N', 'D', 'X', 'I']
            ],
            'showNoAnswer' => [
                'key' => 'shownoanswer',
                'formatter' => ['ynToBool' => ['revert' => true]]
            ],
            'showQNumCode' => [
                'key' => 'showqnumcode', 'range' => ['B', 'N', 'C', 'X', 'I']
            ],
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
                'formatter' => ['dateTimeToJson' => ['revert' => true]]
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

    public function transform($data, $options = []){
        $valueBack = parent::transform($data, $options);

        //useCaptcha
        $valueBack['usecaptcha'] = $this->transformCaptcha($valueBack);
        unset($valueBack['usecaptchaAccess']);

        return $valueBack;
    }

    private function transformCaptcha($mappedData){
        //way back to hell
        $useCaptcha = 'N';

        //check if one of useCaptcha values has changed
        var_dump($mappedData);

        return $useCaptcha;
    }
}
