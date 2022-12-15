<?php

namespace LimeSurvey\Api\Command\V2\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;



class TransformerOutputSurvey extends TransformerOutputActiveRecord
{
    public function __construct()
    {
        $this->setDataMap([
            'sid' => ['type' => 'int'],
            'gsid' => ['type' => 'int'],
            'active' => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            'language'  => true,
            'expires' => true,
            'startdate' => true,
            'anonymized' => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            'savetimings' => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            'additional_languages' => false,
            'datestamp' => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            "usecookie" => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            "allowregister" => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            "allowsave" => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            "autonumber_start" => ['type' => 'int'],
            "autoredirect" => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            "allowprev" => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            "printanswers" => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            "ipaddr" => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            "ipanonymize" => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            "refurl" => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            "datecreated" => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::dateTimeUtcToJson'],
            "publicstatistics" => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            "publicgraphs" => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            "listpublic" => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            "sendconfirmation" => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            "tokenanswerspersistence" => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            "assessments" => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            "usecaptcha" => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            "usetokens" => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            "bounce_email" => true,
            "attributedescriptions" => true,
            "emailresponseto" => true,
            "emailnotificationto" => true,
            "tokenlength" =>  ['type' => 'int'],
            "showxquestions" => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            "showgroupinfo" => true,
            "shownoanswer" => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            "showqnumcode" => true,
            "bouncetime" =>  ['type' => 'int'],
            "bounceprocessing" => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            "bounceaccounttype" => true,
            "bounceaccounthost" => true,
            "bounceaccountpass" => true,
            "bounceaccountencryption" => true,
            "bounceaccountuser" => true,
            "showwelcome" => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            "showprogress" => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            "questionindex" =>  ['type' => 'int'],
            "navigationdelay" =>  ['type' => 'int'],
            "nokeyboard" => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            "alloweditaftercompletion" => ['type' => 'LimeSurvey\Api\Transformer\TypeFormat::ynToBool'],
            "googleanalyticsstyle" =>  ['type' => 'int'],
            "googleanalyticsapikey" => true,
            "showsurveypolicynotice" =>  ['type' => 'int'],
        ]);
    }
}
