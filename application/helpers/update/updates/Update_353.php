<?php

namespace LimeSurvey\Helpers\Update;

use TemplateConfiguration;

class Update_353 extends DatabaseUpdateBase
{
    public function up()
    {
        $themes = TemplateConfiguration::model()->findAll();
        foreach ($themes as $theme) {
            $theme->addOptionFromXMLToLiveTheme();
        }
    }
}
