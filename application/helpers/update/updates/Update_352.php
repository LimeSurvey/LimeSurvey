<?php

namespace LimeSurvey\Helpers\Update;

class Update_352 extends DatabaseUpdateBase
{
    public function run()
    {
            dropColumn('{{sessions}}', 'data');
            addColumn('{{sessions}}', 'data', 'binary');

            $aTHemes = TemplateConfiguration::model()->findAll();

            foreach ($aTHemes as $oTheme) {
                $oTheme->setGlobalOption("ajaxmode", "off");
            }

    }
}