<?php

namespace LimeSurvey\Api\Command\V2\Transformer\Output;

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
            'startdate' => ['type' => $typeDateTimeToUtcJson],
            'anonymized' => ['type' => $typeYnToBool],
            'savetimings' => ['type' => $typeYnToBool],
            'additional_languages' => false,
            'datestamp' => ['type' => $typeYnToBool],
            "usecookie" => ['type' => $typeYnToBool],
            "allowregister" => ['type' => $typeYnToBool],
            "allowsave" => ['type' => $typeYnToBool],
            "autonumber_start" => ['type' => 'int'],
            "autoredirect" => ['type' => $typeYnToBool],
            "allowprev" => ['type' => $typeYnToBool],
            "printanswers" => ['type' => $typeYnToBool],
            "ipaddr" => ['type' => $typeYnToBool],
            "ipanonymize" => ['type' => $typeYnToBool],
            "refurl" => ['type' => $typeYnToBool],
            "datecreated" => ['type' => $typeDateTimeToUtcJson],
            "publicstatistics" => ['type' => $typeYnToBool],
            "publicgraphs" => ['type' => $typeYnToBool],
            "listpublic" => ['type' => $typeYnToBool],
            "sendconfirmation" => ['type' => $typeYnToBool],
            "tokenanswerspersistence" => ['type' => $typeYnToBool],
            "assessments" => ['type' => $typeYnToBool],
            "usecaptcha" => ['type' => $typeYnToBool],
            "usetokens" => ['type' => $typeYnToBool],
            "bounce_email" => true,
            "attributedescriptions" => true,
            "emailresponseto" => true,
            "emailnotificationto" => true,
            "tokenlength" =>  ['type' => 'int'],
            "showxquestions" => ['type' => $typeYnToBool],
            "showgroupinfo" => true,
            "shownoanswer" => ['type' => $typeYnToBool],
            "showqnumcode" => true,
            "bouncetime" =>  ['type' => 'int'],
            "bounceprocessing" => ['type' => $typeYnToBool],
            "bounceaccounttype" => true,
            "bounceaccounthost" => true,
            "bounceaccountpass" => true,
            "bounceaccountencryption" => true,
            "bounceaccountuser" => true,
            "showwelcome" => ['type' => $typeYnToBool],
            "showprogress" => ['type' => $typeYnToBool],
            "questionindex" =>  ['type' => 'int'],
            "navigationdelay" =>  ['type' => 'int'],
            "nokeyboard" => ['type' => $typeYnToBool],
            "alloweditaftercompletion" => ['type' => $typeYnToBool],
            "googleanalyticsstyle" =>  ['type' => 'int'],
            "googleanalyticsapikey" => true,
            "showsurveypolicynotice" =>  ['type' => 'int'],
        ]);
    }

    public function transform($surveyModel)
    {
        $survey = parent::transform($surveyModel);
        $survey['defaultlanguage'] = (new TransformerOutputSurveyLanguageSettings())->transform(
            $surveyModel->defaultlanguage
        );
        $survey['languagesettings'] = (new TransformerOutputSurveyLanguageSettings())->transformAll(
            $surveyModel->languagesettings
        );
        return $survey;
    }
}
