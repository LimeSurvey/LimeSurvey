<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Output;

use Survey;
use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;
use LimeSurvey\Api\Transformer\Formatter\FormatterYnToBool;
use LimeSurvey\Api\Transformer\Formatter\FormatterDateTimeToJson;

class TransformerOutputSurvey extends TransformerOutputActiveRecord
{
    private TransformerOutputSurveyLanguageSettings $transformerOutputSurveyLanguageSettings;

    /**
     *  @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function __construct(
        FormatterYnToBool $formatterYn,
        FormatterDateTimeToJson $formatterDateTime,
        TransformerOutputSurveyLanguageSettings $transformerOutputSurveyLanguageSettings
    ) {
        $this->transformerOutputSurveyLanguageSettings = $transformerOutputSurveyLanguageSettings;

        $this->setDataMap([
            'sid' => ['type' => 'int'],
            'gsid' => ['type' => 'int'],
            'active' => ['formatter' => $formatterYn],
            'language'  => true,
            'expires' => ['formatter' => $formatterDateTime],
            'startdate' => ['key' => 'startDate', 'formatter' => $formatterDateTime],
            'anonymized' => ['formatter' => $formatterYn],
            'savetimings' => ['key' => 'saveTimings', 'formatter' => $formatterYn],
            'additional_languages' => 'additionalLanguages',
            'datestamp' => ['formatter' => $formatterYn],
            "usecookie" => ['key' => 'useCookie', 'formatter' => $formatterYn],
            "allowregister" => ['key' => 'allowRegister', 'formatter' => $formatterYn],
            "allowsave" => ['key' => 'allowSave', 'formatter' => $formatterYn],
            "autonumber_start" => ['key' => 'autoNumberStart', 'type' => 'int'],
            "autoredirect" => ['key' => 'autoRedirect', 'formatter' => $formatterYn],
            "allowprev" => ['key' => 'allowPrev', 'formatter' => $formatterYn],
            "printanswers" => ['key' => 'printAnswers', 'formatter' => $formatterYn],
            "ipaddr" => ['key' => 'ipAddr', 'formatter' => $formatterYn],
            "ipanonymize" => ['key' => 'ipAnonymize', 'formatter' => $formatterYn],
            "refurl" => ['key' => 'refUrl', 'formatter' => $formatterYn],
            "datecreated" => ['key' => 'dateCreated', 'formatter' => $formatterDateTime],
            "publicstatistics" => ['key' => 'publicStatistics', 'formatter' => $formatterYn],
            "publicgraphs" => ['key' => 'publicGraphs', 'formatter' => $formatterYn],
            "listpublic" => ['key' => 'listPublic', 'formatter' => $formatterYn],
            "sendconfirmation" => ['key' => 'sendConfirmation', 'formatter' => $formatterYn],
            "tokenanswerspersistence" => ['key' => 'tokenAnswersPersistence', 'formatter' => $formatterYn],
            "assessments" => ['formatter' => $formatterYn],
            "usecaptcha" => ['key' => 'useCaptcha', 'formatter' => $formatterYn],
            "usetokens" => ['key' => 'useTokens', 'formatter' => $formatterYn],
            "bounce_email" => 'bounceEmail',
            "attributedescriptions" => 'attributeDescriptions',
            "emailresponseto" => 'emailResponseTo',
            "emailnotificationto" => 'emailNotificationTo',
            "tokenlength" => ['key' =>  'tokenLength', 'type' => 'int'],
            "showxquestions" => ['key' =>  'showXQuestions', 'formatter' => $formatterYn],
            "showgroupinfo" => 'showGroupInfo',
            "shownoanswer" => ['key' =>  'showNoAnswer', 'formatter' => $formatterYn],
            "showqnumcode" => 'showQNumCode',
            "bouncetime" => ['key' =>  'bounceTime', 'type' => 'int'],
            "bounceprocessing" => ['key' =>  'bounceProcessing', 'formatter' => $formatterYn],
            "bounceaccounttype" => 'bounceAccountType',
            "bounceaccounthost" => 'bounceAccountHost',
            "bounceaccountpass" => 'bounceAccountPass',
            "bounceaccountencryption" => ['key' =>  'bounceAccountEncryption'],
            "bounceaccountuser" => ['key' =>  'bounceAccountUser'],
            "showwelcome" => ['key' =>  'showWelcome', 'formatter' => $formatterYn],
            "showprogress" => ['key' =>  'showProgress', 'formatter' => $formatterYn],
            "questionindex" => ['key' =>  'questionIndex', 'type' => 'int'],
            "navigationdelay" => ['key' =>  'navigationDelay', 'type' => 'int'],
            "nokeyboard" => ['key' =>  'noKeyboard', 'formatter' => $formatterYn],
            "alloweditaftercompletion" => ['key' =>  'allowedItAfterCompletion', 'formatter' => $formatterYn],
            "googleanalyticsstyle" => ['key' =>  'googleAnalyticsStyle', 'type' => 'int'],
            "googleanalyticsapikey" => 'googleAnalyticsApiKey',
            "showsurveypolicynotice" => ['key' =>  'showSurveyPolicyNotice', 'type' => 'int'],
        ]);
    }

    public function transform($data)
    {
        $survey = null;
        if (!$data instanceof Survey) {
            return null;
        }
        $survey = parent::transform($data);
        $survey['defaultLanguage'] = $this->transformerOutputSurveyLanguageSettings->transform(
            $data->defaultlanguage
        );
        $survey['languageSettings'] = $this->transformerOutputSurveyLanguageSettings->transformAll(
            $data->languagesettings
        );
        return $survey;
    }
}
