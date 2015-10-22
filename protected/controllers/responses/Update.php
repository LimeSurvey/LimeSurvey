<?php


namespace ls\controllers\responses;

use ls\models\Response;
use ls\models\Survey;

/**
 * This action creates a survey session for an existing response and then redirects to the survey runner.
 * @package ls\controllers\responses
 */
class Update extends \Action
{

    public function run($id, $surveyId)
    {
        /** @var \ls\models\Survey $survey */
        $survey = Survey::model()->findByPk($surveyId);
        if (!$survey->isActive) {
            throw new \CHttpException(412, gT("The survey is not active."));
        } elseif ($survey->bool_usetokens
            && null === $response = Response::model($surveyId)->findByPk($id)
        ) {
            throw new \CHttpException(404, gT("Response not found."));
        }

        $this->redirect(['surveys/run', 'SSM' => App()->surveySessionManager->newSession($survey->primaryKey, $response)->getId()]);
    }
}