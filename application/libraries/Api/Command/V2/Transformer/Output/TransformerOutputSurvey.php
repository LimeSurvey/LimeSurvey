<?php

namespace LimeSurvey\Api\Command\V2\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

class TransformerOutputSurvey extends TransformerOutputActiveRecord
{
    public function __construct()
    {
        $ynToBool = function($value) {
            return strtolower($value) === 'y';
        };

        $dateTimeUtcJson = function ($value) {
            return date_format(date_create($value), 'Y-m-d\TH:i:s.000\Z');
        };

        $this->setDataMap([
            'sid' => ['type' => 'int'],
            'gsid' => ['type' => 'int'],
            'active' => ['type' => $ynToBool],
            'language'  => true,
            'expires' => true,
            'startdate' => true,
            'anonymized' => ['type' => $ynToBool],
            'savetimings' => ['type' => $ynToBool],
            'additional_languages' => false,
            'datestamp' => ['type' => $ynToBool],
            "usecookie" => ['type' => $ynToBool],
            "allowregister" => ['type' => $ynToBool],
            "allowsave" => ['type' => $ynToBool],
            "autonumber_start" => ['type' => 'int'],
            "autoredirect" => ['type' => $ynToBool],
            "allowprev" => ['type' => $ynToBool],
            "printanswers" => ['type' => $ynToBool],
            "ipaddr" => ['type' => $ynToBool],
            "ipanonymize" => ['type' => $ynToBool],
            "refurl" => ['type' => $ynToBool],
            "datecreated" => ['type' => $dateTimeUtcJson],
            "publicstatistics" => ['type' => $ynToBool],
            "publicgraphs" => ['type' => $ynToBool],
            "listpublic" => ['type' => $ynToBool],
            "sendconfirmation" => ['type' => $ynToBool],
            "tokenanswerspersistence" => ['type' => $ynToBool],
            "assessments" => ['type' => $ynToBool],
            "usecaptcha" => ['type' => $ynToBool],
            "usetokens" => ['type' => $ynToBool],
            "bounce_email" => true,
            "attributedescriptions" => true,
            "emailresponseto" => true,
            "emailnotificationto" => true,
            "tokenlength" =>  ['type' => 'int'],
            "showxquestions" => ['type' => $ynToBool],
            "showgroupinfo" => true,
            "shownoanswer" => ['type' => $ynToBool],
            "showqnumcode" => true,
            "bouncetime" =>  ['type' => 'int'],
            "bounceprocessing" => ['type' => $ynToBool],
            "bounceaccounttype" => true,
            "bounceaccounthost" => true,
            "bounceaccountpass" => true,
            "bounceaccountencryption" => true,
            "bounceaccountuser" => true,
            "showwelcome" => ['type' => $ynToBool],
            "showprogress" => ['type' => $ynToBool],
            "questionindex" =>  ['type' => 'int'],
            "navigationdelay" =>  ['type' => 'int'],
            "nokeyboard" => ['type' => $ynToBool],
            "alloweditaftercompletion" => ['type' => $ynToBool],
            "googleanalyticsstyle" =>  ['type' => 'int'],
            "googleanalyticsapikey" => true,
            "showsurveypolicynotice" =>  ['type' => 'int'],
        ]);
    }
}
