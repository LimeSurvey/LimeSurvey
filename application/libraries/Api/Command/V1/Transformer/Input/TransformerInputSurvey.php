<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\{
    Transformer,
    Formatter\FormatterYnToBool,
    Formatter\FormatterDateTimeToJson};

class TransformerInputSurvey extends Transformer
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function __construct()
    {
        $formatterYn = new FormatterYnToBool(true);
        $formatterDateTime = new FormatterDateTimeToJson(true);

        $this->setDataMap([
            'sid' => ['type' => 'int'],
            'gsid' => ['type' => 'int'],
            'ownerId' => ['key' => 'owner_id', 'type' => 'int'],
            'active' => ['formatter' => $formatterYn, 'range' => [true, false]],
            'language' => true,
            'admin' => ['length' => ['min' => 1, 'max' => 50]],
            'adminEmail' => ['key' => 'adminemail'],
            'expires' => ['key' => 'expires', 'date' => true],
            'startDate' => ['key' => 'startdate', 'date' => true],
            'anonymized' => ['formatter' => $formatterYn],
            'saveTimings' => ['key' => 'savetimings', 'formatter' => $formatterYn],
            'additionalLanguages' => 'additional_languages',
            'dateStamp' => ['key' => 'datestamp', 'formatter' => $formatterYn],
            'useCookie' => ['key' => 'usecookie', 'formatter' => $formatterYn],
            'allowRegister' => ['key' => 'allowregister', 'formatter' => $formatterYn],
            'allowSave' => ['key' => 'allowsave', 'formatter' => $formatterYn],
            'autoNumberStart' => ['key' => 'autonumber_start', 'type' => 'int', 'numerical' => true],
            'autoRedirect' => ['key' => 'autoredirect', 'formatter' => $formatterYn],
            'allowPrev' => ['key' => 'allowprev', 'formatter' => $formatterYn],
            'printAnswers' => ['key' => 'printanswers', 'formatter' => $formatterYn],
            'ipAddr' => ['key' => 'ipaddr', 'formatter' => $formatterYn],
            'ipAnonymize' => ['key' => 'ipanonymize', 'formatter' => $formatterYn],
            'refUrl' => ['key' => 'refurl', 'formatter' => $formatterYn],
            'dateCreated' => ['key' => 'datecreated', 'date' => true, 'formatter' => $formatterDateTime],
            'publicStatistics' => ['key' => 'publicstatistics', 'formatter' => $formatterYn],
            'publicGraphs' => ['key' => 'publicgraphs', 'formatter' => $formatterYn],
            'listPublic' => ['key' => 'listpublic', 'formatter' => $formatterYn],
            'sendConfirmation' => ['key' => 'sendconfirmation', 'formatter' => $formatterYn],
            'tokenAnswersPersistence' => ['key' => 'tokenanswerspersistence', 'formatter' => $formatterYn],
            'assessments' => ['formatter' => $formatterYn],
            'useCaptcha' => [
                'key' => 'usecaptcha',
                'range' => [
                    'A', 'B', 'C', 'D', 'X', 'R', 'S', 'N', 'E', 'F', 'G', 'H',
                    'I', 'J', 'K', 'L', 'M', 'O', 'P', 'T', 'U',
                    '1', '2', '3', '4', '5', '6'
                ]
            ],
            'useTokens' => ['key' => 'usetokens', 'formatter' => $formatterYn],
            'bounceEmail' => 'bounce_email',
            'attributeDescriptions' => 'attributedescriptions',
            'emailResponseTo' => 'emailresponseto',
            'emailNotificationTo' => 'emailnotificationto',
            'tokenLength' => ['key' => 'tokenlength', 'type' => 'int', 'numerical' => ['min' => -1]],
            'showXQuestions' => ['key' => 'showxquestions', 'formatter' => $formatterYn],
            'showGroupInfo' => ['key' => 'showgroupinfo', 'range' => ['B', 'N', 'D', 'X', 'I']],
            'showNoAnswer' => ['key' => 'shownoanswer', 'formatter' => $formatterYn],
            'showQNumCode' => ['key' => 'showqnumcode', 'range' => ['B', 'N', 'C', 'X', 'I']],
            'bounceTime' => ['key' => 'bouncetime', 'type' => 'int', 'numerical' => true],
            'bounceProcessing' => ['key' => 'bounceprocessing', 'range' => ['L', 'N', 'G']],
            'bounceAccountType' => 'bounceaccounttype',
            'bounceAccountHost' => 'bounceaccounthost',
            'bounceAccountPass' => 'bounceaccountpass',
            'bounceAccountEncryption' => 'bounceaccountencryption',
            'bounceAccountUser' => 'bounceaccountuser',
            'showWelcome' => ['key' => 'showwelcome', 'formatter' => $formatterYn],
            'showProgress' => ['key' => 'showprogress', 'formatter' => $formatterYn],
            'questionIndex' => ['key' => 'questionindex', 'type' => 'int', 'numerical' => ['min' => -1, 'max' => 2]],
            'navigationDelay' => ['key' => 'navigationdelay', 'type' => 'int', 'numerical' => true],
            'noKeyboard' => ['key' => 'nokeyboard', 'formatter' => $formatterYn],
            'allowedItAfterCompletion' => ['key' => 'alloweditaftercompletion', 'range' => $formatterYn],
            'googleAnalyticsStyle' => ['key' => 'googleanalyticsstyle', 'type' => 'int', 'numerical' => ['min' => 0, 'max' => 3]],
            'googleAnalyticsApiKey' => ['key' => 'googleanalyticsapikey', 'pattern' => '/^[a-zA-Z\-\d]*$/'],
            'showSurveyPolicyNotice' => [
                'key' => 'showsurveypolicynotice', 'type' => 'int', 'range' => [0, 1, 2]
            ],
            'template' => true,
            'format' => ['range' => ['G', 'S', 'A', 'I']]
        ]);
    }
}
