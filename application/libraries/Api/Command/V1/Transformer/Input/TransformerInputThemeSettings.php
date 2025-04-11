<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputThemeSettings extends Transformer
{
    public function __construct()
    {
        $this->setDataMap([
            'templateName' => [
                'key' => 'templateName',
                'type' => 'string',
                'required'
            ],
            'font' => true,
            'cssframework' => true,
            'backgroundimagefile' => true,
            'brandlogofile' => true,
            'hideprivacyinfo' => true,
            'showpopups' => true,
            'showclearall' => true,
            'questionhelptextposition' => true,
            'fixnumauto' => true,
            'backgroundimage' => true,
            'brandlogo' => true,
            ],
        );
    }
}
