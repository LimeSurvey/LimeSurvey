<?php


namespace ls\controllers\surveys;

use ls\models\DummyResponse;
use \Yii;
use \CClientScript;

/**
 * Class Run, runs a survey.
 * @package ls\controllers\surveys
 */
class Preview extends \Action
{
    /**
     * Runs the action,
     * @throws \CHttpException
     * @param string $csrfToken
     */
    public function run($id)
    {
        $survey = $this->loadModel($id);
        $dummy = new DummyResponse($survey);
        $session = App()->surveySessionManager->newSession($survey->primaryKey, $dummy);
        return $this->redirect(['surveys/run', 'SSM' => $session->getId()]);
    }

}