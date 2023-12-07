<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\{
    Transformer,
    Formatter\FormatterYnToBool,
    Formatter\FormatterDateTimeToJson
};

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
            'active' => ['formatter' => $formatterYn],
            'language' => true,
            'expires' => ['formatter' => $formatterDateTime],
            'startDate' => ['key' => 'startdate', 'formatter' => $formatterDateTime],
            'anonymized' => ['formatter' => $formatterYn],
            'saveTimings' => ['key' => 'savetimings', 'formatter' => $formatterYn],
            'additionalLanguages' => 'additional_languages',
            'dateStamp' => ['key' => 'datestamp', 'formatter' => $formatterYn],
            'useCookie' => ['key' => 'usecookie', 'formatter' => $formatterYn],
            'allowRegister' => ['key' => 'allowregister', 'formatter' => $formatterYn],
            'allowSave' => ['key' => 'allowsave', 'formatter' => $formatterYn],
            'autoNumberStart' => ['key' => 'autonumber_start', 'type' => 'int'],
            'autoRedirect' => ['key' => 'autoredirect', 'formatter' => $formatterYn],
            'allowPrev' => ['key' => 'allowprev', 'formatter' => $formatterYn],
            'printAnswers' => ['key' => 'printanswers', 'formatter' => $formatterYn],
            'ipAddr' => ['key' => 'ipaddr', 'formatter' => $formatterYn],
            'ipAnonymize' => ['key' => 'ipanonymize', 'formatter' => $formatterYn],
            'refUrl' => ['key' => 'refurl', 'formatter' => $formatterYn],
            'dateCreated' => ['key' => 'datecreated', 'formatter' => $formatterDateTime],
            'publicStatistics' => ['key' => 'publicstatistics', 'formatter' => $formatterYn],
            'publicGraphs' => ['key' => 'publicgraphs', 'formatter' => $formatterYn],
            'listPublic' => ['key' => 'listpublic', 'formatter' => $formatterYn],
            'sendConfirmation' => ['key' => 'sendconfirmation', 'formatter' => $formatterYn],
            'tokenAnswersPersistence' => ['key' => 'tokenanswerspersistence', 'formatter' => $formatterYn],
            'assessments' => ['formatter' => $formatterYn],
            'useCaptcha' => ['key' => 'usecaptcha', 'formatter' => $formatterYn],
            'useTokens' => ['key' => 'usetokens', 'formatter' => $formatterYn],
            'bounceEmail' => 'bounce_email',
            'attributeDescriptions' => 'attributedescriptions',
            'emailResponseTo' => 'emailresponseto',
            'emailNotificationTo' => 'emailnotificationto',
            'tokenLength' => ['key' => 'tokenlength', 'type' => 'int'],
            'showXQuestions' => ['key' => 'showxquestions', 'formatter' => $formatterYn],
            'showGroupInfo' => 'showgroupinfo',
            'showNoAnswer' => ['key' => 'shownoanswer', 'formatter' => $formatterYn],
            'showQNumCode' => 'showqnumcode',
            'bounceTime' => ['key' => 'bouncetime', 'type' => 'int'],
            'bounceProcessing' => ['key' => 'bounceprocessing', 'formatter' => $formatterYn],
            'bounceAccountType' => 'bounceaccounttype',
            'bounceAccountHost' => 'bounceaccounthost',
            'bounceAccountPass' => 'bounceaccountpass',
            'bounceAccountEncryption' => 'bounceaccountencryption',
            'bounceAccountUser' => 'bounceaccountuser',
            'showWelcome' => ['key' => 'showwelcome', 'formatter' => $formatterYn],
            'showProgress' => ['key' => 'showprogress', 'formatter' => $formatterYn],
            'questionIndex' => ['key' => 'questionindex', 'type' => 'int'],
            'navigationDelay' => ['key' => 'navigationdelay', 'type' => 'int'],
            'noKeyboard' => ['key' => 'nokeyboard', 'formatter' => $formatterYn],
            'allowedItAfterCompletion' => ['key' => 'alloweditaftercompletion', 'formatter' => $formatterYn],
            'googleAnalyticsStyle' => ['key' => 'googleanalyticsstyle', 'type' => 'int'],
            'googleAnalyticsApiKey' => 'googleanalyticsapikey',
            'showSurveyPolicyNotice' => ['key' => 'showsurveypolicynotice', 'type' => 'int'],
            'template' => true,
            'format' => true
        ]);
    }
}
