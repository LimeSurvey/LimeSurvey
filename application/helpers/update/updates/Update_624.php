<?php

namespace LimeSurvey\Helpers\Update;

use LimeSurvey\Helpers\Update\DatabaseUpdateBase;

class Update_624 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    #[\Override]
    public function up()
    {
        $createSurveyBox = \Box::model()->find("title=:title", array("title" => 'Create survey'));
        if (!empty($createSurveyBox)) {
            $createSurveyBox->delete();
        }
    }
}
