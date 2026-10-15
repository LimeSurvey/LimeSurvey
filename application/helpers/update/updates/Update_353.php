<?php

namespace LimeSurvey\Helpers\Update;

use TemplateConfiguration;

class Update_353 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
        $themes = TemplateConfiguration::model()->findAll();
        foreach ($themes as $theme) {
            $theme->addOptionFromXMLToLiveTheme();
        }
    }
}
