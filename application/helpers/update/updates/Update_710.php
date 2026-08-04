<?php

namespace LimeSurvey\Helpers\Update;

use TemplateConfiguration;

class Update_710 extends DatabaseUpdateBase
{
    /**
     * Add the 'deselectsinglechoice' option (introduced in fruity_twentythree config.xml)
     * to all existing fruity_twentythree TemplateConfiguration DB records that are missing it.
     */
    public function up()
    {
        $themes = TemplateConfiguration::model()->findAllByAttributes([
            'template_name' => 'fruity_twentythree',
        ]);
        foreach ($themes as $theme) {
            $theme->addOptionFromXMLToLiveTheme();
        }
    }
}
