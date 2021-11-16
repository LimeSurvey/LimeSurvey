<?php

namespace LimeSurvey\Helpers\Update;

class Update_353 extends DatabaseUpdateBase
{
    public function run()
    {

            $aTHemes = TemplateConfiguration::model()->findAll();

        foreach ($aTHemes as $oTheme) {
            $oTheme->addOptionFromXMLToLiveTheme();
        }
    }
}
