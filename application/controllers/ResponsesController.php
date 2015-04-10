<?php
namespace ls\controllers;
use Survey;
/**
 * This class will handle survey creation and manipulation.
 */
class ResponsesController extends Controller
{
    public $layout = 'survey';

    public function actionIndex($id) {
        /**
         * @todo Add permission check.
         */
        $survey = Survey::model()->findByPk($id);
        $this->survey = $survey;

        $dataProvider = new \CActiveDataProvider(\Response::model($id), [
            'pagination' => [
                'pageSize' => 50
            ]
        ]);
        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    public function actionDelete($id, $surveyId) {
        // CSRF is enabled.
        // We allow POST and DELETE requests (since TbButtonColumn uses POST by default).
        /**
         * @todo Add permission check.
         */
        if (App()->request->isPostRequest || App()->request->isDeleteRequest) {
            return \Response::model($surveyId)->deleteByPk($id);
        }
    }

    public function actionView($id, $surveyId)
    {
        $response = \Response::model($surveyId)->findByPk($id);
        $this->survey = $response->survey;
        return $this->render('view', [
            'response' => $response
        ]);
    }

    /**
     * This function appends a new response to the series of the response id given.
     * If the current series_id is set to null it's initialized to 0.
     *
     * @param int $surveyId
     * @param string $id
     * @param bool $copy
     */
    public function actionAppend($surveyId, $id, $copy = false)
    {
        $response = \Response::model($surveyId)->findByPk($id);
        $newResponse = $response->append($copy);
        $newResponse->save();
        $this->redirect(['responses/index', 'id' => $surveyId]);
    }
}