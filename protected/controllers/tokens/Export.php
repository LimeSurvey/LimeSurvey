<?php


namespace ls\controllers\tokens;

use ls\models\Response;
use ls\models\Survey;

/**
 * This action exports tokens to a CSV.
 * @package ls\controllers\responses
 */
class Export extends \Action
{

    public function run($surveyId)
    {
        $survey = \ls\models\Survey::model()->findByPk($surveyId);
        $this->controller->menus['survey'] = $survey;
        $this->render('export');
    }
}