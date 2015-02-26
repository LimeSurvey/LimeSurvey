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
    
    protected function loadModel($id) {
        return \Question::model()->findByAttributes([
            'qid' => $id,
            'language' => App()->language
            
        ]);
    }
}

