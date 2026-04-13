<?php

namespace LimeSurvey\Helpers\Update;

use TemplateConfiguration;

class Update_649 extends DatabaseUpdateBase
{
    public function up()
    {
        // Remove deprecated ajaxmode from all template configurations
        // (whether from old migrations or fresh installs)
        $themes = TemplateConfiguration::model()->findAll();
        foreach ($themes as $theme) {
            if (!empty($theme->options) && $theme->options !== 'inherit') {
                $optionsArray = json_decode($theme->options, true);
                if (is_array($optionsArray) && isset($optionsArray['ajaxmode'])) {
                    unset($optionsArray['ajaxmode']);
                    $theme->options = json_encode($optionsArray);
                    $theme->save();
                }
            }
        }
    }
}
