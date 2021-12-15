<?php

namespace LimeSurvey\Helpers\Update;

use TemplateConfiguration;

class Update_352 extends DatabaseUpdateBase
{
    public function up()
    {
        dropColumn('{{sessions}}', 'data');
        addColumn('{{sessions}}', 'data', 'binary');

        $themes = TemplateConfiguration::model()->findAll();
        foreach ($themes as $theme) {
            $theme->setGlobalOption("ajaxmode", "off");
        }
    }
}
