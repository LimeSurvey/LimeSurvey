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

        $this->setDataMap([
            'sid' => ['type' => 'int'],
            'gsid' => ['type' => 'int'],
            'active' => ['type' => $ynToBool],
            'language'  => true,
            'expires' => true,
            'startdate' => true,
            'anonymized' => ['type' => $ynToBool],
            'savetimings' => ['type' => $ynToBool],
            'additional_languages' => true,
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
            "datecreated" => true,
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
            "tokenlength" => true,
            "showxquestions" => ['type' => $ynToBool],
            "showgroupinfo" => true,
            "shownoanswer" => ['type' => $ynToBool],
            "showqnumcode" => true,
            "bouncetime" => true,
            "bounceprocessing" => ['type' => $ynToBool],
            "bounceaccounttype" => true,
            "bounceaccounthost" => true,
            "bounceaccountpass" => true,
            "bounceaccountencryption" => true,
            "bounceaccountuser" => true,
            "showwelcome" => ['type' => $ynToBool],
            "showprogress" => ['type' => $ynToBool],
            "questionindex" => true,
            "navigationdelay" => true,
            "nokeyboard" => ['type' => $ynToBool],
            "alloweditaftercompletion" => ['type' => $ynToBool],
            "googleanalyticsstyle" => true,
            "googleanalyticsapikey" => true,
            "showsurveypolicynotice" => true,
        ]);
    }
}
