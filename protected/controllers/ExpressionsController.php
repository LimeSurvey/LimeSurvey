<?php
namespace ls\controllers;


class ExpressionsController extends Controller
{
    public function actionBuild($surveyId) {
        $survey = $this->loadModel($surveyId);
        $this->render('build', ['survey' => $survey]);
    }

    /**
     * Base implementation for load model.
     * Should be overwritten if the model for the controller is not standard or
     * has no single PK.
     * @param int $id
     * @return \ls\models\Survey
     * @throws \CHttpException
     */
    public function loadModel($id) {
        // Get the model name.
        $model = \ls\models\Survey::model()->findByPk($id);
        if (!isset($model)) {
            throw new \CHttpException(404, "ls\models\Survey not found.");
        }
        return $model;
    }
}