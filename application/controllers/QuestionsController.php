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
            $this->question->setAttributes(App()->request->getParam(\CHtml::modelName($this->question)));
            if ($this->question->save()) {
                App()->user->setFlash('success', "Question updated.");
            } else {
                App()->user->setFlash('error', "Question could not be updated.");
            }
        }

        $this->render('update', ['question' => $this->question, 'post' => $_POST, 'questionnames' => $this->question->translations]);
    }

    /**
     * @param int $id
     * @return \Question
     */
    protected function loadModel($id) {
        return \Question::model()->findByAttributes([
            'qid' => $id,
            'language' => App()->language
            
        ]);
    }
}

