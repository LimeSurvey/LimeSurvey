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

    public function actionCreate($groupId) {
        /**
         * @todo Switch to findByPk after language has been removed from group table.
         */
        $group = \QuestionGroup::model()->findByAttributes(['gid' => $groupId]);
        $this->survey = $group->survey;
        if (!isset($this->survey)) {
            throw new \CHttpException(404, "Survey not found.");
        } elseif ($this->survey->isActive) {
            throw new \CHttpException(421, "Cannot add questions to active survey.");
        }
        $question = new \Question();
        $question->sid = $group->sid;
        $question->gid = $group->gid;
        if (App()->request->isPostRequest) {
            $question->setAttributes(App()->request->getPost('Question'));
            if ($question->save()) {
                $this->redirect(['questions/update', 'id' => $question->primaryKey]);
            }
        } else {
            $lastTitle = array_values($question->survey->questions)[count($question->survey->questions) - 1]->title;
            if (isset($lastTitle) && preg_match('/^(.*?)(\d+)$/', $lastTitle, $matches)) {
                $question->title = $matches[1] . ($matches[2] + 1);
            }
        }
        $this->render('create', ['question' => $question]);

    }

    /**
     * @param int $id
     * @return \Question
     */
    protected function loadModel($id) {
        return \Question::model()->findByAttributes([
            'qid' => $id
        ]);
    }
}

