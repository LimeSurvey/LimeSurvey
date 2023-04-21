<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

class TransformerOutputSurvey extends TransformerOutputActiveRecord
{
    public function __construct()
    {
        $typeYnToBool = 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool';
        $typeDateTimeToUtcJson = 'LimeSurvey\Api\Transformer\TypeFormat::dateTimeToJson';

        $this->setDataMap([
            'sid' => ['type' => 'int'],
            'gsid' => ['type' => 'int'],
            'active' => ['type' => $typeYnToBool],
            'language'  => true,
            'expires' => ['type' => $typeDateTimeToUtcJson],
            'startdate' => ['key' => 'startDate', 'type' => $typeDateTimeToUtcJson],
            'anonymized' => ['type' => $typeYnToBool],
            'savetimings' => ['key' => 'saveTimings', 'type' => $typeYnToBool],
            'additional_languages' => 'additionalLanguages',
            'datestamp' => ['type' => $typeYnToBool],
            "usecookie" => ['key' => 'useCookie', 'type' => $typeYnToBool],
            "allowregister" => ['key' => 'allowRegister', 'type' => $typeYnToBool],
            "allowsave" => ['key' => 'allowSave', 'type' => $typeYnToBool],
            "autonumber_start" => ['key' => 'autonumberStart', 'type' => 'int'],
            "autoredirect" => ['key' => 'autoRedirect', 'type' => $typeYnToBool],
            "allowprev" => ['key' => 'allowPrev', 'type' => $typeYnToBool],
            "printanswers" => ['key' => 'printAnswers', 'type' => $typeYnToBool],
            "ipaddr" => ['key' => 'ipAddr', 'type' => $typeYnToBool],
            "ipanonymize" => ['key' => 'ipAnonymize', 'type' => $typeYnToBool],
            "refurl" => ['key' => 'refUrl', 'type' => $typeYnToBool],
            "datecreated" => ['key' => 'dateCreated', 'type' => $typeDateTimeToUtcJson],
            "publicstatistics" => ['key' => 'publicStatistics', 'type' => $typeYnToBool],
            "publicgraphs" => ['key' => 'publicGraphs', 'type' => $typeYnToBool],
            "listpublic" => ['key' => 'listPublic', 'type' => $typeYnToBool],
            "sendconfirmation" => ['key' => 'sendConfirmation', 'type' => $typeYnToBool],
            "tokenanswerspersistence" => ['key' => 'tokenAnswersPersistence', 'type' => $typeYnToBool],
            "assessments" => ['type' => $typeYnToBool],
            "usecaptcha" => ['key' => 'useCaptcha', 'type' => $typeYnToBool],
            "usetokens" => ['key' => 'useTokens', 'type' => $typeYnToBool],
            "bounce_email" => 'bounceEmail',
            "attributedescriptions" => 'attributeDescriptions',
            "emailresponseto" => 'emailResponseTo',
            "emailnotificationto" => 'emailNotificationTo',
            "tokenlength" => ['key' =>  'tokenLength', 'type' => 'int'],
            "showxquestions" => ['key' =>  'showXQuestions', 'type' => $typeYnToBool],
            "showgroupinfo" => 'showGroupInfo',
            "shownoanswer" => ['key' =>  'showNoAnswer', 'type' => $typeYnToBool],
            "showqnumcode" => 'showQNumCode',
            "bouncetime" => ['key' =>  'bounceTime', 'type' => 'int'],
            "bounceprocessing" => ['key' =>  'bounceProcessing', 'type' => $typeYnToBool],
            "bounceaccounttype" => 'bounceAccountType',
            "bounceaccounthost" => 'bounceAccountHost',
            "bounceaccountpass" => 'bounceAccountPass',
            "bounceaccountencryption" => ['key' =>  'bounceAccountEncryption'],
            "bounceaccountuser" => ['key' =>  'bounceAccountUser'],
            "showwelcome" => ['key' =>  'showWelcome', 'type' => $typeYnToBool],
            "showprogress" => ['key' =>  'showProgress', 'type' => $typeYnToBool],
            "questionindex" => ['key' =>  'questionIndex', 'type' => 'int'],
            "navigationdelay" => ['key' =>  'navigationDelay', 'type' => 'int'],
            "nokeyboard" => ['key' =>  'noKeyboard', 'type' => $typeYnToBool],
            "alloweditaftercompletion" => ['key' =>  'allowedItAfterCompletion', 'type' => $typeYnToBool],
            "googleanalyticsstyle" => ['key' =>  'googleAnalyticsStyle', 'type' => 'int'],
            "googleanalyticsapikey" => 'googleAnalyticsApiKey',
            "showsurveypolicynotice" => ['key' =>  'showSurveyPolicyNotice', 'type' => 'int'],
        ]);
    }

    public function transform($surveyModel)
    {
        $transformer = new TransformerOutputSurveyLanguageSettings();
        $survey = parent::transform($surveyModel);
        $survey['defaultLanguage'] = $transformer->transform(
            $surveyModel->defaultlanguage
        );
        $survey['languageSettings'] = $transformer->transformAll(
            $surveyModel->languagesettings
        );
        return $survey;
    }
}
