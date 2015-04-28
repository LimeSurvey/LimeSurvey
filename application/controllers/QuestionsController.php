<?php
namespace ls\controllers;
use \Yii;
class QuestionsController extends Controller 
{
    public $layout = 'survey';
    public function actionView($id) {
        $this->question = $this->loadModel($id);
        $this->survey = $this->question->survey;
        $this->group = $this->question->group;
        
        $this->render('view', ['question' => $this->question]);
    }

    public function actionUpdate($id) {
        $this->question = $this->loadModel($id);
        $this->survey = $this->question->survey;
        $this->group = $this->question->group;
        if (App()->request->isPutRequest) {
            // Update the question from data.
            App()->user->setFlash('error', "Saving not implemented.");
        }
        $this->render('update', ['question' => $this->question]);
    }
    protected function loadModel($id) {
        return \Question::model()->findByAttributes([
            'qid' => $id,
            'language' => App()->language
            
        ]);
    }
}

